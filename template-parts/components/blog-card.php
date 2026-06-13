<?php
/**
 * Component: Blog Card — used on front page and blog archives.
 *
 * @package SaasFinder
 */

$formats = wp_get_post_terms(get_the_ID(), 'blog-format', array('fields' => 'names'));
$format_name = !empty($formats) ? $formats[0] : '';
?>

<article class="card blog-card">
    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php the_permalink(); ?>" style="display:block;margin-bottom:var(--space-4);">
            <?php the_post_thumbnail('saas-card', array(
                'loading' => 'lazy',
                'width'   => 400,
                'height'  => 300,
                'style'   => 'border-radius:var(--border-radius);width:100%;height:auto;',
            )); ?>
        </a>
    <?php endif; ?>

    <?php if ($format_name) : ?>
        <span class="badge badge--b2b" style="margin-bottom:var(--space-2);display:inline-block;"><?php echo esc_html($format_name); ?></span>
    <?php endif; ?>

    <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-2);">
        <a href="<?php the_permalink(); ?>" style="color:var(--text-primary);text-decoration:none;"><?php the_title(); ?></a>
    </h3>

    <p class="text-sm text-muted"><?php the_excerpt(); ?></p>

    <span class="text-sm text-muted"><?php echo get_the_date('M j, Y'); ?></span>
</article>
