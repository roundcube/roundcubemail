INTRODUCTION
============

This file describes the basic steps to install Roundcube Webmail on your
web server. For additional information, please consult the [wiki][githubwiki].


REQUIREMENTS
------------

* An IMAP, HTTP and SMTP server
* .htaccess support allowing overrides for DirectoryIndex
* PHP Version 8.1 or greater including:
   - PCRE, DOM, JSON, Session, Sockets, OpenSSL, Mbstring, Filter, Ctype, Intl (required)
   - PHP PDO with driver for either MySQL, PostgreSQL or SQLite (required)
   - Iconv, Zip, Fileinfo, Exif (recommended)
   - LDAP for LDAP addressbook support (optional)
   - GD, Imagick, XMLWriter (optional: thumbnails generation, QR-code)
* PEAR and PEAR packages distributed with Roundcube or external.
  See composer.json for the list of required packages.
* php.ini options:
   - memory_limit > 16MB
   - file_uploads enabled (for uploading attachments and import files)
   - session.auto_start disabled
   - suhosin.session.encrypt disabled
   - mbstring.func_overload disabled
   - pcre.backtrack_limit >= 100000
* A MySQL, PostgreSQL, or SQLite v3 support in PHP - with permission to create tables
* Composer installed either locally or globally ([getcomposer.org][getcomposer])


INSTALLATION
------------

1. Decompress and put this folder somewhere inside your server's filesystem.
  Note: Make sure files have proper owner/group for your setup. If you use
  tar command `--no-same-owner` option might be helpful.
2. In case you don't use the so-called "complete" release package,
  you have to install PHP and javascript dependencies.
   - Install PHP dependencies using composer:
      - get composer from [getcomposer.org][getcomposer]
      - if you want to use LDAP address books, enable the LDAP libraries in your
      composer.json file by moving the items from "suggest" to the "require"
      section (remove the explanation texts after the version!).
      - run `php composer.phar update --no-dev`
   - Install Javascript dependencies by executing `./bin/install-jsdeps.sh` script.
   - Install some developer tools by executing `npm install`.
   - If you use git sources, compile css files for the Elastic skin as described
    in the [skins/elastic/README.md](../skins/elastic/README.md) file.
3. Make sure that the following directories (and the files within)
   are writable by the webserver
   - `/temp`
   - `/logs`
