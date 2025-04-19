<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['teacher_id'])) {
  header('Location: teacher-portal-login.php');
  exit;
}

// Include database connection
include_once '../db/db.php';

// Get teacher information
$teacher_id = $_SESSION['teacher_id'];
$stmt = $conn->prepare("SELECT id, full_name, email FROM teachers WHERE id = ?");
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$stmt->bind_result($teacherId, $fullName, $email);
$stmt->fetch();
$stmt->close();

// Process form submission for creating a new meeting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'create_meeting') {
    $title = trim($_POST['meeting_title']);
    $description = trim($_POST['meeting_description']);
    $scheduled_date = $_POST['meeting_date'];
    $scheduled_time = $_POST['meeting_time'];
    $duration = (int)$_POST['meeting_duration'];

    // Combine date and time for database storage
    $scheduled_datetime = $scheduled_date . ' ' . $scheduled_time;

    // Generate a unique meeting room ID (alphanumeric)
    $roomId = uniqid('class_') . '_' . $teacher_id;

    // Store meeting in the database
    $stmt = $conn->prepare("INSERT INTO meetings (teacher_id, title, description, room_id, scheduled_datetime, duration, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'scheduled')");
    $stmt->bind_param('issssi', $teacher_id, $title, $description, $roomId, $scheduled_datetime, $duration);

    if ($stmt->execute()) {
      $meeting_id = $conn->insert_id;
      $success_message = "Meeting created successfully!";

      // Optionally add code to notify students or add meeting invitations
    } else {
      $error_message = "Error creating meeting: " . $conn->error;
    }
    $stmt->close();
  } elseif ($_POST['action'] === 'delete_meeting') {
    $meeting_id = (int)$_POST['meeting_id'];

    // Make sure this meeting belongs to the current teacher
    $stmt = $conn->prepare("DELETE FROM meetings WHERE meeting_id = ? AND teacher_id = ?");
    $stmt->bind_param('ii', $meeting_id, $teacher_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
      $success_message = "Meeting deleted successfully!";
    } else {
      $error_message = "Error deleting meeting: " . $conn->error;
    }
    $stmt->close();
  } elseif ($_POST['action'] === 'start_meeting') {
    $meeting_id = (int)$_POST['meeting_id'];

    // Update meeting status
    $stmt = $conn->prepare("UPDATE meetings SET status = 'in_progress' WHERE meeting_id = ? AND teacher_id = ?");
    $stmt->bind_param('ii', $meeting_id, $teacher_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
      // Redirect to the meeting room
      $roomStmt = $conn->prepare("SELECT room_id FROM meetings WHERE meeting_id = ?");
      $roomStmt->bind_param('i', $meeting_id);
      $roomStmt->execute();
      $roomStmt->bind_result($roomId);
      $roomStmt->fetch();
      $roomStmt->close();

      header("Location: meeting-room.php?room=" . $roomId);
      exit;
    } else {
      $error_message = "Error starting meeting: " . $conn->error;
    }
    $stmt->close();
  }
}

