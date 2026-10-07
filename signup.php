<?php
require_once 'config.php';
require_once 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $result = signup($username, $email, $password);
        
        if ($result['success']) {
            // Auto login after signup
            login($email, $password);
            header('Location: login.php');
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
    <title>Sign Up - Gaming Accounts</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
</head>
<body class="page-signup"> 
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

    <div class="form-container">
        <h2>Sign Up</h2>

        <?php if (!empty($error)): ?>
            <p style="color: #ff4757; text-align: center; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-submit">Sign Up</button>
        </form>
        
        <div class="signup-prompt">
            Already have an account? <a href="login.php" style="color: #ffcc00; text-decoration: none;">Login here</a>
        </div>
    </div>

</body>
</html>