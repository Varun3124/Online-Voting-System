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
    $candidate_id = $_GET['id'] ?? null;
    if (!$candidate_id) {
        throw new Exception('Candidate ID is required');
    }

    $stmt = $conn->prepare("
        SELECT c.*, e.title as election_title 
        FROM candidates c
        JOIN elections e ON c.election_id = e.id
        WHERE c.id = ?
    ");
    $stmt->bind_param('i', $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($candidate = $result->fetch_assoc()) {
        echo json_encode([
            'status' => 'success',
            'candidate' => $candidate
        ]);
    } else {
        throw new Exception('Candidate not found');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>