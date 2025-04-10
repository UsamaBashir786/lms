<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
if (!$stmt) {
  die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$fullName = $student['full_name'];
$profileImage = $student['profile_image'] ?? 'default-profile.jpg';
$stmt->close();

// Get assignment ID from URL if provided
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
  // Log form submission for debugging
  error_log("Form submission detected!");

  $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
  $submission_text = isset($_POST['submission_text']) ? $conn->real_escape_string($_POST['submission_text']) : '';
  $file_path = null;

  // Validate assignment ID
  if ($assignment_id <= 0) {
    $error = "Invalid assignment ID.";
  } else {
    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
      $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'zip'];
      $max_file_size = 10 * 1024 * 1024; // 10MB

      $file_name = $_FILES['submission_file']['name'];
      $file_size = $_FILES['submission_file']['size'];
      $file_tmp = $_FILES['submission_file']['tmp_name'];
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

      // Validate file extension
      if (!in_array($file_ext, $allowed_extensions)) {
        $error = "File extension not allowed. Please upload a file with one of these extensions: " . implode(', ', $allowed_extensions);
      }

      // Validate file size
      if ($file_size > $max_file_size) {
        $error = "File size exceeds maximum limit (10MB).";
      }

      if (!isset($error)) {
        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/assignments/submissions/';
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $new_file_name = uniqid() . '_' . $file_name;
        $file_path = $upload_dir . $new_file_name;

        // Move uploaded file
        if (!move_uploaded_file($file_tmp, $file_path)) {
          $error = "Failed to upload file. Please try again.";
        }
      }
    }

    if (!isset($error)) {
      // Check if student has already submitted for this assignment
      $check_stmt = $conn->prepare("SELECT submission_id FROM assignment_submissions WHERE student_id = ? AND assignment_id = ?");
      if (!$check_stmt) {
        $error = "Database error: " . $conn->error;
      } else {
        $check_stmt->bind_param("ii", $student_id, $assignment_id);
        if (!$check_stmt->execute()) {
          $error = "Database error: " . $check_stmt->error;
        } else {
          $check_result = $check_stmt->get_result();

          if ($check_result->num_rows > 0) {
            // Update existing submission
            $submission = $check_result->fetch_assoc();
            $submission_id = $submission['submission_id'];

            $update_stmt = $conn->prepare("UPDATE assignment_submissions SET submission_text = ?, file_path = ?, submitted_at = NOW() WHERE submission_id = ?");
            if (!$update_stmt) {
              $error = "Database error: " . $conn->error;
            } else {
              $update_stmt->bind_param("ssi", $submission_text, $file_path, $submission_id);
              if ($update_stmt->execute()) {
                $success = "Your assignment has been updated successfully!";
              } else {
                $error = "Failed to update submission: " . $update_stmt->error;
              }
              $update_stmt->close();
            }
          } else {
            // Insert new submission
            $insert_stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, file_path, submitted_at) VALUES (?, ?, ?, ?, NOW())");
            if (!$insert_stmt) {
              $error = "Database error: " . $conn->error;
            } else {
              $insert_stmt->bind_param("iiss", $assignment_id, $student_id, $submission_text, $file_path);
              if ($insert_stmt->execute()) {
                $success = "Your assignment has been submitted successfully!";
              } else {
                $error = "Failed to submit assignment: " . $insert_stmt->error;
              }
              $insert_stmt->close();
            }
          }
          $check_stmt->close();
        }
      }
    }
  }
}

