<?php
session_start();
require_once '../Database/db.php';

if (!isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
    header('Location: ../Views/home.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
error_log("[" . date('Y-m-d H:i:s') . "] userProfile.php user_id: $user_id");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/Logs/php_errors.log');

try {
    // Fetch user data
    $stmt = $conn->prepare("SELECT `ID` AS uid, `Name` AS username, `PhoneNumber` AS phone, `Gmail` AS email, `DoB` AS dob FROM user WHERE `ID` = ?");
    if (!$stmt) throw new Exception("User query failed: " . $conn->error);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) throw new Exception("User not found.");
    error_log("User data fetched: " . json_encode($user));

    // Fetch profile data
    $stmt = $conn->prepare("SELECT ProfilePic, Bio FROM userprofile WHERE ID = ?");
    if (!$stmt) throw new Exception("Profile query failed: " . $conn->error);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $has_image = !empty($profile['ProfilePic']);
    $user['bio'] = $profile['Bio'] ?? '';
    error_log("Profile data: Bio=" . ($user['bio'] ?? 'None') . ", Has image=" . ($has_image ? "Yes" : "No"));

    $default_image = 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Martin_Cooper.jpg';
    $user['profile_image'] = $has_image ? "/Final_project/Controllers/getImage.php?user_id=" . urlencode($user_id) : $default_image;

    // Fetch all available courses for dropdown
    $stmt = $conn->prepare("SELECT course_id, course_name FROM courses ORDER BY course_name");
    if (!$stmt) throw new Exception("Courses query failed: " . $conn->error);
    $stmt->execute();
    $courses_result = $stmt->get_result();
    $courses = $courses_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    error_log("All courses fetched: " . json_encode($courses));

    // Fetch enrolled courses and associated quizzes
    $stmt = $conn->prepare("SELECT c.course_id, c.course_name, q.quiz_id, q.quiz_name, q.form_url 
                            FROM enrollments e 
                            JOIN courses c ON e.course_id = c.course_id 
                            LEFT JOIN quizzes q ON c.course_id = q.course_id 
                            WHERE e.user_id = ? 
                            ORDER BY c.course_name, q.quiz_name");
    if (!$stmt) throw new Exception("Quizzes query failed: " . $conn->error);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $quizzes_result = $stmt->get_result();
    $quizzes = $quizzes_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    error_log("Quizzes fetched for user $user_id: " . json_encode($quizzes));

    // Notification settings
    $notifications_cache_key = "notifications:$user_id";
    if (isset($_SESSION[$notifications_cache_key])) {
        $notifications = $_SESSION[$notifications_cache_key];
    } else {
        $stmt = $conn->prepare("SELECT setting FROM notification_settings WHERE user_id = ?");
        if (!$stmt) throw new Exception("Notification settings query failed: " . $conn->error);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row['setting'];
        }
        $stmt->close();
        $_SESSION[$notifications_cache_key] = $notifications;
    }

    $notification_settings = [
        'new_courses' => in_array('new_courses', $notifications),
        'sms_alerts' => in_array('sms_alerts', $notifications),
        'monthly_reports' => in_array('monthly_reports', $notifications)
    ];

    // Activity log
    $activity_cache_key = "activity:$user_id";
    if (isset($_SESSION[$activity_cache_key])) {
        $activities = $_SESSION[$activity_cache_key];
    } else {
        $stmt = $conn->prepare("SELECT Action, DateTime FROM user_activity WHERE User_ID = ? ORDER BY DateTime DESC LIMIT 10");
        if (!$stmt) {
            error_log("Activity query failed: " . $conn->error);
            $activities = [];
        } else {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $_SESSION[$activity_cache_key] = $activities;
        }
    }

    $achievements = [
        ['name' => 'Explorer', 'level' => 3, 'progress' => 90, 'total' => 10, 'unit' => 'courses', 'color' => '#007bff', 'tooltip' => 'Complete courses to level up!'],
        ['name' => 'Mentor', 'level' => 2, 'progress' => 66, 'total' => 3, 'unit' => 'created', 'color' => '#32b768', 'tooltip' => 'Create courses to earn this badge.'],
        ['name' => 'Expert', 'level' => 1, 'progress' => 40, 'total' => 5, 'unit' => 'subjects', 'color' => '#ff9800', 'tooltip' => 'Master subjects to become an Expert.']
    ];

    // Fetch enrolled courses for "My Courses" tab
    $stmt = $conn->prepare("SELECT c.course_name FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.user_id = ?");
    if (!$stmt) throw new Exception("Enrolled courses query failed: " . $conn->error);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $my_courses_result = $stmt->get_result();
    $my_courses = $my_courses_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    error_log("Enrolled courses for user $user_id: " . json_encode($my_courses));

    // Define resources and careers arrays
    $resources = [
        ['name' => 'Supplementary Learning Materials', 'icon' => 'description', 'link' => 'supplementaryMaterials.html'],
        ['name' => 'Study Tools', 'icon' => 'psychology', 'link' => 'studyTools.html'],
        ['name' => 'Quizzes & Practice Materials', 'icon' => 'edit', 'link' => 'quizzesPractice.html'],
        ['name' => 'Tips & Strategies', 'icon' => 'lightbulb', 'link' => 'tipsStrategies.html'],
        ['name' => 'Career & Skill Resources', 'icon' => 'school', 'link' => 'careerSkill.html'],
        ['name' => 'External Resources', 'icon' => 'library_books', 'link' => 'externalResources.html']
    ];

    $careers = [
        ['name' => 'Agriculture, Food, and Natural Resources', 'description' => 'The agriculture, food and natural resources industries have a huge impact on our daily lives. Without them, what would we eat or how would we know whether our drinking water is safe? Th...'],
        ['name' => 'Architecture and Construction', 'description' => 'Do you ever look at a building or house and think about how you could have designed it better? As a child, did you continue playing with blocks long after others had moved on? If you\'re a...'],
        ['name' => 'Arts, Audio/Video Technology, and Communications', 'description' => 'The arts, A/V technology and communications are all fields that truly challenge and nurture your creative talents. They are industries that create many different types of jobs in the ar...'],
        ['name' => 'Transportation, Distribution, and Logistics', 'description' => 'Transportation, distribution and logistics is a huge industry that covers a variety of careers to suit the interests of skilled and qualified people. With the ever-increasing growth of ...'],
        ['name' => 'Education and Training', 'description' => 'Research shows that many countries around the world will likely not have enough teachers to provide quality education to all their children by 2030. People with skills in the education ...'],
        ['name' => 'Finance', 'description' => 'As much as \'love makes the world go round\', money is an essential element in each of our lives. We entrust our financial well-being to people who safeguard bank accounts, provide loans...'],
        ['name' => 'Government and Public Administration', 'description' => 'Do you love getting involved in projects and policies focused on improving your community? If so, a career in government and public administration might be for you. Here are some of the...'],
        ['name' => 'Health Science', 'description' => 'Health science is the industry of the healing hand. Do you feel rewarded by helping people feel better? The field of health science guides students to careers that promote health and we...'],
        ['name' => 'Hospitality and Tourism', 'description' => 'Supporting about 300 million jobs globally, the hospitality and tourism industry is one of the biggest in the world. It is the economic lifeblood of many countries and regions around th...'],
        ['name' => 'Human Services', 'description' => 'The objective of the human services industry is to improve overall quality of life. Careers in this field focus on prevention as well as treating problems, such as mental health service...'],
        ['name' => 'Information Technology', 'description' => 'The IT industry is a dynamic and entrepreneurial working environment that has had a revolutionary impact on the global economy and society over the past few decades. If you love technol...'],
        ['name' => 'Business Management and Administration', 'description' => 'From major corporations to independent businesses, every operation needs skilled business managers and administrators to succeed. Knowing how to deal with stress, remain calm and keep t...'],
        ['name' => 'Manufacturing', 'description' => 'Manufacturing is the process of making a product on a large scale using machinery. It is an extensive industry, which ranges from wide-open factory floors to small home-based businesses...'],
        ['name' => 'Marketing, Sales, and Service', 'description' => 'Marketing is an industry that has evolved and grown tremendously over the past few decades. It is a crucial area for businesses, large or small. A business may have the most revolutiona...'],
        ['name' => 'Law, Public Safety, Corrections, and Security', 'description' => 'Careers in law, public safety, correctional services and security cover a wide scope and people interested in these fields need to possess a variety of skills. Opportunities for growth ...'],
        ['name' => 'Science, Technology, Engineering, and Mathematics', 'description' => 'A career in science, technology, engineering and mathematics (STEM) is exciting, challenging, ever-changing and highly in-demand globally. Currently, at least 75 percent of jobs in the ...']
    ];
} catch (Exception $e) {
    $error_message = htmlspecialchars($e->getMessage());
    error_log("Error in userProfile.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile | LearnOnline Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Final_project/Views/userProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERet7yg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <nav id="Navigation" aria-label="Main navigation">
        <div id="Navigation-head">
            <div id="Navigation-head-content">
                <a href="../Views/home.php" id="home-logo" aria-label="Home"><i class="fa-solid fa-house"></i></a>
                <input type="text" id="search-ber" placeholder="What do you want to learn" aria-label="Search courses">
                <label for="search-ber" id="search-logo"><i class="fa-solid fa-magnifying-glass"></i></label>
                <button id="profile_btn" onclick="location.href='/Final_project/Views/userProfile.php';">
                    <i class="fa-solid fa-circle-user"></i> Profile
                </button>
            </div>
        </div>
        <div id="Navigation-tail">
            <div id="Navigation-tail-content">
                <div class="dropdown" data-dropdown="catalogue">
                    <button id="catalogue_btn" class="dropdown-btn debug-dropdown" aria-expanded="false" onclick="toggleDropdown('catalogue_btn', 'catalogue')">Course Catalogue <i class="fa-solid fa-caret-down"></i></button>
                    <div class="dropdown-content" id="catalogue_content" style="display: none;">
                        <?php if (empty($courses)): ?>
                            <a href="#" onclick="return false;">No courses available</a>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <a href="/Final_project/Views/purchase.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown" data-dropdown="resources">
                    <button id="resources_btn" class="dropdown-btn debug-dropdown" aria-expanded="false" onclick="toggleDropdown('resources_btn', 'resources')">Resources <i class="fa-solid fa-caret-down"></i></button>
                    <div class="dropdown-content" id="resources_content" style="display: none;">
                        <?php if (empty($resources)): ?>
                            <a href="#" onclick="return false;">No resources available</a>
                        <?php else: ?>
                            <?php foreach ($resources as $resource): ?>
                                <a href="<?php echo htmlspecialchars($resource['link']); ?>" target="_blank">
                                    <i class="fa-solid fa-<?php echo htmlspecialchars($resource['icon']); ?>"></i>
                                    <?php echo htmlspecialchars($resource['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown" data-dropdown="careers">
                    <button id="careers_btn" class="dropdown-btn debug-dropdown" aria-expanded="false" onclick="toggleDropdown('careers_btn', 'careers')">Discover Careers <i class="fa-solid fa-caret-down"></i></button>
                    <div class="dropdown-content" id="careers_content" style="display: none;">
                        <?php if (empty($careers)): ?>
                            <a href="#" onclick="return false;">No careers available</a>
                        <?php else: ?>
                            <?php foreach ($careers as $career): ?>
                                <a href="careerDetails.php?name=<?php echo urlencode($career['name']); ?>" target="_blank" title="<?php echo htmlspecialchars(substr($career['description'], 0, 100)) . (strlen($career['description']) > 100 ? '...' : ''); ?>">
                                    <?php echo htmlspecialchars($career['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown" data-dropdown="quizzes">
                    <button id="Quiz_btn" class="dropdown-btn debug-dropdown" aria-expanded="false" onclick="toggleDropdown('Quiz_btn', 'quizzes')">Quizzes <i class="fa-solid fa-caret-down"></i></button>
                    <div class="dropdown-content" id="quizzes_content" style="display: none;">
                        <?php if (empty($quizzes)): ?>
                            <a href="#" onclick="return false;">No quizzes available</a>
                        <?php else: ?>
                            <?php foreach ($quizzes as $quiz): ?>
                                <?php if (!empty($quiz['quiz_id'])): ?>
                                    <div class="quiz-item">
                                        <a href="<?php echo htmlspecialchars($quiz['form_url']); ?>" target="_blank" class="quiz-name">
                                            <?php echo htmlspecialchars($quiz['quiz_name']); ?>
                                        </a>
                                        <div class="quiz-course">Course: <?php echo htmlspecialchars($quiz['course_name']); ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <button id="View-Plans_btn">View Plans</button>
            </div>
        </div>
    </nav>

    <div class="top-bar">
        <a href="../Views/home.php" class="go-back">← Go back</a>
        <div class="top-bar-right">
            <button onclick="showTab('courses')" aria-controls="courses">My Courses</button>
            <button onclick="location.href='../Controllers/logout.php'">Logout</button>
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" class="profile-img" alt="Profile Image" loading="lazy">
        </div>
    </div>

    <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="profile-page">
        <div class="sidebar">
            <button class="active" onclick="showTab('personal')" aria-selected="true" aria-controls="personal">Personal Data</button>
            <button onclick="showTab('notifications')" aria-selected="false" aria-controls="notifications">Notifications</button>
            <button onclick="showTab('achievements')" aria-selected="false" aria-controls="achievements">Achievements</button>
            <button onclick="showTab('courses')" aria-selected="false" aria-controls="courses">My Courses</button>
            <button onclick="showTab('activity')" aria-selected="false" aria-controls="activity">Activity Log</button>
        </div>
        <div class="card profile-card">
            <h1 class="profile-title">My Profile</h1>
            <div class="profile-actions">
                <button type="button" onclick="showPreview()" aria-label="Preview profile">Preview Profile</button>
            </div>
            <?php if (isset($_GET['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['error']) || isset($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error'] ?? $error_message); ?></p>
            <?php endif; ?>
            <form id="profile-form" method="POST" action="../Controllers/profileController.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div id="personal" class="tab-content active" role="tabpanel">
                    <div class="photo-section">
                        <img id="photo-preview" src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" loading="lazy">
                        <div>
                            <p>JPG, PNG, GIF, WebP. Max 1MB.</p>
                            <input type="file" name="profile_image" id="profile-image-input" accept="image/jpeg,image/png,image/gif,image/webp" aria-label="Upload profile image">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <div class="input-group">
                            <i class="fa-solid fa-address-card"></i>
                            <textarea id="bio" name="bio" maxlength="250" placeholder="Tell us about yourself (max 250 characters)" aria-describedby="bio-error"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        <small id="bio-error" class="error-text" style="display: none;"></small>
                    </div>
                    <div class="form-group">
                        <label for="uid">UID</label>
                        <input type="text" id="uid" name="uid" value="<?php echo htmlspecialchars($user['uid']); ?>" readonly aria-describedby="uid-help">
                        <small id="uid-help" class="sr-only">This is your unique user ID, which cannot be edited.</small>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required aria-required="true" aria-describedby="username-error">
                        </div>
                        <small id="username-error" class="error-text" style="display: none;"></small>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-group">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" pattern="\+880[0-9]{10}" aria-describedby="phone-error">
                        </div>
                        <small id="phone-error" class="error-text" style="display: none;"></small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly aria-required="true" aria-describedby="email-error">
                        </div>
                        <small id="email-error" class="error-text" style="display: none;"></small>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <div class="input-group">
                            <i class="fa-solid fa-calendar"></i>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" aria-describedby="dob-error">
                        </div>
                        <small id="dob-error" class="error-text" style="display: none;"></small>
                    </div>
                    <button type="submit" name="submit" value="personal" id="personal-submit" onclick="console.log('Personal Save Changes clicked')">Save Changes</button>
                </div>
                <div id="notifications" class="tab-content" role="tabpanel" style="display: none;">
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="email_notifications" name="email_notifications" <?php echo $notification_settings['new_courses'] ? 'checked' : ''; ?>>
                        <label for="email_notifications">Receive New Course Notifications</label>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="sms_notifications" name="sms_notifications" <?php echo $notification_settings['sms_alerts'] ? 'checked' : ''; ?>>
                        <label for="sms_notifications">Receive SMS Alerts</label>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="push_notifications" name="push_notifications" <?php echo $notification_settings['monthly_reports'] ? 'checked' : ''; ?>>
                        <label for="push_notifications">Receive Monthly Reports</label>
                    </div>
                    <button type="submit" name="submit" value="notifications" id="notifications-submit">Save Changes</button>
                </div>
                <div id="achievements" class="tab-content" role="tabpanel" style="display: none;">
                    <h2>Achievements</h2>
                    <?php if (empty($achievements)): ?>
                        <p>No achievements yet. Start learning to earn badges!</p>
                    <?php else: ?>
                        <?php foreach ($achievements as $achievement): ?>
                            <div class="achievement" title="<?php echo htmlspecialchars($achievement['tooltip']); ?>">
                                <div><strong><?php echo htmlspecialchars($achievement['name']); ?></strong> – Level <?php echo htmlspecialchars($achievement['level']); ?></div>
                                <div class="progress-bar">
                                    <div class="progress" style="--progress: <?php echo htmlspecialchars($achievement['progress']); ?>%; --color: <?php echo htmlspecialchars($achievement['color']); ?>;"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo htmlspecialchars(floor($achievement['progress'] / 100 * $achievement['total'])); ?>/
                                    <?php echo htmlspecialchars($achievement['total']); ?> <?php echo htmlspecialchars($achievement['unit']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="button" class="show-all">Show All Achievements</button>
                    <?php endif; ?>
                </div>
                <div id="courses" class="tab-content" role="tabpanel" style="display: none;">
                    <h2>My Courses</h2>
                    <ul>
                        <?php if (empty($my_courses)): ?>
                            <li>No courses enrolled. Explore the Course Catalogue!</li>
                        <?php else: ?>
                            <?php foreach ($my_courses as $course): ?>
                                <li><i class="fa-solid fa-book" aria-hidden="true"></i> <?php echo htmlspecialchars($course['course_name']); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div id="activity" class="tab-content" role="tabpanel" style="display: none;">
                    <h2>Recent Activity</h2>
                    <ul>
                        <?php if (empty($activities)): ?>
                            <li>No recent activity.</li>
                        <?php else: ?>
                            <?php foreach ($activities as $activity): ?>
                                <li><?php echo htmlspecialchars($activity['Action']); ?> - <?php echo htmlspecialchars($activity['DateTime']); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </form>
        </div>
    </div>

    <div id="profilePreview" class="modal" role="dialog" aria-labelledby="previewLabel">
        <div class="modal-content">
            <h3 id="previewLabel">Public Profile Preview</h3>
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile picture" class="preview-img" loading="lazy">
            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
            <p class="preview-bio"><?php echo htmlspecialchars($user['bio'] ?? 'No bio provided.'); ?></p>
            <div class="preview-achievements">
                <h5>Achievements</h5>
                <?php foreach ($achievements as $item): ?>
                    <div><?php echo htmlspecialchars($item['name']); ?> - Level <?php echo htmlspecialchars($item['level']); ?></div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="closePreview()">Close</button>
        </div>
    </div>

    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
        <i class="fa-solid fa-moon"></i>
    </button>

    <div id="foote">
        <footer>
            <div id="number">
                <p><i class="fa-solid fa-phone" aria-hidden="true"></i><a href="tel:098876545678">098876545678</a></p>
                <p><i class="fa-solid fa-envelope" aria-hidden="true"></i><a href="mailto:support@learnonline.com">support@learnonline.com</a></p>
            </div>
            <div id="social-media">
                <a href="https://www.facebook.com/yourprofile" target="_blank" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/yourprofile" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.x.com/yourprofile" target="_blank" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.youtube.com/yourprofile" target="_blank" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                <a href="https://www.linkedin.com/yourprofile" target="_blank" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
            <div id="copy">
                <p>© 2025 Learning Platform</p>
                <p>All Rights Reserved</p>
                <div id="legal-links">
                    <a href="/Final_project/Views/terms-of-use.php" title="Terms of Use"><i class="fa-solid fa-file-contract"></i> Terms of Use</a>
                    <a href="/Final_project/Views/privacy-policy.php" title="Privacy Policy"><i class="fa-solid fa-shield-halved"></i> Privacy Policy</a>
                    <a href="/Final_project/Views/help-center.php" title="Help Center"><i class="fa-solid fa-circle-question"></i> Help Center</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="/Final_project/Views/userProfile.js"></script>
</body>
</html>