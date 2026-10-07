<?php
require_once 'config.php';
require_once 'auth.php';

// Set timezone to Jakarta to match your DB
date_default_timezone_set('Asia/Jakarta'); 

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

// 1. Stats Queries
$total_accounts = $conn->query("SELECT COUNT(*) as count FROM Account")->fetch_assoc()['count'];
$available_accounts = $conn->query("SELECT COUNT(*) as count FROM Account WHERE status = 'available'")->fetch_assoc()['count'];
$sold_accounts = $conn->query("SELECT COUNT(*) as count FROM Account WHERE status = 'sold'")->fetch_assoc()['count'];
$pending_interests = $conn->query("SELECT COUNT(*) as count FROM User_Interest WHERE status = 'pending'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(price_mm) as revenue FROM Account WHERE status = 'sold'")->fetch_assoc()['revenue'] ?? 0;

// 2. Monthly Interest Data (Logic to show empty months)
$m_labels = [];
$m_counts = [];

// Create labels for the last 5 months automatically
for ($i = 4; $i >= 0; $i--) {
    $monthName = date('M', strtotime("-$i months"));
    $m_labels[] = $monthName;
    $m_counts[$monthName] = 0; // Initialize with 0
}

$monthly_data = $conn->query("
    SELECT DATE_FORMAT(interest_time, '%b') AS month_name, COUNT(*) AS count 
    FROM User_Interest 
    WHERE interest_time >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
    GROUP BY month_name
");

while ($row = $monthly_data->fetch_assoc()) {
    if (isset($m_counts[$row['month_name']])) {
        $m_counts[$row['month_name']] = (int)$row['count'];
    }
}

// Convert back to simple array for JavaScript
$final_counts = array_values($m_counts);

// 3. Recent Activity
$recent_activity = $conn->query("
    SELECT ui.email, a.username as account_username, ui.interest_time 
    FROM User_Interest ui 
    JOIN Account a ON ui.account_id = a.id 
    ORDER BY ui.interest_time DESC 
    LIMIT 10
");

// 4. Popular Accounts List
$popular_list = $conn->query("
    SELECT a.username, COUNT(ui.id) as total 
    FROM Account a 
    LEFT JOIN User_Interest ui ON a.id = ui.account_id 
    GROUP BY a.id, a.username 
    ORDER BY total DESC LIMIT 5
");

function formatTimeAgo($timestamp) {
    if (!$timestamp) return "Recently";
    $seconds = time() - strtotime($timestamp);
    if ($seconds < 0) $seconds = abs($seconds); 
    $units = ['yr' => 31536000, 'mo' => 2592000, 'w' => 604800, 'd' => 86400, 'h' => 3600, 'm' => 60];
    foreach ($units as $label => $value) {
        if ($seconds >= $value) return floor($seconds / $value) . $label . " ago";
    }
    return "Just now";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #4a5072; font-family: sans-serif; margin: 0; color: white; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .dashboard-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; }
        .box { background: #6b7399; border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; }
        .stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 10px; text-align: center; }
        .activity-item { background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
        .ago-text { color: #b8c4d9; font-weight: bold; font-size: 0.8rem; }
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

    <div class="dashboard-container">
        <h1 style="font-family: 'Shrikhand'; font-size: 3.5rem;">Admin Dashboard</h1>

        <div class="stats-row">
            <div class="stat-card"><strong><?php echo $total_accounts; ?></strong><br>Total</div>
            <div class="stat-card"><strong><?php echo $available_accounts; ?></strong><br>Available</div>
            <div class="stat-card"><strong><?php echo $sold_accounts; ?></strong><br>Sold</div>
            <div class="stat-card"><strong>$<?php echo number_format($total_revenue); ?></strong><br>Revenue</div>
            <div class="stat-card"><strong><?php echo $pending_interests; ?></strong><br>Pending</div>
        </div>

        <div class="dashboard-grid">
            <div class="left-col">
                <div class="box">
                    <h3 style="margin-top:0">Monthly Interest Levels</h3>
                    <canvas id="monthlyChart"></canvas>
                </div>

                <div class="box">
                    <h3 style="margin-top:0">Most Popular Accounts</h3>
                    <?php while($p = $popular_list->fetch_assoc()): ?>
                        <div style="display:flex; justify-content:space-between; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span><?php echo htmlspecialchars($p['username']); ?></span>
                            <span style="color:#7fff00; font-weight:bold;"><?php echo $p['total']; ?> interests</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="right-col">
                <div class="box">
                    <h3 style="margin-top:0">Recent Activity</h3>
                    <?php while ($row = $recent_activity->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div>
                                <small style="display:block; color:#b8c4d9;"><?php echo htmlspecialchars($row['email']); ?></small>
                                <strong><?php echo htmlspecialchars($row['account_username']); ?></strong>
                            </div>
                            <div class="ago-text"><?php echo formatTimeAgo($row['interest_time']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($m_labels); ?>,
                datasets: [{
                    label: 'Interests',
                    data: <?php echo json_encode($final_counts); ?>,
                    backgroundColor: '#7fff00',
                    borderRadius: 5
                }]
            },
            options: {
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { color: 'white', stepSize: 1 }, 
                        grid: { color: 'rgba(255,255,255,0.1)' } 
                    },
                    x: { ticks: { color: 'white' }, grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>