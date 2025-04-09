<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
  header('Location: teacher-login.php');
  exit;
}

include_once '../db/db.php';

$teacherId = $_SESSION['teacher_id'];
$message = "";

// Fetch existing details
$stmt = $conn->prepare("SELECT full_name, email, phone, subject, experience FROM teachers WHERE id = ?");
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$stmt->bind_result($fullName, $email, $phone, $subject, $experience);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newFullName = $_POST['full_name'];
  $newEmail = $_POST['email'];
  $newPhone = $_POST['phone'];
  $newSubject = $_POST['subject'];
  $newExperience = $_POST['experience'];
  $newPassword = $_POST['password'];  // Get the new password input

  // If a new password is provided, hash it and update it
  if (!empty($newPassword)) {
    // Hash the new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password along with other details
    $updateStmt = $conn->prepare("UPDATE teachers SET full_name = ?, email = ?, phone = ?, subject = ?, experience = ?, password = ? WHERE id = ?");
    $updateStmt->bind_param('ssssisi', $newFullName, $newEmail, $newPhone, $newSubject, $newExperience, $newHashedPassword, $teacherId);
  } else {
    // Only update other details if no password is provided
    $updateStmt = $conn->prepare("UPDATE teachers SET full_name = ?, email = ?, phone = ?, subject = ?, experience = ? WHERE id = ?");
    $updateStmt->bind_param('ssssii', $newFullName, $newEmail, $newPhone, $newSubject, $newExperience, $teacherId);
  }

  // Execute the query and check if it's successful
  if ($updateStmt->execute()) {
    $message = "Details updated successfully!";
  } else {
    $message = "Failed to update details. Please try again.";
  }

  $updateStmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Teacher Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="assets/css/profile-setting.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main-content">
    <div class="main-header">
      <h1>Welcome, Teacher</h1>
      <div class="user-dropdown">
        <button class="text-white">
          <?php echo htmlspecialchars($fullName); ?> &nbsp;
          <i class="fa fa-arrow-down"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile-settings.php">Profile Settings</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>
    <div class="form-container">
      <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary">Upload Assignment</h2>
        <form action="" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="assignment" class="form-label">Select PDF Assignment:</label>
            <input type="file" class="form-control" name="assignment" id="assignment" accept=".pdf" required>
          </div>
          <button type="submit" name="upload" class="btn btn-primary" style="width:100px;">Upload</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  </script>
</body>

</html>