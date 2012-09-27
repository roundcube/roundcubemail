/**
 * ZipDownload plugin script
 */

function rcmail_zipmessages() {
	if (rcmail.message_list && rcmail.message_list.get_selection().length > 1) {
		rcmail.goto_url('plugin.zipdownload.zip_messages', '_mbox=' + urlencode(rcmail.env.mailbox) + '&_uid=' + rcmail.message_list.get_selection().join(','));
	}
}

$(document).ready(function() {
	if (window.rcmail) {
		rcmail.addEventListener('init', function(evt) {
			// register command (directly enable in message view mode)
			rcmail.register_command('plugin.zipdownload.zip_folder', function() {
				rcmail.goto_url('plugin.zipdownload.zip_folder', '_mbox=' + urlencode(rcmail.env.mailbox));
			}, rcmail.env.messagecount > 0);

			if (rcmail.message_list && rcmail.env.zipdownload_selection) {
				rcmail.message_list.addEventListener('select', function(list) {
					rcmail.enable_command('download', list.get_selection().length > 0);
				});

				// check in contextmenu plugin exists and if so allow multiple message download
				if (rcmail.contextmenu_disable_multi)
					rcmail.contextmenu_disable_multi.splice($.inArray('#download', rcmail.contextmenu_disable_multi), 1);
			}
		});

		rcmail.addEventListener('listupdate', function(props) { rcmail.enable_command('plugin.zipdownload.zip_folder', rcmail.env.messagecount > 0); } );
		rcmail.addEventListener('beforedownload', function(props) { rcmail_zipmessages(); } );
	}
});