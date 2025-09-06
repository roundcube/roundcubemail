*************
Overview
*************

The screen of the calendar module presents the following parts: the :ref:`Calendar View <calendar-view>`
itself, a small :ref:`Calendar Widget <calendar-minicalendar>` the :ref:`calendar-lists` as well as the usual
toolbar and search box.


.. index:: Month View, Week View, Day View, Agenda
.. _calendar-view:

Calendar View
=============

The central part of the screen displays the schedule with events from the active calendars matching the current
date range. The active date range is displayed above the calendar in the toolbar area and can be moved forward or
backward in time using the arrow buttons right next to the title.

.. image:: _static/_skin/calendar-header.png


Change Views
------------

You can view your calendar events in Day, Week, Month or Agenda view. Toggle the view mode using the toolbar buttons
above the calendar view.

**Day**
    All events of a single day appear at the time the begin and spawn a box until their end time. The time
    scale is displayed in the left side of the view. All-day events appear at the top.

**Week**
    Similar to the day view but lists all days of the week horizontally. All-day events again appear at the top.

**Month**
    Shows all events of the selected month at a time. Each event only appears as a single line and if there are
    more events in a day than can be listed, a number at the bottom of the day field indicates that. Click that
    link to open a zoomed view of that single day.

**Agenda**
    The agenda view shows a list of events for the selected range in a chronological order and divided by
    headers denoting either days, weeks or months. Both the number of the days considered for the listing as well
    as the mode how to divide list can be adjusted with the controls at the bottom of the agenda view.

.. _calendar-minicalendar:

For all the views, the small calendar on the left highlights the currently listed days.

Go to a specific Date
---------------------

Use the mini calendar widget on the left to jump to a specific date. Simply click a date and the date range of the current
view moves to include the selected day. The left/right arrows in the mini calendar's header quickly cycle through the months.
Use the drop-down menus hidden under the month and year display in the widget header to directly jump to another month or year.

A shortcut to switch the calendar view back to today or the current week provides the *Today* button located in the toolbar.


Show Event Details
------------------

Click an event box in the calendar view to open a dialog displaying all details of the event.


Searching Events
----------------

The search box above the calendar view lets you quickly get a list of events matching the entered keyword
in either the title, location, description or attendees. Enter the search term into the box and press <Enter>
on your keyboard to start the search. The calendar view will switch to *Agenda* mode in order to display
a list of matches. Of course you can switch the view again to display the search results differently.

.. note::  Events are searched within a certain date reange only which is displayed above the calendar view.
    Use the mini calendar widget or the arrow toolbar buttons and the range selector below the agenda view
    to adjust the time frame to search in.

For searching as well as for normal views, only events from active calendars are displayed. Use the checkboxes
in the :ref:`calendar-lists` to add or hide events from different calendars.

Reset the search by clicking the *Reset search* icon on the right border of the search box. This will
also switch the calendar view to whatever mode you had before the search.



.. index:: Calendars
.. _calendar-lists:

Calendars List
==============

Events can be organized in different calendars which are all displayed in the lower left list.
Use the checkboxes in that list to show or hide events from the specific calendars in the main view.

.. only:: kolab

    Beside your personal calendars, the list also displays calendars shared by other users
    or ones that are shared amongst your workgroup. Small icons in the list give a hint
    about the origin and some of them are possibly read-only which is denoted with a small lock icon.


Colorized Events
----------------

In order to better distinguish the events from various calendars in the calendar view, calendars have
a color assigned which is used to colorize the events on the screen. Check the :ref:`settings-calendar`
for more advanced options how to colorize events in the calendar view.

You can create any number of calendars to store all your events and name them individually.


Create a New Calendar
---------------------

1. Click the + icon in the calendars list footer.
2. In the dialog, give the new calendar a unique name and assign a color.
3. Click *Save* to create it.

The calendar view will reload and list the new calendar on the left.

.. _calendar-edit-properties:

Edit Calendar Names and Settings
--------------------------------

1. Select the calendar to edit by clicking it in the list.
2. Click the gear icon in the calendars list footer and select *Edit* from the options menu.
3. Adjust name, color or reminders settings in the edit dialog.
4. Click *Save* to finally update the calendar.

Remove entire Calendars
-----------------------

1. Select the calendar to edit by clicking it in the list.
2. Click the gear icon in the calendars list footer and select *Remove* from the options menu.
3. After a confirmation dialog, the selected calendar with all its events will be deleted.
   Caution: This action cannot be undone!
 