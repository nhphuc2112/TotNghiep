<?php
namespace AffWP\Creative\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-creatives-endpoints.php';
}

/**
 * Implements a REST endpoint for editing a creative.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Creative\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating a creative.
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
				return current_user_can( 'manage_creatives' );
			},
		) );
	}

	/**
	 * Endpoint to update a creative.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$creative_id = $request['id'];

		// Explicitly pass the creative ID.
		$request->set_param( 'creative_id', $creative_id );

		// Update the creative.
		if ( ! affwp_update_creative( $request->get_params() ) ) {
			return new \WP_Error(
				'affwp_rest_update_creative_error',
				__( 'The creative could not be updated.', 'affiliatewp-rest-api' ),
				array( 'status' => 500 )
			);
		}

		if ( ! $creative = affwp_get_creative( $creative_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_creative_error',
				__( 'The creative could not be found.', 'affiliatewp-rest-api' ),
				array( 'status' => 404 )
			);
		}

		// Set the updated flag for the response object.
		$creative->set( 'updated', true );

		/**
		 * Fires immediately after a creative has been updated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Creative  $creative Creative object.
		 * @param \WP_REST_Request $request  Request.
		 */
		do_action( 'affwp_rest_update_creative', $creative, $request );

		$response = affiliate_wp()->creatives->REST->process_for_output( $creative, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $creative_id ) ) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single creative, conforming to JSON Schema.
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

		$schema['properties']['creative_id']['required'] = true;

		return $schema;
	}

}
