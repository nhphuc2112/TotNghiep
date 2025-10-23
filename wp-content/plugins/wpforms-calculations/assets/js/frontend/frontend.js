/* global wpforms_calculations, wpforms, WPFormsUtils, WPFormsFormAbandonment */

'use strict'; // eslint-disable-line strict

/**
 * WPForms Calculation feature. Frontend.
 *
 * @param fieldValueCombine.address1
 * @param window.WPFormsCalculations
 * @param wpforms_calculations.allowedFields
 * @param wpforms_calculations.functionsArrayName
 * @param wpforms_calculations.innerFunctionsArrayName
 * @param wpforms_calculations.resultVarName
 * @param wpforms_calculations.calcDebug
 * @param wpforms_calculations.strings.errorPrefix
 * @param wpforms_calculations.strings.debugPrefix
 * @param wpforms_calculations.strings.errorCircularReference
 *
 * @since 1.0.0
 */
const WPFormsCalculations = window.WPFormsCalculations || ( function( document, window, $ ) {
	/**
	 * Elements holder.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const el = {};

	/**
	 * Functions allowed in calculations.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	let allowedFunctions = {};

	/**
	 * Calculation formulas registry.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const formulasRegistry = {};

	/**
	 * Forms fields values registry.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const formFieldsRegistry = {};

	/**
	 * Runtime vars.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const vars = {};

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Math functions.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		innerFunctions: {},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			$( document ).on( 'wpformsReady', app.setup );
		},

		/**
		 * Setup calculation engine. Load and init modules and prepare fields data registry.
		 *
		 * @since 1.0.0
		 */
		setup() {
			el.$forms = $( 'form.wpforms-form' );
			el.$document = $( document );

			vars.fieldsDisabledCalc = {};

			app.initModules();
			app.initFormFieldsRegistry();

			// Trigger event when calculations are set up.
			// This point is good to define custom functions via `wp.hooks.addFilter( 'wpformsCalculationsFunctions' )` filter.
			el.$document.trigger( 'wpformsCalculationsSetup' );
		},

		/**
		 * Bind events.
		 *
		 * @since 1.0.0
		 */
		events() {
			el.$document

				// Run calculations on input.
				.on(
					'input change',
					'.wpforms-field:not(.wpforms-calculations-field) :input, select.wpforms-payment-quantity',
					WPFormsUtils.debounce( app.inputEvent, 50 )
				)

				// Run calculations before sending a partial (abandoned) entry.
				.on( 'wpformsFormAbandonmentGetFormDataBefore', app.formAbandonmentGetFormDataBefore );
		},

		/**
		 * When functions module is loaded.
		 *
		 * @since 1.0.0
		 */
		functionsLoaded() {
			app.initFormulasRegistry();
			app.events();
			app.triggerAllFormsCalculations();

			// Trigger event when calculations are completely ready.
			el.$document.trigger( 'wpformsCalculationsReady' );
		},

		/**
		 * Init modules.
		 *
		 * @since 1.0.0
		 */
		initModules() {
			const functions = wpforms_calculations.debug ? './functions.js' : './functions.min.js',
				math = wpforms_calculations.debug ? './inner-functions.js' : './inner-functions.min.js',
				modules = wpforms_calculations.debug ? './modules.es5.js' : './modules.es5.min.js';

			// eslint-disable-next-line compat/compat
			Promise.all( [
				import( functions ),
				import( math ),
				import( modules ),
			] )
				.then( ( [ functionsModule, innerFunctionsModule ] ) => {
					allowedFunctions = functionsModule.default();
					app.innerFunctions = innerFunctionsModule.default();

					app.functionsLoaded();
				} );
		},

		/**
		 * Init form fields values registry.
		 *
		 * @since 1.0.0
		 */
		initFormFieldsRegistry() {
			el.$forms.each( function() {
				const $form = $( this );
				const formId = $form.data( 'formid' );

				formFieldsRegistry[ formId ] = app.getSingleFormFieldsValues( $form );
			} );
		},

		/**
		 * Init calculation functions registry.
		 *
		 * @since 1.0.0
		 */
		initFormulasRegistry() {
			let formulaFunctionCode = '',
				formulaCode = '';

			for ( const formId in wpforms_calculations.code ) {
				formulasRegistry[ formId ] = {};

				for ( const fieldId in wpforms_calculations.code[ formId ] ) {
					formulaCode = wpforms_calculations.code[ formId ][ fieldId ];

					// Compile the calculation formula function code.
					formulaFunctionCode = `

						// Define functions object.
						const $${ wpforms_calculations.functionsArrayName } = allowedFunctions;

						// Define inner functions object.
						const $${ wpforms_calculations.innerFunctionsArrayName } = WPFormsCalculations.innerFunctions;

						// Define result variable.
						let $${ wpforms_calculations.resultVarName } = '';

						// Define fields variables.
						${ app.getFormulaFieldsVariables( formId ) }

						try {
							${ formulaCode }
						} catch ( error ) {
							WPFormsCalculations.debug( error, { type: 'error', formId: ${ formId }, fieldId: ${ fieldId } } );
						}

						// Detect infinity, which is means that the formula tried division by zero.
						// This approach doesn't cover all cases, but it's better than nothing.
						if (
							typeof $${ wpforms_calculations.resultVarName } === 'number' &&
							! isFinite( $${ wpforms_calculations.resultVarName } )
						) {
							WPFormsCalculations.debug( '${ wpforms_calculations.strings.errorDivisionByZero.replace() }', { type: 'error', formId: ${ formId }, fieldId: ${ fieldId } } );

							return '';
						}

						return $${ wpforms_calculations.resultVarName };
					`;

					// Create the calculation function for this field.
					formulasRegistry[ formId ][ fieldId ] = new Function(
						'formId', 'fieldId', 'fields', 'eventFieldId', 'allowedFunctions', 'WPFormsCalculations',
						formulaFunctionCode
					);
				}
			}
		},

		/**
		 * Get the $F-variables definition code to put inside the resulting formula function.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId Form Id.
		 *
		 * @return {string} F-variables definition code.
		 */
		getFormulaFieldsVariables( formId ) { // eslint-disable-line complexity
			if ( ! formFieldsRegistry[ formId ] || ! formFieldsRegistry[ formId ].fields ) {
				return '';
			}

			const fieldsRegistry = formFieldsRegistry[ formId ].fields,
				fVars = [];

			let	field,
				varName;

			for ( const fieldId in fieldsRegistry ) {
				field = fieldsRegistry[ fieldId ];
				varName = '$F' + fieldId;

				if ( ! app.isObject( field ) ) {
					fVars.push( `${ varName } = fields['${ fieldId }']` );

					continue;
				}

				for ( const subField in fieldsRegistry[ fieldId ] ) {
					varName = '$F' + fieldId;
					varName += subField === 'value' ? '' : '_' + subField;

					fVars.push( `${ varName } = fields['${ fieldId }']['${ subField }']` );
				}
			}

			return 'const ' + fVars.join( ',\n' ) + ';';
		},

		/**
		 * Trigger calculations in all forms.
		 *
		 * @since 1.0.0
		 */
		triggerAllFormsCalculations() {
			el.$forms.each( function() {
				app.inputEvent.call( $( this ).find( ':input:first' ).get( 0 ), {} );
			} );
		},

		/**
		 * Get field values of a single form.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery|HTMLElement} $form Form jQuery object or DOM element.
		 *
		 * @return {Object} Form fields values.
		 */
		getSingleFormFieldsValues( $form ) {
			const inputs = $form.find( '[name^="wpforms[fields]"]' );

			let	formFieldsValues = {};

			vars.arrayNames = {};

			for ( let i = 0; i < inputs.length; i++ ) {
				formFieldsValues = app.addSingleInputValueToFormFieldsValuesObject( $( inputs[ i ] ), formFieldsValues );
			}

			// After getting the values of all inputs, it is necessary to additionally update the value objects for some fields.
			formFieldsValues = app.updateSingleFormFieldsValues( formFieldsValues, $form );

			return formFieldsValues;
		},

		/**
		 * Add single input value to the form fields values object.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $input           Input element.
		 * @param {Object} formFieldsValues Form fields values object.
		 *
		 * @return {Object} Form fields values object.
		 */
		addSingleInputValueToFormFieldsValuesObject( $input, formFieldsValues ) { // eslint-disable-line complexity
			const $field = $input.closest( '.wpforms-field' ),
				fieldId = $field.data( 'field-id' ),
				nameAttr = $input.prop( 'name' ) || '';

			if ( ! $field.length || fieldId === undefined || fieldId.toString().includes( '_' ) || ! nameAttr.startsWith( 'wpforms' ) ) {
				return formFieldsValues;
			}

			const fieldType = $field.data( 'field-type' );

			// Check if the field is allowed to be used in calculations.
			if ( ! app.isAllowedField( fieldType ) ) {
				return formFieldsValues;
			}

			const fieldValueObject = app.getFieldInputValueObjectFromDOM( $input, $field );

			if ( fieldValueObject === null ) {
				return formFieldsValues;
			}

			formFieldsValues = $.extend( true, formFieldsValues, fieldValueObject );
			formFieldsValues.fields[ fieldId ] = app.addAmountToPaymentFieldValue( formFieldsValues.fields[ fieldId ], $input, fieldType, $field );

			return formFieldsValues;
		},

		/**
		 * Update single form fields values.
		 * Add combined value to the field value object.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} formFieldsValues Form fields values object.
		 * @param {jQuery} $form            Form jQuery object.
		 *
		 * @return {Object} Form fields values object.
		 */
		updateSingleFormFieldsValues( formFieldsValues, $form ) {
			const formId = $form.data( 'formid' ),
				formFields = wpforms_calculations.formFields[ formId ];

			if ( ! formFieldsValues.fields || ! formFields ) {
				return formFieldsValues;
			}

			for ( const fieldId in formFieldsValues.fields ) {
				// Skip if field value is not an object, or it already has `value` property.
				if (
					! app.isObject( formFieldsValues.fields[ fieldId ] ) ||
					typeof formFieldsValues.fields[ fieldId ].value !== 'undefined'
				) {
					continue;
				}

				const fieldUpdateResult = app.updateSingleFormFieldValue( formId, fieldId, formFieldsValues );

				// Get field value as array and remove empty values.
				const fieldValueArray = Object.values( fieldUpdateResult.fieldValueCombine )
					.filter( function( item ) {
						return item !== '' && item !== false;
					} );

				// Update form fields values object.
				formFieldsValues.fields[ fieldId ] = fieldUpdateResult.formFieldsValues.fields[ fieldId ];

				// Finally get combined field value and update form fields values object.
				formFieldsValues.fields[ fieldId ].value = fieldValueArray.join( fieldUpdateResult.separator );
			}

			return formFieldsValues;
		},

		/**
		 * Update single form field value.
		 * Prepare complex field value to be combined.
		 *
		 * @since 1.0.0
		 *
		 * @param {number|string} formId           Form ID.
		 * @param {number|string} fieldId          Field ID.
		 * @param {Object}        formFieldsValues Form fields values object.
		 *
		 * @return {Object} Result object: { separator, formFieldsValues, fieldValueCombine }.
		 */
		updateSingleFormFieldValue( formId, fieldId, formFieldsValues ) { // eslint-disable-line complexity
			const fieldType = app.getFormFieldData( formId, fieldId )?.type;

			if ( ! fieldType ) {
				return { separator: '', formFieldsValues, fieldValueCombine: {} };
			}

			let fieldValueCombine = $.extend( true, {}, formFieldsValues.fields[ fieldId ] );

			// Default separator.
			let separator = '\n';

			// Separator for Name and Date / Time fields.
			if ( [ 'name', 'date-time' ].includes( fieldType ) ) {
				separator = ' ';
			}

			// Date-time field specific logic for old-fashion date-dropdown field type.
			if ( fieldType === 'date-time' && app.isObject( formFieldsValues.fields[ fieldId ].date ) ) {
				const dateAry = Object.values( formFieldsValues.fields[ fieldId ].date ).map( function( item ) {
					return ! item ? '' : item.toString().padStart( 2, '0' );
				} );

				formFieldsValues.fields[ fieldId ].date = dateAry.join( '/' );
				fieldValueCombine.date = formFieldsValues.fields[ fieldId ].date;

				return { separator, formFieldsValues, fieldValueCombine };
			}

			// Email field specific logic.
			if ( fieldType === 'email' ) {
				formFieldsValues.fields[ fieldId ].value = fieldValueCombine.primary;
				delete fieldValueCombine.secondary;

				return { separator, formFieldsValues, fieldValueCombine };
			}

			// Address field specific logic.
			if ( fieldType === 'address' ) {
				fieldValueCombine = app.toStrings( fieldValueCombine );

				// Combined value should be empty if address1 and city are empty.
				if ( fieldValueCombine.address1 === '' && fieldValueCombine.city === '' ) {
					formFieldsValues.fields[ fieldId ].value = '';
					fieldValueCombine = {};

					return { separator, formFieldsValues, fieldValueCombine };
				}

				// Combine city and state values.
				if ( fieldValueCombine.city.length && fieldValueCombine.state.length ) {
					fieldValueCombine.city = fieldValueCombine.city + ', ' + fieldValueCombine.state;
				} else if ( fieldValueCombine.state.length ) {
					fieldValueCombine.city = fieldValueCombine.state;
				}

				delete fieldValueCombine.state;

				// Update country value if it's empty.
				fieldValueCombine.country = fieldValueCombine.country || 'US';
				formFieldsValues.fields[ fieldId ].country = fieldValueCombine.country;

				return { separator, formFieldsValues, fieldValueCombine };
			}

			// Checkbox and payment-checkbox fields specific logic.
			if ( fieldType === 'checkbox' || fieldType === 'payment-checkbox' ) {
				separator = ',\n';

				// Remove amount from combined value.
				delete fieldValueCombine.amount;
			}

			return { separator, formFieldsValues, fieldValueCombine };
		},

		/**
		 * Add amount data to payment field value object.
		 *
		 * @since 1.0.0
		 *
		 * @param {object|string} fieldValue Single field value.
		 * @param {jQuery}        $input     Input element.
		 * @param {string}        fieldType  Single field value.
		 * @param {jQuery}        $field     Field container.
		 *
		 * @return {Object} Payment field value object.
		 */
		addAmountToPaymentFieldValue( fieldValue, $input, fieldType, $field ) { // eslint-disable-line complexity
			let value, amount;

			if ( ! fieldType.startsWith( 'payment-' ) ) {
				return fieldValue;
			}

			if ( app.isHiddenByCL( $field ) ) {
				return {
					value: '',
					amount: '',
				};
			}

			// Get the payment-checkbox input amount.
			if ( fieldType === 'payment-checkbox' ) {
				const choiceAmount = $input.is( ':checked' ) ? app.amountSanitize( $input.data( 'amount' ) ) : 0;

				value = undefined; // Undefined means that we will not update the `value` element of the field value object.
				amount = fieldValue.amount ? fieldValue.amount + choiceAmount : choiceAmount;
			}

			//  Get the payment-single or payment-total input amount.
			if ( fieldType === 'payment-single' || fieldType === 'payment-total' ) {
				amount = app.amountSanitize( fieldValue );
				value = wpforms.amountFormatSymbol( amount );
			}

			//  Get the payment-multiple input amount.
			if ( fieldType === 'payment-multiple' ) {
				value = fieldValue;
				amount = $input.is( ':checked' ) ? $input.data( 'amount' ) : $input.closest( 'ul' ).find( 'input:checked' ).data( 'amount' );
				amount = app.amountSanitize( amount );
			}

			//  Get the payment-select input amount.
			if ( fieldType === 'payment-select' ) {
				value = fieldValue;
				amount = app.amountSanitize( $input.find( ':selected' ).data( 'amount' ) );
			}

			fieldValue = app.isObject( fieldValue ) ? fieldValue : {};

			if ( typeof value !== 'undefined' ) {
				fieldValue.value = value;
			}

			// Re-calculate amount based on the quantity selected.
			if ( fieldType === 'payment-select' || fieldType === 'payment-single' ) {
				amount = amount * app.getFieldQuantity( $input );
			}

			fieldValue.amount = amount;

			return fieldValue;
		},

		/**
		 * Get the field quantity value.
		 *
		 * @since 1.1.0
		 *
		 * @param {jQuery} $input Input element.
		 *
		 * @return {number} Quantity value.
		 */
		getFieldQuantity( $input ) {
			if ( ! $input.closest( '.wpforms-field' ).hasClass( 'wpforms-payment-quantities-enabled' ) ) {
				return 1;
			}

			const $quantityInput = $( '#' + $input.attr( 'id' ) + '-quantity' );

			return $quantityInput.length ? Number( $quantityInput.val() ) : 1;
		},

		/**
		 * Get the input value as the raw object from DOM.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $input Input element.
		 * @param {jQuery} $field Field container.
		 *
		 * @return {Object} Field value data.
		 */
		getFieldInputValueObjectFromDOM( $input, $field ) { // eslint-disable-line complexity
			// Remove `wpforms` prefix, replace `]` with `` and split by `[`.
			const name = $input.prop( 'name' ).replace( /^wpforms\[/gi, '' ).replace( /]/gi, '' ).split( '[' ),
				isCheckbox = $input.is( ':checkbox' ),
				isSelect = $input.is( 'select' ),
				isHiddenByCL = app.isHiddenByCL( $field );

			let value = isHiddenByCL ? '' : app.getFieldInputValueRaw( $input ),
				result = {};

			for ( let i = name.length - 1; i >= 0; i-- ) {
				let nestName = name[ i ] || '';

				if ( nestName.length === 0 && isCheckbox ) {
					result = {};

					const match = $input.attr( 'id' ).match( /[0-9]+$/g );

					vars.arrayNames[ name[ i - 1 ] ] = match[ 0 ];
					nestName = vars.arrayNames[ name[ i - 1 ] ];
				}

				if ( nestName.length === 0 && isSelect ) {
					nestName = 'value';
				}

				// Build a nesting object.
				if ( i < name.length - 1 ) {
					const newObj = result;

					result = {};
					result[ nestName ] = newObj;

					continue;
				}

				const parseFloatValue = parseFloat( value );

				// Add value to the result object.
				if ( value === 'true' ) {
					value = true;
				} else if ( value === 'false' ) {
					value = false;
				} else if ( ! isHiddenByCL && ! isNaN( parseFloatValue ) && parseFloatValue.toString() === value ) {
					value = parseFloatValue;
				} else if ( typeof value === 'string' && ( value[ 0 ] === '{' || value[ 0 ] === '[' ) ) {
					try {
						value = JSON.parse( value );
					} catch ( e ) {} // eslint-disable-line no-empty
				}

				result[ nestName ] = value;
			}

			return result;
		},

		/**
		 * Get input raw value.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $input Input element.
		 *
		 * @return {string|number} Field value.
		 */
		getFieldInputValueRaw( $input ) { // eslint-disable-line complexity
			const $field = $input.closest( '.wpforms-field' ),
				fieldType = $field.data( 'field-type' );

			let value;

			if ( fieldType.startsWith( 'payment-' ) ) {
				return app.getPaymentFieldInputValueRaw( $input, fieldType );
			}

			const formId = $field.closest( 'form' ).data( 'formid' ),
				fieldId = $field.data( 'field-id' ),
				fieldData = app.getFormFieldData( formId, fieldId );

			// If the choice-based fields have enabled values by filter `wpforms_fields_show_options_setting`
			// and the field has enabled `Show Values` option.
			if (
				wpforms_calculations.choicesShowValuesFilter &&
				fieldData?.show_values
			) {
				return app.getFieldInputValueRawShowValues( $input, fieldType );
			}

			// Without `Show Values` option enabled.
			const id = $input.attr( 'id' );

			let $label, $checkedInput;

			switch ( fieldType ) {
				case 'checkbox':
					$label = $input.closest( 'li' ).find( `label[for="${ id }"]` );

					return $input.is( ':checked' ) ? $label.text().trim() : '';

				case 'radio':
					$checkedInput = $input.closest( 'ul' ).find( 'input:checked' );
					$label = $checkedInput.closest( 'li' ).find( `label[for="${ $checkedInput.attr( 'id' ) }"]` );

					return $label.text().trim();

				case 'rating':
					value = $input.is( ':checked' )
						? $input.val()
						: $input.closest( '.wpforms-field-rating-items' ).find( 'input:checked' ).val();

					return value || '';

				case 'number':
					value = $input.val();

					return value.length === 0 ? 0 : Number( $input.val() );

				case 'phone':
					return $input.siblings( '.wpforms-smart-phone-field' ).val();

				case 'select':
					const $selected = $input.find( ':selected' );

					return $selected.length > 1
						? $selected.map( function() {
							return $( this ).text();
						} ).get().join( ',\n' )
						: $selected.text();

				default:
					return $input.val();
			}
		},

		/**
		 * Get input raw value for the case if `Show Values` option is enabled.
		 *
		 * @since 1.1.0
		 *
		 * @param {jQuery} $input    Input element.
		 * @param {string} fieldType Field type.
		 *
		 * @return {string|number} Field value.
		 */
		getFieldInputValueRawShowValues( $input, fieldType ) { // eslint-disable-line complexity
			let value;

			switch ( fieldType ) {
				case 'checkbox':
					return $input.is( ':checked' ) ? $input.val() : '';

				case 'radio':
					value = $input.is( ':checked' )
						? $input.val()
						: $input.closest( 'ul, .wpforms-field-rating-items' ).find( 'input:checked' ).val();

					return value || '';

				case 'select':
					value = $input.val();

					return Array.isArray( value ) ? value.join( ',\n' ) : value;

				default:
					return $input.val();
			}
		},

		/**
		 * Get Payment field input raw value.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $input    Input element.
		 * @param {string} fieldType Field type.
		 *
		 * @return {string} Field value.
		 */
		getPaymentFieldInputValueRaw( $input, fieldType ) { // eslint-disable-line complexity
			let $checkedInput, id, $label, $price;

			switch ( fieldType ) {
				case 'payment-checkbox':
					return $input.is( ':checked' )
						? $input.closest( 'li' ).find( `label[for="${ $input.attr( 'id' ) }"]` ).text().replace( ' – ', ' - ' )
						: '';

				case 'payment-multiple':
					$checkedInput = $input.closest( 'ul' ).find( 'input:checked' );
					id = $checkedInput.attr( 'id' );
					$label = $checkedInput.closest( 'ul' ).find( `label[for="${ id }"]` );

					return $checkedInput.length && $label.length ? $label.text().replace( ' – ', ' - ' ) : '';

				case 'payment-select':
					return $input.find( ':selected:not(:disabled)' ).text().replace( ' – ', ' - ' );

				case 'payment-single':
					$price = $input.closest( '.wpforms-field' ).find( '.wpforms-price' );

					return $input.is( ':hidden' ) && $price.length ? $price.text() : $input.val();

				default:
					return $input.val();
			}
		},

		/**
		 * Input event handler.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event object.
		 */
		inputEvent( event ) { // eslint-disable-line complexity
			const $input = $( this ),
				$form = $input.closest( '.wpforms-form' ),
				formId = $form.data( 'formid' );

			if ( ! formId || typeof wpforms_calculations.code[ formId ] === 'undefined' ) {
				return;
			}

			// Get the ID of the field where `input` event was fired.
			const eventFieldId = $( event.target ).closest( '.wpforms-field' ).data( 'field-id' );

			vars.shouldProcessConditionals = false;
			vars.fieldsDisabledCalc[ formId ] = vars.fieldsDisabledCalc[ formId ] || [];

			app.calculateAllFields( formId, $form, event, eventFieldId );

			// If calculation was not performed.
			if ( ! Object.keys( vars.fieldsResults ).length ) {
				return;
			}

			// Process Conditional Logic.
			if ( window.wpformsconditionals && vars.shouldProcessConditionals ) {
				window.wpformsconditionals.processConditionals( $input, true );
			}

			// Update the Total field amount.
			wpforms.amountTotal( $form.find( '.wpforms-payment-total' ), true );

			app.calculateAllFields( formId, $form, event, eventFieldId );
		},

		/**
		 * Calculate all the fields in the form.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId       Form ID.
		 * @param {jQuery} $form        Form object.
		 * @param {Object} event        Event object.
		 * @param {number} eventFieldId Field ID in which input event was fired.
		 */
		calculateAllFields( formId, $form, event, eventFieldId ) {
			// Update fields values registry.
			formFieldsRegistry[ formId ] = app.getSingleFormFieldsValues( $form );

			vars.fieldsResults = {};

			// Execute all the calculation functions for the current form.
			for ( const fieldId in wpforms_calculations.code[ formId ] ) {
				// Do not calculate disabled fields.
				if ( vars.fieldsDisabledCalc[ formId ].includes( Number( fieldId ) ) ) {
					continue;
				}

				// Do not re-calculate the field if it was already calculated.
				if ( typeof vars.fieldsResults[ fieldId ] !== 'undefined' ) {
					continue;
				}

				// Reset calculation stack.
				vars.fieldsCalcStack = [];

				const $field = $( `#wpforms-${ formId }-field_${ fieldId }-container` ),
					result = app.getCalcResult( formId, Number( fieldId ), event, eventFieldId );

				// Update field value.
				app.updateFieldValue( result, $field, fieldId, formId );

				// Detect conditional trigger field.
				vars.shouldProcessConditionals = vars.shouldProcessConditionals || $field.hasClass( 'wpforms-conditional-trigger' );
			}
		},

		/**
		 * Get field calculation result.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId       Form ID.
		 * @param {number} fieldId      Field ID.
		 * @param {Object} event        Event object.
		 * @param {number} eventFieldId Field ID in which input event was fired.
		 *
		 * @return {string|number|Object|Array} Calculation result.
		 */
		getCalcResult( formId, fieldId, event, eventFieldId ) { // eslint-disable-line complexity
			// Bail if the calculation function is not defined.
			if ( ! formulasRegistry[ formId ] || ! formulasRegistry[ formId ][ fieldId ] ) {
				return '';
			}

			// Push the field ID to the stack to prevent infinite loop in recursive calls.
			vars.fieldsCalcStack.push( fieldId );

			const fields = formFieldsRegistry[ formId ].fields;

			// Pre-calculation of the fields which is used in this formula (dependence).
			app.preCalcFields( formId, fieldId, event, eventFieldId );

			// Do not re-calculate the field if it is already calculated during pre-calculation.
			if ( typeof vars.fieldsResults[ fieldId ] !== 'undefined' ) {
				return vars.fieldsResults[ fieldId ];
			}

			vars.fieldFormulaArgs = vars.fieldFormulaArgs || { formId, fieldId, fields, eventFieldId, allowedFunctions, app };

			const fieldValBefore = app.isObject( fields[ fieldId ] ) ? { ...fields[ fieldId ] } : fields[ fieldId ];

			// Call the calculation function.
			let result = formulasRegistry[ formId ][ fieldId ]( formId, fieldId, fields, eventFieldId, allowedFunctions, app );

			// Apply field-specific normalization.
			result = app.normalizeCalcResult( result, formId, fieldId );

			// Update fields calculations result storage.
			vars.fieldsResults[ fieldId ] = result;

			app.debug( `Field #${ fieldId } calculated:`, {
				formId,
				fieldId,
				code: formulasRegistry[ formId ][ fieldId ].toString(),
				'value before calc': fieldValBefore,
				'value after calc': result,
				eventFieldId,
				event,
				fields,
			}, { type: 'debug' } );

			return result;
		},

		/**
		 * Pre-calculation of the fields which are used in the formula (dependence).
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId       Form ID.
		 * @param {number} fieldId      Field ID.
		 * @param {Object} event        Event object.
		 * @param {number} eventFieldId Field ID in which input event was fired.
		 */
		preCalcFields( formId, fieldId, event, eventFieldId ) { // eslint-disable-line complexity
			const code = wpforms_calculations.code[ formId ][ fieldId ];

			if (
				! code || // Bail if the calculation function is not defined.
				typeof vars.fieldsResults[ fieldId ] !== 'undefined' // Do not re-calculate the field if it was already calculated.
			) {
				return;
			}

			// Get the list of fields used in the formula.
			const fields = code.matchAll( /\$F\d*/mg );

			// Loop through all the fields used in the formula.
			for ( const field of fields ) {
				// Get the ID of the field from $FX field variable.
				const fieldId$F = Number( field[ 0 ].replace( '$F', '' ) );

				if ( vars.fieldsDisabledCalc[ formId ].includes( fieldId$F ) ) {
					continue;
				}

				// Do not recalculate itself.
				if ( fieldId$F === Number( fieldId ) ) {
					continue;
				}

				// Do not re-calculate the field if it was already calculated.
				if ( typeof vars.fieldsResults[ fieldId$F ] !== 'undefined' ) {
					continue;
				}

				// Do not re-calculate the field if the code is empty (it means that calculation is not enabled).
				if ( ! wpforms_calculations.code[ formId ][ fieldId$F ] ) {
					continue;
				}

				// Detect circular reference.
				if ( vars.fieldsCalcStack.includes( fieldId$F ) ) {
					// Empty result for this field.
					vars.fieldsResults[ fieldId$F ] = '';
					vars.fieldsCalcStack.push( fieldId$F );

					// Disable further calculations for this field.
					vars.fieldsDisabledCalc[ formId ].push( fieldId$F );

					// Allow input for this field.
					$( `#wpforms-${ formId }-field_${ fieldId$F }` ).attr( {
						readonly: false,
						title: false,
					} );

					app.debug(
						wpforms_calculations.strings.errorCircularReference.replace( '%1$s', fieldId$F ),
						{ type: 'error', formId, fieldId }
					);

					continue;
				}

				const $calcField = $( `#wpforms-${ formId }-field_${ fieldId$F }-container` ),
					result = app.getCalcResult( formId, fieldId$F, event, eventFieldId );

				// Update field value.
				app.updateFieldValue( result, $calcField, fieldId$F, formId );
			}
		},

		/**
		 * Field-specific calculation result normalization.
		 *
		 * @since 1.1.0
		 *
		 * @param {string|number} result  Calculation result.
		 * @param {number}        formId  Form ID.
		 * @param {number}        fieldId Field ID.
		 *
		 * @return {string|number} Normalized calculation result.
		 */
		normalizeCalcResult( result, formId, fieldId ) {
			const field = app.getFormFieldData( formId, fieldId );

			if ( ! field?.type ) {
				return result;
			}

			// For the Payment Single Item field.
			if ( field.type === 'payment-single' ) {
				const currency = wpforms.getCurrency();

				// Round the result to the number of decimals specified in the currency settings.
				result = app.innerFunctions.round( app.parseFloat( result ), currency.decimals );

				// Do not allow negative values for Payment Single Item field.
				return result < 0 ? 0 : wpforms.amountFormatSymbol( result );
			}

			// For the Number field.
			if ( field.type === 'number' ) {
				return app.parseFloat( result );
			}

			return result;
		},

		/**
		 * Update field value.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} value   New field value.
		 * @param {jQuery} $field  Field element.
		 * @param {number} fieldId Field ID.
		 * @param {number} formId  Form ID.
		 */
		updateFieldValue( value, $field, fieldId, formId ) {
			if ( app.isHiddenByCL( $field ) ) {
				return;
			}

			const fieldType = $field.data( 'field-type' );

			// Update Payment Single Item field value.
			if ( fieldType === 'payment-single' ) {
				app.updatePaymentSingleFieldValue( value, $field, fieldId, formId );

				return;
			}

			let displayValue = value,
				fieldValue = value;

			if ( fieldType === 'number' ) {
				fieldValue = displayValue = app.parseFloat( value );
			}

			// We should display only numeric or string value.
			if ( ! ( app.isNumeric( displayValue ) || app.isString( displayValue ) ) ) {
				displayValue = '';
			}

			// Update regular input value;
			$field.find( ':input' )
				.val( displayValue )
				.attr( 'title', wpforms_calculations.strings.readonlyInputTitle );

			// Update fields values registry after each calculation.
			formFieldsRegistry[ formId ].fields[ fieldId ] = fieldValue;
		},

		/**
		 * Update Payment Single Item field value.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} value   New field value.
		 * @param {jQuery} $field  Field element.
		 * @param {number} fieldId Field ID.
		 * @param {number} formId  Form ID.
		 */
		updatePaymentSingleFieldValue( value, $field, fieldId, formId ) {
			const amount = app.amountSanitize( value ),
				valueFormattedSymbol = wpforms.amountFormatSymbol( amount ),
				$input = $field.find( 'input' );

			// Update the price container text.
			$field.find( '.wpforms-single-item-price .wpforms-price' ).text( valueFormattedSymbol );

			// Update input text and input hidden value, but not the quantities select dropdown.
			$input
				.val( valueFormattedSymbol )
				.attr( 'title', wpforms_calculations.strings.readonlyInputTitle );

			// Update fields values registry after each calculation.
			formFieldsRegistry[ formId ].fields[ fieldId ].value = valueFormattedSymbol;
			formFieldsRegistry[ formId ].fields[ fieldId ].amount = amount * app.getFieldQuantity( $input );
		},

		/**
		 * Get form fields settings data.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId Form ID.
		 *
		 * @return {object|null} Form fields values.
		 */
		getFormFieldsData( formId ) {
			return wpforms_calculations.formFields[ formId ];
		},

		/**
		 * Get form field settings data.
		 *
		 * @since 1.3.0
		 *
		 * @param {number}        formId  Form ID.
		 * @param {number|string} fieldId Field ID.
		 *
		 * @return {object|null} Form fields values.
		 */
		getFormFieldData( formId, fieldId ) {
			if ( ! wpforms_calculations.formFields[ formId ] ) {
				return null;
			}

			const originalFieldId = Number( fieldId.toString().replace( /_\d+$/, '' ) ); // Remove the clone field suffix from the field ID.

			return wpforms_calculations.formFields[ formId ][ originalFieldId ] || null;
		},

		/**
		 * Get form fields values stored in the field values registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId Form ID.
		 *
		 * @return {object|null} Form fields values.
		 */
		getFormFieldsValuesFromRegistry( formId ) {
			if ( ! ( formFieldsRegistry[ formId ] && formFieldsRegistry[ formId ].fields ) ) {
				return null;
			}

			return formFieldsRegistry[ formId ].fields;
		},

		/**
		 * Get field value stored in the field values registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} formId  Form ID.
		 * @param {number} fieldId Field ID.
		 *
		 * @return {string|number|object|null} Field value.
		 */
		getFieldValueFromRegistry( formId, fieldId ) {
			if ( ! ( formFieldsRegistry[ formId ] && formFieldsRegistry[ formId ].fields ) ) {
				return null;
			}

			return formFieldsRegistry[ formId ].fields[ fieldId ];
		},

		/**
		 * Get the current formula arguments.
		 *
		 * @since 1.0.0
		 *
		 * @return {Object} Calculation arguments.
		 */
		getFieldFormulaArgs() {
			return vars.fieldFormulaArgs;
		},

		/**
		 * Check if the value is empty.
		 *
		 * @since 1.1.0
		 *
		 * @param {string|number|object|boolean|null} val Value to check.
		 *
		 * @return {boolean} True when the value is empty.
		 */
		isEmpty( val ) {
			if ( typeof val === 'object' ) {
				return Object.keys( val ).length === 0;
			}

			return [ undefined, null, false, 0, '', '0' ].includes( val );
		},

		/**
		 * Check if argument is object.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} obj Object to check.
		 *
		 * @return {boolean} True when argument is object.
		 */
		isObject( obj ) {
			return typeof obj === 'object' && ! Array.isArray( obj ) && obj !== null;
		},

		/**
		 * Check if argument is numeric (reflect `is_numeric` PHP function).
		 *
		 * @since 1.0.0
		 *
		 * @param {number|string} num Value to check.
		 *
		 * @return {boolean} True when argument is a number or a numeric string.
		 */
		isNumeric( num ) {
			return ! isNaN( parseFloat( num ) ) && isFinite( num );
		},

		/**
		 * Check if argument is string.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} str Value to check.
		 *
		 * @return {boolean} True when argument is a string.
		 */
		isString( str ) {
			return typeof str === 'string' || str instanceof String;
		},

		/**
		 * Check if the field is hidden by Conditional Logic.
		 *
		 * @since 1.4.0
		 *
		 * @param {jQuery} $field Field object.
		 *
		 * @return {boolean} True when the field is hidden.
		 */
		isHiddenByCL( $field ) {
			return $field?.hasClass( 'wpforms-conditional-hide' );
		},

		/* eslint-disable jsdoc/match-description */
		/**
		 * Convert the given string to a number.
		 *
		 * It finds the first number OR formatted money amount with the current currency symbol
		 * in the given string and converts it to the decimal number value.
		 *
		 * Examples:
		 * The current currency is USD:
		 * - "123,123.45"                   -> 123123.45
		 * - "-10 is a -1 * 10"             -> -10
		 * - "price is $1,000.00"           -> 1000
		 * - "Your balance is -$2,555.99"   -> -2555.99
		 *
		 * The current currency is EUR:
		 * - "Ticket to Mars 1.000.000,99€" -> 1000000.99
		 * - "1000,00€ and $50.00"          -> 1000
		 * - "Negative amount -100€"        -> -100
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} str       String to convert.
		 * @param {number}        precision Round number to $precision decimal digits. Default is 12. Optional.
		 *
		 * @return {number|string} The converted number OR empty string.
		 */
		/* eslint-enable jsdoc/match-description */
		parseFloat( str, precision = 12 ) { // eslint-disable-line complexity
			if ( ! [ 0, '0' ].includes( str ) && app.isEmpty( str ) ) {
				return '';
			}

			precision = precision || precision === 0 ? precision : 12;

			// If the given value is already a numeric, just round it.
			if ( app.isNumeric( str ) ) {
				return Number( parseFloat( str ).toFixed( precision ) );
			}

			str = str.toString();

			const currency = wpforms.getCurrency();
			const symbol = currency.symbol.replace( '$', '\\$' );
			const leftSymbol = currency.symbol_pos === 'left' ? symbol + '[ ]?' : '';
			const rightSymbol = currency.symbol_pos === 'right' ? '[ ]?' + symbol : '';

			// Prepare regex pattern.
			const amountPattern =
				// Match amount with currency symbol, decimal and thousands separator.
				`(-?${ leftSymbol }(\\d+)([${ currency.thousands_sep }]?\\d{3})*([${ currency.decimal_sep }]\\d*)?(${ rightSymbol }))` +
				// OR match regular number values with a standard decimal and thousands separator.
				`|(-?(\\d+)([,]?\\d{3})*([.]?\\d*)?)`;

			const matches = str.match( new RegExp( amountPattern, 'g' ) );

			// Detect first not empty match.
			const found = matches?.find( ( item ) => item !== '' );

			// If the number OR amount is not found, return empty string.
			if ( ! found ) {
				return '';
			}

			// If the amount is found, return converted amount.
			if ( found.includes( symbol ) ) {
				return app.amountSanitize( found );
			}

			// Remove all non-numeric characters except decimal point and minus sign.
			const num = found.replaceAll( /[^0-9.-]/g, '' );

			return Number( parseFloat( num ).toFixed( precision ) );
		},

		/**
		 * Convert all object properties to strings.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} obj Object to convert.
		 *
		 * @return {Object} Updated object.
		 */
		toStrings( obj ) {
			if ( ! app.isObject( obj ) ) {
				return obj;
			}

			const result = {};

			for ( const key in obj ) {
				if ( ! Object.prototype.hasOwnProperty.call( obj, key ) ) {
					continue;
				}

				result[ key ] = obj[ key ] ? obj[ key ].toString() : '';
			}

			return result;
		},

		/**
		 * Check if the field is allowed in calculations.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} fieldType Field type.
		 *
		 * @return {boolean} True when field is allowed.
		 */
		isAllowedField( fieldType ) {
			return Object.keys( wpforms_calculations.allowedFields ).includes( fieldType );
		},

		/**
		 * Get sanitized and converted to number amount value.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} amount Amount value.
		 *
		 * @return {number} Amount as a number.
		 */
		amountSanitize( amount ) {
			return Number( wpforms.amountSanitize( amount || '0' ) );
		},

		/**
		 * Debug output helper.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number|Object|Array} msg Debug message (any data).
		 */
		debug( ...msg ) { // eslint-disable-line complexity
			const options = arguments.length > 0 ? arguments[ arguments.length - 1 ] : {},
				type = options.type || 'log';

			if ( options.type ) {
				msg.pop();
			}

			if (
				! ( wpforms_calculations.calcDebug && type === 'debug' ) && // The `debug` messages are allowed only in WPFORMS_CALCULATIONS_DEBUG mode.
				! [ 'log', 'error' ].includes( type )
			) {
				return;
			}

			const e = new Error(),
				stack = e.stack.toString().split( /\r\n|\n/ ),
				chunks = stack[ 2 ].split( '/' ),
				method = chunks[ 0 ].replace( /\s\(http.*$/, '' ),
				file = chunks[ chunks.length - 1 ].replace( ')', '' ),
				color = type === 'error' ? '#aa0000' : '#cd6622',
				prefix = type === 'error' ? wpforms_calculations.strings.errorPrefix : wpforms_calculations.strings.debugPrefix;

			// In the case of error we need to add error field to the beginning of the array.
			if ( type === 'error' && options.formId && options.fieldId ) {
				msg.unshift( wpforms_calculations.strings.errorFormFieldPrefix.replace( '%1$s', options.formId ).replace( '%2$s', options.fieldId ) );
			}

			/* eslint-disable no-console */
			console.group( '%c' + prefix, 'color: ' + color, method + ' (' + file + ')' );
			console.log( ...msg );
			console.groupEnd();
			/* eslint-enable no-console */
		},

		/**
		 * `wpformsFormAbandonmentGetFormDataBefore` event handler.
		 * Fix empty calculated fields in partial (abandoned) entries.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event  Event object.
		 * @param {number} formId Form ID.
		 */
		formAbandonmentGetFormDataBefore( event, formId ) {
			if ( ! WPFormsFormAbandonment ) {
				return;
			}

			const input = $( `#wpforms-form-${ formId } :input:first` ).get( 0 );

			event.target = input;

			// Calculate all fields.
			app.inputEvent.call( input, event );

			// Prepare partial entry data
			WPFormsFormAbandonment.prepData( event );
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsCalculations.init();
