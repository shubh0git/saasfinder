<?php
/**
 * Template Part: Tutorial / How-To
 *
 * "How to [do X] with [Tool]" — step-by-step guides with HowTo schema.
 * Targets "[Tool] tutorial" and "how to [task]" queries.
 * Builds topical authority and naturally embeds affiliate links.
 *
 * Layout:
 * 1. "What you'll learn" summary block (AI-quotable)
 * 2. Prerequisites / what you need
 * 3. Numbered step-by-step sections (via [step] shortcodes)
 * 4. Screenshots/GIFs between steps
 * 5. "Pro tips" callout boxes
 * 6. "What to do next" section with related tutorials and full review link
 *
 * Schema: HowTo with step markup
 *
 * @package SaasFinder
 */

$post_id = get_the_ID();

// Common meta
$linked_ids = get_post_meta($post_id, '_blog_linked_review_ids', true);
$linked_id = !empty($linked_ids) ? absint(explode(',', $linked_ids)[0]) : 0;

// Get tool info from linked review
$tool_name = '';
$tool_url = '';
$tool_rel = 'nofollow sponsored';
if ($linked_id) {
    $tool_name = get_the_title($linked_id);
    $tool_url = get_post_meta($linked_id, '_saas_affiliate_url', true);
    $tool_rel = get_post_meta($linked_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
}

// Estimate read time (rough: 200 words/min)
$word_count = str_word_count(strip_tags(get_the_content()));
$read_time = max(1, ceil($word_count / 200));
?>

<article <?php post_class('blog-format--tutorial'); ?> itemscope itemtype="https://schema.org/HowTo">
    <meta itemprop="datePublished" content="<?php echo get_the_date('c'); ?>">
    <meta itemprop="dateModified" content="<?php echo get_the_modified_date('c'); ?>">
    <?php if (has_post_thumbnail()) : ?>
        <meta itemprop="image" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>">
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
                        <span>&middot;</span>
                        <span><?php echo $read_time; ?> min read</span>
                        <?php if ($tool_name) : ?>
                            <span>&middot;</span>
                            <span>Tool: <a href="<?php echo esc_url(get_permalink($linked_id)); ?>"><?php echo esc_html($tool_name); ?></a></span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- 1. "WHAT YOU'LL LEARN" BLOCK (AI-quotable) -->
                <section class="quick-answer" aria-label="What you'll learn" itemprop="description">
                    <p><strong>What you'll learn:</strong> <?php echo wp_kses_post(get_the_excerpt()); ?></p>
                </section>

                <!-- Quick tool CTA (for tutorials about a specific tool) -->
                <?php if ($tool_name && $tool_url) : ?>
                <div style="display:flex;align-items:center;gap:var(--space-4);padding:var(--space-4);background:var(--bg-surface);border-radius:var(--border-radius);margin-bottom:var(--space-8);flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;">
                        <span class="text-sm text-muted">This tutorial uses:</span>
                        <strong style="display:block;"><?php echo esc_html($tool_name); ?></strong>
                    </div>
                    <a href="<?php echo esc_url($tool_url); ?>"
                       class="btn btn--primary saas-cta"
                       rel="<?php echo esc_attr($tool_rel); ?>"
                       target="_blank"
                       data-track="tutorial-top-cta"
                       data-tool="<?php echo esc_attr($tool_name); ?>">
                        Get <?php echo esc_html($tool_name); ?>
                    </a>
                </div>
                <?php endif; ?>

                <!-- 2–5. MAIN CONTENT
                     The editor content should use [step] shortcodes:
                     [step number="1" title="Create your account"]
                     Content with screenshots...
                     [/step]

                     [step number="2" title="Configure settings"]
                     More content...
                     [/step]

                     Pro tips can use blockquotes or a custom CSS class:
                     <div class="pro-tip">💡 <strong>Pro Tip:</strong> ...</div>
                -->
                <div class="entry-content tutorial-content" itemprop="step" itemscope itemtype="https://schema.org/HowToSection">
                    <?php the_content(); ?>
                </div>

                <!-- 6. "WHAT TO DO NEXT" SECTION -->
                <section style="margin-top:var(--space-12);padding:var(--space-6);background:var(--bg-surface);border-radius:var(--border-radius-lg);" aria-label="Next steps">
                    <h2 style="margin-top:0;">What to Do Next</h2>

                    <?php if ($linked_id) : ?>
                    <div style="margin-bottom:var(--space-4);">
                        <?php echo do_shortcode('[related_review id="' . $linked_id . '"]'); ?>
                    </div>
                    <?php endif; ?>

                    <?php
                    // Find related tutorials in the same category
                    $categories = wp_get_post_terms($post_id, 'saas-category');
                    if (!empty($categories)) :
                        $related_tutorials = get_posts(array(
                            'post_type'    => 'post',
                            'numberposts'  => 3,
                            'post__not_in' => array($post_id),
                            'tax_query'    => array(
                                'relation' => 'AND',
                                array(
                                    'taxonomy' => 'blog-format',
                                    'field'    => 'slug',
                                    'terms'    => 'tutorial',
                                ),
                                array(
                                    'taxonomy' => 'saas-category',
                                    'field'    => 'term_id',
                                    'terms'    => wp_list_pluck($categories, 'term_id'),
                                ),
                            ),
                        ));

                        if (!empty($related_tutorials)) :
                    ?>
                    <h3 style="font-size:var(--text-lg);margin-top:var(--space-6);">Related Tutorials</h3>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:var(--space-3);">
                        <?php foreach ($related_tutorials as $rt) : ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink($rt)); ?>" style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3);background:var(--bg-card);border:1px solid var(--border-default);border-radius:var(--border-radius);text-decoration:none;color:var(--text-primary);transition:border-color var(--transition-fast);">
                                <span style="font-size:var(--text-xl);">📖</span>
                                <span style="font-weight:500;"><?php echo esc_html($rt->post_title); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; endif; ?>

                    <!-- Final CTA -->
                    <?php if ($tool_url) : ?>
                    <div style="margin-top:var(--space-6);padding-top:var(--space-4);border-top:1px solid var(--border-default);text-align:center;">
                        <p class="text-muted">Ready to get started?</p>
                        <a href="<?php echo esc_url($tool_url); ?>"
                           class="btn btn--primary btn--lg saas-cta"
                           rel="<?php echo esc_attr($tool_rel); ?>"
                           target="_blank"
                           data-track="tutorial-bottom-cta"
                           data-tool="<?php echo esc_attr($tool_name); ?>">
                            Try <?php echo esc_html($tool_name); ?> Free
                        </a>
                    </div>
                    <?php endif; ?>
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
