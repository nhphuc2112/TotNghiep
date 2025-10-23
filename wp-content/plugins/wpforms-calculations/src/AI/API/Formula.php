<?php

namespace WPFormsCalculations\AI\API;

use WPForms\Integrations\AI\API\API;
use WPForms\Integrations\AI\Helpers;

/**
 * API class.
 *
 * Communication with middleware to get calculation formula.
 *
 * @since 1.6.0
 */
class Formula extends API {

	/**
	 * The API endpoint.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	private const ENDPOINT = '/ai-calculations';

	/**
	 * Get calculations from the API.
	 *
	 * @since 1.6.0
	 *
	 * @param array  $prompt     Prompt to get calculations for.
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function get_calculations( $prompt, $session_id ): array {

		foreach ( $prompt as $key => $value ) {
			$prompt[ $key ] = $this->prepare_prompt( $value );
		}

		$args = [
			'userPrompt' => $prompt,
			'limit'      => $this->get_limit(),
		];

		if ( ! empty( $session_id ) ) {
			$args['sessionId'] = $session_id;
		}

		$response = $this->request->post( self::ENDPOINT, $args );

		if ( $response->has_errors() ) {
			$error_data = $response->get_error_data();

			Helpers::log_error( $response->get_log_message( $error_data ), self::ENDPOINT, $args );

			return $error_data;
		}

		return $response->get_body();
	}
}
