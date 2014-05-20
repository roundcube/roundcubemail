Running Selenium Tests
======================

In order to run the Selenium-based web tests, some configuration for the 
Roundcube test instance need to be created. Along with the default config for a 
given Roundcube instance, you should provide a config specifically for running 
tests. To do so, create a config file named `config-test.inc.php` in the 
regular Roundcube config dir. That should provide specific `db_dsnw` and 
`default_host` values for testing purposes as well as the credentials of a 
valid IMAP user account used for running the tests with.

Add these config options used by the Selenium tests:

```php
  // Unit tests settings
  $config['tests_username'] = 'roundcube.test@example.org';
  $config['tests_password'] = '<test-account-password>';
  $config['tests_url'] = 'http://localhost/roundcube/index-test.php';
```

The `tests_url` should point to Roundcube's index-test.php file accessible by 
the Selenium web browser.

WARNING
-------
Please note that the configured IMAP account as well as the Roundcube database 
configred in `db_dsnw` will be wiped and filled with test data in every test 
run. Under no circumstances you should use credentials of a production database 
or email account!


Run the tests
-------------

First you need to start a Selenium server. We recommend to use the
[Selenium Standalone Server][selenium-server] but the tests will also run on a 
Selenium  Grid. The tests are based in [PHPUnit_Selenium][phpunit] which can be 
installed through [PEAR][pear-phpunit].

To start the test suite call `phpunit` from the Selenium directory:

```
  cd <roundcube-dir>/tests/Selenium
  phpunit
```

[phpunit]: http://phpunit.de/manual/4.0/en/selenium.html
[pear-phpunit]: http://pear.phpunit.de/
[selenium-server]: http://docs.seleniumhq.org/download/
