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
        '/plugins/jqueryui/js',
    ],
    rules: {
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

        // TODO rules to be removed/fixed later as fixes are not compatible with IE11
        'guard-for-in': 'off', // refactor to "for of"
        'no-restricted-globals': 'off',
        'no-restricted-properties': 'off',
        'no-var': 'off',
        'one-var': 'off',
        'prefer-const': 'off',
        'prefer-exponentiation-operator': 'off',
        'prefer-rest-params': 'off',
        'prefer-spread': 'off',
        'semi-style': 'off',
        'unicorn/no-array-for-each': 'off',
        'unicorn/no-for-loop': 'off', // autofixes to "for of"
        'unicorn/prefer-code-point': 'off',
        'unicorn/prefer-includes': 'off',
        'unicorn/prefer-node-protocol': 'off', // needs Node 14+
        'unicorn/prefer-number-properties': 'off',
        'unicorn/prefer-optional-catch-binding': 'off',
        'unicorn/prefer-prototype-methods': 'off',
        'unicorn/prefer-reflect-apply': 'off',
        'unicorn/prefer-spread': 'off',
        'unicorn/prefer-top-level-await': 'off', // needs Node 14+
        'vars-on-top': 'off',

        // TODO
        'indent': 'off', // eslint-disable-line no-dupe-keys
        'space-infix-ops': 'off',
        'quotes': 'off',
        'no-tabs': 'off',
        'space-before-function-paren': 'off',
        'no-undef': 'off',
        'no-shadow': 'off',
        'key-spacing': 'off',
        'block-spacing': 'off',
        'camelcase': 'off',
        'object-curly-spacing': 'off',
        'unicorn/prevent-abbreviations': 'off', // eslint-disable-line no-dupe-keys
        'no-var': 'off', // eslint-disable-line no-dupe-keys
        'one-var-declaration-per-line': 'off',
        'curly': 'off', // eslint-disable-line no-dupe-keys
        'semi': 'off',
        'space-in-parens': 'off',
        'space-before-blocks': 'off',
        'nonblock-statement-body-position': 'off',
        'brace-style': 'off',
        'eqeqeq': 'off',
        'semi-spacing': 'off',
        'array-bracket-spacing': 'off',
        'one-var': 'off', // eslint-disable-line no-dupe-keys
        'keyword-spacing': 'off',
        'yoda': 'off',
        'no-unused-expressions': 'off',
        'padding-line-between-statements': 'off', // eslint-disable-line no-dupe-keys
        'no-use-before-define': 'off',
        'prefer-arrow-callback': 'off',
        'no-sequences': 'off',
        'vars-on-top': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/no-null': 'off', // eslint-disable-line no-dupe-keys
        'block-scoped-var': 'off',
        'object-curly-newline': 'off',
        'no-return-assign': 'off',
        'unicorn/explicit-length-check': 'off',
        'unicorn/switch-case-braces': 'off',
        'no-mixed-operators': 'off',
        'no-cond-assign': 'off',
        'padded-blocks': 'off',
        'switch-colon-spacing': 'off',
        'comma-dangle': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-module': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-number-properties': 'off', // eslint-disable-line no-dupe-keys
        'strict': 'off', // eslint-disable-line no-dupe-keys
        'no-redeclare': 'off',
        'no-extra-semi': 'off',
        'function-paren-newline': 'off',
        'unicorn/no-this-assignment': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/no-nested-ternary': 'off',
        'unicorn/no-negated-condition': 'off', // eslint-disable-line no-dupe-keys
        'computed-property-spacing': 'off',
        'no-restricted-globals': 'off', // eslint-disable-line no-dupe-keys
        'quote-props': 'off',
        'no-multiple-empty-lines': 'off',
        'wrap-iife': 'off', // eslint-disable-line no-dupe-keys
        'no-multi-assign': 'off',
        'no-multi-spaces': 'off', // eslint-disable-line no-dupe-keys
        'newline-per-chained-call': 'off',
        'unicorn/prefer-query-selector': 'off',
        'unicorn/better-regex': 'off',
        'unicorn/prefer-string-replace-all': 'off',
        'no-void': 'off',
        'prefer-rest-params': 'off', // eslint-disable-line no-dupe-keys
        'no-bitwise': 'off',
        'spaced-comment': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-string-slice': 'off',
        'unicorn/catch-error-name': 'off', // eslint-disable-line no-dupe-keys
        'no-useless-escape': 'off',
        'guard-for-in': 'off', // eslint-disable-line no-dupe-keys
        'object-property-newline': 'off',
        'unicorn/consistent-function-scoping': 'off',
        'unicorn/prefer-regexp-test': 'off',
        'unicorn/prefer-optional-catch-binding': 'off', // eslint-disable-line no-dupe-keys
        'dot-notation': 'off',
        'unicorn/prefer-includes': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-spread': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-reflect-apply': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/no-array-callback-reference': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-dom-node-append': 'off',
        'unicorn/numeric-separators-style': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-add-event-listener': 'off',
        'unicorn/no-lonely-if': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-date-now': 'off',
        'unicorn/prefer-code-point': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-logical-operator-over-ternary': 'off',
        'no-unneeded-ternary': 'off',
        'no-empty': 'off',
        'new-cap': 'off',
        'function-call-argument-newline': 'off',
        'unicorn/filename-case': 'off',
        'no-else-return': 'off',
        'unicorn/prefer-ternary': 'off',
        'new-parens': 'off',
        'no-fallthrough': 'off',
        'operator-linebreak': 'off',
        'space-unary-ops': 'off',
        'radix': 'off',
        'unicorn/no-array-method-this-argument': 'off',
        'no-floating-decimal': 'off',
        'array-callback-return': 'off',
        'prefer-spread': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-string-starts-ends-with': 'off',
        'unicorn/require-array-join-separator': 'off',
        'unicorn/prefer-at': 'off',
        'unicorn/no-typeof-undefined': 'off',
        'unicorn/prefer-dom-node-remove': 'off',
        'no-throw-literal': 'off',
        'no-loop-func': 'off',
        'prefer-exponentiation-operator': 'off', // eslint-disable-line no-dupe-keys
        'no-restricted-properties': 'off', // eslint-disable-line no-dupe-keys
        'operator-assignment': 'off',
        'no-alert': 'off',
        'unicorn/escape-case': 'off',
        'unicorn/prefer-dom-node-dataset': 'off',
        'unicorn/empty-brace-spaces': 'off',
        'unicorn/no-for-loop': 'off', // eslint-disable-line no-dupe-keys
        'lines-around-directive': 'off',
        'unicorn/no-hex-escape': 'off',
        'no-script-url': 'off',
        'no-extend-native': 'off',
        'no-shadow-restricted-names': 'off',
        'unicorn/prefer-math-trunc': 'off',
        'no-labels': 'off',
        'unicorn/prefer-modern-math-apis': 'off',
        'no-eval': 'off',
        'unicorn/new-for-builtins': 'off',
        'unicorn/no-array-for-each': 'off', // eslint-disable-line no-dupe-keys
        'unicorn/prefer-modern-dom-apis': 'off',
        'no-extra-boolean-cast': 'off',
        'no-control-regex': 'off',
        'no-label-var': 'off',
        'global-require': 'off',
        'prefer-regex-literals': 'off',
        'no-array-constructor': 'off',
        'eol-last': 'off',
        'unicorn/no-new-array': 'off',
        'no-debugger': 'off',
        'no-whitespace-before-property': 'off',
        'no-constant-condition': 'off',
        'no-implied-eval': 'off',
        'unicorn/no-document-cookie': 'off',
        'unicorn/prefer-default-parameters': 'off',
        'unicorn/prefer-negative-index': 'off',
        'no-regex-spaces': 'off',
        'unicorn/no-useless-undefined': 'off',
    },
    reportUnusedDisableDirectives: true,
    globals: {
        jQuery: true,
    },
};
