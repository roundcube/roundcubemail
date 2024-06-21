// Attach these to the rcube_webmail prototype to make them callable from
// data-event-handlers (see addEventListenerFromElement() in app.js for
// implementation details of those).
//
rcube_webmail.prototype.toggle_html_editor = function (event) {
    this.toggle_editor({ html: event.target.checked }, null, event);
};

rcube_webmail.prototype.toggle_change_subscription = function (elem) {
    if (elem.checked) {
        ref.subscribe(elem.value);
    } else {
        ref.unsubscribe(elem.value);
    }
};

rcube_webmail.prototype.filter_folder = function (elem) {
    ref.folder_filter(elem.value);
};

rcube_webmail.prototype.reset_value_if_inbox = function (elem) {
    if ($(elem).val() == 'INBOX') {
        $(elem).val('');
    }
};

rcube_webmail.prototype.disable_show_images_if_plaintext_preferred = function (elem) {
    $('#rcmfd_show_images').prop('disabled', !elem.checked).val(0);
};

rcube_webmail.prototype.hide_and_show_next = function (elem) {
    $(elem).hide().next().show();
};

// Allow to call rcmail.message_list.clear() without the extra calls in
// rcmail.clear_message_list().
rcube_webmail.prototype.message_list_clear = function (arg) {
    ref.message_list.clear(arg);
};

rcube_webmail.prototype.onerror_set_placeholder_src = function (event, src) {
    var elem = event.target;
    elem.onerror = null;
    if (!src) {
        src = this.env.photo_placeholder;
    }
    elem.src = src;
};

rcube_webmail.prototype.show_sibling_image_attachments = function (elem) {
    $(elem).parents('p.image-attachment').show();
};

rcube_webmail.prototype.reloadForm = function (elem) {
    this.command('save', 'reload', elem.form);
};

rcube_webmail.prototype.toggle_html_signature_editor = function (event) {
    this.toggle_editor({ id: 'rcmfd_signature', html: event.target.checked }, null, event);
};
