<?php
require_once 'config.php';

// Get filter and sort parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// FIXED QUERY: Join the Screenshot table to actually get the 'ss_link'
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
    case 'price_asc': $query .= " ORDER BY A.price_mm ASC"; break;
    case 'price_desc': $query .= " ORDER BY A.price_mm DESC"; break;
    case 'newest': default: $query .= " ORDER BY A.id DESC"; break;
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
    <title>Browse Accounts - Salju Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
    <style>
        body { background: #4a5072; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .accounts-hero { position: relative; background: url('images/banner.jpg') no-repeat center/cover; height: 300px; display: flex; align-items: center; justify-content: center; }
        .accounts-title { font-family: 'Shrikhand', cursive; font-size: 4rem; color: #fff7e6; text-shadow: 3px 3px 0px rgba(0, 0, 0, 0.7); }
        
        .search-filter-section { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .search-bar { background: white; border-radius: 25px; padding: 0.8rem 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .search-bar input { border: none; outline: none; flex: 1; font-size: 1rem; }
        
        .accounts-grid { max-width: 1200px; margin: 0 auto; padding: 2rem 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .account-card { background: #9bb4c9; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .account-card:hover { transform: translateY(-5px); }
        
        .card-image-container { height: 200px; background: #667eea; overflow: hidden; }
        .card-image-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .card-content { padding: 1.5rem; color: white; }
        .card-username { font-size: 1.4rem; font-weight: bold; margin-bottom: 0.5rem; }
        .card-price { font-size: 1.2rem; color: #fff7e6; font-weight: bold; margin-bottom: 1rem; }
        
        .btn-view { display: block; background: #667eea; color: white; padding: 0.8rem; border-radius: 8px; text-decoration: none; text-align: center; font-weight: bold; transition: background 0.2s; }
        .btn-view:hover { background: #5a6fd6; }
        
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
                    <input type="text" name="search" placeholder="Search by username or description..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if(!empty($search)): ?>
                        <a href="accounts.php" style="text-decoration:none; color:#666;">✕</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="accounts-grid">
        <?php if ($accounts->num_rows === 0): ?>
            <p style="color: white; text-align: center; grid-column: 1/-1;">No accounts found matching your criteria.</p>
        <?php endif; ?>

        <?php while ($account = $accounts->fetch_assoc()): ?>
            <div class="account-card">
                <?php 
                    // Use a placeholder if no screenshot exists or file is missing
                    $image_src = 'images/placeholder.png';
                    if (!empty($account['primary_screenshot']) && file_exists($account['primary_screenshot'])) {
                        $image_src = htmlspecialchars($account['primary_screenshot']);
                    }
                ?>
                <div class="card-image-container">
                    <img src="<?php echo $image_src; ?>" alt="Account Preview">
                </div>
                
                <div class="card-content">
                    <div class="card-username"><?php echo htmlspecialchars($account['username']); ?></div>
                    <div class="card-price">
                        $<?php echo number_format($account['price_mm'], 0); ?>
                    </div>
                    <p style="font-size: 0.85rem; height: 40px; overflow: hidden; opacity: 0.9;">
                        <?php echo htmlspecialchars(substr($account['description'], 0, 80)) . '...'; ?>
                    </p>
                    <a href="account_details.php?id=<?php echo $account['id']; ?>" class="btn-view">View Full Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleSort() {
            const params = new URLSearchParams(window.location.search);
            const currentSort = params.get('sort') || 'newest';
            let newSort = 'newest';
            
            if (currentSort === 'newest') newSort = 'price_desc';
            else if (currentSort === 'price_desc') newSort = 'price_asc';
            else newSort = 'newest';
            
            params.set('sort', newSort);
            window.location.href = '?' + params.toString();
        }
    </script>
</body>
</html>