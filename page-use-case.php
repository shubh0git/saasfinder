<?php
/**
 * Template: Best SaaS Tools For [Audience/Use Case]
 *
 * URL pattern: /for/{slug}/  (e.g., /for/freelancers/, /for/startups/)
 * Programmatic page targeting audience-based search intent.
 *
 * AEO strategy:
 * - Definitive opening paragraph citing top picks with context
 * - Ranked table for quick scanning
 * - Expanded cards for top 5
 * - Use-case-specific FAQ
 * - Internal links to related hubs and comparisons
 *
 * @package SaasFinder
 */

get_header();

$use_case_slug = get_query_var('saas_use_case', '');
$year = date('Y');

// Clean display name
$use_case_name = ucwords(str_replace('-', ' ', $use_case_slug));

// Try to find tools tagged with this audience term
$audience_term = get_term_by('slug', $use_case_slug, 'audience');

// Query reviews by audience taxonomy, fallback to all reviews
$query_args = array(
    'post_type'      => 'saas-review',
    'posts_per_page' => 15,
    'post_status'    => 'publish',
    'meta_key'       => '_saas_tool_rating',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
);

if ($audience_term) {
    $query_args['tax_query'] = array(array(
        'taxonomy' => 'audience',
        'terms'    => array($audience_term->term_id),
    ));
}

$reviews = get_posts($query_args);

// Collect tool data
$tools_data = array();
foreach ($reviews as $review) {
    $tools_data[] = array(
        'id'          => $review->ID,
        'title'       => get_the_title($review->ID),
        'url'         => get_permalink($review->ID),
        'rating'      => floatval(get_post_meta($review->ID, '_saas_tool_rating', true)),
        'pricing'     => get_post_meta($review->ID, '_saas_tool_pricing', true),
        'best_for'    => get_post_meta($review->ID, '_saas_tool_best_for', true),
        'description' => get_post_meta($review->ID, '_saas_tool_description', true),
        'free_plan'   => get_post_meta($review->ID, '_saas_tool_free_plan', true),
        'verdict'     => get_post_meta($review->ID, '_saas_tool_verdict', true),
        'aff_url'     => get_post_meta($review->ID, '_saas_affiliate_url', true),
        'categories'  => wp_get_post_terms($review->ID, 'saas-category', array('fields' => 'names')),
    );
}
?>

