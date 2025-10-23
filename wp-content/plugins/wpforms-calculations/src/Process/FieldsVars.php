<?php

namespace WPFormsCalculations\Process;

use WPFormsCalculations\Helpers;
use WPFormsCalculations\Transpiler\DateTime;

/**
 * Process fields variables class.
 *
 * @since 1.0.0
 */
class FieldsVars {

	/**
	 * Fields allowed in calculations (and their structure).
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	const ALLOWED_FIELDS_STRUCTURE = [
		'text'             => [],
		'textarea'         => [],
		'select'           => [],
		'radio'            => [],
		'checkbox'         => [],
		'number'           => [],
		'name'             => [ 'first', 'middle', 'last' ],
		'email'            => [ 'primary', 'secondary' ],
		'number-slider'    => [],
		'phone'            => [],
		'address'          => [ 'address1', 'address2', 'city', 'state', 'postal', 'country' ],
		'date-time'        => [ 'date', 'time' ],
		'url'              => [],
		'rating'           => [],
		'hidden'           => [],
		'payment-checkbox' => [ 'amount' ],
		'payment-multiple' => [ 'amount' ],
		'payment-select'   => [ 'amount' ],
		'payment-single'   => [ 'amount' ],
		'payment-total'    => [ 'amount' ],
	];

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Date and time helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var DateTime
	 */
	private $datetime;

	/**
	 * Form data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $form_data;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 */
	public function __construct( $form_data ) {

		$this->helpers   = wpforms_calculations()->helpers;
		$this->datetime  = wpforms_calculations()->datetime;
		$this->form_data = $form_data;
	}

	/**
	 * Get variables of the fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields_entry Fields entry data.
	 *
	 * @return string
	 */
	public function get_all_fields_variables( $fields_entry ) {

		$fields_vars      = [];
		$fields_structure = $this->helpers->get_fields_allowed_in_calculation();

		foreach ( $fields_entry as $field ) {

			if ( ! isset( $field['id'], $field['type'], $fields_structure[ $field['type'] ] ) ) {
				continue;
			}

			$fields_vars[] = $this->get_field_variables( $field );
		}

		return implode( "\n", array_merge( ...$fields_vars ) );
	}

	/**
	 * Get field's primary and subfields variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function get_field_variables( $field ) {

		$fields_structure = $this->helpers->get_fields_allowed_in_calculation();
		$field_vars       = [];
		$value            = $this->get_field_variable_value( $field );

		// Primary field variable.
		$field_vars[] = $this->get_field_variable_line( $field['id'], '', $value );

		// Subfield variables.
		foreach ( $fields_structure[ $field['type'] ] as $sub_field ) {

			if ( ! isset( $field[ $sub_field ] ) ) {
				continue;
			}

			$field_vars[] = $this->get_field_variable_line(
				$field['id'],
				$sub_field,
				$this->get_sub_field_variable_value( $field, $sub_field )
			);
		}

		$extra_vars = [];

		// Specific fields variables.
		switch ( $field['type'] ) {
			case 'email':
				$extra_vars = $this->get_field_email_variables( $field );
				break;

			case 'checkbox':
				$extra_vars = $this->get_field_checkbox_variables( $field );
				break;

			case 'payment-checkbox':
				$extra_vars = $this->get_field_payment_checkbox_variables( $field );
		}

		// Field specific variables - Payment Checkbox Items.
		return array_merge( $field_vars, $extra_vars );
	}

	/**
	 * Get field main variable value.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return mixed
	 */
	private function get_field_variable_value( $field ) {

		if ( ! isset( $field['type'], $field['value'] ) ) {
			return '';
		}

		// Fields hidden by CL should have an empty value.
		if ( isset( $field['visible'] ) && ! $field['visible'] ) {
			return '';
		}

		// Selectable (choices based) fields.
		if ( in_array( $field['type'], [ 'select', 'radio', 'checkbox' ], true ) ) {
			return $this->get_selectable_field_variable_value( $field );
		}

		// Payment fields.
		if ( strpos( $field['type'], 'payment' ) === 0 ) {
			return $this->get_payment_field_variable_value( $field );
		}

		return $field['value'];
	}

	/**
	 * Get selectable field main variable value.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field Field data.
	 *
	 * @return string
	 */
	private function get_selectable_field_variable_value( $field ): string {

		if ( ! isset( $field['value'] ) ) {
			return '';
		}

		if (
			! empty( $this->form_data['fields'][ $field['id'] ]['show_values'] ) &&
			wpforms_show_fields_options_setting()
		) {
			return $field['value_raw'] ?? $field['value'];
		}

		return $field['value'];
	}

	/**
	 * Get payment field main variable value.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field Field data.
	 *
	 * @return string
	 */
	private function get_payment_field_variable_value( $field ): string {

		if ( ! isset( $field['value'] ) ) {
			return '';
		}

		// We should use `value_choice` instead of `value` where it is possible.
		$value_decoded = $this->helpers->decode_currency_symbols( $field['value'] );
		$value_choice  = $field['value_choice'] ?? $value_decoded;

		return empty( $this->form_data['fields'][ $field['id'] ]['show_price_after_labels'] ) ? $value_choice : $value_decoded;
	}

