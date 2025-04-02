document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const menuOverlay = document.querySelector('.menu-overlay');

    toggleSidebar.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        mainContent.classList.toggle('expanded');

        // Mobile menu overlay
        if (window.innerWidth <= 768) {
            menuOverlay.classList.toggle('active');
        }
    });

    // Responsive design handling
    function handleResponsiveLayout() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('sidebar-collapsed');
            mainContent.classList.add('expanded');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            mainContent.classList.remove('expanded');
        }
    }

    // Initial responsive check
    handleResponsiveLayout();

    // Recheck on window resize
    window.addEventListener('resize', handleResponsiveLayout);

    // Close mobile menu when clicking outside
    if (menuOverlay) {
        menuOverlay.addEventListener('click', function() {
            sidebar.classList.add('sidebar-collapsed');
            mainContent.classList.add('expanded');
            menuOverlay.classList.remove('active');
        });
    }
});