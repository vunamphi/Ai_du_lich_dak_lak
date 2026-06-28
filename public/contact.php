<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Liên hệ - Đắk Lắk Travel AI';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Demo: chỉ hiển thị thông báo gửi thành công (chưa lưu DB/gửi mail)
    $sent = true;
}

include __DIR__ . '/../includes/header.php';
?>

<h1 class="section-title">Liên hệ với chúng tôi</h1>
<p class="section-sub">Có câu hỏi hoặc góp ý? Gửi cho chúng tôi, đội ngũ sẽ phản hồi sớm nhất.</p>

<div class="form-box" style="max-width:520px;">
  <?php if ($sent): ?>
    <p style="color: var(--green-700); font-weight: 600;">✅ Đã gửi! Cảm ơn bạn đã liên hệ với Đắk Lắk Travel AI.</p>
  <?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Họ và tên</label>
      <input type="text" name="full_name" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Nội dung</label>
      <textarea name="message" rows="5" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;font-family:inherit;"></textarea>
    </div>
    <button type="submit" class="btn">Gửi liên hệ</button>
  </form>
  <p style="margin-top:18px;font-size:14px;color:#555;">
    📍 Buôn Ma Thuột, Đắk Lắk &nbsp; · &nbsp; 📧 contact@daklaktravel.vn
  </p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
