<?php
include('connect.php');
if(isset($_POST['Registerme'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $cpassword=$_POST['cpassword'];
    $std=$_POST['std'];

    // Initialize photo name as empty
    $image = '';

    // Check if a file was uploaded
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['photo']['tmp_name'];
        $image = $_FILES['photo']['name'];
        
        // Generate unique name to prevent overwriting
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $image_ext;
        
        // Create uploads directory if it doesn't exist
        $upload_dir = "../uploads";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Try to move the uploaded file
        if(!move_uploaded_file($tmp_name, "$upload_dir/$image")) {
            echo '<script>
            alert("Error uploading image. Please try again.");
            window.location="../Partials/registration.php";
            </script>';
            exit();
        }
    }

    if($password!=$cpassword){
        echo '<script>
        alert("Passwords do not match");
        window.location="../Partials/registration.php";
        </script>';
    } else {
        $sql="insert into `userdata`(username,password,photo,
        standard,status,votes) values ('$username','$password','$image','$std',0,0)";

    $result=mysqli_query($conn,$sql);

    if($result){
        echo '<script>
        alert("Registration Successful");                
        window.location="../index.php"; 
        </script>';     
    }
    else{
        die(mysqli_error($conn));
    }
}
}
?>