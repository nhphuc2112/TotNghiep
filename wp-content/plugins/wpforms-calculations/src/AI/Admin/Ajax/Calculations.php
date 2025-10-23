<?php

namespace WPFormsCalculations\AI\Admin\Ajax;

use Exception;
use WPFormsCalculations\AI\API\Formula;
use WPForms\Integrations\AI\Admin\Ajax\Base;

/**
 * AI Admin Ajax Calculations class.
 *
 * @since 1.6.0
 */
class Calculations extends Base {

	/**
	 * API instance.
	 *
	 * @since 1.6.0
	 *
	 * @var Formula
	 */
	protected $api;

	/**
	 * Initialize class.
	 *
	 * @since 1.6.0
	 */
	public function init() {

		$this->api = new Formula();

		$this->api->init();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.6.0
	 */
	public function hooks() {

		add_action( 'wp_ajax_wpforms_get_ai_calculations', [ $this, 'get_ai_calculations' ] );
		add_filter(
			'wpforms_integrations_ai_admin_ajax_forms_get_form_before_send',
			[ $this, 'get_calculations_for_ai_forms' ],
			10,
			2
		);
	}

	/**
	 * Get AI calculations.
	 *
	 * @since 1.6.0
	 */
	public function get_ai_calculations(): void {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-calculations' ) ]
			);
		}

		$prompt = $this->get_post_data( 'prompt', 'json' );

		if ( ! isset( $prompt['promptText'] ) || $this->is_empty_prompt( $prompt['promptText'] ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Please provide a prompt.', 'wpforms-calculations' ) ]
			);
		}

		$prompt['promptText'] = wp_strip_all_tags( $prompt['promptText'] );

		$session_id = $this->get_post_data( 'session_id' );

		$response = $this->api->get_calculations( $prompt, $session_id );

		wp_send_json_success( $response );
	}

	/**
	 * Get calculations for AI forms.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $form       Form data.
	 * @param string $session_id Session ID.
	 *
	 * @return array
	 */
	public function get_calculations_for_ai_forms( array $form, string $session_id ): array {

		if ( ! isset( $form['fields'] ) ) {
			return $form;
		}

		$calculation_fields = [];

		foreach ( $form['fields'] as $field_id => $field ) {
			$form['fields'][ $field_id ] = $this->process_field_calculation( $field_id, $field, $form, $session_id );

			if ( (int) ( $form['fields'][ $field_id ]['calculation_is_enabled'] ?? null ) === 1 ) {
				$calculation_fields[] = $form['fields'][ $field_id ];
			}
		}

		return $this->add_information_field( $form, $calculation_fields );
	}

	/**
	 * Process calculation for a single field.
	 *
	 * @since 1.7.0
	 *
	 * @param int|string $field_id   Field ID.
	 * @param array      $field      Field data.
	 * @param array      $form       Form data.
	 * @param string     $session_id Session ID.
	 */
	private function process_field_calculation( $field_id, array $field, array $form, string $session_id ): array {

		if ( ! isset( $field['calculation_is_enabled'] ) || (int) $field['calculation_is_enabled'] !== 1 ) {
			return $field;
		}

		$prompt_arr = $this->prepare_prompt( $field_id, $field, $form );

		try {
			$calc_response = $this->api->get_calculations( $prompt_arr, $session_id );

			if ( ! empty( $calc_response['newCodeEditorContent'] ) ) {
				$field['calculation_code'] = $calc_response['newCodeEditorContent'];
			} else {
				unset( $field['calculation_is_enabled'] );
			}

			if ( ! empty( $field['calculation_formula_description'] ) ) {
				unset( $field['calculation_formula_description'] );
			}
		} catch ( Exception $e ) {
			wpforms_log( '[WPForms AI] Error fetching calculation_code', $e->getMessage(), [ 'type' => 'error' ] );
		}

		return $field;
	}

	/**
	 * Add an information field to the form.
	 *
	 * @since 1.7.0
	 *
	 * @param array $form               Form data.
	 * @param array $calculation_fields Calculation fields.
	 *
	 * @return array
	 */
	private function add_information_field( array $form, array $calculation_fields ): array {

		if ( empty( $calculation_fields ) ) {
			return $form;
		}

		// Build the bulleted list of field labels.
		$field_list = '';

		foreach ( $calculation_fields as $field ) {
			$label = $field['label'] ?? '';

			$field_list .= '<li>' . esc_html( $label ) . '</li>';
		}

		$description = sprintf(
			'%1$s<ul>%2$s</ul>',
			esc_html__( 'Make sure you validate the formula in the following fields before publishing your form.', 'wpforms-calculations' ),
			$field_list
		);

		$current_keys = array_keys( $form['fields'] );
		$highest_key  = ! empty( $current_keys ) ? max( $current_keys ) : 0;
		$new_key      = $highest_key + 1;

		$info_field = [
			'id'          => $new_key,
			'type'        => 'internal-information',
			'label'       => esc_html__( 'Your Form Contains Calculations', 'wpforms-calculations' ),
			'description' => $description,
			'class'       => 'wpforms-ai-form-generator-hidden',
		];

		// Place the $ info_field at the beginning of the form with key $new_key.
		$form['fields'] = [ $new_key => $info_field ] + $form['fields'];

		$form['fieldsOrder'] = array_keys( $form['fields'] );
		$form['preMessage']  = [
			'title' => esc_html__( 'Your form contains calculations.', 'wpforms-calculations' ),
			'text'  => esc_html__( 'Make sure you validate the formulas before publishing your form.', 'wpforms-calculations' ),
		];

		return $form;
	}

	/**
	 * Prepare prompt for AI Calculations.
	 *
	 * @since 1.7.0
	 *
	 * @param int|string $field_id Field ID.
	 * @param array      $field    Field data.
	 * @param array      $form     Form data.
	 *
	 * @return array
	 */
	private function prepare_prompt( $field_id, array $field, array $form ): array {

		$label = $field['label'] ?? '';

		// Do not make the prompt message translatable as it's not a user-facing string and AI uses fewer tokens.
		$prompt_text = 'Generate a calculation formula for: ' . ( ! empty( $label ) ? $label : 'Field ' . $field_id );

		return [
			'fieldId'           => (string) $field_id,
			'promptText'        => $prompt_text,
			'currentCodeEditor' => $field['calculation_code'] ?? '',
			'formJson'          => wp_json_encode( $form ),
		];
	}
}
