console.log('userProfile.js loaded at', new Date().toISOString());

function toggleDropdown(buttonId, dropdownId) {
    try {
        const button = document.getElementById(buttonId);
        const content = document.getElementById(dropdownId + '_content');
        if (!button || !content) {
            console.error(`Dropdown failed: buttonId=${buttonId}, dropdownId=${dropdownId}`);
            return;
        }
        const isOpen = content.style.display === 'block';
        content.style.display = isOpen ? 'none' : 'block';
        button.setAttribute('aria-expanded', !isOpen);
        button.style.border = isOpen ? '' : '2px solid blue'; // Debug border
        console.log(`Toggled ${dropdownId}: isOpen=${isOpen}, display=${content.style.display}`);
        
        // Close other dropdowns
        const dropdowns = ['catalogue', 'resources', 'careers', 'quizzes'];
        dropdowns.forEach(otherDropdown => {
            if (otherDropdown !== dropdownId) {
                const otherContent = document.getElementById(otherDropdown + '_content');
                const otherButton = document.getElementById(otherDropdown === 'catalogue' ? 'catalogue_btn' : 
                    otherDropdown === 'resources' ? 'resources_btn' : 
                    otherDropdown === 'careers' ? 'careers_btn' : 'Quiz_btn');
                if (otherContent && otherButton) {
                    otherContent.style.display = 'none';
                    otherButton.setAttribute('aria-expanded', 'false');
                    otherButton.style.border = '';
                }
            }
        });
    } catch (error) {
        console.error(`Error toggling ${dropdownId}:`, error);
    }
}

// Close dropdowns on outside click
document.addEventListener('DOMContentLoaded', () => {
    try {
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.dropdown')) {
                ['catalogue', 'resources', 'careers', 'quizzes'].forEach(dropdownId => {
                    const content = document.getElementById(dropdownId + '_content');
                    const button = document.getElementById(dropdownId === 'catalogue' ? 'catalogue_btn' : 
                        dropdownId === 'resources' ? 'resources_btn' : 
                        dropdownId === 'careers' ? 'careers_btn' : 'Quiz_btn');
                    if (content && button) {
                        content.style.display = 'none';
                        button.setAttribute('aria-expanded', 'false');
                        button.style.border = '';
                    }
                });
                console.log('Closed all dropdowns (clicked outside)');
            }
        });

        // Real-time phone number validation
        const phoneInput = document.getElementById('phone');
        const phoneError = document.getElementById('phone-error');
        if (phoneInput && phoneError) {
            phoneInput.addEventListener('input', function() {
                const phone = this.value.trim();
                if (phone === '') {
                    phoneError.style.display = 'none';
                    phoneError.textContent = '';
                } else if (!/^\+880\d{0,10}$/.test(phone)) {
                    phoneError.textContent = 'Phone number must start with +880 and be followed by 10 digits.';
                    phoneError.style.display = 'block';
                } else if (phone.length !== 13) {
                    phoneError.textContent = 'Phone number must be exactly 13 digits (including +880).';
                    phoneError.style.display = 'block';
                } else {
                    phoneError.style.display = 'none';
                    phoneError.textContent = '';
                }
                console.log('Phone input validated:', phone, phoneError.textContent);
            });
        } else {
            console.error('Phone input or error element not found');
        }
    } catch (error) {
        console.error('Error setting up outside click handler:', error);
    }
});

function toggleSidebar() {
    try {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
            console.log('Toggled sidebar:', sidebar.classList.contains('active') ? 'active' : 'inactive');
        } else {
            console.error('Sidebar element not found');
        }
    } catch (error) {
        console.error('Error toggling sidebar:', error);
    }
}

