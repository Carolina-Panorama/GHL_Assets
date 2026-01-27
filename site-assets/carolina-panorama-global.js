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

    // Normalize and proxy image URLs via LeadConnector image proxy
    window.CarolinaPanorama.normalizeUrl = function(url) {
        if (!url) return url;
        url = String(url).trim();
        if (!/^https?:\/\//i.test(url)) url = 'https://' + url.replace(/^\/+/, '');
        url = url.replace(/^http:\/\//i, 'https://');

        // Try to decode one level of double-encoding if present
        try {
            if (/%25/.test(url)) {
                const decoded = decodeURIComponent(url);
                if (/^https?:\/\//i.test(decoded)) url = decoded;
            }
        } catch (e) {
            // ignore decode errors
        }

        return url;
    };

    window.CarolinaPanorama.proxiedLeadConnectorUrl = function(originalUrl, width = 1200) {
        if (!originalUrl) return originalUrl;
        const normalized = window.CarolinaPanorama.normalizeUrl(originalUrl);
        if (!normalized) return normalized;
        const safe = encodeURI(normalized);
        return 'https://images.leadconnectorhq.com/image/f_webp/q_80/r_' + width + '/u_' + safe;
    };
    
    console.log('Carolina Panorama Global JS loaded');
})();
