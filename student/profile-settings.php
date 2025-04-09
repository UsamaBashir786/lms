<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['student_id'])) {
  header('Location: student-login.php');
  exit;
}
// Fetch the video_id from the URL, ensure it's an integer
$video_id = isset($_GET['video_id']) ? (int) $_GET['video_id'] : 0;
// Include the database connection file
include_once '../db/db.php';

$user_id = $_SESSION['student_id']; // Get the logged-in user ID

// Fetch the user details from the database
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Update user profile on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and validate the inputs
  $fullName = isset($_POST['full_name']) ? $conn->real_escape_string(trim($_POST['full_name'])) : $user['full_name'];
  $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : $user['email'];
  $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : $user['phone'];
  $dob = isset($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : $user['dob'];
  $course = isset($_POST['course']) ? $conn->real_escape_string($_POST['course']) : $user['course'];
  $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_BCRYPT) : $user['password'];

  // Handle file upload if a new profile image is selected
  $profileImgPath = $user['profile_image'];
  if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = 'uploads/';
    $fileName = uniqid() . '_' . basename($_FILES['profile_img']['name']);
    $targetFilePath = $uploadDir . $fileName;

    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $fileType = mime_content_type($_FILES['profile_img']['tmp_name']);
    if (in_array($fileType, $allowedTypes) && $_FILES['profile_img']['size'] <= $maxFileSize) {
      if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFilePath)) {
        $profileImgPath = $targetFilePath;
      }
    }
  }

  // Update the database with the new values
  $sql = "UPDATE students SET full_name = ?, email = ?, phone = ?, dob = ?, course = ?, password = ?, profile_image = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssssi', $fullName, $email, $phone, $dob, $course, $password, $profileImgPath, $user_id);

  if ($stmt->execute()) {
    // Redirect to thank-you page after successful update
    header('Location: thank-you.php');
    exit;
  } else {
    echo '<div class="alert alert-danger">Failed to update profile: ' . $stmt->error . '</div>';
  }

  $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Settings</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/student-portal.css">
  <link rel="stylesheet" href="assets/css/profile-setting.css">
  <style>

  </style>
</head>

<body>
  <!-- Sidebar -->
  <?php include 'includes/sidebar.php' ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="main-header">
      <h1>Profile Settings</h1>
      <p>Logged in as <strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
    </div>

    <div class="dashboard-section">
      <form id="student-form" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="full-name" class="form-label">Full Name</label>
            <input type="text" id="full-name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Enter your full name" required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Enter your email" required>
          </div>
          <div class="col-md-6">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Enter your phone number" required>
          </div>
          <div class="col-md-6">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="form-control" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="course" class="form-label">Course Interested In</label>
            <select id="course" name="course" class="form-select" required>
              <option value="html" <?php echo $user['course'] == 'html' ? 'selected' : ''; ?>>HTML & CSS</option>
              <option value="js" <?php echo $user['course'] == 'js' ? 'selected' : ''; ?>>JavaScript</option>
              <option value="php" <?php echo $user['course'] == 'php' ? 'selected' : ''; ?>>PHP</option>
              <option value="mysql" <?php echo $user['course'] == 'mysql' ? 'selected' : ''; ?>>MySQL</option>
              <option value="bootstrap" <?php echo $user['course'] == 'bootstrap' ? 'selected' : ''; ?>>Bootstrap</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter a password" required>
          </div>
        </div>
        <div class="col-md-12 mt-3">
          <label for="profile-img" class="form-label">Upload Profile Image</label>
          <input type="file" id="profile-img" name="profile_img" accept="image/*" class="form-control">
          <div class="mt-3">
            <?php if ($user['profile_image']) : ?>
              <img id="img-preview" src="<?php echo $user['profile_image']; ?>" alt="Image Preview" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
            <?php else : ?>
              <img id="img-preview" src="#" alt="Image Preview" style="display: none; max-width: 100%; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
            <?php endif; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-4">Update Profile</button>
      </form>
    </div>
  </div>
</body>

</html>