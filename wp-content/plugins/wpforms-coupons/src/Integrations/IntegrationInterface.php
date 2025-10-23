<?php

namespace WPFormsCoupons\Integrations;

/**
 * Interface integration.
 * Defines required methods for integrations to work properly.
 *
 * @since 1.3.1
 */
interface IntegrationInterface {

	/**
	 * Check if the current integration is allowed to load.
	 *
	 * @since 1.3.1
	 *
	 * @return bool Whether the integration is allowed to load.
	 */
	public function allow_load(): bool;

	/**
	 * Register hooks for the integration.
	 *
	 * @since 1.3.1
	 */
	public function hooks();
}
