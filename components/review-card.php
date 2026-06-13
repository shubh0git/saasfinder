<?php
/**
 * Component: Review Card — used in grids across the theme.
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();
$rating = get_post_meta($post_id, '_saas_tool_rating', true);
$best_for = get_post_meta($post_id, '_saas_tool_best_for', true);
$pricing = get_post_meta($post_id, '_saas_tool_pricing', true);
$verdict = get_post_meta($post_id, '_saas_tool_verdict', true);
$categories = wp_get_post_terms($post_id, 'saas-category', array('fields' => 'names'));
$audiences = wp_get_post_terms($post_id, 'audience', array('fields' => 'slugs'));
?>

<article class="card review-card">
    <?php if (has_post_thumbnail()) : ?>
        <a href="<?php the_permalink(); ?>" style="display:block;margin-bottom:var(--space-4);">
            <?php the_post_thumbnail('saas-logo', array(
                'loading' => 'lazy',
                'width'   => 200,
                'height'  => 200,
                'style'   => 'width:48px;height:48px;object-fit:contain;border-radius:8px;',
            )); ?>
        </a>
    <?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:var(--space-2);">
        <h3 style="font-size:var(--text-lg);margin:0;">
            <a href="<?php the_permalink(); ?>" style="color:var(--text-primary);text-decoration:none;"><?php the_title(); ?></a>
        </h3>
        <?php if ($rating) : ?>
            <span class="badge badge--b2b" style="flex-shrink:0;"><?php echo esc_html($rating); ?>/10</span>
        <?php endif; ?>
    </div>

    <?php if ($verdict) : ?>
        <p class="text-sm" style="margin-bottom:var(--space-3);"><?php echo esc_html($verdict); ?></p>
    <?php endif; ?>

    <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);font-size:var(--text-xs);color:var(--text-secondary);">
        <?php if ($pricing) : ?>
            <span>From <?php echo esc_html($pricing); ?></span>
        <?php endif; ?>
        <?php if (!empty($categories)) : ?>
            <span>&middot; <?php echo esc_html($categories[0]); ?></span>
        <?php endif; ?>
        <?php if (!empty($audiences)) : ?>
            <span class="badge badge--<?php echo esc_attr($audiences[0]); ?>"><?php echo esc_html(strtoupper($audiences[0])); ?></span>
        <?php endif; ?>
    </div>
</article>
