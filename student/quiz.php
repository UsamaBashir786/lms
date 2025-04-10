<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['student_id'])) {
  header('Location: student-portal-login.php');
  exit;
}

// Include database connection
include_once '../db/db.php';

// Get student information
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT full_name, profile_image FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$fullName = $student['full_name'];
$profileImage = $student['profile_image'] ?? 'default-profile.jpg';

// Get quiz ID from URL if provided
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$take_quiz = isset($_GET['take']) && $_GET['take'] == 1;
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

// Process quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
  $quiz_id = intval($_POST['quiz_id']);
  $attempt_id = intval($_POST['attempt_id']);
  $end_time = date('Y-m-d H:i:s');

  // Get total points possible for this quiz
  $totalPointsQuery = "SELECT SUM(points) as total_points FROM quiz_questions WHERE quiz_id = ?";
  $stmt = $conn->prepare($totalPointsQuery);
  $stmt->bind_param("i", $quiz_id);
  $stmt->execute();
  $totalPointsResult = $stmt->get_result()->fetch_assoc();
  $totalPossiblePoints = $totalPointsResult['total_points'] ?? 0;

  // Calculate score
  $score = 0;
  $conn->begin_transaction();

  try {
    // Get all questions for this quiz
    $questionQuery = "SELECT q.question_id, q.question_type, q.points FROM quiz_questions q WHERE q.quiz_id = ? ORDER BY q.position";
    $stmt = $conn->prepare($questionQuery);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $questions = $stmt->get_result();

    while ($question = $questions->fetch_assoc()) {
      $question_id = $question['question_id'];
      $question_type = $question['question_type'];
      $points = $question['points'];
      $is_correct = false;
      $answer = '';

      // Process response based on question type
      if ($question_type === 'multiple_choice' || $question_type === 'true_false') {
        if (isset($_POST['question'][$question_id])) {
          $selected_option_id = intval($_POST['question'][$question_id]);

          // Check if selected option is correct
          $optionQuery = "SELECT is_correct FROM question_options WHERE question_id = ? AND option_id = ?";
          $stmt = $conn->prepare($optionQuery);
          $stmt->bind_param("ii", $question_id, $selected_option_id);
          $stmt->execute();
          $optionResult = $stmt->get_result();

          if ($optionResult->num_rows > 0) {
            $option = $optionResult->fetch_assoc();
            $is_correct = (bool)$option['is_correct'];
            $answer = $selected_option_id;
          }
        }
      } else if ($question_type === 'short_answer') {
        if (isset($_POST['question'][$question_id])) {
          $text_answer = trim($_POST['question'][$question_id]);
          $answer = $text_answer;

          // Get correct answer
          $correctAnswerQuery = "SELECT option_text FROM question_options WHERE question_id = ? AND is_correct = 1";
          $stmt = $conn->prepare($correctAnswerQuery);
          $stmt->bind_param("i", $question_id);
          $stmt->execute();
          $correctResult = $stmt->get_result();

          if ($correctResult->num_rows > 0) {
            $correctAnswer = $correctResult->fetch_assoc()['option_text'];
            // Case-insensitive comparison
            $is_correct = (strtolower($text_answer) === strtolower($correctAnswer));
          }
        }
      }

      // Add points if correct
      if ($is_correct) {
        $score += $points;
      }

      // Store the response
      $responseQuery = "INSERT INTO quiz_responses (attempt_id, question_id, selected_option_id, text_response, is_correct, points_earned) 
                        VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($responseQuery);
      $points_earned = $is_correct ? $points : 0;
      $selected_option_id = ($question_type === 'multiple_choice' || $question_type === 'true_false') ? $answer : null;
      $text_response = ($question_type === 'short_answer') ? $answer : null;
      $stmt->bind_param("iiisid", $attempt_id, $question_id, $selected_option_id, $text_response, $is_correct, $points_earned);
      $stmt->execute();
    }

    // Calculate percentage score
    $percentageScore = ($totalPossiblePoints > 0) ? ($score / $totalPossiblePoints) * 100 : 0;

    // Update the attempt record
    $updateAttemptQuery = "UPDATE quiz_attempts SET end_time = ?, score = ?, status = 'completed' WHERE attempt_id = ?";
    $stmt = $conn->prepare($updateAttemptQuery);
    $stmt->bind_param("sdi", $end_time, $percentageScore, $attempt_id);
    $stmt->execute();

    $conn->commit();

    // Redirect to results page
    header("Location: quiz-results.php?attempt_id=" . $attempt_id);
    exit;
  } catch (Exception $e) {
    $conn->rollback();
    $error = "Error submitting quiz: " . $e->getMessage();
  }
}

