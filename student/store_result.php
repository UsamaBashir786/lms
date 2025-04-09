<?php
// Start the session to access session variables
session_start();

// Connect to the database
$servername = "localhost";
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "lms"; // Database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Ensure the student_id is stored in session after login
// Example of how you might set the session variable during login (not shown here, just for reference):
// $_SESSION['student_id'] = $studentId;

// Get POST data from the request
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
  // Get student_id from session
  if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];  // Retrieve the student_id from session
  } else {
    echo json_encode(["status" => "error", "message" => "Student is not logged in"]);
    exit;
  }

  $video_id = $data['video_id'];
  $checkpoint_id = $data['checkpoint_id'];
  $correct_answers = $data['correct_answers'];
  $total_questions = $data['total_questions'];
  $score = $data['score'];

  // Check if the student exists
  $student_check_query = "SELECT id FROM students WHERE id = '$student_id'";
  $student_check_result = $conn->query($student_check_query);

  if ($student_check_result->num_rows > 0) {
    // Student exists, proceed to insert result
    $sql = "INSERT INTO student_results (student_id, video_id, checkpoint_id, correct_answers, total_questions, score) 
                VALUES ('$student_id', '$video_id', '$checkpoint_id', '$correct_answers', '$total_questions', '$score')";

    if ($conn->query($sql) === TRUE) {
      echo json_encode(["status" => "success", "message" => "Result stored successfully"]);
    } else {
      echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
  } else {
    // Student doesn't exist
    echo json_encode(["status" => "error", "message" => "Student not found"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Invalid input data"]);
}

$conn->close();
