<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['teacher_id'])) {
  header('Location: teacher-portal-login.php');
  exit;
}

include_once '../db/db.php';

$teacherId = $_SESSION['teacher_id'];
$message = '';
$messageType = '';

// Fetch teacher name
$stmt = $conn->prepare("SELECT full_name FROM teachers WHERE id = ?");
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Check if video ID is provided
if (!isset($_GET['video_id'])) {
  header('Location: courses.php');
  exit;
}

$videoId = (int)$_GET['video_id'];

// Get video details
$videoStmt = $conn->prepare("SELECT * FROM videos WHERE video_id = ? AND teacher_id = ?");
$videoStmt->bind_param("ii", $videoId, $teacherId);
$videoStmt->execute();
$videoResult = $videoStmt->get_result();

if ($videoResult->num_rows === 0) {
  // Video not found or doesn't belong to this teacher
  header('Location: courses.php');
  exit;
}

$video = $videoResult->fetch_assoc();
$videoStmt->close();

// Get checkpoints
$checkpointStmt = $conn->prepare("SELECT * FROM checkpoints WHERE video_id = ? ORDER BY time_in_seconds ASC");
$checkpointStmt->bind_param("i", $videoId);
$checkpointStmt->execute();
$checkpointResult = $checkpointStmt->get_result();
$checkpoints = [];

