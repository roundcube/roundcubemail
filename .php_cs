<?php
return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'long'],
        'binary_operator_spaces' => false,
        'concat_space' => ['spacing' => 'one'],
        'heredoc_to_nowdoc' => true,
        'method_argument_space' => true,
        'modernize_types_casting' => false,
        'no_extra_consecutive_blank_lines' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'],
        'no_php4_constructor' => true,
        'no_short_echo_tag' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_fqcn_annotation' => false,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'phpdoc_trim' => false,
        'semicolon_after_instruction' => true,
        'simplified_null_return' => true,
        'single_quote' => false,
        'yoda_style' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/installer')
            ->in(__DIR__ . '/plugins')
            ->in(__DIR__ . '/program')
            ->in(__DIR__ . '/tests')
    )
;
