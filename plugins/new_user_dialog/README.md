# RoundCube New User Dialog Plugin
When a new user is created, this plugin checks the default identity and sets a session flag in case it is incomplete. An overlay box will appear on the screen until the user has reviewed/completed his identity.

## Configuration
Optional you can specify the default signature, `@EMAIL@` is replaced by the user email.

```
$config['new_user_dialog_signature_text'] = '
Your Name

E-Mail : @EMAIL@
Website: some.domain
Phone: 01234/567890
';
```
