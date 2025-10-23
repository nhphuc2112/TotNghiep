<?php

namespace WPFormsCalculations;

use PhpParser\Node;
use PhpParser\PrettyPrinter;
use WPForms\Helpers\Transient;
use WPFormsCalculations\Admin\Builder;
use WPFormsCalculations\Process\FieldsVars;

/**
 * Calculations helpers.
 *
 * @since 1.0.0
 */
class Helpers {

	/**
	 * Fields in which calculation is possible.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $fields_calculation_is_possible;

	/**
	 * Fields and their structure allowed in calculations.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $allowed_fields;

	/**
	 * Pretty printer instance.
	 *
	 * @since 1.0.0
	 *
	 * @var PrettyPrinter\Standard
	 */
	private $pretty_printer;

	/**
	 * Determine whether the Forms have a field with enabled calculations.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms or a single form data.
	 *
	 * @return bool
	 */
	public function has_calculation_enabled_field( $forms ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! is_array( $forms ) ) {
			return false;
		}

		// Convert single form data to multiple forms array.
		$forms = isset( $forms['fields'] ) ? [ $forms ] : $forms;

		foreach ( $forms as $form ) {
			if ( empty( $form['fields'] ) ) {
				continue;
			}

			foreach ( $form['fields'] as $field ) {
				if ( empty( $field['calculation_is_enabled'] ) ) {
					continue;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Detect calculations debug mode.
	 *
	 * @since 1.0.0
	 */
	public function is_calc_debug() {

		$is_calc_debug = defined( 'WPFORMS_CALCULATIONS_DEBUG' ) && WPFORMS_CALCULATIONS_DEBUG;

		return $is_calc_debug || (
			wpforms_current_user_can() && Transient::get( Admin\Admin::DEBUG_MODE_TRANSIENT )
		);
	}

	/**
	 * Log message.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $message Log message or any data.
	 * @param string $type    Message type.
	 */
	public function log( $message, $type = 'log' ) {

		if (
			! (
				( $this->is_calc_debug() && $type === 'debug' ) || // The `debug` messages are allowed only in WPFORMS_CALCULATIONS_DEBUG mode.
				( in_array( $type, [ 'log', 'error' ], true ) )
			)
		) {
			return;
		}

		$message_str = $message;

		if ( is_array( $message ) || is_object( $message ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$message_str = print_r( $message, true );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'WPForms Calculations: ' . $message_str );
		wpforms_log( 'Calculations', $message_str, [ 'type' => $type === 'debug' ? 'log' : $type ] );
	}

	/**
	 * Get fields in which calculation is possible.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_fields_calculation_is_possible() {

		if ( empty( $this->fields_calculation_is_possible ) ) {

			/**
			 * Filter fields in which calculation is possible.
			 *
			 * @since 1.0.0
			 *
			 * @param array $fields_calculation_is_possible Array of field types in which calculation is possible.
			 */
			$this->fields_calculation_is_possible = (array) apply_filters( 'wpforms_calculations_helpers_get_fields_calculation_is_possible', Builder::ALLOWED_FIELD_TYPES );
		}

		return $this->fields_calculation_is_possible;
	}

	/**
	 * Get fields and their structure allowed in calculations.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_fields_allowed_in_calculation() {

		if ( empty( $this->allowed_fields ) ) {

			/**
			 * Filter fields and their structure allowed in calculations.
			 *
			 * @since 1.0.0
			 *
			 * @param array $fields_structure Field types and subfields that are supported in calculations.
			 */
			$this->allowed_fields = (array) apply_filters( 'wpforms_calculations_helpers_get_fields_allowed_in_calculation', FieldsVars::ALLOWED_FIELDS_STRUCTURE );
		}

		return $this->allowed_fields;
	}

	/**
	 * Get fields used in field calculation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return array Array of field IDs used in the calculation.
	 */
	public function get_fields_used_in_calculation( $field ) {

		$used_fields = [];

		if ( empty( $field['calculation_code_php'] ) || empty( $field['calculation_is_enabled'] ) ) {
			return [];
		}

		// Match field variables `$Fn` in the formula.
		preg_match_all( '/\$F\d*/', $field['calculation_code'], $matches );

		if ( empty( $matches[0] ) ) {
			return [];
		}

		foreach ( $matches[0] as $match ) {
			$used_fields[] = str_replace( '$F', '', $match );
		}

		return array_unique( $used_fields );
	}

