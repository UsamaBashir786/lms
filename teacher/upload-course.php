<?php
session_start();
$teacher_id = $_SESSION['teacher_id'];
$conn = new mysqli("localhost", "root", "", "lms");
include_once '../db/db.php';

$teacherId = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT full_name FROM teachers WHERE id = ?");
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $video_title = $_POST['video_title'];

  // Handle video upload
  $video_upload_dir = "../student/uploads/videos/";
  $video_tmp_name = $_FILES['video']['tmp_name'];
  $video_name = basename($_FILES['video']['name']);
  $video_path = $video_upload_dir . $video_name;
  move_uploaded_file($video_tmp_name, $video_path);

  // Handle thumbnail upload
  $thumbnail_upload_dir = "../student/uploads/thumbnails/";
  $thumbnail_tmp_name = $_FILES['thumbnail']['tmp_name'];
  $thumbnail_name = basename($_FILES['thumbnail']['name']);
  $thumbnail_path = $thumbnail_upload_dir . $thumbnail_name;
  move_uploaded_file($thumbnail_tmp_name, $thumbnail_path);

  // Insert video
  $sql = "INSERT INTO videos (teacher_id, video_title, video_path, thumbnail_path) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("isss", $teacher_id, $video_title, $video_path, $thumbnail_path);
  $stmt->execute();
  $video_id = $stmt->insert_id;

  // Handle checkpoints and MCQs
  foreach ($_POST['checkpoints'] as $checkpoint) {
    // Convert minutes and seconds to total seconds
    $minutes = isset($checkpoint['minutes']) ? (int)$checkpoint['minutes'] : 0;
    $seconds = isset($checkpoint['seconds']) ? (int)$checkpoint['seconds'] : 0;
    $time_in_seconds = ($minutes * 60) + $seconds;

    $content = $checkpoint['content'];

    // Insert checkpoint
    $sql_checkpoint = "INSERT INTO checkpoints (video_id, time_in_seconds, content) VALUES (?, ?, ?)";
    $stmt_checkpoint = $conn->prepare($sql_checkpoint);
    $stmt_checkpoint->bind_param("iis", $video_id, $time_in_seconds, $content);
    $stmt_checkpoint->execute();
    $checkpoint_id = $stmt_checkpoint->insert_id;

    // Insert MCQs for the checkpoint
    foreach ($checkpoint['mcqs'] as $mcq) {
      $question = $mcq['question'];
      $option_a = $mcq['option_a'];
      $option_b = $mcq['option_b'];
      $option_c = $mcq['option_c'];
      $option_d = $mcq['option_d'];
      $correct_option = $mcq['correct_option'];

      $sql_mcq = "INSERT INTO mcqs (checkpoint_id, question, option_a, option_b, option_c, option_d, correct_option) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt_mcq = $conn->prepare($sql_mcq);
      $stmt_mcq->bind_param("issssss", $checkpoint_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
      $stmt_mcq->execute();
    }
  }
  echo "Video, checkpoints, and MCQs added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Video and Checkpoints</title>
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="assets/css/upload-course.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <?php include 'includes/sidebar.php' ?>


  <div class="main-content">
    <div class="main-header">
      <h1>Welcome, <?php echo htmlspecialchars($fullName); ?></h1>
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

    <div class="dashboard-section">
      <form action="upload-course.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="video_title" placeholder="Video Title" required>

        <h3 class="d-inline">&nbsp;Video</h3>
        <input type="file" name="video" accept="video/*" required>

        <h3 class="d-inline">&nbsp;Thumbnail</h3>
        <input type="file" name="thumbnail" accept="image/*" required>

        <h3>Checkpoints</h3>
        <div id="checkpointContainer">
          <div class="checkpoint" id="checkpoint-0">
            <div class="time-inputs">
              <input type="number" name="checkpoints[0][minutes]" placeholder="Minutes" min="0" class="time-input" required>
              <input type="number" name="checkpoints[0][seconds]" placeholder="Seconds" min="0" max="59" class="time-input" required>
            </div>
            <textarea name="checkpoints[0][content]" placeholder="Checkpoint Content"></textarea>
            <h4>MCQs</h4>
            <div class="mcqs-container" id="mcqs-container-0">
              <div class="mcq" id="mcq-0-0">
                <input type="text" name="checkpoints[0][mcqs][0][question]" placeholder="Question">
                <input type="text" name="checkpoints[0][mcqs][0][option_a]" placeholder="Option A">
                <input type="text" name="checkpoints[0][mcqs][0][option_b]" placeholder="Option B">
                <input type="text" name="checkpoints[0][mcqs][0][option_c]" placeholder="Option C">
                <input type="text" name="checkpoints[0][mcqs][0][option_d]" placeholder="Option D">
                <select name="checkpoints[0][mcqs][0][correct_option]">
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="C">C</option>
                  <option value="D">D</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <button type="button" id="addCheckpoint">Add Another Checkpoint</button>
        <button type="button" onclick="addMCQ(0)">Add MCQ</button>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>

  <script>
    let checkpointCounter = 1;
    let mcqCounter = [1];

    document.getElementById('addCheckpoint').addEventListener('click', function() {
      let checkpointHTML = `
                <div class="checkpoint" id="checkpoint-${checkpointCounter}">
                    <div class="time-inputs">
                        <input type="number" name="checkpoints[${checkpointCounter}][minutes]" placeholder="Minutes" min="0" class="time-input" required>
                        <input type="number" name="checkpoints[${checkpointCounter}][seconds]" placeholder="Seconds" min="0" max="59" class="time-input" required>
                    </div>
                    <textarea name="checkpoints[${checkpointCounter}][content]" placeholder="Checkpoint Content"></textarea>
                    <h4>MCQs</h4>
                    <div class="mcqs-container" id="mcqs-container-${checkpointCounter}">
                        <div class="mcq" id="mcq-${checkpointCounter}-0">
                            <input type="text" name="checkpoints[${checkpointCounter}][mcqs][0][question]" placeholder="Question">
                            <input type="text" name="checkpoints[${checkpointCounter}][mcqs][0][option_a]" placeholder="Option A">
                            <input type="text" name="checkpoints[${checkpointCounter}][mcqs][0][option_b]" placeholder="Option B">
                            <input type="text" name="checkpoints[${checkpointCounter}][mcqs][0][option_c]" placeholder="Option C">
                            <input type="text" name="checkpoints[${checkpointCounter}][mcqs][0][option_d]" placeholder="Option D">
                            <select name="checkpoints[${checkpointCounter}][mcqs][0][correct_option]">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                            <button type="button" onclick="addMCQ(${checkpointCounter})">Add MCQ</button>
                        </div>
                    </div>
                </div>
            `;
      document.getElementById('checkpointContainer').insertAdjacentHTML('beforeend', checkpointHTML);
      mcqCounter.push(1);
      checkpointCounter++;
    });

    function addMCQ(checkpointId) {
      let mcqId = mcqCounter[checkpointId]++;
      let mcqHTML = `
                <div class="mcq" id="mcq-${checkpointId}-${mcqId}">
                    <input type="text" name="checkpoints[${checkpointId}][mcqs][${mcqId}][question]" placeholder="Question">
                    <input type="text" name="checkpoints[${checkpointId}][mcqs][${mcqId}][option_a]" placeholder="Option A">
                    <input type="text" name="checkpoints[${checkpointId}][mcqs][${mcqId}][option_b]" placeholder="Option B">
                    <input type="text" name="checkpoints[${checkpointId}][mcqs][${mcqId}][option_c]" placeholder="Option C">
                    <input type="text" name="checkpoints[${checkpointId}][mcqs][${mcqId}][option_d]" placeholder="Option D">
                    <select name="checkpoints[${checkpointId}][mcqs][${mcqId}][correct_option]">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                    <button type="button" onclick="removeMCQ(${checkpointId}, ${mcqId})">Remove MCQ</button>
                </div>
            `;
      document.getElementById(`mcqs-container-${checkpointId}`).insertAdjacentHTML('beforeend', mcqHTML);
    }

    function removeMCQ(checkpointId, mcqId) {
      document.getElementById(`mcq-${checkpointId}-${mcqId}`).remove();
    }
  </script>
</body>

</html>