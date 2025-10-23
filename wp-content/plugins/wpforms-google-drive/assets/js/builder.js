/* global WPForms, wpf, wpforms_builder, WPFormsBuilder, google, gapi, Choices */

/**
 * WPForms Providers Builder Google Drive module.
 *
 * @since 1.0.0
 *
 * @property {Object} wpforms_builder                                                 WPForms Builder i18n strings.
 * @property {Object} wpforms_builder.google_drive                                    WPForms Google Drive i18n strings.
 * @property {string} wpforms_builder.google_drive.delete_local_files_prompt          Message to inform a customer the Delete option works globally for all connections.
 * @property {string} wpforms_builder.google_drive.delete_local_files_confirm         Button text to confirm changing the Delete option.
 * @property {string} wpforms_builder.google_drive.google_drive_compatibility_message Message to inform customer the Google Drive not working with Dropbox.
 * @property {string} wpforms_builder.google_drive.dropbox_compatibility_message      Message to inform customer the Dropbox not working with Google Drive.
 * @property {string} wpforms_builder.google_drive.enabled_shared_drives              Should we display directories from shared drives?
 */
WPForms.Admin.Builder.Providers.GoogleDrive = WPForms.Admin.Builder.Providers.GoogleDrive || ( function( document, window, $ ) {
	/**
	 * Public functions and properties.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * CSS selectors.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		selectors: {
			panel: '.wpforms-builder-provider',
			formNameField: '#wpforms-panel-field-settings-form_title',
			settingsPanel: '#wpforms-panel-settings',
			connectionAddBtn: '.js-wpforms-builder-provider-connection-add',
			authButton: '#wpforms-google-drive-sign-in .gsi-material-button',
			connection: '.wpforms-builder-provider-connection',
			connections: '.wpforms-builder-provider-connections',
			fieldWrapper: '.wpforms-builder-provider-connection-block',
			accountField: '.js-wpforms-builder-google-drive-provider-connection-account',
			accountFields: '.js-wpforms-builder-google-drive-provider-account-fields',
			folderTypeField: '.js-wpforms-builder-google-drive-provider-connection-folder-type',
			folderTypeBlock:  '.wpforms-builder-google-drive-provider-folder-type .wpforms-builder-provider-connection-block-field',
			folderTypeRequired:  '.wpforms-builder-google-drive-provider-folder-type .required',
			folderIdField: '.js-wpforms-builder-google-drive-provider-connection-folder-id',
			folderIdFieldChoose: '.js-wpforms-builder-google-drive-provider-connection-folder-id-choose',
			folderIdFieldEmpty: '.js-wpforms-builder-provider-connection-block-field-existing-empty',
			folderIdFieldNotEmpty: '.js-wpforms-builder-provider-connection-block-field-existing-not-empty',
			folderViewBtn: '.js-wpforms-builder-google-drive-provider-connection-folder-id-view',
			folderRemoveBtn: '.js-wpforms-builder-google-drive-provider-connection-folder-id-remove',
			folderNameField: '.js-wpforms-builder-google-drive-provider-connection-folder-name',
			deleteLocalFilesField: '.js-wpforms-builder-google-drive-provider-connection-delete-local-files',
			deleteLocalFilesNotice: '.wpforms-alert',
			restrictedFileUploadFields: '.wpforms-file-upload-access-restrictions:checked',
			innerGroup: '.wpforms-field-option-group-inner',
			restrictedFileUploadType: '.wpforms-file-upload-user-restrictions',
			restrictedFileUploadPassword: '.wpforms-file-upload-password-restrictions:checked',
			choiceJS: '.choicesjs-select',
			DropboxPanel: '#dropbox-provider',
			DropboxAuthorizeButton: '.wpforms-builder-accounts-dropbox-authorize-button',
			defaultContent: '.wpforms-builder-provider-settings-default-content',
			compatibilityContent: '.wpforms-builder-provider-settings-compatibility-content',
			GoogleSheetsPanel: '#google-sheets-provider',
		},

		/**
		 * List of used classes.
		 *
		 * @since 1.0.0
		 */
		classes: {
			// BC: We can't use the `wpforms-hidden` because Add New Account and Add New Connection buttons uses `hidden` class.
			hideBtn: 'hidden',
			hide: 'wpforms-hidden',
			required: 'wpforms-required',
			filesField: 'js-wpforms-builder-google-drive-provider-connection-fields',
		},

		/**
		 * jQuery elements.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		$elements: {
			$builder: $( '#wpforms-builder' ),
			$panel: $( '#google-drive-provider' ),
			$connections: $( '#google-drive-provider .wpforms-builder-provider-connections' ),
		},

		/**
		 * Current provider slug.
		 *
		 * @since 1.0.0
		 *
		 * @type {string}
		 */
		provider: 'google-drive',

		/**
		 * This is a shortcut to the 'WPForms.Admin.Builder.Providers' object
		 * that handles the parent all-providers functionality.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		Providers: {},

		/**
		 * This is a shortcut to the 'WPForms.Admin.Builder.Templates' object
		 * that handles all the template management.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		Templates: {},

		/**
		 * This is a shortcut to the 'WPForms.Admin.Builder.Providers.cache' object
		 * that handles all the cache management.
		 *
		 * @since 1.0.0
		 *
		 * @type {Object}
		 */
		Cache: {},

		/**
		 * This is a flag for the ready state.
		 *
		 * @since 1.0.0
		 *
		 * @type {boolean}
		 */
		isReady: false,

		/**
		 * Start the engine.
		 *
		 * Run initialization on the Settings panel only.
		 *
		 * @since 1.0.0
		 */
		init() {
			// We are requesting/loading the Settings panel.
			if ( wpf.getQueryString( 'view' ) === 'settings' ) {
				$( app.selectors.settingsPanel ).on( 'WPForms.Admin.Builder.Providers.ready', app.ready );
			}

			// We have switched to the Settings panel.
			$( document ).on( 'wpformsPanelSwitched', function( event, panel ) {
				if ( panel === 'settings' ) {
					app.ready();
				}
			} );
		},

		/**
		 * Initialized once the DOM and Providers are fully loaded.
		 *
		 * @since 1.0.0
		 */
		ready() {
			if ( app.isReady ) {
				return;
			}

			app.Providers = WPForms.Admin.Builder.Providers;
			app.Templates = WPForms.Admin.Builder.Templates;
			app.Cache = app.Providers.cache;

			app.Templates.add( [
				'wpforms-google-drive-builder-content-connection',
				'wpforms-google-drive-builder-content-connection-error',
				'wpforms-google-drive-builder-content-connection-conditionals',
			] );

			// Events registration.
			app.bindUIActions();
			app.bindTriggers();
			app.processInitial();
			app.compatibility.Dropbox.init();
			app.compatibility.GoogleSheets.init();

			gapi.load( 'picker' );

			// Save a flag for ready state.
			app.isReady = true;
		},

		/**
		 * Process various events as a response to UI interactions.
		 *
		 * @since 1.0.0
		 */
		bindUIActions() {
			app.$elements.$panel
				.on( 'connectionCreate', app.connection.create )
				.on( 'connectionDelete', app.connection.delete )
				.on( 'change', app.selectors.accountField, app.ui.accountField.change )
				.on( 'click', app.selectors.authButton, app.account.add )
				.on( 'change', app.selectors.folderTypeField, app.ui.folderTypeField.changeType )
				.on( 'click', app.selectors.folderIdFieldChoose, app.ui.folderIdField.openPicker )
				.on( 'click', app.selectors.folderRemoveBtn, app.ui.folderIdField.clear )
				.on( 'input', app.selectors.folderIdField, app.ui.folderIdField.changeState )
				.on( 'input', app.selectors.formNameField, app.ui.folderNameField.update )
				.on( 'input', app.selectors.folderNameField, app.ui.folderNameField.change )
				.on( 'input', app.selectors.folderIdField, app.ui.fileLink.update )
				.on( 'change', app.selectors.deleteLocalFilesField, app.ui.deleteFilesField.change );

			app.$elements.$builder
				.on( 'wpformsFieldSelectMapped', app.ui.filesField.update )
				.on( 'wpformsSaved', app.connection.refresh );
		},

		/**
		 * Fire certain events on certain actions, specifically for related connections.
		 * These are not directly caused by user manipulations.
		 *
		 * @since 1.0.0
		 */
		bindTriggers() {
			app.$elements.$connections.on( 'connectionsDataLoaded', function( event, data ) {
				if ( _.isEmpty( data.connections ) ) {
					return;
				}

				for ( const connectionId in data.connections ) {
					app.connection.renderConnections( {
						connection: data.connections[ connectionId ],
						conditional: data.conditionals[ connectionId ],
					} );
				}
			} );

			app.$elements.$connections.on( 'connectionGenerated', function( event, data ) {
				const $connection = app.connection.getById( data.connection.id );

				if ( _.has( data.connection, 'isNew' ) && data.connection.isNew ) {
					app.ui.deleteFilesField.defaultValueForNewConnection( $connection );
					app.connection.replaceIds( data.connection.id, $connection );

					return;
				}

				$( app.selectors.folderIdField, $connection ).trigger( 'input', [ $connection ] );
			} );
		},

		/**
		 * Compile template with data if any and display them on a page.
		 *
		 * @since 1.0.0
		 */
		processInitial() {
			const error = app.Templates.get( `wpforms-${ app.provider }-builder-content-connection-error` );

			app.$elements.$connections.prepend( error() );
			app.connection.dataLoad();
		},

		/**
		 * Connection property.
		 *
		 * @since 1.0.0
		 */
		connection: {

			/**
			 * Sometimes we might need to get a connection DOM element by its ID.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} connectionId Connection ID to search for a DOM element by.
			 *
			 * @return {jQuery} jQuery object for connection.
			 */
			getById( connectionId ) {
				return app.$elements.$connections.find( '.wpforms-builder-provider-connection[data-connection_id="' + connectionId + '"]' );
			},

			/**
			 * Sometimes in DOM we might have placeholders or temporary connection IDs.
			 * We need to replace them with actual values.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} connectionId New connection ID to replace to.
			 * @param {Object} $connection  jQuery DOM connection element.
			 */
			replaceIds( connectionId, $connection ) {
				// Replace the old temporary% connection_id% from PHP code with the new one.
				$connection.find( 'input, select, label' ).each( function() {
					const $this = $( this );

					if ( $this.attr( 'name' ) ) {
						$this.attr( 'name', $this.attr( 'name' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'id' ) ) {
						$this.attr( 'id', $this.attr( 'id' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'for' ) ) {
						$this.attr( 'for', $this.attr( 'for' ).replace( /%connection_id%/gi, connectionId ) );
					}

					if ( $this.attr( 'data-name' ) ) {
						$this.attr( 'data-name', $this.attr( 'data-name' ).replace( /%connection_id%/gi, connectionId ) );
					}
				} );
			},

			/**
			 * Create a connection using the user-entered name.
			 *
			 * @since 1.0.0
			 *
			 * @param {Object} event Event object.
			 * @param {string} name  Connection name.
			 */
			create( event, name ) {
				const connectionId = new Date().getTime().toString( 16 ),
					connection = {
						id: connectionId,
						name,
						isNew: true,
					};

				app.Cache.addTo( app.provider, 'connections', connectionId, connection );

				app.connection.renderConnections( {
					connection,
				} );
			},

			/**
			 * Connection is deleted - delete a cache as well.
			 *
			 * @since 1.0.0
			 *
			 * @param {Object} event       Event object.
			 * @param {Object} $connection jQuery DOM element for a connection.
			 */
			delete( event, $connection ) {
				const $eHolder = app.Providers.getProviderHolder( app.provider );

				if ( ! $connection.closest( $eHolder ).length ) {
					return;
				}

				const connectionId = $connection.data( 'connection_id' );

				if ( _.isString( connectionId ) ) {
					app.Cache.deleteFrom( app.provider, 'connections', connectionId );
				}

				app.$elements.$connections.trigger( 'connectionDeleted' );
			},

			/**
			 * Render connections.
			 *
			 * @since 1.0.0
			 *
			 * @param {Object} data Connection data.
			 */
			renderConnections( data ) {
				const accounts = app.Cache.get( app.provider, 'accounts' );

				if ( ! app.account.isExists( data.connection.account_id, accounts ) ) {
					return;
				}

				const tmplConnection = app.Templates.get( `wpforms-${ app.provider }-builder-content-connection` ),
					formName = $( app.selectors.formNameField ).val(),
					tmplConditional = $( `#tmpl-wpforms-${ app.provider }-builder-content-connection-conditionals` ).length ? app.Templates.get( `wpforms-${ app.provider }-builder-content-connection-conditionals` ) : app.Templates.get( 'wpforms-providers-builder-content-connection-conditionals' ),
					conditional = _.has( data.connection, 'isNew' ) && data.connection.isNew ? tmplConditional() : data.conditional,
					fileUploadFields = wpf.getFields( [ 'file-upload' ], true );

				app.$elements.$connections
					.prepend(
						tmplConnection( {
							formName,
							fileUploadFields,
							accounts,
							connection: data.connection,
							conditional,
							provider: app.provider,
						} ) );

				// When we added a new connection with its accounts - trigger next steps.
				app.$elements.$connections.trigger( 'connectionGenerated', [ data ] );

				app.ui.initChoicesJS( app.connection.getById( data.connection.id ) );

				app.$elements.$connections.trigger( 'connectionRendered', [ app.provider, data ] );
			},

			/**
			 * Fire AJAX-request to retrieve the list of all saved connections.
			 *
			 * @since 1.0.0
			 */
			dataLoad() {
				app
					.Providers.ajax
					.request( app.provider, {
						data: {
							task: 'connections_get',
						},
					} )
					.done( function( response ) {
						if (
							! response.success ||
							! _.has( response.data, 'connections' )
						) {
							return;
						}

						app.connection.updateCache( response.data );

						app.$elements.$connections.trigger( 'connectionsDataLoaded', [ response.data ] );
					} );
			},

			/**
			 * Set cache data after AJAX requests.
			 *
			 * @since 1.0.0
			 *
			 * @param {Object} data Response data.
			 */
			updateCache( data ) {
				[
					'connections',
					'conditionals',
					'accounts',
				].forEach( ( dataType ) => {
					app.Cache.set( app.provider, dataType, jQuery.extend( {}, data[ dataType ] ) );
				} );
			},

			/**
			 * Refresh connections data after saving progress.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event}  e        Event.
			 * @param {Object} response Ajax response.
			 */
			refresh( e, response ) {
				if ( ! Object.hasOwn( response, app.provider ) ) {
					return;
				}

				const data = response[ app.provider ];

				if ( ! _.has( data, 'connections' ) ) {
					return;
				}

				app.connection.updateCache( data );

				app.$elements.$connections.html( '' );

				app.$elements.$connections.trigger( 'connectionsDataLoaded', [ data ] );
			},
		},

		/**
		 * Account property.
		 *
		 * @since 1.0.0
		 */
		account: {

			/**
			 * Check if a provided account is listed inside an account list.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} accountId Connection account ID to check.
			 * @param {Object} accounts  Array of objects, usually received from API.
			 *
			 * @return {boolean} True if an account exists.
			 */
			isExists( accountId, accounts ) {
				if ( _.isEmpty( accounts ) ) {
					return false;
				}

				// New connections that have not been saved don't have the account ID yet.
				if ( _.isEmpty( accountId ) ) {
					return true;
				}

				return _.has( accounts, accountId );
			},

			/**
			 * Redirect the customer to the Google Drive authorization consent screen.
			 *
			 * @since 1.0.0
			 *
			 * @param {Event} e Event.
			 */
			add( e ) {
				e.preventDefault();

				if ( WPFormsBuilder.formIsSaved() ) {
					window.location.href = wpforms_builder.google_drive.auth_url;

					return;
				}

				// eslint-disable-next-line camelcase
				wpforms_builder.exit_url = wpforms_builder.google_drive.auth_url;

				WPFormsBuilder.formSave( true );
			},
		},

		/**
		 * All methods that modify the UI of a page.
		 *
		 * @since 1.0.0
		 */
		ui: {

			/**
			 * Account field methods.
			 *
			 * @since 1.0.0
			 */
			accountField: {
				/**
				 * Callback-function on change event.
				 *
				 * @since 1.0.0
				 */
				change() {
					const $this = $( this );
					const $connection = $this.closest( app.selectors.connection );
					const $accountFields = $( app.selectors.accountFields, $connection );

					$accountFields.toggleClass( app.classes.hide, $this.val() === '' );

					$( app.selectors.folderIdField, $connection ).val( '' ).trigger( 'input' );
				},
			},

			/**
			 * Folder type field methods.
			 *
			 * @since 1.0.0
			 */
			folderTypeField: {
				/**
				 * Change type field callback.
				 *
				 * @since 1.0.0
				 */
				changeType() {
					const $this = $( this );
					const isNew = $this.val() === 'new';
					const $connection = $this.closest( app.selectors.connection );
					const $folderId = $( app.selectors.folderIdField, $connection );
					const $folderIdWrapper = $folderId.closest( app.selectors.fieldWrapper );
					const $folderName = $( app.selectors.folderNameField, $connection );
					const $folderNameWrapper = $folderName.closest( app.selectors.fieldWrapper );
					const $folderTypeAsterisk = $( app.selectors.folderTypeRequired, $connection );

					$folderTypeAsterisk.toggleClass( app.classes.hide, isNew );

					$folderIdWrapper.toggleClass( app.classes.hide, isNew );
					$folderId.toggleClass( app.classes.required, ! isNew );

					$folderNameWrapper.toggleClass( app.classes.hide, ! isNew );
					$folderName.toggleClass( app.classes.required, isNew );
				},
			},

			/**
			 * Folder Id field methods.
			 *
			 * @since 1.0.0
			 */
			folderIdField: {
				/**
				 * Open Google Drive picker.
				 *
				 * @since 1.0.0
				 */
				openPicker() {
					const $connection = $( this ).closest( app.selectors.connection );

					app.Providers.ajax
						.request( app.provider, {
							data: {
								task: 'access_token_get',
								// eslint-disable-next-line camelcase
								account_id: $( app.selectors.accountField, $connection ).val(),
							},
						} )
						.done( function( response ) {
							if ( ! _.has( response, 'data' ) || ! _.has( response.data, 'access_token' ) ) {
								return;
							}

							const docsView = new google.picker.DocsView( google.picker.ViewId.FOLDERS )
								.setMode( google.picker.DocsViewMode.LIST )
								.setSelectFolderEnabled( true );

							if ( ! wpforms_builder.google_drive.enabled_shared_drives ) {
								docsView.setOwnedByMe( true );
							}

							const picker = new google.picker.PickerBuilder()
								.setOAuthToken( response.data.access_token )
								.addView( docsView )
								.hideTitleBar()
								.enableFeature( google.picker.Feature.NAV_HIDDEN )
								.setCallback( function( data ) {
									if ( data.action !== google.picker.Action.PICKED ) {
										return;
									}

									const $folderIdField = $( app.selectors.folderIdField, $connection );

									const folder = data[ google.picker.Response.DOCUMENTS ][ 0 ];
									const folderId = folder[ google.picker.Document.ID ];

									$folderIdField.val( folderId ).trigger( 'input' );
								} )
								.build();
							picker.setVisible( true );
						} );
				},
				/**
				 * Clear the field.
				 *
				 * @since 1.0.0
				 */
				clear() {
					$( this )
						.closest( app.selectors.connection )
						.find( app.selectors.folderIdField )
						.val( '' )
						.trigger( 'input' );
				},
				/**
				 * Change field state to empty or not-empty.
				 *
				 * @since 1.0.0
				 */
				changeState() {
					const $this = $( this );
					const folderId = $this.val();
					const isEmpty = folderId === '' || folderId === null;
					const $connection = $this.closest( app.selectors.connection );
					const $existingEmptyBlock = $( app.selectors.folderIdFieldEmpty, $connection );
					const $existingNotEmptyBlock = $( app.selectors.folderIdFieldNotEmpty, $connection );
					const $folderTypeBlock = $( app.selectors.folderTypeBlock, $connection );

					$folderTypeBlock.toggleClass( app.classes.hide, ! isEmpty );
					$existingEmptyBlock.toggleClass( app.classes.hide, ! isEmpty );
					$existingNotEmptyBlock.toggleClass( app.classes.hide, isEmpty );
				},
			},

			/**
			 * Folder Name field methods.
			 *
			 * @since 1.0.0
			 */
			folderNameField: {

				/**
				 * Update the folder name field when the Form Name field is changed.
				 *
				 * @since 1.0.0
				 */
				update() {
					const formName = $( this ).val();
					const $folderNameField = $( app.selectors.folderNameField );

					$folderNameField.attr( 'placeholder', formName );

					if ( ! $folderNameField.data( 'changed' ) || $folderNameField.val() === '' ) {
						$folderNameField.val( formName );
					}
				},

				/**
				 * Set the changed data attribute as soon as the field was manually changed.
				 *
				 * @since 1.0.0
				 */
				change() {
					const $folderNameField = $( this );

					if ( $folderNameField.data( 'changed' ) ) {
						return;
					}

					if ( $folderNameField.val() !== $folderNameField.data( 'default' ) ) {
						$( this ).data( 'changed', true );
					}
				},
			},

			/**
			 * Files field methods.
			 *
			 * @since 1.0.0
			 */
			filesField: {
				/**
				 * Update value for choiceJS field.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event}  e       Event.
				 * @param {jQuery} $select Select field.
				 */
				update( e, $select ) {
					if ( ! $select.hasClass( app.classes.filesField ) ) {
						return;
					}

					let choicesObj = $select.data( 'choicesjs' );

					if ( ! choicesObj ) {
						return;
					}

					const choices = app.ui.filesField.getSelectFieldChoices( $select );
					const currentValues = choicesObj.getValue( true ).filter( ( value ) =>
						choices.some( ( choice ) => choice.value === value )
					);

					// Workaround to display a placeholder when no activate values.
					if ( currentValues.length === 0 ) {
						choicesObj.destroy();
						$select.removeData( 'choicesjs' );
						app.ui.initChoicesJS( $select.closest( app.selectors.connection ) );
						choicesObj = $select.data( 'choicesjs' );
					}

					// noinspection JSVoidFunctionReturnValueUsed
					choicesObj
						.clearChoices( true, true )
						.removeActiveItems()
						.setChoices( choices, 'value', 'label', true )
						.setChoiceByValue( currentValues );
				},

				/**
				 * Get select field options and convert them to an array for ChoiceJS choices format.
				 *
				 * @since 1.0.0
				 *
				 * @param {jQuery} $select Select field.
				 *
				 * @return {Array} Choices.
				 */
				getSelectFieldChoices( $select ) {
					const choices = [];

					$select.find( 'option' ).each( function() {
						const $option = $( this );
						const value = $option.val();

						if ( value === '' ) {
							return;
						}

						choices.push(
							{ value, label: $option.text() }
						);
					} );

					return choices;
				},
			},

			/**
			 * Doc Link.
			 *
			 * @since 1.0.0
			 */
			fileLink: {
				/**
				 * Update link URL.
				 *
				 * @since 1.0.0
				 */
				update() {
					const $connection = $( this ).closest( app.selectors.connection );
					const folderType = $( app.selectors.folderTypeField + ':checked', $connection ).val();
					const folderId = $( app.selectors.folderIdField, $connection ).val();

					if ( ! folderId || folderType === 'new' ) {
						return;
					}

					const $link = $( app.selectors.folderViewBtn, $connection );

					$link.attr( 'href', app.ui.fileLink.getUrl( folderId ) );
				},

				/**
				 * Get a spreadsheet URL.
				 *
				 * @since 1.0.0
				 *
				 * @param {string} folderId Folder ID.
				 *
				 * @return {string} Spreadsheet URL.
				 */
				getUrl( folderId ) {
					return `https://drive.google.com/drive/u/0/folders/${ folderId }`;
				},
			},

			/**
			 * Delete files field methods.
			 *
			 * @since 1.0.0
			 */
			deleteFilesField: {
				/**
				 * Set the default value for a new connection.
				 *
				 * @since 1.0.0
				 *
				 * @param {jQuery} $connection Connection element.
				 */
				defaultValueForNewConnection( $connection ) {
					const $deleteFilesField = $( app.selectors.deleteLocalFilesField, $connection );
					const connectionId = $connection.data( 'connection_id' );
					const isChecked = app.$elements.$connections
						.find( app.selectors.connection + ':not([data-connection_id="' + connectionId + '"])' )
						.find( app.selectors.deleteLocalFilesField ).is( ':checked' );

					$deleteFilesField.prop( 'checked', isChecked );
				},
				/**
				 * Change event callback.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event} e Event.
				 */
				change( e ) {
					e.preventDefault();

					if ( app.$elements.$connections.find( app.selectors.deleteLocalFilesField ).length <= 1 ) {
						return;
					}

					app.ui.deleteFilesField.confirmationModal( ! $( this ).is( ':checked' ) );
				},
				/**
				 * Confirmation modal.
				 *
				 * @since 1.0.0
				 *
				 * @param {boolean} isChecked Are fields checked.
				 */
				confirmationModal( isChecked ) {
					const $deleteFilesFields = app.$elements.$connections.find( app.selectors.deleteLocalFilesField );

					$.confirm( {
						title: wpforms_builder.heads_up,
						content: wpforms_builder.google_drive.delete_local_files_prompt,
						icon: 'fa fa-exclamation-circle',
						type: 'orange',
						buttons: {
							confirm: {
								text: wpforms_builder.google_drive.delete_local_files_confirm,
								btnClass: 'btn-confirm',
								keys: [ 'enter' ],
								action() {
									$deleteFilesFields.prop( 'checked', ! isChecked );
									app.compatibility.GoogleSheets.updateVisibility();
								},
							},
							cancel: {
								text: wpforms_builder.cancel,
								btnClass: 'btn-cancel',
								action() {
									$deleteFilesFields.prop( 'checked', isChecked );
									app.compatibility.GoogleSheets.updateVisibility();
								},
							},
						},
					} );
				},
			},

			/**
			 * Initialize Choices.js library.
			 *
			 * @since 1.0.0
			 *
			 * @param {Object} $connection jQuery connection selector.
			 */
			initChoicesJS( $connection ) {
				// Load if the function exists.
				if ( typeof window.Choices !== 'function' ) {
					return;
				}

				const $choices = $( app.selectors.choiceJS, $connection );

				$choices.each( function( index, element ) {
					const $this = $( element );

					if ( 'undefined' !== typeof $this.data( 'choicesjs' ) ) {
						return;
					}

					$this.data( 'choicesjs', new Choices( $this[ 0 ], {
						shouldSort: false,
						removeItemButton: true,
						fuseOptions:{
							threshold: 0.1,
							distance: 1000,
						},
						callbackOnInit() {
							wpf.initMultipleSelectWithSearch( this );
							wpf.showMoreButtonForChoices( this.containerOuter.element );
						},
					} ) );
				} );
			},
		},

		/**
		 * Addons compatibility logic.
		 *
		 * @since 1.0.0
		 */
		compatibility: {

			/**
			 * Dropbox-related logic.
			 *
			 * @since 1.0.0
			 */
			Dropbox: {

				/**
				 * Dropbox provider slug.
				 *
				 * @since 1.0.0
				 */
				provider: 'dropbox',

				/**
				 * Determine if both Google Drive and Dropbox are installed and have at least one connected account.
				 *
				 * @since 1.0.0
				 *
				 * @return {boolean} True if connected.
				 */
				isConfigured() {
					const isGoogleDriveAccountExists = app.$elements.$panel
						.find( app.selectors.authButton ).length === 0;

					if ( ! isGoogleDriveAccountExists ) {
						return false;
					}

					const $dropboxPanel = $( app.selectors.DropboxPanel );

					if ( ! $dropboxPanel.length ) {
						return false;
					}

					return ! $dropboxPanel.find( app.selectors.DropboxAuthorizeButton ).length;
				},

				/**
				 * Initialize.
				 *
				 * @since 1.0.0
				 */
				init() {
					if ( ! app.compatibility.Dropbox.isConfigured() ) {
						return;
					}

					const $panels = app.$elements.$panel.add( app.selectors.DropboxPanel );
					const $connections = $panels.find( app.selectors.connections );

					app.compatibility.Dropbox.lockButton( app.provider );
					app.compatibility.Dropbox.lockButton( app.compatibility.Dropbox.provider );

					$connections
						.on( 'connectionsDataLoaded', app.compatibility.Dropbox.dataLoaded )
						.on( 'connectionGenerated', app.compatibility.Dropbox.lockNew );

					$panels
						.on( 'connectionDelete', app.compatibility.Dropbox.unlockDelete );
				},

				/**
				 * Get an element's provider name.
				 *
				 * @since 1.0.0
				 *
				 * @param {jQuery} $el Element.
				 *
				 * @return {string} Provider name.
				 */
				getElementProvider( $el ) {
					return $el.closest( app.selectors.panel ).data( 'provider' );
				},

				/**
				 * Get a linked provider.
				 *
				 * @since 1.0.0
				 *
				 * @param {string} currentProvider Current provider.
				 *
				 * @return {string} Return linked provider, for Google Drive is Dropbox, and wise versa.
				 */
				getLinkedProvider( currentProvider ) {
					return currentProvider === app.provider ? app.compatibility.Dropbox.provider : app.provider;
				},

				/**
				 * Unlock the provider.
				 * Show a button and hide compatibility text.
				 *
				 * @since 1.0.0
				 *
				 * @param {string} provider Provider slug.
				 */
				unlock( provider ) {
					const $holder = app.Providers.getProviderHolder( provider );
					const $defaultContent = $holder.find( app.selectors.defaultContent );
					const $compatibilityContent = $holder.find( app.selectors.compatibilityContent );

					$holder
						.find( app.selectors.connectionAddBtn )
						.removeClass( app.classes.hideBtn );

					$defaultContent.removeClass( app.classes.hide );

					$compatibilityContent.addClass( app.classes.hide );
				},

				/**
				 * Add or show a compatibility message.
				 *
				 * @since 1.0.0
				 *
				 * @param {string} provider Provider slug.
				 */
				lockMessage( provider ) {
					const $holder = app.Providers.getProviderHolder( provider );
					const $defaultContent = $holder.find( app.selectors.defaultContent );
					const $compatibilityContent = $holder.find( app.selectors.compatibilityContent );

					$defaultContent.addClass( app.classes.hide );

					if ( $compatibilityContent.length ) {
						$compatibilityContent.removeClass( app.classes.hide );

						return;
					}

					const message = provider === app.provider
						? wpforms_builder.google_drive.google_drive_compatibility_message
						: wpforms_builder.google_drive.dropbox_compatibility_message;
					const compatibilityClass = app.selectors.compatibilityContent.substring( 1 );

					$defaultContent
						.after( `<p class="${ compatibilityClass }">${ message }</p>` );
				},

				/**
				 * Hide the `Add New Connection` button for a specific provider.
				 *
				 * @since 1.0.0
				 *
				 * @param {string} provider Provider slug.
				 */
				lockButton( provider ) {
					const $holder = app.Providers.getProviderHolder( provider );

					$holder
						.find( app.selectors.connectionAddBtn )
						.addClass( app.classes.hideBtn );
				},

				/**
				 * Show a compatibility message or unlock the button.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event}  e    Event.
				 * @param {Object} data AJAX response.
				 */
				dataLoaded( e, data ) {
					const currentProvider = app.compatibility.Dropbox.getElementProvider( $( this ) );

					if ( currentProvider !== app.provider && currentProvider !== app.compatibility.Dropbox.provider ) {
						return;
					}

					const linkedProvider = app.compatibility.Dropbox.getLinkedProvider( currentProvider );

					if ( ! _.isEmpty( data.connections ) ) {
						app.compatibility.Dropbox.lockMessage( linkedProvider );

						return;
					}

					app.compatibility.Dropbox.unlock( linkedProvider );
				},

				/**
				 * Unlock a linked provider when the last connection is deleted.
				 *
				 * @since 1.0.0
				 */
				unlockDelete() {
					const currentProvider = app.compatibility.Dropbox.getElementProvider( $( this ) );
					const connections = app.Cache.get( currentProvider, 'connections' );

					if ( ! _.isEmpty( connections ) ) {
						return;
					}

					app.compatibility.Dropbox.unlock( app.compatibility.Dropbox.getLinkedProvider( currentProvider ) );
				},

				/**
				 * Lock a linked provider when adding a new connection.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event}  e    Event.
				 * @param {Object} data Data required to create a connection.
				 */
				lockNew( e, data ) {
					if ( ! _.has( data.connection, 'isNew' ) || ! data.connection.isNew ) {
						return;
					}

					const linkedProvider = app.compatibility.Dropbox.getLinkedProvider( app.compatibility.Dropbox.getElementProvider( $( this ) ) );

					app.compatibility.Dropbox.lockButton( linkedProvider );
					app.compatibility.Dropbox.lockMessage( linkedProvider );
				},
			},

			/**
			 * Google Sheets related logic.
			 *
			 * @since 1.0.0
			 */
			GoogleSheets: {

				/**
				 * Initialize.
				 *
				 * @since 1.0.0
				 */
				init() {
					if ( ! app.compatibility.GoogleSheets.isConfigured() ) {
						return;
					}

					const $GoogleSheetsPanel = $( app.selectors.GoogleSheetsPanel );
					const $connections = app.$elements.$panel.find( app.selectors.connections );

					app.$elements.$panel
						.on( 'change', app.compatibility.GoogleSheets.updateVisibility );

					app.$elements.$builder
						.on( 'wpformsPanelSwitch', app.compatibility.GoogleSheets.switchPanel )
						.on( 'wpformsPanelSectionSwitch', app.compatibility.GoogleSheets.switchSection );

					$GoogleSheetsPanel
						.on( 'connectionGenerated', app.compatibility.GoogleSheets.updateVisibility );
					$connections
						.on( 'connectionGenerated', app.compatibility.GoogleSheets.updateVisibility );
				},

				/**
				 * Is Google Sheets having a connected account.
				 *
				 * @since 1.0.0
				 *
				 * @return {boolean} True if it has.
				 */
				isConfigured() {
					const $panel = $( app.selectors.GoogleSheetsPanel );

					return ! $panel.find( app.selectors.connectionAddBtn ).hasClass( app.classes.hideBtn );
				},

				/**
				 * Determine if the form contains a properly set File Upload Restriction field.
				 *
				 * @since 1.0.0
				 *
				 * @return {boolean} True if a form contains at least one File Upload field with properly configured Restrictions.
				 */
				isFormHasRestrictedFields() {
					const $enabledRestrictions = $( app.selectors.restrictedFileUploadFields );

					if ( ! $enabledRestrictions.length ) {
						return false;
					}

					let hasRestrictedField = false;

					$enabledRestrictions.each( function() {
						const $group = $( this ).closest( app.selectors.innerGroup );

						if (
							$group.find( app.selectors.restrictedFileUploadType ).val() !== 'none' ||
							$group.find( app.selectors.restrictedFileUploadPassword ).length
						) {
							hasRestrictedField = true;

							return false;
						}
					} );

					return hasRestrictedField;
				},

				/**
				 * Update notice visibility.
				 *
				 * @since 1.0.0
				 */
				updateVisibility() {
					const hasFields = app.compatibility.GoogleSheets.isFormHasRestrictedFields();
					const hasConnections = Boolean( $( app.selectors.GoogleSheetsPanel ).find( app.selectors.connection ).length );
					const deleteLocalFilesEnabled = $( app.selectors.deleteLocalFilesField ).is( ':checked' );

					$( app.selectors.deleteLocalFilesNotice, app.$elements.$connections )
						.toggleClass( app.classes.hide, ! hasFields || ! hasConnections || ! deleteLocalFilesEnabled );
				},

				/**
				 * Switch a panel event.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event}  e     Event.
				 * @param {string} panel Panel name.
				 */
				switchPanel( e, panel ) {
					if ( panel === 'settings' ) {
						app.compatibility.GoogleSheets.updateVisibility();
					}
				},

				/**
				 * Switch a panel section event.
				 *
				 * @since 1.0.0
				 *
				 * @param {Event}  e       Event.
				 * @param {string} section Section name.
				 */
				switchSection( e, section ) {
					if ( section === app.provider ) {
						app.compatibility.GoogleSheets.updateVisibility();
					}
				},
			},
		},
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );

// Initialize.
WPForms.Admin.Builder.Providers.GoogleDrive.init();
