/* global wpforms_builder, wpf */

'use strict';

/**
 * WPForms Save and Resume builder function.
 *
 * @since 1.0.0
 */
var WPFormsBuilderSaveResume = window.WPFormsBuilderSaveResume || ( function( document, window, $ ) {

	var app = {

		showPopup: 0,

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );

			// Page load.
			$( window ).on( 'load', function() {

				// In the case of jQuery 3.+, we need to wait for a ready event first.
				if ( typeof $.ready.then === 'function' ) {
					$.ready.then( app.load );
				} else {
					app.load();
				}
			} );
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.bindUIActions();
			app.panelToggle();
			app.disclaimerToggle();
			app.notificationToggle();
		},

		/**
		 * Page load.
		 *
		 * @since 1.4.0
		 */
		load: function() {

			app.toggleResumeLink();
		},

		/**
		 * Element bindings.
		 *
		 * @since 1.0.0
		 */
		bindUIActions: function() {

			// Cache builder element.
			var $builder = $( '#wpforms-builder' );

			// Don't allow users to enable the addon if entries storage is disabled.
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_enable', app.showDisabledNotification );
			$builder.on( 'change', '#wpforms-panel-field-settings-disable_entries', app.disableAddon );

			// Toggle hide message
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_enable', app.panelToggle );
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_disclaimer_enable[type="checkbox"]', app.disclaimerToggle );
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_enable_resume_link, #wpforms-panel-field-settings-save_resume_enable_email_notification', app.requiredFieldsCheck );
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_enable_email_notification[type="checkbox"]', app.notificationToggle );
			$builder.on( 'wpformsSaved', app.requiredFieldsCheck );
			$builder.on( 'wpformsSaved', app.showRequiredFieldsPopup );
			$builder.on( 'click', '#wpforms-preview-btn', app.requiredFieldsCheck );

			// Show/hide Save and Resume Later link in builder preview area.
			$builder.on( 'change', '#wpforms-panel-field-settings-save_resume_enable', app.toggleResumeLink );
			$builder.on( 'wpformsFieldAdd wpformsFieldDelete', app.toggleResumeLink );
			$builder.on( 'wpformsFieldAdd', app.addResumeLink );

			// Update link text in the fields preview.
			$builder.on( 'input', '#wpforms-panel-field-settings-save_resume_link_text', app.updateResumeLinks );
		},

		/**
		 * Check if addon is enable.
		 *
		 * @since 1.0.0
		 *
		 * @returns {boolean} Save and Resume is enabled.
		 */
		isSaveResumeEnabled: function() {

			return $( '#wpforms-panel-field-settings-save_resume_enable' ).is( ':checked' );
		},

		/**
		 * Toggle addon's visibility on switching the addon on/off.
		 *
		 * @since 1.0.0
		 */
		panelToggle: function( ) {

			var $block = $( '.wpforms-save-resume-sub-panel' );

			if ( app.isSaveResumeEnabled() ) {
				$block.show();
			} else {
				$block.hide();
			}
		},

		/**
		 * Show/hide Disclaimer related fields on enable switch.
		 *
		 * @since 1.0.0
		 */
		disclaimerToggle: function( ) {

			var $hide = $( '#wpforms-panel-field-settings-save_resume_disclaimer_enable[type="checkbox"]' ),
				$block = $( '#wpforms-panel-field-settings-save_resume_disclaimer_message-wrap' );

			if ( ! $hide.length ) {
				return;
			}

			$hide.is( ':checked' ) ? $block.show() : $block.hide();
		},

		/**
		 * Show/hide Notification related fields on enable switch.
		 *
		 * @since 1.0.0
		 */
		notificationToggle: function( ) {

			const $hide = $( '#wpforms-panel-field-settings-save_resume_enable_email_notification[type="checkbox"]' );

			if ( ! $hide.length ) {
				return;
			}

			const $block = $( '.wpforms-save-resume-email-settings' );

			$hide.is( ':checked' ) ? $block.show() : $block.hide();
		},

		/**
		 * Check if Enable Resume Link or/and Enable Email Notification are switched.
		 * Otherwise, prevent builder saving and show warning popup.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} e Event object.
		 */
		requiredFieldsCheck: function( e ) {

			if ( ! app.isSaveResumeEnabled() ) {
				return;
			}

			if (
				$( '#wpforms-panel-field-settings-save_resume_enable_resume_link[type="checkbox"]' ).is( ':checked' ) ||
				$( '#wpforms-panel-field-settings-save_resume_enable_email_notification[type="checkbox"]' ).is( ':checked' )
			) {
				return;
			}

			$( this ).prop( 'checked', ! $( this ).prop( 'checked' ) );

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.save_resume_email_link_settings_required,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * Check Disclaimer Message is not empty.
		 *
		 * @since 1.0.0
		 */
		requiredDisclaimerMsg: function( ) {

			if ( ! app.isSaveResumeEnabled() ) {
				return;
			}

			if ( ! $( '#wpforms-panel-field-settings-save_resume_disclaimer_enable[type="checkbox"]' ).is( ':checked' ) ) {
				return;
			}

			if ( $( '#wpforms_panel_field_settings_save_resume_disclaimer_message' ).val().trim().length > 0 ) {
				return;
			}

			app.showPopup += 1;
		},

		/**
		 * Check Email Notification required fields.
		 *
		 * @since 1.0.0
		 */
		requiredNotificationMsg: function() {

			if ( ! app.isSaveResumeEnabled() ) {
				return;
			}

			if ( ! $( '#wpforms-panel-field-settings-save_resume_enable_email_notification[type="checkbox"]' ).is( ':checked' ) ) {
				return;
			}


			if ( $( '#wpforms-panel-field-settings-save_resume_email_notification_message' ).val().trim().length > 0 && $( '#wpforms_panel_field_settings_save_resume_email_settings_message' ).val().trim().length > 0 ) {
				return;
			}

			app.showPopup += 1;
		},

		/**
		 * Check Confirmation message required message.
		 *
		 * @since 1.0.0
		 */
		requiredConfirmationMsg: function() {

			if ( ! app.isSaveResumeEnabled() ) {
				return;
			}

			if ( $( '#wpforms_panel_field_settings_save_resume_confirmation_message' ).val().trim().length > 0 ) {
				return;
			}

			if ( $( '#wpforms_panel_field_settings_save_resume_disclaimer_message' ).val().trim().length > 0 ) {
				return;
			}

			app.showPopup += 1;
		},

		/**
		 * Show warning popup with message.
		 */
		showRequiredFieldsPopup: function() {

			app.requiredDisclaimerMsg();
			app.requiredNotificationMsg();
			app.requiredConfirmationMsg();

			if ( app.showPopup < 1 ) {
				return;
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.save_resume_required_text_fields,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );

			app.showPopup = false;
		},

		/**
		 * Prevent enabling the addon functionality if entries storage is disabled.
		 *
		 * @since 1.0.0
		 */
		showDisabledNotification: function() {

			var $this = $( this );
			if ( ! $this.prop( 'checked' ) ) {
				return;
			}

			if ( ! $( '#wpforms-panel-field-settings-disable_entries' ).prop( 'checked' ) ) {
				return;
			}

			$.confirm( {
				title: wpforms_builder.heads_up,
				content: wpforms_builder.save_resume_disabled_entry_storage,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );

			$this.prop( 'checked', false );
		},

		/**
		 * Disable addon functionality if entries storage is disabled.
		 *
		 * @since 1.0.0
		 */
		disableAddon: function() {

			var $this = $( this ),
				$addonEnable = $( '#wpforms-panel-field-settings-save_resume_enable' );

			if ( $this.prop( 'checked' ) && $addonEnable.prop( 'checked' ) ) {
				$addonEnable.prop( 'checked', false );
				app.panelToggle();
			}
		},

		/**
		 * Show/hide Save and Resume Later button in builder preview area.
		 *
		 * @since 1.1.0
		 */
		toggleResumeLink() {
			const $block = $( '.wpforms-preview .wpforms-save-resume-button' );

			if ( app.isSaveResumeEnabled() && wpf.getFields() ) {
				$block.removeClass( 'wpforms-hidden' );

				return;
			}

			$block.addClass( 'wpforms-hidden' );
		},

		/**
		 * Update Save and Resume Later link when a new field is added.
		 *
		 * @since 1.4.0
		 *
		 * @param {Event}  e    Event.
		 * @param {number} id   Field ID.
		 * @param {string} type Field type.
		 */
		addResumeLink( e, id, type ) {
			if ( type !== 'pagebreak' ) {
				return;
			}

			const $fieldPreview = $( '#wpforms-field-' + id );
			const $saveResumeButton = $fieldPreview.find( '.wpforms-save-resume-button' );

			if ( ! $saveResumeButton.length ) {
				return;
			}

			if ( ! app.isSaveResumeEnabled() || ! wpf.getFields() ) {
				$saveResumeButton.addClass( 'wpforms-hidden' );
			}

			app.updateResumeLink( $saveResumeButton );
		},

		/**
		 * Update Save and Resume Later link text.
		 *
		 * @since 1.4.0
		 *
		 * @param {jQuery} $saveResumeButton Save and resume button element.
		 */
		updateResumeLink: function( $saveResumeButton ) {

			const val = $( '#wpforms-panel-field-settings-save_resume_link_text' ).val().trim();
			const linkText = val.length ? val : wpforms_builder.save_resume_default_link_text;

			$saveResumeButton.find( 'span' ).text( linkText );
		},

		/**
		 * Update text for all Save and Resume Later links.
		 *
		 * @since 1.4.0
		 */
		updateResumeLinks: function() {

			$( '.wpforms-preview' ).find( '.wpforms-save-resume-button' ).each( function() {

				app.updateResumeLink( $( this ) );
			} );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsBuilderSaveResume.init();
