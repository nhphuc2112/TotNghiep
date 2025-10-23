<?php

namespace WPFormsCalculations\Admin;

use WPFormsCalculations\Transpiler\Transpiler;
use WPFormsCalculations\Helpers;

/**
 * Calculation AJAX in Form Builder.
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * Helpers class instance.
	 *
	 * @since 1.4.1
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->helpers = new Helpers();

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'wpforms_save_form_args', [ $this, 'save_form_args' ], 10, 3 );
		add_filter( 'wpforms_raw_options', [ $this, 'sanitize_field_raw_options' ], 10, 2 );
		add_action( 'wp_ajax_wpforms_calculations_validate_formula',  [ $this, 'validate_formula' ] );
	}

	/**
	 * Add `calculation_code` to the raw options array.
	 * This is necessary to skip the formula sanitization performed in `wpforms_sanitize_field()`.
	 *
	 * @since 1.4.1
	 *
	 * @param array  $raw_options Raw options.
	 * @param string $field_type  Field type.
	 *
	 * @return array
	 */
	public function sanitize_field_raw_options( $raw_options, $field_type ): array {

		$calc_fields = $this->helpers->get_fields_calculation_is_possible();

		if ( ! in_array( $field_type, $calc_fields, true ) ) {
			return $raw_options;
		}

		$raw_options   = is_array( $raw_options ) ? $raw_options : [];
		$raw_options[] = 'calculation_code';

		return $raw_options;
	}

	/**
	 * Save form args action callback.
	 * Process all the fields with enabled calculations.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form Form update post arguments.
	 * @param array $data Initial form data.
	 * @param array $args Update form args.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_form_args( $form, $data, $args ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		if ( ! wpforms_calculations()->helpers->has_calculation_enabled_field( $form_data ) ) {
			return $form;
		}

		$transpiler = new Transpiler();

		foreach ( $form_data['fields'] as $field_id => $field ) {

			if ( empty( $field['calculation_is_enabled'] ) ) {
				continue;
			}

			$form_data['fields'][ $field_id ] = $transpiler->process_field( $field, $form_data );
		}

		$form['post_content'] = wpforms_encode( $form_data );

		return $form;
	}

	/**
	 * Validate formula AJAX callback.
	 *
	 * @since 1.0.0
	 */
	public function validate_formula() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Run a security check.
		if ( ! check_ajax_referer( 'wpforms-builder', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-calculations' ) );
		}

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to perform this action.', 'wpforms-calculations' ) );
		}

		$form_obj = wpforms()->obj( 'form' );

		// Check for form ID and field ID.
		if ( empty( $_POST['form_id'] ) || ! isset( $_POST['field_id'] ) || ! $form_obj ) {
			wp_send_json_error( esc_html__( 'Something went wrong while performing this action.', 'wpforms-calculations' ) );
		}

		$form_id   = (int) $_POST['form_id'];
		$field_id  = (int) $_POST['field_id'];
		$form_data = $form_obj->get( $form_id, [ 'content_only' => true ] );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$code       = isset( $_POST['code'] ) ? trim( wp_unslash( $_POST['code'] ) ) : '';
		$transpiler = new Transpiler();

		// If the form is still not saved, we have to mimic the field in the $form_data array.
		// It is needed just to be able to run the code validation.
		if ( ! isset( $form_data['fields'][ $field_id ] ) ) {
			$form_data['fields'][ $field_id ] = [
				'id' => $field_id,
			];
		}

		$errors = $transpiler->parse_and_validate_formula_code( $code, $field_id, $form_data, false );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
			die();
		}

		wp_send_json_success();
	}
}
