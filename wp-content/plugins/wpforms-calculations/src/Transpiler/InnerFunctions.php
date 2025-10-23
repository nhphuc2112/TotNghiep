<?php

namespace WPFormsCalculations\Transpiler;

/**
 * Inner functions class.
 *
 * @since 1.0.0
 */
class InnerFunctions {

	/**
	 * Convert the given string to a number.
	 *
	 * It finds the first number OR formatted money amount with the current currency symbol
	 * in the given string and converts it to the decimal number value.
	 *
	 * Examples:
	 *   The current currency is USD:
	 *     - "123,123.45"                   -> 123123.45
	 *     - "-10 is a -1 * 10"             -> -10
	 *     - "price is $1,000.00"           -> 1000
	 *     - "Your balance is -$2,555.99"   -> -2555.99
	 *
	 *   The current currency is EUR:
	 *     - "Ticket to Mars 1.000.000,99€" -> 1000000.99
	 *     - "1000,00€ and $50.00"          -> 1000
	 *     - "Negative amount -100€"        -> -100
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $str       String to convert.
	 * @param int   $precision Precision.
	 *
	 * @return float|string Floating point number value OR empty string.
	 */
	public function parse_float( $str, $precision = 12 ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		// If the given value is a zero, return zero.
		if ( in_array( $str, [ 0, 0.0, '0', '0.0' ], true ) ) {
			return (int) $str;
		}

		if ( empty( $str ) ) {
			return '';
		}

		$precision = $precision || $precision === 0 ? (int) $precision : 12;

		// If the given value is already a numeric, just round it.
		if ( is_numeric( $str ) ) {
			return $this->round( $str, $precision );
		}

		$default_currency = [
			'symbol'              => '&#36;',
			'symbol_pos'          => 'left',
			'thousands_separator' => ',',
			'decimal_separator'   => '.',
			'decimals'            => 2,
		];

		// Get currency data.
		$currency_code = strtoupper( wpforms_get_currency() );
		$currencies    = wpforms_get_currencies();
		$currency      = $currencies[ $currency_code ] ?? $default_currency;
		$currency      = wp_parse_args( $currency, $default_currency );

		// Prepare regexp pattern chunks.
		$symbol       = html_entity_decode( $currency['symbol'] ?? '$' );
		$str          = str_replace( $currency['symbol'], $symbol, (string) $str );
		$slash_symbol = str_replace( '$', '\$', $symbol );
		$left_symbol  = $currency['symbol_pos'] === 'left' ? $slash_symbol . '[ ]?' : '';
		$right_symbol = $currency['symbol_pos'] === 'right' ? '[ ]?' . $slash_symbol : '';
		$matches      = [];

		// Prepare regex pattern.
		$amount_pattern =
			// Match amount with currency symbol, decimal and thousands separator.
			"#(-?$left_symbol(\\d*)([{$currency['thousands_separator']}]?\\d{3})*([{$currency['decimal_separator']}]\\d*)?($right_symbol))" .
			// OR match regular number values with a standard decimal and thousands separator.
			'|(-?(\\d+)(,?\\d{3})*([.]?\\d*)?)#';

		preg_match_all( $amount_pattern, $str, $matches );

		if ( empty( $matches[0] ) || ! is_array( $matches[0] ) ) {
			return '';
		}

		// Detect first not empty match.
		$matches[0] = array_filter( $matches[0] );
		$found      = reset( $matches[0] );

		// If the number OR amount is not found, return empty string.
		if ( ! $found ) {
			return '';
		}

		if ( strpos( $found, $symbol ) !== false ) {
			return wpforms_sanitize_amount( $found );
		}

		// Remove a thousand separator and space.
		// At this point, we should have only a numeric value with decimal point.
		$found = str_replace( [ ',', ' ' ], '', $found );

		return $this->round( $found, $precision );
	}

	/**
	 * Convert the given string to a floating point number.
	 *
	 * It is a wrapper for the `parse_float` method, but it always returns a float value.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $str       String to convert.
	 * @param int   $precision Precision.
	 *
	 * @return float Floating point number.
	 */
	public function to_float( $str, $precision = 12 ): float {

		return (float) $this->parse_float( $str, $precision );
	}

