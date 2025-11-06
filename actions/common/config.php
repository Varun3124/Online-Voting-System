<?php
// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('ADMIN_SESSION_KEY', 'admin_id');
define('VOTER_SESSION_KEY', 'voter_id');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
?>