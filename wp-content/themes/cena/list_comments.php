<?php

$GLOBALS['comment'] = $comment;
$add_below = '';

?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

	<div class="the-comment media">
		<?php if( !empty(get_avatar($comment, 92)) ) :?>
			<div class="avatar media-left">
				<?php echo get_avatar($comment, 92); ?>
			</div>
		<?php endif; ?>

		<div class="comment-box media-body">
			<div class="comment-author meta">
				<span><strong><?php echo get_comment_author_link() ?></strong></span>
				<span class="date"><?php printf(esc_html__('%1$s', 'cena'), get_comment_date()) ?></span>
				<?php edit_comment_link(esc_html__(' - Edit', 'cena'),'  ','') ?>
				<span class="pull-right"><?php comment_reply_link(array_merge( $args, array( 'reply_text' => esc_html__(' Reply', 'cena'), 'add_below' => 'comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?></span>
			</div>
			<div class="comment-text">
				<?php if ($comment->comment_approved == '0') : ?>
				<em><?php esc_html_e('Your comment is awaiting moderation.', 'cena') ?></em>
				<br />
				<?php endif; ?>
				<?php comment_text() ?>
			</div>
		</div>
	</div>