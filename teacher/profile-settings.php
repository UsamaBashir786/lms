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
      <form method="POST" action="">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>
        <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
        </div>
        <div class="form-group">
          <label for="experience">Experience (Years)</label>
          <input type="number" id="experience" name="experience" value="<?php echo htmlspecialchars($experience); ?>" required>
        </div>
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Update Details</button>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": false,
      "progressBar": true,
      "positionClass": "toast-bottom-right",
      "preventDuplicates": true,
      "onclick": null,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "5000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
    };

    <?php if (!empty($message) && $message === "Details updated successfully!") : ?>
      toastr.success("<?php echo $message; ?>");
      setTimeout(function() {
        window.location.href = "teacher-portal.php"; // Redirect after 4 seconds
      }, 4000);
    <?php elseif (!empty($message)) : ?>
      toastr.error("<?php echo $message; ?>");
    <?php endif; ?>
  </script>


  </script>
</body>

</html>