<?php
namespace AffWP\Customer\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-customers-endpoints.php';
}

/**
 * Implements a REST endpoint for editing a customer.
 *
 * @since 1.0.5
 *
 * @see \AffWP\Customer\REST\v1\Endpoints
 */
class Edit_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for editing/updating a customer.
	 *
	 * @since 1.0.5
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_item' ),
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_customers' );
			},
		) );
	}

	/**
	 * Endpoint to update a customer.
	 *
	 * @since 1.0.5
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if the update failed.
	 */
	public function update_item( $request ) {
		// 'id' has already been set-checked by this point.
		$customer_id = $request['id'];

		// Bail if the customer does not exist.
		if ( ! $customer = affwp_get_customer( $customer_id ) ) {
			return new \WP_Error(
				'affwp_rest_get_customer_not_found',
				__( 'The customer could not be found.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 404 )
			);
		}

		// Explicitly pass the customer ID.
		$request->set_param( 'customer_id', $customer_id );

		$email = $request->get_param( 'email' );

		if ( null !== $email && affiliate_wp()->customers->get_by( 'email', $email ) ) {
			return new \WP_Error(
				'affwp_rest_update_customer_email_in_use',
				__( 'The given customer email is already in use.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 403 )
			);
		}

		// Update the customer.
		if ( ! affwp_update_customer( $request->get_params() ) ) {
			return new \WP_Error(
				'affwp_rest_update_customer_error',
				__( 'The customer could not be updated.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 500 )
			);
		}

		// Set the updated flag for the response object.
		$customer->set( 'updated', true );

		/**
		 * Fires immediately after a customer has been updated via REST.
		 *
		 * @since 1.0.5
		 *
		 * @param \AffWP\Customer  $customer Customer object.
		 * @param \WP_REST_Request $request  Request.
		 */
		do_action( 'affwp_rest_update_customer', $customer, $request );

		$response = affiliate_wp()->customers->REST->process_for_output( $customer, $request );
		$response = $this->response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $customer_id ) ) );

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

		$schema['properties']['customer_id']['required'] = true;

		// Customer email can be updated here.
		$schema['properties']['email']['readonly'] = false;

		// IP and date_created are read-only.
		foreach ( array( 'ip', 'date_created' ) as $field ) {
			$schema['properties'][ $field ]['readonly'] = true;
		}

		return $schema;
	}

}
