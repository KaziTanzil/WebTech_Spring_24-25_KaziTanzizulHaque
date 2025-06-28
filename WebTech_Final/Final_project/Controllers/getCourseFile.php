<?php
session_start();
require_once '../Database/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    error_log("getCourseFile.php: User not logged in");
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id) {
    error_log("getCourseFile.php: Invalid course ID");
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid course ID');
}

try {
    // Verify user has paid for the course
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments WHERE user_id = ? AND course_id = ? AND status = 'completed'");
    if (!$stmt) throw new Exception("Payment check query failed: " . $conn->error);
    $stmt->bind_param("ss", $_SESSION['user']['id'], $course_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($result['count'] == 0) {
        error_log("getCourseFile.php: User has not purchased course_id=$course_id");
        header('HTTP/1.1 403 Forbidden');
        exit('You have not purchased this course');
    }

    // Fetch file path
    $stmt = $conn->prepare("SELECT file_path FROM courses WHERE course_id = ?");
    if (!$stmt) throw new Exception("Course query failed: " . $conn->error);
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course || empty($course['file_path'])) {
        error_log("getCourseFile.php: File not found for course_id=$course_id");
        header('HTTP/1.1 404 Not Found');
        exit('File not found');
    }

    $file_path = $course['file_path'];
    $full_path = realpath("C:/xampp/htdocs/Final_project/uploads/Courses/" . $file_path);
    error_log("getCourseFile.php: Attempting to serve file: $full_path");

    if (!file_exists($full_path)) {
        error_log("getCourseFile.php: File does not exist: $full_path");
        header('HTTP/1.1 404 Not Found');
        exit('File does not exist');
    }

    // Serve the file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($full_path));
    readfile($full_path);
    error_log("getCourseFile.php: File served: $file_path");
    exit;
} catch (Exception $e) {
    error_log("getCourseFile.php: Error - " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error serving file');
}
?>