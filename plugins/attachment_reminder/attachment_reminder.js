/* Attachment Reminder plugin script */

function rcmail_get_compose_message()
{
  var msg;

  if (window.tinyMCE && (ed = tinyMCE.get(rcmail.env.composebody))) {
    msg = ed.getContent();
    msg = msg.replace(/<blockquote[^>]*>(.|[\r\n])*<\/blockquote>/gmi, '');
  }
  else {
    msg = $('#' + rcmail.env.composebody).val();
    msg = msg.replace(/^>.*$/gmi, '');
  }

  return msg;
}

function rcmail_check_message(msg)
{
  var i, rx, keywords = rcmail.gettext('keywords', 'attachment_reminder').split(",").concat([".doc", ".pdf"]);

  $.each(keywords, function(n) { return RegExp.escape(n); });
  rx = new RegExp('(' + keywords.join('|') + ')', 'i');

  return msg.search(rx) != -1;
}

function rcmail_have_attachments()
{
  return rcmail.env.attachments && $('li', rcmail.gui_objects.attachmentlist).length;
}


if (window.rcmail) {
  rcmail.addEventListener('beforesend', function(evt) {
    var msg = rcmail_get_compose_message(),
      subject = $('#compose-subject').val();

    if (!rcmail_have_attachments() && (rcmail_check_message(msg) || rcmail_check_message(subject))) {
      if (confirm(rcmail.gettext('forgotattachment', 'attachment_reminder'))) {
        if (window.UI && UI.show_uploadform) // Larry skin
          UI.show_uploadform();
        else if (window.rcmail_ui && rcmail_ui.show_popup) // classic skin
          rcmail_ui.show_popup('uploadmenu', true);

        return false;
      }
    }
  });
}
