<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'includes/head.php' ?>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>
  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <!-- Header -->
    <div class="bg-success text-white p-4 mb-4 rounded">
      <h2 class="mb-0">Dashboard Overview</h2>
    </div>

    <!-- Dashboard Cards -->
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Students</h5>
          <div class="stat-number">200</div>
          <button class="btn btn-primary" onclick="location.href='#students'">
            <i class="fas fa-eye me-2"></i>View Students
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Users</h5>
          <div class="stat-number">350</div>
          <button class="btn btn-primary" onclick="location.href='#users'">
            <i class="fas fa-eye me-2"></i>View Users
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Comments</h5>
          <div class="stat-number">40</div>
          <button class="btn btn-primary" onclick="location.href='#comments'">
            <i class="fas fa-eye me-2"></i>View Comments
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Courses</h5>
          <div class="stat-number">30</div>
          <button class="btn btn-primary" onclick="location.href='#courses'">
            <i class="fas fa-cog me-2"></i>Manage Courses
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Quizzes</h5>
          <div class="stat-number">25</div>
          <button class="btn btn-primary" onclick="location.href='#quizzes'">
            <i class="fas fa-cog me-2"></i>Manage Quizzes
          </button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card stat-card">
          <h5 class="card-title">Total Teachers</h5>
          <div class="stat-number">100</div>
          <button class="btn btn-primary" onclick="location.href='#teachers'">
            <i class="fas fa-eye me-2"></i>View Teachers
          </button>
        </div>
      </div>
    </div>
  </div>

<?php include 'includes/js-links.php' ?>
</body>

</html>