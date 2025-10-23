<?php

namespace WPFormsCalculations\Process;

use Error;
use ParseError;
use DivisionByZeroError;
use WPForms\Forms\Fields\PaymentTotal;
use WPFormsCalculations\Helpers;
use WPFormsCalculations\Transpiler\Transpiler;

/**
 * Process calculations class.
 *
 * @since 1.0.0
 */
class Process {

	/**
	 * Fields entry data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $fields_entry;

	/**
	 * Form settings data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $form_data;

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Process Fields class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Fields
	 */
	private $fields;

	/**
	 * Process Fields variables class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var FieldsVars
	 */
	private $fields_vars;

	/**
	 * Calculation results storage.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $fields_results;

	/**
	 * Recursive calculations stack.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $fields_calc_stack;

	/**
	 * CFL Functions array.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $functions;

	/**
	 * Inner functions array.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $inner_functions;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->helpers         = wpforms_calculations()->helpers;
		$this->fields          = new Fields();
		$this->functions       = wpforms_calculations()->functions->get();
		$this->inner_functions = wpforms_calculations()->inner_functions->get();

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'wpforms_process_filter', [ $this, 'process_fields' ], 20, 3 );
	}

	/**
	 * Process fields entry.
	 *
	 * Do not trust the posted calculated fields since that relies on javascript.
	 * Instead, we re-calculate them server side.
	 *
	 * @since 1.0.0
	 *
	 * @param array|mixed $fields_entry Array of fields entry data.
	 * @param array       $submit_entry Submitted form data.
	 * @param array       $form_data    Form settings.
	 * @param bool        $apply_cl     Whether the Conditional Logic must be applied after calculation.
	 *                                  Defaults to true.
	 *
	 * @return array
	 */
	public function process_fields( $fields_entry, $submit_entry, $form_data, $apply_cl = true ): array {

		$fields_entry = (array) $fields_entry;

		if ( $this->should_skip_processing( $form_data ) ) {
			return $fields_entry;
		}

		$this->fields_entry = $fields_entry;
		$this->form_data    = $form_data;
		$this->fields_vars  = new FieldsVars( $form_data );

		// Calculate all the fields.
		$this->calculate_all_fields( $form_data );

		// Apply Conditional Logic to entry data after all calculations.
		if ( $apply_cl ) {
			$this->fields_entry = $this->apply_conditional_logic( $this->fields_entry, $this->form_data );
		}

		// Re-calculate the Total field after applying Conditional Logic.
		$this->fields_entry = PaymentTotal\Field::calculate_total_static( $this->fields_entry, [], $this->form_data );

		// Re-calculate fields after updating the Total field.
		$this->calculate_all_fields( $form_data );

		// Reset invisible fields after re-calculation.
		// This is needed to reset the fields hidden before the Total field re-calculation.
		if ( $apply_cl ) {
			$this->fields_entry = $this->reset_invisible_fields( $this->fields_entry, $this->form_data );
		}

		/**
		 * Allow post-calculation entry processing.
		 *
		 * @since 1.2.0
		 * @since 1.4.0 Added $apply_cl parameter.
		 *
		 * @param array $fields_entry Array of fields entry data.
		 * @param array $submit_entry Submitted form data.
		 * @param array $form_data    Form settings.
		 * @param bool  $apply_cl     Whether the Conditional Logic must be applied after calculation.
		 */
		$this->fields_entry = (array) apply_filters( 'wpforms_calculations_process_filter', $this->fields_entry, $submit_entry, $form_data, $apply_cl );

		return $this->fields_entry;
	}

