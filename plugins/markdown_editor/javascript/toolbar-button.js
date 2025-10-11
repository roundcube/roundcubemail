export default class ToolbarButton extends HTMLElement {
    constructor(name, content, command) {
        super();
        this.name = name;
        this.className = `fa-icon toolbar-button-${name}`;
        this.title = rcmail.get_label(`markdown_editor.toolbar_button_${name}`),
        this.command = command;
        this.append(content);
    }

    set disabled(value) {
        if (value) {
            this.classList.add('disabled');
        } else {
            this.classList.remove('disabled');
        }
    }

    get disabled() {
        return this.classList.contains('disabled');
    }
}
customElements.define('toolbar-button', ToolbarButton);
