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
    $title = validate_input($_POST['title'] ?? '');
    $description = validate_input($_POST['description'] ?? '');
    $constituency = validate_input($_POST['constituency'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (empty($title) || empty($start_date) || empty($end_date)) {
        throw new Exception('Required fields cannot be empty');
    }

    if (strtotime($end_date) <= strtotime($start_date)) {
        throw new Exception('End date must be after start date');
    }

    $stmt = $conn->prepare("
        INSERT INTO elections (title, description, constituency, start_date, end_date, created_by) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $admin_id = $_SESSION[ADMIN_SESSION_KEY];
    $stmt->bind_param('sssssi', $title, $description, $constituency, $start_date, $end_date, $admin_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Election created successfully'
        ]);
    } else {
        throw new Exception('Failed to create election');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>