<?php
/**
 * Shortcodes — Content utility blocks.
 *
 * For Reviews & General Use:
 *   [saas_cta id="123"]
 *   [saas_comparison ids="12,34,56"]
 *   [pricing_table id="123"]
 *   [verdict_box]
 *   [pros_cons]
 *   [tldr]...[/tldr]
 *   [last_updated]
 *
 * For Blog Content Formats:
 *   [direct_answer]...[/direct_answer]
 *   [user_sentiment positive|mixed|negative]
 *   [review_source name="G2" rating="4.5" count="1200" url="..."]
 *   [step number="1" title="..."]...[/step]
 *   [before_after before="$15/mo" after="$25/mo" label="Starter Plan"]
 *   [related_review id="123"]
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

// =============================================================================
// [saas_cta id="123"] — Styled CTA button from review meta
// =============================================================================
function saasfinder_shortcode_cta($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'saas_cta');
    $post_id = absint($atts['id']) ?: get_the_ID();

    $url = get_post_meta($post_id, '_saas_affiliate_url', true);
    $text = get_post_meta($post_id, '_saas_cta_text', true) ?: 'Try It Free';
    $rel = get_post_meta($post_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
    $tool_name = get_the_title($post_id);

    if (!$url) return '';

    return sprintf(
        '<a href="%s" class="btn btn--primary btn--lg saas-cta" rel="%s" target="_blank" data-track="cta-click" data-tool="%s">%s</a>',
        esc_url($url),
        esc_attr($rel),
        esc_attr($tool_name),
        esc_html($text)
    );
}
add_shortcode('saas_cta', 'saasfinder_shortcode_cta');

// =============================================================================
// [saas_comparison ids="12,34,56"] — Comparison table
// =============================================================================
function saasfinder_shortcode_comparison($atts) {
    $atts = shortcode_atts(array('ids' => '', 'highlight' => ''), $atts, 'saas_comparison');

    if (empty($atts['ids'])) return '';

    $ids = array_map('absint', explode(',', $atts['ids']));
    $highlight_id = absint($atts['highlight']);

    ob_start();
    ?>
    <div class="comparison-table">
        <table>
            <thead>
                <tr>
                    <th>Feature</th>
                    <?php foreach ($ids as $id) :
                        $class = ($id === $highlight_id) ? ' class="editors-pick"' : '';
                    ?>
                        <th<?php echo $class; ?>><?php echo esc_html(get_the_title($id)); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Rating</td>
                    <?php foreach ($ids as $id) : ?>
                        <td><strong><?php echo esc_html(get_post_meta($id, '_saas_tool_rating', true)); ?>/10</strong></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Best For</td>
                    <?php foreach ($ids as $id) : ?>
                        <td><?php echo esc_html(get_post_meta($id, '_saas_tool_best_for', true)); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Pricing From</td>
                    <?php foreach ($ids as $id) : ?>
                        <td><?php echo esc_html(get_post_meta($id, '_saas_tool_pricing', true)); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Free Plan</td>
                    <?php foreach ($ids as $id) : ?>
                        <td><?php echo esc_html(ucfirst(get_post_meta($id, '_saas_tool_free_plan', true))); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Verdict</td>
                    <?php foreach ($ids as $id) : ?>
                        <td><?php echo esc_html(get_post_meta($id, '_saas_tool_verdict', true)); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td></td>
                    <?php foreach ($ids as $id) :
                        $url = get_post_meta($id, '_saas_affiliate_url', true);
                        $rel = get_post_meta($id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
                    ?>
                        <td>
                            <?php if ($url) : ?>
                                <a href="<?php echo esc_url($url); ?>" class="btn btn--primary" rel="<?php echo esc_attr($rel); ?>" target="_blank" data-track="comparison-cta">Try It</a>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('saas_comparison', 'saasfinder_shortcode_comparison');

// =============================================================================
// [pricing_table id="123"] — Renders pricing from meta
// =============================================================================
function saasfinder_shortcode_pricing_table($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'pricing_table');
    $post_id = absint($atts['id']) ?: get_the_ID();

    $pricing = get_post_meta($post_id, '_saas_tool_pricing', true);
    $free_plan = get_post_meta($post_id, '_saas_tool_free_plan', true);
    $tool_name = get_the_title($post_id);

    // This is a basic scaffold — pricing tiers would be stored as serialized meta in production
    ob_start();
    ?>
    <div class="pricing-table card">
        <h3><?php echo esc_html($tool_name); ?> Pricing</h3>
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($free_plan === 'yes' || $free_plan === 'limited') : ?>
                <tr>
                    <td>Free</td>
                    <td>$0</td>
                    <td><?php echo $free_plan === 'limited' ? 'Limited features' : 'Full free tier'; ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Paid</td>
                    <td><?php echo esc_html($pricing); ?></td>
                    <td>Starting price</td>
                </tr>
            </tbody>
        </table>
        <p class="text-sm text-muted"><?php echo saasfinder_get_last_verified_text($post_id); ?></p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pricing_table', 'saasfinder_shortcode_pricing_table');

// =============================================================================
// [verdict_box] — Rating + verdict + CTA (uses current post)
// =============================================================================
function saasfinder_shortcode_verdict_box($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'verdict_box');
    $post_id = absint($atts['id']) ?: get_the_ID();

    $rating = get_post_meta($post_id, '_saas_tool_rating', true);
    $verdict = get_post_meta($post_id, '_saas_tool_verdict', true);
    $url = get_post_meta($post_id, '_saas_affiliate_url', true);
    $cta_text = get_post_meta($post_id, '_saas_cta_text', true) ?: 'Try It Free';
    $rel = get_post_meta($post_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';

    if (!$rating && !$verdict) return '';

    ob_start();
    ?>
    <div class="verdict-box" itemscope itemtype="https://schema.org/Review">
        <div class="verdict-box__score">
            <span itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                <span itemprop="ratingValue"><?php echo esc_html($rating); ?></span><span class="text-muted">/10</span>
                <meta itemprop="bestRating" content="10">
            </span>
        </div>
        <div class="verdict-box__content">
            <p itemprop="description"><strong><?php echo esc_html($verdict); ?></strong></p>
        </div>
        <?php if ($url) : ?>
        <div class="verdict-box__cta">
            <a href="<?php echo esc_url($url); ?>" class="btn btn--primary btn--lg" rel="<?php echo esc_attr($rel); ?>" target="_blank" data-track="verdict-cta"><?php echo esc_html($cta_text); ?></a>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('verdict_box', 'saasfinder_shortcode_verdict_box');

// =============================================================================
// [pros_cons] — Two-column pros/cons (content in shortcode body not needed,
//               pulls from editor content structure)
// =============================================================================
function saasfinder_shortcode_pros_cons($atts, $content = null) {
    $atts = shortcode_atts(array(
        'pros' => '',
        'cons' => '',
    ), $atts, 'pros_cons');

    $pros = array_filter(array_map('trim', explode('|', $atts['pros'])));
    $cons = array_filter(array_map('trim', explode('|', $atts['cons'])));

    if (empty($pros) && empty($cons)) return '';

    ob_start();
    ?>
    <div class="pros-cons">
        <div>
            <h4>Pros</h4>
            <ul class="pros-cons__list pros-cons__list--pros">
                <?php foreach ($pros as $pro) : ?>
                    <li class="pros-cons__item"><?php echo esc_html($pro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Cons</h4>
            <ul class="pros-cons__list pros-cons__list--cons">
                <?php foreach ($cons as $con) : ?>
                    <li class="pros-cons__item"><?php echo esc_html($con); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pros_cons', 'saasfinder_shortcode_pros_cons');

// =============================================================================
// [tldr]...[/tldr] — Highlighted summary block
// =============================================================================
function saasfinder_shortcode_tldr($atts, $content = null) {
    if (!$content) return '';
    return '<div class="tldr-block"><span class="tldr-block__label">TL;DR</span>' . wp_kses_post(do_shortcode($content)) . '</div>';
}
add_shortcode('tldr', 'saasfinder_shortcode_tldr');

// =============================================================================
// [last_updated] — Auto-displays modified date
// =============================================================================
function saasfinder_shortcode_last_updated($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'last_updated');
    $post_id = absint($atts['id']) ?: get_the_ID();

    $modified = get_the_modified_date('F j, Y', $post_id);
    return '<span class="last-updated text-sm text-muted">Last updated: ' . esc_html($modified) . '</span>';
}
add_shortcode('last_updated', 'saasfinder_shortcode_last_updated');

// =============================================================================
// [direct_answer]...[/direct_answer] — AI-quotable answer block
// =============================================================================
function saasfinder_shortcode_direct_answer($atts, $content = null) {
    if (!$content) return '';
    return '<div class="direct-answer" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer"><div itemprop="text">' . wp_kses_post(do_shortcode($content)) . '</div></div>';
}
add_shortcode('direct_answer', 'saasfinder_shortcode_direct_answer');

// =============================================================================
// [user_sentiment positive|mixed|negative] — Visual sentiment indicator
// =============================================================================
function saasfinder_shortcode_user_sentiment($atts) {
    // Support positional first attribute
    $sentiment = isset($atts[0]) ? $atts[0] : 'mixed';
    $sentiment = sanitize_key($sentiment);

    $icons = array(
        'positive' => '👍',
        'mixed'    => '👋',
        'negative' => '👎',
    );

    $labels = array(
        'positive' => 'Overall Positive',
        'mixed'    => 'Mixed Reviews',
        'negative' => 'Overall Negative',
    );

    $icon = $icons[$sentiment] ?? $icons['mixed'];
    $label = $labels[$sentiment] ?? $labels['mixed'];

    return sprintf(
        '<span class="sentiment sentiment--%s">%s %s</span>',
        esc_attr($sentiment),
        $icon,
        esc_html($label)
    );
}
add_shortcode('user_sentiment', 'saasfinder_shortcode_user_sentiment');

// =============================================================================
// [review_source name="G2" rating="4.5" count="1200" url="..."]
// =============================================================================
function saasfinder_shortcode_review_source($atts) {
    $atts = shortcode_atts(array(
        'name'   => '',
        'rating' => '',
        'count'  => '',
        'url'    => '',
    ), $atts, 'review_source');

    if (!$atts['name']) return '';

    $link_open = $atts['url'] ? '<a href="' . esc_url($atts['url']) . '" rel="nofollow" target="_blank">' : '';
    $link_close = $atts['url'] ? '</a>' : '';

    ob_start();
    ?>
    <span class="review-source">
        <?php echo $link_open; ?>
        <span class="review-source__name"><?php echo esc_html($atts['name']); ?></span>
        <?php if ($atts['rating']) : ?>
            <span class="review-source__rating">★ <?php echo esc_html($atts['rating']); ?></span>
        <?php endif; ?>
        <?php if ($atts['count']) : ?>
            <span class="review-source__count">(<?php echo esc_html(number_format((int)$atts['count'])); ?> reviews)</span>
        <?php endif; ?>
        <?php echo $link_close; ?>
    </span>
    <?php
    return ob_get_clean();
}
add_shortcode('review_source', 'saasfinder_shortcode_review_source');

// =============================================================================
// [step number="1" title="..."]...[/step] — HowTo step with schema
// =============================================================================
function saasfinder_shortcode_step($atts, $content = null) {
    $atts = shortcode_atts(array(
        'number' => '1',
        'title'  => '',
    ), $atts, 'step');

    if (!$content) return '';

    ob_start();
    ?>
    <div class="tutorial-step" itemscope itemtype="https://schema.org/HowToStep" itemprop="step">
        <meta itemprop="position" content="<?php echo esc_attr($atts['number']); ?>">
        <h3 itemprop="name">Step <?php echo esc_html($atts['number']); ?>: <?php echo esc_html($atts['title']); ?></h3>
        <div itemprop="text" class="tutorial-step__content">
            <?php echo wp_kses_post(do_shortcode($content)); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('step', 'saasfinder_shortcode_step');

// =============================================================================
// [before_after before="$15/mo" after="$25/mo" label="Starter Plan"]
// =============================================================================
function saasfinder_shortcode_before_after($atts) {
    $atts = shortcode_atts(array(
        'before' => '',
        'after'  => '',
        'label'  => '',
    ), $atts, 'before_after');

    if (!$atts['before'] || !$atts['after']) return '';

    ob_start();
    ?>
    <div class="before-after">
        <?php if ($atts['label']) : ?>
            <span class="before-after__label"><?php echo esc_html($atts['label']); ?></span>
        <?php endif; ?>
        <span class="before-after__before"><?php echo esc_html($atts['before']); ?></span>
        <span class="before-after__arrow">→</span>
        <span class="before-after__after"><?php echo esc_html($atts['after']); ?></span>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('before_after', 'saasfinder_shortcode_before_after');

// =============================================================================
// [related_review id="123"] — Inline review card
// =============================================================================
function saasfinder_shortcode_related_review($atts) {
    $atts = shortcode_atts(array('id' => ''), $atts, 'related_review');
    $post_id = absint($atts['id']);

    if (!$post_id || get_post_type($post_id) !== 'saas-review') return '';

    $rating = get_post_meta($post_id, '_saas_tool_rating', true);
    $verdict = get_post_meta($post_id, '_saas_tool_verdict', true);
    $permalink = get_permalink($post_id);

    ob_start();
    ?>
    <a href="<?php echo esc_url($permalink); ?>" class="card related-review-card" style="display:flex;align-items:center;gap:var(--space-4);text-decoration:none;color:inherit;margin:var(--space-4) 0;">
        <?php if (has_post_thumbnail($post_id)) : ?>
            <div style="flex-shrink:0;width:48px;height:48px;">
                <?php echo get_the_post_thumbnail($post_id, 'saas-logo', array('style' => 'width:48px;height:48px;object-fit:contain;border-radius:8px;')); ?>
            </div>
        <?php endif; ?>
        <div>
            <strong><?php echo esc_html(get_the_title($post_id)); ?></strong>
            <?php if ($rating) : ?> <span class="badge badge--b2b"><?php echo esc_html($rating); ?>/10</span><?php endif; ?>
            <?php if ($verdict) : ?><br><span class="text-sm text-muted"><?php echo esc_html($verdict); ?></span><?php endif; ?>
        </div>
    </a>
    <?php
    return ob_get_clean();
}
add_shortcode('related_review', 'saasfinder_shortcode_related_review');

// =============================================================================
// [faq] — Renders FAQ from meta or content. Outputs FAQPage schema.
//
// Usage:
//   [faq] — auto-pulls from _saas_faq_items meta
//   [faq]Q: Question|A: Answer||Q: Another|A: Answer[/faq] — inline items
// =============================================================================
function saasfinder_shortcode_faq($atts, $content = null) {
    $post_id = get_the_ID();
    $faq_items = array();

    // If content is provided inline, parse it
    if ($content) {
        $pairs = explode('||', $content);
        foreach ($pairs as $pair) {
            $parts = explode('|', $pair);
            $q = '';
            $a = '';
            foreach ($parts as $part) {
                $part = trim($part);
                if (stripos($part, 'Q:') === 0) {
                    $q = trim(substr($part, 2));
                } elseif (stripos($part, 'A:') === 0) {
                    $a = trim(substr($part, 2));
                }
            }
            if ($q && $a) {
                $faq_items[] = array('q' => $q, 'a' => $a);
            }
        }
    }

    // Fallback: pull from post meta
    if (empty($faq_items)) {
        $meta = get_post_meta($post_id, '_saas_faq_items', true);
        if ($meta) {
            $faq_items = json_decode($meta, true);
        }
    }

    if (empty($faq_items)) return '';

    // Build FAQPage schema
    $schema_entities = array();
    foreach ($faq_items as $faq) {
        if (empty($faq['q']) || empty($faq['a'])) continue;
        $schema_entities[] = array(
            '@type' => 'Question',
            'name'  => $faq['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $faq['a'],
            ),
        );
    }

    ob_start();

    // Inline JSON-LD for FAQ
    if (!empty($schema_entities)) :
    ?>
    <script type="application/ld+json">
    <?php echo wp_json_encode(array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $schema_entities,
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <?php endif; ?>

    <div class="faq-section" style="margin:var(--space-8) 0;">
        <h2 style="margin-bottom:var(--space-4);">Frequently Asked Questions</h2>
        <?php foreach ($faq_items as $faq) :
            if (empty($faq['q']) || empty($faq['a'])) continue;
        ?>
        <details class="faq-item" style="border:1px solid var(--border-default);border-radius:var(--border-radius-sm);margin-bottom:var(--space-2);padding:var(--space-3) var(--space-4);">
            <summary style="cursor:pointer;font-weight:var(--font-semibold);"><?php echo esc_html($faq['q']); ?></summary>
            <p style="margin-top:var(--space-2);color:var(--text-secondary);line-height:var(--leading-relaxed);">
                <?php echo esc_html($faq['a']); ?>
            </p>
        </details>
        <?php endforeach; ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('faq', 'saasfinder_shortcode_faq');

// =============================================================================
// [feature_list] — Structured feature list with checkmarks
//
// Usage: [feature_list]Feature 1|Feature 2|Feature 3[/feature_list]
// =============================================================================
function saasfinder_shortcode_feature_list($atts, $content = null) {
    if (!$content) return '';

    $features = array_filter(array_map('trim', explode('|', $content)));
    if (empty($features)) return '';

    ob_start();
    ?>
    <ul class="feature-list" style="list-style:none;padding:0;margin:var(--space-4) 0;">
        <?php foreach ($features as $feature) : ?>
        <li style="padding:var(--space-2) 0;padding-left:var(--space-6);position:relative;line-height:var(--leading-relaxed);">
            <span style="position:absolute;left:0;color:var(--color-success);">&#10003;</span>
            <?php echo esc_html($feature); ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}
add_shortcode('feature_list', 'saasfinder_shortcode_feature_list');

// =============================================================================
// [callout type="info|warning|tip"] — Styled callout block
// =============================================================================
function saasfinder_shortcode_callout($atts, $content = null) {
    $atts = shortcode_atts(array('type' => 'info', 'title' => ''), $atts, 'callout');
    if (!$content) return '';

    $styles = array(
        'info'    => 'background:#EFF6FF;border-color:#3B82F6;',
        'warning' => 'background:#FEF3C7;border-color:#F59E0B;',
        'tip'     => 'background:#F0FDF4;border-color:#22C55E;',
        'danger'  => 'background:#FEF2F2;border-color:#EF4444;',
    );
    $icons = array(
        'info'    => 'ℹ️',
        'warning' => '⚠️',
        'tip'     => '💡',
        'danger'  => '🚨',
    );

    $type = isset($styles[$atts['type']]) ? $atts['type'] : 'info';
    $title = $atts['title'] ?: ucfirst($type);

    ob_start();
    ?>
    <div class="callout callout--<?php echo esc_attr($type); ?>" style="border-left:4px solid;<?php echo $styles[$type]; ?>padding:var(--space-4) var(--space-5);border-radius:0 var(--border-radius) var(--border-radius) 0;margin:var(--space-6) 0;">
        <p style="margin:0 0 var(--space-2);font-weight:var(--font-semibold);"><?php echo $icons[$type]; ?> <?php echo esc_html($title); ?></p>
        <div style="color:var(--text-secondary);line-height:var(--leading-relaxed);"><?php echo wp_kses_post(do_shortcode($content)); ?></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('callout', 'saasfinder_shortcode_callout');

// =============================================================================
// [rating value="8.5" label="Our Score"] — Inline rating display
// =============================================================================
function saasfinder_shortcode_rating($atts) {
    $atts = shortcode_atts(array('value' => '', 'label' => 'Rating', 'max' => '10'), $atts, 'rating');
    $value = floatval($atts['value']);
    if (!$value) return '';

    $max = floatval($atts['max']);
    $percent = ($value / $max) * 100;

    $color = 'var(--rating-average)';
    if ($value >= 8.5) $color = 'var(--rating-excellent)';
    elseif ($value >= 7) $color = 'var(--rating-good)';
    elseif ($value < 5) $color = 'var(--rating-poor)';

    return sprintf(
        '<span class="inline-rating" style="display:inline-flex;align-items:center;gap:var(--space-2);font-weight:var(--font-semibold);">
            <span style="color:%s;font-size:var(--text-lg);">%s</span><span style="color:var(--text-muted);font-size:var(--text-sm);">/%s %s</span>
        </span>',
        $color,
        esc_html($value),
        esc_html($max),
        esc_html($atts['label'])
    );
}
add_shortcode('rating', 'saasfinder_shortcode_rating');

// =============================================================================
// Helper: Get "last verified" text
// =============================================================================
function saasfinder_get_last_verified_text($post_id) {
    $date = get_post_meta($post_id, '_saas_last_verified_date', true);
    if (!$date) return '';
    $formatted = date_i18n('F j, Y', strtotime($date));
    return 'Pricing and features verified on ' . esc_html($formatted) . '.';
}
