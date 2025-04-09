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
        <h2 class="text-center text-primary">Create a Quiz</h2>
        <form id="quizForm">
          <div class="mb-3">
            <label class="form-label">Quiz Title:</label>
            <input type="text" class="form-control" id="quizTitle" required>
          </div>
          <div id="questionsContainer">
            <div class="question-block mb-3">
              <label class="form-label">Question 1:</label>
              <input type="text" class="form-control question" required>
              <label class="form-label mt-2">Options:</label>
              <div class="row">
                <div class="col-md-6"><input type="text" class="form-control option" placeholder="Option 1" required></div>
                <div class="col-md-6"><input type="text" class="form-control option" placeholder="Option 2" required></div>
                <div class="col-md-6 mt-2"><input type="text" class="form-control option" placeholder="Option 3"></div>
                <div class="col-md-6 mt-2"><input type="text" class="form-control option" placeholder="Option 4"></div>
              </div>
              <label class="form-label mt-2">Correct Answer:</label>
              <input type="text" class="form-control correct-answer" placeholder="Enter correct option" required>
            </div>
          </div>
          <button type="button" class="btn btn-secondary mt-3" id="addQuestion">+ Add Question</button>
          <button type="submit" class="btn btn-primary mt-3 w-100">Generate Quiz Link</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    document.getElementById("addQuestion").addEventListener("click", function() {
      let qNum = document.querySelectorAll(".question-block").length + 1;
      let newQuestion = document.createElement("div");
      newQuestion.classList.add("question-block", "mb-3");
      newQuestion.innerHTML = `
        <label class="form-label">Question ${qNum}:</label>
        <input type="text" class="form-control question" required>
        <label class="form-label mt-2">Options:</label>
        <div class="row">
            <div class="col-md-6"><input type="text" class="form-control option" placeholder="Option 1" required></div>
            <div class="col-md-6"><input type="text" class="form-control option" placeholder="Option 2" required></div>
            <div class="col-md-6 mt-2"><input type="text" class="form-control option" placeholder="Option 3"></div>
            <div class="col-md-6 mt-2"><input type="text" class="form-control option" placeholder="Option 4"></div>
        </div>
        <label class="form-label mt-2">Correct Answer:</label>
        <input type="text" class="form-control correct-answer" placeholder="Enter correct option" required>
    `;
      document.getElementById("questionsContainer").appendChild(newQuestion);
    });

    document.getElementById("quizForm").addEventListener("submit", function(e) {
      e.preventDefault();
      let quizTitle = document.getElementById("quizTitle").value;
      let quizLink = "attempt-quiz.php?quiz_id=" + Math.floor(Math.random() * 10000);
      Swal.fire({
        title: "Quiz Created!",
        text: "Share this link: " + quizLink,
        icon: "success",
        confirmButtonText: "Copy Link"
      }).then(() => {
        navigator.clipboard.writeText(window.location.origin + "/" + quizLink);
      });
    });
  </script>
  </script>
</body>

</html>