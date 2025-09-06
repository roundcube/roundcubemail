// Plugin: contextmenu_folder
// Roundcube Context Menu Folder Manager
// Adds context menus with mailbox operations

// plugin class
function plugin_contextmenu_folder() {
	var self = this;

	// flat mailbox list, total
	this.parent_list = [];

	// flat mailbox list, total
	this.folder_list = [];

	// structured message header, selected
	this.header_list = [];

	// mailbox full path
	this.selected_folder = null;

	// message imap uid
	this.selected_message = null;

	// keep name: css
	this.status_id = 'plugin_contextmenu_folder_status';

	//
	this.collect_special = function() { // keep name
		return self.env('collect_special') || {};
	}

	//
	this.collect_selected = function() { // keep name
		return self.env('collect_selected') || {};
	}

	// 
	this.collect_transient = function() { // keep name
		return self.env('collect_transient') || {};
	}

	// 
	this.collect_predefined = function() { // keep name
		return self.env('collect_predefined') || {};
	}

	// plugin name space
	this.key = function(name) {
		return 'plugin.contextmenu_folder.' + name; // keep in sync with *.php
	}

	// plugin client logger
	this.log = function log(text, force) {
		if (self.env('enable_logging') || force) {
			if (console && console.log) {
				var name = arguments.callee.caller.name;
				var entry = self.key(name);
				var color = force ? 'color: #8B0000' : 'color: #000080'; // red:blue
				console.log('%c' + entry + ': ' + text, color);
			}
		}
	};

	// plugin environment variable
	this.env = function(name) {
		return rcmail.env[self.key(name)];
	}

	// client ui behaviour
	this.has_feature = function has_feature(name) {
		return (self.env('feature_choice') || []).indexOf(name) >= 0;
	}

	// resolve string to jquery
	this.html_by_id = function(id) {
		return id.startsWith('#') ? $(id) : $('[id="' + id + '"]');
	}

	// imap folder path separator
	this.delimiter = function() {
		return rcmail.env.delimiter;
	}

	// auto remove mailbox from transient collection
	function make_expire_transient() {
		self.log('...');
		var expire_mins = self.env('transient_expire_mins');
		var collect_transient = self.collect_transient();
		var current_msec = (new Date()).getTime();
		var minute_msec = 60 * 1000;
		var has_change = false;
		$.each(collect_transient, //
		function make_expire_transient$(mbox, mbox_meta) {
			var created_msec = mbox_meta.created_msec;
			if (created_msec) {
				var elapse_mins = (current_msec - created_msec) / minute_msec;
				if (elapse_mins > expire_mins) {
					self.log(mbox);
					has_change = true;
					delete collect_transient[mbox];
					self.mbox_mark_transient(mbox, false);
				}
			}
		});
		if (has_change) {
			self.save_pref('collect_transient', collect_transient);
			if (self.has_feature('filter_on_expire_transient')) {
				var filter_list = self.filter_list();
				if (filter_list.indexOf('transient') >= 0) {
					self.mbox_filter_apply();
				}
			}
		}
	}

	// update ui
	this.periodic_refresh = function periodic_refresh() {
		self.log('...');
		if (self.has_feature('expire_transient')) {
			make_expire_transient();
		}
	}

	// filters defined by show mode
	this.filter_list = function filter_list(show_mode) {
		var show_mode = show_mode ? show_mode : self.env('show_mode');
		switch (show_mode) {
		default:
		case 'show_all':
			return []; // empty matches all
		case 'show_active':
			return self.env('filter_active');
			break;
		case 'show_favorite':
			return self.env('filter_favorite');
		}
	}

	// folder parent
	this.mbox_root = function mbox_root(mbox) {
		var delimiter = self.delimiter();
		if (mbox.indexOf(delimiter) >= 0) {
			var split = mbox.split(delimiter);
			split.pop();
			return split.join(delimiter);
		} else {
			return '';
		}
	}

	// folder base name
	function mbox_name(mbox) {
		var delimiter = self.delimiter();
		if (mbox.indexOf(delimiter) >= 0) {
			var split = mbox.split(delimiter);
			return split.pop();
		} else {
			return mbox;
		}
	}

	// extra functions
	this.jquery_extend = function jquery_extend() {
		$.fn.extend({
			// match all/any of space separated classes
			hasClass$List : function(type, klaz_text) {
				var mode;
				switch (type) {
				case 'all':
				case 'and':
					mode = true;
					break;
				case 'any':
				case 'or':
					mode = false;
					break;
				default:
					self.log('error: type=' + type, true);
					return false;
				}
				var node = this;
				var klaz_list = klaz_text.split(' ');
				for (var idx = 0, len = klaz_list.length; idx < len; idx++) {
					var has_klaz = $(node).hasClass(klaz_list[idx]);
					if (mode && has_klaz || !mode && !has_klaz) {
						continue;
					} else {
						return !mode;
					}
				}
				return mode;
			},
		});
	}

	// add/rem folder css
	this.mbox_mark = function mbox_mark(mbox, klaz, on) {
		var html_li_a = self.mbox_html_li_a(mbox);
		if (on) {
			html_li_a.addClass(klaz);
		} else {
			html_li_a.removeClass(klaz);
		}
	}

	// add/rem folder css
	this.mbox_mark_span = function mbox_mark_span(mbox, name, on) {
		var html_li_a = self.mbox_html_li_a(mbox);
		var span = html_li_a.find('span[class*="' + name + '"]');
		if (span.length == 0) {
			span = $('<span>').attr('class', name).prependTo(html_li_a);
		}
		var klaz = self.icon_mapa(name); // keep name
		if (on) {
			span.addClass(klaz);
		} else {
			span.removeClass(klaz);
		}
	}

	// add/rem 'selected' folder css
	this.mbox_mark_selected = function mbox_mark_selected(mbox, on) {
		if (self.has_feature('render_selected')) {
			self.mbox_mark_span(mbox, 'mark_selected', on);
		}
	}

	// add/rem 'transient' folder css
	this.mbox_mark_transient = function mbox_mark_transient(mbox, on) {
		if (self.has_feature('render_transient')) {
			self.mbox_mark_span(mbox, 'mark_transient', on);
		}
	}

	// rcmail folder identity
	this.mbox_rcm_id = function mbox_rcm_id(mbox) {
		return 'rcmli' + rcmail.html_identifier_encode(mbox);
	}

	// jquery ui folder object
	this.mbox_html_li = function mbox_html_li(mbox) {
		return self.html_by_id(self.mbox_rcm_id(mbox));
	}

	// jquery ui folder object
	this.mbox_html_li_a = function(mbox) {
		return self.mbox_html_li(mbox).find('a:first');
	}

	// filter assembly
	this.mbox_filter_entry = { // keep keys
		unread : function(mbox) {
			return rcmail.env.unread_counts[mbox] > 0;
		},
		special : function(mbox) {
			return self.collect_special()[mbox] ? true : false;
		},
		selected : function(mbox) {
			return self.collect_selected()[mbox] ? true : false;
		},
		transient : function(mbox) {
			return self.collect_transient()[mbox] ? true : false;
		},
		predefined : function(mbox) {
			return self.collect_predefined()[mbox] ? true : false;
		},
	}

	// provide filtered mailbox view
	this.mbox_filter_apply = function mbox_filter_apply() {

		var show_mode = self.env('show_mode');
		self.log('show_mode=' + show_mode);

		var treelist = rcmail.treelist;
		var container = treelist.container;

		switch (show_mode) {
		default:
		case 'show_all':
			container.find('li').show();
			return;
		case 'show_active':
		case 'show_favorite':
			break;
		}

		var filter_list = self.filter_list(show_mode);
		self.log('filter_list=' + filter_list);

		function match(mbox) {
			for (var idx = 0, len = filter_list.length; idx < len; idx++) {
				var name = filter_list[idx];
				if (self.mbox_filter_entry[name](mbox)) {
					return true;
				}
			}
			return false;
		}

		container.find('li').hide();
		
		container.find('li').each(function filter_apply(index) {
			var html_li = $(this);
			var mbox = html_li.data('id');
			if (match(mbox)) {
				self.mbox_show_tree(mbox);
			}
		});
	}

	// paint folders from 'selected' collection
	this.mbox_render_selected = function mbox_render_selected(on) {
		var collect_selected = self.collect_selected();
		var mbox_list = Object.keys(collect_selected).sort();
		$.each(mbox_list, function(_, mbox) {
			self.mbox_mark_selected(mbox, on);
		});
	}

	// paint folders from 'transient' collection
	this.mbox_render_transient = function mbox_render_transient(on) {
		var collect_transient = self.collect_transient();
		var mbox_list = Object.keys(collect_transient).sort();
		$.each(mbox_list, function(_, mbox) {
			self.mbox_mark_transient(mbox, on);
		});
	}

	// recursively expand and show folder
	this.mbox_show_tree = function mbox_show_tree(mbox) {
		// show folder <li>
		self.mbox_html_li(mbox).show();
		// navigate up tree
		var root = self.mbox_root(mbox);
		if (is_tree_root(root)) {
			return;
		} else {
			self.mbox_show_tree(root);
			rcmail.treelist.expand(root);
		}
	}

	// folder: show, expand, scroll, refresh,
	this.mbox_locate = function mbox_locate(mbox) {
		var mbox = mbox ? mbox : self.mbox_source();
		self.log('mbox: ' + mbox);
		self.mbox_show_tree(mbox);
		rcmail.select_folder(mbox);
		rcmail.env.mailbox = mbox;
		rcmail.refresh_list();
	}

	// message
	this.mesg_locate = function mesg_locate(uid) {
		var uid = uid ? uid : self.selected_message;
		var message_list = rcmail.message_list;
		var rows = message_list ? message_list.rows : {};
		var row = rows[uid] ? rows[uid] : {};
		var row_id = row.id ? row.id : 'invalid';
		self.log('uid: ' + uid + ' row_id: ' + row_id);
		message_list && message_list.select(uid);
	}

	// empty folder collection
	this.mbox_reset_collect = function mbox_reset_collect(collect) {
		self.save_pref(collect, {});
	}

	// convert object to text
	this.json_encode = function(json, tabs) {
		return JSON.stringify(json, null, tabs);
	}

	// convert text to object
	this.json_decode = function(text) {
		return JSON.parse(text);
	}

	// substitution key format
	this.var_key = function(name) {
		return '{' + name + '}';
	}

	// substitution template processor
	this.var_subst = function var_subst(template, mapping) {
		var result = template;
		$.each(mapping, function(key, value) {
			var match = new RegExp(self.var_key(key), 'g');
			result = result.replace(match, value);
		});
		return result;
	}

	// determine folder type
	this.mbox_type = function mbox_type(folder) {
		return self.collect_special()[folder] ? 'special' : 'regular';
	}

	// server request/response processor class
	this.ajax_core = function ajax_core(name, request, response) {
		var core = this;
		core.name = name;
		core.action = self.key(name);
		core.request = function(param) {
			var param = request ? request(param) : param;
			if (param) {
				var lock = rcmail.set_busy(true, core.name);
				rcmail.http_post(core.action, param, lock);
			}
		}
		core.response = function(param) {
			response ? response(param) : true;
		}
		core.bind = function() {
			rcmail.addEventListener(core.action, core.response);
		}
		core.unbind = function() {
			rcmail.removeEventListener(core.action, core.response);
		}
	}

	// track headers on message selection change
	this.ajax_header_list = new self.ajax_core('header_list',
			function conf_header_list(uid) {
				var msg = rcmail.env.messages[uid];
				if (uid && msg && msg.mbox) {
					return {
						uid : uid,
						mbox : msg.mbox,
					};
				} else {
					self.log('missing selection');
					return null; // no post
				}
			}, // 
			function make_header_list(param) {
				self.header_list = param['header_list'];
				self.log('header_list: ' + self.header_list.length)
			});

	// obtain total mailbox collection
	this.ajax_folder_list = new self.ajax_core('folder_list',
			function conf_folder_list() {
				return {};
			}, //
			function make_folder_list(param) {
				self.folder_list = param['folder_list'];
				self.log('folder_list: ' + self.folder_list.length);
				window.setTimeout(self.update_parent_list, 100);
			});

	// apply ui for new messages
	function make_folder_notify(param) {
		var folder = param['folder'];
		self.log('folder: ' + folder);
		// folder auto show for 'unread'
		var filter_list = self.filter_list();
		if (filter_list.indexOf('unread') >= 0) {
			self.mbox_show_tree(folder);
		}
	}

	// reflect new folder messages
	this.ajax_folder_notify = new self.ajax_core('folder_notify', null,
			make_folder_notify);

	// apply ui create/delete/rename
	// see js/treelist.js/rcube_treelist_widget.render_node()
	// see include/rcmail.php/rcmail.render_folder_tree_html()
	function make_folder_update(param) {
		self.log(self.json_encode(param, 4));
		var action = param['action'];
		var source = param['source'];
		var target = param['target'];
		var locate;
		switch (action) {
		case 'create':
			self.mbox_create(target);
			locate = target;
			break;
		case 'delete':
			self.mbox_delete(target);
			locate = self.mbox_root(target);
			break;
		case 'rename':
			self.mbox_rename(source, target);
			locate = target;
			break;
		default:
			self.log('invalid action: ' + action, true);
			return;
		}
		if (is_tree_root(locate)) {
			locate = 'INBOX';
		}
		self.mbox_filter_apply();
		self.mbox_locate(locate);
		self.ajax_folder_list.request();
	}

	// process server folder changes on client
	this.ajax_folder_update = new self.ajax_core('folder_update', null,
			make_folder_update);

	// reflect server folder scan action result
	function make_folder_scan_tree(param) {
		self.log(self.json_encode(param, 4));
		var scan_mode = param['scan_mode'];
		switch (scan_mode) {
		case 'read_this':
		case 'read_tree':
			if (self.has_feature('filter_on_mbox_mark_read')) {
				self.mbox_filter_apply();
			}
			break;
		default:
			self.log('invalid scan_mode: ' + scan_mode, true);
			return;
		}
	}

	// process server folder scan actions
	this.ajax_folder_scan_tree = new self.ajax_core('folder_scan_tree', null,
			make_folder_scan_tree);

	// reflect server folder changes on client
	function make_folder_purge() {
		self.mbox_locate();
	}

	// process server folder changes on client
	this.ajax_folder_purge = new self.ajax_core('folder_purge', null,
			make_folder_purge);

	// plugin ui icons
	this.icon_mapa = function icon_mapa(name) {
		return self.env('icon_mapa')[name];
	}

	// populate context menu item
	this.menu_item = function menu_item(source, entry) {
		source.push({
			props : entry,
			label : self.localize(entry),
			command : self.key(entry),
			classes : 'override ' + self.icon_mapa(entry),
		});
	}

	// delete matching properties
	function object_delete(object, regex) {
		$.each(Object.keys(object), function(_, name) {
			if (name.match(regex)) {
				delete object[name];
			}
		});
	}

	// select matching properties
	function object_select(object, regex) {
		var select = {};
		$.each(Object.keys(object), function(_, name) {
			if (name.match(regex)) {
				select[name] = object[name];
			}
		});
		return select;
	}

	// top of mailbox hierarchy
	function is_tree_root(mbox) {
		return mbox == '';
	}

	// 
	function mbox_tree_regex(mbox) {
		return mbox + '(' + self.delimiter() + '.+)?';
	}

	// ui link as text
	function mbox_link(mbox) {
		var rel = mbox;
		var name = mbox_name(mbox);
		var href = './?_task=mail&_mbox=' + urlencode(mbox);
		var onclick = //
		"return rcmail.command('list','" + mbox + "',this,event)";
		var link = $('<a>').attr({
			rel : rel,
			href : href,
			onclick : onclick,
		}).html(name);
		var html = $('<div>').append(link).html();
		return html;
	}

	// mailbox descriptor in plugin collection
	this.mbox_meta = function mbox_meta(mbox, action) {
		return {
			mbox : mbox,
			action : action,
			created_msec : (new Date()).getTime(),
		}
	}

	// mailbox descriptor in rcmail.env.mailboxes
	this.mbox_info = function mbox_info(mbox) {
		return {
			id : mbox,
			name : mbox_name(mbox),
			virtual : false,
		}
	}

	// mailbox descriptor in rcube_treelist_widget
	this.mbox_node = function mbox_node(mbox) {
		return {
			id : mbox,
			text : mbox_name(mbox),
			html : mbox_link(mbox),
			classes : [ 'mailbox' ],
			children : [],
		};
	}

	// ui object
	this.mbox_create = function mbox_create(mbox, no_tail) {
		var root = self.mbox_root(mbox);
		var has_root = is_tree_root(root) || rcmail.env.mailboxes[root];
		if (!has_root) {
			self.mbox_create(root, true);
		}
		var info = self.mbox_info(mbox);
		var node = self.mbox_node(mbox);
		self.log(mbox);
		rcmail.env.mailboxes[mbox] = info; // model
		rcmail.treelist.insert(node, root, 'mailbox'); // view
		if (no_tail) {
			return;
		} else {
			self.track_on_create(mbox);
			make_rcm_foldermenu_reset();
		}
	}

	// ui object
	this.mbox_delete = function mbox_delete(mbox, no_tail) {
		// strict stem leaf
		var regex = mbox + self.delimiter() + '[^' + self.delimiter() + ']+$';
		var select_list = object_select(rcmail.env.mailboxes, regex);
		var folder_list = Object.keys(select_list);
		$.each(folder_list, function(_, folder) {
			self.mbox_delete(folder, true);
		});
		self.log(mbox);
		delete rcmail.env.mailboxes[mbox]; // model
		rcmail.treelist.remove(mbox); // view
		if (no_tail) {
			return;
		} else {
			self.track_on_delete(mbox);
		}
	}

	// recursively rename ui model entry
	function mbox_tree_rename(base, source, target) {
		var mbox = base.id;
		var mbox_old = mbox;
		var mbox_new = mbox_old.replace(source, target);
		var temp = self.mbox_node(mbox_new);
		base.id = temp.id;
		base.text = temp.text;
		base.html = temp.html;
		self.log(mbox_old + ' -> ' + mbox_new);
		$.each(base.children, function(_, node) {
			mbox_tree_rename(node, source, target);
		});
	}

	// recursively register ui model entry
	function mbox_tree_reg_env(base, action) {
		var mbox = base.id;
		self.log(action + ': ' + mbox);
		switch (action) {
		case 'create':
			rcmail.env.mailboxes[mbox] = self.mbox_info(mbox);
			break;
		case 'delete':
			delete rcmail.env.mailboxes[mbox];
			break;
		default:
			self.log('invalid action: ' + action, true);
			return;
		}
		$.each(base.children, function(_, node) {
			mbox_tree_reg_env(node, action);
		});
	}

	// recursively transfer 'unread' counts
	function mbox_transfer_unread(base, source, target) {
		var mbox = base.id;
		var unread_counts = rcmail.env.unread_counts || {};
		var count = unread_counts[mbox];
		if (count) {
			var mbox_old = mbox;
			var mbox_new = mbox.replace(source, target);
			self.log(mbox_old + ' -> ' + mbox_new);
			delete unread_counts[mbox_old];
			rcmail.set_unread_count(mbox_new, count);
		}
		$.each(base.children, function(_, node) {
			mbox_transfer_unread(node, source, target);
		});
	}

	// recursively transfer 'selected' collection
	function mbox_transfer_selected(base, source, target, no_save) {
		var mbox = base.id;
		var collect_selected = self.collect_selected();
		var mbox_meta = collect_selected[mbox];
		if (mbox_meta) {
			var mbox_old = mbox;
			var mbox_new = mbox.replace(source, target);
			self.log(mbox_old + ' -> ' + mbox_new);
			mbox_meta.mbox = mbox_new;
			delete collect_selected[mbox_old];
			collect_selected[mbox_new] = mbox_meta;
			self.mbox_mark_selected(mbox_old, false);
			self.mbox_mark_selected(mbox_new, true);
		}
		$.each(base.children, function(_, node) {
			mbox_transfer_selected(node, source, target, true);
		});
		if (no_save) {
			return;
		} else {
			self.save_pref('collect_selected', collect_selected);
		}
	}

	// define context menu event listeners on the tree
	function make_rcm_foldermenu_init(mbox) {
		var item = rcmail.treelist.get_item(mbox);
		var item_id = '#' + item.id;
		self.log(mbox + ' [' + item_id + ']');
		var selector = [ item_id, item_id + ' li ', ].join(',');
		rcm_foldermenu_init(selector, { // plugin:contextmenu
			'menu_source' : [ '#rcmFolderMenu', '#mailboxoptionsmenu' ]
		});
	}

	// define context menu event listeners on the tree
	function make_rcm_foldermenu_reset() {
		self.log('...');
		var selector = '#mailboxlist li';
		$(selector).off('click contextmenu');
		rcm_foldermenu_init(selector, { // plugin:contextmenu
			'menu_source' : [ '#rcmFolderMenu', '#mailboxoptionsmenu' ]
		});
	}

	// ui object
	this.mbox_rename = function mbox_rename(source, target) {
		var root = self.mbox_root(source);
		var node_old = rcmail.treelist.get_node(source);
		var node_new = $.extend(true, {}, node_old); // clone
		mbox_tree_rename(node_new, source, target);
		// delete
		mbox_tree_reg_env(node_old, 'delete'); // model
		rcmail.treelist.remove(source); // view
		self.track_on_delete(source);
		// create
		mbox_tree_reg_env(node_new, 'create'); // model
		rcmail.treelist.insert(node_new, root, 'mailbox'); // view
		self.track_on_create(target);
		// update
		mbox_transfer_unread(node_old, source, target);
		mbox_transfer_selected(node_old, source, target);
		make_rcm_foldermenu_reset();
	}

	// transient collection
	this.track_on_create = function track_on_create(mbox) {
		var track = self.has_feature('track_on_create')
				|| self.has_feature('track_on_rename');
		if (track) {
			self.mbox_mark_transient(mbox, true);
			var collect_transient = self.collect_transient();
			collect_transient[mbox] = self.mbox_meta(mbox, 'create');
			self.save_pref('collect_transient', collect_transient);
		}
	}

	// transient collection
	this.track_on_delete = function track_on_delete(mbox) {
		var track = self.has_feature('track_on_delete')
				|| self.has_feature('track_on_rename');
		if (track) {
			self.mbox_mark_transient(mbox, false);
			var collect_transient = self.collect_transient();
			object_delete(collect_transient, mbox_tree_regex(mbox));
			self.save_pref('collect_transient', collect_transient);
		}
	}

	// transient collection
	this.track_on_locate = function track_on_locate(mbox) {
		var track = self.has_feature('track_on_locate');
		if (track) {
			self.mbox_mark_transient(mbox, true);
			var collect_transient = self.collect_transient();
			collect_transient[mbox] = self.mbox_meta(mbox, 'locate');
			self.save_pref('collect_transient', collect_transient);
		}
	}

	// extract top level mailbox list
	this.update_parent_list = function() {
		var delimiter = self.delimiter();
		var index, length, folder, parent_list = [];
		length = self.folder_list.length;
		for (index = 0; index < length; ++index) {
			folder = self.folder_list[index];
			if (folder.indexOf(delimiter) === -1) {
				parent_list.push({
					folder : folder,
				});
			}
		}
		self.parent_list = parent_list;
	}

	// TODO
	this.update_collect = function update_collect(action, name, mbox) {
		var collect = name; // XXX
		switch (action) {
		case 'create':
			collect[mbox] = mbox;
			break;
		case 'delete':
			delete collect[mbox];
			break;
		}
	}

	// remember between sessions
	this.remember_current_mailbox = function remember_current_mailbox(mailbox) {
		if (self.has_feature('remember_mailbox')) {
			var mailbox = mailbox ? mailbox : rcmail.env.mailbox;
			self.save_pref('memento_current_mailbox', mailbox);
		}
	}

	// remember between sessions
	this.remember_current_message = function remember_current_message(message) {
		if (self.has_feature('remember_message')) {
			var message = message ? message : self.selected_message;
			self.save_pref('memento_current_message', message);
		}
	}

	// provide localization
	this.localize = function localize(name) {
		return rcmail.get_label(name, 'contextmenu_folder');
	}

	// discover folder to be used by the command
	this.mbox_source = function mbox_source(param) {
		var source;
		if (self.selected_folder) {
			source = self.selected_folder;
			self.log('self.selected_folder');
		} else if (rcmail.env.mailbox) {
			source = rcmail.env.mailbox;
			self.log('rcmail.env.mailbox');
		} else if (param) {
			source = param;
			self.log('param');
		} else {
			source = '';
			self.log('missing source', true);
		}
		return source;
	}

	// command helper
	this.register_command = function(name) {
		rcmail.register_command(self.key(name), self[name].bind(self), true);
	}

	// publish rcmail plugin commands
	this.register_command_list = function() {
		var command_list = [ //

		'contact_folder_create', //

		'folder_create', //
		'folder_delete', //
		'folder_locate', //
		'folder_rename', //

		'folder_purge', //

		'folder_select', //
		'folder_unselect', //

		'folder_read_this', //
		'folder_read_tree', //

		'show_all', //
		'show_active', //
		'show_favorite', //

		'reset_selected', //
		'reset_transient', //

		'message_copy', //
		'message_move', //

		];

		$.each(command_list, function(index, command) {
			self.register_command(command);
		});
	}

	// persist user settings on client and server
	this.save_pref = function save_pref(name, value, no_dump) {
		var key = self.key(name);
		rcmail.save_pref({
			env : key,
			name : key,
			value : value,
		});
		self.log(name + '=' + (no_dump ? '...' : self.json_encode(value, 4)));
	}

	// verify purge command context
	this.has_allow_purge = function has_allow_purge(mbox) {
		if (self.has_feature('allow_purge_any')) {
			self.log('any');
			return true;
		}
		if (self.has_feature('allow_purge_junk')) {
			var junk = rcmail.env.junk_mailbox;
			if (mbox.match(mbox_tree_regex(junk))) {
				self.log('junk');
				return true;
			}
		}
		if (self.has_feature('allow_purge_trash')) {
			var trash = rcmail.env.trash_mailbox;
			if (mbox.match(mbox_tree_regex(trash))) {
				self.log('trash');
				return true;
			}
		}
		if (self.has_feature('allow_purge_regex')) {
			var regex = self.env('allow_purge_regex');
			if (mbox.match(regex)) {
				self.log('regex');
				return true;
			}
		}
		self.log('none');
		return false;
	}

	// apply action to jquery selector list
	this.selector_action = function selector_action(action, list_name) {
		var selector_list = self.env(list_name) || [];
		$.each(selector_list, function selector_action$(_, selector) {
			var element = $(selector);
			self.log(action + ': ' + selector);
			if (element.length) {
				switch (action) {
				case 'show':
					element.show();
					break;
				case 'hide':
					element.hide();
					break;
				default:
					self.log('invalid action: ' + action, true);
				}
			} else {
				self.log('invalid element: ' + selector, true);
			}
		});
	}

	// jquery dialog title icon
	this.dialog_icon = function(dialog, name) {
		dialog.find('span.ui-dialog-title').addClass(
				'plugin_contextmenu_folder title_icon ' + name);
	}

	// convert keys to clicks
	this.key_enter = function key_enter(id, event) {
		switch (event.which) {
		case 9: // tab
		case 27: // esc
			return true; // event fire
		case 13: // enter
		case 32: // space
			self.html_by_id(id).click();
		default:
			return false; // event stop
		}
	}

	// buttons builder
	this.dialog_buttons = function(submit, cancel) {
		return [ {
			id : 'submit',
			text : self.localize( //
			submit && submit.name ? submit.name : 'submit'),
			'class' : 'mainaction',
			click : function() {
				if (!$('#submit').prop('disabled')) {
					submit && submit.func ? submit.func() : true;
				}
				$(this).dialog('close');
			},
			keydown : self.key_enter.bind(null, 'submit'),
		}, {
			id : 'cancel',
			text : self.localize( //
			cancel && cancel.name ? cancel.name : 'cancel'),
			click : function() {
				if (!$('#cancel').prop('disabled')) {
					cancel && cancel.func ? cancel.func() : true;
				}
				$(this).dialog('close');
			},
			keydown : self.key_enter.bind(null, 'cancel'),
		} ];

	}

	// options builder
	this.dialog_options = function(icon_name, func_open, func_close) {
		var icon = self.icon_mapa(icon_name);
		return {
			open : function open(event, ui) {
				self.dialog_icon($(this).parent(), icon ? icon : '');
				self.has_dialog = true;
				func_open ? func_open(event, ui) : true;
			},
			close : function close(event, ui) {
				func_close ? func_close(event, ui) : true;
				self.has_dialog = false;
				$(this).remove();
			},
		};
	}

	//
	this.is_plugin_active = function is_plugin_active() {
		return self.env('activate_plugin');
	}

	// //

	this.initialize();

}

