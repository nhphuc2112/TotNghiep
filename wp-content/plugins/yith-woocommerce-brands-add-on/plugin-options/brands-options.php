<?php
/**
 * Brands tab
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Brands\PluginOptions
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCBR' ) ) {
	exit;
} // Exit if accessed directly

$table = array(
	'brands' => array(
		'brands_list_table' => array(
			'type'          => 'taxonomy',
			'taxonomy'      => YITH_WCBR::$default_taxonomy,
			'wp-list-style' => 'classic',
		),
	),
);

return $table;
