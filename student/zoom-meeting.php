<?php
session_start();

// Check if the user is logged in as a teacher
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

// Get room ID from URL
$room_id = isset($_GET['room']) ? trim($_GET['room']) : '';

if (empty($room_id)) {
  header('Location: index.php');
  exit;
}

// Check if meeting exists and belongs to this teacher
$stmt = $conn->prepare("SELECT * FROM meetings WHERE room_id = ? AND teacher_id = ?");
$stmt->bind_param("si", $room_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  // Meeting not found or doesn't belong to this teacher
  header('Location: index.php');
  exit;
}

$meeting = $result->fetch_assoc();
$stmt->close();

// Update meeting status if needed
if ($meeting['status'] === 'scheduled') {
  $updateStmt = $conn->prepare("UPDATE meetings SET status = 'in_progress' WHERE meeting_id = ?");
  $updateStmt->bind_param("i", $meeting['meeting_id']);
  $updateStmt->execute();
  $updateStmt->close();
}

// Process meeting end request
if (isset($_POST['end_meeting'])) {
  $updateStmt = $conn->prepare("UPDATE meetings SET status = 'completed' WHERE meeting_id = ?");
  $updateStmt->bind_param("i", $meeting['meeting_id']);

  if ($updateStmt->execute()) {
    header('Location: index.php?meeting_ended=1');
    exit;
  }
  $updateStmt->close();
}

// Get participants for this meeting
$participantsStmt = $conn->prepare("SELECT mp.*, s.full_name 
                                 FROM meeting_participants mp 
                                 JOIN students s ON mp.student_id = s.id 
                                 WHERE mp.meeting_id = ? 
                                 ORDER BY mp.join_time DESC");
$participantsStmt->bind_param("i", $meeting['meeting_id']);
$participantsStmt->execute();
$participantsResult = $participantsStmt->get_result();
$participants = [];

while ($row = $participantsResult->fetch_assoc()) {
  $participants[] = $row;
}
$participantsStmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Class Room: <?php echo htmlspecialchars($meeting['title']); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://meet.jit.si/external_api.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f4f6f9;
      color: #333;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background-color: #006633;
      color: white;
      padding: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-left {
      display: flex;
      align-items: center;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      margin-right: 2rem;
    }

    .meeting-info {
      display: flex;
      flex-direction: column;
    }

    .meeting-title {
      font-size: 1.2rem;
      margin-bottom: 0.25rem;
    }

    .meeting-time {
      font-size: 0.9rem;
      opacity: 0.8;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .end-meeting-btn {
      background-color: #e74c3c;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .end-meeting-btn:hover {
      background-color: #c0392b;
    }

    .teacher-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }

    main {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

    .meeting-container {
      flex: 1;
      height: 100%;
      position: relative;
    }

    #jitsiContainer {
      width: 100%;
      height: 100%;
    }

    .sidebar {
      width: 300px;
      background-color: white;
      border-left: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease;
    }

    .sidebar.collapsed {
      transform: translateX(280px);
    }

    .sidebar-header {
      padding: 1rem;
      background-color: #f1f1f1;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .sidebar-header h3 {
      font-size: 1rem;
      color: #333;
    }

    .toggle-sidebar {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      color: #666;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 30px;
      height: 30px;
    }

    .participants-list {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
    }

    .participant {
      padding: 0.75rem;
      border-radius: 4px;
      margin-bottom: 0.5rem;
      background-color: #f9f9f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .participant-info {
      display: flex;
      flex-direction: column;
    }

    .participant-name {
      font-weight: bold;
      margin-bottom: 0.25rem;
    }

    .join-time {
      font-size: 0.8rem;
      color: #666;
    }

    .participant-actions button {
      background: none;
      border: none;
      cursor: pointer;
      color: #666;
      font-size: 1rem;
    }

    .participant-actions button:hover {
      color: #e74c3c;
    }

    .empty-participants {
      padding: 2rem;
      text-align: center;
      color: #666;
    }

    .empty-participants i {
      font-size: 3rem;
      color: #ddd;
      margin-bottom: 1rem;
    }

    /* Class controls */
    .class-controls {
      position: absolute;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 0.5rem;
      z-index: 10;
    }

    .control-button {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: #333;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: none;
      font-size: 1.2rem;
    }

    .control-button:hover {
      background-color: #555;
    }

    .control-button.red {
      background-color: #e74c3c;
    }

    .control-button.red:hover {
      background-color: #c0392b;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-left {
        flex-direction: column;
        align-items: flex-start;
      }

      .logo {
        margin-right: 0;
        margin-bottom: 0.5rem;
      }

      .sidebar {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        z-index: 100;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
      }

      .sidebar.collapsed {
        transform: translateX(100%);
      }
    }
  </style>
