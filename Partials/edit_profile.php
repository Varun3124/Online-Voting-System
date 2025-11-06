<?php
session_start();
include('../actions/connect.php');

if(!isset($_SESSION['id'])){
    header('location:../');
}
$data = $_SESSION['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Voting System</title>

    <!-- bootstrap link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" 
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- CSS -->
    <link rel="stylesheet" href="../style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-secondary text-light">
    <div class="container my-5">
        <a href="dashboard.php"><button class="btn btn-dark text-light px-3">Back</button></a>
        <h2 class="my-4">Edit Profile</h2>

        <div class="row">
            <div class="col-md-6">
                <!-- General Information Form -->
                <form action="../actions/update_profile.php" method="POST" enctype="multipart/form-data">
                    <h4 class="mb-4">General Information</h4>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo $data['username']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Photo</label><br>
                        <?php if(!empty($data['photo']) && file_exists("../uploads/".$data['photo'])) { ?>
                            <img src="../uploads/<?php echo $data['photo'] ?>" alt="Current photo" class="img-fluid" style="max-width: 150px;">
                        <?php } else { ?>
                            <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Photo (leave blank to keep current)</label>
                        <input type="file" class="form-control" name="new_photo">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="standard" class="form-control">
                            <option value="Voter" <?php if($data['standard'] == 'Voter') echo 'selected'; ?>>Voter</option>
                            <option value="Candidate" <?php if($data['standard'] == 'Candidate') echo 'selected'; ?>>Candidate</option>
                        </select>
                    </div>

                    <button type="submit" name="update_general" class="btn btn-primary mb-4">Update Profile</button>
                </form>

                <!-- Password Change Form -->
                <form action="../actions/update_profile.php" method="POST">
                    <h4 class="mb-4 mt-5">Change Password</h4>
                    <div class="mb-3 password-container">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new-password" name="new_password" required>
                        <i class="fas fa-eye password-toggle" id="new-password-icon" onclick="togglePassword('new-password')"></i>
                    </div>

                    <div class="mb-3 password-container">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        <i class="fas fa-eye password-toggle" id="confirm-password-icon" onclick="togglePassword('confirm-password')"></i>
                    </div>

                    <div class="mb-3 password-container">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current-password" name="current_password" required>
                        <i class="fas fa-eye password-toggle" id="current-password-icon" onclick="togglePassword('current-password')"></i>
                    </div>

                    <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
                </form>
                <!-- JavaScript for password toggle -->
                <script src="../javascripts/password-toggle.js"></script>
            </div>
        </div>
    </div>
</body>
</html>