<?php
session_start();
require_once '../Database/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: home.php');
    exit;
}

$admin = $_SESSION['admin'];

// Check database connection
if ($conn->connect_error) {
    $_SESSION['error'] = 'Database connection failed: ' . $conn->connect_error;
    header('Location: adminProfile.php');
    exit;
}

// Helper function to log admin activity
function logAdminActivity($conn, $admin_id, $action) {
    $stmt = $conn->prepare("INSERT INTO admin_activity (activity_id, admin_id, action) VALUES (?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare admin activity log: ' . $conn->error;
        return;
    }
    $activity_id = uniqid('activity_');
    $stmt->bind_param("sss", $activity_id, $admin_id, $action);
    $stmt->execute();
    $stmt->close();
}

// Mock sendNotification function
function sendNotification($recipient_email, $subject, $message, $type) {
    error_log("Sending $type notification to $recipient_email: $subject - $message");
    return true;
}

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: adminProfile.php');
        exit;
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate CSRF token
}

// Course Management: Delete Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM payments WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM enrollments WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM quizzes WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM quiz_scores WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM course_downloads WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT file_path FROM courses WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $file_path = '../Uploads/Courses/' . $row['file_path'];
            if ($row['file_path'] && file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->bind_param("s", $course_id);
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = 'Course and related data deleted successfully.';
            logAdminActivity($conn, $admin['id'], "Deleted course ID: $course_id");
        } else {
            throw new Exception('Failed to delete course: ' . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Failed to delete course: ' . $e->getMessage();
    }
    header('Location: adminProfile.php#course-management');
    exit;
}

// Course Management: Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_id = uniqid('course_');
    $course_name = filter_var($_POST['course_name'], FILTER_SANITIZE_STRING);
    $lecture_list = filter_var($_POST['lecture_list'], FILTER_SANITIZE_STRING);
    $lecture_descriptions = filter_var($_POST['lecture_descriptions'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $duration = (int)$_POST['duration'];
    $level = in_array($_POST['level'], ['beginner', 'intermediate', 'advanced']) ? $_POST['level'] : 'beginner';
    $cost = (float)$_POST['cost'];
    $file_path = '';

    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/Courses/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = uniqid('course_file_') . '_' . basename($_FILES['course_file']['name']);
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['course_file']['tmp_name'], $file_path)) {
            $file_path = $file_name;
        } else {
            $_SESSION['error'] = 'Failed to upload course file.';
            $file_path = '';
        }
    }

    $stmt = $conn->prepare("INSERT INTO courses (course_id, course_name, lecture_list, lecture_descriptions, category, duration, level, cost, file_path, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare add course query: ' . $conn->error;
        header('Location: adminProfile.php#course-management');
        exit;
    }
    $stmt->bind_param("sssssisdss", $course_id, $course_name, $lecture_list, $lecture_descriptions, $category, $duration, $level, $cost, $file_path, $admin['id']);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course added successfully.';
        logAdminActivity($conn, $admin['id'], "Added course: $course_name");
    } else {
        $_SESSION['error'] = 'Failed to add course: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#course-management');
    exit;
}

// Course Management: Update Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = filter_var($_POST['course_name'], FILTER_SANITIZE_STRING);
    $lecture_list = filter_var($_POST['lecture_list'], FILTER_SANITIZE_STRING);
    $lecture_descriptions = filter_var($_POST['lecture_descriptions'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $duration = (int)$_POST['duration'];
    $level = in_array($_POST['level'], ['beginner', 'intermediate', 'advanced']) ? $_POST['level'] : 'beginner';
    $cost = (float)$_POST['cost'];

    $stmt = $conn->prepare("SELECT file_path FROM courses WHERE course_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare select course query: ' . $conn->error;
        header('Location: adminProfile.php#course-management');
        exit;
    }
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_file = $result->fetch_assoc()['file_path'] ?? '';
    $stmt->close();

    $file_path = $existing_file;
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/Courses/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = uniqid('course_file_') . '_' . basename($_FILES['course_file']['name']);
        $new_file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['course_file']['tmp_name'], $new_file_path)) {
            if ($existing_file && file_exists('../Uploads/Courses/' . $existing_file)) {
                unlink('../Uploads/Courses/' . $existing_file);
            }
            $file_path = $file_name;
        } else {
            $_SESSION['error'] = 'Failed to upload new course file.';
        }
    }

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, lecture_list = ?, lecture_descriptions = ?, category = ?, duration = ?, level = ?, cost = ?, file_path = ? WHERE course_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare update course query: ' . $conn->error;
        header('Location: adminProfile.php#course-management');
        exit;
    }
    $stmt->bind_param("ssssisdss", $course_name, $lecture_list, $lecture_descriptions, $category, $duration, $level, $cost, $file_path, $course_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Course updated successfully.';
        logAdminActivity($conn, $admin['id'], "Updated course ID: $course_id");
    } else {
        $_SESSION['error'] = 'Failed to update course: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#course-management');
    exit;
}

