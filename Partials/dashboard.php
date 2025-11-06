<?php

session_start();
include('../actions/connect.php');
if(!isset($_SESSION['id'])){
    header('location:../');
}
$data=$_SESSION['data'];
if($_SESSION['status']==1){
    $status='<b class="text-success">Voted</b>';
}else{
    $status='<b class="text-danger">Not Voted</b>';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting system - Dashboard</title>

     <!-- bootstrap link -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" 
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- CSS -->
    
    <link rel="stylesheet" href="../style.css">

</head>
<body class="bg-secondary text-light">
    <div class="container my-5">
        <a href="../"><button class="btn btn-dark text-light px-3">Back</button></a>
        <a href="logout.php"><button class="btn btn-dark text-light px-3">Logout</button></a>

        <h1 class="my-3">Voting System</h1>
        <div class="row my-5">
            <div class="col-md-7">
                <?php 
                if(isset($_SESSION['candidates'])){
                    $candidates=$_SESSION['candidates'];
                    for($i=0;$i<count($candidates);$i++){
                        ?>
                        <div class="row">
                        <div class="col-md-4">
                            <?php if(!empty($candidates[$i]['photo']) && file_exists("../uploads/".$candidates[$i]['photo'])) { ?>
                                <img src="../uploads/<?php echo $candidates[$i]['photo'] ?>" alt="Candidate image" class="img-fluid" style="max-width: 150px;">
                            <?php } else { ?>
                                <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                            <?php } ?>
                        </div>
                        <div class="col-md-8">
                            <strong class="text-dark h5">Candidate name:</strong>
                            <?php echo $candidates[$i]['username'] ?>
                            <br>
                            <strong class="text-dark h5">Votes:</strong>
                            <?php echo $candidates[$i]['votes'] ?><br>
                        </div>
                    </div>
                
                <form action="../actions/voting.php" method="post">
                    <input type="hidden" name="candidatevotes" value="<?php echo $candidates[$i]['votes'] ?>">
                    <input type="hidden" name="candidateid" value="<?php echo $candidates[$i]['id'] ?>">
                        
                    <?php 
                        $uid = $_SESSION['id'];
                        $cid = $candidates[$i]['id'];
                        $voted = mysqli_query($conn, "SELECT * FROM `votes` WHERE voter_id = '$uid' AND candidate_id = '$cid'");
                        
                        if(mysqli_num_rows($voted) > 0){
                            ?>
                            <button class="bg-success my-3 text-white px-3" disabled>Voted</button>
                            </form>
                            <!-- Add unvote form -->
                            <form action="../actions/unvote.php" method="post">
                                <input type="hidden" name="candidatevotes" value="<?php echo $candidates[$i]['votes'] ?>">
                                <input type="hidden" name="candidateid" value="<?php echo $candidates[$i]['id'] ?>">
                                <button class="bg-warning my-1 text-white px-3" type="submit">Unvote</button>
                            </form>
                            <?php 
                        }else{
                            ?>
                            <button class="bg-danger my-3 text-white px-3" type="submit">Vote</button>
                            <?php 
                        }
                    ?>    

                </form>
                <hr>
                <?php
                    
                   }
                }else{
                    ?>
                    <div class="container">
                        <p>No Candidates to display</p>
                    </div>
                    <?php
                }
                ?>
                
                <!-- candidate -->
                
            </div>
            <div class="col-md-5">
                <!-- voters -->
                <?php if(!empty($data['photo']) && file_exists("../uploads/".$data['photo'])) { ?>
                    <img src="../uploads/<?php echo $data['photo'] ?>" alt="Voter image" class="img-fluid" style="max-width: 150px;">
                <?php } else { ?>
                    <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                <?php } ?>
                <br>
                <br>
                <strong class="text-dark h5">Name:</strong>
                <?php echo $data['username'];?> <br><br>
                <strong class="text-dark h5">Role:</strong>
                <?php echo $data['standard'];?> <br><br>
                <strong class="text-dark h5">Status:</strong>
                <?php echo $status;?><br><br>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a><br><br>
            </div>
        </div>
    </div>
    
</body>
</html>