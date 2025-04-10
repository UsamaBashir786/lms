<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize response array
$response = array(
  'status' => 'error',
  'message' => 'Invalid action'
);

// Database connection
function getConnection()
{
  $servername = "localhost";
  $username = "root"; // Replace with your DB username
  $password = "";     // Replace with your DB password
  $dbname = "lms";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  return $conn;
}

// Validate and sanitize input
function sanitizeInput($input)
{
  if (is_array($input)) {
    return array_map('sanitizeInput', $input);
  }
  return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Process requests
if (isset($_REQUEST['action'])) {
  $action = sanitizeInput($_REQUEST['action']);
  $conn = getConnection();

  switch ($action) {
    case 'create_quiz':
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = sanitizeInput($_POST['title']);
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
        $teacher_id = (int)$_POST['teacher_id'];
        $time_limit = isset($_POST['time_limit_minutes']) && $_POST['time_limit_minutes'] !== '' ? (int)$_POST['time_limit_minutes'] : null;
        $pass_percentage = (int)$_POST['pass_percentage'];
        $questions = isset($_POST['questions']) ? $_POST['questions'] : [];

        if (empty($title) || $teacher_id <= 0 || $pass_percentage < 0 || $pass_percentage > 100) {
          $response['message'] = 'Invalid quiz data';
          break;
        }

        $conn->begin_transaction();

        try {
          // Insert quiz
          $stmt = $conn->prepare("INSERT INTO quizzes (teacher_id, title, description, time_limit_minutes, pass_percentage) VALUES (?, ?, ?, ?, ?)");
          $stmt->bind_param("issii", $teacher_id, $title, $description, $time_limit, $pass_percentage);
          $stmt->execute();
          $quiz_id = $conn->insert_id;
          $stmt->close();

          // Insert questions and options if provided
          if (!empty($questions)) {
            foreach ($questions as $index => $question) {
              $questionText = sanitizeInput($question['text']);
              $questionType = sanitizeInput($question['type']);
              $points = isset($question['points']) ? (int)$question['points'] : 1;
              $position = $index; // Position based on array index

              // Insert question
              $questionStmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, position) VALUES (?, ?, ?, ?, ?)");
              $questionStmt->bind_param("issii", $quiz_id, $questionText, $questionType, $points, $position);
              $questionStmt->execute();
              $question_id = $conn->insert_id;
              $questionStmt->close();

              // Process options based on question type
              if ($questionType === 'multiple_choice' && isset($question['options'])) {
                $correctOptionIndex = isset($question['correctOption']) ? (int)$question['correctOption'] : 0;
                foreach ($question['options'] as $optIndex => $optionText) {
                  $optionText = sanitizeInput($optionText);
                  $isCorrect = ($optIndex === $correctOptionIndex) ? 1 : 0;

                  $optionStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                  $optionStmt->bind_param("isi", $question_id, $optionText, $isCorrect);
                  $optionStmt->execute();
                  $optionStmt->close();
                }
              } elseif ($questionType === 'true_false') {
                $tfAnswer = isset($question['correctOption']) && $question['correctOption'] === 0 ? 1 : 0;

                // Insert True option
                $trueStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, 'True', ?)");
                $trueStmt->bind_param("ii", $question_id, $tfAnswer);
                $trueStmt->execute();
                $trueStmt->close();

                // Insert False option
                $falseStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, 'False', ?)");
                $falseStmt->bind_param("ii", $question_id, (int)!$tfAnswer);
                $falseStmt->execute();
                $falseStmt->close();
              } elseif ($questionType === 'short_answer' && isset($question['correctAnswer'])) {
                $shortAnswer = sanitizeInput($question['correctAnswer']);

                $answerStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, 1)");
                $answerStmt->bind_param("is", $question_id, $shortAnswer);
                $answerStmt->execute();
                $answerStmt->close();
              }
            }
          }

          $conn->commit();
          $response['status'] = 'success';
          $response['message'] = 'Quiz created successfully';
          $response['quiz_id'] = $quiz_id;
        } catch (Exception $e) {
          $conn->rollback();
          $response['message'] = 'Error creating quiz: ' . $e->getMessage();
        }
      }
      break;

    case 'update_quiz':
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $quiz_id = (int)$_POST['quiz_id'];
        $title = sanitizeInput($_POST['title']);
        $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
        $teacher_id = (int)$_POST['teacher_id'];
        $time_limit = isset($_POST['time_limit_minutes']) && $_POST['time_limit_minutes'] !== '' ? (int)$_POST['time_limit_minutes'] : null;
        $pass_percentage = (int)$_POST['pass_percentage'];
        $questions = isset($_POST['questions']) ? $_POST['questions'] : [];

        if (empty($title) || $teacher_id <= 0 || $pass_percentage < 0 || $pass_percentage > 100 || $quiz_id <= 0) {
          $response['message'] = 'Invalid quiz data';
          break;
        }

        $conn->begin_transaction();
        try {
          $stmt = $conn->prepare("UPDATE quizzes SET teacher_id = ?, title = ?, description = ?, time_limit_minutes = ?, pass_percentage = ? WHERE quiz_id = ?");
          $stmt->bind_param("issiii", $teacher_id, $title, $description, $time_limit, $pass_percentage, $quiz_id);
          $stmt->execute();
          $stmt->close();

          if (!empty($questions)) {
            $processedQuestionIds = [];
            foreach ($questions as $index => $question) {
              $questionText = sanitizeInput($question['text']);
              $questionType = sanitizeInput($question['type']);
              $points = (int)$question['points'];
              $position = (int)$question['position'];
              $questionId = isset($question['id']) && !empty($question['id']) ? (int)$question['id'] : 0;

              if ($questionId > 0) {
                $questionStmt = $conn->prepare("UPDATE quiz_questions SET question_text = ?, question_type = ?, points = ?, position = ? WHERE question_id = ? AND quiz_id = ?");
                $questionStmt->bind_param("ssiiii", $questionText, $questionType, $points, $position, $questionId, $quiz_id);
                $questionStmt->execute();
                $questionStmt->close();
                $processedQuestionIds[] = $questionId;
              } else {
                $questionStmt = $conn->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, position) VALUES (?, ?, ?, ?, ?)");
                $questionStmt->bind_param("issii", $quiz_id, $questionText, $questionType, $points, $position);
                $questionStmt->execute();
                $questionId = $conn->insert_id;
                $processedQuestionIds[] = $questionId;
                $questionStmt->close();
              }

              // Delete existing options
              $deleteOptionsStmt = $conn->prepare("DELETE FROM question_options WHERE question_id = ?");
              $deleteOptionsStmt->bind_param("i", $questionId);
              $deleteOptionsStmt->execute();
              $deleteOptionsStmt->close();

              // Process options
              if ($questionType === 'multiple_choice' && isset($question['options'])) {
                $correctOptionIndex = (int)$question['correctOption'];
                foreach ($question['options'] as $optIndex => $optionText) {
                  $optionText = sanitizeInput($optionText);
                  $isCorrect = ($optIndex === $correctOptionIndex) ? 1 : 0;
                  $optionStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                  $optionStmt->bind_param("isi", $questionId, $optionText, $isCorrect);
                  $optionStmt->execute();
                  $optionStmt->close();
                }
              } elseif ($questionType === 'true_false') {
                $tfAnswer = isset($question['correctOption']) && $question['correctOption'] === 0 ? 1 : 0;
                $trueStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, 'True', ?)");
                $trueStmt->bind_param("ii", $questionId, $tfAnswer);
                $trueStmt->execute();
                $trueStmt->close();

                $falseStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, 'False', ?)");
                $falseStmt->bind_param("ii", $questionId, (int)!$tfAnswer);
                $falseStmt->execute();
                $falseStmt->close();
              } elseif ($questionType === 'short_answer' && isset($question['correctAnswer'])) {
                $shortAnswer = sanitizeInput($question['correctAnswer']);
                $answerStmt = $conn->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, 1)");
                $answerStmt->bind_param("is", $questionId, $shortAnswer);
                $answerStmt->execute();
                $answerStmt->close();
              }
            }

            if (!empty($processedQuestionIds)) {
              $placeholders = str_repeat('?,', count($processedQuestionIds) - 1) . '?';
              $deleteStmt = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id = ? AND question_id NOT IN ($placeholders)");
              $params = array_merge([$quiz_id], $processedQuestionIds);
              $deleteStmt->bind_param('i' . str_repeat('i', count($processedQuestionIds)), ...$params);
              $deleteStmt->execute();
              $deleteStmt->close();
            }
          }

          $conn->commit();
          $response['status'] = 'success';
          $response['message'] = 'Quiz updated successfully';
        } catch (Exception $e) {
          $conn->rollback();
          $response['message'] = 'Error updating quiz: ' . $e->getMessage();
        }
      }
      break;

    case 'get_quiz':
      if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['quiz_id'])) {
        $quiz_id = (int)$_GET['quiz_id'];
        $quizStmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
        $quizStmt->bind_param("i", $quiz_id);
        $quizStmt->execute();
        $quizResult = $quizStmt->get_result();

        if ($quizResult->num_rows > 0) {
          $quiz = $quizResult->fetch_assoc();
          $questionStmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY position");
          $questionStmt->bind_param("i", $quiz_id);
          $questionStmt->execute();
          $questionResult = $questionStmt->get_result();
          $questions = [];

          while ($question = $questionResult->fetch_assoc()) {
            $optionStmt = $conn->prepare("SELECT * FROM question_options WHERE question_id = ?");
            $optionStmt->bind_param("i", $question['question_id']);
            $optionStmt->execute();
            $optionResult = $optionStmt->get_result();
            $options = [];
            $correctAnswer = null;

            while ($option = $optionResult->fetch_assoc()) {
              $options[] = [
                'option_id' => $option['option_id'],
                'option_text' => $option['option_text'],
                'is_correct' => (bool)$option['is_correct']
              ];
              if ($option['is_correct']) $correctAnswer = $option['option_text'];
            }

            $questionData = [
              'question_id' => $question['question_id'],
              'question_text' => $question['question_text'],
              'question_type' => $question['question_type'],
              'points' => $question['points'],
              'position' => $question['position'],
              'options' => $options
            ];

            if ($question['question_type'] === 'true_false') {
              $questionData['correct_answer'] = strtolower($correctAnswer);
            } elseif ($question['question_type'] === 'short_answer') {
              $questionData['correct_answer'] = $correctAnswer;
            }
            $questions[] = $questionData;
            $optionStmt->close();
          }

          $response['status'] = 'success';
          $response['quiz'] = $quiz;
          $response['questions'] = $questions;
        } else {
          $response['message'] = 'Quiz not found';
        }
        $quizStmt->close();
        $questionStmt->close();
      }
      break;

    case 'delete_quiz':
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
        $quiz_id = (int)$_POST['quiz_id'];
        $conn->begin_transaction();
        try {
          $questionStmt = $conn->prepare("SELECT question_id FROM quiz_questions WHERE quiz_id = ?");
          $questionStmt->bind_param("i", $quiz_id);
          $questionStmt->execute();
          $questionResult = $questionStmt->get_result();
          $questionIds = [];
          while ($row = $questionResult->fetch_assoc()) {
            $questionIds[] = $row['question_id'];
          }
          $questionStmt->close();

          if (!empty($questionIds)) {
            $placeholders = str_repeat('?,', count($questionIds) - 1) . '?';
            $deleteOptionsStmt = $conn->prepare("DELETE FROM question_options WHERE question_id IN ($placeholders)");
            $deleteOptionsStmt->bind_param(str_repeat('i', count($questionIds)), ...$questionIds);
            $deleteOptionsStmt->execute();
            $deleteOptionsStmt->close();
          }

          $deleteStmt = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
          $deleteStmt->bind_param("i", $quiz_id);
          $deleteStmt->execute();
          $deleteStmt->close();

          $deleteQuizStmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
          $deleteQuizStmt->bind_param("i", $quiz_id);
          $deleteQuizStmt->execute();
          $deleteQuizStmt->close();

          $conn->commit();
          $response['status'] = 'success';
          $response['message'] = 'Quiz deleted successfully';
        } catch (Exception $e) {
          $conn->rollback();
          $response['message'] = 'Error deleting quiz: ' . $e->getMessage();
        }
      }
      break;

    case 'get_results':
      if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['quiz_id'])) {
        $quiz_id = (int)$_GET['quiz_id'];
        $quizStmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
        $quizStmt->bind_param("i", $quiz_id);
        $quizStmt->execute();
        $quizResult = $quizStmt->get_result();

        if ($quizResult->num_rows > 0) {
          $quiz = $quizResult->fetch_assoc();
          $statsStmt = $conn->prepare("SELECT COUNT(*) as total_attempts, AVG(score) as avg_score FROM quiz_attempts WHERE quiz_id = ?");
          $statsStmt->bind_param("i", $quiz_id);
          $statsStmt->execute();
          $statsResult = $statsStmt->get_result();
          $stats = $statsResult->fetch_assoc();

          $response['status'] = 'success';
          $response['quiz'] = $quiz;
          $response['stats'] = [
            'total_attempts' => $stats['total_attempts'],
            'avg_score' => $stats['avg_score'] ? round($stats['avg_score'], 1) : 0
          ];
        } else {
          $response['message'] = 'Quiz not found';
        }
        $quizStmt->close();
        $statsStmt->close();
      }
      break;
  }
  $conn->close();
  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .quiz-card {
      transition: all 0.3s ease;
    }

    .quiz-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
    }

    .action-btn {
      width: 38px;
      height: 38px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .modal-dialog.large-modal {
      max-width: 850px;
    }

    .question-container {
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
      position: relative;
    }

    .option-container {
      margin-top: 1rem;
    }

    .delete-question-btn {
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .drag-handle {
      cursor: move;
      position: absolute;
      top: 10px;
      left: 10px;
      color: #6c757d;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar Placeholder -->
      <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <!-- <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li> -->
            <!-- <li class="nav-item"><a class="nav-link active" href="#">Quizzes</a></li> -->
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4" id="mainContent">
        <div class="bg-success text-white p-4 mb-4 d-flex justify-content-between align-items-center">
          <h2 class="mb-0">Quiz Management</h2>
          <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createQuizModal">
            <i class="fas fa-plus-circle me-2"></i>Create New Quiz
          </button>
        </div>

        <!-- Quiz List -->
        <div class="row g-4" id="quizList">
          <?php
          $conn = getConnection();
          $quizQuery = "SELECT q.*, t.full_name as teacher_name FROM quizzes q JOIN teachers t ON q.teacher_id = t.id ORDER BY q.created_at DESC";
          $quizResult = $conn->query($quizQuery);

          if ($quizResult->num_rows > 0) {
            while ($quiz = $quizResult->fetch_assoc()) {
              $questionCount = $conn->query("SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = {$quiz['quiz_id']}")->fetch_assoc()['count'];
              $attemptCount = $conn->query("SELECT COUNT(*) as count FROM quiz_attempts WHERE quiz_id = {$quiz['quiz_id']}")->fetch_assoc()['count'];
              $avgScoreResult = $conn->query("SELECT AVG(score) as avg FROM quiz_attempts WHERE quiz_id = {$quiz['quiz_id']} AND status = 'completed'");
              $avgScore = $avgScoreResult->fetch_assoc()['avg'] ? number_format($avgScoreResult->fetch_assoc()['avg'], 1) . "%" : "N/A";
          ?>
              <div class="col-md-6 col-lg-4 quiz-item" data-teacher="<?php echo $quiz['teacher_id']; ?>">
                <div class="card quiz-card h-100">
                  <div class="card-body">
                    <h5 class="card-title text-truncate"><?php echo $quiz['title']; ?></h5>
                    <p class="text-muted mb-2">By: <?php echo $quiz['teacher_name']; ?></p>
                    <p class="card-text text-truncate"><?php echo $quiz['description']; ?></p>
                    <div class="row text-center mt-3 mb-3">
                      <div class="col">
                        <div class="fw-bold"><?php echo $questionCount; ?></div><small class="text-muted">Questions</small>
                      </div>
                      <div class="col">
                        <div class="fw-bold"><?php echo $attemptCount; ?></div><small class="text-muted">Attempts</small>
                      </div>
                      <div class="col">
                        <div class="fw-bold"><?php echo $avgScore; ?></div><small class="text-muted">Avg Score</small>
                      </div>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                      <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="editQuiz(<?php echo $quiz['quiz_id']; ?>)">
                        <i class="fas fa-edit me-1"></i>Edit
                      </button>
                      <button class="btn btn-sm btn-outline-success flex-grow-1" onclick="viewResults(<?php echo $quiz['quiz_id']; ?>)">
                        <i class="fas fa-chart-bar me-1"></i>Results
                      </button>
                      <button class="btn btn-sm btn-outline-danger action-btn" onclick="deleteQuiz(<?php echo $quiz['quiz_id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </div>
                  <div class="card-footer text-muted small">
                    <i class="fas fa-calendar-alt me-1"></i>Created: <?php echo date('M d, Y', strtotime($quiz['created_at'])); ?>
                  </div>
                </div>
              </div>
          <?php
            }
          } else {
            echo '<div class="col-12"><div class="alert alert-info">No quizzes found. Create your first quiz!</div></div>';
          }
          $conn->close();
          ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Create Quiz Modal -->
  <div class="modal fade" id="createQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Create New Quiz</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="createQuizForm" method="POST">
            <input type="hidden" name="action" value="create_quiz">
            <div class="mb-3">
              <label class="form-label">Quiz Title</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Assigned Teacher</label>
                <select class="form-select" name="teacher_id" required>
                  <option value="">Select Teacher</option>
                  <?php
                  $conn = getConnection();
                  $teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name ASC");
                  while ($teacher = $teacherResult->fetch_assoc()) {
                    echo "<option value='{$teacher['id']}'>{$teacher['full_name']}</option>";
                  }
                  $conn->close();
                  ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Time Limit (minutes)</label>
                <input type="number" class="form-control" name="time_limit_minutes" min="0">
              </div>
              <div class="col-md-3">
                <label class="form-label">Pass Percentage</label>
                <input type="number" class="form-control" name="pass_percentage" value="60" min="0" max="100" required>
              </div>
            </div>

            <hr>
            <h5 class="mb-3">Quiz Questions</h5>
            <div id="create_questions_container">
              <!-- Questions will be added here -->
            </div>

            <div class="text-center mb-3">
              <button type="button" class="btn btn-outline-primary" id="create_add_question_btn">
                <i class="fas fa-plus-circle me-2"></i>Add New Question
              </button>
            </div>

            <div class="text-end">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Create Quiz</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Quiz Modal -->
  <div class="modal fade" id="editQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable large-modal">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Quiz</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editQuizForm" method="POST">
            <input type="hidden" name="action" value="update_quiz">
            <input type="hidden" name="quiz_id" id="edit_quiz_id">
            <div class="mb-3">
              <label class="form-label">Quiz Title</label>
              <input type="text" class="form-control" name="title" id="edit_title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Assigned Teacher</label>
                <select class="form-select" name="teacher_id" id="edit_teacher_id" required>
                  <option value="">Select Teacher</option>
                  <?php
                  $conn = getConnection();
                  $teacherResult = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name ASC");
                  while ($teacher = $teacherResult->fetch_assoc()) {
                    echo "<option value='{$teacher['id']}'>{$teacher['full_name']}</option>";
                  }
                  $conn->close();
                  ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Time Limit (minutes)</label>
                <input type="number" class="form-control" name="time_limit_minutes" id="edit_time_limit" min="0">
              </div>
              <div class="col-md-3">
                <label class="form-label">Pass Percentage</label>
                <input type="number" class="form-control" name="pass_percentage" id="edit_pass_percentage" min="0" max="100" required>
              </div>
            </div>
            <hr>
            <h5 class="mb-3">Quiz Questions</h5>
            <div id="questions_container"></div>
            <div class="text-center mb-3">
              <button type="button" class="btn btn-outline-primary" id="addQuestionBtn">
                <i class="fas fa-plus-circle me-2"></i>Add New Question
              </button>
            </div>
            <div class="text-end mt-4">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Question Template -->
  <template id="questionTemplate">
    <div class="question-container" data-question-id="">
      <i class="fas fa-grip-vertical drag-handle"></i>
      <button type="button" class="btn btn-sm btn-outline-danger delete-question-btn"><i class="fas fa-times"></i></button>
      <div class="mb-3">
        <label class="form-label">Question Text</label>
        <textarea class="form-control question-text" name="questions[INDEX][text]" rows="2" required></textarea>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Question Type</label>
          <select class="form-select question-type" name="questions[INDEX][type]" onchange="showQuestionTypeOptions(this, INDEX)">
            <option value="multiple_choice">Multiple Choice</option>
            <option value="true_false">True/False</option>
            <option value="short_answer">Short Answer</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Points</label>
          <input type="number" class="form-control question-points" name="questions[INDEX][points]" value="1" min="1">
        </div>
      </div>
      <div class="option-container" data-for="multiple_choice">
        <label class="form-label">Options (select the correct answer)</label>
        <div class="options-list">
          <div class="input-group mb-2">
            <div class="input-group-text">
              <input type="radio" class="form-check-input mt-0 correct-option" name="questions[INDEX][correctOption]" value="0" required>
            </div>
            <input type="text" class="form-control option-text" name="questions[INDEX][options][0]" placeholder="Option A" required>
            <button type="button" class="btn btn-outline-danger delete-option-btn"><i class="fas fa-times"></i></button>
          </div>
          <div class="input-group mb-2">
            <div class="input-group-text">
              <input type="radio" class="form-check-input mt-0 correct-option" name="questions[INDEX][correctOption]" value="1">
            </div>
            <input type="text" class="form-control option-text" name="questions[INDEX][options][1]" placeholder="Option B" required>
            <button type="button" class="btn btn-outline-danger delete-option-btn"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mt-2 add-option-btn" onclick="addOption(INDEX)"><i class="fas fa-plus me-1"></i>Add Option</button>
      </div>
      <div class="option-container" data-for="true_false" style="display:none;">
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="questions[INDEX][correctOption]" value="0" id="tf_true_INDEX" required>
          <label class="form-check-label" for="tf_true_INDEX">True</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="questions[INDEX][correctOption]" value="1" id="tf_false_INDEX">
          <label class="form-check-label" for="tf_false_INDEX">False</label>
        </div>
      </div>
      <div class="option-container" data-for="short_answer" style="display:none;">
        <label class="form-label">Correct Answer</label>
        <input type="text" class="form-control" name="questions[INDEX][correctAnswer]" placeholder="Correct answer (exact match)">
      </div>
      <input type="hidden" name="questions[INDEX][id]" class="question-id" value="">
      <input type="hidden" name="questions[INDEX][position]" class="question-position" value="INDEX">
    </div>
  </template>

  <!-- View Results Modal -->
  <div class="modal fade" id="viewResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Quiz Results</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h3 id="results_quiz_title" class="mb-3"></h3>
          <div class="row mb-4">
            <div class="col-md-6 text-center">
              <div class="card p-3">
                <h4 id="total_attempts" class="mb-0 text-primary">0</h4>
                <small class="text-muted">Total Attempts</small>
              </div>
            </div>
            <div class="col-md-6 text-center">
              <div class="card p-3">
                <h4 id="avg_score" class="mb-0 text-info">0%</h4>
                <small class="text-muted">Average Score</small>
              </div>
            </div>
          </div>
          <div id="no_attempts_message" class="alert alert-info" style="display:none;">No attempts have been made for this quiz yet.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script>
    // Global variables
    let questionCounter = 0;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize create quiz form
      document.getElementById('create_add_question_btn').addEventListener('click', function() {
        addQuestionToCreateForm();
      });

      // Initialize edit quiz form
      document.getElementById('addQuestionBtn').addEventListener('click', function() {
        addQuestion();
      });

      // Add first question to create form by default
      addQuestionToCreateForm();

      // Initialize form submission
      initFormSubmissions();

      // Initialize sortable for questions
      initSortable();
    });

    function addQuestionToCreateForm() {
      const template = document.getElementById('questionTemplate');
      const container = document.getElementById('create_questions_container');
      const clone = template.content.cloneNode(true);
      const qIndex = questionCounter++;

      // Replace INDEX placeholders with actual index
      clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
        el.name = el.name.replace('INDEX', qIndex);
      });
      clone.querySelectorAll('[id*="INDEX"]').forEach(el => {
        el.id = el.id.replace('INDEX', qIndex);
      });
      clone.querySelectorAll('[onclick*="INDEX"]').forEach(el => {
        el.setAttribute('onclick', el.getAttribute('onclick').replace('INDEX', qIndex));
      });

      // Set question position
      clone.querySelector('.question-position').value = qIndex;

      // Add event listeners
      const qContainer = clone.querySelector('.question-container');
      qContainer.dataset.index = qIndex;

      clone.querySelector('.delete-question-btn').addEventListener('click', function() {
        deleteQuestion(qContainer);
      });

      // Add to container
      container.appendChild(clone);
    }

    function editQuiz(quizId) {
      fetch(`?action=get_quiz&quiz_id=${quizId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('edit_quiz_id').value = data.quiz.quiz_id;
            document.getElementById('edit_title').value = data.quiz.title;
            document.getElementById('edit_description').value = data.quiz.description;
            document.getElementById('edit_teacher_id').value = data.quiz.teacher_id;
            document.getElementById('edit_time_limit').value = data.quiz.time_limit_minutes;
            document.getElementById('edit_pass_percentage').value = data.quiz.pass_percentage;

            const container = document.getElementById('questions_container');
            container.innerHTML = '';
            data.questions.forEach((q, i) => addQuestion(q, i));

            new bootstrap.Modal(document.getElementById('editQuizModal')).show();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => alert('Fetch error: ' + error));
    }

    function addQuestion(questionData = null, index = null) {
      const template = document.getElementById('questionTemplate');
      const container = document.getElementById('questions_container');
      const clone = template.content.cloneNode(true);
      const qIndex = index !== null ? index : container.children.length;

      clone.querySelectorAll('[name*="INDEX"]').forEach(el => el.name = el.name.replace('INDEX', qIndex));
      clone.querySelectorAll('[id*="INDEX"]').forEach(el => el.id = el.id.replace('INDEX', qIndex));
      clone.querySelectorAll('[onclick*="INDEX"]').forEach(el => {
        el.setAttribute('onclick', el.getAttribute('onclick').replace('INDEX', qIndex));
      });

      const qContainer = clone.querySelector('.question-container');
      qContainer.dataset.index = qIndex;

      if (questionData) {
        qContainer.dataset.questionId = questionData.question_id;
        clone.querySelector('.question-id').value = questionData.question_id;
        clone.querySelector('.question-text').value = questionData.question_text;
        clone.querySelector('.question-type').value = questionData.question_type;
        clone.querySelector('.question-points').value = questionData.points;
        clone.querySelector('.question-position').value = questionData.position;

        if (questionData.question_type === 'multiple_choice') {
          const optionsList = clone.querySelector('.options-list');
          optionsList.innerHTML = '';
          let correctIndex = 0;

          questionData.options.forEach((opt, i) => {
            if (opt.is_correct) correctIndex = i;

            optionsList.innerHTML += `
                            <div class="input-group mb-2">
                                <div class="input-group-text">
                                    <input type="radio" class="form-check-input mt-0 correct-option" name="questions[${qIndex}][correctOption]" value="${i}" ${opt.is_correct ? 'checked' : ''} required>
                                </div>
                                <input type="text" class="form-control option-text" name="questions[${qIndex}][options][${i}]" value="${opt.option_text}" required>
                                <button type="button" class="btn btn-outline-danger delete-option-btn" onclick="deleteOption(${qIndex}, ${i})"><i class="fas fa-times"></i></button>
                            </div>
                        `;
          });

          showQuestionTypeOptions(qContainer.querySelector('.question-type'), qIndex);
        } else if (questionData.question_type === 'true_false') {
          const tfValue = questionData.correct_answer === 'true' ? 0 : 1;
          clone.querySelector(`input[name="questions[${qIndex}][correctOption]"][value="${tfValue}"]`).checked = true;
          showQuestionTypeOptions(qContainer.querySelector('.question-type'), qIndex);
        } else if (questionData.question_type === 'short_answer') {
          clone.querySelector(`input[name="questions[${qIndex}][correctAnswer]"]`).value = questionData.correct_answer;
          showQuestionTypeOptions(qContainer.querySelector('.question-type'), qIndex);
        }
      }

      clone.querySelector('.delete-question-btn').addEventListener('click', function() {
        deleteQuestion(qContainer);
      });

      container.appendChild(clone);
      updateQuestionPositions();
      return qContainer;
    }

    function showQuestionTypeOptions(selectElement, questionIndex) {
      const type = selectElement.value;
      const container = selectElement.closest('.question-container');

      container.querySelectorAll('.option-container').forEach(c => {
        c.style.display = c.dataset.for === type ? 'block' : 'none';
      });
    }

    function addOption(questionIndex) {
      const container = document.querySelector(`.question-container[data-index="${questionIndex}"]`);
      if (!container) return;

      const optionsList = container.querySelector('.options-list');
      const optCount = optionsList.children.length;

      if (optCount >= 8) {
        alert('Maximum 8 options allowed');
        return;
      }

      const newOption = document.createElement('div');
      newOption.className = 'input-group mb-2';
      newOption.innerHTML = `
                <div class="input-group-text">
                    <input type="radio" class="form-check-input mt-0 correct-option" name="questions[${questionIndex}][correctOption]" value="${optCount}" required>
                </div>
                <input type="text" class="form-control option-text" name="questions[${questionIndex}][options][${optCount}]" placeholder="Option ${String.fromCharCode(65 + optCount)}" required>
                <button type="button" class="btn btn-outline-danger delete-option-btn" onclick="deleteOption(${questionIndex}, ${optCount})"><i class="fas fa-times"></i></button>
            `;

      optionsList.appendChild(newOption);
    }

    function deleteOption(questionIndex, optionIndex) {
      const container = document.querySelector(`.question-container[data-index="${questionIndex}"]`);
      if (!container) return;

      const optionsList = container.querySelector('.options-list');

      if (optionsList.children.length <= 2) {
        alert('Must have at least 2 options.');
        return;
      }

      // Remove option
      optionsList.children[optionIndex].remove();

      // Update indices
      Array.from(optionsList.children).forEach((opt, i) => {
        const radio = opt.querySelector('.correct-option');
        const input = opt.querySelector('.option-text');
        const button = opt.querySelector('.delete-option-btn');

        radio.value = i;
        input.name = `questions[${questionIndex}][options][${i}]`;
        input.placeholder = `Option ${String.fromCharCode(65 + i)}`;
        button.setAttribute('onclick', `deleteOption(${questionIndex}, ${i})`);
      });
    }

    function deleteQuestion(container) {
      if (container.closest('form').querySelectorAll('.question-container').length <= 1) {
        alert('You must have at least one question.');
        return;
      }

      if (confirm('Delete this question?')) {
        container.remove();
        updateQuestionPositions();
      }
    }

    function updateQuestionPositions() {
      document.querySelectorAll('#questions_container .question-container').forEach((c, i) => {
        c.querySelector('.question-position').value = i;
      });

      document.querySelectorAll('#create_questions_container .question-container').forEach((c, i) => {
        c.querySelector('.question-position').value = i;
      });
    }

    function viewResults(quizId) {
      fetch(`?action=get_results&quiz_id=${quizId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('results_quiz_title').textContent = data.quiz.title;
            document.getElementById('total_attempts').textContent = data.stats.total_attempts;
            document.getElementById('avg_score').textContent = data.stats.avg_score + '%';
            document.getElementById('no_attempts_message').style.display = data.stats.total_attempts > 0 ? 'none' : 'block';
            new bootstrap.Modal(document.getElementById('viewResultsModal')).show();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => alert('Fetch error: ' + error));
    }

    function deleteQuiz(quizId) {
      if (confirm('Delete this quiz? All questions and student attempts will be permanently deleted.')) {
        fetch('?action=delete_quiz', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `quiz_id=${quizId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              alert('Quiz deleted successfully');
              location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => alert('Fetch error: ' + error));
      }
    }

    function initFormSubmissions() {
      document.getElementById('createQuizForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validation
        if (!validateQuizForm(this)) {
          return;
        }

        // Get form data
        const formData = new FormData(this);

        fetch('?', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              alert('Quiz created successfully!');
              bootstrap.Modal.getInstance(document.getElementById('createQuizModal')).hide();
              location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => alert('Submit error: ' + error));
      });

      document.getElementById('editQuizForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Validation
        if (!validateQuizForm(this)) {
          return;
        }

        // Get form data
        const formData = new FormData(this);

        fetch('?', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              alert('Quiz updated successfully!');
              bootstrap.Modal.getInstance(document.getElementById('editQuizModal')).hide();
              location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => alert('Submit error: ' + error));
      });
    }

    function validateQuizForm(form) {
      // Check title
      if (!form.querySelector('input[name="title"]').value.trim()) {
        alert('Quiz title is required');
        return false;
      }

      // Check teacher
      if (!form.querySelector('select[name="teacher_id"]').value) {
        alert('Please select a teacher');
        return false;
      }

      // Check questions
      const questions = form.querySelectorAll('.question-container');
      if (questions.length === 0) {
        alert('At least one question is required');
        return false;
      }

      // Check each question
      for (let i = 0; i < questions.length; i++) {
        const q = questions[i];
        const questionText = q.querySelector('textarea.question-text').value.trim();
        const questionType = q.querySelector('select.question-type').value;

        if (!questionText) {
          alert(`Question ${i+1} text is required`);
          return false;
        }

        if (questionType === 'multiple_choice') {
          const options = q.querySelectorAll('.option-text');
          const correctOption = q.querySelector('.correct-option:checked');

          if (!correctOption) {
            alert(`Please select correct answer for question ${i+1}`);
            return false;
          }

          for (let j = 0; j < options.length; j++) {
            if (!options[j].value.trim()) {
              alert(`Option ${String.fromCharCode(65 + j)} for question ${i+1} is required`);
              return false;
            }
          }
        } else if (questionType === 'true_false') {
          const correctOption = q.querySelector(`input[name^="questions["][name$="][correctOption]"]:checked`);

          if (!correctOption) {
            alert(`Please select correct answer (True/False) for question ${i+1}`);
            return false;
          }
        } else if (questionType === 'short_answer') {
          const correctAnswer = q.querySelector('input[name$="[correctAnswer]"]').value.trim();

          if (!correctAnswer) {
            alert(`Correct answer for question ${i+1} is required`);
            return false;
          }
        }
      }

      return true;
    }

    function initSortable() {
      new Sortable(document.getElementById('questions_container'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: updateQuestionPositions
      });

      new Sortable(document.getElementById('create_questions_container'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: updateQuestionPositions
      });
    }
  </script>
</body>

</html>