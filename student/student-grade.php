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
                  COUNT(*) as total_attempts,
                  COUNT(DISTINCT quiz_id) as total_quizzes,
                  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_quizzes
                FROM quiz_attempts 
                WHERE student_id = ?";
$stmt_overall = $conn->prepare($sql_overall);
$stmt_overall->bind_param("i", $student_id);
$stmt_overall->execute();
$result_overall = $stmt_overall->get_result();
$quiz_stats = $result_overall->fetch_assoc();

// Get total questions and correct answers
$sql_responses = "SELECT 
                    COUNT(*) as total_questions,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as total_correct
                  FROM quiz_responses qr
                  JOIN quiz_attempts qa ON qr.attempt_id = qa.attempt_id
                  WHERE qa.student_id = ? AND qa.status = 'completed'";
$stmt_responses = $conn->prepare($sql_responses);
$stmt_responses->bind_param("i", $student_id);
$stmt_responses->execute();
$result_responses = $stmt_responses->get_result();
$response_stats = $result_responses->fetch_assoc();
$quiz_stats = array_merge($quiz_stats, $response_stats);

// Recent quiz results (last 5)
$sql_recent = "SELECT 
                qa.attempt_id,
                qa.quiz_id,
                qa.score,
                qa.start_time,
                qa.end_time,
                q.title as quiz_title,
                t.full_name as teacher_name,
                (SELECT COUNT(*) FROM quiz_responses WHERE attempt_id = qa.attempt_id AND is_correct = 1) as correct_answers,
                (SELECT COUNT(*) FROM quiz_responses WHERE attempt_id = qa.attempt_id) as total_questions
              FROM quiz_attempts qa
              JOIN quizzes q ON qa.quiz_id = q.quiz_id
              JOIN teachers t ON q.teacher_id = t.id
              WHERE qa.student_id = ? AND qa.status = 'completed'
              ORDER BY qa.end_time DESC
              LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("i", $student_id);
$stmt_recent->execute();
$result_recent = $stmt_recent->get_result();
while ($row = $result_recent->fetch_assoc()) {
  $recent_quizzes[] = $row;
}

// Performance by quiz category/teacher
$sql_by_teacher = "SELECT 
                    t.full_name as teacher_name,
                    COUNT(DISTINCT qa.attempt_id) as attempts,
                    AVG(qa.score) as avg_score,
                    MAX(qa.score) as best_score
                  FROM quiz_attempts qa
                  JOIN quizzes q ON qa.quiz_id = q.quiz_id
                  JOIN teachers t ON q.teacher_id = t.id
                  WHERE qa.student_id = ? AND qa.status = 'completed'
                  GROUP BY t.id
                  ORDER BY avg_score DESC";
$stmt_by_teacher = $conn->prepare($sql_by_teacher);
$stmt_by_teacher->bind_param("i", $student_id);
$stmt_by_teacher->execute();
$result_by_teacher = $stmt_by_teacher->get_result();
$teacher_performance = [];
while ($row = $result_by_teacher->fetch_assoc()) {
  $teacher_performance[] = $row;
}

// Calculate improvement over time
$sql_improvement = "SELECT 
                    DATE(end_time) as quiz_date,
                    AVG(score) as daily_avg
                  FROM quiz_attempts
                  WHERE student_id = ? AND status = 'completed'
                  GROUP BY DATE(end_time)
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

// Get in-progress quizzes
$sql_in_progress = "SELECT 
                    qa.attempt_id,
                    qa.quiz_id,
                    qa.start_time,
                    q.title as quiz_title,
                    t.full_name as teacher_name,
                    q.time_limit_minutes
                  FROM quiz_attempts qa
                  JOIN quizzes q ON qa.quiz_id = q.quiz_id
                  JOIN teachers t ON q.teacher_id = t.id
                  WHERE qa.student_id = ? AND qa.status = 'in_progress'
                  ORDER BY qa.start_time DESC";
$stmt_in_progress = $conn->prepare($sql_in_progress);
$stmt_in_progress->bind_param("i", $student_id);
$stmt_in_progress->execute();
$result_in_progress = $stmt_in_progress->get_result();
$in_progress_quizzes = [];
while ($row = $result_in_progress->fetch_assoc()) {
  $in_progress_quizzes[] = $row;
}

