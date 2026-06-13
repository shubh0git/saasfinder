<?php
/**
 * Template Part: Single Query Answer
 *
 * One blog post per specific question that people ask on ChatGPT, Perplexity,
 * Reddit, Quora, or Google's "People Also Ask."
 *
 * Examples:
 * - "Can Notion replace Jira?"
 * - "Is Calendly free for teams?"
 * - "What's the cheapest CRM with email automation?"
 *
 * Layout:
 * 1. Direct answer block (definitive, 1-2 sentences, AI-quotable)
 * 2. Context section (why this question matters)
 * 3. Detailed answer with evidence
 * 4. "Related questions" FAQ blocks with FAQPage schema
 * 5. Affiliate CTA if a tool is recommended
 *
 * Schema: FAQPage wrapping the entire post
 * URL pattern: /answers/{slug}/
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();

// Format-specific meta
$query_text   = get_post_meta($post_id, '_blog_query_text', true);
$query_source = get_post_meta($post_id, '_blog_query_source', true);
$linked_ids   = get_post_meta($post_id, '_blog_linked_review_ids', true);

// Source labels
$source_labels = array(
    'chatgpt'    => 'ChatGPT',
    'perplexity' => 'Perplexity',
    'reddit'     => 'Reddit',
    'quora'      => 'Quora',
    'paa'        => 'Google People Also Ask',
    'other'      => 'Search',
);
$source_label = $source_labels[$query_source] ?? '';

// The actual question to display (use meta field or fall back to title)
$display_question = $query_text ?: get_the_title();
?>

<article <?php post_class('blog-format--query-answer'); ?> itemscope itemtype="https://schema.org/FAQPage">
    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">
        <div class="layout-with-sidebar">
            <div class="blog-main">

                <!-- Header -->
                <header class="blog-header" style="margin-bottom:var(--space-6);">
                    <h1><?php echo esc_html($display_question); ?></h1>
                    <div class="blog-header__meta text-sm text-muted">
                        <span>Answered by <?php the_author(); ?></span>
                        <span>&middot;</span>
                        <time datetime="<?php echo get_the_modified_date('c'); ?>">Updated <?php echo get_the_modified_date('F j, Y'); ?></time>
                        <?php if ($source_label) : ?>
                            <span>&middot;</span>
                            <span>Originally asked on: <?php echo esc_html($source_label); ?></span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- 1. DIRECT ANSWER BLOCK
                     THE most important element. This is what AI engines cite verbatim.
                     Must be definitive — no hedging, no "it depends" unless genuinely conditional.
                     Semantically marked for both microdata and CSS targeting.
                -->
                <section class="direct-answer" aria-label="Direct answer" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">
                    <meta itemprop="name" content="<?php echo esc_attr($display_question); ?>">
                    <div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                        <div itemprop="text">
                            <?php
                            // The excerpt IS the direct answer for this format
                            $excerpt = get_the_excerpt();
                            if ($excerpt) {
                                echo '<p>' . wp_kses_post($excerpt) . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </section>

                <!-- 2–3. MAIN CONTENT
                     Editor content should include:
                     - Context section (why this question matters, who's asking)
                     - Detailed answer with evidence, pricing, screenshots, comparisons
                     - Use [related_review id="X"] shortcodes inline where tools are mentioned
                -->
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <!-- 5. AFFILIATE CTAs (if tools are referenced) -->
                <?php if (!empty($linked_ids)) :
                    $ids = array_filter(array_map('absint', explode(',', $linked_ids)));
                    if (!empty($ids)) :
                ?>
                <section style="margin-top:var(--space-8);padding:var(--space-6);background:var(--bg-surface);border-radius:var(--border-radius-lg);" aria-label="Recommended tools">
                    <h2 style="margin-top:0;font-size:var(--text-xl);">Tools Mentioned in This Answer</h2>
                    <div style="display:flex;flex-direction:column;gap:var(--space-4);">
                        <?php foreach ($ids as $rid) :
                            if (get_post_status($rid) !== 'publish') continue;
                            $r_name   = get_the_title($rid);
                            $r_rating = get_post_meta($rid, '_saas_tool_rating', true);
                            $r_verdict = get_post_meta($rid, '_saas_tool_verdict', true);
                            $r_url    = get_post_meta($rid, '_saas_affiliate_url', true);
                            $r_rel    = get_post_meta($rid, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                        ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-3);padding:var(--space-4);background:var(--bg-card);border:1px solid var(--border-default);border-radius:var(--border-radius);">
                            <div>
                                <a href="<?php echo esc_url(get_permalink($rid)); ?>" style="font-weight:600;color:var(--text-primary);text-decoration:none;">
                                    <?php echo esc_html($r_name); ?>
                                </a>
                                <?php if ($r_rating) : ?>
                                    <span class="badge badge--b2b" style="margin-left:var(--space-2);"><?php echo esc_html($r_rating); ?>/10</span>
                                <?php endif; ?>
                                <?php if ($r_verdict) : ?>
                                    <p class="text-sm text-muted" style="margin:var(--space-1) 0 0;"><?php echo esc_html($r_verdict); ?></p>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex;gap:var(--space-2);">
                                <?php if ($r_url) : ?>
                                <a href="<?php echo esc_url($r_url); ?>" class="btn btn--primary saas-cta" rel="<?php echo esc_attr($r_rel); ?>" target="_blank" data-track="answer-cta" data-tool="<?php echo esc_attr($r_name); ?>">Try It</a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(get_permalink($rid)); ?>" class="btn btn--outline">Review</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; endif; ?>

                <!-- 4. RELATED QUESTIONS (auto-generated, also via internal-links.php filter)
                     These are additional FAQ entries that can each capture PAA slots.
                -->
                <?php
                // Find related single-query-answer posts in same category
                $categories = wp_get_post_terms($post_id, 'saas-category');
                if (!empty($categories)) :
                    $related_questions = get_posts(array(
                        'post_type'    => 'post',
                        'numberposts'  => 5,
                        'post__not_in' => array($post_id),
                        'tax_query'    => array(
                            'relation' => 'AND',
                            array(
                                'taxonomy' => 'blog-format',
                                'field'    => 'slug',
                                'terms'    => 'single-query-answer',
                            ),
                            array(
                                'taxonomy' => 'saas-category',
                                'field'    => 'term_id',
                                'terms'    => wp_list_pluck($categories, 'term_id'),
                            ),
                        ),
                    ));

                    if (!empty($related_questions)) :
                ?>
                <section class="faq-section" style="margin-top:var(--space-12);" aria-label="Related questions">
                    <h2>Related Questions</h2>
                    <?php foreach ($related_questions as $rq) :
                        $rq_query = get_post_meta($rq->ID, '_blog_query_text', true) ?: $rq->post_title;
                        $rq_excerpt = get_the_excerpt($rq);
                    ?>
                    <details itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">
                        <summary itemprop="name"><?php echo esc_html($rq_query); ?></summary>
                        <div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                            <div itemprop="text">
                                <p><?php echo esc_html($rq_excerpt); ?></p>
                                <a href="<?php echo esc_url(get_permalink($rq)); ?>">Read the full answer →</a>
                            </div>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </section>
                <?php endif; endif; ?>

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
