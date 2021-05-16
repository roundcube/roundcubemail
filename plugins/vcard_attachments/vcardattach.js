/**
 * vcard_attachments plugin script
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

function plugin_vcard_import(mime_id)
{
  if (!mime_id) {
    var content = [];

    $.each(rcmail.env.vcards, function (id, contact) {
      var chbox = $('<input>').attr({type: 'checkbox', value: id, checked: true, 'class': 'pretty-checkbox'}),
        label = $('<label>').text(' ' + contact);

      content.push($('<div>').append(label.prepend(chbox)));
    });

    var dialog,
      action = function(e, a) {
        var contacts = []

        dialog.find('input:checked').each(function() {
          contacts.push(this.value);
        });

        if (contacts.length) {
          plugin_vcard_import(contacts.join());
          return true; // close the dialog
        }
      },
      props = {
        button: 'import',
        height: content.length > 4 ? 250 : 100
      };

    dialog = rcmail.simple_dialog(content, 'vcard_attachments.addvcardmsg', action, props);

    return false;
  }

  rcmail.http_post(
    'plugin.savevcard',
    { _uid: rcmail.env.uid, _mbox: rcmail.env.mailbox, _part: mime_id },
    rcmail.set_busy(true, 'loading')
  );

  return false;
}

function plugin_vcard_insertrow(data)
{
  if (data.row.ctype.match(/^(text\/vcard|text\/x-vcard|text\/directory)$/i)) {
    $(data.row.obj).find('.attachment > .attachment').addClass('vcard');
  }
}

function plugin_vcard_attach()
{
  var id, n, contacts = [],
    ts = new Date().getTime(),
    args = {_uploadid: ts, _id: rcmail.env.compose_id || null},
    selection = rcmail.contact_list.get_selection();

  for (n=0; n < selection.length; n++) {
    if (rcmail.env.task == 'addressbook') {
      id = selection[n];
      contacts.push(rcmail.env.source + '-' + id + '-0');
    }
    else {
      id = selection[n];
      if (id && id.charAt(0) != 'E' && rcmail.env.contactdata[id])
        contacts.push(id);
    }
  }

  if (!contacts.length)
    return false;

  args._uri = 'vcard://' + contacts.join(',');

  if (rcmail.env.task == 'addressbook') {
      args._attach_vcard = 1;
      rcmail.open_compose_step(args);
  }
  else {
    // add to attachments list
    if (!rcmail.add2attachment_list(ts, {name: '', html: rcmail.get_label('attaching'), classname: 'uploading', complete: false}))
      rcmail.file_upload_id = rcmail.set_busy(true, 'attaching');

    rcmail.http_post('upload', args);
  }
}

window.rcmail && rcmail.addEventListener('init', function(evt) {
  if (rcmail.gui_objects.messagelist)
    rcmail.addEventListener('insertrow', function(data, evt) { plugin_vcard_insertrow(data); });

  if ((rcmail.env.action == 'compose' || (rcmail.env.task == 'addressbook' && rcmail.env.action == '')) && rcmail.gui_objects.contactslist) {
    if (rcmail.env.action == 'compose') {
      rcmail.env.compose_commands.push('attach-vcard');

      // Elastic: add "Attach vCard" button to the attachments widget
      if (window.UI && UI.recipient_selector) {
        var button, form = $('#compose-attachments > div');
        button = $('<button class="btn btn-secondary attach vcard">')
          .attr({type: 'button', tabindex: $('button,input', form).first().attr('tabindex') || 0})
          .text(rcmail.gettext('vcard_attachments.attachvcard'))
          .appendTo(form)
          .click(function() {
            UI.recipient_selector('', {
              title: 'vcard_attachments.attachvcard',
              button: 'vcard_attachments.attachvcard',
              button_class: 'attach',
              focus: button,
              multiselect: false,
              action: function() { rcmail.command('attach-vcard'); }
            });
          });
      }
    }

    rcmail.register_command('attach-vcard', function() { plugin_vcard_attach(); });
    rcmail.contact_list.addEventListener('select', function(list) {
      // TODO: support attaching more than one at once
      var selection = list.get_selection();
      rcmail.enable_command('attach-vcard', selection.length == 1 && selection[0].charAt(0) != 'E');
    });
  }
});
