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

// Check if election exists and is active
$stmt = $conn->prepare("
    SELECT e.*, CASE WHEN v.election_id IS NOT NULL THEN 1 ELSE 0 END as has_voted
    FROM elections e
    LEFT JOIN votes v ON e.id = v.election_id AND v.voter_id = ?
    WHERE e.id = ? AND e.status = 'active'
    AND e.start_date <= NOW()
    AND e.end_date >= NOW()
");

$stmt->bind_param("ii", $voter_id, $election_id);
$stmt->execute();
$result = $stmt->get_result();
$election = $result->fetch_assoc();

if (!$election) {
    redirect_with_message('dashboard.php', 'Election not found or not active', 'error');
}

if ($election['has_voted']) {
    redirect_with_message('dashboard.php', 'You have already voted in this election', 'error');
}

// Get candidates for this election
$stmt = $conn->prepare("
    SELECT id, name, photo, party 
    FROM candidates 
    WHERE election_id = ? 
    ORDER BY name ASC
");

$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
$candidates = $result->fetch_all(MYSQLI_ASSOC);

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['candidate_id'])) {
            throw new Exception('Please select a candidate');
        }

        $candidate_id = intval($_POST['candidate_id']);
        
        // Verify candidate belongs to this election
        $valid_candidate = false;
        foreach ($candidates as $candidate) {
            if ($candidate['id'] === $candidate_id) {
                $valid_candidate = true;
                break;
            }
        }
        
        if (!$valid_candidate) {
            throw new Exception('Invalid candidate selected');
        }

        // Start transaction
        $conn->begin_transaction();

        // Double-check hasn't voted
        $stmt = $conn->prepare("
            SELECT 1 FROM votes 
            WHERE election_id = ? AND voter_id = ?
        ");
        $stmt->bind_param("ii", $election_id, $voter_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('You have already voted in this election');
        }

        // Record vote
        $stmt = $conn->prepare("
            INSERT INTO votes (election_id, voter_id, candidate_id) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iii", $election_id, $voter_id, $candidate_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to record vote');
        }

        $conn->commit();
        redirect_with_message('dashboard.php', 'Your vote has been recorded successfully', 'success');

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Vote - <?php echo htmlspecialchars($election['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        .candidate-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .candidate-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
        .candidate-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin: 1rem auto;
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
        <h2><?php echo htmlspecialchars($election['title']); ?></h2>
        <p class="lead"><?php echo htmlspecialchars($election['description']); ?></p>
        <p class="text-muted">
            Voting ends: <?php echo date('M d, Y h:i A', strtotime($election['end_date'])); ?>
        </p>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="post" id="voteForm">
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="col">
                        <div class="card h-100 candidate-card" onclick="selectCandidate(<?php echo $candidate['id']; ?>)">
                            <img src="<?php echo $candidate['photo'] ? '../uploads/' . htmlspecialchars($candidate['photo']) : '../img/default-candidate.png'; ?>" 
                                 class="candidate-photo" 
                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                <p class="card-text">Party: <?php echo htmlspecialchars($candidate['party']); ?></p>
                                <div class="form-check justify-content-center">
                                    <input type="radio" 
                                           name="candidate_id" 
                                           value="<?php echo $candidate['id']; ?>" 
                                           class="form-check-input"
                                           id="candidate<?php echo $candidate['id']; ?>"
                                           required>
                                    <label class="form-check-label" for="candidate<?php echo $candidate['id']; ?>">
                                        Select Candidate
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mb-4">
                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirmVote()">
                    Cast Vote
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectCandidate(id) {
            // Remove selected class from all cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            const radio = document.getElementById('candidate' + id);
            if (radio) {
                radio.checked = true;
                radio.closest('.candidate-card').classList.add('selected');
            }
        }

        function confirmVote() {
            const selected = document.querySelector('input[name="candidate_id"]:checked');
            if (!selected) {
                alert('Please select a candidate');
                return false;
            }
            
            return confirm('Are you sure you want to cast your vote? This action cannot be undone.');
        }
    </script>
</body>
</html>