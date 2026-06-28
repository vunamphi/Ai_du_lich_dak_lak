<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Điểm đến - Đắk Lắk Travel AI';

$categories = getAllCategories();
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$destinations = getAllDestinations($catId);

include __DIR__ . '/../includes/header.php';
?>

<h1 class="section-title">Điểm đến tại Đắk Lắk</h1>
<p class="section-sub">Lọc theo danh mục để tìm điểm đến phù hợp</p>

<div class="pills">
  <a href="<?= url('/public/destinations.php') ?>" class="pill <?= $catId === null ? 'active' : '' ?>">Tất cả</a>
  <?php foreach ($categories as $c): ?>
    <a href="<?= url('/public/destinations.php') ?>?cat=<?= e((string)$c['id']) ?>"
       class="pill <?= $catId === (int)$c['id'] ? 'active' : '' ?>">
       <?= e($c['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid">
  <?php if (empty($destinations)): ?>
    <p>Chưa có điểm đến nào trong danh mục này.</p>
  <?php endif; ?>
  <?php foreach ($destinations as $d): ?>
    <a href="<?= url('/public/destination.php') ?>?slug=<?= e($d['slug']) ?>" class="card">
      <div class="card-img">🌄</div>
      <div class="card-body">
        <h3><?= e($d['name']) ?></h3>
        <p><?= e($d['short_desc']) ?></p>
        <span class="badge">⭐ <?= e((string)$d['rating']) ?></span>
        <span class="badge">~<?= e((string)$d['avg_visit_hours']) ?>h</span>
        <span class="badge"><?= e($d['price_level']) ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
