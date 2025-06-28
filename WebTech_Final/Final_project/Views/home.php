<?php
session_start();
require_once '../Database/db.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure CSRF token is set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch courses using prepared statement and group by category
try {
    $courses_stmt = $conn->prepare("SELECT course_id, course_name, lecture_list, lecture_descriptions, category, level, duration FROM courses");
    if (!$courses_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $courses_stmt->execute();
    $result = $courses_stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $category = $row['category'] ?: 'Uncategorized';
        $categories[$category][] = $row;
    }
    $courses_stmt->close();
} catch (Exception $e) {
    error_log("Course query error: " . $e->getMessage());
    $categories = [];
}

// Fetch quizzes using prepared statement
try {
    $quizzes_stmt = $conn->prepare("SELECT quiz_id, quiz_name, course_id FROM quizzes");
    if (!$quizzes_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $quizzes_stmt->execute();
    $result = $quizzes_stmt->get_result();
    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    $quizzes_stmt->close();
} catch (Exception $e) {
    error_log("Quiz query error: " . $e->getMessage());
    $quizzes = [];
}

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
    <title><?php echo htmlspecialchars($settings['platform_name']); ?></title>
    <link rel="stylesheet" href="/Final_project/Views/home.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <nav id="Navigation">
        <div id="Navigation-head">
            <div id="Navigation-head-content">
                <label for="check" id="home-logo" style="color: antiquewhite;">
                    <a href="home.php" title="Home"><i class="fa-solid fa-house"></i></a>
                </label>
                <span><?php echo htmlspecialchars($settings['platform_name']); ?></span>
                <input type="text" id="search-ber" placeholder="What do you want to learn" />
                <label for="check" id="search-logo" style="color: antiquewhite;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <button id="signup_btn" onclick="location.href='Registration.php';" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;">
                    <i class="fa-solid fa-circle-user"></i> Sign Up
                </button>
            </div>
        </div>
        <div id="Navigation-tail">
            <div id="Navigation-tail-content">
                <button id="catalogue_btn" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;">
                    Course Catalogue <i class="fa-solid fa-caret-down"></i>
                </button>
                <button style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px;" id="resource_btn">
                    Resource <i class="fa-solid fa-caret-down"></i>
                </button>
                <button style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px;" id="career_btn">
                    Discover Career <i class="fa-solid fa-caret-down"></i>
                </button>
                <button style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px;" id="quiz_btn">
                    Quiz <i class="fa-solid fa-caret-down"></i>
                </button>
                <button style="margin-left: 200px; border: 2px solid black; border-radius: 30px; padding: 5px 20px; color: black; font-size: 1rem; display: inline-flex; align-items: center; gap: 5px; background-color: white; cursor: pointer;" id="View-Plans_btn">
                    View Plans <i class="fa-solid fa-caret-down"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Course Catalogue Sidebar -->
    <div id="course-sidebar" class="sidebar-menu course-sidebar">
        <div class="category-menu">
            <div class="category-list">
                <?php foreach (array_keys($categories) as $category): ?>
                    <button class="category-btn" onclick="showCourses('<?php echo htmlspecialchars($category); ?>')">
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="course-menu" id="course-menu"></div>
        <div class="lecture-menu" id="lecture-menu"></div>
        <div class="description-menu" id="description-menu"></div>
    </div>

    <!-- Resource Sidebar -->
    <div id="resource-sidebar" class="sidebar-menu">
        <div class="resource-menu">
            <h3>Resources</h3>
            <div id="resource-list"></div>
        </div>
    </div>

    <!-- Career Sidebar -->
    <div id="career-sidebar" class="sidebar-menu">
        <div class="career-menu">
            <h3>Career Paths</h3>
            <div id="career-list"></div>
        </div>
    </div>

    <!-- Quiz Sidebar -->
    <div id="quiz-sidebar" class="sidebar-menu">
        <div class="quiz-menu">
            <h3>Quizzes</h3>
            <div id="quiz-list">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="quiz-item">
                        <h4><?php echo htmlspecialchars($quiz['quiz_name']); ?></h4>
                        <p><strong>Course ID:</strong> <?php echo htmlspecialchars($quiz['course_id']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="main-wrapper">
        <div id="box">
            <?php if ($settings['logo_path']): ?>
                <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Platform Logo" style="max-width: 200px; margin: 20px;">
            <?php endif; ?>
        </div>

        <div id="box_bottom"></div>

        <div id="loginbox">
            <h2 style="margin-left: 55px;">Login</h2>
            <div id="login_logos" style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                <button><i class="fa-brands fa-google"></i></button>
                <button><i class="fa-brands fa-linkedin"></i></button>
                <button><i class="fa-brands fa-facebook"></i></button>
                <button><i class="fa-brands fa-apple"></i></button>
            </div>
            <div>------------------------------------or------------------------------------</div>
            <form action="../Controllers/loginValidate.php" method="POST" style="position: relative; margin-top: 30px; margin-left: 20px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <label for="gmail" style="display: block; font-size: 20px; margin-bottom: 5px;">Email Address</label>
                <input type="email" id="gmail" name="gmail" placeholder="Enter your email" required style="width: 300px; padding: 8px; font-size: 16px;" />
                <label for="password" style="display: block; font-size: 20px; margin-top: 20px; margin-bottom: 5px;">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required style="width: 300px; padding: 8px; font-size: 16px;" />
                <div style="margin-top: 10px;">
                    <button type="submit" style="width: 300px; padding: 10px; font-size: 18px; background-color: #007BFF; color: white; border: none; cursor: pointer; border-radius: 5px;">
                        Login
                    </button>
                </div>
            </form>
            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<p style="color:red; margin-left:20px; margin-top:10px;">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
                unset($_SESSION['login_error']);
            }
            if (isset($_SESSION['login_message'])) {
                echo '<p style="color:green; margin-left:20px; margin-top:10px;">' . htmlspecialchars($_SESSION['login_message']) . '</p>';
                unset($_SESSION['login_message']);
            }
            ?>
            <p style="width: 300px; margin-top: 5px; font-size: 14px; color: #ccc; margin-left: 20px;">
                By continuing, you accept our
                <a href="terms-of-use.php" style="color: #66b2ff;">Terms of Use</a>,
                our <a href="privacy-policy.php" style="color: #66b2ff;">Privacy Policy</a>
                and that your data is stored in Bangladesh.
            </p>
        </div>
    </div>

    <div id="foote">
        <footer>
            <div id="number">
                <p><i class="fa-solid fa-phone" style="font-size: 15px; margin-right: 5px;"></i>098876545678</p>
                <div id="social-media">
                    <a href="https://www.facebook.com/yourprofile" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://www.instagram.com/yourprofile" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://www.x.com/yourprofile" title="X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://www.youtube.com/yourprofile" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/yourprofile" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                </div>
                <p>© 2025 <?php echo htmlspecialchars($settings['platform_name']); ?> All Rights Reserved</p>
            </div>
            <div id="copy">
                <p><i class="fa-solid fa-envelope" style="font-size: 15px;"></i> learningPlatform@gmail.com</p>
                <div id="legal-links">
                    <a href="terms-of-use.php" title="Terms of Use"><i class="fa-solid fa-file-contract"></i>Terms of Use</a>
                    <a href="privacy-policy.php" title="Privacy Policy"><i class="fa-solid fa-shield-halved"></i>Privacy Policy</a>
                    <a href="help-center.php" title="Help Center"><i class="fa-solid fa-circle-question"></i>Help Center</a>
                </div>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        var coursesByCategory = <?php
            try {
                echo json_encode($categories, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                error_log("JSON encode error: " . $e->getMessage());
                echo '{}';
            }
        ?>;
        console.log('coursesByCategory:', JSON.stringify(coursesByCategory, null, 2));

        var careers = [
            { name: 'Agriculture, Food, and Natural Resources', description: 'The agriculture, food and natural resources industries have a huge impact on our daily lives. Without them, what would we eat or how would we know whether our drinking water is safe? Th...' },
            { name: 'Architecture and Construction', description: 'Do you ever look at a building or house and think about how you could have designed it better? As a child, did you continue playing with blocks long after others had moved on? If you\'re a...' },
            { name: 'Arts, Audio/Video Technology, and Communications', description: 'The arts, A/V technology and communications are all fields that truly challenge and nurture your creative talents. They are industries that create many different types of jobs in the ar...' },
            { name: 'Transportation, Distribution, and Logistics', description: 'Transportation, distribution and logistics is a huge industry that covers a variety of careers to suit the interests of skilled and qualified people. With the ever-increasing growth of ...' },
            { name: 'Education and Training', description: 'Research shows that many countries around the world will likely not have enough teachers to provide quality education to all their children by 2030. People with skills in the education ...' },
            { name: 'Finance', description: 'As much as \'love makes the world go round\', money is an essential element in each of our lives. We entrust our financial well-being to people who safeguard bank accounts, provide loans...' },
            { name: 'Government and Public Administration', description: 'Do you love getting involved in projects and policies focused on improving your community? If so, a career in government and public administration might be for you. Here are some of the...' },
            { name: 'Health Science', description: 'Health science is the industry of the healing hand. Do you feel rewarded by helping people feel better? The field of health science guides students to careers that promote health and we...' },
            { name: 'Hospitality and Tourism', description: 'Supporting about 300 million jobs globally, the hospitality and tourism industry is one of the biggest in the world. It is the economic lifeblood of many countries and regions around th...' },
            { name: 'Human Services', description: 'The objective of the human services industry is to improve overall quality of life. Careers in this field focus on prevention as well as treating problems, such as mental health service...' },
            { name: 'Information Technology', description: 'The IT industry is a dynamic and entrepreneurial working environment that has had a revolutionary impact on the global economy and society over the past few decades. If you love technol...' },
            { name: 'Business Management and Administration', description: 'From major corporations to independent businesses, every operation needs skilled business managers and administrators to succeed. Knowing how to deal with stress, remain calm and keep t...' },
            { name: 'Manufacturing', description: 'Manufacturing is the process of making a product on a large scale using machinery. It is an extensive industry, which ranges from wide-open factory floors to small home-based businesses...' },
            { name: 'Marketing, Sales, and Service', description: 'Marketing is an industry that has evolved and grown tremendously over the past few decades. It is a crucial area for businesses, large or small. A business may have the most revolutiona...' },
            { name: 'Law, Public Safety, Corrections, and Security', description: 'Careers in law, public safety, correctional services and security cover a wide scope and people interested in these fields need to possess a variety of skills. Opportunities for growth ...' },
            { name: 'Science, Technology, Engineering, and Mathematics', description: 'A career in science, technology, engineering and mathematics (STEM) is exciting, challenging, ever-changing and highly in-demand globally. Currently, at least 75 percent of jobs in the ...' }
        ];

        var resources = [
            { name: 'Supplementary Learning Materials', icon: 'description', link: 'supplementaryMaterials.html' },
            { name: 'Study Tools', icon: 'psychology', link: 'studyTools.html' },
            { name: 'Quizzes & Practice Materials', icon: 'edit', link: 'quizzesPractice.html' },
            { name: 'Tips & Strategies', icon: 'lightbulb', link: 'tipsStrategies.html' },
            { name: 'Career & Skill Resources', icon: 'school', link: 'careerSkill.html' },
            { name: 'External Resources', icon: 'library_books', link: 'externalResources.html' }
        ];

        function populateCareerList() {
            const careerList = document.getElementById('career-list');
            if (!careerList) return;
            careerList.innerHTML = '';
            careers.forEach(career => {
                if (career.name) {
                    const careerItem = document.createElement('div');
                    careerItem.className = 'career-item';
                    careerItem.innerHTML = `
                        <h4>${career.name}</h4>
                        <p>${career.description}</p>
                        <button class="view-careers-btn">View Careers</button>
                    `;
                    careerList.appendChild(careerItem);
                }
            });
        }

        function populateResourceList() {
            const resourceList = document.getElementById('resource-list');
            if (!resourceList) return;
            resourceList.innerHTML = '';
            resources.forEach(resource => {
                const resourceItem = document.createElement('div');
                resourceItem.className = 'resource-item';
                resourceItem.innerHTML = `
                    <a href="${resource.link}" class="resource-link">
                        <i class="material-icons">${resource.icon}</i>
                        ${resource.name}
                    </a>
                `;
                resourceList.appendChild(resourceItem);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded fired, populating lists');
            populateCareerList();
            populateResourceList();
        });
    </script>
    <script src="/Final_project/Views/home.js?v=<?php echo time(); ?>"></script>
</body>
</html>