// plugin setup
plugin_contextmenu_folder.prototype.initialize = function initialize() {
	var self = this;

	if (self.is_plugin_active()) {
		self.log('active');
	} else {
		self.log('inactive');
		return;
	}

	if (rcmail.env['framed']) {
		self.log('error: framed', true);
		return;
	}

	// control resource select/unselect
	function rcmail_menu_work(action, param) {
		var name = param.name;
		var operation = name + ': ' + action + ': ';
		if (name == 'rcm_folderlist') { // plugin:contextmenu
			if (action == 'open') {
				self.selected_folder = rcmail.env.context_menu_source_id;
			}
			if (action == 'close') {
				self.selected_folder = null;
			}
			self.log(operation + self.selected_folder);
		}
		if (name == 'rcm_messagelist') { // plugin:contextmenu
			if (action == 'open') {
				self.selected_message = rcmail.get_single_uid();
				self.ajax_header_list.request(self.selected_message);
			}
			if (action == 'close') {
				self.selected_message = null;
			}
			self.log(operation + self.selected_message);
		}
	}

	// use folder select for context menu source
	function rcmail_select_folder(param) {
		var folder = param.folder;
		if (folder) {
			self.log(folder);
			rcmail.env.context_menu_source_id = folder;
			self.remember_current_mailbox(folder);
		} else {
			self.log('...');
		}
	}

	// use message select for context menu headers
	function rcmail_select_message(widget) {
		var message = rcmail.get_single_uid();
		if (message) {
			self.log(message);
			self.selected_message = message;
			self.remember_current_message(message);
			window.setTimeout(function prevent_race_save_pref() {
				self.ajax_header_list.request(message);
			}, 100);
		} else {
			self.log('...');
		}
	}
	
	self.ajax_header_list.bind();
	self.ajax_folder_list.bind();
	self.ajax_folder_purge.bind();
	self.ajax_folder_notify.bind();
	self.ajax_folder_update.bind();
	self.ajax_folder_scan_tree.bind();

	self.register_command_list();

	self.ajax_folder_list.request();

	// delayed plugin setup
	function plugin_setup() {

		self.mbox_render_selected(true);
		self.mbox_render_transient(true);

		// FIXME replace delays with ready-events
		window.setTimeout(function remember_filter() {
			self.log('...');
			if (self.has_feature('remember_filter')) {
				self.mbox_filter_apply();
			}
			window.setTimeout(function remember_mailbox() {
				self.log('...');
				if (self.has_feature('remember_mailbox')) {
					self.mbox_locate(self.env('memento_current_mailbox'));
				}
				window.setTimeout(function remember_message() {
					self.log('...');
					if (self.has_feature('remember_message')) {
						self.mesg_locate(self.env('memento_current_message'));
					}
				}, 500);
			}, 500);
		}, 500);

		var minute_msec = 60 * 1000;
		window.setInterval(self.periodic_refresh, minute_msec);
	}

	// delayed plugin setup
	function rcmail_responseafter_getunread(param) {
		var response = param.response;
		plugin_setup();
		rcmail.removeEventListener('responseaftergetunread', rcmail_responseafter_getunread);
	}
	rcmail.addEventListener('responseaftergetunread', rcmail_responseafter_getunread);

	rcmail.addEventListener('menu-open', rcmail_menu_work.bind(null, 'open'));
	rcmail.addEventListener('menu-close', rcmail_menu_work.bind(null, 'close'));
	rcmail.addEventListener('selectfolder', rcmail_select_folder);
	
	if (rcmail.message_list) {
		rcmail.message_list.addEventListener('select', rcmail_select_message);
	}

}

