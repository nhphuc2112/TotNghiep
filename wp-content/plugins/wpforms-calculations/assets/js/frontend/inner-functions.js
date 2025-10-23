/* global WPFormsCalculations */

/**
 * WPForms Calculation feature.
 *
 * Inner functions module.
 *
 * @since 1.0.0
 *
 * @return {Object} Inner Functions module.
 */
export default function() { // eslint-disable-line max-lines-per-function
	/**
	 * Math functions.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	const math = {
		/**
		 * Plus operation.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		plus( left, right ) {
			const arg = math.getPreparedMathArgs( left, right );

			return math.round( arg.leftNum + arg.rightNum, arg.precision );
		},

		/**
		 * Minus operation.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		minus( left, right ) {
			const arg = math.getPreparedMathArgs( left, right );

			return math.round( arg.leftNum - arg.rightNum, arg.precision );
		},

		/**
		 * Multiply operation.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		mul( left, right ) {
			const arg = math.getPreparedMathArgs( left, right );

			return math.round( arg.leftNum * arg.rightNum, arg.precision );
		},

		/**
		 * Division operation.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		div( left, right ) {
			const arg = math.getPreparedMathArgs( left, right );

			return math.round( arg.leftNum / arg.rightNum );
		},

		/**
		 * Modulo operation.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		mod( left, right ) {
			const arg = math.getPreparedMathArgs( left, right );

			return math.round( arg.leftNum % arg.rightNum, arg.precision );
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
		round( num, precision = 12 ) {
			return Number( Math.round( Number( num + 'e+' + precision ) ) + 'e-' + precision );
		},

		/**
		 * Convert string to a number.
		 *
		 * This function is a wrapper for WPFormsCalculations.parseFloat,
		 * but in the case of str === '' it returns 0.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} str       String to convert.
		 * @param {number} precision Round number to $precision decimal digits. Defaults to 12. Optional.
		 *
		 * @return {number} The converted number.
		 */
		parseFloat( str, precision = 12 ) {
			return Number( WPFormsCalculations.parseFloat( str, precision ) );
		},

		/**
		 * Get prepared math operation arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param {string|number} left  The left argument.
		 * @param {string|number} right The right argument.
		 *
		 * @return {Object} Prepared args.
		 */
		getPreparedMathArgs( left, right ) {
			const leftNum = math.parseFloat( left ),
				rightNum = math.parseFloat( right );

			return {
				leftNum,
				rightNum,
				precision: Math.max( math.getPrecision( leftNum ), math.getPrecision( rightNum ) ),
			};
		},

		/**
		 * Get prepared math operation arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param {number} num Number value.
		 *
		 * @return {number} Number precision.
		 */
		getPrecision( num ) {
			const chunks = num.toString().split( '.' );

			return chunks[ 1 ] ? chunks[ 1 ].length : 0;
		},

		/**
		 * Boolean OR operation.
		 *
		 * @since 1.7.0
		 *
		 * @param {boolean} left  The left argument.
		 * @param {boolean} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		booleanor( left, right ) {
			return Number( !! ( left || right ) );
		},

		/**
		 * Boolean AND operation.
		 *
		 * @since 1.7.0
		 *
		 * @param {boolean} left  The left argument.
		 * @param {boolean} right The right argument.
		 *
		 * @return {number} Result of the operation.
		 */
		booleanand( left, right ) {
			return Number( !! ( left && right ) );
		},
	};

	return { ...math };
}
