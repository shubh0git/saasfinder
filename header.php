<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php // Critical CSS inlined for above-the-fold performance ?>
    <style>
        :root{--brand-primary:#2563EB;--brand-accent:#F59E0B;--text-primary:#1E293B;--text-secondary:#475569;--bg-body:#FFF;--bg-surface:#F8FAFC;--border-default:#E2E8F0;--font-body:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;--space-4:1rem;--space-6:1.5rem;--container-max:1200px}*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}html{font-size:100%}body{font-family:var(--font-body);color:var(--text-primary);background:var(--bg-body);-webkit-font-smoothing:antialiased}.container{max-width:var(--container-max);margin-inline:auto;padding-inline:var(--space-4)}.site-header{background:#fff;border-bottom:1px solid var(--border-default);padding:var(--space-4) 0;position:sticky;top:0;z-index:1000}.site-header__inner{display:flex;align-items:center;justify-content:space-between;gap:var(--space-6)}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sr-only" href="#main-content">Skip to content</a>

<header class="site-header" role="banner">
    <div class="container">
        <div class="site-header__inner">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" aria-label="<?php bloginfo('name'); ?> — Home">
                <strong style="font-size:1.25rem;color:var(--text-primary);"><?php bloginfo('name'); ?></strong>
            </a>

            <nav class="site-nav" role="navigation" aria-label="Primary navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'site-nav__list',
                    'fallback_cb'    => 'saasfinder_fallback_menu',
                    'depth'          => 2,
                    'link_before'    => '',
                    'link_after'     => '',
                ));
                ?>
            </nav>

            <button class="site-nav__toggle" aria-label="Toggle navigation" aria-expanded="false" style="display:none;">
                <span></span>
            </button>
        </div>
    </div>
</header>

<?php
// Breadcrumbs — show on all pages except front page
if (!is_front_page()) :
    saasfinder_render_breadcrumbs();
endif;
?>

<main id="main-content" role="main">
<?php

/**
 * Fallback menu if no menu is assigned.
 */
function saasfinder_fallback_menu() {
    echo '<ul class="site-nav__list">';
    echo '<li><a class="site-nav__link" href="' . esc_url(get_post_type_archive_link('saas-review')) . '">Reviews</a></li>';
    echo '<li><a class="site-nav__link" href="' . esc_url(get_post_type_archive_link('saas-deal')) . '">Deals</a></li>';
    echo '<li><a class="site-nav__link" href="' . esc_url(get_permalink(get_option('page_for_posts'))) . '">Blog</a></li>';
    echo '</ul>';
}

/**
 * Render breadcrumbs (visual, not schema — schema is in schema.php).
 */
function saasfinder_render_breadcrumbs() {
    echo '<nav class="breadcrumbs" aria-label="Breadcrumbs"><div class="container"><ol class="breadcrumbs__list">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';

    if (is_singular('saas-review')) {
        $categories = wp_get_post_terms(get_the_ID(), 'saas-category');
        if (!empty($categories)) {
            $cat = $categories[0];
            echo '<li><span class="breadcrumbs__separator">/</span></li>';
            echo '<li><a href="' . esc_url(home_url('/best/' . $cat->slug . '-software/')) . '">' . esc_html($cat->name) . '</a></li>';
        }
        echo '<li><span class="breadcrumbs__separator">/</span></li>';
        echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_singular('post')) {
    $categories = get_the_category();
    if (!empty($categories)) {
        $cat = $categories[0];
        echo '<li><span class="breadcrumbs__separator">/</span></li>';
        echo '<li><a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a></li>';
    }
    echo '<li><span class="breadcrumbs__separator">/</span></li>';
    echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_singular('saas-deal')) {
        echo '<li><span class="breadcrumbs__separator">/</span></li>';
        echo '<li><a href="' . esc_url(get_post_type_archive_link('saas-deal')) . '">Deals</a></li>';
        echo '<li><span class="breadcrumbs__separator">/</span></li>';
        echo '<li aria-current="page">' . esc_html(get_the_title()) . '</li>';
    } elseif (is_archive()) {
        echo '<li><span class="breadcrumbs__separator">/</span></li>';
        echo '<li aria-current="page">' . esc_html(get_the_archive_title()) . '</li>';
    }

    echo '</ol></div></nav>';
}
