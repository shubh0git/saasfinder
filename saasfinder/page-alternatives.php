<?php
/**
 * Template: Best [Tool] Alternatives in [Year]
 *
 * URL pattern: /alternatives/notion/
 * Dynamically pulls from same saas-category, excludes the named tool.
 *
 * Features:
 * - AI-quotable definitive opening paragraph
 * - Ranked alternatives table with ratings, pricing, verdicts
 * - Individual alternative cards with key differentiators
 * - "Why switch from [Tool]?" section
 * - FAQ targeting "[Tool] alternatives" queries
 * - Full internal linking
 *
 * @package SaasFinder
 */

get_header();

$tool_slug = get_query_var('saas_alt_tool', '');
$tool_post = get_page_by_path($tool_slug, OBJECT, 'saas-review');

if (!$tool_post) :
?>
<div class="container" style="padding:var(--space-12) 0;text-align:center;">
    <h1>Tool Not Found</h1>
    <p class="text-muted">We don't have a published review for this tool yet.</p>
    <a href="<?php echo esc_url(get_post_type_archive_link('saas-review')); ?>" class="btn btn--outline" style="margin-top:var(--space-4);">Browse All Reviews</a>
</div>
<?php
    get_footer();
    return;
endif;

$tool_name     = $tool_post->post_title;
$tool_rating   = get_post_meta($tool_post->ID, '_saas_tool_rating', true);
$tool_pricing  = get_post_meta($tool_post->ID, '_saas_tool_pricing', true);
$tool_desc     = get_post_meta($tool_post->ID, '_saas_tool_description', true);
$tool_best_for = get_post_meta($tool_post->ID, '_saas_tool_best_for', true);

$categories = wp_get_post_terms($tool_post->ID, 'saas-category');
$category = !empty($categories) ? $categories[0] : null;
$year = date('Y');

// Fetch alternatives from same category
$alt_args = array(
    'post_type'    => 'saas-review',
    'numberposts'  => 12,
    'post__not_in' => array($tool_post->ID),
    'post_status'  => 'publish',
    'meta_key'     => '_saas_tool_rating',
    'orderby'      => 'meta_value_num',
    'order'        => 'DESC',
);

if ($category) {
    $alt_args['tax_query'] = array(
        array(
            'taxonomy' => 'saas-category',
            'field'    => 'term_id',
            'terms'    => $category->term_id,
        ),
    );
}

$alternatives = get_posts($alt_args);
$alt_count = count($alternatives);
?>

