// Admin Panel JavaScript
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle for mobile
    const sidebarToggle = document.createElement('div');
    sidebarToggle.className = 'mobile-sidebar-toggle';
    sidebarToggle.innerHTML = '<ion-icon name="menu-outline"></ion-icon>';
    sidebarToggle.style.position = 'fixed';
    sidebarToggle.style.top = '20px';
    sidebarToggle.style.left = '20px';
    sidebarToggle.style.backgroundColor = 'var(--surface-color)';
    sidebarToggle.style.border = 'none';
    sidebarToggle.style.borderRadius = 'var(--border-radius)';
    sidebarToggle.style.padding = '10px';
    sidebarToggle.style.cursor = 'pointer';
    sidebarToggle.style.zIndex = '1000';
    sidebarToggle.style.display = 'none'; // Hidden by default, show on mobile

    document.body.appendChild(sidebarToggle);

    const sidebar = document.querySelector('.admin-sidebar');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open-mobile');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('open-mobile');
        }
    });

    // Responsive handling
    function handleResize() {
        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        } else {
            sidebarToggle.style.display = 'none';
            sidebar.classList.remove('open-mobile');
        }
    }

    // Initial check
    handleResize();

    // Listen for resize events
    window.addEventListener('resize', handleResize);

    // Add fade-in animation to elements
    const fadeElements = document.querySelectorAll('.stat-card, .recent-activity');
    fadeElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Socket.IO placeholder for real-time updates (would be implemented in a real system)
    console.log('Admin panel initialized');

    // Make functions available globally for debugging
    window.adminPanel = {
        // Placeholder for admin panel functions
        refreshStats: () => {
            console.log('Refreshing stats...');
            // In a real implementation, this would fetch updated stats via AJAX
        }
    };
});