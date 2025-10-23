<?php

namespace WPFormsCalculations\Transpiler;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

/**
 * CFL transpiler. Node visitor.
 *
 * @since 1.0.0
 */
class NodeVisitor extends NodeVisitorAbstract {

	/**
	 * Result of node validation.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $node_is_valid;

	/**
	 * Validator class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Validator
	 */
	private $validator;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Validator $validator Validator class instance.
	 */
	public function __construct( $validator ) {

		$this->validator = $validator;
	}

	/**
	 * Before traversal.
	 *
	 * @since 1.0.0
	 *
	 * @param Node[] $nodes Nodes array.
	 */
	public function beforeTraverse( array $nodes ) {

		$this->validator->reset_errors();
	}

	/**
	 * Enter node. Make all checks here.
	 *
	 * @since 1.0.0
	 *
	 * @param Node $node Node.
	 *
	 * @return null
	 */
	public function enterNode( Node $node ) {

		// Check if node is valid.
		$this->node_is_valid = $this->validator->is_valid( $node );

		return null;
	}

	/**
	 * Leave node. Make node transformations here.
	 *
	 * @since 1.0.0
	 *
	 * @param Node $node Node.
	 *
	 * @return null
	 */
	public function leaveNode( Node $node ) {

		if ( ! $this->node_is_valid ) {
			return null;
		}

		// Convert all functions calls to $_FUNCTION['function_name]( ... ) call.
		if ( isset( $node->name ) && $this->validator->is_function( $node ) ) {
			$node->name = $this->get_updated_function_name( $node );

			return null;
		}

		// Convert operators to $_INTERNAL_FUNC['operator']( ... ) call.
		if (
			$this->validator->is_math_op( $node ) || // Math operators.
			$this->validator->is_boolean_op( $node ) // || and && operators.
		) {
			return $this->get_updated_op( $node );
		}

		if ( ! $this->validator->is_if( $node ) ) {
			return null;
		}

		// Convert all statements inside if-elseif-else to assignments of $_RETVAL variable.
		if ( empty( $node->stmts ) ) {
			return null;
		}

		$node->stmts = $this->get_updated_statements( $node->stmts );

		return null;
	}

	/**
	 * Update nodes single level.
	 *
	 * @since 1.0.0
	 *
	 * @param Node[] $nodes Nodes array.
	 *
	 * @return Node[]
	 */
	public function get_updated_statements( $nodes ) {

		$new_nodes = [];

		// Create $_RETVAL variable node.
		$retval = new Node\Expr\Variable( Transpiler::RESULT_VAR_NAME );

		foreach ( $nodes as $node ) {

			if (
				$this->validator->is_assign( $node ) ||
				$this->validator->is_if( $node ) ||
				$this->validator->is_nop( $node )
			) {
				$new_nodes[] = $node;

				continue;
			}

			// Add $_RETVAL variable assignment.
			if ( isset( $node->expr ) ) {
				// Add $_RETVAL variable assignment.
				$new_nodes[] = new Node\Stmt\Expression( new Node\Expr\Assign( $retval, $node->expr ) );
			}
		}

		return $new_nodes;
	}

	/**
	 * Update function call node name element.
	 * Replace function name with `$_FUNCTION['function_name']['func']`.
	 *
	 * @since 1.0.0
	 *
	 * @param Node $node Function call node.
	 *
	 * @return Node\Expr\ArrayDimFetch
	 */
	private function get_updated_function_name( $node ) {

		if ( ! isset( $node->name ) ) {
			return null;
		}

		if ( ! $node->name instanceof Node\Name ) {
			return $node->name;
		}

		$func = new Node\Expr\ArrayDimFetch(
			new Node\Expr\Variable( Transpiler::FUNCTIONS_ARRAY_NAME ),
			new Node\Scalar\String_( $node->name->toString() )
		);

		// Replace function name with `$_FUNCTION['function_name']['func']`.
		return new Node\Expr\ArrayDimFetch(
			$func,
			new Node\Scalar\String_( 'func' )
		);
	}

	/**
	 * Update operator node.
	 * Replace operator with `$_INNER_FUNC['operator']( ... )`.
	 *
	 * @since 1.0.0
	 *
	 * @param Node $node Function call node.
	 *
	 * @return Node\Expr\FuncCall
	 */
	private function get_updated_op( $node ) {

		if ( ! isset( $node->left, $node->right ) ) {
			return null;
		}

		$func_name = new Node\Expr\ArrayDimFetch(
			new Node\Expr\Variable( Transpiler::INNER_FUNCTIONS_ARRAY_NAME ),
			new Node\Scalar\String_( strtolower( ( new ReflectionClass( $node ) )->getShortName() ) )
		);

		return new Node\Expr\FuncCall(
			$func_name,
			[
				new Node\Arg( $node->left ),
				new Node\Arg( $node->right ),
			]
		);
	}
}
