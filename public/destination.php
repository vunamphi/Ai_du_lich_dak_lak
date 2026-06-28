<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
$d = getDestinationBySlug($slug);

if (!$d) {
  http_response_code(404);
  $pageTitle = 'Không tìm thấy điểm đến';
  include __DIR__ . '/../includes/header.php';
  echo '<h1>404 - Không tìm thấy điểm đến này.</h1>';
  echo '<p><a href="' . url('/public/destinations.php') . '">← Quay lại danh sách điểm đến</a></p>';
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$pageTitle = $d['name'] . ' - Đắk Lắk Travel AI';
include __DIR__ . '/../includes/header.php';
?>

<p><a href="<?= url('/public/destinations.php') ?>">← Quay lại danh sách điểm đến</a></p>

<div class="detail-hero">
  <?php if (!empty($d['image_url'])): ?>
    <img src="<?= e($d['image_url']) ?>" alt="<?= e($d['name']) ?>"
      style="width:100%;height:100%;object-fit:cover;border-radius:16px;">
  <?php else: ?>
    🌄
  <?php endif; ?>
</div>
<h1><?= e($d['name']) ?></h1>
<p style="color:#666;"><?= e($d['address']) ?></p>

<div class="meta-row">
  <div class="meta-item">⭐ <?= e((string) $d['rating']) ?>/5</div>
  <div class="meta-item">⏱ ~<?= e((string) $d['avg_visit_hours']) ?> giờ tham quan</div>
  <div class="meta-item">💰 Mức chi phí: <?= e($d['price_level']) ?></div>
  <?php if ($d['tags']): ?>
    <div class="meta-item">🏷 <?= e($d['tags']) ?></div>
  <?php endif; ?>
</div>

<div class="form-box">
  <h3>Giới thiệu</h3>
  <p><?= nl2br(e($d['description'])) ?></p>
</div>

<div class="cta">
  <a href="<?= url('/public/itinerary.php') ?>?prefill=<?= e($d['slug']) ?>" class="btn">🧭 Đưa vào lịch trình AI</a>
  <a href="<?= url('/public/chatbot.php') ?>?ask=<?= urlencode('Cho tôi biết thêm về ' . $d['name']) ?>"
    class="btn secondary">💬 Hỏi Chatbot AI về nơi này</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>