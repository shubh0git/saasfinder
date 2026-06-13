<?php
/**
 * Template Part: User Reviews Roundup
 *
 * "What Real Users Say About [Tool]"
 * Curates and analyzes customer reviews from G2, Capterra, Trustpilot, Reddit.
 * Targets "[Tool] reviews" and "[Tool] Reddit" search queries.
 *
 * Layout:
 * 1. Direct answer block (AI-quotable)
 * 2. Overall sentiment summary with visual indicator
 * 3. "What users love" section (themed highlights)
 * 4. "Common complaints" section
 * 5. Platform-by-platform breakdown (HTML table)
 * 6. "Who should (and shouldn't) use [Tool]" verdict
 * 7. Affiliate CTA
 * 8. FAQ section targeting review-intent queries
 *
 * Schema: Review + AggregateRating
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();

// Format-specific meta
$tool_name      = get_post_meta($post_id, '_blog_review_tool_name', true);
$sources        = get_post_meta($post_id, '_blog_review_sources', true);
$sentiment      = get_post_meta($post_id, '_blog_review_sentiment', true);
$linked_ids     = get_post_meta($post_id, '_blog_linked_review_ids', true);
$linked_id      = !empty($linked_ids) ? absint(explode(',', $linked_ids)[0]) : 0;

// Get data from linked review if available
$review_rating  = $linked_id ? get_post_meta($linked_id, '_saas_tool_rating', true) : '';
$review_url     = $linked_id ? get_post_meta($linked_id, '_saas_affiliate_url', true) : '';
$review_rel     = $linked_id ? (get_post_meta($linked_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored') : '';
$review_verdict = $linked_id ? get_post_meta($linked_id, '_saas_tool_verdict', true) : '';

// Source platforms array
$source_list = $sources ? array_map('trim', explode(',', $sources)) : array();

// Sentiment metadata
$sentiment_labels = array(
    'positive' => 'Overall Positive',
    'mixed'    => 'Mixed Reviews',
    'negative' => 'Overall Negative',
);
$sentiment_icons = array(
    'positive' => '👍',
    'mixed'    => '🤔',
    'negative' => '👎',
);
$sentiment_descriptions = array(
    'positive' => 'The majority of users across platforms report positive experiences.',
    'mixed'    => 'User opinions are split — some love it, others have significant complaints.',
    'negative' => 'More users report frustrations than positive experiences.',
);
?>

<article <?php post_class('blog-format--user-reviews'); ?> itemscope itemtype="https://schema.org/Review">
    <meta itemprop="datePublished" content="<?php echo get_the_date('c'); ?>">
    <meta itemprop="dateModified" content="<?php echo get_the_modified_date('c'); ?>">
    <span itemprop="author" itemscope itemtype="https://schema.org/Organization" style="display:none;">
        <meta itemprop="name" content="SaasFinder">
    </span>
    <?php if ($tool_name) : ?>
    <span itemprop="itemReviewed" itemscope itemtype="https://schema.org/SoftwareApplication" style="display:none;">
        <meta itemprop="name" content="<?php echo esc_attr($tool_name); ?>">
        <meta itemprop="applicationCategory" content="BusinessApplication">
    </span>
    <?php endif; ?>

    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">
        <div class="layout-with-sidebar">
            <div class="blog-main">

                <!-- Header -->
                <header class="blog-header" style="margin-bottom:var(--space-6);">
                    <h1 itemprop="name"><?php the_title(); ?></h1>
                    <div class="blog-header__meta text-sm text-muted">
                        <span>By <?php the_author(); ?></span>
                        <span>&middot;</span>
                        <time datetime="<?php echo get_the_modified_date('c'); ?>">Updated <?php echo get_the_modified_date('F j, Y'); ?></time>
                        <?php if ($sources) : ?>
                            <span>&middot;</span>
                            <span>Sources: <?php echo esc_html($sources); ?></span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- 1. DIRECT ANSWER BLOCK (AI-quotable) -->
                <section class="quick-answer" aria-label="Quick summary" itemprop="description">
                    <p>
                        Is <?php echo esc_html($tool_name); ?> worth it?
                        Based on user reviews across <?php echo esc_html($sources ?: 'multiple platforms'); ?>, the consensus is <strong><?php echo esc_html($sentiment_labels[$sentiment] ?? 'mixed'); ?></strong>.
                        <?php echo wp_kses_post(get_the_excerpt()); ?>
                    </p>
                </section>

                <!-- 2. SENTIMENT SUMMARY -->
                <?php if ($sentiment) : ?>
                <section class="sentiment-summary card" style="margin-bottom:var(--space-8);padding:var(--space-6);" aria-label="Sentiment overview">
                    <div style="display:flex;align-items:center;gap:var(--space-4);flex-wrap:wrap;">
                        <span style="font-size:2.5rem;"><?php echo $sentiment_icons[$sentiment] ?? '🤔'; ?></span>
                        <div>
                            <div class="sentiment sentiment--<?php echo esc_attr($sentiment); ?>" style="margin-bottom:var(--space-2);">
                                <?php echo esc_html($sentiment_labels[$sentiment] ?? 'Mixed'); ?>
                            </div>
                            <p class="text-sm text-muted" style="margin:0;">
                                <?php echo esc_html($sentiment_descriptions[$sentiment] ?? ''); ?>
                            </p>
                        </div>
                        <?php if ($review_rating) : ?>
                        <div style="margin-left:auto;text-align:center;">
                            <div style="font-size:var(--text-sm);color:var(--text-muted);">Our Rating</div>
                            <div style="font-size:var(--text-2xl);font-weight:700;color:var(--brand-primary);"><?php echo esc_html($review_rating); ?>/10</div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($source_list)) : ?>
                    <div style="margin-top:var(--space-4);padding-top:var(--space-4);border-top:1px solid var(--border-default);">
                        <span class="text-sm" style="font-weight:600;">Reviews analyzed from:</span>
                        <div style="margin-top:var(--space-2);display:flex;flex-wrap:wrap;gap:var(--space-2);">
                            <?php foreach ($source_list as $src) : ?>
                            <span class="review-source">
                                <span class="review-source__name"><?php echo esc_html($src); ?></span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- 3–6. MAIN CONTENT
                     The editor content should follow this structure:
                     - H2: What Users Love About [Tool] (with H3 sub-themes)
                     - H2: Common Complaints (with H3 sub-themes)
                     - Platform breakdown table using [review_source] shortcodes
                     - H2: Who Should (and Shouldn't) Use [Tool]
                -->
                <div class="entry-content" itemprop="reviewBody">
                    <?php the_content(); ?>
                </div>

                <!-- 7. AFFILIATE CTA -->
                <?php if ($linked_id && $review_url) : ?>
                <section class="review-cta-section" style="margin-top:var(--space-8);" aria-label="Try <?php echo esc_attr($tool_name); ?>">
                    <div class="review-cta-section__inner" style="background:var(--bg-surface);border-radius:var(--border-radius-lg);padding:var(--space-6);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:var(--space-4);">
                        <div>
                            <h2 style="margin:0 0 var(--space-2);font-size:var(--text-xl);">Want to try <?php echo esc_html($tool_name); ?> yourself?</h2>
                            <?php if ($review_verdict) : ?>
                            <p class="text-muted" style="margin:0;"><?php echo esc_html($review_verdict); ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
                            <a href="<?php echo esc_url($review_url); ?>"
                               class="btn btn--primary btn--lg saas-cta"
                               rel="<?php echo esc_attr($review_rel); ?>"
                               target="_blank"
                               data-track="roundup-cta"
                               data-tool="<?php echo esc_attr($tool_name); ?>">
                                Try <?php echo esc_html($tool_name); ?> Free
                            </a>
                            <a href="<?php echo esc_url(get_permalink($linked_id)); ?>" class="btn btn--outline btn--lg">
                                Read Our Full Review
                            </a>
                        </div>
                    </div>
                </section>
                <?php elseif ($linked_id) : ?>
                <div style="margin-top:var(--space-8);">
                    <?php echo do_shortcode('[related_review id="' . $linked_id . '"]'); ?>
                </div>
                <?php endif; ?>

                <!-- 8. FAQ SECTION — targets review-intent queries -->
                <section class="faq-section" style="margin-top:var(--space-12);" aria-label="FAQ">
                    <h2>Frequently Asked Questions</h2>

                    <details>
                        <summary>Is <?php echo esc_html($tool_name); ?> worth it?</summary>
                        <div>
                            <p>
                                Based on our analysis of user reviews across <?php echo esc_html($sources ?: 'multiple platforms'); ?>,
                                <?php echo esc_html($tool_name); ?> receives <?php echo esc_html(strtolower($sentiment_labels[$sentiment] ?? 'mixed')); ?> feedback overall.
                                <?php if ($review_rating) : ?>
                                    We rated it <?php echo esc_html($review_rating); ?>/10 in our hands-on review.
                                <?php endif; ?>
                                <?php if ($linked_id) : ?>
                                    <a href="<?php echo esc_url(get_permalink($linked_id)); ?>">Read our full review for a detailed breakdown.</a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </details>

                    <details>
                        <summary>What do users like most about <?php echo esc_html($tool_name); ?>?</summary>
                        <div>
                            <p>The most commonly praised aspects across review platforms include ease of use, feature set, and value for money. Scroll up to the "What Users Love" section for detailed, themed highlights from real users.</p>
                        </div>
                    </details>

                    <details>
                        <summary>What are the main complaints about <?php echo esc_html($tool_name); ?>?</summary>
                        <div>
                            <p>Common complaints from users include pricing concerns, learning curve, and occasional performance issues. See the "Common Complaints" section above for a detailed breakdown.</p>
                        </div>
                    </details>

                    <details>
                        <summary>Is <?php echo esc_html($tool_name); ?> good for small teams?</summary>
                        <div>
                            <p>This depends on your specific needs and budget. Check the "Who Should (and Shouldn't) Use <?php echo esc_html($tool_name); ?>" section above for audience-specific guidance.</p>
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
        </div><!-- .layout-with-sidebar -->
    </div><!-- .container -->
</article>
