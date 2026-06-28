<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$userMessage = trim((string)($input['message'] ?? ''));

if ($userMessage === '') {
    echo json_encode(['reply' => 'Bạn hãy nhập câu hỏi nhé!']);
    exit;
}

if (empty($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = bin2hex(random_bytes(16));
}
$sessionId = $_SESSION['chat_session_id'];
$user = currentUser();
$userId = $user['id'] ?? null;

$destinationsContext = getDestinationsSummaryForAI();

$systemPrompt = <<<SYS
Bạn là trợ lý AI tư vấn du lịch thân thiện, am hiểu sâu về tỉnh Đắk Lắk, Việt Nam (Tây Nguyên).
Hãy trả lời ngắn gọn, tự nhiên, hữu ích, bằng tiếng Việt. Ưu tiên nhắc tới các điểm đến có trong danh sách dữ liệu sau nếu liên quan:

{$destinationsContext}

Nếu câu hỏi không liên quan đến du lịch Đắk Lắk, vẫn trả lời lịch sự nhưng khéo léo hướng người dùng quay lại chủ đề du lịch Đắk Lắk.
Không trả lời bằng JSON hay markdown phức tạp, chỉ trả lời bằng văn bản thường, súc tích (tối đa khoảng 150 từ).
SYS;

try {
    $db = getDB();

    // Lấy lịch sử gần nhất (tối đa 6 lượt) để AI có ngữ cảnh
    $histStmt = $db->prepare(
        "SELECT role, message FROM chat_logs WHERE session_id = ? ORDER BY id DESC LIMIT 10"
    );
    $histStmt->execute([$sessionId]);
    $history = array_reverse($histStmt->fetchAll());
} catch (Exception $e) {
    $history = [];
}

$messages = [];
foreach ($history as $h) {
    $messages[] = ['role' => $h['role'], 'content' => $h['message']];
}
$messages[] = ['role' => 'user', 'content' => $userMessage];

$reply = callGemini($messages, $systemPrompt, 600);

// Lưu lịch sử chat
try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO chat_logs (session_id, user_id, role, message) VALUES (?, ?, 'user', ?)");
    $stmt->execute([$sessionId, $userId, $userMessage]);

    $stmt = $db->prepare("INSERT INTO chat_logs (session_id, user_id, role, message) VALUES (?, ?, 'assistant', ?)");
    $stmt->execute([$sessionId, $userId, $reply]);
} catch (Exception $e) {
    // Bỏ qua lỗi lưu DB, vẫn trả lời người dùng
}

echo json_encode(['reply' => $reply]);
