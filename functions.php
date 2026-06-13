<?php
/**
 * SaasFinder Theme Functions
 *
 * Modular architecture: each concern lives in its own file under /inc/.
 * This file handles only theme setup and loading.
 *
 * @package SaasFinder
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Theme constants
define('SAASFINDER_VERSION', '1.0.0');
define('SAASFINDER_DIR', get_template_directory());
define('SAASFINDER_URI', get_template_directory_uri());
define('SAASFINDER_INC', SAASFINDER_DIR . '/inc');

/**
 * Theme Setup — runs on after_setup_theme hook.
 */
function saasfinder_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable featured images on posts and CPTs
    add_theme_support('post-thumbnails');

    // Custom image sizes for the theme
    add_image_size('saas-card', 400, 300, true);
    add_image_size('saas-logo', 200, 200, false);
    add_image_size('saas-screenshot', 1200, 800, false);

    // Register navigation menus
    register_nav_menus(array(
        'primary'  => __('Primary Navigation', 'saasfinder'),
        'footer'   => __('Footer Navigation', 'saasfinder'),
    ));

    // HTML5 markup support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Disable block editor for custom post types (we use classic editor + meta boxes)
    // Blog posts can still use either editor
    add_filter('use_block_editor_for_post_type', function($use, $post_type) {
        if (in_array($post_type, array('saas-review', 'saas-deal'))) {
            return false;
        }
        return $use;
    }, 10, 2);
}
add_action('after_setup_theme', 'saasfinder_setup');

/**
 * Enqueue Scripts & Styles
 *
 * Performance strategy:
 * - No jQuery
 * - Critical CSS inlined in header.php
 * - Main stylesheet loaded with preload pattern
 * - Single vanilla JS file, deferred
 */
function saasfinder_enqueue_assets() {
    // Main stylesheet (design system)
    wp_enqueue_style(
        'saasfinder-style',
        get_stylesheet_uri(),
        array(),
        SAASFINDER_VERSION
    );

    // Component styles (loaded after main stylesheet)
    wp_enqueue_style(
        'saasfinder-components',
        SAASFINDER_URI . '/assets/css/components.css',
        array('saasfinder-style'),
        SAASFINDER_VERSION
    );

    // Main JS — vanilla, no jQuery dependency
    wp_enqueue_script(
        'saasfinder-main',
        SAASFINDER_URI . '/assets/js/main.js',
        array(), // No dependencies — no jQuery
        SAASFINDER_VERSION,
        true // Load in footer
    );

    // Pass data to JS for AJAX filtering and click tracking
    wp_localize_script('saasfinder-main', 'saasfinder', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('saasfinder_nonce'),
        'home_url' => home_url('/'),
    ));

    // Dequeue jQuery if nothing else needs it
    if (!is_admin()) {
        wp_deregister_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'saasfinder_enqueue_assets');

/**
 * Preload Inter font for above-the-fold performance.
 * Uses font-display: swap to prevent render blocking.
 * Falls back to Google Fonts CDN if local file missing.
 */
function saasfinder_preload_fonts() {
    $local_font = SAASFINDER_DIR . '/assets/fonts/inter-var.woff2';
    if (file_exists($local_font)) {
        echo '<link rel="preload" href="' . esc_url(SAASFINDER_URI . '/assets/fonts/inter-var.woff2') . '" as="font" type="font/woff2" crossorigin>' . "\n";
    } else {
        // Fallback: load Inter from Google Fonts CDN
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap">' . "\n";
    }
}
add_action('wp_head', 'saasfinder_preload_fonts', 1);

/**
 * Add resource hints for performance.
 */
function saasfinder_resource_hints($urls, $relation_type) {
    if ($relation_type === 'dns-prefetch') {
        $urls[] = '//fonts.googleapis.com';
    }
    return $urls;
}
add_filter('wp_resource_hints', 'saasfinder_resource_hints', 10, 2);

/**
 * Disable comments on CPTs (keep on blog posts only).
 */
function saasfinder_disable_cpt_comments() {
    // Remove comment support from CPTs
    remove_post_type_support('saas-review', 'comments');
    remove_post_type_support('saas-deal', 'comments');
}
add_action('init', 'saasfinder_disable_cpt_comments', 20);

/**
 * Clean up wp_head for performance.
 */
function saasfinder_cleanup_head() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('after_setup_theme', 'saasfinder_cleanup_head');

/**
 * Register widget areas.
 */
function saasfinder_widgets_init() {
    register_sidebar(array(
        'name'          => __('Review Sidebar', 'saasfinder'),
        'id'            => 'sidebar-review',
        'description'   => __('Appears on SaaS review pages.', 'saasfinder'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget__title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Blog Sidebar', 'saasfinder'),
        'id'            => 'sidebar-blog',
        'description'   => __('Appears on blog posts.', 'saasfinder'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget__title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'saasfinder_widgets_init');

/**
 * Custom excerpt length optimized for card displays.
 */
function saasfinder_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'saasfinder_excerpt_length');

/**
 * Remove "Read More" [...] and replace with ellipsis.
 */
function saasfinder_excerpt_more($more) {
    return '&hellip;';
}
add_filter('excerpt_more', 'saasfinder_excerpt_more');

// =============================================================================
// MODULAR INCLUDES — each file handles one concern
// =============================================================================

// Custom Post Types (saas-review, saas-deal)
require_once SAASFINDER_INC . '/custom-post-types.php';

// Custom Taxonomies (saas-category, pricing-model, audience, blog-format)
require_once SAASFINDER_INC . '/taxonomies.php';

// Meta Boxes (affiliate fields, deal fields, blog format fields, last_verified_date)
require_once SAASFINDER_INC . '/meta-boxes.php';

// Schema.org structured data output (JSON-LD)
require_once SAASFINDER_INC . '/schema.php';

// Shortcodes (saas_cta, saas_comparison, pricing_table, verdict_box, etc.)
require_once SAASFINDER_INC . '/shortcodes.php';

// Affiliate link handling (disclosure, rel attributes, click tracking)
require_once SAASFINDER_INC . '/affiliate.php';

// Automated internal linking system
require_once SAASFINDER_INC . '/internal-links.php';

// Custom rewrite rules for programmatic SEO pages
require_once SAASFINDER_INC . '/rewrites.php';

// Sitemap generation (static XML on save_post)
require_once SAASFINDER_INC . '/sitemap.php';

// AJAX handlers (archive filtering, search)
require_once SAASFINDER_INC . '/ajax-handlers.php';