	/**
	 * Get sub field main variable value.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field     Field data.
	 * @param string $sub_field Sub field. Defaults to 'value'.
	 *
	 * @return string
	 */
	private function get_sub_field_variable_value( $field, $sub_field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! isset( $field['type'], $field[ $sub_field ] ) ) {
			return '';
		}

		// Fields hidden by CL should have an empty subfield value.
		if ( isset( $field['visible'] ) && ! $field['visible'] ) {
			return '';
		}

		if ( $field['type'] !== 'date-time' ) {
			return $field[ $sub_field ];
		}

		// DateTime field specific logic.
		$field_data  = $this->form_data['fields'][ $field['id'] ];
		$format_key  = $sub_field === 'date' ? 'date_format' : 'time_format';
		$from_format = 'g:i A';
		$to_format   = isset( $field_data[ $format_key ] ) ? $field_data[ $format_key ] : '';

		if ( $sub_field === 'date' ) {
			$from_format = $to_format === 'd/m/Y' ? 'm/d/Y' : $to_format;
		}

		return $this->datetime->convert_format( $field[ $sub_field ], $from_format, $to_format );
	}

	/**
	 * Get email field variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function get_field_email_variables( $field ) {

		$fields_structure = $this->helpers->get_fields_allowed_in_calculation();

		if ( empty( $this->form_data['fields'][ $field['id'] ]['confirmation'] ) ) {
			return [];
		}

		$field_vars = [];

		foreach ( $fields_structure['email'] as $subfield ) {
			$field_vars[] = $this->get_field_variable_line( $field['id'], $subfield, $field['value'] );
		}

		return $field_vars;
	}

	/**
	 * Get checkbox field choices variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function get_field_checkbox_variables( $field ) {

		if ( empty( $this->form_data['fields'][ $field['id'] ]['choices'] ) ) {
			return [];
		}

		$field_vars = [];
		$checked    = array_map( 'trim', explode( "\n", $field['value'] ) );

		foreach ( $this->form_data['fields'][ $field['id'] ]['choices'] as $id => $choice ) {

			if ( ! isset( $choice['label'] ) ) {
				continue;
			}

			$choice_value = ! empty( $this->form_data['fields'][ $field['id'] ]['show_values'] ) && wpforms_show_fields_options_setting() ? $choice['value'] : $choice['label'];
			$var_value    = in_array( $choice['label'], $checked, true ) ? $choice_value : false;
			$field_vars[] = $this->get_field_variable_line( $field['id'], $id, $var_value );
		}

		return $field_vars;
	}

	/**
	 * Get checkbox field choices variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function get_field_payment_checkbox_variables( $field ) {

		if ( empty( $this->form_data['fields'][ $field['id'] ]['choices'] ) ) {
			return [];
		}

		$field_vars = [];
		$checked    = array_map( 'trim', explode( "\n", $field['value_choice'] ) );

		// Add choices variables.
		foreach ( $this->form_data['fields'][ $field['id'] ]['choices'] as $id => $choice ) {

			if ( ! isset( $choice['label'] ) ) {
				continue;
			}

			$label        = $choice['label'];
			$choice_value = $this->helpers->decode_currency_symbols( wpforms_format_amount( $choice['value'], true ) );
			$label       .= empty( $this->form_data['fields'][ $field['id'] ]['show_price_after_labels'] ) ? '' : ' - ' . $choice_value;
			$var_value    = in_array( $choice['label'], $checked, true ) ? $label : false;
			$field_vars[] = $this->get_field_variable_line( $field['id'], $id, $var_value );
		}

		return $field_vars;
	}

	/**
	 * Get field variable line.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $field_id  Field Id.
	 * @param string $sub_field Subfield slug.
	 * @param mixed  $value     Value.
	 *
	 * @return string
	 */
	private function get_field_variable_line( $field_id, $sub_field, $value ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $this->form_data['fields'][ $field_id ] ) ) {
			return '';
		}

		$slug    = $field_id;
		$field   = $this->form_data['fields'][ $field_id ];
		$comment = $field['label'];

		if ( ! wpforms_is_empty_string( $sub_field ) ) {
			$slug     = $field_id . '_' . $sub_field;
			$comment .= ' - ' . $sub_field;
		}

		// Amount should be float number.
		if ( $sub_field === 'amount' ) {
			$value = (float) wpforms_sanitize_amount( $value );

			// phpcs:disable WordPress.Security.NonceVerification.Missing
			// Adjust field amount based on submitted quantity.
			if ( ! empty( $field['enable_quantity'] ) && isset( $_POST['wpforms']['quantities'][ $field_id ] ) ) {
				$value = $value * (int) $_POST['wpforms']['quantities'][ $field_id ];
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		// Prepare value.
		$value = is_null( $value ) ? '' : $value;
		$value = is_string( $value ) && ! is_numeric( $value )
			? addslashes( sanitize_text_field( $value ) )
			: $value;

		// Number field variables should have numeric value.
		if ( in_array( $field['type'], [ 'number', 'number-slider' ], true ) ) {
			$value = (float) $value;
		}

		if ( is_bool( $value ) ) {
			// Boolean values should be printed without quotes.
			$value = $value ? 'true' : 'false';
		} else {
			$value = '"' . $value . '"';
		}

		// Compile line.
		return sprintf(
			'$F%1$s = %2$s; // %3$s',
			$slug,
			$value,
			$comment
		);
	}
}
