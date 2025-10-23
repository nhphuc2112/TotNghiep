<?php
namespace AffWP\Visit\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-visits-endpoints.php';
}

/**
 * Implements a REST endpoint for creating a visit.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Visit\REST\v1\Endpoints
 */
class Create_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for creating a visit.
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
				return current_user_can( 'manage_visits' );
			},
		) );
	}

	/**
	 * Endpoint to add a new visit.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if creation failed.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request->get_param( 'visit_id' ) ) ) {
			return new \WP_Error(
				'affwp_rest_visit_exists',
				__( 'Cannot create an existing visit.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		// Add the visit.
		$visit_id = affiliate_wp()->visits->add( $request->get_params() );

		if ( ! $visit_id ) {
			return new \WP_Error(
				'affwp_rest_add_visit_error',
				__( 'The visit could not be added.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		$visit = affwp_get_visit( $visit_id );

		/**
		 * Fires immediately after a visit has been added via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Visit     $visit   Visit object.
		 * @param \WP_REST_Request $request Request.
		 */
		do_action( 'affwp_rest_add_visit', $visit, $request );

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

		// Visit ID unavailable when adding.
		$schema['properties']['visit_id']['readonly'] = true;

		// Required.
		$schema['properties']['affiliate_id']['required'] = true;

		return $schema;
	}

}
