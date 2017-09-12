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
    this.about_dialog = about_dialog;
    this.spellmenu = spellmenu;
    this.searchmenu = searchmenu;
    this.headersmenu = headersmenu;
    this.attachmentmenu = attachmentmenu;
    this.show_list = show_list;
    this.show_sidebar = show_sidebar;


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

    // Update layout after initialization
    resize();

    // Setup various UI elements
    setup();


    /**
     * Setup procedure
     */
    function setup()
    {
        var content_buttons = [],
            is_framed = rcmail.is_framed();

        // Initialize search forms (in list headers)
        $('.header > .searchbar').each(function() { searchbar_init(this); });

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
        buttons.back_content.on('click', function() { show_content(); return false; });

        $('body').on('click', function() { if (mode == 'phone') layout.menu.addClass('hidden'); });

        // Set content frame title in parent window (exclude ext-windows and dialog frames)
        if (is_framed && !rcmail.env.extwin && !parent.$('.ui-dialog:visible').length) {
            var title = $('h1.voice:first').text();
            if (title) {
                parent.$('.header > .header-title', layout.content).text(title);
            }
        }
        else {
            var title = $('.boxtitle:first', layout.content).detach().text();
            if (title) {
                $('.header > .header-title', layout.content).text(title);
            }
        }

        // Move some buttons to the toolbar
        $('a[data-content-button]').each(function() {
            var target = $(this),
                button = target.clone(),
                target_id = target.attr('id'),
                button_id = target_id + '-clone';

            content_buttons.push(
                button.attr({'onclick': '', id: button_id, title: target.text()})
                    .on('click', function(e) { target.click(); })
                    .text('')
            );

            // Register the button to get active state updates
            register_cloned_button(target_id, button_id);
        });

        // Move form buttons from the content frame into the frame header (on parent window)
        // TODO: Active button state
        $('.formbuttons').children(':not(.cancel)').each(function() {
            var target = $(this);

            // skip non-content buttons
            if (!is_framed && !target.parents('.content').length) {
                return;
            }

            var button = target.clone();

            content_buttons.push(
                button.attr({'onclick': '', disabled: false, id: button.attr('id') + '-clone', title: target.text()})
                    .data('target', target)
                    .on('click', function(e) { target.click(); })
                    .text('')
            );
        });

        if (content_buttons.length) {
            if (is_framed) {
                if (parent.UI) {
                    parent.UI.register_content_buttons(content_buttons);
                }
            }
            else {
                register_content_buttons(content_buttons);
            }
        }

        $('[data-recipient-input]').each(function() { recipient_input(this); });
        $('.image-upload').each(function() { image_upload_input(this); });

        // Show input elements with non-empty value
        // These event handlers need to be registered before rcmail 'init' event
        $('#_cc, #_bcc, #_replyto, #_followupto', $('.compose-headers')).each(function() {
            $(this).on('change', function() {
                $('#compose' + $(this).attr('id'))[this.value ? 'removeClass' : 'addClass']('hidden');
            });
        });

        // We put compose options for outside of the main form
        // Because IE/Edge does not support 'form' attribute we'll copy
        // inputs into the main form hidden fields
        // TODO: Consider doing this for IE/Edge only, just set the 'form' attribute on others
        $('#compose-options').find('textarea,input,select').each(function() {
            var hidden = $('<input>')
                .attr({type: 'hidden', name: $(this).attr('name')})
                .appendTo(rcmail.gui_objects.messageform);

            $(this).on('change', function() { hidden.val($(this).val()); }).change();
        });

        $('#dragmessage-menu,#dragcontact-menu').each(function() {
            rcmail.gui_object('dragmenu', this.id);
        });

        // Taskmenu items added by plugins do not use elastic classes (e.g help plugin)
        // it's for larry skin compat. We'll assign 'button', 'selected' and icon-specific class.
        $('#taskmenu > a').each(function() {
            if (/button-([a-z]+)/.test(this.className)) {
                var data, name = RegExp.$1,
                    button = find_button(this.id);

                if (data = button.data) {
                    if (data.sel) {
                        data.sel += ' button ' + name;
                        data.sel = data.sel.replace('button-selected', 'selected');
                    }

                    if (data.act) {
                        data.act += ' button ' + name;
                    }

                    rcmail.buttons[button.command][button.index] = data;
                    rcmail.init_button(button.command, data);
                    $(this).addClass('button ' + name);
                    $('.button-inner', this).addClass('inner');
                }
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

        // Assign .formcontainer class to the iframe body, when it
        // contains .formcontent and .formbuttons.
        if (is_framed) {
            $('.formcontent').each(function() {
                if ($(this).next('.formbuttons').length) {
                    $(this).parent().addClass('formcontainer');
                }
            });
        }
    };

    /**
     * Moves form buttons into content frame toolbar (for mobile)
     */
    function register_content_buttons(buttons)
    {
        // we need these buttons really only in phone mode
        if (/*mode == 'phone' && */layout.content.length && buttons && buttons.length) {
            var header = layout.content.children('.header');

            if (header.length) {
                var toolbar = header.children('.buttons');

                if (!toolbar.length) {
                    var menu = $('a.toolbar-menu-button', header);
                    toolbar = $('<span class="buttons">');

                    if (menu.length) {
                        menu.before(toolbar);
                    }
                    else {
                        toolbar.appendTo(header);
                    }
                }

                content_buttons = [];
                $.each(buttons, function() {
                    if (this.data('target')) {
                        content_buttons.push(this.data('target'));
                    }
                });

                toolbar.html('').append(buttons);
                resize();
            }
        }
    };

    /**
     * Registers cloned button
     */
    function register_cloned_button(old_id, new_id)
    {
        var button = find_button(old_id);

        if (button) {
            rcmail.register_button(button.command, new_id,
                button.data.type, button.data.act, button.data.sel, button.data.over);
        }
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

        // Add menu link for each attachment
        $('#attachment-list > li').each(function() {
            attachmentmenu_append(this);
        });

        rcmail.addEventListener('fileappended', function(e) { if (e.attachment.complete) attachmentmenu_append(e.item); });

        rcmail.init_pagejumper('.pagenav > input');

        if (rcmail.task == 'mail') {
            // In compose window we do not provide "Back' button, instead
            // we modify the Mail button in the task menu to act like it (i.e. calls 'list' command)
            if (rcmail.env.action == 'compose' && !rcmail.env.extwin) {
                $('a.button.mail', layout.menu).attr('onclick', "return rcmail.command('list','',this,event)");
            }
        }

        // Update searchbar state
        $('.header > .searchbar').each(function() {
            if ($('input', this).val()) {
                $('a.button.search', this).addClass('active');
            }
        });

        rcmail.env.thread_padding = '1.5rem';
    };

    /**
     * Apply bootstrap classes to html elements
     */
    function bootstrap_style(context)
    {
        $('input.button,button', context || document).addClass('btn').not('.btn-primary,.primary,.mainaction').addClass('btn-secondary');
        $('input.button.mainaction,button.primary,button.mainaction', context || document).addClass('btn-primary');
        $('button.btn.delete').addClass('btn-danger');

        $.each(['warning', 'error'], function() {
            var type = this;
            $('.box' + type, context).each(function() {
                message_displayed({object: this, type: type});
            });
        });

        // Forms
        $('input,select,textarea', $('table.propform')).not('[type=checkbox]').addClass('form-control');
        $('[type=checkbox]', $('table.propform')).addClass('form-check-input');
        $('table.propform > tbody > tr').each(function() {
            var first, last, row = $(this),
                row_classes = ['form-group', 'row'],
                cells = row.children('td');

            if (cells.length == 2) {
                first = cells.first();
                last = cells.last();

                $('label', first).addClass('col-form-label');
                first.addClass('col-sm-4 col-form-label');
                last.addClass('col-sm-8');

                if (last.find('[type=checkbox]').length) {
                    row_classes.push('form-check');
                }
                else if (!last.find('input,textarea,radio,select').length) {
                    last.addClass('form-control-plaintext');
                }
            }

            row.addClass(row_classes.join(' '));
        });

        // Testing Bootstrap Tabs on contact info/edit page
        // Tabs do not scale nicely on very small screen, so can be used
        // only with small number of tabs with short text labels
        // TODO: Should we use Accordion widget instead on mobile?
        $('form.tabbed,div.tabbed', context).each(function(idx, item) {
            var tabs = [], nav = $('<ul>').attr({'class': 'nav nav-tabs', role: 'tablist'});

            $(this).addClass('tab-content').children('fieldset').each(function(i, fieldset) {
                var tab, id = 'tab' + idx + '-' + i;

                $(fieldset).addClass('tab-pane').attr({id: id, role: 'tabpanel'});

                tab = $('<li>').addClass('nav-item').append(
                    $('<a>').addClass('nav-link')
                        .attr({role: 'tab', 'href': '#' + id})
                        .text($('legend:first', fieldset).text())
                        .click(function() { $(this).tab('show'); })
                );

                $('legend:first', fieldset).hide();
                tabs.push(tab);
            });

            // create the navigation bar
            nav.append(tabs).insertBefore(item);
            // activate the first tab
            $('a.nav-link:first', nav).click();
        });

        // Make message-objects alerts pretty (the same as UI alerts)
        $('#message-objects').children('div').each(function() {
            alert_style(this, $(this).attr('class'));
            $('a', this).addClass('btn btn-primary');
        });

        // Make logon form prettier
        if (rcmail.env.task == 'login') {
            $('#login-form table tr').each(function() {
                var input = $('input,select', this),
                    label = $('label', this),
                    icon = $('<i>').attr('class', 'input-group-addon icon ' + input.attr('name').replace('_', ''));

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
        if (href.match(/_action=(create|add)/)) {
            if (env.frame_nav) {
                $(env.frame_nav).addClass('hidden');
            }

            return;
        }

        var uid, list = $('[data-list]', layout.list).data('list');

        if (!list || !(list = rcmail[list]) || !list.get_single_selection) {
            return;
        }

        // expand collapsed row so we do not skip the whole thread
        if (uid = list.get_single_selection()) {
            if (list.rows[uid] && !list.rows[uid].expanded) {
                list.expand_row(event, uid);
            }
        }

        // TODO: Add "message X from Y" text between buttons
        // TODO: Support tree_list widget (Settings > Folders)

        if (!env.frame_nav) {
            env.frame_nav = $('<div class="footer toolbar content-frame-navigation">')
                .append($('<a class="button prev">'))
                .append($('<span>'))
                .append($('<a class="button next">'))
                .appendTo(layout.content);
        }

        var prev, next, found = false,
            next_button = $('a.button.next', env.frame_nav).off('click').addClass('disabled'),
            prev_button = $('a.button.prev', env.frame_nav).off('click').addClass('disabled'),
            span = $('span', env.frame_nav).text('');

        if ((next = list.get_next_row()) || rcmail.env.current_page < rcmail.env.pagecount) {
            found = true;
            next_button.removeClass('disabled').on('click', function() {
                env.content_lock = true;
                if (next) {
                    list.select(next.uid);
                }
                else {
                    rcmail.env.list_uid = 'FIRST';
                    rcmail.command('nextpage');
                }
            });
        }

        if ((prev = list.get_prev_row()) || rcmail.env.current_page > 1) {
            found = true;
            prev_button.removeClass('disabled').on('click', function() {
                env.content_lock = true;
                if (prev) {
                    list.select(prev.uid);
                }
                else {
                    rcmail.env.list_uid = 'LAST';
                    rcmail.command('previouspage');
                }
            });
        }

        env.frame_nav[found ? 'removeClass' : 'addClass']('hidden');
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
        $('table,ul', o.obj).addClass('listing iconized');
        $(o.obj).addClass('popupmenu');
        bootstrap_style(o.obj);
    };

    /**
     * Window resize handler
     * Does layout reflows e.g. on screen orientation change
     */
    function resize()
    {
        var size, width = $(window).width();

        if (width <= 480)
            size = 'phone';
        else if (width > 1200)
            size = 'large';
        else if (width >= 768)
            size = 'normal';
        else
            size = 'small';

        touch = width <= 1024;
        mode = size;
        screen_resize();
        screen_resize_html();
//        display_screen_size(); // debug info
    };

    // for development only (to be removed)
    function display_screen_size()
    {
        if (rcmail.is_framed()) {
            return;
        }

        var div = $('#screen-size'), win = $(window);
        if (!div.length) {
            div = $('<div>').attr({
                id: 'screen-size',
                style: 'position:absolute;display:block;right:0;z-index:100;'
                    + (rcmail.is_framed() ? 'top:0;' : 'bottom:0;')
                    + 'opacity:0.5;color:white;background-color:black;white-space:nowrap'
            }).appendTo(document.body);
        }

        div.text(win.width() + ' x ' + win.height() + ' (' + mode + ')');
    };

    function screen_resize()
    {
        // TODO: Shall we do this in iframes?

        switch (mode) {
            case 'phone': screen_resize_phone(); break;
            case 'small': screen_resize_small(); break;
            case 'normal': screen_resize_normal(); break;
            case 'large': screen_resize_large(); break;
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
            show = layout.list.is(env.last_selected) || !layout.sidebar.is(env.last_selected);
            layout.list[show ? 'removeClass' : 'addClass']('hidden');
        }
        if (layout.sidebar.length) {
            show = !layout.list.length || layout.sidebar.is(env.last_selected);
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

        $.each(content_buttons, function() { $(this).hide(); });

        // disable ext-windows and other features
        rcmail.set_env(env.small_screen_config);
        rcmail.enable_command('extwin', false);
    };

    function screen_resize_small_none()
    {
        buttons.back_list.hide();
        $.each(content_buttons, function() { $(this).show(); });
        $('ul.toolbar.popupmenu').removeClass('popupmenu');

        // re-enable ext-windows
        rcmail.set_env(env.config);
        rcmail.enable_command('extwin', true);
    };

    function show_content()
    {
        // show sidebar and hide list
        layout.list.addClass('hidden');
        layout.sidebar.addClass('hidden');
        layout.content.removeClass('hidden');
    };

    function show_sidebar()
    {
        // show sidebar and hide list
        layout.list.addClass('hidden');
        layout.sidebar.removeClass('hidden');

        if (mode == 'small' || mode == 'phone') {
            layout.content.addClass('hidden');
        }
    };

    function show_list()
    {
        if (!layout.list.length && !layout.sidebar.length) {
            history.back();
        }
        else {
            // show list and hide sidebar and content
            layout.sidebar.addClass('hidden');
            layout.list.removeClass('hidden');

            if (mode == 'small' || mode == 'phone') {
                hide_content();
            }
        }
    };

    function hide_content()
    {
        // show sidebar or list, hide content frame
        //$(layout.content).addClass('hidden');
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
        alert_style(p.object, p.type);
        $(p.object).attr('role', 'alert');
        $('a', p.object).addClass('alert-link');
/*
        var siblings = $(p.object).siblings('div');
        if (siblings.length)
            $(p.object).insertBefore(siblings.first());

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
    function alert_style(object, type)
    {
        var cl, classes = 'ui alert',
            map = {
                information: 'alert-success',
                confirmation: 'alert-success',
                notice: 'alert-info',
                error: 'alert-danger',
                warning: 'alert-warning',
                loading: 'alert-info loading'
            };

        if (cl = map[type]) {
            classes += ' ' + cl;
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
        var input = $('input', bar),
            button = $('a.button.search', bar),
            settings_button = $('a.button.settings', bar)[0],
            form = $('form', bar),
            all_elements = $('form, a.button.options, a.button.reset', bar),
            is_search_pending = function() {
                // TODO: This have to be improved to detect real searching state
                //       There are cases when search is active but the input is empty
                return input.val();
            },
            hide_func = function(event, focus) {
                if (button.is(':visible')) {
                    return;
                }
                $(bar).animate({'width': '0'}, 200, 'swing', function() {
                    all_elements.hide();
                    $(bar).width('auto'); // fixes search button position in Chrome
                    button[is_search_pending() ? 'addClass' : 'removeClass']('active')
                        .css('display', 'block');
                    if (focus) {
                        button.focus();
                    }
                });
            };

        if (is_search_pending()) {
            button.addClass('active');
        }

        // Display search form (with animation effect)
        button.on('click', function() {
            $(bar).animate({'width': '100%'}, 200);
            all_elements.css('display', 'table-cell');
            button.hide();
            input.focus();
        });

        // Search reset action
        $('a.button.reset', bar).on('click', function(e) {
            // for treelist widget's search setting val and keyup.treelist is needed
            // in normal search form reset-search command will do the trick
            // TODO: This calls for some generalization, what about two searchboxes on a page?
            input.val('').change().trigger('keyup.treelist', {keyCode: 27});
            hide_func(e, true);
        });

        // These will hide the form, but not reset it
        rcube_webmail.set_iframe_events({mousedown: hide_func});
        $('body').on('mousedown', function(e) {
            // close searchbar on mousedown anywhere, but not inside the searchbar or dialogs
            if ($.inArray(bar, $(e.target).parents()) == -1 && !$(e.target).parents('.popover').length) {
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

        // TODO: if the toolbar contains "global or list only" buttons
        //       another popup menu with these options should be created
        //       on the list (or sidebar if there's no list element).
        // TODO: spacer item
        // TODO: a way to inject buttons to the menu from content iframe
        //       or automatically add all buttons except Save and Cancel
        //       (example QR Code button in contact frame)

        var items = [];

        // convert toolbar to a popup list
        $('.header > .toolbar', layout.content).each(function() {
            var toolbar = $(this);

            toolbar.children().each(function() {
                var button = $(this).detach();

                // Remove empty text nodes that break alignment of text of the menu item
                button.contents().filter(function() { if (this.nodeType == 3 && !$.trim(this.nodeValue).length) $(this).remove(); });

                items.push($('<li role="menuitem">').append(button));
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
    function popup_init(item)
    {
        var level,
            popup_id = $(item).data('popup'),
            popup = $('#' + popup_id),
            title = $(item).attr('title');

        $(item).attr({
                'aria-haspopup': 'true',
                'aria-expanded': 'false',
                'aria-owns': popup_id,
            })
            .popover({
                content: popup,
                trigger: $(item).data('popup-trigger') || 'click',
                placement: $(item).data('popup-pos') || 'bottom',
                animation: true,
                html: true
            })
            .on('show.bs.popover', function(event) {
                var init_func = $(popup).data('popup-init');

                if (init_func && ref[init_func]) {
                    ref[init_func](popup, item, event);
                }
                else if (init_func && window[init_func]) {
                    window[init_func](popup, item, event);
                }

                popup.attr('aria-hidden', false)
                    // Stop propagation on menu items that have popups
                    // to make a click on them not hide their parent menu(s)
                    .find('[aria-haspopup="true"]')
                        .off('click.popup')
                        .on('click.popup', function(e) { e.stopPropagation(); });

                if (!is_mobile()) {
                    // Set popup height so it is less than the window height
                    popup.css('max-height', Math.min(500, $(window).height() - 30));
                }
            })
            .on('shown.bs.popover', function(event, el) {
                var mobile = is_mobile();

                // Set popup Back/Close title
                if (mobile) {
                    level = $('div.popover:visible').length;

                    var label = level > 1 ? 'back' : 'close',
                        title = rcmail.gettext(label),
                        class_name = 'button icon ' + (label == 'back' ? 'back' : 'cancel');

                    $('.popover-header:last').empty()
                        .append($('<a>').attr('class', class_name).text(title))
                        .click(function(e) {
                            if (level > 1) {
                                $(item).popover('hide');
                                e.stopPropagation();
                            }
                        });
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
            })
            .on('hidden.bs.popover', function() {
                var parent = popup.data('popup-parent') || document.body;

                popup.attr('aria-hidden', true)
                    // Bootstrap will detach the popup element from
                    // the DOM (https://github.com/twbs/bootstrap/issues/20219)
                    // making our menus to not update buttons state.
                    // Work around this by attaching it back to the DOM tree.
                    .appendTo(parent);

                if (popup_id && menus[popup_id]) {
                    menus[popup_id].transitioning = false;
                }
            })
            .on('hide.bs.popover', function() {
                if (level == 1) {
                    $('.popover-overlay').remove();
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
        $('.popover-body:visible').each(function() {
            var popup = $(this),
                button = popup.children().first().data('button');

            if (button && e.target != button && !$(button).find(e.target).length && typeof button !== 'string') {
                $(button).popover('hide');
            }
        });
    };

    /**
     * Handler for menu-open and menu-close events
     */
    function menu_toggle(p)
    {
        if (!p || !p.name) {
            return;
        }

        if (p.name == 'messagelistmenu') {
            menu_messagelist(p);
        }
        else if (p.event == 'menu-open') {
            var fn, pos,
                content = $('ul:first', p.obj),
                target = p.originalEvent.target;

            if ($(target).is('span')) {
                target = $(target).parents('a,li')[0];
            }

            if (p.name == 'folder-selector') {
                content.addClass('listing folderlist');
            }
            else if (content.hasClass('toolbarmenu')) {
                content.addClass('listing');
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

            pos = 'right';

            if (p.name == 'pagejump-selector') {
                pos = 'top';
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
                    popup_init(target);
                }

                menus[p.name] = {target: target, transitioning: true};
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
            menus[name].transitioning = true;
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
        $('input[name="sort_col"][value="'+rcmail.env.sort_col+'"]', dialog).prop('checked', true);
        $('input[name="sort_ord"][value="DESC"]', dialog).prop('checked', rcmail.env.sort_order == 'DESC');
        $('input[name="sort_ord"][value="ASC"]', dialog).prop('checked', rcmail.env.sort_order != 'DESC');
        $('input[name="mode"][value="list"]', dialog).prop('checked', !rcmail.env.threading);
        $('input[name="mode"][value="threads"]', dialog).prop('checked', !!rcmail.env.threading);

        // set checkboxes
        $('input[name="list_col[]"]', dialog).each(function() {
            $(this).prop('checked', $.inArray(this.value, rcmail.env.listcols) != -1);
        });

        var save_func = function(e) {
            if (rcube_event.is_keyboard(e.originalEvent)) {
                $('#listmenulink').focus();
            }

            var sort = $('input[name="sort_col"]:checked', dialog).val(),
                ord = $('input[name="sort_ord"]:checked', dialog).val(),
                mode = $('input[name="mode"]:checked', dialog).val(),
                cols = $('input[name="list_col[]"]:checked', dialog)
                    .map(function() { return this.value; }).get();

            rcmail.set_list_options(cols, sort, ord, mode == 'threads');
            return true;
        };

        rcmail.simple_dialog(dialog, rcmail.gettext('listoptionstitle'), save_func, {
            closeOnEscape: true,
            open: function(e) {
                setTimeout(function() { dialog.find('a, input:not(:disabled)').not('[aria-disabled=true]').first().focus(); }, 100);
            },
            minWidth: 500,
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
                return rcmail.command(action + '-attachment', id, this);
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
                .on('click keypress', function(e) {
                    if (e.type != 'keypress' || rcube_event.get_keycode(e) == 13) {
                        attachmentmenu($('#attachmentmenu'), button, e);
                    }
                })
                .append($('<span>').attr('class', 'inner').text(label))
                .appendTo(item);
        }
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
                    $(target).removeClass('hidden').find('.recipient-input').focus();
                });
        });
    };

    /**
     * Replaces recipient input with content-editable element that uses "recipient boxes"
     */
    function recipient_input(obj)
    {
        var input, ac_props;

        var insert_recipient = function(name, email) {
            var recipient = $('<span>'),
                last = input.children('span:last'),
                name_element = $('<span>').attr({'class': 'name', contenteditable: false})
                    .html(recipient_input_name(name || email)),
                email_element = $('<span>').attr({'class': 'email', contenteditable: false}),
                // TODO: should the 'close' link have tabindex?
                link = $('<a>').attr({'class': 'button icon remove', contenteditable: false})
                    .click(function() {
                        recipient.remove();
                        // update the original input
                        $(obj).val(input.text());
                        // no need to propagate the event
                        return false;
                    });

            if (name) {
                email = ' <' + email + '>';
            }

            email_element.text((name ? email : '') + ',');
            recipient.attr({'class': 'recipient', contenteditable: false, title: name ? (name + email) : null})
                .append([name_element, email_element, link]);

            if (last.length) {
                (last).after(recipient);
            }
            else {
                input.html('').append(recipient)
                    // contentEditable BR is required as a workaround for cursor issues in Chrome
                    .append($('<br>').attr('contenteditable', false));
            }
        };

        // get text input node from inside of the widget
        var get_text_node = function() {
            return $(input).contents().filter(function() { return this.nodeType == 3; }).last()[0];
        };

        // Backspace key can add <br type="_moz"> in Firefox
        // Puts cursor at proper place of the content editable element
        var focus_func = function() {
            var obj, range = document.createRange();

            rcmail.env.focused_field = obj;

            // if there's a text node, put cursor at the end of it
            if (obj = get_text_node()) {
                range.setStart(obj, $(obj).text().length);
            }
            // else if there's <br> put the cursor before it
            else if (obj = input.children('br:last')[0]) {
                range.setStartBefore(obj);
            }
            // else if there's at least one recipient box put the cursor after the last one
            else if (obj = input.children('span:last')[0]) {
                range.setStartAfter(obj);
            }
            // else if there's any node, put the cursor after it
            else if (obj = input.lastChild) {
                range.setStartAfter(obj);
            }
            // else do nothing
            else {
                return;
            }

            range.collapse(true);
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        };

        var update_func = function() {
            var node, text, recipients = [], cloned = input.clone();

            cloned.find('span').remove();
            text = cloned.text().replace(/[,;\s]+$/, '');
            recipients = recipient_input_parser(text);

            $.each(recipients, function() {
                insert_recipient(this.name, this.email);
                text = text.replace(this.text, '');
            });

            if (recipients.length) {
                // update text node
                text = $.trim(text.replace(/[,]{1,}/g, ',').replace(/(^,|,$)/g, ''));
                $(input).contents().each(function() { if (this.nodeType == 3) $(this).remove(); });
                input.children('span:last').after(document.createTextNode(text));
            }

            return recipients.length > 0;
        };

        var parse_func = function(e) {
            // Note it can be also executed when autocomplete inserts a recipient
            update_func();

            // update the original input
            $(obj).val(input.text());

            // fix cursor position
            if (e.type != 'blur') {
                focus_func();
            }
        };

        var keydown_func = function(e) {
            // Backspace removes all recipients in Chrome, but in Firefox
            // it does nothing. We'll consistently remove the last recipient
            if (e.keyCode == 8) {
                // check if we're on the far left side of the text entry node
                var node = get_text_node(), selection = window.getSelection();
                if ((node == selection.focusNode && !selection.focusOffset) || selection.anchorNode == input[0]) {
                    input.children('span:last').remove();
                    // update the original input
                    $(obj).val(input.text());
                    focus_func();
                    return false;
                }
            }
            // Here we add a recipient box when the separator character (,;) was pressed
            else if (e.keyCode == 188 || e.keyCode == 59) {
                if (update_func()) {
                    focus_func();
                    return false;
                }
            }
        };

        // Create the content-editable div
        input = $('<div>')
            .attr({contenteditable: true, tabindex: $(obj).attr('tabindex')})
            .addClass('form-control recipient-input')
            .on('paste change blur', parse_func)
            .on('keydown', keydown_func)
            .on('focus mousedown', focus_func)
            .on('keyup', function() {
                // Backspace key can add <br type="_moz"> in Firefox
                // TODO: this fixes that, but causes input height jump effect
                $('br[type=\"_moz\"]', this).remove();
            });

        // "Replace" the original input/textarea with the content-editable div
        // Note: we do not remove the original element, and we do not use
        // display: none, because we want to handle onfocus event
        // Note: tabindex:-1 to make Shift+TAB working on these widgets
        $(obj).css({position: 'absolute', opacity: 0, left: '-5000px', width: '10px'})
            .attr('tabindex', -1).after(input)
            // some core code sometimes focuses or changes the original node
            // in such cases we wan't to parse it's value and apply changes
            // to the widget element
            .on('focus', function(e) { input.focus(); })
            .on('change', function(e) { input.text(this.value).change(); });

        // this one line is here to fix border of Bootstrap's input-group
        // input-group should not contain any hidden elements
        $(obj).detach().insertBefore(input.parent());

        // Copy and parse the value already set
        input.text($(obj).val()).change();

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
            .attr({'class': 'icon button cancel', href: '#', })
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
     * Wrapper for rcmail.open_window to intercept window opening
     * and display a dialog with an iframe instead of a real window.
     */
    function window_open(url)
    {
        if (!is_mobile()) {
            return env.open_window.apply(rcmail, arguments);
        }

        // _extwin=1 is required to display attachment preview layout properly
        $.each(['_framed', '_extwin'], function() {
            if (!RegExp('(&|\\?)' + this + '=').test(url)) {
                url += (url.match(/\?/) ? '&' : '?') + this + '=1';
            }
        });

        var label, title = '',
            frame = $('<iframe>').attr({id: 'windowframe', src: url});

        if (/_action=([a-z_]+)/.test(url) && (label = rcmail.labels[RegExp.$1])) {
            title = label;
        }

        rcmail.simple_dialog(frame, title, null, {cancel_button: 'close'});

        // Remove title bar of the dialog widget, we most likely have
        // one bar in the iframe
        frame.parent().parent().children('.ui-dialog-titlebar').hide();

        return true;
    };

    /**
     * Get layout modes. In frame mode returns the parent layout modes.
     */
    function layout_metadata()
    {
        if (rcmail.is_framed()) {
            var doc = $(parent.document.documentElement);

            return {
                mode: doc[0].className.match(/layout-([a-z]+)/) ? RegExp.$1 : mode,
                touch: doc.is('.touch'),
            };
        }

        return {mode: mode, touch: touch};
    }

    /**
     * Returns true if the layout is in 'small' or 'phone' mode
     */
    function is_mobile()
    {
        var meta = layout_metadata();

        return meta.mode == 'phone' || meta.mode == 'small';
    }

    /**
     * Returns true if the layout is in 'touch' mode
     */
    function is_touch()
    {
        var meta = layout_metadata();

        return meta.touch;
    }
}

var UI = new rcube_elastic_ui();
