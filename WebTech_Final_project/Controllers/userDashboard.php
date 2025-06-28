<?php
session_start();
require_once '../Database/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// Fetch enrolled courses and their quizzes
$enrollments = $conn->query("SELECT e.course_id, c.course_name FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.user_id = '{$user['ID']}'")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERet7yg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100 font-sans">
    <div class="p-6 md:ml-64">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
            <a href="../Controllers/logout.php" class="text-indigo-600 hover:underline"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
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

        <h2 class="text-2xl font-semibold mb-4">Your Courses</h2>
        <?php if (empty($enrollments)): ?>
            <p class="text-gray-600">You are not enrolled in any courses.</p>
        <?php else: ?>
            <?php foreach ($enrollments as $enrollment): ?>
            <div class="bg-white p-4 rounded-lg shadow-md mb-4">
                <h3 class="text-xl font-medium"><?php echo htmlspecialchars($enrollment['course_name']); ?></h3>
                <h4 class="text-lg font-medium mt-2">Quizzes</h4>
                <?php
                $quizzes = $conn->query("SELECT quiz_id, quiz_name, form_url FROM quizzes WHERE course_id = '{$enrollment['course_id']}'")->fetch_all(MYSQLI_ASSOC);
                if (empty($quizzes)) {
                    echo '<p class="text-gray-600">No quizzes available for this course.</p>';
                } else {
                    echo '<ul class="list-disc pl-5">';
                    foreach ($quizzes as $quiz) {
                        $form_url = htmlspecialchars($quiz['form_url']) . '?user_id=' . htmlspecialchars($user['ID']) . '&quiz_id=' . htmlspecialchars($quiz['quiz_id']);
                        echo '<li><a href="' . $form_url . '" target="_blank" class="text-indigo-600 hover:underline">' . htmlspecialchars($quiz['quiz_name']) . '</a></li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide notifications
        setTimeout(() => {
            document.querySelectorAll('.notification').forEach(notification => {
                notification.classList.add('hidden');
            });
        }, 5000);
    </script>
</body>
</html>