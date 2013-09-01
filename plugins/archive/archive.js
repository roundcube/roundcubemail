/*
 * Archive plugin script
 * @version 2.0
 */

function rcmail_archive(prop)
{
    if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length)) {
        return;
    }

    if (rcmail.env.mailbox.indexOf(rcmail.env.archive_folder) != 0) {
        if (!rcmail.env.archive_type) {
            // simply move to archive folder (if no partition type is set)
            rcmail.command('move', rcmail.env.archive_folder);
        } else {
            // let the server sort the messages to the according subfolders
            var post_data = { _uid: rcmail.message_list.get_selection().join(','), _mbox: rcmail.env.mailbox };

            rcmail.http_post('plugin.move2archive', post_data);
        }
    }
}

// callback for app-onload event
if (window.rcmail) {
    rcmail.addEventListener('init', function(evt)
    {
        // register command (directly enable in message view mode)
        rcmail.register_command('plugin.archive', rcmail_archive, (rcmail.env.uid && rcmail.env.mailbox != rcmail.env.archive_folder));

        // add event-listener to message list
        if (rcmail.message_list) {
            rcmail.message_list.addEventListener('select', function(list) {
                rcmail.enable_command('plugin.archive', (list.get_selection().length > 0 && rcmail.env.mailbox != rcmail.env.archive_folder));
            });
        }

        // set css style for archive folder
        var li;

        if (rcmail.env.archive_folder && (li = rcmail.get_folder_li(rcmail.env.archive_folder, '', true))) {
            $(li).addClass('archive');
        }

        // callback for server response
        rcmail.addEventListener('plugin.move2archive_response', function(result) {
            // refresh list
            if (result.update) rcmail.command('checkmail');
        });
    })
}
