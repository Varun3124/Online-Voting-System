<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify voter session
verify_voter_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = $_SESSION[VOTER_SESSION_KEY];
    $election_id = mysqli_real_escape_string($conn, $_POST['election_id']);
    $candidate_id = mysqli_real_escape_string($conn, $_POST['candidate_id']);
    $verification_token = mysqli_real_escape_string($conn, $_POST['verification_token']);

    // Verify if election is active
    $election_query = "SELECT * FROM elections WHERE id = '$election_id' AND status = 'active'";
    $election_result = mysqli_query($conn, $election_query);
    
    if (mysqli_num_rows($election_result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or inactive election']);
        exit;
    }

    // Check if voter has already voted in this election
    $vote_check = "SELECT id FROM votes WHERE voter_id = '$voter_id' AND election_id = '$election_id'";
    $vote_result = mysqli_query($conn, $vote_check);
    
    if (mysqli_num_rows($vote_result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already voted in this election']);
        exit;
    }

    // Verify candidate is valid for this election
    $candidate_check = "SELECT id FROM candidates WHERE id = '$candidate_id' AND election_id = '$election_id'";
    $candidate_result = mysqli_query($conn, $candidate_check);
    
    if (mysqli_num_rows($candidate_result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid candidate']);
        exit;
    }

    // Generate vote receipt
    $receipt = bin2hex(random_bytes(16));

    // Record vote
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $vote_query = "INSERT INTO votes (voter_id, election_id, candidate_id, ip_address, verification_token) 
                   VALUES ('$voter_id', '$election_id', '$candidate_id', '$ip_address', '$receipt')";

    if (mysqli_query($conn, $vote_query)) {
        // Update candidate vote count
        mysqli_query($conn, "UPDATE candidates SET votes = votes + 1 WHERE id = '$candidate_id'");

        echo json_encode([
            'status' => 'success',
            'message' => 'Vote cast successfully',
            'receipt' => $receipt
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error recording vote']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>