<?php
/**
 * Archive Template (Blog)
 *
 * Used for category, tag, date, and author archives.
 * Displays blog posts in a clean grid.
 *
 * @package SaasFinder
 */

get_header();
?>

<div class="container" style="padding:var(--space-8) 0;">

    <header style="margin-bottom:var(--space-8);">
        <?php
        if (is_category()) :
            echo '<h1>' . single_cat_title('', false) . '</h1>';
            if (category_description()) :
                echo '<p class="text-muted" style="font-size:var(--text-lg);">' . category_description() . '</p>';
            endif;
        elseif (is_tag()) :
            echo '<h1>Tagged: ' . single_tag_title('', false) . '</h1>';
        elseif (is_author()) :
            echo '<h1>Posts by ' . get_the_author() . '</h1>';
        elseif (is_date()) :
            echo '<h1>Archives: ' . get_the_date('F Y') . '</h1>';
        else :
            echo '<h1>Blog</h1>';
        endif;
        ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="grid-auto">
            <?php while (have_posts()) : the_post();
                get_template_part('template-parts/components/blog-card');
            endwhile; ?>
        </div>

        <?php the_posts_pagination(array(
            'mid_size'  => 2,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        )); ?>

    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
