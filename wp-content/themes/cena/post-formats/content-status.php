<?php
/**
 *
 * The default template for displaying content
 * @since 1.0
 * @version 1.2.0
 *
 */
$thumbsize = isset($thumbsize) ? $thumbsize : 'full';
?>
<!-- /post-standard -->
<?php if ( ! is_single() ) : ?>
<div  class="post-list clearfix">
<?php endif; ?>
  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php if ( is_single() ) : ?>
	<div class="entry-single">
<?php endif; ?>
      <?php
        if ( has_post_thumbnail() ) {
            ?>
            <figure class="entry-thumb <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
                <?php cena_tbay_post_thumbnail(); ?>
                
            </figure>
            <?php
        }
        ?>
        <?php
			if ( is_single() ) : ?>
	        	<div class="entry-header">
	        		<div class="entry-meta">
			            <?php
			                if (get_the_title()) {
			                ?>
			                    <h1 class="entry-title">
			                       <?php the_title(); ?>
			                    </h1>
			                <?php
			            	}
			            ?>
			        </div>
					<div class="meta-info">
						<span class="entry-date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo cena_time_link(); ?></span>
						<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
						<span class="comments-link"><i class="fa fa-comments"></i> <?php comments_popup_link( esc_html__( '0 comment', 'cena' ), esc_html__( '1 Comment', 'cena' ), esc_html__( '% Comments', 'cena' ) ); ?></span>
						<?php endif; ?>
						<span class="author"><i class="fa fa-user-o" aria-hidden="true"></i> <?php the_author_posts_link(); ?></span>
					</div>

				</div>
				<div class="post-excerpt entry-content"><?php the_content( esc_html__( 'Read More', 'cena' ) ); ?></div><!-- /entry-content -->
				
				<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'cena' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'cena' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				) );
				?>
				
				<?php cena_tbay_post_tags(); ?>
				<?php cena_tbay_post_share_box(); ?>
			
		<?php endif; ?>
    <?php if ( ! is_single() ) : ?>
	
	<?php
	 if ( has_post_thumbnail() ) {
	  ?>
	  <figure class="entry-thumb <?php echo  (!has_post_thumbnail() ? 'no-thumb' : ''); ?>">
	   <?php cena_tbay_post_thumbnail(); ?>
	  </figure>
	  <?php
	 }
	 ?>
	
   <div class="entry-content <?php echo ( !has_post_thumbnail() ) ? 'no-thumb' : ''; ?>">
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
        
         <div class="meta-info">
			<span class="entry-date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo cena_time_link(); ?></span>
			<?php if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
			<span class="comments-link"><i class="fa fa-comments"></i> <?php comments_popup_link( esc_html__( '0 comment', 'cena' ), esc_html__( '1 Comment', 'cena' ), esc_html__( '% Comments', 'cena' ) ); ?></span>
			<?php endif; ?>
			<span class="author"><i class="fa fa-user-o" aria-hidden="true"></i> <?php the_author_posts_link(); ?></span>
		</div>
		
		<div class="entry-top">
		<?php
			if ( has_excerpt()) {
				echo the_excerpt();
			} else {
				?>
					<div class="entry-description"><?php echo cena_tbay_substring( get_the_excerpt(), 25, '[...]' ); ?> <a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'Read More', 'cena' ); ?>"><i class="icon-arrow-right-circle icons"></i></a></div>
				<?php
			}
		?>
		</div>
		
    </div>
    <?php endif; ?>
    <?php if ( is_single() ) : ?>
</div>
<?php endif; ?>
</article>

<?php if ( ! is_single() ) : ?>
</div>
<?php endif; ?>