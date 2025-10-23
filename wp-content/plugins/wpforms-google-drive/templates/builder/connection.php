<?php

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection" data-connection_id="{{ data.connection.id }}">
	<input type="hidden" class="wpforms-builder-provider-connection-id" name="providers[{{ data.provider }}][{{ data.connection.id }}][id]" value="{{ data.connection.id }}">

	<div class="wpforms-builder-provider-connection-title">
		{{ data.connection.name }}
		<button class="wpforms-builder-provider-connection-delete js-wpforms-builder-provider-connection-delete" type="button">
			<i class="fa fa-trash-o"></i>
		</button>
		<input type="hidden"
			id="wpforms-builder-google-drive-provider-{{ data.connection.id }}-name"
			name="providers[{{ data.provider }}][{{ data.connection.id }}][name]"
			value="{{ data.connection.name }}">
	</div>

	<div class="wpforms-builder-provider-connection-block wpforms-builder-google-drive-provider-accounts">
		<label for="js-wpforms-builder-google-drive-provider-{{ data.connection.id }}-account"><?php esc_html_e( 'Account', 'wpforms-google-drive' ); ?><span class="required">*</span></label>

		<select id="js-wpforms-builder-google-drive-provider-{{ data.connection.id }}-account" class="js-wpforms-builder-google-drive-provider-connection-account wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][account_id]"<# if ( _.isEmpty( data.accounts ) ) { #> disabled<# } #>>
			<option value="" selected disabled><?php esc_html_e( '--- Select Account ---', 'wpforms-google-drive' ); ?></option>

			<# _.each( data.accounts, function ( account, account_id ) { #>
				<option value="{{ account_id }}" data-option_id="{{ account['option_id'] }}"
					<# if ( account_id === data.connection.account_id ) { #> selected<# } #>>
					{{ account }}
				</option>
			<# } ); #>
		</select>
	</div>

	<div class="js-wpforms-builder-google-drive-provider-account-fields wpforms-builder-google-drive-provider-account-fields<# if ( _.isEmpty( data.connection.account_id ) ) { #> wpforms-hidden<# } #>">
		<div class="wpforms-builder-provider-connection-block wpforms-builder-google-drive-provider-folder-type">
			<label for="wpforms-builder-google-drive-folder-field-{{ data.connection.id }}">
				<?php esc_html_e( 'Folder', 'wpforms-google-drive' ); ?><span class="required <# if ( _.isEmpty( data.connection.folder_type ) || data.connection.folder_type === 'new' ) { #> wpforms-hidden<# } #>">*</span>
			</label>

			<div class="wpforms-builder-provider-connection-block-field">
				<label>
					<input type="radio" value="new" name="providers[{{ data.provider }}][{{ data.connection.id }}][folder_type]" class="js-wpforms-builder-google-drive-provider-connection-folder-type wpforms-builder-google-drive-folder-type"<# if ( _.isEmpty( data.connection.folder_type ) || data.connection.folder_type === 'new' ) { #> checked <# } #>>
					<?php esc_html_e( 'Create New', 'wpforms-google-drive' ); ?>
				</label>
				<label>
					<input type="radio" value="existing" name="providers[{{ data.provider }}][{{ data.connection.id }}][folder_type]" class="js-wpforms-builder-google-drive-provider-connection-folder-type wpforms-builder-google-drive-folder-type"<# if ( data.connection.folder_type === 'existing' ) { #> checked <# } #>>
					<?php esc_html_e( 'Select Existing', 'wpforms-google-drive' ); ?>
				</label>
			</div>
		</div>

		<div class="wpforms-builder-provider-connection-block wpforms-builder-google-drive-provider-folder-id<# if ( _.isEmpty( data.connection.folder_type ) || data.connection.folder_type !== 'existing' ) { #> wpforms-hidden<# } #>">
			<input type="hidden"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][folder_id]"
					value="{{ data.connection.folder_id }}"
					class="js-wpforms-builder-google-drive-provider-connection-folder-id wpforms-required"
			>
			<div class="wpforms-builder-provider-connection-block-field wpforms-builder-provider-connection-block-field-existing-empty js-wpforms-builder-provider-connection-block-field-existing-empty<# if ( ! _.isEmpty( data.connection.folder_id ) ) { #> wpforms-hidden<# } #>">
				<button type="button"
						class="js-wpforms-builder-google-drive-provider-connection-folder-id-choose wpforms-btn wpforms-btn-sm wpforms-btn-blue-borders">
					<?php esc_html_e( 'Select Folder', 'wpforms-google-drive' ); ?>
				</button>
			</div>
			<div class="wpforms-builder-provider-connection-block-field wpforms-builder-provider-connection-block-field-existing-not-empty js-wpforms-builder-provider-connection-block-field-existing-not-empty<# if ( _.isEmpty( data.connection.folder_id ) ) { #> wpforms-hidden<# } #>">
				<a href="#"
					class="js-wpforms-builder-google-drive-provider-connection-folder-id-view wpforms-btn wpforms-btn-sm wpforms-btn-blue-borders"
					target="_blank"
					rel="noopener noreferrer">
					<?php esc_html_e( 'View Folder on Drive', 'wpforms-google-drive' ); ?>
				</a>
				<button type="button"
						class="js-wpforms-builder-google-drive-provider-connection-folder-id-remove wpforms-btn wpforms-btn-sm wpforms-btn-red-borders">
					<?php esc_html_e( 'Remove Folder Connection', 'wpforms-google-drive' ); ?>
				</button>

			</div>
		</div>

		<div class="wpforms-builder-provider-connection-block wpforms-builder-google-drive-provider-folder-name<# if ( ! _.isEmpty( data.connection.folder_type ) && data.connection.folder_type !== 'new'  ) { #> wpforms-hidden<# } #>">
			<label for="wpforms-builder-google-drive-folder-name-field-{{ data.connection.id }}">
				<?php esc_html_e( 'Folder Name', 'wpforms-google-drive' ); ?><span class="required">*</span>
			</label>

			<input id="wpforms-builder-google-drive-folder-name-field-{{ data.connection.id }}"
					type="text"
					class="js-wpforms-builder-google-drive-provider-connection-folder-name"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][folder_name]"
					placeholder="{{ data.formName }}"
					data-default="{{ data.formName }}"
					value="{{ data.formName }}">
		</div>

		<div class="wpforms-builder-provider-connection-block wpforms-builder-google-drive-provider-files">
			<label for="js-wpforms-builder-google-drive-provider-{{ data.connection.id }}-files"><?php esc_html_e( 'File Upload Fields', 'wpforms-google-drive' ); ?></label>

			<select id="js-wpforms-builder-google-drive-provider-{{ data.connection.id }}-fields"
					class="js-wpforms-builder-google-drive-provider-connection-fields wpforms-field-map-select choicesjs-select"
					name="providers[{{ data.provider }}][{{ data.connection.id }}][fields][]"
					data-field-map-allowed="file-upload"
					data-placeholder="<?php esc_html_e( 'All File Upload Fields', 'wpforms-google-drive' ); ?>"
					multiple="multiple">

				<# _.each( data.fileUploadFields, function ( field ) { #>
					<option value="{{ field.id }}"
						<# if ( _.contains( data.connection.fields, field.id ) ) { #> selected<# } #>>
						{{ field.label }}
					</option>
				<# } ); #>
			</select>
		</div>

		{{{ data.conditional }}}

		<# const opened = wpCookies.get('wpforms_fields_group_google_drive_advanced_options_' + data.connection.id ) ? 'opened': ''; #>
		<div class="wpforms-builder-provider-connection-block">
			<div class="wpforms-panel-fields-group unfoldable {{opened}}" data-group="google_drive_advanced_options_{{ data.connection.id }}">
				<div class="wpforms-panel-fields-group-border-top"></div>
				<div class="wpforms-panel-fields-group-title"><?php esc_html_e( 'Advanced Options', 'wpforms-google-drive' ); ?><i class="fa fa-chevron-circle-right"></i>
				</div>
				<div class="wpforms-panel-fields-group-inner" <# if ( _.isEmpty( opened ) ) { #> style="display: none;" <# } #>>
					<span class="wpforms-toggle-control">
						<input type="checkbox" id="wpforms-builder-google-drive-provider-{{ data.connection.id }}-delete-local-files" name="providers[{{ data.provider }}][{{ data.connection.id }}][delete_local_files]" class="js-wpforms-builder-google-drive-provider-connection-delete-local-files wpforms-builder-google-drive-provider-connection-delete-local-files" value="1" <# if ( data.connection.delete_local_files ) { #>checked<# } #>>
						<label class="wpforms-toggle-control-icon" for="wpforms-builder-google-drive-provider-{{ data.connection.id }}-delete-local-files"></label>
						<label for="wpforms-builder-google-drive-provider-{{ data.connection.id }}-delete-local-files" class="wpforms-toggle-control-label"><?php esc_html_e( 'Delete Local Files After Upload', 'wpforms-google-drive' ); ?></label>
						<i class="fa fa-question-circle-o wpforms-help-tooltip" title="<?php echo esc_attr__( 'Once the files are successfully uploaded to Google Drive, they will be deleted from your server.', 'wpforms-google-drive' ); ?>"></i>
					</span>
					<div class="wpforms-alert wpforms-alert-danger wpforms-bottom wpforms-hidden" style="margin-top: 10px;">
						<div class="wpforms-aside-left">
							<p class="wpforms-alert-content"><?php esc_html_e( 'Enabling this setting will result in broken links in Google Sheets.', 'wpforms-google-drive' ); ?></p>
						</div>
					</div>
			</div>
		</div>
	</div>
</div>
<div></div>
