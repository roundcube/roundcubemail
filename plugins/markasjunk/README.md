Roundcube Webmail MarkAsJunk Plugin
===================================
This plugin adds "mark as spam" or "mark as not spam" button to the message
menu.

When not in the Junk mailbox:
  Messages are moved into the Junk mailbox and marked as read

When in the Junk mailbox:
  The buttons are changed to "mark as not spam" or "this message is not spam"
  and the message is moved to the Inbox


License
-------

This plugin is released under the [GNU General Public License Version 3+][gpl].

Even if skins might contain some programming work, they are not considered
as a linked part of the plugin and therefore skins DO NOT fall under the
provisions of the GPL license. See the README file located in the core skins
folder for details on the skin license.


Configuration
-------------

The default config file is plugins/markasjunk/config.inc.php.dist
Rename this to plugins/markasjunk/config.inc.php

All config parameters are optional.


The Learning Driver
-------------------

The learning driver allows you to perform additional processing on each message
marked as spam/ham. A driver must contain a class named markasjunk_{driver
file name}. The class must contain 3 functions:

**spam:** This function should take 2 arguments: an array of UIDs of message(s)
being marked as spam, the name of the mailbox containing those messages

**ham:** This function should take 2 arguments: an array of UIDs of message(s)
being marked as ham, the name of the mailbox containing those messages

**init:** Optional, this function should take 0 arguments. eg: allows drivers
to add JS to the page to control which of the spam/ham options are displayed.
The `jsevents` driver is available to show how to use the JS events.

Several drivers are provided by default they are:

**cmd_learn:** This driver calls an external command (for example salearn) to
process the message

**dir_learn:** This driver places a copy of the message in a predefined folder,
for example to allow for processing later

**email_learn:** This driver emails the message either as an attachment or
directly to a set address. This driver requires Roundcube 1.4 or above.

**sa_blacklist:** This driver adds the sender address of a spam message to the
users blacklist (or whitelist of ham messages) Requires SAUserPrefs plugin

**amavis_blacklist:** This driver adds the sender address of a spam message to
the users blacklist (or whitelist of ham messages) Requires Amacube plugin.
Driver by Der-Jan

**sa_detach:** If the message is a Spamassassin spam report with the original
email attached then this is detached and saved in the Inbox, the spam report is
deleted

**edit_headers:** Edit the message headers. Headers are edited using
preg_replace.

**WARNING:** Be sure to match the entire header line, including the name of the
header, and include the ^ and $ and test carefully before use on real messages.
This driver alters the message source


Running multiple drivers
------------------------

**WARNING:** This is very dangerous please always test carefully. Run multiple
drivers at your own risk! It may be safer to create one driver that does
everything you want.

It is possible to run multiple drivers when marking a message as spam/ham. For
example running sa_blacklist followed by cmd_learn or edit_headers and
cmd_learn. An [example multi-driver][multidriver] is available. This is a
starting point only, it requires modification for individual cases.


Spam learning commands
----------------------

Spamassassin:

```sa-learn --spam --username=%u %f``` or
```sa-learn --spam --prefs-file=/var/mail/%d/%l/.spamassassin/user_prefs %f```


Ham learning commands
---------------------

Spamassassin:

```sa-learn --ham --username=%u %f``` or
```sa-learn --ham --prefs-file=/var/mail/%d/%l/.spamassassin/user_prefs %f```


edit_headers example config
---------------------------

**WARNING:** These are simple examples of how to configure the driver options,
use at your own risk

```php
$config['markasjunk_spam_patterns'] = array(
  'patterns'     => array('/^(Subject:\s*)(.*)$/m'),
  'replacements' => array('$1[SPAM] $2')
);
```

```php
$config['markasjunk_ham_patterns'] = array(
  'patterns'     => array('/^(Subject:\s*)\[SPAM\](.*)$/m'),
  'replacements' => array('$1$2')
);
```

[gpl]: https://www.gnu.org/licenses/gpl.html
[multidriver]: https://gist.github.com/johndoh/8173505
