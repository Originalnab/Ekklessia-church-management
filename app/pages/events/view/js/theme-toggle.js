// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function () {
    // Enable debug mode for theme changes
    const DEBUG = true;

    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;    // Set initial theme
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        document.body.classList.add('dark-mode');
        if (document.getElementById('themeToggle')) {
            document.getElementById('themeToggle').checked = true;
        }
        if (DEBUG) {
            if (savedTheme === 'dark') {
                console.log('[Theme] Initial theme: dark (user preference)');
            } else {
                console.log('[Theme] Initial theme: dark (system preference)');
            }
        }
    } else {
        if (DEBUG) {
            if (savedTheme === 'light') {
                console.log('[Theme] Initial theme: light (user preference)');
            } else {
                console.log('[Theme] Initial theme: light (system preference)');
            }
        }
    }

    // Add event listener to toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                if (DEBUG) console.log('[Theme] Switched to dark mode (user preference)');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                if (DEBUG) console.log('[Theme] Switched to light mode (user preference)');
            }

            // Force calendar to redraw
            if (window.calendar) {
                window.calendar.updateSize();
            }
        });
    }

    // Listen for system theme changes
    const colorSchemeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    colorSchemeMediaQuery.addEventListener('change', function (e) {
        // Only auto-update if user hasn't set a preference
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                document.body.classList.add('dark-mode');
                if (themeToggle) themeToggle.checked = true;
                if (DEBUG) console.log('[Theme] Switched to dark mode (system preference)');
            } else {
                document.body.classList.remove('dark-mode');
                if (themeToggle) themeToggle.checked = false;
                if (DEBUG) console.log('[Theme] Switched to light mode (system preference)');
            }

            // Force calendar to redraw
            if (window.calendar) {
                window.calendar.updateSize();
            }
        }
    });
});
