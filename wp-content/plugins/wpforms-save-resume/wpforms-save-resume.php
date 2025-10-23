<?php
/**
 * Plugin Name:       WPForms Save and Resume
 * Plugin URI:        https://wpforms.com
 * Description:       Save partial entries and resume them later with WPForms.
 * Requires at least: 5.5
 * Requires PHP:      7.0
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.12.0
 * Text Domain:       wpforms-save-resume
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

use WPFormsSaveResume\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
const WPFORMS_SAVE_RESUME_VERSION = '1.12.0';

/**
 * Plugin file.
 *
 * @since 1.0.0
 */
const WPFORMS_SAVE_RESUME_FILE = __FILE__;

/**
 * Plugin path.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_SAVE_RESUME_PATH', plugin_dir_path( WPFORMS_SAVE_RESUME_FILE ) );

/**
 * Plugin URL.
 *
 * @since 1.0.0
 */
define( 'WPFORMS_SAVE_RESUME_URL', plugin_dir_url( WPFORMS_SAVE_RESUME_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 1.0.0
 * @since 1.6.0 Uses requirements feature.
 */
function wpforms_save_resume_load() {

	$requirements = [
		'file'    => WPFORMS_SAVE_RESUME_FILE,
		'wpforms' => '1.9.2.3',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_save_resume();
}

add_action( 'wpforms_loaded', 'wpforms_save_resume_load' );

/**
 * Get the instance of the addon main class.
 *
 * @since 1.0.0
 *
 * @return Plugin
 */
function wpforms_save_resume() {

	require_once WPFORMS_SAVE_RESUME_PATH . 'vendor/autoload.php';

	return Plugin::get_instance();
}
