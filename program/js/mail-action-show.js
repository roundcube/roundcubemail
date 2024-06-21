rcmail.addEventListener('init', () => {
    $('#attachment-list li a.filename').each((_idx, elem) => {
        elem.addEventListener('click', (ev) => {
            ev.preventDefault();
            if (elem.dataset.emptyattachment) {
                rcmail.alert_dialog(rcmail.get_label('emptyattachment'));
            } else {
                rcmail.command('load-attachment', elem.dataset.mimeId, elem);
            }
            return false;
        });

        if (!elem.title) {
            elem.addEventListener('mouseover', () => {
                rcube_webmail.long_subject_title_ex(elem, 0);
            });
        }
    });
});
