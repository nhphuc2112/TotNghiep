/**
 * WPForms Save and Resume confirmation page JS.
 *
 * @since 1.8.0
 */
const WPFormsSaveResumeConfirmation = window.WPFormsSaveResumeConfirmation || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.8.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.8.0
		 */
		init() {
			app.handleSuccessfulSubmit();

			$( '.wpforms-form' ).on( 'wpformsAjaxSubmitSuccess', app.removeQueryParameter );
		},

		/**
		 * Remove 'wpforms_resume_entry' query parameter on confirmation page.
		 *
		 * @since 1.8.0
		 */
		handleSuccessfulSubmit() {
			if ( window.location.search.indexOf( 'wpforms_resume_entry' ) === -1 ) {
				return;
			}

			if ( $( '.wpforms-confirmation-container-full' ).length === 0 ) {
				return;
			}

			app.removeQueryParameter();
		},

		/**
		 * Remove 'wpforms_resume_entry' query parameter after successful form submitting.
		 *
		 * @since 1.8.0
		 */
		removeQueryParameter() {
			const newUrl = new URL( location.href ); // eslint-disable-line compat/compat

			newUrl.searchParams.delete( 'wpforms_resume_entry' );
			window.history.replaceState( {}, document.title, newUrl.href );
		},
	};

	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsSaveResumeConfirmation.init();
