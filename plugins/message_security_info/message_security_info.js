/**
 * Message security info plugin script
 *
 * Builds a "DKIM" link in the message header's links row (next to Summary /
 * Headers / Plain text) from the data the server placed in
 * rcmail.env.message_security_info. The link is always shown; its icon colour reflects the
 * verdict. Clicking it opens a popup with the parsed SPF / DKIM / DMARC results
 * and the raw authentication headers.
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
    var info = rcmail.env.message_security_info,
        links = document.querySelector('#message-header .header-links');

    if (!info || !links) {
        return;
    }

    var link = document.createElement('a');
    link.href = '#';
    link.className = 'msgsec-link msgsec-' + (info.status || 'unknown');
    link.textContent = rcmail.get_label('linktitle', 'message_security_info');
    if (info.summary) {
        link.title = info.summary;
    }

    link.addEventListener('click', function (e) {
        e.preventDefault();
        rcmail.simple_dialog(build_table(info), 'message_security_info.authresults', null, {
            cancel_button: 'close',
            width: 560,
        });
    });

    links.appendChild(link);

    // Build the popup table: parsed SPF/DKIM/DMARC rows, then raw header lines.
    // Values are set via textContent, so header content is never interpreted.
    function build_table(info) {
        var table = document.createElement('table'),
            rows = info.rows || [],
            headers = info.headers || [],
            i;

        table.className = 'msgsec-table';

        for (i = 0; i < rows.length; i++) {
            add_row(table, rows[i].label, rows[i].value, false);
        }
        for (i = 0; i < headers.length; i++) {
            add_row(table, headers[i].name, headers[i].value, true);
        }

        return table;
    }

    function add_row(table, label, value, raw) {
        var tr = document.createElement('tr'),
            th = document.createElement('th'),
            td = document.createElement('td');

        th.textContent = label;

        if (raw) {
            tr.className = 'msgsec-raw';
            var code = document.createElement('code');
            code.className = 'msgsec-value';
            // Override Bootstrap's pink code colour directly on the element:
            // an inline style outranks any stylesheet rule (including a
            // higher-specificity !important one in the compiled skin CSS that
            // an external selector here can't reliably beat).
            code.style.setProperty('color', 'inherit', 'important');
            code.textContent = value;
            td.appendChild(code);
        } else {
            td.textContent = value;
        }

        tr.appendChild(th);
        tr.appendChild(td);
        table.appendChild(tr);
    }
});
