/* global wpforms_calculations, wpf, WPForms, wpforms_builder, CodeMirror, DropdownList, WPFormsAIModal, wpFormsAIDock */

/**
 * @name CodeMirror
 * @class
 * @property {Function} focus           Set focus.
 * @property {Function} getCursor       Get cursor.
 * @property {Function} getDoc          Get doc.
 * @property {Function} getLine         Get line.
 * @property {any}      display         Display.
 * @property {any}      display.wrapper Display wrapper.
 */

/**
 * @param wpforms_builder.ajax_url
 * @param wpforms_calculations.strings.ajaxFail
 * @param wpforms_calculations.strings.allowedFieldsTypes
 * @param wpforms_calculations.strings.calculation_code
 * @param wpforms_calculations.strings.display
 * @param wpforms_calculations.functionsList
 * @param wpforms_calculations.isLicenseActive
 * @param wpforms_calculations.isAiDisabled
 * @param wpforms_calculations.calculationIsPossibleFields
 * @param wpforms_calculations.strings.insertFieldDropdownTitle
 * @param wpforms_calculations.strings.validateButtonAjaxError
 * @param wpforms_calculations.strings.validateButtonErrors
 * @param wpforms_calculations.strings.validateButtonSuccess
 * @param wpforms_calculations.strings.validationFieldDoesntExist
 * @param wpforms_calculations.strings.validationFieldNotAllowed
 * @param wpforms_calculations.strings.validationSubfieldNotAllowed
 * @param wpforms_calculations.strings.validationModalErrorMsg
 * @param wpforms_calculations.strings.validationModalErrorTitle
 * @param wpforms_calculations.strings.validationVariableNotAllowed
 * @param wpforms_calculations.strings.thisFieldUsedInField
 * @param wpforms_calculations.strings.thisFieldUsedInFields
 * @param wpforms_calculations.strings.validationFieldTotalInSingleItem
 * @param wpforms_calculations.strings.fixWithAiInitialPrompt
 * @param wpforms_calculations.strings.aiCalculations
 */

/**
 * WPForms Calculations in the Form Builder.
 *
 * @since 1.0.0
 */
