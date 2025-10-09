export default class ToolbarPlugin {
    destroy() {
        this.element.remove();
    }

    constructor(view, buttons) {
        this.view = view;
        buttons.forEach((button) => {
            if (typeof button.command === 'function') {
                button.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    if (!button.disabled) {
                        button.command(this.view);
                    }
                });
                button.classList.add('clickable');
            }
        });
        this.element = document.createElement('div');
        this.element.classList.add('codemirror-toolbar');
        this.element.append(...buttons);
        this.view.dom.prepend(this.element);
    }
}
