<?php
/**
 * Default Page Template
 *
 * Used for static pages (About, Contact, Privacy Policy, etc.)
 * that don't have a specific template assigned.
 *
 * @package SaasFinder
 */

get_header();

while (have_posts()) : the_post();
?>

<article <?php post_class(); ?>>
    <div class="container" style="padding:var(--space-8) 0;max-width:var(--container-narrow);margin-inline:auto;">

        <header style="margin-bottom:var(--space-8);">
            <h1><?php the_title(); ?></h1>
            <?php if (get_the_modified_date() !== get_the_date()) : ?>
                <p class="text-sm text-muted">Last updated: <?php echo get_the_modified_date('F j, Y'); ?></p>
            <?php endif; ?>
        </header>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>

    </div>
</article>

<?php
endwhile;
get_footer();
?>
