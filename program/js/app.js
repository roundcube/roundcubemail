/*
 +-----------------------------------------------------------------------+
 | Roundcube Webmail Client Script                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Aleksander 'A.L.E.C' Machniak <alec@alec.pl>                 |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: jquery.js, common.js, list.js                               |
 +-----------------------------------------------------------------------+
*/

function rcube_webmail()
{
  this.labels = {};
  this.buttons = {};
  this.buttons_sel = {};
  this.gui_objects = {};
  this.gui_containers = {};
  this.commands = {};
  this.command_handlers = {};
  this.onloads = [];
  this.messages = {};
  this.group2expand = {};

  // webmail client settings
  this.dblclick_time = 500;
  this.message_time = 4000;
  this.identifier_expr = new RegExp('[^0-9a-z\-_]', 'gi');

  // environment defaults
  this.env = {
    request_timeout: 180,  // seconds
    draft_autosave: 0,     // seconds
    comm_path: './',
    blankpage: 'program/resources/blank.gif',
    recipients_separator: ',',
    recipients_delimiter: ', '
  };

  // create protected reference to myself
  this.ref = 'rcmail';
  var ref = this;

  // set jQuery ajax options
  $.ajaxSetup({
    cache: false,
    timeout: this.env.request_timeout * 1000,
    error: function(request, status, err){ ref.http_error(request, status, err); },
    beforeSend: function(xmlhttp){ xmlhttp.setRequestHeader('X-Roundcube-Request', ref.env.request_token); }
  });

  // unload fix
  $(window).bind('beforeunload', function() { rcmail.unload = true; });

  // set environment variable(s)
  this.set_env = function(p, value)
  {
    if (p != null && typeof p === 'object' && !value)
      for (var n in p)
        this.env[n] = p[n];
    else
      this.env[p] = value;
  };

  // add a localized label to the client environment
  this.add_label = function(p, value)
  {
    if (typeof p == 'string')
      this.labels[p] = value;
    else if (typeof p == 'object')
      $.extend(this.labels, p);
  };

  // add a button to the button list
  this.register_button = function(command, id, type, act, sel, over)
  {
    var button_prop = {id:id, type:type};

    if (act) button_prop.act = act;
    if (sel) button_prop.sel = sel;
    if (over) button_prop.over = over;

    if (!this.buttons[command])
      this.buttons[command] = [];

    this.buttons[command].push(button_prop);

    if (this.loaded)
      init_button(command, button_prop);
  };

  // register a specific gui object
  this.gui_object = function(name, id)
  {
    this.gui_objects[name] = this.loaded ? rcube_find_object(id) : id;
  };

  // register a container object
  this.gui_container = function(name, id)
  {
    this.gui_containers[name] = id;
  };

  // add a GUI element (html node) to a specified container
  this.add_element = function(elm, container)
  {
    if (this.gui_containers[container] && this.gui_containers[container].jquery)
      this.gui_containers[container].append(elm);
  };

  // register an external handler for a certain command
  this.register_command = function(command, callback, enable)
  {
    this.command_handlers[command] = callback;

    if (enable)
      this.enable_command(command, true);
  };

  // execute the given script on load
  this.add_onload = function(f)
  {
    this.onloads.push(f);
  };

  // initialize webmail client
  this.init = function()
  {
    var n, p = this;
    this.task = this.env.task;

    // check browser
    if (!bw.dom || !bw.xmlhttp_test() || (bw.mz && bw.vendver < 1.9)) {
      this.goto_url('error', '_code=0x199');
      return;
    }

    // find all registered gui containers
    for (n in this.gui_containers)
      this.gui_containers[n] = $('#'+this.gui_containers[n]);

    // find all registered gui objects
    for (n in this.gui_objects)
      this.gui_objects[n] = rcube_find_object(this.gui_objects[n]);

    // clickjacking protection
    if (this.env.x_frame_options) {
      try {
        // bust frame if not allowed
        if (this.env.x_frame_options == 'deny' && top.location.href != self.location.href)
          top.location.href = self.location.href;
        else if (top.location.hostname != self.location.hostname)
          throw 1;
      } catch (e) {
        // possible clickjacking attack: disable all form elements
        $('form').each(function(){ ref.lock_form(this, true); });
        this.display_message("Blocked: possible clickjacking attack!", 'error');
        return;
      }
    }

    // init registered buttons
    this.init_buttons();

    // tell parent window that this frame is loaded
    if (this.is_framed()) {
      parent.rcmail.set_busy(false, null, parent.rcmail.env.frame_lock);
      parent.rcmail.env.frame_lock = null;
    }

    // enable general commands
    this.enable_command('close', 'logout', 'mail', 'addressbook', 'settings', 'save-pref', 'compose', 'undo', 'about', 'switch-task', true);

    if (this.env.permaurl)
      this.enable_command('permaurl', 'extwin', true);

    switch (this.task) {

      case 'mail':
        // enable mail commands
        this.enable_command('list', 'checkmail', 'add-contact', 'search', 'reset-search', 'collapse-folder', true);

        if (this.gui_objects.messagelist) {
          this.message_list = new rcube_list_widget(this.gui_objects.messagelist, {
            multiselect:true, multiexpand:true, draggable:true, keyboard:true,
            column_movable:this.env.col_movable, dblclick_time:this.dblclick_time
            });
          this.message_list.row_init = function(o){ p.init_message_row(o); };
          this.message_list.addEventListener('dblclick', function(o){ p.msglist_dbl_click(o); });
          this.message_list.addEventListener('click', function(o){ p.msglist_click(o); });
          this.message_list.addEventListener('keypress', function(o){ p.msglist_keypress(o); });
          this.message_list.addEventListener('select', function(o){ p.msglist_select(o); });
          this.message_list.addEventListener('dragstart', function(o){ p.drag_start(o); });
          this.message_list.addEventListener('dragmove', function(e){ p.drag_move(e); });
          this.message_list.addEventListener('dragend', function(e){ p.drag_end(e); });
          this.message_list.addEventListener('expandcollapse', function(e){ p.msglist_expand(e); });
          this.message_list.addEventListener('column_replace', function(e){ p.msglist_set_coltypes(e); });

          document.onmouseup = function(e){ return p.doc_mouse_up(e); };
          this.gui_objects.messagelist.parentNode.onmousedown = function(e){ return p.click_on_list(e); };

          this.message_list.init();
          this.enable_command('toggle_status', 'toggle_flag', 'menu-open', 'menu-save', 'sort', true);

          // load messages
          this.command('list');
        }

        if (this.gui_objects.qsearchbox) {
          if (this.env.search_text != null)
            this.gui_objects.qsearchbox.value = this.env.search_text;
          $(this.gui_objects.qsearchbox).focusin(function() { rcmail.message_list && rcmail.message_list.blur(); });
        }

        this.set_button_titles();

        this.env.message_commands = ['show', 'reply', 'reply-all', 'reply-list',
          'moveto', 'copy', 'delete', 'open', 'mark', 'edit', 'viewsource',
          'print', 'load-attachment', 'show-headers', 'hide-headers', 'download',
          'forward', 'forward-inline', 'forward-attachment'];

        if (this.env.action == 'show' || this.env.action == 'preview') {
          this.enable_command(this.env.message_commands, this.env.uid);
          this.enable_command('reply-list', this.env.list_post);

          if (this.env.action == 'show') {
            this.http_request('pagenav', {_uid: this.env.uid, _mbox: this.env.mailbox, _search: this.env.search_request},
              this.display_message('', 'loading'));
          }

          if (this.env.blockedobjects) {
            if (this.gui_objects.remoteobjectsmsg)
              this.gui_objects.remoteobjectsmsg.style.display = 'block';
            this.enable_command('load-images', 'always-load', true);
          }

          // make preview/message frame visible
          if (this.env.action == 'preview' && this.is_framed()) {
            this.enable_command('compose', 'add-contact', false);
            parent.rcmail.show_contentframe(true);
          }
        }
        else if (this.env.action == 'compose') {
          this.env.compose_commands = ['send-attachment', 'remove-attachment', 'send', 'cancel', 'toggle-editor', 'list-adresses', 'search', 'reset-search', 'extwin'];

          if (this.env.drafts_mailbox)
            this.env.compose_commands.push('savedraft')

          this.enable_command(this.env.compose_commands, 'identities', true);

          // add more commands (not enabled)
          $.merge(this.env.compose_commands, ['add-recipient', 'firstpage', 'previouspage', 'nextpage', 'lastpage']);

          if (this.env.spellcheck) {
            this.env.spellcheck.spelling_state_observer = function(s) { ref.spellcheck_state(); };
            this.env.compose_commands.push('spellcheck')
            this.enable_command('spellcheck', true);
          }

          document.onmouseup = function(e){ return p.doc_mouse_up(e); };

          // init message compose form
          this.init_messageform();
        }
        // show printing dialog
        else if (this.env.action == 'print' && this.env.uid)
          if (bw.safari)
            setTimeout('window.print()', 10);
          else
            window.print();

        // get unread count for each mailbox
        if (this.gui_objects.mailboxlist) {
          this.env.unread_counts = {};
          this.gui_objects.folderlist = this.gui_objects.mailboxlist;
          this.http_request('getunread');
        }

        // init address book widget
        if (this.gui_objects.contactslist) {
          this.contact_list = new rcube_list_widget(this.gui_objects.contactslist,
            { multiselect:true, draggable:false, keyboard:false });
          this.contact_list.addEventListener('select', function(o){ ref.compose_recipient_select(o); });
          this.contact_list.addEventListener('dblclick', function(o){ ref.compose_add_recipient('to'); });
          this.contact_list.init();
        }

        if (this.gui_objects.addressbookslist) {
          this.gui_objects.folderlist = this.gui_objects.addressbookslist;
          this.enable_command('list-adresses', true);
        }

        // ask user to send MDN
        if (this.env.mdn_request && this.env.uid) {
          var postact = 'sendmdn',
            postdata = {_uid: this.env.uid, _mbox: this.env.mailbox};
          if (!confirm(this.get_label('mdnrequest'))) {
            postdata._flag = 'mdnsent';
            postact = 'mark';
          }
          this.http_post(postact, postdata);
        }

        // detect browser capabilities
        if (!this.is_framed() && !this.env.extwin)
          this.browser_capabilities_check();

        break;

      case 'addressbook':
        if (this.gui_objects.folderlist)
          this.env.contactfolders = $.extend($.extend({}, this.env.address_sources), this.env.contactgroups);

        this.enable_command('add', 'import', this.env.writable_source);
        this.enable_command('list', 'listgroup', 'listsearch', 'advanced-search', true);

        if (this.gui_objects.contactslist) {
          this.contact_list = new rcube_list_widget(this.gui_objects.contactslist,
            {multiselect:true, draggable:this.gui_objects.folderlist?true:false, keyboard:true});
          this.contact_list.row_init = function(row){ p.triggerEvent('insertrow', { cid:row.uid, row:row }); };
          this.contact_list.addEventListener('keypress', function(o){ p.contactlist_keypress(o); });
          this.contact_list.addEventListener('select', function(o){ p.contactlist_select(o); });
          this.contact_list.addEventListener('dragstart', function(o){ p.drag_start(o); });
          this.contact_list.addEventListener('dragmove', function(e){ p.drag_move(e); });
          this.contact_list.addEventListener('dragend', function(e){ p.drag_end(e); });
          this.contact_list.init();

          if (this.env.cid)
            this.contact_list.highlight_row(this.env.cid);

          this.gui_objects.contactslist.parentNode.onmousedown = function(e){ return p.click_on_list(e); };
          document.onmouseup = function(e){ return p.doc_mouse_up(e); };
          if (this.gui_objects.qsearchbox)
            $(this.gui_objects.qsearchbox).focusin(function() { rcmail.contact_list.blur(); });

          this.update_group_commands();
          this.command('list');
        }

        this.set_page_buttons();

        if (this.env.cid) {
          this.enable_command('show', 'edit', true);
          // register handlers for group assignment via checkboxes
          if (this.gui_objects.editform) {
            $('input.groupmember').change(function() {
              ref.group_member_change(this.checked ? 'add' : 'del', ref.env.cid, ref.env.source, this.value);
            });
          }
        }

        if (this.gui_objects.editform) {
          this.enable_command('save', true);
          if (this.env.action == 'add' || this.env.action == 'edit' || this.env.action == 'search')
              this.init_contact_form();
        }

        if (this.gui_objects.qsearchbox)
          this.enable_command('search', 'reset-search', 'moveto', true);

        break;

      case 'settings':
        this.enable_command('preferences', 'identities', 'save', 'folders', true);

        if (this.env.action == 'identities') {
          this.enable_command('add', this.env.identities_level < 2);
        }
        else if (this.env.action == 'edit-identity' || this.env.action == 'add-identity') {
          this.enable_command('save', 'edit', 'toggle-editor', true);
          this.enable_command('delete', this.env.identities_level < 2);

          if (this.env.action == 'add-identity')
            $("input[type='text']").first().select();
        }
        else if (this.env.action == 'folders') {
          this.enable_command('subscribe', 'unsubscribe', 'create-folder', 'rename-folder', true);
        }
        else if (this.env.action == 'edit-folder' && this.gui_objects.editform) {
          this.enable_command('save', 'folder-size', true);
          parent.rcmail.env.exists = this.env.messagecount;
          parent.rcmail.enable_command('purge', this.env.messagecount);
          $("input[type='text']").first().select();
        }

        if (this.gui_objects.identitieslist) {
          this.identity_list = new rcube_list_widget(this.gui_objects.identitieslist, {multiselect:false, draggable:false, keyboard:false});
          this.identity_list.addEventListener('select', function(o){ p.identity_select(o); });
          this.identity_list.init();
          this.identity_list.focus();

          if (this.env.iid)
            this.identity_list.highlight_row(this.env.iid);
        }
        else if (this.gui_objects.sectionslist) {
          this.sections_list = new rcube_list_widget(this.gui_objects.sectionslist, {multiselect:false, draggable:false, keyboard:false});
          this.sections_list.addEventListener('select', function(o){ p.section_select(o); });
          this.sections_list.init();
          this.sections_list.focus();
        }
        else if (this.gui_objects.subscriptionlist)
          this.init_subscription_list();

        break;

      case 'login':
        var input_user = $('#rcmloginuser');
        input_user.bind('keyup', function(e){ return rcmail.login_user_keyup(e); });

        if (input_user.val() == '')
          input_user.focus();
        else
          $('#rcmloginpwd').focus();

        // detect client timezone
        if (window.jstz && !bw.ie6) {
          var timezone = jstz.determine();
          if (timezone.name())
            $('#rcmlogintz').val(timezone.name());
        }
        else {
          $('#rcmlogintz').val(new Date().getStdTimezoneOffset() / -60);
        }

        // display 'loading' message on form submit, lock submit button
        $('form').submit(function () {
          $('input[type=submit]', this).prop('disabled', true);
          rcmail.clear_messages();
          rcmail.display_message('', 'loading');
        });

        this.enable_command('login', true);
        break;
    }

    // unset contentframe variable if preview_pane is enabled
    if (this.env.contentframe && !$('#' + this.env.contentframe).is(':visible'))
      this.env.contentframe = null;

    // prevent from form submit with Enter key in file input fields
    if (bw.ie)
      $('input[type=file]').keydown(function(e) { if (e.keyCode == '13') e.preventDefault(); });

    // flag object as complete
    this.loaded = true;

    // show message
    if (this.pending_message)
      this.display_message(this.pending_message[0], this.pending_message[1], this.pending_message[2]);

    // map implicit containers
    if (this.gui_objects.folderlist)
      this.gui_containers.foldertray = $(this.gui_objects.folderlist);

    // activate html5 file drop feature (if browser supports it and if configured)
    if (this.gui_objects.filedrop && this.env.filedrop && ((window.XMLHttpRequest && XMLHttpRequest.prototype && XMLHttpRequest.prototype.sendAsBinary) || window.FormData)) {
      $(document.body).bind('dragover dragleave drop', function(e){ return ref.document_drag_hover(e, e.type == 'dragover'); });
      $(this.gui_objects.filedrop).addClass('droptarget')
        .bind('dragover dragleave', function(e){ return ref.file_drag_hover(e, e.type == 'dragover'); })
        .get(0).addEventListener('drop', function(e){ return ref.file_dropped(e); }, false);
    }

    // trigger init event hook
    this.triggerEvent('init', { task:this.task, action:this.env.action });

    // execute all foreign onload scripts
    // @deprecated
    for (var i in this.onloads) {
      if (typeof this.onloads[i] === 'string')
        eval(this.onloads[i]);
      else if (typeof this.onloads[i] === 'function')
        this.onloads[i]();
      }

    // start keep-alive and refresh intervals
    this.start_refresh();
    this.start_keepalive();
  };

  this.log = function(msg)
  {
    if (window.console && console.log)
      console.log(msg);
  };

  /*********************************************************/
  /*********       client command interface        *********/
  /*********************************************************/

  // execute a specific command on the web client
  this.command = function(command, props, obj, event)
  {
    var ret, uid, cid, url, flag;

    if (obj && obj.blur)
      obj.blur();

    if (this.busy)
      return false;

    // let the browser handle this click (shift/ctrl usually opens the link in a new window/tab)
    if ((obj && obj.href && String(obj.href).indexOf('#') < 0) && rcube_event.get_modifier(event)) {
      return true;
    }

    // command not supported or allowed
    if (!this.commands[command]) {
      // pass command to parent window
      if (this.is_framed())
        parent.rcmail.command(command, props);

      return false;
    }

    // check input before leaving compose step
    if (this.task == 'mail' && this.env.action == 'compose' && $.inArray(command, this.env.compose_commands)<0) {
      if (this.cmp_hash != this.compose_field_hash() && !confirm(this.get_label('notsentwarning')))
        return false;
    }

    // process external commands
    if (typeof this.command_handlers[command] === 'function') {
      ret = this.command_handlers[command](props, obj);
      return ret !== undefined ? ret : (obj ? false : true);
    }
    else if (typeof this.command_handlers[command] === 'string') {
      ret = window[this.command_handlers[command]](props, obj);
      return ret !== undefined ? ret : (obj ? false : true);
    }

    // trigger plugin hooks
    this.triggerEvent('actionbefore', {props:props, action:command});
    ret = this.triggerEvent('before'+command, props);
    if (ret !== undefined) {
      // abort if one of the handlers returned false
      if (ret === false)
        return false;
      else
        props = ret;
    }

    ret = undefined;

    // process internal command
    switch (command) {

      case 'login':
        if (this.gui_objects.loginform)
          this.gui_objects.loginform.submit();
        break;

      // commands to switch task
      case 'mail':
      case 'addressbook':
      case 'settings':
      case 'logout':
        this.switch_task(command);
        break;

      case 'about':
        this.redirect('?_task=settings&_action=about', false);
        break;

      case 'permaurl':
        if (obj && obj.href && obj.target)
          return true;
        else if (this.env.permaurl)
          parent.location.href = this.env.permaurl;
        break;

      case 'extwin':
        if (this.env.action == 'compose') {
          var prevstate = this.env.compose_extwin;
          $("input[name='_action']", this.gui_objects.messageform).val('compose');
          this.gui_objects.messageform.action = this.url('mail/compose', { _id: this.env.compose_id, _extwin: 1 });
          this.gui_objects.messageform.target = this.open_window('', 1100);
          this.gui_objects.messageform.submit();
        }
        else {
          this.open_window(this.env.permaurl, 900);
        }
        break;

      case 'menu-open':
      case 'menu-save':
        this.triggerEvent(command, {props:props});
        return false;

      case 'open':
        if (uid = this.get_single_uid()) {
          obj.href = this.url('show', {_mbox: this.env.mailbox, _uid: uid});
          return true;
        }
        break;

      case 'close':
        if (this.env.extwin)
          window.close();
        break;

      case 'list':
        if (props && props != '')
          this.reset_qsearch();
        if (this.env.action == 'compose' && this.env.extwin)
          window.close();
        else if (this.task == 'mail') {
          this.list_mailbox(props);
          this.set_button_titles();
        }
        else if (this.task == 'addressbook')
          this.list_contacts(props);
        break;

      case 'sort':
        var sort_order = this.env.sort_order,
          sort_col = !this.env.disabled_sort_col ? props : this.env.sort_col;

        if (!this.env.disabled_sort_order)
          sort_order = this.env.sort_col == sort_col && sort_order == 'ASC' ? 'DESC' : 'ASC';

        // set table header and update env
        this.set_list_sorting(sort_col, sort_order);

        // reload message list
        this.list_mailbox('', '', sort_col+'_'+sort_order);
        break;

      case 'nextpage':
        this.list_page('next');
        break;

      case 'lastpage':
        this.list_page('last');
        break;

      case 'previouspage':
        this.list_page('prev');
        break;

      case 'firstpage':
        this.list_page('first');
        break;

      case 'expunge':
        if (this.env.exists)
          this.expunge_mailbox(this.env.mailbox);
        break;

      case 'purge':
      case 'empty-mailbox':
        if (this.env.exists)
          this.purge_mailbox(this.env.mailbox);
        break;

      // common commands used in multiple tasks
      case 'show':
        if (this.task == 'mail') {
          uid = this.get_single_uid();
          if (uid && (!this.env.uid || uid != this.env.uid)) {
            if (this.env.mailbox == this.env.drafts_mailbox)
              this.open_compose_step({ _draft_uid: uid, _mbox: this.env.mailbox });
            else
              this.show_message(uid);
          }
        }
        else if (this.task == 'addressbook') {
          cid = props ? props : this.get_single_cid();
          if (cid && !(this.env.action == 'show' && cid == this.env.cid))
            this.load_contact(cid, 'show');
        }
        break;

      case 'add':
        if (this.task == 'addressbook')
          this.load_contact(0, 'add');
        else if (this.task == 'settings') {
          this.identity_list.clear_selection();
          this.load_identity(0, 'add-identity');
        }
        break;

      case 'edit':
        if (this.task == 'addressbook' && (cid = this.get_single_cid()))
          this.load_contact(cid, 'edit');
        else if (this.task == 'settings' && props)
          this.load_identity(props, 'edit-identity');
        else if (this.task == 'mail' && (cid = this.get_single_uid())) {
          url = { _mbox: this.env.mailbox };
          url[this.env.mailbox == this.env.drafts_mailbox && props != 'new' ? '_draft_uid' : '_uid'] = cid;
          this.open_compose_step(url);
        }
        break;

      case 'save':
        var input, form = this.gui_objects.editform;
        if (form) {
          // adv. search
          if (this.env.action == 'search') {
          }
          // user prefs
          else if ((input = $("input[name='_pagesize']", form)) && input.length && isNaN(parseInt(input.val()))) {
            alert(this.get_label('nopagesizewarning'));
            input.focus();
            break;
          }
          // contacts/identities
          else {
            // reload form
            if (props == 'reload') {
              form.action += '?_reload=1';
            }
            else if (this.task == 'settings' && (this.env.identities_level % 2) == 0  &&
              (input = $("input[name='_email']", form)) && input.length && !rcube_check_email(input.val())
            ) {
              alert(this.get_label('noemailwarning'));
              input.focus();
              break;
            }

            // clear empty input fields
            $('input.placeholder').each(function(){ if (this.value == this._placeholder) this.value = ''; });
          }

          // add selected source (on the list)
          if (parent.rcmail && parent.rcmail.env.source)
            form.action = this.add_url(form.action, '_orig_source', parent.rcmail.env.source);

          form.submit();
        }
        break;

      case 'delete':
        // mail task
        if (this.task == 'mail')
          this.delete_messages(event);
        // addressbook task
        else if (this.task == 'addressbook')
          this.delete_contacts();
        // user settings task
        else if (this.task == 'settings')
          this.delete_identity();
        break;

      // mail task commands
      case 'move':
      case 'moveto':
        if (this.task == 'mail')
          this.move_messages(props);
        else if (this.task == 'addressbook' && this.drag_active)
          this.copy_contact(null, props);
        break;

      case 'copy':
        if (this.task == 'mail')
          this.copy_messages(props);
        break;

      case 'mark':
        if (props)
          this.mark_message(props);
        break;

      case 'toggle_status':
        if (props && !props._row)
          break;

        flag = 'read';

        if (props._row.uid) {
          uid = props._row.uid;

          // toggle read/unread
          if (this.message_list.rows[uid].deleted)
            flag = 'undelete';
          else if (!this.message_list.rows[uid].unread)
            flag = 'unread';
        }

        this.mark_message(flag, uid);
        break;

      case 'toggle_flag':
        if (props && !props._row)
          break;

        flag = 'flagged';

        if (props._row.uid) {
          uid = props._row.uid;
          // toggle flagged/unflagged
          if (this.message_list.rows[uid].flagged)
            flag = 'unflagged';
        }
        this.mark_message(flag, uid);
        break;

      case 'always-load':
        if (this.env.uid && this.env.sender) {
          this.add_contact(this.env.sender);
          setTimeout(function(){ ref.command('load-images'); }, 300);
          break;
        }

      case 'load-images':
        if (this.env.uid)
          this.show_message(this.env.uid, true, this.env.action=='preview');
        break;

      case 'load-attachment':
        var qstring = '_mbox='+urlencode(this.env.mailbox)+'&_uid='+this.env.uid+'&_part='+props.part;

        // open attachment in frame if it's of a supported mimetype
        if (this.env.uid && props.mimetype && this.env.mimetypes && $.inArray(props.mimetype, this.env.mimetypes) >= 0) {
          var attachment_win = window.open(this.env.comm_path+'&_action=get&'+qstring+'&_frame=1', this.html_identifier('rcubemailattachment'+this.env.uid+props.part));
          if (attachment_win) {
            setTimeout(function(){ attachment_win.focus(); }, 10);
            break;
          }
        }

        this.goto_url('get', qstring+'&_download=1', false);
        break;

      case 'select-all':
        this.select_all_mode = props ? false : true;
        this.dummy_select = true; // prevent msg opening if there's only one msg on the list
        if (props == 'invert')
          this.message_list.invert_selection();
        else
          this.message_list.select_all(props == 'page' ? '' : props);
        this.dummy_select = null;
        break;

      case 'select-none':
        this.select_all_mode = false;
        this.message_list.clear_selection();
        break;

      case 'expand-all':
        this.env.autoexpand_threads = 1;
        this.message_list.expand_all();
        break;

      case 'expand-unread':
        this.env.autoexpand_threads = 2;
        this.message_list.collapse_all();
        this.expand_unread();
        break;

      case 'collapse-all':
        this.env.autoexpand_threads = 0;
        this.message_list.collapse_all();
        break;

      case 'nextmessage':
        if (this.env.next_uid)
          this.show_message(this.env.next_uid, false, this.env.action=='preview');
        break;

      case 'lastmessage':
        if (this.env.last_uid)
          this.show_message(this.env.last_uid);
        break;

      case 'previousmessage':
        if (this.env.prev_uid)
          this.show_message(this.env.prev_uid, false, this.env.action == 'preview');
        break;

      case 'firstmessage':
        if (this.env.first_uid)
          this.show_message(this.env.first_uid);
        break;

      case 'compose':
        url = {};

        if (this.task == 'mail') {
          url._mbox = this.env.mailbox;
          if (props)
             url._to = props;
          // also send search request so we can go back to search result after message is sent
          if (this.env.search_request)
            url._search = this.env.search_request;
        }
        // modify url if we're in addressbook
        else if (this.task == 'addressbook') {
          // switch to mail compose step directly
          if (props && props.indexOf('@') > 0) {
            url._to = props;
          }
          else {
            // use contact_id passed as command parameter
            var n, len, a_cids = [];
            if (props)
              a_cids.push(props);
            // get selected contacts
            else if (this.contact_list) {
              var selection = this.contact_list.get_selection();
              for (n=0, len=selection.length; n<len; n++)
                a_cids.push(selection[n]);
            }

            if (a_cids.length)
              this.http_post('mailto', { _cid: a_cids.join(','), _source: this.env.source }, true);
            else if (this.env.group)
              this.http_post('mailto', { _gid: this.env.group, _source: this.env.source }, true);

            break;
          }
        }
        else if (props)
          url._to = props;

        this.open_compose_step(url);
        break;

      case 'spellcheck':
        if (this.spellcheck_state()) {
          this.stop_spellchecking();
        }
        else {
          if (window.tinyMCE && tinyMCE.get(this.env.composebody)) {
            tinyMCE.execCommand('mceSpellCheck', true);
          }
          else if (this.env.spellcheck && this.env.spellcheck.spellCheck) {
            this.env.spellcheck.spellCheck();
          }
        }
        this.spellcheck_state();
        break;

      case 'savedraft':
        // Reset the auto-save timer
        clearTimeout(this.save_timer);

        // compose form did not change (and draft wasn't saved already)
        if (this.env.draft_id && this.cmp_hash == this.compose_field_hash()) {
          this.auto_save_start();
          break;
        }

        this.submit_messageform(true);
        break;

      case 'send':
        if (!props.nocheck && !this.check_compose_input(command))
          break;

        // Reset the auto-save timer
        clearTimeout(this.save_timer);

        this.submit_messageform();
        break;

      case 'send-attachment':
        // Reset the auto-save timer
        clearTimeout(this.save_timer);

        this.upload_file(props || this.gui_objects.uploadform);
        break;

      case 'insert-sig':
        this.change_identity($("[name='_from']")[0], true);
        break;

      case 'list-adresses':
        this.list_contacts(props);
        this.enable_command('add-recipient', false);
        break;

      case 'add-recipient':
        this.compose_add_recipient(props);
        break;

      case 'reply-all':
      case 'reply-list':
      case 'reply':
        if (uid = this.get_single_uid()) {
          url = {_reply_uid: uid, _mbox: this.env.mailbox};
          if (command == 'reply-all')
            // do reply-list, when list is detected and popup menu wasn't used
            url._all = (!props && this.commands['reply-list'] ? 'list' : 'all');
          else if (command == 'reply-list')
            url._all = 'list';

          this.open_compose_step(url);
        }
        break;

      case 'forward-attachment':
      case 'forward-inline':
      case 'forward':
        var uids = this.env.uid ? [this.env.uid] : (this.message_list ? this.message_list.get_selection() : []);
        if (uids.length) {
          url = { _forward_uid: this.uids_to_list(uids), _mbox: this.env.mailbox };
          if (command == 'forward-attachment' || (!props && this.env.forward_attachment) || uids.length > 1)
            url._attachment = 1;
          this.open_compose_step(url);
        }
        break;

      case 'print':
        if (uid = this.get_single_uid()) {
          ref.printwin = window.open(this.env.comm_path+'&_action=print&_uid='+uid+'&_mbox='+urlencode(this.env.mailbox)+(this.env.safemode ? '&_safe=1' : ''));
          if (this.printwin) {
            setTimeout(function(){ ref.printwin.focus(); }, 20);
            if (this.env.action != 'show')
              this.mark_message('read', uid);
          }
        }
        break;

      case 'viewsource':
        if (uid = this.get_single_uid()) {
          ref.sourcewin = window.open(this.env.comm_path+'&_action=viewsource&_uid='+uid+'&_mbox='+urlencode(this.env.mailbox));
          if (this.sourcewin)
            setTimeout(function(){ ref.sourcewin.focus(); }, 20);
          }
        break;

      case 'download':
        if (uid = this.get_single_uid())
          this.goto_url('viewsource', { _uid: uid, _mbox: this.env.mailbox, _save: 1 });
        break;

      // quicksearch
      case 'search':
        if (!props && this.gui_objects.qsearchbox)
          props = this.gui_objects.qsearchbox.value;
        if (props) {
          this.qsearch(props);
          break;
        }

      // reset quicksearch
      case 'reset-search':
        var n, s = this.env.search_request || this.env.qsearch;

        this.reset_qsearch();
        this.select_all_mode = false;

        if (s && this.env.action == 'compose') {
          if (this.contact_list)
            this.list_contacts_clear();
        }
        else if (s && this.env.mailbox) {
          this.list_mailbox(this.env.mailbox, 1);
        }
        else if (s && this.task == 'addressbook') {
          if (this.env.source == '') {
            for (n in this.env.address_sources) break;
            this.env.source = n;
            this.env.group = '';
          }
          this.list_contacts(this.env.source, this.env.group, 1);
        }
        break;

      case 'listgroup':
        this.reset_qsearch();
        this.list_contacts(props.source, props.id);
        break;

      case 'import':
        if (this.env.action == 'import' && this.gui_objects.importform) {
          var file = document.getElementById('rcmimportfile');
          if (file && !file.value) {
            alert(this.get_label('selectimportfile'));
            break;
          }
          this.gui_objects.importform.submit();
          this.set_busy(true, 'importwait');
          this.lock_form(this.gui_objects.importform, true);
        }
        else
          this.goto_url('import', (this.env.source ? '_target='+urlencode(this.env.source)+'&' : ''));
        break;

      case 'export':
        if (this.contact_list.rowcount > 0) {
          this.goto_url('export', { _source: this.env.source, _gid: this.env.group, _search: this.env.search_request });
        }
        break;

      case 'upload-photo':
        this.upload_contact_photo(props || this.gui_objects.uploadform);
        break;

      case 'delete-photo':
        this.replace_contact_photo('-del-');
        break;

      // user settings commands
      case 'preferences':
      case 'identities':
      case 'folders':
        this.goto_url('settings/' + command);
        break;

      case 'undo':
        this.http_request('undo', '', this.display_message('', 'loading'));
        break;

      // unified command call (command name == function name)
      default:
        var func = command.replace(/-/g, '_');
        if (this[func] && typeof this[func] === 'function') {
          ret = this[func](props, obj);
        }
        break;
    }

    if (this.triggerEvent('after'+command, props) === false)
      ret = false;
    this.triggerEvent('actionafter', {props:props, action:command});

    return ret === false ? false : obj ? false : true;
  };

  // set command(s) enabled or disabled
  this.enable_command = function()
  {
    var i, n, args = Array.prototype.slice.call(arguments),
      enable = args.pop(), cmd;

    for (n=0; n<args.length; n++) {
      cmd = args[n];
      // argument of type array
      if (typeof cmd === 'string') {
        this.commands[cmd] = enable;
        this.set_button(cmd, (enable ? 'act' : 'pas'));
      }
      // push array elements into commands array
      else {
        for (i in cmd)
          args.push(cmd[i]);
      }
    }
  };

  // lock/unlock interface
  this.set_busy = function(a, message, id)
  {
    if (a && message) {
      var msg = this.get_label(message);
      if (msg == message)
        msg = 'Loading...';

      id = this.display_message(msg, 'loading');
    }
    else if (!a && id) {
      this.hide_message(id);
    }

    this.busy = a;
    //document.body.style.cursor = a ? 'wait' : 'default';

    if (this.gui_objects.editform)
      this.lock_form(this.gui_objects.editform, a);

    return id;
  };

  // return a localized string
  this.get_label = function(name, domain)
  {
    if (domain && this.labels[domain+'.'+name])
      return this.labels[domain+'.'+name];
    else if (this.labels[name])
      return this.labels[name];
    else
      return name;
  };

  // alias for convenience reasons
  this.gettext = this.get_label;

  // switch to another application task
  this.switch_task = function(task)
  {
    if (this.task===task && task!='mail')
      return;

    var url = this.get_task_url(task);
    if (task=='mail')
      url += '&_mbox=INBOX';

    this.redirect(url);
  };

  this.get_task_url = function(task, url)
  {
    if (!url)
      url = this.env.comm_path;

    return url.replace(/_task=[a-z]+/, '_task='+task);
  };

  this.reload = function(delay)
  {
    if (this.is_framed())
      parent.rcmail.reload(delay);
    else if (delay)
      setTimeout(function(){ rcmail.reload(); }, delay);
    else if (window.location)
      location.href = this.env.comm_path + (this.env.action ? '&_action='+this.env.action : '');
  };

  // Add variable to GET string, replace old value if exists
  this.add_url = function(url, name, value)
  {
    value = urlencode(value);

    if (/(\?.*)$/.test(url)) {
      var urldata = RegExp.$1,
        datax = RegExp('((\\?|&)'+RegExp.escape(name)+'=[^&]*)');

      if (datax.test(urldata)) {
        urldata = urldata.replace(datax, RegExp.$2 + name + '=' + value);
      }
      else
        urldata += '&' + name + '=' + value

      return url.replace(/(\?.*)$/, urldata);
    }

    return url + '?' + name + '=' + value;
  };

  this.is_framed = function()
  {
    return (this.env.framed && parent.rcmail && parent.rcmail != this && parent.rcmail.command);
  };

  this.save_pref = function(prop)
  {
    var request = {'_name': prop.name, '_value': prop.value};

    if (prop.session)
      request['_session'] = prop.session;
    if (prop.env)
      this.env[prop.env] = prop.value;

    this.http_post('save-pref', request);
  };

  this.html_identifier = function(str, encode)
  {
    str = String(str);
    if (encode)
      return Base64.encode(str).replace(/=+$/, '').replace(/\+/g, '-').replace(/\//g, '_');
    else
      return str.replace(this.identifier_expr, '_');
  };

  this.html_identifier_decode = function(str)
  {
    str = String(str).replace(/-/g, '+').replace(/_/g, '/');

    while (str.length % 4) str += '=';

    return Base64.decode(str);
  };


  /*********************************************************/
  /*********        event handling methods         *********/
  /*********************************************************/

  this.drag_menu = function(e, target)
  {
    var modkey = rcube_event.get_modifier(e),
      menu = this.gui_objects.message_dragmenu;

    if (menu && modkey == SHIFT_KEY && this.commands['copy']) {
      var pos = rcube_event.get_mouse_pos(e);
      this.env.drag_target = target;
      $(menu).css({top: (pos.y-10)+'px', left: (pos.x-10)+'px'}).show();
      return true;
    }

    return false;
  };

  this.drag_menu_action = function(action)
  {
    var menu = this.gui_objects.message_dragmenu;
    if (menu) {
      $(menu).hide();
    }
    this.command(action, this.env.drag_target);
    this.env.drag_target = null;
  };

  this.drag_start = function(list)
  {
    var model = this.task == 'mail' ? this.env.mailboxes : this.env.contactfolders;

    this.drag_active = true;

    if (this.preview_timer)
      clearTimeout(this.preview_timer);
    if (this.preview_read_timer)
      clearTimeout(this.preview_read_timer);

    // save folderlist and folders location/sizes for droptarget calculation in drag_move()
    if (this.gui_objects.folderlist && model) {
      this.initialBodyScrollTop = bw.ie ? 0 : window.pageYOffset;
      this.initialListScrollTop = this.gui_objects.folderlist.parentNode.scrollTop;

      var k, li, height,
        list = $(this.gui_objects.folderlist);
        pos = list.offset();

      this.env.folderlist_coords = { x1:pos.left, y1:pos.top, x2:pos.left + list.width(), y2:pos.top + list.height() };

      this.env.folder_coords = [];
      for (k in model) {
        if (li = this.get_folder_li(k)) {
          // only visible folders
          if (height = li.firstChild.offsetHeight) {
            pos = $(li.firstChild).offset();
            this.env.folder_coords[k] = { x1:pos.left, y1:pos.top,
              x2:pos.left + li.firstChild.offsetWidth, y2:pos.top + height, on:0 };
          }
        }
      }
    }
  };

  this.drag_end = function(e)
  {
    this.drag_active = false;
    this.env.last_folder_target = null;

    if (this.folder_auto_timer) {
      clearTimeout(this.folder_auto_timer);
      this.folder_auto_timer = null;
      this.folder_auto_expand = null;
    }

    // over the folders
    if (this.gui_objects.folderlist && this.env.folder_coords) {
      for (var k in this.env.folder_coords) {
        if (this.env.folder_coords[k].on)
          $(this.get_folder_li(k)).removeClass('droptarget');
      }
    }
  };

  this.drag_move = function(e)
  {
    if (this.gui_objects.folderlist && this.env.folder_coords) {
      var k, li, div, check, oldclass,
        layerclass = 'draglayernormal',
        mouse = rcube_event.get_mouse_pos(e),
        pos = this.env.folderlist_coords,
        // offsets to compensate for scrolling while dragging a message
        boffset = bw.ie ? -document.documentElement.scrollTop : this.initialBodyScrollTop,
        moffset = this.initialListScrollTop-this.gui_objects.folderlist.parentNode.scrollTop;

      if (this.contact_list && this.contact_list.draglayer)
        oldclass = this.contact_list.draglayer.attr('class');

      mouse.y += -moffset-boffset;

      // if mouse pointer is outside of folderlist
      if (mouse.x < pos.x1 || mouse.x >= pos.x2 || mouse.y < pos.y1 || mouse.y >= pos.y2) {
        if (this.env.last_folder_target) {
          $(this.get_folder_li(this.env.last_folder_target)).removeClass('droptarget');
          this.env.folder_coords[this.env.last_folder_target].on = 0;
          this.env.last_folder_target = null;
        }
        if (layerclass != oldclass && this.contact_list && this.contact_list.draglayer)
          this.contact_list.draglayer.attr('class', layerclass);
        return;
      }

      // over the folders
      for (k in this.env.folder_coords) {
        pos = this.env.folder_coords[k];
        if (mouse.x >= pos.x1 && mouse.x < pos.x2 && mouse.y >= pos.y1 && mouse.y < pos.y2) {
          if (check = this.check_droptarget(k)) {
            li = this.get_folder_li(k);
            div = $(li.getElementsByTagName('div')[0]);

            // if the folder is collapsed, expand it after 1sec and restart the drag & drop process.
            if (div.hasClass('collapsed')) {
              if (this.folder_auto_timer)
                clearTimeout(this.folder_auto_timer);

              this.folder_auto_expand = this.env.mailboxes[k].id;
              this.folder_auto_timer = setTimeout(function() {
                rcmail.command('collapse-folder', rcmail.folder_auto_expand);
                rcmail.drag_start(null);
              }, 1000);
            }
            else if (this.folder_auto_timer) {
              clearTimeout(this.folder_auto_timer);
              this.folder_auto_timer = null;
              this.folder_auto_expand = null;
            }

            $(li).addClass('droptarget');
            this.env.folder_coords[k].on = 1;
            this.env.last_folder_target = k;
            layerclass = 'draglayer' + (check > 1 ? 'copy' : 'normal');
          }
          // Clear target, otherwise drag end will trigger move into last valid droptarget
          else
            this.env.last_folder_target = null;
        }
        else if (pos.on) {
          $(this.get_folder_li(k)).removeClass('droptarget');
          this.env.folder_coords[k].on = 0;
        }
      }

      if (layerclass != oldclass && this.contact_list && this.contact_list.draglayer)
        this.contact_list.draglayer.attr('class', layerclass);
    }
  };

  this.collapse_folder = function(name)
  {
    var li = this.get_folder_li(name, '', true),
      div = $('div:first', li),
      ul = $('ul:first', li);

    if (div.hasClass('collapsed')) {
      ul.show();
      div.removeClass('collapsed').addClass('expanded');
      var reg = new RegExp('&'+urlencode(name)+'&');
      this.env.collapsed_folders = this.env.collapsed_folders.replace(reg, '');
    }
    else if (div.hasClass('expanded')) {
      ul.hide();
      div.removeClass('expanded').addClass('collapsed');
      this.env.collapsed_folders = this.env.collapsed_folders+'&'+urlencode(name)+'&';

      // select the folder if one of its childs is currently selected
      // don't select if it's virtual (#1488346)
      if (this.env.mailbox.indexOf(name + this.env.delimiter) == 0 && !$(li).hasClass('virtual'))
        this.command('list', name);
    }
    else
      return;

    // Work around a bug in IE6 and IE7, see #1485309
    if (bw.ie6 || bw.ie7) {
      var siblings = li.nextSibling ? li.nextSibling.getElementsByTagName('ul') : null;
      if (siblings && siblings.length && (li = siblings[0]) && li.style && li.style.display != 'none') {
        li.style.display = 'none';
        li.style.display = '';
      }
    }

    this.command('save-pref', { name: 'collapsed_folders', value: this.env.collapsed_folders });
    this.set_unread_count_display(name, false);
  };

  this.doc_mouse_up = function(e)
  {
    var model, list, id;

    // ignore event if jquery UI dialog is open
    if ($(rcube_event.get_target(e)).closest('.ui-dialog, .ui-widget-overlay').length)
      return;

    if (list = this.message_list)
      model = this.env.mailboxes;
    else if (list = this.contact_list)
      model = this.env.contactfolders;
    else if (this.ksearch_value)
      this.ksearch_blur();

    if (list && !rcube_mouse_is_over(e, list.list.parentNode))
      list.blur();

    // handle mouse release when dragging
    if (this.drag_active && model && this.env.last_folder_target) {
      var target = model[this.env.last_folder_target];

      $(this.get_folder_li(this.env.last_folder_target)).removeClass('droptarget');
      this.env.last_folder_target = null;
      list.draglayer.hide();

      if (!this.drag_menu(e, target))
        this.command('moveto', target);
    }

    // reset 'pressed' buttons
    if (this.buttons_sel) {
      for (id in this.buttons_sel)
        if (typeof id !== 'function')
          this.button_out(this.buttons_sel[id], id);
      this.buttons_sel = {};
    }
  };

  this.click_on_list = function(e)
  {
    if (this.gui_objects.qsearchbox)
      this.gui_objects.qsearchbox.blur();

    if (this.message_list)
      this.message_list.focus();
    else if (this.contact_list)
      this.contact_list.focus();

    return true;
  };

  this.msglist_select = function(list)
  {
    if (this.preview_timer)
      clearTimeout(this.preview_timer);
    if (this.preview_read_timer)
      clearTimeout(this.preview_read_timer);

    var selected = list.get_single_selection();

    this.enable_command(this.env.message_commands, selected != null);
    if (selected) {
      // Hide certain command buttons when Drafts folder is selected
      if (this.env.mailbox == this.env.drafts_mailbox)
        this.enable_command('reply', 'reply-all', 'reply-list', 'forward', 'forward-attachment', 'forward-inline', false);
      // Disable reply-list when List-Post header is not set
      else {
        var msg = this.env.messages[selected];
        if (!msg.ml)
          this.enable_command('reply-list', false);
      }
    }
    // Multi-message commands
    this.enable_command('delete', 'moveto', 'copy', 'mark', 'forward', 'forward-attachment', list.selection.length > 0);

    // reset all-pages-selection
    if (selected || (list.selection.length && list.selection.length != list.rowcount))
      this.select_all_mode = false;

    // start timer for message preview (wait for double click)
    if (selected && this.env.contentframe && !list.multi_selecting && !this.dummy_select)
      this.preview_timer = setTimeout(function() { ref.msglist_get_preview(); }, this.dblclick_time);
    else if (this.env.contentframe)
      this.show_contentframe(false);
  };

  // This allow as to re-select selected message and display it in preview frame
  this.msglist_click = function(list)
  {
    if (list.multi_selecting || !this.env.contentframe)
      return;

    if (list.get_single_selection())
      return;

    var win = this.get_frame_window(this.env.contentframe);

    if (win && win.location.href.indexOf(this.env.blankpage) >= 0) {
      if (this.preview_timer)
        clearTimeout(this.preview_timer);
      if (this.preview_read_timer)
        clearTimeout(this.preview_read_timer);

      this.preview_timer = setTimeout(function() { ref.msglist_get_preview(); }, this.dblclick_time);
    }
  };

  this.msglist_dbl_click = function(list)
  {
    if (this.preview_timer)
      clearTimeout(this.preview_timer);
    if (this.preview_read_timer)
      clearTimeout(this.preview_read_timer);

    var uid = list.get_single_selection();

    if (uid && this.env.mailbox == this.env.drafts_mailbox)
      this.open_compose_step({ _draft_uid: uid, _mbox: this.env.mailbox });
    else if (uid)
      this.show_message(uid, false, false);
  };

  this.msglist_keypress = function(list)
  {
    if (list.modkey == CONTROL_KEY)
      return;

    if (list.key_pressed == list.ENTER_KEY)
      this.command('show');
    else if (list.key_pressed == list.DELETE_KEY || list.key_pressed == list.BACKSPACE_KEY)
      this.command('delete');
    else if (list.key_pressed == 33)
      this.command('previouspage');
    else if (list.key_pressed == 34)
      this.command('nextpage');
  };

  this.msglist_get_preview = function()
  {
    var uid = this.get_single_uid();
    if (uid && this.env.contentframe && !this.drag_active)
      this.show_message(uid, false, true);
    else if (this.env.contentframe)
      this.show_contentframe(false);
  };

  this.msglist_expand = function(row)
  {
    if (this.env.messages[row.uid])
      this.env.messages[row.uid].expanded = row.expanded;
    $(row.obj)[row.expanded?'addClass':'removeClass']('expanded');
  };

  this.msglist_set_coltypes = function(list)
  {
    var i, found, name, cols = list.list.tHead.rows[0].cells;

    this.env.coltypes = [];

    for (i=0; i<cols.length; i++)
      if (cols[i].id && cols[i].id.match(/^rcm/)) {
        name = cols[i].id.replace(/^rcm/, '');
        this.env.coltypes.push(name);
      }

    if ((found = $.inArray('flag', this.env.coltypes)) >= 0)
      this.env.flagged_col = found;

    if ((found = $.inArray('subject', this.env.coltypes)) >= 0)
      this.env.subject_col = found;

    this.command('save-pref', { name: 'list_cols', value: this.env.coltypes, session: 'list_attrib/columns' });
  };

  this.check_droptarget = function(id)
  {
    if (this.task == 'mail')
      return (this.env.mailboxes[id] && this.env.mailboxes[id].id != this.env.mailbox && !this.env.mailboxes[id].virtual) ? 1 : 0;

    if (this.task == 'settings')
      return id != this.env.mailbox ? 1 : 0;

    if (this.task == 'addressbook') {
      if (id != this.env.source && this.env.contactfolders[id]) {
        // droptarget is a group - contact add to group action
        if (this.env.contactfolders[id].type == 'group') {
          var target_abook = this.env.contactfolders[id].source;
          if (this.env.contactfolders[id].id != this.env.group && !this.env.contactfolders[target_abook].readonly) {
            // search result may contain contacts from many sources
            return (this.env.selection_sources.length > 1 || $.inArray(target_abook, this.env.selection_sources) == -1) ? 2 : 1;
          }
        }
        // droptarget is a (writable) addressbook - contact copy action
        else if (!this.env.contactfolders[id].readonly) {
          // search result may contain contacts from many sources
          return (this.env.selection_sources.length > 1 || $.inArray(id, this.env.selection_sources) == -1) ? 2 : 0;
        }
      }
    }

    return 0;
  };

  this.open_window = function(url, width)
  {
    var win = this.is_framed() ? parent.window : window,
      page = $(win),
      page_width = page.width(),
      page_height = bw.mz ? $('body', win).height() : page.height(),
      w = Math.min(width, page_width),
      h = page_height, // always use same height
      l = (win.screenLeft || win.screenX) + 20,
      t = (win.screenTop || win.screenY) + 20,
      wname = 'rcmextwin' + new Date().getTime(),
      extwin = window.open(url + (url.match(/\?/) ? '&' : '?') + '_extwin=1', wname,
        'width='+w+',height='+h+',top='+t+',left='+l+',resizable=yes,toolbar=no,status=no,location=no');

    // write loading... message to empty windows
    if (!url && extwin.document) {
      extwin.document.write('<html><body>' + this.get_label('loading') + '</body></html>');
    }

    // focus window, delayed to bring to front
    window.setTimeout(function() { extwin.focus(); }, 10);

    return wname;
  };


  /*********************************************************/
  /*********     (message) list functionality      *********/
  /*********************************************************/

  this.init_message_row = function(row)
  {
    var expando, self = this, uid = row.uid,
      status_icon = (this.env.status_col != null ? 'status' : 'msg') + 'icn' + row.uid;

    if (uid && this.env.messages[uid])
      $.extend(row, this.env.messages[uid]);

    // set eventhandler to status icon
    if (row.icon = document.getElementById(status_icon)) {
      row.icon._row = row.obj;
      row.icon.onmousedown = function(e) { self.command('toggle_status', this); rcube_event.cancel(e); };
    }

    // save message icon position too
    if (this.env.status_col != null)
      row.msgicon = document.getElementById('msgicn'+row.uid);
    else
      row.msgicon = row.icon;

    // set eventhandler to flag icon, if icon found
    if (this.env.flagged_col != null && (row.flagicon = document.getElementById('flagicn'+row.uid))) {
      row.flagicon._row = row.obj;
      row.flagicon.onmousedown = function(e) { self.command('toggle_flag', this); rcube_event.cancel(e); };
    }

    if (!row.depth && row.has_children && (expando = document.getElementById('rcmexpando'+row.uid))) {
      row.expando = expando;
      expando.onmousedown = function(e) { return self.expand_message_row(e, uid); };
      if (bw.touch) {
        expando.addEventListener('touchend', function(e) {
          if (e.changedTouches.length == 1) {
            self.expand_message_row(e, uid);
            return rcube_event.cancel(e);
          }
        }, false);
      }
    }

    this.triggerEvent('insertrow', { uid:uid, row:row });
  };

  // create a table row in the message list
  this.add_message_row = function(uid, cols, flags, attop)
  {
    if (!this.gui_objects.messagelist || !this.message_list)
      return false;

    // Prevent from adding messages from different folder (#1487752)
    if (flags.mbox != this.env.mailbox && !flags.skip_mbox_check)
      return false;

    if (!this.env.messages[uid])
      this.env.messages[uid] = {};

    // merge flags over local message object
    $.extend(this.env.messages[uid], {
      deleted: flags.deleted?1:0,
      replied: flags.answered?1:0,
      unread: !flags.seen?1:0,
      forwarded: flags.forwarded?1:0,
      flagged: flags.flagged?1:0,
      has_children: flags.has_children?1:0,
      depth: flags.depth?flags.depth:0,
      unread_children: flags.unread_children?flags.unread_children:0,
      parent_uid: flags.parent_uid?flags.parent_uid:0,
      selected: this.select_all_mode || this.message_list.in_selection(uid),
      ml: flags.ml?1:0,
      ctype: flags.ctype,
      // flags from plugins
      flags: flags.extra_flags
    });

    var c, n, col, html, css_class,
      tree = '', expando = '',
      list = this.message_list,
      rows = list.rows,
      message = this.env.messages[uid],
      row_class = 'message'
        + (!flags.seen ? ' unread' : '')
        + (flags.deleted ? ' deleted' : '')
        + (flags.flagged ? ' flagged' : '')
        + (flags.unread_children && flags.seen && !this.env.autoexpand_threads ? ' unroot' : '')
        + (message.selected ? ' selected' : ''),
      // for performance use DOM instead of jQuery here
      row = document.createElement('tr');

    row.id = 'rcmrow'+uid;

    // message status icons
    css_class = 'msgicon';
    if (this.env.status_col === null) {
      css_class += ' status';
      if (flags.deleted)
        css_class += ' deleted';
      else if (!flags.seen)
        css_class += ' unread';
      else if (flags.unread_children > 0)
        css_class += ' unreadchildren';
    }
    if (flags.answered)
      css_class += ' replied';
    if (flags.forwarded)
      css_class += ' forwarded';

    // update selection
    if (message.selected && !list.in_selection(uid))
      list.selection.push(uid);

    // threads
    if (this.env.threading) {
      if (message.depth) {
        // This assumes that div width is hardcoded to 15px,
        tree += '<span id="rcmtab' + uid + '" class="branch" style="width:' + (message.depth * 15) + 'px;">&nbsp;&nbsp;</span>';

        if ((rows[message.parent_uid] && rows[message.parent_uid].expanded === false)
          || ((this.env.autoexpand_threads == 0 || this.env.autoexpand_threads == 2) &&
            (!rows[message.parent_uid] || !rows[message.parent_uid].expanded))
        ) {
          row.style.display = 'none';
          message.expanded = false;
        }
        else
          message.expanded = true;

        row_class += ' thread expanded';
      }
      else if (message.has_children) {
        if (message.expanded === undefined && (this.env.autoexpand_threads == 1 || (this.env.autoexpand_threads == 2 && message.unread_children))) {
          message.expanded = true;
        }

        expando = '<div id="rcmexpando' + uid + '" class="' + (message.expanded ? 'expanded' : 'collapsed') + '">&nbsp;&nbsp;</div>';
        row_class += ' thread' + (message.expanded? ' expanded' : '');
      }
    }

    tree += '<span id="msgicn'+uid+'" class="'+css_class+'">&nbsp;</span>';
    row.className = row_class;

    // build subject link 
    if (!bw.ie && cols.subject) {
      var action = flags.mbox == this.env.drafts_mailbox ? 'compose' : 'show';
      var uid_param = flags.mbox == this.env.drafts_mailbox ? '_draft_uid' : '_uid';
      cols.subject = '<a href="./?_task=mail&_action='+action+'&_mbox='+urlencode(flags.mbox)+'&'+uid_param+'='+uid+'"'+
        ' onclick="return rcube_event.cancel(event)" onmouseover="rcube_webmail.long_subject_title(this,'+(message.depth+1)+')">'+cols.subject+'</a>';
    }

    // add each submitted col
    for (n in this.env.coltypes) {
      c = this.env.coltypes[n];
      col = document.createElement('td');
      col.className = String(c).toLowerCase();

      if (c == 'flag') {
        css_class = (flags.flagged ? 'flagged' : 'unflagged');
        html = '<span id="flagicn'+uid+'" class="'+css_class+'">&nbsp;</span>';
      }
      else if (c == 'attachment') {
        if (/application\/|multipart\/(m|signed)/.test(flags.ctype))
          html = '<span class="attachment">&nbsp;</span>';
        else if (/multipart\/report/.test(flags.ctype))
          html = '<span class="report">&nbsp;</span>';
        else
          html = '&nbsp;';
      }
      else if (c == 'status') {
        if (flags.deleted)
          css_class = 'deleted';
        else if (!flags.seen)
          css_class = 'unread';
        else if (flags.unread_children > 0)
          css_class = 'unreadchildren';
        else
          css_class = 'msgicon';
        html = '<span id="statusicn'+uid+'" class="'+css_class+'">&nbsp;</span>';
      }
      else if (c == 'threads')
        html = expando;
      else if (c == 'subject') {
        if (bw.ie) {
          col.onmouseover = function() { rcube_webmail.long_subject_title_ie(this, message.depth+1); };
          if (bw.ie8)
            tree = '<span></span>' + tree; // #1487821
        }
        html = tree + cols[c];
      }
      else if (c == 'priority') {
        if (flags.prio > 0 && flags.prio < 6)
          html = '<span class="prio'+flags.prio+'">&nbsp;</span>';
        else
          html = '&nbsp;';
      }
      else
        html = cols[c];

      if (html)
        col.innerHTML = html;

      row.appendChild(col);
    }

    list.insert_row(row, attop);

    // remove 'old' row
    if (attop && this.env.pagesize && list.rowcount > this.env.pagesize) {
      var uid = list.get_last_row();
      list.remove_row(uid);
      list.clear_selection(uid);
    }
  };

  this.set_list_sorting = function(sort_col, sort_order)
  {
    // set table header class
    $('#rcm'+this.env.sort_col).removeClass('sorted'+(this.env.sort_order.toUpperCase()));
    if (sort_col)
      $('#rcm'+sort_col).addClass('sorted'+sort_order);

    this.env.sort_col = sort_col;
    this.env.sort_order = sort_order;
  };

  this.set_list_options = function(cols, sort_col, sort_order, threads)
  {
    var update, post_data = {};

    if (sort_col === undefined)
      sort_col = this.env.sort_col;
    if (!sort_order)
      sort_order = this.env.sort_order;

    if (this.env.sort_col != sort_col || this.env.sort_order != sort_order) {
      update = 1;
      this.set_list_sorting(sort_col, sort_order);
    }

    if (this.env.threading != threads) {
      update = 1;
      post_data._threads = threads;
    }

    if (cols && cols.length) {
      // make sure new columns are added at the end of the list
      var i, idx, name, newcols = [], oldcols = this.env.coltypes;
      for (i=0; i<oldcols.length; i++) {
        name = oldcols[i];
        idx = $.inArray(name, cols);
        if (idx != -1) {
          newcols.push(name);
          delete cols[idx];
        }
      }
      for (i=0; i<cols.length; i++)
        if (cols[i])
          newcols.push(cols[i]);

      if (newcols.join() != oldcols.join()) {
        update = 1;
        post_data._cols = newcols.join(',');
      }
    }

    if (update)
      this.list_mailbox('', '', sort_col+'_'+sort_order, post_data);
  };

  // when user double-clicks on a row
  this.show_message = function(id, safe, preview)
  {
    if (!id)
      return;

    var win, target = window,
      action = preview ? 'preview': 'show',
      url = '&_action='+action+'&_uid='+id+'&_mbox='+urlencode(this.env.mailbox);

    if (preview && (win = this.get_frame_window(this.env.contentframe))) {
      target = win;
      url += '&_framed=1';
    }

    if (safe)
      url += '&_safe=1';

    // also send search request to get the right messages
    if (this.env.search_request)
      url += '&_search='+this.env.search_request;

    // add browser capabilities, so we can properly handle attachments
    url += '&_caps='+urlencode(this.browser_capabilities());

    if (this.env.extwin)
      url += '&_extwin=1';

    if (preview && String(target.location.href).indexOf(url) >= 0) {
      this.show_contentframe(true);
    }
    else {
      if (!preview && this.env.message_extwin && !this.env.extwin)
        this.open_window(this.env.comm_path+url, 1000);
      else
        this.location_href(this.env.comm_path+url, target, true);

      // mark as read and change mbox unread counter
      if (preview && this.message_list && this.message_list.rows[id] && this.message_list.rows[id].unread && this.env.preview_pane_mark_read >= 0) {
        this.preview_read_timer = setTimeout(function() {
          ref.set_message(id, 'unread', false);
          ref.update_thread_root(id, 'read');
          if (ref.env.unread_counts[ref.env.mailbox]) {
            ref.env.unread_counts[ref.env.mailbox] -= 1;
            ref.set_unread_count(ref.env.mailbox, ref.env.unread_counts[ref.env.mailbox], ref.env.mailbox == 'INBOX');
          }
          if (ref.env.preview_pane_mark_read > 0)
            ref.http_post('mark', {_uid: id, _flag: 'read', _quiet: 1});
        }, this.env.preview_pane_mark_read * 1000);
      }
    }
  };

  this.show_contentframe = function(show)
  {
    var frame, win, name = this.env.contentframe;

    if (name && (frame = this.get_frame_element(name))) {
      if (!show && (win = this.get_frame_window(name))) {
        if (win.location && win.location.href.indexOf(this.env.blankpage)<0)
          win.location.href = this.env.blankpage;
      }
      else if (!bw.safari && !bw.konq)
        $(frame)[show ? 'show' : 'hide']();
    }

    if (!show && this.busy)
      this.set_busy(false, null, this.env.frame_lock);
  };

  this.get_frame_element = function(id)
  {
    var frame;

    if (id && (frame = document.getElementById(id)))
      return frame;
  };

  this.get_frame_window = function(id)
  {
    var frame = this.get_frame_element(id);

    if (frame && frame.name && window.frames)
      return window.frames[frame.name];
  };

  this.lock_frame = function()
  {
    if (!this.env.frame_lock)
      (this.is_framed() ? parent.rcmail : this).env.frame_lock = this.set_busy(true, 'loading');
  };

  // list a specific page
  this.list_page = function(page)
  {
    if (page == 'next')
      page = this.env.current_page+1;
    else if (page == 'last')
      page = this.env.pagecount;
    else if (page == 'prev' && this.env.current_page > 1)
      page = this.env.current_page-1;
    else if (page == 'first' && this.env.current_page > 1)
      page = 1;

    if (page > 0 && page <= this.env.pagecount) {
      this.env.current_page = page;

      if (this.task == 'addressbook' || this.contact_list)
        this.list_contacts(this.env.source, this.env.group, page);
      else if (this.task == 'mail')
        this.list_mailbox(this.env.mailbox, page);
    }
  };

  // sends request to check for recent messages
  this.checkmail = function()
  {
    var lock = this.set_busy(true, 'checkingmail'),
      params = this.check_recent_params();

    this.http_request('check-recent', params, lock);
  };

  // list messages of a specific mailbox using filter
  this.filter_mailbox = function(filter)
  {
    var lock = this.set_busy(true, 'searching');

    this.clear_message_list();

    // reset vars
    this.env.current_page = 1;
    this.http_request('search', this.search_params(false, filter), lock);
  };

  // list messages of a specific mailbox
  this.list_mailbox = function(mbox, page, sort, url)
  {
    var win, target = window;

    if (typeof url != 'object')
      url = {};

    if (!mbox)
      mbox = this.env.mailbox ? this.env.mailbox : 'INBOX';

    // add sort to url if set
    if (sort)
      url._sort = sort;

    // also send search request to get the right messages
    if (this.env.search_request)
      url._search = this.env.search_request;

    // set page=1 if changeing to another mailbox
    if (this.env.mailbox != mbox) {
      page = 1;
      this.env.current_page = page;
      this.select_all_mode = false;
    }

    // unselect selected messages and clear the list and message data
    this.clear_message_list();

    if (mbox != this.env.mailbox || (mbox == this.env.mailbox && !page && !sort))
      url._refresh = 1;

    this.select_folder(mbox, '', true);
    this.unmark_folder(mbox, 'recent', '', true);
    this.env.mailbox = mbox;

    // load message list remotely
    if (this.gui_objects.messagelist) {
      this.list_mailbox_remote(mbox, page, url);
      return;
    }

    if (win = this.get_frame_window(this.env.contentframe)) {
      target = win;
      url._framed = 1;
    }

    // load message list to target frame/window
    if (mbox) {
      this.set_busy(true, 'loading');
      url._mbox = mbox;
      if (page)
        url._page = page;
      this.location_href(url, target);
    }
  };

  this.clear_message_list = function()
  {
      this.env.messages = {};
      this.last_selected = 0;

      this.show_contentframe(false);
      if (this.message_list)
        this.message_list.clear(true);
  };

  // send remote request to load message list
  this.list_mailbox_remote = function(mbox, page, post_data)
  {
    // clear message list first
    this.message_list.clear();

    var lock = this.set_busy(true, 'loading');

    if (typeof post_data != 'object')
      post_data = {};
    post_data._mbox = mbox;
    if (page)
      post_data._page = page;

    this.http_request('list', post_data, lock);
  };

  // removes messages that doesn't exists from list selection array
  this.update_selection = function()
  {
    var selected = this.message_list.selection,
      rows = this.message_list.rows,
      i, selection = [];

    for (i in selected)
      if (rows[selected[i]])
        selection.push(selected[i]);

    this.message_list.selection = selection;
  }

  // expand all threads with unread children
  this.expand_unread = function()
  {
    var r, tbody = this.gui_objects.messagelist.tBodies[0],
      new_row = tbody.firstChild;

    while (new_row) {
      if (new_row.nodeType == 1 && (r = this.message_list.rows[new_row.uid]) && r.unread_children) {
        this.message_list.expand_all(r);
        this.set_unread_children(r.uid);
      }
      new_row = new_row.nextSibling;
    }
    return false;
  };

  // thread expanding/collapsing handler
  this.expand_message_row = function(e, uid)
  {
    var row = this.message_list.rows[uid];

    // handle unread_children mark
    row.expanded = !row.expanded;
    this.set_unread_children(uid);
    row.expanded = !row.expanded;

    this.message_list.expand_row(e, uid);
  };

  // message list expanding
  this.expand_threads = function()
  {
    if (!this.env.threading || !this.env.autoexpand_threads || !this.message_list)
      return;

    switch (this.env.autoexpand_threads) {
      case 2: this.expand_unread(); break;
      case 1: this.message_list.expand_all(); break;
    }
  };

  // Initializes threads indicators/expanders after list update
  this.init_threads = function(roots, mbox)
  {
    // #1487752
    if (mbox && mbox != this.env.mailbox)
      return false;

    for (var n=0, len=roots.length; n<len; n++)
      this.add_tree_icons(roots[n]);
    this.expand_threads();
  };

  // adds threads tree icons to the list (or specified thread)
  this.add_tree_icons = function(root)
  {
    var i, l, r, n, len, pos, tmp = [], uid = [],
      row, rows = this.message_list.rows;

    if (root)
      row = rows[root] ? rows[root].obj : null;
    else
      row = this.message_list.list.tBodies[0].firstChild;

    while (row) {
      if (row.nodeType == 1 && (r = rows[row.uid])) {
        if (r.depth) {
          for (i=tmp.length-1; i>=0; i--) {
            len = tmp[i].length;
            if (len > r.depth) {
              pos = len - r.depth;
              if (!(tmp[i][pos] & 2))
                tmp[i][pos] = tmp[i][pos] ? tmp[i][pos]+2 : 2;
            }
            else if (len == r.depth) {
              if (!(tmp[i][0] & 2))
                tmp[i][0] += 2;
            }
            if (r.depth > len)
              break;
          }

          tmp.push(new Array(r.depth));
          tmp[tmp.length-1][0] = 1;
          uid.push(r.uid);
        }
        else {
          if (tmp.length) {
            for (i in tmp) {
              this.set_tree_icons(uid[i], tmp[i]);
            }
            tmp = [];
            uid = [];
          }
          if (root && row != rows[root].obj)
            break;
        }
      }
      row = row.nextSibling;
    }

    if (tmp.length) {
      for (i in tmp) {
        this.set_tree_icons(uid[i], tmp[i]);
      }
    }
  };

  // adds tree icons to specified message row
  this.set_tree_icons = function(uid, tree)
  {
    var i, divs = [], html = '', len = tree.length;

    for (i=0; i<len; i++) {
      if (tree[i] > 2)
        divs.push({'class': 'l3', width: 15});
      else if (tree[i] > 1)
        divs.push({'class': 'l2', width: 15});
      else if (tree[i] > 0)
        divs.push({'class': 'l1', width: 15});
      // separator div
      else if (divs.length && !divs[divs.length-1]['class'])
        divs[divs.length-1].width += 15;
      else
        divs.push({'class': null, width: 15});
    }

    for (i=divs.length-1; i>=0; i--) {
      if (divs[i]['class'])
        html += '<div class="tree '+divs[i]['class']+'" />';
      else
        html += '<div style="width:'+divs[i].width+'px" />';
    }

    if (html)
      $('#rcmtab'+uid).html(html);
  };

  // update parent in a thread
  this.update_thread_root = function(uid, flag)
  {
    if (!this.env.threading)
      return;

    var root = this.message_list.find_root(uid);

    if (uid == root)
      return;

    var p = this.message_list.rows[root];

    if (flag == 'read' && p.unread_children) {
      p.unread_children--;
    }
    else if (flag == 'unread' && p.has_children) {
      // unread_children may be undefined
      p.unread_children = p.unread_children ? p.unread_children + 1 : 1;
    }
    else {
      return;
    }

    this.set_message_icon(root);
    this.set_unread_children(root);
  };

  // update thread indicators for all messages in a thread below the specified message
  // return number of removed/added root level messages
  this.update_thread = function (uid)
  {
    if (!this.env.threading)
      return 0;

    var r, parent, count = 0,
      rows = this.message_list.rows,
      row = rows[uid],
      depth = rows[uid].depth,
      roots = [];

    if (!row.depth) // root message: decrease roots count
      count--;
    else if (row.unread) {
      // update unread_children for thread root
      parent = this.message_list.find_root(uid);
      rows[parent].unread_children--;
      this.set_unread_children(parent);
    }

    parent = row.parent_uid;

    // childrens
    row = row.obj.nextSibling;
    while (row) {
      if (row.nodeType == 1 && (r = rows[row.uid])) {
        if (!r.depth || r.depth <= depth)
          break;

        r.depth--; // move left
        // reset width and clear the content of a tab, icons will be added later
        $('#rcmtab'+r.uid).width(r.depth * 15).html('');
        if (!r.depth) { // a new root
          count++; // increase roots count
          r.parent_uid = 0;
          if (r.has_children) {
            // replace 'leaf' with 'collapsed'
            $('#rcmrow'+r.uid+' '+'.leaf:first')
              .attr('id', 'rcmexpando' + r.uid)
              .attr('class', (r.obj.style.display != 'none' ? 'expanded' : 'collapsed'))
              .bind('mousedown', {uid:r.uid, p:this},
                function(e) { return e.data.p.expand_message_row(e, e.data.uid); });

            r.unread_children = 0;
            roots.push(r);
          }
          // show if it was hidden
          if (r.obj.style.display == 'none')
            $(r.obj).show();
        }
        else {
          if (r.depth == depth)
            r.parent_uid = parent;
          if (r.unread && roots.length)
            roots[roots.length-1].unread_children++;
        }
      }
      row = row.nextSibling;
    }

    // update unread_children for roots
    for (var i=0; i<roots.length; i++)
      this.set_unread_children(roots[i].uid);

    return count;
  };

  this.delete_excessive_thread_rows = function()
  {
    var rows = this.message_list.rows,
      tbody = this.message_list.list.tBodies[0],
      row = tbody.firstChild,
      cnt = this.env.pagesize + 1;

    while (row) {
      if (row.nodeType == 1 && (r = rows[row.uid])) {
        if (!r.depth && cnt)
          cnt--;

        if (!cnt)
          this.message_list.remove_row(row.uid);
      }
      row = row.nextSibling;
    }
  };

  // set message icon
  this.set_message_icon = function(uid)
  {
    var css_class,
      row = this.message_list.rows[uid];

    if (!row)
      return false;

    if (row.icon) {
      css_class = 'msgicon';
      if (row.deleted)
        css_class += ' deleted';
      else if (row.unread)
        css_class += ' unread';
      else if (row.unread_children)
        css_class += ' unreadchildren';
      if (row.msgicon == row.icon) {
        if (row.replied)
          css_class += ' replied';
        if (row.forwarded)
          css_class += ' forwarded';
        css_class += ' status';
      }

      row.icon.className = css_class;
    }

    if (row.msgicon && row.msgicon != row.icon) {
      css_class = 'msgicon';
      if (!row.unread && row.unread_children)
        css_class += ' unreadchildren';
      if (row.replied)
        css_class += ' replied';
      if (row.forwarded)
        css_class += ' forwarded';

      row.msgicon.className = css_class;
    }

    if (row.flagicon) {
      css_class = (row.flagged ? 'flagged' : 'unflagged');
      row.flagicon.className = css_class;
    }
  };

  // set message status
  this.set_message_status = function(uid, flag, status)
  {
    var row = this.message_list.rows[uid];

    if (!row)
      return false;

    if (flag == 'unread')
      row.unread = status;
    else if(flag == 'deleted')
      row.deleted = status;
    else if (flag == 'replied')
      row.replied = status;
    else if (flag == 'forwarded')
      row.forwarded = status;
    else if (flag == 'flagged')
      row.flagged = status;
  };

  // set message row status, class and icon
  this.set_message = function(uid, flag, status)
  {
    var row = this.message_list && this.message_list.rows[uid];

    if (!row)
      return false;

    if (flag)
      this.set_message_status(uid, flag, status);

    var rowobj = $(row.obj);

    if (row.unread && !rowobj.hasClass('unread'))
      rowobj.addClass('unread');
    else if (!row.unread && rowobj.hasClass('unread'))
      rowobj.removeClass('unread');

    if (row.deleted && !rowobj.hasClass('deleted'))
      rowobj.addClass('deleted');
    else if (!row.deleted && rowobj.hasClass('deleted'))
      rowobj.removeClass('deleted');

    if (row.flagged && !rowobj.hasClass('flagged'))
      rowobj.addClass('flagged');
    else if (!row.flagged && rowobj.hasClass('flagged'))
      rowobj.removeClass('flagged');

    this.set_unread_children(uid);
    this.set_message_icon(uid);
  };

  // sets unroot (unread_children) class of parent row
  this.set_unread_children = function(uid)
  {
    var row = this.message_list.rows[uid];

    if (row.parent_uid)
      return;

    if (!row.unread && row.unread_children && !row.expanded)
      $(row.obj).addClass('unroot');
    else
      $(row.obj).removeClass('unroot');
  };

  // copy selected messages to the specified mailbox
  this.copy_messages = function(mbox)
  {
    if (mbox && typeof mbox === 'object')
      mbox = mbox.id;

    // exit if current or no mailbox specified
    if (!mbox || mbox == this.env.mailbox)
      return;

    var post_data = this.selection_post_data({_target_mbox: mbox});

    // exit if selection is empty
    if (!post_data._uid)
      return;

    // send request to server
    this.http_post('copy', post_data, this.display_message(this.get_label('copyingmessage'), 'loading'));
  };

  // move selected messages to the specified mailbox
  this.move_messages = function(mbox)
  {
    if (mbox && typeof mbox === 'object')
      mbox = mbox.id;

    // exit if current or no mailbox specified
    if (!mbox || mbox == this.env.mailbox)
      return;

    var lock = false, post_data = this.selection_post_data({_target_mbox: mbox});

    // exit if selection is empty
    if (!post_data._uid)
      return;

    // show wait message
    if (this.env.action == 'show')
      lock = this.set_busy(true, 'movingmessage');
    else
      this.show_contentframe(false);

    // Hide message command buttons until a message is selected
    this.enable_command(this.env.message_commands, false);

    this._with_selected_messages('moveto', post_data, lock);
  };

  // delete selected messages from the current mailbox
  this.delete_messages = function(event)
  {
    var uid, i, len, trash = this.env.trash_mailbox,
      list = this.message_list,
      selection = list ? list.get_selection() : [];

    // exit if no mailbox specified or if selection is empty
    if (!this.env.uid && !selection.length)
      return;

    // also select childs of collapsed rows
    for (i=0, len=selection.length; i<len; i++) {
      uid = selection[i];
      if (list.rows[uid].has_children && !list.rows[uid].expanded)
        list.select_children(uid);
    }

    // if config is set to flag for deletion
    if (this.env.flag_for_deletion) {
      this.mark_message('delete');
      return false;
    }
    // if there isn't a defined trash mailbox or we are in it
    else if (!trash || this.env.mailbox == trash)
      this.permanently_remove_messages();
    // we're in Junk folder and delete_junk is enabled
    else if (this.env.delete_junk && this.env.junk_mailbox && this.env.mailbox == this.env.junk_mailbox)
      this.permanently_remove_messages();
    // if there is a trash mailbox defined and we're not currently in it
    else {
      // if shift was pressed delete it immediately
      if ((list && list.modkey == SHIFT_KEY) || (event && rcube_event.get_modifier(event) == SHIFT_KEY)) {
        if (confirm(this.get_label('deletemessagesconfirm')))
          this.permanently_remove_messages();
      }
      else
        this.move_messages(trash);
    }

    return true;
  };

  // delete the selected messages permanently
  this.permanently_remove_messages = function()
  {
    var post_data = this.selection_post_data();

    // exit if selection is empty
    if (!post_data._uid)
      return;

    this.show_contentframe(false);
    this._with_selected_messages('delete', post_data);
  };

  // Send a specifc moveto/delete request with UIDs of all selected messages
  // @private
  this._with_selected_messages = function(action, post_data, lock)
  {
    var count = 0, msg;

    // update the list (remove rows, clear selection)
    if (this.message_list) {
      var n, id, root, roots = [],
        selection = this.message_list.get_selection();

      for (n=0, len=selection.length; n<len; n++) {
        id = selection[n];

        if (this.env.threading) {
          count += this.update_thread(id);
          root = this.message_list.find_root(id);
          if (root != id && $.inArray(root, roots) < 0) {
            roots.push(root);
          }
        }
        this.message_list.remove_row(id, (this.env.display_next && n == selection.length-1));
      }
      // make sure there are no selected rows
      if (!this.env.display_next)
        this.message_list.clear_selection();
      // update thread tree icons
      for (n=0, len=roots.length; n<len; n++) {
        this.add_tree_icons(roots[n]);
      }
    }

    if (this.env.display_next && this.env.next_uid)
      post_data._next_uid = this.env.next_uid;

    if (count < 0)
      post_data._count = (count*-1);
    // remove threads from the end of the list
    else if (count > 0)
      this.delete_excessive_thread_rows();

    if (!lock) {
      msg = action == 'moveto' ? 'movingmessage' : 'deletingmessage';
      lock = this.display_message(this.get_label(msg), 'loading');
    }

    // send request to server
    this.http_post(action, post_data, lock);
  };

  // build post data for message delete/move/copy/flag requests
  this.selection_post_data = function(data)
  {
    if (typeof(data) != 'object')
      data = {};

    data._mbox = this.env.mailbox;

    if (!data._uid) {
      var uids = this.env.uid ? [this.env.uid] : this.message_list.get_selection();
      data._uid = this.uids_to_list(uids);
    }

    if (this.env.action)
      data._from = this.env.action;

    // also send search request to get the right messages
    if (this.env.search_request)
      data._search = this.env.search_request;

    return data;
  };

  // set a specific flag to one or more messages
  this.mark_message = function(flag, uid)
  {
    var a_uids = [], r_uids = [], len, n, id,
      list = this.message_list;

    if (uid)
      a_uids[0] = uid;
    else if (this.env.uid)
      a_uids[0] = this.env.uid;
    else if (list)
      a_uids = list.get_selection();

    if (!list)
      r_uids = a_uids;
    else {
      list.focus();
      for (n=0, len=a_uids.length; n<len; n++) {
        id = a_uids[n];
        if ((flag == 'read' && list.rows[id].unread)
            || (flag == 'unread' && !list.rows[id].unread)
            || (flag == 'delete' && !list.rows[id].deleted)
            || (flag == 'undelete' && list.rows[id].deleted)
            || (flag == 'flagged' && !list.rows[id].flagged)
            || (flag == 'unflagged' && list.rows[id].flagged))
        {
          r_uids.push(id);
        }
      }
    }

    // nothing to do
    if (!r_uids.length && !this.select_all_mode)
      return;

    switch (flag) {
        case 'read':
        case 'unread':
          this.toggle_read_status(flag, r_uids);
          break;
        case 'delete':
        case 'undelete':
          this.toggle_delete_status(r_uids);
          break;
        case 'flagged':
        case 'unflagged':
          this.toggle_flagged_status(flag, a_uids);
          break;
    }
  };

  // set class to read/unread
  this.toggle_read_status = function(flag, a_uids)
  {
    var i, len = a_uids.length,
      post_data = this.selection_post_data({_uid: this.uids_to_list(a_uids), _flag: flag}),
      lock = this.display_message(this.get_label('markingmessage'), 'loading');

    // mark all message rows as read/unread
    for (i=0; i<len; i++)
      this.set_message(a_uids[i], 'unread', (flag == 'unread' ? true : false));

    this.http_post('mark', post_data, lock);

    for (i=0; i<len; i++)
      this.update_thread_root(a_uids[i], flag);
  };

  // set image to flagged or unflagged
  this.toggle_flagged_status = function(flag, a_uids)
  {
    var i, len = a_uids.length,
      post_data = this.selection_post_data({_uid: this.uids_to_list(a_uids), _flag: flag}),
      lock = this.display_message(this.get_label('markingmessage'), 'loading');

    // mark all message rows as flagged/unflagged
    for (i=0; i<len; i++)
      this.set_message(a_uids[i], 'flagged', (flag == 'flagged' ? true : false));

    this.http_post('mark', post_data, lock);
  };

  // mark all message rows as deleted/undeleted
  this.toggle_delete_status = function(a_uids)
  {
    var len = a_uids.length,
      i, uid, all_deleted = true,
      rows = this.message_list ? this.message_list.rows : [];

    if (len == 1) {
      if (!rows.length || (rows[a_uids[0]] && !rows[a_uids[0]].deleted))
        this.flag_as_deleted(a_uids);
      else
        this.flag_as_undeleted(a_uids);

      return true;
    }

    for (i=0; i<len; i++) {
      uid = a_uids[i];
      if (rows[uid] && !rows[uid].deleted) {
        all_deleted = false;
        break;
      }
    }

    if (all_deleted)
      this.flag_as_undeleted(a_uids);
    else
      this.flag_as_deleted(a_uids);

    return true;
  };

  this.flag_as_undeleted = function(a_uids)
  {
    var i, len = a_uids.length,
      post_data = this.selection_post_data({_uid: this.uids_to_list(a_uids), _flag: 'undelete'}),
      lock = this.display_message(this.get_label('markingmessage'), 'loading');

    for (i=0; i<len; i++)
      this.set_message(a_uids[i], 'deleted', false);

    this.http_post('mark', post_data, lock);
  };

  this.flag_as_deleted = function(a_uids)
  {
    var r_uids = [],
      post_data = this.selection_post_data({_uid: this.uids_to_list(a_uids), _flag: 'delete'}),
      lock = this.display_message(this.get_label('markingmessage'), 'loading'),
      rows = this.message_list ? this.message_list.rows : [],
      count = 0;

    for (var i=0, len=a_uids.length; i<len; i++) {
      uid = a_uids[i];
      if (rows[uid]) {
        if (rows[uid].unread)
          r_uids[r_uids.length] = uid;

        if (this.env.skip_deleted) {
          count += this.update_thread(uid);
          this.message_list.remove_row(uid, (this.env.display_next && i == this.message_list.selection.length-1));
        }
        else
          this.set_message(uid, 'deleted', true);
      }
    }

    // make sure there are no selected rows
    if (this.env.skip_deleted && this.message_list) {
      if(!this.env.display_next)
        this.message_list.clear_selection();
      if (count < 0)
        post_data._count = (count*-1);
      else if (count > 0) 
        // remove threads from the end of the list
        this.delete_excessive_thread_rows();
    }

    // ??
    if (r_uids.length)
      post_data._ruid = this.uids_to_list(r_uids);

    if (this.env.skip_deleted && this.env.display_next && this.env.next_uid)
      post_data._next_uid = this.env.next_uid;

    this.http_post('mark', post_data, lock);
  };

  // flag as read without mark request (called from backend)
  // argument should be a coma-separated list of uids
  this.flag_deleted_as_read = function(uids)
  {
    var icn_src, uid, i, len,
      rows = this.message_list ? this.message_list.rows : [];

    uids = String(uids).split(',');

    for (i=0, len=uids.length; i<len; i++) {
      uid = uids[i];
      if (rows[uid])
        this.set_message(uid, 'unread', false);
    }
  };

  // Converts array of message UIDs to comma-separated list for use in URL
  // with select_all mode checking
  this.uids_to_list = function(uids)
  {
    return this.select_all_mode ? '*' : uids.join(',');
  };

  // Sets title of the delete button
  this.set_button_titles = function()
  {
    var label = 'deletemessage';

    if (!this.env.flag_for_deletion
      && this.env.trash_mailbox && this.env.mailbox != this.env.trash_mailbox
      && (!this.env.delete_junk || !this.env.junk_mailbox || this.env.mailbox != this.env.junk_mailbox)
    )
      label = 'movemessagetotrash';

    this.set_alttext('delete', label);
  };

  /*********************************************************/
  /*********       mailbox folders methods         *********/
  /*********************************************************/

  this.expunge_mailbox = function(mbox)
  {
    var lock, post_data = {_mbox: mbox};

    // lock interface if it's the active mailbox
    if (mbox == this.env.mailbox) {
      lock = this.set_busy(true, 'loading');
      post_data._reload = 1;
      if (this.env.search_request)
        post_data._search = this.env.search_request;
    }

    // send request to server
    this.http_post('expunge', post_data, lock);
  };

  this.purge_mailbox = function(mbox)
  {
    var lock, post_data = {_mbox: mbox};

    if (!confirm(this.get_label('purgefolderconfirm')))
      return false;

    // lock interface if it's the active mailbox
    if (mbox == this.env.mailbox) {
       lock = this.set_busy(true, 'loading');
       post_data._reload = 1;
     }

    // send request to server
    this.http_post('purge', post_data, lock);
  };

  // test if purge command is allowed
  this.purge_mailbox_test = function()
  {
    return (this.env.exists && (this.env.mailbox == this.env.trash_mailbox || this.env.mailbox == this.env.junk_mailbox
      || this.env.mailbox.match('^' + RegExp.escape(this.env.trash_mailbox) + RegExp.escape(this.env.delimiter))
      || this.env.mailbox.match('^' + RegExp.escape(this.env.junk_mailbox) + RegExp.escape(this.env.delimiter))));
  };


  /*********************************************************/
  /*********           login form methods          *********/
  /*********************************************************/

  // handler for keyboard events on the _user field
  this.login_user_keyup = function(e)
  {
    var key = rcube_event.get_keycode(e);
    var passwd = $('#rcmloginpwd');

    // enter
    if (key == 13 && passwd.length && !passwd.val()) {
      passwd.focus();
      return rcube_event.cancel(e);
    }

    return true;
  };


  /*********************************************************/
  /*********        message compose methods        *********/
  /*********************************************************/

  this.open_compose_step = function(p)
  {
    var url = this.url('mail/compose', p);

    // open new compose window
    if (this.env.compose_extwin && !this.env.extwin) {
      this.open_window(url, 1150);
    }
    else {
      this.redirect(url);
      if (this.env.extwin)
        window.resizeTo(Math.max(1150, $(window).width()), $(window).height()+24);
    }
  };

  // init message compose form: set focus and eventhandlers
  this.init_messageform = function()
  {
    if (!this.gui_objects.messageform)
      return false;

    var input_from = $("[name='_from']"),
      input_to = $("[name='_to']"),
      input_subject = $("input[name='_subject']"),
      input_message = $("[name='_message']").get(0),
      html_mode = $("input[name='_is_html']").val() == '1',
      ac_fields = ['cc', 'bcc', 'replyto', 'followupto'],
      ac_props, opener_rc = this.opener();

    // close compose step in opener
    if (opener_rc && opener_rc.env.action == 'compose') {
      setTimeout(function(){ opener.history.back(); }, 100);
      this.env.opened_extwin = true;
    }

    // configure parallel autocompletion
    if (this.env.autocomplete_threads > 0) {
      ac_props = {
        threads: this.env.autocomplete_threads,
        sources: this.env.autocomplete_sources
      };
    }

    // init live search events
    this.init_address_input_events(input_to, ac_props);
    for (var i in ac_fields) {
      this.init_address_input_events($("[name='_"+ac_fields[i]+"']"), ac_props);
    }

    if (!html_mode) {
      this.set_caret_pos(input_message, this.env.top_posting ? 0 : $(input_message).val().length);
      // add signature according to selected identity
      // if we have HTML editor, signature is added in callback
      if (input_from.prop('type') == 'select-one') {
        this.change_identity(input_from[0]);
      }
    }

    if (input_to.val() == '')
      input_to.focus();
    else if (input_subject.val() == '')
      input_subject.focus();
    else if (input_message)
      input_message.focus();

    this.env.compose_focus_elem = document.activeElement;

    // get summary of all field values
    this.compose_field_hash(true);

    // start the auto-save timer
    this.auto_save_start();
  };

  this.init_address_input_events = function(obj, props)
  {
    this.env.recipients_delimiter = this.env.recipients_separator + ' ';

    obj[bw.ie || bw.safari || bw.chrome ? 'keydown' : 'keypress'](function(e) { return ref.ksearch_keydown(e, this, props); })
      .attr('autocomplete', 'off');
  };

  this.submit_messageform = function(draft)
  {
    var form = this.gui_objects.messageform;

    if (!form)
      return;

    // all checks passed, send message
    var msgid = this.set_busy(true, draft ? 'savingmessage' : 'sendingmessage'),
      lang = this.spellcheck_lang(),
      files = [];

    // send files list
    $('li', this.gui_objects.attachmentlist).each(function() { files.push(this.id.replace(/^rcmfile/, '')); });
    $('input[name="_attachments"]', form).val(files.join());

    form.target = 'savetarget';
    form._draft.value = draft ? '1' : '';
    form.action = this.add_url(form.action, '_unlock', msgid);
    form.action = this.add_url(form.action, '_lang', lang);

    // register timer to notify about connection timeout
    this.submit_timer = setTimeout(function(){
      ref.set_busy(false, null, msgid);
      ref.display_message(ref.get_label('requesttimedout'), 'error');
    }, this.env.request_timeout * 1000);

    form.submit();
  };

  this.compose_recipient_select = function(list)
  {
    this.enable_command('add-recipient', list.selection.length > 0);
  };

  this.compose_add_recipient = function(field)
  {
    var recipients = [], input = $('#_'+field), delim = this.env.recipients_delimiter;

    if (this.contact_list && this.contact_list.selection.length) {
      for (var id, n=0; n < this.contact_list.selection.length; n++) {
        id = this.contact_list.selection[n];
        if (id && this.env.contactdata[id]) {
          recipients.push(this.env.contactdata[id]);

          // group is added, expand it
          if (id.charAt(0) == 'E' && this.env.contactdata[id].indexOf('@') < 0 && input.length) {
            var gid = id.substr(1);
            this.group2expand[gid] = { name:this.env.contactdata[id], input:input.get(0) };
            this.http_request('group-expand', {_source: this.env.source, _gid: gid}, false);
          }
        }
      }
    }

    if (recipients.length && input.length) {
      var oldval = input.val(), rx = new RegExp(RegExp.escape(delim) + '\\s*$');
      if (oldval && !rx.test(oldval))
        oldval += delim + ' ';
      input.val(oldval + recipients.join(delim + ' ') + delim + ' ');
      this.triggerEvent('add-recipient', { field:field, recipients:recipients });
    }
  };

  // checks the input fields before sending a message
  this.check_compose_input = function(cmd)
  {
    // check input fields
    var ed, input_to = $("[name='_to']"),
      input_cc = $("[name='_cc']"),
      input_bcc = $("[name='_bcc']"),
      input_from = $("[name='_from']"),
      input_subject = $("[name='_subject']"),
      input_message = $("[name='_message']");

    // check sender (if have no identities)
    if (input_from.prop('type') == 'text' && !rcube_check_email(input_from.val(), true)) {
      alert(this.get_label('nosenderwarning'));
      input_from.focus();
      return false;
    }

    // check for empty recipient
    var recipients = input_to.val() ? input_to.val() : (input_cc.val() ? input_cc.val() : input_bcc.val());
    if (!rcube_check_email(recipients.replace(/^\s+/, '').replace(/[\s,;]+$/, ''), true)) {
      alert(this.get_label('norecipientwarning'));
      input_to.focus();
      return false;
    }

    // check if all files has been uploaded
    for (var key in this.env.attachments) {
      if (typeof this.env.attachments[key] === 'object' && !this.env.attachments[key].complete) {
        alert(this.get_label('notuploadedwarning'));
        return false;
      }
    }

    // display localized warning for missing subject
    if (input_subject.val() == '') {
      var myprompt = $('<div class="prompt">').html('<div class="message">' + this.get_label('nosubjectwarning') + '</div>').appendTo(document.body);
      var prompt_value = $('<input>').attr('type', 'text').attr('size', 30).appendTo(myprompt).val(this.get_label('nosubject'));

      var buttons = {};
      buttons[this.get_label('cancel')] = function(){
        input_subject.focus();
        $(this).dialog('close');
      };
      buttons[this.get_label('sendmessage')] = function(){
        input_subject.val(prompt_value.val());
        $(this).dialog('close');
        ref.command(cmd, { nocheck:true });  // repeat command which triggered this
      };

      myprompt.dialog({
        modal: true,
        resizable: false,
        buttons: buttons,
        close: function(event, ui) { $(this).remove() }
      });
      prompt_value.select();
      return false;
    }

    // Apply spellcheck changes if spell checker is active
    this.stop_spellchecking();

    if (window.tinyMCE)
      ed = tinyMCE.get(this.env.composebody);

    // check for empty body
    if (!ed && input_message.val() == '' && !confirm(this.get_label('nobodywarning'))) {
      input_message.focus();
      return false;
    }
    else if (ed) {
      if (!ed.getContent() && !confirm(this.get_label('nobodywarning'))) {
        ed.focus();
        return false;
      }
      // move body from html editor to textarea (just to be sure, #1485860)
      tinyMCE.triggerSave();
    }

    return true;
  };

  this.toggle_editor = function(props)
  {
    this.stop_spellchecking();

    if (props.mode == 'html') {
      this.plain2html($('#'+props.id).val(), props.id);
      tinyMCE.execCommand('mceAddControl', false, props.id);

      if (this.env.default_font)
        setTimeout(function() {
          $(tinyMCE.get(props.id).getBody()).css('font-family', rcmail.env.default_font);
        }, 500);
    }
    else {
      var thisMCE = tinyMCE.get(props.id), existingHtml;

      if (existingHtml = thisMCE.getContent()) {
        if (!confirm(this.get_label('editorwarning'))) {
          return false;
        }
        this.html2plain(existingHtml, props.id);
      }
      tinyMCE.execCommand('mceRemoveControl', false, props.id);
    }

    return true;
  };

  this.stop_spellchecking = function()
  {
    var ed;

    if (window.tinyMCE && (ed = tinyMCE.get(this.env.composebody))) {
      if (ed.plugins && ed.plugins.spellchecker && ed.plugins.spellchecker.active)
        ed.execCommand('mceSpellCheck');
    }
    else if (ed = this.env.spellcheck) {
      if (ed.state && ed.state != 'ready' && ed.state != 'no_error_found')
        $(ed.spell_span).trigger('click');
    }

    this.spellcheck_state();
  };

  this.spellcheck_state = function()
  {
    var ed, active;

    if (window.tinyMCE && (ed = tinyMCE.get(this.env.composebody)) && ed.plugins && ed.plugins.spellchecker)
      active = ed.plugins.spellchecker.active;
    else if ((ed = this.env.spellcheck) && ed.state)
      active = ed.state != 'ready' && ed.state != 'no_error_found';

    if (rcmail.buttons.spellcheck)
      $('#'+rcmail.buttons.spellcheck[0].id)[active ? 'addClass' : 'removeClass']('selected');

    return active;
  };

  // get selected language
  this.spellcheck_lang = function()
  {
    var ed;

    if (window.tinyMCE && (ed = tinyMCE.get(this.env.composebody)) && ed.plugins && ed.plugins.spellchecker)
      return ed.plugins.spellchecker.selectedLang;
    else if (this.env.spellcheck)
      return GOOGIE_CUR_LANG;
  };

  this.spellcheck_lang_set = function(lang)
  {
    var ed;

    if (window.tinyMCE && (ed = tinyMCE.get(this.env.composebody)) && ed.plugins)
      ed.plugins.spellchecker.selectedLang = lang;
    else if (this.env.spellcheck)
      this.env.spellcheck.setCurrentLanguage(lang);
  };

  // resume spellchecking, highlight provided mispellings without new ajax request
  this.spellcheck_resume = function(ishtml, data)
  {
    if (ishtml) {
      var ed = tinyMCE.get(this.env.composebody);
        sp = ed.plugins.spellchecker;

      sp.active = 1;
      sp._markWords(data);
      ed.nodeChanged();
    }
    else {
      var sp = this.env.spellcheck;
      sp.prepare(false, true);
      sp.processData(data);
    }

    this.spellcheck_state();
  }

  this.set_draft_id = function(id)
  {
    var rc;

    if (!this.env.draft_id && id && (rc = this.opener())) {
      // refresh the drafts folder in opener window
      if (rc.env.task == 'mail' && rc.env.action == '' && rc.env.mailbox == this.env.drafts_mailbox)
        rc.command('checkmail');
    }

    this.env.draft_id = id;
    $("input[name='_draft_saveid']").val(id);
  };

  this.auto_save_start = function()
  {
    if (this.env.draft_autosave)
      this.save_timer = setTimeout(function(){ ref.command("savedraft"); }, this.env.draft_autosave * 1000);

    // Unlock interface now that saving is complete
    this.busy = false;
  };

  this.compose_field_hash = function(save)
  {
    // check input fields
    var ed, i, val, str = '', hash_fields = ['to', 'cc', 'bcc', 'subject'];

    for (i=0; i<hash_fields.length; i++)
      if (val = $('[name="_' + hash_fields[i] + '"]').val())
        str += val + ':';

    if (window.tinyMCE && (ed = tinyMCE.get(this.env.composebody)))
      str += ed.getContent();
    else
      str += $("[name='_message']").val();

    if (this.env.attachments)
      for (var upload_id in this.env.attachments)
        str += upload_id;

    if (save)
      this.cmp_hash = str;

    return str;
  };

  this.change_identity = function(obj, show_sig)
  {
    if (!obj || !obj.options)
      return false;

    if (!show_sig)
      show_sig = this.env.show_sig;

    // first function execution
    if (!this.env.identities_initialized) {
      this.env.identities_initialized = true;
      if (this.env.show_sig_later)
        this.env.show_sig = true;
      if (this.env.opened_extwin)
        return;
    }

    var cursor_pos, p = -1,
      id = obj.options[obj.selectedIndex].value,
      input_message = $("[name='_message']"),
      message = input_message.val(),
      is_html = ($("input[name='_is_html']").val() == '1'),
      sig = this.env.identity;

    // enable manual signature insert
    if (this.env.signatures && this.env.signatures[id]) {
      this.enable_command('insert-sig', true);
      this.env.compose_commands.push('insert-sig');
    }
    else
      this.enable_command('insert-sig', false);

    if (!is_html) {
      // remove the 'old' signature
      if (show_sig && sig && this.env.signatures && this.env.signatures[sig]) {
        sig = this.env.signatures[sig].text;
        sig = sig.replace(/\r\n/g, '\n');

        p = this.env.top_posting ? message.indexOf(sig) : message.lastIndexOf(sig);
        if (p >= 0)
          message = message.substring(0, p) + message.substring(p+sig.length, message.length);
      }
      // add the new signature string
      if (show_sig && this.env.signatures && this.env.signatures[id]) {
        sig = this.env.signatures[id].text;
        sig = sig.replace(/\r\n/g, '\n');

        if (this.env.top_posting) {
          if (p >= 0) { // in place of removed signature
            message = message.substring(0, p) + sig + message.substring(p, message.length);
            cursor_pos = p - 1;
          }
          else if (!message) { // empty message
            cursor_pos = 0;
            message = '\n\n' + sig;
          }
          else if (pos = this.get_caret_pos(input_message.get(0))) { // at cursor position
            message = message.substring(0, pos) + '\n' + sig + '\n\n' + message.substring(pos, message.length);
            cursor_pos = pos;
          }
          else { // on top
            cursor_pos = 0;
            message = '\n\n' + sig + '\n\n' + message.replace(/^[\r\n]+/, '');
          }
        }
        else {
          message = message.replace(/[\r\n]+$/, '');
          cursor_pos = !this.env.top_posting && message.length ? message.length+1 : 0;
          message += '\n\n' + sig;
        }
      }
      else
        cursor_pos = this.env.top_posting ? 0 : message.length;

      input_message.val(message);

      // move cursor before the signature
      this.set_caret_pos(input_message.get(0), cursor_pos);
    }
    else if (show_sig && this.env.signatures) {  // html
      var editor = tinyMCE.get(this.env.composebody),
        sigElem = editor.dom.get('_rc_sig');

      // Append the signature as a div within the body
      if (!sigElem) {
        var body = editor.getBody(),
          doc = editor.getDoc();

        sigElem = doc.createElement('div');
        sigElem.setAttribute('id', '_rc_sig');

        if (this.env.top_posting) {
          // if no existing sig and top posting then insert at caret pos
          editor.getWin().focus(); // correct focus in IE & Chrome

          var node = editor.selection.getNode();
          if (node.nodeName == 'BODY') {
            // no real focus, insert at start
            body.insertBefore(sigElem, body.firstChild);
            body.insertBefore(doc.createElement('br'), body.firstChild);
          }
          else {
            body.insertBefore(sigElem, node.nextSibling);
            body.insertBefore(doc.createElement('br'), node.nextSibling);
          }
        }
        else {
          if (bw.ie)  // add empty line before signature on IE
            body.appendChild(doc.createElement('br'));

          body.appendChild(sigElem);
        }
      }

      if (this.env.signatures[id])
        sigElem.innerHTML = this.env.signatures[id].html;
    }

    this.env.identity = id;
    return true;
  };

  // upload attachment file
  this.upload_file = function(form)
  {
    if (!form)
      return false;

    // count files and size on capable browser
    var size = 0, numfiles = 0;

    $('input[type=file]', form).each(function(i, field) {
      var files = field.files ? field.files.length : (field.value ? 1 : 0);

      // check file size
      if (field.files) {
        for (var i=0; i < files; i++)
          size += field.files[i].size;
      }

      numfiles += files;
    });

    // create hidden iframe and post upload form
    if (numfiles) {
      if (this.env.max_filesize && this.env.filesizeerror && size > this.env.max_filesize) {
        this.display_message(this.env.filesizeerror, 'error');
        return;
      }

      var frame_name = this.async_upload_form(form, 'upload', function(e) {
        var d, content = '';
        try {
          if (this.contentDocument) {
            d = this.contentDocument;
          } else if (this.contentWindow) {
            d = this.contentWindow.document;
          }
          content = d.childNodes[0].innerHTML;
        } catch (err) {}

        if (!content.match(/add2attachment/) && (!bw.opera || (rcmail.env.uploadframe && rcmail.env.uploadframe == e.data.ts))) {
          if (!content.match(/display_message/))
            rcmail.display_message(rcmail.get_label('fileuploaderror'), 'error');
          rcmail.remove_from_attachment_list(e.data.ts);
        }
        // Opera hack: handle double onload
        if (bw.opera)
          rcmail.env.uploadframe = e.data.ts;
      });

      // display upload indicator and cancel button
      var content = '<span>' + this.get_label('uploading' + (numfiles > 1 ? 'many' : '')) + '</span>',
        ts = frame_name.replace(/^rcmupload/, '');

      this.add2attachment_list(ts, { name:'', html:content, classname:'uploading', frame:frame_name, complete:false });

      // upload progress support
      if (this.env.upload_progress_time) {
        this.upload_progress_start('upload', ts);
      }
    }

    // set reference to the form object
    this.gui_objects.attachmentform = form;
    return true;
  };

  // add file name to attachment list
  // called from upload page
  this.add2attachment_list = function(name, att, upload_id)
  {
    if (!this.gui_objects.attachmentlist)
      return false;

    if (!att.complete && ref.env.loadingicon)
      att.html = '<img src="'+ref.env.loadingicon+'" alt="" class="uploading" />' + att.html;

    if (!att.complete && att.frame)
      att.html = '<a title="'+this.get_label('cancel')+'" onclick="return rcmail.cancel_attachment_upload(\''+name+'\', \''+att.frame+'\');" href="#cancelupload" class="cancelupload">'
        + (this.env.cancelicon ? '<img src="'+this.env.cancelicon+'" alt="" />' : this.get_label('cancel')) + '</a>' + att.html;

    var indicator, li = $('<li>').attr('id', name).addClass(att.classname).html(att.html);

    // replace indicator's li
    if (upload_id && (indicator = document.getElementById(upload_id))) {
      li.replaceAll(indicator);
    }
    else { // add new li
      li.appendTo(this.gui_objects.attachmentlist);
    }

    if (upload_id && this.env.attachments[upload_id])
      delete this.env.attachments[upload_id];

    this.env.attachments[name] = att;

    return true;
  };

  this.remove_from_attachment_list = function(name)
  {
    delete this.env.attachments[name];
    $('#'+name).remove();
  };

  this.remove_attachment = function(name)
  {
    if (name && this.env.attachments[name])
      this.http_post('remove-attachment', { _id:this.env.compose_id, _file:name });

    return true;
  };

  this.cancel_attachment_upload = function(name, frame_name)
  {
    if (!name || !frame_name)
      return false;

    this.remove_from_attachment_list(name);
    $("iframe[name='"+frame_name+"']").remove();
    return false;
  };

  this.upload_progress_start = function(action, name)
  {
    setTimeout(function() { rcmail.http_request(action, {_progress: name}); },
      this.env.upload_progress_time * 1000);
  };

  this.upload_progress_update = function(param)
  {
    var elem = $('#'+param.name + '> span');

    if (!elem.length || !param.text)
      return;

    elem.text(param.text);

    if (!param.done)
      this.upload_progress_start(param.action, param.name);
  };

  // send remote request to add a new contact
  this.add_contact = function(value)
  {
    if (value)
      this.http_post('addcontact', {_address: value});

    return true;
  };

  // send remote request to search mail or contacts
  this.qsearch = function(value)
  {
    if (value != '') {
      var r, lock = this.set_busy(true, 'searching'),
        url = this.search_params(value);

      if (this.message_list)
        this.clear_message_list();
      else if (this.contact_list)
        this.list_contacts_clear();

      if (this.env.source)
        url._source = this.env.source;
      if (this.env.group)
        url._gid = this.env.group;

      // reset vars
      this.env.current_page = 1;

      var action = this.env.action == 'compose' && this.contact_list ? 'search-contacts' : 'search';
      r = this.http_request(action, url, lock);

      this.env.qsearch = {lock: lock, request: r};
    }
  };

  // build URL params for search
  this.search_params = function(search, filter)
  {
    var n, url = {}, mods_arr = [],
      mods = this.env.search_mods,
      mbox = this.env.mailbox;

    if (!filter && this.gui_objects.search_filter)
      filter = this.gui_objects.search_filter.value;

    if (!search && this.gui_objects.qsearchbox)
      search = this.gui_objects.qsearchbox.value;

    if (filter)
      url._filter = filter;

    if (search) {
      url._q = search;

      if (mods && this.message_list)
        mods = mods[mbox] ? mods[mbox] : mods['*'];

      if (mods) {
        for (n in mods)
          mods_arr.push(n);
        url._headers = mods_arr.join(',');
      }
    }

    if (mbox)
      url._mbox = mbox;

    return url;
  };

  // reset quick-search form
  this.reset_qsearch = function()
  {
    if (this.gui_objects.qsearchbox)
      this.gui_objects.qsearchbox.value = '';

    if (this.env.qsearch)
      this.abort_request(this.env.qsearch);

    this.env.qsearch = null;
    this.env.search_request = null;
    this.env.search_id = null;
  };

  this.sent_successfully = function(type, msg, target)
  {
    this.display_message(msg, type);

    if (this.env.extwin) {
      var rc = this.opener();
      this.lock_form(this.gui_objects.messageform);
      if (rc) {
        rc.display_message(msg, type);
        // refresh the folder where sent message was saved
        if (target && rc.env.task == 'mail' && rc.env.action == '' && rc.env.mailbox == target)
          rc.command('checkmail');
      }
      setTimeout(function(){ window.close() }, 1000);
    }
    else {
      // before redirect we need to wait some time for Chrome (#1486177)
      setTimeout(function(){ ref.list_mailbox(); }, 500);
    }
  };


  /*********************************************************/
  /*********     keyboard live-search methods      *********/
  /*********************************************************/

  // handler for keyboard events on address-fields
  this.ksearch_keydown = function(e, obj, props)
  {
    if (this.ksearch_timer)
      clearTimeout(this.ksearch_timer);

    var highlight,
      key = rcube_event.get_keycode(e),
      mod = rcube_event.get_modifier(e);

    switch (key) {
      case 38:  // arrow up
      case 40:  // arrow down
        if (!this.ksearch_visible())
          break;

        var dir = key==38 ? 1 : 0;

        highlight = document.getElementById('rcmksearchSelected');
        if (!highlight)
          highlight = this.ksearch_pane.__ul.firstChild;

        if (highlight)
          this.ksearch_select(dir ? highlight.previousSibling : highlight.nextSibling);

        return rcube_event.cancel(e);

      case 9:   // tab
        if (mod == SHIFT_KEY || !this.ksearch_visible()) {
          this.ksearch_hide();
          return;
        }

      case 13:  // enter
        if (!this.ksearch_visible())
          return false;

        // insert selected address and hide ksearch pane
        this.insert_recipient(this.ksearch_selected);
        this.ksearch_hide();

        return rcube_event.cancel(e);

      case 27:  // escape
        this.ksearch_hide();
        return;

      case 37:  // left
      case 39:  // right
        if (mod != SHIFT_KEY)
          return;
    }

    // start timer
    this.ksearch_timer = setTimeout(function(){ ref.ksearch_get_results(props); }, 200);
    this.ksearch_input = obj;

    return true;
  };

  this.ksearch_visible = function()
  {
    return (this.ksearch_selected !== null && this.ksearch_selected !== undefined && this.ksearch_value);
  };

  this.ksearch_select = function(node)
  {
    var current = $('#rcmksearchSelected');
    if (current[0] && node) {
      current.removeAttr('id').removeClass('selected');
    }

    if (node) {
      $(node).attr('id', 'rcmksearchSelected').addClass('selected');
      this.ksearch_selected = node._rcm_id;
    }
  };

  this.insert_recipient = function(id)
  {
    if (id === null || !this.env.contacts[id] || !this.ksearch_input)
      return;

    // get cursor pos
    var inp_value = this.ksearch_input.value,
      cpos = this.get_caret_pos(this.ksearch_input),
      p = inp_value.lastIndexOf(this.ksearch_value, cpos),
      trigger = false,
      insert = '',
      // replace search string with full address
      pre = inp_value.substring(0, p),
      end = inp_value.substring(p+this.ksearch_value.length, inp_value.length);

    this.ksearch_destroy();

    // insert all members of a group
    if (typeof this.env.contacts[id] === 'object' && this.env.contacts[id].id) {
      insert += this.env.contacts[id].name + this.env.recipients_delimiter;
      this.group2expand[this.env.contacts[id].id] = $.extend({ input: this.ksearch_input }, this.env.contacts[id]);
      this.http_request('mail/group-expand', {_source: this.env.contacts[id].source, _gid: this.env.contacts[id].id}, false);
    }
    else if (typeof this.env.contacts[id] === 'string') {
      insert = this.env.contacts[id] + this.env.recipients_delimiter;
      trigger = true;
    }

    this.ksearch_input.value = pre + insert + end;

    // set caret to insert pos
    cpos = p+insert.length;
    if (this.ksearch_input.setSelectionRange)
      this.ksearch_input.setSelectionRange(cpos, cpos);

    if (trigger)
      this.triggerEvent('autocomplete_insert', { field:this.ksearch_input, insert:insert });
  };

  this.replace_group_recipients = function(id, recipients)
  {
    if (this.group2expand[id]) {
      this.group2expand[id].input.value = this.group2expand[id].input.value.replace(this.group2expand[id].name, recipients);
      this.triggerEvent('autocomplete_insert', { field:this.group2expand[id].input, insert:recipients });
      this.group2expand[id] = null;
    }
  };

  // address search processor
  this.ksearch_get_results = function(props)
  {
    var inp_value = this.ksearch_input ? this.ksearch_input.value : null;

    if (inp_value === null)
      return;

    if (this.ksearch_pane && this.ksearch_pane.is(":visible"))
      this.ksearch_pane.hide();

    // get string from current cursor pos to last comma
    var cpos = this.get_caret_pos(this.ksearch_input),
      p = inp_value.lastIndexOf(this.env.recipients_separator, cpos-1),
      q = inp_value.substring(p+1, cpos),
      min = this.env.autocomplete_min_length,
      ac = this.ksearch_data;

    // trim query string
    q = $.trim(q);

    // Don't (re-)search if the last results are still active
    if (q == this.ksearch_value)
      return;

    this.ksearch_destroy();

    if (q.length && q.length < min) {
      if (!this.ksearch_info) {
        this.ksearch_info = this.display_message(
          this.get_label('autocompletechars').replace('$min', min));
      }
      return;
    }

    var old_value = this.ksearch_value;
    this.ksearch_value = q;

    // ...string is empty
    if (!q.length)
      return;

    // ...new search value contains old one and previous search was not finished or its result was empty
    if (old_value && old_value.length && q.indexOf(old_value) == 0 && (!ac || ac.num <= 0) && this.env.contacts && !this.env.contacts.length)
      return;

    var i, lock, source, xhr, reqid = new Date().getTime(),
      post_data = {_search: q, _id: reqid},
      threads = props && props.threads ? props.threads : 1,
      sources = props && props.sources ? props.sources : [],
      action = props && props.action ? props.action : 'mail/autocomplete';

    this.ksearch_data = {id: reqid, sources: sources.slice(), action: action,
      locks: [], requests: [], num: sources.length};

    for (i=0; i<threads; i++) {
      source = this.ksearch_data.sources.shift();
      if (threads > 1 && source === undefined)
        break;

      post_data._source = source ? source : '';
      lock = this.display_message(this.get_label('searching'), 'loading');
      xhr = this.http_post(action, post_data, lock);

      this.ksearch_data.locks.push(lock);
      this.ksearch_data.requests.push(xhr);
    }
  };

  this.ksearch_query_results = function(results, search, reqid)
  {
    // search stopped in meantime?
    if (!this.ksearch_value)
      return;

    // ignore this outdated search response
    if (this.ksearch_input && search != this.ksearch_value)
      return;

    // display search results
    var i, len, ul, li, text, init,
      value = this.ksearch_value,
      data = this.ksearch_data,
      maxlen = this.env.autocomplete_max ? this.env.autocomplete_max : 15;

    // create results pane if not present
    if (!this.ksearch_pane) {
      ul = $('<ul>');
      this.ksearch_pane = $('<div>').attr('id', 'rcmKSearchpane')
        .css({ position:'absolute', 'z-index':30000 }).append(ul).appendTo(document.body);
      this.ksearch_pane.__ul = ul[0];
    }

    ul = this.ksearch_pane.__ul;

    // remove all search results or add to existing list if parallel search
    if (reqid && this.ksearch_pane.data('reqid') == reqid) {
      maxlen -= ul.childNodes.length;
    }
    else {
      this.ksearch_pane.data('reqid', reqid);
      init = 1;
      // reset content
      ul.innerHTML = '';
      this.env.contacts = [];
      // move the results pane right under the input box
      var pos = $(this.ksearch_input).offset();
      this.ksearch_pane.css({ left:pos.left+'px', top:(pos.top + this.ksearch_input.offsetHeight)+'px', display: 'none'});
    }

    // add each result line to list
    if (results && (len = results.length)) {
      for (i=0; i < len && maxlen > 0; i++) {
        text = typeof results[i] === 'object' ? results[i].name : results[i];
        li = document.createElement('LI');
        li.innerHTML = text.replace(new RegExp('('+RegExp.escape(value)+')', 'ig'), '##$1%%').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/##([^%]+)%%/g, '<b>$1</b>');
        li.onmouseover = function(){ ref.ksearch_select(this); };
        li.onmouseup = function(){ ref.ksearch_click(this) };
        li._rcm_id = this.env.contacts.length + i;
        ul.appendChild(li);
        maxlen -= 1;
      }
    }

    if (ul.childNodes.length) {
      this.ksearch_pane.show();
      // select the first
      if (!this.env.contacts.length) {
        $('li:first', ul).attr('id', 'rcmksearchSelected').addClass('selected');
        this.ksearch_selected = 0;
      }
    }

    if (len)
      this.env.contacts = this.env.contacts.concat(results);

    // run next parallel search
    if (data.id == reqid) {
      data.num--;
      if (maxlen > 0 && data.sources.length) {
        var lock, xhr, source = data.sources.shift(), post_data;
        if (source) {
          post_data = {_search: value, _id: reqid, _source: source};
          lock = this.display_message(this.get_label('searching'), 'loading');
          xhr = this.http_post(data.action, post_data, lock);

          this.ksearch_data.locks.push(lock);
          this.ksearch_data.requests.push(xhr);
        }
      }
      else if (!maxlen) {
        if (!this.ksearch_msg)
          this.ksearch_msg = this.display_message(this.get_label('autocompletemore'));
        // abort pending searches
        this.ksearch_abort();
      }
    }
  };

  this.ksearch_click = function(node)
  {
    if (this.ksearch_input)
      this.ksearch_input.focus();

    this.insert_recipient(node._rcm_id);
    this.ksearch_hide();
  };

  this.ksearch_blur = function()
  {
    if (this.ksearch_timer)
      clearTimeout(this.ksearch_timer);

    this.ksearch_input = null;
    this.ksearch_hide();
  };

  this.ksearch_hide = function()
  {
    this.ksearch_selected = null;
    this.ksearch_value = '';

    if (this.ksearch_pane)
      this.ksearch_pane.hide();

    this.ksearch_destroy();
  };

  // Clears autocomplete data/requests
  this.ksearch_destroy = function()
  {
    this.ksearch_abort();

    if (this.ksearch_info)
      this.hide_message(this.ksearch_info);

    if (this.ksearch_msg)
      this.hide_message(this.ksearch_msg);

    this.ksearch_data = null;
    this.ksearch_info = null;
    this.ksearch_msg = null;
  }

  // Aborts pending autocomplete requests
  this.ksearch_abort = function()
  {
    var i, len, ac = this.ksearch_data;

    if (!ac)
      return;

    for (i=0, len=ac.locks.length; i<len; i++)
      this.abort_request({request: ac.requests[i], lock: ac.locks[i]});
  };


  /*********************************************************/
  /*********         address book methods          *********/
  /*********************************************************/

  this.contactlist_keypress = function(list)
  {
    if (list.key_pressed == list.DELETE_KEY)
      this.command('delete');
  };

  this.contactlist_select = function(list)
  {
    if (this.preview_timer)
      clearTimeout(this.preview_timer);

    var n, id, sid, ref = this, writable = false,
      source = this.env.source ? this.env.address_sources[this.env.source] : null;

    // we don't have dblclick handler here, so use 200 instead of this.dblclick_time
    if (id = list.get_single_selection())
      this.preview_timer = setTimeout(function(){ ref.load_contact(id, 'show'); }, 200);
    else if (this.env.contentframe)
      this.show_contentframe(false);

    if (list.selection.length) {
      // no source = search result, we'll need to detect if any of
      // selected contacts are in writable addressbook to enable edit/delete
      // we'll also need to know sources used in selection for copy
      // and group-addmember operations (drag&drop)
      this.env.selection_sources = [];
      if (!source) {
        for (n in list.selection) {
          sid = String(list.selection[n]).replace(/^[^-]+-/, '');
          if (sid && this.env.address_sources[sid]) {
            writable = writable || !this.env.address_sources[sid].readonly;
            this.env.selection_sources.push(sid);
          }
        }
        this.env.selection_sources = $.unique(this.env.selection_sources);
      }
      else {
        this.env.selection_sources.push(this.env.source);
        writable = !source.readonly;
      }
    }

    // if a group is currently selected, and there is at least one contact selected
    // thend we can enable the group-remove-selected command
    this.enable_command('group-remove-selected', this.env.group && list.selection.length > 0);
    this.enable_command('compose', this.env.group || list.selection.length > 0);
    this.enable_command('edit', id && writable);
    this.enable_command('delete', list.selection.length && writable);

    return false;
  };

  this.list_contacts = function(src, group, page)
  {
    var win, folder, url = {},
      target = window;

    if (!src)
      src = this.env.source;

    if (page && this.current_page == page && src == this.env.source && group == this.env.group)
      return false;

    if (src != this.env.source) {
      page = this.env.current_page = 1;
      this.reset_qsearch();
    }
    else if (group != this.env.group)
      page = this.env.current_page = 1;

    if (this.env.search_id)
      folder = 'S'+this.env.search_id;
    else if (!this.env.search_request)
      folder = group ? 'G'+src+group : src;

    this.select_folder(folder);

    this.env.source = src;
    this.env.group = group;

    // load contacts remotely
    if (this.gui_objects.contactslist) {
      this.list_contacts_remote(src, group, page);
      return;
    }

    if (win = this.get_frame_window(this.env.contentframe)) {
      target = win;
      url._framed = 1;
    }

    if (group)
      url._gid = group;
    if (page)
      url._page = page;
    if (src)
      url._source = src;

    // also send search request to get the correct listing
    if (this.env.search_request)
      url._search = this.env.search_request;

    this.set_busy(true, 'loading');
    this.location_href(url, target);
  };

  // send remote request to load contacts list
  this.list_contacts_remote = function(src, group, page)
  {
    // clear message list first
    this.list_contacts_clear();

    // send request to server
    var url = {}, lock = this.set_busy(true, 'loading');

    if (src)
      url._source = src;
    if (page)
      url._page = page;
    if (group)
      url._gid = group;

    this.env.source = src;
    this.env.group = group;

    // also send search request to get the right records
    if (this.env.search_request)
      url._search = this.env.search_request;

    this.http_request(this.env.task == 'mail' ? 'list-contacts' : 'list', url, lock);
  };

  this.list_contacts_clear = function()
  {
    this.contact_list.clear(true);
    this.show_contentframe(false);
    this.enable_command('delete', false);
    this.enable_command('compose', this.env.group ? true : false);
  };

  // load contact record
  this.load_contact = function(cid, action, framed)
  {
    var win, url = {}, target = window;

    if (win = this.get_frame_window(this.env.contentframe)) {
      url._framed = 1;
      target = win;
      this.show_contentframe(true);

      // load dummy content
      if (!cid) {
        // unselect selected row(s)
        this.contact_list.clear_selection();
        this.enable_command('delete', 'compose', false);
      }
    }
    else if (framed)
      return false;

    if (action && (cid || action=='add') && !this.drag_active) {
      if (this.env.group)
        url._gid = this.env.group;

      url._action = action;
      url._source = this.env.source;
      url._cid = cid;

      this.location_href(url, target, true);
    }

    return true;
  };

  // add/delete member to/from the group
  this.group_member_change = function(what, cid, source, gid)
  {
    what = what == 'add' ? 'add' : 'del';
    var label = this.get_label(what == 'add' ? 'addingmember' : 'removingmember'),
      lock = this.display_message(label, 'loading'),
      post_data = {_cid: cid, _source: source, _gid: gid};

    this.http_post('group-'+what+'members', post_data, lock);
  };

  // copy a contact to the specified target (group or directory)
  this.copy_contact = function(cid, to)
  {
    var n, dest = to.type == 'group' ? to.source : to.id,
      source = this.env.source,
      group = this.env.group ? this.env.group : '';

    if (!cid)
      cid = this.contact_list.get_selection().join(',');

    if (!cid || !this.env.address_sources[dest] || this.env.address_sources[dest].readonly)
      return;

    // search result may contain contacts from many sources, but if there is only one...
    if (source == '' && this.env.selection_sources.length == 1)
      source = this.env.selection_sources[0];

    // tagret is a group
    if (to.type == 'group') {
      if (dest == source)
        this.group_member_change('add', cid, dest, to.id);
      else {
        var lock = this.display_message(this.get_label('copyingcontact'), 'loading'),
          post_data = {_cid: cid, _source: this.env.source, _to: dest, _togid: to.id, _gid: group};

        this.http_post('copy', post_data, lock);
      }
    }
    // target is an addressbook
    else if (to.id != source) {
      var lock = this.display_message(this.get_label('copyingcontact'), 'loading'),
        post_data = {_cid: cid, _source: this.env.source, _to: to.id, _gid: group};

      this.http_post('copy', post_data, lock);
    }
  };

  this.delete_contacts = function()
  {
    var selection = this.contact_list.get_selection(),
      undelete = this.env.source && this.env.address_sources[this.env.source].undelete;

    // exit if no mailbox specified or if selection is empty
    if (!(selection.length || this.env.cid) || (!undelete && !confirm(this.get_label('deletecontactconfirm'))))
      return;

    var id, n, a_cids = [],
      post_data = {_source: this.env.source, _from: (this.env.action ? this.env.action : '')},
      lock = this.display_message(this.get_label('contactdeleting'), 'loading');

    if (this.env.cid)
      a_cids.push(this.env.cid);
    else {
      for (n=0; n<selection.length; n++) {
        id = selection[n];
        a_cids.push(id);
        this.contact_list.remove_row(id, (n == selection.length-1));
      }

      // hide content frame if we delete the currently displayed contact
      if (selection.length == 1)
        this.show_contentframe(false);
    }

    post_data._cid = a_cids.join(',');

    if (this.env.group)
      post_data._gid = this.env.group;

    // also send search request to get the right records from the next page
    if (this.env.search_request)
      post_data._search = this.env.search_request;

    // send request to server
    this.http_post('delete', post_data, lock)

    return true;
  };

  // update a contact record in the list
  this.update_contact_row = function(cid, cols_arr, newcid, source)
  {
    var c, row, list = this.contact_list;

    cid = this.html_identifier(cid);

    // when in searching mode, concat cid with the source name
    if (!list.rows[cid]) {
      cid = cid+'-'+source;
      if (newcid)
        newcid = newcid+'-'+source;
    }

    if (list.rows[cid] && (row = list.rows[cid].obj)) {
      for (c=0; c<cols_arr.length; c++)
        if (row.cells[c])
          $(row.cells[c]).html(cols_arr[c]);

      // cid change
      if (newcid) {
        newcid = this.html_identifier(newcid);
        row.id = 'rcmrow' + newcid;
        list.remove_row(cid);
        list.init_row(row);
        list.selection[0] = newcid;
        row.style.display = '';
      }
    }
  };

  // add row to contacts list
  this.add_contact_row = function(cid, cols, classes)
  {
    if (!this.gui_objects.contactslist)
      return false;

    var c, col, list = this.contact_list,
      row = document.createElement('tr');

    row.id = 'rcmrow'+this.html_identifier(cid);
    row.className = 'contact ' + (classes || '');

    if (list.in_selection(cid))
      row.className += ' selected';

    // add each submitted col
    for (c in cols) {
      col = document.createElement('td');
      col.className = String(c).toLowerCase();
      if (cols[c])
        col.innerHTML = cols[c];
      row.appendChild(col);
    }

    list.insert_row(row);

    this.enable_command('export', list.rowcount > 0);
  };

  this.init_contact_form = function()
  {
    var ref = this, col;

    if (this.env.coltypes) {
      this.set_photo_actions($('#ff_photo').val());
      for (col in this.env.coltypes)
        this.init_edit_field(col, null);
    }

    $('.contactfieldgroup .row a.deletebutton').click(function() {
      ref.delete_edit_field(this);
      return false;
    });

    $('select.addfieldmenu').change(function(e) {
      ref.insert_edit_field($(this).val(), $(this).attr('rel'), this);
      this.selectedIndex = 0;
    });

    // enable date pickers on date fields
    if ($.datepicker && this.env.date_format) {
      $.datepicker.setDefaults({
        dateFormat: this.env.date_format,
        changeMonth: true,
        changeYear: true,
        yearRange: '-100:+10',
        showOtherMonths: true,
        selectOtherMonths: true,
        onSelect: function(dateText) { $(this).focus().val(dateText) }
      });
      $('input.datepicker').datepicker();
    }

    $("input[type='text']:visible").first().focus();

    // Submit search form on Enter
    if (this.env.action == 'search')
      $(this.gui_objects.editform).append($('<input type="submit">').hide())
        .submit(function() { $('input.mainaction').click(); return false; });
  };

  this.group_create = function()
  {
    this.add_input_row('contactgroup');
  };

  this.group_rename = function()
  {
    if (!this.env.group || !this.gui_objects.folderlist)
      return;

    if (!this.name_input) {
      this.enable_command('list', 'listgroup', false);
      this.name_input = $('<input>').attr('type', 'text').val(this.env.contactgroups['G'+this.env.source+this.env.group].name);
      this.name_input.bind('keydown', function(e){ return rcmail.add_input_keydown(e); });
      this.env.group_renaming = true;

      var link, li = this.get_folder_li(this.env.source+this.env.group, 'rcmliG');
      if (li && (link = li.firstChild)) {
        $(link).hide().before(this.name_input);
      }
    }

    this.name_input.select().focus();
  };

  this.group_delete = function()
  {
    if (this.env.group && confirm(this.get_label('deletegroupconfirm'))) {
      var lock = this.set_busy(true, 'groupdeleting');
      this.http_post('group-delete', {_source: this.env.source, _gid: this.env.group}, lock);
    }
  };

  // callback from server upon group-delete command
  this.remove_group_item = function(prop)
  {
    var li, key = 'G'+prop.source+prop.id;
    if ((li = this.get_folder_li(key))) {
      this.triggerEvent('group_delete', { source:prop.source, id:prop.id, li:li });

      li.parentNode.removeChild(li);
      delete this.env.contactfolders[key];
      delete this.env.contactgroups[key];
    }

    this.list_contacts(prop.source, 0);
  };

  // @TODO: maybe it would be better to use popup instead of inserting input to the list?
  this.add_input_row = function(type)
  {
    if (!this.gui_objects.folderlist)
      return;

    if (!this.name_input) {
      this.name_input = $('<input>').attr('type', 'text').data('tt', type);
      this.name_input.bind('keydown', function(e){ return rcmail.add_input_keydown(e); });
      this.name_input_li = $('<li>').addClass(type).append(this.name_input);

      var li = type == 'contactsearch' ? $('li:last', this.gui_objects.folderlist) : this.get_folder_li(this.env.source);
      this.name_input_li.insertAfter(li);
    }

    this.name_input.select().focus();
  };

  //remove selected contacts from current active group
  this.group_remove_selected = function()
  {
    ref.http_post('group-delmembers', {_cid: this.contact_list.selection,
      _source: this.env.source, _gid: this.env.group});
  };

  //callback after deleting contact(s) from current group
  this.remove_group_contacts = function(props)
  {
    if('undefined' != typeof this.env.group && (this.env.group === props.gid)){
      var n, selection = this.contact_list.get_selection();
      for (n=0; n<selection.length; n++) {
        id = selection[n];
        this.contact_list.remove_row(id, (n == selection.length-1));
      }
    }
  }

  // handler for keyboard events on the input field
  this.add_input_keydown = function(e)
  {
    var key = rcube_event.get_keycode(e),
      input = $(e.target), itype = input.data('tt');

    // enter
    if (key == 13) {
      var newname = input.val();

      if (newname) {
        var lock = this.set_busy(true, 'loading');

        if (itype == 'contactsearch')
          this.http_post('search-create', {_search: this.env.search_request, _name: newname}, lock);
        else if (this.env.group_renaming)
          this.http_post('group-rename', {_source: this.env.source, _gid: this.env.group, _name: newname}, lock);
        else
          this.http_post('group-create', {_source: this.env.source, _name: newname}, lock);
      }
      return false;
    }
    // escape
    else if (key == 27)
      this.reset_add_input();

    return true;
  };

  this.reset_add_input = function()
  {
    if (this.name_input) {
      if (this.env.group_renaming) {
        var li = this.name_input.parent();
        li.children().last().show();
        this.env.group_renaming = false;
      }

      this.name_input.remove();

      if (this.name_input_li)
        this.name_input_li.remove();

      this.name_input = this.name_input_li = null;
    }

    this.enable_command('list', 'listgroup', true);
  };

  // callback for creating a new contact group
  this.insert_contact_group = function(prop)
  {
    this.reset_add_input();

    prop.type = 'group';
    var key = 'G'+prop.source+prop.id,
      link = $('<a>').attr('href', '#')
        .attr('rel', prop.source+':'+prop.id)
        .click(function() { return rcmail.command('listgroup', prop, this); })
        .html(prop.name),
      li = $('<li>').attr({id: 'rcmli'+this.html_identifier(key), 'class': 'contactgroup'})
        .append(link);

    this.env.contactfolders[key] = this.env.contactgroups[key] = prop;
    this.add_contact_group_row(prop, li);

    this.triggerEvent('group_insert', { id:prop.id, source:prop.source, name:prop.name, li:li[0] });
  };

  // callback for renaming a contact group
  this.update_contact_group = function(prop)
  {
    this.reset_add_input();

    var key = 'G'+prop.source+prop.id,
      li = this.get_folder_li(key),
      link;

    // group ID has changed, replace link node and identifiers
    if (li && prop.newid) {
      var newkey = 'G'+prop.source+prop.newid,
        newprop = $.extend({}, prop);;

      li.id = 'rcmli' + this.html_identifier(newkey);
      this.env.contactfolders[newkey] = this.env.contactfolders[key];
      this.env.contactfolders[newkey].id = prop.newid;
      this.env.group = prop.newid;

      delete this.env.contactfolders[key];
      delete this.env.contactgroups[key];

      newprop.id = prop.newid;
      newprop.type = 'group';

      link = $('<a>').attr('href', '#')
        .attr('rel', prop.source+':'+prop.newid)
        .click(function() { return rcmail.command('listgroup', newprop, this); })
        .html(prop.name);
      $(li).children().replaceWith(link);
    }
    // update displayed group name
    else if (li && (link = li.firstChild) && link.tagName.toLowerCase() == 'a')
      link.innerHTML = prop.name;

    this.env.contactfolders[key].name = this.env.contactgroups[key].name = prop.name;
    this.add_contact_group_row(prop, $(li), true);

    this.triggerEvent('group_update', { id:prop.id, source:prop.source, name:prop.name, li:li[0], newid:prop.newid });
  };

  // add contact group row to the list, with sorting
  this.add_contact_group_row = function(prop, li, reloc)
  {
    var row, name = prop.name.toUpperCase(),
      sibling = this.get_folder_li(prop.source),
      prefix = 'rcmliG' + this.html_identifier(prop.source);

    // When renaming groups, we need to remove it from DOM and insert it in the proper place
    if (reloc) {
      row = li.clone(true);
      li.remove();
    }
    else
      row = li;

    $('li[id^="'+prefix+'"]', this.gui_objects.folderlist).each(function(i, elem) {
      if (name >= $(this).text().toUpperCase())
        sibling = elem;
      else
        return false;
    });

    row.insertAfter(sibling);
  };

  this.update_group_commands = function()
  {
    var source = this.env.source != '' ? this.env.address_sources[this.env.source] : null;
    this.enable_command('group-create', (source && source.groups && !source.readonly));
    this.enable_command('group-rename', 'group-delete', (source && source.groups && this.env.group && !source.readonly));
  };

  this.init_edit_field = function(col, elem)
  {
    var label = this.env.coltypes[col].label;

    if (!elem)
      elem = $('.ff_' + col);

    if (label)
      elem.placeholder(label);
  };

  this.insert_edit_field = function(col, section, menu)
  {
    // just make pre-defined input field visible
    var elem = $('#ff_'+col);
    if (elem.length) {
      elem.show().focus();
      $(menu).children('option[value="'+col+'"]').prop('disabled', true);
    }
    else {
      var lastelem = $('.ff_'+col),
        appendcontainer = $('#contactsection'+section+' .contactcontroller'+col);

      if (!appendcontainer.length) {
        var sect = $('#contactsection'+section),
          lastgroup = $('.contactfieldgroup', sect).last();
        appendcontainer = $('<fieldset>').addClass('contactfieldgroup contactcontroller'+col);
        if (lastgroup.length)
          appendcontainer.insertAfter(lastgroup);
        else
          sect.prepend(appendcontainer);
      }

      if (appendcontainer.length && appendcontainer.get(0).nodeName == 'FIELDSET') {
        var input, colprop = this.env.coltypes[col],
          row = $('<div>').addClass('row'),
          cell = $('<div>').addClass('contactfieldcontent data'),
          label = $('<div>').addClass('contactfieldlabel label');

        if (colprop.subtypes_select)
          label.html(colprop.subtypes_select);
        else
          label.html(colprop.label);

        var name_suffix = colprop.limit != 1 ? '[]' : '';
        if (colprop.type == 'text' || colprop.type == 'date') {
          input = $('<input>')
            .addClass('ff_'+col)
            .attr({type: 'text', name: '_'+col+name_suffix, size: colprop.size})
            .appendTo(cell);

          this.init_edit_field(col, input);

          if (colprop.type == 'date' && $.datepicker)
            input.datepicker();
        }
        else if (colprop.type == 'textarea') {
          input = $('<textarea>')
            .addClass('ff_'+col)
            .attr({ name: '_'+col+name_suffix, cols:colprop.size, rows:colprop.rows })
            .appendTo(cell);

          this.init_edit_field(col, input);
        }
        else if (colprop.type == 'composite') {
          var childcol, cp, first, templ, cols = [], suffices = [];
          // read template for composite field order
          if ((templ = this.env[col+'_template'])) {
            for (var j=0; j < templ.length; j++) {
              cols.push(templ[j][1]);
              suffices.push(templ[j][2]);
            }
          }
          else {  // list fields according to appearance in colprop
            for (childcol in colprop.childs)
              cols.push(childcol);
          }

          for (var i=0; i < cols.length; i++) {
            childcol = cols[i];
            cp = colprop.childs[childcol];
            input = $('<input>')
              .addClass('ff_'+childcol)
              .attr({ type: 'text', name: '_'+childcol+name_suffix, size: cp.size })
              .appendTo(cell);
            cell.append(suffices[i] || " ");
            this.init_edit_field(childcol, input);
            if (!first) first = input;
          }
          input = first;  // set focus to the first of this composite fields
        }
        else if (colprop.type == 'select') {
          input = $('<select>')
            .addClass('ff_'+col)
            .attr('name', '_'+col+name_suffix)
            .appendTo(cell);

          var options = input.attr('options');
          options[options.length] = new Option('---', '');
          if (colprop.options)
            $.each(colprop.options, function(i, val){ options[options.length] = new Option(val, i); });
        }

        if (input) {
          var delbutton = $('<a href="#del"></a>')
            .addClass('contactfieldbutton deletebutton')
            .attr({title: this.get_label('delete'), rel: col})
            .html(this.env.delbutton)
            .click(function(){ ref.delete_edit_field(this); return false })
            .appendTo(cell);

          row.append(label).append(cell).appendTo(appendcontainer.show());
          input.first().focus();

          // disable option if limit reached
          if (!colprop.count) colprop.count = 0;
          if (++colprop.count == colprop.limit && colprop.limit)
            $(menu).children('option[value="'+col+'"]').prop('disabled', true);
        }
      }
    }
  };

  this.delete_edit_field = function(elem)
  {
    var col = $(elem).attr('rel'),
      colprop = this.env.coltypes[col],
      fieldset = $(elem).parents('fieldset.contactfieldgroup'),
      addmenu = fieldset.parent().find('select.addfieldmenu');

    // just clear input but don't hide the last field
    if (--colprop.count <= 0 && colprop.visible)
      $(elem).parent().children('input').val('').blur();
    else {
      $(elem).parents('div.row').remove();
      // hide entire fieldset if no more rows
      if (!fieldset.children('div.row').length)
        fieldset.hide();
    }

    // enable option in add-field selector or insert it if necessary
    if (addmenu.length) {
      var option = addmenu.children('option[value="'+col+'"]');
      if (option.length)
        option.prop('disabled', false);
      else
        option = $('<option>').attr('value', col).html(colprop.label).appendTo(addmenu);
      addmenu.show();
    }
  };

  this.upload_contact_photo = function(form)
  {
    if (form && form.elements._photo.value) {
      this.async_upload_form(form, 'upload-photo', function(e) {
        rcmail.set_busy(false, null, rcmail.file_upload_id);
      });

      // display upload indicator
      this.file_upload_id = this.set_busy(true, 'uploading');
    }
  };

  this.replace_contact_photo = function(id)
  {
    var img_src = id == '-del-' ? this.env.photo_placeholder :
      this.env.comm_path + '&_action=photo&_source=' + this.env.source + '&_cid=' + this.env.cid + '&_photo=' + id;

    this.set_photo_actions(id);
    $(this.gui_objects.contactphoto).children('img').attr('src', img_src);
  };

  this.photo_upload_end = function()
  {
    this.set_busy(false, null, this.file_upload_id);
    delete this.file_upload_id;
  };

  this.set_photo_actions = function(id)
  {
    var n, buttons = this.buttons['upload-photo'];
    for (n=0; buttons && n < buttons.length; n++)
      $('a#'+buttons[n].id).html(this.get_label(id == '-del-' ? 'addphoto' : 'replacephoto'));

    $('#ff_photo').val(id);
    this.enable_command('upload-photo', this.env.coltypes.photo ? true : false);
    this.enable_command('delete-photo', this.env.coltypes.photo && id != '-del-');
  };

  // load advanced search page
  this.advanced_search = function()
  {
    var win, url = {_form: 1, _action: 'search'}, target = window;

    if (win = this.get_frame_window(this.env.contentframe)) {
      url._framed = 1;
      target = win;
      this.contact_list.clear_selection();
    }

    this.location_href(url, target, true);

    return true;
  };

  // unselect directory/group
  this.unselect_directory = function()
  {
    this.select_folder('');
    this.enable_command('search-delete', false);
  };

  // callback for creating a new saved search record
  this.insert_saved_search = function(name, id)
  {
    this.reset_add_input();

    var key = 'S'+id,
      link = $('<a>').attr('href', '#')
        .attr('rel', id)
        .click(function() { return rcmail.command('listsearch', id, this); })
        .html(name),
      li = $('<li>').attr({id: 'rcmli' + this.html_identifier(key), 'class': 'contactsearch'})
        .append(link),
      prop = {name:name, id:id, li:li[0]};

    this.add_saved_search_row(prop, li);
    this.select_folder('S'+id);
    this.enable_command('search-delete', true);
    this.env.search_id = id;

    this.triggerEvent('abook_search_insert', prop);
  };

  // add saved search row to the list, with sorting
  this.add_saved_search_row = function(prop, li, reloc)
  {
    var row, sibling, name = prop.name.toUpperCase();

    // When renaming groups, we need to remove it from DOM and insert it in the proper place
    if (reloc) {
      row = li.clone(true);
      li.remove();
    }
    else
      row = li;

    $('li[class~="contactsearch"]', this.gui_objects.folderlist).each(function(i, elem) {
      if (!sibling)
        sibling = this.previousSibling;

      if (name >= $(this).text().toUpperCase())
        sibling = elem;
      else
        return false;
    });

    if (sibling)
      row.insertAfter(sibling);
    else
      row.appendTo(this.gui_objects.folderlist);
  };

  // creates an input for saved search name
  this.search_create = function()
  {
    this.add_input_row('contactsearch');
  };

  this.search_delete = function()
  {
    if (this.env.search_request) {
      var lock = this.set_busy(true, 'savedsearchdeleting');
      this.http_post('search-delete', {_sid: this.env.search_id}, lock);
    }
  };

  // callback from server upon search-delete command
  this.remove_search_item = function(id)
  {
    var li, key = 'S'+id;
    if ((li = this.get_folder_li(key))) {
      this.triggerEvent('search_delete', { id:id, li:li });

      li.parentNode.removeChild(li);
    }

    this.env.search_id = null;
    this.env.search_request = null;
    this.list_contacts_clear();
    this.reset_qsearch();
    this.enable_command('search-delete', 'search-create', false);
  };

  this.listsearch = function(id)
  {
    var folder, lock = this.set_busy(true, 'searching');

    if (this.contact_list) {
      this.list_contacts_clear();
    }

    this.reset_qsearch();
    this.select_folder('S'+id);

    // reset vars
    this.env.current_page = 1;
    this.http_request('search', {_sid: id}, lock);
  };


  /*********************************************************/
  /*********        user settings methods          *********/
  /*********************************************************/

  // preferences section select and load options frame
  this.section_select = function(list)
  {
    var win, id = list.get_single_selection(), target = window,
      url = {_action: 'edit-prefs', _section: id};

    if (id) {
      if (win = this.get_frame_window(this.env.contentframe)) {
        url._framed = 1;
        target = win;
      }
      this.location_href(url, target, true);
    }

    return true;
  };

  this.identity_select = function(list)
  {
    var id;
    if (id = list.get_single_selection()) {
      this.enable_command('delete', list.rowcount > 1 && this.env.identities_level < 2);
      this.load_identity(id, 'edit-identity');
    }
  };

  // load identity record
  this.load_identity = function(id, action)
  {
    if (action == 'edit-identity' && (!id || id == this.env.iid))
      return false;

    var win, target = window,
      url = {_action: action, _iid: id};

    if (win = this.get_frame_window(this.env.contentframe)) {
      url._framed = 1;
      target = win;
    }

    if (action && (id || action == 'add-identity')) {
      this.set_busy(true);
      this.location_href(url, target);
    }

    return true;
  };

  this.delete_identity = function(id)
  {
    // exit if no identity is specified or if selection is empty
    var selection = this.identity_list.get_selection();
    if (!(selection.length || this.env.iid))
      return;

    if (!id)
      id = this.env.iid ? this.env.iid : selection[0];

    // submit request with appended token
    if (confirm(this.get_label('deleteidentityconfirm')))
      this.goto_url('delete-identity', { _iid: id, _token: this.env.request_token }, true);

    return true;
  };

  this.update_identity_row = function(id, name, add)
  {
    var row, col, list = this.identity_list,
      rid = this.html_identifier(id);

    if (list.rows[rid] && (row = list.rows[rid].obj)) {
      $(row.cells[0]).html(name);
    }
    else if (add) {
      row = $('<tr>').attr('id', 'rcmrow'+rid).get(0);
      col = $('<td>').addClass('mail').html(name).appendTo(row);
      list.insert_row(row);
      list.select(rid);
    }
  };


  /*********************************************************/
  /*********        folder manager methods         *********/
  /*********************************************************/

  this.init_subscription_list = function()
  {
    var p = this;
    this.subscription_list = new rcube_list_widget(this.gui_objects.subscriptionlist,
      {multiselect:false, draggable:true, keyboard:false, toggleselect:true});
    this.subscription_list.addEventListener('select', function(o){ p.subscription_select(o); });
    this.subscription_list.addEventListener('dragstart', function(o){ p.drag_active = true; });
    this.subscription_list.addEventListener('dragend', function(o){ p.subscription_move_folder(o); });
    this.subscription_list.row_init = function (row) {
      row.obj.onmouseover = function() { p.focus_subscription(row.id); };
      row.obj.onmouseout = function() { p.unfocus_subscription(row.id); };
    };
    this.subscription_list.init();
    $('#mailboxroot')
      .mouseover(function(){ p.focus_subscription(this.id); })
      .mouseout(function(){ p.unfocus_subscription(this.id); })
  };

  this.focus_subscription = function(id)
  {
    var row, folder,
      delim = RegExp.escape(this.env.delimiter),
      reg = RegExp('['+delim+']?[^'+delim+']+$');

    if (this.drag_active && this.env.mailbox && (row = document.getElementById(id)))
      if (this.env.subscriptionrows[id] &&
          (folder = this.env.subscriptionrows[id][0]) !== null
      ) {
        if (this.check_droptarget(folder) &&
            !this.env.subscriptionrows[this.get_folder_row_id(this.env.mailbox)][2] &&
            (folder != this.env.mailbox.replace(reg, '')) &&
            (!folder.match(new RegExp('^'+RegExp.escape(this.env.mailbox+this.env.delimiter))))
        ) {
          this.env.dstfolder = folder;
          $(row).addClass('droptarget');
        }
      }
  };

  this.unfocus_subscription = function(id)
  {
    var row = $('#'+id);

    this.env.dstfolder = null;
    if (this.env.subscriptionrows[id] && row[0])
      row.removeClass('droptarget');
    else
      $(this.subscription_list.frame).removeClass('droptarget');
  };

  this.subscription_select = function(list)
  {
    var id, folder;

    if (list && (id = list.get_single_selection()) &&
        (folder = this.env.subscriptionrows['rcmrow'+id])
    ) {
      this.env.mailbox = folder[0];
      this.show_folder(folder[0]);
      this.enable_command('delete-folder', !folder[2]);
    }
    else {
      this.env.mailbox = null;
      this.show_contentframe(false);
      this.enable_command('delete-folder', 'purge', false);
    }
  };

  this.subscription_move_folder = function(list)
  {
    var delim = RegExp.escape(this.env.delimiter),
      reg = RegExp('['+delim+']?[^'+delim+']+$');

    if (this.env.mailbox && this.env.dstfolder !== null && (this.env.dstfolder != this.env.mailbox) &&
        (this.env.dstfolder != this.env.mailbox.replace(reg, ''))
    ) {
      reg = new RegExp('[^'+delim+']*['+delim+']', 'g');
      var basename = this.env.mailbox.replace(reg, ''),
        newname = this.env.dstfolder === '' ? basename : this.env.dstfolder+this.env.delimiter+basename;

      if (newname != this.env.mailbox) {
        this.http_post('rename-folder', {_folder_oldname: this.env.mailbox, _folder_newname: newname}, this.set_busy(true, 'foldermoving'));
        this.subscription_list.draglayer.hide();
      }
    }
    this.drag_active = false;
    this.unfocus_subscription(this.get_folder_row_id(this.env.dstfolder));
  };

  // tell server to create and subscribe a new mailbox
  this.create_folder = function()
  {
    this.show_folder('', this.env.mailbox);
  };

  // delete a specific mailbox with all its messages
  this.delete_folder = function(name)
  {
    var id = this.get_folder_row_id(name ? name : this.env.mailbox),
      folder = this.env.subscriptionrows[id][0];

    if (folder && confirm(this.get_label('deletefolderconfirm'))) {
      var lock = this.set_busy(true, 'folderdeleting');
      this.http_post('delete-folder', {_mbox: folder}, lock);
    }
  };

  // Add folder row to the table and initialize it
  this.add_folder_row = function (name, display_name, is_protected, subscribed, skip_init, class_name)
  {
    if (!this.gui_objects.subscriptionlist)
      return false;

    var row, n, i, tmp, tmp_name, folders, rowid, list = [], slist = [],
      tbody = this.gui_objects.subscriptionlist.tBodies[0],
      refrow = $('tr', tbody).get(1),
      id = 'rcmrow'+((new Date).getTime());

    if (!refrow) {
      // Refresh page if we don't have a table row to clone
      this.goto_url('folders');
      return false;
    }

    // clone a table row if there are existing rows
    row = $(refrow).clone(true);

    // set ID, reset css class
    row.attr('id', id);
    row.attr('class', class_name);

    // set folder name
    row.find('td:first').html(display_name);

    // update subscription checkbox
    $('input[name="_subscribed[]"]', row).val(name)
      .prop({checked: subscribed ? true : false, disabled: is_protected ? true : false});

    // add to folder/row-ID map
    this.env.subscriptionrows[id] = [name, display_name, 0];

    // sort folders, to find a place where to insert the row
    folders = [];
    $.each(this.env.subscriptionrows, function(k,v){ folders.push(v) });
    folders.sort(function(a,b){ return a[0] < b[0] ? -1 : (a[0] > b[0] ? 1 : 0) });

    for (n in folders) {
      // protected folder
      if (folders[n][2]) {
        tmp_name = folders[n][0] + this.env.delimiter;
        // prefix namespace cannot have subfolders (#1488349)
        if (tmp_name == this.env.prefix_ns)
          continue;
        slist.push(folders[n][0]);
        tmp = tmp_name;
      }
      // protected folder's child
      else if (tmp && folders[n][0].indexOf(tmp) == 0)
        slist.push(folders[n][0]);
      // other
      else {
        list.push(folders[n][0]);
        tmp = null;
      }
    }

    // check if subfolder of a protected folder
    for (n=0; n<slist.length; n++) {
      if (name.indexOf(slist[n]+this.env.delimiter) == 0)
        rowid = this.get_folder_row_id(slist[n]);
    }

    // find folder position after sorting
    for (n=0; !rowid && n<list.length; n++) {
      if (n && list[n] == name)
        rowid = this.get_folder_row_id(list[n-1]);
    }

    // add row to the table
    if (rowid)
      $('#'+rowid).after(row);
    else
      row.appendTo(tbody);

    // update list widget
    this.subscription_list.clear_selection();
    if (!skip_init)
      this.init_subscription_list();

    row = row.get(0);
    if (row.scrollIntoView)
      row.scrollIntoView();

    return row;
  };

  // replace an existing table row with a new folder line (with subfolders)
  this.replace_folder_row = function(oldfolder, newfolder, display_name, is_protected, class_name)
  {
    if (!this.gui_objects.subscriptionlist)
      return false;

    var i, n, len, name, dispname, oldrow, tmprow, row, level,
      tbody = this.gui_objects.subscriptionlist.tBodies[0],
      folders = this.env.subscriptionrows,
      id = this.get_folder_row_id(oldfolder),
      regex = new RegExp('^'+RegExp.escape(oldfolder)),
      subscribed = $('input[name="_subscribed[]"]', $('#'+id)).prop('checked'),
      // find subfolders of renamed folder
      list = this.get_subfolders(oldfolder);

    // replace an existing table row
    this._remove_folder_row(id);
    row = $(this.add_folder_row(newfolder, display_name, is_protected, subscribed, true, class_name));

    // detect tree depth change
    if (len = list.length) {
      level = (oldfolder.split(this.env.delimiter)).length - (newfolder.split(this.env.delimiter)).length;
    }

    // move subfolders to the new branch
    for (n=0; n<len; n++) {
      id = list[n];
      name = this.env.subscriptionrows[id][0];
      dispname = this.env.subscriptionrows[id][1];
      oldrow = $('#'+id);
      tmprow = oldrow.clone(true);
      oldrow.remove();
      row.after(tmprow);
      row = tmprow;
      // update folder index
      name = name.replace(regex, newfolder);
      $('input[name="_subscribed[]"]', row).val(name);
      this.env.subscriptionrows[id][0] = name;
      // update the name if level is changed
      if (level != 0) {
        if (level > 0) {
          for (i=level; i>0; i--)
            dispname = dispname.replace(/^&nbsp;&nbsp;&nbsp;&nbsp;/, '');
        }
        else {
          for (i=level; i<0; i++)
            dispname = '&nbsp;&nbsp;&nbsp;&nbsp;' + dispname;
        }
        row.find('td:first').html(dispname);
        this.env.subscriptionrows[id][1] = dispname;
      }
    }

    // update list widget
    this.init_subscription_list();
  };

  // remove the table row of a specific mailbox from the table
  this.remove_folder_row = function(folder, subs)
  {
    var n, len, list = [], id = this.get_folder_row_id(folder);

    // get subfolders if any
    if (subs)
      list = this.get_subfolders(folder);

    // remove old row
    this._remove_folder_row(id);

    // remove subfolders
    for (n=0, len=list.length; n<len; n++)
      this._remove_folder_row(list[n]);
  };

  this._remove_folder_row = function(id)
  {
    this.subscription_list.remove_row(id.replace(/^rcmrow/, ''));
    $('#'+id).remove();
    delete this.env.subscriptionrows[id];
  }

  this.get_subfolders = function(folder)
  {
    var name, list = [],
      regex = new RegExp('^'+RegExp.escape(folder)+RegExp.escape(this.env.delimiter)),
      row = $('#'+this.get_folder_row_id(folder)).get(0);

    while (row = row.nextSibling) {
      if (row.id) {
        name = this.env.subscriptionrows[row.id][0];
        if (regex.test(name)) {
          list.push(row.id);
        }
        else
          break;
      }
    }

    return list;
  }

  this.subscribe = function(folder)
  {
    if (folder) {
      var lock = this.display_message(this.get_label('foldersubscribing'), 'loading');
      this.http_post('subscribe', {_mbox: folder}, lock);
    }
  };

  this.unsubscribe = function(folder)
  {
    if (folder) {
      var lock = this.display_message(this.get_label('folderunsubscribing'), 'loading');
      this.http_post('unsubscribe', {_mbox: folder}, lock);
    }
  };

  // helper method to find a specific mailbox row ID
  this.get_folder_row_id = function(folder)
  {
    var id, folders = this.env.subscriptionrows;
    for (id in folders)
      if (folders[id] && folders[id][0] == folder)
        break;

    return id;
  };

  // when user select a folder in manager
  this.show_folder = function(folder, path, force)
  {
    var win, target = window,
      url = '&_action=edit-folder&_mbox='+urlencode(folder);

    if (path)
      url += '&_path='+urlencode(path);

    if (win = this.get_frame_window(this.env.contentframe)) {
      target = win;
      url += '&_framed=1';
    }

    if (String(target.location.href).indexOf(url) >= 0 && !force)
      this.show_contentframe(true);
    else
      this.location_href(this.env.comm_path+url, target, true);
  };

  // disables subscription checkbox (for protected folder)
  this.disable_subscription = function(folder)
  {
    var id = this.get_folder_row_id(folder);
    if (id)
      $('input[name="_subscribed[]"]', $('#'+id)).prop('disabled', true);
  };

  this.folder_size = function(folder)
  {
    var lock = this.set_busy(true, 'loading');
    this.http_post('folder-size', {_mbox: folder}, lock);
  };

  this.folder_size_update = function(size)
  {
    $('#folder-size').replaceWith(size);
  };


  /*********************************************************/
  /*********           GUI functionality           *********/
  /*********************************************************/

  var init_button = function(cmd, prop)
  {
    var elm = document.getElementById(prop.id);
    if (!elm)
      return;

    var preload = false;
    if (prop.type == 'image') {
      elm = elm.parentNode;
      preload = true;
    }

    elm._command = cmd;
    elm._id = prop.id;
    if (prop.sel) {
      elm.onmousedown = function(e){ return rcmail.button_sel(this._command, this._id); };
      elm.onmouseup = function(e){ return rcmail.button_out(this._command, this._id); };
      if (preload)
        new Image().src = prop.sel;
    }
    if (prop.over) {
      elm.onmouseover = function(e){ return rcmail.button_over(this._command, this._id); };
      elm.onmouseout = function(e){ return rcmail.button_out(this._command, this._id); };
      if (preload)
        new Image().src = prop.over;
    }
  };

  // set event handlers on registered buttons
  this.init_buttons = function()
  {
    for (var cmd in this.buttons) {
      if (typeof cmd !== 'string')
        continue;

      for (var i=0; i<this.buttons[cmd].length; i++) {
        init_button(cmd, this.buttons[cmd][i]);
      }
    }

    // set active task button
    this.set_button(this.task, 'sel');
  };

  // set button to a specific state
  this.set_button = function(command, state)
  {
    var n, button, obj, a_buttons = this.buttons[command],
      len = a_buttons ? a_buttons.length : 0;

    for (n=0; n<len; n++) {
      button = a_buttons[n];
      obj = document.getElementById(button.id);

      if (!obj)
        continue;

      // get default/passive setting of the button
      if (button.type == 'image' && !button.status) {
        button.pas = obj._original_src ? obj._original_src : obj.src;
        // respect PNG fix on IE browsers
        if (obj.runtimeStyle && obj.runtimeStyle.filter && obj.runtimeStyle.filter.match(/src=['"]([^'"]+)['"]/))
          button.pas = RegExp.$1;
      }
      else if (!button.status)
        button.pas = String(obj.className);

      // set image according to button state
      if (button.type == 'image' && button[state]) {
        button.status = state;
        obj.src = button[state];
      }
      // set class name according to button state
      else if (button[state] !== undefined) {
        button.status = state;
        obj.className = button[state];
      }
      // disable/enable input buttons
      if (button.type == 'input') {
        button.status = state;
        obj.disabled = !state;
      }
    }
  };

  // display a specific alttext
  this.set_alttext = function(command, label)
  {
    var n, button, obj, link, a_buttons = this.buttons[command],
      len = a_buttons ? a_buttons.length : 0;

    for (n=0; n<len; n++) {
      button = a_buttons[n];
      obj = document.getElementById(button.id);

      if (button.type == 'image' && obj) {
        obj.setAttribute('alt', this.get_label(label));
        if ((link = obj.parentNode) && link.tagName.toLowerCase() == 'a')
          link.setAttribute('title', this.get_label(label));
      }
      else if (obj)
        obj.setAttribute('title', this.get_label(label));
    }
  };

  // mouse over button
  this.button_over = function(command, id)
  {
    var n, button, obj, a_buttons = this.buttons[command],
      len = a_buttons ? a_buttons.length : 0;

    for (n=0; n<len; n++) {
      button = a_buttons[n];
      if (button.id == id && button.status == 'act') {
        obj = document.getElementById(button.id);
        if (obj && button.over) {
          if (button.type == 'image')
            obj.src = button.over;
          else
            obj.className = button.over;
        }
      }
    }
  };

  // mouse down on button
  this.button_sel = function(command, id)
  {
    var n, button, obj, a_buttons = this.buttons[command],
      len = a_buttons ? a_buttons.length : 0;

    for (n=0; n<len; n++) {
      button = a_buttons[n];
      if (button.id == id && button.status == 'act') {
        obj = document.getElementById(button.id);
        if (obj && button.sel) {
          if (button.type == 'image')
            obj.src = button.sel;
          else
            obj.className = button.sel;
        }
        this.buttons_sel[id] = command;
      }
    }
  };

  // mouse out of button
  this.button_out = function(command, id)
  {
    var n, button, obj, a_buttons = this.buttons[command],
      len = a_buttons ? a_buttons.length : 0;

    for (n=0; n<len; n++) {
      button = a_buttons[n];
      if (button.id == id && button.status == 'act') {
        obj = document.getElementById(button.id);
        if (obj && button.act) {
          if (button.type == 'image')
            obj.src = button.act;
          else
            obj.className = button.act;
        }
      }
    }
  };

  // write to the document/window title
  this.set_pagetitle = function(title)
  {
    if (title && document.title)
      document.title = title;
  };

  // display a system message, list of types in common.css (below #message definition)
  this.display_message = function(msg, type, timeout)
  {
    // pass command to parent window
    if (this.is_framed())
      return parent.rcmail.display_message(msg, type, timeout);

    if (!this.gui_objects.message) {
      // save message in order to display after page loaded
      if (type != 'loading')
        this.pending_message = [msg, type, timeout];
      return 1;
    }

    type = type ? type : 'notice';

    var ref = this,
      key = this.html_identifier(msg),
      date = new Date(),
      id = type + date.getTime();

    if (!timeout)
      timeout = this.message_time * (type == 'error' || type == 'warning' ? 2 : 1);

    if (type == 'loading') {
      key = 'loading';
      timeout = this.env.request_timeout * 1000;
      if (!msg)
        msg = this.get_label('loading');
    }

    // The same message is already displayed
    if (this.messages[key]) {
      // replace label
      if (this.messages[key].obj)
        this.messages[key].obj.html(msg);
      // store label in stack
      if (type == 'loading') {
        this.messages[key].labels.push({'id': id, 'msg': msg});
      }
      // add element and set timeout
      this.messages[key].elements.push(id);
      setTimeout(function() { ref.hide_message(id, type == 'loading'); }, timeout);
      return id;
    }

    // create DOM object and display it
    var obj = $('<div>').addClass(type).html(msg).data('key', key),
      cont = $(this.gui_objects.message).append(obj).show();

    this.messages[key] = {'obj': obj, 'elements': [id]};

    if (type == 'loading') {
      this.messages[key].labels = [{'id': id, 'msg': msg}];
    }
    else {
      obj.click(function() { return ref.hide_message(obj); });
    }

    this.triggerEvent('message', { message:msg, type:type, timeout:timeout, object:obj });

    if (timeout > 0)
      setTimeout(function() { ref.hide_message(id, type == 'loading'); }, timeout);
    return id;
  };

  // make a message to disapear
  this.hide_message = function(obj, fade)
  {
    // pass command to parent window
    if (this.is_framed())
      return parent.rcmail.hide_message(obj, fade);

    if (!this.gui_objects.message)
      return;

    var k, n, i, msg, m = this.messages;

    // Hide message by object, don't use for 'loading'!
    if (typeof obj === 'object') {
      $(obj)[fade?'fadeOut':'hide']();
      msg = $(obj).data('key');
      if (this.messages[msg])
        delete this.messages[msg];
    }
    // Hide message by id
    else {
      for (k in m) {
        for (n in m[k].elements) {
          if (m[k] && m[k].elements[n] == obj) {
            m[k].elements.splice(n, 1);
            // hide DOM element if last instance is removed
            if (!m[k].elements.length) {
              m[k].obj[fade?'fadeOut':'hide']();
              delete m[k];
            }
            // set pending action label for 'loading' message
            else if (k == 'loading') {
              for (i in m[k].labels) {
                if (m[k].labels[i].id == obj) {
                  delete m[k].labels[i];
                }
                else {
                  msg = m[k].labels[i].msg;
                }
                m[k].obj.html(msg);
              }
            }
          }
        }
      }
    }
  };

  // remove all messages immediately
  this.clear_messages = function()
  {
    // pass command to parent window
    if (this.is_framed())
      return parent.rcmail.clear_messages();

    var k, n, m = this.messages;

    for (k in m)
      for (n in m[k].elements)
        if (m[k].obj)
          m[k].obj.hide();

    this.messages = {};
  };

  // open a jquery UI dialog with the given content
  this.show_popup_dialog = function(html, title)
  {
    // forward call to parent window
    if (this.is_framed()) {
      parent.rcmail.show_popup_dialog(html, title);
      return;
    }

    var popup = $('<div class="popup">')
      .html(html)
      .dialog({
        title: title,
        modal: true,
        resizable: true,
        width: 580,
        close: function(event, ui) { $(this).remove() }
      });

      // resize and center popup
      var win = $(window), w = win.width(), h = win.height(),
        width = popup.width(), height = popup.height();
      popup.dialog('option', { height: Math.min(h-40, height+50), width: Math.min(w-20, width+50) })
        .dialog('option', 'position', ['center', 'center']);  // only works in a separate call (!?)
  };

  // enable/disable buttons for page shifting
  this.set_page_buttons = function()
  {
    this.enable_command('nextpage', 'lastpage', (this.env.pagecount > this.env.current_page));
    this.enable_command('previouspage', 'firstpage', (this.env.current_page > 1));
  };

  // mark a mailbox as selected and set environment variable
  this.select_folder = function(name, prefix, encode)
  {
    if (this.gui_objects.folderlist) {
      var current_li, target_li;

      if ((current_li = $('li.selected', this.gui_objects.folderlist))) {
        current_li.removeClass('selected').addClass('unfocused');
      }
      if ((target_li = this.get_folder_li(name, prefix, encode))) {
        $(target_li).removeClass('unfocused').addClass('selected');
      }

      // trigger event hook
      this.triggerEvent('selectfolder', { folder:name, prefix:prefix });
    }
  };

  // adds a class to selected folder
  this.mark_folder = function(name, class_name, prefix, encode)
  {
    $(this.get_folder_li(name, prefix, encode)).addClass(class_name);
  };

  // adds a class to selected folder
  this.unmark_folder = function(name, class_name, prefix, encode)
  {
    $(this.get_folder_li(name, prefix, encode)).removeClass(class_name);
  };

  // helper method to find a folder list item
  this.get_folder_li = function(name, prefix, encode)
  {
    if (!prefix)
      prefix = 'rcmli';

    if (this.gui_objects.folderlist) {
      name = this.html_identifier(name, encode);
      return document.getElementById(prefix+name);
    }

    return null;
  };

  // for reordering column array (Konqueror workaround)
  // and for setting some message list global variables
  this.set_message_coltypes = function(coltypes, repl, smart_col)
  {
    var list = this.message_list,
      thead = list ? list.list.tHead : null,
      cell, col, n, len, th, tr;

    this.env.coltypes = coltypes;

    // replace old column headers
    if (thead) {
      if (repl) {
        th = document.createElement('thead');
        tr = document.createElement('tr');

        for (c=0, len=repl.length; c < len; c++) {
          cell = document.createElement('td');
          cell.innerHTML = repl[c].html || '';
          if (repl[c].id) cell.id = repl[c].id;
          if (repl[c].className) cell.className = repl[c].className;
          tr.appendChild(cell);
        }
        th.appendChild(tr);
        thead.parentNode.replaceChild(th, thead);
        list.thead = thead = th;
      }

      for (n=0, len=this.env.coltypes.length; n<len; n++) {
        col = this.env.coltypes[n];
        if ((cell = thead.rows[0].cells[n]) && (col == 'from' || col == 'to' || col == 'fromto')) {
          cell.id = 'rcm'+col;
          $('span,a', cell).text(this.get_label(col == 'fromto' ? smart_col : col));
          // if we have links for sorting, it's a bit more complicated...
          $('a', cell).click(function(){
            return rcmail.command('sort', this.id.replace(/^rcm/, ''), this);
          });
        }
      }
    }

    this.env.subject_col = null;
    this.env.flagged_col = null;
    this.env.status_col = null;

    if ((n = $.inArray('subject', this.env.coltypes)) >= 0) {
      this.env.subject_col = n;
      if (list)
        list.subject_col = n;
    }
    if ((n = $.inArray('flag', this.env.coltypes)) >= 0)
      this.env.flagged_col = n;
    if ((n = $.inArray('status', this.env.coltypes)) >= 0)
      this.env.status_col = n;

    if (list)
      list.init_header();
  };

  // replace content of row count display
  this.set_rowcount = function(text, mbox)
  {
    // #1487752
    if (mbox && mbox != this.env.mailbox)
      return false;

    $(this.gui_objects.countdisplay).html(text);

    // update page navigation buttons
    this.set_page_buttons();
  };

  // replace content of mailboxname display
  this.set_mailboxname = function(content)
  {
    if (this.gui_objects.mailboxname && content)
      this.gui_objects.mailboxname.innerHTML = content;
  };

  // replace content of quota display
  this.set_quota = function(content)
  {
    if (this.gui_objects.quotadisplay && content && content.type == 'text')
      $(this.gui_objects.quotadisplay).html(content.percent+'%').attr('title', content.title);

    this.triggerEvent('setquota', content);
    this.env.quota_content = content;
  };

  // update the mailboxlist
  this.set_unread_count = function(mbox, count, set_title, mark)
  {
    if (!this.gui_objects.mailboxlist)
      return false;

    this.env.unread_counts[mbox] = count;
    this.set_unread_count_display(mbox, set_title);

    if (mark)
      this.mark_folder(mbox, mark, '', true);
    else if (!count)
      this.unmark_folder(mbox, 'recent', '', true);
  };

  // update the mailbox count display
  this.set_unread_count_display = function(mbox, set_title)
  {
    var reg, link, text_obj, item, mycount, childcount, div;

    if (item = this.get_folder_li(mbox, '', true)) {
      mycount = this.env.unread_counts[mbox] ? this.env.unread_counts[mbox] : 0;
      link = $(item).children('a').eq(0);
      text_obj = link.children('span.unreadcount');
      if (!text_obj.length && mycount)
        text_obj = $('<span>').addClass('unreadcount').appendTo(link);
      reg = /\s+\([0-9]+\)$/i;

      childcount = 0;
      if ((div = item.getElementsByTagName('div')[0]) &&
          div.className.match(/collapsed/)) {
        // add children's counters
        for (var k in this.env.unread_counts)
          if (k.indexOf(mbox + this.env.delimiter) == 0)
            childcount += this.env.unread_counts[k];
      }

      if (mycount && text_obj.length)
        text_obj.html(this.env.unreadwrap.replace(/%[sd]/, mycount));
      else if (text_obj.length)
        text_obj.remove();

      // set parent's display
      reg = new RegExp(RegExp.escape(this.env.delimiter) + '[^' + RegExp.escape(this.env.delimiter) + ']+$');
      if (mbox.match(reg))
        this.set_unread_count_display(mbox.replace(reg, ''), false);

      // set the right classes
      if ((mycount+childcount)>0)
        $(item).addClass('unread');
      else
        $(item).removeClass('unread');
    }

    // set unread count to window title
    reg = /^\([0-9]+\)\s+/i;
    if (set_title && document.title) {
      var new_title = '',
        doc_title = String(document.title);

      if (mycount && doc_title.match(reg))
        new_title = doc_title.replace(reg, '('+mycount+') ');
      else if (mycount)
        new_title = '('+mycount+') '+doc_title;
      else
        new_title = doc_title.replace(reg, '');

      this.set_pagetitle(new_title);
    }
  };

  // display fetched raw headers
  this.set_headers = function(content)
  {
    if (this.gui_objects.all_headers_row && this.gui_objects.all_headers_box && content)
      $(this.gui_objects.all_headers_box).html(content).show();
  };

  // display all-headers row and fetch raw message headers
  this.show_headers = function(props, elem)
  {
    if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box || !this.env.uid)
      return;

    $(elem).removeClass('show-headers').addClass('hide-headers');
    $(this.gui_objects.all_headers_row).show();
    elem.onclick = function() { rcmail.command('hide-headers', '', elem); };

    // fetch headers only once
    if (!this.gui_objects.all_headers_box.innerHTML) {
      var lock = this.display_message(this.get_label('loading'), 'loading');
      this.http_post('headers', {_uid: this.env.uid}, lock);
    }
  };

  // hide all-headers row
  this.hide_headers = function(props, elem)
  {
    if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box)
      return;

    $(elem).removeClass('hide-headers').addClass('show-headers');
    $(this.gui_objects.all_headers_row).hide();
    elem.onclick = function() { rcmail.command('show-headers', '', elem); };
  };


  /********************************************************/
  /*********  html to text conversion functions   *********/
  /********************************************************/

  this.html2plain = function(htmlText, id)
  {
    var rcmail = this,
      url = '?_task=utils&_action=html2text',
      lock = this.set_busy(true, 'converting');

    this.log('HTTP POST: ' + url);

    $.ajax({ type: 'POST', url: url, data: htmlText, contentType: 'application/octet-stream',
      error: function(o, status, err) { rcmail.http_error(o, status, err, lock); },
      success: function(data) { rcmail.set_busy(false, null, lock); $('#'+id).val(data); rcmail.log(data); }
    });
  };

  this.plain2html = function(plain, id)
  {
    var lock = this.set_busy(true, 'converting');

    plain = plain.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    $('#'+id).val(plain ? '<pre>'+plain+'</pre>' : '');

    this.set_busy(false, null, lock);
  };


  /********************************************************/
  /*********        remote request methods        *********/
  /********************************************************/

  // compose a valid url with the given parameters
  this.url = function(action, query)
  {
    var querystring = typeof query === 'string' ? '&' + query : '';

    if (typeof action !== 'string')
      query = action;
    else if (!query || typeof query !== 'object')
      query = {};

    if (action)
      query._action = action;
    else
      query._action = this.env.action;

    var base = this.env.comm_path, k, param = {};

    // overwrite task name
    if (query._action.match(/([a-z]+)\/([a-z0-9-_.]+)/)) {
      query._action = RegExp.$2;
      base = base.replace(/\_task=[a-z]+/, '_task='+RegExp.$1);
    }

    // remove undefined values
    for (k in query) {
      if (query[k] !== undefined && query[k] !== null)
        param[k] = query[k];
    }

    return base + '&' + $.param(param) + querystring;
  };

  this.redirect = function(url, lock)
  {
    if (lock || lock === null)
      this.set_busy(true);

    if (this.is_framed()) {
      parent.rcmail.redirect(url, lock);
    }
    else {
      if (this.env.extwin) {
        if (typeof url == 'string')
          url += (url.indexOf('?') < 0 ? '?' : '&') + '_extwin=1';
        else
          url._extwin = 1;
      }
      this.location_href(url, window);
    }
  };

  this.goto_url = function(action, query, lock)
  {
    this.redirect(this.url(action, query));
  };

  this.location_href = function(url, target, frame)
  {
    if (frame)
      this.lock_frame();

    if (typeof url == 'object')
      url = this.env.comm_path + '&' + $.param(url);

    // simulate real link click to force IE to send referer header
    if (bw.ie && target == window)
      $('<a>').attr('href', url).appendTo(document.body).get(0).click();
    else
      target.location.href = url;

    // reset keep-alive interval
    this.start_keepalive();
  };

  // send a http request to the server
  this.http_request = function(action, query, lock)
  {
    var url = this.url(action, query);

    // trigger plugin hook
    var result = this.triggerEvent('request'+action, query);

    if (result !== undefined) {
      // abort if one the handlers returned false
      if (result === false)
        return false;
      else
        query = result;
    }

    url += '&_remote=1';

    // send request
    this.log('HTTP GET: ' + url);

    // reset keep-alive interval
    this.start_keepalive();

    return $.ajax({
      type: 'GET', url: url, data: { _unlock:(lock?lock:0) }, dataType: 'json',
      success: function(data){ ref.http_response(data); },
      error: function(o, status, err) { ref.http_error(o, status, err, lock, action); }
    });
  };

  // send a http POST request to the server
  this.http_post = function(action, postdata, lock)
  {
    var url = this.url(action);

    if (postdata && typeof postdata === 'object') {
      postdata._remote = 1;
      postdata._unlock = (lock ? lock : 0);
    }
    else
      postdata += (postdata ? '&' : '') + '_remote=1' + (lock ? '&_unlock='+lock : '');

    // trigger plugin hook
    var result = this.triggerEvent('request'+action, postdata);
    if (result !== undefined) {
      // abort if one of the handlers returned false
      if (result === false)
        return false;
      else
        postdata = result;
    }

    // send request
    this.log('HTTP POST: ' + url);

    // reset keep-alive interval
    this.start_keepalive();

    return $.ajax({
      type: 'POST', url: url, data: postdata, dataType: 'json',
      success: function(data){ ref.http_response(data); },
      error: function(o, status, err) { ref.http_error(o, status, err, lock, action); }
    });
  };

  // aborts ajax request
  this.abort_request = function(r)
  {
    if (r.request)
      r.request.abort();
    if (r.lock)
      this.set_busy(false, null, r.lock);
  };

  // handle HTTP response
  this.http_response = function(response)
  {
    if (!response)
      return;

    if (response.unlock)
      this.set_busy(false);

    this.triggerEvent('responsebefore', {response: response});
    this.triggerEvent('responsebefore'+response.action, {response: response});

    // set env vars
    if (response.env)
      this.set_env(response.env);

    // we have labels to add
    if (typeof response.texts === 'object') {
      for (var name in response.texts)
        if (typeof response.texts[name] === 'string')
          this.add_label(name, response.texts[name]);
    }

    // if we get javascript code from server -> execute it
    if (response.exec) {
      this.log(response.exec);
      eval(response.exec);
    }

    // execute callback functions of plugins
    if (response.callbacks && response.callbacks.length) {
      for (var i=0; i < response.callbacks.length; i++)
        this.triggerEvent(response.callbacks[i][0], response.callbacks[i][1]);
    }

    // process the response data according to the sent action
    switch (response.action) {
      case 'delete':
        if (this.task == 'addressbook') {
          var sid, uid = this.contact_list.get_selection(), writable = false;

          if (uid && this.contact_list.rows[uid]) {
            // search results, get source ID from record ID
            if (this.env.source == '') {
              sid = String(uid).replace(/^[^-]+-/, '');
              writable = sid && this.env.address_sources[sid] && !this.env.address_sources[sid].readonly;
            }
            else {
              writable = !this.env.address_sources[this.env.source].readonly;
            }
          }
          this.enable_command('compose', (uid && this.contact_list.rows[uid]));
          this.enable_command('delete', 'edit', writable);
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));
        }

      case 'moveto':
        if (this.env.action == 'show') {
          // re-enable commands on move/delete error
          this.enable_command(this.env.message_commands, true);
          if (!this.env.list_post)
            this.enable_command('reply-list', false);
        }
        else if (this.task == 'addressbook') {
          this.triggerEvent('listupdate', { folder:this.env.source, rowcount:this.contact_list.rowcount });
        }

      case 'purge':
      case 'expunge':
        if (this.task == 'mail') {
          if (!this.env.exists) {
            // clear preview pane content
            if (this.env.contentframe)
              this.show_contentframe(false);
            // disable commands useless when mailbox is empty
            this.enable_command(this.env.message_commands, 'purge', 'expunge',
              'select-all', 'select-none', 'expand-all', 'expand-unread', 'collapse-all', false);
          }
          if (this.message_list)
            this.triggerEvent('listupdate', { folder:this.env.mailbox, rowcount:this.message_list.rowcount });
        }
        break;

      case 'refresh':
      case 'check-recent':
      case 'getunread':
      case 'search':
        this.env.qsearch = null;
      case 'list':
        if (this.task == 'mail') {
          this.enable_command('show', 'select-all', 'select-none', this.env.messagecount > 0);
          this.enable_command('expunge', this.env.exists);
          this.enable_command('purge', this.purge_mailbox_test());
          this.enable_command('expand-all', 'expand-unread', 'collapse-all', this.env.threading && this.env.messagecount);

          if ((response.action == 'list' || response.action == 'search') && this.message_list) {
            this.msglist_select(this.message_list);
            this.triggerEvent('listupdate', { folder:this.env.mailbox, rowcount:this.message_list.rowcount });
          }
        }
        else if (this.task == 'addressbook') {
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));

          if (response.action == 'list' || response.action == 'search') {
            this.enable_command('search-create', this.env.source == '');
            this.enable_command('search-delete', this.env.search_id);
            this.update_group_commands();
            this.triggerEvent('listupdate', { folder:this.env.source, rowcount:this.contact_list.rowcount });
          }
        }
        break;
    }

    if (response.unlock)
      this.hide_message(response.unlock);

    this.triggerEvent('responseafter', {response: response});
    this.triggerEvent('responseafter'+response.action, {response: response});

    // reset keep-alive interval
    this.start_keepalive();
  };

  // handle HTTP request errors
  this.http_error = function(request, status, err, lock, action)
  {
    var errmsg = request.statusText;

    this.set_busy(false, null, lock);
    request.abort();

    // don't display error message on page unload (#1488547)
    if (this.unload)
      return;

    if (request.status && errmsg)
      this.display_message(this.get_label('servererror') + ' (' + errmsg + ')', 'error');
    else if (status == 'timeout')
      this.display_message(this.get_label('requesttimedout'), 'error');
    else if (request.status == 0 && status != 'abort')
      this.display_message(this.get_label('servererror') + ' (No connection)', 'error');

    // redirect to url specified in location header if not empty
    var location_url = request.getResponseHeader("Location");
    if (location_url && this.env.action != 'compose')  // don't redirect on compose screen, contents might get lost (#1488926)
      this.redirect(location_url);

    // 403 Forbidden response (CSRF prevention) - reload the page.
    // In case there's a new valid session it will be used, otherwise
    // login form will be presented (#1488960).
    if (request.status == 403) {
      (this.is_framed() ? parent : window).location.reload();
      return;
    }

    // re-send keep-alive requests after 30 seconds
    if (action == 'keep-alive')
      setTimeout(function(){ ref.keep_alive(); ref.start_keepalive(); }, 30000);
  };

  // callback when an iframe finished loading
  this.iframe_loaded = function(unlock)
  {
    this.set_busy(false, null, unlock);

    if (this.submit_timer)
      clearTimeout(this.submit_timer);
  };

  // post the given form to a hidden iframe
  this.async_upload_form = function(form, action, onload)
  {
    var ts = new Date().getTime(),
      frame_name = 'rcmupload'+ts;

    // upload progress support
    if (this.env.upload_progress_name) {
      var fname = this.env.upload_progress_name,
        field = $('input[name='+fname+']', form);

      if (!field.length) {
        field = $('<input>').attr({type: 'hidden', name: fname});
        field.prependTo(form);
      }

      field.val(ts);
    }

    // have to do it this way for IE
    // otherwise the form will be posted to a new window
    if (document.all) {
      var html = '<iframe name="'+frame_name+'" src="program/resources/blank.gif" style="width:0;height:0;visibility:hidden;"></iframe>';
      document.body.insertAdjacentHTML('BeforeEnd', html);
    }
    else { // for standards-compilant browsers
      var frame = document.createElement('iframe');
      frame.name = frame_name;
      frame.style.border = 'none';
      frame.style.width = 0;
      frame.style.height = 0;
      frame.style.visibility = 'hidden';
      document.body.appendChild(frame);
    }

    // handle upload errors, parsing iframe content in onload
    $(frame_name).bind('load', {ts:ts}, onload);

    $(form).attr({
        target: frame_name,
        action: this.url(action, { _id:this.env.compose_id||'', _uploadid:ts }),
        method: 'POST'})
      .attr(form.encoding ? 'encoding' : 'enctype', 'multipart/form-data')
      .submit();

    return frame_name;
  };

  // html5 file-drop API
  this.document_drag_hover = function(e, over)
  {
    e.preventDefault();
    $(ref.gui_objects.filedrop)[(over?'addClass':'removeClass')]('active');
  };

  this.file_drag_hover = function(e, over)
  {
    e.preventDefault();
    e.stopPropagation();
    $(ref.gui_objects.filedrop)[(over?'addClass':'removeClass')]('hover');
  };

  // handler when files are dropped to a designated area.
  // compose a multipart form data and submit it to the server
  this.file_dropped = function(e)
  {
    // abort event and reset UI
    this.file_drag_hover(e, false);

    // prepare multipart form data composition
    var files = e.target.files || e.dataTransfer.files,
      formdata = window.FormData ? new FormData() : null,
      fieldname = (this.env.filedrop.fieldname || '_file') + (this.env.filedrop.single ? '' : '[]'),
      boundary = '------multipartformboundary' + (new Date).getTime(),
      dashdash = '--', crlf = '\r\n',
      multipart = dashdash + boundary + crlf;

    if (!files || !files.length)
      return;

    // inline function to submit the files to the server
    var submit_data = function() {
      var multiple = files.length > 1,
        ts = new Date().getTime(),
        content = '<span>' + (multiple ? ref.get_label('uploadingmany') : files[0].name) + '</span>';

      // add to attachments list
      if (!ref.add2attachment_list(ts, { name:'', html:content, classname:'uploading', complete:false }))
        ref.file_upload_id = ref.set_busy(true, 'uploading');

      // complete multipart content and post request
      multipart += dashdash + boundary + dashdash + crlf;

      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ref.url(ref.env.filedrop.action||'upload', { _id:ref.env.compose_id||ref.env.cid||'', _uploadid:ts, _remote:1 }),
        contentType: formdata ? false : 'multipart/form-data; boundary=' + boundary,
        processData: false,
        timeout: 0, // disable default timeout set in ajaxSetup()
        data: formdata || multipart,
        headers: {'X-Roundcube-Request': ref.env.request_token},
        xhr: function() { var xhr = jQuery.ajaxSettings.xhr(); if (!formdata && xhr.sendAsBinary) xhr.send = xhr.sendAsBinary; return xhr; },
        success: function(data){ ref.http_response(data); },
        error: function(o, status, err) { ref.http_error(o, status, err, null, 'attachment'); }
      });
    };

    // get contents of all dropped files
    var last = this.env.filedrop.single ? 0 : files.length - 1;
    for (var j=0, i=0, f; j <= last && (f = files[i]); i++) {
      if (!f.name) f.name = f.fileName;
      if (!f.size) f.size = f.fileSize;
      if (!f.type) f.type = 'application/octet-stream';

      // file name contains non-ASCII characters, do UTF8-binary string conversion.
      if (!formdata && /[^\x20-\x7E]/.test(f.name))
        f.name_bin = unescape(encodeURIComponent(f.name));

      // filter by file type if requested
      if (this.env.filedrop.filter && !f.type.match(new RegExp(this.env.filedrop.filter))) {
        // TODO: show message to user
        continue;
      }

      // do it the easy way with FormData (FF 4+, Chrome 5+, Safari 5+)
      if (formdata) {
        formdata.append(fieldname, f);
        if (j == last)
          return submit_data();
      }
      // use FileReader supporetd by Firefox 3.6
      else if (window.FileReader) {
        var reader = new FileReader();

        // closure to pass file properties to async callback function
        reader.onload = (function(file, j) {
          return function(e) {
            multipart += 'Content-Disposition: form-data; name="' + fieldname + '"';
            multipart += '; filename="' + (f.name_bin || file.name) + '"' + crlf;
            multipart += 'Content-Length: ' + file.size + crlf;
            multipart += 'Content-Type: ' + file.type + crlf + crlf;
            multipart += reader.result + crlf;
            multipart += dashdash + boundary + crlf;

            if (j == last)  // we're done, submit the data
              return submit_data();
          }
        })(f,j);
        reader.readAsBinaryString(f);
      }
      // Firefox 3
      else if (f.getAsBinary) {
        multipart += 'Content-Disposition: form-data; name="' + fieldname + '"';
        multipart += '; filename="' + (f.name_bin || f.name) + '"' + crlf;
        multipart += 'Content-Length: ' + f.size + crlf;
        multipart += 'Content-Type: ' + f.type + crlf + crlf;
        multipart += f.getAsBinary() + crlf;
        multipart += dashdash + boundary +crlf;

        if (j == last)
          return submit_data();
      }

      j++;
    }
  };

  // starts interval for keep-alive signal
  this.start_keepalive = function()
  {
    if (!this.env.session_lifetime || this.env.framed || this.env.extwin || this.task == 'login' || this.env.action == 'print')
      return;

    if (this._keepalive)
      clearInterval(this._keepalive);

    this._keepalive = setInterval(function(){ ref.keep_alive(); }, this.env.session_lifetime * 0.5 * 1000);
  };

  // starts interval for refresh signal
  this.start_refresh = function()
  {
    if (!this.env.refresh_interval || this.env.framed || this.env.extwin || this.task == 'login' || this.env.action == 'print')
      return;

    if (this._refresh)
      clearInterval(this._refresh);

    this._refresh = setInterval(function(){ ref.refresh(); }, this.env.refresh_interval * 1000);
  };

  // sends keep-alive signal
  this.keep_alive = function()
  {
    if (!this.busy)
      this.http_request('keep-alive');
  };

  // sends refresh signal
  this.refresh = function()
  {
    if (this.busy) {
      // try again after 10 seconds
      setTimeout(function(){ ref.refresh(); ref.start_refresh(); }, 10000);
      return;
    }

    var params = {}, lock = this.set_busy(true, 'refreshing');

    if (this.task == 'mail' && this.gui_objects.mailboxlist)
      params = this.check_recent_params();

    // plugins should bind to 'requestrefresh' event to add own params
    this.http_request('refresh', params, lock);
  };

  // returns check-recent request parameters
  this.check_recent_params = function()
  {
    var params = {_mbox: this.env.mailbox};

    if (this.gui_objects.mailboxlist)
      params._folderlist = 1;
    if (this.gui_objects.messagelist)
      params._list = 1;
    if (this.gui_objects.quotadisplay)
      params._quota = 1;
    if (this.env.search_request)
      params._search = this.env.search_request;

    return params;
  };


  /********************************************************/
  /*********            helper methods            *********/
  /********************************************************/

  // get window.opener.rcmail if available
  this.opener = function()
  {
    // catch Error: Permission denied to access property rcmail
    try {
      if (window.opener && !opener.closed && opener.rcmail)
        return opener.rcmail;
    }
    catch (e) {}
  };

  // check if we're in show mode or if we have a unique selection
  // and return the message uid
  this.get_single_uid = function()
  {
    return this.env.uid ? this.env.uid : (this.message_list ? this.message_list.get_single_selection() : null);
  };

  // same as above but for contacts
  this.get_single_cid = function()
  {
    return this.env.cid ? this.env.cid : (this.contact_list ? this.contact_list.get_single_selection() : null);
  };

  // gets cursor position
  this.get_caret_pos = function(obj)
  {
    if (obj.selectionEnd !== undefined)
      return obj.selectionEnd;

    if (document.selection && document.selection.createRange) {
      var range = document.selection.createRange();
      if (range.parentElement() != obj)
        return 0;

      var gm = range.duplicate();
      if (obj.tagName == 'TEXTAREA')
        gm.moveToElementText(obj);
      else
        gm.expand('textedit');

      gm.setEndPoint('EndToStart', range);
      var p = gm.text.length;

      return p <= obj.value.length ? p : -1;
    }

    return obj.value.length;
  };

  // moves cursor to specified position
  this.set_caret_pos = function(obj, pos)
  {
    if (obj.setSelectionRange)
      obj.setSelectionRange(pos, pos);
    else if (obj.createTextRange) {
      var range = obj.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  };

  // disable/enable all fields of a form
  this.lock_form = function(form, lock)
  {
    if (!form || !form.elements)
      return;

    var n, len, elm;

    if (lock)
      this.disabled_form_elements = [];

    for (n=0, len=form.elements.length; n<len; n++) {
      elm = form.elements[n];

      if (elm.type == 'hidden')
        continue;
      // remember which elem was disabled before lock
      if (lock && elm.disabled)
        this.disabled_form_elements.push(elm);
      // check this.disabled_form_elements before inArray() as a workaround for FF5 bug
      // http://bugs.jquery.com/ticket/9873
      else if (lock || (this.disabled_form_elements && $.inArray(elm, this.disabled_form_elements)<0))
        elm.disabled = lock;
    }
  };

  this.mailto_handler_uri = function()
  {
    return location.href.split('?')[0] + '?_task=mail&_action=compose&_to=%s';
  };

  this.register_protocol_handler = function(name)
  {
    try {
      window.navigator.registerProtocolHandler('mailto', this.mailto_handler_uri(), name);
    }
    catch(e) {};
  };

  this.check_protocol_handler = function(name, elem)
  {
    var nav = window.navigator;
    if (!nav
      || (typeof nav.registerProtocolHandler != 'function')
      || ((typeof nav.isProtocolHandlerRegistered == 'function')
        && nav.isProtocolHandlerRegistered('mailto', this.mailto_handler_uri()) == 'registered')
    )
      $(elem).addClass('disabled');
    else
      $(elem).click(function() { rcmail.register_protocol_handler(name); return false; });
  };

  // Checks browser capabilities eg. PDF support, TIF support
  this.browser_capabilities_check = function()
  {
    if (!this.env.browser_capabilities)
      this.env.browser_capabilities = {};

    if (this.env.browser_capabilities.pdf === undefined)
      this.env.browser_capabilities.pdf = this.pdf_support_check();

    if (this.env.browser_capabilities.flash === undefined)
      this.env.browser_capabilities.flash = this.flash_support_check();

    if (this.env.browser_capabilities.tif === undefined)
      this.tif_support_check();
  };

  // Returns browser capabilities string
  this.browser_capabilities = function()
  {
    if (!this.env.browser_capabilities)
      return '';

    var n, ret = [];

    for (n in this.env.browser_capabilities)
      ret.push(n + '=' + this.env.browser_capabilities[n]);

    return ret.join();
  };

  this.tif_support_check = function()
  {
    var img = new Image();

    img.onload = function() { rcmail.env.browser_capabilities.tif = 1; };
    img.onerror = function() { rcmail.env.browser_capabilities.tif = 0; };
    img.src = 'program/resources/blank.tif';
  };

  this.pdf_support_check = function()
  {
    var plugin = navigator.mimeTypes ? navigator.mimeTypes["application/pdf"] : {},
      plugins = navigator.plugins,
      len = plugins.length,
      regex = /Adobe Reader|PDF|Acrobat/i;

    if (plugin && plugin.enabledPlugin)
        return 1;

    if (window.ActiveXObject) {
      try {
        if (axObj = new ActiveXObject("AcroPDF.PDF"))
          return 1;
      }
      catch (e) {}
      try {
        if (axObj = new ActiveXObject("PDF.PdfCtrl"))
          return 1;
      }
      catch (e) {}
    }

    for (i=0; i<len; i++) {
      plugin = plugins[i];
      if (typeof plugin === 'String') {
        if (regex.test(plugin))
          return 1;
      }
      else if (plugin.name && regex.test(plugin.name))
        return 1;
    }

    return 0;
  };

  this.flash_support_check = function()
  {
    var plugin = navigator.mimeTypes ? navigator.mimeTypes["application/x-shockwave-flash"] : {};

    if (plugin && plugin.enabledPlugin)
        return 1;

    if (window.ActiveXObject) {
      try {
        if (axObj = new ActiveXObject("ShockwaveFlash.ShockwaveFlash"))
          return 1;
      }
      catch (e) {}
    }

    return 0;
  };

  // Cookie setter
  this.set_cookie = function(name, value, expires)
  {
    setCookie(name, value, expires, this.env.cookie_path, this.env.cookie_domain, this.env.cookie_secure);
  }

}  // end object rcube_webmail


// some static methods
rcube_webmail.long_subject_title = function(elem, indent)
{
  if (!elem.title) {
    var $elem = $(elem);
    if ($elem.width() + indent * 15 > $elem.parent().width())
      elem.title = $elem.html();
  }
};

rcube_webmail.long_subject_title_ie = function(elem, indent)
{
  if (!elem.title) {
    var $elem = $(elem),
      txt = $.trim($elem.text()),
      tmp = $('<span>').text(txt)
        .css({'position': 'absolute', 'float': 'left', 'visibility': 'hidden',
          'font-size': $elem.css('font-size'), 'font-weight': $elem.css('font-weight')})
        .appendTo($('body')),
      w = tmp.width();

    tmp.remove();
    if (w + indent * 15 > $elem.width())
      elem.title = txt;
  }
};

rcube_webmail.prototype.get_cookie = getCookie;

// copy event engine prototype
rcube_webmail.prototype.addEventListener = rcube_event_engine.prototype.addEventListener;
rcube_webmail.prototype.removeEventListener = rcube_event_engine.prototype.removeEventListener;
rcube_webmail.prototype.triggerEvent = rcube_event_engine.prototype.triggerEvent;
