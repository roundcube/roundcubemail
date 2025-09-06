/**
 * ContextMenu plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2009-2018 Philip Weir
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

rcube_webmail.prototype.contextmenu = {
    settings: {
        no_right_click_on_menu: true,
        skip_commands: ['mail-checkmail', 'mail-compose', 'addressbook-add', 'addressbook-import', 'addressbook-advanced-search', 'addressbook-search-create'],
        always_enable_commands: ['move', 'copy'],
        command_pattern: /rcmail\.command\(\x27([^\x27]+)\x27,\s?\x27((?:\\\x27|[^\x27])*)\x27/,
        addressbook_pattern: /([A-Z0-9\-_]+(:[A-Z0-9\-_]+)?)/i,
        popup_attrib: 'onclick',
        popup_pattern: '',
        popup_func: '',
        classes: {
                container: 'contextmenu',
                mainmenu: 'rcmmainmenu rcm-mainmenu', // rcmmainmenu class depreciated in v3.0
                submenu: 'rcmsubmenu submenu rcm-submenu', // rcmsubmenu submenu classes depreciated in v3.0
                popupmenu: 'popupmenu',
                button_remove: 'button',
                button_active: 'active',
                button_disabled: 'disabled'
            },
        menu_defaults: {},
        menu_events: {
            'command': function(p) {
                if (!$(p.el).hasClass(rcmail.contextmenu.settings.classes.button_active))
                    return;

                // enable the required command
                var prev_command = rcmail.commands[p.command];
                rcmail.enable_command(p.command, true);
                var result = rcmail.command(p.command, p.args, p.el, p.evt);

                // leave commands in always_enable_commands enabled, they are disabled when menu is closes
                if ($.inArray(p.command, rcmail.contextmenu.settings.always_enable_commands) >= 0 && prev_command === false) {
                    rcmail.contextmenu.vars.commands_disable_on_hide.push(p.command);
                }
                else {
                    rcmail.enable_command(p.command, prev_command);
                }

                return result;
            },
            'activate': function(p) {
                $(p.el).addClass(p.enabled ? rcmail.contextmenu.settings.classes.button_active : rcmail.contextmenu.settings.classes.button_disabled);
            },
            'submenu_toggle' : function(p) {
                $(p.id).parent()[(p.show) ? 'show' : 'hide']();
            }
        }
    },

    vars: {
        popup_menus: [],
        popup_commands: {},
        commands_disable_on_hide: []
    },

    skin_funcs: {},

    init_list: function(row, props, events) {
        if (!events)
            events = {};

        // backwards compatibility, list_object changed to string in v3.0
        if (typeof props.list_object == 'object') {
            var id = $(props.list_object.list).attr('id');
            props.list_object = null;

            if (id == 'contacts-table') {
                props.list_object = 'contact_list';
            }
        }

        if (!props.list_object)
            props.list_object = 'message_list';

        var menu = rcmail.contextmenu.init(props, $.extend({
            'beforeactivate': function(p) {
                rcmail.env.contextmenu_opening = true;

                if (p.ref.is_submenu) {
                    p.ref.list_selection(true, rcmail.env.contextmenu_prev_selection);
                    p.ref.menu_selection = p.ref.parent_menu.menu_selection;
                }
                else {
                    rcmail.env.contextmenu_prev_selection = p.ref.list_selection(true);
                    p.ref.menu_selection = rcmail[p.ref.list_object].get_selection();
                }
            },
            'afteractivate': function(p) {
                p.ref.list_selection(false, rcmail.env.contextmenu_prev_selection);
                rcmail.env.contextmenu_opening = false;
            },
            'aftercommand': function(p) {
                if (p.command == 'group-remove-selected') {
                    // update contactlist after contact remove (not done by core because there is no selection)
                    rcmail.command('listgroup', {'source': rcmail.env.source, 'id': rcmail.env.group}, p.el);
                }
            }
        }, events));

        $("#" + row).on('contextmenu', function(e) {
            var uid;
            if (uid = rcmail[props.list_object].get_row_uid(this)) {
                rcmail.contextmenu.hide_all(e);
                rcmail.contextmenu.show_one(e, this, uid, menu);
            }
        });

        rcmail[props.list_object].addEventListener('getselection', function() {
            if (rcmail.env.contextmenu_opening)
                return;

            var uids = null;
            $.each(rcmail.env.contextmenus, function() {
                if ($(this.container).is(':visible') && this.menu_selection.length > 0) {
                    uids = this.menu_selection;
                    return false;
                }
            });
            return uids;
        });
    },

    init_folder: function(el, props, events) {
        if (!events)
            events = {};

        var menu = rcmail.contextmenu.init($.extend({'menu_name': 'folderlist', 'list_object': null}, props), $.extend({
            'beforeactivate': function() {
                if (rcmail.env.contextmenu_messagecount_request) {
                    rcmail.env.contextmenu_messagecount_request.abort();
                }
                rcmail.env.contextmenu_messagecount_request = null;
            },
            'activate': function(p) {
                if ($.inArray(p.command, Array('expunge', 'purge', 'mark-all-read')) >= 0) {
                    // disable the commands by default
                    $(p.el).addClass(rcmail.contextmenu.settings.classes.button_disabled).removeClass(rcmail.contextmenu.settings.classes.button_active);

                    // if menu is opened on current folder (or special mark-all-read command) then enable the commands same as in UI
                    if ((rcmail.env.context_menu_source_id == rcmail.env.mailbox || p.command == 'mark-all-read') && rcmail.contextmenu.ui_button_check(p.btn, true)) {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                    // if menu is opened on difference folder then get message count for the folder
                    else if (rcmail.env.context_menu_source_id != rcmail.env.mailbox && !rcmail.env.contextmenu_messagecount_request) {
                        // folder check called async to prevent slowdown on menu load
                        rcmail.env.contextmenu_messagecount_request = $.ajax({
                            type: 'POST', url: rcmail.url('plugin.contextmenu.messagecount'), data: {'_mbox': rcmail.env.context_menu_source_id}, dataType: 'json', async: true,
                            success: function(data) {
                                if (data.messagecount > 0 && $('#rcm_folderlist').is(':visible')) {
                                    // override the environment to check if commands should be abled
                                    var temp_exists = rcmail.env.exists;
                                    var temp_mailbox = rcmail.env.mailbox;
                                    rcmail.env.exists = data.messagecount;
                                    rcmail.env.mailbox = rcmail.env.context_menu_source_id;

                                    $('#rcm_folderlist').find('a.cmd_expunge').addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                                    if (rcmail.purge_mailbox_test()) {
                                        $('#rcm_folderlist').find('a.cmd_purge').addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                                    }

                                    rcmail.env.exists = temp_exists;
                                    rcmail.env.mailbox = temp_mailbox;
                                }
                            }
                        });
                    }
                }
                else if (p.command == 'plugin.contextmenu.collapseall') {
                    if ($(rcmail.gui_objects.mailboxlist).find('div.expanded').length > 0) {
                        $('#rcm_folderlist').find('a.cmd_plugin-contextmenu-collapseall').addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                }
                else if (p.command == 'plugin.contextmenu.expandall') {
                    if ($(rcmail.gui_objects.mailboxlist).find('div.collapsed').length > 0) {
                        $('#rcm_folderlist').find('a.cmd_plugin-contextmenu-expandall').addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                }
            },
            'beforecommand': function(p) {
                if (rcmail.env.context_menu_source_id != rcmail.env.mailbox && $.inArray(p.command, Array('expunge', 'purge')) >= 0) {
                    var result = rcmail[p.command + '_mailbox'](rcmail.env.context_menu_source_id);

                    // update the unread count and trash icon
                    if (p.command == 'purge' && result !== false) {
                        rcmail.set_unread_count(rcmail.env.context_menu_source_id, 0, false);

                        if (rcmail.env.context_menu_source_id == rcmail.env.trash_mailbox)
                            rcmail.set_trash_count(0);
                    }

                    return {'abort': true, 'result': true};
                }
                else if (rcmail.env.context_menu_source_id != rcmail.env.mailbox && p.command == 'mark-all-read') {
                    rcmail.mark_all_read(rcmail.env.context_menu_source_id);
                    return {'abort': true, 'result': true};
                }
            }
        }, events));

        $(el).on('click', function(e) {
            // hide menu when changing folder
            rcmail.contextmenu.hide_all(e);
        })
        .on('contextmenu', function(e) {
            var source = $(this).find('a[rel][onclick]').filter(function() { return $(this).attr('onclick').match(rcmail.contextmenu.settings.command_pattern); }).first();
            source.blur(); // remove focus (and keyboard nav highlighting) from source element

            rcmail.contextmenu.hide_all(e);
            rcmail.contextmenu.show_one(e, this, source.attr('rel'), menu);
        });
    },

    init_addressbook: function(el, props, events) {
        if (!events)
            events = {};

        var menu = rcmail.contextmenu.init($.extend({'menu_name': 'abooklist'}, props), $.extend({
            'beforeactivate': function(p) {
                p.ref.container.find('li.' + rcmail.contextmenu.settings.classes.submenu.replace(/ /g, '.')).remove();
            },
            'activate': function(p) {
                var ids = rcmail.env.context_menu_source_id.split(':', 2);
                var cur_source = ids[0];

                if (p.command == 'group-create') {
                    // addressbook
                    if ($(p.source).hasClass('addressbook') && rcmail.env.address_sources[cur_source].groups && !rcmail.env.address_sources[cur_source].readonly) {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                    else {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_disabled).removeClass(rcmail.contextmenu.settings.classes.button_active);
                    }
                }
                else if (p.command == 'group-rename' || p.command == 'group-delete') {
                    // group
                    if ($(p.source).hasClass('contactgroup') && !rcmail.env.address_sources[cur_source].readonly) {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                    else {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_disabled).removeClass(rcmail.contextmenu.settings.classes.button_active);
                    }
                }
                else if (p.command == 'search-delete') {
                    // saved search
                    if ($(p.source).hasClass('contactsearch')) {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                    else {
                        $(p.el).addClass(rcmail.contextmenu.settings.classes.button_disabled).removeClass(rcmail.contextmenu.settings.classes.button_active);
                    }
                }
            },
            'command': function(p) {
                if (!$(p.el).hasClass(rcmail.contextmenu.settings.classes.button_active))
                    return;

                var prev_source = rcmail.env.source;
                var prev_group = rcmail.env.group;
                var result = false;

                var ids = rcmail.env.context_menu_source_id.split(':', 2);
                var cur_source = ids[0];
                var cur_id = ids[1];

                rcmail.env.source = cur_source;
                rcmail.env.group = cur_id;

                // enable the required command
                var prev_command = rcmail.commands[p.command];
                rcmail.enable_command(p.command, true);

                switch (p.command) {
                    case 'search-delete':
                        if ($(p.ref.selected_object).children('a').attr('rel')) {
                            var prev_search_id = rcmail.env.search_id;
                            var prev_search_request = rcmail.env.search_request;
                            rcmail.env.search_request = true;
                            rcmail.env.search_id = $(p.ref.selected_object).children('a').attr('rel').replace('S', '');

                            result = rcmail.command(p.command, p.args, p.el, p.evt);

                            rcmail.env.search_request = prev_search_request;
                            rcmail.env.search_id = prev_search_id;
                        }
                        break;
                    default:
                        result = rcmail.command(p.command, p.args, p.el, p.evt);
                        break;
                }

                rcmail.enable_command(p.command, prev_command);
                rcmail.env.source = prev_source;
                rcmail.env.group = prev_group;

                return result;
            }
        }, events));

        $(el).on('click', function(e) {
            // hide menu when changing address book
            rcmail.contextmenu.hide_all(e);
        })
        .on('contextmenu', function(e) {
            var source = $(this).find('a[rel]').filter(function() { return $(this).attr('rel').match(rcmail.contextmenu.settings.addressbook_pattern); }).first(), matches;
            source.blur(); // remove focus (and keyboard nav highlighting) from source element

            if (matches = source.attr('rel').match(rcmail.contextmenu.settings.addressbook_pattern)) {
                rcmail.contextmenu.hide_all(e);
                rcmail.contextmenu.show_one(e, this, matches[1], menu);
            }
        });
    },

    init: function(props, ext_events) {
        var events = $.extend({}, rcmail.contextmenu.settings.menu_events, ext_events), menu;

        if (!rcmail.env.contextmenus[props.menu_name]) {
            menu = new rcube_context_menu(props);
            $.each(events, function(trigger, func) {
                if (trigger.slice(0, 1) == '+') {
                    trigger = trigger.slice(1);
                    menu.addEventListener(trigger, function(p) { return func(p); })
                }
                else {
                    menu.addEventListener(trigger, function(p) { return func(p); });
                }
            });
            menu.init();
            rcmail.env.contextmenus[props.menu_name] = menu;
        }
        else {
            menu = rcmail.env.contextmenus[props.menu_name];
        }

        return menu;
    },

    show_one: function(e, src_elm, src_id, menu_obj) {
        // if contextmenus have been disabled then show browser context menu as normal
        if (!rcmail.env.contextmenu)
            return true;

        rcube_event.cancel(e);

        // hide any other open menus
        for (var i = 0; i < rcmail.contextmenu.vars.popup_menus.length; i++) {
            rcmail.hide_menu(rcmail.contextmenu.vars.popup_menus[i], e);
        }

        rcmail.env.context_menu_source_id = src_id;
        menu_obj.show_menu(src_elm, e);
    },

    hide_all: function(e, sub_only, no_trigger) {
        var remove_menu = function(e, menu) {
            if (!menu.container.is(':visible'))
                return;

            menu.hide_menu(e);

            if (!no_trigger) {
                var menu_name = 'rcm_' + menu.menu_name;
                rcmail.triggerEvent('menu-close', { name: menu_name, props:{ menu: menu_name }, originalEvent: e });
            }
        }

        $.each(rcmail.env.contextmenus, function() {
            if (!sub_only) {
                remove_menu(e, this);
            }
            else {
                $.each(this.submenus, function() {
                    remove_menu(e, this);
                });
            }
        });

        // close popup menus opened by the contextmenu
        for (var i = rcmail.contextmenu.vars.popup_menus.length - 1; i >= 0; i--) {
            rcmail.hide_menu(rcmail.contextmenu.vars.popup_menus[i], e);
        }

        // disable commands that were enabled by force
        if (rcmail.contextmenu.vars.commands_disable_on_hide.length > 0) {
            rcmail.enable_command(rcmail.contextmenu.vars.commands_disable_on_hide, false);
            rcmail.contextmenu.vars.commands_disable_on_hide = [];
        }
    },

    ui_button_check: function(btn, active) {
        var classes = (active ? rcmail.contextmenu.settings.classes.button_active : rcmail.contextmenu.settings.classes.button_disabled).split(' ');
        var found = false;

        $.each(classes, function(i) {
            if ($('#' + btn).hasClass(classes[i])) {
                found = true;

                // stop processing
                return false;
            }
        });

        return found;
    }
};

// backwards compatibility, functions renamed in v3.0
function rcm_listmenu_init(row, props, events) { rcmail.contextmenu.init_list(row, props, events); }
function rcm_foldermenu_init(el, props, events) { rcmail.contextmenu.init_folder(el, props, events); }
function rcm_abookmenu_init(el, props, events) { rcmail.contextmenu.init_addressbook(el, props, events); }
function rcm_callbackmenu_init(props, ext_events) { return rcmail.contextmenu.init(props, ext_events); }
function rcm_show_menu(e, obj, id, menu) { rcmail.contextmenu.show_one(e, obj, id, menu); }
function rcm_hide_menu(e, sub_only, no_trigger) { rcmail.contextmenu.hide_all(e, sub_only, no_trigger); }
function rcm_check_button_state(btn, active) { return rcmail.contextmenu.ui_button_check(btn, active); }

function rcube_context_menu(p) {
    this.menu_name = null;
    this.menu_source = new Array();
    this.menu_source_obj = null;
    this.list_object = null;
    this.mouseover_timeout = 400;
    this.classes = {
        source: 'contextRow context-source', // contextRow class depreciated in v3.0
        div: rcmail.contextmenu.settings.classes.container + ' popupmenu',
        ul: 'toolbarmenu iconized',
        a: 'icon',
        span: 'icon',
        sub_button_a: null,
        sub_button_span: 'right-arrow sub-button' // right-arrow class depreciated in v3.0
    }

    this.modal = false;
    this.is_submenu = false;
    this.submenu_position = 'right';
    this.parent_menu = this;
    this.parent_object = null;
    this.selected_object = null
    this.container = null;
    this.menu_selection = new Array();
    this.submenus = {};
    this.timers = {};

    // add global config defaults and instance config
    $.extend(true, this, rcmail.contextmenu.settings.menu_defaults, p);

    // ensure manu_source option is always an array
    if (typeof this.menu_source == 'string')
        this.menu_source = [this.menu_source];

    var ref = this;

    this.init = function() {
        if (!this.container) {
            rcmail.triggerEvent('contextmenu_init', this);

            this.container = $('<div>').attr('id', 'rcm_'+ this.menu_name).css('display', 'none');
            this.container.addClass(this.classes.div);
            this.container.addClass(this.is_submenu ? rcmail.contextmenu.settings.classes.submenu : rcmail.contextmenu.settings.classes.mainmenu);

            var rows = [], ul = $('<ul role="menu">'),
            li = $('<li>'), link = $('<a>'), span = $('<span>');

            ul.addClass(this.classes.ul);
            li.attr('role', 'menuitem');

            link.attr('href', '#');
            link.addClass(this.classes.a);
            link.attr('role', 'button');
            link.attr('tabindex', '-1');
            link.attr('aria-disabled', 'true');

            span.addClass(this.classes.span);

            this.menu_source_obj = {};
            $.each(this.menu_source, function(i) {
                var source_elements;
                if (typeof ref.menu_source[i] == 'string') {
                    ref.menu_source_obj[ref.menu_source[i]] = {
                        'toggle': !$(ref.menu_source[i]).is(':visible')
                    };
                    source_elements = $(ref.menu_source[i]).children();
                }
                else {
                    ref.menu_source_obj[i] = {
                        'toggle': false
                    };
                    source_elements = $(ref.menu_source[i]);
                }

                ul.attr('aria-labelledby', $(ref.menu_source[i]).attr('aria-labelledby'));

                $.each(source_elements, function() {
                    var src_elem, elem, command, args;

                    var callback = ref.triggerEvent('addmenuitem', {ref: ref, el: this});
                    if (!callback || (!callback.abort && !callback.result)) {
                        if ($(this).is('li')&& $(this).children().length == 1) {
                            src_elem = $(this).children()[0];
                        }
                        else {
                            src_elem = this;
                        }

                        if ($(src_elem).is('a')) {
                            elem = $(src_elem).clone();

                            if (!elem[0].hasAttribute('onclick') || !elem.attr('onclick').match(rcmail.contextmenu.settings.command_pattern)) {
                                if (elem[0].hasAttribute(rcmail.contextmenu.settings.popup_attrib) && !elem.attr(rcmail.contextmenu.settings.popup_attrib).match(rcmail.contextmenu.settings.popup_pattern)) {
                                    return;
                                }
                            }
                        }
                        else if ($(src_elem).is('span') && $(src_elem).children().length == 2) {
                            elem = $(src_elem).children(':first').clone();

                            if ($(src_elem).children(':last').attr(rcmail.contextmenu.settings.popup_attrib).match(rcmail.contextmenu.settings.popup_pattern)) {
                                $(elem).attr(rcmail.contextmenu.settings.popup_attrib, $(src_elem).children(':last').attr(rcmail.contextmenu.settings.popup_attrib));
                            }
                        }
                        else if ($(src_elem).parent().is('a')) {
                            elem = $(src_elem).parent().clone();
                        }
                        else if (src_elem.command && src_elem.label) {
                            elem = $('<a>').attr('href', '#')
                                    .attr('id', 'rcmjs')
                                    .attr('onclick', "return rcmail.command('"+ src_elem.command +"','"+ src_elem.props +"',src_elem,event)")
                                    .addClass(src_elem.classes)
                                    .html(src_elem.label);
                        }
                        else {
                            return;
                        }
                    }
                    else if (callback.abort) {
                        return;
                    }
                    else {
                        elem = callback.result;
                    }

                    // turn custom popup function into onclick
                    if (rcmail.contextmenu.settings.popup_func.length > 0 && elem[0].hasAttribute(rcmail.contextmenu.settings.popup_attrib) && elem.attr(rcmail.contextmenu.settings.popup_attrib).match(rcmail.contextmenu.settings.popup_pattern)) {
                        elem.attr('onclick', elem.attr(rcmail.contextmenu.settings.popup_attrib).replace(rcmail.contextmenu.settings.popup_pattern, rcmail.contextmenu.settings.popup_func));
                    }

                    // skip any element that does not look like a Roundcube button
                    if (!elem[0].hasAttribute('onclick')) {
                        return;
                    }

                    command = '';
                    var matches;
                    if (matches = elem.attr('onclick').match(rcmail.contextmenu.settings.command_pattern)) {
                        command = matches[1];
                        args = matches[2];
                    }

                    // skip elements we don't need
                    if ($.inArray(rcmail.env.task + '-' + command, rcmail.contextmenu.settings.skip_commands) >= 0 || elem.hasClass('rcm_ignore') || elem.hasClass('rcm-ignore')) { // rcm_ignore class depreciated in v3.0
                        return;
                    }

                    var a = link.clone(), row = li.clone();

                    // add command name element
                    var tmp = span.clone();
                    tmp.text($.trim(elem.text()).length > 0 ? $.trim(elem.text()) : elem.attr('title'));
                    tmp.addClass(elem.children('span').attr('class'));
                    a.append(tmp);
                    a.addClass(elem.attr('class'));
                    a.removeClass(rcmail.contextmenu.settings.classes.button_remove);
                    a.removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    a.addClass('rcm_elem_' + elem.attr('id'));

                    if (matches = elem.attr('onclick').match(rcmail.contextmenu.settings.popup_pattern)) {
                        a.data('command', matches[1]);
                        a.data('menu-pos', ref.submenu_position);
                        a.addClass(ref.classes.sub_button_a);
                        if (ref.classes.sub_button_span)
                            a.append($('<span>').addClass(ref.classes.sub_button_span));
                        row.addClass(rcmail.contextmenu.settings.classes.submenu);
                        a.on('click', function(e) {
                            if (!$(this).hasClass(rcmail.contextmenu.settings.classes.button_active))
                                return;

                            ref.submenu(a, e);
                            return false;
                        });

                        if (ref.mouseover_timeout > -1) {
                            a.mouseover(function(e) {
                                if (!$(this).hasClass(rcmail.contextmenu.settings.classes.button_active))
                                    return;

                                ref.timers['submenu_show'] = window.setTimeout(function(a, e) {
                                    ref.submenu(a, e);
                                }, ref.mouseover_timeout, a, e);
                            });

                            a.mouseout(function() {
                                if (!$(this).hasClass(rcmail.contextmenu.settings.classes.button_active))
                                    return;

                                $(this).blur(); clearTimeout(ref.timers['submenu_show']);
                            });
                        }
                    }
                    else {
                        a.addClass('cmd_' + command.replace(/\./g, '-'));
                        a.data('command', command);
                        if (elem.attr('target'))
                            a.attr('target', elem.attr('target'));

                        a.on('click', function(e) {
                            if ($(this).parents('div.' + rcmail.contextmenu.settings.classes.submenu.replace(/ /g, '.')).length == 0) {
                                rcmail.contextmenu.hide_all(e, true);
                                clearTimeout(ref.timers['submenu_hide']);
                            }

                            var cur_popups = rcmail.contextmenu.vars.popup_menus.length;
                            var result;

                            var callback = ref.parent_menu.triggerEvent('beforecommand', {ref: ref, el: this, command: command, args: args});
                            if (!callback || !callback.abort) {
                                result = ref.parent_menu.triggerEvent('command', {ref: ref, el: this, command: command, args: args, evt: e});
                            }
                            else {
                                result = callback.result;
                            }

                            if (!callback || !callback.skipaftercommand)
                                ref.parent_menu.triggerEvent('aftercommand', {ref: ref, el: this, command: command, args: args});

                            if (rcmail.contextmenu.vars.popup_menus.length > cur_popups) {
                                var popup_name = rcmail.contextmenu.vars.popup_menus[rcmail.contextmenu.vars.popup_menus.length - 1];

                                // make sure enabled commands match context menu message selection
                                $.each(rcmail.contextmenu.vars.popup_commands[popup_name], function(cmd, state) {
                                    rcmail.enable_command(cmd, state);
                                });
                            }

                            // ensure menu is always hidden after action
                            ref.parent_menu.hide_menu(e);

                            return result;
                        });

                        if (ref.mouseover_timeout > -1 && !ref.is_submenu) {
                            a.mouseover(function(e) {
                                ref.timers['submenu_hide'] = window.setTimeout(function(e) {
                                    rcmail.contextmenu.hide_all(e, true);
                                }, ref.mouseover_timeout, e);
                            });

                            a.mouseout(function() { clearTimeout(ref.timers['submenu_hide']); });
                        }
                    }

                    row.append(a);
                    ref.parent_menu.triggerEvent('insertitem', {item: row});
                    rows.push(row);
                });

                // listen for a menu-change event, it means the context menu object needs to be rebuilt
                $(ref.menu_source[i]).on('menu-change', function(e) {
                    // watch out for event bubbling, we only want the original event
                    if (e.target == this)
                        ref.destroy();
                });
            });

            ul.append(rows).appendTo(this.container);
            this.parent_menu.triggerEvent('init', {ref: this});
            this.container.appendTo($('body'));
        }
    };

    this.show_menu = function(obj, e) {
        if (obj) {
            this.hide_menu(e);
        }

        var callback = this.parent_menu.triggerEvent('beforeactivate', {ref: this, source: obj, originalEvent: e});
        if (!callback || !callback.abort) {
            if (obj) {
                $(obj).addClass(this.classes.source);
            }

            if (this.modal && !this.is_submenu && ($('#rcm_modal').length == 0 || (rcmail.contextmenu.settings.classes.modal_overlay && $('.' + rcmail.contextmenu.settings.classes.modal_overlay).length == 0))) {
                $('<div>').attr('id', 'rcm_modal').addClass(rcmail.contextmenu.settings.classes.modal_overlay)
                    .on('contextmenu', function(e) { $(e.target).trigger('click'); rcube_event.cancel(e); })
                    .insertBefore('#rcm_'+ this.menu_name);
            }

            $.each(ref.menu_source_obj, function(id, props) {
                if (props.toggle) {
                    // wait for return
                    var ret = ref.parent_menu.triggerEvent('submenu_toggle', {id: id, ref: ref, show: true});
                }
            });

            $.each(this.container.find('a'), function() {
                var btn;
                if ($(this).hasClass('rcm_active') || $(this).hasClass('rcm-active')) { // rcm_active class depreciated in v3.0
                    $(this).addClass(rcmail.contextmenu.settings.classes.button_active);
                }
                else if (btn = $(this).attr('class').match(/rcm_elem_([a-z0-9]+)/)) {
                    $(this).parent('li')[(btn[1] == 'rcmjs' || $('#' + btn[1]).is(':visible')) ? 'show' : 'hide']();
                    $(this).removeClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);

                    var enabled = false;
                    if (!rcmail.contextmenu.ui_button_check(btn[1], false) && (!ref.is_submenu || rcmail.contextmenu.ui_button_check(btn[1], true))) {
                        enabled = true;
                    }

                    var ret = ref.parent_menu.triggerEvent('activate', {el: this, btn: btn[1], source: obj, command: $(this).data('command'), enabled: enabled});
                    if (ret === true) {
                        $(this).addClass(rcmail.contextmenu.settings.classes.button_active).removeClass(rcmail.contextmenu.settings.classes.button_disabled);
                    }
                    else if (ret === false) {
                        $(this).addClass(rcmail.contextmenu.settings.classes.button_disabled).removeClass(rcmail.contextmenu.settings.classes.button_active);
                    }
                }
            });

            $.each(ref.menu_source_obj, function(id, props) {
                if (props.toggle) {
                    // wait for return
                    var ret = ref.parent_menu.triggerEvent('submenu_toggle', {id: id, ref: ref, show: false});
                }
            });

            this.parent_menu.triggerEvent('afteractivate', {ref: this, source: obj, originalEvent: e});
        }

        // position menu on the screen
        if (this.is_submenu) {
            rcmail.element_position(this.container, this.parent_object);
        }
        else {
            this.position(e, this.container);
        }

        if (!callback || callback.show !== false) {
            this.selected_object = obj;
            this.container.show();
            rcmail.triggerEvent('menu-open', { name: this.container.attr('id'), props:{ menu: this.container.attr('id'), skinable: false }, originalEvent: e });
        }
    };

    this.hide_menu = function(e) {
        if ($('div.' + rcmail.contextmenu.settings.classes.container).is(':visible') && (rcmail.contextmenu.vars.popup_menus.length == 0 || $(e.target).parents('div.' + rcmail.contextmenu.settings.classes.container).length == 0)) {
            if (!this.is_submenu) {
                this.selected_object = null;
                $('.' + this.classes.source.replace(/ /g, '.')).removeClass(this.classes.source);

                if (this.modal)
                    $('#rcm_modal').remove();
            }

            // if the menu has submenus they should be hidden to
            $.each(this.submenus, function() {
                this.container.hide();
            });

            this.container.hide();
        }
    };

    this.submenu = function(link, e) {
        if (e) {
            rcube_event.cancel(e);
        }

        rcmail.contextmenu.hide_all(e, true);

        var id = rcmail.gui_containers[$(link).data('command')] ? rcmail.gui_containers[$(link).data('command')].attr('id') : $(link).data('command');
        if (!this.submenus[id]) {
            var elem = !$('#' + id).is('ul') ? '#' + id + ' ul' : '#' + id; // check if the container returned is a ul else there should be one directly beneath it
            this.submenus[id] = new rcube_context_menu({'menu_name': id, 'menu_source': elem, 'parent_menu': this, 'parent_object': link, 'is_submenu': true, 'list_object': this.list_object});
            this.submenus[id].init();
        }

        this.submenus[id].show_menu(null, e);
    };

    this.position = function(e, menu) {
        // temporarily show element to calculate its size
        menu.css({left: '-1000px', top: '-1000px'}).show();

        var win = $(window),
        win_height = win.height(),
        elem_height = $(menu).height(),
        elem_width = $(menu).width(),
        top = e.pageY,
        left = e.pageX;

        if (top + elem_height > win_height) {
            top -= elem_height;

            if (top < 0)
                top = Math.max(0, (win_height - elem_height) / 2);
        }

        if (left + elem_width > win.width())
            left -= ((left + elem_width) - win.width()) + 10;

        // sanity check
        if (left < 0) left = 0;

        menu.hide();
        menu.css({left: left + 'px', top: top + 'px'});
    };

    this.list_selection = function(show, prev_sel) {
        // make the system think no preview pane exists while we do some fake message selects
        // to enable/disable relevant commands for current selection
        var prev_contentframe = rcmail.env.contentframe, i;
        rcmail.env.contentframe = null;

        if (show) {
            if (rcmail[this.list_object].selection.length == 0 || !rcmail[this.list_object].in_selection(rcmail.env.context_menu_source_id)) {
                prev_sel = prev_sel ? prev_sel : rcmail[this.list_object].get_selection();
                rcmail[this.list_object].highlight_row(rcmail.env.context_menu_source_id, true);

                for (i in prev_sel)
                    rcmail[this.list_object].highlight_row(prev_sel[i], true);

                rcmail[this.list_object].triggerEvent('select');
            }
            else {
                // trigger a select event to update active commands
                // use case: select multiple message, open contextmenu; open contextmenu on a message not in selection; open contextmenu on selection
                rcmail[this.list_object].triggerEvent('select');
            }
        }
        else if (prev_sel) {
            for (i in prev_sel)
                rcmail[this.list_object].highlight_row(prev_sel[i], true);

            rcmail[this.list_object].highlight_row(rcmail.env.context_menu_source_id, true);
            rcmail[this.list_object].triggerEvent('select');
        }

        rcmail.env.contentframe = prev_contentframe;

        return prev_sel;
    };

    this.destroy = function() {
        $.each(this.submenus, function() {
            this.destroy();
        });

        // remove the menu from the UI
        this.container.remove();

        if (this.is_submenu)
            delete rcmail.env.contextmenus[this.parent_menu.menu_name].submenus[this.menu_name];
        else
            delete rcmail.env.contextmenus[this.menu_name];
    };

    this.addEventListener = rcube_event_engine.prototype.addEventListener;
    this.removeEventListener = rcube_event_engine.prototype.removeEventListener;
    this.triggerEvent = rcube_event_engine.prototype.triggerEvent;
}

$(document).ready(function() {
    if (window.rcmail) {
        rcmail.env.contextmenus = {};

        // backwards compatibility with old settings code removed in v3.0
        var old_settings = ['context_menu_skip_commands', 'context_menu_overload_commands', 'context_menu_command_pattern', 'context_menu_popup_pattern', 'context_menu_button_active_class', 'context_menu_button_disabled_class'];
        $.each(old_settings, function() {
            if (rcmail[this]) {
                var opt = this.replace(/^context_menu_/, '');

                if ((this == 'context_menu_button_active_class' || this == 'context_menu_button_disabled_class') && $.isArray(rcmail[this])) {
                    rcmail.contextmenu.settings[opt] = rcmail[this].join(' ');
                }
                else if (this == 'context_menu_overload_commands') {
                    rcmail.contextmenu.settings['always_enable_commands'] = rcmail[this];
                }
                else {
                    rcmail.contextmenu.settings[opt] = rcmail[this];
                }
            }
        });

        rcmail.addEventListener('init', function() {
            // no need to reattach events inside iframe
            if (rcmail.is_framed())
                return;

            var body_mouseup = function(e) {
                $.each(rcmail.env.contextmenus, function() {
                    if (rcmail.contextmenu.settings.no_right_click_on_menu && e.which == 3 && $(e.target).parents('.contextmenu').length > 0) {
                        // useability - on the contextmenu make right click the same as left (sometimes users think they have to right click because they right clicked to get there)
                        $(e.target).trigger('click');
                        rcube_event.cancel(e);
                    }
                    else {
                        rcmail.contextmenu.hide_all(e);
                    }
                });
            };
            $(document.body).on('click contextmenu', body_mouseup);

            // Hide menu after clicks in iframes (eg. preview pane)
            $('iframe').on('load', function() {
                try { $(this.contentDocument || this.contentWindow).on('mouseup', body_mouseup) }
                catch (e) { /* catch possible "Permission denied" error in IE */ }
            })
            .contents().on('mouseup', body_mouseup);
        });

        if (rcmail.env.task == 'mail' && rcmail.env.action == '') {
            rcmail.register_command('plugin.contextmenu.collapseall', function() {
                if (rcmail.gui_objects.mailboxlist) {
                    $(rcmail.gui_objects.mailboxlist).find('div.expanded').each(function() {
                        var event = jQuery.Event('click');
                        event.target = this;
                        $(rcmail.gui_objects.mailboxlist).trigger(event);
                    });
                }
            }, false);

            rcmail.register_command('plugin.contextmenu.expandall', function() {
                if (rcmail.gui_objects.mailboxlist) {
                    $(rcmail.gui_objects.mailboxlist).find('div.collapsed').each(function() {
                        var event = jQuery.Event('click');
                        event.target = this;
                        $(rcmail.gui_objects.mailboxlist).trigger(event);
                    });
                }
            }, false);

            rcmail.register_command('plugin.contextmenu.openfolder', function() {
                var button_id = rcmail.buttons['plugin.contextmenu.openfolder'][0].id;

                rcube_find_object(button_id).href = '?_task=mail&_mbox='+urlencode(rcmail.env.context_menu_source_id);
                rcmail.sourcewin = window.open(rcube_find_object(button_id).href);
                if (rcmail.sourcewin)
                    window.setTimeout(function() { rcmail.sourcewin.focus(); }, 20);

                rcube_find_object(button_id).href = '#';
            }, false);
        }

        // special event listeners for intreacting with plugins which open popup menus (eg: zipdownload)
        rcmail.addEventListener('menu-open', function(p) {
            // check for popupmenus that arent part of contextmenu
            if ($('div.' + rcmail.contextmenu.settings.classes.container).is(':visible') && p.name.indexOf('rcm_') != 0) {
                rcmail.contextmenu.vars.popup_commands[p.name] = {};
                $('#' + p.name).find('a').each(function() {
                    var matches;
                    if ($(this).attr('onclick') && (matches = $(this).attr('onclick').match(rcmail.contextmenu.settings.command_pattern))) {
                        rcmail.contextmenu.vars.popup_commands[p.name][matches[1]] = rcmail.commands[matches[1]];
                    }
                });

                // keep track of whats open
                rcmail.contextmenu.vars.popup_menus.push(p.name);
            }
        });

        rcmail.addEventListener('menu-close', function(p) {
            // keep track of whats open
            var idx;
            if ((idx = $.inArray(p.name, rcmail.contextmenu.vars.popup_menus)) >= 0) {
                rcmail.contextmenu.vars.popup_menus.splice(idx, 1);
            }

            // check required args are present, other plugins trigger this event too
            if (!p.originalEvent) {
                return;
            }

            // check for popupmenus that arent part of contextmenu
            var e = p.originalEvent.currentTarget ? p.originalEvent.currentTarget : p.originalEvent.srcElement;
            if ($('div.' + rcmail.contextmenu.settings.classes.container).is(':visible') && p.name.indexOf('rcm_') != 0 && $(e).prop('class').indexOf('rcm_elem_') == -1) {
                rcmail.contextmenu.hide_all(p.originalEvent, false, true);
            }
        });

        rcmail.addEventListener('get_single_uid', function() {
            if ($('#rcm_messagelist').is(':visible') && rcmail.env.contextmenus['messagelist'].menu_selection.length == 1) {
                return rcmail.env.contextmenus['messagelist'].menu_selection[0];
            }
        });

        rcmail.addEventListener('get_single_cid', function() {
            if ($('#rcm_contactlist').is(':visible') && rcmail.env.contextmenus['contactlist'].menu_selection.length == 1) {
                return rcmail.env.contextmenus['contactlist'].menu_selection[0];
            }
            else if ($('#rcm_composeto').is(':visible') && rcmail.env.contextmenus['composeto'].menu_selection.length == 1) {
                return rcmail.env.contextmenus['composeto'].menu_selection[0];
            }
        });
    }
});