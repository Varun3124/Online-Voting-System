<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'voting');

// Session keys
define('ADMIN_SESSION_KEY', 'admin_id');
define('VOTER_SESSION_KEY', 'voter_id');

// Security settings
define('RATE_LIMIT_ATTEMPTS', 5);
define('RATE_LIMIT_MINUTES', 15);
define('TOKEN_LENGTH', 32);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Error reporting in development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');
?>