/* global wpforms, wpforms_settings, WPFormsPaypalCommerce */

/**
 * WPForms Coupons Frontend module.
 *
 * @since 1.0.0
 */
const WPFormsCoupons = window.WPFormsCoupons || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Cache coupons values to prevent multiple requests.
		 *
		 * @since 1.1.0
		 */
		cache: {},

		/**
		 * CSS selectors.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		selectors: {
			applyButton: '.wpforms-field-payment-coupon-button',
			couponInput: '.wpforms-field-payment-coupon-input',
			removeCouponButton: '.wpforms-field-payment-coupon-applied-item-remove',
			couponRow: '.wpforms-field-payment-coupon-wrapper',
			appliedCouponsWrapper: '.wpforms-field-payment-coupon-applied-coupons',
			appliedCouponItem: '.wpforms-field-payment-coupon-applied-item',
			field: '.wpforms-field',
			form: '.wpforms-form',
			errorMessage: 'em.wpforms-error, label.wpforms-error',
			errorField: '.wpforms-error',
			payPalCommerceNotice: '.wpforms-coupons-paypal-commerce-notice',
		},

		/**
		 * CSS class names
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		classes: {
			hasAppliedCoupons: 'wpforms-field-payment-coupon-wrapper-applied',
			fieldHasError: 'wpforms-has-error',
			error: 'wpforms-error',
			applyingCoupon: 'wpforms-field-payment-coupon-wrapper-applying',
			payPalCommerceNoticeClass: 'wpforms-coupons-paypal-commerce-notice',
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			$( app.ready );

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
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready() {
			app.registerValidationMethod();
			app.bindEvents();
		},

		/**
		 * Bind events.
		 *
		 * @since 1.1.0
		 */
		bindEvents() {
			$( document )
				.on( 'click', app.selectors.applyButton, app.applyButtonClick )
				.on( 'keydown', app.selectors.couponInput, app.inputPressEnter )
				.on( 'click', app.selectors.removeCouponButton, app.removeCoupon )
				.on( 'wpformsAmountTotalCalculate', app.recalculateTotal )
				.on( 'input', app.selectors.couponInput, app.resetField );
		},

		/**
		 * Register validation method for coupon fields.
		 *
		 * @since 1.1.0
		 */
		registerValidationMethod() {
			if ( typeof $.fn.validate === 'undefined' ) {
				return;
			}

			$.validator.addMethod( 'coupon', function( value, element ) {
				const $el = $( element );

				if ( ! $el.val().length ) {
					return true;
				}

				const $form = $el.closest( 'form' );
				const formId = $form.data( 'formid' );

				if (
					! Object.prototype.hasOwnProperty.call( app.cache, formId ) ||
					! Object.prototype.hasOwnProperty.call( app.cache[ formId ], value )
				) {
					app.showDebugMessage( 'Send coupon validation request' );
					app.applyCouponRequest( element, value );

					return 'pending';
				}

				app.showDebugMessage( 'Validation coupon from cache' );

				const cache = app.cache[ formId ][ value ];

				if ( typeof cache !== 'object' ) {
					app.showDebugMessage( 'The ' + value + ' coupon is invalid' );

					return false;
				}

				const $field = $el.closest( app.selectors.field );

				app.applyCouponSuccess( $field, cache.formatted_code, cache.value, cache.type );

				app.showDebugMessage( 'Coupon successfully applied' );
				app.showDebugMessage( cache );

				return true;
			}, wpforms_settings.val_invalid_coupon );
		},

		/**
		 * Send coupon validation request.
		 *
		 * @since 1.1.0
		 *
		 * @param {Element} element Coupon input field.
		 * @param {string}  value   Field value.
		 */
		applyCouponRequest( element, value ) {
			const $el = $( element );
			const $form = $el.closest( 'form' );
			const validator = $form.data( 'validator' );
			const formId = $form.data( 'formid' );
			const $field = $el.closest( app.selectors.field );
			const $couponRow = $field.find( app.selectors.couponRow );

			app.cache[ formId ] = app.cache[ formId ] || {};

			validator.startRequest( element );

			$.ajax( {
				type: 'POST',
				url: wpforms_settings.ajaxurl,
				mode: 'abort',
				port: 'validate' + element.name,
				data: {
					action: 'wpforms_coupons_apply_coupon',
					// eslint-disable-next-line camelcase
					form_id: formId,
					// eslint-disable-next-line camelcase
					coupon_code: value,
				},
				dataType: 'json',
				async: false,
				beforeSend() {
					$couponRow.addClass( app.classes.applyingCoupon );
				},
				success( res ) {
					validator.stopRequest( element, true );
					app.cache[ formId ][ value ] = res.data;

					app.applyCouponSuccess( $field, res.data.formatted_code, res.data.value, res.data.type );

					app.showDebugMessage( 'Coupon successfully applied' );
					app.showDebugMessage( res.data );
				},
				error() {
					validator.stopRequest( element, false );
					app.showError( $field, wpforms_settings.val_invalid_coupon );
					app.cache[ formId ][ value ] = false;

					app.showDebugMessage( 'The ' + value + ' coupon is invalid' );
				},
				complete() {
					$couponRow.removeClass( app.classes.applyingCoupon );
				},
			} );
		},

		/**
		 * Page load.
		 *
		 * @since 1.0.0
		 */
		load() {
			$( app.selectors.couponInput ).each( function() {
				const $couponInput = $( this );

				if ( ! app.isEmptyField( $couponInput ) ) {
					$couponInput.trigger( 'focusout' );
				}
			} );
		},

		/**
		 * Is the field empty?
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $field Field element.
		 *
		 * @return {boolean} True if the field is empty, false otherwise.
		 */
		isEmptyField( $field ) {
			return ! $field.val().trim().length > 0;
		},

		/**
		 * Apply coupon on Enter press.
		 *
		 * @since 1.0.0
		 *
		 * @param {Event} e Event.
		 */
		inputPressEnter( e ) {
			if ( e.keyCode !== 13 ) {
				return;
			}

			e.preventDefault();

			const $field = $( this ).closest( app.selectors.field );
			const $button = $field.find( app.selectors.applyButton );

			$button.trigger( 'focus' );
		},

		/**
		 * Apply coupon on button click.
		 *
		 * @since 1.0.0
		 *
		 * @param {Event} e Event.
		 */
		applyButtonClick( e ) {
			e.preventDefault();
		},

		/**
		 * Apply coupon success ajax callback.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $field     Field.
		 * @param {string} couponCode Coupon code.
		 * @param {number} value      Coupon value.
		 * @param {string} type       Flat or percentage.
		 */
		applyCouponSuccess( $field, couponCode, value, type ) {
			const $appliedCouponsWrapper = $field.find( app.selectors.appliedCouponsWrapper );
			const $couponInput = $field.find( app.selectors.couponInput );
			const $button = $field.find( app.selectors.applyButton );
			const $couponRow = $field.find( app.selectors.couponRow );
			const $form = $field.closest( app.selectors.form );

			$field.removeClass( app.classes.fieldHasError );
			$field.find( app.selectors.errorMessage ).remove();
			$field.find( app.selectors.errorField ).removeClass( app.classes.error );

			$couponInput.prop( 'disabled', true ).prop( 'required', false );
			$button.prop( 'disabled', true );
			$couponRow.addClass( app.classes.hasAppliedCoupons );

			const formattedValue = app.getFormattedValue( value, type );

			$appliedCouponsWrapper.html(
				'<div class="wpforms-field-payment-coupon-applied-item" data-value="' + value + '" data-type="' + type + '">' +
					'<button type="button" aria-live="assertive" aria-label="' + wpforms_settings.remove_coupon_icon_text + '" class="wpforms-field-payment-coupon-applied-item-remove">' +
						'<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"><path fill="#D63637" d="M7 .469A6.78 6.78 0 0 0 .219 7.25 6.78 6.78 0 0 0 7 14.031a6.78 6.78 0 0 0 6.781-6.781A6.78 6.78 0 0 0 7 .469Zm3.309 8.586c.136.11.136.328 0 .465l-1.067 1.066c-.137.137-.355.137-.465 0L7 8.78l-1.805 1.805c-.11.137-.328.137-.465 0L3.664 9.492c-.137-.11-.137-.328 0-.465L5.47 7.25 3.664 5.473c-.137-.11-.137-.328 0-.465L4.758 3.94c.11-.136.328-.136.465 0L7 5.72 8.777 3.94c.11-.136.328-.136.465 0l1.067 1.067c.136.137.136.355 0 .465L8.53 7.25l1.778 1.805Z"/></svg>' +
					'</button>' +
					'<span class="wpforms-field-payment-coupon-applied-item-code">' + couponCode + ' (-' + formattedValue + ')</span>' +
					'<input type="hidden" class="wpforms-field-skip-validation" name="' + $couponInput.attr( 'name' ) + '" value="' + couponCode + '"/>' +
				'</div>'
			);

			wpforms.amountTotal( $form, false );

			// Trigger updating total amount.
			$form.find( '.wpforms-payment-total' ).trigger( 'input' );
		},

		/**
		 * Update coupon data in the summary table.
		 *
		 * @since 1.2.0
		 *
		 * @param {jQuery} $form    Form element.
		 * @param {number} value    Coupon value.
		 * @param {string} type     Coupon type.
		 * @param {string} code     Coupon code.
		 * @param {number} total    Total amount.
		 * @param {number} discount Discount amount.
		 */
		updateOrderSummary( $form, value, type, code, total, discount ) {
			$form.find( '.wpforms-order-summary-preview' ).each( function() {
				const $this = $( this ),
					$subtotal = $this.find( '.wpforms-order-summary-preview-subtotal' ),
					$coupon = $this.find( '.wpforms-order-summary-preview-coupon-total' ),
					couponName = wpforms_settings.summary_coupon_name.replace( /%name%/g, code );

				let formattedValue = app.getFormattedValue( value, type );

				if ( type === 'percentage' ) {
					formattedValue = `${ formattedValue } (${ wpforms.amountFormatSymbol( discount ) })`;
				}

				$subtotal.show();
				$coupon.show();

				$subtotal.find( '.wpforms-order-summary-item-price' ).text( wpforms.amountFormatSymbol( total ) );
				$coupon.find( '.wpforms-order-summary-item-label' ).text( couponName );
				$coupon.find( '.wpforms-order-summary-item-price' ).text( `-${ formattedValue }` );

				// Update price column width.
				const priceColumnWidth = Math.max( formattedValue.length + 2, $this.find( '.wpforms-order-summary-preview-total .wpforms-order-summary-item-price' ).text().length + 3 );

				$this.find( '.wpforms-order-summary-item-price' ).css( 'width', `${ priceColumnWidth }ch` );
			} );
		},

		/**
		 * Formatting coupon value.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} value Coupon value.
		 * @param {string} type  Coupon type.
		 *
		 * @return {string} Formatted coupon amount.
		 */
		getFormattedValue( value, type ) {
			if ( type === 'percentage' ) {
				return value + '%';
			}

			value = wpforms.amountFormat( value );

			const currency = wpforms.getCurrency();

			if ( 'left' === currency.symbol_pos ) {
				return currency.symbol + value;
			}

			return value + ' ' + currency.symbol;
		},

		/**
		 * Update total amount when coupons are applied.
		 *
		 * @since 1.0.0
		 *
		 * @param {Event}  event Event.
		 * @param {jQuery} $form Form.
		 * @param {number} total Total amount.
		 *
		 * @return {number} Total amount with applied discount.
		 */
		recalculateTotal( event, $form, total ) {
			let discount = 0;

			$form.find( app.selectors.appliedCouponItem ).each( function() {
				const $this = $( this ),
					value = $this.data( 'value' ),
					type = $this.data( 'type' ),
					code = $this.find( 'input[type="hidden"]' ).val();

				if ( type !== 'percentage' ) {
					discount += value;
				} else {
					discount += ( total / 100 ) * value;
				}

				app.updateOrderSummary( $form, value, type, code, total, discount );
			} );

			const currentTotal = event.result !== undefined ? event.result : total;

			if ( discount === 0 ) {
				return currentTotal;
			}

			const currency = wpforms.getCurrency();
			// Format the discount amount before calculating a total to prevent incorrect rounding for the end form total later.
			// We should always use default `.` decimals separator for correct calculations.
			discount = wpforms.numberFormat( discount, currency.decimals, '.', '' );

			app.showDebugMessage( 'Total was recalculated. Discount: ' + discount );

			return Math.max( 0, Number( currentTotal ) - Number( discount ) );
		},

		/**
		 * Show error message.
		 *
		 * @since 1.0.0
		 *
		 * @param {jQuery} $field       Field.
		 * @param {string} errorMessage Error message.
		 */
		showError( $field, errorMessage ) {
			const $form = $field.closest( app.selectors.form );
			const validator = $form.data( 'validator' );

			if ( ! validator ) {
				return;
			}

			const $input = $field.find( app.selectors.couponInput );
			const fieldName = $input.attr( 'name' );
			const errors = {};

			errors[ fieldName ] = errorMessage;

			// Prevent error hiding when you click to fast on the apply button.
			delete validator.invalid[ fieldName ];
			validator.resetElements( $input );

			validator.showErrors( errors );
		},

		/**
		 * Remove coupon.
		 *
		 * @since 1.0.0
		 */
		removeCoupon() {
			const $field = $( this ).closest( app.selectors.field );
			const $form = $field.closest( app.selectors.form );
			const $couponItem = $field.find( app.selectors.appliedCouponItem );
			const $button = $field.find( app.selectors.applyButton );
			const $input = $field.find( app.selectors.couponInput );
			const couponCode = $input.val();
			const $couponRow = $field.find( app.selectors.couponRow );
			const isRequired = $input.hasClass( 'wpforms-field-required' );

			$input.val( '' ).prop( 'disabled', false ).prop( 'required', isRequired );
			$button.prop( 'disabled', false );
			$couponRow.removeClass( app.classes.hasAppliedCoupons );
			$couponItem.remove();

			wpforms.amountTotal( $form, false );

			if ( $field.hasClass( 'wpforms-conditional-trigger' ) ) {
				window.wpformsconditionals.processConditionals( $form, false );
			}

			// Hide coupons data in the summary table.
			$form.find( '.wpforms-order-summary-preview' ).each( function() {
				const $this = $( this ),
					$subtotal = $this.find( '.wpforms-order-summary-preview-subtotal' ),
					$total = $this.find( '.wpforms-order-summary-preview-coupon-total' );

				// Reset total rows.
				$subtotal.hide();
				$total.hide();
				$subtotal.find( '.wpforms-order-summary-item-price' ).text( '' );
				$total.find( '.wpforms-order-summary-item-price' ).text( '' );

				const priceColumnWidth = $this.find( '.wpforms-order-summary-preview-total td:last-child' ).text().length + 3;

				$this.find( '.wpforms-order-summary-item-price' ).css( 'width', `${ priceColumnWidth }ch` );
			} );

			app.showDebugMessage( 'The ' + couponCode + ' coupon was removed' );
		},

		/**
		 * Reset field under CL.
		 *
		 * @since 1.1.0
		 */
		resetField() {
			if ( ! app.isEmptyField( $( this ) ) ) {
				return;
			}

			const $field = $( this ).closest( app.selectors.field );
			const $removeButton = $field.find( app.selectors.removeCouponButton );

			$removeButton.click();
		},

		/**
		 * Show debug message.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} message Message.
		 */
		showDebugMessage( message ) {
			if ( window.location.hash && window.location.hash === '#wpformsdebug' ) {
				// eslint-disable-next-line no-console
				console.log( message );
			}
		},

		/**
		 * Apply a coupon.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 Deprecated.
		 * @deprecated Use `WPFormsCoupons.applyCouponRequest: function( element, value )` instead.
		 *
		 * @param {jQuery} $field Field.
		 *
		 * @return {Promise} Is the field has an applied coupon?
		 */
		applyCoupon( $field ) {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsCoupons.applyCoupon( $field )" has been deprecated, please use the new "WPFormsCoupons.applyCouponRequest: function( element, value )" function instead!' );

			// eslint-disable-next-line compat/compat -- Promise is supported in all browsers we support.
			return new Promise(
				function( resolve, reject ) {
					const $couponRow = $field.find( app.selectors.couponRow );

					if ( $couponRow.hasClass( app.classes.hasAppliedCoupons ) ) {
						resolve( true );

						return;
					}

					const couponCode = $field.find( app.selectors.couponInput ).val().trim();
					const errorMessage = wpforms_settings.val_invalid_coupon;

					if ( ! couponCode ) {
						app.showError( $field, errorMessage );

						reject( errorMessage );

						return;
					}

					const $form = $field.closest( app.selectors.form );
					const formId = $form.data( 'formid' );

					$.ajax( {
						type: 'POST',
						url: wpforms_settings.ajaxurl,
						data: {
							action: 'wpforms_coupons_apply_coupon',
							// eslint-disable-next-line camelcase
							form_id: formId,
							// eslint-disable-next-line camelcase
							coupon_code: couponCode,
						},
						dataType: 'json',
						beforeSend() {
							$couponRow.addClass( app.classes.applyingCoupon );
						},
						success( res ) {
							if ( ! res.success || ! res.data || ! res.data.formatted_code || ! res.data.value ) {
								app.showError( $field, errorMessage );
								reject( errorMessage );

								return;
							}

							app.applyCouponSuccess( $field, res.data.formatted_code, res.data.value, res.data.type );
							resolve( true );
						},
						error() {
							app.showError( $field, errorMessage );

							reject( errorMessage );
						},
						complete() {
							$couponRow.removeClass( app.classes.applyingCoupon );
						},
					} );
				}
			);
		},

		/**
		 * Apply coupon before page change.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 Deprecated.
		 *
		 * @param {Event}  e        Event.
		 * @param {number} nextPage Next page number.
		 * @param {jQuery} $form    Form.
		 * @param {string} action   Next or previous.
		 */
		beforePageChange( e, nextPage, $form, action ) {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsCoupons.beforePageChange( e, nextPage, $form, action )" has been deprecated' );

			if ( action !== 'next' ) {
				return;
			}

			const $button = $( e.target );
			const $activePage = $form.find( '.wpforms-page:visible' );

			$activePage.find( app.selectors.couponInput ).each( function() {
				const $couponInput = $( this );

				if ( ! app.isEmptyField( $couponInput ) && ! app.hasCoupon( $couponInput ) ) {
					e.preventDefault();

					app.applyCoupon( $couponInput.closest( app.selectors.field ) ).then( function() {
						wpforms.pagebreakNav( $button );
					} ).catch( app.showDebugMessage );
				}
			} );
		},

		/**
		 * Apply coupon on PayPal Commerce checkout click.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 Deprecated.
		 *
		 * @param {Event}  e     Event.
		 * @param {jQuery} $form Current form.
		 */
		onPayPalCommerceSubmit( e, $form ) {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsCoupons.onPayPalCommerceSubmit( e, $form )" has been deprecated' );

			app.removePayPalCommerceNotice();

			$form.find( app.selectors.couponInput ).each( function() {
				const $couponInput = $( this );

				if ( ! app.isEmptyField( $couponInput ) && ! app.hasCoupon( $couponInput ) ) {
					e.preventDefault();

					app.applyCoupon( $couponInput.closest( app.selectors.field ) )
						.then( function() {
							const $button = $form.find( '.wpforms-submit' );

							if ( WPFormsPaypalCommerce.isCheckoutSelected( $form ) ) {
								const $buttonContainer = $button.closest( '.wpforms-submit-container' );

								$buttonContainer.before(
									'<div class="wpforms-info wpforms-notice ' + app.classes.payPalCommerceNoticeClass + '">' + wpforms_settings.ppc_applied_coupon + '</div>'
								);

								return;
							}

							$button.click();
						} ).catch( app.showDebugMessage );
				}
			} );
		},

		/**
		 * Determine if field has applied coupon or apply coupon process has been already started.
		 *
		 * @since 1.1.0
		 * @deprecated 1.1.0 Deprecated.
		 *
		 * @param {jQuery} $couponInput Coupon input field.
		 *
		 * @return {boolean} True if field has applied coupon or apply coupon process has been already started.
		 */
		hasCoupon( $couponInput ) {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsCoupons.hasCoupon( $couponInput )" has been deprecated' );

			const $row = $couponInput.closest( app.selectors.couponRow );

			return $row.hasClass( app.classes.hasAppliedCoupons ) || $row.hasClass( app.classes.applyingCoupon );
		},

		/**
		 * Remove PayPal Commerce notice.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 Deprecated.
		 */
		removePayPalCommerceNotice() {
			// eslint-disable-next-line no-console
			console.warn( 'WARNING! Function "WPFormsCoupons.removePayPalCommerceNotice()" has been deprecated' );

			$( app.selectors.payPalCommerceNotice ).remove();
		},
	};

	return app;
}( document, window, jQuery ) );

WPFormsCoupons.init();

