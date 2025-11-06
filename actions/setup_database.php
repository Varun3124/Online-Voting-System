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

// Create the votes table with foreign key references
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

if(mysqli_query($conn, $create_userdata_table) && mysqli_query($conn, $create_votes_table)) {
    echo "Tables created successfully<br>";

    // Insert default values only if the userdata table is empty
    $check_empty = mysqli_query($conn, "SELECT * FROM userdata LIMIT 1");
    if(mysqli_num_rows($check_empty) == 0) {
        // First insert the candidates and voters
        $insert_defaults = "INSERT INTO `userdata` 
            (`username`, `password`, `photo`, `standard`, `status`, `votes`) VALUES
            ('BJP', 'modi', 'uploads/bjp.jpg', 'Candidate', 0, 0),
            ('INC', 'rahul', 'uploads/inc.jpg', 'Candidate', 0, 0),
            ('BJP supporter', '123', NULL, 'Voter', 0, 0),
            ('Congress supporter', '123', NULL, 'Voter', 0, 0)";

        if(mysqli_query($conn, $insert_defaults)) {
            echo "Default users created successfully<br>";
            
            // Now add the default votes - BJP supporter votes for BJP, Congress supporter votes for INC
            $get_ids = "SELECT id, username FROM userdata";
            $result = mysqli_query($conn, $get_ids);
            $ids = array();
            while($row = mysqli_fetch_assoc($result)) {
                $ids[$row['username']] = $row['id'];
            }

            // Insert the default votes
            $insert_votes = "INSERT INTO `votes` (voter_id, candidate_id) VALUES
                ({$ids['BJP supporter']}, {$ids['BJP']}),
                ({$ids['Congress supporter']}, {$ids['INC']})";

            if(mysqli_query($conn, $insert_votes)) {
                // Update the vote counts for candidates
                mysqli_query($conn, "UPDATE userdata SET votes = votes + 1 WHERE id = {$ids['BJP']}");
                mysqli_query($conn, "UPDATE userdata SET votes = votes + 1 WHERE id = {$ids['INC']}");
                mysqli_query($conn, "UPDATE userdata SET status = 1 WHERE id IN ({$ids['BJP supporter']}, {$ids['Congress supporter']})");
                echo "Default votes recorded successfully<br>";
            } else {
                echo "Error recording default votes: " . mysqli_error($conn) . "<br>";
            }
        } else {
            echo "Error creating default users: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Error creating tables: " . mysqli_error($conn) . "<br>";
}

echo "<br>Database setup completed!<br>";
echo "Default login credentials:<br>";
echo "Candidates: BJP (pass: modi) and INC (pass: rahul)<br>";
echo "Voters: BJP supporter and Congress supporter (pass: 123)<br>";
echo "Note: Default supporters have already cast their votes for their respective parties<br>";
?>