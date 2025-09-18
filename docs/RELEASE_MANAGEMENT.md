# Release management

Roundcubemail releases follow the rules of [semantic versioning](https://semver.org). As public API in the sense of that document we consider [the plugin API](https://github.com/roundcube/roundcubemail/wiki/Plugin-API) and the upgrade process.

We fully maintain and support only the latest released version of Roundcubemail.

Only security and similarly critical fixes will be published for the previous minor version, too.

(As an example: Imagine the latest release to be version 1.2.3, and the latest release before 1.2.0 to have been 1.1.5. Then, a security fix would be released in a version 1.2.4, as well as a version 1.1.6. A new feature or uncritical bug fix would only be released in a version 1.2.4.)

For new minor or major version, we might release beta versions in advance, but we don't promise those.

There is no guarantee that a change in the "master" branch will be included in the next release.

Exceptions of these rules may happen, but only for good reasons.
