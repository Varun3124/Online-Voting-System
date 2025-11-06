<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify admin session
verify_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $constituency = mysqli_real_escape_string($conn, $_POST['constituency']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $admin_id = $_SESSION['admin_id'];

    // Validate inputs
    if (empty($title) || empty($start_date) || empty($end_date)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
        exit;
    }

    // Create election
    $query = "INSERT INTO elections (title, description, constituency, start_date, end_date, created_by) 
              VALUES ('$title', '$description', '$constituency', '$start_date', '$end_date', $admin_id)";

    if (mysqli_query($conn, $query)) {
        $election_id = mysqli_insert_id($conn);
        echo json_encode(['status' => 'success', 'message' => 'Election created successfully', 'election_id' => $election_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error creating election: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>