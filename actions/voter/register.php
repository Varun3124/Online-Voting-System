<?php
require_once('../common/db_connect.php');
require_once('../common/utils.php');
require_once('../common/config.php');

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate required fields
    $name = validate_input($_POST['name'] ?? '');
    $aadhar = validate_input($_POST['aadhar'] ?? '');
    $dob = validate_input($_POST['dob'] ?? '');
    $phone = validate_input($_POST['phone'] ?? '');
    $email = validate_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($name) || empty($aadhar) || empty($dob) || empty($password)) {
        throw new Exception('Required fields cannot be empty');
    }

    // Validate Aadhar format
    if (!preg_match("/^\d{12}$/", $aadhar)) {
        throw new Exception('Invalid Aadhar number format');
    }

    // Validate phone format if provided
    if (!empty($phone) && !preg_match("/^\d{10}$/", $phone)) {
        throw new Exception('Invalid phone number format');
    }

    // Validate email format if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate age
    if (!verify_voter_age($dob)) {
        throw new Exception('Voter must be at least 18 years old');
    }

    // Check if voter already exists
    $stmt = $conn->prepare("SELECT id FROM voters WHERE aadhar_number = ?");
    $stmt->bind_param("s", $aadhar);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Voter with this Aadhar already exists');
    }

    // Upload photo if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        // Validate file type
        $file_info = getimagesize($_FILES['photo']['tmp_name']);
        if ($file_info === false || !in_array($file_info['mime'], ['image/jpeg', 'image/png'])) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed');
        }

        // Create directory if it doesn't exist
        $target_dir = "../../uploads/voter_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Generate unique filename
        $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_path = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
        $full_path = $target_dir . $photo_path;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $full_path)) {
            throw new Exception('Failed to upload photo');
        }
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert voter
        $stmt = $conn->prepare("INSERT INTO voters (name, aadhar_number, dob, phone, email, photo, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssssss", $name, $aadhar, $dob, $phone, $email, $photo_path, $hashed_password);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to register voter');
        }

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Please wait for admin approval.',
            'redirect' => './voter_login.php'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // If photo was uploaded, delete it on error
    if (isset($full_path) && file_exists($full_path)) {
        unlink($full_path);
    }

    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>