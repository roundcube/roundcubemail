# Changelog Roundcube Webmail

## Unreleased

- Managesieve: Protect special scripts in managesieve_kolab_master mode
- Fix newmail_notifier notification focus in Chrome (#9467)
- Fix fatal error when parsing some TNEF attachments (#9462)
- Fix double scrollbar when composing a mail with many plain text lines (#7760)
- Fix decoding mail parts with multiple base64-encoded text blocks (#9290)
- Fix bug where some messages could get malformed in an import from a MBOX file (#9510)
- Fix invalid line break characters in multi-line text in Sieve scripts (#9543)
- Fix bug where "with attachment" filter could fail on some fts engines (#9514)
- Fix bug where an unhandled exception was caused by an invalid image attachment (#9475)
- Fix bug where a long subject title could not be displayed in some cases (#9416)
- Fix infinite loop when parsing malformed Sieve script (#9562)
- Fix bug where imap_conn_option's 'socket' was ignored (#9566)
- Fix XSS vulnerability in post-processing of sanitized HTML content [CVE-2024-42009]
- Fix XSS vulnerability in serving of attachments other than HTML or SVG [CVE-2024-42008]
- Fix information leak (access to remote content) via insufficient CSS filtering [CVE-2024-42010]

## Release 1.6.7

- Makefile: Use phpDocumentor v3.4 for the Framework docs (#9313)
- Fix bug where HTML entities in URLs were not decoded on HTML to plain text conversion (#9312)
- Fix bug in collapsing/expanding folders with some special characters in names (#9324)
- Fix PHP8 warnings (#9363, #9365, #9429)
- Fix missing field labels in CSV import, for some locales (#9393)
- Fix command injection via crafted im_convert_path/im_identify_path on Windows
- Fix cross-site scripting (XSS) vulnerability in handling list columns from user preferences
- Fix cross-site scripting (XSS) vulnerability in handling SVG animate attributes

## Release 1.6.6

- Fix regression in handling LDAP search_fields configuration parameter (#9210)
- Enigma: Fix finding of a private key when decrypting a message using GnuPG v2.3
- Fix page jump menu flickering on click (#9196)
- Update to TinyMCE 5.10.9 security release (#9228)
- Fix PHP8 warnings (#9235, #9238, #9242, #9306)
- Fix saving other encryption settings besides enigma's (#9240)
- Fix unneeded php command use in installto.sh and deluser.sh scripts (#9237)
- Fix TinyMCE localization installation (#9266)
- Fix bug where trailing non-ascii characters in email addresses could have been removed in recipient input (#9257)
- Fix IMAP GETMETADATA command with options - RFC5464

## Release 1.6.5

- Fix PHP8 fatal error when parsing a malformed BODYSTRUCTURE (#9171)
- Fix duplicated Inbox folder on IMAP servers that do not use Inbox folder with all capital letters (#9166)
- Fix PHP warnings (#9174)
- Fix UI issue when dealing with an invalid managesieve_default_headers value (#9175)
- Fix bug where images attached to application/smil messages weren't displayed (#8870)
- Fix PHP string replacement error in utils/error.php (#9185)
- Fix regression where `smtp_user` did not allow pre/post strings before/after `%u` placeholder (#9162)
- Fix cross-site scripting (XSS) vulnerability in setting Content-Type/Content-Disposition for attachment preview/download

## Release 1.6.4

- Fix PHP8 warnings (#9142, #9160)
- Fix default 'mime.types' path on Windows (#9113)
- Managesieve: Fix javascript error when relational or spamtest extension is not enabled (#9139)
- Fix cross-site scripting (XSS) vulnerability in handling of SVG in HTML messages [CVE-2023-5631] (#9168)

## Release 1.6.3

- Fix bug where installto.sh/update.sh scripts were removing some essential options from the config file (#9051)
- Update jQuery-UI to version 1.13.2 (#9041)
- Fix regression that broke use_secure_urls feature (#9052)
- Fix potential PHP fatal error when opening a message with message/rfc822 part (#8953)
- Fix bug where a duplicate `<title>` tag in HTML email could cause some parts being cut off (#9029)
- Fix bug where a list of folders could have been sorted incorrectly (#9057)
- Fix regression where LDAP addressbook 'filter' option was ignored (#9061)
- Fix wrong order of a multi-folder search result when sorting by size (#9065)
- Fix so install/update scripts do not require PEAR (#9037)
- Fix regression where some mail parts could have been decoded incorrectly, or not at all (#9096)
- Fix handling of an error case in Cyrus IMAP BINARY FETCH, fallback to non-binary FETCH (#9097)
- Fix PHP8 deprecation warning in the reconnect plugin (#9083)
- Fix "Show source" on mobile with x_frame_options = deny (#9084)
- Fix various PHP warnings (#9098)
- Fix deprecated use of ldap_connect() in password's ldap_simple driver (#9060)
- Fix cross-site scripting (XSS) vulnerability in handling of linkrefs in plain text messages [CVE-2023-43770]

## Release 1.6.2

- Add Uyghur localization
- Fix regression in OAuth request URI caused by use of REQUEST_URI instead of SCRIPT_NAME as a default (#8878)
- Fix bug where false attachment reminder was displayed on HTML mail with inline images (#8885)
- Fix bug where a non-ASCII character in app.js could cause error in javascript engine (#8894)
- Fix JWT decoding with url safe base64 schema (#8890)
- Fix bug where .wav instead of .mp3 file was used for the new mail notification in Firefox (#8895)
- Fix PHP8 warning (#8891)
- Fix support for Windows-31J charset (#8869)
- Fix so LDAP VLV option is disabled by default as documented (#8833)
- Fix so an email address with name is supported as input to the managesieve notify :from parameter (#8918)
- Fix Help plugin menu (#8898)
- Fix invalid onclick handler on the logo image when using non-array skin_logo setting (#8933)
- Fix duplicate recipients in "To" and "Cc" on reply (#8912)
- Fix bug where it wasn't possible to scroll lists by clicking middle mouse button (#8942)
- Fix bug where label text in a single-input dialog could be partially invisible in some locales (#8905)
- Fix bug where LDAP (fulltext) search didn't work without 'search_fields' in config (#8874)
- Fix extra leading newlines in plain text converted from HTML (#8973)
- Fix so recipients with a domain ending with .s are allowed (#8854)
- Fix so vCard output does not contain non-standard/redundant TYPE=OTHER and TYPE=INTERNET (#8838)
- Fix QR code images for contacts with non-ASCII characters (#9001)
- Fix PHP8 warnings when using list_flags and list_cols properties by plugins (#8998)
- Fix bug where subfolders could loose subscription on parent folder rename (#8892)
- Fix connecting to LDAP using an URI with ldapi:// scheme (#8990)
- Fix insecure shell command params handling in cmd_learn driver of markasjunk plugin (#9005)
- Fix bug where some mail headers didn't work in cmd_learn driver of markasjunk plugin (#9005)
- Fix PHP fatal error when importing vcf file using PHP 8.2 (#9025)
- Fix so output of log_date_format with microseconds contains time in server time zone, not UTC

## Release 1.6.1

- Kill session if refreshing oauth token fails (#8734)
- Fix various PHP 8.1 warnings (#8628, #8644, #8667, #8656, #8647)
- Password: Remove references to %c variable that has been removed before (#8633)
- Fix anchor links in HTML mail (#8632)
- Fix bug where config creation in Installer did ignore options in the form (#8634)
- Fix bug where renamed options were removed from the config on installto.sh (update.sh) run (#8643)
- Fix favicon rewrite rule in .htaccess (#8654)
- Fix various PHP 8.2 warnings
- Fix bug where it wasn't possible to create more than one response record on SQLite and Postgres (#8664)
- Fix support for ManageSieve over implicit SSL (#8670)
- Fix bug where "about:blank" page could trigger "load error" (#8554)
- Fix bug where setting 'Clear Trash on Logout' to 'all messages' didn't work (#8687)
- Fix bug where the attachment menu wouldn't disappear after an action is selected (#8691)
- Fix bug where some dialogs in an eml attachment preview would not close on mobile (#8627)
- Fix bug where multiline data:image URI's in emails were stripped from the message on display (#8613)
- Fix fatal error on identity page if Enigma plugin is misconfigured (#8719)
- Fix so N property always exists in a vCard export (#8771)
- Fix authenticating to Courier IMAP with passwords containing a '~' character (#8772)
- Fix handling of smtp/imap port options on configuration file update (#8756)
- Fix bug where array values could not be saved in utils/save_pref action (#8781)
- Add workaround for using Roundcube behind a reverse proxy with a subpath: 'request_path' option (#8738, #8770)
- Fix bug where "Invalid skin name" error was logged on preferences save if there's only one skin (#8825)
- Fix SIGBUS raised in ImageMagick when more than one process tried to generate a thumbnail of the same image attachment (#8511)
- Fix bug where updater does not update the vendor packages (#8642)
- Fix missing mail composing textarea on reply/draft with a long plain text content (#8866)

## Release 1.6.0

- Fix SMTP XCLIENT extension when not using STARTTLS (#8581)
- Fix call to undefined method rcube_ldap_generic::option_set() (#8564)
- Fix PHP Fatal error on incompatible method declaration of rcmail_output_json::command() and rcmail_output::command() (#8579)
- Fix support for DSN specification without host e.g. `pgsql:///dbname` (#8558)
- Fix TinyMCE configuration for handling styles of pasted content in webkit browsers (#8555)
- Fix bug where some checkboxes could be selected unintentinally (#8565)
- Fix css styles of the email recipient element while dragging (#8580)
- Fix PHP 8.1 warnings in the LDAP backend code (#8572)
- Fix various PHP 8.1 warnings (#8584)
- Fix bug where a recipient address containing UTF-8 characters was ignored when sending an email (#8493, #8546)
- Fix so rcmail::contact_exists() works with IDNA addresses (#8545)
- Fix password option in `storage_init` hook after refreshing oauth access token (#8436)
- Fix attachment Options popover menu after attachment delete (#8602)
- Fix so "Found unconstructed Spoofchecker" error is not fatal (#8537)

## Release 1.6-rc

- Update to jQuery-UI 1.13.1 (#8455)
- Added possibility to make the logo image a link via the 'skin_logo' option (#8501)
- Use navigator.pdfViewerEnabled for PDF viewer detection
- Remove use of unreliable charset detection (#8344)
- Don't list images attached to multipart/related part as attachments (#7184)
- Password: Add support for ssha256 algorithm (#8459)
- Fix so unix:// URI is supported in various host spec. options again (#8468)
- Fix slow loading of long HTML content into the HTML editor (#8108)
- Fix bug where SMTP password didn't work if it contained '%p' (#8435)
- Enigma: Fix initial synchronization of private keys
- Enigma: Fix double quoted-printable encoding of pgp-signed messages with no attachments (#8413)
- Fix handling of message/rfc822 parts that are small and are multipart structures with a single part (#8458)
- Fix bug where session could time out if DB and PHP timezone were different (#8303)
- Fix bug where DSN flag state wasn't stored with a draft (#8371)
- Fix broken encoding of HTML content encapsulated in a RTF attachment (#8444)
- Fix problem with aria-hidden=true on toolbar menus in the Elastic skin (#8517)
- Fix so links (e.g. www.some.page or http://some.page) are not considered mispellings (#8527)
- Fix bug where title tag content was displayed in the body if it contained HTML tags (#8540)

## Release 1.6-beta

- Unified and simplified services connection options (#8310):
    1. IMAP:
        - renamed `default_host` to `imap_host`
        - removed `default_port` option (non-standard port can be set via `imap_host`)
        - set "localhost:143" as a default for `imap_host`
    2. SMTP:
        - renamed `smtp_server` to `smtp_host`
        - removed `smtp_port` option (non-standard port can be set via `smtp_host`)
        - set "localhost:587" as a default for `smtp_host`
    3. LDAP:
        - removed `port` option from `ldap_public` array (non-standard port can be set via `host`)
        - removed `use_tls` option from `ldap_public` array (use tls:// prefix in `host`)
    4. Managesieve:
        - removed `managesieve_port` option (non-standard port can be set via `managesieve_host`)
        - removed `managesieve_usetls` option (tls:// prefix in `managesieve_host` have to be used)
- Plugin API: Removed `smtp_port` parameter in `smtp_connect` hook
- Plugin API: Renamed `smtp_server` parameter to `smtp_host` in `smtp_connect` hook
- Plugin API: Removed `port` parameter in `managesieve_connect` hook
- Plugin API: Removed `usetls` parameter in `managesieve_connect` hook
- Added support for PHP 8.1 (#8151)
- Dropped support for PHP < 7.3 (#7976)
- Dropped support for strftime-like format (with % sign) in date and time format configuration
- Moved the Classic and Larry skins to their own repository (#8271)
- SQLite: Use foreign keys, require SQLite >= 3.6.19
- Replace Endroid QrCode with BaconQrCode (#8173)
- Support responses (snippets) in HTML format (#5315)
- Purge also subfolders of Trash (and/or messages in them) on logout (#1037)
- Add support for encryption with AEAD ciphers, e.g. aes-256-gcm (#7097)
- Add option to purge deleted mails older than 30, 60 or 90 days (#5493)
- Add ability to mark multiple messages as not deleted at once (#5133)
- Add possibility to disable line-wrapping of sent mail body (#5101)
- Improve/Fix wrapping of plain text messages on preview and reply (#6974, #8391, #8378, #8289)
- Improve searching by sender/recipient headers, support Reply-To and Followup-To (#6582)
- Add option to control links handling behavior on html to text conversion (#6485)
- Add 'loginform_content' plugin hook (#8273, #6569)
- SMTP: If requested use TLS also without authentication (#4590, #8111)
- Display a generic error page on initial DB/configuration errors (#8222)
- Display telephone numbers as tel: links (#8240)
- Elastic: Move scrollbar settings to variables (#8352)
- Elastic: Use thin scrollbars in both light and dark mode
- Elastic: Make the scrollbar color lighter in dark mode (#8345)
- Autologout: A new plugin to auto log out users with a POST request (#8270)
- Enigma: Upgrade to OpenPGP.js v5.0
- Identicon: Make background color of the image to match the current skin colors (#8256)
- Newmail_notifier: Update favicon to match the current favicon style and size (#7826)
- Password: Remove password_blowfish_cost option, in favor of password_algorithm_options
- Password: Remove support for password_algorithms crypt, hash and cram-md5
- Password: Remove support for %c, %d, %n, %q variables in password_query
- Password: Add support for passwords based on PHP's password_hash() function (#7724)
- Password: Verify current password with IMAP (#8142)
- Password: Improve handling errors on executed commands (#8200)
- Password: Add Mailcow driver (#8291)
- Fix compatibility with Referrer-Policy: "strict-origin" (#8170)
- Fix locked SQLite database for the CLI tools (#8035)
- Fix Makefile on Linux (#8211)
- Fix so PHP warnings are ignored when resizing a malformed image attachment (#8387)
- Fix various PHP8 warnings (#8392, #9193)
- Fix mail headers injection via the subject field on mail compose (#8404)
- Fix bug where small message/rfc822 parts could not be decoded (#8408)
- Fix setting HTML mode on reply/forward of a signed message (#8405)
- Fix handling of RFC2231-encoded attachment names inside of a message/rfc822 part (#8418)
- Fix bug where some mail parts (images) could have not be listed as attachments (#8425)
- Fix bug where attachment icons were stuck at the top of the messages list in Safari (#8433)

## Release 1.5.2

- OAuth: pass 'id_token' to 'oauth_login' plugin hook (#8214)
- OAuth: fix expiration of short-lived oauth tokens (#8147)
- OAuth: fix relative path to assets if /index.php/foo/bar url is used (#8144)
- OAuth: no auto-redirect on imap login failures (#8370)
- OAuth: refresh access token in 'refresh' plugin hook (#8224)
- Fix so folder search parameters are honored by subscriptions_option plugin (#8312)
- Fix password change with Directadmin driver (#8322, #8329)
- Fix so css files in plugins/jqueryui/themes will be minified too (#8337)
- Fix handling of unicode/special characters in custom From input (#8357)
- Fix some PHP8 compatibility issues (#8363)
- Fix chpass-wrapper.py helper compatibility with Python 3 (#8324)
- Fix scrolling and missing Close button in the Select image dialog in Elastic/mobile (#8367)
- Security: Fix cross-site scripting (XSS) via HTML messages with malicious CSS content

## Release 1.5.1

- Fix importing contacts with no email address (#8227)
- Fix so session's search scope is not used if search is not active (#8199)
- Fix some PHP8 warnings (#8239)
- Fix so dark mode state is retained after closing the browser (#8237)
- Fix bug where new messages were not added to the list on refresh if skip_deleted=true (#8234)
- Fix colors on "Show source" page in dark mode (#8246)
- Fix handling of dark_mode_support:false setting in skins meta.json - also when devel_mode=false (#8249)
- Fix database initialization if db_prefix is a schema prefix (#8221)
- Fix undefined constant error in Installer on Windows (#8258)
- Fix installation/upgrade on MySQL 5.5 - Index column size too large (#8231)
- Fix regression in setting of contact listing name (#8260)
- Fix bug in Larry skin where headers toggle state was reset on full page preview (#8203)
- Fix bug where \u200b characters were added into the recipient input preventing mail delivery (#8269)
- Fix charset conversion errors on PHP < 8 for charsets not supported by mbstring (#8252)
- Fix bug where adding a contact to trusted senders via "Always allow from..." button didn't work (#8264, #8268)
- Fix bug with show_images setting where option 1 and 3 were swapped (#8268)
- Fix PHP fatal error on an undefined constant in contacts import action (#8277)
- Fix fetching headers of multiple message parts at once in rcube_imap_generic::fetchMIMEHeaders() (#8282)
- Fix bug where attachment download could sometimes fail with a CSRF check error (#8283)
- Fix an infinite loop when parsing environment variables with float/integer values (#8293)
- Fix so 'small-dark' logo has more priority than the 'small' logo (#8298)

## Release 1.5.0

- Support displaying RTF content (including encapsulated HTML) from a TNEF attachment
- Newmail_notifier: Improved the notification sound (#8155)
- Disable the default spellchecker option using spell.roundcube.net (#8182)
- Fix size of Mailvelope iframe for PGP-inlined mail, again (#8126)
- Fix handling of group names with @ character in autocomplete and contacts widget (#8098)
- Fix Firefox infinate loading display on mail screen (#8128)
- Fix converting >1MB of HTML content into plain text (#8137)
- Fix bug where expanding a group in the recipient input could corrupt the input content (#7569)
- Fix fatal error/warning on invalid input to user parameter (#8152)
- Fix changing password with dovecot_passwdfile driver (#8145)
- Fix handling of headers that occur multiple times by show_additional_headers plugin (#8157)
- Fix bug where vertical scrollbar in new HTML message bounced back on scroll (#8046)
- Fix displaying inline images with incorrectly declared content-type (#8158)
- Fix so addr-spec with missing closing angle bracket can be parsed (#8164)
- Fix handling of spellcheck connection errors (#8172)
- Fix a couple of PHP8 warnings (#8175, #8176)
- Fix bug where "from my contacts" and "from trusted senders" values were mixed up (#8177)
- Fix password/token length check on OAuth login (#8178)
- Fix XSS issue in handling attachment filename extension in mimetype mismatch warning (#8193)
- Fix SQL injection via some session variables
- Fix handling of dark_mode_support:false setting in skins meta.json (#8186)
- Fix security issues regarding server name and trusted_host_patterns setting

## Release 1.5-rc

- Upgrade to TinyMCE 5.8.2
- SMTP XCLIENT support (#7893, #6411)
- Add IDN homograph attack (spoofing) detection [CVE-2019-15237] (#6891)
- Add configuration options for subject prefixes (#7929, #4981)
- Support IMAP LITERAL- extension [RFC 7888] (#6878)
- Warn the user about a potential data leak on mail bounce or forward (#7993)
- Make the Empty action available for every non-empty folder, not only Trash (#7948)
- Remove (incorrect) use of Return-Receipt-To header (#8069)
- Submit various simple dialog forms with the Enter key (#7133)
- Add RFC2231 support to rcube_mime_decode (#7390)
- Plugin API: Allow modification of 'error' argument in 'message_send_error' hook (#7914)
- OAuth: add plugin hooks `oauth_login` and `oauth_refresh_token` for oauth events (#8028, #8040)
- Debug_logger: Fix the main plugin functionality and documentation (#8041)
- Enigma: Fix bug where signature verification could fail for non-ascii bodies (#7919)
- Enigma: Fix invalid expiration dates of PGP keys on a 32bit system (#7531)
- Enigma: Display an information that public and private keys are stored on the server (#7941)
- Enigma: Optional support for passwordless keys (#7265)
- Managesieve: Fix removing nested rules in scripts (#8011)
- Managesieve: Support XOAUTH2, requires Net_Sieve 1.4.5 (#7925)
- Managesieve: Added ability to remove 'redirect' option from UI (#7922)
- New_user_dialog: Use the 'identity_update' hook (#8023)
- Password: Fix broken 'hmail' driver (#7966)
- Password: Set password_minimum_length to 8 by default (#8003)
- Vcard_attachments: Improve handling of multiple contacts (#7027)
- Fix inserting a group from non-default source using the Insert contact(s) dialog (#8095)
- Fix invalid search fields after search scope change (#6919)
- Fix so "Always allow from..." button appears also when allow_images=3 (#7961)
- Fix Elastic's pretty select scroll position in Chrome (#7964)
- Fix bug where invalid non-unicode characters in JSON output could make the UI unresponsive (#7955)
- Fix PHP 8 fatal error when allowing images in an email (#7968)
- Fix so session expiration is more precise and do not depend on the garbage collector (#7576)
- Fix bug where imap_conn_options settings were ignored (#7912)
- Fix bug causing some HTML message content to be not centered in Elastic skin (#7911)
- Fix bug when sending an email and recipient's email address contains a trailing dot (#7899)
- Fix bug where the list page wasn't reset when changing a folder on mail view page (#7932)
- Fix so selecting the same folder to reset search resets also the page number (#7125)
- Fix login page rendering after oauth failure (#7812,#7923)
- Fix bug where assigning users to groups via menu (not drag'n'drop) could fail in Elastic theme (#7973)
- Fix HTML5 parser issue with a messy HTML code from Outlook (#7356)
- Fix handling of multiple link references with the same index in plain text message (#8021)
- Fix various actions on folders with angle brackets in name (#8037)
- Fix inconsistent fowarding actions statuses on drafts (#8039)
- Fix bug where `start` and `reversed` attributes of `ol` tag were ignored (#8059)
- Fix bug where consecutive LDAP searches could return wrong results (#8064)
- Fix bug where plus characters in attachment filename could have been ignored (#8074)
- Fix displaying HTML body with inline images encapsulated using TNEF format (winmail.dat)
- Fix handling of custom sender addresses with names (#8106)
- Fix shift + drag'n'drop menu not working in Elastic skin with Chrome browser (#8107)

## Release 1.5-beta

- Require PHP >= 5.5
- Support PHP 8.0 (#7625)
- Require php-intl
- Remove use of Net_IDNA2 package
- Require GuzzleHttp\Client
- Upgrade to TinyMCE 5.5.1
- Upgrade to jQuery 3.5.1 (#7464)
- Update build tools (#7800, #7804, #7497):
    - jsshrink.sh: Replace google-closure-compiler with UglifyJS
    - cssshrink.sh: Replace yuicompressor with csso
    - require lessc >= 2.5.2 (and add support for v4) with less-plugin-clean-css for Less files compilation
- Automatically collected recipients and trusted senders (#6904)
    - Added configurable Collected Recipients addressbook source (#4971)
    - Added configurable Trusted Senders addressbook source (#5046)
    - Added 'contact_exists' hook
    - Added separate "trusted senders" options for show_images and mdn_request preferences (#7614)
- Contact form mode: private/business (#7630)
- OAuth/XOauth support (#7425, #6933)
- Cache refactoring (#6312)
- Added special value 'email' to login_username_filter, it changes also logon input type (#7179)
- Allow array in smtp_host config (#7296)
- Support proxy for server-side HTTP requests (#7658)
- By default do not set the User-Agent header (#7731)
- Add possibility to (re-)define field mapping on contacts import from a CSV file (#7045, #6668)
- Move "On request for return receipt" from "Mailbox View" to "Displaying Messages" (#7614)
- Support RFC8438: IMAP STATUS=SIZE - for faster folder size calculation (#7269)
- MySQL: Use utf8mb4 charset and utf8mb4_unicode_ci collation (#6535, #7113)
- Allow NULL in users.preferences column in postgres and sqlite db, the same as for other engines (#7767)
- Support for language codes up to 16 chars long (e.g. es-419) in database schema (#6851)
- Relaxed domain name validation for extended TLDs support (#5588)
- Allow opening application/octet-stream attachments according to filename extension (#6821)
- Added support for INSERT OR REPLACE queries (#6771)
- Allow skins to define which layout options they support (#7235)
- Extract RFC2231 attachment name from message headers (#6729, #6783)
- Add support for SameSite cookie attribute via session_samesite option (req PHP >= 7.3.0) (#6772)
- Change folders sorting so shared/other users namespaces are listed last (#5012)
- Display a warning and do not try to open empty attachments (#7332)
- Return 204 rather than 404 on missing contact photo (#7777)
- Add 'reconnect' plugin to retry IMAP connection (#7844)
- Plugin API: Added 'message' argument to 'message_compose_body' hook
- Plugin API: Added 'preferences' parameter to 'user_create' hook (#7692)
- Elastic: Dark mode (#6709)
- Elastic: Display email size on the list of messages (#7162)
- Elastic: Replace properties sidebar with a dialog on the attachment preview page (#7635)
- Elastic: Minimize forms/colors blink on page load
- Elastic: Improve mail header "detailed mode" (#7224)
- Elastic: Moving single recipients between recipient inputs with drag-n-drop (#5069)
- Elastic: Display a special icon for other users and shared namespace roots (#5012)
- Elastic: Support space-separated email addresses in recipient input (#6529, #6457)
- Elastic: Remember list checkbox selection state (#7148)
- Elastic: Add "Open in new window" in mail compose (#7260)
- Elastic: Make custom less files optional (#7497)
- Elastic: Prevent from opening mail preview in a new window on touch devices using double tap (#7732)
- Templates: Add support for expressions in object attributes (#7237)
- Templates: Add support for nested if conditions (#6818)
- Templates: Make [space][slash] ending of condition objects optional (#6954)
- Mailvelope: Fix size of iframe for PGP-inlined mail (#7348)
- Mailvelope: Add config option to use Main Keyring (#7348, #7157)
- Mailvelope: Add config option to set the size for new keys (#7348)
- Mailvelope: Always ask before discarding email currently being composed (#7348)
- Mailvelope: Fix unnecessary warning to re-add attachments when restoring a draft (#7348)
- Archive: Added options to split archive by year or year+month and folder (#7216)
- Enigma: Support ECC key generation - when using GnuPG >= 2.1.7 (#6853)
- Managesieve: Add support for 'spamtest' extension - RFC3685 (#6950)
- Managesieve: Allow display name with email address in vacation :from field (#6760)
- Managesieve: Improve UX on custom header input (#7207)
- Managesieve: Fix bug where activation of forward/vacation rule could activate a wrong script (#7423)
- Managesieve: Fix bug where forward/vacation rule could end up being duplicated (#7349)
- new_user_identity: Fix missing password for user-specific LDAP operations (#7667)
- Password: Added 'pwned' password strength driver (#7274)
- Password: Added Mail-in-a-Box (miab) driver (#7824)
- Password: Added TinyCP driver (#7510)
- Password: Added httpapi driver to connect to generic HTTP/HTTPS APIs (#7439)
- Password: Added dovecot_passwdfile driver (#5786)
- Password: Removed old 'cpanel' driver, 'cpanel_webmail' driver renamed to 'cpanel' (#7780)
- Fix handling of address groups in email headers by ignoring their names (#7663)
- Fix so message flags are updated on refresh also for multifolder search results (#7774)
- Fix so IMAP ID command is send only after authentication (#7517)
- Fix bug where it wasn't possible to save Spanish (Latin America) locale preference (#7784)
- Fix mail search error on invalid search_mods definition (#7789)
- Fix error when dealing with message/rfc822 attachments using Gmail IMAP (#6854)
- Fix ISO-2022-JP-MS encoding issues (#7091)
- Fix so messages in threads with no root aren't displayed separately (#4999)
- Fix so anchor tags without href attribute are not modified (#7413)
- Fix invalid IMAP SEARCH command in some rare case on messages cache synchronization (#7895)
- Fix so allowing remote resources does not add an entry to browser history (#6620)

## Release 1.4.11

- Display a nice error informing about no PHP8 support
- Elastic: Fix compatibility with Less v3 and v4 (#7813)
- Fix bug with managesieve_domains in Settings > Forwarding form (#7849)
- Fix errors in MSSQL database update scripts (#7853)
- Security: Fix cross-site scripting (XSS) via HTML messages with malicious CSS content

## Release 1.4.10

- Fix extra angle brackets in In-Reply-To header derived from mailto: params (#7655)
- Fix folder list issue whan special folder is a subfolder (#7647)
- Fix Elastic's folder subscription toggle in search result (#7653)
- Fix state of subscription toggle on folders list after changing folder state from the search result (#7653)
- Security: Fix cross-site scripting (XSS) via HTML or Plain text messages with malicious content [CVE-2020-35730]

## Release 1.4.9

- Fix HTML editor in latest Chrome 85.0.4183.102, update to TinyMCE 4.9.11 (#7615)
- Add missing localization for some label/legend elements in userinfo plugin (#7478)
- Fix importing birthday dates from Gmail vCards (BDAY:YYYYMMDD)
- Fix restoring Cc/Bcc fields from local storage (#7554)
- Fix jstz.min.js installation, bump version to 1.0.7
- Fix link to closure compiler in bin/jsshrink.sh script (#7567)
- Fix incorrect PDO::lastInsertId() use in sqlsrv driver (#7564)
- Fix bug where some parts of a message could have been missing in a reply/forward body (#7568)
- Fix empty space on mail printouts in Chrome (#7604)
- Fix empty output from HTML5 parser when content contains XML tag (#7624)
- Fix scroll jump on key press in plain text mode of the HTML editor (#7622)
- Fix so autocompletion list does not hide on scroll inside it (#7592)

## Release 1.4.8

- Fix support for an error as a string in message_before_send hook (#7475)
- Elastic: Fix redundant scrollbar in plain text editor on mail reply (#7500)
- Elastic: Fix deleted and replied+forwarded icons on messages list (#7503)
- Managesieve: Fix too-small input field in Elastic when using custom headers (#7498)
- Managesieve: Allow angle brackets in out-of-office message body (#7518)
- Fix bug in conversion of email addresses to mailto links in plain text messages (#7526)
- Fix format=flowed formatting on plain text part derived from the HTML content (#7504)
- Fix incorrect rewriting of internal links in HTML content (#7512)
- Fix handling links without defined protocol (#7454)
- Fix paging of search results on IMAP servers with no SORT capability (#7462)
- Fix detecting special folders on servers with both SPECIAL-USE and LIST-STATUS (#7525)
- Security: Fix cross-site scripting (XSS) via HTML messages with malicious svg content [CVE-2020-16145]
- Security: Fix cross-site scripting (XSS) via HTML messages with malicious math content
- Security: Fix potential XSS issue in HTML editor of the identity signature input (#7507)

## Release 1.4.7

- Fix bug where subfolders of special folders could have been duplicated on folder list
- Increase maximum size of contact jobtitle and department fields to 128 characters
- Fix missing newline after the logged line when writing to stdout (#7418)
- Elastic: Fix context menu (paste) on the recipient input (#7431)
- Fix problem with forwarding inline images attached to messages with no HTML part (#7414)
- Fix problem with handling attached images with same name when using database_attachments/redundant_attachments (#7455)
- Security: Fix cross-site scripting (XSS) via HTML messages with malicious svg/namespace [CVE-2020-15562]

## Release 1.4.6

- Installer: Fix regression in SMTP test section (#7417)

## Release 1.4.5

- Fix bug in extracting required plugins from composer.json that led to spurious error in log (#7364)
- Fix so the database setup description is compatible with MySQL 8 (#7340)
- Markasjunk: Fix regression in jsevent driver (#7361)
- Fix missing flag indication on collapsed thread in Larry and Elastic (#7366)
- Fix default keyservers (use keys.openpgp.org), add note about CORS (#7373, #7367)
- Password: Fix issue with Modoboa driver (#7372)
- Mailvelope: Use sender's address to find pubkeys to check signatures (#7348)
- Mailvelope: Fix Encrypt button hidden in Elastic (#7353)
- Fix PHP warning: count(): Parameter must be an array or an object... in ID command handler (#7392)
- Fix error when user-configured skin does not exist anymore (#7271)
- Elastic: Fix aspect ratio of a contact photo in mail preview (#7339)
- Fix bug where PDF attachments marked as inline could have not been attached on mail forward (#7382)
- Security: Fix a couple of XSS issues in Installer (#7406)
- Security: Fix XSS issue in template object 'username' (#7406)
- Security: Better fix for CVE-2020-12641
- Security: Fix cross-site scripting (XSS) via malicious XML attachment

## Release 1.4.4

- Fix bug where attachments with Content-Id were attached to the message on reply (#7122)
- Fix identity selection on reply when both sender and recipient addresses are included in identities (#7211)
- Elastic: Fix text selection with Shift+PageUp and Shift+PageDown in plain text editor when using Chrome (#7230)
- Elastic: Fix recipient input bug when using click to select a contact from autocomplete list (#7231)
- Elastic: Fix color of a folder with recent messages (#7281)
- Elastic: Restrict logo size in print view (#7275)
- Fix invalid Content-Type for messages with only html part and inline images - Mail_Mime-1.10.7 (#7261)
- Fix missing contact display name in QR Code data (#7257)
- Fix so button label in Select image/media dialogs is "Close" not "Cancel" (#7246)
- Fix regression in testing database schema on MSSQL (#7227)
- Fix cursor position after inserting a group to a recipient input using autocompletion (#7267)
- Fix string literals handling in IMAP STATUS (and various other) responses (#7290)
- Fix bug where multiple images in a message were replaced by the first one on forward/reply/edit (#7293)
- Fix handling keyservers configured with protocol prefix (#7295)
- Markasjunk: Fix marking as spam/ham on moving messages with Move menu (#7189)
- Markasjunk: Fix bug where moving to Junk was failing on messages selected with Select > All (#7206)
- Fix so imap error message is displayed to the user on folder create/update (#7245)
- Fix bug where a special folder couldn't be created if a special-use flag is not supported (#7147)
- Mailvelope: Fix bug where recipients with name were not handled properly in mail compose (#7312)
- Fix characters encoding in group rename input after group creation/rename (#7330)
- Fix bug where some message/rfc822 parts could not be attached on forward (#7323)
- Make install-jsdeps.sh script working without the 'file' program installed (#7325)
- Fix performance issue of parsing big HTML messages by disabling HTML5 parser for these (#7331)
- Fix so Print button for PDF attachments works on Firefox >= 75 (#5125)
- Security: Fix XSS issue in handling of CDATA in HTML messages [CVE-2020-12625]
- Security: Fix remote code execution via crafted 'im_convert_path' or 'im_identify_path' settings [CVE-2020-12641]
- Security: Fix local file inclusion (and code execution) via crafted 'plugins' option [CVE-2020-12640]
- Security: Fix CSRF bypass that could be used to log out an authenticated user [CVE-2020-12626] (#7302)

## Release 1.4.3

- Enigma: Fix so key list selection is reset when opening key creation form (#7154)
- Enigma: Fix so using list checkbox selection does not load the key preview frame
- Enigma: Fix generation of key pairs for identities with IDN domains (#7181)
- Enigma: Display IDN domains of key users and identities in UTF8
- Enigma: Fix bug where "Send unencrypted" button didn't work in Elastic skin (#7205)
- Managesieve: Fix bug where it wasn't possible to save flag actions (#7188)
- Markasjunk: Fix bug where marking as spam/ham didn't work on moving messages with drag-and-drop (#7137)
- Password: Make chpass-wrapper.py Python 3 compatible (#7135)
- Elastic: Fix disappearing sidebar in mail compose after clicking Mail button
- Elastic: Fix incorrect aria-disabled attribute on Mail taskmenu button in mail compose
- Elastic: Fix bug where it was possible to switch editor mode when 'htmleditor' was in 'dont_override' (#7143)
- Elastic: Fix text selection in recipient inputs (#7129)
- Elastic: Fix missing Close button in "more recipients" dialog
- Elastic: Fix non-working folder subscription checkbox for newly added folders (#7174)
- Fix regression where "Open in new window" action didn't work (#7155)
- Fix PHP Warning: array_filter() expects parameter 1 to be array, null given in subscriptions_option plugin (#7165)
- Fix unexpected error message when mail refresh involves folder auto-unsubscribe (#6923)
- Fix recipient duplicates in print-view when the recipient list has been expanded (#7169)
- Fix bug where files in skins/ directory were listed on skins list (#7180)
- Fix bug where message parts with no Content-Disposition header and no name were not listed on attachments list (#7117)
- Fix display issues with mail subject that contains line-breaks (#7191)
- Fix invalid Content-Transfer-Encoding on multipart messages - Mail_Mime fix (#7170)
- Fix regression where using an absolute path to SQLite database file on Windows didn't work (#7196)
- Fix using unix:///path/to/socket.file in memcached driver (#7210)

## Release 1.4.2

- Add support for PHPUnit 6 and 7 (#6870)
- Plugin API: Make `actionbefore`, `before<action>`, `actionafter` and `after<action>` events working with plugin actions (#7106)
- Managesieve: Replace "Filter disabled" with "Filter enabled" (#7028)
- Managesieve: Fix so modifier type select wasn't hidden after hiding modifier select on header change
- Managesieve: Fix filter selection after removing a first filter (#7079)
- Markasjunk: Fix marking more than one message as spam/ham with email_learn driver (#7121)
- Password: Fix kpasswd and smb drivers' double-escaping bug (#7092)
- Enigma: Add script to import keys from filesystem to the db storage (for multihost)
- Installer: Fix DB Write test on SQLite database ("database is locked" error) (#7064)
- Installer: Fix so SQLite DSN with a relative path to the database file works in Installer
- Elastic: Fix contrast of warning toasts (#7058)
- Elastic: Simple search in pretty selects (#7072)
- Elastic: Fix hidden list widget on mobile/tablet when selecting folder while search menu is open (#7120)
- Fix so type attribute on script tags is not used on HTML5 pages (#6975)
- Fix unread count after purge on a folder that is not currently selected (#7051)
- Fix bug where Enter key didn't work on messages list in "List" layout (#7052)
- Fix bug where deleting a saved search in addressbook caused display issue on sources/groups list (#7061)
- Fix bug where a new saved search added after removing all searches wasn't added to the list (#7061)
- Fix bug where a new contact group added after removing all groups from addressbook wasn't added to the list
- Fix bug where Ctype extension wasn't required in Installer and INSTALL file (#7049)
- Fix so install-jsdeps.sh removes Bootstrap's sourceMappingURL (#7035)
- Fix so use of Ctrl+A does not scroll the list (#7020)
- Fix/remove useless keyup event handler on username input in logon form (#6970)
- Fix bug where cancelling switching from HTML to plain text didn't set the flag properly (#7077)
- Fix bug where HTML reply could add an empty line with extra indentation above the original message (#7088)
- Fix matching multiple X-Forwarded-For addresses with 'proxy_whitelist' (#7107)
- Fix so displayed maximum attachment size depends also on 'max_message_size' (#7105)
- Fix bug where 'skins_allowed' option didn't enforce user skin preference (#7080)
- Fix so contact's organization field accepts up to 128 characters (it was 50)
- Fix bug where listing tables in PostgreSQL database with db_prefix didn't work (#7093)
- Fix bug where 'text' attribute on body tag was ignored when displaying HTML message (#7109)
- Fix bug where next message wasn't displayed after delete in List mode (#7096)
- Fix so number of contacts in a group is not limited to 200 when redirecting to mail composer from Contacts (#6972)
- Fix malformed characters in HTML message with charset meta tag not in head (#7116)

## Release 1.4.1

- Elastic: Change HTML editor widget to improve form flow (#6992)
- Elastic: Fix position of mobile floating action button (#7038)
- Managesieve: Fix locked UI after opening filter frame (#7007)
- Fix PHP warning: "array_merge(): Expected parameter 2 to be an array, null given in sendmail.inc (#7003)
- Fix bug where cache keys could exceed length limit specified in db schema (#7004)
- Fix invalid Signature button state after escaping Mailvelope mode (#7015)
- Fix so 401 error is returned only on failed logon requests (#7010)
- Fix db_prefix handling in queries with `TRUNCATE TABLE <name>` and `UNIQUE <name>` (#7013)
- Fix so update.sh script warns about changed defaults (#7011)
- Fix tables listing routine when DSN contained a database with unsupported suffix (#7034)
- Fix so Elastic is also a default in jqueryui plugin (#7039)
- Fix bug where the Installer would not warn about required schema upgrade (#7042)

## Release 1.4.0

- Elastic: Resizable columns (#6929)
- Elastic: Fix position and style of auto-complete dropdown on small screens (#6951)
- Elastic: Fix initial focus on recipients input in mail compose screen
- Elastic: Fix inserting responses at cursor position (#6971)
- Elastic: Fix unread filter icon and search state on folder change (#6978)
- Elastic: Fix regression where Encrypt button wasn't displayed in mail compose toolbar (#6982)
- Elastic: Fix regression where recipient input didn't update internal input state (#6988)
- Enigma: Fix bug where signing option was set to disabled after saving a draft in Elastic skin (#6515)
- Redis: Improve error handling and phpredis 5.X support (#6888)
- Archive: Fix bug where next email was not displayed after Archive button use (#6965)
- Archive: Fix missing Archive icon in folder selector popup in Elastic
- Fix bug where cache keys were not case-sensitive on MySQL/MSSQL (#6942)
- Fix so an error is logged when encryption fails (#6948)
- Fix bug where inline images could have been ignored if Content-Id header contained redundant spaces (#6980)
- Fix and document skin_logo setup (#6981)

## Release 1.4-rc2

- Update to jQuery 3.4.1
- Clarified 'address_book_type' option behavior (#6680)
- Added cookie mismatch detection, display an error message informing the user to clear cookies
- Renamed 'log_session' option to 'session_debug'
- Removed 'delete_always' option (#6782)
- Don't log full session identifiers in userlogins log (#6625)
- Support $HasAttachment/$HasNoAttachment keywords (#6201)
- Support PECL memcached extension as a session and cache storage driver (experimental)
- Switch to IDNA2008 variant (#6806)
- installto.sh: Add possibility to run the update even on the up-to-date installation (#6533)
- Plugin API: Add 'render_folder_selector' hook
- Added 'keyservers' option to define list of HKP servers for Enigma/Mailvelope (#6326)
- Added flag to disable server certificate validation via Mysql DSN argument (#6848)
- Select all records on the current list page with CTRL + A (#6813)
- Use Left/Right Arrow keys to faster move over threaded messages list (#6399)
- Changes in `display_next` setting (#6795):
    - Move it to Preferences > User Interface > Main Options
    - Make it apply to Contacts interface too
    - Make it apply only if deleting/moving a previewed message/contact
- Redis: Support connection to unix socket
- Put charset meta specification before a title tag, add page title automatically (#6811)
- Elastic: Various internal refactorings
- Elastic: Add Prev/Next buttons on message page toolbar (#6648)
- Elastic: Close search options on Enter key press in quick-search input (#6660)
- Elastic: Changed some icons (#6852)
- Elastic: Changed read/unread icons (#6636)
- Elastic: Changed "Move to..." icon (#6637)
- Elastic: Add hide/show for advanced preferences (#6632)
- Elastic: Add default icon on Settings/Preferences lists for external plugins (#6814)
- Elastic: Add indicator for popover menu items that open a submenu (#6868)
- Elastic: Move compose attachments/options to the right side (#6839)
- Elastic: Add border/background to attachments list widget (#6842)
- Elastic: Add "Show unread messages" button to the search bar (#6587)
- Elastic: Fix bug where toolbar disappears on attachment menu use in Chrome (#6677)
- Elastic: Fix folders list scrolling on touch devices (#6706)
- Elastic: Fix non-working pretty selects in Chrome browser (#6705)
- Elastic: Fix issue with absolute positioned mail content (#6739)
- Elastic: Fix bug where some menu actions could cause a browser popup warning
- Elastic: Fix handling mailto: URL parameters in contact menu (#6751)
- Elastic: Fix keyboard navigation in some menus, e.g. the contact menu
- Elastic: Fix visual issue with long buttons in .boxwarning (#6797)
- Elastic: Fix handling new-line in text pasted to a recipient input
- Elastic: Fix so search is not reset when returning from the message preview page (#6847)
- Larry: Fix regression where menu actions didn't work with keyboard (#6740)
- ACL: Display user/group names (from ldap) instead of acl identifier
- Password: Added ldap_exop driver (#4992)
- Password: Added support for SSHA512 password algorithm (#6805)
- Managesieve: Fix bug where global includes were requested for vacation (#6716)
- Managesieve: Use RFC-compliant line endings, CRLF instead of LF (#6686)
- Managesieve: Fix so "Create filter" option does not show up when Filters menu is disabled (#6723)
- Enigma: For verified signatures, display the user id associated with the sender address (#5958)
- Enigma: Fix bug where revoked users/keys were not greyed out in key info
- Enigma: Fix error message when trying to encrypt with a revoked key (#6607)
- Enigma: Fix "decryption oracle" bug [CVE-2019-10740] (#6638)
- Enigma: Fix bug where signature verification could have been skipped for some message structures (#6838)
- Fix language selection for spellchecker in html mode (#6915)
- Fix css styles leak from replied/forwarded message to the rest of the composed text (#6831)
- Fix invalid path to "add contact" icon when using assets_path setting
- Fix invalid path to blocked.gif when using assets_path setting (#6752)
- Fix so advanced search dialog is not automatically displayed on searchonly addressbooks (#6679)
- Fix so an error is logged when more than one attachment plugin has been enabled, initialize the first one (#6735)
- Fix bug where flag change could have been passed to a preview frame when not expected
- Fix bug in HTML parser that could cause missing text fragments when there was no head/body tag (#6713)
- Fix bug where HTML messages with a xml:namespace tag were not rendered (#6697)
- Fix TinyMCE download location (#6694)
- Fix so "Open in new window" consistently displays "external window" interface (#6659)
- Fix bug where next row wasn't selected after deleting a collapsed thread (#6655)
- Fix bug where external content (e.g. mail body) was passed to templates parsing code (#6640)
- Fix bug where attachment preview didn't work with x_frame_options=deny (#6688)
- Fix so bin/install-jsdeps.sh returns error code on error (#6704)
- Fix bug where bmp images couldn't be displayed on some systems (#6728)
- Fix bug in parsing vCard data using PHP 7.3 due to an invalid regexp (#6744)
- Fix bug where bold/strong text was converted to upper-case on html-to-text conversion (6758)
- Fix bug in rcube_utils::parse_hosts() where %t, %d, %z could return only tld (#6746)
- Fix bug where Next/Prev button in mail view didn't work with multi-folder search result (#6793)
- Fix bug where selection of columns on messages list wasn't working
- Fix bug in converting multi-page Tiff images to Jpeg (#6824)
- Fix bug where handling multiple messages from multi-folder search result could not work (#6845)
- Fix bug where unread count wasn't updated after moving multi-folder result (#6846)
- Fix wrong messages order after returning to a multi-folder search result (#6836)
- Fix some PHP 7.4 compat. issues (#6884, #6866)
- Fix bug where it was possible to bypass the position:fixed CSS check in received messages (#6898)
- Fix bug where some strict remote URIs in url() style were unintentionally blocked (#6899)
- Fix bug where it was possible to bypass the CSS jail in HTML messages using :root pseudo-class (#6897)
- Fix bug where it was possible to bypass href URI check with data:application/xhtml+xml URIs (#6896)

## Release 1.4-rc1

- Changed 'password_charset' default to 'UTF-8' (#6522)
- Add skins_allowed option (#6483)
- SMTP GSSAPI support via krb_authentication plugin (#6417)
- Avoid Referer leaking by using Referrer-Policy:same-origin header (#6385)
- Removed 'referer_check' option (#6440)
- Use constant prefix for temp file names, don't remove temp files from other apps (#6511)
- Ignore 'Sender' header on Reply-All action (#6506)
- deluser.sh: Add option to delete users who have not logged in for more than X days (#6340)
- HTML5 Upload Progress - as a replacement for the old server-side solution (#6177)
- Update to TinyMCE 4.8.2
- Update to jQuery-MiniColors 2.3.4
- Prevent from using deprecated timezone names from jsTimezoneDetect
- Force session.gc_probability=1 when using custom session handlers (#6560)
- Support simple field labels (e.g. LetterHub examples) in csv imports (#6541)
- Add cache busters also to images used by templates (#6610)
- Plugin API: Added 'raise_error' hook (#6199)
- Plugin API: Added 'common_headers' hook (#6385)
- Plugin API: Added 'ldap_connected' hook
- Enigma: Update to OpenPGPjs 4.2.1 - fixes user name encoding issues in key generation (#6524)
- Enigma: Fixed multi-host synchronization of private and deleted keys and pubring.kbx file
- Managesieve: Added support for 'editheader' extension - RFC5293 (#5954)
- Managesieve: Fix bug where custom header or variable could be lost on form submission (#6594)
- Markasjunk: Integrate markasjunk2 features into markasjunk - marking as non-junk + learning engine (#6504)
- Password: Added 'modoboa' driver (#6361)
- Password: Fix bug where password_dovecotpw_with_method setting could be ignored (#6436)
- Password: Fix bug where new users could skip forced password change (#6434)
- Password: Allow drivers to override default password comparisons (eg new is not same as current) (#6473)
- Password: Allow drivers to override default strength checks (eg allow for 'not the same as last x passwords') (#246)
- Password: Allow drivers to define password strength rules displayed to the user
- Password: Allow separate password saving and strength drivers for use of strength checking services (#5040)
- Password: Add zxcvbn driver for checking password strength (#6479)
- Password: Disallow control characters in passwords
- Password: Add support for Plesk >= 17.8 (#6526)
- Elastic: Improved datepicker displayed always in parent window
- Elastic: On touch devices display attachment icons on messages list (#6296)
- Elastic: Make menu button inactive if all subactions are inactive (#6444)
- Elastic: On mobile/tablet jump to the list on folder selection (#6415)
- Elastic: Various improvements on mail compose screen (#6413)
- Elastic: Support new-line char as a separator for pasted recipients (#6460)
- Elastic: Improved UX of search dialogs (#6416)
- Elastic: Fix unwanted thread expanding when selecting a collapsed thread in non-mobile mode (#6445)
- Elastic: Fix too small height of mailvelope mail preview frame (#6600)
- Elastic: Add "status bar" for mobile in mail composer
- Elastic: Add selection options on contacts list (#6595)
- Elastic: Fix unintentional layout preference overwrite (#6613)
- Elastic: Fix bug where Enigma options in mail compose could sometimes be ignored (#6515)
- Log errors caused by low pcre.backtrack_limit when sending a mail message (#6433)
- Fix regression where drafts were not deleted after sending the message (#6756)
- Fix so max_message_size limit is checked also when forwarding messages as attachments (#6580)
- Fix so performance stats are logged to the main console log also when per_user_logging=true
- Fix malformed message saved into Sent folder when using big attachments and low memory limit (#6498)
- Fix incorrect IMAP SASL GSSAPI negotiation (#6308)
- Fix so unicode in local part of the email address is also supported in recipient inputs (#6490)
- Fix bug where autocomplete list could be displayed out of screen (#6469)
- Fix style/navigation on error page depending on authentication state (#6362)
- Fix so invalid smtp_helo_host is never used, fallback to localhost (#6408)
- Fix custom logo size in Elastic (#6424)
- Fix listing the same attachment multiple times on forwarded messages
- Fix bug where a message/rfc822 part without a filename wasn't listed on the attachments list (#6494)
- Fix inconsistent offset for various time zones - always display Standard Time offset (#6531)
- Fix dummy Message-Id when resuming a draft without Message-Id header (#6548)
- Fix handling of empty entries in vCard import (#6564)
- Fix bug in parsing some IMAP command responses that include unsolicited replies (#6577)
- Fix PHP 7.2 compatibility in debug_logger plugin (#6586)
- Fix so ANY record is not used for email domain validation, use A, MX, CNAME, AAAA instead (#6581)
- Fix so mime_content_type check in Installer uses files that should always be available (i.e. from program/resources) (#6599)
- Fix missing CSRF token on a link to download too-big message part (#6621)
- Fix bug when aborting dragging with ESC key didn't stop the move action (#6623)

## Release 1.4-beta

- Added new skin with mobile support - the Elastic
- Support Redis cache
- Email Resent (Bounce) feature (#4985)
- Improved Mailvelope integration
  - Added private key listing and generating to identity settings
  - Enable encrypt & sign option if Mailvelope supports it
- Allow contacts without an email address (#5079)
- Support SMTPUTF8 and relax email address validation to support unicode in local part (#5120)
- Support for IMAP folders that cannot contain both folders and messages (#5057)
- Update to jQuery-3.3.1
- Update to jQuery-minicolors 2.2.6
- Update to TinyMCE 4.7.13
- Remove sample PHP configuration from .htaccess and .user.ini files (#5850)
- Extend skin_logo setting to allow per skin logos (#6272)
- Use Masterminds/HTML5 parser for better HTML5 support (#5761)
- Add More actions button in Contacts toolbar with Copy/Move actions (#6081)
- Display an error when clicking disabled link to register protocol handler (#6079)
- Add option trusted_host_patterns (#6009, #5752)
- Support additional connect parameters in PostgreSQL database wrapper
- Use UI dialogs instead of confirm() and alert() where possible
- Display value of the SMTP message size limit in the error message (#6032)
- Show message flagged status in message view (#5080)
- Skip redundant INSERT query on successful logon when using PHP7
- Replace display_version with display_product_version (#5904)
- Extend disabled_actions config so it accepts also button names (#5903)
- Handle remote stylesheets the same as remote images, ask the user to allow them (#5994)
- Add Message-ID to the sendmail log (#5871)
- Add option to hide folders in share/other-user namespace or outside of the personal namespace root (#5073)
- Archive: Fix archiving by sender address on cyrus-imap
- Archive: Style Archive folder also on folder selector and folder manager lists
- Archive: Add Thunderbird compatible Month option (#5623)
- Archive: Create archive folder automatically if it's configured, but does not exist (#6076)
- Enigma: Add button to send mail unencrypted if no key was found (#5913)
- Enigma: Add options to set PGP cipher/digest algorithms (#5645)
- Enigma: Multi-host support
- Managesieve: Add ability to disable filter sets and other actions (#5496, #5898)
- Managesieve: Add option managesieve_forward to enable settings dialog for simple forwarding (#6021)
- Managesieve: Support filter action with custom IMAP flags (#6011)
- Managesieve: Support 'mime' extension tests - RFC5703 (#5832)
- Managesieve: Support GSSAPI authentication with krb_authentication plugin (#5779)
- Managesieve: Support enabling the plugin for specified hosts only (#6292)
- Password: Support host variables in password_db_dsn option (#5955)
- Password: Automatic virtualmin domain setting, removed password_virtualmin_format option (#5759)
- Password: Added password_username_format option (#5766)
- subscriptions_option: show \\Noselect folders greyed out (#5621)
- zipdownload: Added option to define size limit for multiple messages download (#5696)
- vcard_attachments: Add possibility to send contact vCard from Contacts toolbar (#6080)
- Changed defaults for smtp_user (%u), smtp_pass (%p) and smtp_port (587)
- Composer: Fix certificate validation errors by using packagist only (#5148)
- Add --get and --extract arguments and CACHEDIR env-variable support to install-jsdeps.sh (#5882)
- Support _filter and _scope as GET arguments for opening mail UI (#5825)
- Various improvements for templating engine and skin behaviours
  - Support conditional include
  - Support for 'link' objects
  - Support including files with path relative to templates directory
  - Use `<button>` instead of `<input>` for submit button on logon screen
- Support skin localization (#5853)
- Reset onerror on images if placeholder does not exist to prevent from requests storm
- Unified and simplified code for loading content frame for responses and identities
- Display contact import and advanced search in popup dialogs
- Display a dialog for mail import with supported format description and upload size hint
- Make possible to set (some) config options from a skin
- Added optional checkbox selection for the list widget
- Make 'compose' command always enabled
- Add .log suffix to all log file names, add option log_file_ext to control this (#313)
- Return "401 Unauthorized" status when login fails (#5663)
- Support both comma and semicolon as recipient separator, drop recipients_separator option (#5092)
- Plugin API: Added 'show_bytes' hook (#5001)
- Add option to not indent quoted text on top-posting reply (#5105)
- Removed global $CONFIG variable
- Removed debug_level setting
- Support AUTHENTICATE LOGIN for IMAP connections (#5563)
- Support LDAP GSSAPI authentication (#5703)
- Localized timezone selector (#4983)
- Use 7bit encoding for ISO-2022-* charsets in sent mail (#5640)
- Handle inline images also inside multipart/mixed messages (#5905)
- Allow style tags in HTML editor on composed/reply messages (#5751)
- Use Github API as a fallback to fetch js dependencies to workaround throttling issues (#6248)
- Show confirm dialog when moving folders using drag and drop (#6119)
- Fix bug where new_user_dialog email check could have been circumvented by deleting / abandoning session (#5929)
- Fix skin extending for assets (#5115)
- Fix handling of forwarded messages inside of a TNEF message (#5632)
- Fix bug where attachment size wasn't visible when the filename was too long (#6033)
- Fix checking table columns when there's more schemas/databases in postgres/mysql (#6047)
- Fix css conflicts in user interface and e-mail content (#5891)
- Fix duplicated signature when using Back button in Chrome (#5809)
- Fix touch event issue on messages list in IE/Edge (#5781)
- Fix so links over images are not removed in plain text signatures converted from HTML (#4473)
- Fix various issues when downloading files with names containing non-ascii chars, use RFC 2231 (#5772)

## Release 1.3.8

- Fix PHP warnings on dummy QUOTA responses in Courier-IMAP 4.17.1 (#6374)
- Fix so fallback from BINARY to BODY FETCH is used also on [PARSE] errors in dovecot 2.3 (#6383)
- Enigma: Fix deleting keys with authentication subkeys (#6381)
- Fix invalid regular expressions that throw warnings on PHP 7.3 (#6398)
- Fix so Classic skin splitter does not escape out of window (#6397)
- Fix XSS issue in handling invalid style tag content [CVE-2018-19206] (#6410)
- Fix compatibility with MySQL 8 - error on 'system' table use
- Managesieve: Fix bug where show_real_foldernames setting wasn't respected (#6422)
- New_user_identity: Fix %fu/%u vars substitution in user specific LDAP params (#6419)
- Fix support for `allow-from <uri>` in `x_frame_options` config option (#6449)
- Fix bug where valid content between HTML comments could have been skipped in some cases (#6464)
- Fix multiple VCard field search (#6466)
- Fix session issue on long running requests (#6470)

## Release 1.3.7

- Fix PHP Warning: Use of undefined constant IDNA_DEFAULT on systems without php-intl (#6244)
- Fix bug where some parts of quota information could have been ignored (#6280)
- Fix bug where some escape sequences in html styles could bypass security checks
- Fix bug where some forbidden characters on Cyrus-IMAP were not prevented from use in folder names
- Fix bug where only attachments with the same name would be ignored on zip download (#6301)
- Fix bug where unicode contact names could have been broken/emptied or caused DB errors (#6299)
- Fix bug where after "mark all folders as read" action message counters were not reset (#6307)
- Enigma: [EFAIL] Don't decrypt PGP messages with no MDC protection (#6289)
- Fix bug where some HTML comments could have been malformed by HTML parser (#6333)

## Release 1.3.6

- Fix parsing date strings (e.g. from a Date: mail header) with comments (#6216)
- Fix PHP 7.2: count(): Parameter must be an array in enchant-based spellchecker (#6234)
- Fix possible IMAP command injection and type juggling vulnerabilities (#6229)
- Enigma: Fix key selection for signing
- Enigma: Enable keypair generation on Internet Explorer 11
- Fix check_request() bypass in places using get_uids() [CVE-2018-9846] (#6238)
- Fix bug where usernames without domain part could be malformed or converted to lower-case on logon (#6224)

## Release 1.3.5

- Managesieve: Fix bug where text: syntax was forced for strings longer than 1024 characters (#6143)
- Managesieve: Fix missing Save button in Edit Filter Set page of Classic skin (#6154)
- Fix duplicated labels in Test SMTP Config section (#6166)
- Fix PHP Warning: exif_read_data(...): Illegal IFD size (#6169)
- Enigma: Fix key generation in Safari by upgrade to OpenPGP 2.6.2 (#6149)
- Fix security issue in remote content blocking on HTML image and style tags (#6178)
- Added 9pt and 11pt to the list of font sizes in HTML editor
- Fix handling encoding of HTML tags in "inline" JSON output (#6207)
- Fix bug where some unix timestamps were not handled correctly by rcube_utils::anytodatetime() (#6212)

## Release 1.3.4

- Fix bug where contacts search could skip some records (#6130)
- Fix possible information leak - add more strict sql error check on user creation (#6125)
- Fix a couple of warnings on PHP 7.2 (#6098)
- Fix broken long filenames when using imap4d server - workaround server bug (#6048)
- Fix so temp_dir misconfiguration prints an error to the log (#6045)
- Fix untagged COPYUID responses handling - again (#5982)
- Fix PHP warning "idn_to_utf8(): INTL_IDNA_VARIANT_2003 is deprecated" with PHP 7.2 (#6075)
- Fix bug where Archive folder wasn't auto-created on login with create_default_folders=true
- Fix performance issue when parsing malformed and long Date header (#6087)
- Fix syntax error in mssql.initial.sql (#6097)
- Fix bug where contacts export by selection returned no more than 10 entries (#6103)
- Fix searching contacts by address in LDAP source (#6084)
- Fix X-Frame-Options:ALLOW-FROM support, remove custom click-jacking protection (#6057)

## Release 1.3.3

- Fix decoding of mailto: links with + character in HTML messages (#6020)
- Fix false reporting of failed upgrade in installto.sh (#6019)
- Fix file disclosure vulnerability caused by insufficient input validation [CVE-2017-16651] (#6026)
- Fix mangled non-ASCII characters in links in HTML messages (#6028)

## Release 1.3.2

- Fix bug where pink image was used instead of a thumbnail when image resize fails (#5933)
- Fix so files size/count limit is verified (client-side) also on drag-n-drop uploads (#5940)
- Fix invalid template loading on a message error in preview frame (#5941)
- Fix bug where HTML messages could have been rendered empty on some systems (#5957)
- Fix wording of "Mark previewed messages as read" to "Mark messages as read" (#5952)
- Enigma: Fix decryption of messages encoded with non-ascii charset (#5962)
- Fix missing cursor in HTML editor on mail reply (#5969)
- Fix (again) bug where image data URIs in css style were treated as evil/remote in mail preview (#5580)
- Fix bug where mail search could return empty result on servers without SORT capability (#5973)
- Fix bug where assets_path wasn't added to some watermark frames
- Fix so untagged COPYUID responses are also supported according to RFC6851 (#5982)
- Fix issue caused by non-default session.cookie_lifetime setting (#5961)
- Fix Edge encoding bug when pasting text into the HTML editor, update to TinyMCE 4.5.8 (#5885)
- Fix handling of unknown Content-Disposition type (#6002)
- Fix truncated folder name on messages list in multi-folder mode, for folders with non-ascii characters (#6004)
- Fix bug where removing the last subfolder did not hide toggle button on its parent record (#6007)
- Fix bug where ghost messages could be added to the list after fast delete (#5941)

## Release 1.3.1

- Add Preferences > Mailbox View > Main Options > Layout (#5829)
- Password: Fix compatibility with PHP 7+ in cpanel_webmail driver (#5820)
- Managesieve: Fix parsing dot-staffed lines in multiline text (#5838)
- Managesieve: Fix AM/PM suffix in vacation time selectors
- Managesieve: Fix bug where 'exists' operator was reset to 'contains' (#5899)
- Remove non-printable characters from filenames on download/display (#5880)
- Fix decoding non-ascii attachment names from TNEF attachments (#5646, #5799)
- Fix uninitialized string offset in rcube_utils::bin2ascii() and make sure rcube_utils::random_bytes() result has always requested length (#5788)
- Fix bug where HTML messages with @media styles could modify style of page body (#5811)
- Fix style issue on selected and unfocused message that is part of a thread (#5798)
- Fix bug where a.button style from managesieve plugin could impact other elements (#5800)
- Fix position of selected icon for (Mailvelope) Encrypt button
- Fix fatal error when using DMY- or MDY-based date format in PostgreSQL (#5808)
- Fix bug where errors were not printed when using bin/update.sh (#5834)
- Fix PHP 7.2 warnings on count() use (#5845)
- Fix bug where Chrome could not upload the same file that was selected before (#5854)
- Fix duplicate messages on the list after deleting messages on the next to the last page (#5862)
- Fix bug where messages count was not updated after delete when imap_cache is set (#5872)
- Fix potential XSS vulnerability with malformed HTML message markup
- Fix sending message with "Too many public recipients" dialog buttons (#5924)
- Bring back double-click behavior on the message list which was removed in 1.3.0 (#5823)
- Enigma: Fix decrypting an encrypted+signed message when signature verification fails (#5914)

## Release 1.3.0

- Update to TinyMCE 4.5.7
- Fix bug where invalid recipients could be silently discarded (#5739)
- Fix conflict with _gid cookie of Google Analytics (#5748)
- Print error from CLI scripts when system/exec function is disabled (#5744)
- Fix bug where comment notation within style tag would cause the whole style to be ignored (#5747)
- Fix bug where it wasn't possible to scroll folders list in Edge (#5750)
- Fix folders list sorting on Windows - if php-intl is available (#5732)
- Fix addressbook searching by gender (#5757)
- Fix prevention from using % and * characters in folder name (#5762)
- Fix POST parameter reflection in default_charset selector (#5768)
- Enigma: Fix compatibility with assets_dir
- Managesieve: Skip redundant LISTSCRIPTS command
- Fix SQL syntax error on MariaDB 10.2 (#5774)
- Fix bug where zipdownload ignored files with the same name (#5777)
- Fix bug where it wasn't possible to set timezone to auto-detected value (#5782)

## Release 1.3-rc

- "Flattened" the larry theme: fresher look by removing shadows and gradients
- Support logging to php://stdout (#5721)
- Add support for DelSp=Yes in format=flowed messages (#5702)
- Update to jQuery 3.2.1
- Update to TinyMCE 4.5.6
- Plugin API: Call message_part_structure hook for sub-parts of multipart/alternative message (#5678)
- Enigma: Always use detached signatures (#5624)
- Enigma: Fix handling of messages with nested PGP encrypted parts (#5634)
- Minimize unwanted message loading in preview frame on drag (#5616)
- Fix failing database schema check in all engines except mysql (#5730)
- Fix autocomplete popup closing with click outside the input, don't handle Tab key as Enter (#5606)
- Fix jsdeps.json synchronization on update, warn about missing requirements of install-jsdeps.sh (#5598)
- Fix missing thread expand icon on search result in widescreen mode (#5613)
- Fix bug where image data URIs in css style were treated as evil/remote in mail preview (#5580)
- Fix bug where external content in src attribute of input/video tags was not secured (#5583)
- Fix PHP error on update of a contact with multiple email addresses when using PHP 7.1 (#5587)
- Fix bug where mail content frame couldn't be reset in some corner cases (#5608)
- Fix bug where some classic skin images were not displayed in IE/Edge (#5614)
- Fix bug where signature couldn't be added above the quote in Firefox 51 (#5628)
- Fix regression where groups with email address were resolved to its members' addresses
- Fix update of group name in the contacts list header on group rename (#5648)
- Add rewrite rule to disable access to /vendor/bin folder in .htaccess (#5630)
- Fix bug where it was too easy accidentally move a folder when using the subscription checkbox (#5655)
- Managesieve: Fix parser issue with empty lines between comments (#5657)
- Managesieve: Fix possible defect in handling \r\n in scripts (#5685)
- Fix/rephrase "unsaved changes" warning when cancelling a draft (#5610)
- Fix XSS issue in handling of a style tag inside of an svg element [CVE-2017-6820]
- Fix bug where settings/upload.inc could not be used by plugins (#5694)
- Fix regression in LDAP fuzzy search where it always used prefix search instead (#5713)
- Fix bug where namespace prefix could not be truncated on folders list if show_real_foldernames=true (#5695)
- Fix undesired effects when postgres database uses different timezone than PHP host (#5708)
- Installer: Fix DB schema initialization on MS SQL Server
- Fix bug where base_dn setting was ignored inside group_filters (#5720)
- Password: Fix security issue in virtualmin and sasl drivers [CVE-2017-8114]

## Release 1.3-beta

- Nicely handle contact deletion on contact edit (#5522)
- vcard_attachments: Add possibility to attach contact vCard to composed message (#4997)
- Preserve message internal/received date on import in mbox format (#5559)
- Zipdownload: Fix date format in mbox "From line"
- Possibility to display QR code for contacts data (#5030)
- Added identicon plugin
- Widescreen layout aka three column view (#5093)
- Unify automatic marking as \Seen in preview pane, full-page and extwin views (#5071)
- Disable double-click on the list when preview pane is on (#5199)
- Support hostname and hostname:port in force_https option (#5511)
- Support ALLOW-FROM in x_frame_options (#5122)
- Allow to omit a subject when sending an email (#5068)
- Warn about too many disclosed recipients in composed email [max_disclosed_recipients] (#5132)
- identity_select: Support Received header (#5085)
- Plugin API: Added get_compose_responses hook (#5457)
- Display error when trying to upload more files than specified in max_file_uploads (#5483)
- Add missing sql upgrade file for 'ip' column resize in session table (#5465)
- Do not show inline images of unsupported mimetype (#5463)
- Password: Added replacement variables support in password_pop_host (#5539)
- Password: Don't store passwords in temp files when using dovecotpw (#5531)
- Password: Added LDAP PPolicy driver (#5364)
- Password: Added cpanel_webmail driver (#5549)
- Password: Added possibility to nicely redirect from other plugins on password expiration (#5468)
- Implement separate action to mark all messages in a folder as \Seen (#5006)
- Implement marking as \Seen in all folders or in a folder and its subfolders (#5076)
- Archive: Don't reload messages list when it's not needed (#5225)
- Archive: Add option to automatically mark archived messages as \Seen (#5142)
- Improve randomness of password salts and random hashes (#5266)
- Password/cPanel: Add support for hash authentication and reseller accounts (#5252)
- Support host-specific imap_conn_options/smtp_conn_options/managesieve_conn_options (#5136)
- Center and scale images in attachment preview frame (#5421)
- Added max_message_size option enforced when attaching files to a composed message (#4993)
- Added Search button in quick search menus (#5312)
- Implement "one click" attachment/messages/photo upload (#5024)
- Squirrelmail_usercopy: Add option to define character set of data files
- Removed useless 'created' column from 'session' table (#5389)
- Dropped legacy browsers support (#5167)
    - Removed legacy_browser plugin
    - Removed hacks for IE < 10
    - Update to jQuery 3.1.1 and jQuery-UI 1.12.0
    - compile .min.js files with ECMASCRIPT5 option
- Require PHP >= 5.4
- Add possibility to preview and download attachments in mail compose (#5053)
- Add possibility to rename attachments in mail compose (#4996)
- Remove backward compatibility "layer" of bc.php (#4902)
- Support WEBP images in mail messages (#5362)
- Support MathML in HTML message preview (#5182)
- Rename Addressbook to Contacts (#5233)
- Remove PHP mail() support, smtp_server is required now (#5340)
- Display full message subject in onmouseover on truncated subject in mail view (#5346)
- Enigma: Support GnuPG 2.1 (#5313)
- Enigma: Support key generation for multiple identities (#5383)
- Enigma: Import keys from key-server(s) (#5286)
- Enigma: Search missing public keys on a key-server in mail compose (#5286)
- Enigma: Delete user keys when using deluser.sh script
- Enigma: Fix redundant list-secret-keys/list-public-keys calls on signing/encryption
- Enigma: Implement PGP encryption and signing in one go (#5302)
- Enigma: Display signature verification status for encrypted+signed messages (#5302)
- Display different attachment icon on encrypted messages
- Display different confirmation text when moving messages to Trash (#5220)
- Indicate that a collapsed thread has flagged children (#5013)
- Implemented message/rfc822 attachment preview
- Update to jsTimezoneDetect 1.0.6
- Managesieve: Add (optional) RAW script editor (#5414)
- Managesieve: Add option to automatically set vacation :from address (#5428)
- Managesieve: Support 'string' test from variables extension [RFC 5229] (#5248)
- Managesieve: Support 'duplicate' extension [RFC 7352]
- Managesieve: Unhide advanced rule controls if there are inputs with errors
- Managesieve: Display warning message when filter form contains errors
- Control search engine crawlers via X-Robots-Tag header instead of `<meta>` and robots.txt (#5098)
- Fixed redundancy in sql caching system and compatibility with Galera Cluster (#5439)
    - Removed redundant 'created' column from cache and cache_shared tables
    - Removed use of redundant data records
    - Added missing primary keys (dictionary, cache, cache_shared tables)
- Fix so templating system does not mess with external (e.g. email) content (#5499)
- Fix redundant keep-alive/refresh after session error on compose page (#5500)
- Managesieve: Fix handling of scripts with nested rules (#5540)
- Fix variable substitution in ldap host for some use-cases, e.g. new_user_identity (#5544)
- Enigma: Fix PHP fatal error when decrypting a message with invalid signature (#5555)
- Fix adding images to new identity signatures
- Fix rsync error handling in installto.sh script (#5562)
- Fix some advanced search issues with multiple addressbooks (#5572)
- Fix so group/addressbook selection is retained on page refresh

## Release 1.2.3

- Searching in both contacts and groups when LDAP addressbook with group_filters option is used
- Fix vulnerability in handling of mail()'s 5th argument
- Fix To: header encoding in mail sent with mail() method (#5475)
- Fix flickering of header topline in min-mode (#5426)
- Fix bug where folders list would scroll to top when clicking on subscription checkbox (#5447)
- Fix decoding of GB2312/GBK text when iconv is not installed (#5448)
- Fix regression where creation of default folders wasn't functioning without prefix (#5460)
- Enigma: Fix bug where last records on keys list were hidden (#5461)
- Enigma: Fix key search with keyword containing non-ascii characters (#5459)
- Fix bug where deleting folders with subfolders could fail in some cases (#5466)
- Fix bug where IMAP password could be exposed via error message (#5472)
- Fix bug where it wasn't possible to store more that 2MB objects in memcache/apc,
  Added memcache_max_allowed_packet and apc_max_allowed_packet settings (#5452)
- Fix "Illegal string offset" warning in rcube::log_bug() on PHP 7.1 (#5508)
- Fix storing "empty" values in rcube_cache/rcube_cache_shared (#5519)
- Fix missing content check when image resize fails on attachment thumbnail generation (#5485)
- Fix displaying attached images with wrong Content-Type specified (#5527)

## Release 1.2.2

- Enigma: Add possibility to configure gpg-agent binary location (enigma_pgp_agent)
- Enigma: Fix signature verification with some IMAP servers, e.g. Gmail, DBMail (#5371)
- Enigma: Make recipient key searches case-insensitive (#5434)
- Fix regression in resizing JPEG images with Imagick (#5376)
- Managesieve: Fix parsing of vacation date-time with non-default date_format (#5372)
- Use SymLinksIfOwnerMatch in .htaccess instead of FollowSymLinks disabled on some hosts for security reasons (#5370)
- Wash position:fixed style in HTML mail for better security (#5264)
- Fix bug where memcache_debug didn't work for session operations
- Fix bug where Message-ID domain part was tied to username instead of current identity (#5385)
- Fix bug where blocked.gif couldn't be attached to reply/forward with insecure content
- Fix E_DEPRECATED warning when using Auth_SASL::factory() (#5401)
- Fix bug where names of downloaded files could be malformed when derived from the message subject (#5404)
- Fix so "All" messages selection is reset on search reset (#5413)
- Fix bug where folder creation could fail if personal namespace contained more than one entry (#5403)
- Fix error causing empty INBOX listing in Firefox when using an URL with user:password specified (#5400)
- Fix PHP warning when handling shared namespace with empty prefix (#5420)
- Fix so folders list is scrolled to the selected folder on page load (#5424)
- Fix so when moving to Trash we make sure the folder exists (#5192)
- Fix displaying size of attachments with zero size
- Fix so "Action disabled" error uses more appropriate 404 code (#5440)

## Release 1.2.1

- Update TinyMCE to version 4.3.13 (#5309)
- Fix bug where errors could have been not logged when per_user_logging=true
- Fix bug where message list columns could be in wrong order after column drag-n-drop and list sorting
- Fix so minified publickey.js (with cache-buster) is used when available (#5254)
- Fix (replace) application/x-tar file extension test as it might not exist in nginx config (#5253)
- Fix PHP warning when password_hosts is set, but is not an array (#5260)
- Fix redundant keep-alive requests when session_lifetime is greater than ~20000 (#5273)
- Fix so subfolders of INBOX can be set as Archive (#5274)
- Fix bug where multi-folder search could choose a wrong folder in "this and subfolders" scope (#5282)
- Fix bug where multi-folder search didn't work for unsubscribed INBOX (#5259)
- Fix bug where "no body" alert could be displayed when sending mailvelope email
- Enigma: Fix keys import from inside of an encrypted message (#5285)
- Enigma: Fix malformed signed messages with force_7bit=true (#5292)
- Enigma: Add possibility to configure gpg binary location (enigma_pgp_binary)
- Enigma: Add possibility to export private keys (#5321)
- Fix searching by email address in contacts with multiple addresses (#5291)
- Fix handling of --delete argument in moduserprefs.sh script (#5296)
- Workaround PHP issue by calling closelog() on script shutdown when using log_driver=syslog (#5289)
- Fix so upgrade script makes sure program/lib directory does not contain old libraries (#5287)
- Fix subscription checkbox state on error in folder subscribe/unsubscribe action (#5243)
- Fix bug where microsecond format in logged date didn't work in some cases
- Fix conflict in new_user_dialog and password_force_new_user settings (#5275)
- Don't create multipart/alternative messages with empty text/plain part (#5283)
- Use contact_search_name format in popup on results in compose contacts search
- Fix handling of 'mailto' and 'error' arguments in message_before_send hook (#5347)
- Fix missing localization of HTML editor when assets_dir != INSTALL_PATH
- Fix handling of blockquote tags with mixed case on html2text conversion (#5363)
- Fix javascript errors in IE on page with iframe that points to another domain

## Release 1.2.0

- Enigma: Added enigma_debug option
- Fix message list multi-select/deselect issue (#5219)
- Fix bug where getting HTML editor content could steal focus from other form controls (#5223)
- Fix bug where contact search menu fields where always unchecked in Larry skin
- Fix autoloading of 'html' class
- Fix bug where Encrypt button appears when switching editor to HTML (#5235)
- Fix XSS issue in href attribute on area tag (#5240)

## Release 1.2-rc

- Managesieve: Refactored script parser to be 100x faster
- Enigma: added option to force users to use signing/encryption
- Enigma: Added option to attach public keys to sent mail (#5152)
- Enigma: Handle messages with text before an encrypted block (#5149)
- Enigma: Handle encrypted/signed content inside message/rfc822 attachments
- Enigma: Fix missing html/plain switch on multipart/signed messages (#4963)
- Enigma: Disable format=flowed for signed plain text messages (#4960)
- Enigma: Fix handling of encrypted + signed messages (#4950)
- Enigma: Fix invalid boundary use in signed messages structure
- Enable use of TLSv1.1 and TLSv1.2 for IMAP (#4955)
- Save copy of original .htaccess file when using installto.sh script (#4947)
- Fix regression where some message attachments could be missing on edit/forward (#4939)
- Fix regression in displaying contents of message/rfc822 parts (#4937)
- Fix handling of message/rfc822 attachments on replies and forwards (#4938)
- Fix PDF support detection in Firefox > 19 (#4941)
- Fix path traversal vulnerability in setting a skin [CVE-2015-8770] (#4945)
- Fix so drag-n-drop of text (e.g. recipient addresses) on compose page actually works (#4944)
- Fix .htaccess rewrite rules to not block .well-known URIs (#4943)
- Fix mail view scaling on iOS (#4915)
- Fix PHP7 warning "session_start(): Session callback expects true/false return value" (#4948)
- Fix XSS issue in SVG images handling [CVE-2015-8864, CVE-2016-4068] (#4949)
- Fix missing language name in "Add to Dictionary" request in HTML mode (#4951)
- Fix (again) security issue in DBMail driver of password plugin [CVE-2015-2181] (#4958)
- Fix bug where Archive/Junk buttons were not active after page jump with select=all mode (#4961)
- Fix bug in long recipients list parsing for cases where recipient name contained @-char (#4964)
- Plugin API: Added addressbook_export hook
- Fix additional_message_headers plugin compatibility with Mail_Mime >= 1.9 (#4966)
- Hide DSN option in Preferences when smtp_server is not used (#4967)
- Fix handling of body parameter in mail compose request
- Protect download urls against CSRF using unique request tokens [CVE-2016-4069] (#4957)
- newmail_notifier: Refactor desktop notifications
- Fix so contactlist_fields option can be set via config file
- Fix so SPECIAL-USE assignments are forced only until user sets special folders (#4782)
- Fix performance in reverting order of THREAD result
- Fix converting mail addresses with @www. into mailto links (#5197)

## Release 1.2-beta

- Update TinyMCE to version 4.2
- Added support for Redis session handler
- Removed some deprecated methods: https://github.com/roundcube/roundcubemail/commit/454b0b1c
- Remove backward compatibility "layer" of bc.php (#4902)
- Add possibility to define date format in write operations for ldap attributes (#3956)
- Display attachment size in compose (#1329)
- Added possibility to drag-n-drop attachments from mail preview to compose window
- Implemented mail messages searching with predefined date interval
- PGP encryption support via Mailvelope integration
- PGP encryption support via Enigma plugin
- PHP7 compatibility fixes (#4836)
- Security: Added brute-force attack prevention via login rate limit (#4922)
- Security: Added options to validate username/password on logon (#4884)
- Security: Improve randomness of security tokens (#4899)
- Security: Use random security tokens instead of hashes based on encryption key (#4829)
- Security: Improved encrypt/decrypt methods with option to choose the cipher_method (#4492)
- Make optional adding of standard signature separator - sig_separator (#3276)
- Optimize folder_size() on Cyrus IMAP by using special folder annotation (#4894)
- Make optional hiding of folders with name starting with a dot - imap_skip_hidden_folders (#4870)
- Add option to enable HTML editor always, except when replying to plain text messages (#4352)
- Emoticons: Added option to switch on/off emoticons in compose editor (#2076)
- Emoticons: Added option to switch on/off emoticons in plain text messages
- Emoticons: All emoticons-related functionality is handled by the plugin now
- Installer: Add button to save generated config file in system temp directory (#3553)
- Remove common subject prefixes Re:, Re[x]:, Re-x: on reply (#4882)
- Added GSSAPI/Kerberos authentication plugin - krb_authentication
- Password: Allow temporarily disabling the plugin functionality with a notice
- Require Mbstring and OpenSSL extensions (#5166)
- Add --config and --type options to moduserprefs.sh script (#4651)
- Implemented memcache_debug and apc_debug options
- Installer: Remove system() function use (#4695)
- Password plugin: Added 'kpasswd' driver by Peter Allgeyer
- Add initdb.sh to create database from initial.sql script with prefix support (#4722)
- Plugin API: Added disabled_plugins an disabled_buttons options in html_editor hook
- Plugin API: Added html2text hook
- Plugin API: Added message_part_body hook
- Plugin API: Added message_ready hook
- Plugin API: Add special onload() method to execute plugin actions before startup (session and GUI initialization)
- Implemented UI element to jump to specified page of the messages list (#1677)
- Fix searching of contacts to allow remote images for known senders (#4886)
- Fix bug where clicking date column with 'arrival' sorting would switch to sorting by 'date' (#4690)
- Fix bug where message content could overlap attachments list in Larry skin (#4876)
- Fix so microseconds macro (u) in log_date_format works (#4855)
- Fix so unrecognized TNEF attachments are displayed on the list of attachments (#5138)
- Fix so database_attachments::cleanup() does not remove attachments from other sessions (#4907)
- Fix responses list update issue after response name change (#4917)
- Fix bug where message preview was unintentionally reset on check-recent action (#4921)
- Fix bug where HTML messages with invalid/excessive css styles couldn't be displayed (#4905)
- Fix redundant blank lines when using HTML and top posting (#4927)
- Fix redundant blank lines on start of text after html to text conversion (#4928)
- Fix HTML sanitizer to skip `<!-- node type X -->` in output (#4932)
- Fix invalid LDAP query in ACL user autocompletion (#4934)

## Release 1.1.3

- Fix closing of nested menus (#4854)
- Fix so E_DEPRECATED errors from PEAR libs are ignored by error_reporting change (#4770)
- Fix compatibility with PHP 5.3 in rcube_ldap class (#4842)
- Get rid of Mail_mimeDecode package dependency (#4836)
- Fix "Importing..." message does not hide on error (#4840)
- Fix Compose action in addressbook for results from multiple addressbooks (#4834)
- Fix bug where some messages in multi-folder search couldn't be viewed/printed/downloaded (#4843)
- Fix unintentional messages list page change on page switch in compose addressbook (#4844)
- Fix race-condition in saving user preferences and loading plugin config (#4845)
- Fix so plain text signature field uses monospace font (#4848)
- Fix so links with href == content aren't added to links list on html to text conversion (#4847)
- Fix handling of non-break spaces in html to text conversion (#4849)
- Fix self-reply detection issues (#4852)
- Fix multi-folder search result sorting by arrival date (#4858)
- Fix so *-request@ addresses in Sender: header are also ignored on reply-all (#4860)
- Update to TinyMCE 4.1.10 (#5164)
- Fix draft removal after a message is sent and storing sent message is disabled (#4869)
- Fix so imap folder attribute comparisons are case-insensitive (#4868)
- Fix bug where new messages weren't added to the list in search mode
- Fix wrong positioning of message list header on page scroll in Webkit browsers (#4646)
- Fix some javascript errors in rare situations (#4853)
- Fix error when using back button after sending an email (#4628)
- Fix removing signature when switching to identity with an empty sig in HTML mode (#4872)
- Disable links list generation on html-to-text conversion of identities or composed message (#4850)
- Fix "washing" of style elements wrapped into many lines
- Fix so input field (e.g. search box) does not loose focus on list load (#4862)
- Fix so css of one html part does not apply to other text parts on message display (#4887)
- Fix XSS issue in drag-n-drop file uploads [CVE-2015-8105] (#4900)
- Fix handling of plus character in mailto: links (#4891)
- Fix so adding CC/BCC recipients from the sidebar unhides compose form fields in Classic skin (#4874)
- Fix so gc.sh script removes also expired sessions from sql database (#4893)
- Fix support for Mozilla-based browsers, e.g. Pale Moon (#4895)
- Fix various issues with Turkish (and similar) locales (#4896)
- Fix so In-Reply-To header is set also for MDN receipts (#4897)
- Fix missing HTTP_X_FORWARDED_FOR address in generated Received header
- Fix issue where Content-Length of some attachments could be set to wrong value causing browser errors (#4877)

## Release 1.1.2

- Add new plugin hook 'identity_create_after' providing the ID of the inserted identity (#4807)
- Add option to place signature at bottom of the quoted text even in top-posting mode [sig_below]
- Fix handling of %-encoded entities in mailto: URLs (#4799)
- Fix zipped messages downloads after selecting all messages in a folder (#4797)
- Fix vpopmaild driver of password plugin
- Fix PHP warning: Non-static method PEAR::setErrorHandling() should not be called statically (#4798)
- Fix tables listing routine on mysql and postgres so it skips system or other database tables and views (#4796)
- Fix message list header in classic skin on window resize in Internet Explorer (#4732)
- Fix so text/calendar parts are listed as attachments even if not marked as such (#4795)
- Fix lack of signature separator for plain text signatures in html mode (#4802)
- Fix font artifact in Google Chrome on Windows (#4803)
- Fix bug where forced extwin page reload could exit from the extwin mode (#4801)
- Fix bug where some unrelated attachments in multipart/related message were not listed (#4805)
- Fix mouseup event handling when dragging a list record (#4808)
- Fix bug where preview_pane setting wasn't always saved into user preferences (#4809)
- Fix bug where messages count was not updated after message move/delete with skip_deleted=false (#4814)
- Fix security issue in contact photo handling (#4817)
- Fix possible memcache/apc cache data consistency issues (#4820)
- Fix bug where imap_conn_options were ignored in IMAP connection test (#4822)
- Fix bug where some files could have "executable" extension when stored in temp folder (#4815)
- Fix attached file path unsetting in database_attachments plugin (#4823)
- Fix issues when using moduserprefs.sh without --user argument (#4825)
- Fix potential info disclosure issue by protecting directory access (#4816)
- Fix blank image in html_signature when saving identity changes (#4833)
- Installer: Use openssl_random_pseudo_bytes() (if available) to generate des_key (#4827)
- Fix XSS vulnerability in _mbox argument handling (#4837)

## Release 1.1.1

- ACL: Allow other plugins to adjust the list of permissions and groups to edit
- Add possibility to print contact information (of a single contact)
- Add possibility to configure max_allowed_packet value for all database engines (#4772)
- Improved handling of storage errors after message is sent
- Update to TinyMCE 4.1.9
- Unified request* event arguments handling, added support for _unlock and _action parameters
- Security: Generate random hash for the per-user local storage prefix (#4768)
- Fix refreshing of drafts list when sending a message which was saved in meantime (#4745)
- Fix saving/sending emoticon images when assets_dir is set
- Fix PHP fatal error when visiting Vacation interface and there's no sieve script yet (#4778)
- Fix setting max packet size for DB caches and check packet size also in shared cache
- Fix needless security warning on BMP attachments display (#4771)
- Fix handling of some improper constructs in format=flowed text as per the RFC3676[4.5] (#4773)
- Fix performance of rcube_db_mysql::get_variable()
- Fix missing or not up-to-date CATEGORIES entry in vCard export (#4766)
- Fix fatal errors on systems without mbstring extension or mb_regex_encoding() function (#4769)
- Fix cursor position on reply below the quote in HTML mode (#4759)
- Fix so "over quota" errors are displayed also in message compose page
- Fix duplicate entries suppression in autocomplete result (#4776)
- Fix "Non-static method PEAR::isError() should not be called statically" errors (#4770)
- Fix parsing invalid HTML messages with BOM after `<!DOCTYPE>` (#4777)
- Fix duplicate entry on timezones list in rcube_config::timezone_name_from_abbr() (#4779)
- Fix so localized folder name is displayed in multi-folder search result (#4750)
- Fix javascript error after creating a folder which is a subfolder of another one (#4781)
- Fix bug where subject of sent/saved message was removed if mbstring wasn't installed (#4780)
- Fix missing vcard_attachment icon on messages list (#4783)
- Fix storing signatures with big images in MySQL database (#4785)
- Fix Opera browser detection in javascript (#4786)
- Fix so search filter, scope and fields are reset on folder change
- Fix rows count when messages search fails (#4760)
- Fix bug where spellchecking in HTML editor do not work after switching editor type more than once (#4789)
- Fix bug where TinyMCE area height was too small on slow network connection (#4788)
- Fix backtick character handling in sql queries (#4790)
- Fix redirect URL for attachments loaded in an iframe when behind a proxy (#4724)
- Fix menu container references to point to the actual `<ul>` element (#4791)
- Fix javascripts errors in IE8 - lack of Event.which, focusing a hidden element (#4793)

## Release 1.1.0

- Make SMTP error log more verbose - include server response and error code
- Fix download options menu (added by zipdownload plugin) in classic skin (#4740)
- Fix blocked.gif image usage with assets_dir set
- Fix bug where max_group_members was ignored when adding a new contact (#4733)
- Hide MDN and DSN options in compose if disabled by admin (#4735)
- Fix checks based on window.ActiveXObject in IE > 10
- Fix XSS issue in style attribute handling [CVE-2015-1433] (#4739)
- Fix bug where Drafts list wasn't updated on draft-save action in new window (#4737)
- Fix so "set as default" option is hidden if identities_level > 1 (#4738)
- Fix bug where search was reset after returning from compose visited for reply
- Fix javascript error in "IE 8.0/Tablet PC" browser (#4730)
- Fix bug where Reply-To address was ignored on reply to messages sent by self (#4742)
- Fix bug where empty fieldmap config entries caused empty results of ldap search (#4741)
- Fix bug where drafts list wasn't refreshed after draft message was sent from another window (#4745)
- Fix keyboard navigation and css in datepicker widget across many Firefox versions
- Fix false warning when opening attached text/plain files (#4748)
- Fix bug where signature could have been inserted twice after plain-to-html switch (#4746)
- Fix security issue in DBMail driver of password plugin (#4757)
- Enable FollowSymLinks option in .htaccess file which is required by rewrite rules (#4754)
- Fix so JSON.parse() errors on localStorage items are ignored (#4752)

## Release 1.1-rc

- Update jQuery to version 2.1.3
- Allow to override any config option through env variables
- Improve system security by using optional special URL with security token - use_secure_urls
- Allow to define separate server/path for image/js/css files - assets_url/assets_dir
- Sync vendor folder if exists in source package (#4700)
- Avoid useless reloading list when resetting search with active filter (#4654)
- Fix invalid folder selection if clicked while busy (#4709)
- Fix import of multiple contact email addresses from Outlook-csv format (#4714)
- Fix drag-n-drop to folders expanded while dragging (#4708)
- Fix import of multiple contact groups from Google-csv format (#4710)
- Fix import of contacts with multiple email addresses from Google-csv format (#4719)
- Fix bugs where CSRF attacks were still possible on some requests [CVE-2014-9587]
- Fix some rcube_utils::anytodatetime() corner cases with timezone mismatches (#4712)
- Improve move-to and contact-export button in classic skin (#4713)
- Fix wrong icon for download button in classic skin
- Fix bug where sent message was saved in Sent folder even if disabled by user (#4729)

## Release 1.1-beta

- Fix skin path handling in plugin context (#4111)
- Prevent memory exhaustion on image resizing with GD on Windows (#4580)
- Add plugin hook for database table name lookups as requested in #4538
- Added Oracle database support
- Support contacts import in GMail CSV format
- Added namespace filter in Folder Manager
- Added folder searching in Folder Manager
- Fix restoring draft messages from localStorage if editor mode differs (#4631)
- Added config option/user preference to disable saving messages in localStorage (#4606)
- Added config option 'imap_log_session' to enable Roundcube and IMAP session ID logging
- Added config option 'log_session_id' to control the length of the session identifier in logs
- Implemented 'storage_connected' API hook after successful IMAP login (#4638)
- Integrate Net_LDAP3 and rcube_ldap_generic classes
- Add option (disabled_actions) to disable UI elements/actions (#4478)
- Support password encryption using openssl extension (#4614)
- Create/rename groups in UI dialogs (#4592)
- Added 'contact_search_name' option to define autocompletion entry format
- Display quota information for current folder not INBOX only (#3442)
- Support images in HTML signatures (#3917)
- Display full quota information in popup (#2103, #2746)
- Mail compose: Selecting contact inserts recipient to previously focused input - to/cc/bcc accordingly (#4487)
- Close "no subject" prompt with Enter key (#4463)
- Password: Add option to force new users to change their password (#2963)
- Improve support for screen readers and assistive technology using WCAG 2.0 and WAI ARIA standards
- Enable basic keyboard navigation throughout the UI (#3333)
- Select/scroll to previously selected message when returning from message page (#4146)
- Display a warning if popup window was blocked (#4472)
- Remove (was: ...) from message subject on reply (#4359)
- Update to TinyMCE 4.1 (#4168)
- Enable autolink plugin in TinyMCE (#4029)
- Support image operations with Imagick extension (#4498)
- Support upload progress with session.upload_progress and PECL uploadprogress module (#3934)
- Make identity name field optional (#4435)
- Utility script to remove user records from the local database
- Plugin API: Added message_saved hook (#4503)
- Plugin API: Added imap_search_before hook
- Support messages import from zip archives
- Zipdownload: Added mbox format support (#2354)
- Drop support for IE6, move IE7/IE8 support to legacy_browser plugin
- Update to jQuery-2.1.1
- Search across multiple folders (#1676)
- Improve UI integration of ACL settings
- Drop support for PHP < 5.3.7
- Set In-Reply-To and References for forwarded messages (#4465)
- Removed redundant default_folders config option (#4500)
- Implemented IMAP SPECIAL-USE extension support [RFC6154] (#3326)
- Optimize some framed pages content for better performance (#4517)
- Improve text messages display and conversion to HTML (#4091)
- Don't remove links when html signature is converted to text (#4473)
- Fix page title when using search filter (#4636)
- Fix mbox files import
- Fix some character sets detection (#4694)
- Fix so attachment charset is set in headers of forward/draft message (#4676)
- Fix bug where wrong charset could be used for text attachment preview page (#4674)

## Release 1.0.5

- Fix wrong icon for download button in classic skin
- Fix checks based on window.ActiveXObject in IE > 10
- Fix XSS issue in style attribute handling (#4739)
- Fix bug where Drafts list wasn't updated on draft-save action in new window (#4737)
- Fix so "set as default" option is hidden if identities_level > 1 (#4738)
- Fix javascript error in "IE 8.0/Tablet PC" browser (#4730)
- Fix bug where empty fieldmap config entries caused empty results of ldap search (#4741)
- Fix bug where sent message was saved in Sent folder even if disabled by user (#4729)

## Release 1.0.4

- Disable TinyMCE contextmenu plugin as there are more cons than pros in using it (#4684)
- Fix bug where show_real_foldernames setting wasn't honored on compose page (#4705)
- Fix issue where Archive folder wasn't protected in Folder Manager (#4706)
- Fix compatibility with PHP 5.2. in rcube_imap_generic (#4682)
- Fix setting flags on servers with no PERMANENTFLAGS response (#4667)
- Fix regression in SHA password generation in ldap driver of password plugin (#4670)
- Fix displaying of HTML messages with absolutely positioned elements in Larry skin (#4672)
- Fix font style display issue in HTML messages with styled `<span>` elements (#4671)
- Fix download of attachments that are part of TNEF message (#4668)
- Fix handling of uuencoded messages if messages_cache is enabled (#4675)
- Fix handling of base64-encoded attachments with extra spaces (#4678)
- Fix handling of UNKNOWN-CTE response, try do decode content client-side (#4650)
- Fix bug where creating subfolders in shared folders wasn't possible without ACL extension (#4680)
- Fix reply scrolling issue with text mode and start message below the quote (#4681)
- Fix possible issues in skin/skin_path config handling (#4689)
- Fix lack of delimiter for recipient addresses in smtp_log (#4703)
- Fix generation of Blowfish-based password hashes (#4721)
- Fix bugs where CSRF attacks were still possible on some requests [CVE-2014-9587]

## Release 1.0.3

- Initialize HTML editor before restoring a message from localStorage (#4631)
- Add 'sig_max_lines' config option to default config file (#5162)
- Add config option to specify IMAP connection socket parameters - imap_conn_options (#4589)
- Add option to set default message list mode - default_list_mode (#3157)
- Enable contextmenu plugin for TinyMCE editor (#3062)
- Fix insert-signature command in external compose window if opened from inline compose screen (#4663)
- Fix some mime-type to extension mapping checks in Installer (#4610)
- Fix errors when using localStorage in Safari's private browsing mode (#4619)
- Fix bug where $Forwarded flag was being set even if server didn't support it (#4621)
- Fix various iCloud vCard issues, added fallback for external photos (#4617)
- Fix invalid Content-Type header when send_format_flowed=false (#4616)
- Fix errors when adding/updating contacts in active search (#4630)
- Fix incorrect thumbnail rotation with GD and exif orientation data (#4641)
- Fix contacts list update after adding/deleting/moving a contact (#4640, #4644)
- Fix handling of email addresses with quoted domain part (#4647)
- Fix comm_path update on task switch (#4648)
- Fix error in MSSQL update script 2013061000.sql (#4658)
- Fix validation of email addresses with IDNA domains (#4661)

## Release 1.0.2

- Fix storing unsaved drafts in localStorage (#4529)
- Add configurable LDAP_OPT_DEREF option (#4546)
- Fix so when switching editor mode original version of signature is used (#4032)
- Fix unintentional draft autosave request if autosave is disabled (#4550)
- Fix malformed References: header in send/saved mail (#4552)
- Fix handling unicode characters in links (#4555)
- Fix incorrect handling of HTML comments in messages sanitization code (#4558)
- Fix so current page is reset on list-mode change (#4561)
- Fix so responses menu hides on click in classic skin (#4566)
- Fix unintentional line-height style modification in HTML messages (#4567)
- Fix broken normalize_string(), add support for ISO-8859-2 (#4568)
- Support csv contacts import in German localization (#4570)
- Fix so message list and counters are updated when a message is opened in new window (#4569)
- Fix malformed recipient name when composing a message by clicking on mailto link (#4583)
- Fix list reload after sending message in another window (#4576)
- Fix so address format errors are ignored when saving a draft (#4594)
- Fix incorrect label translation in return receipt (#4598)
- Fix security issue in delete-response action - allow only ajax request
- Fix Delete button state after deleting identity/response (#4603)
- Fix bug where contacts with no email address were listed on compose addressbook (#4602)
- Fix images import from various vCard formats (#4604)
- Fix sorting messages by size on servers without SORT capability (#4608)

## Release 1.0.1

- Support 'error' and 'body_file' return attribs in 'message_before_send' hook (#4467)
- Apply user-specific replacements to group's base_dn property (#4512)
- Fix missing email address when importing contacts from outlook csv (#4535)
- Fix bug where "With attachment" option in search filter wasn't selected after return from mail view (#4508)
- Fix "washing" of unicode style attributes (#4510)
- Fix unintentional redirect from compose page in Webkit browsers (#4516)
- Fix messages index cache update under some conditions (e.g. proxy) (#4505)
- Fix lack of translation of special folders in some configurations (#4520)
- Fix XSS issue in plain text spellchecker (#4524)
- Fix invalid page title for some folders (1489804)
- Fix redundant alert message on over-size uploads (#4528)
- Fix next message display after removing a message (#4521)
- Fix missing Mail-Followup-To header in sent mail (#4534)
- Fix error when spell-checking an empty text (#4536)
- Avoid popupmenus being closed when scrollbar is clicked (#4537)
- Add proxy_whitelist configuration option (#4496)
- Fix identities_level=4 handling in new_user_dialog plugin (#4540)
- Fix various db_prefix issues (#4539)
- Fix too small length of users.preferences column data type on MySQL
- Fix redundant warning when switching from html to text in empty editor (#4530)
- Fix invalid host validation on login (#4541)
- Fix IMAP connection test in installer so it is aware of imap_auth_type (#4502)

## Release 1.0.0

- Added toolbar button to move message in message view
- Fix style of disabled protocol handler link on IE (#4460)
- Fix message import dialog when no file is selected (#4488)
- Fix opening compose screen in new window after saving as draft (#4479)
- Fix directories check in Installer on Windows (#4462)
- Fix issue when default_addressbook option is set to integer value (#4379)
- Fix Opera > 15 detection (#4455)
- Fix security issue in DomainFactory driver of Password plugin
- Fix invalid X-Draft-Info on forwarded message draft (#4464)
- Fix regression in handling of 'attachments' result in message_compose hook (#4474)
- Fix issue where msgexport.sh printed the message to STDOUT instead of a file (#4476)
- Fix fatal error in database_attachments plugin under some conditions (#4495)

## Release 1.0-rc

- Small CSS fix with message notice boxes in Larry skin (#4429)
- Include groups in contacts search on mail compose (#4186)
- Add mime-type mapping for .7z files (#4436)
- Invoke update scripts with php to circumvent execution restrictions (#4330)
- Fix drag & drop message/contact moving on touch device (#4395)
- Fix canned responses in HTML mode (#4446)
- Check/create default folders on every login not only the first (#4391)
- Update to jQuery-1.11.0 and jQuery-UI-1.9.2
- Support SMTP socket context options via new config option 'smtp_conn_options'
- Fix compatibility with PHP 5.2 in html.php file (#4438)
- Remove expand/collapse with plus/minus keys (on numeric keypad) (#4437)
- Fix issue where filesystem path was added to all-attachments (zip) file (#4433)
- Fix case-sensitivity of email addresses handling on compose (#1899)
- Don't alter Message-ID of a draft when sending (#4381)
- Fix issue where deprecated syntax for HTML lists was not handled properly (#3975)
- Display different icons when Trash folder is empty or full (#2108)
- Remember last position of more headers switch (#3660)
- Fix so message flags modified by another client are applied on the list on refresh (#1639)
- Fix broken text/* attachments when forwarding/editing a message (#4393)
- Improved minified files handling, added css minification (#3041)
- Fix handling of X-Forwarded-For header with multiple addresses (#4424)
- Fix border issue on folders list in classic skin (#4419)
- Implemented menu actions to copy/move messages, added folder-selector widget (#863)
- Fix security rules in .htaccess preventing access to base URL without the ending slash (#4422)
- Fix regression where only first new folder was placed in correct place on the list (#4418)
- Fix issue where children of selected and collapsed thread were skipped on various actions (#4410)
- Fix issue where groups were not deleted when "Replace entire addressbook" option on contacts import was used (#4388)
- Fix unreliable mimetype tests in Installer (#4408)
- Fix performance of listing writeable folders (#4406)

## Release 1.0-beta

- Fix handling of invalid closing tags in HTML messages (#4403)
- Set real content-type for file downloads (#4400)
- Update TinyMCE to version 3.5.10 (#4401)
- Fix keyboard navigation in list widgets (#4367)
- Allow plugins to grab the reference of opened windows (#4383)
- Larry skin: Improved status message display for better visibility (#4115)
- Fix Internet Explorer 11 detection (#4397)
- Fix date column width to fit the widest possible date format (#4354)
- Move certain user preference options to a collapsed "advanced" block (#4015)
- Add file type icons for PowerPoint and Open Office presentations (#4269)
- Fix operations on folders with trailing spaces in name (#4387)
- Improve identity selection based on From: header (#4360)
- Fix issue where mails with inline images of the same name contained only the first image multiple times (#4378)
- Use left/right arrow keys to collapse/expand thread and spacebar to select a row, change Ctrl key behavior (#4367)
- Fix an issue where using arrow keys to go up a list can result in selected message being under headers (#4375)
- Fix an issue where Home/End keys don't focus list row properly, don't scrollTo properly (#4370)
- Add an option to disable smart Reply-List behaviour - reply_all_mode (#3953)
- Fix an issue where pressing minus key on contacts list was hiding list records (#4368)
- Fix an issue where shift + arrow-up key wasn't selecting all messages in collapsed thread (#4371)
- Added icon for priority column in messages list header (#4275)
- New feature "Canned Responses" to save and recall boilerplate text snippets
- Fix HTML part detection when encapsulated inside multipart/signed (#4357)
- Add spellchecker backend for the After the Deadline service
- Replace markdown-style [1] link indexes in plain text email bodies
- Improved mailto: link arguments handling (#4351)
- Use DOMDocument LIBXML_PARSEHUGE and LIBXML_COMPACT options if possible (#4316)
- Support HTTP_HOST, SERVER_NAME and SERVER_ADDR values in include_host_config feature
- Make default font size for HTML messages configurable (request #118)
- Fix XSS issue in addressbook group name field [CVE-2013-5646] (#4337)
- After message is sent refresh messages list of replied message folder (#4282)
- Add option force specified domain in user login - username_domain_forced (#4290)
- Add option to import Vcards with group assignments
- Save groups membership in Vcard export (#3801)
- Workaround broken PHP function timezone_name_from_abbr (#4289)
- Make cached message size limit configurable - messages_cache_threshold (#4326)
- Log also failed logins to userlogins log
- Add temp_dir_ttl configuration option (#4318)
- Allow setting INBOX as Sent folder (#4264)
- Fix replacement variables in user-specific base_dn in some LDAP requests (#4299)
- Fix image scaling issues when image has only one dimension smaller than the limit (#4296)
- Fix issue where uploaded photo was lost when contact form did not validate (#4296)
- Move identity selection based on non-standard headers into (new) identity_select plugin (#3835)
- Fix downloading binary files with (wrong) text/* content-type (#4292)
- Respect HTTP_X_FORWARDED_FOR and HTTP_X_REAL_IP variables for session IP check
- Simplified configuration by merging it into one file + defaults (#3156)
- Make message list header stay on top when scrolling (#353)
- Add support for 'enchant' spellcheck engine
- Check filetype detection in installer and update script (#4252)
- Fix folder names truncation in Classic skin (#4265)
- Make possible to disable some (broken) IMAP extensions with imap_disable_caps option (#4245)
- Contacts drag-n-drop default action is to move contacts (#3962)
- Added possibility to choose to move or copy contacts from drag-n-drop menu (#3962)
- Fix Close link and remove About link on error pages (#4201)
- Improved/unified attachment preview screen, added print button
- Fix lack of space between searchfilter and quicksearchbar in Larry skin (#4233)
- Cache LDAP's user_specific search and use vlv for better performance (#4247)
- LDAP: auto-detect and use VLV indices for all search operations
- LDAP: additional group configuration options for  address books
- LDAP: separated address book implementation from a generic LDAP wrapper class
- Allow address books to browse a multi-level group hierarchy in the contacts list
- Fix session issues when local and database time differs (#2401)
- Fix thread cache synchronization/validation (#4150)
- Added feature to import messages to the currently selected folder
- Add option show_real_foldernames to disable localization of special folders
- Fix database cache expunge issues (#4229)
- Fix date format issues on MS SQL Server (#4078)
- Add imap_cache_ttl option to configure TTL of imap_cache
- Make LDAP cache engine configurable via ldap_cache and ldap_cache_ttl options
- Fix "duplicate entry" errors on inserts to imap cache tables (#4228)
- Improved handling of Reply-To/Bcc addresses of identity in compose form (#4142)
- Added user preference to open all popups as standard windows
- Implemented shared cache (rcube_cache_shared)
- Change Reply-All button label/title when mailing list is detected (#4092)
- Fix SMTP connection using IPv6 address in smtp_server option (#4147)
- Added attachment_reminder plugin
- Make PHP code eval() free, use create_function()
- Add option to display email address together with a name in mail preview (#3952)
- Support CSV import from Atmail (#4161)
- Add db_prefix configuration option in place of db_table_*/db_sequence_* options
- Make possible to use db_prefix for schema initialization in Installer (#4175)
- Fix updatedb.sh script so it recognizes also table prefix for external DDL files
- Fix parsing invalid date string (#4155)
- Add "with attachment" option to messages list filter (#1795)
- Call resize handler in intervals to prevent lags and double onresize calls in Chrome (#4137)
- Add rel="noreferrer" for links in displayed messages (#4976)
- Add ability to toggle between HTML and text while viewing a message (#3005)
- Remove "HTML message" from attachments list while viewing a message in text mode (#3005)
- Support IMAP MOVE extension [RFC 6851]
- Add attachment menu with Open and Download options (#4116)
- Display user-friendly message on IMAP "over quota" errors (#914)
- Extended archive plugin with user-configurable options to store messages into subfolders
- Fix export of selected contacts from search result (#4070)
- Feature to export only selected contacts from addressbook (by Phil Weir)

## Release 0.9.5

- Fix failing vCard import when email address field contains spaces (#4363)
- Fix default spell-check configuration after Google suspended their spell service
- Fix vulnerability in handling _session argument of utils/save-prefs [CVE-2013-6172] (#4362)
- Fix iframe onload for upload errors handling (#4361)
- Fix address matching in Return-Path header on identity selection (#4358)
- Fix text wrapping issue with long unwrappable lines (#4356)
- Fixed issues where HTML comments inside style tag would hang Internet Explorer
- Hide Delivery Status Notification option when smtp_server is unset (#4339)
- Display full attachment name using title attribute when name is too long to display (#4328)
- Fix attachment icon issue when rare font/language is used (#4334)
- Fix expanded thread root message styling after refreshing messages list (#4335)
- Fix issue where From address was removed from Cc and Bcc fields when editing a draft (#4327)
- Fix error_reporting directive check (#4331)
- Fix de_DE localization of "About" label in Help plugin (#4333)

## Release 0.9.4

- Make identities matching case insensitive (#1881)
- Fix issue where too big message data was stored in cache causing sql errors (#4325)
- Fix iframe scrollbars on webkit desktop browsers (#4319)
- Fix issue where legacy config was overridden by default config (#4305)
- Fix newmail_notifier issue where favicon wasn't changed back to default (#4324)
- Fix setting of Junk and NonJunk flags by markasjunk plugin (#4303)
- Fix lack of Reply-To address in header of forwarded message body (#4314)
- Fix bugs when invoking contact creation form when read-only addressbook is selected (#4313)
- Fix identity selection on reply (#4308)
- Fix so additional headers are added to all messages sent (#4302)
- Fix display issue after moving folder in Folder Manager (#4310)
- Fix handling of non-default date formats (#4311)
- Fix unquoted path in PREG expression on Windows (#4307)
- Fix wrong close tag in /template/mail.html (#4312)

## Release 0.9.3

- Fix setting refresh_interval to "Never" in Preferences (#4304)
- Fixed iframe scrolling on touch devices
- Optimized message list for touch devices
- Fix purge action in folder manager (#4300)
- Fix base URL resolving on attribute values with no quotes (#4297)
- Fix wrong handling of links with '|' character (#4298)
- Fix colorspace issue on image conversion using ImageMagick (#4294)
- Fix XSS vulnerability when editing a message "as new" or draft [CVE-2013-5645] (#4283)
- Fix XSS vulnerability when saving HTML signatures [CVE-2013-5645] (#4283)
- Fix rewrite rule in .htaccess (#4278)
- Fix detecting Turkish language in ISO-8859-9 encoding (#4284)
- Fix identity-selection using Return-Path headers (#4279)
- Fix parsing of links with ... in URL (#4251)
- Fix compose priority selector when opening in new window (#4286)
- Fix bug where signature wasn't changed on identity selection when editing a draft (#4272)
- Fix IMAP SETMETADATA parameters quoting (#4274)
- Fix "could not load message" error on valid empty message body (#4271)
- Fix handling of message/rfc822 attachments on message forward and edit (#4262)
- Fix parsing of square bracket characters in IMAP response strings (#4267)
- Don't clear References and in-Reply-To when a message is "edited as new" (#4263)
- Fix messages list sorting with THREAD=REFS
- Remove deprecated (in PHP 5.5) PREG /e modifier usage (#4239)
- Fix empty messages list when register_globals is enabled (#4232)
- Fix so valid and set date.timezone is not required by installer checks (#4242)
- Canonize boolean ini_get() results (#4249)
- Fix so install do not fail when one of DB driver checks fails but other drivers exist (#4240)
- Fix so exported vCard specifies encoding in v3-compatible format (#4244)

## Release 0.9.2

- Fix image thumbnails display in print mode (#4220)
- Fix height of message headers block (#4200)
- Fix timeout issue on drag&drop uploads (#4238)
- Fix default sorting of threaded list when THREAD=REFS isn't supported
- Fix list mode switch to 'List' after saving list settings in Larry skin (#4236)
- Fix error when there's no writeable addressbook source (#4235)
- Fix zipdownload plugin issue with filenames charset (#4231)
- Fix so non-inline images aren't skipped on forward (#4230)
- Fix "null" instead of empty string on messages list in IE10 (#4227)
- Fix legacy options handling
- Fix so bounces addresses in Sender headers are skipped on Reply-All (#4140)
- Fix bug where serialized strings were truncated in PDO::quote() (#4226)
- Fix displaying messages with invalid self-closing HTML tags (#4223)
- Fix PHP warning when responding to a message with many Return-Path headers (#4222)
- Fix unintentional compose window resize (#4206)
- Fix performance regression in text wrapping function (#4219)
- Fix connection to postgres db using unix socket (#4218)
- Fix handling of comma when adding contact from contacts widget (#4199)
- Fix bug where a message was opened in both preview pane and new window on double-click (#4212)
- Fix fatal error when xdebug.max_nesting_level was exceeded in rcube_washtml (#4202)
- Fix PHP warning in html_table::set_row_attribs() in PHP 5.4 (#4194)
- Fix invalid option selected in default_font selector when font is unset (#4204)
- Fix displaying contact with ID divisible by 100 in sql addressbook (#4211)
- Fix browser warnings on PDF plugin detection (#4209)
- Fix fatal error when parsing UUencoded messages (#4210)

## Release 0.9.1

- Better German labels for from/to to avoid conflicts with 'sender' (#4188)
- Fix problem where security warning was displayed for valid images with image/jpg type (#4196)
- Fix handling of invalid email addresses in headers (#4193)
- Fix IMAP connection issue with default_socket_timeout < 0 and imap_timeout < 0 (#4191)
- Fix various PHP code bugs found using static analysis (#4190)
- Fix backslash character handling on vCard import (#4189)
- Fix csv import from Thunderbird with French localization (#4170)
- Fix messages list focus issue in Opera and Webkit (#4169)
- Fix Reply-To header handling in Reply-All action (#4157)
- Fix so Sender: address is added to Cc: field on reply to all (#4140)
- Fix so addressbook_search_mode works also for group search (#4183)
- Fix removal of a contact from a group in LDAP addressbook (#4185)
- Include SQL query in the log on SQL error (#4172)
- Fix handling untagged responses in IMAP FETCH - "could not load message" error (#4180)
- Fix very small window size in Chrome (#4087)
- Fix list page reset when viewing a message in Larry skin (#4182)
- Fix min_refresh_interval handling on preferences save (#4179)
- Fix PDF support detection for Firefox PDF.js (#4113)
- Fix possible collision in generated thumbnail cache key (#4177)
- Fix exit code on bootstrap errors in CLI mode (#4160)
- Fix error handling in CLI mode, use STDERR and non-empty exit code (#5161)
- Fix error when using check_referer=true
- Fix incorrect handling of some specific links (#4171)
- Fix incorrect handling of leading spaces in text wrapping
- Fix unintentional messages list jumps on click in Internet Explorer (#4167)
- Fix list of required configuration options (#4166)
- Fix DB error when creating a new contact and a group is selected (#4164)
- Fix handling of deprecated boolean value of reply_mode option (#4165)

## Release 0.9.0

- Fix display of HTML entities in protected folder name (#4159)
- Set minimal permissions to temp files (#4131)
- Improve content check for embedded images without filename (#4151)
- Fix handling of invalid characters in message headers and output (#4153)
- Fix selecting collapsed rows on select-all (#4156)
- Avoid race-conditions with concurrent attachment uploads (#3739)
- Fix possible header duplicates when using additional headers (#4154)
- Fix session issues with use_https=true (#4125)
- Fix blockquote width in sent mail (#4152)
- Fix keyboard events on list widgets in Internet Explorer (#4148)

## Release 0.9-rc2

- Fix security issue in save-pref command
- Remove sig_above configuration option, use reply_mode only (#4135)
- Refresh current folder in opener window after draft save or message sent (#4132)
- Fix saving draft just after entering compose window (#4141)
- Fix javascript error in IE9 when loading form with placeholders into an iframe (#4138)
- Fix handling of some conditional comment tags in HTML message (#4136)
- Fix so forward as attachment works if additional attachment is added by message_compose hook (#4134)
- Better handling of session errors in ajax requests (#4105)
- Fix HTML part detection for some specific message structures (#4130)
- Don't show fake address - phishing prevention (#4120)
- Fix forward as attachment bug with editormode != 1 (#4129)
- Fix LIMIT/OFFSET queries handling on MS SQL Server (#4123)
- Fix so task name can really contain all from a-z0-9_- characters (#4095)
- Fix javascript errors when working in a page opened with target="_blank"
- Mention SQLite database format change in UPGRADING file (#4122)
- Increase maxlength to 254 chars for email input fields in addressbook (#4126)
- Fix thumbnail size when GD extension is used for image resize (#4124)
- Display notice that message is encrypted also for application/pkcs7-mime messages (#3815)

## Release 0.9-rc

- Fix plain text spellchecker incorrect highlighting in non-ASCII text (#4114)
- Add workaround for invalid message charset detection by IMAP servers (#4112)
- Fix NUL characters in content-type of ms-tnef attachment (#4108)
- Fix regression in handling LDAP contact identifiers (#4104)
- Updated translations from Transifex
- Fix buggy error template in a frame (#4092)
- Add addressbook widget on compose page in classic skin
- Add search box to compose address book widget (#3710)
- Fix login in case when default_host is an array with one element (#4085)
- Use LDAP fallback hosts on connect + bind instead of ldap_connect() only.
- Add config option for LDAP bind timeout (sets LDAP_OPT_NETWORK_TIMEOUT option)
- Submit Addressbook advanced search form with Enter key (#3843)
- Also block remote images in HTML part view (#4013)
- Improved database schema upgrade procedure, added updatedb.sh script
- Force autocommit mode in mysql database driver (#4068)

## Release 0.9-beta

- Fix searching by date in address book (#4058)
- Improve charset detection by prioritizing charset according to user language (#2032)
- Fix handling of escaped separator in vCard file (#4064)
- Add option to use envelope From address for MDN responses (#4052)
- Add possibility to search in message body only (#3977)
- Support "multipart/relative" as an alias for "multipart/related" type (#4057)
- Display PGP/MIME signature attachments as "Digital Signature" (#3845)
- Workaround UW-IMAP bug where hierarchy separator is added to the shared folder name (#4051)
- Fix version comparisons with -stable suffix (#4050)
- Add unsupported alternative parts to attachments list (#4046)
- Add Compose button on message view page (#3959)
- Display 'Sender' header in message preview
- Plugin API: Added message_before_send hook
- Fix contact copy/add-to-group operations on search result (#4042)
- Use matching identity in MDN response (#4043)
- Fix handling of signatures on draft edit (#3996)
- Fix so compacting of non-empty folder is possible also when messages list is empty (#4039)
- Allow forwarding of multiple emails (#2941)
- Fix big memory consumption of DB layer (#4037)
- Fix broken message/part bodies when FETCH response contains more untagged lines (#4020)
- Fix empty email on identities list after identity update (#4018)
- Add new identities_level: (4) one identity with possibility to edit only signature
- Use Delivered-To and Envelope-To headers for identity selection (#4024, #3835)
- Fix XSS vulnerability using Flash files (#4014)
- Always save drafts with format=flowed in order to keep original line wraps (#3997)
- Select default_addressbook on the list in Address Book (#3624)
- Fix so mobile phone has TYPE=CELL in exported vCard (#4004)
- Support contacts import from CSV file (#2605)
- Improved keep-alive action. Now the interval is based on session_lifetime (#3799)
- Added cross-task 'refresh' request for system state updates (#3799)
- Renamed config options: keep_alive to refresh_interval, min_keep_alive to min_refresh_interval
- Fix handling of text/enriched content on message reply/forward/edit
- Option to display attached images as thumbnails below message body
- Upgraded to jQuery 1.8.3 and jQuery UI 1.9.1
- Add config option to automatically generate LDAP attributes for new entries
- Add user settings to open message view and compose form in new windows (#1886)
- Better client-side timezone detection using the jsTimezoneDetect library (#3947)
- Add option to disable saving sent mail in Sent folder - no_save_sent_messages (#3923)
- Fix handling dont_override with message_sort_col and message_sort_order settings (#3970)
- Fix handling of URLs with asterisk characters (#3969)
- Remove automatic to-lowercase conversion of usernames (#3941)
- Plugin API: Add 'email_list' argument for identities data in user_create hook
- Integrated zipdownload plugin to download all attachments (#617)
- Fix HTML special characters handling in message list/header display (#3812)
- List related text/html part as attachment in plain text mode (#3918)
- Use IMAP BINARY (RFC3516) extension to fetch message/part bodies
- Fix folder creation under public namespace root (#3910)
- Fix so "Edit as new" on draft creates a new message (#3924)
- Fix invalid error message on deleting mail from read only folder (#3929)
- Replace data URIs of images (pasted in HTML editor) with inline attachments (#3795)
- Remove (too big) min-width on mail screen
- Added template object 'frame'
- Add option to enable HTML editor on forwarding (#3807)
- Add option to not include original message on reply, rename option top_posting to reply_mode (#1615)
- Added session_path config option and unified cookies settings in javascript
- Added "Undeleted" option to messages list filter
- Rewritten test scripts for PHPUnit
- Add new DB abstraction layer based on PHP PDO, supporting SQLite3 (#3668)
- Removed PEAR::MDB2 package
- Removed users.alias column, added option ('user_aliases')
  to use email address from identities as username (#3851)
- Removed redundant cache.cache_id column (#3817)
- Fix order of attachments in sent mail (#3740)
- Fix Shift + delete button does not permanently delete messages (#3598)
- Add Content-Length for attachments where possible (#1880)
- Fix attachment sizes in message print page and attachment preview page (#3805)
- Add mail attachments using drag & drop on HTML5 enabled browsers
- Add workaround for invalid BODYSTRUCTURE response - parse message with Mail_mimeDecode package (#1966)
- Display Tiff as Jpeg in browsers without Tiff support (#3757)
- Don't display Pdf/Tiff/Flash attachments inline without browser support (#3757, #3394)
- Add is_escaped attribute for html_select and html_textarea (#3782)
- Fix issue where draft auto-save wasn't executed after some inactivity time
- Add vCard import from multiple files at once (#3458)
- Roundcube Framework:
    Add possibility to replace IMAP driver with custom class
    Add IMAP auto-connection feature, improving performance with caching enabled
    Replace imap_init hook with storage_init (with additional 'driver' argument)
    Improved performance by caching IMAP server's capabilities in session
    Unified global functions naming (rcube_ prefix)
    Better classes separation
    Framework files moved to lib/Roundcube

## Release 0.8.5

- Fix #countcontrols issue in IE<=8 when text is very long (#4060)
- Fix unwanted horizontal scrollbar in message preview header (#4044)
- Add workaround for IE<=8 bug where Content-Disposition:inline was ignored (#4028)
- Fix XSS vulnerability in vbscript: and data:text links handling [CVE-2012-6121] (#4033)
- Fix absolute positioning in HTML messages (#4007)
- Fix cache (in)validation after setting \Deleted flag
- Fix keyboard events on messages list in opera browser (#4011)
- Fix selection of collapsed thread rows (#3978)
- Fix wrapping of quoted text with format=flowed (#3561)

## Release 0.8.4

- Fix regression where unintentional page reload was done after request abort (#3999)
- Fix XSS vulnerability in handling of text/enriched messages (#4000)
- Fix handling of 'media' attribute on linked css (#3989)
- Fix excessive LFs at the end of composed message with top_posting=true (#3995)
- Fix bug where leading blanks were stripped from quoted lines (#3994)

## Release 0.8.3

- Fix AREA links handling (#3992)
- Fix possible HTTP DoS on error in keep-alive requests (#3983)
- Fix compatibility with MDB2 2.5.0b4 (#3982)
- Fix a bug where saving a message in INBOX wasn't possible
- Fix HTML part detection in messages with attachments (#3976)
- Fix bug where wrong words were highlighted on spell-before-send check
- Fix scrolling quirk in email preview frame using Opera 12 (#3973)
- Fix displaying of multipart/alternative messages with empty parts (#3961)
- Fix threaded list sorting on PHP < 5.2.9 (#3960)
- Fix Warning: htmlspecialchars(): charset `RCMAIL_CHARSET` not supported warning in Installer (#3958)

## Release 0.8.2

- Fix XSS vulnerability from HTTP User-Agent header (#3954)
- Force fonts in compose fields to be all the same (#3926)
- Fix handling vCard entries with TEL;TYPE=CELL (#3949)
- Fix error where session wasn't updated after folder rename/delete (#3928)
- Fix PLAIN authentication for some IMAP servers (#3916)
- Fix encoding vCard file when contains PHOTO;ENCODING=b (#3922)
- Fix focus issue in IE when selecting message row (#3881)
- Add full headers view in message preview window (#3823)
- Fix message display page issues - unified with message preview (#3856, #3895)
- Fix displaying all headers when they contain malformed characters (#3911)
- Fix decoding of HTML messages with UTF-16 charset specified (#3902)
- Fix quota capability detection so it can be overwritten by a plugin (#3903)
- Fix identity selection on reply (#3516)
- Fix Larry's messages list filter in IE (#3890)
- Fix more IE issues by disabling Compat. mode with X-UA-Compatible meta tag (#3886)
- Fix setting locales under Solaris - use additional .UTF-8 suffix (#3887)
- Fix email address validation for addresses with IP address in domain part
- Fix Larry skin issues in IE7 compat. mode (#3879)
- Fix so subscribed non-existing/non-accessible shared folder can be unsubscribed

## Release 0.8.1

- Fix bug where domain name was converted to lower-case even with login_lc=false (#3859)
- Fix lower-casing email address on replies (#3863)
- Fix line separator in exported messages (#3866)
- Fix XSS issue where plain signatures wasn't secured in HTML mode [CVE-2012-4668] (#3875)
- Fix XSS issue where href="javascript:" wasn't secured [CVE-2012-3508] (#3875)
- Fix impossible to create message with empty plain text part (#3873)
- Fix stripped apostrophes when replying in plain text to HTML message (#3869)
- Fix inactive Save search option after advanced search (#3870)
- Fix Remove from group option is active for contact search result (#3871)
- Disable autocapitalization in login form on iPad/iPhone (#3872)
- Fix focus on the list when list row is clicked (#3865)
- Added separate From and To columns apart from smart From/To column (#2970)
- Fix fallback to Larry skin when configured skin isn't available (#3857)
- Fix (workaround) delete operations with some versions of memcache (#3858)
- Fix (disable) request validation for spell and spell_html actions

## Release 0.8.0

- Don't show product version on login screen (can be enabled by config)
- Renamed old default skin to 'classic'. Larry is the new default skin.
- Support connections to memcached socket file (#3848)
- Enable TinyMCE inlinepopups plugin
- Update to TinyMCE 3.5.6
- Correctly escape localized labels in javascript variable (#3842)
- Update Net_SMTP/Auth_SASL packages to fix Digest-MD5/Cram-MD5 authentication (#3846)
- Don't add attachments content into reply/forward/draft message body (#3837)
- Fix 'no connection' errors on page unloads (#3832)
- Plugin API: Add 'unauthenticated' hook (#3545)
- Show explicit error message when provided hostname is invalid (#3834)
- Fix wrong compose screen elements focus in IE9 (#3826)
- Fix fatal error when date.timezone isn't set (#3831)
- Update to TinyMCE 3.5.4.1
- Better icons with distinct shapes for priority columns (#3706)
- Show dedicated icon for multipart/report messages (#3813)
- Properly hide text of icon links/buttons (#3820)
- Fix handling of unitless CSS size values in HTML message (#3821)
- Fix removing contact photo using LDAP addressbook (#3737)
- Fix storing X-ANNIVERSARY date in vCard format (#3816)
- Update to Mail_Mime-1.8.5 (#3810)
- Fix XSS vulnerability in message subject handling using Larry skin [CVE-2012-3507] (#3809)
- Fix handling of links with various URI schemes e.g. "skype:" (#3521)
- Fix handling of links inside PRE elements on html to text conversion
- Fix indexing of links on html to text conversion
- Decode header value in rcube_mime::get() by default (#3803)
- Fix errors with enabled PHP magic_quotes_sybase option (#3798)
- Fix SQL query for contacts listing on MS SQL Server (#3797)
- Fix window.resize handler on IE8 and Opera (#3758)
- Don't let error message popups cover the login form (#3794)
- Update to TinyMCE 3.5.2
- Don't show errors when moving contacts into groups they are already in (#3788)
- Make folders with unread messages in subfolders bold again (#2892)
- Abbreviate long attachment file names with ellipsis (#3793)
- Fix html2text conversion of strong|b|a|th|h tags when used in upper case
- Add listcontrols template container in Larry skin (#3792)
- Fix host autoselection when default_host is an array (#3790)
- Move messages forwarding mode setting into Preferences
- Fix HTML entities handling in HTML editor (#3780)
- Fix listing shared folders on Courier IMAP (#3767)

## Release 0.8-rc

- Added new translations in Belarusian, Interlingua and Malayalam
- Flipped compose options arrow (#3772)
- Fix handling of large uuencode attachments (#3771)
- Fix handling of "usemap" attribute (#3770)
- Fix handling of some HTML tags e.g. IMG (#3769)
- Use similar language as a fallback for plugin localization (#3726)
- Fix issue where signature wasn't re-added on draft compose (#3659)
- Update to TinyMCE 3.5 (#3762)
- Fixed multi-threaded autocompletion when number of threads > number of sources
- Allow to configure the number of values allowed for each LDAP attribute
- Support for serialized LDAP address values (usually delimited with a $)
- Less restrictive session auth checks, repeat keep-alive requests on failure (#3755)
- Fix redirect to mail/compose on re-login (#3585)
- Add IE8 hack for messages list issue (#3317)
- Fix handling errors on draft auto-save
- Fix importing vCard photo with ENCODING param specified (#3746)
- Support multiple name/email pairs for Bcc and Reply-To identity settings (#3752)
- Set flexible width to login form fields (#3735)
- Fix re-draw bug on list columns change in IE8 (#3318)
- Allow mass-removal of addresses from a group (#3259)
- Fix removing all contacts on import to LDAP addressbook
- Fix so "Back" from compose/show doesn't reset search request (#3594)
- Add option to delete messages instead of moving to Trash when in Junk folder (#2805)
- Fix invisible cursor when replying to a html message (#3100)
- Reset IP stored in session when destroying session data (#3485)
- Fix bug where memory_limit = -1 wasn't handled properly
- Support LDAP RFC2256's country object class read/write (#3535)
- Upgraded to jQuery 1.7.2
- Image resize with GD extension (#3712)
- Fix lack of warning when switching task in compose window (#3725)
- Fix bug where it wasn't possible to enter ( or & characters in autocomplete fields
- Request all needed fields from address book backends (#3721)
- Unified (single) spellchecker button
- Scroll long lists on drag&drop (#2249)
- Copy all skins in installto script (#3705)

## Release 0.8-beta

- Upgraded to jQuery 1.7.1 (#3673) and jQuery UI 1.8.18
- Add Russian to the spellchecker languages list (#3542)
- Remember custom skin selection after logout (#3688)
- Make sure About tab is always the last tab (#3609)
- Fix issue with folder creation under INBOX. namespace (#3683)
- Added mailto: protocol handler registration link in User Preferences (#2729)
- Handle identity details box with an iframe (#3066)
- Fix issue where some text from original message was missing on reply (#3675)
- Fix autoselect_host() for login (#3639)
- Changed license to GNU GPLv3+ with exceptions for skins & plugins
- Added address book widget on compose screen
- Use proper timezones from PHP's internal timezonedb (#1973)
- Add separate pagesize setting for mail messages and contacts (#3617)
- Deprecate $DB, $USER, $IMAP global variables, Use $RCMAIL instead
- Add option to set default font for HTML message (#894)
- Fix issues with big memory allocation of IMAP results
- Prevent from memory_limit exceeding when trying to parse big messages bodies (#3164)
- Add possibility to add SASL mechanisms for SMTP in smtp_connect hook (#3399)
- Mark (with different color) folders with recent messages (#2479)
- Added About tab in Settings
- TinyMCE updated to 3.4.6

## Release 0.7.2

- Fix encoding of attachment with comma in name (#3717)
- Fix handling of % character in IMAP protocol (#3711)
- Fix duplicate names handling in addressbook searches (#3704)
- Fix displaying of HTML messages from Disqus (#3702)
- Disable E_STRICT warnings on PHP 5.4
- Prevent from folder selection on virtual folder collapsing (#3681)
- Fix automatic unsubscribe of non-existent folders
- Fix double-quotes handling in recipient names
- User configurable setting how to display contact names in list
- Make contacts list sorting configurable for the admin/user
- Fix parse errors in DDL files for MS SQL Server
- Revert SORT=DISPLAY support, removed by mistake (#3664)
- Add lost translation label in de_DE (#3654)
- Fix drafts update issues when edited from preview pane (#3653)
- Fix wrong variable name in rcube_ldap.php (#3643)
- Make mime type detection based on filename extension to be case-insensitive
- Fix failure on MySQL database upgrade from 0.7 - text column can't have default value (#3642)


## Release 0.7.1

- Fix bug in handling of base href and inline content (#3634)
- Fix SQL Error when saving a contact with many email addresses (#3630)
- Fix strict email address searching if contact has more than one address
- Remove duplicated 'organization' label (#3631)
- Fix so editor selector is hidden when 'htmleditor' is listed in 'dont_override'
- Fix wrong (long) label usage (#3627)
- Fix handling of INBOX's subfolders in special folders config (#3623)
- Add ifModule statement for setting Options -Indexes in .htaccess file (#3620)
- Fix crashes with eAccelerator (#3608)
- Fix searching on IMAP servers without CHARSET specifier support (#3619)
- Fix expanding folders during drag&drop (#3611)
- Fix wrong postgres sequence name in upgrade from 0.6
- Fix broken CREATE INDEX queries in SQLite DDL files (#3607)

## Release 0.7

- Make Roundcube render the Email Standards Project Acid Test correctly
- Replace prompt() with jQuery UI dialog (#1603)
- Fix navigation in messages search results
- Improved handling of some malformed values encoded with quoted-printable (#3590)
- Add possibility to do LDAP bind before searching for bind DN
- Fix handling of empty `<U>` tags in HTML messages (#3584)
- Add content filter for embedded attachments to protect from XSS on IE [CVE-2012-1253] (#3372)
- Use strpos() instead of strstr() when possible (#3581)
- Fix handling HTML entities when converting HTML to text (#3582)
- Fix fit_string_to_size() renders browser and ui unresponsive (#3577)
- Fix handling of invalid characters in request (#3536)
- Fix merging some configuration options in update.sh script (#2181)
- Fix so TEXT key will remove all HEADER keys in IMAP SEARCH (#3578)
- Fix handling contact photo url with https:// prefix (#3575)
- Fix possible infinite redirect on attachment preview (#3572)
- Improved clickjacking protection for browsers which don't support X-Frame-Options headers
- Fixed bug where similar folder names were highlighted wrong (#3345)
- Fixed bug in handling link with '!' character in it (#3569)
- Fixed bug where session ID's length was limited to 40 characters (#3570)
- TinyMCE security issue: removed moxieplayer (embedding flv and mp4 is not supported anymore)

## Release 0.7-beta

- Fix handling of HTML form elements in messages (#1604)
- Fix regression in setting recipient to self when replying to a Sent message (#3101)
- Fix listing of folders in hidden namespaces (#2895)
- Don't consider \Noselect flag when building folders tree (#3448)
- Fix sorting autocomplete results (#3504)
- Add option to set session name (#2630)
- Add option to skip alternative email addresses in autocompletion
- Fix inconsistent behaviour of Compose button in Drafts folder, add Edit button for drafts
- Fix problem with parsing HTML message body with non-unicode characters (#3312)
- Add option to define matching method for addressbook search (#2720, #3378)
- Make email recipients separator configurable
- Fix so folders with \Noinferiors attribute aren't listed in parent selector
- Fix handling of curly brackets in URLs (#3555)
- Fix handling of dates (birthday/anniversary) in contact data (#3552)
- Fix error on opening searched LDAP contact (#3550)
- Fix redundant line break in flowed format (#3551)
- Fix IDN address validation issue (#3544)
- Fix JS error when dst_active checkbox doesn't exist (#3540)
- Autocomplete LDAP records when adding contacts from mail (#3498)
- Plugin API: added 'ready' hook (#3492)
- Ignore DSN request when it isn't supported by SMTP server (#3300)
- Make sure LDAP name fields aren't arrays (#3523)
- Fixed imap test to non-default port when using ssl (#3532)
- Force all files to be overwritten when updating (#3531)
- Fix issue where it wasn't possible to change list view mode in folder manager for INBOX (#3522)
- Fix namespace handling in special folders settings (#3527)
- Disable time limit for CLI scripts (#3524)
- Fix misleading display when changing editor type (#3519)
- Add loading indicator on contact delete
- Fix bug where after delete message rows can be added to the list of another folder (#3263)
- Add notice on autocompletion that not all records were displayed
- Add option 'searchonly' for LDAP address books
- Add Priority filter to the messages list
- Cache synchronization using QRESYNC/CONDSTORE
- Trigger 'new_messages' hook for all checked folders (#3503)
- Make date/time format user configurable; drop 'date_today' config option
- Fix setting title for truncated subject in IE (#3141)
- Fix displaying multipart/alternative messages with only one part (#3400)
- Rewritten messages caching:
  Indexes are stored in a separate table, so there's no need to store all messages in a folder
  Added threads data caching
  Flags are stored separately, so flag change doesn't cause DELETE+INSERT, just UPDATE
- Improved FETCH response handling
- Improvements in response tokenization method
- Use 'From' and 'To' labels instead of 'Sender' and 'Recipient'
- Fix username case-insensitivity issue in MySQL (#3462)
- Addressbook Saved Searches
- Added spellchecker exceptions dictionary (shared or per-user)
- Added possibility to ignore words containing caps, numbers, symbols (spellcheck_ignore_* options)
- Added 'priority' column on messages list (#2884)
- Localize forwarded message header (#3487)

## Release 0.6

- Fix bug where the last identity is used on reply (#3516)
- Fix locked folder rename option on servers supporting RFC2086 only (#3508)
- Fix session race conditions when composing new messages
- Fix encoding of LDAP contacts identifiers (#3501)
- jQuery 1.6.4
- Fix handling of binary attachments encoded with quoted-printable (#3494)
- Fix text-overflow:ellipsis issues on messages list in FF7 and Webkit (#3490)
- Fix handling of links with IP address
- Fix compacting folder resets message list filter (#3499)

## Release 0.6-rc

- Send X-Frame-Options headers to protect from clickjacking (#3079)
- Fallback to mail_domain in LDAP variable replacements; added 'host' to 'user_create' hook arguments (#3464)
- Fixed wrong vCard type parameter mobile (#3496)
- Fixed vCard WORKFAX issue (#3476)
- Add vCard's Profile URL support (#3491)
- jQuery 1.6.3
- Fix imap_cache setting to values other than 'db' (#3489)
- Fix handling of attachments inside message/rfc822 parts (#3466)
- Make list of mimetypes that open in preview window configurable (#3175)
- Added plugin hook 'message_part_get' for attachment downloads
- Added unique connection identifier to IMAP debug messages
- Fix image type check for contact photo uploads

## Release 0.6-beta

- Fixed selecting identity on reply/forward (#3434)
- Add option to hide selected LDAP addressbook on the list
- Add client-side checking of uploaded files size
- Add newlines between organization, department, jobtitle (#3468)
- Recalculate date when replying to a message and localize the cite header (#3212)
- Fix handling of email addresses with quoted local part (#3401)
- Fix EOL character in vCard exports (#3357)
- Added optional "multithreading" autocomplete feature
- Plugin API: Added 'config_get' hook
- Fixed new_user_identity plugin to work with updated rcube_ldap class (#3443)
- Plugin API: added folder_delete and folder_rename hooks
- Added possibility to undo last contact delete operation
- Fix sorting of contact groups after group create (#3258)
- Add optional textual upload progress indicator (#2330)
- Fix parsing URLs containing commas (#3425)
- Added vertical splitter for books/groups list in addressbook (#3389)
- Improved namespace roots handling in folder manager
- Added searching in all addressbook sources
- Added addressbook source selection in contacts import
- Implement LDAPv3 Virtual List View (VLV) for paged results listing
- Use 'address_template' config option when adding a new address block (#3406)
- Added addressbook advanced search
- Add popup with basic fields selection for addressbook search
- Case-insensitive matching in autocompletion (#3398)
- Added option to force spellchecking before sending a message (#1862)
- Fix handling of "<" character in contact data, search fields and folder names (#3349)
- Fix saving "<" character in identity name and organization fields (#3349)
- Added option to specify to which address book add new contacts
- Added plugin hook for keep-alive requests
- Store user preferences in session when write-master is not available and session is stored in memcache, write them later
- Improve performance of folder manager operations
- Fix default_port option handling in Installer when config.inc.php file exists (#3390)
- Removed option focus_on_new_message, added newmail_notifier plugin
- Added general rcube_cache class with Memcache and APC support
- Improved caching performance by skipping writes of unchanged data
- Option enable_caching replaced by imap_cache and messages_cache options
- Fix WORKFAX saving in address book (#3380)
- Add forward-as-attachment feature
- jQuery-1.6.2 (#5158, #3154)
- Improve display name composition when saving contacts (#3153)
- Fix problems with subfolders of INBOX folder on some IMAP servers (#3247)
- Fix handling of folders that doesn't belong to any namespace (#3184)
- Enable multiselection for attachments uploading in capable browsers (#2266)
- Add possibility to change HTML editor configuration by skin
- Fix a bug where selecting too many contacts would produce too large URI request (#3369)
- Improve performance by including files with absolute path (#3337)
- Move folder name truncation to client/skin (#1822)
- Added plugin hook for request token creation
- Replace LDAP vars in group queries (#3329)
- Fix vcard folding with unicode characters (#3353)
- Keep all submitted data if contact form validation fails (#3350)
- Handle unicode strings in rcube_addressbook::normalize_string() (#3351)
- Fix handling of debug_level=4 in ajax requests (#3327)
- Enable TinyMCE's contextmenu (#3062)
- Allow multiple concurrent compose sessions
- New config option for custom logo
- Allow skins to define/override texts with `<roundcube:label />`
- Add simple ACL rights/namespace handling in folder manager
- Force IE to send referers (#3306)
- Better display of vcard import results (#1861)
- Improved vcard import
- Interactive update script with improved DB schema check
- Fix problem with contactgroupmembers table creation on MySQL 4.x, add index on contact_id column
- Add LDAP SASL bind and proxy authentication (#2810)
- Replying to a sent message puts the old recipient as the new recipient (#3101)
- Fulltext search over (almost) all data for contacts
- Extend address book with rich contact information

## Release 0.5.4

- Fix XSS vulnerability in UI messages [CVE-2011-2937] (#3469)

## Release 0.5.3

- Fix identities "reply-to" and "bcc" fields have a bogus value when left empty (#3405)
- Fix issue which cases IMAP disconnection when encrypt() method was used (#3374)
- Fix some CSS issues in Settings for Internet Explorer
- Fixed handling of folder with name "0" in folder selector
- Fix bug where messages were deleted instead moved to trash folder after Shift key was used (#3376)
- Fix relative URLs handling according to a `<base>` in HTML (#3368)
- Fix handling of top-level domains with more than 5 chars or unicode chars (#3366)
- Fix usage of non-standard HTTP error codes (#3297)
- Fix PHP warning on mistaken in_array() usage (#3375)

## Release 0.5.2

- TinyMCE 3.4.2 now compatible with IE9
- PEAR::Net_SMTP 1.5.2, fixed timeout issue (#3332)
- Fix bug where template name without plugin prefix was used in render_page hook
- Support 'abort' and 'result' response in 'preferences_save' hook, add error handling
- Fix bug where some content would cause hang on html2text conversion (#3348)
- Improve space-stuffing handling in format=flowed messages (#3346)
- Fix bug where some dates would produce SQL error in MySQL (#3342)
- Added workaround for some IMAP server with broken STATUS response (#3344)
- Fix bug where default_charset was not used for text messages (#3328)
- Stateless request tokens. No keep-alive necessary on login page (#3325)
- Force names of unique constraints in PostgreSQL DDL
- Add code for prevention from IMAP connection hangs when server closes socket unexpectedly
- Remove redundant DELETE query (for old session deletion) on login
- Get around unreliable rand() and mt_rand() in session ID generation (#2516)
- Fix some emails are not shown using Cyrus IMAP (#3316)
- Fix handling of mime-encoded words with non-integral number of octets in a word (#3301)
- Fix parsing links with non-printable characters inside (#3305)
- Fixed de_CH Localization bugs (#3279)
- Add variable for 'Today' label in date_today option (#2394)
- Fix dont_override setting does not override existing user preferences (#3205)
- Use only one from IMAP authentication methods to prevent login delays (1487784)
- Support strftime format in date_today option
- Fix SQL query in rcube_user::query() so it uses index on MySQL again
- Removed redundant `</form>` tags from contact add/edit pages
- Fix CSS error in contact details screen on IE7 (#3281)

## Release 0.5.1

- Fix handling of attachments with invalid content type (#3275)
- Add workaround for DBMail's bug http://www.dbmail.org/mantis/view.php?id=881 (#3274)
- Use IMAP's ID extension (RFC2971) to print more info into debug log
- Security: add optional referer check to prevent CSRF in GET requests
- Fix email_dns_check setting not used for identities/contacts (#3251)
- Fix ICANN example addresses doesn't validate (#3253)
- Security: protect login form submission from CSRF [CVE-2011-1491]
- Security: prevent from relaying malicious requests through modcss.inc [CVE-2011-1492]
- Fix handling of non-image attachments in multipart/related messages (#3261)
- Fix IDNA support when IDN/INTL modules are in use (#3253)
- Fix handling of invalid HTML comments in messages (#3269)
- Fix parsing FETCH response for very long headers (#3264)
- Fix add/remove columns in message list when message_sort_order isn't set (#3262)
- Check mime headers before attempt to parse them (#3256)
- Quote header values in show_additional_headers plugin (#3255)
- Fix settings UI on IE 6 (#3246)
- Remove double borders in folder listing (#3236)
- Separate full message headers UI element from headers table (#3238)
- Add part MIME ID to message_part_* hooks (#3241)
- Improve parsing of MS Outlook vCards (#3239)
- Updated PEAR::Net_Socket to 1.0.10
- Updated PEAR::Net_IDNA2 to 0.1.1
- Fix handling of comments inside an email address spec. (#3210)
- Show full mail subject as title when hovering a cut subject link (#3141)
- Fix randomly disappearing folders list in IE (#3231)
- Fix list column add/removal in IE (#3230)
- Fix login redirect issues (#3221)
- Require PHP 5.2.1 or greater
- Fix %h/%z variables in username_domain option (#3228)
- Workaround for setting charset in case of malformed bodystructure response (#3227)
- Fix impossible to subscribe to protected folders (#3199)
- Fix setting timezone in Preferences (#3232)

## Release 0.5

- Fix double-login/session issue (#3124)
- Wrap HTML parts with `<html><body>` and add Doctype declaration (#3119)
- Make rcube_autoload silently skip unknown classes (#3128)
- Fix charset detection in vcards with encoded values (#1934)
- Better CSS cursors for splitters (#2954)
- Show the same message only once (#3186)
- Fix namespaces handling (#3192)
- Add handling of multifolder METADATA/ANNOTATION responses
- Fix handling of INBOX when personal namespace prefix is non-empty (#3200)
- Fix handling square brackets in links (#3209)
- Add description of 'use_https' option in main.inc.php.dist file

## Release 0.5-RC

- Plugin API: Add 'pass' argument in 'authenticate' hook (#3147)
- Fix attachments of type message/rfc822 are not listed on attachments list
- Add 'login_lc' config option for case-insensitive authentication (#3131)
- Fix window is blur'ed in IE when selecting a message (#3161)
- Fix cursor position on compose form in Webkit browsers (#2796)
- Fix setting charset of attachment filenames (#3136)
- Allow setting autocomplete attribute for all inputs separately (#3158)
- New Folder Manager UI
- Fix invalid Request when creating a folder (#3165)
- Add folder size and quota indicator in folder manager (#2112)
- Add possibility to move a subfolder into root folder (#2890)
- Fix copying all messages in a folder copies only messages from current page
- Improve performance of moving or copying of all messages in a folder
- Fix plaintext versions of HTML messages don't contain placeholders for emotions (#1657)
- Improve performance of folder rename and delete actions
- Better support for READ-ONLY and NOPERM responses handling (#3108)
- Add confirmation message on purge/expunge command response
- Fix handling of untagged responses for AUTHENTICATE command (#3171)
- Add username and IP address to log message on unsuccessful login (#3176)
- Improved Mail-Followup-To and Mail-Reply-To headers handling
- Fix charset conversion for text attachments without charset specification (#3181)

## Release 0.5-BETA

- Make session data storage more robust against garbage session data (#3148)
- Config option for autocomplete on login screen
- Allow plugin templates to include local files (#3146)
- List groups in address detail view and allow to subscribe/unsubscribe from there (#2862)
- Messages caching: performance improvements, fixed syncing, fixes related with #2857
- Add link to identities in compose window (#2843)
- Add Internationalized Domain Name (IDNA) support (#729)
- Add option to automatically send read notifications for known senders (#2199)
- Add option to "Return receipt" will be always checked (#2571)
- Fix HTML to plain text conversion doesn't handle citation blocks (#2992)
- Use custom sorting when SORT is disabled by IMAP admin (#3020)
- Allow setting some washtml options from plugin (#2727)
- Add option do bind for an individual LDAP address book (#3048)
- Change reply prefix to display email address only if sender name doesn't exist (#2709)
- Plugin API: improved 'abort' flag handling, added 'result' item in some hooks (#2988)
- Fix mailto optional params in plain text messages aren't handled (#3071)
- Add Reply-to-List feature (#977)
- Add Mail-Followup-To/Mail-Reply-To support (#1937)
- Fix confirmation message isn't displayed after sending mail on Chrome (#2437)
- Fix keyboard doesn't work with autocomplete list with Chrome (#3073)
- Improve tabs to fixed width and add tabs in identities info (#3030)
- Add unique index on users.username+users.mail_host
- Make htmleditor option more consistent and add option to use HTML on reply to HTML message (#2164)
- Use empty envelope sender address for message disposition notifications (RFC 2298.3)
- Support SMTP Delivery Status Notifications - RFC 3461 (#2409)
- Use css sprite image for messages list
- Add (different) attachment icon for messages of type multipart/report (#2426)
- Prevent from inserting empty link when composing HTML message (#3007)
- Add caching support in id2uid and uid2id functions (#3065)
- Add SASL proxy authentication for SMTP (#2811)
- Improve displaying of UI messages (#3033)
- Fix double e-mail filed in identity form (#3088)
- Display IMAP errors for LIST/THREAD/SEARCH commands (#2981)
- Add LITERAL+ (IMAP4 non-synchronizing literals) support (RFC 2088)
- Add separate column for message status icon (#2788)
- Add ACL extension support into IMAP classes (RFC 4314)
- Add ANNOTATEMORE extension support into IMAP classes (draft-daboo-imap-annotatemore)
- Add METADATA extension support into IMAP classes (RFC 5464)
- Fix decoding of e-mail address strings in message headers (#3097)
- Fix handling of attachments when Content-Disposition is not inline nor attachment (#3086)
- Improve performance of unseen messages counting (#3090)
- Improve performance of messages counting using ESEARCH extension (RFC4731)
- Add LIST-STATUS support in rcube_imap_generic class (RFC 5819)
- Add SASL-IR support in IMAP (RFC 4959)
- Add LOGINDISABLED support (RFC 2595)
- Add support for AUTH=PLAIN in IMAP authentication
- Re-implemented SMTP proxy authentication support
- Add support for IMAP proxy authentication (#2808)
- Add support for AUTH=DIGEST-MD5 in IMAP (RFC 2831)
- Fix parent folder with unread subfolder not bold when message is open (#3104)
- Add basic IMAP LIST's \Noselect option support
- Add support for selection options from LIST-EXTENDED extension (RFC 5258)
- Don't list subscribed but non-existent folders (#2474)
- Fix handling of URLs with tilde (~) or semicolon (;) character (#3110, #3111)
- Plugin API: added 'contact_form' hook
- Add SORT=DISPLAY support (RFC 5957)
- Plugin API: add possibility to disable plugin in AJAX mode, 'noajax' property
- Plugin API: add possibility to disable plugin in framed mode, 'noframe' property
- Improve performance of setting IMAP flags using .SILENT suffix
- Improve performance of message cache status checking with skip_disabled=true
- Support contact's email addresses up to 255 characters long (#3116)
- Add option to place replies in the folder of the message being replied to (#2248)
- Add missing confirmation/error messages on contact/group/message actions (#2935)
- Add 'loading' message on message move/copy/delete/mark actions
- Improve responsiveness of messages displaying (#3039)
- Add option for minimum length of autocomplete's string (#2625)
- Fix operations on messages in unsubscribed folder (#3126)
- Add support for shared folders (#525)
- Fix handling of folders with name "0" (#3133)
- Fix handling of folders with `<>` characters in name
- jQuery 1.4.4
- Fix handling of HTML entity strings in plain text messages
- Fix focused elements aren't unfocused when clicking on the list (#3137)
- Fix error in MSSQL DDL scripts (#3130)
- Lock submit button in onsubmit event on login page (#3078)
- Don't set attachment's charset in Content-type header (#3136)
- Fix handling of message bodies (quoted-printable encoded) with NULL characters (#2448)
- Add workaround for MSOE's multipart/related messages with non-related attachments

## Release 0.4.2

- Fix handling of backslash as IMAP delimiter
- Fix charset replacement in HTML message bodies (#3067)
- Fix: contact group input is empty when using rename action more than once on the same group record
- Fix "Server Error! (Not Found)" when using utils/save-pref action (#3069)
- Fix handling of Thunderbird's vCards (#3070)

## Release 0.4.1

- Fix space-stuffing in format=flowed messages (#3064)
- Fix msgexport.sh now using the new imap wrapper
- Avoid displaying password on shell (#3010)
- Only lower-case user name if first login attempt failed (#2600)
- Make alias setting in squirrelmail_usercopy plugin configurable (patch by pommi, #3056)
- Prevent from saving a non-existing skin path in user prefs (#3004)
- Improve handling of single-part messages with bogus BODYSTRUCTURE (#2976)
- Fix path to SQL files when using pgsql/mysqli/sqlsrv drivers (#2979)
- Fix upgrade script for SQLite (#2980)
- Fixes in SQL init script + added update script for MSSQL database
- Remove redundant date in syslog messages (#3008)
- Fix contacts list page controls when a group is selected (#3009)
- Fix SMTP test in Installer (#3014)
- Fix "Select all" causes message to be opened in folder with exactly one message (#2987)
- Fix Tab key doesn't work in HTML editor in Google Chrome (#2995)
- Fix TinyMCE uses zh_CN when zh_TW locale is set (#2998)
- Fix TinyMCE buttons are hidden in Opera (#2993)
- Fix JS error on IE when trying to send HTML message with enabled spellchecker (#3006)
- Display inline images with known extensions and non-image content-type (#3002)
- Fix "Threaded" checkbox after subfolder creation (#2997)
- Fix timezone string in sent mail (#3021)
- Show disabled checkboxes for protected folders instead of dots (#1898)
- Added fieldsets in Identity form, added 'identity_form' hook
- Re-added 'Close' button in upload form (#2999, #2917)
- Fix handling of charsets with LATIN-* label
- Fix messages background image handling in some cases (#3043)
- Fix format=flowed handling (#3042)
- Fix when IMAP connection fails in 'get' action session shouldn't be destroyed (#3046)
- Fix list_cols is not updated after column dragging (#3050)
- Support %z variable in host configuration options (#3054)

## Release 0.4

- Fix disappearing upload form disappears when user selects a file on Safari (#2917)
- Don't replace error messages with loading info (#2534)
- Fix JS errors on compose mode switch (#2952)
- Fix message structure parsing when it lacks optional fields (#2960)
- Include all recipients in sendmail log
- Support HTTP_X_FORWARDED_PROTO header for HTTPS detecting (#2950)
- Fix default IMAP port configuration (#2948)
- Create Sent folder when starting to compose a new message (#2900)
- Fix handling of messages with Content-Type: application/* and no filename (#840)
- Improved compose screen: resizable body and attachments list, vertical splitter, options menu
- Fix RC forgets search results (#722)
- TinyMCE 3.3.7
- Improve parsing of styled empty tags in HTML messages (#2908)
- Add %dc variable support in base_dn/bind_dn config (#2881)
- Add button to hide/unhide the preview pane (#955)
- Fix no-cache headers on https to prevent content caching by proxies (#2897)
- Fix attachment filenames broken with TNEF decoder using long filenames (#2894)
- Use user's timezone in Date header, not server's timezone (#2393)
- Add option to set separate footer for HTML messages (#2784)
- Add real SMTP error description to displayed error messages (#2233)
- Fix some IMAP errors handling when opening the message (#1848)
- Fix related parts aren't displayed when got mimetype other than image/* (#2629)
- Multiple identity and database support for squirrelmail_usercopy plugin (#2686)
- Support dynamic hostname (%d/%n) variables in configuration options (#1843)
- Add 'messages_list' hook (#2504)
- Add request* event triggers in http_post/http_request (#2340)
- Fix use RFC-compliant line-delimiter when saving messages on IMAP (#2828)
- Add 'imap_timeout' option (#2869)
- Fix forwarding of messages with winmail attachments
- Fix handling of uuencoded attachments in message body (#2163)
- Added list_mailboxes hook in rcube_imap::list_unsubscribed() (#2791)
- Fix wrong message on file upload error (#2839)
- Add support for data URI scheme [RFC2397] (#2851)
- Added 'actionbefore', 'actionafter', 'responsebefore', 'responseafter' events
- Fix double-addition of e-mail domain to content ID in HTML images
- Read and send messages with format=flowed (#1052), fixes word wrapping issues (#2703)
- Fix duplicated attachments when forwarding a message (#2670)
- Fix message/rfc822 attachments containing only attachments are not parsed properly (#2854)
- Fix %00 character in winmail.dat attachments names (#2850)
- Fix handling errors of folder deletion (#2821)
- Parse untagged CAPABILITY response for LOGIN command (#2853)
- Renamed all php-cli scripts to use .sh extension
- Some files from /bin + spellchecking actions moved to the new 'utils' task
- Added thread tree icons
- Extend contact groups support (#2802)
- Fix check-recent action issues and performance (#2690)
- Fix messages order after checking for recent (#1249)
- Fix autocomplete shows entries without email (#2640)
- Fix listupdate event doesn't trigger on search response (#2824)
- Fix select_all_mode value after selecting a message (#2834)
- Set focus to editor on reply in HTML mode (#2768)
- Fix composing in HTML jumps cursor to body instead of recipients (#2796)
- Allow columns order change per user - drag&drop (#2124)
- Add References header in read receipt (#2801)
- Fix database constraint violation when opening a message (#2814)
- Add 'loading' message while login is in progress (#2790)
- Fix quota_zero_as_unlimited (#2786)
- Fix folder subscription checking (#2804)
- Fix INBOX appears (sometimes) twice in mailbox list (#2794)
- Fix listing of attachments of some types e.g. "x-epoc/x-sisx-app" (#2779)
- Fix DB Schema checking when some db_table_* options are not set (#2780)

## Release 0.4-beta

- Add sizelimit and timelimit variables in LDAP config (#2704)
- Hide IMAP host dropdown when single host is defined (#2553)
- Add images pre-loading on login page (#623)
- Add HTTP_X_REAL_IP and HTTP_X_FORWARDED_FOR to successful logins log (#2634)
- Fix setting spellcheck languages with extended codes (#2747)
- Fix messages list scrolling in FF3.6 (#2657)
- Fix quicksearch input focus (#2770)
- Always set changed date when flagging a DB record as deleted + provide a cleanup script
- Fix address book/group selection (#2760)
- Assign newly created contacts to the active group (#2764)
- Added option not to mark messages as read when viewed in preview pane (#1513)
- Allow plugins modify the Sent folder when composing (#2708)
- Added optional (max_recipients) support to restrict total number of recipients per message (#1167)
- Re-organize editor buttons, add blockquote and search buttons
- Make possible to write inside or after a quoted html message (#1878)
- Fix bugs on unexpected IMAP connection close (#2449, #2507)
- Iloha's imap.inc rewritten into rcube_imap_generic class
- Added contact groups in address book (not finished yet)
- Added PageUp/PageDown/Home/End keys support on lists (#2627)
- Added possibility to select all messages in a folder (#1312)
- Added 'imap_force_caps' option for after-login CAPABILITY checking (#2087)
- Password: Support dovecotpw encryption
- TinyMCE 3.3.1
- Implemented messages copying using drag&drop + SHIFT (#863)
- Improved performance of folders operations (#2689)
- Fix blocked.gif attachment is not attached to the message (#2685)
- Managesieve: import from Horde-INGO
- Managesieve: support for more than one match (#2362)
- Managesieve: support for selectively disabling rules within a single sieve script (#2198)
- Threaded message listing now available
- Added sorting by ARRIVAL and CC
- Message list columns configurable by the user
- Removed 'index_sort' option, now we're using empty 'message_sort_col' for this
- virtuser_query: support other identity data (#2413)
- Options virtuser_* replaced with virtuser_* plugins
- Plugin API: Implemented 'email2user' and 'user2email' hooks
- Fix forwarding message omits CC header (#2538)
- Add 'default_charset' option to user preferences (#1855)
- Add 'delete_always' option to user preferences
- Support/Require tls:// prefix in 'smtp_server' option for TLS connections
- Fix inconsistent behaviour of 'delete_always' option (#2533)
- Fix deleting all messages from last list page (#2528)
- Flag original messages when sending a draft (#2458)
- Changed signature separator when top-posting (#2555)
- Let the admin define defaults for search modifiers (#2211)
- Fix long e-mail addresses validation (#2641)
- Remember search modifiers in user prefs (#2411)
- Added force_7bit option to force MIME encoding of plain/text messages (#2679)
- Use case sensitive check when checking for default folders (#2567)
- Fix checking for new mail: now checks unseen count of inbox (#2123)
- Improve performance by avoiding unnecessary updates to the session table (#2552)
- Fix invalid `<font>` tags which cause HTML message rendering problems (#2687)
- Fix CVE-2010-0464: Disable DNS prefetching (#2639)
- Fix Received headers to behave better with SpamAssassin (#2682)
- Password: Make passwords encoding consistent with core, add 'password_charset' global option (#2658)
- Fix adding contacts SQL error on mysql (#2645)
- Squirrelmail_usercopy: support reply-to field (#2678)
- Fix IE spellcheck suggestion popup issue (#2656)
- Fix email address auto-completion shows regexp pattern (#2498)
- Fix merging of configuration parameters: user prefs always survive (#2584)
- Fix quota indicator value after folder purge/expunge (#2671)
- Fix external mailto links support for use as protocol handler (#2328)
- Fix attachment excessive memory use, support messages of any size (#1245)
- Fix setting task name according to auth state
- Password: fix vpopmaild driver (#2662)
- Add workaround for MySQL bug [http://bugs.mysql.com/bug.php?id=46293] (#2659)
- Fix quoted text wrapping when replying to an HTML email in plain text (#897)
- Fix handling of extended mailto links (with params) (#2573)
- Fix sorting by date of messages without date header on servers without SORT (#2521)
- Fix inconsistency when not using default table names (#2652)
- Fix folder rename/delete buttons do not appear on creation of first folder (#2653)
- Fix character set conversion fails on systems where iconv doesn't accept //IGNORE (#2590)
- Log in performance: Create default folders on first login only
- Import contacts into the selected address book (by Phil Weir)
- Add support for MDB2's 'sqlsrv' driver (#2602)
- Use jQuery-1.4
- Removed problematic browser-caching of messages
- Fix incompatibility with suhosin.executor.disable_emodifier (#2549)
- Use PLAIN auth when CRAM fails and imap_auth_type='check' (#2587)
- Fix removal of `<title>` tag from HTML messages (#2629)
- Fix 'force_https' to specified port when URL contains a port number (#2612)
- Fix to-text converting of HTML entities inside b/strong/th/hX tags (#2621)
- Bug in spellchecker suggestions when server charset != UTF8 (#2607)
- Managesieve: Fix requires generation for multiple actions (#2603)
- Fix LDAP problem with special characters in RDN (#2548)
- Improved handling of message parts of type message/rfc822
- Plugin API: added 'quota' hook
- Fix parsing conditional comments in HTML messages (#2569)
- Use built-in json_encode() for proper JSON format in AJAX replies
- Allow setting only selected params in 'message_compose' hook (#2543)
- Plugin API: added 'message_compose_body' hook (#2520)
- Fix counters of all folders are checked in 'getunread' action  with check_all_folders disabled (#2399)
- Fix displaying alternative parts in messages of type message/rfc822 (#2488)
- Fix possible messages exposure when using Roundcube behind a proxy (#2516)
- Fix unicode para and line separators in javascript response (#2542)
- Additional_message_headers: allow unsetting headers, support plugin's config file (#2505)
- Fix displaying of hidden directories in skins list (#2535)
- Fix open_basedir restriction error when reading skins list (#2537)
- Fix pasting from Office apps into html editor (#2508)
- Fix empty `<a>` tags parsing (#2509)
- Don't cut off attachment names when using non-RFC2231 encoding (#1912)
- Allow inserting signatures above replied message body (#991)
- Managesieve 2.0: multi-script support
- Fix imap_auth_type regression (#2502)

## Release 0.3.1

- Specify toolbar container in compose template (#2489)
- Fix $_SERVER['HTTPS'] check for SSL forcing on IIS (#2486)
- Avoid unnecessary page loads for selected tab (#2324)
- Fix quota indicator issues by content generation on client-size (#2454, #2470)
- Don't display disabled sections in Settings (#2380)
- Added server-side e-mail address validation with 'email_dns_check' option (#2175)
- Fix login page loading into an iframe when session expires (#2253)
- Allow setting port number in 'force_https' option (#2373)
- Option 'force_https' replaced by 'force_https' plugin
- Fix IE issue with non-UTF-8 characters in AJAX response (#2422)
- Partially fixed "empty body" issue by showing raw body of malformed message (#2427)
- Fix importing/sending to email address with whitespace (#2467)
- Added XIMSS (CommuniGate) driver for Password plugin
- Fix newly attached files are not saved in drafts w/o editing any text (#2457)
- Added attachment upload indicator with parallel upload (#2344)
- Use default_charset for bodies of messages without charset definition (#2446)
- Password: added cPanel driver
- Fix return to first page from e-mail screen (#2385)
- Fix handling HTML comments in HTML messages (#2448)
- Fix folder/messagelist controls alignment - icons used (#2356)
- Fix LDAP addressbook shows 'Contact not found' error sometimes (#2438)
- Fix cache status checking + improve cache operations performance (#2384)
- Prevent from setting INBOX as any of special folders (#2390)
- Fix regular expression for e-mail address (#2417)
- Fix Received header format
- Implemented sorting by message index - added 'index_sort' option (#2240)
- Fix dl() use in installer (#2415)
- Added 'ldap_debug' option
- Fix "Empty startup greeting" bug (#2369)
- Fix setting user name in 'new_user_identity' plugin (#2405)
- Fix incorrect count of new messages in folder list when using multiple IMAP clients (#2289)
- Fix all folders checking for new messages with disabled caching (#2399)
- Support skins in 'archive' and 'markasjunk' plugins
- Added 'html_editor' hook (#2353)
- Fix DB constraint violation when populating messages cache (#2338)
- Password: added password strength options (#2348)
- Fix LDAP partial result warning (#1928)
- Fix delete in message view deletes permanently with flag_for_deletion=true (#2382)
- Use faster/secure mt_rand() (#2376)
- Fix roundcube hangs on empty inbox with bincimapd (#2375)
- Fix wrong headers for IE on servers without $_SERVER['HTTPS'] (#2232)
- Force IE style headers for attachments in non-HTTPS session, 'use_https' option (#2023)
- Check 'post_max_size' for upload max filesize (#2372)
- Password Plugin: Fix %d inserts username instead of domain (#2371)
- Fix rcube_mdb2::affected_rows() (#2366)

## Release 0.3-stable

- Fix gn and givenName should be synonymous in LDAP addressbook (#2208)
- Add mail_domain to LDAP email entries without @ sign (#1652)
- Fix saving empty values in LDAP contact data (#2113)
- Fix LDAP contact update when RDN field is changed (#2119)
- Fix LDAP attributes case sensitivity problems (#2155)
- Fix LDAP addressbook browsing when only one directory is used (#2314)
- Fix endless loop on error response for APPEND command (#2346)
- Don't require date.timezone setting in installer (#2284)
- Fix date sorting problem with Courier IMAP server (#2351)
- Unselect pressed buttons on mouse up (#2283)
- Don't set php_value error_log in .htaccess but mention in INSTALL (#2230)
- Fix too small status/flag/attachment columns in Safari 4 (#2349)
- Fix selection disabling while dragging splitter in webkit browsers (#2342)
- Added 'new_messages' plugin hook (#2298)
- Added 'logout_after' plugin hook (#2333)
- Added 'message_compose' hook
- Added 'imap_connect' hook (#2256)
- Fix vcard_attachments plugin (#2326)
- Updated PEAR::Auth_SASL to 1.0.3 version
- Use sequence names only with PostgreSQL (#2310)
- Re-designed User Preferences interface
- Fix MS SQL DDL (#2312)
- Fix rcube_mdb2.php: call to setCharset not implemented in mssql driver (#2311)
- Added 'display_next' option
- Fix rcube_mdb2::unixtimestamp for MS SQL (#2308)
- Fix HTML washing to respect character encoding
- Fix endless loop in iil_C_Login() with Courier IMAP (#2303)
- Fix #messagemenu display on IE (#2299)
- Speedup UI by using sprites for (toolbar) buttons
- Fix charset names with X- prefix handling
- Fix displaying of HTML messages with unknown/malformed tags (#2296)

## Release 0.3-RC1

- Fix import of vCard entries with params (#1857)
- Fix HTML messages output with empty block elements (#2271)
- Use request tokens to protect POST requests from CSRF [CVE-2009-4076, CVE-2009-4077]
- Added hook when killing a session
- Added hook to write_log function (#2268)
- Performance improvements by use UID commands (#2046)
- Fix HTML editor tabIndex setting (#2269)
- Added 'imap_debug' and 'smtp_debug' options
- Support strftime's format modifiers in date_* options (#1354)
- Support %h variable in 'smtp_server' option (#2101)
- Show SMTP errors in browser (#2233)
- Allow WBR tag in HTML message (#2259)
- Use spl_autoload_register() instead of __autoload (#2250)
- Add hook for identities listing (#2257)
- Trigger hook 'smtp_connect' when opening an SMTP connection (#2255)
- Added config option to enforce HTTPS connections
- Fix non-unicode characters caching in unicode database (#1209)
- Performance improvements of messages caching
- Fix empty Date header issue (#2229)
- Open collapsed folders during drag & drop (#2221)
- Fixed link text replacements (#2120)
- Also trigger 'insertrow' events on page load (#2151)
- No link on subject in IE browsers (#1438)
- Fixed filename encoding according to RFC2231 (#2192)
- Added message Edit feature (#727, #1101)
- Fix message Etag generation for counter issues (#1996)
- Fix messages searching on MailEnable IMAP (#2097)
- Fixed many 'skip_deleted' issues (#2006)
- Fixed messages list sorting on servers without SORT capability
- Colorized signatures in plain text messages
- Reviewed/fixed skip_deleted/read_when_deleted/flag_for_deletion options handling in UI
- Fix displaying of big maximum upload filesize (#2205)
- Added possibility to invert messages selection
- After move/delete from 'show' action display next message instead of messages list (#2203)
- Fixed problem with double quote at the end of folder name (#2200)
- Speedup UI by using CSS sprites and etags/expires/deflate in Apache config (#1397,#2128)
- Support UID EXPUNGE: remove only moved/deleted messages
- Add drag cancelling with ESC key (#1036)
- Support initial identity name from virtuser_query (#807)
- Added message menu, removed Print and Source buttons
- Added possibility to save message as .eml file (#2178)
- Added 1 minute interval in autosave options (#2173)
- Support UTF-7 encoding in messages (#2156)
- Better support for malformed character names (#2093)

## Release 0.3-BETA

- Plugin API + jQuery engine
- Added possibility to encrypt received header, option 'http_received_header_encrypt',
  added some more logic in encrypt/decrypt functions for security
- Fix Answered/Forwarded flag setting for messages in subfolders
- Fix autocomplete problem with capital letters (#2122)
- Support UUencode content encoding (#2163)
- Minimize chance of race condition in session handling (#1260)
- Fix session handling on non-session SQL query error (#2078)
- Fix html editor mode setting when reopening draft message (#2158)
- Added quick search box menu (#1010)
- Fix wrong column sort order icons (#2149)
- Updated TinyMCE to 3.2.3 version
- Fix attachment names encoding when charset isn't specified in attachment part (#1483)
- Fix message normal priority problem (#2146)
- Fix autocomplete spinning wheel does not disappear (#2132)
- Added log_date_format option (#2060)
- Fix text wrapping in HTML editor after switching from plain text to HTML (#1917)
- Fix auto-complete function hangs with plus sign (#2141)
- Fix AJAX requests errors handler (#1503)
- Speed up message list displaying on IE
- Fix read/write database recognition (#2137)

## Release 0.2.2

- Fix quicksearchbox look in Chrome and Konqueror (#1380)
- Fix UTF-8 byte-order mark removing (#1911)
- Fix folders subscriptions on Konqueror (#1380)
- Fix debug console on Konqueror and Safari
- Fix messagelist focus issue when modifying status of selected messages (#2134)
- Support STARTTLS in IMAP connection (#1714)
- Fix DEL key problem in search boxes (#1923)
- Support several e-mail addresses per user from virtuser_file (#2036)
- Fix drag&drop with scrolling on IE (#2117)
- Fix adding signature separator in html mode (#1768)
- Fix opening attachment marks message as read (#2131)
- Fix 'temp_dir' does not support relative path under Windows (#1157)
- Fix "Initialize Database" button missing from installer (#2130)
- Fix compose window doesn't fit 1024x768 window (#1807)
- Fix service not available error when pressing back from compose dialog (#1942)
- Fix using mail() on Windows (#2111)
- Fix word wrapping in message-part's `<PRE>` tags in printing (#2118)
- Fix incorrect word wrapping in outgoing plaintext multibyte messages (#2062)
- Fix double footer in HTML message with embedded images
- Fix TNEF implementation bug (#2107)
- Fix incorrect row id parsing for LDAP contacts list (#2116)
- Fix 'mode' parameter in sqlite DSN (#2106)

## Release 0.2.1

- Use US-ASCII as failover when Unicode searching fails (#2097)
- Fix errors handling in IMAP command continuations (#2097)
- Fix FETCH result parsing for servers returning flags at the end of result (#2098)
- Fix datetime columns defaults in mysql's DDL (#2012)
- Fix attaching more than nine inline images (#2094)
- Support 'UNICODE-1-1-UTF-7' alias for UTF-7 encoding (#2093)
- Fix mime-type detection using a hard-coded map (#1735)
- Don't return empty string if charset conversion failed (#2092)
- Disable concurrent autocomplete query results display (#2082)
- Fix new lines stripped from message footer (#2088)
- Fix IE problem with mouse click autocomplete (#2080)
- Fix html body washing on reply/forward + fix attachments handling (#2034)
- Fix multiple recipients input parsing (#2077)
- Fix replying to message with html attachment (#2034)
- Use default_charset for messages without specified charset (#2027, #1484961)
- Support non-standard "GMT-XXXX" literal in date header (#2074)
- Added TNEF support to decode MS Outlook attachments (winmail.dat)
- Fix "value continuation" MIME headers by adding required semicolon (#2073)
- Fix pressing select all/unread multiple times (#2069)
- Fix selecting all unread does not honor new messages (#2070)
- Fix some base64 encoded attachments handling (#2071)
- Support NGINX as IMAP backend: better BAD response handling (#2066)
- Performance fix: don't fetch attachment parts headers twice to parse filename
- Fix checking for recent messages on various IMAP servers (#2055)
- Performance fix: Don't fetch quota and recent messages in "message view" mode
- Fix displaying of alternative-inside-alternative messages (#2061)
- Fix MDNSent flag checking, use arbitrary keywords (asterisk) flag (#2059)
- Fix creation of folders with '&' sign in name
- Fix parsing of email addresses without angle brackets (#2048)
- Save spellcheck corrections when switching from plain to html editor (and spellchecking is on)
- Fix large search results on server without SORT capability (#2031)
- Get rid of preg_replace() with eval modifier and create_function usage (#2042)
- Bring back `<base>` and `<link>` tags in HTML messages
- Fix XSS vulnerability through background attributes [CVE-2009-0413]
- Fix problems with backslash as IMAP hierarchy delimiter (#1116)
- Secure vcard export by getting rid of preg's 'e' modifier use (#2045)
- Fix authentication when submitting form with existing session (#2037)
- Allow absolute URLs to images in HTML messages/sigs (#2029)
- Fix message body which contains both inline attachments and emotions
- Fix SQL query execution errors handling in rcube_mdb2 class (#1907)
- Fix address names with '@' sign handling (#2022)
- Improve messages display performance
- Fix messages searching with 'to:' modifier

## Release 0.2-STABLE

- Fix mark popup in IE 7 (#1785)
- Fix line-break issue when copy & paste in Firefox (#1832)
- Fix autocomplete "unknown server error" (#2008)
- Fix STARTTLS before AUTH in SMTP connection (#1415)
- Support multiple quota values in QUOTAROOT response (#1999)
- Only abbreviate file name for IE < 7 browsers (#1548)
- Performance: allow setting imap root dir and delimiter before connect (#1628)
- Fix sorting of folders with more than 2 levels (#1953)
- Fix search results page jumps in LDAP addressbook (#1689)
- Fix empty line before the signature in IE (#1769)
- Fix horizontal scrollbar in preview pane on IE (#1228)
- Add Robots meta tag in login page and installer (#1385)
- Added 'show_images' option, removed 'addrbook_show_images' (#1977)
- Option to check for new mails in all folders (#1053)
- Don't set client busy when checking for new messages (#1706)
- Allow UTF-8 folder names in config (#1960)
- Add junk_mbox option configuration in installer (#1960)
- Do serverside addressbook queries for autocompletion (#1925)
- Allow setting attachment col position in 'list_cols' option
- Allow override 'list_cols' via skin (#1958)
- Fix 'cache' table cleanup on session destroy (#1913)
- Increase speed of session destroy and garbage clean up
- Fix session timeout when DB server got clock skew (#1890)
- Fix handling of some malformed messages (#1099)
- Speed up raw message body handling
- Better HTML entities conversion in html2text (#1916)
- Fix big memory consumption and speed up searching on servers without SORT capability
- Fix setting locale to tr_TR, ku and az_AZ (#1872)
- Use SORT for searching on servers with SORT capability
- Added message status filter
- Fix empty file sending (#1801)
- Improved searching with many criteria (calling one SEARCH command)
- Fix HTML editor initialization on IE (#1731)
- Add warning when switching editor mode from html to plain (#1888)
- Make identities list scrollable (#1930)
- Fix problem with numeric folder names (#1922)
- Added BYE response simple support to prevent from endless loops in imap.inc (#777)
- Fix unread message unintentionally marked as read if read_when_deleted=true (#1819)
- Remove port number from SERVER_NAME in smtp_helo_host (#1915)
- Don't send disposition notification receipts for messages marked as 'read' (#1918)
- Added 'keep_alive' and 'min_keep_alive' options (#1777)
- Added option 'identities_level', removed 'multiple_identities'
- Allow deleting identities when multiple_identities=false (#1840)
- Added option focus_on_new_message (#1789)
- Fix html2text class autoloading on Windows (#1904)
- Fix html signature formatting when identity save error occurred (#1833)
- Add feedback and set busy when moving folder (#1897)
- Fix 'Empty' link visibility for some languages e.g. Slovak (#1889)
- Fix messages count bar overlapping (#1703)
- Fix adding signature in drafts compose mode (#1884)
- Fix iil_C_Sort() to support very long and/or divided responses (#1713)
- Fix matching case sensitivity when setting identity on reply (#1881)
- Prefer default identity on reply
- Fix imap searching on ISMail server (#1870)
- Add css class for flagged messages (#1868)
- Write username instead of id in sendmail log (#1879)
- Fix htmlspecialchars() use for PHP version < 5.2.3 (#1877)
- Fix js keywords escaping in json_serialize() for IE/Opera (#1874)
- Added bin/killcache.php script (#1839)
- Add support for SJIS, GB2312, BIG5 in rc_detect_encoding()
- Fix vCard file encoding detection for non-UTF-8 strings (#1820)
- Add 'skip_deleted' option in User Preferences (#1850)
- Minimize "inline" javascript scripts use (#1838)
- Fix css class setting for folders with names matching defined classes names (#1772)
- Fix race conditions when changing mailbox
- Fix spellchecking when switching to html editor (#1779)
- Fix compose window width/height (#1807)
- Allow calling msgimport.sh/msgexport.sh from any directory (#1837)
- Localized filesize units (#1760)
- Better handling of "no identity" and "no email in identity" situations (#1592)
- Added 'mime_param_folding' option with possibility to choose long/non-ascii attachment names encoding e.g. to be readable in MS Outlook/OE (#1743)
- Added "advanced options" feature in User Preferences
- Fix unread counter when displaying cached massage in preview panel (#1720)
- Fix htmleditor spellchecking on MS Windows (#1808)
- Fix problem with non-ascii attachment names in Mail_mime (#1700, #1576)
- Fix language autodetection (#1812)
- Fix button label in folders management (#1816)
- Fix collapsed folder not indicating unread msgs count of all subfolders (#1814)
- Fix handling of apostrophes in filenames decoded according to rfc2231

## Release 0.2-BETA

- Made config files location configurable (#1664)
- Reduced memory footprint when forwarding attachments (#1764)
- Allow and use spellcheck attribute for input/textarea fields (#1545)
- Added icons for forwarded/forwarded+replied messages (#1691)
- Added Reply-To to forwarded emails (#1739)
- Display progress message for folders create/delete/rename (#1774)
- Smart Tags and NOBR tag support in html messages (#1780, #1748)
- Redesign of the identities settings (#836)
- Add config option to disable creation/deletion of identities (#1139)
- Added 'sendmail_delay' option to restrict messages sending interval (#1135)
- Added vertical splitter for folders list resizing
- Added possibility to view all headers in message view
- Fixed splitter drag/resize on Opera (#1626)
- Fixed quota img height/width setting from template (#1396)
- Refactor drag & drop functionality. Don't rely on browser events anymore (#1108)
- Insert "virtual" folders in subscription list (#1333)
- Added link to open message in new window
- Enable export of address book contacts as vCard
- Add feature to import contacts from vcard files (#395)
- Respect Content-Location headers in multipart/related messages according to RFC2110 (#1464)
- Allowed max. attachment size now indicated in compose screen (#1523)
- Also capture backspace key in list mode (#1186)
- Allow application/pgp parts to be displayed (#1309)
- Correctly handle options in mailto-links (#1671)
- Immediately save sort_col/sort_order in user prefs (#1698)
- Truncate very long (above 50 characters) attachment filenames when displaying
- Allow to auto-detect client language if none set (#1095)
- Auto-detect the client timezone (user configurable)
- Add RFC2231 header value continuations support for attachment filenames + hack for servers that not support that feature
- Fix Reply-To header displaying (#1738)
- Mark form buttons that provide the most obvious operation (mainaction)
- Added option 'quota_zero_as_unlimited' (#1206)
- Added PRE handling in html2text class (#1301)
- Added folder hierarchy collapsing
- Added options to use syslog instead of log file (#1389)
- Added Logging & Debugging section in Installer
- Fix In-Reply-To and References headers when composing saved draft message (#1718)
- Fix html message charset conversion for charsets with underline (#1717)
- Fix buttons status after contacts deletion (#1675)
- Fix escaping of To: and From: fields when building message body for reply or forward in the HTML editor (#1432)
- Use current mailbox name in template (#1690)
- Better fix for skipping untagged responses (#1694)
- Added pspell support patch by Kris Steinhoff (#781)
- Enable spellchecker for HTML editor (#1589)
- Respect spellcheck_uri in tinyMCE spellchecker (#941)
- Case insensitive contacts searching using PostgreSQL (#1692)
- Make default imap folders configurable for each user (#1558)
- Save outgoing mail to selectable folder (#1324581)
- Fix hiding of mark menu when clicking th button again (#1463)
- Use long date format in print mode (#1643)
- Updated TinyMCE to version 3.1.0.1
- Re-enable autocomplete attribute for login form (#1661)
- Check PERMANENTFLAGS before saving $MDNSent flag (#1478, #1485163)
- Added flag column on messages list (#1220)
- Patched Mail/MimePart.php (http://pear.php.net/bugs/bug.php?id=14232)
- Allow trash/junk subfolders to be purged (#1568)
- Store compose parameters in session and redirect to a unique URL
- Fixed CRAM-MD5 authentication (#1364)
- Fixed forwarding messages with one HTML attachment (#1103)
- Fixed encoding of message/rfc822 attachments and image/pjpeg handling (#1439)
- Added option to select skin in user preferences
- Added option to configure displaying of attached images below the message body
- Added option to display images in messages from known senders (#1204)
- User preferences grouped in more fieldsets
- Fix corrupted MIME headers of messages in Sent folder (#1587)
- Fixed bug in MDB2 package: http://pear.php.net/bugs/bug.php?id=14124
- Use keypress instead of keydown to select list's row (#1362)
- Don't call expunge and don't remove message row after message move if flag_for_deletion is set to true (#1505)

## Release 0.2-ALPHA

- Added option to disable autocompletion from selected LDAP address books (#1445)
- TLS support in LDAP connections: 'use_tls' property (#1581)
- Fixed removing messages from search set after deleting them (#1583)
- imap.inc: Fixed iil_C_FetchStructureString() to handle many
  literal strings in response (#1483)
- Support for subfolders in default/protected folders (#1250)
- Disallowed delimiter in folder name (#1351)
- Support " and \ in folder names
- Escape \ in login (#1214)
- Better HTML sanitization with the DOM-based washtml script (#1276)
- Fixed sorting of folders with non-ascii characters
- Fixed Mysql DDL for default identities creation (#1554)
- In Preferences added possibility to configure 'read_when_deleted',
  'mdn_requests', 'flag_for_deletion' options
- Made IMAP auth type configurable (#683)
- Fixed empty values with FROM_UNIXTIME() in rcube_mdb2 (#1540)
- Fixed attachment list on IE 6/7 (#1355)
- Fixed JavaScript in compose.html that shows cc/bcc fields if populated
- Make password input fields of type password in installer (#1417)
- Don't attempt to delete cache entries if enable_caching is FALSE (#1537)
- Optimized messages sorting on servers without sort capability (#1535)
- Corrected message headers decoding when charset isn't specified and improved
  support for native languages (#1536, #1534)
- Expanded LDAP configuration options to support LDAP server writes.
- Installer: encode special characters in DB username/password (#1529)
- Fixed management of folders with national characters in names (#1526, #1504)
- Fixed identities saving when using MDB2 pgsql driver (#1525)
- Fixed BCC header reset (#1501)
- Improved messages list performance - patch from Justin Heesemann
- Append skin_path to images location only when it starts with '/' sign (#1398)
- Fix IMAP response in message body when message has no body (#1479)
- Fixed non-RFC dates formatting (#1429)
- Fixed typo in set_charset() (#1498)
- Decode entities when inserting HTML signature to plain text message (#1497)
- HTML editing is now working with PHP5 updates and TinyMCE v3.0.6
- Fixed signature loading on Windows (#1169)
- Added language support to HTML editing (#1401)
- Fixed remove signature when replying (#446)
- Fixed problem with line with a space at the end (#1440)
- Fixed `<!DOCTYPE>` tag filtering (#1066)
- Fixed `<?xml>` tag filtering (#1075)
- Added sections (fieldset+label) in Settings interface
- Mark as read in one action with message preview (#1486)
- Deleted redundant quota reads (#1486)
- Added options for empty trash and expunge inbox on logout (#707)
- Removed lines wrapping when displaying message
- Fixed month localization
- Changed codebase to PHP5 with autoloader

## Release 0.1.1

- Clear selection when selecting single item (#1461)
- Remove hard-coded image size in skin templates (#1423)
- Database schema improvements (dropped unnecessary indexes)
- Fixed creating a new folder with a comma in its name (#1263)
- Fixed sorting of messages when default mailbox is empty (#1020)
- Improve message previewpane - less loading (#1019)
- Fixed login form autocompletion (#1378)
- Fixed virtuser_query option for mdb2 backend (#1409)
- Fixed attachment restoring from Drafts when message body was empty (#1144)
- Fixed usage of ob_gzhandler (#1390)
- Fixed message part window in IE6 (#1211)
- Fixed decoding of mime-encoded strings (#938)
- Fixed some iconv/mb_string problems (#1202)
- Correctly quote mailbox name when using in URL (#1016)
- Fixed "headers already sent" errors (#1399)

## Release 0.1-STABLE

- Added interactive installer script
- Fix folder adding/renaming inspired by #1349
- Localize folder name in page title (#1338)
- Fix code using wrong variable name (#818)
- Allow to send mail with BCC recipients only
- condense TinyMCE toolbar down to one line, removing table buttons (#1306)
- Add function to mark the selected messages as read/unread (#641)
- Also do charset decoding as suggested in RFC 2231 (fix #1022)
- Show message count in folder list and hint when creating a subfolder
- Distinguish ssl and tls for imap connections (#1252)
- Added some charset aliases to fix typical mis-labelling (#1185)
- Remember decision to display images for a certain message during session (#1310)
- Truncate attachment filenames to 55 characters due to an IE bug (#1313)
- Make sending of read receipts configurable
- Respect config when localize folder names (#1280)
- Also respect receipt and priority settings when re-opening a draft message
- Remember search results (closes #722), patch by the_glu
- Add Received header on outgoing mail
- Upgrade to TinyMCE 2.1.3
- Allow inserting image attachments into HTML messages while composing (#1179)
- Implement Message-Disposition-Notification (Receipts)
- Fix overriding of session vars when register_globals is on (#1255)
- Fix bug with case-sensitive folder names (#973)
- Don't create default folders by default
- Fixed some potential security risks (audited by Andris)
- Only show new messages if they match the current search (#925)
- Switch to/from when searching in Sent folder (#1177)
- Correctly read the References header (#1236)
- Unset old cookie before sending a new value (#1232)
- Correctly decode attachments when downloading them (#1235 and #1484642)
- Suppress IE errors when clearing attachments form (#1043)
- Log error when login fails due to auto_create_user turned off
- Filter linked/imported CSS files (closes #844)
- Improve message compose screen (closes #1060)
- Select next row after removing one from list (#1063)

## Release 0.1-RC2

- Enable drag-&-dropping of folders to a new parent and allow to create subfolders (#637)
- Suppress IE errors when clearing attachments form (#1043)
- Set preferences field in user table to NULL (#1062)
- Log error when login fails due to auto_create_user turned off
- Filter linked/imported CSS files (closes #844)
- Improve message compose screen (closes #1060)
- Select next row after removing one from list (#1063)
- Make smtp HELO/EHLO hostname configurable (#851)
- IPv6 Compatibility (#1023), Patch #1484373
- Unlock interface when message sending fails (#1188)
- Eval PHP code in template includes (if configured)
- Show message when folder is empty. Mo more static text in table (#1068)
- Only display unread count in page title when new messages arrived
- Fixed wrong delete button tooltip (#785)
- Fixed charset encoding bug (#1091)
- Applied patch for LDAP version (#1175)
- Improved XHTML validation
- Fix message list selection (#1174)
- Better fix lowercased usernames (#1120)
- Update pngbehavior Script as suggested in #1134
- Fixed moving/deleting messages when more than 1 is selected
- Applied patch for LDAP contacts listing by Glen Ogilvie
- Applied patch for more address fields in LDAP contacts (#1074)
- Add alternative for getallheaders() (fix #1146)
- Identify mailboxes case-sensitive
- Sort mailbox list case-insensitive (closes #1032)
- Fix display of multipart messages from Apple Mail (closes #823)
- Protect AJAX request from being fetched by a foreign site (XSS)
- Make autocomplete for loginform configurable by the skin template
- Fix compose function from address book (closes #1089)
- Added //IGNORE to iconv call (patch #1086, closes #821)
- Check if mbstring supports charset (#1003 and #1004)
- Prefer iconv over mbstring (as suggested in #1004)
- Check filesize of template includes (#1079)
- Fixed bug with buttons not dimming/enabling properly after switching folders
- Fixed compose window becoming unresponsive after saving a draft (#1132)
- Re-enabled "Back" button in compose window now that bug #1132 is fixed
- Fixed unresponsive interface issue when downloading attachments (#1138)
- Lowered status message time from 5 to 3 seconds to improve responsiveness
- Raised .htaccess upload_max_filesize from 2M to 5M to differ from default php.ini
- Increased "mailboxcontrols" mail.css width from 160 to 170px to fix non-english languages (#1140)
- Fix status message bug #1114 with regard to #1041
- Fix address adding bug reported by David Koblas
- Applied socket error patch by Thomas Mangin
- Pass-by-reference workaround for PHP5 in sendmail.inc
- Fixed buggy imap_root settings (closes #1056)
- Prevent default events on subject links (#1071)
- Use HTTP-POST requests for actions that change state

## Release 0.1-RC1

- Use global filters and bind username/ for Ldap searches (#909)
- Hide quota display if imap server does not support it
- Hide address groups if no LDAP servers configured
- Add link to message subjects (closes #982)
- Better SQL query for contact listing/search (closes #1051)
- Fixed marking as read in preview pane (closes #1048)
- CSS hack to display attachments correctly in IE6
- Wrap message body text (closes #901)
- LDAP access is back in address book (closes #864)
- Added search function for contacts
- New Template parsing and output encoding
- Fixed bugs #884 and #793
- Fixed message moving procedure (closes #1013)
- Fixed display of multiple attachments (closes #647)
- Fixed check for new messages (closes #1015)
- List attachments without filename
- New session authentication: Change sessid cookie when login, authentication with sessauth cookie is now configurable.
  Should close bugs #774 and #1484299
- Correctly translate mailbox names (closes #993)
- Quote e-mail address links (closes #1007)
- Updated PEAR::Mail_mime package
- Accept single quotes for HTML attributes when modifying message body (thanks Jason)
- Sanitize input for new users/identities (thanks Colin Alston)
- Don't download HTML message parts
- Convert HTML parts to plaintext if 'prefer_html' is off
- Correctly parse message/rfc822 parts (closes #838)
- Also use user_id for unique key in messages table (closes #857)
- Hide contacts drop down on blur (closes #946)
- Make entries in contacts drop down clickable
- Turn off browser autocompletion on login page
- Quote `<?` in text/html message parts
- Hide border around radio buttons
- Applied patch for attachment download by crichardson (closes #943)
- Fixed bug in Postgres DB handling (closes #852)
- Fixed bug of invalid calls to fetchRow() in rcube_db.inc (closes #996)
- Fixed array_merge bug (closes #997)
- Fixed flag for deletion in list view (closes #987)
- Finally support semicolons as recipient separator (closes ##976)
- Fixed message headers (subject) encoding
- check if safe mode is on or not (closes #990)
- Show "no subject" in message list if subject is missing (closes #971)
- Solved page caching of message preview (closes #905)
- Only use gzip compression if configured (closes #967)
- Fixed priority selector issue (#903)
- Fixed some CSS issues in default skin (closes #951 and #911)
- Prevent from double quoting of numeric HTML character references (closes #978)
- Fixed display of HTML message attachments (closes #927)
- Applied patch for preview caching (closes #933)
- Added error handling for attachment uploads
- Use multibyte safe string functions where necessary (closes #798)
- Applied security patch to validate the submitted host value (by Kees Cook)
- Applied security patch to validate input values when deleting contacts (by Kees Cook)
- Applied security patch that sanitizes emoticon paths when attaching them (by Kees Cook)
- Applied a patch to more aggressively sanitize a HTML message
- Visualize blocked images in HTML messages
- Fixed wrong message listing when showing search results (closes #890)
- Show remote images when opening HTML message part as attachment
- Improve memory usage when sending mail (closes #871)
- Mark messages as read once the preview is loaded (closes #1484132)
- Include smtp final response in log (closes #862)
- Corrected date string in sent message header (closes #887)
- Correctly choose "To" column in sent and draft mailboxes (closes #769)
- Changed tooltips for message browse buttons (closes #757)
- Fixed signature delimiter character to be standard (Bug #830)
- Fixed XSS vulnerability (Bug #877)
- Remove newlines from mail headers (Bug #827)
- Selection issues when moving/deleting (Bug #837)
- Applied patch of Clement Moulin for imap host auto-selection
- ISO-encode IMAP password for plaintext login (Bugs #792 & #723)
- Fixed folder name encoding in subscription list (Bug #879)
- Fixed JS errors in identity list (Bug #885)
- Translate foldernames in folder form (closes #879)
- Added first and last buttons to message list, address book
  and message detail
- Pressing Shift-Del bypasses Trash folder
- Enable purge command for Junk folder
- Fetch all aliases if virtuser_query is used instead
- Re-enabled multi select of contacts (Bug #817)
- Enable contact editing right after creation (Bug #644)
- Correct UTF-7 to UTF-8 conversion if mbstring is not available
- Fixed IMAP fetch of message body (Bug #819)
- Fixed safe_mode problems (Bug #539)
- Fixed wrong header encoding (Bug #1483976)
- Made automatic draft saving configurable
- Fixed JS bug when renaming folders (Bug #799)
- Added quota display as image (by Brett Patterson)
- Corrected creation of a message-id
- New indentation for quoted message text
- Improved HTML validity
- Fixed URL character set (Ticket #616)
- Fixed saving of contact into MySQL from LDAP query results (Ticket #681)
- Fixed folder renaming: unsubscribe before rename (Bug #750)
- Finalized new message parsing (+ caching)
- Fixed wrong usage of mbstring (Bug #645)
- Set default spelling language (Ticket #764)
- Added support for Nox Spell Server
- Re-built message parsing (Bug #422)
  Now based on the message structure delivered by the IMAP server.
- Fixed some XSS and SQL injection issues
- Fixed charset problems with folder renaming
