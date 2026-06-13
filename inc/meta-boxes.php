<?php
/**
 * Meta Boxes — Custom fields for CPTs and blog posts.
 *
 * Architecture:
 * - saas-review: affiliate data, tool info, rating, pricing, last_verified_date
 * - saas-deal: deal pricing, countdown, terms
 * - blog posts: conditional fields based on blog-format taxonomy term
 *
 * Uses native WordPress meta boxes (no ACF/CMB2 dependency).
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

// =============================================================================
// REGISTER META BOXES
// =============================================================================

function saasfinder_register_meta_boxes() {

    // SaaS Review — Affiliate & Tool Info
    add_meta_box(
        'saasfinder_review_details',
        __('Review Details & Affiliate Info', 'saasfinder'),
        'saasfinder_review_details_callback',
        'saas-review',
        'normal',
        'high'
    );

    // SaaS Review — Last Verified Date
    add_meta_box(
        'saasfinder_review_freshness',
        __('Content Freshness', 'saasfinder'),
        'saasfinder_review_freshness_callback',
        'saas-review',
        'side',
        'default'
    );

    // SaaS Deal — Deal Info
    add_meta_box(
        'saasfinder_deal_details',
        __('Deal Details', 'saasfinder'),
        'saasfinder_deal_details_callback',
        'saas-deal',
        'normal',
        'high'
    );

    // Blog Posts — Format-specific fields
    add_meta_box(
        'saasfinder_blog_format_fields',
        __('Blog Format Fields', 'saasfinder'),
        'saasfinder_blog_format_fields_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'saasfinder_register_meta_boxes');

// =============================================================================
// SaaS REVIEW META BOX — Callback & Save
// =============================================================================

function saasfinder_review_details_callback($post) {
    wp_nonce_field('saasfinder_review_meta', 'saasfinder_review_nonce');

    // Get existing values
    $affiliate_url     = get_post_meta($post->ID, '_saas_affiliate_url', true);
    $affiliate_rel     = get_post_meta($post->ID, '_saas_affiliate_rel', true) ?: 'nofollow sponsored';
    $cta_text          = get_post_meta($post->ID, '_saas_cta_text', true) ?: 'Try It Free';
    $cta_text_secondary = get_post_meta($post->ID, '_saas_cta_text_secondary', true) ?: 'View Pricing';
    $secondary_url     = get_post_meta($post->ID, '_saas_secondary_url', true);
    $disclosure_text   = get_post_meta($post->ID, '_saas_disclosure_text', true) ?: 'We may earn a commission when you click links on this page. This doesn\'t affect our editorial independence.';
    $tool_website      = get_post_meta($post->ID, '_saas_tool_website', true);
    $tool_description  = get_post_meta($post->ID, '_saas_tool_description', true);
    $tool_best_for     = get_post_meta($post->ID, '_saas_tool_best_for', true);
    $tool_pricing      = get_post_meta($post->ID, '_saas_tool_pricing', true);
    $tool_free_plan    = get_post_meta($post->ID, '_saas_tool_free_plan', true);
    $tool_rating       = get_post_meta($post->ID, '_saas_tool_rating', true);
    $tool_verdict      = get_post_meta($post->ID, '_saas_tool_verdict', true);
    $quick_answer      = get_post_meta($post->ID, '_saas_quick_answer', true);

    ?>
    <style>.sf-meta-field { margin-bottom: 15px; } .sf-meta-field label { display: block; font-weight: 600; margin-bottom: 4px; } .sf-meta-field input[type="text"], .sf-meta-field input[type="url"], .sf-meta-field input[type="number"], .sf-meta-field textarea, .sf-meta-field select { width: 100%; padding: 8px; } .sf-meta-section { border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px; } .sf-meta-section h4 { margin: 0 0 10px; color: #1d2327; }</style>

    <h4>Quick Answer (AI-quotable, 2-3 sentences)</h4>
    <div class="sf-meta-field">
        <textarea name="_saas_quick_answer" rows="3" placeholder="What is [Tool]? Is it good? Plain-language answer for AI engines."><?php echo esc_textarea($quick_answer); ?></textarea>
    </div>

    <div class="sf-meta-section">
        <h4>At a Glance (table data)</h4>
        <div class="sf-meta-field">
            <label>What it does (one line)</label>
            <input type="text" name="_saas_tool_description" value="<?php echo esc_attr($tool_description); ?>" placeholder="e.g., All-in-one workspace for notes, docs, and project management">
        </div>
        <div class="sf-meta-field">
            <label>Best for (audience)</label>
            <input type="text" name="_saas_tool_best_for" value="<?php echo esc_attr($tool_best_for); ?>" placeholder="e.g., Small teams and solopreneurs">
        </div>
        <div class="sf-meta-field">
            <label>Pricing starts at</label>
            <input type="text" name="_saas_tool_pricing" value="<?php echo esc_attr($tool_pricing); ?>" placeholder="e.g., $8/user/month">
        </div>
        <div class="sf-meta-field">
            <label>Free plan?</label>
            <select name="_saas_tool_free_plan">
                <option value="yes" <?php selected($tool_free_plan, 'yes'); ?>>Yes</option>
                <option value="no" <?php selected($tool_free_plan, 'no'); ?>>No</option>
                <option value="limited" <?php selected($tool_free_plan, 'limited'); ?>>Limited</option>
            </select>
        </div>
        <div class="sf-meta-field">
            <label>Our Rating (out of 10)</label>
            <input type="number" name="_saas_tool_rating" min="1" max="10" step="0.1" value="<?php echo esc_attr($tool_rating); ?>" placeholder="8.5">
        </div>
        <div class="sf-meta-field">
            <label>One-line Verdict</label>
            <input type="text" name="_saas_tool_verdict" value="<?php echo esc_attr($tool_verdict); ?>" placeholder="e.g., Best all-in-one workspace for teams under 50 people.">
        </div>
    </div>

    <div class="sf-meta-section">
        <h4>Pros & Cons</h4>
        <?php
        $pros = get_post_meta($post->ID, '_saas_pros', true);
        $cons = get_post_meta($post->ID, '_saas_cons', true);
        ?>
        <div class="sf-meta-field">
            <label>Pros (one per line)</label>
            <textarea name="_saas_pros" rows="4" placeholder="Fast onboarding&#10;Generous free tier&#10;Great API documentation"><?php echo esc_textarea($pros); ?></textarea>
        </div>
        <div class="sf-meta-field">
            <label>Cons (one per line)</label>
            <textarea name="_saas_cons" rows="4" placeholder="Limited integrations&#10;No mobile app&#10;Steep learning curve"><?php echo esc_textarea($cons); ?></textarea>
        </div>
    </div>

    <div class="sf-meta-section">
        <h4>FAQ Section (optional — generates FAQPage schema)</h4>
        <?php
        $faq_items = get_post_meta($post->ID, '_saas_faq_items', true);
        $faq_items = $faq_items ? json_decode($faq_items, true) : array();
        if (empty($faq_items)) $faq_items = array(array('q' => '', 'a' => ''));
        ?>
        <div id="sf-faq-repeater">
            <?php foreach ($faq_items as $i => $faq) : ?>
            <div class="sf-faq-item" style="border:1px solid #ddd;padding:10px;margin-bottom:8px;border-radius:4px;">
                <div class="sf-meta-field">
                    <label>Question <?php echo $i + 1; ?></label>
                    <input type="text" name="_saas_faq_q[]" value="<?php echo esc_attr($faq['q']); ?>" placeholder="e.g., Is Notion free?">
                </div>
                <div class="sf-meta-field">
                    <label>Answer</label>
                    <textarea name="_saas_faq_a[]" rows="2" placeholder="Concise, factual answer (2-3 sentences)"><?php echo esc_textarea($faq['a']); ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" onclick="saasfinder_add_faq()">+ Add FAQ Item</button>
        <script>
        function saasfinder_add_faq() {
            var container = document.getElementById('sf-faq-repeater');
            var count = container.querySelectorAll('.sf-faq-item').length + 1;
            var html = '<div class="sf-faq-item" style="border:1px solid #ddd;padding:10px;margin-bottom:8px;border-radius:4px;">' +
                '<div class="sf-meta-field"><label>Question ' + count + '</label><input type="text" name="_saas_faq_q[]" placeholder="e.g., Is Notion free?"></div>' +
                '<div class="sf-meta-field"><label>Answer</label><textarea name="_saas_faq_a[]" rows="2" placeholder="Concise, factual answer"></textarea></div>' +
                '<button type="button" class="button" onclick="this.parentElement.remove()" style="color:#a00;">Remove</button></div>';
            container.insertAdjacentHTML('beforeend', html);
        }
        </script>
    </div>

    <div class="sf-meta-section">
        <h4>Affiliate & CTA Settings</h4>
        <div class="sf-meta-field">
            <label>Affiliate URL (primary CTA)</label>
            <input type="url" name="_saas_affiliate_url" value="<?php echo esc_url($affiliate_url); ?>" placeholder="https://...">
        </div>
        <div class="sf-meta-field">
            <label>Secondary URL (e.g., pricing page)</label>
            <input type="url" name="_saas_secondary_url" value="<?php echo esc_url($secondary_url); ?>" placeholder="https://...">
        </div>
        <div class="sf-meta-field">
            <label>Primary CTA Button Text</label>
            <input type="text" name="_saas_cta_text" value="<?php echo esc_attr($cta_text); ?>">
        </div>
        <div class="sf-meta-field">
            <label>Secondary CTA Button Text</label>
            <input type="text" name="_saas_cta_text_secondary" value="<?php echo esc_attr($cta_text_secondary); ?>">
        </div>
        <div class="sf-meta-field">
            <label>Link Rel Attribute</label>
            <select name="_saas_affiliate_rel">
                <option value="nofollow sponsored" <?php selected($affiliate_rel, 'nofollow sponsored'); ?>>nofollow sponsored (default)</option>
                <option value="nofollow" <?php selected($affiliate_rel, 'nofollow'); ?>>nofollow only</option>
                <option value="sponsored" <?php selected($affiliate_rel, 'sponsored'); ?>>sponsored only</option>
            </select>
        </div>
        <div class="sf-meta-field">
            <label>Disclosure Text</label>
            <textarea name="_saas_disclosure_text" rows="2"><?php echo esc_textarea($disclosure_text); ?></textarea>
        </div>
        <div class="sf-meta-field">
            <label>Tool Website (non-affiliate, for reference)</label>
            <input type="url" name="_saas_tool_website" value="<?php echo esc_url($tool_website); ?>">
        </div>
    </div>
    <?php
}

/**
 * Content Freshness meta box — last_verified_date.
 */
