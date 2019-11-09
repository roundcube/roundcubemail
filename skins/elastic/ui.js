/**
 * Roundcube webmail functions for the Elastic skin
 *
 * Copyright (c) The Roundcube Dev Team
 *
 * The contents are subject to the Creative Commons Attribution-ShareAlike
 * License. It is allowed to copy, distribute, transmit and to adapt the work
 * by keeping credits to the original autors in the README file.
 * See http://creativecommons.org/licenses/by-sa/3.0/ for details.
 *
 * @license magnet:?xt=urn:btih:90dc5c0be029de84e523b9b3922520e79e0e6f08&dn=cc0.txt CC0-1.0
 */

"use strict";

function rcube_elastic_ui()
{
    var prefs, ref = this,
        mode = 'normal', // one of: large, normal, small, phone
        touch = false,
        ios = false,
        popups_close_lock,
        is_framed = rcmail.is_framed(),
        env = {
            config: {
                standard_windows: rcmail.env.standard_windows,
                message_extwin: rcmail.env.message_extwin,
                compose_extwin: rcmail.env.compose_extwin,
                help_open_extwin: rcmail.env.help_open_extwin
            },
            checkboxes: 0,
            small_screen_config: {
                standard_windows: true,
                message_extwin: false,
                compose_extwin: false,
                help_open_extwin: false
            }
        },
        menus = {},
        content_buttons = [],
        frame_buttons = [],
        layout = {
            menu: $('#layout-menu'),
            sidebar: $('#layout-sidebar'),
            list: $('#layout-list'),
            content: $('#layout-content'),
        },
        buttons = {
            menu: $('a.task-menu-button'),
            back_sidebar: $('a.back-sidebar-button'),
            back_list: $('a.back-list-button'),
            back_content: $('a.back-content-button'),
        };


    // Public methods
    this.register_content_buttons = register_content_buttons;
    this.menu_hide = menu_hide;
    this.menu_toggle = menu_toggle;
    this.menu_destroy = menu_destroy;
    this.popup_init = popup_init;
    this.about_dialog = about_dialog;
    this.headers_dialog = headers_dialog;
    this.import_dialog = import_dialog;
    this.headers_show = headers_show;
    this.spellmenu = spellmenu;
    this.searchmenu = searchmenu;
    this.headersmenu = headersmenu;
    this.header_reset = header_reset;
    this.compose_status = compose_status;
    this.attachmentmenu = attachmentmenu;
    this.mailtomenu = mailtomenu;
    this.recipient_selector = recipient_selector;
    this.show_list = show_list;
    this.show_sidebar = show_sidebar;
    this.smart_field_init = smart_field_init;
    this.smart_field_reset = smart_field_reset;
    this.form_errors = form_errors;
    this.switch_nav_list = switch_nav_list;
    this.searchbar_init = searchbar_init;
    this.pretty_checkbox = pretty_checkbox;
    this.pretty_select = pretty_select;
    this.datepicker_init = datepicker_init;
    this.bootstrap_style = bootstrap_style;


    // Detect screen size/mode
    screen_mode();

    // Initialize layout
    layout_init();

    // Convert some elements to Bootstrap style
    bootstrap_style();

    // Initialize responsive toolbars (have to be before popups init)
    toolbar_init();

    // Initialize content frame and list handlers
    content_frame_init();

    // Initialize menu dropdowns
    dropdowns_init();

    // Setup various UI elements
    setup();

    // Update layout after initialization
    resize();


    /**
     * Setup procedure
     */
    function setup()
    {
        var title, form, content_buttons = [];

        // Intercept jQuery-UI dialogs...
        $.ui && $.widget('ui.dialog', $.ui.dialog, {
            open: function() {
                // ... to unify min width for iframe'd dialogs
                if ($(this.element).is('.iframe')) {
                    this.options.width = Math.max(576, this.options.width);
                }
                this._super();
                // ... to re-style them on dialog open
                dialog_open(this);
                return this;
            },
            close: function() {
                this._super();
                // ... to close custom select dropdowns on dialog close
                $('.select-menu:visible').remove();
                return this;
            }
        });

        // menu/sidebar/list button
        buttons.menu.on('click', function() { app_menu(true); return false; });
        buttons.back_sidebar.on('click', function() { show_sidebar(); return false; });
        buttons.back_list.on('click', function() { show_list(); return false; });
        buttons.back_content.on('click', function() { show_content(true); return false; });

        // Initialize search forms
        $('.searchbar').each(function() { searchbar_init(this); });

        // Set content frame title in parent window (exclude ext-windows and dialog frames)
        if (is_framed && !rcmail.env.extwin && !parent.$('.ui-dialog:visible').length) {
            if (title = $('h1.voice').first().text()) {
                parent.$('#layout-content > .header > .header-title:not(.constant)').text(title);
            }
        }
        else if (!is_framed) {
            title = layout.content.find('.boxtitle').first().detach().text();

            if (!title) {
                title = $('h1.voice').first().text();
            }

            if (title) {
                layout.content.find('.header > .header-title').text(title);
            }
        }

        // Add content frame toolbar in the footer, for content buttons and navigation
        if (!is_framed && layout.content.length && !layout.content.is('.no-navbar')
            && !layout.content.children('.frame-content').length
        ) {
            env.frame_nav = $('<div class="footer menu toolbar content-frame-navigation hide-nav-buttons">')
                .append($('<a class="button prev">')
                    .append($('<span class="inner"></span>').text(rcmail.gettext('previous'))))
                .append($('<span class="buttons">'))
                .append($('<a class="button next">')
                    .append($('<span class="inner"></span>').text(rcmail.gettext('next'))))
                .appendTo(layout.content);
        }

        // Move some buttons to the frame footer toolbar
        $('a[data-content-button]').each(function() {
            content_buttons.push(create_cloned_button($(this)));
        });

        // Move form buttons from the content frame into the frame footer (on parent window)
        $('.formbuttons').filter(function() { return !$(this).parent('.searchoptions').length; }).children().each(function() {
            var target = $(this);

            // skip non-content buttons
            if (!is_framed && !target.parents('#layout-content').length) {
                return;
            }

            if (target.is('.cancel')) {
                target.addClass('hidden');
                return;
            }

            content_buttons.push(create_cloned_button(target));
        });

        (is_framed ? parent.UI : ref).register_content_buttons(content_buttons);

        // Mail compose features
        if (form = rcmail.gui_objects.messageform) {
            form = $('form[name="' + form + '"]');
            // Show input elements with non-empty value
            // These event handlers need to be registered before rcmail 'init' event
            $('#_cc, #_bcc, #_replyto, #_followupto', $('.compose-headers')).each(function() {
                $(this).on('change', function() {
                    $('#compose' + $(this).attr('id'))[this.value ? 'removeClass' : 'addClass']('hidden');
                });
            });

            // We put compose options outside of the main form
            // Because IE/Edge (<16) does not support 'form' attribute we'll copy
            // inputs into the main form as hidden fields
            // TODO: Consider doing this for IE/Edge only, just set the 'form' attribute on others
            $('#compose-options').find('textarea,input,select').each(function() {
                var hidden = $('<input>')
                    .attr({type: 'hidden', name: $(this).attr('name')})
                    .appendTo(form);

                $(this).attr('tabindex', 2)
                    .on('change', function() {
                        hidden.val(this.type != 'checkbox' || this.checked ? $(this).val() : '');
                    })
                    .change();
            });
        }

        // Use smart recipient inputs
        // This have to be after mail compose feature above
        $('[data-recipient-input]').each(function() { recipient_input(this); });

        // Image upload widget
        $('.image-upload').each(function() { image_upload_input(this); });

        // Add HTML/Plain tabs (switch) on top of textarea with TinyMCE editor
        $('textarea[data-html-editor]').each(function() { html_editor_init(this); });

        $('#dragmessage-menu,#dragcontact-menu').each(function() {
            rcmail.gui_object('dragmenu', this.id);
        });

        // Taskmenu items added by plugins do not use elastic classes (e.g help plugin)
        // it's for larry skin compat. We'll assign 'selected' and icon-specific class.
        $('#taskmenu > a').each(function() {
            if (/button-([a-z]+)/.test(this.className)) {
                var data, name = RegExp.$1,
                    button = find_button(this.id);

                if (button && (data = button.data)) {
                    if (data.sel) {
                        data.sel = data.sel.replace('button-selected', 'selected') + ' ' + name;
                    }

                    if (data.act) {
                        data.act += ' ' + name;
                    }

                    rcmail.buttons[button.command][button.index] = data;
                    rcmail.init_button(button.command, data);
                }

                $(this).addClass(name);
                $('.button-inner', this).addClass('inner');
            }

            $(this).on('mouseover', function() { rcube_webmail.long_subject_title(this, 0, $('span.inner', this)); });
        });

        // Some plugins use 'listbubtton' class, we'll replace it with 'button'
        $('.listbutton').each(function() {
            var button = find_button(this.id);

            $(this).addClass('button').removeClass('listbutton');

            if (button.data.sel) {
                button.data.sel = button.data.sel.replace('listbutton', 'button');
            }
            if (button.data.act) {
                button.data.act = button.data.act.replace('listbutton', 'button');
            }

            rcmail.buttons[button.command][button.index] = button.data;
            rcmail.init_button(button.command, button.data);
        });

        // buttons that should be hidden on small screen devices
        $('[data-hidden]').each(function() {
            var m, v = $(this).data('hidden'),
                parent = $(this).parent('li'),
                re = /(large|big|small|phone|lbs)/g;

                while (m = re.exec(v)) {
                    $(parent.length ? parent : this).addClass('hidden-' + m[1]);
                }
        });

        // Modify normal checkboxes on lists so they are different
        // than those used for row selection, i.e. use icons
        $('[data-list]').each(function() {
            $('input[type=checkbox]', this).each(function() { pretty_checkbox(this); });
        });

        // Assign .formcontainer class to the iframe body, when it
        // contains .formcontent and .formbuttons.
        if (is_framed) {
            $('.formcontent').each(function() {
                if ($(this).next('.formbuttons').length) {
                    $(this).parent().addClass('formcontainer');
                }
            });
        }

        // move "Download all attachments" button into a better location
        $('#attachment-list + a.zipdownload').appendTo('.header-links');

        if (ios = $('html').is('.ipad,.iphone')) {
            $('.iframe-wrapper, .scroller').addClass('ios-scroll');
        }

        if ($('html').filter('.ipad,.iphone,.webkit.mobile,.webkit.tablet').addClass('webkit-scroller').length) {
            $(layout.menu).addClass('webkit-scroller');
        }

        // Set .notree class on treelist widget update
        $('.treelist').each(function() {
            var list = this, callback = function() {
                    $(list)[$('.treetoggle', list).length > 0 ? 'removeClass' : 'addClass']('notree');
                };

            if (window.MutationObserver) {
                (new MutationObserver(callback)).observe(list, {childList: true, subtree: true});
            }
            callback();

            // Add title with full folder name on hover
            // TODO: This should be done in another way, so if an entry is
            // added after page load it also works there.
            $('li.mailbox > a').on('mouseover', function() { rcube_webmail.long_subject_title_ex(this); });
        });

        // Store default logo path if not already set
        if (!$('#logo').data('src-default')) {
            $('#logo').data('src-default', $('#logo').attr('src'));
        }
    };

    /**
     * Moves form buttons into the content frame actions toolbar (for mobile)
     */
    function register_content_buttons(buttons)
    {
        // we need these buttons really only in phone mode
        if (/*mode == 'phone' && */ env.frame_nav && buttons && buttons.length) {
            var toolbar = env.frame_nav.children('.buttons');

            content_buttons = [];
            $.each(buttons, function() {
                if (this.data('target')) {
                    content_buttons.push(this.data('target'));
                }
            });

            toolbar.html('').append(buttons);
        }
    };

    /**
     * Registers cloned button
     */
    function register_cloned_button(old_id, new_id, active_class)
    {
        var button = find_button(old_id);
        if (button) {
            rcmail.register_button(button.command, new_id, button.data.type, active_class, button.data.sel);
        }
    };

    /**
     * Create a button clone for use in toolbar
     */
    function create_cloned_button(target, menu_button, add_class, always_active)
    {
        var popup, click = true,
            button = $('<a>'),
            target_id = target.attr('id') || new Date().getTime(),
            button_id = target_id + '-clone',
            btn_class = target[0].className + (add_class ? ' ' + add_class : '');

        if (!menu_button) {
            btn_class = $.trim(btn_class.replace('btn-primary', 'primary').replace(/(btn[a-z-]*|button|disabled)/g, ''))
            btn_class += ' button' + (!always_active ? ' disabled' : '');
        }
        else if (popup = target.data('popup')) {
            button.data({popup: popup, 'toggle-button': target.data('toggle-button')});
            popup_init(button[0]);
            click = false;
            rcmail.register_menu_button(button[0], popup);
        }

        button.attr({id: button_id, href: '#', 'class': btn_class})
            .append($('<span class="inner">').text(target.text()));

        if (click) {
            button.on('click', function(e) { target.click(); });
        }

        if (is_framed && !menu_button) {
            button.data('target', target);
            frame_buttons.push($.extend({button_id: button_id}, find_button(target[0].id)));
        }
        else {
            // Register the button to get active state updates
            register_cloned_button(target_id, button_id, btn_class.replace(' disabled', ''));
        }

        return button;
    };

    /**
     * Finds an rcmail button
     */
    function find_button(id)
    {
        var i, button, command;

        for (command in rcmail.buttons) {
            for (i = 0; i < rcmail.buttons[command].length; i++) {
                button = rcmail.buttons[command][i];
                if (button.id == id) {
                    return {
                        command: command,
                        index: i,
                        data: button
                    };
                }
            }
        }
    };

    /**
     * Setup environment
     */
    function layout_init()
    {
        // Select current layout element
        env.last_selected = $('#layout > div.selected')[0];
        if (!env.last_selected && layout.content.length) {
            $.each(['sidebar', 'list', 'content'], function() {
                if (layout[this].length) {
                    env.last_selected = layout[this][0];
                    layout[this].addClass('selected');
                    return false;
                }
            });
        }

        // Register resize handler
        $(window).on('resize', function() {
            clearTimeout(env.resize_timeout);
            env.resize_timeout = setTimeout(function() { resize(); }, 25);
        });

        // Enable rcmail.open_window intercepting
        env.open_window = rcmail.open_window;
        rcmail.open_window = window_open;

        rcmail
            .addEventListener('message', message_displayed)
            .addEventListener('menu-open', menu_toggle)
            .addEventListener('menu-close', menu_toggle)
            .addEventListener('editor-init', tinymce_init)
            .addEventListener('autocomplete_create', rcmail_popup_init)
            .addEventListener('googiespell_create', rcmail_popup_init)
            .addEventListener('setquota', update_quota)
            .addEventListener('enable-command', enable_command_handler)
            .addEventListener('init', init);

        // Add styling for TinyMCE editor popups
        // We need to use MutationObserver, as TinyMCE does not provide any events for this
        if (window.MutationObserver && window.tinymce) {
            var callback = function(list) {
                $.each(list, function() {
                    $.each(this.addedNodes, function() {
                        tinymce_style(this);
                    });
                });
            };

            (new MutationObserver(callback)).observe(document.body, {childList: true});
        }

        // Initialize column resizers
        if (layout.sidebar.length) {
            splitter_init(layout.sidebar);
        }

        if (layout.list.length) {
            splitter_init(layout.list);
        }
    };

    /**
     * rcmail 'init' event handler
     */
    function init()
    {
        // Additional functionality on list widgets
        $('[data-list]').filter('ul,table').each(function() {
            var button,
                table = $(this),
                list = table.data('list');

            if (rcmail[list] && rcmail[list].multiselect) {
                var repl, button,
                    parent = table.parents('layout-sidebar,#layout-list,#layout-content').last(),
                    header = parent.find('.header'),
                    toolbar = header.find('ul');

                if (!toolbar.length) {
                    toolbar = header;
                }
                else if (button = toolbar.find('a.select').data('toggle-button')) {
                    button = $('#' + button);
                }

                // Enable checkbox selection on list widgets
                rcmail[list].enable_checkbox_selection();

                // Add Select button to the list navigation bar
                if (!button) {
                    button = $('<a>').attr({'class': 'button selection disabled', role: 'button', title: rcmail.gettext('select')})
                        .on('click', function() { if ($(this).is('.active')) table.toggleClass('withselection'); })
                        .append($('<span class="inner">').text(rcmail.gettext('select')));

                    if (toolbar.is('.menu')) {
                        button.prependTo(toolbar).wrap('<li role="menuitem">');

                        // Add a button to the content toolbar menu too
                        if (layout.content) {
                            var button2 = create_cloned_button(button, true, 'hidden-big hidden-large');
                            $('<li role="menuitem">').append(button2).appendTo('#toolbar-menu');
                            button = button.add(button2);
                        }
                    }
                    else {
                        if (repl = table.data('list-select-replace')) {
                            $(repl).replaceWith(button);
                        }
                        else {
                            button.appendTo(toolbar).addClass('icon');
                            if (!parent.is('#layout-sidebar')) {
                                button.addClass('toolbar-button');
                            }
                        }
                    }
                }

                // Update Select button state on list update
                rcmail.addEventListener('listupdate', function(prop) {
                    if (prop.list && prop.list == rcmail[list]) {
                        if (prop.rowcount) {
                            button.addClass('active').removeClass('disabled').attr('tabindex', 0);
                        }
                        else {
                            button.removeClass('active').addClass('disabled').attr('tabindex', -1);
                        }
                    }
                });
            }

            // https://github.com/roundcube/elastic/issues/45
            // Draggable blocks scrolling on touch devices, we'll disable it there
            if (touch && rcmail[list]) {
                if (typeof rcmail[list].draggable == 'function') {
                    rcmail[list].draggable('destroy');
                }
                else if (typeof rcmail[list].draggable == 'boolean') {
                    rcmail[list].draggable = false;
                }
            }
        });

        // Display "List is empty..." on the list
        if (window.MutationObserver) {
            $('[data-label-msg]').filter('ul,table').each(function() {
                var fn, observer, callback,
                    info = $('<div class="listing-info hidden">').insertAfter(this),
                    table = $(this),

                fn = function() {
                    var ext, command,
                        msg = table.data('label-msg'),
                        list = table.is('ul') ? table : table.children('tbody');

                    if (!rcmail.env.search_request && !rcmail.env.qsearch
                        && msg && !list.children(':visible').length
                    ) {
                        ext = table.data('label-ext');
                        command = table.data('create-command');

                        if (ext && (!command || rcmail.commands[command])) {
                            msg += ' ' + ext;
                        }

                        info.text(msg).removeClass('hidden');
                        return;
                    }

                    info.addClass('hidden');
                };

                callback = function() {
                    // wait until the UI stops loading and the list is visible
                    if (rcmail.busy || !table.is(':visible')) {
                        return setTimeout(callback, 250);
                    }

                    clearTimeout(env.list_timer);
                    env.list_timer = setTimeout(fn, 50);
                };

                // show/hide the message when something changes on the list
                observer = new MutationObserver(callback);
                observer.observe(table[0], {childList: true, subtree: true, attributes: true, attributeFilter: ['style']});

                // initialize the message
                callback();
            });
        }

        // Create floating action button(s)
        if ((layout.list.length || layout.content.length) && is_mobile()) {
            var fabuttons = [];

            $('[data-fab]').each(function() {
                var button = $(this),
                    task = button.data('fab-task') || '*',
                    action = button.data('fab-action') || '*';

                if ((task == '*' || task == rcmail.task)
                    && (action == '*' || action == rcmail.env.action || (action == 'none' && !rcmail.env.action))
                ) {
                    fabuttons.push(create_cloned_button(button, false, false, true));
                }
            });

            if (fabuttons.length) {
                $('<div class="floating-action-buttons">').append(fabuttons)
                    .appendTo(layout.list.length ? layout.list : layout.content);
            }
        }

        // Add menu link for each attachment
        if (rcmail.env.action != 'print') {
            $('#attachment-list > li').each(function() {
                attachmentmenu_append(this);
            });
        }

        var phone_confirmation = function(label) {
            if (mode == 'phone') {
                rcmail.display_message(rcmail.gettext(label), 'confirmation');
            }
        };

        rcmail.addEventListener('fileappended', function(e) {
                if (e.attachment.complete) {
                    attachmentmenu_append(e.item);
                    if (e.attachment.mimetype == 'text/vcard' && rcmail.commands['attach-vcard']) {
                        phone_confirmation('vcard_attachments.vcardattached');
                    }
                }
            })
            .addEventListener('managesieve.insertrow', function(o) { bootstrap_style(o.obj); })
            .addEventListener('add-recipient', function() { phone_confirmation('recipientsadded'); });

        rcmail.init_pagejumper('.pagenav > input');

        if (rcmail.task == 'mail') {
            if (rcmail.env.action == 'compose') {
                rcmail.addEventListener('compose-encrypted', function(e) {
                    $("a.mode-html, button.attach").prop('disabled', e.active);
                    $('a.attach, a.responses')[e.active ? 'addClass' : 'removeClass']('disabled');
                });

                $('#layout-sidebar > .footer:not(.pagenav) > a.button').click(function() {
                    if ($(this).is('.disabled')) {
                        rcmail.display_message(rcmail.gettext('nocontactselected'), 'warning');
                    }
                });

                // Update compose status bar on attachments list update
                if (window.MutationObserver) {
                    var observer, list = $('#attachment-list'),
                        status_callback = function() { compose_status('attach', list.children().length > 0); };

                    observer = new MutationObserver(status_callback);
                    observer.observe(list[0], {childList: true});
                    status_callback();
                }
            }

            // In compose/preview window we do not provide "Back" button, instead
            // we modify the "Mail" button in the task menu to act like it (i.e. calls 'list' command)
            if (!rcmail.env.extwin && (rcmail.env.action == 'compose' || rcmail.env.action == 'show')) {
                $('a.mail', layout.menu).attr('onclick', "return rcmail.command('list','',this,event)");
            }

            // Append contact menu to all mailto: links
            if (rcmail.env.action == 'preview' || rcmail.env.action == 'show') {
                $('a').filter('[href^="mailto:"]').each(function() {
                    mailtomenu_append(this);
                });
            }
        }
        else if (rcmail.task == 'settings') {
            rcmail.addEventListener('identity-encryption-show', function(p) {
                bootstrap_style(p.container);
            });
            rcmail.addEventListener('identity-encryption-update', function(p) {
                bootstrap_style(p.container);
            });
        }

        rcmail.set_env({
            thread_padding: '1.5rem',
            // increase popup windows, so they do not switch to tablet mode
            popup_width_small: 1025,
            popup_width: 1200
        });

        // Update layout after initialization (again)
        // In devel mode we have to wait until all styles are applied by less
        if (rcmail.env.devel_mode && window.less) {
            less.pageLoadFinished.then(function() {
                resize();
                // Re-focus the focused input field on mail compose
                if (rcmail.env.compose_focus_elem) {
                    $(rcmail.env.compose_focus_elem).focus();
                }
            });
        }
        else {
            resize();
        }

        // Add date format placeholder to datepicker inputs
        var func, format = rcmail.env.date_format_localized;
        if (format) {
            func = function(input) {
                $(input).filter('.datepicker').attr('placeholder', format);
                // also make selects pretty
                $(input).parent().find('select').each(function() { pretty_select(this); });
            };

            $('input.datepicker').each(function() { func(this); });
            rcmail.addEventListener('insert-edit-field', func);
        }
    };

    /**
     * Apply bootstrap classes to html elements
     */
    function bootstrap_style(context)
    {
        if (!context) {
            context = document;
        }

        // Buttons
        $('input.button,button', context).not('.btn').addClass('btn').not('.btn-primary,.primary,.mainaction').addClass('btn-secondary');
        $('input.button.mainaction,button.primary,button.mainaction', context).addClass('btn-primary');
        $('button.btn.delete,button.btn.discard', context).addClass('btn-danger');

        $.each(['warning', 'error', 'information', 'confirmation'], function() {
            var type = this;
            $('.box' + type + ':not(.ui.alert)', context).each(function() {
                alert_style(this, type, true);
            });
        });

        // Convert structure of single dialogs (one input or just an image),
        // e.g. group create, attachment rename where we use <label>Label<input></label>
        if (context != document && $('.popup', context).children().length == 1) {
            var content = $('.popup', context).children().first();
            if (content.is('img')) {
                $('.popup', context).addClass('justified');
            }
            else if (content.is('label')) {
                var input = content.find('input').detach(),
                    label = content.detach(),
                    id = input.attr('id');

                if (!id) {
                    input.attr('id', id = 'dialog-input-elastic');
                }

                $('.popup', context).addClass('formcontent').append(
                    $('<div class="form-group row">')
                        .append(label.attr('for', id).addClass('col-sm-2 col-form-label'))
                        .append($('<div class="col-sm-10">').append(input))
                );

                input.focus();
            }
        }

        // Forms
        var supported_controls = 'input:not(.button,.no-bs,[type=button],[type=radio],[type=checkbox]),textarea';
        $(supported_controls, $('.propform', context)).addClass('form-control');
        $('[type=checkbox]', $('.propform', context)).addClass('form-check-input');

        // Note: On selects we add form-control to get consistent focus
        //       and to not have to create separate rules for selects and inputs
        $('select', context).addClass('form-control custom-select');

        if (context != document) {
            $(supported_controls, context).addClass('form-control');
        }

        $('table.propform', context).each(function() {
            var text_rows = 0, form_rows = 0;
            var col_sizes = ['sm', 4, 8];

            if ($(this).attr('class').match(/cols-([a-z]+)-(\d)-(\d)/)) {
                col_sizes = [RegExp.$1, RegExp.$2, RegExp.$3];
            }

            $(this).find('> tbody > tr, > tr').each(function() {
                var first, last, row = $(this),
                    row_classes = ['form-group', 'row'],
                    cells = row.children('td');

                if (cells.length == 2) {
                    first = cells.first();
                    last = cells.last();

                    $('label', first).addClass('col-form-label');
                    first.addClass('col-' + col_sizes[0] + '-' + col_sizes[1]);
                    last.addClass('col-' + col_sizes[0] + '-' + col_sizes[2]);

                    if (last.find('[type=checkbox]').length == 1 && !last.find('.proplist').length) {
                        row_classes.push('form-check');

                        if (last.find('a').length) {
                            row_classes.push('with-link');
                        }

                        form_rows++;
                    }
                    else if (!last.find('input:not([type=hidden]),textarea,radio,select').length) {
                        last.addClass('form-control-plaintext');
                        text_rows++;
                    }
                    else {
                        form_rows++;
                    }

                    // style some multi-input fields
                    if (last.children('.datepicker') && last.children('input').length == 2) {
                        last.addClass('datetime');
                    }
                }
                else if (cells.length == 1) {
                    cells.css('width', '100%');
                }

                row.addClass(row_classes.join(' '));
            });

            if (text_rows > form_rows) {
                $(this).addClass('text-only');
            }
        });

        // Special input + anything entry
        $('td.input-group', context).each(function() {
            $(this).children().slice(1).addClass('input-group-append');
        });

        // Other forms, e.g. Contact advanced search
        $('fieldset.propform:not(.groupped) div.row', context).each(function() {
            var has_input = $('input:not([type=hidden]),select,textarea', this).length > 0;

            if (has_input) {
                $(supported_controls, this).addClass('form-control');
            }

            $(this).children().last().addClass('col-sm-8' + (!has_input ? ' form-control-plaintext' : ''));
            $(this).children().first().addClass('col-sm-4 col-form-label');
            $(this).addClass('form-group');
        });

        // Contact info/edit form
        $('fieldset.propform.groupped fieldset', context).each(function() {
            $('.row', this).each(function() {
                var label, first,
                    has_input = $('input,select,textarea', this).length > 0,
                    items = $(this).children();

                if (has_input) {
                    $(supported_controls, this).addClass('form-control');
                }

                if (items.length < 2) {
                    return;
                }

                first = items.first();
                if (first.is('select')) {
                    first.addClass('input-group-prepend');
                }
                else {
                    first.wrap('<span class="input-group-prepend">').addClass('input-group-text');
                }

                if (!has_input) {
                    items.last().addClass('form-control-plaintext');
                }

                $('.content', this).addClass('input-group-prepend input-group-append input-group-text');
                $('a.deletebutton', this).addClass('input-group-text icon delete').wrap('<span class="input-group-append">');
                $(this).addClass('input-group');
            });
        });

        // Advanced options form
        $('fieldset.advanced', context).each(function() {
            var table = $(this).children('.propform').first();
            table.wrap($('<div>').addClass('collapse'));
            $(this).children('legend').first().addClass('closed').on('click', function() {
                table.parent().collapse('toggle');
                $(this).toggleClass('closed');
            });
        });

        // Other forms, e.g. Insert response
        $('.propform > .prop.block:not(.row)', context).each(function() {
            $(this).addClass('form-group row').each(function() {
                $('label', this).addClass('col-form-label').wrap($('<div class="col-sm-4">'));
                $('input,select,textarea', this).wrap($('<div class="col-sm-8">'));
                $(supported_controls, this).addClass('form-control');
            });
        });

        $('td.rowbuttons > a', context).addClass('btn');

        // Testing Bootstrap Tabs on contact info/edit page
        // Tabs do not scale nicely on very small screen, so can be used
        // only with small number of tabs with short text labels
        $('form.tabbed,div.tabbed', context).each(function(idx, item) {
            var tabs = [], nav = $('<ul>').attr({'class': 'nav nav-tabs', role: 'tablist'});

            $(this).addClass('tab-content').children('fieldset').each(function(i, fieldset) {
                var tab, id = fieldset.id || ('tab' + idx + '-' + i),
                    tab_class = $(fieldset).data('navlink-class');

                $(fieldset).addClass('tab-pane').attr({id: id, role: 'tabpanel'});

                tab = $('<li>').addClass('nav-item').append(
                    $('<a>').addClass('nav-link' + (tab_class ? ' ' + tab_class : ''))
                        .attr({role: 'tab', 'href': '#' + id})
                        .text($('legend', fieldset).first().text())
                        .click(function(e) {
                            $(this).tab('show');
                            // Because we return false we have to close popups
                            popups_close(e);
                            // Returning false here prevents from strange scrolling issue
                            // when the form is in an iframe, e.g. contact edit form
                            return false;
                        })
                );

                $('legend', fieldset).first().hide();
                tabs.push(tab);
            });

            // create the navigation bar
            nav.append(tabs).insertBefore(item);
            // activate the first tab
            $('a.nav-link', nav).first().click();
        });

        $('input[type=file]:not(.custom-file-input)', context).each(function() {
            var label_text = rcmail.gettext('choosefile' + (this.multiple ? 's' : '')),
                label = $('<label>').attr({'class': 'custom-file-label',
                    'data-browse': rcmail.gettext('browse')}).text(label_text);

            $(this).addClass('custom-file-input').wrap('<div class="custom-file">');
            $(this).on('change', function() {
                    var text = label_text;
                    if (this.files.length) {
                        text = this.files[0].name;
                        if (this.files.length > 1) {
                            text += ', ...';
                        }
                    }

                    // Note: We don't use label variable to allow cloning of the input
                    $(this).next().text(text);
                })
                .parent().append(label);
        });

        // Make tables pretier
        $('table:not(.table,.compact-table,.propform,.listing,.ui-datepicker-calendar)', context)
            .filter(function() {
                // exclude direct propform children and external content
                return !$(this).parent().is('.propform')
                    && !$(this).parents('.message-htmlpart,.message-partheaders,.boxinformation,.raw-tables').length;
            })
            .each(function() {
                // TODO: Consider implementing automatic setting of table-responsive on window resize
                var table = $(this).addClass('table');
                table.parent().addClass('table-responsive-sm');
                table.find('thead').addClass('thead-default');
            });

        // The same for some other checkboxes
        // We do this here, not in setup() because we want to cover dialogs
        $('input.pretty-checkbox, .propform input[type=checkbox], .form-check input, .popupmenu.form input[type=checkbox], .menu input[type=checkbox]', context)
            .each(function() { pretty_checkbox(this); });

        // Also when we add action-row of the form, e.g. Managesieve plugin adds them after the page is ready
        if ($(context).is('.actionrow')) {
            $('input[type=checkbox]', context).each(function() { pretty_checkbox(this); });
        }

        // Input-group combo is an element with a select field on the left
        // and input(s) on right, and where the whole right side can be hidden
        // depending on the select position. This code fixes border radius on select
        $('.input-group-combo > select', context).first().on('change', function() {
            var select = $(this),
                fn = function() {
                    select[select.next().is(':visible') ? 'removeClass' : 'addClass']('alone');
                };

            setTimeout(fn, 50);
            setTimeout(fn, 2000); // for devel mode
        }).trigger('change');

        // Make message-objects alerts pretty (the same as UI alerts)
        $('#message-objects', context).children(':not(.ui.alert)').add('.part-notice').each(function() {
            // message objects with notice class are really warnings
            var cl = $(this).removeClass('notice part-notice').attr('class').split(/\s/)[0] || 'warning';
            alert_style(this, cl);
            $(this).addClass('box' + cl);
            $('a', this).addClass('btn btn-primary btn-sm');
        });

        // Form validation errors (managesieve plugin)
        $('.error', context).addClass('is-invalid');

        // Make logon form prettier
        if (rcmail.env.task == 'login' && context == document) {
            $('#rcmloginsubmit').addClass('btn-lg text-uppercase w-100');
            $('#login-form table tr').each(function() {
                var input = $('input,select', this),
                    label = $('label', this),
                    icon_name = input.data('icon'),
                    icon = $('<i>').attr('class', 'input-group-text icon ' + input.attr('name').replace('_', ''));

                if (icon_name) {
                    icon.addClass(icon_name);
                }

                $(this).addClass('form-group row');
                label.parent().css('display', 'none');
                input.addClass(input.is('select') ? 'custom-select' : 'form-control')
                    .attr('placeholder', label.text())
                    .before($('<span class="input-group-prepend">').append(icon))
                    .parent().addClass('input-group input-group-lg');
            });
        }

        $('select:not([multiple])', context).each(function() { pretty_select(this); });
    };

    /**
     * Detects if the element is TinyMCE dialog/menu
     * and adds Elastic styling to it
     */
    function tinymce_style(elem)
    {
        // TinyMCE dialog widnows
        if ($(elem).is('.mce-window')) {
            var body = $(elem).find('.mce-window-body'),
                foot = $(elem).find('.mce-foot > .mce-container-body');

            // Apply basic forms style
            if (body.length) {
                bootstrap_style(body[0]);
            }

            body.find('button').filter(function() { return $(this).parent('.mce-btn').length > 0; }).removeClass('btn btn-secondary');

            // Fix icons in Find and Replace dialog footer
            if (foot.children('.mce-widget').length === 5) {
                foot.addClass('mce-search-foot');
            }

            // Apply some form structure fixes and helper classes
            $(elem).find('.mce-charmap').parent().parent().addClass('mce-charmap-dialog');
            $(elem).find('.mce-combobox').each(function() {
                if (!$(this).children('.mce-btn').length) {
                    $(this).addClass('mce-combobox-fake');
                }
            });
            $(elem).find('.mce-form > .mce-container-body').each(function() {
                if ($(this).children('.mce-formitem').length > 4) {
                    $(this).addClass('mce-form-split');
                }
            });
            $(elem).find('.mce-form').next(':not(.mce-formitem)').addClass('mce-form');

            // Fix dialog height (e.g. Table properties dialog)
            if (!is_mobile()) {
                var offset, max_height = 0, height = body.height();
                $(elem).find('.mce-form').each(function() {
                    max_height = Math.max(max_height, $(this).height());
                });

                if (height < max_height) {
                    max_height += (body.find('.mce-tabs').height() || 0) + 25;
                    body.height(max_height);
                    $(elem).height($(elem).height() + (max_height - height));
                    $(elem).css('top', ($(window).height() - $(elem).height())/2 + 'px');
                }
            }
        }
        // TinyMCE menus on mobile
        else if ($(elem).is('.mce-menu')) {
            $(elem).prepend(
                $('<h3 class="popover-header">').append(
                    $('<a class="button icon "' + 'cancel' + '">')
                        .text(rcmail.gettext('close'))
                        .on('click', function() { $(document.body).click(); })));

            if (window.MutationObserver) {
                var callback = function() {
                        if (mode != 'phone') {
                            return;
                        }
                        if (!$('.mce-menu:visible').length) {
                            $('div.mce-overlay').click();
                        }
                        else if (!$('div.mce-overlay').length) {
                            $('<div>').attr('class', 'popover-overlay mce-overlay')
                                .appendTo('body')
                                .click(function() { $(this).remove(); });
                        }
                    };

                (new MutationObserver(callback)).observe(elem, {attributes: true});
            }
        }
    };

    /**
     * Initializes popup menus
     */
    function dropdowns_init()
    {
        $('[data-popup]').each(function() { popup_init(this); });

        $(document).on('click', popups_close);
        rcube_webmail.set_iframe_events({mousedown: popups_close, touchstart: popups_close});
    };

    /**
     * Init content frame
     */
    function content_frame_init()
    {
        var last_selected = env.last_selected,
            title_reset = function(title) {
                if (typeof title !== 'string' || !title.length) {
                    title = $('h1.voice').text() || $('title').text() || '';
                }

                layout.content.find('.header > .header-title').text(title);
            };

        // display or reset the content frame
        var common_content_handler = function(e, href, show, title)
        {
            if (is_mobile() && env.frame_nav) {
                content_frame_navigation(href, e);
            }

            if (show && !layout.content.is(':visible')) {
                env.last_selected = layout.content[0];
            }
            else if (!show && env.last_selected != last_selected && !env.content_lock) {
                env.last_selected = last_selected;
            }

            screen_resize();

            title_reset(title && show ? title : null);

            env.content_lock = false;
        };

        var common_list_handler = function(e) {
            if (mode != 'large' && !env.content_lock && e.force) {
                show_list();
            }

            env.content_lock = false;

            // display current folder name in list header
            if (e.title) {
                $('.header > .header-title', layout.list).text(e.title);
            }
        };

        var list_handler = function(e) {
            var args = {};

            if (rcmail.env.task == 'addressbook' || rcmail.env.task == 'mail') {
                args.force = true;
            }

            // display current folder name in list header
            if (rcmail.env.task == 'mail' && !rcmail.env.action) {
                var name = $.type(e) == 'string' ? e : rcmail.env.mailbox,
                    folder = rcmail.env.mailboxes[name];

                args.title = folder ? folder.name : '';
            }

            common_list_handler(args);
        };

        // when loading content-frame in small-screen mode display it
        layout.content.find('iframe').on('load', function(e) {
            var href = '', show = true;

            // Reset the scroll position of the iframe-wrapper
            $(this).parent('.iframe-wrapper').scrollTop(0);

            try {
                href = e.target.contentWindow.location.href;
                show = !href.endsWith(rcmail.env.blankpage);
                // Reset title back to the default
                $(e.target.contentWindow).on('unload', title_reset);
            }
            catch(e) { /* ignore */ }

            common_content_handler(e, href, show);
        });

        rcmail
            .addEventListener('afterlist', list_handler)
            .addEventListener('afterlistgroup', list_handler)
            .addEventListener('afterlistsearch', list_handler)
            // plugins
            .addEventListener('show-list', function(e) {
                e.force = true;
                common_list_handler(e);
            })
            .addEventListener('show-content', function(e) {
                if (e.obj && !$(e.obj).is('iframe')) {
                    $(e.scrollElement || e.obj).scrollTop(0);
                    if (is_mobile()) {
                        iframe_loader(e.obj);
                    }
                }

                common_content_handler(e.event || new Event, '_action=' + (e.mode || 'edit'), true, e.title);
            });
    };

    /**
     * Content frame navigation
     */
    function content_frame_navigation(href, event)
    {
        // Don't display navigation for create/add action frames
        if (href.match(/_action=(create|add)/) || href.match(/_nav=hide/)) {
            $(env.frame_nav).addClass('hide-nav-buttons');
            return;
        }

        var node, uid, list, _list = $('[data-list]', layout.list).data('list');

        if (!_list || !(list = rcmail[_list])) {
            // hide navbar if there are no visible buttons, e.g. Help plugin UI
            if ($(env.frame_nav).is('.hide-nav-buttons') && !$('.buttons', env.frame_nav).children().length) {
                $(env.frame_nav).addClass('hidden');
            }
            return;
        }

        $(env.frame_nav).removeClass('hide-nav-buttons hidden');

        // expand collapsed row so we do not skip the whole thread
        // TODO: Unified interface for list and treelist widgets
        if (uid = list.get_single_selection()) {
            if (list.rows && list.rows[uid] && !list.rows[uid].expanded) {
                list.expand_row(event, uid);
            }
            else if (list.get_node && (node = list.get_node(uid)) && node.collapsed) {
                list.expand(uid);
            }
        }

        var prev, next,
            frame = $('#' + rcmail.env.contentframe),
            next_button = $('a.button.next', env.frame_nav).off('click').addClass('disabled'),
            prev_button = $('a.button.prev', env.frame_nav).off('click').addClass('disabled');

        if ((next = list.get_next()) || rcmail.env.current_page < rcmail.env.pagecount) {
            next_button.removeClass('disabled').on('click', function() {
                env.content_lock = true;
                iframe_loader(frame);

                if (next) {
                    list.select(next);
                }
                else {
                    rcmail.env.list_uid = 'FIRST';
                    rcmail.command('nextpage');
                }
            });
        }

        if (((prev = list.get_prev()) && (prev != '*' || _list != 'subscription_list')) || rcmail.env.current_page > 1) {
            prev_button.removeClass('disabled').on('click', function() {
                env.content_lock = true;
                iframe_loader(frame);

                if (prev) {
                    list.select(prev);
                }
                else {
                    rcmail.env.list_uid = 'LAST';
                    rcmail.command('previouspage');
                }
            });
        }
    };

    /**
     * Handler for editor-init event
     */
    function tinymce_init(o)
    {
        // Enable autoresize plugin
        o.config.plugins += ' autoresize';

        if (is_touch()) {
            // Make the toolbar icons bigger
            o.config.toolbar_items_size = null;

            // Use minimalistic toolbar
            o.config.toolbar = 'undo redo | insert | styleselect';

            if (o.config.plugins.match(/emoticons/)) {
                o.config.toolbar += ' emoticons';
            }
        }

        if (rcmail.task == 'mail' && rcmail.env.action == 'compose') {
            var form = $('#compose-content > form'),
                keypress = function(e) {
                    if (e.key == 'Tab' && e.shiftKey) {
                        $('#compose-content > form').scrollTop(0);
                    }
                };

            // Shift+Tab on mail compose editor scrolls the page to the top
            o.config.setup_callback = function(ed) {
                ed.on('keypress', keypress);
            };

            $('#composebody').on('keypress', keypress);

            // Keep the editor toolbar on top of the screen on scroll
            form.on('scroll', function() {
                var container = $('.mce-container-body', form),
                    toolbar = $('.mce-top-part', container),
                    editor_offset = container.offset(),
                    header_top = form.offset().top;

                if (editor_offset && (editor_offset.top - header_top < 0)) {
                    toolbar.css({position: 'fixed', top: header_top + 'px', width: container.width() + 'px'});
                }
                else {
                    toolbar.css({position: 'relative', top: 0, width: 'auto'})
                }
            });

            $(window).resize(function() { form.trigger('scroll'); });
        }
    };

    function datepicker_init(datepicker)
    {
        // Datepicker widget improvements: overlay element, styling updates on calendar element update
        // The widget does not provide any event system, so we use MutationObserver
        if (window.MutationObserver) {
            $(datepicker).not('[data-observed]').each(function() {
                var overlay, hidden = true,
                    win = is_framed ? parent : window,
                    callback = function(data) {
                        $.each(data, function(i, v) {
                            // add/remove overlay on widget show/hide
                            if (v.type == 'attributes') {
                                var is_hidden = $(v.target).attr('aria-hidden') == 'true';
                                if (is_hidden != hidden) {
                                    if (!is_hidden) {
                                        overlay = $('<div>').attr('class', 'ui-widget-overlay datepicker')
                                            .appendTo(win.document.body)
                                            .click(function(e) {
                                                $(this).remove();
                                                if (is_framed) {
                                                    $.datepicker._hideDatepicker();
                                                }
                                            });
                                    }
                                    else if (overlay) {
                                        overlay.remove();
                                    }
                                    hidden = is_hidden;
                                }
                            }
                            else if (v.addedNodes.length) {
                                // apply styles when widget content changed
                                win.UI.bootstrap_style(v.target);

                                // Month/Year change handlers do not work from parent, fix it
                                if (is_framed) {
                                    win.$('select.ui-datepicker-month', v.target).on('change', function() {
                                        $.datepicker._selectMonthYear($.datepicker._lastInput, this, "M");
                                    });
                                    win.$('select.ui-datepicker-year', v.target).on('change', function() {
                                        $.datepicker._selectMonthYear($.datepicker._lastInput, this, "Y");
                                    });
                                }
                            }
                        });
                    };

                $(this).attr('data-observed', '1');

                if (is_framed) {
                    // move the datepicker to parent window
                    $(this).detach().appendTo(parent.document.body);

                    // create fake element, so the valid one is not removed by datepicker code
                    $('<div id="ui-datepicker-div" class="hidden">').appendTo(document.body);
                }

                (new MutationObserver(callback)).observe(this, {childList: true, subtree: false, attributes: true, attributeFilter: ['aria-hidden']});
            });
        }
    };

    /**
     * Handler for some Roundcube core popups
     */
    function rcmail_popup_init(o)
    {
        // Add some common styling to the autocomplete/googiespell popups
        $('ul', o.obj).addClass('menu listing iconized');
        $(o.obj).addClass('popupmenu popover');
        bootstrap_style(o.obj);

        // for googiespell list
        $('input', o.obj).addClass('form-control');

        // Modify the googiespell menu on mobile
        if (is_mobile() && $(o.obj).is('.googie_window')) {
            // Set popup Close title
            var title = rcmail.gettext('close'),
                class_name = 'button icon cancel',
                close_link = $('<a>').attr('class', class_name).text(title)
                    .click(function(e) {
                        e.stopPropagation();
                        $('.popover-overlay').remove();
                        $(o.obj).hide();
                    });

            $('<h3 class="popover-header">').append(close_link).prependTo(o.obj);

            // add overlay element for phone layout
            if (!$('.popover-overlay').length) {
                $('<div>').attr('class', 'popover-overlay')
                    .appendTo('body')
                    .click(function() { $(this).remove(); });
            }

            $('ul,button', o.obj).click(function(e) {
                if (!$(e.target).is('input')) {
                    $('.popover-overlay').remove();
                }
            });
        }
    };

    /**
     * Handler for 'enable-command' event
     */
    function enable_command_handler(args)
    {
        if (is_framed) {
            $.each(frame_buttons, function(i, button) {
                if (args.command == button.command) {
                    parent.$('#' + button.button_id)[args.status ? 'removeClass' : 'addClass']('disabled');
                }
            });
        }

        if (rcmail.task == 'mail') {
            switch (args.command) {
            case 'reply-list':
                if (rcmail.env.reply_all_mode == 1) {
                    var label = rcmail.gettext(args.status ? 'replylist' : 'replyall');
                    $('.toolbar a.reply-all').attr('title', label).find('.inner').text(label);
                }
                break;

            case 'compose-encrypted':
                // show the toolbar button for Mailvelope
                if (args.status) {
                    $('.toolbar a.encrypt').parent().show();
                }
                break;

            case 'compose-encrypted-signed':
                // enable selector for encrypt and sign
                $('#encryption-menu-button').show();
                break;
            }
        }
    };

    /**
     *  screen mode
     */
    function screen_mode()
    {
        var size, width = $(window).width();

        if (width <= 480)
            size = 'phone';
        else if (width > 1200)
            size = 'large';
        else if (width > 768)
            size = 'normal';
        else
            size = 'small';

        touch = width <= 1024;
        mode = size;
    };

    /**
     * Window resize handler
     * Does layout reflows e.g. on screen orientation change
     */
    function resize()
    {
        var mobile;

        screen_mode();
        screen_resize();
        screen_resize_html();

        // disable ext-windows and other features
        if (mobile = is_mobile()) {
            rcmail.set_env(env.small_screen_config);
            rcmail.enable_command('extwin', false);
        }
        else {
            rcmail.set_env(env.config);
            rcmail.enable_command('extwin', true);
        }

        // Hide content frame buttons on small devices (with frame toolbar in parent window)
        $.each(content_buttons, function() { $(this)[mobile ? 'hide' : 'show'](); });
    };

    function screen_resize()
    {
        if (is_framed && !layout.sidebar.length && !layout.list.length) {
            screen_resize_headers();
            return;
        }

        switch (mode) {
            case 'phone': screen_resize_phone(); break;
            case 'small': screen_resize_small(); break;
            case 'normal': screen_resize_normal(); break;
            case 'large': screen_resize_large(); break;
        }

        screen_resize_logo(mode);
        screen_resize_headers();

        // On iOS and Android the content frame height is never correct, fix it.
        // Actually I observed the issue on my old iPad with iOS 9.3.
        if (bw.webkit && bw.ipad && bw.agent.match(/OS 9/)) {
            $('.iframe-wrapper').each(function() {
                var h = $(this).height();
                if (h) {
                    $(this).children('iframe').height(h);
                }
            });
        }
    };

    /**
     * Assigns layout-* and touch-mode class to the 'html' element
     *
     * If we're inside an iframe that is small we have to
     * check if the parent window is also small (mobile).
     * We use that e.g. to still display desktop-like popovers in dialogs
     */
    function screen_resize_html()
    {
        var meta = layout_metadata(),
            html = $(document.documentElement);

        if (html[0].className.match(/layout-([a-z]+)/)) {
            if (RegExp.$1 != meta.mode) {
                html.removeClass('layout-' + RegExp.$1)
                    .addClass('layout-' + meta.mode);
            }
        }
        else {
            html.addClass('layout-' + meta.mode);
        }

        if (meta.touch && !html.is('.touch')) {
            html.addClass('touch');
        }
        else if (!meta.touch && html.is('.touch')) {
            html.removeClass('touch');
        }
    };

    function screen_resize_logo(mode)
    {
        if (mode == 'phone' && $('#logo').data('src-small')) {
            $('#logo').attr('src', $('#logo').data('src-small'));
        }
        else {
            $('#logo').attr('src', $('#logo').data('src-default'));
        }
    }

    /**
     * Sets left and right margin to the header title element to make it
     * properly centered depending on the number of buttons on both sides
     */
    function screen_resize_headers()
    {
        $('#layout > div > .header').each(function() {
            var title, right = 0, left = 0, padding = 0,
                sizes = {left: 0, right: 0};

            $(this).children(':visible').each(function() {
                if (!title && $(this).is('.header-title')) {
                    title = $(this);
                    return;
                }

                sizes[title ? 'right' : 'left'] += this.offsetWidth;
            });

            if (padding + sizes.right >= sizes.left) {
                right = 0;
                left = sizes.right + padding - sizes.left;
            }
            else {
                left = 0;
                right = sizes.left - (padding + sizes.right);
            }

            $(title).css({
                'margin-right': right + 'px',
                'margin-left': left + 'px',
                'padding-right': padding + 'px'
            });
        });
    };

    function screen_resize_phone()
    {
        screen_resize_small_all();
        app_menu(false);
    };

    function screen_resize_small()
    {
        screen_resize_small_all();
        app_menu(true);
    };

    function screen_resize_normal()
    {
        var show;

        if (layout.list.length) {
            show = layout.list.is(env.last_selected) || (!layout.sidebar.is(env.last_selected) && !layout.sidebar.is('.layout-sticky'));
            layout.list[show ? 'removeClass' : 'addClass']('hidden');
        }
        if (layout.sidebar.length) {
            show = !layout.list.length || layout.sidebar.is(env.last_selected) || layout.sidebar.is('.layout-sticky');
            layout.sidebar[show ? 'removeClass' : 'addClass']('hidden');
        }

        layout.content.removeClass('hidden');
        app_menu(true);
        screen_resize_small_none();

        if (layout.list.length) {
            $('.header > ul.menu', layout.list).addClass('popupmenu');
        }
    };

    function screen_resize_large()
    {
        $.each(layout, function(name, item) { item.removeClass('hidden'); });

        screen_resize_small_none();

        if (layout.list) {
            $('.header > ul.menu.popupmenu', layout.list).removeClass('popupmenu');
        }
    };

    function screen_resize_small_all()
    {
        var show, got_content = false;

        if (layout.content.length) {
            show = got_content = layout.content.is(env.last_selected);
            layout.content[show ? 'removeClass' : 'addClass']('hidden');
            $('.header > ul.menu', layout.content).addClass('popupmenu');
        }

        if (layout.list.length) {
            show = !got_content && layout.list.is(env.last_selected);
            layout.list[show ? 'removeClass' : 'addClass']('hidden');
            $('.header > ul.menu', layout.list).addClass('popupmenu');
        }

        if (layout.sidebar.length) {
            show = !got_content && (layout.sidebar.is(env.last_selected) || !layout.list.length);
            layout.sidebar[show ? 'removeClass' : 'addClass']('hidden');
        }

        if (got_content) {
            buttons.back_list.show();
        }
    };

    function screen_resize_small_none()
    {
        buttons.back_list.filter(function() { return $(this).parents('#layout-sidebar').length == 0; }).hide();
        $('ul.menu.popupmenu').removeClass('popupmenu');
    };

    function show_content(unsticky)
    {
        // show sidebar and hide list
        layout.list.addClass('hidden');
        layout.sidebar.addClass('hidden');
        layout.content.removeClass('hidden');

        if (unsticky) {
            layout.sidebar.removeClass('layout-sticky');
        }

        screen_resize_headers();
        env.last_selected = layout.content[0];
    };

    function show_sidebar(sticky)
    {
        // show sidebar and hide list
        layout.list.addClass('hidden');
        layout.sidebar.removeClass('hidden');

        if (sticky) {
            layout.sidebar.addClass('layout-sticky');
        }

        if (mode == 'small' || mode == 'phone') {
            layout.content.addClass('hidden');
        }

        screen_resize_headers();
        env.last_selected = layout.sidebar[0];
    };

    function show_list(scroll)
    {
        if (!layout.list.length && !layout.sidebar.length) {
            history.back();
        }
        else {
            // show list and hide sidebar and content
            layout.sidebar.addClass('hidden').removeClass('layout-sticky');
            layout.list.removeClass('hidden');

            if (mode == 'small' || mode == 'phone') {
                hide_content();
            }

            if (scroll) {
                layout.list.children('.scroller').scrollTop(0);
            }

            env.last_selected = layout.list[0];
        }

        screen_resize_headers();
    };

    function hide_content()
    {
        // show sidebar or list, hide content frame
        env.last_selected = layout.list[0] || layout.sidebar[0];
        screen_resize();

        // reset content frame, so we can load it again
        rcmail.show_contentframe(false);

        // now we have to unselect selected row on the list
        $('[data-list]', layout.list).each(function() {
            var list = $(this).data('list');
            if (rcmail[list]) {
                if (rcmail[list].clear_selection) {
                    rcmail[list].clear_selection(); // list widget
                }
                else if (rcmail[list].select) {
                    rcmail[list].select(); // treelist widget
                }
            }
        });
    };

    // show menu widget
    function app_menu(show)
    {
        if (show) {
            if (mode == 'phone') {
                $('<div id="menu-overlay" class="popover-overlay">')
                    .on('click', function() { app_menu(false); })
                    .appendTo('body');

                if (!env.menu_initialized) {
                    env.menu_initialized = true;
                    $('a', layout.menu).on('click', function(e) { if (mode == 'phone') app_menu(); });
                }

                layout.menu.addClass('popover');
            }

            layout.menu.removeClass('hidden');
        }
        else {
            $('#menu-overlay').remove();
            layout.menu.addClass('hidden').removeClass('popover');
        }
    };

    /**
     * Triggered when a UI message is displayed
     */
    function message_displayed(p)
    {
        if (p.type == 'loading' && $('.iframe-loader:visible').length) {
            // hide original message object, we don't need two "loaders"
            rcmail.hide_message(p.object);
            return;
        }

        alert_style(p.object, p.type, true);
        $(p.object).attr('role', 'alert');
    };

    /**
     * Applies some styling and icon to an alert object
     */
    function alert_style(object, type, wrap)
    {
        var tmp, classes = 'ui alert',
            addicon = !$(object).is('.noicon'),
            map = {
                information: 'alert-info',
                notice: 'alert-info',
                confirmation: 'alert-success',
                warning: 'alert-warning',
                error: 'alert-danger',
                loading: 'alert-info loading',
                uploading: 'alert-info loading',
                vcardattachment: 'alert-info' // vcard_attachments plugin
            };

        // we need the content to be non-text node for best alignment
        if (wrap && addicon && !$(object).is('.aligned-buttons')) {
            $(object).html($('<span>').html($(object).html()));
        }

        // Type can be e.g. 'notice chat'
        type = type.split(' ')[0];

        if (tmp = map[type]) {
            classes += ' ' + tmp;
            if (addicon) {
                $('<i>').attr('class', 'icon').prependTo(object);
            }
        }

        $(object).addClass(classes);
    };

    /**
     * Set UI dialogs size/style depending on screen size
     */
    function dialog_open(dialog)
    {
        var me = $(dialog.uiDialog),
            width = me.width(),
            height = me.height(),
            maxWidth = $(window).width(),
            maxHeight = $(window).height();

        if (maxWidth <= 480) {
            me.css({width: '100%', height: '100%'});
        }
        else {
            if (height > maxHeight) {
                me.css('height', '100%');
            }
            if (width > maxWidth) {
                me.css('width', '100%');
            }
        }

        // Close all popovers
        $(document).click();

        // Display loader when the dialog has an iframe
        iframe_loader($('div.popup > iframe', me));

        // TODO: style buttons/forms
        bootstrap_style(dialog.uiDialog);
    };

    /**
     * Initializes searchbar widget
     */
    function searchbar_init(bar)
    {
        var unread_button = $(),
            options_button = $('a.button.options', bar),
            input = $('input:not([type=hidden])', bar),
            placeholder = input.attr('placeholder'),
            form = $('form', bar),
            is_search_pending = function() {
                if (input.val()) {
                    return true;
                }

                if (rcmail.task == 'mail' && $('#s_interval').val()) {
                    return true;
                }

                if (rcmail.gui_objects.search_filter && $(rcmail.gui_objects.search_filter).val() != 'ALL') {
                    return true;
                }

                if (rcmail.gui_objects.foldersfilter && $(rcmail.gui_objects.foldersfilter).val() != '---') {
                    return true;
                }
            },
            close_func = function() {
                if ($(bar).is('.open')) {
                    options_button.click();
                }
            },
            update_func = function() {
                $(bar)[is_search_pending() ? 'addClass' : 'removeClass']('active');
                unread_button[rcmail.gui_objects.search_filter && $(rcmail.gui_objects.search_filter).val() == 'UNSEEN' ? 'addClass' : 'removeClass']('selected');
            };

        // Add Unread filter button
        if (input.is('#mailsearchform')) {
            unread_button = $('<a>')
                .attr({'class': 'button unread', href: '#', role: 'button', title: rcmail.gettext('showunread')})
                .on('click', function(e) {
                    $(rcmail.gui_objects.search_filter).val($(e.target).is('.selected') ? 'ALL' : 'UNSEEN');
                    rcmail.command('search');
                })
                .insertBefore(options_button);
        }

        options_button.on('click', function(e) {
            var id = $(this).data('target'),
                options = $('#' + id),
                open = options.is(':visible');

            if (options.length) {
                if (!open) {
                    if (ref[id]) {
                        ref[id](options.get(0), this, e);
                    }
                    else if (typeof window[id] == 'function') {
                        window[id](options.get(0), this, e);
                    }
                }

                options.next()[open ? 'show' : 'hide']();
                options.toggleClass('hidden');
                $('.floating-action-buttons').toggleClass('hidden');
                $(bar).toggleClass('open');

                $('button.search', options).off('click.search').on('click.search', function() {
                    options_button.trigger('click');
                    update_func();
                });
            }
        });

        input.on('input change', update_func)
            .on('focus blur', function(e) { input.attr('placeholder', e.type == 'blur' ? placeholder : ''); });

        // Search reset action
        $('a.reset', bar).on('click', function(e) {
            // for treelist widget's search setting val and keyup.treelist is needed
            // in normal search form reset-search command will do the trick
            input.val('').change().trigger('keyup.treelist', {keyCode: 27});
            if ($(bar).is('.open')) {
                options_button.click();
            }

            // Reset filter
            if (rcmail.gui_objects.search_filter) {
                $(rcmail.gui_objects.search_filter).val('ALL');
            }

            if (rcmail.gui_objects.foldersfilter) {
                $(rcmail.gui_objects.foldersfilter).val('---').change();
                rcmail.folder_filter('---');
            }

            update_func();
        });

        rcmail.addEventListener('init', update_func)
            .addEventListener('responsebeforesearch', update_func)
            .addEventListener('beforelist', close_func)
            .addEventListener('afterlist', update_func)
            .addEventListener('beforesearch', close_func);
    };

    /**
     * Converts toolbar menu into popup-menu for small screens
     */
    function toolbar_init()
    {
        if (env.got_smart_toolbar) {
            return;
        }

        env.got_smart_toolbar = true;

        var list_mark, items = [],
            list_items = [],
            meta = layout_metadata(),
            button_func = function(button, items, cloned) {
                var item = $('<li role="menuitem">'),
                    button = cloned ? create_cloned_button($(button), true, 'hidden-big hidden-large') : $(button).detach();

                // Remove empty text nodes that break alignment of text of the menu item
                button.contents().filter(function() { if (this.nodeType == 3 && !$.trim(this.nodeValue).length) $(this).remove(); });

                if (button.is('.spacer')) {
                    item.addClass('spacer');
                }
                else {
                    item.append(button);
                }

                items.push(item);
            };

        // convert content toolbar to a popup list
        layout.content.find('.header > .menu').each(function() {
            var toolbar = $(this);

            toolbar.children().each(function() { button_func(this, items); });
            toolbar.remove();
        });

        // convert list toolbar to a popup list
        layout.list.find('.header > .menu').each(function() {
            var toolbar = $(this);

            list_mark = toolbar.next();

            toolbar.children().each(function() {
                if (meta.mode != 'large') {
                    // TODO: Would be better to set this automatically on submenu display
                    //       i.e. in show/shown event (see popup_init()), if possible
                    $(this).data('popup-pos', 'right');
                }

                // add items to the content menu too
                button_func(this, items, true);
                button_func(this, list_items);
            });

            toolbar.remove();
        });

        // special elements to clone and add to the toolbar (mobile only)
        $('ul[data-menu="toolbar-small"] > li > a').each(function() {
            var button = $(this).clone();

            button.attr('id', this.id + '_clone');

            // TODO: rcmail.register_button()

            items.push($('<li role="menuitem">').addClass('hidden-big').append(button));
        });

        // append the new list toolbar and menu button
        if (list_items.length) {
            var container = layout.list.children('.header'),
                menu_attrs = {'class': 'menu toolbar popupmenu listing iconized', id: 'toolbar-list-menu'},
                menu_button = $('<a class="button icon toolbar-list-button" href="#list-menu">')
                    .attr({'data-popup': 'toolbar-list-menu'}),
                // TODO: copy original toolbar attributes (class, role, aria-*)
                toolbar = $('<ul>').attr(menu_attrs).data('popup-parent', container).append(list_items);

            if (list_mark.length) {
                toolbar.insertBefore(list_mark);
            }
            else {
                container.append(toolbar);
            }
            container.append(menu_button);
        }

        // append the new toolbar and menu button
        if (items.length) {
            var container = layout.content.children('.header'),
                menu_attrs = {'class': 'menu toolbar popupmenu listing iconized', id: 'toolbar-menu'},
                menu_button = $('<a class="button icon toolbar-menu-button" href="#menu">')
                    .attr({'data-popup': 'toolbar-menu'});

            container
                // TODO: copy original toolbar attributes (class, role, aria-*)
                .append($('<ul>').attr(menu_attrs).data('popup-parent', container).append(items))
                .append(menu_button);

            // bind toolbar menu with the menu button in the list header
            layout.list.find('a.toolbar-menu-button').click(function(e) {
                e.stopPropagation();
                menu_button.click();
            });
        }
    };

    /**
     * Initialize a popup for specified button element
     */
    function popup_init(item, win)
    {
        // On mobile we display the menu from the frame in the parent window
        if (is_framed && is_mobile()) {
            return parent.UI.popup_init(item, win || window);
        }

        if (!win) win = window;
        var level,
            popup_id = $(item).data('popup'),
            popup = $(win.$('#' + popup_id).get(0)), // a "hack" to support elements in frames
            popup_orig = popup,
            title = $(item).attr('title'),
            content_element = function() {
                // On mobile we display a menu from the frame in the parent window
                // To make menu actions working we have to clone the menu
                // and pass click events to it...
                if (win != window) {
                    popup = popup_orig.clone(true, true);
                    popup.attr('id', popup_id + '-clone')
                        .appendTo(document.body)
                        .find('li > a').attr('onclick', '').off('click').on('click', function(e) {
                            if (!$(this).is('.disabled')) {
                                $(item).popover('hide');
                                win.$('#' + $(this).attr('id')).click();
                            }

                            return false;
                        });
                }

                return popup.get(0);
            };

        $(item).attr({
                'aria-haspopup': 'true',
                'aria-expanded': 'false',
                'aria-owns': popup_id,
            })
            .popover({
                content: content_element,
                trigger: $(item).data('popup-trigger') || 'click',
                placement: $(item).data('popup-pos') || 'bottom',
                animation: true,
                boundary: 'window', // fix for https://github.com/twbs/bootstrap/issues/25428
                html: true
            })
            .on('show.bs.popover', function(event) {
                var init_func = popup.data('popup-init');

                if (popup_id && menus[popup_id]) {
                    menus[popup_id].transitioning = true;
                }

                if (init_func && ref[init_func]) {
                    ref[init_func](popup.get(0), item, event);
                }
                else if (init_func && win[init_func]) {
                    win[init_func](popup.get(0), item, event);
                }

                level = $('div.popover:visible').length + 1;

                popup.removeClass('hidden').attr('aria-hidden', false)
                    // Stop propagation on menu items that have popups
                    // to make a click on them not hide their parent menu(s)
                    .find('[aria-haspopup="true"]')
                        .data('level', level + 1)
                        .off('click.popup')
                        .on('click.popup', function(e) { e.stopPropagation(); });

                if (!is_mobile()) {
                    // Set popup height so it is less than the window height
                    popup.css('max-height', Math.min(36 * 15 - 1, $(window).height() - 30));
                }
            })
            .on('shown.bs.popover', function(event) {
                var mobile = is_mobile(),
                    popover = $('#' + $(item).attr('aria-describedby'));

                level = $(item).data('level') || 1;

                // Set popup Back/Close title
                if (mobile) {
                    var label = level > 1 ? 'back' : 'close',
                        title = rcmail.gettext(label),
                        class_name = 'button icon ' + (label == 'back' ? 'back' : 'cancel');

                    $('.popover-header', popover).empty()
                        .append($('<a>').attr('class', class_name).text(title)
                            .on('click', function(e) {
                                $(item).popover('hide');
                                if (level > 1) {
                                    e.stopPropagation();
                                }
                            })
                            .on('mousedown', function(e) {
                                // stop propagation to i.e. do not close jQuery-UI dialogs below
                                e.stopPropagation();
                            })
                        );
                }

                // Hide other menus on the same level
                $.each(menus, function(id, prop) {
                    if ($(prop.target).data('level') == level && id != popup_id) {
                        menu_hide(id);
                    }
                });

                // On keyboard event focus the first (active) entry and enable keyboard navigation
                if ($(item).data('event') == 'key') {
                    popover.off('keydown.popup').on('keydown.popup', 'a.active', function(e) {
                        var entry, node, mode = 'next';

                        switch (e.which) {
                            case 27: // ESC
                            case 9:  // TAB
                                $(item).popover('toggle').focus();
                                return false;

                            case 38: // ARROW-UP
                            case 63232:
                                mode = 'previous';
                            case 40: // ARROW-DOWN
                            case 63233:
                                entry = e.target.parentNode;
                                while (entry = entry[mode + 'Sibling']) {
                                    if (node = $(entry).children('.active')[0]) {
                                        node.focus();
                                        break;
                                    }
                                }
                                return false; // prevents from scrolling the whole page
                        }
                    });

                    popover.find('a.active').first().focus();
                }

                if (popup_id && menus[popup_id]) {
                    menus[popup_id].transitioning = false;
                }

                // add overlay element for phone layout
                if (mobile && !$('.popover-overlay').length) {
                    $('<div>').attr('class', 'popover-overlay')
                        .appendTo('body')
                        .click(function() { $(this).remove(); });
                }

                $('.popover-body', popover).addClass('webkit-scroller');
            })
            .on('hide.bs.popover', function() {
                if (level == 1) {
                    $('.popover-overlay').remove();
                }

                if (popup_id && menus[popup_id] && popup.is(':visible')) {
                    menus[popup_id].transitioning = true;
                }
            })
            .on('hidden.bs.popover', function() {
                if (/-clone$/.test(popup.attr('id'))) {
                    popup.remove();
                }
                else {
                    popup.attr('aria-hidden', true)
                        // Some menus aren't being hidden, force that
                        .addClass('hidden')
                        // Bootstrap will detach the popup element from
                        // the DOM (https://github.com/twbs/bootstrap/issues/20219)
                        // making our menus to not update buttons state.
                        // Work around this by attaching it back to the DOM tree.
                        .appendTo(popup.data('popup-parent') || document.body);
                }

                // close orphaned popovers, for some reason there are sometimes such dummy elements left
                $('.popover-body:empty').each(function() { $(this).parent().remove(); });

                if (popup_id && menus[popup_id]) {
                    delete menus[popup_id];
                }
            })
            // Because Bootstrap does not provide originalEvent in show/shown events
            // we have to handle that by our own using click and keydown handlers
            .on('click', function() {
                $(this).data('event', 'mouse');
            })
            .on('keydown', function(e) {
                if (e.originalEvent) {
                    switch (e.originalEvent.which) {
                    case 13:
                    case 32:
                        // Open the popup on ENTER or SPACE
                        e.preventDefault();
                        $(this).data('event', 'key').popover('toggle');
                        break;
                    case 27:
                        // Close the popup on ESC key
                        $(this).popover('hide');
                        break;
                    }
                }
            });

        // re-add title attribute removed by bootstrap popover
        if (title) {
            $(item).attr('title', title);
        }

        popup.attr('aria-hidden', 'true').data('button', item);

        // stop propagation to e.g. do not hide the popup when
        // clicking inside on form elements
        if (popup.data('editable')) {
            popup.on('click mousedown', function(e) { e.stopPropagation(); });
        }
    };

    /**
     * Closes all popups (for use as event handler)
     */
    function popups_close(e)
    {
        // Ignore some of propagated click events (see pretty_select())
        if (popups_close_lock && popups_close_lock > (new Date().getTime() - 250)) {
            return;
        }

        $('.popover.show').each(function() {
            var popup = $('.popover-body', this),
                button = popup.children().first().data('button');

            if (button && e.target != button && !$(button).find(e.target).length && typeof button !== 'string') {
                $(button).popover('hide');
            }

            if (!button) {
                $(this).remove();
            }
        });
    };

    /**
     * Handler for menu-open and menu-close events
     */
    function menu_toggle(p)
    {
        if (!p || !p.name || (p.props && p.props.skinable === false)) {
            return;
        }

        if (is_framed && is_mobile()) {
            if (!p.win) {
                p.win = window;
            }
            return parent.UI.menu_toggle(p);
        }

        if (p.name == 'messagelistmenu') {
            menu_messagelist(p);
        }
        else if (p.event == 'menu-open') {
            var fn, pos,
                content = $('ul', p.obj).first(),
                target = p.props && p.props.link ? p.props.link : p.originalEvent.target;

            if ($(target).is('span')) {
                target = $(target).parents('a,li')[0];
            }

            if (p.name.match(/^drag/)) {
                // create a fake element to position drag menu on the cursor position
                pos = rcube_event.get_mouse_pos(p.originalEvent);
                target = $('<a>').css({
                        position: 'absolute',
                        left: pos.x,
                        top: pos.y,
                        height: '1px',
                        width: '1px',
                        visibility: 'hidden'
                    })
                    .appendTo(document.body).get(0);
            }

            pos = $(target).data('popup-pos') || 'right';

            if (p.name == 'folder-selector') {
                content.addClass('listing folderlist');
            }
            else if (p.name == 'addressbook-selector' || p.name == 'contactgroup-selector') {
                content.addClass('listing contactlist');
            }
            else if (content.hasClass('menu')) {
                content.addClass('listing');
            }

            if (p.name == 'pagejump-selector') {
                content.addClass('simplelist');
                p.obj.addClass('simplelist');
                pos = 'top';
            }

            // There can be only one menu of the same type
            if (menus[p.name]) {
                menu_hide(p.name, p.originalEvent);
            }

            // Popover menus use animation. Sometimes the same menu is
            // immediately hidden and shown (e.g. folder-selector for copy and move action)
            // we have to wait until the previous menu hides before we can open it again
            fn = function() {
                if (menus[p.name] && menus[p.name].transitioning) {
                    return setTimeout(fn, 50);
                }

                if (!$(target).data('popup')) {
                    $(target).data({
                        event: rcube_event.is_keyboard(p.originalEvent) ? 'key' : 'mouse',
                        popup: p.name,
                        'popup-pos': pos,
                        'popup-trigger': 'manual'
                    });
                    popup_init(target, p.win);
                }

                menus[p.name] = {target: target};
                $(target).popover('show');
            }

            fn();
        }
        else {
            menu_hide(p.name, p.originalEvent);
        }

        // Stop propagation so multi-level menus work properly
        p.originalEvent.stopPropagation();
    };

    /**
     * Close menu by name
     */
    function menu_hide(name, event)
    {
        var target = menu_target(name);

        if (name.match(/^drag/)) {
            $(target).popover('dispose').remove();
        }
        else {
            $(target).popover('hide');

            // In phone mode close all menus when forwardmenu is requested to be closed
            // FIXME: This is a hack, we need some generic solution.
            if (name == 'forwardmenu') {
                popups_close(event);
            }
        }
    };

    /**
     * Destroys menu by name
     *
     * This is required when you replace the menu content element
     */
    function menu_destroy(name)
    {
        $('[aria-owns=' + name + ']').popover('dispose').data('popup', null);
    };

    /**
     * Get menu target by name
     */
    function menu_target(name)
    {
        var target;

        if (menus[name]) {
            target = menus[name].target;
        }
        else {
            target = $('#' + name).data('button');

            if (!target) {
                // catch cases as 'forwardmenu' where menu suffix has no hyphen
                // or try with -menu suffix if it's not in the menu name already
                if (name.match(/(?!-)menu$/)) {
                    name = name.substr(0, name.length - 4);
                }

                target = $('#' + name + '-menu').data('button');
            }
        }

        return target;
    };

    /**
     * Messages list options dialog
     */
    function menu_messagelist(p)
    {
        var content = $('#listoptions-menu'),
            width = content.width() + 25,
            dialog = content.clone(true);

        // set form values
        $('select[name="sort_col"]', dialog).val(rcmail.env.sort_col || '');
        $('select[name="sort_ord"]', dialog).val(rcmail.env.sort_order || 'ASC');
        $('select[name="mode"]', dialog).val(rcmail.env.threading ? 'threads' : 'list');

        // Fix id/for attributes
        $('select', dialog).each(function() { this.id = this.id + '-clone'; });
        $('label', dialog).each(function() { $(this).attr('for', $(this).attr('for') + '-clone'); });

        var save_func = function(e) {
            if (rcube_event.is_keyboard(e.originalEvent)) {
                $('#listmenulink').focus();
            }

            var col = $('select[name="sort_col"]', dialog).val(),
                ord = $('select[name="sort_ord"]', dialog).val(),
                mode = $('select[name="mode"]', dialog).val();

            rcmail.set_list_options([], col, ord, mode == 'threads' ? 1 : 0);
            return true;
        };

        dialog = rcmail.simple_dialog(dialog, rcmail.gettext('listoptionstitle'), save_func, {
            closeOnEscape: true,
            minWidth: 400
        });
    };

    /**
     * About dialog
     */
    function about_dialog(elem)
    {
        var support_url, support_func, support_button = false,
            dialog = $('<iframe>').attr({id: 'aboutframe', src: rcmail.url('settings/about', {_framed: 1})}),
            support_link = $('#supportlink');

        if (support_link.length && (support_url = support_link.attr('href'))) {
            support_button = support_link.text();
            support_func = function(e) { support_url.indexOf('mailto:') < 0 ? window.open(support_url) : location.href = support_url; };
        }

        rcmail.simple_dialog(dialog, $(elem).text(), support_func, {
            button: support_button,
            button_class: 'help',
            cancel_button: 'close',
            height: 400
        });
    };

    /**
     * Show/hide more mail headers (envelope)
     */
    function headers_show(button)
    {
        var headers = $(button).parent().prev();
        headers[headers.is('.hidden') ? 'removeClass' : 'addClass']('hidden');
    };

    /**
     * Mail headers dialog
     */
    function headers_dialog()
    {
        var props = {_uid: rcmail.env.uid, _mbox: rcmail.env.mailbox, _framed: 1},
            dialog = $('<iframe>').attr({id: 'headersframe', src: rcmail.url('headers', props)});

        rcmail.simple_dialog(dialog, rcmail.gettext('arialabelmessageheaders'), null, {
            cancel_button: 'close',
            height: 400
        });
    };

    /**
     * Mail import dialog
     */
    function import_dialog()
    {
        if (!rcmail.commands['import-messages']) {
            return;
        }

        var content = $('#uploadform'),
            dialog = content.clone(true);

        var save_func = function(e) {
            return rcmail.command('import-messages', $(dialog.find('form')[0]));
        };

        rcmail.simple_dialog(dialog, rcmail.gettext('importmessages'), save_func, {
            button: 'import',
            closeOnEscape: true,
            minWidth: 400
        });
    };

    /**
     * Search options menu popup
     */
    function searchmenu(obj)
    {
        var n, all,
            list = $('input[name="s_mods[]"]', obj),
            scope_select = $('#s_scope', obj),
            mbox = rcmail.env.mailbox,
            mods = rcmail.env.search_mods,
            scope = rcmail.env.search_scope || 'base';

        if (!$(obj).data('initialized')) {
            $(obj).data('initialized', true);
            if (list.length) {
                list.on('change', function() { set_searchmod(obj, this); });
                rcmail.addEventListener('beforesearch', function() { set_searchmod(obj); });
            }
        }

        if (rcmail.env.search_mods) {
            if (rcmail.env.task == 'mail') {
                if (scope == 'all') {
                    mbox = '*';
                }

                mods = mods[mbox] ? mods[mbox] : mods['*'];
                all = 'text';
                scope_select.val(scope);
            }
            else {
                all = '*';
            }

            if (mods[all]) {
                list.map(function() {
                    this.checked = true;
                    this.disabled = this.value != all;
                });
            }
            else {
                list.prop('disabled', false).prop('checked', false);
                for (n in mods) {
                    list.filter('[value="' + n + '"]').prop('checked', true);
                }
            }
        }
    };

    function set_searchmod(menu, elem)
    {
        var all, m, task = rcmail.env.task,
            mods = rcmail.env.search_mods,
            mbox = rcmail.env.mailbox,
            scope = $('#s_scope', menu).val(),
            interval = $('#s_interval', menu).val();

        if (scope == 'all') {
            mbox = '*';
        }

        if (!mods) {
            mods = {};
        }

        if (task == 'mail') {
            if (!mods[mbox]) {
                mods[mbox] = rcube_clone_object(mods['*']);
            }
            m = mods[mbox];
            all = 'text';

            rcmail.env.search_scope = scope;
            rcmail.env.search_interval = interval;
        }
        else { //addressbook
            m = mods;
            all = '*';
        }

        if (!elem) {
            return;
        }

        if (!elem.checked) {
            delete(m[elem.value]);
        }
        else {
            m[elem.value] = 1;
        }

        // mark all fields
        if (elem.value == all) {
            $('input[name="s_mods[]"]', menu).map(function() {
                if (this == elem) {
                    return;
                }

                this.checked = true;

                if (elem.checked) {
                    this.disabled = true;
                    delete m[this.value];
                }
                else {
                    this.disabled = false;
                    m[this.value] = 1;
                }
            });
        }

        rcmail.set_searchmods(m);
    };

    /**
     * Spellcheck languages list
     */
    function spellmenu(obj)
    {
        var i, link, li, list = [],
            lang = rcmail.spellcheck_lang(),
            ul = $('ul', obj);

        if (!ul.length) {
            ul = $('<ul class="selectable listing iconized" role="menu">');

            for (i in rcmail.env.spell_langs) {
                li = $('<li role="menuitem">');
                link = $('<a href="#'+ i +'" tabindex="0"></a>')
                    .text(rcmail.env.spell_langs[i])
                    .addClass('active').data('lang', i)
                    .on('click keypress', function(e) {
                        if (e.type != 'keypress' || rcube_event.get_keycode(e) == 13) {
                            rcmail.spellcheck_lang_set($(this).data('lang'));
                            rcmail.hide_menu('spell-menu', e);
                            return false;
                        }
                    });

                link.appendTo(li);
                list.push(li);
            }

            ul.append(list).appendTo(obj);
        }

        // select current language
        $('li', ul).each(function() {
            var el = $('a', this);
            if (el.data('lang') == lang) {
                el.addClass('selected').attr('aria-selected', 'true');
            }
            else if (el.hasClass('selected')) {
                el.removeClass('selected').removeAttr('aria-selected');
            }
        });
    };

    /**
     * Add/remove item to/from compose options status bar
     */
    function compose_status(id, status)
    {
        var bar = $('#composestatusbar'), ico = bar.find('a.button.icon.' + id);

        if (!status) {
            ico.remove();
        }
        else if (!ico.length) {
            $('<a>').attr('class', 'button icon ' + id)
                .on('click', function() { show_sidebar(); })
                .appendTo(bar);
        }
    };

    /**
     * Attachment menu
     */
    function attachmentmenu(obj, button, event)
    {
        var id = $(button).parent().attr('id').replace(/^attach/, '');

        $.each(['open', 'download', 'rename'], function() {
            var action = this;
            $('#attachmenu' + action, obj).off('click').attr('onclick', '').click(function(e) {
                return rcmail.command(action + '-attachment', id, this, e.originalEvent);
            });
        });

        // call menu-open so core can set state of menu commands
        return rcmail.command('menu-open', {menu: 'attachmentmenu', id: id}, obj, event);
    };

    /**
     * Appends drop-icon to attachments list item (to invoke attachment menu)
     */
    function attachmentmenu_append(item)
    {
        item = $(item);

        if (!item.is('.no-menu') && !item.children('.dropdown').length) {
            var label = rcmail.gettext('options'),
                fname = item.find('a.filename');

            var button = $('<a>').attr({
                    href: '#',
                    tabindex: fname.attr('tabindex') || 0,
                    title: label,
                    'class': 'button icon dropdown skip-content'
                })
                .on('click', function(e) {
                    return attachmentmenu($('#attachmentmenu'), button, e);
                })
                .append($('<span>').attr('class', 'inner').text(label));

            if (fname.length) {
                button.insertAfter(fname);
            }
            else {
                button.appendTo(item);
            }
        }
    };

    /**
     * Mailto menu
     */
    function mailtomenu(obj, button, event, onclick)
    {
        var mailto = $(button).attr('href').replace(/^mailto:/, '');

        if (mailto.indexOf('@') < 0) {
            return true; // let the browser handle this
        }

        // disable all menu actions
        obj.find('a').off('click').removeClass('active');

        if (rcmail.env.has_writeable_addressbook) {
            $('.addressbook', obj).addClass('active')
                .on('click', function(e) {
                    var i, contact = mailto,
                        txt = $(button).filter('.rcmContactAddress').text();

                    contact = contact.split('?')[0].split(',')[0].replace(/(^<|>$)/g, '');

                    if (txt) {
                        txt = txt.replace('<' + contact + '>', '');
                        contact = '"' + $.trim(txt) + '" <' + contact + '>';
                    }

                    return rcmail.command('add-contact', contact, this, e.originalEvent);
                });
        }

        $('.compose', obj).addClass('active').on('click', function(e) {
            // Execute the original onclick handler to support mailto URL arguments (#6751)
            if (onclick) {
                button.onclick = onclick;
                // use the second argument to tell our handler to not display the menu again
                $(button).trigger('click', [true]);
                button.onclick = null;
            }
            else {
                rcmail.command('compose', mailto, this, e.originalEvent);
            }

            return false; // for Chrome
        });

        return rcmail.command('menu-open', {menu: 'mailto-menu', link: button}, button, event.originalEvent);
    };

    /**
     * Appends popup menu to mailto links
     */
    function mailtomenu_append(item)
    {
        // Remember the original onclick handler and display the menu instead
        var onclick = item.onclick;
        item.onclick = null;
        $(item).on('click', function(e, menu) {
            return menu || mailtomenu($('#mailto-menu'), item, e, onclick);
        });
    };

    /**
     * Headers menu in mail compose
     */
    function headersmenu(obj, button, event)
    {
        $('li > a', obj).each(function() {
            var link = $(this), target = '#compose_' + link.data('target');

            link[$(target).is(':visible') ? 'removeClass' : 'addClass']('active')
                .off().on('click', function() {
                    $(target).removeClass('hidden').find('.recipient-input input').focus();
                    link.removeClass('active');
                    rcmail.set_menu_buttons();
                });
        });
    };

    /**
     * Reset/hide compose message recipient input
     */
    function header_reset(id)
    {
        $('#' + id).val('').change()
            // jump to the next input
            .closest('.form-group').nextAll(':not(.hidden)').first().find('input').focus();

        $('a[data-target=' + id.replace(/^_/, '') + ']').addClass('active');
        rcmail.set_menu_buttons();
    };

    /**
     * Recipient (contact) selector
     */
    function recipient_selector(field, opts)
    {
        if (!opts) opts = {};

        var title = rcmail.gettext(opts.title || 'insertcontact'),
            dialog = $('#recipient-dialog'),
            parent = dialog.parent(),
            close_func = function() {
                if (dialog.is(':visible')) {
                    rcmail.env.recipient_dialog.dialog('close');
                }
            },
            insert_func = function() {
                if (opts.action) {
                    opts.action();
                    close_func();
                    return;
                }

                rcmail.command('add-recipient');
            };

        if (!rcmail.env.recipient_selector_initialized) {
            rcmail.addEventListener('add-recipient', close_func);
            rcmail.env.recipient_selector_initialized = true;
        }

        if (field) {
            rcmail.env.focused_field = '#_' + field;
        }

        rcmail.contact_list.clear_selection();
        rcmail.contact_list.multiselect = 'multiselect' in opts ? opts.multiselect : true;

        rcmail.env.recipient_dialog = rcmail.simple_dialog(dialog, title, insert_func, {
            button: rcmail.gettext(opts.button || 'insert'),
            button_class: opts.button_class || 'insert recipient',
            height: 600,
            classes: {
              'ui-dialog-content': 'p-0' // remove padding on dialog content
            },
            open: function() {
                // Don't want focus in the search field, we focus first contacts source record instead
                $('#directorylist a').first().focus();
            },
            close: function() {
                dialog.appendTo(parent);
                $(this).remove();
                $(opts.focus || rcmail.env.focused_field).focus();
            }
        });
    };

    /**
     * Create/Update quota widget (setquota event handler)
     */
    function update_quota(p)
    {
        var element = $('#quotadisplay'),
            bar = element.find('.bar'),
            value = p.total ? p.percent : 0;

        if (!bar.length) {
            bar = $('<span class="bar"><span class="value"></span></span>').appendTo(element);
        }

        if (value > 0 && value < 10) {
            value = 10; // smaller values look not so nice
        }

        bar.find('.value').css('width', value + '%')[value >= 90 ? 'addClass' : 'removeClass']('warning');
        // set title and reset tooltip's data (needed in case of empty title)
        element.attr({'data-original-title': '', title: element.find('.count').attr('title')});

        if (p.table) {
            element.css('cursor', 'pointer').data('popup-pos', 'top')
                .off('click').on('click', function(e) {
                    rcmail.simple_dialog(p.table, 'quota', null, {cancel_button: 'close'});
                });
        }
        else {
            element.tooltip('dispose').tooltip({trigger: is_mobile() ? 'click' : 'hover'});
        }
    };

    /**
     * Replaces recipient input with content-editable element that uses "recipient boxes"
     */
    function recipient_input(obj)
    {
        var list, input,
            input_len_update = function() {
                input.css('width', Math.max(40, input.val().length * 15 + 25));
            },
            apply_func = function() {
                // update the original input
                $(obj).val(list.text() + input.val());
            },
            insert_recipient = function(name, email, replace) {
                var recipient = $('<li class="recipient">'),
                    name_element = $('<span class="name">').html(recipient_input_name(name || email))
                        .on('dblclick', function(e) { recipient_input_edit_dialog(e, insert_recipient); }),
                    email_element = $('<span class="email">'),
                    // TODO: should the 'close' link have tabindex?
                    link = $('<a>').attr({'class': 'button icon remove'})
                        .click(function() {
                            recipient.remove();
                            apply_func();
                            input.focus();
                            return false;
                        });

                if (name) {
                    email = ' <' + email + '>';
                }

                email_element.text((name ? email : '') + ',');
                recipient.attr('title', name ? (name + email) : null)
                    .append([name_element, email_element, link])

                if (replace)
                    replace.replaceWith(recipient);
                else
                    recipient.insertBefore(input.parent());

                apply_func();
            },
            update_func = function(text) {
                var result;

                text = (text || input.val()).replace(/[,;\s]+$/, '');
                result = recipient_input_parser(text);

                $.each(result.recipients, function() {
                    insert_recipient(this.name, this.email);
                });

                input.val(result.text);
                apply_func();
                input_len_update();

                return result.recipients.length > 0;
            },
            parse_func = function(e) {
                var paste, value = this.value;

                // On paste the text is not yet in the input we have to use clipboard.
                // Also because on paste new-line characters are replaced by spaces (#6460)
                if (e.type == 'paste') {
                    // pasted text
                    paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text') || '';
                    // insert pasted text in place of the selection (or just cursor position)
                    value = value.substring(0, this.selectionStart) + paste + value.substring(this.selectionEnd);
                    e.preventDefault();
                }

                update_func(value);
            },
            keydown_func = function(e) {
                // On Backspace remove the last recipient
                if (e.keyCode == 8 && !input.val().length) {
                    list.children('li.recipient').last().remove();
                    apply_func();
                    return false;
                }
                // Here we add a recipient box when the separator (,;) or Enter was pressed
                else if (e.key == ',' || e.key == ';' || (e.key == 'Enter' && !rcmail.ksearch_visible())) {
                    if (update_func()) {
                        return false;
                    }
                }

                input_len_update();
            };

        // Create the input element and "editable" area
        input = $('<input>').attr({type: 'text', tabindex: $(obj).attr('tabindex')})
            .on('paste change', parse_func)
            .on('input', input_len_update) // only to fix input length after paste
            .on('keydown', keydown_func)
            .on('blur', function() { list.removeClass('focus'); })
            .on('focus mousedown', function() { list.addClass('focus'); });

        list = $('<ul>').addClass('form-control recipient-input ac-input rounded-left')
            .append($('<li>').append(input))
            .on('click', function() { input.focus(); });

        // Hide the original input/textarea
        // Note: we do not remove the original element, and we do not use
        // display: none, because we want to handle onfocus event
        // Note: tabindex:-1 to make Shift+TAB working on these widgets
        $(obj).css({position: 'absolute', opacity: 0, left: '-5000px', width: '10px'})
            .attr('tabindex', -1)
            .after(list)
            // some core code sometimes focuses or changes the original node
            // in such cases we wan't to parse it's value and apply changes
            // to the widget element
            .on('focus', function(e) { input.focus(); e.preventDefault(); })
            .on('change', function() {
                $('li.recipient', list).remove();
                input.val(this.value).change();
            })
            // copy and parse the value already set
            .change();

        // Init autocompletion
        rcmail.init_address_input_events(input);
    };

    /**
     * Parses recipient address input and extracts recipients from it
     */
    function recipient_input_parser(text)
    {
        // support new-line as a separator, for paste action (#6460)
        text = $.trim(text.replace(/[,;\s]*[\r\n]+/g, ','));

        var recipients = [],
            address_rx_part = '(\\S+|("[^"]+"))@\\S+',
            recipient_rx1 = new RegExp('(<' + address_rx_part + '>)'),
            recipient_rx2 = new RegExp('(' + address_rx_part + ')'),
            global_rx = /(?=\S)[^",;]*(?:"[^\\"]*(?:\\[,;\S][^\\"]*)*"[^",;]*)*/g,
            matches = text.match(global_rx);

        $.each(matches || [], function() {
            if (this.length && (recipient_rx1.test(this) || recipient_rx2.test(this))) {
                var email = RegExp.$1,
                    name = $.trim(this.replace(email, ''));

                recipients.push({
                    name: name,
                    email: email.replace(/(^<|>$)/g, ''),
                    text: this
                });

                text = text.replace(this, '');
            }
        });

        text = text.replace(/[,;]+/, ',').replace(/^[,;\s]+/, '');

        return {recipients: recipients, text: text};
    };

    /**
     * Generates HTML for a text adding <span class="hidden">
     * for quote/backslash characters, so they are hidden from the user,
     * but still in place to make copying simpler
     *
     * Note: Selection works in Chrome, but not in Firefox?
     */
    function recipient_input_name(text)
    {
        var i, char, result = '', len = text.length;

        if (text.charAt(0) != '"' && text.indexOf('"') > -1) {
            text = '"' + text.replace('\\', '\\\\').replace('"', '\\"') + '"';
        }

        for (i=0; i<len; i++) {
            char = text.charAt(i);
            switch (char) {
                case '"':
                    if (i > 0 && i < len - 1) {
                        result += '"';
                        break;
                    }

                    result += '<span class="quotes">' + char + '</span>';
                    break;

                case '\\':
                    result += '<span class="quotes">' + char + '</span>';

                    if (text.charAt(i+1) == '\\') {
                        result += char;
                        i++;
                    }
                    break;

                case '<':
                    result += '&lt;';
                    break;

                case '>':
                    result += '&gt;';
                    break;

                default:
                    result += char;
            }
        }

        return result;
    };

    /**
     * Displays dialog to edit a recipient entry
     */
    function recipient_input_edit_dialog(e, callback)
    {
        var element = $(e.target).parents('.recipient'),
            recipient = element.text().replace(/,+$/, ''),
            input = $('<input>').attr({type: 'text', size: 50}).val(recipient),
            content = $('<label>').text(rcmail.gettext('recipient')).append(input);

        rcmail.simple_dialog(content, 'recipientedit', function() {
                var result, value = input.val();
                if (value) {
                    if (value != recipient) {
                        result = recipient_input_parser(value);

                        if (result.recipients.length != 1) {
                            return false;
                        }

                        callback(result.recipients[0].name, result.recipients[0].email, element);
                    }

                    return true;
                }
            });
    };

    /**
     * Adds logic to the contact photo widget
     */
    function image_upload_input(obj)
    {
        var reset_button = $('<a>')
                .attr({'class': 'icon button delete', href: '#', })
                .click(function(e) { rcmail.command('delete-photo', '', this, e); return false; }),
            img = $(obj).find('img')[0],
            img_onload = function() {
                var state = (img.currentSrc || img.src).indexOf(rcmail.env.photo_placeholder) != -1;
                $(obj)[state ? 'removeClass' : 'addClass']('changed');
            };

        $(obj).append(reset_button).click(function() { rcmail.upload_input('upload-form'); });

        // Note: Looks like only Firefox does not need this separate call
        img_onload();
        $(img).on('load', img_onload);
    };

    /**
     * Displays loading... overlay for iframes
     */
    function iframe_loader(frame)
    {
        frame = $(frame);

        if (frame.length) {
            var loader = $('<div class="iframe-loader">')
                .append($('<div class="spinner spinner-border" role="status">')
                    .append($('<span class="sr-only">').text(rcmail.gettext('loading'))));

            // custom 'loaded' event is expected to be triggered by plugins
            // when using the loader not on an iframe
            frame.on('load error loaded', function() {
                    // wait some time to make sure the iframe stopped loading
                    setTimeout(function() { loader.remove(); }, 500);
                })
                .parent().append(loader);

            // fix scrolling in iOS
            if (ios) {
                frame.parent().addClass('ios-scroll');
            }
        }
    };

    /**
     * Convert checkbox input into Bootstrap's custom switch
     */
    function pretty_checkbox(checkbox)
    {
        var label, parent, id, checkbox = $(checkbox);

        if (checkbox.is('.custom-control-input')) {
            return;
        }

        if (!(id = checkbox.attr('id'))) {
            id = 'icochk' + (++env.checkboxes);
            checkbox.attr('id', id);
        }

        if (checkbox.parent().is('label')) {
            label = checkbox.parent();
            checkbox = checkbox.detach();
            label.before(checkbox);
        }
        else {
            label = $('<label>');
        }

        label.attr({'for': id, 'class': 'custom-control-label', title: checkbox.attr('title') || ''})
            .on('click', function(e) { e.stopPropagation(); });

        checkbox.addClass('form-check-input custom-control-input')
            .wrap('<div class="custom-control custom-switch">')
            .parent().append(label);
    };

    /**
     * Make select dropdowns pretty
     * TODO: searching, optgroup, [multiple], iPhone/iPad
     */
    function pretty_select(select)
    {
        // iPhone is not supported yet (problem with browser dropdown on focus)
        if (bw.iphone || bw.ipad) {
            return;
        }

        select = $(select);

        if (select.is('.pretty-select')) {
            return;
        }

        var select_ident = 'select' + select.attr('id') + select.attr('name');
        var is_menu_open = function() {
            // Use proper window in cases when the select element intialized
            // inside an iframe is then used in a dialog inside a parent's window
            // For some reason we can't access data-button property in cross-window
            // case, we use data-ident attribute instead
            var win = select[0].ownerDocument.defaultView;
            if (win.$('.select-menu .listing').data('ident') == select_ident) {
                return true;
            }
        };

        var close_func = function() {
            var open = is_menu_open();
            select.popover('dispose').focus();
            return !open;
        };

        var open_func = function(e) {
            var items = [],
                dialog = select.closest('.ui-dialog')[0],
                max_height = (document.documentElement.clientHeight || $(document.body).height()) - 75,
                max_width = $(document.body).width() - 20,
                min_width = Math.min(select.outerWidth(), max_width),
                value = select.val();

            if (!is_mobile()) {
                max_height *= 0.5;
            }

            // close other popups
            popups_close(e);

            $('option', select).each(function() {
                var label = $(this).text(),
                    link = $('<a href="#">')
                        .data('value', this.value)
                        .addClass(this.disabled ? 'disabled' : 'active' + (this.value == value ? ' selected' : ''));

                if (label.length) {
                    link.text(label);
                }
                else {
                    link.html('&nbsp;'); // link can't be empty
                }

                items.push($('<li>').append(link));
            });

            var list = $('<ul class="listing selectable iconized">')
                .attr('data-ident', select_ident)
                .data('button', select[0])
                .append(items)
                .on('click', 'a.active', function() {
                    // first close the list, then update the select, the order is important
                    // for cases when the select might be removed in change event (datepicker)
                    var val = $(this).data('value'), ret = close_func();
                    select.val(val).change();
                    return ret;
                })
                .on('keydown', 'a.active', function(e) {
                    var item, node, mode = 'next';

                    switch (e.which) {
                        case 27: // ESC
                        case 9:  // TAB
                            return close_func();

                        case 13: // ENTER
                        case 32: // SPACE
                            $(this).click();
                            return false; // for IE

                        case 38: // ARROW-UP
                        case 63232:
                            mode = 'previous';
                        case 40: // ARROW-DOWN
                        case 63233:
                            item = e.target.parentNode;
                            while (item = item[mode + 'Sibling']) {
                                if (node = $(item).children('.active')[0]) {
                                    node.focus();
                                    break;
                                }
                            }
                            return false; // prevents from scrolling the whole page
                    }
                });

            select.popover('dispose')
                .popover({
                    // because of focus issues we can't always use body,
                    // if select is in a dialog, popover has to be a child of this dialog
                    container: dialog || document.body,
                    content: list[0],
                    placement: 'bottom',
                    trigger: 'manual',
                    boundary: 'viewport',
                    html: true,
                    offset: '0,2',
                    sanitize: false,
                    template: '<div class="popover select-menu" style="min-width: ' + min_width + 'px; max-width: ' + max_width + 'px">'
                        + '<div class="popover-header"></div>'
                        + '<div class="popover-body" style="max-height: ' + max_height + 'px"></div></div>'
                })
                .on('shown.bs.popover', function() {
                    select.focus(); // for Chrome
                    // Set popup Close title
                    list.parent().prev()
                        .empty()
                        .append($('<a class="button icon cancel">').text(rcmail.gettext('close'))
                            .on('click', function(e) {
                                e.stopPropagation();
                                return close_func();
                            })
                        );

                    // focus first active element on the list
                    if (rcube_event.is_keyboard(e)) {
                        list.find('a.active').first().focus();
                    }

                    // don't propagate mousedown event
                    list.on('mousedown', function(e) { e.stopPropagation(); });
                })
                .popover('show');
        };

        select.addClass('pretty-select custom-select form-control')
            .on('mousedown keydown', function(e) {
                select = $(e.target); // so it works after clone

                // Do nothing on disabled select or on TAB key
                if (select.prop('disabled')) {
                    return;
                }

                if (e.which == 9) {
                    close_func();
                    return true;
                }

                // Close popup on ESC key or on click if already open
                if (e.which == 27 || (e.type == 'mousedown' && is_menu_open())) {
                    return close_func();
                }

                select.focus();

                // prevent displaying browser-default select dropdown
                select.prop('disabled', true);
                setTimeout(function() { select.prop('disabled', false); }, 0);
                e.stopPropagation();

                // display options in our way (on SPACE, ENTER, ARROW-DOWN or mousedown)
                if (e.type == 'mousedown' || e.which == 13 || e.which == 32 || e.which == 40 || e.which == 63233) {
                    open_func(e);

                    // Prevent from closing the menu by general popover closing handler (popups_close())
                    // We used to just stop propagation in onclick handler, but it didn't work
                    // in Chrome where onclick handler wasn't invoked on mobile (#6705)
                    popups_close_lock = new Date().getTime();

                    return false;
                }
            })
    };

    /**
     * HTML editor textarea wrapper with nice looking tabs-like switch
     */
    function html_editor_init(obj)
    {
        // Here we support two structures
        // 1. <div><textarea></textarea><select name="editorSelector"></div>
        // 2. <tr><td><td><td><textarea></textarea></td></tr>
        //    <tr><td><td><td><input type="checkbox"></td></tr>

        var sw, is_table = false,
            editor = $(obj),
            parent = editor.parent(),
            tabindex = editor.attr('tabindex'),
            mode = function() {
                if (is_table) {
                    return sw.is(':checked') ? 'html' : 'plain';
                }

                return sw.val();
            },
            tabs = $('<ul class="nav nav-tabs">')
                .append($('<li class="nav-item">')
                    .append($('<a class="nav-link mode-html" href="#">')
                        .text(rcmail.gettext('htmltoggle'))))
                .append($('<li class="nav-item">')
                    .append($('<a class="nav-link mode-plain" href="#">')
                        .text(rcmail.gettext('plaintoggle'))));

        if (parent.is('td')) {
            sw = $('input[type="checkbox"]', parent.parent().next());
            is_table = true;
        }
        else {
            sw = $('[name="editorSelector"]', obj.form);
        }

        // sanity check
        if (sw.length != 1) {
            return;
        }

        parent.addClass('html-editor');
        editor.before(tabs);

        $('a', tabs).attr('tabindex', tabindex)
            .on('click', function(e) {
                var id = editor.attr('id'), is_html = $(this).is('.mode-html');

                e.preventDefault();
                if (rcmail.command('toggle-editor', {id: id, html: is_html}, '', e.originalEvent)) {
                    $(this).tab('show').prop('tabindex', -1);
                    $('.mode-' + (is_html ? 'plain' : 'html'), tabs).prop('tabindex', tabindex);

                    if (is_table) {
                        sw.prop('checked', is_html);
                    }
                }
            })
            .filter('.mode-' + mode()).tab('show').prop('tabindex', -1);

        if (is_table) {
            // Hide unwanted table cells
            sw.parents('tr').first().hide();
            parent.prev().hide();
            // Modify the textarea cell to use 100% width
            parent.addClass('col-sm-12');
        }

        // make the textarea autoresizeable
        textarea_autoresize_init(editor);
    };

    /**
     * Make the textarea autoresizeable depending on it's content length.
     * The way there's no vertical scrollbar.
     */
    function textarea_autoresize_init(textarea)
    {
        var resize = function(e) {
            clearTimeout(env.textarea_timer);
            env.textarea_timer = setTimeout(function() {
                var area = $(e.target),
                    initial_height = area.data('initial-height'),
                    scroll_height = area[0].scrollHeight;

                // do nothing when the area is hidden
                if (!scroll_height) {
                    return;
                }

                if (!initial_height) {
                    area.data('initial-height', initial_height = scroll_height);
                }

                // strange effect in Chrome/Firefox when you delete a line in the textarea
                // the scrollHeight is not decreased by the line height, but by 2px
                // so jumps up many times in small steps, we'd rather use one big step
                if (area.outerHeight() - scroll_height == 2) {
                    scroll_height -= 19; // 21px is the assumed line height
                }

                area.outerHeight(Math.max(initial_height, scroll_height));
            }, 10);
        };

        $(textarea).css('overflow-y', 'hidden').on('input', resize).trigger('input');

        // Make sure the height is up-to-date also in time intervals
        setInterval(function() { $(textarea).trigger('input'); }, 1000);
    };

    // Inititalizes smart list input
    function smart_field_init(field)
    {
        var tip, id = field.id + '_list',
            area = $('<div class="multi-input"><div class="content"></div><div class="invalid-feedback"></div></div>'),
            list = field.value ? field.value.split("\n") : [''];

        if ($('#' + id).length) {
            return;
        }

        // add input rows
        $.each(list, function(i, v) {
            smart_field_row_add($('.content', area), v, field.name, i, $(field).data('size'));
        });

        area.attr('id', id);
        field = $(field);

        if (field.attr('disabled')) {
            area.hide();
        }
        // disable the original field anyway, we don't want it in POST
        else {
            field.prop('disabled', true);
        }

        if (field.data('hidden')) {
            area.hide();
        }

        field.after(area);

        if (field.hasClass('is-invalid')) {
            area.addClass('is-invalid');
            $('.invalid-feedback', area).text(field.data('error-msg'));
        }
    };

    function smart_field_row_add(area, value, name, idx, size, after)
    {
        // build row element content
        var input, elem = $('<div class="input-group">'
                + '<input type="text" class="form-control">'
                + '<span class="input-group-append"><a class="icon reset input-group-text" href="#"></a></span>'
                + '</div>'),
            attrs = {value: value, name: name + '[]'};

        if (size) {
            attrs.size = size;
        }

        input = $('input', elem).attr(attrs)
            .keydown(function(e) {
                var input = $(this);

                // element creation event (on Enter)
                if (e.which == 13) {
                    var name = input.attr('name').replace(/\[\]$/, ''),
                        dt = (new Date()).getTime(),
                        elem = smart_field_row_add(area, '', name, dt, size, input.parent());

                    $('input', elem).focus();
                }
                // backspace or delete: remove input, focus previous one
                else if ((e.which == 8 || e.which == 46) && input.val() == '') {
                    var parent = input.parent(),
                        siblings = area.children();

                    if (siblings.length > 1) {
                        if (parent.prev().length) {
                            parent.prev().children('input').focus();
                        }
                        else {
                            parent.next().children('input').focus();
                        }

                        parent.remove();
                        return false;
                    }
                }
            });

        // element deletion event
        $('a.reset', elem).click(function() {
            var record = $(this.parentNode.parentNode);

            if (area.children().length > 1) {
                $('input', record.next().length ? record.next() : record.prev()).focus();
                record.remove();
            }
            else {
                $('input', record).val('').focus();
            }
        });

        $(elem).find('input,a')
            .on('focus', function() { area.addClass('focused'); })
            .on('blur', function() { area.removeClass('focused'); });

        if (after) {
            after.after(elem);
        }
        else {
            elem.appendTo(area);
        }

        return elem;
    };

    // Reset and fill the smart list input with new data
    function smart_field_reset(field, data)
    {
        var id = field.id + '_list',
            list = data.length ? data : [''],
            area = $('#' + id).children('.content');

        area.empty();

        // add input rows
        $.each(list, function(i, v) {
            smart_field_row_add(area, v, field.name, i, $(field).data('size'));
        });
    };

    /**
     * Register form errors, mark fields as invalid, dsplay the error below the input
     */
    function form_errors(tips)
    {
        $.each(tips, function() {
            var input = $('#' + this[0]).addClass('is-invalid');

            if (input.data('type') == 'list') {
                input.data('error-msg', this[2]);
                $('#' + this[0] + '_list > .invalid-feedback').text(this[2]);
                return;
            }

            input.after($('<span class="invalid-feedback">').text(this[2]));
        });
    };

    /**
     * Show/hide the navigation list
     */
    function switch_nav_list(obj)
    {
        var records, height, speed = 250,
            button = $('a', obj),
            navlist = $(obj).next();

        if (!navlist.height()) {
            records = $('tr,li', navlist).filter(function() { return this.style.display != 'none'; });
            height = $(records[0]).height() || 50;

            navlist.animate({height: (Math.min(5, records.length) * height + 1) + 'px'}, speed);
            button.addClass('collapse').removeClass('expand');
            $(obj).addClass('expanded');
        }
        else {
            navlist.animate({height: '0'}, speed);
            button.addClass('expand').removeClass('collapse');
            $(obj).removeClass('expanded');
        }
    };

    /**
     * Create a splitter (resizing) element on a layout column
     */
    function splitter_init(node)
    {
        // Use id of the list element, if exists, as a part of the key, instead of action.column-id
        // This way e.g. the sidebar in Settings is always the same width for all Settings' pages
        var list_id = node.find('.scroller .listing').first().attr('id'),
            key = rcmail.env.task + '.' + (list_id || (rcmail.env.action + '.' + node.attr('id'))),
            pos = get_pref(key),
            reverted = node.is('.sidebar-right'),
            set_width = function(width) {
                node.css({
                    width: Math.max(100, width),
                    // reset default properties
                    // 'min-width': 100,
                    flex: 'none'
                });
            };

        if (!node[reverted ? 'prev' : 'next']().length) {
            return;
        }

        $('<div class="column-resizer">')
            .appendTo(node)
            .on('mousedown', function(e) {
                var ts, splitter = $(this), offset = node.position().left;

                // Makes col-resize cursor follow the mouse pointer on dragging
                // and fixes issues related to iframes
                splitter.width(10000).css(reverted ? 'left' : 'right',  -5000);

                // Disable selection on document while dragging
                // It can happen when you move mouse out of window, on top
                document.body.style.userSelect = 'none';

                // Start listening to mousemove events
                $(document)
                    .on('mousemove.resizer', function(e) {
                        // Use of timeouts makes the move more smooth in Chrome
                        clearTimeout(ts);
                        ts = setTimeout(function() {
                            // For left-side-splitter we need the current offset
                            if (reverted) {
                                offset = node.position().left;
                            }
                            var cursor_position = rcube_event.get_mouse_pos(e).x,
                                width = reverted ? node.width() + (offset - cursor_position) : cursor_position - offset;

                            set_width(width);
                        }, 5);
                    })
                    .on('mouseup.resizer', function() {
                        // Remove registered events
                        $(document).off('.resizer');
                        $('iframe').off('.resizer');
                        document.body.style.userSelect = 'auto';

                        // Set back the splitter width to normal
                        splitter.width(6).css(reverted ? 'left' : 'right', -3);

                        // Save the current position (width)
                        save_pref(key, node.width());
                    });
            });

        if (pos) {
            set_width(pos);
        }
    };

    /**
     * Wrapper for rcmail.open_window to intercept window opening
     * and display a dialog with an iframe instead of a real window.
     */
    function window_open(url)
    {
        // Use 4th argument to bypass the dialog-mode e.g. for external windows
        if (!is_mobile() || arguments[3] === true) {
            return env.open_window.apply(rcmail, arguments);
        }

        // _extwin=1, _framed=1 are required to display attachment preview
        // layout properly and make mobile menus working
        url = rcmail.add_url(url, '_framed', 1);
        url = rcmail.add_url(url, '_extwin', 1);

        var label, title = '',
            props = {cancel_button: 'close', width: 768, height: 768},
            frame = $('<iframe>').attr({id: 'windowframe', src: url});

        if (/_action=([a-z_]+)/.test(url) && (label = rcmail.labels[RegExp.$1])) {
            title = label;
        }

        if (/_frame=1/.test(url)) {
            props.dialogClass = 'no-titlebar';
        }

        rcmail.simple_dialog(frame, title, null, props);

        return true;
    };

    /**
     * Get layout modes. In frame mode returns the parent layout modes.
     */
    function layout_metadata()
    {
        if (is_framed) {
            var doc = $(parent.document.documentElement);

            return {
                mode: doc[0].className.match(/layout-([a-z]+)/) ? RegExp.$1 : mode,
                touch: doc.is('.touch'),
            };
        }

        return {mode: mode, touch: touch};
    };

    /**
     * Returns true if the layout is in 'small' or 'phone' mode
     */
    function is_mobile()
    {
        var meta = layout_metadata();

        return meta.mode == 'phone' || meta.mode == 'small';
    };

    /**
     * Returns true if the layout is in 'touch' mode
     */
    function is_touch()
    {
        var meta = layout_metadata();

        return meta.touch;
    };

    /**
     * Get preference stored in browser
     */
    function get_pref(key)
    {
        if (!prefs) {
            prefs = rcmail.local_storage_get_item('prefs.elastic', {});
        }

        // fall-back to cookies
        if (prefs[key] == null) {
            var cookie = rcmail.get_cookie(key);
            if (cookie != null) {
                prefs[key] = cookie;

                // copy value to local storage and remove cookie (if localStorage is supported)
                if (rcmail.local_storage_set_item('prefs.elastic', prefs)) {
                    rcmail.set_cookie(key, cookie, new Date());  // expire cookie
                }
            }
        }

        return prefs[key];
    };

    /**
     * Saves preference value to browser storage
     */
    function save_pref(key, val)
    {
        prefs[key] = val;

        // write prefs to local storage (if supported)
        if (!rcmail.local_storage_set_item('prefs.elastic', prefs)) {
            // store value in cookie
            var exp = new Date();
            exp.setYear(exp.getFullYear() + 1);
            rcmail.set_cookie(key, val, exp);
        }
    };
}

