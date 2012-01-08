<?php

/*
 +-----------------------------------------------------------------------+
 | localization/de_CH/labels.inc                                         |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Christoph Wickert <Unknown>                                   |
 +-----------------------------------------------------------------------+
 @version $Id$
*/

$labels = array();
$labels['filters'] = 'Filter';
$labels['managefilters'] = 'Verwalte eingehende Nachrichtenfilter';
$labels['filtername'] = 'Filtername';
$labels['newfilter'] = 'Neuer Filter';
$labels['filteradd'] = 'Filter hinzufügen';
$labels['filterdel'] = 'Filter löschen';
$labels['moveup'] = 'Nach oben';
$labels['movedown'] = 'Nach unten';
$labels['filterallof'] = 'UND (alle Regeln müssen zutreffen)';
$labels['filteranyof'] = 'ODER (eine der Regeln muss zutreffen';
$labels['filterany'] = 'Für alle Nachrichten';
$labels['filtercontains'] = 'enthält';
$labels['filternotcontains'] = 'enthält nicht';
$labels['filteris'] = 'ist gleich';
$labels['filterisnot'] = 'ist ungleich';
$labels['filterexists'] = 'ist vorhanden';
$labels['filternotexists'] = 'nicht vorhanden';
$labels['filtermatches'] = 'entspricht Ausdruck';
$labels['filternotmatches'] = 'entspricht nicht Ausdruck';
$labels['filterregex'] = 'trifft regulären Ausdruck';
$labels['filternotregex'] = 'entspricht regulärem Ausdruck';
$labels['filterunder'] = 'unter';
$labels['filterover'] = 'über';
$labels['addrule'] = 'Regel hinzufügen';
$labels['delrule'] = 'Regel löschen';
$labels['messagemoveto'] = 'Verschiebe Nachricht nach';
$labels['messageredirect'] = 'Leite Nachricht um nach';
$labels['messagecopyto'] = 'Kopiere Nachricht nach';
$labels['messagesendcopy'] = 'Sende Kopie an';
$labels['messagereply'] = 'Antworte mit Nachricht';
$labels['messagedelete'] = 'Nachricht löschen';
$labels['messagediscard'] = 'Discard with message';
$labels['messagesrules'] = 'Für eingehende Nachrichten:';
$labels['messagesactions'] = 'Führe folgende Aktionen aus:';
$labels['add'] = 'Hinzufügen';
$labels['del'] = 'Löschen';
$labels['sender'] = 'Absender';
$labels['recipient'] = 'Empfänger';
$labels['vacationaddresses'] = 'Zusätzliche Liste von Empfängern (Komma getrennt):';
$labels['vacationdays'] = 'Antwort wird erneut gesendet nach (in Tagen):';
$labels['vacationreason'] = 'Inhalt der Nachricht (Abwesenheitsgrund):';
$labels['vacationsubject'] = 'Betreff';
$labels['rulestop'] = 'Regelauswertung anhalten';
$labels['enable'] = 'Aktivieren/Deaktivieren';
$labels['filterset'] = 'Filtersätze';
$labels['filtersets'] = 'Filtersätze';
$labels['filtersetadd'] = 'Filtersatz anlegen';
$labels['filtersetdel'] = 'Aktuellen Filtersatz löschen';
$labels['filtersetact'] = 'Aktuellen Filtersatz aktivieren';
$labels['filtersetdeact'] = 'Aktuellen Filtersatz deaktivieren';
$labels['filterdef'] = 'Filterdefinition';
$labels['filtersetname'] = 'Filtersatzname';
$labels['newfilterset'] = 'Neuer Filtersatz';
$labels['active'] = 'aktiv';
$labels['none'] = 'keine';
$labels['fromset'] = 'aus Filtersatz';
$labels['fromfile'] = 'aus Datei';
$labels['filterdisabled'] = 'Filter deaktiviert';
$labels['countisgreaterthan'] = 'Anzahl ist grösser als';
$labels['countisgreaterthanequal'] = 'Anzahl ist gleich oder grösser als';
$labels['countislessthan'] = 'Anzahl ist kleiner als';
$labels['countislessthanequal'] = 'Anzahl ist gleich oder kleiner als';
$labels['countequals'] = 'Anzahl ist gleich';
$labels['countnotequals'] = 'Anzahl ist ungleich';
$labels['valueisgreaterthan'] = 'Wert ist grösser als';
$labels['valueisgreaterthanequal'] = 'Wert ist gleich oder grösser als';
$labels['valueislessthan'] = 'Wert ist kleiner';
$labels['valueislessthanequal'] = 'Wert ist gleich oder kleiner als';
$labels['valueequals'] = 'Wert ist gleich';
$labels['valuenotequals'] = 'Wert ist ungleich';
$labels['setflags'] = 'Setze Markierungen';
$labels['addflags'] = 'Füge Markierung hinzu';
$labels['removeflags'] = 'Entferne Markierung';
$labels['flagread'] = 'gelesen';
$labels['flagdeleted'] = 'Gelöscht';
$labels['flaganswered'] = 'Beantwortet';
$labels['flagflagged'] = 'Markiert';
$labels['flagdraft'] = 'Entwurf';
$labels['filtercreate'] = 'Filter erstellen';
$labels['usedata'] = 'Die folgenden Daten im Filter benutzen:';
$labels['nextstep'] = 'Nächster Schritt';
$labels['...'] = '...';
$labels['advancedopts'] = 'Erweiterte Optionen';
$labels['body'] = 'Inhalt';
$labels['address'] = 'Adresse';
$labels['envelope'] = 'Umschlag';
$labels['modifier'] = 'Wandler';
$labels['text'] = 'Text';
$labels['undecoded'] = 'kodiert (roh)';
$labels['contenttype'] = 'Inhaltstyp';
$labels['modtype'] = 'Typ:';
$labels['allparts'] = 'alle';
$labels['domain'] = 'Domain';
$labels['localpart'] = 'lokaler Teil';
$labels['user'] = 'Benutzer';
$labels['detail'] = 'Detail';
$labels['comparator'] = 'Komparator';
$labels['default'] = 'Vorgabewert';
$labels['octet'] = 'strikt (Oktet)';
$labels['asciicasemap'] = 'Gross-/Kleinschreibung ignorieren';
$labels['asciinumeric'] = 'numerisch (ascii-numeric)';
$labels['filterunknownerror'] = 'Unbekannter Serverfehler';
$labels['filterconnerror'] = 'Kann nicht zum Sieve-Server verbinden';
$labels['filterdeleteerror'] = 'Fehler beim des löschen  Filters. Serverfehler';
$labels['filterdeleted'] = 'Filter erfolgreich gelöscht';
$labels['filtersaved'] = 'Filter gespeichert';
$labels['filtersaveerror'] = 'Serverfehler, konnte den Filter nicht speichern.';
$labels['filterdeleteconfirm'] = 'Möchten Sie den Filter löschen ?';
$labels['ruledeleteconfirm'] = 'Sicher, dass Sie die Regel löschen wollen?';
$labels['actiondeleteconfirm'] = 'Sicher, dass Sie die ausgewaehlte Aktion löschen wollen?';
$labels['forbiddenchars'] = 'Unerlaubte Zeichen im Feld';
$labels['cannotbeempty'] = 'Feld darf nicht leer sein';
$labels['ruleexist'] = 'Ein Filter mit dem angegebenen Namen existiert bereits.';
$labels['setactivateerror'] = 'Filtersatz kann nicht aktiviert werden. Serverfehler.';
$labels['setdeactivateerror'] = 'Filtersatz kann nicht deaktiviert werden. Serverfehler.';
$labels['setdeleteerror'] = 'Filtersatz kann nicht gelöscht werden. Serverfehler.';
$labels['setactivated'] = 'Filtersatz erfolgreich aktiviert.';
$labels['setdeactivated'] = 'Filtersatz erfolgreich deaktiviert.';
$labels['setdeleted'] = 'Filtersatz erfolgreich gelöscht.';
$labels['setdeleteconfirm'] = 'Sind Sie sicher, dass Sie den ausgewählten Filtersatz löschen möchten?';
$labels['setcreateerror'] = 'Filtersatz kann nicht erstellt werden. Serverfehler.';
$labels['setcreated'] = 'Filter erfolgreich erstellt.';
$labels['activateerror'] = 'Filter kann nicht aktiviert werden. Serverfehler.';
$labels['deactivateerror'] = 'Filter kann nicht deaktiviert werden. Serverfehler.';
$labels['activated'] = 'Filter erfolgreich deaktiviert.';
$labels['deactivated'] = 'Filter erfolgreich aktiviert.';
$labels['moved'] = 'Filter erfolgreich verschoben.';
$labels['moveerror'] = 'Filter kann nicht verschoben werden. Serverfehler.';
$labels['nametoolong'] = 'Filtersatz kann nicht erstellt werden. Name zu lang.';
$labels['namereserved'] = 'Reservierter Name.';
$labels['setexist'] = 'Filtersatz existiert bereits.';
$labels['nodata'] = 'Mindestens eine Position muss ausgewählt werden!';

