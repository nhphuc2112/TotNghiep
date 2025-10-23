<?php
/**
 * Plugin Name:       WPForms Calculations
 * Plugin URI:        https://wpforms.com
 * Description:       Adds calculations feature to WPForms.
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.7.0
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpforms-calculations
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
 *
 * @since     1.0.0
 * @author    WPForms
 * @package   WPFormsCalculations
 * @license   GPL-2.0+
 * @copyright Copyright (c) 2023, WPForms LLC
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPFormsCalculations\Plugin;

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
const WPFORMS_CALCULATIONS_VERSION = '1.7.0';

/**
 * Plugin FILE.
 *
 * @since 1.0.0
 */
const WPFORMS_CALCULATIONS_FILE = __FILE__;

/**
 * Plugin PATH.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_CALCULATIONS_PATH', plugin_dir_path( WPFORMS_CALCULATIONS_FILE ) );

/**
 * Plugin URL.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_CALCULATIONS_URL', plugin_dir_url( WPFORMS_CALCULATIONS_FILE ) );

/**
 * Load the plugin files.
 *
 * @since 1.0.0
 */
function wpforms_calculations_load() {

	$requirements = [
		'file'    => WPFORMS_CALCULATIONS_FILE,
		'wpforms' => '1.9.4',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_calculations();
}

add_action( 'wpforms_loaded', 'wpforms_calculations_load' );

/**
 * Get the instance of the `\WPFormsCalculations\Plugin` class.
 * This function is useful for quickly grabbing data used throughout the plugin.
 *
 * @since 1.0.0
 *
 * @return Plugin
 */
function wpforms_calculations() {

	// Actually, load the addon now, as we met all the requirements.
	require_once __DIR__ . '/vendor/autoload.php';

	return Plugin::get_instance();
}
