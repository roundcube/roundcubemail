/* Show user-info plugin script */

if (window.rcmail) {
  rcmail.addEventListener('init', function() {
    // <span id="settingstabdefault" class="tablink"><roundcube:button command="preferences" type="link" label="preferences" title="editpreferences" /></span>
    var tab = $('<span>').attr('id', 'settingstabpluginaccount_details').addClass('tablink');

    $('<a>').attr('href', rcmail.env.comm_path + '&_action=plugin.account_details')
      .text(rcmail.get_label('account_details', 'account_details'))
      .click(function(e) { return rcmail.command('plugin.account_details', '', this, e); })
      .appendTo(tab);

    // add button and register command
    rcmail.add_element(tab, 'tabs');
    rcmail.register_command('plugin.account_details', function() { rcmail.goto_url('plugin.account_details') }, true);
  })
}

