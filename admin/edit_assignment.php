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

// Fetch assignment data
$assignment = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: manage_assignments.php");
  exit();
}
try {
  $stmt = $pdo->prepare("SELECT assignment_id, teacher_id, title, description, file_path, total_points, due_date FROM assignments WHERE assignment_id = ?");
  $stmt->execute([$_GET['id']]);
  $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$assignment) {
    header("Location: manage_assignments.php");
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
  $total_points = trim($_POST['total_points'] ?? '');
  $due_date = trim($_POST['due_date'] ?? '');
  $file = $_FILES['file'] ?? null;

  // Validation
  if (empty($teacher_id) || !is_numeric($teacher_id)) $errors[] = "Teacher is required.";
  if (empty($title)) $errors[] = "Title is required.";
  if (empty($description)) $errors[] = "Description is required.";
  if (empty($total_points) || !is_numeric($total_points) || $total_points <= 0) $errors[] = "Valid total points is required.";
  if (empty($due_date)) $errors[] = "Due date is required.";

  // File handling
  $file_path = $assignment['file_path'];
  if ($file && $file['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($file['type'], $allowed_types)) {
      $errors[] = "Only PDF, DOC, or DOCX files are allowed.";
    } elseif ($file['size'] > 5 * 1024 * 1024) {
      $errors[] = "File size must not exceed 5MB.";
    } else {
      $file_name = uniqid() . '_' . basename($file['name']);
      $file_path = '../Uploads/assignments/' . $file_name;
      if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        $errors[] = "Failed to upload file.";
      }
    }
  }

  // If no errors, update database
  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("UPDATE assignments SET teacher_id = ?, title = ?, description = ?, file_path = ?, total_points = ?, due_date = ? WHERE assignment_id = ?");
      $stmt->execute([$teacher_id, $title, $description, $file_path, $total_points, $due_date, $_GET['id']]);
      $success = "Assignment updated successfully.";
    } catch (PDOException $e) {
      $errors[] = "Failed to update assignment: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Assignment - LMS</title>
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
        <h1 class="mb-4">Edit Assignment</h1>

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

        <!-- Edit Assignment Form -->
        <div class="card">
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="teacher_id" class="form-label">Teacher</label>
                <select class="form-control" id="teacher_id" name="teacher_id">
                  <option value="">Select Teacher</option>
                  <?php foreach ($teachers as $teacher): ?>
                    <option value="<?php echo $teacher['id']; ?>" <?php echo $assignment['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($teacher['full_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>">
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
              </div>
              <div class="mb-3">
                <label for="total_points" class="form-label">Total Points</label>
                <input type="number" class="form-control" id="total_points" name="total_points" value="<?php echo htmlspecialchars($assignment['total_points']); ?>">
              </div>
              <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?php echo str_replace(' ', 'T', $assignment['due_date']); ?>">
              </div>
              <div class="mb-3">
                <label for="file" class="form-label">Assignment File (PDF, DOC, DOCX, leave blank to keep current)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx">
                <small>Current: <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank">View File</a></small>
              </div>
              <button type="submit" class="btn btn-primary">Update Assignment</button>
              <a href="manage_assignments.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>