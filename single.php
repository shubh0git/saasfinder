<?php
/**
 * Single Blog Post Template
 *
 * Architecture:
 * - Checks the post's blog-format taxonomy term
 * - Loads matching template part from /template-parts/blog/{format-slug}.php
 * - Falls back to default blog layout if no format set
 *
 * This is the extensibility mechanism for blog content types.
 *
 * @package SaasFinder
 */

get_header();

while (have_posts()) : the_post();

    // Determine blog format
    $terms = wp_get_post_terms(get_the_ID(), 'blog-format', array('fields' => 'slugs'));
    $format = !empty($terms) ? $terms[0] : 'default';

    // Try to load format-specific template part
    $template_found = locate_template('template-parts/blog/' . $format . '.php');

    if ($template_found) {
        get_template_part('template-parts/blog/' . $format);
    } else {
        // Fallback to default blog layout
        get_template_part('template-parts/blog/default');
    }

endwhile;

get_footer();
?>
