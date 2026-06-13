<?php
/**
 * Schema.org Structured Data Output (JSON-LD)
 *
 * Outputs appropriate schema for each content type:
 * - saas-review: SoftwareApplication + Review + AggregateRating
 * - saas-deal: Product + Offer
 * - Blog (tutorial): HowTo
 * - Blog (FAQ): FAQPage
 * - All pages: BreadcrumbList
 *
 * @package SaasFinder
 */

defined('ABSPATH') || exit;

/**
 * Output JSON-LD schema in wp_head based on current page context.
 */
function saasfinder_output_schema() {
    if (is_admin()) return;

    $schemas = array();

    // Breadcrumbs — on every page
    $schemas[] = saasfinder_schema_breadcrumbs();

    // SaaS Review pages
    if (is_singular('saas-review')) {
        $schemas[] = saasfinder_schema_review();
    }

    // SaaS Deal pages
    if (is_singular('saas-deal')) {
        $schemas[] = saasfinder_schema_deal();
    }

    // Blog posts — check format for appropriate schema
    if (is_singular('post')) {
        $terms = wp_get_post_terms(get_the_ID(), 'blog-format', array('fields' => 'slugs'));
        $format = !empty($terms) ? $terms[0] : '';

        switch ($format) {
            case 'tutorial':
                $schemas[] = saasfinder_schema_howto();
                break;
            case 'single-query-answer':
                $schemas[] = saasfinder_schema_faq_page();
                break;
            case 'user-reviews-roundup':
                $schemas[] = saasfinder_schema_aggregate_rating();
                break;
        }

        // FAQPage schema for any post with FAQ section (check for details/summary in content)
        if ($format !== 'single-query-answer') {
            $content = get_the_content();
            if (strpos($content, '<details') !== false || strpos($content, '[faq') !== false) {
                // FAQ schema will be handled by the FAQ shortcode/template part
            }
        }
    }

    // Homepage — WebSite + Organization
    if (is_front_page()) {
        $schemas[] = saasfinder_schema_website();
        $schemas[] = saasfinder_schema_organization();
    }

    // Comparison & Alternatives pages — ItemList + FAQ
    if (is_page_template('page-comparison.php') || get_query_var('saas_compare')) {
        $schemas[] = saasfinder_schema_comparison_faq();
    }
    if (is_page_template('page-alternatives.php') || get_query_var('saas_alternatives')) {
        $schemas[] = saasfinder_schema_alternatives_itemlist();
    }

    // Category Hub — ItemList
    if (get_query_var('saas_category_hub')) {
        $schemas[] = saasfinder_schema_category_itemlist();
    }

    // Output all collected schemas
    foreach ($schemas as $schema) {
        if (!empty($schema)) {
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo "\n</script>\n";
        }
    }
}
add_action('wp_head', 'saasfinder_output_schema', 20);

/**
 * BreadcrumbList schema — contextual based on content type.
 */
function saasfinder_schema_breadcrumbs() {
    $items = array();
    $position = 1;

    // Home is always first
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => 'Home',
        'item'     => home_url('/'),
    );

    if (is_singular('saas-review')) {
        // Home → Category Hub → Review
        $categories = wp_get_post_terms(get_the_ID(), 'saas-category');
        if (!empty($categories)) {
            $cat = $categories[0];
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cat->name,
                'item'     => get_term_link($cat),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    } elseif (is_singular('post')) {
        // Home → Blog → Post
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Blog',
            'item'     => get_permalink(get_option('page_for_posts')),
        );
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    } elseif (is_singular('saas-deal')) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Deals',
            'item'     => get_post_type_archive_link('saas-deal'),
        );
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
    }

    if (count($items) <= 1) return array(); // Don't output breadcrumbs for homepage

    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );
}

/**
 * SaaS Review schema — SoftwareApplication + Review.
 */
function saasfinder_schema_review() {
    $post_id = get_the_ID();
    $rating = get_post_meta($post_id, '_saas_tool_rating', true);
    $description = get_post_meta($post_id, '_saas_tool_description', true);
    $pricing = get_post_meta($post_id, '_saas_tool_pricing', true);
    $url = get_post_meta($post_id, '_saas_tool_website', true);
    $verdict = get_post_meta($post_id, '_saas_tool_verdict', true);
    $free_plan = get_post_meta($post_id, '_saas_tool_free_plan', true);

    $schema = array(
        '@context'           => 'https://schema.org',
        '@type'              => 'SoftwareApplication',
        'name'               => get_the_title(),
        'description'        => $description ?: get_the_excerpt(),
        'applicationCategory'=> 'BusinessApplication',
        'operatingSystem'    => 'Web',
    );

    if ($url) {
        $schema['url'] = $url;
    }

    if ($pricing) {
        $schema['offers'] = array(
            '@type'         => 'Offer',
            'price'         => preg_replace('/[^0-9.]/', '', $pricing),
            'priceCurrency' => 'USD',
        );
        if ($free_plan === 'yes') {
            $schema['offers']['price'] = '0';
        }
    }

    if ($rating) {
        $schema['review'] = array(
            '@type'        => 'Review',
            'reviewRating' => array(
                '@type'       => 'Rating',
                'ratingValue' => $rating,
                'bestRating'  => '10',
                'worstRating' => '1',
            ),
            'author' => array(
                '@type' => 'Organization',
                'name'  => 'SaasFinder',
                'url'   => home_url('/'),
            ),
            'reviewBody' => $verdict ?: '',
            'datePublished' => get_the_date('c'),
            'dateModified'  => get_the_modified_date('c'),
        );

        $schema['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => $rating,
            'bestRating'  => '10',
            'reviewCount' => '1',
        );
    }

    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
    }

    return $schema;
}

