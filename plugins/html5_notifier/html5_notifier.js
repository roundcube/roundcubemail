/**
 * html5_notifier
 * Shows a desktop notification every time a new (recent) mail comes in
 *
 * @version 0.5.0 - 19.12.2013
 * @author Tilman Stremlau <tilman@stremlau.net>
 * @website stremlau.net/html5_notifier
 * @licence GNU GPL
 *
 **/

function rcmail_show_notification(message)
{
    if (use_notifications)
    {
        if ("Notification" in window) {
            var notification = new Notification(rcmail.gettext('notification_title', 'html5_notifier').replace('[from]', message.from), {
                icon: './plugins/html5_notifier/images/new_mail.png',
                body: message.subject
            });
            notification.onclick = function() {
				if(message.opentype == '1') {
					rcmail.open_window('?_task=mail&_action=show&_uid='+message.uid);
				} else {
					window.open('?_task=mail&_action=show&_extwin=1&_uid='+message.uid);
				}
            }
            if (parseInt(message.duration) > 0)
            {
                setTimeout(function(){ notification.close(); }, (parseInt(message.duration)*1000));
            }
        }
    }
}

function rcmail_browser_notifications()
{
    if ("Notification" in window && Notification.permission) {
        if (Notification.permission === "granted") {
            rcmail.display_message(rcmail.gettext('ok_notifications', 'html5_notifier'), 'notice');
        }
        else {
            Notification.requestPermission(rcmail_check_notifications);
        }
    }
    else if (window.webkitNotifications) {
        if (window.webkitNotifications.checkPermission() == 0)
        {
            rcmail.display_message(rcmail.gettext('ok_notifications', 'html5_notifier'), 'notice');
        }
        else
        {
            window.webkitNotifications.requestPermission(rcmail_check_notifications);
        }
    }
    else
    {
        rcmail.display_message(rcmail.gettext('no_notifications', 'html5_notifier'), 'error');
    }
}

function rcmail_browser_notifications_test() {
    if (use_notifications)
    {
        rcmail.display_message(rcmail.gettext('check_ok', 'html5_notifier'), 'notice');
       
        var message = new Object();
        message.duration = 8;
        message.uid = 0;
        message.subject = 'It Works!';
        message.from = 'TESTMAN';
        message.opentype = $('select[name=_html5_notifier_popuptype]').val();
        rcmail_show_notification(message);   
    }
    else
    {
        if ("Notification" in window && Notification.permission) {
            if (Notification.permission == 'denied') {
                rcmail.display_message(rcmail.gettext('check_fail_blocked', 'html5_notifier'), 'error');
                return false;
            }
        }
        else if (window.webkitNotifications)
        {
            if (window.webkitNotifications.checkPermission() == 2)
            {
                rcmail.display_message(rcmail.gettext('check_fail_blocked', 'html5_notifier'), 'error');
                return false;
            }
        }
        rcmail.display_message(rcmail.gettext('check_fail', 'html5_notifier'), 'error');
    }
}

function rcmail_browser_notifications_colorate() {
    if ("Notification" in window && Notification.permission) {
        var broco = $('#rcmfd_html5_notifier_browser_conf');
        if (broco)
        {
            switch (Notification.permission)
            {
                case 'granted': broco.css('color', 'green'); break;
                case 'default': broco.css('color', 'orange'); break;
                case 'denied': broco.css('color', 'red'); break;
            }
        }
    }
    else if (window.webkitNotifications)
    {
        var broco = $('#rcmfd_html5_notifier_browser_conf');
        if (broco)
        {
            switch (window.webkitNotifications.checkPermission())
            {
                case 0: broco.css('color', 'green'); break;
                case 1: broco.css('color', 'orange'); break;
                case 2: broco.css('color', 'red'); break;
            }
        }
    }
}

var use_notifications = false;

var rcmail_check_notifications = function(e)
{
    if ("Notification" in window && Notification.permission) {
        if (Notification.permission === "granted") {
            use_notifications = true;
        }
    }
    else if (window.webkitNotifications)
    {
        if (window.webkitNotifications.checkPermission() == 0) {
            use_notifications = true;
        }
    }
    rcmail_browser_notifications_colorate();
}

if (window.rcmail)
{
    rcmail.addEventListener('plugin.showNotification', rcmail_show_notification);
    rcmail.addEventListener('init', rcmail_check_notifications);
}
