// ==============================
// Chatbot Interactivity
// ==============================
const options = document.querySelectorAll(".option-btn");
const messages = document.querySelector(".chatbot-messages");

if (options.length > 0 && messages) {
  options.forEach(option => {
    option.addEventListener("click", () => {
      const userText = option.innerText;

      // User message
      const userMsg = document.createElement("div");
      userMsg.classList.add("message", "user-message");
      userMsg.innerText = userText;
      messages.appendChild(userMsg);

      // Bot reply
      const botMsg = document.createElement("div");
      botMsg.classList.add("message", "bot-message");

      if (userText.includes("buy")) {
        botMsg.innerText = "To buy an account, click View and then Buy Now.";
      } 
      else if (userText.includes("Payment")) {
        botMsg.innerText = "We support bank transfer and digital wallets.";
      } 
      else if (userText.includes("Contact")) {
        botMsg.innerText = "You can contact us via WhatsApp or Telegram.";
      } 
      else {
        botMsg.innerText = "I’m here to help!";
      }

      messages.appendChild(botMsg);

      // Auto scroll
      messages.scrollTop = messages.scrollHeight;
    });
  });
}

// ==============================
// Filter Accounts
// ==============================
const statusFilter = document.querySelector("select");

if (statusFilter) {
  statusFilter.addEventListener("change", () => {
    const cards = document.querySelectorAll(".account-card");
    const value = statusFilter.value.toLowerCase();

    cards.forEach(card => {
      const status = card.innerText.toLowerCase();

      if (value === "all" || status.includes(value)) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  });
}

// ==============================
// Button click animations
// ==============================
const buttons = document.querySelectorAll(".btn");

buttons.forEach(btn => {
  btn.addEventListener("click", () => {
    btn.style.transform = "scale(0.95)";
    setTimeout(() => {
      btn.style.transform = "scale(1)";
    }, 150);
  });
});

// ==============================
// Dashboard fake live counters
// ==============================
const stats = document.querySelectorAll(".stat-number");

stats.forEach(stat => {
  let count = 0;
  const target = parseInt(stat.innerText);

  const interval = setInterval(() => {
    count++;
    stat.innerText = count;

    if (count >= target) {
      clearInterval(interval);
    }
  }, 20);
});
