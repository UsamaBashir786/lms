<?php
session_start();
$student_id = $_SESSION['student_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all videos
$sql = "SELECT * FROM videos";
$result = $conn->query($sql);
$videos = array();

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
  }
} else {
  $videos = [];
}

// Fetch completed videos
$completed_videos = array();
$sql_completed = "SELECT video_id FROM completed_videos WHERE student_id = ?";
$stmt = $conn->prepare($sql_completed);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_completed = $stmt->get_result();

while ($row = $result_completed->fetch_assoc()) {
  $completed_videos[] = $row['video_id'];
}

// Default video_id or from GET parameter
$video_id = isset($_GET['video_id']) ? intval($_GET['video_id']) : (count($videos) > 0 ? $videos[0]['video_id'] : 1);

// Fetch ALL checkpoints for ALL videos to use in JavaScript
$all_checkpoints = [];
$sql_all_checkpoints = "SELECT * FROM checkpoints ORDER BY video_id, time_in_seconds ASC";
$result_all_checkpoints = $conn->query($sql_all_checkpoints);

if ($result_all_checkpoints->num_rows > 0) {
  while ($row = $result_all_checkpoints->fetch_assoc()) {
    $all_checkpoints[] = $row;
  }
}

// Fetch ALL MCQs - Modified to get all MCQs without grouping by checkpoint
$all_mcqs = [];
$sql_all_mcqs = "SELECT m.mcq_id, m.checkpoint_id, m.question, m.option_a, m.option_b, m.option_c, m.option_d, m.correct_option, c.video_id 
                FROM mcqs m 
                JOIN checkpoints c ON m.checkpoint_id = c.checkpoint_id";
$result_all_mcqs = $conn->query($sql_all_mcqs);

if ($result_all_mcqs->num_rows > 0) {
  while ($row = $result_all_mcqs->fetch_assoc()) {
    $all_mcqs[] = $row; // Store all MCQs in a flat array
  }
}

// Fetch completed checkpoints for the student
$completed_checkpoints = [];
$sql_completed_checkpoints = "SELECT checkpoint_id FROM completed_checkpoints WHERE student_id = ?";
$stmt = $conn->prepare($sql_completed_checkpoints);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_completed_checkpoints = $stmt->get_result();

