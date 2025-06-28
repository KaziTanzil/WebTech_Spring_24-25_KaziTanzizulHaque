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
    <title><?php echo htmlspecialchars($settings['platform_name']); ?> - Privacy Policy</title>
    <link rel="stylesheet" href="/Final_project/Views/privacy-policy.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'public_header.php'; ?>

    <div id="main-wrapper">
        <header>
            <h1>Privacy Policy</h1>
            <p>Effective Date: June 12, 2025</p>
        </header>

        <div class="container">
            <section>
                <h2>1. Introduction</h2>
                <p>LearnOnline is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our platform.</p>
            </section>

            <section>
                <h2>2. Information We Collect</h2>
                <ul>
                    <li>Personal Information: name, email address, phone number, etc.</li>
                    <li>Account Information: username, password, and course enrollments.</li>
                    <li>Usage Data: IP address, browser type, access times, and pages visited.</li>
                    <li>Cookies and Tracking: to improve your user experience.</li>
                </ul>
            </section>

            <section>
                <h2>3. How We Use Your Information</h2>
                <p>We use your data to:</p>
                <ul>
                    <li>Provide and manage your account and learning progress.</li>
                    <li>Communicate with you regarding your account or services.</li>
                    <li>Improve our platform, personalize content, and enhance security.</li>
                    <li>Comply with legal obligations.</li>
                </ul>
            </section>

            <section>
                <h2>4. Sharing Your Information</h2>
                <p>We do not sell your personal information. We may share your data with:</p>
                <ul>
                    <li>Service providers who help us operate the platform.</li>
                    <li>Legal authorities, when required by law.</li>
                    <li>Affiliates and partners under strict data protection agreements.</li>
                </ul>
            </section>

            <section>
                <h2>5. Data Security</h2>
                <p>We use encryption, firewalls, and secure servers to protect your data.</p>
            </section>

            <section>
                <h2>6. Your Rights</h2>
                <ul>
                    <li>Access and update your personal information.</li>
                    <li>Request deletion of your data (subject to legal obligations).</li>
                    <li>Withdraw consent at any time.</li>
                </ul>
            </section>

            <section>
                <h2>7. Children's Privacy</h2>
                <p>Our services are not directed to individuals under the age of 13. We do not knowingly collect data from children.</p>
            </section>

            <section>
                <h2>8. Changes to This Policy</h2>
                <p>We may update this policy from time to time. Changes will be posted on this page with an updated effective date.</p>
            </section>

            <section>
                <h2>9. Contact Us</h2>
                <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                <p>Email: support@learnonline.com</p>
            </section>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>