	/**
	 * Process fields once.
	 *
	 * This is the simplified version of the `process_fields()` method
	 * without recalculation of the total field and filtering the result.
	 * Needed for cases when coupon code is applied,
	 * and there are calculated fields dependent on the Total field.
	 *
	 * @since 1.3.0
	 * @since 1.4.0 Added $apply_cl parameter.
	 *
	 * @param array|mixed $fields_entry Array of fields entry data.
	 * @param array       $form_data    Form settings.
	 * @param bool        $apply_cl     Whether the Conditional Logic must be applied after calculation.
	 *                                  Defaults to true.
	 *
	 * @return array
	 */
	public function process_fields_once( $fields_entry, $form_data, bool $apply_cl = true ): array {

		$fields_entry = (array) $fields_entry;

		if ( $this->should_skip_processing( $form_data ) ) {
			return $fields_entry;
		}

		$this->fields_entry = $fields_entry;
		$this->form_data    = $form_data;
		$this->fields_vars  = new FieldsVars( $form_data );

		// Calculate all the fields.
		$this->calculate_all_fields( $form_data );

		// Apply Conditional Logic to entry data after calculations.
		if ( $apply_cl ) {
			$this->fields_entry = $this->apply_conditional_logic( $this->fields_entry, $this->form_data );
		}

		return $this->fields_entry;
	}

	/**
	 * Check if we should skip processing.
	 *
	 * @since 1.3.0
	 *
	 * @param array|mixed $form_data Form settings.
	 *
	 * @return bool
	 */
	private function should_skip_processing( $form_data ): bool {

		return empty( $form_data['fields'] ) ||
			! is_array( $form_data['fields'] ) ||
			// Do not calculate value if this is an in-form entry preview (after page break).
			wpforms_is_ajax( 'wpforms_get_entry_preview' );
	}

	/**
	 * Calculate all the fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form settings.
	 */
	private function calculate_all_fields( $form_data ) {

		$this->fields_results = [];

		foreach ( $form_data['fields'] as $field_id => $field_settings ) {

			// Do not re-calculate the field if it was already calculated.
			if ( isset( $this->fields_results[ $field_id ] ) ) {
				continue;
			}

			// Reset calculation stack.
			$this->fields_calc_stack = [];

			if ( ! isset( $this->fields_entry[ $field_id ] ) ) {
				continue;
			}

			$this->fields_entry[ $field_id ] = $this->calculate_field( $this->fields_entry[ $field_id ], $field_settings );
		}
	}

	/**
	 * Get current state of fields entry.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_fields_entry() {

		return $this->fields_entry;
	}

	/**
	 * Get current form settings data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_form_data() {

		return $this->form_data;
	}

	/**
	 * Calculate single field and update field entry data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field          Field entry data.
	 * @param array $field_settings Field settings.
	 *
	 * @return array
	 *
	 * @throws Error               When calculation code has errors.
	 * @throws ParseError          When calculation code has parse errors.
	 * @throws DivisionByZeroError When calculation code has division by zero.
	 */
	private function calculate_field( $field, $field_settings ) {

		if (
			empty( $field_settings['calculation_is_enabled'] ) ||
			empty( $field_settings['calculation_code_php'] )
		) {
			return $field;
		}

		// Push the field ID to the stack to prevent infinite loop in recursive calls.
		$this->fields_calc_stack[] = (int) $field_settings['id'];

		// Pre-calculate fields which is used in the formula (dependence).
		$this->pre_calculate_fields( $field, $field_settings );

		// Do not re-calculate the field if it is already calculated during pre-calculation.
		if ( isset( $this->fields_results[ $field_settings['id'] ] ) ) {
			return $field;
		}

		$result = $this->get_field_calculation_result( $field_settings );

		// Update fields calculations result storage.
		$this->fields_results[ $field_settings['id'] ] = $result;

		if ( ! $this->fields->validate_calculation_result( $result, $field, $field_settings, $this->fields_entry, $this->form_data ) ) {
			return $field;
		}

		return $this->fields->update_field_entry_data( $result, $field, $field_settings, $this->fields_entry, $this->form_data );
	}

