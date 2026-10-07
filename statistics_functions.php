<?php
require_once 'config.php';

// Get total statistics
function getTotalStatistics() {
    global $conn;
    
    $stats = [];
    
    // Total accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM Account");
    $stats['total_accounts'] = $result->fetch_assoc()['count'];
    
    // Available accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM Account WHERE status = 'available'");
    $stats['available_accounts'] = $result->fetch_assoc()['count'];
    
    // Sold accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM Account WHERE status = 'sold'");
    $stats['sold_accounts'] = $result->fetch_assoc()['count'];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM User");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Pending interests
    $result = $conn->query("SELECT COUNT(*) as count FROM User_Interest WHERE status = 'pending'");
    $stats['pending_interests'] = $result->fetch_assoc()['count'];
    
    // Completed interests
    $result = $conn->query("SELECT COUNT(*) as count FROM User_Interest WHERE status = 'complete'");
    $stats['completed_interests'] = $result->fetch_assoc()['count'];
    
    // Total revenue (from sold accounts)
    $result = $conn->query("SELECT SUM(price_mm) as revenue FROM Account WHERE status = 'sold'");
    $stats['total_revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
    
    // Conversion rate
    $total_interests = $stats['pending_interests'] + $stats['completed_interests'];
    $stats['conversion_rate'] = $total_interests > 0 ? 
        round(($stats['completed_interests'] / $total_interests) * 100, 2) : 0;
    
    return $stats;
}

// Get account popularity data
function getAccountPopularity($limit = 10) {
    global $conn;
    
    $query = "SELECT a.id, a.username, 
              COUNT(DISTINCT ui.id) as interest_count,
              a.price_mm,
              a.status
              FROM Account a 
              LEFT JOIN User_Interest ui ON a.id = ui.account_id 
              GROUP BY a.id 
              ORDER BY interest_count DESC
              LIMIT $limit";
    
    return $conn->query($query);
}

// Get most viewed accounts
function getMostViewedAccounts($limit = 10) {
    global $conn;
    
    $query = "SELECT id, username, description, price_mm, status 
              FROM Account 
              ORDER BY id DESC 
              LIMIT $limit";
    
    return $conn->query($query);
}

// Get recent activity
function getRecentActivity($limit = 10) {
    global $conn;
    
    $query = "SELECT 'interest' as type, ui.email, a.username as account_username, 
              ui.interest_date as activity_date
              FROM User_Interest ui
              JOIN Account a ON ui.account_id = a.id
              ORDER BY activity_date DESC
              LIMIT $limit";
    
    return $conn->query($query);
}

// Get time-based statistics (last 7 days, 30 days, etc.)
function getTimeBasedStats($days = 7) {
    global $conn;
    
    $stats = [];
    
    // New users in time period
    $result = $conn->query("SELECT COUNT(*) as count FROM User 
                           WHERE createdAt >= DATE_SUB(NOW(), INTERVAL $days DAY)");
    $stats['new_users'] = $result->fetch_assoc()['count'];
    
    // New interests in time period
    $result = $conn->query("SELECT COUNT(*) as count FROM User_Interest 
                           WHERE interest_date >= DATE_SUB(NOW(), INTERVAL $days DAY)");
    $stats['new_interests'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Get daily statistics for charts
function getDailyStats($days = 30) {
    global $conn;
    
    $query = "SELECT 
              DATE(interest_date) as date,
              COUNT(*) as interest_count
              FROM User_Interest
              WHERE interest_date >= DATE_SUB(NOW(), INTERVAL $days DAY)
              GROUP BY DATE(interest_date)
              ORDER BY date ASC";
    
    return $conn->query($query);
}

// Get interest conversion funnel
function getConversionFunnel() {
    global $conn;
    
    $funnel = [];
    
    // Total accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM Account");
    $funnel['accounts'] = $result->fetch_assoc()['count'];
    
    // Total interests expressed
    $result = $conn->query("SELECT COUNT(*) as count FROM User_Interest");
    $funnel['interests'] = $result->fetch_assoc()['count'];
    
    // Total completed/converted
    $result = $conn->query("SELECT COUNT(*) as count FROM User_Interest WHERE status = 'complete'");
    $funnel['conversions'] = $result->fetch_assoc()['count'];
    
    // Calculate percentages
    $funnel['account_to_interest_rate'] = $funnel['accounts'] > 0 ? 
        round(($funnel['interests'] / $funnel['accounts']) * 100, 2) : 0;
    
    $funnel['interest_to_conversion_rate'] = $funnel['interests'] > 0 ? 
        round(($funnel['conversions'] / $funnel['interests']) * 100, 2) : 0;
    
    return $funnel;
}
?>