function saasfinder_review_freshness_callback($post) {
    $last_verified = get_post_meta($post->ID, '_saas_last_verified_date', true);
    ?>
    <div class="sf-meta-field">
        <label>Last Verified Date</label>
        <input type="date" name="_saas_last_verified_date" value="<?php echo esc_attr($last_verified); ?>">
        <p class="description">When pricing & features were last manually checked. Displays as "Verified on [date]" on frontend.</p>
    </div>
    <?php
}

/**
 * Save SaaS Review meta.
 */
function saasfinder_save_review_meta($post_id) {
    if (!isset($_POST['saasfinder_review_nonce']) || !wp_verify_nonce($_POST['saasfinder_review_nonce'], 'saasfinder_review_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = array(
        '_saas_affiliate_url',
        '_saas_affiliate_rel',
        '_saas_cta_text',
        '_saas_cta_text_secondary',
        '_saas_secondary_url',
        '_saas_disclosure_text',
        '_saas_tool_website',
        '_saas_tool_description',
        '_saas_tool_best_for',
        '_saas_tool_pricing',
        '_saas_tool_free_plan',
        '_saas_tool_rating',
        '_saas_tool_verdict',
        '_saas_quick_answer',
        '_saas_last_verified_date',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            if (strpos($field, '_url') !== false) {
                $value = esc_url_raw($_POST[$field]);
            }
            if (in_array($field, array('_saas_disclosure_text', '_saas_quick_answer'))) {
                $value = sanitize_textarea_field($_POST[$field]);
            }
            update_post_meta($post_id, $field, $value);
        }
    }

    // Pros & Cons (textarea, one per line)
    if (isset($_POST['_saas_pros'])) {
        update_post_meta($post_id, '_saas_pros', sanitize_textarea_field($_POST['_saas_pros']));
    }
    if (isset($_POST['_saas_cons'])) {
        update_post_meta($post_id, '_saas_cons', sanitize_textarea_field($_POST['_saas_cons']));
    }

    // FAQ repeater — store as JSON
    if (isset($_POST['_saas_faq_q']) && is_array($_POST['_saas_faq_q'])) {
        $faq_items = array();
        $questions = $_POST['_saas_faq_q'];
        $answers   = isset($_POST['_saas_faq_a']) ? $_POST['_saas_faq_a'] : array();
        foreach ($questions as $i => $q) {
            $q = sanitize_text_field($q);
            $a = isset($answers[$i]) ? sanitize_textarea_field($answers[$i]) : '';
            if ($q || $a) {
                $faq_items[] = array('q' => $q, 'a' => $a);
            }
        }
        update_post_meta($post_id, '_saas_faq_items', wp_json_encode($faq_items));
    }
}
add_action('save_post_saas-review', 'saasfinder_save_review_meta');

