<?php

namespace WPFormsSaveResume\Email\Templates;

use WPForms\Emails\Templates\General as GeneralEmailTemplate;

/**
 * SaveResume email template.
 *
 * This class is no longer used and is only here for backward compatibility.
 * It will be removed in the future, so please do not use it.
 *
 * @since 1.0.0
 * @deprecated 1.7.0
 */
class SaveResume extends GeneralEmailTemplate {

	/**
	 * Template slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const TEMPLATE_SLUG = 'save-resume';

	/**
	 * Initialize class.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		// Add a deprecated notice to the constructor to alert developers about the change.
		_deprecated_function( __CLASS__, '1.7.0 of the WPForms Save and Resume Add-on' );

		// Call the parent constructor.
		parent::__construct();
	}
}
