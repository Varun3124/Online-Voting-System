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
$voter_name = $_SESSION['voter_name'] ?? 'Voter';

// Get active elections
$stmt = $conn->prepare("
    SELECT e.*, 
           CASE WHEN v.election_id IS NOT NULL THEN 1 ELSE 0 END as has_voted
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id AND v.voter_id = ?
    WHERE e.status = 'active'
    AND e.start_date <= NOW()
    AND e.end_date >= NOW()
    ORDER BY e.end_date ASC
");

$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$active_elections = $result->fetch_all(MYSQLI_ASSOC);

// Get past elections where voter has participated
$stmt = $conn->prepare("
    SELECT e.*, v.candidate_id
    FROM elections e
    INNER JOIN votes v ON e.id = v.election_id
    WHERE v.voter_id = ?
    AND (e.status = 'completed' OR e.end_date < NOW())
    ORDER BY e.end_date DESC
");

$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$past_elections = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Online Voting System</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">Welcome, <?php echo htmlspecialchars($voter_name); ?></span>
                <a class="nav-link" href="../actions/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2>Active Elections</h2>
        <?php if (empty($active_elections)): ?>
            <div class="alert alert-info">No active elections available at the moment.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                <?php foreach ($active_elections as $election): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($election['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($election['description']); ?></p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Ends: <?php echo date('M d, Y h:i A', strtotime($election['end_date'])); ?>
                                    </small>
                                </p>
                                <?php if ($election['has_voted']): ?>
                                    <button class="btn btn-success" disabled>Vote Recorded</button>
                                <?php else: ?>
                                    <a href="vote.php?election=<?php echo $election['id']; ?>" class="btn btn-primary">
                                        Cast Vote
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Past Elections</h2>
        <?php if (empty($past_elections)): ?>
            <div class="alert alert-info">You haven't participated in any past elections.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Election</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past_elections as $election): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($election['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($election['end_date'])); ?></td>
                                <td><?php echo ucfirst($election['status']); ?></td>
                                <td>
                                    <a href="results.php?election=<?php echo $election['id']; ?>" 
                                       class="btn btn-sm btn-info">View Results</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>