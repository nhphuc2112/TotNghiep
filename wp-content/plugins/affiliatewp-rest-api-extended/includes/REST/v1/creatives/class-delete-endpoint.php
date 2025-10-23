<?php
namespace AffWP\Creative\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-creatives-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting a creative.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Creative\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting a creative.
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
				return current_user_can( 'manage_creatives' );
			},
		) );
	}

	/**
	 * Endpoint to delete a creative.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$creative_id = $request['id'];

		$request->set_param( 'creative_id', $creative_id );

		if ( ! $old_creative = affwp_get_creative( $creative_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_creative_error',
				__( 'The creative could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->creatives->REST->process_for_output( $old_creative, $request );

		// Attempt to delete the creative.
		$deleted = affwp_delete_creative( $creative_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_creative_error',
				__( 'The creative could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a creative has been deleted via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Creative  $old_creative Old creative object.
		 * @param \WP_REST_Request $request      Request.
		 */
		do_action( 'affwp_rest_delete_creative', $old_creative, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

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

		// All params other than 'creative_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'creative_id' === $field_id ) {
				$schema['properties']['creative_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
