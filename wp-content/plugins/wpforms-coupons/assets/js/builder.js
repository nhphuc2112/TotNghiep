/* global WPForms, WPFormsBuilder, Choices, wpforms_builder, wpf */

/**
 * WPForms Coupons Builder module.
 *
 * @since 1.0.0
 */
WPForms.Admin.Builder.Coupons = WPForms.Admin.Builder.Coupons || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Field type.
		 *
		 * @since 1.0.0
		 *
		 * @type {string}
		 */
		fieldType: 'payment-coupon',

		/**
		 * CSS selectors.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		selectors: {
			buttonTextInput: '.wpforms-field-option-row-button_text input',
			allowedCouponsSelect: '.wpforms-field-option-row-allowed_coupons select',
			allowedFormsHiddenInput: '.wpforms-coupons-allowed_coupons_json',
			allowedCouponsAlert: '.wpforms-alert',
			allowedCouponsPreviewAlert: '.fa-exclamation-triangle',
			buttonPreview: '.wpforms-field-payment-coupon-button',
			fieldOptionRow: '.wpforms-field-option-row',
			noCouponsButton: '.wpforms-add-fields-button-no-coupons',
			addField: '#wpforms-add-fields-payment-coupon',
		},

		/**
		 * CSS class names
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		classes: {
			disabledAddButton: 'wpforms-add-fields-button-disabled',
			noCouponsAddButton: 'wpforms-add-fields-button-no-coupons',
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init() {
			$( app.ready );
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready() {
			app.events();
			app.initChoicesJS();
		},

		/**
		 * Register JS events.
		 *
		 * @since 1.0.0
		 */
		events() {
			$( document )
				.on( 'input', app.selectors.buttonTextInput, app.updateButtonPreview )
				.on( 'change', app.selectors.allowedCouponsSelect, app.changeAllowedCoupons )
				.on( 'wpformsBeforeFieldAddOnClick', app.lockField )
				.on( 'wpformsFieldAddDragStart', app.lockField )
				.on( 'click', app.selectors.noCouponsButton, app.noCouponsPopup )
				.on( 'wpformsFieldAdd', app.initChoicesJS )
				.on( 'wpformsFieldDelete', ( e, id, type ) => {
					app.unlockField( type );
				} )
				.on( 'wpformsFieldAddDragStop', ( e, type ) => {
					app.unlockField( type );
				} )
				.on( 'wpformsFieldOptionTabToggle', ( e, fieldId ) => {
					app.reInitChoices( fieldId );
				} );
		},

		/**
		 * Initialize Choices.js for the field.
		 *
		 * @since 1.0.0
		 */
		initChoicesJS() {
			if ( typeof window.Choices !== 'function' ) {
				return;
			}

			$( app.selectors.allowedCouponsSelect + ':not(.choices__input)' ).each( function() {
				const $select = $( this );
				const choicesInstance = new Choices(
					$select.get( 0 ),
					{
						shouldSort: false,
						removeItemButton: true,
						renderChoicesLimit: 5,
						callbackOnInit() {
							wpf.showMoreButtonForChoices( this.containerOuter.element );
						},
					} );

				// Save Choices.js instance for future access.
				$select.data( 'choicesjs', choicesInstance );
			} );
		},

		/**
		 * Re-initialize Choices.js for coupon field.
		 *
		 * @since 1.4.0
		 *
		 * @param {string} fieldId Field ID.
		 */
		reInitChoices( fieldId ) {
			const $field = $( '#wpforms-field-option-' + fieldId ),
				$fieldType = $field.find( '.wpforms-field-option-hidden-type' ).val();

			if ( $fieldType !== app.fieldType ) {
				return;
			}

			const $choiceInstance = $( '#wpforms-field-option-row-' + fieldId + '-allowed_coupons' ).find( '.choices select' ).data( 'choicesjs' );
			wpf.showMoreButtonForChoices( $choiceInstance.containerOuter.element );
		},

		/**
		 * Change Allowed forms event handler.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} $select Select object.
		 */
		updateAllowedCouponsField( $select ) {
			const $row = $select.closest( app.selectors.fieldOptionRow );
			const allowedCouponIds = $select.data( 'choicesjs' ).getValue();
			const forms = [];

			for ( const coupon of allowedCouponIds ) {
				forms.push( parseInt( coupon.value, 10 ) );
			}

			$row.find( app.selectors.allowedFormsHiddenInput ).val(
				JSON.stringify( forms )
			);
		},

		/**
		 * Show a popup when no coupon has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param {Event} e Event.
		 */
		noCouponsPopup( e ) {
			e.preventDefault();

			$.alert( {
				title: wpforms_builder.coupons.no_coupons_title,
				content: wpforms_builder.coupons.no_coupons_message,
				icon: 'fa fa-info-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.coupons.no_coupons_button,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
						action() {
							// eslint-disable-next-line camelcase
							wpforms_builder.exit_url = wpforms_builder.coupons.add_new_coupon_url;

							WPFormsBuilder.formSave( true );
						},
					},
					cancel: {
						text: wpforms_builder.cancel,
					},
				},
			} );
		},

		/**
		 * Update the button preview when we change the button text setting.
		 *
		 * @since 1.0.0
		 */
		updateButtonPreview() {
			const $this = $( this );
			const value = $this.val().trim();
			const fieldId = $this.closest( app.selectors.fieldOptionRow ).data( 'field-id' );
			const $previewButton = $( '#wpforms-field-' + fieldId ).find( app.selectors.buttonPreview );
			const buttonText = value ? value : wpforms_builder.coupons.button_text;

			$previewButton.text( buttonText );
		},

		/**
		 * Show alerts when the allowed coupons setting is changed.
		 *
		 * @since 1.0.0
		 */
		changeAllowedCoupons() {
			const $row = $( this ).closest( app.selectors.fieldOptionRow );
			const fieldId = $row.data( 'field-id' );
			const $alert = $row.find( app.selectors.allowedCouponsAlert );
			const $previewWarning = $( '#wpforms-field-' + fieldId ).find( app.selectors.allowedCouponsPreviewAlert );
			const isEmpty = $( this ).val().length === 0;

			app.updateAllowedCouponsField( $( this ) );

			if ( isEmpty ) {
				$alert.show();
				$previewWarning.show();

				return;
			}

			$alert.hide();
			$previewWarning.hide();
		},

		/**
		 * Disable the add field button when the field is added.
		 *
		 * @since 1.0.0
		 *
		 * @param {Object} e    Event object.
		 * @param {string} type Field type.
		 */
		lockField( e, type ) {
			if ( type !== app.fieldType ) {
				return;
			}

			const $addField = $( app.selectors.addField );

			if (
				$addField.hasClass( app.classes.disabledAddButton ) ||
				$addField.hasClass( app.classes.noCouponsAddButton )
			) {
				e.preventDefault();

				return;
			}

			$( app.selectors.addField ).addClass( app.classes.disabledAddButton );
		},

		/**
		 * Enable the add field button when the field is deleted.
		 *
		 * @since 1.0.0
		 * @since 1.1.0 Removed `e` and `id` parameters.
		 *
		 * @param {string} type Field type.
		 */
		unlockField( type ) {
			if ( type === app.fieldType ) {
				$( app.selectors.addField ).removeClass( app.classes.disabledAddButton );
			}
		},
	};

	return app;
}( document, window, jQuery ) );

WPForms.Admin.Builder.Coupons.init();
