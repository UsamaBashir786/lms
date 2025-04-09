<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
  header('Location: teacher-portal-login.php');
  exit;
}

include_once '../db/db.php';

$teacherId = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT full_name FROM teachers WHERE id = ?");
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Check if login success message is set
$loginMessage = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : '';
if ($loginMessage) {
  // Unset session variable after displaying the message
  unset($_SESSION['login_success']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Portal</title>
  <link rel="stylesheet" href="assets/css/toaster.css">
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main-content">
    <div class="main-header">
      <h1>Welcome, <?php echo htmlspecialchars($fullName); ?></h1>
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

    <!-- Toast for welcome message -->
    <div id="toast" class="toast">
      <span class="icon"></span>
      <span class="message"></span>
    </div>

    <div class="dashboard-section">
      <div class="dashboard-card">
        <h3>Profile</h3>
        <p>Update your personal and contact information.</p>
        <button onclick="location.href='profile-settings.php'">Go to Profile</button>
      </div>
      <div class="dashboard-card">
        <h3>Courses</h3>
        <p>Add, update, or delete your courses.</p>
        <button onclick="location.href='upload-course.php'">Go to Courses</button>
      </div>
      <div class="dashboard-card">
        <h3>Manage Students</h3>
        <p>View and manage your enrolled students.</p>
        <button onclick="location.href='#manage-students'">Go to Students</button>
      </div>
      <div class="dashboard-card">
        <h3>Create Quiz</h3>
        <p>Create quizzes for your students to test their knowledge.</p>
        <button onclick="location.href='#create-quiz'">Create Quiz</button>
      </div>
    </div>
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

    <?php if ($loginMessage): ?>
      showToast("<?php echo $loginMessage; ?>", "success");
    <?php endif; ?>
  </script>
</body>

</html>