<?php
/**
 * Comments Template
 *
 * Minimal, clean comment display for blog posts.
 * Comments are disabled on CPTs (handled in functions.php).
 *
 * @package SaasFinder
 */

// Prevent direct access
if (post_password_required()) return;
?>

<section id="comments" class="comments-section" style="margin-top:var(--space-12);padding-top:var(--space-8);border-top:1px solid var(--border-default);">

    <?php if (have_comments()) : ?>
        <h2 class="comments-title" style="margin-bottom:var(--space-6);">
            <?php
            $count = get_comments_number();
            printf(
                _n('%s Comment', '%s Comments', $count, 'saasfinder'),
                number_format_i18n($count)
            );
            ?>
        </h2>

        <ol class="comment-list" style="list-style:none;">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 40,
                'callback'    => 'saasfinder_comment_callback',
            ));
            ?>
        </ol>

        <?php if (get_comment_pages_count() > 1) : ?>
            <nav class="comment-navigation" style="margin-top:var(--space-6);display:flex;justify-content:space-between;">
                <div><?php previous_comments_link('← Older Comments'); ?></div>
                <div><?php next_comments_link('Newer Comments →'); ?></div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (comments_open()) : ?>
        <?php
        comment_form(array(
            'title_reply'          => '<h3 style="margin-bottom:var(--space-4);">Leave a Comment</h3>',
            'title_reply_before'   => '',
            'title_reply_after'    => '',
            'comment_notes_before' => '<p class="text-sm text-muted" style="margin-bottom:var(--space-4);">Your email address will not be published. Required fields are marked *</p>',
            'label_submit'         => 'Post Comment',
            'class_submit'         => 'btn btn--secondary',
            'comment_field'        => '<div class="sf-meta-field"><label for="comment">Comment *</label><textarea id="comment" name="comment" rows="5" required style="width:100%;padding:var(--space-3);border:1px solid var(--border-default);border-radius:var(--border-radius-sm);font-family:var(--font-body);"></textarea></div>',
        ));
        ?>
    <?php elseif (get_comments_number()) : ?>
        <p class="text-muted" style="margin-top:var(--space-6);">Comments are closed.</p>
    <?php endif; ?>

</section>

<?php
/**
 * Custom comment callback for cleaner markup.
 */
function saasfinder_comment_callback($comment, $args, $depth) {
    $tag = ($args['style'] === 'div') ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class('', $comment); ?> style="margin-bottom:var(--space-6);padding:var(--space-4);background:var(--bg-surface);border-radius:var(--border-radius);<?php echo $depth > 1 ? 'margin-left:var(--space-6);' : ''; ?>">

        <div style="display:flex;align-items:center;gap:var(--space-3);margin-bottom:var(--space-3);">
            <?php echo get_avatar($comment, 40, '', '', array('style' => 'border-radius:50%;')); ?>
            <div>
                <strong style="display:block;"><?php comment_author(); ?></strong>
                <time class="text-sm text-muted" datetime="<?php comment_date('c'); ?>">
                    <?php comment_date('F j, Y'); ?> at <?php comment_time(); ?>
                </time>
            </div>
        </div>

        <?php if ($comment->comment_approved === '0') : ?>
            <p class="text-sm" style="color:var(--color-warning);margin-bottom:var(--space-2);">Your comment is awaiting moderation.</p>
        <?php endif; ?>

        <div class="comment-content" style="line-height:var(--leading-relaxed);">
            <?php comment_text(); ?>
        </div>

        <div style="margin-top:var(--space-3);">
            <?php
            comment_reply_link(array_merge($args, array(
                'depth'     => $depth,
                'max_depth' => $args['max_depth'],
                'before'    => '<span class="text-sm">',
                'after'     => '</span>',
            )));
            ?>
        </div>

    </<?php echo $tag; ?>>
    <?php
}
?>
