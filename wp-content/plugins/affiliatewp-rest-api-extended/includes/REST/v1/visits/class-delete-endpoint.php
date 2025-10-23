<?php
namespace AffWP\Visit\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-visits-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting a visit.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Visit\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting a visit.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_item' ),
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::DELETABLE ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_visits' );
			},
		) );
	}

	/**
	 * Endpoint to delete a visit.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$visit_id = $request['id'];

		$request->set_param( 'visit_id', $visit_id );

		if ( ! $old_visit = affwp_get_visit( $visit_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_visit_error',
				__( 'The visit could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->visits->REST->process_for_output( $old_visit, $request );

		// Attempt to delete the visit.
		$deleted = affwp_delete_visit( $visit_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_visit_error',
				__( 'The visit could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a visit has been deleted via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Visit     $old_visit Old visit object.
		 * @param \WP_REST_Request $request      Request.
		 */
		do_action( 'affwp_rest_delete_visit', $old_visit, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

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

		// All params other than 'visit_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'visit_id' === $field_id ) {
				$schema['properties']['visit_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
