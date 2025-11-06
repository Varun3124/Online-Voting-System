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
    <title>Manage Candidates - Online Voting System</title>
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
                    <a class="nav-link text-white" href="voters.php">Manage Voters</a>
                    <a class="nav-link active text-white" href="candidates.php">Manage Candidates</a>
                    <a class="nav-link text-white" href="../actions/admin/logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-9 text-white p-4">
                <h2 class="text-center mb-4">Manage Candidates</h2>
                
                <!-- Add New Candidate Button -->
                <button class="btn btn-info mb-3" data-toggle="modal" data-target="#addCandidateModal">
                    Add New Candidate
                </button>

                <!-- Election Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select id="electionFilter" class="form-control">
                            <option value="">All Elections</option>
                        </select>
                    </div>
                </div>

                <!-- Candidates Table -->
                <div class="card bg-info">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="candidatesTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Party</th>
                                        <th>Election</th>
                                        <th>Votes</th>
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

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Candidate</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addCandidateForm">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Party</label>
                            <input type="text" class="form-control" name="party" required>
                        </div>
                        <div class="form-group">
                            <label>Election</label>
                            <select class="form-control" name="election_id" required>
                                <!-- Elections will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" class="form-control-file" name="photo" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-info">Add Candidate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div class="modal fade" id="editCandidateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Candidate</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCandidateForm">
                        <input type="hidden" name="candidate_id">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Party</label>
                            <input type="text" class="form-control" name="party" required>
                        </div>
                        <div class="form-group">
                            <label>Election</label>
                            <select class="form-control" name="election_id" required>
                                <!-- Elections will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Current Photo</label>
                            <div id="currentPhoto"></div>
                        </div>
                        <div class="form-group">
                            <label>New Photo</label>
                            <input type="file" class="form-control-file" name="photo" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-info">Update Candidate</button>
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
        // Load elections for filter and form dropdowns
        function loadElections() {
            fetch('../actions/admin/get_elections.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const elections = data.elections;
                        let options = '<option value="">All Elections</option>';
                        elections.forEach(election => {
                            options += `<option value="${election.id}">${election.title}</option>`;
                        });
                        $('#electionFilter').html(options);
                        $('select[name="election_id"]').html(options);
                    }
                });
        }
        loadElections();

        const table = $('#candidatesTable').DataTable({
            ajax: {
                url: '../actions/admin/get_candidates.php',
                dataSrc: 'candidates'
            },
            columns: [
                { data: 'id' },
                { 
                    data: 'photo',
                    render: function(data) {
                        return data ? 
                            `<img src="../uploads/candidate_photos/${data}" class="img-thumbnail" style="max-width: 50px;">` : 
                            'No photo';
                    }
                },
                { data: 'name' },
                { data: 'party' },
                { data: 'election_title' },
                { data: 'votes' },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary edit-candidate" data-id="${data.id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-candidate" data-id="${data.id}">Delete</button>
                        `;
                    }
                }
            ]
        });

        // Filter by election
        $('#electionFilter').on('change', function() {
            table.column(4).search(this.value).draw();
        });

        // Add Candidate Form Submit
        $('#addCandidateForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../actions/admin/add_candidate.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#addCandidateModal').modal('hide');
                    table.ajax.reload();
                    this.reset();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the candidate');
            });
        });

        // Edit Candidate
        $('#candidatesTable').on('click', '.edit-candidate', function() {
            const candidateId = $(this).data('id');
            fetch(`../actions/admin/get_candidate_details.php?id=${candidateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const candidate = data.candidate;
                        const form = $('#editCandidateForm');
                        form.find('[name=candidate_id]').val(candidate.id);
                        form.find('[name=name]').val(candidate.name);
                        form.find('[name=party]').val(candidate.party);
                        form.find('[name=election_id]').val(candidate.election_id);
                        
                        const photoHtml = candidate.photo ? 
                            `<img src="../uploads/candidate_photos/${candidate.photo}" class="img-thumbnail mb-2" style="max-width: 100px;">` : 
                            'No current photo';
                        $('#currentPhoto').html(photoHtml);
                        
                        $('#editCandidateModal').modal('show');
                    }
                });
        });

        // Edit Candidate Form Submit
        $('#editCandidateForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../actions/admin/update_candidate.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#editCandidateModal').modal('hide');
                    table.ajax.reload();
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the candidate');
            });
        });

        // Delete Candidate
        $('#candidatesTable').on('click', '.delete-candidate', function() {
            const candidateId = $(this).data('id');
            if (confirm('Are you sure you want to delete this candidate? This cannot be undone.')) {
                fetch('../actions/admin/delete_candidate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ candidate_id: candidateId })
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
    });
    </script>
</body>
</html>