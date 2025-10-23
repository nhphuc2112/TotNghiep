<?php

namespace WPFormsCalculations\Process;

use WPFormsCalculations\Helpers;
use WPFormsCalculations\Transpiler\InnerFunctions;

/**
 * Process fields class.
 *
 * Different field types has their own specific needs for calculations.
 *
 * @since 1.0.0
 */
class Fields {

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * InnerFunctions class instance.
	 *
	 * @since 1.1.0
	 *
	 * @var InnerFunctions
	 */
	private $inner_functions;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->helpers         = wpforms_calculations()->helpers;
		$this->inner_functions = wpforms_calculations()->inner_functions;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'wpforms_forms_fields_payment_single_field_validate_amount', [ $this, 'payment_single_field_disable_validate_amount' ], 10, 4 );
	}

	/**
	 * Payment Single item field have its amount validation. We must disable it for fields with an enabled calculation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $validate     Whether to validate amount or not. Default true.
	 * @param int    $field_id     Field ID.
	 * @param string $field_submit Field data submitted by a user.
	 * @param array  $form_data    Form data and settings.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function payment_single_field_disable_validate_amount( $validate, $field_id, $field_submit, $form_data ) {

		if (
			empty( $form_data['fields'][ $field_id ]['calculation_is_enabled'] ) ||
			empty( $form_data['fields'][ $field_id ]['calculation_code_php'] )
		) {
			return $validate;
		}

		return false;
	}

	/**
	 * Validate calculation result.
	 *
	 * @since 1.0.0
	 *
	 * @param array $result         Calculation result.
	 * @param array $field          Field entry data.
	 * @param array $field_settings Field settings.
	 * @param array $fields_entry   Fields entry data before calculation.
	 * @param array $form_data      Form data and settings.
	 *
	 * @return bool
	 */
	public function validate_calculation_result( $result, $field, $field_settings, $fields_entry, $form_data ): bool {
		/**
		 * Whether the calculation result is valid or not.
		 *
		 * @since 1.0.0
		 *
		 * @param bool  $is_valid       Whether Calculation result is valid.
		 * @param mixed $result         Calculation result.
		 * @param array $field          Field entry data before calculation.
		 * @param array $field_settings Field settings.
		 * @param array $fields_entry   Fields entry data before calculation.
		 * @param array $form_data      Form data and settings.
		 */
		return (bool) apply_filters( 'wpforms_calculations_process_fields_validate_calculation_result', true, $result, $field, $field_settings, $fields_entry, $form_data );
	}

	/**
	 * Field-specific calculation result normalization.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $result         Calculation result.
	 * @param array $field_settings Field settings.
	 *
	 * @return mixed
	 */
	public function normalize_calculation_result( $result, $field_settings ) {

		if ( ! isset( $field_settings['type'] ) ) {
			return $result;
		}

		// For the Payment Single Item field.
		if ( $field_settings['type'] === 'payment-single' ) {
			$decimals       = $this->helpers->get_currency_decimals();
			$numeric_result = $this->inner_functions->parse_float( $result );
			$numeric_result = $this->inner_functions->round( $numeric_result, $decimals );

			// Do not allow negative values for Payment Single Item field.
			return $numeric_result < 0 ? 0 : html_entity_decode( wpforms_format_amount( $numeric_result, true ) );
		}

		// For the Number field.
		if ( $field_settings['type'] === 'number' ) {
			return $this->inner_functions->parse_float( $result );
		}

		return $result;
	}

	/**
	 * Update field entry data according to the new calculation result.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $result         Calculation result.
	 * @param array $field          Field entry data.
	 * @param array $field_settings Field settings.
	 * @param array $fields_entry   Fields entry data before calculation.
	 * @param array $form_data      Form data and settings.
	 *
	 * @return array Updated field data.
	 */
	public function update_field_entry_data( $result, $field, $field_settings, $fields_entry, $form_data ): array {

		// We should store only numeric or string result.
		if ( ! ( is_numeric( $result ) || is_string( $result ) ) ) {
			$result = '';
		}

		// Update field entry data.
		$field['submit_value'] = $field['value'];
		$field['value']        = $result;

		// Update the Numbers field entry data.
		if ( $field['type'] === 'number' ) {
			$field['value'] = $this->inner_functions->parse_float( $result );
		}

		// Update the Payment Single Item field entry data.
		if ( $field['type'] === 'payment-single' ) {
			$field['amount_raw'] = wpforms_sanitize_amount( $result );
			$field['value']      = wpforms_format_amount( $field['amount_raw'], true );
			$field['amount']     = wpforms_format_amount( $field['amount_raw'] );
		}

		// Log calculation result.
		$this->helpers->log(
			sprintf(
				'Field #%1$s updated data: %2$s',
				$field_settings['id'],
				print_r( $field, true ) // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			),
			'debug'
		);

		/**
		 * Filter field entry data after calculation.
		 *
		 * By default, the only field's `value` is updated with the calculation result.
		 * Developers may need to update other keys.
		 *
		 * @since 1.0.0
		 *
		 * @param array $field          Field entry data.
		 * @param array $field_settings Field settings.
		 * @param array $fields_entry   Fields entry data before calculation.
		 * @param array $form_data      Form data and settings.
		 */
		return (array) apply_filters( 'wpforms_calculations_process_fields_update_field_entry_data', $field, $field_settings, $fields_entry, $form_data );
	}
}
