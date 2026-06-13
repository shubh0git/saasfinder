<?php
/**
 * 404 Page Template
 *
 * Helpful error page that guides visitors to useful content
 * rather than dead-ending them.
 *
 * @package SaasFinder
 */

get_header();
?>

<div class="container" style="padding:var(--space-16) 0;text-align:center;max-width:var(--container-narrow);margin-inline:auto;">

    <h1 style="font-size:4rem;color:var(--brand-primary);margin-bottom:var(--space-4);">404</h1>
    <h2 style="margin-top:0;margin-bottom:var(--space-4);">Page Not Found</h2>
    <p class="text-muted" style="font-size:var(--text-lg);margin-bottom:var(--space-8);">
        The page you're looking for doesn't exist or has been moved.
        Try searching or browse our most popular content below.
    </p>

    <!-- Search -->
    <form class="search-bar" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get" style="margin-bottom:var(--space-12);">
        <label for="search-404" class="sr-only">Search SaasFinder</label>
        <input type="search" id="search-404" class="search-bar__input" name="s" placeholder="Search tools, reviews, or topics..." autofocus>
        <button type="submit" class="search-bar__btn">Search</button>
    </form>

    <!-- Popular content -->
    <div style="text-align:left;">
        <h3>Popular Reviews</h3>
        <div class="grid-auto" style="margin-bottom:var(--space-8);">
            <?php
            $popular = get_posts(array(
                'post_type'      => 'saas-review',
                'posts_per_page' => 3,
                'meta_key'       => '_saas_tool_rating',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ));
            foreach ($popular as $p) :
                $GLOBALS['post'] = $p;
                setup_postdata($p);
                get_template_part('template-parts/components/review-card');
            endforeach;
            wp_reset_postdata();
            ?>
        </div>

        <h3>Browse by Category</h3>
        <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);margin-bottom:var(--space-8);">
            <?php
            $cats = get_terms(array('taxonomy' => 'saas-category', 'hide_empty' => true, 'number' => 10));
            if (!is_wp_error($cats)) :
                foreach ($cats as $cat) :
            ?>
            <a href="<?php echo esc_url(home_url('/best/' . $cat->slug . '-software/')); ?>" class="content-cluster__link"><?php echo esc_html($cat->name); ?></a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--secondary">← Back to Homepage</a>
</div>

<?php get_footer(); ?>
