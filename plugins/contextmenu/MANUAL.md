# Contextmenu manual

This file provides information for plugin and skin developers. The Contextmenu plugin can be extended by other plugins; new menus can be created and existing menus manipulated. For basic installation information please see the [README](./README.md).

- [Global options](#global-options)
- [Creating a new Contextmenu](#creating-a-new-contextmenu)
- [Working with an existing Contextmenu](#working-with-an-existing-contextmenu)
- [Events](#events)
- [Contextmenu and skins](#contextmenu-and-skins)

## Global options

The following global options are available:
* no_right_click_on_menu - (boolean) treat right click as left click when on the contextmenu
* skip_commands - (array) Roundcube commands that should be ignored by the contextmenu
* always_enable_commands - (array) Roundcube commands that should rename enabled, once clicked, until the menu is closed
* command_pattern - (regex) pattern matching the Roundcube command call
* popup_attrib - (string) attribute that contains the Roundcube popup call
* popup_pattern - (regex) pattern matching the Roundcube popup call
* popup_func - (string) function used by Contextmenu when opening a popup
* classes - (object) CSS classes for various aspects of the menu
    * container - class(es) applied to the menu container
    * mainmenu - class(es) applied to the menu container of top level menus
    * submenu - class(es) applied to the menu container of sub menus
    * popupmenu - class(es) applied to popup menus created by this plugin
    * button_remove - thse class(es) will be removed from the context menu button (for example classes that are used in the UI which are not needed in the context menu)
    * button_active - class(es) for buttons in an active state
    * button_disabled - class(es) for buttons in an inactive state
* menu_defaults - (object) default options for Contextmenu objects
* menu_events - (object) default events for Contextmenu objects

## Creating a new Contextmenu

By default Contextmenu is added to the `mail` and `addressbook` tasks in Roundcube. It can be added to other tasks by calling the PHP function `include_plugin()` like this `$this->include_plugin('contextmenu');` from inside your plugin. This function checks if the Contextmenu plugin is available and loads it if possible.

The JavaScript function `rcmail.contextmenu.init()` creates the Contextmenu object. If the Contextmenu functions are enabled in the UI then the `rcmail.env.contextmenu` variable JavaScript will be set to true. Setting this variable to false will disable all context menus.

```js
var menu = rcmail.contextmenu.init(props, events);
```

The functions takes 2 parameters:

`props` (required) JSON object:
* menu_name - (string) required - A friendly name for the Contextmenu, it is also used as the ID for the Contextmenu element.
* menu_source - (string or array) required - See [Menu sources](#menu-sources) for details.
* list_object - (object) optional - If Contextmenu is used on a Roundcube list object then that list object should be set here (e.g. `rcmail.message_list`), set to `null` if using Contextmenu on another element. It is set to `rcmail.message_list` by default.
* source_class - (string) optional - The CSS class applied to the triggering element, `contextRow` by default.
* mouseover_timeout - (int) optional - The delay for displaying submenus on mouseover, set to -1 to disable mouseover. `400` by default.
* classes - (object) optional - CSS classes for various aspects of the menu:
    * source - class(es) applied to object on which the menu was triggered
    * div - class(es) applied to the menu container
    * ul - class(es) applied to the UL object in the menu
    * a - class(es) applied to the A objects in the menu
    * span - class(es) applied to spans inside in the A objects
    * sub_button_a - class(es) applied to A objects which have a submenu
    * sub_button_span - class(es) applied to the submenu indicator element, set to null to remove this span
* modal - (boolean) optional - Display the menu in a modal fashion

`events` (optional) JSON object. Contextmenu triggers a number of events during execution, for example `command` is tiggered when the user clicks on an item in the menu. Full details of all the events can be found in the [Events](#events) section of this file. This parameters allows a plugin author to attach their own functions to the Contextmenu events, overriding the defaults.

Creating a simple Contextmenu looks like this:
```js
var menu = rcmail.contextmenu.init(
  {'menu_name': 'messagelist', 'menu_source': '#messagetoolbar'},
  {'beforeactivate': function(p) {
    rcmail.env.contextmenu_selection = p.ref.list_selection(true);
  },
  'afteractivate': function(p) {
    p.ref.list_selection(false, rcmail.env.contextmenu_selection);
  }});
```

The Contextmenu must then be attached to the element(s) in the UI. For example:
```js
$(el).on("contextmenu", function(e) {
  rcmail.contextmenu.show(e, obj, source_id, menu);
});
```

The `rcmail.contextmenu.show` displays a Contextmenu on the screen. It has the following parameters:
* e - (event) The JS event object
* obj - (object) The object the Contextmenu is active on (typically `this`)
* source_id - (string) The object ID used by core function. When using the Contextmenu on a Roundcube list object then the ID can be retrieved from the object, like this:
```js
if (uid = list_object.get_row_uid(this)) {
  rcmail.contextmenu.show(e, this, uid, menu);
}
```
The ID can also be extracted from the originial function call, like this:
```js
if (source.attr('onclick') && source.attr('onclick').match(rcmail.contextmenu.command_pattern)) {
  rcmail.contextmenu.show(e, this, RegExp.$2, menu);
}
```
* menu - (object) The menu object as created by `rcmail.contextmenu.init`

## Menu sources

The menu_source parameter can be a string (for building the Contextmenu from a single source) or an array of jQuery selectors. To add custom elements to the Contextmenu a menu element must first be added to the IU, for example:
```php
$li = '';
$li .= html::tag('li', null, $this->api->output->button(array('command' => 'plugin.myplugin.command1', 'type' => 'link', 'class' => 'myclass1', 'label' => 'myplugin.command1')));
$li .= html::tag('li', null, $this->api->output->button(array('command' => 'plugin.myplugin.command2', 'type' => 'link', 'class' => 'myclass2', 'label' => 'myplugin.command2')));
$li .= html::tag('li', null, $this->api->output->button(array('command' => 'plugin.myplugin.command3', 'type' => 'link', 'class' => 'myclass3', 'label' => 'myplugin.command3')));
$out = html::tag('ul', array('id' => 'mymenu'), $li);
$this->api->output->add_footer(html::div(array('style' => 'display: none;'), $out));
```
The Contextmenu can then be invoked like this:
```js
var menu = rcmail.contextmenu.init({menu_name: 'mymenu', menu_source: '#mymenu'});
```
A JSON object can also be used instead of an element selector to add simple elements to the Contextmenu. For example:
```js
var menu = rcmail.contextmenu.init({menu_name: 'mymenu', menu_source: ['#mymenu', {label: 'extra item', command: 'plugin.myplugin.command', props: '', class: 'myclass'}]});
```
The JSON object can have:
* `label` (string) required - text for the menu element
* `command` (string) required - the Roundcube command to execute on click
* `props` (string) optional - arguments to pass to the Roundcube command
* `classes` (string) optional - classes to apply to the menu element

## Working with an existing Contextmenu

A global event `contextmenu_init` is triggered when a new Contextmenu is initialised so other plugins can interact with it.
```js
rcmail.addEventListener('contextmenu_init', function(menu) {
  // identify the folder list context menu
  if (menu.menu_name == 'folderlist') {
    // add a shortcut to the folder management screen to the end of the menu
    menu.menu_source.push({label: 'Manage folders', command: 'folders', props: '', classes: 'managefolders'});

    // make sure this new shortcut is always active
    menu.addEventListener('activate', function(p) {
      if (p.command == 'folders') {
        return true;
      }
    });
  }
});
```

The Contextmnu object is passed to the function allowing properities to be manipulated and/or new events to be attached. By default the following menus are created:

On the mail screen:
* messagelist - attached to rows in the message list
* folderlist - attached to entries in the folder list

On the message composing screen:
* composeto - attached to contacts in the contacts search widget

On the address book screen:
* contactlist - attached to rows in the contacts list
* abooklist - attached to addressbooks and groups

To prevent an element from appearing in a Contextmenu give it the class `rcm-ignore`.

To make sure an element in the Contextmenu is always active give it the class `rcm-active`.

The environmental variable `rcmail.env.context_menu_source_id` contains the ID of the specific element that the Contextmenu was triggered on, this is the `source_id` passed to `rcmail.contextmenu.show`

## Events

The following events are triggered by Contextmenu:

`init` - Triggered once the Contextmenu object has been initalized
* ref - The Contextmenu object

`addmenuitem` - Triggered when an element is being added to the menu
* ref - The Contextmenu object
* el - The element being added
This function can return the following in a JSON object:
* result - The element to be added to the menu
* abort - Boolean, abort the default command execution

`beforecommand` - Triggered when an element in the menu is clicked
* ref - The Contextmenu object
* el - The HTML object being clicked
* command - The Roundcube command to run
* args - The arguments being passed to the Roundcube command
This function can return the following in a JSON object:
* abort - Boolean, abort the default command execution, other events like `command` and `aftercommand` will not be executed
* result - Result of the command, if abort if true this is returned to the client

`command` - Triggered when an element in the menu is clicked
* ref - The Contextmenu object
* el - The HTML object being clicked
* command - The Roundcube command to run
* args - The arguments being passed to the Roundcube command
* evt - The JS event object
This function can return the result of the command to pass back to the client

By default the following function is used:

```js
function(p) {
  if (!$(p.el).hasClass(rcmail.contextmenu.settings.classes.button_active))
    return;

  // enable the required command
  var prev_command = rcmail.commands[p.command];
  rcmail.enable_command(p.command, true);
  var result = rcmail.command(p.command, p.args, p.el, p.evt);
  rcmail.enable_command(p.command, prev_command);

  // leave commands in always_enable_commands enabled, they are disabled when menu is closes
  if ($.inArray(p.command, rcmail.contextmenu.settings.always_enable_commands) >= 0 && prev_command === false) {
      rcmail.contextmenu.vars.commands_disable_on_hide.push(p.command);
  }
  else {
      rcmail.enable_command(p.command, prev_command);
  }

  return result;
}
```

The Contextmenu works by faking a message selection and calling the normal Roundcube command before putting everything back to normal.

`aftercommand` - Triggered when an element in the menu is clicked
* ref - The Contextmenu object
* el - The HTML object being clicked
* command - The Roundcube command to run
* args - The arguments being passed to the Roundcube command

`submenu_toggle` - Triggered when a hidden menu is being loaded for display in the Contextmenu
* id - The ID of the menu being shown
* ref - The Contextmenu object
* show - Boolean, show the menu or not

`beforeactivate` - Triggered when a Contextmenu is displayed
* ref - The Contextmenu object
* source - The element the Contextmenu has been triggered on
* originalEvent - The triggering event
This function can return the following in a JSON object:
* abort - Boolean, abort the default activation process, other events like `activate` and `afteractivat` will not be executed
* show - Boolean, show the menu or not

`activate` - Triggered when a Contextmenu is displayed, a separate event is triggered for each menu item
* el - The menu element being activated
* btn - The ID of the button in the UI on which the menu element is based
* source - The element the Contextmenu has been triggered on
* command - The command the menu element executes
* enabled - Boolean, if the menu element is active or not
This function can return a boolean value: true to activate the element, false to disable it

`afteractivate` - Triggered when a Contextmenu is displayed
* ref - The Contextmenu object
* source - The element the Contextmenu has been triggered on
* originalEvent - The triggering event

`insertitem` - Triggered each time an item is added to a Contextmenu
* item - The HTML object to be added to the menu

For example permanently deactivating the delete option on the message list Contextmenu could be done like this:
```js
rcmail.addEventListener('contextmenu_init', function(menu) {
  if (menu.menu_name == 'messagelist') {
    menu.addEventListener('activate', function(p) {
      var is_delete = false;

      $.each(rcmail.buttons['delete'], function() {
        if (this.id == p.btn) {
          is_delete = true;
          return false;
        }
      });

      return is_delete ? false : null;
    });
  }
});
```

Note: A plus sign (+) can be prepended to an event name, for example `+beforeactivate`, to add the new event handler to the existing one used by the core rather than replacing it.

## menu-change JS event

Contextmenu listens for a menu-change event on all source elements. If this event is triggered then the exisiting Contextmenu object is destroyed and will be rebuilt next time its called.

## Contextmenu and skins

In the plugin folder there is a skins folder, and inside that there is a folder for each skin. Two files are needed for each skin: contextmenu.css - CSS for the menu, and functions.js containing the JavaScript to create Contextmenus in the skin. This plugin provides some helper functions for adding the default menus to the UI, they are: `rcmail.contextmenu.init_list()` for attaching a Contextmenu to a Roundcube list object, `rcmail.contextmenu.init_folder()` for attaching a Contextmenu to the folder list on the mail screen, and `rcmail.contextmenu.init_addressbook()` for attaching a Contextmenu to the address book and groups list on the address book screen. Each function expects the same 3 parameters:
* The HTML object or jQuery selector of the element to attach to.
* A props object, see [Creating a new Contextmenu](#creating-a-new-contextmenu)
* An events object, see [Events](#events)

Contextmenus must be defined separately for each skin because they are built from the toolbar elements in the UI which may have different IDs as well as different construction on each skin.