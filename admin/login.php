<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'includes/head.php'; ?>
  <!-- Custom CSS -->
</head>

<body>
  <div class="container-fluid p-0">
    <!-- Top Banner -->
    <div class="top-banner">
      <div class="container">
        <h1 class="display-4 fw-bold">Admin Panel</h1>
        <p class="lead">Manage all the website content and features</p>
      </div>
    </div>

    <!-- Login Form -->
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          <div class="login-card">
            <h2 class="login-title text-center">Admin Login</h2>

            <form id="loginForm" action="index.php" method="POST">
              <!-- Username -->
              <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username"><i class="fas fa-user me-2"></i>Username</label>
              </div>

              <!-- Password -->
              <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
              </div>

              <!-- Remember Me -->
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                <label class="form-check-label" for="rememberMe">
                  Remember me
                </label>
              </div>

              <!-- Submit Button -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" onclick="return validateLogin()">
                  <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
              </div>

              <div class="error-message text-center" id="error-message" style="display:none;">
                <i class="fas fa-exclamation-circle me-1"></i>Invalid username or password!
              </div>
            </form>

            <div class="footer text-center">
              <p class="mb-0">&copy; 2025 Website Admin Panel</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/js-links.php'; ?>
</body>

</html>