	/**
	 * Pre-calculation of the fields which are used in the formula (dependence).
	 *
	 * @since 1.0.0
	 *
	 * @param array $field          Field entry data.
	 * @param array $field_settings Field settings.
	 */
	private function pre_calculate_fields( $field, $field_settings ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Do not re-calculate.
		if (
			empty( $field_settings['calculation_is_enabled'] ) || // Not enabled.
			empty( $field_settings['calculation_code_php'] ) || // The PHP code is empty.
			isset( $this->fields_results[ $field_settings['id'] ] ) // It was already calculated.
		) {
			return;
		}

		// Match field variables `$Fn` in the formula.
		preg_match_all( '/\$F\d*/', $field_settings['calculation_code'], $fields );

		if ( empty( $fields[0] ) ) {
			return;
		}

		foreach ( $fields[0] as $var_field_id ) {

			$var_field_id = (int) str_replace( '$F', '', $var_field_id );

			// Do not recalculate itself.
			if ( $var_field_id === (int) $field_settings['id'] ) {
				continue;
			}

			// Do not re-calculate the field if it was already calculated.
			if ( isset( $this->fields_results[ $var_field_id ] ) ) {
				continue;
			}

			// Do not re-calculate the field if the calculation is not enabled or the code is empty.
			if (
				empty( $this->form_data['fields'][ $var_field_id ]['calculation_is_enabled'] ) ||
				empty( $this->form_data['fields'][ $var_field_id ]['calculation_code_php'] )
			) {
				continue;
			}

			// Detect circular reference.
			if ( in_array( $var_field_id, $this->fields_calc_stack, true ) ) {

				// Empty result for this field.
				$this->fields_results[ $var_field_id ] = '';
				$this->fields_calc_stack[]             = $var_field_id;

				$this->helpers->log(
					sprintf(
						'Field #%1$s error: circular reference detected in field #%2$s.',
						$field['id'],
						$var_field_id
					),
					'error'
				);

				continue;
			}

			$this->fields_entry[ $var_field_id ] = $this->calculate_field( $this->fields_entry[ $var_field_id ], $this->form_data['fields'][ $var_field_id ] );
		}
	}

	/**
	 * Get field calculation result.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_settings Field settings.
	 *
	 * @return mixed
	 *
	 * @throws Error               When calculation code has errors.
	 * @throws ParseError          When calculation code has parse errors.
	 * @throws DivisionByZeroError When calculation code has division by zero.
	 */
	private function get_field_calculation_result( $field_settings ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		$prev_reporting_level = error_reporting( 0 );

		set_error_handler( [ $this, 'error_handler' ] );

		$formula_php_code = $this->get_formula_php_code( $field_settings );

		// Log formula PHP code.
		$this->helpers->log(
			sprintf(
				'Field #%1$s PHP code: %2$s',
				$field_settings['id'],
				$formula_php_code
			),
			'debug'
		);

		$result = '';

		// Define the functions array which is used in the formula.
		${Transpiler::FUNCTIONS_ARRAY_NAME}       = $this->functions;
		${Transpiler::INNER_FUNCTIONS_ARRAY_NAME} = $this->inner_functions;

		try {

			// phpcs:ignore Squiz.PHP.Eval.Discouraged, Generic.PHP.ForbiddenFunctions.Found
			$result = eval( $formula_php_code );

		// phpcs:ignore PHPCompatibility.Classes.NewClasses.divisionbyzeroerrorFound
		} catch ( DivisionByZeroError $e ) {

			$error = 'Formula tried dividing by zero.';

		// phpcs:ignore PHPCompatibility.Classes.NewClasses.parseerrorFound
		} catch ( ParseError $e ) {

			$error = sprintf( 'Formula could not be parsed: "%s".', $e->getMessage() );

		// phpcs:ignore PHPCompatibility.Classes.NewClasses.errorFound
		} catch ( Error $e ) {

			$error = sprintf( 'Formula caused an error exception: "%s".', $e->getMessage() );
		}

		// Restore error handling.
		restore_error_handler();
		error_reporting( $prev_reporting_level );
		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		// Apply field-specific normalization.
		$result = $this->fields->normalize_calculation_result( $result, $field_settings );

		// Log error.
		if ( ! empty( $error ) ) {
			$this->helpers->log(
				sprintf(
					'Field #%1$s error: %2$s',
					$field_settings['id'],
					$error
				),
				'error'
			);
		}

		// Log calculation result.
		$this->helpers->log(
			sprintf(
				'Field #%1$s result: %2$s',
				$field_settings['id'],
				is_numeric( $result ) ? $result : '"' . $result . '"'
			),
			'debug'
		);

		/**
		 * Filter calculation result.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $result         Calculation result.
		 * @param array $field_settings Field settings.
		 * @param array $fields_entry   Fields entry data.
		 * @param array $form_data      Form data and settings.
		 */
		return apply_filters( 'wpforms_calculations_process_get_field_calculation_result', $result, $field_settings, $this->fields_entry, $this->form_data );
	}

