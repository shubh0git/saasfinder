<?php
/**
 * Template: Side-by-side SaaS Comparison (up to 3 tools)
 *
 * URL pattern: /compare/slack-vs-teams/ or /compare/slack-vs-teams-vs-discord/
 * Dynamically populated from saas-review CPT based on URL slug.
 *
 * Features:
 * - AI-quotable quick verdict at top
 * - Full feature-by-feature comparison table
 * - Individual tool sections with scores
 * - "Winner by use case" summary
 * - FAQ section for PAA capture
 * - Proper BreadcrumbList + Product schema
 *
 * @package SaasFinder
 */

get_header();

// Parse comparison slug from URL
$compare_slug = get_query_var('saas_compare_slug', '');
$tool_slugs = array_filter(explode('-vs-', $compare_slug));

if (empty($tool_slugs) || count($tool_slugs) < 2) :
?>
<div class="container" style="padding:var(--space-12) 0;text-align:center;">
    <h1>Comparison Not Found</h1>
    <p class="text-muted">Invalid comparison URL. Expected format: <code>/compare/tool1-vs-tool2/</code></p>
    <a href="<?php echo esc_url(get_post_type_archive_link('saas-review')); ?>" class="btn btn--outline" style="margin-top:var(--space-4);">Browse All Reviews</a>
</div>
<?php
    get_footer();
    return;
endif;

// Fetch review posts by slug
$tools = array();
$tool_data = array();

foreach ($tool_slugs as $slug) {
    $post = get_page_by_path(trim($slug), OBJECT, 'saas-review');
    if ($post) {
        $tools[] = $post;
        $tool_data[$post->ID] = array(
            'name'        => $post->post_title,
            'slug'        => $post->post_name,
            'rating'      => get_post_meta($post->ID, '_saas_tool_rating', true),
            'description' => get_post_meta($post->ID, '_saas_tool_description', true),
            'best_for'    => get_post_meta($post->ID, '_saas_tool_best_for', true),
            'pricing'     => get_post_meta($post->ID, '_saas_tool_pricing', true),
            'free_plan'   => get_post_meta($post->ID, '_saas_tool_free_plan', true),
            'verdict'     => get_post_meta($post->ID, '_saas_tool_verdict', true),
            'quick_answer'=> get_post_meta($post->ID, '_saas_quick_answer', true),
            'url'         => get_post_meta($post->ID, '_saas_affiliate_url', true),
            'rel'         => get_post_meta($post->ID, '_saas_affiliate_rel', true) ?: 'nofollow sponsored',
            'permalink'   => get_permalink($post),
        );
    }
}

if (count($tools) < 2) :
?>
<div class="container" style="padding:var(--space-12) 0;text-align:center;">
    <h1>Tools Not Found</h1>
    <p class="text-muted">One or more tools in this comparison don't have a published review yet.</p>
    <a href="<?php echo esc_url(get_post_type_archive_link('saas-review')); ?>" class="btn btn--outline" style="margin-top:var(--space-4);">Browse All Reviews</a>
</div>
<?php
    get_footer();
    return;
endif;

// Build page metadata
$tool_names = array_map(function($t) { return $t->post_title; }, $tools);
$page_title = implode(' vs ', $tool_names);
$year = date('Y');

// Determine "winner" (highest rated)
$sorted_by_rating = $tool_data;
uasort($sorted_by_rating, function($a, $b) {
    return floatval($b['rating']) <=> floatval($a['rating']);
});
$winner = reset($sorted_by_rating);
$winner_id = key($sorted_by_rating);
?>