while ($row = $result_completed_checkpoints->fetch_assoc()) {
  $completed_checkpoints[] = $row['checkpoint_id'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Video Playlist</title>
  <link rel="stylesheet" href="assets/css/student-quizes.css">
  <style>
    .mcq-container {
      display: none;
      margin-top: 20px;
    }

    .mcq {
      margin-bottom: 20px;
    }

    .video-item.locked {
      opacity: 0.7;
      cursor: not-allowed;
      position: relative;
    }

    .lock-icon {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 24px;
      color: #fff;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .video-item.locked:hover::after {
      content: "Complete previous video first";
      position: absolute;
      bottom: -30px;
      left: 50%;
      transform: translateX(-50%);
      background: #333;
      color: white;
      padding: 5px 10px;
      border-radius: 5px;
      font-size: 12px;
      white-space: nowrap;
      z-index: 1000;
    }

    .toast {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #333;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      z-index: 1000;
    }

    .video-item.active {
      border: 2px solid #4CAF50;
      background: rgba(76, 175, 80, 0.1);
    }

    .toast.show {
      opacity: 1;
    }

    /* Debug mode */
    .debug-info {
      background: #f0f0f0;
      padding: 10px;
      margin-top: 20px;
      border: 1px solid #ccc;
      display: none;
    }
  </style>
</head>

<body>
  <div class="header">
    <button class="go-back" onclick="window.location.href='student-portal.php';">Go Back</button>
    <h1>Learning Management System</h1>
  </div>

  <div class="player-container">
    <div class="video-player-container">
      <video id="video-player" controls>
        <source id="video-source" src="" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <div class="now-playing">
        <span id="current-video">No video playing</span>
      </div>
    </div>

    <div class="playlist-container">
      <div class="playlist-header">Course Playlist</div>
      <?php
      foreach ($videos as $index => $video) {
        $isLocked = $index > 0 && !in_array($videos[$index - 1]['video_id'], $completed_videos);
        $videoClass = $isLocked ? 'video-item locked' : 'video-item';
        $isActive = ($video['video_id'] == $video_id) ? ' active' : '';

        echo '
          <div id="video-item-' . $video['video_id'] . '" class="' . $videoClass . $isActive . '" ' . (!$isLocked ? 'onclick="playVideo(\'' . $video['video_path'] . '\', \'' . $video['video_title'] . '\', ' . $video['video_id'] . ')"' : '') . '>
            <img src="' . $video['thumbnail_path'] . '" alt="' . $video['video_title'] . '">
            <div class="title">' . $video['video_title'] . '</div>
            ' . ($isLocked ? '<div class="lock-icon">🔒</div>' : '') . '
          </div>';
      }
      ?>
    </div>
  </div>

  <div id="mcq-section" class="mcq-container">
    <h2>MCQs for Video Checkpoints</h2>
    <div id="mcq-content"></div>
    <button id="submit-quiz" style="display:none;">Submit Quiz</button>
  </div>

  <!-- Debug section - remove in production -->
  <div id="debug-info" class="debug-info">
    <h3>Debug Info</h3>
    <div id="debug-content"></div>
  </div>

  <script>
    // Pre-populate all video data once for easier access
    const allVideosData = <?= json_encode($videos) ?>;
    const completedVideosData = <?= json_encode(array_map('intval', $completed_videos)) ?>;
    const allCheckpointsData = <?= json_encode($all_checkpoints) ?>;
    const allMcqsData = <?= json_encode($all_mcqs) ?>; // This is now a flat array of all MCQs
    // Pre-populated completed checkpoints from database
    const completedCheckpointsData = <?= json_encode(array_map('intval', $completed_checkpoints)) ?>;

    // Element references
    const videoPlayer = document.getElementById("video-player");
    const videoSource = document.getElementById("video-source");
    const currentVideoDisplay = document.getElementById("current-video");
    const mcqSection = document.getElementById("mcq-section");
    const mcqContent = document.getElementById("mcq-content");
    const submitButton = document.getElementById("submit-quiz");
    const debugInfo = document.getElementById("debug-info");
    const debugContent = document.getElementById("debug-content");

    // Debugging function - can be removed in production
    function debug(message) {
      //debugInfo.style.display = "block";
      //debugContent.innerHTML += `<p>${message}</p>`;
      console.log(message);
    }

    // State variables
    let currentVideoId = <?= json_encode((int)$video_id) ?>;
    let currentCheckpoint = -1;
    // Initialize the completedCheckpoints object with data from the server
    let completedCheckpoints = {};
    completedCheckpointsData.forEach(checkpointId => {
      completedCheckpoints[checkpointId] = true;
    });
    let totalQuestions = 0;
    let correctAnswers = 0;
    let currentQuizQuestions = []; // To keep track of the current quiz questions being displayed

    // Initialize the player when the page loads
    window.onload = function() {
      debug("Window loaded. Videos: " + allVideosData.length);
      debug("Current video ID: " + currentVideoId);
      debug("Completed checkpoints: " + JSON.stringify(completedCheckpoints));

      // Find the current video in our data - log all video IDs for debugging
      debug("All video IDs: " + allVideosData.map(v => v.video_id).join(", "));
      debug("Looking for video ID: " + currentVideoId);
      
      let currentVideo = allVideosData.find(video => parseInt(video.video_id) === parseInt(currentVideoId));
      
      // Log whether we found the video
      if (currentVideo) {
        debug("Found matching video: " + currentVideo.video_title);
      } else {
        debug("No matching video found for ID: " + currentVideoId);
      }

      // If not found or invalid, use the first available video
      if (!currentVideo && allVideosData.length > 0) {
        debug("Current video not found, defaulting to first video");
        currentVideo = allVideosData[0];
        currentVideoId = parseInt(currentVideo.video_id);
      }

      if (currentVideo) {
        debug("Playing video: " + currentVideo.video_title);
        playVideo(currentVideo.video_path, currentVideo.video_title, currentVideo.video_id);
      } else {
        debug("No videos available to play");
        currentVideoDisplay.textContent = "No videos available";
      }
    };

    function playVideo(videoPath, videoTitle, videoId) {
      debug("Attempting to play: " + videoId + ", " + videoPath + ", " + videoTitle);

      if (!videoPath) {
        debug("Error: Video path is empty");
        showToast("Error: Video path is empty");
        return;
      }

      // Update URL
      const newUrl = new URL(window.location.href);
      newUrl.searchParams.set('video_id', videoId);
      window.history.pushState({}, '', newUrl);

      // Update current video ID (ensure it's an integer)
      currentVideoId = parseInt(videoId);

      // Update video source and title
      videoSource.src = videoPath;
      currentVideoDisplay.textContent = `Now Playing: ${videoTitle}`;
      
      // Add a debug message to check the video path
      debug(`Setting video source to: ${videoPath}`);
      
      // Clear any previous error state
      videoPlayer.error = null;
      
      mcqSection.style.display = "none";

      // Update active video in playlist
      document.querySelectorAll('.video-item').forEach(item => {
        item.classList.remove('active');
      });

      const activeItem = document.getElementById(`video-item-${videoId}`);
      if (activeItem) {
        activeItem.classList.add('active');
      }

      // Important! This loads the new video source
      try {
        videoPlayer.load();
        debug("Video load() called successfully");
      } catch (error) {
        debug("Error loading video: " + error.message);
        showToast("Error loading video. Please try again.");
      }

      // Add event listeners for video loading and errors
      videoPlayer.oncanplay = function() {
        debug("Video can play now");
        // Try to play the video, but handle autoplay restrictions gracefully
        videoPlayer.play()
          .then(() => {
            debug("Video playback started successfully");
          })
          .catch(error => {
            debug("Error playing video: " + error.message);
            // Don't show error toast here, just log it - user can press play manually
            console.log("Autoplay may be blocked by browser - user can press play manually");
          });
      };

      videoPlayer.onerror = function() {
        const errorCode = videoPlayer.error ? videoPlayer.error.code : "unknown";
        debug("Video error: " + errorCode);
        
        // More descriptive error message based on error code
        if (videoPlayer.error) {
          switch(videoPlayer.error.code) {
            case 1: // MEDIA_ERR_ABORTED
              showToast("Video playback aborted. Please try again.");
              break;
            case 2: // MEDIA_ERR_NETWORK
              showToast("Network error. Check your connection and try again.");
              break;
            case 3: // MEDIA_ERR_DECODE
              showToast("Video decoding error. The file may be corrupted.");
              break;
            case 4: // MEDIA_ERR_SRC_NOT_SUPPORTED
              showToast("Video format not supported. Try a different video.");
              break;
            default:
              showToast("Error loading video. Please check file path.");
          }
        } else {
          showToast("Error loading video. Please check file path.");
        }
      };
    }

    // Video checkpoint handling
    videoPlayer.addEventListener("timeupdate", () => {
      const currentTime = Math.floor(videoPlayer.currentTime);

      // Filter checkpoints for the current video only
      const videoCheckpoints = allCheckpointsData.filter(
        checkpoint => parseInt(checkpoint.video_id) === currentVideoId
      );

      videoCheckpoints.forEach(checkpoint => {
        const checkpointId = parseInt(checkpoint.checkpoint_id);
        if (currentTime >= parseInt(checkpoint.time_in_seconds) &&
          !completedCheckpoints[checkpointId]) {
          displayMCQs(checkpointId);
        }
      });
    });

    function displayMCQs(checkpointId) {
      debug("Displaying MCQs for checkpoint " + checkpointId);
      debug("Completed checkpoints: " + JSON.stringify(completedCheckpoints));

      if (completedCheckpoints[checkpointId]) {
        debug("Checkpoint already completed, skipping MCQs");
        return;
      }

      currentCheckpoint = checkpointId;
      mcqSection.style.display = "block";
      mcqContent.innerHTML = `<h3>MCQs for Checkpoint</h3>`;

      // Get all available MCQs (from all checkpoints)
      const allAvailableMcqs = [...allMcqsData]; // Create a copy to shuffle
      
      // Shuffle all MCQs to randomize selection
      shuffleArray(allAvailableMcqs);
      
      // Select the first 10 MCQs (or fewer if less than 10 are available)
      currentQuizQuestions = allAvailableMcqs.slice(0, 10);
      totalQuestions = currentQuizQuestions.length;
      
      // Display the selected MCQs
      currentQuizQuestions.forEach(mcq => {
        const mcqElement = document.createElement("div");
        mcqElement.classList.add("mcq");
        mcqElement.innerHTML = `
          <h4>${mcq.question}</h4>
          <label><input type="radio" name="mcq_${checkpointId}_q${mcq.mcq_id}" value="A"> ${mcq.option_a}</label><br>
          <label><input type="radio" name="mcq_${checkpointId}_q${mcq.mcq_id}" value="B"> ${mcq.option_b}</label><br>
          <label><input type="radio" name="mcq_${checkpointId}_q${mcq.mcq_id}" value="C"> ${mcq.option_c}</label><br>
          <label><input type="radio" name="mcq_${checkpointId}_q${mcq.mcq_id}" value="D"> ${mcq.option_d}</label>
        `;
        mcqContent.appendChild(mcqElement);
      });

      if (totalQuestions > 0) {
        submitButton.style.display = "block";
        videoPlayer.pause();
        videoPlayer.controls = false;

        window.scrollTo({
          top: mcqSection.offsetTop,
          behavior: 'smooth'
        });
      } else {
        mcqContent.innerHTML = "<p>No MCQs available for this checkpoint.</p>";
      }
    }

    function shuffleArray(array) {
      for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
      }
    }

    // Handle video completion
    videoPlayer.addEventListener('ended', function() {
      if (currentVideoId) {
        fetch('mark_video_complete.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              video_id: currentVideoId
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast('Video completed! Next video unlocked.');
              // Add to completed videos array
              if (!completedVideosData.includes(parseInt(currentVideoId))) {
                completedVideosData.push(parseInt(currentVideoId));
              }
              updatePlaylistUI();
            }
          })
          .catch(error => {
            debug("Error marking video complete: " + error);
            showToast("Error marking video as complete.");
          });
      }
    });

    function updatePlaylistUI() {
      allVideosData.forEach((video, index) => {
        const videoElement = document.getElementById(`video-item-${video.video_id}`);
        if (!videoElement) return;

        // Check if previous video is completed or if it's the first video
        const prevVideoId = index > 0 ? parseInt(allVideosData[index - 1].video_id) : null;
        const isLocked = prevVideoId !== null && !completedVideosData.includes(prevVideoId);

        if (isLocked) {
          videoElement.classList.add('locked');
          videoElement.removeAttribute('onclick');
          if (!videoElement.querySelector('.lock-icon')) {
            const lockIcon = document.createElement('div');
            lockIcon.className = 'lock-icon';
            lockIcon.textContent = '🔒';
            videoElement.appendChild(lockIcon);
          }
        } else {
          videoElement.classList.remove('locked');
          const lockIcon = videoElement.querySelector('.lock-icon');
          if (lockIcon) {
            videoElement.removeChild(lockIcon);
          }
          // Escape single quotes in video path and title to prevent JS errors
        const escapedPath = video.video_path.replace(/'/g, "\\'");
        const escapedTitle = video.video_title.replace(/'/g, "\\'");
        videoElement.setAttribute('onclick', `playVideo('${escapedPath}', '${escapedTitle}', ${video.video_id})`);
        }
      });
    }

    submitButton.addEventListener("click", () => {
      correctAnswers = 0;

      // Check all the currently displayed questions
      currentQuizQuestions.forEach(mcq => {
        const selectedRadio = document.querySelector(`input[name="mcq_${currentCheckpoint}_q${mcq.mcq_id}"]:checked`);
        if (selectedRadio) {
          const selectedAnswer = selectedRadio.value;
          const correctOption = mcq.correct_option;

          if (selectedAnswer === correctOption) {
            correctAnswers++;
          }
        }
      });

      const percentage = (correctAnswers / totalQuestions) * 100;
      const resultMessage = `You got ${correctAnswers} out of ${totalQuestions} correct!<br>Score: ${percentage.toFixed(2)}%`;
      mcqContent.innerHTML = `<h3>Quiz Completed</h3><p>${resultMessage}</p>`;
      submitButton.style.display = "none";

      if (percentage >= 50) {
        showToast('You passed the quiz! Video will resume.');

        // Modify this part in your submit button event listener
        fetch('mark_checkpoint_complete.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              checkpoint_id: currentCheckpoint,
              score: parseFloat(percentage.toFixed(2)),
              correct_answers: correctAnswers,
              total_questions: totalQuestions
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              debug("Checkpoint and quiz result saved to database: " + JSON.stringify(data));
            } else {
              debug("Error saving checkpoint and quiz result to database: " + JSON.stringify(data));
            }
          })
          .catch(error => {
            debug("Error: " + error);
          });

        completedCheckpoints[currentCheckpoint] = true;

        setTimeout(() => {
          videoPlayer.play();
          videoPlayer.controls = true;
        }, 2000);
      } else {
        showToast('You didn\'t pass. Try again!');
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      }
    });

    function showToast(message) {
      const toast = document.createElement('div');
      toast.classList.add('toast');
      toast.textContent = message;
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.classList.add('show');
      }, 100);

      setTimeout(() => {
        toast.classList.remove('show');
        document.body.removeChild(toast);
      }, 3000);
    }
  </script>
</body>

</html>