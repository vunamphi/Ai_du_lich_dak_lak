<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pageTitle = 'Quản lý điểm đến - Admin';
$db = getDB();

// Xử lý xoá
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: ' . url('/admin/destinations.php'));
    exit;
}

// Xử lý thêm/sửa
$editing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $shortDesc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $avgHours = (float)($_POST['avg_visit_hours'] ?? 2);
    $priceLevel = $_POST['price_level'] ?? 'low';
    $tags = trim($_POST['tags'] ?? '');

    if ($id > 0) {
        $stmt = $db->prepare(
            "UPDATE destinations SET name=?, slug=?, short_desc=?, description=?, category_id=?, avg_visit_hours=?, price_level=?, tags=? WHERE id=?"
        );
        $stmt->execute([$name, $slug, $shortDesc, $description, $categoryId, $avgHours, $priceLevel, $tags, $id]);
    } else {
        $stmt = $db->prepare(
            "INSERT INTO destinations (name, slug, short_desc, description, category_id, avg_visit_hours, price_level, tags) VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([$name, $slug, $shortDesc, $description, $categoryId, $avgHours, $priceLevel, $tags]);
    }
    header('Location: ' . url('/admin/destinations.php'));
    exit;
}

if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch();
}

$categories = getAllCategories();
$destinations = getAllDestinations();

include __DIR__ . '/../includes/header.php';
?>

<h1 class="section-title">Quản lý điểm đến (Admin)</h1>
<p><a href="<?= url('/admin/destinations.php?logout=1') ?>">Đăng xuất</a></p>
<?php if (isset($_GET['logout'])) { unset($_SESSION['user']); header('Location: ' . url('/admin/login.php')); exit; } ?>

<div class="form-box">
  <h3><?= $editing ? 'Sửa điểm đến' : 'Thêm điểm đến mới' ?></h3>
  <form method="post">
    <input type="hidden" name="id" value="<?= e((string)($editing['id'] ?? '')) ?>">
    <div class="form-group">
      <label>Tên điểm đến</label>
      <input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Slug (để trống để tự tạo)</label>
      <input type="text" name="slug" value="<?= e($editing['slug'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Danh mục</label>
      <select name="category_id">
        <option value="">-- Chọn danh mục --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e((string)$c['id']) ?>" <?= ($editing['category_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Mô tả ngắn</label>
      <input type="text" name="short_desc" value="<?= e($editing['short_desc'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Mô tả chi tiết</label>
      <textarea name="description" rows="4"><?= e($editing['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Thời gian tham quan (giờ)</label>
      <input type="number" step="0.5" name="avg_visit_hours" value="<?= e((string)($editing['avg_visit_hours'] ?? 2)) ?>">
    </div>
    <div class="form-group">
      <label>Mức chi phí</label>
      <select name="price_level">
        <?php foreach (['free','low','medium','high'] as $pl): ?>
          <option value="<?= $pl ?>" <?= ($editing['price_level'] ?? '') === $pl ? 'selected' : '' ?>><?= $pl ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Tags (phân cách bởi dấu phẩy)</label>
      <input type="text" name="tags" value="<?= e($editing['tags'] ?? '') ?>">
    </div>
    <button type="submit" class="btn"><?= $editing ? 'Lưu thay đổi' : 'Thêm điểm đến' ?></button>
  </form>
</div>

<h3 class="section-title">Danh sách điểm đến</h3>
<table style="width:100%;background:white;border-radius:14px;overflow:hidden;border-collapse:collapse;">
  <tr style="background:#f1f1f1;text-align:left;">
    <th style="padding:10px;">Tên</th><th style="padding:10px;">Danh mục</th><th style="padding:10px;">Rating</th><th style="padding:10px;">Hành động</th>
  </tr>
  <?php foreach ($destinations as $d): ?>
    <tr style="border-top:1px solid #eee;">
      <td style="padding:10px;"><?= e($d['name']) ?></td>
      <td style="padding:10px;"><?= e((string)($d['category_id'] ?? '-')) ?></td>
      <td style="padding:10px;"><?= e((string)$d['rating']) ?></td>
      <td style="padding:10px;">
        <a href="<?= url('/admin/destinations.php') ?>?edit=<?= e((string)$d['id']) ?>">Sửa</a> |
        <a href="<?= url('/admin/destinations.php') ?>?delete=<?= e((string)$d['id']) ?>" onclick="return confirm('Xoá điểm đến này?')">Xoá</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>
