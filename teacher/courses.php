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

// Handle video deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'delete_video') {
    $videoId = (int)$_POST['video_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
      // Get video paths before deletion for file cleanup
      $pathStmt = $conn->prepare("SELECT video_path, thumbnail_path FROM videos WHERE video_id = ? AND teacher_id = ?");
      $pathStmt->bind_param("ii", $videoId, $teacherId);
      $pathStmt->execute();
      $result = $pathStmt->get_result();
      $videoData = $result->fetch_assoc();
      $pathStmt->close();

      if (!$videoData) {
        throw new Exception("Video not found or you don't have permission to delete it.");
      }

      // Get checkpoint IDs first
      $checkpointStmt = $conn->prepare("SELECT checkpoint_id FROM checkpoints WHERE video_id = ?");
      $checkpointStmt->bind_param("i", $videoId);
      $checkpointStmt->execute();
      $checkpointResult = $checkpointStmt->get_result();
      $checkpointIds = [];

      while ($row = $checkpointResult->fetch_assoc()) {
        $checkpointIds[] = $row['checkpoint_id'];
      }
      $checkpointStmt->close();

      // Delete MCQs for each checkpoint
      if (!empty($checkpointIds)) {
        $placeholders = str_repeat('?,', count($checkpointIds) - 1) . '?';
        $deleteMcqsStmt = $conn->prepare("DELETE FROM mcqs WHERE checkpoint_id IN ($placeholders)");
        $deleteMcqsStmt->bind_param(str_repeat('i', count($checkpointIds)), ...$checkpointIds);
        $deleteMcqsStmt->execute();
        $deleteMcqsStmt->close();
      }

      // Delete checkpoints
      $deleteCheckpointsStmt = $conn->prepare("DELETE FROM checkpoints WHERE video_id = ?");
      $deleteCheckpointsStmt->bind_param("i", $videoId);
      $deleteCheckpointsStmt->execute();
      $deleteCheckpointsStmt->close();

      // Delete video
      $deleteVideoStmt = $conn->prepare("DELETE FROM videos WHERE video_id = ? AND teacher_id = ?");
      $deleteVideoStmt->bind_param("ii", $videoId, $teacherId);
      $deleteVideoStmt->execute();
      $deleteVideoStmt->close();

      // Commit transaction
      $conn->commit();

      // Delete the actual files
      if (file_exists($videoData['video_path'])) {
        unlink($videoData['video_path']);
      }

      if (file_exists($videoData['thumbnail_path'])) {
        unlink($videoData['thumbnail_path']);
      }

      $message = "Video course and associated materials deleted successfully.";
      $messageType = "success";
    } catch (Exception $e) {
      // Rollback on error
      $conn->rollback();
      $message = "Error: " . $e->getMessage();
      $messageType = "error";
    }
  }
}

// Fetch videos with counts of checkpoints and MCQs
// Remove the reference to created_at column since it doesn't exist
$query = "SELECT v.*, 
          (SELECT COUNT(*) FROM checkpoints c WHERE c.video_id = v.video_id) AS checkpoint_count,
          (SELECT COUNT(*) FROM checkpoints c JOIN mcqs m ON c.checkpoint_id = m.checkpoint_id 
           WHERE c.video_id = v.video_id) AS mcq_count
          FROM videos v 
          WHERE v.teacher_id = ? 
          ORDER BY v.video_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$videos = [];

