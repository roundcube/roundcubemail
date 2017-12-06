# Running Roundcube in a Docker Container

The simplest method is to run the official image:

```
docker run -e ROUNDCUBEMAIL_DEFAULT_HOST=mail -d roundcube/roundcubemail
```

## Configuration/Environment Variables

The following env variables can be set to configure your Roundcube Docker instance:

`ROUNDCUBEMAIL_DEFAULT_HOST` - Hostname of the IMAP server to connect to

`ROUNDCUBEMAIL_DEFAULT_PORT` - IMAP port number; defaults to `143`

`ROUNDCUBEMAIL_SMTP_SERVER` - Hostname of the SMTP server to send mails

`ROUNDCUBEMAIL_SMTP_PORT`  - SMTP port number; defaults to `587`

`ROUNDCUBEMAIL_PLUGINS` - List of built-in plugins to activate. Defaults to `archive,zipdownload`

By default, the image will use a local SQLite database for storing user account metadata.
It'll be created inside the `/var/www/html` volume and can be backed up from there. Please note that
this option should not be used for production environments.

### Connect to a MySQL Database

The recommended way to run Roundcube is connected to a MySQL database. Specify the following env variables to do so:

`ROUNDCUBEMAIL_DB_TYPE` - Database provider; currently supported: `mysql`, `pgsql`, `sqlite`

`ROUNDCUBEMAIL_DB_HOST` - Host (or Docker instance) name of the database service; defaults to `mysql` or `postgres` depending on linked containers.

`ROUNDCUBEMAIL_DB_USER` - The database username for Roundcube; defaults to `root` on `mysql`

`ROUNDCUBEMAIL_DB_PASSWORD` - The password for the database connection

`ROUNDCUBEMAIL_DB_NAME` - The database name for Roundcube to use; defaults to `roundcubemail`

Before starting the container, please make sure that the supplied database exists and the given database user
has privileges to create tables.

Run it with a link to the MySQL host and the username/password variables:

```
docker run --link=mysql:mysql -d roundcube/roundcubemail
```

### Advanced configuration

Apart from the above described environment variables, the Docker image also allows to add custom config files
which are merged into Roundcube's default config. Therefore the image defines a volume `/var/roundcube/config`
where additional config files (`*.php`) are searched and included. Mount a local directory with your config
files - check for valid PHP syntax - when starting the Docker container:

```
docker run -v ./config/:/var/roundcube/config/ -d roundcube/roundcubemail
```

Check our wiki for a reference of [Roundcube config options](https://github.com/roundcube/roundcubemail/wiki/Configuration).

## Building a Docker image

Use the `Dockerfile` in this directory to build your own Docker image.
It pulls the latest build of Roundcube Webmail from the Github download page and builds it on top of a `php:7.1-apache` Docker image.

Build it from this directory with

```
docker build -t roundcubemail .
```

