<?php
session_start();
require_once '../Database/db.php';

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    header('Location: ../Views/home.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = '';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/Logs/php_errors.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
        error_log("CSRF token validation failed.");
    }

    // Handle image upload
    if (empty($errors) && !empty($_FILES['profile_image']['tmp_name']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);
            finfo_close($finfo);
            error_log("Uploaded file MIME type: $mime, Name: " . $_FILES['profile_image']['name']);
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array(strtolower($mime), $allowed_mimes)) {
                $errors[] = "Invalid image format. Only JPG, PNG, GIF, or WebP allowed. Detected: $mime";
            } elseif ($_FILES['profile_image']['size'] > 1 * 1024 * 1024) {
                $errors[] = "Image must be under 1MB.";
            } else {
                $image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
                if ($image_data === false) {
                    $errors[] = "Failed to read image file.";
                    error_log("Failed to read image file: " . $_FILES['profile_image']['error']);
                } else {
                    $stmt = $conn->prepare("INSERT INTO userProfile (ID, ProfilePic) VALUES (?, ?) ON DUPLICATE KEY UPDATE ProfilePic = ?");
                    if (!$stmt) {
                        throw new Exception("Profile image update query failed: " . $conn->error);
                    }
                    $stmt->bind_param("iss", $user_id, $image_data, $image_data);
                    error_log("Image update params: user_id=$user_id");
                    if (!$stmt->execute()) {
                        throw new Exception("Image update execution failed: " . $stmt->error);
                    }
                    $stmt->close();

                    // Log activity
                    $stmt = $conn->prepare("INSERT INTO user_activity (User_ID, Action) VALUES (?, ?)");
                    if (!$stmt) {
                        throw new Exception("Activity insert query failed: " . $conn->error);
                    }
                    $action = "Updated profile image";
                    $stmt->bind_param("is", $user_id, $action);
                    error_log("Activity insert params: user_id=$user_id, action=$action");
                    if (!$stmt->execute()) {
                        throw new Exception("Activity insert execution failed: " . $stmt->error);
                    }
                    $stmt->close();

                    $success = "Profile image uploaded successfully.";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
            error_log("Database error in uploadImage.php: " . $e->getMessage());
        }
    } else {
        $errors[] = "No image selected.";
    }
}

$query = empty($errors) ? "success=" . urlencode($success) : "error=" . urlencode(implode(', ', $errors));
header("Location: ../Views/userProfile.php?$query");
exit;
?>