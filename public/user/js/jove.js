document.addEventListener("DOMContentLoaded", () => {
  const widget = document.getElementById("jove-widget");
  if (!widget) return;

  const panel = document.getElementById("jove-panel");
  const toggleBtn = document.getElementById("jove-toggle");
  const closeBtn = document.getElementById("jove-close");
  const form = document.getElementById("jove-form");
  const input = document.getElementById("jove-input");
  const sendBtn = document.getElementById("jove-send");
  const messagesEl = document.getElementById("jove-messages");
  const endpoint = widget.dataset.endpoint || "api/jove_chat.php";
  const historyKey = "jove_chat_history";

  const defaultGreeting = {
    role: "assistant",
    content: "Hai! Saya JOVE ✨, asisten wisata JogjaVerse. Mau cari destinasi apa hari ini?"
  };

  const loadHistory = () => {
    try {
      const raw = localStorage.getItem(historyKey);
      const parsed = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(parsed)) return [defaultGreeting];
      return parsed.length ? parsed : [defaultGreeting];
    } catch (err) {
      return [defaultGreeting];
    }
  };

  let history = loadHistory();

  const saveHistory = () => {
    try {
      localStorage.setItem(historyKey, JSON.stringify(history));
    } catch (err) {
      // ignore storage errors
    }
  };

  const renderMessages = () => {
    messagesEl.innerHTML = "";
    history.forEach((msg) => {
      const bubble = document.createElement("div");
      bubble.className = `jove-bubble ${msg.role === "user" ? "jove-user" : "jove-bot"}`;
      bubble.textContent = msg.content;
      messagesEl.appendChild(bubble);
    });
    messagesEl.scrollTop = messagesEl.scrollHeight;
  };

  const addMessage = (role, content) => {
    history.push({ role, content });
    saveHistory();
    renderMessages();
  };

  const setPanelOpen = (open) => {
    if (open) {
      panel.hidden = false;
      panel.classList.add("open");
      toggleBtn.setAttribute("aria-expanded", "true");
      setTimeout(() => input?.focus(), 50);
    } else {
      panel.classList.remove("open");
      panel.hidden = true;
      toggleBtn.setAttribute("aria-expanded", "false");
    }
  };

  renderMessages();

  toggleBtn.addEventListener("click", () => {
    const isOpen = !panel.hidden;
    setPanelOpen(!isOpen);
  });

  closeBtn.addEventListener("click", () => {
    setPanelOpen(false);
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const message = (input.value || "").trim();
    if (!message) return;
    if (message.length > 500) {
      addMessage("assistant", "Pesan terlalu panjang. Maksimal 500 karakter ya.");
      return;
    }

    addMessage("user", message);
    input.value = "";
    sendBtn.disabled = true;

    const typingBubble = document.createElement("div");
    typingBubble.className = "jove-bubble jove-bot jove-typing";
    typingBubble.textContent = "JOVE sedang mengetik...";
    messagesEl.appendChild(typingBubble);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
      });
      const data = res.ok ? await res.json() : null;
      typingBubble.remove();
      const reply = data && data.reply ? data.reply : "Maaf, JOVE sedang sibuk. Coba lagi ya.";
      addMessage("assistant", reply);
    } catch (err) {
      typingBubble.remove();
      addMessage("assistant", "Maaf, JOVE sedang sibuk. Coba lagi ya.");
    } finally {
      sendBtn.disabled = false;
    }
  });
});
