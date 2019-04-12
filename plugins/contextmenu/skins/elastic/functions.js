/**
 * ContextMenu plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2017 Philip Weir
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

rcube_webmail.prototype.contextmenu.skin_funcs.compose_menu_text = function(p) {
    var matches;
    if (matches = $(p.item).children('a').attr('class').match(/\b(addto|addcc|addbcc)\b/)) {
        $(p.item).children('a').children('span').text($('#layout > .sidebar > div.footer a.' + matches[1]).attr('title'));
    }
};

rcube_webmail.prototype.contextmenu.skin_funcs.submenu_toggle = function(p) {
    var matches;
    if ((matches = p.id.match(/^(#[^\s]+(\s>\s\.[a-z]+\s>)?(\sdiv)?)/)) && p.id.indexOf('#taskmenu') == -1) {
        var source_id = matches[1];

        // make sure its a real submenu and not the toolbar on a small screen
        if ($(source_id).is('div') && !$(source_id).hasClass('footer')) {
            if (p.show) {
                $(source_id).show();
                $(source_id).removeClass('hidden');
            }
            else {
                $(source_id).hide();
                $(source_id).addClass('hidden');
            }
        }
    }
};

$(document).ready(function() {
    if (window.rcmail) {
        $.extend(true, rcmail.contextmenu.settings, {
            popup_attrib: 'data-popup',
            popup_func: "rcm_popup_wrapper('$2');",
            popup_pattern: /rcm_popup_wrapper\(\x27([^\x27]+)\x27|^([a-z0-9\-]+)$/i,
            classes: { button_remove: '', modal_overlay: 'popover-overlay' },
            menu_defaults: {
                modal: true,
                classes: {
                    div: rcmail.contextmenu.settings.classes.container + ' popover',
                    ul: 'toolbarmenu toolbar listing',
                    a: 'button rcmbutton',
                    sub_button_a: 'rcmsubbutton',
                    sub_button_span: null
                }
            },
            menu_events: {
                '+init': function(p) {
                    var label = $('div.contextmenu:visible').length >= 1 ? 'back' : 'close',
                        title = rcmail.gettext(label),
                        class_name = 'button icon ' + (label == 'back' ? 'back' : 'cancel');

                    var header = $('<h3>').addClass('popover-header')
                        .append($('<a>').attr('class', class_name).text(title))
                        .on('click', function(e) {
                            rcmail.contextmenu.hide_all(e, $('div.contextmenu:visible').length >= 1);
                        });

                    $(p.ref.container).prepend(header).children('ul').wrap($('<div>').addClass('popover-body'));
                },
                '+beforeactivate': function() {
                    // force toolbar display on small screens while the contextmenu renders
                    if (!$('#layout > .content').is(':visible'))
                        $('#layout > .content').addClass('contextmenu_content');

                    // change default message list contextmenu to look like normal rc menu when on small screen
                    $('a.rcm_elem_markmessagemenulink,a.rcm_elem_messagemenulink')[($('html').hasClass('layout-phone') || $('html').hasClass('layout-small')) ? 'removeClass' : 'addClass']('rcmsubbutton');
                },
                '+afteractivate': function() {
                    $('#layout > .content').removeClass('contextmenu_content');
                },
                'submenu_toggle': function(p) { rcmail.contextmenu.skin_funcs.submenu_toggle(p); }
            }
        });

        if (rcmail.env.task == 'mail' && rcmail.env.action == '') {
            $('#message-menu a.import').addClass('rcm-ignore');
            rcmail.buttons['import-messages'][0]['act'] += ' rcm-ignore';
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'messagelist', 'menu_source': '#toolbar-menu > li'}); } );
            rcmail.add_onload("rcmail.contextmenu.init_folder('#mailboxlist li', {'menu_source': ['#rcmfolder-menu > ul', '#mailboxoptions-menu > ul > li']})");
        }
        else if (rcmail.env.task == 'mail' && rcmail.env.action == 'compose') {
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'composeto', 'menu_source': '#layout > .sidebar > div.footer', 'list_object': 'contact_list'}, {
                'insertitem': function(p) { rcmail.contextmenu.skin_funcs.compose_menu_text(p); }
            }); } );
        }
        else if (rcmail.env.task == 'addressbook' && rcmail.env.action == '') {
            // on small screens the compose button is not visible so use rcm-active class to ensure it shows up in the menu
            $('#taskmenu > span > a.compose').addClass('rcm-active');
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'contactlist', 'menu_source': ['#taskmenu > span > a.compose', '#toolbar-menu > li'], 'list_object': 'contact_list'}); } );
            rcmail.add_onload("rcmail.contextmenu.init_addressbook('#directorylist li, #savedsearchlist li', {'menu_source': ['#layout > .sidebar > div.footer a.create', '#groupoptions-menu > ul > li']})");
            rcmail.addEventListener('group_insert', function(props) { rcmail.contextmenu.init_addressbook(props.li, {'menu_source': ['#layout > .sidebar > div.footer a.create', '#groupoptions-menu > ul > li']}); } );
            rcmail.addEventListener('abook_search_insert', function(props) { rcmail.contextmenu.init_addressbook(rcmail.savedsearchlist.get_item('S' + props.id), {'menu_source': ['#layout > .sidebar > div.footer a.create', '#groupoptions-menu > ul > li']}); } );
        }

        // Elastic skin uses custom popup handling, not rcmail.menu_stack
        // Contextmenu menu cannot access Elastic's custom way so here we make sure any popups we've created are closed before the next command is run
        rcmail.addEventListener('actionbefore', function(p) {
            if (p.originalEvent && $(p.originalEvent.target).parents('div.' + rcmail.contextmenu.settings.classes.container).length == 1) {
                for (var i = 0; i < rcmail.contextmenu.vars.popup_menus.length; i++) {
                    if (p.name != rcmail.contextmenu.vars.popup_menus[i]) {
                        rcmail.hide_menu(rcmail.contextmenu.vars.popup_menus[i], p.originalEvent);
                    }
                }
            }
        });
    }
});