<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$days = max(1, min(10, (int)($input['days'] ?? 2)));
$prefs = is_array($input['prefs'] ?? null) ? $input['prefs'] : [];
$notes = trim((string)($input['notes'] ?? ''));

$destinationsContext = getDestinationsSummaryForAI();
$prefsText = $prefs ? implode(', ', $prefs) : 'không có yêu cầu cụ thể';

$systemPrompt = <<<SYS
Bạn là chuyên gia du lịch địa phương tại tỉnh Đắk Lắk, Việt Nam.
Bạn CHỈ được gợi ý các điểm đến có trong danh sách dữ liệu được cung cấp dưới đây (không tự bịa thêm địa điểm khác ngoài danh sách, có thể bổ sung gợi ý ăn uống/nghỉ ngơi chung nếu cần).

DANH SÁCH ĐIỂM ĐẾN:
{$destinationsContext}

Luôn trả lời CHỈ bằng JSON hợp lệ (không thêm text, không markdown, không dùng dấu ```), theo đúng cấu trúc:
{
  "itinerary": [
    {
      "day": 1,
      "title": "Tên chủ đề ngắn cho ngày",
      "items": [
        {"time": "Sáng", "activity": "Nội dung hoạt động, có thể nhắc tên địa điểm", "address": "Địa chỉ cụ thể của địa điểm (lấy đúng từ danh sách dữ liệu, để trống nếu không phải 1 địa điểm cụ thể)"},
        {"time": "Trưa", "activity": "...", "address": "..."},
        {"time": "Chiều", "activity": "...", "address": "..."},
        {"time": "Tối", "activity": "...", "address": "..."}
      ]
    }
  ]
}
Luôn điền trường "address" bằng địa chỉ cụ thể (lấy đúng từ trường "địa chỉ" trong danh sách điểm đến) mỗi khi hoạt động gắn với 1 địa điểm trong danh sách.
SYS;

$userPrompt = "Hãy lên lịch trình du lịch Đắk Lắk trong {$days} ngày.\n"
    . "Sở thích của du khách: {$prefsText}.\n"
    . ($notes !== '' ? "Yêu cầu thêm: {$notes}.\n" : '')
    . "Hãy phân bổ hợp lý các điểm đến theo từng buổi, tránh di chuyển quá xa trong cùng 1 ngày, và trả lời đúng định dạng JSON yêu cầu.";

$aiResponse = callGemini(
    [['role' => 'user', 'content' => $userPrompt]],
    $systemPrompt,
    4096
);

// Cố gắng parse JSON từ AI (loại bỏ markdown fences nếu có, và chỉ lấy phần
// JSON nằm giữa dấu { đầu tiên và } cuối cùng, đề phòng AI thêm chữ thừa).
$clean = trim($aiResponse);
$clean = preg_replace('/^```json\s*|\s*```$/m', '', $clean);
$clean = trim($clean, "` \n");

$firstBrace = strpos($clean, '{');
$lastBrace  = strrpos($clean, '}');
if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
    $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
}

$parsed = json_decode($clean, true);

if (!$parsed || empty($parsed['itinerary'])) {
    echo json_encode([
        'success' => false,
        'message' => 'AI chưa trả về dữ liệu hợp lệ. Phản hồi gốc: ' . substr($aiResponse, 0, 300),
    ]);
    exit;
}

// Lưu vào MySQL
try {
    $db = getDB();
    $user = currentUser();
    $userId = $user['id'] ?? null;

    $stmt = $db->prepare("INSERT INTO itineraries (user_id, title, days, preferences, ai_raw_response) VALUES (?, ?, ?, ?, ?)");
    $title = "Lịch trình {$days} ngày - " . ($prefsText !== 'không có yêu cầu cụ thể' ? $prefsText : 'Đắk Lắk');
    $stmt->execute([$userId, $title, $days, $prefsText, $aiResponse]);
    $itineraryId = (int)$db->lastInsertId();

    $itemStmt = $db->prepare(
        "INSERT INTO itinerary_items (itinerary_id, day_number, time_slot, activity, address, sort_order) VALUES (?, ?, ?, ?, ?, ?)"
    );

    $sort = 0;
    foreach ($parsed['itinerary'] as $day) {
        $dayNum = (int)($day['day'] ?? 1);
        foreach (($day['items'] ?? []) as $item) {
            $itemStmt->execute([
                $itineraryId,
                $dayNum,
                $item['time'] ?? '',
                $item['activity'] ?? '',
                $item['address'] ?? '',
                $sort++,
            ]);
        }
    }
} catch (Exception $e) {
    // Nếu lưu DB lỗi (vd chưa setup DB), vẫn trả kết quả AI về cho người dùng xem
    echo json_encode([
        'success' => true,
        'itinerary' => $parsed['itinerary'],
        'warning' => 'Không lưu được vào database: ' . $e->getMessage(),
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'itinerary_id' => $itineraryId,
    'itinerary' => $parsed['itinerary'],
]);
