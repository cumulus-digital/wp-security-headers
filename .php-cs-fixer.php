<?php

// PHP-CS-Fixer project configuration
$config = new PhpCsFixer\Config();
require_once __DIR__ . '/vendor/cumulus-digital/wp-php-cs-fixer/loader.php';

// Load WP core classes/functions/constants for qualifying
$wp_core      = \str_getcsv( \file_get_contents( __DIR__ . '/.php-cs-fixer/wp-core.csv' ) );
$NFI_includes = \array_filter( \array_merge(
	array( '@compiler_optimized' ),
	array( '@internal' ),
	$wp_core
) );

return $config
	->registerCustomFixers( array(
		new WeDevs\Fixer\SpaceInsideParenthesisFixer(),
		new WeDevs\Fixer\BlankLineAfterClassOpeningFixer(),
	) )
	->setRiskyAllowed( true )
	->setIndent( "\t" )
	->setRules( \array_merge(
		WeDevs\Fixer\Fixer::rules(),
		array(
			/*
			'@PSR2' => true,
			// Each element of an array must be indented exactly once.
			'array_indentation' => true,
			// Replace non multibyte-safe functions with corresponding mb function.
			'mb_str_functions' => true,
			// Add leading `\` before constant invocation of internal constant to speed up resolving. Constant name match is case-sensitive, except for `null`, `false` and `true`.
			'native_constant_invocation' => true,
			// Add leading `\` before function invocation to speed up resolving.
			'native_function_invocation' => [ 'strict' => false, 'include' => $NFI_includes ],
			// Master language constructs shall be used instead of aliases.
			'no_alias_language_construct_call' => true,
			// PHP single-line arrays should not have trailing comma.
			'no_trailing_comma_in_singleline_array' => true,
			// Remove trailing whitespace at the end of blank lines.
			'no_whitespace_in_blank_line' => true,
			// Logical NOT operators (`!`) should have leading and trailing whitespaces.
			'not_operator_with_space' => true,
			// Standardize spaces around ternary operator.
			'ternary_operator_spaces' => true,
			// Multi-line arrays, arguments list and parameters list must have a trailing comma.
			'trailing_comma_in_multiline' => true,
			// Replace control structure alternative syntax to use braces.
			'no_alternative_syntax' => false,
			// Curly Braces should be on the same line
			'curly_braces_position' => [
				'classes_opening_brace'   => 'same_line',
				'functions_opening_brace' => 'same_line',
			],
			 */

			'@PSR2' => true,
			// Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.
			'align_multiline_comment' => array( 'comment_type' => 'all_multiline' ),
			// Each element of an array must be indented exactly once.
			'array_indentation' => true,
			// PHP arrays should be declared using the configured syntax.
			'array_syntax' => array( 'syntax' => 'long' ),
			// Use the null coalescing assignment operator `??=` where possible.
			'assign_null_coalescing_to_coalesce_equal' => true,
			// Binary operators should be surrounded by space as configured.
			'binary_operator_spaces' => array( 'default' => 'align_single_space_minimal' ),
			// An empty line feed must precede any configured statement.
			'blank_line_before_statement' => array( 'statements' => array( 'break', 'continue', 'declare', 'exit', 'return', 'throw', 'try' ) ),
			// A single space or none should be between cast and variable.
			'cast_spaces' => array( 'space' => 'single' ),
			// Class, trait and interface elements must be separated with one or none blank line.
			'class_attributes_separation' => true,
			// When referencing an internal class it must be written using the correct casing.
			'class_reference_name_casing' => true,
			// Namespace must not contain spacing, comments or PHPDoc.
			'clean_namespace' => true,
			// Using `isset($var) &&` multiple times should be done in one call.
			'combine_consecutive_issets' => true,
			// Calling `unset` on multiple items should be done in one call.
			'combine_consecutive_unsets' => true,
			// Concatenation should be spaced according to configuration.
			'concat_space' => array( 'spacing' => 'one' ),
			// Curly braces must be placed as configured.
			'curly_braces_position' => array( 'classes_opening_brace' => 'same_line', 'functions_opening_brace' => 'same_line' ),
			// Equal sign in declare statement should be surrounded by spaces or not following configuration.
			'declare_equal_normalize' => array( 'space' => 'single' ),
			// Replaces short-echo `<?=` with long format `<?php echo`/`<?php print` syntax, or vice-versa.
			'echo_tag_syntax' => true,
			// Empty loop-body must be in configured style.
			'empty_loop_body' => array( 'style' => 'braces' ),
			// Add curly braces to indirect variables to make them clear to understand. Requires PHP >= 7.0.
			'explicit_indirect_variable' => true,
			// Converts implicit variables into explicit ones in double-quoted strings or heredoc syntax.
			'explicit_string_variable' => true,
			// PHP code must use the long `<?php` tags or short-echo `<?=` tags and not other tag variations.
			'full_opening_tag' => true,
			// Ensure single space between function's argument and its typehint.
			'function_typehint_space' => true,
			// Function `implode` must be called with 2 arguments in the documented order.
			'implode_call' => true,
			// Include/Require and file path should be divided with a single space. File path should not be placed under brackets.
			'include' => true,
			// Integer literals must be in correct case.
			'integer_literal_case' => true,
			// Lambda must not import variables it doesn't use.
			'lambda_not_used_import' => true,
			// List (`array` destructuring) assignment should be declared using the configured syntax. Requires PHP >= 7.1.
			'list_syntax' => array( 'syntax' => 'long' ),
			// Class static references `self`, `static` and `parent` MUST be in lower case.
			'lowercase_static_reference' => true,
			// Magic constants should be referred to using the correct casing.
			'magic_constant_casing' => true,
			// Magic method definitions and calls must be using the correct casing.
			'magic_method_casing' => true,
			// Replace non multibyte-safe functions with corresponding mb function.
			'mb_str_functions' => true,
			// Method chaining MUST be properly indented. Method chaining with different levels of indentation is not supported.
			'method_chaining_indentation' => true,
			// Forbid multi-line whitespace before the closing semicolon or move the semicolon to the new line for chained calls.
			'multiline_whitespace_before_semicolons' => true,
			// Add leading `\` before constant invocation of internal constant to speed up resolving. Constant name match is case-sensitive, except for `null`, `false` and `true`.
			'native_constant_invocation' => true,
			// Function defined by PHP should be called using the correct casing.
			'native_function_casing' => true,
			// Add leading `\` before function invocation to speed up resolving.
			'native_function_invocation' => array( 'include' => $NFI_includes ),
			// Native type hints for functions should use the correct case.
			'native_function_type_declaration_casing' => true,
			// All instances created with `new` keyword must (not) be followed by braces.
			'new_with_braces' => true,
			// Master language constructs shall be used instead of aliases.
			'no_alias_language_construct_call' => true,
			// Replace control structure alternative syntax to use braces.
			'no_alternative_syntax' => array( 'fix_non_monolithic_code' => false ),
			// There should be no empty lines after class opening brace.
			'no_blank_lines_after_class_opening' => true,
			// There should not be blank lines between docblock and the documented element.
			'no_blank_lines_after_phpdoc' => true,
			// Remove useless (semicolon) statements.
			'no_empty_statement' => true,
			// Remove leading slashes in `use` clauses.
			'no_leading_import_slash' => true,
			// The namespace declaration line shouldn't contain leading whitespace.
			'no_leading_namespace_whitespace' => true,
			// Either language construct `print` or `echo` should be used.
			'no_mixed_echo_print' => true,
			// Operator `=>` should not be surrounded by multi-line whitespaces.
			'no_multiline_whitespace_around_double_arrow' => true,
			// Short cast `bool` using double exclamation mark should not be used.
			'no_short_bool_cast' => true,
			// Single-line whitespace before closing semicolon are prohibited.
			'no_singleline_whitespace_before_semicolons' => true,
			// There MUST NOT be spaces around offset braces.
			'no_spaces_around_offset' => array( 'positions' => array( 'outside' ) ),
			// There MUST NOT be a space after the opening parenthesis. There MUST NOT be a space before the closing parenthesis.
			'no_spaces_inside_parenthesis' => false,
			// If a list of values separated by a comma is contained on a single line, then the last item MUST NOT have a trailing comma.
			'no_trailing_comma_in_singleline' => true,
			// Remove trailing whitespace at the end of non-blank lines.
			'no_trailing_whitespace' => true,
			// There MUST be no trailing spaces inside comment or PHPDoc.
			'no_trailing_whitespace_in_comment' => true,
			// Removes unneeded parentheses around control statements.
			'no_unneeded_control_parentheses' => true,
			// Removes unneeded curly braces that are superfluous and aren't part of a control structure's body.
			'no_unneeded_curly_braces' => array( 'namespaces' => true ),
			// There should not be useless `else` cases.
			'no_useless_else' => true,
			// In array declaration, there MUST NOT be a whitespace before each comma.
			'no_whitespace_before_comma_in_array' => true,
			// Remove trailing whitespace at the end of blank lines.
			'no_whitespace_in_blank_line' => true,
			// Remove Zero-width space (ZWSP), Non-breaking space (NBSP) and other invisible unicode symbols.
			'non_printable_character' => true,
			// Array index should always be written by using square braces.
			'normalize_index_brace' => true,
			// Logical NOT operators (`!`) should have leading and trailing whitespaces.
			'not_operator_with_space' => true,
			// Logical NOT operators (`!`) should have one trailing whitespace.
			'not_operator_with_successor_space' => true,
			// There should not be space before or after object operators `->` and `?->`.
			'object_operator_without_whitespace' => true,
			// Operators - when multiline - must always be at the beginning or at the end of the line.
			'operator_linebreak' => true,
			// PHPDoc should contain `@param` for all params.
			'phpdoc_add_missing_param_annotation' => true,
			// All items of the given phpdoc tags must be either left-aligned or (by default) aligned vertically.
			'phpdoc_align' => true,
			// Docblocks should have the same indentation as the documented subject.
			'phpdoc_indent' => true,
			// Annotations in PHPDoc should be ordered in defined sequence.
			'phpdoc_order' => true,
			// Scalar types should always be written in the same form. `int` not `integer`, `bool` not `boolean`, `float` not `real` or `double`.
			'phpdoc_scalar' => true,
			// Annotations in PHPDoc should be grouped together so that annotations of the same type immediately follow each other. Annotations of a different type are separated by a single blank line.
			'phpdoc_separation' => true,
			// Single line `@var` PHPDoc should have proper spacing.
			'phpdoc_single_line_var_spacing' => true,
			// PHPDoc summary should end in either a full stop, exclamation mark, or question mark.
			'phpdoc_summary' => true,
			// Fixes casing of PHPDoc tags.
			'phpdoc_tag_casing' => true,
			// PHPDoc should start and end with content, excluding the very first and last line of the docblocks.
			'phpdoc_trim' => true,
			// Removes extra blank lines after summary and after description in PHPDoc.
			'phpdoc_trim_consecutive_blank_line_separation' => true,
			// The correct case must be used for standard PHP types in PHPDoc.
			'phpdoc_types' => true,
			// Adjust spacing around colon in return type declarations and backed enum types.
			'return_type_declaration' => array( 'space_before' => 'none' ),
			// Instructions must be terminated with a semicolon.
			'semicolon_after_instruction' => true,
			// Converts explicit variables in double-quoted strings and heredoc syntax from simple to complex format (`${` to `{$`).
			'simple_to_complex_string_variable' => true,
			// A return statement wishing to return `void` should not return `null`.
			'simplified_null_return' => true,
			// There MUST NOT be more than one property or constant declared per statement.
			'single_class_element_per_statement' => array( 'elements' => array( 'const', 'property' ) ),
			// There MUST be one use keyword per declaration.
			'single_import_per_statement' => array( 'group_to_single_imports' => true ),
			// Single-line comments must have proper spacing.
			'single_line_comment_spacing' => true,
			// Single-line comments and multi-line comments with only one line of actual content should use the `//` syntax.
			'single_line_comment_style' => true,
			// Convert double quotes to single quotes for simple strings.
			'single_quote' => true,
			// Ensures a single space after language constructs.
			'single_space_around_construct' => true,
			// Fix whitespace after a semicolon.
			'space_after_semicolon' => true,
			// Removes extra spaces between colon and case value.
			'switch_case_space' => true,
			// Standardize spaces around ternary operator.
			'ternary_operator_spaces' => true,
			// Multi-line arrays, arguments list, parameters list and `match` expressions must have a trailing comma.
			'trailing_comma_in_multiline' => true,
			// A single space or none should be around union type and intersection type operators.
			'types_spaces' => array( 'space' => 'single' ),
			// Unary operators should be placed adjacent to their operands.
			'unary_operator_spaces' => true,
			// In array declaration, there MUST be a whitespace after each comma.
			'whitespace_after_comma_in_array' => array( 'ensure_single_space' => true ),
			// Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
			'yoda_style' => array( 'equal' => true, 'identical' => true, 'less_and_greater' => null ),
		),
	) );
