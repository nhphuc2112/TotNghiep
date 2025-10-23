<?php
namespace AffWP\Affiliate\Payout\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-payouts-endpoints.php';
}

/**
 * Implements a REST endpoint for creating a payout.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\Payout\REST\v1\Endpoints
 */
class Create_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for creating a payout.
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
				return current_user_can( 'manage_referrals' );
			},
		) );
	}

	/**
	 * Endpoint to generate a new payout.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if creation failed.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['payout_id'] ) ) {
			return new \WP_Error(
				'affwp_rest_payout_exists',
				__( 'Cannot create existing payout.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $request['referrals'] ) ) {
			return new \WP_Error(
				'affwp_rest_payout_referrals_error',
				__( 'The referrals parameter cannot be empty.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		// Generate the payout.
		$payout_id = affwp_add_payout( $request->get_params() );

		if ( ! $payout_id ) {
			return new \WP_Error(
				'affwp_rest_add_payout_error',
				__( 'The payout could not be generated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		$payout = affwp_get_payout( $payout_id );

		/**
		 * Fires immediately after a payout has been generated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Affiliate\Payout $payout  Payout object.
		 * @param \WP_REST_Request        $request Request.
		 */
		do_action( 'affwp_rest_add_payout', $payout, $request );

		$response = affiliate_wp()->affiliates->payouts->REST->process_for_output( $payout, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $payout_id ) ) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single payout, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// Params unavailable when adding.
		foreach ( array( 'payout_id', 'owner', 'date' ) as $field_id ) {
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		// Required fields.
		$schema['properties']['affiliate_id']['required'] = true;
		$schema['properties']['referrals']['required'] = true;

		return $schema;
	}

}
