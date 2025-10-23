<?php

namespace WPFormsCalculations\Transpiler;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\ErrorHandler;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use RuntimeException;
use WPFormsCalculations\Helpers;

/**
 * CFL transpiler class.
 *
 * @since 1.0.0
 */
class Transpiler {

	/**
	 * Result variable name.
	 *
	 * @since 1.0.0
	 */
	const RESULT_VAR_NAME = '_RETVAL';

	/**
	 * Functions array name.
	 *
	 * @since 1.0.0
	 */
	const FUNCTIONS_ARRAY_NAME = '_FUNCTION';

	/**
	 * Internal functions array name.
	 *
	 * @since 1.0.0
	 */
	const INNER_FUNCTIONS_ARRAY_NAME = '_INNER_FUNC';

	/**
	 * Parser instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Parser
	 */
	private $parser;

	/**
	 * Field settings which is currently processing.
	 *
	 * @since 1.0.0
	 *
	 * @var Parser
	 */
	private $field;

	/**
	 * Error handler instance.
	 *
	 * @since 1.0.0
	 *
	 * @var ErrorHandler\Collecting
	 */
	private $error_handler;

	/**
	 * Translated parser errors.
	 *
	 * @since 1.0.0
	 *
	 * @var string[]
	 */
	private $parser_errors;

	/**
	 * Pretty printer instance.
	 *
	 * @since 1.0.0
	 *
	 * @var PrettyPrinter\Standard
	 */
	private $pretty_printer;

	/**
	 * Node traverser instance.
	 *
	 * @since 1.0.0
	 *
	 * @var NodeTraverser
	 */
	private $traverser;

	/**
	 * Node traverser instance.
	 *
	 * @since 1.0.0
	 *
	 * @var NodeVisitor
	 */
	private $node_visitor;

	/**
	 * Functions class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Functions
	 */
	private $functions;

	/**
	 * Helpers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Helpers
	 */
	private $helpers;

	/**
	 * Validation errors.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $validation_errors;

	/**
	 * Currently processed formula code.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $code;

	/**
	 * Code parsing result. AST nodes.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $nodes;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$lexer = new Lexer(
			[
				'usedAttributes' => [ 'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos' ],
			]
		);

		$this->parser         = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7, $lexer );
		$this->error_handler  = new ErrorHandler\Collecting();
		$this->pretty_printer = new PrettyPrinter\Standard();
		$this->traverser      = new NodeTraverser();
		$this->functions      = ( new Functions() )->get();
		$this->helpers        = wpforms_calculations()->helpers;
	}

	/**
	 * Parse and validate formula code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code       Formula code.
	 * @param int    $field_id   Field ID.
	 * @param array  $form_data  Form settings data.
	 * @param bool   $check_vars Whether check field variables or not.
	 *
	 * @return array Array of collected errors.
	 */
	public function parse_and_validate_formula_code( $code, $field_id, $form_data, $check_vars = true ): array {

		if (
			empty( $code ) ||
			! isset( $form_data['fields'][ $field_id ] )
		) {
			return [];
		}

		// Store field settings that are currently processing.
		$this->field = $form_data['fields'][ $field_id ];

		// Clear parser errors. We need to do this because we use the same error_handler instance for all fields.
		$this->error_handler->clearErrors();

		// Prepare the code.
		$this->code = $this->get_prepared_code( $code, $form_data );

		// Check the code length. The maximum allowed is 64KB.
		if ( strlen( $this->code ) > Validator::MAX_FORMULA_LENGTH ) {
			return [
				sprintf( /* translators: %1$s - Error location `line:column`. */
					esc_html__( 'The size of the formula exceeds %1$s chars', 'wpforms-calculations' ),
					Validator::MAX_FORMULA_LENGTH
				),
			];
		}

		// Parse the code.
		$this->nodes = $this->parser->parse( $this->code, $this->error_handler );

		// Parser error processing.
		if ( $this->error_handler->hasErrors() ) {
			$this->process_parser_errors();

			return $this->parser_errors;
		}

		// Prepare validator and node visitor.
		$validator          = new Validator( $form_data, $this->functions, $this->code, $check_vars, $this->field );
		$this->node_visitor = new NodeVisitor( $validator );

		// Traverse AST data.
		$this->traverser->addVisitor( $this->node_visitor );
		$this->traverser->traverse( $this->nodes );

		// Get validation errors.
		$this->validation_errors = $validator->get_errors();

		if ( empty( $this->validation_errors ) ) {
			return [];
		}

		$this->process_validation_errors();

		return $this->validation_errors;
	}

	/**
	 * Process single field.
	 *
	 * - Parse and validate the formula code.
	 * - Generate `calculation_code_php` and `calculation_code_js`.
	 * - Update field settings data with the new generated values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field settings data.
	 * @param array $form_data Form settings data.
	 *
	 * @return array
	 */
	public function process_field( $field, $form_data ) {

		$code   = isset( $field['calculation_code'] ) ? trim( $field['calculation_code'] ) : '';
		$errors = $this->parse_and_validate_formula_code( $code, $field['id'], $form_data );

		// There are parser or validation errors, so we need to process them.
		if ( ! empty( $errors ) || empty( $code ) ) {

			// Reset generated executable code.
			$field['calculation_code_php'] = '';
			$field['calculation_code_js']  = '';

			return $field;
		}

		// There are no parser or validation errors, so we can proceed.

		// Update statements in the root of the nodes tree.
		$nodes = $this->node_visitor->get_updated_statements( $this->nodes );

		// Generate new PHP code.
		$field['calculation_code_php'] = $this->pretty_printer->prettyPrintFile( $nodes );

		$this->helpers->log(
			sprintf(
				'Field #%1$s PHP code: %2$s',
				$field['id'],
				$field['calculation_code_php']
			),
			'debug'
		);

		// Generate new JS code.
		$field['calculation_code_js'] = $this->convert_php_to_js( $field['calculation_code_php'] );

		$this->helpers->log(
			sprintf(
				'Field #%1$s JS code: %2$s',
				$field['id'],
				$field['calculation_code_js']
			),
			'debug'
		);

		// Add backslashes to not lose the special symbols like `\n` and `\t`.
		$field['calculation_code']     = addslashes( $field['calculation_code'] );
		$field['calculation_code_php'] = addslashes( $field['calculation_code_php'] );
		$field['calculation_code_js']  = addslashes( $field['calculation_code_js'] );

		return $field;
	}

