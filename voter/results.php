<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');
require_once('../common/utils.php');

// Check if voter is logged in
if (!isset($_SESSION[VOTER_SESSION_KEY])) {
    header('Location: ../voter_login.php');
    exit();
}

$voter_id = $_SESSION[VOTER_SESSION_KEY];
$election_id = isset($_GET['election']) ? intval($_GET['election']) : 0;

// Validate election ID
if (!$election_id) {
    redirect_with_message('dashboard.php', 'Invalid election selected', 'error');
}

// Get election details and verify it's completed or past end date
$stmt = $conn->prepare("
    SELECT e.*, 
           CASE WHEN v.election_id IS NOT NULL THEN 1 ELSE 0 END as has_voted,
           CASE 
               WHEN e.status = 'completed' THEN 1
               WHEN e.end_date < NOW() THEN 1
               ELSE 0 
           END as can_view_results
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id AND v.voter_id = ?
    WHERE e.id = ?
");

$stmt->bind_param("ii", $voter_id, $election_id);
$stmt->execute();
$result = $stmt->get_result();
$election = $result->fetch_assoc();

if (!$election) {
    redirect_with_message('dashboard.php', 'Election not found', 'error');
}

// Only show results if election is completed or past end date
if (!$election['can_view_results']) {
    redirect_with_message('dashboard.php', 'Results are not available yet', 'error');
}

// Get candidates and their vote counts
$stmt = $conn->prepare("
    SELECT c.id, c.name, c.party, c.photo,
           COUNT(v.id) as vote_count,
           (COUNT(v.id) / (
               SELECT COUNT(*) 
               FROM votes 
               WHERE election_id = ?
           ) * 100) as vote_percentage
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
    WHERE c.election_id = ?
    GROUP BY c.id
    ORDER BY vote_count DESC, c.name ASC
");

$stmt->bind_param("ii", $election_id, $election_id);
$stmt->execute();
$result = $stmt->get_result();
$candidates = $result->fetch_all(MYSQLI_ASSOC);

// Get total votes
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM votes WHERE election_id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$total_votes = $stmt->get_result()->fetch_assoc()['total'];

// Get winner (if there's a tie, all candidates with max votes are considered winners)
$max_votes = 0;
$winners = [];
foreach ($candidates as $candidate) {
    if ($candidate['vote_count'] > $max_votes) {
        $max_votes = $candidate['vote_count'];
        $winners = [$candidate];
    } elseif ($candidate['vote_count'] == $max_votes) {
        $winners[] = $candidate;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - <?php echo htmlspecialchars($election['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        .progress {
            height: 25px;
        }
        .winner-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ffc107;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .candidate-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .result-card {
            position: relative;
            transition: transform 0.2s;
        }
        .result-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Online Voting System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Back to Dashboard</a>
                <a class="nav-link" href="../actions/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2><?php echo htmlspecialchars($election['title']); ?> - Results</h2>
        <p class="lead"><?php echo htmlspecialchars($election['description'] ?? ''); ?></p>
        
        <div class="alert alert-info">
            <strong>Total Votes Cast:</strong> <?php echo $total_votes; ?>
            <br>
            <strong>Election Status:</strong> <?php echo ucfirst($election['status']); ?>
            <br>
            <strong>End Date:</strong> <?php echo date('M d, Y h:i A', strtotime($election['end_date'])); ?>
        </div>

        <?php if (count($winners) > 1): ?>
            <div class="alert alert-warning">
                <h4 class="alert-heading">Tie Result!</h4>
                <p>There is a tie between multiple candidates with <?php echo $max_votes; ?> votes each.</p>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($candidates as $candidate): ?>
                <div class="col-12 mb-4">
                    <div class="card result-card">
                        <?php if (in_array($candidate, $winners)): ?>
                            <div class="winner-badge">Winner!</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="<?php echo $candidate['photo'] ? '../uploads/' . htmlspecialchars($candidate['photo']) : '../img/default-candidate.png'; ?>" 
                                         class="candidate-photo mb-2" 
                                         alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                </div>
                                <div class="col-md-10">
                                    <h5 class="card-title"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                    <p class="card-text">Party: <?php echo htmlspecialchars($candidate['party']); ?></p>
                                    <div class="progress mb-2">
                                        <div class="progress-bar" 
                                             role="progressbar" 
                                             style="width: <?php echo number_format($candidate['vote_percentage'], 1); ?>%" 
                                             aria-valuenow="<?php echo number_format($candidate['vote_percentage'], 1); ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo number_format($candidate['vote_percentage'], 1); ?>%
                                        </div>
                                    </div>
                                    <p class="mb-0">
                                        <strong>Votes received:</strong> <?php echo $candidate['vote_count']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>