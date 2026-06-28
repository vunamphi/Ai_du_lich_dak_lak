<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Lịch trình AI - Đắk Lắk Travel AI';
$prefill = $_GET['prefill'] ?? '';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="section-title">🧭 Lên lịch trình du lịch Đắk Lắk bằng AI</h1>
<p class="section-sub">Chọn số ngày và sở thích, AI sẽ gợi ý lịch trình chi tiết theo từng ngày, từng buổi.</p>

<div class="form-box">
  <form id="itinerary-form">
    <div class="form-group">
      <label>Số ngày du lịch</label>
      <select name="days" id="days">
        <option value="1">1 ngày</option>
        <option value="2" selected>2 ngày</option>
        <option value="3">3 ngày</option>
        <option value="4">4 ngày</option>
        <option value="5">5 ngày</option>
      </select>
    </div>

    <div class="form-group">
      <label>Sở thích / phong cách du lịch</label>
      <div class="checkbox-group">
        <label><input type="checkbox" name="prefs[]" value="thiên nhiên"> Thiên nhiên</label>
        <label><input type="checkbox" name="prefs[]" value="văn hoá"> Văn hoá - bản địa</label>
        <label><input type="checkbox" name="prefs[]" value="ẩm thực"> Ẩm thực</label>
        <label><input type="checkbox" name="prefs[]" value="trekking"> Trekking/mạo hiểm</label>
        <label><input type="checkbox" name="prefs[]" value="cà phê"> Cà phê</label>
        <label><input type="checkbox" name="prefs[]" value="gia đình"> Gia đình có trẻ nhỏ</label>
        <label><input type="checkbox" name="prefs[]" value="chụp ảnh"> Chụp ảnh</label>
      </div>
    </div>

    <div class="form-group">
      <label>Yêu cầu thêm (tuỳ chọn)</label>
      <textarea name="notes" rows="3" placeholder="Ví dụ: đi cùng người lớn tuổi, ngân sách thấp, muốn nghỉ trưa dài..."><?= $prefill ? 'Muốn ghé: ' . e($prefill) : '' ?></textarea>
    </div>

    <button type="submit" class="btn">✨ Tạo lịch trình bằng AI</button>
  </form>
</div>

<div id="result"></div>

<script>
const form = document.getElementById('itinerary-form');
const resultBox = document.getElementById('result');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const days = document.getElementById('days').value;
  const prefs = Array.from(form.querySelectorAll('input[name="prefs[]"]:checked')).map(c => c.value);
  const notes = form.querySelector('textarea[name="notes"]').value;

  resultBox.innerHTML = '<p class="loading-dots">🤖 AI đang lên lịch trình cho bạn, vui lòng đợi vài giây...</p>';

  try {
    const res = await fetch('<?= url('/api/generate_itinerary.php') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ days, prefs, notes })
    });
    const data = await res.json();

    if (!data.success) {
      resultBox.innerHTML = '<p style="color:red;">❌ ' + (data.message || 'Có lỗi xảy ra.') + '</p>';
      return;
    }

    let html = '<h2 class="section-title">Lịch trình gợi ý của bạn</h2>';
    data.itinerary.forEach(day => {
      html += '<div class="day-block"><h3>Ngày ' + day.day + (day.title ? ': ' + day.title : '') + '</h3>';
      day.items.forEach(item => {
        const addr = item.address ? '<div class="time-slot-address">📍 ' + item.address + '</div>' : '';
        html += '<div class="time-slot"><strong>' + (item.time || '') + ':</strong> ' + item.activity + addr + '</div>';
      });
      html += '</div>';
    });
    resultBox.innerHTML = html;
  } catch (err) {
    resultBox.innerHTML = '<p style="color:red;">❌ Lỗi kết nối tới server.</p>';
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