// Enrollment Management: Enroll User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_user'])) {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];
    $enrollment_id = uniqid('enrollment_');
    $stmt = $conn->prepare("INSERT INTO enrollments (enrollment_id, user_id, course_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare enroll user query: ' . $conn->error;
        header('Location: adminProfile.php#enrollment-management');
        exit;
    }
    $stmt->bind_param("sss", $enrollment_id, $user_id, $course_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'User enrolled successfully.';
        logAdminActivity($conn, $admin['id'], "Enrolled user ID: $user_id in course ID: $course_id");
    } else {
        $_SESSION['error'] = 'Failed to enroll user: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#enrollment-management');
    exit;
}

// Enrollment Management: Delete Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enrollment'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE enrollment_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare delete enrollment query: ' . $conn->error;
        header('Location: adminProfile.php#enrollment-management');
        exit;
    }
    $stmt->bind_param("s", $enrollment_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Enrollment deleted successfully.';
        logAdminActivity($conn, $admin['id'], "Deleted enrollment ID: $enrollment_id");
    } else {
        $_SESSION['error'] = 'Failed to delete enrollment: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#enrollment-management');
    exit;
}

// Quiz Management: Add Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quiz'])) {
    $quiz_id = uniqid('quiz_');
    $quiz_name = filter_var($_POST['quiz_name'], FILTER_SANITIZE_STRING);
    $course_id = filter_var($_POST['course_id'], FILTER_SANITIZE_STRING);
    $pass_mark = (int)$_POST['pass_mark'];
    $form_url = filter_var($_POST['form_url'] ?? 'https://default-form-url.com', FILTER_SANITIZE_URL);

    $stmt = $conn->prepare("INSERT INTO quizzes (quiz_id, quiz_name, course_id, pass_mark, created_by, form_url) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare add quiz query: ' . $conn->error;
        header('Location: adminProfile.php#quiz-management');
        exit;
    }
    $stmt->bind_param("sssiss", $quiz_id, $quiz_name, $course_id, $pass_mark, $admin['id'], $form_url);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Quiz added successfully.';
        logAdminActivity($conn, $admin['id'], "Added quiz: $quiz_name");
    } else {
        $_SESSION['error'] = 'Failed to add quiz: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#quiz-management');
    exit;
}

// Delete Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quiz'])) {
    $quiz_id = $_POST['quiz_id'];
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM quiz_submissions WHERE quiz_id = ?");
        $stmt->bind_param("s", $quiz_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare delete quiz query: ' . $conn->error);
        }
        $stmt->bind_param("s", $quiz_id);
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = 'Quiz deleted successfully.';
            logAdminActivity($conn, $admin['id'], "Deleted quiz ID: $quiz_id");
        } else {
            throw new Exception('Failed to delete quiz: ' . $stmt->error);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Failed to delete quiz: ' . $e->getMessage();
    }
    header('Location: adminProfile.php#quiz-management');
    exit;
}

