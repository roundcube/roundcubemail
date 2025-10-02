import { EditorSelection } from '@codemirror/state';

export const imageDataUri = (view) => {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.addEventListener('load', () => {
            const dataUri = reader.result;
            const { state } = view;
            const { doc } = state;

            view.dispatch(state.changeByRange((range) => {
                const { from, to } = range;
                const text = doc.sliceString(from, to);
                const link = `![${text}](${dataUri})`;
                const cursor = from + (text.length ? 3 + text.length : 2);
                return {
                    changes: [
                        {
                            from: from,
                            to: to,
                            insert: link,
                        },
                    ],
                    range: EditorSelection.range(cursor, cursor),
                };
            }));
            view.focus();
        });
        reader.addEventListener('error', (event) => {
            console.error('Error reading file:', event);
        });
        reader.readAsDataURL(file);
    });
    fileInput.click();
};
