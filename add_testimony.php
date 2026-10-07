<?php
require_once 'config.php';
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_path = null;

    // Handle Screenshot Upload
    if (isset($_FILES['testimony_img']) && $_FILES['testimony_img']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/testimonies/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['testimony_img']['name'], PATHINFO_EXTENSION));
        $new_filename = 'testimony_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $destination = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['testimony_img']['tmp_name'], $destination)) {
            $image_path = $destination;
        }
    }

    if (!$image_path) {
        $error = 'Please upload a proof screenshot to save the testimony.';
    } else {
        // We only insert into columns that actually exist: 'name' and 'tss_link'
        $placeholder_name = "Buyer Proof"; 
        
        $stmt = $conn->prepare("INSERT INTO Testimony (name, tss_link) VALUES (?, ?)");
        $stmt->bind_param("ss", $placeholder_name, $image_path);
        
        if ($stmt->execute()) {
            header('Location: admin_testimonies.php?msg=added');
            exit();
        } else {
            $error = 'Database Error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Testimony - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #4a5072; color: white; font-family: sans-serif; margin: 0; }
        .form-container { background: #5d678f; padding: 2.5rem; border-radius: 15px; max-width: 450px; margin: 5rem auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 12px; font-weight: bold; color: #7fff00; font-size: 1.1rem; }
        .btn-submit { background: #7fff00; color: #4a5072; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 1rem; font-size: 1.1rem; }
        .preview-container { margin-top: 15px; text-align: center; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 10px; min-height: 150px; display: flex; align-items: center; justify-content: center; border: 2px dashed rgba(255,255,255,0.1); }
        #output-image { max-width: 100%; border-radius: 5px; display: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 style="text-align: center; margin-top: 0; font-family: 'Shrikhand', cursive;">Upload Proof</h2>
        
        <?php if ($error): ?>
            <div style="background: #ff4757; padding: 12px; border-radius: 5px; margin-bottom: 1.5rem; text-align: center;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Proof Screenshot</label>
                <input type="file" name="testimony_img" accept="image/*" onchange="previewImage(this)" required>
                <div class="preview-container">
                    <p id="preview-text" style="font-size: 0.8rem; color: #ccc;">No image selected</p>
                    <img src="" id="output-image">
                </div>
            </div>

            <button type="submit" class="btn-submit">Upload Testimony</button>
            <a href="admin_testimonies.php" style="display:block; text-align:center; margin-top:1.5rem; color:#ccc; text-decoration:none; font-size: 0.9rem;">← Cancel</a>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('output-image');
            const text = document.getElementById('preview-text');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    text.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>