<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lms");

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all videos for the playlist
$videos = [];
$result = $conn->query("SELECT * FROM videos");

// Check if any videos were returned from the database
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
  }
}

// Ensure that the $videos array has data
if (count($videos) > 0) {
  // Use the first video if no video is selected
  $selected_video_id = isset($_GET['video_id']) ? $_GET['video_id'] : $videos[0]['video_id'];

  $checkpoints = [];
  $mcqs = [];

  if ($selected_video_id) {
    // Fetch checkpoints for the selected video
    $checkpoints_query = "SELECT * FROM checkpoints WHERE video_id = $selected_video_id";
    $result = $conn->query($checkpoints_query);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $checkpoints[] = $row;

        // Fetch MCQs for each checkpoint
        $checkpoint_id = $row['checkpoint_id'];
        if ($checkpoint_id) {
          $mcq_query = "SELECT * FROM mcqs WHERE checkpoint_id = $checkpoint_id";
          $mcq_result = $conn->query($mcq_query);

          if ($mcq_result->num_rows > 0) {
            while ($mcq = $mcq_result->fetch_assoc()) {
              $mcqs[$checkpoint_id][] = $mcq;
            }
          }
        }
      }
    }
  }
} else {
  echo "No videos available.";
  exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $userAnswers = $_POST['answers'] ?? [];

  // Initialize variables
  $correctAnswers = 0;
  $totalQuestions = 0;

  // Calculate score based on correct answers fetched from database
  foreach ($userAnswers as $mcqId => $selectedAnswer) {
    // Fetch correct answer from database
    $mcqId = (int)$mcqId;
    $query = "SELECT correct_option FROM mcqs WHERE mcq_id = $mcqId"; // Assuming 'mcq_id' is the correct column name
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $correctOption = $row['correct_option'];

      // Check if user's answer matches the correct option
      if ($selectedAnswer === $correctOption) {
        $correctAnswers++;
      }
      $totalQuestions++;
    }
  }

  // Calculate score percentage
  $scorePercentage = ($totalQuestions > 0) ? ($correctAnswers / $totalQuestions) * 100 : 0;

  // Display score (you can modify this to save or display as per your requirement)
  echo "<h2>Your Score: $correctAnswers / $totalQuestions</h2>";
  echo "<p>Percentage: $scorePercentage%</p>";

  // Example of saving the score in a database table (you can adjust this as needed)
  $userId = $_SESSION['user_id'] ?? 0; // Assuming user is logged in and you have a user_id
  $insertScoreQuery = "INSERT INTO user_scores (user_id, video_id, score, total_questions) VALUES ($userId, $selected_video_id, $correctAnswers, $totalQuestions)";
  $conn->query($insertScoreQuery);

  exit(); // Exit to prevent further HTML output after processing
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Video Player</title>
  <style>
    /* Your CSS styles */
    /* Styling as before */
    :root {
      --primary-color: green;
      --secondary-color: goldenrod;
      --text-color: #333;
      --bg-color: #f4f7f6;
      --sidebar-bg: #fff;
      --sidebar-hover: goldenrod;
      --button-bg: #4CAF50;
      --button-hover-bg: #45a049;
      --mcq-bg: #fff;
      --shadow-color: rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      height: 100vh;
      background-color: var(--bg-color);
      color: var(--text-color);
    }

    #playlist {
      width: 25%;
      background-color: var(--sidebar-bg);
      border-left: 1px solid #ddd;
      overflow-y: auto;
      box-shadow: -1px 0 3px var(--shadow-color);
    }

    #playlist h3 {
      padding: 20px;
      margin: 0;
      background-color: var(--primary-color);
      color: #fff;
      font-size: 1.5em;
    }

    #playlist ul {
      list-style-type: none;
      margin: 0;
      padding: 0;
    }

    #playlist li {
      padding: 15px;
      border-bottom: 1px solid #ddd;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    #playlist li:hover {
      background-color: var(--sidebar-hover);
    }

    #playlist li.active {
      background-color: var(--primary-color);
      color: white;
    }

    #video-container {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }

    #submit-all {
      display: block;
      /* Ensure it's block or inline-block when visible */
      margin: 10px auto;
      padding: 10px 20px;
      background-color: #007bff;
      /* Bootstrap primary color */
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    #video-player {
      width: 100%;
      height: 60%;
      background-color: #000;
      margin-bottom: 20px;
      border-radius: 8px;
    }

    #mcqs-container {
      background-color: var(--mcq-bg);
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px var(--shadow-color);
      display: none;
    }

    .mcq {
      margin-bottom: 20px;
    }

    .mcq h4 {
      margin: 0 0 10px 0;
      font-size: 1.1em;
    }

    .mcq label {
      display: block;
      margin-bottom: 5px;
    }

    button {
      background-color: var(--button-bg);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: var(--button-hover-bg);
    }

    #submit-all {
      margin-top: 20px;
      display: none;
      background-color: #ff9800;
    }
  </style>
</head>