while ($checkpoint = $checkpointResult->fetch_assoc()) {
  // Get MCQs for each checkpoint
  $mcqStmt = $conn->prepare("SELECT * FROM mcqs WHERE checkpoint_id = ?");
  $mcqStmt->bind_param("i", $checkpoint['checkpoint_id']);
  $mcqStmt->execute();
  $mcqResult = $mcqStmt->get_result();
  $mcqs = [];

  while ($mcq = $mcqResult->fetch_assoc()) {
    $mcqs[] = $mcq;
  }

  $checkpoint['mcqs'] = $mcqs;
  $checkpoints[] = $checkpoint;
  $mcqStmt->close();
}
$checkpointStmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $videoTitle = trim($_POST['video_title']);

  // Start transaction
  $conn->begin_transaction();

  try {
    // Update video title
    $updateVideoStmt = $conn->prepare("UPDATE videos SET video_title = ? WHERE video_id = ? AND teacher_id = ?");
    $updateVideoStmt->bind_param("sii", $videoTitle, $videoId, $teacherId);
    $updateVideoStmt->execute();
    $updateVideoStmt->close();

    // Handle thumbnail update if provided
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
      $thumbnail_upload_dir = "../student/uploads/thumbnails/";
      $thumbnail_tmp_name = $_FILES['thumbnail']['tmp_name'];
      $thumbnail_name = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
      $thumbnail_path = $thumbnail_upload_dir . $thumbnail_name;

      if (move_uploaded_file($thumbnail_tmp_name, $thumbnail_path)) {
        // Delete old thumbnail
        if (file_exists($video['thumbnail_path'])) {
          unlink($video['thumbnail_path']);
        }

        // Update thumbnail path in database
        $updateThumbStmt = $conn->prepare("UPDATE videos SET thumbnail_path = ? WHERE video_id = ?");
        $updateThumbStmt->bind_param("si", $thumbnail_path, $videoId);
        $updateThumbStmt->execute();
        $updateThumbStmt->close();
      }
    }

    // Process existing checkpoints
    if (isset($_POST['checkpoints'])) {
      foreach ($_POST['checkpoints'] as $checkpointId => $checkpointData) {
        $minutes = isset($checkpointData['minutes']) ? (int)$checkpointData['minutes'] : 0;
        $seconds = isset($checkpointData['seconds']) ? (int)$checkpointData['seconds'] : 0;
        $timeInSeconds = ($minutes * 60) + $seconds;
        $content = trim($checkpointData['content']);

        // Update checkpoint
        $updateCheckpointStmt = $conn->prepare("UPDATE checkpoints SET time_in_seconds = ?, content = ? WHERE checkpoint_id = ?");
        $updateCheckpointStmt->bind_param("isi", $timeInSeconds, $content, $checkpointId);
        $updateCheckpointStmt->execute();
        $updateCheckpointStmt->close();

        // Process MCQs for this checkpoint
        if (isset($checkpointData['mcqs'])) {
          foreach ($checkpointData['mcqs'] as $mcqId => $mcqData) {
            $question = trim($mcqData['question']);
            $optionA = trim($mcqData['option_a']);
            $optionB = trim($mcqData['option_b']);
            $optionC = trim($mcqData['option_c']);
            $optionD = trim($mcqData['option_d']);
            $correctOption = $mcqData['correct_option'];

            // Update MCQ
            $updateMcqStmt = $conn->prepare("UPDATE mcqs SET question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE mcq_id = ?");
            $updateMcqStmt->bind_param("ssssssi", $question, $optionA, $optionB, $optionC, $optionD, $correctOption, $mcqId);
            $updateMcqStmt->execute();
            $updateMcqStmt->close();
          }
        }

        // Handle new MCQs
        if (isset($checkpointData['new_mcqs'])) {
          foreach ($checkpointData['new_mcqs'] as $newMcq) {
            if (!empty($newMcq['question'])) {
              $question = trim($newMcq['question']);
              $optionA = trim($newMcq['option_a']);
              $optionB = trim($newMcq['option_b']);
              $optionC = trim($newMcq['option_c']);
              $optionD = trim($newMcq['option_d']);
              $correctOption = $newMcq['correct_option'];

              $insertMcqStmt = $conn->prepare("INSERT INTO mcqs (checkpoint_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
              $insertMcqStmt->bind_param("issssss", $checkpointId, $question, $optionA, $optionB, $optionC, $optionD, $correctOption);
              $insertMcqStmt->execute();
              $insertMcqStmt->close();
            }
          }
        }
      }
    }

    // Handle new checkpoints
    if (isset($_POST['new_checkpoints'])) {
      foreach ($_POST['new_checkpoints'] as $newCheckpoint) {
        if (!empty($newCheckpoint['minutes']) || !empty($newCheckpoint['seconds'])) {
          $minutes = isset($newCheckpoint['minutes']) ? (int)$newCheckpoint['minutes'] : 0;
          $seconds = isset($newCheckpoint['seconds']) ? (int)$newCheckpoint['seconds'] : 0;
          $timeInSeconds = ($minutes * 60) + $seconds;
          $content = trim($newCheckpoint['content']);

          $insertCheckpointStmt = $conn->prepare("INSERT INTO checkpoints (video_id, time_in_seconds, content) VALUES (?, ?, ?)");
          $insertCheckpointStmt->bind_param("iis", $videoId, $timeInSeconds, $content);
          $insertCheckpointStmt->execute();
          $newCheckpointId = $conn->insert_id;
          $insertCheckpointStmt->close();

          // Add MCQs for new checkpoint
          if (isset($newCheckpoint['mcqs'])) {
            foreach ($newCheckpoint['mcqs'] as $mcq) {
              if (!empty($mcq['question'])) {
                $question = trim($mcq['question']);
                $optionA = trim($mcq['option_a']);
                $optionB = trim($mcq['option_b']);
                $optionC = trim($mcq['option_c']);
                $optionD = trim($mcq['option_d']);
                $correctOption = $mcq['correct_option'];

                $insertMcqStmt = $conn->prepare("INSERT INTO mcqs (checkpoint_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertMcqStmt->bind_param("issssss", $newCheckpointId, $question, $optionA, $optionB, $optionC, $optionD, $correctOption);
                $insertMcqStmt->execute();
                $insertMcqStmt->close();
              }
            }
          }
        }
      }
    }

    // Delete MCQs if requested
    if (isset($_POST['delete_mcqs']) && !empty($_POST['delete_mcqs'])) {
      $deleteMcqIds = explode(',', $_POST['delete_mcqs']);
      foreach ($deleteMcqIds as $mcqId) {
        if (!empty($mcqId)) {
          $deleteMcqStmt = $conn->prepare("DELETE FROM mcqs WHERE mcq_id = ?");
          $deleteMcqStmt->bind_param("i", $mcqId);
          $deleteMcqStmt->execute();
          $deleteMcqStmt->close();
        }
      }
    }

    // Delete checkpoints if requested
    if (isset($_POST['delete_checkpoints']) && !empty($_POST['delete_checkpoints'])) {
      $deleteCheckpointIds = explode(',', $_POST['delete_checkpoints']);
      foreach ($deleteCheckpointIds as $checkpointId) {
        if (!empty($checkpointId)) {
          // First delete associated MCQs
          $deleteMcqsStmt = $conn->prepare("DELETE FROM mcqs WHERE checkpoint_id = ?");
          $deleteMcqsStmt->bind_param("i", $checkpointId);
          $deleteMcqsStmt->execute();
          $deleteMcqsStmt->close();

          // Then delete the checkpoint
          $deleteCheckpointStmt = $conn->prepare("DELETE FROM checkpoints WHERE checkpoint_id = ?");
          $deleteCheckpointStmt->bind_param("i", $checkpointId);
          $deleteCheckpointStmt->execute();
          $deleteCheckpointStmt->close();
        }
      }
    }

    // Commit transaction
    $conn->commit();

    $message = "Course updated successfully!";
    $messageType = "success";

    // Refresh video and checkpoint data
    header("Location: edit-course.php?video_id=$videoId&updated=1");
    exit;
  } catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $message = "Error: " . $e->getMessage();
    $messageType = "error";
  }
}

