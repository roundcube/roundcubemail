/**
 * ContextMenu plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2014-2017 Philip Weir
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
    if ($(p.item).children('a').hasClass('vcard')) {
        $(p.item).children('a').children('span').text($('#abookactions a.vcard').attr('title'));
    }
};

rcube_webmail.prototype.contextmenu.skin_funcs.reorder_addressbook_menu = function(p) {
    // remove the remove from group option from the address book menu
    p.ref.container.find('a.rcm_elem_groupmenulink').remove();
    p.ref.container.find('a.cmd_group-remove-selected').remove();
};

$(document).ready(function() {
    if (window.rcmail) {
        $.extend(true, rcmail.contextmenu.settings, {
            popup_pattern: /rcmail_ui\.show_popup\(\x27([^\x27]+)\x27/,
            classes: {
                button_active: 'active button',
                button_disabled: 'disabled buttonPas'
            }
        });

        if (rcmail.env.task == 'mail' && rcmail.env.action == '') {
            $('#message-menu a.import').addClass('rcm-ignore');
            rcmail.buttons['import-messages'][0]['act'] += ' rcm-ignore';
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'messagelist', 'menu_source': '#messagetoolbar'}); } );
            rcmail.add_onload("rcmail.contextmenu.init_folder('#mailboxlist li', {'menu_source': ['#rcmfoldermenu > ul', '#mailboxoptionsmenu ul']})");
        }
        else if (rcmail.env.task == 'mail' && rcmail.env.action == 'compose') {
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'composeto', 'menu_source': '#abookactions', 'list_object': 'contact_list'}, {
                'insertitem': function(p) { rcmail.contextmenu.skin_funcs.compose_menu_text(p); }
            }); } );
        }
        else if (rcmail.env.task == 'addressbook' && rcmail.env.action == '') {
            rcmail.addEventListener('insertrow', function(props) { rcmail.contextmenu.init_list(props.row.id, {'menu_name': 'contactlist', 'menu_source': ['#abooktoolbar'], 'list_object': 'contact_list'}); } );
            rcmail.add_onload("rcmail.contextmenu.init_addressbook('#directorylist li, #savedsearchlist li', {'menu_source': ['#directorylist-footer', '#groupoptionsmenu ul']}, {'init': function(p) { rcmail.contextmenu.skin_funcs.reorder_addressbook_menu(p); }})");
            rcmail.addEventListener('group_insert', function(props) { rcmail.contextmenu.init_addressbook(props.li, {'menu_source': ['#directorylist-footer', '#groupoptionsmenu ul']}); } );
            rcmail.addEventListener('abook_search_insert', function(props) { rcmail.contextmenu.init_addressbook(rcmail.savedsearchlist.get_item('S' + props.id), {'menu_source': ['#directorylist-footer', '#groupoptionsmenu ul']}); } );
        }
    }
});