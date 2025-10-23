<?php
namespace AffWP\Referral\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-referrals-endpoints.php';
}

/**
 * Implements a REST endpoint for editing a referral.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Referral\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating a referral.
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
				return current_user_can( 'manage_referrals' );
			},
		) );
	}

	/**
	 * Endpoint to update a referral.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$referral_id = $request['id'];

		// Explicitly pass the referral ID.
		$request->set_param( 'referral_id', $referral_id );

		// Fix dates timezone - when timezone is GMT+X, Zapier sends timezone without the '+'.
		$params = $request->get_params();
		if ( isset( $params['date'] ) && preg_match( '/\d{2}:\d{2}:\d{2}\s\d{2}:\d{2}/', $params['date'] ) ) {
			$date = str_replace( ' ', '+', $params['date'] );
			$request->set_param( 'date', $date );
		}

		// Update the referral.
		if ( ! affiliate_wp()->referrals->update_referral( $referral_id, $request->get_params() ) ) {
			return new \WP_Error(
				'affwp_rest_update_referral_error',
				__( 'The referral could not be updated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		if ( ! $referral = affwp_get_referral( $referral_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_referral_error',
				__( 'The referral could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		// Set the updated flag for the response object.
		$referral->set( 'updated', true );

		/**
		 * Fires immediately after a referral has been updated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Referral  $referral Referral object.
		 * @param \WP_REST_Request $request  Request.
		 */
		do_action( 'affwp_rest_update_referral', $referral, $request );

		$response = affiliate_wp()->referrals->REST->process_for_output( $referral, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $referral_id ) ) );

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

		// Referral ID unavailable when editing.
		$schema['properties']['referral_id']['readonly'] = true;

		return $schema;
	}

}
