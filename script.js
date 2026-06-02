document.addEventListener('DOMContentLoaded', () => {
    // Configuration
    // REPLACE THIS WITH YOUR VERCEL PROJECT URL
    const API_BASE = 'https://your-vercel-project-name.vercel.app/api';
    const EDIT_MODE_KEY = 'pixel_test_edit_mode';

    // State
    let currentTeamMemberId = null;
    let teamMembers = [];
    let isEditMode = localStorage.getItem(EDIT_MODE_KEY) === 'true';

    // DOM Elements
    const tabs = document.querySelectorAll('.tab');
    const sections = document.querySelectorAll('.section');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const teamSelectorBtn = document.getElementById('teamSelectorBtn');
    const teamList = document.getElementById('teamList');
    const closeSidebar = document.getElementById('closeSidebar');
    const teamGrid = document.getElementById('teamGrid');
    const teamMemberModal = document.getElementById('teamMemberModal');
    const closeTeamModal = document.getElementById('closeTeamModal');
    const modalTitle = document.getElementById('teamMemberModal').querySelector('.modal-title');
    const memberAvatarImg = document.getElementById('teamMemberModal').querySelector('.modal-avatar-img');
    const memberName = document.getElementById('teamMemberModal').querySelector('.member-name');
    const memberRole = document.getElementById('teamMemberModal').querySelector('.member-role');
    const memberBio = document.getElementById('teamMemberModal').querySelector('.member-bio');
    const memberSkillsContainer = document.getElementById('memberSkills');
    const memberPortfolioGrid = document.getElementById('memberPortfolio');
    const memberBlogList = document.getElementById('memberBlog');
    const modalTabBtns = document.querySelectorAll('.tab-btn');
    const modalTabPanes = document.querySelectorAll('.tab-pane');
    const editModeIndicator = document.getElementById('editModeIndicator');
    const exitEditModeBtn = document.getElementById('exitEditMode');
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Dynamic content containers
    const aboutContent = document.querySelector('[data-content="about_content"]');
    const aboutTitle = document.querySelector('[data-content="about_title"]');
    const teamTitle = document.querySelector('[data-content="team_title"]');

    // Initialize
    function init() {
        checkEditMode();
        loadTeamMembers();
        loadDynamicContent();
        setupEventListeners();
        handleHashChange();

        // Handle hash changes
        window.addEventListener('hashchange', handleHashChange);
    }

    // Check and set edit mode
    function checkEditMode() {
        isEditMode = localStorage.getItem(EDIT_MODE_KEY) === 'true';
        editModeIndicator.style.display = isEditMode ? 'flex' : 'none';

        // Add edit mode class to body for styling
        document.body.classList.toggle('edit-mode', isEditMode);
    }

    // Toggle edit mode
    function toggleEditMode() {
        isEditMode = !isEditMode;
        localStorage.setItem(EDIT_MODE_KEY, isEditMode);
        checkEditMode();

        // Show notification
        showNotification(isEditMode ? 'Edit mode enabled' : 'Edit mode disabled', isEditMode ? 'success' : 'info');

        // Reload content to reflect edit mode changes
        loadDynamicContent();
        loadTeamMembers();
    }

    // Setup event listeners
    function setupEventListeners() {
        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and sections
                tabs.forEach(t => t.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));

                // Add active class to clicked tab and corresponding section
                tab.classList.add('active');
                const sectionId = tab.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');

                // Update URL hash
                window.location.hash = sectionId;
            });
        });

        // Sidebar toggle
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            const icon = sidebarToggle.querySelector('ion-icon');
            icon.name = sidebar.classList.contains('open') ? 'chevron-back' : 'menu-outline';
        });

        // Close sidebar
        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('open');
            const icon = sidebarToggle.querySelector('ion-icon');
            icon.name = 'menu-outline';
        });

        // Team selector button (opens sidebar)
        teamSelectorBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            const icon = sidebarToggle.querySelector('ion-icon');
            icon.name = 'chevron-back';
        });

        // Close team member modal
        closeTeamModal.addEventListener('click', () => {
            teamMemberModal.style.display = 'none';
        });

        // Close modal when clicking outside
        teamMemberModal.addEventListener('click', (e) => {
            if (e.target === teamMemberModal) {
                teamMemberModal.style.display = 'none';
            }
        });

        // Modal tab switching
        modalTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all tabs and panes
                modalTabBtns.forEach(b => b.classList.remove('active'));
                modalTabPanes.forEach(p => p.classList.remove('active'));

                // Add active class to clicked tab and corresponding pane
                btn.classList.add('active');
                const tabId = btn.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Edit mode toggle
        exitEditModeBtn.addEventListener('click', toggleEditMode);

        // Portfolio filtering (keeping existing functionality)
        const filterBtns = document.querySelectorAll('.filter-btn');
        const projectItems = document.querySelectorAll('.project-item');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.getAttribute('data-filter');

                projectItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Testimonials modal (keeping existing functionality)
        const testimonialCards = document.querySelectorAll('.testimonial-card');
        const testimonialModal = document.getElementById('testimonialModal');
        const testimonialModalAvatar = testimonialModal.querySelector('.modal-avatar');
        const testimonialModalName = testimonialModal.querySelector('.modal-name');
        const testimonialModalText = testimonialModal.querySelector('.modal-text');
        const closeTestimonialModal = testimonialModal.querySelector('.close-modal');

        testimonialCards.forEach(card => {
            card.addEventListener('click', () => {
                const avatarSrc = card.querySelector('img').src;
                const name = card.querySelector('h4').textContent;
                const text = card.querySelector('p').textContent;

                testimonialModalAvatar.src = avatarSrc;
                testimonialModalName.textContent = name;
                testimonialModalText.textContent = text;

                testimonialModal.style.display = 'flex';
            });
        });

        closeTestimonialModal.addEventListener('click', () => {
            testimonialModal.style.display = 'none';
        });

        testimonialModal.addEventListener('click', (e) => {
            if (e.target === testimonialModal) {
                testimonialModal.style.display = 'none';
            }
        });

        // Contact form handling - UPDATED TO USE SERVERLESS FUNCTION
        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const formResponse = document.getElementById('formResponse');

        contactForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Prevent default form submission

            // Simple client-side validation (HTML5 validation is also present)
            const name = contactForm.name.value.trim();
            const email = contactForm.email.value.trim();
            const subject = contactForm.subject.value.trim();
            const message = contactForm.message.value.trim();

            if (!name || !email || !subject || !message) {
                showResponse('Please fill in all fields.', 'error');
                return;
            }

            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            // Prepare form data
            const formData = {
                name: name,
                email: email,
                subject: subject,
                message: message
            };

            // Send AJAX request to contact serverless function
            fetch(`${API_BASE}/contact`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResponse(data.message, 'success');
                    contactForm.reset(); // Reset form on success
                } else {
                    showResponse(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showResponse('Failed to connect to the server.', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            });
        });

        function showResponse(message, type) {
            formResponse.textContent = message;
            formResponse.className = `form-response ${type}`;
            formResponse.style.display = 'block';
            // Hide response after 5 seconds
            setTimeout(() => {
                formResponse.style.display = 'none';
            }, 5000);
        }
    }

    // Handle hash changes for direct linking
    function handleHashChange() {
        const hash = window.location.hash.substring(1); // Remove '#'
        const section = document.getElementById(hash);

        if (section && section.classList.contains('section')) {
            // Remove active class from all tabs and sections
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));

            // Add active class to matching tab and section
            const correspondingTab = document.querySelector(`.tab[data-section="${hash}"]`);
            if (correspondingTab) {
                correspondingTab.classList.add('active');
            }
            section.classList.add('active');
        }
    }

    // Show loading overlay
    function showLoading(show = true) {
        loadingOverlay.style.display = show ? 'flex' : 'none';
    }

    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Add to body
        document.body.appendChild(notification);

        // Remove after timeout
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Load team members from API
    async function loadTeamMembers() {
        showLoading(true);
        try {
            const response = await fetch(`${API_BASE}/team/`);
            if (!response.ok) {
                throw new Error('Failed to fetch team members');
            }

            const data = await response.json();
            if (data.success && data.data && data.data.team_members) {
                teamMembers = data.data.team_members;
                renderTeamList();
                renderTeamGrid();
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error loading team members:', error);
            showNotification('Failed to load team members', 'error');
        } finally {
            showLoading(false);
        }
    }

    // Render team list in sidebar
    function renderTeamList() {
        teamList.innerHTML = '';

        if (teamMembers.length === 0) {
            teamList.innerHTML = '<p>No team members found</p>';
            return;
        }

        teamMembers.forEach(member => {
            const memberElement = document.createElement('div');
            memberElement.className = `team-member-item${member.id === currentTeamMemberId ? ' active' : ''}`;
            memberElement.innerHTML = `
                <div class="member-avatar">
                    <img src="${member.avatar_url || 'https://via.placeholder.com/60'}" alt="${member.name}">
                </div>
                <div class="member-info">
                    <h4>${member.name}</h4>
                    <p>${member.role || 'Team Member'}</p>
                </div>
            `;

            memberElement.addEventListener('click', () => {
                setCurrentTeamMember(member.id);
            });

            teamList.appendChild(memberElement);
        });
    }

    // Render team grid
    function renderTeamGrid() {
        teamGrid.innerHTML = '';

        if (teamMembers.length === 0) {
            teamGrid.innerHTML = '<p>No team members found</p>';
            return;
        }

        teamMembers.forEach(member => {
            const card = document.createElement('div');
            card.className = 'team-member-card';
            card.innerHTML = `
                <div class="member-avatar">
                    <img src="${member.avatar_url || 'https://via.placeholder.com/100'}" alt="${member.name}">
                </div>
                <h3>${member.name}</h3>
                <p class="member-role">${member.role || 'Team Member'}</p>
                <p class="member-bio">${member.bio ? member.bio.substring(0, 100) + (member.bio.length > 100 ? '...' : '') : 'No bio available'}</p>
                ${isEditMode ? `
                <div class="edit-actions">
                    <button class="edit-btn" data-id="${member.id}" title="Edit member">
                        <ion-icon name="create-outline"></ion-icon>
                    </button>
                    <button class="delete-btn" data-id="${member.id}" title="Delete member">
                        <ion-icon name="trash-outline"></ion-icon>
                    </button>
                </div>
                ` : ''}
            `;

            // Add click event to view member details
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking edit/delete buttons
                if (!e.target.closest('.edit-btn') && !e.target.closest('.delete-btn')) {
                    setCurrentTeamMember(member.id);
                }
            });

            // Add edit button functionality
            const editBtn = card.querySelector('.edit-btn');
            if (editBtn) {
                editBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    editTeamMember(member.id);
                });
            }

            // Add delete button functionality
            const deleteBtn = card.querySelector('.delete-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteTeamMember(member.id);
                });
            }

            teamGrid.appendChild(card);
        });
    }

    // Set current team member and load their details
    function setCurrentTeamMember(id) {
        currentTeamMemberId = id;
        renderTeamList(); // Update active state in sidebar
        loadTeamMemberDetails(id);
    }

    // Load team member details for modal
    async function loadTeamMemberDetails(id) {
        showLoading(true);
        try {
            const response = await fetch(`${API_BASE}/team/${id}`);
            if (!response.ok) {
                throw new Error('Failed to fetch team member details');
            }

            const data = await response.json();
            if (data.success && data.data && data.data.team_member) {
                const member = data.data.team_member;
                renderTeamMemberModal(member);
                teamMemberModal.style.display = 'flex';
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            console.error('Error loading team member details:', error);
            showNotification('Failed to load team member details', 'error');
        } finally {
            showLoading(false);
        }
    }

    // Render team member modal
    function renderTeamMemberModal(member) {
        modalTitle.textContent = member.name;
        memberAvatarImg.src = member.avatar_url || 'https://via.placeholder.com/100';
        memberName.textContent = member.name;
        memberRole.textContent = member.role || 'Team Member';
        memberBio.textContent = member.bio || 'No bio available';

        // Render skills
        memberSkillsContainer.innerHTML = '';
        if (member.skills && Array.isArray(member.skills) && member.skills.length > 0) {
            member.skills.forEach(skill => {
                const skillElement = document.createElement('div');
                skillElement.className = 'skill-bar';
                skillElement.innerHTML = `
                    <div class="skill-label">${skill.name}</div>
                    <div class="skill-percent">${skill.level}%</div>
                    <div class="skill-progress">
                        <div class="skill-fill" style="width: ${skill.level}%"></div>
                    </div>
                `;
                memberSkillsContainer.appendChild(skillElement);
            });
        } else {
            memberSkillsContainer.innerHTML = '<p>No skills listed</p>';
        }

        // Render portfolio items
        memberPortfolioGrid.innerHTML = '';
        if (member.portfolio_items && Array.isArray(member.portfolio_items) && member.portfolio_items.length > 0) {
            member.portfolio_items.forEach(item => {
                const portfolioItem = document.createElement('div');
                portfolioItem.className = 'mini-project-item';
                portfolioItem.innerHTML = `
                    <img src="${item.image_url || 'https://via.placeholder.com/80'}" alt="${item.title}">
                    <div class="mini-project-info">
                        <h4>${item.title}</h4>
                        <p>${item.category}</p>
                    </div>
                `;
                memberPortfolioGrid.appendChild(portfolioItem);
            });
        } else {
            memberPortfolioGrid.innerHTML = '<p>No portfolio items</p>';
        }

        // Render blog posts
        memberBlogList.innerHTML = '';
        if (member.blog_posts && Array.isArray(member.blog_posts) && member.blog_posts.length > 0) {
            member.blog_posts.forEach(post => {
                const blogItem = document.createElement('div');
                blogItem.className = 'blog-item';
                blogItem.innerHTML = `
                    <h4>${post.title}</h4>
                    <p>${post.excerpt || post.content.substring(0, 100) + (post.content.length > 100 ? '...' : '')}</p>
                    <a href="${post.url || '#'}" class="read-more">Read more</a>
                `;
                memberBlogList.appendChild(blogItem);
            });
        } else {
            memberBlogList.innerHTML = '<p>No blog posts</p>';
        }
    }

    // Edit team member (placeholder for edit mode)
    function editTeamMember(id) {
        showNotification('Edit functionality would be implemented here', 'info');
        // In a real implementation, this would open a form to edit the team member
    }

    // Delete team member
    async function deleteTeamMember(id) {
        if (!confirm('Are you sure you want to delete this team member?')) {
            return;
        }

        showLoading(true);
        try {
            const response = await fetch(`${API_BASE}/team/${id}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error('Failed to delete team member');
            }

            const data = await response.json();
            if (data.success) {
                showNotification('Team member deleted successfully', 'success');
                loadTeamMembers(); // Reload team list

                // Close modal if deleting current member
                if (currentTeamMemberId === id) {
                    teamMemberModal.style.display = 'none';
                    currentTeamMemberId = null;
                }
            } else {
                throw new Error(data.message || 'Failed to delete team member');
            }
        } catch (error) {
            console.error('Error deleting team member:', error);
            showNotification('Failed to delete team member', 'error');
        } finally {
            showLoading(false);
        }
    }

    // Load dynamic content for sections
    async function loadDynamicContent() {
        showLoading(true);
        try {
            // Load About section content
            const aboutResponse = await fetch(`${API_BASE}/about/`);
            if (aboutResponse.ok) {
                const aboutData = await aboutResponse.json();
                if (aboutData.success && aboutData.data) {
                    // Set title
                    if (aboutData.data.title && aboutTitle) {
                        aboutTitle.textContent = aboutData.data.title;
                    }

                    // Set content - sanitize HTML to prevent XSS
                    if (aboutData.data.content && aboutContent) {
                        aboutContent.innerHTML = aboutData.data.content;
                    }
                }
            }

            // Load Team section title
            const teamResponse = await fetch(`${API_BASE}/team/title/`);
            if (teamResponse.ok) {
                const teamData = await teamResponse.json();
                if (teamData.success && teamData.data && teamData.data.title && teamTitle) {
                    teamTitle.textContent = teamData.data.title;
                }
            }

            // TODO: Load portfolio and blog content when endpoints are available
        } catch (error) {
            console.error('Error loading dynamic content:', error);
            // Don't show error for dynamic content as it's optional
        } finally {
            showLoading(false);
        }
    }

    // Initialize the application
    init();

    // Make functions globally available for debugging
    window.loadTeamMembers = loadTeamMembers;
    window.toggleEditMode = toggleEditMode;
});