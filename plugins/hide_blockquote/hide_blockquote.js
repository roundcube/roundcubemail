if (window.rcmail) {
    rcmail.addEventListener('init', function ()
    {
        hide_blockquote();
    });
}

function hide_blockquote()
{
    var limit = rcmail.env.blockquote_limit;

    if (limit <= 0) {
        return;
    }

    $('pre > blockquote', $('#messagebody')).each(function ()
    {
        var div, link, q = $(this),
            text = $.trim(q.text()),
            res = text.split(/\n/);

        if (res.length <= limit) {
            // there can be also a block with very long wrapped line
            // assume line height = 15px
            if (q.height() <= limit * 15) {
                return;
            }
        }

        div = $('<blockquote class="blockquote-header">')
            .css({'white-space': 'nowrap', overflow: 'hidden', position: 'relative'})
            .text(res[0]);

        link = $('<span class="blockquote-link"></span>')
            .css({position: 'absolute', 'z-Index': 2})
            .text(rcmail.gettext('hide_blockquote.show'))
            .data('parent', div)
            .click(function ()
            {
                var t = $(this), parent = t.data('parent'), visible = parent.is(':visible');

                t.text(rcmail.gettext(visible ? 'hide' : 'show', 'hide_blockquote'))
                    .detach().appendTo(visible ? q : parent);

                parent[visible ? 'hide' : 'show']();
                q[visible ? 'show' : 'hide']();
            });

        link.appendTo(div);

        // Modify blockquote
        q.hide().css({position: 'relative'}).before(div);
    });
}