function showTab(tabId) {
    try {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
            tab.setAttribute('aria-hidden', 'true');
        });
        document.querySelectorAll('.sidebar button').forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });
        const targetTab = document.getElementById(tabId);
        if (targetTab) {
            targetTab.style.display = 'block';
            targetTab.setAttribute('aria-hidden', 'false');
        } else {
            console.error(`Tab with ID ${tabId} not found`);
        }
        const targetButton = document.querySelector(`button[aria-controls="${tabId}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
            targetButton.setAttribute('aria-selected', 'true');
        } else {
            console.error(`Button for tab ${tabId} not found`);
        }
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) sidebar.classList.remove('active');
        }
        console.log(`Switched to tab: ${tabId}`);
    } catch (error) {
        console.error('Error switching tab:', error);
    }
}

function showPreview() {
    try {
        const preview = document.getElementById('profilePreview');
        if (preview) {
            preview.style.display = 'flex';
            console.log('Opened profile preview');
        } else {
            console.error('Profile preview element not found');
        }
    } catch (error) {
        console.error('Error showing preview:', error);
    }
}

function closePreview() {
    try {
        const preview = document.getElementById('profilePreview');
        if (preview) {
            preview.style.display = 'none';
            console.log('Closed profile preview');
        } else {
            console.error('Profile preview element not found');
        }
    } catch (error) {
        console.error('Error closing preview:', error);
    }
}

function toggleTheme() {
    try {
        document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
        console.log('Toggled theme to', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
    } catch (error) {
        console.error('Error toggling theme:', error);
    }
}

if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
    console.log('Applied dark theme from localStorage');
}

try {
    const profileImageInput = document.getElementById('profile-image-input');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                console.log('Image selected: ', file.name, file.size, file.type);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const photoPreview = document.getElementById('photo-preview');
                    if (photoPreview) {
                        photoPreview.src = e.target.result;
                    } else {
                        console.error('Photo preview element not found');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        console.error('Profile image input not found');
    }
} catch (error) {
    console.error('Error setting up profile image input:', error);
}

try {
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(event) {
            console.log('Form submission attempted');
            let valid = true;
            const formType = event.submitter.value;
            console.log('Form type: ', formType);

            document.querySelectorAll('.error-text').forEach(error => {
                error.style.display = 'none';
                error.textContent = '';
            });

            if (formType === 'personal') {
                const username = document.getElementById('username')?.value.trim();
                const phone = document.getElementById('phone')?.value.trim();
                const dob = document.getElementById('dob')?.value;
                const bio = document.getElementById('bio')?.value.trim();
                const file = document.getElementById('profile-image-input')?.files[0];

                // Skip email validation as it's readonly
                const email = document.getElementById('email')?.value.trim();
                if (email && !email.includes('@') && !email.includes('.')) {
                    console.warn('Email validation skipped due to readonly status');
                }

                if (!username) {
                    const usernameError = document.getElementById('username-error');
                    if (usernameError) {
                        usernameError.textContent = 'Username is required.';
                        usernameError.style.display = 'block';
                    }
                    valid = false;
                } else if (/^\d/.test(username)) {
                    const usernameError = document.getElementById('username-error');
                    if (usernameError) {
                        usernameError.textContent = 'Username must not start with a number.';
                        usernameError.style.display = 'block';
                    }
                    valid = false;
                }

                if (phone && !/^\+880\d{10}$/.test(phone)) {
                    const phoneError = document.getElementById('phone-error');
                    if (phoneError) {
                        phoneError.textContent = 'Phone number must be a valid Bangladeshi number (+880 followed by 10 digits, total 13 digits).';
                        phoneError.style.display = 'block';
                    }
                    valid = false;
                }

                if (dob) {
                    const dobDate = new Date(dob);
                    const currentDate = new Date('2025-06-29'); // Current date: June 29, 2025
                    const ageInYears = (currentDate - dobDate) / (1000 * 60 * 60 * 24 * 365.25);
                    if (ageInYears < 10 || ageInYears > 100) {
                        const dobError = document.getElementById('dob-error');
                        if (dobError) {
                            dobError.textContent = 'You must be between 10 and 100 years old.';
                            dobError.style.display = 'block';
                        }
                        valid = false;
                    }
                }

                if (bio && bio.length > 250) {
                    const bioError = document.getElementById('bio-error');
                    if (bioError) {
                        bioError.textContent = 'Bio must be 250 characters or less.';
                        bioError.style.display = 'block';
                    }
                    valid = false;
                }

                if (file) {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        const bioError = document.getElementById('bio-error');
                        if (bioError) {
                            bioError.textContent = 'Invalid image format. Only JPG, PNG, GIF, or WebP allowed.';
                            bioError.style.display = 'block';
                        }
                        valid = false;
                    } else if (file.size > 1 * 1024 * 1024) {
                        const bioError = document.getElementById('bio-error');
                        if (bioError) {
                            bioError.textContent = 'Image must be under 1MB.';
                            bioError.style.display = 'block';
                        }
                        valid = false;
                    }
                }
            }

            if (!valid) {
                console.log('Form validation failed');
                event.preventDefault();
            } else {
                console.log('Form validation passed, submitting');
            }
        });
    } else {
        console.error('Profile form element not found');
    }
} catch (error) {
    console.error('Error setting up form submission:', error);
}