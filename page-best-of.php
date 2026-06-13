<?php
/**
 * Template Name: Best Of Roundup
 *
 * "Best X tools in 2026" roundup listicle template.
 * Assigned via page attributes in WP admin.
 * Content is editorial; tool grid is auto-generated from saas-category.
 *
 * @package SaasFinder
 */

get_header();

while (have_posts()) : the_post();
    $year = date('Y');
?>

<div class="container" style="padding:var(--space-8) 0;">
    <h1><?php the_title(); ?></h1>

    <p class="text-sm text-muted">
        Updated <?php echo get_the_modified_date('F j, Y'); ?>
    </p>

    <!-- Main editorial content (quick answer, picks, rationale) -->
    <div class="entry-content">
        <?php the_content(); ?>
    </div>

    <!-- Auto-populate from saas-category if the page shares one -->
    <?php
    $categories = wp_get_post_terms(get_the_ID(), 'saas-category');
    if (!empty($categories)) :
        $cat = $categories[0];
        $reviews = get_posts(array(
            'post_type'    => 'saas-review',
            'numberposts'  => 15,
            'tax_query'    => array(
                array(
                    'taxonomy' => 'saas-category',
                    'field'    => 'term_id',
                    'terms'    => $cat->term_id,
                ),
            ),
            'meta_key'     => '_saas_tool_rating',
            'orderby'      => 'meta_value_num',
            'order'        => 'DESC',
        ));

        if (!empty($reviews)) :
    ?>
    <section style="margin-top:var(--space-8);">
        <h2>All <?php echo esc_html($cat->name); ?> Tools We've Reviewed</h2>
        <table>
            <thead>
                <tr><th>#</th><th>Tool</th><th>Rating</th><th>Price From</th><th>Best For</th></tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $i => $r) : ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><a href="<?php echo esc_url(get_permalink($r)); ?>"><?php echo esc_html($r->post_title); ?></a></td>
                    <td><?php echo esc_html(get_post_meta($r->ID, '_saas_tool_rating', true)); ?>/10</td>
                    <td><?php echo esc_html(get_post_meta($r->ID, '_saas_tool_pricing', true)); ?></td>
                    <td><?php echo esc_html(get_post_meta($r->ID, '_saas_tool_best_for', true)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; endif; ?>
</div>

<?php
endwhile;
get_footer();
?>