// Fetch all assignments
$assignments_stmt = $conn->prepare("
    SELECT a.*, t.full_name AS teacher_name,
    (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = ?) AS has_submitted,
    (SELECT grade FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = ?) AS grade,
    (SELECT feedback FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = ?) AS feedback,
    (SELECT submitted_at FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = ?) AS submitted_at
    FROM assignments a
    JOIN teachers t ON a.teacher_id = t.id
    ORDER BY a.due_date ASC
");
if (!$assignments_stmt) {
  die("Database error: " . $conn->error);
}
$assignments_stmt->bind_param("iiii", $student_id, $student_id, $student_id, $student_id);
$assignments_stmt->execute();
$assignments_result = $assignments_stmt->get_result();
$assignments = [];

while ($row = $assignments_result->fetch_assoc()) {
  $assignments[] = $row;
}
$assignments_stmt->close();

// Fetch specific assignment details if ID is provided
$assignment = null;
$submission = null;

if ($assignment_id > 0) {
  $assignment_stmt = $conn->prepare("
        SELECT a.*, t.full_name AS teacher_name
        FROM assignments a
        JOIN teachers t ON a.teacher_id = t.id
        WHERE a.assignment_id = ?
    ");
  if (!$assignment_stmt) {
    die("Database error: " . $conn->error);
  }
  $assignment_stmt->bind_param("i", $assignment_id);
  $assignment_stmt->execute();
  $assignment_result = $assignment_stmt->get_result();

  if ($assignment_result->num_rows > 0) {
    $assignment = $assignment_result->fetch_assoc();

    // Check if student has already submitted
    $submission_stmt = $conn->prepare("
            SELECT * FROM assignment_submissions
            WHERE assignment_id = ? AND student_id = ?
        ");
    if (!$submission_stmt) {
      die("Database error: " . $conn->error);
    }
    $submission_stmt->bind_param("ii", $assignment_id, $student_id);
    $submission_stmt->execute();
    $submission_result = $submission_stmt->get_result();

    if ($submission_result->num_rows > 0) {
      $submission = $submission_result->fetch_assoc();
    }
    $submission_stmt->close();
  } else {
    $error = "Assignment not found.";
  }
  $assignment_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Assignments</title>
  <?php include 'includes/css-links.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Assignment specific styles */
    .main-content {
      padding: 20px;
    }

    .assignments-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .assignment-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .assignment-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .assignment-header {
      padding: 15px;
      background-color: var(--primary-color);
      color: white;
    }

    .assignment-body {
      padding: 15px;
      flex-grow: 1;
    }

    .assignment-footer {
      padding: 15px;
      border-top: 1px solid #eee;
      background-color: #f8f9fa;
    }

    .badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: normal;
    }

    .badge-upcoming {
      background-color: #3498db;
      color: white;
    }

    .badge-due-soon {
      background-color: #f39c12;
      color: white;
    }

    .badge-overdue {
      background-color: #e74c3c;
      color: white;
    }

    .badge-submitted {
      background-color: #2ecc71;
      color: white;
    }

    .badge-graded {
      background-color: #9b59b6;
      color: white;
    }

    .assignment-detail {
      padding: 25px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .assignment-detail h3 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #eee;
    }

    .submission-form {
      margin-top: 20px;
      padding: 20px;
      background-color: #f8f9fa;
      border-radius: 10px;
    }

    .feedback-section {
      margin-top: 20px;
      padding: 20px;
      background-color: #f0f8ff;
      border-radius: 10px;
      border-left: 4px solid #3498db;
    }

    .grade-display {
      display: inline-block;
      padding: 5px 15px;
      background-color: var(--primary-color);
      color: white;
      border-radius: 5px;
      font-weight: bold;
      margin-right: 10px;
    }

    .deadline-indicator {
      font-size: 14px;
      padding: 5px 10px;
      border-radius: 5px;
      margin-bottom: 15px;
      display: inline-block;
    }

    .upcoming {
      background-color: #d4edda;
      color: #155724;
    }

    .due-soon {
      background-color: #fff3cd;
      color: #856404;
    }

    .overdue {
      background-color: #f8d7da;
      color: #721c24;
    }

    .file-link {
      display: inline-block;
      padding: 8px 15px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border: 1px solid #ddd;
      text-decoration: none;
      color: #333;
      margin-top: 10px;
      transition: background-color 0.3s ease;
    }

    .file-link:hover {
      background-color: #e9ecef;
    }

    .back-button {
      margin-bottom: 20px;
      display: inline-block;
      padding: 8px 15px;
      background-color: #f8f9fa;
      border-radius: 5px;
      border: 1px solid #ddd;
      text-decoration: none;
      color: #333;
      transition: background-color 0.3s ease;
    }

    .back-button:hover {
      background-color: #e9ecef;
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
      <h1>Assignments</h1>

      <!-- User dropdown -->
      <div class="user-dropdown">
        <button class="text-white">
          <img src="<?php echo $profileImage; ?>" alt="Profile Image">
          <?php echo htmlspecialchars($fullName); ?>
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
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success" role="alert">
        <?php echo $success; ?>
      </div>
    <?php endif; ?>

    <?php if ($assignment_id > 0 && $assignment): ?>
      <!-- Detailed Assignment View -->
      <a href="assignment.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to All Assignments
      </a>

      <div class="assignment-detail">
        <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>

        <?php
        $due_date = new DateTime($assignment['due_date']);
        $now = new DateTime();
        $interval = $now->diff($due_date);
        $days_diff = $interval->format('%R%a');

        if ($submission) {
          echo '<span class="badge badge-submitted">Submitted</span>';
          if ($submission['grade'] !== null) {
            echo ' <span class="badge badge-graded">Graded</span>';
          }
        } elseif ($days_diff < 0) {
          echo '<span class="deadline-indicator overdue">Overdue by ' . abs($days_diff) . ' days</span>';
        } elseif ($days_diff <= 2) {
          echo '<span class="deadline-indicator due-soon">Due soon (' . $days_diff . ' days left)</span>';
        } else {
          echo '<span class="deadline-indicator upcoming">Due in ' . $days_diff . ' days</span>';
        }
        ?>

        <div class="row mt-4">
          <div class="col-md-8">
            <p><strong>Description:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>

            <?php if ($assignment['file_path']): ?>
              <p><strong>Assignment File:</strong></p>
              <a href="<?php echo $assignment['file_path']; ?>" class="file-link" target="_blank">
                <i class="fas fa-download"></i> Download Assignment File
              </a>
            <?php endif; ?>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <p><strong>Due Date:</strong><br>
                  <?php echo date('F j, Y, g:i a', strtotime($assignment['due_date'])); ?></p>

                <p><strong>Total Points:</strong><br>
                  <?php echo $assignment['total_points']; ?></p>

                <p><strong>Teacher:</strong><br>
                  <?php echo htmlspecialchars($assignment['teacher_name']); ?></p>

                <?php if ($submission): ?>
                  <p><strong>Submitted:</strong><br>
                    <?php echo date('F j, Y, g:i a', strtotime($submission['submitted_at'])); ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <?php if ($submission && $submission['grade'] !== null): ?>
          <!-- Grade and Feedback Section -->
          <div class="feedback-section">
            <div class="row">
              <div class="col-md-6">
                <h4>Your Grade</h4>
                <div class="grade-display"><?php echo $submission['grade']; ?> / <?php echo $assignment['total_points']; ?></div>
                <div class="progress mt-2" style="height: 20px;">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($submission['grade'] / $assignment['total_points']) * 100; ?>%;"
                    aria-valuenow="<?php echo $submission['grade']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $assignment['total_points']; ?>">
                    <?php echo round(($submission['grade'] / $assignment['total_points']) * 100); ?>%
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h4>Teacher Feedback</h4>
                <p><?php echo $submission['feedback'] ? nl2br(htmlspecialchars($submission['feedback'])) : 'No feedback provided yet.'; ?></p>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Submission Form -->
        <div class="submission-form">
          <h4><?php echo $submission ? 'Update Your Submission' : 'Submit Your Assignment'; ?></h4>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">

            <div class="mb-3">
              <label for="submission_text" class="form-label">Your Answer</label>
              <textarea class="form-control" id="submission_text" name="submission_text" rows="6"><?php echo $submission ? htmlspecialchars($submission['submission_text']) : ''; ?></textarea>
            </div>

            <div class="mb-3">
              <label for="submission_file" class="form-label">Upload File (Optional)</label>
              <input class="form-control" type="file" id="submission_file" name="submission_file">
              <small class="text-muted">Accepted file formats: PDF, DOC, DOCX, TXT, PNG, JPG, JPEG, ZIP (Max 10MB)</small>
            </div>

            <?php if ($submission && $submission['file_path']): ?>
              <div class="mb-3">
                <p>Current uploaded file:
                  <a href="<?php echo $submission['file_path']; ?>" target="_blank">
                    <?php echo basename($submission['file_path']); ?>
                  </a>
                </p>
                <small class="text-muted">Upload a new file to replace the current one.</small>
              </div>
            <?php endif; ?>

            <button type="submit" name="submit_assignment" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> <?php echo $submission ? 'Update Submission' : 'Submit Assignment'; ?>
            </button>
          </form>
        </div>
      </div>

    <?php else: ?>
      <!-- Assignments List View -->
      <div class="assignments-container">
        <?php if (count($assignments) > 0): ?>
          <?php foreach ($assignments as $assignment): ?>
            <?php
            $due_date = new DateTime($assignment['due_date']);
            $now = new DateTime();
            $interval = $now->diff($due_date);
            $days_diff = $interval->format('%R%a');

            // Determine status badge
            $status_badge = '';
            $status_class = '';

            if ($assignment['has_submitted'] > 0) {
              if ($assignment['grade'] !== null) {
                $status_badge = 'Graded';
                $status_class = 'badge-graded';
              } else {
                $status_badge = 'Submitted';
                $status_class = 'badge-submitted';
              }
            } elseif ($days_diff < 0) {
              $status_badge = 'Overdue';
              $status_class = 'badge-overdue';
            } elseif ($days_diff <= 2) {
              $status_badge = 'Due Soon';
              $status_class = 'badge-due-soon';
            } else {
              $status_badge = 'Upcoming';
              $status_class = 'badge-upcoming';
            }
            ?>
            <div class="assignment-card">
              <div class="assignment-header">
                <h5 class="mb-0"><?php echo htmlspecialchars($assignment['title']); ?></h5>
                <small><?php echo htmlspecialchars($assignment['teacher_name']); ?></small>
              </div>

              <div class="assignment-body">
                <span class="badge <?php echo $status_class; ?>"><?php echo $status_badge; ?></span>

                <div class="mt-3">
                  <p><i class="fas fa-calendar-alt"></i> Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></p>
                  <p><i class="fas fa-star"></i> Points: <?php echo $assignment['total_points']; ?></p>

                  <?php if ($assignment['has_submitted'] > 0): ?>
                    <p><i class="fas fa-clock"></i> Submitted: <?php echo date('M d, Y', strtotime($assignment['submitted_at'])); ?></p>

                    <?php if ($assignment['grade'] !== null): ?>
                      <p><i class="fas fa-check-circle"></i> Grade: <strong><?php echo $assignment['grade']; ?> / <?php echo $assignment['total_points']; ?></strong></p>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>

                <div class="mt-3">
                  <p><?php echo substr(htmlspecialchars($assignment['description']), 0, 100) . (strlen($assignment['description']) > 100 ? '...' : ''); ?></p>
                </div>
              </div>

              <div class="assignment-footer">
                <a href="assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn btn-primary">
                  <?php if ($assignment['has_submitted'] > 0): ?>
                    <i class="fas fa-eye"></i> View Submission
                  <?php else: ?>
                    <i class="fas fa-pen"></i> Submit Assignment
                  <?php endif; ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              <p>No assignments available at this time. Check back later!</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    // Auto-hide alerts after 5 seconds
    $(document).ready(function() {
      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
    });
  </script>
</body>

</html>