/* global WPFormsAIChatHTMLElement, WPFormsBuilder, WPFormsCalculationsBuilder, WPFormsAIModal */

/**
 * The WPForms AI-chat element.
 *
 * Calculations chat helpers module.
 *
 * @since 1.6.0
 *
 * @param {WPFormsAIChatHTMLElement} chat The chat element.
 *
 * @return {Object} The calculations chat helpers object.
 */
export default function( chat ) { // eslint-disable-line max-lines-per-function
	/**
	 * The `calculations` mode helpers object.
	 *
	 * @since 1.6.0
	 */
	return {
		/**
		 * New code editor content.
		 *
		 * @since 1.6.0
		 *
		 * @type {Object}
		 */
		newCodeEditorContent: {},

		/**
		 * Whether the answer is possible to be answered.
		 *
		 * Used to determine if the "Insert Formula" button should be displayed.
		 *
		 * @since 1.6.0
		 *
		 * @type {boolean}
		 */
		isPossibleToAnswer: true,

		/**
		 * Get the `calculations` answer based on AI response data.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} response The response data.
		 *
		 * @return {string} Answer HTML markup.
		 */
		// eslint-disable-next-line complexity
		getAnswer( response ) {
			this.isPossibleToAnswer = ! response.isImpossible;
			this.newCodeEditorContent[ response.responseId ] = response?.newCodeEditorContent ?? '';

			const header = response.answerText.title ?? '',
				text = response.answerText.details ?? '';

			let answerHtml = `
				<h4>${ chat.htmlSpecialChars( header ) }</h4>
				<span>${ chat.htmlSpecialChars( text ) }</span>
			`;

			if ( response.newCodeEditorContent && this.isPossibleToAnswer ) {
				answerHtml += `<pre dir="ltr">${ chat.htmlSpecialChars( this.newCodeEditorContent[ response.responseId ] ) }</pre>`;
			}

			// Add footer to the first answer only.
			if ( ! chat.sessionId ) {
				answerHtml += this.isPossibleToAnswer ? `<span>${ chat.modeStrings.footer }</span>` : `<span class="wpforms-ai-calculations-chat-answer-footer-learn-more">${ chat.modeStrings.footerLearnMore }</span>`;
			}

			return answerHtml;
		},

		/**
		 * Split the response text into header and text.
		 *
		 * @since 1.6.0
		 *
		 * @param {string} responseText The response text.
		 *
		 * @return {Array} Two elements array with the header and the text.
		 */
		splitResponseText( responseText ) {
			let header, text;

			const dotIndex = responseText.indexOf( '. ' ); // With space to not split numbers eg. USD 1.00.

			if ( dotIndex > -1 ) {
				header = responseText.substring( 0, dotIndex ) + '.';
				text = responseText.substring( dotIndex + 1 );
			} else {
				header = responseText;
				text = '';
			}

			return [ header, text ];
		},

		/**
		 * Get the answer pre-buttons HTML markup.
		 *
		 * @since 1.6.0
		 *
		 * @return {string} The answer pre-buttons HTML markup.
		 */
		getAnswerButtonsPre() {
			if ( this.isPossibleToAnswer ) {
				return `
					<button type="button" class="wpforms-ai-chat-calculations-insert wpforms-ai-chat-answer-action wpforms-btn-sm wpforms-btn-orange" >
						<span>${ chat.modeStrings.insert }</span>
					</button>
				`;
			}

			return `
				<a class="wpforms-ai-chat-calculations-learn-more wpforms-ai-chat-answer-learn-more wpforms-btn-sm wpforms-btn-orange" href="${ chat.modeStrings.learnMoreUrl }" target="_blank" rel="noopener noreferrer">
					<span>${ chat.modeStrings.learnMoreButton }</span>
				</a>
			`;
		},

		/**
		 * Get the warning message HTML markup.
		 *
		 * @since 1.6.0
		 *
		 * @return {string} The warning message HTML markup.
		 */
		getWarningMessage() {
			if ( chat.prefill.length !== 0 ) {
				return '<div class="wpforms-ai-chat-divider"></div>';
			}

			return this.getChatNotice( chat.modeStrings.warning );
		},

		/**
		 * Get the initial chat message.
		 *
		 * @since 1.6.0
		 *
		 * @param {string} content The initial chat message.
		 *
		 * @return {string} The initial chat message.
		 */
		getInitialChat( content ) {
			return this.getChatNotice( content );
		},

		/**
		 * Get the chat messages styled as notice.
		 *
		 * @since 1.6.0
		 *
		 * @param {string} content The chat message.
		 *
		 * @return {string} The chat message.
		 */
		getChatNotice( content ) {
			WPFormsAIModal.resizeModalHeight( chat.fieldId );

			return `<div class="wpforms-ai-chat-divider"></div>
					<div class="wpforms-chat-item-notice">
						<div class="wpforms-chat-item-notice-content">
							<span>${ content }</span>
						</div>
					</div>`;
		},

		/**
		 * If the field has no calculations yet, the welcome screen is active.
		 *
		 * @since 1.6.0
		 *
		 * @return {boolean} True if the field has no calculation formula defined, false otherwise.
		 */
		isWelcomeScreen() {
			return this.getCodeMirrorInstance( chat.fieldId )?.getValue().trim().length === 0;
		},

		/**
		 * Add the `calculations` answer.
		 *
		 * @since 1.6.0
		 *
		 * @param {HTMLElement} element The answer element.
		 */
		addedAnswer( element ) {
			const button = element.querySelector( '.wpforms-ai-chat-calculations-insert' );

			// Listen to the button click event.
			button?.addEventListener( 'click', this.insertButtonClick.bind( this ) );
		},

		/**
		 * Sanitize response.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} response The response data to sanitize.
		 *
		 * @return {Object} The sanitized response.
		 */
		sanitizeResponse( response ) {
			response.answerText.details = chat.htmlSpecialChars( response?.answerText?.details ?? '' );
			response.answerText.title = chat.htmlSpecialChars( response?.answerText?.title ?? '' );
			response.isImpossible = Boolean( response?.isImpossible ?? false );
			response.isImpossibleReason = chat.htmlSpecialChars( response?.isImpossibleReason ?? '' );

			return response;
		},

		/**
		 * Click on the Use Formula button.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} e Event object.
		 */
		insertButtonClick( e ) {
			const editor = this.getCodeMirrorInstance( chat.fieldId ),
				$message = jQuery( e.target ).closest( '.wpforms-chat-item.wpforms-chat-item-answer' ),
				responseId = $message.data( 'response-id' ),
				formula = this.newCodeEditorContent[ responseId ] ?? '';

			if ( ! editor ) {
				return;
			}

			// Rate the response.
			chat.wpformsAiApi.rate( true, responseId );

			jQuery( `.jconfirm-wpforms-ai-modal-calculations-${ chat.fieldId }` ).addClass( 'wpforms-hidden' ).fadeOut();

			Promise.all( [
				// Click on the field in the preview area, so the settings panel will be opened.
				jQuery( `#wpforms-field-${ chat.fieldId }` ).click().promise(),
				// Toggle settings to advanced tab.
				jQuery( `#wpforms-field-option-advanced-${ chat.fieldId } a.wpforms-field-option-group-toggle` ).click().promise(),
			] ).then( () => {
				editor.setValue( formula + ' ' );
				editor.setCursor( editor.lineCount(), 0 );
			} );
		},

		/**
		 * Prepare a message to send to the AI.
		 *
		 * @since 1.6.0
		 *
		 * @param {string} message Message to send.
		 *
		 * @return {string} Prepared message.
		 */
		prepareMessage( message ) {
			const currentCodeEditor = this.getCodeMirrorInstance( chat.fieldId )?.getValue() ?? '',
				formJson = JSON.stringify( this.reduceFormData( WPFormsBuilder.serializeAllData( jQuery( '#wpforms-builder-form' ) ) ) );

			const data = {
				fieldId: chat.fieldId,
				promptText: message,
				currentCodeEditor,
				formJson,
			};

			return JSON.stringify( data );
		},

		/**
		 * Get CodeMirror instance.
		 *
		 * @since 1.6.0
		 *
		 * @param {string} fieldId Field ID.
		 *
		 * @return {Object|null} CodeMirror instance or null.
		 */
		getCodeMirrorInstance( fieldId ) {
			return WPFormsCalculationsBuilder?.getEditorInstance( `wpforms-field-option-${ fieldId }-calculation_code` )?.codemirror ?? null;
		},

		/**
		 * Reduce serialized form data.
		 *
		 * Removes calculation code from field settings.
		 * Removes form settings (except title and description).
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} serializedFormData Serialized form data.
		 *
		 * @return {Object} Reduced serialized form data.
		 */
		reduceFormData( serializedFormData ) { // eslint-disable-line complexity
			serializedFormData = this.removeRedundantFields( serializedFormData );

			for ( const key in serializedFormData ) {
				// If this is setting but not the form title or description, remove it.
				if (
					serializedFormData[ key ].name.startsWith( 'settings[' ) &&
					! serializedFormData[ key ].name.startsWith( 'settings[form_desc]' ) &&
					! serializedFormData[ key ].name.startsWith( 'settings[form_title]' )
				) {
					delete serializedFormData[ key ];
					continue;
				}

				// Delete some values which are not important in calculation.
				if (
					serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][size]` ) ||
					serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][map_position]` ) ||
					serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][enable_address_autocomplete]` ) ||
					serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][display_map]` ) ||
					serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][css]` )
				) {
					delete serializedFormData[ key ];

					continue;
				}

				// Delete values not set.
				if ( serializedFormData[ key ].value.length === 0 ) {
					delete serializedFormData[ key ];

					continue;
				}

				// If this is not, the calculation code or calculation is enabled field setting, continue.
				if (
					! serializedFormData[ key ].name.endsWith( '[calculation_code]' ) &&
					! serializedFormData[ key ].name.endsWith( '[calculation_is_enabled]' )
				) {
					continue;
				}

				// Delete any calculation related settings from the field but not for the current field.
				if ( ! serializedFormData[ key ].name.startsWith( `fields[${ chat.fieldId }][calculation_` ) ) {
					delete serializedFormData[ key ];
				}
			}

			return serializedFormData;
		},

		/**
		 * Remove redundant fields from the form data.
		 *
		 * @since 1.6.0
		 *
		 * @param {Object} serializedFormData Serialized form data.
		 *
		 * @return {Object} Reduced serialized form data.
		 */
		removeRedundantFields( serializedFormData ) { // eslint-disable-line complexity
			const redundantFieldTypes = [
				'pagebreak',
				'divider',
				'file-upload',
				'richtext',
				'layout',
				'signature',
				'password',
			];

			const toDelete = [];

			for ( const key in serializedFormData ) {
				const isIncludes = redundantFieldTypes.includes( serializedFormData[ key ].value );
				const isStartsWith = serializedFormData[ key ].name.startsWith( 'fields[' );
				const isEndsWith = serializedFormData[ key ].name.endsWith( '][type]' );

				if (
					! isIncludes ||
					! isStartsWith ||
					! isEndsWith
				) {
					continue;
				}

				const fieldId = serializedFormData[ key ].name.split( '[' )[ 1 ].split( ']' )[ 0 ];

				// Delete all serializedFormData where name starts with `fields[${fieldId}]`.
				if ( ! toDelete.includes( `fields[${ fieldId }]` ) ) {
					toDelete.push( `fields[${ fieldId }]` );
				}
			}

			if ( ! toDelete.length ) {
				return serializedFormData;
			}

			for ( const key in serializedFormData ) {
				const matchingPrefix = toDelete.find( ( prefix ) => serializedFormData[ key ].name.startsWith( prefix ) );
				if ( matchingPrefix ) {
					delete serializedFormData[ key ];
				}
			}

			return serializedFormData;
		},
	};
}
