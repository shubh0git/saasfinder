<?php
/**
 * AJAX Handlers
 *
 * Handles:
 * - Archive filtering (saas-review grid with category/pricing/audience/rating filters)
 * - Search autocomplete
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * AJAX handler for archive filtering.
 */
function saasfinder_ajax_filter_reviews() {
    check_ajax_referer('saasfinder_nonce', 'nonce');

    $args = array(
        'post_type'      => 'saas-review',
        'posts_per_page' => 12,
        'paged'          => isset($_POST['page']) ? absint($_POST['page']) : 1,
        'post_status'    => 'publish',
    );

    // Tax queries
    $tax_query = array('relation' => 'AND');

    if (!empty($_POST['category'])) {
        $tax_query[] = array(
            'taxonomy' => 'saas-category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_POST['category']),
        );
    }

    if (!empty($_POST['pricing'])) {
        $tax_query[] = array(
            'taxonomy' => 'pricing-model',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_POST['pricing']),
        );
    }

    if (!empty($_POST['audience'])) {
        $tax_query[] = array(
            'taxonomy' => 'audience',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($_POST['audience']),
        );
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    // Rating filter (meta query)
    if (!empty($_POST['min_rating'])) {
        $args['meta_query'] = array(
            array(
                'key'     => '_saas_tool_rating',
                'value'   => floatval($_POST['min_rating']),
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ),
        );
    }

    // Sort
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'rating';
    switch ($sort) {
        case 'rating':
            $args['meta_key'] = '_saas_tool_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'newest':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'title':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
    }

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        echo '<div class="grid-auto">';
        while ($query->have_posts()) : $query->the_post();
            get_template_part('template-parts/components/review-card');
        endwhile;
        echo '</div>';

        // Pagination info
        echo '<div class="ajax-pagination" data-max-pages="' . esc_attr($query->max_num_pages) . '"></div>';
    else :
        echo '<p class="text-muted text-center">No reviews match your filters. Try adjusting your criteria.</p>';
    endif;

    wp_reset_postdata();

    wp_send_json_success(array(
        'html'      => ob_get_clean(),
        'found'     => $query->found_posts,
        'max_pages' => $query->max_num_pages,
    ));
}
add_action('wp_ajax_filter_reviews', 'saasfinder_ajax_filter_reviews');
add_action('wp_ajax_nopriv_filter_reviews', 'saasfinder_ajax_filter_reviews');

/**
 * AJAX search handler (lightweight, returns JSON for autocomplete).
 */
function saasfinder_ajax_search() {
    check_ajax_referer('saasfinder_nonce', 'nonce');

    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    if (strlen($query) < 2) {
        wp_send_json_success(array('results' => array()));
    }

    $results = array();

    // Search reviews
    $posts = get_posts(array(
        'post_type'      => array('saas-review', 'post'),
        'posts_per_page' => 5,
        's'              => $query,
        'post_status'    => 'publish',
    ));

    foreach ($posts as $post) {
        $results[] = array(
            'title' => $post->post_title,
            'url'   => get_permalink($post),
            'type'  => $post->post_type === 'saas-review' ? 'Review' : 'Blog',
        );
    }

    wp_send_json_success(array('results' => $results));
}
add_action('wp_ajax_saasfinder_search', 'saasfinder_ajax_search');
add_action('wp_ajax_nopriv_saasfinder_search', 'saasfinder_ajax_search');
