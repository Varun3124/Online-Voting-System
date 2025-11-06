<?php
session_start();
require_once('../actions/common/db_connect.php');
require_once('../actions/common/utils.php');

if(!isset($_SESSION['voter_id'])){
    header('location:../voter_login.php');
    exit;
}

$voter_id = $_SESSION['voter_id'];
$stmt = $conn->prepare("SELECT * FROM voters WHERE id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$voter = $result->fetch_assoc();
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
                <form action="../actions/voter/update_profile.php" method="POST" enctype="multipart/form-data">
                    <h4 class="mb-4">General Information</h4>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($voter['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Aadhar Number</label>
                        <input type="text" class="form-control" value="<?php echo substr($voter['aadhar'], 0, 4) . '****' . substr($voter['aadhar'], -4); ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" value="<?php echo $voter['dob']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($voter['phone']); ?>" pattern="\d{10}" title="Please enter valid 10-digit mobile number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($voter['email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Photo</label><br>
                        <?php if(!empty($voter['photo']) && file_exists("../uploads/voter_photos/".$voter['photo'])) { ?>
                            <img src="../uploads/voter_photos/<?php echo $voter['photo'] ?>" alt="Current photo" class="img-fluid" style="max-width: 150px;">
                        <?php } else { ?>
                            <img src="../uploads/default.png" alt="Default image" class="img-fluid" style="max-width: 150px;">
                        <?php } ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Photo (leave blank to keep current)</label>
                        <input type="file" class="form-control" name="new_photo" accept="image/*">
                    </div>

                    <button type="submit" name="update_general" class="btn btn-primary mb-4">Update Profile</button>
                </form>

                <!-- Password Change Form -->
                <form action="../actions/voter/update_password.php" method="POST" id="passwordForm">
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

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>

                <script>
                document.getElementById('passwordForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const newPass = document.getElementById('new-password').value;
                    const confirmPass = document.getElementById('confirm-password').value;
                    
                    if (newPass !== confirmPass) {
                        alert('New passwords do not match!');
                        return;
                    }
                    
                    this.submit();
                });
                </script>
                <!-- JavaScript for password toggle -->
                <script src="../javascripts/password-toggle.js"></script>
            </div>
        </div>
    </div>
</body>
</html>