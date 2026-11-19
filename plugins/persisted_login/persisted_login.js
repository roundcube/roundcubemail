// Run on 'elastic' and all derivate skins.
if (window.rcmail && (window.rcmail.env.skin == 'elastic' || rcmail.env.skin_extends?.includes('elastic'))) {
    rcmail.addEventListener('init', () => {
        const days = rcmail.env.persisted_login_days;
        let txt = rcmail.gettext('switch_text', 'persisted_login');
        txt = txt.replace('#', days);

        const elems = $('<tr>').addClass('form-group row').append(
            $('<td>').addClass('title').hide(),
            $('<td>').addClass('input input-group input-group-lg').append(
                $('<div>').addClass('custom-control custom-switch').css('padding', '1em 0').append(
                    $('<input>').attr({
                        type: 'checkbox',
                        class: 'custom-control-input',
                        id: '_persisted_login',
                        name: '_persisted_login',
                        value: '1',
                    }),
                    $('<label>').attr({ class: 'custom-control-label', for: '_persisted_login' }).text(txt)
                )
            )
        );

        $('#login-form table tbody').append(elems);
    });
}
