<?php
session_start();
include_once '../db/db.php';

$message = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect and validate inputs
  $inputs = [
    'name' => ['value' => trim($_POST['name'] ?? ''), 'required' => true, 'error' => 'Full name is required.'],
    'email' => ['value' => filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL), 'required' => true, 'error' => 'Valid email address is required.'],
    'phone' => ['value' => trim($_POST['phone'] ?? ''), 'pattern' => '/^[0-9]{11}$/', 'error' => 'Phone number must be exactly 11 digits.'],
    'subject' => ['value' => trim($_POST['subject'] ?? ''), 'required' => true, 'error' => 'Subject specialization is required.'],
    'experience' => ['value' => (int)($_POST['experience'] ?? 0), 'min' => 0, 'error' => 'Experience cannot be negative.'],
    'password' => ['value' => $_POST['password'] ?? '', 'minlength' => 8, 'error' => 'Password must be at least 8 characters long.']
  ];

  // Validate text inputs
  foreach ($inputs as $key => $input) {
    if (isset($input['required']) && $input['required'] && empty($input['value'])) {
      $errors[] = $input['error'];
    } elseif (isset($input['pattern']) && !preg_match($input['pattern'], $input['value'])) {
      $errors[] = $input['error'];
    } elseif (isset($input['min']) && $input['value'] < $input['min']) {
      $errors[] = $input['error'];
    } elseif (isset($input['minlength']) && strlen($input['value']) < $input['minlength']) {
      $errors[] = $input['error'];
    }
  }

  // Validate resume upload
  if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== 0) {
    $errors[] = 'Resume/CV is required.';
  } else {
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    if (finfo_file($fileInfo, $_FILES['resume']['tmp_name']) !== 'application/pdf') {
      $errors[] = 'Only PDF files are allowed.';
    }
    finfo_close($fileInfo);
  }

  if (empty($errors)) {
    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
    $stmt->bind_param('s', $inputs['email']['value']);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
      $message = ['text' => 'Email is already registered.', 'type' => 'error'];
    } else {
      // Create upload directory if needed
      $uploadDir = '../uploads/resumes/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Process the upload
      $resumeFileName = 'resume_' . time() . '_' . uniqid() . '.pdf';
      $uploadPath = $uploadDir . $resumeFileName;

      if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)) {
        // Insert new teacher
        $hashedPassword = password_hash($inputs['password']['value'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO teachers (full_name, email, phone, subject, experience, password, resume_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($sql);
        $insertStmt->bind_param(
          'ssssiss',
          $inputs['name']['value'],
          $inputs['email']['value'],
          $inputs['phone']['value'],
          $inputs['subject']['value'],
          $inputs['experience']['value'],
          $hashedPassword,
          $resumeFileName
        );

        if ($insertStmt->execute()) {
          $message = ['text' => 'Registration successful! Redirecting...', 'type' => 'success'];
          echo "<script>setTimeout(function() { window.location.href = 'teacher-portal-login.php'; }, 3000);</script>";
        } else {
          $message = ['text' => 'Registration failed: ' . $insertStmt->error, 'type' => 'error'];
        }
      } else {
        $message = ['text' => 'Failed to upload resume.', 'type' => 'error'];
      }
    }
  } else {
    $message = ['text' => implode('<br>', $errors), 'type' => 'error'];
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Portal</title>
  <!-- <link rel="stylesheet" href="styles.css"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/teacher-registeration.css">
  <style>

  </style>
</head>

<body>
  <!-- Banner Section -->
  <div class="banner" style="background-image:linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)),url(assets/images/background-contact.jpg);">
    <h1>Register as a Teacher
      <br>
      <a href="../index.php" class="btn go-back text-white">Go Back</a>
    </h1>
  </div>

  <!-- Main Content Section -->
  <div class="container">
    <div class="row">
      <!-- Content Area -->
      <div class="col-md-6">
        <div class="feature-container">
          <h2>Welcome to Our Platform</h2>
          <p>
            Join our team of skilled educators and help shape the future! Our platform connects passionate teachers
            with students eager to learn. Register now and start sharing your expertise.
          </p>
          <ul class="feature-list">
            <li><i class="fas fa-chalkboard-teacher"></i> Manage your classes easily</li>
            <li><i class="fas fa-chart-line"></i> Track student performance</li>
            <li><i class="fas fa-book-reader"></i> Access teaching resources</li>
          </ul>
        </div>
      </div>

      <!-- Form Area -->
      <div class="col-md-6">
        <div class="form-section">
          <form id="teacherForm" method="POST" enctype="multipart/form-data">
            <div>
              <label for="name" class="form-label">Full Name</label>
              <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>
            <div>
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
              <label for="resume" class="form-label">Resume/CV (PDF only)</label>
              <input type="file" id="resume" name="resume" accept=".pdf" required>
            </div>
            <div>
              <label for="phone" class="form-label">Phone Number</label>
              <input type="number" id="phone" name="phone" placeholder="Enter your phone number" required maxlength="11" pattern="^[0-9]{11}$">
              <!-- <small>Phone number must be 11 digits</small> -->
            </div>
            <div>
              <label for="subject" class="form-label">Subject Specialization</label>
              <input type="text" id="subject" name="subject" placeholder="Enter your subject expertise" required>
            </div>
            <div>
              <label for="experience" class="form-label">Teaching Experience (Years)</label>
              <input type="number" id="experience" name="experience" placeholder="Enter years of experience" required min="0">
            </div>
            <!-- Password Field -->
            <div>
              <label for="password" class="form-label">Password</label>
              <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit">Register</button>
          </form>
          <?php echo isset($message) ? $message['text'] : ''; ?>
        </div>
      </div>
    </div>
  </div>
  <?php include 'includes/footer.php' ?>
  <?php include 'includes/js-links.php' ?>
  <?php include 'includes/teacher-registeration-toaster.php' ?>
</body>

</html>