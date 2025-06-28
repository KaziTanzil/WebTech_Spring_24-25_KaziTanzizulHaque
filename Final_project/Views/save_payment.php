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

    $user_id = trim($_POST['User_ID'] ?? '');
    $cardholder_name = trim($_POST['cardholder_name'] ?? '');
    $card_number = trim($_POST['card_number'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $billing_address = trim($_POST['billing_address'] ?? '');

    // Validate inputs
    if (empty($user_id) || empty($cardholder_name) || empty($card_number) || empty($card_expiry) || empty($billing_address)) {
        die("Invalid form submission: All fields are required.");
    }

    // Basic format validation
    if (!preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $card_number)) {
        die("Invalid card number format (use XXXX-XXXX-XXXX-XXXX).");
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/20\d{2}$/', $card_expiry)) {
        die("Invalid expiry date format (use MM/YYYY).");
    }

    // Check if user_id exists in user table
    $check_user = $conn->prepare("SELECT ID FROM user WHERE ID = ?");
    $check_user->bind_param("s", $user_id);
    $check_user->execute();
    if ($check_user->get_result()->num_rows == 0) {
        die("Invalid user ID.");
    }
    $check_user->close();

    // Insert into payment_info
    try {
        $stmt = $conn->prepare("INSERT INTO payment_info (User_ID, cardholder_name, card_number, card_expiry, billing_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $user_id, $cardholder_name, $card_number, $card_expiry, $billing_address);
        if ($stmt->execute()) {
            echo "Payment info saved successfully.";
        } else {
            echo "Error saving payment info: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
    }
}

$conn->close();
?>