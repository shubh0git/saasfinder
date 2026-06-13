<?php
/**
 * Archive: SaaS Reviews — Filterable grid with AJAX.
 *
 * Filters: category, pricing model, audience, rating
 * Sort: rating, newest, alphabetical
 *
 * @package SaasFinder
 */

get_header();
?>

<div class="container" style="padding:var(--space-8) 0;">
    <h1>SaaS Reviews</h1>
    <p class="text-muted" style="margin-bottom:var(--space-8);">In-depth reviews of the best B2B and B2C software tools.</p>

    <!-- Filters (AJAX-powered) -->
    <div class="archive-filters" id="review-filters">
        <select class="archive-filters__select" data-filter="category" aria-label="Filter by category">
            <option value="">All Categories</option>
            <?php
            $cats = get_terms(array('taxonomy' => 'saas-category', 'hide_empty' => true));
            if (!is_wp_error($cats)) :
                foreach ($cats as $cat) :
            ?>
                <option value="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></option>
            <?php endforeach; endif; ?>
        </select>

        <select class="archive-filters__select" data-filter="pricing" aria-label="Filter by pricing">
            <option value="">All Pricing</option>
            <?php
            $pricing = get_terms(array('taxonomy' => 'pricing-model', 'hide_empty' => true));
            if (!is_wp_error($pricing)) :
                foreach ($pricing as $term) :
            ?>
                <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
            <?php endforeach; endif; ?>
        </select>

        <select class="archive-filters__select" data-filter="audience" aria-label="Filter by audience">
            <option value="">All Audiences</option>
            <?php
            $audiences = get_terms(array('taxonomy' => 'audience', 'hide_empty' => true));
            if (!is_wp_error($audiences)) :
                foreach ($audiences as $term) :
            ?>
                <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
            <?php endforeach; endif; ?>
        </select>

        <select class="archive-filters__select" data-filter="min_rating" aria-label="Minimum rating">
            <option value="">Any Rating</option>
            <option value="9">9+ Excellent</option>
            <option value="8">8+ Great</option>
            <option value="7">7+ Good</option>
        </select>

        <select class="archive-filters__select" data-filter="sort" aria-label="Sort by">
            <option value="rating">Highest Rated</option>
            <option value="newest">Newest</option>
            <option value="title">A–Z</option>
        </select>
    </div>

    <!-- Results container (populated via AJAX or initial load) -->
    <div id="review-results">
        <div class="grid-auto">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    get_template_part('template-parts/components/review-card');
                endwhile;
            else :
                echo '<p>No reviews found.</p>';
            endif;
            ?>
        </div>

        <?php the_posts_pagination(array('mid_size' => 2)); ?>
    </div>
</div>

<?php get_footer(); ?>
