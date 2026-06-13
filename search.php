<?php
/**
 * Search Results Template
 *
 * Displays results from SaaS reviews, blog posts, and deals.
 * Groups results by post type for clarity.
 *
 * @package SaasFinder
 */

get_header();

$search_query = get_search_query();
$total_results = $wp_query->found_posts;
?>

<div class="container" style="padding:var(--space-8) 0;">

    <header style="margin-bottom:var(--space-8);">
        <h1>Search Results for "<?php echo esc_html($search_query); ?>"</h1>
        <p class="text-muted"><?php echo esc_html($total_results); ?> result<?php echo $total_results !== 1 ? 's' : ''; ?> found</p>
    </header>

    <!-- Refine search -->
    <form class="search-bar" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get" style="margin-bottom:var(--space-8);">
        <label for="search-refine" class="sr-only">Refine your search</label>
        <input type="search" id="search-refine" class="search-bar__input" name="s" value="<?php echo esc_attr($search_query); ?>">
        <button type="submit" class="search-bar__btn">Search</button>
    </form>

    <?php if (have_posts()) : ?>

        <div class="grid-auto">
            <?php while (have_posts()) : the_post(); ?>
                <?php
                $post_type = get_post_type();
                if ($post_type === 'saas-review') :
                    get_template_part('template-parts/components/review-card');
                elseif ($post_type === 'saas-deal') :
                    get_template_part('template-parts/components/deal-card');
                else :
                    get_template_part('template-parts/components/blog-card');
                endif;
                ?>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(array(
            'mid_size'  => 2,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        )); ?>

    <?php else : ?>

        <div style="text-align:center;padding:var(--space-12) 0;">
            <p style="font-size:var(--text-lg);margin-bottom:var(--space-6);">No results found for "<?php echo esc_html($search_query); ?>"</p>
            <p class="text-muted" style="margin-bottom:var(--space-8);">Try different keywords or browse our categories below.</p>

            <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);justify-content:center;">
                <?php
                $cats = get_terms(array('taxonomy' => 'saas-category', 'hide_empty' => true, 'number' => 12));
                if (!is_wp_error($cats)) :
                    foreach ($cats as $cat) :
                ?>
                <a href="<?php echo esc_url(home_url('/best/' . $cat->slug . '-software/')); ?>" class="content-cluster__link"><?php echo esc_html($cat->name); ?></a>
                <?php endforeach; endif; ?>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
