<?php
namespace AffWP\Referral\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-referrals-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting a referral.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Referral\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting a referral.
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
				return current_user_can( 'manage_referrals' );
			},
		) );
	}

	/**
	 * Endpoint to delete a referral.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$referral_id = $request['id'];

		$request->set_param( 'referral_id', $referral_id );

		if ( ! $old_referral = affwp_get_referral( $referral_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_referral_error',
				__( 'The referral could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->referrals->REST->process_for_output( $old_referral, $request );

		// Attempt to delete the referral.
		$deleted = affwp_delete_referral( $referral_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_referral_error',
				__( 'The referral could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a referral has been deleted via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Referral  $old_referral Old referral object.
		 * @param \WP_REST_Request $request      Request.
		 */
		do_action( 'affwp_rest_delete_referral', $old_referral, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single referral, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// All params other than 'referral_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'referral_id' === $field_id ) {
				$schema['properties']['referral_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
