/**
 * Help plugin client script
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
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

    rcmail.addEventListener('init', function(prop) {
        if (rcmail.env.contentframe && rcmail.task == 'help') {
            $('#' + rcmail.env.contentframe).on('load error', function(e) {
                // Unlock UI
                rcmail.set_busy(false, null, rcmail.env.frame_lock);
                rcmail.env.frame_lock = null;

                // Select menu item
                if (e.type == 'load') {
                    $(rcmail.env.help_action_item).parents('ul').children().removeClass('selected');
                    $(rcmail.env.help_action_item).parent().addClass('selected');
                }
            });

            try {
                var win = rcmail.get_frame_window(rcmail.env.contentframe);
                if (win && win.location.href.indexOf(rcmail.env.blankpage) >= 0) {
                    show_help_content(rcmail.env.action);
                }
            }
            catch (e) { /* ignore */}
        }
    });
}

function show_help_content(action, event)
{
    var win, target = window,
        url = rcmail.env.help_links[action];

    if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
        target = win;
        url += (url.indexOf('?') > -1 ? '&' : '?') + '_framed=1';
    }

    if (rcmail.env.extwin) {
        url += (url.indexOf('?') > -1 ? '&' : '?') + '_extwin=1';
    }

    if (/^self/.test(url)) {
        url = url.substr(4) + '&_content=1&_task=help&_action=' + action;
    }

    rcmail.env.help_action_item = event ? event.target : $('[rel="' + action + '"]');
    rcmail.show_contentframe(true);
    rcmail.location_href(url, target, true);

    return false;
}
