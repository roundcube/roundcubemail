/*
 +-----------------------------------------------------------------------+
 | Roundcube installer client function                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

function toggleblock(id, link) {
    var block = document.getElementById(id);

    return false;
}


function addhostfield() {
    var container = document.getElementById('defaulthostlist');
    var row = document.createElement('div');
    var input = document.createElement('input');
    var link = document.createElement('a');

    input.name = '_imap_host[]';
    input.size = '30';
    link.href = '#';
    link.onclick = function () {
        removehostfield(this.parentNode); return false;
    };
    link.className = 'removelink';
    link.innerHTML = 'remove';

    row.appendChild(input);
    row.appendChild(link);
    container.appendChild(row);
}


function removehostfield(row) {
    var container = document.getElementById('defaulthostlist');
    container.removeChild(row);
}

function addOnclickCallback(id, callback) {
    var elem = document.getElementById(id);
    if (!elem) {
        console.error('No element found with ID "' + id + '", cannot add callback!');
        return false;
    }
    elem.addEventListener('click', callback);
}

document.addEventListener('DOMContentLoaded', function () {
    addOnclickCallback('button-save-config', function () {
        document.getElementById('getconfig_form').submit();
    });

    addOnclickCallback('button-download-config', function () {
        location.href = 'index.php?_getconfig=1';
    });

    addOnclickCallback('button-continue-step-3', function () {
        location.href = './index.php?_step=3';
    });

    addOnclickCallback('remove-host-field', function (event) {
        removehostfield(event.target.parentNode);
        return false;
    });

    addOnclickCallback('add-host-field', function () {
        addhostfield();
    });
});
