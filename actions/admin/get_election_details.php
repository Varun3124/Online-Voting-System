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
    $election_id = $_GET['id'] ?? null;
    if (!$election_id) {
        throw new Exception('Election ID is required');
    }

    $stmt = $conn->prepare("
        SELECT id, title, description, constituency, start_date, end_date, status
        FROM elections 
        WHERE id = ?
    ");
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($election = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'election' => $election
        ]);
    } else {
        throw new Exception('Election not found');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>