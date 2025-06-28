C:\xampp\htdocs\Final_project\Controllers\adminLoginValidate.php

<?php
session_start();
require_once '../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $_SESSION['login_error'] = 'Invalid CSRF token.';
        header('Location: ../Views/home.php');
        exit;
    }

    $gmail = filter_var($_POST['gmail'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($gmail) || empty($password)) {
        $_SESSION['login_error'] = 'Email and password are required.';
        header('Location: ../Views/home.php');
        exit;
    }

    // Check user table first
    $stmt = $conn->prepare("SELECT ID, Name, Password FROM user WHERE Gmail = ?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = [
            'id' => $user['ID'],
            'username' => $user['Name'],
            'email' => $gmail
        ];
        header('Location: ../Views/userProfile.php');
        exit;
    }

    // Check admin table if user check fails
    $stmt = $conn->prepare("SELECT ID, Name, Password, Role FROM admin WHERE Gmail = ?");
    $stmt->bind_param("s", $gmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['Password'])) {
        $_SESSION['admin'] = [
            'id' => $admin['ID'],
            'name' => $admin['Name'],
            'gmail' => $gmail,
            'role' => $admin['Role']
        ];

        // Log admin login activity
        $stmt = $conn->prepare("INSERT INTO admin_activity (activity_id, admin_id, action) VALUES (?, ?, ?)");
        $activity_id = uniqid('activity_');
        $action = 'Logged in';
        $stmt->bind_param("sss", $activity_id, $admin['ID'], $action);
        $stmt->execute();
        $stmt->close();

        header('Location: ../Views/adminProfile.php');
        exit;
    }

    $_SESSION['login_error'] = 'Invalid email or password.';
    header('Location: ../Views/home.php');
    exit;
}
?>