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
        SELECT c.*, e.title as election_title 
        FROM candidates c
        JOIN elections e ON c.election_id = e.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'candidates' => $candidates
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve candidates'
    ]);
}
?>