<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...\n";

// Try connecting without database first
$conn = new mysqli('localhost', 'root', '');
if ($conn->connect_error) {
    die("MySQL Server Connection Failed: " . $conn->connect_error . "\n");
}
echo "Connected to MySQL server successfully\n";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE 'online_voting'");
if ($result->num_rows === 0) {
    echo "Database 'online_voting' does not exist. Creating it...\n";
    if ($conn->query("CREATE DATABASE online_voting")) {
        echo "Database created successfully\n";
    } else {
        die("Failed to create database: " . $conn->error . "\n");
    }
}

// Select the database
if (!$conn->select_db('online_voting')) {
    die("Failed to select database: " . $conn->error . "\n");
}
echo "Selected database successfully\n";

// Check if tables exist
$required_tables = ['voters', 'elections', 'candidates', 'votes'];
$missing_tables = [];

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "Missing tables: " . implode(', ', $missing_tables) . "\n";
    echo "Creating tables...\n";
    
    // Create tables
    $queries = [
        "CREATE TABLE voters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            aadhar_number VARCHAR(12) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(15),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE elections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            status ENUM('pending', 'active', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE candidates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            election_id INT,
            name VARCHAR(100) NOT NULL,
            photo VARCHAR(255),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            election_id INT,
            voter_id INT,
            candidate_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
            FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE CASCADE,
            FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
            UNIQUE KEY unique_vote (election_id, voter_id)
        )"
    ];
    
    foreach ($queries as $query) {
        if ($conn->query($query)) {
            echo "Table created successfully\n";
        } else {
            echo "Error creating table: " . $conn->error . "\n";
        }
    }
}

echo "Database setup complete!\n";
?>