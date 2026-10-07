<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interest Registered - Gaming Accounts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <a href="user_home.php" class="far-left-logo">
            <img src="images/icon.png" alt="Logo" class="nav-logo">
        </a>
        <a href="#" class="nav-user-group"> 
            <span class="nav-icon">👤</span> 
            <span class="nav-link-text">User</span>
        </a>
        <a href="logout.php" class="nav-button logout-button">Logout</a>
        <div class="nav-right-container">
            <div class="nav-right">
                <a href="user_testimonies.php" class="nav-button active">Testimonies</a>
                <a href="user_accounts.php" class="nav-button">Accounts</a>
                <div class="external-communications">
                    <img src="images/flag.png" alt="ID" class="comm-icon">
                    <a href="https://wa.me/08118086100" class="whatsapp-button comm-icon" target="_blank">
                        <img src="images/whatsapp-icon.png" alt="WhatsApp Icon" class="whatsapp-icon">
                    </a>
                    <span class="comm-icon">🌎</span>
                    <a href="https://t.me/saljustore" class="comm-icon">
                        <img src="images/telegram.png" alt="Telegram Icon" class="whatsapp-icon">
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="padding: 5rem 0; text-align: center;">
        <div style="max-width: 600px; margin: 0 auto; background: white; padding: 3rem; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <div style="width: 100px; height: 100px; background: #28a745; border-radius: 50%; margin: 0 auto 2rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white;">✓</div>
            
            <h1 style="color: #28a745; margin-bottom: 1rem;">Interest Registered Successfully!</h1>
            <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">Thank you for expressing interest in this account. We'll be in touch with you shortly via email.</p>
            
            <div style="background: #547097; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; text-align: left;">
                <h3 style="color: #ffffff; margin-bottom: 1rem;">What happens next?</h3>
                <p style="margin-bottom: 0.5rem;">1. Our team will review your interest</p>
                <p style="margin-bottom: 0.5rem;">2. We'll contact you via email within 24 hours</p>
                <p style="margin-bottom: 0.5rem;">3. We'll provide payment instructions and finalize the details</p>
                <p style="margin-bottom: 0;">4. The account will be transferred securely through our middleman service</p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="user_accounts.php" class="btn btn-primary">Browse More Accounts</a>
                <a href="user_home.php" class="btn btn-secondary">Return to Home</a>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <p style="color: #666; margin-bottom: 1rem;">Have questions? Contact us:</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="https://wa.me/1234567890" target="_blank" class="social-btn whatsapp">WhatsApp</a>
                    <a href="https://t.me/yourusername" target="_blank" class="social-btn telegram">Telegram</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <div class="social-links">
                        <a href="https://wa.me/1234567890" target="_blank" class="social-btn whatsapp">WhatsApp</a>
                        <a href="https://t.me/yourusername" target="_blank" class="social-btn telegram">Telegram</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Need Help?</h3>
                    <a href="chatbot.php" class="btn btn-small">Chat with Bot</a>
                </div>
            </div>
            <p class="copyright">&copy; 2024 Gaming Accounts Marketplace. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>