<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System - Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-dark">
    <h1 class="text-info text-center p-3">Online Voting System</h1>
    <div class="bg-info py-4">
        <h2 class="text-center">Admin Login</h2>
        <div class="container text-center"> 
            <form action="./actions/admin/login.php" method="POST" id="adminLoginForm">
                <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="username" 
                           placeholder="Enter admin username" required="required">
                </div>
                <div class="mb-3 password-container">
                    <input type="password" class="form-control w-50 m-auto" id="admin-password" 
                           name="password" placeholder="Enter password" required="required">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('admin-password')"></i>
                </div>
                <button type="submit" class="btn btn-dark my-4">Login</button>
                <p><a href="./voter_login.php" class="text-white">Go to Voter Login</a></p>
            </form>
        </div>
    </div>
    <script src="./javascripts/password-toggle.js"></script>
    <script>
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                window.location.href = './admin/dashboard.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during login');
        });
    });
    </script>
</body>
</html>