<?php
require_once('../actions/common/config.php');
require_once('../actions/common/utils.php');

// Check if admin is logged in
if (!isset($_SESSION[ADMIN_SESSION_KEY])) {
    header('Location: ../admin_login.php');
    exit;
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Voting System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 bg-info p-0 min-vh-100">
                <div class="text-white p-4">
                    <h4>Welcome, <?php echo htmlspecialchars($admin_username); ?></h4>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active text-white" href="dashboard.php">Dashboard</a>
                    <a class="nav-link text-white" href="elections.php">Manage Elections</a>
                    <a class="nav-link text-white" href="voters.php">Manage Voters</a>
                    <a class="nav-link text-white" href="candidates.php">Manage Candidates</a>
                    <a class="nav-link text-white" href="../actions/admin/logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 text-white p-4">
                <h2 class="text-center mb-4">Admin Dashboard</h2>
                
                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Pending Voters</h5>
                                <p class="card-text h2" id="pendingVotersCount">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Elections</h5>
                                <p class="card-text h2" id="activeElectionsCount">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Votes Cast</h5>
                                <p class="card-text h2" id="totalVotesCount">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card bg-info text-white">
                    <div class="card-header">
                        <h5>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentActivity">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Function to load dashboard data
    function loadDashboardData() {
        // Load pending voters count
        fetch('../actions/admin/get_pending_voters_count.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('pendingVotersCount').textContent = data.count;
            })
            .catch(error => console.error('Error:', error));

        // Load active elections count
        fetch('../actions/admin/get_active_elections_count.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('activeElectionsCount').textContent = data.count;
            })
            .catch(error => console.error('Error:', error));

        // Load total votes count
        fetch('../actions/admin/get_total_votes_count.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalVotesCount').textContent = data.count;
            })
            .catch(error => console.error('Error:', error));

        // Load recent activity
        fetch('../actions/admin/get_recent_activity.php')
            .then(response => response.json())
            .then(data => {
                const activityHtml = data.activities.map(activity => `
                    <div class="activity-item mb-2">
                        <strong>${activity.time}</strong>: ${activity.description}
                    </div>
                `).join('');
                document.getElementById('recentActivity').innerHTML = activityHtml || 'No recent activity';
            })
            .catch(error => console.error('Error:', error));
    }

    // Load data when page loads
    document.addEventListener('DOMContentLoaded', loadDashboardData);
    
    // Refresh data every minute
    setInterval(loadDashboardData, 60000);
    </script>
</body>
</html>