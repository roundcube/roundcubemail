In-Browser Tests
================

The idea of these testing suite is to make it as simple as possible to execute
the tests. So, you don't have to run any additional services, nor download
and install anything manually.

The tests are using [Laravel Dusk][laravel-dusk] and Chrome WebDriver.
PHP server is used to serve Roundcube instance on tests run.


INSTALLATION
------------

Installation:

0. Note that the suite requires PHP >= 8.0 and PHPUnit >= 7.5.
1. Install Laravel Dusk with all dependencies.
```
composer require "laravel/dusk:^7.9"
```
2. Install Chrome WebDriver for the version of Chrome/Chromium in your system. Yes,
   you have to have Chrome/Chromium installed.
```
php tests/Browser/install.php [version]
```
3. Configure the test account and Roundcube instance.

Create a config file named `config-test.inc.php` in the Roundcube config dir.
That file should provide specific `db_dsnw` and
`imap_host` values for testing purposes as well as the credentials of a
valid IMAP user account used for running the tests with.

Add these config options used by the Browser tests:

```php
  // Unit tests settings
  $config['tests_username'] = 'roundcube.test@example.org';
  $config['tests_password'] = '<test-account-password>';
```

WARNING
-------
Please note that the configured IMAP account as well as the Roundcube database
configred in `db_dsnw` will be wiped and filled with test data in every test
run. Under no circumstances you should use credentials of a production database
or email account!

Please, keep the file as simple as possible, i.e. containing only database
and imap/smtp settings needed for the test user authentication. We would
want to test default configuration. Especially only Elastic skin is supported.

NOTE: Do not use devel_mode=true (i.e. you should build Elastic styles),
it makes chrome-driver to behave weird, timing out when using iframes (which we do a lot).

NOTE: See `.ci` directory for sample config and scripts we use for in-browser
tests on Travis.


EXECUTING THE TESTS
-------------------

To run the test suite call `phpunit` from the tests/Browser directory:

```
  cd <roundcube-dir>/tests/Browser
  TESTS_MODE=desktop phpunit
  TESTS_MODE=phone phpunit
  TESTS_MODE=tablet phpunit
```

[laravel-dusk]: https://github.com/laravel/dusk
