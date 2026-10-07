<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$account_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// --- HANDLE INDIVIDUAL IMAGE DELETION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_single_ss'])) {
    $ss_id = $_POST['ss_id_to_delete'];
    
    // 1. Get path to delete file from disk
    $path_stmt = $conn->prepare("SELECT ss_link FROM Screenshot WHERE id = ?");
    $path_stmt->bind_param("i", $ss_id);
    $path_stmt->execute();
    $path_res = $path_stmt->get_result()->fetch_assoc();
    
    if ($path_res) {
        if (file_exists($path_res['ss_link'])) {
            unlink($path_res['ss_link']);
        }
        // 2. Clear records
        $conn->query("DELETE FROM Account_ss WHERE ss_id = $ss_id");
        $conn->query("DELETE FROM Screenshot WHERE id = $ss_id");
        $success = "Image removed successfully.";
    }
}

// --- FETCH CURRENT ACCOUNT DATA ---
$stmt = $conn->prepare("SELECT * FROM Account WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin_accounts.php');
    exit();
}
$account = $result->fetch_assoc();

// --- FETCH CURRENT SCREENSHOTS ---
$ss_stmt = $conn->prepare("SELECT ss.id, ss.ss_link FROM Screenshot ss JOIN Account_ss acc_ss ON ss.id = acc_ss.ss_id WHERE acc_ss.account_id = ?");
$ss_stmt->bind_param("i", $account_id);
$ss_stmt->execute();
$current_screenshots = $ss_stmt->get_result();

// --- HANDLE MAIN FORM UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_mm = $_POST['price_mm'] ?? 0;
    $mm = $_POST['mm'] ?? 0;
    $status = $_POST['status'] ?? 'available';
    $additional = $_POST['additional'] ?? null;
    $admin_id = $_SESSION['user_id'];
    
    if (empty($username) || empty($description) || empty($price_mm)) {
        $error = 'Please fill in all required fields';
    } else {
        if (!empty($password)) {
            $stmt = $conn->prepare("UPDATE Account SET username=?, password=?, description=?, price_mm=?, mm=?, status=?, additional=?, updatedBy=? WHERE id=?");
            $stmt->bind_param("sssiiisii", $username, $password, $description, $price_mm, $mm, $status, $additional, $admin_id, $account_id);
        } else {
            $stmt = $conn->prepare("UPDATE Account SET username=?, description=?, price_mm=?, mm=?, status=?, additional=?, updatedBy=? WHERE id=?");
            $stmt->bind_param("ssiisiii", $username, $description, $price_mm, $mm, $status, $additional, $admin_id, $account_id);
        }
        
        if ($stmt->execute()) {
            // Handle new uploads (Additive)
            if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
                $upload_dir = 'uploads/screenshots/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                foreach ($_FILES['screenshots']['tmp_name'] as $i => $tmp_name) {
                    if ($_FILES['screenshots']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['screenshots']['name'][$i], PATHINFO_EXTENSION);
                        $dest = $upload_dir . 'acc_' . $account_id . '_' . time() . '_' . $i . '.' . $ext;
                        
                        if (move_uploaded_file($tmp_name, $dest)) {
                            $stmt_new_ss = $conn->prepare("INSERT INTO Screenshot (ss_link) VALUES (?)");
                            $stmt_new_ss->bind_param("s", $dest);
                            $stmt_new_ss->execute();
                            $new_id = $conn->insert_id;
                            
                            $stmt_link = $conn->prepare("INSERT INTO Account_ss (account_id, ss_id) VALUES (?, ?)");
                            $stmt_link->bind_param("ii", $account_id, $new_id);
                            $stmt_link->execute();
                        }
                    }
                }
            }
            header('Location: admin_accounts.php?msg=updated');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Account - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #4a5072; color: white; font-family: sans-serif; }
        .form-container { background: #5d678f; padding: 2rem; border-radius: 15px; max-width: 700px; margin: 2rem auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #fff; }
        input, textarea, select { width: 100%; padding: 0.8rem; border-radius: 8px; border: none; box-sizing: border-box; }
        
        .gallery { display: flex; gap: 15px; flex-wrap: wrap; margin: 15px 0; padding: 10px; background: rgba(0,0,0,0.1); border-radius: 10px; }
        .gallery-item { position: relative; width: 110px; height: 110px; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 2px solid #fff; }
        .del-btn { position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-weight: bold; }
        
        .btn-submit { background: #25d366; color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center;">Edit Account</h2>
        
        <?php if ($success): ?><div style="background:#2ecc71; padding:10px; border-radius:5px; margin-bottom:1rem;"><?php echo $success; ?></div><?php endif; ?>
        <?php if ($error): ?><div style="background:#ff4757; padding:10px; border-radius:5px; margin-bottom:1rem;"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($account['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Change Password (leave blank to keep current)</label>
                <input type="text" name="password">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required><?php echo htmlspecialchars($account['description']); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label>Price</label>
                    <input type="number" name="price_mm" value="<?php echo $account['price_mm']; ?>" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>MM Fee</label>
                    <input type="number" name="mm" value="<?php echo $account['mm']; ?>" required>
                </div>
            </div>

            <label>Current Screenshots (Click × to delete)</label>
            <div class="gallery">
                <?php while($ss = $current_screenshots->fetch_assoc()): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($ss['ss_link']); ?>">
                        <button type="submit" name="delete_single_ss" class="del-btn" onclick="document.getElementById('ss_del_id').value='<?php echo $ss['id']; ?>';">×</button>
                    </div>
                <?php endwhile; ?>
            </div>
            <input type="hidden" name="ss_id_to_delete" id="ss_del_id">

            <div class="form-group">
                <label>Add More Screenshots</label>
                <input type="file" name="screenshots[]" multiple accept="image/*">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="available" <?php if($account['status'] == 'available') echo 'selected'; ?>>Available</option>
                    <option value="sold" <?php if($account['status'] == 'sold') echo 'selected'; ?>>Sold</option>
                </select>
            </div>

            <button type="submit" name="update_account" class="btn-submit">Update Account</button>
            <a href="admin_accounts.php" style="display:block; text-align:center; margin-top:1rem; color:#ccc; text-decoration:none;">Cancel</a>
        </form>
    </div>
</body>
</html>