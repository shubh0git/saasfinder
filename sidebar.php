<?php
/**
 * Sidebar template.
 *
 * Renders the appropriate sidebar based on context:
 * - saas-review: sidebar-review widget area + sticky CTA
 * - blog posts: sidebar-blog widget area
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;
?>

<aside class="sidebar" role="complementary" aria-label="Sidebar">

    <?php if (is_singular('saas-review')) : ?>
        <!-- Sticky CTA for review pages -->
        <div class="sticky-cta">
            <?php
            $post_id = get_the_ID();
            $url = get_post_meta($post_id, '_saas_affiliate_url', true);
            $cta_text = get_post_meta($post_id, '_saas_cta_text', true) ?: 'Try It Free';
            $rating = get_post_meta($post_id, '_saas_tool_rating', true);
            $rel = get_post_meta($post_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
            ?>

            <?php if ($url) : ?>
            <div class="card" style="text-align:center;">
                <?php if ($rating) : ?>
                    <div style="font-size:var(--text-3xl);font-weight:700;color:var(--brand-primary);margin-bottom:var(--space-3);">
                        <?php echo esc_html($rating); ?>/10
                    </div>
                <?php endif; ?>
                <a href="<?php echo esc_url($url); ?>"
                   class="btn btn--primary btn--lg"
                   rel="<?php echo esc_attr($rel); ?>"
                   target="_blank"
                   data-track="sidebar-cta"
                   style="width:100%;margin-bottom:var(--space-3);">
                    <?php echo esc_html($cta_text); ?>
                </a>
                <?php
                $secondary_url = get_post_meta($post_id, '_saas_secondary_url', true);
                $secondary_text = get_post_meta($post_id, '_saas_cta_text_secondary', true) ?: 'View Pricing';
                if ($secondary_url) :
                ?>
                <a href="<?php echo esc_url($secondary_url); ?>"
                   class="btn btn--outline"
                   rel="<?php echo esc_attr($rel); ?>"
                   target="_blank"
                   data-track="sidebar-secondary-cta"
                   style="width:100%;">
                    <?php echo esc_html($secondary_text); ?>
                </a>
                <?php endif; ?>

                <?php
                $verified_text = saasfinder_get_last_verified_text($post_id);
                if ($verified_text) :
                ?>
                    <p class="text-sm text-muted" style="margin-top:var(--space-3);"><?php echo esc_html($verified_text); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Related tools in same category -->
            <?php
            $categories = wp_get_post_terms($post_id, 'saas-category');
            if (!empty($categories)) :
                $related_reviews = get_posts(array(
                    'post_type'    => 'saas-review',
                    'numberposts'  => 4,
                    'post__not_in' => array($post_id),
                    'tax_query'    => array(
                        array(
                            'taxonomy' => 'saas-category',
                            'field'    => 'term_id',
                            'terms'    => $categories[0]->term_id,
                        ),
                    ),
                ));
                if (!empty($related_reviews)) :
            ?>
            <div class="card" style="margin-top:var(--space-4);">
                <h3 class="widget__title">Related Tools</h3>
                <ul style="list-style:none;">
                    <?php foreach ($related_reviews as $related) : ?>
                    <li style="padding:var(--space-2) 0;border-bottom:1px solid var(--border-default);">
                        <a href="<?php echo esc_url(get_permalink($related)); ?>" style="display:flex;justify-content:space-between;align-items:center;text-decoration:none;color:var(--text-primary);">
                            <span><?php echo esc_html($related->post_title); ?></span>
                            <span class="badge badge--b2b"><?php echo esc_html(get_post_meta($related->ID, '_saas_tool_rating', true)); ?>/10</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; endif; ?>

            <?php if (is_active_sidebar('sidebar-review')) : ?>
                <?php dynamic_sidebar('sidebar-review'); ?>
            <?php endif; ?>
        </div>

    <?php else : ?>
        <!-- Blog sidebar -->
        <?php if (is_active_sidebar('sidebar-blog')) : ?>
            <?php dynamic_sidebar('sidebar-blog'); ?>
        <?php endif; ?>
    <?php endif; ?>

</aside>