// Quiz Marks: Upload and Process CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_quiz_scores'])) {
    $quiz_name = filter_var($_POST['quiz_name'], FILTER_SANITIZE_STRING);
    $course_id = filter_var($_POST['course_id'], FILTER_SANITIZE_STRING);

    // Validate course_id exists
    $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare course query: ' . $conn->error;
        header('Location: adminProfile.php#quiz-marks');
        exit;
    }
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$course) {
        $_SESSION['error'] = 'Invalid course selected.';
        header('Location: adminProfile.php#quiz-marks');
        exit;
    }
    $course_name = $course['course_name'];

    if (isset($_FILES['quiz_scores_file']) && $_FILES['quiz_scores_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['quiz_scores_file']['tmp_name'];
        $file_name = $_FILES['quiz_scores_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file extension
        if ($file_ext !== 'csv') {
            $_SESSION['error'] = 'Invalid file format. Please upload a .csv file.';
            header('Location: adminProfile.php#quiz-marks');
            exit;
        }

        if (($handle = fopen($file, 'r')) !== false) {
            $conn->begin_transaction();
            $header = fgetcsv($handle); // Skip header row
            $row_count = 0;
            $error_messages = [];

            while (($row = fgetcsv($handle)) !== false) {
                // Debug: Log raw row data
                error_log("Row " . ($row_count + 2) . ": " . print_r($row, true));

                // Ensure enough columns
                if (count($row) < 7) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Insufficient columns (found " . count($row) . ", expected at least 7).";
                    continue;
                }

                // Map CSV columns
                $email = filter_var($row[1], FILTER_SANITIZE_EMAIL); // Username (email)
                $score = (float)str_replace(' / 10', '', $row[2]); // Total score
                $user_name = filter_var($row[3], FILTER_SANITIZE_STRING); // Name
                $user_id = filter_var($row[6], FILTER_SANITIZE_STRING); // uID

                // Debug: Log mapped data
                error_log("Row " . ($row_count + 2) . ": email=$email, score=$score, user_name=$user_name, user_id=$user_id");

                // Validate data
                if (empty($email) || empty($user_name) || empty($user_id) || !is_numeric($score)) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Missing or invalid data (Email: $email, Name: $user_name, UID: $user_id, Score: $score).";
                    continue;
                }

                // Validate user_id
                $stmt = $conn->prepare("SELECT Name FROM user WHERE ID = ?");
                if (!$stmt) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Failed to prepare user query: " . $conn->error;
                    continue;
                }
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$user) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Invalid user_id: $user_id.";
                    continue;
                }

                // Validate enrollment
                $stmt = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE user_id = ? AND course_id = ?");
                if (!$stmt) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Failed to prepare enrollment query: " . $conn->error;
                    continue;
                }
                $stmt->bind_param("ss", $user_id, $course_id);
                $stmt->execute();
                $enrollment = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$enrollment) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": User $user_id not enrolled in course $course_id.";
                    continue;
                }

                // Insert or update quiz score
                $stmt = $conn->prepare("INSERT INTO quiz_scores (user_id, user_name, score, quiz_name, course_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE score = ?, user_name = ?");
                if (!$stmt) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Failed to prepare insert query: " . $conn->error;
                    continue;
                }
                $stmt->bind_param("ssissss", $user_id, $user_name, $score, $quiz_name, $course_id, $score, $user_name);
                if (!$stmt->execute()) {
                    $error_messages[] = "Row " . ($row_count + 2) . ": Failed to save score for user $user_id: " . $stmt->error;
                    $stmt->close();
                    continue;
                }
                $stmt->close();
                $row_count++;
            }

            fclose($handle);
            $conn->commit();
            if ($row_count > 0) {
                $_SESSION['success'] = "Uploaded $row_count quiz score(s) successfully from csv file.";
                logAdminActivity($conn, $admin['id'], "Uploaded $row_count quiz scores for quiz: $quiz_name, course: $course_name from csv file");
            } else {
                $_SESSION['error'] = "No quiz scores were saved. Check file data and database constraints.";
                if (!empty($error_messages)) {
                    $_SESSION['error'] .= " Errors: " . implode(' | ', $error_messages);
                }
            }
        } else {
            $_SESSION['error'] = 'Failed to read CSV file.';
        }
    } else {
        $_SESSION['error'] = 'No file uploaded or upload error: ' . ($_FILES['quiz_scores_file']['error'] ?? 'Unknown error');
    }
    header('Location: adminProfile.php#quiz-marks');
    exit;
}
// Quiz Marks: Update Score
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quiz_score'])) {
    $score_id = $_POST['score_id'];
    $score = (int)$_POST['score'];
    $stmt = $conn->prepare("UPDATE quiz_scores SET score = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare update score query: ' . $conn->error;
        header('Location: adminProfile.php#quiz-marks');
        exit;
    }
    $stmt->bind_param("ii", $score, $score_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Quiz score updated successfully.';
        logAdminActivity($conn, $admin['id'], "Updated quiz score ID: $score_id");
    } else {
        $_SESSION['error'] = 'Failed to update quiz score: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#quiz-marks');
    exit;
}

// Notification Management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $recipient_email = filter_var($_POST['recipient_email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
    $type = $_POST['type'] === 'sms' ? 'sms' : 'email';
    $notification_id = uniqid('notification_');

    $stmt = $conn->prepare("INSERT INTO notifications (notification_id, recipient_email, subject, message, type) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Failed to prepare notification query: ' . $conn->error;
        header('Location: adminProfile.php#notifications');
        exit;
    }
    $stmt->bind_param("sssss", $notification_id, $recipient_email, $subject, $message, $type);
    if ($stmt->execute()) {
        if (sendNotification($recipient_email, $subject, $message, $type)) {
            $_SESSION['success'] = "$type notification queued and logged successfully.";
            logAdminActivity($conn, $admin['id'], "Queued $type notification ID: $notification_id");
        } else {
            $_SESSION['error'] = "Failed to send $type notification.";
        }
    } else {
        $_SESSION['error'] = 'Failed to queue notification: ' . $stmt->error;
    }
    $stmt->close();
    header('Location: adminProfile.php#notifications');
    exit;
}

// Fetch data for display
$users = [];
$courses = [];
$enrollments = [];
$quiz_submissions = [];
$quiz_scores = [];

$result = $conn->query("SELECT ID, Name, Gmail, PhoneNumber FROM user");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $_SESSION['error'] = 'Failed to fetch users: ' . $conn->error;
}

