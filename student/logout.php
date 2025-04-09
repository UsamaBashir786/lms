<?php
session_start(); 

session_unset();
session_destroy();

header('Location: student-portal-login.php');
exit;
