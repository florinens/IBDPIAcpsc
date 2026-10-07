<?php
require_once 'config.php';
require_once 'auth.php';

$error = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = login($email, $password);
        
        if ($result['success']) {
            // Check role name (not role_id)
            if ($result['role'] === 'admin') {
                header('Location: admin_home.php'); 
            } else {
                header('Location: user_home.php'); // Regular users go to home
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gaming Accounts</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <a href="index.php" class="far-left-logo">
            <img src="images/icon.png" alt="Logo" class="nav-logo">
        </a>
        <a href="login.php" class="nav-login-group">
            <span class="nav-icon">👤</span> 
            <span class="nav-link-text">Login</span>
        </a>
        <div class="nav-right-container">
            <div class="nav-right">
                <a href="index.php" class="nav-button">Home</a> 
                <a href="accounts.php" class="nav-button">Accounts</a> 
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

    <div class="form-container">
        <h2>Login to Your Account</h2>
        
        <?php if ($error): ?>
            <div style="background: #ff4757; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login_submit" class="btn-submit">Login</button>
        </form>
        
        <p class="signup-prompt">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>