$result = $conn->query("SELECT course_id, course_name, lecture_list, lecture_descriptions, category, duration, level, cost, file_path FROM courses");
if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $_SESSION['error'] = 'Failed to fetch courses: ' . $conn->error;
}

$result = $conn->query("SELECT e.enrollment_id, e.user_id, e.course_id, e.progress, u.Name AS user_name, c.course_name FROM enrollments e JOIN user u ON e.user_id = u.ID JOIN courses c ON e.course_id = c.course_id");
if ($result) {
    $enrollments = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $_SESSION['error'] = 'Failed to fetch enrollments: ' . $conn->error;
}

$result = $conn->query("SELECT s.submission_id, s.user_id, s.quiz_id, s.score, u.Name AS user_name, q.quiz_name FROM quiz_submissions s JOIN user u ON s.user_id = u.ID JOIN quizzes q ON s.quiz_id = q.quiz_id");
if ($result) {
    $quiz_submissions = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $_SESSION['error'] = 'Failed to fetch quiz submissions: ' . $conn->error;
}

$score_search = isset($_GET['score_search']) ? filter_var($_GET['score_search'], FILTER_SANITIZE_STRING) : '';
$query = $score_search ? 
    "SELECT qs.id, qs.user_id, qs.user_name, qs.score, qs.quiz_name, qs.course_id, c.course_name 
     FROM quiz_scores qs 
     JOIN courses c ON qs.course_id = c.course_id 
     WHERE qs.user_name LIKE ? OR qs.user_id LIKE ? OR qs.quiz_name LIKE ? OR c.course_name LIKE ?" :
    "SELECT qs.id, qs.user_id, qs.user_name, qs.score, qs.quiz_name, qs.course_id, c.course_name 
     FROM quiz_scores qs 
     JOIN courses c ON qs.course_id = c.course_id";
