<?php
session_start();
include_once '../db/db.php';

$toastrMessage = '';  // Variable to store Toastr message
$toastrType = '';     // Variable to store Toastr type (success/error)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and get input values
  $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : null;
  $password = isset($_POST['password']) ? $_POST['password'] : null;

  if ($email && $password) {
    // Check if the student exists
    $stmt = $conn->prepare("SELECT id, password FROM students WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      // Student found, verify password
      $stmt->bind_result($studentId, $hashedPassword);
      $stmt->fetch();

      if (password_verify($password, $hashedPassword)) {
        // Set session variables and display success message
        $_SESSION['student_id'] = $studentId;
        $toastrMessage = "Login successful! Redirecting to your portal...";
        $toastrType = "success";
      } else {
        // Invalid password
        $toastrMessage = "Invalid password. Please try again.";
        $toastrType = "error";
      }
    } else {
      // No student found with that email
      $toastrMessage = "No account found with that email.";
      $toastrType = "error";
    }

    $stmt->close();
  } else {
    // Missing input
    $toastrMessage = "Please enter both email and password.";
    $toastrType = "error";
  }
  // Set session variable to indicate user is logged in
  $_SESSION['logged_in'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <link rel="stylesheet" href="assets/css/student-portal-login.css">
</head>

<body>
  <!-- Banner Section -->
  <div class="banner text-center" style="background-image:linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)),url(assets/images/background-contact.jpg);">
    <h1>Student Registration
      <br>
      <a href="../index.php" class="btn go-back mt-3">Go Back</a>
    </h1>
  </div>

  <!-- Main Content -->
  <div class="container">
    <h2>Welcome, Student!</h2>
    <p>Access your courses, assignments, and other important student resources here.</p>

    <!-- Student Login Form -->
    <div class="login-form">
      <form method="POST" action="">
        <label for="student-email">Email Address</label>
        <input type="email" id="student-email" name="email" placeholder="Enter your email" required>

        <label for="student-password">Password</label>
        <input type="password" id="student-password" name="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>
      </form>
      <br>
      <div style="text-align: right;">
        have not register yet? <a href="student-registeration.php" style="margin: 0;padding:0;color:green;">Register Now</a>
      </div>
    </div>
  </div>

  <!-- Toastr Notifications -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    // Configure Toastr options
    toastr.options = {
      closeButton: true,
      progressBar: true,
      positionClass: "toast-bottom-right", // Changed to bottom-right
      timeOut: 4000
    };

    // Display Toastr notifications based on PHP variables
    <?php if (!empty($toastrMessage)): ?>
      toastr.<?= $toastrType ?>("<?= $toastrMessage; ?>");

      // Redirect if login is successful
      <?php if ($toastrType === 'success'): ?>
        setTimeout(() => {
          window.location.href = "student-portal.php";
        }, 4000);
      <?php endif; ?>
    <?php endif; ?>
  </script>

</body>

</html>