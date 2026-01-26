/**
 * Carolina Panorama Global JavaScript
 * Shared utilities and functions for Carolina Panorama widgets
 */

// Initialize when DOM is ready
(function() {
    'use strict';
    
    // Utility: Format date for display
    window.CarolinaPanorama = window.CarolinaPanorama || {};
    
    window.CarolinaPanorama.formatDate = function(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };
    
    // Utility: Truncate text to specified length
    window.CarolinaPanorama.truncateText = function(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength).trim() + '...';
    };
    
    // Utility: Get category class for styling
    window.CarolinaPanorama.getCategoryClass = function(category) {
        if (!category) return '';
        return category.toLowerCase().replace(/\s+/g, '-');
    };
    
    // Apply color scheme to blog tags based on content
    function applyTagColors() {
        const tags = document.querySelectorAll('.blog-tag');
        
        tags.forEach(tag => {
            const text = tag.textContent.trim().toLowerCase();
            
            // Remove existing color classes
            tag.style.removeProperty('background-color');
            
            // Apply colors based on category name
            if (text.includes('business')) {
                tag.style.backgroundColor = '#10b981';
            } else if (text.includes('politics')) {
                tag.style.backgroundColor = '#8b5cf6';
            } else if (text.includes('sports')) {
                tag.style.backgroundColor = '#f59e0b';
            } else if (text.includes('education')) {
                tag.style.backgroundColor = '#06b6d4';
            } else if (text.includes('health')) {
                tag.style.backgroundColor = '#ec4899';
            } else if (text.includes('local')) {
                tag.style.backgroundColor = '#3b82f6';
            }
            // Default blue is already set in CSS
        });
    }
    
    // Run on page load
    applyTagColors();
    
    // Re-run when content changes (for dynamic content)
    const observer = new MutationObserver(applyTagColors);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('Carolina Panorama Global JS loaded');
})();
