<?php

namespace WPFormsCalculations\Admin;

use WPForms_Field;
use WPFormsCalculations\Helpers;
use WPFormsCalculations\Transpiler\Functions;
use WPForms\Integrations\AI\Helpers as AIHelpers;

/**
 * Calculation feature.
 *
 * @since 1.0.0
 */
class Builder {

	/**
	 * Script handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private const SCRIPT_HANDLE = 'wpforms-calculations-builder';

	/**
	 * Modules handle.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private const MODULES_HANDLE = 'wpforms-calculations-builder-modules';

	/**
	 * Support calculations in these field types.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public const ALLOWED_FIELD_TYPES = [ 'text', 'textarea', 'number', 'hidden', 'payment-single' ];

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Functions class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Functions
	 */
	private $functions;

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->helpers   = wpforms_calculations()->helpers;
		$this->functions = new Functions();

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_css' ] );
		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_js' ] );
		add_action( 'wpforms_field_options_bottom_advanced-options', [ $this, 'advanced_options' ], 50, 2 );
		add_filter( 'wpforms_integrations_ai_admin_builder_enqueues_localize_chat_strings', [ $this, 'localize_chat_data' ] );
	}

	/**
	 * Enqueue builder CSS.
	 *
	 * @since 1.0.0
	 */
	public function builder_css() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::SCRIPT_HANDLE,
			WPFORMS_CALCULATIONS_URL . "assets/css/builder{$min}.css",
			[],
			WPFORMS_CALCULATIONS_VERSION
		);
	}

	/**
	 * Localize chat data.
	 *
	 * @since 1.6.0
	 *
	 * @param array $strings Localized strings.
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 */
	public function localize_chat_data( $strings ): array {

		$min = wpforms_get_min_suffix();

		$learn_more_url = wpforms_utm_link( 'https://wpforms.com/features/wpforms-ai/', 'Builder - Settings', 'Learn more - AI Calculations modal' );

		$strings['calculations'] = [
			'title'           => esc_html__( 'Generate Formula', 'wpforms-calculations' ),
			'description'     => esc_html__( 'Describe the formula you would like to create or use one of the examples below to get started.', 'wpforms-calculations' ),
			'placeholder'     => esc_html__( 'What would you like to create?', 'wpforms-calculations' ),
			'initialChat'     => $this->get_initial_chat(),
			'learnMore'       => esc_html__( 'Learn More About WPForms AI', 'wpforms-calculations' ),
			'insert'          => esc_html__( 'Insert Formula', 'wpforms-calculations' ),
			'learnMoreButton' => esc_html__( 'Learn More', 'wpforms-calculations' ),
			'footer'          => wp_kses(
				__( '<strong>What do you think of this formula?</strong> If you’re happy with it, you can insert this formula, or make changes by entering additional prompts.', 'wpforms-calculations' ),   // phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
				[
					'strong' => [],
				]
			),
			'footerLearnMore' => wp_kses(
				sprintf(
					/* translators: 1: Link to the documentation, 2: Link closing tag. */
					__( 'If you need help generating formulas, %1$s check out our documentation%2$s.', 'wpforms-calculations' ),
					'<a href="' . $learn_more_url . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			'warning'         => esc_html__( 'It looks like you have some existing formulas in this field. If you generate new formulas, your existing formulas will be overwritten. You can simply close this window if you’d like to keep your existing formulas.', 'wpforms-calculations' ),
			'waiting'         => esc_html__( 'Just a minute...', 'wpforms-calculations' ),
			'descrEndDot'     => '.',
			'learnMoreUrl'    => $learn_more_url,
			'errors'          => [
				'default'    => esc_html__( 'An error occurred while generating formula.', 'wpforms-calculations' ),
				'rate_limit' => esc_html__( 'Sorry, you\'ve reached your daily limit for generating formulas.', 'wpforms-calculations' ),
			],
			'reasons'         => [
				'rate_limit' => sprintf(
					wp_kses( /* translators: %s - WPForms contact support link. */
						__( 'You may only generate calculation formulas 50 times per day. If you believe this is an error, <a href="%s" target="_blank" rel="noopener noreferrer">please contact WPForms support</a>.', 'wpforms-calculations' ),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					),
					wpforms_utm_link( 'https://wpforms.com/account/support/', 'Builder - Settings', 'AI Calculations limit - Contact Support' )
				),
			],
			'warnings'        => [
				'prohibited_code' => esc_html__( 'Prohibited code has been removed from your calculation formula.', 'wpforms-calculations' ),
			],
		];

		$strings['actions']['calculations'] = 'wpforms_get_ai_calculations';
		$strings['modules'][]               = [
			'name' => 'calculations',
			'path' => sprintf(
				'%sassets/js/builder/modules/helpers/calculations-chat%s.js',
				WPFORMS_CALCULATIONS_URL,
				$min
			),
		];

		return $strings;
	}

	/**
	 * Get initial chat.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_initial_chat(): string {

		$header         = __( 'Hi there! Describe the calculation formula you’d like to create.', 'wpforms-calculations' );
		$first_line     = __( 'For example, "calculate the total price of items" or "calculate sales tax."', 'wpforms-calculations' );
		$second_line    = __( 'Just make sure you have the necessary fields in your form for the calculation formula you’d like.', 'wpforms-calculations' );
		$learn_more     = __( 'Learn More', 'wpforms-calculations' );
		$learn_more_url = wpforms_utm_link( 'https://wpforms.com/docs/generating-calculation-formulas-with-wpforms-ai/', 'Builder - Settings', 'Learn more - AI Calculations modal' );

		return wp_kses(
			sprintf(
				'<p>
					<strong>
						%1$s
					</strong>
				</p>
				<p>
					%2$s
				</p>
				<p>
					%3$s <a class="wpforms-chat-element-learn-more" href="%4$s" target="_blank" rel="noopener noreferrer">%5$s</a>
				</p>',
				$header,
				$first_line,
				$second_line,
				$learn_more_url,
				$learn_more
			),
			[
				'strong' => [],
				'p'      => [],
				'a'      => [
					'href'   => [],
					'target' => [],
					'rel'    => [],
					'class'  => [],
				],
			]
		);
	}

	/**
	 * Enqueue builder js.
	 *
	 * @since 1.0.0
	 */
	public function builder_js() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_code_editor( [ 'type' => 'text/php' ] );

		wp_enqueue_script(
			self::MODULES_HANDLE,
			WPFORMS_CALCULATIONS_URL . "assets/js/builder/modules.es5{$min}.js",
			[ 'jquery', 'wpforms-builder' ],
			WPFORMS_CALCULATIONS_VERSION,
			true
		);

		$dependencies = [ 'jquery', 'wpforms-builder', self::MODULES_HANDLE ];

		if ( ! AIHelpers::is_disabled() ) {
			$dependencies[] = 'wpforms-ai-dock';
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			WPFORMS_CALCULATIONS_URL . "assets/js/builder/builder{$min}.js",
			$dependencies,
			WPFORMS_CALCULATIONS_VERSION,
			true
		);

		// Localize data.
		wp_localize_script(
			self::SCRIPT_HANDLE,
			'wpforms_calculations',
			[
				'allowedFields'               => $this->helpers->get_fields_allowed_in_calculation(),
				'calculationIsPossibleFields' => $this->helpers->get_fields_calculation_is_possible(),
				'functionsList'               => $this->functions->get_names(),
				'isLicenseActive'             => AIHelpers::is_license_active(),
				'isAiDisabled'                => AIHelpers::is_disabled(),
				'strings'                     => [
					'insertFieldDropdownTitle'         => esc_html__( 'Form Fields', 'wpforms-calculations' ),
					'validateButtonSuccess'            => esc_html__( 'The code is correct. Click to re-validate.', 'wpforms-calculations' ),
					'validateButtonErrors'             => esc_html__( 'The code has errors. Click to re-validate.', 'wpforms-calculations' ),
					'validateButtonAjaxError'          => esc_html__( 'The validation attempt failed, there was a problem communicating with the server', 'wpforms-calculations' ),
					'validationModalErrorTitle'        => esc_html__( 'Uh oh!', 'wpforms-calculations' ),
					'validationModalErrorMsg'          => esc_html__( 'There appears to be a problem with your formula. Please fix the formula and try again', 'wpforms-calculations' ),
					'ajaxFail'                         => esc_html__( 'Formula validation AJAX call error; Server response text:', 'wpforms-calculations' ),
					/* translators: %1$s - Variable name. */
					'validationVariableNotAllowed'     => esc_html__( 'Variable %1$s is not allowed. Only $Fx field variables are allowed.', 'wpforms-calculations' ),
					/* translators: %1$s - Field ID. */
					'validationFieldDoesntExist'       => esc_html__( 'Variable $F%1$s cannot be used. Field with ID %1$s does not exist.', 'wpforms-calculations' ),
					/* translators: %1$s - Total field ID, %2$s - Single Item field ID. */
					'validationFieldTotalInSingleItem' => esc_html__( 'Field Total (ID %1$s) could not be used in the Single Item field (ID %2$s).', 'wpforms-calculations' ),
					/* translators: %1$s - Field ID, %2$s - field type. */
					'validationFieldNotAllowed'        => esc_html__( 'Variable $F%1$s is not allowed. Field with ID %1$s has type "%2$s", which is not allowed.', 'wpforms-calculations' ),
					/* translators: %1$s - Field ID, %2$s - subfield. */
					'validationSubfieldNotAllowed'     => esc_html__( 'Variable $F%1$s_%2$s is not allowed. Field with ID %1$s does not have "%2$s" subfield.', 'wpforms-calculations' ),
					/* translators: %1$s - Field ID. */
					'validationRepeaterFieldNotAllowed' => esc_html__( 'The variable ($F%1$s) cannot be used because the field is inside a Repeater. Please revise and try again.', 'wpforms-calculations' ),
					'field'                            => esc_html__( 'Field', 'wpforms-calculations' ),
					'fields'                           => esc_html__( 'Fields', 'wpforms-calculations' ),
					'thisFieldUsedInField'             => esc_html__( 'It\'s used in calculation in the following field:', 'wpforms-calculations' ),
					'thisFieldUsedInFields'            => esc_html__( 'It\'s used in calculations in the following fields:', 'wpforms-calculations' ),
					'aiCalculations'                   => esc_html__( 'AI Calculations', 'wpforms-calculations' ),
					'fixWithAi'                        => esc_html__( 'Fix with AI', 'wpforms-calculations' ),
					'ok'                               => esc_html__( 'Okay', 'wpforms-calculations' ),
					'fixWithAiInitialPrompt'           =>
					sprintf(
						'<h4>%1$s</h4><p>%2$s</p>',
						esc_html__( 'Fix my formula', 'wpforms-calculations' ),
						esc_html__( 'I am trying to create calculation formula for my form. When I validate my formula I am getting following errors:', 'wpforms-calculations' )
					),
				],
			]
		);
	}

	/**
	 * Calculation options.
	 *
	 * @since 1.0.0
	 *
	 * @param array         $field     Field data.
	 * @param WPForms_Field $field_obj Field object instance.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function advanced_options( $field, $field_obj ) {

		// Limit to our specific field types.
		if (
			! isset( $field['type'] ) ||
			! in_array( $field['type'], $this->helpers->get_fields_calculation_is_possible(), true )
		) {
			return;
		}

		$is_enabled = $field['calculation_is_enabled'] ?? '0';

		$field_obj->field_element(
			'row',
			$field,
			[
				'slug'    => 'calculation_is_enabled',
				'class'   => '',
				'content' => $field_obj->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'calculation_is_enabled',
						'value'   => $is_enabled,
						'desc'    => esc_html__( 'Enable Calculation', 'wpforms-calculations' ),
						'tooltip' => esc_html__( 'Enable calculations for this field.', 'wpforms-calculations' ),
					],
					false
				),
			]
		);

		$content = $field_obj->field_element(
			'label',
			$field,
			[
				'slug'          => 'calculation_code',
				'value'         => esc_html__( 'Formula', 'wpforms-calculations' ),
				'tooltip'       => esc_html__( 'Using your form fields as variables, enter your formula below and ensure there are no extra spaces. Validate your formula when finished.', 'wpforms-calculations' ),
				'after_tooltip' => $this->generate_formula_button( $field, $field_obj ),
			],
			false
		)
		. $this->editor_options_markup( $field, $field_obj );

		$field_obj->field_element(
			'row',
			$field,
			[
				'slug'    => 'calculation_code',
				'class'   => $is_enabled ? '' : 'wpforms-hidden',
				'content' => $content,
			]
		);
	}

	/**
	 * Generate formula button.
	 *
	 * @since 1.6.0
	 *
	 * @param array         $field     Field data.
	 * @param WPForms_Field $field_obj Field object instance.
	 *
	 * @return string
	 */
	private function generate_formula_button( array $field, $field_obj ): string {

		if ( AIHelpers::is_disabled() ) {
			return '';
		}

		$classes = [
			'wpforms-btn-purple',
			'wpforms-ai-modal-button',
			'wpforms-ai-calculations-button',
		];
		$data    = [
			'field-id' => $field['id'],
		];

		if ( ! AIHelpers::is_license_active() ) {
			$classes[]           = 'education-modal';
			$classes[]           = 'wpforms-prevent-default';
			$data['action']      = 'license';
			$data['field-name']  = esc_html__( 'AI Calculations', 'wpforms-calculations' );
			$data['utm-content'] = 'AI Calculations';
		}

		return $field_obj->field_element(
			'button',
			$field,
			[
				'type'  => 'calculations',
				'slug'  => 'ai_modal_button',
				'value' => esc_html__( 'Generate Formula', 'wpforms-calculations' ),
				'class' => wpforms_sanitize_classes( $classes ),
				'data'  => $data,
			],
			false
		);
	}

	/**
	 * Calculation editor options markup.
	 *
	 * @since 1.0.0
	 *
	 * @param array         $field     Field data.
	 * @param WPForms_Field $field_obj Field object instance.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	private function editor_options_markup( $field, $field_obj ) {

		$cheatsheet_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="wpforms-calculations-cheatsheet-link">
				<i class="fa fa-file-text-o"></i><span>%2$s</span>
			</a>',
			esc_url( wpforms_utm_link( 'https://wpforms.com/calculations-formula-cheatsheet/', 'Calculations Formula', 'Calculations Cheatsheet' ) ),
			esc_html__( 'Cheatsheet', 'wpforms-calculations' )
		);

		return '<div class="wpforms-calculations-editor-collapsed">
			<div class="wpforms-calculations-editor-wrap">
				<div class="toolbar">
					<button type="button" class="button-insert-field">' . esc_html__( 'Insert Field', 'wpforms-calculations' ) . '</button>
					<button type="button" class="button-plus">+</button>
					<button type="button" class="button-plus">-</button>
					<button type="button" class="button-divide">/</button>
					<button type="button" class="button-multiply">*</button>
					<button type="button" class="button-br-open">(</button>
					<button type="button" class="button-br-close">)</button>

					<button type="button" class="button-expand-editor wpforms-calculations-expand-editor"><i class="fa fa-expand"></i></button>
				</div>' .
				$field_obj->field_element(
					'textarea',
					$field,
					[
						'slug'  => 'calculation_code',
						'value' => $field['calculation_code'] ?? '',
						'class' => [ 'wpforms-codemirror-editor' ],
					],
					false
				) .
				'<div class="wpforms-calculations-below-editor">
					<div class="wpforms-calculations-validate-formula-wrap">
						<button type="button" class="wpforms-calculations-validate-formula">' . esc_html__( 'Validate Formula', 'wpforms-calculations' ) . '</button>
						<span class="wpforms-calculations-validate-formula-status"></span>
					</div>
					' . $cheatsheet_link . '
				</div>
				</div>
		</div>
		<div class="wpforms-calculations-editor-expanded"></div>';
	}
}
