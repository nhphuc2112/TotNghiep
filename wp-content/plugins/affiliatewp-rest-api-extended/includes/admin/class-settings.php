<?php
namespace AffWP\AddOns\REST;

/**
 * Implements a REST API settings tab.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Sets up the settings tab and saving operations.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'affwp_settings_tabs', array( $this, 'settings_tab' ) );
		add_filter( 'affwp_settings',      array( $this, 'register_settings' ) );
	}

	/**
	 * Adds the 'REST API' settings tab.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param array $tabs Settings tabs array.
	 * @return array Modified settings tabs array.
	 */
	public function settings_tab( $tabs ) {
		$tabs['rest_api'] = __( 'REST API', 'affiliatewp-rest-api-extended' );

		return $tabs;
	}

	/**
	 * Registers settings for the 'REST API' tab.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param array $settings AffiliateWP settings.
	 * @return array AffiliateWP settings with 'REST API' settings registered.
	 */
	public function register_settings( $settings ) {
		$settings['rest_api'] = array(
			'rest_api_affiliates_read' => array(
				'name'     => _x( 'Read', 'affiliates via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/affiliates/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_affiliates_create' => array(
				'name' => _x( 'Create', 'affiliates via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) wrapped in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'affiliate POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/affiliates/?user_id=20' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_affiliates_edit' => array(
				'name' => _x( 'Edit/Update', 'affiliates via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/affiliates/1?rate=20' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_affiliates_delete' => array(
				'name' => _x( 'Delete', 'affiliates via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'affiliate DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/affiliates/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_creatives_settings' => array(
				'name' => __( 'Creative Endpoints', 'affiliatewp-rest-api-extended' ),
				'type' => 'header',
			),
			'rest_api_creatives_read' => array(
				'name'     => _x( 'Read', 'creatives via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/creatives/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_creatives_create' => array(
				'name' => _x( 'Create', 'creatives via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'creative POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/creatives/?name=AffWP' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_creatives_edit' => array(
				'name' => _x( 'Edit/Update', 'creatives via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/creatives/1?name=New+Name' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_creatives_delete' => array(
				'name' => _x( 'Delete', 'creatives via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'creative DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/creatives/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_customers_settings' => array(
				'name' => __( 'Customer Endpoints', 'affiliatewp-rest-api-extended' ),
				'type' => 'header',
			),
			'rest_api_customers_read' => array(
				'name'     => _x( 'Read', 'customers via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/customers/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_customers_create' => array(
				'name' => _x( 'Create', 'customers via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) wrapped in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'customer POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/customers/?affiliate_id=123' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_customers_edit' => array(
				'name' => _x( 'Edit/Update', 'customers via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/customers/1?affiliate_id=123' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_customers_delete' => array(
				'name' => _x( 'Delete', 'customers via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'customer DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/customers/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_payouts_settings' => array(
				'name' => __( 'Payout Endpoints', 'affiliatewp-rest-api-extended' ),
				'type' => 'header',
			),
			'rest_api_payouts_read' => array(
				'name'     => _x( 'Read', 'payouts via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/payouts/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_payouts_create' => array(
				'name' => _x( 'Create', 'payouts via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) wrapped in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'payout POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/payouts/?referrals=10,11,12' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_payouts_edit' => array(
				'name' => _x( 'Edit/Update', 'payouts via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/payouts/1?status=paid' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_payouts_delete' => array(
				'name' => _x( 'Delete', 'payouts via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'payout DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/payouts/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_referrals_settings' => array(
				'name' => __( 'Referral Endpoints', 'affiliatewp-rest-api-extended' ),
				'type' => 'header',
			),
			'rest_api_referrals_read' => array(
				'name'     => _x( 'Read', 'referrals via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/referrals/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_referrals_create' => array(
				'name' => _x( 'Create', 'referrals via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) wrapped in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'referral POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/referrals/?affiliate_id=35' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_referrals_edit' => array(
				'name' => _x( 'Edit/Update', 'referrals via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/referrals/1?amount=20' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_referrals_delete' => array(
				'name' => _x( 'Delete', 'referrals via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'referral DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/referrals/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_visits_settings' => array(
				'name' => __( 'Visit Endpoints', 'affiliatewp-rest-api-extended' ),
				'type' => 'header',
			),
			'rest_api_visits_read' => array(
				'name'     => _x( 'Read', 'visits via REST', 'affiliatewp-rest-api-extended' ),
				'type'     => 'checked_checkbox',
				'callback' => array( $this, 'checked_checkbox_callback' ),
				/* translators: 'GET' (method) in code tags */
				'desc'     => sprintf( __( 'Enabled by AffiliateWP core. Cannot be disabled. Accessed via the %1$s method.', 'affiliatewp-rest-api-extended' ), '<code>GET</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/visits/1' ) . '</code>'
					) . '</p>',
			),
			'rest_api_visits_create' => array(
				'name' => _x( 'Create', 'visits via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'POST' (method) wrapped in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'visit POST', 'affiliatewp-rest-api-extended' ), '<code>POST</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/visits/?affiliate_id=35' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_visits_edit' => array(
				'name' => _x( 'Edit/Update', 'visits via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 1: 'POST' (method) in code tags, 2: 'PUT' (method), 3: 'PATCH' (method) */
				'desc' => sprintf( __( 'Enables the %1$s, %2$s, and %3$s methods.', 'affiliatewp-rest-api-extended' ), '<code>POST</code>', '<code>PUT</code>', '<code>PATCH</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/visits/1?referral_id=0' ) . '</code>'
					) . '</p>'
					. '<p>' . __( 'Send an <code>OPTIONS</code> request for more information on accepted arguments.', 'affiliate-wp' ) . '</p>',
			),
			'rest_api_visits_delete' => array(
				'name' => _x( 'Delete', 'visits via REST', 'affiliatewp-rest-api-extended' ),
				'type' => 'checkbox',
				/* translators: 'DELETE' (method) in code tags */
				'desc' => sprintf( _x( 'Enables the %1$s method', 'visit DELETE', 'affiliatewp-rest-api-extended' ), '<code>DELETE</code>' )
					/* translators: Example request URL */
					. '<p>' . sprintf( __( 'Example request: %1$s', 'affiliate-wp' ),
						'<code>' . get_rest_url( null, 'affwp/v1/visits/1' ) . '</code>'
					) . '</p>',
			),
		);

		return $settings;
	}

	/**
	 * Displays a checked, disabled checkbox.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param array $args Settings field arguments.
	 * @return string HTML markup for the special checkbox field.
	 */
	public function checked_checkbox_callback( $args ) {
		$setting_key = "affwp_settings[{$args['id']}]";
		?>
		<label for="<?php echo esc_attr( $setting_key ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $setting_key ); ?>" name="<?php echo esc_attr( $setting_key ); ?>" checked="checked" disabled="disabled">
			<?php echo $args['desc']; ?>
		</label>
		<?php
	}

}
new Settings;
