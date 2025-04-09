  <!-- Sidebar Toggle Button (Mobile) -->
  <button class="btn btn-primary d-lg-none" id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h3 class="fw-bold">Admin Panel</h3>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="#dashboard" class="nav-link">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="#users" class="nav-link">
          <i class="fas fa-users"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a href="#courses" class="nav-link">
          <i class="fas fa-book"></i> Manage Courses
        </a>
      </li>
      <li class="nav-item">
        <a href="#quizzes" class="nav-link">
          <i class="fas fa-question-circle"></i> Manage Quizzes
        </a>
      </li>
      <li class="nav-item">
        <a href="#teachers" class="nav-link">
          <i class="fas fa-chalkboard-teacher"></i> Teachers
        </a>
      </li>
      <li class="nav-item">
        <a href="#settings" class="nav-link">
          <i class="fas fa-cogs"></i> Settings
        </a>
      </li>
    </ul>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');

      sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
      });

      // Close sidebar when clicking on a nav item on mobile
      const navLinks = document.querySelectorAll('.sidebar .nav-link');
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          if (window.innerWidth < 992) {
            sidebar.classList.remove('active');
            mainContent.classList.remove('active');
          }
        });
      });
    });
  </script>