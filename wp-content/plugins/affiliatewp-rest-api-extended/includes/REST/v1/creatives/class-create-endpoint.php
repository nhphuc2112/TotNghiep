<?php
namespace AffWP\Creative\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-creatives-endpoints.php';
}

/**
 * Implements a REST endpoint for creating a creative.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Creative\REST\v1\Endpoints
 */
class Create_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for creating a creative.
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
				return current_user_can( 'manage_creatives' );
			},
		) );
	}

	/**
	 * Endpoint to add a new creative.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if creation failed.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['creative_id'] ) ) {
			return new \WP_Error(
				'affwp_rest_creative_exists',
				__( 'Cannot create existing creative.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		// Add the creative.
		$creative_id = affwp_add_creative( $request->get_params() );

		if ( ! $creative_id ) {
			return new \WP_Error(
				'affwp_rest_add_creative_error',
				__( 'The creative could not be added.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		$creative = affwp_get_creative( $creative_id );

		/**
		 * Fires immediately after a creative has been added via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Creative  $creative Creative object.
		 * @param \WP_REST_Request $request  Request.
		 */
		do_action( 'affwp_rest_add_creative', $creative, $request );

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

		// Params unavailable when adding.
		foreach ( array( 'creative_id', 'date_registered' ) as $field_id ) {
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
