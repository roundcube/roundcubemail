/**
 * Archive plugin script
 * @version 2.1
 */

function rcmail_archive(prop)
{
  if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
    return;

  if (!rcmail_is_archive()) {
    if (!rcmail.env.archive_type) {
      // simply move to archive folder (if no partition type is set)
      rcmail.command('move', rcmail.env.archive_folder);
    }
    else {
      // let the server sort the messages to the according subfolders
      rcmail.http_post('plugin.move2archive', rcmail.selection_post_data());
    }
  }
}

function rcmail_is_archive()
{
  // check if current folder is an archive folder or one of its children
  if (rcmail.env.mailbox == rcmail.env.archive_folder
    || rcmail.env.mailbox.startsWith(rcmail.env.archive_folder + rcmail.env.delimiter)
  ) {
    return true;
  }
}

// callback for app-onload event
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    // register command (directly enable in message view mode)
    rcmail.register_command('plugin.archive', rcmail_archive, rcmail.env.uid && !rcmail_is_archive());

    // add event-listener to message list
    if (rcmail.message_list)
      rcmail.message_list.addEventListener('select', function(list) {
        rcmail.enable_command('plugin.archive', list.get_selection().length > 0 && !rcmail_is_archive());
      });

    // set css style for archive folder
    var li;
    if (rcmail.env.archive_folder && (li = rcmail.get_folder_li(rcmail.env.archive_folder, '', true)))
      $(li).addClass('archive');

    // callback for server response
    rcmail.addEventListener('plugin.move2archive_response', function(result) {
      if (result.update)
        rcmail.command('list');  // refresh list
    });
  })
}
