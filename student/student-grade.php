<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['student_id'])) {
  header('Location: student-login.php');
  exit;
}
// Fetch the video_id from the URL, ensure it's an integer
$video_id = isset($_GET['video_id']) ? (int) $_GET['video_id'] : 0;

// Include the database connection file
include_once '../db/db.php';

// Get student information
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT full_name, profile_image FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$fullName = $student['full_name'];
$profileImage = $student['profile_image'] ?? 'default-profile.jpg';

// Get quiz performance data
$quiz_stats = [];
$recent_quizzes = [];

// Overall quiz statistics
$sql_overall = "SELECT 
                  AVG(score) as avg_score, 
                  COUNT(*) as total_quizzes,
                  SUM(correct_answers) as total_correct,
                  SUM(total_questions) as total_questions
                FROM quiz_results 
                WHERE student_id = ?";
$stmt_overall = $conn->prepare($sql_overall);
$stmt_overall->bind_param("i", $student_id);
$stmt_overall->execute();
$result_overall = $stmt_overall->get_result();
$quiz_stats = $result_overall->fetch_assoc();

// Recent quiz results (last 5)
$sql_recent = "SELECT 
                qr.result_id,
                qr.score,
                qr.correct_answers,
                qr.total_questions,
                qr.completion_date,
                v.video_title,
                c.time_in_seconds
              FROM quiz_results qr
              JOIN checkpoints c ON qr.checkpoint_id = c.checkpoint_id
              JOIN videos v ON c.video_id = v.video_id
              WHERE qr.student_id = ?
              ORDER BY qr.completion_date DESC
              LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("i", $student_id);
$stmt_recent->execute();
$result_recent = $stmt_recent->get_result();
while ($row = $result_recent->fetch_assoc()) {
  $recent_quizzes[] = $row;
}

// Performance by video
$sql_by_video = "SELECT 
                  v.video_title,
                  COUNT(qr.result_id) as attempts,
                  AVG(qr.score) as avg_score,
                  MAX(qr.score) as best_score
                FROM quiz_results qr
                JOIN checkpoints c ON qr.checkpoint_id = c.checkpoint_id
                JOIN videos v ON c.video_id = v.video_id
                WHERE qr.student_id = ?
                GROUP BY v.video_id
                ORDER BY v.video_title";
$stmt_by_video = $conn->prepare($sql_by_video);
$stmt_by_video->bind_param("i", $student_id);
$stmt_by_video->execute();
$result_by_video = $stmt_by_video->get_result();
$video_performance = [];
while ($row = $result_by_video->fetch_assoc()) {
  $video_performance[] = $row;
}

// Calculate improvement over time
$sql_improvement = "SELECT 
                    DATE(completion_date) as quiz_date,
                    AVG(score) as daily_avg
                  FROM quiz_results
                  WHERE student_id = ?
                  GROUP BY DATE(completion_date)
                  ORDER BY quiz_date
                  LIMIT 10";
$stmt_improvement = $conn->prepare($sql_improvement);
$stmt_improvement->bind_param("i", $student_id);
$stmt_improvement->execute();
$result_improvement = $stmt_improvement->get_result();
$improvement_data = [];
while ($row = $result_improvement->fetch_assoc()) {
  $improvement_data[] = $row;
}

