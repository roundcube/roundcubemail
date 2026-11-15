DAAK-Redmono Mail UI
====================

A modern, high-clarity soft-sage floating workspace skin for Roundcube Webmail,
extending the core `elastic` skin.

Architecture
------------
- **Child skin inheritance (`"extends": "elastic"`)**: Inherits all Elastic templates,
  accessibility semantics, FontAwesome icons, Bootstrap 4 grid, and plugin hooks.
- **Floating Workspace Shell**: Soft-sage gradient frame (`#e3ebe9` -> `#edf3f1`) housing
  a 224px navigation rail and a rounded white primary surface (`border-radius: 26px`).
- **Unified Login & Internal Views**: Centered clean login card, inline docked folder tree,
  pill search bars, crisp toolbar icon alignment (`white-space: nowrap`), and full styling
  across Mail, Compose, Contacts, and all Settings panes (including ManageSieve filters & vacation).
- **Internationalization**: Uses standard Roundcube `<roundcube:label>` tags alongside
  automatic `en` / `tr` dynamic frame copy and `<roundcube:object name="productname" />`.

Quick Enable (`config/config.inc.php`)
--------------------------------------
```php
$config['skin'] = 'daak-redmono';
```

Showcase & Screenshots
----------------------
Standalone repository & high-resolution gallery:
https://github.com/benfirad/daak-redmono-mail-ui

License & Credits
-----------------
- Author: Firat Dasdogen (DAAK / Redmono)
- License: GPL-3.0-or-later
- Extends Roundcube's `elastic` skin by Aleksander Machniak (CC-BY-SA-3.0).
