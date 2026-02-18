<?php
/**
 * The header for Carolina Panorama theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site">
        <a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'carolina-panorama' ); ?></a>

        <header id="masthead" class="site-header">
            <div class="site-header-inner">

                <div class="site-branding">
                    <!-- Logo (hidden on mobile via CSS, matching original GHL desktop-only behaviour) -->
                    <div class="site-logo">
                        <?php if ( has_custom_logo() ) : ?>
                            <?php the_custom_logo(); ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                                <picture>
                                    <source media="(max-width:900px) and (min-width:768px)" srcset="https://images.leadconnectorhq.com/image/f_webp/q_80/r_900/u_https://assets.cdn.filesafe.space/9Iv8kFcMiUgScXzMPv23/media/69728af6310c2d5fb7a20741.png">
                                    <source media="(max-width:768px) and (min-width:640px)"  srcset="https://images.leadconnectorhq.com/image/f_webp/q_80/r_768/u_https://assets.cdn.filesafe.space/9Iv8kFcMiUgScXzMPv23/media/69728af6310c2d5fb7a20741.png">
                                    <source media="(max-width:640px)  and (min-width:480px)"  srcset="https://images.leadconnectorhq.com/image/f_webp/q_80/r_640/u_https://assets.cdn.filesafe.space/9Iv8kFcMiUgScXzMPv23/media/69728af6310c2d5fb7a20741.png">
                                    <source media="(max-width:480px)"                         srcset="https://images.leadconnectorhq.com/image/f_webp/q_80/r_480/u_https://assets.cdn.filesafe.space/9Iv8kFcMiUgScXzMPv23/media/69728af6310c2d5fb7a20741.png">
                                    <img
                                        src="https://images.leadconnectorhq.com/image/f_webp/q_80/r_1200/u_https://assets.cdn.filesafe.space/9Iv8kFcMiUgScXzMPv23/media/69728af6310c2d5fb7a20741.png"
                                        alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                                        class="site-logo-img"
                                        loading="eager">
                                </picture>
                            </a>
                        <?php endif; ?>
                    </div><!-- .site-logo -->

                    <div class="site-identity">
                        <p class="site-title">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                                <?php bloginfo( 'name' ); ?>
                            </a>
                        </p>
                        <?php $description = get_bloginfo( 'description', 'display' ); ?>
                        <?php if ( $description ) : ?>
                            <p class="site-description"><?php echo esc_html( $description ); ?></p>
                        <?php endif; ?>
                    </div><!-- .site-identity -->
                </div><!-- .site-branding -->

                <nav id="site-navigation" class="main-navigation" aria-label="Primary">
                    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                        <span class="menu-toggle-icon" aria-hidden="true">&#9776;</span>
                        <span class="screen-reader-text"><?php _e( 'Menu', 'carolina-panorama' ); ?></span>
                    </button>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'depth'          => 2,
                    ] );
                    ?>
                </nav><!-- #site-navigation -->

            </div><!-- .site-header-inner -->
        </header><!-- #masthead -->

        <script>
        // Mobile nav toggle
        (function() {
            var btn = document.querySelector( '.menu-toggle' );
            var nav = document.getElementById( 'site-navigation' );
            if ( ! btn || ! nav ) return;
            btn.addEventListener( 'click', function() {
                var expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
                btn.setAttribute( 'aria-expanded', String( ! expanded ) );
                nav.classList.toggle( 'toggled' );
            } );
        })();
        </script>

        <main id="content" class="site-content">
