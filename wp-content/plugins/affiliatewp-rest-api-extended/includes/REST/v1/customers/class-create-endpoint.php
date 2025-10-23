<?php
namespace AffWP\Customer\REST\v1;

if ( ! class_exists( 'Endpoints' ) ) {
	require_once AFFILIATEWP_PLUGIN_DIR . 'includes/REST/v1/class-customers-endpoints.php';
}

/**
 * Implements a REST endpoint for creating a customer.
 *
 * @since 1.0.5
 *
 * @see \AffWP\Customer\REST\v1\Endpoints
 */
class Create_Endpoint extends Endpoints {

	/**
	 * Registers the endpoint for creating customers.
	 *
	 * @since 1.0.5
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
			'callback'            => array( $this, 'create_item' ),
			'permission_callback' => function( $request ) {
				return current_user_can( 'manage_customers' );
			},
		) );
	}

	/**
	 * Endpoint to add a new customer.
	 *
	 * @since 1.0.5
	 *
	 * @param \WP_REST_Request $request Request arguments.
	 * @return \WP_REST_Response|\WP_Error Response object or \WP_Error object if creation failed.
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['customer_id'] ) ) {
			return new \WP_Error(
				'affwp_rest_customer_exists',
				__( 'Cannot create existing customer.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $request['email'] ) ) {
			return new \WP_Error(
				'affwp_rest_customer_missing_email',
				__( 'An email must be specified to create a customer.', 'affiliatewp-rest-api-extended' ),
				array(
					'status' => 400,
					'args'   => $request->get_params(),
				)
			);
		}

		if ( affiliate_wp()->customers->get_by( 'email', $request['email'] ) ) {
			return new \WP_Error(
				'affwp_rest_update_customer_email_in_use',
				__( 'The given customer email is already in use.', 'affiliatewp-rest-api-extended' ),
				array( 'status' => 403 )
			);
		}

		// Add the customer.
		$customer_id = affwp_add_customer( $request->get_params() );

		if ( ! $customer_id ) {
			return new \WP_Error(
				'affwp_rest_add_customer_error',
				__( 'The customer could not be added.', 'affiliatewp-rest-api-extended' ),
				array(
					'status'      => 500,
					'args'        => $request->get_params(),
					'customer_id' => $customer_id,
				)
			);
		}

		$customer = affwp_get_customer( $customer_id );

		/**
		 * Fires immediately after a customer has been added via REST.
		 *
		 * @since 1.0.5
		 *
		 * @param \AffWP\Customer $customer Customer object.
		 * @param \WP_REST_Request $request Request.
		 */
		do_action( 'affwp_rest_add_customer', $customer, $request );

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

		// Params unavailable when adding.
		foreach ( array( 'customer_id' ) as $field_id ) {
			$schema['properties'][ $field_id ]['readonly'] = true;
		}

		return $schema;
	}

}
