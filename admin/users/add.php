<?php require_once '../header.php'; ?>

<?php require_once '../sidebar.php'; ?>
<div class="col-lg-10">
  <div class="container">
    <div class="row mt-4 ps-4">
      <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-4">
        <h3 class="mb-0">Add User</h3>
        <a href="#" class="arrow-link">
          <a href="index.php"><img src="/assets/images/icons8-arrow-left-48.png" alt="png" width="30"></a>
        </a>
      </div>
    </div>
  </div>

  <body>

    <div class="container mt-5">
      <!-- form -->
      <div class="row justify-content-center">
        <div class="col-md-12 ms-4">
          <div class="card mb-3">
            <div class="card-body">
              <form method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" id="first_name" placeholder="Enter your first name">
                  </div>
                  <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" id="last_name" placeholder="Enter your last name">
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email">
                  </div>
                  <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" id="phone" placeholder="+92 (___) ___-____">
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" id="dob" placeholder="Enter your date of birth">
                  </div>
                  <div class="col-md-6">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" class="form-select" id="gender">
                      <option value="" disabled selected>Select your gender</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password">
                  </div>
                  <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" name="role" id="role">
                      <option value="" disabled selected>Select your role</option>
                      <option value="Admin">Admin</option>
                      <option value="User">User</option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <button type="submit" class="btn btn-primary ps-3 pe-3">Register</button>
                  </div>
                </div>
              </form>
            </div> <!-- card body -->
          </div> <!-- card -->
        </div> <!-- col -->
      </div> <!-- row -->
    </div>
    <?php require_once '../footer.php'; ?>
</div>