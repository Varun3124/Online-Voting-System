<?php
require_once('config.php');

function verify_admin_session() {
    if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
}

function verify_voter_session() {
    if (!isset($_SESSION[VOTER_SESSION_KEY])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
}

function generate_session_token() {
    return bin2hex(random_bytes(32));
}

function verify_session_token($token) {
    return isset($_SESSION['session_token']) && hash_equals($_SESSION['session_token'], $token);
}

// Rate limiting check
function check_rate_limit($ip, $action, $max_attempts = 5, $timeframe = 300) {
    global $conn;
    
    $ip = mysqli_real_escape_string($conn, $ip);
    $action = mysqli_real_escape_string($conn, $action);
    
    // Clean old entries
    mysqli_query($conn, "DELETE FROM rate_limits WHERE timestamp < DATE_SUB(NOW(), INTERVAL $timeframe SECOND)");
    
    // Count attempts
    $query = "SELECT COUNT(*) as attempts FROM rate_limits 
              WHERE ip = '$ip' AND action = '$action' 
              AND timestamp > DATE_SUB(NOW(), INTERVAL $timeframe SECOND)";
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['attempts'] >= $max_attempts) {
        return false;
    }
    
    // Log attempt
    mysqli_query($conn, "INSERT INTO rate_limits (ip, action) VALUES ('$ip', '$action')");
    return true;
}
?>