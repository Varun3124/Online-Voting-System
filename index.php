<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP voting system </title>

    <!-- bootstrap link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" 
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 27%;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-dark">
    <h1 class= "text-info text-center p-3">Voting System</h1>
    <div class="bg-info py-4">
        <h2 class="text-center">Welcome to Online Voting System</h2>
        <div class="container text-center">
            <p class="text-white">
                <a href="./public/results.php" class="btn btn-light">
                    <i class="fas fa-chart-bar"></i> View Election Results
                </a>
            </p>
            <div class="row justify-content-center mt-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Voter Login</h3>
                            <p>Login to cast your vote</p>
                            <a href="./voter_login.php" class="btn btn-dark">Voter Login</a>
                            <hr>
                            <p>New voter? <a href="./voter_register.php">Register here</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h3>Admin Login</h3>
                            <p>Login to manage elections</p>
                            <a href="./admin_login.php" class="btn btn-dark">Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JavaScript for password toggle -->
    <script src="./javascripts/password-toggle.js"></script>
</body>
</html>