# Changelog
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [1.12.0] - 2024-12-11
### Changed
- The minimum WPForms version supported is 1.9.2.3.

### Fixed
- Amount mismatch error in case the Single Item field price was adjusted.
- Multiple partial entries were generated when old link was visited.

## [1.11.1] - 2024-06-19
### Fixed
- Custom form styles were not applied for the Save and Resume confirmation message in Elementor.
- Address data from resumed link is now correctly restored if Address Autocomplete settings are enabled.
- Save and Resume confirmation message was displayed on unrelated pages when the addon was activated.

## [1.11.0] - 2024-06-11
### Added
- Compatibility with WPForms 1.8.9.
- Save and Resume form can load signature preview when the form is resumed.

### Changed
- The minimum WPForms version supported is 1.8.9.

### Fixed
- Compatibility with the Divi page builder.
- Not all displayed elements were styled with Form Styles 2.0.
- Users with permissions to only view forms could not submit the form on the Form Preview page.

## [1.10.0] - 2024-04-24
### Added
- Compatibility with the WPForms 1.8.8.

### Fixed
- Form styles were not applied to the Save & Resume messages.

## [1.9.1] - 2024-02-29
### Fixed
- Antispam token was passed as a simple form field, not protected from spam bots.

## [1.9.0] - 2024-02-20
### Added
- Compatibility with WPForms 1.8.7.

### Fixed
- Save and Resume Later label was displayed in the builder preview area when there were no fields added.
- The Form Builder settings screen had visual issues when an RTL language was used.

## [1.8.0] - 2024-01-11
### Added
- Compatibility with WPForms 1.8.6.

### Changed
- Improve the logic of sending emails to increase performance.
- Minimum WPForms version supported is 1.8.6.

### Fixed
- Improved expiration error handling if multiple forms with Save and Resume enabled are embedded on a page.
- Form restore link could be missing from the email notification in rare cases.
- Deprecation message while submitting non-AJAX forms with enabled Save and Resume.

## [1.7.0] - 2023-11-08
### IMPORTANT
- Support for PHP 5.6 has been discontinued. If you are running PHP 5.6, you MUST upgrade PHP before installing WPForms Save and Resume 1.7.0. Failure to do that will disable WPForms Save and Resume functionality.
- Support for WordPress 5.4 and below has been discontinued. If you are running any of those outdated versions, you MUST upgrade WordPress before installing WPForms Save and Resume 1.7.0. Failure to do that will disable WPForms Save and Resume functionality.

### Added
- Compatibility with WPForms 1.8.5.

### Changed
- Minimum WPForms version supported is 1.8.5.

### Fixed
- Font family was not inherited from the theme for some elements.
- PHP Warnings were generated when the user tried to open the saved link of a deleted partial entry.
- Error message about expired link had incorrect styling.

## [1.6.0] - 2023-08-08
### Changed
- Minimum WPForms version supported is 1.8.3.

### Fixed
- The form with saved data was displayed when visiting via expired link.

## [1.5.0] - 2023-03-15
### Added
- Compatibility with the upcoming WPForms v1.8.1 release.

### Fixed
- Incorrect error message placement if multiple forms were embedded on a page.

## [1.4.1] - 2023-02-14
### Added
- WPForms 1.8.0 compatibility.

### Changed
- Disable "Resend Notifications" link on the Entry page instead of hiding it.

## [1.4.0] - 2023-01-10
### Added
- Compatibility with the Lead Forms addon.

### Fixed
- In the Firefox browser, Save and Resume link was displayed in the Form Builder even if the feature was not enabled.
- Dropdown, Multiple Choice, and Checkbox fields were forcing their corresponding default values in resumed forms.
- Form submission has expired for non-AJAX forms after submitting the form and refreshing the page.
- The resume link could have been emailed before it was generated.
- Save and Resume link was not displayed alongside the "Next" button of the new Page Break fields in the Form Builder.

## [1.3.0] - 2022-09-21
### Changed
- Minimum WPForms version is now 1.7.5.5.

### Fixed
- Save and resume link representation was broken in the Block Editor on WordPress 5.2-5.4.

## [1.2.0] - 2022-07-14
### Added
- Display partial entry expiration in the "Entry Details" metabox when viewing entry details.
- Allow copying saved entry link from the "Entry Details" metabox when viewing entry details.

### Changed
- Partial entry is now deleted immediately after completing and submitting the form.
- Minimum WPForms version supported is 1.7.5.
- Check GDPR settings before trying to use cookies.
- Partial entries do not rely on user cookies anymore.
- Improved compatibility with Twenty Twenty-Two theme and Full Site Editing (FSE).

### Fixed
- Partial entry processing had no anti-spam protection.
- Link was displayed in the Form Builder and Elementor widget preview even if the feature was not enabled.
- Incorrect saved entry link was generated on setups with mixed HTTP/HTTPS.
- Incorrect date was displayed in the resumed form.
- Form field labels were underlined when Save and Resume was enabled.
- PHP notice was generated when email notifications were sent.

## [1.1.0] - 2022-03-16
### Added
- Compatibility with WPForms 1.7.3 and Form Revisions.
- Compatibility with WPForms 1.7.3 and search functionality on the Entries page.

### Changed
- Minimum WPForms version supported is 1.7.3.

## [1.0.1] - 2021-10-28
### Fixed
- Improved Paragraph field text formatting when restored from the saved partial entry.
- Likert Scale field values haven't been restored when multiple responses are enabled.
- Properly handle empty values for the Date / Time field and its Date Dropdown format.
- Properly restore partial entries with dynamic and/or multiple choices in checkboxes and dropdowns fields.

## [1.0.0] - 2021-10-21
### Added
- Initial release.
