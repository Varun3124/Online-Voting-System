<?php
require_once('../common/config.php');
require_once('../common/db_connect.php');
require_once('../common/utils.php');

// Get completed elections
$stmt = $conn->prepare("
    SELECT e.*, 
           COUNT(DISTINCT v.id) as total_votes
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id
    WHERE e.status = 'completed' OR e.end_date < NOW()
    GROUP BY e.id
    ORDER BY e.end_date DESC
");

$stmt->execute();
$elections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Online Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        .progress { height: 25px; }
        .candidate-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .winner-badge {
            background: #ffc107;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
        .result-card {
            transition: transform 0.2s;
        }
        .result-card:hover {
            transform: translateY(-5px);
        }
        .election-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .election-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Online Voting System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Home</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2>Public Election Results</h2>
        
        <?php if (empty($elections)): ?>
            <div class="alert alert-info">
                No completed elections available at the moment.
            </div>
        <?php else: ?>
            <?php foreach ($elections as $election): ?>
                <div class="card mb-4 election-card" onclick="toggleResults(<?php echo $election['id']; ?>)">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php echo htmlspecialchars($election['title']); ?>
                        </h5>
                        <span class="text-muted">
                            Ended: <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><?php echo htmlspecialchars($election['description'] ?? ''); ?></p>
                        <p><strong>Total Votes Cast:</strong> <?php echo $election['total_votes']; ?></p>
                        
                        <div id="results-<?php echo $election['id']; ?>" style="display: none;">
                            <?php
                            // Get candidates and their vote counts
                            $stmt = $conn->prepare("
                                SELECT c.id, c.name, c.party, c.photo,
                                       COUNT(v.id) as vote_count,
                                       (COUNT(v.id) / ? * 100) as vote_percentage
                                FROM candidates c
                                LEFT JOIN votes v ON c.id = v.candidate_id
                                WHERE c.election_id = ?
                                GROUP BY c.id
                                ORDER BY vote_count DESC, c.name ASC
                            ");
                            
                            $stmt->bind_param("ii", $election['total_votes'], $election['id']);
                            $stmt->execute();
                            $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            // Determine winner(s)
                            $max_votes = 0;
                            foreach ($candidates as $candidate) {
                                if ($candidate['vote_count'] > $max_votes) {
                                    $max_votes = $candidate['vote_count'];
                                }
                            }
                            ?>
                            
                            <div class="row mt-3">
                                <?php foreach ($candidates as $candidate): ?>
                                    <div class="col-12 mb-3">
                                        <div class="card result-card">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-2 text-center">
                                                        <img src="<?php echo $candidate['photo'] ? '../uploads/' . htmlspecialchars($candidate['photo']) : '../img/default-candidate.png'; ?>" 
                                                             class="candidate-photo mb-2" 
                                                             alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                    </div>
                                                    <div class="col-md-10">
                                                        <h5 class="card-title">
                                                            <?php echo htmlspecialchars($candidate['name']); ?>
                                                            <?php if ($candidate['vote_count'] == $max_votes && $election['total_votes'] > 0): ?>
                                                                <span class="winner-badge">Winner!</span>
                                                            <?php endif; ?>
                                                        </h5>
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
                        
                        <button class="btn btn-primary mt-2" onclick="event.stopPropagation(); toggleResults(<?php echo $election['id']; ?>)">
                            <span id="button-text-<?php echo $election['id']; ?>">Show Results</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleResults(electionId) {
            const resultsDiv = document.getElementById('results-' + electionId);
            const buttonText = document.getElementById('button-text-' + electionId);
            
            if (resultsDiv.style.display === 'none') {
                resultsDiv.style.display = 'block';
                buttonText.textContent = 'Hide Results';
            } else {
                resultsDiv.style.display = 'none';
                buttonText.textContent = 'Show Results';
            }
        }
    </script>
</body>
</html>