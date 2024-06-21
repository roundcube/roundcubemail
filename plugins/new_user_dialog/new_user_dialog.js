rcmail.addEventListener('plugin.new_user_dialog_open', function (title) {
    var newuserdialog = rcmail.show_popup_dialog($('#newuserdialog'), title, [{
        text: rcmail.get_label('save'),
        class: 'mainaction save',
        click: function () {
            var request = {};
            $.each($('form', this).serializeArray(), function () {
                request[this.name] = this.value;
            });

            rcmail.http_post('plugin.newusersave', request, true);
            return false;
        },
    }],
    {
        resizable: false,
        closeOnEscape: false,
        width: 500,
        open: function () { $('#newuserdialog').show(); $('#newuserdialog-name').focus(); },
        beforeClose: function () {
            return false;
        },
    }
    );

    rcmail.addEventListener('plugin.new_user_dialog_close', function (title) {
        newuserdialog.dialog('destroy');
    });
});
