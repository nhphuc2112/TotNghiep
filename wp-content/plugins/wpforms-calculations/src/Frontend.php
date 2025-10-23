<?php

namespace WPFormsCalculations;

use WPFormsCalculations\Transpiler\DateTime;
use WPFormsCalculations\Transpiler\Transpiler;

/**
 * Frontend calculations class.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * Script handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'wpforms-calculations';

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
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->helpers  = wpforms_calculations()->helpers;
		$this->datetime = wpforms_calculations()->datetime;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_action( 'wpforms_frontend_js', [ $this, 'frontend_js' ] );
		add_filter( 'wpforms_field_properties', [ $this, 'field_properties' ], 10, 3 );
	}

	/**
	 * Determine if assets need to be enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms on the current page.
	 *
	 * @return bool
	 */
	private function is_enqueue_assets( $forms ) {

		return wpforms()->obj( 'frontend' )->assets_global() || $this->helpers->has_calculation_enabled_field( $forms );
	}

	/**
	 * Enqueue frontend JS.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms on the current page.
	 */
	public function frontend_js( $forms ) {

		if ( ! $this->is_enqueue_assets( $forms ) ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// Manually load the wp-hooks script.
		wp_enqueue_script( 'wp-hooks' );

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			WPFORMS_CALCULATIONS_URL . "assets/js/frontend/frontend{$min}.js",
			[ 'jquery', 'wp-hooks' ],
			WPFORMS_CALCULATIONS_VERSION,
			true
		);

		$this->localize_data( $forms );
	}

	/**
	 * Localize calculations data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms list.
	 */
	private function localize_data( $forms ) {

		$form_fields = [];

		// Prepare form fields data.
		foreach ( $forms as $form_id => $form ) {
			$form_fields[ $form_id ] = $form['fields'] ?? [];
		}

		// Make time zone string compatible with Luxon JS library.
		// Instead of '+03:00' we should have 'UTC+03:00'.
		$time_zone = preg_replace( '/^([+-]\d+)(:\d+)*/', 'UTC$1$2', wp_timezone_string() );

		// Localize data.
		wp_localize_script(
			self::SCRIPT_HANDLE,
			'wpforms_calculations',
			[
				'code'                    => $this->get_calculations_code( $forms ),
				'formFields'              => $form_fields,
				'choicesShowValuesFilter' => wpforms_show_fields_options_setting(),
				'debug'                   => wpforms_debug(),
				'calcDebug'               => $this->helpers->is_calc_debug(),
				'allowedFields'           => $this->helpers->get_fields_allowed_in_calculation(),
				'timeZone'                => $time_zone,
				'datetimeDefaultFormat'   => $this->datetime->get_default_format(),
				'datetimeDefaultFormats'  => $this->datetime->get_default_formats(),
				'resultVarName'           => Transpiler::RESULT_VAR_NAME,
				'functionsArrayName'      => Transpiler::FUNCTIONS_ARRAY_NAME,
				'innerFunctionsArrayName' => Transpiler::INNER_FUNCTIONS_ARRAY_NAME,
				'strings'                 => [
					'debugPrefix'            => __( 'WPForms Calculations Debug:', 'wpforms-calculations' ),
					'errorPrefix'            => __( 'WPForms Calculations Error:', 'wpforms-calculations' ),

					/* translators: %1$s - Form ID; %2$s - field ID. */
					'errorFormFieldPrefix'   => __( 'Form: #%1$s, field #%2$s:', 'wpforms-calculations' ),

					/* translators: %1$s - Field ID. */
					'errorCircularReference' => __( 'Circular reference detected in field #%1$s.', 'wpforms-calculations' ),

					/* translators: %1$s - Field ID. */
					'errorDivisionByZero'    => __( 'Formula tried dividing by zero.', 'wpforms-calculations' ),
					'readonlyInputTitle'     => __( 'This field is read-only because the value is the result of a calculation.', 'wpforms-calculations' ),
				],
			]
		);
	}

	/**
	 * Field properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 * @param array $form_data  Prepared form data/settings.
	 *
	 * @return array Modified field properties.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function field_properties( $properties, $field, $form_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$properties['container']['data']['field-type'] = $field['type'];

		if ( empty( $field['calculation_is_enabled'] ) || empty( $field['calculation_code_php'] ) ) {
			return $properties;
		}

		// The field should have a marker class.
		$properties['container']['class'][] = 'wpforms-calculations-field';

		$properties = $this->apply_readonly_property( $properties, $field );

		// The field default value and range restrictions should be cleared.
		unset(
			$properties['inputs']['primary']['attr']['value'],
			$properties['inputs']['primary']['attr']['min'],
			$properties['inputs']['primary']['attr']['max']
		);

		return $properties;
	}

	/**
	 * The field shouldn't allow manual input. Except for hidden fields.
	 *
	 * @since 1.3.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Current field specific data.
	 *
	 * @return array
	 */
	private function apply_readonly_property( array $properties, array $field ): array {

		// Readonly property can't be applied to hidden inputs (e.g., Single Payment field with single and hidden formats).
		if (
			$field['type'] === 'hidden' ||
			( $field['type'] === 'payment-single' && isset( $field['format'] ) && $field['format'] !== 'user' )
		) {
			return $properties;
		}

		$properties['inputs']['primary']['attr']['readonly'] = 'readonly';

		return $properties;
	}

	/**
	 * Get calculations code data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms list.
	 *
	 * @return array
	 */
	private function get_calculations_code( $forms ) {

		// Detect if an array of forms is being passed, or the form data from a single form.
		if ( ! empty( $forms['fields'] ) ) {
			$forms = [ $forms ];
		}

		$calculations = [];

		foreach ( $forms as $form ) {
			if ( empty( $form['fields'] ) ) {
				continue;
			}

			$form_id = (int) $form['id'];

			foreach ( $form['fields'] as $field ) {
				if (
					empty( $field['calculation_is_enabled'] ) ||
					empty( $field['calculation_code_js'] )
				) {
					continue;
				}

				$field_id = (int) $field['id'];

				$calculations[ $form_id ][ $field_id ] = $field['calculation_code_js'];
			}
		}

		return $calculations;
	}
}
