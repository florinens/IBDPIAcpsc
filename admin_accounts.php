<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $account_id = (int)$_POST['account_id'];
    $conn->query("DELETE FROM User_Interest WHERE account_id = $account_id");
    $conn->query("DELETE FROM Account_ss WHERE account_id = $account_id");
    $conn->query("DELETE FROM Account WHERE id = $account_id");
    header('Location: admin_accounts.php');
    exit();
}

// Get filter and sort parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// FIXED SQL: JOIN Screenshot table to get the actual ss_link path
$query = "
    SELECT 
        A.*, 
        (SELECT ss.ss_link 
         FROM Account_ss AS acc_ss 
         JOIN Screenshot AS ss ON acc_ss.ss_id = ss.id 
         WHERE acc_ss.account_id = A.id 
         ORDER BY acc_ss.id ASC LIMIT 1) AS primary_screenshot
    FROM 
        Account A
    WHERE 
        1=1
";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (A.username LIKE ? OR A.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Add sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY A.price_mm ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY A.price_mm DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY A.id DESC";
        break;
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute(); 
$accounts = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
    <style>
        body { background: #4a5072; margin: 0; padding: 0; }
        .accounts-hero {
            position: relative;
            background: url('images/banner.jpg') no-repeat center/cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .accounts-title {
            font-family: 'Shrikhand', cursive;
            font-size: 5rem;
            color: #fff7e6;
            text-shadow: 4px 4px 0px rgba(0, 0, 0, 0.7), -2px -2px 0px #fff, 2px 2px 0px #fff;
            z-index: 5;
        }
        .search-filter-section { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .search-bar {
            background: white; border-radius: 25px; padding: 0.8rem 1.5rem;
            display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .search-bar input { border: none; outline: none; flex: 1; font-size: 1rem; }
        .search-bar button { background: none; border: none; cursor: pointer; font-size: 1.2rem; }
        
        .accounts-grid {
            max-width: 1200px; margin: 0 auto; padding: 2rem 20px;
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;
        }
        .account-card { background: #9bb4c9; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .account-card:hover { transform: translateY(-5px); }
        .card-image-container { height: 200px; overflow: hidden; background: #667eea; }
        .card-image-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .add-account-card { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px; cursor: pointer; border: 3px dashed rgba(255,255,255,0.5); }
        .add-icon { width: 150px; height: 150px; border: 5px solid white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 5rem; color: white; margin-bottom: 1rem; }
        
        .card-content { padding: 1.5rem; color: white; }
        .card-username { font-size: 1.5rem; font-weight: bold; margin-bottom: 0.5rem; }
        .card-price { font-size: 1.3rem; font-weight: bold; margin-bottom: 1rem; }
        .card-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .card-actions button, .card-actions a { flex: 1; padding: 0.6rem; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; text-align: center; font-weight: bold; }
        .btn-edit { background: #667eea; color: white; }
        .btn-delete { background: #e74c3c; color: white; }

         .btn-view { display: block; background: #0c1b5e; color: white; padding: 0.8rem; border-radius: 8px; text-decoration: none; text-align: center; font-weight: bold; transition: background 0.2s; }
        .btn-view:hover { background: #5a6fd6; }
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

    <div class="accounts-hero">
        <div class="accounts-hero-banner">
            <div class="accounts-hero-content">
                <h1 class="accounts-text">Accounts</h1>
                <img src="images/banner.png" alt="Lords Mobile" class="accounts-banner-logo">
            </div>
        </div>
    </div>
    <div class="search-filter-section">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <form method="GET" style="flex: 1;">
                <div class="search-bar">
                    <span>🔍</span>
                    <input type="text" name="search" placeholder="search here" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="button" onclick="window.location.href='admin_accounts.php'">✕</button>
                </div>
            </form>
        </div>
    </div>

    <div class="accounts-grid">
        <div class="account-card add-account-card" onclick="window.location.href='add_account.php'">
            <div class="add-icon">+</div>
            <div style="color: white; font-size: 1.5rem; font-weight: bold;">Add Account</div>
        </div>

        <?php while ($account = $accounts->fetch_assoc()): ?>
            <div class="account-card">
                <?php 
                    $image_src = 'images/placeholder.png';
                    if (!empty($account['primary_screenshot'])) {
                        $image_src = htmlspecialchars($account['primary_screenshot']);
                    }
                ?>
                <div class="card-image-container">
                    <img src="<?php echo $image_src; ?>" alt="Screenshot">
                </div>
                
                <div class="card-content">
                    <div class="card-username"><?php echo htmlspecialchars($account['username']); ?></div>
                    <div class="card-price">Price: USD <?php echo number_format($account['price_mm'], 0, ',', '.'); ?></div>
                    
                    <div class="card-actions">
                        <a href="edit_account.php?id=<?php echo $account['id']; ?>" class="btn-edit">Edit</a>
                        <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this account?');">
                            <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
                            <button type="submit" name="delete_account" class="btn-delete">Delete</button>
                        </form>
                    </div>
                </div>
                <a href="admin_preview.php?id=<?php echo $account['id']; ?>" class="btn-view">Preview</a>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleSort() {
            const currentSort = '<?php echo $sort; ?>';
            let newSort = 'newest';
            if (currentSort === 'newest') newSort = 'price_desc';
            else if (currentSort === 'price_desc') newSort = 'price_asc';
            window.location.href = '?sort=' + newSort + '<?php echo !empty($search) ? "&search=" . urlencode($search) : ""; ?>';
        }
    </script>
</body>
</html>