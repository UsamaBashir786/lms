<?php
session_start(); // Start the session

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the login page
header('Location: teacher-portal-login.php');
exit;
