console.log('home.js loaded at:', new Date().toLocaleString());

document.addEventListener('DOMContentLoaded', function() {
    console.log('home.js DOMContentLoaded fired');
    console.log('coursesByCategory on load:', JSON.stringify(coursesByCategory, null, 2));

    // Add manual event listeners for buttons
    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn'),
        quiz: document.getElementById('quiz_btn')
    };

    Object.keys(buttons).forEach(type => {
        if (buttons[type]) {
            buttons[type].addEventListener('click', (e) => {
                e.stopPropagation();
                console.log(`Button clicked: ${type}`);
                toggleMenu(type);
            });
        } else {
            console.error(`Button not found: ${type}`);
        }
    });

    // Enable hover and click for categories
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('mouseover', (e) => {
            const category = e.target.getAttribute('onclick').match(/'([^']+)'/)[1];
            console.log(`Hover on category: ${category}`);
            showCourses(category);
        });
        // Click is handled via onclick attribute in home.php
    });
});

function closeSubMenus() {
    const courseMenu = document.getElementById('course-menu');
    const lectureMenu = document.getElementById('lecture-menu');
    const descriptionMenu = document.getElementById('description-menu');
    [courseMenu, lectureMenu, descriptionMenu].forEach(menu => {
        if (menu) {
            menu.innerHTML = '';
            menu.classList.remove('open');
            menu.style.display = 'none';
            menu.style.visibility = 'hidden';
        }
    });
    console.log('Sub-menus closed');
}

function toggleMenu(type) {
    console.log('toggleMenu called with type:', type);

    const sidebars = {
        course: document.getElementById('course-sidebar'),
        resource: document.getElementById('resource-sidebar'),
        career: document.getElementById('career-sidebar'),
        quiz: document.getElementById('quiz-sidebar')
    };

    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn'),
        quiz: document.getElementById('quiz_btn')
    };

    const button = buttons[type];
    const sidebar = sidebars[type];

    if (!button || !sidebar) {
        console.error(`Missing ${type} button or sidebar`);
        return;
    }

    const buttonRect = button.getBoundingClientRect();
    const isOpen = sidebar.classList.contains('open');
    console.log(`${type} sidebar isOpen: ${isOpen}`);

    // Close all sidebars and sub-menus
    Object.values(sidebars).forEach(s => {
        if (s) s.classList.remove('open');
    });
    closeSubMenus();

    // Toggle target sidebar
    if (!isOpen) {
        sidebar.classList.add('open');
        console.log(`Opened ${type} sidebar`);
        if (type === 'course') {
            // Open course sidebar without populating courses
            const sidebarWidth = window.innerWidth * 0.8; // 80% of screen width
            const maxLeft = window.innerWidth - sidebarWidth;
            sidebar.style.top = `${buttonRect.bottom + window.scrollY}px`;
            sidebar.style.left = `${Math.min(buttonRect.left, maxLeft)}px`;
            sidebar.style.width = `${sidebarWidth}px`;
            console.log(`Positioned ${type} at top: ${buttonRect.bottom + window.scrollY}px, left: ${Math.min(buttonRect.left, maxLeft)}px, width: ${sidebarWidth}px`);
        } else {
            // Open other sidebars with 20% screen width
            const sidebarWidth = window.innerWidth * 0.2; // 20% of screen width
            const maxLeft = window.innerWidth - sidebarWidth;
            sidebar.style.top = `${buttonRect.bottom + window.scrollY}px`;
            sidebar.style.left = `${Math.min(buttonRect.left, maxLeft)}px`;
            sidebar.style.width = `${sidebarWidth}px`;
            console.log(`Positioned ${type} at top: ${buttonRect.bottom + window.scrollY}px, left: ${Math.min(buttonRect.left, maxLeft)}px, width: ${sidebarWidth}px`);
        }
    } else {
        console.log(`Closed ${type} sidebar`);
    }
}

