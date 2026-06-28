<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Chatbot AI - Đắk Lắk Travel AI';

if (empty($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = bin2hex(random_bytes(16));
}
$askPrefill = $_GET['ask'] ?? '';

include __DIR__ . '/../includes/header.php';
?>

<section class="chat-hero">
  <div class="chat-hero-text">
    <h1>Trợ lý AI du lịch Đắk Lắk 🌿</h1>
    <p>Ngôn ngữ tự nhiên như người thật — hỏi gì cũng có, trả lời 24/7 về điểm đến, văn hoá, ẩm thực và lịch trình.</p>
    <a href="#chat-box" class="btn">💬 Bắt đầu trò chuyện</a>
  </div>
  <div class="chat-hero-art">
    <svg viewBox="0 0 220 220" width="220" height="220">
      <circle cx="110" cy="110" r="105" fill="rgba(255,255,255,0.08)"/>
      <circle cx="110" cy="110" r="80" fill="rgba(255,255,255,0.10)"/>
      <!-- antenna -->
      <line x1="110" y1="40" x2="110" y2="58" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
      <circle cx="110" cy="34" r="7" fill="#ffb703"/>
      <!-- head -->
      <rect x="62" y="58" width="96" height="78" rx="22" fill="#ffffff"/>
      <!-- eyes -->
      <circle cx="90" cy="96" r="9" fill="#2d6a4f"/>
      <circle cx="130" cy="96" r="9" fill="#2d6a4f"/>
      <!-- smile -->
      <path d="M85 114 Q110 130 135 114" stroke="#2d6a4f" stroke-width="5" fill="none" stroke-linecap="round"/>
      <!-- body -->
      <rect x="74" y="142" width="72" height="50" rx="16" fill="#e9ecef"/>
      <circle cx="110" cy="167" r="10" fill="#ffb703"/>
      <!-- arms -->
      <circle cx="58" cy="160" r="10" fill="#ffffff"/>
      <circle cx="162" cy="160" r="10" fill="#ffffff"/>
    </svg>
  </div>
</section>

<div id="chat-box">
<p class="section-sub" style="margin-top:6px;">Hỏi bất cứ điều gì về điểm đến, văn hoá, ẩm thực, thời điểm nên đi du lịch Đắk Lắk...</p>

<div class="chat-window" id="chat-window">
  <div class="msg-row bot-row">
    <div class="msg-avatar">🤖</div>
    <div class="msg bot">Xin chào! Mình là trợ lý AI du lịch Đắk Lắk 🌿. Bạn muốn hỏi gì về Hồ Lắk, Buôn Đôn, cà phê Buôn Ma Thuột hay lịch trình du lịch?</div>
  </div>
</div>

<form id="chat-form" class="chat-input-row">
  <input type="text" id="chat-input" placeholder="Nhập câu hỏi của bạn..." autocomplete="off" value="<?= e($askPrefill) ?>">
  <button type="submit" class="btn">Gửi</button>
</form>
</div>

<script>
const chatWindow = document.getElementById('chat-window');
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');

function addMessage(text, role) {
  const row = document.createElement('div');
  row.className = 'msg-row ' + (role === 'user' ? 'user-row' : 'bot-row');

  const avatar = document.createElement('div');
  avatar.className = 'msg-avatar';
  avatar.textContent = role === 'user' ? '🧑' : '🤖';

  const bubble = document.createElement('div');
  bubble.className = 'msg ' + role;
  bubble.textContent = text;

  if (role === 'user') {
    row.appendChild(bubble);
    row.appendChild(avatar);
  } else {
    row.appendChild(avatar);
    row.appendChild(bubble);
  }

  chatWindow.appendChild(row);
  chatWindow.scrollTop = chatWindow.scrollHeight;
  return bubble;
}

async function sendMessage(text) {
  if (!text.trim()) return;
  addMessage(text, 'user');
  chatInput.value = '';
  const loadingDiv = addMessage('Đang trả lời...', 'bot');
  loadingDiv.classList.add('loading-dots');

  try {
    const res = await fetch('<?= url('/api/chat.php') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text })
    });
    const data = await res.json();
    loadingDiv.classList.remove('loading-dots');
    loadingDiv.textContent = data.reply || 'Xin lỗi, có lỗi xảy ra.';
  } catch (err) {
    loadingDiv.classList.remove('loading-dots');
    loadingDiv.textContent = 'Lỗi kết nối tới server.';
  }
}

chatForm.addEventListener('submit', (e) => {
  e.preventDefault();
  sendMessage(chatInput.value);
});

window.addEventListener('DOMContentLoaded', () => {
  const prefill = chatInput.value;
  if (prefill) sendMessage(prefill);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
