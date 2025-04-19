<?php
// session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Database connection
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=lms", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch student data
$student = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_students.php");
    exit();
}
try {
    $stmt = $pdo->prepare("SELECT id, full_name, email, phone, dob, course, profile_image FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        header("Location: manage_students.php");
        exit();
    }
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Handle form submission
$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $profile_image = $_FILES['profile_image'] ?? null;

    // Validation
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($dob)) $errors[] = "Date of birth is required.";
    if (empty($course)) $errors[] = "Course is required.";

    // Profile image handling
    $image_path = $student['profile_image'];
    if ($profile_image && $profile_image['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!in_array($profile_image['type'], $allowed_types)) {
            $errors[] = "Only PNG, JPG, or JPEG images are allowed.";
        } elseif ($profile_image['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image size must not exceed 2MB.";
        } else {
            $image_name = uniqid() . '_' . basename($profile_image['name']);
            $image_path = '../Uploads/' . $image_name;
            if (!move_uploaded_file($profile_image['tmp_name'], $image_path)) {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // If no errors, update database
    if (empty($errors)) {
        try {
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, dob = ?, course = ?, password = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $dob, $course, $hashed_password, $image_path, $_GET['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE students SET full_name = ?, email = ?, phone = ?, dob = ?, course = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $dob, $course, $image_path, $_GET['id']]);
            }
            $success = "Student updated successfully.";
        } catch (PDOException $e) {
            $errors[] = "Failed to update student: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar">
                <h4 class="text-center">Admin Panel</h4>
                <a href="index.php">Dashboard</a>
                <a href="manage_students.php">Manage Students</a>
                <a href="manage_teachers.php">Manage Teachers</a>
                <a href="manage_assignments.php">Manage Assignments</a>
                <a href="manage_quizzes.php">Manage Quizzes</a>
                <a href="manage_meetings.php">Manage Meetings</a>
                <a href="logout.php">Logout</a>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 p-4">
                <h1 class="mb-4">Edit Student</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Edit Student Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="course" class="form-label">Course</label>
                                <input type="text" class="form-control" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password (leave blank to keep unchanged)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Image (leave blank to keep current)</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/png,image/jpeg,image/jpg">
                                <small>Current: <img src="<?php echo htmlspecialchars($student['profile_image']); ?>" alt="Profile" style="width: 50px; height: 50px;"></small>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                            <a href="manage_students.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>