// dialog content
plugin_contextmenu_folder.prototype.html_list = function html_list(args, opts) {
	var self = this;

	var field_list = args.field_list;
	var entry_list = args.entry_list;

	function part_id(name) {
		return args.name + '_' + name;
	}

	var content = $('<div>').attr({
		id : part_id('root'),
		'class' : 'uibox',
		style : 'max-height: 10em; overflow-x: hidden; overflow-y: auto;',
	});

	var table = $('<table>').attr({
		id : part_id('list'),
		role : 'listbox',
		'class' : 'records-table sortheader fixedheader', // TODO
	});
	content.append(table);

	var head = $('<thead>').attr({
		id : part_id('head'),
	});
	table.append(head);
	var row = $('<tr>');
	head.append(row);
	$.each(field_list, function(index, field) {
		row.append($('<th>').text(self.localize(field)));
	});

	var body = $('<tbody>').attr({
		id : part_id('body'),
	});
	table.append(body);

	var widget = new rcube_list_widget(table[0], opts).init();

	var inst_list = rcube_list_widget._instances;
	if ($.isArray(inst_list && inst_list[inst_list.length - 1]) == widget) {
		inst_list.pop(); // transient table, remove self
	}

	content.widget = widget;

	content.select = function content_select(id) { // row id
		self.log(args.name + ': ' + id);
		widget.select(id);
	}

	content.choice = function content_choice() { // selected object
		var id = widget.get_single_selection();
		return content.entry_list[id];
	}

	content.build = function content_build(entry_list) {
		content.entry_list = entry_list;
		widget.clear(true);
		$.each(entry_list, function(row_id, entry) {
			var cols = [];
			$.each(field_list, function(col_id, field) {
				cols.push({
					innerHTML : entry[field],
				});
			});
			widget.insert_row({
				id : 'rcmrow' + row_id,
				cols : cols,
			});
		});
	}

	content.build(entry_list);

	return content;
}

