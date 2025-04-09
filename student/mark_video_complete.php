<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $_SESSION['student_id'];
$video_id = $data['video_id'];

$conn = new mysqli("localhost", "root", "", "lms");

$sql = "INSERT INTO completed_videos (student_id, video_id) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE completed_at = CURRENT_TIMESTAMP";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $video_id);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
