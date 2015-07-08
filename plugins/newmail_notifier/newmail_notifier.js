/**
 * New Mail Notifier plugin script
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2013, The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
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
    if (prop.desktop)
        newmail_notifier_desktop(rcmail.gettext('body', 'newmail_notifier'));
}

// Stops notification
function newmail_notifier_stop(prop)
{
    // revert original favicon
    if (rcmail.env.favicon_href && rcmail.env.favicon_changed && (!prop || prop.action != 'check-recent')) {
        $('<link rel="shortcut icon" href="'+rcmail.env.favicon_href+'"/>').replaceAll('link[rel="shortcut icon"]');
        rcmail.env.favicon_changed = 0;
    }

    // Remove IE icon overlay if we're pinned to Taskbar
    try {
        if(window.external.msIsSiteMode()) {
            window.external.msSiteModeClearIconOverlay();
        }
    } catch(e) {}
}

// Basic notification: window.focus and favicon change
function newmail_notifier_basic()
{
    var w = rcmail.is_framed() ? window.parent : window,
        path = rcmail.assets_path('plugins/newmail_notifier');

    w.focus();

    // we cannot simply change a href attribute, we must to replace the link element (at least in FF)
    var link = $('<link rel="shortcut icon">').attr('href', path + '/favicon.ico'),
        oldlink = $('link[rel="shortcut icon"]', w.document);

    if (!rcmail.env.favicon_href)
        rcmail.env.favicon_href = oldlink.attr('href');

    rcmail.env.favicon_changed = 1;
    link.replaceAll(oldlink);

    // Add IE icon overlay if we're pinned to Taskbar
    try {
        if (window.external.msIsSiteMode()) {
            window.external.msSiteModeSetIconOverlay(path + '/overlay.ico', rcmail.gettext('title', 'newmail_notifier'));
        }
    } catch(e) {}
}

// Sound notification
function newmail_notifier_sound()
{
    var elem, src = rcmail.assets_path('plugins/newmail_notifier/sound'),
        plugin = navigator.mimeTypes ? navigator.mimeTypes['audio/mp3'] : {};

    // Internet Explorer does not support wav files,
    // support in other browsers depends on enabled plugins,
    // so we use wav as a fallback
    src += bw.ie || (plugin && plugin.enabledPlugin) ? '.mp3' : '.wav';

    // HTML5
    try {
        elem = $('<audio>').attr('src', src);
        elem.get(0).play();
    }
    // old method
    catch (e) {
        elem = $('<embed id="sound" src="' + src + '" hidden=true autostart=true loop=false />');
        elem.appendTo($('body'));
        window.setTimeout("$('#sound').remove()", 5000);
    }
}

// Desktop notification
// - Require Chrome or Firefox latest version (22+) / 21.0 or older with a plugin
function newmail_notifier_desktop(body)
{
    var timeout = rcmail.env.newmail_notifier_timeout || 10,
        icon = rcmail.assets_path('plugins/newmail_notifier/mail.png');


    // As of 17 June 2013, Chrome/Chromium does not implement Notification.permission correctly that
    // it gives 'undefined' until an object has been created:
    // https://code.google.com/p/chromium/issues/detail?id=163226
    try {
        if (Notification.permission == 'granted' || Notification.permission == undefined) {
            var popup = new Notification(rcmail.gettext('title', 'newmail_notifier'), {
                dir: "auto",
                lang: "",
                body: body,
                tag: "newmail_notifier",
                icon: icon
            });
            popup.onclick = function() {
                this.close();
            }
            setTimeout(function() { popup.close(); }, timeout * 1000);
            if (popup.permission == 'granted') return true;
        }
    }
    catch (e) {
        var dn = window.webkitNotifications;

        if (dn && !dn.checkPermission()) {
            if (rcmail.newmail_popup)
                rcmail.newmail_popup.cancel();
            var popup = window.webkitNotifications.createNotification(icon,
                rcmail.gettext('title', 'newmail_notifier'), body);
            popup.onclick = function() {
                this.cancel();
            }
            popup.show();
            setTimeout(function() { popup.cancel(); }, timeout * 1000);
            rcmail.newmail_popup = popup;
            return true;
        }
    }
    return false;
}

function newmail_notifier_test_desktop()
{
    var txt = rcmail.gettext('testbody', 'newmail_notifier');

    // W3C draft implementation (with fix for Chrome/Chromium)
    try {
        var testNotification = new window.Notification(txt, {tag: "newmail_notifier"});  // Try to show a test message
        if (Notification.permission !== 'granted' || (testNotification.permission && testNotification.permission !== 'granted'))
            newmail_notifier_desktop_authorize();
    }
    // webkit implementation
    catch (e) {
        var dn = window.webkitNotifications;
        if (dn) {
            if (!dn.checkPermission())
                newmail_notifier_desktop(txt);
            else
                dn.requestPermission(function() {
                    if (!newmail_notifier_desktop(txt))
                        rcmail.display_message(rcmail.gettext('desktopdisabled', 'newmail_notifier'), 'error');
                });
        }
        else
            // Everything fails, means the browser has no support
            rcmail.display_message(rcmail.gettext('desktopunsupported', 'newmail_notifier'), 'error');
    }
}

function newmail_notifier_test_basic()
{
    newmail_notifier_basic();
}

function newmail_notifier_test_sound()
{
    newmail_notifier_sound();
}

function newmail_notifier_desktop_authorize() {
        Notification.requestPermission(function(perm) {
                if (perm == 'denied')
                        rcmail.display_message(rcmail.gettext('desktopdisabled', 'newmail_notifier'), 'error');
                if (perm == 'granted')
                        newmail_notifier_test_desktop();  // Test again, which should show test message
        });
}
