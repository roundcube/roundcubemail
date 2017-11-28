/**
 * Roundcube webmail functions for the Elastic skin
 *
 * Copyright (c) 2017, The Roundcube Dev Team
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
    var ref = this,
        mode = 'normal', // one of: large, normal, small, phone
        touch = false,
        is_framed = rcmail.is_framed(),
        env = {
            config: {
                standard_windows: rcmail.env.standard_windows,
                message_extwin: rcmail.env.message_extwin,
                compose_extwin: rcmail.env.compose_extwin,
                help_open_extwin: rcmail.env.help_open_extwin
            },
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
            menu: $('#layout > .menu'),
            sidebar: $('#layout > .sidebar'),
            list: $('#layout > .list'),
            content: $('#layout > .content'),
        },
        buttons = {
            menu: $('a.menu-button'),
            back_sidebar: $('a.back-sidebar-button'),
            back_list: $('a.back-list-button'),
            back_content: $('a.back-content-button'),
        };


    // Public methods
    this.register_content_buttons = register_content_buttons;
    this.menu_hide = menu_hide;
    this.menu_toggle = menu_toggle;
    this.popup_init = popup_init;
    this.about_dialog = about_dialog;
    this.headers_dialog = headers_dialog;
    this.headers_show = headers_show;
    this.spellmenu = spellmenu;
    this.searchmenu = searchmenu;
    this.headersmenu = headersmenu;
    this.attachmentmenu = attachmentmenu;
    this.mailtomenu = mailtomenu;
    this.show_list = show_list;
    this.show_sidebar = show_sidebar;
    this.smart_field_init = smart_field_init;
    this.smart_field_reset = smart_field_reset;
    this.form_errors = form_errors;
    this.switch_nav_list = switch_nav_list;
    this.searchbar_init = searchbar_init;
    this.pretty_checkbox = pretty_checkbox;


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

        // Initialize search forms (in list headers)
        $('.header > .searchbar').each(function() { searchbar_init(this); });
        $('.header > .searchfilterbar').each(function() { searchfilterbar_init(this); });

        // Intercept jQuery-UI dialogs to re-style them
        if ($.ui) {
            $.widget('ui.dialog', $.ui.dialog, {
                open: function() {
                    this._super();
                    dialog_open(this);
                    return this;
                }
            });
        }

        // menu/sidebar/list button
        buttons.menu.on('click', function() { show_menu(); return false; });
        buttons.back_sidebar.on('click', function() { show_sidebar(); return false; });
        buttons.back_list.on('click', function() { show_list(); return false; });
        buttons.back_content.on('click', function() { show_content(true); return false; });

        $('body').on('click', function() { if (mode == 'phone') layout.menu.addClass('hidden'); });

        // Set content frame title in parent window (exclude ext-windows and dialog frames)
        if (is_framed && !rcmail.env.extwin && !parent.$('.ui-dialog:visible').length) {
            if (title = $('h1.voice:first').text()) {
                parent.$('#layout > .content > .header > .header-title:not(.constant)').text(title);
            }
        }
        else if (!is_framed) {
            title = $('.boxtitle:first', layout.content).detach().text();

            if (!title) {
                title = $('h1.voice:first').text();
            }

            if (title) {
                $('.header > .header-title', layout.content).text(title);
            }
        }

        // Add content frame toolbar in the footer, for content buttons and navigation
        if (!is_framed && layout.content.length && !$(layout.content).is('.no-navbar')
            && !$(layout.content).children('.frame-content').length
        ) {
            env.frame_nav = $('<div class="footer toolbar content-frame-navigation hide-nav-buttons">')
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
        $('.formbuttons').children().each(function() {
            var target = $(this);

            // skip non-content buttons
            if (!is_framed && !target.parents('.content').length) {
                return;
            }

            if (target.is('.cancel')) {
                target.addClass('hidden');
                return;
            }

            content_buttons.push(create_cloned_button(target));
        });

        (is_framed ? parent.UI : ref).register_content_buttons(content_buttons);

        $('[data-recipient-input]').each(function() { recipient_input(this); });
        $('.image-upload').each(function() { image_upload_input(this); });

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

        // Add HTML/Plain tabs (switch) on top of textarea with TinyMCE editor
        $('textarea[data-html-editor]').each(function() { html_editor_init(this); });

        $('#dragmessage-menu,#dragcontact-menu').each(function() {
            rcmail.gui_object('dragmenu', this.id);
        });

        // Taskmenu items added by plugins do not use elastic classes (e.g help plugin)
        // it's for larry skin compat. We'll assign 'button', 'selected' and icon-specific class.
        $('#taskmenu > a').each(function() {
            if (/button-([a-z]+)/.test(this.className)) {
                var data, name = RegExp.$1,
                    button = find_button(this.id);

                if (button && (data = button.data)) {
                    if (data.sel) {
                        data.sel += ' button ' + name;
                        data.sel = data.sel.replace('button-selected', 'selected');
                    }

                    if (data.act) {
                        data.act += ' button ' + name;
                    }

                    rcmail.buttons[button.command][button.index] = data;
                    rcmail.init_button(button.command, data);
                }

                $(this).addClass('button ' + name);
                $('.button-inner', this).addClass('inner');
            }
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
        $('a[data-hidden],button[data-hidden]').each(function() {
            var parent = $(this).parent('li'),
                sizes = $(this).data('hidden').split(',');

            $(parent.length ? parent : this).addClass('hidden-' + sizes.join(' hidden-'));
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
    function create_cloned_button(target, is_ext)
    {
        var button = $('<a>'),
            target_id = target.attr('id'),
            button_id = target_id + '-clone',
            btn_class = target[0].className;

        btn_class = $.trim(btn_class.replace('btn-primary', 'primary').replace(/(btn[a-z-]*|button|disabled)/g, ''))
        btn_class += ' button disabled';

        button.attr({'onclick': '', id: button_id, href: '#', 'class': btn_class})
            .append($('<span class="inner">').text(target.text()))
            .on('click', function(e) { target.click(); });

        if (is_framed) {
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
    };

    /**
     * rcmail 'init' event handler
     */
    function init()
    {
        // Enable checkbox selection on list widgets
        $('table[data-list]').each(function() {
            var list = $(this).data('list');
            if (rcmail[list] && rcmail[list].multiselect) {
                rcmail[list].checkbox_selection = true;
            }
        });

        // https://github.com/roundcube/elastic/issues/45
        // Draggable blocks scrolling on touch devices, we'll disable it there
        if (is_touch()) {
            $('[data-list]', layout.list).each(function() {
                var list = $(this).data('list');
                if (rcmail[list] && typeof rcmail[list].draggable == 'function') {
                    rcmail[list].draggable('destroy');
                }
            });
        }

        // Add menu link for each attachment
        if (rcmail.env.action != 'print') {
            $('#attachment-list > li').each(function() {
                attachmentmenu_append(this);
            });
        }

        rcmail.addEventListener('fileappended', function(e) { if (e.attachment.complete) attachmentmenu_append(e.item); })
            .addEventListener('managesieve.insertrow', function(o) { bootstrap_style(o.obj); });

        rcmail.init_pagejumper('.pagenav > input');

        if (rcmail.task == 'mail') {
            // In compose window we do not provide "Back' button, instead
            // we modify the Mail button in the task menu to act like it (i.e. calls 'list' command)
            if (rcmail.env.action == 'compose' && !rcmail.env.extwin) {
                $('a.button.mail', layout.menu).attr('onclick', "return rcmail.command('list','',this,event)");
            }

            // Append contact menu to all mailto: links
            if (rcmail.env.action == 'preview' || rcmail.env.action == 'show') {
                $('a').filter('[href^="mailto:"]').each(function() {
                    mailtomenu_append(this);
                });
            }
        }

        rcmail.env.thread_padding = '1.5rem';

        // In devel mode we have to wait until all styles are aplied by less
        if (rcmail.env.devel_mode) {
            setTimeout(resize, 1000);
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

        $('input.button,button', context).not('.btn').addClass('btn').not('.btn-primary,.primary,.mainaction').addClass('btn-secondary');
        $('input.button.mainaction,button.primary,button.mainaction', context).addClass('btn-primary');
        $('button.btn.delete,button.btn.discard', context).addClass('btn-danger');

        $.each(['warning', 'error', 'information'], function() {
            var type = this;
            $('.box' + type, context).each(function() {
                message_displayed({object: this, type: type});
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
        $('input:not(.button,[type=file],[type=radio],[type=checkbox]),select,textarea', $('.propform', context)).addClass('form-control');
        $('[type=checkbox]', $('.propform', context)).addClass('form-check-input');
        $('table.propform > tbody > tr', context).each(function() {
            var first, last, row = $(this),
                row_classes = ['form-group', 'row'],
                cells = row.children('td');

            if (cells.length == 2) {
                first = cells.first();
                last = cells.last();

                $('label', first).addClass('col-form-label');
                first.addClass('col-sm-4');
                last.addClass('col-sm-8');

                if (last.find('[type=checkbox]').length == 1 && !last.find('.proplist').length) {
                    row_classes.push('form-check');

                    if (last.find('a').length) {
                        row_classes.push('with-link');
                    }
                }
                else if (!last.find('input:not([type=hidden]),textarea,radio,select').length) {
                    last.addClass('form-control-plaintext');
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

        // Special input + anything entry
        $('td.input-group', context).each(function() {
            $(this).children(':not(:first)').addClass('input-group-addon');
        });

        // Other forms, e.g. Contact advanced search
        $('fieldset.propform:not(.groupped) div.row', context).each(function() {
            var has_input = $('input,select,textarea', this).addClass('form-control').length > 0;

            $(this).children().last().addClass('col-sm-8' + (!has_input ? ' form-control-plaintext' : ''));
            $(this).children().first().addClass('col-sm-4 col-form-label');
            $(this).addClass('form-group');
        });

        // Contact info/edit form
        $('fieldset.propform.groupped fieldset', context).each(function() {
            $('.row', this).each(function() {
                var label, has_input = $('input,select,textarea', this).addClass('form-control').length > 0,
                    items = $(this).children();

                if (items.length < 2) {
                    return;
                }

                items.first().addClass('input-group-addon').not('select').addClass('col-form-label');

                if (!has_input) {
                    items.last().addClass('form-control-plaintext');
                }

                $('.content', this).addClass('input-group-addon');
                $('a.deletebutton', this).addClass('input-group-addon icon delete');
                $(this).addClass('input-group');
            });
        });

        // Other forms, e.g. Insert response
        $('.propform > .prop.block:not(.row)', context).each(function() {
            $(this).addClass('form-group row').each(function() {
              $('label', this).addClass('col-form-label').wrap($('<div class="col-sm-4 col-form-label">'));
              $('input,select,textarea', this).addClass('form-control').wrap($('<div class="col-sm-8">'));
            });
        });

        $('td.rowbuttons > a', context).addClass('btn');

        // Testing Bootstrap Tabs on contact info/edit page
        // Tabs do not scale nicely on very small screen, so can be used
        // only with small number of tabs with short text labels
        $('form.tabbed,div.tabbed', context).each(function(idx, item) {
            var tabs = [], nav = $('<ul>').attr({'class': 'nav nav-tabs', role: 'tablist'});

            $(this).addClass('tab-content').children('fieldset').each(function(i, fieldset) {
                var tab, id = 'tab' + idx + '-' + i;

                $(fieldset).addClass('tab-pane').attr({id: id, role: 'tabpanel'});

                tab = $('<li>').addClass('nav-item').append(
                    $('<a>').addClass('nav-link')
                        .attr({role: 'tab', 'href': '#' + id})
                        .text($('legend:first', fieldset).text())
                        .click(function() {
                            $(this).tab('show');
                            // Returning false here prevents from strange scrolling issue
                            // when the form is in an iframe, e.g. contact edit form
                            return false;
                        })
                );

                $('legend:first', fieldset).hide();
                tabs.push(tab);
            });

            // create the navigation bar
            nav.append(tabs).insertBefore(item);
            // activate the first tab
            $('a.nav-link:first', nav).click();
        });

        // Make tables pretier
        $('table:not(.propform):not(.listing)', context)
            .filter(function() {
                // exclude direct propform children and external content
                return !$(this).parent().is('.propform')
                    && !$(this).parents('.message-htmlpart,.message-partheaders').length;
            })
            .each(function() {
                // TODO: Consider implementing automatic setting of table-responsive on window resize
                $(this).addClass('table table-responsive-sm').find('thead').addClass('thead-default');
            });

        $('.toolbarmenu select', context).addClass('form-control');
        if (context != document) {
            $('select,textarea,input:not([type="checkbox"],[type="radio"])', context).addClass('form-control');
        }

        // The same for some other checkboxes
        // We do this here, not in setup() because we want to cover dialogs
        $('.propform input[type=checkbox], .form-check > input, .popupmenu.form input[type=checkbox], .toolbarmenu input[type=checkbox]', context)
            .each(function() { pretty_checkbox(this); });

        // Also when we add action-row of the form, e.g. Managesieve plugin adds them after the page is ready
        if ($(context).is('.actionrow')) {
            $('input[type=checkbox]', context).each(function() { pretty_checkbox(this); });
        }

        // Make message-objects alerts pretty (the same as UI alerts)
        $('#message-objects', context).children().each(function() {
            alert_style(this, $(this).addClass('boxwarning').attr('class').split(/\s/)[0]);
            $('a', this).addClass('btn btn-primary');
        });

        // Style calendar widget (we use setTimeout() because there's no widget event we could bind to)
        $('input.datepicker', context).focus(function() {
            setTimeout(function() { bootstrap_style($('.ui-datepicker')); }, 5);
        });

        // Form validation errors (managesieve plugin)
        $('.error', context).addClass('is-invalid');

        // Make logon form prettier
        if (rcmail.env.task == 'login' && context == document) {
            $('#login-form table tr').each(function() {
                var input = $('input,select', this),
                    label = $('label', this),
                    icon_name = input.data('icon'),
                    icon = $('<i>').attr('class', 'input-group-addon icon ' + input.attr('name').replace('_', ''));

                if (icon_name) {
                    icon.addClass(icon_name);
                }

                $(this).addClass('form-group row');
                label.parent().css('display', 'none');
                input.addClass('form-control')
                    .attr('placeholder', label.text())
                    .before(icon)
                    .parent().addClass('input-group');
            });
        }
    };

    /**
     * Initializes popup menus
     */
    function dropdowns_init()
    {
        $('[data-popup]').each(function() { popup_init(this); });

        // close popups on click in an iframe on the page
        var close_all_popups = function(e) {
            $('.popover-body:visible').each(function() {
                var button = $(this).children('*:first').data('button');
                if (e.target != button && typeof button !== 'string') {
                    $(button).popover('hide');
                }
            });
        };

        $(document).on('click', popups_close);
        rcube_webmail.set_iframe_events({mousedown: popups_close});
    };

    /**
     * Init content frame
     */
    function content_frame_init()
    {
        var last_selected = env.last_selected,
            title_reset = function() {
                var title = $('h1.voice').text() || $('title').text() || '';
                $('.header > .header-title', layout.content).text(title);
            };

        // when loading content-frame in small-screen mode display it
        layout.content.find('iframe').on('load', function(e) {
            var href = '', show = true;
            try {
                href = e.target.contentWindow.location.href;
                show = !href.endsWith(rcmail.env.blankpage);
                // Reset title back to the default
                $(e.target.contentWindow).on('unload', title_reset);
            }
            catch(e) { /* ignore */ }

            content_frame_navigation(href, e);

            if (show && !layout.content.is(':visible')) {
                env.last_selected = layout.content[0];
                screen_resize();
            }
            else if (!show) {
                if (env.last_selected != last_selected && !env.content_lock) {
                    env.last_selected = last_selected;
                    screen_resize();
                }
                title_reset();
            }

            env.content_lock = false;
        });

        // display the list widget after 'list' and 'listgroup' commands
        // @TODO: plugins should be able to do the same
        var list_handler = function(e) {
            if (mode != 'large' && !env.content_lock) {
                if (rcmail.env.task == 'addressbook' || (rcmail.env.task == 'mail' && !rcmail.env.action)) {
                    show_list();
                }
            }

            env.content_lock = false;

            // display current folder name in list header
            if (rcmail.env.task == 'mail' && !rcmail.env.action) {
                var name = $.type(e) == 'string' ? e : rcmail.env.mailbox,
                    folder = rcmail.env.mailboxes[name];

                $('.header > .header-title', layout.list).text(folder ? folder.name : '');
            }
        };

        rcmail
            .addEventListener('afterlist', list_handler)
            .addEventListener('afterlistgroup', list_handler)
            .addEventListener('afterlistsearch', list_handler);
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

        var uid, list, _list = $('[data-list]', layout.list).data('list');

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
            else if (list.get_node && list.get_node(uid).collapsed) {
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
    };

    /**
     * Handler for some Roundcube core popups
     */
    function rcmail_popup_init(o)
    {
        // Add some common styling to the autocomplete/googiespell popups
        $('table,ul', o.obj).addClass('toolbarmenu listing iconized');
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

            $('table,button', o.obj).click(function(e) {
                if (!$(e.target).is('input')) {
                    $('.popover-overlay').remove();
                }
            });
        }
    };

    function enable_command_handler(args)
    {
        if (is_framed) {
            $.each(frame_buttons, function(i, button) {
                if (args.command == button.command) {
                    parent.$('#' + button.button_id)[args.status ? 'removeClass' : 'addClass']('disabled');
                }
            });
        }
    };

    /**
     * Window resize handler
     * Does layout reflows e.g. on screen orientation change
     */
    function resize()
    {
        var size, mobile, width = $(window).width();

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
        screen_resize();
        screen_resize_html();
//        display_screen_size(); // debug info

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

    // for development only (to be removed)
    function display_screen_size()
    {
        if (is_framed) {
            return;
        }

        var div = $('#screen-size'), win = $(window);
        if (!div.length) {
            div = $('<div>').attr({
                id: 'screen-size',
                style: 'position:absolute;display:block;right:0;z-index:100;'
                    + (is_framed ? 'top:0;' : 'bottom:0;')
                    + 'opacity:0.5;color:white;background-color:black;white-space:nowrap'
            }).appendTo(document.body);
        }

        div.text(win.width() + ' x ' + win.height() + ' (' + mode + ')');
    };

    function screen_resize()
    {
        if (!layout.sidebar.length && !layout.list.length) {
            return;
        }

        switch (mode) {
            case 'phone': screen_resize_phone(); break;
            case 'small': screen_resize_small(); break;
            case 'normal': screen_resize_normal(); break;
            case 'large': screen_resize_large(); break;
        }

        screen_resize_headers();
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

                if ($(this).is('.searchbar')) {
                    padding += this.offsetWidth;
                }
                else {
                    sizes[title ? 'right' : 'left'] += this.offsetWidth;
                }
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

        layout.menu.addClass('hidden');
    };

    function screen_resize_small()
    {
        screen_resize_small_all();

        layout.menu.removeClass('hidden');
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
        layout.menu.removeClass('hidden');

        screen_resize_small_none();
    };

    function screen_resize_large()
    {
        $.each(layout, function(name, item) { item.removeClass('hidden'); });

        screen_resize_small_none();
    };

    function screen_resize_small_all()
    {
        var show, got_content = false;

        if (layout.content.length) {
            show = got_content = layout.content.is(env.last_selected);
            layout.content[show ? 'removeClass' : 'addClass']('hidden');
        }

        if (layout.list.length) {
            show = !got_content && layout.list.is(env.last_selected);
            layout.list[show ? 'removeClass' : 'addClass']('hidden');
        }

        if (layout.sidebar.length) {
            show = !got_content && (layout.sidebar.is(env.last_selected) || !layout.list.length);
            layout.sidebar[show ? 'removeClass' : 'addClass']('hidden');
        }

        if (got_content) {
            buttons.back_list.show();
        }

        $('.header > ul.toolbar', layout.content).addClass('popupmenu');
    };

    function screen_resize_small_none()
    {
        buttons.back_list.hide();
        $('ul.toolbar.popupmenu').removeClass('popupmenu');
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
    function show_menu()
    {
        var show = true;

        if (mode == 'phone') {
            show = layout.menu.is(':visible') ? false : true;
        }

        layout.menu[show ? 'removeClass' : 'addClass']('hidden');
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
/*
        $('a', p.object).addClass('alert-link');

        // show a popup dialog on errors
        if (p.type == 'error' && rcmail.env.task != 'login') {
            // hide original message object, we don't want both
            rcmail.hide_message(p.object);
        }
*/
    };

    /**
     * Applies some styling and icon to an alert object
     */
    function alert_style(object, type, wrap)
    {
        var tmp, classes = 'ui alert',
            map = {
                information: 'alert-info',
                confirmation: 'alert-success',
                notice: 'alert-warning',
                warning: 'alert-warning',
                error: 'alert-danger',
                loading: 'alert-info loading',
                vcardattachment: 'alert-info' /* vcard_attachments plugin */
            };

        if (wrap) {
            // we need the content to be non-text node for best alignment
            tmp = $(object).html();
            $(object).html($('<span>').html(tmp));
        }

        if (tmp = map[type]) {
            classes += ' ' + tmp;
            $('<i>').attr('class', 'icon').prependTo(object);
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
        var parent_class = 'with-search',
            input = $('input', bar).addClass('form-control'),
            button = $('a.button.search', bar),
            form = $('form', bar),
            is_search_pending = function() {
                // TODO: This have to be improved to detect real searching state
                //       There are cases when search is active but the input is empty
                return input.val();
            },
            hide_func = function(event, focus) {
                if (button.is(':visible')) {
                    return;
                }
                form.hide();
                // width:auto fixes search button position in Chrome
                $(bar).css('left', 'auto')[is_search_pending() ? 'addClass' : 'removeClass']('active');
                button.css('display', 'block');
                if (focus && rcube_event.is_keyboard(event)) {
                    button.focus();
                }
            };

        if (!$(bar).next().length) {
            parent_class += ' no-toolbar';
        }

        $(bar).parent().addClass(parent_class);

        if (is_search_pending()) {
            $(bar).addClass('active');
        }

        // make the input pretty
        form.addClass('input-group')
            .prepend($('<i class="input-group-addon icon search">'))
            .append($('a.options', bar).detach().removeClass('button').addClass('icon input-group-addon'))
            .append($('a.reset', bar).detach().removeClass('button').addClass('icon input-group-addon'))
            .append($('<a class="icon cancel input-group-addon" href="#">'));

        // Display search form
        button.on('click', function() {
            $(bar).css('left', 0);
            form.css('display', 'flex');
            button.hide();
            input.focus();
        });

        // Search reset action
        $('a.reset', bar).on('click', function(e) {
            // for treelist widget's search setting val and keyup.treelist is needed
            // in normal search form reset-search command will do the trick
            // TODO: This calls for some generalization, what about two searchboxes on a page?
            input.val('').change().trigger('keyup.treelist', {keyCode: 27});

            // we have to de-activate filter
            // TODO: Probably that should not reset filter, but that's current Roundcube bahavior
            $(bar).prev('.searchfilterbar').removeClass('active');

            hide_func(e, true);
        });

        $('a.cancel', bar).attr('title', rcmail.gettext('close')).on('click', function(e) { hide_func(e, true); });

        // These will hide the form, but not reset it
        rcube_webmail.set_iframe_events({mousedown: hide_func});
        $('body').on('mousedown', function(e) {
            // close searchbar on mousedown anywhere, but not inside the searchbar or dialogs
            if (!$(e.target).parents('.popover,.searchbar').length) {
                hide_func(e);
            }
        });

        rcmail.addEventListener('init', function() { if (input.val()) $(bar).addClass('active'); });
    };

    /**
     * Initializes searchfilterbar widget
     */
    function searchfilterbar_init(bar)
    {
        bar = $('<div class="searchfilterbar searchbar toolbar">')
            .insertAfter(bar)
            .append($(bar).detach())
            .append($('<a class="button icon filter">').attr('title', rcmail.gettext('filter')));

        $('select', bar).wrap($('<div class="input-group">'))
            .parent().prepend($('<i class="input-group-addon icon filter">'))
                .append($('<a class="icon cancel input-group-addon">')
                    .attr({title: rcmail.gettext('close'), href: '#'}));

        var select = $('select', bar),
            button = $('a.button.filter', bar),
            form = $('.input-group', bar),
            is_filter_enabled = function() {
                var value = select.val();
                return value && value != 'ALL';
            },
            hide_func = function(event, focus) {
                if (button.is(':visible')) {
                    return;
                }
                form.hide();
                bar.css('left', 'auto')[is_filter_enabled() ? 'addClass' : 'removeClass']('active');
                button.css('display', 'block');
                if (focus && rcube_event.is_keyboard(event)) {
                    button.focus();
                }
            };

        bar.parent().addClass('with-filter');

        if (is_filter_enabled()) {
            bar.addClass('active');
        }

        select.removeClass('hidden searchfilterbar').addClass('form-control')
            .on('change', function(e) {
                hide_func(e, true);
                // It may be called when the form is hidden... update status
                if (is_filter_enabled()) {
                    bar.removeClass('active');
                }
            });

        // Display filter selection (with animation effect)
        button.on('click', function() {
            $(bar).css('left', 0);
            form.css('display', 'flex');
            button.hide();
            select.focus();
        });

        // Filter close button
        $('a.cancel', bar).on('click', function(e) { hide_func(e, true); });

        // These will hide the form, but not reset it
        rcube_webmail.set_iframe_events({mousedown: hide_func});
        $('body').on('mousedown', function(e) {
            // close searchbar on mousedown anywhere, but not inside the searchbar or dialogs
            if (!$(e.target).parents('.searchfilterbar').length) {
                hide_func(e);
            }
        });
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

        // TODO: a way to inject buttons to the menu from content iframe
        //       or automatically add all buttons except Save and Cancel
        //       (example QR Code button in contact frame)

        var items = [];

        // convert toolbar to a popup list
        $('.header > .toolbar', layout.content).each(function() {
            var toolbar = $(this);

            toolbar.children().each(function() {
                var item = $('<li role="menuitem">'),
                    button = $(this).detach();

                // Remove empty text nodes that break alignment of text of the menu item
                button.contents().filter(function() { if (this.nodeType == 3 && !$.trim(this.nodeValue).length) $(this).remove(); });

                if (button.is('.spacer')) {
                    item.addClass('spacer');
                }
                else {
                    item.append(button);
                }

                items.push(item);
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

        // append the new toolbar and menu button
        if (items.length) {
            var container = layout.content.children('.header'),
                menu_attrs = {'class': 'toolbar popupmenu listing iconized', id: 'toolbar-menu'},
                menu_button = $('<a class="button icon toolbar-menu-button" href="#menu">')
                    .attr({'data-popup': 'toolbar-menu'});

            container
                // TODO: copy original toolbar attributes (class, role, aria-*)
                .append($('<ul>').attr(menu_attrs).data('popup-parent', container).append(items))
                .append(menu_button);

            if (layout.list.length) {
                // bind toolbar menu with the menu button in the list header
                $('a.toolbar-menu-button', layout.list).click(function(e) {
                    e.stopPropagation();
                    menu_button.click();
                });
            }
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
                        .find('li > a, li.checkbox > label').attr('onclick', '').off('click').on('click', function(e) {
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
                    popup.css('max-height', Math.min(510, $(window).height() - 30));
                }
            })
            .on('shown.bs.popover', function(event) {
                var mobile = is_mobile();

                level = $(item).data('level') || 1;

                // Set popup Back/Close title
                if (mobile) {
                    var label = level > 1 ? 'back' : 'close',
                        title = rcmail.gettext(label),
                        class_name = 'button icon ' + (label == 'back' ? 'back' : 'cancel');

                    $('#' + $(item).attr('aria-describedby') + ' > .popover-header').empty()
                        .append($('<a>').attr('class', class_name).text(title))
                        .off('click').on('click', function(e) {
                            $(item).popover('hide');
                            if (level > 1) {
                                e.stopPropagation();
                            }
                        });
                }

                // Hide other menus on the same level
                $.each(menus, function(id, prop) {
                    if ($(prop.target).data('level') == level && id != popup_id) {
                        menu_hide(id);
                    }
                });

                if (popup_id && menus[popup_id]) {
                    menus[popup_id].transitioning = false;
                }

                // add overlay element for phone layout
                if (mobile && !$('.popover-overlay').length) {
                    $('<div>').attr('class', 'popover-overlay')
                        .appendTo('body')
                        .click(function() { $(this).remove(); });
                }
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
                        popup.appendTo(popup.data('popup-parent') || document.body);
                }

                // close orphaned popovers, for some reason there are sometimes such dummy elements left
                $('.popover-body:empty').each(function() { $(this).parent().remove(); });

                if (popup_id && menus[popup_id]) {
                    delete menus[popup_id];
                }
            })
            .on('keypress', function(event) {
                // Close the popup on ESC key
                if (event.originalEvent.keyCode == 27) {
                    $(item).popover('hide');
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
                content = $('ul:first', p.obj),
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
            else if (content.hasClass('toolbarmenu')) {
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
        var target;

        if (menus[name]) {
            target = menus[name].target;
        }
        else {
            target = $('#' + name).data('button');

            // catch cases as 'forwardmenu' where menu suffix has no hyphen
            if (!target && name.match(/(?!-)menu$/)) {
                target = $('#' + name.substr(0, name.length - 4) + '-menu').data('button');
            }
        }

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
     * Messages list options dialog
     */
    function menu_messagelist(p)
    {
        var content = $('#listoptions-menu'),
            width = content.width() + 25,
            dialog = content.clone();

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
            open: function(e) {
                setTimeout(function() { dialog.find('select').first().focus(); }, 100);
            },
            minWidth: 400,
            width: width
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

        // TODO: 'Get Support' link/button
        if (support_link.length && (support_url = support_link.attr('href'))) {
            support_button = support_link.html();
            support_func = function(e) { support_url.indexOf('mailto:') < 0 ? window.open(support_url) : location.href = support_url };
        }

        rcmail.simple_dialog(dialog, $(elem).text(), support_func, {
            button: support_button,
            cancel_button: 'close',
            width: 600,
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
        var props = {_uid: rcmail.env.uid, _mbox: rcmail.env.mailbox},
            dialog = $('<iframe>').attr({id: 'headersframe', src: rcmail.url('headers', props)});

        rcmail.simple_dialog(dialog, rcmail.gettext('arialabelmessageheaders'), null, {
            cancel_button: 'close',
            width: 600,
            height: 400
        });
    };

    /**
     * Search options menu popup
     */
    function searchmenu(obj)
    {
        var n, all,
            list = $('input[name="s_mods[]"]', obj),
            scope_list = $('input[name="s_scope"]', obj),
            mbox = rcmail.env.mailbox,
            mods = rcmail.env.search_mods,
            scope = rcmail.env.search_scope || 'base';

        if (!$(obj).data('initialized')) {
            list.on('click', function() { set_searchmod(this, obj); });
            scope_list.on('click', function() { rcmail.set_searchscope(this.value); });
            $(obj).data('initialized', true);
        }

        if (rcmail.env.search_mods) {
            if (rcmail.env.task == 'mail') {
                if (scope == 'all') {
                    mbox = '*';
                }

                mods = mods[mbox] ? mods[mbox] : mods['*'];
                all = 'text';
                scope_list.prop('checked', false).filter('#s_scope_' + scope).prop('checked', true);
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
                    $('#s_mod_' + n, obj).prop('checked', true);
                }
            }
        }
    };

    function set_searchmod(elem, menu)
    {
        var all, m, task = rcmail.env.task,
            mods = rcmail.env.search_mods,
            mbox = rcmail.env.mailbox,
            scope = $('input[name="s_scope"]:checked', menu).val();

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
        }
        else { //addressbook
            m = mods;
            all = '*';
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
            ul = $('<ul class="toolbarmenu selectable listing iconized" role="menu">');

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
     * Attachment menu
     */
    function attachmentmenu(obj, button, event)
    {
        var id = $(button).parent().attr('id').replace(/^attach/, '');

        $.each(['open', 'download', 'rename'], function() {
            var action = this;
            $('#attachmenu' + action, obj).off('click').attr('onclick', '').click(function(e) {
                rcmail.command(action + '-attachment', id, this, e.originalEvent);
            });
        });

        // call menu-open so core can set state of menu commands
        rcmail.command('menu-open', {menu: 'attachmentmenu', id: id}, obj, event);
    };

    /**
     * Appends drop-icon to attachments list item (to invoke attachment menu)
     */
    function attachmentmenu_append(item)
    {
        item = $(item);

        if (!item.children('.drop').length) {
            var label = rcmail.gettext('options');
            var button = $('<a>')
                .attr({
                    href: '#',
                    tabindex: 0,
                    title: label,
                    'class': 'button icon dropdown skip-content'
                })
                .on('click', function(e) {
                    attachmentmenu($('#attachmentmenu'), button, e);
                })
                .append($('<span>').attr('class', 'inner').text(label))
                .appendTo(item);
        }
    };

    /**
     * Mailto menu
     */
    function mailtomenu(obj, button, event)
    {
        var mailto = $(button).attr('href').replace(/^mailto:/, '');

        if (mailto.indexOf('@') < 0) {
            return true; // let the browser handle this
        }

        if (rcmail.env.has_writeable_addressbook) {
            $('.addressbook', obj).addClass('active')
                .off('click').on('click', function(e) {
                    var i, contact = mailto,
                        txt = $(button).filter('.rcmContactAddress').text();

                    contact = contact.split('?')[0].split(',')[0].replace(/(^<|>$)/g, '');

                    if (txt) {
                        txt = txt.replace('<' + contact + '>', '');
                        contact = '"' + $.trim(txt) + '" <' + contact + '>';
                    }

                    rcmail.command('add-contact', contact, this, e.originalEvent);
                });
        }

        $('.compose', obj).off('click').on('click', function(e) {
            rcmail.command('compose', mailto, this, e.originalEvent);
        });

        return rcmail.command('menu-open', {menu: 'mailto-menu', link: button}, button, event);
    };

    /**
     * Appends popup menu to mailto links
     */
    function mailtomenu_append(item)
    {
        $(item).attr('onclick', '').on('click', function(e) {
            return mailtomenu($('#mailto-menu'), item, e);
        });
    };

    /**
     * Headers menu in mail compose
     */
    function headersmenu(obj, button, event)
    {
        $('li > a', obj).each(function() {
            var target = '#compose_' + $(this).data('target');

            $(this)[$(target).is(':visible') ? 'removeClass' : 'addClass']('active')
                .off().on('click', function() {
                    $(target).removeClass('hidden').find('.recipient-input > input').focus();
                });
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
        element.attr('title', element.find('.count').attr('title'));

        if (p.table) {
            element.css('cursor', 'pointer').data('popup-pos', 'top')
                .off('click').on('click', function(e) {
                    rcmail.simple_dialog(p.table, 'quota', null, {cancel_button: 'close'});
                });
        }
    };

    /**
     * Replaces recipient input with content-editable element that uses "recipient boxes"
     */
    function recipient_input(obj)
    {
        var area, input, ac_props,
            apply_func = function() {
                // update the original input
                $(obj).val(area.text());
            },
            focus_func = function() {
                area.addClass('focus');
            },
            insert_recipient = function(name, email) {
                var recipient = $('<span class="recipient">'),
                    name_element = $('<span class="name">').html(recipient_input_name(name || email)),
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
                    .insertBefore(input);
            },
            update_func = function() {
                var text = input.val().replace(/[,;\s]+$/, ''),
                    recipients = recipient_input_parser(text);

                $.each(recipients, function() {
                    insert_recipient(this.name, this.email);
                });

                if (recipients.length) {
                    input.val('');
                    apply_func();
                    return true;
                }
            },
            parse_func = function(e) {
                // Note it can be also executed when autocomplete inserts a recipient
                update_func();

                if (e.type == 'blur') {
                    area.removeClass('focus');
                }
            },
            keydown_func = function(e) {
                // On Backspace remove the last recipient
                if (e.keyCode == 8 && !input.val().length) {
                    area.children('span.recipient:last').remove();
                    apply_func();
                    return false;
                }
                // Here we add a recipient box when the separator character (,;) was pressed
                else if (e.key == ',' || e.key == ';') {
                    if (update_func()) {
                        return false;
                    }
                }
            };

        // Create the content-editable div
        input = $('<input>').attr({type: 'text', tabindex: $(obj).attr('tabindex')})
            .on('paste change blur', parse_func)
            .on('keydown', keydown_func)
            .on('focus mousedown', focus_func);

        area = $('<div>').addClass('form-control recipient-input')
            .append(input)
            .on('click', function() { input.focus(); });

        // "Replace" the original input/textarea with the content-editable div
        // Note: we do not remove the original element, and we do not use
        // display: none, because we want to handle onfocus event
        // Note: tabindex:-1 to make Shift+TAB working on these widgets
        $(obj).css({position: 'absolute', opacity: 0, left: '-5000px', width: '10px'})
            .attr('tabindex', -1)
            .after(area)
            // some core code sometimes focuses or changes the original node
            // in such cases we wan't to parse it's value and apply changes
            // to the widget element
            .on('focus', function(e) { input.focus(); })
            .on('change', function(e) {
                $('span.recipient', area).remove();
                input.val(this.value).change();
            })
            // copy and parse the value already set
            .change();

        // this one line is here to fix border of Bootstrap's input-group,
        // input-group should not contain any hidden elements
        $(obj).detach().insertBefore(area.parent());

        if (rcmail.env.autocomplete_threads > 0) {
            ac_props = {
                threads: rcmail.env.autocomplete_threads,
                sources: rcmail.env.autocomplete_sources
            };
        }

        // Init autocompletion
        rcmail.init_address_input_events(input, ac_props);
    };

    /**
     * Parses recipient address input and extracts recipients from it
     */
    function recipient_input_parser(text)
    {
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
            }
        });

        return recipients;
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
     * Adds logic to the contact photo widget
     */
    function image_upload_input(obj)
    {
        var reset_button = $('<a>')
            .attr({'class': 'icon button delete', href: '#', })
            .click(function(e) { rcmail.command('delete-photo', '', this, e); return false; });

        $(obj).append(reset_button).click(function() { rcmail.upload_input('upload-form'); });

        $('img', obj).on('load', function() {
            // FIXME: will that work in IE?
            var state = (this.currentSrc || this.src).indexOf(rcmail.env.photo_placeholder) != -1;
            $(obj)[state ? 'removeClass' : 'addClass']('changed');
        });
    };

    /**
     * Displays loading... overlay for iframes
     */
    function iframe_loader(frame)
    {
        frame = $(frame);

        if (frame.length) {
            var loader = $('<div>').attr('class', 'iframe-loader')
                .append($('<div>').attr('class', 'spinner').text(rcmail.gettext('loading')));

            frame.on('load error', function() {
                    // wait some time to make sure the iframe stopped loading
                    setTimeout(function() { loader.remove(); }, 500);
                })
                .parent().append(loader);
        }
    };

    /**
     * Checkbox wrapper
     */
    function pretty_checkbox(checkbox)
    {
        var checkbox = $(checkbox),
            id = checkbox.attr('id');

        if (!id) {
            if (!env.icon_checkbox) env.icon_checkbox = 0;
            id = 'icochk' + (++env.icon_checkbox);
            checkbox.attr('id', id);
        }

        checkbox.addClass('icon-checkbox form-check-input').after(
            $('<label>').attr({'for': id, title: checkbox.attr('title') || ''})
        );
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

        $('a', tabs).attr('tabindex', editor.attr('tabindex'))
            .on('click', function(e) {
                var id = editor.attr('id'), is_html = $(this).is('.mode-html');

                e.preventDefault();
                if (rcmail.command('toggle-editor', {id: id, html: is_html}, '', e.originalEvent)) {
                    $(this).tab('show');

                    if (is_table) {
                        sw.prop('checked', is_html);
                    }
                }
            })
            .filter('.mode-' + mode()).tab('show');

        if (is_table) {
            // Hide unwanted table cells
            sw.parent().parent().hide();
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
                + '<a class="icon reset input-group-addon" href="#"></a>'
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
            var record = $(this.parentNode);

            if (area.children().length > 1) {
                record.remove();
            }
            else {
                $('input', record).val('').focus();
            }
        });

        $(elem).children()
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
            button = $('a.button', obj),
            navlist = $(obj).next();

        if (!navlist.height()) {
            records = $('tr,li', navlist).filter(function() { return this.style.display != 'none'; });
            height = $(records[0]).height() || 50;

            navlist.animate({height: (Math.min(5, records.length) * height) + 'px'}, speed);
            button.addClass('collapse').removeClass('expand');
        }
        else {
            navlist.animate({height: '0'}, speed);
            button.addClass('expand').removeClass('collapse');
        }
    };

    /**
     * Wrapper for rcmail.open_window to intercept window opening
     * and display a dialog with an iframe instead of a real window.
     */
    function window_open(url)
    {
        if (!is_mobile()) {
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
    var rcmail = parent.rcmail;
    var rcube_webmail = parent.rcube_webmail;
}

var UI = new rcube_elastic_ui();
