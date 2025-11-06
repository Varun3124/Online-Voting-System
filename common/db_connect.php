<?php
require_once('config.php');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to handle Unicode correctly
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log error and show generic message
    error_log($e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>