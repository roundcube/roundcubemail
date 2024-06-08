<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__])
    ->exclude([
        'node_modules',
        'vendor',
    ])
    ->ignoreDotFiles(false)
    ->name('*.php.dist')
    ->name('*.sh');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,

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
        'native_constant_invocation' => [
            'include' => [
                // https://github.com/php/php-src/commit/2475337bd8a0fad0dac03db3f5e7e9d331d53653
                'LOG_LOCAL0',
                'LOG_LOCAL1',
                'LOG_LOCAL2',
                'LOG_LOCAL3',
                'LOG_LOCAL4',
                'LOG_LOCAL5',
                'LOG_LOCAL6',
                'LOG_LOCAL7',
                // https://github.com/php/php-src/blob/php-8.3.0/ext/ldap/ldap.stub.php#L104
                'LDAP_OPT_PROTOCOL_VERSION',
                // https://github.com/php/pecl-text-pspell/blob/1.0.1/pspell.stub.php#L24
                'PSPELL_FAST',
                // https://github.com/websupport-sk/pecl-memcache/blob/8.2/src/memcache.c#L755
                'MEMCACHE_COMPRESSED',
            ],
        ],
        'native_function_invocation' => false,
        'void_return' => false,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'exit'],
        ],
        'final_internal_class' => false,
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

        // fn => without curly brackets is less readable,
        // also prevent bounding of unwanted variables for GC
        'use_arrow_functions' => false,

        // disable too destructive formating for now
        'blank_line_before_statement' => false,
        'declare_strict_types' => false,
        'increment_style' => [
            'style' => 'post',
        ],
        'php_unit_data_provider_name' => [
            'prefix' => 'provide_',
            'suffix' => '_cases',
        ],
        'php_unit_method_casing' => false,
        'php_unit_test_case_static_method_calls' => false,
        'psr_autoloading' => false,
        'strict_comparison' => false,

        // TODO
        'array_indentation' => false,
        'general_phpdoc_annotation_remove' => false,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'modernize_types_casting' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_break_comment' => false,
        'phpdoc_summary' => false,
        'string_length_to_empty' => false,

        // TODO - risky
        'no_unset_on_property' => false,
        'random_api_migration' => false,
        'strict_param' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(sys_get_temp_dir() . '/php-cs-fixer.' . md5(__DIR__) . '.cache');
