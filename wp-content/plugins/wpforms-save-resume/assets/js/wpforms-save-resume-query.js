/**
 * WPForms Save and Resume query related JS for non-form pages.
 *
 * @since 1.8.0
 */
const WPFormsSaveResumeQuery = window.WPFormsSaveResumeQuery || ( function( document, window, $ ) {
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
			$( document )
				.on( 'ready', app.cleanUrlParams );
		},

		/**
		 * Clean URL params.
		 *
		 * @since 1.8.0
		 */
		cleanUrlParams() {
			app.emailIsSent();
			app.entryIsExpired();
		},

		/**
		 * Remove $_GET['wpforms_sr_email_is_sent'] from the URL on successfully email sent.
		 *
		 * @since 1.8.0
		 */
		emailIsSent() {
			app.removeQueryParameter( 'wpforms_sr_email_is_sent' );
		},

		/**
		 * Remove $_GET['wpforms_sr_entry_is_completed'] from the URL on successfully entry completed.
		 *
		 * @since 1.8.0
		 */
		entryIsExpired() {
			app.removeQueryParameter( 'wpforms_sr_entry_is_completed' );

			// Remove $_GET['wpforms_resume_entry'] from the form action when user use the expired link.
			if ( $( '.wpforms-save-resume-expired-message' ).length > 0 ) {
				app.removeQueryParameter( 'wpforms_resume_entry' );
				$( 'form.wpforms-form' ).each( function() {
					$( this ).attr( 'action', window.location.pathname );
				} );
			}
		},

		/**
		 * Remove query parameter from URL.
		 *
		 * @since 1.8.0
		 *
		 * @param {string} patameterName Parameter name.
		 */
		removeQueryParameter( patameterName ) {
			if ( window.location.search.indexOf( patameterName ) === -1 ) {
				return;
			}

			// eslint-disable-next-line compat/compat
			const newUrl = new URL( location.href );
			newUrl.searchParams.delete( patameterName );
			window.history.replaceState( {}, document.title, newUrl.href );
		},
	};

	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsSaveResumeQuery.init();
