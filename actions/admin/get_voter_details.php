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
    $voter_id = $_GET['id'] ?? null;
    if (!$voter_id) {
        throw new Exception('Voter ID is required');
    }

    $stmt = $conn->prepare("
        SELECT id, name, aadhar_number, dob, phone, email, photo, status, created_at 
        FROM voters 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($voter = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'voter' => $voter
        ]);
    } else {
        throw new Exception('Voter not found');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>