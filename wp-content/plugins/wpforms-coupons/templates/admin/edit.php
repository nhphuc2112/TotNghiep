<?php
/**
 * Add/edit coupon template.
 *
 * @since 1.0.0
 *
 * @var Coupon $coupon      Coupon.
 * @var string $title       Page title.
 * @var string $description Page description.
 * @var string $date_format Date format.
 * @var string $time_format Time format.
 * @var string $currency    Disabled class.
 * @var array  $forms       Forms.
 */

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WPFormsCoupons\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_edit            = $coupon && ! empty( $coupon->get_id() );
$is_disabled        = $is_edit;
$disabled_class     = $is_disabled ? 'wpforms-coupon-disabled' : '';
$overview_page_url  = add_query_arg(
	'page',
	'wpforms-coupons',
	admin_url( 'admin.php' )
);
$coupon_name        = $coupon ? $coupon->get_name() : '';
$coupon_code        = $coupon ? $coupon->get_code() : '';
$coupon_amount      = $coupon ? $coupon->get_discount_amount() : '';
$coupon_type        = $coupon ? $coupon->get_discount_type() : '';
$coupon_usage_limit = $coupon ? $coupon->get_usage_limit() : null;
$coupon_usage_count = $coupon ? $coupon->get_usage_count() : '';
$coupon_start_date  = $coupon && $coupon->get_start_date_time_gmt() !== null ? wpforms_date_format( $coupon->get_start_date_time_gmt()->getTimestamp(), $date_format, true ) : '';
$coupon_start_time  = $coupon && $coupon->get_start_date_time_gmt() !== null ? wpforms_time_format( $coupon->get_start_date_time_gmt()->getTimestamp(), $time_format, true ) : '';
$coupon_end_date    = $coupon && $coupon->get_end_date_time_gmt() !== null ? wpforms_date_format( $coupon->get_end_date_time_gmt()->getTimestamp(), $date_format, true ) : '';
$coupon_end_time    = $coupon && $coupon->get_end_date_time_gmt() !== null ? wpforms_time_format( $coupon->get_end_date_time_gmt()->getTimestamp(), $time_format, true ) : '';
$allowed_forms      = $coupon ? array_keys( $coupon->get_allowed_forms() ) : [];
$is_global_active   = $coupon && $coupon->get_is_global();
$usage_counts       = $coupon ? $coupon->get_usage_counts() : [];
?>

