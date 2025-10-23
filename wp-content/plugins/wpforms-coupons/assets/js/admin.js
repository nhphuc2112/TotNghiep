/* global wpforms_admin, wpforms_coupon_admin */

'use strict';

/**
 * WPForms Coupons Admin function.
 *
 * @since 1.0.0
 */
const WPFormsCouponsAdmin = window.WPFormsCouponsAdmin || ( function( document, window, $ ) {

	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {object}
	 */
	const app = {

		/**
		 * Elements.
		 *
		 * @since 1.0.0
		 *
		 * @type {object}
		 */
		el: {
			holder: $( '#wpforms-coupons' ),
			form: $( 'form.wpforms-admin-settings-form' ),
			formsToggles: $( '.wpforms-coupon-allowed-forms tbody .wpforms-coupon-toggle-control input' ),
			isGlobalToggle: $( '#wpforms-coupons-is_global' ),
			tooltips: $( '.wpforms-coupons-tooltip' ),
		},

		/**
		 * Runtime vars.
		 *
		 * @since 1.0.0
		 *
		 * @type {object}
		 */
		vars: {},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			app.events();
			app.initSavedFormData();
			app.dateTimePicker.init();
			app.tooltips.init();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.0.0
		 */
		events: function() {

			app.el.holder
				.on( 'click', '.wpforms-btn-coupon-generate', app.generateCouponCode )
				.on( 'click', '#wpforms-coupons-is_global', app.toggleAllowedFormsGlobal )
				.on( 'click', '.wpforms-coupons-show-all-forms', app.showAllForms )
				.on( 'click', '.wpforms-coupon-allowed-forms tbody .wpforms-coupon-toggle-control input', app.toggleSingleForm )
				.on( 'submit', 'form.wpforms-admin-settings-form', app.submitForm )
				.on( 'change', '#wpforms-coupons-amount-type', app.changeStep );

			// Prevent losing not saved changes.
			$( window ).on( 'beforeunload', app.beforeUnload );
		},

		/**
		 * Store form data for further comparison.
		 *
		 * @since 1.0.0
		 */
		initSavedFormData: function() {

			app.vars.savedFormData     = app.el.form.serialize();
			app.vars.isReloadConfirmed = false;
		},

		/**
		 * Date Time Picker related logic.
		 *
		 * @since 1.0.0
		 */
		dateTimePicker: {

			/**
			 * Elements.
			 *
			 * @since 1.0.0
			 *
			 * @type {object}
			 */
			el: {
				wrapper: $( '#wpforms-coupon-datetime-block' ),
				$clearButton: $( '.wpforms-clear-datetime-field' ),
				scheduling: {
					start: {
						$dpicker: $( '#wpforms-coupons-start_date' ),
						$tpicker: $( '#wpforms-coupons-start_time' ),
					},
					end: {
						$dpicker: $( '#wpforms-coupons-end_date' ),
						$tpicker: $( '#wpforms-coupons-end_time' ),
					},
				},
			},

			/**
			 * Init datetime pickers.
			 *
			 * @since 1.0.0
			 */
			init: function() {

				if ( ! app.dateTimePicker.el.wrapper.length ) {
					return;
				}

				app.dateTimePicker.datePickerInit();
				app.dateTimePicker.timePickerInit();
				app.dateTimePicker.datePairInit();
				app.dateTimePicker.clearButtonsInit();
				app.dateTimePicker.updatePairedField();
				app.dateTimePicker.events();
			},

			/**
			 * Register JS events.
			 *
			 * @since 1.0.0
			 */
			events: function() {

				$( document )
					.on( 'click', '.wpforms-clear-datetime-field', app.dateTimePicker.resetField )

					// This is needed for handling wrong datetime range situations.
					.on( 'change', 'input', '#wpforms-coupon-datetime-block', app.dateTimePicker.updatePairedField );
			},

			/**
			 * Initialize date picker.
			 *
			 * @since 1.0.0
			 */
			datePickerInit: function() {

				if ( 'undefined' === typeof $.fn.flatpickr ) {
					return;
				}

				app.dateTimePicker.el.scheduling.start.$dpicker.flatpickr( app.dateTimePicker.getArgs( 'start' ) );
				app.dateTimePicker.el.scheduling.end.$dpicker.flatpickr( app.dateTimePicker.getArgs( 'end' ) );
			},

			/**
			 * Initialize time picker.
			 *
			 * @since 1.0.0
			 */
			timePickerInit: function() {

				if ( 'undefined' === typeof $.fn.timepicker ) {
					return;
				}

				const args = {
					appendTo: app.el.holder,
					disableTextInput: true,
					timeFormat: wpforms_coupon_admin.time_format,
				};

				app.dateTimePicker.el.scheduling.start.$tpicker.timepicker( args ).on( 'selectTime', app.dateTimePicker.clearButtonsRefresh ).on( 'showTimepicker', app.dateTimePicker.showTimepicker ).on( 'hideTimepicker', app.dateTimePicker.hideTimepicker );
				app.dateTimePicker.el.scheduling.end.$tpicker.timepicker( args ).on( 'selectTime', app.dateTimePicker.clearButtonsRefresh ).on( 'showTimepicker', app.dateTimePicker.showTimepicker ).on( 'hideTimepicker', app.dateTimePicker.hideTimepicker );
			},

			/**
			 * Initialize date pair.
			 *
			 * @since 1.0.0
			 */
			datePairInit: function() {

				const args = {
					anchor: null,
					defaultDateDelta: 0,
					dateClass: 'wpforms-datepair-date',
					timeClass: 'wpforms-datepair-time',
					startClass: 'wpforms-datepair-start',
					endClass: 'wpforms-datepair-end',
					parseDate: function( input ) {

						return $( input ).prop( '_flatpickr' ).selectedDates[0];
					},
					updateDate: function( input, dateObj ) {

						const $input = $( input );

						$input.prop( '_flatpickr' ).setDate( dateObj );
						app.dateTimePicker.clearButtonsRefresh( $input );
						$input.trigger( 'change' );
					},
					updateTime: function( input, dateObj ) {

						const $input = $( input );

						$input.timepicker( 'setTime', dateObj );
						app.dateTimePicker.clearButtonsRefresh( $input );
						$input.trigger( 'change' );
					},
				};

				app.dateTimePicker.el.wrapper.datepair( args );
			},

			/**
			 * Init delete/time field clear buttons.
			 *
			 * @since 1.0.0
			 */
			clearButtonsInit: function() {

				app.dateTimePicker.el.$clearButton
					.each( function() {

						const $t = $( this );

						if ( $t.siblings( '[id^="wpforms-coupons-"]' ).val() === '' ) {
							$t.hide();
						}
					} );
			},

			/**
			 * Refresh a clear button for an element.
			 *
			 * @since 1.0.0
			 *
			 * @param {jQuery|Event} $el Element to refresh a clear button for.
			 */
			clearButtonsRefresh: function( $el ) {

				// Extract the element if an event was passed.
				// Useful when used as a callback.
				if ( $el.target ) {
					$el = $( $el.target );
				}

				if ( ! $el ) {
					return;
				}

				if ( $el.val() !== '' ) {
					$el.nextAll( 'button.wpforms-clear-datetime-field' ).show();
				}
			},

			/**
			 * Add class to input when time picker is shown from the top.
			 *
			 * @since 1.0.0
			 */
			showTimepicker: function() {

				$( '.ui-timepicker-wrapper' ).addClass( 'ui-timepicker-wrapper-focus' );
			},

			/**
			 * Hide time picker.
			 *
			 * @since 1.0.0
			 */
			hideTimepicker: function() {

				$( '.ui-timepicker-wrapper' ).removeClass( 'ui-timepicker-wrapper-focus' );
			},

			/**
			 * Get arguments for date picker start/end date fields.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} field Field type (e.g 'start' or 'end').
			 *
			 * @returns {object} Date picker arguments.
			 */
			getArgs: function( field ) {

				field = field === 'start' ? 'start' : 'end';

				const $tpicker = app.dateTimePicker.el.scheduling[field].$tpicker;
				const $dpicker = app.dateTimePicker.el.scheduling[field].$dpicker;

				return {
					altInput  : true,
					altFormat : wpforms_coupon_admin.date_format,
					dateFormat: wpforms_coupon_admin.date_format,
					onChange: function( date ) {

						const second = {
							field : field === 'start' ? 'end' : 'start',
						};

						second.$dpicker = app.dateTimePicker.el.scheduling[second.field].$dpicker;
						second.$tpicker = app.dateTimePicker.el.scheduling[second.field].$tpicker;

						let firstInit = second.$dpicker.val() === '' && second.$tpicker.val() === '' && $dpicker.val() !== '';

						if ( firstInit === true && ( $dpicker.val() !== '' && $tpicker.val() === '' && second.$tpicker.val() === '' ) || ( second.$dpicker.val() !== '' && second.$tpicker.val() === '' && $tpicker.val() === '' ) ) {
							$tpicker.timepicker( 'setTime', '00:00' );
							second.$tpicker.timepicker( 'setTime', '00:00' );
							app.dateTimePicker.clearButtonsRefresh( $tpicker );
							app.dateTimePicker.clearButtonsRefresh( second.$tpicker );
						}

						// Set the start date picker to today if it's empty.
						if ( firstInit === true && field === 'end' && second.$dpicker.val() === '' && second.$dpicker.val() === '' ) {
							second.$dpicker.prop( '_flatpickr' ).setDate( new Date() );
							app.dateTimePicker.clearButtonsRefresh( second.$dpicker );
						}

						app.dateTimePicker.clearButtonsRefresh( $dpicker );
					},
				};
			},

			/**
			 * Update paired datetime field.
			 *
			 * @since 1.0.0
			 */
			updatePairedField: function() {

				const start = {
					date: app.dateTimePicker.el.scheduling.start.$dpicker.val(),
					time: app.dateTimePicker.el.scheduling.start.$tpicker.val(),
				};
				const end   = {
					date: app.dateTimePicker.el.scheduling.end.$dpicker.val(),
					time: app.dateTimePicker.el.scheduling.end.$tpicker.val(),
				};

				if ( start.date === '' || end.date === '' ) {
					return;
				}

				const sdateTime = ( start.date + ' ' + start.time ).replace( /-/g, '/' );
				const edateTime = ( end.date + ' ' + end.time ).replace( /-/g, '/' );
				const sdateObj  = new Date( sdateTime );
				const edateObj  = new Date( edateTime );

				if ( edateObj.getTime() > sdateObj.getTime() ) {
					return;
				}
			},

			/**
			 * Clear datetime field.
			 *
			 * @since 1.0.0
			 */
			resetField: function() {

				const $input                     = $( this ).siblings( 'input' );
				const $siblingTimepickerInput    = $( this ).parent().next().find( 'input' );
				const $siblingTimepickerResetBtn = $( this ).parent().next().find( '.wpforms-clear-datetime-field' );

				if ( $input.prop( '_flatpickr' ) ) {
					$input.prop( '_flatpickr' ).clear();
				} else {
					$input.val( '' );
				}

				$siblingTimepickerInput.val( '' );

				$( this ).hide();
				$siblingTimepickerResetBtn.hide();
			},
		},

		/**
		 * Generate coupon code.
		 *
		 * @since 1.0.0
		 */
		generateCouponCode: function() {

			const $btn = $( '.wpforms-btn-coupon-generate' );
			const $codeInput = $( '#wpforms-coupon-code' );
			const buttonHTML  = $btn.html();
			const buttonWidth = $btn.outerWidth();

			$.ajax(
				{
					url: wpforms_admin.ajax_url,
					type: 'GET',
					data: {
						'nonce': wpforms_admin.nonce,
						'action': 'wpforms_coupons_generate_coupon_code',
					},
					dataType: 'json',
					beforeSend: function() {

						$btn.css( 'width', buttonWidth ).text( wpforms_coupon_admin.generating ).prop( 'disabled', true );
					},
					success: function( response ) {

						$codeInput.val( '' ).val( response.data );
					},
					error: function( data ) {

						console.log( data );
					},
					complete: function() {

						$btn.css( 'width', '' ).html( buttonHTML ).prop( 'disabled', false );
					},
				},
			);
		},

		/**
		 * Toggle global allowed forms input.
		 *
		 * @since 1.0.0
		 */
		toggleAllowedFormsGlobal: function() {

			app.el.formsToggles.prop( 'checked', $( this ).is( ':checked' ) );
		},

		/**
		 * Toggle single form input.
		 *
		 * @since 1.0.0
		 */
		toggleSingleForm: function() {

			if ( ! $( this ).is( ':checked' ) ) {
				app.el.isGlobalToggle.prop( 'checked', false );
			}
		},

		/**
		 * Show all hidden forms in allowed forms table.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} e Event object.
		 */
		showAllForms: function( e ) {

			e.preventDefault();

			const $this           = $( this );
			const $allHiddenForms = $this.parent().find( 'table.wpforms-table-list' ).find( 'tr.wpforms-coupons-hidden' );

			$allHiddenForms.removeClass( 'wpforms-coupons-hidden' );
			$this.hide();
		},

		/**
		 * Tooltips functionality.
		 *
		 * @since 1.0.0
		 */
		tooltips: {

			/**
			 * Init tooltips.
			 *
			 * @since 1.0.0
			 */
			init: function() {

				if ( ! app.el.tooltips.length ) {
					return;
				}

				app.el.tooltips.tooltipster( {
					contentAsHTML: true,
					position: 'top',
					maxWidth: 300,
					multiple: true,
					interactive: true,
					debug: false,
					functionReady( instance ) {

						$( instance.elementTooltip() ).addClass( 'wpforms-tooltipster-coupons' );
					},
				} );
			},
		},

		/**
		 * Exit form builder.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} event Event object.
		 *
		 * @returns {string|undefined} Confirmation message.
		 */
		beforeUnload: function( event ) {

			if ( app.el.form.serialize() === app.vars.savedFormData  ) {
				return;
			}

			if ( app.vars.isReloadConfirmed ) {
				return;
			}

			event.returnValue = wpforms_coupon_admin.leave_page;

			return event.returnValue;
		},

		/**
		 * Submit form.
		 *
		 * @since 1.0.0
		 */
		submitForm: function() {

			app.vars.isReloadConfirmed = true;
		},

		/**
		 * Change amount step to control decimals for the different currencies.
		 *
		 * @since 1.0.0
		 */
		changeStep: function() {

			const step = $( this ).val() === 'flat' ?
				1 / Math.pow( 10, wpforms_coupon_admin.decimals ) :
				'0.01';

			$( '#wpforms-coupon-amount' ).attr( 'step', step );
		},
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

// Initialize.
WPFormsCouponsAdmin.init();
