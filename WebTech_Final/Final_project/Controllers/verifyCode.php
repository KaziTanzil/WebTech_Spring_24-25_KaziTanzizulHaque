<?php
session_start();

require_once '../Database/db.php';
require_once '../Models/User.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputCode = trim($_POST['code'] ?? '');

    // Check session data exists
    if (!isset($_SESSION['authcode']) || !isset($_SESSION['pending_user'])) {
        echo "Session expired. Please register again.";
        exit();
    }

    // Check code expiry (5 minutes)
    if (time() - $_SESSION['authcode_time'] > 300) {
        echo "Code expired.";
        session_destroy();
        exit();
    }

    // Validate input code against session code
    if ($inputCode == $_SESSION['authcode']) {
        $userModel = new User($conn);

        // Save user (with original password as provided)
        $result = $userModel->save($_SESSION['pending_user']);

        if ($result['success']) {
            // Clear session variables after success
            unset($_SESSION['authcode'], $_SESSION['authcode_time'], $_SESSION['pending_user']);
            header("Location: ../Views/home.php");
            exit();
        } else {
            echo "Failed to save user: " . $result['error'];
        }
    } else {
        echo "Invalid code. Try again.";
    }
} else {
    echo "Invalid request.";
}
?>
