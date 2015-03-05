/**
 * vcard_attachments plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2012-2014, The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

function plugin_vcard_save_contact(mime_id)
{
  var lock = rcmail.set_busy(true, 'loading');
  rcmail.http_post('plugin.savevcard', { _uid: rcmail.env.uid, _mbox: rcmail.env.mailbox, _part: mime_id }, lock);

  return false;
}

function plugin_vcard_insertrow(data)
{
  var ctype = data.row.ctype;

  if (ctype == 'text/vcard' || ctype == 'text/x-vcard' || ctype == 'text/directory') {
    $('#rcmrow' + rcmail.html_identifier(data.uid, true) + ' > td.attachment')
      .html('<img src="' + rcmail.env.vcard_icon + '" alt="" />');
  }
}

if (window.rcmail && rcmail.gui_objects.messagelist) {
  rcmail.addEventListener('insertrow', function(data, evt) { plugin_vcard_insertrow(data); });
}
