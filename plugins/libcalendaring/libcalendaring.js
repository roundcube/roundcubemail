/**
 * Basic Javascript utilities for calendar-related plugins
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this page.
 *
 * Copyright (C) 2012-2015, Kolab Systems AG <contact@kolabsys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this page.
 */

function rcube_libcalendaring(settings)
{
    // member vars
    this.settings = settings || {};
    this.alarm_ids = [];
    this.alarm_dialog = null;
    this.snooze_popup = null;
    this.dismiss_link = null;
    this.group2expand = {};

    // abort if env isn't set
    if (!settings || !settings.date_format)
      return;

    // private vars
    var me = this;
    var gmt_offset = (new Date().getTimezoneOffset() / -60) - (settings.timezone || 0) - (settings.dst || 0);
    var client_timezone = new Date().getTimezoneOffset();

    // general datepicker settings
    var datepicker_settings = {
        // translate from fullcalendar format to datepicker format
        dateFormat: settings.date_format.replace(/M/g, 'm').replace(/mmmmm/, 'MM').replace(/mmm/, 'M').replace(/dddd/, 'DD').replace(/ddd/, 'D').replace(/yy/g, 'y'),
        firstDay : settings.first_day,
        dayNamesMin: settings.days_short,
        monthNames: settings.months,
        monthNamesShort: settings.months,
        changeMonth: false,
        showOtherMonths: true,
        selectOtherMonths: true
    };


    /**
     * Quote html entities
     */
    var Q = this.quote_html = function(str)
    {
      return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };

    /**
     * Create a nice human-readable string for the date/time range
     */
    this.event_date_text = function(event, voice)
    {
      if (!event.start)
        return '';
      if (!event.end)
        event.end = event.start;

      var fromto, duration = event.end.getTime() / 1000 - event.start.getTime() / 1000,
        until = voice ? ' ' + rcmail.gettext('until','libcalendaring') + ' ' : ' — ';
      if (event.allDay) {
        fromto = this.format_datetime(event.start, 1, voice)
          + (duration > 86400 || event.start.getDay() != event.end.getDay() ? until + this.format_datetime(event.end, 1, voice) : '');
      }
      else if (duration < 86400 && event.start.getDay() == event.end.getDay()) {
        fromto = this.format_datetime(event.start, 0, voice)
          + (duration > 0 ? until + this.format_datetime(event.end, 2, voice) : '');
      }
      else {
        fromto = this.format_datetime(event.start, 0, voice)
          + (duration > 0 ? until + this.format_datetime(event.end, 0, voice) : '');
      }

      return fromto;
    };


    /**
     * From time and date strings to a real date object
     */
    this.parse_datetime = function(time, date)
    {
        // we use the utility function from datepicker to parse dates
        var date = date ? $.datepicker.parseDate(datepicker_settings.dateFormat, date, datepicker_settings) : new Date();

        var time_arr = time.replace(/\s*[ap][.m]*/i, '').replace(/0([0-9])/g, '$1').split(/[:.]/);
        if (!isNaN(time_arr[0])) {
            date.setHours(time_arr[0]);
        if (time.match(/p[.m]*/i) && date.getHours() < 12)
            date.setHours(parseInt(time_arr[0]) + 12);
        else if (time.match(/a[.m]*/i) && date.getHours() == 12)
            date.setHours(0);
      }
      if (!isNaN(time_arr[1]))
            date.setMinutes(time_arr[1]);

      return date;
    }

    /**
     * Convert an ISO 8601 formatted date string from the server into a Date object.
     * Timezone information will be ignored, the server already provides dates in user's timezone.
     */
    this.parseISO8601 = function(s)
    {
        // force d to be on check's YMD, for daylight savings purposes
        var fixDate = function(d, check) {
            if (+d) { // prevent infinite looping on invalid dates
                while (d.getDate() != check.getDate()) {
                    d.setTime(+d + (d < check ? 1 : -1) * 3600000);
                }
            }
        }

        // derived from http://delete.me.uk/2005/03/iso8601.html
        var m = s && s.match(/^([0-9]{4})(-([0-9]{2})(-([0-9]{2})([T ]([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2})(:?([0-9]{2}))?))?)?)?)?$/);
        if (!m) {
            return null;
        }

        var date = new Date(m[1], 0, 2),
            check = new Date(m[1], 0, 2, 9, 0);
        if (m[3]) {
            date.setMonth(m[3] - 1);
            check.setMonth(m[3] - 1);
        }
        if (m[5]) {
            date.setDate(m[5]);
            check.setDate(m[5]);
        }
        fixDate(date, check);
        if (m[7]) {
            date.setHours(m[7]);
        }
        if (m[8]) {
            date.setMinutes(m[8]);
        }
        if (m[10]) {
            date.setSeconds(m[10]);
        }
        if (m[12]) {
            date.setMilliseconds(Number("0." + m[12]) * 1000);
        }
        fixDate(date, check);

        return date;
    }

    /**
     * Turn the given date into an ISO 8601 date string understandable by PHPs strtotime()
     */
    this.date2ISO8601 = function(date)
    {
        var zeropad = function(num) { return (num < 10 ? '0' : '') + num; };

        return date.getFullYear() + '-' + zeropad(date.getMonth()+1) + '-' + zeropad(date.getDate())
            + 'T' + zeropad(date.getHours()) + ':' + zeropad(date.getMinutes()) + ':' + zeropad(date.getSeconds());
    };

    /**
     * Format the given date object according to user's prefs
     */
    this.format_datetime = function(date, mode, voice)
    {
        var res = '';
        if (!mode || mode == 1) {
          res += $.datepicker.formatDate(voice ? 'MM d yy' : datepicker_settings.dateFormat, date, datepicker_settings);
        }
        if (!mode) {
            res += voice ? ' ' + rcmail.gettext('at','libcalendaring') + ' ' : ' ';
        }
        if (!mode || mode == 2) {
            res += this.format_time(date, voice);
        }

        return res;
    }

    /**
     * Clone from fullcalendar.js
     */
    this.format_time = function(date, voice)
    {
        var zeroPad = function(n) { return (n < 10 ? '0' : '') + n; }
        var formatters = {
            s   : function(d) { return d.getSeconds() },
            ss  : function(d) { return zeroPad(d.getSeconds()) },
            m   : function(d) { return d.getMinutes() },
            mm  : function(d) { return zeroPad(d.getMinutes()) },
            h   : function(d) { return d.getHours() % 12 || 12 },
            hh  : function(d) { return zeroPad(d.getHours() % 12 || 12) },
            H   : function(d) { return d.getHours() },
            HH  : function(d) { return zeroPad(d.getHours()) },
            t   : function(d) { return d.getHours() < 12 ? 'a' : 'p' },
            tt  : function(d) { return d.getHours() < 12 ? 'am' : 'pm' },
            T   : function(d) { return d.getHours() < 12 ? 'A' : 'P' },
            TT  : function(d) { return d.getHours() < 12 ? 'AM' : 'PM' }
        };

        var i, i2, c, formatter, res = '',
          format = voice ? settings['time_format'].replace(':',' ').replace('HH','H').replace('hh','h').replace('mm','m').replace('ss','s') : settings['time_format'];
        for (i=0; i < format.length; i++) {
            c = format.charAt(i);
            for (i2=Math.min(i+2, format.length); i2 > i; i2--) {
                if (formatter = formatters[format.substring(i, i2)]) {
                    res += formatter(date);
                    i = i2 - 1;
                    break;
                }
            }
            if (i2 == i) {
                res += c;
            }
        }

        return res;
    }

    /**
     * Convert the given Date object into a unix timestamp respecting browser's and user's timezone settings
     */
    this.date2unixtime = function(date)
    {
        var dst_offset = (client_timezone - date.getTimezoneOffset()) * 60;  // adjust DST offset
        return Math.round(date.getTime()/1000 + gmt_offset * 3600 + dst_offset);
    }

    /**
     * Turn a unix timestamp value into a Date object
     */
    this.fromunixtime = function(ts)
    {
        ts -= gmt_offset * 3600;
        var date = new Date(ts * 1000),
            dst_offset = (client_timezone - date.getTimezoneOffset()) * 60;
        if (dst_offset)  // adjust DST offset
            date.setTime((ts + 3600) * 1000);
        return date;
    }

    /**
     * Simple plaintext to HTML converter, makig URLs clickable
     */
    this.text2html = function(str, maxlen, maxlines)
    {
        var html = Q(String(str));

        // limit visible text length
        if (maxlen) {
            var morelink = '<span>... <a href="#more" onclick="$(this).parent().hide().next().show();return false" class="morelink">'+rcmail.gettext('showmore','libcalendaring')+'</a></span><span style="display:none">',
                lines = html.split(/\r?\n/),
                words, out = '', len = 0;

            for (var i=0; i < lines.length; i++) {
                len += lines[i].length;
                if (maxlines && i == maxlines - 1) {
                    out += lines[i] + '\n' + morelink;
                    maxlen = html.length * 2;
                }
                else if (len > maxlen) {
                    len = out.length;
                    words = lines[i].split(' ');
                    for (var j=0; j < words.length; j++) {
                        len += words[j].length + 1;
                        out += words[j] + ' ';
                        if (len > maxlen) {
                            out += morelink;
                            maxlen = html.length * 2;
                            maxlines = 0;
                        }
                    }
                    out += '\n';
                }
                else
                    out += lines[i] + '\n';
            }

            if (maxlen > str.length)
                out += '</span>';

            html = out;
        }

        // simple link parser (similar to rcube_string_replacer class in PHP)
        var utf_domain = '[^?&@"\'/\\(\\)\\s\\r\\t\\n]+\\.([^\x00-\x2f\x3b-\x40\x5b-\x60\x7b-\x7f]{2,}|xn--[a-z0-9]{2,})';
        var url1 = '.:;,', url2 = 'a-z0-9%=#@+?&/_~\\[\\]-';
        var link_pattern = new RegExp('([hf]t+ps?://)('+utf_domain+'(['+url1+']?['+url2+']+)*)', 'ig');
        var mailto_pattern = new RegExp('([^\\s\\n\\(\\);]+@'+utf_domain+')', 'ig');
        var link_replace = function(matches, p1, p2) {
          var title = '', text = p2;
          if (p2 && p2.length > 55) {
            text = p2.substr(0, 45) + '...' + p2.substr(-8);
            title = p1 + p2;
          }
          return '<a href="'+p1+p2+'" class="extlink" target="_blank" title="'+title+'">'+p1+text+'</a>'
        };

        return html
            .replace(link_pattern, link_replace)
            .replace(mailto_pattern, '<a href="mailto:$1">$1</a>')
            .replace(/(mailto:)([^"]+)"/g, '$1$2" onclick="rcmail.command(\'compose\', \'$2\');return false"')
            .replace(/\n/g, "<br/>");
    };

    this.init_alarms_edit = function(prefix, index)
    {
        var edit_type = $(prefix+' select.edit-alarm-type'),
          dom_id = edit_type.attr('id');

        // register events on alarm fields
        edit_type.change(function(){
            $(this).parent().find('span.edit-alarm-values')[(this.selectedIndex>0?'show':'hide')]();
        });
        $(prefix+' select.edit-alarm-offset').change(function(){
            var mode = $(this).val() == '@' ? 'show' : 'hide';
            $(this).parent().find('.edit-alarm-date, .edit-alarm-time')[mode]();
            $(this).parent().find('.edit-alarm-value').prop('disabled', mode == 'show');
        });

        $(prefix+' .edit-alarm-date').removeClass('hasDatepicker').removeAttr('id').datepicker(datepicker_settings);

        $(prefix).on('click', 'a.delete-alarm', function(e){
            if ($(this).closest('.edit-alarm-item').siblings().length > 0) {
                $(this).closest('.edit-alarm-item').remove();
            }
            return false;
        });

        // set a unique id attribute and set label reference accordingly
        if ((index || 0) > 0 && dom_id) {
            dom_id += ':' + (new Date().getTime());
            edit_type.attr('id', dom_id);
            $(prefix+' label:first').attr('for', dom_id);
        }

        $(prefix).on('click', 'a.add-alarm', function(e){
            var i = $(this).closest('.edit-alarm-item').siblings().length + 1;
            var item = $(this).closest('.edit-alarm-item').clone(false)
              .removeClass('first')
              .appendTo(prefix);

              me.init_alarms_edit(prefix + ' .edit-alarm-item:eq(' + i + ')', i);
              $('select.edit-alarm-type, select.edit-alarm-offset', item).change();
              return false;
        });
    }

    this.set_alarms_edit = function(prefix, valarms)
    {
        $(prefix + ' .edit-alarm-item:gt(0)').remove();

        var i, alarm, domnode, val, offset;
        for (i=0; i < valarms.length; i++) {
          alarm = valarms[i];
          if (!alarm.action)
              alarm.action = 'DISPLAY';

          if (i == 0) {
              domnode = $(prefix + ' .edit-alarm-item').eq(0);
          }
          else {
              domnode = $(prefix + ' .edit-alarm-item').eq(0).clone(false).removeClass('first').appendTo(prefix);
              this.init_alarms_edit(prefix + ' .edit-alarm-item:eq(' + i + ')', i);
          }

          $('select.edit-alarm-type', domnode).val(alarm.action);

          if (String(alarm.trigger).match(/@(\d+)/)) {
              var ondate = this.fromunixtime(parseInt(RegExp.$1));
              $('select.edit-alarm-offset', domnode).val('@');
              $('input.edit-alarm-value', domnode).val('');
              $('input.edit-alarm-date', domnode).val(this.format_datetime(ondate, 1));
              $('input.edit-alarm-time', domnode).val(this.format_datetime(ondate, 2));
          }
          else if (String(alarm.trigger).match(/([-+])(\d+)([MHDS])/)) {
              val = RegExp.$2; offset = ''+RegExp.$1+RegExp.$3;
              $('input.edit-alarm-value', domnode).val(val);
              $('select.edit-alarm-offset', domnode).val(offset);
          }
        }

        // set correct visibility by triggering onchange handlers
        $(prefix + ' select.edit-alarm-type, ' + prefix + ' select.edit-alarm-offset').change();
    };

    this.serialize_alarms = function(prefix)
    {
        var valarms = [];

        $(prefix + ' .edit-alarm-item').each(function(i, elem) {
            var val, offset, alarm = { action: $('select.edit-alarm-type', elem).val() };
            if (alarm.action) {
                offset = $('select.edit-alarm-offset', elem).val();
                if (offset == '@') {
                    alarm.trigger = '@' + me.date2unixtime(me.parse_datetime($('input.edit-alarm-time', elem).val(), $('input.edit-alarm-date', elem).val()));
                }
                else if (!isNaN((val = parseInt($('input.edit-alarm-value', elem).val()))) && val >= 0) {
                    alarm.trigger = offset[0] + val + offset[1];
                }

                valarms.push(alarm);
            }
        });

        return valarms;
    };


    /*****  Alarms handling  *****/

    /**
     * Display a notification for the given pending alarms
     */
    this.display_alarms = function(alarms)
    {
        // clear old alert first
        if (this.alarm_dialog)
            this.alarm_dialog.dialog('destroy').remove();

        this.alarm_dialog = $('<div>').attr('id', 'alarm-display');

        var i, actions, adismiss, asnooze, alarm, html, event_ids = [], buttons = {};
        for (i=0; i < alarms.length; i++) {
            alarm = alarms[i];
            alarm.start = this.parseISO8601(alarm.start);
            alarm.end = this.parseISO8601(alarm.end);
            event_ids.push(alarm.id);

            html = '<h3 class="event-title">' + Q(alarm.title) + '</h3>';
            html += '<div class="event-section">' + Q(alarm.location || '') + '</div>';
            html += '<div class="event-section">' + Q(this.event_date_text(alarm)) + '</div>';

            adismiss = $('<a href="#" class="alarm-action-dismiss"></a>').html(rcmail.gettext('dismiss','libcalendaring')).click(function(){
                me.dismiss_link = $(this);
                me.dismiss_alarm(me.dismiss_link.data('id'), 0);
            });
            asnooze = $('<a href="#" class="alarm-action-snooze"></a>').html(rcmail.gettext('snooze','libcalendaring')).click(function(e){
                me.snooze_dropdown($(this), e);
                e.stopPropagation();
                return false;
            });
            actions = $('<div>').addClass('alarm-actions').append(adismiss.data('id', alarm.id)).append(asnooze.data('id', alarm.id));

            $('<div>').addClass('alarm-item').html(html).append(actions).appendTo(this.alarm_dialog);
        }

        buttons[rcmail.gettext('close')] = function() {
            $(this).dialog('close');
        };

        buttons[rcmail.gettext('dismissall','libcalendaring')] = function() {
            // submit dismissed event_ids to server
            me.dismiss_alarm(me.alarm_ids.join(','), 0);
            $(this).dialog('close');
        };

        this.alarm_dialog.appendTo(document.body).dialog({
            modal: false,
            resizable: true,
            closeOnEscape: false,
            dialogClass: 'alarms',
            title: rcmail.gettext('alarmtitle','libcalendaring'),
            buttons: buttons,
            open: function() {
              setTimeout(function() {
                me.alarm_dialog.parent().find('.ui-button:not(.ui-dialog-titlebar-close)').first().focus();
              }, 5);
            },
            close: function() {
              $('#alarm-snooze-dropdown').hide();
              $(this).dialog('destroy').remove();
              me.alarm_dialog = null;
              me.alarm_ids = null;
            },
            drag: function(event, ui) {
              $('#alarm-snooze-dropdown').hide();
            }
        });

        this.alarm_dialog.closest('div[role=dialog]').attr('role', 'alertdialog');

        this.alarm_ids = event_ids;
    };

    /**
     * Show a drop-down menu with a selection of snooze times
     */
    this.snooze_dropdown = function(link, event)
    {
        if (!this.snooze_popup) {
            this.snooze_popup = $('#alarm-snooze-dropdown');
            // create popup if not found
            if (!this.snooze_popup.length) {
                this.snooze_popup = $('<div>').attr('id', 'alarm-snooze-dropdown').addClass('popupmenu').appendTo(document.body);
                this.snooze_popup.html(rcmail.env.snooze_select)
            }
            $('#alarm-snooze-dropdown a').click(function(e){
                var time = String(this.href).replace(/.+#/, '');
                me.dismiss_alarm($('#alarm-snooze-dropdown').data('id'), time);
                return false;
            });
        }

        // hide visible popup
        if (this.snooze_popup.is(':visible') && this.snooze_popup.data('id') == link.data('id')) {
            rcmail.command('menu-close', 'alarm-snooze-dropdown');
            this.dismiss_link = null;
        }
        else {  // open popup below the clicked link
            rcmail.command('menu-open', 'alarm-snooze-dropdown', link.get(0), event);
            this.snooze_popup.data('id', link.data('id'));
            this.dismiss_link = link;
        }
    };

    /**
     * Dismiss or snooze alarms for the given event
     */
    this.dismiss_alarm = function(id, snooze)
    {
        rcmail.command('menu-close', 'alarm-snooze-dropdown');
        rcmail.http_post('utils/plugin.alarms', { action:'dismiss', data:{ id:id, snooze:snooze } });

        // remove dismissed alarm from list
        if (this.dismiss_link) {
            this.dismiss_link.closest('div.alarm-item').hide();
            var new_ids = jQuery.grep(this.alarm_ids, function(v){ return v != id; });
            if (new_ids.length)
                this.alarm_ids = new_ids;
            else
                this.alarm_dialog.dialog('close');
        }

        this.dismiss_link = null;
    };


    /*****  Recurrence form handling  *****/

    /**
     * Install event handlers on recurrence form elements
     */
    this.init_recurrence_edit = function(prefix)
    {
        // toggle recurrence frequency forms
        $('#edit-recurrence-frequency').change(function(e){
            var freq = $(this).val().toLowerCase();
            $('.recurrence-form').hide();
            if (freq) {
              $('#recurrence-form-'+freq).show();
              if (freq != 'rdate')
                $('#recurrence-form-until').show();
            }
        });
        $('#recurrence-form-rdate input.button.add').click(function(e){
            var dt, dv = $('#edit-recurrence-rdate-input').val();
            if (dv && (dt = me.parse_datetime('12:00', dv))) {
                me.add_rdate(dt);
                me.sort_rdates();
                $('#edit-recurrence-rdate-input').val('')
            }
            else {
                $('#edit-recurrence-rdate-input').select();
            }
        });
        $('#edit-recurrence-rdates').on('click', 'a.delete', function(e){
            $(this).closest('li').remove();
            return false;
        });

        $('#edit-recurrence-enddate').datepicker(datepicker_settings).click(function(){ $("#edit-recurrence-repeat-until").prop('checked', true) });
        $('#edit-recurrence-repeat-times').change(function(e){ $('#edit-recurrence-repeat-count').prop('checked', true); });
        $('#edit-recurrence-rdate-input').datepicker(datepicker_settings);
    };

    /**
     * Set recurrence form according to the given event/task record
     */
    this.set_recurrence_edit = function(rec)
    {
        var recurrence = $('#edit-recurrence-frequency').val(rec.recurrence ? rec.recurrence.FREQ || (rec.recurrence.RDATE ? 'RDATE' : '') : '').change(),
            interval = $('.recurrence-form select.edit-recurrence-interval').val(rec.recurrence ? rec.recurrence.INTERVAL || 1 : 1),
            rrtimes = $('#edit-recurrence-repeat-times').val(rec.recurrence ? rec.recurrence.COUNT || 1 : 1),
            rrenddate = $('#edit-recurrence-enddate').val(rec.recurrence && rec.recurrence.UNTIL ? this.format_datetime(this.parseISO8601(rec.recurrence.UNTIL), 1) : '');
        $('.recurrence-form input.edit-recurrence-until:checked').prop('checked', false);
        $('#edit-recurrence-rdates').html('');

        var weekdays = ['SU','MO','TU','WE','TH','FR','SA'],
            rrepeat_id = '#edit-recurrence-repeat-forever';
        if      (rec.recurrence && rec.recurrence.COUNT) rrepeat_id = '#edit-recurrence-repeat-count';
        else if (rec.recurrence && rec.recurrence.UNTIL) rrepeat_id = '#edit-recurrence-repeat-until';
        $(rrepeat_id).prop('checked', true);

        if (rec.recurrence && rec.recurrence.BYDAY && rec.recurrence.FREQ == 'WEEKLY') {
            var wdays = rec.recurrence.BYDAY.split(',');
            $('input.edit-recurrence-weekly-byday').val(wdays);
        }
        if (rec.recurrence && rec.recurrence.BYMONTHDAY) {
            $('input.edit-recurrence-monthly-bymonthday').val(String(rec.recurrence.BYMONTHDAY).split(','));
            $('input.edit-recurrence-monthly-mode').val(['BYMONTHDAY']);
        }
        if (rec.recurrence && rec.recurrence.BYDAY && (rec.recurrence.FREQ == 'MONTHLY' || rec.recurrence.FREQ == 'YEARLY')) {
            var byday, section = rec.recurrence.FREQ.toLowerCase();
            if ((byday = String(rec.recurrence.BYDAY).match(/(-?[1-4])([A-Z]+)/))) {
                $('#edit-recurrence-'+section+'-prefix').val(byday[1]);
                $('#edit-recurrence-'+section+'-byday').val(byday[2]);
            }
            $('input.edit-recurrence-'+section+'-mode').val(['BYDAY']);
        }
        else if (rec.start) {
            $('#edit-recurrence-monthly-byday').val(weekdays[rec.start.getDay()]);
        }
        if (rec.recurrence && rec.recurrence.BYMONTH) {
            $('input.edit-recurrence-yearly-bymonth').val(String(rec.recurrence.BYMONTH).split(','));
        }
        else if (rec.start) {
            $('input.edit-recurrence-yearly-bymonth').val([String(rec.start.getMonth()+1)]);
        }
        if (rec.recurrence && rec.recurrence.RDATE) {
            $.each(rec.recurrence.RDATE, function(i,rdate){
                me.add_rdate(me.parseISO8601(rdate));
            });
        }
    };

    /**
     * Gather recurrence settings from form
     */
    this.serialize_recurrence = function(timestr)
    {
        var recurrence = '',
            freq = $('#edit-recurrence-frequency').val();

        if (freq != '') {
            recurrence = {
                FREQ: freq,
                INTERVAL: $('#edit-recurrence-interval-'+freq.toLowerCase()).val()
            };

            var until = $('input.edit-recurrence-until:checked').val();
            if (until == 'count')
                recurrence.COUNT = $('#edit-recurrence-repeat-times').val();
            else if (until == 'until')
                recurrence.UNTIL = me.date2ISO8601(me.parse_datetime(timestr || '00:00', $('#edit-recurrence-enddate').val()));

            if (freq == 'WEEKLY') {
                var byday = [];
                $('input.edit-recurrence-weekly-byday:checked').each(function(){ byday.push(this.value); });
                if (byday.length)
                    recurrence.BYDAY = byday.join(',');
            }
            else if (freq == 'MONTHLY') {
                var mode = $('input.edit-recurrence-monthly-mode:checked').val(), bymonday = [];
                if (mode == 'BYMONTHDAY') {
                    $('input.edit-recurrence-monthly-bymonthday:checked').each(function(){ bymonday.push(this.value); });
                    if (bymonday.length)
                        recurrence.BYMONTHDAY = bymonday.join(',');
                }
                else
                    recurrence.BYDAY = $('#edit-recurrence-monthly-prefix').val() + $('#edit-recurrence-monthly-byday').val();
            }
            else if (freq == 'YEARLY') {
                var byday, bymonth = [];
                $('input.edit-recurrence-yearly-bymonth:checked').each(function(){ bymonth.push(this.value); });
                if (bymonth.length)
                    recurrence.BYMONTH = bymonth.join(',');
                if ((byday = $('#edit-recurrence-yearly-byday').val()))
                    recurrence.BYDAY = $('#edit-recurrence-yearly-prefix').val() + byday;
            }
            else if (freq == 'RDATE') {
                recurrence = { RDATE:[] };
                // take selected but not yet added date into account
                if ($('#edit-recurrence-rdate-input').val() != '') {
                    $('#recurrence-form-rdate input.button.add').click();
                }
                $('#edit-recurrence-rdates li').each(function(i, li){
                    recurrence.RDATE.push($(li).attr('data-value'));
                });
            }
        }

        return recurrence;
    };

    // add the given date to the RDATE list
    this.add_rdate = function(date)
    {
        var li = $('<li>')
            .attr('data-value', this.date2ISO8601(date))
            .html('<span>' + Q(this.format_datetime(date, 1)) + '</span>')
            .appendTo('#edit-recurrence-rdates');

        $('<a>').attr('href', '#del')
            .addClass('iconbutton delete')
            .html(rcmail.get_label('delete', 'libcalendaring'))
            .attr('title', rcmail.get_label('delete', 'libcalendaring'))
            .appendTo(li);
    };

    // re-sort the list items by their 'data-value' attribute
    this.sort_rdates = function()
    {
        var mylist = $('#edit-recurrence-rdates'),
            listitems = mylist.children('li').get();
        listitems.sort(function(a, b) {
            var compA = $(a).attr('data-value');
            var compB = $(b).attr('data-value');
            return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
        })
        $.each(listitems, function(idx, item) { mylist.append(item); });
    };


    /*****  Attendee form handling  *****/

    // expand the given contact group into individual event/task attendees
    this.expand_attendee_group = function(e, add, remove)
    {
        var id = (e.data ? e.data.email : null) || $(e.target).attr('data-email'),
            role_select = $(e.target).closest('tr').find('select.edit-attendee-role option:selected');

        this.group2expand[id] = { link: e.target, data: $.extend({}, e.data || {}), adder: add, remover: remove }

        // copy group role from the according form element
        if (role_select.length) {
            this.group2expand[id].data.role = role_select.val();
        }

        // register callback handler
        if (!this._expand_attendee_listener) {
            this._expand_attendee_listener = this.expand_attendee_callback;
            rcmail.addEventListener('plugin.expand_attendee_callback', function(result) {
                me._expand_attendee_listener(result);
            });
        }

        rcmail.http_post('libcal/plugin.expand_attendee_group', { id: id, data: e.data || {} }, rcmail.set_busy(true, 'loading'));
    };

    // callback from server to expand an attendee group
    this.expand_attendee_callback = function(result)
    {
        var attendee, id = result.id,
            data = this.group2expand[id],
            row = $(data.link).closest('tr');

        // replace group entry with all members returned by the server
        if (data && data.adder && result.members && result.members.length) {
            for (var i=0; i < result.members.length; i++) {
                attendee = result.members[i];
                attendee.role = data.data.role;
                attendee.cutype = 'INDIVIDUAL';
                attendee.status = 'NEEDS-ACTION';
                data.adder(attendee, null, row);
            }

            if (data.remover) {
                data.remover(data.link, id)
            }
            else {
                row.remove();
            }

            delete this.group2expand[id];
        }
        else {
            rcmail.display_message(result.error || rcmail.gettext('expandattendeegroupnodata','libcalendaring'), 'error');
        }
    };


    // Render message reference links to the given container
    this.render_message_links = function(links, container, edit, plugin)
    {
        var ul = $('<ul>').addClass('attachmentslist');

        $.each(links, function(i, link) {
            if (!link.mailurl)
                return true;  // continue

            var li = $('<li>').addClass('link')
                .addClass('message eml')
                .append($('<a>')
                    .attr('href', link.mailurl)
                    .addClass('messagelink')
                    .text(link.subject || link.uri)
                )
                .appendTo(ul);

            // add icon to remove the link
            if (edit) {
                $('<a>')
                    .attr('href', '#delete')
                    .attr('title', rcmail.gettext('removelink', plugin))
                    .attr('data-uri', link.uri)
                    .addClass('delete')
                    .text(rcmail.gettext('delete'))
                    .appendTo(li);
            }
        });

        container.empty().append(ul);
    }
}

//////  static methods

/**
 *
 */
rcube_libcalendaring.add_from_itip_mail = function(mime_id, task, status, dom_id)
{
    // ask user to delete the declined event from the local calendar (#1670)
    var del = false;
    if (rcmail.env.rsvp_saved && status == 'declined') {
        del = confirm(rcmail.gettext('itip.declinedeleteconfirm'));
    }

    // open dialog for iTip delegation
    if (status == 'delegated') {
        rcube_libcalendaring.itip_delegate_dialog(function(data) {
            rcmail.http_post(task + '/itip-delegate', {
                _uid: rcmail.env.uid,
                _mbox: rcmail.env.mailbox,
                _part: mime_id,
                _to: data.to,
                _rsvp: data.rsvp ? 1 : 0,
                _comment: data.comment,
                _folder: data.target
            }, rcmail.set_busy(true, 'itip.savingdata'));
        }, $('#rsvp-'+dom_id+' .folder-select'));
        return false;
    }

    var noreply = 0, comment = '';
    if (dom_id) {
      noreply = $('#noreply-'+dom_id+':checked').length ? 1 : 0;
      if (!noreply)
        comment = $('#reply-comment-'+dom_id).val();
    }

    rcmail.http_post(task + '/mailimportitip', {
        _uid: rcmail.env.uid,
        _mbox: rcmail.env.mailbox,
        _part: mime_id,
        _folder: $('#itip-saveto').val(),
        _status: status,
        _del: del?1:0,
        _noreply: noreply,
        _comment: comment
      }, rcmail.set_busy(true, 'itip.savingdata'));

    return false;
};

/**
 * Helper function to render the iTip delegation dialog
 * and trigger a callback function when submitted.
 */
rcube_libcalendaring.itip_delegate_dialog = function(callback, selector)
{
    // show dialog for entering the delegatee address and comment
    var html = '<form class="itip-dialog-form" action="javascript:void()">' +
        '<div class="form-section">' +
            '<label for="itip-delegate-to">' + rcmail.gettext('itip.delegateto') + '</label><br/>' +
            '<input type="text" id="itip-delegate-to" class="text" size="40" value="" />' +
        '</div>' +
        '<div class="form-section">' +
            '<label for="itip-delegate-rsvp">' +
                '<input type="checkbox" id="itip-delegate-rsvp" class="checkbox" size="40" value="" />' +
                rcmail.gettext('itip.delegatersvpme') +
            '</label>' +
        '</div>' +
        '<div class="form-section">' +
            '<textarea id="itip-delegate-comment" class="itip-comment" cols="40" rows="8" placeholder="' +
                rcmail.gettext('itip.itipcomment') + '"></textarea>' + 
        '</div>' +
        '<div class="form-section">' +
            (selector && selector.length ? selector.html() : '') +
        '</div>' +
    '</form>';

    var dialog, buttons = [];
    buttons.push({
        text: rcmail.gettext('itipdelegated', 'itip'),
        click: function() {
            var doc = window.parent.document,
                delegatee = String($('#itip-delegate-to', doc).val()).replace(/(^\s+)|(\s+$)/, '');

            if (delegatee != '' && rcube_check_email(delegatee, true)) {
                callback({
                    to: delegatee,
                    rsvp: $('#itip-delegate-rsvp', doc).prop('checked'),
                    comment: $('#itip-delegate-comment', doc).val(),
                    target: $('#itip-saveto', doc).val()
                });

                setTimeout(function() { dialog.dialog("close"); }, 500);
            }
            else {
                alert(rcmail.gettext('itip.delegateinvalidaddress'));
                $('#itip-delegate-to', doc).focus();
            }
        }
    });

    buttons.push({
        text: rcmail.gettext('cancel', 'itip'),
        click: function() {
            dialog.dialog('close');
        }
    });

    dialog = rcmail.show_popup_dialog(html, rcmail.gettext('delegateinvitation', 'itip'), buttons, {
        width: 460,
        open: function(event, ui) {
            $(this).parent().find('.ui-button:not(.ui-dialog-titlebar-close)').first().addClass('mainaction');
            $(this).find('#itip-saveto').val('');

            // initialize autocompletion
            var ac_props, rcm = rcmail.is_framed() ? parent.rcmail : rcmail;
            if (rcmail.env.autocomplete_threads > 0) {
                ac_props = {
                    threads: rcmail.env.autocomplete_threads,
                    sources: rcmail.env.autocomplete_sources
                };
            }
            rcm.init_address_input_events($(this).find('#itip-delegate-to').focus(), ac_props);
            rcm.env.recipients_delimiter = '';
        },
        close: function(event, ui) {
            rcm = rcmail.is_framed() ? parent.rcmail : rcmail;
            rcm.ksearch_blur();
            $(this).remove();
        }
    });

    return dialog;
};

/**
 * Show a menu for selecting the RSVP reply mode
 */
rcube_libcalendaring.itip_rsvp_recurring = function(btn, callback)
{
    var mnu = $('<ul></ul>').addClass('popupmenu libcal-rsvp-replymode');

    $.each(['all','current'/*,'future'*/], function(i, mode) {
        $('<li><a>' + rcmail.get_label('rsvpmode'+mode, 'libcalendaring') + '</a>')
        .addClass('ui-menu-item')
        .attr('rel', mode)
        .appendTo(mnu);
    });

    var action = btn.attr('rel');

    // open the mennu
    mnu.menu({
        select: function(event, ui) {
            callback(action, ui.item.attr('rel'));
        }
    })
    .appendTo(document.body)
    .position({ my: 'left top', at: 'left bottom+2', of: btn })
    .data('action', action);

    setTimeout(function() {
        $(document).one('click', function() {
            mnu.menu('destroy');
            mnu.remove();
        });
    }, 100);
};

/**
 *
 */
rcube_libcalendaring.remove_from_itip = function(event, task, title)
{
    if (confirm(rcmail.gettext('itip.deleteobjectconfirm').replace('$title', title))) {
        rcmail.http_post(task + '/itip-remove',
            event,
            rcmail.set_busy(true, 'itip.savingdata')
        );
    }
};

/**
 *
 */
rcube_libcalendaring.decline_attendee_reply = function(mime_id, task)
{
    // show dialog for entering a comment and send to server
    var html = '<div class="itip-dialog-confirm-text">' + rcmail.gettext('itip.declineattendeeconfirm') + '</div>' +
        '<textarea id="itip-decline-comment" class="itip-comment" cols="40" rows="8"></textarea>';

    var dialog, buttons = [];
    buttons.push({
        text: rcmail.gettext('declineattendee', 'itip'),
        click: function() {
            rcmail.http_post(task + '/itip-decline-reply', {
                _uid: rcmail.env.uid,
                _mbox: rcmail.env.mailbox,
                _part: mime_id,
                _comment: $('#itip-decline-comment', window.parent.document).val()
            }, rcmail.set_busy(true, 'itip.savingdata'));
          dialog.dialog("close");
        }
    });

    buttons.push({
        text: rcmail.gettext('cancel', 'itip'),
        click: function() {
          dialog.dialog('close');
        }
    });

    dialog = rcmail.show_popup_dialog(html, rcmail.gettext('declineattendee', 'itip'), buttons, {
        width: 460,
        open: function() {
            $(this).parent().find('.ui-button:not(.ui-dialog-titlebar-close)').first().addClass('mainaction');
            $('#itip-decline-comment').focus();
        }
    });

    return false;
};

/**
 *
 */
rcube_libcalendaring.fetch_itip_object_status = function(p)
{
  rcmail.http_post(p.task + '/itip-status', { data: p });
};

/**
 *
 */
rcube_libcalendaring.update_itip_object_status = function(p)
{
  rcmail.env.rsvp_saved = p.saved;
  rcmail.env.itip_existing = p.existing;

  // hide all elements first
  $('#itip-buttons-'+p.id+' > div').hide();
  $('#rsvp-'+p.id+' .folder-select').remove();

  if (p.html) {
    // append/replace rsvp status display
    $('#loading-'+p.id).next('.rsvp-status').remove();
    $('#loading-'+p.id).hide().after(p.html);
  }

  // enable/disable rsvp buttons
  if (p.action == 'rsvp') {
    $('#rsvp-'+p.id+' input.button').prop('disabled', false)
      .filter('.'+String(p.status||'unknown').toLowerCase()).prop('disabled', p.latest);
  }
 
  // show rsvp/import buttons (with calendar selector)
  $('#'+p.action+'-'+p.id).show().find('input.button').last().after(p.select);

  // show itip box appendix after replacing the given placeholders
  if (p.append && p.append.selector) {
    var elem = $(p.append.selector);
    if (p.append.replacements) {
      $.each(p.append.replacements, function(k, html) {
        elem.html(elem.html().replace(k, html));
      });
    }
    else if (p.append.html) {
      elem.html(p.append.html)
    }
    elem.show();
  }
};

/**
 * Callback from server after an iTip message has been processed
 */
rcube_libcalendaring.itip_message_processed = function(metadata)
{
  if (metadata.after_action) {
    setTimeout(function(){ rcube_libcalendaring.itip_after_action(metadata.after_action); }, 1200);
  }
  else {
    rcube_libcalendaring.fetch_itip_object_status(metadata);
  }
};

/**
 * After-action on iTip request message. Action types:
 *     0 - no action
 *     1 - move to Trash
 *     2 - delete the message
 *     3 - flag as deleted
 *     folder_name - move the message to the specified folder
 */
rcube_libcalendaring.itip_after_action = function(action)
{
  if (!action) {
    return;
  }

  var rc = rcmail.is_framed() ? parent.rcmail : rcmail;

  if (action === 2) {
    rc.permanently_remove_messages();
  }
  else if (action === 3) {
    rc.mark_message('delete');
  }
  else {
    rc.move_messages(action === 1 ? rc.env.trash_mailbox : action);
  }
};

/**
 * Open the calendar preview for the current iTip event
 */
rcube_libcalendaring.open_itip_preview = function(url, msgref)
{
  if (!rcmail.env.itip_existing)
    url += '&itip=' + escape(msgref);

  var win = rcmail.open_window(url);
};


// extend jQuery
(function($){
  $.fn.serializeJSON = function(){
    var json = {};
    jQuery.map($(this).serializeArray(), function(n, i) {
      json[n['name']] = n['value'];
    });
    return json;
  };
})(jQuery);


/* libcalendaring plugin initialization */
window.rcmail && rcmail.addEventListener('init', function(evt) {
  if (rcmail.env.libcal_settings) {
    var libcal = new rcube_libcalendaring(rcmail.env.libcal_settings);
    rcmail.addEventListener('plugin.display_alarms', function(alarms){ libcal.display_alarms(alarms); });
  }

  rcmail.addEventListener('plugin.update_itip_object_status', rcube_libcalendaring.update_itip_object_status)
    .addEventListener('plugin.fetch_itip_object_status', rcube_libcalendaring.fetch_itip_object_status)
    .addEventListener('plugin.itip_message_processed', rcube_libcalendaring.itip_message_processed);

  $('.rsvp-buttons').on('click', 'a.reply-comment-toggle', function(e){
    $(this).hide().parent().find('textarea').show().focus();
    return false;
  });

  if (rcmail.env.action == 'get-attachment' && rcmail.gui_objects['attachmentframe']) {
    rcmail.register_command('print-attachment', function() {
      var frame = rcmail.get_frame_window(rcmail.gui_objects['attachmentframe'].id);
      if (frame) frame.print();
    }, true);
  }

  if (rcmail.env.action == 'get-attachment' && rcmail.env.attachment_download_url) {
    rcmail.register_command('download-attachment', function() {
      rcmail.location_href(rcmail.env.attachment_download_url, window);
    }, true);
  }

});
