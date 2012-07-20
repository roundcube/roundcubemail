(function() {
  /* Variables */
  var Unity = null;

  var title = document.title.split(' ::')[0];
  var url = window.location.href;
  var end = url.lastIndexOf('/');
  var icon = url.slice(0, end)+'/plugins/unity/media/logo.png';
  
  /* Actions callbacks */
  function goto_inbox() {
    if(rcmail.task == 'mail') {
      rcmail.command('list', 'INBOX');
    } else {
      rcmail.env.mailbox = 'INBOX'; //force INBOX folder
      rcmail.command('switch-task', 'mail');
    }
  }

  function goto_compose() {
    rcmail.command('compose', '');
  }

  function goto_contacts() {
    rcmail.command('switch-task', 'addressbook');
  }

  function goto_settings() {
    rcmail.command('switch-task', 'settings');
  }

  /* Main Setup */
  function unityReady() {
    // Launcher menu
    Unity.Launcher.addAction("Inbox", goto_inbox);
    Unity.Launcher.addAction("New mail", goto_compose);
    Unity.Launcher.addAction("Contacts", goto_contacts);
    Unity.Launcher.addAction("Settings", goto_settings);
  }

  rcmail.addEventListener('init', function(evt) {
    Unity = external.getUnityObject(1.0);
 
    Unity.init({name: title,
                iconUrl: icon,
                onInit: unityReady});
  });
})();
