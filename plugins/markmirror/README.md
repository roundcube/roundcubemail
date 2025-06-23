Markmirror
==========

A markdown editor.

This adds another email composing editor to Roundcubemail, which converts its content to HTML before sending.

It provides syntax highlighting and a toolbar (including a preview button) to help writing markdown. Drafts are saved as-is, and are automatically re-opened in this editor on re-editing. On sending the written markdown text gets converted into HTML.

(Roundcubemail automatically produces a multipart/alternative email from the given HTML, including a text/plain part consisting of re-converted HTML-to-text. Using our original text as text/plain part would be preferable but is left for a future version of this plugin.)


Installation
------------

Run `npm clean-install && npm run build` to produce the minified Javascript and CSS files required to run.

To enable this editor, add `'markmirror'` to the list of plugins in your `config.inc.php`.

There is no configuration.