// Helper function to format seconds to minutes:seconds
function formatTime($seconds)
{
  $minutes = floor($seconds / 60);
  $secs = $seconds % 60;
  return sprintf('%02d:%02d', $minutes, $secs);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Performance Dashboard</title>
  <?php include 'includes/css-links.php'; ?>
  <!-- Add Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="assets/css/student-grade.css">
</head>

<body>
  <!-- JQuery and Toastr for notifications -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="main-header">
      <h1>Welcome, <?php echo htmlspecialchars($fullName); ?></h1>

      <!-- Dropdown for user settings -->
      <div class="user-dropdown">
        <button class="text-white">
          <img src="../uploads/<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image">
          <span><?php echo htmlspecialchars($fullName); ?></span>&nbsp;
          <i class="fa fa-chevron-down"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile-settings.php"><i class="fa fa-user-circle"></i> Profile Settings</a>
          <a href="notification-preferences.php"><i class="fa fa-bell"></i> Notifications</a>
          <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
        </div>
      </div>
    </div>

    <!-- Dashboard Sections -->
    <div class="dashboard-section">
      <h2 class="section-header">Learning Performance</h2>

      <?php if ($quiz_stats['total_quizzes'] > 0): ?>
        <!-- Performance Summary -->
        <div class="dashboard-summary">
          <p class="summary-text">
            You've completed <span class="highlight"><?php echo number_format($quiz_stats['total_quizzes'] ?? 0); ?></span> quizzes
            with an average score of <span class="highlight"><?php echo number_format(($quiz_stats['avg_score'] ?? 0), 1); ?>%</span>.
            Your overall accuracy is <span class="highlight"><?php
                                                              $accuracy = ($quiz_stats['total_questions'] > 0) ?
                                                                ($quiz_stats['total_correct'] / $quiz_stats['total_questions']) * 100 : 0;
                                                              echo number_format($accuracy, 1);
                                                              ?>%</span>.
            <?php if ($accuracy >= 80): ?>
              Great work! Your performance is excellent.
            <?php elseif ($accuracy >= 60): ?>
              You're doing well, keep up the good work!
            <?php else: ?>
              Continue practicing to improve your results.
            <?php endif; ?>
          </p>
        </div>
      <?php endif; ?>

      <!-- Statistics Overview -->
      <div class="stats-overview">
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format($quiz_stats['total_quizzes'] ?? 0); ?></div>
          <div class="stat-label">Quizzes Completed</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format(($quiz_stats['avg_score'] ?? 0), 1); ?>%</div>
          <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format($quiz_stats['total_correct'] ?? 0); ?></div>
          <div class="stat-label">Correct Answers</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php
                                  $accuracy = ($quiz_stats['total_questions'] > 0) ?
                                    ($quiz_stats['total_correct'] / $quiz_stats['total_questions']) * 100 : 0;
                                  echo number_format($accuracy, 1);
                                  ?>%</div>
          <div class="stat-label">Overall Accuracy</div>
        </div>
      </div>

      <div class="row">
        <!-- Performance Over Time Chart -->
        <div class="col-md-6">
          <div class="stats-card">
            <h3>Performance Trend</h3>
            <?php if (count($improvement_data) > 1): ?>
              <div class="chart-container">
                <canvas id="performanceChart"></canvas>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i class="fa fa-line-chart"></i>
                <p>Not enough data to show performance trend. Complete more quizzes to see your progress over time.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Performance by Video -->
        <div class="col-md-6">
          <div class="stats-card">
            <h3>Performance by Course</h3>
            <?php if (!empty($video_performance)): ?>
              <div class="chart-container">
                <canvas id="videoPerformanceChart"></canvas>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i class="fa fa-bar-chart"></i>
                <p>No course performance data available yet. Complete quizzes from different courses to see your results.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Recent Quizzes -->
      <div class="stats-card">
        <h3>Recent Quiz Results</h3>
        <?php if (!empty($recent_quizzes)) : ?>
          <div class="scroll-table">
            <table class="recent-quiz-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Course</th>
                  <th>Checkpoint Time</th>
                  <th>Score</th>
                  <th>Performance</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_quizzes as $quiz) : ?>
                  <tr>
                    <td><?php echo date('M d, Y H:i', strtotime($quiz['completion_date'])); ?></td>
                    <td><?php echo htmlspecialchars($quiz['video_title']); ?></td>
                    <td><span class="timestamp"><?php echo formatTime($quiz['time_in_seconds']); ?></span></td>
                    <td><?php echo $quiz['correct_answers'] . '/' . $quiz['total_questions']; ?>
                      (<?php echo number_format($quiz['score'], 1); ?>%)</td>
                    <td>
                      <?php
                      $score = $quiz['score'];
                      if ($score >= 90) {
                        echo '<span class="performance-label excellent">Excellent</span>';
                      } elseif ($score >= 75) {
                        echo '<span class="performance-label good">Good</span>';
                      } elseif ($score >= 50) {
                        echo '<span class="performance-label average">Average</span>';
                      } else {
                        echo '<span class="performance-label needs-improvement">Needs Improvement</span>';
                      }
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <div class="empty-state">
            <i class="fa fa-clipboard-list"></i>
            <p>No quiz results available yet. Start watching videos and completing quizzes to see your performance.</p>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <script>
    <?php if (count($improvement_data) > 1): ?>
      // Performance Trend Chart
      const performanceCtx = document.getElementById('performanceChart').getContext('2d');
      const performanceChart = new Chart(performanceCtx, {
        type: 'line',
        data: {
          labels: [
            <?php foreach ($improvement_data as $data) : ?> "<?php echo date('M d', strtotime($data['quiz_date'])); ?>",
            <?php endforeach; ?>
          ],
          datasets: [{
            label: 'Average Score (%)',
            data: [
              <?php foreach ($improvement_data as $data) : ?>
                <?php echo $data['daily_avg']; ?>,
              <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderColor: 'rgba(52, 152, 219, 1)',
            borderWidth: 2,
            pointBackgroundColor: 'rgba(52, 152, 219, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.3,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              },
              ticks: {
                callback: function(value) {
                  return value + '%';
                },
                font: {
                  family: "'Poppins', sans-serif"
                }
              }
            },
            x: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  family: "'Poppins', sans-serif"
                }
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(44, 62, 80, 0.9)',
              titleFont: {
                family: "'Poppins', sans-serif",
                size: 14
              },
              bodyFont: {
                family: "'Poppins', sans-serif",
                size: 13
              },
              callbacks: {
                label: function(context) {
                  return 'Score: ' + context.raw.toFixed(1) + '%';
                }
              }
            }
          }
        }
      });
    <?php endif; ?>

    <?php if (!empty($video_performance)): ?>
      // Performance by Video Chart
      const videoCtx = document.getElementById('videoPerformanceChart').getContext('2d');
      const videoChart = new Chart(videoCtx, {
        type: 'bar',
        data: {
          labels: [
            <?php foreach ($video_performance as $data) : ?> "<?php echo htmlspecialchars($data['video_title']); ?>",
            <?php endforeach; ?>
          ],
          datasets: [{
            label: 'Average Score',
            data: [
              <?php foreach ($video_performance as $data) : ?>
                <?php echo $data['avg_score']; ?>,
              <?php endforeach; ?>
            ],
            backgroundColor: [
              <?php foreach ($video_performance as $data) : ?>
                <?php
                $score = $data['avg_score'];
                if ($score >= 90) {
                  echo "'rgba(46, 204, 113, 0.8)'";
                } elseif ($score >= 75) {
                  echo "'rgba(52, 152, 219, 0.8)'";
                } elseif ($score >= 50) {
                  echo "'rgba(241, 196, 15, 0.8)'";
                } else {
                  echo "'rgba(231, 76, 60, 0.8)'";
                }
                ?>,
              <?php endforeach; ?>
            ],
            borderWidth: 0,
            borderRadius: 5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              },
              ticks: {
                callback: function(value) {
                  return value + '%';
                },
                font: {
                  family: "'Poppins', sans-serif"
                }
              }
            },
            x: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  family: "'Poppins', sans-serif"
                }
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(44, 62, 80, 0.9)',
              titleFont: {
                family: "'Poppins', sans-serif",
                size: 14
              },
              bodyFont: {
                family: "'Poppins', sans-serif",
                size: 13
              },
              callbacks: {
                title: function(context) {
                  return context[0].label;
                },
                label: function(context) {
                  return 'Average: ' + context.raw.toFixed(1) + '%';
                },
                afterLabel: function(context) {
                  const index = context.dataIndex;
                  return 'Attempts: ' + <?php echo json_encode(array_column($video_performance, 'attempts')); ?>[index];
                }
              }
            }
          }
        }
      });
    <?php endif; ?>

    // Initialize tooltips, if using Bootstrap
    if (typeof $().tooltip === 'function') {
      $('[data-toggle="tooltip"]').tooltip();
    }
  </script>
</body>

</html>