// dialog content
plugin_contextmenu_folder.prototype.html_locate = function html_locate(args) {
	var self = this;

	$.expr[':'].match = function(e, i, m) {
		return $(e).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
	};

	var control_keys = // only non-edit
	[ 9, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40 ];

	function has_value(entry) {
		return entry && entry.val();
	}

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55, // cols
	}).keydown(function(event) { // scroll folder list
		var target = $('#target');
		var option = target.find('option:selected');
		if (!(has_value(option))) {
			return;
		}
		switch (event.which) {
		case 38: // arrow up
			option = option.prevAll(':visible:first');
			break;
		case 40: // arrow down
			option = option.nextAll(':visible:first');
			break;
		default:
			return;
		}
		event.preventDefault();
		if (has_value(option)) {
			target.val(option.val());
		}
	}).keyup(function(event) { // search while typing
		if (event.which == 13) {
			$('#submit').click();
			return;
		}
		if ($.inArray(event.which, control_keys) > -1) {
			return;
		}
		var source = $('#source');
		var target = $('#target');
		var filter = source.val();
		if (filter) {
			target.find('option:not(:match(' + filter + '))').hide();
			target.find('option:match(' + filter + ')').show();
		} else {
			target.find('option').show();
		}
		var option = target.find('option:visible:first');
		if (has_value(option)) {
			target.val(option.val());
		}
	});

	var target_input = $('<select>').prop({
		id : 'target',
		style : 'width: 100%; overflow: hidden;',
		size : 20, // rows
	}).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	}).dblclick(function(event) {
		$('#submit').click();
	});

	function build_folder_list() {
		var source = $('#source');
		var target = $('#target');
		var folder_list = self.folder_list;
		$.each(folder_list, function(index, folder) {
			target.append($('<option>').prop('value', index).text(folder));
		});
		source.trigger($.Event('keyup', { // select first
			which : 0
		}));
	}

	window.setTimeout(build_folder_list, 10);

	var source_label = $('<label>').text(self.localize('search'));
	var target_label = $('<label>').text(self.localize('folder'));

	var content = $('<table>');
	content.append($('<tr>').append($('<td>').append(source_label)).append(
			$('<td>').append(source_input)));
	content.append($('<tr>').append(
			$('<td>').prop('colspan', 2).append(target_input)));

	return content;
}

