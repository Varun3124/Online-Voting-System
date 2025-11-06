<?php
require_once('common/db_connect.php');

// Create admin table
$create_admin_table = "CREATE TABLE IF NOT EXISTS `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL,
    `password` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $create_admin_table)) {
    echo "Admin table created successfully<br>";
    
    // Insert default admin if table is empty
    $check_admin = mysqli_query($conn, "SELECT * FROM admin LIMIT 1");
    if(mysqli_num_rows($check_admin) == 0) {
        // Hash the password for security
        $default_password = 'admin123';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $insert_admin = "INSERT INTO `admin` (`username`, `password`, `email`) 
                        VALUES ('admin', '$hashed_password', 'admin@example.com')";
        if(mysqli_query($conn, $insert_admin)) {
            echo "Default admin account created (username: admin, password: admin123)<br>";
        }
    }
} else {
    echo "Error creating admin table: " . mysqli_error($conn) . "<br>";
}

// Create elections table
$create_elections_table = "CREATE TABLE IF NOT EXISTS `elections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(200) NOT NULL,
    `description` text,
    `constituency` varchar(100),
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `status` enum('draft','active','completed') DEFAULT 'draft',
    `created_by` int(11),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`created_by`) REFERENCES `admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(!mysqli_query($conn, $create_elections_table)) {
    echo "Error creating elections table: " . mysqli_error($conn) . "<br>";
}

// Create candidates table (replacing old candidates in userdata)
$create_candidates_table = "CREATE TABLE IF NOT EXISTS `candidates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `party` varchar(100) NOT NULL,
    `photo` varchar(100),
    `election_id` int(11) NOT NULL,
    `votes` int(11) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`election_id`) REFERENCES `elections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(!mysqli_query($conn, $create_candidates_table)) {
    echo "Error creating candidates table: " . mysqli_error($conn) . "<br>";
}

// Create voters table (replacing old voters in userdata)
$create_voters_table = "CREATE TABLE IF NOT EXISTS `voters` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `aadhar_number` varchar(12) NOT NULL UNIQUE,
    `dob` date NOT NULL,
    `phone` varchar(15),
    `email` varchar(100),
    `photo` varchar(100),
    `password` varchar(100) NOT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(!mysqli_query($conn, $create_voters_table)) {
    echo "Error creating voters table: " . mysqli_error($conn) . "<br>";
}

// Create votes table with enhanced tracking
$create_votes_table = "CREATE TABLE IF NOT EXISTS `votes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `voter_id` INT NOT NULL,
    `election_id` INT NOT NULL,
    `candidate_id` INT NOT NULL,
    `voted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(45),
    `verification_token` varchar(100),
    FOREIGN KEY (`voter_id`) REFERENCES `voters`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`election_id`) REFERENCES `elections`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `one_vote_per_election` (`voter_id`, `election_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $create_votes_table)) {
    echo "Votes table created successfully<br>";
} else {
    echo "Error creating votes table: " . mysqli_error($conn) . "<br>";
}



// Create rate limiting table
$create_rate_limits = "CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `ip` varchar(45) NOT NULL,
    `action` varchar(50) NOT NULL,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ip_action_idx` (`ip`, `action`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $create_rate_limits)) {
    echo "Rate limits table created successfully<br>";
} else {
    echo "Error creating rate limits table: " . mysqli_error($conn) . "<br>";
}

echo "<br>Database setup completed!<br>";
echo "Default admin credentials:<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "<br>Features enabled:<br>";
echo "- Admin panel for election management<br>";
echo "- Aadhar and DOB verification for voters<br>";

echo "- Secure voting with verification tokens<br>";
?>