while ($row = $result->fetch_assoc()) {
  $videos[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Courses</title>
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .course-card {
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .course-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .course-thumbnail {
      height: 180px;
      overflow: hidden;
      position: relative;
    }

    .course-thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .course-info {
      padding: 15px;
    }

    .course-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #333;
    }

    .course-stats {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 14px;
      color: #666;
    }

    .course-stat {
      display: flex;
      align-items: center;
    }

    .course-stat i {
      margin-right: 5px;
      color: var(--primary-color);
    }

    .course-actions {
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .action-btn {
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      border: none;
      display: flex;
      align-items: center;
      transition: background-color 0.3s;
    }

    .action-btn i {
      margin-right: 5px;
    }

    .edit-btn {
      background-color: #f0f0f0;
      color: #333;
    }

    .edit-btn:hover {
      background-color: #e0e0e0;
    }

    .delete-btn {
      background-color: #fee;
      color: #d33;
    }

    .delete-btn:hover {
      background-color: #fdd;
    }

    .preview-btn {
      background-color: var(--primary-color);
      color: white;
    }

    .preview-btn:hover {
      background-color: #005522;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      background-color: #f9f9f9;
      border-radius: 8px;
      margin: 20px 0;
    }

    .empty-state i {
      font-size: 50px;
      color: #ddd;
      margin-bottom: 15px;
    }

    .empty-state p {
      margin-bottom: 20px;
      color: #777;
    }

    .create-course-btn {
      display: inline-flex;
      align-items: center;
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .create-course-btn:hover {
      background-color: #005522;
    }

    .create-course-btn i {
      margin-right: 8px;
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

    .course-date {
      font-size: 12px;
      color: #999;
      margin-top: 5px;
    }

    .filtering {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    .search-box {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .filter-options {
      margin-left: 15px;
    }

    .sort-by {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .confirm-delete-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      padding: 25px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
    }

    .modal-title {
      margin-top: 0;
      color: #d33;
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .modal-btn {
      padding: 8px 15px;
      margin-left: 10px;
      border-radius: 4px;
      cursor: pointer;
      border: none;
    }

    .cancel-btn {
      background-color: #f0f0f0;
    }

    .confirm-btn {
      background-color: #d33;
      color: white;
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main-content">
    <div class="main-header">
      <h1>Manage Courses</h1>
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

    <div class="controls">
      <div class="filtering">
        <input type="text" class="search-box" id="courseSearch" placeholder="Search courses...">
        <div class="filter-options">
          <select class="sort-by" id="sortCourses">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="title_asc">Title (A-Z)</option>
            <option value="title_desc">Title (Z-A)</option>
            <option value="checkpoints">Most Checkpoints</option>
          </select>
        </div>
      </div>
      <a href="upload-course.php" class="create-course-btn">
        <i class="fas fa-plus-circle"></i> Create New Course
      </a>
    </div>

    <?php if (empty($videos)): ?>
      <div class="empty-state">
        <i class="fas fa-video-slash"></i>
        <p>You haven't created any video courses yet.</p>
        <a href="upload-course.php" class="create-course-btn">
          <i class="fas fa-plus-circle"></i> Create Your First Course
        </a>
      </div>
    <?php else: ?>
      <div class="course-grid" id="courseGrid">
        <?php foreach ($videos as $video): ?>
          <div class="course-card" data-title="<?php echo htmlspecialchars(strtolower($video['video_title'])); ?>">
            <div class="course-thumbnail">
              <img src="<?php echo htmlspecialchars($video['thumbnail_path']); ?>" alt="<?php echo htmlspecialchars($video['video_title']); ?>">
            </div>
            <div class="course-info">
              <div class="course-title"><?php echo htmlspecialchars($video['video_title']); ?></div>
              <div class="course-stats">
                <div class="course-stat">
                  <i class="fas fa-flag"></i> <?php echo $video['checkpoint_count']; ?> checkpoints
                </div>
                <div class="course-stat">
                  <i class="fas fa-question-circle"></i> <?php echo $video['mcq_count']; ?> questions
                </div>
              </div>
              <!-- Removed date display since created_at column doesn't exist -->
            </div>
            <div class="course-actions">
              <a href="../student/video-player.php?video_id=<?php echo $video['video_id']; ?>" class="action-btn preview-btn" target="_blank">
                <i class="fas fa-play"></i> Preview
              </a>
              <a href="edit-course.php?video_id=<?php echo $video['video_id']; ?>" class="action-btn edit-btn">
                <i class="fas fa-edit"></i> Edit
              </a>
              <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $video['video_id']; ?>, '<?php echo htmlspecialchars($video['video_title'], ENT_QUOTES); ?>')">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="confirm-delete-modal" id="deleteModal">
    <div class="modal-content">
      <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Delete Course</h3>
      <p>Are you sure you want to delete "<span id="deleteCourseName"></span>"?</p>
      <p>This will permanently remove the video, thumbnail, checkpoints, and all associated MCQs. This action cannot be undone.</p>

      <form method="POST" id="deleteForm">
        <input type="hidden" name="action" value="delete_video">
        <input type="hidden" name="video_id" id="deleteVideoId">

        <div class="modal-actions">
          <button type="button" class="modal-btn cancel-btn" onclick="hideDeleteModal()">Cancel</button>
          <button type="submit" class="modal-btn confirm-btn">Delete Permanently</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Search and filter functionality
    const courseSearch = document.getElementById('courseSearch');
    const sortCourses = document.getElementById('sortCourses');
    const courseGrid = document.getElementById('courseGrid');
    const courseCards = document.querySelectorAll('.course-card');

    // Search courses
    courseSearch.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();

      courseCards.forEach(card => {
        const title = card.dataset.title;
        if (title.includes(searchTerm)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });

    // Sort courses - modified to work without created_at
    sortCourses.addEventListener('change', function() {
      const sortMethod = this.value;
      const cards = Array.from(courseCards);

      cards.sort((a, b) => {
        if (sortMethod === 'newest' || sortMethod === 'oldest') {
          // Sort by video_id instead since created_at doesn't exist
          const idA = parseInt(a.querySelector('.preview-btn').href.split('video_id=')[1]);
          const idB = parseInt(b.querySelector('.preview-btn').href.split('video_id=')[1]);
          return sortMethod === 'newest' ? idB - idA : idA - idB;
        } else if (sortMethod === 'title_asc') {
          return a.dataset.title.localeCompare(b.dataset.title);
        } else if (sortMethod === 'title_desc') {
          return b.dataset.title.localeCompare(a.dataset.title);
        } else if (sortMethod === 'checkpoints') {
          const checkpointsA = parseInt(a.querySelector('.course-stat').textContent);
          const checkpointsB = parseInt(b.querySelector('.course-stat').textContent);
          return checkpointsB - checkpointsA;
        }
      });

      // Re-append sorted cards
      cards.forEach(card => {
        courseGrid.appendChild(card);
      });
    });

    // Delete confirmation
    function confirmDelete(videoId, videoTitle) {
      document.getElementById('deleteCourseName').textContent = videoTitle;
      document.getElementById('deleteVideoId').value = videoId;
      document.getElementById('deleteModal').style.display = 'flex';
    }

    function hideDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target === modal) {
        hideDeleteModal();
      }
    });
  </script>
</body>

</html>