<article class="use-case-page">
<div class="container" style="padding:var(--space-8) 0;max-width:var(--container-default);margin-inline:auto;">

    <!-- Breadcrumb context -->
    <header style="margin-bottom:var(--space-8);">
        <h1>Best SaaS Tools for <?php echo esc_html($use_case_name); ?> (<?php echo $year; ?>)</h1>

        <?php if ($audience_term && $audience_term->description) : ?>
            <p class="text-muted" style="font-size:var(--text-lg);margin-top:var(--space-2);">
                <?php echo esc_html($audience_term->description); ?>
            </p>
        <?php endif; ?>
    </header>

    <!-- ===== AI-Quotable Definitive Paragraph ===== -->
    <?php if (count($tools_data) >= 3) : ?>
    <div class="quick-answer" style="margin-bottom:var(--space-8);">
        <p>The best SaaS tools for <?php echo esc_html(strtolower($use_case_name)); ?> in <?php echo $year; ?> are
        <strong><a href="<?php echo esc_url($tools_data[0]['url']); ?>"><?php echo esc_html($tools_data[0]['title']); ?></a></strong> (<?php echo esc_html($tools_data[0]['rating']); ?>/10<?php echo $tools_data[0]['best_for'] ? ' — best for ' . strtolower($tools_data[0]['best_for']) : ''; ?>),
        <strong><a href="<?php echo esc_url($tools_data[1]['url']); ?>"><?php echo esc_html($tools_data[1]['title']); ?></a></strong> (<?php echo esc_html($tools_data[1]['rating']); ?>/10),
        and <strong><a href="<?php echo esc_url($tools_data[2]['url']); ?>"><?php echo esc_html($tools_data[2]['title']); ?></a></strong> (<?php echo esc_html($tools_data[2]['rating']); ?>/10).
        <?php if ($tools_data[0]['free_plan'] === 'yes') : ?>
            <?php echo esc_html($tools_data[0]['title']); ?> offers a free plan, making it an excellent starting point.
        <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- ===== Quick Picks Cards (Top 3) ===== -->
    <?php if (count($tools_data) >= 3) : ?>
    <section style="margin-bottom:var(--space-12);">
        <h2>Top Picks for <?php echo esc_html($use_case_name); ?></h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--space-4);margin-top:var(--space-4);">
            <?php
            $badges = array('🥇 Best Overall', '🥈 Runner Up', '🥉 Best Value');
            for ($i = 0; $i < 3; $i++) :
                $tool = $tools_data[$i];
            ?>
            <div style="border:1px solid var(--border-default);border-radius:var(--border-radius);padding:var(--space-5);background:var(--bg-card);position:relative;">
                <span class="badge badge--<?php echo $i === 0 ? 'primary' : 'default'; ?>" style="margin-bottom:var(--space-3);display:inline-block;"><?php echo $badges[$i]; ?></span>
                <h3 style="margin:var(--space-2) 0;font-size:var(--text-lg);">
                    <a href="<?php echo esc_url($tool['url']); ?>"><?php echo esc_html($tool['title']); ?></a>
                </h3>
                <p style="color:var(--text-secondary);font-size:var(--text-sm);margin-bottom:var(--space-3);">
                    <?php echo esc_html($tool['description'] ?: $tool['verdict']); ?>
                </p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
                    <span style="font-weight:var(--font-bold);color:var(--rating-excellent);"><?php echo esc_html($tool['rating']); ?>/10</span>
                    <span style="font-size:var(--text-sm);color:var(--text-muted);">
                        <?php echo $tool['free_plan'] === 'yes' ? 'Free plan available' : 'From ' . esc_html($tool['pricing']); ?>
                    </span>
                </div>
                <?php if ($tool['aff_url']) : ?>
                <a href="<?php echo esc_url($tool['aff_url']); ?>" class="btn btn--primary btn--sm" rel="nofollow sponsored" target="_blank" data-track="use-case-pick" style="width:100%;margin-top:var(--space-3);text-align:center;">Try <?php echo esc_html($tool['title']); ?></a>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Full Ranked Table ===== -->
    <?php if (!empty($tools_data)) : ?>
    <section style="margin-bottom:var(--space-12);">
        <h2>All Tools for <?php echo esc_html($use_case_name); ?> — Ranked</h2>
        <div style="overflow-x:auto;margin-top:var(--space-4);">
            <table class="comparison-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th scope="col" style="width:40px;">#</th>
                        <th scope="col">Tool</th>
                        <th scope="col">Best For</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Pricing</th>
                        <th scope="col">Free Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tools_data as $i => $tool) : ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><a href="<?php echo esc_url($tool['url']); ?>" style="font-weight:var(--font-semibold);"><?php echo esc_html($tool['title']); ?></a></td>
                        <td style="font-size:var(--text-sm);"><?php echo esc_html($tool['best_for'] ?: '—'); ?></td>
                        <td style="font-weight:var(--font-bold);"><?php echo esc_html($tool['rating']); ?>/10</td>
                        <td style="font-size:var(--text-sm);"><?php echo esc_html($tool['pricing'] ?: '—'); ?></td>
                        <td>
                            <?php if ($tool['free_plan'] === 'yes') : ?>
                                <span style="color:var(--color-success);">✓ Yes</span>
                            <?php elseif ($tool['free_plan'] === 'limited') : ?>
                                <span style="color:var(--rating-average);">Limited</span>
                            <?php else : ?>
                                <span style="color:var(--text-muted);">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Expanded Details for Top 5 ===== -->
    <?php $top5 = array_slice($tools_data, 0, 5); ?>
    <?php if (!empty($top5)) : ?>
    <section style="margin-bottom:var(--space-12);">
        <h2>Detailed Overview</h2>
        <?php foreach ($top5 as $i => $tool) : ?>
        <div style="border:1px solid var(--border-default);border-radius:var(--border-radius);padding:var(--space-6);margin-top:var(--space-4);background:var(--bg-card);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:var(--space-3);">
                <div>
                    <h3 style="margin:0;"><?php echo ($i + 1) . '. ' . esc_html($tool['title']); ?></h3>
                    <p style="margin:var(--space-1) 0 0;color:var(--text-secondary);font-size:var(--text-sm);">
                        <?php echo esc_html($tool['description']); ?>
                    </p>
                </div>
                <span style="font-size:var(--text-2xl);font-weight:var(--font-bold);color:var(--rating-excellent);"><?php echo esc_html($tool['rating']); ?></span>
            </div>

            <?php if ($tool['verdict']) : ?>
            <p style="margin-top:var(--space-3);font-style:italic;color:var(--text-secondary);">
                "<?php echo esc_html($tool['verdict']); ?>"
            </p>
            <?php endif; ?>

            <div style="display:flex;gap:var(--space-4);margin-top:var(--space-4);flex-wrap:wrap;font-size:var(--text-sm);">
                <span><strong>Pricing:</strong> <?php echo esc_html($tool['pricing'] ?: 'Contact for quote'); ?></span>
                <span><strong>Free Plan:</strong> <?php echo $tool['free_plan'] === 'yes' ? 'Yes' : ($tool['free_plan'] === 'limited' ? 'Limited' : 'No'); ?></span>
                <?php if (!empty($tool['categories'])) : ?>
                <span><strong>Category:</strong> <?php echo esc_html(implode(', ', $tool['categories'])); ?></span>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:var(--space-3);margin-top:var(--space-4);">
                <a href="<?php echo esc_url($tool['url']); ?>" class="btn btn--secondary btn--sm">Read Full Review</a>
                <?php if ($tool['aff_url']) : ?>
                <a href="<?php echo esc_url($tool['aff_url']); ?>" class="btn btn--primary btn--sm" rel="nofollow sponsored" target="_blank" data-track="use-case-detail">Try <?php echo esc_html($tool['title']); ?> →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- ===== FAQ ===== -->
    <section style="margin-bottom:var(--space-12);">
        <h2>FAQ: Tools for <?php echo esc_html($use_case_name); ?></h2>

        <?php
        $free_tools = array_filter($tools_data, function($t) { return $t['free_plan'] === 'yes'; });
        $cheapest = !empty($tools_data) ? $tools_data[0] : null;
        foreach ($tools_data as $t) {
            if ($t['pricing'] && $cheapest && version_compare(preg_replace('/[^0-9.]/', '', $t['pricing']), preg_replace('/[^0-9.]/', '', $cheapest['pricing']), '<')) {
                $cheapest = $t;
            }
        }

        $faqs = array(
            array(
                'q' => 'What is the best tool for ' . strtolower($use_case_name) . ' in ' . $year . '?',
                'a' => !empty($tools_data) ? $tools_data[0]['title'] . ' is our top-rated tool for ' . strtolower($use_case_name) . ', scoring ' . $tools_data[0]['rating'] . '/10 in our hands-on review.' : 'See our ranked list above.',
            ),
            array(
                'q' => 'Are there free tools for ' . strtolower($use_case_name) . '?',
                'a' => !empty($free_tools) ? 'Yes — ' . implode(', ', array_map(function($t) { return $t['title']; }, array_slice($free_tools, 0, 3))) . ' all offer free plans.' : 'Most tools in this category offer free trials, though fully free plans vary.',
            ),
            array(
                'q' => 'How did we choose these tools?',
                'a' => 'We test each tool hands-on, evaluating features, pricing, ease of use, and support. Ratings are based on our editorial scoring rubric, not affiliate relationships.',
            ),
        );
        ?>

        <?php foreach ($faqs as $faq) : ?>
        <details class="faq-item" style="border:1px solid var(--border-default);border-radius:var(--border-radius-sm);margin-bottom:var(--space-2);padding:var(--space-3) var(--space-4);">
            <summary style="cursor:pointer;font-weight:var(--font-semibold);"><?php echo esc_html($faq['q']); ?></summary>
            <p style="margin-top:var(--space-2);color:var(--text-secondary);"><?php echo esc_html($faq['a']); ?></p>
        </details>
        <?php endforeach; ?>
    </section>

    <!-- ===== Internal Links ===== -->
    <nav class="content-cluster" style="margin-bottom:var(--space-8);">
        <h3 class="content-cluster__title">Related Pages</h3>
        <div class="content-cluster__links">
            <?php
            // Link to category hubs for categories represented here
            $linked_categories = array();
            foreach ($tools_data as $tool) {
                foreach ($tool['categories'] as $cat_name) {
                    $linked_categories[$cat_name] = true;
                }
            }
            foreach (array_keys(array_slice($linked_categories, 0, 6)) as $cat_name) :
                $cat_term = get_term_by('name', $cat_name, 'saas-category');
                if ($cat_term) :
            ?>
                <a href="<?php echo esc_url(home_url('/best/' . $cat_term->slug . '-software/')); ?>" class="content-cluster__link">Best <?php echo esc_html($cat_name); ?> Software</a>
            <?php endif; endforeach; ?>
        </div>
    </nav>

</div>
</article>

<?php get_footer(); ?>
