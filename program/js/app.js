/*
 +-----------------------------------------------------------------------+
 | RoundCube Webmail Client Script                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: common.js, list.js                                          |
 +-----------------------------------------------------------------------+

  $Id$
*/


var rcube_webmail_client;

function rcube_webmail()
  {
  this.env = new Object();
  this.labels = new Object();
  this.buttons = new Object();
  this.gui_objects = new Object();
  this.commands = new Object();
  this.onloads = new Array();

  // create protected reference to myself
  rcube_webmail_client = this;
  this.ref = 'rcube_webmail_client';
  var ref = this;
 
  // webmail client settings
  this.dblclick_time = 500;
  this.message_time = 3000;
  
  this.identifier_expr = new RegExp('[^0-9a-z\-_]', 'gi');
  
  // mimetypes supported by the browser (default settings)
  this.mimetypes = new Array('text/plain', 'text/html', 'text/xml',
                             'image/jpeg', 'image/gif', 'image/png',
                             'application/x-javascript', 'application/pdf',
                             'application/x-shockwave-flash');

  // default environment vars
  this.env.keep_alive = 60;        // seconds
  this.env.request_timeout = 180;  // seconds
  this.env.draft_autosave = 0;     // seconds
  this.env.comm_path = './';
  this.env.bin_path = './bin/';
  this.env.blankpage = 'program/blank.gif';

  // set environment variable(s)
  this.set_env = function(p, value)
    {
    if (p != null && typeof(p) == 'object' && !value)
      for (var n in p)
        this.env[n] = p[n];
    else
      this.env[p] = value;
    };

  // add a localized label to the client environment
  this.add_label = function(key, value)
    {
    this.labels[key] = value;
    };

  // add a button to the button list
  this.register_button = function(command, id, type, act, sel, over)
    {
    if (!this.buttons[command])
      this.buttons[command] = new Array();
      
    var button_prop = {id:id, type:type};
    if (act) button_prop.act = act;
    if (sel) button_prop.sel = sel;
    if (over) button_prop.over = over;

    this.buttons[command][this.buttons[command].length] = button_prop;    
    };

  // register a specific gui object
  this.gui_object = function(name, id)
    {
    this.gui_objects[name] = id;
    };
  
  // execute the given script on load
  this.add_onload = function(f)
    {
      this.onloads[this.onloads.length] = f;
    };

  // initialize webmail client
  this.init = function()
    {
    var p = this;
    this.task = this.env.task;
    
    // check browser
    if (!bw.dom || !bw.xmlhttp_test())
      {
      this.goto_url('error', '_code=0x199');
      return;
      }
    
    // find all registered gui objects
    for (var n in this.gui_objects)
      this.gui_objects[n] = rcube_find_object(this.gui_objects[n]);

    // tell parent window that this frame is loaded
    if (this.env.framed && parent.rcmail && parent.rcmail.set_busy)
      parent.rcmail.set_busy(false);

    // enable general commands
    this.enable_command('logout', 'mail', 'addressbook', 'settings', true);
    
    if (this.env.permaurl)
      this.enable_command('permaurl', true);
    
    switch (this.task)
      {
      case 'mail':
        if (this.gui_objects.messagelist)
          {
          this.message_list = new rcube_list_widget(this.gui_objects.messagelist, {multiselect:true, draggable:true, keyboard:true, dblclick_time:this.dblclick_time});
          this.message_list.row_init = function(o){ p.init_message_row(o); };
          this.message_list.addEventListener('dblclick', function(o){ p.msglist_dbl_click(o); });
          this.message_list.addEventListener('keypress', function(o){ p.msglist_keypress(o); });
          this.message_list.addEventListener('select', function(o){ p.msglist_select(o); });
          this.message_list.addEventListener('dragstart', function(o){ p.drag_start(o); });
          this.message_list.addEventListener('dragmove', function(o, e){ p.drag_move(e); });
          this.message_list.addEventListener('dragend', function(o){ p.drag_active = false; });

          this.message_list.init();
          this.enable_command('toggle_status', 'toggle_flag', true);
          
          if (this.gui_objects.mailcontframe)
            {
            this.gui_objects.mailcontframe.onmousedown = function(e){ return p.click_on_list(e); };
            document.onmouseup = function(e){ return p.doc_mouse_up(e); };
            }
          else
            this.message_list.focus();
          }
          
        if (this.env.coltypes)
          this.set_message_coltypes(this.env.coltypes);

        // enable mail commands
        this.enable_command('list', 'checkmail', 'compose', 'add-contact', 'search', 'reset-search', 'collapse-folder', true);

        if (this.env.search_text != null && document.getElementById('quicksearchbox') != null)
          document.getElementById('quicksearchbox').value = this.env.search_text;
        
        if (this.env.action=='show' || this.env.action=='preview')
          {
          this.enable_command('show', 'reply', 'reply-all', 'forward', 'moveto', 'delete', 'mark', 'viewsource', 'print', 'load-attachment', 'load-headers', true);
          if (this.env.next_uid)
            {
            this.enable_command('nextmessage', true);
            this.enable_command('lastmessage', true);
            }
          if (this.env.prev_uid)
            {
            this.enable_command('previousmessage', true);
            this.enable_command('firstmessage', true);
            }
          }

        if (this.env.trash_mailbox && this.env.mailbox != this.env.trash_mailbox)
          this.set_alttext('delete', 'movemessagetotrash');
        
        // make preview/message frame visible
        if (this.env.action == 'preview' && this.env.framed && parent.rcmail)
          {
          this.enable_command('compose', 'add-contact', false);
          parent.rcmail.show_contentframe(true);
          }

        if ((this.env.action=='show' || this.env.action=='preview') && this.env.blockedobjects)
          {
          if (this.gui_objects.remoteobjectsmsg)
            this.gui_objects.remoteobjectsmsg.style.display = 'block';
          this.enable_command('load-images', 'always-load', true);
          }

        if (this.env.action=='compose')
          {
          this.enable_command('add-attachment', 'send-attachment', 'remove-attachment', 'send', true);
          if (this.env.spellcheck)
            {
            this.env.spellcheck.spelling_state_observer = function(s){ ref.set_spellcheck_state(s); };
            this.set_spellcheck_state('ready');
            if (rcube_find_object('_is_html').value == '1')
              this.display_spellcheck_controls(false);
            }
          if (this.env.drafts_mailbox)
            this.enable_command('savedraft', true);
            
          document.onmouseup = function(e){ return p.doc_mouse_up(e); };
          }

        if (this.env.messagecount)
          this.enable_command('select-all', 'select-none', 'expunge', true);

        if (this.purge_mailbox_test())
          this.enable_command('purge', true);

        this.set_page_buttons();

        // init message compose form
        if (this.env.action=='compose')
          this.init_messageform();

        // show printing dialog
        if (this.env.action=='print')
          window.print();

        // get unread count for each mailbox
        if (this.gui_objects.mailboxlist)
        {
          this.env.unread_counts = {};
          this.gui_objects.folderlist = this.gui_objects.mailboxlist;
          this.http_request('getunread', '');
        }
        
        // ask user to send MDN
        if (this.env.mdn_request && this.env.uid)
        {
          var mdnurl = '_uid='+this.env.uid+'&_mbox='+urlencode(this.env.mailbox);
          if (confirm(this.get_label('mdnrequest')))
            this.http_post('sendmdn', mdnurl);
          else
            this.http_post('mark', mdnurl+'&_flag=mdnsent');
        }

        break;


      case 'addressbook':
        if (this.gui_objects.contactslist)
          {
          this.contact_list = new rcube_list_widget(this.gui_objects.contactslist, {multiselect:true, draggable:true, keyboard:true});
          this.contact_list.addEventListener('keypress', function(o){ p.contactlist_keypress(o); });
          this.contact_list.addEventListener('select', function(o){ p.contactlist_select(o); });
          this.contact_list.addEventListener('dragstart', function(o){ p.drag_start(o); });
          this.contact_list.addEventListener('dragmove', function(o, e){ p.drag_move(e); });
          this.contact_list.addEventListener('dragend', function(o){ p.drag_active = false; });
          this.contact_list.init();

          if (this.env.cid)
            this.contact_list.highlight_row(this.env.cid);

          if (this.gui_objects.contactslist.parentNode)
            {
            this.gui_objects.contactslist.parentNode.onmousedown = function(e){ return p.click_on_list(e); };
            document.onmouseup = function(e){ return p.doc_mouse_up(e); };
            }
          else
            this.contact_list.focus();
          }

        this.set_page_buttons();
        
        if (this.env.address_sources && this.env.address_sources[this.env.source] && !this.env.address_sources[this.env.source].readonly)
          this.enable_command('add', true);
        
        if (this.env.cid)
          this.enable_command('show', 'edit', true);

        if ((this.env.action=='add' || this.env.action=='edit') && this.gui_objects.editform)
          this.enable_command('save', true);
        else
          this.enable_command('search', 'reset-search', 'moveto', 'import', true);
          
        if (this.contact_list && this.contact_list.rowcount > 0)
          this.enable_command('export', true);

        this.enable_command('list', true);
        break;


      case 'settings':
        this.enable_command('preferences', 'identities', 'save', 'folders', true);
        
        if (this.env.action=='identities' || this.env.action=='edit-identity' || this.env.action=='add-identity') {
          this.enable_command('add', this.env.identities_level < 2);
          this.enable_command('delete', 'edit', true);
        }

        if (this.env.action=='edit-identity' || this.env.action=='add-identity')
          this.enable_command('save', true);
          
        if (this.env.action=='folders')
          this.enable_command('subscribe', 'unsubscribe', 'create-folder', 'rename-folder', 'delete-folder', true);

        if (this.gui_objects.identitieslist)
          {
          this.identity_list = new rcube_list_widget(this.gui_objects.identitieslist, {multiselect:false, draggable:false, keyboard:false});
          this.identity_list.addEventListener('select', function(o){ p.identity_select(o); });
          this.identity_list.init();
          this.identity_list.focus();

          if (this.env.iid)
            this.identity_list.highlight_row(this.env.iid);
          }

        if (this.gui_objects.subscriptionlist)
          this.init_subscription_list();

        break;

      case 'login':
        var input_user = rcube_find_object('rcmloginuser');
        var input_pass = rcube_find_object('rcmloginpwd');
        var input_tz = rcube_find_object('rcmlogintz');

        if (input_user)
          input_user.onkeyup = function(e){ return rcmail.login_user_keyup(e); };
        if (input_user && input_user.value=='')
          input_user.focus();
        else if (input_pass)
          input_pass.focus();

        // detect client timezone
        if (input_tz)
          input_tz.value = new Date().getTimezoneOffset() / -60;

        this.enable_command('login', true);
        break;
      
      default:
        break;
      }

    // enable basic commands
    this.enable_command('logout', true);

    // flag object as complete
    this.loaded = true;

    // show message
    if (this.pending_message)
      this.display_message(this.pending_message[0], this.pending_message[1]);

    // start keep-alive interval
    this.start_keepalive();
    
    // execute all foreign onload scripts
    for (var i=0; i<this.onloads.length; i++)
      {
      if (typeof(this.onloads[i]) == 'string')
        eval(this.onloads[i]);
      else if (typeof(this.onloads[i]) == 'function')
        this.onloads[i]();
      }
    };

  // start interval for keep-alive/recent_check signal
  this.start_keepalive = function()
    {
    if (this.env.keep_alive && !this.env.framed && this.task=='mail' && this.gui_objects.mailboxlist)
      this._int = setInterval(function(){ ref.check_for_recent(false); }, this.env.keep_alive * 1000);
    else if (this.env.keep_alive && !this.env.framed && this.task!='login')
      this._int = setInterval(function(){ ref.send_keep_alive(); }, this.env.keep_alive * 1000);
    }

  this.init_message_row = function(row)
  {
    var uid = row.uid;
    if (uid && this.env.messages[uid])
      {
      row.deleted = this.env.messages[uid].deleted ? true : false;
      row.unread = this.env.messages[uid].unread ? true : false;
      row.replied = this.env.messages[uid].replied ? true : false;
      row.flagged = this.env.messages[uid].flagged ? true : false;
      row.forwarded = this.env.messages[uid].forwarded ? true : false;
      }

    // set eventhandler to message icon
    if ((row.icon = row.obj.cells[0].childNodes[0]) && row.icon.nodeName=='IMG')
      {
      var p = this;
      row.icon.id = 'msgicn_'+row.uid;
      row.icon._row = row.obj;
      row.icon.onmousedown = function(e) { p.command('toggle_status', this); };
      }

    // global variable 'flagged_col' may be not defined yet
    if (!this.env.flagged_col && this.env.coltypes)
      {
      var found;
      if((found = find_in_array('flag', this.env.coltypes)) >= 0)
          this.set_env('flagged_col', found+1);
      }

    // set eventhandler to flag icon, if icon found
    if (this.env.flagged_col && (row.flagged_icon = row.obj.cells[this.env.flagged_col].childNodes[0]) 
	&& row.flagged_icon.nodeName=='IMG')
      {
      var p = this;
      row.flagged_icon.id = 'flaggedicn_'+row.uid;
      row.flagged_icon._row = row.obj;
      row.flagged_icon.onmousedown = function(e) { p.command('toggle_flag', this); };
      }
  };

  // init message compose form: set focus and eventhandlers
  this.init_messageform = function()
    {
    if (!this.gui_objects.messageform)
      return false;
    
    //this.messageform = this.gui_objects.messageform;
    var input_from = rcube_find_object('_from');
    var input_to = rcube_find_object('_to');
    var input_cc = rcube_find_object('_cc');
    var input_bcc = rcube_find_object('_bcc');
    var input_replyto = rcube_find_object('_replyto');
    var input_subject = rcube_find_object('_subject');
    var input_message = rcube_find_object('_message');
    var draftid = rcube_find_object('_draft_saveid');

    // init live search events
    if (input_to)
      this.init_address_input_events(input_to);
    if (input_cc)
      this.init_address_input_events(input_cc);
    if (input_bcc)
      this.init_address_input_events(input_bcc);

    // add signature according to selected identity
    if (input_from && input_from.type=='select-one' && (!draftid || draftid.value=='')
	// if we have HTML editor, signature is added in callback
	&& rcube_find_object('_is_html').value != '1')
      {
      this.change_identity(input_from);
      }

    if (input_to && input_to.value=='')
      input_to.focus();
    else if (input_subject && input_subject.value=='')
      input_subject.focus();
    else if (input_message)
      this.set_caret2start(input_message);

    // get summary of all field values
    this.compose_field_hash(true);
 
    // start the auto-save timer
    this.auto_save_start();
    };

  this.init_address_input_events = function(obj)
    {
    var handler = function(e){ return ref.ksearch_keypress(e,this); };
    
    if (obj.addEventListener)
      obj.addEventListener(bw.safari ? 'keydown' : 'keypress', handler, false);
    else
      obj.onkeydown = handler;

    obj.setAttribute('autocomplete', 'off');
    };


  /*********************************************************/
  /*********       client command interface        *********/
  /*********************************************************/

  // execute a specific command on the web client
  this.command = function(command, props, obj)
    {
    if (obj && obj.blur)
      obj.blur();

    if (this.busy)
      return false;

    // command not supported or allowed
    if (!this.commands[command])
      {
      // pass command to parent window
      if (this.env.framed && parent.rcmail && parent.rcmail.command)
        parent.rcmail.command(command, props);

      return false;
      }
      
   // check input before leaving compose step
   if (this.task=='mail' && this.env.action=='compose' && (command=='list' || command=='mail' || command=='addressbook' || command=='settings'))
     {
     if (this.cmp_hash != this.compose_field_hash() && !confirm(this.get_label('notsentwarning')))
        return false;
     }

    // process command
    switch (command)
      {
      case 'login':
        if (this.gui_objects.loginform)
          this.gui_objects.loginform.submit();
        break;

      case 'logout':
        this.goto_url('logout', '', true);
        break;      

      // commands to switch task
      case 'mail':
      case 'addressbook':
      case 'settings':
        this.switch_task(command);
        break;

      case 'permaurl':
        if (obj && obj.href && obj.target)
          return true;
        else if (this.env.permaurl)
          parent.location.href = this.env.permaurl;
          break;

      // misc list commands
      case 'list':
        if (this.task=='mail')
          {
          if (this.env.search_request<0 || (props != '' && (this.env.search_request && props != this.env.mailbox)))
            this.reset_qsearch();

          this.list_mailbox(props);

          if (this.env.trash_mailbox)
            this.set_alttext('delete', this.env.mailbox != this.env.trash_mailbox ? 'movemessagetotrash' : 'deletemessage');
          }
        else if (this.task=='addressbook')
          {
          if (this.env.search_request<0 || (this.env.search_request && props != this.env.source))
            this.reset_qsearch();

          this.list_contacts(props);
          this.enable_command('add', (this.env.address_sources && !this.env.address_sources[props].readonly));
          }
        break;


      case 'load-headers':
        this.load_headers(obj);
        break;


      case 'sort':
        // get the type of sorting
        var a_sort = props.split('_');
        var sort_col = a_sort[0];
        var sort_order = a_sort[1] ? a_sort[1].toUpperCase() : null;
        var header;

        // no sort order specified: toggle
        if (sort_order==null)
          {
          if (this.env.sort_col==sort_col)
            sort_order = this.env.sort_order=='ASC' ? 'DESC' : 'ASC';
          else
            sort_order = this.env.sort_order;
          }

        if (this.env.sort_col==sort_col && this.env.sort_order==sort_order)
          break;

        // set table header class
        if (header = document.getElementById('rcm'+this.env.sort_col))
          this.set_classname(header, 'sorted'+(this.env.sort_order.toUpperCase()), false);
        if (header = document.getElementById('rcm'+sort_col))
          this.set_classname(header, 'sorted'+sort_order, true);

        // save new sort properties
        this.env.sort_col = sort_col;
        this.env.sort_order = sort_order;

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
        if (this.env.messagecount)
          this.expunge_mailbox(this.env.mailbox);
        break;

      case 'purge':
      case 'empty-mailbox':
        if (this.env.messagecount)
          this.purge_mailbox(this.env.mailbox);
        break;


      // common commands used in multiple tasks
      case 'show':
        if (this.task=='mail')
          {
          var uid = this.get_single_uid();
          if (uid && (!this.env.uid || uid != this.env.uid))
            {
            if (this.env.mailbox == this.env.drafts_mailbox)
              this.goto_url('compose', '_draft_uid='+uid+'&_mbox='+urlencode(this.env.mailbox), true);
            else
              this.show_message(uid);
            }
          }
        else if (this.task=='addressbook')
          {
          var cid = props ? props : this.get_single_cid();
          if (cid && !(this.env.action=='show' && cid==this.env.cid))
            this.load_contact(cid, 'show');
          }
        break;

      case 'add':
        if (this.task=='addressbook')
          this.load_contact(0, 'add');
        else if (this.task=='settings')
          {
          this.identity_list.clear_selection();
          this.load_identity(0, 'add-identity');
          }
        break;

      case 'edit':
        var cid;
        if (this.task=='addressbook' && (cid = this.get_single_cid()))
          this.load_contact(cid, 'edit');
        else if (this.task=='settings' && props)
          this.load_identity(props, 'edit-identity');
        break;

      case 'save-identity':
      case 'save':
        if (this.gui_objects.editform)
          {
          var input_pagesize = rcube_find_object('_pagesize');
          var input_name  = rcube_find_object('_name');
          var input_email = rcube_find_object('_email');

          // user prefs
          if (input_pagesize && isNaN(parseInt(input_pagesize.value)))
            {
            alert(this.get_label('nopagesizewarning'));
            input_pagesize.focus();
            break;
            }
          // contacts/identities
          else
            {
            if (input_name && input_name.value == '')
              {
              alert(this.get_label('nonamewarning'));
              input_name.focus();
              break;
              }
            else if (input_email && !rcube_check_email(input_email.value))
              {
              alert(this.get_label('noemailwarning'));
              input_email.focus();
              break;
              }
            }

          this.gui_objects.editform.submit();
          }
        break;

      case 'delete':
        // mail task
        if (this.task=='mail')
          this.delete_messages();
        // addressbook task
        else if (this.task=='addressbook')
          this.delete_contacts();
        // user settings task
        else if (this.task=='settings')
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

      case 'mark':
        if (props)
          this.mark_message(props);
        break;
      
      case 'toggle_status':
        if (props && !props._row)
          break;
        
        var uid;
        var flag = 'read';
        
        if (props._row.uid)
          {
          uid = props._row.uid;
          
          // toggle read/unread
          if (this.message_list.rows[uid].deleted) {
            flag = 'undelete';
          } else if (!this.message_list.rows[uid].unread)
            flag = 'unread';
          }
          
        this.mark_message(flag, uid);
        break;
        
      case 'toggle_flag':
        if (props && !props._row)
          break;

        var uid;
        var flag = 'flagged';

        if (props._row.uid)
          {
          uid = props._row.uid;
          // toggle flagged/unflagged
          if (this.message_list.rows[uid].flagged)
            flag = 'unflagged';
          }
        this.mark_message(flag, uid);
        break;

      case 'always-load':
        if (this.env.uid && this.env.sender) {
          this.add_contact(urlencode(this.env.sender));
          window.setTimeout(function(){ ref.command('load-images'); }, 300);
          break;
        }
        
      case 'load-images':
        if (this.env.uid)
          this.show_message(this.env.uid, true, this.env.action=='preview');
        break;

      case 'load-attachment':
        var qstring = '_mbox='+urlencode(this.env.mailbox)+'&_uid='+this.env.uid+'&_part='+props.part;
        
        // open attachment in frame if it's of a supported mimetype
        if (this.env.uid && props.mimetype && find_in_array(props.mimetype, this.mimetypes)>=0)
          {
          if (props.mimetype == 'text/html')
            qstring += '&_safe=1';
          this.attachment_win = window.open(this.env.comm_path+'&_action=get&'+qstring+'&_frame=1', 'rcubemailattachment');
          if (this.attachment_win)
            {
            window.setTimeout(function(){ ref.attachment_win.focus(); }, 10);
            break;
            }
          }

        this.goto_url('get', qstring+'&_download=1', false);
        break;
        
      case 'select-all':
        this.message_list.select_all(props);
        break;

      case 'select-none':
        this.message_list.clear_selection();
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
          this.show_message(this.env.prev_uid, false, this.env.action=='preview');
        break;

      case 'firstmessage':
        if (this.env.first_uid)
          this.show_message(this.env.first_uid);
        break;
      
      case 'checkmail':
        this.check_for_recent(true);
        break;
      
      case 'compose':
        var url = this.env.comm_path+'&_action=compose';
       
        if (this.task=='mail')
        {
          url += '&_mbox='+urlencode(this.env.mailbox);
          
          if (this.env.mailbox==this.env.drafts_mailbox)
          {
            var uid;
            if (uid = this.get_single_uid())
              url += '&_draft_uid='+uid;
          }
          else if (props)
             url += '&_to='+urlencode(props);
        }
        // modify url if we're in addressbook
        else if (this.task=='addressbook')
          {
          // switch to mail compose step directly
          if (props && props.indexOf('@') > 0)
            {
            url = this.get_task_url('mail', url);
            this.redirect(url + '&_to='+urlencode(props));
            break;
            }
          
          // use contact_id passed as command parameter
          var a_cids = new Array();
          if (props)
            a_cids[a_cids.length] = props;
          // get selected contacts
          else if (this.contact_list)
            {
            var selection = this.contact_list.get_selection();
            for (var n=0; n<selection.length; n++)
              a_cids[a_cids.length] = selection[n];
            }
            
          if (a_cids.length)
            this.http_request('mailto', '_cid='+urlencode(a_cids.join(','))+'&_source='+urlencode(this.env.source), true);

          break;
          }

        // don't know if this is necessary...
        url = url.replace(/&_framed=1/, "");

        this.redirect(url);
        break;
        
      case 'spellcheck':
        if (window.tinyMCE && tinyMCE.get('compose-body')) {
          tinyMCE.execCommand('mceSpellCheck', true);
        }
        else if (this.env.spellcheck && this.env.spellcheck.spellCheck && this.spellcheck_ready) {
          this.env.spellcheck.spellCheck(this.env.spellcheck.check_link);
          this.set_spellcheck_state('checking');
        }
        break;

      case 'savedraft':
        // Reset the auto-save timer
        self.clearTimeout(this.save_timer);

        if (!this.gui_objects.messageform)
          break;

        // if saving Drafts is disabled in main.inc.php
        // or if compose form did not change
        if (!this.env.drafts_mailbox || this.cmp_hash == this.compose_field_hash())
          break;

        this.set_busy(true, 'savingmessage');
        var form = this.gui_objects.messageform;
        form.target = "savetarget";
        form._draft.value = '1';
        form.submit();
        break;

      case 'send':
        if (!this.gui_objects.messageform)
          break;

        if (!this.check_compose_input())
          break;

        // Reset the auto-save timer
        self.clearTimeout(this.save_timer);

        // all checks passed, send message
        this.set_busy(true, 'sendingmessage');
        var form = this.gui_objects.messageform;
        form.target = "savetarget";     
        form._draft.value = '';
        form.submit();
        
        // clear timeout (sending could take longer)
        clearTimeout(this.request_timer);
        break;

      case 'add-attachment':
        this.show_attachment_form(true);
        
      case 'send-attachment':
        // Reset the auto-save timer
        self.clearTimeout(this.save_timer);

        this.upload_file(props)      
        break;
      
      case 'remove-attachment':
        this.remove_attachment(props);
        break;

      case 'reply-all':
      case 'reply':
        var uid;
        if (uid = this.get_single_uid())
          this.goto_url('compose', '_reply_uid='+uid+'&_mbox='+urlencode(this.env.mailbox)+(command=='reply-all' ? '&_all=1' : ''), true);
        break;      

      case 'forward':
        var uid;
        if (uid = this.get_single_uid())
          this.goto_url('compose', '_forward_uid='+uid+'&_mbox='+urlencode(this.env.mailbox), true);
        break;
        
      case 'print':
        var uid;
        if (uid = this.get_single_uid())
        {
          ref.printwin = window.open(this.env.comm_path+'&_action=print&_uid='+uid+'&_mbox='+urlencode(this.env.mailbox)+(this.env.safemode ? '&_safe=1' : ''));
          if (this.printwin)
          {
            window.setTimeout(function(){ ref.printwin.focus(); }, 20);
            if (this.env.action != 'show')
              this.mark_message('read', uid);
          }
        }
        break;

      case 'viewsource':
        var uid;
        if (uid = this.get_single_uid())
          {
          ref.sourcewin = window.open(this.env.comm_path+'&_action=viewsource&_uid='+this.env.uid+'&_mbox='+urlencode(this.env.mailbox));
          if (this.sourcewin)
            window.setTimeout(function(){ ref.sourcewin.focus(); }, 20);
          }
        break;

      case 'add-contact':
        this.add_contact(props);
        break;
      
      // quicksearch
      case 'search':
        if (!props && this.gui_objects.qsearchbox)
          props = this.gui_objects.qsearchbox.value;
        if (props)
        {
          this.qsearch(props);
          break;
        }

      // reset quicksearch
      case 'reset-search':
        var s = this.env.search_request;
        this.reset_qsearch();
        
        if (s && this.env.mailbox)
          this.list_mailbox(this.env.mailbox);
        else if (s && this.task == 'addressbook')
          this.list_contacts(this.env.source);
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
          this.goto_url('import');
        break;
        
      case 'export':
        if (this.contact_list.rowcount > 0) {
          var add_url = (this.env.source ? '_source='+urlencode(this.env.source)+'&' : '');
          if (this.env.search_request)
            add_url += '_search='+this.env.search_request;
        
          this.goto_url('export', add_url);
        }
        break;

      // collapse/expand folder
      case 'collapse-folder':
        if (props)
          this.collapse_folder(props);
        break;

      // user settings commands
      case 'preferences':
        this.goto_url('');
        break;

      case 'identities':
        this.goto_url('identities');
        break;
          
      case 'delete-identity':
        this.delete_identity();
        
      case 'folders':
        this.goto_url('folders');
        break;

      case 'subscribe':
        this.subscribe_folder(props);
        break;

      case 'unsubscribe':
        this.unsubscribe_folder(props);
        break;
        
      case 'create-folder':
        this.create_folder(props);
        break;

      case 'rename-folder':
        this.rename_folder(props);
        break;

      case 'delete-folder':
        this.delete_folder(props);
        break;

      }

    return obj ? false : true;
    };

  // set command enabled or disabled
  this.enable_command = function()
    {
    var args = arguments;
    if(!args.length) return -1;

    var command;
    var enable = args[args.length-1];
    
    for(var n=0; n<args.length-1; n++)
      {
      command = args[n];
      this.commands[command] = enable;
      this.set_button(command, (enable ? 'act' : 'pas'));
      }
      return true;
    };

  // lock/unlock interface
  this.set_busy = function(a, message)
    {
    if (a && message)
      {
      var msg = this.get_label(message);
      if (msg==message)        
        msg = 'Loading...';

      this.display_message(msg, 'loading', true);
      }
    else if (!a)
      this.hide_message();

    this.busy = a;
    //document.body.style.cursor = a ? 'wait' : 'default';
    
    if (this.gui_objects.editform)
      this.lock_form(this.gui_objects.editform, a);
      
    // clear pending timer
    if (this.request_timer)
      clearTimeout(this.request_timer);

    // set timer for requests
    if (a && this.env.request_timeout)
      this.request_timer = window.setTimeout(function(){ ref.request_timed_out(); }, this.env.request_timeout * 1000);
    };

  // return a localized string
  this.get_label = function(name)
    {
    if (this.labels[name])
      return this.labels[name];
    else
      return name;
    };

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
    
  // called when a request timed out
  this.request_timed_out = function()
    {
    this.set_busy(false);
    this.display_message('Request timed out!', 'error');
    };


  /*********************************************************/
  /*********        event handling methods         *********/
  /*********************************************************/

  this.doc_mouse_up = function(e)
  {
    var model, li;

    if (this.message_list) {
      if (!rcube_mouse_is_over(e, this.message_list.list))
        this.message_list.blur();
      model = this.env.mailboxes;
    }
    else if (this.contact_list) {
      if (!rcube_mouse_is_over(e, this.contact_list.list))
        this.contact_list.blur();
      model = this.env.address_sources;
    }
    else if (this.ksearch_value) {
      this.ksearch_blur();
    }

    // handle mouse release when dragging
    if (this.drag_active && model && this.env.last_folder_target) {
      this.set_classname(this.get_folder_li(this.env.last_folder_target), 'droptarget', false);
      this.command('moveto', model[this.env.last_folder_target].id);
      this.env.last_folder_target = null;
    }
  };

  this.drag_start = function(list)
  {
    this.initialBodyScrollTop = bw.ie ? 0 : window.pageYOffset;
    this.initialMailBoxScrollTop = document.getElementById("mailboxlist-container").scrollTop;

    var model = this.task == 'mail' ? this.env.mailboxes : this.env.address_sources;

    this.drag_active = true;
    if (this.preview_timer)
      clearTimeout(this.preview_timer);
    
    // save folderlist and folders location/sizes for droptarget calculation in drag_move()
    if (this.gui_objects.folderlist && model)
      {
      var li, pos, list, height;
      list = rcube_find_object(this.task == 'mail' ? 'mailboxlist' : 'directorylist');
      pos = rcube_get_object_pos(list);
      this.env.folderlist_coords = {x1:pos.x, y1:pos.y, x2:pos.x + list.offsetWidth, y2:pos.y + list.offsetHeight};

      this.env.folder_coords = new Array();
      for (var k in model) {
        if (li = this.get_folder_li(k))
	  {
	  pos = rcube_get_object_pos(li.firstChild);
	  // only visible folders
	  if (height = li.firstChild.offsetHeight)
	    this.env.folder_coords[k] = {x1:pos.x, y1:pos.y, x2:pos.x + li.firstChild.offsetWidth, y2:pos.y + height};
          }
        }
      }
  };

  this.drag_move = function(e)
    {
    if (this.gui_objects.folderlist && this.env.folder_coords)
      {
      // offsets to compensate for scrolling while dragging a message
      var boffset = bw.ie ? -document.documentElement.scrollTop : this.initialBodyScrollTop;
      var moffset = this.initialMailBoxScrollTop-document.getElementById('mailboxlist-container').scrollTop;
      var toffset = -moffset-boffset;

      var li, pos, mouse;
      mouse = rcube_event.get_mouse_pos(e);
      pos = this.env.folderlist_coords;

      mouse.y += toffset;

      // if mouse pointer is outside of folderlist
      if (mouse.x < pos.x1 || mouse.x >= pos.x2 
	    || mouse.y < pos.y1 || mouse.y >= pos.y2)
	{
	if (this.env.last_folder_target) {
	  this.set_classname(this.get_folder_li(this.env.last_folder_target), 'droptarget', false);
          this.env.last_folder_target = null;
	  }
	return;
        }

      // over the folders
      for (var k in this.env.folder_coords)
        {
	pos = this.env.folder_coords[k];
	if (this.check_droptarget(k) && ((mouse.x >= pos.x1) && (mouse.x < pos.x2) 
	    && (mouse.y >= pos.y1) && (mouse.y < pos.y2)))
	  {
          this.set_classname(this.get_folder_li(k), 'droptarget', true);
	  this.env.last_folder_target = k;
	  }
	else
	  this.set_classname(this.get_folder_li(k), 'droptarget', false);
        }
      }
    };
  
  this.collapse_folder = function(id)
    {
    var div;
    if ((li = this.get_folder_li(id)) &&
        (div = li.getElementsByTagName("div")[0]) &&
        (div.className.match(/collapsed/) || div.className.match(/expanded/)))
      {
      var ul = li.getElementsByTagName("ul")[0];
      if (div.className.match(/collapsed/))
        {
        ul.style.display = '';
        this.set_classname(div, 'collapsed', false);
        this.set_classname(div, 'expanded', true);
        var reg = new RegExp('&'+urlencode(id)+'&');
        this.set_env('collapsed_folders', this.env.collapsed_folders.replace(reg, ''));
        }
      else
        {
        ul.style.display = 'none';
        this.set_classname(div, 'expanded', false);
        this.set_classname(div, 'collapsed', true);
        this.set_env('collapsed_folders', this.env.collapsed_folders+'&'+urlencode(id)+'&');

        // select parent folder if one of its childs is currently selected
        if (this.env.mailbox.indexOf(id + this.env.delimiter) == 0)
          this.command('list', id);
        }

      // Work around a bug in IE6 and IE7, see #1485309
      if ((bw.ie6 || bw.ie7) &&
          li.nextSibling &&
          (li.nextSibling.getElementsByTagName("ul").length>0) &&
          li.nextSibling.getElementsByTagName("ul")[0].style &&
          (li.nextSibling.getElementsByTagName("ul")[0].style.display!='none'))
        {
          li.nextSibling.getElementsByTagName("ul")[0].style.display = 'none';
          li.nextSibling.getElementsByTagName("ul")[0].style.display = '';
        }

      this.http_post('save-pref', '_name=collapsed_folders&_value='+urlencode(this.env.collapsed_folders));
      this.set_unread_count_display(id, false);
      }
    }

  this.click_on_list = function(e)
    {
    if (this.gui_objects.qsearchbox)
      this.gui_objects.qsearchbox.blur();

    if (this.message_list)
      this.message_list.focus();
    else if (this.contact_list)
      this.contact_list.focus();

    var mbox_li;
    if (mbox_li = this.get_folder_li())
      this.set_classname(mbox_li, 'unfocused', true);

    return rcube_event.get_button(e) == 2 ? true : rcube_event.cancel(e);
    };

  this.msglist_select = function(list)
    {
    if (this.preview_timer)
      clearTimeout(this.preview_timer);

    var selected = list.selection.length==1;

    // Hide certain command buttons when Drafts folder is selected
    if (this.env.mailbox == this.env.drafts_mailbox)
      {
      this.enable_command('reply', 'reply-all', 'forward', false);
      this.enable_command('show', 'print', selected);
      this.enable_command('delete', 'moveto', 'mark', (list.selection.length > 0 ? true : false));
      }
    else
      {
      this.enable_command('show', 'reply', 'reply-all', 'forward', 'print', selected);
      this.enable_command('delete', 'moveto', 'mark', (list.selection.length > 0 ? true : false));
      }

    // start timer for message preview (wait for double click)
    if (selected && this.env.contentframe && !list.multi_selecting)
      this.preview_timer = window.setTimeout(function(){ ref.msglist_get_preview(); }, 200);
    else if (this.env.contentframe)
      this.show_contentframe(false);
    };

  this.msglist_dbl_click = function(list)
    {
      if (this.preview_timer)
        clearTimeout(this.preview_timer);

    var uid = list.get_single_selection();
    if (uid && this.env.mailbox == this.env.drafts_mailbox)
      this.goto_url('compose', '_draft_uid='+uid+'&_mbox='+urlencode(this.env.mailbox), true);
    else if (uid)
      this.show_message(uid, false, false);
    };

  this.msglist_keypress = function(list)
    {
    if (list.key_pressed == list.ENTER_KEY)
      this.command('show');
    else if (list.key_pressed == list.DELETE_KEY)
      this.command('delete');
    else if (list.key_pressed == list.BACKSPACE_KEY)
      this.command('delete');
    else
      list.shiftkey = false;
    };

  this.msglist_get_preview = function()
  {
    var uid = this.get_single_uid();
    if (uid && this.env.contentframe && !this.drag_active)
      this.show_message(uid, false, true);
    else if (this.env.contentframe)
      this.show_contentframe(false);
  };
  
  this.check_droptarget = function(id)
  {
    if (this.task == 'mail')
      return (this.env.mailboxes[id] && this.env.mailboxes[id].id != this.env.mailbox && !this.env.mailboxes[id].virtual);
    else if (this.task == 'addressbook')
      return (id != this.env.source && this.env.address_sources[id] && !this.env.address_sources[id].readonly);
    else if (this.task == 'settings')
      return (id != this.env.folder);
  };


  /*********************************************************/
  /*********     (message) list functionality      *********/
  /*********************************************************/

  // when user doble-clicks on a row
  this.show_message = function(id, safe, preview)
    {
    if (!id) return;
    
    var add_url = '';
    var action = preview ? 'preview': 'show';
    var target = window;
    
    if (preview && this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      target = window.frames[this.env.contentframe];
      add_url = '&_framed=1';
      }

    if (safe)
      add_url = '&_safe=1';

    // also send search request to get the right messages
    if (this.env.search_request)
      add_url += '&_search='+this.env.search_request;
    var url = '&_action='+action+'&_uid='+id+'&_mbox='+urlencode(this.env.mailbox)+add_url;
    if (action == 'preview' && String(target.location.href).indexOf(url) >= 0)
      this.show_contentframe(true);
    else
      {
      this.set_busy(true, 'loading');
      target.location.href = this.env.comm_path+url;

      // mark as read and change mbox unread counter
      if (action == 'preview' && this.message_list && this.message_list.rows[id] && this.message_list.rows[id].unread)
        {
        this.set_message(id, 'unread', false);
	if (this.env.unread_counts[this.env.mailbox])
	  {
	  this.env.unread_counts[this.env.mailbox] -= 1;
	  this.set_unread_count(this.env.mailbox, this.env.unread_counts[this.env.mailbox], this.env.mailbox == 'INBOX');
	  }
	}
      }
    };

  this.show_contentframe = function(show)
    {
    var frm;
    if (this.env.contentframe && (frm = rcube_find_object(this.env.contentframe)))
      {
      if (!show && window.frames[this.env.contentframe])
        {
        if (window.frames[this.env.contentframe].location.href.indexOf(this.env.blankpage)<0)
          window.frames[this.env.contentframe].location.href = this.env.blankpage;
        }
      else if (!bw.safari && !bw.konq)
        frm.style.display = show ? 'block' : 'none';
      }

    if (!show && this.busy)
      this.set_busy(false);
    };

  // list a specific page
  this.list_page = function(page)
    {
    if (page=='next')
      page = this.env.current_page+1;
    if (page=='last')
      page = this.env.pagecount;
    if (page=='prev' && this.env.current_page>1)
      page = this.env.current_page-1;
    if (page=='first' && this.env.current_page>1)
      page = 1;
      
    if (page > 0 && page <= this.env.pagecount)
      {
      this.env.current_page = page;
      
      if (this.task=='mail')
        this.list_mailbox(this.env.mailbox, page);
      else if (this.task=='addressbook')
        this.list_contacts(this.env.source, page);
      }
    };

  // list messages of a specific mailbox using filter
  this.filter_mailbox = function(filter)
    {
      var search;
      if (this.gui_objects.qsearchbox)
        search = this.gui_objects.qsearchbox.value;
      
      this.message_list.clear();

      // reset vars
      this.env.current_page = 1;
      this.set_busy(true, 'searching');
      this.http_request('search', '_filter='+filter
    	    + (search ? '&_q='+urlencode(search) : '')
    	    + (this.env.mailbox ? '&_mbox='+urlencode(this.env.mailbox) : ''), true);
    }


  // list messages of a specific mailbox
  this.list_mailbox = function(mbox, page, sort)
    {
    this.last_selected = 0;
    var add_url = '';
    var target = window;

    if (!mbox)
      mbox = this.env.mailbox;

    // add sort to url if set
    if (sort)
      add_url += '&_sort=' + sort;

    // also send search request to get the right messages
    if (this.env.search_request)
      add_url += '&_search='+this.env.search_request;
      
    // set page=1 if changeing to another mailbox
    if (!page && mbox != this.env.mailbox)
      {
      page = 1;
      this.env.current_page = page;
      if (this.message_list)
        this.message_list.clear_selection();
      this.show_contentframe(false);
      }
    
    if (mbox != this.env.mailbox || (mbox == this.env.mailbox && !page && !sort))
      add_url += '&_refresh=1';
    
    this.select_folder(mbox, this.env.mailbox);
    this.env.mailbox = mbox;

    // load message list remotely
    if (this.gui_objects.messagelist)
      {
      this.list_mailbox_remote(mbox, page, add_url);
      return;
      }
    
    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      target = window.frames[this.env.contentframe];
      add_url += '&_framed=1';
      }

    // load message list to target frame/window
    if (mbox)
      {
      this.set_busy(true, 'loading');
      target.location.href = this.env.comm_path+'&_mbox='+urlencode(mbox)+(page ? '&_page='+page : '')+add_url;
      }
    };

  // send remote request to load message list
  this.list_mailbox_remote = function(mbox, page, add_url)
    {
    // clear message list first
    this.message_list.clear();

    // send request to server
    var url = '_mbox='+urlencode(mbox)+(page ? '&_page='+page : '');
    this.set_busy(true, 'loading');
    this.http_request('list', url+add_url, true);
    };

  this.expunge_mailbox = function(mbox)
    {
    var lock = false;
    var add_url = '';
    
    // lock interface if it's the active mailbox
    if (mbox == this.env.mailbox)
       {
       lock = true;
       this.set_busy(true, 'loading');
       add_url = '&_reload=1';
       }

    // send request to server
    var url = '_mbox='+urlencode(mbox);
    this.http_post('expunge', url+add_url, lock);
    };

  this.purge_mailbox = function(mbox)
    {
    var lock = false;
    var add_url = '';
    
    if (!confirm(this.get_label('purgefolderconfirm')))
      return false;
    
    // lock interface if it's the active mailbox
    if (mbox == this.env.mailbox)
       {
       lock = true;
       this.set_busy(true, 'loading');
       add_url = '&_reload=1';
       }

    // send request to server
    var url = '_mbox='+urlencode(mbox);
    this.http_post('purge', url+add_url, lock);
    return true;
    };

  // test if purge command is allowed
  this.purge_mailbox_test = function()
  {
    return (this.env.messagecount && (this.env.mailbox == this.env.trash_mailbox || this.env.mailbox == this.env.junk_mailbox 
      || this.env.mailbox.match('^' + RegExp.escape(this.env.trash_mailbox) + RegExp.escape(this.env.delimiter)) 
      || this.env.mailbox.match('^' + RegExp.escape(this.env.junk_mailbox) + RegExp.escape(this.env.delimiter))));
  };

  // set message icon
  this.set_message_icon = function(uid)
  {
    var icn_src;
    var rows = this.message_list.rows;

    if (!rows[uid])
      return false;

    if (rows[uid].deleted && this.env.deletedicon)
      icn_src = this.env.deletedicon;
    else if (rows[uid].replied && this.env.repliedicon)
      {
      if (rows[uid].forwarded && this.env.forwardedrepliedicon)
        icn_src = this.env.forwardedrepliedicon;
      else
        icn_src = this.env.repliedicon;
      }
    else if (rows[uid].forwarded && this.env.forwardedicon)
      icn_src = this.env.forwardedicon;
    else if (rows[uid].unread && this.env.unreadicon)
      icn_src = this.env.unreadicon;
    else if (this.env.messageicon)
      icn_src = this.env.messageicon;
      
    if (icn_src && rows[uid].icon)
      rows[uid].icon.src = icn_src;

    icn_src = '';
    
    if (rows[uid].flagged && this.env.flaggedicon)
      icn_src = this.env.flaggedicon;
    else if (!rows[uid].flagged && this.env.unflaggedicon)
      icn_src = this.env.unflaggedicon;

    if (rows[uid].flagged_icon && icn_src)
      rows[uid].flagged_icon.src = icn_src;
  }

  // set message status
  this.set_message_status = function(uid, flag, status)
    {
    var rows = this.message_list.rows;

    if (!rows[uid]) return false;

    if (flag == 'unread')
      rows[uid].unread = status;
    else if(flag == 'deleted')
      rows[uid].deleted = status;
    else if (flag == 'replied')
      rows[uid].replied = status;
    else if (flag == 'forwarded')
      rows[uid].forwarded = status;
    else if (flag == 'flagged')
      rows[uid].flagged = status;

    this.env.messages[uid] = rows[uid];
    }

  // set message row status, class and icon
  this.set_message = function(uid, flag, status)
    {
    var rows = this.message_list.rows;

    if (!rows[uid]) return false;
    
    if (flag)
      this.set_message_status(uid, flag, status);
    
    if (rows[uid].unread && rows[uid].classname.indexOf('unread')<0)
      {
      rows[uid].classname += ' unread';
      this.set_classname(rows[uid].obj, 'unread', true);
      }
    else if (!rows[uid].unread && rows[uid].classname.indexOf('unread')>=0)
      {
      rows[uid].classname = rows[uid].classname.replace(/\s*unread/, '');
      this.set_classname(rows[uid].obj, 'unread', false);
      }
    
    if (rows[uid].deleted && rows[uid].classname.indexOf('deleted')<0)
      {
      rows[uid].classname += ' deleted';
      this.set_classname(rows[uid].obj, 'deleted', true);
      }
    else if (!rows[uid].deleted && rows[uid].classname.indexOf('deleted')>=0)
      {
      rows[uid].classname = rows[uid].classname.replace(/\s*deleted/, '');
      this.set_classname(rows[uid].obj, 'deleted', false);
      }

    if (rows[uid].flagged && rows[uid].classname.indexOf('flagged')<0)
      {
      rows[uid].classname += ' flagged';
      this.set_classname(rows[uid].obj, 'flagged', true);
      }
    else if (!rows[uid].flagged && rows[uid].classname.indexOf('flagged')>=0)
      {
      rows[uid].classname = rows[uid].classname.replace(/\s*flagged/, '');
      this.set_classname(rows[uid].obj, 'flagged', false);
      }

    this.set_message_icon(uid);
    }

  // move selected messages to the specified mailbox
  this.move_messages = function(mbox)
    {
    // exit if current or no mailbox specified or if selection is empty
    if (!mbox || mbox == this.env.mailbox || (!this.env.uid && (!this.message_list || !this.message_list.get_selection().length)))
      return;

    var lock = false;
    var add_url = '&_target_mbox='+urlencode(mbox)+'&_from='+(this.env.action ? this.env.action : '');

    // show wait message
    if (this.env.action=='show')
      {
      lock = true;
      this.set_busy(true, 'movingmessage');
      }
    else if (!this.env.flag_for_deletion)
      this.show_contentframe(false);

    // Hide message command buttons until a message is selected
    this.enable_command('reply', 'reply-all', 'forward', 'delete', 'mark', 'print', false);

    this._with_selected_messages('moveto', lock, add_url, (this.env.flag_for_deletion ? false : true));
    };

  // delete selected messages from the current mailbox
  this.delete_messages = function()
    {
    var selection = this.message_list ? this.message_list.get_selection() : new Array();

    // exit if no mailbox specified or if selection is empty
    if (!this.env.uid && !selection.length)
        return;

    // if there is a trash mailbox defined and we're not currently in it:
    if (this.env.trash_mailbox && String(this.env.mailbox).toLowerCase() != String(this.env.trash_mailbox).toLowerCase())
      {
      // if shift was pressed delete it immediately
      if (this.message_list && this.message_list.shiftkey)
        {
        if (confirm(this.get_label('deletemessagesconfirm')))
          this.permanently_remove_messages();
        }
      else
        this.move_messages(this.env.trash_mailbox);
      }
    // if there is a trash mailbox defined but we *are* in it:
    else if (this.env.trash_mailbox && String(this.env.mailbox).toLowerCase() == String(this.env.trash_mailbox).toLowerCase())
      this.permanently_remove_messages();
    // if there isn't a defined trash mailbox and the config is set to flag for deletion
    else if (!this.env.trash_mailbox && this.env.flag_for_deletion)
      {
      this.mark_message('delete');
      if(this.env.action=="show")
        this.command('nextmessage','',this);
      else if (selection.length == 1)
        this.message_list.select_next();
      }
    // if there isn't a defined trash mailbox and the config is set NOT to flag for deletion
    else if (!this.env.trash_mailbox) 
      this.permanently_remove_messages();
  };

  // delete the selected messages permanently
  this.permanently_remove_messages = function()
    {
    // exit if no mailbox specified or if selection is empty
    if (!this.env.uid && (!this.message_list || !this.message_list.get_selection().length))
      return;
      
    this.show_contentframe(false);
    this._with_selected_messages('delete', false, '&_from='+(this.env.action ? this.env.action : ''), true);
    };

  // Send a specifc request with UIDs of all selected messages
  // @private
  this._with_selected_messages = function(action, lock, add_url, remove)
  {
    var a_uids = new Array();

    if (this.env.uid)
      a_uids[0] = this.env.uid;
    else
    {
      var selection = this.message_list.get_selection();
      var rows = this.message_list.rows;
      var id;
      for (var n=0; n<selection.length; n++)
        {
        id = selection[n];
        a_uids[a_uids.length] = id;

        if (remove)
          this.message_list.remove_row(id, (n == selection.length-1));
        else
        {
          this.set_message_status(id, 'deleted', true);
          if (this.env.read_when_deleted)
    	    this.set_message_status(id, 'unread', false);
	  this.set_message(id);
        }
      }
    }

    // also send search request to get the right messages 
    if (this.env.search_request) 
      add_url += '&_search='+this.env.search_request;

    // send request to server
    this.http_post(action, '_uid='+a_uids.join(',')+'&_mbox='+urlencode(this.env.mailbox)+add_url, lock);
  };

  // set a specific flag to one or more messages
  this.mark_message = function(flag, uid)
    {
    var a_uids = new Array();
    var r_uids = new Array();
    var selection = this.message_list ? this.message_list.get_selection() : new Array();

    if (uid)
      a_uids[0] = uid;
    else if (this.env.uid)
      a_uids[0] = this.env.uid;
    else if (this.message_list)
      {
      for (var n=0; n<selection.length; n++)
        {
          a_uids[a_uids.length] = selection[n];
        }
      }

    if (!this.message_list)
      r_uids = a_uids;
    else
      for (var id, n=0; n<a_uids.length; n++)
      {
        id = a_uids[n];
        if ((flag=='read' && this.message_list.rows[id].unread) 
            || (flag=='unread' && !this.message_list.rows[id].unread)
            || (flag=='delete' && !this.message_list.rows[id].deleted)
            || (flag=='undelete' && this.message_list.rows[id].deleted)
            || (flag=='flagged' && !this.message_list.rows[id].flagged)
            || (flag=='unflagged' && this.message_list.rows[id].flagged))
        {
          r_uids[r_uids.length] = id;
        }
      }

    // nothing to do
    if (!r_uids.length)
      return;

    switch (flag)
      {
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
    // mark all message rows as read/unread
    for (var i=0; i<a_uids.length; i++)
      this.set_message(a_uids[i], 'unread', (flag=='unread' ? true : false));

    this.http_post('mark', '_uid='+a_uids.join(',')+'&_flag='+flag);
  };

  // set image to flagged or unflagged
  this.toggle_flagged_status = function(flag, a_uids)
  {
    // mark all message rows as flagged/unflagged
    for (var i=0; i<a_uids.length; i++)
      this.set_message(a_uids[i], 'flagged', (flag=='flagged' ? true : false));

    this.http_post('mark', '_uid='+a_uids.join(',')+'&_flag='+flag);
  };
  
  // mark all message rows as deleted/undeleted
  this.toggle_delete_status = function(a_uids)
  {
    var rows = this.message_list ? this.message_list.rows : new Array();
    
    if (a_uids.length==1)
    {
      if (!rows.length || (rows[a_uids[0]] && !rows[a_uids[0]].deleted))
        this.flag_as_deleted(a_uids);
      else
        this.flag_as_undeleted(a_uids);

      return true;
    }
    
    var all_deleted = true;
    for (var i=0; i<a_uids.length; i++)
    {
      uid = a_uids[i];
      if (rows[uid]) {
        if (!rows[uid].deleted)
        {
          all_deleted = false;
          break;
        }
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
    for (var i=0; i<a_uids.length; i++)
      this.set_message(a_uids[i], 'deleted', false);

    this.http_post('mark', '_uid='+a_uids.join(',')+'&_flag=undelete');
    return true;
  };

  this.flag_as_deleted = function(a_uids)
  {
    var add_url = '';
    var r_uids = new Array();
    var rows = this.message_list ? this.message_list.rows : new Array();
    
    for (var i=0; i<a_uids.length; i++)
      {
      uid = a_uids[i];
      if (rows[uid])
        {
	this.set_message(uid, 'deleted', true);
        if (rows[uid].unread)
          r_uids[r_uids.length] = uid;
        }
      }

    if (r_uids.length)
      add_url = '&_ruid='+r_uids.join(',');

    this.http_post('mark', '_uid='+a_uids.join(',')+'&_flag=delete'+add_url);
    return true;  
  };

  // flag as read without mark request (called from backend)
  // argument should be a coma-separated list of uids
  this.flag_deleted_as_read = function(uids)
  {
    var icn_src;
    var rows = this.message_list ? this.message_list.rows : new Array();
    var str = String(uids);
    var a_uids = new Array();

    a_uids = str.split(',');

    for (var uid, i=0; i<a_uids.length; i++)
      {
      uid = a_uids[i];
      if (rows[uid])
        this.set_message(uid, 'unread', false);
      }
  };
  
  
  /*********************************************************/
  /*********           login form methods          *********/
  /*********************************************************/

  // handler for keyboard events on the _user field
  this.login_user_keyup = function(e)
  {
    var key = rcube_event.get_keycode(e);
    var elm;

    // enter
    if ((key==13) && (elm = rcube_find_object('_pass')))
    {
      elm.focus();
      return false;
    }
  };


  /*********************************************************/
  /*********        message compose methods        *********/
  /*********************************************************/
  
  // checks the input fields before sending a message
  this.check_compose_input = function()
    {
    // check input fields
    var input_to = rcube_find_object('_to');
    var input_cc = rcube_find_object('_cc');
    var input_bcc = rcube_find_object('_bcc');
    var input_from = rcube_find_object('_from');
    var input_subject = rcube_find_object('_subject');
    var input_message = rcube_find_object('_message');

    // check sender (if have no identities)
    if (input_from.type == 'text' && !rcube_check_email(input_from.value, true))
      {
      alert(this.get_label('nosenderwarning'));
      input_from.focus();
      return false;
      }

    // check for empty recipient
    var recipients = input_to.value ? input_to.value : (input_cc.value ? input_cc.value : input_bcc.value);
    if (!rcube_check_email(recipients.replace(/^\s+/, '').replace(/[\s,;]+$/, ''), true))
      {
      alert(this.get_label('norecipientwarning'));
      input_to.focus();
      return false;
      }

    // display localized warning for missing subject
    if (input_subject && input_subject.value == '')
      {
      var subject = prompt(this.get_label('nosubjectwarning'), this.get_label('nosubject'));

      // user hit cancel, so don't send
      if (!subject && subject !== '')
        {
        input_subject.focus();
        return false;
        }
      else
        {
        input_subject.value = subject ? subject : this.get_label('nosubject');            
        }
      }

    // check for empty body
    if ((!window.tinyMCE || !tinyMCE.get('compose-body')) && input_message.value == '' && !confirm(this.get_label('nobodywarning')))
      {
      input_message.focus();
      return false;
      }
    else if (window.tinyMCE && tinyMCE.get('compose-body') && !tinyMCE.get('compose-body').getContent() && !confirm(this.get_label('nobodywarning')))
      {
      tinyMCE.get('compose-body').focus();
      return false;
      }

    // Apply spellcheck changes if spell checker is active
    this.stop_spellchecking();

    return true;
    };

  this.stop_spellchecking = function()
    {
    if (this.env.spellcheck && !this.spellcheck_ready) {
      exec_event(this.env.spellcheck.check_link, 'click');
      this.set_spellcheck_state('ready');
      }
    };

  this.display_spellcheck_controls = function(vis)
    {
    if (this.env.spellcheck) {
      // stop spellchecking process
      if (!vis)
	this.stop_spellchecking();
			      
      this.env.spellcheck.check_link.style.visibility = vis ? 'visible' : 'hidden';
      this.env.spellcheck.switch_lan_pic.style.visibility = vis ? 'visible' : 'hidden';
      }
    };

  this.set_spellcheck_state = function(s)
    {
    this.spellcheck_ready = (s=='check_spelling' || s=='ready');
    this.enable_command('spellcheck', this.spellcheck_ready);
    };

  this.set_draft_id = function(id)
    {
    var f;
    if (f = rcube_find_object('_draft_saveid'))
      f.value = id;
    };

  this.auto_save_start = function()
    {
    if (this.env.draft_autosave)
      this.save_timer = self.setTimeout(function(){ ref.command("savedraft"); }, this.env.draft_autosave * 1000);

    // Unlock interface now that saving is complete
    this.busy = false;
    };

  this.compose_field_hash = function(save)
    {
    // check input fields
    var input_to = rcube_find_object('_to');
    var input_cc = rcube_find_object('_cc');
    var input_bcc = rcube_find_object('_bcc');
    var input_subject = rcube_find_object('_subject');
    var editor, input_message;
    var str = '';
    
    if (input_to && input_to.value)
      str += input_to.value+':';
    if (input_cc && input_cc.value)
      str += input_cc.value+':';
    if (input_bcc && input_bcc.value)
      str += input_bcc.value+':';
    if (input_subject && input_subject.value)
      str += input_subject.value+':';
    
    if (editor = tinyMCE.get('compose-body'))
      str += editor.getContent();
    else
      {
      input_message = rcube_find_object('_message');
      str += input_message.value;
      }
    
    if (save)
      this.cmp_hash = str;
    
    return str;
    };
    
  this.change_identity = function(obj)
    {
    if (!obj || !obj.options)
      return false;

    var id = obj.options[obj.selectedIndex].value;
    var input_message = rcube_find_object('_message');
    var message = input_message ? input_message.value : '';
    var is_html = (rcube_find_object('_is_html').value == '1');
    var sig, p;

    if (!this.env.identity)
      this.env.identity = id
  
    if (!is_html)
      {
      // remove the 'old' signature
      if (this.env.identity && this.env.signatures && this.env.signatures[this.env.identity])
        {
        if (this.env.signatures[this.env.identity]['is_html'])
          sig = this.env.signatures[this.env.identity]['plain_text'];
        else
	  sig = this.env.signatures[this.env.identity]['text'];
        
	if (sig.indexOf('-- ')!=0)
          sig = '-- \n'+sig;

        p = message.lastIndexOf(sig);
        if (p>=0)
          message = message.substring(0, p-1) + message.substring(p+sig.length, message.length);
        }

      message = message.replace(/[\r\n]+$/, '');
      
      // add the new signature string
      if (this.env.signatures && this.env.signatures[id])
        {
        sig = this.env.signatures[id]['text'];
        if (this.env.signatures[id]['is_html'])
          {
          sig = this.env.signatures[id]['plain_text'];
          }
        if (sig.indexOf('-- ')!=0)
          sig = '-- \n'+sig;
        message += '\n\n'+sig;
        }
      }
    else
      {
      var editor = tinyMCE.get('compose-body');

      if (this.env.signatures)
        {
        // Append the signature as a div within the body
        var sigElem = editor.dom.get('_rc_sig');
	var newsig = '';
	var htmlsig = true;

        if (!sigElem)
          {
	  // add empty line before signature on IE
	  if (bw.ie)
            editor.getBody().appendChild(editor.getDoc().createElement('br'));

	  sigElem = editor.getDoc().createElement('div');
          sigElem.setAttribute('id', '_rc_sig');
          editor.getBody().appendChild(sigElem);
          }

	if (this.env.signatures[id])
	  {
	  newsig = this.env.signatures[id]['text'];
	  htmlsig = this.env.signatures[id]['is_html'];
        
	  if (newsig) {
	    if (htmlsig && this.env.signatures[id]['plain_text'].indexOf('-- ')!=0)
              newsig = '<p>-- </p>' + newsig;
	    else if (!htmlsig && newsig.indexOf('-- ')!=0)
              newsig = '-- \n' + newsig;
	    }
	  }

        if (htmlsig)
          sigElem.innerHTML = newsig;
        else
          sigElem.innerHTML = '<pre>' + newsig + '</pre>';
        }
      }

    if (input_message)
      input_message.value = message;

    this.env.identity = id;
    return true;
    };

  this.show_attachment_form = function(a)
    {
    if (!this.gui_objects.uploadbox)
      return false;
      
    var elm, list;
    if (elm = this.gui_objects.uploadbox)
      {
      if (a &&  (list = this.gui_objects.attachmentlist))
        {
        var pos = rcube_get_object_pos(list);
        var left = pos.x;
        var top = pos.y + list.offsetHeight + 10;
      
        elm.style.top = top+'px';
        elm.style.left = left+'px';
        }
      
      elm.style.visibility = a ? 'visible' : 'hidden';
      }
      
    // clear upload form
	try {
      if (!a && this.gui_objects.attachmentform != this.gui_objects.messageform)
      	this.gui_objects.attachmentform.reset();
	}
	catch(e){}  // ignore errors
    
    return true;  
    };

  // upload attachment file
  this.upload_file = function(form)
    {
    if (!form)
      return false;
      
    // get file input fields
    var send = false;
    for (var n=0; n<form.elements.length; n++)
      if (form.elements[n].type=='file' && form.elements[n].value)
        {
        send = true;
        break;
        }
    
    // create hidden iframe and post upload form
    if (send)
      {
      var ts = new Date().getTime();
      var frame_name = 'rcmupload'+ts;

      // have to do it this way for IE
      // otherwise the form will be posted to a new window
      if(document.all)
        {
        var html = '<iframe name="'+frame_name+'" src="program/blank.gif" style="width:0;height:0;visibility:hidden;"></iframe>';
        document.body.insertAdjacentHTML('BeforeEnd',html);
        }
      else  // for standards-compilant browsers
        {
        var frame = document.createElement('IFRAME');
        frame.name = frame_name;
        frame.style.border = 'none';
        frame.style.width = 0;
        frame.style.height = 0;
        frame.style.visibility = 'hidden';
        document.body.appendChild(frame);
        }

      form.target = frame_name;
      form.action = this.env.comm_path+'&_action=upload';
      form.setAttribute('enctype', 'multipart/form-data');
      form.submit();
      }
    
    // set reference to the form object
    this.gui_objects.attachmentform = form;
    return true;
    };

  // add file name to attachment list
  // called from upload page
  this.add2attachment_list = function(name, content)
    {
    if (!this.gui_objects.attachmentlist)
      return false;
      
    var li = document.createElement('LI');
    li.id = name;
    li.innerHTML = content;
    this.gui_objects.attachmentlist.appendChild(li);
    return true;
    };

  this.remove_from_attachment_list = function(name)
    {
    if (!this.gui_objects.attachmentlist)
      return false;

    var list = this.gui_objects.attachmentlist.getElementsByTagName("li");
    for (i=0;i<list.length;i++)
      if (list[i].id == name)
        this.gui_objects.attachmentlist.removeChild(list[i]);
    };

  this.remove_attachment = function(name)
    {
    if (name)
      this.http_post('remove-attachment', '_file='+urlencode(name));

    return true;
    };

  // send remote request to add a new contact
  this.add_contact = function(value)
    {
    if (value)
      this.http_post('addcontact', '_address='+value);
    
    return true;
    };

  // send remote request to search mail or contacts
  this.qsearch = function(value, addurl)
    {
    if (value != '')
      {
      if (this.message_list)
        this.message_list.clear();
      else if (this.contact_list) {
        this.contact_list.clear(true);
        this.show_contentframe(false);
        }

      if (this.gui_objects.search_filter)
      addurl = '&_filter=' + this.gui_objects.search_filter.value;

      // reset vars
      this.env.current_page = 1;
      this.set_busy(true, 'searching');
      this.http_request('search', '_q='+urlencode(value)
        + (this.env.mailbox ? '&_mbox='+urlencode(this.env.mailbox) : '')
        + (this.env.source ? '&_source='+urlencode(this.env.source) : '')
        + (addurl ? addurl : ''), true);
      }
    return true;
    };

  // reset quick-search form
  this.reset_qsearch = function()
    {
    if (this.gui_objects.qsearchbox)
      this.gui_objects.qsearchbox.value = '';
      
    this.env.search_request = null;
    return true;
    };

  this.sent_successfully = function(type, msg)
    {
    this.list_mailbox();
    this.display_message(msg, type, true);
    }


  /*********************************************************/
  /*********     keyboard live-search methods      *********/
  /*********************************************************/

  // handler for keyboard events on address-fields
  this.ksearch_keypress = function(e, obj)
  {
    if (this.ksearch_timer)
      clearTimeout(this.ksearch_timer);

    var highlight;
    var key = rcube_event.get_keycode(e);
    var mod = rcube_event.get_modifier(e);

    switch (key)
      {
      case 38:  // key up
      case 40:  // key down
        if (!this.ksearch_pane)
          break;
          
        var dir = key==38 ? 1 : 0;
        
        highlight = document.getElementById('rcmksearchSelected');
        if (!highlight)
          highlight = this.ksearch_pane.ul.firstChild;
        
        if (highlight)
          this.ksearch_select(dir ? highlight.previousSibling : highlight.nextSibling);

        return rcube_event.cancel(e);

      case 9:  // tab
        if(mod == SHIFT_KEY)
          break;

      case 13:  // enter
        if (this.ksearch_selected===null || !this.ksearch_input || !this.ksearch_value)
          break;

        // insert selected address and hide ksearch pane
        this.insert_recipient(this.ksearch_selected);
        this.ksearch_hide();

        return rcube_event.cancel(e);

      case 27:  // escape
        this.ksearch_hide();
        break;
      
      case 37:  // left
      case 39:  // right
        if (mod != SHIFT_KEY)
	  return;
      }

    // start timer
    this.ksearch_timer = window.setTimeout(function(){ ref.ksearch_get_results(); }, 200);
    this.ksearch_input = obj;
    
    return true;
  };
  
  this.ksearch_select = function(node)
  {
    var current = document.getElementById('rcmksearchSelected');
    if (current && node) {
      current.removeAttribute('id');
      this.set_classname(current, 'selected', false);
    }

    if (node) {
      node.setAttribute('id', 'rcmksearchSelected');
      this.set_classname(node, 'selected', true);
      this.ksearch_selected = node._rcm_id;
    }
  };

  this.insert_recipient = function(id)
  {
    if (!this.env.contacts[id] || !this.ksearch_input)
      return;
    
    // get cursor pos
    var inp_value = this.ksearch_input.value.toLowerCase();
    var cpos = this.get_caret_pos(this.ksearch_input);
    var p = inp_value.lastIndexOf(this.ksearch_value, cpos);

    // replace search string with full address
    var pre = this.ksearch_input.value.substring(0, p);
    var end = this.ksearch_input.value.substring(p+this.ksearch_value.length, this.ksearch_input.value.length);
    var insert  = this.env.contacts[id]+', ';
    this.ksearch_input.value = pre + insert + end;

    // set caret to insert pos
    cpos = p+insert.length;
    if (this.ksearch_input.setSelectionRange)
      this.ksearch_input.setSelectionRange(cpos, cpos);
  };

  // address search processor
  this.ksearch_get_results = function()
  {
    var inp_value = this.ksearch_input ? this.ksearch_input.value : null;
    if (inp_value === null)
      return;
      
    if (this.ksearch_pane && this.ksearch_pane.visible)
      this.ksearch_pane.show(0);

    // get string from current cursor pos to last comma
    var cpos = this.get_caret_pos(this.ksearch_input);
    var p = inp_value.lastIndexOf(',', cpos-1);
    var q = inp_value.substring(p+1, cpos);

    // trim query string
    q = q.replace(/(^\s+|\s+$)/g, '').toLowerCase();

    // Don't (re-)search if string is empty or if the last results are still active
    if (!q.length || q == this.ksearch_value)
      return;

    this.ksearch_value = q;
    
    this.display_message(this.get_label('searching'), 'loading', true);
    this.http_post('autocomplete', '_search='+q);
  };

  this.ksearch_query_results = function(results, search)
  {
    // ignore this outdated search response
    if (search != this.ksearch_value)
      return;
      
    this.hide_message();
    this.env.contacts = results ? results : [];
    this.ksearch_display_results(this.env.contacts);
  };

  this.ksearch_display_results = function (a_results)
  {
    // display search results
    if (a_results.length && this.ksearch_input) {
      var p, ul, li;
      
      // create results pane if not present
      if (!this.ksearch_pane) {
        ul = document.createElement('UL');
        this.ksearch_pane = new rcube_layer('rcmKSearchpane', {vis:0, zindex:30000});
        this.ksearch_pane.elm.appendChild(ul);
        this.ksearch_pane.ul = ul;
      }
      else
        ul = this.ksearch_pane.ul;

      // remove all search results
      ul.innerHTML = '';
            
      // add each result line to list
      for (i=0; i<a_results.length; i++) {
        li = document.createElement('LI');
        li.innerHTML = a_results[i].replace(new RegExp('('+this.ksearch_value+')', 'ig'), '##$1%%').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/##([^%]+)%%/g, '<b>$1</b>');
        li.onmouseover = function(){ ref.ksearch_select(this); };
        li.onmouseup = function(){ ref.ksearch_click(this) };
        li._rcm_id = i;
        ul.appendChild(li);
      }

      // select the first
      ul.firstChild.setAttribute('id', 'rcmksearchSelected');
      this.set_classname(ul.firstChild, 'selected', true);
      this.ksearch_selected = 0;

      // move the results pane right under the input box and make it visible
      var pos = rcube_get_object_pos(this.ksearch_input);
      this.ksearch_pane.move(pos.x, pos.y+this.ksearch_input.offsetHeight);
      this.ksearch_pane.show(1);
    }
    // hide results pane
    else
      this.ksearch_hide();
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

    this.ksearch_value = '';
    this.ksearch_input = null;
    
    this.ksearch_hide();
    };


  this.ksearch_hide = function()
    {
    this.ksearch_selected = null;
    
    if (this.ksearch_pane)
      this.ksearch_pane.show(0);
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

      var id, frame, ref = this;
      if (id = list.get_single_selection())
        this.preview_timer = window.setTimeout(function(){ ref.load_contact(id, 'show'); }, 200);
      else if (this.env.contentframe)
        this.show_contentframe(false);

      this.enable_command('compose', list.selection.length > 0);
      this.enable_command('edit', (id && this.env.address_sources && !this.env.address_sources[this.env.source].readonly) ? true : false);
      this.enable_command('delete', list.selection.length && this.env.address_sources && !this.env.address_sources[this.env.source].readonly);

      return false;
    };

  this.list_contacts = function(src, page)
    {
    var add_url = '';
    var target = window;
    
    if (!src)
      src = this.env.source;
    
    if (page && this.current_page==page && src == this.env.source)
      return false;
      
    if (src != this.env.source)
      {
      page = 1;
      this.env.current_page = page;
      this.reset_qsearch();
      }

    this.select_folder(src, this.env.source);
    this.env.source = src;

    // load contacts remotely
    if (this.gui_objects.contactslist)
      {
      this.list_contacts_remote(src, page);
      return;
      }

    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      target = window.frames[this.env.contentframe];
      add_url = '&_framed=1';
      }

    // also send search request to get the correct listing
    if (this.env.search_request)
      add_url += '&_search='+this.env.search_request;

    this.set_busy(true, 'loading');
    target.location.href = this.env.comm_path+(src ? '&_source='+urlencode(src) : '')+(page ? '&_page='+page : '')+add_url;
    };

  // send remote request to load contacts list
  this.list_contacts_remote = function(src, page)
    {
    // clear message list first
    this.contact_list.clear(true);
    this.show_contentframe(false);
    this.enable_command('delete', 'compose', false);

    // send request to server
    var url = (src ? '_source='+urlencode(src) : '') + (page ? (src?'&':'') + '_page='+page : '');
    this.env.source = src;
    
    // also send search request to get the right messages 
    if (this.env.search_request) 
      url += '&_search='+this.env.search_request;

    this.set_busy(true, 'loading');
    this.http_request('list', url, true);
    };

  // load contact record
  this.load_contact = function(cid, action, framed)
    {
    var add_url = '';
    var target = window;
    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      add_url = '&_framed=1';
      target = window.frames[this.env.contentframe];
      this.show_contentframe(true);
      }
    else if (framed)
      return false;
      
    if (action && (cid || action=='add') && !this.drag_active)
      {
      this.set_busy(true);
      target.location.href = this.env.comm_path+'&_action='+action+'&_source='+urlencode(this.env.source)+'&_cid='+urlencode(cid) + add_url;
      }
    return true;
    };

  // copy a contact to the specified target (group or directory)
  this.copy_contact = function(cid, to)
    {
    if (!cid)
      cid = this.contact_list.get_selection().join(',');

    if (to != this.env.source && cid && this.env.address_sources[to] && !this.env.address_sources[to].readonly)
      this.http_post('copy', '_cid='+urlencode(cid)+'&_source='+urlencode(this.env.source)+'&_to='+urlencode(to));
    };


  this.delete_contacts = function()
    {
    // exit if no mailbox specified or if selection is empty
    var selection = this.contact_list.get_selection();
    if (!(selection.length || this.env.cid) || !confirm(this.get_label('deletecontactconfirm')))
      return;
      
    var a_cids = new Array();
    var qs = '';

    if (this.env.cid)
      a_cids[a_cids.length] = this.env.cid;
    else
      {
      var id;
      for (var n=0; n<selection.length; n++)
        {
        id = selection[n];
        a_cids[a_cids.length] = id;
        this.contact_list.remove_row(id, (n == selection.length-1));
        }

      // hide content frame if we delete the currently displayed contact
      if (selection.length == 1)
        this.show_contentframe(false);
      }

    // also send search request to get the right records from the next page
    if (this.env.search_request) 
      qs += '&_search='+this.env.search_request;

    // send request to server
    this.http_post('delete', '_cid='+urlencode(a_cids.join(','))+'&_source='+urlencode(this.env.source)+'&_from='+(this.env.action ? this.env.action : '')+qs);
    return true;
    };

  // update a contact record in the list
  this.update_contact_row = function(cid, cols_arr)
    {
    var row;
    if (this.contact_list.rows[cid] && (row = this.contact_list.rows[cid].obj))
      {
      for (var c=0; c<cols_arr.length; c++)
        if (row.cells[c])
          row.cells[c].innerHTML = cols_arr[c];

      return true;
      }

    return false;
    };


  /*********************************************************/
  /*********        user settings methods          *********/
  /*********************************************************/

  this.init_subscription_list = function()
    {
    var p = this;
    this.subscription_list = new rcube_list_widget(this.gui_objects.subscriptionlist, {multiselect:false, draggable:true, keyboard:false, toggleselect:true});
    this.subscription_list.addEventListener('select', function(o){ p.subscription_select(o); });
    this.subscription_list.addEventListener('dragstart', function(o){ p.drag_active = true; });
    this.subscription_list.addEventListener('dragend', function(o){ p.subscription_move_folder(o); });
    this.subscription_list.row_init = function (row)
      {
      var anchors = row.obj.getElementsByTagName('A');
      if (anchors[0])
        anchors[0].onclick = function() { p.rename_folder(row.id); return false; };
      if (anchors[1])
        anchors[1].onclick = function() { p.delete_folder(row.id); return false; };
      row.obj.onmouseover = function() { p.focus_subscription(row.id); };
      row.obj.onmouseout = function() { p.unfocus_subscription(row.id); };
      }
    this.subscription_list.init();
    }

  this.identity_select = function(list)
    {
    var id;
    if (id = list.get_single_selection())
      this.load_identity(id, 'edit-identity');
    };

  // load contact record
  this.load_identity = function(id, action)
    {
    if (action=='edit-identity' && (!id || id==this.env.iid))
      return false;

    var add_url = '';
    var target = window;
    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      add_url = '&_framed=1';
      target = window.frames[this.env.contentframe];
      document.getElementById(this.env.contentframe).style.visibility = 'inherit';
      }

    if (action && (id || action=='add-identity'))
      {
      this.set_busy(true);
      target.location.href = this.env.comm_path+'&_action='+action+'&_iid='+id+add_url;
      }
    return true;
    };

  this.delete_identity = function(id)
    {
    // exit if no mailbox specified or if selection is empty
    var selection = this.identity_list.get_selection();
    if (!(selection.length || this.env.iid))
      return;
    
    if (!id)
      id = this.env.iid ? this.env.iid : selection[0];

    // if (this.env.framed && id)
    this.goto_url('delete-identity', '_iid='+id, true);
    return true;
    };

  this.focus_subscription = function(id)
    {
    var row, folder;
    var reg = RegExp('['+RegExp.escape(this.env.delimiter)+']?[^'+RegExp.escape(this.env.delimiter)+']+$');

    if (this.drag_active && this.env.folder && (row = document.getElementById(id)))
      if (this.env.subscriptionrows[id] &&
          (folder = this.env.subscriptionrows[id][0]))
        {
        if (this.check_droptarget(folder) &&
    	    !this.env.subscriptionrows[this.get_folder_row_id(this.env.folder)][2] &&    
	    (folder != this.env.folder.replace(reg, '')) &&
            (!folder.match(new RegExp('^'+RegExp.escape(this.env.folder+this.env.delimiter)))))
          {
          this.set_env('dstfolder', folder);
          this.set_classname(row, 'droptarget', true);
          }
        }
      else if (this.env.folder.match(new RegExp(RegExp.escape(this.env.delimiter))))
        {
        this.set_env('dstfolder', this.env.delimiter);
        this.set_classname(this.subscription_list.frame, 'droptarget', true);
        }
    }

  this.unfocus_subscription = function(id)
    {
      var row;
      this.set_env('dstfolder', null);
      if (this.env.subscriptionrows[id] &&
          (row = document.getElementById(id)))
        this.set_classname(row, 'droptarget', false);
      else
        this.set_classname(this.subscription_list.frame, 'droptarget', false);
    }

  this.subscription_select = function(list)
    {
    var id, folder;
    if ((id = list.get_single_selection()) &&
        this.env.subscriptionrows['rcmrow'+id] &&
        (folder = this.env.subscriptionrows['rcmrow'+id][0]))
      this.set_env('folder', folder);
    else
      this.set_env('folder', null);
      
    if (this.gui_objects.createfolderhint)
      this.gui_objects.createfolderhint.innerHTML = this.env.folder ? this.get_label('addsubfolderhint') : '';
    };

  this.subscription_move_folder = function(list)
    {
    var reg = RegExp('['+RegExp.escape(this.env.delimiter)+']?[^'+RegExp.escape(this.env.delimiter)+']+$');
    if (this.env.folder && this.env.dstfolder && (this.env.dstfolder != this.env.folder) &&
        (this.env.dstfolder != this.env.folder.replace(reg, '')))
      {
      var reg = new RegExp('[^'+RegExp.escape(this.env.delimiter)+']*['+RegExp.escape(this.env.delimiter)+']', 'g');
      var basename = this.env.folder.replace(reg, '');
      var newname = this.env.dstfolder==this.env.delimiter ? basename : this.env.dstfolder+this.env.delimiter+basename;

      this.set_busy(true, 'foldermoving');
      this.http_post('rename-folder', '_folder_oldname='+urlencode(this.env.folder)+'&_folder_newname='+urlencode(newname), true);
      }
    this.drag_active = false;
    this.unfocus_subscription(this.get_folder_row_id(this.env.dstfolder));
    };

  // tell server to create and subscribe a new mailbox
  this.create_folder = function(name)
    {
    if (this.edit_folder)
      this.reset_folder_rename();

    var form;
    if ((form = this.gui_objects.editform) && form.elements['_folder_name'])
      {
      name = form.elements['_folder_name'].value;

      if (name.indexOf(this.env.delimiter)>=0)
        {
        alert(this.get_label('forbiddencharacter')+' ('+this.env.delimiter+')');
        return false;
        }

      if (this.env.folder && name != '')
        name = this.env.folder+this.env.delimiter+name;

      this.set_busy(true, 'foldercreating');
      this.http_post('create-folder', '_name='+urlencode(name), true);
      }
    else if (form.elements['_folder_name'])
      form.elements['_folder_name'].focus();
    };

  // start renaming the mailbox name.
  // this will replace the name string with an input field
  this.rename_folder = function(id)
    {
    var temp, row, form;

    // reset current renaming
    if (temp = this.edit_folder)
      {
      this.reset_folder_rename();
      if (temp == id)
        return;
      }

    if (id && this.env.subscriptionrows[id] && (row = document.getElementById(id)))
      {
      var reg = new RegExp('.*['+RegExp.escape(this.env.delimiter)+']');
      this.name_input = document.createElement('INPUT');
      this.name_input.value = this.env.subscriptionrows[id][0].replace(reg, '');
      this.name_input.style.width = '100%';

      reg = new RegExp('['+RegExp.escape(this.env.delimiter)+']?[^'+RegExp.escape(this.env.delimiter)+']+$');
      this.name_input.__parent = this.env.subscriptionrows[id][0].replace(reg, '');
      this.name_input.onkeypress = function(e){ rcmail.name_input_keypress(e); };
      
      row.cells[0].replaceChild(this.name_input, row.cells[0].firstChild);
      this.edit_folder = id;
      this.name_input.select();
      
      if (form = this.gui_objects.editform)
        form.onsubmit = function(){ return false; };
      }
    };

  // remove the input field and write the current mailbox name to the table cell
  this.reset_folder_rename = function()
    {
    var cell = this.name_input ? this.name_input.parentNode : null;

    if (cell && this.edit_folder && this.env.subscriptionrows[this.edit_folder])
      cell.innerHTML = this.env.subscriptionrows[this.edit_folder][1];
      
    this.edit_folder = null;
    };

  // handler for keyboard events on the input field
  this.name_input_keypress = function(e)
    {
    var key = rcube_event.get_keycode(e);

    // enter
    if (key==13)
      {
      var newname = this.name_input ? this.name_input.value : null;
      if (this.edit_folder && newname)
        {
        if (newname.indexOf(this.env.delimiter)>=0)
          {
          alert(this.get_label('forbiddencharacter')+' ('+this.env.delimiter+')');
          return false;
          }

        if (this.name_input.__parent)
          newname = this.name_input.__parent + this.env.delimiter + newname;

        this.set_busy(true, 'folderrenaming');
        this.http_post('rename-folder', '_folder_oldname='+urlencode(this.env.subscriptionrows[this.edit_folder][0])+'&_folder_newname='+urlencode(newname), true);
        }
      }
    // escape
    else if (key==27)
      this.reset_folder_rename();
    };

  // delete a specific mailbox with all its messages
  this.delete_folder = function(id)
    {
    var folder = this.env.subscriptionrows[id][0];

    if (this.edit_folder)
      this.reset_folder_rename();

    if (folder && confirm(this.get_label('deletefolderconfirm')))
      {
      this.set_busy(true, 'folderdeleting');
      this.http_post('delete-folder', '_mboxes='+urlencode(folder), true);
      this.set_env('folder', null);

      if (this.gui_objects.createfolderhint)
        this.gui_objects.createfolderhint.innerHTML = '';
      }
    };

  // add a new folder to the subscription list by cloning a folder row
  this.add_folder_row = function(name, display_name, replace, before)
    {
    if (!this.gui_objects.subscriptionlist)
      return false;

    // find not protected folder    
    for (var refid in this.env.subscriptionrows)
      if (this.env.subscriptionrows[refid]!=null && !this.env.subscriptionrows[refid][2])
        break;

    var refrow, form;
    var tbody = this.gui_objects.subscriptionlist.tBodies[0];
    var id = 'rcmrow'+(tbody.childNodes.length+1);
    var selection = this.subscription_list.get_single_selection();
    
    if (replace && replace.id)
    {
      id = replace.id;
      refid = replace.id;
    }

    if (!id || !(refrow = document.getElementById(refid)))
      {
      // Refresh page if we don't have a table row to clone
      this.goto_url('folders');
      }
    else
      {
      // clone a table row if there are existing rows
      var row = this.clone_table_row(refrow);
      row.id = id;

      if (before && (before = this.get_folder_row_id(before)))
        tbody.insertBefore(row, document.getElementById(before));
      else
        tbody.appendChild(row);
      
      if (replace)
        tbody.removeChild(replace);
      }

    // add to folder/row-ID map
    this.env.subscriptionrows[row.id] = [name, display_name, 0];

    // set folder name
    row.cells[0].innerHTML = display_name;
    
    // set messages count to zero
    if (!replace)
      row.cells[1].innerHTML = '*';
    
    if (!replace && row.cells[2] && row.cells[2].firstChild.tagName=='INPUT')
      {
      row.cells[2].firstChild.value = name;
      row.cells[2].firstChild.checked = true;
      }
    
    // add new folder to rename-folder list and clear input field
    if (!replace && (form = this.gui_objects.editform))
      {
      if (form.elements['_folder_oldname'])
        form.elements['_folder_oldname'].options[form.elements['_folder_oldname'].options.length] = new Option(name,name);
      if (form.elements['_folder_name'])
        form.elements['_folder_name'].value = ''; 
      }

    this.init_subscription_list();
    if (selection && document.getElementById('rcmrow'+selection))
      this.subscription_list.select_row(selection);

    if (document.getElementById(id).scrollIntoView)
      document.getElementById(id).scrollIntoView();
    };

  // replace an existing table row with a new folder line
  this.replace_folder_row = function(oldfolder, newfolder, display_name, before)
    {
    var id = this.get_folder_row_id(oldfolder);
    var row = document.getElementById(id);
    
    // replace an existing table row (if found)
    this.add_folder_row(newfolder, display_name, row, before);
    
    // rename folder in rename-folder dropdown
    var form, elm;
    if ((form = this.gui_objects.editform) && (elm = form.elements['_folder_oldname']))
      {
      for (var i=0;i<elm.options.length;i++)
        {
        if (elm.options[i].value == oldfolder)
          {
          elm.options[i].text = display_name;
          elm.options[i].value = newfolder;
          break;
          }
        }

      form.elements['_folder_newname'].value = '';
      }
    };

  // remove the table row of a specific mailbox from the table
  // (the row will not be removed, just hidden)
  this.remove_folder_row = function(folder)
    {
    var row;
    var id = this.get_folder_row_id(folder);
    if (id && (row = document.getElementById(id)))
      row.style.display = 'none';

    // remove folder from rename-folder list
    var form;
    if ((form = this.gui_objects.editform) && form.elements['_folder_oldname'])
      {
      for (var i=0;i<form.elements['_folder_oldname'].options.length;i++)
        {
        if (form.elements['_folder_oldname'].options[i].value == folder) 
          {
          form.elements['_folder_oldname'].options[i] = null;
          break;
          }
        }
      }
    
    if (form && form.elements['_folder_newname'])
      form.elements['_folder_newname'].value = '';
    };

  this.subscribe_folder = function(folder)
    {
    if (folder)
      this.http_post('subscribe', '_mbox='+urlencode(folder));
    };

  this.unsubscribe_folder = function(folder)
    {
    if (folder)
      this.http_post('unsubscribe', '_mbox='+urlencode(folder));
    };
    
  // helper method to find a specific mailbox row ID
  this.get_folder_row_id = function(folder)
    {
    for (var id in this.env.subscriptionrows)
      if (this.env.subscriptionrows[id] && this.env.subscriptionrows[id][0] == folder)
        break;
        
    return id;
    };

  // duplicate a specific table row
  this.clone_table_row = function(row)
    {
    var cell, td;
    var new_row = document.createElement('TR');
    for(var n=0; n<row.cells.length; n++)
      {
      cell = row.cells[n];
      td = document.createElement('TD');

      if (cell.className)
        td.className = cell.className;
      if (cell.align)
        td.setAttribute('align', cell.align);
        
      td.innerHTML = cell.innerHTML;
      new_row.appendChild(td);
      }
    
    return new_row;
    };


  /*********************************************************/
  /*********           GUI functionality           *********/
  /*********************************************************/

  // eable/disable buttons for page shifting
  this.set_page_buttons = function()
    {
    this.enable_command('nextpage', (this.env.pagecount > this.env.current_page));
    this.enable_command('lastpage', (this.env.pagecount > this.env.current_page));
    this.enable_command('previouspage', (this.env.current_page > 1));
    this.enable_command('firstpage', (this.env.current_page > 1));
    }

  // set button to a specific state
  this.set_button = function(command, state)
    {
    var a_buttons = this.buttons[command];
    var button, obj;

    if(!a_buttons || !a_buttons.length)
      return false;

    for(var n=0; n<a_buttons.length; n++)
      {
      button = a_buttons[n];
      obj = document.getElementById(button.id);

      // get default/passive setting of the button
      if (obj && button.type=='image' && !button.status) {
        button.pas = obj._original_src ? obj._original_src : obj.src;
        // respect PNG fix on IE browsers
        if (obj.runtimeStyle && obj.runtimeStyle.filter && obj.runtimeStyle.filter.match(/src=['"]([^'"]+)['"]/))
          button.pas = RegExp.$1;
      }
      else if (obj && !button.status)
        button.pas = String(obj.className);

      // set image according to button state
      if (obj && button.type=='image' && button[state])
        {
        button.status = state;        
        obj.src = button[state];
        }
      // set class name according to button state
      else if (obj && typeof(button[state])!='undefined')
        {
        button.status = state;        
        obj.className = button[state];        
        }
      // disable/enable input buttons
      if (obj && button.type=='input')
        {
        button.status = state;
        obj.disabled = !state;
        }
      }
    };

  // display a specific alttext
  this.set_alttext = function(command, label)
    {
      if (!this.buttons[command] || !this.buttons[command].length)
        return;
      
      var button, obj, link;
      for (var n=0; n<this.buttons[command].length; n++)
      {
        button = this.buttons[command][n];
        obj = document.getElementById(button.id);
        
        if (button.type=='image' && obj)
        {
          obj.setAttribute('alt', this.get_label(label));
          if ((link = obj.parentNode) && link.tagName == 'A')
            link.setAttribute('title', this.get_label(label));
        }
        else if (obj)
          obj.setAttribute('title', this.get_label(label));
      }
    };

  // mouse over button
  this.button_over = function(command, id)
    {
    var a_buttons = this.buttons[command];
    var button, img;

    if(!a_buttons || !a_buttons.length)
      return false;

    for(var n=0; n<a_buttons.length; n++)
      {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
        {
        img = document.getElementById(button.id);
        if (img && button.over)
          img.src = button.over;
        }
      }
      
    };

  // mouse down on button
  this.button_sel = function(command, id)
    {
    var a_buttons = this.buttons[command];
    var button, img;

    if(!a_buttons || !a_buttons.length)
      return;

    for(var n=0; n<a_buttons.length; n++)
      {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
        {
        img = document.getElementById(button.id);
        if (img && button.sel)
          img.src = button.sel;
        }
      }
    };

  // mouse out of button
  this.button_out = function(command, id)
    {
    var a_buttons = this.buttons[command];
    var button, img;

    if(!a_buttons || !a_buttons.length)
      return;

    for(var n=0; n<a_buttons.length; n++)
      {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
        {
        img = document.getElementById(button.id);
        if (img && button.act)
          img.src = button.act;
        }
      }
    };

  // set/unset a specific class name
  this.set_classname = function(obj, classname, set)
    {
    var reg = new RegExp('\s*'+classname, 'i');
    if (!set && obj.className.match(reg))
      obj.className = obj.className.replace(reg, '');
    else if (set && !obj.className.match(reg))
      obj.className += ' '+classname;
    };

  // write to the document/window title
  this.set_pagetitle = function(title)
  {
    if (title && document.title)
      document.title = title;
  }

  // display a system message
  this.display_message = function(msg, type, hold)
    {
    if (!this.loaded)  // save message in order to display after page loaded
      {
      this.pending_message = new Array(msg, type);
      return true;
      }

    // pass command to parent window
    if (this.env.framed && parent.rcmail)
      return parent.rcmail.display_message(msg, type, hold);

    if (!this.gui_objects.message)
      return false;

    if (this.message_timer)
      clearTimeout(this.message_timer);
    
    var cont = msg;
    if (type)
      cont = '<div class="'+type+'">'+cont+'</div>';

    var _rcube = this;
    this.gui_objects.message.innerHTML = cont;
    this.gui_objects.message.style.display = 'block';
    
    if (type!='loading')
      this.gui_objects.message.onmousedown = function(){ _rcube.hide_message(); return true; };
    
    if (!hold)
      this.message_timer = window.setTimeout(function(){ ref.hide_message(); }, this.message_time);
    };

  // make a message row disapear
  this.hide_message = function()
    {
    if (this.gui_objects.message)
      {
      this.gui_objects.message.style.display = 'none';
      this.gui_objects.message.onmousedown = null;
      }
    };

  // mark a mailbox as selected and set environment variable
  this.select_folder = function(name, old)
  {
    if (this.gui_objects.folderlist)
    {
      var current_li, target_li;
      
      if ((current_li = this.get_folder_li(old)))
      {
        this.set_classname(current_li, 'selected', false);
        this.set_classname(current_li, 'unfocused', false);
      }

      if ((target_li = this.get_folder_li(name)))
      {
        this.set_classname(target_li, 'unfocused', false);
        this.set_classname(target_li, 'selected', true);
      }
    }
  };

  // helper method to find a folder list item
  this.get_folder_li = function(name)
  {
    if (this.gui_objects.folderlist)
    {
      name = String(name).replace(this.identifier_expr, '');
      return document.getElementById('rcmli'+name);
    }

    return null;
  };

  // for reordering column array, Konqueror workaround
  this.set_message_coltypes = function(coltypes) 
  { 
    this.coltypes = coltypes;
    
    // set correct list titles
    var cell, col;
    var thead = this.gui_objects.messagelist ? this.gui_objects.messagelist.tHead : null;
    for (var n=0; thead && n<this.coltypes.length; n++) 
      {
      col = this.coltypes[n];
      if ((cell = thead.rows[0].cells[n+1]) && (col=='from' || col=='to'))
        {
        // if we have links for sorting, it's a bit more complicated...
        if (cell.firstChild && cell.firstChild.tagName=='A')
          {
          cell.firstChild.innerHTML = this.get_label(this.coltypes[n]);
          cell.firstChild.onclick = function(){ return rcmail.command('sort', this.__col, this); };
          cell.firstChild.__col = col;
          }
        else
          cell.innerHTML = this.get_label(this.coltypes[n]);

        cell.id = 'rcm'+col;
        }
      else if (col == 'subject' && this.message_list)
        this.message_list.subject_col = n+1;
      }
  };

  // create a table row in the message list
  this.add_message_row = function(uid, cols, flags, attachment, attop)
    {
    if (!this.gui_objects.messagelist || !this.message_list)
      return false;

    var tbody = this.gui_objects.messagelist.tBodies[0];
    var rowcount = tbody.rows.length;
    var even = rowcount%2;
    
    this.env.messages[uid] = {deleted:flags.deleted?1:0,
                              replied:flags.replied?1:0,
                              unread:flags.unread?1:0,
			      forwarded:flags.forwarded?1:0,
                              flagged:flags.flagged?1:0};
    
    var row = document.createElement('TR');
    row.id = 'rcmrow'+uid;
    row.className = 'message'
	+ (even ? ' even' : ' odd')
        + (flags.unread ? ' unread' : '')
	+ (flags.deleted ? ' deleted' : '')
	+ (flags.flagged ? ' flagged' : '');		    

    if (this.message_list.in_selection(uid))
      row.className += ' selected';

    var icon = this.env.messageicon;
    if (flags.deleted && this.env.deletedicon)
      icon = this.env.deletedicon;
    else if (flags.replied && this.env.repliedicon)
      {
      if (flags.forwarded && this.env.forwardedrepliedicon)
        icon = this.env.forwardedrepliedicon;
      else
        icon = this.env.repliedicon;
      }
    else if (flags.forwarded && this.env.forwardedicon)
      icon = this.env.forwardedicon;
    else if(flags.unread && this.env.unreadicon)
      icon = this.env.unreadicon;
    
    var col = document.createElement('TD');
    col.className = 'icon';
    col.innerHTML = icon ? '<img src="'+icon+'" alt="" />' : '';
    row.appendChild(col);

    // add each submitted col
    for (var n = 0; n < this.coltypes.length; n++) 
      { 
      var c = this.coltypes[n];
      col = document.createElement('TD');
      col.className = String(c).toLowerCase();
      
      if (c=='flag')
        {
        if (flags.flagged && this.env.flaggedicon)
          col.innerHTML = '<img src="'+this.env.flaggedicon+'" alt="" />';
        else if(!flags.flagged && this.env.unflaggedicon)
          col.innerHTML = '<img src="'+this.env.unflaggedicon+'" alt="" />';
      }
      else if (c=='attachment')
        col.innerHTML = attachment && this.env.attachmenticon ? '<img src="'+this.env.attachmenticon+'" alt="" />' : '&nbsp;';
      else
        col.innerHTML = cols[c];

      row.appendChild(col);
      }

    this.message_list.insert_row(row, attop);

    // remove 'old' row
    if (attop && this.env.pagesize && this.message_list.rowcount > this.env.pagesize)
      {
	var uid = this.message_list.get_last_row();
        this.message_list.remove_row(uid);
	this.message_list.clear_selection(uid);
      }
    };

  // replace content of row count display
  this.set_rowcount = function(text)
    {
    if (this.gui_objects.countdisplay)
      this.gui_objects.countdisplay.innerHTML = text;

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
    if (this.gui_objects.quotadisplay && content)
      this.gui_objects.quotadisplay.innerHTML = content;
    };

  // update the mailboxlist
  this.set_unread_count = function(mbox, count, set_title)
    {
    if (!this.gui_objects.mailboxlist)
      return false;

    this.env.unread_counts[mbox] = count;
    this.set_unread_count_display(mbox, set_title);
    }

  // update the mailbox count display
  this.set_unread_count_display = function(mbox, set_title)
    {
    var reg, text_obj, item, mycount, childcount, div;
    if (item = this.get_folder_li(mbox))
      {
      mycount = this.env.unread_counts[mbox] ? this.env.unread_counts[mbox] : 0;
      text_obj = item.getElementsByTagName('a')[0];
      reg = /\s+\([0-9]+\)$/i;

      childcount = 0;
      if ((div = item.getElementsByTagName('div')[0]) &&
          div.className.match(/collapsed/))
        {
        // add children's counters
        for (var k in this.env.unread_counts) 
          if (k.indexOf(mbox + this.env.delimiter) == 0) {
            childcount += this.env.unread_counts[k];
	  }
        }

      if (mycount && text_obj.innerHTML.match(reg))
        text_obj.innerHTML = text_obj.innerHTML.replace(reg, ' ('+mycount+')');
      else if (mycount)
        text_obj.innerHTML += ' ('+mycount+')';
      else
        text_obj.innerHTML = text_obj.innerHTML.replace(reg, '');

      // set parent's display
      reg = new RegExp(RegExp.escape(this.env.delimiter) + '[^' + RegExp.escape(this.env.delimiter) + ']+$');
      if (mbox.match(reg))
        this.set_unread_count_display(mbox.replace(reg, ''), false);

      // set the right classes
      this.set_classname(item, 'unread', (mycount+childcount)>0 ? true : false);
      }

    // set unread count to window title
    reg = /^\([0-9]+\)\s+/i;
    if (set_title && document.title)
      {
      var doc_title = String(document.title);
      var new_title = "";

      if (mycount && doc_title.match(reg))
        new_title = doc_title.replace(reg, '('+mycount+') ');
      else if (mycount)
        new_title = '('+mycount+') '+doc_title;
      else
        new_title = doc_title.replace(reg, '');
        
      this.set_pagetitle(new_title);
      }
    };

  // notifies that a new message(s) has hit the mailbox
  this.new_message_focus = function()
    {
    // focus main window
    if (this.env.framed && window.parent)
      window.parent.focus();
    else
      window.focus();
    }

  // add row to contacts list
  this.add_contact_row = function(cid, cols, select)
    {
    if (!this.gui_objects.contactslist || !this.gui_objects.contactslist.tBodies[0])
      return false;
    
    var tbody = this.gui_objects.contactslist.tBodies[0];
    var rowcount = tbody.rows.length;
    var even = rowcount%2;
    
    var row = document.createElement('TR');
    row.id = 'rcmrow'+cid;
    row.className = 'contact '+(even ? 'even' : 'odd');
    
    if (this.contact_list.in_selection(cid))
      row.className += ' selected';

    // add each submitted col
    for (var c in cols)
      {
      col = document.createElement('TD');
      col.className = String(c).toLowerCase();
      col.innerHTML = cols[c];
      row.appendChild(col);
      }
    
    this.contact_list.insert_row(row);
    this.enable_command('export', (this.contact_list.rowcount > 0));
    };

  this.toggle_prefer_html = function(checkbox)
    {
    var addrbook_show_images;
    if (addrbook_show_images = document.getElementById('rcmfd_addrbook_show_images'))
      addrbook_show_images.disabled = !checkbox.checked;
    }

  // display fetched raw headers
  this.set_headers = function(content)
    {
    if (this.gui_objects.all_headers_row && this.gui_objects.all_headers_box && content)
      {
      var box = this.gui_objects.all_headers_box;
      box.innerHTML = content;
      box.style.display = 'block';

      if (this.env.framed && parent.rcmail)
	parent.rcmail.set_busy(false);
      else
        this.set_busy(false);
      }
    };

  // display all-headers row and fetch raw message headers
  this.load_headers = function(elem)
    {
    if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box || !this.env.uid)
      return;
    
    this.set_classname(elem, 'show-headers', false);
    this.set_classname(elem, 'hide-headers', true);
    this.gui_objects.all_headers_row.style.display = bw.ie ? 'block' : 'table-row';
    elem.onclick = function() { rcmail.hide_headers(elem); }

    // fetch headers only once
    if (!this.gui_objects.all_headers_box.innerHTML)
      {
      this.display_message(this.get_label('loading'), 'loading', true); 
      this.http_post('headers', '_uid='+this.env.uid);
      }
    }

  // hide all-headers row
  this.hide_headers = function(elem)
    {
    if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box)
      return;

    this.set_classname(elem, 'hide-headers', false);
    this.set_classname(elem, 'show-headers', true);
    this.gui_objects.all_headers_row.style.display = 'none';
    elem.onclick = function() { rcmail.load_headers(elem); }
    }


  /********************************************************/
  /*********  html to text conversion functions   *********/
  /********************************************************/

  this.html2plain = function(htmlText, id)
    {
    var http_request = new rcube_http_request();
    var url = this.env.bin_path+'html2text.php';
    var rcmail = this;

    this.set_busy(true, 'converting');
    console.log('HTTP POST: '+url);

    http_request.onerror = function(o) { rcmail.http_error(o); };
    http_request.oncomplete = function(o) { rcmail.set_text_value(o, id); };
    http_request.POST(url, htmlText, 'application/octet-stream');
    }

  this.set_text_value = function(httpRequest, id)
    {
    this.set_busy(false);
    document.getElementById(id).value = httpRequest.get_text();
    console.log(httpRequest.get_text());
    }


  /********************************************************/
  /*********        remote request methods        *********/
  /********************************************************/

  this.redirect = function(url, lock)
    {
    if (lock || lock === null)
      this.set_busy(true);

    if (this.env.framed && window.parent)
      parent.location.href = url;
    else  
      location.href = url;
    };

  this.goto_url = function(action, query, lock)
    {
    var querystring = query ? '&'+query : '';
    this.redirect(this.env.comm_path+'&_action='+action+querystring, lock);
    };

  this.http_sockets = new Array();
  
  // find a non-busy socket or create a new one
  this.get_request_obj = function()
    {
    for (var n=0; n<this.http_sockets.length; n++)
      {
      if (!this.http_sockets[n].busy)
        return this.http_sockets[n];
      }
    
    // create a new XMLHTTP object
    var i = this.http_sockets.length;
    this.http_sockets[i] = new rcube_http_request();

    return this.http_sockets[i];
    };
  
  // send a http request to the server
  this.http_request = function(action, querystring, lock)
    {
    var request_obj = this.get_request_obj();
    querystring += (querystring ? '&' : '') + '_remote=1';
    
    // add timestamp to request url to avoid cacheing problems in Safari
    if (bw.safari)
      querystring += '&_ts='+(new Date().getTime());

    // send request
    if (request_obj)
      {
      console.log('HTTP request: '+this.env.comm_path+'&_action='+action+'&'+querystring);

      if (lock)
        this.set_busy(true);

      var rcm = this;
      request_obj.__lock = lock ? true : false;
      request_obj.__action = action;
      request_obj.onerror = function(o){ ref.http_error(o); };
      request_obj.oncomplete = function(o){ ref.http_response(o); };
      request_obj.GET(this.env.comm_path+'&_action='+action+'&'+querystring);
      }
    };

    // send a http POST request to the server
    this.http_post = function(action, postdata, lock)
      {
      var request_obj;
      if (postdata && typeof(postdata) == 'object')
        postdata._remote = 1;
      else
        postdata += (postdata ? '&' : '') + '_remote=1';

      // send request
      if (request_obj = this.get_request_obj())
        {
        console.log('HTTP POST: '+this.env.comm_path+'&_action='+action);

        if (lock)
          this.set_busy(true);

        var rcm = this;
        request_obj.__lock = lock ? true : false;
        request_obj.__action = action;
        request_obj.onerror = function(o){ rcm.http_error(o); };
        request_obj.oncomplete = function(o){ rcm.http_response(o); };
        request_obj.POST(this.env.comm_path+'&_action='+action, postdata);
        }
      };

  // handle HTTP response
  this.http_response = function(request_obj)
    {
    var ctype = request_obj.get_header('Content-Type');
    if (ctype)
      {
      ctype = String(ctype).toLowerCase();
      var ctype_array=ctype.split(";");
      ctype = ctype_array[0];
      }

    if (request_obj.__lock)
      this.set_busy(false);

    console.log(request_obj.get_text());

    // if we get javascript code from server -> execute it
    if (request_obj.get_text() && (ctype=='text/javascript' || ctype=='application/x-javascript'))
      eval(request_obj.get_text());

    // process the response data according to the sent action
    switch (request_obj.__action) {
      case 'delete':
        if (this.task == 'addressbook') {
          var uid = this.contact_list.get_selection();
          this.enable_command('compose', (uid && this.contact_list.rows[uid]));
          this.enable_command('delete', 'edit', (uid && this.contact_list.rows[uid] && this.env.address_sources && !this.env.address_sources[this.env.source].readonly));
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));
        }
      
      case 'moveto':
        if (this.env.action == 'show')
          this.command('list');
        else if (this.message_list)
          this.message_list.init();
        break;
        
      case 'purge':
      case 'expunge':      
        if (!this.env.messagecount && this.task == 'mail') {
          // clear preview pane content
          if (this.env.contentframe)
            this.show_contentframe(false);
          // disable commands useless when mailbox is empty
          this.enable_command('show', 'reply', 'reply-all', 'forward', 'moveto', 'delete', 'mark', 'viewsource',
            'print', 'load-attachment', 'purge', 'expunge', 'select-all', 'select-none', 'sort', false);
        }
        break;

      case 'check-recent':
      case 'getunread':
      case 'list':
        if (this.task == 'mail') {
          if (this.message_list && request_obj.__action == 'list')
            this.msglist_select(this.message_list);
          this.enable_command('show', 'expunge', 'select-all', 'select-none', 'sort', (this.env.messagecount > 0));
          this.enable_command('purge', this.purge_mailbox_test());
        }
        else if (this.task == 'addressbook')
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));

        break;
      }

    request_obj.reset();
    };

  // handle HTTP request errors
  this.http_error = function(request_obj)
    {
    //alert('Error sending request: '+request_obj.url+' => HTTP '+request_obj.xmlhttp.status);
    if (request_obj.__lock)
      this.set_busy(false);

    request_obj.reset();
    request_obj.__lock = false;
    this.display_message('Unknown Server Error!', 'error');
    };

  // use an image to send a keep-alive siganl to the server
  this.send_keep_alive = function()
    {
    var d = new Date();
    this.http_request('keep-alive', '_t='+d.getTime());
    };

  // send periodic request to check for recent messages
  this.check_for_recent = function(setbusy)
    {
    if (this.busy)
      return;

    if (setbusy)
      this.set_busy(true, 'checkingmail');

    var addurl = '_t=' + (new Date().getTime());

    if (this.gui_objects.messagelist)
      addurl += '&_list=1';
    if (this.gui_objects.quotadisplay)
      addurl += '&_quota=1';
    if (this.env.search_request)
      addurl += '&_search=' + this.env.search_request;

    this.http_request('check-recent', addurl, true);
    };


  /********************************************************/
  /*********            helper methods            *********/
  /********************************************************/
  
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


  this.get_caret_pos = function(obj)
    {
    if (typeof(obj.selectionEnd)!='undefined')
      return obj.selectionEnd;
    else if (document.selection && document.selection.createRange)
      {
      var range = document.selection.createRange();
      if (range.parentElement()!=obj)
        return 0;

      var gm = range.duplicate();
      if (obj.tagName=='TEXTAREA')
        gm.moveToElementText(obj);
      else
        gm.expand('textedit');
      
      gm.setEndPoint('EndToStart', range);
      var p = gm.text.length;

      return p<=obj.value.length ? p : -1;
      }
    else
      return obj.value.length;
    };

  this.set_caret2start = function(obj)
    {
    if (obj.createTextRange)
      {
      var range = obj.createTextRange();
      range.collapse(true);
      range.select();
      }
    else if (obj.setSelectionRange)
      obj.setSelectionRange(0,0);

    obj.focus();
    };

  // set all fields of a form disabled
  this.lock_form = function(form, lock)
    {
    if (!form || !form.elements)
      return;
    
    var type;
    for (var n=0; n<form.elements.length; n++)
      {
      type = form.elements[n];
      if (type=='hidden')
        continue;
        
      form.elements[n].disabled = lock;
      }
    };
    
  }  // end object rcube_webmail


