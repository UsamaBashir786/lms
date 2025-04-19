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
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "Teacher deleted successfully.";
    } catch (PDOException $e) {
        $error = "Failed to delete teacher: " . $e->getMessage();
    }
}

// Fetch all teachers
try {
    $stmt = $pdo->query("SELECT id, full_name, email, phone, subject, experience, created_at FROM teachers ORDER BY created_at DESC");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - LMS</title>
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
                <h1 class="mb-4">Manage Teachers</h1>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Add Teacher Button -->
                <div class="mb-3">
                    <a href="add_teacher.php" class="btn btn-primary">Add New Teacher</a>
                </div>

                <!-- Teachers Table -->
                <div class="card">
                    <div class="card-header">
                        <h5>Teachers List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Experience</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($teachers)): ?>
                                    <tr><td colspan="8" class="text-center">No teachers found</td></tr>
                                <?php else: ?>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?php echo $teacher['id']; ?></td>
                                            <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['subject']); ?></td>
                                            <td><?php echo $teacher['experience']; ?> years</td>
                                            <td><?php echo date('M d, Y H:i', strtotime($teacher['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?delete_id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
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
</body Media, Inc. All rights reserved.
</body>
</html>