<article class="comparison-page">
    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">

        <!-- Page Header -->
        <header class="comparison-header">
            <h1><?php echo esc_html($page_title); ?> — Comparison <?php echo $year; ?></h1>
            <p class="text-muted" style="max-width:700px;">
                A detailed side-by-side comparison based on our hands-on reviews. Last updated <?php echo get_the_date('F j, Y', $tools[0]); ?>.
            </p>
        </header>

        <!-- ================================================================
             QUICK VERDICT (AI-quotable block)
             This is what Perplexity/ChatGPT will cite.
             ================================================================ -->
        <section class="quick-answer" aria-label="Quick comparison verdict">
            <p>
                <strong><?php echo esc_html($winner['name']); ?></strong> wins this comparison with a rating of <?php echo esc_html($winner['rating']); ?>/10.
                <?php echo esc_html($winner['name']); ?> is best for <?php echo esc_html(strtolower($winner['best_for'])); ?>, with pricing from <?php echo esc_html($winner['pricing']); ?>.
                <?php
                // Second tool context
                $other_tools = array_filter($tool_data, function($d) use ($winner) { return $d['name'] !== $winner['name']; });
                $second = reset($other_tools);
                if ($second) {
                    echo esc_html($second['name']) . ' (rated ' . esc_html($second['rating']) . '/10) is better for ' . esc_html(strtolower($second['best_for'])) . '.';
                }
                ?>
            </p>
        </section>

        <!-- ================================================================
             VISUAL SCORE COMPARISON (cards side by side)
             ================================================================ -->
        <section class="comparison-scores" aria-label="Score overview">
            <div class="comparison-scores__grid" style="display:grid;grid-template-columns:repeat(<?php echo count($tools); ?>,1fr);gap:var(--space-4);margin-bottom:var(--space-8);">
                <?php foreach ($tools as $tool) :
                    $d = $tool_data[$tool->ID];
                    $is_winner = ($tool->ID === $winner_id);
                ?>
                <div class="card comparison-scores__card <?php echo $is_winner ? 'editors-pick' : ''; ?>" style="text-align:center;padding:var(--space-6);">
                    <?php if (has_post_thumbnail($tool->ID)) : ?>
                        <div style="margin-bottom:var(--space-3);">
                            <?php echo get_the_post_thumbnail($tool->ID, 'saas-logo', array(
                                'width' => 48, 'height' => 48,
                                'style' => 'width:48px;height:48px;object-fit:contain;border-radius:8px;margin:0 auto;',
                            )); ?>
                        </div>
                    <?php endif; ?>
                    <h2 style="font-size:var(--text-xl);margin:0 0 var(--space-2);"><?php echo esc_html($d['name']); ?></h2>
                    <div style="font-size:var(--text-3xl);font-weight:700;color:var(--brand-primary);margin-bottom:var(--space-2);">
                        <?php echo esc_html($d['rating']); ?><span style="font-size:var(--text-lg);color:var(--text-muted);">/10</span>
                    </div>
                    <p class="text-sm text-muted" style="margin-bottom:var(--space-4);"><?php echo esc_html($d['verdict']); ?></p>
                    <?php if ($d['url']) : ?>
                    <a href="<?php echo esc_url($d['url']); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($d['rel']); ?>" target="_blank" data-track="comparison-top-cta" data-tool="<?php echo esc_attr($d['name']); ?>">
                        Try <?php echo esc_html($d['name']); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ================================================================
             FULL COMPARISON TABLE
             Real HTML table — AI parsers and featured snippets prefer this.
             ================================================================ -->
        <section class="comparison-table" aria-label="Feature comparison table">
            <h2>Feature-by-Feature Comparison</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left;">Feature</th>
                            <?php foreach ($tools as $tool) :
                                $is_winner = ($tool->ID === $winner_id);
                            ?>
                            <th style="text-align:center;<?php echo $is_winner ? 'background:var(--brand-primary-light);' : ''; ?>">
                                <?php echo esc_html($tool->post_title); ?>
                                <?php if ($is_winner) : ?><br><small style="color:var(--brand-primary);">★ Our Pick</small><?php endif; ?>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Our Rating</strong></td>
                            <?php foreach ($tools as $tool) : ?>
                            <td style="text-align:center;font-weight:700;font-size:var(--text-lg);">
                                <?php echo esc_html($tool_data[$tool->ID]['rating']); ?>/10
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Best For</strong></td>
                            <?php foreach ($tools as $tool) : ?>
                            <td style="text-align:center;"><?php echo esc_html($tool_data[$tool->ID]['best_for']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Starting Price</strong></td>
                            <?php foreach ($tools as $tool) : ?>
                            <td style="text-align:center;"><?php echo esc_html($tool_data[$tool->ID]['pricing']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Free Plan</strong></td>
                            <?php foreach ($tools as $tool) :
                                $fp = $tool_data[$tool->ID]['free_plan'];
                            ?>
                            <td style="text-align:center;">
                                <?php if ($fp === 'yes') : ?>
                                    <span style="color:var(--color-success);font-weight:600;">✓ Yes</span>
                                <?php elseif ($fp === 'limited') : ?>
                                    <span style="color:var(--color-warning);font-weight:600;">~ Limited</span>
                                <?php else : ?>
                                    <span style="color:var(--color-error);font-weight:600;">✗ No</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>What It Does</strong></td>
                            <?php foreach ($tools as $tool) : ?>
                            <td style="text-align:center;font-size:var(--text-sm);"><?php echo esc_html($tool_data[$tool->ID]['description']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td><strong>Our Verdict</strong></td>
                            <?php foreach ($tools as $tool) : ?>
                            <td style="text-align:center;font-size:var(--text-sm);"><?php echo esc_html($tool_data[$tool->ID]['verdict']); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td></td>
                            <?php foreach ($tools as $tool) :
                                $d = $tool_data[$tool->ID];
                            ?>
                            <td style="text-align:center;">
                                <?php if ($d['url']) : ?>
                                <a href="<?php echo esc_url($d['url']); ?>"
                                   class="btn btn--primary saas-cta"
                                   rel="<?php echo esc_attr($d['rel']); ?>"
                                   target="_blank"
                                   data-track="comparison-table-cta"
                                   data-tool="<?php echo esc_attr($d['name']); ?>">
                                    Try It Free
                                </a>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ================================================================
             WINNER BY USE CASE
             Helps readers self-select based on their situation.
             ================================================================ -->
        <section class="comparison-verdict-section" style="margin-top:var(--space-12);">
            <h2>Which One Should You Choose?</h2>

            <div class="comparison-use-cases" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--space-6);margin-top:var(--space-6);">
                <?php foreach ($tools as $tool) :
                    $d = $tool_data[$tool->ID];
                ?>
                <div class="card" style="border-top:4px solid var(--brand-primary);">
                    <h3 style="margin-top:0;font-size:var(--text-lg);">Choose <?php echo esc_html($d['name']); ?> if...</h3>
                    <p>You're <?php echo esc_html(strtolower($d['best_for'])); ?> and need <?php echo esc_html(strtolower($d['description'])); ?>.</p>
                    <p class="text-sm text-muted">
                        Rated <?php echo esc_html($d['rating']); ?>/10 &middot; From <?php echo esc_html($d['pricing']); ?>
                        <?php if ($d['free_plan'] === 'yes') echo '&middot; Free plan available'; ?>
                    </p>
                    <div style="margin-top:var(--space-4);display:flex;gap:var(--space-3);">
                        <?php if ($d['url']) : ?>
                        <a href="<?php echo esc_url($d['url']); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($d['rel']); ?>" target="_blank" data-track="usecase-cta" data-tool="<?php echo esc_attr($d['name']); ?>">Try It</a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($d['permalink']); ?>" class="btn btn--outline">Full Review</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ================================================================
             INDIVIDUAL TOOL SUMMARIES
             Each tool gets its own section with the quick answer from its review.
             ================================================================ -->
        <section style="margin-top:var(--space-12);">
            <h2>Detailed Overviews</h2>

            <?php foreach ($tools as $tool) :
                $d = $tool_data[$tool->ID];
            ?>
            <div class="card" style="margin-top:var(--space-6);padding:var(--space-8);">
                <div style="display:flex;align-items:center;gap:var(--space-4);margin-bottom:var(--space-4);">
                    <?php if (has_post_thumbnail($tool->ID)) : ?>
                        <?php echo get_the_post_thumbnail($tool->ID, 'saas-logo', array(
                            'width' => 40, 'height' => 40,
                            'style' => 'width:40px;height:40px;object-fit:contain;border-radius:8px;',
                        )); ?>
                    <?php endif; ?>
                    <h3 style="margin:0;font-size:var(--text-2xl);"><?php echo esc_html($d['name']); ?> — <?php echo esc_html($d['rating']); ?>/10</h3>
                </div>

                <?php if ($d['quick_answer']) : ?>
                <p style="font-size:var(--text-lg);line-height:var(--leading-relaxed);margin-bottom:var(--space-4);">
                    <?php echo wp_kses_post($d['quick_answer']); ?>
                </p>
                <?php endif; ?>

                <div style="display:flex;flex-wrap:wrap;gap:var(--space-3);margin-top:var(--space-4);">
                    <a href="<?php echo esc_url($d['permalink']); ?>" class="btn btn--outline">Read Full <?php echo esc_html($d['name']); ?> Review</a>
                    <?php if ($d['url']) : ?>
                    <a href="<?php echo esc_url($d['url']); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($d['rel']); ?>" target="_blank" data-track="overview-cta" data-tool="<?php echo esc_attr($d['name']); ?>">Try <?php echo esc_html($d['name']); ?> Free</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- ================================================================
             FAQ SECTION
             Targets "[Tool1] vs [Tool2]" People Also Ask queries.
             ================================================================ -->
        <section class="faq-section" style="margin-top:var(--space-12);" aria-label="FAQ">
            <h2>Frequently Asked Questions</h2>

            <details>
                <summary>Is <?php echo esc_html($tool_names[0]); ?> better than <?php echo esc_html($tool_names[1]); ?>?</summary>
                <div>
                    <p>
                        <?php
                        $t1 = $tool_data[$tools[0]->ID];
                        $t2 = $tool_data[$tools[1]->ID];
                        if (floatval($t1['rating']) > floatval($t2['rating'])) {
                            printf(
                                '%s scores higher in our review (%s/10 vs %s/10). %s is best for %s, while %s is best for %s. The right choice depends on your specific needs.',
                                esc_html($t1['name']), esc_html($t1['rating']), esc_html($t2['rating']),
                                esc_html($t1['name']), esc_html(strtolower($t1['best_for'])),
                                esc_html($t2['name']), esc_html(strtolower($t2['best_for']))
                            );
                        } else {
                            printf(
                                '%s scores higher (%s/10 vs %s/10). Choose %s if you\'re %s; choose %s if you\'re %s.',
                                esc_html($t2['name']), esc_html($t2['rating']), esc_html($t1['rating']),
                                esc_html($t2['name']), esc_html(strtolower($t2['best_for'])),
                                esc_html($t1['name']), esc_html(strtolower($t1['best_for']))
                            );
                        }
                        ?>
                    </p>
                </div>
            </details>

            <details>
                <summary>Which is cheaper, <?php echo esc_html($tool_names[0]); ?> or <?php echo esc_html($tool_names[1]); ?>?</summary>
                <div>
                    <p>
                        <?php echo esc_html($t1['name']); ?> starts at <?php echo esc_html($t1['pricing']); ?> (free plan: <?php echo esc_html($t1['free_plan'] === 'yes' ? 'yes' : 'no'); ?>).
                        <?php echo esc_html($t2['name']); ?> starts at <?php echo esc_html($t2['pricing']); ?> (free plan: <?php echo esc_html($t2['free_plan'] === 'yes' ? 'yes' : 'no'); ?>).
                    </p>
                </div>
            </details>

            <details>
                <summary>Can I switch from <?php echo esc_html($tool_names[0]); ?> to <?php echo esc_html($tool_names[1]); ?> easily?</summary>
                <div>
                    <p>Most SaaS tools offer data export features. Check both tools' documentation for import/export capabilities. Many also offer migration assistance for paid plans.</p>
                </div>
            </details>

            <?php if (isset($tool_names[2])) : ?>
            <details>
                <summary>How does <?php echo esc_html($tool_names[2]); ?> compare to the other two?</summary>
                <div>
                    <p>
                        <?php
                        $t3 = $tool_data[$tools[2]->ID];
                        printf(
                            '%s is rated %s/10, best for %s, starting at %s. Read our full review for a detailed breakdown.',
                            esc_html($t3['name']), esc_html($t3['rating']),
                            esc_html(strtolower($t3['best_for'])),
                            esc_html($t3['pricing'])
                        );
                        ?>
                    </p>
                </div>
            </details>
            <?php endif; ?>
        </section>

        <!-- ================================================================
             INTERNAL LINKS
             ================================================================ -->
        <nav class="content-cluster" style="margin-top:var(--space-12);" aria-label="Related comparisons">
            <div class="content-cluster__title">Related Pages</div>
            <ul class="content-cluster__links">
                <?php foreach ($tools as $tool) :
                    $d = $tool_data[$tool->ID];
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url($d['permalink']); ?>">Full <?php echo esc_html($d['name']); ?> Review</a></li>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/alternatives/' . $d['slug'] . '/')); ?>"><?php echo esc_html($d['name']); ?> Alternatives</a></li>
                <?php endforeach; ?>
                <?php
                // Link to category hub if tools share a category
                $shared_cats = wp_get_post_terms($tools[0]->ID, 'saas-category');
                if (!empty($shared_cats)) :
                    $cat = $shared_cats[0];
                ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/best/' . $cat->slug . '-software/')); ?>">Best <?php echo esc_html($cat->name); ?> Software</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div><!-- .container -->
</article>

<?php get_footer(); ?>