// =============================================================================
// SaaS DEAL META BOX — Callback & Save
// =============================================================================

function saasfinder_deal_details_callback($post) {
    wp_nonce_field('saasfinder_deal_meta', 'saasfinder_deal_nonce');

    $deal_url        = get_post_meta($post->ID, '_deal_affiliate_url', true);
    $deal_original   = get_post_meta($post->ID, '_deal_original_price', true);
    $deal_discounted = get_post_meta($post->ID, '_deal_discounted_price', true);
    $deal_discount   = get_post_meta($post->ID, '_deal_discount_percent', true);
    $deal_expires    = get_post_meta($post->ID, '_deal_expires', true);
    $deal_terms      = get_post_meta($post->ID, '_deal_terms', true);
    $deal_cta_text   = get_post_meta($post->ID, '_deal_cta_text', true) ?: 'Grab This Deal';
    $deal_review_id  = get_post_meta($post->ID, '_deal_linked_review', true);

    ?>
    <div class="sf-meta-field">
        <label>Affiliate URL</label>
        <input type="url" name="_deal_affiliate_url" value="<?php echo esc_url($deal_url); ?>">
    </div>
    <div class="sf-meta-field">
        <label>Original Price</label>
        <input type="text" name="_deal_original_price" value="<?php echo esc_attr($deal_original); ?>" placeholder="$49/mo">
    </div>
    <div class="sf-meta-field">
        <label>Discounted Price</label>
        <input type="text" name="_deal_discounted_price" value="<?php echo esc_attr($deal_discounted); ?>" placeholder="$29/mo">
    </div>
    <div class="sf-meta-field">
        <label>Discount %</label>
        <input type="text" name="_deal_discount_percent" value="<?php echo esc_attr($deal_discount); ?>" placeholder="40">
    </div>
    <div class="sf-meta-field">
        <label>Expires (date)</label>
        <input type="datetime-local" name="_deal_expires" value="<?php echo esc_attr($deal_expires); ?>">
    </div>
    <div class="sf-meta-field">
        <label>Deal Terms / Fine Print</label>
        <textarea name="_deal_terms" rows="3"><?php echo esc_textarea($deal_terms); ?></textarea>
    </div>
    <div class="sf-meta-field">
        <label>CTA Button Text</label>
        <input type="text" name="_deal_cta_text" value="<?php echo esc_attr($deal_cta_text); ?>">
    </div>
    <div class="sf-meta-field">
        <label>Linked SaaS Review (Post ID)</label>
        <input type="number" name="_deal_linked_review" value="<?php echo esc_attr($deal_review_id); ?>" placeholder="Leave empty if no review exists yet">
    </div>
    <?php
}

