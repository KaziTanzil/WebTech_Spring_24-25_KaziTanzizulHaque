<?php
session_start();
require_once 'db_connect.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $user_id = trim($_POST['user_id'] ?? '');
    $setting = trim($_POST['setting'] ?? '');

    // Validate inputs
    if (empty($user_id) || empty($setting)) {
        die("Invalid form submission: All fields are required.");
    }

    // Check if user_id exists in user table
    $check_user = $conn->prepare("SELECT ID FROM user WHERE ID = ?");
    $check_user->bind_param("s", $user_id);
    $check_user->execute();
    if ($check_user->get_result()->num_rows == 0) {
        die("Invalid user ID.");
    }
    $check_user->close();

    // Insert into notification_settings
    try {
        $stmt = $conn->prepare("INSERT INTO notification_settings (user_id, setting) VALUES (?, ?)");
        $stmt->bind_param("ss", $user_id, $setting);
        if ($stmt->execute()) {
            echo "Notification settings saved successfully.";
        } else {
            echo "Error saving settings: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
    }
}

$conn->close();
?>