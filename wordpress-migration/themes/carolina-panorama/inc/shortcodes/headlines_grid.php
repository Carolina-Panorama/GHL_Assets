<?php
/**
 * Shortcode: cp_headlines_grid
 * Widget: Headlines Grid
 *
 * Renders the top 5 articles from the native 'article' CPT — no external
 * API calls.  PHP queries WP, outputs fully-rendered HTML; the inline JS
 * that used to fetch from the CMS has been removed.
 */

function cp_headlines_grid_get_articles( int $count = 5 ): array {
    // ── 1. Editor-curated headlines (checked in the article sidebar) ──────────
    $headline_query = new WP_Query( [
        'post_type'      => 'article',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
        'meta_query'     => [ [
            'key'   => '_is_headline',
            'value' => '1',
        ] ],
    ] );
    $posts = $headline_query->posts;

    // ── 2. Backfill with most-recent articles if fewer than $count are marked ─
    if ( count( $posts ) < $count ) {
        $existing_ids = array_map( fn( $p ) => $p->ID, $posts );
        $backfill = new WP_Query( [
            'post_type'      => 'article',
            'post_status'    => 'publish',
            'posts_per_page' => $count - count( $posts ),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'post__not_in'   => $existing_ids ?: [ 0 ],
        ] );
        $posts = array_merge( $posts, $backfill->posts );
    }

    // ── 3. Map WP_Post objects to display arrays ───────────────────────────────
    $articles = [];
    foreach ( $posts as $post ) {
        $image      = cp_article_thumbnail_fallback_url( $post->ID );
        $image_alt  = cp_article_thumbnail_fallback_alt( $post->ID );
        $categories = get_the_terms( $post->ID, 'category' ) ?: [];
        $author_id  = $post->post_author;

        $cats = [];
        foreach ( $categories as $cat ) {
            $color  = get_term_meta( $cat->term_id, '_color_code', true ) ?: '#3b82f6';
            $cats[] = [ 'name' => $cat->name, 'color' => $color, 'slug' => $cat->slug ];
        }

        $articles[] = [
            'title'      => get_the_title( $post ),
            'url'        => get_permalink( $post ),
            'excerpt'    => get_the_excerpt( $post ),
            'image'      => $image ?: 'https://storage.googleapis.com/msgsndr/9Iv8kFcMiUgScXzMPv23/media/697bd8644d56831c95c3248d.svg',
            'image_alt'  => $image_alt ?: get_the_title( $post ),
            'author'     => get_the_author_meta( 'display_name', $author_id ) ?: 'Carolina Panorama',
            'date'       => get_the_date( 'M j, Y', $post ),
            'categories' => $cats,
            'is_headline'=> (bool) get_post_meta( $post->ID, '_is_headline', true ),
        ];
    }
    return $articles;
}

function cp_shortcode_headlines_grid( $atts = [], $content = null, $tag = '' ) {
    wp_enqueue_style( 'cp-article-card-styles' );

    $articles = headlines_grid_get_articles( 5 );
    // Helper closure for rendering one category tag badge
    $cat_badge = function( array $cat ): string {
        $cls   = 'cp-article-tag ' . sanitize_html_class( $cat['slug'] );
        $style = 'background-color:' . esc_attr( $cat['color'] ) . ' !important;';
        return '<span class="' . esc_attr( $cls ) . '" style="' . $style . '">'
             . esc_html( $cat['name'] ) . '</span>';
    };

    ob_start();

    // Bail gracefully if there are no articles yet
    if ( empty( $articles ) ) {
        return '<p style="text-align:center;color:#666;">No articles found.</p>';
    }

    // Slot names in grid order: [0]=center(featured), [1]=left-top, [2]=left-bottom, [3]=right-top, [4]=right-bottom
    $slots = [ 'headlines-center', 'headlines-left-top', 'headlines-left-bottom', 'headlines-right-top', 'headlines-right-bottom' ];
    ?>
<!-- Headlines Grid Widget - Displays 5 articles with featured layout -->
<style>
    .headlines-section-wrapper {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .headlines-section-header {
        text-align: center;
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 3px solid #3b82f6;
    }

    .headlines-section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: 'Georgia', serif;
    }

    .headlines-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 20px;
    }

    /* Featured badge for center article */
    .featured-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #dcb349;
        color: white;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    /* Grid positioning */
    .headlines-center {
        grid-column: 2;
        grid-row: 1 / 3;
        position: relative;
    }

    .headlines-left-top    { grid-column: 1; grid-row: 1; }
    .headlines-left-bottom { grid-column: 1; grid-row: 2; }
    .headlines-right-top   { grid-column: 3; grid-row: 1; }
    .headlines-right-bottom{ grid-column: 3; grid-row: 2; }

    /* Responsive design */
    @media (max-width: 1024px) {
        .headlines-grid {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto;
        }
        .headlines-center      { grid-column: 1 / 3; grid-row: 1; }
        .headlines-left-top    { grid-column: 1;     grid-row: 2; }
        .headlines-left-bottom { grid-column: 2;     grid-row: 2; }
        .headlines-right-top   { grid-column: 1;     grid-row: 3; }
        .headlines-right-bottom{ grid-column: 2;     grid-row: 3; }
    }

    @media (max-width: 640px) {
        .headlines-section-header { margin-bottom: 16px; }
        .headlines-section-title  { font-size: 1.5rem; }
        .headlines-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .headlines-center,
        .headlines-left-top,
        .headlines-left-bottom,
        .headlines-right-top,
        .headlines-right-bottom {
            grid-column: 1;
            grid-row: auto;
        }
    }
