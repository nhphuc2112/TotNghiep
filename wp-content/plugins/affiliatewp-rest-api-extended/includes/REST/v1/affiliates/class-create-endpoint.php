<?php
namespace AffWP\Affiliate\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-affiliates-endpoints.php';
}

/**
 * Implements a REST endpoint for creating an affiliate.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\REST\v1\Endpoints
 */
class Create_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for creating affiliates.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
			'callback'            => array( $this, 'create_item' ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_affiliates' );
			},
		) );
	}

	/**
	 * Endpoint to add a new affiliate.
	 *
	 * @access public
	 * @since  1.0.0
	 * @since  1.0.5 create_item now supports the flat_rate_basis field
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if creation failed.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['affiliate_id'] ) ) {
			return new \WP_Error(
				'affwp_rest_affiliate_exists',
				__( 'Cannot create existing affiliate.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		// Convert object params to their string counterparts if set.
		foreach ( array( 'payment_email', 'rate', 'rate_type', 'flat_rate_basis' ) as $field ) {
			if ( ! $request->offsetExists( $field ) ) {
				continue;
			}

			if ( ! empty( $request[ $field ]->raw ) ) {
				$request->set_param( $field, $request[ $field ]->raw );
			} else {
				$request->offsetUnset( $field );
			}
		}

		if ( ! empty( $request['create_user'] ) && $request->offsetExists( 'payment_email' ) ) {
			$user_id = $this->create_user( $request );

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			} else {
				$request->set_param( 'user_id', $user_id );
			}
		}

		// Unset create_user.
		$request->offsetUnset( 'create_user' );

		if ( empty( $request['user_id'] ) ) {
			return new \WP_Error(
				'affwp_rest_affiliate_missing_user',
				__( 'A user_id must be specified to create an affiliate.', 'affiliatewp-rest-api-extended' ),
				array(
					'status' => 400,
					'args'   => $request->get_params(),
				)
			);
		}

		// Fix dates timezone - when timezone is GMT+X, Zapier sends timezone without the '+'.
		$params = $request->get_params();
		if ( isset( $params['date_registered'] ) && preg_match( '/\d{2}:\d{2}:\d{2}\s\d{2}:\d{2}/', $params['date_registered'] ) ) {
			$date = str_replace( ' ', '+', $params['date_registered'] );
			$request->set_param( 'date_registered', $date );
		}

		// Add the affiliate.
		$affiliate_id = affwp_add_affiliate( $request->get_params() );

		if ( ! $affiliate_id ) {
			return new \WP_Error(
				'affwp_rest_add_affiliate_error',
				__( 'The affiliate could not be added.', 'affiliatewp-rest-api-extended' ),
				array(
					'status'       => 500,
					'args'         => $request->get_params(),
					'affiliate_id' => $affiliate_id
				)
			);
		}

		$affiliate = affwp_get_affiliate( $affiliate_id );

		/**
		 * Fires immediately after an affiliate has been added via REST.
		 *
		 * @since 1.0.0
		 *
		 * @param \AffWP\Affiliate $affiliate Affiliate object.
		 * @param \WP_REST_Request $request   Request.
		 */
		do_action( 'affwp_rest_add_affiliate', $affiliate, $request );

		$response = affiliate_wp()->affiliates->REST->process_for_output( $affiliate, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $affiliate_id ) ) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single affiliate, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// Params unavailable when adding.
		foreach ( array( 'affiliate_id', 'earnings', 'unpaid_earnings', 'referrals', 'visits' ) as $field_id ) {
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		// Parameter for creating a user.
		$schema['properties']['create_user'] = array(
			'description'       => __( 'Whether to create a user account for the new affiliate. payment_email is required for creating a user.', 'affiliatewp-rest-api-extended' ),
			'validate_callback' => function( $param, $request, $key ) {
				return is_string( $param );
			}
		);

		return $schema;
	}

	/**
	 * Creates a user account for the benefit of adding a new affiliate.
	 *
	 * @access protected
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return int|\WP_Error User ID or WP_Error on failure.
	 */
	protected function create_user( $request ) {
		if ( empty( $request['payment_email'] ) ) {
			$user_id = new \WP_Error(
				'affwp_rest_affiliate_create_user_error',
				__( 'A payment email value is required to create a user when adding a new affiliate.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		} else {

			if ( ! empty( $request['username'] ) ) {
				// Create a username from the username parameter, if provided.
				$user_login = sanitize_user( $request['username'] );
			} else {
				// If no username parameter is provided, create a username from the payment email instead.
				$user_login = sanitize_user( $request['payment_email'] );
			}

			$user_id = wp_insert_user( array(
				'user_login' => $user_login,
				'user_email' => sanitize_email( $request['payment_email'] ),
				'user_pass'  => wp_generate_password( 20, false ),
			) );
		}

		if ( is_wp_error( $user_id ) ) {
			$user_id->add_data( array( 'status' => 400 ) );
		}

		return $user_id;
	}

}
