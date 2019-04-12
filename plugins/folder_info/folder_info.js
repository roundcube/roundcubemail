/**
 * Folder Info plugin script
 */

var update_message = function(folder) {
  var formating = function(message) {
    if( folder in rcmail.env.folder_info_messages_args ) {
      switch( typeof rcmail.env.folder_info_messages_args[folder] ) {
        case 'number':
        case 'string':
          message = message.replace('{}', rcmail.env.folder_info_messages_args[folder]);
          break;
        default:
          for(var i=0; i<rcmail.env.folder_info_messages_args[folder].length; i++) {
            message = message.replace('{}', rcmail.env.folder_info_messages_args[folder][i]);
          }
          break;
      }
    }
    return message;
  };
  switch( folder ) {
    case rcmail.env.trash_mailbox:
      $('.folder_info > span').html(formating(rcmail.gettext('trash_message', 'folder_info')));
      $('.folder_info').show();
      $('.messagelist thead th:first-child').css('border-radius', '0');
      break;
    case rcmail.env.junk_mailbox:
      $('.folder_info > span').html(formating(rcmail.gettext('junk_message', 'folder_info')));
      $('.folder_info').show();
      $('.messagelist thead th:first-child').css('border-radius', '0');
      break;
    default:
      if( folder in rcmail.env.folder_info_messages ) {
        $('.folder_info > span').html(formating(rcmail.env.folder_info_messages[folder]));
        $('.folder_info').show();
        $('.messagelist thead th:first-child').css('border-radius', '0');
      } else {
        $('.folder_info > span').html('');
        $('.folder_info').hide();
        $('.messagelist thead th:first-child').css('border-radius', '');
      }
      break;
  }
};

$('.folder_info').ready(function(){
  var folder_info = $('.folder_info');
  var folder_info_fixed = folder_info.clone()
    .addClass('folder_info_fixed')
    .css({ position: 'fixed' });
  folder_info.before(folder_info_fixed);
  $(window).resize(function(){ folder_info_fixed.width($('#messagelist').width()); });
});

rcmail.addEventListener('init', function(evt){ if( evt.task == "mail" ) {update_message(rcmail.env.mailbox);} });
rcmail.addEventListener('selectfolder', function(evt){ update_message(evt.folder); });
rcmail.addEventListener('listupdate', function(evt){ $('.folder_info_fixed').width($('#messagelist').width()); });
