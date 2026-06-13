<?php
/**
 * Template Part: News Reaction / Price Change Alert
 *
 * "[Tool] just raised prices — here's what changed"
 * "[Tool] launched [feature] — does it matter?"
 *
 * Reactive content capturing spike traffic and "[Tool] pricing 2026" evergreen queries.
 *
 * Layout:
 * 1. "What happened" summary block (AI-quotable)
 * 2. What changed (before vs. after, uses [before_after] shortcodes)
 * 3. Who this affects
 * 4. What to do now (alternative recommendations + affiliate links)
 * 5. FAQ section
 *
 * Custom meta: news date, affected tool, change type
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();

// Format-specific meta
$news_date  = get_post_meta($post_id, '_blog_news_date', true);
$news_tool  = get_post_meta($post_id, '_blog_news_tool', true);
$news_type  = get_post_meta($post_id, '_blog_news_change_type', true);
$linked_ids = get_post_meta($post_id, '_blog_linked_review_ids', true);
$linked_id  = !empty($linked_ids) ? absint(explode(',', $linked_ids)[0]) : 0;

// Change type metadata
$type_config = array(
    'pricing' => array(
        'label' => 'Pricing Change',
        'icon'  => '💰',
        'color' => 'var(--color-warning)',
        'badge' => 'badge--paid',
    ),
    'feature' => array(
        'label' => 'New Feature',
        'icon'  => '🚀',
        'color' => 'var(--color-info)',
        'badge' => 'badge--b2b',
    ),
    'acquisition' => array(
        'label' => 'Acquisition',
        'icon'  => '🏢',
        'color' => 'var(--text-secondary)',
        'badge' => 'badge--b2c',
    ),
    'shutdown' => array(
        'label' => 'Shutdown / EOL',
        'icon'  => '⚠️',
        'color' => 'var(--color-error)',
        'badge' => 'badge--paid',
    ),
    'other' => array(
        'label' => 'News',
        'icon'  => '📰',
        'color' => 'var(--text-secondary)',
        'badge' => 'badge--b2b',
    ),
);

$config = $type_config[$news_type] ?? $type_config['other'];

// Get tool info from linked review
$tool_rating = $linked_id ? get_post_meta($linked_id, '_saas_tool_rating', true) : '';
$tool_url    = $linked_id ? get_post_meta($linked_id, '_saas_affiliate_url', true) : '';
$tool_rel    = $linked_id ? (get_post_meta($linked_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored') : '';
?>

<article <?php post_class('blog-format--news-reaction'); ?>>
    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">
        <div class="layout-with-sidebar">
            <div class="blog-main">

                <!-- Header -->
                <header class="blog-header" style="margin-bottom:var(--space-6);">
                    <!-- News type badge -->
                    <span class="badge <?php echo esc_attr($config['badge']); ?>" style="margin-bottom:var(--space-3);display:inline-flex;gap:var(--space-2);align-items:center;font-size:var(--text-sm);">
                        <span><?php echo $config['icon']; ?></span>
                        <span><?php echo esc_html($config['label']); ?></span>
                    </span>

                    <h1><?php the_title(); ?></h1>

                    <div class="blog-header__meta text-sm text-muted">
                        <?php if ($news_date) : ?>
                            <time datetime="<?php echo esc_attr($news_date); ?>">
                                <?php echo esc_html(date_i18n('F j, Y', strtotime($news_date))); ?>
                            </time>
                            <span>&middot;</span>
                        <?php endif; ?>
                        <span>Updated <?php echo get_the_modified_date('F j, Y'); ?></span>
                        <?php if ($news_tool) : ?>
                            <span>&middot;</span>
                            <span>Affects: <strong><?php echo esc_html($news_tool); ?></strong></span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- 1. "WHAT HAPPENED" BLOCK (AI-quotable) -->
                <section class="quick-answer" style="border-left-color:<?php echo esc_attr($config['color']); ?>;" aria-label="Summary">
                    <p>
                        <strong><?php echo $config['icon']; ?> <?php echo esc_html($config['label']); ?>:</strong>
                        <?php echo wp_kses_post(get_the_excerpt()); ?>
                    </p>
                </section>

                <!-- Timeline context -->
                <?php if ($news_date) : ?>
                <div style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3) var(--space-4);background:var(--bg-surface);border-radius:var(--border-radius);margin-bottom:var(--space-6);font-size:var(--text-sm);">
                    <span style="font-weight:600;">📅 Announced:</span>
                    <span><?php echo esc_html(date_i18n('F j, Y', strtotime($news_date))); ?></span>
                    <span style="color:var(--text-muted);">(<?php echo esc_html(human_time_diff(strtotime($news_date), current_time('timestamp'))); ?> ago)</span>
                </div>
                <?php endif; ?>

                <!-- 2–4. MAIN CONTENT
                     Editor content should use:
                     - [before_after] shortcodes for price change tables
                     - H2: What Changed (with detail)
                     - H2: Who This Affects
                     - H2: What to Do Now (with [related_review] shortcodes for alternatives)
                -->
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <!-- Tool CTA section -->
                <?php if ($linked_id && $tool_url) : ?>
                <section style="margin-top:var(--space-8);padding:var(--space-6);background:var(--bg-surface);border-radius:var(--border-radius-lg);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-4);" aria-label="Tool action">
                    <div>
                        <h3 style="margin:0 0 var(--space-2);font-size:var(--text-lg);">
                            <?php if ($news_type === 'shutdown') : ?>
                                Need an alternative to <?php echo esc_html($news_tool); ?>?
                            <?php else : ?>
                                Still want to use <?php echo esc_html($news_tool); ?>?
                            <?php endif; ?>
                        </h3>
                        <p class="text-sm text-muted" style="margin:0;">
                            <?php if ($tool_rating) : ?>
                                Rated <?php echo esc_html($tool_rating); ?>/10 in our review
                            <?php endif; ?>
                        </p>
                    </div>
                    <div style="display:flex;gap:var(--space-3);">
                        <?php if ($news_type !== 'shutdown') : ?>
                        <a href="<?php echo esc_url($tool_url); ?>"
                           class="btn btn--primary saas-cta"
                           rel="<?php echo esc_attr($tool_rel); ?>"
                           target="_blank"
                           data-track="news-cta"
                           data-tool="<?php echo esc_attr($news_tool); ?>">
                            Try <?php echo esc_html($news_tool); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(get_permalink($linked_id)); ?>" class="btn btn--outline">Full Review</a>
                        <a href="<?php echo esc_url(home_url('/alternatives/' . get_post_field('post_name', $linked_id) . '/')); ?>" class="btn btn--outline">See Alternatives</a>
                    </div>
                </section>
                <?php endif; ?>

                <!-- 5. FAQ SECTION -->
                <section class="faq-section" style="margin-top:var(--space-12);" aria-label="FAQ">
                    <h2>Frequently Asked Questions</h2>

                    <?php if ($news_type === 'pricing') : ?>
                    <details>
                        <summary>When does the <?php echo esc_html($news_tool); ?> price change take effect?</summary>
                        <div>
                            <p>The pricing change was announced on <?php echo esc_html(date_i18n('F j, Y', strtotime($news_date))); ?>. Check the article above for specific effective dates and whether existing customers are grandfathered.</p>
                        </div>
                    </details>
                    <details>
                        <summary>Are existing <?php echo esc_html($news_tool); ?> customers affected?</summary>
                        <div>
                            <p>This varies by provider. See the "Who This Affects" section above for details on existing vs. new customer treatment.</p>
                        </div>
                    </details>
                    <?php endif; ?>

                    <?php if ($news_type === 'feature') : ?>
                    <details>
                        <summary>Is the new <?php echo esc_html($news_tool); ?> feature available on all plans?</summary>
                        <div>
                            <p>Check the "What Changed" section above for plan-specific availability. New features are sometimes limited to higher-tier plans initially.</p>
                        </div>
                    </details>
                    <?php endif; ?>

                    <details>
                        <summary>What are the best alternatives to <?php echo esc_html($news_tool); ?>?</summary>
                        <div>
                            <p>
                                <?php if ($linked_id) : ?>
                                    See our <a href="<?php echo esc_url(home_url('/alternatives/' . get_post_field('post_name', $linked_id) . '/')); ?>">full alternatives page</a> for a ranked list.
                                <?php else : ?>
                                    Check the "What to Do Now" section above for our recommended alternatives.
                                <?php endif; ?>
                            </p>
                        </div>
                    </details>

                    <details>
                        <summary>Is <?php echo esc_html($news_tool); ?> still worth using after this change?</summary>
                        <div>
                            <p>
                                <?php if ($linked_id && $tool_rating) : ?>
                                    We currently rate <?php echo esc_html($news_tool); ?> at <?php echo esc_html($tool_rating); ?>/10.
                                    <a href="<?php echo esc_url(get_permalink($linked_id)); ?>">Read our full review</a> for a detailed breakdown of whether it's still the right choice.
                                <?php else : ?>
                                    It depends on how this change affects your specific use case. See the analysis above.
                                <?php endif; ?>
                            </p>
                        </div>
                    </details>
                </section>

                <!-- Comments -->
                <?php if (comments_open() || get_comments_number()) : ?>
                <section style="margin-top:var(--space-12);">
                    <?php comments_template(); ?>
                </section>
                <?php endif; ?>

            </div><!-- .blog-main -->

            <?php get_sidebar(); ?>
        </div>
    </div>
</article>
