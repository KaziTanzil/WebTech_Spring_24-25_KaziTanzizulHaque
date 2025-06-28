C:\xampp\htdocs\Final_project\Controllers\logout.php

<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to home page
header('Location: ../Views/home.php');
exit;
?>