/*
 * Archive plugin script
 * @version @package_version@
 */

function rcmail_archive(prop)
{
  if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
    return;
  
  var uids = rcmail.env.uid ? rcmail.env.uid : rcmail.message_list.get_selection().join(','),
    lock = rcmail.set_busy(true, 'loading');

  rcmail.http_post('plugin.archive', '_uid='+uids+'&_mbox='+urlencode(rcmail.env.mailbox), lock);
}

// callback for app-onload event
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    
    // register command (directly enable in message view mode)
    rcmail.register_command('plugin.archive', rcmail_archive, (rcmail.env.uid && rcmail.env.mailbox != rcmail.env.archive_folder));
    
    // add event-listener to message list
    if (rcmail.message_list)
      rcmail.message_list.addEventListener('select', function(list){
        rcmail.enable_command('plugin.archive', (list.get_selection().length > 0 && rcmail.env.mailbox != rcmail.env.archive_folder));
      });
    
    // set css style for archive folder
    var li;
    if (rcmail.env.archive_folder && rcmail.env.archive_folder_icon && (li = rcmail.get_folder_li(rcmail.env.archive_folder)))
      $(li).css('background-image', 'url(' + rcmail.env.archive_folder_icon + ')');
  })
}