// Create a new attempt if taking quiz
if ($take_quiz && $quiz_id > 0) {
  // Check if there's already an in-progress attempt
  $checkAttemptQuery = "SELECT attempt_id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress'";
  $stmt = $conn->prepare($checkAttemptQuery);
  $stmt->bind_param("ii", $quiz_id, $student_id);
  $stmt->execute();
  $existingAttempt = $stmt->get_result();

  if ($existingAttempt->num_rows > 0) {
    // Continue existing attempt
    $attempt_id = $existingAttempt->fetch_assoc()['attempt_id'];
  } else {
    // Create new attempt
    $start_time = date('Y-m-d H:i:s');
    $createAttemptQuery = "INSERT INTO quiz_attempts (quiz_id, student_id, start_time, status) VALUES (?, ?, ?, 'in_progress')";
    $stmt = $conn->prepare($createAttemptQuery);
    $stmt->bind_param("iis", $quiz_id, $student_id, $start_time);

    if ($stmt->execute()) {
      $attempt_id = $conn->insert_id;
    } else {
      $error = "Error creating quiz attempt.";
      $take_quiz = false;
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Quizzes</title>
  <?php include 'includes/css-links.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Quiz specific styles */
    .main-content {
      padding: 20px;
    }

    .quizzes-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .quiz-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .quiz-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .quiz-header {
      padding: 15px;
      background-color: var(--primary-color);
      color: white;
    }

    .quiz-body {
      padding: 15px;
      flex-grow: 1;
    }

    .quiz-footer {
      padding: 15px;
      border-top: 1px solid #eee;
      background-color: #f8f9fa;
    }

    .badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: normal;
    }

    .badge-new {
      background-color: #3498db;
      color: white;
    }

    .badge-attempted {
      background-color: #f39c12;
      color: white;
    }

    .badge-completed {
      background-color: #2ecc71;
      color: white;
    }

    .quiz-detail {
      padding: 25px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .quiz-detail h3 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #eee;
    }

    .question-container {
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      position: relative;
    }

    .question-number {
      position: absolute;
      top: -15px;
      left: 20px;
      background-color: var(--primary-color);
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    .option-label {
      display: block;
      padding: 10px 15px;
      margin: 5px 0;
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .option-label:hover {
      background-color: #e9ecef;
    }

    .option-input:checked+.option-label {
      background-color: #d6f5d6;
      border-color: #2ecc71;
    }

    .option-input {
      display: none;
    }

    .quiz-timer {
      position: sticky;
      top: 20px;
      background-color: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .timer-count {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--primary-color);
    }

    .timer-warning {
      color: #e74c3c;
      animation: pulse 1s infinite;
    }

    @keyframes pulse {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }

      100% {
        opacity: 1;
      }
    }

    .back-button {
      margin-bottom: 20px;
      display: inline-block;
      padding: 8px 15px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border: 1px solid #ddd;
      text-decoration: none;
      color: #333;
      transition: background-color 0.3s ease;
    }

    .back-button:hover {
      background-color: #e9ecef;
    }

    .progress-container {
      height: 10px;
      background-color: #f1f1f1;
      border-radius: 5px;
      margin-top: 5px;
    }

    .progress-bar {
      height: 100%;
      background-color: #2ecc71;
      border-radius: 5px;
      width: 0%;
      transition: width 0.3s ease;
    }

    .short-answer-input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      margin-top: 10px;
    }

    .submit-warning {
      color: #e74c3c;
      display: none;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="main-header">
      <h1>Quizzes</h1>

      <!-- User dropdown -->
      <div class="user-dropdown">
        <button class="text-white">
          <img src="<?php echo $profileImage; ?>" alt="Profile Image">
          <?php echo htmlspecialchars($fullName); ?>&nbsp;
          <i class="fa fa-arrow-down" style="font-size: 20px;"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile-settings.php">Profile Settings</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <?php
    // Display quiz taking interface
    if ($take_quiz && $quiz_id > 0 && $attempt_id > 0):
      // Fetch quiz details
      $quizQuery = "SELECT q.*, t.full_name as teacher_name 
                    FROM quizzes q 
                    JOIN teachers t ON q.teacher_id = t.id 
                    WHERE q.quiz_id = ?";
      $stmt = $conn->prepare($quizQuery);
      $stmt->bind_param("i", $quiz_id);
      $stmt->execute();
      $quizResult = $stmt->get_result();

      if ($quizResult->num_rows > 0):
        $quiz = $quizResult->fetch_assoc();

        // Fetch attempt details
        $attemptQuery = "SELECT * FROM quiz_attempts WHERE attempt_id = ?";
        $stmt = $conn->prepare($attemptQuery);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $attemptResult = $stmt->get_result();
        $attempt = $attemptResult->fetch_assoc();

        // Check if attempt belongs to current student
        if ($attempt['student_id'] != $student_id) {
          echo '<div class="alert alert-danger">You do not have permission to access this quiz attempt.</div>';
          exit;
        }

        // Check if attempt is already completed
        if ($attempt['status'] == 'completed') {
          echo '<div class="alert alert-info">This quiz attempt has already been completed. <a href="quiz-results.php?attempt_id=' . $attempt_id . '">View Results</a></div>';
          exit;
        }

        // Calculate remaining time if there's a time limit
        $timeLimit = null;
        $remainingSeconds = null;

        if ($quiz['time_limit_minutes']) {
          $timeLimit = $quiz['time_limit_minutes'] * 60; // Convert to seconds
          $startTime = strtotime($attempt['start_time']);
          $currentTime = time();
          $elapsedSeconds = $currentTime - $startTime;
          $remainingSeconds = $timeLimit - $elapsedSeconds;

          // Auto-submit if time is up
          if ($remainingSeconds <= 0) {
            echo '<div class="alert alert-warning">Time is up! The quiz will be auto-submitted.</div>';
            echo '<script>document.addEventListener("DOMContentLoaded", function() { document.getElementById("quiz-form").submit(); });</script>';
          }
        }
    ?>

        <a href="quizzes.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Quizzes</a>

        <div class="quiz-detail">
          <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
          <p><strong>Teacher:</strong> <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
          <p><strong>Description:</strong> <?php echo htmlspecialchars($quiz['description']); ?></p>
          <p><strong>Pass Percentage:</strong> <?php echo $quiz['pass_percentage']; ?>%</p>
          <?php if ($quiz['time_limit_minutes']): ?>
            <p><strong>Time Limit:</strong> <?php echo $quiz['time_limit_minutes']; ?> minutes</p>
          <?php endif; ?>
        </div>

        <?php if ($timeLimit): ?>
          <div class="quiz-timer" id="quiz-timer">
            <div>
              <span>Time Remaining:</span>
              <span class="timer-count" id="timer-count"></span>
            </div>
            <div class="submit-warning" id="submit-warning">
              <i class="fas fa-exclamation-triangle"></i> Time is running out! Submit your answers soon.
            </div>
          </div>
        <?php endif; ?>

        <form id="quiz-form" method="POST" action="">
          <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
          <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">

          <?php
          // Fetch questions
          $questionsQuery = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY position";
          $stmt = $conn->prepare($questionsQuery);
          $stmt->bind_param("i", $quiz_id);
          $stmt->execute();
          $questionsResult = $stmt->get_result();
          $questionCount = $questionsResult->num_rows;
          $questionNumber = 1;

          while ($question = $questionsResult->fetch_assoc()):
            $question_id = $question['question_id'];
          ?>
            <div class="question-container">
              <div class="question-number"><?php echo $questionNumber; ?></div>
              <h4><?php echo htmlspecialchars($question['question_text']); ?> <span class="badge badge-secondary"><?php echo $question['points']; ?> points</span></h4>

              <?php if ($question['question_type'] == 'multiple_choice'): ?>
                <?php
                // Fetch options
                $optionsQuery = "SELECT * FROM question_options WHERE question_id = ?";
                $stmt = $conn->prepare($optionsQuery);
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                $optionsResult = $stmt->get_result();

                while ($option = $optionsResult->fetch_assoc()):
                  $option_id = $option['option_id'];
                ?>
                  <div>
                    <input type="radio" id="option_<?php echo $option_id; ?>" name="question[<?php echo $question_id; ?>]" value="<?php echo $option_id; ?>" class="option-input">
                    <label for="option_<?php echo $option_id; ?>" class="option-label"><?php echo htmlspecialchars($option['option_text']); ?></label>
                  </div>
                <?php endwhile; ?>

              <?php elseif ($question['question_type'] == 'true_false'): ?>
                <?php
                // Fetch True/False options
                $optionsQuery = "SELECT * FROM question_options WHERE question_id = ?";
                $stmt = $conn->prepare($optionsQuery);
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                $optionsResult = $stmt->get_result();

                while ($option = $optionsResult->fetch_assoc()):
                  $option_id = $option['option_id'];
                ?>
                  <div>
                    <input type="radio" id="option_<?php echo $option_id; ?>" name="question[<?php echo $question_id; ?>]" value="<?php echo $option_id; ?>" class="option-input">
                    <label for="option_<?php echo $option_id; ?>" class="option-label"><?php echo htmlspecialchars($option['option_text']); ?></label>
                  </div>
                <?php endwhile; ?>

              <?php elseif ($question['question_type'] == 'short_answer'): ?>
                <div>
                  <input type="text" name="question[<?php echo $question_id; ?>]" class="short-answer-input" placeholder="Type your answer here...">
                </div>
              <?php endif; ?>
            </div>
          <?php
            $questionNumber++;
          endwhile;
          ?>

          <div class="text-center mt-4 mb-5">
            <button type="submit" name="submit_quiz" class="btn btn-primary btn-lg">Submit Quiz</button>
            <p class="text-muted mt-2">Make sure to review all your answers before submitting.</p>
          </div>
        </form>

        <?php if ($timeLimit && $remainingSeconds > 0): ?>
          <script>
            // Timer functionality
            let remainingSeconds = <?php echo $remainingSeconds; ?>;
            const timerElement = document.getElementById('timer-count');
            const warningElement = document.getElementById('submit-warning');
            const quizForm = document.getElementById('quiz-form');

            function updateTimer() {
              if (remainingSeconds <= 0) {
                // Time's up, submit the form
                clearInterval(timerInterval);
                timerElement.textContent = "00:00";
                timerElement.classList.add('timer-warning');
                quizForm.submit();
                return;
              }

              const minutes = Math.floor(remainingSeconds / 60);
              const seconds = remainingSeconds % 60;

              timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

              // Show warning when less than 2 minutes remaining
              if (remainingSeconds <= 120) {
                timerElement.classList.add('timer-warning');
                warningElement.style.display = 'block';
              }

              remainingSeconds--;
            }

            // Initial update
            updateTimer();

            // Update every second
            const timerInterval = setInterval(updateTimer, 1000);
          </script>
        <?php endif; ?>

      <?php else: ?>
        <div class="alert alert-danger">Quiz not found.</div>
      <?php endif; ?>

    <?php else: ?>
      <!-- Display available quizzes -->
      <div class="quizzes-container">
        <?php
        // Fetch all available quizzes
        $quizzesQuery = "SELECT q.*, t.full_name as teacher_name,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.quiz_id) as question_count,
                        (SELECT MAX(attempt_id) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND student_id = ? AND status = 'completed') as last_attempt
                        FROM quizzes q
                        JOIN teachers t ON q.teacher_id = t.id
                        ORDER BY q.created_at DESC";
        $stmt = $conn->prepare($quizzesQuery);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $quizzesResult = $stmt->get_result();

        if ($quizzesResult->num_rows > 0) {
          while ($quiz = $quizzesResult->fetch_assoc()) {
            $quiz_id = $quiz['quiz_id'];
            $hasAttempted = !is_null($quiz['last_attempt']);

            // Get last attempt score if available
            $lastScore = null;
            if ($hasAttempted) {
              $scoreQuery = "SELECT score FROM quiz_attempts WHERE attempt_id = ?";
              $stmt = $conn->prepare($scoreQuery);
              $stmt->bind_param("i", $quiz['last_attempt']);
              $stmt->execute();
              $scoreResult = $stmt->get_result();
              if ($scoreResult->num_rows > 0) {
                $lastScore = $scoreResult->fetch_assoc()['score'];
              }
            }

            // Check if there's an in-progress attempt
            $inProgressQuery = "SELECT attempt_id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress'";
            $stmt = $conn->prepare($inProgressQuery);
            $stmt->bind_param("ii", $quiz_id, $student_id);
            $stmt->execute();
            $inProgressResult = $stmt->get_result();
            $hasInProgress = $inProgressResult->num_rows > 0;
            $inProgressAttemptId = $hasInProgress ? $inProgressResult->fetch_assoc()['attempt_id'] : null;
        ?>

            <div class="quiz-card">
              <div class="quiz-header">
                <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                <div>
                  <?php if ($hasAttempted): ?>
                    <span class="badge badge-completed">Completed</span>
                  <?php elseif ($hasInProgress): ?>
                    <span class="badge badge-attempted">In Progress</span>
                  <?php else: ?>
                    <span class="badge badge-new">New</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="quiz-body">
                <p><?php echo htmlspecialchars(substr($quiz['description'], 0, 100)) . (strlen($quiz['description']) > 100 ? '...' : ''); ?></p>
                <div class="quiz-details">
                  <p><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($quiz['teacher_name']); ?></p>
                  <p><i class="fas fa-question-circle"></i> <?php echo $quiz['question_count']; ?> questions</p>
                  <?php if ($quiz['time_limit_minutes']): ?>
                    <p><i class="fas fa-clock"></i> <?php echo $quiz['time_limit_minutes']; ?> minutes</p>
                  <?php endif; ?>
                  <p><i class="fas fa-percentage"></i> Pass: <?php echo $quiz['pass_percentage']; ?>%</p>
                  <?php if ($hasAttempted && !is_null($lastScore)): ?>
                    <p><i class="fas fa-star"></i> Your score: <?php echo number_format($lastScore, 1); ?>%</p>
                    <?php if ($lastScore >= $quiz['pass_percentage']): ?>
                      <div class="alert alert-success p-2 mt-2">Passed</div>
                    <?php else: ?>
                      <div class="alert alert-danger p-2 mt-2">Failed</div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="quiz-footer">
                <?php if ($hasInProgress): ?>
                  <a href="quiz.php?id=<?php echo $quiz_id; ?>&take=1&attempt_id=<?php echo $inProgressAttemptId; ?>" class="btn btn-warning w-100">Continue Attempt</a>
                <?php elseif ($hasAttempted): ?>
                  <a href="quiz-results.php?attempt_id=<?php echo $quiz['last_attempt']; ?>" class="btn btn-info w-100">View Results</a>
                  <a href="quiz.php?id=<?php echo $quiz_id; ?>&take=1" class="btn btn-outline-primary w-100 mt-2">Retry Quiz</a>
                <?php else: ?>
                  <a href="quiz.php?id=<?php echo $quiz_id; ?>&take=1" class="btn btn-primary w-100">Start Quiz</a>
                <?php endif; ?>
              </div>
            </div>

        <?php
          }
        } else {
          echo '<div class="col-12"><div class="alert alert-info">No quizzes available at the moment.</div></div>';
        }
        ?>
      </div>
    <?php endif; ?>

  </div>

  <!-- JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>

</html>