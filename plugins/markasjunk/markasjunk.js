/* Mark-as-Junk plugin script */

function rcmail_markasjunk(prop)
{
  if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
    return;
  
    var uids = rcmail.env.uid ? rcmail.env.uid : rcmail.message_list.get_selection().join(','),
      lock = rcmail.set_busy(true, 'loading');

    rcmail.http_post('plugin.markasjunk', '_uid='+uids+'&_mbox='+urlencode(rcmail.env.mailbox), lock);
}

// callback for app-onload event
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    
    // register command (directly enable in message view mode)
    rcmail.register_command('plugin.markasjunk', rcmail_markasjunk, rcmail.env.uid);
    
    // add event-listener to message list
    if (rcmail.message_list)
      rcmail.message_list.addEventListener('select', function(list){
        rcmail.enable_command('plugin.markasjunk', list.get_selection().length > 0);
      });
  })
}

