<?php

session_start();
include ('connect.php');

$votes = $_POST['candidatevotes'];
$gid = $_POST['candidateid'];
$uid = $_SESSION['id'];

// First check if the user has already voted
$checkvote = mysqli_query($conn, "SELECT * FROM `votes` WHERE voter_id = '$uid'");

if(mysqli_num_rows($checkvote) > 0) {
    echo '<script>
    alert("You have already voted!");
    window.location="../partials/dashboard.php";
    </script>';
    exit();
}

// If user hasn't voted yet, proceed with voting
$totalvotes = $votes + 1;

// Add vote record to votes table
$addvote = mysqli_query($conn, "INSERT INTO `votes` (voter_id, candidate_id) VALUES ('$uid', '$gid')");
$updatevotes = mysqli_query($conn, "UPDATE `userdata` SET votes = '$totalvotes' WHERE id = '$gid'");
$updatestatus = mysqli_query($conn, "UPDATE `userdata` SET status = 1 WHERE id = '$uid'");

if($updatevotes and $updatestatus) {
    $getcandidates=mysqli_query($conn,"select username ,photo,votes, id from `userdata`
    where standard = 'candidate'");
    $candidates=mysqli_fetch_all($getcandidates,MYSQLI_ASSOC);
    $_SESSION['candidates']=$candidates;
    $_SESSION['status']=1;

    echo '<script>
    alert("Voting Successful");
    window.location="../partials/dashboard.php";
    </script>';

}else{
    echo '<script>
    alert("Technical error !! Vote after sometime");
    window.location="../partials/dashboard.php";
    </script>';
}


?>