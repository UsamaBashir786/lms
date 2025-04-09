<?php
// Include the database connection file
include('db_connection.php');

// Get the data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data
$student_id = $data['student_id'];
$video_id = $data['video_id'];
$checkpoint_id = $data['checkpoint_id'];
$correct_answers = $data['correct_answers'];
$total_questions = $data['total_questions'];
$score = $data['score'];

// Check if the required data is provided
if (!isset($student_id) || !isset($video_id) || !isset($checkpoint_id) || !isset($correct_answers) || !isset($total_questions) || !isset($score)) {
  echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
  exit;
}

try {
  // Prepare the SQL query to insert the data into the database
  $stmt = $pdo->prepare("INSERT INTO student_progress (student_id, video_id, checkpoint_id, correct_answers, total_questions, score) 
                           VALUES (:student_id, :video_id, :checkpoint_id, :correct_answers, :total_questions, :score)");

  // Bind the parameters
  $stmt->bindParam(':student_id', $student_id);
  $stmt->bindParam(':video_id', $video_id);
  $stmt->bindParam(':checkpoint_id', $checkpoint_id);
  $stmt->bindParam(':correct_answers', $correct_answers);
  $stmt->bindParam(':total_questions', $total_questions);
  $stmt->bindParam(':score', $score);

  // Execute the query
  $stmt->execute();

  // Return a success message
  echo json_encode(['status' => 'success', 'message' => 'Progress saved successfully']);
} catch (PDOException $e) {
  // Handle errors (e.g., database connection issues)
  echo json_encode(['status' => 'error', 'message' => 'Error saving progress: ' . $e->getMessage()]);
}
