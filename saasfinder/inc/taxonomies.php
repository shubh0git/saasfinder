<?php
/**
 * Custom Taxonomies Registration
 *
 * Registers:
 * - saas-category (shared across CPTs and posts)
 * - pricing-model (freemium, free-trial, paid)
 * - audience (B2B, B2C, Both)
 * - blog-format (controls blog template parts)
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

function saasfinder_register_taxonomies() {

    // =========================================================================
    // Taxonomy: saas-category — shared across reviews, deals, and posts
    // Examples: CRM, Email Marketing, Project Management
    // =========================================================================
    register_taxonomy('saas-category', array('saas-review', 'saas-deal', 'post'), array(
        'labels' => array(
            'name'              => __('SaaS Categories', 'saasfinder'),
            'singular_name'     => __('SaaS Category', 'saasfinder'),
            'search_items'      => __('Search Categories', 'saasfinder'),
            'all_items'         => __('All Categories', 'saasfinder'),
            'parent_item'       => __('Parent Category', 'saasfinder'),
            'parent_item_colon' => __('Parent Category:', 'saasfinder'),
            'edit_item'         => __('Edit Category', 'saasfinder'),
            'update_item'       => __('Update Category', 'saasfinder'),
            'add_new_item'      => __('Add New Category', 'saasfinder'),
            'new_item_name'     => __('New Category Name', 'saasfinder'),
            'menu_name'         => __('SaaS Categories', 'saasfinder'),
        ),
        'hierarchical'      => true, // Category-like (parent/child)
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array(
            'slug'         => 'category',
            'with_front'   => false,
            'hierarchical' => true,
        ),
    ));

    // =========================================================================
    // Taxonomy: pricing-model — how the tool charges
    // =========================================================================
    register_taxonomy('pricing-model', array('saas-review', 'saas-deal'), array(
        'labels' => array(
            'name'          => __('Pricing Models', 'saasfinder'),
            'singular_name' => __('Pricing Model', 'saasfinder'),
            'search_items'  => __('Search Pricing Models', 'saasfinder'),
            'all_items'     => __('All Pricing Models', 'saasfinder'),
            'edit_item'     => __('Edit Pricing Model', 'saasfinder'),
            'update_item'   => __('Update Pricing Model', 'saasfinder'),
            'add_new_item'  => __('Add New Pricing Model', 'saasfinder'),
            'new_item_name' => __('New Pricing Model', 'saasfinder'),
            'menu_name'     => __('Pricing Models', 'saasfinder'),
        ),
        'hierarchical'      => false, // Tag-like
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array(
            'slug'       => 'pricing',
            'with_front' => false,
        ),
    ));

    // =========================================================================
    // Taxonomy: audience — who the tool is for
    // =========================================================================
    register_taxonomy('audience', array('saas-review', 'saas-deal'), array(
        'labels' => array(
            'name'          => __('Audiences', 'saasfinder'),
            'singular_name' => __('Audience', 'saasfinder'),
            'search_items'  => __('Search Audiences', 'saasfinder'),
            'all_items'     => __('All Audiences', 'saasfinder'),
            'edit_item'     => __('Edit Audience', 'saasfinder'),
            'update_item'   => __('Update Audience', 'saasfinder'),
            'add_new_item'  => __('Add New Audience', 'saasfinder'),
            'new_item_name' => __('New Audience', 'saasfinder'),
            'menu_name'     => __('Audiences', 'saasfinder'),
        ),
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array(
            'slug'       => 'audience',
            'with_front' => false,
        ),
    ));

    // =========================================================================
    // Taxonomy: blog-format — controls which template part loads for blog posts
    //
    // This is the extensibility mechanism: new formats = new term + new
    // template part in /template-parts/blog/{slug}.php. No core changes needed.
    //
    // Starter terms registered below via saasfinder_register_default_terms()
    // =========================================================================
    register_taxonomy('blog-format', array('post'), array(
        'labels' => array(
            'name'          => __('Blog Formats', 'saasfinder'),
            'singular_name' => __('Blog Format', 'saasfinder'),
            'search_items'  => __('Search Blog Formats', 'saasfinder'),
            'all_items'     => __('All Blog Formats', 'saasfinder'),
            'edit_item'     => __('Edit Blog Format', 'saasfinder'),
            'update_item'   => __('Update Blog Format', 'saasfinder'),
            'add_new_item'  => __('Add New Blog Format', 'saasfinder'),
            'new_item_name' => __('New Blog Format', 'saasfinder'),
            'menu_name'     => __('Blog Formats', 'saasfinder'),
        ),
        'hierarchical'      => false,
        'public'            => false, // Not visitor-facing archives
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => false, // No front-end archives for this taxonomy
    ));
}
add_action('init', 'saasfinder_register_taxonomies');

/**
 * Register default taxonomy terms on theme activation.
 * These provide the starter set — more can be added via WP admin.
 */
function saasfinder_register_default_terms() {

    // Default pricing models
    $pricing_models = array(
        'freemium'   => 'Freemium',
        'free-trial' => 'Free Trial',
        'paid'       => 'Paid Only',
    );

    foreach ($pricing_models as $slug => $name) {
        if (!term_exists($slug, 'pricing-model')) {
            wp_insert_term($name, 'pricing-model', array('slug' => $slug));
        }
    }

    // Default audiences
    $audiences = array(
        'b2b'  => 'B2B',
        'b2c'  => 'B2C',
        'both' => 'Both',
    );

    foreach ($audiences as $slug => $name) {
        if (!term_exists($slug, 'audience')) {
            wp_insert_term($name, 'audience', array('slug' => $slug));
        }
    }

    // Default blog formats — each maps to a template part in /template-parts/blog/
    $blog_formats = array(
        'user-reviews-roundup' => array(
            'name'        => 'User Reviews Roundup',
            'description' => 'Curated user reviews from G2, Capterra, Reddit. Template: user-reviews-roundup.php',
        ),
        'single-query-answer' => array(
            'name'        => 'Single Query Answer',
            'description' => 'One post per specific question (PAA, Reddit, ChatGPT). Template: single-query-answer.php',
        ),
        'tutorial' => array(
            'name'        => 'Tutorial / How-To',
            'description' => 'Step-by-step guides with HowTo schema. Template: tutorial.php',
        ),
        'news-reaction' => array(
            'name'        => 'News Reaction',
            'description' => 'Price changes, feature launches, acquisitions. Template: news-reaction.php',
        ),
        'tool-deep-dive' => array(
            'name'        => 'Tool Deep Dive',
            'description' => 'In-depth analysis of a single tool beyond the review. Template: tool-deep-dive.php',
        ),
    );

    foreach ($blog_formats as $slug => $data) {
        if (!term_exists($slug, 'blog-format')) {
            wp_insert_term($data['name'], 'blog-format', array(
                'slug'        => $slug,
                'description' => $data['description'],
            ));
        }
    }
}
add_action('after_switch_theme', 'saasfinder_register_default_terms');
