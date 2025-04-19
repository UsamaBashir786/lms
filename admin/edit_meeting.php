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

// Fetch meeting data
$meeting = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: manage_meetings.php");
  exit();
}
try {
  $stmt = $pdo->prepare("SELECT meeting_id, teacher_id, title, description, room_id, scheduled_datetime, duration, status FROM meetings WHERE meeting_id = ?");
  $stmt->execute([$_GET['id']]);
  $meeting = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$meeting) {
    header("Location: manage_meetings.php");
    exit();
  }
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

// Fetch teachers for dropdown
try {
  $stmt = $pdo->query("SELECT id, full_name FROM teachers ORDER BY full_name");
  $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

// Handle form submission
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $teacher_id = trim($_POST['teacher_id'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $room_id = trim($_POST['room_id'] ?? '');
  $scheduled_datetime = trim($_POST['scheduled_datetime'] ?? '');
  $duration = trim($_POST['duration'] ?? '');
  $status = trim($_POST['status'] ?? '');

  // Validation
  if (empty($teacher_id) || !is_numeric($teacher_id)) $errors[] = "Teacher is required.";
  if (empty($title)) $errors[] = "Title is required.";
  if (empty($room_id)) $errors[] = "Room ID is required.";
  if (empty($scheduled_datetime)) $errors[] = "Scheduled date and time are required.";
  if (empty($duration) || !is_numeric($duration) || $duration <= 0) $errors[] = "Valid duration (minutes) is required.";
  if (empty($status) || !in_array($status, ['scheduled', 'in_progress', 'completed'])) $errors[] = "Valid status is required.";

  // If no errors, update database
  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("UPDATE meetings SET teacher_id = ?, title = ?, description = ?, room_id = ?, scheduled_datetime = ?, duration = ?, status = ? WHERE meeting_id = ?");
      $stmt->execute([$teacher_id, $title, $description, $room_id, $scheduled_datetime, $duration, $status, $_GET['id']]);
      $success = "Meeting updated successfully.";
    } catch (PDOException $e) {
      $errors[] = "Failed to update meeting: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Meeting - LMS</title>
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
        <h1 class="mb-4">Edit Meeting</h1>

        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Edit Meeting Form -->
        <div class="card">
          <div class="card-body">
            <form method="POST">
              <div class="mb-3">
                <label for="teacher_id" class="form-label">Teacher</label>
                <select class="form-control" id="teacher_id" name="teacher_id">
                  <option value="">Select Teacher</option>
                  <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>" <?php echo $meeting['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($teacher['full_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($meeting['title']); ?>">
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($meeting['description']); ?></textarea>
              </div>
              <div class="mb-3">
                <label for="room_id" class="form-label">Room ID</label>
                <input type="text" class="form-control" id="room_id" name="room_id" value="<?php echo htmlspecialchars($meeting['room_id']); ?>">
              </div>
              <div class="mb-3">
                <label for="scheduled_datetime" class="form-label">Scheduled Date & Time</label>
                <input type="datetime-local" class="form-control" id="scheduled_datetime" name="scheduled_datetime" value="<?php echo str_replace(' ', 'T', $meeting['scheduled_datetime']); ?>">
              </div>
              <div class="mb-3">
                <label for="duration" class="form-label">Duration (minutes)</label>
                <input type="number" class="form-control" id="duration" name="duration" value="<?php echo htmlspecialchars($meeting['duration']); ?>">
              </div>
              <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                  <option value="scheduled" <?php echo $meeting['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                  <option value="in_progress" <?php echo $meeting['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                  <option value="completed" <?php echo $meeting['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Update Meeting</button>
              <a href="manage_meetings.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>