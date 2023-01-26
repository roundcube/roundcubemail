/**
 * Attachment Reminder plugin script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

function rcmail_get_compose_message()
{
  var msg = rcmail.editor.get_content({ nosig: true });

  if (rcmail.editor.is_html()) {
    // Remove quoted content, all HTML tags, and some entities
    msg = msg.replace(/<blockquote[^>]*>(.|[\r\n])*<\/blockquote>/gmi, '')
             .replace(/<[^>]+>/gm, ' ')
             .replace(/&nbsp;/g, ' ');
  }
  else {
    // Remove quoted content
    msg = msg.replace(/^>.*$/gmi, '');
  }

  return msg;
};

function rcmail_check_message(msg)
{
  var i, rx, keywords = rcmail.get_label('keywords', 'attachment_reminder').split(",").concat([".doc", ".pdf"]);

  keywords = $.map(keywords, function(n) { return RegExp.escape(n); });
  rx = new RegExp('(' + keywords.join('|') + ')', 'i');

  return msg.search(rx) != -1;
};

function rcmail_have_attachments()
{
  return rcmail.env.attachments && $('li', rcmail.gui_objects.attachmentlist).length;
};

function rcmail_attachment_reminder_dialog()
{
  var buttons = {};

  buttons[rcmail.get_label('addattachment')] = function() {
    $(this).remove();
    $('#messagetoolbar a.attach, .toolbar a.attach').first().click();
  };
  buttons[rcmail.get_label('send')] = function(e) {
    $(this).remove();
    rcmail.env.attachment_reminder = true;
    rcmail.command('send', '', e);
  };

  rcmail.env.attachment_reminder = false;
  rcmail.show_popup_dialog(
    rcmail.get_label('attachment_reminder.forgotattachment'),
    rcmail.get_label('attachment_reminder.missingattachment'),
    buttons,
    {button_classes: ['mainaction attach', 'send']}
  );
};


if (window.rcmail) {
  rcmail.addEventListener('beforesend', function(evt) {
    var msg = rcmail_get_compose_message(),
      subject = $('#compose-subject').val();

    if (!rcmail.env.attachment_reminder && !rcmail_have_attachments()
      && (rcmail_check_message(msg) || rcmail_check_message(subject))
    ) {
      rcmail_attachment_reminder_dialog();
      return false;
    }
  });
}
