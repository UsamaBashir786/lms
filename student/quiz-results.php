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

// Get attempt ID from URL
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if ($attempt_id <= 0) {
  $error = "Invalid quiz attempt.";
} else {
  // Get attempt details
  $attemptQuery = "SELECT a.*, q.title as quiz_title, q.description as quiz_description, 
                  q.pass_percentage, t.full_name as teacher_name 
                  FROM quiz_attempts a 
                  JOIN quizzes q ON a.quiz_id = q.quiz_id 
                  JOIN teachers t ON q.teacher_id = t.id 
                  WHERE a.attempt_id = ? AND a.student_id = ?";
  $stmt = $conn->prepare($attemptQuery);
  $stmt->bind_param("ii", $attempt_id, $student_id);
  $stmt->execute();
  $attemptResult = $stmt->get_result();

  if ($attemptResult->num_rows === 0) {
    $error = "Quiz attempt not found or you don't have permission to view it.";
  } else {
    $attempt = $attemptResult->fetch_assoc();
    $quiz_id = $attempt['quiz_id'];

    // Get total number of questions and points
    $statsQuery = "SELECT COUNT(*) as total_questions, SUM(points) as total_points 
                  FROM quiz_questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $statsResult = $stmt->get_result()->fetch_assoc();
    $totalQuestions = $statsResult['total_questions'];
    $totalPoints = $statsResult['total_points'];

    // Get correct answers count
    $correctQuery = "SELECT COUNT(*) as correct_count 
                    FROM quiz_responses 
                    WHERE attempt_id = ? AND is_correct = 1";
    $stmt = $conn->prepare($correctQuery);
    $stmt->bind_param("i", $attempt_id);
    $stmt->execute();
    $correctResult = $stmt->get_result()->fetch_assoc();
    $correctCount = $correctResult['correct_count'];

    // Calculate percentage and pass status
    $percentage = $attempt['score'];
    $isPassed = $percentage >= $attempt['pass_percentage'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Results</title>
  <?php include 'includes/css-links.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Quiz results specific styles */
    .main-content {
      padding: 20px;
    }

    .results-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      padding: 25px;
      margin-top: 20px;
    }

    .results-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .results-header h2 {
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .score-display {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 30px 0;
    }

    .score-circle {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      color: white;
      margin: 0 20px;
    }

    .score-circle.pass {
      background-color: #2ecc71;
    }

    .score-circle.fail {
      background-color: #e74c3c;
    }

    .score-circle .percentage {
      font-size: 2.5rem;
    }

    .score-circle .label {
      font-size: 1rem;
      text-transform: uppercase;
    }

    .stats-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 15px;
      margin: 30px 0;
    }

    .stat-card {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      min-width: 150px;
      text-align: center;
    }

    .stat-card .number {
      font-size: 1.8rem;
      font-weight: bold;
      color: var(--primary-color);
    }

    .stat-card .label {
      font-size: 0.9rem;
      color: #6c757d;
      text-transform: uppercase;
    }

    .question-review {
      margin-top: 40px;
    }

    .question-card {
      background-color: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 15px;
      position: relative;
      border-left: 5px solid #6c757d;
    }

    .question-card.correct {
      border-left-color: #2ecc71;
    }

    .question-card.incorrect {
      border-left-color: #e74c3c;
    }

    .question-status {
      position: absolute;
      top: 15px;
      right: 15px;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      color: white;
    }

    .question-status.correct {
      background-color: #2ecc71;
    }

    .question-status.incorrect {
      background-color: #e74c3c;
    }

    .option-item {
      padding: 10px;
      margin: 5px 0;
      border-radius: 5px;
      position: relative;
    }

    .option-item.selected {
      background-color: rgba(52, 152, 219, 0.2);
      border: 1px solid #3498db;
    }

    .option-item.correct {
      background-color: rgba(46, 204, 113, 0.2);
      border: 1px solid #2ecc71;
    }

    .option-item.incorrect {
      background-color: rgba(231, 76, 60, 0.2);
      border: 1px solid #e74c3c;
    }

    .option-icon {
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .back-button {
      display: inline-block;
      padding: 8px 15px;
      margin-bottom: 20px;
      background-color: #f8f9fa;
      border-radius: 5px;
      text-decoration: none;
      color: #333;
      border: 1px solid #ddd;
      transition: background-color 0.3s ease;
    }

    .back-button:hover {
      background-color: #e9ecef;
    }

    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
    }

    .action-btn {
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .retry-btn {
      background-color: var(--primary-color);
      color: white;
    }

    .retry-btn:hover {
      background-color: #006025;
      transform: translateY(-2px);
    }

    .all-quizzes-btn {
      background-color: #6c757d;
      color: white;
    }

    .all-quizzes-btn:hover {
      background-color: #495057;
      transform: translateY(-2px);
    }

    .answer-explanation {
      margin-top: 15px;
      padding: 10px;
      background-color: #e9ecef;
      border-radius: 5px;
      font-style: italic;
    }

    .short-answer-container {
      margin-top: 15px;
    }

    .short-answer-container .your-answer,
    .short-answer-container .correct-answer {
      padding: 10px;
      margin-bottom: 5px;
      background-color: #f1f1f1;
      border-radius: 5px;
    }

    .short-answer-container .label {
      font-weight: bold;
      margin-right: 10px;
    }

    @media (max-width: 768px) {
      .score-display {
        flex-direction: column;
      }

      .score-circle {
        margin: 10px 0;
      }

      .stats-container {
        flex-direction: column;
        align-items: center;
      }

      .stat-card {
        width: 100%;
        max-width: 250px;
      }
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
      <h1>Quiz Results</h1>

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
    <?php else: ?>
      <a href="quiz.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Quizzes</a>

      <div class="results-container">
        <div class="results-header">
          <h2><?php echo htmlspecialchars($attempt['quiz_title']); ?></h2>
          <p><?php echo htmlspecialchars($attempt['quiz_description']); ?></p>
          <div class="text-muted">
            <small>
              <i class="fas fa-user-tie"></i> Teacher: <?php echo htmlspecialchars($attempt['teacher_name']); ?> |
              <i class="fas fa-calendar-alt"></i> Attempted: <?php echo date('F j, Y, g:i a', strtotime($attempt['start_time'])); ?> |
              <i class="fas fa-clock"></i> Duration:
              <?php
              $start = new DateTime($attempt['start_time']);
              $end = new DateTime($attempt['end_time'] ?? date('Y-m-d H:i:s'));
              $duration = $start->diff($end);
              echo $duration->format('%H:%I:%S');
              ?>
            </small>
          </div>
        </div>

        <div class="score-display">
          <div class="score-circle <?php echo $isPassed ? 'pass' : 'fail'; ?>">
            <div class="percentage"><?php echo number_format($percentage, 1); ?>%</div>
            <div class="label"><?php echo $isPassed ? 'Passed' : 'Failed'; ?></div>
          </div>
        </div>

        <div class="stats-container">
          <div class="stat-card">
            <div class="number"><?php echo $correctCount; ?>/<?php echo $totalQuestions; ?></div>
            <div class="label">Correct Answers</div>
          </div>
          <div class="stat-card">
            <div class="number"><?php echo $totalQuestions - $correctCount; ?></div>
            <div class="label">Incorrect Answers</div>
          </div>
          <div class="stat-card">
            <div class="number"><?php echo $attempt['pass_percentage']; ?>%</div>
            <div class="label">Passing Score</div>
          </div>
        </div>

        <div class="question-review">
          <h3 class="mb-4">Question Review</h3>

          <?php
          // Get all questions with responses
          $reviewQuery = "SELECT q.question_id, q.question_text, q.question_type, q.points, 
                         r.is_correct, r.selected_option_id, r.text_response 
                         FROM quiz_questions q 
                         LEFT JOIN quiz_responses r ON q.question_id = r.question_id AND r.attempt_id = ? 
                         WHERE q.quiz_id = ? 
                         ORDER BY q.position";
          $stmt = $conn->prepare($reviewQuery);
          $stmt->bind_param("ii", $attempt_id, $quiz_id);
          $stmt->execute();
          $questions = $stmt->get_result();
          $questionNumber = 1;

          while ($question = $questions->fetch_assoc()):
            $questionId = $question['question_id'];
            $isCorrect = (bool)$question['is_correct'];
            $selectedOptionId = $question['selected_option_id'];
            $textResponse = $question['text_response'];
          ?>
            <div class="question-card <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
              <div class="question-status <?php echo $isCorrect ? 'correct' : 'incorrect'; ?>">
                <?php echo $isCorrect ? 'Correct' : 'Incorrect'; ?>
              </div>
              <h4>Question <?php echo $questionNumber; ?> <span class="badge bg-secondary"><?php echo $question['points']; ?> points</span></h4>
              <p><?php echo htmlspecialchars($question['question_text']); ?></p>

              <?php if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false'): ?>
                <?php
                // Get options for this question
                $optionsQuery = "SELECT option_id, option_text, is_correct FROM question_options WHERE question_id = ?";
                $stmt = $conn->prepare($optionsQuery);
                $stmt->bind_param("i", $questionId);
                $stmt->execute();
                $options = $stmt->get_result();

                while ($option = $options->fetch_assoc()):
                  $optionId = $option['option_id'];
                  $isSelected = $selectedOptionId == $optionId;
                  $isCorrectOption = (bool)$option['is_correct'];

                  $optionClass = '';
                  if ($isSelected && $isCorrectOption) {
                    $optionClass = 'selected correct';
                  } elseif ($isSelected && !$isCorrectOption) {
                    $optionClass = 'selected incorrect';
                  } elseif (!$isSelected && $isCorrectOption) {
                    $optionClass = 'correct';
                  }
                ?>
                  <div class="option-item <?php echo $optionClass; ?>">
                    <?php echo htmlspecialchars($option['option_text']); ?>

                    <?php if ($isSelected && $isCorrectOption): ?>
                      <span class="option-icon text-success"><i class="fas fa-check-circle"></i></span>
                    <?php elseif ($isSelected && !$isCorrectOption): ?>
                      <span class="option-icon text-danger"><i class="fas fa-times-circle"></i></span>
                    <?php elseif (!$isSelected && $isCorrectOption): ?>
                      <span class="option-icon text-success"><i class="fas fa-check-circle"></i></span>
                    <?php endif; ?>
                  </div>
                <?php endwhile; ?>

              <?php elseif ($question['question_type'] === 'short_answer'): ?>
                <div class="short-answer-container">
                  <div class="your-answer">
                    <span class="label">Your Answer:</span> <?php echo htmlspecialchars($textResponse ?? 'No answer provided'); ?>
                  </div>

                  <?php
                  // Get correct answer
                  $answerQuery = "SELECT option_text FROM question_options WHERE question_id = ? AND is_correct = 1 LIMIT 1";
                  $stmt = $conn->prepare($answerQuery);
                  $stmt->bind_param("i", $questionId);
                  $stmt->execute();
                  $correctAnswer = $stmt->get_result()->fetch_assoc();
                  ?>

                  <div class="correct-answer">
                    <span class="label">Correct Answer:</span> <?php echo htmlspecialchars($correctAnswer['option_text'] ?? 'Not available'); ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php
            $questionNumber++;
          endwhile;
          ?>
        </div>

        <div class="action-buttons">
          <a href="quiz.php?id=<?php echo $quiz_id; ?>&take=1" class="action-btn retry-btn">
            <i class="fas fa-redo"></i> Retry Quiz
          </a>
          <a href="quiz.php" class="action-btn all-quizzes-btn">
            <i class="fas fa-list"></i> All Quizzes
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>

</html>