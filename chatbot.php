<?php
require_once 'config.php';
require_once 'auth.php';
// Functions like isLoggedIn() and isAdmin() are assumed to be defined in auth.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support Chatbot</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
    <header class="site-header">
        <nav class="navbar">
            <div class="nav-content-container">
                <div class="logo">Gaming Accounts</div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="accounts.php">Accounts</a></li>
                    <li><a href="testimonies.php">Testimonies</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin_dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content-area">
        <div class="chat-wrapper-container">
            <div class="chatbot-container">
                
                <div class="chatbot-header">
                    <h2>Customer Support Chatbot</h2>
                    <p>How may I assist you today?</p>
                </div>
                
                <div class="chatbot-messages-area" id="chatMessages">
                    <div class="message bot-message">
                        Hello! Welcome, How may I assist you today?
                    </div>
                </div>
                
                <div class="chatbot-input-area" id="chatOptions">
                    <button class="option-btn" onclick="handleOption(1)">1 - Account-based questions</button>
                    <button class="option-btn" onclick="handleOption(2)">2 - Price negotiations</button>
                    <button class="option-btn" onclick="handleOption(3)">3 - Payment methods</button>
                    <button class="option-btn" onclick="handleOption(4)">4 - Security and safety</button>
                    <button class="option-btn" onclick="handleOption(5)">5 - Other inquiries</button>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-content-container">
            <div class="contact-section">
                <h3>Contact Us</h3>
                <div class="social-links">
                    <a href="https://wa.me/1234567890" target="_blank" class="social-btn whatsapp">WhatsApp</a>
                    <a href="https://t.me/yourusername" target="_blank" class="social-btn telegram">Telegram</a>
                </div>
            </div>
    
        </div>
    </footer>

    <script>
        let conversationActive = true;
        
        function addMessage(text, isBot = true) {
            const messagesDiv = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isBot ? 'bot-message' : 'user-message'}`;
            messageDiv.textContent = text;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function showContinueOptions() {
            const optionsDiv = document.getElementById('chatOptions');
            optionsDiv.innerHTML = `
                <button class="option-btn" onclick="handleContinue(true)">Yes, I have another question</button>
                <button class="option-btn" onclick="handleContinue(false)">No, thank you</button>
            `;
        }
        
        function showMainOptions() {
            const optionsDiv = document.getElementById('chatOptions');
            optionsDiv.innerHTML = `
                <button class="option-btn" onclick="handleOption(1)">1 - Account-based questions</button>
                <button class="option-btn" onclick="handleOption(2)">2 - Price negotiations</button>
                <button class="option-btn" onclick="handleOption(3)">3 - Payment methods</button>
                <button class="option-btn" onclick="handleOption(4)">4 - Security and safety</button>
                <button class="option-btn" onclick="handleOption(5)">5 - Other inquiries</button>
            `;
        }
        
        function handleOption(option) {
            let userText = '';
            let botResponse = '';
            
            switch(option) {
                case 1:
                    userText = '1 - Account-based questions';
                    botResponse = 'All information about our accounts is available on the accounts page. However, if you need additional specific information, please feel free to contact our owners directly via WhatsApp or Telegram.';
                    break;
                case 2:
                    userText = '2 - Price negotiations';
                    botResponse = "I'm sorry, all price negotiations should be directed to the owners. Please contact them via WhatsApp or Telegram for pricing discussions.";
                    break;
                case 3:
                    userText = '3 - Payment methods';
                    botResponse = 'We accept various payment methods including PayPal and bank transfers. For specific payment arrangements, please contact our owners via WhatsApp or Telegram.';
                    break;
                case 4:
                    userText = '4 - Security and safety';
                    botResponse = 'We use a trusted middleman service to ensure secure transactions. All account details are verified before listing. Your payment is protected until you confirm receipt of the account.';
                    break;
                case 5:
                    userText = '5 - Other inquiries';
                    botResponse = 'For any other inquiries, please reach out to our support team via WhatsApp or Telegram. They will be happy to assist you with your specific questions.';
                    break;
            }
            
            addMessage(userText, false);
            setTimeout(() => {
                addMessage(botResponse, true);
                setTimeout(() => {
                    addMessage('Can I help you with anything else?', true);
                    showContinueOptions();
                }, 500);
            }, 500);
        }
        
        function handleContinue(wantsContinue) {
            if (wantsContinue) {
                addMessage('Yes', false);
                setTimeout(() => {
                    addMessage('Great! How may I assist you?', true);
                    showMainOptions();
                }, 500);
            } else {
                addMessage('No', false);
                setTimeout(() => {
                    addMessage('Thank you for using our chatbot service! If you need further assistance, feel free to contact us via WhatsApp or Telegram.', true);
                    document.getElementById('chatOptions').innerHTML = `
                        <a href="index.php" class="btn btn-primary" style="width: 100%; text-align: center;">Return to Home</a>
                    `;
                }, 500);
            }
        }
    </script>
</body>
</html>