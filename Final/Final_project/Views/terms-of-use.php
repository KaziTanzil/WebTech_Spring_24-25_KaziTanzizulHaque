<?php
session_start();
require_once '../Database/db.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch platform settings using prepared statement
try {
    $settings_stmt = $conn->prepare("SELECT platform_name, logo_path FROM settings LIMIT 1");
    if (!$settings_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $settings_stmt->execute();
    $result = $settings_stmt->get_result();
    $settings = $result->fetch_assoc() ?: ['platform_name' => 'Learning Platform', 'logo_path' => ''];
    $settings_stmt->close();
} catch (Exception $e) {
    error_log("Settings query error: " . $e->getMessage());
    $settings = ['platform_name' => 'Learning Platform', 'logo_path' => ''];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($settings['platform_name']); ?> - Terms of Use</title>
    <link rel="stylesheet" href="/Final_project/Views/terms-of-use.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="/Final_project/Views/home.js"></script>
</head>
<body>
    <?php include 'public_header.php'; ?>

    <div id="main-wrapper">
        <header>
            <h1>Terms of Use</h1>
            <p>Effective Date: June 12, 2025</p>
        </header>

        <div class="container">
            <section>
                <h2>1. Introduction</h2>
                <p>Welcome to LearnOnline. By accessing or using our platform, you agree to be bound by these Terms of Use. If you do not agree, please do not use our services.</p>
            </section>

            <section>
                <h2>2. User Responsibilities</h2>
                <ul>
                    <li><strong>Account Security:</strong> You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.</li>
                    <li><strong>Content Usage:</strong> You may not reproduce, distribute, or modify course materials without permission.</li>
                    <li><strong>Conduct:</strong> You agree not to engage in illegal, harmful, or disruptive activities, including uploading malicious code or harassing other users.</li>
                </ul>
            </section>

            <section>
                <h2>3. Intellectual Property</h2>
                <p>All content on the platform, including courses, videos, and text, is owned by LearnOnline or its licensors. You are granted a limited, non-transferable license to access content for personal, non-commercial use.</p>
            </section>

            <section>
                <h2>4. Payments and Refunds</h2>
                <p>Some courses or features may require payment. All payments are non-refundable unless otherwise stated. You agree to provide accurate billing information.</p>
            </section>

            <section>
                <h2>5. Termination</h2>
                <p>We may suspend or terminate your account for violating these Terms, with or without notice. Upon termination, your access to the platform will cease.</p>
            </section>

            <section>
                <h2>6. Limitation of Liability</h2>
                <p>LearnOnline is not liable for any indirect, incidental, or consequential damages arising from your use of the platform. Our services are provided "as is" without warranties.</p>
            </section>

            <section>
                <h2>7. Changes to Terms</h2>
                <p>We may update these Terms from time to time. Changes will be posted on this page with an updated effective date.</p>
            </section>

            <section>
                <h2>8. Governing Law</h2>
                <p>These Terms are governed by the laws of [Your Jurisdiction]. Any disputes will be resolved in the courts of [Your Jurisdiction].</p>
            </section>

            <section>
                <h2>9. Contact Us</h2>
                <p>For questions about these Terms, contact us at:</p>
                <p>Email: <a href="mailto:support@learnonline.com">support@learnonline.com</a></p>
                <p>Phone: <a href="tel:098876545678">098876545678</a></p>
            </section>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>