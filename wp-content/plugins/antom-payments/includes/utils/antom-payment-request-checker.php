<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Antom_Payment_Request_Checker {

	private $order;
	private $order_id                 = 0;
	private $payment_method           = '';
	private $payment_request_key      = '';
	private $payment_request_limit    = 0;
	private $payment_request_ids      = array();
	private $successfully_payment_ids = array();

	private $error = 'error';

	/**
	 * In the status of the payment order in progress, there should be only one intermediate payment status in the database.
	 *
	 * @var array
	 */
	private $intermediate_payment_id = array();

	private $finished_state = array(
		'SUCCESS',
		'FAIL',
		'CANCELLED',
	);

	/**
	 * Antom_Payment_Request_Checker Constructor.
	 *
	 * @param mixed $order
	 */
	public function __construct( $order ) {
		$this->order                 = $order;
		$this->order_id              = $order->get_id();
		$this->payment_method        = $order->get_payment_method();
		$this->payment_request_key   = Antom_Payment_Gateways_Statement::ANTOM_REQUEST_PAYMENT_KEY;
		$this->payment_request_limit = Antom_Payment_Gateways_Statement::ANTOM_REQUEST_PAYMENT_KEY_LIMIT;
	}

	/**
	 * Load all antom payment request ids, please run this method after Constructor.
	 */
	public function load_all_payment_request_ids() {
		$payment_request_ids            = $this->order->get_meta( $this->payment_request_key, false );
		$this->successfully_payment_ids = array();
		$this->intermediate_payment_id  = array();
		$this->payment_request_ids      = array();
		if ( $payment_request_ids ) {
			foreach ( $payment_request_ids as $payment_request_id_item ) {
				$payment_request_id_data  = $payment_request_id_item->get_data();
				$payment_request_id       = $payment_request_id_data['value'];
				$meta_statement_key       = $this->payment_request_key . '_' . $payment_request_id . '_state';
				$payment_method_key       = $this->payment_request_key . '_' . $payment_request_id . '_payment_method';
				$payment_request_id_state = $this->order->get_meta( $meta_statement_key, true );
				$payment_method           = $this->order->get_meta( $payment_method_key, true );

				if ( in_array( $payment_request_id_state, $this->finished_state, true ) ) {
					if ( 'SUCCESS' === $payment_request_id_state ) {
						$this->successfully_payment_ids[] = array(
							'payment_request_id' => $payment_request_id,
							'state'              => $payment_request_id_state,
							'payment_method'     => $payment_method,
						);
					}
				} else {
					$this->intermediate_payment_id = array(
						'payment_request_id' => $payment_request_id,
						'state'              => $payment_request_id_state,
						'payment_method'     => $payment_method,
					);
				}

				$this->payment_request_ids[] = array(
					'payment_request_id' => $payment_request_id,
					'state'              => $payment_request_id_state,
					'payment_method'     => $payment_method,
				);
			}
		}
	}

	/**
	 * Determine whether there is a payment request id that has been successfully paid.
	 *
	 * @return bool
	 */
	public function has_successful_payment_request_id() {
		return ! empty( $this->successfully_payment_ids );
	}

	/**
	 * Get error.
	 *
	 * @return string errors.
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Intermediate payment or generate new payemntRequestId payment
	 *
	 * @return string errors.
	 */
	public function antom_is_generate_new_id_payment() {
		return empty( $this->intermediate_payment_id );
	}


	/**
	 * Get the payment request id of the progress status.
	 */
	public function get_intermediate_payment_request_id_info() {
		if ( empty( $this->intermediate_payment_id ) ) {
			$this->generate_payment_request_id();
		}

		return $this->intermediate_payment_id;
	}

	/**
	 * Get a payment method by payment method id.
	 *
	 * @param string $payment_request_id payment request id.
	 * @return mixed
	 */
	public function get_payment_method_with_payment_request_id( $payment_request_id ) {
		$payment_method = '';
		foreach ( $this->payment_request_ids as $payment_request_id_item ) {
			if ( $payment_request_id_item['payment_request_id'] === $payment_request_id ) {
				$payment_method = $payment_request_id_item['payment_method'];
				break;
			}
		}
		return $payment_method;
	}

	public function get_payment_status_with_payment_request_id( $payment_request_id ){
		$payment_status = '';
		foreach ( $this->payment_request_ids as $payment_request_id_item ) {
			if ( $payment_request_id_item['payment_request_id'] === $payment_request_id ) {
				$payment_status = $payment_request_id_item['state'];
				break;
			}
		}
		return $payment_status;
	}


	/**
	 * Generate an antom payment request id.
	 */
	public function generate_payment_request_id( $payment_request_id = '' ) {
		if ( count( $this->payment_request_ids ) < $this->payment_request_limit ) {

			if ( empty( $this->intermediate_payment_id ) ) {
				if ( ! $payment_request_id ) {
					$payment_request_id = antom_generate_payment_request_id( $this->order_id );
				}

				// generate an already exists payment request id is an error, stop payment
				foreach ( $this->payment_request_ids as $payment_request_id_item ) {
					if ( $payment_request_id_item['payment_request_id'] === $payment_request_id ) {
						$this->error = __( 'error in generate payment id.', 'antom-payments' );
						return;
					}
				}

				$this->order->add_meta_data( $this->payment_request_key, $payment_request_id );
				$meta_statement_key = $this->payment_request_key . '_' . $payment_request_id . '_state';
				$this->order->add_meta_data( $meta_statement_key, '' );
				$payment_method_key = $this->payment_request_key . '_' . $payment_request_id . '_payment_method';
				$this->order->add_meta_data( $payment_method_key, $this->payment_method );
				$this->order->save();

				$this->intermediate_payment_id = array(
					'payment_request_id' => $payment_request_id,
					'state'              => '',
					'payment_method'     => $this->payment_method,
				);

				$this->payment_request_ids[] = $this->intermediate_payment_id;
			} else {
				$this->error = __( 'There is a payment order in progress, and a new payment order cannot be created.', 'antom-payments' );
			}
		} else {
			$this->error = __( 'The maximum number of payment requests has been reached.', 'antom-payments' );
		}
	}

	/**
	 * Set the payment request id status of the payment order
	 *
	 * @param string $payment_request_id payment request id.
	 * @param string $state Payment status.
	 * @return void
	 */
	public function set_payment_request_id_state( $payment_request_id, $state ) {
		$meta_statement_key = $this->payment_request_key . '_' . $payment_request_id . '_state';
		$this->order->update_meta_data( $meta_statement_key, $state );
		$this->order->save();
	}

	/**
	 * Verify whether a payment status is completed.
	 *
	 * @param string $payment_request_id_state Payment status.
	 * @return bool
	 */
	public function is_finished_state( $payment_request_id_state ) {
		return in_array( $payment_request_id_state, $this->finished_state, true );
	}

	/**
	 * Actively query payment request id payment status
	 *
	 * @param string $payment_request_id Payment request id.
	 * @return string Payment status
	 */
	public function inquiry_payment_request_state( $payment_request_id ) {
		$api_host      = Antom_Payment_Gateways_Statement::ANTOM_INQUIRY_API_HOST;
		$path          = Antom_Payment_Gateways_Statement::ANTOM_INQUIRY_PATH;
		$core_settings = antom_get_core_settings();
		if ( 1 === intval( $core_settings['test_mode'] ) ) {
			$path = Antom_Payment_Gateways_Statement::ANTOM_INQUIRY_SANDBOX_PATH;
		}
		$client_id   = $core_settings['clientid'];
		$private_key = $core_settings['private_key'];
		$public_key = $core_settings['public_key'];

		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/sdk-antom-alipay-inquiry-request.php';
		$request = new Antom_Alipay_Inquiry_Request();
		$request->set_client_id( $client_id );
		$request->set_path( $path );
		$request->set_payment_request_id( $payment_request_id );

		require_once ANTOM_PAYMENT_GATEWAYS_PATH . 'includes/sdk/antom-alipay-client.php';
		$client        = new Antom_Alipay_Client( $api_host, $private_key, $public_key );
		$response_body = (array) $client->execute( $request );
		if ( isset( $response_body['paymentStatus'] ) ) {
			$this->order->update_meta_data(
				Antom_Payment_Gateways_Statement::ANTOM_REQUEST_PAYMENT_KEY . '_' . $payment_request_id . '_state',
				$response_body['paymentStatus']
			);
			$this->order->save();

			return $response_body['paymentStatus'];
		}

		return 'UNKNOWN';
	}

	/**
	 * Determine whether there is a payment request id in the payment request ids list of an order.
	 *
	 * @param string $payment_request_id payment request id.
	 * @return bool if the payment request id exists in the list. return true , else return false.
	 */
	public function check_order_has_payment_request_id( $payment_request_id ) {
		$has_payment_request_id = false;
		foreach ( $this->payment_request_ids as $payment_request_item ) {
			if ( $payment_request_item['payment_request_id'] === $payment_request_id ) {
				$has_payment_request_id = true;
				break;
			}
		}
		return $has_payment_request_id;
	}

	/**
	 *  Check whether the payment request ids number has reached the limit.
	 *
	 * @return int payment request ids number.
	 */
	public function payment_request_ids_number_is_limit() {
		$is_limit = false;
		if ( count( $this->payment_request_ids ) === $this->payment_request_limit ) {
			$is_limit = true;
			if ( isset( $this->intermediate_payment_id ['payment_request_id'] ) ) {
				$is_limit = false;
			}
		}

		return $is_limit;
	}
}
