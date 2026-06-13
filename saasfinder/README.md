# SaasFinder WordPress Theme

A high-performance WordPress theme for **saasfinder.in** — a SaaS affiliate marketing blog optimized for organic search (Google) and AI search engines (ChatGPT, Perplexity, Google AI Overviews).

## Quick Start

1. Upload the `saasfinder/` folder to `wp-content/themes/`
2. Activate the theme in **Appearance → Themes**
3. Go to **Settings → Permalinks** and click "Save Changes" (flushes rewrite rules)
4. Create pages for programmatic SEO routes (see URL Patterns below)
5. Start creating SaaS Reviews

## Theme Architecture

```
saasfinder/
├── style.css                    # Theme declaration + full design system
├── functions.php                # Modular loader (no logic, just requires)
├── header.php                   # Semantic header + critical CSS inline
├── footer.php                   # Footer with category links
├── sidebar.php                  # Context-aware sidebar (review vs blog)
├── index.php                    # Fallback template
├── front-page.php               # Homepage (hero, featured, deals, categories)
├── single.php                   # Blog post router (dispatches to format template parts)
├── single-saas-review.php       # The money page template
├── single-saas-deal.php         # Deal page with countdown
├── archive-saas-review.php      # Filterable review grid (AJAX)
├── page-comparison.php          # /compare/tool1-vs-tool2/
├── page-alternatives.php        # /alternatives/tool-slug/
├── page-category-hub.php        # /best/category-software/
├── page-use-case.php            # /for/use-case/
├── page-best-of.php             # "Best X tools" roundup (Template Name)
├── inc/
│   ├── custom-post-types.php    # saas-review, saas-deal CPTs
│   ├── taxonomies.php           # saas-category, pricing-model, audience, blog-format
│   ├── meta-boxes.php           # All custom fields (native, no ACF)
│   ├── schema.php               # JSON-LD structured data output
│   ├── shortcodes.php           # 13 content utility shortcodes
│   ├── affiliate.php            # Disclosure, rel attributes, click tracking
│   ├── internal-links.php       # Auto-generated contextual links
│   ├── rewrites.php             # Custom URL patterns for programmatic pages
│   ├── sitemap.php              # Static sitemap.xml on save_post
│   └── ajax-handlers.php        # Archive filtering, search autocomplete
├── template-parts/
│   ├── blog/
│   │   ├── default.php          # Fallback blog layout
│   │   ├── user-reviews-roundup.php
│   │   ├── single-query-answer.php
│   │   ├── tutorial.php
│   │   └── news-reaction.php
│   └── components/
│       ├── review-card.php
│       ├── deal-card.php
│       └── blog-card.php
└── assets/
    ├── js/main.js               # Vanilla JS (no jQuery)
    ├── css/                     # Additional CSS if needed
    └── images/                  # Theme images
```

## URL Patterns

| Pattern | Template | Example |
|---------|----------|---------|
| `/review/{tool-slug}/` | single-saas-review.php | /review/notion/ |
| `/deals/{deal-slug}/` | single-saas-deal.php | /deals/notion-50-off/ |
| `/compare/{tool1}-vs-{tool2}/` | page-comparison.php | /compare/slack-vs-teams/ |
| `/alternatives/{tool-slug}/` | page-alternatives.php | /alternatives/notion/ |
| `/best/{category}-software/` | page-category-hub.php | /best/crm-software/ |
| `/for/{use-case}/` | page-use-case.php | /for/freelancers/ |
| `/answers/{slug}/` | single-query-answer post | /answers/can-notion-replace-jira/ |

## Custom Post Types

### saas-review (The Money Pages)
- **Meta fields:** Quick Answer, At a Glance data (description, best for, pricing, free plan, rating, verdict), Affiliate URL, CTA text, Disclosure text, Last Verified Date
- **Taxonomies:** saas-category, pricing-model, audience

### saas-deal
- **Meta fields:** Affiliate URL, Original Price, Discounted Price, Discount %, Expiry date, Terms, CTA text, Linked Review ID
- **Taxonomies:** saas-category

## Blog Formats (Extensibility System)

Blog posts use the `blog-format` taxonomy to determine which template part loads. This is the extensibility mechanism — no core theme changes needed for new formats.

### Creating a SaaS Review

