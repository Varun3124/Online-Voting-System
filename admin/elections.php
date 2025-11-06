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
    <title>Manage Elections - Online Voting System</title>
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
                    <a class="nav-link active text-white" href="elections.php">Manage Elections</a>
                    <a class="nav-link text-white" href="voters.php">Manage Voters</a>
                    <a class="nav-link text-white" href="candidates.php">Manage Candidates</a>
                    <a class="nav-link text-white" href="../actions/admin/logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 text-white p-4">
                <h2 class="text-center mb-4">Manage Elections</h2>
                
                <!-- Add New Election Button -->
                <button class="btn btn-info mb-3" data-toggle="modal" data-target="#addElectionModal">
                    Add New Election
                </button>

                <!-- Elections Table -->
                <div class="card bg-info">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="electionsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
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

    <!-- Add Election Modal -->
    <div class="modal fade" id="addElectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Election</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addElectionForm">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Constituency</label>
                            <input type="text" class="form-control" name="constituency">
                        </div>
                        <div class="form-group">
                            <label>Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="end_date" required>
                        </div>
                        <button type="submit" class="btn btn-info">Create Election</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Election Modal -->
    <div class="modal fade" id="editElectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Election</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editElectionForm">
                        <input type="hidden" name="election_id">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Constituency</label>
                            <input type="text" class="form-control" name="constituency">
                        </div>
                        <div class="form-group">
                            <label>Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="end_date" required>
                        </div>
                        <button type="submit" class="btn btn-info">Update Election</button>
                    </form>
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
        const table = $('#electionsTable').DataTable({
            ajax: {
                url: '../actions/admin/get_elections.php',
                dataSrc: 'elections'
            },
            columns: [
                { data: 'id' },
                { data: 'title' },
                { 
                    data: 'description',
                    render: function(data) {
                        return data ? data.substring(0, 50) + '...' : '';
                    }
                },
                { 
                    data: 'start_date',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                { 
                    data: 'end_date',
                    render: function(data) {
                        return new Date(data).toLocaleString();
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        const classes = {
                            'draft': 'badge-secondary',
                            'active': 'badge-success',
                            'completed': 'badge-info'
                        };
                        return `<span class="badge ${classes[data]}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        let buttons = `
                            <button class="btn btn-sm btn-primary edit-election" data-id="${data.id}">Edit</button>
                        `;
                        if (data.status === 'draft') {
                            buttons += `
                                <button class="btn btn-sm btn-success start-election" data-id="${data.id}">Start</button>
                                <button class="btn btn-sm btn-danger delete-election" data-id="${data.id}">Delete</button>
                            `;
                        } else if (data.status === 'active') {
                            buttons += `
                                <button class="btn btn-sm btn-warning end-election" data-id="${data.id}">End</button>
                            `;
                        }
                        return buttons;
                    }
                }
            ]
        });

        // Add Election Form Submit
        $('#addElectionForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../actions/admin/add_election.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#addElectionModal').modal('hide');
                    table.ajax.reload();
                    this.reset();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the election');
            });
        });

        // Edit Election
        $('#electionsTable').on('click', '.edit-election', function() {
            const electionId = $(this).data('id');
            fetch(`../actions/admin/get_election_details.php?id=${electionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const election = data.election;
                        const form = $('#editElectionForm');
                        form.find('[name=election_id]').val(election.id);
                        form.find('[name=title]').val(election.title);
                        form.find('[name=description]').val(election.description);
                        form.find('[name=constituency]').val(election.constituency);
                        form.find('[name=start_date]').val(election.start_date.slice(0, 16));
                        form.find('[name=end_date]').val(election.end_date.slice(0, 16));
                        $('#editElectionModal').modal('show');
                    }
                });
        });

        // Edit Election Form Submit
        $('#editElectionForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../actions/admin/update_election.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#editElectionModal').modal('hide');
                    table.ajax.reload();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the election');
            });
        });

        // Start Election
        $('#electionsTable').on('click', '.start-election', function() {
            const electionId = $(this).data('id');
            if (confirm('Are you sure you want to start this election?')) {
                updateElectionStatus(electionId, 'active');
            }
        });

        // End Election
        $('#electionsTable').on('click', '.end-election', function() {
            const electionId = $(this).data('id');
            if (confirm('Are you sure you want to end this election?')) {
                updateElectionStatus(electionId, 'completed');
            }
        });

        // Delete Election
        $('#electionsTable').on('click', '.delete-election', function() {
            const electionId = $(this).data('id');
            if (confirm('Are you sure you want to delete this election? This cannot be undone.')) {
                fetch('../actions/admin/delete_election.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ election_id: electionId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        table.ajax.reload();
                    }
                    alert(data.message);
                });
            }
        });

        function updateElectionStatus(electionId, status) {
            fetch('../actions/admin/update_election_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ election_id: electionId, status: status })
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
                alert('An error occurred while updating election status');
            });
        }
    });
    </script>
</body>
</html>