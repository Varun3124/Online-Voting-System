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
        <h2 class="text-center">Login</h2>
        <div class="container text-center"> 
            <form action="./actions/login.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="username" placeholder="Enter your username"
                    required="required">
                </div>
                <div class="mb-3 password-container">
                    <input type="password" class="form-control w-50 m-auto" id="login-password" name="password" placeholder="Enter your password"
                    required="required">
                    <i class="fas fa-eye password-toggle" id="login-password-icon" onclick="togglePassword('login-password')"></i>
                </div>
                <div class="mb-3">
                    <select name="std" class="form-select w-50 m-auto">
                        <option value="Candidate">Candidate</option>
                        <option value="Voter">Voter</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark my-4">Login</button>
                <p>Don't have an account? <a href="./Partials/registration.php" class="text-white"> Register here</a></p>
            </form>
        </div>
    </div>
    <!-- JavaScript for password toggle -->
    <script src="./javascripts/password-toggle.js"></script>
</body>
</html>