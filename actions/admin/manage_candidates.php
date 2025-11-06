<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify admin session
verify_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $election_id = mysqli_real_escape_string($conn, $_POST['election_id'] ?? '');

    switch($action) {
        case 'add':
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $party = mysqli_real_escape_string($conn, $_POST['party']);
            
            // Handle photo upload
            $photo = '';
            if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "../../uploads/candidates/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $photo = $target_dir . time() . '_' . basename($_FILES["photo"]["name"]);
                move_uploaded_file($_FILES["photo"]["tmp_name"], $photo);
            }

            $query = "INSERT INTO candidates (name, party, photo, election_id) 
                     VALUES ('$name', '$party', '$photo', '$election_id')";
            
            if(mysqli_query($conn, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Candidate added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error adding candidate']);
            }
            break;

        case 'remove':
            $candidate_id = mysqli_real_escape_string($conn, $_POST['candidate_id']);
            
            // Get photo path first to delete file
            $photo_query = "SELECT photo FROM candidates WHERE id = '$candidate_id' AND election_id = '$election_id'";
            $result = mysqli_query($conn, $photo_query);
            if($row = mysqli_fetch_assoc($result)) {
                if(!empty($row['photo']) && file_exists($row['photo'])) {
                    unlink($row['photo']);
                }
            }

            $query = "DELETE FROM candidates WHERE id = '$candidate_id' AND election_id = '$election_id'";
            
            if(mysqli_query($conn, $query)) {
                echo json_encode(['status' => 'success', 'message' => 'Candidate removed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error removing candidate']);
            }
            break;

        case 'list':
            $query = "SELECT * FROM candidates WHERE election_id = '$election_id'";
            $result = mysqli_query($conn, $query);
            $candidates = [];
            
            while($row = mysqli_fetch_assoc($result)) {
                $candidates[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'candidates' => $candidates]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>