<?php
// session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Database connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=lms", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    try {
        $delete_id = $_GET['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE assignment_id = ?");
        $stmt->execute([$delete_id]);
        $success = "Assignment deleted successfully.";
    } catch (PDOException $e) {
        $error = "Failed to delete assignment: " . $e->getMessage();
    }
}

// Fetch all assignments
try {
    $stmt = $pdo->query("SELECT a.assignment_id, a.title, a.due_date, a.total_points, t.full_name 
                         FROM assignments a 
                         JOIN teachers t ON a.teacher_id = t.id 
                         ORDER BY a.created_at DESC");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar">
                <h4 class="text-center">Admin Panel</h4>
                <a href="index.php">Dashboard AIML</a>
                <a href="manage_students.php">Manage Students</a>
                <a href="manage_teachers.php">Manage Teachers</a>
                <a href="manage_assignments.php">Manage Assignments</a>
                <a href="manage_quizzes.php">Manage Quizzes</a>
                <a href="manage_meetings.php">Manage Meetings</a>
                <a href="logout.php">Logout</a>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 p-4">
                <h1 class="mb-4">Manage Assignments</h1>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Add Assignment Button -->
                <div class="mb-3">
                    <a href="add_assignment.php" class="btn btn-primary">Add New Assignment</a>
                </div>

                <!-- Assignments Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>Assignments List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Teacher</th>
                                    <th>Due Date</th>
                                    <th>Total Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr><td colspan="6" class="text-center">No assignments found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo $assignment['assignment_id']; ?></td>
                                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                            <td><?php echo htmlspecialchars($assignment['full_name']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($assignment['due_date'])); ?></td>
                                            <td><?php echo $assignment['total_points']; ?></td>
                                            <td>
                                                <a href="edit_assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete_id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>