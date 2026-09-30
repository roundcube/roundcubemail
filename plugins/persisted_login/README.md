Persisted login Roundcubemail plugin
====================================

This plugin adds a toggle switch into the login form of Roundcubemail's "elastic" skin, that makes the session live for a configured number of days (instead of only for the session).

In effect, logins stay valid across network changes of clients, etc.

From a technical point of view this plugin (if enabled) overrides `$config['session_lifetime']` (which sets the session garbage collection max lifetime in PHP) to match the number of days set in its own config.

Usage
-----

Enable the plugin in your Roundcubemail's config:

```php
$config['plugins'] = [ …, 'persisted_login'];
```

By default logins are persisted for 7 days. That value can be changed via the config option `persisted_login_days` in the
config file of this plugin. (Make sure that the config file ends in `.php` to have it used by Roundcubemail.)


Credits
-------

Most of this code was actually written by [Github-Citizen](https://github.com/Github-Citizen) for https://github.com/roundcube/roundcubemail/pull/8689, which fell through due to styling issues, and only cleaned up and renamed for this plugin.
