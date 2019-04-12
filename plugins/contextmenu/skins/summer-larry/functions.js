/**
 * ContextMenu plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2014 Philip Weir
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

rcube_webmail.prototype.context_menu_popup_pattern = /UI\.toggle_popup\(\'([^\']+)\'/;
rcube_webmail.prototype.context_menu_button_active_class = new Array('active');
rcube_webmail.prototype.context_menu_button_disabled_class = new Array('disabled');

function add_menu_text(menu, p) {
	if (menu == 'composeto') {
		if ($(p.item).children('a').hasClass('addto')) {
			$(p.item).children('a').children('span').text($('#compose-contacts div.boxfooter a.addto').attr('title'));
		}
		else if ($(p.item).children('a').hasClass('addcc')) {
			$(p.item).children('a').children('span').text($('#compose-contacts div.boxfooter a.addcc').attr('title'));
		}
		else if ($(p.item).children('a').hasClass('addbcc')) {
			$(p.item).children('a').children('span').text($('#compose-contacts div.boxfooter a.addbcc').attr('title'));
		}
	}
	else if (menu == 'contactlist') {
		if ($(p.item).children('a').hasClass('delete')) {
			$(p.item).children('a').children('span').text($('#addresslist div.boxfooter a.delete').attr('title'));
		}
		else if ($(p.item).children('a').hasClass('removegroup')) {
			$(p.item).children('a').children('span').text($('#addresslist div.boxfooter a.removegroup').attr('title'));
		}
	}
}

function reorder_contact_menu(p) {
	// put export link last
	var ul = p.ref.container.find('ul:first');
	$(p.ref.container).find('a.export').parent('li').appendTo(ul);

	// put assign group link before remove
	$(p.ref.container).find('a.assigngroup').parent('li').insertBefore($(p.ref.container).find('a.removegroup').parent('li'));
}

$(document).ready(function() {
	if (window.rcmail) {
		if (rcmail.env.task == 'mail' && rcmail.env.action == '') {
			rcmail.addEventListener('insertrow', function(props) { rcm_listmenu_init(props.row.id, {'menu_name': 'messagelist', 'menu_source': '#messagetoolbar'}); } );
			rcmail.add_onload("rcm_foldermenu_init('#mailboxlist li', {'menu_source': ['#rcmFolderMenu', '#mailboxoptionsmenu']})");
		}
		else if (rcmail.env.task == 'mail' && rcmail.env.action == 'compose') {
			rcmail.addEventListener('insertrow', function(props) { rcm_listmenu_init(props.row.id, {'menu_name': 'composeto', 'menu_source': '#compose-contacts div.boxfooter', 'list_object': rcmail.contact_list}, {'insertitem': function(p) { add_menu_text('composeto', p); }}); } );
		}
		else if (rcmail.env.task == 'addressbook' && rcmail.env.action == '') {
			rcmail.addEventListener('insertrow', function(props) { rcm_listmenu_init(props.row.id, {'menu_name': 'contactlist', 'menu_source': ['#addressbooktoolbar','#addresslist div.boxfooter a.delete','#addresslist div.boxfooter a.removegroup', '#rcmAddressBookMenu'], 'list_object': rcmail.contact_list}, {
				'insertitem': function(p) { add_menu_text('contactlist', p); },
				'init': function(p) { reorder_contact_menu(p); },
				'afteractivate': function(p) {
					p.ref.list_selection(false, rcmail.env.contextmenu_selection);

					// count the number of groups in the current addressbook
					if (!rcmail.env.group || rcmail.env.readonly)
						p.ref.container.find('a.removegroup').removeClass('active').addClass('disabled');

					var groupcount = 0;
					if (!rcmail.env.readonly && rcmail.env.address_sources[rcmail.env.source] && rcmail.env.address_sources[rcmail.env.source].groups)
						$.each(rcmail.env.contactgroups, function(){ if (this.source === rcmail.env.source) groupcount++ });

					if (groupcount > 0)
						p.ref.container.find('a.assigngroup').removeClass('disabled').addClass('active');
					else
						p.ref.container.find('a.assigngroup').removeClass('active').addClass('disabled');
				},
				'aftercommand': function(p) {
					if ($(p.el).hasClass('active') && p.command == 'group-remove-selected')
						rcmail.command('listgroup', {'source': rcmail.env.source, 'id': rcmail.env.group}, p.el);
				}
			}); } );
			rcmail.add_onload("rcm_abookmenu_init('#directorylist li, #savedsearchlist li', {'menu_source': ['#directorylist-footer', '#groupoptionsmenu']}, {'insertitem': function(p) { add_menu_text('abooklist', p); }})");
			rcmail.addEventListener('group_insert', function(props) { rcm_abookmenu_init(props.li, {'menu_source': ['#directorylist-footer', '#groupoptionsmenu']}); } );
			rcmail.addEventListener('abook_search_insert', function(props) { rcm_abookmenu_init(rcmail.savedsearchlist.get_item('S' + props.id), {'menu_source': ['#directorylist-footer', '#groupoptionsmenu']}); } );
		}
	}
});