<?php

/**
 * Localizations for the Kolab calendaring utilities plugin
 *
 * Copyright (C) 2014, Kolab Systems AG
 *
 * For translation see https://www.transifex.com/projects/p/kolab/resource/libcalendaring/
 */

$labels = array();

// words for spoken dates
$labels['until'] = 'until';
$labels['at'] = 'at';

// alarms related labels
$labels['alarmemail'] = 'Send Email';
$labels['alarmdisplay'] = 'Show message';
$labels['alarmaudio'] = 'Play sound';
$labels['alarmdisplayoption'] = 'Message';
$labels['alarmemailoption'] = 'Email';
$labels['alarmaudiooption'] = 'Sound';
$labels['alarmat'] = 'at $datetime';
$labels['trigger@'] = 'on date';
$labels['trigger-M'] = 'minutes before';
$labels['trigger-H'] = 'hours before';
$labels['trigger-D'] = 'days before';
$labels['trigger+M'] = 'minutes after';
$labels['trigger+H'] = 'hours after';
$labels['trigger+D'] = 'days after';
$labels['triggerattime'] = 'at time';
$labels['addalarm'] = 'Add alarm';
$labels['removealarm'] = 'Remove alarm';

$labels['alarmtitle'] = 'Upcoming events';
$labels['dismissall'] = 'Dismiss all';
$labels['dismiss'] = 'Dismiss';
$labels['snooze'] = 'Snooze';
$labels['repeatinmin'] = 'Repeat in $min minutes';
$labels['repeatinhr'] = 'Repeat in 1 hour';
$labels['repeatinhrs'] = 'Repeat in $hrs hours';
$labels['repeattomorrow'] = 'Repeat tomorrow';
$labels['repeatinweek'] = 'Repeat in a week';

$labels['showmore'] = 'Show more...';

// recurrence related labels
$labels['recurring'] = 'Repeats';
$labels['frequency'] = 'Repeat';
$labels['never'] = 'never';
$labels['daily'] = 'daily';
$labels['weekly'] = 'weekly';
$labels['monthly'] = 'monthly';
$labels['yearly'] = 'annually';
$labels['rdate'] = 'on dates';
$labels['every'] = 'Every';
$labels['days'] = 'day(s)';
$labels['weeks'] = 'week(s)';
$labels['months'] = 'month(s)';
$labels['years'] = 'year(s)';
$labels['bydays'] = 'On';
$labels['untildate'] = 'the';
$labels['each'] = 'Each';
$labels['onevery'] = 'On every';
$labels['onsamedate'] = 'On the same date';
$labels['forever'] = 'forever';
$labels['recurrencend'] = 'until';
$labels['untilenddate'] = 'until date';
$labels['forntimes'] = 'for $nr time(s)';
$labels['first'] = 'first';
$labels['second'] = 'second';
$labels['third'] = 'third';
$labels['fourth'] = 'fourth';
$labels['last'] = 'last';
$labels['dayofmonth'] = 'Day of month';
$labels['addrdate'] = 'Add repeat date';
$labels['except'] = 'except';

// itip related labels
$labels['itipinvitation'] = 'Invitation to';
$labels['itipupdate'] = 'Update of';
$labels['itipcancellation'] = 'Cancelled:';
$labels['itipreply'] = 'Reply to';
$labels['itipaccepted'] = 'Accept';
$labels['itiptentative'] = 'Maybe';
$labels['itipdeclined'] = 'Decline';
$labels['itipdelegated'] = 'Delegate';
$labels['itipneeds-action'] = 'Postpone';
$labels['itipcomment'] = 'Your response';
$labels['itipeditresponse'] = 'Enter a response text';
$labels['itipsendercomment'] = 'Sender\'s comment: ';
$labels['itipsuppressreply'] = 'Do not send a response';

