<?php
session_start();
require_once '../Database/db.php';

if (!isset($_SESSION['user']['id']) || empty(trim($_SESSION['user']['id'])) || !ctype_digit(trim($_SESSION['user']['id']))) {
    error_log("[" . date('Y-m-d H:i:s') . "] Invalid or missing user session, redirecting to home.php");
    header('Location: ../Views/home.php');
    exit;
}

$session_user_id = trim($_SESSION['user']['id']);
error_log("[" . date('Y-m-d H:i:s') . "] Session User ID: $session_user_id");
$errors = [];
$success = '';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/Logs/php_errors.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[" . date('Y-m-d H:i:s') . "] POST request received");
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
        error_log("[" . date('Y-m-d H:i:s') . "] CSRF token validation failed");
    }

    // Validate form UID
    $form_uid = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);
    if (empty($form_uid) || !ctype_digit($form_uid) || $form_uid !== $session_user_id) {
        $errors[] = "Invalid or mismatched user ID.";
        error_log("[" . date('Y-m-d H:i:s') . "] Form UID: " . var_export($form_uid, true) . ", Session User ID: $session_user_id, Validation failed");
    }

    $user_id = $session_user_id;
    $form_type = $_POST['submit'] ?? '';
    error_log("[" . date('Y-m-d H:i:s') . "] Form type: $form_type, User ID: $user_id");

    if (empty($errors)) {
        try {
            // Verify user exists
            $stmt = $conn->prepare("SELECT ID, Password, Name, DoB FROM user WHERE ID = ?");
            if (!$stmt) throw new Exception("User verification query failed: " . $conn->error);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result()->fetch_assoc();
            if (!$user_result) {
                throw new Exception("User ID $user_id not found in user table.");
            }
            $stmt->close();

            if ($form_type === 'personal') {
                error_log("[" . date('Y-m-d H:i:s') . "] Processing personal form");
                $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING) ?: '';
                $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?: '';
                $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);

                if (strlen($bio) > 250) {
                    $errors[] = "Bio must be 250 characters or less.";
                }
                if (!preg_match('/^[A-Za-z\s]+$/', $username)) {
                    $errors[] = "Name can only contain letters and spaces.";
                }
                if (strlen($username) > 100) {
                    $errors[] = "Name must be 100 characters or less.";
                }
                if (empty($dob)) {
                    $errors[] = "Date of Birth is required.";
                } elseif ($dob) {
                    $dob_date = new DateTime($dob);
                    $min_date = new DateTime();
                    $min_date->modify('-10 years');
                    if ($dob_date > $min_date) {
                        $errors[] = "You must be at least 10 years old.";
                    }
                }

                $image_data = null;
                if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    error_log("[" . date('Y-m-d H:i:s') . "] Image upload: Name=" . $_FILES['profile_image']['name'] . ", Size=" . $_FILES['profile_image']['size'] . ", Error=" . $_FILES['profile_image']['error']);
                    switch ($_FILES['profile_image']['error']) {
                        case UPLOAD_ERR_OK:
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mime = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);
                            finfo_close($finfo);
                            error_log("[" . date('Y-m-d H:i:s') . "] MIME type: $mime");
                            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                            if (!in_array(strtolower($mime), $allowed_mimes)) {
                                $errors[] = "Invalid image format. Only JPG, PNG, GIF, or WebP allowed.";
                            } elseif ($_FILES['profile_image']['size'] > 1 * 1024 * 1024) {
                                $errors[] = "Image must be under 1MB.";
                            } else {
                                $image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
                                if ($image_data === false) {
                                    $errors[] = "Failed to read image file.";
                                    error_log("[" . date('Y-m-d H:i:s') . "] Failed to read image file");
                                }
                            }
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errors[] = "Image file is too large.";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errors[] = "Image file was only partially uploaded.";
                            break;
                        default:
                            $errors[] = "Image upload failed with error code: " . $_FILES['profile_image']['error'];
                            break;
                    }
                }

                if (empty($errors)) {
                    if ($image_data === null && $bio === '' && $username === $user_result['Name'] && $dob === $user_result['DoB']) {
                        $success = "No changes to save.";
                    } else {
                        // Update user table for name and dob
                        if ($username !== $user_result['Name'] || $dob !== $user_result['DoB']) {
                            $stmt = $conn->prepare("UPDATE user SET Name = ?, DoB = ? WHERE ID = ?");
                            if (!$stmt) throw new Exception("User update query failed: " . $conn->error);
                            $stmt->bind_param("sss", $username, $dob, $user_id);
                            if (!$stmt->execute()) throw new Exception("User update execution failed: " . $stmt->error);
                            $stmt->close();
                        }

                        // Update or insert profile data
                        $stmt = $conn->prepare("SELECT ID FROM userProfile WHERE ID = ?");
                        if (!$stmt) throw new Exception("Profile existence query failed: " . $conn->error);
                        $stmt->bind_param("s", $user_id);
                        if (!$stmt->execute()) throw new Exception("Profile existence query execution failed: " . $stmt->error);
                        $exists = $stmt->get_result()->num_rows > 0;
                        $stmt->close();

                        if ($exists) {
                            $query = "UPDATE userProfile SET";
                            $params = [];
                            $types = "";
                            $bind_params = [];

                            if ($image_data !== null) {
                                $query .= " ProfilePic = ?,";
                                $types .= "s";
                                $bind_params[] = $image_data;
                            }
                            if ($bio !== '') {
                                $query .= " Bio = ?,";
                                $types .= "s";
                                $bind_params[] = $bio;
                            }
                            $query = rtrim($query, ",") . " WHERE ID = ?";
                            $types .= "s";
                            $bind_params[] = $user_id;

                            if ($image_data !== null || $bio !== '') {
                                $stmt = $conn->prepare($query);
                                if (!$stmt) throw new Exception("Profile update query failed: " . $conn->error);
                                $stmt->bind_param($types, ...$bind_params);
                                error_log("[" . date('Y-m-d H:i:s') . "] Profile update query: $query");
                                if (!$stmt->execute()) throw new Exception("Profile update execution failed: " . $stmt->error);
                                $stmt->close();
                            }
                        } else {
                            $query = "INSERT INTO userProfile (ID, ProfilePic, Bio) VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($query);
                            if (!$stmt) throw new Exception("Profile insert query failed: " . $conn->error);
                            $null = null;
                            $stmt->bind_param("sss", $user_id, $image_data ?? $null, $bio);
                            error_log("[" . date('Y-m-d H:i:s') . "] Profile insert params: user_id=$user_id, bio=$bio, has_image=" . ($image_data ? 'Yes' : 'No'));
                            if (!$stmt->execute()) throw new Exception("Profile insert execution failed: " . $stmt->error);
                            $stmt->close();
                        }

                        $success = "Profile updated successfully";
                        error_log("[" . date('Y-m-d H:i:s') . "] Personal form processed successfully");
                    }
                }
            } elseif ($form_type === 'change-password') {
                error_log("[" . date('Y-m-d H:i:s') . "] Processing change password form");
                $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
                $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
                $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

                if (!$current_password || !$new_password || !$confirm_password) {
                    $errors[] = "All password fields are required.";
                } elseif (!password_verify($current_password, $user_result['Password'])) {
                    $errors[] = "Current password is incorrect.";
                } elseif (strlen($new_password) < 8) {
                    $errors[] = "New password must be at least 8 characters.";
                } elseif ($new_password !== $confirm_password) {
                    $errors[] = "New passwords do not match.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE user SET Password = ? WHERE ID = ?");
                    if (!$stmt) throw new Exception("Password update query failed: " . $conn->error);
                    $stmt->bind_param("ss", $hashed_password, $user_id);
                    if (!$stmt->execute()) throw new Exception("Password update execution failed: " . $stmt->error);
                    $stmt->close();
                    $success = "Password changed successfully.";
                    error_log("[" . date('Y-m-d H:i:s') . "] Password changed successfully for user_id: $user_id");
                }
            } else {
                $errors[] = "Invalid form submission.";
                error_log("[" . date('Y-m-d H:i:s') . "] Invalid form_type: $form_type");
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . htmlspecialchars($e->getMessage());
            error_log("[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage());
        }
    }

    // Redirect with success/error
    $query = empty($errors) ? "success=" . urlencode($success) : "error=" . urlencode(implode(', ', $errors));
    error_log("[" . date('Y-m-d H:i:s') . "] Redirecting to userProfile.php with query: $query");
    header("Location: ../Views/userProfile.php?$query");
    exit;
} else {
    error_log("[" . date('Y-m-d H:i:s') . "] Non-POST request, redirecting to userProfile.php");
    header("Location: ../Views/userProfile.php");
    exit;
}
?>