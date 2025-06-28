<?php
// Default settings if not provided
$settings = isset($settings) ? $settings : ['platform_name' => 'Learning Platform', 'logo_path' => ''];
?>

<div id="foote">
    <footer>
        <div id="number">
            <p><i class="fa-solid fa-phone" style="font-size: 15px; margin-right: 5px;"></i>098876545678</p>
            <div id="social-media">
                <a href="https://www.facebook.com/yourprofile" title="Facebook" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://www.instagram.com/yourprofile" title="Instagram" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.x.com/yourprofile" title="X" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.youtube.com/yourprofile" title="YouTube" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                <a href="https://www.linkedin.com/yourprofile" title="LinkedIn" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
            </div>
            <p>© 2025 <?php echo htmlspecialchars($settings['platform_name']); ?> All Rights Reserved</p>
        </div>
        <div id="copy">
            <p><i class="fa-solid fa-envelope" style="font-size: 15px;"></i> learningPlatform@gmail.com</p>
            <div id="legal-links">
                <a href="/Final_project/Views/terms-of-use.php" title="Terms of Use"><i class="fa-solid fa-file-contract"></i> Terms of Use</a>
                <a href="/Final_project/Views/privacy-policy.php" title="Privacy Policy"><i class="fa-solid fa-shield-halved"></i> Privacy Policy</a>
                <a href="/Final_project/Views/help-center.php" title="Help Center"><i class="fa-solid fa-circle-question"></i> Help Center</a>
            </div>
        </div>
    </footer>
</div>

<button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
    <i class="fa-solid fa-moon"></i>
</button>

<link rel="stylesheet" href="/Final_project/Views/footer.css">
<script src="/Final_project/Views/footer.js"></script>