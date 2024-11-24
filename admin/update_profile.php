<?php

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];

// Handle profile picture upload
if (isset($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['name'] != '') {
    $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    $query = "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email' WHERE id='$admin_id'";
} else {
    $query = "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email' WHERE id='$admin_id'";
}

if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Profile updated successfully!";
} else {
    $_SESSION['error'] = "Error updating profile!";
}

header('Location: settings.php');
?>