4. Create a new database and a database user for Roundcube (see [DATABASE SETUP](#dbsetup))
5. Configure your HTTP server and point it to Roundcube's `public_html` directory.
   This is the document root.
6. Point your browser to `http://url-to-roundcube/installer.php`.
7. Follow the instructions of the install script (or see [MANUAL CONFIGURATION](#manualconf)).
8. Check [KNOWN ISSUES](#knownissues) section of this file


CONFIGURATION HINTS
-------------------

_***IMPORTANT!*** Read all comments in `defaults.inc.php`, understand them
and configure your installation to be not surprised by default behaviour._

Roundcube writes internal errors to the `errors.log` log file located in the logs
directory which can be configured in `config/config.inc.php`. If you want ordinary
PHP errors to be logged there as well, set error_log in php.ini or .htaccess file.
Examine the log_driver config value for other options.

Roundcube forces `display_errors=Off` and `log_errors=On.`

By default the session cookie settings of PHP are not modified by Roundcube.
However if you want to limit the session cookies to the directory where
Roundcube resides you can set session.cookie_path in the php.ini or .htaccess file.

[More about PHP settings][phpconfig].


<a name="dbsetup"></a>
DATABASE SETUP
--------------

_Note: Database for Roundcube must use UTF-8 character set._

_Note: See defaults.inc.php file for examples of DSN configuration._

### MySQL
Setting up the mysql database can be done by creating an empty database,
importing the table layout and granting the proper permissions to the
roundcube user. Here is an example of that procedure:

    > CREATE DATABASE roundcubemail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    > CREATE USER roundcube@localhost IDENTIFIED BY 'password';
    > GRANT ALL PRIVILEGES ON roundcubemail.* TO roundcube@localhost;
    > quit

_Note 1: 'password' is the master password for the roundcube user. It is strongly
recommended you replace this with a more secure password. Please keep in
mind that you must specify this password later in `config/config.inc.php`._

_Note 2: When using MySQL < 5.7.7 or MariaDB < 10.2.2 it is required to configure
the database engine with:_
```
innodb_large_prefix=1
innodb_file_per_table=1
innodb_file_format=Barracuda
```

Now you can run the Installer or configure the database access options in
`config/config.inc.php` and run: `./bin/initdb.sh --dir=SQL`.


### SQLite
Versions of sqlite database engine older than 3.6.19 aren't supported.
Database file and structure is created automatically by Roundcube.
Make sure your configuration points to some file location and that the
webserver can write to the file and the directory containing the file.


### PostgreSQL
To use Roundcube with PostgreSQL support you have to follow these
simple steps, which have to be done as the postgres system user (or
which ever is the database superuser):

    $ createuser -P roundcube
    $ createdb -O roundcube -E UNICODE roundcubemail

Note: in some system configurations you might need to add `-U postgres` to
createuser and createdb commands.

Now you can run the Installer or configure the database access options in
`config/config.inc.php` and run: `./bin/initdb.sh --dir=SQL`.


### Database cleaning
To keep your database slick and clean we recommend to periodically execute
`./bin/cleandb.sh` which finally removes all records that are marked as deleted.
Best solution is to install a cronjob running this script daily.

<a name="manualconf"></a>
MANUAL CONFIGURATION
--------------------

First of all, copy the sample configuration file `config/config.inc.php.sample`
to `config/config.inc.php` and make the necessary adjustments according to your
environment and your needs. More configuration options can be copied from the
`config/defaults.inc.php` file into your local `config.inc.php` file as needed.
Read the comments above the individual configuration options to find out what
they do or read [the installation wiki][install] for even more guidance.

The maximum size of email attachments and other file uploads is controlled by
PHP settings: upload_max_filesize and post_max_size. [Read more about PHP
settings][phpconfig].


CONTENT-SECURITY-POLICY
-----------------------

If you use a Content-Security-Policy, please note that Roundcube *requires* the
`script-src` parameter to include `'unsafe-inline' 'unsafe-eval'`. No external
sources are required (by default).


UPGRADING
---------

If you already have a previous version of Roundcube installed,
please refer to the instructions in [UPGRADING.md](UPGRADING.md) guide.


OPTIMISING
----------

Roundcube can be further optimized by using HTTP compression and caching.
HTTP server setup is out of scope for this manual. _(TODO: wiki page)_.

<a name="knownissues"></a>
KNOWN ISSUES
------------

Installations with uw-imap server should set `imap_disabled_caps = array('ESEARCH')`
in main configuration file. ESEARCH implementation in this server is broken (#1489184).

PHP validates the ssl certificates by default. It means that
if IMAP/SMTP certificates are self-signed or use wrong host name you'll get
connection errors. A solution in such cases is to set `imap_conn_options`,
`smtp_conn_options` and `managesieve_conn_options` in a way described in `config/defaults.inc.php`.

If you have problems with temp files or non-working logs make sure `/temp` and `/logs` folders
are writeable to the user used by http server. Access to them may also be blocked by
SELINUX. Here's some sample commands for SELINUX:

    $ semanage fcontext -a -t httpd_sys_rw_content_t "/path_to_roundcube/logs(/.*)?"
    $ semanage fcontext -a -t httpd_sys_rw_content_t "/path_to_roundcube/temp(/.*)?"
    $ restorecon -Rv /path_to_roundcube/

[githubwiki]:  https://github.com/roundcube/roundcubemail/wiki
[getcomposer]: https://getcomposer.org/
[phpconfig]:   https://github.com/roundcube/roundcubemail/wiki/Installation#php-configuration
[install]:     https://github.com/roundcube/roundcubemail/wiki/Installation
