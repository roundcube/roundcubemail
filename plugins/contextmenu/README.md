Roundcube Webmail ContextMenu
=============================
This plugin creates contextmenus for various parts of Roundcube using commands
from the toolbars.

ATTENTION
---------
This is just a snapshot from the GIT repository and is **NOT A STABLE version
of ContextMenu**. It is Intended for use with the **GIT-master** version of
Roundcube and it may not be compatible with older versions. Stable versions of
ContextMenu are available from the [Roundcube plugin repository][rcplugrepo]
(for 1.0 and above) or the [releases section][releases] of the GitHub
repository.

License
-------
This plugin is released under the [GNU General Public License Version 3+][gpl].

Even if skins might contain some programming work, they are not considered
as a linked part of the plugin and therefore skins DO NOT fall under the
provisions of the GPL license. See the README file located in the core skins
folder for details on the skin license.

Install
-------
* Place this plugin folder into plugins directory of Roundcube
* Add contextmenu to $config['plugins'] in your Roundcube config

**NB:** When downloading the plugin from GitHub you will need to create a
directory called contextmenu and place the files in there, ignoring the root
directory in the downloaded archive.

[rcplugrepo]: https://plugins.roundcube.net/packages/johndoh/contextmenu
[releases]: https://github.com/johndoh/roundcube-contextmenu/releases
[gpl]: https://www.gnu.org/licenses/gpl.html