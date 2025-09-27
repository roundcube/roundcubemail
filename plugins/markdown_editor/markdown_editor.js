class MarkdownEditor {
    // State holder for debouncing.
    _saveTimer = null;
    id = 'markdown_editor';

    constructor() {
        // TODO: I18n: https://github.com/nhn/tui.editor/blob/master/docs/en/i18n.md
        // TODO: handle switching from+to mailvelope (optional, it's disabled in TinyMCE as well).
        // TODO: handle toggling Rechtschreibprüfung

        this.editorEl = html('div', { id: 'markdown_editor' });

        // Replace textarea with markdown editor.
        const container = document.getElementById('composebodycontainer');
        this.textarea = container.querySelector('#composebody');
        this.textarea.style.display = 'none';
        container.append(this.editorEl);

        // Make the server handle the input as HTML.
        rcmail.gui_objects.messageform.querySelector('[name="_is_html"]').value = '1';

        let toastUiConfig = {
            usageStatistics: false,
            el: this.editorEl,
            hideModeSwitch: true,
            height: '500px',
            initialValue: this.textarea.value,
            events: {
                change: () => this.save(),
            },
        };

        this.tuiEditor = new toastui.Editor(toastUiConfig);
        this.contenteditableEl = this.editorEl.querySelector('[contenteditable="true"]');

        this.orig_editor = rcmail.editor;
        rcmail.editor = this;
    }

    get_content(args = {}) {
        let text = null;
        if (args.selection) {
            text = this.tuiEditor.getSelectedText();
        }
        if (!text) {
            if (args.format === 'html') {
                text = this.tuiEditor.getHTML();
            } else {
                text = this.tuiEditor.getMarkdown();
            }
        }

        if (args.nosig) {
            sigstart = text.indexOf('-- \n');
            if (sigstart > 0) {
                text = text.substring(0, sigstart);
            }
        }

        return text;
    }

    get_language() {
        return rcmail.env.spell_lang;
    }

    focus() {
        this.tuiEditor.focus();
    }

    is_html() {
        return false;
    }

    replace(input) {
        if (!input) {
            return false;
        }
        if (typeof input !== 'string') {
            input = input.text || '';
        }
        this.tuiEditor.replaceSelection(input);

    }

    save() {
        // Debounce writing to the textarea using a delay of 1s.
        if (this._saveTimer) {
            clearTimeout(this._saveTimer);
        }
        this._saveTimer = setTimeout(() => {
            // this.textarea.value = this.tuiEditor.getMarkdown();
            this.textarea.value = this.tuiEditor.getHTML();
        }, 1000);
    };


    // This gets also called on identity change, in which case a different signature (that belongs to the previously
    // used identity) needs to be replaced.
    change_signature(sig_id, show_sig) {
        if (!show_sig || !rcmail.env.signatures) {
            return;
        }

        // Remove the old signature.
        let message = this.tuiEditor.getMarkdown();
        const old_sig_id = rcmail.env.identity;
        let p;
        if (old_sig_id && rcmail.env.signatures[old_sig_id]) {
            const old_sig_text = rcmail.env.signatures[old_sig_id].text.replace(/\r\n/g, '\n');
            p = rcmail.env.top_posting ? message.indexOf(old_sig_text) : message.lastIndexOf(old_sig_text);
            if (p >= 0) {
                message = message.substring(0, p) + message.substring(p + old_sig_text.length, message.length);
                if (message.endsWith('\n\n')) {
                    // Strip two newlines that this method probably introduced (see below).
                    message = message.substring(0, message.length-2);
                }
                this.tuiEditor.setMarkdown(message, false);
            }
        }

        if (!rcmail.env.signatures[sig_id]) {
            return;
        }

        let sig_text = rcmail.env.signatures[sig_id].text.replace(/\r\n/g, '\n');

        if (p >= 0) {
            // If a signature was removed, insert the new signature at the same position.
            this.tuiEditor.insertText(sig_text);
            return;
        }

        // TODO: Fix this, it would the handling of some edge cases cleaner (switching between identities if the signature isn't the last block of the text anymore).
        // // Insert sig at next line if the line of the cursor is not empty.
        // const currentPosition = this.tuiEditor.getSelection();
        // console.info({ currentPosition });
        // const currentLineNumber = this.tuiEditor.getSelection()[0][0];
        // console.info({ currentLineNumber });
        // // TODO: Can we make this work without using internal APIs (toastMark)?
        // const currentLineText = this.tuiEditor.toastMark.getLineTexts()[currentLineNumber - 1];
        // console.info({ currentLineText });
        // if (currentLineText !== '') {
        //     sig_text = `\n\n${sig_text}`;
        // }

        if (!message) {
            // If the message is empty, insert at the top, preceeded by a blank line.
            this.tuiEditor.moveCursorToStart();
            this.tuiEditor.insertText(`\n\n${sig_text}`);
            return;
        }

        // Insert at the cursor position if top_posting is enabled and sig_below is disabled.
        if (rcmail.env.top_posting && !rcmail.env.sig_below) {
            this.tuiEditor.insertText(`\n${sig_text}\n\n`);
            return;
        }

        // Insert at the bottom
        if (message.slice(-1) !== '\n\n') {
            // Prepend a newline if the last line isn't blank.
            sig_text = `\n\n${sig_text}`;
        }
        this.tuiEditor.moveCursorToEnd();
        this.tuiEditor.insertText(sig_text);

    };
}

const html = (type, ...args) => {
    args = args.flat();
    let content;
    let attributes;
    const elem = document.createElement(type);
    if (args[0]?.constructor?.name === 'Object') {
        attributes = args.shift();
        for (const [key, value] of Object.entries(attributes)) {
            if (key.slice(0, 2) === 'on' && typeof value === 'function') {
                const eventName = key.slice(2).toLowerCase();
                elem.addEventListener(eventName, value);
            } else {
                elem.setAttribute(key, value);
            }
        }
    }
    content = args;
    if (content?.constructor?.name !== 'Array') {
        content = [content];
    }
    for (const thing of content) {
        if (thing) {
            if (typeof thing === 'string') {
                elem.append(document.createTextNode(thing));
            } else if (thing.nodeName) {
                elem.append(thing);
            }
        }
    }
    return elem;
};

rcmail.addEventListener('init', () => new MarkdownEditor());
