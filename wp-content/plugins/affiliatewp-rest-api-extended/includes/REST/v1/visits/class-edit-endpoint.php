<?php
namespace AffWP\Visit\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-visits-endpoints.php';
}

/**
 * Implements a REST endpoint for editing a visit.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Visit\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating a visit.
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
				return current_user_can( 'manage_visits' );
			},
		) );
	}

	/**
	 * Endpoint to update a visit.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$visit_id = $request['id'];

		// Explicitly pass the visit ID.
		$request->set_param( 'visit_id', $visit_id );

		// Update the visit.
		if ( ! affiliate_wp()->visits->update( $visit_id, $request->get_params(), '', 'visit' ) ) {
			return new \WP_Error(
				'affwp_rest_update_visit_error',
				__( 'The visit could not be updated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		if ( ! $visit = affwp_get_visit( $visit_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_visit_error',
				__( 'The visit could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		// Set the updated flag for the response object.
		$visit->set( 'updated', true );

		/**
		 * Fires immediately after a visit has been updated via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Visit     $visit    Visit object.
		 * @param \WP_REST_Request $request  Request.
		 */
		do_action( 'affwp_rest_update_visit', $visit, $request );

		$response = affiliate_wp()->visits->REST->process_for_output( $visit, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $visit_id ) ) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single visit, conforming to JSON Schema.
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

		$schema['properties']['visit_id']['required'] = true;

		return $schema;
	}

}
