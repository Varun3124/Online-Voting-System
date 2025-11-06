<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');
require_once('../common/utils.php');

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $candidate_id = $_POST['candidate_id'] ?? null;
    $name = validate_input($_POST['name'] ?? '');
    $party = validate_input($_POST['party'] ?? '');
    $election_id = $_POST['election_id'] ?? null;

    if (!$candidate_id || empty($name) || empty($party) || !$election_id) {
        throw new Exception('Required fields cannot be empty');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get current photo if it exists
    $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
    $stmt->bind_param('i', $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_photo = $result->fetch_assoc()['photo'];

    // Handle photo upload if provided
    $photo_path = $current_photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        // Validate file type
        $file_info = getimagesize($_FILES['photo']['tmp_name']);
        if ($file_info === false || !in_array($file_info['mime'], ['image/jpeg', 'image/png'])) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed');
        }

        // Create directory if it doesn't exist
        $target_dir = "../../uploads/candidate_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename
        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_path = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
        $full_path = $target_dir . $photo_path;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $full_path)) {
            throw new Exception('Failed to upload photo');
        }

        // Delete old photo if exists
        if ($current_photo && file_exists($target_dir . $current_photo)) {
            unlink($target_dir . $current_photo);
        }
    }

    // Update candidate
    $stmt = $conn->prepare("
        UPDATE candidates 
        SET name = ?, party = ?, photo = ?, election_id = ?
        WHERE id = ?
    ");
    $stmt->bind_param('sssii', $name, $party, $photo_path, $election_id, $candidate_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Candidate updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update candidate');
    }

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    // Delete newly uploaded photo if exists and update failed
    if (isset($full_path) && file_exists($full_path)) {
        unlink($full_path);
    }
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>