<?php
require_once 'config.php';
require_once 'auth.php';

// Fetch all testimonies from the database
$query = "SELECT * FROM Testimony ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaming Accounts - Testimonies</title>
    <link rel="stylesheet" href="styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Ensuring the images fit perfectly in your boxes */
        .screenshot-box {
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2a2d3e; /* Fallback color */
            min-height: 300px;
        }
        .testimony-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .screenshot-box:hover .testimony-img {
            transform: scale(1.05);
        }
    </style>
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

    <div class="testimonies-hero-section">
        <h1 class="welcome-text">Testimonies</h1>
    </div>

    <main class="trusted-section-wrapper">
        <h2 class="trusted-heading">Trusted by Players</h2>

        <div class="screenshot-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php if (!empty($row['tss_link'])): ?>
                        <div class="screenshot-box">
                            <img src="<?php echo htmlspecialchars($row['tss_link'] ?? ''); ?>" 
                                 alt="Testimony Screenshot" 
                                 class="testimony-img">
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: white; text-align: center; grid-column: 1/-1;">No testimonies yet!</p>
            <?php endif; ?>
        </div>
    </main>

    <a href="chatbot.php" class="chatbot-link">
        <button class="chatbot-button">
            <img src="images/chatbot.png" alt="Chatbot" class="chatbot-img">
        </button>
    </a>
</body>
</html>