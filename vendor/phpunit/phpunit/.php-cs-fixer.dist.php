<?php declare(strict_types=1);
$header = <<<'EOF'
This file is part of PHPUnit.

(c) Sebastian Bergmann <sebastian@phpunit.de>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests/_files')
    ->in(__DIR__ . '/tests/end-to-end')
    ->in(__DIR__ . '/tests/unit')
    // *WithPropertyWith*Hook.php use PHP 8.4 syntax that currently leads to PHP-CS-Fixer errors
    ->notName('ExtendableClassWithPropertyWithGetHook.php')
    ->notName('ExtendableClassWithPropertyWithSetHook.php')
    ->notName('InterfaceWithPropertyWithGetHook.php')
    ->notName('InterfaceWithPropertyWithSetHook.php')
    // DeprecatedPhpFeatureTest.php must not use declare(strict_types=1);
    ->notName('DeprecatedPhpFeatureTest.php')
    // UseBaselineTest.php must not use declare(strict_types=1);
    ->notName('UseBaselineTest.php')
    // Issue5795Test.php contains required whitespace that would be cleaned up
    ->notName('Issue5795Test.php')
    ->notName('*.phpt');

$config = new PhpCsFixer\Config;
$config->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'array_push' => true,
        'array_syntax' => ['syntax' => 'short'],
        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => [
            'operators' => [
                '*=' => 'align_single_space_minimal',
                '+=' => 'align_single_space_minimal',
                '-=' => 'align_single_space_minimal',
                '/=' => 'align_single_space_minimal',
                '=' => 'align_single_space_minimal',
                '=>' => 'align_single_space_minimal',
            ],
        ],
        'blank_line_after_namespace' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
                'yield_from',
            ],
        ],
        'blank_lines_before_namespace' => [
            'max_line_breaks' => 1,
            'min_line_breaks' => 0,
        ],
        'braces_position' => [
            'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'cast_spaces' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'none',
                'method' => 'one',
                'property' => 'only_if_meta'
            ]
        ],
        'class_definition' => true,
        'clean_namespace' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'combine_nested_dirname' => true,
        'compact_nullable_type_declaration' => true,
        'concat_space' => ['spacing' => 'one'],
        'constant_case' => true,
        'control_structure_braces' => true,
        'control_structure_continuation_position' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_parentheses' => true,
        'declare_strict_types' => true,
        'dir_constant' => true,
        'echo_tag_syntax' => true,
        'elseif' => true,
        'encoding' => true,
        'ereg_to_preg' => true,
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,
        'fopen_flag_order' => true,
        'full_opening_tag' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'function_declaration' => true,
        'function_to_constant' => true,
        'get_class_to_class_keyword' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'header_comment' => ['header' => $header, 'separate' => 'none'],
        'heredoc_to_nowdoc' => true,
        'implode_call' => true,
        'include' => true,
        'increment_style' => [
            'style' => 'post',
        ],
        'indentation_type' => true,
        'integer_literal_case' => true,
        'is_null' => true,
        'lambda_not_used_import' => true,
        'line_ending' => true,
        'list_syntax' => ['syntax' => 'short'],
        'logical_operators' => true,
        'lowercase_cast' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'method_chaining_indentation' => true,
        'modernize_strpos' => true,
        'modernize_types_casting' => true,
        'multiline_comment_opening_closing' => true,
        'multiline_whitespace_before_semicolons' => true,
        'native_constant_invocation' => true,
        'native_function_casing' => false,
        'native_function_invocation' => [
            'include' => [
                '@internal',
            ],
        ],
        'native_type_declaration_casing' => true,
        'new_with_parentheses' => [
            'anonymous_class' => false,
            'named_class' => false,
        ],
        'no_alias_functions' => true,
        'no_alias_language_construct_call' => true,
        'no_alternative_syntax' => true,
        'no_binary_string' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_break_comment' => true,
        'no_closing_tag' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'no_homoglyph_names' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'print'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_multiple_statements_per_line' => true,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_space_around_double_colon' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => true,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_trailing_comma_in_singleline' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_trailing_whitespace_in_string' => true,
        'no_unneeded_braces' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_final_method' => true,
        'no_unneeded_import_alias' => true,
        'no_unreachable_default_argument_value' => true,
        'no_unset_cast' => true,
        'no_unset_on_property' => true,
        'no_unused_imports' => true,
        'no_useless_concat_operator' => true,
        'no_useless_else' => true,
        'no_useless_nullsafe_operator' => true,
        'no_useless_return' => true,
        'no_useless_sprintf' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'non_printable_character' => true,
        'normalize_index_brace' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'object_operator_without_whitespace' => true,
        'octal_notation' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'end',
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'method_public_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
                'method_protected_static',
                'method_private_static',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'const',
                'function',
                'class',
            ]
        ],
        'ordered_interfaces' => [
            'direction' => 'ascend',
            'order' => 'alpha',
        ],
        'ordered_traits' => true,
        'ordered_types' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'this',
        ],
        'phpdoc_add_missing_param_annotation' => false,
        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => true,
        'phpdoc_order_by_value' => [
            'annotations' => [
                'covers',
                'dataProvider',
                'throws',
                'uses',
            ],
        ],
        'phpdoc_param_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => ['groups' => ['simple', 'meta']],
        'phpdoc_types_order' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        'pow_to_exponentiation' => true,
        'protected_to_private' => true,
        'return_assignment' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'self_accessor' => true,
        'self_static_accessor' => true,
        'semicolon_after_instruction' => true,
        'set_type_to_cast' => true,
        'short_scalar_cast' => true,
        'simple_to_complex_string_variable' => true,
        'simplified_null_return' => false,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => true,
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_line_comment_spacing' => true,
        'single_quote' => true,
        'single_space_around_construct' => true,
        'single_trait_insert_per_statement' => true,
        'space_after_semicolon' => true,
        'spaces_inside_parentheses' => [
            'space' => 'none',
        ],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'statement_indentation' => true,
        'static_lambda' => true,
        'strict_param' => true,
        'string_length_to_empty'=> true,
        'string_line_ending' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'switch_continue_to_break' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_elvis_operator' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arguments',
                'arrays',
                'match',
            ]
        ],
        'trim_array_spaces' => true,
        'type_declaration_spaces' => [
            'elements' => [
                'function',
            ],
        ],
        'types_spaces' => true,
        'unary_operator_spaces' => true,
        'visibility_required' => [
            'elements' => [
                'const',
                'method',
                'property',
            ],
        ],
        'void_return' => true,
        'whitespace_after_comma_in_array' => true,
    ]);

$config->setCacheFile(__DIR__ . '/.php-cs-fixer.cache/' . json_decode((string) @file_get_contents('composer.json'), true)["extra"]["branch-alias"]["dev-main"] ?? 'unknown');

$config->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

return $config;