<body>
  <div id="playlist">
    <h3>Playlist</h3>
    <ul>
      <?php foreach ($videos as $video): ?>
        <li class="<?= $video['video_id'] == $selected_video_id ? 'active' : '' ?>"
          onclick="location.href='?video_id=<?= $video['video_id'] ?>'">
          <?= htmlspecialchars($video['video_title']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div id="video-container">
    <video id="video-player" controls>
      <source src="<?= $videos[array_search($selected_video_id, array_column($videos, 'video_id'))]['video_path'] ?>"
        type="video/mp4">
      Your browser does not support the video tag.
    </video>

    <div id="mcqs-container">
      <!-- MCQs will be dynamically populated here -->
    </div>
    <button id="submit-all" style="display: none;">Submit</button>
  </div>
  <!-- Within the existing HTML document, ensure JavaScript is correctly integrated -->
  <script>
    // Dynamically Display Video with MCQs
    const videoContainer = document.getElementById("video-container");
    const video = document.createElement("video");
    const mcqSection = document.getElementById("mcq-section");
    const mcqContent = document.getElementById("mcq-content");
    const submitButton = document.getElementById("submit-quiz");

    const checkpoints = <?= json_encode($checkpoints) ?>;
    const mcqs = <?= json_encode($mcqs) ?>;

    const videoId = <?= json_encode($video_id) ?>;
    let totalQuestions = 0;
    let correctAnswers = 0;
    let lastCheckpoint = -1; // Prevent duplicate checkpoint triggers

    // Initialize and load the video dynamically
    video.id = "video-player";
    video.src = <?= json_encode($video_src) ?>;
    video.controls = true;
    video.width = "100%";
    videoContainer.appendChild(video);

    // Function to display MCQs for a checkpoint
    function displayMCQs(checkpointId) {
      if (lastCheckpoint === checkpointId) return;
      lastCheckpoint = checkpointId;
      mcqSection.style.display = "block";
      mcqContent.innerHTML = `<h3>MCQs for Checkpoint ${checkpointId}</h3>`;

      if (mcqs[checkpointId]) {
        mcqs[checkpointId].forEach((mcq) => {
          const mcqElement = document.createElement("div");
          mcqElement.classList.add("mcq");
          mcqElement.innerHTML = `
        <h4>${mcq.question}</h4>
        <label><input type="radio" name="mcq_${mcq.checkpoint_id}_q${mcq.mcq_id}" value="A"> ${mcq.option_a}</label><br>
        <label><input type="radio" name="mcq_${mcq.checkpoint_id}_q${mcq.mcq_id}" value="B"> ${mcq.option_b}</label><br>
        <label><input type="radio" name="mcq_${mcq.checkpoint_id}_q${mcq.mcq_id}" value="C"> ${mcq.option_c}</label><br>
        <label><input type="radio" name="mcq_${mcq.checkpoint_id}_q${mcq.mcq_id}" value="D"> ${mcq.option_d}</label>
      `;
          mcqContent.appendChild(mcqElement);
          totalQuestions++;
        });

        submitButton.style.display = "block";
        forcePauseVideo();
        video.controls = false;

        window.scrollTo({
          top: mcqSection.offsetTop,
          behavior: "smooth",
        });
      } else {
        mcqContent.innerHTML = "<p>No MCQs available for this checkpoint.</p>";
      }
    }

    // Pause the video at checkpoints
    function forcePauseVideo() {
      video.pause();
      setTimeout(() => {
        if (!video.paused) video.pause();
      }, 50);
    }

    // Track video progress and display MCQs
    video.addEventListener("timeupdate", () => {
      const currentTime = Math.floor(video.currentTime);
      checkpoints.forEach((checkpoint) => {
        if (checkpoint.video_id == videoId && currentTime >= checkpoint.time_in_seconds) {
          displayMCQs(checkpoint.checkpoint_id);
        }
      });
    });

    // Handle MCQ submission
    submitButton.addEventListener("click", () => {
      correctAnswers = 0;

      mcqs[videoId].forEach((mcq) => {
        const selectedRadio = document.querySelector(
          `input[name="mcq_${mcq.checkpoint_id}_q${mcq.mcq_id}"]:checked`
        );

        if (selectedRadio && selectedRadio.value === mcq.correct_option) {
          correctAnswers++;
        }
      });

      const percentage = (correctAnswers / totalQuestions) * 100;
      mcqContent.innerHTML = `<h3>Quiz Completed</h3><p>You got ${correctAnswers} out of ${totalQuestions} correct!<br>Score: ${percentage.toFixed(2)}%</p>`;
      submitButton.style.display = "none";

      setTimeout(() => {
        mcqSection.style.display = "none";
      }, 3000);

      if (percentage < 50) {
        Toastify({
          text: "You scored below 50%. Please try again!",
          backgroundColor: "red",
          position: "bottom-right",
          duration: 5000,
        }).showToast();
        setTimeout(() => {
          location.reload();
        }, 3000);
      } else {
        Toastify({
          text: `Congratulations! You passed with a score of ${percentage.toFixed(2)}%`,
          backgroundColor: "green",
          position: "bottom-right",
          duration: 5000,
        }).showToast();
        setTimeout(() => {
          video.controls = true;
          video.play();
        }, 3000);
      }
    });
  </script>

</body>

</html>