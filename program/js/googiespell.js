/**
 * Roundcube SpellCheck script
 *
 * jQuery'fied spell checker based on GoogieSpell 4.0
 * (which was published under GPL "version 2 or any later version")
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2006 Amir Salihefendic
 * Copyright (C) The Roundcube Dev Team
 * Copyright (C) Kolab Systems AG
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 *
 * @author 4mir Salihefendic <amix@amix.dk>
 * @author Aleksander Machniak - <alec [at] alec.pl>
 */

var GOOGIE_CUR_LANG,
    GOOGIE_DEFAULT_LANG = 'en';

function GoogieSpell(img_dir, server_url, has_dict)
{
    var ref = this,
        cookie_value = rcmail.get_cookie('language');

    GOOGIE_CUR_LANG = cookie_value != null ? cookie_value : GOOGIE_DEFAULT_LANG;

    this.array_keys = function(arr) {
        var res = [];
        for (var key in arr) { res.push([key]); }
        return res;
    }

    this.img_dir = img_dir;
    this.server_url = server_url;

    this.org_lang_to_word = {
        "da": "Dansk", "de": "Deutsch", "en": "English",
        "es": "Español", "fr": "Français", "it": "Italiano",
        "nl": "Nederlands", "pl": "Polski", "pt": "Português",
        "ru": "Русский", "fi": "Suomi", "sv": "Svenska"
    };
    this.lang_to_word = this.org_lang_to_word;
    this.langlist_codes = this.array_keys(this.lang_to_word);
    this.show_change_lang_pic = false; // roundcube mod.
    this.change_lang_pic_placement = 'right';
    this.report_state_change = true;

    this.ta_scroll_top = 0;
    this.el_scroll_top = 0;

    this.lang_chck_spell = "Check spelling";
    this.lang_revert = "Revert to";
    this.lang_close = "Close";
    this.lang_rsm_edt = "Resume editing";
    this.lang_no_error_found = "No spelling errors found";
    this.lang_no_suggestions = "No suggestions";
    this.lang_learn_word = "Add to dictionary";

    this.use_ok_pic = false; // added by roundcube
    this.show_spell_img = false; // roundcube mod.
    this.decoration = true;
    this.use_close_btn = false;
    this.edit_layer_dbl_click = true;
    this.report_ta_not_found = true;

    // Extensions
    this.custom_ajax_error = null;
    this.custom_no_spelling_error = null;
    this.extra_menu_items = [];
    this.custom_spellcheck_starter = null;
    this.main_controller = true;
    this.has_dictionary = has_dict;

    // Observers
    this.lang_state_observer = null;
    this.spelling_state_observer = null;
    this.show_menu_observer = null;
    this.all_errors_fixed_observer = null;

    // Focus links - used to give the text box focus
    this.use_focus = false;
    this.focus_link_t = null;
    this.focus_link_b = null;

    // Counters
    this.cnt_errors = 0;
    this.cnt_errors_fixed = 0;

    // Set document's onclick to hide the language and error menu
    $(document).click(function(e) {
        var target = $(e.target);
        if (target.attr('googie_action_btn') != '1' && ref.isErrorWindowShown())
            ref.hideErrorWindow();
    });


this.decorateTextarea = function(id)
{
    this.text_area = typeof id === 'string' ? document.getElementById(id) : id;

    if (this.text_area) {
        if (!this.spell_container && this.decoration) {
            var table = document.createElement('table'),
                tbody = document.createElement('tbody'),
                tr = document.createElement('tr'),
                spell_container = document.createElement('td'),
                r_width = this.isDefined(this.force_width) ? this.force_width : this.text_area.offsetWidth,
                r_height = this.isDefined(this.force_height) ? this.force_height : 16;

            tr.appendChild(spell_container);
            tbody.appendChild(tr);
            $(table).append(tbody).insertBefore(this.text_area).width('100%').height(r_height);
            $(spell_container).height(r_height).width(r_width).css('text-align', 'right');

            this.spell_container = spell_container;
        }

        this.checkSpellingState();
    }
    else if (this.report_ta_not_found) {
        rcmail.alert_dialog('Text area not found');
    }
};

//////
// API Functions (the ones that you can call)
/////
this.setSpellContainer = function(id)
{
    this.spell_container = typeof id === 'string' ? document.getElementById(id) : id;
};

this.setLanguages = function(lang_dict)
{
    this.lang_to_word = lang_dict;
    this.langlist_codes = this.array_keys(lang_dict);
};

this.setCurrentLanguage = function(lan_code)
{
    GOOGIE_CUR_LANG = lan_code;

    //Set cookie
    rcmail.set_cookie('language', lan_code, false);
};

this.setForceWidthHeight = function(width, height)
{
    // Set to null if you want to use one of them
    this.force_width = width;
    this.force_height = height;
};

this.setDecoration = function(bool)
{
    this.decoration = bool;
};

this.dontUseCloseButtons = function()
{
    this.use_close_btn = false;
};

this.appendNewMenuItem = function(name, call_back_fn, checker)
{
    this.extra_menu_items.push([name, call_back_fn, checker]);
};

this.setFocus = function()
{
    try {
        this.focus_link_b.focus();
        this.focus_link_t.focus();
        return true;
    }
    catch(e) {
        return false;
    }
};


//////
// Set functions (internal)
/////
this.setStateChanged = function(current_state)
{
    this.state = current_state;
    if (this.spelling_state_observer != null && this.report_state_change)
        this.spelling_state_observer(current_state, this);
};

this.setReportStateChange = function(bool)
{
    this.report_state_change = bool;
};


//////
// Request functions
/////
this.getUrl = function()
{
    return this.server_url + GOOGIE_CUR_LANG;
};

this.escapeSpecial = function(val)
{
    return val ? val.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
};

this.createXMLReq = function (text)
{
    return '<?xml version="1.0" encoding="utf-8" ?>'
        + '<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">'
        + '<text>' + text + '</text></spellrequest>';
};

this.spellCheck = function(ignore)
{
    this.prepare(ignore);

    var req_text = this.escapeSpecial(this.original_text),
        ref = this;

    $.ajax({ type: 'POST', url: this.getUrl(), data: this.createXMLReq(req_text), dataType: 'text',
        error: function(o) {
            if (ref.custom_ajax_error) {
                ref.custom_ajax_error(ref);
            }
            else {
                rcmail.alert_dialog('An error was encountered on the server. Please try again later.');
            }
            if (ref.main_controller) {
                $(ref.spell_span).remove();
                ref.removeIndicator();
            }
            ref.checkSpellingState();
        },
        success: function(data) {
            ref.processData(data);
            if (!ref.results.length) {
                if (!ref.custom_no_spelling_error)
                    ref.flashNoSpellingErrorState();
                else
                    ref.custom_no_spelling_error(ref);
            }
            ref.removeIndicator();
        }
    });
};

this.learnWord = function(word, id)
{
    word = this.escapeSpecial(word.innerHTML);

    var ref = this,
        req_text = '<?xml version="1.0" encoding="utf-8" ?><learnword><text>' + word + '</text></learnword>';

    $.ajax({ type: 'POST', url: this.getUrl(), data: req_text, dataType: 'text',
        error: function(o) {
            if (ref.custom_ajax_error) {
                ref.custom_ajax_error(ref);
            }
            else {
                rcmail.alert_dialog('An error was encountered on the server. Please try again later.');
            }
        },
        success: function(data) {
        }
    });
};


//////
// Spell checking functions
/////
this.prepare = function(ignore, no_indicator)
{
    this.cnt_errors_fixed = 0;
    this.cnt_errors = 0;
    this.setStateChanged('checking_spell');
    this.original_text = '';

    if (!no_indicator && this.main_controller)
        this.appendIndicator(this.spell_span);

    this.error_links = [];
    this.ta_scroll_top = this.text_area.scrollTop;
    this.ignore = ignore;

    var area = $(this.text_area);

    if (area.val() == '' || ignore) {
        if (!this.custom_no_spelling_error)
            this.flashNoSpellingErrorState();
        else
            this.custom_no_spelling_error(this);
        this.removeIndicator();
        return;
    }

    var height = $(area).css('box-sizing') == 'border-box' ? this.text_area.offsetHeight : $(area).height();

    this.createEditLayer(area.width(), height);
    this.createErrorWindow();

    $('body').append(this.error_window);

    if (this.main_controller)
        $(this.spell_span).off('click');

    this.original_text = area.val();
};

this.parseResult = function(r_text)
{
    // Returns an array: result[item] -> ['attrs'], ['suggestions']
    var re_split_attr_c = /\w+="(\d+|true)"/g,
        re_split_text = /\t/g,
        matched_c = r_text.match(/<c[^>]*>[^<]*<\/c>/g),
        results = [];

    if (matched_c == null)
        return results;

    for (var i=0, len=matched_c.length; i < len; i++) {
        var item = [];
        this.errorFound();

        // Get attributes
        item['attrs'] = [];
        var c_attr, val,
            split_c = matched_c[i].match(re_split_attr_c);
        for (var j=0; j < split_c.length; j++) {
            c_attr = split_c[j].split(/=/);
            val = c_attr[1].replace(/"/g, '');
            item['attrs'][c_attr[0]] = val != 'true' ? parseInt(val) : val;
        }

        // Get suggestions
        item['suggestions'] = [];
        var only_text = matched_c[i].replace(/<[^>]*>/g, ''),
            split_t = only_text.split(re_split_text);
        for (var k=0; k < split_t.length; k++) {
            if(split_t[k] != '')
            item['suggestions'].push(split_t[k]);
        }
        results.push(item);
    }

    return results;
};

this.processData = function(data)
{
    this.results = this.parseResult(data);
    if (this.results.length) {
        this.showErrorsInIframe();
        this.resumeEditingState();
    }
};

//////
// Error menu functions
/////
this.createErrorWindow = function()
{
    this.error_window = document.createElement('div');
    $(this.error_window).addClass('googie_window popupmenu').attr('googie_action_btn', '1');
};

this.isErrorWindowShown = function()
{
    return $(this.error_window).is(':visible');
};

this.hideErrorWindow = function()
{
    $(this.error_window).hide();
    $(this.error_window_iframe).hide();
};

this.updateOriginalText = function(offset, old_value, new_value, id)
{
    var part_1 = this.original_text.substring(0, offset),
        part_2 = this.original_text.substring(offset+old_value.length),
        add_2_offset = new_value.length - old_value.length;

    this.original_text = part_1 + new_value + part_2;
    $(this.text_area).val(this.original_text);
    for (var j=0, len=this.results.length; j<len; j++) {
        // Don't edit the offset of the current item
        if (j != id && j > id)
            this.results[j]['attrs']['o'] += add_2_offset;
    }
};

this.saveOldValue = function(elm, old_value) {
    elm.is_changed = true;
    elm.old_value = old_value;
};

this.createListSeparator = function()
{
    return $('<li>').html('&nbsp;').attr('googie_action_btn', '1')
        .css({'cursor': 'default', 'font-size': '3px', 'border-top': '1px solid #ccc', 'padding-top': '3px'})
        .get(0);
};

this.correctError = function(id, elm, l_elm, rm_pre_space)
{
    var old_value = elm.innerHTML,
        new_value = l_elm.nodeType == 3 ? l_elm.nodeValue : l_elm.innerHTML,
        offset = this.results[id]['attrs']['o'];

    if (rm_pre_space) {
        var pre_length = elm.previousSibling.innerHTML;
        elm.previousSibling.innerHTML = pre_length.slice(0, pre_length.length-1);
        old_value = " " + old_value;
        offset--;
    }

    this.hideErrorWindow();
    this.updateOriginalText(offset, old_value, new_value, id);

    $(elm).html(new_value).css('color', 'green').attr('is_corrected', true);

    this.results[id]['attrs']['l'] = new_value.length;

    if (!this.isDefined(elm.old_value))
        this.saveOldValue(elm, old_value);

    this.errorFixed();
};

this.ignoreError = function(elm, id)
{
    // @TODO: ignore all same words
    $(elm).removeAttr('class').css('color', '').off();
    this.hideErrorWindow();
};

this.showErrorWindow = function(elm, id)
{
    if (this.show_menu_observer)
        this.show_menu_observer(this);

    var ref = this,
        pos = $(elm).offset(),
        list = document.createElement('ul');

    $(this.error_window).html('');
    $(list).addClass('googie_list toolbarmenu').attr('googie_action_btn', '1');

    // Build up the result list
    var suggestions = this.results[id]['suggestions'],
        offset = this.results[id]['attrs']['o'],
        len = this.results[id]['attrs']['l'],
        item, dummy;

    // [Add to dictionary] button
    if (this.has_dictionary && !$(elm).attr('is_corrected')) {
        dummy = $('<a>').text(this.lang_learn_word).addClass('googie_add_to_dict active');

        $('<li>').attr('googie_action_btn', '1').css('cursor', 'default')
            .mouseover(ref.item_onmouseover)
            .mouseout(ref.item_onmouseout)
            .click(function(e) {
                ref.learnWord(elm, id);
                ref.ignoreError(elm, id);
            })
            .append(dummy)
            .appendTo(list);
    }

    for (var i=0, len=suggestions.length; i < len; i++) {
        dummy = $('<a>').html(suggestions[i]).addClass('active');

        $('<li>').mouseover(this.item_onmouseover).mouseout(this.item_onmouseout)
            .click(function(e) { ref.correctError(id, elm, e.target.firstChild); })
            .append(dummy)
            .appendTo(list);
    }

    // The element is changed, append the revert
    if (elm.is_changed && elm.innerHTML != elm.old_value) {
        var old_value = elm.old_value;

        dummy = $('<a>').addClass('googie_list_revert active').html(this.lang_revert + ' ' + old_value);

        $('<li>').mouseover(this.item_onmouseover).mouseout(this.item_onmouseout)
            .click(function(e) {
                ref.updateOriginalText(offset, elm.innerHTML, old_value, id);
                $(elm).removeAttr('is_corrected').css('color', '#b91414').html(old_value);
                ref.hideErrorWindow();
            })
            .append(dummy)
            .appendTo(list);
    }

    // Append the edit box
    var edit_row = document.createElement('li'),
        edit_input = document.createElement('input'),
        ok_pic = document.createElement('button'),
        edit_form = document.createElement('form');

    var onsub = function () {
        if (edit_input.value != '') {
            if (!ref.isDefined(elm.old_value))
                ref.saveOldValue(elm, elm.innerHTML);

            ref.updateOriginalText(offset, elm.innerHTML, edit_input.value, id);
            $(elm).attr('is_corrected', true).css('color', 'green').text(edit_input.value);
            ref.hideErrorWindow();
        }
        return false;
    };

    $(edit_input).width(120).val($(elm).text()).attr('googie_action_btn', '1');
    $(edit_row).css('cursor', 'default').attr('googie_action_btn', '1')
        .on('click', function() { return false; });

    // roundcube modified image use
    if (this.use_ok_pic) {
        $('<img>').attr('src', this.img_dir + 'ok.gif')
            .width(32).height(16)
            .css({cursor: 'pointer', 'margin-left': '2px', 'margin-right': '2px'})
            .appendTo(ok_pic);
    }
    else {
        $(ok_pic).text('OK');
    }

    $(ok_pic).addClass('mainaction save googie_ok_button btn-sm').click(onsub);

    $(edit_form).attr('googie_action_btn', '1')
        .css({'cursor': 'default', 'white-space': 'nowrap'})
        .submit(onsub)
        .append(edit_input)
        .append(ok_pic)
        .appendTo(edit_row);

    list.appendChild(edit_row);

    // Append extra menu items
    if (this.extra_menu_items.length > 0)
        list.appendChild(this.createListSeparator());

    var loop = function(i) {
        if (i < ref.extra_menu_items.length) {
            var e_elm = ref.extra_menu_items[i];

            if (!e_elm[2] || e_elm[2](elm, ref)) {
                var e_row = document.createElement('tr'),
                  e_col = document.createElement('td');

                $(e_col).html(e_elm[0])
                    .mouseover(ref.item_onmouseover)
                    .mouseout(ref.item_onmouseout)
                    .click(function() { return e_elm[1](elm, ref) });

                e_row.appendChild(e_col);
                list.appendChild(e_row);
            }
            loop(i+1);
        }
    };

    loop(0);
    loop = null;

    //Close button
    if (this.use_close_btn) {
        list.appendChild(this.createCloseButton(this.hideErrorWindow));
    }

    this.error_window.appendChild(list);

    // roundcube plugin api hook
    rcmail.triggerEvent('googiespell_create', {obj: this.error_window});

    // calculate and set position
    var height = $(this.error_window).height(),
        width = $(this.error_window).width(),
        pageheight = $(document).height(),
        pagewidth = $(document).width(),
        top = pos.top + height + 20 < pageheight ? pos.top + 20 : pos.top - height,
        left = pos.left + width < pagewidth ? pos.left : pos.left - width;

    if (left < 0) left = 0;
    if (top < 0) top = 0;

    $(this.error_window).css({'top': top+'px', 'left': left+'px', position: 'absolute'}).show();

    // Dummy for IE - dropdown bug fix
    if (document.all && !window.opera) {
        if (!this.error_window_iframe) {
            var iframe = $('<iframe>').css({'position': 'absolute', 'z-index': -1});
            $('body').append(iframe);
            this.error_window_iframe = iframe;
        }

        $(this.error_window_iframe)
            .css({'top': this.error_window.offsetTop, 'left': this.error_window.offsetLeft,
                'width': this.error_window.offsetWidth, 'height': this.error_window.offsetHeight})
            .show();
    }
};


//////
// Edit layer (the layer where the suggestions are stored)
//////
this.createEditLayer = function(width, height)
{
    this.edit_layer = document.createElement('div');
    $(this.edit_layer).addClass('googie_edit_layer').attr('id', 'googie_edit_layer')
        .width(width).height(height);

    if (this.text_area.nodeName.toLowerCase() != 'input' || $(this.text_area).val() == '') {
        $(this.edit_layer).css('overflow', 'auto');
    } else {
        $(this.edit_layer).css('overflow', 'hidden');
    }

    var ref = this;

    if (this.edit_layer_dbl_click) {
        $(this.edit_layer).dblclick(function(e) {
            if (e.target.className != 'googie_link' && !ref.isErrorWindowShown()) {
                ref.resumeEditing();
                var fn1 = function() {
                    $(ref.text_area).focus();
                    fn1 = null;
                };
                setTimeout(fn1, 10);
            }
            return false;
        });
    }
};

this.resumeEditing = function()
{
    this.setStateChanged('ready');

    if (this.edit_layer)
        this.el_scroll_top = this.edit_layer.scrollTop;

    this.hideErrorWindow();

    if (this.main_controller)
        $(this.spell_span).removeClass().addClass('googie_no_style');

    if (!this.ignore) {
        if (this.use_focus) {
            $(this.focus_link_t).remove();
            $(this.focus_link_b).remove();
        }

        $(this.edit_layer).remove();
        $(this.text_area).show();

        if (this.el_scroll_top != undefined)
            this.text_area.scrollTop = this.el_scroll_top;
    }
    this.checkSpellingState(false);
};

this.createErrorLink = function(text, id)
{
    var elm = document.createElement('span'),
        ref = this,
        d = function (e) {
            ref.showErrorWindow(elm, id);
            d = null;
            return false;
        };

    $(elm).html(text).addClass('googie_link').click(d).removeAttr('is_corrected')
        .attr({'googie_action_btn' : '1', 'g_id' : id});

    return elm;
};

this.createPart = function(txt_part)
{
    if (txt_part == " ")
        return document.createTextNode(" ");

    txt_part = this.escapeSpecial(txt_part);
    txt_part = txt_part.replace(/\n/g, "<br>");
    txt_part = txt_part.replace(/    /g, " &nbsp;");
    txt_part = txt_part.replace(/^ /g, "&nbsp;");
    txt_part = txt_part.replace(/ $/g, "&nbsp;");

    var span = document.createElement('span');
    $(span).html(txt_part);
    return span;
};

this.showErrorsInIframe = function()
{
    var output = document.createElement('div'),
        pointer = 0,
        results = this.results;

    if (results.length > 0) {
        for (var i=0, length=results.length; i < length; i++) {
            var offset = results[i]['attrs']['o'],
                len = results[i]['attrs']['l'],
                part_1_text = this.original_text.substring(pointer, offset),
                part_1 = this.createPart(part_1_text);

            output.appendChild(part_1);
            pointer += offset - pointer;

            // If the last child was an error, then insert some space
            var err_link = this.createErrorLink(this.original_text.substr(offset, len), i);
            this.error_links.push(err_link);
            output.appendChild(err_link);
            pointer += len;
        }

        // Insert the rest of the original text
        var part_2_text = this.original_text.substr(pointer, this.original_text.length),
            part_2 = this.createPart(part_2_text);

        output.appendChild(part_2);
    }
    else
        output.innerHTML = this.original_text;

    $(output).css('text-align', 'left');

    var me = this;
    if (this.custom_item_evaluator)
        $.map(this.error_links, function(elm){me.custom_item_evaluator(me, elm)});

    $(this.edit_layer).append(output);

    // Hide text area and show edit layer
    $(this.text_area).hide();
    $(this.edit_layer).insertBefore(this.text_area);

    if (this.use_focus) {
        this.focus_link_t = this.createFocusLink('focus_t');
        this.focus_link_b = this.createFocusLink('focus_b');

        $(this.focus_link_t).insertBefore(this.edit_layer);
        $(this.focus_link_b).insertAfter(this.edit_layer);
    }

//    this.edit_layer.scrollTop = this.ta_scroll_top;
};

this.deHighlightCurSel = function()
{
    $(this.lang_cur_elm).removeClass().addClass('googie_list_onout');
};

this.highlightCurSel = function()
{
    if (GOOGIE_CUR_LANG == null)
        GOOGIE_CUR_LANG = GOOGIE_DEFAULT_LANG;
    for (var i=0; i < this.lang_elms.length; i++) {
        if ($(this.lang_elms[i]).attr('googieId') == GOOGIE_CUR_LANG) {
            this.lang_elms[i].className = 'googie_list_selected';
            this.lang_cur_elm = this.lang_elms[i];
        }
        else {
            this.lang_elms[i].className = 'googie_list_onout';
        }
    }
};

this.createSpellDiv = function()
{
    var span = document.createElement('span');

    $(span).addClass('googie_check_spelling_link').text(this.lang_chck_spell);

    if (this.show_spell_img) {
        $(span).append(' ').append($('<img>').attr('src', this.img_dir + 'spellc.gif'));
    }
    return span;
};


//////
// State functions
/////
this.flashNoSpellingErrorState = function(on_finish)
{
    this.setStateChanged('no_error_found');

    var ref = this;
    if (this.main_controller) {
        var no_spell_errors;
        if (on_finish) {
            var fn = function() {
                on_finish();
                ref.checkSpellingState();
            };
            no_spell_errors = fn;
        }
        else
            no_spell_errors = function () { ref.checkSpellingState() };

        var rsm = $('<span>').text(this.lang_no_error_found);

        $(this.switch_lan_pic).hide();
        $(this.spell_span).empty().append(rsm)
        .removeClass().addClass('googie_check_spelling_ok');

        setTimeout(no_spell_errors, 1000);
    }
};

this.resumeEditingState = function()
{
    this.setStateChanged('resume_editing');

    //Change link text to resume
    if (this.main_controller) {
        var rsm = $('<span>').text(this.lang_rsm_edt);
    var ref = this;

        $(this.switch_lan_pic).hide();
        $(this.spell_span).empty().off().append(rsm)
            .click(function() { ref.resumeEditing(); })
            .removeClass().addClass('googie_resume_editing');
    }

    try { this.edit_layer.scrollTop = this.ta_scroll_top; }
    catch (e) {};
};

this.checkSpellingState = function(fire)
{
    if (fire)
        this.setStateChanged('ready');

    this.switch_lan_pic = document.createElement('span');

    var span_chck = this.createSpellDiv(),
        ref = this;

    if (this.custom_spellcheck_starter)
        $(span_chck).click(function(e) { ref.custom_spellcheck_starter(); });
    else {
        $(span_chck).click(function(e) { ref.spellCheck(); });
    }

    if (this.main_controller) {
        if (this.change_lang_pic_placement == 'left') {
            $(this.spell_container).empty().append(this.switch_lan_pic).append(' ').append(span_chck);
        } else {
            $(this.spell_container).empty().append(span_chck).append(' ').append(this.switch_lan_pic);
        }
    }

    this.spell_span = span_chck;
};


//////
// Misc. functions
/////
this.isDefined = function(o)
{
    return (o !== undefined && o !== null)
};

this.errorFixed = function()
{
    this.cnt_errors_fixed++;
    if (this.all_errors_fixed_observer)
        if (this.cnt_errors_fixed == this.cnt_errors) {
            this.hideErrorWindow();
            this.all_errors_fixed_observer();
        }
};

this.errorFound = function()
{
    this.cnt_errors++;
};

this.createCloseButton = function(c_fn)
{
    return this.createButton(this.lang_close, 'googie_list_close', c_fn);
};

this.createButton = function(name, css_class, c_fn)
{
    var btn_row = document.createElement('tr'),
        btn = document.createElement('td'),
        spn_btn;

    if (css_class) {
        spn_btn = document.createElement('span');
        $(spn_btn).addClass(css_class).html(name);
    } else {
        spn_btn = document.createTextNode(name);
    }

    $(btn).click(c_fn)
        .mouseover(this.item_onmouseover)
        .mouseout(this.item_onmouseout);

    btn.appendChild(spn_btn);
    btn_row.appendChild(btn);

    return btn_row;
};

this.removeIndicator = function(elm)
{
    //$(this.indicator).remove();
    // roundcube mod.
    if (window.rcmail)
        rcmail.set_busy(false, null, this.rc_msg_id);
};

this.appendIndicator = function(elm)
{
    // modified by roundcube
    if (window.rcmail)
        this.rc_msg_id = rcmail.set_busy(true, 'checking');
/*
    this.indicator = document.createElement('img');
    $(this.indicator).attr('src', this.img_dir + 'indicator.gif')
        .css({'margin-right': '5px', 'text-decoration': 'none'}).width(16).height(16);

    if (elm)
        $(this.indicator).insertBefore(elm);
    else
        $('body').append(this.indicator);
*/
}

this.createFocusLink = function(name)
{
    var link = document.createElement('a');
    $(link).attr({'href': 'javascript:;', 'name': name});
    return link;
};

this.item_onmouseover = function(e)
{
    if (this.className != 'googie_list_revert' && this.className != 'googie_list_close')
        this.className = 'googie_list_onhover';
    else
        this.parentNode.className = 'googie_list_onhover';
};

this.item_onmouseout = function(e)
{
    if (this.className != 'googie_list_revert' && this.className != 'googie_list_close')
        this.className = 'googie_list_onout';
    else
        this.parentNode.className = 'googie_list_onout';
};


};
