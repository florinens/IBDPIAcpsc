<?php
// Database configuration
define('DB_HOST', 'localhost:8889');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'lordsmob');

// Create connection
$conn = new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 8889);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session
session_start();
?>