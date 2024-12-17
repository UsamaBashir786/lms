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
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="register py-5">
    <div class="container">
      <div class="row">
        <div class="col-6">
          <div class="card">
            <div class="card-header">
              <h3 class="text-center mb-4">Register</h3>
            </div>
            <div class="card-body">
              <form action="register" method="post" enctype="multipart/form-data">
                <!-- First Name and Last Name -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                  </div>
                </div>
                <!-- Email and Phone -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                  </div>
                </div>

                <!-- DO and Gender -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="dob" class="form-label">DOB</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select">
                      <option value="" selected disabled>Select your gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                      <option value="other">Other</option>
                    </select>
                  </div>

                </div>

                <!-- Password and Confirm Password -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                  </div>
                </div>
                <!-- checkbox -->
                <div class="form-check form-check-inline mb-3">
                  <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                  <label for="terms" class="form-check-label">
                    <span>I agree to the</span>
                    <a href="terms.php" class="text-decoration-none "> terms and conditions</a>
                  </label>
                </div>
              </form>
              <button type="submit" class="btn btn-dark px-4">Register</button>
            </div>
            <div class="card-footer">
              <span>Already have an account?</span>
              <a href="login.php">Login</a>
            </div>
          </div>
          <div class="home-page text-center mt-3">
            <a class="text-decoration-none" href="index.php">
              <i class="bi bi-arrow-left"></i> Back to Home
            </a>
          </div>
        </div>
        <div class="col-6">
          <img src="assets/images/banner1.png" class="image" alt="banner1" height="600" width="400">
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS link -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK" crossorigin="anonymous"></script>
</body>

</html>