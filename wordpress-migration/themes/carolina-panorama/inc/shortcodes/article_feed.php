<?php
/**
 * Shortcode: cp_article_feed
 * Widget: Article Feed
 */

function cp_shortcode_article_feed( $atts = [], $content = null, $tag = '' ) {
    // Enqueue dependencies
    wp_enqueue_script( 'cp-global-js' );
    wp_enqueue_style( 'cp-article-card-styles' );

    // Shortcode attributes with defaults
    $atts = shortcode_atts( [ 'category' => '', 'per_page' => 10, 'page' => 1 ], $atts, 'cp_article_feed' );

    ob_start();
    ?>
<!-- Article Feed Widget with Pagination and Filtering -->
<!-- Include shared-article-card-styles.css in your page -->

<style>
    .article-list-feed-wrapper {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: clamp(12px, 1.6vw, 20px);
    }
    .article-list-container {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .article-list-item {
        display: block;
    }
    .article-list-item .cp-article-card {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: clamp(20px, 2.5vw, 32px);
        align-items: center;
        padding: clamp(16px, 2vw, 24px) clamp(8px, 1vw, 10px);
    }
    .article-list-item .cp-article-card-link {
        display: contents;
    }
    .article-list-item .cp-article-content {
        padding: 0;
        gap: clamp(6px, 0.8vw, 8px);
        order: 1;
    }
    .article-list-item .cp-article-image {
        height: 180px;
        border-radius: 6px;
        order: 2;
    }
    .article-list-item .cp-article-title {
        font-size: clamp(1.125rem, 2vw, 1.375rem);
        line-height: 1.25;
        margin-bottom: clamp(6px, 0.8vw, 8px);
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-weight: 600;
    }
    .article-list-item .cp-article-meta {
        margin: 0;
        font-size: clamp(0.8rem, 1vw, 0.875rem);
    }
    .article-list-item .cp-article-description {
        font-size: clamp(0.875rem, 1.1vw, 0.9375rem);
        line-height: 1.5;
        margin: clamp(8px, 1vw, 10px) 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .article-list-item .cp-article-tags {
        margin-top: clamp(8px, 1vw, 10px);
    }
    .article-list-item .cp-article-read-more {
        margin-top: clamp(8px, 1vw, 10px);
        font-size: clamp(0.875rem, 1vw, 0.9375rem);
    }
    .article-list-item:not(:last-child) {
        border-bottom: 1px solid #e5e7eb;
    }
    @media (max-width: 768px) {
        .article-list-item .cp-article-card {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .article-list-item .cp-article-content {
            order: 2;
        }
        .article-list-item .cp-article-image {
            order: 1;
            height: 200px;
        }
        .article-list-item .cp-article-title {
            font-size: 1.125rem;
        }
        .article-list-item .cp-article-description {
            -webkit-line-clamp: 2;
        }
    }
    .article-feed-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        margin: 24px 0 0 0;
        min-height: 48px;
    }

    .article-feed-pagination .page-count {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        min-width: 60px;
        text-align: center;
        height: 40px;
    }
    
    .article-feed-pagination button {
        width: 48px;
        height: 40px;
        border: none;
        background: #3b82f6;
        color: #fff;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .article-feed-pagination button:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Header container styles for all four states */
    #category-header-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 16px auto 24px auto;
        max-width: 800px;
    }
    
    #category-header-container #category-title {
        margin: 0;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    /* Label above the title (for tag and author) */
    #category-header-container .header-label {
        font-size: 0.875rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 6px;
        opacity: 0.9;
        display: block;
    }
    
    /* Main title styling */
    #category-header-container .header-title {
        font-size: 2.2rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: 'Georgia', serif;
        line-height: 1.2;
        display: block;
    }
    
    /* Tag and Author states - Carolina Navy Blue background with gradient */
    #category-header-container.tag-header,
    #category-header-container.author-header {
        background: linear-gradient(to bottom, #0055aa, #003366);
        color: #fff;
        border-radius: 12px;
        padding: 20px 40px;
    }
    
    /* Category state - uses existing cp-article-tag colors with gradient */
    #category-header-container.cp-article-tag {
        color: #fff;
        border-radius: 12px;
        padding: 20px 40px;
        position: relative;
        overflow: hidden;
    }
    
    #category-header-container.cp-article-tag::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(255,255,255,0.15), rgba(0,0,0,0.2));
        pointer-events: none;
    }
    
    /* Latest/All state - keep original styling */
    #category-header-container.latest-header #category-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
        padding-bottom: 20px;
        border-bottom: 3px solid #2563eb;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-family: 'Georgia', serif;
    }