if (window.rcmail) {
    /**
     * Elastic version of show_menu as we don't need e.g. menu positioning from core
     * TODO: keyboard navigation in menus
     */
    rcmail.show_menu = function(prop, show, event)
    {
        var name = typeof prop == 'object' ? prop.menu : prop,
            obj = $('#' + name);

        if (typeof prop == 'string') {
            prop = {menu: name};
        }

        // just delegate the action to rcube_elastic_ui
        return rcmail.triggerEvent(show === false ? 'menu-close' : 'menu-open', {name: name, obj: obj, props: prop, originalEvent: event});
    }

    /**
     * Elastic version of hide_menu as we don't need e.g. menus stack handling
     */
    rcmail.hide_menu = function(name, event)
    {
        // delegate to rcube_elastic_ui
        return rcmail.triggerEvent('menu-close', {name: name, props: {menu: name}, originalEvent: event});
    }
}
else {
    // rcmail does not exists e.g. on the error template inside a frame
    // we fake the engine a little
    var rcmail = parent.rcmail,
        rcube_webmail = parent.rcube_webmail,
        bw = {};
}

var UI = new rcube_elastic_ui();

// Improve non-inline datepickers
if ($ && $.datepicker) {
    var __newInst = $.datepicker._newInst;

    $.extend($.datepicker, {
        _newInst: function(target, inline) {
            var inst = __newInst.call(this, target, inline);

            if (!inst.inline) {
                UI.datepicker_init(inst.dpDiv);
            }

            return inst;
        }
    });
}
