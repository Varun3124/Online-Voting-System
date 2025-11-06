<?php
require_once('../common/db_connect.php');
require_once('../common/utils.php');
require_once('../common/config.php');

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $username = validate_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }

    // Check rate limiting
    $ip = get_client_ip();
    if (!check_rate_limit($ip, 'admin_login')) {
        throw new Exception('Too many login attempts. Please try again later.');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            // Store admin info in session
            $_SESSION[ADMIN_SESSION_KEY] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_token'] = generate_token();

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'redirect' => '../../admin/dashboard.php'
            ]);
        } else {
            throw new Exception('Invalid credentials');
        }
    } else {
        throw new Exception('Invalid credentials');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>