<?php
namespace Wpexperts\CurrencySwitcherForWoocommerce;

use Automattic\WooCommerce\Utilities\OrderUtil;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * This class defines wccs logic code for the plugin.
 */

if ( ! class_exists( 'WCCS' ) ) {

	class WCCS {

		// Hold the class instance.
		private static $_instance = null;  

		private $default_currency      = null;
		private $default_currency_flag = null;
		private $currency              = null;
		private $currency_info         = array();
		private $currencies            = array();
		private $storage               = null;
		private $is_fixed              = false;
		private $selected_zone_id      = false;
		private $filter_counter        = null;
		public $wccs_priorities        = array();


		public function __construct() {      
		

			// initialize properties
			/**
			* Filter.
			* 
			* @since 1.9.1
			*/
			$this->default_currency      = apply_filters( 'wccs_default_currency', get_woocommerce_currency() );
			$this->default_currency_flag =  get_mulltisite_or_site_option('wccs_default_currency_flag', true);
			$this->currencies            =  get_mulltisite_or_site_option('wccs_currencies', array());
			$this->storage               = new Storage(  get_mulltisite_or_site_option('wccs_currency_storage', 'transient'));
			$this->filter_counter        = 1;

			$currency   = $this->storage->get_val( 'wccs_current_currency' );

			$currencies = $this->wccs_get_currencies();         
			if ( isset( $currencies[ $currency ] ) ) {
				$this->currency      = $currency;
				$this->currency_info = $currencies[ $currency ];
				$this->currency_info['symbol'] = get_woocommerce_currency_symbol( $currency );
			}

			$this->wccs_priorities = array(
				'woocommerce_product_get_price'         => 99,
				'woocommerce_variation_prices_price'    => 99,
			);

			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			$this->wccs_priorities = apply_filters( 'wccs_hook_priorities', $this->wccs_priorities, 10 );           

			// add admin style and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'wccs_admin_enqueue_assets' ) );

			// add frontend style and scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'wccs_front_enqueue_assets' ) );

			// change currency
			add_filter( 'woocommerce_currency', array( $this, 'wccs_woocommerce_currency' ), 9999 );

			// change currency symbol
			add_filter( 'woocommerce_currency_symbol', array( $this, 'wccs_woocommerce_currency_symbol' ), 9999, 2 );

			// for shop page
			add_filter( 'woocommerce_currency_symbol', array( $this, 'wccs_order_page_currency' ), 999, 2 );

			// format price based on currency
			add_filter( 'woocommerce_price_format', array( $this, 'wccs_price_format' ), 999, 2 );

			// add decimals based on currency
			add_filter( 'wc_price_args', array( $this, 'wccs_price_args' ), 999 );

			// We use tier pricing filter to back price to it orignal value.
			add_filter( 'tiered_pricing_table/cart/product_cart_price', array( $this, 'wccs_tier_pricing' ), 99, 2 );

			// override prices for subscriptions schemes all product subscription somewherewarm
			add_filter( 'wcsatt_single_product_subscription_option_data', array( $this, 'wccs_wcsatt_subscription_scheme_prices' ), 99, 3 );            

			// woocommerce booking
			// add_filter('woocommerce_bookings_calculated_booking_cost', array($this, 'woocommerce_booking_product_price'), 999, 3);
			add_filter( 'woocommerce_bookings_calculated_booking_cost_success_output', array( $this, 'woocommerce_booking_product_price_string' ), 999, 3 );
			add_filter( 'woocommerce_get_price_html', array( $this, 'woocommerce_booking_product_price_html' ), 9, 2 );

			// woocommerce subscription products
			add_filter( 'woocommerce_subscriptions_product_price', array( $this, 'wccs_subscription_product_price' ), 99, 2 );
			add_filter( 'woocommerce_subscriptions_product_sign_up_fee', array( $this, 'wccs_subscription_product_price_signup' ), 99, 2 );

			// override simple product price
			add_filter( 'woocommerce_product_get_price', array( $this, 'wccs_product_get_price' ), $this->wccs_priorities['woocommerce_product_get_price'], 2 );
			add_filter( 'woocommerce_product_get_sale_price', array( $this, 'product_get_sale_price' ), $this->wccs_priorities['woocommerce_product_get_price'], 2 );
			add_filter( 'woocommerce_product_get_regular_price', array( $this, 'wccs_get_regular_price' ), $this->wccs_priorities['woocommerce_product_get_price'], 2 );

			// Single Variations Hook
			add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'wccs_custom_variation_get_regular_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 2 );
			add_filter('woocommerce_product_variation_get_sale_price', array( $this, 'wccs_custom_variation_get_sale_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 2);
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'wccs_custom_variation_get_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 2 );

			// Variable Product Hook (price range)          
			add_filter( 'woocommerce_variation_prices_price', array( $this, 'wccs_custom_variable_get_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 3 );
			add_filter( 'woocommerce_variation_prices_regular_price', array( $this, 'wccs_custom_variable_get_regular_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 3 );
			add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'wccs_custom_variable_get_sale_price' ), $this->wccs_priorities['woocommerce_variation_prices_price'], 3 );

			// Handling price caching
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'wccs_add_user_to_variation_prices_hash' ), 999, 1 );

			// detect currency
			if ( ! class_exists( 'WC_Deposits' ) ) {
				add_action( 'template_redirect', array( $this, 'wccs_detect_currency' ) );
			} else {
				add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'wccs_detect_currency' ), 9 );
			}

			// add switcher shortcode
			add_shortcode( 'wcc_switcher', array( $this, 'wcc_switcher_shortcode_callback' ) );

			// add rates shortcode
			add_shortcode( 'wcc_rates', array( $this, 'wcc_rates_shortcode_callback' ) );

			// register widgets
			add_action( 'widgets_init', array( $this, 'wccs_widgets' ) );

			add_filter( 'wp_get_nav_menu_items', array( $this, 'wccs_get_nav_menu_items_filter' ), 999, 3 );

			// add sticky switcher
			add_action( 'wp_enqueue_scripts', array( $this, 'wccs_add_sticky_callback' ) );

			// override shipping price
			add_filter( 'woocommerce_package_rates', array( $this, 'wccs_change_shipping_rates_cost' ), 10, 2 );
			
			// woocommerce product addons support
			add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'wccs_woo_product_addons' ), 50, 2 );
			
			// change currency on checkout before creating order.
			// This will work according to backend value set by user whether to pay in user currency or default currency
			add_action( 'woocommerce_checkout_create_order', array( $this, 'wccs_change_order_currency' ), 999, 1 );                        

			// change currency on checkout before creating order via checkout block.
			// This will work according to backend value set by user whether to pay in user currency or default currency
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'wccs_change_order_currency' ), 999, 1 );

			//Registering cron job to sync order data for analytics if pay in user selected currency is enabled. This hook works for legacy checkout.
			add_action('woocommerce_checkout_order_created', array( $this, 'wccs_register_cron' ), 999, 1);

			//Registering cron job to sync order data for analytics if pay in user selected currency is enabled. This hook works for checkout block.
			add_action('woocommerce_store_api_checkout_order_processed', array( $this, 'wccs_register_cron' ), 999, 1);

			//Registering cron job to sync order data for analytics if pay in user selected currency is enabled. This hook works When order status updated.
			add_action('woocommerce_order_status_changed', array( $this, 'wccs_register_cron' ), 999, 1);

			add_action( 'wp_ajax_wccs_order_sync_process', array( $this, 'wccs_order_sync_process' ) );

			//Order in bulk sync event cron job callback function
			add_action('wccs_order_sync_event_bulk', array( $this, 'wccs_order_sync_event_bulk_callback' ), 10, 1);

			//Order sync event cron job callback function
			add_action('wccs_order_sync_event', array( $this, 'wccs_woo_analytics_sync_callback' ), 10, 1);

			// Change currency on manual order create from admin
			add_action('woocommerce_new_order', array( $this, 'wccs_creating_order_from_admin' ), 999, 1);

			add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'wccs_nonce_checkout_field' ), 10 );

			// calculate shipping price
			add_action( 'woocommerce_checkout_create_order_shipping_item', array( $this, 'wccs_checkout_create_order_shipping_item' ), 10, 4 );

			// adding meta box to order page wp admin
			add_action( 'add_meta_boxes', array( $this, 'wccs_register_order_meta_box' ) );

			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'wccs_change_wc_gateway_if_empty' ), 999, 1 );          

			add_action( 'woocommerce_coupon_loaded', array( $this, 'wccs_woocommerce_coupon_loaded' ), 9999 );

			add_action( 'wp_ajax_wccs_update_currency_by_billing_country', array( $this, 'wccs_update_currency_by_billing_country' ) );
			add_action( 'wp_ajax_nopriv_wccs_update_currency_by_billing_country', array( $this, 'wccs_update_currency_by_billing_country' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'wccs_checkout_scripts' ) );

			add_action( 'wccs_detect_wpml_lang', array( $this, 'wccs_detect_wpml_lang' ) );

			add_action( 'wccs_zone_pricing', array( $this, 'wccs_zone_pricing' ) );         

			add_filter( 'wcs_cart_totals_order_total_html', array( $this, 'wccs_cart_totals_order_total_html' ), 99, 2 );

			add_action('wp_ajax_wccs_currency_to_default', array( $this, 'wccs_currency_to_default' ));
			add_action('wp_ajax_nopriv_wccs_currency_to_default', array( $this, 'wccs_currency_to_default' ));            

			//Cart Block update
			add_action('init', array( $this, 'wccs_refresh_cart' )); 

			// Thank you page hide sticky
			add_filter( 'wccs_sticky_switcher_enable', array( $this, 'wccs_hide_sticky_thankyou_page' ), 99 );
			// Thank you page hide nav menu
			add_filter( 'wccs_before_nav_menu', array( $this, 'wccs_hide_nav_menu_thankyou_page' ), 99 );

			// wholesale Compatible 

			add_action( 'wholesale_user_roles_add_form_fields', array( $this, 'wwp_add_new_field' ), 99 );
			add_action( 'wholesale_user_roles_edit_form_fields', array( $this, 'wwp_edit_new_field' ), 99 );
			add_action( 'edited_wholesale_user_roles', array( $this, 'wwp_save_new_field' ), 10, 2 );
			add_action( 'create_wholesale_user_roles', array( $this, 'wwp_save_new_field' ), 10, 2 );

			//Compatibility with Adify B2B WooCommerce.
			if ( class_exists('Addify_B2B_Plugin') ) {              
				add_filter( 'get_post_metadata', array( $this, 'modify_b2b_rules_meta' ), 99, 4);
				add_action('woocommerce_before_mini_cart_contents', array( $this, 'woocommerce_before_mini_cart_contents' ), 5);
			}
			
			//compatible with woo conditional shipping by Woo
			add_filter('woocommerce_csp_get_condition_resolution', array( $this, 'wccs_conditional_shipping_error_message_modified' ), 90, 5);

			//compatible with Yith gift card
			if ( function_exists('yith_ywgc_premium_init') ) {              
				add_filter('yith_ywgc_cart_totals_gift_card_amount', array( $this, 'wccs_yith_ywgc_cart_totals_gift_card_amount' ), 99, 2);
			}
		}

		public function wccs_yith_ywgc_cart_totals_gift_card_amount( $amount, $code ) {
			if ( isset($amount) && !empty($amount) ) {
				$amount = $this->wccs_price_conveter( $amount, false );
			}

			return $amount;
		}

		public function wccs_conditional_shipping_error_message_modified( $message, $condition_key, $condition_data, $args, $conditions ) {

			if ( is_array( $condition_data ) && isset( $condition_data['value'] ) ) {

				$check_price = 0.00;

				$check_price = $this->wccs_price_conveter( $condition_data['value'], true );


				// Use preg_replace to remove the <span> tags and their content
				$message = preg_replace('/<span.*<\/span>/', $check_price, $message);

				// Trim any extra whitespace
				$message = trim($message);
			}

			return $message;
		}

		public function wwp_add_new_field() {
			// wp_enqueue_style( 'wccs_flags_style', WCCS_PLUGIN_URL . 'assets/lib/flag-icon/flag-icon.css', '', '1.0' );
			?>
			<div class="user-role-currency">
				<label for="user_role_currency"><?php esc_html_e( 'User Role Currency', 'wccs' ); ?></label>
				<input type="checkbox" name="wwp_user_role_currency" id="wwp_user_role_currency" value="yes">
				<span><?php esc_html_e( 'Allow custom currency for this wholesale user role.', 'wccs' ); ?></span>
			</div>
			<br>
			<div class="select-currency" style="display: none;">
				<label for="select_currency"><?php esc_html_e('Select a currency', 'wccs'); ?></label>
				<select name="wwp_wholesaler_select_currency" id="wwp_wholesaler_select_currency" class="">
					<option value=""><?php esc_html_e( 'Select Currency', 'wccs' ); ?></option>
					<?php 
					foreach ($this->currencies as $key => $value) {
						echo '<option value="' . esc_attr($key) . '">' . esc_html($value['label']) . '</option>';
					}
					?>
				</select>
				<!-- <span class="flag-icon flag-icon-<?php //echo esc_attr($this->wccs_get_default_currency_flag()) ?> "></span> -->
				<span><?php esc_html_e('Select a currency for this wholesale user role.', 'wccs'); ?></span>
			</div>
			<br>
			<?php
		}

		public function wwp_edit_new_field( $term ) {
			$term_id                        = $term->term_id;
			$wwp_user_role_currency         = get_term_meta( $term_id, 'wwp_user_role_currency', true );
			$wwp_wholesaler_select_currency = get_term_meta($term_id, 'wwp_wholesaler_select_currency', true);
			?>
			<tr>
				<th class="user-role-currency">
					<label for="user_role_currency"><?php esc_html_e( 'User Role Currency', 'wccs' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="wwp_user_role_currency" id="wwp_user_role_currency" value="yes" <?php checked( 'yes', $wwp_user_role_currency ); ?>>
					<span><?php esc_html_e( 'Allow custom currency for this wholesale user role.', 'wccs' ); ?></span>
				</td>
			</tr>

			<tr class="select-currency-dropdown" style="display: none;">
				<th class="select-currency">
					<label for="select_currency"><?php esc_html_e('Select a currency', 'wccs'); ?></label>
				</th>
				<td>
					<select name="wwp_wholesaler_select_currency" id="wwp_wholesaler_select_currency" class="">
					<option value=""><?php esc_html_e( 'Select Currency', 'wccs' ); ?></option>
						<?php 
						foreach ($this->currencies as $key => $value) {
							$selected ='';
							if ( $wwp_wholesaler_select_currency ==  $key  ) {
								$selected ='selected';
							}
							echo '<option value="' . esc_attr($key) . '"' . esc_attr($selected) . '>' . esc_attr($value['label']) . '</option>';
						}
						?>
						</select>
					<span><?php esc_html_e('Select a currency for this wholesale user role.', 'wccs'); ?></span>
				</td>
			</tr>
			<?php
		}

		public function wwp_save_new_field( $term_id, $term ) {
		
			if ( ! isset( $_POST['wwp_tax_exempt_nonce'] ) || ! wp_verify_nonce( wc_clean( $_POST['wwp_tax_exempt_nonce'] ), 'wwp_tax_exempt_nonce' ) ) {
				return;
			}

			if ( isset( $_POST['wwp_user_role_currency'] ) ) {
				update_term_meta( $term_id, 'wwp_user_role_currency', 'yes' );
			} else {
;
				update_term_meta( $term_id, 'wwp_user_role_currency', 'no' );
			}
			if ( isset( $_POST['wwp_wholesaler_select_currency'] ) ) {
				update_term_meta($term_id, 'wwp_wholesaler_select_currency', wc_clean($_POST['wwp_wholesaler_select_currency']));
			} else {
				update_term_meta($term_id, 'wwp_wholesaler_select_currency', '');
			}
		}


		public function wccs_refresh_cart() {
			woocommerce_store_api_register_update_callback(array(
				'namespace' => 'wccs',
				'callback'  => function ( $data ) {
				},
			 ));
		}


		/**
		 * Filter
		 * 
		 * @since 1.7.0
		 */
		public function wccs_order_sync_process() {

			check_ajax_referer( 'wccs', 'wccs_nonce' );
			$post = $_POST;

			$query = new \WC_Order_Query( array(
				'limit' => -1,
				'orderby' => 'date',
				'order' => 'DESC',
				'return' => 'ids',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => '_wccs_currency_rate',
						'compare' => 'EXISTS',
					),
				),
			) );

			$order_ids = $query->get_orders();

			if ( is_array( $order_ids ) && count( $order_ids ) > 0 ) {              
						
				/**
				 * Run after 3 mins from the current time.
				 *
				 * @since 1.7.0
				 */
				$timestamp = apply_filters('wccs_cron_order_sync_timestamp', time() + 180);                 
				wp_schedule_single_event( $timestamp, 'wccs_order_sync_event_bulk', array( $order_ids ) );

				wp_send_json(array( 'status' => 'success', 'message' => __('Note for Order Sync for Woo Analytics: Order Sync for Woo Analytics has been successfully completed. Please note that it may take some time for the changes to be fully reflected.', 'wccs'), 'order_count' => count($order_ids), 'order_ids' => $order_ids ));
			}

			wp_send_json(array( 'status' => 'failed', 'message' => __('Error: Order not Synced!', 'wccs') ));
		}


		/**
		 * Bulk callback update orders
		 *
		 * @return [type] [orders]
		 */

		public function wccs_order_sync_event_bulk_callback( $order_ids ) {

			if ( is_array( $order_ids ) && count($order_ids) > 0 ) {
				foreach ( $order_ids as $order_id ) {
					if ( is_multisite() ) {
						update_site_option('wcc_order_' . $order_id, 'updated!');
					} else {
						update_option('wcc_order_' . $order_id, 'updated!');
					}
					$this->wccs_woo_analytics_sync_callback($order_id);
				}
			}   
		}

		public function wccs_woo_analytics_sync_callback( $order_id ) {
			
			if ( ! empty($order_id) ) {
				$wc_order = wc_get_order( $order_id );
				$exchange_rate = $wc_order->get_meta('_wccs_currency_rate');
				$exchange_rate = $exchange_rate ? (float) $exchange_rate : 0;

				if ( $exchange_rate ) {
					
					global $wpdb;

					$option_like_data = '%_wc_report_orders%';

					$wpdb->query(
						$wpdb->prepare(
							"
					        DELETE FROM {$wpdb->prefix}options 
					        WHERE `option_name` LIKE %s
					        ",
							$option_like_data
						)
					);

					$new_total_sales = $wc_order->get_total() / $exchange_rate;
					$new_tax_total = $wc_order->get_total_tax() / $exchange_rate;
					$new_shipping_total = $wc_order->get_total_shipping() / $exchange_rate;
					$new_net_total = $wc_order->get_subtotal() / $exchange_rate;

					$wpdb->query(
						$wpdb->prepare(
							"
					        UPDATE {$wpdb->prefix}wc_order_stats 
					        SET total_sales = %f, tax_total = %f, shipping_total = %f, net_total = %f
					        WHERE order_id = %d
					        ",
							$new_total_sales,
							$new_tax_total,
							$new_shipping_total,
							$new_net_total,
							$order_id
						)
					);

					// Update line item totals based on exchange rate
					foreach ( $wc_order->get_items() as $item_id => $item ) {

						// Get the line item subtotal                       
						$new_item_subtotal = $item->get_subtotal() / $exchange_rate;
						$new_item_total = $item->get_total() / $exchange_rate;          
						// $new_item_tax_amount = $item->get_total_tax() / $exchange_rate;
						
						$wpdb->query(
							$wpdb->prepare(
								"
						        UPDATE {$wpdb->prefix}wc_order_product_lookup 
						        SET product_net_revenue = %f, product_gross_revenue = %f 
						        WHERE order_item_id = %d AND order_id = %d
						        ",
								$new_item_subtotal,
								$new_item_total,
								$item_id,
								$order_id
							)
						);
					}
				}   
			}           
		}

		
		public function wccs_register_cron( $arg ) {
			
			if ( empty($arg) ) {
				return;
			}

			if ( is_object( $arg ) && $arg instanceof WC_Order ) {
				$order_id = $arg->get_id(); //$arg contain order object
				$wc_order = $arg;
			} else {
				$order_id = $arg; //$arg contain order id
				$wc_order = wc_get_order($order_id);
			}           

			if ( ! empty($wc_order) ) {
				$exchange_rate = $wc_order->get_meta('_wccs_currency_rate');
				$exchange_rate = $exchange_rate ? (float) $exchange_rate : 0;

				if ( ! empty( $exchange_rate ) ) {
					/**
					 * Filter
					 * 
					 * @since 1.7.0
					 * run after 5 mins from the current time.
					 */
					$timestamp = apply_filters('wccs_cron_order_sync_timestamp', time() + 300);             
					wp_schedule_single_event( $timestamp, 'wccs_order_sync_event', array( $order_id ) );
				}
			}           
		}

		/**
		 * Compatibility with Addify B2B WooCommerce
		 *
		 * @return [type] [description]
		 */
		public function woocommerce_before_mini_cart_contents() {
			
			if ( ! defined('WOOCOMMERCE_CART') ) { 
				define('WOOCOMMERCE_CART', true);
			}
			
		/**
		 * Filter
		 * 
		 * @since 1.7.0
		 */
			do_action('woocommerce_before_calculate_totals', WC()->cart);

			WC()->cart->calculate_totals();            
		}

		/**
		 * Compatibility with Adify B2B WooCommerce
		 *
		 * @param  [type] $metadata  [description]
		 * @param  [type] $object_id [description]
		 * @param  [type] $meta_key  [description]
		 * @param  [type] $single    [description]
		 * @return [type]            [description]
		 */
		public function modify_b2b_rules_meta( $metadata, $object_id, $meta_key, $single ) {

			//For Order restriction Min & Max Order Amount
			if ( ! is_admin() && ( ( 'afor_min_amount' === $meta_key ) || ( 'afor_max_amount' === $meta_key ) ) ) {
				
				global $wpdb;

				$data = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s", $object_id, $meta_key) );
				$data = maybe_unserialize($data);

				$data = $this->wccs_price_conveter($data);

				return $data;
			}

			//For Customer Base Pricing
			if ( ! is_admin() && 'rcus_base_price' === $meta_key ) {

				global $wpdb;

				$data = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s", $object_id, $meta_key) );

				$data = maybe_unserialize($data);               

				$new_data = array();
				$dis_types = array( 'fixed_price', 'fixed_increase', 'fixed_decrease' );
				if ( is_array($data) && count($data) > 0 ) {
					foreach ($data as $key => $data) {
						if ( isset($data['discount_type']) && in_array($data['discount_type'], $dis_types) && isset($data['discount_value']) ) {
							$data['discount_value'] = $this->wccs_price_conveter($data['discount_value']);
							$new_data[][$key] = $data;
						}
					}
				}

				if ( empty($new_data) ) {
					return $metadata;
				}               

				return $new_data;
			}

			//For Role Base Pricing
			if ( ! is_admin() && 'rrole_base_price' === $meta_key ) {

				global $wpdb;

				$data = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s", $object_id, $meta_key) );

				$data = maybe_unserialize($data);               

				$new_data = array();
				$dis_types = array( 'fixed_price', 'fixed_increase', 'fixed_decrease' );
				if ( is_array($data) && count($data) > 0 ) {
					foreach ($data as $key => $data) {
						if ( isset($data['discount_type']) && in_array($data['discount_type'], $dis_types) && isset($data['discount_value']) ) {
							$data['discount_value'] = $this->wccs_price_conveter($data['discount_value']);
							$new_data[][$key] = $data;
						}
					}
				}

				if ( empty($new_data) ) {
					return $metadata;
				}               

				return $new_data;
			}

			return $metadata;
		}

		public function wccs_currency_to_default() {

			$this->currency      = sanitize_text_field( $this->default_currency );
			$this->currency_info = isset( $currencies[ sanitize_text_field( $this->default_currency ) ] ) ? $currencies[ sanitize_text_field( $this->default_currency ) ] : array();

			// set storage to default currency
			$this->storage->set_val( 'wccs_current_currency', $this->currency );
			$this->storage->set_val( 'set_by_location_currency', $this->currency );

			if ( wp_doing_ajax() ) {
				echo 'success';
				wp_die();
			}
		}

		/**
		 *  We have explode the total recurring amount and pass cart total and glued them. 
		 */
		public function wccs_cart_totals_order_total_html( $order_total_html, $cart ) {    

			$str = explode(' / ', $order_total_html);
			// $str[0] = '<strong>' . WC()->cart->get_total() . '</strong>';
			$str = implode(' / ', $str);            
			return $str;
		}

		public function wccs_creating_order_from_admin( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( is_admin() ) {
				if ( ! empty( $this->wccs_get_currency() ) ) {
					if ( $order ) {
						$order->update_meta_data( '_order_currency' , $this->wccs_get_currency() );
						$order->save();
					}
					// update_post_meta( $order_id, '_order_currency', $this->wccs_get_currency() );
				}               
			}
		}

		public function is_early_renew_subscription( $for_price = false ) { 

			global $woocommerce;
			/**
			 * Support Ticket -- WOOC-1234 -- Unable to find user_id on renewal button.
			 */
			// if (isset($_GET['user_id'])) {
			if ( ! empty( $woocommerce->cart ) && ! empty( $woocommerce->cart->get_cart() ) ) {
				foreach ( $woocommerce->cart->get_cart() as $key => $value ) {
					if ( isset( $value['subscription_renewal'] ) && isset( $value['subscription_renewal']['subscription_renewal_early'] ) ) {   
						if ( $value['subscription_renewal']['subscription_renewal_early'] ) {                   
							return 'stop';
						}
					}
				}
			}
			// }
		}

		public function wccs_zone_pricing() {

			//remove currency by location first
			$this->storage->remove_val( 'set_by_location_currency' );
			$this->storage->remove_val( 'wccs_current_currency' );

			$data =  wccs_get_client_ip_server();
			$flag = false;
			$selected_currency = '';
			$selected_rate = '';
			$selected_currency_symbol = '';
			$selected_decimal = 0;        

			// Get existing values from db.
				
			$wccs_zp_data =  get_mulltisite_or_site_option('wccs_zp_data', false);

			if ( false != $wccs_zp_data && count( $wccs_zp_data ) > 0 ) {
				// search for location country in zone.
				foreach ( $wccs_zp_data as $key => $zp_data ) {
					if ( isset( $zp_data['zp_name'] ) && isset( $data['geoplugin_countryCode'] ) ) {
						$needle = $data['geoplugin_countryCode'];
						$hay = $zp_data['zp_countries'];
						if ( in_array( $needle, $hay ) ) {

							$flag = true;
							$this->selected_zone_id  = $key;
							$selected_currency = $zp_data['zp_currency'];
							$selected_rate = $zp_data['zp_rate'];
							$selected_decimal = $zp_data['zp_decimal'];
							$selected_currency_symbol = get_woocommerce_currency_symbol( $selected_currency );
							$zp_prefix = $zp_data['zp_prefix'];
							/* if ( isset($zp_prefix) ) {
								$selected_currency_symbol = sprintf('%s%s', $zp_prefix, $selected_currency_symbol);
							} */
							break;
						}
					}
				}

				//search for all in zone
				foreach ( $wccs_zp_data as $key => $zp_data ) {
					if ( isset( $zp_data['zp_name'] ) ) {
						$hay = $zp_data['zp_countries'];
						if ( in_array( 'ALL', $hay ) ) {
							$flag = true;
							$this->selected_zone_id  = $key;
							$selected_currency = $zp_data['zp_currency'];
							$selected_rate = $zp_data['zp_rate'];
							$selected_decimal = $zp_data['zp_decimal'];
							$zp_prefix = $zp_data['zp_prefix'];
							$selected_currency_symbol = get_woocommerce_currency_symbol( $selected_currency );
							/* if ( isset($zp_prefix) ) {
								$selected_currency_symbol = sprintf('%s%s', $zp_prefix, $selected_currency_symbol);
							} */
							break;
						}
					}
				}               
			}

			if ( $flag && '' != $selected_currency && '' != $selected_rate && '' != $selected_currency_symbol ) {
				
				//$this->currency_info = array();
				$this->currency      = $selected_currency;
				$this->currency_info['zone_id'] = $this->selected_zone_id;
				$this->currency_info['rate'] = $selected_rate;
				$this->currency_info['decimals'] = $selected_decimal;
				//$this->currency_info['symbol'] = $selected_currency_symbol;
				//$this->currency_info['zp_prefix'] = $zp_prefix;

							
				// // set storage
				$this->storage->set_val( 'wccs_current_currency', $this->currency );
			
			} else {
				$this->currency      = null;
				$this->currency_info = array();

				// remove storage
				$this->storage->remove_val( 'wccs_current_currency' );
			}           

			return false;
		}

		public function wccs_detect_wpml_lang() {
			
			if ( function_exists( 'icl_get_languages' ) && defined( 'ICL_LANGUAGE_CODE' ) && '' != ICL_LANGUAGE_CODE ) {

				//remove currency by location first
				$this->storage->remove_val( 'set_by_location_currency' );
				$this->storage->remove_val( 'wccs_current_currency' );

				$wccs_lang =  get_mulltisite_or_site_option('wccs_lang', false);             

				if ( isset( $wccs_lang[ ICL_LANGUAGE_CODE ] ) && ! empty( $wccs_lang[ ICL_LANGUAGE_CODE ] ) ) {
					$currency_code = $wccs_lang[ ICL_LANGUAGE_CODE ];
								
					$currencies = $this->wccs_get_currencies();

					if ( isset( $currencies[ $currency_code ] ) ) {

						$this->currency      = $currency_code;
						$this->currency_info = $currencies[ $currency_code ];

						// set storage
						$this->storage->set_val( 'wccs_current_currency', $this->currency );

					} else {

						$this->currency      = null;
						$this->currency_info = array();

						// remove storage
						$this->storage->remove_val( 'wccs_current_currency' );

					}
					
				} else {
					$this->currency      = null;
					$this->currency_info = array();

					// remove storage
					$this->storage->remove_val( 'wccs_current_currency' );
				}

				return false;
			}
		}

		public function wccs_checkout_scripts() {
			wp_enqueue_script('wc-country-select');
			wp_enqueue_script('wc-address-i18n');
			wp_register_script( 'wccs_checkout', WCCS_PLUGIN_URL . 'assets/frontend/js/wccs_checkout.js', array( 'jquery', 'wc-country-select', 'wc-address-i18n' ), '1.5.5&t=' . gmdate( 'his' ) );
			wp_localize_script(
				'wccs_checkout',
				'wccs_checkout',
				array(
					'admin_url' => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( 'wccs_update_currency_by_billing_country' ),
					'action'    => 'wccs_update_currency_by_billing_country',
					'is_shop_currency' =>  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false),
					'is_billing_currency' =>  get_mulltisite_or_site_option('wccs_currency_by_billing', false),
					'shop_currency' =>  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false),
					'compatible_with_cache' =>  get_mulltisite_or_site_option('wccs_compatible_with_cache', 'disabled'),
				)
			);

			wp_register_script( 'wccs_early_renewal_subscription', WCCS_PLUGIN_URL . 'assets/frontend/js/wccs_early_subscription.js', array( 'jquery' ), '1.5.5&t=' . gmdate( 'his' ) );
			wp_localize_script(
				'wccs_early_renewal_subscription',
				'wccs_early_renewal_subscription',
				array(
					'admin_url' => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( 'wccs_early_renewal_subscription' ),
					'action'    => 'wccs_check_currency_before_early_renew',                
				)
			);

			if ( is_account_page() ) {
				wp_enqueue_script( 'wccs_early_renewal_subscription' );
			}

			if ( is_checkout() || is_cart() ) {
				wp_enqueue_script( 'wccs_checkout' );
			}
		}

		public function wccs_update_currency_by_billing_country() {
			
			if ( ! isset( $_POST['action'] ) || ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), sanitize_text_field( $_POST['action'] ) ) ) {
				return;
			}           

			$result = array();
			$result['status'] = 'fail';
			$result['url'] = '';

			if ( '1' ==  get_mulltisite_or_site_option('wccs_zp_toggle', false) ) {
				wp_send_json( $result );                
			}

			$currencies       =  get_mulltisite_or_site_option('wccs_currencies', array());
			$currencies[ $this->wccs_get_default_currency() ] = array();
			$current_currency = ! empty( $this->wccs_get_currency() ) ? $this->wccs_get_currency() : $this->wccs_get_default_currency();
			if ( ! empty( $_POST['billing_currency'] ) ) {              
				$currency_by_posted_country_code =  wccs_get_country_currency( sanitize_text_field( $_POST['billing_currency'] ) );              

				if ( ! empty( $currency_by_posted_country_code ) && ! empty( $current_currency ) && isset( $currencies[ $currency_by_posted_country_code ] ) && $currency_by_posted_country_code != $current_currency ) {
					$url = wc_get_checkout_url() . '?wcc_switcher=' . $currency_by_posted_country_code;
					$result['url'] = $url;
					$result['status'] = 'success';
					wp_send_json( $result );
				} else {
					wp_send_json( $result );
				}
			} else {
				wp_send_json( $result );
			}           
		}

		public function wccs_woocommerce_coupon_loaded( $coupon ) {

			if ( is_admin() ) {
				return $coupon;
			}

			$coupon_id            = $coupon->get_id();
			$prices               = array();
			$prices['amount']     = $coupon->get_amount();
			$prices['min_spend']  = $coupon->get_minimum_amount();
			$prices['max_spend']  = $coupon->get_maximum_amount();

			/* converting coupon amount to the selected currency, if not percent type coupon*/
			if ( ! $coupon->is_type( 'percent_product' ) && ! $coupon->is_type( 'percent' ) ) {
				$rate = $this->wccs_get_currency_rate();
				if ( $rate ) {
					$decimals = $this->wccs_get_currency_decimals();                    
					if ( isset( $prices['amount'] ) && ! empty( $prices['amount'] ) ) {
						$prices['amount'] = round( ( $prices['amount'] * $rate ), $decimals );
					}

					if ( isset( $prices['min_spend'] ) && ! empty( $prices['min_spend'] ) ) {
						$prices['min_spend'] = round( ( $prices['min_spend'] * $rate ), $decimals );
					}

					if ( isset( $prices['max_spend'] ) && ! empty( $prices['max_spend'] ) ) {
						$prices['max_spend'] = round( ( $prices['max_spend'] * $rate ), $decimals );
					}

					$coupon->set_minimum_amount( $prices['min_spend'] );
					$coupon->set_maximum_amount( $prices['max_spend'] );
					$coupon->set_amount( $prices['amount'] );
				}
			}

			/* Fixed coupon starts from here*/          
			$currencies           =  get_mulltisite_or_site_option('wccs_currencies', array());
			$current_currency     = ! empty( $this->wccs_get_currency() ) ? $this->wccs_get_currency() : $this->wccs_get_default_currency();
			$wccs_cfa_data        = get_post_meta( $coupon_id, 'wccs_cfa_data', true );
			$wccs_cfa_minmax_data = get_post_meta( $coupon_id, 'wccs_cfa_minmax_data', true );

			if ( !  get_mulltisite_or_site_option('wccs_fixed_coupon_amount', false) || !  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false) ) { // If fixed amount for coupon setting is disable OR shop by user currency is disable.
				return $coupon;
			}

			if ( ! $coupon->is_type( 'percent_product' ) && ! $coupon->is_type( 'percent' ) ) {
				$this->is_fixed = true;
			}

			if ( ! $this->is_fixed || empty( $current_currency ) || ! isset( $currencies[ $current_currency ] ) || $this->wccs_get_default_currency() == $current_currency ) {
				return $coupon;
			}

			foreach ( $prices as $key => $value ) {

				if ( 'amount' == $key && $this->is_fixed ) {
					if ( ! empty( $wccs_cfa_data ) && isset( $wccs_cfa_data[ $current_currency ] ) ) {
						$temp_amount = floatval( $wccs_cfa_data[ $current_currency ] );
						if ( '' != $temp_amount && 0 <= $temp_amount ) {
							$prices['amount'] = $temp_amount;
						}
					}
				}

				if ( 'min_spend' == $key && $this->is_fixed ) {
					if ( ! empty( $wccs_cfa_minmax_data ) && isset( $wccs_cfa_minmax_data[ $current_currency ]['min'] ) ) {
						$temp_min_amount = floatval( $wccs_cfa_minmax_data[ $current_currency ]['min'] );
						if ( '' != $temp_min_amount && 0 <= $temp_min_amount ) {
							$prices['min_spend'] = $temp_min_amount;
						}
					}
				}

				if ( 'max_spend' == $key && $this->is_fixed ) {
					if ( ! empty( $wccs_cfa_minmax_data ) && isset( $wccs_cfa_minmax_data[ $current_currency ]['max'] ) ) {
						$temp_max_amount = floatval( $wccs_cfa_minmax_data[ $current_currency ]['max'] );
						if ( '' != $temp_max_amount && 0 <= $temp_max_amount ) {
							$prices['max_spend'] = $temp_max_amount;
						}
					}
				}
			}

			$coupon->set_minimum_amount( $prices['min_spend'] );
			$coupon->set_maximum_amount( $prices['max_spend'] );
			$coupon->set_amount( $prices['amount'] );

			return $coupon;
		}

		public function wccs_wcsatt_subscription_scheme_prices( $option_data, $subscription_scheme, $product ) {

			$rate = $this->wccs_get_currency_rate();

			if ( 0 <= $this->selected_zone_id ) {   
				$individual_rate = trim(get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $this->selected_zone_id, true ));
				if ( '' != $individual_rate && 0 != $individual_rate ) {
					$rate = $individual_rate;                           
				}
			}

			if ( $rate ) {

				$decimals = $this->wccs_get_currency_decimals();

				if ( ! empty( $option_data['subscription_scheme']['regular_price'] ) ) {
					$option_data['subscription_scheme']['regular_price'] = round( ( $option_data['subscription_scheme']['regular_price'] * $rate ), $decimals );
					$option_data['subscription_scheme']['regular_price'] = $this->wccs_get_currency_rounding($option_data['subscription_scheme']['regular_price']);
					$option_data['subscription_scheme']['regular_price'] = $this->wccs_get_currency_charming($option_data['subscription_scheme']['regular_price']);
				}

				if ( ! empty( $option_data['subscription_scheme']['sale_price'] ) ) {
					$option_data['subscription_scheme']['sale_price'] = round( ( $option_data['subscription_scheme']['sale_price'] * $rate ), $decimals );
					$option_data['subscription_scheme']['sale_price'] = $this->wccs_get_currency_rounding($option_data['subscription_scheme']['sale_price']);
					$option_data['subscription_scheme']['sale_price'] = $this->wccs_get_currency_charming($option_data['subscription_scheme']['sale_price']);
				}

				if ( ! empty( $option_data['subscription_scheme']['price'] ) ) {
					$option_data['subscription_scheme']['price'] = round( ( $option_data['subscription_scheme']['price'] * $rate ), $decimals );
					$option_data['subscription_scheme']['price'] = $this->wccs_get_currency_rounding($option_data['subscription_scheme']['price']);
					$option_data['subscription_scheme']['price'] = $this->wccs_get_currency_charming($option_data['subscription_scheme']['price']);
				}
			}

			return $option_data;
		}

		public function wccs_order_page_currency( $symbol, $currency ) {


			if ( is_admin() ) {             

				$post_id = isset( $_GET['post'] ) && ! empty( sanitize_text_field( $_GET['post'] ) ) ? sanitize_text_field( $_GET['post'] ) : '';               
				if ( 'shop_order' === get_post_type( $post_id ) ) {
					if ( 'iso-code' ===  get_mulltisite_or_site_option('wccs_currency_display', 'symbol') ) {                     
						return $currency;
					} else {
						$currencies = $this->wccs_get_currencies();
						$prefix     = null;
						if ( isset( $currencies[ $currency ] ) ) {
							$info = $currencies[ $currency ];
							if ( isset( $info['symbol_prefix'] ) ) {
								$prefix = $info['symbol_prefix'];
							}
						}



						return $prefix . $symbol;
					}
				}
			}

			return $symbol;
		}

		public function woocommerce_booking_product_price_string( $output, $display_price, $product ) {
			// die('yyy');

			$output =  wccs_delete_all_between( '<span class="woocommerce-Price-currencySymbol">', '</span>', $output );

			$output_in_arr = explode( '<strong>', $output );
			$text          = $output_in_arr[0];
			$output_in_arr = explode( '</strong>', $output_in_arr[1] );

			$price = $output_in_arr[0];

			$rate = $this->wccs_get_currency_rate();
			
			if ( 0 <= $this->selected_zone_id ) {   
				$individual_rate = trim(get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $this->selected_zone_id, true ));
				if ( '' != $individual_rate && 0 != $individual_rate ) {
					$rate = $individual_rate;                           
				}
			}

			if ( is_numeric( $display_price ) && $rate ) {

				$decimals = $this->wccs_get_currency_decimals();
				$price    = round( ( $display_price * $rate ), $decimals );
				$price = $this->wccs_get_currency_rounding($price);
				$price = $this->wccs_get_currency_charming($price);
				$price    = '<span class="woocommerce-Price-amount amount"><bdi>' . $price . '</bdi></span>';
			}

			if ( 'left_space' == $this->currency_info['format'] ) {
				$output = $text . '<strong><span class="woocommerce-Price-currencySymbol">' . $this->currency_info['symbol'] . '</span> ' . $price . '</strong>';
			} elseif ( 'right_space' == $this->currency_info['format'] ) {
				$output = $text . '<strong>' . $price . ' <span class="woocommerce-Price-currencySymbol">' . $this->currency_info['symbol'] . '</span></strong>';
			} elseif ( 'left' == $this->currency_info['format'] ) {
				$output = $text . '<strong><span class="woocommerce-Price-currencySymbol">' . $this->currency_info['symbol'] . '</span>' . $price . '</strong>';
			} else {
				$output = $text . '<strong>' . $price . '<span class="woocommerce-Price-currencySymbol">' . $this->currency_info['symbol'] . '</span></strong>';
			}

			// $selected_currency =

			return $output;
		}

		public function woocommerce_booking_product_price_html( $output, $product ) {

			$prod_type = $product->get_type();

			if ( 'booking' === $prod_type ) {

				$output =  wccs_delete_all_between( '<del>', '</del>', $output );
			}

			return $output;
		}

		public function wccs_change_wc_gateway_if_empty( $allowed_gateways ) {

			if ( ! is_admin() && isset( $this->currency_info['payment_gateways'] ) && ! empty( $this->currency_info['payment_gateways'] ) ) {

				foreach ( $this->currency_info['payment_gateways'] as $active_payment_gateway ) {

					unset( $allowed_gateways[ $active_payment_gateway ] );

				}
			}
			return $allowed_gateways;
		}

		/**
		 * Function to return value in exchange rate selected currently..
		 *
		 * @param price
		 * @return exchange_price
		 */
		public function wccs_price_conveter( $price = '', $curr = false ) {

			if ( empty( $price ) ) {
				return;
			}

			$detect_currency = $this->storage->get_val( 'wccs_current_currency' );

			// price will remain same
			if ( empty( $detect_currency ) && '1' !==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && '1' !==  get_mulltisite_or_site_option('wccs_show_in_menu', false) && '1' !==  get_mulltisite_or_site_option('wccs_sticky_switcher', false) ) {
				$price = $price;
			} else {
				$coversion_rate = $this->wccs_get_currency_rate();
				$decimals       = $this->wccs_get_currency_decimals();
				if ( empty( $coversion_rate ) ) {
					$price = $price;
				} elseif ( is_numeric($price) ) {
					$price = round( ( $price * $coversion_rate ), $decimals );
				}
			}

			if ( false === $curr ) {
				return $price;
			} else {
				return wc_price( $price );
			}
		}

		/**
		 * Function to return value in exchange rate selected currently..
		 *
		 * @param price
		 * @return exchange_price
		 */
		public function wccs_price_conveter_to_default( $price = '', $curr = false ) {

			if ( empty( $price ) ) {
				return;
			}

			$detect_currency = $this->storage->get_val( 'wccs_current_currency' );

			// price will remain same
			if ( empty( $detect_currency ) && '1' !==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && '1' !==  get_mulltisite_or_site_option('wccs_show_in_menu', false) && '1' !==  get_mulltisite_or_site_option('wccs_sticky_switcher', false) ) {
				$price = $price;
			} else {
				$coversion_rate = $this->wccs_get_currency_rate();
				$decimals       = $this->wccs_get_currency_decimals();
				if ( empty( $coversion_rate ) ) {
					$price = $price;
				} else {
					$price = round( ( $price / $coversion_rate ), $decimals );
				}
			}

			if ( false === $curr ) {
				return $price;
			} else {
				return wc_price( $price );
			}
		}

		public function wccs_register_order_meta_box() {            
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {              
				global $theorder;
				$order = $theorder;

				// get_post_meta( $post->ID, '_wccs_shop_currency', true );
				if ( isset ( $order ) && ! empty( $order->get_meta( '_wccs_shop_currency' , true) ) ) {
					add_meta_box( 'wccs-currency-metabox', esc_html__( 'WCCS Order Info', 'wccs' ), array( $this, 'wccs_render_order_metabox_html' ), '', 'side', 'core' );
				}
			} else {                
				global $post;
				if ( ! empty( get_post_meta( $post->ID, '_wccs_shop_currency', true ) ) ) {
					add_meta_box( 'wccs-currency-metabox', esc_html__( 'WCCS Order Info', 'wccs' ), array( $this, 'wccs_render_order_metabox_html' ), '', 'side', 'core' );
				}
			}
		}

		public function wccs_render_order_metabox_html() {
			global $theorder;
			$order = $theorder;

			printf( esc_html( '%s %s' ) . esc_html__( 'Order Currency:', 'wccs' ) . esc_html( '%s ' . $order->get_meta( '_wccs_shop_currency' , true) . '%s' ), '<p>', '<strong>', '</strong>', '</p>' );
			printf( esc_html( '%s %s' ) . esc_html__( 'Base Currency:', 'wccs' ) . esc_html( '%s ' . $order->get_meta( '_wccs_base_currency' , true) . '%s' ), '<p>', '<strong>', '</strong>', '</p>' );
			printf( esc_html( '%s %s' ) . esc_html__( 'Order Currency rate:', 'wccs' ) . esc_html( '%s ' . $order->get_meta( '_wccs_currency_rate' , true) . '%s' ), '<p>', '<strong>', '</strong>', '</p>' );
			printf( esc_html( '%s %s' ) . esc_html__( 'Total Amount:', 'wccs' ) . wp_kses_post( '%s ' . wc_price($order->get_meta( '_wccs_total_in_base_currency' , true)) . '%s' ), '<p>', '<strong>', '</strong>', '</p>' );
		}

		public function wccs_nonce_checkout_field() {
			wp_nonce_field( '_wccsnonce', '_wccsnonce' );
		}

		public function wccs_checkout_create_order_shipping_item( $item, $package_key, $package, $order = '' ) {
			/**
			 * Filter
			 * 
			 * @since 1.0.0
			 */
			$surpass = apply_filters( 'wccs_surpass_shipping_conversion', true );           

			if ( ! $surpass ) {

				if ( ! empty( $this->wccs_get_currency() ) ) {
					if ( !  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false) && $this->wccs_get_currency() != $this->wccs_get_default_currency() ) {                        

						$coversion_rate  = $this->wccs_get_currency_rate();
						$decimals        = $this->wccs_get_currency_decimals();
						$rates           = $package['rates'];
						$shipping_total  = 0;
						$data            =  wcc_get_post_data();
						$shipping_method = $data['shipping_method'][0];
						foreach ( $rates as $id => $rate ) {
							if ( isset( $rates[ $id ] ) ) {

								if ( $coversion_rate && $shipping_method === $id ) {
									$decimals       = $this->wccs_get_currency_decimals();
									$shipping_total = $rates[ $id ]->cost;
									break;
								}
							}
						}                       

						$shipping_total = round( ( $shipping_total / $coversion_rate ), $decimals );
						$item->set_total( $shipping_total );
						// Make new taxes calculations
						$item->calculate_taxes();
						$item->save();
						$order->calculate_totals();
					}
				}
			}
		}

		public function wccs_change_order_currency( $order ) {

			if ( ! empty( $this->wccs_get_currency() ) ) {
				
				if ( '1' ==  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false) ) {
					$order_total    = $order->get_total();
					$coversion_rate = $this->wccs_get_currency_rate();
					$decimals       = $this->wccs_get_currency_decimals();
					// update order meta data
					$order->update_meta_data( '_wccs_shop_currency', $this->wccs_get_currency() );
					$order->update_meta_data( '_wccs_base_currency', $this->wccs_get_default_currency() );
					$order->update_meta_data( '_wccs_currency_rate', $coversion_rate );
					$order->update_meta_data( '_wccs_total_in_base_currency', round( ( $order_total / $coversion_rate ), $decimals ) );
					$order->save();                 
				}
			}
		}

		public function wccs_change_shipping_rates_cost( $rates, $package ) {

			/**
			* Filter
			*
			*  @since 1.7.2
			*/
			$filter_counter = apply_filters('wccs_shipping_package_count', $this->filter_counter);

			if ( ( is_checkout() && has_block('woocommerce/checkout', wc_get_page_id('checkout')) ) || 1 == $filter_counter || ( defined( 'REST_REQUEST' ) && REST_REQUEST )  ) {   
				$this->filter_counter++;
				$coversion_rate = $this->wccs_get_currency_rate();
				$decimals       = $this->wccs_get_currency_decimals();

				if ( $coversion_rate ) {
					foreach ( $rates as $id => $rate ) {

						if ( isset( $rates[ $id ] ) ) {

							$rates[ $id ]->cost = round( ( $rates[ $id ]->cost * $coversion_rate ), $decimals );

							// Taxes rate cost (if enabled)
							$taxes = array();
							foreach ( $rates[ $id ]->taxes as $key => $tax ) {
								if ( $tax > 0 ) { // set the new tax cost
									// set the new line tax cost in the taxes array
									$taxes[ $key ] = round( ( $tax * $coversion_rate ), $decimals );
								}
							}
							// Set the new taxes costs
							$rates[ $id ]->taxes = $taxes;
						}
					}
				}

				return $rates;
			}

			return $rates;
		}

		public function wccs_admin_enqueue_assets( $hook ) {

			if ( 'woocommerce_page_wccs-settings' == $hook || 'shop_coupon' == get_post_type() || 'product' == get_post_type() ) {
				wp_enqueue_style( 'wccs_admin_settings_style', WCCS_PLUGIN_URL . 'assets/admin/css/setting_style.css', '', '1.0&t=' . gmdate('dmYhis') );
			}

			if ( 'woocommerce_page_wccs-settings' == $hook || 'edit-tags.php' == $hook || 'term.php' == $hook || 'shop_coupon' == get_post_type() ) {
				wp_enqueue_style( 'wccs_jquery_ui_css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', '', '1.0' );
				// wp_enqueue_style('wccs_select2_style', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css");
				wp_enqueue_style( 'wccs_flags_style', WCCS_PLUGIN_URL . 'assets/lib/flag-icon/flag-icon.css', '', '1.0' );
				wp_enqueue_style( 'wccs_pretty_dd_style', WCCS_PLUGIN_URL . 'assets/lib/pretty_dropdowns/prettydropdowns.css', '', '1.0' );
				
				wp_enqueue_style( 'wccs_select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', array(), WCCS_VERSION . '?t=' . gmdate( 'His' ) );

				wp_enqueue_script( 'wccs_jquery_ui_script', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array( 'jquery' ), '1.0' );
				// wp_enqueue_script('wccs_select2_script', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js", array('jquery'));
				wp_enqueue_script( 'wccs_select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ), WCCS_VERSION . '?t=' . gmdate( 'His' ), true );
				wp_enqueue_script( 'wccs_pretty_dd_script', WCCS_PLUGIN_URL . 'assets/lib/pretty_dropdowns/jquery.prettydropdowns.js', array( 'jquery' ), '1.0' );
				wp_enqueue_script( 'wccs_admin_settings_script', WCCS_PLUGIN_URL . 'assets/admin/js/setting_script.js', array( 'wccs_jquery_ui_script' ), '1.0&t=' . gmdate( 'His' ) );
				wp_localize_script(
					'wccs_admin_settings_script',
					'variables',
					array(
						'ajaxurl'           => admin_url( 'admin-ajax.php' ),
						'flags_placeholder' => __( 'Choose Flag', 'wccs' ),
						'nonce' => wp_create_nonce('wccs'),
					)
				);
			}
		}

		public function wccs_front_enqueue_assets() {

			$currency_rate = $this->wccs_get_currency_rate();
			$currency_convert_rate = array(
				'ajax_url' => admin_url('front-ajax.php'),
				'nonce'     => wp_create_nonce('wccs'),
				'currency_rate' => $currency_rate,
				);

			// Compatible with WooCommerce Measurement Price Calculator
			if ( class_exists('WC_Measurement_Price_Calculator_Loader') ) {
				wp_enqueue_script( 'wccs_price_conveter', WCCS_PLUGIN_URL . 'assets/frontend/js/wccs_price_conveter.js', array( 'jquery', 'wc-price-calculator' ), '1.0' );
				wp_localize_script( 'wccs_price_conveter', 'currency_convert_rate', $currency_convert_rate );
			}
			
			if (  get_mulltisite_or_site_option('wccs_show_in_menu', 0) ) {
				wp_enqueue_style( 'wccs_flags_style', WCCS_PLUGIN_URL . 'assets/lib/flag-icon/flag-icon.css', '', '1.0' );
				wp_enqueue_style( 'wccs_menu_style', WCCS_PLUGIN_URL . 'assets/frontend/css/menu_style.css', '', '1.0' );
				wp_enqueue_script( 'wccs_menu_script', WCCS_PLUGIN_URL . 'assets/frontend/js/menu_script.js', array( 'jquery' ), '1.0' );
			}
		}

		public function wccs_woocommerce_currency( $currency ) {
			if ( ! is_admin() || wp_doing_ajax() ) {                
				if ( $this->wccs_get_currency() ) {
					return $this->wccs_get_currency();
				}
			}

			return $currency;
		}

		public function wccs_woocommerce_currency_symbol( $symbol, $currency ) {

			if ( ! is_admin() || wp_doing_ajax() ) {

				$this->wccs_get_currency();

				if ( 'iso-code' ===  get_mulltisite_or_site_option('wccs_currency_display', 'symbol') ) {
					return $currency;
				}
				$wccs_zp_toggle =  get_mulltisite_or_site_option('wccs_zp_toggle', false);

				if ( $currency != $this->wccs_get_default_currency() || ( isset($wccs_zp_toggle) && '1' == $wccs_zp_toggle ) ) {
					if ( $this->wccs_get_currency_symbol() ) {
						return $this->wccs_get_currency_symbol();
					}
				}
			}

			return $symbol;
		}

		public function wccs_price_format( $format, $currency_pos ) {

			$woo_default_currency =  get_mulltisite_or_site_option('woocommerce_currency', false);
			$current_currency     = $this->wccs_get_currency();

			if ( ( ! is_admin() && ! empty( $current_currency ) ) || ( wp_doing_ajax() && ! empty( $current_currency ) ) ) {
				$get_symbol_position    = $this->wccs_get_currency_format();
				if ( '' != $get_symbol_position ) {
					$current_pos = $get_symbol_position;
				} else {
					$current_pos = $currency_pos;
				}
				
				$default_format = $current_pos;

				switch ( $current_pos ) {
					case 'left':
						$format = '%1$s%2$s';
						break;
					case 'right':
						$format = '%2$s%1$s';
						break;
					case 'left_space':
						$format = '%1$s&nbsp;%2$s';
						break;
					case 'right_space':
						$format = '%2$s&nbsp;%1$s';
						break;
					default:
						$format       = $default_format;
						$currency_pos = $current_pos;
				}
				/**
				 * Filter
				 * 
				 * @since 1.0.0
				 */
				return apply_filters( 'wccs_price_format', $format, $currency_pos );

			} else {
				/**
				 * Filter
				 * 
				 * @since 1.0.0
				 */
				return apply_filters( 'wccs_price_format', $format, $currency_pos );
			}

			// return $format;
		}

		public function wccs_price_args( $args ) {
			if ( ! is_admin() || wp_doing_ajax() ) {
				$decimals = $this->wccs_get_currency_decimals();

				$args['decimals'] = $decimals;
			}

			return $args;
		}

		public function wccs_product_get_price( $price, $product ) {  

			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $product->get_id(), '_price', true);    

				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {
						return $price;
					}
				}
			}

			// if ( function_exists('b2bking_run') ) {
			//  if ( did_action('woocommerce_before_calculate_totals') >= 1 ) {
			//      return $price;
			//  }
			// }

			return $this->wccs_custom_price( $price, $product );
		}

		public function product_get_sale_price( $price, $product ) {            
			
			if ( class_exists('Addify_B2B_Plugin') ) {                  
				$_sale_price = get_post_meta( $product->get_id(), '_sale_price', true);         

				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) {
					if (  $_sale_price != $price ) {
						return $price;
					}
				}
			}

			return $this->wccs_custom_price( $price, $product );
		}

		public function wccs_get_regular_price( $price, $product ) {
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_regular_price = get_post_meta( $product->get_id(), '_regular_price', true);           

				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) {
					if (  $_regular_price != $price ) {
						return $price;
					}
				}
			}

			return $this->wccs_custom_price( $price, $product );
		}

		public function wccs_custom_price( $price, $product ) {
			
			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			$price = apply_filters( 'wccs_simple_prod_price_before_convert', $price, $product );

			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}

				if ( 'donation' == get_post_meta( $product->get_id(), 'is_wc_donation', true ) ) {
					return $price;
				}

				$rate = $this->wccs_get_currency_rate();                    
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;

				wc_delete_product_transients( $product->get_id() );

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {                       
					$individual_rate = trim( get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ) );
					if ( '' != $individual_rate && 0 != $individual_rate ) {
						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                            
						return $price;                  
					}
				}

				// Compatible With Wholesale Suit
				if ( class_exists( 'WooCommerceWholeSalePrices' ) ) {
					$user_info = get_userdata( get_current_user_id() );
					$user_roles = array_values($user_info->roles);

					if ( in_array( 'wholesale_customer', $user_roles ) ) {
						$wholesale_price = \WWP_Wholesale_Prices::get_product_raw_wholesale_price( $product, $user_roles );

						$new_price = $rate ? round( floatval($wholesale_price) * floatval($rate), $decimals ) : $wholesale_price;

						// Register the filter to adjust the wholesale price
						add_filter('wwp_filter_wholesale_price_html_before_return_wholesale_price_only', function ( $wholesale_price_html, $price, $product, $user_wholesale_role, $wholesale_price_title_text, $raw_wholesale_price, $source, $return_wholesale_price_only, $wholesale_price ) use ( $new_price ) {
							return '<span style="display: block;" class="wholesale_price_container"><span class="wholesale_price_title">' . $wholesale_price_title_text . '</span><ins>' . wc_price($new_price) . '</ins></span>';
						}, 10, 9);

						// Handle variable products
						if ( $product->is_type('variable') ) {
							foreach ( $product->get_available_variations() as $variation ) {
								$variation_id = $variation['variation_id'];
								$variation_product = wc_get_product( $variation_id );

								$variation_wholesale_price = \WWP_Wholesale_Prices::get_product_raw_wholesale_price( $variation_product, $user_roles );
								$new_variation_price = $rate ? round( floatval($variation_wholesale_price) * floatval($rate), $decimals ) : $variation_wholesale_price;

								// Apply the same filter to variations
								add_filter('wwp_filter_wholesale_price_html_before_return_wholesale_price_only', function ( $wholesale_price_html, $price, $product, $user_wholesale_role, $wholesale_price_title_text, $raw_wholesale_price, $source, $return_wholesale_price_only, $wholesale_price ) use ( $new_variation_price ) {
									return '<span style="display: block;" class="wholesale_price_container"><span class="wholesale_price_title">' . $wholesale_price_title_text . '</span><ins>' . wc_price($new_variation_price) . '</ins></span>';
								}, 10, 9);
							}
						}
					}
				}

				if ( $rate ) {
					$price = round( ( $price * $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);
				}

				// Compatible with Woo Conditional Shipping and Payments
				add_filter('woocommerce_csp_check_condition', function ( $condition_apply, $condition_key, $condition_data, $args, $conditions ) {
					if ( isset( $condition_data ) ) {
						$order_contents_total = WC()->cart->get_cart_contents_total();
						$condition_price = (float) $this->wccs_price_conveter( $condition_data[ 'value' ], false );
						
						if ( 'gte' == $condition_data['modifier'] && $condition_price  <= $order_contents_total ) {
							return true;
						} elseif ( 'lt' == $condition_data['modifier'] && $condition_price > $order_contents_total ) {
							return true;
						} elseif ( 'lte' == $condition_data['modifier'] && $condition_price >= $order_contents_total ) {
							return true;
						} elseif ( 'gt' == $condition_data['modifier'] && $condition_price < $order_contents_total ) {
							return true;
						}
				
					}
					
					return $condition_apply; // changed this line from false to $condition_apply
				}, 20, 5);

				// Compatible with WooCommerce Measurement Price Calculator
				if (class_exists('\WC_Measurement_Price_Calculator')) {
					add_filter('woocommerce_cart_item_price', function ( $price, $cart_item, $cart_item_key ) {
						// Check if the price meta data exists
						if ( isset($cart_item['pricing_item_meta_data']['_price']) ) {
							// Convert the price using your custom converter function
							$price = $this->wccs_price_conveter($cart_item['pricing_item_meta_data']['_price']);
							return wc_price($price);
						} else {
							return $price;
						}
					}, 20, 3);
				}
				
				// Compatible with WooCommerce Measurement Price Calculator
				add_filter('wc_measurement_price_calculator_get_price_html', function ( $price_html, $product, $pricing_label ) {

					$settings = \WC_Price_Calculator_Settings::for($product);

					$min_regular_price  = $settings->get_pricing_rules_minimum_regular_price();
					$max_regular_price  = $settings->get_pricing_rules_maximum_regular_price();

					$price_html = $this->wccs_price_conveter($min_regular_price, true) . ' - ' . $this->wccs_price_conveter($max_regular_price, true) . ' ';
					$price_html .= $pricing_label;

					// echo '<pre>price_html';
					// print_r( $price_html );
					// echo '</pre>';

					return $price_html;
				}, 20, 3);

				// Compatible With WooCommerce Booking
				if ( $rate ) {
					add_filter('woocommerce_bookings_resource_additional_cost_string', function ( $additional_cost_string, $resource ) use ( $current_currency_info, $rate, $decimals, $product ) {
						$symbol = $current_currency_info['symbol'];
						if ( '' == $product->get_display_cost() ) {
							$price = $resource->get_base_cost();
						} else {
							$price  = $resource->get_base_cost() + $product->get_block_cost() + $product->get_cost();
						}
						$price = round( ( $price * $rate ), $decimals );
						$block_price = $resource->get_block_cost();
						if ( $block_price ) {
							$block_price = round( ( $block_price * $rate ), $decimals );
							$data = "(+ {$symbol}{$price}, +{$symbol}{$block_price} per day)";
						} else {
							$data = "(+ {$symbol}{$price})";
						}
						return $data;
					}, 20, 2);
				}

				// Compatible With WooCommerce Deposits
				if ( class_exists( 'WC_Deposits_Product_Meta' ) ) {
					add_filter('woocommerce_deposits_fixed_deposit_amount', function ( $amount, $product ) use ( $rate, $decimals ) {
						$default_amount =  get_mulltisite_or_site_option('wc_deposits_default_amount', false);
						$amount = !empty( \WC_Deposits_Product_Meta::get_meta( $product->get_id(), '_wc_deposit_amount' )) ? \WC_Deposits_Product_Meta::get_meta( $product->get_id(), '_wc_deposit_amount' ) : $default_amount;
						$amount = $rate ? round( floatval($amount) * floatval($rate), $decimals ) : $amount;
						return $amount;
					}, 99, 2);
				}
			}
 
			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			$price = apply_filters( 'wccs_simple_prod_price_after_convert', $price, $product );

			return $price;
		}

		public function wccs_tier_pricing( $price, $cart_item ) {           
			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}

				$rate = $this->wccs_get_currency_rate();
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {
					$individual_rate = trim(get_post_meta( $cart_item['product_id'], 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ));
					if ( '' != $individual_rate && 0 != $individual_rate ) {
						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                            
						return $price;                  
					}
				}

				if ( $rate ) {
					$price = round( ( $price / $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);
				}
			}

			return $price;
		}

		/**
		 * [wccs_custom_variation_get_price description]
		 *
		 * @param  [type] $price     [description]
		 * @param  [type] $variation [description]
		 * @return [type]            [description]
		 */
		public function wccs_custom_variation_get_price( $price, $variation ) {            

			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {                 
						return $price;
					}
				}
			}

			return $this->wccs_custom_variation_price( $price, $variation );
		}

		/**
		 * [wccs_custom_variation_get_sale_price description]
		 *
		 * @param  [type] $price     [description]
		 * @param  [type] $variation [description]
		 * @return [type]            [description]
		 */
		public function wccs_custom_variation_get_sale_price( $price, $variation ) {
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_sale_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {                 
						return $price;
					}
				}
			}

			return $this->wccs_custom_variation_price( $price, $variation );
		}       
		
		/**
		 * [wccs_custom_variation_get_regular_price description]
		 *
		 * @param  [type] $price     [description]
		 * @param  [type] $variation [description]
		 * @return [type]            [description]
		 */
		public function wccs_custom_variation_get_regular_price( $price, $variation ) {            
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_regular_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {                 
						return $price;
					}
				}
			}

			return $this->wccs_custom_variation_price( $price, $variation );
		}

		/**
		 * [wccs_custom_variation_price description]
		 *
		 * @param  [type] $price     [description]
		 * @param  [type] $variation [description]
		 * @return [type]            [description]
		 */
		public function wccs_custom_variation_price( $price, $variation ) { 

			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			$price = apply_filters( 'wccs_variable_prod_price_before_convert', $price, $variation );

			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}

				$rate = $this->wccs_get_currency_rate();
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;
				$individual_rate = '';  
				wc_delete_product_transients( $variation->get_id() );       

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {

					$individual_rate = trim(get_post_meta( $variation->get_parent_id(), 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ));
					if ( '' != $individual_rate && 0 != $individual_rate ) {

						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                        
						return $price;                  
					}
				}

				if ( $rate ) {      
					$price    = round( ( $price * $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);         
				}
			}

			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			$price = apply_filters( 'wccs_variable_prod_price_after_convert', $price, $variation );

			return $price;
		}

		
		public function wccs_custom_variable_get_price( $price, $variation, $product ) {
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {
						return $price;
					}
				}
			}

			return $this->wccs_custom_variable_price( $price, $variation, $product );
		}

		public function wccs_custom_variable_get_regular_price( $price, $variation, $product ) {            
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_regular_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {
						return $price;
					}
				}
			}

			return $this->wccs_custom_variable_price( $price, $variation, $product );
		}

		public function wccs_custom_variable_get_sale_price( $price, $variation, $product ) {
			
			if ( class_exists('Addify_B2B_Plugin') ) {
				$_price = get_post_meta( $variation->get_id(), '_sale_price', true);
				
				if ( did_action('woocommerce_before_calculate_totals') >= 1 ) { 
					if (  $_price != $price ) {
						return $price;
					}
				}
			}           

			return $this->wccs_custom_variable_price( $price, $variation, $product );
		}

		public function wccs_custom_variable_price( $price, $variation, $product ) {

			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}
				
				$rate = $this->wccs_get_currency_rate();
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {
					$individual_rate = trim(get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ));
					if ( '' != $individual_rate && 0 != $individual_rate ) {
						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                            
						return $price;                  
					}
				}

				if ( $rate ) {                  
					$price    = round( ( $price * $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);
				}
			}

			return $price;
		}

		public function wccs_subscription_product_price( $price, $product ) {           
			
			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}
				
				$rate = $this->wccs_get_currency_rate();
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {
					$individual_rate = trim(get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ));
					if ( '' != $individual_rate && 0 != $individual_rate ) {
						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                            
						return $price;                  
					}
				}

				if ( $rate ) {                  
					$price    = round( ( $price * $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);
				}
			}
			return $price;
		}

		public function wccs_subscription_product_price_signup( $price, $product ) {
					
			if ( ( ! is_admin() && is_numeric( $price ) ) || ( wp_doing_ajax() && is_numeric( $price ) ) ) {

				if ( 'stop' == $this->is_early_renew_subscription() ) {
					return $price;
				}
				
				$rate = $this->wccs_get_currency_rate();
				$decimals = $this->wccs_get_currency_decimals();
				$current_currency_info = $this->currency_info;

				if ( isset( $current_currency_info['zone_id'] ) && 0 <= $current_currency_info['zone_id'] ) {
					$individual_rate = trim(get_post_meta( $product->get_id(), 'wccs_zp_override_rate_for_' . $current_currency_info['zone_id'], true ));
					if ( '' != $individual_rate && 0 != $individual_rate ) {
						$rate = $individual_rate;
						$price = round( ( $price * $rate ), $decimals );                            
						return $price;                  
					}
				}

				if ( $rate ) {                  
					$price    = round( ( $price * $rate ), $decimals );
					$price = $this->wccs_get_currency_rounding($price);
					$price = $this->wccs_get_currency_charming($price);
				}
			}
			return $price;
		}

		public function get_formatted_product_subtotal( $product_subtotal, $product, $quantity, $cart ) {

			$signup_fee = get_post_meta( $product->get_id(), '_subscription_sign_up_fee', true );

			return $product_subtotal;
		}

		public function wccs_add_user_to_variation_prices_hash( $hash ) {
			if ( ! is_admin() || wp_doing_ajax() ) {
				if ( get_current_user_id() ) {
					if ( $this->wccs_get_currency() ) {
						$hash[] = get_current_user_id() . '-' . $this->currency;
					} else {
						$hash[] = get_current_user_id();
					}
				} elseif ( $this->wccs_get_currency() ) {
						$hash[] = \WC_Geolocation::get_ip_address() . '-' . $this->currency;
				} else {
					$hash[] = \WC_Geolocation::get_ip_address();
				}
			}

			return $hash;
		}

		public function wccs_detect_currency() {


			$wccs_compatible_with_cache =  get_mulltisite_or_site_option('wccs_compatible_with_cache', 'disabled');
			if ( isset($wccs_compatible_with_cache) && 'enabled' == $wccs_compatible_with_cache ) {
				if ( isset($_REQUEST['wcc_switcher']) ) {

					// compatibile with WPRocket 
					if ( class_exists('WP_Rocket_Requirements_Check') ) {
						rocket_clean_domain();
					}

					// compatibile with litespeed-cache 
					if ( class_exists('Purge') ) {
						\Purge::purge_all();
					}

				}
			}   

			global $woocommerce;
			if ( ! empty( $woocommerce->cart->get_cart() ) ) {
				foreach ( $woocommerce->cart->get_cart() as $key => $value ) {
					if ( isset( $value['subscription_renewal'] ) && isset( $value['subscription_renewal']['subscription_renewal_early'] ) ) {   
						if ( $value['subscription_renewal']['subscription_renewal_early'] ) {
							return false;                           
						}
					}
				}
			}

 
			if ( '1' ==  get_mulltisite_or_site_option('wccs_zp_toggle', false) && '1' ==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && '1' ==  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false) ) {
				/**
				 * Filter
				 * 
				 * @since 1.0.0
				 */
				do_action( 'wccs_zone_pricing' );
				return false;           
			}           

			if (  get_mulltisite_or_site_option('wccs_currency_by_lang', false) ) {
				/**
				 * Filter
				 * 
				 * @since 1.0.0
				 */
				do_action( 'wccs_detect_wpml_lang' );
				return false;
			}

			$default_location = get_option( 'woocommerce_default_customer_address', 'base' );
			$detect_currency = $this->storage->get_val( 'wccs_current_currency' );
			$data =  wccs_get_client_ip_server();


			$currency_by_location = get_mulltisite_or_site_option('wccs_currency_by_location', true);
			$currency_location_type = get_mulltisite_or_site_option('wccs_currency_location_type', true);

			if ( $data['geoplugin_currencyCode'] != $this->storage->get_val( 'set_by_location_currency' ) ) {
				if ( ( $currency_by_location && 'default' == $currency_location_type ) || empty( $detect_currency ) ) {
					$this->storage->remove_val( 'set_by_location_currency' );
				}
			}

			if ( '1' ==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && empty( $this->storage->get_val( 'set_by_location_currency' ) ) ) {
				$detect_currency = '';
				// remove storage
				$this->storage->remove_val( 'wccs_current_currency' );
			}

			// Compatible with Wholesale
			if ( ! isset( $_REQUEST['wcc_switcher'] ) ) {
				$user_info = get_userdata( get_current_user_id() );
				if ( isset($user_info->roles) ) {
					$user_role = implode( ', ', (array) $user_info->roles );
					$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
					if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
						if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
							$term_id = $wholesale_role['term_id'];
							if ( 'yes' === get_term_meta( $term_id, 'wwp_user_role_currency', true ) && !empty(get_term_meta( $term_id, 'wwp_wholesaler_select_currency', true )) ) {
								$_REQUEST['wcc_switcher'] = get_term_meta( $term_id, 'wwp_wholesaler_select_currency', true );
							}
						}
					}
				}
			}

			if ( ( isset( $_REQUEST['wcc_switcher'] ) && sanitize_text_field( $_REQUEST['wcc_switcher'] ) ) || ( isset( $_GET['currency'] ) && sanitize_text_field( $_GET['currency'] ) ) ) {

				if ( empty( sanitize_text_field($_REQUEST['wcc_switcher']) ) ) {
					$_REQUEST['wcc_switcher'] = $detect_currency;
				}

				$currencies = $this->wccs_get_currencies();

				if ( isset( $_REQUEST['wcc_switcher'] ) && isset( $currencies[ sanitize_text_field( $_REQUEST['wcc_switcher'] ) ] ) ) {

					$this->currency      = sanitize_text_field( $_REQUEST['wcc_switcher'] );

					$this->currency_info = $currencies[ sanitize_text_field( $_REQUEST['wcc_switcher'] ) ];

					// set storage
					$this->storage->set_val( 'wccs_current_currency', $this->currency );
					$this->storage->set_val( 'set_by_location_currency', $this->currency );

				} elseif ( isset( $_GET['currency'] ) && isset( $currencies[ sanitize_text_field( $_GET['currency'] ) ] ) ) {

					$this->currency      = sanitize_text_field( $_GET['currency'] );
					$this->currency_info = $currencies[ sanitize_text_field( $_GET['currency'] ) ];

					// set storage
					$this->storage->set_val( 'wccs_current_currency', $this->currency );
					$this->storage->set_val( 'set_by_location_currency', $this->currency );

				} else {

					$this->currency      = null;
					$this->currency_info = array();

					// remove storage
					$this->storage->remove_val( 'wccs_current_currency' );

				}
			} elseif ( empty( $detect_currency ) && ( 'geolocation' == $default_location || 'geolocation_ajax' == $default_location ) ) {
				$currencies = $this->wccs_get_currencies();

				if ( isset( $data['geoplugin_currencyCode'] )  ) {

					if (array_key_exists($data['geoplugin_currencyCode'], $this->currencies)) {
						$this->currency      = sanitize_text_field( $data['geoplugin_currencyCode'] );
						$this->currency_info = $currencies[ sanitize_text_field( $data['geoplugin_currencyCode'] ) ];
					} else {
						$this->currency      = $this->default_currency;
					}

				} else {
					$this->currency      = null;
					$this->currency_info = array();

					// remove storage
					$this->storage->remove_val( 'wccs_current_currency' );
				}
			} elseif ( empty( $detect_currency ) && '1' ==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && empty( $this->storage->get_val( 'set_by_location_currency' ) ) ) { // && !isset( $_SESSION['set_by_location'] )

				$currencies = $this->wccs_get_currencies();

	

				if ( isset( $data['geoplugin_currencyCode'] )  ) {

					$currencyCode = isset($currencies[$data['geoplugin_currencyCode']]) ? $currencies[sanitize_text_field($data['geoplugin_currencyCode'])] : '';
					
					if (array_key_exists($data['geoplugin_currencyCode'], $this->currencies)) {
						$this->currency      = sanitize_text_field( $data['geoplugin_currencyCode'] );
					} else {
						$this->currency      = $this->default_currency;
					}

					$this->currency_info = $currencyCode;

					// set storage
					$this->storage->set_val( 'wccs_current_currency', $this->currency );
					$this->storage->set_val( 'set_by_location_currency', $this->currency );

				} else {
					$this->currency      = null;
					$this->currency_info = array();

					// remove storage
					$this->storage->remove_val( 'wccs_current_currency' );
				}
			}

			/**
			 * Filter hook to set the currency.
			 *
			 * @param string $currency The currency to be set.
			 * @return string The filtered currency.
			 * @since 1.7.1
			 */
			$this->currency = apply_filters( 'wccs_set_currency', $this->currency );
			/**
			 * Filter hook to set currency information.
			 *
			 * @param array  $currency_info The currency information.
			 * @param array  $currencies    The list of available currencies.
			 * @return array The filtered currency information.
			 * @since 1.7.1
			 */
			$this->currency_info = apply_filters( 'wccs_set_currency_info', $this->currency_info, $this->wccs_get_currencies() );

			//starting session.
			if ( !session_id() ) {
				session_start();
			}

			$_SESSION['wccs_currency_info'] = $this->currency_info;
			$_SESSION['wccs_currency_code'] = $this->currency;
			$this->storage->set_val( 'wccs_current_currency', $this->currency );
			$this->storage->set_val( 'set_by_location_currency', $this->currency );

			WC()->cart->calculate_totals();
		}

		public function wccs_get_currencies() {
			$currencies = array();

			if ( '1' ==  get_mulltisite_or_site_option('wccs_zp_toggle', false) && '1' ==  get_mulltisite_or_site_option('wccs_currency_by_location', false) && '1' ==  get_mulltisite_or_site_option('wccs_pay_by_user_currency', false) ) {  

				$wccs_zp_data =  get_mulltisite_or_site_option('wccs_zp_data', false);                            
				if ( false != $wccs_zp_data && count( $wccs_zp_data ) > 0 ) {                   

					foreach ( $wccs_zp_data as $key => $zp_data ) {
						$currencies[ $zp_data['zp_currency'] ] = array(
							'zone_id' => $key,
							'rate' => $zp_data['zp_rate'],
							// 'symbol' => get_woocommerce_currency_symbol( $zp_data['zp_currency'] ),
							'decimals' => isset($zp_data['zp_decimal']) ? $zp_data['zp_decimal'] : 0,
						);
					}
					
					return $currencies;
				}
			}

			if ( $this->currencies ) {
				$currencies = $this->currencies;
			}

			return $currencies;
		}

		public function wccs_get_default_currency() {
			$default = null;
			if ( $this->default_currency ) {
				$default = $this->default_currency;
			}
			return $default;
		}

		public function wccs_get_default_currency_flag() {
			$default = null;
			if ( $this->default_currency_flag ) {
				$default = $this->default_currency_flag;
			}
			return $default;
		}

		public function wccs_get_currency() {
			$currency = null;
			if ( $this->currency ) {
				$currency = $this->currency;
			}

			if ( isset($_SESSION['wccs_currency_code']) && ! empty($_SESSION['wccs_currency_code']) ) {
				$currency = sanitize_text_field($_SESSION['wccs_currency_code']);
			}

			return $currency;
		}

		public function wccs_get_currency_symbol() {
			$symbol = null;
			$prefix = null;
			$info   = $this->currency_info;


			if ( isset( $info['symbol'] ) ) {
				if ( isset( $info['symbol_prefix'] ) ) {
					$prefix = $info['symbol_prefix'];
				}
				$symbol = $prefix . $info['symbol'];
			}


			if ('1' ==   get_mulltisite_or_site_option('wccs_zp_toggle', false)) {
				$data =  wccs_get_client_ip_server();
				$wccs_zp_data =  get_mulltisite_or_site_option('wccs_zp_data', false);
				foreach ( $wccs_zp_data as $key => $zp_data ) {
					if ( isset( $zp_data['zp_name'] ) && isset( $data['geoplugin_countryCode'] ) ) {
						$needle = $data['geoplugin_countryCode'];
						$hay = $zp_data['zp_countries'];
						if (in_array($needle, $hay)) {
								$symbol = isset($zp_data['zp_prefix']) ? $zp_data['zp_prefix'] . $info['symbol'] : '';
							break;
						}
					}
				}
			}

			// echo $info['symbol'];
		
			return $symbol;
		}

		public function wccs_get_currency_format() {
			$format = null;
			$info   = $this->currency_info;
			if ( isset( $info['format'] ) ) {
				$format = $info['format'];
			}
			return $format;
		}

		public function wccs_get_currency_rate() {
			
			$rate = null;
			$info = $this->currency_info;

			if ( isset( $info['rate'] ) ) {
				$rate = $info['rate'];
			}
			return $rate;
		}

		public function wccs_get_currency_rounding( $price ) {
			
			$rounding = 0;
			$info = $this->currency_info;

			if ( isset( $info['rounding'] ) ) {
				$rounding = $info['rounding'];
			}

			$price_break = explode( '.', $price);

			//Price Rounding
			if ( isset($price_break[1]) && '' != $price_break[1] ) {

				$decimal_num = (int) $price_break[1];

				if ( $decimal_num > 0 ) {
					if ( '0.25' == $rounding ) {
						$rounding = (float) $rounding;
						$num = $this->closestNumber($price, $rounding);
						$added_price = $num - $price;
						$price = $price + $added_price;
					}

					if ( '0.5' == $rounding ) {
						$rounding = (float) $rounding;
						$num = $this->closestNumber($price, $rounding);
						$added_price = $num - $price;
						$price = $price + $added_price;
					}

					if ( '1' == $rounding ) {
						$rounding = (int) $rounding;
						$num = $this->closestNumber($price, $rounding);
						$added_price = $num - $price;
						$price = $price + $added_price;
					}

					if ( '5' == $rounding ) {
						$rounding = (int) $rounding;
						$num = $this->closestNumber($price, $rounding);
						$added_price = $num - $price;
						$price = $price + $added_price; 
					}

					if ( '10' == $rounding ) {
						$rounding = (int) $rounding;
						$num = $this->closestNumber($price, $rounding);
						$added_price = $num - $price;
						$price = $price + $added_price;
					}
				}                       
			}

			return $price;
		}

		public function wccs_get_currency_charming( $price ) {
			
			$charming = 0;
			$info = $this->currency_info;

			if ( isset( $info['charming'] ) ) {
				$charming = $info['charming'];
			}
			
			//Price Charming
			if ( '-0.01' == $charming ) {
				$charming = (float) $charming;
				$price = $price + $charming;
			}

			if ( '-0.05' == $charming ) {
				$charming = (float) $charming;
				$price = $price + $charming;
			}

			if ( '-0.10' == $charming ) {
				$charming = (float) $charming;
				$price = $price + $charming;
			}

			return $price;
		}

		public function wccs_get_currency_decimals() {
			$decimals = wc_get_price_decimals();
			$info     = $this->currency_info;
			if ( isset( $info['decimals'] ) ) {
				$decimals = $info['decimals'];
			}
			return $decimals;
		}

		public function wcc_switcher_shortcode_callback( $atts ) {

			if ( '1' !=  get_mulltisite_or_site_option('wccs_zp_toggle', false) ) {
				ob_start();

				$args = shortcode_atts(
					array(


						'class' => '',
						'style' => '',
					),
					$atts
				);

				if ( $args['style'] && in_array( $args['style'], array( 'style_01', 'style_02', 'style_03', 'style_04' ) ) ) {
					$style = $args['style'];
				} else {
					$style = get_mulltisite_or_site_option('wccs_shortcode_style', 'style_01');
				}

				$variables                          = array();
				$variables['class']                 = $args['class'];
				$variables['default_currency']      = $this->wccs_get_default_currency();
				$variables['default_currency_flag'] = $this->wccs_get_default_currency_flag();
				$variables['default_label']         =  wccs_get_currency_label( $variables['default_currency'] );
				$variables['default_symbol']        = get_woocommerce_currency_symbol( $variables['default_currency'] );
				$variables['currencies']            = $this->wccs_get_currencies();
				$variables['currency']              = $this->wccs_get_currency();
				$variables['show_currency']         =  get_mulltisite_or_site_option('wccs_show_currency', 1);
				$variables['show_flag']             =  get_mulltisite_or_site_option('wccs_show_flag', 1);
				$variables['default_text']          = 
				/**
				 * Filter
				 * 
				 * @since 1.7.3
				 * */
				apply_filters('wccs_change_default_text', '(Default)');

				$this->render_template( WCCS_PLUGIN_PATH . 'templates/' . $style . '.php', $variables );

				return ob_get_clean();
			}
		}

		public function wcc_rates_shortcode_callback( $atts ) {

			if ( '1' !=  get_mulltisite_or_site_option('wccs_zp_toggle', false) ) {
				ob_start();

				$args = shortcode_atts(
					array(
						'class' => '',
					),
					$atts
				);

				$variables                          = array();
				$variables['class']                 = $args['class'];
				$variables['default_currency']      = $this->wccs_get_default_currency();
				$variables['default_currency_flag'] = $this->wccs_get_default_currency_flag();
				$variables['default_label']         =  wccs_get_currency_label( $variables['default_currency'] );
				$variables['default_symbol']        = get_woocommerce_currency_symbol( $variables['default_currency'] );
				$variables['currencies']            = $this->wccs_get_currencies();
				$variables['currency']              = $this->wccs_get_currency();
				$variables['show_currency']         =  get_mulltisite_or_site_option('wccs_show_currency', 1);
				$variables['show_flag']             =  get_mulltisite_or_site_option('wccs_show_flag', 1);
				$variables['default_text']          = 
				/**
				 * Filter
				 * 
				 * @since 1.7.3
				 * */
				apply_filters('wccs_change_default_text', '(Default)');

				$this->render_template( WCCS_PLUGIN_PATH . 'templates/rates.php', $variables );

				return ob_get_clean();
			}
		}

		public function wccs_widgets() {
			register_widget( '\Wpexperts\CurrencySwitcherForWoocommerce\SwitcherWidget' );
		}

		public function wccs_get_nav_menu_items_filter( $items, $menu, $args ) {
			/**
			 * Filter.
			 * 
			 * @since 1.8.0
			 */
			if ( ! apply_filters('wccs_before_nav_menu', true) ) {
				return $items;
			}

			// Compatible with Wholesale
			$user_info = get_userdata( get_current_user_id() );
			if ( isset($user_info->roles) ) {
				$user_role = implode( ', ', (array) $user_info->roles );
				$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
				if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
					if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
						$term_id = $wholesale_role['term_id'];
						if ( 'yes' === get_term_meta( $term_id, 'wwp_user_role_currency', true ) ) {
							return $items;
						}
					}
				}
			}
			

			if ( is_customize_preview() ) {
				return $items;
			}

			// if ( is_account_page() ) { //donot show on account page of user
			//  return $items;
			// }

			if (  get_mulltisite_or_site_option('wccs_show_in_menu', 0) && ! is_admin() && '1' !=  get_mulltisite_or_site_option('wccs_zp_toggle', false) ) {
				$toAdd            = array();
				$target_menu      =  get_mulltisite_or_site_option('wccs_switcher_menu', '');
				$show_flag        =  get_mulltisite_or_site_option('wccs_show_flag', 1);
				$show_currency    =  get_mulltisite_or_site_option('wccs_show_currency', 1);
				$currencies       = $this->wccs_get_currencies();
				$currency         = $this->wccs_get_currency();
				$default_currency = $this->wccs_get_default_currency();
				$default_label    =  wccs_get_currency_label( $default_currency );
				$default_symbol   = get_woocommerce_currency_symbol( $default_currency );
				$counter          = count( $items ) + 1;
				$title            = '';

				if ( count( $currencies ) && $menu->slug == $target_menu ) {
					$item = array();

					$item['ID']                    = 'wcss_' . $default_currency;
					$item['db_id']                 = 'wcss_' . $default_currency;
					$item['object_id']             = 'wcss_' . $default_currency;
					$item['default_currency_flag'] = $this->wccs_get_default_currency_flag();
					$item['object']                = 'wcss_menu_item';
					$item['type']                  = 'wcss_menu_item';
					$item['menu_order']            = $counter;
					$item['target']                = '';
					$item['xfn']                   = '';
					$item['wcc_id']                = 'wcss_' . $default_currency;
					if ( $currency ) {
						$item['menu_item_parent'] = 'wcss_' . $currency;
					} else {
						$item['menu_item_parent'] = '';
					}
					$item['classes']   = array( 'menu-item', 'wccs-click-for-menu', 'wccs-menu-item', 'wccs-menu-item-' . $default_currency );
					$item['post_type'] = 'nav_menu_item';
					/**
					 * Filter
					 * 
					 * @since 1.7.3
					 */
					$title = apply_filters('wccs_change_menu_default_currency_label', $default_label, $default_currency);

					if ( $show_currency ) {
						$title .= ' "' . $default_symbol . '" ';
					}
					// $title .= esc_html__('(Default)', 'wccs');
					if ( $show_flag ) {
						$title .= ' <span class="wcc-flag flag-icon flag-icon-' . $item['default_currency_flag'] . '"></span>';
					}
					$item['title'] = $title;
					$item['url']   = '#' . $default_currency;

					$toAdd[] = (object) $item;

					foreach ( $currencies as $code => $info ) {
						$counter++;
						$item = array();

						$item['ID']         = 'wcss_' . $code;
						$item['db_id']      = 'wcss_' . $code;
						$item['object_id']  = 'wcss_' . $code;
						$item['object']     = 'wcss_menu_item';
						$item['type']       = 'wcss_menu_item';
						$item['menu_order'] = $counter;
						$item['target']     = '';
						$item['xfn']        = '';
						$item['wcc_id']     = 'wcss_' . $default_currency;
						if ( $currency ) {
							if ( $currency != $code ) {
								$item['menu_item_parent'] = 'wcss_' . $currency;
							} else {
								$item['menu_item_parent'] = '';
							}
						} else {
							$item['menu_item_parent'] = 'wcss_' . $default_currency;
						}
						$item['classes']   = array( 'menu-item', 'wccs-click-for-menu', 'wccs-menu-item', 'wccs-menu-item-' . $code );
						$item['post_type'] = 'nav_menu_item';
						/**
						 * Filter
						 * 
						 * @since 1.0.0
						 */
						$title             = apply_filters('wccs_change_default_currency_label', $info['label']);
						if ( $show_currency ) {
							$title .= ' "' . $info['symbol'] . '"';
						}

						if ( $show_flag ) {
							$title .= ' <span class="wcc-flag flag-icon flag-icon-' . $info['flag'] . '"></span>';
						}
						$item['title'] = $title;
						$item['url']   = '#' . $code;

						$toAdd[] = (object) $item;
					}

					return array_merge( $items, $toAdd );
				}
			}

			return $items;
		}

		public function render_template( $template_path, $data = array() ) {
			extract( $data );
			$theme_template_path = get_stylesheet_directory() . '/wccs/templates/' . basename( $template_path );

			if ( file_exists( $theme_template_path ) ) {
				require $theme_template_path; // nosemgrep: audit.php.lang.security.file.inclusion-arg
			} else {
				require $template_path; // nosemgrep: audit.php.lang.security.file.inclusion-arg
			}
		}

		public function wccs_add_sticky_callback() {
			
			/**
			 * Filter.
			 * 
			 * @since 1.6.2
			 */
			if ( ! apply_filters('wccs_before_sticky_swticher', true) ) {
				return false;
			}

			// Compatible with Wholesale
			$user_info = get_userdata( get_current_user_id() );
			if ( isset($user_info->roles) ) {
				$user_role = implode( ', ', (array) $user_info->roles );
				$wholesale_role = term_exists( $user_role, 'wholesale_user_roles' );
				if ( 0 !== $wholesale_role && null !== $wholesale_role ) {
					if ( is_array( $wholesale_role ) && isset( $wholesale_role['term_id'] ) ) {
						$term_id = $wholesale_role['term_id'];
						if ( 'yes' === get_term_meta( $term_id, 'wwp_user_role_currency', true ) ) {
							return false;
						}
					}
				}
			}
			if (  get_mulltisite_or_site_option('wccs_sticky_switcher', 0) && '1' !=  get_mulltisite_or_site_option('wccs_zp_toggle', false) ) {
		
					/**
					 * Filter.
					 *
					 * @since 1.0
					 */
					$stickey_switcher = apply_filters('wccs_sticky_switcher_enable', true);

				$default_currency = $this->wccs_get_default_currency();
				// $default_currency = 'USD';
				$default_label    =  wccs_get_currency_label( $default_currency );
				$currencies = $this->wccs_get_currencies();
				$currency   = $this->wccs_get_currency();
				$show_flag =  get_mulltisite_or_site_option('wccs_show_flag', 1);

				if ( count( $currencies ) ) {

					wp_enqueue_style( 'wccs_flags_style', WCCS_PLUGIN_URL . 'assets/lib/flag-icon/flag-icon.css', '', '1.0' );
					wp_enqueue_style( 'wccs_slick_css', WCCS_PLUGIN_URL . 'assets/frontend/css/wccs_slick.css', '', '1.0' );
					wp_enqueue_style( 'wccs_sticky_css', WCCS_PLUGIN_URL . 'assets/frontend/themes/sticky/theme-05.css', '', '1.0&t=' . gmdate('dmYHis') );
					wp_enqueue_script( 'wccs_slick_script', WCCS_PLUGIN_URL . 'assets/frontend/js/wccs_slick.min.js', array( 'jquery' ), '1.0' );
					
					//Compatibility with Oxygen builder
					if (function_exists('oxygen_vsb_user_can_use_advanced_tab')) {
						if (!oxygen_vsb_user_can_use_advanced_tab()) {
							
							wp_enqueue_script('wccs_sticky_script', WCCS_PLUGIN_URL . 'assets/frontend/themes/sticky/sticky.js', array( 'jquery' ), '1.0&t=' . gmdate('dmYHis'));
						}
					} else {
						wp_enqueue_script('wccs_sticky_script', WCCS_PLUGIN_URL . 'assets/frontend/themes/sticky/sticky.js', array( 'jquery' ), '1.0&t=' . gmdate('dmYHis'));
					}

					?>
					<div id="wcc-sticky-list-wrapper" class="<?php if ( count( $currencies ) > 4 ) { ?>
					wcc-with-more<?php } ?> 
					<?php if (  get_mulltisite_or_site_option('wccs_sticky_position', 'right') == 'left' ) { ?>
					wcc-sticky-left<?php } ?>">
						<?php
						
						/**
						 * Filter
						 * 
						 * @since 1.0
						 */
						if ($stickey_switcher) :
							?>
							<div id="wccs_sticky_container" class="noMoreTop">
								<a href="#" id="wccs_sticky_up"></a>
								<ul class="wcc-sticky-list">
									<li class="d-flex sticky-def <?php if ( ! $currency ) { ?>
									crnt<?php } ?>" data-code="<?php echo esc_attr( $default_currency ); ?>">
										<span class="wcc-name"><?php echo esc_html( $default_currency ); ?></span>
										<?php if ( ! empty( $this->wccs_get_default_currency_flag() ) ) : ?>
											<span class="wcc-flag 
											<?php 
											if ( $show_flag && $this->wccs_get_default_currency_flag() ) {
												?>
												flag-icon flag-icon-<?php echo esc_attr( $this->wccs_get_default_currency_flag() ); } ?>"></span>
										<?php else : ?>
											<span class="wcc-flag"><?php echo esc_html__( 'Def', 'wccs' ); ?></span>
										<?php endif; ?>
									</li>
									<?php
									if ( isset( $currencies[$currency] ) ) {
										$selected_currency = $currencies[$currency];
										?>
										<li class="d-flex crnt" data-code="<?php echo esc_attr( $currency ); ?>">
											<span class="wcc-name"><?php echo esc_html( $currency ); ?></span>
											<span class="wcc-flag 
											<?php 
											if ( $show_flag && $selected_currency['flag'] ) {
												?>
												flag-icon flag-icon-<?php echo esc_attr( $selected_currency['flag'] ); } ?>"></span>
										</li>
										<?php
										unset( $currencies[$currency] );
									}
									foreach ( $currencies as $code => $info ) {
										?>
										<li class="d-flex <?php if ( $code == $currency ) { ?>
										crnt<?php } ?>" data-code="<?php echo esc_attr( $code ); ?>">
											<span class="wcc-name"><?php echo esc_html( $code ); ?></span>
											<span class="wcc-flag 
											<?php 
											if ( $show_flag && $info['flag'] ) {
												?>
												flag-icon flag-icon-<?php echo esc_attr( $info['flag'] ); } ?>"></span>
										</li>
										<?php
									}
									?>
								</ul>
								<a href="#" id="wccs_sticky_down"></a>
							</div>
						<?php endif ?>
					</div>
					<form class="wccs_sticky_form" method="post" action="" style="display: none;">
						<?php wp_nonce_field( '_wccsnonce', '_wccsnonce' ); ?>
						<input type="hidden" name="wcc_switcher" class="wcc_switcher" value="">
					</form>
					<?php
				}
			}
		}

		public function wccs_hide_sticky_thankyou_page( $enable ) {
			if ( is_wc_endpoint_url('order-received')  ) {
				$enable = false;
			} 
			return $enable;
		}

		public function wccs_hide_nav_menu_thankyou_page( $enable ) {
			if ( is_wc_endpoint_url('order-received')  ) {
				$enable = false;
			} 
			return $enable;
		}

		private function closestNumber( $n, $m ) {  
			// find the quotient  
			$q = (int) ( $n / $m );  
			  
			// 1st possible closest number  
			//$n1 = $m * $q;  
			  
			// 2nd possible closest number  
			$n2 = ( $n * $m ) > 0 ? ( $m * ( $q + 1 ) ) : ( $m * ( $q - 1 ) );  
			  
			// if true, then n1 is the  
			// required closest number  
			//if (abs($n - $n1) < abs($n - $n2))  
				//return $n1;  
			  
			// else n2 is the required  
			// closest number  
			return $n2;  
		}
		
		/**
		 * Method wccs_woo_product_addons
		 *
		 * @param float $price
		 * @param array $addon
		 *
		 * @return float
		 */
		public function wccs_woo_product_addons( $price, $addon ) {
			$coversion_rate = $this->wccs_get_currency_rate();
			$decimals       = $this->wccs_get_currency_decimals();
			if ( $coversion_rate ) {
				return round( ( $price * $coversion_rate ), $decimals );
			}
			return $price;
		}

		/**
		 * Singleton Instance Method to initiate class.
		 *
		 * @since 1.0
		 */
		public static function Instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new WCCS();
			}

			return self::$_instance;
		}
	}

	// $wccs            = new WCCS();
	// $GLOBALS['WCCS'] = $wccs;
}
