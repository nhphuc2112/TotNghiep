<?php
extract( $args );
extract( $instance );
$title = apply_filters('widget_title', $instance['title']);

if ( $title ) {
    echo trim($before_title)  . trim( $title ) . trim($after_title);
}
$query = new WP_Query(array(
	'post_type'=>'post',
	'post__in' => $ids
));

if($query->have_posts()){

?>
	<div class="post-widget media-post-layout widget-content">
	<?php while ( $query->have_posts() ): $query->the_post(); ?>
		<article class="item-post media">
			<?php
				if(has_post_thumbnail()){
			?>
			<a href="<?php the_permalink(); ?>" class="image pull-left tbay-image-loaded">
				<?php 
					$post_thumbnail_id = get_post_thumbnail_id(get_the_ID());
					echo cena_tbay_get_attachment_image_loaded($post_thumbnail_id, 'widget');
				?>
			</a>
			<?php } ?>
			<div class="media-body">
				<h6 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h6>
				<p>
					<span class="entry-date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php the_time( 'M d, Y' ); ?></span>
					<span class="author"><i class="fa fa-user-o" aria-hidden="true"></i> <?php the_author_posts_link(); ?></span>
				</p>
			</div>
		</article>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
	</div>

<?php } ?>
