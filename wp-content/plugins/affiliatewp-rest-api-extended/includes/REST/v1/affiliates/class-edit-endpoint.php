<?php
namespace AffWP\Affiliate\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-affiliates-endpoints.php';
}

/**
 * Implements a REST endpoint for editing an affiliate.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating an affiliate.
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
				return current_user_can( 'manage_affiliates' );
			},
		) );
	}

	/**
	 * Endpoint to update an affiliate.
	 *
	 * @access public
	 * @since  1.0.0
	 * @since  1.0.5 create_item now supports the flat_rate_basis field
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$affiliate_id = $request['id'];

		// Explicitly pass the affiliate ID.
		$request->set_param( 'affiliate_id', $affiliate_id );

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

		// Update the affiliate.
		if ( ! affwp_update_affiliate( $request->get_params() ) ) {
			return new \WP_Error(
				'affwp_rest_update_affiliate_error',
				__( 'The affiliate could not be updated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		if ( ! $affiliate = affwp_get_affiliate( $affiliate_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_affiliate_error',
				__( 'The affiliate could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		// Set the updated flag for the response object.
		$affiliate->set( 'updated', true );

		/**
		 * Fires immediately after an affiliate has been updated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Affiliate $affiliate Affiliate object.
		 * @param \WP_REST_Request $request   Request.
		 */
		do_action( 'affwp_rest_update_affiliate', $affiliate, $request );

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

		// Params unavailable when editing.
		foreach ( array( 'earnings', 'unpaid_earnings', 'referrals', 'visits' ) as $field_id ) {
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		$schema['properties']['affiliate_id']['required'] = true;

		// Account email can be updated here.
		$schema['properties']['account_email']['readonly'] = false;

		return $schema;
	}

}
