<?php
session_start();
require_once('../actions/common/db_connect.php');
require_once('../actions/common/utils.php');

if (!isset($_SESSION['voter_id'])) {
    header('location:../voter_login.php');
    exit;
}

$voter_id = $_SESSION['voter_id'];

// Get voter data
$stmt = $conn->prepare("SELECT * FROM voters WHERE id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$voter = $result->fetch_assoc();

// Check if voted
$stmt = $conn->prepare("SELECT * FROM votes WHERE voter_id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$voted = $stmt->get_result();
$status = $voted->num_rows > 0 ? '<b class="text-success">Voted</b>' : '<b class="text-danger">Not Voted</b>';


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
                // Get all candidates
                $stmt = $conn->prepare("SELECT c.*, COUNT(v.id) as vote_count 
                                      FROM candidates c 
                                      LEFT JOIN votes v ON c.id = v.candidate_id 
                                      WHERE c.status = 'approved'
                                      GROUP BY c.id");
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0){
                    while($candidate = $result->fetch_assoc()){
                        ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php if(!empty($candidate['photo']) && file_exists("../uploads/candidate_photos/".$candidate['photo'])) { ?>
                                    <img src="../uploads/candidate_photos/<?php echo $candidate['photo'] ?>" alt="Candidate image" class="img-fluid" style="max-width: 150px;">
                                <?php } else { ?>
                                    <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                                <?php } ?>
                            </div>
                            <div class="col-md-8">
                                <strong class="text-dark h5">Candidate name:</strong>
                                <?php echo htmlspecialchars($candidate['name']) ?>
                                <br>
                                <strong class="text-dark h5">Votes:</strong>
                                <?php echo $candidate['vote_count'] ?><br>
                            </div>
                        </div>
                    
                        <form action="../actions/voter/vote.php" method="post" id="voteForm<?php echo $candidate['id']; ?>">
                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id'] ?>">
                            
                            <?php 
                            $stmt2 = $conn->prepare("SELECT * FROM votes WHERE voter_id = ? AND candidate_id = ?");
                            $stmt2->bind_param("ii", $voter_id, $candidate['id']);
                            $stmt2->execute();
                            $voted_result = $stmt2->get_result();
                            
                            if($voted_result->num_rows > 0){
                                ?>
                                <button class="bg-success my-3 text-white px-3" disabled>Voted</button>
                                </form>
                                <form action="../actions/voter/unvote.php" method="post">
                                    <input type="hidden" name="candidate_id" value="<?php echo $candidate['id'] ?>">
                                    <button class="bg-warning my-1 text-white px-3" type="submit">Unvote</button>
                                </form>
                                <?php 
                            } else {
                                ?>
                                <button class="bg-danger my-3 text-white px-3" type="submit">Vote</button>
                                <?php 
                            }
                            ?>    
                        </form>
                        <hr>
                        <?php
                    }
                } else {
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
                <?php if(!empty($voter['photo']) && file_exists("../uploads/voter_photos/".$voter['photo'])) { ?>
                    <img src="../uploads/voter_photos/<?php echo $voter['photo'] ?>" alt="Voter image" class="img-fluid" style="max-width: 150px;">
                <?php } else { ?>
                    <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                <?php } ?>
                <br>
                <br>
                <strong class="text-dark h5">Name:</strong>
                <?php echo htmlspecialchars($voter['name']);?> <br><br>
                <strong class="text-dark h5">Aadhar:</strong>
                <?php echo substr($voter['aadhar'], 0, 4) . '****' . substr($voter['aadhar'], -4);?> <br><br>
                <strong class="text-dark h5">Status:</strong>
                <?php echo $status;?><br><br>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                <a href="../actions/voter/logout.php" class="btn btn-danger">Logout</a><br><br>
            </div>
        </div>
    </div>
    
</body>
</html>