// Function to calculate time difference in a friendly format
function timeDifference($start_time)
{
  $start = new DateTime($start_time);
  $now = new DateTime();
  $diff = $start->diff($now);

  if ($diff->days > 0) {
    return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
  } elseif ($diff->h > 0) {
    return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
  } elseif ($diff->i > 0) {
    return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
  } else {
    return 'just now';
  }
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Main Dashboard Styles */
    .main-content {
      padding: 20px;
    }

    .dashboard-section {
      margin-bottom: 30px;
    }

    .section-header {
      color: #333;
      font-size: 1.5rem;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #eee;
    }

    /* Performance Summary */
    .dashboard-summary {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .summary-text {
      font-size: 1.1rem;
      color: #555;
      line-height: 1.6;
    }

    .highlight {
      color: var(--primary-color);
      font-weight: 600;
    }

    /* Stats Overview */
    .stats-overview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }

    .stat-box {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .stat-label {
      color: #666;
      font-size: 0.9rem;
    }

    /* Stats Cards */
    .stats-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .stats-card h3 {
      color: #333;
      font-size: 1.2rem;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }

    /* Chart Container */
    .chart-container {
      position: relative;
      height: 300px;
      width: 100%;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 30px 20px;
      color: #777;
    }

    .empty-state i {
      font-size: 3rem;
      color: #ddd;
      margin-bottom: 15px;
    }

    .empty-state p {
      font-size: 0.95rem;
    }

    /* Recent Quiz Table */
    .scroll-table {
      overflow-x: auto;
    }

    .recent-quiz-table {
      width: 100%;
      border-collapse: collapse;
    }

    .recent-quiz-table th,
    .recent-quiz-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    .recent-quiz-table th {
      background-color: #f9f9f9;
      font-weight: 600;
      color: #444;
    }

    .recent-quiz-table tr:hover {
      background-color: #f5f5f5;
    }

    .timestamp {
      font-family: monospace;
      background-color: #f1f1f1;
      padding: 3px 6px;
      border-radius: 3px;
    }

    /* Performance Labels */
    .performance-label {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: normal;
      color: white;
    }

    .excellent {
      background-color: #2ecc71;
    }

    .good {
      background-color: #3498db;
    }

    .average {
      background-color: #f39c12;
    }

    .needs-improvement {
      background-color: #e74c3c;
    }

    /* In-Progress Quizzes */
    .in-progress-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }

    .quiz-card {
      background-color: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
    }

    .quiz-header {
      background-color: var(--primary-color);
      color: white;
      padding: 15px;
    }

    .quiz-header h4 {
      margin: 0;
      font-size: 1.1rem;
    }

    .quiz-body {
      padding: 15px;
      flex-grow: 1;
    }

    .quiz-info {
      margin-bottom: 10px;
    }

    .quiz-info span {
      display: block;
      margin-bottom: 5px;
      color: #666;
    }

    .quiz-info i {
      margin-right: 5px;
      width: 16px;
      text-align: center;
      color: var(--primary-color);
    }

    .quiz-footer {
      padding: 15px;
      background-color: #f8f9fa;
      border-top: 1px solid #eee;
    }

    .continue-btn {
      display: block;
      width: 100%;
      padding: 10px;
      text-align: center;
      background-color: var(--primary-color);
      color: white;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .continue-btn:hover {
      background-color: #005522;
    }

    /* Row for charts */
    .row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -10px;
    }

    .col-md-6 {
      flex: 0 0 50%;
      max-width: 50%;
      padding: 0 10px;
    }

    @media (max-width: 768px) {
      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      .stats-overview {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      }

      .stat-value {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <!-- JQuery for interactive elements -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="main-header">
      <h1>Performance Dashboard</h1>

      <!-- User dropdown -->
      <div class="user-dropdown">
        <button class="text-white">
          <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image">
          <?php echo htmlspecialchars($fullName); ?>&nbsp;
          <i class="fa fa-arrow-down"></i>
        </button>
        <div class="dropdown-content">
          <a href="profile-settings.php">Profile Settings</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>

    <!-- Dashboard Sections -->
    <div class="dashboard-section">
      <h2 class="section-header">Your Learning Progress</h2>

      <?php if ($quiz_stats['total_attempts'] > 0): ?>
        <!-- Performance Summary -->
        <div class="dashboard-summary">
          <p class="summary-text">
            You've attempted <span class="highlight"><?php echo number_format($quiz_stats['total_attempts'] ?? 0); ?></span> quizzes
            and completed <span class="highlight"><?php echo number_format($quiz_stats['completed_quizzes'] ?? 0); ?></span> of them
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
          <div class="stat-label">Total Quizzes</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format($quiz_stats['completed_quizzes'] ?? 0); ?></div>
          <div class="stat-label">Completed</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format(($quiz_stats['avg_score'] ?? 0), 1); ?>%</div>
          <div class="stat-label">Average Score</div>
        </div>
        <div class="stat-box">
          <div class="stat-value"><?php echo number_format($accuracy, 1); ?>%</div>
          <div class="stat-label">Accuracy</div>
        </div>
      </div>

      <!-- In-Progress Quizzes -->
      <?php if (!empty($in_progress_quizzes)): ?>
        <h3>Resume Your Quizzes</h3>
        <div class="in-progress-container">
          <?php foreach ($in_progress_quizzes as $quiz): ?>
            <div class="quiz-card">
              <div class="quiz-header">
                <h4><?php echo htmlspecialchars($quiz['quiz_title']); ?></h4>
              </div>
              <div class="quiz-body">
                <div class="quiz-info">
                  <span><i class="fas fa-user-tie"></i> Teacher: <?php echo htmlspecialchars($quiz['teacher_name']); ?></span>
                  <span><i class="fas fa-clock"></i> Started: <?php echo timeDifference($quiz['start_time']); ?></span>
                  <?php if ($quiz['time_limit_minutes']): ?>
                    <span><i class="fas fa-hourglass-half"></i> Time Limit: <?php echo $quiz['time_limit_minutes']; ?> minutes</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="quiz-footer">
                <a href="quiz.php?id=<?php echo $quiz['quiz_id']; ?>&take=1&attempt_id=<?php echo $quiz['attempt_id']; ?>" class="continue-btn">
                  <i class="fas fa-play-circle"></i> Continue Quiz
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

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
                <i class="fas fa-chart-line"></i>
                <p>Not enough data to show performance trend. Complete more quizzes to see your progress over time.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Performance by Teacher -->
        <div class="col-md-6">
          <div class="stats-card">
            <h3>Performance by Teacher</h3>
            <?php if (!empty($teacher_performance)): ?>
              <div class="chart-container">
                <canvas id="teacherPerformanceChart"></canvas>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-chart-bar"></i>
                <p>No teacher performance data available yet. Complete quizzes from different teachers to see your results.</p>
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
                  <th>Quiz</th>
                  <th>Teacher</th>
                  <th>Score</th>
                  <th>Performance</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_quizzes as $quiz) : ?>
                  <tr>
                    <td><?php echo date('M d, Y H:i', strtotime($quiz['end_time'])); ?></td>
                    <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
                    <td><?php echo htmlspecialchars($quiz['teacher_name']); ?></td>
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
                    <td>
                      <a href="quiz-results.php?attempt_id=<?php echo $quiz['attempt_id']; ?>" class="btn btn-sm btn-outline-primary">
                        View Results
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <p>No quiz results available yet. Start taking quizzes to see your performance.</p>
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
                }
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(44, 62, 80, 0.9)',
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

    <?php if (!empty($teacher_performance)): ?>
      // Performance by Teacher Chart
      const teacherCtx = document.getElementById('teacherPerformanceChart').getContext('2d');
      const teacherChart = new Chart(teacherCtx, {
        type: 'bar',
        data: {
          labels: [
            <?php foreach ($teacher_performance as $data) : ?> "<?php echo htmlspecialchars($data['teacher_name']); ?>",
            <?php endforeach; ?>
          ],
          datasets: [{
            label: 'Average Score',
            data: [
              <?php foreach ($teacher_performance as $data) : ?>
                <?php echo $data['avg_score']; ?>,
              <?php endforeach; ?>
            ],
            backgroundColor: [
              <?php foreach ($teacher_performance as $data) : ?>
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
                }
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(44, 62, 80, 0.9)',
              callbacks: {
                title: function(context) {
                  return context[0].label;
                },
                label: function(context) {
                  return 'Average: ' + context.raw.toFixed(1) + '%';
                },
                afterLabel: function(context) {
                  const index = context.dataIndex;
                  return 'Attempts: ' + <?php echo json_encode(array_column($teacher_performance, 'attempts')); ?>[index];
                }
              }
            }
          }
        }
      });
    <?php endif; ?>
  </script>
</body>

</html>