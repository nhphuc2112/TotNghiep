/* global wpforms_admin */

/**
 * @param wpforms_admin.google_drive.auth_url
 * @param wpforms_admin.google_drive.delete_local_files_confirmation_message
 * @param wpforms_admin.google_drive.is_file_deletion_enabled
 * @param wpforms_admin.google_drive.upload_delete_confirm_button
 */

/**
 * WPForms Google Drive admin module.
 *
 * @since 1.0.0
 */
const WPFormsGoogleDriveAdmin = window.WPFormsGoogleDriveAdmin || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			$( app.ready );
		},

		/**
		 * Initialized once the DOM is fully loaded.
		 *
		 * @since 1.0.0
		 */
		ready() {
			$( '#wpforms-integration-google-drive' )
				.on( 'click', '#wpforms-google-drive-sign-in .gsi-material-button', app.authorize );

			$( '.wpforms-entry-google_drive_reupload a' )
				.on( 'click', app.reupload );
		},

		/**
		 * Redirect to Google Drive auth URL.
		 *
		 * @param {Event} e Event.
		 */
		authorize( e ) {
			e.preventDefault();

			window.location.href = wpforms_admin.google_drive.auth_url;
		},

		/**
		 * Handle reupload action for Google Drive uploads.
		 *
		 * @since 1.1.0
		 *
		 * @param {Event} e Event.
		 */
		reupload( e ) {
			const $this = $( this );
			const lockClass = 'wpforms-google-drive-in-progress';

			if ( $this.hasClass( lockClass ) ) {
				e.preventDefault();

				return;
			}

			$this.addClass( lockClass );

			if ( ! wpforms_admin.google_drive.is_file_deletion_enabled ) {
				return;
			}

			e.preventDefault();

			$.confirm( {
				title: wpforms_admin.heads_up,
				content: wpforms_admin.google_drive.delete_local_files_confirmation_message,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_admin.google_drive.upload_delete_confirm_button,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action() {
							window.location.href = $this.attr( 'href' );
						},
					},
					cancel: {
						text: wpforms_admin.cancel,
						btnClass: 'btn-cancel',
					},
				},
			} );
		},
	};

	return app;
}( document, window, jQuery ) );

// Initialize.
WPFormsGoogleDriveAdmin.init();