$labels['itipobjectnotfound'] = 'The object referred by this message was not found in your account.';
$labels['itipsubjectaccepted'] = '"$title" has been accepted by $name';
$labels['itipsubjecttentative'] = '"$title" has been tentatively accepted by $name';
$labels['itipsubjectdeclined'] = '"$title" has been declined by $name';
$labels['itipsubjectin-process'] = '"$title" is in-process by $name';
$labels['itipsubjectcompleted'] = '"$title" was completed by $name';
$labels['itipsubjectcancel'] = 'Your participation in "$title" has been cancelled';
$labels['itipsubjectdelegated'] = '"$title" has been delegated by $name';
$labels['itipsubjectdelegatedto'] = '"$title" has been delegated to you by $name';

$labels['itipnewattendee'] = 'This is a reply from a new participant';
$labels['updateattendeestatus'] = 'Update the participant\'s status';
$labels['acceptinvitation'] = 'Do you accept this invitation?';
$labels['acceptattendee'] = 'Accept participant';
$labels['declineattendee'] = 'Decline participant';
$labels['declineattendeeconfirm'] = 'Enter a message to the declined participant (optional):';
$labels['rsvpmodeall'] = 'The entire series';
$labels['rsvpmodecurrent'] = 'This occurrence only';
$labels['rsvpmodefuture'] = 'This and future occurrences';

$labels['itipsingleoccurrence'] = 'This is a <em>single occurrence</em> out of a series of events';
$labels['itipfutureoccurrence'] = 'Refers to <em>this and all future occurrences</em> of a series of events';
$labels['itipmessagesingleoccurrence'] = 'The message only refers to this single occurrence';
$labels['itipmessagefutureoccurrence'] = 'The message refers to this and all future occurrences';

$labels['youhaveaccepted'] = 'You have accepted this invitation';
$labels['youhavetentative'] = 'You have tentatively accepted this invitation';
$labels['youhavedeclined'] = 'You have declined this invitation';
$labels['youhavedelegated'] = 'You have delegated this invitation';
$labels['youhavein-process'] = 'You are working on this assignment';
$labels['youhavecompleted'] = 'You have completed this assignment';
$labels['youhaveneeds-action'] = 'Your response to this invitation is still pending';

$labels['youhavepreviouslyaccepted'] = 'You have previously accepted this invitation';
$labels['youhavepreviouslytentative'] = 'You have previously accepted this invitation tentatively';
$labels['youhavepreviouslydeclined'] = 'You have previously declined this invitation';
$labels['youhavepreviouslydelegated'] = 'You have previously delegated this invitation';
$labels['youhavepreviouslyin-process'] = 'You have previously reported to work on this assignment';
$labels['youhavepreviouslycompleted'] = 'You have previously completed this assignment';
$labels['youhavepreviouslyneeds-action'] = 'Your response to this invitation is still pending';

$labels['attendeeaccepted'] = 'Participant has accepted';
$labels['attendeetentative'] = 'Participant has tentatively accepted';
$labels['attendeedeclined'] = 'Participant has declined';
$labels['attendeedelegated'] = 'Participant has delegated to $delegatedto';
$labels['attendeein-process'] = 'Participant is in-process';
$labels['attendeecompleted'] = 'Participant has completed';
$labels['notanattendee'] = 'You\'re not listed as an attendee of this object';
$labels['outdatedinvitation'] = 'This invitation has been replaced by a newer version';

$labels['importtocalendar'] = 'Save to my calendar';
$labels['removefromcalendar'] = 'Remove from my calendar';
$labels['updatemycopy'] = 'Update my copy';
$labels['openpreview'] = 'Open Preview';

$labels['deleteobjectconfirm'] = 'Do you really want to delete this object?';
$labels['declinedeleteconfirm'] = 'Do you also want to delete this declined object from your account?';

$labels['delegateinvitation'] = 'Delegate Invitation';
$labels['delegateto'] = 'Delegate to';
$labels['delegatersvpme'] = 'Keep me informed about updates of this incidence';
$labels['delegateinvalidaddress'] = 'Please enter a valid email address for the delegate';

$labels['savingdata'] = 'Saving data...';

// attendees labels
$labels['expandattendeegroup'] = 'Substitute with group members';
$labels['expandattendeegroupnodata'] = 'Unable to substitute this group. No members found.';
$labels['expandattendeegrouperror'] = 'Unable to substitute this group. It might contain too many members.';
$labels['expandattendeegroupsizelimit'] = 'This group contains too many members for substituting.';

