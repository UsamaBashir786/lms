<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

// Database connection (included for consistency, unused for hardcoded credentials)
try {
  $pdo = new PDO("mysql:host=127.0.0.1;dbname=lms", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  // Predefined credentials
  $valid_username = 'admin';
  $valid_password = 'admin123';

  // Validate credentials
  if ($username === $valid_username && $password === $valid_password) {
    $_SESSION['admin_id'] = 1; // Arbitrary ID for hardcoded admin
    header("Location: index.php");
    exit();
  } else {
    $error = "Invalid username or password!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'includes/head.php'; ?>
  <!-- Custom CSS -->
  <style>
    .top-banner {
      background-color: #343a40;
      color: white;
      padding: 2rem 0;
      text-align: center;
    }

    .login-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-top: 2rem;
    }

    .login-title {
      margin-bottom: 1.5rem;
    }

    .error-message {
      color: #dc3545;
      margin-top: 1rem;
    }

    .footer {
      margin-top: 1.5rem;
    }
  </style>
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

            <form id="loginForm" action="login.php" method="POST">
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

              <?php if ($error): ?>
                <div class="error-message text-center" id="error-message">
                  <i class="fas fa-exclamation-circle me-1"></i><?php echo htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>
            </form>

            <div class="footer text-center">
              <p class="mb-0">© 2025 Website Admin Panel</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/js-links.php'; ?>
  <script>
    function validateLogin() {
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const errorMessage = document.getElementById('error-message');

      if (!username || !password) {
        errorMessage.style.display = 'block';
        errorMessage.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Please fill in all fields!';
        return false;
      }
      return true;
    }
  </script>
</body>

</html>