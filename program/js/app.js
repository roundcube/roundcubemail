/**
 * Roundcube Webmail Client Script
 *
 * This file is part of the Roundcube Webmail client
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) The Roundcube Dev Team
 * Copyright (C) Kolab Systems AG
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 *
 * @author Thomas Bruederli <roundcube@gmail.com>
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 * @author Charles McNulty <charles@charlesmcnulty.com>
 *
 * @requires jquery.js, common.js, list.js
 */

function rcube_webmail() {
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
    this.http_request_jobs = {};
    this.menu_stack = [];
    this.menu_buttons = {};
    this.entity_selectors = [];
    this.image_style = {};
    this.uploads = {};

    // webmail client settings
    this.dblclick_time = 500;
    this.message_time = 5000;
    this.preview_delay_select = 400;
    this.preview_delay_click = 60;
    this.identifier_expr = /[^0-9a-z_-]/gi;
    this.uploadTimeout = 0; // 0 = no timeout | ajax call timeout for loading attachment


    // environment defaults
    this.env = {
        attachments: {},
        request_timeout: 180, // seconds
        draft_autosave: 0, // seconds
        comm_path: './',
        recipients_separator: ',', // @deprecated
        recipients_delimiter: ', ', // @deprecated
        popup_width: 1150,
        popup_width_small: 900,
        thread_padding: '15px',
    };

    // create protected reference to myself
    this.ref = 'rcmail';
    var ref = this;

    // set jQuery ajax options
    $.ajaxSetup({
        cache: false,
        timeout: this.env.request_timeout * 1000,
        error: function (request, status, err) {
            ref.http_error(request, status, err);
        },
        beforeSend: function (xmlhttp) {
            xmlhttp.setRequestHeader('X-Roundcube-Request', ref.env.request_token);
        },
    });

    // unload fix
    $(window).on('beforeunload', function () {
        ref.unload = true;
    });

    // set environment variable(s)
    this.set_env = function (p, value) {
        if (p != null && typeof p === 'object' && !value) {
            for (var n in p) {
                this.env[n] = p[n];
            }
        } else {
            this.env[p] = value;
        }
    };

    // add a localized label to the client environment
    this.add_label = function (p, value) {
        if (typeof p === 'string') {
            this.labels[p] = value;
        } else if (typeof p === 'object') {
            $.extend(this.labels, p);
        }
    };

    // add a button to the button list
    this.register_button = function (command, id, type, act, sel, over) {
        var button_prop = { id: id, type: type };

        if (act) {
            button_prop.act = act;
        }
        if (sel) {
            button_prop.sel = sel;
        }
        if (over) {
            button_prop.over = over;
        }

        if (!this.buttons[command]) {
            this.buttons[command] = [];
        }

        this.buttons[command].push(button_prop);

        if (this.loaded) {
            this.init_button(command, button_prop);
            this.set_button(command, (this.commands[command] ? 'act' : 'pas'));
        }
    };

    // register a button with popup menu, to set its state according to the state of all commands in the menu
    this.register_menu_button = function (button, menu_id) {
        if (this.menu_buttons[menu_id]) {
            this.menu_buttons[menu_id][0].push(button);
        } else {
            var commands = [];
            $('#' + menu_id).find('a').each(function () {
                var command, link = $(this), onclick = link.attr('onclick');

                if (onclick && String(onclick).match(/rcmail\.command\(\'([^']+)/)) {
                    command = RegExp.$1;
                } else {
                    command = function () {
                        return link.is('.active');
                    };
                }

                commands.push(command);
            });

            if (commands.length) {
                this.menu_buttons[menu_id] = [[button], commands];
            }
        }

        this.set_menu_buttons();
    };

    // set state of a menu button according to state of all menu actions
    this.set_menu_buttons = function () {
        // Use timeouts to not block and set menu button states only once
        clearTimeout(this.menu_buttons_timeout);
        this.menu_buttons_timeout = setTimeout(function () {
            $.each(ref.menu_buttons, function () {
                var disabled = true;
                $.each(this[1], function () {
                    var is_func = typeof (this) === 'function';
                    if ((is_func && this()) || (!is_func && ref.commands[this])) {
                        disabled = false;
                        return disabled;
                    }
                });

                $(this[0]).add($(this[0]).parent('.dropbutton'))
                    .addClass(disabled ? 'disabled' : 'active')
                    .removeClass(disabled ? 'active' : 'disabled');
            });
        }, 50);
    };

    // register a specific gui object
    this.gui_object = function (name, id) {
        this.gui_objects[name] = this.loaded ? rcube_find_object(id) : id;
    };

    // register a container object
    this.gui_container = function (name, id) {
        this.gui_containers[name] = id;
    };

    // add a GUI element (html node) to a specified container
    this.add_element = function (elm, container) {
        if (this.gui_containers[container] && this.gui_containers[container].jquery) {
            this.gui_containers[container].append(elm);
        }
    };

    // register an external handler for a certain command
    this.register_command = function (command, callback, enable) {
        this.command_handlers[command] = callback;

        if (enable) {
            this.enable_command(command, true);
        }
    };

    // execute the given script on load
    this.add_onload = function (f) {
        this.onloads.push(f);
    };

    // initialize webmail client
    this.init = function () {
        var n;

        this.interpret_js_calls();

        this.task = this.env.task;

        if (!this.env.blankpage) {
            this.env.blankpage = 'javascript:false;';
        }

        // find all registered gui containers
        for (n in this.gui_containers) {
            this.gui_containers[n] = $('#' + this.gui_containers[n]);
        }

        // find all registered gui objects
        for (n in this.gui_objects) {
            this.gui_objects[n] = rcube_find_object(this.gui_objects[n]);
        }

        // init registered buttons
        this.init_buttons();

        // tell parent window that this frame is loaded
        if (this.is_framed()) {
            parent.rcmail.unlock_frame();
        }

        // enable general commands
        this.enable_command('close', 'logout', 'mail', 'addressbook', 'settings', 'save-pref',
            'compose', 'undo', 'about', 'switch-task', 'menu-open', 'menu-close', 'menu-save', true);

        // set active task button
        this.set_button(this.task, 'sel');

        if (this.env.permaurl) {
            this.enable_command('permaurl', 'extwin', true);
        }

        switch (this.task) {
            case 'mail':
                // enable mail commands
                this.enable_command('list', 'checkmail', 'add-contact', 'search', 'reset-search', 'collapse-folder', 'import-messages', true);

                if (this.gui_objects.messagelist) {
                    // setup message list cols
                    this.msglist_setup(this.env.layout);

                    this.env.widescreen_list_template = [
                        { className: 'threads', cells: ['threads'] },
                        { className: 'subject', cells: ['fromto', 'date', 'size', 'status', 'subject'] },
                        { className: 'flags', cells: ['flag', 'attachment'] },
                    ];

                    this.message_list = new rcube_list_widget(this.gui_objects.messagelist, {
                        multiselect: true,
                        multiexpand: true,
                        draggable: true,
                        keyboard: true,
                        column_movable: this.env.col_movable,
                        dblclick_time: this.dblclick_time,
                    });
                    this.message_list
                        .addEventListener('initrow', function (o) {
                            ref.init_message_row(o);
                        })
                        .addEventListener('dblclick', function (o) {
                            ref.msglist_dbl_click(o);
                        })
                        .addEventListener('keypress', function (o) {
                            ref.msglist_keypress(o);
                        })
                        .addEventListener('select', function (o) {
                            ref.msglist_select(o);
                        })
                        .addEventListener('dragstart', function (o) {
                            ref.drag_start(o);
                        })
                        .addEventListener('dragmove', function (e) {
                            ref.drag_move(e);
                        })
                        .addEventListener('dragend', function (e) {
                            ref.drag_end(e);
                        })
                        .addEventListener('expandcollapse', function (o) {
                            ref.msglist_expand(o);
                        })
                        .addEventListener('column_replace', function (o) {
                            ref.msglist_set_coltypes(o);
                        })
                        .init();

                    // TODO: this should go into the list-widget code
                    $(this.message_list.thead).on('click', 'a.sortcol', function (e) {
                        return ref.command('sort', $(this).attr('rel'), this);
                    });

                    this.enable_command('toggle_status', 'toggle_flag', 'sort', true);
                    this.enable_command('set-listmode', this.env.threads && !this.is_multifolder_listing());

                    // load messages
                    var searchfilter = $(this.gui_objects.search_filter).val();
                    if (searchfilter && searchfilter != 'ALL') {
                        this.filter_mailbox(searchfilter);
                    } else {
                        this.command('list');
                    }

                    $(this.gui_objects.qsearchbox).val(this.env.search_text).focusin(function () {
                        ref.message_list.blur();
                    });
                }

                this.set_button_titles();

                this.env.message_commands = ['show', 'reply', 'reply-all', 'reply-list',
                    'move', 'copy', 'delete', 'open', 'mark', 'edit', 'viewsource', 'bounce',
                    'print', 'load-attachment', 'download-attachment', 'show-headers', 'hide-headers', 'download',
                    'forward', 'forward-inline', 'forward-attachment', 'change-format'];

                // Initialize Mailvelope early so e.g. fresh users have a keyring for
                // this session directly after login.
                // This does not hurt the following code execution because the
                // Mailvelope-related code runs non-blocking (using Promises).
                this.check_mailvelope(this.env.action);

                if (this.env.action == 'show' || this.env.action == 'preview') {
                    this.enable_command(this.env.message_commands, this.env.uid);
                    this.enable_command('reply-list', this.env.list_post);

                    if (this.env.action == 'show') {
                        this.http_request('pagenav', { _uid: this.env.uid, _mbox: this.env.mailbox, _search: this.env.search_request },
                            this.display_message('', 'loading'));
                    }

                    if (this.env.mail_read_time > 0) {
                        setTimeout(function () {
                            ref.http_post('mark', {
                                _uid: ref.env.uid, _flag: 'read', _mbox: ref.env.mailbox, _quiet: 1,
                            });
                        }, this.env.mail_read_time * 1000);
                    }

                    if (this.env.blockedobjects) {
                        $(this.gui_objects.remoteobjectsmsg).show();
                        this.enable_command('load-remote', true);
                    }

                    // make preview/message frame visible
                    if (this.env.action == 'preview' && this.is_framed()) {
                        this.enable_command('compose', 'add-contact', false);
                        parent.rcmail.show_contentframe(true);
                    }

                    if ($.inArray('flagged', this.env.message_flags) >= 0) {
                        $(document.body).addClass('status-flagged');
                    }

                    // initialize drag-n-drop on attachments, so they can e.g.
                    // be dropped into mail compose attachments in another window
                    if (this.gui_objects.attachments) {
                        $('li > a', this.gui_objects.attachments).not('.drop').on('dragstart', function (e) {
                            var n, href = this.href, dt = e.originalEvent.dataTransfer;
                            if (dt) {
                                // inject username to the uri
                                href = href.replace(/^https?:\/\//, function (m) {
                                    return m + urlencode(ref.env.username) + '@';
                                });

                                // cleanup the node to get filename without the size test
                                n = $(this).clone();
                                n.children().remove();

                                dt.setData('roundcube-uri', href);
                                dt.setData('roundcube-name', n.text().trim());
                            }
                        });
                    }
                } else if (this.env.action == 'compose') {
                    this.env.address_group_stack = [];
                    this.env.compose_commands = ['send-attachment', 'remove-attachment', 'send', 'cancel',
                        'toggle-editor', 'list-addresses', 'pushgroup', 'search', 'reset-search', 'extwin',
                        'insert-response', 'menu-open', 'menu-close', 'load-attachment',
                        'download-attachment', 'open-attachment', 'rename-attachment'];

                    if (this.env.drafts_mailbox) {
                        this.env.compose_commands.push('savedraft');
                    }

                    this.enable_command(this.env.compose_commands, true);

                    // add more commands (not enabled)
                    $.merge(this.env.compose_commands, ['add-recipient', 'firstpage', 'previouspage', 'nextpage', 'lastpage']);

                    this.env.editor_config.spellcheck_observer = function (s) {
                        ref.spellcheck_state();
                    };

                    // initialize HTML editor
                    this.editor_init(null, this.env.composebody);

                    if (this.editor.spellchecker) {
                        this.env.compose_commands.push('spellcheck');
                        this.enable_command('spellcheck', true);
                    }

                    // init message compose form
                    this.init_messageform();
                } else if (this.env.action == 'bounce') {
                    this.init_messageform_inputs();
                    this.env.compose_commands = [];
                } else if (this.env.action == 'get') {
                    this.enable_command('download', true);
                    this.enable_command('image-scale', 'image-rotate', !!/^image\//.test(this.env.mimetype));

                    // Mozilla's PDF.js viewer does not allow printing from host page (#5125)
                    // to minimize user confusion we disable the Print button on Firefox < 75
                    this.enable_command('print', this.env.mimetype != 'application/pdf' || !bw.mz || bw.vendver >= 75);

                    if (this.env.is_message) {
                        this.enable_command('reply', 'reply-all', 'edit', 'viewsource',
                            'forward', 'forward-inline', 'forward-attachment', 'bounce', true);
                        if (this.env.list_post) {
                            this.enable_command('reply-list', true);
                        }
                    }

                    // center and scale the image in preview frame
                    // TODO: Find a better way. Onload is late, also we could use embed.css
                    if (this.env.mimetype.startsWith('image/')) {
                        $(this.gui_objects.messagepartframe).on('load', function () {
                            var contents = $(this).contents();

                            // do not apply styles to an error page (with no image)
                            if (contents.find('img').length) {
                                contents.find('img').css({ maxWidth: '100%', maxHeight: '100%' });
                                contents.find('body').css({
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    height: '100%',
                                    margin: 0,
                                });
                                contents.find('html').css({ height: '100%' });
                            }
                        });
                    }
                }
                // show printing dialog unless decryption must be done first
                else if (this.env.action == 'print' && this.env.uid) {
                    if (!this.env.is_pgp_content && !this.env.pgp_mime_part) {
                        this.print_dialog();
                    }
                }

                // get unread count for each mailbox
                if (this.gui_objects.mailboxlist) {
                    this.env.unread_counts = {};
                    this.gui_objects.folderlist = this.gui_objects.mailboxlist;
                    this.http_request('getunread', { _page: this.env.current_page });
                }

                // init address book widget
                if (this.gui_objects.contactslist) {
                    this.contact_list = new rcube_list_widget(this.gui_objects.contactslist,
                        { multiselect: true, draggable: false, keyboard: true });
                    this.contact_list
                        .addEventListener('initrow', function (o) {
                            ref.triggerEvent('insertrow', { cid: o.uid, row: o });
                        })
                        .addEventListener('select', function (o) {
                            ref.compose_recipient_select(o);
                        })
                        .addEventListener('dblclick', function (o) {
                            ref.compose_add_recipient();
                        })
                        .addEventListener('keypress', function (o) {
                            if (o.key_pressed == o.ENTER_KEY) {
                                if (!ref.compose_add_recipient()) {
                                    // execute link action on <enter> if not a recipient entry
                                    if (o.last_selected && String(o.last_selected).charAt(0) == 'G') {
                                        $(o.rows[o.last_selected].obj).find('a').first().click();
                                    }
                                }
                            }
                        })
                        .init();

                    // remember last focused address field
                    $('#_to,#_cc,#_bcc').focus(function () {
                        ref.env.focused_field = this;
                    });
                }

                if (this.gui_objects.addressbookslist) {
                    this.gui_objects.folderlist = this.gui_objects.addressbookslist;
                    this.enable_command('list-addresses', true);
                }

                // ask user to send MDN
                if (this.env.mdn_request && this.env.uid) {
                    this.mdn_request_dialog(this.env.uid, this.env.mailbox);
                }

                // detect browser capabilities
                if (!this.is_framed() && !this.env.extwin) {
                    this.browser_capabilities_check();
                }

                break;
            case 'addressbook':
                this.env.address_group_stack = [];

                if (this.gui_objects.folderlist) {
                    this.env.contactfolders = $.extend($.extend({}, this.env.address_sources), this.env.contactgroups);
                }

                this.enable_command('add', 'import', this.env.writable_source);
                this.enable_command('list', 'listgroup', 'pushgroup', 'popgroup', 'listsearch', 'search', 'reset-search', 'advanced-search', true);

                if (this.gui_objects.contactslist) {
                    this.contact_list = new rcube_list_widget(this.gui_objects.contactslist,
                        { multiselect: true, draggable: this.gui_objects.folderlist ? true : false, keyboard: true });
                    this.contact_list
                        .addEventListener('initrow', function (o) {
                            ref.triggerEvent('insertrow', { cid: o.uid, row: o });
                        })
                        .addEventListener('keypress', function (o) {
                            ref.list_keypress(o);
                        })
                        .addEventListener('select', function (o) {
                            ref.contactlist_select(o);
                        })
                        .addEventListener('dragstart', function (o) {
                            ref.drag_start(o);
                        })
                        .addEventListener('dragmove', function (e) {
                            ref.drag_move(e);
                        })
                        .addEventListener('dragend', function (e) {
                            ref.drag_end(e);
                        })
                        .init();

                    $(this.gui_objects.qsearchbox).focusin(function () {
                        ref.contact_list.blur();
                    });

                    this.update_group_commands();
                    this.command('list');
                }

                if (this.gui_objects.savedsearchlist) {
                    this.savedsearchlist = new rcube_treelist_widget(this.gui_objects.savedsearchlist, {
                        id_prefix: 'rcmli',
                        id_encode: this.html_identifier_encode,
                        id_decode: this.html_identifier_decode,
                    });

                    this.savedsearchlist.addEventListener('select', function (node) {
                        ref.triggerEvent('selectfolder', { folder: node.id, prefix: 'rcmli' });
                    });
                }

                this.set_page_buttons();

                if (this.env.cid) {
                    this.enable_command('show', 'edit', 'qrcode', true);
                    // register handlers for group assignment via checkboxes
                    if (this.gui_objects.editform) {
                        $('input.groupmember').change(function () {
                            ref.group_member_change(this.checked ? 'add' : 'del', ref.env.cid, ref.env.source, this.value);
                        });
                    }
                }

                if (this.gui_objects.editform) {
                    this.enable_command('save', true);
                    if (this.env.action == 'add' || this.env.action == 'edit' || this.env.action == 'search') {
                        this.init_contact_form();
                    }
                } else if (this.env.action == 'print') {
                    this.print_dialog();
                }

                break;
            case 'settings':
                this.enable_command('show', 'save', true);

                if (this.env.action == 'identities') {
                    this.enable_command('add', this.env.identities_level < 2);
                } else if (this.env.action == 'edit-identity' || this.env.action == 'add-identity') {
                    this.enable_command('save', 'edit', true);
                    this.enable_command('delete', this.env.identities_level < 2);

                    if (this.env.action == 'edit-identity') {
                        this.check_mailvelope(this.env.action);
                    }
                } else if (this.env.action == 'folders') {
                    this.enable_command('subscribe', 'unsubscribe', 'create-folder', 'rename-folder', true);
                } else if (this.env.action == 'edit-folder' && this.gui_objects.editform) {
                    this.enable_command('save', 'folder-size', true);
                    parent.rcmail.env.exists = this.env.messagecount;
                    parent.rcmail.enable_command('purge', this.env.messagecount);
                } else if (this.env.action == 'responses') {
                    this.enable_command('add', true);
                }

                if (this.gui_objects.identitieslist) {
                    this.identity_list = new rcube_list_widget(this.gui_objects.identitieslist,
                        { multiselect: false, draggable: false, keyboard: true });
                    this.identity_list
                        .addEventListener('select', function (o) {
                            ref.identity_select(o);
                        })
                        .addEventListener('keypress', function (o) {
                            ref.list_keypress(o);
                        })
                        .init()
                        .focus();
                } else if (this.gui_objects.sectionslist) {
                    this.sections_list = new rcube_list_widget(this.gui_objects.sectionslist, { multiselect: false, draggable: false, keyboard: true });
                    this.sections_list
                        .addEventListener('select', function (o) {
                            ref.section_select(o);
                        })
                        .init()
                        .focus();
                } else if (this.gui_objects.subscriptionlist) {
                    this.init_subscription_list();
                } else if (this.gui_objects.responseslist) {
                    this.responses_list = new rcube_list_widget(this.gui_objects.responseslist, { multiselect: false, draggable: false, keyboard: true });
                    this.responses_list
                        .addEventListener('select', function (o) {
                            ref.response_select(o);
                        })
                        .addEventListener('keypress', function (o) {
                            ref.list_keypress(o);
                        })
                        .init()
                        .focus();
                }

                break;
            case 'login':
                var tz, input_user = $('#rcmloginuser');
                input_tz = $('#rcmlogintz');

                if (input_user.val() == '') {
                    input_user.focus();
                } else {
                    $('#rcmloginpwd').focus();
                }

                // detect client timezone
                try {
                    tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                } catch (e) {}

                input_tz.val(tz ? tz : (new Date().getStdTimezoneOffset() / -60));

                // display 'loading' message on form submit, lock submit button
                $('form').submit(function () {
                    $('[type=submit]', this).prop('disabled', true);
                    ref.clear_messages();
                    ref.display_message('', 'loading');
                });

                break;
        }

        this.addAllEventListenersFromElements();

        // select first input field in an edit form
        if (this.gui_objects.editform) {
            $('input,select,textarea', this.gui_objects.editform)
                .not(':hidden').not(':disabled').first().select().focus();
        }

        // prevent from form submit with Enter key in file input fields
        if (bw.ie) {
            $('input[type=file]').keydown(function (e) {
                if (e.keyCode == '13') {
                    e.preventDefault();
                }
            });
        }

        // flag object as complete
        this.loaded = true;
        this.env.lastrefresh = new Date();

        // show message
        if (this.pending_message) {
            this.display_message.apply(this, this.pending_message);
        }

        // init treelist widget
        if (this.gui_objects.folderlist && window.rcube_treelist_widget
            // some plugins may load rcube_treelist_widget and there's one case
            // when this will cause problems - addressbook widget in compose,
            // which already has been initialized using rcube_list_widget
            && this.gui_objects.folderlist != this.gui_objects.addressbookslist
        ) {
            this.treelist = new rcube_treelist_widget(this.gui_objects.folderlist, {
                selectable: true,
                id_prefix: 'rcmli',
                parent_focus: true,
                id_encode: this.html_identifier_encode,
                id_decode: this.html_identifier_decode,
                check_droptarget: function (node) {
                    return !node.virtual && ref.check_droptarget(node.id);
                },
            });

            this.treelist
                .addEventListener('collapse', function (node) {
                    ref.folder_collapsed(node);
                })
                .addEventListener('expand', function (node) {
                    ref.folder_collapsed(node);
                })
                .addEventListener('beforeselect', function (node) {
                    return !ref.busy;
                })
                .addEventListener('select', function (node) {
                    ref.triggerEvent('selectfolder', { folder: node.id, prefix: 'rcmli' });
                    ref.mark_all_read_state();
                });
        }

        // activate html5 file drop feature (if browser supports it and if configured)
        if (this.gui_objects.filedrop && this.env.filedrop && window.FormData) {
            $(document.body).on('dragover dragleave drop', function (e) {
                return ref.document_drag_hover(e, e.type == 'dragover');
            });

            $(this.gui_objects.filedrop).addClass('droptarget')
                .on('dragover dragleave', function (e) {
                    return ref.file_drag_hover(e, e.type == 'dragover');
                })
                .get(0).addEventListener('drop', function (e) {
                    return ref.file_dropped(e);
                }, false);
        }

        // catch document (and iframe) mouse clicks
        var body_mouseup = function (e) {
            return ref.doc_mouse_up(e);
        };
        $(document.body)
            .mouseup(body_mouseup)
            .keydown(function (e) {
                return ref.doc_keypress(e);
            });

        rcube_webmail.set_iframe_events({ mouseup: body_mouseup });

        // trigger init event hook
        this.triggerEvent('init', { task: this.task, action: this.env.action });

        // execute all foreign onload scripts
        // @deprecated
        for (n in this.onloads) {
            if (typeof this.onloads[n] === 'string') {
                eval(this.onloads[n]);
            } else if (typeof this.onloads[n] === 'function') {
                this.onloads[n]();
            }
        }

        // register menu buttons
        $('[data-popup]').each(function () {
            ref.register_menu_button(this, $(this).data('popup'));
        });

        // start keep-alive and refresh intervals
        this.start_refresh();
        this.start_keepalive();
    };

    /**
     * Handle function calls passed in via #js-calls. Through this, server code
     * can trigger Javascript-methods to be called.
     */
    this.interpret_js_calls = function () {
        // Do not use `.textContent`, and neither jQuery's `.text()` here,
        // because both modify the actual string!
        var raw = document.getElementById('js-calls').dataset.js;
        if (!raw) {
            return;
        }
        var data = JSON.parse(raw);
        data.forEach((args) => {
            if (!Array.isArray(args)) {
                this.log("Unexpected data in '#js-calls'! This is not an array: ", args);
            }
            var command = args.shift();
            if (command.startsWith('parent.')) {
                command = command.replace(/^parent\./, '');
                if (typeof parent.rcmail[command] !== 'function') {
                    this.log("'" + command + "' is not a callable function!");
                    return;
                }
                parent.rcmail[command](...args);
            } else {
                if (typeof this[command] !== 'function') {
                    this.log("'" + command + "' is not a callable function!");
                    return;
                }
                this[command](...args);
            }
        });
    };

    this.log = function (msg) {
        if (this.env.devel_mode && window.console && console.log) {
            console.log(msg);
        }
    };

    /*********************************************************/
    /*               client command interface                */
    /*********************************************************/

    // execute a specific command on the web client
    this.command = function (command, props, obj, event, allow_disabled) {
        var ret;

        if (obj && obj.blur && !(event && rcube_event.is_keyboard(event))) {
            obj.blur();
        }

        // do nothing if interface is locked by another command
        // with exception for searching reset and menu
        if (this.busy && !(command == 'reset-search' && this.last_command == 'search') && !command.match(/^menu-/)) {
            return false;
        }

        // let the browser handle this click (shift/ctrl usually opens the link in a new window/tab)
        if ((obj && obj.href && String(obj.href).indexOf('#') < 0) && rcube_event.get_modifier(event)) {
            return true;
        }

        // command not supported or allowed
        if (!allow_disabled && !this.commands[command]) {
            // pass command to parent window
            if (this.is_framed()) {
                parent.rcmail.command(command, props);
            }

            return false;
        }

        // check input before leaving compose step
        if (this.task == 'mail' && this.env.action == 'compose' && !this.env.server_error && command != 'save-pref'
            && ($.inArray(command, this.env.compose_commands) < 0 || command.startsWith('compose-encrypted') && ref.mailvelope_editor)
            && !this.compose_skip_unsavedcheck
        ) {
            if (!this.env.is_sent && this.cmp_hash != this.compose_field_hash()) {
                this.confirm_dialog(this.get_label('notsentwarning'), 'discard', function () {
                    // remove copy from local storage if compose screen is left intentionally
                    ref.remove_compose_data(ref.env.compose_id);
                    ref.compose_skip_unsavedcheck = true;
                    ref.command(command, props, obj, event);
                });

                return false;
            }
        }

        this.last_command = command;
        this.command_aborted = false;

        // trigger plugin hooks
        this.triggerEvent('actionbefore', { props: props, action: command, originalEvent: event });

        if ((ret = this.triggerEvent('before' + command, props || event)) !== undefined) {
            // abort if one of the handlers returned false
            if (ret === false) {
                return false;
            }

            props = ret;
        }

        // process external commands
        if (typeof this.command_handlers[command] === 'function') {
            ret = this.command_handlers[command](props, obj, event);
        } else if (typeof this.command_handlers[command] === 'string') {
            ret = window[this.command_handlers[command]](props, obj, event);
        }
        // process internal commands
        else {
            ret = this.command_handler(command, props, obj, event);
        }

        if (!this.command_aborted && this.triggerEvent('after' + command, props) === false) {
            ret = false;
        }

        this.triggerEvent('actionafter', {
            props: props, action: command, aborted: this.command_aborted, ret: ret, originalEvent: event,
        });

        if (ret === false) {
            return false;
        }

        if ((obj && ret !== true) || this.command_aborted === true) {
            return false;
        }

        return true;
    };

    // execute a specific known command
    this.command_handler = function (command, props, obj, event) {
        var uid, cid, url, flag;

        // process internal command
        switch (command) {
            // commands to switch task
            case 'logout':
            case 'mail':
            case 'addressbook':
            case 'settings':
                this.switch_task(command);

                break;
            case 'about':
                this.redirect('?_task=settings&_action=about', false);

                break;
            case 'permaurl':
                if (obj && obj.href && obj.target) {
                    return true;
                }
                if (this.env.permaurl) {
                    parent.location.href = this.env.permaurl;
                }

                break;
            case 'extwin':
                if (this.env.action == 'compose') {
                    var form = this.gui_objects.messageform,
                        win = this.open_window('');

                    if (win) {
                        this.save_compose_form_local();
                        this.compose_skip_unsavedcheck = true;
                        $("[name='_action']", form).val('compose');
                        form.action = this.url('mail/compose', { _id: this.env.compose_id, _extwin: 1 });
                        form.target = win.name;
                        form.submit();
                    }
                } else {
                    this.open_window(this.env.permaurl, true);
                }

                break;
            case 'change-format':
                url = this.env.permaurl + '&_format=' + props;

                if (this.env.action == 'preview') {
                    url = url.replace(/_action=show/, '_action=preview') + '&_framed=1';
                }
                if (this.env.extwin) {
                    url += '&_extwin=1';
                }

                location.href = url;

                break;
            case 'menu-open':
                if (props && props.menu == 'attachmentmenu') {
                    var mimetype = this.env.attachments[props.id];

                    // in compose format is different
                    if (mimetype && mimetype.mimetype) {
                        mimetype = mimetype.mimetype;
                    }

                    this.enable_command('open-attachment', mimetype && this.env.mimetypes && $.inArray(mimetype, this.env.mimetypes) >= 0);
                }
                this.show_menu(props, props.show || undefined, event);

                break;
            case 'menu-close':
                this.hide_menu(props, event);

                break;
            case 'menu-save':
                this.triggerEvent(command, { props: props, originalEvent: event });

                return false;
            case 'open':
                if (uid = this.get_single_uid()) {
                    obj.href = this.url('show', this.params_from_uid(uid, { _extwin: 1 }));
                    return true;
                }

                break;
            case 'close':
                if (this.env.extwin) {
                    window.close();
                }

                break;
            case 'list':
                if (props && props != '') {
                    this.reset_qsearch(true);
                }
                if (this.env.action == 'compose' && this.env.extwin) {
                    window.close();
                } else if (this.task == 'mail') {
                    this.list_mailbox(props, props ? 1 : '');
                    this.set_button_titles();
                } else if (this.task == 'addressbook') {
                    this.list_contacts(props);
                }

                break;
            case 'set-listmode':
                this.set_list_options(null, undefined, undefined, props == 'threads' ? 1 : 0);

                break;
            case 'sort':
                var sort_order = this.env.sort_order,
                    sort_col = !this.env.disabled_sort_col ? props : this.env.sort_col;

                if (!this.env.disabled_sort_order) {
                    sort_order = this.env.sort_col == sort_col && sort_order == 'ASC' ? 'DESC' : 'ASC';
                }

                // set table header and update env
                this.set_list_sorting(sort_col, sort_order);

                // reload message list
                this.list_mailbox('', '', sort_col + '_' + sort_order);

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
                if (this.env.exists) {
                    this.expunge_mailbox(this.env.mailbox);
                }

                break;
            case 'purge':
            case 'empty-mailbox':
                if (this.env.exists) {
                    this.purge_mailbox(this.env.mailbox);
                }

                break;
            // common commands used in multiple tasks
            case 'show':
                if (this.task == 'mail') {
                    uid = this.get_single_uid();
                    if (uid && (!this.env.uid || uid != this.env.uid)) {
                        var mbox = this.get_message_mailbox(uid);
                        if (mbox == this.env.drafts_mailbox) {
                            this.open_compose_step({ _draft_uid: uid, _mbox: mbox });
                        } else {
                            this.show_message(uid);
                        }
                    }
                } else if (this.task == 'addressbook') {
                    cid = props ? props : this.get_single_cid();
                    if (cid && !(this.env.action == 'show' && cid == this.env.cid)) {
                        this.load_contact(cid, 'show');
                    }
                } else if (this.task == 'settings') {
                    this.goto_url('settings/' + props, { _framed: 0 });
                }

                break;
            case 'add':
                if (this.task == 'addressbook') {
                    this.load_contact(0, 'add');
                } else if (this.task == 'settings' && this.env.action == 'responses') {
                    this.load_response(0, 'add-response');
                } else if (this.task == 'settings') {
                    this.load_identity(0, 'add-identity');
                }

                break;
            case 'edit':
                if (this.task == 'addressbook' && (cid = this.get_single_cid())) {
                    this.load_contact(cid, 'edit');
                } else if (this.task == 'mail' && (uid = this.get_single_uid())) {
                    url = { _mbox: this.get_message_mailbox(uid) };
                    url[url._mbox == this.env.drafts_mailbox && props != 'new' ? '_draft_uid' : '_uid'] = uid;
                    this.open_compose_step(url);
                }

                break;
            case 'save':
                var input, form = this.gui_objects.editform;
                if (form) {
                    // user prefs
                    if ((input = $("[name='_pagesize']", form)) && input.length && isNaN(parseInt(input.val(), 10))) {
                        this.alert_dialog(this.get_label('nopagesizewarning'), function () {
                            input.focus();
                        });

                        break;
                    }
                    // contacts/identities
                    else {
                        // reload form
                        if (props == 'reload') {
                            form.action += '&_reload=1';
                        } else if (this.task == 'settings' && (this.env.identities_level % 2) == 0
                            && (input = $("[name='_email']", form)) && input.length && !rcube_check_email(input.val())
                        ) {
                            this.alert_dialog(this.get_label('noemailwarning'), function () {
                                input.focus();
                            });

                            break;
                        }
                    }

                    // add selected source (on the list)
                    if (parent.rcmail && parent.rcmail.env.source) {
                        form.action = this.add_url(form.action, '_orig_source', parent.rcmail.env.source);
                    }

                    form.submit();
                }

                break;
            case 'delete':
                // mail task
                if (this.task == 'mail') {
                    this.delete_messages(event);
                }
                // addressbook task
                else if (this.task == 'addressbook') {
                    this.delete_contacts();
                }
                // settings: canned response
                else if (this.task == 'settings' && this.env.action == 'responses') {
                    this.delete_response();
                }
                // settings: user identities
                else if (this.task == 'settings') {
                    this.delete_identity();
                }

                break;
            // mail task commands
            case 'move':
            case 'moveto': // deprecated
                if (this.task == 'mail') {
                    this.move_messages(props, event);
                } else if (this.task == 'addressbook') {
                    this.move_contacts(props, event);
                }

                break;
            case 'copy':
                if (this.task == 'mail') {
                    this.copy_messages(props, event);
                } else if (this.task == 'addressbook') {
                    this.copy_contacts(props, event);
                }

                break;
            case 'mark':
                if (props) {
                    this.mark_message(props);
                }

                break;
            case 'toggle_status':
            case 'toggle_flag':
                flag = command == 'toggle_flag' ? 'flagged' : 'read';

                if (uid = props) {
                    if (flag == 'flagged') {
                        if (this.message_list.rows[uid].flagged) {
                            flag = 'unflagged';
                        }
                    } else if (this.message_list.rows[uid].deleted) {
                        flag = 'undelete';
                    } else if (!this.message_list.rows[uid].unread) {
                        flag = 'unread';
                    }

                    this.mark_message(flag, uid);
                }

                break;
            case 'add-contact':
                this.add_contact(props);

                break;
            case 'load-remote':
                if (this.env.uid) {
                    if (props && this.env.sender) {
                        this.add_contact(this.env.sender, true, props);
                        break;
                    }

                    this.show_message(this.env.uid, true, this.env.action == 'preview');
                }

                break;
            case 'load-attachment':
            case 'open-attachment':
            case 'download-attachment':
                var params, mimetype = this.env.attachments[props];

                if (this.env.action == 'compose') {
                    params = { _file: props, _id: this.env.compose_id };
                    mimetype = mimetype ? mimetype.mimetype : '';
                } else {
                    params = { _mbox: this.env.mailbox, _uid: this.env.uid, _part: props };
                }

                // open attachment in frame if it's of a supported mimetype
                if (command != 'download-attachment' && mimetype && this.env.mimetypes && $.inArray(mimetype, this.env.mimetypes) >= 0) {
                    // Note: We disable _framed for proper X-Frame-Options:deny support (#6688)
                    if (this.open_window(this.url('get', $.extend({ _frame: 1, _framed: 0 }, params)))) {
                        return true;
                    }
                }

                params._download = 1;

                // prevent from page unload warning in compose
                this.compose_skip_unsavedcheck = 1;
                this.goto_url('get', params, false, true);
                this.compose_skip_unsavedcheck = 0;

                return true;
            case 'select-all':
                this.select_all_mode = props ? false : true;
                this.dummy_select = true; // prevent msg opening if there's only one msg on the list
                var list = this[this.task == 'addressbook' ? 'contact_list' : 'message_list'];
                if (props == 'invert') {
                    list.invert_selection();
                } else {
                    list.select_all(props == 'page' ? '' : props);
                }
                this.dummy_select = null;

                break;
            case 'select-none':
                this.select_all_mode = false;
                this[this.task == 'addressbook' ? 'contact_list' : 'message_list'].clear_selection();

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
                if (this.env.next_uid) {
                    this.show_message(this.env.next_uid, false, this.env.action == 'preview');
                }

                break;
            case 'lastmessage':
                if (this.env.last_uid) {
                    this.show_message(this.env.last_uid);
                }

                break;
            case 'previousmessage':
                if (this.env.prev_uid) {
                    this.show_message(this.env.prev_uid, false, this.env.action == 'preview');
                }

                break;
            case 'firstmessage':
                if (this.env.first_uid) {
                    this.show_message(this.env.first_uid);
                }

                break;
            case 'compose':
                url = {};

                if (this.task == 'mail') {
                    url = { _mbox: this.env.mailbox, _search: this.env.search_request };
                    if (props) {
                        url._to = props;
                    }
                }
                // modify url if we're in addressbook
                else if (this.task == 'addressbook') {
                    // switch to mail compose step directly
                    if (props && props.indexOf('@') > 0) {
                        url._to = props;
                    } else {
                        var a_cids = [];
                        // use contact id passed as command parameter
                        if (props) {
                            a_cids.push(props);
                        }
                        // get selected contacts
                        else if (this.contact_list) {
                            a_cids = this.contact_list.get_selection();
                        }

                        if (a_cids.length) {
                            this.http_post('mailto', { _cid: a_cids.join(','), _source: this.env.source }, true);
                            break;
                        } else if (this.env.group && this.env.pagecount) {
                            this.http_post('mailto', { _gid: this.env.group, _source: this.env.source }, true);
                            break;
                        }
                    }
                } else if (props && typeof props === 'string') {
                    url._to = props;
                } else if (props && typeof props === 'object') {
                    $.extend(url, props);
                }

                this.open_compose_step(url);

                break;
            case 'spellcheck':
                if (this.spellcheck_state()) {
                    this.editor.spellcheck_stop();
                } else {
                    this.editor.spellcheck_start();
                }

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
                if (!props.nocheck && !this.env.is_sent && !this.check_compose_input(command)) {
                    break;
                }

                // Reset the auto-save timer
                clearTimeout(this.save_timer);

                this.submit_messageform();

                break;
            case 'send-attachment':
                // Reset the auto-save timer
                clearTimeout(this.save_timer);

                if (!(flag = this.upload_file(props || this.gui_objects.uploadform, 'upload'))) {
                    if (flag !== false) {
                        this.alert_dialog(this.get_label('selectimportfile'));
                    }
                    aborted = true;
                }

                break;
            case 'insert-sig':
                this.change_identity($("[name='_from']")[0], true);

                break;
            case 'list-addresses':
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
                    url = { _reply_uid: uid, _mbox: this.get_message_mailbox(uid), _search: this.env.search_request };
                    if (command == 'reply-all') {
                        // do reply-list, when list is detected and popup menu wasn't used
                        url._all = (!props && this.env.reply_all_mode == 1 && this.commands['reply-list'] ? 'list' : 'all');
                    } else if (command == 'reply-list') {
                        url._all = 'list';
                    }

                    this.open_compose_step(url);
                }

                break;
            case 'forward-attachment':
            case 'forward-inline':
            case 'forward':
                var uids = this.env.uid ? [this.env.uid] : (this.message_list ? this.message_list.get_selection() : []);
                if (uids.length) {
                    url = { _forward_uid: this.uids_to_list(uids), _mbox: this.env.mailbox, _search: this.env.search_request };
                    if (command == 'forward-attachment' || (!props && this.env.forward_attachment) || uids.length > 1) {
                        url._attachment = 1;
                    }
                    this.open_compose_step(url);
                }

                break;
            case 'print':
                if (this.task == 'addressbook') {
                    if (uid = this.get_single_cid()) {
                        url = '&_action=print&_cid=' + uid;
                        if (this.env.source) {
                            url += '&_source=' + urlencode(this.env.source);
                        }
                        this.open_window(this.env.comm_path + url, true, true);
                    }
                } else if (this.env.action == 'get' && !this.env.is_message) {
                    this.gui_objects.messagepartframe.contentWindow.print();
                } else if (uid = this.get_single_uid()) {
                    url = this.url('print', this.params_from_uid(uid, { _safe: this.env.safemode ? 1 : 0 }));
                    if (this.open_window(url, true, true)) {
                        if (this.env.action != 'show' && this.env.action != 'get') {
                            this.mark_message('read', uid);
                        }
                    }
                }

                break;
            case 'viewsource':
                if (uid = this.get_single_uid()) {
                    this.open_window(this.url('viewsource', this.params_from_uid(uid)), true, true);
                }

                break;
            case 'download':
                if (this.env.action == 'get') {
                    location.href = this.secure_url(location.href.replace(/_frame=/, '_download='));
                } else if (uid = this.get_single_uid()) {
                    this.goto_url('viewsource', this.params_from_uid(uid, { _save: 1 }), false, true);
                }

                break;
            // quicksearch
            case 'search':
                return this.qsearch(props);
            // reset quicksearch
            case 'reset-search':
                var n, s = this.env.search_request || this.env.qsearch;

                this.reset_qsearch(true);

                if (s && this.env.action == 'compose') {
                    if (this.contact_list) {
                        this.list_contacts_clear();
                    }
                } else if (s && this.env.mailbox) {
                    this.list_mailbox(this.env.mailbox, 1);
                } else if (s && this.task == 'addressbook') {
                    this.env.source = this.env.last_source || '';
                    this.env.group = this.env.last_group || '';
                    this.list_contacts(this.env.source, this.env.group, 1);
                }

                break;
            case 'pushgroup':
                // add group ID and current search to stack
                var group = {
                    id: props.id,
                    search_request: this.env.search_request,
                    page: this.env.current_page,
                    search: this.env.search_request && this.gui_objects.qsearchbox ? this.gui_objects.qsearchbox.value : null,
                };

                this.env.address_group_stack.push(group);
                if (obj && event) {
                    rcube_event.cancel(event);
                }

                // FIXME: no break?

            case 'listgroup':
                this.reset_qsearch();
                this.list_contacts(props.source, props.id, 1, group);

                break;
            case 'popgroup':
                if (this.env.address_group_stack.length) {
                    var old = this.env.address_group_stack.pop();
                    this.reset_qsearch();

                    if (old.search_request) {
                        // this code is executed when going back to the search result
                        if (old.search && this.gui_objects.qsearchbox) {
                            $(this.gui_objects.qsearchbox).val(old.search);
                        }
                        this.env.search_request = old.search_request;
                        this.list_contacts_remote(null, null, this.env.current_page = old.page);
                    } else {
                        this.list_contacts(props.source, this.env.address_group_stack[this.env.address_group_stack.length - 1].id);
                    }
                }

                break;
            case 'import-messages':
                var form = props || this.gui_objects.importform,
                    importlock = this.set_busy(true, 'importwait');

                if (!(flag = this.upload_file(form, 'import', importlock))) {
                    this.set_busy(false, null, importlock);
                    if (flag !== false) {
                        this.alert_dialog(this.get_label('selectimportfile'));
                    }
                    this.command_aborted = true;
                }

                break;
            case 'import':
                var dialog = $('<iframe>').attr('src', this.url('import', { _framed: 1, _target: this.env.source })),
                    import_func = function (e) {
                        var win = dialog[0].contentWindow,
                            form = win.rcmail.gui_objects.importformmap || win.rcmail.gui_objects.importform;

                        if (form) {
                            var lock, file = win.$('#rcmimportfile')[0];
                            if (file && !file.value) {
                                win.rcmail.alert_dialog(win.rcmail.get_label('selectimportfile'));
                                return;
                            }

                            lock = win.rcmail.set_busy(true, 'importwait');
                            $('[name="_unlock"]', form).val(lock);
                            form.submit();
                            win.rcmail.lock_form(form, true);

                            // disable Import button
                            $(e.target).attr('disabled', true).next().focus();
                        }
                    },
                    close_func = function (event, ui) {
                        $(this).remove();
                        if (ref.import_state == 'reload') {
                            ref.command('list');
                        }
                    };

                this.import_state = null;
                this.import_dialog = this.simple_dialog(dialog, 'importcontacts', import_func, {
                    close: close_func,
                    button: 'import',
                    width: 500,
                    height: 300,
                });

                break;
            case 'export':
                if (this.contact_list.rowcount > 0) {
                    this.goto_url('export', { _source: this.env.source, _gid: this.env.group, _search: this.env.search_request }, false, true);
                }

                break;
            case 'export-selected':
                if (this.contact_list.rowcount > 0) {
                    this.goto_url('export', { _source: this.env.source, _gid: this.env.group, _cid: this.contact_list.get_selection().join(',') }, false, true);
                }

                break;
            case 'upload-photo':
                this.upload_contact_photo(props || this.gui_objects.uploadform);

                break;
            case 'delete-photo':
                this.replace_contact_photo('-del-');

                break;
            case 'undo':
                this.http_request('undo', '', this.display_message('', 'loading'));

                break;
            // unified command call (command name == function name)
            default:
                var func = command.replace(/-/g, '_');
                if (this[func] && typeof this[func] === 'function') {
                    return this[func](props, obj, event);
                }
        }
    };

    // set command(s) enabled or disabled
    this.enable_command = function () {
        var i, n, cmd, args = Array.prototype.slice.call(arguments),
            enable = args.pop();

        for (n = 0; n < args.length; n++) {
            cmd = args[n];
            // argument of type array
            if (typeof cmd === 'string') {
                this.commands[cmd] = enable;
                this.set_button(cmd, (enable ? 'act' : 'pas'));
                this.triggerEvent('enable-command', { command: cmd, status: enable });
            } else {
                // push array elements into commands array
                for (i in cmd) {
                    args.push(cmd[i]);
                }
            }
        }

        this.set_menu_buttons();
    };

    this.command_enabled = function (cmd) {
        return this.commands[cmd];
    };

    // lock/unlock interface
    this.set_busy = function (a, message, id) {
        if (a && message) {
            var msg = this.get_label(message);
            if (msg == message) {
                msg = 'Loading...';
            }

            id = this.display_message(msg, 'loading');
        } else if (!a && id) {
            this.hide_message(id);
        }

        this.busy = a;

        if (this.gui_objects.editform) {
            this.lock_form(this.gui_objects.editform, a);
        }

        return id;
    };

    // return a localized string
    this.get_label = function (name, domain) {
        if (domain && this.labels[domain + '.' + name]) {
            return this.labels[domain + '.' + name];
        }

        if (this.labels[name]) {
            return this.labels[name];
        }

        return name;
    };

    // alias for convenience reasons
    this.gettext = this.get_label;

    // switch to another application task
    this.switch_task = function (task) {
        var action, path;

        if ((path = task.split('/')).length == 2) {
            task = path[0];
            action = path[1];
        }

        if (this.task === task && task != 'mail') {
            return;
        }

        var url = this.get_task_url(task);

        if (action) {
            url += '&_action=' + action;
        }

        if (task == 'mail') {
            url += '&_mbox=INBOX';
        } else if (task == 'logout') {
            url = this.secure_url(url);
            this.clear_compose_data();
        }

        this.redirect(url);
    };

    this.get_task_url = function (task, url) {
        if (!url) {
            url = this.env.comm_path;
        }

        if (url.match(/[?&]_task=[a-zA-Z0-9_-]+/)) {
            return url.replace(/_task=[a-zA-Z0-9_-]+/, '_task=' + task);
        }

        return url.replace(/\?.*$/, '') + '?_task=' + task;
    };

    this.reload = function (delay) {
        if (this.is_framed()) {
            parent.rcmail.reload(delay);
        } else if (delay) {
            setTimeout(function () {
                ref.reload();
            }, delay);
        } else if (window.location) {
            location.href = this.url('', { _extwin: this.env.extwin });
        }
    };

    // Add variable to GET string, replace old value if exists
    this.add_url = function (url, name, value) {
        var urldata, datax, hash = '';

        value = urlencode(value);

        if (/(#[a-z0-9_-]*)$/.test(url)) {
            hash = RegExp.$1;
            url = url.substr(0, url.length - hash.length);
        }

        if (/(\?.*)$/.test(url)) {
            urldata = RegExp.$1;
            datax = RegExp('((\\?|&)' + RegExp.escape(name) + '=[^&]*)');

            if (datax.test(urldata)) {
                urldata = urldata.replace(datax, RegExp.$2 + name + '=' + value);
            } else {
                urldata += '&' + name + '=' + value;
            }

            return url.replace(/(\?.*)$/, urldata) + hash;
        }

        return url + '?' + name + '=' + value + hash;
    };

    // append CSRF protection token to the given url
    this.secure_url = function (url) {
        return this.add_url(url, '_token', this.env.request_token);
    },

    this.is_framed = function () {
        return this.env.framed && parent.rcmail && parent.rcmail != this && typeof parent.rcmail.command === 'function';
    };

    this.save_pref = function (prop) {
        var request = { _name: prop.name, _value: prop.value };

        if (prop.session) {
            request._session = prop.session;
        }
        if (prop.env) {
            this.env[prop.env] = prop.value;
        }

        this.http_post('save-pref', request);
    };

    this.html_identifier = function (str, encode) {
        return encode ? this.html_identifier_encode(str) : String(str).replace(this.identifier_expr, '_');
    };

    this.html_identifier_encode = function (str) {
        return Base64.encode(String(str)).replace(/=+$/, '').replace(/\+/g, '-').replace(/\//g, '_');
    };

    this.html_identifier_decode = function (str) {
        str = String(str).replace(/-/g, '+').replace(/_/g, '/');

        while (str.length % 4) {
            str += '=';
        }

        return Base64.decode(str);
    };


    /*********************************************************/
    /*                 event handling methods                 /
    /*********************************************************/

    this.drag_menu = function (e, target) {
        var modkey = rcube_event.get_modifier(e),
            menu = this.gui_objects.dragmenu;

        if (menu && modkey == SHIFT_KEY && this.commands.copy) {
            var pos = rcube_event.get_mouse_pos(e);
            this.env.drag_target = target;
            this.show_menu(this.gui_objects.dragmenu.id, true, e);
            $(menu).css({ top: (pos.y - 10) + 'px', left: (pos.x - 10) + 'px' });
            return true;
        }

        return false;
    };

    this.drag_menu_action = function (action) {
        var menu = this.gui_objects.dragmenu;
        if (menu) {
            $(menu).hide();
        }

        this.command(action, this.env.drag_target);
        this.env.drag_target = null;
    };

    this.drag_start = function (list) {
        this.drag_active = true;

        if (this.preview_timer) {
            clearTimeout(this.preview_timer);
        }

        // prepare treelist widget for dragging interactions
        if (this.treelist) {
            this.treelist.drag_start();
        }
    };

    this.drag_end = function (e) {
        var list, model;

        if (this.treelist) {
            this.treelist.drag_end();
        }

        // execute drag & drop action when mouse was released
        if (list = this.message_list) {
            model = this.env.mailboxes;
        } else if (list = this.contact_list) {
            model = this.env.contactfolders;
        }

        // Note: we accept only mouse events to ignore dragging aborts with ESC key (#6623)
        if (this.drag_active && model && this.env.last_folder_target && !rcube_event.is_keyboard(e)) {
            var target = model[this.env.last_folder_target];
            list.draglayer.hide();

            if (this.contact_list) {
                if (!this.contacts_drag_menu(e, target)) {
                    this.command('move', target);
                }
            } else if (!this.drag_menu(e, target)) {
                this.command('move', target);
            }
        }

        this.drag_active = false;
        this.env.last_folder_target = null;
    };

    this.drag_move = function (e) {
        if (this.gui_objects.folderlist) {
            var drag_target, oldclass,
                layerclass = 'draglayernormal',
                mouse = rcube_event.get_mouse_pos(e);

            if (this.contact_list && this.contact_list.draglayer) {
                oldclass = this.contact_list.draglayer.attr('class');
            }

            // mouse intersects a valid drop target on the treelist
            if (this.treelist && (drag_target = this.treelist.intersects(mouse, true))) {
                this.env.last_folder_target = drag_target;
                layerclass = 'draglayer' + (this.check_droptarget(drag_target) > 1 ? 'copy' : 'normal');
            } else {
                // Clear target, otherwise drag end will trigger move into last valid droptarget
                this.env.last_folder_target = null;
            }

            if (layerclass != oldclass && this.contact_list && this.contact_list.draglayer) {
                this.contact_list.draglayer.attr('class', layerclass);
            }
        }
    };

    this.collapse_folder = function (name) {
        if (this.treelist) {
            this.treelist.toggle(name);
        }
    };

    this.folder_collapsed = function (node) {
        if (this.folder_collapsed_timer) {
            clearTimeout(this.folder_collapsed_timer);
        }

        var prefname = this.env.task == 'addressbook' ? 'collapsed_abooks' : 'collapsed_folders',
            old = this.env[prefname],
            entry = '&' + urlencode(node.id) + '&';

        this.env[prefname] = old.replace(entry, '');

        if (node.collapsed) {
            this.env[prefname] = this.env[prefname] + entry;

            // select the folder if one of its children is currently selected
            // don't select if it's virtual (#1488346)
            if (!node.virtual && this.env.mailbox && this.env.mailbox.startsWith(node.id + this.env.delimiter)) {
                this.command('list', node.id);
            }
        }

        if (!this.drag_active) {
            if (old !== this.env[prefname]) {
                this.folder_collapsed_timer = setTimeout(function () {
                    ref.command('save-pref', { name: prefname, value: ref.env[prefname] });
                }, 10);
            }

            if (this.env.unread_counts) {
                this.set_unread_count_display(node.id, false);
            }
        }
    };

    // global mouse-click handler to cleanup some UI elements
    this.doc_mouse_up = function (e) {
        var list, id, target = rcube_event.get_target(e);

        // ignore event if jquery UI dialog is open
        if ($(target).closest('.ui-dialog, .ui-widget-overlay').length) {
            return;
        }

        // remove focus from list widgets
        if (window.rcube_list_widget && rcube_list_widget._instances.length) {
            $.each(rcube_list_widget._instances, function (i, list) {
                if (list && !rcube_mouse_is_over(e, list.list.parentNode)) {
                    list.blur();
                }
            });
        }

        // reset 'pressed' buttons
        if (this.buttons_sel) {
            for (id in this.buttons_sel) {
                if (typeof id !== 'function') {
                    this.button_out(this.buttons_sel[id], id);
                }
            }
            this.buttons_sel = {};
        }

        // reset popup menus; delayed to have updated menu_stack data
        setTimeout(function (e) {
            var obj, skip, config, id, i, parents = $(target).parents();
            for (i = ref.menu_stack.length - 1; i >= 0; i--) {
                id = ref.menu_stack[i];
                obj = $('#' + id);

                if (obj.is(':visible')
                    && target != obj.data('opener')
                    && target != obj.get(0) // check if scroll bar was clicked (#1489832)
                    && !parents.is(obj.data('opener'))
                    && id != skip
                    && (obj.attr('data-editable') != 'true' || !$(target).parents('#' + id).length)
                    && (obj.attr('data-sticky') != 'true' || !rcube_mouse_is_over(e, obj.get(0)))
                ) {
                    ref.hide_menu(id, e);
                }
                skip = obj.data('parent');
            }
        }, 10, e);
    };

    // Global keypress event handler
    this.doc_keypress = function (e) {
        var target = e.target || {},
            keyCode = rcube_event.get_keycode(e),
            // Helper method to move focus to the next/prev active menu item
            focus_menu_item = function (dir) {
                var obj, item,
                    mod = dir < 0 ? 'prevAll' : 'nextAll',
                    limit = dir < 0 ? 'last' : 'first';

                if (ref.focused_menu && (obj = $('#' + ref.focused_menu))) {
                    item = obj.find(':focus').closest('li')[mod]().has(':not([aria-disabled=true])').find('a,input')[limit]();
                    if (!item.length) {
                        item = obj.find(':focus').closest('ul')[mod]().has(':not([aria-disabled=true])').find('a,input')[limit]();
                    }
                    return item.focus().length;
                }

                return 0;
            };

        if (e.keyCode != 27 && (!this.menu_keyboard_active || target.nodeName == 'TEXTAREA' || target.nodeName == 'SELECT')) {
            return true;
        }

        switch (keyCode) {
            case 38: // arrow-up
            case 40: // arrow-down
            case 63232: // arrow-up, in safari keypress
            case 63233: // arrow-down, in safari keypress
                focus_menu_item(keyCode == 38 || keyCode == 63232 ? -1 : 1);
                return rcube_event.cancel(e);
            case 9: // tab
                if (this.focused_menu) {
                    var mod = rcube_event.get_modifier(e);
                    if (!focus_menu_item(mod == SHIFT_KEY ? -1 : 1)) {
                        this.hide_menu(this.focused_menu, e);
                    }
                }

                return rcube_event.cancel(e);
            case 27: // esc
                if (this.menu_stack.length) {
                    this.hide_menu(this.menu_stack[this.menu_stack.length - 1], e);
                }

                break;
        }

        return true;
    };

    // Common handler for a keypress event on a list widget
    this.list_keypress = function (list, conf) {
        if (list.modkey == CONTROL_KEY) {
            return;
        }

        if (list.key_pressed == list.DELETE_KEY || list.key_pressed == list.BACKSPACE_KEY) {
            this.command(conf && conf.del ? conf.del : 'delete');
        } else if (list.key_pressed == 33) {
            this.command(conf && conf.prev ? conf.prev : 'previouspage');
        } else if (list.key_pressed == 34) {
            this.command(conf && conf.next ? conf.next : 'nextpage');
        }
    };

    // Handler for a keypress event on a messages list widget
    this.msglist_keypress = function (list) {
        // On Enter open the message in list layout mode (no preview frame)
        if (list.key_pressed == list.ENTER_KEY && !this.env.contentframe) {
            this.command('show');
        } else {
            this.list_keypress(list);
        }
    };

    this.msglist_select = function (list) {
        if (this.preview_timer) {
            clearTimeout(this.preview_timer);
        }

        var isDraft = false,
            selected = list.get_single_selection(),
            selection = list.get_selection(false),
            selected_count = selection.length;

        this.enable_command(this.env.message_commands, selected != null);

        // Find out whether any of the selected messages comes from the Drafts folder
        if (selected_count > 0) {
            if (!this.env.multifolder_listing) {
                isDraft = this.env.mailbox == this.env.drafts_mailbox;
            } else {
                $.each(selection, function (i, v) {
                    if (ref.get_message_mailbox(v) == ref.env.drafts_mailbox) {
                        isDraft = true;
                        return false;
                    }
                });
            }
        }

        // Disable some actions enabled above
        if (selected) {
            if (isDraft) {
                this.enable_command('reply', 'reply-all', 'reply-list', 'forward', 'forward-inline',
                    'forward-attachment', 'bounce', false);
            } else {
                var msg = this.env.messages[selected];
                if (!msg.ml) {
                    this.enable_command('reply-list', false);
                }
            }
        }

        // Multi-message commands
        this.enable_command('delete', 'move', 'copy', 'mark', selected_count > 0);
        this.enable_command('forward', 'forward-attachment', !isDraft && selected_count > 0);

        // reset all-pages-selection
        if (selected || (selected_count && selected_count != list.rowcount)) {
            this.select_all_mode = false;
        }

        // start timer for message preview (wait for double click)
        if (selected && this.env.contentframe && !list.multi_selecting && !this.dummy_select) {
            // try to be responsive and try not to overload the server when user is pressing up/down key repeatedly
            var now = new Date().getTime(),
                time_diff = now - (this._last_msglist_select_time || 0),
                preview_pane_delay = this.preview_delay_click;

            // user is selecting messages repeatedly, wait until this ends (use larger delay)
            if (time_diff < this.preview_delay_select) {
                preview_pane_delay = this.preview_delay_select;
                if (this.preview_timer) {
                    clearTimeout(this.preview_timer);
                }
                if (this.env.contentframe) {
                    this.show_contentframe(false);
                }
            }

            this._last_msglist_select_time = now;
            this.preview_timer = setTimeout(function () {
                ref.msglist_get_preview();
            }, preview_pane_delay);
        } else if (this.env.contentframe) {
            this.show_contentframe(false);
        }
    };

    this.msglist_dbl_click = function (list) {
        if (this.preview_timer) {
            clearTimeout(this.preview_timer);
        }

        var mbox, uid = list.get_single_selection();

        // TODO: Here we should just use this.command('show') but we can't
        // because at this point this.busy=true (set by msglist_get_preview())

        if (uid) {
            mbox = this.get_message_mailbox(uid);
            if (mbox == this.env.drafts_mailbox) {
                this.open_compose_step({ _draft_uid: uid, _mbox: mbox });
            } else {
                this.show_message(uid);
            }
        }
    };

    this.msglist_get_preview = function () {
        var uid = this.get_single_uid();

        if (uid && this.env.contentframe && !this.drag_active) {
            this.show_message(uid, false, true);
        } else if (this.env.contentframe) {
            this.show_contentframe(false);
        }
    };

    this.msglist_expand = function (row) {
        if (this.env.messages[row.uid]) {
            this.env.messages[row.uid].expanded = row.expanded;
        }

        $(row.obj)[row.expanded ? 'addClass' : 'removeClass']('expanded');
    };

    this.msglist_set_coltypes = function (list) {
        var i, found, name, cols = list.thead.rows[0].cells;

        this.env.listcols = [];

        for (i = 0; i < cols.length; i++) {
            if (cols[i].id && cols[i].id.startsWith('rcm')) {
                name = cols[i].id.slice(3);
                this.env.listcols.push(name);
            }
        }

        // update message list setup
        this.msglist_setup(this.env.layout);

        if ((found = $.inArray('flag', this.env.listcols)) >= 0) {
            this.env.flagged_col = found;
        }

        if ((found = $.inArray('subject', this.env.listcols)) >= 0) {
            this.env.subject_col = found;
        }

        this.command('save-pref', { name: 'list_cols', value: this.env.listcols, session: 'list_attrib/columns' });
    };

    this.msglist_setup = function (layout) {
        var ret, listcols;

        // allow plugins or skins to override default list layout
        if (ret = this.triggerEvent('msglist_layout', layout)) {
            layout = ret;
        }

        listcols = this.env[layout == 'widescreen' ? 'listcols_widescreen' : 'listcols'];

        if (layout == 'widescreen' && !this.env.threading) {
            listcols = $.grep(listcols, function (value) {
                return value != 'threads';
            });
        }

        // set env vars for message list
        this.env.msglist_layout = layout;
        this.env.msglist_cols = listcols;

        // Set sort-* class on the list element
        var list = this.gui_objects.messagelist,
            classes = list.className.split(' ').filter(function (v) {
                return !v.startsWith('sort-');
            });

        classes.push('sort-' + (this.env.sort_col || 'none'));
        list.className = classes.join(' ');
    };

    this.check_droptarget = function (id) {
        switch (this.task) {
            case 'mail':
                return (this.env.mailboxes[id]
                    && !this.env.mailboxes[id].virtual
                    && (this.env.mailboxes[id].id != this.env.mailbox || this.is_multifolder_listing())) ? 1 : 0;

            case 'addressbook':
                var target;
                if (id != this.env.source && (target = this.env.contactfolders[id])) {
                    // droptarget is a group
                    if (target.type == 'group') {
                        if (target.id != this.env.group && !this.env.contactfolders[target.source].readonly) {
                            var is_other = this.env.selection_sources.length > 1 || $.inArray(target.source, this.env.selection_sources) == -1;
                            return !is_other || this.commands.move ? 1 : 2;
                        }
                    }
                    // droptarget is a (writable) addressbook and it's not the source
                    else if (!target.readonly && (this.env.selection_sources.length > 1 || $.inArray(id, this.env.selection_sources) == -1)) {
                        return this.commands.move ? 1 : 2;
                    }
                }
        }

        return 0;
    };

    // open popup window
    this.open_window = function (url, small, toolbar) {
        var extwin, features, wname = 'rcmextwin' + new Date().getTime();

        url += (url.match(/\?/) ? '&' : '?') + '_extwin=1';

        if (!this.env.standard_windows) {
            var win = this.is_framed() ? parent.window : window,
                page = $(win),
                page_width = page.width(),
                page_height = bw.mz ? $('body', win).height() : page.height(),
                w = Math.min(small ? this.env.popup_width_small : this.env.popup_width, page_width),
                h = page_height, // always use same height
                l = (win.screenLeft || win.screenX) + 20,
                t = (win.screenTop || win.screenY) + 20;

            features = 'width=' + w + ',height=' + h + ',top=' + t + ',left=' + l
                + ',resizable=yes,location=no,scrollbars=yes'
                + (toolbar ? ',toolbar=yes,menubar=yes,status=yes' : ',toolbar=no,menubar=no,status=no');
        }

        extwin = window.open(url, wname, features);

        // detect popup blocker (#1489618)
        // don't care this might not work with all browsers
        if (!extwin || extwin.closed) {
            this.display_message('windowopenerror', 'warning');
            return;
        }

        // write loading... message to empty windows
        if (!url && extwin.document) {
            extwin.document.write('<html><body>' + this.get_label('loading') + '</body></html>');
        }

        // allow plugins to grab the window reference (#1489413)
        this.triggerEvent('openwindow', { url: url, handle: extwin });

        // focus window, delayed to bring to front
        setTimeout(function () {
            extwin && extwin.focus();
        }, 10);

        return extwin;
    };


    /*********************************************************/
    /*             (message) list functionality              */
    /*********************************************************/

    this.init_message_row = function (row) {
        var i, fn = {}, uid = row.uid,
            status_icon = (this.env.status_col != null ? 'status' : 'msg') + 'icn' + row.id;

        if (uid && this.env.messages[uid]) {
            $.extend(row, this.env.messages[uid]);
        }

        // set eventhandler to status icon
        if (row.icon = document.getElementById(status_icon)) {
            fn.icon = function (e) {
                ref.command('toggle_status', uid);
            };
        }

        // save message icon position too
        if (this.env.status_col != null) {
            row.msgicon = document.getElementById('msgicn' + row.id);
        } else {
            row.msgicon = row.icon;
        }

        // set eventhandler to flag icon
        if (this.env.flagged_col != null && (row.flagicon = document.getElementById('flagicn' + row.id))) {
            fn.flagicon = function (e) {
                ref.command('toggle_flag', uid);
            };
        }

        // set event handler to thread expand/collapse icon
        if (!row.depth && row.has_children && (row.expando = document.getElementById('rcmexpando' + row.id))) {
            fn.expando = function (e) {
                ref.expand_message_row(e, uid);
            };
        }

        // attach events
        $.each(fn, function (i, f) {
            row[i].onclick = function (e) {
                f(e); return rcube_event.cancel(e);
            };
            if (bw.touch && row[i].addEventListener) {
                row[i].addEventListener('touchend', function (e) {
                    if (e.changedTouches.length == 1) {
                        f(e);
                        return rcube_event.cancel(e);
                    }
                }, false);
            }
        });

        this.triggerEvent('insertrow', { uid: uid, row: row });
    };

    // create a table row in the message list
    this.add_message_row = function (uid, cols, flags, attop) {
        if (!this.gui_objects.messagelist || !this.message_list) {
            return false;
        }

        // Prevent from adding messages from different folder (#1487752)
        if (flags.mbox != this.env.mailbox && !flags.skip_mbox_check) {
            return false;
        }

        // When deleting messages fast it may happen that the same message
        // from the next page could be added many times, we prevent this here
        if (this.message_list.rows[uid]) {
            return false;
        }

        if (!this.env.messages[uid]) {
            this.env.messages[uid] = {};
        }

        // merge flags over local message object
        $.extend(this.env.messages[uid], {
            deleted: flags.deleted ? 1 : 0,
            replied: flags.answered ? 1 : 0,
            unread: !flags.seen ? 1 : 0,
            forwarded: flags.forwarded ? 1 : 0,
            flagged: flags.flagged ? 1 : 0,
            has_children: flags.has_children ? 1 : 0,
            depth: flags.depth ? flags.depth : 0,
            unread_children: flags.unread_children || 0,
            flagged_children: flags.flagged_children || 0,
            parent_uid: flags.parent_uid || 0,
            selected: this.select_all_mode || this.message_list.in_selection(uid),
            ml: flags.ml ? 1 : 0,
            ctype: flags.ctype,
            mbox: flags.mbox,
            size: cols.size,
            date: cols.date,
            flags: flags.extra_flags, // flags from plugins
        });

        var c, n, col, html, css_class, label, status_class = '', status_label = '', tree = '', expando = '',
            list = this.message_list,
            rows = list.rows,
            message = this.env.messages[uid],
            msg_id = this.html_identifier(uid, true),
            row_class = 'message'
                + (!flags.seen ? ' unread' : '')
                + (flags.deleted ? ' deleted' : '')
                + (flags.flagged ? ' flagged' : '')
                + (message.selected ? ' selected' : ''),
            row = {
                cols: [], style: {}, id: 'rcmrow' + msg_id, uid: uid,
            },
            layout = this.env.msglist_layout,
            listcols = this.env.msglist_cols;

        // widescreen layout does not have a separate status column
        if (layout == 'widescreen') {
            this.env.status_col = null;
        } else if ((n = $.inArray('status', listcols)) >= 0) {
            this.env.status_col = n;
        }

        // message status icons
        css_class = 'msgicon';
        if (this.env.status_col === null) {
            css_class += ' status';
            if (flags.deleted) {
                status_class += ' deleted';
                status_label += this.get_label('deleted') + ' ';
            } else if (!flags.seen) {
                status_class += ' unread';
                status_label += this.get_label('unread') + ' ';
            } else if (flags.unread_children > 0) {
                status_class += ' unreadchildren';
            }
        }
        if (flags.answered) {
            status_class += ' replied';
            status_label += this.get_label('replied') + ' ';
        }
        if (flags.forwarded) {
            status_class += ' forwarded';
            status_label += this.get_label('forwarded') + ' ';
        }

        // update selection
        if (message.selected && !list.in_selection(uid)) {
            list.selection.push(uid);
        }

        // threads
        if (this.env.threading) {
            if (message.depth) {
                // This assumes that div width is hardcoded to 15px,
                tree += '<span id="rcmtab' + msg_id + '" class="branch" style="width:' + (message.depth * 15) + 'px;">&nbsp;&nbsp;</span>';

                if ((rows[message.parent_uid] && rows[message.parent_uid].expanded === false)
                    || ((this.env.autoexpand_threads == 0 || this.env.autoexpand_threads == 2)
                        && (!rows[message.parent_uid] || !rows[message.parent_uid].expanded))
                ) {
                    row.style.display = 'none';
                    message.expanded = false;
                } else {
                    message.expanded = true;
                }

                row_class += ' thread expanded';
            } else if (message.has_children) {
                if (message.expanded === undefined && (this.env.autoexpand_threads == 1 || (this.env.autoexpand_threads == 2 && message.unread_children))) {
                    message.expanded = true;
                }

                expando = '<div id="rcmexpando' + row.id + '" class="' + (message.expanded ? 'expanded' : 'collapsed') + '">&nbsp;&nbsp;</div>';
                row_class += ' thread' + (message.expanded ? ' expanded' : '');
            }

            if (flags.unread_children && flags.seen && !message.expanded) {
                row_class += ' unroot';
            }

            if (flags.flagged_children && !message.expanded) {
                row_class += ' flaggedroot';
            }
        }

        tree += '<span id="msgicn' + row.id + '" class="' + css_class + status_class + '" title="' + status_label + '"></span>';
        row.className = row_class;

        // build subject link
        if (cols.subject) {
            var action  = flags.mbox == this.env.drafts_mailbox ? 'compose' : 'show',
                uid_param = flags.mbox == this.env.drafts_mailbox ? '_draft_uid' : '_uid',
                query = { _mbox: flags.mbox };

            query[uid_param] = uid;
            cols.subject = '<a href="' + this.url(action, query) + '" onclick="return rcube_event.keyboard_only(event)"'
                + ' onmouseover="rcube_webmail.long_subject_title(this)" tabindex="-1"><span>' + cols.subject + '</span></a>';
        }

        // add each submitted col
        for (n in listcols) {
            c = listcols[n];
            col = { className: String(c).toLowerCase(), events: {} };

            if (this.env.coltypes[c] && this.env.coltypes[c].hidden) {
                col.className += ' hidden';
            }

            if (c == 'flag') {
                css_class = (flags.flagged ? 'flagged' : 'unflagged');
                label = this.get_label(css_class);
                html = '<span id="flagicn' + row.id + '" class="' + css_class + '" title="' + label + '"></span>';
            } else if (c == 'attachment') {
                label = this.get_label('withattachment');
                if (flags.attachmentClass) {
                    html = '<span class="' + flags.attachmentClass + '" title="' + label + '"></span>';
                } else if (flags.ctype == 'multipart/report') {
                    html = '<span class="report"></span>';
                } else if (flags.ctype == 'multipart/encrypted' || flags.ctype == 'application/pkcs7-mime') {
                    html = '<span class="encrypted"></span>';
                } else if (flags.hasattachment || (!flags.hasnoattachment && /application\/|multipart\/(m|signed)/.test(flags.ctype))) {
                    html = '<span class="attachment" title="' + label + '"></span>';
                } else {
                    html = '&nbsp;';
                }
            } else if (c == 'status') {
                label = '';
                if (flags.deleted) {
                    css_class = 'deleted';
                    label = this.get_label('deleted');
                } else if (!flags.seen) {
                    css_class = 'unread';
                    label = this.get_label('unread');
                } else if (flags.unread_children > 0) {
                    css_class = 'unreadchildren';
                } else {
                    css_class = 'msgicon';
                }
                html = '<span id="statusicn' + row.id + '" class="' + css_class + status_class + '" title="' + label + '"></span>';
            } else if (c == 'threads') {
                html = expando;
            } else if (c == 'subject') {
                html = tree + cols[c];
            } else if (c == 'priority') {
                if (flags.prio > 0 && flags.prio < 6) {
                    label = this.get_label('priority') + ' ' + flags.prio;
                    html = '<span class="prio' + flags.prio + '" title="' + label + '"></span>';
                } else {
                    html = '&nbsp;';
                }
            } else if (c == 'folder') {
                html = '<span onmouseover="rcube_webmail.long_subject_title(this)">' + cols[c] + '<span>';
            } else {
                html = cols[c];
            }

            col.innerHTML = html;
            row.cols.push(col);
        }

        if (layout == 'widescreen') {
            row = this.widescreen_message_row(row, uid, message);
        }

        list.insert_row(row, attop);

        // remove 'old' row
        if (attop && this.env.pagesize && list.rowcount > this.env.pagesize) {
            var uid = list.get_last_row();
            list.remove_row(uid);
            list.clear_selection(uid);
        }
    };

    // Converts standard message list record into "widescreen" (3-column) layout
    this.widescreen_message_row = function (row, uid, message) {
        var domrow = document.createElement('tr');

        domrow.id = row.id;
        domrow.uid = row.uid;
        domrow.className = row.className;
        if (row.style) {
            $.extend(domrow.style, row.style);
        }

        $.each(this.env.widescreen_list_template, function () {
            if (!ref.env.threading && this.className == 'threads') {
                return;
            }

            var i, n, e, col, domcol,
                domcell = document.createElement('td');

            if (this.className) {
                domcell.className = this.className;
            }

            for (i = 0; this.cells && i < this.cells.length; i++) {
                for (n = 0; row.cols && n < row.cols.length; n++) {
                    if (this.cells[i] == row.cols[n].className) {
                        col = row.cols[n];
                        domcol = document.createElement('span');
                        domcol.className = this.cells[i];
                        if (this.className == 'subject' && domcol.className != 'subject') {
                            domcol.className += ' skip-on-drag';
                        }
                        if (col.innerHTML) {
                            domcol.innerHTML = col.innerHTML;
                        }
                        domcell.appendChild(domcol);
                        break;
                    }
                }
            }

            domrow.appendChild(domcell);
        });

        if (this.env.threading && message.depth) {
            n = this.calculate_thread_padding(message.depth);
            $('td.subject', domrow).attr('style', 'padding-left:' + n + ' !important');
            $('span.branch', domrow).remove();
        }

        return domrow;
    };

    this.calculate_thread_padding = function (level) {
        ref.env.thread_padding.match(/^([0-9.]+)(.+)/);
        return (Math.min(6, level) * parseFloat(RegExp.$1)) + RegExp.$2;
    };

    this.set_list_sorting = function (sort_col, sort_order) {
        var sort_old = this.env.sort_col == 'arrival' ? 'date' : this.env.sort_col,
            sort_new = sort_col == 'arrival' ? 'date' : sort_col;

        // set table header class
        $('#rcm' + sort_old).removeClass('sorted' + this.env.sort_order.toUpperCase());
        if (sort_new) {
            $('#rcm' + sort_new).addClass('sorted' + sort_order);
        }

        // if sorting by 'arrival' is selected, click on date column should not switch to 'date'
        $('#rcmdate > a').prop('rel', sort_col == 'arrival' ? 'arrival' : 'date');

        this.env.sort_col = sort_col;
        this.env.sort_order = sort_order;
    };

    this.set_list_options = function (cols, sort_col, sort_order, threads, layout) {
        var update, post_data = {};

        if (sort_col === undefined) {
            sort_col = this.env.sort_col;
        }
        if (!sort_order) {
            sort_order = this.env.sort_order;
        }

        if (this.env.sort_col != sort_col || this.env.sort_order != sort_order) {
            update = 1;
            this.set_list_sorting(sort_col, sort_order);
        }

        if (this.env.threading != threads) {
            update = 1;
            post_data._threads = threads;
        }

        if (layout && this.env.layout != layout) {
            this.triggerEvent('layout-change', { old_layout: this.env.layout, new_layout: layout });
            update = 1;
            this.env.layout = post_data._layout = layout;

            // update message list setup
            this.msglist_setup(this.env.layout);
        }

        if (cols && cols.length) {
            // make sure new columns are added at the end of the list
            var i, idx, name, newcols = [], oldcols = this.env.listcols;

            for (i = 0; i < oldcols.length; i++) {
                name = oldcols[i];
                idx = $.inArray(name, cols);
                if (idx != -1) {
                    newcols.push(name);
                    delete cols[idx];
                }
            }

            for (i = 0; i < cols.length; i++) {
                if (cols[i]) {
                    newcols.push(cols[i]);
                }
            }

            if (newcols.join() != oldcols.join()) {
                update = 1;
                post_data._cols = newcols.join(',');
            }
        }

        if (update) {
            this.list_mailbox('', '', sort_col + '_' + sort_order, post_data);
        }
    };

    // when user double-clicks on a row
    this.show_message = function (id, safe, preview) {
        if (!id) {
            return;
        }

        var win, target = window,
            url = this.params_from_uid(id, { _caps: this.browser_capabilities() });

        if (preview && (win = this.get_frame_window(this.env.contentframe))) {
            target = win;
            url._framed = 1;
        }

        if (safe) {
            url._safe = 1;
        }

        // also send search request to get the right messages
        if (this.env.search_request) {
            url._search = this.env.search_request;
        }

        if (this.env.extwin) {
            url._extwin = 1;
        }

        url = this.url(preview ? 'preview' : 'show', url);

        if (preview) {
            this.preview_id = id;
        }

        if (preview && String(target.location.href).indexOf(url) >= 0) {
            this.show_contentframe(true);
        } else {
            if (!preview && this.env.message_extwin && !this.env.extwin) {
                this.open_window(url, true);
            } else {
                // "Allow remote resources" reloads the page, we remove this request from the history,
                // so Back button works as expected, i.e. ignores the reload request (#6620)
                if (safe && document.referrer && window.history.replaceState) {
                    window.history.replaceState({}, '', document.referrer);
                }

                this.location_href(url, target, true);
            }
        }
    };

    // Update message status and unread counter after marking a message as read
    this.set_unread_message = function (id, folder) {
        var self = this;

        // find window with messages list
        if (!self.message_list) {
            self = self.opener();
        }

        if (!self && window.parent) {
            self = parent.rcmail;
        }

        if (!self || !self.message_list) {
            return;
        }

        // this may fail in multifolder mode
        if (self.set_message(id, 'unread', false) === false) {
            self.set_message(id + '-' + folder, 'unread', false);
        }

        if (self.env.unread_counts[folder] > 0) {
            self.env.unread_counts[folder] -= 1;
            self.set_unread_count(folder, self.env.unread_counts[folder], folder == 'INBOX' && !self.is_multifolder_listing());
        }
    };

    this.show_contentframe = function (show) {
        var frame, win, name = this.env.contentframe;

        if (frame = this.get_frame_element(name)) {
            if (!show && (win = this.get_frame_window(name))) {
                if (win.location.href.indexOf(this.env.blankpage) < 0) {
                    win.stop();
                    win.location.href = this.env.blankpage;
                }
            } else if (!bw.safari && !bw.konq) {
                $(frame)[show ? 'show' : 'hide']();
            }
        }

        if (!show) {
            this.unlock_frame();
            delete this.preview_id;
        }
    };

    this.get_frame_element = function (id) {
        var frame;

        if (id && (frame = document.getElementById(id))) {
            return frame;
        }
    };

    this.get_frame_window = function (id) {
        var frame = this.get_frame_element(id);

        if (frame && frame.name && window.frames) {
            return window.frames[frame.name];
        }
    };

    this.lock_frame = function (target) {
        var rc = this.is_framed() ? parent.rcmail : this;

        if (!rc.env.frame_lock) {
            rc.env.frame_lock = rc.set_busy(true, 'loading');
        }

        try {
            if (target.frameElement) {
                $(target.frameElement).on('load.lock', function (e) {
                    rc.unlock_frame();
                    $(this).off('load.lock');
                });
            }
        } catch (e) {
            // Ignore permission denied error
        }
    };

    this.unlock_frame = function () {
        if (this.env.frame_lock) {
            this.set_busy(false, null, this.env.frame_lock);
            this.env.frame_lock = null;
        }
    };

    // List a specific page
    this.list_page = function (page) {
        if (page == 'next') {
            page = this.env.current_page + 1;
        } else if (page == 'last') {
            page = this.env.pagecount;
        } else if (page == 'prev' && this.env.current_page > 1) {
            page = this.env.current_page - 1;
        } else if (page == 'first' && this.env.current_page > 1) {
            page = 1;
        }

        if (page > 0 && page <= this.env.pagecount) {
            this.env.current_page = page;

            if (this.task == 'addressbook' || this.contact_list) {
                this.list_contacts(this.env.source, this.env.group, page);
            } else if (this.task == 'mail') {
                this.list_mailbox(this.env.mailbox, page);
            }
        }
    };

    // Sends request to check for recent messages
    this.checkmail = function () {
        var lock = this.set_busy(true, 'checkingmail'),
            params = this.check_recent_params();

        this.http_post('check-recent', params, lock);
    };

    // List messages of a specific mailbox using filter
    this.filter_mailbox = function (filter) {
        if (this.filter_disabled) {
            return;
        }

        var params = this.search_params(false, filter),
            lock = this.set_busy(true, 'searching');

        this.clear_message_list();

        // reset vars
        this.env.current_page = 1;
        this.env.search_filter = filter;
        this.http_request('search', params, lock);
        this.update_state({ _mbox: params._mbox, _filter: filter, _scope: params._scope });
    };

    // Reload the current message listing
    this.refresh_list = function () {
        this.list_mailbox(this.env.mailbox, this.env.current_page || 1, null, { _clear: 1 }, true);
        if (this.message_list) {
            this.message_list.clear_selection();
        }
    };

    // List messages of a specific mailbox
    this.list_mailbox = function (mbox, page, sort, url, update_only) {
        var win, target = window;

        if (typeof url !== 'object') {
            url = {};
        }

        if (!mbox) {
            mbox = this.env.mailbox ? this.env.mailbox : 'INBOX';
        }

        // add sort to url if set
        if (sort) {
            url._sort = sort;
        }

        // folder change, reset page, search scope, etc.
        if (this.env.mailbox != mbox) {
            page = 1;
            this.env.current_page = page;
            this.env.search_scope = this.env.search_scope || 'base';
            this.select_all_mode = false;
            this.reset_search_filter();
        }
        // also send search request to get the right messages
        else if (this.env.search_request) {
            url._search = this.env.search_request;
        }

        if (!update_only) {
            // unselect selected messages and clear the list and message data
            this.clear_message_list();

            if (mbox != this.env.mailbox || (mbox == this.env.mailbox && !page && !sort)) {
                url._refresh = 1;
            }

            this.select_folder(mbox, '', true);
            this.unmark_folder(mbox, 'recent', '', true);
            this.env.mailbox = mbox;
        }

        // load message list remotely
        if (this.gui_objects.messagelist) {
            this.list_mailbox_remote(mbox, page, url);
            return;
        }

        if (win = this.get_frame_window(this.env.contentframe)) {
            target = win;
            url._framed = 1;
        }

        if (this.env.uid) {
            url._uid = this.env.uid;
        }

        if (page) {
            url._page = page;
        }

        // load message list to target frame/window
        if (mbox) {
            url._mbox = mbox;
            this.set_busy(true, 'loading');
            this.location_href(url, target);
        }
    };

    this.clear_message_list = function () {
        this.env.messages = {};
        this.show_contentframe(false);

        if (this.message_list) {
            this.message_list.clear(true);
        }
    };

    // send remote request to load message list
    this.list_mailbox_remote = function (mbox, page, url) {
        var lock = this.set_busy(true, 'loading');

        if (typeof url !== 'object') {
            url = {};
        }

        url._layout = this.env.layout;
        url._mbox = mbox;
        url._page = page;

        this.http_request('list', url, lock);
        this.update_state({ _mbox: mbox, _page: (page && page > 1 ? page : null) });
    };

    // Removes messages that don't exist from list selection array
    this.update_selection = function () {
        var list = this.message_list,
            selected = list.selection,
            rows = list.rows,
            i, selection = [];

        for (i in selected) {
            if (rows[selected[i]]) {
                selection.push(selected[i]);
            }
        }

        list.selection = selection;

        // reset preview frame, if currently previewed message is not selected (has been removed)
        try {
            var win = this.get_frame_window(this.env.contentframe),
                id = win.rcmail.env.uid;

            if (id && !list.in_selection(id)) {
                this.show_contentframe(false);
            }
        } catch (e) {
            // ignore
        }
    };

    // Expand all threads with unread children
    this.expand_unread = function () {
        var r, tbody = this.message_list.tbody,
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

    // Thread expanding/collapsing handler
    this.expand_message_row = function (e, uid) {
        var row = this.message_list.rows[uid];

        // handle unread_children/flagged_children mark
        row.expanded = !row.expanded;
        this.set_unread_children(uid);
        this.set_flagged_children(uid);
        row.expanded = !row.expanded;

        this.message_list.expand_row(e, uid);
    };

    // Message list expanding
    this.expand_threads = function () {
        if (!this.env.threading || !this.env.autoexpand_threads || !this.message_list) {
            return;
        }

        switch (this.env.autoexpand_threads) {
            case 2:
                this.expand_unread();
                break;
            case 1:
                this.message_list.expand_all();
                break;
        }
    };

    // Initializes threads indicators/expanders after list update
    this.init_threads = function (roots, mbox) {
        // #1487752
        if (mbox && mbox != this.env.mailbox) {
            return false;
        }

        for (var n = 0, len = roots.length; n < len; n++) {
            this.add_tree_icons(roots[n]);
        }

        this.expand_threads();
    };

    // adds threads tree icons to the list (or specified thread)
    this.add_tree_icons = function (root) {
        var i, l, r, n, len, pos, row, tmp = [], uid = [],
            rows = this.message_list.rows;

        if (root) {
            row = rows[root] ? rows[root].obj : null;
        } else {
            row = this.message_list.tbody.firstChild;
        }

        while (row) {
            if (row.nodeType == 1 && (r = rows[row.uid])) {
                if (r.depth) {
                    for (i = tmp.length - 1; i >= 0; i--) {
                        len = tmp[i].length;
                        if (len > r.depth) {
                            pos = len - r.depth;
                            if (!(tmp[i][pos] & 2)) {
                                tmp[i][pos] = tmp[i][pos] ? tmp[i][pos] + 2 : 2;
                            }
                        } else if (len == r.depth) {
                            if (!(tmp[i][0] & 2)) {
                                tmp[i][0] += 2;
                            }
                        }
                        if (r.depth > len) {
                            break;
                        }
                    }

                    tmp.push(new Array(r.depth));
                    tmp[tmp.length - 1][0] = 1;
                    uid.push(r.uid);
                } else {
                    if (tmp.length) {
                        for (i in tmp) {
                            this.set_tree_icons(uid[i], tmp[i]);
                        }
                        tmp = [];
                        uid = [];
                    }
                    if (root && row != rows[root].obj) {
                        break;
                    }
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

    // Adds tree icons to specified message row
    this.set_tree_icons = function (uid, tree) {
        var i, divs = [], html = '', len = tree.length;

        for (i = 0; i < len; i++) {
            if (tree[i] > 2) {
                divs.push({ class: 'l3', width: 15 });
            } else if (tree[i] > 1) {
                divs.push({ class: 'l2', width: 15 });
            } else if (tree[i] > 0) {
                divs.push({ class: 'l1', width: 15 });
            }
            // separator div
            else if (divs.length && !divs[divs.length - 1].class) {
                divs[divs.length - 1].width += 15;
            } else {
                divs.push({ class: null, width: 15 });
            }
        }

        for (i = divs.length - 1; i >= 0; i--) {
            if (divs[i].class) {
                html += '<div class="tree ' + divs[i].class + '" />';
            } else {
                html += '<div style="width:' + divs[i].width + 'px" />';
            }
        }

        if (html) {
            $('#rcmtab' + this.html_identifier(uid, true)).html(html);
        }
    };

    // Update parent in a thread
    this.update_thread_root = function (uid, flag) {
        if (!this.env.threading) {
            return;
        }

        var root = this.message_list.find_root(uid);

        if (uid == root) {
            return;
        }

        var p = this.message_list.rows[root];

        if (flag == 'read' && p.unread_children) {
            p.unread_children--;
        } else if (flag == 'unread' && p.has_children) {
            // unread_children may be undefined
            p.unread_children = (p.unread_children || 0) + 1;
        } else if (flag == 'unflagged' && p.flagged_children) {
            p.flagged_children--;
        } else if (flag == 'flagged' && p.has_children) {
            p.flagged_children = (p.flagged_children || 0) + 1;
        } else {
            return;
        }

        this.set_message_icon(root);
        this.set_unread_children(root);
        this.set_flagged_children(root);
    };

    // Update thread indicators for all messages in a thread below the specified message
    // return number of removed/added root level messages
    this.update_thread = function (uid) {
        if (!this.env.threading || !this.message_list.rows[uid]) {
            return 0;
        }

        var r, parent, count = 0,
            list = this.message_list,
            rows = list.rows,
            row = rows[uid],
            depth = rows[uid].depth,
            roots = [];

        if (!row.depth) { // root message: decrease roots count
            count--;
        }

        // update unread_children for thread root
        if (row.depth && row.unread) {
            parent = list.find_root(uid);
            rows[parent].unread_children--;
            this.set_unread_children(parent);
        }

        // update unread_children for thread root
        if (row.depth && row.flagged) {
            parent = list.find_root(uid);
            rows[parent].flagged_children--;
            this.set_flagged_children(parent);
        }

        parent = row.parent_uid;

        // children
        row = row.obj.nextSibling;
        while (row) {
            if (row.nodeType == 1 && (r = rows[row.uid])) {
                if (!r.depth || r.depth <= depth) {
                    break;
                }

                r.depth--; // move left
                // reset width and clear the content of a tab, icons will be added later
                $('#rcmtab' + r.id).width(r.depth * 15).html('');
                if (!r.depth) { // a new root
                    count++; // increase roots count
                    r.parent_uid = 0;
                    if (r.has_children) {
                        // replace 'leaf' with 'collapsed'
                        $('#' + r.id + ' .leaf').first()
                            .attr('id', 'rcmexpando' + r.id)
                            .attr('class', (r.obj.style.display != 'none' ? 'expanded' : 'collapsed'))
                            .mousedown({ uid: r.uid }, function (e) {
                                return ref.expand_message_row(e, e.data.uid);
                            });

                        r.unread_children = 0;
                        roots.push(r);
                    }
                    // show if it was hidden
                    if (r.obj.style.display == 'none') {
                        $(r.obj).show();
                    }
                } else {
                    if (r.depth == depth) {
                        r.parent_uid = parent;
                    }
                    if (r.unread && roots.length) {
                        roots[roots.length - 1].unread_children++;
                    }
                }
            }
            row = row.nextSibling;
        }

        // update unread_children/flagged_children for roots
        for (r = 0; r < roots.length; r++) {
            this.set_unread_children(roots[r].uid);
            this.set_flagged_children(roots[r].uid);
        }

        return count;
    };

    this.delete_excessive_thread_rows = function () {
        var rows = this.message_list.rows,
            tbody = this.message_list.tbody,
            row = tbody.firstChild,
            cnt = this.env.pagesize + 1;

        while (row) {
            if (row.nodeType == 1 && (r = rows[row.uid])) {
                if (!r.depth && cnt) {
                    cnt--;
                }

                if (!cnt) {
                    this.message_list.remove_row(row.uid);
                }
            }

            row = row.nextSibling;
        }
    };

    // Set message icon
    this.set_message_icon = function (uid) {
        var css_class, label = '',
            row = this.message_list.rows[uid];

        if (!row) {
            return false;
        }

        if (row.icon) {
            css_class = 'msgicon';
            if (row.deleted) {
                css_class += ' deleted';
                label += this.get_label('deleted') + ' ';
            } else if (row.unread) {
                css_class += ' unread';
                label += this.get_label('unread') + ' ';
            } else if (row.unread_children) {
                css_class += ' unreadchildren';
            }

            if (row.msgicon == row.icon) {
                if (row.replied) {
                    css_class += ' replied';
                    label += this.get_label('replied') + ' ';
                }
                if (row.forwarded) {
                    css_class += ' forwarded';
                    label += this.get_label('forwarded') + ' ';
                }
                css_class += ' status';
            }

            $(row.icon).attr({ class: css_class, title: label });
        }

        if (row.msgicon && row.msgicon != row.icon) {
            label = '';
            css_class = 'msgicon';
            if (!row.unread && row.unread_children) {
                css_class += ' unreadchildren';
            }
            if (row.replied) {
                css_class += ' replied';
                label += this.get_label('replied') + ' ';
            }
            if (row.forwarded) {
                css_class += ' forwarded';
                label += this.get_label('forwarded') + ' ';
            }

            $(row.msgicon).attr({ class: css_class, title: label });
        }

        if (row.flagicon) {
            css_class = (row.flagged ? 'flagged' : 'unflagged');
            label = this.get_label(css_class);
            $(row.flagicon).attr({ class: css_class, 'aria-label': label, title: label });
        }
    };

    // Set message status
    this.set_message_status = function (uid, flag, status) {
        var row = this.message_list.rows[uid];

        if (!row) {
            return false;
        }

        if (flag == 'unread') {
            if (row.unread != status) {
                this.update_thread_root(uid, status ? 'unread' : 'read');
            }
        } else if (flag == 'flagged') {
            this.update_thread_root(uid, status ? 'flagged' : 'unflagged');
        }

        if ($.inArray(flag, ['unread', 'deleted', 'replied', 'forwarded', 'flagged']) > -1) {
            row[flag] = status;
        }
    };

    // Set message row status, class and icon
    this.set_message = function (uid, flag, status) {
        var row = this.message_list && this.message_list.rows[uid];

        if (!row) {
            return false;
        }

        if (flag) {
            this.set_message_status(uid, flag, status);
        }

        if ($.inArray(flag, ['unread', 'deleted', 'flagged']) > -1) {
            $(row.obj)[row[flag] ? 'addClass' : 'removeClass'](flag);
        }

        this.set_unread_children(uid);
        this.set_message_icon(uid);
    };

    // Sets unroot (unread_children) class of parent row
    this.set_unread_children = function (uid) {
        var row = this.message_list.rows[uid];

        if (row.parent_uid) {
            return;
        }

        var enable = !row.unread && row.unread_children && !row.expanded;
        $(row.obj)[enable ? 'addClass' : 'removeClass']('unroot');
    };

    // Sets flaggedroot (flagged_children) class of parent row
    this.set_flagged_children = function (uid) {
        var row = this.message_list.rows[uid];

        if (row.parent_uid) {
            return;
        }

        var enable = row.flagged_children && !row.expanded;
        $(row.obj)[enable ? 'addClass' : 'removeClass']('flaggedroot');
    };

    // Copy selected messages to the specified mailbox
    this.copy_messages = function (mbox, event, uids) {
        if (mbox && typeof mbox === 'object') {
            if (mbox.uids) {
                uids = mbox.uids;
            }
            mbox = mbox.id;
        } else if (!mbox) {
            uids = this.env.uid ? [this.env.uid] : this.message_list.get_selection();

            return this.folder_selector(event, function (folder, obj) {
                ref.command('copy', { id: folder, uids: uids }, obj, event, true);
            });
        }

        // exit if current or no mailbox specified
        if (!mbox || mbox == this.env.mailbox) {
            return;
        }

        var post_data = this.selection_post_data({ _target_mbox: mbox, _uid: uids });

        // exit if selection is empty
        if (!post_data._uid) {
            return;
        }

        // send request to server
        this.http_post('copy', post_data, this.display_message('copyingmessage', 'loading'));
    };

    // Move selected messages to the specified mailbox
    this.move_messages = function (mbox, event, uids) {
        if (mbox && typeof mbox === 'object') {
            if (mbox.uids) {
                uids = mbox.uids;
            }
            mbox = mbox.id;
        } else if (!mbox) {
            uids = this.env.uid ? [this.env.uid] : this.message_list.get_selection();

            return this.folder_selector(event, function (folder, obj) {
                ref.command('move', { id: folder, uids: uids }, obj, event, true);
            });
        }

        // exit if current or no mailbox specified
        if (!mbox || (mbox == this.env.mailbox && !this.is_multifolder_listing())) {
            return;
        }

        var lock = false, post_data = this.selection_post_data({ _target_mbox: mbox, _uid: uids });

        // exit if selection is empty
        if (!post_data._uid) {
            return;
        }

        // show wait message
        if (this.env.action == 'show') {
            lock = this.set_busy(true, 'movingmessage');
        }

        // Hide message command buttons until a message is selected
        this.enable_command(this.env.message_commands, false);

        this.with_selected_messages('move', post_data, lock);

        if (this.env.action != 'show') {
            this.show_contentframe(false);
        }
    };

    // Delete selected messages from the current mailbox
    this.delete_messages = function (event, uid) {
        var list = this.message_list, trash = this.env.trash_mailbox;

        // if config is set to flag for deletion
        if (this.env.flag_for_deletion) {
            this.mark_message('delete', uid);
            return false;
        }

        // if there isn't a defined trash mailbox or we are in it
        if (!trash || this.env.mailbox == trash) {
            this.permanently_remove_messages(uid);
        }
        // we're in Junk folder and delete_junk is enabled
        else if (this.env.delete_junk && this.env.junk_mailbox && this.env.mailbox == this.env.junk_mailbox) {
            this.permanently_remove_messages(uid);
        }
        // if there is a trash mailbox defined and we're not currently in it
        else {
            // if shift was pressed delete it immediately
            if ((list && list.modkey == SHIFT_KEY) || (event && rcube_event.get_modifier(event) == SHIFT_KEY)) {
                this.confirm_dialog(this.get_label('deletemessagesconfirm'), 'delete', function () {
                    ref.permanently_remove_messages(uid);
                });
            } else {
                this.move_messages(trash, event, uid ? [uid] : null);
            }
        }

        return true;
    };

    // Delete the selected messages permanently
    this.permanently_remove_messages = function (uid) {
        var post_data = this.selection_post_data(uid ? { _uid: uid } : null);

        // exit if selection is empty
        if (!post_data._uid) {
            return;
        }

        this.with_selected_messages('delete', post_data);
        this.show_contentframe(false);
    };

    // Send a specific move/delete request with UIDs of all selected messages
    this.with_selected_messages = function (action, post_data, lock, http_action) {
        var msg, count = 0,
            remove = (action == 'delete' || !this.is_multifolder_listing());

        // update the list (remove rows, clear selection)
        if (this.message_list) {
            var n, len, id, root, roots = [],
                selection = post_data._uid,
                display_next = this.check_display_next();

            if (selection === '*') {
                selection = this.message_list.get_selection();
            } else if (!Array.isArray(selection)) {
                selection = String(selection).split(',');
            }

            for (n = 0, len = selection.length; n < len; n++) {
                id = selection[n];

                if (this.env.threading) {
                    count += this.update_thread(id);
                    root = this.message_list.find_root(id);
                    if (root != id && $.inArray(root, roots) < 0) {
                        roots.push(root);
                    }
                }

                if (remove) {
                    this.message_list.remove_row(id, display_next && n == selection.length - 1);
                }
            }

            // make sure there are no selected rows
            if (!display_next && remove) {
                this.message_list.clear_selection();
            }

            // update thread tree icons
            for (n = 0, len = roots.length; n < len; n++) {
                this.add_tree_icons(roots[n]);
            }
        }

        if (count < 0) {
            post_data._count = (count * -1);
        } else if (count > 0 && remove) {
            // remove threads from the end of the list
            this.delete_excessive_thread_rows();
        }

        if (!remove) {
            post_data._refresh = 1;
        }

        if (!lock) {
            msg = action == 'move' ? 'movingmessage' : 'deletingmessage';
            lock = this.display_message(msg, 'loading');
        }

        // send request to server
        this.http_post(http_action || action, post_data, lock);
    };

    // Build post data for message delete/move/copy/flag requests
    this.selection_post_data = function (data) {
        if (!data || typeof (data) !== 'object') {
            data = {};
        }

        if (!data._uid) {
            data._uid = this.env.uid ? [this.env.uid] : this.message_list.get_selection();
        }

        data._mbox = this.env.mailbox;
        data._uid = this.uids_to_list(data._uid);

        if (this.env.action) {
            data._from = this.env.action;
        }

        // also send search request to get the right messages
        if (this.env.search_request) {
            data._search = this.env.search_request;
        }

        if (this.env.display_next && this.env.next_uid) {
            data._next_uid = this.env.next_uid;
        }

        return data;
    };

    this.check_display_next = function () {
        return this.env.display_next && (this.preview_id || !this.env.contentframe);
    };

    // Set a specific flag to one or more messages
    this.mark_message = function (flag, uid) {
        var a_uids = [], r_uids = [], len, n, id,
            list = this.message_list;

        if (uid) {
            a_uids.push(uid);
        } else if (this.env.uid) {
            a_uids.push(this.env.uid);
        } else if (list) {
            a_uids = list.get_selection();
        }

        if (!list) {
            r_uids = a_uids;
        } else {
            list.focus();
            for (n = 0, len = a_uids.length; n < len; n++) {
                id = a_uids[n];
                if ((flag == 'read' && list.rows[id].unread)
                    || (flag == 'unread' && !list.rows[id].unread)
                    || (flag == 'delete' && !list.rows[id].deleted)
                    || (flag == 'undelete' && list.rows[id].deleted)
                    || (flag == 'flagged' && !list.rows[id].flagged)
                    || (flag == 'unflagged' && list.rows[id].flagged)
                ) {
                    r_uids.push(id);
                }
            }
        }

        // nothing to do
        if (!r_uids.length && !this.select_all_mode) {
            return;
        }

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

    // Set class to read/unread
    this.toggle_read_status = function (flag, a_uids) {
        var i, len = a_uids.length,
            post_data = this.selection_post_data({ _uid: a_uids, _flag: flag }),
            lock = this.display_message('markingmessage', 'loading');

        // mark all message rows as read/unread
        for (i = 0; i < len; i++) {
            this.set_message(a_uids[i], 'unread', (flag == 'unread' ? true : false));
        }

        this.http_post('mark', post_data, lock);
    };

    // Set image to flagged or unflagged
    this.toggle_flagged_status = function (flag, a_uids) {
        var i, len = a_uids.length,
            win = this.env.contentframe ? this.get_frame_window(this.env.contentframe) : window,
            post_data = this.selection_post_data({ _uid: a_uids, _flag: flag }),
            lock = this.display_message('markingmessage', 'loading');

        // mark all message rows as flagged/unflagged
        for (i = 0; i < len; i++) {
            this.set_message(a_uids[i], 'flagged', (flag == 'flagged' ? true : false));
        }

        if (this.env.action == 'show' || $.inArray(this.preview_id, a_uids) >= 0) {
            $(win.document.body)[flag == 'flagged' ? 'addClass' : 'removeClass']('status-flagged');
        }

        this.http_post('mark', post_data, lock);
    };

    // Mark all message rows as deleted/undeleted
    this.toggle_delete_status = function (a_uids) {
        var i, uid, all_deleted = true,
            len = a_uids.length,
            rows = this.message_list ? this.message_list.rows : {};

        if (len == 1) {
            if (!this.message_list || (rows[a_uids[0]] && !rows[a_uids[0]].deleted)) {
                this.flag_as_deleted(a_uids);
            } else {
                this.flag_as_undeleted(a_uids);
            }

            return true;
        }

        for (i = 0; i < len; i++) {
            uid = a_uids[i];
            if (rows[uid] && !rows[uid].deleted) {
                all_deleted = false;
                break;
            }
        }

        if (all_deleted) {
            this.flag_as_undeleted(a_uids);
        } else {
            this.flag_as_deleted(a_uids);
        }

        return true;
    };

    this.flag_as_undeleted = function (a_uids) {
        var i, len = a_uids.length,
            post_data = this.selection_post_data({ _uid: a_uids, _flag: 'undelete' }),
            lock = this.display_message('markingmessage', 'loading');

        for (i = 0; i < len; i++) {
            this.set_message(a_uids[i], 'deleted', false);
        }

        this.http_post('mark', post_data, lock);
    };

    this.flag_as_deleted = function (a_uids) {
        var count = 0, r_uids = [],
            post_data = this.selection_post_data({ _uid: a_uids, _flag: 'delete' }),
            lock = this.display_message('markingmessage', 'loading'),
            list = this.message_list,
            rows = list ? list.rows : {},
            display_next = this.check_display_next();

        for (var i = 0, len = a_uids.length; i < len; i++) {
            uid = a_uids[i];
            if (rows[uid]) {
                if (rows[uid].unread) {
                    r_uids[r_uids.length] = uid;
                }

                if (this.env.skip_deleted) {
                    count += this.update_thread(uid);
                    list.remove_row(uid, display_next && i == list.get_selection(false).length - 1);
                } else {
                    this.set_message(uid, 'deleted', true);
                }
            }
        }

        // make sure there are no selected rows
        if (this.env.skip_deleted && list) {
            if (!display_next || !list.rowcount) {
                list.clear_selection();
            }
            if (count < 0) {
                post_data._count = (count * -1);
            } else if (count > 0) {
                // remove threads from the end of the list
                this.delete_excessive_thread_rows();
            }
        }

        // set of messages to mark as seen
        if (r_uids.length) {
            post_data._ruid = this.uids_to_list(r_uids);
        }

        if (this.env.skip_deleted && this.env.display_next && this.env.next_uid) {
            post_data._next_uid = this.env.next_uid;
        }

        this.http_post('mark', post_data, lock);
    };

    // Flag as read without mark request (called from backend).
    // The argument should be a coma-separated list of uids
    this.flag_deleted_as_read = function (uids) {
        var uid, i, len,
            rows = this.message_list ? this.message_list.rows : {};

        if (typeof uids === 'string') {
            uids = uids.split(',');
        }

        for (i = 0, len = uids.length; i < len; i++) {
            uid = uids[i];
            if (rows[uid]) {
                this.set_message(uid, 'unread', false);
            }
        }
    };

    // Converts array of message UIDs to comma-separated list for use in URL
    // with select_all mode checking
    this.uids_to_list = function (uids) {
        if (this.select_all_mode) {
            return '*';
        }

        // multi-folder list of uids cannot be passed as a string (#6845)
        if (Array.isArray(uids) && (uids.length == 1 || String(uids[0]).indexOf('-') == -1)) {
            uids = uids.join(',');
        }

        return uids;
    };

    // Sets title of the delete button
    this.set_button_titles = function () {
        var label = 'deletemessage';

        if (!this.env.flag_for_deletion
            && this.env.trash_mailbox && this.env.mailbox != this.env.trash_mailbox
            && (!this.env.delete_junk || !this.env.junk_mailbox || this.env.mailbox != this.env.junk_mailbox)
        ) {
            label = 'movemessagetotrash';
        }

        this.set_alttext('delete', label);
    };

    // Initialize input element for list page jump
    this.init_pagejumper = function (element) {
        $(element).addClass('rcpagejumper')
            .on('focus', function (e) {
                // create and display popup with page selection
                var i, html = '';

                for (i = 1; i <= ref.env.pagecount; i++) {
                    html += '<li>' + i + '</li>';
                }

                html = '<ul class="toolbarmenu menu">' + html + '</ul>';

                if (!ref.pagejump) {
                    ref.pagejump = $('<div id="pagejump-selector" class="popupmenu"></div>')
                        .appendTo(document.body)
                        .on('click', 'li', function () {
                            if (!ref.busy) {
                                $(element).val($(this).text()).change();
                            }
                        });
                }

                if (ref.pagejump.data('count') != i) {
                    ref.pagejump.html(html);
                }

                ref.pagejump.attr('rel', '#' + this.id).data('count', i);

                // display page selector
                ref.show_menu('pagejump-selector', true, e);
            })
            // keyboard navigation
            .on('keydown keyup click', function (e) {
                var current, selector = $('#pagejump-selector'),
                    ul = $('ul', selector),
                    list = $('li', ul),
                    height = ul.height(),
                    p = parseInt(this.value, 10);

                if (e.which != 27 && e.which != 9 && e.which != 13 && !selector.is(':visible')) {
                    return ref.show_menu('pagejump-selector', true, e);
                }

                if (e.type == 'keydown') {
                    switch (e.which) {
                        case 40: // arrow-down
                            if (list.length > p) {
                                this.value = (p += 1);
                            }

                            break;
                        case 38: // arrow-up
                            if (p > 1 && list.length > p - 1) {
                                this.value = (p -= 1);
                            }

                            break;
                        case 13: // enter
                            return $(this).change();
                        case 27: // esc
                        case 9: // tab
                            ref.hide_menu('pagejump-selector', e);
                            return $(element).val(ref.env.current_page);
                    }
                }

                $('li.selected', ul).removeClass('selected');

                if ((current = $(list[p - 1])).length) {
                    current.addClass('selected');
                    $('#pagejump-selector').scrollTop(((ul.height() / list.length) * (p - 1)) - selector.height() / 2);
                }
            })
            .on('change', function (e) {
                // go to specified page
                var p = parseInt(this.value, 10);
                if (p && p != ref.env.current_page && !ref.busy) {
                    ref.hide_menu('pagejump-selector', e);
                    ref.list_page(p);
                }
            });
    };

    // Update page-jumper state on list updates
    this.update_pagejumper = function () {
        $('input.rcpagejumper').val(this.env.current_page).prop('disabled', this.env.pagecount < 2);
    };

    // Check for mailvelope API
    this.check_mailvelope = function (action) {
        if (window.mailvelope) {
            this.mailvelope_load(action);
        } else {
            $(window).on('mailvelope', function () {
                ref.mailvelope_load(action);
            });
        }
    };

    /**
     * Triggger Mailvelope to add the current domain to the list of authorized
     * domains (with API access).
     */
    this.mailvelope_enable = function () {
        // Remove warning and enabling button if mailvelope was enabled.
        window.addEventListener('mailvelope', function (ev) {
            $('#mailvelope-warning').hide();
        });
        // Trigger Mailvelope.
        $('body').append('<iframe style="display: none;" src="https://api.mailvelope.com/authorize-domain/?api=true" />');
    };

    // Load Mailvelope functionality (and initialize keyring if needed)
    this.mailvelope_load = function (action) {
        // Make the server code aware that this browser now knows about
        // PGP/MIME (would otherwise only be recognized after the next login.
        this.env.browser_capabilities.pgpmime = 1;
        var keyring = this.env.mailvelope_main_keyring ? undefined : this.env.user_id,
            fn = function (kr) {
                ref.mailvelope_keyring = kr;
                ref.mailvelope_init(action, kr);
            };

        mailvelope.getVersion().then(function (v) {
            mailvelope.VERSION = v;
            mailvelope.VERSION_MAJOR = Math.floor(parseFloat(v));
            return mailvelope.getKeyring(keyring);
        }).then(fn, function (err) {
            if (keyring) {
                // attempt to create a new keyring for this app/user
                mailvelope.createKeyring(keyring).then(fn, function (err) {
                    console.error(err);
                });
            } else {
                console.error(err);
            }
        });
    };

    // Initializes Mailvelope editor or display container
    this.mailvelope_init = function (action, keyring) {
        if (!window.mailvelope) {
            return;
        }

        if (action == 'show' || action == 'preview' || action == 'print') {
            // decrypt text body
            if (this.env.is_pgp_content) {
                var data = $(this.env.is_pgp_content).text();
                ref.mailvelope_display_container(this.env.is_pgp_content, data, keyring);
            }
            // load pgp/mime message and pass it to the mailvelope display container
            else if (this.env.pgp_mime_part) {
                var msgid = this.display_message('loadingdata', 'loading'),
                    selector = this.env.pgp_mime_container;

                $.ajax({
                    type: 'GET',
                    url: this.url('get', { _mbox: this.env.mailbox, _uid: this.env.uid, _part: this.env.pgp_mime_part }),
                    error: function (o, status, err) {
                        ref.http_error(o, status, err, msgid);
                    },
                    success: function (data) {
                        ref.mailvelope_display_container(selector, data, keyring, msgid);
                    },
                });
            }
        } else if (action == 'compose') {
            this.env.compose_commands.push('compose-encrypted');

            var sign_supported = mailvelope.VERSION_MAJOR >= 2,
                is_html = $('[name="_is_html"]').val() > 0;

            if (sign_supported) {
                this.env.compose_commands.push('compose-encrypted-signed');
            }

            if (this.env.pgp_mime_message) {
                // fetch PGP/Mime part and open load into Mailvelope editor
                var lock = this.set_busy(true, this.get_label('loadingdata'));

                $.ajax({
                    type: 'GET',
                    url: this.url('get', this.env.pgp_mime_message),
                    error: function (o, status, err) {
                        ref.http_error(o, status, err, lock);
                        ref.enable_command('compose-encrypted', !is_html);
                        if (sign_supported) {
                            ref.enable_command('compose-encrypted-signed', !is_html);
                        }
                    },
                    success: function (data) {
                        ref.set_busy(false, null, lock);

                        if (is_html) {
                            ref.command('toggle-editor', { html: false, noconvert: true });
                            $('#' + ref.env.composebody).val('');
                        }

                        ref.compose_encrypted({ quotedMail: data });
                        ref.enable_command('compose-encrypted', true);
                        ref.enable_command('compose-encrypted-signed', false);
                    },
                });
            } else {
                // enable encrypted compose toggle
                this.enable_command('compose-encrypted', !is_html);
                if (sign_supported) {
                    this.enable_command('compose-encrypted-signed', !is_html);
                }
            }

            // make sure to disable encryption button after toggling editor into HTML mode
            this.addEventListener('actionafter', function (args) {
                if (args.ret && args.action == 'toggle-editor') {
                    ref.enable_command('compose-encrypted', !args.props.html);
                    if (sign_supported) {
                        ref.enable_command('compose-encrypted-signed', !args.props.html);
                    }
                }
            });
        } else if (action == 'edit-identity') {
            ref.mailvelope_identity_keygen();
        }
    };

    // Handler for the 'compose-encrypted-signed' command
    this.compose_encrypted_signed = function (props) {
        props = props || {};
        props.signMsg = true;
        this.compose_encrypted(props);
    };

    // Handler for the 'compose-encrypted' command
    this.compose_encrypted = function (props) {
        var options, container = $('#' + this.env.composebody).parent();

        // remove Mailvelope editor if active
        if (ref.mailvelope_editor) {
            ref.mailvelope_editor = null;
            ref.set_button('compose-encrypted', 'act');

            container.removeClass('mailvelope').find('iframe:not([aria-hidden=true])').remove();
            $('#' + ref.env.composebody).show();
            $("[name='_pgpmime']").remove();

            // re-enable commands that operate on the compose body
            ref.enable_command('toggle-editor', 'insert-response', true);
            ref.enable_command('spellcheck', !!ref.editor.spellchecker);
            ref.enable_command('insert-sig', !!(ref.env.signatures && ref.env.identity && ref.env.signatures[ref.env.identity]));

            ref.triggerEvent('compose-encrypted', { active: false });
        }
        // embed Mailvelope editor container
        else {
            if (this.spellcheck_state()) {
                this.editor.spellcheck_stop();
            }

            if (props.quotedMail) {
                options = { quotedMail: props.quotedMail, quotedMailIndent: false };
            } else {
                options = { predefinedText: $('#' + this.env.composebody).val() };
            }

            if (props.signMsg) {
                options.signMsg = props.signMsg;
            }

            if (this.env.compose_mode == 'reply') {
                options.quotedMailIndent = true;
                options.quotedMailHeader = this.env.compose_reply_header;
            }

            mailvelope.createEditorContainer('#' + container.attr('id'), ref.mailvelope_keyring, options).then(function (editor) {
                ref.mailvelope_editor = editor;
                ref.set_button('compose-encrypted', 'sel');

                container.addClass('mailvelope');
                $('#' + ref.env.composebody).hide();

                // disable commands that operate on the compose body
                ref.enable_command('spellcheck', 'insert-sig', 'toggle-editor', 'insert-response', false);
                ref.triggerEvent('compose-encrypted', { active: true });

                if (!$.isEmptyObject(ref.env.attachments)) {
                    // notify user if losing attachments
                    if (ref.env.compose_mode != 'draft'
                        || Object.keys(ref.env.attachments).length != 1
                        || ref.env.attachments[Object.keys(ref.env.attachments)[0]].name != 'encrypted.asc'
                    ) {
                        ref.alert_dialog(ref.get_label('encryptnoattachments'));
                    }

                    $.each(ref.env.attachments, function (name, attach) {
                        ref.remove_from_attachment_list(name);
                    });
                }
            }, function (err) {
                console.error(err);
                console.log(options);
            });
        }
    };

    // Callback to replace the message body with the full armored
    this.mailvelope_submit_messageform = function (draft, saveonly) {
        var recipients = [];

        // get recipients
        $.each(['to', 'cc', 'bcc'], function (i, field) {
            var pos, rcpt, val = $('[name="_' + field + '"]').val().trim();
            while (val.length && rcube_check_email(val, true)) {
                rcpt = RegExp.$2.replace(/^<+/, '').replace(/>+$/, '');
                recipients.push(rcpt);
                val = val.substr(val.indexOf(rcpt) + rcpt.length + 1).replace(/^\s*,\s*/, '');
            }
        });

        var isvalid = recipients.length > 0;

        // check if we have keys for all recipients
        ref.mailvelope_keyring.validKeyForAddress(recipients).then(function (status) {
            var missing_keys = [];
            $.each(status, function (k, v) {
                if (v === false) {
                    isvalid = false;
                    missing_keys.push(k);
                }
            });

            // list recipients with missing keys
            if (!isvalid && missing_keys.length) {
                // display dialog with missing keys
                ref.simple_dialog(
                    ref.get_label('nopubkeyfor').replace('$email', missing_keys.join(', '))
                        + '<p>' + ref.get_label('searchpubkeyservers') + '</p>',
                    'encryptedsendialog',
                    function () {
                        ref.mailvelope_search_pubkeys(missing_keys, function () {
                            return true; // close dialog
                        });
                    },
                    { button: 'search' }
                );
                return false;
            }

            if (!isvalid) {
                if (!recipients.length) {
                    ref.alert_dialog(ref.get_label('norecipientwarning'), function () {
                        $("[name='_to']").focus();
                    });
                }
                return false;
            }

            // add sender identity to recipients to be able to decrypt our very own message
            var senders = [], selected_sender = ref.env.identities[$("[name='_from'] option:selected").val()];
            $.each(ref.env.identities, function (k, sender) {
                senders.push(sender.email);
            });

            ref.mailvelope_keyring.validKeyForAddress(senders).then(function (status) {
                valid_sender = null;
                $.each(status, function (k, v) {
                    if (v !== false) {
                        valid_sender = k;
                        if (valid_sender == selected_sender) {
                            return false; // break
                        }
                    }
                });

                if (!valid_sender) {
                    if (!confirm(ref.get_label('nopubkeyforsender'))) {
                        return false;
                    }
                }

                recipients.push(valid_sender);

                ref.mailvelope_editor.encrypt(recipients).then(function (armored) {
                    // all checks passed, send message
                    var form = ref.gui_objects.messageform,
                        hidden = $("[name='_pgpmime']", form),
                        msgid = ref.set_busy(true, draft || saveonly ? 'savingmessage' : 'sendingmessage');

                    form.target = ref.get_save_target(msgid);
                    form._draft.value = draft ? '1' : '';
                    form.action = ref.add_url(form.action, '_unlock', msgid);
                    form.action = ref.add_url(form.action, '_framed', 1);

                    if (saveonly) {
                        form.action = ref.add_url(form.action, '_saveonly', 1);
                    }

                    // send pgp content via hidden field
                    if (!hidden.length) {
                        hidden = $('<input type="hidden" name="_pgpmime">').appendTo(form);
                    }
                    hidden.val(armored);

                    form.submit();
                }, function (err) {
                    console.log(err);
                }); // mailvelope_editor.encrypt()
            }, function (err) {
                console.error(err);
            }); // mailvelope_keyring.validKeyForAddress(senders)
        }, function (err) {
            console.error(err);
        }); // mailvelope_keyring.validKeyForAddress(recipients)

        return false;
    };

    // Wrapper for the mailvelope.createDisplayContainer API call
    this.mailvelope_display_container = function (selector, data, keyring, msgid) {
        var error_handler = function (error) {
            // remove mailvelope frame with the error message
            $(selector + ' > iframe').remove();
            ref.hide_message(msgid);
            ref.display_message(error.message, 'error');
        };

        mailvelope.createDisplayContainer(selector, data, keyring, { senderAddress: this.env.sender }).then(function (status) {
            if (status.error && status.error.message) {
                return error_handler(status.error);
            }

            ref.hide_message(msgid);
            $(selector).children().not('iframe').hide();
            $(ref.gui_objects.messagebody).addClass('mailvelope');

            // on success we can remove encrypted part from the attachments list
            if (ref.env.pgp_mime_part) {
                $('#attach' + ref.env.pgp_mime_part).remove();
            }

            setTimeout(function () {
                $(window).resize();
            }, 10);
        }, error_handler);
    };

    // Subroutine to query keyservers for public keys
    this.mailvelope_search_pubkeys = function (emails, resolve, import_handler) {
        // query with publickey.js
        var deferred = [],
            pk = new PublicKey(this.env.keyservers),
            lock = ref.display_message('', 'loading');

        $.each(emails, function (i, email) {
            var d = $.Deferred();
            pk.search(email, function (results, errorCode) {
                if (errorCode !== null) {
                    // rejecting would make all fail
                    // d.reject(email);
                    d.resolve([email]);
                } else {
                    d.resolve([email].concat(results));
                }
            });
            deferred.push(d);
        });

        $.when.apply($, deferred).then(function () {
            var missing_keys = [],
                key_selection = [];

            // analyze results of all queries
            $.each(arguments, function (i, result) {
                var email = result.shift();
                if (!result.length) {
                    missing_keys.push(email);
                } else {
                    key_selection = key_selection.concat(result);
                }
            });

            ref.hide_message(lock);
            resolve(true);

            // show key import dialog
            if (key_selection.length) {
                ref.mailvelope_key_import_dialog(key_selection, import_handler);
            }
            // some keys could not be found
            if (missing_keys.length) {
                ref.display_message(ref.get_label('nopubkeyfor').replace('$email', missing_keys.join(', ')), 'warning');
            }
        }).fail(function () {
            console.error('Pubkey lookup failed with', arguments);
            ref.hide_message(lock);
            ref.display_message('pubkeysearcherror', 'error');
            resolve(false);
        });
    };

    // List the given public keys in a dialog with options to import
    // them into the local Mailvelope keyring
    this.mailvelope_key_import_dialog = function (candidates, import_handler) {
        var ul = $('<div>').addClass('listing pgpkeyimport'),
            get_classes = function (uid, init) {
                var classes = [init];
                if (uid.revoked) {
                    classes.push('revoked');
                }
                if (uid.disabled) {
                    classes.push('disabled');
                }
                if (uid.expired) {
                    classes.push('expired');
                }
                return classes;
            };

        $.each(candidates, function (i, keyrec) {
            var li = $('<div>').addClass(get_classes(keyrec, 'key'));

            li.append($('<label>').addClass('keyid').text(ref.get_label('keyid')));
            li.append($('<a>').text(keyrec.keyid.substr(-8).toUpperCase())
                .attr({ href: keyrec.info, target: '_blank', tabindex: '-1' }));

            li.append($('<label>').addClass('keylen').text(ref.get_label('keylength')));
            li.append($('<span>').text(keyrec.keylen));

            if (keyrec.expirationdate) {
                li.append($('<label>').addClass('keyexpired').text(ref.get_label('keyexpired')));
                li.append($('<span>').text(new Date(keyrec.expirationdate * 1000).toDateString()));
            }

            if (keyrec.revoked) {
                li.append($('<span>').addClass('keyrevoked').text(ref.get_label('keyrevoked')));
            }

            var ul_ = $('<ul>').addClass('uids');
            $.each(keyrec.uids, function (j, uid) {
                ul_.append($('<li>').addClass(get_classes(uid, 'uid')).text(uid.uid));
            });

            li.append(ul_);
            li.append($('<button>')
                .attr('rel', keyrec.keyid)
                .text(ref.get_label('import'))
                .addClass('button import importkey')
                .prop('disabled', keyrec.revoked || keyrec.disabled || keyrec.expired));

            ul.append(li);
        });

        // display dialog with missing keys
        ref.simple_dialog(
            $('<div>').append($('<p>').html(ref.get_label('encryptpubkeysfound'))).append(ul),
            'importpubkeys',
            null,
            { cancel_label: 'close', cancel_button: 'close' }
        );

        // delegate handler for import button clicks
        ul.on('click', 'button.importkey', function () {
            var btn = $(this),
                keyid = btn.attr('rel'),
                pk = new PublicKey(ref.env.keyservers),
                lock = ref.display_message('', 'loading');

            // fetch from keyserver and import to Mailvelope keyring
            pk.get(keyid, function (armored, errorCode) {
                ref.hide_message(lock);

                if (errorCode) {
                    ref.display_message('keyservererror', 'error');
                    return;
                }

                if (import_handler) {
                    import_handler(armored);
                    return;
                }

                // import to keyring
                ref.mailvelope_keyring.importPublicKey(armored).then(function (status) {
                    if (status === 'REJECTED') {
                        // ref.alert_dialog(ref.get_label('Key import was rejected'));
                    } else {
                        var $key = keyid.substr(-8).toUpperCase();
                        btn.closest('.key').fadeOut();
                        ref.display_message(ref.get_label('keyimportsuccess').replace('$key', $key), 'confirmation');
                    }
                }, function (err) {
                    console.log(err);
                });
            });
        });
    };

    // enable key management for identity
    this.mailvelope_identity_keygen = function () {
        var container = $(this.gui_objects.editform).find('.identity-encryption').first(),
            identity_email = $(this.gui_objects.editform).find('.ff_email').val().trim();

        if (!container.length || !identity_email || !this.mailvelope_keyring.createKeyGenContainer) {
            return;
        }

        var key_fingerprint;
        this.mailvelope_keyring.validKeyForAddress([identity_email])
            .then(function (keys) {
                var private_keys = [];

                if (keys && keys[identity_email] && Array.isArray(keys[identity_email].keys)) {
                    var checks = [];
                    for (var j = 0; j < keys[identity_email].keys.length; j++) {
                        checks.push((function (key) {
                            return ref.mailvelope_keyring.hasPrivateKey(key.fingerprint)
                                .then(function (found) {
                                    if (found) {
                                        private_keys.push(key);
                                    }
                                });
                        })(keys[identity_email].keys[j]));
                    }
                    return Promise.all(checks)
                        .then(function () {
                            return private_keys;
                        });
                }

                return private_keys;
            })
            .then(function (private_keys) {
                var content = container.find('.identity-encryption-block').empty();
                if (private_keys && private_keys.length) {
                    // show private key information
                    $('<p>').text(ref.get_label('encryptionprivkeysinmailvelope').replace('$nr', private_keys.length)).appendTo(content);
                    var ul = $('<ul>').addClass('keylist').appendTo(content);
                    $.each(private_keys, function (i, key) {
                        $('<li>').appendTo(ul)
                            .append($('<strong>').addClass('fingerprint').text(String(key.fingerprint).toUpperCase()))
                            .append($('<span>').addClass('identity').text('<' + identity_email + '> '));
                    });
                } else {
                    $('<p>').text(ref.get_label('encryptionnoprivkeysinmailvelope')).appendTo(content);
                }

                // show button to create a new key
                $('<button>')
                    .attr('type', 'button')
                    .addClass('button create')
                    .text(ref.get_label('encryptioncreatekey'))
                    .appendTo(content)
                    .on('click', function () {
                        ref.mailvelope_show_keygen_container(content, identity_email);
                    });
                $('<span>').addClass('space').html('&nbsp;').appendTo(content);
                $('<button>')
                    .attr('type', 'button')
                    .addClass('button settings')
                    .text(ref.get_label('openmailvelopesettings'))
                    .appendTo(content)
                    .on('click', function () {
                        ref.mailvelope_keyring.openSettings();
                    });

                container.show();
                ref.triggerEvent('identity-encryption-show', { container: container });
            })
            .catch(function (err) {
                console.error('Mailvelope keyring error', err);
            });
    };

    // Start pgp key generation using Mailvelope
    this.mailvelope_show_keygen_container = function (container, identity_email) {
        var cid = new Date().getTime(),
            user_id = { email: identity_email, fullName: $(ref.gui_objects.editform).find('.ff_name').val().trim() },
            options = { userIds: [user_id], keySize: this.env.mailvelope_keysize };

        $('<div>').attr('id', 'mailvelope-keygen-container-' + cid)
            .css({ height: '245px', marginBottom: '10px' })
            .appendTo(container.empty());

        this.mailvelope_keyring.createKeyGenContainer('#mailvelope-keygen-container-' + cid, options)
            .then(function (generator) {
                if (generator instanceof Error) {
                    throw generator;
                }

                // append button to start key generation
                $('<button>')
                    .attr('type', 'button')
                    .addClass('button mainaction generate')
                    .text(ref.get_label('generate'))
                    .appendTo(container)
                    .on('click', function () {
                        var btn = $(this).prop('disabled', true);
                        generator.generate()
                            .then(function (result) {
                                if (typeof result === 'string' && result.indexOf('BEGIN PGP') > 0) {
                                    ref.display_message(ref.get_label('keypaircreatesuccess').replace('$identity', identity_email), 'confirmation');
                                    // reset keygen view
                                    ref.mailvelope_identity_keygen();
                                }
                            })
                            .catch(function (err) {
                                debugger;
                                ref.display_message(err.message || 'errortitle', 'error');
                                btn.prop('disabled', false);
                            });
                    });

                $('<span>').addClass('space').html('&nbsp;').appendTo(container);

                $('<button>')
                    .attr('type', 'button')
                    .addClass('button cancel')
                    .text(ref.get_label('cancel'))
                    .appendTo(container)
                    .on('click', function () {
                        ref.mailvelope_identity_keygen();
                    });

                ref.triggerEvent('identity-encryption-update', { container: container });
            })
            .catch(function (err) {
                ref.display_message('errortitle', 'error');
                // start over
                ref.mailvelope_identity_keygen();
            });
    };

    this.mdn_request_dialog = function (uid, mailbox) {
        var props = {
                action: 'mark',
                data: { _uid: uid, _mbox: mailbox, _flag: 'mdnsent' },
            },
            buttons = [
                {
                    text: this.get_label('send'),
                    class: 'mainaction send',
                    click: function (e, ui, dialog) {
                        props.action = 'sendmdn';
                        (ref.is_framed() ? parent.$ : $)(dialog || this).dialog('close');
                    },
                },
                {
                    text: this.get_label('ignore'),
                    class: 'cancel',
                    click: function (e, ui, dialog) {
                        (ref.is_framed() ? parent.$ : $)(dialog || this).dialog('close');
                    },
                },
            ],
            mdn_func = function (event, ui) {
                ref.http_post(props.action, props.data);
                // from default close function
                $(this).remove();
            };

        if (this.env.mdn_request_save) {
            buttons.unshift({
                text: this.get_label('sendalwaysto').replace('$email', this.env.mdn_request_sender.mailto),
                class: 'mainaction send',
                click: function (e, ui, dialog) {
                    props.data._save = ref.env.mdn_request_save;
                    props.data._address = ref.env.mdn_request_sender.string;
                    $(e.target).next().click();
                },
            });
        }

        this.show_popup_dialog(this.get_label('mdnrequest'), this.get_label('sendreceipt'), buttons, { close: mdn_func });
    };


    /*********************************************************/
    /*                mailbox folders methods                */
    /*********************************************************/

    this.expunge_mailbox = function (mbox) {
        var lock, post_data = { _mbox: mbox };

        // lock interface if it's the active mailbox
        if (mbox == this.env.mailbox) {
            lock = this.set_busy(true, 'loading');
            post_data._reload = 1;

            if (this.env.search_request) {
                post_data._search = this.env.search_request;
            }
        }

        // send request to server
        this.http_post('expunge', post_data, lock);
    };

    this.purge_mailbox = function (mbox) {
        this.confirm_dialog(this.get_label('purgefolderconfirm'), 'delete', function () {
            var lock, post_data = { _mbox: mbox };

            // lock interface if it's the active mailbox
            if (mbox == ref.env.mailbox) {
                lock = ref.set_busy(true, 'loading');
                post_data._reload = 1;
            }

            // send request to server
            ref.http_post('purge', post_data, lock);
        });

        return false;
    };

    // Mark all messages as read in:
    //   - selected folder (mode=cur)
    //   - selected folder and its subfolders (mode=sub)
    //   - all folders (mode=all)
    this.mark_all_read = function (mbox, mode) {
        var state, content, nodes = [],
            list = this.message_list,
            folder = mbox || this.env.mailbox,
            post_data = {
                _uid: '*', _flag: 'read', _mbox: folder, _folders: mode,
            };

        if (typeof mode !== 'string') {
            state = this.mark_all_read_state(folder);
            if (!state) {
                return;
            }

            if (state > 1) {
                // build content of the dialog
                $.each({ cur: 1, sub: 2, all: 4 }, function (i, v) {
                    var id = 'readallmode' + i,
                        label = $('<label>').attr('for', id).text(ref.get_label('folders-' + i)),
                        input = $('<input>').attr({
                            type: 'radio', value: i, name: 'mode', id: id, disabled: !(state & v),
                        });

                    nodes.push($('<li>').append([input, label]));
                });

                content = $('<ul class="proplist">').append(nodes);
                $('input:not([disabled])', content).first().attr('checked', true);

                this.simple_dialog(content, 'markallread',
                    function () {
                        ref.mark_all_read(folder, $('input:checked', content).val());
                        return true;
                    },
                    { button: 'mark', button_class: 'save' }
                );

                return;
            }

            post_data._folders = 'cur'; // only current folder has unread messages
        }

        // mark messages on the list
        $.each(list ? list.rows : [], function (uid, row) {
            if (!row.unread) {
                return;
            }

            var mbox = ref.env.messages[uid].mbox;
            if (mode == 'all' || mbox == ref.env.mailbox
                || (mode == 'sub' && mbox.startsWith(ref.env.mailbox + ref.env.delimiter))
            ) {
                ref.set_message(uid, 'unread', false);
            }
        });

        // send the request
        this.http_post('mark', post_data, this.display_message('markingmessage', 'loading'));
    };

    // Enable/disable mark-all-read action depending on folders state
    this.mark_all_read_state = function (mbox) {
        var state = 0,
            li = this.treelist.get_item(mbox || this.env.mailbox),
            folder_item = $(li).is('.unread') ? 1 : 0,
            subfolder_items = $('li.unread', li).length,
            all_items = $('li.unread', ref.gui_objects.folderlist).length;

        state += folder_item;
        state += subfolder_items ? 2 : 0;
        state += all_items > folder_item + subfolder_items ? 4 : 0;

        this.enable_command('mark-all-read', state > 0);

        return state;
    };

    // Display "bounce message" dialog
    this.bounce = function (props, obj, event) {
        // get message uid and folder
        var uid = this.get_single_uid(),
            url = this.url('bounce', { _framed: 1, _uid: uid, _mbox: this.get_message_mailbox(uid) }),
            dialog = $('<iframe>').attr('src', url),
            get_form = function () {
                var rc = $('iframe', dialog)[0].contentWindow.rcmail;
                return { rc: rc, form: rc.gui_objects.messageform };
            },
            post_func = function () {
                var post = {}, form = get_form();

                $.each($(form.form).serializeArray(), function () {
                    post[this.name] = this.value;
                });

                post._uid = form.rc.env.uid;
                post._mbox = form.rc.env.mailbox;
                delete post._action;
                delete post._task;

                if (post._to || post._cc || post._bcc) {
                    ref.http_post('bounce', post, ref.set_busy(true, 'sendingmessage'));
                    dialog.dialog('close');
                }
            },
            submit_func = function () {
                var form = get_form();

                if (typeof form.form !== 'object') {
                    return false;
                }

                if (!form.rc.check_compose_address_fields(post_func, form.form)) {
                    return false;
                }

                return post_func();
            };

        this.hide_menu('forwardmenu', event);

        dialog = this.simple_dialog(dialog, 'bouncemsg', submit_func, {
            button: 'bounce',
            width: 400,
            height: 300,
        });

        return true;
    };


    /*********************************************************/
    /*                 message compose methods               */
    /*********************************************************/

    this.open_compose_step = function (p) {
        var url = this.url('mail/compose', p);

        // open new compose window
        if (this.env.compose_extwin && !this.env.extwin) {
            this.open_window(url);
        } else {
            this.redirect(url);
            if (this.env.extwin) {
                window.resizeTo(Math.max(this.env.popup_width, $(window).width()), $(window).height() + 24);
            }
        }
    };

    // init message compose form: set focus and eventhandlers
    this.init_messageform = function () {
        if (!this.gui_objects.messageform) {
            return false;
        }

        var elem, pos,
            input_from = $("[name='_from']"),
            input_to = $("[name='_to']"),
            input_subject = $("[name='_subject']"),
            input_message = $("[name='_message']").get(0),
            html_mode = $("[name='_is_html']").val() == '1',
            opener_rc = this.opener();

        // close compose step in opener
        if (opener_rc && opener_rc.env.action == 'compose') {
            setTimeout(function () {
                if (opener.history.length > 1) {
                    opener.history.back();
                } else {
                    opener_rc.redirect(opener_rc.get_task_url('mail'));
                }
            }, 100);

            this.env.opened_extwin = true;
        }

        if (!html_mode) {
            // On Back button Chrome will overwrite textarea with old content
            // causing e.g. the same signature is added twice (#5809)
            if (input_message.value && input_message.defaultValue !== undefined) {
                input_message.value = input_message.defaultValue;
            }

            pos = this.env.top_posting && this.env.compose_mode ? 0 : input_message.value.length;

            // add signature according to selected identity
            // if we have HTML editor, signature is added in a callback
            if (input_from.prop('type') == 'select-one') {
                // for some reason the caret initially is not at pos=0 in Firefox 51 (#5628)
                this.set_caret_pos(input_message, 0);
                this.change_identity(input_from[0]);
            }

            // set initial cursor position
            this.set_caret_pos(input_message, pos);

            // scroll to the bottom of the textarea (#1490114)
            if (pos) {
                $(input_message).scrollTop(input_message.scrollHeight);
            }
        }

        // check for locally stored compose data
        if (this.env.save_localstorage) {
            this.compose_restore_dialog(0, html_mode);
        }

        if (input_to.val() == '') {
            elem = input_to;
        } else if (input_subject.val() == '') {
            elem = input_subject;
        } else if (input_message) {
            elem = input_message;
        }

        this.env.compose_focus_elem = this.init_messageform_inputs(elem);

        // get summary of all field values
        this.compose_field_hash(true);

        // start the auto-save timer
        this.auto_save_start();
    };

    // Init autocomplete events on compose form inputs
    this.init_messageform_inputs = function (focused) {
        var i,
            input_to = $("[name='_to']"),
            ac_fields = ['cc', 'bcc', 'replyto', 'followupto'];

        // init live search events
        this.init_address_input_events(input_to);
        for (i in ac_fields) {
            this.init_address_input_events($("[name='_" + ac_fields[i] + "']"));
        }

        if (!focused) {
            focused = input_to;
        }

        // focus first empty element (and return it)
        return $(focused).focus().get(0);
    };

    this.compose_restore_dialog = function (j, html_mode) {
        var i, key, formdata, index = this.local_storage_get_item('compose.index', []);

        var show_next = function (i) {
            if (++i < index.length) {
                ref.compose_restore_dialog(i, html_mode);
            }
        };

        for (i = j || 0; i < index.length; i++) {
            key = index[i];
            formdata = this.local_storage_get_item('compose.' + key, null, true);
            if (!formdata) {
                continue;
            }
            // restore saved copy of current compose_id
            if (formdata.changed && key == this.env.compose_id) {
                this.restore_compose_form(key, html_mode);
                break;
            }
            // skip records from 'other' drafts
            if (this.env.draft_id && formdata.draft_id && formdata.draft_id != this.env.draft_id) {
                continue;
            }
            // skip records on reply
            if (this.env.reply_msgid && formdata.reply_msgid != this.env.reply_msgid) {
                continue;
            }
            // show dialog asking to restore the message
            if (formdata.changed && formdata.session != this.env.session_id) {
                this.show_popup_dialog(
                    this.get_label('restoresavedcomposedata')
                        .replace('$date', new Date(formdata.changed).toLocaleString())
                        .replace('$subject', formdata._subject)
                        .replace(/\n/g, '<br/>'),
                    this.get_label('restoremessage'),
                    [{
                        text: this.get_label('restore'),
                        class: 'mainaction restore',
                        click: function () {
                            ref.restore_compose_form(key, html_mode);
                            ref.remove_compose_data(key); // remove old copy
                            ref.save_compose_form_local(); // save under current compose_id
                            $(this).dialog('close');
                        },
                    },
                    {
                        text: this.get_label('delete'),
                        class: 'delete',
                        click: function () {
                            ref.remove_compose_data(key);
                            $(this).dialog('close');
                            show_next(i);
                        },
                    },
                    {
                        text: this.get_label('ignore'),
                        class: 'cancel',
                        click: function () {
                            $(this).dialog('close');
                            show_next(i);
                        },
                    }]
                );
                break;
            }
        }
    };

    this.init_address_input_events = function (obj, props) {
        // configure parallel autocompletion
        if (!props && this.env.autocomplete_threads > 0) {
            props = {
                threads: this.env.autocomplete_threads,
                sources: this.env.autocomplete_sources,
            };
        }

        obj.keydown(function (e) {
            return ref.ksearch_keydown(e, this, props);
        })
            .attr({
                autocomplete: 'off', 'aria-autocomplete': 'list', 'aria-expanded': 'false', role: 'combobox',
            });

        var callback = function (e) {
            if (ref.ksearch_pane && e.target === ref.ksearch_pane.get(0)) {
                return;
            }
            ref.ksearch_hide();
        };

        // hide the popup on any click
        $(document).on('click', callback);
        // and on scroll (that cannot be jQuery.on())
        document.addEventListener('scroll', callback, true);
    };

    this.submit_messageform = function (draft, saveonly) {
        var form = this.gui_objects.messageform;

        if (!form) {
            return;
        }

        // the message has been sent but not saved, ask the user what to do
        if (!saveonly && this.env.is_sent) {
            return this.simple_dialog(this.get_label('messageissent'), '', // TODO: dialog title
                function () {
                    ref.submit_messageform(false, true);
                    return true;
                }
            );
        }

        // delegate sending to Mailvelope routine
        if (this.mailvelope_editor) {
            return this.mailvelope_submit_messageform(draft, saveonly);
        }

        // all checks passed, send message
        var msgid = this.set_busy(true, draft || saveonly ? 'savingmessage' : 'sendingmessage'),
            lang = this.spellcheck_lang(),
            files = [];

        // send files list
        $('li', this.gui_objects.attachmentlist).each(function () {
            files.push(this.id.replace(/^rcmfile/, ''));
        });
        $('[name="_attachments"]', form).val(files.join());

        form.target = this.get_save_target(msgid);
        form._draft.value = draft ? '1' : '';
        form.action = this.add_url(form.action, '_unlock', msgid);
        form.action = this.add_url(form.action, '_framed', 1);

        if (lang) {
            form.action = this.add_url(form.action, '_lang', lang);
        }
        if (saveonly) {
            form.action = this.add_url(form.action, '_saveonly', 1);
        }

        // register timer to notify about connection timeout
        this.submit_timer = setTimeout(function () {
            ref.set_busy(false, null, msgid);
            ref.display_message('requesttimedout', 'error');
        }, this.env.request_timeout * 1000);

        form.submit();
    };

    this.compose_recipient_select = function (list) {
        var id, n, recipients = 0, selection = list.get_selection();
        for (n = 0; n < selection.length; n++) {
            id = selection[n];
            if (this.env.contactdata[id]) {
                recipients++;
            }
        }
        this.enable_command('add-recipient', recipients);
    };

    this.compose_add_recipient = function (field) {
        // find last focused field name
        if (!field) {
            field = $(this.env.focused_field).filter(':visible');
            field = field.length ? field.attr('id').replace('_', '') : 'to';
        }

        var recipients = [], input = $('#_' + field), selection = this.contact_list.get_selection();

        if (this.contact_list && selection.length) {
            var data, name, n, id;
            for (n = 0; n < selection.length; n++) {
                if ((id = selection[n]) && (data = this.env.contactdata[id])) {
                    name = data.name || data;

                    // group is added, expand it
                    if (id.charAt(0) == 'E' && input.length) {
                        // We wrap the group name with invisible markers to prevent from problems with group expanding (#7569)
                        name = '\u200B' + name + '\u200B';
                        var gid = id.substr(1);
                        this.group2expand[gid] = { name: name, input: input.get(0) };
                        this.http_request('group-expand', { _source: data.source || this.env.source, _gid: gid }, false);
                    }

                    recipients.push(name);
                }
            }
        }

        if (recipients.length && input.length) {
            var oldval = input.val();
            if (oldval && !/[,;]\s*$/.test(oldval)) {
                oldval += ', ';
            }
            input.val(oldval + recipients.join(', ') + ', ').change();
            this.triggerEvent('add-recipient', { field: field, recipients: recipients });
        }

        return recipients.length;
    };

    // Checks the input fields before sending a message
    this.check_compose_input = function (cmd) {
        var key,
            input_subject = $("[name='_subject']");

        // check if all files has been uploaded
        for (key in this.env.attachments) {
            if (typeof this.env.attachments[key] === 'object' && !this.env.attachments[key].complete) {
                this.alert_dialog(this.get_label('notuploadedwarning'));
                return false;
            }
        }

        // display localized warning for missing subject
        if (!this.env.nosubject_warned && input_subject.val() == '') {
            var dialog,
                prompt_value = $('<input>').attr({ type: 'text', size: 40, 'data-submit': 'true' }),
                myprompt = $('<div class="prompt">')
                    .append($('<p class="message">').text(this.get_label('nosubjectwarning')))
                    .append(prompt_value),
                save_func = function () {
                    input_subject.val(prompt_value.val());
                    dialog.dialog('close');
                    if (ref.check_compose_input(cmd)) {
                        ref.command(cmd, { nocheck: true });
                    } // repeat command which triggered this
                };

            dialog = this.show_popup_dialog(
                myprompt,
                this.get_label('nosubjecttitle'),
                [{
                    text: this.get_label('sendmessage'),
                    class: 'mainaction send',
                    click: function () {
                        save_func();
                    },
                }, {
                    text: this.get_label('cancel'),
                    class: 'cancel',
                    click: function () {
                        input_subject.focus();
                        dialog.dialog('close');
                    },
                }],
                { dialogClass: 'warning' }
            );

            this.env.nosubject_warned = true;
            return false;
        }

        // check for empty body (only possible if not mailvelope encrypted)
        if (!this.mailvelope_editor && !this.editor.get_content() && !confirm(this.get_label('nobodywarning'))) {
            this.editor.focus();
            return false;
        }

        if (!this.check_compose_address_fields(cmd)) {
            return false;
        }

        // move body from html editor to textarea (just to be sure, #1485860)
        this.editor.save();

        return true;
    };

    this.check_compose_address_fields = function (cmd, form) {
        if (!form) {
            form = window.document;
        }

        // check input fields
        var key, recipients, dialog,
            limit = this.env.max_disclosed_recipients,
            input_to = $("[name='_to']", form),
            input_cc = $("[name='_cc']", form),
            input_bcc = $("[name='_bcc']", form),
            input_from = $("[name='_from']", form),
            get_recipients = function (fields) {
                fields = $.map(fields, function (v) {
                    v = v.val().trim();
                    return v.length ? v : null;
                });
                return fields.join(',').replace(/^[\s,;]+/, '').replace(/[\s,;]+$/, '');
            };

        // check sender (if have no identities)
        if (input_from.prop('type') == 'text' && !rcube_check_email(input_from.val(), true)) {
            this.alert_dialog(this.get_label('nosenderwarning'), function () {
                input_from.focus();
            });
            return false;
        }

        // check for empty recipient
        if (!rcube_check_email(get_recipients([input_to, input_cc, input_bcc]), true)) {
            this.alert_dialog(this.get_label('norecipientwarning'), function () {
                input_to.focus();
            });
            return false;
        }

        // check disclosed recipients limit
        if (limit && !this.env.disclosed_recipients_warned
            && rcube_check_email(recipients = get_recipients([input_to, input_cc]), true, true) > limit
        ) {
            var save_func = function (move_to_bcc) {
                if (move_to_bcc) {
                    var bcc = input_bcc.val();
                    input_bcc.val((bcc ? (bcc + ', ') : '') + recipients).change();
                    input_to.val('').change();
                    input_cc.val('').change();
                }

                dialog.dialog('close');

                if (typeof cmd === 'function') {
                    cmd();
                } else if (cmd) {
                    ref.command(cmd, { nocheck: true });
                } // repeat command which triggered this
            };

            dialog = this.show_popup_dialog(
                this.get_label('disclosedrecipwarning'),
                this.get_label('disclosedreciptitle'),
                [
                    {
                        text: this.get_label('sendmessage'),
                        click: function () {
                            save_func(false);
                        },
                        class: 'mainaction',
                    },
                    {
                        text: this.get_label('bccinstead'),
                        click: function () {
                            save_func(true);
                        },
                    },
                    {
                        text: this.get_label('cancel'),
                        click: function () {
                            dialog.dialog('close');
                        },
                        class: 'cancel',
                    },
                ],
                { dialogClass: 'warning' }
            );

            this.env.disclosed_recipients_warned = true;
            return false;
        }

        return true;
    };

    this.toggle_editor = function (props, obj, e) {
        // @todo: this should work also with many editors on page
        var mode, result = this.editor.toggle(props.html, props.noconvert || false),
            control = $('#' + this.editor.id).data('control') || $(e ? e.target : []);

        if (result) {
            mode = props.html ? 'html' : 'plain';
        } else {
            mode = props.html ? 'plain' : 'html';
        }

        // update internal format flag
        $("[name='_is_html']").val(mode == 'html' ? 1 : 0);

        if (control.is('[type=checkbox]')) {
            control.prop('checked', mode == 'html');
        } else {
            control.val(mode);
        }

        return result;
    };

    // Inserts a predefined response to the compose editor
    this.insert_response = function (response) {
        if (typeof response === 'object') {
            var replace = {};
            replace[response.is_html ? 'html' : 'text'] = response.data;

            this.editor.replace(replace);
            this.display_message('responseinserted', 'confirmation');
        } else {
            var lock = this.display_message('', 'loading');
            this.http_get('settings/response-get', { _id: response, _is_html: this.editor.is_html() ? 1 : 0 }, lock);
        }
    };

    // Updates spellchecker buttons on state change
    this.spellcheck_state = function () {
        var active = this.editor.spellcheck_state();

        $.each(this.buttons.spellcheck || [], function (i, v) {
            $('#' + v.id)[active ? 'addClass' : 'removeClass']('selected');
        });

        return active;
    };

    // Get selected language
    this.spellcheck_lang = function () {
        return this.editor.get_language();
    };

    this.spellcheck_lang_set = function (lang) {
        this.editor.set_language(lang);
    };

    // Resume spellchecking, highlight provided misspellings without new ajax request
    this.spellcheck_resume = function (data) {
        this.editor.spellcheck_resume(data);
    };

    this.set_draft_id = function (id) {
        if (id && id != this.env.draft_id) {
            var filter = { task: 'mail', action: '' },
                rc = this.opener(false, filter) || this.opener(true, filter);

            // refresh the drafts folder in the opener window
            if (rc && rc.env.mailbox == this.env.drafts_mailbox) {
                rc.command('checkmail');
            }

            this.env.draft_id = id;
            $("[name='_draft_saveid']").val(id);
        }

        // always remove local copy upon saving as draft
        this.remove_compose_data(this.env.compose_id);
        this.compose_skip_unsavedcheck = false;
    };

    // Create (attach) 'savetarget' iframe before use
    this.get_save_target = function (unlock) {
        // Removing the frame on load/error to workaround issues with window history
        this.dummy_iframe('savetarget', 'javascript:false;')
            .on('load error', function () {
                // catch invalid/error response from server and unlock the UI (#7494, #7488, #7522)
                if (unlock && $(this).contents().find('meta[name="generator"][content="Roundcube"]').length == 0) {
                    ref.iframe_loaded(unlock);
                    ref.display_message('connerror', 'error');
                }

                $(this).remove();
            });

        return 'savetarget';
    };

    this.auto_save_start = function () {
        if (this.env.draft_autosave) {
            this.save_timer = setTimeout(function () {
                ref.command('savedraft');
            }, this.env.draft_autosave * 1000);
        }

        // save compose form content to local storage every 5 seconds
        if (!this.local_save_timer && window.localStorage && this.env.save_localstorage) {
            // track typing activity and only save on changes
            this.compose_type_activity = this.compose_type_activity_last = 0;
            $(document).keypress(function (e) {
                ref.compose_type_activity++;
            });

            this.local_save_timer = setInterval(function () {
                if (ref.compose_type_activity > ref.compose_type_activity_last) {
                    ref.save_compose_form_local();
                    ref.compose_type_activity_last = ref.compose_type_activity;
                }
            }, 5000);

            $(window).on('unload', function () {
                // remove copy from local storage if compose screen is left after warning
                if (!ref.env.server_error) {
                    ref.remove_compose_data(ref.env.compose_id);
                }
            });
        }

        // check for unsaved changes before leaving the compose page
        if (!window.onbeforeunload) {
            window.onbeforeunload = function () {
                if (!ref.compose_skip_unsavedcheck && ref.cmp_hash != ref.compose_field_hash()) {
                    return ref.get_label('notsentwarning');
                }
            };
        }

        // Unlock interface now that saving is complete
        this.busy = false;
    };

    this.compose_field_hash = function (save) {
        // check input fields
        var i, id, val, str = '', hash_fields = ['to', 'cc', 'bcc', 'subject'];

        for (i = 0; i < hash_fields.length; i++) {
            if (val = $('[name="_' + hash_fields[i] + '"]').val()) {
                str += val + ':';
            }
        }

        str += this.editor.get_content({ refresh: false });

        for (id in this.env.attachments) {
            str += id;
        }

        // we can't detect changes in the Mailvelope editor so assume it changed
        if (this.mailvelope_editor) {
            str += ';' + new Date().getTime();
        }

        if (save) {
            this.cmp_hash = str;
        }

        return str;
    };

    // store the contents of the compose form to localstorage
    this.save_compose_form_local = function () {
        // feature is disabled
        if (!this.env.save_localstorage) {
            return;
        }

        var ed, empty = true,
            formdata = { session: this.env.session_id, changed: new Date().getTime() };

        // get fresh content from editor
        this.editor.save();

        if (this.env.draft_id) {
            formdata.draft_id = this.env.draft_id;
        }

        if (this.env.reply_msgid) {
            formdata.reply_msgid = this.env.reply_msgid;
        }

        $('input, select, textarea', this.gui_objects.messageform).each(function (i, elem) {
            switch (elem.tagName.toLowerCase()) {
                case 'input':
                    if (elem.type == 'button' || elem.type == 'submit' || (elem.type == 'hidden' && elem.name != '_is_html')) {
                        break;
                    }
                    formdata[elem.name] = elem.type != 'checkbox' || elem.checked ? $(elem).val() : '';

                    if (formdata[elem.name] != '' && elem.type != 'hidden') {
                        empty = false;
                    }

                    break;
                case 'select':
                    formdata[elem.name] = $('option:checked', elem).val();
                    break;
                default:
                    formdata[elem.name] = $(elem).val();
                    if (formdata[elem.name] != '') {
                        empty = false;
                    }
            }
        });

        if (!empty) {
            var index = this.local_storage_get_item('compose.index', []),
                key = this.env.compose_id;

            if ($.inArray(key, index) < 0) {
                index.push(key);
            }

            this.local_storage_set_item('compose.' + key, formdata, true);
            this.local_storage_set_item('compose.index', index);
        }
    };

    // write stored compose data back to form
    this.restore_compose_form = function (key, html_mode) {
        var ed, formdata = this.local_storage_get_item('compose.' + key, true);

        if (formdata && typeof formdata === 'object') {
            $.each(formdata, function (k, value) {
                if (k[0] == '_') {
                    var elem = $('[name=' + k + ']');
                    if (elem[0] && elem[0].type == 'checkbox') {
                        elem.prop('checked', value != '');
                    } else {
                        elem.val(value).change();
                    }
                }
            });

            // initialize HTML editor
            if ((formdata._is_html == '1' && !html_mode) || (formdata._is_html != '1' && html_mode)) {
                this.command('toggle-editor', { id: this.env.composebody, html: !html_mode, noconvert: true });
            }
        }
    };

    // Remove stored compose data from localStorage
    this.remove_compose_data = function (key) {
        var index = this.local_storage_get_item('compose.index', []);

        if ($.inArray(key, index) >= 0) {
            this.local_storage_remove_item('compose.' + key);
            this.local_storage_set_item('compose.index', $.grep(index, function (val, i) {
                return val != key;
            }));
        }
    };

    // Clear all stored compose data of this user
    this.clear_compose_data = function () {
        var i, index = this.local_storage_get_item('compose.index', []);

        for (i = 0; i < index.length; i++) {
            this.local_storage_remove_item('compose.' + index[i]);
        }

        this.local_storage_remove_item('compose.index');
    };

    this.change_identity = function (obj, show) {
        if (!obj || !obj.options) {
            return false;
        }

        var id = $(obj).val(),
            got_sig = this.env.signatures && this.env.signatures[id],
            sig = this.env.identity,
            show_sig = show ? show : this.env.show_sig;

        // enable manual signature insert
        if (got_sig) {
            this.enable_command('insert-sig', true);
            this.env.compose_commands.push('insert-sig');
            got_sig = true;
        } else {
            this.enable_command('insert-sig', false);
        }

        // first function execution
        if (!this.env.identities_initialized) {
            this.env.identities_initialized = true;
            if (this.env.show_sig_later) {
                this.env.show_sig = true;
            }
            if (this.env.opened_extwin) {
                return;
            }
        }

        // update reply-to/bcc fields with addresses defined in identities
        $.each(['replyto', 'bcc'], function () {
            var rx, key = this,
                old_val = sig && ref.env.identities[sig] ? ref.env.identities[sig][key] : '',
                new_val = id && ref.env.identities[id] ? ref.env.identities[id][key] : '',
                input = $('[name="_' + key + '"]'), input_val = input.val();

            // remove old address(es)
            if (old_val && input_val) {
                rx = new RegExp('\\s*' + RegExp.escape(old_val) + '\\s*');
                input_val = input_val.replace(rx, '');
            }

            // cleanup
            input_val = String(input_val).replace(/[,;]\s*[,;]/g, ',').replace(/^[\s,;]+/, '');

            // add new address(es)
            if (new_val && input_val.indexOf(new_val) == -1 && input_val.indexOf(new_val.replace(/"/g, '')) == -1) {
                if (input_val) {
                    input_val = input_val.replace(/[,;\s]+$/, '') + ', ';
                }

                input_val += new_val + ', ';
            }

            if (old_val || new_val) {
                input.val(input_val).change();
            }
        });

        if (this.editor) {
            this.editor.change_signature(id, show_sig);
        }

        if (show && got_sig) {
            this.display_message('siginserted', 'confirmation');
        }

        this.env.identity = id;
        this.triggerEvent('change_identity');

        return true;
    };

    // Open file selection dialog for defined upload form
    // Works only on click and only with smart-upload forms
    this.upload_input = function (name) {
        $('#' + name + ' input[type="file"]').click();
    };

    // upload (attachment) file
    this.upload_file = function (form, action, lock) {
        if (form) {
            var fname, files = [];
            $('input', form).each(function () {
                if (this.files) {
                    fname = this.name;
                    for (var i = 0; i < this.files.length; i++) {
                        files.push(this.files[i]);
                    }
                }
            });

            return this.file_upload(files, { _id: this.env.compose_id || '' }, {
                name: fname,
                action: action,
                lock: lock,
            });
        }
    };

    // Add file name to attachment list, called from upload page
    this.add2attachment_list = function (name, att, upload_id) {
        if (upload_id) {
            this.triggerEvent('fileuploaded', { name: name, attachment: att, id: upload_id });
        }

        if (upload_id && this.env.attachments[upload_id]) {
            delete this.env.attachments[upload_id];
        }

        this.env.attachments[name] = att;

        if (!this.gui_objects.attachmentlist) {
            return false;
        }

        var label, indicator, li = $('<li>');

        if (!att.complete && att.html.indexOf('<') < 0) {
            att.html = '<span class="uploading">' + att.html + '</span>';
        }

        if (!att.complete && this.env.loadingicon) {
            att.html = '<img src="' + this.env.loadingicon + '" class="uploading" />' + att.html;
        }

        if (!att.complete) {
            label = this.get_label('cancel');
            att.html = '<a title="' + label + '" onclick="return rcmail.cancel_attachment_upload(\'' + name + '\');" href="#cancelupload" class="cancelupload">'
                + (this.env.cancelicon ? '<img src="' + this.env.cancelicon + '" />' : '<span class="inner">' + label + '</span>') + '</a>' + att.html;
        }

        li.attr('id', name).addClass(att.classname).html(att.html)
            .find('.attachment-name').on('mouseover', function () {
                rcube_webmail.long_subject_title_ex(this);
            });

        // replace indicator's li
        if (upload_id && (indicator = document.getElementById(upload_id))) {
            li.replaceAll(indicator);
        } else {
            // add new li
            li.appendTo(this.gui_objects.attachmentlist);
        }

        // set tabindex attribute
        var tabindex = $(this.gui_objects.attachmentlist).attr('data-tabindex') || '0';
        li.find('a').attr('tabindex', tabindex);

        this.triggerEvent('fileappended', {
            name: name, attachment: att, id: upload_id, item: li,
        });

        return true;
    };

    this.remove_from_attachment_list = function (name) {
        delete this.env.attachments[name];
        $('#' + name).remove();
    };

    this.remove_attachment = function (name) {
        if (name && this.env.attachments[name]) {
            this.http_post('remove-attachment', { _id: this.env.compose_id, _file: name });
        }

        return false;
    };

    this.cancel_attachment_upload = function (name) {
        if (!name || !this.uploads[name]) {
            return false;
        }

        this.remove_from_attachment_list(name);
        this.uploads[name].abort();
        return false;
    };

    // rename uploaded attachment (in compose)
    this.rename_attachment = function (id) {
        var attachment = this.env.attachments[id];

        if (!attachment) {
            return;
        }

        var input = $('<input>').attr({ type: 'text', size: 50 }).val(attachment.name),
            content = $('<label>').text(this.get_label('namex')).append(input);

        this.simple_dialog(content, 'attachmentrename', function () {
            var name;
            if ((name = input.val()) && name != attachment.name) {
                ref.http_post('rename-attachment', { _id: ref.env.compose_id, _file: id, _name: name },
                    ref.set_busy(true, 'loading'));
                return true;
            }
        }
        );
    };

    // update attachments list with the new name
    this.rename_attachment_handler = function (id, name) {
        var attachment = this.env.attachments[id];

        if (!attachment || !name) {
            return;
        }

        attachment.name = name;

        $('#' + id + ' .attachment-name').text(name).attr('title', '');
    };

    // send remote request to add a new contact
    this.add_contact = function (value, reload, source) {
        if (value) {
            this.http_post('addcontact', { _address: value, _reload: reload, _source: source });
        }
    };

    // send remote request to search mail or contacts
    this.qsearch = function (value) {
        // Note: Some plugins would like to do search without value,
        // so we keep value != '' check to allow that use-case. Which means
        // e.g. that qsearch() with no argument will execute the search.
        if (value != '' || $(this.gui_objects.qsearchbox).val() || $(this.gui_objects.search_interval).val()) {
            var r, lock = this.set_busy(true, 'searching'),
                url = this.search_params(value),
                action = this.env.action == 'compose' && this.contact_list ? 'search-contacts' : 'search';

            if (this.message_list) {
                this.clear_message_list();
            } else if (this.contact_list) {
                this.list_contacts_clear();
            }

            if (this.env.source) {
                url._source = this.env.source;
            }
            if (this.env.group) {
                url._gid = this.env.group;
            }

            // reset vars
            this.env.current_page = 1;

            r = this.http_request(action, url, lock);

            this.env.qsearch = { lock: lock, request: r };
            this.enable_command('set-listmode', this.env.threads && (this.env.search_scope || 'base') == 'base');

            return true;
        }

        return false;
    };

    this.continue_search = function (request_id) {
        var lock = this.set_busy(true, 'stillsearching');

        setTimeout(function () {
            var url = ref.search_params();
            url._continue = request_id;
            ref.env.qsearch = { lock: lock, request: ref.http_request('search', url, lock) };
        }, 100);
    };

    // build URL params for search
    this.search_params = function (search, filter) {
        var n, url = {}, mods_arr = [],
            mods = this.env.search_mods,
            scope = this.env.search_scope || 'base',
            mbox = this.env.mailbox;

        if (!filter && this.gui_objects.search_filter) {
            filter = this.gui_objects.search_filter.value;
        }

        if (!search && this.gui_objects.qsearchbox) {
            search = this.gui_objects.qsearchbox.value;
        }

        if (this.gui_objects.search_interval) {
            url._interval = $(this.gui_objects.search_interval).val();
        }

        if (search) {
            url._q = search;

            if (mods && this.message_list) {
                mods = mods[mbox] || mods['*'];
            }

            if (mods) {
                for (n in mods) {
                    mods_arr.push(n);
                }
                url._headers = mods_arr.join(',');
            }
        }

        url._layout = this.env.layout;
        url._filter = filter;
        url._scope = scope;
        url._mbox = mbox;

        return url;
    };

    // reset search filter
    this.reset_search_filter = function () {
        this.filter_disabled = true;
        if (this.gui_objects.search_filter) {
            $(this.gui_objects.search_filter).val('ALL').change();
        }
        this.filter_disabled = false;
    };

    // reset quick-search form
    this.reset_qsearch = function (all) {
        if (this.gui_objects.qsearchbox) {
            this.gui_objects.qsearchbox.value = '';
        }

        if (this.gui_objects.search_interval) {
            $(this.gui_objects.search_interval).val('');
        }

        if (this.env.qsearch) {
            this.abort_request(this.env.qsearch);
        }

        if (all) {
            this.env.search_scope = this.env.search_scope || 'base';
            this.reset_search_filter();
        }

        this.env.qsearch = null;
        this.env.search_request = null;
        this.env.search_id = null;
        this.select_all_mode = false;

        this.enable_command('set-listmode', this.env.threads);
    };

    this.set_searchscope = function (scope) {
        this.env.search_scope = scope;
    };

    this.set_searchinterval = function (interval) {
        this.env.search_interval = interval;
    };

    this.set_searchmods = function (mods) {
        var mbox = this.env.mailbox,
            scope = this.env.search_scope || 'base';

        if (!this.env.search_mods) {
            this.env.search_mods = {};
        }

        if (mbox) {
            this.env.search_mods[mbox] = mods;
        }
    };

    this.is_multifolder_listing = function () {
        return this.env.multifolder_listing !== undefined ? this.env.multifolder_listing
            : (this.env.search_request && (this.env.search_scope || 'base') != 'base');
    };

    // action executed after mail is sent
    this.sent_successfully = function (type, msg, folders, save_error) {
        this.display_message(msg, type);
        this.compose_skip_unsavedcheck = true;

        if (this.env.extwin) {
            if (!save_error) {
                this.lock_form(this.gui_objects.messageform);
            }

            var filter = { task: 'mail', action: '' },
                rc = this.opener(false, filter) || this.opener(true, filter);

            if (rc) {
                rc.display_message(msg, type);
                // refresh the folder where sent message was saved or replied message comes from
                if (folders && $.inArray(rc.env.mailbox, folders) >= 0) {
                    rc.command('checkmail');
                }
            }

            if (!save_error) {
                setTimeout(function () {
                    window.close();
                }, 1000);
            }
        } else if (!save_error) {
            // before redirect we need to wait some time for Chrome (#1486177)
            setTimeout(function () {
                ref.list_mailbox();
            }, 500);
        }

        if (save_error) {
            this.env.is_sent = true;
        }
    };

    this.image_rotate = function () {
        var curr = this.image_style ? (this.image_style.rotate || 0) : 0;

        this.image_style.rotate = curr > 180 ? 0 : curr + 90;
        this.apply_image_style();
    };

    this.image_scale = function (prop) {
        var curr = this.image_style ? (this.image_style.scale || 1) : 1;

        this.image_style.scale = Math.max(0.1, curr + 0.1 * (prop == '-' ? -1 : 1));
        this.apply_image_style();
    };

    this.apply_image_style = function () {
        var style = [],
            img = $(this.gui_objects.messagepartframe).contents().find('img');

        $.each({ scale: '', rotate: 'deg' }, function (i, v) {
            var val = ref.image_style[i];
            if (val) {
                style.push(i + '(' + val + v + ')');
            }
        });

        img.css('transform', style.join(' '));
    };

    // Update import dialog state
    this.import_state_set = function (state) {
        if (this.import_dialog) {
            this.import_state = state;

            var button = $(this.import_dialog).parent().find('.ui-dialog-buttonset > button').first();

            if (state != 'error') {
                // replace Import/Cancel buttons with Close button
                button.hide();
                button.next().text(this.gettext('close')).focus();
            } else {
                // activate the Import button
                button.prop('disabled', false);
            }
        }
    };


    /*********************************************************/
    /*              keyboard live-search methods             */
    /*********************************************************/

    // handler for keyboard events on address-fields
    this.ksearch_keydown = function (e, obj, props) {
        if (this.ksearch_timer) {
            clearTimeout(this.ksearch_timer);
        }

        var key = rcube_event.get_keycode(e);

        switch (key) {
            case 38: // arrow up
            case 40: // arrow down
                if (!this.ksearch_visible()) {
                    return;
                }

                var dir = key == 38 ? 1 : 0,
                    highlight = this.ksearch_pane.find('li.selected')[0];

                if (!highlight) {
                    highlight = this.ksearch_pane.__ul.firstChild;
                }

                if (highlight) {
                    this.ksearch_select(dir ? highlight.previousSibling : highlight.nextSibling);
                }

                return rcube_event.cancel(e);
            case 9: // tab
                if (rcube_event.get_modifier(e) == SHIFT_KEY || !this.ksearch_visible()) {
                    this.ksearch_hide();
                    return;
                }

            case 13: // enter
                if (!this.ksearch_visible()) {
                    return false;
                }

                // insert selected address and hide ksearch pane
                this.insert_recipient(this.ksearch_selected);
                this.ksearch_hide();

                // Don't cancel on Tab, we want to jump to the next field (#5659)
                return key == 9 ? null : rcube_event.cancel(e);
            case 27: // escape
                this.ksearch_hide();
                return;
            case 37: // left
            case 39: // right
                return;
        }

        // start timer
        this.ksearch_timer = setTimeout(function () {
            ref.ksearch_get_results(props);
        }, 200);
        this.ksearch_input = obj;

        return true;
    };

    this.ksearch_visible = function () {
        return this.ksearch_selected !== null && this.ksearch_selected !== undefined && this.ksearch_value;
    };

    this.ksearch_select = function (node) {
        if (this.ksearch_pane && node) {
            this.ksearch_pane.find('li.selected').removeClass('selected').removeAttr('aria-selected');
        }

        if (node) {
            $(node).addClass('selected').attr('aria-selected', 'true');
            this.ksearch_selected = node._rcm_id;
            $(this.ksearch_input).attr('aria-activedescendant', 'rcmkSearchItem' + this.ksearch_selected);
        }
    };

    this.insert_recipient = function (id) {
        if (id === null || !this.env.contacts[id] || !this.ksearch_input) {
            return;
        }

        var trigger = false, insert = '', delim = ', ',
            contact = this.env.contacts[id];

        this.ksearch_destroy();

        // insert all members of a group
        if (typeof contact === 'object' && contact.type == 'group' && !contact.email && contact.id) {
            // We wrap the group name with invisible markers to prevent from problems with group expanding (#7569)
            var name = '\u200B' + contact.name + '\u200B';
            insert = name + delim;
            this.group2expand[contact.id] = { name: name, input: this.ksearch_input };
            this.http_request('mail/group-expand', { _source: contact.source, _gid: contact.id }, false);
        } else if (typeof contact === 'object' && contact.name) {
            insert = contact.name + delim;
            trigger = true;
        } else if (typeof contact === 'string') {
            insert = contact + delim;
            trigger = true;
        }

        this.ksearch_input_replace(this.ksearch_value, insert, null, trigger);

        if (trigger) {
            this.triggerEvent('autocomplete_insert', {
                field: this.ksearch_input,
                insert: insert,
                data: contact,
                search: this.ksearch_value_last,
                result_type: 'person',
            });

            this.ksearch_value_last = null;
            this.compose_type_activity++;
        }
    };

    this.replace_group_recipients = function (id, recipients) {
        var data = this.group2expand[id];

        if (data) {
            this.ksearch_input_replace(data.name, recipients, data.input);

            this.triggerEvent('autocomplete_insert', {
                field: data.input,
                insert: recipients,
                data: data,
                search: this.ksearch_value_last,
                result_type: 'group',
            });

            this.ksearch_value_last = null;
            this.group2expand[id] = null;
            this.compose_type_activity++;
        }
    };

    // address search processor
    this.ksearch_get_results = function (props) {
        if (this.ksearch_pane && this.ksearch_pane.is(':visible')) {
            this.ksearch_pane.hide();
        }

        // get string from cursor position back to the last comma or semicolon
        var q = this.ksearch_input_get(),
            min = this.env.autocomplete_min_length,
            data = this.ksearch_data;

        // trim query string
        q = q.trim();

        // Don't (re-)search if the last results are still active
        if (q == this.ksearch_value) {
            return;
        }

        this.ksearch_destroy();

        if (q.length && q.length < min) {
            if (!this.ksearch_info) {
                this.ksearch_info = this.display_message(this.get_label('autocompletechars').replace('$min', min));
            }
            return;
        }

        var old_value = this.ksearch_value;
        this.ksearch_value = q;

        // Group expansion clears ksearch_value before calling autocomplete_insert trigger,
        // therefore store it in separate variable for later consumption.
        this.ksearch_value_last = q;

        // ...string is empty
        if (!q.length) {
            return;
        }

        // ...new search value contains old one and previous search was not finished or its result was empty
        if (old_value && old_value.length && q.startsWith(old_value) && (!data || data.num <= 0) && this.env.contacts && !this.env.contacts.length) {
            return;
        }

        var sources = props && props.sources ? props.sources : [''];
        var reqid = this.multi_thread_http_request({
            items: sources,
            threads: props && props.threads ? props.threads : 1,
            action: props && props.action ? props.action : 'mail/autocomplete',
            postdata: { _search: q, _source: '%s' },
            lock: this.display_message('searching', 'loading'),
        });

        this.ksearch_data = { id: reqid, sources: sources.slice(), num: sources.length };
    };

    this.ksearch_query_results = function (results, search, reqid) {
        // trigger multi-thread http response callback
        this.multi_thread_http_response(results, reqid);

        // search stopped in meantime?
        if (!this.ksearch_value) {
            return;
        }

        // ignore this outdated search response
        if (this.ksearch_input && search != this.ksearch_value) {
            return;
        }

        // display search results
        var i, id, len, ul, text, type, init,
            is_framed = this.is_framed(),
            value = this.ksearch_value,
            maxlen = this.env.autocomplete_max ? this.env.autocomplete_max : 15;

        // create results pane if not present
        if (!this.ksearch_pane) {
            ul = $('<ul>');
            this.ksearch_pane = $('<div>')
                .attr({ id: 'rcmKSearchpane', role: 'listbox', class: 'select-menu inline' })
                .css({ position: 'absolute', 'z-index': 30000 })
                .append(ul)
                .appendTo(is_framed ? parent.document.body : document.body);

            this.ksearch_pane.__ul = ul[0];
            this.triggerEvent('autocomplete_create', { obj: this.ksearch_pane });
        }

        ul = this.ksearch_pane.__ul;

        // remove all search results or add to existing list if parallel search
        if (reqid && this.ksearch_pane.data('reqid') == reqid) {
            maxlen -= ul.childNodes.length;
        } else {
            this.ksearch_pane.data('reqid', reqid);
            init = 1;
            // reset content
            ul.innerHTML = '';
            this.env.contacts = [];

            // Calculate the results pane position and size
            // Elastic: On small screen we use the width/position of the whole .ac-input element (input's parent)
            var is_composite_input = $('html').is('.layout-small,.layout-phone') && $(this.ksearch_input).parents('.ac-input').length == 1,
                input = is_composite_input ? $(this.ksearch_input).parents('.ac-input')[0] : $(this.ksearch_input)[0],
                pos = $(input).offset();

            // ... consider scroll position
            pos.left -= $(document.documentElement).scrollLeft();
            pos.top -= $(document.documentElement).scrollTop();

            // ... consider iframe position
            if (is_framed) {
                try {
                    parent.$('iframe').each(function () {
                        if (this.contentWindow == window) {
                            var offset = $(this).offset();
                            pos.left += offset.left;
                            pos.top += offset.top;
                        }
                    });
                } catch (e) {}
            }

            var w = $(is_framed ? parent : window).width(),
                input_width = $(input).outerWidth(),
                left = w - pos.left > 200 ? pos.left : w - 200,
                top = (pos.top + input.offsetHeight + 1),
                width = Math.min(400, w - left);

            this.ksearch_pane.css({
                left: (is_composite_input ? pos.left : left) + 'px',
                top: top + 'px',
                maxWidth: (is_composite_input ? input_width : width) + 'px',
                minWidth: '200px',
                width: is_composite_input ? (input_width + 'px') : 'auto',
                display: 'none',
            });
        }

        // add each result line to list
        if (results && (len = results.length)) {
            for (i = 0; i < len && maxlen > 0; i++) {
                text = typeof results[i] === 'object' ? (results[i].display || results[i].name) : results[i];
                type = typeof results[i] === 'object' ? results[i].type : '';
                id = i + this.env.contacts.length;
                $('<li>').attr({ id: 'rcmkSearchItem' + id, role: 'option' })
                    .html('<i class="icon"></i>' + this.quote_html(text.replace(new RegExp('(' + RegExp.escape(value) + ')', 'ig'), '##$1%%')).replace(/##([^%]+)%%/g, '<b>$1</b>'))
                    .addClass(type || '')
                    .appendTo(ul)
                    .mouseover(function () {
                        ref.ksearch_select(this);
                    })
                    .mouseup(function () {
                        ref.ksearch_click(this);
                    })
                    .get(0)._rcm_id = id;
                maxlen -= 1;
            }
        }

        if (ul.childNodes.length) {
            // set the right aria-* attributes to the input field
            $(this.ksearch_input)
                .attr({ 'aria-haspopup': 'true', 'aria-expanded': 'true', 'aria-owns': 'rcmKSearchpane' });

            this.ksearch_pane.show();

            // select the first
            if (!this.env.contacts.length) {
                this.ksearch_select($('li', ul)[0]);
            }
        }

        if (len) {
            this.env.contacts = this.env.contacts.concat(results);
        }

        if (this.ksearch_data.id == reqid) {
            this.ksearch_data.num--;
        }
    };

    // Getter for input value
    // returns a string from the last comma to current cursor position
    this.ksearch_input_get = function () {
        if (!this.ksearch_input) {
            return '';
        }

        var cp = this.get_caret_pos(this.ksearch_input);

        return this.ksearch_input.value.substr(0, cp).split(/[,;]/).pop();
    };

    // Setter for input value
    // replaces 'from' string with 'to' and sets cursor position at the end
    this.ksearch_input_replace = function (from, to, input, trigger) {
        if (!this.ksearch_input && !input) {
            return;
        }

        if (!input) {
            input = this.ksearch_input;
        }

        var cpos = this.get_caret_pos(input),
            p = input.value.lastIndexOf(from, cpos),
            pre = input.value.substring(0, p),
            end = input.value.substring(p + from.length, input.value.length);

        input.value = pre + to + end;

        // set caret to insert pos
        this.set_caret_pos(input, cpos + to.length - from.length);

        // run onchange action on the element
        $(input).trigger('change', [true, trigger]);
    };

    this.ksearch_click = function (node) {
        if (this.ksearch_input) {
            this.ksearch_input.focus();
        }

        this.insert_recipient(node._rcm_id);
        this.ksearch_hide();
    };

    this.ksearch_blur = function () {
        if (this.ksearch_timer) {
            clearTimeout(this.ksearch_timer);
        }

        this.ksearch_input = null;
        this.ksearch_hide();
    };

    this.ksearch_hide = function () {
        this.ksearch_selected = null;
        this.ksearch_value = '';

        if (this.ksearch_pane) {
            this.ksearch_pane.hide();
        }

        $(this.ksearch_input)
            .attr({ 'aria-haspopup': 'false', 'aria-expanded': 'false' })
            .removeAttr('aria-activedescendant')
            .removeAttr('aria-owns');

        this.ksearch_destroy();
    };

    // Clears autocomplete data/requests
    this.ksearch_destroy = function () {
        if (this.ksearch_data) {
            this.multi_thread_request_abort(this.ksearch_data.id);
        }

        if (this.ksearch_info) {
            this.hide_message(this.ksearch_info);
        }

        if (this.ksearch_msg) {
            this.hide_message(this.ksearch_msg);
        }

        this.ksearch_data = null;
        this.ksearch_info = null;
        this.ksearch_msg = null;
    };


    /*********************************************************/
    /*                  address book methods                 */
    /*********************************************************/

    this.contactlist_select = function (list) {
        if (this.preview_timer) {
            clearTimeout(this.preview_timer);
        }

        var id, targets,
            groupcount = 0,
            writable = false,
            deletable = false,
            copy_writable = false,
            selected = list.get_selection().length,
            source = this.env.source ? this.env.address_sources[this.env.source] : null;

        // we don't have dblclick handler here, so use 50 instead of this.dblclick_time
        if (this.env.contentframe && !list.multi_selecting && (id = list.get_single_selection())) {
            this.preview_timer = setTimeout(function () {
                ref.load_contact(id, 'show');
            }, this.preview_delay_click);
        } else if (this.env.contentframe) {
            this.show_contentframe(false);
        }

        if (selected) {
            list.draggable = false;

            // no source = search result, we'll need to detect if any of
            // selected contacts are in writable addressbook to enable edit/delete
            // we'll also need to know sources used in selection for copy
            // and group-addmember operations (drag&drop)
            this.env.selection_sources = [];

            if (source) {
                this.env.selection_sources.push(this.env.source);
            }

            $.each(list.get_selection(), function (i, v) {
                var book, sid, contact = list.data[v];
                if (!source) {
                    sid = String(v).replace(/^[^-]+-/, '');
                    book = sid ? ref.env.address_sources[sid] : null;

                    if (book) {
                        writable = writable || (!book.readonly && !contact.readonly);
                        deletable = deletable || book.deletable === true;
                        ref.env.selection_sources.push(sid);
                    }
                } else {
                    writable = writable || (!source.readonly && !contact.readonly);
                    deletable = deletable || source.deletable === true;
                }

                if (contact._type != 'group') {
                    list.draggable = true;
                }
            });

            this.env.selection_sources = $.unique(this.env.selection_sources);

            if (source && source.groups) {
                $.each(this.env.contactgroups, function () {
                    if (this.source === ref.env.source) {
                        groupcount++;
                    }
                });
            }

            targets = $.map(this.env.address_sources, function (v, i) {
                return v.readonly ? null : i;
            });
            copy_writable = $.grep(targets, function (v) {
                return jQuery.inArray(v, ref.env.selection_sources) < 0;
            }).length > 0;
        }

        // if a group is currently selected, and there is at least one contact selected
        // we can enable the group-remove-selected command
        this.enable_command('group-assign-selected', groupcount > 0 && writable);
        this.enable_command('group-remove-selected', this.env.group && writable);
        this.enable_command('print', 'qrcode', selected == 1);
        this.enable_command('export-selected', selected > 0);
        this.enable_command('edit', id && writable);
        this.enable_command('delete', 'move', writable || deletable);
        this.enable_command('copy', copy_writable);

        return false;
    };

    this.list_contacts = function (src, group, page, search) {
        var win, folder, index = -1, url = {},
            refresh = src === undefined && group === undefined && page === undefined,
            target = window;

        if (!src) {
            src = this.env.source;
        }

        if (refresh) {
            group = this.env.group;
        }

        if (src != this.env.source) {
            page = this.env.current_page = 1;
            this.reset_qsearch();
        } else if (!refresh && group != this.env.group) {
            page = this.env.current_page = 1;
        }

        if (this.env.search_id) {
            folder = 'S' + this.env.search_id;
        } else if (!this.env.search_request) {
            folder = group ? 'G' + src + group : src;
        }

        this.env.source = this.env.last_source = src;
        this.env.group = this.env.last_group = group;

        // truncate groups listing stack
        $.each(this.env.address_group_stack, function (i, v) {
            if (ref.env.group == v.id) {
                index = i;
                return false;
            }
        });

        this.env.address_group_stack = index < 0 ? [] : this.env.address_group_stack.slice(0, index);

        // remove cached contact group selector
        this.destroy_entity_selector('contactgroup-selector');

        // make sure the current group is on top of the stack
        if (this.env.group) {
            if (!search) {
                search = {};
            }
            search.id = this.env.group;
            this.env.address_group_stack.push(search);

            // mark the first group on the stack as selected in the directory list
            folder = 'G' + src + this.env.address_group_stack[0].id;
        } else if (this.gui_objects.addresslist_title) {
            $(this.gui_objects.addresslist_title).text(this.get_label('contacts'));
        }

        if (!this.env.search_id) {
            this.select_folder(folder, '', true);
        }

        // load contacts remotely
        if (this.gui_objects.contactslist) {
            this.list_contacts_remote(src, group, page);
            return;
        }

        if (win = this.get_frame_window(this.env.contentframe)) {
            target = win;
            url._framed = 1;
        }

        if (group) {
            url._gid = group;
        }
        if (page) {
            url._page = page;
        }
        if (src) {
            url._source = src;
        }

        // also send search request to get the correct listing
        if (this.env.search_request) {
            url._search = this.env.search_request;
        }

        this.set_busy(true, 'loading');
        this.location_href(url, target);
    };

    // send remote request to load contacts list
    this.list_contacts_remote = function (src, group, page) {
        // clear message list first
        this.list_contacts_clear();

        // send request to server
        var url = {}, lock = this.set_busy(true, 'loading');

        if (src) {
            url._source = src;
        }
        if (page) {
            url._page = page;
        }
        if (group) {
            url._gid = group;
        }

        this.env.source = src;
        this.env.group = group;

        // also send search request to get the right records
        if (this.env.search_request) {
            url._search = this.env.search_request;
        }

        this.http_request(this.env.task == 'mail' ? 'list-contacts' : 'list', url, lock);

        if (this.env.task != 'mail') {
            this.update_state({ _source: src, _page: page && page > 1 ? page : null, _gid: group });
        }
    };

    this.list_contacts_clear = function () {
        this.contact_list.data = {};
        this.contact_list.clear(true);
        this.show_contentframe(false);
        this.enable_command('delete', 'move', 'copy', 'print', false);
    };

    this.set_group_prop = function (prop) {
        if (this.gui_objects.addresslist_title) {
            var boxtitle = $(this.gui_objects.addresslist_title).html(''); // clear contents

            // add link to pop back to parent group
            if (this.env.address_group_stack.length > 1
                || (this.env.address_group_stack.length == 1 && this.env.address_group_stack[0].search_request)
            ) {
                var link = $('<a href="#list">...</a>')
                    .attr({ title: this.get_label('uponelevel'), class: 'poplink' })
                    .click(function () {
                        return ref.command('popgroup', '', this);
                    });

                boxtitle.append(link).append('&nbsp;&raquo;&nbsp;');
            }

            boxtitle.append($('<span>').text(prop ? prop.name : this.get_label('contacts')));
        }
    };

    // load contact record
    this.load_contact = function (cid, action, framed) {
        var win, url = {}, target = window,
            rec = this.contact_list ? this.contact_list.data[cid] : null;

        if (win = this.get_frame_window(this.env.contentframe)) {
            url._framed = 1;
            target = win;
            this.show_contentframe(true);

            // load dummy content, unselect selected row(s)
            if (!cid) {
                this.contact_list.clear_selection();
            }

            this.enable_command('export-selected', 'print', rec && rec._type != 'group');
        } else if (framed) {
            return false;
        }

        if (action && (cid || action == 'add') && !this.drag_active) {
            if (this.env.group) {
                url._gid = this.env.group;
            }

            if (this.env.search_request) {
                url._search = this.env.search_request;
            }

            if (cid) {
                url._cid = this.preview_id = cid;
            }

            url._action = action;
            url._source = this.env.source;

            this.location_href(url, target, true);
        }

        return true;
    };

    // add/delete member to/from the group
    this.group_member_change = function (what, cid, source, gid) {
        if (what != 'add') {
            what = 'del';
        }

        var lock = this.display_message(what == 'add' ? 'addingmember' : 'removingmember', 'loading'),
            post_data = { _cid: cid, _source: source, _gid: gid };

        this.http_post('group-' + what + 'members', post_data, lock);
    };

    this.contacts_drag_menu = function (e, to) {
        var dest = to.type == 'group' ? to.source : to.id,
            source = this.env.source;

        if (!this.env.address_sources[dest] || this.env.address_sources[dest].readonly) {
            return true;
        }

        // search result may contain contacts from many sources, but if there is only one...
        if (source == '' && this.env.selection_sources.length == 1) {
            source = this.env.selection_sources[0];
        }

        if (to.type == 'group' && dest == source) {
            var cid = this.contact_list.get_selection().join(',');
            this.group_member_change('add', cid, dest, to.id);
            return true;
        }

        // move action is not possible, "redirect" to copy if menu wasn't requested
        if (!this.commands.move && rcube_event.get_modifier(e) != SHIFT_KEY) {
            this.copy_contacts(to);
            return true;
        }

        return this.drag_menu(e, to);
    };

    // copy contact(s) to the specified target (group or directory)
    this.copy_contacts = function (to, event, cid) {
        if (!to) {
            cid = this.contact_list.get_selection();
            return this.addressbook_selector(event, function (to, obj) {
                var to = $(obj).data('source') ? ref.env.contactgroups['G' + $(obj).data('source') + $(obj).data('gid')] : ref.env.address_sources[to];
                ref.copy_contacts(to, null, cid);
            });
        }

        var dest = to.type == 'group' ? to.source : to.id,
            source = this.env.source,
            group = this.env.group ? this.env.group : '';

        cid = cid ? cid.join(',') : this.contact_list.get_selection().join(',');

        if (!cid || !this.env.address_sources[dest] || this.env.address_sources[dest].readonly) {
            return;
        }

        // search result may contain contacts from many sources, but if there is only one...
        if (source == '' && this.env.selection_sources.length == 1) {
            source = this.env.selection_sources[0];
        }

        // target is a group
        if (to.type == 'group') {
            if (dest == source) {
                return;
            }

            var lock = this.display_message('copyingcontact', 'loading'),
                post_data = {
                    _cid: cid, _source: this.env.source, _to: dest, _togid: to.id, _gid: group,
                };

            this.http_post('copy', post_data, lock);
        }
        // target is an addressbook
        else if (to.id != source) {
            var lock = this.display_message('copyingcontact', 'loading'),
                post_data = {
                    _cid: cid, _source: this.env.source, _to: to.id, _gid: group,
                };

            this.http_post('copy', post_data, lock);
        }
    };

    // move contact(s) to the specified target (group or directory)
    this.move_contacts = function (to, event, cid) {
        if (!to) {
            cid = this.contact_list.get_selection();
            return this.addressbook_selector(event, function (to, obj) {
                var to = $(obj).data('source') ? ref.env.contactgroups['G' + $(obj).data('source') + $(obj).data('gid')] : ref.env.address_sources[to];
                ref.move_contacts(to, null, cid);
            });
        }

        var dest = to.type == 'group' ? to.source : to.id,
            source = this.env.source,
            group = this.env.group ? this.env.group : '';

        if (!this.env.address_sources[dest] || this.env.address_sources[dest].readonly) {
            return;
        }

        if (!cid) {
            cid = this.contact_list.get_selection();
        }

        // search result may contain contacts from many sources, but if there is only one...
        if (source == '' && this.env.selection_sources.length == 1) {
            source = this.env.selection_sources[0];
        }

        if (to.type == 'group') {
            if (dest == source) {
                return;
            }

            this._with_selected_contacts('move', { _to: dest, _togid: to.id, _cid: cid });
        }
        // target is an addressbook
        else if (to.id != source) {
            this._with_selected_contacts('move', { _to: to.id, _cid: cid });
        }
    };

    // delete contact(s)
    this.delete_contacts = function () {
        var undelete = this.env.source && this.env.address_sources[this.env.source].undelete;

        if (undelete) {
            this._with_selected_contacts('delete', { _cid: this.contact_list.get_selection() });
        } else {
            var cid = this.contact_list.get_selection();
            this.confirm_dialog(this.get_label('deletecontactconfirm'), 'delete', function () {
                ref._with_selected_contacts('delete', { _cid: cid });
            });
        }
    };

    this._with_selected_contacts = function (action, post_data) {
        var selection = post_data._cid;

        // exit if no contact specified or if selection is empty
        if (!selection.length && !this.env.cid) {
            return;
        }

        var n, a_cids = [],
            label = action == 'delete' ? 'contactdeleting' : 'movingcontact',
            lock = this.display_message(label, 'loading'),
            display_next = this.check_display_next();

        if (this.env.cid) {
            a_cids.push(this.env.cid);
        } else {
            for (n = 0; n < selection.length; n++) {
                id = selection[n];
                a_cids.push(id);
                this.contact_list.remove_row(id, display_next && n == selection.length - 1);
            }

            if (!display_next) {
                this.contact_list.clear_selection();
            }
        }

        if (!post_data) {
            post_data = {};
        }

        post_data._source = this.env.source;
        post_data._from = this.env.action;
        post_data._cid = a_cids.join(',');

        if (this.env.group) {
            post_data._gid = this.env.group;
        }

        // also send search request to get the right records from the next page
        if (this.env.search_request) {
            post_data._search = this.env.search_request;
        }

        // send request to server
        this.http_post(action, post_data, lock);

        return true;
    };

    // update a contact record in the list
    this.update_contact_row = function (cid, cols_arr, newcid, source, data) {
        var list = this.contact_list;

        cid = this.html_identifier(cid);

        // when in searching mode, concat cid with the source name
        if (!list.rows[cid]) {
            cid = cid + '-' + source;
            if (newcid) {
                newcid = newcid + '-' + source;
            }
        }

        list.update_row(cid, cols_arr, newcid, true);
        list.data[cid] = data;
    };

    // add row to contacts list
    this.add_contact_row = function (cid, cols, classes, data) {
        if (!this.gui_objects.contactslist) {
            return false;
        }

        var c, col, list = this.contact_list,
            row = { cols: [] };

        row.id = 'rcmrow' + this.html_identifier(cid);
        row.className = 'contact ' + (classes || '');

        if (list.in_selection(cid)) {
            row.className += ' selected';
        }

        // add each submitted col
        for (c in cols) {
            col = {};
            col.className = String(c).toLowerCase();
            col.innerHTML = cols[c];
            row.cols.push(col);
        }

        // store data in list member
        list.data[cid] = data;
        list.insert_row(row);

        this.enable_command('export', list.rowcount > 0);
    };

    this.init_contact_form = function () {
        var col;

        if (this.env.coltypes) {
            this.set_photo_actions($('#ff_photo').val());
            for (col in this.env.coltypes) {
                this.init_edit_field(col, null);
            }
        }

        $('.contactfieldgroup .row a.deletebutton').click(function () {
            ref.delete_edit_field(this);
            return false;
        });

        $('select.addfieldmenu').change(function () {
            ref.insert_edit_field($(this).val(), $(this).attr('rel'), this);
            this.selectedIndex = 0;
        });

        // enable date pickers on date fields
        if ($.datepicker && this.env.date_format) {
            $.datepicker.setDefaults({
                dateFormat: this.env.date_format,
                changeMonth: true,
                changeYear: true,
                yearRange: '-120:+10',
                showOtherMonths: true,
                selectOtherMonths: true,
            });
            $('input.datepicker').datepicker();
        }

        // Submit search form on Enter
        if (this.env.action == 'search') {
            $(this.gui_objects.editform).append($('<input type="submit">').hide())
                .submit(function () {
                    $('input.mainaction').click(); return false;
                });
        }
    };

    // group creation dialog
    this.group_create = function () {
        var input = $('<input>').attr({ type: 'text', 'data-submit': 'true' }),
            content = $('<label>').text(this.get_label('namex')).append(input),
            source = this.env.source;

        this.simple_dialog(content, 'newgroup', function () {
            var name;
            if (name = input.val()) {
                ref.http_post('group-create', { _source: source, _name: name },
                    ref.set_busy(true, 'loading'));
                return true;
            }
        });
    };

    // group rename dialog
    this.group_rename = function () {
        if (!this.env.group) {
            return;
        }

        var group_name = this.env.contactgroups['G' + this.env.source + this.env.group].name,
            input = $('<input>').attr({ type: 'text', 'data-submit': 'true' }).val(group_name),
            content = $('<label>').text(this.get_label('namex')).append(input),
            source = this.env.source,
            group = this.env.group;

        this.simple_dialog(content, 'grouprename', function () {
            var name;
            if ((name = input.val()) && name != group_name) {
                ref.http_post('group-rename', { _source: source, _gid: group, _name: name },
                    ref.set_busy(true, 'loading'));
                return true;
            }
        });
    };

    this.group_delete = function () {
        if (this.env.group) {
            var group = this.env.group;
            this.confirm_dialog(this.get_label('deletegroupconfirm'), 'delete', function () {
                var lock = ref.set_busy(true, 'groupdeleting');
                ref.http_post('group-delete', { _source: ref.env.source, _gid: group }, lock);
            });
        }
    };

    // Callback from server upon group-delete command
    this.remove_group_item = function (prop) {
        var key = 'G' + prop.source + prop.id;

        if (this.treelist.remove(key)) {
            // make sure there is no cached address book or contact group selectors
            this.destroy_entity_selector('addressbook-selector');
            this.destroy_entity_selector('contactgroup-selector');

            this.triggerEvent('group_delete', { source: prop.source, id: prop.id });

            delete this.env.contactfolders[key];
            delete this.env.contactgroups[key];
        }

        if (prop.source == this.env.source && prop.id == this.env.group) {
            this.list_contacts(prop.source, 0);
        }
    };

    // assign selected contacts to a group
    this.group_assign_selected = function (props, obj, event) {
        var cid = ref.contact_list.get_selection(),
            source = ref.env.source;

        this.contactgroup_selector(event, function (to) {
            ref.group_member_change('add', cid, source, to);
        });
    };

    // remove selected contacts from current active group
    this.group_remove_selected = function () {
        this.http_post('group-delmembers', {
            _cid: this.contact_list.get_selection(),
            _source: this.env.source,
            _gid: this.env.group,
        });
    };

    // callback after deleting contact(s) from current group
    this.remove_group_contacts = function (props) {
        if (this.env.group !== undefined && this.env.group === props.gid) {
            var n, selection = this.contact_list.get_selection(),
                display_next = this.check_display_next();

            for (n = 0; n < selection.length; n++) {
                id = selection[n];
                this.contact_list.remove_row(id, display_next && n == selection.length - 1);
            }

            if (!display_next) {
                this.contact_list.clear_selection();
            }
        }
    };

    // callback for creating a new contact group
    this.insert_contact_group = function (prop) {
        prop.type = 'group';

        var key = 'G' + prop.source + prop.id,
            link = $('<a>').attr({ href: '#', rel: prop.source + ':' + prop.id })
                .click(function () {
                    return ref.command('listgroup', prop, this);
                })
                .text(prop.name);

        this.env.contactfolders[key] = this.env.contactgroups[key] = prop;
        this.treelist.insert({ id: key, html: link, classes: ['contactgroup'] }, prop.source, 'contactgroup');

        // make sure there is no cached address book or contact group selectors
        this.destroy_entity_selector('addressbook-selector');
        this.destroy_entity_selector('contactgroup-selector');

        this.triggerEvent('group_insert', {
            id: prop.id, source: prop.source, name: prop.name, li: this.treelist.get_item(key),
        });
    };

    // callback for renaming a contact group
    this.update_contact_group = function (prop) {
        var key = 'G' + prop.source + prop.id,
            newnode = {};

        // group ID has changed, replace link node and identifiers
        if (prop.newid) {
            var newkey = 'G' + prop.source + prop.newid,
                newprop = $.extend({}, prop);

            this.env.contactfolders[newkey] = this.env.contactfolders[key];
            this.env.contactfolders[newkey].id = prop.newid;
            this.env.group = prop.newid;

            delete this.env.contactfolders[key];
            delete this.env.contactgroups[key];

            newprop.id = prop.newid;
            newprop.type = 'group';

            newnode.id = newkey;
            newnode.html = $('<a>').attr({ href: '#', rel: prop.source + ':' + prop.newid })
                .click(function () {
                    return ref.command('listgroup', newprop, this);
                })
                .text(prop.name);
        } else {
            // update displayed group name
            $(this.treelist.get_item(key)).children().first().text(prop.name);
            this.env.contactfolders[key].name = this.env.contactgroups[key].name = prop.name;

            if (prop.source == this.env.source && prop.id == this.env.group) {
                this.set_group_prop(prop);
            }
        }

        // update list node and re-sort it
        this.treelist.update(key, newnode, true);

        // make sure there is no cached address book or contact group selectors
        this.destroy_entity_selector('addressbook-selector');
        this.destroy_entity_selector('contactgroup-selector');

        this.triggerEvent('group_update', {
            id: prop.id, source: prop.source, name: prop.name, li: this.treelist.get_item(key), newid: prop.newid,
        });
    };

    this.update_group_commands = function () {
        var source = this.env.source != '' ? this.env.address_sources[this.env.source] : null,
            supported = source && source.groups && !source.readonly;

        this.enable_command('group-create', supported);
        this.enable_command('group-rename', 'group-delete', supported && this.env.group);
    };

    this.init_edit_field = function (col, elem) {
        var label = this.env.coltypes[col].label;

        if (!elem) {
            elem = $('.ff_' + col);
        }

        if (label && !$('label[for="ff_' + col + '"]').length) {
            elem.placeholder(label);
        }
    };

    this.insert_edit_field = function (col, section, menu) {
        // just make pre-defined input field visible
        var elem = $('#ff_' + col);

        if (elem.length) {
            $('label[for="ff_' + col + '"]').parent().show();
            elem.show().focus();
            $(menu).children('option[value="' + col + '"]').prop('disabled', true);
        } else {
            var lastelem = $('.ff_' + col),
                appendcontainer = $('#contactsection' + section + ' .contactcontroller' + col);

            if (!appendcontainer.length) {
                var sect = $('#contactsection' + section),
                    lastgroup = $('.contactfieldgroup', sect).last();
                appendcontainer = $('<fieldset>').addClass('contactfieldgroup contactcontroller' + col);
                if (lastgroup.length) {
                    appendcontainer.insertAfter(lastgroup);
                } else {
                    sect.prepend(appendcontainer);
                }
            }

            if (appendcontainer.get(0).nodeName == 'FIELDSET') {
                var label, input,
                    colprop = this.env.coltypes[col],
                    name_suffix = colprop.limit != 1 ? '[]' : '',
                    compact = $(menu).data('compact') ? true : false,
                    input_id = 'ff_' + col + (colprop.count || 0),
                    row = $('<div>').addClass('row input-group'),
                    cell = $('<div>').addClass('contactfieldcontent ' + colprop.type);

                // Field label
                if (colprop.subtypes_select) {
                    label = $(colprop.subtypes_select);
                    if (!compact) {
                        label = $('<div>').addClass('contactfieldlabel label').append(label);
                    } else {
                        label.addClass('input-group-prepend');
                    }
                } else {
                    label = $('<label>').addClass('contactfieldlabel label input-group-text')
                        .attr('for', input_id).text(colprop.label);

                    if (compact) {
                        label = $('<span class="input-group-prepend">').append(label);
                    }
                }

                // Field input
                if (colprop.type == 'text' || colprop.type == 'date') {
                    input = $('<input>')
                        .addClass('form-control ff_' + col)
                        .attr({
                            type: 'text', name: '_' + col + name_suffix, size: colprop.size, id: input_id,
                        });

                    this.init_edit_field(col, input);

                    if (colprop.type == 'date' && $.datepicker) {
                        input.addClass('datepicker').datepicker();
                    }
                } else if (colprop.type == 'textarea') {
                    input = $('<textarea>')
                        .addClass('form-control ff_' + col)
                        .attr({
                            name: '_' + col + name_suffix, cols: colprop.size, rows: colprop.rows, id: input_id,
                        });

                    this.init_edit_field(col, input);
                } else if (colprop.type == 'composite') {
                    var i, childcol, cp, first, templ, cols = [], suffices = [], content = cell;

                    row.addClass('composite');

                    if (compact) {
                        content = $('<div class="content input-group-text">');
                    }

                    // read template for composite field order
                    if (templ = this.env[col + '_template']) {
                        for (i = 0; i < templ.length; i++) {
                            cols.push(templ[i][1]);
                            suffices.push(templ[i][2]);
                        }
                    } else { // list fields according to appearance in colprop
                        for (childcol in colprop.childs) {
                            cols.push(childcol);
                        }
                    }

                    for (i = 0; i < cols.length; i++) {
                        childcol = cols[i];
                        cp = colprop.childs[childcol];
                        input = $('<input>')
                            .addClass('form-control ff_' + childcol)
                            .attr({ type: 'text', name: '_' + childcol + name_suffix, size: cp.size })
                            .appendTo(content);

                        if (!compact) {
                            content.append(suffices[i] || ' ');
                        }

                        this.init_edit_field(childcol, input);
                        if (!first) {
                            first = input;
                        }
                    }

                    if (compact) {
                        input = content;
                    } else {
                        input = first;
                    } // set focus to the first of this composite fields
                } else if (colprop.type == 'select') {
                    input = $('<select>')
                        .addClass('custom-select ff_' + col)
                        .attr({ name: '_' + col + name_suffix, id: input_id });

                    var options = input.attr('options');
                    options[options.length] = new Option('---', '');
                    if (colprop.options) {
                        $.each(colprop.options, function (i, val) {
                            options[options.length] = new Option(val, i);
                        });
                    }
                }

                if (input) {
                    var delbutton = $('<a href="#del"></a>')
                        .addClass('contactfieldbutton deletebutton input-group-text icon delete')
                        .attr({ title: this.get_label('delete'), rel: col })
                        .html(this.env.delbutton)
                        .click(function () {
                            ref.delete_edit_field(this); return false;
                        });

                    row.append(label);

                    if (!compact) {
                        if (colprop.type != 'composite') {
                            cell.append(input);
                        }
                        row.append(cell.append(delbutton));
                    } else {
                        row.append(input).append(delbutton);
                        delbutton.wrap('<span class="input-group-append">');
                    }

                    row.appendTo(appendcontainer.show());

                    if (input.is('div')) {
                        input.find('input').first().focus();
                    } else {
                        input.first().focus();
                    }

                    // disable option if limit reached
                    if (!colprop.count) {
                        colprop.count = 0;
                    }
                    if (++colprop.count == colprop.limit && colprop.limit) {
                        $(menu).children('option[value="' + col + '"]').prop('disabled', true);
                    }

                    this.triggerEvent('insert-edit-field', input);
                }
            }
        }
    };

    this.delete_edit_field = function (elem) {
        var col = $(elem).attr('rel'),
            colprop = this.env.coltypes[col],
            input_group = $(elem).parents('div.row'),
            fieldset = $(elem).parents('fieldset.contactfieldgroup'),
            addmenu = fieldset.parent().find('select.addfieldmenu');

        // just clear input but don't hide the last field
        if (--colprop.count <= 0 && colprop.visible) {
            input_group.find('input').val('').blur();
        } else {
            input_group.remove();
            // hide entire fieldset if no more rows
            if (!fieldset.children('div.row').length) {
                fieldset.hide();
            }
        }

        // enable option in add-field selector or insert it if necessary
        if (addmenu.length) {
            var option = addmenu.children('option[value="' + col + '"]');
            if (option.length) {
                option.prop('disabled', false);
            } else {
                option = $('<option>').attr('value', col).html(colprop.label).appendTo(addmenu);
            }
            addmenu.show();
        }
    };

    this.upload_contact_photo = function (form) {
        if (form && form.elements._photo.value) {
            this.async_upload_form(form, 'upload-photo', function (e) {
                ref.set_busy(false, null, ref.file_upload_id);
            });

            // display upload indicator
            this.file_upload_id = this.set_busy(true, 'uploading');
        }
    };

    this.replace_contact_photo = function (id) {
        var img_src = id == '-del-' ? this.env.photo_placeholder
            : this.env.comm_path + '&_action=photo&_source=' + this.env.source + '&_cid=' + (this.env.cid || 0) + '&_photo=' + id;

        this.set_photo_actions(id);
        $(this.gui_objects.contactphoto).children('img').attr('src', img_src);
    };

    this.photo_upload_end = function () {
        this.set_busy(false, null, this.file_upload_id);
        delete this.file_upload_id;
    };

    this.set_photo_actions = function (id) {
        var n, buttons = this.buttons['upload-photo'];
        for (n = 0; buttons && n < buttons.length; n++) {
            $('a#' + buttons[n].id).html(this.get_label(id == '-del-' ? 'addphoto' : 'replacephoto'));
        }

        $('#ff_photo').val(id);
        this.enable_command('upload-photo', this.env.coltypes.photo ? true : false);
        this.enable_command('delete-photo', this.env.coltypes.photo && id != '-del-');
    };

    // load advanced search page
    this.advanced_search = function () {
        var dialog = $('<iframe>').attr('src', this.url('search', { _form: 1, _framed: 1 })),
            search_func = function () {
                var valid = false, form = { _adv: 1 };

                $.each($(dialog[0].contentWindow.rcmail.gui_objects.editform).serializeArray(), function () {
                    if (this.name.match(/^_search/) && this.value != '') {
                        form[this.name] = this.value;
                        valid = true;
                    }
                });

                if (valid) {
                    ref.http_post('search', form, ref.set_busy(true, 'searching'));
                    return true;
                }
            };

        this.simple_dialog(dialog, 'advsearch', search_func, {
            button: 'search',
            width: 600,
            height: 500,
        });

        return true;
    };

    // unselect directory/group
    this.unselect_directory = function () {
        this.select_folder('');
        this.enable_command('search-delete', false);
    };

    // callback for creating a new saved search record
    this.insert_saved_search = function (name, id) {
        var key = 'S' + id,
            link = $('<a>').attr({ href: '#', rel: id })
                .click(function () {
                    return ref.command('listsearch', id, this);
                })
                .html(name),
            prop = { name: name, id: id };

        this.savedsearchlist.insert({ id: key, html: link, classes: ['contactsearch'] }, null, 'contactsearch');
        this.select_folder(key, '', true);
        this.enable_command('search-delete', true);
        this.env.search_id = id;

        this.triggerEvent('abook_search_insert', prop);
    };

    // creates a dialog for saved search
    this.search_create = function () {
        var input = $('<input>').attr('type', 'text'),
            content = $('<label>').text(this.get_label('namex')).append(input);

        this.simple_dialog(content, 'searchsave',
            function () {
                var name;
                if (name = input.val()) {
                    ref.http_post('search-create', { _search: ref.env.search_request, _name: name },
                        ref.set_busy(true, 'loading'));
                    return true;
                }
            }
        );
    };

    this.search_delete = function () {
        if (this.env.search_request) {
            var lock = this.set_busy(true, 'savedsearchdeleting');
            this.http_post('search-delete', { _sid: this.env.search_id }, lock);
        }
    };

    // callback from server upon search-delete command
    this.remove_search_item = function (id) {
        if (this.savedsearchlist.remove('S' + id)) {
            this.triggerEvent('search_delete', { id: id });
        }

        this.env.search_id = null;
        this.env.search_request = null;
        this.list_contacts_clear();
        this.reset_qsearch();
        this.enable_command('search-delete', 'search-create', false);
    };

    this.listsearch = function (id) {
        var lock = this.set_busy(true, 'searching');

        if (this.contact_list) {
            this.list_contacts_clear();
        }

        this.reset_qsearch();

        if (this.savedsearchlist) {
            this.treelist.select('');
            this.savedsearchlist.select('S' + id);
        } else {
            this.select_folder('S' + id, '', true);
        }

        // reset vars
        this.env.current_page = 1;
        this.http_request('search', { _sid: id }, lock);
    };

    // display a dialog with QR code image
    this.qrcode = function () {
        var img = new Image(300, 300);

        img.src = this.url('addressbook/qrcode', { _source: this.env.source, _cid: this.get_single_cid() });

        return this.simple_dialog(img, 'qrcode', null, {
            button: false,
            cancel_button: 'close',
            width: 300,
            height: 300,
        });
    };


    /*********************************************************/
    /*                 user settings methods                 */
    /*********************************************************/

    // preferences section select and load options frame
    this.section_select = function (list) {
        var win, id = list.get_single_selection();

        if (id && (win = this.get_frame_window(this.env.contentframe))) {
            this.location_href({ _action: 'edit-prefs', _section: id, _framed: 1 }, win, true);
        }
    };

    this.response_select = function (list) {
        var id = list.get_single_selection();

        this.enable_command('delete', !!id && $.inArray(id, this.env.readonly_responses) < 0);

        if (id) {
            this.load_response(id, 'edit-response');
        }
    };

    // load response record
    this.load_response = function (id, action) {
        var win;

        if (win = this.get_frame_window(this.env.contentframe)) {
            if (id || action == 'add-response') {
                if (!id) {
                    this.responses_list.clear_selection();
                }

                this.location_href({ _action: action, _id: id, _framed: 1 }, win, true);
            }
        }
    };

    this.identity_select = function (list) {
        var id = list.get_single_selection();

        this.enable_command('delete', !!id && list.rowcount > 1 && this.env.identities_level < 2);

        if (id) {
            this.load_identity(id, 'edit-identity');
        }
    };

    // load identity record
    this.load_identity = function (id, action) {
        var win;

        if (win = this.get_frame_window(this.env.contentframe)) {
            if (id || action == 'add-identity') {
                if (!id) {
                    this.identity_list.clear_selection();
                }

                this.location_href({ _action: action, _iid: id, _framed: 1 }, win, true);
            }
        }
    };

    this.delete_identity = function (id) {
        if (!id && this.identity_list) {
            id = this.identity_list.get_single_selection();
        }

        if (id) {
            this.confirm_dialog(this.get_label('deleteidentityconfirm'), 'delete', function () {
                ref.http_post('settings/delete-identity', { _iid: id }, true);
            });
        }
    };

    this.delete_response = function (id) {
        if (!id && this.responses_list) {
            id = this.responses_list.get_single_selection();
        }

        if (id) {
            this.confirm_dialog(this.get_label('deleteresponseconfirm'), 'delete', function () {
                ref.http_post('settings/delete-response', { _id: id }, true);
            });
        }
    };

    // Update indetities list (add or update a record)
    this.update_identity_row = function (id, name, add) {
        var list = this.identity_list,
            rid = this.html_identifier(id);

        if (add) {
            list.insert_row({ id: 'rcmrow' + rid, cols: [{ className: 'mail', innerHTML: name }] });
            list.select(rid);
        } else {
            list.update_row(rid, [name]);
        }
    };

    // Update responses list (add or update a record)
    this.update_response_row = function (id, name, add) {
        var list = this.responses_list;

        if (add) {
            list.insert_row({ id: 'rcmrow' + id, cols: [{ className: 'name', innerHTML: name }] });
            list.select(id);
        } else {
            list.update_row(id, [name]);
        }
    };

    // Remove response record from the responses list
    this.remove_response = function (id) {
        if (this.responses_list) {
            this.responses_list.remove_row(id);
            this.show_contentframe(false);
        }

        this.enable_command('delete', false);
    };

    // Remove identity record from the identities list
    this.remove_identity = function (id) {
        var list = this.identity_list,
            rid = this.html_identifier(id);

        if (list && id) {
            list.remove_row(rid);
            this.show_contentframe(false);
        }

        this.enable_command('delete', false);
    };


    /*********************************************************/
    /*                 folder manager methods                */
    /*********************************************************/

    this.init_subscription_list = function () {
        var delim = RegExp.escape(this.env.delimiter);

        this.last_sub_rx = RegExp('[' + delim + ']?[^' + delim + ']+$');

        this.subscription_list = new rcube_treelist_widget(this.gui_objects.subscriptionlist, {
            selectable: true,
            tabexit: false,
            parent_focus: true,
            id_prefix: 'rcmli',
            id_encode: this.html_identifier_encode,
            id_decode: this.html_identifier_decode,
            searchbox: '#foldersearch',
        });

        this.subscription_list
            .addEventListener('select', function (node) {
                ref.subscription_select(node.id);
            })
            .addEventListener('collapse', function (node) {
                ref.folder_collapsed(node);
            })
            .addEventListener('expand', function (node) {
                ref.folder_collapsed(node);
            })
            .addEventListener('search', function (p) {
                if (p.query) {
                    ref.subscription_select();
                }
            })
            .draggable({ cancel: 'li.mailbox.root,input,div.treetoggle,.custom-control' })
            .droppable({
                // @todo: find better way, accept callback is executed for every folder
                // on the list when dragging starts (and stops), this is slow, but
                // I didn't find a method to check droptarget on over event
                accept: function (node) {
                    if (!node.is('.mailbox')) {
                        return false;
                    }

                    var source_folder = ref.folder_id2name(node.attr('id')),
                        dest_folder = ref.folder_id2name(this.id),
                        source = ref.env.subscriptionrows[source_folder],
                        dest = ref.env.subscriptionrows[dest_folder];

                    return source && !source[2]
                        && dest_folder != source_folder.replace(ref.last_sub_rx, '')
                        && !dest_folder.startsWith(source_folder + ref.env.delimiter);
                },
                drop: function (e, ui) {
                    var source = ref.folder_id2name(ui.draggable.attr('id')),
                        dest = ref.folder_id2name(this.id);

                    ref.subscription_move_folder(source, dest);
                },
            });
    };

    this.folder_id2name = function (id) {
        return id ? ref.html_identifier_decode(id.replace(/^rcmli/, '')) : null;
    };

    this.subscription_select = function (id) {
        var folder;

        if (id && id != '*' && (folder = this.env.subscriptionrows[id])) {
            this.env.mailbox = id;
            this.show_folder(id);
            this.enable_command('delete-folder', !folder[2]);
        } else {
            this.env.mailbox = null;
            this.show_contentframe(false);
            this.enable_command('delete-folder', 'purge', false);
        }
    };

    this.subscription_move_folder = function (from, to) {
        if (from && to !== null && from != to && to != from.replace(this.last_sub_rx, '')) {
            var path = from.split(this.env.delimiter),
                basename = path.pop(),
                newname = to === '' || to === '*' ? basename : to + this.env.delimiter + basename;

            if (newname != from) {
                this.confirm_dialog(this.get_label('movefolderconfirm'), 'move', function () {
                    ref.http_post('rename-folder', { _folder_oldname: from, _folder_newname: newname },
                        ref.set_busy(true, 'foldermoving'));
                }, { button_class: 'save move' });
            }
        }
    };

    // tell server to create and subscribe a new mailbox
    this.create_folder = function () {
        this.show_folder('', this.env.mailbox);
    };

    // delete a specific mailbox with all its messages
    this.delete_folder = function (name) {
        if (!name) {
            name = this.env.mailbox;
        }

        if (name) {
            this.confirm_dialog(this.get_label('deletefolderconfirm'), 'delete', function () {
                ref.http_post('delete-folder', { _mbox: name }, ref.set_busy(true, 'folderdeleting'));
            });
        }
    };

    // Add folder row to the table and initialize it
    this.add_folder_row = function (id, name, display_name, is_protected, subscribed, class_name, refrow, subfolders) {
        if (!this.gui_objects.subscriptionlist) {
            return false;
        }

        // reset searching
        if (this.subscription_list.is_search()) {
            this.subscription_select();
            this.subscription_list.reset_search();
        }

        // disable drag-n-drop temporarily
        // some skins disable dragging in mobile mode, so we have to check if it is still draggable
        if (this.subscription_list.is_draggable()) {
            this.subscription_list.draggable('destroy').droppable('destroy');
        }

        var n, tmp, tmp_name, rowid, collator, pos, p, parent = '',
            folders = [], list = [], slist = [],
            list_element = $(this.gui_objects.subscriptionlist),
            row = refrow ? refrow : $($('li', list_element).get(1)).clone(true);

        if (!row.length) {
            // Refresh page if we don't have a table row to clone
            this.goto_url('folders');
            return false;
        }

        // set ID, reset css class
        row.attr({ id: 'rcmli' + this.html_identifier_encode(id), class: class_name });

        if (!refrow || !refrow.length) {
            // remove old data, subfolders and toggle
            $('ul,div.treetoggle', row).remove();
            row.removeData('filtered');
        }

        // set folder name
        $('a', row).first().text(display_name).removeAttr('title');

        // update subscription checkbox
        $('input[name="_subscribed[]"]', row).first().val(id)
            .prop({ checked: subscribed ? true : false, disabled: is_protected ? true : false });

        // add to folder/row-ID map
        this.env.subscriptionrows[id] = [name, display_name, false];

        // copy folders data to an array for sorting
        $.each(this.env.subscriptionrows, function (k, v) {
            v[3] = k; folders.push(v);
        });

        try {
            // use collator if supported (FF29, IE11, Opera15, Chrome24)
            collator = new Intl.Collator(this.env.locale.replace('_', '-'));
        } catch (e) {}

        // sort folders
        folders.sort(function (a, b) {
            var i, f1, f2,
                path1 = a[0].split(ref.env.delimiter),
                path2 = b[0].split(ref.env.delimiter),
                len = path1.length;

            for (i = 0; i < len; i++) {
                f1 = path1[i];
                f2 = path2[i];

                if (f1 !== f2) {
                    if (f2 === undefined) {
                        return 1;
                    }
                    if (collator) {
                        return collator.compare(f1, f2);
                    }

                    return f1 < f2 ? -1 : 1;
                }

                if (i == len - 1) {
                    return -1;
                }
            }
        });

        for (n in folders) {
            p = folders[n][3];
            // protected folder
            if (folders[n][2]) {
                tmp_name = p + this.env.delimiter;
                // prefix namespace cannot have subfolders (#1488349)
                if (tmp_name == this.env.prefix_ns) {
                    continue;
                }
                slist.push(p);
                tmp = tmp_name;
            }
            // protected folder's child
            else if (tmp && p.startsWith(tmp)) {
                slist.push(p);
            }
            // other
            else {
                list.push(p);
                tmp = null;
            }
        }

        // check if subfolder of a protected folder
        for (n = 0; n < slist.length; n++) {
            if (id.startsWith(slist[n] + this.env.delimiter)) {
                rowid = slist[n];
            }
        }

        // find folder position after sorting
        for (n = 0; !rowid && n < list.length; n++) {
            if (n && list[n] == id) {
                rowid = list[n - 1];
            }
        }

        // add row to the table
        if (rowid && (n = this.subscription_list.get_item(rowid, true))) {
            // find parent folder
            if (pos = id.lastIndexOf(this.env.delimiter)) {
                parent = id.substring(0, pos);
                parent = this.subscription_list.get_item(parent, true);

                // add required tree elements to the parent if not already there
                if (!$('div.treetoggle', parent).length) {
                    $('<div>&nbsp;</div>').addClass('treetoggle collapsed').appendTo(parent);
                }
                if (!$('ul', parent).length) {
                    $('<ul>').css('display', 'none').appendTo(parent);
                }
            }

            if (parent && n == parent) {
                $('ul', parent).first().append(row);
            } else {
                while (p = $(n).parent().parent().get(0)) {
                    if (parent && p == parent) {
                        break;
                    }
                    if (!$(p).is('li.mailbox')) {
                        break;
                    }
                    n = p;
                }

                $(n).after(row);
            }
        } else {
            list_element.append(row);
        }

        // add subfolders
        $.extend(this.env.subscriptionrows, subfolders || {});

        // update list widget
        this.subscription_list.reset(true);
        this.subscription_select();

        // expand parent
        if (parent) {
            this.subscription_list.expand(this.folder_id2name(parent.id));
        }

        row = row.show().get(0);
        if (row.scrollIntoView) {
            row.scrollIntoView(false);
        }

        // Let skins to do their magic, e.g. Elastic will fix pretty checkbox
        if (!refrow) {
            this.triggerEvent('clonerow', { row: row, id: id });
        }

        return row;
    };

    // replace an existing table row with a new folder line (with subfolders)
    this.replace_folder_row = function (oldid, id, name, display_name, is_protected, class_name) {
        if (!this.gui_objects.subscriptionlist) {
            if (this.is_framed()) {
                // @FIXME: for some reason this 'parent' variable need to be prefixed with 'window.'
                return window.parent.rcmail.replace_folder_row(oldid, id, name, display_name, is_protected, class_name);
            }

            return false;
        }

        // reset searching
        if (this.subscription_list.is_search()) {
            this.subscription_select();
            this.subscription_list.reset_search();
        }

        var subfolders = {},
            row = this.subscription_list.get_item(oldid, true),
            parent = $(row).parent(),
            old_folder = this.env.subscriptionrows[oldid],
            prefix_len_id = oldid.length,
            prefix_len_name = old_folder[0].length,
            subscribed = $('input[name="_subscribed[]"]', row).first().prop('checked');

        // no renaming, only update class_name
        if (oldid == id) {
            $(row).attr('class', class_name || '');
            return;
        }

        // update subfolders
        $('li', row).each(function () {
            var fname = ref.folder_id2name(this.id),
                folder = ref.env.subscriptionrows[fname],
                newid = id + fname.slice(prefix_len_id);

            this.id = 'rcmli' + ref.html_identifier_encode(newid);
            $('input[name="_subscribed[]"]', this).first().val(newid);
            folder[0] = name + folder[0].slice(prefix_len_name);

            subfolders[newid] = folder;
            delete ref.env.subscriptionrows[fname];
        });

        // get row off the list
        row = $(row).detach();

        delete this.env.subscriptionrows[oldid];

        // remove parent list/toggle elements if not needed
        if (parent.get(0) != this.gui_objects.subscriptionlist && !$('li', parent).length) {
            $('ul,div.treetoggle', parent.parent()).remove();
        }

        // move the existing table row
        this.add_folder_row(id, name, display_name, is_protected, subscribed, class_name, row, subfolders);
    };

    // remove the table row of a specific mailbox from the table
    this.remove_folder_row = function (folder) {
        // reset searching
        if (this.subscription_list.is_search()) {
            this.subscription_select();
            this.subscription_list.reset_search();
        }

        var list = [], row = this.subscription_list.get_item(folder, true);

        // get subfolders if any
        $('li', row).each(function () {
            list.push(ref.folder_id2name(this.id));
        });

        // remove folder row (and subfolders)
        this.subscription_list.remove(folder);

        // update local list variable
        list.push(folder);
        $.each(list, function (i, v) {
            delete ref.env.subscriptionrows[v];
        });
    };

    this.subscribe = function (folder) {
        this.change_subscription_state(folder, true);
    };

    this.unsubscribe = function (folder) {
        this.change_subscription_state(folder, false);
    };

    this.change_subscription_state = function (folder, state) {
        if (folder) {
            var prefix = state ? '' : 'un',
                lock = this.display_message('folder' + prefix + 'subscribing', 'loading');

            this.http_post(prefix + 'subscribe', { _mbox: folder }, lock);

            // in case this was a list of search results, update also the main list
            $(this.gui_objects.subscriptionlist).find('input[value="' + folder + '"]').prop('checked', state);
        }
    };


    // when user select a folder in manager
    this.show_folder = function (folder, path, force) {
        var win, target = window,
            action = folder === '' ? 'add' : 'edit',
            url = '&_action=' + action + '-folder&_mbox=' + urlencode(folder);

        if (path) {
            url += '&_path=' + urlencode(path);
        }

        if (win = this.get_frame_window(this.env.contentframe)) {
            target = win;
            url += '&_framed=1';
        }

        if (String(target.location.href).indexOf(url) >= 0 && !force) {
            this.show_contentframe(true);
        } else {
            this.location_href(this.env.comm_path + url, target, true);
        }
    };

    // disables subscription checkbox (for protected folder)
    this.disable_subscription = function (folder) {
        var row = this.subscription_list.get_item(folder, true);
        if (row) {
            $('input[name="_subscribed[]"]', row).first().prop('disabled', true);
        }
    };

    // resets state of subscription checkbox (e.g. on error)
    this.reset_subscription = function (folder, state) {
        var row = this.subscription_list.get_item(folder, true);
        if (row) {
            $('input[name="_subscribed[]"]', row).first().prop('checked', state);
        }
    };

    this.folder_size = function (folder) {
        var lock = this.set_busy(true, 'loading');
        this.http_post('folder-size', { _mbox: folder }, lock);
    };

    this.folder_size_update = function (size) {
        $('#folder-size').replaceWith(size);
    };

    // filter folders by namespace
    this.folder_filter = function (prefix) {
        this.subscription_list.reset_search();

        this.subscription_list.container.children('li').each(function () {
            var i, folder = ref.folder_id2name(this.id);
            // show all folders
            if (prefix == '---') {
                // do nothing
            }
            // got namespace prefix
            else if (prefix) {
                if (folder !== prefix) {
                    $(this).data('filtered', true).hide();
                    return;
                }
            }
            // no namespace prefix, filter out all other namespaces
            else {
                // first get all namespace roots
                for (i in ref.env.ns_roots) {
                    if (folder === ref.env.ns_roots[i]) {
                        $(this).data('filtered', true).hide();
                        return;
                    }
                }
            }

            $(this).removeData('filtered').show();
        });
    };

    /*********************************************************/
    /*                   GUI functionality                   */
    /*********************************************************/

    this.init_button = function (cmd, prop) {
        var elm = document.getElementById(prop.id);
        if (!elm) {
            return;
        }

        var preload = false;
        if (prop.type == 'image') {
            elm = elm.parentNode;
            preload = true;
        }

        elm._command = cmd;
        elm._id = prop.id;

        if (prop.sel) {
            elm.onmousedown = function (e) {
                return ref.button_sel(this._command, this._id);
            };
            elm.onmouseup = function (e) {
                return ref.button_out(this._command, this._id);
            };
            if (preload) {
                new Image().src = prop.sel;
            }
        }

        if (prop.over) {
            elm.onmouseover = function (e) {
                return ref.button_over(this._command, this._id);
            };
            elm.onmouseout = function (e) {
                return ref.button_out(this._command, this._id);
            };
            if (preload) {
                new Image().src = prop.over;
            }
        }
    };

    // set event handlers on registered buttons
    this.init_buttons = function () {
        for (var cmd in this.buttons) {
            if (typeof cmd !== 'string') {
                continue;
            }

            for (var i = 0; i < this.buttons[cmd].length; i++) {
                this.init_button(cmd, this.buttons[cmd][i]);
            }
        }
    };

    // set button to a specific state
    this.set_button = function (command, state) {
        var n, button, obj, a_buttons = this.buttons[command],
            len = a_buttons ? a_buttons.length : 0;

        for (n = 0; n < len; n++) {
            button = a_buttons[n];
            obj = document.getElementById(button.id);

            if (!obj || button.status === state) {
                continue;
            }

            // get default/passive setting of the button
            if (button.type == 'image' && !button.status) {
                button.pas = obj._original_src ? obj._original_src : obj.src;
                // respect PNG fix on IE browsers
                if (obj.runtimeStyle && obj.runtimeStyle.filter && obj.runtimeStyle.filter.match(/src=['"]([^'"]+)['"]/)) {
                    button.pas = RegExp.$1;
                }
            } else if (!button.status) {
                button.pas = String(obj.className);
            }

            button.status = state;

            // set image according to button state
            if (button.type == 'image' && button[state]) {
                obj.src = button[state];
            }
            // set class name according to button state
            else if (button[state] !== undefined) {
                obj.className = button[state];
            }

            // disable/enable input buttons
            if (button.type == 'input' || button.type == 'button') {
                obj.disabled = state == 'pas';
            } else {
                $(obj).attr({
                    tabindex: state == 'pas' || state == 'sel' ? '-1' : ($(obj).attr('data-tabindex') || '0'),
                    'aria-disabled': state == 'pas' || state == 'sel' ? 'true' : 'false',
                });
            }
        }
    };

    // display a specific alttext
    this.set_alttext = function (command, label) {
        var n, button, obj, link, label,
            a_buttons = this.buttons[command],
            len = a_buttons ? a_buttons.length : 0;

        for (n = 0; n < len; n++) {
            button = a_buttons[n];
            obj = document.getElementById(button.id);
            label = this.get_label(label);

            if (obj && button.type == 'image') {
                obj.setAttribute('alt', label);
                if ((link = obj.parentNode) && link.tagName.toLowerCase() == 'a') {
                    link.setAttribute('title', label);
                }
            } else if (obj) {
                obj.setAttribute('title', label);
            }
        }
    };

    // mouse over button
    this.button_over = function (command, id) {
        this.button_event(command, id, 'over');
    };

    // mouse down on button
    this.button_sel = function (command, id) {
        this.button_event(command, id, 'sel');
    };

    // mouse out of button
    this.button_out = function (command, id) {
        this.button_event(command, id, 'act');
    };

    // event of button
    this.button_event = function (command, id, event) {
        var n, button, obj, a_buttons = this.buttons[command],
            len = a_buttons ? a_buttons.length : 0;

        for (n = 0; n < len; n++) {
            button = a_buttons[n];
            if (button.id == id && button.status == 'act') {
                if (button[event] && (obj = document.getElementById(button.id))) {
                    obj[button.type == 'image' ? 'src' : 'className'] = button[event];
                }

                if (event == 'sel') {
                    this.buttons_sel[id] = command;
                }
            }
        }
    };

    // write to the document/window title
    this.set_pagetitle = function (title) {
        if (title && document.title) {
            document.title = title;
        }
    };

    // display a system message, list of types in common.css (below #message definition)
    this.display_message = function (msg, type, timeout, key) {
        if (msg && msg.length && /^[a-z._]+$/.test(msg)) {
            msg = this.get_label(msg);
        }

        // pass command to parent window
        if (this.is_framed()) {
            return parent.rcmail.display_message(msg, type, timeout);
        }

        if (!this.gui_objects.message) {
            // save message in order to display after page loaded
            if (type != 'loading') {
                this.pending_message = [msg, type, timeout, key];
            }
            return 1;
        }

        if (!type) {
            type = 'notice';
        } else if (type == 'loading') {
            if (!key) {
                key = 'loading';
            }
            if (!timeout) {
                timeout = this.env.request_timeout * 1000;
            }
            if (!msg) {
                msg = this.get_label('loading');
            }
        }

        if (!key) {
            key = this.html_identifier(msg);
        }

        var date = new Date(),
            id = type + date.getTime();

        if (!timeout) {
            switch (type) {
                case 'error':
                case 'warning':
                    timeout = this.message_time * 2;
                    break;
                case 'uploading':
                    timeout = 0;
                    break;
                default:
                    timeout = this.message_time;
            }
        }

        // The same message is already displayed
        if (this.messages[key]) {
            // replace label
            if (this.messages[key].obj) {
                $('div.content', this.messages[key].obj).html(msg);
            }

            // store label in stack
            if (type == 'loading') {
                this.messages[key].labels.push({ id: id, msg: msg });
            }

            // add element and set timeout
            this.messages[key].elements.push(id);
            setTimeout(function () {
                ref.hide_message(id, type == 'loading');
            }, timeout);

            return id;
        }

        // create DOM object and display it
        var obj = $('<div>').addClass(type + ' content').html(msg).data('key', key),
            cont = $(this.gui_objects.message).append(obj).show();

        this.messages[key] = { obj: obj, elements: [id] };

        if (type == 'loading') {
            this.messages[key].labels = [{ id: id, msg: msg }];
        } else if (type != 'uploading') {
            obj.click(function () {
                return ref.hide_message(obj);
            })
                .attr('role', 'alert');
        }

        this.triggerEvent('message', {
            message: msg, type: type, timeout: timeout, object: obj,
        });

        if (timeout > 0) {
            setTimeout(function () {
                ref.hide_message(id, type != 'loading');
            }, timeout);
        }

        return id;
    };

    // Make a message to disappear
    this.hide_message = function (obj, fade) {
        // pass command to parent window
        if (this.is_framed()) {
            return parent.rcmail.hide_message(obj, fade);
        }

        if (!this.gui_objects.message) {
            return;
        }

        var k, n, i, o, m = this.messages;

        // Hide message by object, don't use for 'loading'!
        if (typeof obj === 'object') {
            o = $(obj);
            k = o.data('key');
            this.hide_message_object(o, fade);
            if (m[k]) {
                delete m[k];
            }
        }
        // Hide message by id
        else {
            for (k in m) {
                for (n in m[k].elements) {
                    if (m[k] && m[k].elements[n] == obj) {
                        m[k].elements.splice(n, 1);
                        // hide DOM element if last instance is removed
                        if (!m[k].elements.length) {
                            this.hide_message_object(m[k].obj, fade);
                            delete m[k];
                        }
                        // set pending action label for 'loading' message
                        else if (k == 'loading') {
                            for (i in m[k].labels) {
                                if (m[k].labels[i].id == obj) {
                                    delete m[k].labels[i];
                                } else {
                                    o = m[k].labels[i].msg;
                                    $('div.content', m[k].obj).html(o);
                                }
                            }
                        }
                    }
                }
            }
        }
    };

    // hide message object and remove from the DOM
    this.hide_message_object = function (o, fade) {
        if (fade) {
            o.fadeOut(600, function () {
                $(this).remove();
            });
        } else {
            o.hide().remove();
        }
    };

    // remove all messages immediately
    this.clear_messages = function () {
        // pass command to parent window
        if (this.is_framed()) {
            return parent.rcmail.clear_messages();
        }

        var k, n, m = this.messages;

        for (k in m) {
            for (n in m[k].elements) {
                if (m[k].obj) {
                    this.hide_message_object(m[k].obj);
                }
            }
        }

        this.messages = {};
    };

    // display uploading message with progress indicator
    // data should contain: name, total, current, percent, text
    this.display_progress = function (data) {
        if (!data || !data.name) {
            return;
        }

        var msg = this.messages['progress' + data.name];

        if (!data.label) {
            data.label = this.get_label('uploadingmany');
        }

        if (!msg) {
            if (!data.percent || data.percent < 100) {
                this.display_message(data.label, 'uploading', 0, 'progress' + data.name);
            }
            return;
        }

        if (!data.total || data.percent >= 100) {
            this.hide_message(msg.obj);
            return;
        }

        if (data.text) {
            data.label += ' ' + data.text;
        }

        msg.obj.text(data.label);
    };

    // open a jquery UI dialog with the given content
    this.show_popup_dialog = function (content, title, buttons, options) {
        // forward call to parent window
        if (this.is_framed()) {
            return parent.rcmail.show_popup_dialog(content, title, buttons, options);
        }

        var popup = $('<div class="popup">');

        if (typeof content === 'object') {
            popup.append(content);
            if ($(content).is('iframe')) {
                popup.addClass('iframe');
            }
        } else {
            popup.html(content);
        }

        // assign special classes to dialog buttons
        var i = 0, fn = function (button, classes, idx) {
            if (typeof button === 'function') {
                button = {
                    click: button,
                    text: idx,
                    class: classes,
                };
            } else {
                buttons.class = classes;
            }

            return button;
        };

        if (options && options.button_classes) {
            $.each(buttons, function (idx, button) {
                var cl = options.button_classes[i];
                if (cl) {
                    buttons[idx] = fn(button, cl, idx);
                }
                i++;
            });
        }

        options = $.extend({
            title: title,
            buttons: buttons,
            modal: true,
            resizable: true,
            width: 500,
            close: function (event, ui) {
                $(this).remove();
            },
        }, options || {});

        popup.dialog(options);

        // Remember the jQuery instance (window), useful when closing the dialog
        popup[0].jqref = $;

        if (options.width) {
            popup.width(options.width);
        }
        if (options.height) {
            popup.height(options.height);
        }

        var dialog = popup.parent();

        if (!options.noresize) {
            // resize and center popup
            var win = $(window), w = win.width(), h = win.height(),
                width = popup.width(),
                height = options.height || (popup[0].scrollHeight + 20),
                titlebar_height = $('.ui-dialog-titlebar', dialog).outerHeight() || 0,
                buttonpane_height = $('.ui-dialog-buttonpane', dialog).outerHeight() || 0,
                padding = (parseInt(dialog.css('padding-top'), 10) + parseInt(popup.css('padding-top'), 10)) * 2;

            popup.dialog('option', {
                height: Math.min(h - 40, height + titlebar_height + buttonpane_height + padding + 2),
                width: Math.min(w - 20, width + 28),
            });
        } else {
            popup.css('width', 'auto');
        }

        // Don't propagate keyboard events to the UI below the dialog (#6055)
        dialog.on('keydown keyup', function (e) {
            e.stopPropagation();
        });

        // Add Enter key handler to the input, click the 'mainaction' button
        dialog.find('input[data-submit]').on('keydown', function (e) {
            if (e.which == 13) {
                dialog.find('.ui-dialog-buttonpane button.mainaction').click();
            }
        });

        this.triggerEvent('dialog-open', { obj: popup });

        return popup;
    };

    // show_popup_dialog() wrapper for simple dialogs with action and Cancel buttons
    this.simple_dialog = function (content, title, action_func, options) {
        if (!options) {
            options = {};
        }

        var title = this.get_label(title),
            save_label = options.button || 'save',
            save_class = options.button_class || save_label.replace(/^[^\.]+\./i, ''),
            cancel_label = options.cancel_button || 'cancel',
            cancel_class = options.cancel_class || cancel_label.replace(/^[^\.]+\./i, ''),
            close_func = function (e, ui, dialog) {
                if (!dialog) {
                    dialog = this;
                }

                // The dialog might got open in the current window, but also any of its parents (#8627)
                // We have to use the jQuery object that did invoke the dialog, set in show_popup_dialog()
                dialog.jqref(dialog).dialog('close');
                if (options.cancel_func) {
                    options.cancel_func(e, ref);
                }
            },
            buttons = [{
                text: this.get_label(cancel_label),
                class: cancel_class.replace(/close/i, 'cancel'),
                click: close_func,
            }];

        if (!action_func) {
            buttons[0].class += ' mainaction';
        } else {
            buttons.unshift({
                text: this.get_label(save_label),
                class: 'mainaction ' + save_class,
                click: function (e, ui) {
                    if (action_func(e, ref)) {
                        close_func(e, ui, this);
                    }
                },
            });
        }

        return this.show_popup_dialog(content, title, buttons, options);
    };

    // show_popup_dialog() wrapper for alert() type dialogs
    this.alert_dialog = function (content, action, options) {
        options = $.extend(options || {}, {
            cancel_button: 'ok',
            cancel_class: 'save',
            cancel_func: action,
            noresize: true,
        });

        return this.simple_dialog(content, options.title || 'alerttitle', null, options);
    };

    // simple_dialog() wrapper for confirm() type dialogs
    this.confirm_dialog = function (content, button_label, action, options) {
        var action_func = function (e, ref) {
            action(e, ref); return true;
        };

        options = $.extend(options || {}, {
            button: button_label || 'continue',
            noresize: true,
        });

        return this.simple_dialog(content, options.title || 'confirmationtitle', action_func, options);
    };

    // enable/disable buttons for page shifting
    this.set_page_buttons = function () {
        this.enable_command('nextpage', 'lastpage', this.env.pagecount > this.env.current_page);
        this.enable_command('previouspage', 'firstpage', this.env.current_page > 1);

        this.update_pagejumper();
    };

    // mark a mailbox as selected and set environment variable
    this.select_folder = function (name, prefix, encode) {
        if (this.savedsearchlist) {
            this.savedsearchlist.select('');
        }

        if (this.treelist) {
            this.treelist.select(name);
        } else if (this.gui_objects.folderlist) {
            $('li.selected', this.gui_objects.folderlist).removeClass('selected');
            $(this.get_folder_li(name, prefix, encode)).addClass('selected');

            // trigger event hook
            this.triggerEvent('selectfolder', { folder: name, prefix: prefix });
        }
    };

    // adds a class to selected folder
    this.mark_folder = function (name, class_name, prefix, encode) {
        $(this.get_folder_li(name, prefix, encode)).addClass(class_name);
        this.triggerEvent('markfolder', { folder: name, mark: class_name, status: true });
    };

    // adds a class to selected folder
    this.unmark_folder = function (name, class_name, prefix, encode) {
        $(this.get_folder_li(name, prefix, encode)).removeClass(class_name);
        this.triggerEvent('markfolder', { folder: name, mark: class_name, status: false });
    };

    // helper method to find a folder list item
    this.get_folder_li = function (name, prefix, encode) {
        if (!prefix) {
            prefix = 'rcmli';
        }

        if (this.gui_objects.folderlist) {
            name = this.html_identifier(name, encode);
            return document.getElementById(prefix + name);
        }
    };

    // for reordering column array (Konqueror workaround)
    // and for setting some message list global variables
    this.set_message_coltypes = function (cols, repl, smart_col) {
        // update list mode columns list
        this.env.listcols = cols;

        // reset message list cols
        this.msglist_setup(this.env.layout);

        var list = this.message_list,
            thead = list ? list.thead : null,
            repl, cell, col, c, n, len, tr,
            listcols = this.env.msglist_cols;

        if (!this.env.coltypes) {
            this.env.coltypes = {};
        }

        // replace old column headers
        if (thead) {
            if (repl) {
                thead.innerHTML = '';
                tr = document.createElement('tr');

                for (n in listcols) {
                    c = listcols[n];
                    cell = document.createElement('th');
                    cell.innerHTML = repl[c].html || '';
                    if (repl[c].id) {
                        cell.id = repl[c].id;
                    }
                    if (repl[c].className) {
                        cell.className = repl[c].className;
                    }
                    tr.appendChild(cell);
                }

                if (list.checkbox_selection) {
                    list.insert_checkbox(tr, 'thead');
                }

                thead.appendChild(tr);
            }

            for (n = 0, len = listcols.length; n < len; n++) {
                col = listcols[list.checkbox_selection ? n - 1 : n];
                if ((cell = thead.rows[0].cells[n]) && (col == 'from' || col == 'to' || col == 'fromto')) {
                    $(cell).attr('rel', col).find('span,a').text(this.get_label(col == 'fromto' ? smart_col : col));
                }
            }
        }

        this.env.subject_col = null;
        this.env.flagged_col = null;
        this.env.status_col = null;

        if (this.env.coltypes.folder) {
            this.env.coltypes.folder.hidden = !(this.env.search_request || this.env.search_id) || this.env.search_scope == 'base';
        }

        if ((n = $.inArray('subject', listcols)) >= 0) {
            this.env.subject_col = n;
            if (list) {
                list.subject_col = n;
            }
        }
        if ((n = $.inArray('flag', listcols)) >= 0) {
            this.env.flagged_col = n;
        }
        if ((n = $.inArray('status', listcols)) >= 0) {
            this.env.status_col = n;
        }

        if (list) {
            list.hide_column('folder', (this.env.coltypes.folder && this.env.coltypes.folder.hidden) || $.inArray('folder', listcols) < 0);
            list.init_header();
        }
    };

    // replace content of row count display
    this.set_rowcount = function (text, mbox) {
        // #1487752
        if (mbox && mbox != this.env.mailbox) {
            return false;
        }

        $(this.gui_objects.countdisplay).html(text);

        // update page navigation buttons
        this.set_page_buttons();
    };

    // replace content of mailboxname display
    this.set_mailboxname = function (content) {
        if (this.gui_objects.mailboxname && content) {
            this.gui_objects.mailboxname.innerHTML = content;
        }
    };

    // replace content of quota display
    this.set_quota = function (content) {
        if (!content || !content.total) {
            return;
        }
        if (this.gui_objects.quotadisplay && content.type == 'text') {
            $(this.gui_objects.quotadisplay).text((content.percent || 0) + '%').attr('title', content.title || '');
        }

        this.triggerEvent('setquota', content);
        this.env.quota_content = content;
    };

    // update trash folder state
    this.set_trash_count = function (count) {
        this[(count ? 'un' : '') + 'mark_folder'](this.env.trash_mailbox, 'empty', '', true);
    };

    // update the mailboxlist
    this.set_unread_count = function (mbox, count, set_title, mark) {
        if (!this.gui_objects.mailboxlist) {
            return false;
        }

        this.env.unread_counts[mbox] = count;
        this.set_unread_count_display(mbox, set_title);

        if (mark) {
            this.mark_folder(mbox, mark, '', true);
        } else if (!count) {
            this.unmark_folder(mbox, 'recent', '', true);
        }

        this.mark_all_read_state();
        this.triggerEvent('set-unread-count', { mbox: mbox, count: count });
    };

    // update the mailbox count display
    this.set_unread_count_display = function (mbox, set_title) {
        var reg, link, text_obj, item, mycount, childcount, div;

        if (item = this.get_folder_li(mbox, '', true)) {
            mycount = this.env.unread_counts[mbox] ? this.env.unread_counts[mbox] : 0;
            link = $(item).children('a').eq(0);
            text_obj = link.children('span.unreadcount');
            if (!text_obj.length && mycount) {
                text_obj = $('<span>').addClass('unreadcount skip-content').appendTo(link);
            }
            reg = /\s+\([0-9]+\)$/i;

            childcount = 0;
            if ((div = item.getElementsByTagName('div')[0])
                && div.className.match(/collapsed/)
            ) {
                // add children's counters
                for (var k in this.env.unread_counts) {
                    if (k.startsWith(mbox + this.env.delimiter)) {
                        childcount += this.env.unread_counts[k];
                    }
                }
            }

            if (mycount && text_obj.length) {
                text_obj.html(this.env.unreadwrap.replace(/%[sd]/, mycount));
            } else if (text_obj.length) {
                text_obj.remove();
            }

            // set parent's display
            reg = new RegExp(RegExp.escape(this.env.delimiter) + '[^' + RegExp.escape(this.env.delimiter) + ']+$');
            if (mbox.match(reg)) {
                this.set_unread_count_display(mbox.replace(reg, ''), false);
            }

            // set the right classes
            if ((mycount + childcount) > 0) {
                $(item).addClass('unread');
            } else {
                $(item).removeClass('unread');
            }
        }

        // set unread count to window title
        reg = /^\([0-9]+\)\s+/i;
        if (set_title && document.title) {
            var new_title = '',
                doc_title = String(document.title);

            if (mycount && doc_title.match(reg)) {
                new_title = doc_title.replace(reg, '(' + mycount + ') ');
            } else if (mycount) {
                new_title = '(' + mycount + ') ' + doc_title;
            } else {
                new_title = doc_title.replace(reg, '');
            }

            this.set_pagetitle(new_title);
        }
    };

    // display fetched raw headers
    this.set_headers = function (content) {
        if (this.gui_objects.all_headers_box && content) {
            $(this.gui_objects.all_headers_box).html(content).show();
        }
    };

    // display all-headers row and fetch raw message headers
    this.show_headers = function (props, elem) {
        if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box || !this.env.uid) {
            return;
        }

        $(elem).removeClass('show-headers').addClass('hide-headers');
        $(this.gui_objects.all_headers_row).show();
        elem.onclick = function () {
            ref.command('hide-headers', '', elem);
        };

        // fetch headers only once
        if (!this.gui_objects.all_headers_box.innerHTML) {
            this.http_request('headers', { _uid: this.env.uid, _mbox: this.env.mailbox },
                this.display_message('', 'loading')
            );
        }
    };

    // hide all-headers row
    this.hide_headers = function (props, elem) {
        if (!this.gui_objects.all_headers_row || !this.gui_objects.all_headers_box) {
            return;
        }

        $(elem).removeClass('hide-headers').addClass('show-headers');
        $(this.gui_objects.all_headers_row).hide();
        elem.onclick = function () {
            ref.command('show-headers', '', elem);
        };
    };

    // create folder selector popup
    this.folder_selector = function (event, callback) {
        this.entity_selector('folder-selector', callback, this.env.mailboxes_list, function (obj, a) {
            var folder = ref.env.mailboxes[obj],
                id = folder.id,
                depth = folder.level || 0,
                row = $('<li>');

            if (folder.virtual) {
                a.addClass('virtual').attr({ 'aria-disabled': 'true', tabindex: '-1' });
            } else {
                a.addClass('active').data('id', folder.id);
            }

            if (folder.class) {
                row.addClass(folder.class);
            }

            // set indentation level
            a.css('padding-left', depth ? (depth * 16) + 'px' : 0);

            // add folder name element
            a.append($('<span>').text(folder.name));

            return row.append(a);
        }, event);
    };

    // create addressbook selector popup
    this.addressbook_selector = function (event, callback) {
        // build addressbook + groups list
        var combined_sources = [];

        // check we really need it before processing
        if (!this.entity_selectors['addressbook-selector']) {
            $.each(this.env.address_sources, function () {
                if (!this.readonly) {
                    var source = this;
                    combined_sources.push(source);

                    $.each(ref.env.contactgroups, function () {
                        if (source.id === this.source) {
                            combined_sources.push(this);
                        }
                    });
                }
            });
        }

        this.entity_selector('addressbook-selector', callback, combined_sources, function (obj, a) {
            if (obj.type == 'group') {
                a.attr('rel', obj.source + ':' + obj.id)
                    .addClass('contactgroup active')
                    .data({ source: obj.source, gid: obj.id, id: obj.source + ':' + obj.id })
                    .css('padding-left', '16px');
            } else {
                a.addClass('addressbook active').data('id', obj.id);
            }
            a.append($('<span>').text(obj.name));

            return $('<li>').append(a);
        }, event);
    };

    // create contactgroup selector popup
    this.contactgroup_selector = function (event, callback) {
        this.entity_selector('contactgroup-selector', callback, this.env.contactgroups, function (obj, a) {
            if (ref.env.source === obj.source) {
                a.addClass('contactgroup active')
                    .data({ id: obj.id })
                    .append($('<span>').text(obj.name));

                return $('<li>').append(a);
            }
        }, event);
    };

    // create selector popup (eg for folders or address books), position and display it
    this.entity_selector = function (name, click_callback, entity_list, list_callback, event) {
        var container = this.entity_selectors[name];

        if (!container) {
            var rows = [],
                container = $('<div>').attr('id', name).addClass('popupmenu'),
                ul = $('<ul>').addClass('toolbarmenu menu'),
                link = document.createElement('a');

            link.href = '#';
            link.className = 'icon';

            // loop over entity list
            $.each(entity_list, function (i) {
                var a = $(link.cloneNode(false)).attr('rel', this.id);
                rows.push(list_callback(this, a, i));
            });

            ul.append(rows).appendTo(container);

            // temporarily show element to calculate its size
            container.css({ left: '-1000px', top: '-1000px' })
                .appendTo(document.body).show();

            // set max-height if the list is long
            if (rows.length > 10) {
                container.css('max-height', $('li', container)[0].offsetHeight * 10 + 9);
            }

            // register delegate event handler for folder item clicks
            container.on('click', 'a.active', function (e) {
                container.data('callback')($(this).data('id'), this);
            });

            this.entity_selectors[name] = container;
        }

        container.data('callback', click_callback);

        // position menu on the screen
        this.show_menu(name, true, event);
    };

    this.destroy_entity_selector = function (name) {
        $('#' + name).remove();
        delete this.entity_selectors[name];
        this.triggerEvent('destroy-entity-selector', { name: name });
    };

    /***********************************************/
    /*             popup menu functions            */
    /***********************************************/

    // Show/hide a specific popup menu
    this.show_menu = function (prop, show, event) {
        var name = typeof prop === 'object' ? prop.menu : prop,
            obj = $('#' + name),
            ref = event && event.target ? $(event.target) : $(obj.attr('rel') || '#' + name + 'link'),
            keyboard = rcube_event.is_keyboard(event),
            align = obj.attr('data-align') || '',
            stack = false;

        // find "real" button element
        if (ref.get(0).tagName != 'A' && ref.closest('a').length) {
            ref = ref.closest('a');
        }

        if (typeof prop === 'string') {
            prop = { menu: name };
        }

        // let plugins or skins provide the menu element
        if (!obj.length) {
            obj = this.triggerEvent('menu-get', { name: name, props: prop, originalEvent: event });
        }

        if (!obj || !obj.length) {
            // just delegate the action to subscribers
            return this.triggerEvent(show === false ? 'menu-close' : 'menu-open', { name: name, props: prop, originalEvent: event });
        }

        // move element to top for proper absolute positioning
        obj.appendTo(document.body);

        if (typeof show === 'undefined') {
            show = obj.is(':visible') ? false : true;
        }

        if (show && ref.length) {
            var win = $(window),
                pos = ref.offset(),
                above = align.indexOf('bottom') >= 0;

            stack = ref.attr('role') == 'menuitem' || ref.closest('[role=menuitem]').length > 0;

            ref.offsetWidth = ref.outerWidth();
            ref.offsetHeight = ref.outerHeight();
            if (!above && pos.top + ref.offsetHeight + obj.height() > win.height()) {
                above = true;
            }
            if (align.indexOf('right') >= 0) {
                pos.left = pos.left + ref.outerWidth() - obj.width();
            } else if (stack) {
                pos.left = pos.left + ref.offsetWidth - 5;
                pos.top -= ref.offsetHeight;
            }
            if (pos.left + obj.width() > win.width()) {
                pos.left = win.width() - obj.width() - 12;
            }
            pos.top = Math.max(0, pos.top + (above ? -obj.height() : ref.offsetHeight));
            obj.css({ left: pos.left + 'px', top: pos.top + 'px' });
        }

        // add menu to stack
        if (show) {
            // truncate stack down to the one containing the ref link
            for (var i = this.menu_stack.length - 1; stack && i >= 0; i--) {
                if (!$(ref).parents('#' + this.menu_stack[i]).length && $(event.target).parent().attr('role') != 'menuitem') {
                    this.hide_menu(this.menu_stack[i], event);
                }
            }
            if (stack && this.menu_stack.length) {
                obj.data('parent', $.last(this.menu_stack));
                obj.css('z-index', ($('#' + $.last(this.menu_stack)).css('z-index') || 0) + 1);
            } else if (!stack && this.menu_stack.length) {
                this.hide_menu(this.menu_stack[0], event);
            }

            obj.show().attr('aria-hidden', 'false').data('opener', ref.attr('aria-expanded', 'true').get(0));
            this.triggerEvent('menu-open', {
                name: name, obj: obj, props: prop, originalEvent: event,
            });
            this.menu_stack.push(name);

            this.menu_keyboard_active = show && keyboard;
            if (this.menu_keyboard_active) {
                this.focused_menu = name;
                obj.find('a,input:not(:disabled)').not('[aria-disabled=true]').first().focus();
            }
        } else { // close menu
            this.hide_menu(name, event);
        }

        return show;
    };

    // hide the given popup menu (and its children)
    this.hide_menu = function (name, event) {
        if (!this.menu_stack.length) {
            // delegate to subscribers
            this.triggerEvent('menu-close', { name: name, props: { menu: name }, originalEvent: event });
            return;
        }

        var obj, keyboard = rcube_event.is_keyboard(event);
        for (var j = this.menu_stack.length - 1; j >= 0; j--) {
            obj = $('#' + this.menu_stack[j]).hide().attr('aria-hidden', 'true').data('parent', false);
            this.triggerEvent('menu-close', {
                name: this.menu_stack[j], obj: obj, props: { menu: this.menu_stack[j] }, originalEvent: event,
            });
            if (this.menu_stack[j] == name) {
                j = -1; // stop loop
                if (obj.data('opener')) {
                    $(obj.data('opener')).attr('aria-expanded', 'false');
                    if (keyboard) {
                        obj.data('opener').focus();
                    }
                }
            }
            this.menu_stack.pop();
        }

        // focus previous menu in stack
        if (this.menu_stack.length && keyboard) {
            this.menu_keyboard_active = true;
            this.focused_menu = $.last(this.menu_stack);
            if (!obj || !obj.data('opener')) {
                $('#' + this.focused_menu).find('a,input:not(:disabled)').not('[aria-disabled=true]').first().focus();
            }
        } else {
            this.focused_menu = null;
            this.menu_keyboard_active = false;
        }
    };

    // position a menu element on the screen in relation to other object
    this.element_position = function (element, obj) {
        var obj = $(obj), win = $(window),
            width = obj.outerWidth(),
            height = obj.outerHeight(),
            menu_pos = obj.data('menu-pos'),
            win_height = win.height(),
            elem_height = $(element).height(),
            elem_width = $(element).width(),
            pos = obj.offset(),
            top = pos.top,
            left = pos.left + width;

        if (menu_pos == 'bottom') {
            top += height;
            left -= width;
        } else {
            left -= 5;
        }

        if (top + elem_height > win_height) {
            top -= elem_height - height;
            if (top < 0) {
                top = Math.max(0, (win_height - elem_height) / 2);
            }
        }

        if (left + elem_width > win.width()) {
            left -= elem_width + width;
        }

        element.css({ left: left + 'px', top: top + 'px' });
    };

    // initialize HTML editor
    this.editor_init = function (config, id) {
        this.editor = new rcube_text_editor(config || this.env.editor_config, id);
        this.editor.init();
    };


    /********************************************************/
    /*           html to text conversion functions          */
    /********************************************************/

    this.html2plain = function (html, func) {
        return this.format_converter(html, 'html', func);
    };

    this.plain2html = function (plain, func) {
        return this.format_converter(plain, 'plain', func);
    };

    this.format_converter = function (text, format, func) {
        // warn the user (if converted content is not empty)
        if (!text
            || (format == 'html' && !(text.replace(/<[^>]+>|&nbsp;|\u00C2\u00A0|\s/g, '')).length)
            || (format != 'html' && !(text.replace(/\u00C2\u00A0|\s/g, '')).length)
        ) {
            // without setTimeout() here, textarea is filled with initial (onload) content
            if (func) {
                setTimeout(function () {
                    func('');
                }, 50);
            }
            return true;
        }

        var confirmed = this.env.editor_warned || confirm(this.get_label('editorwarning'));

        this.env.editor_warned = true;

        if (!confirmed) {
            return false;
        }

        var url = '?_task=utils&_action=' + (format == 'html' ? 'html2text' : 'text2html'),
            lock = this.set_busy(true, 'converting');

        $.ajax({
            type: 'POST',
            url: url,
            data: text,
            contentType: 'application/octet-stream',
            error: function (o, status, err) {
                ref.http_error(o, status, err, lock);
            },
            success: function (data) {
                ref.set_busy(false, null, lock);
                if (func) {
                    func(data);
                }
            },
        });

        return true;
    };


    /********************************************************/
    /*                 Remote request methods               */
    /********************************************************/

    // compose a valid url with the given parameters
    this.url = function (action, query) {
        var querystring = typeof query === 'string' ? query : '';

        if (typeof action !== 'string') {
            query = action;
        } else if (!query || typeof query !== 'object') {
            query = {};
        }

        if (action) {
            query._action = action;
        } else if (this.env.action) {
            query._action = this.env.action;
        }

        var url = this.env.comm_path, k, param = {};

        // overwrite task name
        if (action && action.match(/([a-z0-9_-]+)\/([a-z0-9-_.]+)/)) {
            query._action = RegExp.$2;
            url = url.replace(/\_task=[a-z0-9_-]+/, '_task=' + RegExp.$1);
        }

        // force _framed=0
        if (query._framed === 0) {
            url = url.replace('&_framed=1', '');
            query._framed = null;
        }

        // remove undefined values
        for (k in query) {
            if (query[k] !== undefined && query[k] !== null) {
                param[k] = query[k];
            }
        }

        if (param = $.param(param)) {
            url += (url.indexOf('?') > -1 ? '&' : '?') + param;
        }

        if (querystring) {
            url += (url.indexOf('?') > -1 ? '&' : '?') + querystring;
        }

        return url;
    };

    this.redirect = function (url, lock) {
        if (lock !== false) {
            this.set_busy(true, 'loading');
        }

        if (this.is_framed()) {
            url = url.replace(/&_framed=1/, '');
            parent.rcmail.redirect(url, lock);
        } else {
            if (this.env.extwin) {
                if (typeof url === 'string') {
                    url += (url.indexOf('?') < 0 ? '?' : '&') + '_extwin=1';
                } else {
                    url._extwin = 1;
                }
            }
            this.location_href(url, window);
        }
    };

    this.goto_url = function (action, query, lock, secure) {
        var url = this.url(action, query);
        if (secure) {
            url = this.secure_url(url);
        }
        this.redirect(url, lock);
    };

    this.location_href = function (url, target, frame) {
        if (frame) {
            this.lock_frame(target);
        }

        if (typeof url === 'object') {
            url = this.env.comm_path + '&' + $.param(url);
        }

        // simulate real link click to force IE to send referer header
        if (bw.ie && target == window) {
            $('<a>').attr('href', url).appendTo(document.body).get(0).click();
        } else {
            target.location.href = url;
        }

        // reset keep-alive interval
        this.start_keepalive();
    };

    // update browser location to remember current view
    this.update_state = function (query) {
        if (window.history.replaceState) {
            try {
                // This may throw security exception in Firefox (#5400)
                window.history.replaceState({}, document.title, rcmail.url('', query));
            } catch (e) {
                // ignore
            }
        }
    };

    // send a http request to the server
    this.http_request = function (action, data, lock, type) {
        if (type != 'POST') {
            type = 'GET';
        }

        if (typeof data !== 'object') {
            data = rcube_parse_query(data);
        }

        data._remote = 1;
        data._unlock = lock ? lock : 0;

        // trigger plugin hook
        var result = this.triggerEvent('request' + action, data);

        // abort if one of the handlers returned false
        if (result === false) {
            if (data._unlock) {
                this.set_busy(false, null, data._unlock);
            }
            return false;
        }

        if (result && result.getResponseHeader) {
            return result;
        }

        if (result !== undefined) {
            data = result;
            if (data._action) {
                action = data._action;
                delete data._action;
            }
        }

        var url = this.url(action);

        // reset keep-alive interval
        this.start_keepalive();

        // send request
        return $.ajax({
            type: type,
            url: url,
            data: data,
            dataType: 'json',
            success: function (data) {
                ref.http_response(data);
            },
            error: function (o, status, err) {
                ref.http_error(o, status, err, lock, action);
            },
        });
    };

    // send a http GET request to the server
    this.http_get = this.http_request;

    // send a http POST request to the server
    this.http_post = function (action, data, lock) {
        return this.http_request(action, data, lock, 'POST');
    };

    // aborts ajax request
    this.abort_request = function (r) {
        if (r.request) {
            r.request.abort();
        }
        if (r.lock) {
            this.set_busy(false, null, r.lock);
        }
    };

    // handle HTTP response
    this.http_response = function (response) {
        if (!response) {
            return;
        }

        if (response.unlock) {
            this.set_busy(false, null, response.unlock);
        }

        this.triggerEvent('responsebefore', { response: response });
        this.triggerEvent('responsebefore' + response.action, { response: response });

        // set env vars
        if (response.env) {
            this.set_env(response.env);
        }

        var i;

        // we have labels to add
        if (typeof response.texts === 'object') {
            for (i in response.texts) {
                if (typeof response.texts[i] === 'string') {
                    this.add_label(i, response.texts[i]);
                }
            }
        }

        // if we get javascript code from server -> execute it
        if (response.exec) {
            eval(response.exec);
        }

        // execute callback functions of plugins
        if (response.callbacks && response.callbacks.length) {
            for (i = 0; i < response.callbacks.length; i++) {
                this.triggerEvent(response.callbacks[i][0], response.callbacks[i][1]);
            }
        }

        // process the response data according to the sent action
        switch (response.action) {
            case 'mark':
                // Mark the message as Seen also in the opener/parent
                if ((this.env.action == 'show' || this.env.action == 'preview') && this.env.last_flag == 'SEEN') {
                    this.set_unread_message(this.env.uid, this.env.mailbox);
                }

                break;
            case 'delete':
                if (this.task == 'addressbook') {
                    var sid, uid = this.contact_list.get_selection(), writable = false;

                    if (uid && this.contact_list.rows[uid]) {
                        // search results, get source ID from record ID
                        if (this.env.source == '') {
                            sid = String(uid).replace(/^[^-]+-/, '');
                            writable = sid && this.env.address_sources[sid] && !this.env.address_sources[sid].readonly;
                        } else {
                            writable = !this.env.address_sources[this.env.source].readonly;
                        }
                    }
                    this.enable_command('delete', 'edit', writable);
                    this.enable_command('export', (this.contact_list && this.contact_list.rowcount > 0));
                    this.enable_command('export-selected', 'print', false);
                }

            case 'move':
                if (this.env.action == 'show') {
                    // re-enable commands on move/delete error
                    this.enable_command(this.env.message_commands, true);
                    if (!this.env.list_post) {
                        this.enable_command('reply-list', false);
                    }
                } else if (this.task == 'addressbook') {
                    this.triggerEvent('listupdate', { list: this.contact_list, folder: this.env.source, rowcount: this.contact_list.rowcount });
                }

            case 'purge':
            case 'expunge':
                if (this.task == 'mail') {
                    if (!this.env.exists) {
                        // clear preview pane content
                        if (this.env.contentframe) {
                            this.show_contentframe(false);
                        }
                        // disable commands useless when mailbox is empty
                        this.enable_command(this.env.message_commands, 'purge', 'expunge',
                            'select-all', 'select-none', 'expand-all', 'expand-unread', 'collapse-all', false);
                    }
                    if (this.message_list) {
                        this.triggerEvent('listupdate', { list: this.message_list, folder: this.env.mailbox, rowcount: this.message_list.rowcount });
                    }
                }

                break;
            case 'refresh':
            case 'check-recent':
                // update message flags
                $.each(this.env.recent_flags || {}, function (uid, flags) {
                    ref.set_message(uid, 'deleted', flags.deleted);
                    ref.set_message(uid, 'replied', flags.answered);
                    ref.set_message(uid, 'unread', !flags.seen);
                    ref.set_message(uid, 'forwarded', flags.forwarded);
                    ref.set_message(uid, 'flagged', flags.flagged);
                });
                delete this.env.recent_flags;

            case 'getunread':
            case 'search':
                this.env.qsearch = null;
            case 'list':
                if (this.task == 'mail') {
                    var is_multifolder = this.is_multifolder_listing(),
                        list = this.message_list,
                        uid = this.env.list_uid;

                    this.enable_command('show', 'select-all', 'select-none', this.env.messagecount > 0);
                    this.enable_command('expunge', 'purge', this.env.exists && !is_multifolder);
                    this.enable_command('import-messages', !is_multifolder);
                    this.enable_command('expand-all', 'expand-unread', 'collapse-all', this.env.threading && this.env.messagecount && !is_multifolder);

                    if (list) {
                        if (response.action == 'list' || response.action == 'search') {
                            // highlight message row when we're back from message page
                            if (uid) {
                                if (uid === 'FIRST') {
                                    uid = list.get_first_row();
                                } else if (uid === 'LAST') {
                                    uid = list.get_last_row();
                                } else if (!list.rows[uid]) {
                                    uid += '-' + this.env.mailbox;
                                }

                                if (uid && list.rows[uid]) {
                                    list.select(uid);
                                }

                                delete this.env.list_uid;
                            }

                            this.enable_command('set-listmode', this.env.threads && !is_multifolder);
                            if (list.rowcount > 0 && !$(document.activeElement).is('input,textarea')) {
                                list.focus();
                            }

                            // trigger 'select' so all dependent actions update its state
                            // e.g. plugins use this event to activate buttons (#1490647)
                            list.triggerEvent('select');
                        }

                        if (response.action != 'getunread') {
                            this.triggerEvent('listupdate', { list: list, folder: this.env.mailbox, rowcount: list.rowcount });
                        }
                    }
                } else if (this.task == 'addressbook') {
                    var list = this.contact_list,
                        uid = this.env.list_uid;

                    this.enable_command('export', 'select-all', 'select-none', (list && list.rowcount > 0));

                    if (response.action == 'list' || response.action == 'search') {
                        this.enable_command('search-create', this.env.source == '');
                        this.enable_command('search-delete', this.env.search_id);
                        this.update_group_commands();

                        if (list && uid) {
                            if (uid === 'FIRST') {
                                uid = list.get_first_row();
                            } else if (uid === 'LAST') {
                                uid = list.get_last_row();
                            }

                            if (uid && list.rows[uid]) {
                                list.select(uid);
                            }

                            delete this.env.list_uid;

                            // trigger 'select' so all dependent actions update its state
                            list.triggerEvent('select');
                        }

                        if (list.rowcount > 0 && !$(document.activeElement).is('input,textarea')) {
                            list.focus();
                        }

                        this.triggerEvent('listupdate', { list: list, folder: this.env.source, rowcount: list.rowcount });
                    }
                }

                break;
            case 'list-contacts':
            case 'search-contacts':
                if (this.contact_list) {
                    if (this.contact_list.rowcount > 0) {
                        this.contact_list.focus();
                    }
                    this.triggerEvent('listupdate', { list: this.contact_list, rowcount: this.contact_list.rowcount });
                }

                break;
        }

        if (response.unlock) {
            this.hide_message(response.unlock);
        }

        this.triggerEvent('responseafter', { response: response });
        this.triggerEvent('responseafter' + response.action, { response: response });

        // reset keep-alive interval
        this.start_keepalive();
    };

    // handle HTTP request errors
    this.http_error = function (request, status, err, lock, action) {
        var errmsg = request.statusText;

        this.set_busy(false, null, lock);
        request.abort();

        // don't display error message on page unload (#1488547)
        if (this.unload) {
            return;
        }

        if (request.status && errmsg) {
            this.display_message(this.get_label('servererror') + ' (' + errmsg + ')', 'error');
        } else if (status == 'timeout') {
            this.display_message('requesttimedout', 'error');
        } else if (request.status == 0 && status != 'abort') {
            this.display_message('connerror', 'error');
        }

        // redirect to url specified in location header if not empty
        var location_url = request.getResponseHeader('Location');
        if (location_url && this.env.action != 'compose') { // don't redirect on compose screen, contents might get lost (#1488926)
            this.redirect(location_url);
        }

        // 403 Forbidden response (CSRF prevention) - reload the page.
        // In case there's a new valid session it will be used, otherwise
        // login form will be presented (#1488960).
        if (request.status == 403) {
            (this.is_framed() ? parent : window).location.reload();
            return;
        }

        // re-send keep-alive requests after 30 seconds
        if (action == 'keep-alive') {
            setTimeout(function () {
                ref.keep_alive(); ref.start_keepalive();
            }, 30000);
        }
    };

    // handler for session errors detected on the server
    this.session_error = function (redirect_url) {
        this.env.server_error = 401;

        // save message in local storage and do not redirect
        if (this.env.action == 'compose') {
            this.save_compose_form_local();
            this.compose_skip_unsavedcheck = true;
            // stop keep-alive and refresh processes
            this.env.session_lifetime = 0;
            if (this._keepalive) {
                clearInterval(this._keepalive);
            }
            if (this._refresh) {
                clearInterval(this._refresh);
            }
        } else if (redirect_url) {
            setTimeout(function () {
                ref.redirect(redirect_url, true);
            }, 2000);
        }
    };

    // callback when an iframe finished loading
    this.iframe_loaded = function (unlock) {
        if (!unlock) {
            unlock = this.env.frame_lock;
        }

        this.set_busy(false, null, unlock);

        if (this.submit_timer) {
            clearTimeout(this.submit_timer);
        }
    };

    /**
     Send multi-threaded parallel HTTP requests to the server for a list if items.
     The string '%' in either a GET query or POST parameters will be replaced with the respective item value.
     This is the argument object expected: {
         items: ['foo','bar','gna'],      // list of items to send requests for
         action: 'task/some-action',      // Roundcube action to call
         query: { q:'%s' },               // GET query parameters
         postdata: { source:'%s' },       // POST data (sends a POST request if present)
         threads: 3,                      // max. number of concurrent requests
         onresponse: function(data){ },   // Callback function called for every response received from server
         whendone: function(alldata){ }   // Callback function called when all requests have been sent
     }
     */
    this.multi_thread_http_request = function (prop) {
        var i, item, reqid = new Date().getTime(),
            threads = prop.threads || 1;

        prop.reqid = reqid;
        prop.running = 0;
        prop.requests = [];
        prop.result = [];
        prop._items = $.extend([], prop.items); // copy items

        if (!prop.lock) {
            prop.lock = this.display_message('', 'loading');
        }

        // add the request arguments to the jobs pool
        this.http_request_jobs[reqid] = prop;

        // start n threads
        for (i = 0; i < threads; i++) {
            item = prop._items.shift();
            if (item === undefined) {
                break;
            }

            prop.running++;
            prop.requests.push(this.multi_thread_send_request(prop, item));
        }

        return reqid;
    };

    // helper method to send an HTTP request with the given iterator value
    this.multi_thread_send_request = function (prop, item) {
        var k, postdata, query;

        // replace %s in post data
        if (prop.postdata) {
            postdata = {};
            for (k in prop.postdata) {
                postdata[k] = String(prop.postdata[k]).replace('%s', item);
            }
            postdata._reqid = prop.reqid;
        }
        // replace %s in query
        else if (typeof prop.query === 'string') {
            query = prop.query.replace('%s', item);
            query += '&_reqid=' + prop.reqid;
        } else if (typeof prop.query === 'object' && prop.query) {
            query = {};
            for (k in prop.query) {
                query[k] = String(prop.query[k]).replace('%s', item);
            }
            query._reqid = prop.reqid;
        }

        // send HTTP GET or POST request
        return postdata ? this.http_post(prop.action, postdata) : this.http_request(prop.action, query);
    };

    // callback function for multi-threaded http responses
    this.multi_thread_http_response = function (data, reqid) {
        var prop = this.http_request_jobs[reqid];
        if (!prop || prop.running <= 0 || prop.cancelled) {
            return;
        }

        prop.running--;

        // trigger response callback
        if (prop.onresponse && typeof prop.onresponse === 'function') {
            prop.onresponse(data);
        }

        prop.result = $.extend(prop.result, data);

        // send next request if prop.items is not yet empty
        var item = prop._items.shift();
        if (item !== undefined) {
            prop.running++;
            prop.requests.push(this.multi_thread_send_request(prop, item));
        }
        // trigger whendone callback and mark this request as done
        else if (prop.running == 0) {
            if (prop.whendone && typeof prop.whendone === 'function') {
                prop.whendone(prop.result);
            }

            this.set_busy(false, '', prop.lock);

            // remove from this.http_request_jobs pool
            delete this.http_request_jobs[reqid];
        }
    };

    // abort a running multi-thread request with the given identifier
    this.multi_thread_request_abort = function (reqid) {
        var prop = this.http_request_jobs[reqid];
        if (prop) {
            for (var i = 0; prop.running > 0 && i < prop.requests.length; i++) {
                if (prop.requests[i].abort) {
                    prop.requests[i].abort();
                }
            }

            prop.running = 0;
            prop.cancelled = true;
            this.set_busy(false, '', prop.lock);
        }
    };

    // post the given form to a hidden iframe
    this.async_upload_form = function (form, action, onload) {
    // create hidden iframe
        var ts = new Date().getTime(),
            frame_name = 'rcmupload' + ts,
            frame = this.dummy_iframe(frame_name);

        // handle upload errors by parsing iframe content in onload
        frame.on('load', { ts: ts }, onload);

        $(form).attr({
            target: frame_name,
            action: this.url(action, { _id: this.env.compose_id || '', _uploadid: ts, _from: this.env.action }),
            method: 'POST',
            enctype: 'multipart/form-data',
        })
            .submit();

        return frame_name;
    };

    // create hidden iframe element
    this.dummy_iframe = function (name, src) {
        return $('<iframe>').attr({
            name: name,
            src: src,
            style: 'width:0;height:0;visibility:hidden',
            'aria-hidden': 'true',
        })
            .appendTo(document.body);
    };

    // html5 file-drop API
    this.document_drag_hover = function (e, over) {
        // don't e.preventDefault() here to not block text dragging on the page (#1490619)
        $(this.gui_objects.filedrop)[(over ? 'addClass' : 'removeClass')]('active');
    };

    this.file_drag_hover = function (e, over) {
        e.preventDefault();
        e.stopPropagation();
        $(this.gui_objects.filedrop)[(over ? 'addClass' : 'removeClass')]('hover');
    };

    // handler when files are dropped to a designated area.
    // compose a multipart form data and submit it to the server
    this.file_dropped = function (e) {
        // abort event and reset UI
        this.file_drag_hover(e, false);

        // prepare multipart form data composition
        var uri,
            files = e.target.files || e.dataTransfer.files,
            args = { _id: this.env.compose_id || this.env.cid || '', _remote: 1, _from: this.env.action };

        if (!files || !files.length) {
            // Roundcube attachment, pass its uri to the backend and attach
            if (uri = e.dataTransfer.getData('roundcube-uri')) {
                var ts = 'upload' + new Date().getTime(),
                    // jQuery way to escape filename (#1490530)
                    content = $('<span>').text(e.dataTransfer.getData('roundcube-name') || this.get_label('attaching')).html();

                args._uri = uri;
                args._uploadid = ts;

                // add to attachments list
                if (!this.add2attachment_list(ts, {
                    name: '', html: content, classname: 'uploading', complete: false,
                })) {
                    this.file_upload_id = this.set_busy(true, 'attaching');
                }

                this.http_post(this.env.filedrop.action || 'upload', args);
            }

            return;
        }

        this.file_upload(files, args, {
            name: (this.env.filedrop.fieldname || '_file') + (this.env.filedrop.single ? '' : '[]'),
            single: this.env.filedrop.single,
            filter: this.env.filedrop.filter,
            action: ref.env.filedrop.action,
        });
    };

    // Files upload using ajax
    this.file_upload = function (files, post_args, props) {
        if (!window.FormData || !files || !files.length) {
            return false;
        }

        var f, i, fname, size = 0, numfiles = 0,
            formdata = new FormData(),
            fieldname = props.name || '_file[]',
            limit = props.single ? 1 : files.length;
        args = $.extend({ _remote: 1, _from: this.env.action }, post_args || {});

        // add files to form data
        for (i = 0; numfiles < limit && (f = files[i]); i++) {
            // filter by file type if requested
            if (props.filter && !f.type.match(new RegExp(props.filter))) {
                // TODO: show message to user
                continue;
            }

            formdata.append(fieldname, f);
            size += f.size;
            fname = f.name;
            numfiles++;
        }

        if (numfiles) {
            if (this.env.max_filesize && this.env.filesizeerror && size > this.env.max_filesize) {
                this.display_message(this.env.filesizeerror, 'error');
                return false;
            }

            if (this.env.max_filecount && this.env.filecounterror && numfiles > this.env.max_filecount) {
                this.display_message(this.env.filecounterror, 'error');
                return false;
            }

            var ts = 'upload' + new Date().getTime(),
                label = numfiles > 1 ? this.get_label('uploadingmany') : fname,
                // jQuery way to escape filename (#1490530)
                content = $('<span>').text(label).html();

            // add to attachments list
            if (!this.add2attachment_list(ts, {
                name: '', html: content, classname: 'uploading', complete: false,
            }) && !props.lock) {
                props.lock = this.file_upload_id = this.set_busy(true, 'uploading');
            }

            args._uploadid = ts;
            args._unlock = props.lock;

            this.uploads[ts] = $.ajax({
                type: 'POST',
                dataType: 'json',
                url: this.url(props.action || 'upload', args),
                contentType: false,
                processData: false,
                timeout: this.uploadTimeout, // ajax call timeout for loading attachment
                data: formdata,
                headers: { 'X-Roundcube-Request': this.env.request_token },
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload && ref.labels.uploadprogress) {
                        xhr.upload.onprogress = function (e) {
                            var msg = ref.file_upload_msg(e.loaded, e.total);
                            if (msg) {
                                $('#' + ts).find('.uploading').text(msg);
                            }
                        };
                    }
                    return xhr;
                },
                success: function (data) {
                    delete ref.uploads[ts];
                    ref.http_response(data);
                },
                error: function (o, status, err) {
                    delete ref.uploads[ts];
                    ref.remove_from_attachment_list(ts);
                    ref.http_error(o, status, err, props.lock, 'attachment');
                },
            });
        }

        return true;
    };

    this.file_upload_msg = function (current, total) {
        if (total && current < total) {
            var percent = Math.round(current / total * 100),
                label = ref.get_label('uploadprogress');

            if (total >= 1073741824) {
                total = parseFloat(total / 1073741824).toFixed(1) + ' ' + this.get_label('GB');
                current = parseFloat(current / 1073741824).toFixed(1);
            } else if (total >= 1048576) {
                total = parseFloat(total / 1048576).toFixed(1) + ' ' + this.get_label('MB');
                current = parseFloat(current / 1048576).toFixed(1);
            } else if (total >= 1024) {
                total = parseInt(total / 1024, 10) + ' ' + this.get_label('KB');
                current = parseInt(current / 1024, 10);
            } else {
                total = total + ' ' + this.get_label('B');
            }

            return label.replace('$percent', percent + '%').replace('$current', current).replace('$total', total);
        }
    };

    // starts interval for keep-alive signal
    this.start_keepalive = function () {
        if (!this.env.session_lifetime || this.env.framed || this.env.extwin || this.task == 'login' || this.env.action == 'print') {
            return;
        }

        if (this._keepalive) {
            clearInterval(this._keepalive);
        }

        // use Math to prevent from an integer overflow (#5273)
        // maximum interval is 15 minutes, minimum is 30 seconds
        var interval = Math.min(1800, this.env.session_lifetime) * 0.5 * 1000;
        this._keepalive = setInterval(function () {
            ref.keep_alive();
        }, interval < 30000 ? 30000 : interval);
    };

    // starts interval for refresh signal
    this.start_refresh = function () {
        if (!this.env.refresh_interval || this.env.framed || this.env.extwin || this.task == 'login' || this.env.action == 'print') {
            return;
        }

        if (this._refresh) {
            clearInterval(this._refresh);
        }

        this._refresh = setInterval(function () {
            ref.refresh();
        }, this.env.refresh_interval * 1000);
    };

    // sends keep-alive signal
    this.keep_alive = function () {
        if (!this.busy) {
            this.http_request('keep-alive');
        }
    };

    // sends refresh signal
    this.refresh = function () {
        if (this.busy) {
            // try again after 10 seconds
            setTimeout(function () {
                ref.refresh(); ref.start_refresh();
            }, 10000);
            return;
        }

        var params = {}, lock = this.set_busy(true, 'refreshing');

        if (this.task == 'mail' && this.gui_objects.mailboxlist) {
            params = this.check_recent_params();
        }

        params._last = Math.floor(this.env.lastrefresh.getTime() / 1000);
        this.env.lastrefresh = new Date();

        // plugins should bind to 'requestrefresh' event to add own params
        this.http_post('refresh', params, lock);
    };

    // returns check-recent request parameters
    this.check_recent_params = function () {
        var params = { _mbox: this.env.mailbox };

        if (this.gui_objects.mailboxlist) {
            params._folderlist = 1;
        }
        if (this.gui_objects.quotadisplay) {
            params._quota = 1;
        }
        if (this.env.search_request) {
            params._search = this.env.search_request;
        }

        if (this.gui_objects.messagelist) {
            params._list = 1;

            // message uids for flag updates check
            params._uids = $.map(this.message_list.rows, function (row, uid) {
                return uid;
            }).join(',');
        }

        return params;
    };


    /********************************************************/
    /*                     helper methods                   */
    /********************************************************/

    /**
     * Quote html entities
     */
    this.quote_html = function (str) {
        return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };

    // get window.opener.rcmail if available
    this.opener = function (deep, filter) {
        var i, win = window.opener;

        // catch Error: Permission denied to access property rcmail
        try {
            if (win && !win.closed && win !== window) {
                // try parent of the opener window, e.g. preview frame
                if (deep && (!win.rcmail || win.rcmail.env.framed) && win.parent && win.parent.rcmail) {
                    win = win.parent;
                }

                if (win.rcmail && filter) {
                    for (i in filter) {
                        if (win.rcmail.env[i] != filter[i]) {
                            return;
                        }
                    }
                }

                return win.rcmail;
            }
        } catch (e) {}
    };

    // check if we're in show mode or if we have a unique selection
    // and return the message uid
    this.get_single_uid = function () {
        var uid = this.env.uid || (this.message_list ? this.message_list.get_single_selection() : null);
        var result = ref.triggerEvent('get_single_uid', { uid: uid });
        return result || uid;
    };

    // same as above but for contacts
    this.get_single_cid = function () {
        var cid = this.env.cid || (this.contact_list ? this.contact_list.get_single_selection() : null);
        var result = ref.triggerEvent('get_single_cid', { cid: cid });
        return result || cid;
    };

    // get the IMP mailbox of the message with the given UID
    this.get_message_mailbox = function (uid) {
        var msg;

        if (this.env.messages && uid && (msg = this.env.messages[uid]) && msg.mbox) {
            return msg.mbox;
        }

        if (/^[0-9]+-(.*)$/.test(uid)) {
            return RegExp.$1;
        }

        return this.env.mailbox;
    };

    // build request parameters from single message id (maybe with mailbox name)
    this.params_from_uid = function (uid, params) {
        if (!params) {
            params = {};
        }

        params._uid = String(uid).split('-')[0];
        params._mbox = this.get_message_mailbox(uid);

        return params;
    };

    // gets cursor position
    this.get_caret_pos = function (obj) {
        if (obj.selectionEnd !== undefined) {
            return obj.selectionEnd;
        }

        return obj.value.length;
    };

    // moves cursor to specified position
    this.set_caret_pos = function (obj, pos) {
        try {
            if (obj.setSelectionRange) {
                obj.setSelectionRange(pos, pos);
            }
        } catch (e) {} // catch Firefox exception if obj is hidden
    };

    // get selected text from an input field
    this.get_input_selection = function (obj) {
        var start = 0, end = 0, normalizedValue = '';

        if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
            normalizedValue = obj.value;
            start = obj.selectionStart;
            end = obj.selectionEnd;
        }

        return { start: start, end: end, text: normalizedValue.substr(start, end - start) };
    };

    // disable/enable all fields of a form
    this.lock_form = function (form, lock) {
        if (!form || !form.elements) {
            return;
        }

        if (lock) {
            this.disabled_form_elements = [];
        }

        $.each(form.elements, function () {
            if (this.type == 'hidden') {
                return;
            }
            // remember which elem was disabled before lock
            if (lock && this.disabled) {
                ref.disabled_form_elements.push(this);
            } else if (lock || $.inArray(this, ref.disabled_form_elements) < 0) {
                this.disabled = lock;
            }
        });
    };

    this.mailto_handler_uri = function () {
        return location.href.split('?')[0] + '?_task=mail&_action=compose&_to=%s';
    };

    this.register_protocol_handler = function (name) {
        try {
            window.navigator.registerProtocolHandler('mailto', this.mailto_handler_uri(), name);
        } catch (e) {
            this.display_message(String(e), 'error');
        }
    };

    this.check_protocol_handler = function (name, elem) {
        var nav = window.navigator;

        if (!nav || (typeof nav.registerProtocolHandler !== 'function')) {
            $(elem).addClass('disabled').click(function () {
                ref.display_message('nosupporterror', 'error');
                return false;
            });
        } else if (typeof nav.isProtocolHandlerRegistered === 'function') {
            var status = nav.isProtocolHandlerRegistered('mailto', this.mailto_handler_uri());
            if (status) {
                $(elem).parent().find('.mailtoprotohandler-status').html(status);
            }
        } else {
            $(elem).click(function () {
                ref.register_protocol_handler(name); return false;
            });
        }
    };

    // Checks browser capabilities e.g. PDF support, TIF support
    this.browser_capabilities_check = function () {
        if (!this.env.browser_capabilities) {
            this.env.browser_capabilities = {};
        }

        $.each(['pdf', 'flash', 'tiff', 'webp', 'pgpmime'], function () {
            if (ref.env.browser_capabilities[this] === undefined) {
                ref.env.browser_capabilities[this] = ref[this + '_support_check']();
            }
        });
    };

    // Returns browser capabilities string
    this.browser_capabilities = function () {
        if (!this.env.browser_capabilities) {
            return '';
        }

        var n, ret = [];

        for (n in this.env.browser_capabilities) {
            ret.push(n + '=' + this.env.browser_capabilities[n]);
        }

        return ret.join();
    };

    this.tiff_support_check = function () {
        this.image_support_check('tiff');
        return 0;
    };

    this.webp_support_check = function () {
        this.image_support_check('webp');
        return 0;
    };

    this.image_support_check = function (type) {
        setTimeout(function () {
            var img = new Image();
            img.onload = function () {
                ref.env.browser_capabilities[type] = 1;
            };
            img.onerror = function () {
                ref.env.browser_capabilities[type] = 0;
            };
            img.src = ref.assets_path('program/resources/blank.' + type);
        }, 10);
    };

    this.pdf_support_check = function () {
        if ('pdfViewerEnabled' in navigator) {
            return navigator.pdfViewerEnabled ? 1 : 0;
        }

        var i, plugin = navigator.mimeTypes ? navigator.mimeTypes['application/pdf'] : {},
            regex = /Adobe Reader|PDF|Acrobat/i;

        if (plugin && plugin.enabledPlugin) {
            return 1;
        }

        for (i in navigator.plugins) {
            plugin = navigator.plugins[i];
            if (typeof plugin === 'string') {
                if (regex.test(plugin)) {
                    return 1;
                }
            } else if (plugin.name && regex.test(plugin.name)) {
                return 1;
            }
        }

        setTimeout(function () {
            $('<object>').attr({
                data: ref.assets_path('program/resources/dummy.pdf'),
                type: 'application/pdf',
                style: 'position: "absolute"; top: -1000px; height: 1px; width: 1px',
            })
                .on('load error', function (e) {
                    ref.env.browser_capabilities.pdf = e.type == 'load' ? 1 : 0;

                    // add a short delay before attempting to remove element (#8128)
                    var obj = this;
                    setTimeout(function () {
                        $(obj).remove();
                    }, 10);
                })
                .appendTo(document.body);
        }, 10);

        return 0;
    };

    this.flash_support_check = function () {
        var plugin = navigator.mimeTypes ? navigator.mimeTypes['application/x-shockwave-flash'] : {};

        if (plugin && plugin.enabledPlugin) {
            return 1;
        }

        if ('ActiveXObject' in window) {
            try {
                plugin = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');

                return 1;
            } catch (e) {}
        }

        return 0;
    };

    // check for mailvelope API
    this.pgpmime_support_check = function (action) {
        if (window.mailvelope) {
            return 1;
        }

        $(window).on('mailvelope', function () {
            ref.env.browser_capabilities.pgpmime = 1;
        });

        return 0;
    };

    this.assets_path = function (path) {
        if (this.env.assets_path && !path.startsWith(this.env.assets_path)) {
            path = this.env.assets_path + path;
        }

        return path;
    };

    // Cookie setter
    this.set_cookie = function (name, value, expires) {
        if (expires === false) {
            var expires = new Date();
            expires.setYear(expires.getFullYear() + 1);
        }

        setCookie(name, value, expires, this.env.cookie_path, this.env.cookie_domain, this.env.cookie_secure);
    };

    this.get_local_storage_prefix = function () {
        if (!this.local_storage_prefix) {
            this.local_storage_prefix = 'roundcube.' + (this.env.user_id || 'anonymous') + '.';
        }

        return this.local_storage_prefix;
    };

    // wrapper for localStorage.getItem(key)
    this.local_storage_get_item = function (key, deflt, encrypted) {
        var item, result;

        // TODO: add encryption
        try {
            item = localStorage.getItem(this.get_local_storage_prefix() + key);
            result = JSON.parse(item);
        } catch (e) {}

        return result || deflt || null;
    };

    // wrapper for localStorage.setItem(key, data)
    this.local_storage_set_item = function (key, data, encrypted) {
        // try/catch to handle no localStorage support, but also error
        // in Safari-in-private-browsing-mode where localStorage exists
        // but can't be used (#1489996)
        try {
            // TODO: add encryption
            localStorage.setItem(this.get_local_storage_prefix() + key, JSON.stringify(data));
            return true;
        } catch (e) {
            return false;
        }
    };

    // wrapper for localStorage.removeItem(key)
    this.local_storage_remove_item = function (key) {
        try {
            localStorage.removeItem(this.get_local_storage_prefix() + key);
            return true;
        } catch (e) {
            return false;
        }
    };

    this.print_dialog = function () {
        // setTimeout for Safari
        setTimeout('window.print()', 10);
    };

    this.preload_images = function (urls) {
        for (var i = 0; i < urls.length; i++) {
            var img = new Image();
            img.src = urls[i];
        }
    };

    this.toggle_html_editor = function (event) {
        this.toggle_editor({ html: event.target.checked }, null, event);
    };

    this.toggle_change_subscription = function (elem) {
        if (elem.checked) {
            ref.subscribe(elem.value);
        } else {
            ref.unsubscribe(elem.value);
        }
    };

    this.filter_folder = function (elem) {
        ref.folder_filter(elem.value);
    };

    this.reset_value_if_inbox = function (elem) {
        if ($(elem).val() == 'INBOX') {
            $(elem).val('');
        }
    };

    this.disable_show_images_if_plaintext_preferred = function (elem) {
        $('#rcmfd_show_images').prop('disabled', !elem.checked).val(0);
    };

    this.hide_and_show_next = function (elem) {
        $(elem).hide().next().show();
    };

    // Allow to call rcmail.message_list.clear() without the extra calls in
    // rcmail.clear_message_list().
    this.message_list_clear = function (arg) {
        ref.message_list.clear(arg);
    };

    this.onerror_set_placeholder_src = function (event, src) {
        var elem = event.target;
        elem.onerror = null;
        if (!src) {
            src = this.env.photo_placeholder;
        }
        elem.src = src;
    };

    this.show_sibling_image_attachments = function (elem) {
        $(elem).parents('p.image-attachment').show();
    };

    this.reloadForm = function (elem) {
        this.command('save', 'reload', elem.form);
    };

    this.toggle_html_signature_editor = function (event) {
        this.toggle_editor({ id: 'rcmfd_signature', html: event.target.checked }, null, event);
    };

    /**
     * Add eventListeners parsed from element datasets. This allows to avoid
     * inline javascript event handlers (which require a very lax CSP).
     * The first element of the array is the function on `rcmail` to call.
     * All further elements of the array are arguments that are handed to the
     * called function.
     * In that list, '__THIS__' is replaced with the element that the event was
     * attached to, and '__EVENT__' is the event itself.
     * E.g. with this element:
     * <a data-click='["aMethodToCall", "arg1"]' data-dblclick='["something", "__THIS__"]'>something</a>
     * A click will cause a call to `this[aMethodToCall]("arg1")`, and a double
     * click will cause a call to `this[something](theDoubleClickedElement)`.
     */
    this.addEventListenerFromElement = function (elem, eventName) {
        var value = elem.dataset['on' + eventName];
        if (!value) {
            return;
        }
        var eventArgs = JSON.parse(value);
        var methodName = eventArgs.shift();
        if (!methodName) {
            this.log("data-on-event '" + eventName + "' has invalid value (no method name)");
            return;
        }
        if (typeof this[methodName] !== 'function') {
            this.log("'" + methodName + "' is not a valid method name of this class");
            return;
        }
        // Inject a reference to the event target element, if required.
        eventArgs = eventArgs.map(function (arg) {
            if (arg === '__THIS__') {
                return elem;
            }
            return arg;
        });
        elem.addEventListener(eventName, (ev) => {
            // Inject a reference to the event object, if required.
            var localEventArgs = eventArgs.map(function (arg) {
                if (arg === '__EVENT__') {
                    return ev;
                }
                return arg;
            });
            this[methodName](...localEventArgs);
        });
    };

    this.addEventListenerFromElements = function (rootElem, eventName) {
        rootElem ||= document.body;
        rootElem.querySelectorAll('[data-on' + eventName + ']').forEach(function (elem) {
            ref.addEventListenerFromElement(elem, eventName);
        });
    };

    this.addAllEventListenersFromElements = function (rootElem) {
        [
            'click',
            'dblclick',
            'mouseup',
            'mousedown',
            'mouseout',
            'mouseover',
            'error',
            'load',
            'change',
        ].forEach(function (name) {
            ref.addEventListenerFromElements(rootElem, name);
        });
    };
} // end object rcube_webmail


// some static methods
rcube_webmail.long_subject_title = function (elem, indent, text_elem) {
    if (!elem.title) {
        var siblings_width = 0, $elem = $(text_elem || elem);

        $elem.siblings().each(function () {
            // Note: width() returns 0 for elements with icons in :before (Elastic)
            siblings_width += $(this).width() + (parseFloat(window.getComputedStyle(this, ':before').width) || 0);
        });

        // Note: 3px to be on the safe side, but also specifically for Elastic
        if ($elem.width() + siblings_width + (indent || 0) * 15 >= $elem.parent().width() - 3) {
            elem.title = rcube_webmail.subject_text($elem[0]);
        }
    }
};

rcube_webmail.long_subject_title_ex = function (elem) {
    if (!elem.title) {
        var $elem = $(elem),
            txt = $elem.text().trim(),
            indent = $('span.branch', $elem).width() || 0,
            tmp = $('<span>').text(txt)
                .css({
                    position: 'absolute',
                    float: 'left',
                    visibility: 'hidden',
                    'font-size': $elem.css('font-size'),
                    'font-weight': $elem.css('font-weight'),
                })
                .appendTo(document.body),
            w = tmp.width();

        tmp.remove();
        if (w + indent * 15 > $elem.width()) {
            elem.title = rcube_webmail.subject_text(elem);
        }
    }
};

rcube_webmail.subject_text = function (elem) {
    var t = $(elem).clone();
    t.find('.skip-on-drag,.skip-content,.voice').remove();
    return t.text().trim();
};

// set event handlers on all iframe elements (and their contents)
rcube_webmail.set_iframe_events = function (events) {
    $('iframe').each(function () {
        var frame = $(this);
        $.each(events, function (event_name, event_handler) {
            frame.on('load', function (e) {
                try {
                    $(this).contents().on(event_name, event_handler);
                } catch (e) { /* catch possible permission error in IE */ }
            });

            try {
                frame.contents().on(event_name, event_handler);
            } catch (e) { /* catch possible permission error in IE */ }
        });
    });
};

rcube_webmail.prototype.get_cookie = getCookie;

// copy event engine prototype
rcube_webmail.prototype.addEventListener = rcube_event_engine.prototype.addEventListener;
rcube_webmail.prototype.removeEventListener = rcube_event_engine.prototype.removeEventListener;
rcube_webmail.prototype.triggerEvent = rcube_event_engine.prototype.triggerEvent;

window.rcmail = new rcube_webmail();
document.addEventListener('DOMContentLoaded', function () { window.rcmail.init(); });
