<?php
// session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//   header("Location: login.php");
//   exit();
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
    $stmt = $pdo->prepare("DELETE FROM meetings WHERE meeting_id = ?");
    $stmt->execute([$delete_id]);
    $success = "Meeting deleted successfully.";
  } catch (PDOException $e) {
    $error = "Failed to delete meeting: " . $e->getMessage();
  }
}

// Fetch all meetings
try {
  $stmt = $pdo->query("SELECT m.meeting_id, m.title, m.scheduled_datetime, m.duration, m.status, t.full_name 
                         FROM meetings m 
                         JOIN teachers t ON m.teacher_id = t.id 
                         ORDER BY m.created_at DESC");
  $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Meetings - LMS</title>
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

    .table th,
    .table td {
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
        <h1 class="mb-4">Manage Meetings</h1>

        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Meeting Button -->
        <div class="mb-3">
          <a href="add_meeting.php" class="btn btn-primary">Add New Meeting</a>
        </div>

        <!-- Meetings Table -->
        <div class="card">
          <div class="card-header">
            <h5>Meetings List</h5>
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Teacher</th>
                  <th>Scheduled Date</th>
                  <th>Duration (min)</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($meetings)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No meetings found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($meetings as $meeting): ?>
                    <tr>
                      <td><?php echo $meeting['meeting_id']; ?></td>
                      <td><?php echo htmlspecialchars($meeting['title']); ?></td>
                      <td><?php echo htmlspecialchars($meeting['full_name']); ?></td>
                      <td><?php echo date('M d, Y H:i', strtotime($meeting['scheduled_datetime'])); ?></td>
                      <td><?php echo $meeting['duration']; ?></td>
                      <td><?php echo ucfirst($meeting['status']); ?></td>
                      <td>
                        <a href="edit_meeting.php?id=<?php echo $meeting['meeting_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete_id=<?php echo $meeting['meeting_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this meeting?');">Delete</a>
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