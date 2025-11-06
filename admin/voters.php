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
    <title>Manage Voters - Online Voting System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
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
                    <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
                    <a class="nav-link text-white" href="elections.php">Manage Elections</a>
                    <a class="nav-link active text-white" href="voters.php">Manage Voters</a>
                    <a class="nav-link text-white" href="candidates.php">Manage Candidates</a>
                    <a class="nav-link text-white" href="../actions/admin/logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 text-white p-4">
                <h2 class="text-center mb-4">Manage Voters</h2>
                
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Voters Table -->
                <div class="card bg-info">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="votersTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Aadhar</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Voter Modal -->
    <div class="modal fade" id="viewVoterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Voter Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="voterDetails">
                        <!-- Voter details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        const table = $('#votersTable').DataTable({
            ajax: {
                url: '../actions/admin/get_voters.php',
                dataSrc: 'voters'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { 
                    data: 'aadhar_number',
                    render: function(data) {
                        return 'XXXX-XXXX-' + data.slice(-4);
                    }
                },
                { data: 'phone' },
                { data: 'email' },
                {
                    data: 'status',
                    render: function(data) {
                        const classes = {
                            'pending': 'badge-warning',
                            'approved': 'badge-success',
                            'rejected': 'badge-danger'
                        };
                        return `<span class="badge ${classes[data]}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        let buttons = `
                            <button class="btn btn-sm btn-primary view-voter" data-id="${data.id}">View</button>
                        `;
                        if (data.status === 'pending') {
                            buttons += `
                                <button class="btn btn-sm btn-success approve-voter" data-id="${data.id}">Approve</button>
                                <button class="btn btn-sm btn-danger reject-voter" data-id="${data.id}">Reject</button>
                            `;
                        }
                        return buttons;
                    }
                }
            ]
        });

        // Filter by status
        $('#statusFilter').on('change', function() {
            table.column(5).search(this.value).draw();
        });

        // View voter details
        $('#votersTable').on('click', '.view-voter', function() {
            const voterId = $(this).data('id');
            fetch(`../actions/admin/get_voter_details.php?id=${voterId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const voter = data.voter;
                        let photoHtml = voter.photo ? 
                            `<img src="../uploads/voter_photos/${voter.photo}" class="img-fluid mb-3" alt="Voter Photo">` : 
                            'No photo uploaded';
                        
                        $('#voterDetails').html(`
                            ${photoHtml}
                            <p><strong>Name:</strong> ${voter.name}</p>
                            <p><strong>Aadhar:</strong> XXXX-XXXX-${voter.aadhar_number.slice(-4)}</p>
                            <p><strong>Date of Birth:</strong> ${voter.dob}</p>
                            <p><strong>Phone:</strong> ${voter.phone || 'Not provided'}</p>
                            <p><strong>Email:</strong> ${voter.email || 'Not provided'}</p>
                            <p><strong>Status:</strong> <span class="badge ${getStatusClass(voter.status)}">${voter.status}</span></p>
                            <p><strong>Registered:</strong> ${new Date(voter.created_at).toLocaleString()}</p>
                        `);
                        $('#viewVoterModal').modal('show');
                    }
                });
        });

        // Approve voter
        $('#votersTable').on('click', '.approve-voter', function() {
            const voterId = $(this).data('id');
            if (confirm('Are you sure you want to approve this voter?')) {
                updateVoterStatus(voterId, 'approved');
            }
        });

        // Reject voter
        $('#votersTable').on('click', '.reject-voter', function() {
            const voterId = $(this).data('id');
            if (confirm('Are you sure you want to reject this voter?')) {
                updateVoterStatus(voterId, 'rejected');
            }
        });

        function updateVoterStatus(voterId, status) {
            fetch('../actions/admin/update_voter_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ voter_id: voterId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    table.ajax.reload();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating voter status');
            });
        }

        function getStatusClass(status) {
            const classes = {
                'pending': 'badge-warning',
                'approved': 'badge-success',
                'rejected': 'badge-danger'
            };
            return classes[status] || 'badge-secondary';
        }
    });
    </script>
</body>
</html>