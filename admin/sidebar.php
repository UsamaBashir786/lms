<?php require_once 'header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-2 p-0">
      <nav class="sidebar sidebar-border p-3">
        <a href="/admin/index.php" class="text-decoration-none fs-3 ms-4">Dashboard</a>
        <?php
        // Get the current file name
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>
        <div class="accordion mt-4 border-0" id="sidebarAccordion">
          <!-- Users Section -->
          <div class="accordion-item border-0">
            <h2 class="accordion-header" id="usersHeading">
              <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#usersCollapse" aria-expanded="false" aria-controls="usersCollapse">
                Courses
              </button>
            </h2>
            <div id="usersCollapse" class="accordion-collapse collapse" aria-labelledby="usersHeading"
              data-bs-parent="#sidebarAccordion">
              <div class="accordion-body p-0">
                <ul class="list-unstyled ms-3">
                  <li><a class="text-dark text-decoration-none" href="/admin/course/all.php" <?= ($currentPage == 'all.php' && strpos($_SERVER['REQUEST_URI'], 'course') !== false) ? 'active' : '' ?>>All Courses</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/course/create.php" <?= ($currentPage == 'create.php' && strpos($_SERVER['REQUEST_URI'], 'course') !== false) ? 'active' : '' ?>>Add Course</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Categories</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Tags</a></li>
                </ul>
              </div>
            </div>
          </div>
          <!-- Quiz Section -->
          <div class="accordion-item border-0">
            <h2 class="accordion-header" id="quizHeading">
              <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#quizCollapse" aria-expanded="false" aria-controls="quizCollapse">
                Quizes
              </button>
            </h2>
            <div id="quizCollapse" class="accordion-collapse collapse" aria-labelledby="quizHeading"
              data-bs-parent="#sidebarAccordion">
              <div class="accordion-body p-0">
                <ul class="list-unstyled ms-3">
                  <li><a class="text-dark text-decoration-none" href="#">All Quizes</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Add Quiz</a></li>
                </ul>
              </div>
            </div>
          </div>
          <!-- Courses Section -->
          <div class="accordion-item border-0">
            <h2 class="accordion-header" id="coursesHeading">
              <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#coursesCollapse" aria-expanded="false" aria-controls="coursesCollapse">
                Users
              </button>
            </h2>
            <div id="coursesCollapse" class="accordion-collapse collapse" aria-labelledby="coursesHeading"
              data-bs-parent="#sidebarAccordion">
              <div class="accordion-body p-0">
                <ul class="list-unstyled ms-3">
                  <li><a class="text-dark text-decoration-none" href="/admin/users/index.php" <?= ($currentPage == 'index.php' && strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : '' ?>>All Users</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/users/add.php" <?= ($currentPage == 'add.php' && strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : '' ?>>Add User</a></li>
                  <li><a class="text-dark text-decoration-none" href="">Teachers</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Students</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Admins</a></li>
                </ul>
              </div>
            </div>
          </div>
          <!-- Settings Section -->
          <div class="accordion-item border-0">
            <h2 class="accordion-header" id="settingsHeading">
              <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#settingsCollapse" aria-expanded="false" aria-controls="settingsCollapse">
                Settings
              </button>
            </h2>
            <div id="settingsCollapse" class="accordion-collapse collapse" aria-labelledby="settingsHeading"
              data-bs-parent="#sidebarAccordion">
              <div class="accordion-body p-0">
                <ul class="list-unstyled ms-3">
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php" <?= ($currentPage == 'settings.php') ? 'active' : '' ?>>Edit Profile</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php" <?= ($currentPage == 'settings.php') ? 'active' : '' ?>>General</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">Header</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">Footer</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </div>

    <script>
      // Select all sidebar links
      const links = document.querySelectorAll('.sidebar a');

      // Add click event to each link
      links.forEach(link => {
        link.addEventListener('click', () => {
          // Remove active-link class from all links
          links.forEach(l => l.classList.remove('active-link'));

          // Add active-link class to the clicked link
          link.classList.add('active-link');
        });
      });
    </script>