/**
 * SaaS Deal schema — Product + Offer.
 */
function saasfinder_schema_deal() {
    $post_id = get_the_ID();
    $original = get_post_meta($post_id, '_deal_original_price', true);
    $discounted = get_post_meta($post_id, '_deal_discounted_price', true);
    $expires = get_post_meta($post_id, '_deal_expires', true);

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => get_the_title(),
        'description' => get_the_excerpt(),
        'offers'      => array(
            '@type'         => 'Offer',
            'price'         => preg_replace('/[^0-9.]/', '', $discounted),
            'priceCurrency' => 'USD',
            'availability'  => 'https://schema.org/InStock',
        ),
    );

    if ($expires) {
        $schema['offers']['priceValidUntil'] = date('Y-m-d', strtotime($expires));
    }

    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
    }

    return $schema;
}

/**
 * HowTo schema for tutorial blog format.
 */
function saasfinder_schema_howto() {
    $post_id = get_the_ID();

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'HowTo',
        'name'        => get_the_title(),
        'description' => get_the_excerpt(),
        'datePublished' => get_the_date('c'),
        'dateModified'  => get_the_modified_date('c'),
    );

    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($post_id, 'full');
    }

    // Steps will be injected via [step] shortcode output (itemscope/itemprop)
    // This provides the wrapper schema; individual steps add themselves via microdata

    return $schema;
}

/**
 * FAQPage schema for single-query-answer format.
 */
function saasfinder_schema_faq_page() {
    $post_id = get_the_ID();
    $query = get_post_meta($post_id, '_blog_query_text', true);

    if (!$query) {
        $query = get_the_title();
    }

    $schema = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array(
            array(
                '@type' => 'Question',
                'name'  => $query,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags(get_the_excerpt()),
                ),
            ),
        ),
    );

    return $schema;
}

/**
 * AggregateRating schema for user reviews roundup.
 */
function saasfinder_schema_aggregate_rating() {
    $post_id = get_the_ID();
    $tool_name = get_post_meta($post_id, '_blog_review_tool_name', true);

    if (!$tool_name) return array();

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'SoftwareApplication',
        'name'     => $tool_name,
        'review'   => array(
            '@type'  => 'Review',
            'author' => array(
                '@type' => 'Organization',
                'name'  => 'SaasFinder',
            ),
            'datePublished' => get_the_date('c'),
        ),
    );

    return $schema;
}

/**
 * Output canonical URL on every page to prevent duplicate content.
 */
function saasfinder_output_canonical() {
    if (is_admin()) return;

    // Don't output if Yoast or other SEO plugin is handling it
    if (defined('WPSEO_VERSION') || class_exists('RankMath')) return;

    $canonical = '';

    if (is_singular()) {
        $canonical = get_permalink();
    } elseif (is_home()) {
        $canonical = get_permalink(get_option('page_for_posts'));
    } elseif (is_front_page()) {
        $canonical = home_url('/');
    } elseif (is_tax() || is_category() || is_tag()) {
        $canonical = get_term_link(get_queried_object());
    } elseif (is_post_type_archive()) {
        $canonical = get_post_type_archive_link(get_queried_object()->name);
    }

    if ($canonical) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }
}
add_action('wp_head', 'saasfinder_output_canonical', 5);

/**
 * WebSite schema with SearchAction — enables Google sitelinks search box.
 */
function saasfinder_schema_website() {
    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => 'SaasFinder',
        'url'             => home_url('/'),
        'description'     => 'In-depth SaaS reviews, comparisons, and deals for businesses.',
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => array(
                '@type'        => 'EntryPoint',
                'urlTemplate'  => home_url('/?s={search_term_string}'),
            ),
            'query-input' => 'required name=search_term_string',
        ),
    );
}

/**
 * Organization schema — establishes site authority.
 */
function saasfinder_schema_organization() {
    return array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'SaasFinder',
        'url'      => home_url('/'),
        'logo'     => SAASFINDER_URI . '/assets/images/logo.svg',
        'sameAs'   => array(), // Add social profiles when available
    );
}

/**
 * FAQ schema for comparison pages (auto-generated questions).
 */
