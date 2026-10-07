<?php
require_once 'config.php';
require_once 'auth.php';

// Safety check for statistics
if (file_exists('statistics_functions.php')) {
    require_once 'statistics_functions.php';
}

$account_id = $_GET['id'] ?? 0;

if ($account_id > 0 && function_exists('trackAccountView')) {
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    trackAccountView($account_id, $user_id);
}

// Get account details
$stmt = $conn->prepare("SELECT * FROM Account WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: accounts.php');
    exit();
}

$account = $result->fetch_assoc();

// Fetch screenshots
$ss_stmt = $conn->prepare("
    SELECT ss.ss_link 
    FROM Screenshot ss 
    JOIN Account_ss acc_ss ON ss.id = acc_ss.ss_id 
    WHERE acc_ss.account_id = ?
");
$ss_stmt->bind_param("i", $account_id);
$ss_stmt->execute();
$screenshots = $ss_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($account['username']); ?> - Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #4a5072; }
        
        /* The Card Container */
        .details-card { 
            max-width: 800px; 
            margin: 3rem auto; 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: #333; /* Forces all text inside to be dark grey/black */
        }

        .gallery-section { background: #000; padding: 10px; display: flex; flex-direction: column; gap: 10px; }
        .gallery-img { width: 100%; border-radius: 5px; }

        .info-section { padding: 2.5rem; }
        
        /* Explicitly setting dark colors for readability */
        .info-section h1 { color: #4a5072; margin-top: 0; font-size: 2.2rem; }
        .info-section p { color: #444; line-height: 1.6; }
        .info-section strong { color: #000; }

        .price-box { 
            background: #f0f2f8; 
            padding: 1.5rem; 
            border-radius: 10px; 
            margin: 1.5rem 0; 
            border-left: 5px solid #667eea;
        }
        .price-value { color: #667eea; font-size: 2rem; font-weight: bold; display: block; }
        
        .status-pill {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
            background: <?php echo $account['status'] == 'available' ? '#2ecc71' : '#e74c3c'; ?>;
        }

        .btn-group { display: flex; gap: 15px; margin-top: 2rem; }
        .btn-interest { background: #2ecc71; color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-weight: bold; cursor: pointer; flex: 2; font-size: 1.1rem; }
        .btn-back { background: #95a5a6; color: white; text-decoration: none; padding: 1rem; border-radius: 8px; flex: 1; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
        <header class="navbar">
        <a href="user_home.php" class="far-left-logo">
            <img src="images/icon.png" alt="Logo" class="nav-logo">
        </a>
        <a href="login.php" class="nav-login-group">
            <span class="nav-icon">👤</span> 
            <span class="nav-link-text">Login</span>
        </a>
        <div class="nav-right-container">
            <div class="nav-right">
                <a href="testimonies.php" class="nav-button">Testimonies</a>
                <a href="accounts.php" class="nav-button active">Accounts</a>
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
    <div class="details-card">
        <div class="gallery-section">
            <?php if ($screenshots->num_rows > 0): ?>
                <?php while ($ss = $screenshots->fetch_assoc()): ?>
                    <img src="<?php echo htmlspecialchars($ss['ss_link']); ?>" class="gallery-img">
                <?php endwhile; ?>
            <?php else: ?>
                <div style="height: 200px; color: white; display: flex; align-items: center; justify-content: center;">No screenshots available.</div>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1><?php echo htmlspecialchars($account['username']); ?></h1>
                <span class="status-pill"><?php echo $account['status']; ?></span>
            </div>

            <div class="price-box">
                <span style="color: #666; font-size: 0.9rem;">Total Price (USD)</span>
                <span class="price-value">$<?php echo number_format($account['price_mm']); ?></span>
            </div>

            <p><strong>Description:</strong></p>
            <p style="white-space: pre-wrap; background: #fafafa; padding: 1rem; border-radius: 5px;"><?php echo htmlspecialchars($account['description']); ?></p>

            <div class="btn-group">
                <a href="accounts.php" class="btn-back">Back to List</a>
            </div>
        </div>
    </div>

</body>
</html>