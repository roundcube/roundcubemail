import markdownit from 'markdown-it';
import {
    EditorView, keymap, highlightSpecialChars, ViewPlugin,
} from '@codemirror/view';
import { defaultHighlightStyle, syntaxHighlighting, indentOnInput } from '@codemirror/language';
import {
    defaultKeymap, history, historyKeymap, undo, redo,
} from '@codemirror/commands';
import { EditorState, Compartment } from '@codemirror/state';
import { markdown } from '@codemirror/lang-markdown';
import * as Commands from 'codemirror-markdown-commands';
import { materialLight } from '@fsegurai/codemirror-theme-material-light';
import { materialDark } from '@fsegurai/codemirror-theme-material-dark';
import ToolbarButton from './toolbar-button';
import ToolbarPlugin from './toolbar-plugin';

// TODO:
// * Better icons for 'redo' and 'undo' buttons. In Font Awesome v5 Free the good icons are not included.
// * Replace SVG markdown element with markdown icon from Font Awesome after upgrading to a version that includes it.

class Index {
    #defaultTextarea;

    #toolbar;

    #markdownIt;

    #previewIframe;

    #domParser;

    #textEditingToolbarButtons;

    #debounceTimers;

    #editorTheme;

    #view;

    #container;

    // Use a map with fixed callbacks so we can remove the event-listeners later, too.
    #eventListeners = new Map();