// plugin command
plugin_contextmenu_folder.prototype.contact_folder_create = function contact_folder_create() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var uid = self.selected_message;
	var msg = rcmail.env.messages[uid];
	self.log('uid: ' + uid);

	var content = $('<div>');

	var parent_part = self.html_list({
		name : 'parent_part',
		field_list : [ 'folder' ],
		entry_list : self.parent_list,
	});

	parent_part.widget.addEventListener('select', function(widget) {
		var item = widget.get_single_selection();
		if (item) {
			self.save_pref('memento_contact_parent_item', item);
			update_format_part();
		}
	});

	var header_part = self.html_list({
		name : 'header_part',
		field_list : [ 'type', 'full_name', 'mail_addr', ],
		entry_list : self.header_list,
	});

	header_part.widget.addEventListener('select', function(widget) {
		var item = widget.get_single_selection();
		if (item) {
			self.save_pref('memento_contact_header_item', item);
			update_format_part();
		}
	});

	var format_part = self.html_list({
		name : 'format_part',
		field_list : [ 'folder' ],
		entry_list : [], // build on demand
	});

	format_part.widget.addEventListener('select', function(widget) {
		var item = widget.get_single_selection();
		if (item) {
			self.save_pref('memento_contact_format_item', item);
			update_folder_part();
		}
	});

	var folder_part = $('<input>').attr({
		id : 'target',
		style : 'width:100%;',
	}).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	});

	function section(id) {
		return $('<label>').css({
			'font-weight' : 'bold',
		}).text(self.localize(id));
	}

	content.append(section('parent'));
	content.append(parent_part);
	content.append($('<p>'));
	content.append(section('header'));
	content.append(header_part);
	content.append($('<p>'));
	content.append(section('format'));
	content.append(format_part);
	content.append($('<p>'));
	content.append(section('folder'));
	content.append($('<br>'));
	content.append(folder_part);

	function update_format_part() {
		var parent = parent_part.choice();
		var header = header_part.choice();
		if (!parent || !header) {
			return;
		}
		var mapping = { // store key=value
			parent : parent.folder,
		};
		$.each(header, function(key, value) { // store key=value
			mapping[key] = value;
		});
		var entry_list = [];
		var format_list = self.env('contact_folder_format_list');
		$.each(format_list, function(index, format) { // substitute format
			var folder = self.var_subst(format, mapping);
			entry_list.push({
				folder : folder,
			});
		});
		format_part.build(entry_list);
		format_part.select(self.env('memento_contact_format_item'));
	}

	function update_folder_part() {
		var format = format_part.choice();
		if (!format) {
			return;
		}
		folder_part.val(format.folder);
	}

	var title = self.localize('folder_create');

	var ajax = new self.ajax_core('folder_create', function request() {
		var target = $('#target');
		return {
			target : target.val(),
		}
	});

	var buttons = self.dialog_buttons({
		name : 'create',
		func : ajax.request,
	});

	function open() {
		parent_part.select(self.env('memento_contact_parent_item'));
		header_part.select(self.env('memento_contact_header_item'));
		format_part.select(self.env('memento_contact_format_item'));
	}

	var options = self.dialog_options('folder_create', open);

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_create = function folder_create() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var source = self.mbox_source();
	if (!source) {
		return;
	}

	var target = source + '/';

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled',
	}).val(source);

	var target_input = $('<input>').prop({
		id : 'target',
		type : 'text',
		size : 55
	}).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	}).on('input', function(event) {
		render();
	}).val(target);

	function render() {
		$('#submit').prop('disabled', source == $('#target').val());
	}

	var source_label = $('<label>').text(self.localize('folder'));
	var target_label = $('<label>').text(self.localize('folder'));

	var content = $('<table>');
	// content.append($('<tr>').append($('<td>').append(source_label)).append(
	// $('<td>').append(source_input)));
	content.append($('<tr>').append($('<td>').append(target_label)).append(
			$('<td>').append(target_input)));

	var title = self.localize('folder_create');

	var ajax = new self.ajax_core('folder_create', function request() {
		var source = $('#source');
		var target = $('#target');
		return {
			source : source.val(),
			target : target.val(),
		}
	});

	var buttons = self.dialog_buttons({
		name : 'create',
		func : ajax.request,
	});

	var options = self.dialog_options('folder_create');

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_delete = function folder_delete() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var source = self.mbox_source();
	if (!source) {
		return;
	}

	var target = source;

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled'
	}).val(source);

	var target_input = $('<input>').prop({
		id : 'target',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled'
	}).val(target).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	});

	var source_label = $('<label>').text(self.localize('folder'));
	var target_label = $('<label>').text(self.localize('folder'));

	var content = $('<table>');
	// content.append($('<tr>').append($('<td>').append(source_label)).append(
	// $('<td>').append(source_input)));
	content.append($('<tr>').append($('<td>').append(target_label)).append(
			$('<td>').append(target_input)));

	var title = self.localize('folder_delete');

	var ajax = new self.ajax_core('folder_delete', function request() {
		var source = $('#source');
		var target = $('#target');
		return {
			source : source.val(),
			target : target.val(),
		}
	});

	var buttons = self.dialog_buttons({
		name : 'delete',
		func : ajax.request,
	});

	function render() {
		var type = self.mbox_type(source);
		if (type == 'special') {
			$('#submit').prop('disabled', true);
		}
	}

	function open() {
		render();
	}

	var options = self.dialog_options('folder_delete', open);

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_rename = function folder_rename() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var source = self.mbox_source();
	if (!source) {
		return;
	}

	var target = source;

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled'
	}).val(source);

	var target_input = $('<input>').prop({
		id : 'target',
		type : 'text',
		size : 55
	}).val(target).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	}).on('input', function(event) {
		render();
	});

	var source_label = $('<label>').text(self.localize('source'));
	var target_label = $('<label>').text(self.localize('target'));

	var content = $('<table>');
	content.append($('<tr>').append($('<td>').append(source_label)).append(
			$('<td>').append(source_input)));
	content.append($('<tr>').append($('<td>').append(target_label)).append(
			$('<td>').append(target_input)));

	var title = self.localize('folder_rename');

	var ajax = new self.ajax_core('folder_rename', function request() {
		var source = $('#source');
		var target = $('#target');
		return {
			source : source.val(),
			target : target.val(),
		}
	});

	var buttons = self.dialog_buttons({
		name : 'rename',
		func : ajax.request,
	});

	function render() {
		var type = self.mbox_type(source);
		if (type == 'special') {
			$('#submit').prop('disabled', true);
			return;
		}
		$('#submit').prop('disabled', source == $('#target').val());
	}

	function open() {
		render();
	}

	var options = self.dialog_options('folder_rename', open);

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_locate = function folder_locate() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var content = self.html_locate();

	var title = self.localize('folder_locate');

	function locate() {
		var source = $('#source');
		var target = $('#target');
		var option = target.find('option:selected');
		var folder = option.text();
		self.mbox_locate(folder);
		self.track_on_locate(folder);
	}

	var buttons = self.dialog_buttons({
		name : 'locate',
		func : locate,
	});

	function open() {
		$('#source').val(self.env('memento_folder_locate_text')).focus()
				.select();
	}

	function close() {
		self.save_pref('memento_folder_locate_text', $('#source').val());
	}

	var options = self.dialog_options('folder_locate', open, close);

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_select = function folder_select() {
	var self = this;
	self.folder_change_select('folder_select');
}

