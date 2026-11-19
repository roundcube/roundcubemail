/**
 * Attachment Reminder plugin script
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

function rcmail_get_compose_message() {
    var msg = rcmail.editor.get_content({ nosig: true });

    if (rcmail.editor.is_html()) {
    // Remove quoted content, all HTML tags, and some entities
        msg = msg.replace(/<blockquote[^>]*>(.|[\r\n])*<\/blockquote>/gmi, '')
            .replace(/<[^>]+>/gm, ' ')
            .replace(/&nbsp;/g, ' ');
    } else {
    // Remove quoted content
        msg = msg.replace(/^>.*$/gmi, '');
    }

    return msg;
}

/**
 * Convert one keyword to a regular expression.
 *
 * Keyword syntax:
 *   *      any number of word characters, e.g. "attach*" matches "attach", "attached", "attachments"
 *   (...)  optional letters, e.g. "attachment(s)" matches "attachment" and "attachments"
 *   |      choice inside (...), e.g. "attach(ed|ing|ment)" matches "attach", "attached", "attaching", "attachment"
 * All other characters match literally. A "|" outside (...) matches literally too. If the parentheses do not balance, they match literally too.
 * Returns null if the keyword has no required characters (e.g. "*" or "(s)"), because it would match everything.
 */
function rcmail_keyword_pattern(keyword, word) {
    var i, c, depth = 0, required = 0, out = '';

    for (i = 0; i < keyword.length; i++) {
        c = keyword.charAt(i);

        if (c == '(') {
            depth++;
            out += '(?:';
        } else if (c == ')') {
            if (--depth < 0) {
                break;
            }
            out += ')?';
        } else if (c == '|' && depth) {
            out += '|';
        } else if (c == '*') {
            out += word + '*';
        } else {
            out += RegExp.escape(c);
            if (!depth) {
                required++;
            }
        }
    }

    if (depth !== 0) {
        return RegExp.escape(keyword);
    }

    return required ? out : null;
}

function rcmail_check_message(msg) {
    // A "word character" is any letter, combining mark, digit or underscore, in any script
    var word = '[\\p{L}\\p{M}\\p{N}_]',
        // Scripts that do not separate words with spaces (or attach particles to words),
        // where a whole-word match would miss most real uses of a keyword
        no_boundary = /[\p{Script=Han}\p{Script=Hiragana}\p{Script=Katakana}\p{Script=Hangul}\p{Script=Thai}]/u,
        is_word = new RegExp(word, 'u'),
        keywords = $.map(rcmail.get_label('keywords', 'attachment_reminder').split(','), function (n) {
            n = n.trim();

            if (!n) {
                return null; // drop empty items (e.g. a trailing comma), they would match everything
            }

            // Edge characters decide the whole-word check. A "*" counts as a word character.
            var plain = n.replace(/[()|]/g, '').replace(/\*/g, 'a'),
                first = plain.charAt(0),
                last = plain.charAt(plain.length - 1),
                pattern = rcmail_keyword_pattern(n, word);

            if (!pattern) {
                return null;
            }

            // Match whole words only: "CV" matches "CV", "CV." or "(CV)", but not "CVE"
            return (is_word.test(first) && !no_boundary.test(first) ? '(?<!' + word + ')' : '')
                + pattern
                + (is_word.test(last) && !no_boundary.test(last) ? '(?!' + word + ')' : '');
        });

    // File extensions match anywhere, e.g. "report.pdf" or "letter.docx"
    keywords = keywords.concat([RegExp.escape('.doc'), RegExp.escape('.pdf')]);

    return new RegExp('(' + keywords.join('|') + ')', 'iu').test(msg);
}

function rcmail_have_attachments() {
    return rcmail.env.attachments && $('li', rcmail.gui_objects.attachmentlist).length;
}

function rcmail_attachment_reminder_dialog() {
    var buttons = {};

    buttons[rcmail.get_label('addattachment')] = function () {
        $(this).remove();
        $('#messagetoolbar a.attach, .toolbar a.attach').first().click();
    };
    buttons[rcmail.get_label('send')] = function (e) {
        $(this).remove();
        rcmail.env.attachment_reminder = true;
        rcmail.command('send', '', e);
    };

    rcmail.env.attachment_reminder = false;
    rcmail.show_popup_dialog(
        rcmail.get_label('attachment_reminder.forgotattachment'),
        rcmail.get_label('attachment_reminder.missingattachment'),
        buttons,
        { button_classes: ['mainaction attach', 'send'] }
    );
}


if (window.rcmail) {
    rcmail.addEventListener('beforesend', function (evt) {
        var msg = rcmail_get_compose_message(),
            subject = $('#compose-subject').val();

        if (!rcmail.env.attachment_reminder && !rcmail_have_attachments()
            && (rcmail_check_message(msg) || rcmail_check_message(subject))
        ) {
            rcmail_attachment_reminder_dialog();
            return false;
        }
    });
}
