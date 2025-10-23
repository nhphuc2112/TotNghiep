/* global wpforms, wpforms_save_resume, wpforms_settings */

'use strict';

/**
 * WPForms Save and Resume function.
 *
 * @since 1.0.0
 */
var WPFormsSaveResume = window.WPFormsSaveResume || ( function( document, window, $ ) {

	var $form,
		$formContainer,
		formData,
		submitState,
		currentFormId,
		pageScroll = 75;

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	var app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( document ).on( 'click', '.wpforms-save-resume-button', app.showSaveResume )
				.on( 'click', '.wpforms-save-resume-disclaimer-back', app.goBack )
				.on( 'click', '.wpforms-save-resume-disclaimer-continue', app.showConfirmation )
				.on( 'click', '.wpforms-save-resume-shortcode-copy', app.copyShortcodeToClipboard )
				.on( 'change', '.wpforms-field-email input[type=email]', app.validateEmail );

			$( '.wpforms-form' ).on( 'wpformsAjaxSubmitFailed', app.submitFailed );
		},

		/**
		 * Show save and resume.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event obj.
		 *
		 * @return {void}
		 */
		showSaveResume( event ) {
			$form = $( event.target ).closest( 'form.wpforms-form' );
			$formContainer = $form.parent().parent();
			currentFormId = $form.data( 'formid' );

			submitState = $form.find( '.wpforms-submit-container' ).is( ':visible' );

			// Scroll to top before showing the screen.
			app.scrollToTop( $form.offset().top - pageScroll, 750 );

			if ( $( '#wpforms-save-resume-disclaimer-' + currentFormId ).length >= 1 ) {
				app.showDisclaimer( event );
			} else {
				app.showConfirmation( event );
			}
		},

		/**
		 * Save entry on click.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} $form Form.
		 */
		// eslint-disable-next-line no-shadow
		prepareData( $form ) {
			formData = new FormData( $form.get( 0 ) );

			formData.append( 'action', 'wpforms_save_resume' );
			formData.append( 'page_url', window.location.href );

			// Include form token if exists.
			if ( $form.data( 'token' ) && $( '.wpforms-token', $form ).length === 0 ) {
				formData.append( 'wpforms[token]', $form.data( 'token' ) );
			}
		},

		/**
		 * Display Disclaimer.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event object.
		 *
		 * @return {void}
		 */
		showDisclaimer( event ) {
			event.preventDefault();

			app.prepareData( $form );
			$form.find( '.wpforms-field-container, .wpforms-submit-container, .wpforms-recaptcha-container, .wpforms-page-indicator, .wpforms-error-container, .wpforms-save-resume-expired-message' ).hide();

			app.hideExpiredMessage();

			$( '#wpforms-save-resume-disclaimer-' + currentFormId ).detach().insertAfter( $form ).show();
		},

		/**
		 * Display Confirmation.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event object.
		 */
		showConfirmation( event ) {
			event.preventDefault();

			$( '#wpforms-save-resume-disclaimer-' + currentFormId ).hide();
			app.prepareData( $form );
			$form.find( '.wpforms-field-container, .wpforms-submit-container, .wpforms-recaptcha-container, .wpforms-page-indicator, .wpforms-error-container' ).hide();

			app.hideExpiredMessage();

			$( '#wpforms-save-resume-confirmation-' + currentFormId ).detach().insertAfter( $form ).show();
			app.sendData( event );
		},

		/**
		 * Send the data.
		 *
		 * @since 1.0.0
		 */
		sendData( ) {
			if ( ! formData || ! currentFormId ) {
				return;
			}

			app.debug( 'The hash is generating...' );
			const args = {
				type: 'post',
				dataType: 'json',
				url: wpforms_save_resume.ajaxurl,
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
			};

			// Send the form(s) data via ajax.
			$.ajax( args ).done( function( data ) {

				if ( data.success === false || ! data.data.hash.length ) {
					app.debug( 'The hash hasn\'t generated' );
					$form.find( '.wpforms-error-container' ).remove();
					$form.prepend( '<div class="wpforms-error-container">' + wpforms_settings.save_resume_hash_error + '</div>' );
					$( '#wpforms-save-resume-confirmation-' + currentFormId ).hide();
					$form.find( '.wpforms-field-container, .wpforms-submit-container, .wpforms-recaptcha-container, .wpforms-page-indicator, .wpforms-error-container' ).show();
					wpforms.scrollToError( $form.find( '.wpforms-error-container' ) );

					return;
				}

				app.debug( 'The hash has generated successfully' );
				$formContainer.find( '.wpforms-save-resume-entry-id' ).val( data.data.entry_id );
				$formContainer.find( '.wpforms-save-resume-shortcode' ).val( data.data.hash );
				$formContainer.find( '.wpforms-submit' ).prop( 'disabled', false );
			} );

			formData = new FormData();
		},

		/**
		 * Return the form.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} event Event obj.
		 */
		goBack( event ) {
			event.preventDefault();

			const $submitContainer = $form.find( '.wpforms-submit-container' );

			$( '#wpforms-save-resume-disclaimer-' + currentFormId ).hide();

			app.hideExpiredMessage();

			if ( submitState !== false ) {
				$submitContainer.show();
			}

			$form.find( '.wpforms-field-container, .wpforms-recaptcha-container, .wpforms-page-indicator' ).show();
		},

		/**
		 * Copies the shortcode embed code to the clipboard.
		 *
		 * @since 1.0.0
		 */
		copyShortcodeToClipboard: function() {

			var $shortcodeInput = $formContainer.find( '.wpforms-save-resume-shortcode' ),
				$shortcodeCopy = $formContainer.find( '.wpforms-save-resume-shortcode-copy' );

			// Remove disabled attribute, select the text, and re-add disabled attribute.
			$shortcodeInput
				.prop( 'disabled', false )
				.select()
				.prop( 'disabled', true );

			// Copy it.
			document.execCommand( 'copy' );

			var $icon = $shortcodeCopy.find( 'i' );

			// Add visual feedback to copy command.
			$icon.removeClass( 'fa-files-o' ).addClass( 'fa-check' );

			// Reset visual confirmation back to default state after 2.5 sec.
			window.setTimeout( function() {
				$icon.removeClass( 'fa-check' ).addClass( 'fa-files-o' );
			}, 2500 );

			app.debug( 'The link has copied successfully' );
		},

		/**
		 * Validate email.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} event Event obj.
		 */
		validateEmail: function( event ) {

			event.preventDefault();

			$( $form ).validate( {
				errorClass: 'wpforms-error',
				validClass: 'wpforms-valid',
				rules: {
					email: {
						required: true,
						email: true,
					},
				},
				messages: {
					email: wpforms_settings.val_email,
				},
				submitHandler: function( form ) {
					form.submit();
				},
			} );
		},

		/**
		 * Hide expired entry message.
		 *
		 * @since 1.2.0
		 */
		hideExpiredMessage() {
			const $expiredMsg = $( '.wpforms-save-resume-expired-message' );

			if ( $expiredMsg.length > 0 ) {
				$expiredMsg.hide();
				$( '.wpforms-save-resume-form-hidden' ).show();
			}
		},

		/**
		 * Scroll to the top of a form.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} position Position (in pixels) to scroll to,
		 * @param {number} duration Animation duration.
		 */
		scrollToTop: function( position, duration ) {

			duration = duration || 1000;
			$( 'html, body' ).animate( { scrollTop: parseInt( position, 10 ) }, { duration: duration } );
		},

		/**
		 * Optional debug messages.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} msg Debug message.
		 */
		debug: function( msg ) {

			if ( window.location.hash && window.location.hash === '#wpformsfadebug' ) {
				console.log( 'WPForms S&R: ' + msg );
			}
		},

		/**
		 * Hide the form fields if the resume save failed.
		 *
		 * @since 1.6.0
		 *
		 * @param {object} event Form submit event.
		 * @param {object} json  Ajax response data.
		 */
		submitFailed: function( event, json ) {

			if ( json.data.save_resume_error ) {

				const $form = $( event.target );
				$form.find( '.wpforms-field-container' ).hide();
				$form.find( '.wpforms-submit-container' ).hide();
			}
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsSaveResume.init();
