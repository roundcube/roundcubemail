<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__])
    ->exclude(['vendor']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PHP74Migration' => true,

        // required by PSR-12
        'concat_space' => [
            'spacing' => 'one',
        ],

        // disable some too strict rules
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'single_line_throw' => false,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
        ],
        'native_constant_invocation' => true,
        'native_function_invocation' => false,
        'void_return' => false,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'exit'],
        ],
        'combine_consecutive_issets' => false,
        'combine_consecutive_unsets' => false,
        'multiline_whitespace_before_semicolons' => false,
        'no_superfluous_elseif' => false,
        'ordered_class_elements' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_add_missing_param_annotation' => false,
        'return_assignment' => false,
        'comment_to_phpdoc' => false,
        'general_phpdoc_annotation_remove' => [
            'annotations' => ['author', 'copyright', 'throws'],
        ],
        'nullable_type_declaration_for_default_null_value' => [
            'use_nullable_type_declaration' => false,
        ],

        // fn => without curly brackets is less readable,
        // also prevent bounding of unwanted variables for GC
        'use_arrow_functions' => false,

        // disable too destructive formating for now
        'escape_implicit_backslashes' => false,
        'heredoc_to_nowdoc' => false,
        'no_useless_else' => false,
        'no_useless_return' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => false,
        'phpdoc_var_annotation_correct_order' => false,
        'protected_to_private' => false,
        'simple_to_complex_string_variable' => false,
        'single_line_comment_style' => false,

        // enable some safe rules from @PHP71Migration:risky
        'pow_to_exponentiation' => true,
        'is_null' => true,
        'modernize_types_casting' => true,
        'dir_constant' => true,
        'combine_nested_dirname' => true,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],

        // TODO
        'align_multiline_comment' => false,
        'array_indentation' => false,
        'array_syntax' => false,
        'backtick_to_shell_exec' => false,
        'binary_operator_spaces' => false,
        'blank_line_before_statement' => false,
        'class_attributes_separation' => false,
        'class_definition' => false,
        'class_reference_name_casing' => false,
        'class_reference_name_casing' => false,
        'concat_space' => false,
        'constant_case' => false,
        'control_structure_continuation_position' => false,
        'dir_constant' => false,
        'elseif' => false,
        'empty_loop_condition' => false,
        'explicit_indirect_variable' => false,
        'explicit_string_variable' => false,
        'function_declaration' => false,
        'general_phpdoc_annotation_remove' => false,
        'global_namespace_import' => false,
        'heredoc_indentation' => false,
        'increment_style' => false,
        'integer_literal_case' => false,
        'is_null' => false,
        'list_syntax' => false,
        'method_argument_space' => false,
        'method_chaining_indentation' => false,
        'modernize_types_casting' => false,
        'native_constant_invocation' => false,
        'native_type_declaration_casing' => false,
        'new_with_parentheses' => false,
        'no_alias_language_construct_call' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_break_comment' => false,
        'no_empty_statement' => false,
        'no_extra_blank_lines' => false,
        'no_null_property_initialization' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_unneeded_control_parentheses' => false,
        'no_useless_concat_operator' => false,
        'operator_linebreak' => false,
        'php_unit_method_casing' => false,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_no_package' => false,
        'phpdoc_scalar' => false,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => false,
        'phpdoc_types_order' => false,
        'phpdoc_var_without_name' => false,
        'single_line_comment_spacing' => false,
        'single_quote' => false,
        'single_trait_insert_per_statement' => false,
        'standardize_increment' => false,
        'ternary_to_null_coalescing' => false,
        'trailing_comma_in_multiline' => false,
        'visibility_required' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(sys_get_temp_dir() . '/php-cs-fixer.' . md5(__DIR__) . '.cache');