	/**
	 * Get fields used in form calculations.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Field data.
	 *
	 * @return array Each key is a field ID, and the value is an array of field IDs used in this field calculation.
	 */
	public function get_fields_used_in_form_calculations( $form_data ) {

		$calc_fields = [];

		if ( empty( $form_data['fields'] ) ) {
			return $calc_fields;
		}

		foreach ( $form_data['fields'] as $field ) {
			$used_fields = $this->get_fields_used_in_calculation( $field );

			if ( ! empty( $used_fields ) ) {
				$calc_fields[ $field['id'] ] = $used_fields;
			}
		}

		return $calc_fields;
	}

	/**
	 * Whether the form has the Payment Single Item field with enabled calculation AND
	 * other fields are used in the formula.
	 *
	 * @since 1.0.0
	 * @deprecated 1.2.0
	 *
	 * @param array $form_data   Field data.
	 * @param array $used_fields Fields used in calculations in the form.
	 *
	 * @return bool
	 */
	public function is_form_has_payment_field_calculation( $form_data, $used_fields ) {

		_deprecated_function( __METHOD__, '1.2.0 of the WPForms Calculations addon.' );

		// Detect if form has a payment field with calculation.
		foreach ( $form_data['fields'] as $form_field ) {
			if (
				$form_field['type'] === 'payment-single' &&
				! empty( $form_field['calculation_is_enabled'] ) &&
				! empty( $form_field['calculation_code_php'] ) &&
				! empty( $used_fields[ $form_field['id'] ] )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replace by given search regexp pattern in the text avoiding replacements inside the quotes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pattern Search pattern.
	 * @param string $replace Replacement.
	 * @param string $text    Text.
	 *
	 * @return string
	 */
	public function preg_replace_not_in_quotes( $pattern, $replace, $text ) {
		/*
		 * Lookahead pattern to find the needle string avoiding quoted strings.
		 *
		 * This is how it is constructed from the ground up:
		 * (?=[^"]*") - sees any amount of non-quote characters followed by a quote
		 * (?=([^"]*"){2}) - twice
		 * (?=(([^"]*"){2})*) - as many times as possible
		 * (?=(([^"]*"){2})*[^"]*) - then optional non-quote characters
		 * (?=(([^"]*"){2})*[^"]*$) - to the end
		 * (?=(?:(?:[^"]*"){2})*[^"]*$) - add non-capturing groups.
		 *
		 * Source: https://stackoverflow.com/questions/20767089/preg-replace-when-not-inside-double-quotes
		 */
		$lookahead       = '(?=(?:(?:[^"]*"){2})*[^"]*$)';
		$replace_pattern = '/' . $pattern . $lookahead . '/i';

		return preg_replace( $replace_pattern, $replace, $text );
	}

	/**
	 * Decode currencies symbols in string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $str String with encoded currency symbols.
	 *
	 * @return string String with decoded currency symbols.
	 */
	public function decode_currency_symbols( $str ) {

		foreach ( wpforms_get_currencies() as $currency ) {
			$str = str_replace( $currency['symbol'], html_entity_decode( $currency['symbol'] ), $str );
		}

		return $str;
	}

	/**
	 * Get current currency decimals.
	 *
	 * @since 1.3.0
	 *
	 * @return int Current currency decimals.
	 */
	public function get_currency_decimals(): int {

		static $decimals = null;

		if ( $decimals !== null ) {
			return $decimals;
		}

		$decimals = wpforms_get_currency_decimals( wpforms_get_currency() );

		return $decimals;
	}

	/**
	 * Print AST node to a string.
	 *
	 * @since 1.0.0
	 *
	 * @param Node[] $nodes AST nodes object.
	 *
	 * @return string
	 */
	public function print_node( $nodes ) {

		if ( empty( $this->pretty_printer ) ) {
			$this->pretty_printer = new PrettyPrinter\Standard();
		}

		$nodes = is_array( $nodes ) ? $nodes : [ $nodes ];
		$code  = $this->pretty_printer->prettyPrint( $nodes );

		return trim( str_replace( '<php', '', $code ) );
	}
}
