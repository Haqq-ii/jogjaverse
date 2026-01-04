<div id="jove-widget" class="jove-widget" data-endpoint="api/jove_chat.php">
  <button id="jove-toggle" class="jove-toggle" type="button" aria-controls="jove-panel" aria-expanded="false">
    JOVE ✨
  </button>
  <div id="jove-panel" class="jove-panel" role="dialog" aria-label="Chat JOVE" hidden>
    <div class="jove-header">
      <span class="jove-title">JOVE ✨</span>
      <button id="jove-close" class="jove-close" type="button" aria-label="Tutup chat">×</button>
    </div>
    <div id="jove-messages" class="jove-body" aria-live="polite"></div>
    <form id="jove-form" class="jove-footer" autocomplete="off">
      <input id="jove-input" type="text" name="message" maxlength="500" placeholder="Tulis pesan..." required>
      <button id="jove-send" type="submit">Kirim</button>
    </form>
  </div>
</div>
