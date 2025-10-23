/* global WPFormsCalculations, wpforms_calculations */

/**
 * WPForms Calculation feature.
 *
 * Functions module.
 *
 * @since 1.0.0
 *
 * @return {Object} Functions module.
 */
export default function() { // eslint-disable-line max-lines-per-function
	/**
	 * Date and Time helper functions.
	 *
	 * @since 1.0.0
	 */
	const DateTime = {

		/**
		 * Get date and time string format by comparing date time string with the values of the datetime fields of the form.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} dateTimeStr The date and time string.
		 *
		 * @return {string} Luxon date time string format.
		 */
		getFormatFromFields( dateTimeStr ) { // eslint-disable-line complexity
			const args = WPFormsCalculations.getFieldFormulaArgs();
			const fieldsValues = WPFormsCalculations.getFormFieldsValuesFromRegistry( args.formId );

			if ( ! fieldsValues ) {
				return '';
			}

			const fieldsData = WPFormsCalculations.getFormFieldsData( args.formId );

			let format = '';

			// Attempt to find format by comparing date time string with the values of the datetime fields of the form.
			for ( const fieldId in fieldsData ) {
				if ( ! fieldsData.hasOwnProperty( fieldId ) ) {
					continue;
				}

				const fieldData = fieldsData[ fieldId ];

				if ( fieldData.type !== 'date-time' ) {
					continue;
				}

				if (
					fieldsValues[ fieldId ].value !== dateTimeStr &&
					fieldsValues[ fieldId ].date !== dateTimeStr &&
					fieldsValues[ fieldId ].time !== dateTimeStr
				) {
					continue;
				}

				if ( fieldData.format === 'date' || fieldsValues[ fieldId ].date === dateTimeStr ) {
					format = fieldData.date_format;
					break;
				}

				if ( fieldData.format === 'time' || fieldsValues[ fieldId ].time === dateTimeStr ) {
					format = fieldData.time_format;
					break;
				}

				if ( fieldData.format === 'date-time' ) {
					format = fieldData.date_format + ' ' + fieldData.time_format;
					break;
				}
			}

			return format;
		},

		/**
		 * Return date and time string format by comparing with now() in default formats.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} dateTimeStr The date and time string.
		 *
		 * @return {string} Luxon date time string format.
		 */
		getFormatFromNow( dateTimeStr ) {
			for ( const format of window.wpforms_calculations.datetimeDefaultFormats ) {
				const now = DateTime.now( format );

				if ( now === dateTimeStr ) {
					return format;
				}
			}

			return '';
		},

		/**
		 * Return date and time string format by testing default formats.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} dateTimeStr The date and time string.
		 *
		 * @return {string} Luxon date time string format.
		 */
		getFormatFromDefaults( dateTimeStr ) {
			for ( const format of window.wpforms_calculations.datetimeDefaultFormats ) {
				const convertedFormat = DateTime.convertFormat( format );
				const date = window.WPFormsLuxon.DateTime.fromFormat( dateTimeStr, convertedFormat );

				if ( date.isValid ) {
					return format;
				}
			}

			return '';
		},

		/**
		 * Return date and time string format by comparing field values.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} dateTimeStr The date and time string.
		 *
		 * @return {string} Luxon date time string format.
		 */
		getFormatByValue( dateTimeStr ) {
			let format = DateTime.getFormatFromFields( dateTimeStr );

			// If still no format - attempt to get format comparing to the result of now() in different formats.
			if ( ! format.length ) {
				format = DateTime.getFormatFromNow( dateTimeStr );
			}

			// If still no format - loop through the default formats and try to find the right one.
			if ( ! format.length ) {
				format = DateTime.getFormatFromDefaults( dateTimeStr );
			}

			return DateTime.convertFormat( format );
		},

		/**
		 * Returns Luxon DateTime format from given WPForms format.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} format WPForms date and time format.
		 *
		 * @return {string} Luxon date time string format.
		 */
		convertFormat( format ) {
			if ( ! format || ! format.length ) {
				return '';
			}

			const formatConvert = {
				d : 'dd',
				D : 'ccc',
				j : 'd',
				l : 'cccc',
				N : 'c',
				S : '',
				w : '',
				z : 'o',
				W : 'W',
				F : 'MMMM',
				m : 'MM',
				M : 'MMM',
				n : 'M',
				t : '',
				L : '',
				o : 'kkkk',
				Y : 'yyyy',
				y : 'yy',
				a : 'a',
				A : 'a',
				B : '',
				g : 'h',
				G : 'H',
				h : 'hh',
				H : 'HH',
				i : 'mm',
				s : 'ss',
				u : 'SSS',
				e : 'ZZZZZ',
				I : '',
				O : '',
				P : '',
				T : '',
				Z : '',
				c : '',
				r : '',
				U : 'X',
			};

			const result = format.split( '' ).map( function( chr ) {
				return chr in formatConvert ? formatConvert[ chr ] : chr;
			} );

			return result.join( '' );
		},

		/**
		 * Returns Luxon DateTime object from given date string.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} dateTimeStr The date and time string.
		 * @param {string} format      Date and time format. Empty string means that we will try to determine the right format automatically.
		 *
		 * @return {Object} Luxon DateTime object.
		 */
		parse( dateTimeStr, format = '' ) {
			// Attempt to find format by date time string value in the case if format is not provided.
			format = format === '' ? DateTime.getFormatByValue( dateTimeStr ) : DateTime.convertFormat( format );

			return window.WPFormsLuxon.DateTime.fromFormat( dateTimeStr, format );
		},

		/**
		 * Calculate time range length in units.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} units  Units: years, months, weeks, days, hours, minutes, seconds.
		 * @param {string} format Date and time string format.
		 *
		 * @return {number|string} Time range length in units.
		 */
		diff( start, end, units = 'days', format = '' ) { // eslint-disable-line complexity
			const dateStart = DateTime.parse( start, format ),
				dateEnd = DateTime.parse( end, format );

			// Bail if some dates are invalid.
			if ( ! dateStart.isValid || ! dateEnd.isValid ) {
				return '';
			}

			const diff = dateEnd.diff( dateStart, units ),
				diffUnits = diff.as( units );

			// Negative diff means that start date is greater than end date.
			// Ceil negative diff to get correct result.
			return diff > 0 ? Math.floor( diffUnits ) : Math.ceil( diffUnits );
		},

		/**
		 * Returns current date and time.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} format Date and time string format. Empty string means default format: `d-m-Y H:i`.
		 *
		 * @return {string} Current date and time string.
		 */
		now( format = '' ) {
			// Use default format if format is not provided.
			format = format === '' ? wpforms_calculations.datetimeDefaultFormat : format;

			const nowDateTime = window.WPFormsLuxon.DateTime.now();
			let	dateTimeZone = nowDateTime.setZone( wpforms_calculations.timeZone );

			if ( ! dateTimeZone.isValid ) {
				dateTimeZone = nowDateTime.setZone( 'system' );
			}

			let now = dateTimeZone.toFormat( DateTime.convertFormat( format ) );

			// Convert AM/PM to lowercase if format contains 'a'.
			// We need this because Luxon doesn't support lowercase AM/PM.
			if ( format.includes( 'a' ) ) {
				now = now.replaceAll( 'AM', 'am' ).replaceAll( 'PM', 'pm' );
			}

			return now;
		},
	};

	/**
	 * Default functions.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}.
	 */
	const defaultFunctions = {

		/**
		 * Returns the absolute value of num.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The argument to process.
		 *
		 * @return {number} The absolute value of num.
		 */
		abs( num ) {
			num = WPFormsCalculations.innerFunctions.parseFloat( num );

			return Math.abs( num );
		},

		/**
		 * Returns the average value of given values.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} values Values.
		 *
		 * @return {number} The average value of given values.
		 */
		// eslint-disable-next-line no-unused-vars
		average( ...values ) {
			return values.reduce( ( x, y ) => WPFormsCalculations.innerFunctions.plus( x, y ) ) / arguments.length;
		},

		/**
		 * Returns the next highest integer value by rounding up num if necessary.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The argument to process.
		 *
		 * @return {number} The next highest integer value by rounding up num if necessary.
		 */
		ceil( num ) {
			return Math.ceil( WPFormsCalculations.innerFunctions.parseFloat( num ) );
		},

		/**
		 * Output debug data to console.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} msg Debug data.
		 *
		 * @return {number} Always returns 0.
		 */
		debug( ...msg ) {
			WPFormsCalculations.debug( ...msg );

			return 0;
		},

		/**
		 * Calculates the exponent of e.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The argument to process.
		 *
		 * @return {number} The exponent of e.
		 */
		exp( num ) {
			return WPFormsCalculations.innerFunctions.round(
				Math.exp( WPFormsCalculations.innerFunctions.parseFloat( num ) )
			);
		},

		/**
		 * Round fractions down.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The argument to process.
		 *
		 * @return {number} The rounded value.
		 */
		floor( num ) {
			return Math.floor( WPFormsCalculations.innerFunctions.parseFloat( num ) );
		},

		/**
		 * Natural logarithm.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The value to calculate the logarithm for.
		 *
		 * @return {number} The natural logarithm of num.
		 */
		ln( num ) {
			return WPFormsCalculations.innerFunctions.round(
				Math.log( WPFormsCalculations.innerFunctions.parseFloat( num ) )
			);
		},

		/**
		 * Base-10 logarithm.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The value to calculate the logarithm for.
		 *
		 * @return {number} The base-10 logarithm of num.
		 */
		log( num ) {
			return WPFormsCalculations.innerFunctions.round(
				Math.log10( WPFormsCalculations.innerFunctions.parseFloat( num ) )
			);
		},

		/**
		 * Find highest value.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} values Values.
		 *
		 * @return {number} The highest value.
		 */
		max( ...values ) {
			values = values.map( ( num ) => WPFormsCalculations.innerFunctions.parseFloat( num ) );

			return Math.max( ...values );
		},

		/**
		 * Find lowest value.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} values Values.
		 *
		 * @return {number} The lowest value.
		 */
		min( ...values ) {
			values = values.map( ( num ) => WPFormsCalculations.innerFunctions.parseFloat( num ) );

			return Math.min( ...values );
		},

		/**
		 * Convert string to a number.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} str       String to convert.
		 * @param {number} precision Round number to $precision decimal digits. Default is 12. Optional.
		 *
		 * @return {number} The converted number.
		 */
		num( str, precision = 12 ) {
			return WPFormsCalculations.innerFunctions.parseFloat( str, precision );
		},

		/**
		 * Get the value of pi.
		 *
		 * @since 1.0.0
		 *
		 * @return {number} The value of pi.
		 */
		pi() {
			return WPFormsCalculations.innerFunctions.round( Math.PI );
		},

		/**
		 * Return base raised to the power of exponent.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} base     The base.
		 * @param {number} exponent The exponent.
		 *
		 * @return {number} The base raised to the power of exponent.
		 */
		pow( base, exponent ) {
			return WPFormsCalculations.innerFunctions.round(
				Math.pow( WPFormsCalculations.innerFunctions.parseFloat( base ), WPFormsCalculations.innerFunctions.parseFloat( exponent ) )
			);
		},

		/**
		 * Generate a random integer.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} min The lowest value to return (default: 0).
		 * @param {number} max The highest value to return (default: 2147483647).
		 *
		 * @return {number} A random integer.
		 */
		rand( min = 0, max = 2147483647 ) {
			min = Math.ceil( WPFormsCalculations.innerFunctions.parseFloat( min ) );
			max = Math.floor( WPFormsCalculations.innerFunctions.parseFloat( max ) );

			return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
		},

		/**
		 * Rounds a float.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num       The value to round.
		 * @param {number} precision The optional number of decimal digits to round to.
		 *
		 * @return {number} The rounded value.
		 */
		round( num, precision = 0 ) {
			return WPFormsCalculations.innerFunctions.round(
				WPFormsCalculations.innerFunctions.parseFloat( num ),
				WPFormsCalculations.innerFunctions.parseFloat( precision )
			);
		},

		/**
		 * Square root.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num The argument to process.
		 *
		 * @return {number} The square root of num.
		 */
		sqrt( num ) {
			return WPFormsCalculations.innerFunctions.round(
				Math.sqrt( WPFormsCalculations.innerFunctions.parseFloat( num ) )
			);
		},

		/**
		 * Strips whitespace (or other characters) from the beginning and end of the string.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} str The string to process.
		 *
		 * @return {string} Trimmed string.
		 */
		trim( str ) {
			return str.trim();
		},

		/**
		 * Returns the first length characters of the string.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} str    The string to process.
		 * @param {number} length String length limit.
		 *
		 * @return {string} Truncated string.
		 */
		truncate( str, length ) {
			return str.substring( 0, length );
		},

		/**
		 * Concatenates all arguments str1, str2 … strN to one string.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} strs Strings to concatenate.
		 *
		 * @return {string} Concatenated string.
		 */
		concat( ...strs ) {
			return ''.concat( ...strs );
		},

		/**
		 * Join arguments str1, str2 … strN to one string with a separator.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} separator Separator.
		 * @param {string} strs      Strings to concatenate.
		 *
		 * @return {string} Concatenated string.
		 */
		join( separator, ...strs ) {
			return strs.filter( ( str ) => str && str.toString().length ).join( separator );
		},

		/**
		 * Format amount with the currency symbol.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} amount Amount to format.
		 * @param {number}        symbol Whether display the currency symbol or not. Values: `1` or `0`. Default is `1`.
		 *
		 * @return {string} Formatted amount.
		 */
		format_amount( amount, symbol = 1 ) { // eslint-disable-line camelcase
			return symbol
				? window.wpforms.amountFormatSymbol( WPFormsCalculations.innerFunctions.parseFloat( amount ) )
				: window.wpforms.amountFormat( WPFormsCalculations.innerFunctions.parseFloat( amount ) );
		},

		/**
		 * Returns current date and time.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} format Date and time string format. Empty string means default format: `d-m-Y H:i`.
		 *
		 * @return {string} Current datetime string.
		 */
		now( format = '' ) {
			return DateTime.now( format );
		},

		/**
		 * Calculate time range length in units.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} units  Units: years, months, weeks, days, hours, minutes, seconds.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in units.
		 */
		date_diff( start, end, units = 'days', format = '' ) { // eslint-disable-line camelcase
			return DateTime.diff( start, end, units, format );
		},

		/**
		 * Calculate time range length in years.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in years.
		 */
		years( start, end, format = '' ) {
			return DateTime.diff( start, end, 'years', format );
		},

		/**
		 * Calculate time range length in months.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in months.
		 */
		months( start, end, format = '' ) {
			return DateTime.diff( start, end, 'months', format );
		},

		/**
		 * Calculate time range length in weeks.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in weeks.
		 */
		weeks( start, end, format = '' ) {
			return DateTime.diff( start, end, 'weeks', format );
		},

		/**
		 * Calculate time range length in days.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in days.
		 */
		days( start, end, format = '' ) {
			return DateTime.diff( start, end, 'days', format );
		},

		/**
		 * Calculate time range length in hours.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in hours.
		 */
		hours( start, end, format = '' ) {
			return DateTime.diff( start, end, 'hours', format );
		},

		/**
		 * Calculate time range length in minutes.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in minutes.
		 */
		minutes( start, end, format = '' ) {
			return DateTime.diff( start, end, 'minutes', format );
		},

		/**
		 * Calculate time range length in seconds.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} start  Start datetime.
		 * @param {string} end    End datetime.
		 * @param {string} format Datetime format.
		 *
		 * @return {number|string} Time range length in seconds.
		 */
		seconds( start, end, format = '' ) {
			return DateTime.diff( start, end, 'seconds', format );
		},
	};

	/**
	 * Filter functions allowed in calculations.
	 * This filter allows to add custom functions.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} functions Functions allowed in calculations.
	 */
	const filteredFunctions = wp.hooks.applyFilters( 'wpformsCalculationsFunctions', defaultFunctions );

	return WPFormsCalculations.isObject( filteredFunctions ) ? filteredFunctions : defaultFunctions;
}
