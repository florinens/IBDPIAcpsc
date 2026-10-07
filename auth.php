<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sign up function
function signup($username, $email, $password) {
    global $conn;
    
    $username = trim($username);
    $email = trim($email);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM User WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'This email is already registered'];
    }
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM User WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'This username is already taken'];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO User (username, email, password, role_id) VALUES (?, ?, ?, 2)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Account created successfully'];
    } else {
        return ['success' => false, 'message' => 'Error creating account'];
    }
}

// Login function
function login($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT u.id, u.username, u.email, u.password, r.role_name 
                           FROM User u 
                           JOIN Role r ON u.role_id = r.id 
                           WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role_name'];
        
        return ['success' => true, 'role' => $user['role_name']];
    } else {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Logout function
function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>