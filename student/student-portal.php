<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['student_id'])) {
  header('Location: student-login.php');
  exit;
}

// Include the database connection file
include_once '../db/db.php';

// Fetch user information from the database based on the session ID
$studentId = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT full_name, profile_image FROM students WHERE id = ?");
$stmt->bind_param('i', $studentId);
$stmt->execute();
$stmt->bind_result($fullName, $profileImage);
$stmt->fetch();
$stmt->close();

// Show welcome message only once
$welcomeMessage = '';
if (!isset($_SESSION['welcome_shown'])) {
  $welcomeMessage = "Welcome, $fullName, to the Dashboard!";
  $_SESSION['welcome_shown'] = true; // Set flag to indicate message has been shown
}

// Fetch the video_id from the URL, ensure it's an integer
$video_id = isset($_GET['video_id']) ? (int) $_GET['video_id'] : 0;

// Query to fetch checkpoints for the current video
$sql_checkpoints = "SELECT * FROM checkpoints WHERE video_id = ? ORDER BY time_in_seconds ASC";
$stmt = $conn->prepare($sql_checkpoints);
$stmt->bind_param('i', $video_id);
$stmt->execute();
$result_checkpoints = $stmt->get_result();

// Check if there are any checkpoints available
if ($result_checkpoints->num_rows == 0) {
  $checkpointMessage = "No checkpoints available for this video.";
} else {
  $checkpointMessage = ""; // Reset the message if checkpoints exist
}

// Close the prepared statement
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'includes/css-links.php'; ?>
</head>

<body>
  <!-- Toastr Script -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>
  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="main-header">
      <h1>Welcome, Student</h1>

      <!-- Dropdown for user settings -->
      <div class="user-dropdown">
        <button class="text-white">
          <img src="../uploads/<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image">
          <?php echo htmlspecialchars($fullName); ?>&nbsp;
          <i class="fa fa-arrow-down" style="font-size: 20px;"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile-settings.php">Profile Settings</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>

    <!-- Dashboard Sections -->
    <div class="dashboard-section">
      <div class="dashboard-card">
        <h3>Profile</h3>
        <p>View and update your personal details.</p>
        <button onclick="location.href='profile-settings.php'">Go to Profile</button>
      </div>
      <div class="dashboard-card">
        <h3>Courses</h3>
        <p>View the courses.</p>
        <button onclick="location.href='student-quiz.php?video_id=<?php echo $video_id; ?>'">Go to Courses</button>
      </div>
      <div class="dashboard-card">
        <h3>Grades</h3>
        <p>Check your grades and academic performance.</p>
        <button onclick="location.href='#grades'">Go to Grades</button>
      </div>
      <div class="dashboard-card">
        <h3>Assignments</h3>
        <p>View and submit your assignments.</p>
        <button onclick="location.href='#assignments'">Go to Assignments</button>
      </div>
      <div class="dashboard-card">
        <h3>Messages</h3>
        <p>Check messages from your instructors and peers.</p>
        <button onclick="location.href='#messages'">Go to Messages</button>
      </div>
    </div>
  </div>

  <!-- Toastr Notification -->
  <script>
    <?php if (!empty($welcomeMessage)): ?>
      toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-bottom-right",
        timeOut: 4000
      };
      toastr.success("<?= $welcomeMessage; ?>");
    <?php endif; ?>
  </script>
</body>

</html>