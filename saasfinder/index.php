<?php
/**
 * Default index template — fallback for any unmatched query.
 *
 * @package SaasFinder
 */

get_header();
?>

<div class="container" style="padding:var(--space-8) 0;">
    <div class="layout-with-sidebar">
        <div>
            <?php if (have_posts()) : ?>
                <div class="grid-auto">
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class('card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('saas-card', array('loading' => 'lazy', 'width' => 400, 'height' => 300)); ?>
                                </a>
                            <?php endif; ?>
                            <h2 style="font-size:var(--text-xl);margin-top:var(--space-4);">
                                <a href="<?php the_permalink(); ?>" style="color:var(--text-primary);text-decoration:none;"><?php the_title(); ?></a>
                            </h2>
                            <p class="text-sm text-muted"><?php the_excerpt(); ?></p>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php the_posts_pagination(array('mid_size' => 2)); ?>
            <?php else : ?>
                <p>No content found.</p>
            <?php endif; ?>
        </div>

        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>