    constructor() {
        this.#defaultTextarea = rcmail.gui_objects.messageform.querySelector('#composebody');
        this.#toolbar = document.querySelector('.editor-toolbar');
        this.#toolbar.append(this.#makeMarkdownEditorButton());

        this.#markdownIt = markdownit({
            // Turn '\n' into  '<br>' (required to preserve e.g. email signatures)
            breaks: true,
        });
        this.#editorTheme = new Compartment();

        // Reload from plain text textarea if text was inserted or changed through buttons.
        this.#eventListeners.set('change_identity', () => this.#reloadContentFromDefaultTextarea());
        // If a quick-response is to be inserted, put the textarea cursor at the position where our cursor is, so the
        // response text is actually inserted at the right position.
        this.#eventListeners.set('requestsettings/response-get', () => this.#setTextareaCursorPosition());
        // Reload content from the textarea after a quick-response was inserted.
        this.#eventListeners.set('insert_response', () => this.#reloadContentFromDefaultTextarea());
    }

    get #wantedTheme() {
        if (document.firstElementChild.classList.contains('dark-mode')) {
            return materialDark;
        }
        return materialLight;
    }

    #toggleTheme() {
        if (!this.#view) {
            return;
        }
        this.#view.dispatch({
            effects: this.#editorTheme.reconfigure(this.#wantedTheme),
        });
    }

    #makePreviewIframe(elementToAppendTo) {
        // We're using an iframe to isolate the preview content from any styles of the main document. Those wouldn't be
        // sent with the email, so the preview shouldn't be using them, either.
        const iframe = document.createElement('iframe');
        iframe.id = 'markdown-editor-preview';
        this.#hide(iframe);
        elementToAppendTo.append(iframe);
        // Handle dark-mode by injecting minimal CSS and a class (must be done after connecting the iframe-element to
        // the DOM).
        const iframeDoc = iframe.contentWindow.document;
        if (document.firstElementChild.classList.contains('dark-mode')) {
            iframeDoc.firstElementChild.classList.add('dark-mode');
        }
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = rcmail.assets_path(rcmail.env.markdown_editor_iframe_css_path);
        iframeDoc.head.append(link);
        return iframe;
    }

    #makeMarkdownEditorButton() {
        const markdownEditorButton = document.createElement('a');
        markdownEditorButton.className = 'markdown-editor-start-button';
        markdownEditorButton.tabIndex = '-2';
        markdownEditorButton.href = '#';
        markdownEditorButton.addEventListener('click', (ev) => {
            ev.preventDefault();
            this.startMarkdownEditor();
            // Force saving to mark this content as edited by markdown_editor.
            rcmail.submit_messageform(true);
        });
        markdownEditorButton.title = rcmail.get_label('markdown_editor.editor_button_title');
        const readonly = this.#defaultTextarea.hasAttribute('readonly') || this.#defaultTextarea.hasAttribute('disabled');
        if (readonly) {
            markdownEditorButton.setAttribute('disabled', 'disabled');
        }

        // Use an inline SVG element so we can style it with CSS.
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.setAttribute('viewBox', '0 0 471 289.85');
        const svgTitle = document.createElement('title');
        svgTitle.textContent = 'markdown editor';
        const svgPath1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        svgPath1.setAttribute('d', 'M437,289.85H34a34,34,0,0,1-34-34V34A34,34,0,0,1,34,0H437a34,34,0,0,1,34,34V255.88A34,34,0,0,1,437,289.85ZM34,22.64A11.34,11.34,0,0,0,22.64,34V255.88A11.34,11.34,0,0,0,34,267.2H437a11.34,11.34,0,0,0,11.33-11.32V34A11.34,11.34,0,0,0,437,22.64Z');
        const svgPath2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        svgPath2.setAttribute('d', 'M67.93,221.91v-154h45.29l45.29,56.61L203.8,67.93h45.29v154H203.8V133.6l-45.29,56.61L113.22,133.6v88.31Zm283.06,0-67.94-74.72h45.29V67.93h45.29v79.26h45.29Z');
        svg.append(svgTitle, svgPath1, svgPath2);
        markdownEditorButton.append(svg);

        return markdownEditorButton;
    }

    #makeContainer() {
        const container = document.createElement('div');
        container.id = 'markdown-editor-container';
        return container;
    }

    #makeEditorView(content) {
        const contentChangedNotifier = EditorView.updateListener.of(
            (viewUpdate) => this.#save()
        );
        this.#textEditingToolbarButtons = [
            new ToolbarButton('bold', '\uF032', Commands.bold),
            new ToolbarButton('italic', '\uF033', Commands.italic),
            new ToolbarButton('strike', '\uF0CC', Commands.strike),
            new ToolbarButton('separator', '|'),
            new ToolbarButton('h1', '\uF1DC1', Commands.h1),
            new ToolbarButton('h2', '\uF1DC2', Commands.h2),
            new ToolbarButton('h3', '\uF1DC3', Commands.h3),
            new ToolbarButton('h4', '\uF1DC4', Commands.h4),
            new ToolbarButton('separator', '|'),
            new ToolbarButton('blockquote', '\uF10E', Commands.quote),
            new ToolbarButton('ordered_list', '\uF0CB', Commands.ol),
            new ToolbarButton('unordered_list', '\uF0CA', Commands.ul),
            new ToolbarButton('separator', '|'),
            new ToolbarButton('link', '\uF0C1', Commands.link),
            new ToolbarButton('separator', '|'),
            new ToolbarButton('undo', '\uF0E2', (view) => undo(view)),
            new ToolbarButton('redo', '\uF01E', (view) => redo(view)),
        ];
        const toolbarItems = [
            new ToolbarButton('quit', '\uF00D', (view) => this.stopMarkdownEditor()),
            new ToolbarButton('separator', '|'),
            ...this.#textEditingToolbarButtons,
            new ToolbarButton('space', ''),
            new ToolbarButton('help', '\uF128', (view) => window.open('https://www.markdownguide.org/basic-syntax/', '_blank')),
            new ToolbarButton('preview', '\uF06E', (view) => this.#togglePreview()),
        ];
        const toolbarExtension = ViewPlugin.define((view) => new ToolbarPlugin(view, toolbarItems));

        return new EditorView({
            parent: this.#container,
            state: EditorState.create({
                doc: content,
                extensions: [
                    this.#editorTheme.of(this.#wantedTheme),
                    markdown(),
                    // Replace non-printable characters with placeholders
                    highlightSpecialChars(),
                    // The undo history
                    history(),
                    // Re-indent lines when typing specific input
                    indentOnInput(),
                    // Highlight syntax with a default style
                    syntaxHighlighting(defaultHighlightStyle),
                    keymap.of([
                        // A large set of basic bindings
                        ...defaultKeymap,
                        // Redo/undo keys
                        ...historyKeymap,
                    ]),
                    contentChangedNotifier,
                    toolbarExtension,
                    EditorView.lineWrapping,
                ],
            }),
        });
    }

    startMarkdownEditor() {
        if (!this.#container) {
            this.#container = this.#makeContainer();
            document.querySelector('#composebodycontainer').append(this.#container);
        }

        const content = this.#defaultTextarea.value ?? '';
        this.#view = this.#makeEditorView(content);
        this.#previewIframe = this.#makePreviewIframe(this.#view.scrollDOM);
        this.#setupDarkModeWatcher();

        // Add a new field to mark the content as markdown (pun intended).
        const markdownField = document.createElement('input');
        markdownField.type = 'hidden';
        markdownField.name = '_markdown_editor';
        markdownField.value = '1';
        this.#view.dom.append(markdownField);

        this.#eventListeners.forEach((callback, eventName) => {
            rcmail.addEventListener(eventName, callback);
        });

        // Disable the spellchecker
        rcmail.enable_command('spellcheck', false);

        // Hook into the sending logic to convert the content to HTML
        this.#defaultTextarea.form.addEventListener('submit', (ev) => {
            const is_draft = this.#defaultTextarea.form._draft.value === '1';
            // Only convert to HTML if this actually gets send now. We want drafts to be in plain text to not trigger
            // TinyMCE to take over when editing drafts.
            if (!is_draft) {
                this.#defaultTextarea.value = this.#editorContentAsHTML;
                this.#defaultTextarea.form._is_html.value = '1';
                this.#defaultTextarea.form._markdown_editor.value = '0';
            }
        });

        rcmail.editor.spellcheck_stop();
        this.#hide(this.#defaultTextarea, this.#toolbar);
    }

    stopMarkdownEditor() {
        this.#defaultTextarea.value = this.#editorContent;
        this.#view.destroy();
        this.#eventListeners.forEach((callback, eventName) => {
            rcmail.removeEventListener(eventName, callback);
        });
        this.#stopDarkModeWatcher();
        this.#hide(this.#previewIframe);
        this.#show(this.#defaultTextarea, this.#toolbar);
        // Re-enable the spellchecker
        rcmail.enable_command('spellcheck', true);
        // Force saving to mark this content as *not* edited by markdown_editor.
        rcmail.submit_messageform(true);
    }

    #stopDarkModeWatcher() {
        this.mutationObserver.disconnect();
    }

    #setupDarkModeWatcher() {
        // Callback function to execute when mutations are observed
        const mutationCallback = (mutationList, observer) => {
            for (const mutation of mutationList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    this.#toggleTheme();
                }
            }
        };

        // Create an observer instance linked to the callback function
        this.mutationObserver = new MutationObserver(mutationCallback);

        // Start observing the target node for configured mutations
        this.mutationObserver.observe(document.firstElementChild, { attributes: true, childList: false, subtree: false });
    }

    #setTextareaCursorPosition() {
        this.#defaultTextarea.selectionEnd = this.#view.state.selection.main.head;
    }

    #reloadContentFromDefaultTextarea() {
        this.#debounce(() => {
            this.#view.dispatch({
                changes: {
                    from: 0,
                    to: this.#view.state.doc.length,
                    insert: this.#defaultTextarea.value,
                },
            });
            const cursorPosition = this.#defaultTextarea.selectionEnd;
            if (cursorPosition !== undefined) {
                this.#view.dispatch({
                    selection: {
                        anchor: cursorPosition,
                    },
                    scrollIntoView: true,
                });
            }
            this.#view.focus();
        }, 100);
    }

    #debounce(callback, delay) {
        this.#debounceTimers ??= {};
        if (this.#debounceTimers[callback]) {
            clearTimeout(this.#debounceTimers[callback]);
        }
        this.#debounceTimers[callback] = setTimeout(() => callback(), delay);
    }

    #save() {
        // Debounce writing to the textarea using a delay of 1s.
        this.#debounce(() => {
            this.#defaultTextarea.value = this.#editorContent;
        }, 1000);
    }

    #hide(...elems) {
        elems.forEach((elem) => {
            elem.style.display = 'none';
        });
    }

    #show(...elems) {
        elems.forEach((elem) => {
            elem.style.display = null;
        });
    }

    get #editorContent() {
        return this.#view.state.doc.toString();
    }

    #disableToolbarButtons() {
        this.#textEditingToolbarButtons.forEach((elem) => {
            elem.disabled = 'disabled';
        });
    }

    #enableToolbarButtons() {
        this.#textEditingToolbarButtons.forEach((elem) => {
            elem.disabled = null;
        });
    }

    #togglePreview() {
        const previewButtonElem = document.querySelector('.codemirror-toolbar .toolbar-button-preview');
        if (this.#previewIframe.checkVisibility()) {
            this.#enableToolbarButtons();
            this.#hide(this.#previewIframe);
            this.#show(this.#view.contentDOM);
            previewButtonElem.classList.remove('active');
        } else {
            // markdown-it by default strips raw HTML, so we don't have to purify the result.
            this.#domParser ??= new DOMParser();
            const doc = this.#domParser.parseFromString(this.#editorContentAsHTML, 'text/html');
            this.#previewIframe.contentDocument.body = doc.body;
            this.#disableToolbarButtons();
            this.#previewIframe.style.height = this.#view.scrollDOM.scrollHeight + 'px';
            this.#hide(this.#view.contentDOM);
            this.#show(this.#previewIframe);
            previewButtonElem.classList.add('active');
        }
    }

    get #editorContentAsHTML() {
        // Replace the space in the signature separator ('\n-- \n') by a non-breakable white-space so it gets preserved in HTML.
        return this.#markdownIt.render(this.#editorContent.replace(/\n-- \n/, '\n--\u00A0\n'));
    }
}

rcmail.addEventListener('init', () => {
    window.markdown_editor = new Index();
    if (rcmail.env.start_markdown_editor === true) {
        window.markdown_editor.startMarkdownEditor();
    }
});
