<?php
/**
 * Template: Best [Category] Software — Hub/Pillar Page
 *
 * URL pattern: /best/crm-software/
 * Acts as topical authority pillar linking to all reviews in that category.
 *
 * Features:
 * - Definitive opening paragraph (AI engines cite this)
 * - Auto-rendered linked table of all tools with rating, price, verdict
 * - Category description / editorial content
 * - "By use case" sub-sections
 * - FAQ targeting "best [category] software" queries
 * - Full internal linking to individual reviews, comparisons, alternatives
 *
 * @package SaasFinder
 */

get_header();

// Parse category from URL (format: {slug}-software or just {slug})
$hub_slug = get_query_var('saas_hub_category', '');
$category_slug = preg_replace('/-software$/', '', $hub_slug);

$category = get_term_by('slug', $category_slug, 'saas-category');

if (!$category) :
?>
<div class="container" style="padding:var(--space-12) 0;text-align:center;">
    <h1>Category Not Found</h1>
    <p class="text-muted">We don't have reviews in this category yet.</p>
    <a href="<?php echo esc_url(get_post_type_archive_link('saas-review')); ?>" class="btn btn--outline" style="margin-top:var(--space-4);">Browse All Reviews</a>
</div>
<?php
    get_footer();
    return;
endif;

// Fetch all reviews in this category, sorted by rating
$reviews = get_posts(array(
    'post_type'    => 'saas-review',
    'numberposts'  => -1,
    'post_status'  => 'publish',
    'tax_query'    => array(
        array(
            'taxonomy' => 'saas-category',
            'field'    => 'term_id',
            'terms'    => $category->term_id,
        ),
    ),
    'meta_key'     => '_saas_tool_rating',
    'orderby'      => 'meta_value_num',
    'order'        => 'DESC',
));

$review_count = count($reviews);
$year = date('Y');

// Pre-fetch data for top 3 (used in quick answer)
$top_tools = array_slice($reviews, 0, 3);
$top_data = array();
foreach ($top_tools as $t) {
    $top_data[] = array(
        'name'     => $t->post_title,
        'rating'   => get_post_meta($t->ID, '_saas_tool_rating', true),
        'best_for' => get_post_meta($t->ID, '_saas_tool_best_for', true),
        'pricing'  => get_post_meta($t->ID, '_saas_tool_pricing', true),
    );
}

// Group tools by audience
$b2b_tools = array();
$b2c_tools = array();
foreach ($reviews as $r) {
    $auds = wp_get_post_terms($r->ID, 'audience', array('fields' => 'slugs'));
    if (in_array('b2b', $auds)) $b2b_tools[] = $r;
    if (in_array('b2c', $auds)) $b2c_tools[] = $r;
}

// Group by pricing model
$free_tools = array_filter($reviews, function($r) {
    return get_post_meta($r->ID, '_saas_tool_free_plan', true) === 'yes';
});
?>

