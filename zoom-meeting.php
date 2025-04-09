<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Virtual Meeting</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="assets/css/zoom-meetings.css">
</head>

<body>
  <!-- Header -->
  <div class="header">
    <h1>Virtual Meeting</h1>
    <div class="header-controls">
      <button id="endMeetingBtn">End Meeting</button>
      <button id="participantsBtn">Participants</button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Sidebar -->
    <div class="sidebar">
      <h3>Meeting Controls</h3>
      <ul>
        <li id="muteBtn"><i class="fas fa-microphone"></i> Mute</li>
        <li id="videoBtn"><i class="fas fa-video"></i> Stop Video</li>
        <li><i class="fas fa-share-alt"></i> Share Screen</li>
        <li><i class="fas fa-user-plus"></i> Add Participant</li>
        <li><i class="fas fa-cogs"></i> Settings</li>
      </ul>
    </div>

    <!-- Video Section -->
    <div class="video-section">
      <!-- Video Card for Participant 1 -->
      <div class="video-card" id="videoCard1">
        <video autoplay muted id="video1"></video>
        <div class="video-controls">
          <i id="micIcon1" class="fas fa-microphone-slash" onclick="toggleMic(1)"></i>
          <i id="videoIcon1" class="fas fa-video-slash" onclick="toggleVideo(1)"></i>
        </div>
      </div>
      <!-- Video Card for Participant 2 -->
      <div class="video-card" id="videoCard2">
        <video autoplay id="video2"></video>
        <div class="video-controls">
          <i id="micIcon2" class="fas fa-microphone" onclick="toggleMic(2)"></i>
          <i id="videoIcon2" class="fas fa-video" onclick="toggleVideo(2)"></i>
        </div>
      </div>
      <!-- Add more video cards as needed -->
    </div>
  </div>

  <!-- Control Panel (Fixed at Bottom) -->
  <div class="controls-panel">
    <button id="muteAllBtn"><i class="fas fa-microphone-slash"></i></button>
    <button id="videoAllBtn"><i class="fas fa-video-slash"></i></button>
    <button id="shareScreenBtn"><i class="fas fa-share-alt"></i></button>
    <button id="endMeetingPanelBtn"><i class="fas fa-phone-slash"></i></button>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>© 2025 Virtual Meeting, All Rights Reserved.</p>
  </div>
  <script src="assets/js/zoom.js"></script>
</body>

</html>