// plugin command
plugin_contextmenu_folder.prototype.folder_unselect = function folder_unselect() {
	var self = this;
	self.folder_change_select('folder_unselect');
}

// command provider
plugin_contextmenu_folder.prototype.folder_change_select = function folder_change_select(
		props) {
	var self = this;

	var mode = props;
	var source = self.mbox_source();
	var target = source;
	if (!source) {
		return;
	}

	var collect_selected = self.collect_selected();
	switch (mode) {
	case 'folder_select':
		collect_selected[source] = self.mbox_meta(source, 'select');
		self.mbox_mark_selected(source, true);
		break;
	case 'folder_unselect':
		delete collect_selected[source];
		self.mbox_mark_selected(source, false);
		break;
	default:
		self.log('invalid mode: ' + mode);
		break;
	}
	self.save_pref('collect_selected', collect_selected);

}

// plugin command
plugin_contextmenu_folder.prototype.folder_read_this = function folder_read_this() {
	var self = this;
	self.folder_scan_tree('read_this');
}

// plugin command
plugin_contextmenu_folder.prototype.folder_read_tree = function folder_read_tree() {
	var self = this;
	self.folder_scan_tree('read_tree');
}

// command provider
plugin_contextmenu_folder.prototype.folder_scan_tree = function folder_scan_tree(
		props) {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var scan_mode = props;
	var source = self.mbox_source();
	var target = source;

	if (!source) {
		return;
	}

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled',
	}).val(source);

	var target_input = $('<textarea>').prop({
		id : 'target',
		rows : 7,
		cols : 55,
		readonly : 'true',
		disabled : 'disabled',
	}).val('');

	var folder_rx = '';
	switch (scan_mode) {
	case 'read_this':
		folder_rx = new RegExp('^' + source + '$');
		break;
	case 'read_tree':
		folder_rx = new RegExp('^' + source + '/.+$');
		break;
	}

	$.each(self.folder_list, function(_, folder) {
		var has_match = folder_rx.test(folder);
		var has_unread = self.mbox_filter_entry.unread(folder);
		if (has_match && has_unread) {
			target_input.val(target_input.val() + folder + '\n');
		}
	});

	var source_label = $('<label>').text(self.localize('folder'));
	var target_label = $('<label>').text(self.localize(''));

	var content = $('<table>');
	content.append($('<tr>').append($('<td>').append(source_label)).append(
			$('<td>').append(source_input)));
	content.append($('<tr>').append($('<td>').append(target_label)).append(
			$('<td>').append(target_input)));

	var title = self.localize('folder_' + scan_mode);

	function post_ajax() {
		self.ajax_folder_scan_tree.request({
			target : target,
			scan_mode : scan_mode,
		});
	}

	var buttons = self.dialog_buttons({
		name : 'apply',
		func : post_ajax,
	});

	var options = self.dialog_options('folder_read_tree');

	rcmail.show_popup_dialog(content, title, buttons, options);

}