<article class="hub-page">
    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">

        <!-- Page Header -->
        <header style="margin-bottom:var(--space-6);">
            <h1>Best <?php echo esc_html($category->name); ?> Software in <?php echo $year; ?></h1>
            <p class="text-sm text-muted">
                <?php echo esc_html($review_count); ?> tools reviewed &middot; Last updated <?php echo date('F j, Y'); ?>
            </p>
        </header>

        <!-- ================================================================
             DEFINITIVE OPENING PARAGRAPH
             This is THE paragraph Perplexity/ChatGPT will cite.
             Must be factual, specific, and directly answer the page query.
             ================================================================ -->
        <section class="quick-answer" aria-label="Top picks summary">
            <p>
                <?php if (count($top_data) >= 3) : ?>
                The best <?php echo esc_html(strtolower($category->name)); ?> software in <?php echo $year; ?> is
                <strong><?php echo esc_html($top_data[0]['name']); ?></strong> for <?php echo esc_html(strtolower($top_data[0]['best_for'])); ?> (rated <?php echo esc_html($top_data[0]['rating']); ?>/10),
                <strong><?php echo esc_html($top_data[1]['name']); ?></strong> for <?php echo esc_html(strtolower($top_data[1]['best_for'])); ?> (<?php echo esc_html($top_data[1]['rating']); ?>/10), and
                <strong><?php echo esc_html($top_data[2]['name']); ?></strong> for <?php echo esc_html(strtolower($top_data[2]['best_for'])); ?> (<?php echo esc_html($top_data[2]['rating']); ?>/10).
                <?php elseif (!empty($top_data)) : ?>
                The best <?php echo esc_html(strtolower($category->name)); ?> software in <?php echo $year; ?> is <strong><?php echo esc_html($top_data[0]['name']); ?></strong>, rated <?php echo esc_html($top_data[0]['rating']); ?>/10.
                <?php endif; ?>
            </p>
        </section>

        <!-- ================================================================
             TOP 3 PICK CARDS (visual, scannable)
             ================================================================ -->
        <?php if (count($top_tools) >= 3) : ?>
        <section style="margin-bottom:var(--space-10);" aria-label="Top 3 picks">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--space-4);">
                <?php foreach ($top_tools as $i => $tool) :
                    $td = $top_data[$i];
                    $url = get_post_meta($tool->ID, '_saas_affiliate_url', true);
                    $rel = get_post_meta($tool->ID, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                    $labels = array('🥇 Best Overall', '🥈 Runner-Up', '🥉 Best Value');
                ?>
                <div class="card <?php echo $i === 0 ? 'editors-pick' : ''; ?>" style="padding:var(--space-6);text-align:center;">
                    <span class="text-sm" style="font-weight:600;color:var(--brand-primary);display:block;margin-bottom:var(--space-3);">
                        <?php echo $labels[$i]; ?>
                    </span>
                    <?php if (has_post_thumbnail($tool->ID)) :
                        echo get_the_post_thumbnail($tool->ID, 'saas-logo', array(
                            'width' => 48, 'height' => 48,
                            'style' => 'width:48px;height:48px;object-fit:contain;border-radius:8px;margin:0 auto var(--space-3);',
                        ));
                    endif; ?>
                    <h3 style="margin:0 0 var(--space-2);font-size:var(--text-xl);"><?php echo esc_html($td['name']); ?></h3>
                    <div style="font-size:var(--text-2xl);font-weight:700;color:var(--brand-primary);margin-bottom:var(--space-2);">
                        <?php echo esc_html($td['rating']); ?>/10
                    </div>
                    <p class="text-sm text-muted">Best for <?php echo esc_html(strtolower($td['best_for'])); ?></p>
                    <p class="text-sm">From <?php echo esc_html($td['pricing']); ?></p>
                    <div style="margin-top:var(--space-4);display:flex;flex-direction:column;gap:var(--space-2);">
                        <?php if ($url) : ?>
                        <a href="<?php echo esc_url($url); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($rel); ?>" target="_blank" data-track="hub-top3-cta" data-tool="<?php echo esc_attr($td['name']); ?>">Try It Free</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(get_permalink($tool)); ?>" class="btn btn--outline">Read Review</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ================================================================
             FULL RANKED TABLE
             The comprehensive table that links to every reviewed tool.
             ================================================================ -->
        <section aria-label="Complete rankings">
            <h2>All <?php echo esc_html($category->name); ?> Tools — Ranked by Rating</h2>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Tool</th>
                            <th style="text-align:center;">Rating</th>
                            <th>Best For</th>
                            <th>Price From</th>
                            <th style="text-align:center;">Free</th>
                            <th>Verdict</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $i => $review) :
                            $r_id      = $review->ID;
                            $r_rating  = get_post_meta($r_id, '_saas_tool_rating', true);
                            $r_best    = get_post_meta($r_id, '_saas_tool_best_for', true);
                            $r_pricing = get_post_meta($r_id, '_saas_tool_pricing', true);
                            $r_free    = get_post_meta($r_id, '_saas_tool_free_plan', true);
                            $r_verdict = get_post_meta($r_id, '_saas_tool_verdict', true);
                            $r_url     = get_post_meta($r_id, '_saas_affiliate_url', true);
                            $r_rel     = get_post_meta($r_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                            $rank = $i + 1;
                        ?>
                        <tr<?php echo $rank <= 3 ? ' style="background:var(--bg-surface);"' : ''; ?>>
                            <td><strong><?php echo $rank; ?></strong></td>
                            <td>
                                <a href="<?php echo esc_url(get_permalink($review)); ?>" style="font-weight:600;color:var(--text-primary);">
                                    <?php echo esc_html($review->post_title); ?>
                                </a>
                            </td>
                            <td style="text-align:center;font-weight:700;"><?php echo esc_html($r_rating); ?>/10</td>
                            <td class="text-sm"><?php echo esc_html($r_best); ?></td>
                            <td><?php echo esc_html($r_pricing); ?></td>
                            <td style="text-align:center;">
                                <?php if ($r_free === 'yes') : ?>
                                    <span style="color:var(--color-success);">✓</span>
                                <?php elseif ($r_free === 'limited') : ?>
                                    <span style="color:var(--color-warning);">~</span>
                                <?php else : ?>
                                    <span style="color:var(--text-muted);">✗</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm"><?php echo esc_html($r_verdict); ?></td>
                            <td>
                                <?php if ($r_url) : ?>
                                <a href="<?php echo esc_url($r_url); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($r_rel); ?>" target="_blank" data-track="hub-table-cta" data-tool="<?php echo esc_attr($review->post_title); ?>" style="white-space:nowrap;">Try It</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ================================================================
             BY USE CASE SECTIONS
             ================================================================ -->
        <?php if (!empty($free_tools)) : ?>
        <section style="margin-top:var(--space-12);">
            <h2>Best Free <?php echo esc_html($category->name); ?> Software</h2>
            <p class="text-muted">Tools with a completely free plan — no credit card required.</p>
            <div class="grid-auto" style="margin-top:var(--space-6);">
                <?php
                $free_slice = array_slice($free_tools, 0, 4);
                foreach ($free_slice as $ft) :
                    $GLOBALS['post'] = $ft;
                    setup_postdata($ft);
                    get_template_part('template-parts/components/review-card');
                endforeach;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($b2b_tools) && count($b2b_tools) >= 2) : ?>
        <section style="margin-top:var(--space-12);">
            <h2>Best <?php echo esc_html($category->name); ?> for B2B Teams</h2>
            <div class="grid-auto" style="margin-top:var(--space-6);">
                <?php
                $b2b_slice = array_slice($b2b_tools, 0, 4);
                foreach ($b2b_slice as $bt) :
                    $GLOBALS['post'] = $bt;
                    setup_postdata($bt);
                    get_template_part('template-parts/components/review-card');
                endforeach;
                wp_reset_postdata();
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ================================================================
             CATEGORY DESCRIPTION (editorial content, from term description)
             ================================================================ -->
        <?php if ($category->description) : ?>
        <section style="margin-top:var(--space-12);">
            <h2>What is <?php echo esc_html($category->name); ?> Software?</h2>
            <div class="entry-content">
                <?php echo wp_kses_post(wpautop($category->description)); ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ================================================================
             FAQ SECTION — targets "best [category] software" PAA queries
             ================================================================ -->
        <section class="faq-section" style="margin-top:var(--space-12);" aria-label="FAQ">
            <h2>Frequently Asked Questions</h2>

            <details>
                <summary>What is the best <?php echo esc_html(strtolower($category->name)); ?> software in <?php echo $year; ?>?</summary>
                <div>
                    <p>
                        Based on our hands-on reviews, the best <?php echo esc_html(strtolower($category->name)); ?> software in <?php echo $year; ?> is <?php echo esc_html($top_data[0]['name']); ?> (rated <?php echo esc_html($top_data[0]['rating']); ?>/10). It's best for <?php echo esc_html(strtolower($top_data[0]['best_for'])); ?> with pricing from <?php echo esc_html($top_data[0]['pricing']); ?>.
                    </p>
                </div>
            </details>

            <?php if (!empty($free_tools)) : ?>
            <details>
                <summary>Is there free <?php echo esc_html(strtolower($category->name)); ?> software?</summary>
                <div>
                    <p>
                        Yes! <?php echo count($free_tools); ?> of the <?php echo esc_html(strtolower($category->name)); ?> tools we reviewed offer free plans. The highest-rated free option is
                        <?php echo esc_html($free_tools[array_key_first($free_tools)]->post_title); ?>.
                    </p>
                </div>
            </details>
            <?php endif; ?>

            <details>
                <summary>How many <?php echo esc_html(strtolower($category->name)); ?> tools have you reviewed?</summary>
                <div>
                    <p>We've reviewed <?php echo esc_html($review_count); ?> <?php echo esc_html(strtolower($category->name)); ?> tools. We add new reviews regularly and update existing ones when pricing or features change.</p>
                </div>
            </details>

            <details>
                <summary>How do you rate <?php echo esc_html(strtolower($category->name)); ?> software?</summary>
                <div>
                    <p>We rate each tool on a scale of 1-10 based on features, ease of use, pricing value, customer support, and integration capabilities. All ratings are based on hands-on testing and research.</p>
                </div>
            </details>
        </section>

        <!-- ================================================================
             INTERNAL LINKS — connect this hub to the rest of the content cluster
             ================================================================ -->
        <nav class="content-cluster" style="margin-top:var(--space-12);" aria-label="Related content">
            <div class="content-cluster__title">Explore <?php echo esc_html($category->name); ?> Content</div>
            <ul class="content-cluster__links">
                <?php
                // Link to top tool comparisons
                if (count($reviews) >= 2) :
                    $r1_slug = get_post_field('post_name', $reviews[0]);
                    $r2_slug = get_post_field('post_name', $reviews[1]);
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/compare/' . $r1_slug . '-vs-' . $r2_slug . '/')); ?>"><?php echo esc_html($reviews[0]->post_title); ?> vs <?php echo esc_html($reviews[1]->post_title); ?></a></li>
                <?php endif; ?>

                <?php
                // Link to individual reviews
                foreach (array_slice($reviews, 0, 5) as $r) :
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(get_permalink($r)); ?>"><?php echo esc_html($r->post_title); ?> Review</a></li>
                <?php endforeach; ?>

                <?php
                // Link to alternatives pages
                foreach (array_slice($reviews, 0, 3) as $r) :
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/alternatives/' . get_post_field('post_name', $r) . '/')); ?>"><?php echo esc_html($r->post_title); ?> Alternatives</a></li>
                <?php endforeach; ?>

                <?php
                // Link to related blog posts in this category
                $related_posts = get_posts(array(
                    'post_type'    => 'post',
                    'numberposts'  => 3,
                    'tax_query'    => array(
                        array(
                            'taxonomy' => 'saas-category',
                            'field'    => 'term_id',
                            'terms'    => $category->term_id,
                        ),
                    ),
                ));
                foreach ($related_posts as $rp) :
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(get_permalink($rp)); ?>"><?php echo esc_html($rp->post_title); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

    </div><!-- .container -->
</article>

<?php get_footer(); ?>
