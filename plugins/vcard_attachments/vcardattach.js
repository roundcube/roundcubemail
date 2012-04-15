/*
 * vcard_attachments plugin script
 * @version @package_version@
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
    $('#rcmrow'+data.uid+' > td.attachment').html('<img src="'+rcmail.env.vcard_icon+'" alt="" />');
  }
}

if (window.rcmail && rcmail.gui_objects.messagelist) {
  rcmail.addEventListener('insertrow', function(data, evt) { plugin_vcard_insertrow(data); });
}
