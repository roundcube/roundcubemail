/**
 * Help plugin client script
 * @version 1.4
 */

// hook into switch-task event to open the help window
if (window.rcmail) {
    rcmail.addEventListener('beforeswitch-task', function(prop) {
        // catch clicks to help task button
        if (prop == 'help') {
            if (rcmail.task == 'help')  // we're already there
                return false;

            var url = rcmail.url('help/index', { _rel: rcmail.task + (rcmail.env.action ? '/'+rcmail.env.action : '') });
            if (rcmail.env.help_open_extwin) {
                rcmail.open_window(url, 1020, false);
            }
            else {
                rcmail.redirect(url, false);
            }

            return false;
      }
  });
}
