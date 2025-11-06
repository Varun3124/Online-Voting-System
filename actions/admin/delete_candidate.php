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
    $candidate_id = $data['candidate_id'] ?? null;

    if (!$candidate_id) {
        throw new Exception('Candidate ID is required');
    }

    // Get candidate photo
    $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
    $stmt->bind_param('i', $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $photo = $result->fetch_assoc()['photo'];

    // Check if candidate has votes
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM votes WHERE candidate_id = ?");
    $stmt->bind_param('i', $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vote_count = $result->fetch_assoc()['count'];

    if ($vote_count > 0) {
        throw new Exception('Cannot delete candidate with existing votes');
    }

    // Delete candidate
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param('i', $candidate_id);
    
    if ($stmt->execute()) {
        // Delete photo if exists
        if ($photo) {
            $photo_path = "../../uploads/candidate_photos/" . $photo;
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Candidate deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete candidate');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>