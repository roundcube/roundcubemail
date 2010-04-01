/*
 +-----------------------------------------------------------------------+
 | RoundCube Webmail Client Script                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2010, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Aleksander 'A.L.E.C' Machniak <alec@alec.pl>                 |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: jquery.js, common.js, list.js                               |
 +-----------------------------------------------------------------------+

  $Id$
*/


function rcube_webmail()
{
  this.env = new Object();
  this.labels = new Object();
  this.buttons = new Object();
  this.buttons_sel = new Object();
  this.gui_objects = new Object();
  this.gui_containers = new Object();
  this.commands = new Object();
  this.command_handlers = new Object();
  this.onloads = new Array();

  // create protected reference to myself
  this.ref = 'rcmail';
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

  // set jQuery ajax options
  jQuery.ajaxSetup({ cache:false,
    error:function(request, status, err){ ref.http_error(request, status, err); },
    beforeSend:function(xmlhttp){ xmlhttp.setRequestHeader('X-RoundCube-Request', ref.env.request_token); }
  });

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

    // find all registered gui containers
    for (var n in this.gui_containers)
      this.gui_containers[n] = $('#'+this.gui_containers[n]);

    // find all registered gui objects
    for (var n in this.gui_objects)
      this.gui_objects[n] = rcube_find_object(this.gui_objects[n]);
      
    // init registered buttons
    this.init_buttons();

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
        // enable mail commands
        this.enable_command('list', 'checkmail', 'compose', 'add-contact', 'search', 'reset-search', 'collapse-folder', true);
      
        if (this.gui_objects.messagelist)
          {
          this.message_list = new rcube_list_widget(this.gui_objects.messagelist,
            {multiselect:true, multiexpand:true, draggable:true, keyboard:true, dblclick_time:this.dblclick_time});
          this.message_list.row_init = function(o){ p.init_message_row(o); };
          this.message_list.addEventListener('dblclick', function(o){ p.msglist_dbl_click(o); });
          this.message_list.addEventListener('keypress', function(o){ p.msglist_keypress(o); });
          this.message_list.addEventListener('select', function(o){ p.msglist_select(o); });
          this.message_list.addEventListener('dragstart', function(o){ p.drag_start(o); });
          this.message_list.addEventListener('dragmove', function(e){ p.drag_move(e); });
          this.message_list.addEventListener('dragend', function(e){ p.drag_end(e); });
          this.message_list.addEventListener('expandcollapse', function(e){ p.msglist_expand(e); });
          document.onmouseup = function(e){ return p.doc_mouse_up(e); };

          this.set_message_coltypes(this.env.coltypes);
          this.message_list.init();
          this.enable_command('toggle_status', 'toggle_flag', 'menu-open', 'menu-save', true);
          
          if (this.gui_objects.mailcontframe)
            this.gui_objects.mailcontframe.onmousedown = function(e){ return p.click_on_list(e); };
          else
            this.message_list.focus();
          
          // load messages
          if (this.env.messagecount)
            this.command('list');
          }

        if (this.env.search_text != null && document.getElementById('quicksearchbox') != null)
          document.getElementById('quicksearchbox').value = this.env.search_text;
        
        if (this.env.action=='show' || this.env.action=='preview')
          {
          this.enable_command('show', 'reply', 'reply-all', 'forward', 'moveto', 'copy', 'delete',
            'open', 'mark', 'edit', 'viewsource', 'download', 'print', 'load-attachment', 'load-headers', true);

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
        
          if (this.env.blockedobjects)
            {
            if (this.gui_objects.remoteobjectsmsg)
              this.gui_objects.remoteobjectsmsg.style.display = 'block';
            this.enable_command('load-images', 'always-load', true);
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

        if (this.env.action=='compose')
          {
          this.enable_command('add-attachment', 'send-attachment', 'remove-attachment', 'send', true);
          if (this.env.spellcheck)
            {
            this.env.spellcheck.spelling_state_observer = function(s){ ref.set_spellcheck_state(s); };
            this.set_spellcheck_state('ready');
            if ($("input[name='_is_html']").val() == '1')
              this.display_spellcheck_controls(false);
            }
          if (this.env.drafts_mailbox)
            this.enable_command('savedraft', true);
            
          document.onmouseup = function(e){ return p.doc_mouse_up(e); };

          // init message compose form
          this.init_messageform();
          }

        if (this.env.messagecount) {
          this.enable_command('select-all', 'select-none', 'expunge', true);
          this.enable_command('expand-all', 'expand-unread', 'collapse-all', this.env.threading);
        }

        if (this.purge_mailbox_test())
          this.enable_command('purge', true);

        this.set_page_buttons();

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
        if (this.gui_objects.folderlist)
          this.env.contactfolders = $.extend($.extend({}, this.env.address_sources), this.env.contactgroups);
        
        if (this.gui_objects.contactslist)
          {
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

          if (this.gui_objects.contactslist.parentNode)
            {
            this.gui_objects.contactslist.parentNode.onmousedown = function(e){ return p.click_on_list(e); };
            document.onmouseup = function(e){ return p.doc_mouse_up(e); };
            }
          else
            this.contact_list.focus();
            
          //this.gui_objects.folderlist = this.gui_objects.contactslist;
          }

        this.set_page_buttons();
        
        if (this.env.address_sources && this.env.address_sources[this.env.source] && !this.env.address_sources[this.env.source].readonly) {
          this.enable_command('add', 'import', true);
          this.enable_command('group-create', this.env.address_sources[this.env.source].groups);
        }
        
        if (this.env.cid)
          this.enable_command('show', 'edit', true);

        if ((this.env.action=='add' || this.env.action=='edit') && this.gui_objects.editform)
          this.enable_command('save', true);
        else
          this.enable_command('search', 'reset-search', 'moveto', true);
          
        if (this.contact_list && this.contact_list.rowcount > 0)
          this.enable_command('export', true);

        this.enable_command('list', 'listgroup', true);
        break;


      case 'settings':
        this.enable_command('preferences', 'identities', 'save', 'folders', true);
        
        if (this.env.action=='identities') {
          this.enable_command('add', this.env.identities_level < 2);
        }
        else if (this.env.action=='edit-identity' || this.env.action=='add-identity') {
          this.enable_command('add', this.env.identities_level < 2);
          this.enable_command('save', 'delete', 'edit', true);
        }
        else if (this.env.action=='folders')
          this.enable_command('subscribe', 'unsubscribe', 'create-folder', 'rename-folder', 'delete-folder', 'enable-threading', 'disable-threading', true);

        if (this.gui_objects.identitieslist)
          {
          this.identity_list = new rcube_list_widget(this.gui_objects.identitieslist, {multiselect:false, draggable:false, keyboard:false});
          this.identity_list.addEventListener('select', function(o){ p.identity_select(o); });
          this.identity_list.init();
          this.identity_list.focus();

          if (this.env.iid)
            this.identity_list.highlight_row(this.env.iid);
          }
        else if (this.gui_objects.sectionslist)
          {
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
        $('#rcmlogintz').val(new Date().getTimezoneOffset() / -60);

        this.enable_command('login', true);
        break;
      
      default:
        break;
      }

    // flag object as complete
    this.loaded = true;

    // show message
    if (this.pending_message)
      this.display_message(this.pending_message[0], this.pending_message[1]);
      
    // map implicit containers
    if (this.gui_objects.folderlist)
      this.gui_containers.foldertray = $(this.gui_objects.folderlist);

    // trigger init event hook
    this.triggerEvent('init', { task:this.task, action:this.env.action });
    
    // execute all foreign onload scripts
    // @deprecated
    for (var i=0; i<this.onloads.length; i++)
      {
      if (typeof(this.onloads[i]) == 'string')
        eval(this.onloads[i]);
      else if (typeof(this.onloads[i]) == 'function')
        this.onloads[i]();
      }

    // start keep-alive interval
    this.start_keepalive();
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

    // process external commands
    if (typeof this.command_handlers[command] == 'function')
    {
      var ret = this.command_handlers[command](props, obj);
      return ret !== null ? ret : (obj ? false : true);
    }
    else if (typeof this.command_handlers[command] == 'string')
    {
      var ret = window[this.command_handlers[command]](props, obj);
      return ret !== null ? ret : (obj ? false : true);
    }
    
    // trigger plugin hook
    var event_ret = this.triggerEvent('before'+command, props);
    if (typeof event_ret != 'undefined') {
      // abort if one the handlers returned false
      if (event_ret === false)
        return false;
      else
        props = event_ret;
    }

    // process internal command
    switch (command)
      {
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

      case 'permaurl':
        if (obj && obj.href && obj.target)
          return true;
        else if (this.env.permaurl)
          parent.location.href = this.env.permaurl;
        break;

      case 'menu-open':
      case 'menu-save':
        this.triggerEvent(command, {props:props});
        return false;

      case 'open':
        var uid;
        if (uid = this.get_single_uid())
        {
          obj.href = '?_task='+this.env.task+'&_action=show&_mbox='+urlencode(this.env.mailbox)+'&_uid='+uid;
          return true;
        }
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
          this.enable_command('add', 'import', (this.env.address_sources && !this.env.address_sources[props].readonly));
          }
        break;


      case 'listgroup':
        this.list_contacts(null, props);
        break;


      case 'load-headers':
        this.load_headers(obj);
        break;


      case 'sort':
        var sort_order, sort_col = props;

        if (this.env.sort_col==sort_col)
          sort_order = this.env.sort_order=='ASC' ? 'DESC' : 'ASC';
        else
          sort_order = 'ASC';

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
        else if (this.task=='mail' && (cid = this.get_single_uid())) {
          var url = (this.env.mailbox == this.env.drafts_mailbox) ? '_draft_uid=' : '_uid=';
          this.goto_url('compose', url+cid+'&_mbox='+urlencode(this.env.mailbox), true);
        }
        break;

      case 'save-identity':
      case 'save':
        if (this.gui_objects.editform)
          {
          var input_pagesize = $("input[name='_pagesize']");
          var input_name  = $("input[name='_name']");
          var input_email = $("input[name='_email']");

          // user prefs
          if (input_pagesize.length && isNaN(parseInt(input_pagesize.val())))
            {
            alert(this.get_label('nopagesizewarning'));
            input_pagesize.focus();
            break;
            }
          // contacts/identities
          else
            {
            if (input_name.length && input_name.val() == '')
              {
              alert(this.get_label('nonamewarning'));
              input_name.focus();
              break;
              }
            else if (input_email.length && !rcube_check_email(input_email.val()))
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
        if (this.env.uid && props.mimetype && jQuery.inArray(props.mimetype, this.mimetypes)>=0)
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
        this.select_all_mode = props ? false : true;
        if (props == 'invert')
          this.message_list.invert_selection();
        else
          this.message_list.select_all(props == 'page' ? '' : props);
        break;

      case 'select-none':
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
        if (window.tinyMCE && tinyMCE.get(this.env.composebody)) {
          tinyMCE.execCommand('mceSpellCheck', true);
        }
        else if (this.env.spellcheck && this.env.spellcheck.spellCheck && this.spellcheck_ready) {
          this.env.spellcheck.spellCheck();
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

      case 'insert-sig':
        this.change_identity($("[name='_from']")[0], true);
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
          ref.sourcewin = window.open(this.env.comm_path+'&_action=viewsource&_uid='+uid+'&_mbox='+urlencode(this.env.mailbox));
          if (this.sourcewin)
            window.setTimeout(function(){ ref.sourcewin.focus(); }, 20);
          }
        break;

      case 'download':
        var uid;
        if (uid = this.get_single_uid())
          this.goto_url('viewsource', '&_uid='+uid+'&_mbox='+urlencode(this.env.mailbox)+'&_save=1');
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
          this.list_contacts(this.env.source, this.env.group);
        break;

      case 'group-create':
        this.add_contact_group(props)
        break;
        
      case 'group-rename':
        this.rename_contact_group();
        break;
        
      case 'group-delete':
        this.delete_contact_group();
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

      case 'enable-threading':
        this.enable_threading(props);
        break;

      case 'disable-threading':
        this.disable_threading(props);
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
      
    this.triggerEvent('after'+command, props);

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
    
  // called when a request timed out
  this.request_timed_out = function()
    {
    this.set_busy(false);
    this.display_message('Request timed out!', 'error');
    };
  
  this.reload = function(delay)
  {
    if (this.env.framed && parent.rcmail)
      parent.rcmail.reload(delay);
    else if (delay)
      window.setTimeout(function(){ rcmail.reload(); }, delay);
    else if (window.location)
      location.href = this.env.comm_path;
  };


  /*********************************************************/
  /*********        event handling methods         *********/
  /*********************************************************/

  this.doc_mouse_up = function(e)
  {
    var model, list, li;

    if (this.message_list) {
      if (!rcube_mouse_is_over(e, this.message_list.list))
        this.message_list.blur();
      list = this.message_list;
      model = this.env.mailboxes;
    }
    else if (this.contact_list) {
      if (!rcube_mouse_is_over(e, this.contact_list.list))
        this.contact_list.blur();
      list = this.contact_list;
      model = this.env.contactfolders;
    }
    else if (this.ksearch_value) {
      this.ksearch_blur();
    }

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
      for (var id in this.buttons_sel)
        if (typeof id != 'function')
          this.button_out(this.buttons_sel[id], id);
      this.buttons_sel = {};
    }
  };

  this.drag_menu = function(e, target)
  {
    var modkey = rcube_event.get_modifier(e);
    var menu = $('#'+this.gui_objects.message_dragmenu);

    if (menu && modkey == SHIFT_KEY && this.commands['copy']) {
      var pos = rcube_event.get_mouse_pos(e);
      this.env.drag_target = target;
      menu.css({top: (pos.y-10)+'px', left: (pos.x-10)+'px'}).show();
      return true;
    }
    
    return false;
  };

  this.drag_menu_action = function(action)
  {
    var menu = $('#'+this.gui_objects.message_dragmenu);
    if (menu) {
      menu.hide();
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
    
    // save folderlist and folders location/sizes for droptarget calculation in drag_move()
    if (this.gui_objects.folderlist && model)
      {
      this.initialBodyScrollTop = bw.ie ? 0 : window.pageYOffset;
      this.initialListScrollTop = this.gui_objects.folderlist.parentNode.scrollTop;

      var li, pos, list, height;
      list = $(this.gui_objects.folderlist);
      pos = list.offset();
      this.env.folderlist_coords = { x1:pos.left, y1:pos.top, x2:pos.left + list.width(), y2:pos.top + list.height() };

      this.env.folder_coords = new Array();
      for (var k in model) {
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
      window.clearTimeout(this.folder_auto_timer);
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
      // offsets to compensate for scrolling while dragging a message
      var boffset = bw.ie ? -document.documentElement.scrollTop : this.initialBodyScrollTop;
      var moffset = this.initialListScrollTop-this.gui_objects.folderlist.parentNode.scrollTop;
      var toffset = -moffset-boffset;

      var li, div, pos, mouse;
      mouse = rcube_event.get_mouse_pos(e);
      pos = this.env.folderlist_coords;
      mouse.y += toffset;

      // if mouse pointer is outside of folderlist
      if (mouse.x < pos.x1 || mouse.x >= pos.x2 || mouse.y < pos.y1 || mouse.y >= pos.y2) {
        if (this.env.last_folder_target) {
          $(this.get_folder_li(this.env.last_folder_target)).removeClass('droptarget');
          this.env.folder_coords[this.env.last_folder_target].on = 0;
          this.env.last_folder_target = null;
        }
        return;
      }
    
      // over the folders
      for (var k in this.env.folder_coords) {
        pos = this.env.folder_coords[k];
        if (mouse.x >= pos.x1 && mouse.x < pos.x2 && mouse.y >= pos.y1 && mouse.y < pos.y2
            && this.check_droptarget(k)) {

          li = this.get_folder_li(k);
          div = $(li.getElementsByTagName("div")[0]);

          // if the folder is collapsed, expand it after 1sec and restart the drag & drop process.
          if (div.hasClass('collapsed')) {
            if (this.folder_auto_timer)
              window.clearTimeout(this.folder_auto_timer);
            
            this.folder_auto_expand = k;
            this.folder_auto_timer = window.setTimeout(function() {
                rcmail.command("collapse-folder", rcmail.folder_auto_expand);
                rcmail.drag_start(null);
              }, 1000);
          } else if (this.folder_auto_timer) {
            window.clearTimeout(this.folder_auto_timer);
            this.folder_auto_timer = null;
            this.folder_auto_expand = null;
          }
          
          $(li).addClass('droptarget');
          this.env.last_folder_target = k;
          this.env.folder_coords[k].on = 1;
        }
        else if (pos.on) {
          $(this.get_folder_li(k)).removeClass('droptarget');
          this.env.folder_coords[k].on = 0;
        }
      }
    }
  };

  this.collapse_folder = function(id)
    {
    var div;
    if ((li = this.get_folder_li(id)) &&
        (div = $(li.getElementsByTagName("div")[0])) &&
        (div.hasClass('collapsed') || div.hasClass('expanded')))
      {
      var ul = $(li.getElementsByTagName("ul")[0]);
      if (div.hasClass('collapsed'))
        {
        ul.show();
        div.removeClass('collapsed').addClass('expanded');
        var reg = new RegExp('&'+urlencode(id)+'&');
        this.set_env('collapsed_folders', this.env.collapsed_folders.replace(reg, ''));
        }
      else
        {
        ul.hide();
        div.removeClass('expanded').addClass('collapsed');
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

    return rcube_event.get_button(e) == 2 ? true : rcube_event.cancel(e);
    };

  this.msglist_select = function(list)
    {
    if (this.preview_timer)
      clearTimeout(this.preview_timer);

    var selected = list.get_single_selection() != null;

    // Hide certain command buttons when Drafts folder is selected
    if (this.env.mailbox == this.env.drafts_mailbox)
      {
      this.enable_command('reply', 'reply-all', 'forward', false);
      this.enable_command('show', 'print', 'open', 'edit', 'download', 'viewsource', selected);
      this.enable_command('delete', 'moveto', 'copy', 'mark', (list.selection.length > 0 ? true : false));
      }
    else
      {
      this.enable_command('show', 'reply', 'reply-all', 'forward', 'print', 'edit', 'open', 'download', 'viewsource', selected);
      this.enable_command('delete', 'moveto', 'copy', 'mark', (list.selection.length > 0 ? true : false));
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
    else if (list.key_pressed == 33)
      this.command('previouspage');
    else if (list.key_pressed == 34)
      this.command('nextpage');
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
  
  this.msglist_expand = function(row)
  {
    if (this.env.messages[row.uid])
      this.env.messages[row.uid].expanded = row.expanded;
  };
  
  this.check_droptarget = function(id)
  {
    if (this.task == 'mail')
      return (this.env.mailboxes[id] && this.env.mailboxes[id].id != this.env.mailbox && !this.env.mailboxes[id].virtual);
    else if (this.task == 'addressbook')
      return (id != this.env.source && this.env.contactfolders[id] && !this.env.contactfolders[id].readonly &&
        !(!this.env.source && this.env.contactfolders[id].group) &&
        !(this.env.contactfolders[id].type == 'group' && this.env.contactfolders[id].id == this.env.group));
    else if (this.task == 'settings')
      return (id != this.env.folder);
  };


  /*********************************************************/
  /*********     (message) list functionality      *********/
  /*********************************************************/

  this.init_message_row = function(row)
  {
    var self = this;
    var uid = row.uid;
    
    if (uid && this.env.messages[uid])
      $.extend(row, this.env.messages[uid]);

    // set eventhandler to message icon
    if (this.env.subject_col != null && (row.icon = document.getElementById('msgicn'+row.uid))) {
      row.icon._row = row.obj;
      row.icon.onmousedown = function(e) { self.command('toggle_status', this); };
    }

    // set eventhandler to flag icon, if icon found
    if (this.env.flagged_col != null && (row.flagged_icon = document.getElementById('flaggedicn'+row.uid))) {
      row.flagged_icon._row = row.obj;
      row.flagged_icon.onmousedown = function(e) { self.command('toggle_flag', this); };
    }

    var expando;
    if (!row.depth && row.has_children && (expando = document.getElementById('rcmexpando'+row.uid))) {
      expando.onmousedown = function(e) { return self.expand_message_row(e, uid); };
    }

    this.triggerEvent('insertrow', { uid:uid, row:row });
  };

  // create a table row in the message list
  this.add_message_row = function(uid, cols, flags, attop)
  {
    if (!this.gui_objects.messagelist || !this.message_list)
      return false;

    if (this.message_list.background)
      var tbody = this.message_list.background;
    else
      var tbody = this.gui_objects.messagelist.tBodies[0];

    var rows = this.message_list.rows;
    var rowcount = tbody.rows.length;
    var even = rowcount%2;

    if (!this.env.messages[uid])
      this.env.messages[uid] = {};

    // merge flags over local message object
    $.extend(this.env.messages[uid], {
      deleted: flags.deleted?1:0,
      replied: flags.replied?1:0,
      unread: flags.unread?1:0,
      forwarded: flags.forwarded?1:0,
      flagged: flags.flagged?1:0,
      has_children: flags.has_children?1:0,
      depth: flags.depth?flags.depth:0,
      unread_children: flags.unread_children,
      parent_uid: flags.parent_uid
    });

    var message = this.env.messages[uid];

    var css_class = 'message'
        + (even ? ' even' : ' odd')
        + (flags.unread ? ' unread' : '')
        + (flags.deleted ? ' deleted' : '')
        + (flags.flagged ? ' flagged' : '')
        + (flags.unread_children && !flags.unread && !this.env.autoexpand_threads ? ' unroot' : '')
        + (this.message_list.in_selection(uid) ? ' selected' : '');

    // for performance use DOM instead of jQuery here
    var row = document.createElement('tr');
    row.id = 'rcmrow'+uid;
    row.className = css_class;

    var icon = this.env.messageicon;
    if (!flags.unread && flags.unread_children > 0 && this.env.unreadchildrenicon)
      icon = this.env.unreadchildrenicon;
    else if (flags.deleted && this.env.deletedicon)
      icon = this.env.deletedicon;
    else if (flags.replied && this.env.repliedicon) {
      if (flags.forwarded && this.env.forwardedrepliedicon)
        icon = this.env.forwardedrepliedicon;
      else
        icon = this.env.repliedicon;
    }
    else if (flags.forwarded && this.env.forwardedicon)
      icon = this.env.forwardedicon;
    else if(flags.unread && this.env.unreadicon)
      icon = this.env.unreadicon;

    var tree = expando = '';

    if (this.env.threading) {
      // This assumes that div width is hardcoded to 15px,
      var width = message.depth * 15;
      if (message.depth) {
        if ((this.env.autoexpand_threads == 0 || this.env.autoexpand_threads == 2) &&
            (!rows[message.parent_uid] || !rows[message.parent_uid].expanded)) {
          row.style.display = 'none';
          message.expanded = false;
        }
        else
          message.expanded = true;
        }
      else if (message.has_children) {
        if (typeof(message.expanded) == 'undefined' && (this.env.autoexpand_threads == 1 || (this.env.autoexpand_threads == 2 && message.unread_children))) {
          message.expanded = true;
        }
      }

      if (width)
        tree += '<span id="rcmtab' + uid + '" class="branch" style="width:' + width + 'px;">&nbsp;&nbsp;</span>';

      if (message.has_children && !message.depth)
        expando = '<div id="rcmexpando' + uid + '" class="' + (message.expanded ? 'expanded' : 'collapsed') + '">&nbsp;&nbsp;</div>';
    }

    tree += icon ? '<img id="msgicn'+uid+'" src="'+icon+'" alt="" class="msgicon" />' : '';
    
    // first col is always there
    var col = document.createElement('td');
    col.className = 'threads';
    col.innerHTML = expando;
    row.appendChild(col);
    
    // build subject link 
    if (!bw.ie && cols.subject) {
      var action = flags.mbox == this.env.drafts_mailbox ? 'compose' : 'show';
      var uid_param = flags.mbox == this.env.drafts_mailbox ? '_draft_uid' : '_uid';
      cols.subject = '<a href="./?_task=mail&_action='+action+'&_mbox='+urlencode(flags.mbox)+'&'+uid_param+'='+uid+'"'+
        ' onclick="return rcube_event.cancel(event)">'+cols.subject+'</a>';
    }

    // add each submitted col
    for (var n = 0; n < this.env.coltypes.length; n++) {
      var c = this.env.coltypes[n];
      col = document.createElement('td');
      col.className = String(c).toLowerCase();

      var html;
      if (c=='flag') {
        if (flags.flagged && this.env.flaggedicon)
          html = '<img id="flaggedicn'+uid+'" src="'+this.env.flaggedicon+'" class="flagicon" alt="" />';
        else if(!flags.flagged && this.env.unflaggedicon)
          html = '<img id="flaggedicn'+uid+'" src="'+this.env.unflaggedicon+'" class="flagicon" alt="" />';
      }
      else if (c=='attachment')
        html = flags.attachment && this.env.attachmenticon ? '<img src="'+this.env.attachmenticon+'" alt="" />' : '&nbsp;';
      else if (c=='subject')
        html = tree + cols[c];
      else
        html = cols[c];

      col.innerHTML = html;

      row.appendChild(col);
    }

    this.message_list.insert_row(row, attop);

    // remove 'old' row
    if (attop && this.env.pagesize && this.message_list.rowcount > this.env.pagesize) {
      var uid = this.message_list.get_last_row();
      this.message_list.remove_row(uid);
      this.message_list.clear_selection(uid);
    }
  };

  // messages list handling in background (for performance)
  this.offline_message_list = function(flag)
    {
      if (this.message_list)
      	this.message_list.set_background_mode(flag);
    };

  this.set_list_sorting = function(sort_col, sort_order)
    {
    // set table header class
    $('#rcm'+this.env.sort_col).removeClass('sorted'+(this.env.sort_order.toUpperCase()));
    if (sort_col)
      $('#rcm'+sort_col).addClass('sorted'+sort_order);
    
    this.env.sort_col = sort_col;
    this.env.sort_order = sort_order;
    }

  this.set_list_options = function(cols, sort_col, sort_order, threads)
    {
    var update, add_url = '';

    if (this.env.sort_col != sort_col || this.env.sort_order != sort_order) {
      update = 1;
      this.set_list_sorting(sort_col, sort_order);
      }
    
    if (this.env.threading != threads) {
      update = 1;
      add_url += '&_threads=' + threads;     
      }

    if (cols.join() != this.env.coltypes.join()) {
      update = 1;
      add_url += '&_cols=' + cols.join(',');
      }

    if (update)
      this.list_mailbox('', '', sort_col+'_'+sort_order, add_url);
    }

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
        this.update_thread_root(id, 'read');
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
    if (this.env.contentframe && (frm = $('#'+this.env.contentframe)) && frm.length)
      {
      if (!show && window.frames[this.env.contentframe])
        {
        if (window.frames[this.env.contentframe].location.href.indexOf(this.env.blankpage)<0)
          window.frames[this.env.contentframe].location.href = this.env.blankpage;
        }
      else if (!bw.safari && !bw.konq)
        frm[show ? 'show' : 'hide']();
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
        this.list_contacts(this.env.source, null, page);
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
  this.list_mailbox = function(mbox, page, sort, add_url)
    {
    var url = '';
    var target = window;

    if (!mbox)
      mbox = this.env.mailbox;

    if (add_url)
      url += add_url;

    // add sort to url if set
    if (sort)
      url += '&_sort=' + sort;

    // also send search request to get the right messages
    if (this.env.search_request)
      url += '&_search='+this.env.search_request;

    // set page=1 if changeing to another mailbox
    if (!page && this.env.mailbox != mbox)
      {
      page = 1;
      this.env.current_page = page;
      this.show_contentframe(false);
      }

    if (mbox != this.env.mailbox || (mbox == this.env.mailbox && !page && !sort))
      url += '&_refresh=1';

    // unselect selected messages
    this.last_selected = 0;
    if (this.message_list) {
      this.message_list.clear_selection();
      this.select_all_mode = false;
    }
    this.select_folder(mbox, this.env.mailbox);
    this.env.mailbox = mbox;

    // load message list remotely
    if (this.gui_objects.messagelist)
      {
      this.list_mailbox_remote(mbox, page, url);
      return;
      }
    
    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      target = window.frames[this.env.contentframe];
      url += '&_framed=1';
      }

    // load message list to target frame/window
    if (mbox)
      {
      this.set_busy(true, 'loading');
      target.location.href = this.env.comm_path+'&_mbox='+urlencode(mbox)+(page ? '&_page='+page : '')+url;
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

  // expand all threads with unread children
  this.expand_unread = function()
    {
    var tbody = this.gui_objects.messagelist.tBodies[0];
    var new_row = tbody.firstChild;
    var r;
    
    while (new_row) {
      if (new_row.nodeType == 1 && (r = this.message_list.rows[new_row.uid])
	    && r.unread_children) {
	this.message_list.expand_all(r);
	var expando = document.getElementById('rcmexpando' + r.uid);
	if (expando)
	  expando.className = 'expanded';
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
    //  this.message_list.expand(null);
    }

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
    } else if (flag == 'unread' && p.has_children) {
      // unread_children may be undefined
      p.unread_children = p.unread_children ? p.unread_children + 1 : 1;
    } else {
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

    var rows = this.message_list.rows;
    var row = rows[uid]
    var depth = rows[uid].depth;
    var r, parent, count = 0;
    var roots = new Array();

    if (!row.depth) // root message: decrease roots count
      count--;
    else if (row.unread) {
      // update unread_children for thread root
      var parent = this.message_list.find_root(uid);
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
	$('#rcmtab'+r.uid).width(r.depth * 15);
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
	    roots[roots.length] = r;
	    }
	  // show if it was hidden
	  if (r.obj.style.display == 'none')
	    $(r.obj).show();
	  }
	else {
	  if (r.depth == depth)
	    r.parent_uid = parent;
	  if (r.unread && roots.length) {
	    roots[roots.length-1].unread_children++;
	    }
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
    var rows = this.message_list.rows;    
    var tbody = this.message_list.list.tBodies[0];
    var row = tbody.firstChild;
    var cnt = this.env.pagesize + 1;
    
    while (row) {
      if (row.nodeType == 1 && (r = rows[row.uid])) {
	if (!r.depth && cnt)
	  cnt--;

        if (!cnt)
	  this.message_list.remove_row(row.uid);
	}
	row = row.nextSibling;
      }
  }

  // set message icon
  this.set_message_icon = function(uid)
  {
    var icn_src;
    var rows = this.message_list.rows;

    if (!rows[uid])
      return false;
    if (!rows[uid].unread && rows[uid].unread_children && this.env.unreadchildrenicon) {
      icn_src = this.env.unreadchildrenicon;
    }
    else if (rows[uid].deleted && this.env.deletedicon)
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

//    this.env.messages[uid] = rows[uid];
    }

  // set message row status, class and icon
  this.set_message = function(uid, flag, status)
    {
    var rows = this.message_list.rows;

    if (!rows[uid]) return false;
    
    if (flag)
      this.set_message_status(uid, flag, status);

    var rowobj = $(rows[uid].obj);

    if (rows[uid].unread && !rowobj.hasClass('unread'))
      rowobj.addClass('unread');
    else if (!rows[uid].unread && rowobj.hasClass('unread'))
      rowobj.removeClass('unread');
    
    if (rows[uid].deleted && !rowobj.hasClass('deleted'))
      rowobj.addClass('deleted');
    else if (!rows[uid].deleted && rowobj.hasClass('deleted'))
      rowobj.removeClass('deleted');

    if (rows[uid].flagged && !rowobj.hasClass('flagged'))
      rowobj.addClass('flagged');
    else if (!rows[uid].flagged && rowobj.hasClass('flagged'))
      rowobj.removeClass('flagged');

    this.set_unread_children(uid);
    this.set_message_icon(uid);
    };

  // sets unroot (unread_children) class of parent row
  this.set_unread_children = function(uid)
    {
    var row = this.message_list.rows[uid];
    
    if (row.parent_uid || !row.has_children)
      return;

    if (!row.unread && row.unread_children && !row.expanded)
      $(row.obj).addClass('unroot');
    else
      $(row.obj).removeClass('unroot');
    };

  // copy selected messages to the specified mailbox
  this.copy_messages = function(mbox)
    {
    // exit if current or no mailbox specified or if selection is empty
    if (!mbox || mbox == this.env.mailbox || (!this.env.uid && (!this.message_list || !this.message_list.get_selection().length)))
      return;

    var add_url = '&_target_mbox='+urlencode(mbox)+'&_from='+(this.env.action ? this.env.action : '');
    var a_uids = new Array();

    if (this.env.uid)
      a_uids[0] = this.env.uid;
    else
    {
      var selection = this.message_list.get_selection();
      var id;
      for (var n=0; n<selection.length; n++) {
        id = selection[n];
        a_uids[a_uids.length] = id;
      }
    }

    // send request to server
    this.http_post('copy', '_uid='+a_uids.join(',')+'&_mbox='+urlencode(this.env.mailbox)+add_url, false);
    };

  // move selected messages to the specified mailbox
  this.move_messages = function(mbox)
    {
    if (mbox && typeof mbox == 'object')
      mbox = mbox.id;
      
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
    else
      this.show_contentframe(false);

    // Hide message command buttons until a message is selected
    this.enable_command('reply', 'reply-all', 'forward', 'delete', 'mark', 'print', 'open', 'edit', 'viewsource', 'download', false);

    this._with_selected_messages('moveto', lock, add_url);
    };

  // delete selected messages from the current mailbox
  this.delete_messages = function()
  {
    var selection = this.message_list ? $.merge([], this.message_list.get_selection()) : new Array();

    // exit if no mailbox specified or if selection is empty
    if (!this.env.uid && !selection.length)
      return;
      
    // also select childs of collapsed rows
    for (var uid, i=0; i < selection.length; i++) {
      uid = selection[i];
      if (this.message_list.rows[uid].has_children && !this.message_list.rows[uid].expanded)
        this.message_list.select_childs(uid);
    }
    
    // if config is set to flag for deletion
    if (this.env.flag_for_deletion) {
      this.mark_message('delete');
      return false;
    }
    // if there isn't a defined trash mailbox or we are in it
    else if (!this.env.trash_mailbox || this.env.mailbox == this.env.trash_mailbox) 
      this.permanently_remove_messages();
    // if there is a trash mailbox defined and we're not currently in it
    else {
      // if shift was pressed delete it immediately
      if (this.message_list && this.message_list.shiftkey) {
        if (confirm(this.get_label('deletemessagesconfirm')))
          this.permanently_remove_messages();
      }
      else
        this.move_messages(this.env.trash_mailbox);
    }

    return true;
  };

  // delete the selected messages permanently
  this.permanently_remove_messages = function()
    {
    // exit if no mailbox specified or if selection is empty
    if (!this.env.uid && (!this.message_list || !this.message_list.get_selection().length))
      return;
      
    this.show_contentframe(false);
    this._with_selected_messages('delete', false, '&_from='+(this.env.action ? this.env.action : ''));
    };

  // Send a specifc moveto/delete request with UIDs of all selected messages
  // @private
  this._with_selected_messages = function(action, lock, add_url)
  {
    var a_uids = new Array(),
      count = 0;

    if (this.env.uid)
      a_uids[0] = this.env.uid;
    else
    {
      var selection = this.message_list.get_selection();
      var id;
      for (var n=0; n<selection.length; n++) {
        id = selection[n];
        a_uids[a_uids.length] = id;
        count += this.update_thread(id);
        this.message_list.remove_row(id, (this.env.display_next && n == selection.length-1));
      }
      // make sure there are no selected rows
      if (!this.env.display_next)
        this.message_list.clear_selection();
    }

    // also send search request to get the right messages 
    if (this.env.search_request) 
      add_url += '&_search='+this.env.search_request;

    if (this.env.display_next && this.env.next_uid)
      add_url += '&_next_uid='+this.env.next_uid;

    if (count < 0)
      add_url += '&_count='+(count*-1);
    else if (count > 0) 
      // remove threads from the end of the list
      this.delete_excessive_thread_rows();

    add_url += '&_uid='+this.uids_to_list(a_uids);

    // send request to server
    this.http_post(action, '_mbox='+urlencode(this.env.mailbox)+add_url, lock);
  };

  // set a specific flag to one or more messages
  this.mark_message = function(flag, uid)
    {
    var a_uids = new Array(),
      r_uids = new Array(),
      selection = this.message_list ? this.message_list.get_selection() : new Array();

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
    if (!r_uids.length && !this.select_all_mode)
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

    this.http_post('mark', '_uid='+this.uids_to_list(a_uids)+'&_flag='+flag);

    for (var i=0; i<a_uids.length; i++)
      this.update_thread_root(a_uids[i], flag);
  };

  // set image to flagged or unflagged
  this.toggle_flagged_status = function(flag, a_uids)
  {
    // mark all message rows as flagged/unflagged
    for (var i=0; i<a_uids.length; i++)
      this.set_message(a_uids[i], 'flagged', (flag=='flagged' ? true : false));

    this.http_post('mark', '_uid='+this.uids_to_list(a_uids)+'&_flag='+flag);
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
    for (var uid, i=0; i<a_uids.length; i++)
    {
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
    for (var i=0; i<a_uids.length; i++)
      this.set_message(a_uids[i], 'deleted', false);

    this.http_post('mark', '_uid='+this.uids_to_list(a_uids)+'&_flag=undelete');
    return true;
  };

  this.flag_as_deleted = function(a_uids)
  {
    var add_url = '',
      r_uids = new Array(),
      rows = this.message_list ? this.message_list.rows : new Array(),
      count = 0;

    for (var i=0; i<a_uids.length; i++) {
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
        add_url += '&_count='+(count*-1);
      else if (count > 0) 
        // remove threads from the end of the list
        this.delete_excessive_thread_rows();
    }

    add_url = '&_from='+(this.env.action ? this.env.action : '');
    
    // ??
    if (r_uids.length)
      add_url += '&_ruid='+this.uids_to_list(r_uids);

    if (this.env.skip_deleted) {
      // also send search request to get the right messages 
      if (this.env.search_request) 
        add_url += '&_search='+this.env.search_request;
      if (this.env.display_next && this.env.next_uid)
        add_url += '&_next_uid='+this.env.next_uid;
    }
    
    this.http_post('mark', '_uid='+this.uids_to_list(a_uids)+'&_flag=delete'+add_url);
    return true;  
  };

  // flag as read without mark request (called from backend)
  // argument should be a coma-separated list of uids
  this.flag_deleted_as_read = function(uids)
  {
    var icn_src, uid,
      rows = this.message_list ? this.message_list.rows : new Array(),
      str = String(uids),
      a_uids = str.split(',');

    for (var i=0; i<a_uids.length; i++) {
      uid = a_uids[i];
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
  

  /*********************************************************/
  /*********       mailbox folders methods         *********/
  /*********************************************************/

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
  
  // init message compose form: set focus and eventhandlers
  this.init_messageform = function()
  {
    if (!this.gui_objects.messageform)
      return false;
    
    //this.messageform = this.gui_objects.messageform;
    var input_from = $("[name='_from']");
    var input_to = $("[name='_to']");
    var input_subject = $("input[name='_subject']");
    var input_message = $("[name='_message']").get(0);
    var html_mode = $("input[name='_is_html']").val() == '1';

    // init live search events
    this.init_address_input_events(input_to);
    this.init_address_input_events($("[name='_cc']"));
    this.init_address_input_events($("[name='_bcc']"));
    
    if (!html_mode)
      this.set_caret_pos(input_message, this.env.top_posting ? 0 : $(input_message).val().length);

    // add signature according to selected identity
    if (input_from.attr('type') == 'select-one' && $("input[name='_draft_saveid']").val() == ''
        && !html_mode) {  // if we have HTML editor, signature is added in callback
      this.change_identity(input_from[0]);
    }
    else if (!html_mode) 
      this.set_caret_pos(input_message, this.env.top_posting ? 0 : $(input_message).val().length);

    if (input_to.val() == '')
      input_to.focus();
    else if (input_subject.val() == '')
      input_subject.focus();
    else if (input_message && !html_mode)
      input_message.focus();

    // get summary of all field values
    this.compose_field_hash(true);
 
    // start the auto-save timer
    this.auto_save_start();
  };

  this.init_address_input_events = function(obj)
  {
    var handler = function(e){ return ref.ksearch_keypress(e,this); };
    obj.bind((bw.safari || bw.ie ? 'keydown' : 'keypress'), handler);
    obj.attr('autocomplete', 'off');
  };

  // checks the input fields before sending a message
  this.check_compose_input = function()
  {
    // check input fields
    var input_to = $("[name='_to']");
    var input_cc = $("[name='_cc']");
    var input_bcc = $("[name='_bcc']");
    var input_from = $("[name='_from']");
    var input_subject = $("[name='_subject']");
    var input_message = $("[name='_message']");

    // check sender (if have no identities)
    if (input_from.attr('type') == 'text' && !rcube_check_email(input_from.val(), true))
      {
      alert(this.get_label('nosenderwarning'));
      input_from.focus();
      return false;
      }

    // check for empty recipient
    var recipients = input_to.val() ? input_to.val() : (input_cc.val() ? input_cc.val() : input_bcc.val());
    if (!rcube_check_email(recipients.replace(/^\s+/, '').replace(/[\s,;]+$/, ''), true))
      {
      alert(this.get_label('norecipientwarning'));
      input_to.focus();
      return false;
      }

    // check if all files has been uploaded
    for (var key in this.env.attachments) {
      if (typeof this.env.attachments[key] == 'object' && !this.env.attachments[key].complete) {
        alert(this.get_label('notuploadedwarning'));
        return false;
      }
    }
    
    // display localized warning for missing subject
    if (input_subject.val() == '') {
      var subject = prompt(this.get_label('nosubjectwarning'), this.get_label('nosubject'));

      // user hit cancel, so don't send
      if (!subject && subject !== '') {
        input_subject.focus();
        return false;
      }
      else
        input_subject.val((subject ? subject : this.get_label('nosubject')));
    }

    // check for empty body
    if ((!window.tinyMCE || !tinyMCE.get(this.env.composebody))
        && input_message.val() == '' && !confirm(this.get_label('nobodywarning'))) {
      input_message.focus();
      return false;
    }
    else if (window.tinyMCE && tinyMCE.get(this.env.composebody)
        && !tinyMCE.get(this.env.composebody).getContent()
        && !confirm(this.get_label('nobodywarning'))) {
      tinyMCE.get(this.env.composebody).focus();
      return false;
    }

    // Apply spellcheck changes if spell checker is active
    this.stop_spellchecking();

    // move body from html editor to textarea (just to be sure, #1485860)
    if (window.tinyMCE && tinyMCE.get(this.env.composebody))
      tinyMCE.triggerSave();

    return true;
  };

  this.stop_spellchecking = function()
  {
    if (this.env.spellcheck && !this.spellcheck_ready) {
      $(this.env.spellcheck.spell_span).trigger('click');
      this.set_spellcheck_state('ready');
    }
  };

  this.display_spellcheck_controls = function(vis)
  {
    if (this.env.spellcheck) {
      // stop spellchecking process
      if (!vis)
        this.stop_spellchecking();

      $(this.env.spellcheck.spell_container).css('visibility', vis ? 'visible' : 'hidden');
      }
  };

  this.set_spellcheck_state = function(s)
    {
    this.spellcheck_ready = (s == 'ready' || s == 'no_error_found');
    this.enable_command('spellcheck', this.spellcheck_ready);
    };

  this.set_draft_id = function(id)
    {
    $("input[name='_draft_saveid']").val(id);
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
    var value_to = $("[name='_to']").val();
    var value_cc = $("[name='_cc']").val();
    var value_bcc = $("[name='_bcc']").val();
    var value_subject = $("[name='_subject']").val();
    var str = '';
    
    if (value_to)
      str += value_to+':';
    if (value_cc)
      str += value_cc+':';
    if (value_bcc)
      str += value_bcc+':';
    if (value_subject)
      str += value_subject+':';
    
    var editor = tinyMCE.get(this.env.composebody);
    if (editor)
      str += editor.getContent();
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

    var id = obj.options[obj.selectedIndex].value;
    var input_message = $("[name='_message']");
    var message = input_message.val();
    var is_html = ($("input[name='_is_html']").val() == '1');
    var sig_separator = this.env.sig_above && (this.env.compose_mode == 'reply' || this.env.compose_mode == 'forward') ? '---' : '-- ';
    var sig, cursor_pos, p = -1;

    if (!this.env.identity)
      this.env.identity = id
  
    // enable manual signature insert
    if (this.env.signatures && this.env.signatures[id])
      this.enable_command('insert-sig', true);
    else
      this.enable_command('insert-sig', false);

    if (!is_html) {
      // remove the 'old' signature
      if (show_sig && this.env.identity && this.env.signatures && this.env.signatures[this.env.identity]) {
        sig = this.env.signatures[this.env.identity].is_html ? this.env.signatures[this.env.identity].plain_text : this.env.signatures[this.env.identity].text;
        sig = sig.replace(/\r\n/, '\n');

        if (!sig.match(/^--[ -]\n/))
          sig = sig_separator + '\n' + sig;

        p = this.env.sig_above ? message.indexOf(sig) : message.lastIndexOf(sig);
        if (p >= 0)
          message = message.substring(0, p) + message.substring(p+sig.length, message.length);
      }
      // add the new signature string
      if (show_sig && this.env.signatures && this.env.signatures[id]) {
        sig = this.env.signatures[id]['is_html'] ? this.env.signatures[id]['plain_text'] : this.env.signatures[id]['text'];
        sig = sig.replace(/\r\n/, '\n');

        if (!sig.match(/^--[ -]\n/))
          sig = sig_separator + '\n' + sig;

        if (this.env.sig_above) {
          if (p >= 0) { // in place of removed signature
            message = message.substring(0, p) + sig + message.substring(p, message.length);
            cursor_pos = p - 1;
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
    else if (is_html && show_sig && this.env.signatures) {  // html
      var editor = tinyMCE.get(this.env.composebody);
      var sigElem = editor.dom.get('_rc_sig');

      // Append the signature as a div within the body
      if (!sigElem) {
        var body = editor.getBody();
        var doc = editor.getDoc();
          
        sigElem = doc.createElement('div');
        sigElem.setAttribute('id', '_rc_sig');
          
        if (this.env.sig_above) {
          // if no existing sig and top posting then insert at caret pos
          editor.getWin().focus(); // correct focus in IE
            
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

      if (this.env.signatures[id]) {
        if (this.env.signatures[id].is_html) {
          sig = this.env.signatures[id].text;
          if (!this.env.signatures[id].plain_text.match(/^--[ -]\r?\n/))
            sig = sig_separator + '<br />' + sig;
        }
        else {
          sig = this.env.signatures[id].text;
          if (!sig.match(/^--[ -]\r?\n/))
            sig = sig_separator + '\n' + sig;
          sig = '<pre>' + sig + '</pre>';
        }

        sigElem.innerHTML = sig;
      }
    }

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
      if (a && (list = this.gui_objects.attachmentlist))
        {
        var pos = $(list).offset();
        elm.style.top = (pos.top + list.offsetHeight + 10) + 'px';
        elm.style.left = pos.left + 'px';
        }

      $(elm).toggle();
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
        var frame = document.createElement('iframe');
        frame.name = frame_name;
        frame.style.border = 'none';
        frame.style.width = 0;
        frame.style.height = 0;
        frame.style.visibility = 'hidden';
        document.body.appendChild(frame);
        }

      // handle upload errors, parsing iframe content in onload
      var fr = document.getElementsByName(frame_name)[0];
      $(fr).bind('load', {ts:ts}, function(e) {
        var content = '';
        try {
          if (this.contentDocument) {
            var d = this.contentDocument;
          } else if (this.contentWindow) {
            var d = this.contentWindow.document;
          }
          content = d.childNodes[0].innerHTML;
        } catch (e) {}

        if (!String(content).match(/add2attachment/) && (!bw.opera || (rcmail.env.uploadframe && rcmail.env.uploadframe == e.data.ts))) {
          rcmail.display_message(rcmail.get_label('fileuploaderror'), 'error');
          rcmail.remove_from_attachment_list(e.data.ts);
        }
        // Opera hack: handle double onload
        if (bw.opera)
          rcmail.env.uploadframe = e.data.ts;
      });

      form.target = frame_name;
      form.action = this.env.comm_path+'&_action=upload&_uploadid='+ts;
      form.setAttribute('enctype', 'multipart/form-data');
      form.submit();
      
      // hide upload form
      this.show_attachment_form(false);
      // display upload indicator and cancel button
      var content = this.get_label('uploading');
      if (this.env.loadingicon)
        content = '<img src="'+this.env.loadingicon+'" alt="" />'+content;
      if (this.env.cancelicon)
        content = '<a title="'+this.get_label('cancel')+'" onclick="return rcmail.cancel_attachment_upload(\''+ts+'\', \''+frame_name+'\');" href="#cancelupload"><img src="'+this.env.cancelicon+'" alt="" /></a>'+content;
      this.add2attachment_list(ts, { name:'', html:content, complete:false });
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
    
    var li = $('<li>').attr('id', name).html(att.html);
    var indicator;
    
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
    if (this.env.attachments[name])
      delete this.env.attachments[name];
    
    if (!this.gui_objects.attachmentlist)
      return false;

    var list = this.gui_objects.attachmentlist.getElementsByTagName("li");
    for (i=0;i<list.length;i++)
      if (list[i].id == name)
        this.gui_objects.attachmentlist.removeChild(list[i]);
  };

  this.remove_attachment = function(name)
    {
    if (name && this.env.attachments[name])
      this.http_post('remove-attachment', '_file='+urlencode(name));

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

  // send remote request to add a new contact
  this.add_contact = function(value)
    {
    if (value)
      this.http_post('addcontact', '_address='+value);
    
    return true;
    };

  // send remote request to search mail or contacts
  this.qsearch = function(value)
    {
    if (value != '')
      {
      var addurl = '';
      if (this.message_list) {
        this.message_list.clear();
        if (this.env.search_mods) {
          var mods = this.env.search_mods[this.env.mailbox] ? this.env.search_mods[this.env.mailbox] : this.env.search_mods['*'];
          if (mods) {
            var head_arr = new Array();
            for (var n in mods)
              head_arr.push(n);
            addurl += '&_headers='+head_arr.join(',');
            }
          }
        } else if (this.contact_list) {
        this.contact_list.clear(true);
        this.show_contentframe(false);
        }

      if (this.gui_objects.search_filter)
        addurl += '&_filter=' + this.gui_objects.search_filter.value;

      // reset vars
      this.env.current_page = 1;
      this.set_busy(true, 'searching');
      this.http_request('search', '_q='+urlencode(value)
        + (this.env.mailbox ? '&_mbox='+urlencode(this.env.mailbox) : '')
        + (this.env.source ? '&_source='+urlencode(this.env.source) : '')
        + (this.env.group ? '&_gid='+urlencode(this.env.group) : '')
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
          highlight = this.ksearch_pane.__ul.firstChild;
        
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
    if (!this.env.contacts[id] || !this.ksearch_input)
      return;
    
    // get cursor pos
    var inp_value = this.ksearch_input.value;
    var cpos = this.get_caret_pos(this.ksearch_input);
    var p = inp_value.lastIndexOf(this.ksearch_value, cpos);

    // replace search string with full address
    var pre = this.ksearch_input.value.substring(0, p);
    var end = this.ksearch_input.value.substring(p+this.ksearch_value.length, this.ksearch_input.value.length);
    var insert = '';
    
    // insert all members of a group
    if (typeof this.env.contacts[id] == 'object' && this.env.contacts[id].members) {
      for (var i=0; i < this.env.contacts[id].members.length; i++)
        insert += this.env.contacts[id].members[i] + ', ';
    }
    else if (typeof this.env.contacts[id] == 'string')
      insert = this.env.contacts[id] + ', ';

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
      
    if (this.ksearch_pane && this.ksearch_pane.is(":visible"))
      this.ksearch_pane.hide();

    // get string from current cursor pos to last comma
    var cpos = this.get_caret_pos(this.ksearch_input);
    var p = inp_value.lastIndexOf(',', cpos-1);
    var q = inp_value.substring(p+1, cpos);

    // trim query string
    q = q.replace(/(^\s+|\s+$)/g, '');

    // Don't (re-)search if the last results are still active
    if (q == this.ksearch_value)
      return;
    
    var old_value = this.ksearch_value;
    this.ksearch_value = q;
    
    // ...string is empty
    if (!q.length)
      return;

    // ...new search value contains old one and previous search result was empty
    if (old_value && old_value.length && this.env.contacts && !this.env.contacts.length && q.indexOf(old_value) == 0)
      return;
    
    this.display_message(this.get_label('searching'), 'loading', true);
    this.http_post('autocomplete', '_search='+urlencode(q));
  };

  this.ksearch_query_results = function(results, search)
  {
    // ignore this outdated search response
    if (this.ksearch_value && search != this.ksearch_value)
      return;
      
    this.hide_message();
    this.env.contacts = results ? results : [];
    this.ksearch_display_results(this.env.contacts);
  };

  this.ksearch_display_results = function (a_results)
  {
    // display search results
    if (a_results.length && this.ksearch_input && this.ksearch_value) {
      var p, ul, li, text, s_val = this.ksearch_value;
      
      // create results pane if not present
      if (!this.ksearch_pane) {
        ul = $('<ul>');
        this.ksearch_pane = $('<div>').attr('id', 'rcmKSearchpane').css({ position:'absolute', 'z-index':30000 }).append(ul).appendTo(document.body);
        this.ksearch_pane.__ul = ul[0];
      }

      // remove all search results
      ul = this.ksearch_pane.__ul;
      ul.innerHTML = '';

      // add each result line to list
      for (i=0; i < a_results.length; i++) {
        text = typeof a_results[i] == 'object' ? a_results[i].name : a_results[i];
        li = document.createElement('LI');
        li.innerHTML = text.replace(new RegExp('('+s_val+')', 'ig'), '##$1%%').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/##([^%]+)%%/g, '<b>$1</b>');
        li.onmouseover = function(){ ref.ksearch_select(this); };
        li.onmouseup = function(){ ref.ksearch_click(this) };
        li._rcm_id = i;
        ul.appendChild(li);
      }

      // select the first
      $(ul.firstChild).attr('id', 'rcmksearchSelected').addClass('selected');
      this.ksearch_selected = 0;

      // move the results pane right under the input box and make it visible
      var pos = $(this.ksearch_input).offset();
      this.ksearch_pane.css({ left:pos.left+'px', top:(pos.top + this.ksearch_input.offsetHeight)+'px' }).show();
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
      this.ksearch_pane.hide();
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

  this.list_contacts = function(src, group, page)
    {
    var add_url = '';
    var target = window;
    
    // currently all groups belong to the local address book
    if (group)
      src = 0;
    
    if (!src)
      src = this.env.source;
    
    if (page && this.current_page == page && src == this.env.source && group == this.env.group)
      return false;
      
    if (src != this.env.source)
      {
      page = 1;
      this.env.current_page = page;
      this.reset_qsearch();
      }
    else if (group != this.env.group)
      page = this.env.current_page = 1;

    this.select_folder(src, this.env.source);
    this.select_folder(group, this.env.group, 'rcmliG');
    
    this.env.source = src;
    this.env.group = group;

    // load contacts remotely
    if (this.gui_objects.contactslist)
      {
      this.list_contacts_remote(src, group, page);
      return;
      }

    if (this.env.contentframe && window.frames && window.frames[this.env.contentframe])
      {
      target = window.frames[this.env.contentframe];
      add_url = '&_framed=1';
      }
      
    if (group)
      add_url += '&_gid='+group;
    if (page)
      add_url += '&_page='+page;

    // also send search request to get the correct listing
    if (this.env.search_request)
      add_url += '&_search='+this.env.search_request;

    this.set_busy(true, 'loading');
    target.location.href = this.env.comm_path + (src ? '&_source='+urlencode(src) : '') + add_url;
    };

  // send remote request to load contacts list
  this.list_contacts_remote = function(src, group, page)
    {
    // clear message list first
    this.contact_list.clear(true);
    this.show_contentframe(false);
    this.enable_command('delete', 'compose', false);

    // send request to server
    var url = (src ? '_source='+urlencode(src) : '') + (page ? (src?'&':'') + '_page='+page : '');
    this.env.source = src;
    this.env.group = group;
    
    if (group)
      url += '&_gid='+group;
    
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

    if (to.type == 'group')
      this.http_post('group-addmembers', '_cid='+urlencode(cid)+'&_source='+urlencode(this.env.source)+'&_gid='+urlencode(to.id));
    else if (to.id != this.env.source && cid && this.env.address_sources[to.id] && !this.env.address_sources[to.id].readonly)
      this.http_post('copy', '_cid='+urlencode(cid)+'&_source='+urlencode(this.env.source)+'&_to='+urlencode(to.id));
    };


  this.delete_contacts = function()
    {
    // exit if no mailbox specified or if selection is empty
    var selection = this.contact_list.get_selection();
    if (!(selection.length || this.env.cid) || (!this.env.group && !confirm(this.get_label('deletecontactconfirm'))))
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
    if (this.env.group)
      this.http_post('group-delmembers', '_cid='+urlencode(a_cids.join(','))+'&_source='+urlencode(this.env.source)+'&_gid='+urlencode(this.env.group)+qs);
    else
      this.http_post('delete', '_cid='+urlencode(a_cids.join(','))+'&_source='+urlencode(this.env.source)+'&_from='+(this.env.action ? this.env.action : '')+qs);
      
    return true;
    };

  // update a contact record in the list
  this.update_contact_row = function(cid, cols_arr, newcid)
  {
    var row;
    if (this.contact_list.rows[cid] && (row = this.contact_list.rows[cid].obj)) {
      for (var c=0; c<cols_arr.length; c++)
        if (row.cells[c])
          $(row.cells[c]).html(cols_arr[c]);

      // cid change
      if (newcid) {
        row.id = 'rcmrow' + newcid;
        this.contact_list.remove_row(cid);
        this.contact_list.init_row(row);
        this.contact_list.selection[0] = newcid;
        ow.style.display = '';
      }

      return true;
    }

    return false;
  };

  // add row to contacts list
  this.add_contact_row = function(cid, cols, select)
    {
    if (!this.gui_objects.contactslist || !this.gui_objects.contactslist.tBodies[0])
      return false;
    
    var tbody = this.gui_objects.contactslist.tBodies[0];
    var rowcount = tbody.rows.length;
    var even = rowcount%2;
    
    var row = document.createElement('tr');
    row.id = 'rcmrow'+cid;
    row.className = 'contact '+(even ? 'even' : 'odd');
	    
    if (this.contact_list.in_selection(cid))
      row.className += ' selected';

    // add each submitted col
    for (var c in cols) {
      col = document.createElement('td');
      col.className = String(c).toLowerCase();
      col.innerHTML = cols[c];
      row.appendChild(col);
    }
    
    this.contact_list.insert_row(row);
    
    this.enable_command('export', (this.contact_list.rowcount > 0));
    };
  
  
  this.add_contact_group = function()
  {
    if (!this.gui_objects.folderlist || !this.env.address_sources[this.env.source].groups)
      return;
      
    if (!this.name_input) {
      this.name_input = document.createElement('input');
      this.name_input.type = 'text';
      this.name_input.onkeypress = function(e){ return rcmail.add_input_keypress(e); };
    
      this.gui_objects.folderlist.parentNode.appendChild(this.name_input);
    }
    
    this.name_input.select();
  };
  
  this.rename_contact_group = function()
  {
    if (!this.env.group || !this.gui_objects.folderlist)
      return;
    
    if (!this.name_input) {
      this.name_input = document.createElement('input');
      this.name_input.type = 'text';
      this.name_input.value = this.env.contactgroups['G'+this.env.group].name;
      this.name_input.onkeypress = function(e){ return rcmail.add_input_keypress(e); };
      this.env.group_renaming = true;

      var link, li = this.get_folder_li(this.env.group, 'rcmliG');
      if (li && (link = li.firstChild)) {
        $(link).hide();
        li.insertBefore(this.name_input, link);
      }
    }

    this.name_input.select();
  };
  
  this.delete_contact_group = function()
  {
    if (this.env.group)
      this.http_post('group-delete', '_source='+urlencode(this.env.source)+'&_gid='+urlencode(this.env.group), true);
  };
  
  // callback from server upon group-delete command
  this.remove_group_item = function(id)
  {
    var li, key = 'G'+id;
    if ((li = this.get_folder_li(key))) {
      li.parentNode.removeChild(li);
      delete this.env.contactfolders[key];
      delete this.env.contactgroups[key];
    }
  };
  
  // handler for keyboard events on the input field
  this.add_input_keypress = function(e)
  {
    var key = rcube_event.get_keycode(e);

    // enter
    if (key == 13) {
      var newname = this.name_input.value;
      
      if (newname) {
        this.set_busy(true, 'loading');
        if (this.env.group_renaming)
          this.http_post('group-rename', '_source='+urlencode(this.env.source)+'&_gid='+urlencode(this.env.group)+'&_name='+urlencode(newname), true);
        else
          this.http_post('group-create', '_source='+urlencode(this.env.source)+'&_name='+urlencode(newname), true);
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
        var li = this.name_input.parentNode;
        $(li.lastChild).show();
        this.env.group_renaming = false;
      }
      
      this.name_input.parentNode.removeChild(this.name_input);
      this.name_input = null;
    }
  };
  
  // callback for creating a new contact group
  this.insert_contact_group = function(prop)
  {
    this.reset_add_input();
    
    prop.type = 'group';
    var key = 'G'+prop.id;
    this.env.contactfolders[key] = this.env.contactgroups[key] = prop;

    var link = $('<a>').attr('href', '#').attr('onclick', "return rcmail.command('listgroup','"+prop.id+"',this)").html(prop.name);
    var li = $('<li>').attr('id', 'rcmli'+key).addClass('contactgroup').append(link);
    $(this.gui_objects.folderlist).append(li);
  };
  
  // callback for renaming a contact group
  this.update_contact_group = function(id, name)
  {
    this.reset_add_input();
    
    var key = 'G'+id;
    var link, li = this.get_folder_li(key);
    if (li && (link = li.firstChild) && link.tagName.toLowerCase() == 'a')
      link.innerHTML = name;
    
    this.env.contactfolders[key].name = this.env.contactgroups[key].name = name;
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
      var anchors = row.obj.getElementsByTagName('a');
      if (anchors[0])
        anchors[0].onclick = function() { p.rename_folder(row.id); return false; };
      if (anchors[1])
        anchors[1].onclick = function() { p.delete_folder(row.id); return false; };
      row.obj.onmouseover = function() { p.focus_subscription(row.id); };
      row.obj.onmouseout = function() { p.unfocus_subscription(row.id); };
      }
    this.subscription_list.init();
    }

  // preferences section select and load options frame
  this.section_select = function(list)
    {
    var id = list.get_single_selection();
    
    if (id) {
      var add_url = '';
      var target = window;
      this.set_busy(true);

      if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
        add_url = '&_framed=1';
        target = window.frames[this.env.contentframe];
        }
      target.location.href = this.env.comm_path+'&_action=edit-prefs&_section='+id+add_url;
      }

    return true;
    };

  this.identity_select = function(list)
    {
    var id;
    if (id = list.get_single_selection())
      this.load_identity(id, 'edit-identity');
    };

  // load identity record
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

    // append token to request
    this.goto_url('delete-identity', '_iid='+id+'&_token='+this.env.request_token, true);
    
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
          $(row).addClass('droptarget');
          }
        }
      else if (this.env.folder.match(new RegExp(RegExp.escape(this.env.delimiter))))
        {
        this.set_env('dstfolder', this.env.delimiter);
        $(this.subscription_list.frame).addClass('droptarget');
        }
    }

  this.unfocus_subscription = function(id)
    {
      var row = $('#'+id);
      this.set_env('dstfolder', null);
      if (this.env.subscriptionrows[id] && row[0])
        row.removeClass('droptarget');
      else
        $(this.subscription_list.frame).removeClass('droptarget');
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
      $(this.gui_objects.createfolderhint).html(this.env.folder ? this.get_label('addsubfolderhint') : '');
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
      this.name_input = document.createElement('input');
      this.name_input.type = 'text';
      this.name_input.value = this.env.subscriptionrows[id][0].replace(reg, '');

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
      $(cell).html(this.env.subscriptionrows[this.edit_folder][1]);
      
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

      $(this.gui_objects.createfolderhint).html('');
      }
    };

  // add a new folder to the subscription list by cloning a folder row
  this.add_folder_row = function(name, display_name, replace, before)
    {
    if (!this.gui_objects.subscriptionlist)
      return false;

    // find not protected folder
    var refid;
    for (var rid in this.env.subscriptionrows)
      if (this.env.subscriptionrows[rid]!=null && !this.env.subscriptionrows[rid][2]) {
        refid = rid;
        break;
      }

    var refrow, form;
    var tbody = this.gui_objects.subscriptionlist.tBodies[0];
    var id = 'rcmrow'+(tbody.childNodes.length+1);
    var selection = this.subscription_list.get_single_selection();
    
    if (replace && replace.id)
    {
      id = replace.id;
      refid = replace.id;
    }

    if (!id || !refid || !(refrow = document.getElementById(refid)))
      {
      // Refresh page if we don't have a table row to clone
      this.goto_url('folders');
      return false;
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

    if (!replace && row.cells[2] && row.cells[2].firstChild.tagName.toLowerCase()=='input')
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

  this.enable_threading = function(folder)
    {
    if (folder)
      this.http_post('enable-threading', '_mbox='+urlencode(folder));
    };

  this.disable_threading = function(folder)
    {
    if (folder)
      this.http_post('disable-threading', '_mbox='+urlencode(folder));
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
    var new_row = document.createElement('tr');
    for(var n=0; n<row.cells.length; n++)
      {
      cell = row.cells[n];
      td = document.createElement('td');

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
  };
  
  // set event handlers on registered buttons
  this.init_buttons = function()
  {
    for (var cmd in this.buttons) {
      if (typeof cmd != 'string')
        continue;
      
      for (var i=0; i< this.buttons[cmd].length; i++) {
        var prop = this.buttons[cmd][i];
        var elm = document.getElementById(prop.id);
        if (!elm)
          continue;

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
      }
    }
  };

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
    var a_buttons = this.buttons[command];
    var button, elm;

    if(!a_buttons || !a_buttons.length)
      return false;

    for(var n=0; n<a_buttons.length; n++)
    {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
      {
        elm = document.getElementById(button.id);
        if (elm && button.over) {
          if (button.type == 'image')
            elm.src = button.over;
          else
            elm.className = button.over;
        }
      }
    }
  };

  // mouse down on button
  this.button_sel = function(command, id)
  {
    var a_buttons = this.buttons[command];
    var button, elm;

    if(!a_buttons || !a_buttons.length)
      return;

    for(var n=0; n<a_buttons.length; n++)
    {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
      {
        elm = document.getElementById(button.id);
        if (elm && button.sel) {
          if (button.type == 'image')
            elm.src = button.sel;
          else
            elm.className = button.sel;
        }
        this.buttons_sel[id] = command;
      }
    }
  };

  // mouse out of button
  this.button_out = function(command, id)
  {
    var a_buttons = this.buttons[command];
    var button, elm;

    if(!a_buttons || !a_buttons.length)
      return;

    for(var n=0; n<a_buttons.length; n++)
    {
      button = a_buttons[n];
      if(button.id==id && button.status=='act')
      {
        elm = document.getElementById(button.id);
        if (elm && button.act) {
          if (button.type == 'image')
            elm.src = button.act;
          else
            elm.className = button.act;
        }
      }
    }
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

    var obj = $(this.gui_objects.message).html(cont).show();
    
    if (type!='loading')
      obj.bind('mousedown', function(){ ref.hide_message(); return true; });
    
    if (!hold)
      this.message_timer = window.setTimeout(function(){ ref.hide_message(true); }, this.message_time);
    };

  // make a message row disapear
  this.hide_message = function(fade)
    {
    if (this.gui_objects.message)
      $(this.gui_objects.message).unbind()[(fade?'fadeOut':'hide')]();
    };

  // mark a mailbox as selected and set environment variable
  this.select_folder = function(name, old, prefix)
  {
    if (this.gui_objects.folderlist)
    {
      var current_li, target_li;
      
      if ((current_li = this.get_folder_li(old, prefix))) {
        $(current_li).removeClass('selected').removeClass('unfocused');
      }
      if ((target_li = this.get_folder_li(name, prefix))) {
        $(target_li).removeClass('unfocused').addClass('selected');
      }
      
      // trigger event hook
      this.triggerEvent('selectfolder', { folder:name, old:old, prefix:prefix });
    }
  };

  // helper method to find a folder list item
  this.get_folder_li = function(name, prefix)
  {
    if (!prefix)
      prefix = 'rcmli';
    if (this.gui_objects.folderlist)
    {
      name = String(name).replace(this.identifier_expr, '_');
      return document.getElementById(prefix+name);
    }

    return null;
  };

  // for reordering column array (Konqueror workaround)
  // and for setting some message list global variables
  this.set_message_coltypes = function(coltypes, repl)
  { 
    this.env.coltypes = coltypes;
    
    // set correct list titles
    var thead = this.gui_objects.messagelist ? this.gui_objects.messagelist.tHead : null;

    // replace old column headers
    if (thead && repl) {
      for (var cell, c=0; c < repl.length; c++) {
        cell = thead.rows[0].cells[c];
        if (!cell) {
          cell = document.createElement('td');
          thead.rows[0].appendChild(cell);
        }
        cell.innerHTML = repl[c].html;
        if (repl[c].id) cell.id = repl[c].id;
        if (repl[c].className) cell.className = repl[c].className;
      }
    }

    var cell, col, n;
    for (n=0; thead && n<this.env.coltypes.length; n++)
      {
      col = this.env.coltypes[n];
      if ((cell = thead.rows[0].cells[n+1]) && (col=='from' || col=='to'))
        {
        // if we have links for sorting, it's a bit more complicated...
        if (cell.firstChild && cell.firstChild.tagName.toLowerCase()=='a')
          {
          cell.firstChild.innerHTML = this.get_label(this.env.coltypes[n]);
          cell.firstChild.onclick = function(){ return rcmail.command('sort', this.__col, this); };
          cell.firstChild.__col = col;
          }
        else
          cell.innerHTML = this.get_label(this.env.coltypes[n]);

        cell.id = 'rcm'+col;
        }
      }

    // remove excessive columns
    for (var i=n+1; thead && i<thead.rows[0].cells.length; i++)
      thead.rows[0].removeChild(thead.rows[0].cells[i]);

    this.env.subject_col = null;
    this.env.flagged_col = null;

    var found;
    if((found = jQuery.inArray('subject', this.env.coltypes)) >= 0) {
      this.set_env('subject_col', found);
      if (this.message_list)
        this.message_list.subject_col = found+1;
      }
    if((found = jQuery.inArray('flag', this.env.coltypes)) >= 0)
      this.set_env('flagged_col', found);
  };

  // replace content of row count display
  this.set_rowcount = function(text)
    {
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
    if (content && this.gui_objects.quotadisplay) {
      if (typeof(content) == 'object')
        this.percent_indicator(this.gui_objects.quotadisplay, content);
      else
        $(this.gui_objects.quotadisplay).html(content);
      }
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
          if (k.indexOf(mbox + this.env.delimiter) == 0)
            childcount += this.env.unread_counts[k];
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
      if ((mycount+childcount)>0)
        $(item).addClass('unread');
      else
        $(item).removeClass('unread');
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

  this.toggle_prefer_html = function(checkbox)
    {
    var addrbook_show_images;
    if (addrbook_show_images = document.getElementById('rcmfd_addrbook_show_images'))
      addrbook_show_images.disabled = !checkbox.checked;
    }

  // display fetched raw headers
  this.set_headers = function(content)
  {
    if (this.gui_objects.all_headers_row && this.gui_objects.all_headers_box && content) {
      $(this.gui_objects.all_headers_box).html(content).show();

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
    
    $(elem).removeClass('show-headers').addClass('hide-headers');
    $(this.gui_objects.all_headers_row).show();
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

    $(elem).removeClass('hide-headers').addClass('show-headers');
    $(this.gui_objects.all_headers_row).hide();
    elem.onclick = function() { rcmail.load_headers(elem); }
    }

  // percent (quota) indicator
  this.percent_indicator = function(obj, data)
    {
    if (!data || !obj)
      return false;

    var limit_high = 80;
    var limit_mid  = 55;
    var width = data.width ? data.width : this.env.indicator_width ? this.env.indicator_width : 100;
    var height = data.height ? data.height : this.env.indicator_height ? this.env.indicator_height : 14;
    var quota = data.percent ? Math.abs(parseInt(data.percent)) : 0;
    var quota_width = parseInt(quota / 100 * width);
    var pos = $(obj).position();

    this.env.indicator_width = width;
    this.env.indicator_height = height;
    
    // overlimit
    if (quota_width > width) {
      quota_width = width;
      quota = 100; 
      }
  
    // main div
    var main = $('<div>');
    main.css({position: 'absolute', top: pos.top, left: pos.left,
	width: width + 'px', height: height + 'px', zIndex: 100, lineHeight: height + 'px'})
	.attr('title', data.title).addClass('quota_text').html(quota + '%');
    // used bar
    var bar1 = $('<div>');
    bar1.css({position: 'absolute', top: pos.top + 1, left: pos.left + 1,
	width: quota_width + 'px', height: height + 'px', zIndex: 99});
    // background
    var bar2 = $('<div>');
    bar2.css({position: 'absolute', top: pos.top + 1, left: pos.left + 1,
	width: width + 'px', height: height + 'px', zIndex: 98})
	.addClass('quota_bg');

    if (quota >= limit_high) {
      main.addClass(' quota_text_high');
      bar1.addClass('quota_high');
      }
    else if(quota >= limit_mid) {
      main.addClass(' quota_text_mid');
      bar1.addClass('quota_mid');
      }
    else {
      main.addClass(' quota_text_normal');
      bar1.addClass('quota_low');
      }

    // replace quota image
    obj.innerHTML = '';
    $(obj).append(bar1).append(bar2).append(main);
    }

  /********************************************************/
  /*********  html to text conversion functions   *********/
  /********************************************************/

  this.html2plain = function(htmlText, id)
    {
    var url = this.env.bin_path+'html2text.php';
    var rcmail = this;

    this.set_busy(true, 'converting');
    console.log('HTTP POST: '+url);

    $.ajax({ type: 'POST', url: url, data: htmlText, contentType: 'application/octet-stream',
      error: function(o) { rcmail.http_error(o); },
      success: function(data) { rcmail.set_busy(false); $(document.getElementById(id)).val(data); console.log(data); }
      });
    }

  this.plain2html = function(plainText, id)
    {
    this.set_busy(true, 'converting');
    $(document.getElementById(id)).val('<pre>'+plainText+'</pre>');
    this.set_busy(false);
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

  // send a http request to the server
  this.http_request = function(action, querystring, lock)
  {
    querystring += (querystring ? '&' : '') + '_remote=1';
    var url = this.env.comm_path + '&_action=' + action + '&' + querystring
    
    // send request
    console.log('HTTP GET: ' + url);
    jQuery.get(url, { _unlock:(lock?1:0) }, function(data){ ref.http_response(data); }, 'json');
  };

  // send a http POST request to the server
  this.http_post = function(action, postdata, lock)
  {
    var url = this.env.comm_path+'&_action=' + action;
    
    if (postdata && typeof(postdata) == 'object') {
      postdata._remote = 1;
      postdata._unlock = (lock ? 1 : 0);
    }
    else
      postdata += (postdata ? '&' : '') + '_remote=1' + (lock ? '&_unlock=1' : '');

    // send request
    console.log('HTTP POST: ' + url);
    jQuery.post(url, postdata, function(data){ ref.http_response(data); }, 'json');
  };

  // handle HTTP response
  this.http_response = function(response)
  {
    var console_msg = '';
    
    if (response.unlock)
      this.set_busy(false);

    // set env vars
    if (response.env)
      this.set_env(response.env);

    // we have labels to add
    if (typeof response.texts == 'object') {
      for (var name in response.texts)
        if (typeof response.texts[name] == 'string')
          this.add_label(name, response.texts[name]);
    }

    // if we get javascript code from server -> execute it
    if (response.exec) {
      console.log(response.exec);
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
          var uid = this.contact_list.get_selection();
          this.enable_command('compose', (uid && this.contact_list.rows[uid]));
          this.enable_command('delete', 'edit', (uid && this.contact_list.rows[uid] && this.env.address_sources && !this.env.address_sources[this.env.source].readonly));
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));
        }
      
      case 'moveto':
        if (this.env.action == 'show') {
          // re-enable commands on move/delete error
          this.enable_command('reply', 'reply-all', 'forward', 'delete', 'mark', 'print', 'open', 'edit', 'viewsource', 'download', true);
        } else if (this.message_list)
          this.message_list.init();
        break;
        
      case 'purge':
      case 'expunge':
        if (!this.env.messagecount && this.task == 'mail') {
          // clear preview pane content
          if (this.env.contentframe)
            this.show_contentframe(false);
          // disable commands useless when mailbox is empty
          this.enable_command('show', 'reply', 'reply-all', 'forward', 'moveto', 'copy', 'delete', 
            'mark', 'viewsource', 'open', 'edit', 'download', 'print', 'load-attachment', 
            'purge', 'expunge', 'select-all', 'select-none', 'sort',
            'expand-all', 'expand-unread', 'collapse-all', false);
        }
        break;

      case 'check-recent':
      case 'getunread':
      case 'search':
      case 'list':
        if (this.task == 'mail') {
          if (this.message_list && (response.action == 'list' || response.action == 'search')) {
            this.msglist_select(this.message_list);
            this.expand_threads();
          }
          this.enable_command('show', 'expunge', 'select-all', 'select-none', 'sort', (this.env.messagecount > 0));
          this.enable_command('purge', this.purge_mailbox_test());
         
          this.enable_command('expand-all', 'expand-unread', 'collapse-all', this.env.threading && this.env.messagecount);

          if (response.action == 'list')
            this.triggerEvent('listupdate', { folder:this.env.mailbox, rowcount:this.message_list.rowcount });
        }
        else if (this.task == 'addressbook') {
          this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));
          
          if (response.action == 'list') {
            this.enable_command('group-create', this.env.address_sources[this.env.source].groups);
            this.enable_command('group-rename', 'group-delete', this.env.address_sources[this.env.source].groups && this.env.group);
            this.triggerEvent('listupdate', { folder:this.env.source, rowcount:this.contact_list.rowcount });
          }
        }
        break;
    }
  };

  // handle HTTP request errors
  this.http_error = function(request, status, err)
    {
    var errmsg = request.statusText;

    this.set_busy(false);
    request.abort();
    
    if (errmsg)
      this.display_message(this.get_label('servererror') + ' (' + errmsg + ')', 'error');
    };

  // use an image to send a keep-alive siganl to the server
  this.send_keep_alive = function()
    {
    var d = new Date();
    this.http_request('keep-alive', '_t='+d.getTime());
    };

  // start interval for keep-alive/recent_check signal
  this.start_keepalive = function()
    {
    if (this.env.keep_alive && !this.env.framed && this.task=='mail' && this.gui_objects.mailboxlist)
      this._int = setInterval(function(){ ref.check_for_recent(false); }, this.env.keep_alive * 1000);
    else if (this.env.keep_alive && !this.env.framed && this.task!='login')
      this._int = setInterval(function(){ ref.send_keep_alive(); }, this.env.keep_alive * 1000);
    }

  // send periodic request to check for recent messages
  this.check_for_recent = function(refresh)
    {
    if (this.busy)
      return;

    var addurl = '_t=' + (new Date().getTime());

    if (refresh) {
      this.set_busy(true, 'checkingmail');
      addurl += '&_refresh=1';
    }

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

  this.set_caret_pos = function(obj, pos)
    {
    if (obj.setSelectionRange)
      obj.setSelectionRange(pos, pos);
    else if (obj.createTextRange)
      {
      var range = obj.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
      }
    }

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

// copy event engine prototype
rcube_webmail.prototype.addEventListener = rcube_event_engine.prototype.addEventListener;
rcube_webmail.prototype.removeEventListener = rcube_event_engine.prototype.removeEventListener;
rcube_webmail.prototype.triggerEvent = rcube_event_engine.prototype.triggerEvent;
