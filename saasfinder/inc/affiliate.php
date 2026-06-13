<?php
/**
 * Affiliate Link Handling
 *
 * - Auto-insert disclosure after first paragraph on reviews
 * - Ensure all external affiliate links get rel="nofollow sponsored" + target="_blank"
 * - Click tracking hooks (fires custom JS events for GTM)
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Auto-insert affiliate disclosure after first paragraph of every saas-review.
 */
function saasfinder_insert_affiliate_disclosure($content) {
    if (!is_singular('saas-review')) return $content;
    if (is_admin()) return $content;

    $post_id = get_the_ID();
    $disclosure = get_post_meta($post_id, '_saas_disclosure_text', true);

    if (!$disclosure) {
        $disclosure = 'We may earn a commission when you click links on this page. This doesn\'t affect our editorial independence.';
    }

    $disclosure_html = '<div class="affiliate-disclosure" role="note" aria-label="Affiliate Disclosure">'
        . '<strong>Disclosure:</strong> '
        . esc_html($disclosure)
        . '</div>';

    // Insert after first </p>
    $position = strpos($content, '</p>');
    if ($position !== false) {
        $content = substr_replace($content, '</p>' . $disclosure_html, $position, 4);
    } else {
        // No paragraph found — prepend
        $content = $disclosure_html . $content;
    }

    return $content;
}
add_filter('the_content', 'saasfinder_insert_affiliate_disclosure', 15);

/**
 * Process all outbound affiliate links in content to ensure proper rel attributes.
 * Scans for links with class "saas-cta" or data-track attribute.
 */
function saasfinder_process_affiliate_links($content) {
    if (is_admin()) return $content;

    // Match all anchor tags
    $content = preg_replace_callback(
        '/<a\s([^>]*?)>/i',
        function($matches) {
            $attrs = $matches[1];

            // Only process links that are affiliate CTAs (have data-track or saas-cta class)
            if (strpos($attrs, 'data-track') === false && strpos($attrs, 'saas-cta') === false) {
                return $matches[0];
            }

            // Ensure rel="nofollow sponsored"
            if (preg_match('/rel=["\']([^"\']*)["\']/', $attrs, $rel_match)) {
                $rel = $rel_match[1];
                if (strpos($rel, 'nofollow') === false) $rel .= ' nofollow';
                if (strpos($rel, 'sponsored') === false) $rel .= ' sponsored';
                $attrs = str_replace($rel_match[0], 'rel="' . trim($rel) . '"', $attrs);
            } else {
                $attrs .= ' rel="nofollow sponsored"';
            }

            // Ensure target="_blank"
            if (strpos($attrs, 'target=') === false) {
                $attrs .= ' target="_blank"';
            }

            return '<a ' . $attrs . '>';
        },
        $content
    );

    return $content;
}
add_filter('the_content', 'saasfinder_process_affiliate_links', 20);

/**
 * Output click tracking data attributes for GTM integration.
 * The actual event firing happens in main.js (vanilla JS).
 *
 * This filter ensures any affiliate URL rendered via wp_nav_menu or
 * other template functions also gets tracking attributes.
 */
function saasfinder_nav_menu_link_attributes($atts, $item, $args, $depth) {
    // Check if this is an external link (potential affiliate)
    if (isset($atts['href']) && strpos($atts['href'], home_url()) === false && strpos($atts['href'], 'http') === 0) {
        $atts['data-track'] = 'nav-affiliate-click';
        $atts['rel'] = 'nofollow sponsored';
        $atts['target'] = '_blank';
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'saasfinder_nav_menu_link_attributes', 10, 4);
