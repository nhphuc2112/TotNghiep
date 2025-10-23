<?php
namespace AffWP\Affiliate\Payout\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-payouts-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting a payout.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Affiliate\Payout\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting a payout.
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
				return current_user_can( 'manage_payouts' );
			},
		) );
	}

	/**
	 * Endpoint to delete a payout.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$payout_id = $request['id'];

		$request->set_param( 'payout_id', $payout_id );

		if ( ! $old_payout = affwp_get_payout( $payout_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_payout_error',
				__( 'The payout could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->affiliates->payouts->REST->process_for_output( $old_payout, $request );

		// Attempt to delete the payout.
		$deleted = affwp_delete_payout( $payout_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_payout_error',
				__( 'The payout could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a payout has been deleted via REST.
		 *
		 * @since 2.0
		 *
		 * @param \AffWP\Affiliate\Payout  $old_payout Old payout object.
		 * @param \WP_REST_Request         $request      Request.
		 */
		do_action( 'affwp_rest_delete_payout', $old_payout, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single payout, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// All params other than 'payout_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'payout_id' === $field_id ) {
				$schema['properties']['payout_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
