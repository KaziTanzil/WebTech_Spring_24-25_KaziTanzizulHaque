<?php
// Ensure session is started in the including page
// Expects $conn (database connection) and optional $settings from the including page
if (!isset($conn)) {
    die("Database connection not available in public_header.php");
}

// Default settings if not provided
$settings = isset($settings) ? $settings : ['platform_name' => 'Learning Platform', 'logo_path' => ''];

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
    error_log("Public Header: Course query error: " . $e->getMessage());
    $categories = [];
}
?>

<nav id="Navigation" aria-label="Main navigation">
    <div id="Navigation-head">
        <div id="Navigation-head-content">
            <label for="check" id="home-logo" style="color: antiquewhite;">
                <a href="/Final_project/Views/home.php" title="Home" aria-label="Home"><i class="fa-solid fa-house"></i></a>
            </label>
            <span><?php echo htmlspecialchars($settings['platform_name']); ?></span>
            <input type="text" id="search-ber" placeholder="What do you want to learn" aria-label="Search courses" />
            <label for="check" id="search-logo" style="color: antiquewhite;">
                <i class="fa-solid fa-magnifying-glass"></i>
            </label>
            <button id="signup_btn" onclick="location.href='/Final_project/Views/Registration.php';" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;">
                <i class="fa-solid fa-circle-user"></i> Sign Up
            </button>
        </div>
    </div>
    <div id="Navigation-tail">
        <div id="Navigation-tail-content">
            <button id="catalogue_btn" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;" aria-expanded="false">
                Course Catalogue <i class="fa-solid fa-caret-down"></i>
            </button>
            <button id="resource_btn" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;" aria-expanded="false">
                Resource <i class="fa-solid fa-caret-down"></i>
            </button>
            <button id="career_btn" style="font-size: large; cursor: pointer; background: none; border: none; display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px;" aria-expanded="false">
                Discover Career <i class="fa-solid fa-caret-down"></i>
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
                <button class="category-btn" onclick="showCourses('<?php echo htmlspecialchars($category); ?>')" aria-label="Show courses for <?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
            <!-- Dummy categories if no data in DB -->
            <?php if (empty($categories)): ?>
                <button class="category-btn" onclick="showCourses('Programming')" aria-label="Show courses for Programming">Programming</button>
                <button class="category-btn" onclick="showCourses('Data Science')" aria-label="Show courses for Data Science">Data Science</button>
                <button class="category-btn" onclick="showCourses('Design')" aria-label="Show courses for Design">Design</button>
            <?php endif; ?>
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

<link rel="stylesheet" href="/Final_project/Views/public_header.css?v=<?php echo time(); ?>">
<script src="/Final_project/Views/public_header.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript">
    var coursesByCategory = <?php
        try {
            echo json_encode($categories, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            error_log("Public Header: JSON encode error: " . $e->getMessage());
            echo '{}';
        }
    ?>;
</script>