<?php

namespace WPFormsGoogleDrive\Api\Http;

/**
 * Wrapper class for HTTP requests.
 *
 * @since 1.0.0
 */
class Request {

	/**
	 * Access token.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Request constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $access_token Access token.
	 */
	public function __construct( string $access_token ) {

		$this->access_token = $access_token;
	}

	/**
	 * Send a POST request.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $url     URL to send the request to.
	 * @param string|array $body    Request body.
	 * @param array        $headers Non-default headers.
	 *
	 * @return Response
	 */
	public function post( string $url, $body = [], array $headers = [] ): Response {

		return $this->request( 'POST', $url, $body, $headers );
	}

	/**
	 * Send a PUT request.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $url     URL to send the request to.
	 * @param string|array $body    Request body.
	 * @param array        $headers Non-default headers.
	 *
	 * @return Response
	 */
	public function put( string $url, $body = [], array $headers = [] ): Response {

		return $this->request( 'PUT', $url, $body, $headers );
	}

	/**
	 * Perform a DELETE request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url          Target URL for the DELETE request.
	 * @param array  $query_params Query parameters to include in the request.
	 * @param array  $headers      Additional headers to include in the request.
	 *
	 * @return Response
	 */
	public function delete( string $url, array $query_params = [], array $headers = [] ): Response {

		return $this->request( 'DELETE', $url, $query_params, $headers );
	}

	/**
	 * Perform a GET request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url          Target URL for the GET request.
	 * @param array  $query_params Query parameters to include in the request.
	 * @param array  $headers      Additional headers to include in the request.
	 *
	 * @return Response
	 */
	public function get( string $url, array $query_params = [], array $headers = [] ): Response {

		return $this->request( 'GET', $url, $query_params, $headers );
	}

	/**
	 * POST request with multipart body for file uploading.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url           URL to send the request to.
	 * @param array  $meta_data     File meta data.
	 * @param string $file_contents File contents.
	 *
	 * @return Response
	 */
	public function post_multipart( string $url, array $meta_data, string $file_contents ): Response {

		$boundary = 'wpforms_google_drive_boundary-' . wp_generate_uuid4();

		$body = '--' . $boundary . "\r\n";

		$body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
		$body .= wp_json_encode( $meta_data );
		$body .= "\r\n--" . $boundary . "\r\n";
		$body .= "Content-Type: text/plain\r\n\r\n";
		$body .= $file_contents;
		$body .= "\r\n--" . $boundary . '--';

		return $this->post(
			$url,
			$body,
			[
				'Content-Type'   => 'multipart/related; boundary=' . $boundary,
				'Content-Length' => strlen( $body ),
			]
		);
	}

	/**
	 * Send a request based on method (main interface).
	 *
	 * @since 1.0.0
	 *
	 * @param string       $method  Request method.
	 * @param string       $url     Request URL.
	 * @param string|array $body    Body.
	 * @param array        $headers Headers.
	 *
	 * @return Response
	 */
	private function request( string $method, string $url, $body, array $headers = [] ): Response {

		$options['headers'] = $this->get_default_headers();

		if ( $headers ) {
			$options['headers'] = array_merge( $options['headers'], $headers );
		}

		if ( in_array( $method, [ 'GET', 'DELETE' ], true ) ) {
			$url = ! empty( $body ) ? add_query_arg( $body, $url ) : $url;
		} else {
			$options['body'] = is_array( $body ) ? wp_json_encode( $body ) : $body;
		}

		$options['method'] = $method;

		/**
		 * Filter a request options array before it's sent.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $options  Request options.
		 * @param string  $method   Request method.
		 * @param string  $url      Request URL.
		 * @param Request $instance Instance of Request class.
		 */
		$options = (array) apply_filters( 'wpforms_google_drive_api_http_request_options', $options, $method, $url, $this );

		// Retrieve the raw response from a safe HTTP request.
		$response = wp_safe_remote_request( $url, $options );
		$body     = wp_remote_retrieve_body( $response );

		$response = new Response( $response );

		if ( $response->has_errors() ) {
			wpforms_log(
				'Request to Google Drive API was failed',
				[
					'message' => $body,
					'request' => [
						'method' => $method,
						'url'    => $url,
					],
				],
				[
					'type' => [ 'provider', 'error' ],
				]
			);
		}

		return $response;
	}

	/**
	 * Retrieve default headers for request.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_default_headers(): array {

		return [
			'Authorization' => 'Bearer ' . $this->access_token,
			'Accept'        => 'application/json',
			'Connection'    => 'keep-alive',
			'Content-Type'  => 'application/json',
		];
	}
}
