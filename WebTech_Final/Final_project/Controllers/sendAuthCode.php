<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = trim($_POST['gmail'] ?? '');

    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Gmail address.";
        exit();
    }

    // Generate a 6-digit authentication code
    $authcode = rand(100000, 999999);
    $_SESSION['authcode'] = (string)$authcode;

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kazitanzizulhaque@gmail.com';      // <-- your Gmail address
        $mail->Password = 'kiyluxxdgcbyixio';         // <-- your Gmail app password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Email headers and content
        $mail->setFrom('kazitanzizulhaque@gmail.com', 'project');
        $mail->addAddress($gmail);
        $mail->isHTML(false);
        $mail->Subject = 'Your Registration Authentication Code';
        $mail->Body    = "Your authentication code is: $authcode";

        $mail->send();
        echo "Authentication code sent to your Gmail.";
    } catch (Exception $e) {
        echo "Failed to send authentication code. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request method.";
}
