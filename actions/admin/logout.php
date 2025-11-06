<?php
require_once('../common/config.php');

// Clear admin session data
unset($_SESSION[ADMIN_SESSION_KEY]);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_login_time']);
unset($_SESSION['admin_token']);

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../../admin_login.php');
exit;
?>