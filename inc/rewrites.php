<?php
/**
 * Custom Rewrite Rules for Programmatic SEO Pages
 *
 * URL patterns:
 * - /compare/slack-vs-teams/        → page-comparison.php
 * - /alternatives/notion/            → page-alternatives.php
 * - /best/crm-software/             → page-category-hub.php
 * - /for/freelancers/               → page-use-case.php
 * - /answers/{slug}/                → single-query-answer posts (via blog-format)
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Register custom rewrite rules.
 */
function saasfinder_custom_rewrites() {
    // /compare/{slug}/ → loads page-comparison.php template
    add_rewrite_rule(
        '^compare/([^/]+)/?$',
        'index.php?pagename=compare&saas_compare_slug=$matches[1]',
        'top'
    );

    // /alternatives/{tool-slug}/ → loads page-alternatives.php template
    add_rewrite_rule(
        '^alternatives/([^/]+)/?$',
        'index.php?pagename=alternatives&saas_alt_tool=$matches[1]',
        'top'
    );

    // /best/{category-slug}-software/ → loads page-category-hub.php template
    add_rewrite_rule(
        '^best/([^/]+)/?$',
        'index.php?pagename=best&saas_hub_category=$matches[1]',
        'top'
    );

    // /for/{use-case}/ → loads page-use-case.php template
    add_rewrite_rule(
        '^for/([^/]+)/?$',
        'index.php?pagename=for&saas_use_case=$matches[1]',
        'top'
    );

    // /answers/{slug}/ → targets single-query-answer blog posts
    add_rewrite_rule(
        '^answers/([^/]+)/?$',
        'index.php?name=$matches[1]&saas_answer=1',
        'top'
    );
}
add_action('init', 'saasfinder_custom_rewrites');

/**
 * Register custom query variables so WP recognizes them.
 */
function saasfinder_query_vars($vars) {
    $vars[] = 'saas_compare_slug';
    $vars[] = 'saas_alt_tool';
    $vars[] = 'saas_hub_category';
    $vars[] = 'saas_use_case';
    $vars[] = 'saas_answer';
    return $vars;
}
add_filter('query_vars', 'saasfinder_query_vars');

/**
 * Template routing — load the correct page template based on query vars.
 */
function saasfinder_template_include($template) {

    if (get_query_var('saas_compare_slug')) {
        $custom = locate_template('page-comparison.php');
        if ($custom) return $custom;
    }

    if (get_query_var('saas_alt_tool')) {
        $custom = locate_template('page-alternatives.php');
        if ($custom) return $custom;
    }

    if (get_query_var('saas_hub_category')) {
        $custom = locate_template('page-category-hub.php');
        if ($custom) return $custom;
    }

    if (get_query_var('saas_use_case')) {
        $custom = locate_template('page-use-case.php');
        if ($custom) return $custom;
    }

    return $template;
}
add_filter('template_include', 'saasfinder_template_include');

/**
 * Flush rewrite rules on theme activation.
 */
function saasfinder_flush_rewrites_on_activate() {
    saasfinder_custom_rewrites();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'saasfinder_flush_rewrites_on_activate');
/**
 * Fix Soft 404 Status for Programmatic Virtual Pages
 * This forces a 200 Success status so Google indexes the pages.
 */
function saasfinder_fix_virtual_page_404() {
    global $wp_query;

    // Check if we are on ANY of our custom programmatic pages
    if ( get_query_var('saas_compare_slug') || get_query_var('saas_alt_tool') || get_query_var('saas_hub_category') || get_query_var('saas_use_case') ) {
        // Override the 404 flag and send a 200 OK header
        $wp_query->is_404 = false;
        status_header(200);
    }
}
add_action('template_redirect', 'saasfinder_fix_virtual_page_404');

/**
 * Dynamically Generate Browser Tab / SEO Titles for Virtual Pages
 */
function saasfinder_virtual_page_titles( $title_parts ) {
    // Fix title specifically for the /best/ category pages
    if ( $hub_slug = get_query_var('saas_hub_category') ) {
        $category_slug = preg_replace('/-software$/', '', $hub_slug);
        $category = get_term_by('slug', $category_slug, 'saas-category');

        if ( $category ) {
            $title_parts['title'] = 'Best ' . $category->name . ' Software in ' . date('Y');
        }
    }

    // Note: You can add additional if() statements here later to handle dynamic titles for your /compare/ and /alternatives/ pages!

    return $title_parts;
}
add_filter('document_title_parts', 'saasfinder_virtual_page_titles');