	/**
	 * Error handler for get_field_calculation_result().
	 *
	 * @since 1.0.0
	 *
	 * @param integer $errno  Error code.
	 * @param string  $errstr Error message.
	 *
	 * @throws Error               When calculation code has errors.
	 * @throws DivisionByZeroError When calculation code has division by zero.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function error_handler( $errno, $errstr ) {

		if ( $errstr === 'Division by zero' ) {
			throw new DivisionByZeroError();
		}

		throw new Error( esc_html( $errstr ) );
	}

	/**
	 * Get formula PHP code.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_settings Field settings.
	 *
	 * @return string
	 */
	private function get_formula_php_code( $field_settings ) {

		$formula_php_code = sprintf(
			str_replace(
				"\t\t\t\t",
				'',
				'
				// All variables.
				%1$s

				// Define result variable.
				$%3$s = "";

				// Calculation code.
				%2$s

				// Return result.
				return $%3$s;'
			),
			$this->fields_vars->get_all_fields_variables( $this->fields_entry ),
			$field_settings['calculation_code_php'],
			Transpiler::RESULT_VAR_NAME
		);

		// Remove not needed open tag.
		return str_replace( [ '<?php' . "\n\n", '<?php' ], '', $formula_php_code );
	}

	/**
	 * Apply conditional logic to entry fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields_entry List of entry fields.
	 * @param array $form_data    Form data and settings.
	 *
	 * @return array
	 */
	private function apply_conditional_logic( array $fields_entry, array $form_data ): array {

		if ( empty( $fields_entry ) ) {
			return $fields_entry;
		}

		foreach ( $fields_entry as $field_id => $field ) {
			if (
				! is_array( $field ) ||
				! wpforms_conditional_logic_fields()->field_is_hidden( $this->form_data, $field_id )
			) {
				continue;
			}

			// Reset field value.
			$fields_entry[ $field_id ] = $this->reset_entry_field_value( $field, $form_data['fields'][ $field_id ] );

			// Save field visibility.
			$fields_entry[ $field_id ]['visible'] = false;
		}

		return $fields_entry;
	}

	/**
	 * Reset invisible fields in entry.
	 *
	 * @since 1.3.0
	 *
	 * @param array $fields_entry List of entry fields.
	 * @param array $form_data    Form data and settings.
	 *
	 * @return array
	 */
	private function reset_invisible_fields( array $fields_entry, array $form_data ): array {

		if ( empty( $fields_entry ) ) {
			return $fields_entry;
		}

		foreach ( $fields_entry as $field_id => $field ) {
			if ( ! is_array( $field ) || ! isset( $field['visible'] ) || $field['visible'] ) {
				continue;
			}

			// Reset field value.
			$fields_entry[ $field_id ] = $this->reset_entry_field_value( $field, $form_data['fields'][ $field_id ] ?? [] );
		}

		return $fields_entry;
	}


	/**
	 * Reset entry field value.
	 *
	 * @since 1.3.0
	 *
	 * @param array $entry_field Entry field data.
	 * @param array $form_field  Form field data.
	 *
	 * @return array
	 */
	private function reset_entry_field_value( array $entry_field, array $form_field ): array {

		// Reset field value.
		$entry_field['value'] = '';

		// Reset Payment fields entry data.
		if ( isset( $form_field['type'] ) && strpos( $form_field['type'], 'payment' ) === 0 ) {
			$entry_field['amount_raw'] = 0;
			$entry_field['amount']     = 0;
		}

		return $entry_field;
	}
}
