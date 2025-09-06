Folder Info (Roundcube Webmail Plugin)
==========
Plugin to show information message above the messages list.

Configuration Options
---------------------
Set the following options directly in Roundcube's config file (example):
```php
$rcmail_config['folder_info_messages'] = array(
   'Folder 1' => 'Messages will be deleted after {} {}.',
   'Folder 2' => 'Messages will be deleted after {} days.'
);
$rcmail_config['folder_info_messages_args'] = array(
  'Folder 1' => array(30, 'days'),
  'Folder 2' => 14
);
```

Translation
-----------
You can help me to translate plugin [here](https://www.transifex.com/san4op/roundcube-folder-info-plugin/).

Donate
------
You can make a donation [here](http://yasobe.ru/na/roundcube_folder_info), this will help motivate me to continue my work.