	/**
	 * Prepare the code before parsing.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code      Source code.
	 * @param array  $form_data Form settings data.
	 *
	 * @return string
	 */
	private function get_prepared_code( $code, $form_data ) {

		$code = '<?php ' . $code . ';';

		// Replace empty conditions `if()` and `elseif()` with `if(0)` and `elseif(0)`.
		// This is needed to avoid unpredictable resulting code structure since PHP-Parser breaks the if statement in this case.
		$code = $this->helpers->preg_replace_not_in_quotes( '\sif\s*\(\s*\)', 'if(0)', $code );
		$code = $this->helpers->preg_replace_not_in_quotes( 'elseif\s*\(\s*\)', 'elseif(0)', $code );

		// Add semicolon before `if`, `elseif`, `else` and `endif` statements.
		$code = $this->helpers->preg_replace_not_in_quotes( '\R\s*if\s*\(', ";\nif(", $code );
		$code = $this->helpers->preg_replace_not_in_quotes( '\R\s*elseif\s*\(', ";\nelseif(", $code );
		$code = $this->helpers->preg_replace_not_in_quotes( '\R\s*else:', ";\nelse:", $code );
		$code = $this->helpers->preg_replace_not_in_quotes( '\R\s*endif', ";\nendif;", $code );

		/**
		 * Filter the CFL code before transpiling (converting) to PHP.
		 *
		 * @since 1.0.0
		 *
		 * @param string $code      Source code.
		 * @param array  $field     Field settings data.
		 * @param array  $form_data Form settings data.
		 */
		return apply_filters( 'wpforms_calculations_transpiler_get_prepared_code', $code, $this->field, $form_data );
	}

	/**
	 * Check errors collected by the parser.
	 *
	 * @since 1.0.0
	 */
	private function process_parser_errors() {

		if ( ! $this->error_handler->hasErrors() ) {
			return;
		}

		$this->parser_errors = [];
		$error_num           = 0;

		foreach ( $this->error_handler->getErrors() as $error ) {

			$token = str_replace(
				[
					'Syntax error, unexpected ',
					'Unexpected null byte',
					'Unexpected character',
				],
				[
					'',
					esc_html__( 'null byte', 'wpforms-calculations' ),
					esc_html__( 'character', 'wpforms-calculations' ),
				],
				$error->getRawMessage()
			);

			++$error_num;

			// Compile translatable error message.
			$message = sprintf( /* translators: %1$s - unexpected token; %2$s - error location `line:column`. */
				esc_html__( 'Unexpected %1$s on line %2$s', 'wpforms-calculations' ),
				$token,
				$this->get_error_location( $error, $this->code )
			);

			$this->parser_errors[] = $message;

			$this->helpers->log(
				sprintf( /* translators: %1$s - field ID; %2$s - error number; %3$s - error message. */
					'Field #%1$s syntax error %2$s: %3$s',
					$this->field['id'],
					$error_num,
					$message
				),
				'error'
			);
		}
	}

	/**
	 * Get parser error location in the code.
	 *
	 * @since 1.0.0
	 *
	 * @param Error  $error Error object.
	 * @param string $code  Source code.
	 *
	 * @return string
	 **/
	private function get_error_location( $error, $code ) {

		if ( ! $error instanceof Error ) {
			return esc_html__( 'unknown', 'wpforms-calculations' );
		}

		$line = $error->getStartLine();

		try {
			$column = $error->getStartColumn( $code );
			$column = $line === 1 ? $column - 6 : $column;

		} catch ( RuntimeException $e ) {
			$column = null;
		}

		return ! is_null( $column ) ? $line . ':' . $column : $line;
	}

	/**
	 * Check errors collected by the parser.
	 *
	 * @since 1.0.0
	 **/
	private function process_validation_errors() {

		foreach ( $this->validation_errors as $i => $error ) {

			$this->helpers->log(
				sprintf( /* translators: %1$s - field ID; %2$s - error number; %3$s - error message. */
					'Field #%1$s validation error %2$s: %3$s',
					$this->field['id'],
					$i + 1,
					$error
				),
				'error'
			);
		}
	}

	/**
	 * Convert PHP code to JavaScript.
	 *
	 * @since 1.0.0
	 *
	 * @param string $php_code PHP code.
	 *
	 * @return string
	 **/
	private function convert_php_to_js( $php_code ) {

		if ( empty( $php_code ) || ! is_string( $php_code ) ) {
			return '';
		}

		// Remove open PHP tag.
		$js_code = preg_replace( '/^<\?php/i', '', $php_code );

		// Replace `elseif` with `else if`.
		$js_code = $this->helpers->preg_replace_not_in_quotes(
			'\selseif\s*\(',
			"\nelse if (",
			$js_code
		);

		// Simplify function call to `$_FUNCTION['function']`.
		return $this->helpers->preg_replace_not_in_quotes(
			'\$\_FUNCTION\[\'(\w+)\'\]\[\'func\'\]',
			'\$_FUNCTION[\'$1\']',
			$js_code
		);
	}
}
