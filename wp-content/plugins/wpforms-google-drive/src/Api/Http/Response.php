<?php

namespace WPFormsGoogleDrive\Api\Http;

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WP_Error;

/**
 * Wrapper class to parse responses.
 *
 * @since 1.0.0
 */
class Response {

	/**
	 * Input data.
	 *
	 * @since 1.0.0
	 *
	 * @var array|WP_Error
	 */
	private $input;

	/**
	 * Response constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array|WP_Error $input The response data.
	 */
	public function __construct( $input ) {

		$this->input = $input;
	}

	/**
	 * Retrieve only the response code from the raw response.
	 *
	 * @since 1.0.0
	 *
	 * @return int The response code as an integer.
	 */
	private function get_response_code(): int {

		return absint( wp_remote_retrieve_response_code( $this->input ) );
	}

	/**
	 * Retrieve only the body from the raw response.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_body(): array {

		if ( $this->has_errors() ) {
			return [];
		}

		return (array) json_decode( wp_remote_retrieve_body( $this->input ), true );
	}

	/**
	 * Retrieves the raw body of the response.
	 *
	 * @since 1.0.0
	 *
	 * @return string The raw response body, or an empty string if there are errors.
	 */
	public function get_file(): string {

		if ( $this->has_errors() ) {
			return '';
		}

		return wp_remote_retrieve_body( $this->input );
	}

	/**
	 * Retrieve only the location from the raw response.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_location(): string {

		if ( $this->has_errors() ) {
			return '';
		}

		return (string) wp_remote_retrieve_header( $this->input, 'Location' );
	}

	/**
	 * Whether we received errors in the response.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if response has errors.
	 */
	public function has_errors(): bool {

		$code = $this->get_response_code();

		// Resume Incomplete response indicates that you must continue to upload the file.
		if ( $code === 308 ) {
			return false;
		}

		return $code < 200 || $code > 299;
	}
}
