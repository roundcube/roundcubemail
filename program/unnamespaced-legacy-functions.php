<?php

namespace {
    /**
     * @param string $needle
     * @param ?array $haystack
     *
     * @return bool
     */
    function in_array_nocase($needle, $haystack)
    {
        return \Roundcube\WIP\in_array_nocase($needle, $haystack);
    }

    /**
     * @param string|int|float $str
     *
     * @return int|false
     */
    function parse_bytes($str)
    {
        return \Roundcube\WIP\parse_bytes($str);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    function slashify($str)
    {
        return \Roundcube\WIP\slashify($str);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    function unslashify($str)
    {
        return \Roundcube\WIP\unslashify($str);
    }

    /**
     * @param string|int $str
     *
     * @return int
     */
    function get_offset_sec($str)
    {
        return \Roundcube\WIP\get_offset_sec($str);
    }

    /**
     * @param string $offset_str
     * @param int    $factor
     *
     * @return int
     */
    function get_offset_time($offset_str, $factor = 1)
    {
        return \Roundcube\WIP\get_offset_time($offset_str, $factor);
    }

    /**
     * @param string $str
     * @param int    $maxlength
     * @param string $placeholder
     * @param bool   $ending
     *
     * @return string
     */
    function abbreviate_string($str, $maxlength, $placeholder = '...', $ending = false)
    {
        return \Roundcube\WIP\abbreviate_string($str, $maxlength, $placeholder, $ending);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    function array_keys_recursive($array)
    {
        return \Roundcube\WIP\array_keys_recursive($array);
    }

    /**
     * @param array $array
     *
     * @return mixed
     */
    function array_first($array)
    {
        return \Roundcube\WIP\array_first($array);
    }

    /**
     * @param string $str
     * @param bool   $css_id
     * @param string $replace_with
     *
     * @return string
     */
    function asciiwords($str, $css_id = false, $replace_with = '')
    {
        return \Roundcube\WIP\asciiwords($str, $css_id, $replace_with);
    }

    /**
     * @param string $str
     * @param bool   $control_chars
     *
     * @return bool
     */
    function is_ascii($str, $control_chars = true)
    {
        return \Roundcube\WIP\is_ascii($str, $control_chars);
    }

    /**
     * @param string $email
     * @param string $name
     *
     * @return string
     */
    function format_email_recipient($email, $name = '')
    {
        return \Roundcube\WIP\format_email_recipient($email, $name);
    }

    /**
     * @param string $email
     *
     * @return string
     */
    function format_email($email)
    {
        return \Roundcube\WIP\format_email($email);
    }

    /**
     * @param string $version
     *
     * @return string
     */
    function version_parse($version)
    {
        return \Roundcube\WIP\version_parse($version);
    }
}
