<?php
session_start();
include('connect.php');

if(!isset($_SESSION['id'])) {
    header('location:../');
    exit();
}

$uid = $_SESSION['id'];

// Password Update Form
if(isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_password = $_POST['current_password'];

    // Verify the current password
    $verify = mysqli_query($conn, "SELECT * FROM `userdata` WHERE id='$uid' AND password='$current_password'");
    if(mysqli_num_rows($verify) == 0) {
        echo '<script>
        alert("Current password is incorrect!");
        window.location="../Partials/edit_profile.php";
        </script>';
        exit();
    }

    // Check if new passwords match
    if($new_password != $confirm_password) {
        echo '<script>
        alert("New passwords do not match!");
        window.location="../Partials/edit_profile.php";
        </script>';
        exit();
    }

    // Update password
    $sql = "UPDATE `userdata` SET password='$new_password' WHERE id='$uid'";
    $result = mysqli_query($conn, $sql);

    if($result) {
        echo '<script>
        alert("Password updated successfully!");
        window.location="../Partials/dashboard.php";
        </script>';
        exit();
    } else {
        echo '<script>
        alert("Error updating password: ' . mysqli_error($conn) . '");
        window.location="../Partials/edit_profile.php";
        </script>';
        exit();
    }
}

// General Information Update Form
if(isset($_POST['update_general'])) {
    $username = $_POST['username'];
    $standard = $_POST['standard'];

    // Check if username already exists (excluding current user)
    $check_username = mysqli_query($conn, "SELECT * FROM `userdata` WHERE username='$username' AND id != '$uid'");
    if(mysqli_num_rows($check_username) > 0) {
        echo '<script>
        alert("Username already exists!");
        window.location="../Partials/edit_profile.php";
        </script>';
        exit();
    }

    // Handle photo upload
    $photo_update = "";
    if(isset($_FILES['new_photo']) && $_FILES['new_photo']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['new_photo']['tmp_name'];
        $image = $_FILES['new_photo']['name'];
        
        // Generate unique name
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $image_ext;
        
        // Create uploads directory if it doesn't exist
        $upload_dir = "../uploads";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Try to move the uploaded file
        if(move_uploaded_file($tmp_name, "$upload_dir/$image")) {
            $photo_update = ", photo='$image'";
        } else {
            echo '<script>
            alert("Error uploading image. Other changes will still be saved.");
            </script>';
        }
    }

    // Update the user data
    $sql = "UPDATE `userdata` SET username='$username', standard='$standard' $photo_update WHERE id='$uid'";
    $result = mysqli_query($conn, $sql);
}

if($result) {
    // Update session data
    $get_updated = mysqli_query($conn, "SELECT * FROM `userdata` WHERE id='$uid'");
    $updated_data = mysqli_fetch_array($get_updated);
    $_SESSION['data'] = $updated_data;
    
    echo '<script>
    alert("Profile updated successfully!");
    window.location="../Partials/dashboard.php";
    </script>';
} else {
    echo '<script>
    alert("Error updating profile: ' . mysqli_error($conn) . '");
    window.location="../Partials/edit_profile.php";
    </script>';
}
?>