<?php
/**
 * Common utility functions for the voting system
 */

/**
 * Sanitize input data
 */
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}



/**
 * Check rate limiting
 */
function check_rate_limit($ip, $action, $limit = 5, $window = 300) {
    global $conn;
    
    // Delete old entries
    $stmt = $conn->prepare("DELETE FROM rate_limits WHERE action = ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("si", $action, $window);
    $stmt->execute();
    
    // Count recent attempts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE ip = ? AND action = ? AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->bind_param("ssi", $ip, $action, $window);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count >= $limit) {
        return false;
    }
    
    // Log this attempt
    $stmt = $conn->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $action);
    $stmt->execute();
    
    return true;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Generate secure token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Verify voter age
 */
function verify_voter_age($dob, $min_age = 18) {
    $dob_date = new DateTime($dob);
    $now = new DateTime();
    $age = $now->diff($dob_date)->y;
    return $age >= $min_age;
}
?>