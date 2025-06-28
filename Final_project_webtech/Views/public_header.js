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
    { name: 'Supplementary Learning Materials', icon: 'description', link: 'supplementaryMaterials.php' },
    { name: 'Study Tools', icon: 'psychology', link: 'studyTools.php' },
    { name: 'Quizzes & Practice Materials', icon: 'edit', link: 'quizzesPractice.php' },
    { name: 'Tips & Strategies', icon: 'lightbulb', link: 'tipsStrategies.php' },
    { name: 'Career & Skill Resources', icon: 'school', link: 'careerSkill.php' },
    { name: 'External Resources', icon: 'library_books', link: 'externalResources.php' }
];

function populateCareerList() {
    const careerList = document.getElementById('career-list');
    if (!careerList) {
        console.error('Career list element not found');
        return;
    }
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
    console.log('Career list populated');
}

function populateResourceList() {
    const resourceList = document.getElementById('resource-list');
    if (!resourceList) {
        console.error('Resource list element not found');
        return;
    }
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
    console.log('Resource list populated');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('public_header.js DOMContentLoaded fired');
    populateCareerList();
    populateResourceList();

    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn')
    };

    Object.keys(buttons).forEach(type => {
        if (buttons[type]) {
            buttons[type].addEventListener('click', (e) => {
                e.stopPropagation();
                console.log(`Button clicked: ${type}`);
                toggleMenu(type);
            });
        } else {
            console.error(`Button not found: ${type}_btn`);
        }
    });

    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('mouseover', (e) => {
            const category = e.target.getAttribute('onclick').match(/'([^']+)'/)[1];
            console.log(`Hover on category: ${category}`);
            showCourses(category);
        });
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
        career: document.getElementById('career-sidebar')
    };

    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn')
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
        if (s) {
            s.classList.remove('open');
            s.style.top = '-100%';
            s.style.visibility = 'hidden';
        }
    });
    Object.values(buttons).forEach(b => {
        if (b) b.setAttribute('aria-expanded', 'false');
    });
    closeSubMenus();

    // Open the clicked sidebar if it was not already open
    if (!isOpen) {
        sidebar.classList.add('open');
        const sidebarWidth = type === 'course' ? window.innerWidth * 0.8 : window.innerWidth * 0.2;
        const maxLeft = window.innerWidth - sidebarWidth;
        sidebar.style.top = `${buttonRect.bottom + window.scrollY}px`;
        sidebar.style.left = `${Math.min(buttonRect.left, maxLeft)}px`;
        sidebar.style.width = `${sidebarWidth}px`;
        sidebar.style.visibility = 'visible';
        button.setAttribute('aria-expanded', 'true');
        console.log(`Opened ${type} sidebar at top: ${buttonRect.bottom + window.scrollY}px, left: ${Math.min(buttonRect.left, maxLeft)}px, width: ${sidebarWidth}px`);

        // Re-populate content to ensure it's fresh
        if (type === 'resource') populateResourceList();
        if (type === 'career') populateCareerList();
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

    const button = document.getElementById('catalogue_btn');
    const buttonRect = button.getBoundingClientRect();
    const sidebarWidth = window.innerWidth * 0.8;
    const maxLeft = window.innerWidth - sidebarWidth;
    courseSidebar.classList.add('open');
    courseSidebar.style.top = `${buttonRect.bottom + window.scrollY}px`;
    courseSidebar.style.left = `${Math.min(buttonRect.left, maxLeft)}px`;
    courseSidebar.style.width = `${sidebarWidth}px`;
    courseSidebar.style.visibility = 'visible';
    console.log(`Opened course sidebar at top: ${buttonRect.bottom + window.scrollY}px, left: ${Math.min(buttonRect.left, maxLeft)}px, width: ${sidebarWidth}px`);

    ['resource-sidebar', 'career-sidebar'].forEach(id => {
        const sidebar = document.getElementById(id);
        if (sidebar) {
            sidebar.classList.remove('open');
            sidebar.style.top = '-100%';
            sidebar.style.visibility = 'hidden';
        }
    });

    courseMenu.innerHTML = '';
    lectureMenu.innerHTML = '';
    descriptionMenu.innerHTML = '';
    lectureMenu.classList.remove('open');
    descriptionMenu.classList.remove('open');
    lectureMenu.style.display = 'none';
    descriptionMenu.style.display = 'none';
    lectureMenu.style.visibility = 'hidden';
    descriptionMenu.style.visibility = 'hidden';

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
        career: document.getElementById('career-sidebar')
    };
    const buttons = {
        course: document.getElementById('catalogue_btn'),
        resource: document.getElementById('resource_btn'),
        career: document.getElementById('career_btn')
    };
    const courseMenu = document.getElementById('course-menu');
    const lectureMenu = document.getElementById('lecture-menu');
    const descriptionMenu = document.getElementById('description-menu');

    // Check if click is inside any sidebar or button
    for (const type in sidebars) {
        if (sidebars[type] && sidebars[type].contains(event.target) ||
            buttons[type] && buttons[type].contains(event.target)) {
            console.log(`Click inside ${type} sidebar or button, not closing`);
            return;
        }
    }

    // Close all sidebars and sub-menus if click is outside
    console.log('Click outside, closing all sidebars and sub-menus');
    Object.entries(sidebars).forEach(([type, sidebar]) => {
        if (sidebar) {
            sidebar.classList.remove('open');
            sidebar.style.top = '-100%';
            sidebar.style.visibility = 'hidden';
        }
        if (buttons[type]) {
            buttons[type].setAttribute('aria-expanded', 'false');
        }
    });
    closeSubMenus();
});