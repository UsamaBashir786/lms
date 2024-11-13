<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Bootstrap CSS link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!-- Bootstrap Icons link -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
  <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
  <link rel="stylesheet" href="assets/css/admins.css">

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

  <!-- navbar -->
  <div class="container-fluid bg-light">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-light px-5">
        <a class="navbar-brand " href="#">Logo</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item active">
              <a class="nav-link" href="#"><i class="bi bi-bell-fill fs-5 me-3"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#"><i class="bi bi-envelope-fill fs-5 me-3"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link me-3" href="#"><img src="assets/images/profile" alt="" height="40px"></a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                Name
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="#">Profile</a>
                <a class="dropdown-item" href="#">Change Password</a>
                <a class="dropdown-item" href="#">Logout</a>
              </div>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar bg-dark text-white vh-100">
    <div class="container-fluid py-3">
      <div class="row">
        <div class="col-md-3 ">
          <h4 class="fw-bold mb-4">Dashboard</h4>

          <!-- Courses -->
          <div class="dropdown mb-3">
            <button class="btn btn-outline-light dropdown-toggle w-100 text-start" type="button" id="coursesDropdown"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bi bi-book"></i> Courses
            </button>
            <div class="dropdown-menu w-100" aria-labelledby="coursesDropdown">
              <a class="dropdown-item" href="all.php"><i class="bi bi-collection"></i> All Courses</a>
              <a class="dropdown-item" href="create.php"><i class="bi bi-plus-circle"></i> Add Courses</a>
              <a class="dropdown-item" href="#"><i class="bi bi-folder"></i> Categories</a>
              <a class="dropdown-item" href="#"><i class="bi bi-tag"></i> Tags</a>
            </div>
          </div>

          <!-- User -->
          <div class="dropdown mb-3">
            <button class="btn btn-outline-light dropdown-toggle w-100 text-start" type="button" id="userDropdown"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bi bi-person"></i> User
            </button>
            <div class="dropdown-menu w-100" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="#"><i class="bi bi-people"></i> All Users</a>
              <a class="dropdown-item" href="#"><i class="bi bi-person-plus"></i> Add User</a>
              <a class="dropdown-item" href="#"><i class="bi bi-folder"></i> Categories</a>
              <a class="dropdown-item" href="#"><i class="bi bi-tag"></i> Tags</a>
            </div>
          </div>

          <!-- Settings -->
          <div class="dropdown mb-3">
            <button class="btn btn-outline-light dropdown-toggle w-100 text-start" type="button" id="settingsDropdown"
              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bi bi-gear"></i> Settings
            </button>
            <div class="dropdown-menu w-100" aria-labelledby="settingsDropdown">
              <a class="dropdown-item" href="#"><i class="bi bi-lock"></i> Role & Permission</a>
              <a class="dropdown-item" href="#"><i class="bi bi-bell"></i> Notification</a>
              <a class="dropdown-item" href="#"><i class="bi bi-credit-card"></i> Payment Method</a>
              <a class="dropdown-item" href="#"><i class="bi bi-tag"></i> Tags</a>
            </div>
          </div>
        </div>
        <div class="col-md-9 mt-4">
          <div class="row">
            <!-- Courses Box -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-info text-white">
                <div class="card-body">
                  <h5 class="card-title">Courses</h5>
                  <p class="display-4">100</p>
                </div>
              </div>
            </div>

            <!-- Teachers Box -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-warning text-white">
                <div class="card-body">
                  <h5 class="card-title">Teachers</h5>
                  <p class="display-4">20</p>
                </div>
              </div>
            </div>

            <!-- Students Box -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-success text-white">
                <div class="card-body">
                  <h5 class="card-title">Students</h5>
                  <p class="display-4">500</p>
                </div>
              </div>
            </div>
            <!-- Review -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-warning text-white">
                <div class="card-body">
                  <h5 class="card-title">Review</h5>
                  <p class="display-4">1500</p>
                </div>
              </div>
            </div>
            <!-- Earnig -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-success text-white">
                <div class="card-body">
                  <h5 class="card-title"> Total Earning</h5>
                  <p class="display-4">$10,000</p>
                </div>
              </div>
            </div>
            <!-- monthly earning -->
            <div class="col-md-4 mb-4">
              <div class="card text-center bg-info  text-white">
                <div class="card-body">
                  <h5 class="card-title">Monthly Eanrning</h5>
                  <p class="display-4">$200</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Icons link -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script src="assets/js/owl.carousel.min.js"></script>
  <script src="assets/js/custom.js"></script>

</body>

</html>