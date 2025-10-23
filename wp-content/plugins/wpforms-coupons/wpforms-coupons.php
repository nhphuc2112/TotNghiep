<?php
/**
 * Plugin Name:       WPForms Coupons
 * Plugin URI:        https://wpforms.com
 * Description:       Create Coupons with WPForms.
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.6.0
 * Requires at least: 5.5
 * Requires PHP:      7.1
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-coupons
 * Domain Path:       /languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

use WPFormsCoupons\Install;
use WPFormsCoupons\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Version.
 *
 * @since 1.0.0
 */
const WPFORMS_COUPONS_VERSION = '1.6.0';

/**
 * Plugin FILE.
 *
 * @since 1.0.0
 */
const WPFORMS_COUPONS_FILE = __FILE__;

/**
 * Plugin PATH.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_COUPONS_PATH', plugin_dir_path( WPFORMS_COUPONS_FILE ) );

/**
 * Plugin URL.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_COUPONS_URL', plugin_dir_url( WPFORMS_COUPONS_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 1.0.0
 * @since 1.1.0 Uses requirements feature.
 */
function wpforms_coupons_load() {

	$requirements = [
		'file'    => WPFORMS_COUPONS_FILE,
		'wpforms' => '1.9.4',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_coupons();
}
add_action( 'wpforms_loaded', 'wpforms_coupons_load' );

/**
 * Get the instance of the `\WPFormsCoupons\Plugin` class.
 *
 * @since 1.0.0
 *
 * @return Plugin
 */
function wpforms_coupons(): Plugin {

	// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
	return Plugin::get_instance();
}

require_once WPFORMS_COUPONS_PATH . 'vendor/autoload.php';

// Load installation things immediately for a reason how activation hook works.
new Install();
