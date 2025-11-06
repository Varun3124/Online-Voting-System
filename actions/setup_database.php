<?php
include('connect.php');

// First, create the userdata table
$create_userdata_table = "CREATE TABLE IF NOT EXISTS `userdata` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL,
    `password` varchar(100) NOT NULL,
    `photo` varchar(100),
    `standard` enum('Candidate','Voter') NOT NULL,
    `status` int(11) DEFAULT 0,
    `votes` int(11) DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $create_userdata_table)) {
    echo "Userdata table created successfully<br>";

    // Insert default values only if the table is empty
    $check_empty = mysqli_query($conn, "SELECT * FROM userdata LIMIT 1");
    if(mysqli_num_rows($check_empty) == 0) {
        // Insert some default candidates
        $insert_defaults = "INSERT INTO `userdata` 
            (`username`, `password`, `standard`, `status`, `votes`) VALUES
            ('BJP', 'modi', 'Candidate', 0, 0),
            ('INC', 'rahul', 'Candidate', 0, 0),
            ('BJP supporter', '123', 'Voter', 0, 0),
            ('Congress supporter', '123', 'Voter', 0, 0)";

        if(mysqli_query($conn, $insert_defaults)) {
            echo "Default users created successfully<br>";
        } else {
            echo "Error creating default users: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Error creating userdata table: " . mysqli_error($conn) . "<br>";
}

// Then create the votes table with foreign key references
$create_votes_table = "CREATE TABLE IF NOT EXISTS `votes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `voter_id` INT NOT NULL,
    `candidate_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`voter_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $create_votes_table)) {
    echo "Votes table created successfully<br>";
} else {
    echo "Error creating votes table: " . mysqli_error($conn) . "<br>";
}

echo "<br>Database setup completed!<br>";
echo "Default login credentials:<br>";
echo "Candidates: username='candidate1' or 'candidate2', password='pass123'<br>";
echo "Voters: username='voter1' or 'voter2', password='pass123'<br>";
?>