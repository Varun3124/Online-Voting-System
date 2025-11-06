<?php
require_once('config.php');

// Create rate limiting table if not exists
$create_rate_limits = "CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip` varchar(45) NOT NULL,
    `action` varchar(50) NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ip_action_idx` (`ip`, `action`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(!mysqli_query($conn, $create_rate_limits)) {
    die("Error creating rate limits table: " . mysqli_error($conn));
}

// Function to clean old rate limiting entries
function clean_rate_limits() {
    global $conn;
    mysqli_query($conn, "DELETE FROM rate_limits WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 DAY)");
}

// Clean old entries periodically
if (rand(1, 100) <= 5) { // 5% chance to clean on each request
    clean_rate_limits();
}

// Additional helper functions
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

function validate_aadhar($aadhar) {
    return preg_match("/^\d{12}$/", $aadhar);
}

function validate_phone($phone) {
    return preg_match("/^\d{10}$/", $phone);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function log_activity($user_id, $action, $details = '') {
    global $conn;
    $user_id = (int)$user_id;
    $action = sanitize_input($action);
    $details = sanitize_input($details);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    mysqli_query($conn, "INSERT INTO activity_log (user_id, action, details, ip_address) 
                        VALUES ('$user_id', '$action', '$details', '$ip')");
}
?>