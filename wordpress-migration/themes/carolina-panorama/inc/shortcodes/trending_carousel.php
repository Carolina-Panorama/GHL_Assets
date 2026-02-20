<?php
/**
 * Shortcode: cp_trending_carousel
 * Widget: Trending Carousel
 *
 * Renders recent articles from the native 'article' CPT — no external
 * API calls.
 */

function cp_shortcode_trending_carousel( $atts = [], $content = null, $tag = '' ) {
    wp_enqueue_style( 'cp-article-card-styles' );

    $atts = shortcode_atts( [ 'limit' => 10 ], $atts, 'cp_trending_carousel' );
    $limit = max( 1, intval( $atts['limit'] ) );

    // Primary sort: view count descending (most-viewed first).
    // The meta_query OUTER relation=OR ensures articles with no view count yet
    // are still included (they sort to the bottom, then by date).
    $q = new WP_Query( [
        'post_type'      => 'article',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation'  => 'OR',
            'has_views' => [
                'key'     => '_view_count',
                'type'    => 'NUMERIC',
                'compare' => 'EXISTS',
            ],
            'no_views'  => [
                'key'     => '_view_count',
                'compare' => 'NOT EXISTS',
            ],
        ],
        'orderby'        => [
            'has_views' => 'DESC',  // numeric sort on _view_count for articles that have it
            'date'      => 'DESC',  // new articles (no count yet) fall back to most-recent
        ],
    ] );

    if ( ! $q->have_posts() ) {
        return '';
    }

    $placeholder = 'https://storage.googleapis.com/msgsndr/9Iv8kFcMiUgScXzMPv23/media/697bd8644d56831c95c3248d.svg';

    ob_start();
    ?>
