<?php
/**
 * Taxonomy creation form.
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @package YITH\Brands\Views
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

?>

<div class="form-field">
	<?php
	/**
	 * APPLY_FILTERS: yith_wcbr_thumbnail_label
	 *
	 * Filter the label for the field to select the brand thumbnail.
	 *
	 * @param string $label Field label
	 *
	 * @return string
	 */
	?>
	<label>
		<strong><?php echo esc_html( apply_filters( 'yith_wcbr_thumbnail_label', __( 'Thumbnail', 'yith-woocommerce-brands-add-on' ) ) ); ?></strong>
	</label>
	<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img/></div>
	<div style="line-height:60px;">
		<input type="hidden" id="product_brand_thumbnail_id" class="yith_wcbr_upload_image_id" name="product_brand_thumbnail_id" />
		<button id="product_brand_thumbnail_upload" type="button" class="yith_wcbr_upload_image_button button yith-plugin-fw__button--secondary"><?php esc_html_e( 'Upload/Add image', 'yith-woocommerce-brands-add-on' ); ?></button>
		<button id="product_brand_thumbnail_remove" type="button" class="yith_wcbr_remove_image_button button yith-plugin-fw__button--secondary"><?php esc_html_e( 'Remove image', 'yith-woocommerce-brands-add-on' ); ?></button>
	</div>
	<div class="clear"></div>
</div>
