<?php
/**
 * Antom_Admin
 *
 * @user Antom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Antom_Admin {

	protected static $_instance = null;

	protected function __construct() {
	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function maybe_render_welcome() {
		if ( ! antom_is_active() ) {
			$connect = antom_request( 'connect', false, 'bool' );
			if ( ! $connect ) {
				$this->render_welcome();
			}
		}
	}

	private function render_welcome() {
		$section = antom_request( 'section' );
		if ( $section ) {
			$validate_sections = array( 'antom-payment-gateway' );
			$methods_settings  = antom_get_payment_methods();
			$validate_sections = array_merge( $validate_sections, array_column( $methods_settings, 'slug' ) );
			if ( in_array( $section, $validate_sections ) && ! antom_is_active() ) {
				?>
				<div class="antom-payment-gateways-welcome">
					<div class="antom-payment-gateways-welcome-inner">
						<div class="antom-payment-gateways-welcome-inner-header">
							<a>
								<img src="<?php echo esc_html( ANTOM_PAYMENT_GATEWAYS_URL ) . 'assets/images/antom.png'; ?>"/>
							</a>
						</div>
						<div class="antom-payment-gateways-welcome-inner-section">
							<h1><?php echo esc_html( __( 'Get Start with Antom', 'antom-payments' ) ); ?></h1>
							<p class='antom-payment-gateways-inner-tips'>
							<?php
							echo esc_html(
								__(
									'Create your own Antom account for WooCommerce business and start to accept payments for your store. ',
									'antom-payments'
								)
							);
							?>
							</p>
							<p>
                            <?php
                            echo wp_kses(
                                __(
                                    '<span style="font-size: 14px;"><em>Please notice that Antom account for WooCommerce business has to be created by clicking the "Register new account" button below. Account registered in other channels can\'t be used for WooCommerce business.</em></span>',
                                    'antom-payments'
                                ),
                                array(
                                    'em' => array(),  // 允许 <em> 标签
                                    'span' => array( 'style' => true )  // 允许 <span> 标签并支持 style 属性
                                )
                            );
                            ?>
                            </p>

							<div class="antom-payment-gateways-welcome-inner-section-footer">
								<a class="register-button" href="
								<?php
								echo esc_html( Antom_Payment_Gateways_Statement::ANTOM_REGESITER_URL );
								?>
									" target="_blank">
								<?php
								echo esc_html( __( 'Register new account', 'antom-payments' ) )
								?>
								</a>
								<?php
									$link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_SLUG . '&connect=true' )
								?>
								<a class="active-antom-button" href="<?php echo esc_url( $link ); ?>">
									<img src="<?php echo esc_html( ANTOM_PAYMENT_GATEWAYS_URL ) . 'assets/images/active.svg'; ?>"/>
									<span>
								<?php
								echo esc_html(
									__(
										'Connect your account',
										'antom-payments'
									)
								);
								?>
											</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
	}

	public function maybe_render_setting_page() {
		$connect = antom_request( 'connect', false, 'bool' );
		if ( antom_is_active() || $connect ) {
			$this->render_setting_page();
		}
	}

	private function render_setting_page() {
		$section = antom_request( 'section' );
		if ( 'antom-payment-gateway' === $section ) {
			?>
			<h2> Antom Payment Gateway 
			<?php
			wc_back_link(
				__( 'Return to payments', 'antom-payments' ),
				admin_url( 'admin.php?page=wc-settings&tab=checkout' )
			);
			?>
					</h2>
			<p>
			<?php
			echo esc_html(
				__(
					'Visit Antom Portal to view your account, reconcile transactions, and process refunds, and more. ',
					'antom-payments'
				)
			) . ' ' . sprintf(
				'<a href="%s">%s</a>',
				esc_url('https://dashboard.alipay.com/global-payments/account/register?goto=https%3A%2F%2Fdashboard.alipay.com%2Fglobal-payments%2Fhome&bizMode=ISV_SELF_BUILD'),
				esc_html__('register account', 'antom-payments')
			);
			
			?>
					</p>
			<?php
			antom_init_setting_menus( $section );

			$form_fields         = antom_get_core_setting_form_fields();
			$form_api            = new Antom_Payment_Gateways_Form_Api();
			$antom_core_settings = antom_load_core_setting();

			$is_test_mode = 1 === intval( $antom_core_settings['test_mode'] );
			if ( ! $is_test_mode ) {
				foreach ( $form_fields as $key => &$field ) {
					if ( in_array( $key, array( 'test_clientid', 'test_public_key', 'test_private_key', 'test_currency' ) ) ) {
						$field['tr_class'] = 'antom-hide-tr-line';
					}
				}
			}

			?>
			<table class="form-table"><?php $form_api->generate_settings_html( $form_fields ); ?></table>
			<?php
		}
	}

	public function maybe_save_core_settings() {
		$section = antom_request( 'section', '' );
		if ( antom_is_post_request() && Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_SLUG == $section ) {
			$core_settings = array(
				'clientid'         => antom_request( 'woocommerce__clientid', '' ),
				'public_key'       => antom_request( 'woocommerce__public_key', '' ),
				'private_key'      => antom_request( 'woocommerce__private_key', '' ),
				'test_mode'        => antom_request( 'woocommerce__test_mode', 'no' ),
				'test_clientid'    => antom_request( 'woocommerce__test_clientid', '' ),
				'test_public_key'  => antom_request( 'woocommerce__test_public_key', '' ),
				'test_private_key' => antom_request( 'woocommerce__test_private_key', '' ),
				'prod_currency'    => antom_request( 'woocommerce__prod_currency', '' ),
				'test_currency'    => antom_request( 'woocommerce__test_currency', '' ),
			);

			if ( 'yes' === $core_settings['test_mode'] || '1' === $core_settings['test_mode'] ) {
				$core_settings['test_mode'] = 1;
			} else {
				$core_settings['test_mode'] = 0;
			}

			// validate the currency must be in the list of currencies settings,if not ,it will be removed.
			$currency_settings = antom_get_settlement_currencies();
			if ( ! in_array( $core_settings['prod_currency'], $currency_settings ) ) {
				$core_settings['prod_currency'] = '';
			}
			if ( ! in_array( $core_settings['test_currency'], $currency_settings ) ) {
				$core_settings['test_currency'] = '';
			}

			/**
			 * Add a filter hook to change the core settings values when it is saved.
			 *
			 * @since 1.0.0
			 */
			$core_settings = apply_filters( 'antom_payment_gateways_save_core_settings_values', $core_settings );
			Antom_Payment_Gateways_Options::get_instance()->set_option(
				Antom_Payment_Gateways_Statement::ANTOM_CORE_SETTING_FIELD,
				$core_settings
			);

			if ( ! antom_is_active() ) {
				Antom_Payment_Gateways_Options::get_instance()->set_option(
					Antom_Payment_Gateways_Statement::ANTOM_ACTIVE_STATE_FIELD,
					Antom_Payment_Gateways_Statement::ANTOM_ACTIVE_STATE
				);
			}
		}
	}

	public function add_antom_filter_by_extra_tablenav( $order_type, $which ) {
		$payment_gateways         = new WC_Payment_Gateways();
		$available_gateways       = $payment_gateways->get_available_payment_gateways();
		$has_antom_payment_method = false;
		foreach ( $available_gateways as $available_gateway ) {
			if ( strpos( $available_gateway->id, 'antom_' ) !== false ) {
				$has_antom_payment_method = true;
				break;
			}
		}
		$payment_method = antom_request( 'payment_method' );

		if ( $has_antom_payment_method && 'top' == $which ) {
			?>
			<select name="payment_method" id="filter-by-payment-method">
				<option value="">
				<?php
				echo esc_html(
					__(
						'Filter All Antom payment methods',
						'antom-payments'
					)
				);
				?>
						</option>
				<?php
				foreach ( $available_gateways as $available_gateway ) {
					if ( strpos( $available_gateway->id, 'antom_' ) !== false ) {
						?>
						<option <?php echo esc_html( selected( $available_gateway->id, $payment_method, false ) ); ?>
								value="<?php echo esc_html( $available_gateway->id ); ?>"><?php echo esc_html( $available_gateway->method_title ); ?></option>
						<?php
					}
				}
				?>
			</select>
			<?php
		}
	}

	public function woocommerce_order_list_table_prepare_items_query_args( $order_query_args ) {
		$payment_method = antom_request( 'payment_method', '' );
		if ( $payment_method ) {
			$order_query_args['payment_method'] = $payment_method;
		}

		return $order_query_args;
	}

	public function append_antom_payment_method( $buyer, $order ) {
		$payment_method = $order->get_payment_method();
		if ( strpos( $payment_method, 'antom_' ) === 0 ) {
			$buyer .= ' ( ' . __(
				'pay via',
				'antom-payments'
			) . ' ' . $order->get_payment_method_title() . ' ) ';
		}

		return $buyer;
	}

	public function antom_order_abnormal_warning( $order ) {
		$order         = wc_get_order( $order->get_id() );
		$abnormal_logs = $order->get_meta( Antom_Payment_Gateways_Statement::ANTOM_NOTIFY_ABNORMAL_KEY, false );
		foreach ( $abnormal_logs as $abnormal_log ) {
			$abnormal_info_data = $abnormal_log->get_data();
			echo '<p class="abnormal-warning"><strong>' . esc_html( $abnormal_info_data['value'] ) . '</strong></p>';
		}
	}
}
