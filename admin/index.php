<?php
// session_start();

// Check if admin is logged in (assuming admin authentication is implemented)
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

// Fetch statistics
try {
  // Count total students
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
  $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Count total teachers
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
  $total_teachers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Count total assignments
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM assignments");
  $total_assignments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Count total quizzes
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM quizzes");
  $total_quizzes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Count total meetings
  $stmt = $pdo->query("SELECT COUNT(*) as total FROM meetings");
  $total_meetings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Fetch recent assignments (last 5)
  $stmt = $pdo->query("SELECT a.title, a.due_date, t.full_name 
                         FROM assignments a 
                         JOIN teachers t ON a.teacher_id = t.id 
                         ORDER BY a.created_at DESC LIMIT 5");
  $recent_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch recent quiz attempts (corrected query)
  $stmt = $pdo->query("SELECT q.title, s.full_name, qa.score, qa.end_time 
                         FROM quiz_attempts qa 
                         JOIN quizzes q ON qa.quiz_id = q.quiz_id 
                         JOIN students s ON qa.student_id = s.id 
                         WHERE qa.status = 'completed' 
                         ORDER BY qa.end_time DESC LIMIT 5");
  $recent_quiz_attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      overflow-x: hidden;
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

    .card {
      transition: transform 0.2s;
    }

    .card:hover {
      transform: scale(1.05);
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
        <h1 class="mb-4">Admin Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <div class="card text-white bg-primary">
              <div class="card-body">
                <h5 class="card-title">Total Students</h5>
                <p class="card-text display-4"><?php echo $total_students; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card text-white bg-success">
              <div class="card-body">
                <h5 class="card-title">Total Teachers</h5>
                <p class="card-text display-4"><?php echo $total_teachers; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card text-white bg-info">
              <div class="card-body">
                <h5 class="card-title">Total Assignments</h5>
                <p class="card-text display-4"><?php echo $total_assignments; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card text-white bg-warning">
              <div class="card-body">
                <h5 class="card-title">Total Quizzes</h5>
                <p class="card-text display-4"><?php echo $total_quizzes; ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger">
              <div class="card-body">
                <h5 class="card-title">Total Meetings</h5>
                <p class="card-text display-4"><?php echo $total_meetings; ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Assignments -->
        <div class="card mb-4">
          <div class="card-header">
            <h5>Recent Assignments</h5>
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Teacher</th>
                  <th>Due Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recent_assignments)): ?>
                  <tr>
                    <td colspan="3" class="text-center">No recent assignments</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recent_assignments as $assignment): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                      <td><?php echo htmlspecialchars($assignment['full_name']); ?></td>
                      <td><?php echo date('M d, Y H:i', strtotime($assignment['due_date'])); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Quiz Attempts -->
        <div class="card">
          <div class="card-header">
            <h5>Recent Quiz Attempts</h5>
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Quiz Title</th>
                  <th>Student</th>
                  <th>Score</th>
                  <th>Completed At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recent_quiz_attempts)): ?>
                  <tr>
                    <td colspan="4" class="text-center">No recent quiz attempts</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recent_quiz_attempts as $attempt): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($attempt['title']); ?></td>
                      <td><?php echo htmlspecialchars($attempt['full_name']); ?></td>
                      <td><?php echo number_format($attempt['score'], 2); ?>%</td>
                      <td><?php echo date('M d, Y H:i', strtotime($attempt['end_time'])); ?></td>
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