<?php

namespace WPFormsCalculations\Admin;

use WPForms\Helpers\Transient;

/**
 * Calculations Admin class.
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Temporary debug mode transient name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const DEBUG_MODE_TRANSIENT = 'wpforms_calculations_debug';

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		add_filter( 'removable_query_args', [ $this, 'removable_query_args' ] );
	}

	/**
	 * Remove certain arguments from a query string that WordPress should always hide for users.
	 *
	 * @since 1.0.0
	 *
	 * @param array $removable_query_args An array of parameters to remove from the URL.
	 *
	 * @return array Extended/filtered array of parameters to remove from the URL.
	 */
	public function removable_query_args( $removable_query_args ) {

		// Check if we should enable tmp debug mode.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET[ self::DEBUG_MODE_TRANSIENT ] ) ) {
			return $removable_query_args;
		}

		// Check if the current user has full access to all WPForms features (admin).
		if ( wpforms_current_user_can() ) {

			// Set transient to enable debug mode for 2 hours.
			Transient::set( self::DEBUG_MODE_TRANSIENT, 1, 2 * HOUR_IN_SECONDS );
		}

		// Remove query argument.
		$removable_query_args[] = self::DEBUG_MODE_TRANSIENT;

		return $removable_query_args;
	}
}
