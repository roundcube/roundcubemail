Roundcube Context Menu Folder Manager
=====================================
Technical plugin name is [contextmenu_folder][contextmenu_folder_link].

| Folder list context menu      | Folder list control menu      | Message list context menu      |
|:-----------------------------:|:-----------------------------:|:------------------------------:|
| ![][folder_list_context_menu] | ![][folder_list_control_menu] | ![][message_list_context_menu] |

This plugin can be useful for users who want to work efficiently with large number
of mailboxes or imap folders (anywhere form few hundred to few thousand folders).

Plugin [contextmenu_folder][contextmenu_folder_link] provides
context menus for the following folder operations:
* create/delete/rename/locate mailbox imap folder
* apply mailbox tree view filters, grouped in categories: [`active`, `favorite`]
* where each category uses filter selectors from: [`unread`, `special`, `selected`, `transient`, `predefined`]

Filter selectors support these features:
* `unread` : this filter finds mailboxes with unread messages
* `special` : will include special imap folders: [`inbox`, `drafts`, `sent`, `junk`, `trash`]
* `selected` : represents folder collection which can be selected/unselected into by the user
* `transient` : based on automatic folder collection, which tracks created/deleted/renamed mailboxes
* `predefined` : static user-defined list of mailbox folders, which is more "permanent" then `selected` 

Dependencies
------------
Plugin [contextmenu_folder][contextmenu_folder_link] requires few other plugins:
* `jqueryui`: [jquery ui plugin, included with roundcube][jqueryui_link]
* `contextmenu`: [context menu plugin, from roundcube repo][contextmenu_link]

Manual Install
--------------
Installation can be done in two steps:
providing resources and activating configuration.

1) Provision plugin resources.
For example, for [roundcube on archlinux][roundcube_arch]:
```
cd /usr/share/webapps/roundcubemail/plugins

rm -r -f contextmenu
git clone https://github.com/random-cuber/contextmenu.git contextmenu

rm -r -f contextmenu_folder
git clone https://github.com/random-cuber/contextmenu_folder.git contextmenu_folder
```

2) Activate plugin in `roundcube` configuration.
For example, for [roundcube on archlinux][roundcube_arch]:
```
cat /etc/webapps/roundcubemail/config/config.inc.php

$config['plugins'] = array(
    'jqueryui',           // dependency
    'contextmenu',        // dependency
    'contextmenu_folder', // plugin proper
);
```

Settings
--------

Navigate to:
```
Settings -> Preferences -> Mailbox View -> Folder Menu
```

Menu entries:
* `TODO` : TODO

Available features are described in [plugin default config][default_config].

Operation
---------

1) Folder list context menu:

Navigate to:
```
Mail -> [Mailbox list] -> [Access context menu]
```

Menu entries:
* `Select folder` : append given mailbox to the `selected` collection
* `Unselect folder` : remove given mailbox from the `selected` collection
* `Create folder` : create new sub folder using given mailbox as a parent
* `Delete folder` : completely remove given folder and its messages
* `Rename folder` : change mailbox name, keep existing messages in new folder
* `Folder tree read` : mark all read in the given folder and all of the descendants

2) Folder list control menu:

Navigate to:
```
Mail -> [Mailbox list] -> [Click footer button]
```

Menu entries:
* `Show all` : remove all filters and display all available mailboxes
* `Show active` : apply mailbox filters form `active` category (see settings)
* `Show favorite` : apply mailbox filters form `favorite` category (see settings) 
* `Reset selected` : remove all mailboxes form the `selected` collection
* `Reset transient` : remove all mailboxes form the `transient` collection 
* `Expand all` : expand all mailboxes in the current view
* `Collapse all` : collapse all mailboxes in the current view 
* `Locate folder` : present a search dialog to find a mailbox via simple name match

Note that `footer button` will change appearance to reflect `all/active/favorite` view.

3) Message list context menu:

Navigate to:
```
Mail -> [Message list] -> [Access context menu]
```

Menu entries:
* `Create folder` : create imap folder based on company/contact name derived from email headers
* `Copy to folder` : copy selected mailbox messages to a folder located by quick folder name search
* `Move to folder` : move selected mailbox messages to a folder located by quick folder name search

Translation
-----------

Contribute to [translation via transifex][translation].

[roundcube_arch]: https://wiki.archlinux.org/index.php/Roundcube
[jqueryui_link]: https://github.com/roundcube/roundcubemail/tree/master/plugins/jqueryui
[contextmenu_link]: http://plugins.roundcube.net/packages/johndoh/contextmenu
[contextmenu_folder_link]: http://plugins.roundcube.net/packages/random-cuber/contextmenu_folder

[folder_list_context_menu]:  https://raw.githubusercontent.com/random-cuber/contextmenu_folder/master/build/folder_list_context_menu.png
[folder_list_control_menu]:  https://raw.githubusercontent.com/random-cuber/contextmenu_folder/master/build/folder_list_control_menu.png
[message_list_context_menu]: https://raw.githubusercontent.com/random-cuber/contextmenu_folder/master/build/message_list_context_menu.png

[default_config]: https://github.com/random-cuber/contextmenu_folder/blob/master/default.inc.php

[translation]: https://www.transifex.com/random-cuber/contextmenu_folder/
