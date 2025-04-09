<?php
session_start();
header('Content-Type: application/json');

// Get the student ID from the session
$student_id = $_SESSION['student_id'];

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
$checkpoint_id = isset($input['checkpoint_id']) ? intval($input['checkpoint_id']) : 0;

// Get quiz result data
$score = isset($input['score']) ? floatval($input['score']) : 0;
$correct_answers = isset($input['correct_answers']) ? intval($input['correct_answers']) : 0;
$total_questions = isset($input['total_questions']) ? intval($input['total_questions']) : 0;

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
  exit;
}

// Start transaction
$conn->begin_transaction();
$transaction_success = true;

// Check if this checkpoint is already completed
$sql_check = "SELECT id FROM completed_checkpoints WHERE student_id = ? AND checkpoint_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $student_id, $checkpoint_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
  // Insert the completed checkpoint
  $sql = "INSERT INTO completed_checkpoints (student_id, checkpoint_id, completed_date) VALUES (?, ?, NOW())";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $student_id, $checkpoint_id);

  if (!$stmt->execute()) {
    $transaction_success = false;
    $error_message = $stmt->error;
  }
  $stmt->close();
}

// Store the quiz result
$sql_result = "INSERT INTO quiz_results (student_id, checkpoint_id, score, correct_answers, total_questions, completion_date) 
               VALUES (?, ?, ?, ?, ?, NOW())";
$stmt_result = $conn->prepare($sql_result);
$stmt_result->bind_param("iidii", $student_id, $checkpoint_id, $score, $correct_answers, $total_questions);

if (!$stmt_result->execute()) {
  $transaction_success = false;
  $error_message = $stmt_result->error;
}
$stmt_result->close();

// Commit or rollback transaction
if ($transaction_success) {
  $conn->commit();
  echo json_encode(['success' => true, 'message' => 'Checkpoint and quiz results saved successfully']);
} else {
  $conn->rollback();
  echo json_encode(['success' => false, 'message' => 'Error: ' . $error_message]);
}

$conn->close();
