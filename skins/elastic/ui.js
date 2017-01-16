
"use strict";

function rcube_elastic_ui()
{
    var mode = 'normal', // one of: wide, normal, tablet, phone
        env = {},
        content_buttons = [];

    var layout = {
        menu: $('#layout > .menu')[0],
        sidebar: $('#layout > .sidebar')[0],
        list: $('#layout > .list')[0],
        content: $('#layout > .content')[0],
    };

    var buttons = {
        menu: $('a.menu-button'),
        back_sidebar: $('a.back-sidebar-button'),
        back_list: $('a.back-list-button'),
    };

    // public methods
    this.register_frame_buttons = register_frame_buttons;

    env.last_selected = $('#layout > div.selected')[0];

    $(window).on('resize', function() {
        clearTimeout(env.resize_timeout);
        env.resize_timeout = setTimeout(function() { resize(); }, 25);
    }).resize();

    $('body').on('click', function() { if (mode == 'phone') $(layout.menu).hide(); });

    // when loading content-frame in small-screen mode display it
    $('#layout > .content').find('iframe').on('load', function(e) {
        var show = !e.target.contentWindow.location.href.endsWith(rcmail.env.blankpage);

        if (show && !$(layout.content).is(':visible')) {
            env.last_selected = layout.content;
            screen_resize();
        }
    });

    // display the list widget after 'list' and 'listgroup' commands
    // @TODO: plugins should be able to do the same
    var list_handler = function(e) {
            if (mode != 'wide') {
                if (rcmail.env.task == 'addressbook' || (rcmail.env.task == 'mail' && !rcmail.env.action)) {
                    show_list();
                }
            }

            // display current folder name in list header
            if (rcmail.env.task == 'mail' && !rcmail.env.action) {
                var name = $.type(e) == 'string' ? e : rcmail.env.mailbox;
                var folder = rcmail.env.mailboxes[name];
                $('#folder-name').text(folder ? folder.name : '');
            }
        };

    rcmail.addEventListener('afterlist', list_handler);
    rcmail.addEventListener('afterlistgroup', list_handler);
    rcmail.addEventListener('message', message_displayed);

    // menu/sidebar button
    buttons.menu.on('click', function() { show_menu(); return false; });
    buttons.back_sidebar.on('click', function() { show_sidebar(); return false; });
    buttons.back_list.on('click', function() {
        if (!layout.list && !layout.sidebar) {
            history.back();
        }
        else {
            hide_content();
        }
        return false;
    });


    // Semantic-UI style
    $('input.button').addClass('ui');
    $('input.button.mainaction').addClass('primary');

    // TODO: Most of this style-related code should not be needed
    // We should implement some features in the core that would
    // allow as to tell the engine to add additional html code/attribs
    $('select').dropdown();

    // Make forms pretty with semantic-ui's accordion widget
    // TODO: Consider using tabs when the page width is big enough
    $('form.propform,.tabbed').each(function() {
        var form = $(this), fieldsets = form.children('fieldset');

        if (fieldsets.length) {
            $(this).addClass('ui styled fluid accordion');
            fieldsets.each(function(i, fieldset) {
                var title = $('<div>').attr('class', 'title' + (i ? '' : ' active'))
                    .html('<i class="dropdown icon"></i>')
                    .append($('<span>').text($('legend', fieldset).text()));
                var content = $('<div>').attr('class', 'content' + (i ? '' : ' active'))
                    .append($(fieldset).children().not('legend'));

                form.append(title).append(content);
                $(fieldset).remove();
            });

            form.accordion({animateChildren: false});
        }
    });

    // Initialize responsive toolbars (have to be before popups init)
    toolbar_init();

    // Initialize menu dropdowns
    $('*[data-popup]').each(function() { popup_init(this); });

    // close popups on click in an iframe on the page
    var close_all_popups = function() {
        $('.ui.popup:visible').each(function() {
            $($(this).data('button')).popup('hide');
        });
    };

    rcube_webmail.set_iframe_events({mousedown: close_all_popups});

    // Move form buttons from the content frame into the frame header (on parent window)
    // TODO: Active button state
    var form_buttons = [];
    $('.formbuttons').children(':not(.cancel)').each(function() {
        var target = $(this);

        // skip non-content buttons
        if (!rcmail.is_framed() && !target.parents('.content').length) {
            return;
        }

        var button = target.clone();

        form_buttons.push(
            button.attr({'onclick': '', disabled: false, id: button.attr('id') + '-clone'})
                .data('target', target)
                .on('click', function(e) { target.click(); })
        );
    });

    if (form_buttons.length) {
        if (rcmail.is_framed())
            parent.UI.register_frame_buttons(form_buttons);
        else
            register_frame_buttons(form_buttons);
    }

    function register_frame_buttons(buttons)
    {
        // we need these buttons really only in phone mode
        if (/*mode == 'phone' && */layout.content && buttons && buttons.length) {
            var header = $(layout.content).children('.header');

            if (header.length) {
                var toolbar = header.children('.buttons');

                if (!toolbar.length)
                    toolbar = $('<span class="buttons">').appendTo(header);

                content_buttons = [];
                toolbar.html('');
                $.each(buttons, function() {
                    toolbar.append(this);
                    content_buttons.push(this.data('target'));
                });
                resize();
            }
        }
    }

    // Initialize search forms (in list headers)
    $('.header > .searchbar').each(function() { searchbar_init(this); });

    // Make login form pretty
    if (rcmail.env.task == 'login') {
        var inputs = [],
            icon_map = {user: 'user', pass: 'lock', host: 'home'},
            table = $('#login-form table');

        $('tr', table).each(function() {
            var input = $('input', this).detach(),
                input_name = input.attr('name').replace('_', ''),
                icon = $('<i>').attr('class', 'icon ' + icon_map[input_name]);

            input.attr('placeholder', $('label', this).text());
            inputs.push($('<div>').attr('class', 'ui left icon input').append([input, icon]));
        });

        table.after(inputs);
    }


    // window resize handler
    function resize()
    {
        var size, width = $(window).width();

        if (width <= 480)
            size = 'phone';
        else if (width > 1200)
            size = 'wide';
        else if (width <= 768)
            size = 'tablet';
        else
            size = 'normal';

        mode = size;
        screen_resize(size);
        display_screen_size(); // debug info
    };

    // for development only
    function display_screen_size()
    {
        if ($('body.iframe').length)
            return;

        var div = $('#screen-size'), win = $(window);
        if (!div.length) {
            div = $('<div>').attr({
                id: 'screen-size',
                style: 'position:absolute;display:block;right:0;bottom:0;z-index:100;'
                    + 'opacity:0.5;color:white;background-color:black;white-space:nowrap'
            }).appendTo(document.body);
        }

        div.text(win.width() + ' x ' + win.height() + ' (' + mode + ')');
    };

    function screen_resize()
    {
        switch (mode) {
            case 'phone': screen_resize_phone(); break;
            case 'tablet': screen_resize_tablet(); break;
            case 'normal': screen_resize_normal(); break;
            case 'wide': screen_resize_wide(); break;
        }
    };

    function screen_resize_phone()
    {
        screen_resize_small();

        $(layout.menu).hide();
    };

    function screen_resize_tablet()
    {
        screen_resize_small();

        $(layout.menu).css('display', 'flex');
    };

    function screen_resize_small()
    {
        var show, got_content = false;;

        if (layout.content) {
            show = got_content = layout.content === env.last_selected;

            $(layout.content).css('display', show ? 'flex' : 'none');
        }

        if (layout.list) {
            show = layout.list === env.last_selected && !got_content;
            $(layout.list).css('display', show ? 'flex' : 'none');
        }

        if (layout.sidebar) {
            show = (layout.sidebar === env.last_selected || !layout.list) && !got_content;
            $(layout.sidebar).css('display', show ? 'flex' : 'none');
        }

        if (got_content) {
            buttons.back_list.show();
        }

        $('.header > ul.toolbar', layout.content).addClass('hidden ui popup');

        $.each(content_buttons, function() { $(this).hide(); });
    };

    function screen_resize_normal()
    {
        var show;

        if (layout.list) {
            show = layout.list === env.last_selected || layout.sidebar !== env.last_selected;
            $(layout.list).css('display', show ? 'flex' : 'none');
        }
        if (layout.sidebar) {
            show = layout.sidebar === env.last_selected || !layout.list;
            $(layout.sidebar).css('display', show ? 'flex' : 'none');
        }

        $(layout.content).css('display', 'flex');
        $(layout.menu).css('display', 'flex');
        buttons.back_list.hide();
        $.each(content_buttons, function() { $(this).show(); });
        $('ul.toolbar.ui.popup').removeClass('hidden ui popup');
    };

    function screen_resize_wide()
    {
        $.each(layout, function(name, item) { $(item).css('display', 'flex'); });
        buttons.back_list.hide();
        $.each(content_buttons, function() { $(this).show(); });
        $('ul.toolbar.ui.popup').removeClass('hidden ui popup');
    };

    function show_sidebar()
    {
        // show sidebar and hide list
        $(layout.list).hide();
        $(layout.sidebar).css('display', 'flex');
    };

    function show_list()
    {
        // show list and hide sidebar
        $(layout.sidebar).hide();
        $(layout.list).css('display', 'flex');
    };

    function hide_content()
    {
        // show sidebar or list, hide content frame
        //$(layout.content).hide();
        env.last_selected = layout.list || layout.sidebar;
        screen_resize();

        // reset content frame, so we can load it again
        rcmail.show_contentframe(false);
        // or env.content_window.location.href = rcmail.env.blankpage; ?

        // now we have to unselect selected row on the list
        // TODO: do this with some magic, so it works for plugins UI
        // e.g. we could store list widget reference in list container data
        if (rcmail.env.task == 'settings' && !rcmail.env.action) {
            rcmail.sections_list.clear_selection();
        }
        else if (rcmail.env.task == 'addressbook' && !rcmail.env.action) {
            rcmail.contact_list.clear_selection();
        }
    };

    function show_menu()
    {
        // show menu widget
        var menu = $(layout.menu),
            phone_display = menu.is(':visible') && mode == 'phone' ? 'none' : 'block';

        menu.css('display', mode == 'phone' ? phone_display : 'flex');
    };

    /**
     * Triggered when a UI message is displayed
     */
    function message_displayed(p)
    {
        var icon, classes = 'ui icon message',
            map = {
                information: ['success', 'info circle icon'],
                confirmation: ['success', 'info circle icon'],
                notice: ['', 'info circle icon'],
                error: ['negative', 'warning circle icon'],
                warning: ['negative', 'warning sign icon'],
                loading: ['', 'notched circle loading icon']
            };

        if (icon = map[p.type]) {
            if (icon[0])
                classes += ' ' + icon[0];
            $('<i>').attr('class', icon[1]).prependTo(p.object);
        }

        $(p.object).addClass(classes);

/*
        var siblings = $(p.object).siblings('div');
        if (siblings.length)
            $(p.object).insertBefore(siblings.first());

        // show a popup dialog on errors
        if (p.type == 'error' && rcmail.env.task != 'login') {
            // hide original message object, we don't want both
            rcmail.hide_message(p.object);
        }
*/
    };

    /**
     * Initializes searchbar widget
     */
    function searchbar_init(bar)
    {
        var input = $('input', bar),
            button = $('a.button.search', bar),
            form = $('form', bar),
            all_elements = $('form, a.button.options, a.button.reset', bar),
            is_search_pending = function() {
                // TODO: This have to be improved to detect real searching state
                //       There are cases when search is active but the input is empty
                return input.val();
            },
            hide_func = function() {
                // TODO: This animation in Chrome does not look as good as in Firefox
                $(bar).animate({'width': '0'}, 200, 'swing', function() {
                    all_elements.hide();
                    $(bar).width('auto'); // fixes search button position in Chrome
                    button[is_search_pending() ? 'addClass' : 'removeClass']('active')
                        .css('display', 'inline-block')
                        .focus();
                });
            };

        if (is_search_pending()) {
            button.addClass('active');
        }

        // Display search form (with animation effect)
        button.on('click', function() {
            $(bar).animate({'width': '100%'}, 200);
            all_elements.css('display', 'table-cell');
            button.hide();
            input.focus();
        });

        // Search reset action
        $('a.button.reset', bar).on('click', function() {
            // for treelist widget's search setting val and keyup.treelist is needed
            // in normal search form reset-search command will do the trick
            // TODO: This calls for some generalization, what about two searchboxes on a page?
            input.val('').change().trigger('keyup.treelist', {keyCode: 27});
            hide_func();
        });

        // These will hide the form, but not reset it
        rcube_webmail.set_iframe_events({mousedown: hide_func});
        $('body').on('mousedown', function(e) {
            if (!button.is(':visible') && $.inArray(bar, $(e.target).parents()) == -1) {
                hide_func();
            }
        });
    };

    /**
     * Converts toolbar menu into popup-menu for small screens
     */
    function toolbar_init()
    {
        if (env.got_smart_toolbar) {
            return;
        }

        env.got_smart_toolbar = true;

        // TODO: if the toolbar contains "global or list only" buttons
        //       another popup menu with these options should be created
        //       on the list (or sidebar if there's no list element).
        // TODO: spacer item
        // TODO: dropbutton item
        // TODO: a way to inject buttons to the menu from content iframe

        var items = [], buttons_with_popup = [];

        // convert toolbar to a popup list
        $('.header > .toolbar', layout.content).each(function() {
            var toolbar = $(this);

            toolbar.children().each(function() {
                var button = $(this).detach();

                $('[data-popup]', button).each(function() {
                    buttons_with_popup.push(this);
                });

                items.push($('<li role="menuitem">').append(button));
            });
        });

        // append the new toolbar and menu button
        if (items.length) {
            var menu_button = $('<a class="button toolbar-menu-button" href="#menu">')
                .html('<i class="icon ellipsis vertical"></i>')
                .attr({'data-popup': 'toolbar-menu', 'data-popup-pos': 'bottom right'});

            $(layout.content).children('.header')
                // TODO: copy original toolbar attributes (class, role, aria-*)
                .append($('<ul>').attr({'class': 'toolbar ui popup', id: 'toolbar-menu'}).append(items))
                .append(menu_button);

            // TODO: A menu converted to a popup will be hidden on click in the body
            //       we do not want that
        }
    }

    /**
     * Initialize a popup for specified button element
     */
    function popup_init(item)
    {
        var popup_id = $(item).data('popup'),
            popup = $('#' + popup_id)[0],
            popup_position = $(item).data('popup-pos') || 'bottom left';

        $(item).attr({
                'aria-haspopup': 'true',
                'aria-expanded': 'false',
                'aria-owns': popup_id
            })
            .popup({
                popup: popup,
                exclusive: true,
                on: 'click',
                position: popup_position,
                lastResort: true
            });

        // TODO: Set aria attributes on menu show/hide
        // TODO: Set popup height so it is less that window height
        $(popup).attr('aria-hidden', 'true')
            .data('button', item);
    }
}

var UI = new rcube_elastic_ui();
