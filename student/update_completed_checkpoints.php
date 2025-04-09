<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  if (isset($data['checkpoint_id'])) {
    // Save checkpoint ID in session
    if (!isset($_SESSION['completed_checkpoints'])) {
      $_SESSION['completed_checkpoints'] = [];
    }

    $_SESSION['completed_checkpoints'][] = $data['checkpoint_id'];
    echo json_encode(['status' => 'success', 'completed_checkpoints' => $_SESSION['completed_checkpoints']]);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
  }
}
