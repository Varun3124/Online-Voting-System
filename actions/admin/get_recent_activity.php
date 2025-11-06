<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $activities = [];

    // Get recent voter registrations
    $stmt = $conn->prepare("SELECT name, created_at FROM voters ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'time' => date('M j, Y g:i A', strtotime($row['created_at'])),
            'description' => "New voter registration: " . htmlspecialchars($row['name'])
        ];
    }

    // Get recent votes
    $stmt = $conn->prepare("
        SELECT v.voted_at, vt.name as voter_name, e.title as election_title 
        FROM votes v
        JOIN voters vt ON v.voter_id = vt.id
        JOIN elections e ON v.election_id = e.id
        ORDER BY v.voted_at DESC LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'time' => date('M j, Y g:i A', strtotime($row['voted_at'])),
            'description' => htmlspecialchars($row['voter_name']) . " voted in election: " . htmlspecialchars($row['election_title'])
        ];
    }

    // Sort activities by time
    usort($activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Take only the most recent 5 activities
    $activities = array_slice($activities, 0, 5);

    echo json_encode(['activities' => $activities]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get recent activity']);
}
?>