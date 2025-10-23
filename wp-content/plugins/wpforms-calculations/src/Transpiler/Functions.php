<?php

namespace WPFormsCalculations\Transpiler;

use WPFormsCalculations\Helpers;

/**
 * Custom functions class.
 *
 * @since 1.0.0
 */
class Functions {

	/**
	 * Filtered array of functions allowed in calculations.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $filtered_functions;

	/**
	 * Inner functions class instance.
	 *
	 * @since 1.1.0
	 *
	 * @var InnerFunctions
	 */
	private $inner_functions;

	/**
	 * Helpers class instance.
	 *
	 * @since 1.1.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->inner_functions = wpforms_calculations()->inner_functions;
		$this->helpers         = wpforms_calculations()->helpers;
	}

	/**
	 * Get default functions as array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of functions and their metadata.
	 */
	private function get_default(): array {
		// phpcs:ignore .Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
		return [

			/**
			 * Returns the absolute value of num.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The argument to process.
			 *
			 * @return int|float
			 */
			'abs'           => [
				'func'     => function ( $num ) {
					return abs( $this->inner_functions->to_float( $num ) );
				},
				'args_num' => 1,
			],

			/**
			 * Returns the average value of given values.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $val1 Value 1.
			 * @param int|float|string $val2 Value 2.
			 *                        ...
			 * @param int|float|string $valN Value N.
			 *
			 * @return int|float
			 */
			'average'       => [
				'func'     => function () {

					$args = array_map( [ $this->inner_functions, 'to_float' ], func_get_args() );

					return array_sum( $args ) / func_num_args();
				},
				'args_num' => '1+',
			],

			/**
			 * Returns the next highest integer value by rounding up num if necessary.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The argument to process.
			 *
			 * @return float
			 */
			'ceil'          => [
				'func'     => function ( $num ) {
					return ceil( $this->inner_functions->to_float( $num ) );
				},
				'args_num' => 1,
			],

			/**
			 * Output debug data to debug log
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $val1 Value 1.
			 * @param int|float|string $val2 Value 2.
			 * ...
			 * @param int|float|string $valN Value N.
			 *
			 * @return int
			 */
			'debug'         => [
				'func'     => function () {

					$args = func_get_args();
					$msg  = '';

					foreach ( $args as $arg ) {
						$msg .= print_r( $arg, true ) . ' '; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					}

					$this->helpers->log( $msg );

					return 0;
				},
				'args_num' => '1+',
			],

			/**
			 * Calculates the exponent of e.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The argument to process.
			 *
			 * @return float
			 */
			'exp'           => [
				'func'     => function ( $num ) {
					return $this->inner_functions->round(
						exp( $this->inner_functions->to_float( $num ) )
					);
				},
				'args_num' => 1,
			],

			/**
			 * Round fractions down.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The argument to process.
			 *
			 * @return float
			 */
			'floor'         => [
				'func'     => function ( $num ) {
					return floor( $this->inner_functions->to_float( $num ) );
				},
				'args_num' => 1,
			],

			/**
			 * Natural logarithm.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The value to calculate the logarithm for.
			 *
			 * @return float
			 */
			'ln'            => [
				'func'     => function ( $num ) {
					return $this->inner_functions->round(
						log( $this->inner_functions->to_float( $num ), M_E )
					);
				},
				'args_num' => 1,
			],

			/**
			 * Base-10 logarithm.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The value to calculate the logarithm for.
			 *
			 * @return float
			 */
			'log'           => [
				'func'     => function ( $num ) {
					return $this->inner_functions->round(
						log( $this->inner_functions->to_float( $num ), 10 )
					);
				},
				'args_num' => 1,
			],

			/**
			 * Find maximum value.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $val1 Value 1.
			 * @param int|float|string $val2 Value 2.
			 * ...
			 * @param int|float|string $valN Value N.
			 *
			 * @return int|float
			 */
			'max'           => [
				'func'     => function () {

					$args = array_map( [ $this->inner_functions, 'to_float' ], func_get_args() );

					return max( $args );
				},
				'args_num' => '1+',
			],

			/**
			 * Find minimal value.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $val1 Value 1.
			 * @param int|float|string $val2 Value 2.
			 * ...
			 * @param int|float|string $valN Value N.
			 *
			 * @return int|float
			 */
			'min'           => [
				'func'     => function () {

					$args = array_map( [ $this->inner_functions, 'to_float' ], func_get_args() );

					return min( $args );
				},
				'args_num' => '1+',
			],

			/**
			 * Convert string to a number.
			 *
			 * @since 1.0.0
			 *
			 * @param string           $str       String to convert.
			 * @param int|float|string $precision Round number to $precision decimal digits. Defaults to 12. Optional.
			 *
			 * @return int|float
			 */
			'num'           => [
				'func'     => function ( $str, $precision = 12 ) {
					$num = $this->inner_functions->to_float( $str, $precision );

					return empty( $num ) ? 0 : $num;
				},
				'args_num' => '1-2',
			],

			/**
			 * Get the value of pi.
			 *
			 * @since 1.0.0
			 *
			 * @return float
			 */
			'pi'            => [
				'func'     => function () {
					return $this->inner_functions->round( M_PI );
				},
				'args_num' => 0,
			],

			/**
			 * Returns base raised to the power of exponent.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $base     The base.
			 * @param int|float|string $exponent The exponent.
			 *
			 * @return float
			 */
			'pow'           => [
				'func'     => function ( $base, $exponent ) {

					$base     = $this->inner_functions->to_float( $base );
					$exponent = $this->inner_functions->to_float( $exponent );

					return $this->inner_functions->round( $base ** $exponent );
				},
				'args_num' => 2,
			],

			/**
			 * Generate a random integer.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $min The lowest value to return (default: 0).
			 * @param int|float|string $max The highest value to return (default: 2147483647).
			 *
			 * @return int
			 */
			'rand'          => [
				'func'     => function ( $min = 0, $max = 2147483647 ) {

					$min = (int) $this->inner_functions->to_float( $min );
					$max = (int) $this->inner_functions->to_float( $max );

					return wp_rand( $min, $max );
				},
				'args_num' => '0-2',
			],

			/**
			 * Rounds a float.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num       The value to round.
			 * @param int|float|string $precision The optional number of decimal digits to round to.
			 *
			 * @return float
			 */
			'round'         => [
				'func'     => function ( $num, $precision = 0 ) {

					$num       = $this->inner_functions->to_float( $num );
					$precision = (int) $this->inner_functions->to_float( $precision );

					return $this->inner_functions->round( $num, $precision );
				},
				'args_num' => '1-2',
			],

			/**
			 * Square root.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $num The argument to process.
			 *
			 * @return float
			 */
			'sqrt'          => [
				'func'     => function ( $num ) {

					$num = $this->inner_functions->to_float( $num );

					return $this->inner_functions->round( sqrt( $num ) );
				},
				'args_num' => 1,
			],

			/**
			 * Strips whitespace (or other characters) from the beginning and end of the string.
			 *
			 * @since 1.0.0
			 *
			 * @param string $str The string to process.
			 *
			 * @return string
			 */
			'trim'          => [
				'func'     => function ( $str ) {
					return trim( $str );
				},
				'args_num' => 1,
			],

			/**
			 * Returns the first length characters of the string.
			 *
			 * @since 1.0.0
			 * @since 1.3.0 Added multibyte support.
			 *
			 * @param string $str    The string to process.
			 * @param int    $length String length limit.
			 *
			 * @return string
			 */
			'truncate'      => [
				'func'     => function ( $str, $length ) {
					return mb_substr( $str, 0, $length );
				},
				'args_num' => 2,
			],

			/**
			 * Concatenates all arguments str1, str2 … strN to one string.
			 *
			 * @since 1.0.0
			 *
			 * @param string $strs Strings to concatenate.
			 *
			 * @return string
			 */
			'concat'        => [
				'func'     => function ( ...$strs ) {
					return implode( '', $strs );
				},
				'args_num' => '1+',
			],

			/**
			 * Join arguments str1, str2 … strN to one string with a separator.
			 *
			 * @since 1.0.0
			 *
			 * @param string $separator Separator.
			 * @param string $strs      Strings to join.
			 *
			 * @return string
			 */
			'join'          => [
				'func'     => function ( $separator, ...$strs ) {
					return implode( $separator, array_filter( $strs, 'strlen' ) );
				},
				'args_num' => '2+',
			],

			/**
			 * Format amount with the currency symbol.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $amount Amount to format.
			 * @param int              $symbol Whether display the currency symbol or not. Values: `1` or `0`. Default is `1`.
			 *
			 * @return string
			 */
			'format_amount' => [
				'func'     => function ( $amount, $symbol = 1 ) {
					return wpforms_format_amount( $this->inner_functions->to_float( $amount ), (bool) $symbol );
				},
				'args_num' => '1-2',
			],

			/**
			 * Returns current date and time.
			 *
			 * @since 1.0.0
			 *
			 * @param string $format Date and time string format. Empty string means default format: `d-m-Y H:i`.
			 *
			 * @return string
			 */
			'now'           => [
				'func'     => function ( $format = '' ) {
					return wpforms_calculations()->datetime->now( $format );
				},
				'args_num' => '0-1',
			],

			/**
			 * Get difference between two dates in given units.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $units  Units: years, months, weeks, days, hours, minutes, seconds.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'date_diff'     => [
				'func'     => function ( $start, $end, $units = 'days', $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, $units, $format );
				},
				'args_num' => '2-4',
			],

			/**
			 * Get difference between two dates in years.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'years'         => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'years', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in months.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'months'        => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'months', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in weeks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'weeks'         => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'weeks', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in days.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'days'          => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'days', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in hours.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'hours'         => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'hours', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in minutes.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'minutes'       => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'minutes', $format );
				},
				'args_num' => '2-3',
			],

			/**
			 * Get difference between two dates in seconds.
			 *
			 * @since 1.0.0
			 *
			 * @param string $start  Start datetime.
			 * @param string $end    End datetime.
			 * @param string $format Datetime format.
			 *
			 * @return float|string
			 */
			'seconds'       => [
				'func'     => function ( $start, $end, $format = '' ) {
					return wpforms_calculations()->datetime->diff( $start, $end, 'seconds', $format );
				},
				'args_num' => '2-3',
			],
		];
	}

	/**
	 * Get filtered functions as array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of functions.
	 */
	public function get(): array {

		if ( empty( $this->filtered_functions ) ) {

			/**
			 * Filter functions allowed in calculations.
			 *
			 * @since 1.0.0
			 *
			 * @param array $functions Array of functions allowed in calculations.
			 */
			$this->filtered_functions = apply_filters( 'wpforms_calculations_transpiler_functions_get', $this->get_default() );
		}

		return $this->filtered_functions;
	}

	/**
	 * Get filtered functions names.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of function names.
	 */
	public function get_names(): array {

		return array_keys( $this->get() );
	}
}
