<?php $thumbsize = isset($thumbsize) ? $thumbsize : 'medium';?>
<?php
  $post_category = "";
  $categories = get_the_category();
  $separator = ' | ';
  $output = '';
  if($categories){
    foreach($categories as $category) {
      $output .= '<a href="'.esc_url( get_category_link( $category->term_id ) ).'" title="' . esc_attr( sprintf( esc_html__( "View all posts in %s", 'cena' ), $category->name ) ) . '">'.$category->cat_name.'</a>'.$separator;
    }
  $post_category = trim($output, $separator);
  }      
?>
<div  class="post-list clearfix">
	  <article class="post">
		  <?php
			if ( has_post_thumbnail() ) {
				?>
				<figure class="entry-thumb <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
				<a href="<?php the_permalink(); ?>" class="entry-image tbay-image-loaded">
					<?php
						if ( cena_vc_is_activated() ) {
	                        $thumbnail_id = get_post_thumbnail_id(get_the_ID());
	                        echo cena_tbay_get_attachment_image_loaded($thumbnail_id, $thumbsize);
						} else {
							the_post_thumbnail();
						}
					?>
				</a>
				
			</figure>
				<?php
			}
			?>
		
		<div class="entry-content">
		  <div class="entry-meta">
				<?php
					if (get_the_title()) {
					?>
						<h4 class="entry-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h4>
					<?php
				}
				?>
			</div>
			<div class="meta-info" style="color: black">
				<span class="entry-date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo cena_time_link(); ?></span>
				<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
				<span class="comments-link"><i class="fa fa-comments"></i> <?php comments_popup_link( esc_html__( '0 comment', 'cena' ), esc_html__( '1 Comment', 'cena' ), esc_html__( '% Comments', 'cena' ) ); ?></span>
				<?php endif; ?>
				<span class="author"><i class="fa fa-user-o" aria-hidden="true"></i> <?php the_author_posts_link(); ?></span>
			</div>
		   <div class="entry-top">
				<?php
					if (! has_excerpt()) {
						echo "";
					} else {
						?>
							<div class="entry-description"><?php echo cena_tbay_substring( get_the_excerpt(), 25, '[...]' ); ?> <a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Read More', 'cena' ); ?>"><i class="icon-arrow-right-circle icons"></i></a></div>
						<?php
					}
				?>
			</div>
		</div>
	</article>
</div>
