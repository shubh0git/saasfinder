<?php
/**
 * Template Part: Default Blog Layout
 *
 * Fallback for posts with no blog-format set, or format: general.
 * Clean layout with optional direct answer block, FAQ section, related sidebar.
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();
$direct_answer_enabled = get_post_meta($post_id, '_blog_direct_answer_enabled', true);
?>

<article <?php post_class(); ?>>
    <div class="container" style="padding:var(--space-8) 0;">
        <div class="layout-with-sidebar">
            <div>
                <header style="margin-bottom:var(--space-8);">
                    <h1><?php the_title(); ?></h1>
                    <p class="text-sm text-muted">
                        By <?php the_author(); ?> &middot; <?php echo get_the_date('F j, Y'); ?>
                        &middot; Updated <?php echo get_the_modified_date('F j, Y'); ?>
                    </p>
                </header>

                <?php if ($direct_answer_enabled) : ?>
                <div class="direct-answer">
                    <?php echo wp_kses_post(get_the_excerpt()); ?>
                </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <?php
                // Comments (enabled for blog posts)
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</article>
