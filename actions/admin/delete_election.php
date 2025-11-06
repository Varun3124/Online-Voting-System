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
    $election_id = $data['election_id'] ?? null;

    if (!$election_id) {
        throw new Exception('Election ID is required');
    }

    // Check if election has any votes
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes WHERE election_id = ?");
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vote_count = $result->fetch_assoc()['count'];

    if ($vote_count > 0) {
        throw new Exception('Cannot delete election with existing votes');
    }

    $conn->begin_transaction();

    // Delete candidates first (due to foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM candidates WHERE election_id = ?");
    $stmt->bind_param('i', $election_id);
    $stmt->execute();

    // Delete election
    $stmt = $conn->prepare("DELETE FROM elections WHERE id = ?");
    $stmt->bind_param('i', $election_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Election deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete election');
    }

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>