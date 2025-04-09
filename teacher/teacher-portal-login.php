<?php
// Include database connection
include_once '../db/db.php';
session_start(); // Start the session to store user info

// Default values for message and message type
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize input data
  $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';

  if (!empty($email) && !empty($password)) {
    // Query to find the teacher by email
    $sql = "SELECT * FROM teachers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $teacher = $result->fetch_assoc();
      if (password_verify($password, $teacher['password'])) {
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['full_name'];

        // Set login success message in session
        $_SESSION['login_success'] = 'Welcome, ' . $teacher['full_name'] . '! You have successfully logged in.';
        $message = "Login successful! Redirecting to your portal...";
        $message_type = 'success';
      } else {
        $message = "Invalid email or password!";
        $message_type = 'error';
      }
    } else {
      $message = "Teacher not found with that email!";
      $message_type = 'error';
    }

    $stmt->close();
  } else {
    $message = "Please enter both email and password!";
    $message_type = 'error';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Portal Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/teacher-portal-login.css">
  <link rel="stylesheet" href="assets/css/toaster.css">
</head>

<body>
  <div class="banner" style="background-image: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url(assets/images/background-contact.jpg);">
    <h1>Teacher Portal
      <br>
      <a href="../index.php" class="btn custom-btn text-white" style="background-color: var(--primary-color);">Go Back</a>
    </h1>
  </div>

  <div class="container">
    <h2>Welcome, Teacher!</h2>
    <p>Manage your classes, assignments, and student progress through the Teacher Portal.</p>

    <div class="login-form">
      <form id="teacher-login-form" method="POST" action="">
        <label for="teacher-email">Email Address</label>
        <input type="email" id="teacher-email" name="email" placeholder="Enter your email" required>

        <label for="teacher-password">Password</label>
        <input type="password" id="teacher-password" name="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>
      </form>
      <br>
      <div style="text-align: right;">
        Haven't registered yet? <a href="teacher-registeration.php" style="margin: 0;padding:0;color:green;">Register Now</a>
      </div>
    </div>
  </div>

  <div id="toast" class="toast">
    <span class="icon"></span>
    <span class="message"></span>
  </div>




























  
  <script>
    function showToast(message, type) {
      if (!message) return;

      const toast = document.getElementById('toast');
      const icon = toast.querySelector('.icon');
      const msg = toast.querySelector('.message');

      msg.textContent = message;
      toast.className = 'toast show ' + type;

      if (type === 'error') {
        icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
      } else if (type === 'success') {
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
      }

      setTimeout(function() {
        toast.classList.remove('show');
      }, 4000);
    }

    <?php if (isset($message)): ?>
      const message = "<?php echo $message; ?>";
      const messageType = "<?php echo $message_type; ?>";
      showToast(message, messageType);

      if (messageType === 'success') {
        setTimeout(() => {
          window.location.href = "teacher-portal.php";
        }, 3000);
      }
    <?php endif; ?>
  </script>
</body>

</html>