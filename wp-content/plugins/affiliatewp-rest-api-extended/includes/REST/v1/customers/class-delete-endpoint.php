<?php
namespace AffWP\Customer\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-customers-endpoints.php';
}

/**
 * Implements a REST endpoint for deleting a customer.
 *
 * @since 1.0.5
 *
 * @see \AffWP\Customer\REST\v1\Endpoints
 */
class Delete_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for deleting a customer.
	 *
	 * @since 1.0.5
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_item' ),
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::DELETABLE ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_customers' );
			},
		) );
	}

	/**
	 * Endpoint to delete a customer.
	 *
	 * @since 1.0.5
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the deletion failed.
	 */
	public function delete_item( $request ) {
		// 'id' has already been set-checked by this point.
		$customer_id = $request['id'];

		$request->set_param( 'customer_id', $customer_id );

		if ( ! $old_customer = affwp_get_customer( $customer_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_customer_not_found',
				__( 'The customer could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		/** @var \WP_REST_Response $previous */
		$previous = affiliate_wp()->customers->REST->process_for_output( $old_customer, $request );

		// Attempt to delete the customer.
		$deleted = affwp_delete_customer( $customer_id );

		if ( ! $deleted ) {
			return new \WP_Error(
				'affwp_rest_delete_customer_error',
				__( 'The customer could not be deleted.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a customer has been deleted via REST.
		 *
		 * @since 1.0.5
		 *
		 * @param \AffWP\Customer  $old_customer Old customer object.
		 * @param \WP_REST_Request $request      Request.
		 */
		do_action( 'affwp_rest_delete_customer', $old_customer, $request );

		$response = new \WP_REST_Response;

		$response->set_data( array(
			'deleted'  => true,
			'previous' => $previous->get_data()
		) );

		return $response;
	}

	/**
	 * Retrieves the schema for a single customer, conforming to JSON Schema.
	 *
	 * @since 1.0.5
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// All params other than 'customer_id' unavailable when deleting.
		foreach ( array_keys( $schema['properties'] ) as $field_id ) {
			if ( 'customer_id' === $field_id ) {
				$schema['properties']['customer_id']['required'] = true;
				continue;
			}

			// Set everything else to readonly.
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
