# Changelog
All notable changes to this project will be documented in this file and formatted via [this recommendation](https://keepachangelog.com/).

## [1.7.0] - 2025-07-08
### IMPORTANT
- Support for PHP 7.1 has been discontinued. If you are running PHP 7.1, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Added
- AI Forms can now generate forms that use WPForms Calculations with an auto-generated formula (requires WPForms 1.9.7).

## Changed
- Prompt input field now resizes when a user provides a long text.
- Updated `luxon` library to v3.6.1.

## Fixed
- Calculations involving || and && operators gave a different result in the front-end than in the computed saved entry.
- Generated formula on RTL languages had incorrect formatting.
- Generated formula in the calculation chat window could have the wrong width overflowing the dialog item.
- Dropdown, Checkboxes, and Multiple Choice fields with icons couldn't be used inside an advanced calculation formula.
- Performance issue on large forms containing numerous different fields.
- Default values of the Single Item field were reset after reloading the form builder screen.

## [1.6.0] - 2025-03-11
### Added
- AI Calculations. Now you are able to leverage AI to generate new formulas and correct issues with manually generated formulas.

### Changed
- The validation error text to a more appropriate one when the field was moved to the Repeater.

### Fixed
- Calculation formula validation was not checking correctness of the subfield key (except checkbox fields).
- Duplicated fields had incorrectly initialized calculation editor.

## [1.5.0] - 2025-02-25
### Added
- Compatibility with WPForms version 1.9.4.

### Changed
- The minimum WPForms version supported is 1.9.4.

## [1.4.1] - 2025-01-14
### IMPORTANT
- Support for PHP 7.0 has been discontinued. If you are running PHP 7.0, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Changed
- The minimum WPForms version supported is 1.9.3.

## [1.4.0] - 2024-12-03
### Fixed
- There were errors in some cases during processing the Date/Time field value used in calculation.
- Changes were lost when editing entries with Conditional logic fields and Coupons + Calculations addons enabled.
- The Payment Single Item field had a non-empty value when it was hidden by Conditional Logic.
- Calculation results of 0 was stored as an empty value in the number fields.
- Inconsistent results on frontend and backend in case of division by zero.
- A JavaScript error occurred when the field used in the formula was placed inside the Repeater field.

## [1.3.0] - 2024-09-24
### Changed
- The minimum WPForms version supported is 1.9.1.
- Updated `luxon` library to v3.5.0.

### Added
- Coupons addon compatibility.

### Fixed
- Results from calculations were not displayed in the confirmation message with a very long formula.
- Entry Preview was displaying an empty calculations result if fields being calculated were not in the same pagebreak wrap with result field.
- Conditional Logic applied for Repeater field was not reflected on single entry views.
- The `date_diff` function sometimes returned an incorrect result that did not match the result on the frontend.
- The truncate() function worked incorrectly with multibyte strings.
- The Repeater field sometimes caused console errors on the front end.
- Fields sorting changed to the form fields order in settings with field mapping and smart tags.
- It was possible to use the Total field in the Single Item field formula.
- Resolved W3C error for the Number field.
- Certain math function results had discrepancies between the front-end and back-end.
- The Single Item field displayed amount rounding discrepancies between the front-end and back-end.

## [1.2.0] - 2024-04-03
### Added
- The new filter `wpforms_calculations_process_filter` to allow post-calculation entry processing.

### Fixed
- Automatic update for the addon did not work.
- The Authorize.Net credit card field threw an error on the entry edit page.
- Payment Single item field was not included in the Entry Preview, Confirmations and Notifications.

## [1.1.0] - 2024-01-24
### Added
- Improved support and processing for the Single Item field.
- Compatibility with the upcoming WPForms v1.8.7 release.

### Changed
- Updated `nikic/php-parser` library to 4.18.0.
- Updated `luxon` library to v3.4.4.

### Fixed
- It was impossible to edit a formula after duplicating the Layout Field with the Calculation Field inside.
- In some cases, the calculated field values were inconsistent between displayed value on the front-end and saved value in the database.
- Calculations were not using correct values when the option "Show values" for selectable fields was set.
- Line breaks and other special characters were not preserved in the formula code and in the calculation result.
- The formula validation returned the false positive result in some cases when the form was not saved before validation.
- In some cases, incorrect calculation results were shown in the confirmation message.
- The Validate Formula button AJAX calls failed on the servers that do not support `$_SERVER[HTTP_REFERER]`.
- "Illegal numeric literal" error appeared in the error.log when the field value was numeric and started with 0.
- The Error Handler threw the invalid callback error in some rare cases.
- Math functions were throwing a TypeError in some rare cases.

## [1.0.0] - 2023-10-24
### Added
- Initial release.
