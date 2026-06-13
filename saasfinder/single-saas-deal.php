<?php
/**
 * Single SaaS Deal Template
 *
 * Deal card with countdown timer, pricing, affiliate CTA, terms.
 *
 * @package SaasFinder
 */

get_header();

while (have_posts()) : the_post();
    $post_id     = get_the_ID();
    $url         = get_post_meta($post_id, '_deal_affiliate_url', true);
    $original    = get_post_meta($post_id, '_deal_original_price', true);
    $discounted  = get_post_meta($post_id, '_deal_discounted_price', true);
    $discount    = get_post_meta($post_id, '_deal_discount_percent', true);
    $expires     = get_post_meta($post_id, '_deal_expires', true);
    $terms       = get_post_meta($post_id, '_deal_terms', true);
    $cta_text    = get_post_meta($post_id, '_deal_cta_text', true) ?: 'Grab This Deal';
    $review_id   = get_post_meta($post_id, '_deal_linked_review', true);
?>

<article <?php post_class(); ?>>
    <div class="container" style="padding:var(--space-8) 0;max-width:var(--container-narrow);margin-inline:auto;">

        <div class="deal-card">
            <!-- Countdown Timer -->
            <?php if ($expires) : ?>
            <div class="deal-card__countdown" data-countdown="<?php echo esc_attr($expires); ?>">
                <span class="countdown-text">Deal expires in: <span class="countdown-timer">--:--:--</span></span>
            </div>
            <?php endif; ?>

            <h1 style="margin-bottom:var(--space-4);"><?php the_title(); ?></h1>

            <!-- Pricing -->
            <div class="deal-card__prices" style="margin-bottom:var(--space-6);">
                <?php if ($original) : ?>
                    <span class="deal-card__original"><?php echo esc_html($original); ?></span>
                <?php endif; ?>
                <?php if ($discounted) : ?>
                    <span class="deal-card__discounted"><?php echo esc_html($discounted); ?></span>
                <?php endif; ?>
                <?php if ($discount) : ?>
                    <span class="badge badge--freemium" style="font-size:var(--text-base);margin-left:var(--space-3);">Save <?php echo esc_html($discount); ?>%</span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="entry-content" style="margin-bottom:var(--space-6);">
                <?php the_content(); ?>
            </div>

            <!-- CTA -->
            <?php if ($url) : ?>
            <div style="text-align:center;margin-bottom:var(--space-6);">
                <a href="<?php echo esc_url($url); ?>" class="btn btn--primary btn--lg" rel="nofollow sponsored" target="_blank" data-track="deal-cta" style="width:100%;max-width:400px;">
                    <?php echo esc_html($cta_text); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Terms -->
            <?php if ($terms) : ?>
            <div style="font-size:var(--text-sm);color:var(--text-secondary);border-top:1px solid var(--border-default);padding-top:var(--space-4);">
                <strong>Terms & Conditions:</strong>
                <p><?php echo esc_html($terms); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Linked Review -->
        <?php if ($review_id && get_post_status($review_id) === 'publish') : ?>
        <div style="margin-top:var(--space-8);">
            <h2>Read Our Full Review</h2>
            <?php echo do_shortcode('[related_review id="' . absint($review_id) . '"]'); ?>
        </div>
        <?php endif; ?>

    </div>
</article>

<?php
endwhile;
get_footer();
?>
