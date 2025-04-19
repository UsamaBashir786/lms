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
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
        $stmt->execute([$delete_id]);
        $success = "Quiz deleted successfully.";
    } catch (PDOException $e) {
        $error = "Failed to delete quiz: " . $e->getMessage();
    }
}

// Fetch all quizzes
try {
    $stmt = $pdo->query("SELECT q.quiz_id, q.title, q.time_limit_minutes, q.pass_percentage, t.full_name 
                         FROM quizzes q 
                         JOIN teachers t ON q.teacher_id = t.id 
                         ORDER BY q.created_at DESC");
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - LMS</title>
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
                <a href="index.php">Dashboard</a>
                <a href="manage_students.php">Manage Students</a>
                <a href="manage_teachers.php">Manage Teachers</a>
                <a href="manage_assignments.php">Manage Assignments</a>
                <a href="manage_quizzes.php">Manage Quizzes</a>
                <a href="manage_meetings.php">Manage Meetings</a>
                <a href="logout.php">Logout</a>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 p-4">
                <h1 class="mb-4">Manage Quizzes</h1>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Add Quiz Button -->
                <div class="mb-3">
                    <a href="add_quiz.php" class="btn btn-primary">Add New Quiz</a>
                </div>

                <!-- Quizzes Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>Quizzes List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Teacher</th>
                                    <th>Time Limit (min)</th>
                                    <th>Pass Percentage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($quizzes)): ?>
                                    <tr><td colspan="6" class="text-center">No quizzes found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <tr>
                                            <td><?php echo $quiz['quiz_id']; ?></td>
                                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                            <td><?php echo htmlspecialchars($quiz['full_name']); ?></td>
                                            <td><?php echo $quiz['time_limit_minutes'] ?: 'No limit'; ?></td>
                                            <td><?php echo $quiz['pass_percentage']; ?>%</td>
                                            <td>
                                                <a href="edit_quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
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