# authres_status plugin for roundcube

This plugin checks the Authentication-Results headers that were added by your MTA and displays an icon to show the verification status. Parsing of the Authentication-Results headers is more or less done according to [RFC5451](https://tools.ietf.org/html/rfc5451) which supports DKIM, DomainKeys, SPF, Sender-ID, iprev and SMTP AUTH result values.

This plugin is partially based on [dkimstatus](https://github.com/jvehent/dkimstatus) by jvehent, which was based on a plugin by [Vladimir Mach](http://www.wladik.net).

Icons by [brankic1979](http://brankic1979.com/icons);

## Install
If not using composer, copy all files to your plugins/ folder and add 'authres_status' to your $config['plugins'] array in config/main.inc.php or config/config.inc.php.

## Configuration
If you want to enable the results column in your message list, enable this in your settings. You can also choose which statuses you would like to see/ignore.

As of version 0.2 you can also enable an internal DKIM verifier ([php-dkim](https://github.com/pimlie/php-dkim) by angrychimp) if your MTA did not add a Authentication-Results header. You could experience some slow down because we need to retrieve the whole message body of each message for which we run the verifier.

### Trusted mta's (since v0.3)
An email can be passed through many mta's before it finally ends up in your mailbox. Each mta can add additional headers to the email, thus also Authentication-Result headers. This makes it possible for a malicious mta to add a Authentication-Result header that has a passing result, eventhough the signature is invalid (or not existing). Section [2.2](https://tools.ietf.org/html/rfc5451#section-2.2) of RFC5451 states that every Authentication-Result headers should start with an authserv-id which has a similar syntax as a fully-qualified domain name. Often the authserv-id is equal to the fqdn of the mta.

Since version 0.3 you can add a comma separated list of authserv-id's that you trust, then only results from those mta's will be displayed. If you are not sure what the authserv-id from your mta is, toggle the 'raw message headers' display in the preview pane and look for a **Authentication-Results** header. It should look like:
```
Authentication-Results: example.com;
                  sender-id=hardfail header.from=example.com;
                  dkim=pass (good signature) header.i=sender@example.com
```

The text between `Authentication-Results:` and the first `;` is the authserv-id, in the example above it is `example.com`.

## Tested
Tested on Roundcube 1.0.0+, let me know if it works on previous version as well

## Known issues
- After changing layouts (e.g. from list to widescreen) you need to refresh the page to correctly show the authentication status column
