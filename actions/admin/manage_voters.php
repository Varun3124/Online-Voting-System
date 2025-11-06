<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify admin session
verify_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch($action) {
        case 'approve':
        case 'reject':
            $voter_id = mysqli_real_escape_string($conn, $_POST['voter_id']);
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            $query = "UPDATE voters SET status = '$status' WHERE id = '$voter_id'";
            
            if(mysqli_query($conn, $query)) {
                echo json_encode(['status' => 'success', 'message' => "Voter $status successfully"]);
            } else {
                echo json_encode(['status' => 'error', 'message' => "Error updating voter status"]);
            }
            break;

        case 'list':
            $status_filter = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
            $search = mysqli_real_escape_string($conn, $_POST['search'] ?? '');
            
            $query = "SELECT * FROM voters WHERE 1=1";
            if($status_filter) {
                $query .= " AND status = '$status_filter'";
            }
            if($search) {
                $query .= " AND (name LIKE '%$search%' OR aadhar_number LIKE '%$search%')";
            }
            $query .= " ORDER BY created_at DESC";
            
            $result = mysqli_query($conn, $query);
            $voters = [];
            
            while($row = mysqli_fetch_assoc($result)) {
                // Remove sensitive information
                unset($row['password']);
                $voters[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'voters' => $voters]);
            break;

        case 'delete':
            $voter_id = mysqli_real_escape_string($conn, $_POST['voter_id']);
            
            // Check if voter has already voted
            $check_query = "SELECT id FROM votes WHERE voter_id = '$voter_id' LIMIT 1";
            $check_result = mysqli_query($conn, $check_query);
            
            if(mysqli_num_rows($check_result) > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete voter who has already voted']);
                exit;
            }
            
            $query = "DELETE FROM voters WHERE id = '$voter_id'";
            
            if(mysqli_query($conn, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Voter deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting voter']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>