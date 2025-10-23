<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Cena
 * @since Cena 1.0
 */
/*

*Template Name: 404 Page
*/
get_header();
$sidebar_configs = cena_tbay_get_page_layout_configs();

cena_tbay_render_breadcrumbs();

?>
<section id="main-container" class=" container inner">
	<div class="clearfix">
		<?php if ( isset($sidebar_configs['left']) ) : ?>
			<div class="<?php echo esc_attr($sidebar_configs['left']['class']) ;?>">
			  	<aside class="sidebar sidebar-left" itemscope="itemscope" itemtype="http://schema.org/WPSideBar">
			   		<?php dynamic_sidebar( $sidebar_configs['left']['sidebar'] ); ?>
			  	</aside>
			</div>
		<?php endif; ?>
		<div id="main-content" class="main-page page-404 <?php echo esc_attr($sidebar_configs['main']['class']); ?>">

			<section class="error-404 not-found text-center clearfix">
			<div class="notfound-top">
				<i class="big-icon zmdi zmdi-mood-bad"></i>
				<h1><?php esc_html_e( 'Oops! I am embarrassed...', 'cena' ); ?></h1>
				<p class="sub"><?php esc_html_e( 'We can not seem to find the page you are looking for', 'cena' ); ?></p>
			</div>
				<div class="page-content notfound-bottom">
					<p class="sub-title"><?php esc_html_e( 'Perhaps your are here because The page has been moved or no longer exist', 'cena' ); ?></p>

					<?php get_search_form(); ?>
					<a class="backtohome btn btn-default" href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="icon-home icons"></i> <?php esc_html_e('back to home', 'cena'); ?></a>
				</div><!-- .page-content -->
			</section><!-- .error-404 -->

		</div><!-- .content-area -->
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