function saasfinder_schema_comparison_faq() {
    $compare_slug = get_query_var('saas_compare');
    if (!$compare_slug) return array();

    $slugs = explode('-vs-', $compare_slug);
    if (count($slugs) < 2) return array();

    $tools = array();
    foreach ($slugs as $slug) {
        $posts = get_posts(array(
            'post_type'   => 'saas-review',
            'name'        => $slug,
            'numberposts' => 1,
        ));
        if (!empty($posts)) {
            $tools[] = get_the_title($posts[0]->ID);
        }
    }

    if (count($tools) < 2) return array();

    $t1 = $tools[0];
    $t2 = $tools[1];

    $questions = array(
        array(
            'q' => "Is {$t1} better than {$t2}?",
            'a' => "It depends on your use case. Compare their features, pricing, and ratings above to decide which fits your needs.",
        ),
        array(
            'q' => "Which is cheaper, {$t1} or {$t2}?",
            'a' => "See the pricing comparison table above for the latest pricing of both tools.",
        ),
        array(
            'q' => "Can I switch from {$t1} to {$t2}?",
            'a' => "Most SaaS tools offer data export options. Check each tool's migration and import capabilities.",
        ),
    );

    $entities = array();
    foreach ($questions as $qa) {
        $entities[] = array(
            '@type' => 'Question',
            'name'  => $qa['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $qa['a'],
            ),
        );
    }

    return array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    );
}

/**
 * ItemList schema for alternatives pages — helps with rich results.
 */
function saasfinder_schema_alternatives_itemlist() {
    $alt_slug = get_query_var('saas_alternatives');
    if (!$alt_slug) return array();

    // Find the original tool
    $original = get_posts(array(
        'post_type'   => 'saas-review',
        'name'        => $alt_slug,
        'numberposts' => 1,
    ));
    if (empty($original)) return array();

    $original_post = $original[0];
    $categories = wp_get_post_terms($original_post->ID, 'saas-category', array('fields' => 'ids'));
    if (empty($categories)) return array();

    $alternatives = get_posts(array(
        'post_type'      => 'saas-review',
        'posts_per_page' => 10,
        'post__not_in'   => array($original_post->ID),
        'tax_query'      => array(array(
            'taxonomy' => 'saas-category',
            'terms'    => $categories,
        )),
        'meta_key'       => '_saas_tool_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ));

    if (empty($alternatives)) return array();

    $items = array();
    $position = 1;
    foreach ($alternatives as $alt) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title($alt->ID),
            'url'      => get_permalink($alt->ID),
        );
    }

    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Best Alternatives to ' . get_the_title($original_post->ID),
        'itemListElement' => $items,
    );
}

/**
 * ItemList schema for category hub pages.
 */
function saasfinder_schema_category_itemlist() {
    $hub_slug = get_query_var('saas_category_hub');
    if (!$hub_slug) return array();

    $term = get_term_by('slug', $hub_slug, 'saas-category');
    if (!$term) return array();

    $tools = get_posts(array(
        'post_type'      => 'saas-review',
        'posts_per_page' => 15,
        'tax_query'      => array(array(
            'taxonomy' => 'saas-category',
            'terms'    => array($term->term_id),
        )),
        'meta_key'       => '_saas_tool_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ));

    if (empty($tools)) return array();

    $items = array();
    $position = 1;
    foreach ($tools as $tool) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title($tool->ID),
            'url'      => get_permalink($tool->ID),
        );
    }

    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Best ' . $term->name . ' Software',
        'itemListElement' => $items,
    );
}

/**
 * Output meta description — reads like a direct answer, not marketing copy.
 */
function saasfinder_output_meta_description() {
    if (is_admin()) return;
    if (defined('WPSEO_VERSION') || class_exists('RankMath')) return;

    $description = '';

    if (is_singular('saas-review')) {
        $post_id = get_the_ID();
        $tool_desc = get_post_meta($post_id, '_saas_tool_description', true);
        $rating = get_post_meta($post_id, '_saas_tool_rating', true);
        $best_for = get_post_meta($post_id, '_saas_tool_best_for', true);
        $pricing = get_post_meta($post_id, '_saas_tool_pricing', true);

        $description = get_the_title() . ' is ' . strtolower($tool_desc);
        if ($rating) $description .= ', rated ' . $rating . '/10';
        if ($best_for) $description .= ', best for ' . strtolower($best_for);
        if ($pricing) $description .= '. Pricing starts at ' . $pricing;
        $description .= '.';
    } elseif (is_singular('post')) {
        $description = get_the_excerpt();
    } elseif (is_singular()) {
        $description = get_the_excerpt();
    } elseif (is_front_page()) {
        $description = 'Find the right SaaS tool for your business. In-depth reviews, pricing comparisons, and deals on B2B and B2C software.';
    }

    if ($description) {
        $description = wp_strip_all_tags($description);
        $description = substr($description, 0, 160);
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
}
add_action('wp_head', 'saasfinder_output_meta_description', 5);