// Check for update message
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
  $message = "Course updated successfully!";
  $messageType = "success";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Course</title>
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .course-edit-container {
      background-color: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      flex: 1;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }

    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    .preview-thumbnail {
      width: 200px;
      height: 120px;
      object-fit: cover;
      border-radius: 4px;
      margin-top: 10px;
    }

    .checkpoint-container {
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      position: relative;
    }

    .checkpoint-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    .checkpoint-header h3 {
      margin: 0;
      font-size: 18px;
      color: #333;
    }

    .checkpoint-actions {
      display: flex;
      gap: 10px;
    }

    .delete-checkpoint {
      color: #d33;
      background-color: transparent;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      font-size: 14px;
    }

    .delete-checkpoint i {
      margin-right: 5px;
    }

    .time-inputs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .time-input {
      width: 80px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .mcq-container {
      border: 1px solid #eee;
      border-radius: 6px;
      padding: 15px;
      margin-bottom: 15px;
      position: relative;
    }

    .mcq-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .mcq-header h4 {
      margin: 0;
      font-size: 16px;
      color: #333;
    }

    .delete-mcq {
      color: #d33;
      background-color: transparent;
      border: none;
      cursor: pointer;
      font-size: 14px;
    }

    .option-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 10px;
    }

    .option-group {
      position: relative;
    }

    .option-group label {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
    }

    .option-group .radio-label {
      margin-right: 10px;
      font-weight: 500;
    }

    .add-btn {
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 8px 15px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      font-size: 14px;
    }

    .add-btn i {
      margin-right: 5px;
    }

    .add-btn:hover {
      background-color: #45a049;
    }

    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      border: none;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: #005522;
    }

    .btn-secondary {
      background-color: #f0f0f0;
      color: #333;
    }

    .btn-secondary:hover {
      background-color: #e0e0e0;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
    }

    .section-title {
      margin-top: 30px;
      margin-bottom: 20px;
      font-size: 20px;
      color: #333;
      border-bottom: 2px solid #eee;
      padding-bottom: 10px;
    }

    .action-btns {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main-content">
    <div class="main-header">
      <h1>Edit Course</h1>
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

    <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="course-edit-container">
      <form method="POST" enctype="multipart/form-data" id="editCourseForm">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Course Title</label>
            <input type="text" class="form-control" name="video_title" value="<?php echo htmlspecialchars($video['video_title']); ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Current Thumbnail</label>
            <div>
              <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" alt="Course Thumbnail" class="preview-thumbnail">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Change Thumbnail (optional)</label>
            <input type="file" class="form-control" name="thumbnail" accept="image/*">
            <p class="text-muted">Leave empty to keep the current thumbnail</p>
          </div>
        </div>

        <!-- Existing Checkpoints -->
        <h2 class="section-title">Checkpoints</h2>

        <div id="checkpoints-container">
          <?php foreach ($checkpoints as $index => $checkpoint): ?>
            <div class="checkpoint-container" data-checkpoint-id="<?php echo $checkpoint['checkpoint_id']; ?>">
              <div class="checkpoint-header">
                <h3>Checkpoint #<?php echo $index + 1; ?></h3>
                <div class="checkpoint-actions">
                  <button type="button" class="delete-checkpoint" onclick="deleteCheckpoint(<?php echo $checkpoint['checkpoint_id']; ?>)">
                    <i class="fas fa-trash"></i> Delete Checkpoint
                  </button>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Time (minutes:seconds)</label>
                  <div class="time-inputs">
                    <input type="number" class="time-input" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][minutes]" value="<?php echo floor($checkpoint['time_in_seconds'] / 60); ?>" min="0" placeholder="Min" required>
                    <input type="number" class="time-input" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][seconds]" value="<?php echo $checkpoint['time_in_seconds'] % 60; ?>" min="0" max="59" placeholder="Sec" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Content</label>
                  <textarea class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][content]" rows="2"><?php echo htmlspecialchars($checkpoint['content']); ?></textarea>
                </div>
              </div>

              <h4>MCQs</h4>
              <div class="mcqs-container" id="mcqs-container-<?php echo $checkpoint['checkpoint_id']; ?>">
                <?php foreach ($checkpoint['mcqs'] as $mcqIndex => $mcq): ?>
                  <div class="mcq-container" data-mcq-id="<?php echo $mcq['mcq_id']; ?>">
                    <div class="mcq-header">
                      <h4>Question #<?php echo $mcqIndex + 1; ?></h4>
                      <button type="button" class="delete-mcq" onclick="deleteMcq(<?php echo $mcq['mcq_id']; ?>)">
                        <i class="fas fa-times"></i> Remove
                      </button>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Question</label>
                      <input type="text" class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][question]" value="<?php echo htmlspecialchars($mcq['question']); ?>" required>
                    </div>

                    <div class="option-grid">
                      <div class="option-group">
                        <label>
                          <input type="radio" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][correct_option]" value="A" <?php echo $mcq['correct_option'] === 'A' ? 'checked' : ''; ?> required>
                          <span class="radio-label">Option A</span>
                        </label>
                        <input type="text" class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][option_a]" value="<?php echo htmlspecialchars($mcq['option_a']); ?>" required>
                      </div>

                      <div class="option-group">
                        <label>
                          <input type="radio" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][correct_option]" value="B" <?php echo $mcq['correct_option'] === 'B' ? 'checked' : ''; ?>>
                          <span class="radio-label">Option B</span>
                        </label>
                        <input type="text" class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][option_b]" value="<?php echo htmlspecialchars($mcq['option_b']); ?>" required>
                      </div>

                      <div class="option-group">
                        <label>
                          <input type="radio" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][correct_option]" value="C" <?php echo $mcq['correct_option'] === 'C' ? 'checked' : ''; ?>>
                          <span class="radio-label">Option C</span>
                        </label>
                        <input type="text" class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][option_c]" value="<?php echo htmlspecialchars($mcq['option_c']); ?>" required>
                      </div>

                      <div class="option-group">
                        <label>
                          <input type="radio" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][correct_option]" value="D" <?php echo $mcq['correct_option'] === 'D' ? 'checked' : ''; ?>>
                          <span class="radio-label">Option D</span>
                        </label>
                        <input type="text" class="form-control" name="checkpoints[<?php echo $checkpoint['checkpoint_id']; ?>][mcqs][<?php echo $mcq['mcq_id']; ?>][option_d]" value="<?php echo htmlspecialchars($mcq['option_d']); ?>" required>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <button type="button" class="add-btn" onclick="addMcq(<?php echo $checkpoint['checkpoint_id']; ?>)">
                <i class="fas fa-plus"></i> Add MCQ
              </button>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="new-checkpoints-container"></div>

        <button type="button" class="add-btn" onclick="addCheckpoint()">
          <i class="fas fa-plus"></i> Add New Checkpoint
        </button>

        <input type="hidden" name="delete_mcqs" id="delete-mcqs-input" value="">
        <input type="hidden" name="delete_checkpoints" id="delete-checkpoints-input" value="">

        <div class="action-btns">
          <a href="courses.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Track elements to delete
    let mcqsToDelete = [];
    let checkpointsToDelete = [];
    let newMcqCounter = {};
    let newCheckpointCounter = 0;

    // Delete MCQ
    function deleteMcq(mcqId) {
      if (confirm('Are you sure you want to delete this MCQ?')) {
        const mcqElement = document.querySelector(`.mcq-container[data-mcq-id="${mcqId}"]`);
        if (mcqElement) {
          mcqElement.remove();
          mcqsToDelete.push(mcqId);
          document.getElementById('delete-mcqs-input').value = mcqsToDelete.join(',');
        }
      }
    }

    // Delete checkpoint
    function deleteCheckpoint(checkpointId) {
      if (confirm('Are you sure you want to delete this checkpoint and all its MCQs?')) {
        const checkpointElement = document.querySelector(`.checkpoint-container[data-checkpoint-id="${checkpointId}"]`);
        if (checkpointElement) {
          checkpointElement.remove();
          checkpointsToDelete.push(checkpointId);
          document.getElementById('delete-checkpoints-input').value = checkpointsToDelete.join(',');
        }
      }
    }

    // Add new MCQ to existing checkpoint
    function addMcq(checkpointId) {
      if (!newMcqCounter[checkpointId]) {
        newMcqCounter[checkpointId] = 0;
      }

      const mcqIndex = newMcqCounter[checkpointId]++;
      const mcqsContainer = document.getElementById(`mcqs-container-${checkpointId}`);

      const mcqHtml = `
        <div class="mcq-container">
          <div class="mcq-header">
            <h4>New Question</h4>
            <button type="button" class="delete-mcq" onclick="this.parentElement.parentElement.remove()">
              <i class="fas fa-times"></i> Remove
            </button>
          </div>
          
          <div class="form-group">
            <label class="form-label">Question</label>
            <input type="text" class="form-control" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][question]" required>
          </div>
          
          <div class="option-grid">
            <div class="option-group">
              <label>
                <input type="radio" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][correct_option]" value="A" checked required>
                <span class="radio-label">Option A</span>
              </label>
              <input type="text" class="form-control" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][option_a]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][correct_option]" value="B">
                <span class="radio-label">Option B</span>
              </label>
              <input type="text" class="form-control" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][option_b]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][correct_option]" value="C">
                <span class="radio-label">Option C</span>
              </label>
              <input type="text" class="form-control" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][option_c]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][correct_option]" value="D">
                <span class="radio-label">Option D</span>
              </label>
              <input type="text" class="form-control" name="checkpoints[${checkpointId}][new_mcqs][${mcqIndex}][option_d]" required>
            </div>
          </div>
        </div>
      `;

      mcqsContainer.insertAdjacentHTML('beforeend', mcqHtml);
    }

    // Add new checkpoint
    function addCheckpoint() {
      const checkpointIndex = newCheckpointCounter++;
      const containerHtml = `
        <div class="checkpoint-container">
          <div class="checkpoint-header">
            <h3>New Checkpoint</h3>
            <div class="checkpoint-actions">
              <button type="button" class="delete-checkpoint" onclick="this.closest('.checkpoint-container').remove()">
                <i class="fas fa-trash"></i> Delete Checkpoint
              </button>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Time (minutes:seconds)</label>
              <div class="time-inputs">
                <input type="number" class="time-input" name="new_checkpoints[${checkpointIndex}][minutes]" min="0" placeholder="Min" required>
                <input type="number" class="time-input" name="new_checkpoints[${checkpointIndex}][seconds]" min="0" max="59" placeholder="Sec" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Content</label>
              <textarea class="form-control" name="new_checkpoints[${checkpointIndex}][content]" rows="2"></textarea>
            </div>
          </div>
          
          <h4>MCQs</h4>
          <div class="mcqs-container" id="new-mcqs-container-${checkpointIndex}">
            <div class="mcq-container">
              <div class="mcq-header">
                <h4>New Question</h4>
                <button type="button" class="delete-mcq" onclick="this.parentElement.parentElement.remove()">
                  <i class="fas fa-times"></i> Remove
                </button>
              </div>
              
              <div class="form-group">
                <label class="form-label">Question</label>
                <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][0][question]" required>
              </div>
              
              <div class="option-grid">
                <div class="option-group">
                  <label>
                    <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][0][correct_option]" value="A" checked required>
                    <span class="radio-label">Option A</span>
                  </label>
                  <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][0][option_a]" required>
                </div>
                
                <div class="option-group">
                  <label>
                    <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][0][correct_option]" value="B">
                    <span class="radio-label">Option B</span>
                  </label>
                  <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][0][option_b]" required>
                </div>
                
                <div class="option-group">
                  <label>
                    <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][0][correct_option]" value="C">
                    <span class="radio-label">Option C</span>
                  </label>
                  <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][0][option_c]" required>
                </div>
                
                <div class="option-group">
                  <label>
                    <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][0][correct_option]" value="D">
                    <span class="radio-label">Option D</span>
                  </label>
                  <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][0][option_d]" required>
                </div>
              </div>
            </div>
          </div>
          
          <button type="button" class="add-btn" onclick="addNewCheckpointMcq(${checkpointIndex})">
            <i class="fas fa-plus"></i> Add MCQ
          </button>
        </div>
      `;

      document.getElementById('new-checkpoints-container').insertAdjacentHTML('beforeend', containerHtml);
    }

    // Add MCQ to new checkpoint
    function addNewCheckpointMcq(checkpointIndex) {
      const mcqsContainer = document.getElementById(`new-mcqs-container-${checkpointIndex}`);
      const mcqCount = mcqsContainer.querySelectorAll('.mcq-container').length;

      const mcqHtml = `
        <div class="mcq-container">
          <div class="mcq-header">
            <h4>New Question</h4>
            <button type="button" class="delete-mcq" onclick="this.parentElement.parentElement.remove()">
              <i class="fas fa-times"></i> Remove
            </button>
          </div>
          
          <div class="form-group">
            <label class="form-label">Question</label>
            <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][question]" required>
          </div>
          
          <div class="option-grid">
            <div class="option-group">
              <label>
                <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][correct_option]" value="A" checked required>
                <span class="radio-label">Option A</span>
              </label>
              <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][option_a]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][correct_option]" value="B">
                <span class="radio-label">Option B</span>
              </label>
              <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][option_b]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][correct_option]" value="C">
                <span class="radio-label">Option C</span>
              </label>
              <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][option_c]" required>
            </div>
            
            <div class="option-group">
              <label>
                <input type="radio" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][correct_option]" value="D">
                <span class="radio-label">Option D</span>
              </label>
              <input type="text" class="form-control" name="new_checkpoints[${checkpointIndex}][mcqs][${mcqCount}][option_d]" required>
            </div>
          </div>
        </div>
      `;

      mcqsContainer.insertAdjacentHTML('beforeend', mcqHtml);
    }

    // Form validation
    document.getElementById('editCourseForm').addEventListener('submit', function(e) {
      const title = document.querySelector('input[name="video_title"]').value.trim();

      if (!title) {
        e.preventDefault();
        alert('Please enter a course title.');
        return;
      }

      // Continue with form submission
      return true;
    });
  </script>
</body>

</html>