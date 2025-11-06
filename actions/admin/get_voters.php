<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, name, aadhar_number, phone, email, status, created_at 
        FROM voters 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $voters = [];
    while ($row = $result->fetch_assoc()) {
        $voters[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'voters' => $voters
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve voters'
    ]);
}
?>