	/**
	 * Rounds a float.
	 *
	 * @since 1.1.0
	 *
	 * @param int|float|string $num       The value to round.
	 * @param int              $precision Precision.
	 *
	 * @return float Floating point number.
	 */
	public function round( $num, $precision = 12 ): float {
		// JavaScript half-rounding behaves differently from PHP's, and is not configurable.
		// As a result, we need to make PHP behave the same way that JS does.
		// see: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round#description.
		return round( (float) $num, $precision, $num < 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP );
	}

	/**
	 * Get the number precision.
	 *
	 * @since 1.0.0
	 *
	 * @param int|float $num Number value.
	 *
	 * @return int Number precision.
	 */
	public function get_precision( $num ): int {

		if ( ! is_numeric( $num ) ) {
			return 0;
		}

		$chunks = explode( '.', (string) $num );

		return strlen( $chunks[1] ?? '' );
	}

	/**
	 * Get prepared math operation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param int|float|string $left  The left argument.
	 * @param int|float|string $right The right argument.
	 *
	 * @return array Prepared args.
	 */
	private function get_prepared_math_args( $left, $right ): array {

		$left_num  = $this->to_float( $left );
		$right_num = $this->to_float( $right );

		return [
			'left_num'  => $left_num,
			'right_num' => $right_num,
			'precision' => max( $this->get_precision( $left_num ), $this->get_precision( $right_num ) ),
		];
	}

	/**
	 * Get inner functions as an array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of functions and their metadata.
	 */
	public function get(): array {

		return [

			/**
			 * Plus operation.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $left  The left argument.
			 * @param int|float|string $right The right argument.
			 *
			 * @return int|float
			 */
			'plus'       => function ( $left, $right ) {

				$arg = $this->get_prepared_math_args( $left, $right );

				return $this->round( $arg['left_num'] + $arg['right_num'], $arg['precision'] );
			},

			/**
			 * Minus operation.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $left  The left argument.
			 * @param int|float|string $right The right argument.
			 *
			 * @return int|float
			 */
			'minus'      => function ( $left, $right ) {

				$arg = $this->get_prepared_math_args( $left, $right );

				return $this->round( $arg['left_num'] - $arg['right_num'], $arg['precision'] );
			},

			/**
			 * Multiply operation.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $left  The left argument.
			 * @param int|float|string $right The right argument.
			 *
			 * @return int|float
			 */
			'mul'        => function ( $left, $right ) {

				$arg = $this->get_prepared_math_args( $left, $right );

				return $this->round( $arg['left_num'] * $arg['right_num'], $arg['precision'] );
			},

			/**
			 * Division operation.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $left  The left argument.
			 * @param int|float|string $right The right argument.
			 *
			 * @return int|float
			 */
			'div'        => function ( $left, $right ) {

				$arg = $this->get_prepared_math_args( $left, $right );

				return $this->round( $arg['left_num'] / $arg['right_num'] );
			},

			/**
			 * Modulo operation.
			 *
			 * @since 1.0.0
			 *
			 * @param int|float|string $left  The left argument.
			 * @param int|float|string $right The right argument.
			 *
			 * @return int|float
			 */
			'mod'        => function ( $left, $right ) {

				$arg = $this->get_prepared_math_args( $left, $right );

				$div = $arg['left_num'] / $arg['right_num'];
				$mod = $arg['left_num'] - floor( $div ) * $arg['right_num'];

				return $this->round( $mod, $arg['precision'] );
			},

			/**
			 * Boolean OR operation.
			 *
			 * @since 1.7.0
			 *
			 * @param bool $left  The left argument.
			 * @param bool $right The right argument.
			 *
			 * @return int
			 */
			'booleanor'  => function ( $left, $right ) {
				return (int) ( $left || $right );
			},

			/**
			 * Boolean AND operation.
			 *
			 * @since 1.7.0
			 *
			 * @param bool $left  The left argument.
			 * @param bool $right The right argument.
			 *
			 * @return int
			 */
			'booleanand' => function ( $left, $right ) {
				return (int) ( $left && $right );
			},
		];
	}
}
