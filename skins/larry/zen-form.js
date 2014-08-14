/** Zen Forms 1.0.3 | MIT License | git.io/zen-form */

(function ($) {

    $.fn.zenForm = function (settings) {

        settings = $.extend({
            trigger: '.go-zen',
            theme: 'dark'
        }, settings);

        /**
         * Helper functions
         */
        var Utils = {

            /**
             * (Un)Wrap body content to hide overflow
             */
            bodyWrap: function () {

                var $body = $('body'),
                    $wrap = $body.children('.zen-forms-body-wrap');

                if ($wrap.length) {
                    $wrap.children().unwrap();
                } else {
                    $body.wrapInner('<div class="zen-forms-body-wrap"/>');
                }

            }, // bodyWrap

            /**
             * Watch inputs and add "empty" class if needed
             */
            watchEmpty: function () {

                App.Environment.find('input, textarea, select').each(function () {

                   $(this).on('change', function () {

                        $(this)[$(this).val() ? 'removeClass' : 'addClass']('empty');

                   }).trigger('change');

                });

            },

            /**
             * Custom styled selects
             */
            customSelect: function ($select, $customSelect) {

                var $selected;

                $customSelect.on('click', function (event) {

                    event.stopPropagation();

                    $selected = $customSelect.find('.selected');

                    $customSelect.toggleClass('is-open');

                    if ($customSelect.hasClass('is-open')) {
                        $customSelect.scrollTop(
                            $selected.position().top - $selected.outerHeight()
                        );
                    }


                }).find('a').on('click', function () {

                    $(this).addClass('selected').siblings().removeClass('selected');

                    $select.val($(this).data('value'));

                });

            }, // customSelect

            /**
             * Hide any elements(mostly selects) when clicked outside them
             */
            manageSelects: function () {

                $(document).on('click', function () {
                    $('.is-open').removeClass('is-open');
                });

            }, // manageSelects

            /**
             * Hide any elements(mostly selects) when clicked outside them
             */
            focusFirst: function () {

                var $first = App.Environment.find('input').first();

                // we need to re-set value to remove focus selection
                $first.focus().val($first.val());

            } // focusFirst

        }, // Utils

        /**
         * Core functionality
         */
        App = {

            /**
             * Orginal form element
             */
            Form: null,

            /**
             * Wrapper element
             */
            Environment: null,

            /**
             * Functions to create and manipulate environment
             */
            env: {


                /**
                 * Object where elements created with App.env.addObject are appended
                 */
                wrapper: null,

                create: function () {

                    // Callback: zf-initialize
                    App.Form.trigger('zf-initialize');

                    Utils.bodyWrap();

                    App.Environment = $('<div>', {
                        class: 'zen-forms' + (settings.theme == 'dark' ? '' : ' light-theme')
                    }).hide().appendTo('body').fadeIn(200);

                    // ESC to exit. Thanks @ktmud
                    $('body').on('keydown', function (event) {

                        if (event.which == 27)
                            App.env.destroy($elements);

                    });

                    return App.Environment;

                }, // create

                /**
                 * Update orginal inputs with new values and destroy Environment
                 */
                destroy: function ($elements) {

                    // Callback: zf-destroy
                    App.Form.trigger('zf-destroy', App.Environment);

                    $('body').off('keydown');

                    // Update orginal inputs with new values
                    $elements.each(function (i) {

                        var $el = $('#zen-forms-input' + i);

                        if ($el.length) {
                            $(this).val($el.val());
                        }

                    });

                    Utils.bodyWrap();

                    // Hide and remove Environment
                    App.Environment.fadeOut(200, function () {

                        App.env.wrapper = null;

                        App.Environment.remove();

                    });

                    // Callback: zf-destroyed
                    App.Form.trigger('zf-destroyed');

                }, // destroy

                /**
                 * Append inputs, textareas to Environment
                 */
                add: function ($elements) {

                    var $el, $label, value, id, ID, label;

                    $elements.each(function (i) {

                        App.env.wrapper = App.env.createObject('div', {
                            class: 'zen-forms-input-wrap'
                        }).appendTo(App.Environment);

                        $el = $(this);

                        value = $el.val();

                        id = $el.attr('id');

                        ID = 'zen-forms-input' + i;

                        label = $el.data('label') || $("label[for=" + id + "]").text() || $el.attr('placeholder') || '';

                        // Exclude specified elements
                        if ($.inArray( $el.attr('type'), ['checkbox', 'radio', 'submit']) == -1) {

                            if ($el.is('input') )
                                App.env.addInput($el, ID, value);
                            else if ($el.is('select') )
                                App.env.addSelect($el, ID);
                            else
                                App.env.addTextarea($el, ID, value);

                            $label = App.env.addObject('label', {
                                for: ID,
                                text: label
                            });

                            if ($el.is('select') )
                                $label.prependTo(App.env.wrapper);

                        }

                    });

                    // Callback: zf-initialized
                    App.Form.trigger('zf-initialized', App.Environment);

                }, // add

                addInput: function ($input, ID, value) {

                    return App.env.addObject('input', {
                        id: ID,
                        value: value,
                        class: 'input',
                        type: $input.attr('type')
                    });

                }, // addInput

                addTextarea: function ($textarea, ID, value) {

                    return App.env.addObject('textarea', {
                        id: ID,
                        text: value,
                        rows: 5,
                        class: 'input'
                    });

                }, // addTextarea

                addSelect: function ($orginalSelect, ID) {

                    var $select = App.env.addObject('select', {
                            id: ID,
                            class: 'select'
                        }),
                        $options = $orginalSelect.find('option'),
                        $customSelect = App.env.addObject('div', {
                            class: 'custom-select-wrap',
                            html: '<div class="custom-select"></div>'
                        }).children();

                    $select.append($options.clone());

                    $.each($options, function (i, option) {

                        App.env.createObject('a', {
                            href: '#',
                            html: '<span>' + $(option).text() + '</span>' ,
                            'data-value': $(option).attr('value'),
                            class: $(option).prop('selected') ? 'selected' : ''
                        }).appendTo($customSelect);

                    });

                    $select.val($orginalSelect.val());

                    Utils.customSelect($select, $customSelect);

                    return $customSelect;

                }, // addSelect

                /**
                 * Wrapper for creating jQuery objects
                 */
                createObject: function (type, params, fn, fnMethod) {

                    return $('<'+type+'>', params).on(fnMethod || 'click', fn);

                }, // createObject

                /**
                 * Wrapper for adding jQuery objects to wrapper
                 */
                addObject: function (type, params, fn, fnMethod) {

                    return App.env.createObject(type, params, fn, fnMethod).appendTo(App.env.wrapper || App.Environment);

                }, // addObject

                switchTheme: function () {

                    App.Environment.toggleClass('light-theme');

                } // switchTheme

            }, // env

            zen: function ($elements) {

                // Create environment
                App.env.create();

                // Add wrapper div for close and theme buttons
                App.env.wrapper = App.env.createObject('div', {
                    class: 'zen-forms-header'
                }).appendTo(App.Environment);

                // Add close button
                App.env.addObject('a', {
                    class: 'zen-forms-close-button',
                    html: '<i class="zen-icon zen-icon--close"></i> Exit Zen Mode'
                }, function () {
                    App.env.destroy($elements);
                });

                // Add theme switch button
                App.env.addObject('a', {
                    class: 'zen-forms-theme-switch',
                    html: '<i class="zen-icon zen-icon--theme"></i> Switch theme'
                }, function () {
                    App.env.switchTheme();
                });

                // Add inputs and textareas from form
                App.env.add($elements);

                // Additional select functionality
                Utils.manageSelects();

                // Select first input
                Utils.focusFirst();

                // Add .empty class for empty inputs
                Utils.watchEmpty();

            } // zen

        }; // App

        App.Form = $(this);

        var $elements = App.Form.is('form') ? App.Form.find('input, textarea, select') : App.Form;

        $(settings.trigger).on('click', function (event) {

            event.preventDefault();

            App.zen($elements);

        });

        // Command: destroy
        App.Form.on('destroy', function () {
            App.env.destroy($elements);
        });

        // Command: init
        App.Form.on('init', function () {
            App.zen($elements);
        });

        return this;

    };

})(jQuery);
