/**
 * Print view for the Calendar plugin
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2011, Kolab Systems AG <contact@kolabsys.com>
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
 * for the JavaScript code in this file.
 */


/* calendar plugin printing code */
window.rcmail && rcmail.addEventListener('init', function(evt) {

  // quote html entities
  var Q = function(str)
  {
    return String(str).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  };
  
  var rc_loading;
  var showdesc = true;
  var settings = $.extend(rcmail.env.calendar_settings, rcmail.env.libcal_settings);
  
  // create list of event sources AKA calendars
  var src, event_sources = [];
  var add_url = (rcmail.env.search ? '&q='+escape(rcmail.env.search) : '');
  for (var id in rcmail.env.calendars) {
    if (!rcmail.env.calendars[id].active)
      continue;

    var driver = rcmail.env.calendars[id].driver;
    source = $.extend({
      url: "./?_task=calendar&_action=load_events&driver=" + driver + "&source=" + escape(id) + add_url,
      className: 'fc-event-cal-'+id,
      id: id
    }, rcmail.env.calendars[id]);

    source.color = '#' + source.color.replace(/^#/, '');

    if (source.color.match(/^#f+$/i))
      source.color = '#ccc';

    event_sources.push(source);
  }
  
  var viewdate = new Date();
  if (rcmail.env.date)
    viewdate.setTime(rcmail.env.date * 1000);

  // initalize the fullCalendar plugin
  var fc = $('#calendar').fullCalendar({
    header: {
      left: '',
      center: 'title',
      right: 'agendaDay,agendaWeek,month,table'
    },
    aspectRatio: 0.85,
    ignoreTimezone: true,  // will treat the given date strings as in local (browser's) timezone
    date: viewdate.getDate(),
    month: viewdate.getMonth(),
    year: viewdate.getFullYear(),
    defaultView: rcmail.env.view,
    eventSources: event_sources,
    monthNames : settings['months'],
    monthNamesShort : settings['months_short'],
    dayNames : settings['days'],
    dayNamesShort : settings['days_short'],
    firstDay : settings['first_day'],
    firstHour : settings['first_hour'],
    slotMinutes : 60/settings['timeslots'],
    timeFormat: {
      '': settings['time_format'],
      agenda: settings['time_format'] + '{ - ' + settings['time_format'] + '}',
      list: settings['time_format'] + '{ - ' + settings['time_format'] + '}',
      table: settings['time_format'] + '{ - ' + settings['time_format'] + '}'
    },
    axisFormat : settings['time_format'],
    columnFormat: {
      month: 'ddd', // Mon
      week: 'ddd ' + settings['date_short'], // Mon 9/7
      day: 'dddd ' + settings['date_short'],  // Monday 9/7
      list: settings['date_agenda'],
      table: settings['date_agenda']
    },
    titleFormat: {
      month: 'MMMM yyyy',
      week: settings['dates_long'],
      day: 'dddd ' + settings['date_long'],
      list: settings['dates_long'],
      table: settings['dates_long']
    },
    listSections: rcmail.env.listSections !== undefined ? rcmail.env.listSections : settings['agenda_sections'],
    listRange: rcmail.env.listRange || settings['agenda_range'],
    tableCols: ['handle', 'date', 'time', 'title', 'location'],
    allDayText: rcmail.gettext('all-day', 'calendar'),
    buttonText: {
      day: rcmail.gettext('day', 'calendar'),
      week: rcmail.gettext('week', 'calendar'),
      month: rcmail.gettext('month', 'calendar'),
      table: rcmail.gettext('agenda', 'calendar')
    },
    listTexts: {
      until: rcmail.gettext('until', 'calendar'),
      past: rcmail.gettext('pastevents', 'calendar'),
      today: rcmail.gettext('today', 'calendar'),
      tomorrow: rcmail.gettext('tomorrow', 'calendar'),
      thisWeek: rcmail.gettext('thisweek', 'calendar'),
      nextWeek: rcmail.gettext('nextweek', 'calendar'),
      thisMonth: rcmail.gettext('thismonth', 'calendar'),
      nextMonth: rcmail.gettext('nextmonth', 'calendar'),
      future: rcmail.gettext('futureevents', 'calendar'),
      week: rcmail.gettext('weekofyear', 'calendar')
    },
    loading: function(isLoading) {
      rc_loading = rcmail.set_busy(isLoading, 'loading', rc_loading);
    },
    // event rendering
    eventRender: function(event, element, view) {
      if (view.name != 'month' && view.name != 'table') {
        var cont = element.find('.fc-event-title');
        if (event.location) {
          cont.after('<div class="fc-event-location">@&nbsp;' + Q(event.location) + '</div>');
          cont = cont.next();
        }
        if (event.description && showdesc) {
          cont.after('<div class="fc-event-description">' + Q(event.description) + '</div>');
        }
/* TODO: create icons black on white
        if (event.recurrence)
          element.find('.fc-event-time').append('<i class="fc-icon-recurring"></i>');
        if (event.alarms)
          element.find('.fc-event-time').append('<i class="fc-icon-alarms"></i>');
*/
      }
      if (view.name == 'table' && event.description && showdesc) {
        var cols = element.children().css('border', 0).length;
        element.after('<tr class="fc-event-row-secondary fc-event"><td colspan="'+cols+'" class="fc-event-description">' + Q(event.description) + '</td></tr>');
      }
    },
    viewDisplay: function(view) {
      // remove hard-coded hight and make contents visible
      window.setTimeout(function(){
        if (view.name == 'table') {
          $('div.fc-list-content').css('overflow', 'visible').height('auto');
        }
        else {
          $('div.fc-agenda-divider')
            .next().css('overflow', 'visible').height('auto')
            .children('div').css('overflow', 'visible').height('auto');
          }
          // adjust fixed height if vertical day slots
          var h = $('table.fc-agenda-slots:visible').height() + $('table.fc-agenda-allday:visible').height() + 4;
          if (h) $('table.fc-agenda-days td.fc-widget-content').children('div').height(h);
         }, 20);
    }
  });
  
  // activate settings form
  $('#propdescription').change(function(){
    showdesc = this.checked;
    fc.fullCalendar('render');
  });

});
