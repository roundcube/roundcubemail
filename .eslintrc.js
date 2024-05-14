module.exports = {
    env: {
        browser: true,
        es6: true,
    },
    extends: [
        'airbnb-base',
        'plugin:unicorn/recommended',
    ],
    parserOptions: {
        ecmaVersion: '2020',
        sourceType: 'module',
    },
    ignorePatterns: [
        '!.*',
        '/vendor',
        '/public_html',
        '/plugins/jqueryui/js',
    ],
    rules: {
        'brace-style': ['error', '1tbs'],
        'class-methods-use-this': 'off',
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
            imports: 'always-multiline',
            objects: 'always-multiline',
        }],
        'consistent-return': 'off',
        curly: ['error', 'all'],
        'default-case': 'off',
        'func-names': 'off',
        'import/no-unresolved': 'off',
        'import/prefer-default-export': 'off',
        indent: ['error', 4, {
            SwitchCase: 1,
        }],
        'linebreak-style': ['error', 'unix'],
        'max-len': 'off',
        'no-console': 'off',
        'no-continue': 'off',
        'no-lonely-if': 'off',
        'no-multi-spaces': ['error', {
            exceptions: {
                Property: true,
                VariableDeclarator: true,
            },
        }],
        'no-nested-ternary': 'off',
        'no-param-reassign': 'off',
        'no-plusplus': 'off',
        'no-restricted-syntax': 'off',
        'no-underscore-dangle': 'off',
        'no-unused-vars': 'off',
        'object-shorthand': ['error', 'never'],
        'padding-line-between-statements': ['error', {
            blankLine: 'always',
            next: ['continue', 'break', 'export', 'return', 'throw'],
            prev: '*',
        }],
        'prefer-destructuring': 'off',
        'prefer-template': 'off',
        'spaced-comment': ['error', 'always', {
            block: {
                balanced: true,
                exceptions: ['*'],
                markers: ['!'],
            },
            line: {
                exceptions: ['-', '+'],
                markers: ['/'],
            },
        }],
        strict: 'off',
        'unicorn/catch-error-name': 'off',
        'unicorn/no-array-callback-reference': 'off',
        'unicorn/no-lonely-if': 'off',
        'unicorn/no-negated-condition': 'off',
        'unicorn/no-null': 'off',
        'unicorn/no-this-assignment': 'off',
        'unicorn/numeric-separators-style': 'off',
        'unicorn/prefer-array-find': 'off',
        'unicorn/prefer-array-some': 'off', // https://github.com/sindresorhus/eslint-plugin-unicorn/issues/2007
        'unicorn/prefer-module': 'off',
        'unicorn/prevent-abbreviations': 'off',
        'wrap-iife': ['error', 'inside'],

        // disable too destructive formating for now
        camelcase: 'off', // (1886 matches)
        eqeqeq: 'off', // (876 matches)
        'padding-line-between-statements': 'off', // eslint-disable-line no-dupe-keys -- (170 matches)

        // TODO rules to be removed/fixed later as fixes are not compatible with IE11
        'guard-for-in': 'off', // refactor to "for of" (32 matches)
        'no-restricted-globals': 'off', // (72 matches)
        'no-var': 'off', // (986 matches)
        'one-var': 'off', // (489 matches)
        'prefer-rest-params': 'off', // (3 matches)
        'prefer-spread': 'off', // (2 matches)
        'unicorn/no-array-for-each': 'off', // (2 matches)
        'unicorn/no-for-loop': 'off', // autofixes to "for of" // (3 matches)
        'unicorn/prefer-code-point': 'off', // (21 matches)
        'unicorn/prefer-includes': 'off', // (34 matches)
        'unicorn/prefer-number-properties': 'off', // (24 matches)
        'unicorn/prefer-optional-catch-binding': 'off', // (25 matches)
        'unicorn/prefer-spread': 'off', // (7 matches)
        'vars-on-top': 'off', // (448 matches)

        // TODO
        'array-callback-return': 'off', // (7 matches)
        'block-scoped-var': 'off', // (391 matches)
        'brace-style': 'off', // eslint-disable-line no-dupe-keys -- (69 remaining non-autofixable matches)
        'function-call-argument-newline': 'off', // (20 matches)
        'function-paren-newline': 'off', // (109 matches)
        'guard-for-in': 'off', // eslint-disable-line no-dupe-keys -- (32 matches)
        'new-cap': 'off', // (16 matches)
        'newline-per-chained-call': 'off', // (38 matches)
        'no-alert': 'off', // (3 matches)
        'no-bitwise': 'off', // (45 matches)
        'no-cond-assign': 'off', // (142 matches)
        'no-debugger': 'off', // (1 match)
        'no-empty': 'off', // (15 matches)
        'no-eval': 'off', // (2 matches)
        'no-extend-native': 'off', // (3 matches)
        'no-fallthrough': 'off', // (10 matches)
        'no-implied-eval': 'off', // (1 match)
        'no-loop-func': 'off', // (5 matches)
        'no-mixed-operators': 'off', // (8 matches)
        'no-multi-assign': 'off', // (18 matches)
        'no-multiple-empty-lines': 'off', // (85 matches)
        'no-redeclare': 'off', // (37 matches)
        'no-restricted-globals': 'off', // eslint-disable-line no-dupe-keys -- (72 matches)
        'no-script-url': 'off', // (3 matches)
        'no-sequences': 'off', // (5 matches)
        'no-shadow': 'off', // (85 matches)
        'no-undef': 'off', // (217 matches)
        'no-unneeded-ternary': 'off', // (22 matches)
        'no-unused-expressions': 'off', // (15 matches)
        'no-use-before-define': 'off', // (495 matches)
        'no-useless-escape': 'off', // (18 matches)
        'no-var': 'off', // eslint-disable-line no-dupe-keys -- (986 matches)
        'one-var': 'off', // eslint-disable-line no-dupe-keys -- (489 matches)
        'one-var-declaration-per-line': 'off', // (501 matches)
        'operator-assignment': 'off', // (4 matches)
        'prefer-arrow-callback': 'off', // (423 matches)
        'prefer-rest-params': 'off', // eslint-disable-line no-dupe-keys -- (3 matches)
        'prefer-spread': 'off', // eslint-disable-line no-dupe-keys -- (2 matches)
        'unicorn/better-regex': 'off', // (42 matches)
        'unicorn/consistent-function-scoping': 'off', // (19 matches)
        'unicorn/explicit-length-check': 'off', // (219 matches)
        'unicorn/filename-case': 'off', // (3 matches)
        'unicorn/new-for-builtins': 'off', // (2 matches)
        'unicorn/no-array-for-each': 'off', // eslint-disable-line no-dupe-keys -- (2 matches)
        'unicorn/no-document-cookie': 'off', // (1 match)
        'unicorn/no-for-loop': 'off', // eslint-disable-line no-dupe-keys -- (3 matches)
        'unicorn/no-nested-ternary': 'off', // (2 matches)
        'unicorn/no-new-array': 'off', // (1 match)
        'unicorn/no-typeof-undefined': 'off', // (3 matches)
        'unicorn/prefer-add-event-listener': 'off', // (28 matches)
        'unicorn/prefer-at': 'off', // (7 matches)
        'unicorn/prefer-code-point': 'off', // eslint-disable-line no-dupe-keys -- (21 matches)
        'unicorn/prefer-date-now': 'off', // (25 matches)
        'unicorn/prefer-default-parameters': 'off', // (1 match)
        'unicorn/prefer-dom-node-append': 'off', // (28 matches)
        'unicorn/prefer-dom-node-dataset': 'off', // (1 match)
        'unicorn/prefer-dom-node-remove': 'off', // (4 matches)
        'unicorn/prefer-includes': 'off', // eslint-disable-line no-dupe-keys -- (34 matches)
        'unicorn/prefer-logical-operator-over-ternary': 'off', // (23 matches)
        'unicorn/prefer-modern-dom-apis': 'off', // (2 matches)
        'unicorn/prefer-negative-index': 'off', // (1 match)
        'unicorn/prefer-number-properties': 'off', // eslint-disable-line no-dupe-keys -- (24 matches)
        'unicorn/prefer-optional-catch-binding': 'off', // eslint-disable-line no-dupe-keys -- (25 matches)
        'unicorn/prefer-query-selector': 'off', // (61 matches)
        'unicorn/prefer-regexp-test': 'off', // (31 matches)
        'unicorn/prefer-spread': 'off', // eslint-disable-line no-dupe-keys -- (7 matches)
        'unicorn/prefer-string-replace-all': 'off', // (43 matches)
        'unicorn/prefer-string-slice': 'off', // (39 matches)
        'unicorn/prefer-string-starts-ends-with': 'off', // (2 matches)
        'unicorn/prefer-string-raw': 'off', // (2 matches)
        'unicorn/prefer-ternary': 'off', // (16 matches)
        'unicorn/require-array-join-separator': 'off', // (5 matches)
        'unicorn/switch-case-braces': 'off', // (161 matches)
        'vars-on-top': 'off', // eslint-disable-line no-dupe-keys -- (448 matches)
    },
    reportUnusedDisableDirectives: true,
    globals: {
        jQuery: true,
        $: true,
        rcmail: true,
        rcube_event: true,
        rcube_event_engine: true,
        rcube_webmail: true,
    },
};
