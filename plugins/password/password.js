/**
 * Password plugin script
 * @version @package_version@
 */

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {

    // register command handler
    rcmail.register_command('plugin.password-save', function() { 
      var input_curpasswd = rcube_find_object('_curpasswd'),
        input_newpasswd = rcube_find_object('_newpasswd'),
        input_confpasswd = rcube_find_object('_confpasswd');

      if (input_curpasswd && input_curpasswd.value == '') {
          alert(rcmail.gettext('nocurpassword', 'password'));
          input_curpasswd.focus();
      } else if (input_newpasswd && input_newpasswd.value == '') {
          alert(rcmail.gettext('nopassword', 'password'));
          input_newpasswd.focus();
      } else if (input_confpasswd && input_confpasswd.value == '') {
          alert(rcmail.gettext('nopassword', 'password'));
          input_confpasswd.focus();
      } else if (input_newpasswd && input_confpasswd && input_newpasswd.value != input_confpasswd.value) {
          alert(rcmail.gettext('passwordinconsistency', 'password'));
          input_newpasswd.focus();
      } else {
          rcmail.gui_objects.passform.submit();
      }
    }, true);
  })
}
