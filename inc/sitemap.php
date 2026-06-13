<?php
/**
 * Static Sitemap Generator
 *
 * Generates sitemap.xml on save_post hook.
 * Does not rely on any plugin — generates a static file at the site root.
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Regenerate sitemap when any relevant post is saved.
 */
function saasfinder_generate_sitemap($post_id) {
    // Don't run on autosaves or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') return;

    // Only regenerate for relevant post types
    $relevant_types = array('saas-review', 'saas-deal', 'post', 'page');
    if (!in_array($post->post_type, $relevant_types)) return;

    saasfinder_build_sitemap();
}
add_action('save_post', 'saasfinder_generate_sitemap', 99);

/**
 * Build the sitemap XML file.
 */
function saasfinder_build_sitemap() {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Homepage
    $xml .= saasfinder_sitemap_url(home_url('/'), 'daily', '1.0');

    // SaaS Reviews (highest priority — money pages)
    $reviews = get_posts(array(
        'post_type'   => 'saas-review',
        'numberposts' => -1,
        'post_status' => 'publish',
    ));
    foreach ($reviews as $post) {
        $xml .= saasfinder_sitemap_url(
            get_permalink($post),
            'weekly',
            '0.9',
            get_the_modified_date('c', $post)
        );
    }

    // Blog posts
    $posts = get_posts(array(
        'post_type'   => 'post',
        'numberposts' => -1,
        'post_status' => 'publish',
    ));
    foreach ($posts as $post) {
        $xml .= saasfinder_sitemap_url(
            get_permalink($post),
            'weekly',
            '0.7',
            get_the_modified_date('c', $post)
        );
    }

    // SaaS Deals
    $deals = get_posts(array(
        'post_type'   => 'saas-deal',
        'numberposts' => -1,
        'post_status' => 'publish',
    ));
    foreach ($deals as $post) {
        $xml .= saasfinder_sitemap_url(
            get_permalink($post),
            'daily',
            '0.8',
            get_the_modified_date('c', $post)
        );
    }

    // Pages (including programmatic SEO pages)
    $pages = get_posts(array(
        'post_type'   => 'page',
        'numberposts' => -1,
        'post_status' => 'publish',
    ));
    foreach ($pages as $post) {
        $xml .= saasfinder_sitemap_url(
            get_permalink($post),
            'weekly',
            '0.6',
            get_the_modified_date('c', $post)
        );
    }

    // Category hub pages (dynamic)
    $categories = get_terms(array(
        'taxonomy'   => 'saas-category',
        'hide_empty' => true,
    ));
    foreach ($categories as $cat) {
        $xml .= saasfinder_sitemap_url(
            home_url('/best/' . $cat->slug . '-software/'),
            'weekly',
            '0.8'
        );
    }

    $xml .= '</urlset>';

    // Write to site root
    $sitemap_path = ABSPATH . 'sitemap.xml';
    file_put_contents($sitemap_path, $xml);
}

/**
 * Format a single <url> entry.
 */
function saasfinder_sitemap_url($loc, $changefreq = 'weekly', $priority = '0.5', $lastmod = '') {
    $url = '  <url>' . "\n";
    $url .= '    <loc>' . esc_url($loc) . '</loc>' . "\n";
    if ($lastmod) {
        $url .= '    <lastmod>' . esc_html($lastmod) . '</lastmod>' . "\n";
    }
    $url .= '    <changefreq>' . esc_html($changefreq) . '</changefreq>' . "\n";
    $url .= '    <priority>' . esc_html($priority) . '</priority>' . "\n";
    $url .= '  </url>' . "\n";
    return $url;
}

/**
 * Generate sitemap on theme activation.
 */
add_action('after_switch_theme', 'saasfinder_build_sitemap');
