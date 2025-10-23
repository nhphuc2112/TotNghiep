# Changelog
All notable changes to this project will be documented in this file and formatted via [this recommendation](https://keepachangelog.com/en/1.0.0/).

## [1.6.0] - 2025-02-25
### IMPORTANT
- Support for PHP 7.0 has been discontinued. If you are running PHP 7.0, you MUST upgrade PHP before installing this addon. Failure to do that will disable addon functionality.

### Changed
- The minimum WPForms version supported is 1.9.4.

### Fixed
- Changes were not preserved when editing Entries belonging to Forms with Conditional Logic fields in combination with the Coupons and Calculations addons

## [1.5.0] - 2024-09-24
### Added
- Calculations addon compatibility.

### Changed
- The minimum WPForms version supported is 1.9.1.

### Fixed
- Fixed a potential PHP fatal error that occurred when the Coupon field was hidden by conditional logic.
- Corrected the calculation of discounts passed to the Square dashboard for currencies with no decimal places (zero-decimal currencies).

## [1.4.0] - 2024-08-06
### Changed
- The minimum WPForms version supported is 1.9.0.
- Improved compatibility with 3rd-party plugins.

### Fixed
- RTL support for the Coupon field.
- MySQL errors might have occurred when creating a table in some unique configurations.
- Hide "Press to select" label from the Allowed Coupons dropdown in Form Builder.

## [1.3.1] - 2024-04-25
### Added
- A label to indicate the User Template in the list of allowed forms for the coupon.

### Fixed
- The total amount was sometimes incorrect when the percentage coupon type was applied.
- Incorrect discount was applied for Square payments when the amount was a fractional value.
- Compatibility with the Divi page builder.

## [1.3.0] - 2024-04-16
### Changed
- The minimum WPForms version supported is 1.8.8.

### Added
- Compatibility with the WPForms 1.8.8.

### Fixed
- The form was not saved in the Form Builder when following the prompt to create the first Coupon.
- Time input fields appeared too narrow on the Edit Coupon page.
- RTL problems in the form builder.
- Removed redundant DB queries from the addon happening on non-related admin pages.
- Discount amount was wrong when the form contains calculated payment fields.

## [1.2.0] - 2024-02-20
### Added
- Compatibility with the WPForms 1.8.7.

### Changed
- The minimum WPForms version supported is 1.8.7.
- Added Coupon field to allowed fields for use in `wpforms_get_form_fields()` function.
- Improve Coupons page display on mobile devices.

### Fixed
- Space between Currency and Amount in Coupon field was removed.
- Various issues in the user interface when an RTL language was used.

## [1.1.0] - 2023-09-26
### IMPORTANT
- Support for PHP 5.6 has been discontinued. If you are running PHP 5.6, you MUST upgrade PHP before installing WPForms Coupons 1.1.0. Failure to do that will disable WPForms Coupons functionality.
- Support for WordPress 5.4 and below has been discontinued. If you are running any of those outdated versions, you MUST upgrade WordPress before installing WPForms Coupons 1.1.0. Failure to do that will disable WPForms Coupons functionality.

### Changed
- Minimum WPForms version supported is 1.8.4.
- The Coupon field has an improved preview in the Form Builder.
- Front-end validation and the process of applying a coupon has a better UX.

### Fixed
- Smart Logic was not working when the Coupon field value was used to show/hide other fields.
- Show an Allowed Forms error notice if there is no enabled form, only if the coupon is already created.
- The coupon was not applied when the maximum limit was reached and then increased again.
- The `wpforms_coupons_admin_coupons_edit_date_format` filter was not changing the date format on the Edit Coupon page.
- The Coupon field became unavailable in the Form Builder if drag-n-drop action started and stopped.

## [1.0.0] - 2023-06-28
### Added
- Initial release.
