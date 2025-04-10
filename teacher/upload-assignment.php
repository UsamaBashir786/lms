<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize response array
$response = array('status' => 'error', 'message' => 'Invalid action');

// Database connection
function getConnection() {
    $servername = "localhost";
    $username = "root"; // Replace with your DB username
    $password = ""; // Replace with your DB password
    $dbname = "lms";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Upload file and return path
function uploadFile($file, $directory) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $filename = basename($file['name']);
    $target_dir = $directory;
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $target_dir . $new_filename;

    return move_uploaded_file($file['tmp_name'], $target_path) ? $target_path : null;
}

// Process requests
if (isset($_REQUEST['action'])) {
    $action = sanitizeInput($_REQUEST['action']);
    $conn = getConnection();

    switch ($action) {
        case 'create_assignment':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $teacher_id = (int)$_POST['teacher_id'];
                $due_date = $_POST['due_date'] . ' 23:59:59';
                $total_points = (int)$_POST['total_points'];

                if (empty($title) || empty($description) || $teacher_id <= 0 || empty($due_date) || $total_points <= 0) {
                    $response['message'] = 'Please fill in all required fields.';
                    break;
                }

                $file_path = null;
                if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['size'] > 0) {
                    $file_path = uploadFile($_FILES['assignment_file'], '../uploads/assignments/');
                    if (!$file_path) {
                        $response['message'] = 'Error uploading file.';
                        break;
                    }
                }

                $stmt = $conn->prepare("INSERT INTO assignments (teacher_id, title, description, file_path, total_points, due_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssis", $teacher_id, $title, $description, $file_path, $total_points, $due_date);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Assignment created successfully.';
                    $response['assignment_id'] = $conn->insert_id;
                } else {
                    $response['message'] = 'Error creating assignment: ' . $conn->error;
                }
                $stmt->close();
            }
            break;

        case 'update_assignment':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $assignment_id = (int)$_POST['assignment_id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $teacher_id = (int)$_POST['teacher_id'];
                $due_date = $_POST['due_date'] . ' 23:59:59';
                $total_points = (int)$_POST['total_points'];

                if (empty($title) || empty($description) || $teacher_id <= 0 || empty($due_date) || $total_points <= 0 || $assignment_id <= 0) {
                    $response['message'] = 'Please fill in all required fields.';
                    break;
                }

                $fileStmt = $conn->prepare("SELECT file_path FROM assignments WHERE assignment_id = ?");
                $fileStmt->bind_param("i", $assignment_id);
                $fileStmt->execute();
                $fileResult = $fileStmt->get_result();
                $currentFile = $fileResult->fetch_assoc();
                $file_path = $currentFile['file_path'];
                $fileStmt->close();

                if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['size'] > 0) {
                    $new_file_path = uploadFile($_FILES['assignment_file'], '../uploads/assignments/');
                    if (!$new_file_path) {
                        $response['message'] = 'Error uploading file.';
                        break;
                    }
                    if ($file_path && file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $file_path = $new_file_path;
                }

                $stmt = $conn->prepare("UPDATE assignments SET teacher_id = ?, title = ?, description = ?, file_path = ?, total_points = ?, due_date = ? WHERE assignment_id = ?");
                $stmt->bind_param("isssisi", $teacher_id, $title, $description, $file_path, $total_points, $due_date, $assignment_id);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Assignment updated successfully.';
                } else {
                    $response['message'] = 'Error updating assignment: ' . $conn->error;
                }
                $stmt->close();
            }
            break;

        case 'delete_assignment':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {
                $assignment_id = (int)$_POST['assignment_id'];
                $conn->begin_transaction();
                try {
                    $fileStmt = $conn->prepare("SELECT file_path FROM assignments WHERE assignment_id = ?");
                    $fileStmt->bind_param("i", $assignment_id);
                    $fileStmt->execute();
                    $fileResult = $fileStmt->get_result();
                    $file = $fileResult->fetch_assoc();
                    $fileStmt->close();

                    $submissionFilesStmt = $conn->prepare("SELECT file_path FROM assignment_submissions WHERE assignment_id = ? AND file_path IS NOT NULL");
                    $submissionFilesStmt->bind_param("i", $assignment_id);
                    $submissionFilesStmt->execute();
                    $submissionFilesResult = $submissionFilesStmt->get_result();
                    $submissionFiles = [];
                    while ($row = $submissionFilesResult->fetch_assoc()) {
                        if ($row['file_path']) $submissionFiles[] = $row['file_path'];
                    }
                    $submissionFilesStmt->close();

                    $deleteSubmissionsStmt = $conn->prepare("DELETE FROM assignment_submissions WHERE assignment_id = ?");
                    $deleteSubmissionsStmt->bind_param("i", $assignment_id);
                    $deleteSubmissionsStmt->execute();
                    $deleteSubmissionsStmt->close();

                    $deleteStmt = $conn->prepare("DELETE FROM assignments WHERE assignment_id = ?");
                    $deleteStmt->bind_param("i", $assignment_id);
                    $deleteStmt->execute();
                    $deleteStmt->close();

                    $conn->commit();
                    if ($file && $file['file_path'] && file_exists($file['file_path'])) {
                        unlink($file['file_path']);
                    }
                    foreach ($submissionFiles as $submissionFile) {
                        if (file_exists($submissionFile)) unlink($submissionFile);
                    }
                    $response['status'] = 'success';
                    $response['message'] = 'Assignment deleted successfully.';
                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Error deleting assignment: ' . $e->getMessage();
                }
            }
            break;

        case 'get_assignment':
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['assignment_id'])) {
                $assignment_id = (int)$_GET['assignment_id'];
                $stmt = $conn->prepare("SELECT a.*, t.full_name as teacher_name FROM assignments a JOIN teachers t ON a.teacher_id = t.id WHERE a.assignment_id = ?");
                $stmt->bind_param("i", $assignment_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $response['status'] = 'success';
                    $response['assignment'] = $result->fetch_assoc();
                } else {
                    $response['message'] = 'Assignment not found.';
                }
                $stmt->close();
            }
            break;

        case 'get_submissions':
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['assignment_id'])) {
                $assignment_id = (int)$_GET['assignment_id'];
                $assignmentStmt = $conn->prepare("SELECT a.*, t.full_name as teacher_name FROM assignments a JOIN teachers t ON a.teacher_id = t.id WHERE a.assignment_id = ?");
                $assignmentStmt->bind_param("i", $assignment_id);
                $assignmentStmt->execute();
                $assignmentResult = $assignmentStmt->get_result();

                if ($assignmentResult->num_rows > 0) {
                    $assignment = $assignmentResult->fetch_assoc();
                    $submissionsStmt = $conn->prepare("SELECT s.*, st.full_name as student_name FROM assignment_submissions s JOIN students st ON s.student_id = st.id WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC");
                    $submissionsStmt->bind_param("i", $assignment_id);
                    $submissionsStmt->execute();
                    $submissionsResult = $submissionsStmt->get_result();
                    $submissions = [];
                    while ($submission = $submissionsResult->fetch_assoc()) {
                        $submissions[] = $submission;
                    }
                    $response['status'] = 'success';
                    $response['assignment'] = $assignment;
                    $response['submissions'] = $submissions;
                    $submissionsStmt->close();
                } else {
                    $response['message'] = 'Assignment not found.';
                }
                $assignmentStmt->close();
            }
            break;

        case 'get_submission':
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['submission_id'])) {
                $submission_id = (int)$_GET['submission_id'];
                $stmt = $conn->prepare("SELECT s.*, st.full_name as student_name, a.* FROM assignment_submissions s JOIN students st ON s.student_id = st.id JOIN assignments a ON s.assignment_id = a.assignment_id WHERE s.submission_id = ?");
                $stmt->bind_param("i", $submission_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $data = $result->fetch_assoc();
                    $submission = [
                        'submission_id' => $data['submission_id'],
                        'student_id' => $data['student_id'],
                        'student_name' => $data['student_name'],
                        'submission_text' => $data['submission_text'],
                        'file_path' => $data['file_path'],
                        'submitted_at' => $data['submitted_at'],
                        'grade' => $data['grade'],
                        'feedback' => $data['feedback'],
                        'graded_at' => $data['graded_at']
                    ];
                    $assignment = [
                        'assignment_id' => $data['assignment_id'],
                        'teacher_id' => $data['teacher_id'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'file_path' => $data['file_path'],
                        'total_points' => $data['total_points'],
                        'due_date' => $data['due_date'],
                        'created_at' => $data['created_at']
                    ];
                    $response['status'] = 'success';
                    $response['submission'] = $submission;
                    $response['assignment'] = $assignment;
                } else {
                    $response['message'] = 'Submission not found.';
                }
                $stmt->close();
            }
            break;

        case 'grade_submission':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $submission_id = (int)$_POST['submission_id'];
                $grade = (int)$_POST['grade'];
                $feedback = isset($_POST['feedback']) ? sanitizeInput($_POST['feedback']) : '';

                if ($submission_id <= 0 || $grade < 0) {
                    $response['message'] = 'Invalid submission data.';
                    break;
                }

                $maxPointsStmt = $conn->prepare("SELECT a.total_points FROM assignments a JOIN assignment_submissions s ON a.assignment_id = s.assignment_id WHERE s.submission_id = ?");
                $maxPointsStmt->bind_param("i", $submission_id);
                $maxPointsStmt->execute();
                $maxPointsResult = $maxPointsStmt->get_result();
                if ($maxPointsResult->num_rows > 0) {
                    $maxPoints = $maxPointsResult->fetch_assoc()['total_points'];
                    if ($grade > $maxPoints) {
                        $response['message'] = "Grade cannot exceed maximum points ($maxPoints).";
                        $maxPointsStmt->close();
                        break;
                    }
                } else {
                    $response['message'] = 'Assignment not found.';
                    $maxPointsStmt->close();
                    break;
                }
                $maxPointsStmt->close();

                $now = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("UPDATE assignment_submissions SET grade = ?, feedback = ?, graded_at = ? WHERE submission_id = ?");
                $stmt->bind_param("issi", $grade, $feedback, $now, $submission_id);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Submission graded successfully.';
                } else {
                    $response['message'] = 'Error updating grade: ' . $conn->error;
                }
                $stmt->close();
            }
            break;
    }
    $conn->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .assignment-card { transition: all 0.3s ease; }
        .assignment-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,.12), 0 4px 8px rgba(0,0,0,.06); }
        .action-btn { width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .status-badge { position: absolute; top: 10px; right: 10px; }
        .submission-list { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Placeholder -->
            <!-- <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link active" href="#">Assignments</a></li>
                    </ul>
                </div>
            </nav> -->

            <!-- Main Content -->
            <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4" id="mainContent">
                <div class="bg-success text-white p-4 mb-4  d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Assignment Management</h2>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                        <i class="fas fa-plus-circle me-2"></i>Create New Assignment
                    </button>
                </div>

                <!-- Filter Controls -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" id="searchAssignment" placeholder="Search by title...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sort By</label>
                                <select class="form-select" id="sortAssignments">
                                    <option value="latest">Latest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="title_asc">Title (A-Z)</option>
                                    <option value="title_desc">Title (Z-A)</option>
                                    <option value="due_date">Due Date</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by Teacher</label>
                                <select class="form-select" id="filterTeacher">
                                    <option value="">All Teachers</option>
                                    <?php
                                    $conn = getConnection();
                                    $teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name ASC");
                                    while ($teacher = $teacherResult->fetch_assoc()) {
                                        echo "<option value='{$teacher['id']}'>{$teacher['full_name']}</option>";
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyFilters">
                                    <i class="fas fa-filter me-2"></i>Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment List -->
                <div class="row g-4" id="assignmentList">
                    <?php
                    $conn = getConnection();
                    $assignmentQuery = "SELECT a.*, t.full_name as teacher_name, 
                        (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.assignment_id) as submission_count
                        FROM assignments a JOIN teachers t ON a.teacher_id = t.id ORDER BY a.created_at DESC";
                    $assignmentResult = $conn->query($assignmentQuery);

                    if ($assignmentResult && $assignmentResult->num_rows > 0) {
                        while ($assignment = $assignmentResult->fetch_assoc()) {
                            $isDue = strtotime($assignment['due_date']) < time();
                            $gradedQuery = "SELECT COUNT(*) as graded_count FROM assignment_submissions WHERE assignment_id = {$assignment['assignment_id']} AND grade IS NOT NULL";
                            $gradedResult = $conn->query($gradedQuery);
                            $gradedCount = $gradedResult->fetch_assoc()['graded_count'];
                            $gradingProgress = $assignment['submission_count'] > 0 ? round(($gradedCount / $assignment['submission_count']) * 100) : 0;
                            ?>
                            <div class="col-md-6 col-lg-4 assignment-item" data-teacher="<?php echo $assignment['teacher_id']; ?>">
                                <div class="card assignment-card h-100 position-relative">
                                    <span class="badge <?php echo $isDue ? 'bg-danger' : 'bg-success'; ?> status-badge">
                                        <?php echo $isDue ? 'Due Date Passed' : 'Active'; ?>
                                    </span>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate"><?php echo $assignment['title']; ?></h5>
                                        <p class="text-muted mb-2"><i class="fas fa-user-tie me-1"></i><?php echo $assignment['teacher_name']; ?></p>
                                        <p class="card-text text-truncate"><?php echo $assignment['description']; ?></p>
                                        <div class="d-flex justify-content-between mt-3 mb-3">
                                            <div><i class="fas fa-calendar-alt text-primary me-1"></i>Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></div>
                                            <div><i class="fas fa-star text-warning me-1"></i><?php echo $assignment['total_points']; ?> points</div>
                                        </div>
                                        <div class="progress mb-3" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $gradingProgress; ?>%;"><?php echo $gradingProgress; ?>%</div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 text-muted small">
                                            <span><?php echo $gradedCount; ?>/<?php echo $assignment['submission_count']; ?> graded</span>
                                            <span><?php echo $gradingProgress; ?>% complete</span>
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="editAssignment(<?php echo $assignment['assignment_id']; ?>)">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="viewSubmissions(<?php echo $assignment['assignment_id']; ?>)">
                                                <i class="fas fa-clipboard-check me-1"></i>Submissions
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger action-btn" onclick="deleteAssignment(<?php echo $assignment['assignment_id']; ?>)">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted small">
                                        <i class="fas fa-clock me-1"></i>Created: <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info">No assignments found. Create your first assignment!</div></div>';
                    }
                    $conn->close();
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Assignment Modal -->
    <div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Create New Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createAssignmentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create_assignment">
                        <div class="mb-3">
                            <label class="form-label">Assignment Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Assigned Teacher</label>
                                <select class="form-select" name="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    <?php
                                    $conn = getConnection();
                                    $teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name ASC");
                                    while ($teacher = $teacherResult->fetch_assoc()) {
                                        echo "<option value='{$teacher['id']}'>{$teacher['full_name']}</option>";
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total Points</label>
                                <input type="number" class="form-control" name="total_points" value="100" min="1" max="1000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assignment File (Optional)</label>
                            <input type="file" class="form-control" name="assignment_file">
                            <small class="text-muted">Upload instructions or materials (PDF, DOC, etc.)</small>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Create Assignment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_assignment">
                        <input type="hidden" name="assignment_id" id="edit_assignment_id">
                        <div class="mb-3">
                            <label class="form-label">Assignment Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Assigned Teacher</label>
                                <select class="form-select" name="teacher_id" id="edit_teacher_id" required>
                                    <option value="">Select Teacher</option>
                                    <?php
                                    $conn = getConnection();
                                    $teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name ASC");
                                    while ($teacher = $teacherResult->fetch_assoc()) {
                                        echo "<option value='{$teacher['id']}'>{$teacher['full_name']}</option>";
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" id="edit_due_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total Points</label>
                                <input type="number" class="form-control" name="total_points" id="edit_total_points" min="1" max="1000" required>
                            </div>
                        </div>
                        <div class="mb-3" id="current_file_container">
                            <label class="form-label">Current File</label>
                            <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                                <span id="current_file_name">No file attached</span>
                                <a href="#" id="download_file" class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Replace File (Optional)</label>
                            <input type="file" class="form-control" name="assignment_file" id="edit_file">
                            <small class="text-muted">Leave empty to keep current file</small>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Submissions Modal -->
    <div class="modal fade" id="viewSubmissionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Assignment Submissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="assignment-details mb-4">
                        <h3 id="assignment_title" class="mb-2"></h3>
                        <div class="row">
                            <div class="col-md-8">
                                <p id="assignment_description" class="text-muted"></p>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-calendar-alt text-primary"></i> Due Date:</span>
                                            <span id="assignment_due_date" class="fw-bold"></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><i class="fas fa-star text-warning"></i> Total Points:</span>
                                            <span id="assignment_points" class="fw-bold"></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fas fa-user-tie text-info"></i> Teacher:</span>
                                            <span id="assignment_teacher" class="fw-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <a href="#" id="assignment_file_link" class="btn btn-outline-primary" target="_blank" style="display:none;">
                                <i class="fas fa-file-download me-1"></i>Download Assignment File
                            </a>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Student Submissions</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary active" id="filterAll">All</button>
                            <button type="button" class="btn btn-outline-success" id="filterGraded">Graded</button>
                            <button type="button" class="btn btn-outline-warning" id="filterUngraded">Ungraded</button>
                            <button type="button" class="btn btn-outline-danger" id="filterLate">Late</button>
                        </div>
                        <button type="button" class="btn btn-success" id="exportSubmissions">
                            <i class="fas fa-download me-2"></i>Export to CSV
                        </button>
                    </div>
                    <div class="table-responsive submission-list">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th width="200">Feedback</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="submissions_table"></tbody>
                        </table>
                    </div>
                    <div id="no_submissions_message" class="alert alert-info mt-3" style="display:none;">No submissions found.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Submission Modal -->
    <div class="modal fade" id="gradeSubmissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Grade Submission</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Student Information</h6>
                            <p><strong>Name:</strong> <span id="grade_student_name"></span></p>
                            <p><strong>Submission Date:</strong> <span id="grade_submission_date"></span></p>
                            <p><strong>Status:</strong> <span id="grade_submission_status"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Assignment Information</h6>
                            <p><strong>Title:</strong> <span id="grade_assignment_title"></span></p>
                            <p><strong>Due Date:</strong> <span id="grade_assignment_due_date"></span></p>
                            <p><strong>Total Points:</strong> <span id="grade_total_points"></span></p>
                        </div>
                    </div>
                    <div class="mb-4" id="submission_content_container">
                        <h6>Submission Content</h6>
                        <div class="border rounded p-3 bg-light">
                            <p id="submission_text" class="mb-0"></p>
                        </div>
                    </div>
                    <div class="mb-4" id="submission_file_container" style="display:none;">
                        <h6>Submission File</h6>
                        <div class="border rounded p-3 bg-light d-flex justify-content-between align-items-center">
                            <span id="submission_file_name"></span>
                            <a href="#" id="download_submission" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                    <form id="gradeForm" method="POST">
                        <input type="hidden" name="action" value="grade_submission">
                        <input type="hidden" name="submission_id" id="grade_submission_id">
                        <div class="mb-3">
                            <label class="form-label">Grade (out of <span id="max_points"></span> points)</label>
                            <input type="number" class="form-control" name="grade" id="grade_input" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Feedback</label>
                            <textarea class="form-control" name="feedback" id="feedback_input" rows="4"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Grade</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAssignment(assignmentId) {
            fetch(`?action=get_assignment&assignment_id=${assignmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('edit_assignment_id').value = data.assignment.assignment_id;
                        document.getElementById('edit_title').value = data.assignment.title;
                        document.getElementById('edit_description').value = data.assignment.description;
                        document.getElementById('edit_teacher_id').value = data.assignment.teacher_id;
                        document.getElementById('edit_due_date').value = data.assignment.due_date.split(' ')[0];
                        document.getElementById('edit_total_points').value = data.assignment.total_points;
                        const currentFileName = document.getElementById('current_file_name');
                        const downloadFileLink = document.getElementById('download_file');
                        if (data.assignment.file_path) {
                            currentFileName.textContent = data.assignment.file_path.split('/').pop();
                            downloadFileLink.href = data.assignment.file_path;
                        } else {
                            currentFileName.textContent = 'No file attached';
                            downloadFileLink.href = '#';
                        }
                        new bootstrap.Modal(document.getElementById('editAssignmentModal')).show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Fetch error: ' + error));
        }

        function viewSubmissions(assignmentId) {
            fetch(`?action=get_submissions&assignment_id=${assignmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('assignment_title').textContent = data.assignment.title;
                        document.getElementById('assignment_description').textContent = data.assignment.description;
                        document.getElementById('assignment_due_date').textContent = new Date(data.assignment.due_date).toLocaleDateString();
                        document.getElementById('assignment_points').textContent = data.assignment.total_points;
                        document.getElementById('assignment_teacher').textContent = data.assignment.teacher_name;
                        const fileLink = document.getElementById('assignment_file_link');
                        if (data.assignment.file_path) {
                            fileLink.href = data.assignment.file_path;
                            fileLink.style.display = 'inline-block';
                        } else {
                            fileLink.style.display = 'none';
                        }

                        const submissionsTable = document.getElementById('submissions_table');
                        submissionsTable.innerHTML = '';
                        if (data.submissions.length > 0) {
                            document.getElementById('no_submissions_message').style.display = 'none';
                            data.submissions.forEach(submission => {
                                const submissionDate = new Date(submission.submitted_at);
                                const dueDate = new Date(data.assignment.due_date);
                                const isLate = submissionDate > dueDate;
                                const row = `
                                    <tr class="submission-row ${submission.grade !== null ? 'graded' : 'ungraded'} ${isLate ? 'late' : ''}">
                                        <td>${submission.student_name}</td>
                                        <td>${submissionDate.toLocaleString()} ${isLate ? '<span class="badge bg-danger ms-2">Late</span>' : ''}</td>
                                        <td>${submission.grade !== null ? '<span class="badge bg-success">Graded</span>' : '<span class="badge bg-warning text-dark">Ungraded</span>'}</td>
                                        <td>${submission.grade !== null ? `<strong>${submission.grade} / ${data.assignment.total_points}</strong>` : '-'}</td>
                                        <td><div class="text-truncate" style="max-width: 200px;">${submission.feedback || '-'}</div></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="gradeSubmission(${submission.submission_id})">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                submissionsTable.insertAdjacentHTML('beforeend', row);
                            });
                        } else {
                            document.getElementById('no_submissions_message').style.display = 'block';
                        }
                        new bootstrap.Modal(document.getElementById('viewSubmissionsModal')).show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Fetch error: ' + error));
        }

        function gradeSubmission(submissionId) {
            fetch(`?action=get_submission&submission_id=${submissionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('grade_submission_id').value = data.submission.submission_id;
                        document.getElementById('grade_student_name').textContent = data.submission.student_name;
                        document.getElementById('grade_submission_date').textContent = new Date(data.submission.submitted_at).toLocaleString();
                        const isLate = new Date(data.submission.submitted_at) > new Date(data.assignment.due_date);
                        document.getElementById('grade_submission_status').innerHTML = isLate ? '<span class="badge bg-danger">Late</span>' : '<span class="badge bg-success">On Time</span>';
                        document.getElementById('grade_assignment_title').textContent = data.assignment.title;
                        document.getElementById('grade_assignment_due_date').textContent = new Date(data.assignment.due_date).toLocaleDateString();
                        document.getElementById('grade_total_points').textContent = data.assignment.total_points;
                        document.getElementById('max_points').textContent = data.assignment.total_points;
                        document.getElementById('grade_input').max = data.assignment.total_points;

                        if (data.submission.grade !== null) {
                            document.getElementById('grade_input').value = data.submission.grade;
                            document.getElementById('feedback_input').value = data.submission.feedback || '';
                        } else {
                            document.getElementById('grade_input').value = '';
                            document.getElementById('feedback_input').value = '';
                        }

                        const submissionTextContainer = document.getElementById('submission_content_container');
                        if (data.submission.submission_text) {
                            document.getElementById('submission_text').textContent = data.submission.submission_text;
                            submissionTextContainer.style.display = 'block';
                        } else {
                            submissionTextContainer.style.display = 'none';
                        }

                        const submissionFileContainer = document.getElementById('submission_file_container');
                        if (data.submission.file_path) {
                            document.getElementById('submission_file_name').textContent = data.submission.file_path.split('/').pop();
                            document.getElementById('download_submission').href = data.submission.file_path;
                            submissionFileContainer.style.display = 'block';
                        } else {
                            submissionFileContainer.style.display = 'none';
                        }

                        new bootstrap.Modal(document.getElementById('gradeSubmissionModal')).show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Fetch error: ' + error));
        }

        function deleteAssignment(assignmentId) {
            if (confirm('Are you sure you want to delete this assignment and all its submissions?')) {
                fetch('?', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_assignment&assignment_id=${assignmentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Assignment deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('Fetch error: ' + error));
            }
        }

        document.getElementById('createAssignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('?', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Assignment created!');
                    bootstrap.Modal.getInstance(document.getElementById('createAssignmentModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => alert('Submit error: ' + error));
        });

        document.getElementById('editAssignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('?', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Assignment updated!');
                    bootstrap.Modal.getInstance(document.getElementById('editAssignmentModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => alert('Submit error: ' + error));
        });

        document.getElementById('gradeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('?', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Submission graded!');
                    bootstrap.Modal.getInstance(document.getElementById('gradeSubmissionModal')).hide();
                    viewSubmissions(document.getElementById('grade_submission_id').value);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => alert('Submit error: ' + error));
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchAssignment').value.toLowerCase();
            const sortBy = document.getElementById('sortAssignments').value;
            const teacherFilter = document.getElementById('filterTeacher').value;
            const assignmentItems = document.querySelectorAll('.assignment-item');

            assignmentItems.forEach(item => {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                const teacherId = item.dataset.teacher;
                const matchesSearch = searchTerm === '' || title.includes(searchTerm);
                const matchesTeacher = teacherFilter === '' || teacherId === teacherFilter;
                item.style.display = matchesSearch && matchesTeacher ? 'block' : 'none';
            });

            const assignmentList = document.getElementById('assignmentList');
            const items = Array.from(assignmentList.children).filter(item => item.style.display !== 'none');
            items.sort((a, b) => {
                if (sortBy === 'latest') return new Date(b.querySelector('.card-footer').textContent.split('Created: ')[1]) - new Date(a.querySelector('.card-footer').textContent.split('Created: ')[1]);
                if (sortBy === 'oldest') return new Date(a.querySelector('.card-footer').textContent.split('Created: ')[1]) - new Date(b.querySelector('.card-footer').textContent.split('Created: ')[1]);
                if (sortBy === 'title_asc') return a.querySelector('.card-title').textContent.localeCompare(b.querySelector('.card-title').textContent);
                if (sortBy === 'title_desc') return b.querySelector('.card-title').textContent.localeCompare(a.querySelector('.card-title').textContent);
                if (sortBy === 'due_date') return new Date(a.querySelector('.text-muted').textContent.split('Due: ')[1]) - new Date(b.querySelector('.text-muted').textContent.split('Due: ')[1]);
                return 0;
            });
            items.forEach(item => assignmentList.appendChild(item));
        });

        ['filterAll', 'filterGraded', 'filterUngraded', 'filterLate'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                filterSubmissions(id.replace('filter', '').toLowerCase());
                document.querySelectorAll('#viewSubmissionsModal .btn-group .btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        function filterSubmissions(filter) {
            const rows = document.querySelectorAll('.submission-row');
            rows.forEach(row => {
                row.style.display = filter === 'all' || row.classList.contains(filter) ? '' : 'none';
            });
        }

        document.getElementById('exportSubmissions').addEventListener('click', function() {
            const assignmentTitle = document.getElementById('assignment_title').textContent;
            const rows = document.querySelectorAll('#submissions_table tr');
            if (rows.length === 0) return alert('No data to export.');
            let csv = 'Student,Submission Date,Status,Grade,Feedback\n';
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cols = row.querySelectorAll('td');
                    csv += `"${cols[0].textContent.trim()}","${cols[1].textContent.trim().replace(/Late/g, '').trim()}","${cols[2].textContent.trim()}","${cols[3].textContent.trim()}","${cols[4].textContent.trim()}"\n`;
                }
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${assignmentTitle.replace(/\s+/g, '_')}_submissions.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    </script>
</body>
</html>