</style>

<div class="article-list-feed-wrapper">
    <div id="category-header-container">
        <h1 id="category-title"></h1>
    </div>
    <div class="article-list-container" id="article-feed-container">
        <!-- Articles will be rendered here -->
    </div>
    <div class="article-feed-pagination" id="article-feed-pagination">
        <!-- Pagination buttons will be rendered here -->
    </div>
</div>

<script>
// Wait for CarolinaPanorama global before running widget logic
function waitForCarolinaPanorama(callback, timeout = 5000) {
    const start = Date.now();
    (function check() {
        if (window.CarolinaPanorama) {
            callback();
        } else if (Date.now() - start < timeout) {
            setTimeout(check, 30);
        } else {
            console.error('CarolinaPanorama global not found.');
        }
    })();
}

waitForCarolinaPanorama(function() {
    const container  = document.getElementById('article-feed-container');
    const pagination = document.getElementById('article-feed-pagination');
    const ARTICLES_PER_PAGE = 10;
    let currentPage  = 1;
    let totalCount   = 0;

    // WP REST API base (injected by PHP via CarolinaPanoramaConfig)
    const restBase = window.CarolinaPanorama.WP_REST_URL; // e.g. /wp-json
    const restNonce = window.CarolinaPanorama.WP_NONCE || '';

    // ── URL filter helpers ──────────────────────────────────────────────────

    function slugify(str) {
        return String(str || '').toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
    }

    function getFilterFromUrl() {
        const path        = window.location.pathname;
        const queryParams = new URLSearchParams(window.location.search);

        const matchCategory = path.match(/\/articles\/category\/([^\/]+)/);
        const matchTag      = path.match(/\/articles\/tag\/([^\/]+)/);
        const matchAuthor   = path.match(/\/articles\/author\/([^\/]+)/);

        if (matchCategory) return { categorySlug: decodeURIComponent(matchCategory[1]) };
        if (matchTag)      return { tag:          decodeURIComponent(matchTag[1]) };
        if (matchAuthor)   return { authorSlug:   decodeURIComponent(matchAuthor[1]) };

        if (queryParams.has('category')) return { categorySlug: queryParams.get('category') };
        if (queryParams.has('tag'))      return { tag:          queryParams.get('tag') };
        if (queryParams.has('author'))   return { authorSlug:   queryParams.get('author') };

        return {};
    }

    // ── WP REST fetchers ────────────────────────────────────────────────────

    const restHeaders = restNonce ? { 'X-WP-Nonce': restNonce } : {};

    // Resolve a category slug → { id, name, description, color_code }
    async function resolveCategoryBySlug(slug) {
        try {
            const res  = await fetch(`${restBase}/wp/v2/categories?slug=${encodeURIComponent(slug)}&_fields=id,name,slug,description,meta`, { headers: restHeaders });
            const data = await res.json();
            if (!Array.isArray(data) || !data.length) return null;
            const cat = data[0];
            return {
                id:          cat.id,
                name:        cat.name,
                slug:        cat.slug,
                description: cat.description || '',
                color_code:  (cat.meta && cat.meta._color_code) ? cat.meta._color_code : '',
            };
        } catch (e) {
            console.error('[Article Feed] Failed to resolve category:', e);
            return null;
        }
    }

    // Resolve an author slug → { id, name, description }
    async function resolveAuthorBySlug(slug) {
        try {
            const res  = await fetch(`${restBase}/wp/v2/users?slug=${encodeURIComponent(slug)}&_fields=id,name,slug,description`, { headers: restHeaders });
            const data = await res.json();
            if (!Array.isArray(data) || !data.length) return null;
            return { id: data[0].id, name: data[0].name, bio: data[0].description || '' };
        } catch (e) {
            console.error('[Article Feed] Failed to resolve author:', e);
            return null;
        }
    }

    // Fetch articles page from WP REST API
    async function fetchArticles(page = 1) {
        const filter = getFilterFromUrl();
        const params = new URLSearchParams();
        params.set('post_type', 'article');   // custom post type
        params.set('per_page', String(ARTICLES_PER_PAGE));
        params.set('page', String(page));
        params.set('_embed', '1');            // embeds featured media + author + terms
        params.set('_fields', 'id,title,excerpt,slug,date,link,_embedded,_links');

        let categoryDetails = null;
        let authorDetails   = null;

        if (filter.categorySlug) {
            const cat = await resolveCategoryBySlug(filter.categorySlug);
            if (cat) {
                params.set('categories', String(cat.id));
                categoryDetails = cat;
            }
        }

        if (filter.tag) {
            // Tags: resolve slug → id
            try {
                const tagSlug = slugify(filter.tag);
                const res  = await fetch(`${restBase}/wp/v2/tags?slug=${encodeURIComponent(tagSlug)}&_fields=id,name`, { headers: restHeaders });
                const data = await res.json();
                if (Array.isArray(data) && data.length) {
                    params.set('tags', String(data[0].id));
                }
            } catch(e) { /* ignore */ }
        }

        if (filter.authorSlug) {
            const author = await resolveAuthorBySlug(filter.authorSlug);
            if (author) {
                params.set('author', String(author.id));
                authorDetails = author;
            }
        }

        // WP REST for CPTs uses /wp/v2/{post_type} where slug == CPT name
        const url = `${restBase}/wp/v2/article?${params.toString()}`;
        const res = await fetch(url, { headers: restHeaders });

        // WP sends total count in headers
        totalCount = parseInt(res.headers.get('X-WP-Total') || '0', 10);

        const data = await res.json();
        if (!Array.isArray(data)) {
            return { articles: [], categoryDetails, authorDetails };
        }

        const placeholder = 'https://storage.googleapis.com/msgsndr/9Iv8kFcMiUgScXzMPv23/media/697bd8644d56831c95c3248d.svg';

        const articles = data.map(post => {
            // Featured image from _embedded
            const mediaArr = post._embedded?.['wp:featuredmedia'] || [];
            const image    = mediaArr[0]?.source_url || placeholder;

            // Author from _embedded
            const authorArr = post._embedded?.author || [];
            const author    = authorArr[0]?.name || 'Carolina Panorama';

            // Categories from _embedded
            const termsArr  = post._embedded?.['wp:term'] || [];
            const cats      = (termsArr[0] || []).map(t => t.name);

            return {
                url:         post.link,
                title:       post.title?.rendered || '',
                description: (post.excerpt?.rendered || '').replace(/<[^>]*>/g, ''),
                image,
                author,
                date:        post.date,
                categories:  cats.length ? cats : ['News'],
            };
        });

        return { articles, categoryDetails, authorDetails };
    }

    // ── Rendering ───────────────────────────────────────────────────────────

    async function renderArticles(articles) {
        if (!articles || !articles.length) {
            container.innerHTML = '<p style="text-align:center;color:#666;">No articles found.</p>';
            return;
        }
        const cardsHTML = await Promise.all(articles.map((data, index) => createArticleCard(data, index)));
        container.innerHTML = cardsHTML.join('');
    }

    function renderPagination(page, total) {
        const prevDisabled  = page === 1;
        const nextDisabled  = total <= page * ARTICLES_PER_PAGE;
        const totalPages    = Math.max(1, Math.ceil(total / ARTICLES_PER_PAGE));
        pagination.innerHTML = `
            <button id="feed-prev" ${prevDisabled ? 'disabled' : ''} aria-label="Previous Page">&laquo;</button>
            <span class="page-count">${page} / ${totalPages}</span>
            <button id="feed-next" ${nextDisabled ? 'disabled' : ''} aria-label="Next Page">&raquo;</button>
        `;
        const debouncedChangePage = window.CarolinaPanorama.debounce(changePage, 300);
        document.getElementById('feed-prev').onclick = () => { if (!prevDisabled) debouncedChangePage(page - 1); };
        document.getElementById('feed-next').onclick = () => { if (!nextDisabled) debouncedChangePage(page + 1); };
    }

    async function createArticleCard(data, index) {
        const categoryTagsHTML = await Promise.all(
            data.categories.slice(0, 2).map(async cat => {
                const cls   = cat.toLowerCase().replace(/\s+/g, '-');
                const style = await window.CarolinaPanorama.getCategoryStyle(cat);
                return `<span class="cp-article-tag ${cls}" style="${style}">${cat}</span>`;
            })
        );
        const imageUrl   = data.image || 'https://storage.googleapis.com/msgsndr/9Iv8kFcMiUgScXzMPv23/media/697bd8644d56831c95c3248d.svg';
        const loadingAttr = index >= 3 ? 'loading="lazy"' : 'loading="eager"';
        return `
            <div class="article-list-item">
                <article class="cp-article-card">
                    <a href="${data.url}" class="cp-article-card-link">
                        <img src="${imageUrl}" alt="${data.title}" class="cp-article-image" ${loadingAttr}>
                        <div class="cp-article-content">
                            <h2 class="cp-article-title">${data.title}</h2>
                            <div class="cp-article-meta">
                                <span class="cp-article-author">${data.author}</span>
                                <span class="cp-article-date">${formatDate(data.date)}</span>
                            </div>
                            <p class="cp-article-description">${data.description}</p>
                            <div class="cp-article-tags">${categoryTagsHTML.join('')}</div>
                            <span class="cp-article-read-more">Read More</span>
                        </div>
                    </a>
                </article>
            </div>
        `;
    }

    function formatDate(date) {
        const options = { month: 'numeric', day: 'numeric', year: 'numeric' };
        return 'Published on: ' + new Date(date).toLocaleDateString('en-US', options);
    }

    // ── Page change & header ────────────────────────────────────────────────

    async function changePage(page) {
        currentPage = page;
        const { articles, categoryDetails, authorDetails } = await fetchArticles(page);
        await renderArticles(articles);
        renderPagination(page, totalCount);

        const headerContainer = document.getElementById('category-header-container');
        const headerTitle     = document.getElementById('category-title');
        const filter          = getFilterFromUrl();

        headerContainer.className = '';
        headerContainer.removeAttribute('style');

        if (categoryDetails) {
            const cls = categoryDetails.name.toLowerCase().replace(/\s+/g, '-');
            headerContainer.className = 'cp-article-tag ' + cls;
            if (categoryDetails.color_code) {
                headerContainer.setAttribute('style', `background-color:${categoryDetails.color_code};`);
            } else {
                const style = await window.CarolinaPanorama.getCategoryStyle(categoryDetails.name);
                if (style) headerContainer.setAttribute('style', style);
            }
            headerTitle.innerHTML = `<span class="header-title">${categoryDetails.name}</span>`;

            const desc     = categoryDetails.description || `Browse the latest ${categoryDetails.name} articles from Carolina Panorama`;
            const keywords = [categoryDetails.name, 'Carolina Panorama', 'news', 'articles'].join(', ');
            window.CarolinaPanorama.setPageMeta({
                title:       `${categoryDetails.name} Articles | Carolina Panorama`,
                description: desc,
                keywords,
                url:  window.location.href,
                type: 'website',
            });
        } else if (filter.tag) {
            headerContainer.className = 'tag-header';
            headerTitle.innerHTML = `<span class="header-label">Tagged Entity</span><span class="header-title">${filter.tag}</span>`;
            window.CarolinaPanorama.setPageMeta({
                title:       `Articles tagged: ${filter.tag} | Carolina Panorama`,
                description: `Explore articles about ${filter.tag} from Carolina Panorama`,
                keywords:    `${filter.tag}, Carolina Panorama, tags, topics`,
                url:  window.location.href,
                type: 'website',
            });
        } else if (filter.authorSlug) {
            const authorName = authorDetails ? authorDetails.name : filter.authorSlug;
            const authorBio  = authorDetails ? authorDetails.bio  : '';
            headerContainer.className = 'author-header';
            headerTitle.innerHTML = `<span class="header-label">Written by:</span><span class="header-title">${authorName}</span>`;
            window.CarolinaPanorama.setPageMeta({
                title:       `Articles by ${authorName} | Carolina Panorama`,
                description: authorBio || `Read articles written by ${authorName} at Carolina Panorama`,
                keywords:    `${authorName}, author, Carolina Panorama, articles`,
                url:  window.location.href,
                type: 'profile',
            });
        } else {
            headerContainer.className = 'latest-header';
            headerTitle.textContent = 'Latest Articles';
            window.CarolinaPanorama.setPageMeta({
                title:       'Latest Articles | Carolina Panorama',
                description: 'Browse the latest news and articles from Carolina Panorama',
                keywords:    'Carolina Panorama, latest news, articles, updates',
                url:  window.location.href,
                type: 'website',
            });
        }
    }

    // Initial load
    changePage(1);
});
</script>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode( 'cp_article_feed', 'cp_shortcode_article_feed' );
