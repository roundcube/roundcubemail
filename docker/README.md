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

`MYSQL_ENV_MYSQL_HOST` - Host (or Docker instance) name of the MySQL service; defaults to `mysql`

`MYSQL_ENV_MYSQL_USER` - The database username for Roundcube; defaults to `root`

`MYSQL_ENV_MYSQL_PASSWORD` - The password for the database connection or
`MYSQL_ENV_MYSQL_ROOT_PASSWORD` - if the database username is `root`

`MYSQL_ENV_MYSQL_DATABASE` - The database name for Roundcube to use; defaults to `roundcubemail`

Before starting the container, please make sure that the supplied database exists and the given database user
has privileges to create tables.

Run it with a link to the MySQL host and the username/password variables:

```
docker run -e MYSQL_ENV_MYSQL_ROOT_PASSWORD=my-secret-password --link=mysql:mysql -d roundcube/roundcubemail
```

## Building a Docker image

Use the `Dockerfile` in this directory to build your own Docker image.
It pulls the latest build of Roundcube Webmail from the Github download page and builds it on top of a `php:7.1-apache` Docker image.

Build it from this directory with

```
docker build -t roundcubemail .
```

