<?php
/**
 * Shortcode: cp_category_grid
 * Widget: Category Grid
 *
 * Renders a grid of WP categories using native get_terms() and term meta
 * (_color_code) — no external API calls.
 */

function cp_shortcode_category_grid( $atts = [], $content = null, $tag = '' ) {

    $terms = get_terms( [
        'taxonomy'   => 'category',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return '';
    }

    ob_start();
    ?>
<!-- Category Grid Widget -->
<style>
.category-grid-widget {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: clamp(12px, 1.6vw, 20px);
}
.category-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 5px;
}
.category-grid-item {
  background: #dbeafe;
  border-radius: 12px;
  padding: 12px 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e2761;
  text-decoration: none;
  transition: background 0.2s, color 0.2s;
}
.category-grid-item:hover {
  background: #003366;
  color: #fff;
}
@media (max-width: 700px) {
  .category-grid {
    grid-template-columns: 1fr 1fr;
  }
}
@media (max-width: 480px) {
  .category-grid {
    grid-template-columns: 1fr;
  }
}
</style>
<div class="category-grid-widget">
  <div class="category-grid">
    <?php foreach ( $terms as $term ) :
        $color = get_term_meta( $term->term_id, '_color_code', true );
        $style = $color ? 'background-color:' . esc_attr( $color ) . ';color:#fff;' : '';
        // Archive URL: /articles/category/{slug}  (handled by CPT rewrite rules)
        $href  = esc_url( home_url( '/articles/category/' . $term->slug . '/' ) );
    ?>
      <a class="category-grid-item" href="<?php echo $href; ?>"
         style="<?php echo $style; ?>"
         tabindex="0">
        <?php echo esc_html( $term->name ); ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode( 'cp_category_grid', 'cp_shortcode_category_grid' );
