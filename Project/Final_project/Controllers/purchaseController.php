<?php
session_start();
require_once '../Database/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    error_log("purchaseController.php: User not logged in");
    header('Location: ../Views/home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("purchaseController.php: Invalid request method");
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

$course_id = isset($_POST['course_id']) ? $_POST['course_id'] : null;
$amount = isset($_POST['amount']) ? $_POST['amount'] : null;
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
$card_number = isset($_POST['card_number']) ? $_POST['card_number'] : null;
$card_holder = isset($_POST['card_holder']) ? $_POST['card_holder'] : null;
$expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
$cvv = isset($_POST['cvv']) ? $_POST['cvv'] : null;

if (!$course_id || !$amount || !$csrf_token || !$card_number || !$card_holder || !$expiry_date || !$cvv) {
    error_log("purchaseController.php: Missing required fields");
    header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Missing required fields'));
    exit;
}

// Verify CSRF token
if ($csrf_token !== $_SESSION['csrf_token']) {
    error_log("purchaseController.php: Invalid CSRF token");
    header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Invalid CSRF token'));
    exit;
}

// Validate course exists
try {
    $stmt = $conn->prepare("SELECT course_name, cost, file_path FROM courses WHERE course_id = ?");
    if (!$stmt) throw new Exception("Course query failed: " . $conn->error);
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course) {
        error_log("purchaseController.php: Course not found - course_id=$course_id");
        header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Course not found'));
        exit;
    }

    // Verify amount matches course cost
    if ($amount != $course['cost']) {
        error_log("purchaseController.php: Amount mismatch - submitted=$amount, expected={$course['cost']}");
        header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Invalid payment amount'));
        exit;
    }

    // Validate payment details (server-side)
    if (!preg_match('/^\d{16}$/', str_replace(' ', '', $card_number))) {
        error_log("purchaseController.php: Invalid card number - course_id=$course_id");
        header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Invalid card number'));
        exit;
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry_date)) {
        error_log("purchaseController.php: Invalid expiry date - course_id=$course_id");
        header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Invalid expiry date'));
        exit;
    }
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        error_log("purchaseController.php: Invalid CVV - course_id=$course_id");
        header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Invalid CVV'));
        exit;
    }

    // Insert payment record
    $payment_id = 'payment_' . uniqid();
    $payment_date = date('Y-m-d H:i:s');
    $status = 'completed'; // Simulate successful payment
    $stmt = $conn->prepare("INSERT INTO payments (payment_id, user_id, course_id, amount, payment_date, status, card_number, card_holder, expiry_date, cvv) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Payment insert query failed: " . $conn->error);
    $stmt->bind_param("ssssdsssss", $payment_id, $_SESSION['user']['id'], $course_id, $amount, $payment_date, $status, $card_number, $card_holder, $expiry_date, $cvv);
    $stmt->execute();
    $stmt->close();

    // Insert into admin_revenue
    $revenue_id = 'revenue_' . uniqid();
    $stmt = $conn->prepare("INSERT INTO admin_revenue (revenue_id, admin_id, course_id, user_id, amount, payment_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) throw new Exception("Admin revenue insert query failed: " . $conn->error);
    $admin_id = '1'; // Assuming admin ID is '1' based on database
    $stmt->bind_param("ssssds", $revenue_id, $admin_id, $course_id, $_SESSION['user']['id'], $amount, $payment_id);
    $stmt->execute();
    $stmt->close();

    // Insert into course_downloads (optional, for tracking downloads)
    if ($course['file_path']) {
        $download_id = 'download_' . uniqid();
        $download_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO course_downloads (download_id, user_id, course_id, payment_id, download_date) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception("Course download insert query failed: " . $conn->error);
        $stmt->bind_param("sssss", $download_id, $_SESSION['user']['id'], $course_id, $payment_id, $download_date);
        $stmt->execute();
        $stmt->close();
    }

    // Clear CSRF token
    unset($_SESSION['csrf_token']);

    // Redirect to purchase.php with success message
    error_log("purchaseController.php: Payment processed successfully for course_id=$course_id");
    header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&success=' . urlencode('Payment successful'));
    exit;
} catch (Exception $e) {
    error_log("purchaseController.php: Error - " . $e->getMessage());
    header('Location: ../Views/purchase.php?course_id=' . urlencode($course_id) . '&error=' . urlencode('Error processing payment'));
    exit;
}
?>