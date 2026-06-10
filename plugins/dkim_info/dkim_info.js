/**
 * DKIM info plugin script
 *
 * The green "aligned pass" verdict is rendered (server-side) as a small item
 * inside #message-objects. This moves it into the message header's
 * .header-links row — after Summary / Headers / Plain text — so it reads as a
 * status next to those links instead of floating in a corner. Any non-green
 * verdict keeps its full banner and is left untouched.
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

/* global rcmail */

window.rcmail && rcmail.addEventListener('init', function () {
    var badge = document.querySelector('.dkim-info-link'),
        links = document.querySelector('#message-header .header-links');

    if (badge && links) {
        // Drop any "ui alert / box*" classes the skin may have stamped on it
        // while it was still a #message-objects child, then move it.
        badge.className = 'dkim-info-link';
        links.appendChild(badge);
    }
});
