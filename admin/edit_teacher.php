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

// Fetch teacher data
$teacher = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: manage_teachers.php");
  exit();
}
try {
  $stmt = $pdo->prepare("SELECT id, full_name, email, phone, subject, experience, resume_path FROM teachers WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$teacher) {
    header("Location: manage_teachers.php");
    exit();
  }
} catch (PDOException $e) {
  die("Query failed: " . $e->getMessage());
}

// Handle form submission
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $experience = trim($_POST['experience'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $resume = $_FILES['resume'] ?? null;

  // Validation
  if (empty($full_name)) $errors[] = "Full name is required.";
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if (empty($phone)) $errors[] = "Phone number is required.";
  if (empty($subject)) $errors[] = "Subject is required.";
  if (empty($experience) || !is_numeric($experience) || $experience < 0) $errors[] = "Valid experience (years) is required.";

  // Resume handling
  $resume_path = $teacher['resume_path'];
  if ($resume && $resume['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['application/pdf'];
    if (!in_array($resume['type'], $allowed_types)) {
      $errors[] = "Only PDF files are allowed for resume.";
    } elseif ($resume['size'] > 5 * 1024 * 1024) {
      $errors[] = "Resume size must not exceed 5MB.";
    } else {
      $resume_name = 'resume_' . uniqid() . '_' . basename($resume['name']);
      $resume_path = '../Uploads/' . $resume_name;
      if (!move_uploaded_file($resume['tmp_name'], $resume_path)) {
        $errors[] = "Failed to upload resume.";
      }
    }
  }

  // If no errors, update database
  if (empty($errors)) {
    try {
      if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE teachers SET full_name = ?, email = ?, phone = ?, subject = ?, experience = ?, password = ?, resume_path = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $subject, $experience, $hashed_password, $resume_path, $_GET['id']]);
      } else {
        $stmt = $pdo->prepare("UPDATE teachers SET full_name = ?, email = ?, phone = ?, subject = ?, experience = ?, resume_path = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $subject, $experience, $resume_path, $_GET['id']]);
      }
      $success = "Teacher updated successfully.";
    } catch (PDOException $e) {
      $errors[] = "Failed to update teacher: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Teacher - LMS</title>
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
        <h1 class="mb-4">Edit Teacher</h1>

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

        <!-- Edit Teacher Form -->
        <div class="card">
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($teacher['full_name']); ?>">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>">
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>">
              </div>
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($teacher['subject']); ?>">
              </div>
              <div class="mb-3">
                <label for="experience" class="form-label">Experience (years)</label>
                <input type="number" class="form-control" id="experience" name="experience" value="<?php echo htmlspecialchars($teacher['experience']); ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password (leave blank to keep unchanged)</label>
                <input type="password" class="form-control" id="password" name="password">
              </div>
              <div class="mb-3">
                <label for="resume" class="form-label">Resume (PDF, leave blank to keep current)</label>
                <input type="file" class="form-control" id="resume" name="resume" accept="application/pdf">
                <small>Current: <a href="<?php echo htmlspecialchars($teacher['resume_path']); ?>" target="_blank">View Resume</a></small>
              </div>
              <button type="submit" class="btn btn-primary">Update Teacher</button>
              <a href="manage_teachers.php" class="btn btn-secondary">Cancel</a>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>