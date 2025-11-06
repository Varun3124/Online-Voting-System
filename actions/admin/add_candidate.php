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
    $name = validate_input($_POST['name'] ?? '');
    $party = validate_input($_POST['party'] ?? '');
    $election_id = $_POST['election_id'] ?? null;
    
    if (empty($name) || empty($party) || !$election_id) {
        throw new Exception('Required fields cannot be empty');
    }

    // Handle photo upload if provided
    $photo_path = null;
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
    }

    $stmt = $conn->prepare("
        INSERT INTO candidates (name, party, photo, election_id) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('sssi', $name, $party, $photo_path, $election_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Candidate added successfully'
        ]);
    } else {
        // Delete uploaded photo if database insert fails
        if ($photo_path && file_exists($target_dir . $photo_path)) {
            unlink($target_dir . $photo_path);
        }
        throw new Exception('Failed to add candidate');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>