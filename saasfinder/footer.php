</main><!-- #main-content -->

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-8);">
            <div>
                <strong style="font-size:var(--text-lg);display:block;margin-bottom:var(--space-4);"><?php bloginfo('name'); ?></strong>
                <p style="font-size:var(--text-sm);color:var(--bg-surface-alt);line-height:1.6;">
                    Find the right SaaS tool for your business. In-depth reviews, honest comparisons, and exclusive deals.
                </p>
            </div>

            <div>
                <strong style="display:block;margin-bottom:var(--space-4);">Quick Links</strong>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => '',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ));
                ?>
            </div>

            <div>
                <strong style="display:block;margin-bottom:var(--space-4);">Categories</strong>
                <ul style="list-style:none;font-size:var(--text-sm);">
                    <?php
                    $categories = get_terms(array(
                        'taxonomy'   => 'saas-category',
                        'hide_empty' => true,
                        'number'     => 8,
                    ));
                    if (!is_wp_error($categories)) :
                        foreach ($categories as $cat) :
                    ?>
                        <li style="margin-bottom:var(--space-2);">
                            <a href="<?php echo esc_url(home_url('/best/' . $cat->slug . '-software/')); ?>"><?php echo esc_html($cat->name); ?></a>
                        </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>

            <div>
                <strong style="display:block;margin-bottom:var(--space-4);">Legal</strong>
                <ul style="list-style:none;font-size:var(--text-sm);">
                    <li style="margin-bottom:var(--space-2);"><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a></li>
                    <li style="margin-bottom:var(--space-2);"><a href="<?php echo esc_url(home_url('/terms/')); ?>">Terms of Use</a></li>
                    <li style="margin-bottom:var(--space-2);"><a href="<?php echo esc_url(home_url('/affiliate-disclosure/')); ?>">Affiliate Disclosure</a></li>
                </ul>
            </div>
        </div>

        <div style="margin-top:var(--space-12);padding-top:var(--space-6);border-top:1px solid rgba(255,255,255,0.1);font-size:var(--text-sm);color:var(--bg-surface-alt);text-align:center;">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            <p style="margin-top:var(--space-2);">Affiliate Disclosure: Some links on this site are affiliate links. We may earn a commission at no extra cost to you.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