function saasfinder_save_deal_meta($post_id) {
    if (!isset($_POST['saasfinder_deal_nonce']) || !wp_verify_nonce($_POST['saasfinder_deal_nonce'], 'saasfinder_deal_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $text_fields = array('_deal_original_price', '_deal_discounted_price', '_deal_discount_percent', '_deal_expires', '_deal_cta_text', '_deal_linked_review');
    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    if (isset($_POST['_deal_affiliate_url'])) {
        update_post_meta($post_id, '_deal_affiliate_url', esc_url_raw($_POST['_deal_affiliate_url']));
    }
    if (isset($_POST['_deal_terms'])) {
        update_post_meta($post_id, '_deal_terms', sanitize_textarea_field($_POST['_deal_terms']));
    }
}
add_action('save_post_saas-deal', 'saasfinder_save_deal_meta');

// =============================================================================
// BLOG FORMAT CONDITIONAL META FIELDS
//
// These fields show/hide based on the selected blog-format taxonomy term.
// JavaScript in the admin toggles visibility.
// =============================================================================

function saasfinder_blog_format_fields_callback($post) {
    wp_nonce_field('saasfinder_blog_format_meta', 'saasfinder_blog_format_nonce');

    // Get current blog-format
    $terms = wp_get_post_terms($post->ID, 'blog-format', array('fields' => 'slugs'));
    $current_format = !empty($terms) ? $terms[0] : '';

    // Common fields
    $linked_review_ids = get_post_meta($post->ID, '_blog_linked_review_ids', true);
    $direct_answer_toggle = get_post_meta($post->ID, '_blog_direct_answer_enabled', true);

    // User Reviews Roundup fields
    $review_tool_name   = get_post_meta($post->ID, '_blog_review_tool_name', true);
    $review_sources     = get_post_meta($post->ID, '_blog_review_sources', true);
    $review_sentiment   = get_post_meta($post->ID, '_blog_review_sentiment', true);

    // Single Query Answer fields
    $query_text     = get_post_meta($post->ID, '_blog_query_text', true);
    $query_source   = get_post_meta($post->ID, '_blog_query_source', true);

    // News Reaction fields
    $news_date      = get_post_meta($post->ID, '_blog_news_date', true);
    $news_tool      = get_post_meta($post->ID, '_blog_news_tool', true);
    $news_type      = get_post_meta($post->ID, '_blog_news_change_type', true);

    ?>
    <p class="description">Fields below are conditional — they apply based on the Blog Format taxonomy selected for this post. If no format is set, only the "Common" fields appear.</p>

    <!-- Common Fields (all formats) -->
    <div class="sf-meta-section">
        <h4>Common Fields</h4>
        <div class="sf-meta-field">
            <label>Linked SaaS Review Post IDs (comma-separated)</label>
            <input type="text" name="_blog_linked_review_ids" value="<?php echo esc_attr($linked_review_ids); ?>" placeholder="123, 456">
            <p class="description">Links this blog post to specific SaaS reviews for internal linking.</p>
        </div>
        <div class="sf-meta-field">
            <label><input type="checkbox" name="_blog_direct_answer_enabled" value="1" <?php checked($direct_answer_toggle, '1'); ?>> Show Direct Answer block at top</label>
        </div>
    </div>

    <!-- User Reviews Roundup Fields -->
    <div class="sf-meta-section sf-format-fields" data-format="user-reviews-roundup" <?php echo $current_format !== 'user-reviews-roundup' ? 'style="display:none;"' : ''; ?>>
        <h4>User Reviews Roundup Fields</h4>
        <div class="sf-meta-field">
            <label>Tool Name</label>
            <input type="text" name="_blog_review_tool_name" value="<?php echo esc_attr($review_tool_name); ?>" placeholder="e.g., Notion">
        </div>
        <div class="sf-meta-field">
            <label>Review Sources (comma-separated)</label>
            <input type="text" name="_blog_review_sources" value="<?php echo esc_attr($review_sources); ?>" placeholder="G2, Capterra, Trustpilot, Reddit">
        </div>
        <div class="sf-meta-field">
            <label>Overall Sentiment</label>
            <select name="_blog_review_sentiment">
                <option value="">— Select —</option>
                <option value="positive" <?php selected($review_sentiment, 'positive'); ?>>Positive</option>
                <option value="mixed" <?php selected($review_sentiment, 'mixed'); ?>>Mixed</option>
                <option value="negative" <?php selected($review_sentiment, 'negative'); ?>>Negative</option>
            </select>
        </div>
    </div>

    <!-- Single Query Answer Fields -->
    <div class="sf-meta-section sf-format-fields" data-format="single-query-answer" <?php echo $current_format !== 'single-query-answer' ? 'style="display:none;"' : ''; ?>>
        <h4>Single Query Answer Fields</h4>
        <div class="sf-meta-field">
            <label>Exact Query Being Answered</label>
            <input type="text" name="_blog_query_text" value="<?php echo esc_attr($query_text); ?>" placeholder="e.g., Can Notion replace Jira?">
        </div>
        <div class="sf-meta-field">
            <label>Source Platform</label>
            <select name="_blog_query_source">
                <option value="">— Select —</option>
                <option value="chatgpt" <?php selected($query_source, 'chatgpt'); ?>>ChatGPT</option>
                <option value="perplexity" <?php selected($query_source, 'perplexity'); ?>>Perplexity</option>
                <option value="reddit" <?php selected($query_source, 'reddit'); ?>>Reddit</option>
                <option value="quora" <?php selected($query_source, 'quora'); ?>>Quora</option>
                <option value="paa" <?php selected($query_source, 'paa'); ?>>Google PAA</option>
                <option value="other" <?php selected($query_source, 'other'); ?>>Other</option>
            </select>
        </div>
    </div>

    <!-- News Reaction Fields -->
    <div class="sf-meta-section sf-format-fields" data-format="news-reaction" <?php echo $current_format !== 'news-reaction' ? 'style="display:none;"' : ''; ?>>
        <h4>News Reaction Fields</h4>
        <div class="sf-meta-field">
            <label>News Date</label>
            <input type="date" name="_blog_news_date" value="<?php echo esc_attr($news_date); ?>">
        </div>
        <div class="sf-meta-field">
            <label>Affected Tool</label>
            <input type="text" name="_blog_news_tool" value="<?php echo esc_attr($news_tool); ?>" placeholder="e.g., Slack">
        </div>
        <div class="sf-meta-field">
            <label>Change Type</label>
            <select name="_blog_news_change_type">
                <option value="">— Select —</option>
                <option value="pricing" <?php selected($news_type, 'pricing'); ?>>Pricing Change</option>
                <option value="feature" <?php selected($news_type, 'feature'); ?>>New Feature</option>
                <option value="acquisition" <?php selected($news_type, 'acquisition'); ?>>Acquisition</option>
                <option value="shutdown" <?php selected($news_type, 'shutdown'); ?>>Shutdown / EOL</option>
                <option value="other" <?php selected($news_type, 'other'); ?>>Other</option>
            </select>
        </div>
    </div>

    <!-- Admin JS to toggle format fields based on blog-format taxonomy selection -->
    <script>
    (function() {
        // Watch for changes to the blog-format taxonomy checkboxes/selector
        function toggleFormatFields() {
            var checkedTerms = document.querySelectorAll('#blog-formatchecklist input:checked, #taxonomy-blog-format input:checked');
            var activeFormat = '';
            if (checkedTerms.length > 0) {
                // Get the slug from the label text (WP stores it as term name in the label)
                var label = checkedTerms[0].closest('label') || checkedTerms[0].parentElement;
                if (label) {
                    // Try to get slug from value mapping — fallback to a data attribute approach
                    activeFormat = checkedTerms[0].getAttribute('data-slug') || '';
                }
            }

            // Hide all conditional sections
            document.querySelectorAll('.sf-format-fields').forEach(function(el) {
                el.style.display = 'none';
            });

            // Show the matching one
            if (activeFormat) {
                var target = document.querySelector('.sf-format-fields[data-format="' + activeFormat + '"]');
                if (target) target.style.display = 'block';
            }
        }

        // Run on load and on taxonomy change
        document.addEventListener('DOMContentLoaded', function() {
            var taxBox = document.getElementById('blog-formatchecklist') || document.getElementById('taxonomy-blog-format');
            if (taxBox) {
                taxBox.addEventListener('change', toggleFormatFields);
            }
            toggleFormatFields();
        });
    })();
    </script>
    <?php
}

function saasfinder_save_blog_format_meta($post_id) {
    if (!isset($_POST['saasfinder_blog_format_nonce']) || !wp_verify_nonce($_POST['saasfinder_blog_format_nonce'], 'saasfinder_blog_format_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_post_type($post_id) !== 'post') return;

    $text_fields = array(
        '_blog_linked_review_ids',
        '_blog_review_tool_name',
        '_blog_review_sources',
        '_blog_review_sentiment',
        '_blog_query_text',
        '_blog_query_source',
        '_blog_news_date',
        '_blog_news_tool',
        '_blog_news_change_type',
    );

    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Checkbox field
    $direct_answer = isset($_POST['_blog_direct_answer_enabled']) ? '1' : '0';
    update_post_meta($post_id, '_blog_direct_answer_enabled', $direct_answer);
}
add_action('save_post_post', 'saasfinder_save_blog_format_meta');
