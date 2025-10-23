<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "site-content" div and all content after.
 *
 * @package WordPress
 * @subpackage Cena
 * @since Cena 1.0
 */

$footer = apply_filters( 'cena_tbay_get_footer_layout', 'default' );
$copyright 	= cena_tbay_get_config('copyright_text', '');

?>

	</div><!-- .site-content -->

	<?php if ( is_active_sidebar( 'footer' ) ) : ?>
		<div class="footer">
			<div class="container">
				<div class="row">
					<?php dynamic_sidebar( 'footer' ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<footer id="tbay-footer" class="tbay-footer" role="contentinfo">
		<?php if ( !empty($footer) ): ?>
			<?php cena_tbay_display_footer_builder($footer); ?>
		<?php else: ?>
			<div class="tbay-copyright">
				<div class="container">
					<div class="copyright-content">
						<div class="text-copyright text-center">
							<?php
								$allowed_html_array = array( 'a' => array('href' => array() ) );
								echo wp_kses(__('Copyright &copy; 2025 - DICHVUPC.COM. All Rights Reserved. <br/> Powered by <a href="https://facebook.com/nhfuc">nhfuc</a>', 'cena'), $allowed_html_array);
								
							?>
						</div>
					</div>
				</div>
			</div>
			
		<?php endif; ?>			
	</footer><!-- .site-footer -->

	<?php $tbay_header = apply_filters( 'cena_tbay_get_header_layout', cena_tbay_get_config('header_type') );
		if ( empty($tbay_header) ) {
			$tbay_header = 'v1';
		}
	?>
	
	<?php
	if ( cena_tbay_get_config('back_to_top') ) { ?>
		<div class="tbay-to-top <?php echo esc_attr($tbay_header); ?>">
			<?php get_template_part( 'page-templates/parts/search-modal' ); ?>
			
			<?php if ( cena_is_woocommerce_activated() ): ?>
			<!-- Setting -->
			<div class="tbay-cart top-cart hidden-xs">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="wc-forward mini-cart"><span class="text-skin cart-icon">
                <i class="icon-basket icons"></i></span></a>
			</div>
			<?php endif; ?>
			
			<?php if( class_exists( 'YITH_WCWL' ) ) { ?>
			<a class="text-skin wishlist-icon" href="<?php $wishlist_url = YITH_WCWL()->get_wishlist_url(); echo esc_url($wishlist_url); ?>"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="count_wishlist"><?php $wishlist_count = (YITH_WCWL_VERSION >= '4.0.0') ? yith_wcwl_count_products() : YITH_WCWL()->count_products(); echo esc_attr($wishlist_count); ?></span></a>
			<?php } ?>
			
			<a href="#" id="back-to-top">
				<i class="zmdi zmdi-long-arrow-up"></i>
				<p><?php esc_html_e('TOP', 'cena'); ?></p>
			</a>
		</div>
			
	<?php
	}
	?>

	<?php if( cena_tbay_get_config('category_fixed') ) {

		$_id = cena_tbay_random_key();

		?>

		<?php if ( has_nav_menu( 'category-menu-image' ) ): ?>
			<div class="tbay-category-fixed <?php echo esc_attr($tbay_header); ?>">
				<nav class="tbay-category-image" role="navigation">
					<?php   $args = array(
							'theme_location' => 'category-menu-image',
							'container_class' => 'collapse navbar-collapse',
							'menu_class' => 'nav navbar-nav megamenu',
							'fallback_cb' => '',
							'menu_id' => 'category-menu-image-'.$_id,
							'walker' => new Cena_Tbay_Nav_Menu()
						);
						wp_nav_menu($args);
					?>
				</nav>
			</div>
		<?php endif;?>

	<?php } ?>
	
	

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>