</head>

<body>
  <header>
    <div class="header-left">
      <div class="logo">
        <i class="fas fa-graduation-cap"></i> LMS
      </div>
      <div class="meeting-info">
        <div class="meeting-title"><?php echo htmlspecialchars($meeting['title']); ?></div>
        <div class="meeting-time">
          Started at <?php echo date('g:i A', strtotime($meeting['scheduled_datetime'])); ?> |
          Duration: <?php echo $meeting['duration']; ?> minutes
        </div>
      </div>
    </div>
    <div class="header-actions">
      <div class="teacher-info">
        <i class="fas fa-user-tie"></i>
        <?php echo htmlspecialchars($fullName); ?>
      </div>
      <form method="POST" onsubmit="return confirm('Are you sure you want to end this class for all participants?');">
        <button type="submit" name="end_meeting" class="end-meeting-btn">
          <i class="fas fa-stop-circle"></i> End Class
        </button>
      </form>
    </div>
  </header>

  <main>
    <div class="meeting-container">
      <div id="jitsiContainer"></div>

      <div class="class-controls">
        <button type="button" id="toggle-mic" class="control-button">
          <i class="fas fa-microphone"></i>
        </button>
        <button type="button" id="toggle-video" class="control-button">
          <i class="fas fa-video"></i>
        </button>
        <button type="button" id="toggle-screen" class="control-button">
          <i class="fas fa-desktop"></i>
        </button>
        <button type="button" id="toggle-chat" class="control-button">
          <i class="fas fa-comment-alt"></i>
        </button>
        <button type="button" id="toggle-participants" class="control-button">
          <i class="fas fa-users"></i>
        </button>
        <button type="button" id="end-meeting-control" class="control-button red">
          <i class="fas fa-phone-slash"></i>
        </button>
      </div>
    </div>

    <div class="sidebar">
      <div class="sidebar-header">
        <h3>Participants (<?php echo count($participants); ?>)</h3>
        <button type="button" class="toggle-sidebar">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
      <div class="participants-list">
        <?php if (empty($participants)): ?>
          <div class="empty-participants">
            <i class="fas fa-users"></i>
            <p>No students have joined yet.</p>
            <p>Share the class link with your students to invite them.</p>
          </div>
        <?php else: ?>
          <?php foreach ($participants as $participant): ?>
            <div class="participant">
              <div class="participant-info">
                <div class="participant-name"><?php echo htmlspecialchars($participant['full_name']); ?></div>
                <div class="join-time">Joined <?php echo date('g:i A', strtotime($participant['join_time'])); ?></div>
              </div>
              <div class="participant-actions">
                <button type="button" data-student-id="<?php echo $participant['student_id']; ?>" title="Remove participant">
                  <i class="fas fa-times-circle"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Jitsi Meet
      const domain = 'meet.jit.si';
      const options = {
        roomName: '<?php echo $room_id; ?>',
        width: '100%',
        height: '100%',
        parentNode: document.getElementById('jitsiContainer'),
        userInfo: {
          displayName: '<?php echo htmlspecialchars($fullName); ?> (Teacher)'
        },
        configOverwrite: {
          startWithAudioMuted: false,
          startWithVideoMuted: false,
          disableDeepLinking: true,
          prejoinPageEnabled: false
        },
        interfaceConfigOverwrite: {
          TOOLBAR_BUTTONS: [],
          DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
          SHOW_JITSI_WATERMARK: false,
          SHOW_WATERMARK_FOR_GUESTS: false,
          DISABLE_FOCUS_INDICATOR: true
        }
      };

      // Initialize the Jitsi Meet API
      const api = new JitsiMeetExternalAPI(domain, options);

      // Handle sidebar toggle
      const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
      const sidebar = document.querySelector('.sidebar');

      toggleSidebarBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        const icon = this.querySelector('i');

        if (sidebar.classList.contains('collapsed')) {
          icon.classList.remove('fa-chevron-right');
          icon.classList.add('fa-chevron-left');
        } else {
          icon.classList.remove('fa-chevron-left');
          icon.classList.add('fa-chevron-right');
        }
      });

      // Add event listeners for meeting controls
      document.getElementById('toggle-mic').addEventListener('click', function() {
        api.executeCommand('toggleAudio');
        updateButtonIcon(this, 'fa-microphone', 'fa-microphone-slash');
      });

      document.getElementById('toggle-video').addEventListener('click', function() {
        api.executeCommand('toggleVideo');
        updateButtonIcon(this, 'fa-video', 'fa-video-slash');
      });

      document.getElementById('toggle-screen').addEventListener('click', function() {
        api.executeCommand('toggleShareScreen');
      });

      document.getElementById('toggle-chat').addEventListener('click', function() {
        api.executeCommand('toggleChat');
      });

      document.getElementById('toggle-participants').addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
      });

      document.getElementById('end-meeting-control').addEventListener('click', function() {
        if (confirm('Are you sure you want to end this class for all participants?')) {
          const form = document.createElement('form');
          form.method = 'POST';
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'end_meeting';
          input.value = '1';
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      });

      // Helper function to update button icons
      function updateButtonIcon(button, originalIcon, newIcon) {
        const iconElement = button.querySelector('i');
        if (iconElement.classList.contains(originalIcon)) {
          iconElement.classList.remove(originalIcon);
          iconElement.classList.add(newIcon);
        } else {
          iconElement.classList.remove(newIcon);
          iconElement.classList.add(originalIcon);
        }
      }

      // Add participant removal functionality
      document.querySelectorAll('.participant-actions button').forEach(button => {
        button.addEventListener('click', function() {
          const studentId = this.dataset.studentId;
          const studentName = this.closest('.participant').querySelector('.participant-name').textContent;

          if (confirm(`Remove ${studentName} from the class?`)) {
            // You could implement an AJAX call here to remove the participant
            fetch('remove-participant.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `student_id=${studentId}&meeting_id=<?php echo $meeting['meeting_id']; ?>`
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  this.closest('.participant').remove();
                  const participantCount = document.querySelectorAll('.participant').length;
                  document.querySelector('.sidebar-header h3').textContent = `Participants (${participantCount})`;
                } else {
                  alert('Failed to remove participant: ' + data.message);
                }
              })
              .catch(error => {
                console.error('Error:', error);
              });
          }
        });
      });

      // Auto-refresh participants list every 30 seconds
      setInterval(function() {
        fetch(`get-participants.php?meeting_id=<?php echo $meeting['meeting_id']; ?>`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const participantsList = document.querySelector('.participants-list');

              if (data.participants.length === 0) {
                participantsList.innerHTML = `
                <div class="empty-participants">
                  <i class="fas fa-users"></i>
                  <p>No students have joined yet.</p>
                  <p>Share the class link with your students to invite them.</p>
                </div>
              `;
              } else {
                let participantsHTML = '';

                data.participants.forEach(participant => {
                  participantsHTML += `
                  <div class="participant">
                    <div class="participant-info">
                      <div class="participant-name">${participant.full_name}</div>
                      <div class="join-time">Joined ${new Date(participant.join_time).toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'})}</div>
                    </div>
                    <div class="participant-actions">
                      <button type="button" data-student-id="${participant.student_id}" title="Remove participant">
                        <i class="fas fa-times-circle"></i>
                      </button>
                    </div>
                  </div>
                `;
                });

                participantsList.innerHTML = participantsHTML;

                // Re-add event listeners to the new buttons
                document.querySelectorAll('.participant-actions button').forEach(button => {
                  button.addEventListener('click', function() {
                    const studentId = this.dataset.studentId;
                    const studentName = this.closest('.participant').querySelector('.participant-name').textContent;

                    if (confirm(`Remove ${studentName} from the class?`)) {
                      // You could implement an AJAX call here to remove the participant
                      fetch('remove-participant.php', {
                          method: 'POST',
                          headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                          },
                          body: `student_id=${studentId}&meeting_id=<?php echo $meeting['meeting_id']; ?>`
                        })
                        .then(response => response.json())
                        .then(data => {
                          if (data.success) {
                            this.closest('.participant').remove();
                            const participantCount = document.querySelectorAll('.participant').length;
                            document.querySelector('.sidebar-header h3').textContent = `Participants (${participantCount})`;
                          } else {
                            alert('Failed to remove participant: ' + data.message);
                          }
                        })
                        .catch(error => {
                          console.error('Error:', error);
                        });
                    }
                  });
                });
              }

              // Update participant count
              document.querySelector('.sidebar-header h3').textContent = `Participants (${data.participants.length})`;
            }
          })
          .catch(error => {
            console.error('Error refreshing participants:', error);
          });
      }, 30000);

      // Handle window close/refresh to end meeting properly
      window.addEventListener('beforeunload', function(e) {
        const confirmationMessage = 'Leaving this page will disconnect you from the class. Are you sure?';
        e.returnValue = confirmationMessage;
        return confirmationMessage;
      });
    });
  </script>
</body>

</html>