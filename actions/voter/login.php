<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');
require_once('../common/utils.php');

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input
    $aadhar = validate_input($_POST['aadhar'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($aadhar) || empty($password)) {
        throw new Exception('Aadhar and password are required');
    }

    // Check rate limiting
    $ip = get_client_ip();
    if (!check_rate_limit($ip, 'voter_login')) {
        throw new Exception('Too many login attempts. Please try again later.');
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM voters WHERE aadhar_number = ? AND status = 'approved'");
    $stmt->bind_param("s", $aadhar);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $voter = $result->fetch_assoc();
        
        if (password_verify($password, $voter['password'])) {
            // Start session and store voter info
            $_SESSION[VOTER_SESSION_KEY] = $voter['id'];
            $_SESSION['voter_name'] = $voter['name'];
            $_SESSION['voter_login_time'] = time();
            $_SESSION['voter_token'] = generate_token();

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'redirect' => '../voter/dashboard.php'
            ]);
        } else {
            throw new Exception('Invalid credentials');
        }
    } else {
        throw new Exception('Invalid credentials or account not approved');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>