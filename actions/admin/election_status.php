<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify admin session
verify_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $election_id = mysqli_real_escape_string($conn, $_POST['election_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']); // 'active' or 'completed'

    // Validate status
    if (!in_array($status, ['active', 'completed', 'draft'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid election status']);
        exit;
    }

    // Check if election exists
    $check_query = "SELECT * FROM elections WHERE id = '$election_id'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Election not found']);
        exit;
    }

    $election = mysqli_fetch_assoc($result);

    // Additional validations based on status
    if ($status === 'active') {
        // Check if there's already an active election
        $active_check = "SELECT id FROM elections WHERE status = 'active' AND id != '$election_id'";
        $active_result = mysqli_query($conn, $active_check);
        
        if (mysqli_num_rows($active_result) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Another election is already active']);
            exit;
        }

        // Check if election has candidates
        $candidate_check = "SELECT id FROM candidates WHERE election_id = '$election_id' LIMIT 1";
        $candidate_result = mysqli_query($conn, $candidate_check);
        
        if (mysqli_num_rows($candidate_result) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot start election without candidates']);
            exit;
        }
    }

    // Update election status
    $update_query = "UPDATE elections SET status = '$status' WHERE id = '$election_id'";
    
    if (mysqli_query($conn, $update_query)) {
        // If completing the election, calculate and store final results
        if ($status === 'completed') {
            $votes_query = "SELECT candidate_id, COUNT(*) as vote_count 
                          FROM votes 
                          WHERE election_id = '$election_id' 
                          GROUP BY candidate_id";
            
            $votes_result = mysqli_query($conn, $votes_query);
            
            while ($row = mysqli_fetch_assoc($votes_result)) {
                $candidate_id = $row['candidate_id'];
                $votes = $row['vote_count'];
                mysqli_query($conn, "UPDATE candidates SET votes = '$votes' 
                                   WHERE id = '$candidate_id' AND election_id = '$election_id'");
            }
        }

        echo json_encode([
            'status' => 'success', 
            'message' => "Election marked as $status successfully"
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error updating election status: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>