$stmt = $conn->prepare($query);
if ($score_search) {
    $search_term = "%" . $score_search . "%";
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
}
$stmt->execute();
$quiz_scores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$analytics = [
    'signups' => $conn->query("SELECT COUNT(*) AS count FROM user")->fetch_assoc()['count'] ?? 0,
    'course_popularity' => $conn->query("SELECT c.course_name, COUNT(e.enrollment_id) AS enrollments FROM courses c LEFT JOIN enrollments e ON c.course_id = e.course_id GROUP BY c.course_id ORDER BY enrollments DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC) ?? [],
    'revenue' => $conn->query("SELECT SUM(p.amount) AS total FROM payments p WHERE p.status = 'completed'")->fetch_assoc()['total'] ?? 0,
    'course_revenue' => $conn->query("SELECT c.course_name, SUM(p.amount) AS revenue FROM payments p JOIN courses c ON p.course_id = c.course_id WHERE p.status = 'completed' GROUP BY c.course_id ORDER BY revenue DESC")->fetch_all(MYSQLI_ASSOC) ?? [],
];

$stmt = $conn->prepare("SELECT SUM(amount) AS total FROM admin_revenue WHERE admin_id = ?");
if (!$stmt) {
    $_SESSION['error'] = 'Failed to prepare admin revenue query: ' . $conn->error;
    $analytics['admin_revenue'] = 0;
} else {
    $stmt->bind_param("s", $admin['id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $analytics['admin_revenue'] = $result->fetch_assoc()['total'] ?? 0;
    } else {
        $_SESSION['error'] = 'Failed to execute admin revenue query: ' . $stmt->error;
        $analytics['admin_revenue'] = 0;
    }
    $stmt->close();
}

// Fetch selected course for update form
$selected_course = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_course'])) {
    $course_id = $_POST['course_id'];
    $stmt = $conn->prepare("SELECT course_id, course_name, lecture_list, lecture_descriptions, category, duration, level, cost, file_path FROM courses WHERE course_id = ?");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $selected_course = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif (isset($_GET['selected_course_id'])) {
    $course_id = filter_var($_GET['selected_course_id'], FILTER_SANITIZE_STRING);
    $stmt = $conn->prepare("SELECT course_id, course_name, lecture_list, lecture_descriptions, category, duration, level, cost, file_path FROM courses WHERE course_id = ?");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $selected_course = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERet7yg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .section-content {
            display: none;
        }
        .section-content.active {
            display: block;
        }
        .table-row:hover {
            background-color: #f0f9ff;
            transition: background-color 0.3s;
        }
        .notification {
            transition: opacity 0.5s ease-in-out;
        }
        .notification.hidden {
            opacity: 0;
            display: none;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 400px;
            width: 100%;
            max-width: 800px;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-indigo-800 text-white sidebar transform md:transform-none">
        <div class="p-4 text-2xl font-bold">Admin Dashboard</div>
        <nav class="mt-4">
            <a href="#course-management" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="course-management"><i class="fas fa-book mr-2"></i>Course Management</a>
            <a href="#enrollment-management" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="enrollment-management"><i class="fas fa-user-plus mr-2"></i>Enrollment Management</a>
            <a href="#quiz-management" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="quiz-management"><i class="fas fa-question-circle mr-2"></i>Quiz Management</a>
            <a href="#quiz-marks" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="quiz-marks"><i class="fas fa-clipboard-check mr-2"></i>Quiz Marks</a>
            <a href="#analytics" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="analytics"><i class="fas fa-chart-bar mr-2"></i>Analytics</a>
            <a href="#notifications" class="block px-4 py-2 hover:bg-indigo-600 section-link" data-section="notifications"><i class="fas fa-bell mr-2"></i>Notifications</a>
            <a href="../Controllers/logout.php" class="block px-4 py-2 hover:bg-indigo-600"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($admin['name']); ?></h1>
            <button class="md:hidden text-indigo-600" onclick="toggleSidebar()"><i class="fas fa-bars text-2xl"></i></button>
        </div>

        <!-- Notifications -->
        <?php if (isset($_SESSION['success'])): ?>
        <div id="success-notification" class="notification bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 fade-in">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
        <div id="error-notification" class="notification bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 fade-in">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Course Management -->
        <div id="course-management" class="section-content bg-white p-6 rounded-lg shadow-md mb-6 active">
            <h2 class="text-2xl font-semibold mb-4">Course Management</h2>
            <!-- Add Course -->
            <h3 class="text-xl font-semibold mb-4">Add New Course</h3>
            <form action="adminProfile.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="hidden" name="add_course" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="course_name" class="block text-sm font-medium">Course Name</label>
                    <input type="text" id="course_name" name="course_name" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="lecture_list" class="block text-sm font-medium">Lecture List (comma-separated)</label>
                    <input type="text" id="lecture_list" name="lecture_list" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="lecture_descriptions" class="block text-sm font-medium">Lecture Descriptions (comma-separated)</label>
                    <input type="text" id="lecture_descriptions" name="lecture_descriptions" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium">Category</label>
                    <input type="text" id="category" name="category" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="duration" class="block text-sm font-medium">Duration (hours)</label>
                    <input type="number" id="duration" name="duration" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium">Level</label>
                    <select id="level" name="level" class="w-full p-2 border rounded">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div>
                    <label for="cost" class="block text-sm font-medium">Cost ($)</label>
                    <input type="number" id="cost" name="cost" step="0.01" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="course_file" class="block text-sm font-medium">Course File</label>
                    <input type="file" id="course_file" name="course_file" accept=".pdf,.zip,.docx" class="w-full p-2 border rounded">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Course</button>
                </div>
            </form>

            <!-- Modify Course -->
            <h3 class="text-xl font-semibold mb-4">Modify Existing Course</h3>
            <!-- Search Course -->
            <h4 class="text-lg font-medium mb-2">Search Courses</h4>
            <form action="adminProfile.php" method="GET" class="mb-4">
                <input type="hidden" name="section" value="<?php echo htmlspecialchars(isset($_GET['section']) ? $_GET['section'] : 'course-management'); ?>">
                <?php if ($selected_course): ?>
                    <input type="hidden" name="selected_course_id" value="<?php echo htmlspecialchars($selected_course['course_id']); ?>">
                <?php endif; ?>
                <div class="flex">
                    <input type="text" name="course_search" placeholder="Search by course name or category" value="<?php echo htmlspecialchars($course_search ?? ''); ?>" class="w-full p-2 border rounded-l-md">
                    <button type="submit" class="bg-indigo-600 text-white p-2 rounded-r-md hover:bg-indigo-700">Search</button>
                </div>
            </form>
            <!-- Course List with Select Button -->
            <h4 class="text-lg font-medium mb-2">Select Course to Modify or Delete</h4>
            <div class="overflow-x-auto mb-6">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Category</th>
                            <th class="p-3">Cost</th>
                            <th class="p-3">File</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $course_search = isset($_GET['course_search']) ? filter_var($_GET['course_search'], FILTER_SANITIZE_STRING) : '';
                        $query = $course_search ? "SELECT course_id, course_name, category, cost, file_path FROM courses WHERE course_name LIKE ? OR category LIKE ?" : "SELECT course_id, course_name, category, cost, file_path FROM courses";
                        $stmt = $conn->prepare($query);
                        if ($course_search) {
                            $search_term = "%" . $course_search . "%";
                            $stmt->bind_param("ss", $search_term, $search_term);
                        }
                        $stmt->execute();
                        $courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();
                        if (empty($courses)) {
                            echo '<tr><td colspan="6" class="p-3 text-center">No courses found.</td></tr>';
                        } else {
                            foreach ($courses as $course):
                        ?>
                        <tr class="table-row">
                            <td class="p-3"><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($course['category']); ?></td>
                            <td class="p-3">$<?php echo number_format($course['cost'], 2); ?></td>
                            <td class="p-3"><?php echo $course['file_path'] ? htmlspecialchars(basename($course['file_path'])) : 'No file'; ?></td>
                            <td class="p-3">
                                <form action="adminProfile.php" method="POST" class="inline">
                                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                    <input type="hidden" name="select_course" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Select</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; } ?>
                    </tbody>
                </table>
            </div>

            <!-- Update/Delete Course Form -->
            <?php if ($selected_course): ?>
            <h4 class="text-lg font-medium mb-2">Update Course: <?php echo htmlspecialchars($selected_course['course_name']); ?></h4>
            <form action="adminProfile.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" id="update-course-form">
                <input type="hidden" name="update_course" value="1">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($selected_course['course_id']); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="update_course_name" class="block text-sm font-medium">Course Name</label>
                    <input type="text" id="update_course_name" name="course_name" value="<?php echo htmlspecialchars($selected_course['course_name']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_lecture_list" class="block text-sm font-medium">Lecture List (comma-separated)</label>
                    <input type="text" id="update_lecture_list" name="lecture_list" value="<?php echo htmlspecialchars($selected_course['lecture_list']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_lecture_descriptions" class="block text-sm font-medium">Lecture Descriptions (comma-separated)</label>
                    <input type="text" id="update_lecture_descriptions" name="lecture_descriptions" value="<?php echo htmlspecialchars($selected_course['lecture_descriptions']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_category" class="block text-sm font-medium">Category</label>
                    <input type="text" id="update_category" name="category" value="<?php echo htmlspecialchars($selected_course['category']); ?>" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_duration" class="block text-sm font-medium">Duration (hours)</label>
                    <input type="number" id="update_duration" name="duration" value="<?php echo htmlspecialchars($selected_course['duration']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_level" class="block text-sm font-medium">Level</label>
                    <select id="update_level" name="level" class="w-full p-2 border rounded">
                        <option value="beginner" <?php echo $selected_course['level'] === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="intermediate" <?php echo $selected_course['level'] === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="advanced" <?php echo $selected_course['level'] === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
                <div>
                    <label for="update_cost" class="block text-sm font-medium">Cost ($)</label>
                    <input type="number" id="update_cost" name="cost" step="0.01" value="<?php echo htmlspecialchars($selected_course['cost']); ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="update_course_file" class="block text-sm font-medium">Course File (Current: <?php echo $selected_course['file_path'] ? htmlspecialchars(basename($selected_course['file_path'])) : 'None'; ?>)</label>
                    <input type="file" id="update_course_file" name="course_file" accept=".pdf,.zip,.docx" class="w-full p-2 border rounded">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update Course</button>
                </div>
            </form>
            <h4 class="text-lg font-medium mb-2">Delete Course: <?php echo htmlspecialchars($selected_course['course_name']); ?></h4>
            <form action="adminProfile.php" method="POST" class="mb-6" id="delete-course-form">
                <input type="hidden" name="delete_course" value="1">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($selected_course['course_id']); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete Course</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Enrollment Management -->
        <div id="enrollment-management" class="section-content bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-semibold mb-4">Enroll User in Course</h2>
            <form action="adminProfile.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="hidden" name="enroll_user" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="user_id" class="block text-sm font-medium">User</label>
                    <select id="user_id" name="user_id" required class="w-full p-2 border rounded">
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['ID']); ?>">
                            <?php echo htmlspecialchars($user['Name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="course_id" class="block text-sm font-medium">Course</label>
                    <select id="course_id" name="course_id" required class="w-full p-2 border rounded">
                        <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Enroll</button>
                </div>
            </form>
            <h3 class="text-xl font-semibold mb-4">Enrollments</h3>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3">User</th>
                            <th class="p-3">Course</th>
                            <th class="p-3">Progress</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                        <tr class="table-row">
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['user_name']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($enrollment['progress']); ?>%</td>
                            <td class="p-3">
                                <form action="adminProfile.php" method="POST" class="inline">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['enrollment_id']; ?>">
                                    <input type="hidden" name="delete_enrollment" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quiz Management -->
        <div id="quiz-management" class="section-content bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-semibold mb-4">Quiz Management</h2>
            <form action="adminProfile.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="hidden" name="add_quiz" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="quiz_name" class="block text-sm font-medium">Quiz Name</label>
                    <input type="text" id="quiz_name" name="quiz_name" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="course_id" class="block text-sm font-medium">Associated Course</label>
                    <select id="course_id" name="course_id" required class="w-full p-2 border rounded">
                        <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="pass_mark" class="block text-sm font-medium">Pass Mark (%)</label>
                    <input type="number" id="pass_mark" name="pass_mark" value="50" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="form_url" class="block text-sm font-medium">Form URL</label>
                    <input type="url" id="form_url" name="form_url" value="https://default-form-url.com" required class="w-full p-2 border rounded">
                </div>
                <div class="inline">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Quiz</button>
                </div>
            </form>
            <h3 class="text-xl font-semibold mb-4">Existing Quizzes</h3>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT quiz_id, quiz_name FROM quizzes");
                        $quizzes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                        if (!$result) {
                            $_SESSION['error'] = 'Failed to fetch quizzes: ' . $conn->error;
                        }
                        foreach ($quizzes as $quiz): ?>
                        <tr class="table-row">
                            <td class="p-3"><?php echo htmlspecialchars($quiz['quiz_id']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($quiz['quiz_name']); ?></td>
                            <td class="p-3">
                                <form action="adminProfile.php" method="POST" class="inline">
                                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
                                    <input type="hidden" name="delete_quiz" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </div>

        <!-- Quiz Marks -->
        <div id="quiz-marks" class="section-content bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-semibold mb-4">Quiz Marks</h2>
            <!-- Upload Quiz Scores -->
            <h3 class="text-xl font-semibold mb-4">Upload Quiz Scores</h3>
            <form action="adminProfile.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="hidden" name="upload_quiz_scores" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="quiz_name_scores" class="block text-sm font-medium">Quiz Name</label>
                    <input type="text" id="quiz_name_scores" name="quiz_name" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="course_id_scores" class="block text-sm font-medium">Associated Course</label>
                    <select id="course_id_scores" name="course_id" required class="w-full p-2 border rounded">
                        <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="quiz_scores_file" class="block text-sm font-medium">Upload CSV File</label>
                    <input type="file" id="quiz_scores_file" name="quiz_scores_file" accept=".csv" required class="w-full p-2 border rounded">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Upload Scores</button>
                </div>
            </form>
            <!-- Search Quiz Scores -->
            <h3 class="text-xl font-semibold mb-4">Search Quiz Scores</h3>
            <form action="adminProfile.php" method="GET" class="mb-4">
                <input type="hidden" name="section" value="quiz-marks">
                <div class="flex">
                    <input type="text" name="score_search" placeholder="Search by Name, UID, Quiz Name, or Course" value="<?php echo htmlspecialchars($score_search ?? ''); ?>" class="w-full p-2 border rounded-l-md">
                    <button type="submit" class="bg-indigo-600 text-white p-2 rounded-r-md hover:bg-indigo-700">Search</button>
                </div>
            </form>
            <!-- Quiz Scores Table -->
            <h3 class="text-xl font-semibold mb-4">Quiz Scores</h3>
            <div class="overflow-x-auto mb-6">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3">UID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Score</th>
                            <th class="p-3">Quiz Name</th>
                            <th class="p-3">Course</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quiz_scores)): ?>
                            <tr><td colspan="6" class="p-3 text-center">No quiz scores found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($quiz_scores as $score): ?>
                            <tr class="table-row">
                                <td class="p-3"><?php echo htmlspecialchars($score['user_id']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($score['user_name']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($score['score']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($score['quiz_name']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($score['course_name']); ?></td>
                                <td class="p-3">
                                    <form action="adminProfile.php" method="POST" class="inline">
                                        <input type="hidden" name="update_quiz_score" value="1">
                                        <input type="hidden" name="score_id" value="<?php echo $score['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                                        <input type="number" name="score" value="<?php echo htmlspecialchars($score['score']); ?>" required class="w-20 p-1 border rounded">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Analytics -->
        <div id="analytics" class="section-content bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-semibold mb-4">Analytics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-indigo-100 p-4 rounded-lg text-center shadow-md">
                    <h3 class="text-lg font-medium">Total Signups</h3>
                    <p class="text-2xl font-bold"><?php echo $analytics['signups']; ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg text-center shadow-md">
                    <h3 class="text-lg font-medium">Course Sold</h3>
                    <p class="text-2xl font-bold">$<?php echo number_format($analytics['revenue'] ?: 0, 2); ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg text-center shadow-md">
                    <h3 class="text-lg font-medium">Total Revenue</h3>
                    <p class="text-2xl font-bold">$<?php echo number_format($analytics['admin_revenue'] ?: 0, 2); ?></p>
                </div>
            </div>
            <h3 class="text-xl font-semibold mb-4">Course Popularity</h3>
            <div class="overflow-x-auto mb-6">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-3">Course</th>
                            <th class="p-3">Enrollments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics['course_popularity'] as $course): ?>
                        <tr class="table-row">
                            <td class="p-3"><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td class="p-3"><?php echo $course['enrollments']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <h3 class="text-xl font-semibold mb-4">Course Revenue</h3>
            <div class="chart-container mb-6">
                <canvas id="revenueChart"></canvas>
            </div>
            <h3 class="text-xl font-semibold mb-4">Course Progress</h3>
            <div class="chart-container">
                <canvas id="progressHeatmap"></canvas>
            </div>
            <script>
                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 400);
                revenueGradient.addColorStop(0, 'rgba(34, 197, 94, 0.8)');
                revenueGradient.addColorStop(1, 'rgba(34, 197, 94, 0.4)');

                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo "'" . implode("','", array_column($analytics['course_revenue'], 'course_name')) . "'"; ?>],
                        datasets: [{
                            label: 'Course Revenue ($)',
                            data: [<?php echo implode(',', array_column($analytics['course_revenue'], 'revenue')); ?>],
                            backgroundColor: revenueGradient,
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue ($)', font: { size: 14 } },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            },
                            x: {
                                title: { display: true, text: 'Courses', font: { size: 14 } },
                                ticks: { maxRotation: 45, minRotation: 45 }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { font: { size: 12 } } },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return `$${context.raw.toFixed(2)}`;
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        }
                    }
                });

                // Progress Chart
                const progressCtx = document.getElementById('progressHeatmap').getContext('2d');
                const progressGradient = progressCtx.createLinearGradient(0, 0, 0, 400);
                progressGradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
                progressGradient.addColorStop(1, 'rgba(79, 70, 229, 0.4)');

                new Chart(progressCtx, {
                    type: 'bar',
                    data: {
                        labels: [<?php echo "'" . implode("','", array_column($enrollments, 'course_name')) . "'"; ?>],
                        datasets: [{
                            label: 'Course Progress (%)',
                            data: [<?php echo implode(',', array_column($enrollments, 'progress')); ?>],
                            backgroundColor: progressGradient,
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: { display: true, text: 'Progress (%)', font: { size: 14 } },
                                grid: { color: 'rgba(0, 0, 0, 0.1)' }
                            },
                            x: {
                                title: { display: true, text: 'Courses', font: { size: 14 } },
                                ticks: { maxRotation: 45, minRotation: 45 }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { font: { size: 12 } } },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            </script>
        </div>

        <!-- Notifications -->
        <div id="notifications" class="section-content bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-2xl font-semibold mb-4">Send Notification</h2>
            <form action="adminProfile.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="send_notification" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))); ?>">
                <div>
                    <label for="recipient_email" class="block text-sm font-medium">Recipient Email</label>
                    <input type="email" id="recipient_email" name="recipient_email" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium">Subject</label>
                    <input type="text" id="subject" name="subject" required class="w-full p-2 border rounded">
                </div>
                <div class="md:col-span-2">
                    <label for="message" class="block text-sm font-medium">Message</label>
                    <textarea id="message" name="message" required class="w-full p-2 border rounded h-24"></textarea>
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium">Type</label>
                    <select id="type" name="type" class="w-full p-2 border rounded">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Send</button>
                </div>
            </form>
        </div>

        <script>
            // Sidebar toggle
            function toggleSidebar() {
                document.querySelector('.sidebar').classList.toggle('sidebar-hidden');
            }

            // Function to set the active section
            function setActiveSection(sectionId) {
                document.querySelectorAll('.section-content').forEach(content => content.classList.remove('active'));
                document.querySelectorAll('.section-link').forEach(link => link.classList.remove('bg-indigo-600'));
                
                const section = document.querySelector(`#${sectionId}`);
                if (section) {
                    section.classList.add('active');
                    const link = document.querySelector(`.section-link[data-section="${sectionId}"]`);
                    if (link) {
                        link.classList.add('bg-indigo-600');
                    }
                    window.scrollTo(0, 0);
                }
            }

            // Section navigation
            document.querySelectorAll('.section-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sectionId = link.getAttribute('data-section');
                    setActiveSection(sectionId);
                    window.location.hash = sectionId;
                });
            });

            // On page load, check URL hash and set active section
            window.addEventListener('load', () => {
                const urlParams = new URLSearchParams(window.location.search);
                const sectionId = urlParams.get('section') || window.location.hash.replace('#', '') || 'course-management';
                setActiveSection(sectionId);
            });

            // Auto-hide notifications
            setTimeout(() => {
                document.querySelectorAll('.notification').forEach(notification => {
                    notification.classList.add('hidden');
                });
            }, 5000);

            // Form validation and section preservation
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    const requiredInputs = form.querySelectorAll('[required]');
                    let valid = true;
                    requiredInputs.forEach(input => {
                        if (!input.value.trim()) {
                            valid = false;
                            input.classList.add('border-red-500');
                        } else {
                            input.classList.remove('border-red-500');
                        }
                    });
                    if (!valid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    } else {
                        const activeSection = document.querySelector('.section-content.active');
                        if (activeSection) {
                            const sectionId = activeSection.id;
                            const action = form.getAttribute('action') || 'adminProfile.php';
                            form.setAttribute('action', `${action}#${sectionId}`);
                        }
                    }
                });
            });
        </script>
    </body>
</html>