const WPFormsCalculationsBuilder = window.WPFormsCalculationsBuilder || ( function( document, window, $ ) {
	/**
	 * Elements holder.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const el = {
		$document: $( document ),
		$builder: $( '#wpforms-builder' ),
	};

	/**
	 * Private runtime variables.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const vars = {
		codemirror: {},
	};

	/**
	 * Fields functions.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	// eslint-disable-next-line prefer-const
	let fields;

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * The way the autocomplete was triggered.
		 *
		 * Can be 'keyup' or 'mousedown'.
		 *
		 * @since 1.6.0
		 *
		 * @type {string}
		 */
		autocompleteTriggeredBy: 'keyup',

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			if ( navigator.userAgent.toLowerCase().includes( 'mac' ) ) {
				document.body.classList.add( 'is-mac' );
			}

			// Default CodeMirror options.
			vars.codemirror.options = {
				theme: 'mdn-like',
				mode: 'text/x-php',
				startOpen: false,
				scrollbarStyle: 'native',
				lineNumbers: true,
				readOnly: false,
				autoCloseBrackets: true,
				matchBrackets: true,
				indentUnit: 4,
				indentWithTabs: true,
				enterMode: 'keep',
				tabMode: 'shift',
				hintOptions: {
					hint: app.getAutocompleteData,
					completeSingle: false,
				},
			};

			vars.codemirror.expandedEditorWidth = 720;

			// Codemirror editors.
			vars.codemirror.editors = {};

			el.$builder.on( 'wpformsBuilderReady', app.ready );
		},

		/**
		 * Ready.
		 *
		 * @since 1.0.0
		 */
		ready() {
			fields.init();
			app.initFieldsOptions();

			app.events();
		},

		/**
		 * Events.
		 *
		 * @since 1.0.0
		 */
		events() {
			// Update field variables registry.
			el.$builder
				.on( 'wpformsFieldAdd', fields.add )
				.on( 'wpformsFieldDelete', fields.delete )
				.on( 'wpformsFieldMove', fields.move )
				.on( 'wpformsFieldDuplicated', fields.duplicated )
				.on( 'click', '.wpforms-field-option-row-choices .add', fields.updateChoicesField )
				.on( 'click', '.wpforms-field-option-row-choices .remove', fields.updateChoicesField );

			// General events.
			el.$builder
				.on( 'click', '.wpforms-field-option-group .wpforms-field-option-group-toggle', app.clickOptionGroup )
				.on( 'change', '.wpforms-field-option-row-calculation_is_enabled input', app.toggleCodeEditor )
				.on( 'click', '.wpforms-calculations-expand-editor', app.expandCollapseEditor )
				.on( 'click', '.wpforms-calculations-validate-formula', app.validateFormula )
				.on( 'focus', '.wpforms-calculations-editor-wrap .toolbar button', app.toolbarButtonFocus )
				.on( 'click', '.wpforms-calculations-editor-wrap .toolbar button', app.toolbarButtonClick )
				.on( 'keyup', '.wpforms-calculations-editor-wrap .toolbar button', app.toolbarButtonEnterKey )
				.on( 'change', '.wpforms-field-option :input', app.fieldOptionChange )
				.on( 'mouseover', '.cm-variable-2', app.variableHover )
				.on( 'wpformsBeforeFieldDeleteAlert', app.fieldDeleteConfirmAlert )
				.on( 'wpformsCalcFieldChange wpformsFieldAdd wpformsFieldDelete wpformsFieldDuplicated', app.updateFieldsEvent )
				.on( 'wpformsFieldDuplicated', app.fieldDuplicated )
				.on( 'wpformsFieldOptionTabToggle', app.fieldTabToggle )
				.on( 'click', '.wpforms-ai-calculations-button:not(.education-modal)', app.clickGenerateFormulaButton );

			el.$document
				.on( 'wpformsFieldUpdate', app.updateFieldsEvent );
		},

		/**
		 * Init fields options.
		 *
		 * @since 1.0.0
		 */
		initFieldsOptions() {
			const formFields = wpf.getFields( fields.calculationIsPossibleTypes, true );

			for ( const fieldKey in formFields ) {
				const field = formFields[ fieldKey ],
					$fieldOptions = $( '#wpforms-field-option-' + field.id ),
					isCalculationEnabled = field.calculation_is_enabled;

				// Enable/disable default value field option.
				app.toggleFieldDisabledOptions( $fieldOptions, isCalculationEnabled );
			}
		},

		/**
		 * Click on the field option group tab event handler.
		 *
		 * @since 1.0.0
		 */
		clickOptionGroup() {
			const $group = $( this ).closest( '.wpforms-field-option-group' );

			if ( $group.hasClass( 'wpforms-field-option-group-advanced' ) ) {
				app.setupAllEditors( $group );
			}
		},

		/**
		 * Update field options on select the field in the preview area.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object}        event   Event object.
		 * @param {number|string} fieldId Field ID.
		 */
		fieldTabToggle( event, fieldId ) { // eslint-disable-line complexity
			if ( ( ! fieldId && Number( fieldId ) !== 0 ) || [ 'add-fields', 'field-options' ].includes( fieldId ) ) {
				return;
			}

			const $fieldOptions = $( '#wpforms-field-option-' + fieldId ),
				fieldSettings = wpf.getField( fieldId ),
				isCalculationEnabled = fieldSettings && fieldSettings.calculation_is_enabled && fieldSettings.calculation_code?.length;

			if ( ! $fieldOptions.length || ! isCalculationEnabled ) {
				return;
			}

			// Maybe initialize Codemirror editor.
			// It will be skipped if the editor is already initialized.
			app.setupAllEditors( $fieldOptions );

			// Collapse the formula editor (if expanded).
			app.collapseEditor( $fieldOptions.find( '.wpforms-calculations-expand-editor' ) );

			// Enable/disable field options.
			app.toggleFieldDisabledOptions( $fieldOptions, isCalculationEnabled );
		},

		/**
		 * Show the AI modal.
		 *
		 * @see wp-content/plugins/wpforms/assets/js/integrations/ai/wpforms-ai-modal.js
		 *
		 * @since 1.6.0
		 */
		clickGenerateFormulaButton() {
			const args = app.prepareModalArgs( this );

			if ( ! args ) {
				return;
			}

			WPFormsAIModal.initModal( args );
		},

		/**
		 * Click on the fix with AI button.
		 *
		 * @since 1.6.0
		 *
		 * @param {Array}  errorMessages Error messages
		 * @param {number} fieldId       Field ID
		 */
		clickFixWithAiButton( errorMessages, fieldId ) {
			const args = app.prepareModalArgs( this, errorMessages, fieldId );

			if ( ! args ) {
				return;
			}

			WPFormsAIModal.initModal( args );
		},

		/**
		 * Prepare modal arguments.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} button        Button element.
		 * @param {Array}  errorMessages Formula validation error messages.
		 * @param {number} fieldId       Field ID.
		 *
		 * @return {Object|null} Modal arguments, empty if modal is disabled or not present.
		 */
		prepareModalArgs( button, errorMessages = [], fieldId = null ) {
			fieldId = fieldId ?? $( button ).data( 'field-id' );

			// Close any other modals.
			$( `.jconfirm-wpforms-ai-modal:not(.jconfirm-wpforms-ai-modal-calculations-${ fieldId })` )
				.addClass( 'wpforms-hidden' )
				.fadeOut();

			if ( app.maybeReopenModal( fieldId, errorMessages ) ) {
				return null;
			}

			const args = {},
				hideCalculations = function() {
					$( `.jconfirm-wpforms-ai-modal-calculations-${ fieldId }` ).addClass( 'wpforms-hidden' ).fadeOut();

					return false;
				};

			if ( errorMessages.length > 0 ) {
				const initialPrompt = app.getInitialPrompt( errorMessages );

				args.content = `<wpforms-ai-chat mode="calculations" field-id="${ fieldId }" prefill="${ initialPrompt }" auto-submit="true" />`;
			} else {
				args.content = `<wpforms-ai-chat mode="calculations" field-id="${ fieldId }" />`;
			}

			args.theme = `wpforms-ai-modal, wpforms-ai-purple, wpforms-ai-modal-calculations-${ fieldId }`;
			args.backgroundDismiss = hideCalculations;
			args.backgroundDismissAnimation = '';
			args.contentMaxHeight = Math.min( WPFormsAIModal.defaultOptions.contentMaxHeight, WPFormsAIModal.getMaxModalHeight() );
			args.onOpen = function() {
				setTimeout( () => {
					const $modal = $( `.jconfirm-wpforms-ai-modal-calculations-${ fieldId }` );
					const $closeIcon = $modal.find( '.jconfirm-closeIcon' );

					$closeIcon.off( 'click' );
					$closeIcon.on( 'click', hideCalculations );

					WPFormsAIModal.resizeModalHeight( fieldId );
				}, 0 );
			};
			args.onOpenBefore = function() {
				wpFormsAIDock.init( fieldId );
			};

			return args;
		},

		/**
		 * Get initial prompt for AI modal with error messages.
		 *
		 * @since 1.6.0
		 *
		 * @param {Array} errorMessages Error messages.
		 *
		 * @return {string} Initial prompt.
		 */
		getInitialPrompt( errorMessages ) {
			const errorMessagesStr = errorMessages
				.map( ( error ) => {
					const filteredError = wpf.sanitizeHTML( error );

					return `Error message: ${ filteredError }`;
				} )
				.join( '<br>' );

			return `<div>${ wpforms_calculations.strings.fixWithAiInitialPrompt }<p>${ errorMessagesStr }</p></div>`;
		},

		/**
		 * Maybe reopen modal.
		 *
		 * @since 1.6.0
		 *
		 * @param {number} fieldId       Field ID.
		 * @param {Array}  errorMessages Formula validation error messages.
		 *
		 * @return {boolean} True if modal was reopened, false otherwise.
		 */
		maybeReopenModal( fieldId, errorMessages ) {
			const $modal = $( `.jconfirm-wpforms-ai-modal-calculations-${ fieldId }` );

			if ( ! $modal.length ) {
				return false;
			}

			setTimeout( () => {
				$modal.filter( ':last' ).removeClass( 'wpforms-hidden' ).fadeIn();

				if ( errorMessages.length ) {
					const $textarea = $modal.find( '.wpforms-ai-chat-message-input textarea' ),
						$submitButton = $modal.find( '.wpforms-ai-chat-message-input button.wpforms-ai-chat-send' ),
						initialPrompt = app.getInitialPrompt( errorMessages );

					$textarea.val( initialPrompt );
					$submitButton.click();
				}
			}, 100 );

			return true;
		},

		/**
		 * Update field options on select the field.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event     Event object.
		 * @param {Object} fieldData Field Data object.
		 */
		fieldDeleteConfirmAlert( event, fieldData ) {
			const usedInFields = fields.getUsedInFields( fieldData.id );

			if ( ! usedInFields ) {
				return;
			}

			const usedInFieldsIds = Object.keys( usedInFields ),
				usedInFieldsStrings = [];

			let alert;

			for ( const id of usedInFieldsIds ) {
				usedInFieldsStrings.push( `${ usedInFields[ id ] } (${ wpforms_calculations.strings.field } #${ id })` );
			}

			if ( usedInFieldsIds.length > 1 ) {
				alert = wpforms_calculations.strings.thisFieldUsedInFields + '<br><br>' +
					wpforms_calculations.strings.fields + ' #' + usedInFieldsStrings.join( '<br>' );
			} else {
				alert = wpforms_calculations.strings.thisFieldUsedInField + '<br><br>' + usedInFieldsStrings[ 0 ];
			}

			fieldData.trigger = true;
			fieldData.message = fieldData.message + '<br><br>' + alert + '<br>';
		},

		/**
		 * Update field options on select the field.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event object.
		 */
		fieldOptionChange( event ) { // eslint-disable-line complexity
			const $input = $( this ),
				$fieldOptions = $input.closest( '.wpforms-field-option' ),
				fieldType = $fieldOptions.find( '.wpforms-field-option-hidden-type' ).val();

			// Get fields with subfields and not `payment-*` fields.
			const fieldsWithSubfields = Object.keys( wpforms_calculations.allowedFields ).filter( function( type ) {
				return wpforms_calculations.allowedFields[ type ].length > 0 && ! type.includes( 'payment' );
			} );

			if ( ! fieldsWithSubfields.includes( fieldType ) ) {
				return;
			}

			fields.add( event, $fieldOptions.data( 'field-id' ), fieldType );
			app.updateInsertFieldDropdowns();
		},

		/**
		 * Mouseover event handler for field variables.
		 *
		 * @since 1.0.0
		 */
		variableHover() { // eslint-disable-line complexity
			const $var = $( this ),
				varName = $var.text(),

				// Get the field ID from the variable name.
				match = varName.match( /^\$F\d*(_[A-Za-z0-9]+)*/mg );

			if ( ! match || ! match[ 0 ] ) {
				return;
			}

			const parts = match[ 0 ].split( '_' ),
				fieldId = parts[ 0 ].replace( '$F', '' );

			if ( ! fieldId ) {
				return;
			}

			let field;

			try {
				field = wpf.getField( fieldId );
			} catch ( e ) {
				return;
			}

			if ( ! field ) {
				return;
			}

			const subField = parts[ 1 ] ? parts[ 1 ] : null;
			let title = field.label;

			if ( subField ) {
				title += ` (${ subField })`;
			}

			// Add tooltip with field label.
			$var.attr( 'title', title );
		},

		/**
		 * Toggle calculation code editor.
		 *
		 * @since 1.0.0
		 */
		toggleCodeEditor() {
			const $this = $( this );

			if ( $this.prop( 'disabled' ) ) {
				return;
			}

			const enable = $this.prop( 'checked' ),
				$optionGroup = $this.closest( '.wpforms-field-option-group-inner' ),
				$fieldOptions = $optionGroup.closest( '.wpforms-field-option' ),
				$EditorRow = $optionGroup.find( '.wpforms-field-option-row-calculation_code' );

			app.toggleFieldDisabledOptions( $fieldOptions, enable );

			if ( ! enable ) {
				$EditorRow.slideUp();

				return;
			}

			// Open the formula code editor.
			const tabContent = $EditorRow.closest( '.wpforms-field-options.wpforms-tab-content' )[ 0 ],
				$group = $EditorRow.closest( '.wpforms-field-option-group-inner' );

			// Scroll to the bottom of the field options tab container.
			$group.css( 'padding-bottom', '250px' );
			tabContent.scrollBy( { top: 250 } );

			$EditorRow.slideDown( 200, function() {
				$group.css( 'padding-bottom', '' );
			} );

			// Initialize the editor.
			app.setupSingleEditor( $EditorRow.find( 'textarea.wpforms-codemirror-editor' ) );
		},

		/**
		 * Enable or disable specific field options.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery}  $fieldOptions Field options container.
		 * @param {boolean} disable       Whether to disable (true) or enable (false) the options.
		 */
		toggleFieldDisabledOptions( $fieldOptions, disable ) {
			const $minMax = $fieldOptions.find( '.wpforms-field-option-row-min_max input' ),
				fieldId = $fieldOptions.data( 'field-id' ),
				fieldType = $fieldOptions.find( '.wpforms-field-option-hidden-type' ).val(),
				$fieldPreview = el.$builder.find( `#wpforms-field-${ fieldId } .primary-input` ),
				$paymentItemPreview = el.$builder.find( `#wpforms-field-${ fieldId } .price` ),
				$paymentItemPrice = $fieldOptions.find( '.wpforms-field-option-row-price input' );

			let $defaultValue = $fieldOptions.find( '.wpforms-field-option-row-default_value input' );

			// Single Item has default values saved different place.
			if ( fieldType === 'payment-single' ) {
				$defaultValue = $fieldOptions.find( '.wpforms-field-option-row-price input' );
			}

			$defaultValue.prop( 'readonly', disable );
			$minMax.prop( 'readonly', disable );
			$paymentItemPrice.prop( 'readonly', disable );

			$fieldPreview.val( disable ? '' : $defaultValue.val() );
			$paymentItemPreview.html( wpf.amountFormatCurrency( disable ? '' : $paymentItemPrice.val() ) );
		},

		/**
		 * Setup and configure all the Codemirror instances.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $scope The scope to search for the Codemirror editor.
		 */
		setupAllEditors( $scope ) {
			$scope = $scope || el.$builder;

			// Initialize all the Codemirror editors.
			$( 'textarea.wpforms-codemirror-editor', $scope ).each( function() {
				app.setupSingleEditor( $( this ) );
			} );
		},

		/**
		 * Setup and configure single Codemirror instance.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery}  $textarea Textarea element.
		 * @param {boolean} force     Force re-init Codemirror editor with the same ID.
		 */
		setupSingleEditor( $textarea, force = false ) {
			if ( ! wp.codeEditor ) {
				return;
			}

			$textarea = $textarea || $( this );

			if ( ! $textarea.is( ':visible' ) ) {
				return;
			}

			const id = $textarea.attr( 'id' );

			if ( vars.codemirror.editors[ id ] && ! force ) {
				return;
			}

			const options = $.extend( true, {}, vars.codemirror.options );

			// Initialize the editor.
			const editor = wp.codeEditor.initialize( $textarea, { codemirror: options } ),
				$editorWrap = $( editor.codemirror.display.wrapper ).closest( '.wpforms-calculations-editor-wrap' ),
				$validateButton = $editorWrap.find( '.wpforms-calculations-validate-formula' );

			// Initialize the Insert Field button dropdown list.
			app.getDropdownListInstance( $editorWrap.find( '.button-insert-field' ), editor.codemirror );

			// Initialize Validate Formula button.
			$validateButton.toggleClass( 'disabled', editor.codemirror.getValue().trim() === '' );

			// Store the editor instance.
			vars.codemirror.editors[ id ] = editor;

			app.singleEditorEvents( editor.codemirror, $editorWrap );
		},

		/**
		 * Single Codemirror editor events.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} codemirror  Codemirror editor instance.
		 * @param {jQuery} $editorWrap Editor wrapper object.
		 */
		singleEditorEvents( codemirror, $editorWrap ) { // eslint-disable-line max-lines-per-function
			// On keydown event.
			codemirror.on( 'keydown', function( cm, e ) {
				// We only need to adjust behavior for the Enter key.
				if ( e.keyCode !== 13 ) {
					return;
				}

				const doc = cm.getDoc(),
					cursor = doc.getCursor(),
					line = doc.getLine( cursor.line ),
					// Remove all spaces from the line.
					lineTrim = line.replaceAll( /\s/g, '' ),
					// Whether the line contains if-elseif-else.
					isIfElse = /if\(.*\):|elseif\(.*\):|else:/.test( lineTrim ),
					// Get the first tabs in the line.
					matchTabs = line.match( /^\t+/ ),
					firstTabs = matchTabs ? matchTabs[ 0 ] : '';

				let addTabs = firstTabs;

				// Add tabs if the line contains if-elseif-else.
				if ( isIfElse ) {
					addTabs = firstTabs + '\t';
				}

				if ( ! addTabs.length ) {
					return;
				}

				// This should run in the next tick.
				setTimeout( function() {
					const lineNum = doc.getCursor().line,
						newLine = doc.getLine( lineNum );

					// Do not add tabs if the new line ends with a tab (auto-indentation).
					if ( newLine && newLine[ newLine.length - 1 ] === '\t' ) {
						return;
					}

					// Add tabs to the new line.
					doc.replaceRange( addTabs, { line: lineNum } );
				}, 0 );
			} );

			// Trigger autocomplete on keyup.
			codemirror.on( 'keyup', function( cm, e ) {
				// Skip autocomplete on special keys.
				const controls = [ 9, 16, 17, 18, 32, 27, 45, 46, 36, 35, 33, 34, 37, 38, 39, 40 ];

				if ( controls.includes( e.keyCode ) ) {
					return;
				}

				app.autocompleteTriggeredBy = 'keyup';

				cm.execCommand( 'autocomplete' );
			} );

			// On change event.
			codemirror.on( 'change', function( cm, e ) {
				// When the change was autocompletion.
				if ( vars.autocompleteLastCompletion ) {
					app.autocompleteInsertAfter( cm, e );
				}
				// Synchronize all the changes with the hidden textarea onchange.
				cm.save();

				const $validateButton = $editorWrap.find( '.wpforms-calculations-validate-formula' );

				// Remove error/success status of Validate Formula button.
				$validateButton.removeClass( 'error success' ).attr( 'title', '' );
				$validateButton.toggleClass( 'disabled', cm.getValue().trim() === '' );
			} );

			// Set editor focused state.
			codemirror.on( 'focus', function() {
				$editorWrap.addClass( 'focused' );
			} );

			// Remove editor focused state.
			codemirror.on( 'blur', function() {
				$editorWrap.removeClass( 'focused' );
			} );

			// On mouse click, maybe show autocomplete.
			codemirror.on( 'mousedown', function( cm, event ) {
				// Check if variable name has been clicked.
				if ( event.target.classList.contains( 'cm-variable-2' ) ) {
					setTimeout( () => {
						app.autocompleteTriggeredBy = 'mousedown';
						cm.execCommand( 'autocomplete' );
					}, 0 );
				}
			} );
		},

		/**
		 * Autocomplete data provider.
		 *
		 * @since 1.0.0
		 *
		 * @param {CodeMirror} cm CodeMirror instance.
		 *
		 * @return {Promise} Promise.
		 */
		getAutocompleteData( cm ) { // eslint-disable-line max-lines-per-function
			const fieldId = $( cm.display.wrapper ).closest( '.wpforms-field-option-row' ).data( 'field-id' ),
				words = app.getAutocompleteWords( fieldId );

			return new Promise( function( accept ) { // eslint-disable-line compat/compat, max-lines-per-function
				setTimeout( function() { // eslint-disable-line complexity, max-lines-per-function
					const cursor = cm.getCursor(),
						line = cm.getLine( cursor.line );

					let start = cursor.ch,
						end = cursor.ch;

					// Find the start and end of the word at the cursor.
					while ( start && /[\w$]/.test( line.charAt( start - 1 ) ) ) {
						--start;
					}

					while ( end < line.length && /[\w$]/.test( line.charAt( end ) ) ) {
						++end;
					}

					// Get the word.
					let word = line.slice( start, end ).toLowerCase();
					const fullWord = word;

					if ( word.length === 0 ) {
						return accept( null );
					}

					if ( app.autocompleteTriggeredBy === 'mousedown' && word.startsWith( '$f' ) ) {
						// If this is autocomplete triggered by mouse click,
						// trim word to just $f so the dropdown will contain all possible variables.
						word = '$f';
					}

					const list = [],
						listItem = {};

					let selectedHint = 0;

					for ( let i = 0; i < words.length; i++ ) {
						listItem.text = app.isObject( words[ i ] ) ? words[ i ].text : words[ i ];

						if ( ! listItem.text.toLowerCase().startsWith( word ) ) {
							continue;
						}

						if ( listItem.text.toLowerCase() === fullWord ) {
							selectedHint = list.length;
						}

						listItem.text += ' ';

						if ( listItem.text === words[ i ] ) {
							continue;
						}

						listItem.displayText = words[ i ].displayText;

						// Define render function for the list item.
						listItem.render = function( element, self, data ) {
							$( element ).append( data.displayText ? data.displayText : data.text );
						};

						list.push( $.extend( {}, listItem ) );
					}

					if ( list.length === 0 ) {
						return accept( null );
					}

					const result = {
						list,
						from: wp.CodeMirror.Pos( cursor.line, start ), // eslint-disable-line new-cap
						to: wp.CodeMirror.Pos( cursor.line, end ), // eslint-disable-line new-cap
						selectedHint,
					};

					// Add event listener for the `pick` event.
					wp.CodeMirror.on( result, 'pick', app.autocompletePick );

					// Add event listener for when hints are shown.
					wp.CodeMirror.on( result, 'shown', function() {
						const $hints = $( '.CodeMirror-hints' );
						const $selected = $hints.find( 'li.CodeMirror-hint-active' );

						if ( ! $selected.length ) {
							return;
						}

						// Get position of hints and editor.
						const hintsPosition = $hints.offset().top;
						const editorPosition = $( cm.getWrapperElement() ).offset().top;

						$hints.scrollTop( $selected.position().top );

						// Reposition hints.
						if ( hintsPosition > editorPosition ) {
							$hints.css( {
								'margin-top': '5px',
								'margin-bottom': '0',
							} );

							return;
						}

						$hints.css( {
							'margin-top': '0',
							'margin-bottom': '9px',
						} );
					} );

					return accept( result );
				}, 100 );
			} );
		},

		/**
		 * Get all words for autocomplete.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} currentFieldId Current field ID.
		 *
		 * @return {Array} Array of all words for autocomplete.
		 */
		getAutocompleteWords( currentFieldId ) {
			const staticWords = [
				{
					displayText: 'if ( ):',
					text: 'if (  ):\n\t\nelse:\n\t\nendif;',
				},
				'else:',
				'elseif ( ):',
				'endif;',
			];

			const functions = wpforms_calculations.functionsList.map( ( func ) => func + '( )' );

			return [].concat( staticWords, functions, fields.getVarsList( 'autocomplete', currentFieldId ) );
		},

		/**
		 * Autocomplete `pick` event handler.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|Object} completion Completion value.
		 */
		autocompletePick( completion ) {
			vars.autocompleteLastCompletion = completion;
		},

		/**
		 * After the autocomplete text was inserted.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} cm    Codemirror instance.
		 * @param {Object} event Codemirror change event object.
		 */
		autocompleteInsertAfter( cm, event ) {
			const completion = vars.autocompleteLastCompletion;

			// Remove the last completion.
			delete vars.autocompleteLastCompletion;

			// We only need to proceed when the `if-else` statement was inserted.
			if ( completion.displayText !== 'if ( ):' ) {
				return;
			}

			const doc = cm.getDoc(),
				line = cm.getLine( event.from.line ),
				matchIndent = line.match( /^\s+/ ),
				indent = matchIndent ? matchIndent[ 0 ] : '',
				textLines = completion.text ? completion.text.split( '\n' ) : [];

			// Put cursor to the condition parentheses.
			doc.setCursor( {
				line: event.from.line,
				ch: event.from.ch + 5,
			} );

			if ( indent === '' ) {
				return;
			}

			// Add indentation if the start line has an indentation.
			for ( let i = 1; i < textLines.length; i++ ) {
				const lineNum = event.from.line + i;

				doc.replaceRange( indent + textLines[ i ], { line: lineNum, ch: 0 }, { line: lineNum } );
			}
		},

		/**
		 * Expand/collapse code editor.
		 *
		 * @since 1.0.0
		 */
		expandCollapseEditor() {
			const $button = $( this ),
				$row = $button.closest( '.wpforms-field-option-row' );

			// Collapse.
			if ( $row.hasClass( 'expanded' ) ) {
				app.collapseEditor( $button );

				return;
			}

			// Expand.
			app.expandEditor( $button );
		},

		/**
		 * Collapse code editor.
		 *
		 * @param {jQuery} $button Collapse button.
		 *
		 * @since 1.0.0
		 */
		collapseEditor( $button ) {
			const $row = $button.closest( '.wpforms-field-option-row' );

			if ( ! $row.hasClass( 'expanded' ) ) {
				return;
			}

			const $editorWrap = $row.find( '.wpforms-calculations-editor-wrap' ),
				$editorCollapsed = $row.find( '.wpforms-calculations-editor-collapsed' ),
				$codeMirror = $editorWrap.find( '.CodeMirror' ),
				cm = $codeMirror[ 0 ] ? $codeMirror[ 0 ].CodeMirror : null;

			$row.removeClass( 'expanded' );

			// Before insert the editor into the collapsed container, we need to set the editor size to the expanded container size.
			cm.setSize( '100%', '' );

			$editorWrap.appendTo( $editorCollapsed );
			$button.find( 'i' ).removeClass( 'fa-compress' ).addClass( 'fa-expand' );

			// Enable scrolling in the field options container.
			$editorWrap.closest( '.wpforms-field-options.wpforms-tab-content' ).css( 'overflow-y', 'auto' );

			// No needs for the event listener.
			el.$builder.off( 'click.ExpandedEditor' );

			if ( cm ) {
				cm.refresh();
				cm.focus();
			}
		},

		/**
		 * Expand code editor.
		 *
		 * @param {jQuery} $button Collapse button.
		 *
		 * @since 1.0.0
		 */
		expandEditor( $button ) {
			const $row = $button.closest( '.wpforms-field-option-row' );

			if ( $row.hasClass( 'expanded' ) ) {
				return;
			}

			const $editorWrap = $row.find( '.wpforms-calculations-editor-wrap' ),
				$editorExpanded = $row.find( '.wpforms-calculations-editor-expanded' ),
				$codeMirror = $editorWrap.find( '.CodeMirror' ),
				cm = $codeMirror[ 0 ] ? $codeMirror[ 0 ].CodeMirror : null,
				offset = $editorWrap.offset();

			// Before insert the editor into the expanded container, we need to set the editor size to the expanded container size.
			cm.setSize( vars.codemirror.expandedEditorWidth, '' );

			// Expand.
			$editorWrap.appendTo( $editorExpanded );
			$row.addClass( 'expanded' );
			$editorExpanded.css( 'top', offset.top );
			$button.find( 'i' ).removeClass( 'fa-expand' ).addClass( 'fa-compress' );

			// Disable scrolling in the field options container.
			$editorWrap.closest( '.wpforms-field-options.wpforms-tab-content' ).css( 'overflow-y', 'hidden' );

			// Collapse on click outside.
			el.$builder.on( 'click.ExpandedEditor', function( e ) {
				const $target = $( e.target );

				// Skip clicks inside the editor wrapper and the insert field dropdown.
				if ( $target.closest( $editorWrap ).length || $target.closest( '.insert-field-dropdown' ).length ) {
					return;
				}

				el.$builder.off( 'click.ExpandedEditor' );

				if ( $row.hasClass( 'expanded' ) ) {
					// Collapse the formula editor (if expanded).
					app.collapseEditor( $button );
				}
			} );

			if ( cm ) {
				cm.refresh();
				cm.focus();
			}
		},

		/**
		 * Validate formula.
		 *
		 * @since 1.0.0
		 */
		validateFormula() {
			const $button = $( this );
			const aiFixableErrors = [
				'validation_variable_not_allowed',
				'validation_field_not_allowed',
				'validation_field_total_in_single_item',
				'validation_subfield_not_allowed',
				'validation_repeater_field_not_allowed',
			];

			if ( $button.hasClass( 'disabled' ) ) {
				return;
			}

			const fieldId = $button.closest( '.wpforms-field-option-row' ).data( 'field-id' ),
				data = {
					action: 'wpforms_calculations_validate_formula',
					_wp_http_referer: app.updateURLQueryParam( window.location.href, '_wp_http_referer' ), // eslint-disable-line camelcase
					form_id: $( '#wpforms-builder-form' ).data( 'id' ), // eslint-disable-line camelcase
					field_id: fieldId, // eslint-disable-line camelcase
					code: wpf.getField( fieldId ).calculation_code,
					nonce: wpforms_builder.nonce,
				};

			let $spinner = $button.find( '.wpforms-loading-spinner' );

			if ( ! $spinner.length ) {
				$button.append( '<i class="wpforms-loading-spinner wpforms-loading-inline"></i>' );
				$spinner = $button.find( '.wpforms-loading-spinner' );
			}

			$spinner.show();
			$button.removeClass( 'success' ).removeClass( 'error' );

			// Validate the field variables in the formula before submitting to the server-side validation.
			const fieldVarsError = app.getValidateFieldVarsError( data.code, fieldId );

			if ( fieldVarsError ) {
				const canAiFixThis = ! wpforms_calculations.isAiDisabled && aiFixableErrors.includes( fieldVarsError.code );

				$spinner.hide();
				app.displayValidationResultModal( [ fieldVarsError.text ], canAiFixThis, fieldId );
				app.setValidateFormulaStatus( $button, 'error' );

				return;
			}

			// Perform server-side validation.
			$.post( wpforms_builder.ajax_url, data )
				.done( function( res ) {
					if ( res.success ) {
						app.setValidateFormulaStatus( $button, 'success' );
					} else {
						app.setValidateFormulaStatus( $button, 'error' );
						app.displayValidationResultModal( res.data, ! wpforms_calculations.isAiDisabled, fieldId );
					}
				} )
				.fail( function( xhr, textStatus ) {
					wpf.debug( 'Calculations:', wpforms_calculations.strings.ajaxFail, xhr.responseText || textStatus );
					app.displayValidationResultModal( wpforms_calculations.strings.ajaxFail + ' ' + ( xhr.responseText || textStatus ), false, fieldId );
					app.setValidateFormulaStatus( $button, 'error' );
				} )
				.always( function() {
					$spinner.hide();
				} );
		},

		/**
		 * Update parameter in the given URL and return the update URL.
		 *
		 * @since 1.1.0
		 *
		 * @param {string} url   URL string.
		 * @param {string} param Query parameter.
		 * @param {string} value New param value. If the value is empty - param will be removed from the URL.
		 *
		 * @return {string} Updated URL.
		 */
		updateURLQueryParam( url, param, value = '' ) {
			const urlObj = new URL( url );

			if ( value ) {
				// New value of param.
				urlObj.searchParams.set( param, value );
			} else {
				// Remove param from the URL.
				urlObj.searchParams.delete( param );
			}

			return urlObj.toString();
		},

		/**
		 * Set Validate Formula button status.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $button Validate Formula button.
		 * @param {string} status  Status: 'success' or 'error'.
		 */
		setValidateFormulaStatus( $button, status ) {
			status = status === 'success' ? 'success' : 'error';

			$button
				.addClass( status )
				.find( '+ span' )
				.attr( 'title', status === 'success' ? wpforms_calculations.strings.validateButtonSuccess : wpforms_calculations.strings.validateButtonErrors );
		},

		/**
		 * Get field variables validation error.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} code    Formula code.
		 * @param {string} fieldId Field ID.
		 *
		 * @return {object|null} Error message.
		 */
		getValidateFieldVarsError( code, fieldId ) { // eslint-disable-line complexity
			// Get the list of fields used in the formula.
			const fieldsMatch = code.toString().matchAll( /(\$+[A-Za-z]+)(\d*)(_[A-Za-z0-9_]+)*/mg ),
				formFields = app.getFormFieldsByIds(); // eslint-disable-line @wordpress/no-unused-vars-before-return

			// Loop through all the fields used in the formula.
			for ( const field of fieldsMatch ) {
				const varName = field[ 1 ],
					fieldId$F = field[ 2 ];

				// The variable is not allowed.
				if (
					! varName ||
					varName !== '$F' ||
					( varName === '$F' && ! fieldId$F )
				) {
					return {
						text: wpforms_calculations.strings.validationVariableNotAllowed.replaceAll( '%1$s', field[ 0 ] ),
						code: 'validation_variable_not_allowed',
					};
				}

				// Check if field is in repeater
				if ( app.isFieldInRepeater( fieldId$F ) ) {
					return {
						text: wpforms_calculations.strings.validationRepeaterFieldNotAllowed.replaceAll( '%1$s', fieldId$F.toString() ),
						code: 'validation_repeater_field_not_allowed',
					};
				}

				// The field doesn't exist.
				if ( ! formFields[ fieldId$F ] ) {
					return {
						text: wpforms_calculations.strings.validationFieldDoesntExist.replaceAll( '%1$s', fieldId$F.toString() ),
						code: 'validation_field_doesnt_exist',
					};
				}

				// The field type is not allowed to use in calculations.
				if ( ! fields.isAllowedField( formFields[ fieldId$F ].type ) ) {
					return {
						text: wpforms_calculations.strings.validationFieldNotAllowed.replaceAll( '%1$s', fieldId$F.toString() ).replaceAll( '%2$s', formFields[ fieldId$F ].type ),
						code: 'validation_field_not_allowed',
					};
				}

				// The Total field is not allowed in Single Item field (circular reference).
				if ( formFields[ fieldId$F ].type === 'payment-total' && formFields[ fieldId ].type === 'payment-single' ) {
					return {
						text: wpforms_calculations.strings.validationFieldTotalInSingleItem
							.replaceAll( '%1$s', fieldId$F.toString() )
							.replaceAll( '%2$s', fieldId.toString() ),
						code: 'validation_field_total_in_single_item',
					};
				}

				const subfieldError = app.getValidateSubfieldVarsError( formFields[ fieldId$F ], field, fieldId$F );

				if ( subfieldError ) {
					return subfieldError;
				}
			}

			return null;
		},

		/**
		 * Get the error message for the subfield validation.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object}        formField The form field.
		 * @param {Array}         field     The field.
		 * @param {number|string} fieldId$F The field ID.
		 *
		 * @return {Object|null} The error message.
		 */
		getValidateSubfieldVarsError( formField, field, fieldId$F ) {
			// Determine the subfield.
			const subfield = field[ 3 ] ? field[ 3 ].replace( '_', '' ) : null;

			// The subfield is not allowed.
			if ( ! subfield || subfield.length === 0 ) {
				return null;
			}

			if ( fields.isAllowedSubField( subfield, Number( fieldId$F ), formField ) ) {
				return null;
			}

			return {
				text: wpforms_calculations.strings.validationSubfieldNotAllowed
					.replaceAll( '%1$s', fieldId$F.toString() )
					.replaceAll( '%2$s', subfield ),
				code: 'validation_subfield_not_allowed',
			};
		},

		/**
		 * Display the validation result in the modal window.
		 *
		 * @since 1.0.0
		 *
		 * @param {Array|string} errors       Validation errors.
		 * @param {boolean}      canAiFixThis Whether to show "Fix with AI" button.
		 * @param {number}       fieldId      Field ID.
		 */
		displayValidationResultModal( errors, canAiFixThis = false, fieldId = 0 ) {
			if ( ! errors || errors.length === 0 ) {
				return;
			}

			errors = Array.isArray( errors ) ? errors : [ errors ];

			const title = wpforms_calculations.strings.validationModalErrorTitle,
				content = `${ wpforms_calculations.strings.validationModalErrorMsg }
					<div class="wpforms-calculations-modal-errors">
						<ol><li>${ errors.join( '<li>' ) }</ol>
					</div>`;

			const alertConf = {
				title,
				content,
				icon: 'fa fa-exclamation-circle',
				type: 'red',
				boxWidth: '400px',
				buttons: {
					confirm: {
						text: wpforms_calculations.strings.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			};

			if ( canAiFixThis ) {
				alertConf.buttons.fix = {
					text:  wpforms_calculations.strings.fixWithAi,
					btnClass: 'btn-fix-with-ai',
					keys: [ 'enter' ],
				};

				if ( wpforms_calculations.isLicenseActive ) {
					alertConf.buttons.fix.action = function() {
						app.clickFixWithAiButton( errors, fieldId );
					};
				} else {
					alertConf.onOpen = function() {
						$( '.btn-fix-with-ai' ).attr( {
							'data-action': 'license',
							'data-field-name': wpforms_calculations.strings.aiCalculations,
							'data-utm-content': 'AI Calculations',
							'data-field-id': fieldId,
						} ).addClass( ' education-modal' );
					};
				}
			}

			$.alert( alertConf );
		},

		/**
		 * Click on the toolbar button event handler.
		 *
		 * @since 1.0.0
		 */
		toolbarButtonClick() {
			const $button = $( this );

			// Skip if expand editor button.
			if ( $button.hasClass( 'button-expand-editor' ) ) {
				return;
			}

			// Insert field.
			if ( $button.hasClass( 'button-insert-field' ) ) {
				app.toolbarButtonInsertFieldClick( $button );

				return;
			}

			// Other buttons (operators, brackets).
			const char = $button.text(),
				cm = app.getButtonCodeMirrorInstance( $button ),
				doc = cm.getDoc(),
				selection = doc.getSelection();

			cm.focus();

			if ( selection.length && [ '(', ')' ].includes( char ) ) {
				doc.replaceSelection( ' ( ' + selection + ' ) ' );

				return;
			}

			if ( selection.length ) {
				doc.replaceSelection( selection + ` ${ char } ` );

				return;
			}

			doc.replaceRange( ' ' + char + ' ', doc.getCursor() );
		},

		/**
		 * Press Enter key toolbar button event handler.
		 *
		 * @since 1.0.0
		 */
		toolbarButtonFocus() {
			$( this ).closest( '.wpforms-calculations-editor-wrap' ).addClass( 'focused' );
		},

		/**
		 * Press Enter key toolbar button event handler.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event object.
		 */
		toolbarButtonEnterKey( event ) {
			if ( event.keyCode === 13 ) {
				$( this ).click();
			}
		},

		/**
		 * Click on the Insert Field toolbar button event handler.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $button Insert Field button jQuery object.
		 */
		toolbarButtonInsertFieldClick( $button ) {
			const cm = app.getButtonCodeMirrorInstance( $button );

			// Get the Dropdown List instance.
			const dropdownList = app.getDropdownListInstance( $button, cm );

			// Bail if the button is disabled and the list is empty.
			if ( ! dropdownList ) {
				return;
			}

			const isActive = $button.hasClass( 'active' );

			$button.toggleClass( 'active', ! isActive );

			// Close the dropdown and focus back to the editor.
			if ( isActive ) {
				cm.focus();
				dropdownList.close();

				return;
			}

			// Open the dropdown.
			dropdownList.open();
		},

		/**
		 * Get the DropdownList instance of the Insert Field button.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery}     $button Insert Field button jQuery object.
		 * @param {CodeMirror} cm      CodeMirror instance.
		 *
		 * @return {DropdownList|null} DropdownList instance.
		 */
		getDropdownListInstance( $button, cm ) {
			let dropdownList = $button.data( 'dropdown-list' );

			if ( dropdownList ) {
				return dropdownList;
			}

			const fieldId = $button.closest( '.wpforms-field-option-row' ).data( 'field-id' ),
				list = fields.getVarsList( 'dropdown', fieldId );

			// Bail and disable the button if there are no variables in the list.
			if ( ! list.length ) {
				$button.addClass( 'disabled' );

				return null;
			}

			$button.removeClass( 'disabled' );

			// Get the CodeMirror instance if it's not passed.
			cm = cm || app.getButtonCodeMirrorInstance( $button );

			if ( ! cm ) {
				return null;
			}

			dropdownList = WPForms.Admin.Builder.DropdownList.init( {
				class: 'insert-field-dropdown',
				title: wpforms_calculations.strings.insertFieldDropdownTitle,
				list,
				container: $button.closest( '.wpforms-field-option-row' ),
				scrollableContainer: $button.closest( '.wpforms-field-options.wpforms-tab-content' ),
				button: $button,
				buttonDistance: 21,
				itemFormat( item ) {
					return `<span title="${ item.text }">${ item.text }</span><span class="grey field-variable">${ item.value }</span>`;
				},
				onSelect( event, value, text, $item, dropdownListInstance ) {
					const doc = cm.getDoc(),
						cursor = doc.getCursor();

					doc.replaceRange( ' ' + value + ' ', cursor );
					dropdownListInstance.close();
					$button.removeClass( 'active' );
				},
			} );

			// Callback for an opening dropdown list. Save it to avoid recursion.
			const openCallback = dropdownList.open.bind( dropdownList );

			// Override the open method to handle re-initialization.
			dropdownList.open = function() {
				// If the button doesn't need to re-init, call the original open method.
				if ( ! this.$button.data( 're-init' ) ) {
					openCallback();

					return;
				}

				// Reset re-init flag.
				this.$button.data( 're-init', false );
				// Destroy the current instance.
				this.destroy();
				fields.updateVarsObject();

				// Create and open a new instance.
				const newInstance = app.getDropdownListInstance( this.$button, null );
				newInstance.open();
			};

			$button.data( 'dropdown-list', dropdownList );

			return dropdownList;
		},

		/**
		 * Get CodeMirror instance for given toolbar button.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $button Toolbar button element.
		 *
		 * @return {CodeMirror|null} CodeMirror instance.
		 */
		getButtonCodeMirrorInstance( $button ) {
			try {
				return $button.closest( '.wpforms-calculations-editor-wrap' ).find( '.CodeMirror' )[ 0 ].CodeMirror;
			} catch ( e ) {
				return null;
			}
		},

		/**
		 * Update Fields event.
		 *
		 * @since 1.0.0
		 */
		updateFieldsEvent() {
			app.updateInsertFieldDropdowns();
		},

		/**
		 * Update Insert Field Dropdowns.
		 *
		 * @since 1.0.0
		 */
		updateInsertFieldDropdowns() {
			el.$builder.find( '.wpforms-calculations-editor-wrap .button-insert-field' ).each( function() {
				const $button = $( this ),
					dropdownList = $button.data( 'dropdown-list' );

				if ( ! dropdownList ) {
					return;
				}

				$button.data( 're-init', true );
				dropdownList.close();
				$button.removeClass( 'active' );
			} );
		},

		/**
		 * Handler for the `wpformsFieldDuplicated` event.
		 *
		 * @since 1.1.0
		 *
		 * @param {Object} event      event.
		 * @param {number} id         Field ID.
		 * @param {jQuery} $field     Field element.
		 * @param {number} newFieldId New field ID.
		 * @param {jQuery} $newField  New field element.
		 */
		fieldDuplicated( event, id, $field, newFieldId, $newField ) { // eslint-disable-line no-unused-vars
			if ( $newField.hasClass( 'wpforms-field-layout' ) ) {
				app.initDuplicatedLayoutFieldCodeEditors( $newField );

				return;
			}

			app.initDuplicatedFieldCodeEditor( newFieldId );
		},

		/**
		 * Re-init code editor of the duplicated field.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} newFieldId New field ID.
		 */
		initDuplicatedFieldCodeEditor( newFieldId ) {
			const $calcIsEnabled = el.$builder.find( `#wpforms-field-option-${ newFieldId }-calculation_is_enabled` );

			if ( ! $calcIsEnabled.length ) {
				return;
			}

			const $fieldOptions = el.$builder.find( '#wpforms-field-option-' + newFieldId ),
				$EditorWrap = $fieldOptions.find( `#wpforms-field-option-row-${ newFieldId }-calculation_code .wpforms-calculations-editor-wrap` ),
				$selectFieldDropDown = $EditorWrap.closest( '.wpforms-field-option-row-calculation_code' ).find( '.wpforms-builder-dropdown-list' ),
				$textarea = $EditorWrap.find( 'textarea.wpforms-codemirror-editor' );

			// Remove the editor instance.
			$EditorWrap.find( '.CodeMirror' ).remove();

			$selectFieldDropDown.remove();

			$textarea.show();

			app.setupAllEditors( $fieldOptions );
		},

		/**
		 * Re-init code editor of the fields inside the duplicated Layout field.
		 *
		 * @since 1.1.0
		 *
		 * @param {jQuery} $layoutField New field element.
		 */
		initDuplicatedLayoutFieldCodeEditors( $layoutField ) {
			$layoutField.find( '.wpforms-layout-column .wpforms-field' ).each( function() {
				const $field = $( this ),
					fieldId = $field.data( 'field-id' );

				app.initDuplicatedFieldCodeEditor( fieldId );
			} );
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
		 * Get the form fields data structured by IDs.
		 *
		 * @since 1.3.0
		 *
		 * @return {Object} The fields data object.
		 */
		getFormFieldsByIds() {
			const formFields = wpf.getFields();
			const fieldsById = {};

			for ( const key in formFields ) {
				fieldsById[ formFields[ key ].id ] = formFields[ key ];
			}

			return fieldsById;
		},

		/**
		 * Get editor instance by field ID.
		 *
		 * @since 1.6.0
		 *
		 * @param {number} id Field ID.
		 *
		 * @return {Object|null} Editor instance.
		 */
		getEditorInstance( id ) {
			return vars.codemirror.editors[ id ] || null;
		},

		/**
		 * Check if a field is inside a repeater field.
		 *
		 * @since 1.6.0
		 *
		 * @param {string|number} searchId The ID of the field to search for.
		 *
		 * @return {boolean} Returns true if the field is found inside a repeater field, otherwise false.
		 */
		isFieldInRepeater( searchId ) {
			const formData = wpf.formObject( '#wpforms-field-options' ) || {};
			const actualFields = formData.fields ?? {};

			return Object.values( actualFields ).some( ( field ) => {
				if ( field.type !== 'repeater' ) {
					return false;
				}

				const columns = field[ 'columns-json' ] ?? {};
				return Object.values( columns ).some( ( column ) => {
					const columnFields = column?.fields ?? [];
					return columnFields.some( ( fieldId ) => fieldId?.toString() === String( searchId ) );
				} );
			} );
		},
	};

	/**
	 * Fields functions.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	fields = {

		/**
		 * Field variables registry.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		variables: null,

		/**
		 * Fields variables order.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		variablesOrder: null,

		/**
		 * Field types allowed in calculations.
		 *
		 * @since 1.0.0
		 *
		 * @type {Array}
		 */
		allowedTypes: null,

		/**
		 * Field types in which calculation is possibles.
		 *
		 * @since 1.0.0
		 *
		 * @type {Array}
		 */
		calculationIsPossibleTypes: null,

		/**
		 * Init fields object.
		 *
		 * @since 1.0.0
		 */
		init() {
			fields.allowedTypes = Object.keys( wpforms_calculations.allowedFields );
			fields.calculationIsPossibleTypes = Object.values( wpforms_calculations.calculationIsPossibleFields );

			fields.updateVarsObject();
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
			return fields.allowedTypes.includes( fieldType );
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
		isCalculationPossible( fieldType ) {
			return fields.calculationIsPossibleTypes.includes( fieldType );
		},

		/**
		 * Check if the subfield is allowed in calculations.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} subfield  Subfield slug.
		 * @param {number} fieldId   Field Id.
		 * @param {Object} formField Form field.
		 *
		 * @return {boolean} True when field is allowed.
		 */
		isAllowedSubField( subfield, fieldId, formField = null ) { // eslint-disable-line complexity
			const field = wpf.getField( fieldId );

			let allowedSubfields = wpforms_calculations.allowedFields[ field.type ];

			switch ( field.type ) {
				case 'checkbox':
				case 'payment-checkbox':
					if ( ! field.dynamic_choices ) {
						allowedSubfields = allowedSubfields.concat( Object.keys( field.choices ) );
					}
					return allowedSubfields.includes( subfield );

				case 'payment-multiple':
				case 'payment-select':
				case 'payment-single':
				case 'payment-total':
					return allowedSubfields.includes( subfield );

				case 'email':
					return formField?.confirmation === 1 && allowedSubfields.includes( subfield );

				case 'address':
					if ( formField.scheme !== 'international' && formField?.additional?.includes( 'country' ) ) {
						// Remove the country in that case.
						formField.additional = formField.additional.filter( ( item ) => item !== 'country' );
					}
					return formField?.additional && formField?.additional.includes( subfield );

				case 'date-time':
				case 'name':
					return formField?.additional && formField?.additional.includes( subfield );

				default:
					// Field has subfield but is not one of the types above. Return false as it's not allowed.
					return false;
			}
		},

		/**
		 * Get fields in which the field is used in formula.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} fieldId Field Id.
		 *
		 * @return {Array|boolean} Array of fields in which the field is used in formula. False if not used.
		 */
		getUsedInFields( fieldId ) {
			const fieldsData = wpf.getFields();

			let result = false;

			for ( const id in fieldsData ) {
				// Find the field variable in the formula.
				const rgx = new RegExp( '\\$F' + fieldId + '[^\\d]', 'mg' );

				if ( rgx.test( fieldsData[ id ].calculation_code ) ) {
					result = ! result ? {} : result;
					result[ fieldsData[ id ].id ] = wpf.sanitizeHTML( fieldsData[ id ].label, undefined );
				}
			}

			return result;
		},

		/**
		 * Get field variables object.
		 *
		 * @since 1.0.0
		 *
		 * @return {Object} Field variables.
		 */
		getVarsObject() {
			if ( fields.variables === null ) {
				fields.updateVarsObject();
			}

			return fields.variables;
		},

		/**
		 * Update field variables object.
		 *
		 * @since 1.0.0
		 */
		updateVarsObject() {
			fields.variables = {};

			const fieldsData = wpf.getFields( fields.allowedTypes, false );

			for ( const id in fieldsData ) {
				fields.add( {}, fieldsData[ id ].id, fieldsData[ id ].type );
			}

			fields.updateVarsOrder();
		},

		/**
		 * Get field variables as flattened array.
		 *
		 * @since 1.0.0
		 *
		 * @return {Array} Field variables.
		 */
		getVars() {
			const obj = fields.getVarsObject();

			let flattened = [];

			for ( const id in obj ) {
				flattened = [].concat( flattened, obj[ id ] );
			}

			return flattened;
		},

		/**
		 * Get field variables order.
		 *
		 * @since 1.0.0
		 *
		 * @return {Object} Field variables.
		 */
		getVarsOrder() {
			if ( fields.variablesOrder === null ) {
				fields.updateVarsOrder();
			}

			return fields.variablesOrder;
		},

		/**
		 * Update field variables order.
		 *
		 * @since 1.0.0
		 *
		 * @return {Object} Field variables.
		 */
		updateVarsOrder() {
			fields.variablesOrder = [];

			el.$builder.find( '.wpforms-preview .wpforms-field' ).each( function() {
				const $field = $( this );

				if ( ! fields.isAllowedField( $field.data( 'field-type' ) ) ) {
					return;
				}

				fields.variablesOrder.push( $field.data( 'field-id' ) );
			} );

			return fields.variablesOrder;
		},

		/**
		 * Get field variables as array of objects.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} type           List type.
		 * @param {string} currentFieldId Current field ID.
		 *
		 * @return {Array} Field variables.
		 */
		getVarsList( type, currentFieldId ) { // eslint-disable-line complexity
			const fieldVarsObj = fields.getVarsObject(),
				fieldVarsOrder = fields.getVarsOrder(),
				currentFieldSettings = wpf.getField( currentFieldId ),
				list = [];

			let fvar,
				subfield;

			type = type || 'autocomplete';

			for ( const o in fieldVarsOrder ) {
				const id = fieldVarsOrder[ o ];

				// Skip the current field.
				if ( currentFieldId && id.toString() === currentFieldId.toString() ) {
					continue;
				}

				const listFieldSettings = wpf.getField( id );

				// Skip Total field in Single Item field (prevent self-cycle).
				if ( listFieldSettings.type === 'payment-total' && currentFieldSettings.type === 'payment-single' ) {
					continue;
				}

				for ( const i in fieldVarsObj[ id ] ) {
					fvar = fieldVarsObj[ id ][ i ];

					// Prepare subfield.
					subfield = fvar.replace( /^\$F\d+_/, '' );
					subfield = subfield === fvar ? '' : subfield;
					subfield = parseInt( subfield, 10 ).toString() === subfield ? 'choice ' + subfield : subfield;
					subfield = subfield.length ? ' (' + subfield + ')' : '';

					// Add list item.
					if ( type === 'autocomplete' ) {
						list.push( {
							text: fvar,
							displayText: `<span>${ fvar }</span><span title="${ listFieldSettings.label + subfield }">${ listFieldSettings.label + subfield }</span>`,
						} );

						continue;
					}

					if ( type === 'dropdown' ) {
						list.push( {
							value: fvar,
							text: listFieldSettings.label + subfield,
						} );

						continue;
					}

					list.push( fvar );
				}
			}

			return list;
		},

		/**
		 * Add field variable to the registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Current DOM event.
		 * @param {number} id    Field ID.
		 * @param {string} type  Field type.
		 */
		add( event, id, type ) { // eslint-disable-line complexity
			if ( ! fields.isAllowedField( type ) ) {
				return;
			}

			// Delete the same field variable.
			fields.delete( event, id, type );
			fields.variables[ id ] = [ '$F' + id ];

			const fieldData = wpf.getField( id ),
				subFields = wpforms_calculations.allowedFields[ type ];

			// Update variables order in case if it is the add field event (single field operation).
			if ( event.type ) {
				fields.updateVarsOrder();
			}

			for ( const i in subFields ) {
				if ( type === 'email' && ! fieldData.confirmation ) {
					continue;
				}

				if (
					( type === 'address' && fieldData.scheme === 'us' && subFields[ i ] === 'country' ) ||
					( type === 'address' && fieldData.postal_hide && subFields[ i ] === 'postal' ) ||
					( type === 'address' && fieldData.address2_hide && subFields[ i ] === 'address2' )
				) {
					continue;
				}

				if (
					( type === 'name' && fieldData.format === 'simple' ) ||
					( type === 'name' && fieldData.format === 'first-last' && subFields[ i ] === 'middle' )
				) {
					continue;
				}

				if ( type === 'date-time' && fieldData.format !== 'date-time' ) {
					continue;
				}

				fields.variables[ id ].push( '$F' + id + '_' + subFields[ i ] );
			}

			if ( [ 'checkbox', 'payment-checkbox' ].includes( type ) ) {
				fields.addChoices( id );
			}
		},

		/**
		 * Add duplicated field variable to the registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event      Current DOM event.
		 * @param {number} id         Field ID.
		 * @param {jQuery} $field     Field element.
		 * @param {number} newFieldId New field ID.
		 * @param {jQuery} $newField  New field element.
		 */
		duplicated( event, id, $field, newFieldId, $newField ) { // eslint-disable-line no-unused-vars
			fields.add( event, newFieldId, wpf.getField( id ).type );
		},

		/**
		 * Remove field variable from the registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Current DOM event.
		 * @param {number} id    Field ID.
		 * @param {string} type  Field type.
		 */
		delete( event, id, type ) { // eslint-disable-line no-unused-vars
			// Delete in variables object.
			if ( fields.variables[ id ] ) {
				delete fields.variables[ id ];
			}

			// Remove element from the variables order array.
			const index = fields.variablesOrder ? fields.variablesOrder.indexOf( id ) : -1;
			if ( index !== -1 ) {
				fields.variablesOrder.splice( index, 1 );
			}
		},

		/**
		 * Move field (re-order fields) event.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Form Builder event.
		 */
		move( event ) { // eslint-disable-line no-unused-vars
			fields.updateVarsOrder();
		},

		/**
		 * Add Checkboxes field choices subfields variable to the registry.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} id Field ID.
		 */
		addChoices( id ) {
			const fieldData = wpf.getField( id );

			if ( ! fieldData || ! fieldData.choices || fieldData.dynamic_choices ) {
				return;
			}

			for ( const i in fieldData.choices ) {
				fields.variables[ id ].push( '$F' + id + '_' + i );
			}
		},

		/**
		 * Update Checkboxes field choices subfields variable to the registry.
		 *
		 * @since 1.0.0
		 */
		updateChoicesField() {
			const $this = $( this ),
				$field = $this.closest( '.wpforms-field-option' ),
				id = $field.find( '.wpforms-field-option-hidden-id' ).val();

			setTimeout( function() {
				el.$builder.trigger( 'wpformsCalcFieldChange', [ id ] );
			}, 100 );
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsCalculationsBuilder.init();
