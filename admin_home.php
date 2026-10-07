<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lords Mobile Mockup</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <header class="navbar">
        <a href="admin_home.php" class="far-left-logo">
            <img src="images/icon.png" alt="Logo" class="nav-logo">
        </a>
        <a href="#" class="nav-user-group"> 
            <span class="nav-icon">👤</span> 
            <span class="nav-link-text">Admin</span>
        </a>
        <a href="logout.php" class="nav-button">Logout</a>
        <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
        <div class="nav-right-container">
            <div class="nav-right">
                <a href="admin_testimonies.php" class="nav-button active">Testimonies</a>
                <a href="admin_accounts.php" class="nav-button">Accounts</a>
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
        <main class="hero-section">
            <button class="info-button">I</button> 
            <h1 class="welcome-text">Welcome Admin</h1>
        </main>
        <a href="chatbot.php" class="chatbot-link">
            <button class="chatbot-button">
                <img src="images/chatbot.png" alt="Chatbot" class="chatbot-img">
            </button>
        </a>
    </div>

</body>
</html>