</style>

<div class="headlines-section-wrapper">
    <div class="headlines-section-header">
        <h2 class="headlines-section-title">Top Headlines</h2>
    </div>
    <div class="headlines-grid">
    <?php foreach ( $slots as $i => $slot ) :
        if ( ! isset( $articles[ $i ] ) ) continue;
        $a         = $articles[ $i ];
        $is_center = ( $i === 0 );
        $card_cls  = $is_center ? 'cp-article-card cp-article-card-featured' : 'cp-article-card cp-article-card-small';
        $title_tag = $is_center ? 'h2' : 'h3';
        $loading   = $is_center ? 'eager' : 'lazy';
        $priority  = $is_center ? ' fetchpriority="high"' : '';
        $cats_html = '';
        foreach ( array_slice( $a['categories'], 0, 2 ) as $cat ) {
            $cls        = 'cp-article-tag ' . sanitize_html_class( $cat['slug'] );
            $cat_style  = 'background-color:' . esc_attr( $cat['color'] ) . ' !important;';
            $cats_html .= '<span class="' . esc_attr( $cls ) . '" style="' . $cat_style . '">' . esc_html( $cat['name'] ) . '</span>';
        }
    ?>
        <div class="<?php echo esc_attr( $slot ); ?>">
            <?php if ( $is_center ) : ?><span class="featured-badge">Featured</span><?php endif; ?>
            <article class="<?php echo esc_attr( $card_cls ); ?>">
                <a href="<?php echo esc_url( $a['url'] ); ?>" class="cp-article-card-link">
                    <img src="<?php echo esc_url( $a['image'] ); ?>"
                         alt="<?php echo esc_attr( $a['image_alt'] ); ?>"
                         class="cp-article-image"
                         loading="<?php echo $loading; ?>"<?php echo $priority; ?>>
                    <div class="cp-article-content">
                        <div class="cp-article-tags"><?php echo $cats_html; ?></div>
                        <<?php echo $title_tag; ?> class="cp-article-title"><?php echo esc_html( $a['title'] ); ?></<?php echo $title_tag; ?>>
                        <div class="cp-article-meta">
                            <span class="cp-article-author"><?php echo esc_html( $a['author'] ); ?></span>
                            <span class="cp-article-date"><?php echo esc_html( $a['date'] ); ?></span>
                        </div>
                        <?php if ( $is_center && $a['excerpt'] ) : ?>
                        <p class="cp-article-description"><?php echo esc_html( $a['excerpt'] ); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
        </div>
    <?php endforeach; ?>
    </div>
</div>
    <?php
    return ob_get_clean();
}

// Internal helper used only by this shortcode (avoids collision with any global)
function headlines_grid_get_articles( int $count ): array {
    return cp_headlines_grid_get_articles( $count );

// Register shortcode
add_shortcode( 'cp_headlines_grid', 'cp_shortcode_headlines_grid' );
