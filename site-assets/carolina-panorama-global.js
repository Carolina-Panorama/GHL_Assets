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
    
    // Apply color scheme to tags (covers both .blog-tag and .cp-article-tag)
    function applyTagColors(root = document) {
        const tags = root.querySelectorAll('.blog-tag, .cp-article-tag');

        tags.forEach(tag => {
            const text = (tag.textContent || '').trim().toLowerCase();

            // Reset any previously applied inline styles
            tag.style.backgroundColor = '';
            tag.style.color = '';

            // Apply colors based on category name
            if (text.includes('business')) {
                tag.style.backgroundColor = '#10b981';
                tag.style.color = '#fff';
            } else if (text.includes('politics')) {
                tag.style.backgroundColor = '#8b5cf6';
                tag.style.color = '#fff';
            } else if (text.includes('sports')) {
                tag.style.backgroundColor = '#f59e0b';
                tag.style.color = '#111';
            } else if (text.includes('education')) {
                tag.style.backgroundColor = '#06b6d4';
                tag.style.color = '#fff';
            } else if (text.includes('health')) {
                tag.style.backgroundColor = '#ec4899';
                tag.style.color = '#111';
            } else if (text.includes('local')) {
                tag.style.backgroundColor = '#3b82f6';
                tag.style.color = '#fff';
            }
            // If no match, leave defaults from CSS
        });
    }
    
    // Run on page load
    applyTagColors();

    // Re-run when content changes (for dynamic content)
    const observer = new MutationObserver(muts => {
        muts.forEach(m => {
            m.addedNodes.forEach(n => {
                if (n.nodeType === 1) applyTagColors(n);
            });
        });
    });
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('Carolina Panorama Global JS loaded');
})();
