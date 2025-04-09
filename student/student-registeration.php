<?php
include_once '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and validate inputs
  $fullName = isset($_POST['full_name']) ? $conn->real_escape_string(trim($_POST['full_name'])) : null;
  $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : null;
  $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : null;
  $dob = isset($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : null;
  $course = isset($_POST['course']) ? $conn->real_escape_string($_POST['course']) : null;
  $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_BCRYPT) : null;

  if ($fullName && $email && $phone && $dob && $course && $password) {
    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT email FROM students WHERE email = ?");
    $checkEmail->bind_param('s', $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
      $error = 'This email is already registered. Please use a different email address.';
    } else {
      // Handle file upload
      if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        $uploadDir = '../uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['profile_img']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }

        $fileType = mime_content_type($_FILES['profile_img']['tmp_name']);
        if (in_array($fileType, $allowedTypes) && $_FILES['profile_img']['size'] <= $maxFileSize) {
          if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFilePath)) {
            // Insert data into the database
            $stmt = $conn->prepare("INSERT INTO students (full_name, email, phone, dob, course, password, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $fullName, $email, $phone, $dob, $course, $password, $targetFilePath);

            if ($stmt->execute()) {
              // Redirect to thank-you.php
              header("Location: thank-you.php");
              exit; // Ensure no further code is executed
            } else {
              $error = 'Database Error: ' . $stmt->error;
            }

            $stmt->close();
          } else {
            $error = 'Failed to upload profile image.';
          }
        } else {
          $error = 'Invalid image type or size exceeds 2MB.';
        }
      } else {
        $error = 'Please upload a valid profile image.';
      }
    }
    $checkEmail->close();
  } else {
    $error = 'Please fill in all required fields.';
  }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/student-registeration.css">
</head>

<body>
  <!-- Banner Section -->
  <div class="banner text-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)),url(assets/images/background-contact.jpg);">
    <h1>Student Registration
      <br>
      <a href="../index.php" class="btn go-back mt-3">Go Back</a>
    </h1>
  </div>

  <!-- Main Content Section -->
  <div class="container my-5">
    <div class="row g-4">
      <!-- Instructions Section -->
      <div class="col-md-4">
        <h2>Instructions</h2>
        <p>Follow the steps below to register as a student:</p>
        <ul>
          <li>Enter your full name, email, and phone number.</li>
          <li>Select your date of birth from the calendar.</li>
          <li>Choose the course you are interested in.</li>
          <li>Set a strong password for your account.</li>
          <li>Upload a profile image.</li>
          <li>Click on the 'Register' button to complete your registration.</li>
        </ul>
      </div>

      <!-- Form Section -->
      <div class="col-md-8">
        <?php
        // If there's an error, display it
        if (isset($error)) {
          echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        }
        ?>
        <form id="student-form" method="POST" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="full-name" class="form-label">Full Name</label>
              <input type="text" id="full-name" name="full_name" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your phone number" required>
            </div>
            <div class="col-md-6">
              <label for="dob" class="form-label">Date of Birth</label>
              <input type="date" id="dob" name="dob" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="course" class="form-label">Course Interested In</label>
              <select id="course" name="course" class="form-select" required>
                <option value="html">HTML & CSS</option>
                <option value="js">JavaScript</option>
                <option value="php">PHP</option>
                <option value="mysql">MySQL</option>
                <option value="bootstrap">Bootstrap</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="password" class="form-label">Password</label>
              <input type="password" id="password" name="password" class="form-control" placeholder="Enter a password" required>
            </div>
          </div>
          <div class="col-md-12 mt-3">
            <label for="profile-img" class="form-label">Upload Profile Image</label>
            <input type="file" id="profile-img" name="profile_img" accept="image/*" required class="form-control">
            <div class="mt-3">
              <img id="img-preview" src="#" alt="Image Preview" style="display: none; max-width: 100%; max-height: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 8px;">
            </div>
          </div>
          <button type="submit" class="btn btn-success mt-4">Register</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Image Preview Script -->
  <script>
    document.getElementById('profile-img').addEventListener('change', function() {
      const preview = document.getElementById('img-preview');
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
      }
    });
  </script>
</body>

</html>