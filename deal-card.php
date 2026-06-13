<?php
/**
 * Component: Deal Card — used on front page and deal archives.
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();
$original = get_post_meta($post_id, '_deal_original_price', true);
$discounted = get_post_meta($post_id, '_deal_discounted_price', true);
$discount = get_post_meta($post_id, '_deal_discount_percent', true);
$expires = get_post_meta($post_id, '_deal_expires', true);
$url = get_post_meta($post_id, '_deal_affiliate_url', true);
$cta_text = get_post_meta($post_id, '_deal_cta_text', true) ?: 'Grab Deal';
?>

<article class="card deal-card">
    <?php if ($discount) : ?>
        <span class="badge badge--freemium" style="margin-bottom:var(--space-3);display:inline-block;">-<?php echo esc_html($discount); ?>%</span>
    <?php endif; ?>

    <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-3);">
        <a href="<?php the_permalink(); ?>" style="color:var(--text-primary);text-decoration:none;"><?php the_title(); ?></a>
    </h3>

    <div class="deal-card__prices" style="margin-bottom:var(--space-4);">
        <?php if ($original) : ?>
            <span class="deal-card__original"><?php echo esc_html($original); ?></span>
        <?php endif; ?>
        <?php if ($discounted) : ?>
            <span class="deal-card__discounted"><?php echo esc_html($discounted); ?></span>
        <?php endif; ?>
    </div>

    <?php if ($expires) : ?>
        <p class="text-sm text-muted" style="margin-bottom:var(--space-3);">
            Expires: <?php echo esc_html(date_i18n('M j, Y', strtotime($expires))); ?>
        </p>
    <?php endif; ?>

    <?php if ($url) : ?>
        <a href="<?php echo esc_url($url); ?>" class="btn btn--primary" rel="nofollow sponsored" target="_blank" data-track="deal-card-cta" style="width:100%;">
            <?php echo esc_html($cta_text); ?>
        </a>
    <?php endif; ?>
</article>