<!-- Trending/Editor's Picks Carousel Widget -->
<style>
    .trending-section-wrapper {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .trending-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .trending-section-icon { font-size: 1.5rem; }

    .trending-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .trending-carousel-container {
        position: relative;
        overflow: hidden;
    }

    .trending-carousel-track {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
        padding-bottom: 10px;
    }

    .trending-carousel-track::-webkit-scrollbar       { height: 8px; }
    .trending-carousel-track::-webkit-scrollbar-track { background: #f7fafc; border-radius: 4px; }
    .trending-carousel-track::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }
    .trending-carousel-track::-webkit-scrollbar-thumb:hover { background: #a0aec0; }

    .trending-carousel-item {
        flex: 0 0 320px;
        min-width: 320px;
        max-width: 320px;
    }

    .trending-carousel-item .cp-article-image { height: 200px; }

    @media (max-width: 768px) {
        .trending-carousel-item {
            flex: 0 0 280px;
            min-width: 280px;
            max-width: 280px;
        }
        .trending-carousel-item .cp-article-image { height: 180px; }
    }
</style>

<div class="trending-section-wrapper">
    <div class="trending-carousel-container">
        <div class="trending-carousel-track">
            <?php while ( $q->have_posts() ) : $q->the_post();
                $post_id    = get_the_ID();
                $image      = cp_article_thumbnail_fallback_url( $post_id ) ?: $placeholder;
                $image_alt  = cp_article_thumbnail_fallback_alt( $post_id ) ?: get_the_title();
                $url        = get_permalink();
                $cats       = get_the_terms( $post_id, 'category' ) ?: [];
                $first_cat  = ! empty( $cats ) ? $cats[0] : null;
                $cat_name   = $first_cat ? $first_cat->name  : 'News';
                $cat_slug   = $first_cat ? $first_cat->slug  : 'news';
                $cat_color  = $first_cat ? ( get_term_meta( $first_cat->term_id, '_color_code', true ) ?: '#3b82f6' ) : '#3b82f6';
                $author     = get_the_author();
                $date       = get_the_date( 'M j, Y' );
            ?>
            <div class="trending-carousel-item">
                <article class="cp-article-card cp-article-card-medium">
                    <a href="<?php echo esc_url( $url ); ?>" class="cp-article-card-link">
                        <img src="<?php echo esc_url( $image ); ?>"
                             alt="<?php echo esc_attr( $image_alt ); ?>"
                             class="cp-article-image" loading="lazy">
                        <div class="cp-article-content">
                            <div class="cp-article-tags">
                                <span class="cp-article-tag <?php echo esc_attr( sanitize_html_class( $cat_slug ) ); ?>"
                                      style="background-color:<?php echo esc_attr( $cat_color ); ?> !important;">
                                    <?php echo esc_html( $cat_name ); ?>
                                </span>
                            </div>
                            <h3 class="cp-article-title"><?php the_title(); ?></h3>
                            <div class="cp-article-meta">
                                <span class="cp-article-author"><?php echo esc_html( $author ); ?></span>
                                <span class="cp-article-date"><?php echo esc_html( $date ); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</div>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode( 'cp_trending_carousel', 'cp_shortcode_trending_carousel' );


    ob_start();
    ?>
<!-- Trending/Editor's Picks Carousel Widget -->
<!-- Include shared-article-card-styles.css in your page -->
<!-- Define window.TRENDING_ARTICLES in head tracking code with article URLs -->

<style>
    .trending-section-wrapper {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .trending-section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .trending-section-icon {
        font-size: 1.5rem;
    }

    .trending-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .trending-carousel-container {
        position: relative;
        overflow: hidden;
    }

    .trending-carousel-track {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
        padding-bottom: 10px;
    }

    .trending-carousel-track::-webkit-scrollbar {
        height: 8px;
    }

    .trending-carousel-track::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 4px;
    }

    .trending-carousel-track::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }

    .trending-carousel-track::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    .trending-carousel-item {
        flex: 0 0 320px;
        min-width: 320px;
        max-width: 320px;
    }

    .trending-carousel-item .cp-article-image {
        height: 200px;
    }

    @media (max-width: 768px) {
        .trending-carousel-item {
            flex: 0 0 280px;
            min-width: 280px;
            max-width: 280px;
        }

        .trending-carousel-item .cp-article-image {
            height: 180px;
        }
    }
</style>

<div class="trending-section-wrapper">
    <div class="trending-carousel-container">
        <div class="trending-carousel-track" id="trending-track">
            <!-- Articles will be inserted here -->
        </div>
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
        const track = document.getElementById('trending-track');
        const apiBase = window.CarolinaPanorama.API_BASE_URL || 'https://cms.carolinapanorama.org';

        function formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                const options = { month: 'short', day: 'numeric', year: 'numeric' };
                return new Date(dateStr).toLocaleDateString('en-US', options);
            } catch (e) {
                return dateStr;
            }
        }

        function slugify(str) {
            return String(str || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-');
        }

        // Create article card HTML
        async function createArticleCard(article) {
            const categoryTag = article.categories?.[0]?.name || 'News';
            const categoryClass = slugify(categoryTag);
            const style = await window.CarolinaPanorama.getCategoryStyle(categoryTag);
            const placeholderUrl = 'https://storage.googleapis.com/msgsndr/9Iv8kFcMiUgScXzMPv23/media/697bd8644d56831c95c3248d.svg';
            const imageUrl = article.featured_image || placeholderUrl;
            const authorName = article.author?.name || 'Carolina Panorama';
            
            return `
                <div class="trending-carousel-item">
                    <article class="cp-article-card cp-article-card-medium">
                        <a href="${article.url}" class="cp-article-card-link">
                            <img src="${imageUrl}" alt="${article.featured_image_alt || article.title}" class="cp-article-image">
                            <div class="cp-article-content">
                                <div class="cp-article-tags">
                                    <span class="cp-article-tag ${categoryClass}" style="${style}">${categoryTag}</span>
                                </div>
                                <h3 class="cp-article-title">${article.title}</h3>
                                <div class="cp-article-meta">
                                    <span class="cp-article-author">${authorName}</span>
                                    <span class="cp-article-date">${formatDate(article.publish_date)}</span>
                                </div>
                            </div>
                        </a>
                    </article>
                </div>
            `;
        }

        // Initialize carousel from new trending API
        async function initializeCarousel() {
            try {
                const url = `${apiBase}/api/public/trending`;
                console.log('[Trending Carousel] Fetching from:', url);
                
                const res = await fetch(url);
                const json = await res.json();
                
                if (!json.success || !json.data || json.data.length === 0) {
                    console.error('[Trending Carousel] No articles returned from API');
                    return;
                }
                
                let articles = json.data;
                const source = json.source;
                
                // Sort by most recent publish date
                articles.sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date));
                
                console.log(`[Trending Carousel] Loaded ${articles.length} articles (source: ${source}, sorted by date)`);
                
                const cardsHTML = await Promise.all(articles.map(article => createArticleCard(article)));
                track.innerHTML = cardsHTML.join('');
            } catch (e) {
                console.error('[Trending Carousel] Failed to load articles:', e);
            }
        }

        initializeCarousel();
    });
    </script>
</script>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode( 'cp_trending_carousel', 'cp_shortcode_trending_carousel' );
