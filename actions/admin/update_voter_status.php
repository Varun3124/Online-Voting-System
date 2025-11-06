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
    $data = json_decode(file_get_contents('php://input'), true);
    $voter_id = $data['voter_id'] ?? null;
    $status = $data['status'] ?? null;

    if (!$voter_id || !$status || !in_array($status, ['approved', 'rejected'])) {
        throw new Exception('Invalid request parameters');
    }

    $stmt = $conn->prepare("UPDATE voters SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $voter_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => "Voter has been $status"
        ]);
    } else {
        throw new Exception('Failed to update voter status');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>