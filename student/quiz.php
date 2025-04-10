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

// Fetch all available quizzes
$quizzes_stmt = $conn->prepare("
  SELECT q.*, 
         t.full_name AS teacher_name,
         (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND student_id = ?) AS attempts_count,
         (SELECT MAX(score) FROM quiz_attempts WHERE quiz_id = q.quiz_id AND student_id = ? AND status = 'completed') AS best_score
  FROM quizzes q
  JOIN teachers t ON q.teacher_id = t.id
  ORDER BY q.created_at DESC
");
$quizzes_stmt->bind_param("ii", $student_id, $student_id);
$quizzes_stmt->execute();
$quizzes_result = $quizzes_stmt->get_result();
$quizzes = [];

while ($row = $quizzes_result->fetch_assoc()) {
  $quizzes[] = $row;
}
$quizzes_stmt->close();

// Fetch quiz details if ID is provided
$quiz = null;
$questions = [];
$current_attempt = null;

if ($quiz_id > 0) {
  // Get quiz details
  $quiz_stmt = $conn->prepare("
    SELECT q.*, t.full_name AS teacher_name
    FROM quizzes q
    JOIN teachers t ON q.teacher_id = t.id
    WHERE q.quiz_id = ?
  ");
  $quiz_stmt->bind_param("i", $quiz_id);
  $quiz_stmt->execute();
  $quiz_result = $quiz_stmt->get_result();

  if ($quiz_result->num_rows > 0) {
    $quiz = $quiz_result->fetch_assoc();

    // Check if there's an ongoing attempt
    $attempt_stmt = $conn->prepare("
      SELECT * FROM quiz_attempts 
      WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress'
      ORDER BY start_time DESC LIMIT 1
    ");
    $attempt_stmt->bind_param("ii", $quiz_id, $student_id);
    $attempt_stmt->execute();
    $attempt_result = $attempt_stmt->get_result();

    if ($attempt_result->num_rows > 0) {
      $current_attempt = $attempt_result->fetch_assoc();
      $attempt_id = $current_attempt['attempt_id'];
    }
    $attempt_stmt->close();

    // If taking the quiz or continuing an attempt
    if ($take_quiz || $attempt_id > 0) {
      // Create a new attempt if needed
      if (!$current_attempt && $take_quiz) {
        $new_attempt_stmt = $conn->prepare("
          INSERT INTO quiz_attempts (quiz_id, student_id, start_time, status)
          VALUES (?, ?, NOW(), 'in_progress')
        ");
        $new_attempt_stmt->bind_param("ii", $quiz_id, $student_id);

        if ($new_attempt_stmt->execute()) {
          $attempt_id = $conn->insert_id;

          // Fetch the new attempt details
          $fetch_attempt_stmt = $conn->prepare("SELECT * FROM quiz_attempts WHERE attempt_id = ?");
          $fetch_attempt_stmt->bind_param("i", $attempt_id);
          $fetch_attempt_stmt->execute();
          $fetch_result = $fetch_attempt_stmt->get_result();
          $current_attempt = $fetch_result->fetch_assoc();
          $fetch_attempt_stmt->close();
        }
        $new_attempt_stmt->close();
      }

      // Get questions for this quiz
      $questions_stmt = $conn->prepare("
        SELECT q.*, 
               (SELECT COUNT(*) FROM question_options WHERE question_id = q.question_id) AS options_count
        FROM quiz_questions q
        WHERE q.quiz_id = ?
        ORDER BY q.position
      ");
      $questions_stmt->bind_param("i", $quiz_id);
      $questions_stmt->execute();
      $questions_result = $questions_stmt->get_result();

      while ($question = $questions_result->fetch_assoc()) {
        // Get options for this question
        $options_stmt = $conn->prepare("
          SELECT * FROM question_options 
          WHERE question_id = ?
          ORDER BY RAND()
        ");
        $options_stmt->bind_param("i", $question['question_id']);
        $options_stmt->execute();
        $options_result = $options_stmt->get_result();

        $options = [];
        while ($option = $options_result->fetch_assoc()) {
          $options[] = $option;
        }

        $question['options'] = $options;
        $questions[] = $question;

        $options_stmt->close();
      }
      $questions_stmt->close();
    }
  }
  $quiz_stmt->close();
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
  $attempt_id = isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : 0;
  $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
  $responses = isset($_POST['responses']) ? $_POST['responses'] : [];

  if ($attempt_id > 0 && $quiz_id > 0 && !empty($responses)) {
    // Start a transaction
    $conn->begin_transaction();

    try {
      // Update attempt status
      $update_attempt_stmt = $conn->prepare("
        UPDATE quiz_attempts 
        SET end_time = NOW(), status = 'completed'
        WHERE attempt_id = ? AND student_id = ?
      ");
      $update_attempt_stmt->bind_param("ii", $attempt_id, $student_id);
      $update_attempt_stmt->execute();

      // Process responses
      $total_points = 0;
      $earned_points = 0;
      $correct_count = 0;

      foreach ($responses as $question_id => $response) {
        $question_id = intval($question_id);

        // Get question details
        $question_stmt = $conn->prepare("
          SELECT * FROM quiz_questions WHERE question_id = ?
        ");
        $question_stmt->bind_param("i", $question_id);
        $question_stmt->execute();
        $question_result = $question_stmt->get_result();
        $question = $question_result->fetch_assoc();
        $question_stmt->close();

        if ($question) {
          $total_points += $question['points'];
          $is_correct = 0;
          $points_earned = 0;
          $selected_option_id = null;
          $text_response = null;

          // Process based on question type
          if ($question['question_type'] === 'multiple_choice') {
            $selected_option_id = intval($response);

            // Check if selected option is correct
            $check_stmt = $conn->prepare("
              SELECT is_correct FROM question_options 
              WHERE option_id = ? AND question_id = ?
            ");
            $check_stmt->bind_param("ii", $selected_option_id, $question_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
              $option = $check_result->fetch_assoc();
              if ($option['is_correct']) {
                $is_correct = 1;
                $points_earned = $question['points'];
                $correct_count++;
              }
            }
            $check_stmt->close();
          } elseif ($question['question_type'] === 'true_false') {
            $selected_option_id = intval($response);

            // Check if selected option is correct
            $check_stmt = $conn->prepare("
              SELECT is_correct FROM question_options 
              WHERE option_id = ? AND question_id = ?
            ");
            $check_stmt->bind_param("ii", $selected_option_id, $question_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
              $option = $check_result->fetch_assoc();
              if ($option['is_correct']) {
                $is_correct = 1;
                $points_earned = $question['points'];
                $correct_count++;
              }
            }
            $check_stmt->close();
          } elseif ($question['question_type'] === 'short_answer') {
            $text_response = $conn->real_escape_string(trim($response));

            // Check if text response matches any correct answer
            $check_stmt = $conn->prepare("
              SELECT option_text FROM question_options 
              WHERE question_id = ? AND is_correct = 1
            ");
            $check_stmt->bind_param("i", $question_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
              $option = $check_result->fetch_assoc();
              // Case-insensitive comparison
              if (strtolower($text_response) === strtolower($option['option_text'])) {
                $is_correct = 1;
                $points_earned = $question['points'];
                $correct_count++;
              }
            }
            $check_stmt->close();
          }

          // Save the response
          $save_response_stmt = $conn->prepare("
            INSERT INTO quiz_responses 
            (attempt_id, question_id, selected_option_id, text_response, is_correct, points_earned)
            VALUES (?, ?, ?, ?, ?, ?)
          ");
          $save_response_stmt->bind_param("iiisid", $attempt_id, $question_id, $selected_option_id, $text_response, $is_correct, $points_earned);
          $save_response_stmt->execute();
          $save_response_stmt->close();

          $earned_points += $points_earned;
        }
      }

      // Calculate final score
      $score = ($total_points > 0) ? ($earned_points / $total_points) * 100 : 0;

      // Update attempt with score
      $update_score_stmt = $conn->prepare("
        UPDATE quiz_attempts 
        SET score = ?
        WHERE attempt_id = ?
      ");
      $update_score_stmt->bind_param("di", $score, $attempt_id);
      $update_score_stmt->execute();
      $update_score_stmt->close();

      // Commit transaction
      $conn->commit();

      // Set success message and redirect to results
      $_SESSION['quiz_completed'] = true;
      $_SESSION['quiz_score'] = round($score, 2);
      $_SESSION['quiz_correct'] = $correct_count;
      $_SESSION['quiz_total'] = count($responses);

      header("Location: quiz_result.php?attempt_id=" . $attempt_id);
      exit;
    } catch (Exception $e) {
      // Rollback transaction on error
      $conn->rollback();
      $error = "An error occurred while processing your quiz: " . $e->getMessage();
    }
  } else {
    $error = "Missing required data for quiz submission.";
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

    <?php if ($quiz_id > 0 && $quiz): ?>
      <?php if ($take_quiz || $attempt_id > 0): ?>
        <!-- Quiz Taking Interface -->
        <?php if (count($questions) > 0 && $current_attempt): ?>
          <div class="quiz-timer" id="quiz-timer">
            <div>
              <span class="timer-label">Time Remaining:</span>
              <span class="timer-count" id="timer-display">
                <?php if ($quiz['time_limit_minutes']): ?>
                  <?php
                  $start_time = new DateTime($current_attempt['start_time']);
                  $current_time = new DateTime();
                  $time_elapsed = $current_time->getTimestamp() - $start_time->getTimestamp();
                  $time_limit_seconds = $quiz['time_limit_minutes'] * 60;
                  $time_remaining = max(0, $time_limit_seconds - $time_elapsed);
                  echo gmdate("H:i:s", $time_remaining);
                  ?>
                <?php else: ?>
                  No time limit
                <?php endif; ?>
              </span>
            </div>
            <div>
              <span id="question-progress">Question 1 of <?php echo count($questions); ?></span>
              <div class="progress-container">
                <div class="progress-bar" id="progress-bar" style="width: <?php echo (1 / count($questions)) * 100; ?>%"></div>
              </div>
            </div>
          </div>

          <div class="quiz-detail">
            <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>

            <form id="quiz-form" method="POST" onsubmit="return confirmSubmission();">
              <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
              <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">

              <?php foreach ($questions as $index => $question): ?>
                <div class="question-container" id="question-<?php echo $index + 1; ?>" <?php echo $index > 0 ? 'style="display:none;"' : ''; ?>>
                  <div class="question-number"><?php echo $index + 1; ?></div>

                  <h4><?php echo htmlspecialchars($question['question_text']); ?></h4>

                  <?php if ($question['question_type'] === 'multiple_choice'): ?>
                    <?php foreach ($question['options'] as $option): ?>
                      <div class="form-check">
                        <input type="radio" name="responses[<?php echo $question['question_id']; ?>]"
                          value="<?php echo $option['option_id']; ?>" class="option-input"
                          id="option-<?php echo $question['question_id']; ?>-<?php echo $option['option_id']; ?>"
                          required>
                        <label class="option-label" for="option-<?php echo $question['question_id']; ?>-<?php echo $option['option_id']; ?>">
                          <?php echo htmlspecialchars($option['option_text']); ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  <?php elseif ($question['question_type'] === 'true_false'): ?>
                    <?php foreach ($question['options'] as $option): ?>
                      <div class="form-check">
                        <input type="radio" name="responses[<?php echo $question['question_id']; ?>]"
                          value="<?php echo $option['option_id']; ?>" class="option-input"
                          id="option-<?php echo $question['question_id']; ?>-<?php echo $option['option_id']; ?>"
                          required>
                        <label class="option-label" for="option-<?php echo $question['question_id']; ?>-<?php echo $option['option_id']; ?>">
                          <?php echo htmlspecialchars($option['option_text']); ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  <?php elseif ($question['question_type'] === 'short_answer'): ?>
                    <input type="text" class="short-answer-input"
                      name="responses[<?php echo $question['question_id']; ?>]"
                      placeholder="Type your answer here" required>
                  <?php endif; ?>

                  <div class="mt-4 d-flex justify-content-between">
                    <?php if ($index > 0): ?>
                      <button type="button" class="btn btn-outline-secondary prev-btn"
                        onclick="showQuestion(<?php echo $index; ?>)">
                        <i class="fas fa-arrow-left"></i> Previous
                      </button>
                    <?php else: ?>
                      <div></div>
                    <?php endif; ?>

                    <?php if ($index < count($questions) - 1): ?>
                      <button type="button" class="btn btn-primary next-btn"
                        onclick="showQuestion(<?php echo $index + 2; ?>)">
                        Next <i class="fas fa-arrow-right"></i>
                      </button>
                    <?php else: ?>
                      <button type="submit" name="submit_quiz" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Submit Quiz
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>

              <div class="submit-warning" id="submit-warning">
                <div class="alert alert-warning">
                  <p><i class="fas fa-exclamation-triangle"></i> You have unanswered questions. Please review your responses before submitting.</p>
                </div>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="alert alert-warning">
            <p>No questions available for this quiz. Please contact your instructor.</p>
            <a href="quiz.php" class="btn btn-primary mt-3">Back to Quizzes</a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <!-- Quiz Details View -->
        <a href="quiz.php" class="back-button">
          <i class="fas fa-arrow-left"></i> Back to All Quizzes
        </a>

        <div class="quiz-detail">
          <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>

          <div class="row">
            <div class="col-md-8">
              <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>

              <h5 class="mt-4">Quiz Information</h5>
              <ul class="list-group">
                <li class="list-group-item"><i class="fas fa-question-circle"></i>
                  Total Questions:
                  <?php
                  $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = ?");
                  $count_stmt->bind_param("i", $quiz_id);
                  $count_stmt->execute();
                  $count_result = $count_stmt->get_result();
                  $count_row = $count_result->fetch_assoc();
                  echo $count_row['count'];
                  $count_stmt->close();
                  ?>
                </li>
                <li class="list-group-item"><i class="fas fa-stopwatch"></i>
                  Time Limit:
                  <?php echo $quiz['time_limit_minutes'] ? $quiz['time_limit_minutes'] . ' minutes' : 'No time limit'; ?>
                </li>
                <li class="list-group-item"><i class="fas fa-check-double"></i>
                  Passing Score: <?php echo $quiz['pass_percentage']; ?>%
                </li>
                <li class="list-group-item"><i class="fas fa-user-tie"></i>
                  Created by: <?php echo htmlspecialchars($quiz['teacher_name']); ?>
                </li>
              </ul>

              <div class="mt-4">
                <h5>Your Past Attempts</h5>
                <?php
                $attempts_stmt = $conn->prepare("
                    SELECT * FROM quiz_attempts 
                    WHERE quiz_id = ? AND student_id = ? 
                    ORDER BY start_time DESC
                  ");
                $attempts_stmt->bind_param("ii", $quiz_id, $student_id);
                $attempts_stmt->execute();
                $attempts_result = $attempts_stmt->get_result();

                if ($attempts_result->num_rows > 0):
                ?>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Attempt #</th>
                          <th>Date</th>
                          <th>Status</th>
                          <th>Score</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $attempt_number = 1;
                        while ($attempt = $attempts_result->fetch_assoc()):
                        ?>
                          <tr>
                            <td><?php echo $attempt_number++; ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($attempt['start_time'])); ?></td>
                            <td>
                              <?php if ($attempt['status'] === 'completed'): ?>
                                <span class="badge badge-success">Completed</span>
                              <?php elseif ($attempt['status'] === 'in_progress'): ?>
                                <span class="badge badge-warning">In Progress</span>
                              <?php else: ?>
                                <span class="badge badge-secondary">Abandoned</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($attempt['score'] !== null): ?>
                                <?php echo number_format($attempt['score'], 1); ?>%
                                <?php if ($attempt['score'] >= $quiz['pass_percentage']): ?>
                                  <span class="badge badge-success">Passed</span>
                                <?php else: ?>
                                  <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                              <?php else: ?>
                                -
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if ($attempt['status'] === 'completed'): ?>
                                <a href="quiz_result.php?attempt_id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-sm btn-info">
                                  <i class="fas fa-eye"></i> View Results
                                </a>
                              <?php elseif ($attempt['status'] === 'in_progress'): ?>
                                <a href="quiz.php?id=<?php echo $quiz_id; ?>&attempt_id=<?php echo $attempt['attempt_id']; ?>" class="btn btn-sm btn-warning">
                                  <i class="fas fa-play"></i> Continue
                                </a>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-info">
                    <p>You haven't attempted this quiz yet.</p>
                  </div>
                <?php endif; ?>
                <?php $attempts_stmt->close(); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="card mb-4">
                <div class="card-body">
                  <h5 class="card-title">Start Quiz</h5>
                  <p class="card-text">Ready to test your knowledge? Start the quiz now!</p>

                  <?php if ($current_attempt): ?>
                    <a href="quiz.php?id=<?php echo $quiz_id; ?>&attempt_id=<?php echo $current_attempt['attempt_id']; ?>" class="btn btn-warning w-100">
                      <i class="fas fa-play"></i> Continue Attempt
                    </a>
                    <small class="text-muted mt-2 d-block">You have an attempt in progress started on <?php echo date('M d, Y g:i A', strtotime($current_attempt['start_time'])); ?></small>
                  <?php else: ?>
                    <a href="quiz.php?id=<?php echo $quiz_id; ?>&take=1" class="btn btn-primary w-100">
                      <i class="fas fa-play"></i> Take Quiz
                    </a>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Your Stats</h5>

                  <?php
                  $stats_stmt = $conn->prepare("
                      SELECT COUNT(*) as attempts, 
                             MAX(score) as best_score, 
                             AVG(score) as avg_score
                      FROM quiz_attempts 
                      WHERE quiz_id = ? AND student_id = ? AND status = 'completed'
                    ");
                  $stats_stmt->bind_param("ii", $quiz_id, $student_id);
                  $stats_stmt->execute();
                  $stats_result = $stats_stmt->get_result();
                  $stats = $stats_result->fetch_assoc();
                  $stats_stmt->close();
                  ?>

                  <div class="row text-center">
                    <div class="col-4">
                      <div class="h3"><?php echo $stats['attempts']; ?></div>
                      <div class="small text-muted">Attempts</div>
                    </div>
                    <div class="col-4">
                      <div class="h3"><?php echo $stats['best_score'] !== null ? number_format($stats['best_score'], 1) . '%' : '-'; ?></div>
                      <div class="small text-muted">Best Score</div>
                    </div>
                    <div class="col-4">
                      <div class="h3"><?php echo $stats['avg_score'] !== null ? number_format($stats['avg_score'], 1) . '%' : '-'; ?></div>
                      <div class="small text-muted">Average</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <!-- Quizzes List View -->
      <div class="quizzes-container">
        <?php if (count($quizzes) > 0): ?>
          <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
              <div class="quiz-header">
                <h5 class="mb-0"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                <small><?php echo htmlspecialchars($quiz['teacher_name']); ?></small>
              </div>

              <div class="quiz-body">
                <?php if ($quiz['attempts_count'] > 0): ?>
                  <?php if ($quiz['best_score'] !== null && $quiz['best_score'] >= $quiz['pass_percentage']): ?>
                    <span class="badge badge-completed">Completed</span>
                  <?php else: ?>
                    <span class="badge badge-attempted">Attempted</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge badge-new">New</span>
                <?php endif; ?>

                <div class="mt-3">
                  <p><?php echo substr(htmlspecialchars($quiz['description']), 0, 100) . (strlen($quiz['description']) > 100 ? '...' : ''); ?></p>
                </div>

                <div class="d-flex justify-content-between mt-3">
                  <div>
                    <i class="fas fa-question-circle"></i>
                    <?php
                    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = ?");
                    $count_stmt->bind_param("i", $quiz['quiz_id']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $count_row = $count_result->fetch_assoc();
                    echo $count_row['count'] . ' questions';
                    $count_stmt->close();
                    ?>
                  </div>
                  <div>
                    <i class="fas fa-stopwatch"></i>
                    <?php echo $quiz['time_limit_minutes'] ? $quiz['time_limit_minutes'] . ' min' : 'No limit'; ?>
                  </div>
                </div>

                <?php if ($quiz['attempts_count'] > 0 && $quiz['best_score'] !== null): ?>
                  <div class="mt-3">
                    <div class="d-flex justify-content-between">
                      <span>Best Score:</span>
                      <span class="fw-bold"><?php echo number_format($quiz['best_score'], 1); ?>%</span>
                    </div>
                    <div class="progress-container">
                      <div class="progress-bar" style="width: <?php echo $quiz['best_score']; ?>%;"></div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="quiz-footer">
                <a href="quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary w-100">
                  <?php if ($quiz['attempts_count'] > 0): ?>
                    <i class="fas fa-eye"></i> View Quiz
                  <?php else: ?>
                    <i class="fas fa-play"></i> Start Quiz
                  <?php endif; ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              <p>No quizzes available at this time. Check back later!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    // Quiz navigation functions
    function showQuestion(questionNumber) {
      // Hide all questions
      const questionContainers = document.querySelectorAll('.question-container');
      questionContainers.forEach(container => {
        container.style.display = 'none';
      });

      // Show the selected question
      const selectedQuestion = document.getElementById('question-' + questionNumber);
      if (selectedQuestion) {
        selectedQuestion.style.display = 'block';
      }

      // Update progress
      const progressBar = document.getElementById('progress-bar');
      const questionProgress = document.getElementById('question-progress');
      const totalQuestions = <?php echo count($questions); ?>;

      if (progressBar && questionProgress) {
        progressBar.style.width = ((questionNumber / totalQuestions) * 100) + '%';
        questionProgress.textContent = 'Question ' + questionNumber + ' of ' + totalQuestions;
      }

      // Scroll to top of question
      window.scrollTo({
        top: document.getElementById('quiz-timer').offsetTop - 20,
        behavior: 'smooth'
      });
    }

    // Timer functionality
    <?php if ($quiz && isset($quiz['time_limit_minutes']) && $quiz['time_limit_minutes'] > 0 && $current_attempt): ?>
      let timeRemaining = <?php echo $time_remaining; ?>;
      const timerDisplay = document.getElementById('timer-display');
      const quizForm = document.getElementById('quiz-form');

      const timerInterval = setInterval(function() {
        timeRemaining--;

        if (timeRemaining <= 0) {
          clearInterval(timerInterval);
          alert('Time\'s up! Your quiz will be submitted automatically.');
          quizForm.submit();
          return;
        }

        // Format time as HH:MM:SS
        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;

        const formattedTime =
          (hours < 10 ? '0' + hours : hours) + ':' +
          (minutes < 10 ? '0' + minutes : minutes) + ':' +
          (seconds < 10 ? '0' + seconds : seconds);

        timerDisplay.textContent = formattedTime;

        // Add warning class when time is running low (less than 5 minutes)
        if (timeRemaining < 300) {
          timerDisplay.classList.add('timer-warning');
        }
      }, 1000);
    <?php endif; ?>

    // Form submission confirmation
    function confirmSubmission() {
      // Check if all questions are answered
      const form = document.getElementById('quiz-form');
      const questions = <?php echo count($questions); ?>;
      let answered = 0;

      // Count answered questions
      for (let i = 0; i < form.elements.length; i++) {
        const element = form.elements[i];
        if (element.name.startsWith('responses') && element.value) {
          // For radio buttons, only count one per question
          if (element.type !== 'radio' || element.checked) {
            answered++;
          }
        }
      }

      // Show warning if not all questions are answered
      const warningElement = document.getElementById('submit-warning');
      if (answered < questions) {
        warningElement.style.display = 'block';
        return false;
      }

      // Ask for confirmation
      return confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.');
    }

    // Auto-save answers to local storage every 30 seconds
    <?php if ($current_attempt): ?>
      const attemptId = <?php echo $attempt_id; ?>;
      const autoSaveInterval = setInterval(function() {
        const form = document.getElementById('quiz-form');
        const answers = {};

        // Collect all answers
        for (let i = 0; i < form.elements.length; i++) {
          const element = form.elements[i];
          if (element.name.startsWith('responses')) {
            // For radio buttons, only save checked ones
            if (element.type !== 'radio' || element.checked) {
              answers[element.name] = element.value;
            }
          }
        }

        // Save to local storage
        localStorage.setItem('quiz_' + attemptId + '_answers', JSON.stringify(answers));

      }, 30000); // Save every 30 seconds

      // Load saved answers on page load
      window.addEventListener('load', function() {
        const savedAnswers = localStorage.getItem('quiz_' + attemptId + '_answers');
        if (savedAnswers) {
          const answers = JSON.parse(savedAnswers);

          // Apply saved answers
          for (const [name, value] of Object.entries(answers)) {
            const elements = document.getElementsByName(name);

            if (elements.length > 0) {
              const element = elements[0];

              if (element.type === 'radio') {
                // For radio buttons, find and check the correct one
                document.querySelector('input[name="' + name + '"][value="' + value + '"]')?.checked = true;
              } else {
                // For other input types
                element.value = value;
              }
            }
          }
        }
      });
    <?php endif; ?>
  </script>
</body>

</html>