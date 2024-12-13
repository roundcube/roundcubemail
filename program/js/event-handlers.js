window.rcmail.addEventListener('init', function () {
    $('[data-event-handle="rcmaddcontactlink"]').on('click', function (event) {
        const string = event.target.dataset.string;
        return this.command('add-contact', string, event.target);
    });

    $('[data-event-handle="morelink_simpledialog"]').on('click', function (event) {
        const allvalues = event.target.dataset.allvalues;
        const title = event.target.dataset.title;
        return window.rcmail.simple_dialog(allvalues, title, null, {cancel_button: 'close'});
    });

    $('[data-event-handle="listmenulink"]').on('click', function (event) {
        return window.rcmail.command('menu-open', event.target.dataset.ref, event.target, event);
    });

    $('[data-event-handle="compose_mailto_handle"]').on('click', function (event) {
        return window.rcmail.command('compose', event.target.dataset.url, event.target);
    });

    $('[data-event-handle="compose_format_recipient"]').on('click', function (event) {
        return window.rcmail.command('compose', event.target.dataset.recipient, event.target);
    });

    $('[data-event-handle="hide_and_show_next"]').on('click', function (event) {
        $(event.target).hide().next().show();
    });

    $('[data-event-handle="filter_mailbox_with_this_value"]').on('click', function (event) {
        window.rcmail.filter_mailbox(event.target.value);
    });

    $('[data-event-handle="compose_mailto_callback"]').on('click', function (event) {
        // TODO: JQ encoding necessary?
        window.rcmail.command('compose', event.target.dataset.href, event.target);
    });

    $('[data-event-handle="compose_mailto_callback"]').on('click', function (event) {
        window.rcmail.command('pushgroup', {source: event.target.dataset.source, id: event.target.dataset.id}, event.target, event);
    });

    $('[data-event-handle="contacts_delete_undo"]').on('click', function (event) {
        window.rcmail.command('undo', '', event.target);
    });

    $('[data-event-handle="mail_load_remote"]').on('click', function (event) {
        window.rcmail.command('load-remote');
    });

    $('[data-event-handle="mail_load_remote_with_arg"]').on('click', function (event) {
         window.rcmail.command('load-remote', event.target.dataset.arg);
     });

    $('[data-event-handle="mail_edit"]').on('click', function (event) {
         window.rcmail.command('edit');
     });

    $('[data-event-handle="onerror_set_placeholder_src"]').on('error', function (event) {
        const src = event.target.dataset.src ?? window.rcmail.env.photo_placeholder;
        event.target.src = src;
    });

    $('[data-event-handle="mail_show_headers"]').on('click', function (event) {
         window.rcmail.command('show-headers', '', event.target);
     });

    $('[data-event-handle="mail_load_attachment"]').on('click', function (event) {
        const mime_id = event.target.dataset.mimeId;
        window.rcmail.command('load-attachment', mime_id, event.target);
        return false;
    });

    $('[data-event-handle="mail_load_attachment_with_event"]').on('click', function (event) {
        const mime_id = event.target.dataset.mimeId;
        return window.rcmail.command('load-attachment', $mime_id, event.target, event);
     });

    $('[data-event-handle="mail_remove_attachment"]').on('click', function (event) {
        const mime_id = event.target.dataset.mimeId;
        return window.rcmail.command('remove-attachment', $mime_id, event.target, event);
     });

    $('[data-event-handle="mail_show_sibling_image_attachments"]').on('load', function (event) {
        $(event.target).parents('p.image-attachment').show();
     });

    $('[data-event-handle="contacts_compose"]').on('click', function (event) {
         return window.rcmail.command('compose', event.target.dataset.email, event.target);
     });

    $('[data-event-handle="mail_long_subject_title_ex"]').on('mouseover', function (event) {
         window.rcmail.long_subject_title_ex(event.target);
     });

    $('[data-event-handle="mail_toggle_html_editor_by_value"]').on('change', function (event) {
        return this.command('toggle-editor', { html: event.target.value == 'html' }, null, event);
     });


    $('[data-event-handle="mail_list_addresses"]').on('click', function (event) {
         return window.rcmail.command('list-addresses', event.target.dataset.arg, event.target);
     });


    $('[data-event-handle="mail_insert_reponse"]').on('click', function (event) {
         return window.rcmail.command('insert-response', event.target.dataset.responseId, event.target, event);
     });


    $('[data-event-handle="contacts_directory_list"]').on('click', function (event) {
         return window.rcmail.command('list', event.target.dataset.arg, event.target);
     });

    $('[data-event-handle="contacts_listsearch"]').on('click', function (event) {
         return window.rcmail.command('listsearch', event.target.dataset.arg, event.target);
     });

    $('[data-event-handle="contacts_listgroup"]').on('click', function (event) {
         const arg = event.target.dataset.arg;
         return window.rcmail.command('listgroup', {source: arg, id: arg}, event.target);
     });


    $('[data-event-handle="contacts_pushgroup"]').on('click', function (event) {
        const data = event.target.dataset;
        return window.rcmail.command(
            'pushgroup',
            {source: data.source, id: data.id},
            event.target,
            event
        );
     });

    $('[data-event-handle="toggle_html_signature_editor"]').on('click', function (event) {
        window.rcmail.toggle_editor({ id: 'rcmfd_signature', html: event.target.checked }, null, event);
     });

    $('[data-event-handle="hide_by_id"]').on('click', function (event) {
        $('#' + event.target.dataset.id).hide();
     });

    $('[data-event-handle="command_with_form"]').on('change', function (event) {
        const elem = event.target;
        const data = elem.dataset;
        this.command(data.action, elem.form);
        if (data.nullifyValue) {
            elem.value = null;
        }
     });

    $('[data-event-handle="folder_tree_list"]').on('click', function (event) {
         window.rcmail.command('list', event.target.dataset.name, event.target, event);
     });

    $('[data-event-handle="call_redirect"]').on('click', function (event) {
         window.rcmail.redirect(event.target.dataset.url, false);
     });

    $('[data-event-handle="folder_form_size"]').on('click', function (event) {
         window.rcmail.command('folder-size', event.target.dataset.mbox, event.target);
     });


    $('[data-event-handle="toggle_html_editor"]').on('click', function (event) {
        return this.toggle_editor({ html: event.target.checked }, null, event);
     });


    $('[data-event-handle="mailvelope_enable"]').on('click', function (event) {
         window.rcmail.mailvelope_enable();
     });

    $('[data-event-handle="call_switch_task"]').on('click', function (event) {
         const elem = event.target;
         window.rcmail.command('switch-task', elem.dataset.arg, elem, event);
     });

    $('[data-event-handle="call_command"]').on('click', function (event) {
         const data = event.target.dataset;
         window.rcmail.command(data.command, data.prop ?? "", event.target, event);
     });

    $('[data-event-handle="call_search_command"]').on('click', function (event) {
         window.rcmail.command('search');
     });

    $('[data-event-handle="toggle_change_subscription"]').on('click', function (event) {
        const elem = event.target;
        if (elem.checked) {
            window.rcmail.subscribe(elem.value);
        } else {
            window.rcmail.unsubscribe(elem.value);
        }
     });

    $('[data-event-handle="call_show_help_content"]').on('click', function (event) {
         return show_help_content(event.target.dataset.action, event);
     });

    $('[data-event-handle="filter_folder"]').on('click', function (event) {
        const selector = event.target.dataset.selector;
        window.rcmail.folder_filter($(selector).val());
     });

    $('[data-event-handle=""]').on('click', function (event) {
         window.rcmail.command(
     });


})
