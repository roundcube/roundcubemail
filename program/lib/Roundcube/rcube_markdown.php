<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Helper class to convert Markdown text to HTML format                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 */

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Class for Markdown to HTML conversion
 */
class rcube_markdown
{
    public static function to_html(string $markdown): string
    {
        return self::converter()->convert($markdown);
    }

    protected static function converter()
    {
        // Some settings for security.
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        // GFM bundles some commonly used syntax extensions as well as protects against some raw HTML elements that can
        // breaks things easily.
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $converter = new MarkdownConverter($environment);
        $converter = new MarkdownConverter($environment);
        return $converter;
    }
}