// Fetch all meetings for this teacher
$stmt = $conn->prepare("SELECT m.*, 
                        (SELECT COUNT(*) FROM meeting_participants mp WHERE mp.meeting_id = m.meeting_id) as participant_count 
                       FROM meetings m 
                       WHERE m.teacher_id = ? 
                       ORDER BY m.scheduled_datetime DESC");
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$meetings = [];

while ($row = $result->fetch_assoc()) {
  $meetings[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Online Classes</title>
  <link rel="stylesheet" href="assets/css/teacher-portals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Meeting specific styles */
    .meetings-container {
      margin-top: 20px;
    }

    .meeting-card {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .meeting-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
    }

    .meeting-title {
      font-size: 1.3rem;
      margin: 0;
      color: var(--primary-color);
    }

    .meeting-status {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      color: white;
    }

    .status-scheduled {
      background-color: #3498db;
    }

    .status-in-progress {
      background-color: #2ecc71;
    }

    .status-completed {
      background-color: #7f8c8d;
    }

    .meeting-info {
      margin-bottom: 15px;
    }

    .meeting-info p {
      margin: 5px 0;
      color: #555;
    }

    .meeting-info i {
      width: 20px;
      text-align: center;
      margin-right: 10px;
      color: var(--primary-color);
    }

    .meeting-actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
      text-align: center;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.3s, transform 0.3s;
    }

    .btn i {
      margin-right: 8px;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: #005522;
      transform: translateY(-2px);
    }

    .btn-danger {
      background-color: #e74c3c;
      color: white;
      border: none;
    }

    .btn-danger:hover {
      background-color: #c0392b;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background-color: #7f8c8d;
      color: white;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #6c7a7a;
      transform: translateY(-2px);
    }

    .create-meeting-button {
      padding: 10px 20px;
      font-size: 1rem;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: background-color 0.3s;
    }

    .create-meeting-button:hover {
      background-color: #005522;
    }

    /* Meeting form */
    .meeting-form-container {
      max-width: 700px;
      margin: 0 auto;
      background-color: white;
      border-radius: 8px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .meeting-form-header {
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }

    .meeting-form-header h2 {
      margin: 0;
      color: var(--primary-color);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }

    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 102, 51, 0.1);
    }

    .form-row {
      display: flex;
      gap: 20px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .form-footer {
      text-align: right;
      margin-top: 30px;
    }

    .form-text {
      font-size: 0.9rem;
      color: #6c757d;
      margin-top: 5px;
    }

    /* Dashboard tab navigation */
    .dashboard-tabs {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 1px solid #ddd;
    }

    .dashboard-tab {
      padding: 10px 20px;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      color: #555;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .dashboard-tab.active {
      border-bottom-color: var(--primary-color);
      color: var(--primary-color);
    }

    .dashboard-tab:hover {
      background-color: #f8f9fa;
    }

    /* Tab content */
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
    }

    .empty-state i {
      font-size: 3rem;
      color: #ddd;
      margin-bottom: 15px;
    }

    .empty-state p {
      color: #777;
      margin-bottom: 20px;
    }

    /* Alert messages */
    .alert {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    /* Copy link button */
    .copy-link {
      display: flex;
      align-items: center;
      margin-top: 10px;
    }

    .copy-link input {
      flex: 1;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 5px 0 0 5px;
      font-size: 0.9rem;
      background-color: #f8f9fa;
    }

    .copy-link button {
      padding: 8px 15px;
      background-color: #6c757d;
      color: white;
      border: none;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .copy-link button:hover {
      background-color: #5a6268;
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>

  <div class="main-content">
    <div class="main-header">
      <h1>Online Classes</h1>
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

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success">
        <?php echo $success_message; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <div class="dashboard-tabs">
      <div class="dashboard-tab active" data-tab="meetings">My Classes</div>
      <div class="dashboard-tab" data-tab="create">Schedule New Class</div>
    </div>

    <div id="meetings-tab" class="tab-content active">
      <div class="meetings-container">
        <?php if (empty($meetings)): ?>
          <div class="empty-state">
            <i class="fas fa-video-slash"></i>
            <p>You haven't scheduled any online classes yet.</p>
            <button class="create-meeting-button" id="create-meeting-btn">
              <i class="fas fa-plus-circle"></i> Schedule Your First Class
            </button>
          </div>
        <?php else: ?>
          <?php foreach ($meetings as $meeting): ?>
            <div class="meeting-card">
              <div class="meeting-header">
                <h3 class="meeting-title"><?php echo htmlspecialchars($meeting['title']); ?></h3>
                <span class="meeting-status status-<?php echo $meeting['status']; ?>">
                  <?php
                  if ($meeting['status'] === 'scheduled') {
                    echo 'Scheduled';
                  } elseif ($meeting['status'] === 'in_progress') {
                    echo 'In Progress';
                  } else {
                    echo 'Completed';
                  }
                  ?>
                </span>
              </div>

              <div class="meeting-info">
                <p><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y', strtotime($meeting['scheduled_datetime'])); ?></p>
                <p><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($meeting['scheduled_datetime'])); ?></p>
                <p><i class="fas fa-hourglass-half"></i> <?php echo $meeting['duration']; ?> minutes</p>
                <p><i class="fas fa-users"></i> <?php echo $meeting['participant_count']; ?> participants</p>
                <?php if ($meeting['description']): ?>
                  <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($meeting['description']); ?></p>
                <?php endif; ?>

                <div class="copy-link">
                  <input type="text" readonly value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/student/join-meeting.php?room=" . $meeting['room_id']; ?>">
                  <button type="button" onclick="copyMeetingLink(this)"><i class="fas fa-copy"></i> Copy Link</button>
                </div>
              </div>

              <div class="meeting-actions">
                <?php if ($meeting['status'] === 'scheduled'): ?>
                  <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="start_meeting">
                    <input type="hidden" name="meeting_id" value="<?php echo $meeting['meeting_id']; ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-video"></i> Start Class</button>
                  </form>
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this class?');">
                    <input type="hidden" name="action" value="delete_meeting">
                    <input type="hidden" name="meeting_id" value="<?php echo $meeting['meeting_id']; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                <?php elseif ($meeting['status'] === 'in_progress'): ?>
                  <a href="meeting-room.php?room=<?php echo $meeting['room_id']; ?>" class="btn btn-primary"><i class="fas fa-video"></i> Join Class</a>
                  <button type="button" class="btn btn-secondary" onclick="endMeeting(<?php echo $meeting['meeting_id']; ?>)"><i class="fas fa-stop-circle"></i> End Class</button>
                <?php else: ?>
                  <button type="button" class="btn btn-secondary" disabled><i class="fas fa-check-circle"></i> Completed</button>
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this class?');">
                    <input type="hidden" name="action" value="delete_meeting">
                    <input type="hidden" name="meeting_id" value="<?php echo $meeting['meeting_id']; ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div id="create-tab" class="tab-content">
      <div class="meeting-form-container">
        <div class="meeting-form-header">
          <h2>Schedule a New Online Class</h2>
        </div>

        <form method="POST" id="createMeetingForm">
          <input type="hidden" name="action" value="create_meeting">

          <div class="form-group">
            <label for="meeting_title">Class Title</label>
            <input type="text" class="form-control" id="meeting_title" name="meeting_title" required>
          </div>

          <div class="form-group">
            <label for="meeting_description">Description (Optional)</label>
            <textarea class="form-control" id="meeting_description" name="meeting_description" rows="3"></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="meeting_date">Date</label>
              <input type="date" class="form-control" id="meeting_date" name="meeting_date" required>
            </div>

            <div class="form-group">
              <label for="meeting_time">Time</label>
              <input type="time" class="form-control" id="meeting_time" name="meeting_time" required>
            </div>
          </div>

          <div class="form-group">
            <label for="meeting_duration">Duration (minutes)</label>
            <input type="number" class="form-control" id="meeting_duration" name="meeting_duration" min="5" max="240" value="60" required>
            <div class="form-text">We recommend 30-60 minutes for effective online classes.</div>
          </div>

          <div class="form-footer">
            <button type="button" class="btn btn-secondary" id="cancel-create">Cancel</button>
            <button type="submit" class="btn btn-primary">Schedule Class</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Tab navigation
    document.querySelectorAll('.dashboard-tab').forEach(tab => {
      tab.addEventListener('click', function() {
        // Remove active class from all tabs
        document.querySelectorAll('.dashboard-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));

        // Add active class to current tab
        this.classList.add('active');
        document.getElementById(this.dataset.tab + '-tab').classList.add('active');
      });
    });

    // Create meeting button in empty state
    document.getElementById('create-meeting-btn')?.addEventListener('click', function() {
      document.querySelector('.dashboard-tab[data-tab="create"]').click();
    });

    // Cancel create form
    document.getElementById('cancel-create').addEventListener('click', function() {
      document.querySelector('.dashboard-tab[data-tab="meetings"]').click();
    });

    // Set minimum date for scheduling to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('meeting_date').min = today;

    // Copy meeting link function
    function copyMeetingLink(button) {
      const input = button.previousElementSibling;
      input.select();
      document.execCommand('copy');

      const originalText = button.innerHTML;
      button.innerHTML = '<i class="fas fa-check"></i> Copied!';

      setTimeout(() => {
        button.innerHTML = originalText;
      }, 2000);
    }

    // End meeting function
    function endMeeting(meetingId) {
      if (confirm('Are you sure you want to end this class?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'end_meeting';

        const meetingInput = document.createElement('input');
        meetingInput.type = 'hidden';
        meetingInput.name = 'meeting_id';
        meetingInput.value = meetingId;

        form.appendChild(actionInput);
        form.appendChild(meetingInput);
        document.body.appendChild(form);
        form.submit();
      }
    }

    // Set default meeting date to today
    document.getElementById('meeting_date').valueAsDate = new Date();
  </script>
</body>

</html>