1. Go to **SaaS Reviews → Add New**
2. Fill in the title (tool name)
3. In the **Review Details & Affiliate Info** meta box:
   - Write a 2-3 sentence Quick Answer (this is what AI engines cite)
   - Fill in the At a Glance fields (description, best for, pricing, free plan, rating, verdict)
   - Add your affiliate URL and CTA text
4. Set the **Content Freshness** date (last manual verification)
5. Assign taxonomies: saas-category, pricing-model, audience
6. Write the review body using shortcodes for structured elements

### Creating Blog Posts by Format

**User Reviews Roundup:**
1. Create a new post, assign blog-format: "User Reviews Roundup"
2. Fill in format-specific fields: Tool Name, Review Sources, Sentiment
3. Link to the saas-review via Linked Review IDs
4. Write content following the layout in the template

**Single Query Answer:**
1. Create a new post, assign blog-format: "Single Query Answer"
2. Fill in: Exact Query, Source Platform
3. Write a definitive 1-2 sentence answer as the excerpt (this becomes the direct-answer block)
4. Use `[direct_answer]...[/direct_answer]` shortcode for the answer block

**Tutorial:**
1. Create a new post, assign blog-format: "Tutorial / How-To"
2. Write the excerpt as "What you'll learn" summary
3. Use `[step number="1" title="..."]...[/step]` shortcodes for each step

**News Reaction:**
1. Create a new post, assign blog-format: "News Reaction"
2. Fill in: News Date, Affected Tool, Change Type
3. Use `[before_after before="$X" after="$Y" label="Plan"]` for price changes

### Adding a New Blog Format

1. Go to **Posts → Blog Formats** and add a new term (e.g., slug: `case-study`)
2. Create `/template-parts/blog/case-study.php` with your layout
3. (Optional) Add conditional meta fields in `inc/meta-boxes.php`
4. Done — no other core files need changes

## Shortcodes Reference

### Reviews & General
- `[saas_cta id="123"]` — Styled affiliate CTA button
- `[saas_comparison ids="12,34,56" highlight="12"]` — Comparison table
- `[pricing_table id="123"]` — Pricing from meta fields
- `[verdict_box id="123"]` — Score + verdict + CTA
- `[pros_cons pros="Pro 1|Pro 2" cons="Con 1|Con 2"]` — Two-column list
- `[tldr]...[/tldr]` — Highlighted TL;DR block
- `[last_updated]` — Shows post modified date

### Blog Formats
- `[direct_answer]...[/direct_answer]` — AI-quotable answer box
- `[user_sentiment positive]` — Visual sentiment indicator
- `[review_source name="G2" rating="4.5" count="1200" url="..."]` — Platform badge
- `[step number="1" title="Do this"]...[/step]` — HowTo step with schema
- `[before_after before="$15" after="$25" label="Starter"]` — Price change row
- `[related_review id="123"]` — Inline review card

## Recommended Plugins

| Plugin | Purpose |
|--------|---------|
| Yoast SEO or Rank Math | Advanced SEO (schema validation, XML sitemaps override, meta) |
| WP Rocket | Caching, minification, CDN integration |
| ShortPixel or Imagify | Image optimization + WebP conversion |
| Redirection | Manage 301 redirects for URL changes |
| WP Mail SMTP | Reliable transactional email |
| UpdraftPlus | Backups |

**Note:** The theme includes its own sitemap, canonical URLs, and meta descriptions. If you install Yoast/RankMath, those features will automatically defer to the plugin (detected via `WPSEO_VERSION` / `RankMath` class checks).

## Performance Targets

- **LCP:** < 1.5s (critical CSS inlined, font preloaded, no render-blocking JS)
- **CLS:** < 0.05 (explicit image dimensions, no layout shifts)
- **INP:** < 150ms (vanilla JS, no jQuery, deferred loading)

## AI Search Optimization (AEO)

Every page includes elements specifically designed for AI engine citation:
- **Quick Answer blocks** — 2-3 sentence plain-language summaries
- **Real HTML tables** — not styled divs (AI parsers prefer tables)
- **FAQPage schema** — targets People Also Ask and AI citation
- **Definitive opening paragraphs** — on hub pages, these are the sentences Perplexity quotes
- **Semantic HTML** — content reads well when stripped of all CSS

## Click Tracking

All affiliate links fire a `saasfinder:affiliate_click` custom event and push to `window.dataLayer` for GTM. Event data includes:
- `click_type` (verdict-cta, sidebar-cta, comparison-cta, deal-cta, etc.)
- `tool_name`
- `destination` URL
- `page_url`
