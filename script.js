document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.tab');
    const sections = document.querySelectorAll('.section');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and sections
            tabs.forEach(t => t.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            // Add active class to clicked tab and corresponding section
            tab.classList.add('active');
            const sectionId = tab.getAttribute('data-section');
            document.getElementById(sectionId).classList.add('active');
        });
    });

    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        // Change icon based on sidebar state
        const icon = sidebarToggle.querySelector('ion-icon');
        if (sidebar.classList.contains('open')) {
            icon.name = 'chevron-back';
        } else {
            icon.name = 'chevron-forward';
        }
    });

    // Portfolio filtering
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectItems = document.querySelectorAll('.project-item');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active filter button
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.getAttribute('data-filter');
            // Show/hide project items based on filter
            projectItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Testimonials modal
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    const modal = document.getElementById('testimonialModal');
    const modalAvatar = modal.querySelector('.modal-avatar');
    const modalName = modal.querySelector('.modal-name');
    const modalText = modal.querySelector('.modal-text');
    const closeModal = modal.querySelector('.close-modal');

    testimonialCards.forEach(card => {
        card.addEventListener('click', () => {
            // Get data from the clicked card
            const avatarSrc = card.querySelector('img').src;
            const name = card.querySelector('h4').textContent;
            const text = card.querySelector('p').textContent;
            // Set modal content
            modalAvatar.src = avatarSrc;
            modalName.textContent = name;
            modalText.textContent = text;
            // Show modal
            modal.style.display = 'flex';
        });
    });

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Contact form handling
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
        const formData = new FormData(contactForm);

        // Send AJAX request to contact.php
        fetch('contact.php', {
            method: 'POST',
            body: formData
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
});