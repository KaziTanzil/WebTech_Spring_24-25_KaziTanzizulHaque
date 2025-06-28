<?php
session_start();
require_once '../Database/db.php';

// Prevent session fixation
session_regenerate_id(true);

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['login_error'] = 'Invalid CSRF token.';
        header('Location: ../Views/home.php');
        exit;
    }

    $gmail = filter_var(trim($_POST['gmail'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($gmail) || empty($password)) {
        $_SESSION['login_error'] = 'Email and password are required.';
        header('Location: ../Views/home.php');
        exit;
    }

    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = 'Invalid email format.';
        header('Location: ../Views/home.php');
        exit;
    }

    try {
        // Log input for debugging
        error_log("Login attempt: Email = '$gmail'");

        // Check user table first
        $stmt = $conn->prepare("SELECT ID, Name, Gender, PhoneNumber, Gmail, DoB, Password FROM user WHERE Gmail = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['login_error'] = 'Database error occurred.';
            header('Location: ../Views/home.php');
            exit;
        }
        $stmt->bind_param("s", $gmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            error_log("User found: ID = {$user['ID']}, Gmail = '{$user['Gmail']}', Password (plain) = '{$user['Password']}'");
            if ($password === $user['Password']) {
                error_log("User login successful: ID = {$user['ID']}");
                $_SESSION['user'] = [
                    'id' => $user['ID'],
                    'username' => $user['Name'],
                    'gender' => $user['Gender'],
                    'phone' => $user['PhoneNumber'],
                    'email' => $user['Gmail'],
                    'dob' => $user['DoB']
                ];
                header('Location: ../Views/userProfile.php');
                exit;
            } else {
                error_log("User password mismatch: Input = '$password', DB = '{$user['Password']}'");
            }
        } else {
            error_log("No user found for email: '$gmail'");
        }

        // Check admin table if user check fails
        $stmt = $conn->prepare("SELECT ID, Name, Gmail, Password, Role FROM admin WHERE Gmail = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $_SESSION['login_error'] = 'Database error occurred.';
            header('Location: ../Views/home.php');
            exit;
        }
        $stmt->bind_param("s", $gmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin) {
            error_log("Admin found: ID = {$admin['ID']}, Gmail = '{$admin['Gmail']}', Password (plain) = '{$admin['Password']}'");
            if ($password === $admin['Password']) {
                error_log("Admin login successful: ID = {$admin['ID']}");
                $_SESSION['admin'] = [
                    'id' => $admin['ID'],
                    'name' => $admin['Name'],
                    'gmail' => $admin['Gmail'],
                    'role' => $admin['Role']
                ];

                // Log admin login activity
                $stmt = $conn->prepare("INSERT INTO admin_activity (activity_id, admin_id, action) VALUES (?, ?, ?)");
                if (!$stmt) {
                    error_log("Prepare failed: " . $conn->error);
                    $_SESSION['login_error'] = 'Database error occurred.';
                    header('Location: ../Views/home.php');
                    exit;
                }
                $activity_id = uniqid('activity_');
                $action = 'Logged in';
                $stmt->bind_param("sis", $activity_id, $admin['ID'], $action);
                $stmt->execute();
                $stmt->close();

                header('Location: ../Views/adminProfile.php');
                exit;
            } else {
                error_log("Admin password mismatch: Input = '$password', DB = '{$admin['Password']}'");
            }
        } else {
            error_log("No admin found for email: '$gmail'");
        }

        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: ../Views/home.php');
        exit;
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['login_error'] = 'An error occurred. Please try again later.';
        header('Location: ../Views/home.php');
        exit;
    }
} else {
    header('Location: ../Views/home.php');
    exit;
}
?>