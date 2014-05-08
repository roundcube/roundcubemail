/**
 * Mark-as-Junk plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2013, The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

function rcmail_markasjunk(prop)
{
  if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
    return;
  
  var uids = rcmail.env.uid ? rcmail.env.uid : rcmail.message_list.get_selection().join(','),
    lock = rcmail.set_busy(true, 'loading');

  if (rcmail.env.mailboxes[rcmail.env.mailbox]['class'] == 'junk')
    rcmail.http_post('plugin.markasnotjunk', '_uid='+uids+'&_mbox='+urlencode(rcmail.env.mailbox), lock);
  else 
    rcmail.http_post('plugin.markasjunk', '_uid='+uids+'&_mbox='+urlencode(rcmail.env.mailbox), lock);
}

// callback for app-onload event
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    
    // register command (directly enable in message view mode)
    rcmail.register_command('plugin.markasjunk', rcmail_markasjunk, rcmail.env.uid);
    rcmail.register_command('plugin.markasnotjunk', rcmail_markasjunk, rcmail.env.uid);

    // add event-listener to message list
    if (rcmail.message_list) {
      rcmail.message_list.addEventListener('select', function(list){
        rcmail.enable_command('plugin.markasjunk', list.get_selection().length > 0);
      });
      rcmail.addEventListener('afterlist', function(list){
        if (rcmail.env.mailboxes[rcmail.env.mailbox]['class'] == 'junk') {
          $('.button.junk').text(rcmail.labels['markasjunk.buttontext2']);
          $('.button.junk').attr('title',rcmail.labels['markasjunk.buttontitle2']);
        } else {
          $('.button.junk').text(rcmail.labels['markasjunk.buttontext']);
          $('.button.junk').attr('title',rcmail.labels['markasjunk.buttontitle']);
        }
      });
    }
  })
}

