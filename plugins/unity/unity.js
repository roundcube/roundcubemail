(function() {
  /* Variables */
  var Unity = null;

  var title = document.title.split(' ::')[0];
  var url = window.location.href;
  var end = url.lastIndexOf('/');
  var icon = url.slice(0, end)+'/plugins/unity/media/logo.png';
  
  /* Actions callbacks */
  function goto_mbox(mbox) {
    return function() {  
      if(rcmail.task == 'mail') {
        rcmail.command('list', mbox);
      } else {
        rcmail.env.mailbox = mbox; //force folder
        rcmail.command('switch-task', 'mail');
      }
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
  
  /* Utility */
  function showTotalUnreadCount() {
    unread = 0;
    for(i in rcmail.env.unread_counts) {
      unread += rcmail.env.unread_counts[i];
    }

    Unity.Launcher.setCount(unread);
  }
function dummy() {
  alert("I'm dunb, trust me !");
}
  /* Main Setup */
  function unityReady() {
    // Launcher menu
    Unity.Launcher.addAction("Inbox", goto_mbox("Inbox"));
    Unity.Launcher.addAction("New mail", goto_compose);
    Unity.Launcher.addAction("Contacts", goto_contacts);
    Unity.Launcher.addAction("Settings", goto_settings);
    
    // General: Unread count
    /* Overwrite some core functions to detect events */
    var set_unread_count_real = rcmail.set_unread_count;
    rcmail.set_unread_count = function(mbox, count, set_title, mark) {
      if(count > 0) {
        Unity.MessagingIndicator.showIndicator(mbox, {
          count: count,
          //onIndicatorActivated: goto_mbox(mbox)
          onIndicatorActivated: dummy
        });
      } else {
        Unity.MessagingIndicator.clearIndicator(mbox);
      }
      //actual call
      ret = set_unread_count_real.apply(rcmail, arguments);
      //post action
      showTotalUnreadCount();
      return ret;
    }

  }

  rcmail.addEventListener('init', function(evt) {
    Unity = external.getUnityObject(1.0);
 
    Unity.init({name: title,
                iconUrl: icon,
                onInit: unityReady});
  });
})();
