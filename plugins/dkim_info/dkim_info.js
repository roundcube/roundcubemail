/**
 * DKIM info plugin script
 *
 * The green "aligned pass" verdict is rendered (server-side) as a small badge
 * inside #message-objects, which sits directly above the message body — so on
 * plain-text mail it overlaps the first lines. This moves the badge into the
 * empty right-hand space of the message header instead, where it doesn't
 * cover any text. The non-green banner is left untouched.
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
    var badge = document.querySelector('.dkim-info-badge'),
        header = document.getElementById('message-header');

    if (badge && header) {
        header.appendChild(badge);
    }
});