/**
 * Class for sending HTTP requests
 * @constructor
 */
function rcube_http_request()
  {
  this.url = '';
  this.busy = false;
  this.xmlhttp = null;

  // reset object properties
  this.reset = function()
    {
    // set unassigned event handlers
    this.onloading = function(){ };
    this.onloaded = function(){ };
    this.oninteractive = function(){ };
    this.oncomplete = function(){ };
    this.onabort = function(){ };
    this.onerror = function(){ };
    
    this.url = '';
    this.busy = false;
    this.xmlhttp = null;
    }

  // create HTMLHTTP object
  this.build = function()
    {
    if (window.XMLHttpRequest)
      this.xmlhttp = new XMLHttpRequest();
    else if (window.ActiveXObject)
      {
      try { this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
      catch(e) { this.xmlhttp = null; }
      }
    else
      {
      
      }
    }

  // send GET request
  this.GET = function(url)
    {
    this.build();

    if (!this.xmlhttp)
      {
      this.onerror(this);
      return false;
      }

    var _ref = this;
    this.url = url;
    this.busy = true;

    this.xmlhttp.onreadystatechange = function(){ _ref.xmlhttp_onreadystatechange(); };
    this.xmlhttp.open('GET', url, true);
    this.xmlhttp.setRequestHeader('X-RoundCube-Referer', bw.get_cookie('roundcube_sessid'));
    this.xmlhttp.send(null);
    };

  this.POST = function(url, body, contentType)
    {
    // default value for contentType if not provided
    if (typeof(contentType) == 'undefined')
      contentType = 'application/x-www-form-urlencoded';

    this.build();
    
    if (!this.xmlhttp)
    {
       this.onerror(this);
       return false;
    }
    
    var req_body = body;
    if (typeof(body) == 'object')
    {
      req_body = '';
      for (var p in body)
        req_body += (req_body ? '&' : '') + p+'='+urlencode(body[p]);
    }

    var ref = this;
    this.url = url;
    this.busy = true;
    
    this.xmlhttp.onreadystatechange = function() { ref.xmlhttp_onreadystatechange(); };
    this.xmlhttp.open('POST', url, true);
    this.xmlhttp.setRequestHeader('Content-Type', contentType);
    this.xmlhttp.setRequestHeader('X-RoundCube-Referer', bw.get_cookie('roundcube_sessid'));
    this.xmlhttp.send(req_body);
    };

  // handle onreadystatechange event
  this.xmlhttp_onreadystatechange = function()
    {
    if(this.xmlhttp.readyState == 1)
      this.onloading(this);

    else if(this.xmlhttp.readyState == 2)
      this.onloaded(this);

    else if(this.xmlhttp.readyState == 3)
      this.oninteractive(this);

    else if(this.xmlhttp.readyState == 4)
      {
      try {
        if (this.xmlhttp.status == 0)
          this.onabort(this);
        else if(this.xmlhttp.status == 200)
          this.oncomplete(this);
        else
          this.onerror(this);

        this.busy = false;
        }
      catch(err)
        {
        this.onerror(this);
        this.busy = false;
        }
      }
    }

  // getter method for HTTP headers
  this.get_header = function(name)
    {
    return this.xmlhttp.getResponseHeader(name);
    };

  this.get_text = function()
    {
    return this.xmlhttp.responseText;
    };

  this.get_xml = function()
    {
    return this.xmlhttp.responseXML;
    };

  this.reset();
  
  }  // end class rcube_http_request

// helper function to call the init method with a delay
function call_init(o)
  {
    window.setTimeout('if (window[\''+o+'\'] && window[\''+o+'\'].init) { '+o+'.init(); }',
        bw.win ? 500 : 200);
  }

