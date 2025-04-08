/**
 * Dark Mode Functionality for Cluster7
 * 
 * This file handles dark mode toggle and preference storage.
 * Being separate makes it easy to modify or remove from the project.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode based on saved preference
    initDarkMode();
    
    // Add toggle event listener
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', toggleDarkMode);
    }
});

/**
 * Initialize dark mode based on user preference
 */
function initDarkMode() {
    // Check saved preference
    const darkModeEnabled = localStorage.getItem('darkModeEnabled') === 'true';
    
    // Apply theme based on saved preference
    if (darkModeEnabled) {
        document.documentElement.classList.add('dark-theme');
        updateToggleButton(true);
    } else {
        document.documentElement.classList.remove('dark-theme');
        updateToggleButton(false);
    }
}

/**
 * Toggle dark mode on/off
 */
function toggleDarkMode() {
    const isDarkMode = document.documentElement.classList.contains('dark-theme');
    
    if (isDarkMode) {
        // Switch to light mode
        document.documentElement.classList.remove('dark-theme');
        localStorage.setItem('darkModeEnabled', 'false');
        updateToggleButton(false);
    } else {
        // Switch to dark mode
        document.documentElement.classList.add('dark-theme');
        localStorage.setItem('darkModeEnabled', 'true');
        updateToggleButton(true);
    }
}

/**
 * Update toggle button display
 */
function updateToggleButton(isDarkMode) {
    const moonIcon = document.getElementById('moon-icon');
    const sunIcon = document.getElementById('sun-icon');
    
    if (moonIcon && sunIcon) {
        if (isDarkMode) {
            moonIcon.classList.add('hidden');
            sunIcon.classList.remove('hidden');
        } else {
            moonIcon.classList.remove('hidden');
            sunIcon.classList.add('hidden');
        }
    }
}