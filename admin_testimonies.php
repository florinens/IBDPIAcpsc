<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

// Handle Testimony Deletion using your column name 'id'
if (isset($_POST['delete_testimony'])) {
    $t_id = (int)$_POST['testimony_id'];
    $conn->query("DELETE FROM Testimony WHERE id = $t_id");
    header('Location: admin_testimonies.php');
    exit();
}

// Fetch all testimonies using your column names
$result = $conn->query("SELECT * FROM Testimony ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonies - Admin</title>
    <link rel="stylesheet" href="styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Shrikhand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #4a5072; margin: 0; padding: 0; }
        
        .admin-grid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .testimony-card {
            background: #9bb4c9;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .testimony-card:hover { transform: translateY(-5px); }

        .img-container {
            width: 100%;
            height: 250px;
            background: #2a2d3e;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: contain; 
        }

        .card-body {
            padding: 1.2rem;
            color: #222;
            background: white;
            flex-grow: 1;
        }

        .buyer-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #4a5072;
            margin-bottom: 5px;
            display: block;
        }

        .buyer-msg {
            font-size: 0.9rem;
            color: #555;
            line-height: 1.4;
        }

        .add-card-trigger {
            border: 3px dashed rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 350px;
            cursor: pointer;
            transition: 0.3s;
            border-radius: 15px;
        }

        .add-card-trigger:hover {
            border-color: #7fff00;
            background: rgba(127, 255, 0, 0.05);
        }

        .btn-delete {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-delete:hover { background: #cc0000; }
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

    <div class="testimonies-hero-section">
        <h1 class="welcome-text" style="text-align: center; color: white; font-family: 'Shrikhand'; margin-top: 20px;">Manage Testimonies</h1>
    </div>

    <div class="admin-grid">
        <div class="add-card-trigger" onclick="window.location.href='add_testimony.php'">
            <div style="text-align:center; color:white;">
                <i class="fa-solid fa-plus-circle" style="font-size: 3rem; margin-bottom: 10px;"></i>
                <div style="font-weight:bold;">Add New Testimony</div>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="testimony-card">
                    <div class="img-container">
                        <?php $img = !empty($row['tss_link']) ? $row['tss_link'] : 'images/placeholder.png'; ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Proof Screenshot">
                    </div>
                    <form method="POST" onsubmit="return confirm('Delete this review permanently?');">
                        <input type="hidden" name="testimony_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_testimony" class="btn-delete">
                            <i class="fa-solid fa-trash"></i> DELETE
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: white; text-align: center; grid-column: 1/-1;">No testimonies found in the database.</p>
        <?php endif; ?>
    </div>
</body>
</html>