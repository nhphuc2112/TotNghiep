<?php

get_header();

$sidebar_configs = cena_tbay_get_blog_layout_configs();

cena_tbay_render_breadcrumbs();

if ( isset($sidebar_configs['left']) && !isset($sidebar_configs['right']) ) {
	$sidebar_configs['main']['class'] .= ' pull-right';
}

?>


<section id="main-container" class="main-content <?php echo apply_filters( 'cena_tbay_blog_content_class', 'container' ); ?> inner">
	<div class="row">
		
		<?php if ( isset($sidebar_configs['left']) && isset($sidebar_configs['right']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
			  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
		
		<div id="main-content" class="col-xs-12 <?php echo esc_attr($sidebar_configs['main']['class']); ?>">
			<div id="primary" class="content-area">
				<div id="content" class="site-content single-post" role="main">
					<?php
						// Start the Loop.
						while ( have_posts() ) : the_post();

							/*
							 * Include the post format-specific template for the content. If you want to
							 * use this in a child theme, then include a file called called content-___.php
							 * (where ___ is the post format) and that will be used instead.
							 */
							get_template_part( 'post-formats/content', get_post_format() );
							// Previous/next post navigation.
							the_post_navigation( array(
								'next_text' => '<span class="meta-nav" aria-hidden="true">' . esc_html__( 'Next', 'cena' ) . '</span> ' .
									'<span class="screen-reader-text">' . esc_html__( 'Next post:', 'cena' ) . '</span> ' .
									'<span class="post-title">%title</span>',
								'prev_text' => '<span class="meta-nav" aria-hidden="true">' . esc_html__( 'Previous', 'cena' ) . '</span> ' .
									'<span class="screen-reader-text">' . esc_html__( 'Previous post:', 'cena' ) . '</span> ' .
									'<span class="post-title">%title</span>',
							) );

							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;

							

							if ( cena_tbay_get_config('show_blog_releated', true) ): ?>
								<div class="related-posts">
									<?php get_template_part( 'page-templates/parts/posts-releated' ); ?>
								</div>
			                <?php
			                endif;
						// End the loop.
						endwhile;
					?>
				</div><!-- #content -->
			</div><!-- #primary -->
		</div>	
		
		<?php if ( isset($sidebar_configs['left']) && !isset($sidebar_configs['right']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
			  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
		
		<?php if ( isset($sidebar_configs['right']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['right']['class']) ;?>">
			  	<aside class="sidebar sidebar-right" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['right']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
	</div>	
</section>
<?php get_footer(); ?>