<article class="alternatives-page">
    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">

        <!-- Page Header -->
        <header style="margin-bottom:var(--space-6);">
            <h1><?php echo esc_html($alt_count); ?> Best <?php echo esc_html($tool_name); ?> Alternatives in <?php echo $year; ?></h1>
            <p class="text-sm text-muted">
                Last updated <?php echo date('F j, Y'); ?> &middot; Based on our hands-on reviews
                <?php if ($category) : ?>&middot; Category: <?php echo esc_html($category->name); ?><?php endif; ?>
            </p>
        </header>

        <!-- ================================================================
             AI-QUOTABLE OPENING (the paragraph Perplexity will cite)
             ================================================================ -->
        <section class="quick-answer" aria-label="Quick recommendation">
            <?php if (!empty($alternatives)) :
                $top1 = $alternatives[0];
                $top1_name = $top1->post_title;
                $top1_rating = get_post_meta($top1->ID, '_saas_tool_rating', true);
                $top1_best = get_post_meta($top1->ID, '_saas_tool_best_for', true);
            ?>
            <p>
                The best alternative to <?php echo esc_html($tool_name); ?> in <?php echo $year; ?> is <strong><?php echo esc_html($top1_name); ?></strong> (rated <?php echo esc_html($top1_rating); ?>/10), best for <?php echo esc_html(strtolower($top1_best)); ?>.
                <?php if (isset($alternatives[1]) && isset($alternatives[2])) :
                    $top2 = $alternatives[1];
                    $top3 = $alternatives[2];
                ?>
                Other top alternatives include <strong><?php echo esc_html($top2->post_title); ?></strong> (<?php echo esc_html(get_post_meta($top2->ID, '_saas_tool_rating', true)); ?>/10) and <strong><?php echo esc_html($top3->post_title); ?></strong> (<?php echo esc_html(get_post_meta($top3->ID, '_saas_tool_rating', true)); ?>/10).
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </section>

        <!-- ================================================================
             ABOUT THE ORIGINAL TOOL (context for why people switch)
             ================================================================ -->
        <section class="card" style="padding:var(--space-6);margin-bottom:var(--space-8);border-left:4px solid var(--border-strong);">
            <h2 style="margin-top:0;font-size:var(--text-xl);">About <?php echo esc_html($tool_name); ?></h2>
            <p><?php echo esc_html($tool_desc); ?></p>
            <div style="display:flex;flex-wrap:wrap;gap:var(--space-4);margin-top:var(--space-3);font-size:var(--text-sm);color:var(--text-secondary);">
                <span><strong>Our rating:</strong> <?php echo esc_html($tool_rating); ?>/10</span>
                <span><strong>Best for:</strong> <?php echo esc_html($tool_best_for); ?></span>
                <span><strong>Price:</strong> <?php echo esc_html($tool_pricing); ?></span>
            </div>
            <a href="<?php echo esc_url(get_permalink($tool_post)); ?>" class="btn btn--outline" style="margin-top:var(--space-4);">Read Our Full <?php echo esc_html($tool_name); ?> Review</a>
        </section>

        <!-- ================================================================
             RANKED ALTERNATIVES TABLE
             Real HTML table for AI parsers and featured snippets.
             ================================================================ -->
        <section aria-label="Alternatives ranking">
            <h2>Top <?php echo esc_html($tool_name); ?> Alternatives — Ranked</h2>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Tool</th>
                            <th style="text-align:center;">Rating</th>
                            <th>Best For</th>
                            <th>Price From</th>
                            <th style="text-align:center;">Free Plan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alternatives as $i => $alt) :
                            $alt_id = $alt->ID;
                            $alt_rating  = get_post_meta($alt_id, '_saas_tool_rating', true);
                            $alt_best    = get_post_meta($alt_id, '_saas_tool_best_for', true);
                            $alt_pricing = get_post_meta($alt_id, '_saas_tool_pricing', true);
                            $alt_free    = get_post_meta($alt_id, '_saas_tool_free_plan', true);
                            $alt_url     = get_post_meta($alt_id, '_saas_affiliate_url', true);
                            $alt_rel     = get_post_meta($alt_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                            $rank = $i + 1;
                        ?>
                        <tr<?php echo $rank === 1 ? ' style="background:var(--brand-primary-light);"' : ''; ?>>
                            <td><strong><?php echo $rank; ?></strong></td>
                            <td>
                                <a href="<?php echo esc_url(get_permalink($alt)); ?>" style="font-weight:600;color:var(--text-primary);">
                                    <?php echo esc_html($alt->post_title); ?>
                                </a>
                                <?php if ($rank === 1) : ?>
                                    <span class="badge badge--freemium" style="margin-left:var(--space-2);">Top Pick</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;font-weight:700;"><?php echo esc_html($alt_rating); ?>/10</td>
                            <td class="text-sm"><?php echo esc_html($alt_best); ?></td>
                            <td><?php echo esc_html($alt_pricing); ?></td>
                            <td style="text-align:center;">
                                <?php if ($alt_free === 'yes') : ?>
                                    <span style="color:var(--color-success);">✓</span>
                                <?php elseif ($alt_free === 'limited') : ?>
                                    <span style="color:var(--color-warning);">~</span>
                                <?php else : ?>
                                    <span style="color:var(--text-muted);">✗</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($alt_url) : ?>
                                <a href="<?php echo esc_url($alt_url); ?>"
                                   class="btn btn--primary saas-cta"
                                   rel="<?php echo esc_attr($alt_rel); ?>"
                                   target="_blank"
                                   data-track="alt-table-cta"
                                   data-tool="<?php echo esc_attr($alt->post_title); ?>"
                                   style="white-space:nowrap;">
                                    Try It
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ================================================================
             INDIVIDUAL ALTERNATIVE CARDS (top 5 get expanded sections)
             ================================================================ -->
        <section style="margin-top:var(--space-12);">
            <h2>Detailed Look at the Top Alternatives</h2>

            <?php
            $detailed_alts = array_slice($alternatives, 0, 5);
            foreach ($detailed_alts as $i => $alt) :
                $alt_id      = $alt->ID;
                $alt_rating  = get_post_meta($alt_id, '_saas_tool_rating', true);
                $alt_best    = get_post_meta($alt_id, '_saas_tool_best_for', true);
                $alt_pricing = get_post_meta($alt_id, '_saas_tool_pricing', true);
                $alt_verdict = get_post_meta($alt_id, '_saas_tool_verdict', true);
                $alt_desc    = get_post_meta($alt_id, '_saas_tool_description', true);
                $alt_answer  = get_post_meta($alt_id, '_saas_quick_answer', true);
                $alt_url     = get_post_meta($alt_id, '_saas_affiliate_url', true);
                $alt_rel     = get_post_meta($alt_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                $rank = $i + 1;
            ?>
            <div class="card" style="margin-top:var(--space-6);padding:var(--space-6);">
                <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:var(--space-4);">
                    <div style="display:flex;align-items:center;gap:var(--space-3);">
                        <span style="font-size:var(--text-2xl);font-weight:700;color:var(--text-muted);min-width:32px;">#<?php echo $rank; ?></span>
                        <?php if (has_post_thumbnail($alt_id)) :
                            echo get_the_post_thumbnail($alt_id, 'saas-logo', array(
                                'width' => 40, 'height' => 40,
                                'style' => 'width:40px;height:40px;object-fit:contain;border-radius:8px;',
                            ));
                        endif; ?>
                        <div>
                            <h3 style="margin:0;font-size:var(--text-xl);"><?php echo esc_html($alt->post_title); ?></h3>
                            <p class="text-sm text-muted" style="margin:0;"><?php echo esc_html($alt_desc); ?></p>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:var(--text-2xl);font-weight:700;color:var(--brand-primary);"><?php echo esc_html($alt_rating); ?>/10</div>
                        <p class="text-sm text-muted" style="margin:0;">From <?php echo esc_html($alt_pricing); ?></p>
                    </div>
                </div>

                <?php if ($alt_answer) : ?>
                <p style="margin-top:var(--space-4);line-height:var(--leading-relaxed);">
                    <?php echo wp_kses_post($alt_answer); ?>
                </p>
                <?php endif; ?>

                <div style="margin-top:var(--space-4);padding-top:var(--space-4);border-top:1px solid var(--border-default);display:flex;flex-wrap:wrap;gap:var(--space-4);align-items:center;">
                    <span class="text-sm"><strong>Best for:</strong> <?php echo esc_html($alt_best); ?></span>
                    <?php if ($alt_verdict) : ?>
                    <span class="text-sm"><strong>Verdict:</strong> <?php echo esc_html($alt_verdict); ?></span>
                    <?php endif; ?>
                </div>

                <div style="margin-top:var(--space-4);display:flex;gap:var(--space-3);">
                    <?php if ($alt_url) : ?>
                    <a href="<?php echo esc_url($alt_url); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($alt_rel); ?>" target="_blank" data-track="alt-detail-cta" data-tool="<?php echo esc_attr($alt->post_title); ?>">Try <?php echo esc_html($alt->post_title); ?></a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(get_permalink($alt)); ?>" class="btn btn--outline">Read Full Review</a>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- ================================================================
             FAQ SECTION — targets "[Tool] alternatives" PAA queries
             ================================================================ -->
        <section class="faq-section" style="margin-top:var(--space-12);" aria-label="FAQ">
            <h2>Frequently Asked Questions</h2>

            <details>
                <summary>What is the best free alternative to <?php echo esc_html($tool_name); ?>?</summary>
                <div>
                    <p>
                        <?php
                        $free_alts = array_filter($alternatives, function($a) {
                            return get_post_meta($a->ID, '_saas_tool_free_plan', true) === 'yes';
                        });
                        if (!empty($free_alts)) {
                            $best_free = reset($free_alts);
                            printf(
                                'The best free alternative to %s is %s (rated %s/10). It offers a full free plan and is best for %s.',
                                esc_html($tool_name),
                                esc_html($best_free->post_title),
                                esc_html(get_post_meta($best_free->ID, '_saas_tool_rating', true)),
                                esc_html(strtolower(get_post_meta($best_free->ID, '_saas_tool_best_for', true)))
                            );
                        } else {
                            echo 'Currently, none of the top-rated alternatives offer a completely free plan, though several offer free trials or limited free tiers.';
                        }
                        ?>
                    </p>
                </div>
            </details>

            <details>
                <summary>What are the cheapest alternatives to <?php echo esc_html($tool_name); ?>?</summary>
                <div>
                    <p>The most affordable alternatives based on starting price are:</p>
                    <ul style="margin-top:var(--space-2);padding-left:var(--space-6);">
                        <?php
                        $sorted_by_price = array_slice($alternatives, 0, 3);
                        foreach ($sorted_by_price as $alt) :
                        ?>
                        <li><?php echo esc_html($alt->post_title); ?> — from <?php echo esc_html(get_post_meta($alt->ID, '_saas_tool_pricing', true)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </details>

            <details>
                <summary>Is <?php echo esc_html($tool_name); ?> still worth using in <?php echo $year; ?>?</summary>
                <div>
                    <p>
                        <?php echo esc_html($tool_name); ?> is rated <?php echo esc_html($tool_rating); ?>/10 in our review and is best for <?php echo esc_html(strtolower($tool_best_for)); ?>.
                        Whether it's worth using depends on your specific needs. If you're looking for alternatives, the tools listed above offer competitive features.
                        <a href="<?php echo esc_url(get_permalink($tool_post)); ?>">Read our full <?php echo esc_html($tool_name); ?> review</a> for a detailed breakdown.
                    </p>
                </div>
            </details>

            <details>
                <summary>How many alternatives to <?php echo esc_html($tool_name); ?> are there?</summary>
                <div>
                    <p>We've reviewed <?php echo esc_html($alt_count); ?> alternatives to <?php echo esc_html($tool_name); ?> in the <?php echo $category ? esc_html(strtolower($category->name)) : 'same'; ?> category. Our top pick is <?php echo esc_html($alternatives[0]->post_title); ?> with a rating of <?php echo esc_html(get_post_meta($alternatives[0]->ID, '_saas_tool_rating', true)); ?>/10.</p>
                </div>
            </details>
        </section>

        <!-- ================================================================
             INTERNAL LINKS
             ================================================================ -->
        <nav class="content-cluster" style="margin-top:var(--space-12);" aria-label="Related content">
            <div class="content-cluster__title">Explore More</div>
            <ul class="content-cluster__links">
                <li><a class="content-cluster__link" href="<?php echo esc_url(get_permalink($tool_post)); ?>">Full <?php echo esc_html($tool_name); ?> Review</a></li>
                <?php if ($category) : ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/best/' . $category->slug . '-software/')); ?>">Best <?php echo esc_html($category->name); ?> Software</a></li>
                <?php endif; ?>
                <?php if (!empty($alternatives)) : ?>
                <li><a class="content-cluster__link" href="<?php echo esc_url(home_url('/compare/' . $tool_slug . '-vs-' . get_post_field('post_name', $alternatives[0]) . '/')); ?>"><?php echo esc_html($tool_name); ?> vs <?php echo esc_html($alternatives[0]->post_title); ?></a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div><!-- .container -->
</article>

<?php get_footer(); ?>
