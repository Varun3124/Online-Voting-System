<?php
require_once('config.php');

/**
 * Validate and sanitize input
 */
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['REMOTE_ADDR'];
    return $ip;
}

/**
 * Check rate limiting
 */
function check_rate_limit($ip, $action) {
    $file = sys_get_temp_dir() . "/rate_limit_{$action}_{$ip}.txt";
    
    // Get current attempts
    $attempts = file_exists($file) ? file_get_contents($file) : '{}';
    $attempts = json_decode($attempts, true);
    
    // Clean old attempts
    $now = time();
    $attempts = array_filter($attempts, function($timestamp) use ($now) {
        return $now - $timestamp < (RATE_LIMIT_MINUTES * 60);
    });
    
    // Add new attempt
    $attempts[] = $now;
    
    // Save attempts
    file_put_contents($file, json_encode($attempts));
    
    // Check if limit exceeded
    return count($attempts) <= RATE_LIMIT_ATTEMPTS;
}

/**
 * Generate secure random token
 */
function generate_token($length = TOKEN_LENGTH) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate file upload
 */
function validate_file_upload($file, $allowed_types = ALLOWED_IMAGE_TYPES, $max_size = MAX_FILE_SIZE) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return $errors;
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = 'File is too large';
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = 'Invalid file type';
    }
    
    return $errors;
}

/**
 * Save uploaded file
 */
function save_uploaded_file($file, $new_filename) {
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = UPLOAD_PATH . $new_filename . '.' . $extension;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $new_filename . '.' . $extension;
    }
    
    return false;
}

/**
 * Check if user is logged in as admin
 */
function is_admin_logged_in() {
    return isset($_SESSION[ADMIN_SESSION_KEY]);
}

/**
 * Check if user is logged in as voter
 */
function is_voter_logged_in() {
    return isset($_SESSION[VOTER_SESSION_KEY]);
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}
?>