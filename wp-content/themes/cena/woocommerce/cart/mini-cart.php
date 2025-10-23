<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<div class="mini_cart_content">
	<div class="mini_cart_inner">
		<div class="mcart-border">
			<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>
				<ul class="cart_list product_list_widget <?php echo esc_attr($args['list_class']); ?>">
					<?php
          			do_action( 'woocommerce_before_mini_cart_contents' );
					foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item) {
						$_product     = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
						$product_id   = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

						if($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
							/**
							 * This filter is documented in woocommerce/templates/cart/cart.php.
							 *
							 * @since 2.1.0
							 */
							$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
							$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
							$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

							?>
							<li id="mcitem-<?php echo esc_attr($cart_item_key); ?> <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
								<a class="product-image" href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo str_replace(array('http:', 'https:'), '', $thumbnail); ?>
								</a>
								<div class="product-details">
									<?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
									    '<a role="button" href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">&times;</a>',
									    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'cena' ), wp_strip_all_tags( $product_name ) ) ),
									    esc_attr( $product_id ),
									    esc_attr( $cart_item_key ),
									    esc_attr( $_product->get_sku() ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'cena' ), wp_strip_all_tags( $product_name ) ) )
									), $cart_item_key ); ?>
									<a class="product-name" href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post($product_name); ?></a>
									
									<span class="quantity">
										<?php esc_html_e('Qty', 'cena'); ?>: <?php echo apply_filters('woocommerce_widget_cart_item_quantity',  sprintf('%s', $cart_item['quantity']) , $cart_item, $cart_item_key); ?>
									</span>
									
									<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
									<?php echo apply_filters('woocommerce_widget_cart_item_quantity',  sprintf('%s', $product_price) , $cart_item, $cart_item_key); ?>
								</div>
							</li>
							<?php
						}
					}

          do_action( 'woocommerce_mini_cart_contents' );
					?>
				</ul><!-- end product list -->
			<?php else: ?>
				<ul class="cart_empty <?php echo esc_attr($args['list_class']); ?>">
					<li><?php esc_html_e('You have no items in your shopping cart', 'cena'); ?></li>
					<li class="total"><?php esc_html_e('Subtotal', 'cena'); ?>: <?php echo WC()->cart->get_cart_subtotal(); ?></li>
				</ul>
			<?php endif; ?>

			<?php if(sizeof(WC()->cart->get_cart()) > 0) : ?>

				<p class="total">
					<?php
					/**
					 * Woocommerce_widget_shopping_cart_total hook.
					 *
					 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
					 */
					do_action( 'woocommerce_widget_shopping_cart_total' );
					?>
				</p>

				<?php do_action('woocommerce_widget_shopping_cart_before_buttons'); ?>

				<p class="buttons">
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button wc-forward view-cart"><?php esc_html_e('View Cart', 'cena'); ?></a>
					<a href="<?php echo esc_url( wc_get_checkout_url() );?>" class="button checkout wc-forward"><?php esc_html_e('Checkout', 'cena'); ?></a>	
				</p>

			<?php endif; ?>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
<?php do_action('woocommerce_after_mini_cart'); ?>