<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = $_POST['username'] ?? '';
    $password    = $_POST['password'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_mm    = $_POST['price_mm'] ?? 0;
    $mm          = $_POST['mm'] ?? 0;
    $status      = $_POST['status'] ?? 'available';
    $additional  = $_POST['additional'] ?? null;
    $admin_id    = $_SESSION['user_id'] ?? 1;

    if (empty($username) || empty($password) || empty($description) || empty($price_mm)) {
        $error = 'Please fill in all required fields (Username, Password, Description, and Price)';
    } else {
        // Start Transaction to ensure data integrity
        $conn->begin_transaction();

        try {
            // 1. Insert into Account table
            $stmt = $conn->prepare("INSERT INTO Account (username, password, description, price_mm, mm, status, additional, updatedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiissi", $username, $password, $description, $price_mm, $mm, $status, $additional, $admin_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating account: " . $conn->error);
            }

            $account_id = $conn->insert_id;

            // 2. Handle Screenshot Uploads
            if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
                $upload_dir = 'uploads/screenshots/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['screenshots']['tmp_name'] as $i => $tmp_name) {
                    if ($_FILES['screenshots']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['screenshots']['name'][$i], PATHINFO_EXTENSION));
                        $new_filename = 'acc_' . $account_id . '_' . time() . '_' . $i . '.' . $ext;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            // Step A: Insert into Screenshot table
                            $stmt_ss = $conn->prepare("INSERT INTO Screenshot (ss_link) VALUES (?)");
                            $stmt_ss->bind_param("s", $destination);
                            $stmt_ss->execute();
                            $ss_id = $conn->insert_id;

                            // Step B: Link to Account_ss table
                            $stmt_link = $conn->prepare("INSERT INTO Account_ss (account_id, ss_id) VALUES (?, ?)");
                            $stmt_link->bind_param("ii", $account_id, $ss_id);
                            $stmt_link->execute();
                        }
                    }
                }
            }

            $conn->commit();
            header('Location: admin_accounts.php?msg=added');
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Account - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #4a5072; color: white; font-family: sans-serif; margin: 0; padding: 0; }
        .form-container { background: #5d678f; padding: 2rem; border-radius: 15px; max-width: 700px; margin: 2rem auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem; color: #b8c4d9; }
        input, textarea, select { width: 100%; padding: 0.8rem; border-radius: 8px; border: none; box-sizing: border-box; background: #fff; color: #333; }
        .btn-submit { background: #7fff00; color: #4a5072; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 1rem; font-size: 1.1rem; }
        .btn-submit:hover { background: #66cc00; }
        .preview-container { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .preview-item { width: 100px; height: 100px; border-radius: 5px; overflow: hidden; border: 2px solid #7fff00; position: relative; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
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
    <div class="form-container">
        <h2 style="text-align: center; font-style: italic;">Add New Account</h2>
        
        <?php if ($error): ?>
            <div style="background: #ff4757; padding: 10px; border-radius: 5px; margin-bottom: 1rem; font-weight: bold;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Account Username *</label>
                <input type="text" name="username" placeholder="e.g. LordsPlayer123" required>
            </div>
            
            <div class="form-group">
                <label>Account Password *</label>
                <input type="text" name="password" placeholder="Login password" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="List heroes, gear, etc."></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label>Price (USD) *</label>
                    <input type="number" name="price_mm" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>MM Fee (USD)</label>
                    <input type="number" name="mm" value="0">
                </div>
            </div>

            <div class="form-group">
                <label>Screenshots</label>
                <input type="file" name="screenshots[]" multiple accept="image/*" id="ss_input">
                <div id="image-preview" class="preview-container"></div>
            </div>

            <button type="submit" class="btn-submit">Add Account to Store</button>
        </form>
    </div>

    <script>
        document.getElementById('ss_input').addEventListener('change', function(event) {
            const previewContainer = document.getElementById('image-preview');
            previewContainer.innerHTML = '';
            
            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `<img src="${e.target.result}">`;
                        previewContainer.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>