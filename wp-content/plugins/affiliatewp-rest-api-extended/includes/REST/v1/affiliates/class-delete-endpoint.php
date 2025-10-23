<?php
namespace AffWP\Affiliate\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-affiliates-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting an affiliate.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting an affiliate.
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
				return current_user_can( 'manage_affiliates' );
			},
		) );
	}

	/**
	 * Endpoint to delete an affiliate.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$affiliate_id = $request['id'];

		$request->set_param( 'affiliate_id', $affiliate_id );

		if ( ! $old_affiliate = affwp_get_affiliate( $affiliate_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_affiliate_error',
				__( 'The affiliate could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->affiliates->REST->process_for_output( $old_affiliate, $request );

		// Attempt to delete the affiliate.
		$deleted = affwp_delete_affiliate( $affiliate_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_affiliate_error',
				__( 'The affiliate could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after an affiliate has been deleted via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Affiliate $old_affiliate Old affiliate object.
		 * @param \WP_REST_Request $request       Request.
		 */
		do_action( 'affwp_rest_delete_affiliate', $old_affiliate, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

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

		// All params other than 'affiliate_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'affiliate_id' === $field_id ) {
				$schema['properties']['affiliate_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