function showCourses(category) {
    console.log('showCourses called for:', category);
    console.log('Available categories:', Object.keys(coursesByCategory));
    console.log('Courses for', category, ':', coursesByCategory[category]);

    const courseSidebar = document.getElementById('course-sidebar');
    const courseMenu = document.getElementById('course-menu');
    const lectureMenu = document.getElementById('lecture-menu');
    const descriptionMenu = document.getElementById('description-menu');

    if (!courseSidebar || !courseMenu || !lectureMenu || !descriptionMenu) {
        console.error('Missing course sidebar or menus');
        return;
    }

    // Ensure course sidebar is open
    const button = document.getElementById('catalogue_btn');
    const buttonRect = button.getBoundingClientRect();
    const sidebarWidth = window.innerWidth * 0.8;
    const maxLeft = window.innerWidth - sidebarWidth;
    courseSidebar.classList.add('open');
    courseSidebar.style.top = `${buttonRect.bottom + window.scrollY}px`;
    courseSidebar.style.left = `${Math.min(buttonRect.left, maxLeft)}px`;
    courseSidebar.style.width = `${sidebarWidth}px`;
    console.log(`Opened course sidebar at top: ${buttonRect.bottom + window.scrollY}px, left: ${Math.min(buttonRect.left, maxLeft)}px, width: ${sidebarWidth}px`);

    // Close other sidebars
    ['resource-sidebar', 'career-sidebar', 'quiz-sidebar'].forEach(id => {
        const sidebar = document.getElementById(id);
        if (sidebar) sidebar.classList.remove('open');
    });

    // Clear sub-menus
    courseMenu.innerHTML = '';
    lectureMenu.innerHTML = '';
    descriptionMenu.innerHTML = '';
    lectureMenu.classList.remove('open');
    descriptionMenu.classList.remove('open');
    lectureMenu.style.display = 'none';
    descriptionMenu.style.display = 'none';
    lectureMenu.style.visibility = 'hidden';
    descriptionMenu.style.visibility = 'hidden';

    // Populate course menu
    const courses = coursesByCategory[category] || [];
    if (courses.length > 0) {
        const courseList = document.createElement('div');
        courseList.className = 'course-list';
        courses.forEach(course => {
            const courseItem = document.createElement('div');
            courseItem.className = 'course-item';
            courseItem.innerHTML = `
                <div class="course-title">${course.course_name || 'Unnamed Course'}</div>
                <div class="course-details">
                    <span>Level: ${course.level || 'Not specified'}</span>
                    <span>Duration: ${course.duration ? course.duration + ' hours' : 'Not specified'}</span>
                </div>
            `;
            courseItem.addEventListener('click', (e) => {
                e.stopPropagation();
                showLectures(course);
            });
            courseList.appendChild(courseItem);
        });
        courseMenu.appendChild(courseList);
    } else {
        courseMenu.innerHTML = '<div class="course-item">No courses available</div>';
    }

    courseMenu.classList.add('open');
    courseMenu.style.display = 'flex';
    courseMenu.style.visibility = 'visible';
    console.log('Course menu populated and shown for:', category);
}

function showLectures(course) {
    console.log('showLectures called for course:', course.course_name);

    const lectureMenu = document.getElementById('lecture-menu');
    const descriptionMenu = document.getElementById('description-menu');

    if (!lectureMenu || !descriptionMenu) {
        console.error('Missing lecture or description menu');
        return;
    }

    // Clear lecture and description menus
    lectureMenu.innerHTML = '';
    descriptionMenu.innerHTML = '';
    descriptionMenu.classList.remove('open');
    descriptionMenu.style.display = 'none';
    descriptionMenu.style.visibility = 'hidden';

    const lectures = course.lecture_list ? course.lecture_list.split(',') : [];
    const descriptions = course.lecture_descriptions ? course.lecture_descriptions.split(',') : [];

    if (lectures.length > 0) {
        const lectureList = document.createElement('div');
        lectureList.className = 'lecture-list';
        lectures.forEach((lecture, index) => {
            const lectureItem = document.createElement('div');
            lectureItem.className = 'lecture-item';
            lectureItem.innerText = lecture || 'Unnamed Lecture';
            lectureItem.dataset.description = descriptions[index] || 'No description available';
            lectureItem.addEventListener('click', (e) => {
                e.stopPropagation();
                showDescription(lectureItem.dataset.description);
            });
            lectureList.appendChild(lectureItem);
        });
        lectureMenu.appendChild(lectureList);
    } else {
        lectureMenu.innerHTML = '<div class="lecture-item">No lectures available</div>';
    }

    lectureMenu.classList.add('open');
    lectureMenu.style.display = 'flex';
    lectureMenu.style.visibility = 'visible';
    console.log('Lecture menu populated and shown');
}

function showDescription(description) {
    console.log('showDescription called with:', description);

    const descriptionMenu = document.getElementById('description-menu');
    if (!descriptionMenu) {
        console.error('Missing description menu');
        return;
    }

    descriptionMenu.innerHTML = '';
    const descriptionItem = document.createElement('div');
    descriptionItem.className = 'description-item';
    descriptionItem.innerText = description || 'No description available';
    descriptionMenu.appendChild(descriptionItem);

    descriptionMenu.classList.add('open');
    descriptionMenu.style.display = 'flex';
    descriptionMenu.style.visibility = 'visible';
    console.log('Description menu populated and shown');
}

document.addEventListener('click', function(event) {
    const sidebars = {
        course: document.getElementById('course-sidebar'),
        resource: document.getElementById('resource-sidebar'),
        career: document.getElementById('career-sidebar'),
        quiz: document.getElementById('quiz-sidebar')
    };
    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn'),
        quiz: document.getElementById('quiz_btn')
    };
    const courseMenu = document.getElementById('course-menu');
    const lectureMenu = document.getElementById('lecture-menu');
    const descriptionMenu = document.getElementById('description-menu');

    // Check if click is inside any sidebar, button, or sub-menu
    for (const type in sidebars) {
        if (sidebars[type] && sidebars[type].contains(event.target) ||
            buttons[type] && buttons[type].contains(event.target)) {
            console.log(`Click inside ${type} sidebar or button, not closing`);
            return;
        }
    }
    if (courseMenu && courseMenu.contains(event.target) ||
        lectureMenu && lectureMenu.contains(event.target) ||
        descriptionMenu && descriptionMenu.contains(event.target)) {
        console.log('Click inside course, lecture, or description menu, not closing');
        return;
    }

    console.log('Click outside, closing all sidebars');
    Object.values(sidebars).forEach(sidebar => {
        if (sidebar) sidebar.classList.remove('open');
    });
    closeSubMenus();
});