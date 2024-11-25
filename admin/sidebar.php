<?php require_once 'header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-2 p-0">
      <nav class="sidebar sidebar-border p-3">
        <h3 class="text-dark fs-3 ms-3">Dashboard</h3>
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
                  <li><a class="text-dark text-decoration-none" href="#">All Courses</a></li>
                  <li><a class="text-dark text-decoration-none" href="#">Add Course</a></li>
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
                  <li><a class="text-dark text-decoration-none" href="#">All Users</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/users/index.php">Add User</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/teacher/index.php">Teachers</a></li>
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
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">Edit Profile</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">General</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">Header</a></li>
                  <li><a class="text-dark text-decoration-none" href="/admin/settings.php">Footer</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </div>