<?php
/**
 * Archive: SaaS Deals
 *
 * Grid of active deals sorted by newest first.
 * Expired deals are visually muted but still accessible.
 *
 * @package SaasFinder
 */

get_header();
$year = date('Y');
?>

<div class="container" style="padding:var(--space-8) 0;">
    <header style="margin-bottom:var(--space-8);">
        <h1>SaaS Deals & Discounts (<?php echo $year; ?>)</h1>
        <p class="text-muted" style="font-size:var(--text-lg);">
            Exclusive discounts on B2B and B2C software tools. We verify every deal and update this page regularly.
        </p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="grid-auto">
            <?php while (have_posts()) : the_post();
                get_template_part('template-parts/components/deal-card');
            endwhile; ?>
        </div>

        <?php the_posts_pagination(array(
            'mid_size'  => 2,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        )); ?>

    <?php else : ?>
        <div style="text-align:center;padding:var(--space-12) 0;">
            <p style="font-size:var(--text-lg);margin-bottom:var(--space-4);">No active deals right now.</p>
            <p class="text-muted">Check back soon — we add new deals every week.</p>
            <a href="<?php echo esc_url(get_post_type_archive_link('saas-review')); ?>" class="btn btn--secondary" style="margin-top:var(--space-6);">Browse All Reviews</a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
