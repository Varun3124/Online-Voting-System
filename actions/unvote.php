<?php
session_start();
include ('connect.php');

$votes = $_POST['candidatevotes'];
$gid = $_POST['candidateid'];
$uid = $_SESSION['id'];

// Check if the user has actually voted for this candidate
$checkvote = mysqli_query($conn, "SELECT * FROM `votes` WHERE voter_id = '$uid' AND candidate_id = '$gid'");

if(mysqli_num_rows($checkvote) == 0) {
    echo '<script>
    alert("You haven\'t voted for this candidate!");
    window.location="../partials/dashboard.php";
    </script>';
    exit();
}

// Remove the vote
$totalvotes = $votes - 1;

// Remove vote record from votes table
$removevote = mysqli_query($conn, "DELETE FROM `votes` WHERE voter_id = '$uid' AND candidate_id = '$gid'");
$updatevotes = mysqli_query($conn, "UPDATE `userdata` SET votes = '$totalvotes' WHERE id = '$gid'");
$updatestatus = mysqli_query($conn, "UPDATE `userdata` SET status = 0 WHERE id = '$uid'");

if($updatevotes and $updatestatus) {
    $getcandidates = mysqli_query($conn, "SELECT username, photo, votes, id FROM `userdata` WHERE standard = 'candidate'");
    $candidates = mysqli_fetch_all($getcandidates, MYSQLI_ASSOC);
    $_SESSION['candidates'] = $candidates;
    $_SESSION['status'] = 0;

    echo '<script>
    alert("Vote removed successfully");
    window.location="../partials/dashboard.php";
    </script>';
} else {
    echo '<script>
    alert("Technical error! Please try again later");
    window.location="../partials/dashboard.php";
    </script>';
}
?>