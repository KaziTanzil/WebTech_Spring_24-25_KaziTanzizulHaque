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
    <title><?php echo htmlspecialchars($settings['platform_name']); ?> - Help Center</title>
    <link rel="stylesheet" href="/Final_project/Views/help-center.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="/Final_project/Views/home.js"></script>
</head>
<body>
    <?php include 'public_header.php'; ?>

    <div id="main-wrapper">
        <header>
            <h1>Help Center</h1>
            <p>Welcome to LearnOnline’s Help Center. Find answers to your questions or contact us for support.</p>
        </header>
        <div class="container">
            <section>
                <h2>Frequently Asked Questions (FAQs)</h2>
                <div class="faq-item">
                    <h3>How do I enroll in a course?</h3>
                    <p>Log in to your account, browse the Course Catalogue, select a course, and click "Enroll Now." Follow the prompts to complete enrollment.</p>
                </div>

                <div class="faq-item">
                    <h3>Can I access courses on mobile devices?</h3>
                    <p>Yes, our platform is mobile-friendly. Access courses via your browser on any smartphone or tablet.</p>
                </div>

                <div class="faq-item">
                    <h3>What if I forget my password?</h3>
                    <p>Click "Forgot Password" on the login page, enter your email, and follow the instructions to reset your password.</p>
                </div>

                <div class="faq-item">
                    <h3>Are certificates provided upon course completion?</h3>
                    <p>Yes, you’ll receive a digital certificate upon successfully completing a course, which can be downloaded from your account.</p>
                </div>
            </section>

            <section>
                <h2>Contact Support</h2>
                <p>If you can’t find answers in our FAQs, reach out to our support team:</p>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:support@learnonline.com">support@learnonline.com</a></li>
                    <li><strong>Phone:</strong> <a href="tel:098876545678">098876545678</a></li>
                    <li><strong>Live Chat:</strong> Available via your account dashboard (Monday–Friday, 9 AM–5 PM).</li>
                </ul>
            </section>

            <section>
                <h2>Additional Resources</h2>
                <p>Explore these resources for more help:</p>
                <ul>
                    <li><a href="/Final_project/Views/user-guide.php">User Guide</a> – Step-by-step instructions for using the platform.</li>
                    <li><a href="/Final_project/Views/community-forum.php">Community Forum</a> – Connect with other learners and instructors.</li>
                    <li><a href="/Final_project/Views/technical-support.php">Technical Support</a> – Troubleshoot platform issues.</li>
                </ul>
            </section>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>