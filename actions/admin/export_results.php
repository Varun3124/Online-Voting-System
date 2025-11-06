<?php
require_once('../common/config.php');
require_once('../common/verify_session.php');

// Verify admin session
verify_admin_session();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $election_id = mysqli_real_escape_string($conn, $_GET['election_id'] ?? '');
    $format = mysqli_real_escape_string($conn, $_GET['format'] ?? 'json'); // 'json' or 'csv'

    // Get election details
    $election_query = "SELECT * FROM elections WHERE id = '$election_id'";
    $election_result = mysqli_query($conn, $election_query);
    
    if (mysqli_num_rows($election_result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Election not found']);
        exit;
    }

    $election = mysqli_fetch_assoc($election_result);

    // Get candidates and their votes
    $results_query = "
        SELECT 
            c.id,
            c.name as candidate_name,
            c.party,
            COUNT(v.id) as votes,
            (SELECT COUNT(*) FROM votes WHERE election_id = '$election_id') as total_votes
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id AND v.election_id = '$election_id'
        WHERE c.election_id = '$election_id'
        GROUP BY c.id
        ORDER BY votes DESC";

    $results_result = mysqli_query($conn, $results_query);
    $results = [];
    
    while ($row = mysqli_fetch_assoc($results_result)) {
        $total_votes = $row['total_votes'];
        $percentage = $total_votes > 0 ? round(($row['votes'] / $total_votes) * 100, 2) : 0;
        
        $results[] = [
            'candidate_id' => $row['id'],
            'candidate_name' => $row['candidate_name'],
            'party' => $row['party'],
            'votes' => $row['votes'],
            'percentage' => $percentage
        ];
    }

    // Get voter turnout statistics
    $turnout_query = "
        SELECT 
            (SELECT COUNT(DISTINCT voter_id) FROM votes WHERE election_id = '$election_id') as voted,
            (SELECT COUNT(*) FROM voters WHERE status = 'approved') as total_voters";

    $turnout_result = mysqli_query($conn, $turnout_query);
    $turnout = mysqli_fetch_assoc($turnout_result);
    $turnout['percentage'] = $turnout['total_voters'] > 0 ? 
        round(($turnout['voted'] / $turnout['total_voters']) * 100, 2) : 0;

    if ($format === 'csv') {
        // Output as CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="election_results_'.$election_id.'.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write election details
        fputcsv($output, ['Election Results']);
        fputcsv($output, ['Title', $election['title']]);
        fputcsv($output, ['Constituency', $election['constituency']]);
        fputcsv($output, ['Date', $election['end_date']]);
        fputcsv($output, []);
        
        // Write turnout
        fputcsv($output, ['Voter Turnout']);
        fputcsv($output, ['Total Voters', 'Votes Cast', 'Turnout Percentage']);
        fputcsv($output, [$turnout['total_voters'], $turnout['voted'], $turnout['percentage'].'%']);
        fputcsv($output, []);
        
        // Write results
        fputcsv($output, ['Candidate Results']);
        fputcsv($output, ['Candidate Name', 'Party', 'Votes', 'Percentage']);
        foreach ($results as $result) {
            fputcsv($output, [
                $result['candidate_name'],
                $result['party'],
                $result['votes'],
                $result['percentage'].'%'
            ]);
        }
        
        fclose($output);
    } else {
        // Output as JSON
        echo json_encode([
            'status' => 'success',
            'election' => $election,
            'results' => $results,
            'turnout' => $turnout
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>