// plugin command
plugin_contextmenu_folder.prototype.reset_selected = function reset_selected() {
	var self = this;
	self.reset_collect('selected');
}

// plugin command
plugin_contextmenu_folder.prototype.reset_transient = function reset_transient() {
	var self = this;
	self.reset_collect('transient');
}

// command provider
plugin_contextmenu_folder.prototype.reset_collect = function reset_collect(
		props) {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var source = rcmail.env.context_menu_source_id;
	var target = rcmail.env.mailbox;

	var mode = 'reset_' + props;
	var title = self.localize(mode);
	var collect = 'collect_' + props;

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled'
	}).val(source);

	var target_input = $('<select>').prop({
		id : 'target',
		style : 'width: 40em; overflow: hidden;',
		size : 20, // rows
	}).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	});

	var source_label = $('<label>').text(self.localize('folder'));
	var target_label = $('<label>').text(self.localize('folder'));

	var content = $('<table>');
	// content.append($('<tr>').append($('<td>').append(source_label)).append(
	// $('<td>').append(source_input)));
	content.append($('<tr>').append(
			$('<td>').prop('colspan', 2).append(target_input)));

	function reset() {
		if (collect == 'collect_selected') {
			self.mbox_render_selected(false);
		}
		if (collect == 'collect_transient') {
			self.mbox_render_transient(false);
		}
		self.mbox_reset_collect(collect);
	}

	function display() {
		var target = $('#target');
		var func = self[collect];
		var folder_list = Object.keys(func()).sort();
		$.each(folder_list, function(index, folder) {
			$('<option>').prop('value', index).text(folder).appendTo(target);
		});
	}

	var buttons = self.dialog_buttons({
		name : 'reset',
		func : reset,
	});

	function open() {
		display();
	}

	function close() {
		self.mbox_filter_apply();
	}

	var options = self.dialog_options(mode, open, close);

	rcmail.show_popup_dialog(content, title, buttons, options);

}

// plugin command
plugin_contextmenu_folder.prototype.show_all = function show_all() {
	var self = this;
	self.show_mode('show_all');
}

// plugin command
plugin_contextmenu_folder.prototype.show_active = function show_active() {
	var self = this;
	self.show_mode('show_active');
}

// plugin command
plugin_contextmenu_folder.prototype.show_favorite = function show_favorite() {
	var self = this;
	self.show_mode('show_favorite');
}

// command provider
plugin_contextmenu_folder.prototype.show_mode = function show_mode(props) {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	self.log('props: ' + props);

	var source = rcmail.env.context_menu_source_id;
	var target = rcmail.env.mailbox;
	var show_mode = props;

	self.save_pref('show_mode', show_mode);
	self.html_by_id(self.status_id).trigger('show_mode');

	self.mbox_filter_apply();
	self.mbox_locate();

}

// plugin command
plugin_contextmenu_folder.prototype.message_copy = function message_copy() {
	var self = this;
	self.message_transfer('message_copy');
}

// plugin command
plugin_contextmenu_folder.prototype.message_move = function message_move() {
	var self = this;
	self.message_transfer('message_move');
}

