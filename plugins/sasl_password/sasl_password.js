/* SASL pssword change interface (tab) */

function sasl_password_save()
{
  var input_curpasswd = $('#saslcurpasswd')[0];
  var input_newpasswd = $('#saslnewpasswd')[0];
  var input_confpasswd = $('#saslconfpasswd')[0];

  if (input_curpasswd && input_curpasswd.value=='') {
    alert(rcmail.gettext('nocurpassword', 'sasl_password'));
    input_curpasswd.focus();
  }
  else if (input_newpasswd && input_newpasswd.value=='') {
    alert(rcmail.gettext('nopassword', 'sasl_password'));
    input_newpasswd.focus();
  }
  else if (input_confpasswd && input_confpasswd.value=='') {
    alert(rcmail.gettext('nopassword', 'sasl_password'));
    input_confpasswd.focus();
  }
  else if ((input_newpasswd && input_confpasswd) && (input_newpasswd.value != input_confpasswd.value)) {
    alert(rcmail.gettext('passwordinconsistency', 'sasl_password'));
    input_newpasswd.focus();
  }
  else {
    rcmail.gui_objects.passform.submit();
  }
}

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
  // <span id="settingstabdefault" class="tablink"><roundcube:button command="preferences" type="link" label="preferences" title="editpreferences" /></span>
  var tab = $('<span>').attr('id', 'settingstabpluginsaslpassword').addClass('tablink');
    
  var button = $('<a>').attr('href', rcmail.env.comm_path+'&_action=plugin.saslpassword').html(rcmail.gettext('password')).appendTo(tab);
  button.bind('click', function(e){ return rcmail.command('plugin.saslpassword', this) });

  // add button and register commands
  rcmail.add_element(tab, 'tabs');
  rcmail.register_command('plugin.saslpassword', function() { rcmail.goto_url('plugin.saslpassword') }, true);
  rcmail.register_command('plugin.saslpassword-save', sasl_password_save, true);
  });
}
