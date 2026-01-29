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

    // Article image fallback: replace broken/missing images with a styled placeholder
    (function() {
    function createImgPlaceholder(alt) {
        const div = document.createElement('div');
        div.className = 'img-placeholder';
        div.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="4" fill="#e2e8f0"/><path d="M8 13l2.5 3.5L15 11l4 6H5l3-4z" fill="#94a3b8"/></svg>
        <span>No Image</span>
        `;
        if (alt) div.title = alt;
        return div;
    }
    function handleImgError(e) {
        const img = e.target;
        if (!img.classList.contains('img-placeholder')) {
        const alt = img.alt || '';
        const ph = createImgPlaceholder(alt);
        ph.style.width = img.width ? img.width + 'px' : '';
        ph.style.height = img.height ? img.height + 'px' : '';
        img.replaceWith(ph);
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img.cp-article-image').forEach(img => {
        img.addEventListener('error', handleImgError);
        // If already broken (cached 404), trigger error
        if (!img.complete || img.naturalWidth === 0) {
            handleImgError({ target: img });
        }
        });
    });
    })();


    window.CarolinaPanorama.proxiedLeadConnectorUrl = function(originalUrl, width = 1200) {
        if (!originalUrl) return originalUrl;
        const normalized = window.CarolinaPanorama.normalizeUrl(originalUrl);
        if (!normalized) return normalized;
        const safe = encodeURI(normalized);
        return 'https://images.leadconnectorhq.com/image/f_webp/q_80/r_' + width + '/u_' + safe;
    };
    
    /**
     * Fetch metadata for a single article URL by scraping meta tags and common selectors.
     * Returns an object with url, title, description, image, author, date, categories.
     */
    window.CarolinaPanorama.fetchArticleMetadata = async function(url) {
        try {
            const response = await fetch(url);
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const getMetaContent = (property) => {
                const ogTag = doc.querySelector(`meta[property="${property}"]`);
                const nameTag = doc.querySelector(`meta[name="${property}"]`);
                return ogTag?.content || nameTag?.content || '';
            };

            const title = getMetaContent('og:title') ||
                getMetaContent('twitter:title') ||
                doc.querySelector('title')?.textContent ||
                'Article';

            const description = getMetaContent('og:description') ||
                getMetaContent('twitter:description') ||
                getMetaContent('description') ||
                '';

            const image = getMetaContent('og:image') ||
                getMetaContent('twitter:image') ||
                '

            const authorElement = doc.querySelector('.blog-author-name, [itemprop="author"]');
            const author = authorElement?.textContent?.trim() || 'Carolina Panorama';

            let dateStr = doc.querySelector('.blog-date')?.textContent?.trim();
            if (!dateStr) {
                const dateElement = doc.querySelector('[itemprop="datePublished"], time');
                dateStr = dateElement?.getAttribute('datetime') || dateElement?.textContent;
            }
            const date = dateStr ? new Date(dateStr) : new Date();

            const categoryElements = doc.querySelectorAll('.blog-category, [rel="category tag"]');
            const categories = Array.from(categoryElements)
                .map(el => el.textContent.trim().replace(/^\|\s*/, ''))
                .filter(Boolean);

            return {
                url: url,
                title: title,
                description: description,
                image: image,
                author: author,
                date: date,
                categories: categories.length > 0 ? categories : ['News']
            };
        } catch (error) {
            console.error('Error fetching article metadata:', error);
            return null;
        }
    };

    // proxiedLeadConnectorUrl already present as window.CarolinaPanorama.proxiedLeadConnectorUrl
    /**
     * Fetch articles from backend API and map to metadata objects.
     * @param {Object} params - { limit, offset, categoryUrlSlug }
     * @returns {Promise<Array>} Array of article metadata objects
     */
    window.CarolinaPanorama.fetchArticlesFromBackend = async function({
        limit = 10,
        offset = 0,
        categoryUrlSlug = null,
        tag = null,
        locationId = '9Iv8kFcMiUgScXzMPv23',
        blogId = 'iWSdkAQOuuRNrWiAHku1'
    } = {}) {
        const baseUrl = 'https://backend.leadconnectorhq.com/blogs/posts/list';
        const params = [
            `locationId=${encodeURIComponent(locationId)}`,
            `blogId=${encodeURIComponent(blogId)}`,
            `limit=${encodeURIComponent(limit)}`,
            `offset=${encodeURIComponent(offset)}`
        ];
        // Only one of tag or categoryUrlSlug can be used
        if (tag && !categoryUrlSlug) {
            // If tag is an array, join with comma, else use as is
            const tagValue = Array.isArray(tag) ? tag.join(',') : tag;
            params.push(`tag=${encodeURIComponent(tagValue)}`);
        } else if (categoryUrlSlug && !tag) {
            params.push(`categoryUrlSlug=${encodeURIComponent(categoryUrlSlug)}`);
        }
        const url = `${baseUrl}?${params.join('&')}`;
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Backend fetch failed: ${response.status}`);
            const data = await response.json();
            if (!data.blogPosts || !Array.isArray(data.blogPosts)) return [];
            return data.blogPosts.map(post => ({
                url: post.canonicalLink,
                title: post.title,
                description: post.description,
                image: post.imageUrl,
                author: post.author?.name || 'Unknown',
                date: post.publishedAt,
                categories: Array.isArray(post.categories) && post.categories.length > 0
                    ? post.categories.map(cat => cat.label)
                    : ['News']
            }));
        } catch (error) {
            console.error('Error fetching articles from backend:', error);
            return [];
        }
    };
    console.log('Carolina Panorama Global JS loaded');

    // Wait for Broadstreet JS library to load, then insert the ad zone
    (function loadBroadstreetAndSignalReady() {
    var bsScript = document.createElement('script');
    bsScript.src = 'https://cdn.broadstreetads.com/init-2.min.js';
    bsScript.onload = function() {
        if (window.broadstreet) {
        broadstreet.watch({ networkId: 10001 });
        document.dispatchEvent(new Event('broadstreet:ready'));
        }
    };
    document.head.appendChild(bsScript);
    })();   
})();

