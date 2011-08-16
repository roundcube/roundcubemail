/**
 * New Mail Notifier plugin script
 *
 * @version 0.1
 * @author Aleksander Machniak <alec@alec.pl>
 */

if (window.rcmail && rcmail.env.task == 'mail') {
    rcmail.addEventListener('plugin.newmail_notifier', newmail_notifier_run);
    rcmail.addEventListener('actionbefore', newmail_notifier_stop);
    rcmail.addEventListener('init', function() {
        // bind to messages list select event, so favicon will be reverted on message preview too
        if (rcmail.message_list)
            rcmail.message_list.addEventListener('select', newmail_notifier_stop);
    });
}

// Executes notification methods
function newmail_notifier_run(prop)
{
    if (prop.basic)
        newmail_notifier_basic();
    if (prop.sound)
        newmail_notifier_sound();
}

// Stops notification
function newmail_notifier_stop(prop)
{
    // revert original favicon
    if (rcmail.env.favicon_href && (!prop || prop.action != 'check-recent')) {
        $('<link rel="shortcut icon" href="'+rcmail.env.favicon_href+'"/>').replaceAll('link[rel="shortcut icon"]');
        rcmail.env.favicon_href = null;
    }
}

// Basic notification: window.focus and favicon change
function newmail_notifier_basic()
{
    window.focus();

    // we cannot simply change a href attribute, we must to replace the link element (at least in FF)
    var link = $('<link rel="shortcut icon" href="plugins/newmail_notifier/favicon.ico"/>'),
        oldlink = $('link[rel="shortcut icon"]');

    rcmail.env.favicon_href = oldlink.attr('href');
    link.replaceAll(oldlink);
}

// Sound notification
function newmail_notifier_sound()
{
    // HTML5
    try {
        var elem = $('<audio src="success.wav" />');
        elem.get(0).play();
    }
    // old method
    catch (e) {
        var elem = $('<embed id="sound" src="success.wav" hidden=true autostart=true loop=false />');
        elem.appendTo($('body'));
        window.setTimeout("$('#sound').remove()", 5000);
    }
}
