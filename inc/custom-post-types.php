<?php
/**
 * Custom Post Types Registration
 *
 * Registers: saas-review, saas-deal
 * Blog content uses the default 'post' type intentionally —
 * keeps RSS, sitemaps, and writing workflow standard.
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Register Custom Post Types.
 */
function saasfinder_register_post_types() {

    // =========================================================================
    // CPT: saas-review — The money pages
    // =========================================================================
    $review_labels = array(
        'name'               => __('SaaS Reviews', 'saasfinder'),
        'singular_name'      => __('SaaS Review', 'saasfinder'),
        'menu_name'          => __('SaaS Reviews', 'saasfinder'),
        'add_new'            => __('Add New Review', 'saasfinder'),
        'add_new_item'       => __('Add New SaaS Review', 'saasfinder'),
        'edit_item'          => __('Edit SaaS Review', 'saasfinder'),
        'new_item'           => __('New SaaS Review', 'saasfinder'),
        'view_item'          => __('View SaaS Review', 'saasfinder'),
        'search_items'       => __('Search SaaS Reviews', 'saasfinder'),
        'not_found'          => __('No reviews found', 'saasfinder'),
        'not_found_in_trash' => __('No reviews found in Trash', 'saasfinder'),
        'all_items'          => __('All Reviews', 'saasfinder'),
    );

    $review_args = array(
        'labels'              => $review_labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true, // Enable REST API for potential headless use
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-star-filled',
        'capability_type'     => 'post',
        'has_archive'         => true,
        'rewrite'             => array(
            'slug'       => 'review',
            'with_front' => false,
        ),
        'supports'            => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'revisions',
            'author',
        ),
        'taxonomies'          => array('saas-category', 'pricing-model', 'audience'),
    );

    register_post_type('saas-review', $review_args);

    // =========================================================================
    // CPT: saas-deal — Time-sensitive affiliate deals
    // =========================================================================
    $deal_labels = array(
        'name'               => __('SaaS Deals', 'saasfinder'),
        'singular_name'      => __('SaaS Deal', 'saasfinder'),
        'menu_name'          => __('SaaS Deals', 'saasfinder'),
        'add_new'            => __('Add New Deal', 'saasfinder'),
        'add_new_item'       => __('Add New SaaS Deal', 'saasfinder'),
        'edit_item'          => __('Edit SaaS Deal', 'saasfinder'),
        'new_item'           => __('New SaaS Deal', 'saasfinder'),
        'view_item'          => __('View SaaS Deal', 'saasfinder'),
        'search_items'       => __('Search SaaS Deals', 'saasfinder'),
        'not_found'          => __('No deals found', 'saasfinder'),
        'not_found_in_trash' => __('No deals found in Trash', 'saasfinder'),
        'all_items'          => __('All Deals', 'saasfinder'),
    );

    $deal_args = array(
        'labels'              => $deal_labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-tag',
        'capability_type'     => 'post',
        'has_archive'         => true,
        'rewrite'             => array(
            'slug'       => 'deals',
            'with_front' => false,
        ),
        'supports'            => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'revisions',
        ),
        'taxonomies'          => array('saas-category'),
    );

    register_post_type('saas-deal', $deal_args);
}
add_action('init', 'saasfinder_register_post_types');

/**
 * Flush rewrite rules on theme activation to register CPT permalinks.
 */
function saasfinder_rewrite_flush() {
    saasfinder_register_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'saasfinder_rewrite_flush');
