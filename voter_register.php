<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System - Voter Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-dark">
    <h1 class="text-center text-info">Online Voting System</h1>
    <div class="bg-info py-4">
        <h2 class="text-center">Voter Registration</h2>
        <div class="container text-center">
            <form action="./actions/voter/register.php" method="POST" enctype="multipart/form-data" id="voterRegForm">
                <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="name" 
                           placeholder="Enter your full name" required="required">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="aadhar" 
                           placeholder="Enter Aadhar Number" required="required"
                           pattern="\d{12}" title="Please enter valid 12-digit Aadhar number">
                </div>
                <div class="mb-3">
                    <input type="date" class="form-control w-50 m-auto" name="dob" 
                           required="required" title="Enter your date of birth">
                </div>
                <div class="mb-3">
                    <input type="tel" class="form-control w-50 m-auto" name="phone" 
                           placeholder="Enter mobile number" pattern="\d{10}"
                           title="Please enter valid 10-digit mobile number">
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control w-50 m-auto" name="email" 
                           placeholder="Enter email address">
                </div>
                <div class="mb-3 password-container">
                    <input type="password" class="form-control w-50 m-auto" id="reg-password" 
                           name="password" placeholder="Create password" required="required">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('reg-password')"></i>
                </div>
                <div class="mb-3 password-container">
                    <input type="password" class="form-control w-50 m-auto" id="reg-cpassword" 
                           placeholder="Confirm Password" required="required" name="cpassword">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('reg-cpassword')"></i>
                </div>
                <div class="mb-3">
                    <input type="file" class="form-control w-50 m-auto" name="photo" accept="image/*">
                </div>

                <button type="submit" class="btn btn-dark my-4">Register</button>
                <p>Already have an account? <a href="./voter_login.php" class="text-white">Login here</a></p>
            </form>
        </div>
    </div>
    <script src="./javascripts/password-toggle.js"></script>
    <script>
    document.getElementById('voterRegForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate passwords match
        const password = document.getElementById('reg-password').value;
        const cpassword = document.getElementById('reg-cpassword').value;
        
        if (password !== cpassword) {
            alert('Passwords do not match!');
            return;
        }

        // Submit registration data
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during registration');
        });
    });
    </script>
</body>
</html>