<div id="wpforms-coupons" class="wrap wpforms-admin-wrap wpforms-admin-settings">

	<div class="wpforms-h1-placeholder"></div>

	<div class="wpforms-setting-row section-heading">
		<div class="wpforms-setting-field">
			<h4><?php echo esc_html( $title ); ?></h4>
			<p><?php echo esc_html( $description ); ?></p>
		</div>
	</div>

	<form class="wpforms-admin-settings-form" method="POST">

		<div class="wpforms-setting-row wpforms-setting-row-text">
			<div class="wpforms-setting-label">
				<label for="wpforms-coupon-name"><?php esc_html_e( 'Name', 'wpforms-coupons' ); ?> <span class="required">*</span></label>
			</div>

			<div class="wpforms-setting-field">
				<input
					id="wpforms-coupon-name"
					type="text"
					name="wpforms-coupons[name]"
					required
					value="<?php echo esc_attr( $coupon_name ); ?>"
				/>
				<p class="desc"><?php esc_html_e( 'Give your coupon a name so you can easily identify it. This is not displayed to customers.', 'wpforms-coupons' ); ?></p>
			</div>
		</div>

		<div class="wpforms-setting-row wpforms-setting-row-code">
			<div class="wpforms-setting-label">
				<label for="wpforms-coupon-code"><?php esc_html_e( 'Code', 'wpforms-coupons' ); ?> <span class="required">*</span></label>
			</div>

			<div class="wpforms-setting-field">
				<div class="wpforms-coupon-code-wrapper">
					<input
						id="wpforms-coupon-code"
						type="text"
						name="wpforms-coupons[code]"
						required
						value="<?php echo esc_attr( $coupon_code ); ?>"
						<?php disabled( true, $is_disabled ); ?>
					/>
					<button
						type="button"
						class="wpforms-btn wpforms-btn-md wpforms-btn-blue wpforms-btn-coupon-generate"
						<?php disabled( true, $is_disabled ); ?>
					>
						<?php esc_html_e( 'Generate Code', 'wpforms-coupons' ); ?>
					</button>
				</div>
				<p class="desc"><?php esc_html_e( 'The code customers will enter to receive a discount.', 'wpforms-coupons' ); ?></p>
			</div>
		</div>

		<div class="wpforms-setting-row wpforms-setting-row-amount">
			<div class="wpforms-setting-label">
				<label for="wpforms-coupon-amount"><?php esc_html_e( 'Amount', 'wpforms-coupons' ); ?> <span class="required">*</span></label>
			</div>

			<div class="wpforms-setting-field">
				<div class="wpforms-coupon-amount-select<?php echo $is_disabled ? ' is-disabled' : ''; ?>">
					<input
						id="wpforms-coupon-amount"
						type="number"
						name="wpforms-coupons[discount_amount]"
						step="0.01"
						required
						value="<?php echo esc_attr( $coupon_amount ); ?>"
						<?php disabled( true, $is_disabled ); ?>
					/>
					<select
						id="wpforms-coupons-amount-type"
						aria-label="<?php esc_html_e( 'The coupon amount type: flat or percentage', 'wpforms-coupons' ); ?>"
						name="wpforms-coupons[discount_type]"
						class="choicesjs-select"<?php disabled( true, $is_disabled ); ?>
					>
						<option value="percentage" <?php selected( 'percentage', $coupon_type ); ?>>%</option>
						<option value="flat" <?php selected( 'flat', $coupon_type ); ?>><?php echo esc_attr( $currency ); ?></option>
					</select>
				</div>
				<p class="desc">
					<?php
					echo esc_html(
						sprintf( /* translators: %s - currency symbol. */
							__( 'The amount of the discount as a percentage (%%) or fixed amount (%s). Cannot be left blank.', 'wpforms-coupons' ),
							$currency
						)
					);
					?>
				</p>
			</div>
		</div>

		<div id="wpforms-coupon-datetime-block">
			<div class="wpforms-setting-row wpforms-setting-row-date">
				<div class="wpforms-setting-label">
					<label for="wpforms-coupons-start_date"><?php esc_html_e( 'Start Date / Time', 'wpforms-coupons' ); ?></label>
				</div>

				<div class="wpforms-setting-field">
					<div class="wpforms-coupon-datepicker-wrapper">
						<div class="wpforms-coupon-datepicker-container">
							<input
								id="wpforms-coupons-start_date"
								type="text"
								name="wpforms-coupons[start_date]"
								class="wpforms-datepair-date wpforms-datepair-start"
								value="<?php echo esc_attr( $coupon_start_date ); ?>"
							/>
							<button type="button" class="wpforms-clear-datetime-field" title="<?php esc_html_e( 'Clear Start Date', 'wpforms-coupons' ); ?>">
								<i class="fa fa-times-circle fa-lg"></i>
							</button>
						</div>
						<div class="wpforms-coupon-timepicker-container">
							<input
								aria-label="<?php esc_html_e( 'Start Coupon Time', 'wpforms-coupons' ); ?>"
								type="text" name="wpforms-coupons[start_time]" class="wpforms-datepair-time"
								id="wpforms-coupons-start_time"
								value="<?php echo esc_attr( $coupon_start_time ); ?>"
							/>
							<button type="button" class="wpforms-clear-datetime-field" title="<?php esc_html_e( 'Clear Start Time', 'wpforms-coupons' ); ?>">
								<i class="fa fa-times-circle fa-lg"></i>
							</button>
						</div>
					</div>
					<p class="desc"><?php esc_html_e( 'Set the date and time this discount will start on. Leave blank for no start date.', 'wpforms-coupons' ); ?></p>
				</div>
			</div>

			<div class="wpforms-setting-row wpforms-setting-row-date">
				<div class="wpforms-setting-label">
					<label for="wpforms-coupons-end_date"><?php esc_html_e( 'End Date / Time', 'wpforms-coupons' ); ?></label>
				</div>

				<div class="wpforms-setting-field">
					<div class="wpforms-coupon-datepicker-wrapper">
						<div class="wpforms-coupon-datepicker-container">
							<input
								id="wpforms-coupons-end_date"
								type="text"
								name="wpforms-coupons[end_date]"
								class="wpforms-datepair-date wpforms-datepair-end"
								value="<?php echo esc_attr( $coupon_end_date ); ?>"
							/>
							<button type="button" class="wpforms-clear-datetime-field" title="<?php esc_html_e( 'Clear End Date', 'wpforms-coupons' ); ?>">
								<i class="fa fa-times-circle fa-lg"></i>
							</button>
						</div>
						<div class="wpforms-coupon-timepicker-container">
							<input
								aria-label="<?php esc_html_e( 'End Coupon Time', 'wpforms-coupons' ); ?>"
								type="text" name="wpforms-coupons[end_time]" class="wpforms-datepair-time"
								id="wpforms-coupons-end_time"
								value="<?php echo esc_attr( $coupon_end_time ); ?>"
							/>
							<button type="button" class="wpforms-clear-datetime-field" title="<?php esc_html_e( 'Clear End Time', 'wpforms-coupons' ); ?>">
								<i class="fa fa-times-circle fa-lg"></i>
							</button>
						</div>
					</div>
					<p class="desc"><?php esc_html_e( 'Set the date and time this discount will end on. Leave blank for no end date.', 'wpforms-coupons' ); ?></p>
				</div>
			</div>
		</div>

		<div class="wpforms-setting-row wpforms-setting-row-number">
			<div class="wpforms-setting-label">
				<label for="wpforms-coupons-usage_limit"><?php esc_html_e( 'Max Uses', 'wpforms-coupons' ); ?></label>
			</div>

			<div class="wpforms-setting-field">
				<input
					id="wpforms-coupons-usage_limit"
					type="number"
					name="wpforms-coupons[usage_limit]"
					min="0"
					step="1"
					max="65535"
					value="<?php echo esc_attr( $coupon_usage_limit ); ?>"
				/>
				<p class="desc"><?php esc_html_e( 'The total number of times this coupon can be used.', 'wpforms-coupons' ); ?></p>
			</div>
		</div>

		<hr>

		<div class="wpforms-setting-row section-heading" id="wpforms-coupons-allowed-table">
			<h4><?php esc_html_e( 'Allowed Forms', 'wpforms-coupons' ); ?></h4>
			<p id="wpforms-coupons-allowed-table-description"><?php esc_html_e( 'Specify which forms your coupon can be used on and view valuable statistics.', 'wpforms-coupons' ); ?></p>
			<?php if ( ! $is_edit ) { ?>
				<div class="wpforms-coupons-notice notice-info">
					<p><?php esc_html_e( 'Make sure you enable your coupon for at least one form.', 'wpforms-coupons' ); ?></p>
				</div>
			<?php } ?>

			<?php if ( $is_edit && ( ! $is_global_active && empty( $allowed_forms ) ) ) { ?>
				<div class="wpforms-coupons-notice notice-error">
					<p><?php esc_html_e( 'Your coupon is not enabled for any forms. Your customers will not be able to use it.', 'wpforms-coupons' ); ?></p>
				</div>
			<?php } ?>
		</div>

		<div class="wpforms-setting-row-content">
			<div  class="wpforms-coupon-allowed-forms">
				<div class="wpforms-setting-row">
					<table class="wp-list-table widefat striped wpforms-table-list table-view-list" aria-describedby="wpforms-coupons-allowed-table-description">
						<thead>
						<tr>
							<th scope="col" style="width: 5%">
										<span class="wpforms-coupon-toggle-control wpforms-coupon-toggle-control-global" title="<?php esc_html_e( 'Allow All Forms', 'wpforms-coupons' ); ?>">
											<input type="checkbox"
												id="wpforms-coupons-is_global"
												name="wpforms-coupons[is_global]"
												value="1"
												<?php checked( true, $is_global_active ); ?>
											/>
											<label for="wpforms-coupons-is_global" class="wpforms-coupon-toggle-control-icon"></label>
										</span>
							</th>
							<th scope="col"><?php esc_html_e( 'Form', 'wpforms-coupons' ); ?></th>
							<th scope="col" class="wpforms-coupon-table-center wpforms-coupon-small-column"><?php esc_html_e( 'Payments', 'wpforms-coupons' ); ?></th>
							<th scope="col" class="wpforms-coupon-table-center wpforms-coupon-small-column-reverse"><?php esc_html_e( 'Coupon Uses', 'wpforms-coupons' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach ( $forms as $key => $form_data ) {
							$classes     = [];
							$form_id     = $form_data['id'];
							$is_disabled = in_array( $form_data['status'], [ 'deleted', 'trash' ], true );
							$is_checked  = $is_global_active || ( $allowed_forms && in_array( $form_id, $allowed_forms, true ) );

							if ( $form_data['status'] === 'trash' ) {
								$tr_title = __( 'This form is in Trash', 'wpforms-coupons' );
							} elseif ( $form_data['status'] === 'deleted' ) {
								$tr_title = __( 'This form was deleted', 'wpforms-coupons' );
							} else {
								$tr_title = '';
							}

							$is_disabled ? array_push( $classes, 'wpforms-coupon-disabled' ) : [];
							$key > 9 ? array_push( $classes, 'wpforms-coupons-hidden' ) : [];
							?>
							<tr class="<?php echo wpforms_sanitize_classes( $classes, true ); ?>" title="<?php echo esc_html( $tr_title ); ?>">
								<td>
									<div class="switch-container">
											<span class="wpforms-coupon-toggle-control">
												<?php if ( ! $is_disabled ) : ?>
												<input type="checkbox"
													id="wpforms-coupon-allowed-<?php echo absint( $form_id ); ?>"
													name="wpforms-coupons[allowed_forms][]"
													value="<?php echo absint( $form_id ); ?>"
													<?php checked( true, $is_checked ); ?>
												/>
												<?php endif; ?>
												<label for="wpforms-coupon-allowed-<?php echo absint( $form_id ); ?>" class="wpforms-coupon-toggle-control-icon"></label>
											</span>
									</div>
								</td>
								<td>
									<?php
									$template_label = wpforms_is_form_template( $form_id ) ? '<span> &mdash; ' . esc_html__( 'Template', 'wpforms-coupons' ) . '</span>' : '';

									printf(
										'<a href="%1$s" title="%2$s" target="_blank">%3$s</a>%4$s',
										esc_url(
											add_query_arg(
												[
													// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
													'page'    => 'wpforms-builder',
													'view'    => 'fields',
													'form_id' => $form_id,
													// phpcs:enable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
												],
												admin_url( 'admin.php' )
											)
										),
										esc_attr__( 'Allow using the coupon in the form', 'wpforms-coupons' ),
										esc_html( $form_data['title'] ),
										wp_kses( $template_label, [ 'span' => [] ] )
									);
									?>
								</td>
								<td class="wpforms-coupon-table-center"><?php echo ! empty( $usage_counts[ $form_id ]['payments_count'] ) ? absint( $usage_counts[ $form_id ]['payments_count'] ) : 0; ?></td>
								<td class="wpforms-coupon-table-center"><?php echo ! empty( $usage_counts[ $form_id ]['usage_count'] ) ? absint( $usage_counts[ $form_id ]['usage_count'] ) : 0; ?></td>
							</tr>
						<?php } ?>
						</tbody>
						<tfoot>
						<tr>
							<th colspan="4">
								<?php
								$coupon_usage_limit = ! empty( $coupon_usage_limit ) ? $coupon_usage_limit : __( 'Unlimited', 'wpforms-coupons' );

								printf( '%d / %s', absint( $coupon_usage_count ), esc_html( $coupon_usage_limit ) );
								?>
							</th>
						</tr>
						</tfoot>
					</table>
					<?php
					$show_all_title = sprintf( /* translators: %d all forms in table count. */
						esc_html__( 'Show all %d forms', 'wpforms-coupons' ),
						absint( count( $forms ) )
					);

					echo count( $forms ) > 10 ? sprintf(
						'<a href="#" rel="noopener noreferrer" class="wpforms-coupons-show-all-forms" title="">%1$s</a>',
						esc_attr( $show_all_title )
					) : ''
					?>
				</div>
			</div>
		</div>

		<p class="submit">
			<button type="submit" class="wpforms-btn wpforms-btn-md wpforms-btn-orange wpforms-coupons-submit">
				<?php
				echo $is_edit
					? esc_html( __( 'Update Coupon', 'wpforms-coupons' ) )
					: esc_html( __( 'Save Coupon', 'wpforms-coupons' ) );
				?>
			</button>
			<?php
			if ( $is_edit ) {
				$url        = $coupon_usage_count > 0 ? $coupon->get_archive_url() : $coupon->get_trash_url();
				$link_title = $coupon_usage_count > 0 ? __( 'Archive Coupon', 'wpforms-coupons' ) : __( 'Delete Coupon', 'wpforms-coupons' );

				printf(
					'<a href="%1$s" rel="noopener noreferrer" class="wpforms-coupons-delete">%2$s</a>',
					esc_url( $url ),
					esc_html( $link_title )
				);
			}
			?>

		</p>
		<input type="hidden" name="action" value="<?php echo $is_edit ? 'edit' : 'add'; ?>" id="wpforms-coupons-page-type">
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpforms-coupons-nonce' ) ); ?>">
	</form>
</div>
