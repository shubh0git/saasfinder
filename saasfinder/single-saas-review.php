<?php
/**
 * Single SaaS Review Template — The Money Page
 *
 * This is the highest-value template in the theme. Every element is ordered
 * for maximum AI citation potential and conversion.
 *
 * Layout order:
 * 1. Quick Answer block (AI-quotable, 2-3 sentences)
 * 2. At a Glance data table (real HTML table for AI parsers + featured snippets)
 * 3. Quick Verdict box (score + one-line + CTA — for fast-answer seekers)
 * 4. Tool name + logo + meta info
 * 5. Pros/cons table
 * 6. Detailed pricing breakdown table
 * 7. Screenshot gallery (lazy-loaded, WebP with fallback)
 * 8. Affiliate CTA buttons (primary + secondary)
 * 9. FAQ section (<details>/<summary> with FAQPage schema)
 * 10. Auto-generated internal links (via internal-links.php filter)
 * 11. Related tools sidebar
 * 12. "Alternatives to [Tool]" section
 *
 * @package SaasFinder
 */

get_header();

while (have_posts()) : the_post();
    $post_id = get_the_ID();

    // Pull all meta fields
    $quick_answer      = get_post_meta($post_id, '_saas_quick_answer', true);
    $description       = get_post_meta($post_id, '_saas_tool_description', true);
    $best_for          = get_post_meta($post_id, '_saas_tool_best_for', true);
    $pricing           = get_post_meta($post_id, '_saas_tool_pricing', true);
    $free_plan         = get_post_meta($post_id, '_saas_tool_free_plan', true);
    $rating            = get_post_meta($post_id, '_saas_tool_rating', true);
    $verdict           = get_post_meta($post_id, '_saas_tool_verdict', true);
    $affiliate_url     = get_post_meta($post_id, '_saas_affiliate_url', true);
    $secondary_url     = get_post_meta($post_id, '_saas_secondary_url', true);
    $cta_text          = get_post_meta($post_id, '_saas_cta_text', true) ?: 'Try It Free';
    $cta_text_secondary = get_post_meta($post_id, '_saas_cta_text_secondary', true) ?: 'View Pricing';
    $rel               = get_post_meta($post_id, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
    $tool_website      = get_post_meta($post_id, '_saas_tool_website', true);
    $last_verified     = get_post_meta($post_id, '_saas_last_verified_date', true);
    $tool_name         = get_the_title();

    // Taxonomy data
    $categories = wp_get_post_terms($post_id, 'saas-category');
    $category   = !empty($categories) ? $categories[0] : null;
    $audiences  = wp_get_post_terms($post_id, 'audience', array('fields' => 'names'));
    $pricing_models = wp_get_post_terms($post_id, 'pricing-model', array('fields' => 'names'));

    // Rating color logic
    $rating_float = floatval($rating);
    $rating_class = 'rating-good';
    if ($rating_float >= 9) $rating_class = 'rating-excellent';
    elseif ($rating_float >= 7) $rating_class = 'rating-good';
    elseif ($rating_float >= 5) $rating_class = 'rating-average';
    else $rating_class = 'rating-poor';
?>

<article <?php post_class('review-article'); ?> itemscope itemtype="https://schema.org/Review">
    <meta itemprop="datePublished" content="<?php echo get_the_date('c'); ?>">
    <meta itemprop="dateModified" content="<?php echo get_the_modified_date('c'); ?>">
    <span itemprop="author" itemscope itemtype="https://schema.org/Organization">
        <meta itemprop="name" content="SaasFinder">
        <meta itemprop="url" content="<?php echo esc_url(home_url('/')); ?>">
    </span>

    <div class="container" style="padding-top:var(--space-6);padding-bottom:var(--space-12);">
        <div class="layout-with-sidebar">

            <!-- ============================================================
                 MAIN CONTENT COLUMN
                 ============================================================ -->
            <div class="review-main">

                <!-- ========================================================
                     1. QUICK ANSWER BLOCK (Primary AEO element)
                     AI engines cite this block verbatim. Keep it factual,
                     definitive, and under 3 sentences.
                     ======================================================== -->
                <?php if ($quick_answer) : ?>
                <section class="quick-answer" aria-label="Quick summary">
                    <p itemprop="description"><?php echo wp_kses_post($quick_answer); ?></p>
                </section>
                <?php endif; ?>

                <!-- ========================================================
                     2. AT A GLANCE TABLE
                     Real HTML <table> — AI parsers and Google featured snippets
                     strongly prefer tables with clear <th> headers over styled divs.
                     ======================================================== -->
                <section class="at-a-glance" aria-label="At a glance">
                    <table class="at-a-glance__table">
                        <caption class="sr-only"><?php echo esc_html($tool_name); ?> — Key Facts</caption>
                        <tbody>
                            <?php if ($description) : ?>
                            <tr>
                                <th scope="row">What it does</th>
                                <td><?php echo esc_html($description); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($best_for) : ?>
                            <tr>
                                <th scope="row">Best for</th>
                                <td><?php echo esc_html($best_for); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($pricing) : ?>
                            <tr>
                                <th scope="row">Pricing starts at</th>
                                <td><?php echo esc_html($pricing); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th scope="row">Free plan</th>
                                <td>
                                    <?php
                                    switch ($free_plan) {
                                        case 'yes':
                                            echo '<span class="badge badge--freemium">Yes — Free Forever Plan</span>';
                                            break;
                                        case 'limited':
                                            echo '<span class="badge badge--free-trial">Limited Free Tier</span>';
                                            break;
                                        case 'no':
                                        default:
                                            echo '<span class="badge badge--paid">No — Paid Only</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php if ($rating) : ?>
                            <tr>
                                <th scope="row">Our rating</th>
                                <td>
                                    <strong class="<?php echo esc_attr($rating_class); ?>"><?php echo esc_html($rating); ?>/10</strong>
                                    <?php echo saasfinder_render_star_rating($rating_float); ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($audiences)) : ?>
                            <tr>
                                <th scope="row">Audience</th>
                                <td>
                                    <?php foreach ($audiences as $aud) : ?>
                                        <span class="badge badge--<?php echo esc_attr(strtolower(str_replace(' ', '', $aud))); ?>"><?php echo esc_html($aud); ?></span>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($tool_website) : ?>
                            <tr>
                                <th scope="row">Website</th>
                                <td><a href="<?php echo esc_url($tool_website); ?>" rel="nofollow" target="_blank"><?php echo esc_html(parse_url($tool_website, PHP_URL_HOST)); ?></a></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- ========================================================
                     3. QUICK VERDICT BOX
                     For search visitors who came for a fast answer.
                     Score + one-line verdict + primary CTA.
                     ======================================================== -->
                <?php if ($rating || $verdict) : ?>
                <section class="verdict-box" aria-label="Quick verdict">
                    <?php if ($rating) : ?>
                    <div class="verdict-box__score <?php echo esc_attr($rating_class); ?>">
                        <span itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                            <span itemprop="ratingValue"><?php echo esc_html($rating); ?></span>
                            <meta itemprop="bestRating" content="10">
                            <meta itemprop="worstRating" content="1">
                        </span>
                        <span class="verdict-box__score-label">/10</span>
                    </div>
                    <?php endif; ?>

                    <div class="verdict-box__content">
                        <?php if ($verdict) : ?>
                            <p class="verdict-box__text"><strong><?php echo esc_html($verdict); ?></strong></p>
                        <?php endif; ?>
                        <?php if ($last_verified) : ?>
                            <p class="verdict-box__verified text-sm text-muted">
                                Pricing &amp; features verified <?php echo esc_html(date_i18n('F j, Y', strtotime($last_verified))); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($affiliate_url) : ?>
                    <div class="verdict-box__cta">
                        <a href="<?php echo esc_url($affiliate_url); ?>"
                           class="btn btn--primary btn--lg saas-cta"
                           rel="<?php echo esc_attr($rel); ?>"
                           target="_blank"
                           data-track="verdict-cta"
                           data-tool="<?php echo esc_attr($tool_name); ?>">
                            <?php echo esc_html($cta_text); ?>
                        </a>
                        <?php if ($secondary_url) : ?>
                        <a href="<?php echo esc_url($secondary_url); ?>"
                           class="btn btn--outline verdict-box__secondary-cta"
                           rel="<?php echo esc_attr($rel); ?>"
                           target="_blank"
                           data-track="verdict-secondary-cta"
                           data-tool="<?php echo esc_attr($tool_name); ?>">
                            <?php echo esc_html($cta_text_secondary); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- ========================================================
                     4. TOOL NAME + LOGO + META INFO
                     The actual H1 for the page. Placed after the quick-answer
                     blocks because search visitors get their answer first.
                     ======================================================== -->
                <header class="review-header">
                    <div class="review-header__top">
                        <?php if (has_post_thumbnail()) : ?>
                        <div class="review-header__logo">
                            <?php the_post_thumbnail('saas-logo', array(
                                'width'  => 64,
                                'height' => 64,
                                'style'  => 'object-fit:contain;border-radius:12px;border:1px solid var(--border-default);',
                                'itemprop' => 'image',
                            )); ?>
                        </div>
                        <?php endif; ?>

                        <div class="review-header__info">
                            <h1 class="review-header__title" itemprop="itemReviewed" itemscope itemtype="https://schema.org/SoftwareApplication">
                                <span itemprop="name"><?php echo esc_html($tool_name); ?></span> Review
                                <meta itemprop="applicationCategory" content="BusinessApplication">
                                <meta itemprop="operatingSystem" content="Web">
                                <?php if ($tool_website) : ?>
                                    <meta itemprop="url" content="<?php echo esc_url($tool_website); ?>">
                                <?php endif; ?>
                            </h1>

                            <div class="review-header__meta text-sm text-muted">
                                <span>By <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" rel="author"><?php the_author(); ?></a></span>
                                <span class="review-header__separator">&middot;</span>
                                <time datetime="<?php echo get_the_modified_date('c'); ?>">Updated <?php echo get_the_modified_date('F j, Y'); ?></time>
                                <?php if ($last_verified) : ?>
                                    <span class="review-header__separator">&middot;</span>
                                    <span class="review-header__verified">Verified <?php echo esc_html(date_i18n('M j, Y', strtotime($last_verified))); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($pricing_models) || !empty($audiences)) : ?>
                            <div class="review-header__badges" style="margin-top:var(--space-2);">
                                <?php foreach ($pricing_models as $pm) : ?>
                                    <span class="badge badge--<?php echo esc_attr(sanitize_title($pm)); ?>"><?php echo esc_html($pm); ?></span>
                                <?php endforeach; ?>
                                <?php foreach ($audiences as $aud) : ?>
                                    <span class="badge badge--<?php echo esc_attr(strtolower(str_replace(' ', '', $aud))); ?>"><?php echo esc_html($aud); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <!-- ========================================================
                     5–8. MAIN EDITOR CONTENT
                     The post editor content should contain:
                     - Pros/cons via [pros_cons] shortcode
                     - Pricing breakdown (can use [pricing_table] or manual tables)
                     - Screenshots (inserted as images with proper alt text)
                     - Inline CTA buttons via [saas_cta]
                     - Any additional editorial content
                     ======================================================== -->
                <div class="entry-content review-content" itemprop="reviewBody">
                    <?php the_content(); ?>
                </div>

                <!-- ========================================================
                     8. FULL-WIDTH CTA SECTION (after main content)
                     A clear conversion point for readers who scrolled through.
                     ======================================================== -->
                <?php if ($affiliate_url) : ?>
                <section class="review-cta-section" aria-label="Try <?php echo esc_attr($tool_name); ?>">
                    <div class="review-cta-section__inner">
                        <div class="review-cta-section__text">
                            <h2 class="review-cta-section__heading">Ready to try <?php echo esc_html($tool_name); ?>?</h2>
                            <?php if ($verdict) : ?>
                                <p class="text-muted"><?php echo esc_html($verdict); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="review-cta-section__buttons">
                            <a href="<?php echo esc_url($affiliate_url); ?>"
                               class="btn btn--primary btn--lg saas-cta"
                               rel="<?php echo esc_attr($rel); ?>"
                               target="_blank"
                               data-track="bottom-cta"
                               data-tool="<?php echo esc_attr($tool_name); ?>">
                                <?php echo esc_html($cta_text); ?>
                            </a>
                            <?php if ($secondary_url) : ?>
                            <a href="<?php echo esc_url($secondary_url); ?>"
                               class="btn btn--outline btn--lg"
                               rel="<?php echo esc_attr($rel); ?>"
                               target="_blank"
                               data-track="bottom-secondary-cta"
                               data-tool="<?php echo esc_attr($tool_name); ?>">
                                <?php echo esc_html($cta_text_secondary); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- ========================================================
                     9. FAQ SECTION
                     Uses <details>/<summary> for progressive disclosure.
                     FAQPage schema is output by schema.php when it detects
                     this structure. Targets "People Also Ask" + AI citation.
                     ======================================================== -->
                <?php
                // Check if the content already has FAQ via shortcode/manual entry
                // If not, check for a custom field with FAQ data
                $faq_data = get_post_meta($post_id, '_saas_faq_items', true);
                if (!empty($faq_data) && is_array($faq_data)) :
                ?>
                <section class="faq-section" aria-label="Frequently Asked Questions">
                    <h2>Frequently Asked Questions about <?php echo esc_html($tool_name); ?></h2>
                    <?php foreach ($faq_data as $faq) : ?>
                    <details>
                        <summary><?php echo esc_html($faq['question']); ?></summary>
                        <div>
                            <p><?php echo wp_kses_post($faq['answer']); ?></p>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>

                <!-- ========================================================
                     10. INTERNAL LINKS (auto-generated via the_content filter
                         in /inc/internal-links.php — no manual code needed here)
                     ======================================================== -->

                <!-- ========================================================
                     12. ALTERNATIVES SECTION
                     Pulls other reviewed tools in the same category.
                     ======================================================== -->
                <?php
                if ($category) :
                    $alternatives = get_posts(array(
                        'post_type'    => 'saas-review',
                        'numberposts'  => 4,
                        'post__not_in' => array($post_id),
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

                    if (!empty($alternatives)) :
                ?>
                <section class="review-alternatives" aria-label="Alternative tools">
                    <h2>Alternatives to <?php echo esc_html($tool_name); ?></h2>
                    <p class="text-muted">Other <?php echo esc_html(strtolower($category->name)); ?> tools worth considering:</p>

                    <div class="grid-auto" style="margin-top:var(--space-6);">
                        <?php
                        foreach ($alternatives as $alt) :
                            $GLOBALS['post'] = $alt;
                            setup_postdata($alt);
                            get_template_part('template-parts/components/review-card');
                        endforeach;
                        wp_reset_postdata();
                        ?>
                    </div>

                    <div style="margin-top:var(--space-6);">
                        <a href="<?php echo esc_url(home_url('/alternatives/' . get_post_field('post_name', $post_id) . '/')); ?>" class="btn btn--outline">
                            View All <?php echo esc_html($tool_name); ?> Alternatives
                        </a>
                        <?php if ($category) : ?>
                        <a href="<?php echo esc_url(home_url('/best/' . $category->slug . '-software/')); ?>" class="btn btn--outline" style="margin-left:var(--space-3);">
                            All <?php echo esc_html($category->name); ?> Tools
                        </a>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; endif; ?>

            </div><!-- .review-main -->

            <!-- ============================================================
                 11. SIDEBAR (sticky CTA + related tools)
                 ============================================================ -->
            <?php get_sidebar(); ?>

        </div><!-- .layout-with-sidebar -->
    </div><!-- .container -->
</article>

<!-- Mobile sticky bottom CTA bar (hidden on desktop via CSS) -->
<?php if ($affiliate_url) : ?>
<div class="sticky-cta--mobile" aria-label="Quick action">
    <div class="sticky-cta--mobile__rating">
        <strong><?php echo esc_html($rating); ?></strong><span class="text-muted">/10</span>
    </div>
    <a href="<?php echo esc_url($affiliate_url); ?>"
       class="btn btn--primary saas-cta"
       rel="<?php echo esc_attr($rel); ?>"
       target="_blank"
       data-track="mobile-sticky-cta"
       data-tool="<?php echo esc_attr($tool_name); ?>">
        <?php echo esc_html($cta_text); ?>
    </a>
</div>
<?php endif; ?>

<?php
endwhile;
get_footer();

// =============================================================================
// HELPER: Render visual star rating from numeric score
// =============================================================================
function saasfinder_render_star_rating($score) {
    $stars_out_of_5 = round($score / 2, 1);
    $full_stars = floor($stars_out_of_5);
    $half_star = ($stars_out_of_5 - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

    $html = '<span class="rating__stars" aria-label="' . esc_attr($stars_out_of_5 . ' out of 5 stars') . '">';
    $html .= str_repeat('★', $full_stars);
    if ($half_star) $html .= '½';
    $html .= str_repeat('☆', $empty_stars);
    $html .= '</span>';
    return $html;
}
?>
