<?php
/**
 * Automated Internal Linking System
 *
 * Auto-generates contextual internal links at the bottom of:
 * - saas-review posts (alternatives, comparisons, hub, user reviews, tutorials)
 * - Blog posts (linked review, comparisons, hub)
 * - single-query-answer posts (related questions)
 *
 * Also provides the "content cluster" nav component for review pages.
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Append internal links section to saas-review content.
 */
function saasfinder_review_internal_links($content) {
    if (!is_singular('saas-review') || is_admin()) return $content;

    $post_id = get_the_ID();
    $tool_name = get_the_title();
    $tool_slug = get_post_field('post_name', $post_id);

    // Get the tool's categories
    $categories = wp_get_post_terms($post_id, 'saas-category');
    $category = !empty($categories) ? $categories[0] : null;

    ob_start();
    ?>
    <nav class="content-cluster" aria-label="Related content for <?php echo esc_attr($tool_name); ?>">
        <div class="content-cluster__title">Explore More About <?php echo esc_html($tool_name); ?></div>
        <ul class="content-cluster__links">
            <?php
            // "Alternatives to [Tool]" link
            $alt_url = home_url('/alternatives/' . $tool_slug . '/');
            echo '<li><a class="content-cluster__link" href="' . esc_url($alt_url) . '">Alternatives to ' . esc_html($tool_name) . '</a></li>';

            // "Compare [Tool] vs..." — find existing comparison pages
            // (In production, these would be queried from pages using the comparison template)
            $comparison_pages = get_posts(array(
                'post_type'   => 'page',
                'meta_key'    => '_wp_page_template',
                'meta_value'  => 'page-comparison.php',
                'numberposts' => 5,
                's'           => $tool_name,
            ));
            foreach ($comparison_pages as $comp) {
                echo '<li><a class="content-cluster__link" href="' . esc_url(get_permalink($comp)) . '">' . esc_html($comp->post_title) . '</a></li>';
            }

            // "More [Category] tools" — link to category hub
            if ($category) {
                $hub_url = home_url('/best/' . $category->slug . '-software/');
                echo '<li><a class="content-cluster__link" href="' . esc_url($hub_url) . '">Best ' . esc_html($category->name) . ' Software</a></li>';
            }

            // "What users say about [Tool]" — find user-reviews-roundup blog post
            $user_reviews_posts = get_posts(array(
                'post_type'   => 'post',
                'numberposts' => 1,
                'tax_query'   => array(
                    array(
                        'taxonomy' => 'blog-format',
                        'field'    => 'slug',
                        'terms'    => 'user-reviews-roundup',
                    ),
                ),
                'meta_query' => array(
                    array(
                        'key'     => '_blog_linked_review_ids',
                        'value'   => $post_id,
                        'compare' => 'LIKE',
                    ),
                ),
            ));
            if (!empty($user_reviews_posts)) {
                echo '<li><a class="content-cluster__link" href="' . esc_url(get_permalink($user_reviews_posts[0])) . '">What Users Say About ' . esc_html($tool_name) . '</a></li>';
            }

            // "Guides & tutorials for [Tool]"
            if ($category) {
                $tutorials = get_posts(array(
                    'post_type'   => 'post',
                    'numberposts' => 3,
                    'tax_query'   => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'blog-format',
                            'field'    => 'slug',
                            'terms'    => 'tutorial',
                        ),
                        array(
                            'taxonomy' => 'saas-category',
                            'field'    => 'term_id',
                            'terms'    => $category->term_id,
                        ),
                    ),
                ));
                foreach ($tutorials as $tut) {
                    echo '<li><a class="content-cluster__link" href="' . esc_url(get_permalink($tut)) . '">' . esc_html($tut->post_title) . '</a></li>';
                }
            }
            ?>
        </ul>
    </nav>
    <?php
    $links_html = ob_get_clean();

    return $content . $links_html;
}
add_filter('the_content', 'saasfinder_review_internal_links', 30);

/**
 * Append internal links to blog posts that reference a saas-review.
 */
function saasfinder_blog_internal_links($content) {
    if (!is_singular('post') || is_admin()) return $content;

    $post_id = get_the_ID();
    $linked_ids = get_post_meta($post_id, '_blog_linked_review_ids', true);

    if (empty($linked_ids)) return $content;

    $ids = array_map('absint', array_filter(explode(',', $linked_ids)));
    if (empty($ids)) return $content;

    ob_start();
    echo '<nav class="content-cluster" aria-label="Related reviews">';
    echo '<div class="content-cluster__title">Related Reviews & Comparisons</div>';
    echo '<ul class="content-cluster__links">';

    foreach ($ids as $review_id) {
        $review_title = get_the_title($review_id);
        if (!$review_title) continue;

        $review_slug = get_post_field('post_name', $review_id);
        $categories = wp_get_post_terms($review_id, 'saas-category');
        $category = !empty($categories) ? $categories[0] : null;

        // Full review link
        echo '<li><a class="content-cluster__link" href="' . esc_url(get_permalink($review_id)) . '">Read Our Full ' . esc_html($review_title) . ' Review</a></li>';

        // Alternatives
        echo '<li><a class="content-cluster__link" href="' . esc_url(home_url('/alternatives/' . $review_slug . '/')) . '">Alternatives to ' . esc_html($review_title) . '</a></li>';

        // Category hub
        if ($category) {
            echo '<li><a class="content-cluster__link" href="' . esc_url(home_url('/best/' . $category->slug . '-software/')) . '">Best ' . esc_html($category->name) . ' Software</a></li>';
        }
    }

    echo '</ul></nav>';
    $links_html = ob_get_clean();

    return $content . $links_html;
}
add_filter('the_content', 'saasfinder_blog_internal_links', 31);

/**
 * Auto-generate "Related Questions" nav on single-query-answer posts.
 */
function saasfinder_related_questions_links($content) {
    if (!is_singular('post') || is_admin()) return $content;

    $terms = wp_get_post_terms(get_the_ID(), 'blog-format', array('fields' => 'slugs'));
    if (empty($terms) || $terms[0] !== 'single-query-answer') return $content;

    // Find other single-query-answer posts in the same saas-category
    $categories = wp_get_post_terms(get_the_ID(), 'saas-category');
    if (empty($categories)) return $content;

    $related = get_posts(array(
        'post_type'    => 'post',
        'numberposts'  => 5,
        'post__not_in' => array(get_the_ID()),
        'tax_query'    => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'blog-format',
                'field'    => 'slug',
                'terms'    => 'single-query-answer',
            ),
            array(
                'taxonomy' => 'saas-category',
                'field'    => 'term_id',
                'terms'    => wp_list_pluck($categories, 'term_id'),
            ),
        ),
    ));

    if (empty($related)) return $content;

    ob_start();
    echo '<nav class="content-cluster" aria-label="Related questions">';
    echo '<div class="content-cluster__title">Related Questions</div>';
    echo '<ul class="content-cluster__links">';
    foreach ($related as $post) {
        $query = get_post_meta($post->ID, '_blog_query_text', true) ?: $post->post_title;
        echo '<li><a class="content-cluster__link" href="' . esc_url(get_permalink($post)) . '">' . esc_html($query) . '</a></li>';
    }
    echo '</ul></nav>';

    return $content . ob_get_clean();
}
add_filter('the_content', 'saasfinder_related_questions_links', 32);