// command provider
plugin_contextmenu_folder.prototype.message_transfer = function message_transfer(
		props) {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var action = props;
	var content = self.html_locate();

	var title = 'invalid';
	var command = function command(mbox) {
		self.log(title);
	};

	switch (action) {
	case 'message_copy':
		title = self.localize('message_copy');
		command = rcmail.copy_messages.bind(rcmail);
		break;
	case 'message_move':
		title = self.localize('message_move');
		command = rcmail.move_messages.bind(rcmail);
		break;
	default:
		self.log('invalid action: ' + action, true);
		return;
	}

	function post_ajax() {
		var target = $('#target');
		var option = target.find('option:selected');
		if (option && option.val()) {
			var folder = option.text();
			self.log(action + ': ' + folder);
			self.track_on_locate(folder);
			command(folder); // rcmail command
		} else {
			self.log(action + ': ' + 'missing folder');
		}
	}

	var buttons = self.dialog_buttons({
		name : 'apply',
		func : post_ajax,
	});

	function open() {
		$('#source').val(self.env('memento_folder_locate_text')).focus()
				.select();
	}

	function close() {
		self.save_pref('memento_folder_locate_text', $('#source').val());
	}

	var options = self.dialog_options('folder_locate', open, close);

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// plugin command
plugin_contextmenu_folder.prototype.folder_purge = function folder_purge() {
	var self = this;

	if (self.has_dialog) {
		return;
	}

	var source = self.mbox_source();
	var target = source;

	if (!source) {
		return;
	}

	var source_input = $('<input>').prop({
		id : 'source',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled',
	}).val(source);

	var target_input = $('<input>').prop({
		id : 'target',
		type : 'text',
		size : 55,
		readonly : 'true',
		disabled : 'disabled'
	}).val(target).keypress(function(event) {
		if (event.which == 13) {
			$('#submit').click();
		}
	});

	var source_label = $('<label>').text(self.localize('folder'));
	var target_label = $('<label>').text(self.localize('folder'));

	var content = $('<table>');
	// content.append($('<tr>').append($('<td>').append(source_label)).append(
	// $('<td>').append(source_input)));
	content.append($('<tr>').append($('<td>').append(target_label)).append(
			$('<td>').append(target_input)));

	var title = self.localize('folder_purge');

	function post_ajax() {
		self.ajax_folder_purge.request({
			source : source,
			_mbox : source,
			_reload : 1,
		});
	}

	var buttons = self.dialog_buttons({
		name : 'apply',
		func : post_ajax,
	});

	var options = self.dialog_options('folder_purge');

	rcmail.show_popup_dialog(content, title, buttons, options);
}

// menu setup
plugin_contextmenu_folder.prototype.mbox_list_control_menu = function mbox_list_control_menu() {
	var self = this;
	var enable = self.env('enable_folder_list_control_menu');
	self.log('enable: ' + enable);
	if (!enable) {
		return;
	}

	// default footer button
	var menu_link = $('#mailboxmenulink');
	if (!menu_link.length) {
		self.log('missing menu_link', true);
		return;
	}

	// plugin control button
	var ctrl_link = $('<a>').prop({
		id : self.status_id,
		title : self.localize('status_title'),
		href : '#',
	})

	// icon render target
	var ctrl_span = $('<span>').appendTo(ctrl_link);

	// display status icon
	ctrl_link.on('show_mode', function render_icon(event) {
		var show_mode = self.env('show_mode');
		ctrl_span.attr('class', self.icon_mapa(show_mode));
	});

	ctrl_link.trigger('show_mode');

	var menu_src = self.key('status_src');
	var menu_name = self.key('status_menu');

	var menu_source = [ menu_src ];

	self.menu_item(menu_source, 'show_all');
	self.menu_item(menu_source, 'show_active');
	self.menu_item(menu_source, 'show_favorite');
	self.menu_item(menu_source, 'reset_selected');
	self.menu_item(menu_source, 'reset_transient');

	menu_source.push({
		label : self.localize('folder_expand_all'),
		command : 'plugin.contextmenu.expandall',
		props : '',
		classes : 'expandall'
	});
	menu_source.push({
		label : self.localize('folder_collapse_all'),
		command : 'plugin.contextmenu.collapseall',
		props : '',
		classes : 'collapseall'
	});

	self.menu_item(menu_source, 'folder_locate');

	// plugin:contextmenu
	var menu = rcm_callbackmenu_init({
		menu_name : menu_name,
		menu_source : menu_source,
	});

	// plugin:contextmenu
	ctrl_link[0].onclick = function show_menu(event) {
		rcm_show_menu(event, this, null, menu);
	};

	// mimic default button
	ctrl_link.attr('role', menu_link.attr('role'));
	ctrl_link.attr('class', menu_link.attr('class'));

	// place control after default
	ctrl_link.insertAfter(menu_link);

	if (self.has_feature('hide_menu_link')) {
		menu_link.hide();
	}

	if (self.has_feature('footer_contextmenu')) {
		var link_list = menu_link.siblings().addBack().filter('a');
		$.each(link_list, function footer_contextmenu$(_, link) {
			var onclick = link.onclick;
			var oncontextmenu = link.oncontextmenu;
			if (onclick && !oncontextmenu) {
				self.log(link.id);
				link.oncontextmenu = onclick;
			}
		});
	}

	menu.addEventListener('activate', function activate(args) {
		//
	});

	menu.addEventListener('afteractivate', function afteractivate(args) {
		if (self.has_feature('hide_ctrl_menu')) {
			self.selector_action('hide', 'hide_ctrl_menu_list');
		}
	});

}

// menu setup
plugin_contextmenu_folder.prototype.mbox_list_context_menu = function mbox_list_context_menu(
		menu) {
	var self = this;
	if (menu.menu_name != 'folderlist') {
		return;
	}
	var enable = self.env('enable_folder_list_context_menu');
	self.log('enable: ' + enable);
	if (!enable) {
		return;
	}

	if (!$.isArray(menu.menu_source)) {
		menu.menu_source = [ menu.menu_source ];
	}

	var menu_source = menu.menu_source;
	self.menu_item(menu_source, 'folder_select');
	self.menu_item(menu_source, 'folder_unselect');
	self.menu_item(menu_source, 'folder_create');
	self.menu_item(menu_source, 'folder_delete');
	self.menu_item(menu_source, 'folder_rename');
	self.menu_item(menu_source, 'folder_read_tree');

	function replace_menu_purge(source) { // plugin:contextmenu
		var allow = self.has_allow_purge(source);
		var link = $('#rcm_folderlist a[class*="cmd_purge"]');
		var classes = 'override ' + self.icon_mapa('folder_purge');
		link.find('span:first').text(self.localize('folder_purge'));
		link.addClass('replace_cmd_purge').removeClass('cmd_purge');
		link.addClass(classes).off('click');
		if (allow) {
			link.on('click', function(event) {
				self.folder_purge();
			});
			link.addClass('active').removeClass('disabled');
		} else {
			link.addClass('disabled').removeClass('active');
		}
		return allow;
	}

	menu.addEventListener('activate', function activate(args) {
		var source = rcmail.env.context_menu_source_id;
		function is_regular() {
			return self.mbox_type(source) == 'regular';
		}
		function is_selected() {
			return typeof self.collect_selected()[source] !== 'undefined';
		}
		if (args.command == self.key('folder_create')) {
			return true;
		}
		if (args.command == self.key('folder_delete')) {
			return is_regular();
		}
		if (args.command == self.key('folder_rename')) {
			return is_regular();
		}
		if (args.command == self.key('folder_select')) {
			return !is_selected();
		}
		if (args.command == self.key('folder_unselect')) {
			return is_selected();
		}
		if (args.command == self.key('folder_read_tree')) {
			return true;
		}
		if (args.command == 'purge' && self.has_feature('replace_menu_purge')) {
			return replace_menu_purge(source);
		}
	});

	menu.addEventListener('afteractivate', function afteractivate(args) {
		if (self.has_feature('hide_mbox_menu')) {
			self.selector_action('hide', 'hide_mbox_menu_list');
		}
	});

}

// menu setup
plugin_contextmenu_folder.prototype.mesg_list_context_menu = function mesg_list_context_menu(
		menu) {
	var self = this;
	if (menu.menu_name != 'messagelist') {
		return;
	}
	var enable = self.env('enable_message_list_context_menu');
	self.log('enable: ' + enable);
	if (!enable) {
		return;
	}

	if (!$.isArray(menu.menu_source)) {
		menu.menu_source = [ menu.menu_source ];
	}

	menu.menu_source.push({
		label : self.localize('folder_create'),
		command : self.key('contact_folder_create'),
		props : '',
		classes : 'override ' + self.icon_mapa('folder_create'),
	});

	menu.menu_source.push({
		label : self.localize('message_copy'),
		command : self.key('message_copy'),
		props : '',
		classes : 'copy copycontact', // FIXME css
	});
	menu.menu_source.push({
		label : self.localize('message_move'),
		command : self.key('message_move'),
		props : '',
		classes : 'move movecontact', // FIXME css
	});

	menu.addEventListener('activate', function activate(args) {
		//
	});

	menu.addEventListener('afteractivate', function afteractivate(args) {
		if (self.has_feature('hide_mesg_menu')) {
			self.selector_action('hide', 'hide_mesg_menu_list');
		}
	});

}

// plugin context
if (window.rcmail && !rcmail.is_framed()) {

	// plugin instance
	rcmail.addEventListener('init', function instance(param) {
		plugin_contextmenu_folder.instance = new plugin_contextmenu_folder();
	});

	// build control menu
	rcmail.addEventListener('init', function control_menu(param) {
		var instance = plugin_contextmenu_folder.instance;
		if (instance && instance.is_plugin_active()) {
			instance.mbox_list_control_menu();
		}
	});

	// build context menu
	rcmail.addEventListener('contextmenu_init', function context_menu(menu) {
		var instance = plugin_contextmenu_folder.instance;
		if (instance && instance.is_plugin_active()) {
			instance.mbox_list_context_menu(menu);
			instance.mesg_list_context_menu(menu);
		}
	});

}
