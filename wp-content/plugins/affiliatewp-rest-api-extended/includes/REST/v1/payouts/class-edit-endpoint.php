<?php
namespace AffWP\Affiliate\Payout\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-payouts-endpoints.php';
}

/**
 * Implements a REST endpoint for editing a payout.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\Payout\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating a payout.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_item' ),
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_payouts' );
			},
		) );
	}

	/**
	 * Endpoint to update a payout.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$payout_id = $request['id'];

		// Explicitly pass the payout ID.
		$request->set_param( 'payout_id', $payout_id );

		// Update the payout.
		if ( ! affiliate_wp()->affiliates->payouts->update( $payout_id, $request->get_params(), '', 'payout' ) ) {
			return new \WP_Error(
				'affwp_rest_update_payout_error',
				__( 'The payout could not be updated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		if ( ! $payout = affwp_get_payout( $payout_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_payout_error',
				__( 'The payout could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		// Set the updated flag for the response object.
		$payout->set( 'updated', true );

		/**
		 * Fires immediately after a payout has been updated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Affiliate\Payout $payout  Payout object.
		 * @param \WP_REST_Request        $request Request.
		 */
		do_action( 'affwp_rest_update_payout', $payout, $request );

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

		// Params unavailable when editing.
		$schema['properties']['date_registered']['readonly'] = true;

		$schema['properties']['payout_id']['required'] = true;

		return $schema;
	}

}
