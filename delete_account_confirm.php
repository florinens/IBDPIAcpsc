<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$account_id = $_GET['id'] ?? 0;

// 1. Fetch account details for the warning message
$stmt = $conn->prepare("SELECT username FROM Account WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

if (!$account) {
    header('Location: admin_accounts.php');
    exit();
}

// 2. Handle the actual deletion after user clicks "Yes"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    
    // A. Get all screenshot paths to delete files from disk
    $ss_stmt = $conn->prepare("SELECT ss.ss_link, ss.id FROM Screenshot ss JOIN Account_ss acc_ss ON ss.id = acc_ss.ss_id WHERE acc_ss.account_id = ?");
    $ss_stmt->bind_param("i", $account_id);
    $ss_stmt->execute();
    $screenshots = $ss_stmt->get_result();

    while ($ss = $screenshots->fetch_assoc()) {
        if (file_exists($ss['ss_link'])) {
            unlink($ss['ss_link']); // Deletes physical file
        }
        $ss_id = $ss['id'];
        $conn->query("DELETE FROM Screenshot WHERE id = $ss_id");
    }

    // B. Delete the link records
    $conn->query("DELETE FROM Account_ss WHERE account_id = $account_id");

    // C. Delete the account itself
    $del_stmt = $conn->prepare("DELETE FROM Account WHERE id = ?");
    $del_stmt->bind_param("i", $account_id);
    
    if ($del_stmt->execute()) {
        header('Location: admin_accounts.php?msg=deleted');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Delete - Salju Store</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #4a5072; color: white; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .confirm-box { background: #5d678f; padding: 2.5rem; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.4); max-width: 400px; }
        .warning-icon { font-size: 4rem; color: #ff4757; margin-bottom: 1rem; }
        .btn-container { display: flex; gap: 10px; margin-top: 2rem; }
        .btn { flex: 1; padding: 0.8rem; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; }
        .btn-danger { background: #ff4757; color: white; }
        .btn-cancel { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <div class="confirm-box">
        <div class="warning-icon">⚠️</div>
        <h2>Are you sure?</h2>
        <p>You are about to delete account: <br><strong><?php echo htmlspecialchars($account['username']); ?></strong></p>
        <p style="font-size: 0.85rem; opacity: 0.8;">This will permanently remove the account details and all associated screenshots. This action cannot be undone.</p>
        
        <form method="POST" class="btn-container">
            <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete It</button>
            <a href="admin_accounts.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>