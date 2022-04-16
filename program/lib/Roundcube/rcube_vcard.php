<?php

/**
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
 |   Logical representation of a vcard address record                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Logical representation of a vcard-based address record
 * Provides functions to parse and export vCard data format
 *
 * @package    Framework
 * @subpackage Addressbook
 */
class rcube_vcard
{
    private static $values_decoded = false;
    private $raw = [
        'FN' => [],
        'N'  => [['','','','','']],
    ];
    private static $fieldmap = [
        'phone'    => 'TEL',
        'birthday' => 'BDAY',
        'website'  => 'URL',
        'notes'    => 'NOTE',
        'email'    => 'EMAIL',
        'address'  => 'ADR',
        'jobtitle' => 'TITLE',
        'department'  => 'X-DEPARTMENT',
        'gender'      => 'X-GENDER',
        'maidenname'  => 'X-MAIDENNAME',
        'anniversary' => 'X-ANNIVERSARY',
        'assistant'   => 'X-ASSISTANT',
        'manager'     => 'X-MANAGER',
        'spouse'      => 'X-SPOUSE',
        'edit'        => 'X-AB-EDIT',
        'groups'      => 'CATEGORIES',
    ];
    private $typemap = [
        'IPHONE'   => 'mobile',
        'CELL'     => 'mobile',
        'WORK,FAX' => 'workfax',
    ];
    private $phonetypemap = [
        'HOME1'       => 'HOME',
        'BUSINESS1'   => 'WORK',
        'BUSINESS2'   => 'WORK2',
        'BUSINESSFAX' => 'WORK,FAX',
        'MOBILE'      => 'CELL',
    ];
    private $addresstypemap = [
        'BUSINESS' => 'WORK',
    ];
    private $immap = [
        'X-JABBER' => 'jabber',
        'X-ICQ'    => 'icq',
        'X-MSN'    => 'msn',
        'X-AIM'    => 'aim',
        'X-YAHOO'  => 'yahoo',
        'X-SKYPE'  => 'skype',
        'X-SKYPE-USERNAME' => 'skype',
    ];

    public $business = false;
    public $displayname;
    public $surname;
    public $firstname;
    public $middlename;
    public $nickname;
    public $organization;
    public $email = [];

    public static $eol = "\r\n";


    /**
     * Constructor
     *
     * @param string $vcard    vCard content
     * @param string $charset  Charset of string values
     * @param bool   $detect   True if loading a 'foreign' vcard and extra heuristics
     *                         for charset detection is required
     * @param array  $fieldmap Fields mapping definition
     */
    public function __construct($vcard = null, $charset = RCUBE_CHARSET, $detect = false, $fieldmap = [])
    {
        if (!empty($fieldmap)) {
            $this->extend_fieldmap($fieldmap);
        }

        if (!empty($vcard)) {
            $this->load($vcard, $charset, $detect);
        }
    }

    /**
     * Load record from (internal, unfolded) vcard 3.0 format
     *
     * @param string $vcard   vCard string to parse
     * @param string $charset Charset of string values
     * @param bool   $detect  True if loading a 'foreign' vcard and extra heuristics
     *                        for charset detection is required
     */
    public function load($vcard, $charset = RCUBE_CHARSET, $detect = false)
    {
        self::$values_decoded = false;
        $this->raw = self::vcard_decode(self::cleanup($vcard));

        // resolve charset parameters
        if ($charset == null) {
            $this->raw = self::charset_convert($this->raw);
        }
        // vcard has encoded values and charset should be detected
        else if (self::$values_decoded) {
            if ($detect) {
                $charset = self::detect_encoding(self::vcard_encode($this->raw));
            }
            if ($charset != RCUBE_CHARSET) {
                $this->raw = self::charset_convert($this->raw, $detected_charset);
            }
        }

        // find well-known address fields
        $this->displayname  = $this->raw['FN'][0][0] ?? null;
        $this->surname      = $this->raw['N'][0][0] ?? null;
        $this->firstname    = $this->raw['N'][0][1] ?? null;
        $this->middlename   = $this->raw['N'][0][2] ?? null;
        $this->nickname     = $this->raw['NICKNAME'][0][0] ?? null;
        $this->organization = $this->raw['ORG'][0][0] ?? null;
        $this->business     = (isset($this->raw['X-ABSHOWAS'][0][0]) && $this->raw['X-ABSHOWAS'][0][0] == 'COMPANY')
            || (!empty($this->organization) && isset($this->raw['N'][0]) && @implode('', (array) $this->raw['N'][0]) === '');

        if (!empty($this->raw['EMAIL'])) {
            foreach ((array) $this->raw['EMAIL'] as $i => $raw_email) {
                $this->email[$i] = is_array($raw_email) ? $raw_email[0] : $raw_email;
            }
        }

        // make the pref e-mail address the first entry in $this->email
        $pref_index = $this->get_type_index('EMAIL');
        if ($pref_index > 0) {
            $tmp = $this->email[0];
            $this->email[0] = $this->email[$pref_index];
            $this->email[$pref_index] = $tmp;
        }

        // fix broken vcards from Outlook that only supply ORG but not the required N or FN properties
        if (!strlen(trim($this->displayname . $this->surname . $this->firstname)) && strlen($this->organization)) {
            $this->displayname = $this->organization;
        }
    }

    /**
     * Return vCard data as associative array to be used in Roundcube address books
     *
     * @return array Hash array with key-value pairs
     */
    public function get_assoc()
    {
        $out     = ['name' => $this->displayname];
        $typemap = $this->typemap;

        // copy name fields to output array
        foreach (['firstname', 'surname', 'middlename', 'nickname', 'organization'] as $col) {
            if (is_string($this->$col) && strlen($this->$col)) {
                $out[$col] = $this->$col;
            }
        }

        if (!empty($this->raw['N'][0][3])) {
            $out['prefix'] = $this->raw['N'][0][3];
        }

        if (!empty($this->raw['N'][0][4])) {
            $out['suffix'] = $this->raw['N'][0][4];
        }

        // convert from raw vcard data into associative data for Roundcube
        foreach (array_flip(self::$fieldmap) as $tag => $col) {
            if (empty($this->raw[$tag])) {
                continue;
            }

            foreach ((array) $this->raw[$tag] as $i => $raw) {
                if (is_array($raw)) {
                    $k       = -1;
                    $key     = $col;
                    $subtype = '';

                    if (!empty($raw['type'])) {
                        $raw['type'] = array_map('strtolower', $raw['type']);

                        $combined = implode(',', array_diff($raw['type'], ['internet', 'pref']));
                        $combined = strtoupper($combined);

                        if (!empty($typemap[$combined])) {
                            $subtype = $typemap[$combined];
                        }
                        else if (!empty($typemap[$raw['type'][++$k]])) {
                            $subtype = $typemap[$raw['type'][$k]];
                        }
                        else {
                            $subtype = $raw['type'][$k];
                        }

                        while ($k < count($raw['type']) && ($subtype == 'internet' || $subtype == 'pref')) {
                            $k++;
                            if (!empty($raw['type'][$k])) {
                                if (!empty($typemap[$raw['type'][$k]])) {
                                    $subtype = $typemap[$raw['type'][$k]];
                                }
                                else {
                                    $subtype = $raw['type'][$k];
                                }
                            }
                        }
                    }

                    // read vcard 2.1 subtype
                    if (!$subtype) {
                        foreach ($raw as $k => $v) {
                            if (!is_numeric($k) && $v === true && ($k = strtolower($k))
                                && !in_array($k, ['pref', 'internet', 'voice', 'base64'])
                            ) {
                                $k_uc    = strtoupper($k);
                                $subtype = $typemap[$k_uc] ?: $k;
                                break;
                            }
                        }
                    }

                    // force subtype if none set
                    if (!$subtype && preg_match('/^(email|phone|address|website)/', $key)) {
                        $subtype = 'other';
                    }

                    if ($subtype) {
                        $key .= ':' . $subtype;
                    }

                    // split ADR values into assoc array
                    if ($tag == 'ADR') {
                        if (isset($raw[2])) {
                            $value['street'] = $raw[2];
                        }
                        if (isset($raw[3])) {
                            $value['locality'] = $raw[3];
                        }
                        if (isset($raw[4])) {
                            $value['region'] = $raw[4];
                        }
                        if (isset($raw[5])) {
                            $value['zipcode'] = $raw[5];
                        }
                        if (isset($raw[6])) {
                            $value['country'] = $raw[6];
                        }
                        $out[$key][] = $value;
                    }
                    // support vCard v4 date format (YYYYMMDD)
                    else if ($tag == 'BDAY' && preg_match('/^([12][90]\d\d)([01]\d)([0123]\d)$/', $raw[0], $m)) {
                        $out[$key][] = sprintf('%04d-%02d-%02d', intval($m[1]), intval($m[2]), intval($m[3]));
                    }
                    else {
                        $out[$key][] = $raw[0];
                    }
                }
                else {
                    $out[$col][] = $raw;
                }
            }
        }

        // handle special IM fields as used by Apple
        foreach ($this->immap as $tag => $type) {
            if (!empty($this->raw[$tag])) {
                foreach ((array) $this->raw[$tag] as $i => $raw) {
                    $out['im:'.$type][] = $raw[0];
                }
            }
        }

        // copy photo data
        if (!empty($this->raw['PHOTO'])) {
            $out['photo'] = $this->raw['PHOTO'][0][0];
        }

        return $out;
    }

    /**
     * Convert the data structure into a vcard 3.0 string
     *
     * @param bool $folder Use RFC2425 folding
     *
     * @return string vCard output
     */
    public function export($folded = true)
    {
        $vcard = self::vcard_encode($this->raw);
        return $folded ? self::rfc2425_fold($vcard) : $vcard;
    }

    /**
     * Clear the given fields in the loaded vcard data
     *
     * @param array List of field names to be reset
     */
    public function reset($fields = [])
    {
        if (empty($fields)) {
            $fields = ['FN', 'N', 'ORG', 'NICKNAME', 'EMAIL', 'ADR', 'BDAY'];
            $fields = array_merge(array_values(self::$fieldmap), array_keys($this->immap), $fields);
        }

        foreach ($fields as $f) {
            unset($this->raw[$f]);
        }

        if (empty($this->raw['N'])) {
            $this->raw['N'] = [['','','','','']];
        }

        if (empty($this->raw['FN'])) {
            $this->raw['FN'] = [];
        }

        $this->email = [];
    }

    /**
     * Setter for address record fields
     *
     * @param string $field Field name
     * @param mixed  $value Field value
     * @param string $type  Type/section name
     */
    public function set($field, $value, $type = 'HOME')
    {
        $field   = strtolower($field);
        $type_uc = strtoupper((string) $type);

        switch ($field) {
        case 'name':
        case 'displayname':
            $this->raw['FN'][0][0] = $this->displayname = $value;
            break;

        case 'surname':
            $this->raw['N'][0][0] = $this->surname = $value;
            break;

        case 'firstname':
            $this->raw['N'][0][1] = $this->firstname = $value;
            break;

        case 'middlename':
            $this->raw['N'][0][2] = $this->middlename = $value;
            break;

        case 'prefix':
            $this->raw['N'][0][3] = $value;
            break;

        case 'suffix':
            $this->raw['N'][0][4] = $value;
            break;

        case 'nickname':
            $this->raw['NICKNAME'][0][0] = $this->nickname = $value;
            break;

        case 'organization':
            $this->raw['ORG'][0][0] = $this->organization = $value;
            break;

        case 'photo':
            if (strpos($value, 'http:') === 0) {
                // TODO: fetch file from URL and save it locally?
                $this->raw['PHOTO'][0] = [0 => $value, 'url' => true];
            }
            else {
                $this->raw['PHOTO'][0] = [0 => $value, 'base64' => (bool) preg_match('![^a-z0-9/=+-]!i', $value)];
            }
            break;

        case 'email':
            $this->raw['EMAIL'][] = [0 => $value, 'type' => array_filter(['INTERNET', $type_uc])];
            $this->email[] = $value;
            break;

        case 'im':
            // save IM subtypes into extension fields
            $typemap = array_flip($this->immap);
            if (!empty($typemap[strtolower($type)])) {
                $field = $typemap[strtolower($type)];
                $this->raw[$field][] = [$value];
            }
            break;

        case 'birthday':
        case 'anniversary':
            if (($val = rcube_utils::anytodatetime($value)) && !empty(self::$fieldmap[$field])) {
                $fn = self::$fieldmap[$field];
                $this->raw[$fn][] = [0 => $val->format('Y-m-d'), 'value' => ['date']];
            }
            break;

        case 'address':
            if (!empty($this->addresstypemap[$type_uc])) {
                $type = $this->addresstypemap[$type_uc];
            }

            if (empty($value[0])) {
                $value = [
                    '',
                    '',
                    $value['street'] ?? '',
                    $value['locality'] ?? '',
                    $value['region'] ?? '',
                    $value['zipcode'] ?? '',
                    $value['country'] ?? '',
                ];
            }

            // fall through if not empty
            if (!strlen(@implode('', $value))) {
                break;
            }

        default:
            if ($field == 'phone' && !empty($this->phonetypemap[$type_uc])) {
                $type = $this->phonetypemap[$type_uc];
            }

            if (!empty(self::$fieldmap[$field])) {
                $tag = self::$fieldmap[$field];

                if (is_array($value) || (is_string($value) && strlen($value))) {
                    $this->raw[$tag][] = (array) $value;
                    if ($type) {
                        $index    = count($this->raw[$tag]) - 1;
                        $typemap  = array_flip($this->typemap);
                        $type_val = !empty($typemap[$type_uc]) ? $typemap[$type_uc] : $type;
                        $this->raw[$tag][$index]['type'] = explode(',', $type_val);
                    }
                }
                else {
                    unset($this->raw[$tag]);
                }
            }

            break;
        }
    }

    /**
     * Setter for individual vcard properties
     *
     * @param string $tag    VCard tag name
     * @param array  $value  Value-set of this vcard property
     * @param bool   $append Set to true if the value-set should be appended
     *                       instead of replacing any existing value-set
     */
    public function set_raw($tag, $value, $append = false)
    {
        $index = $append && isset($this->raw[$tag]) ? count($this->raw[$tag]) : 0;
        $this->raw[$tag][$index] = (array) $value;
    }

    /**
     * Find index with the '$type' attribute
     *
     * @param string $field Field name
     *
     * @return int Field index having $type set
     */
    private function get_type_index($field)
    {
        $result = 0;
        if (!empty($this->raw[$field])) {
            foreach ((array) $this->raw[$field] as $i => $data) {
                if (isset($data['type']) && is_array($data['type']) && in_array_nocase('pref', $data['type'])) {
                    $result = $i;
                }
            }
        }

        return $result;
    }

    /**
     * Convert a whole vcard (array) to UTF-8.
     * If $force_charset is null, each member value that has a charset parameter will be converted
     */
    private static function charset_convert($card, $force_charset = null)
    {
        foreach ($card as $key => $node) {
            foreach ($node as $i => $subnode) {
                if (!is_array($subnode)) {
                    continue;
                }

                $charset = $force_charset;
                if (!$charset && isset($subnode['charset'][0])) {
                    $charset = $subnode['charset'][0];
                }

                if ($charset) {
                    foreach ($subnode as $j => $value) {
                        if (is_numeric($j) && is_string($value)) {
                            $card[$key][$i][$j] = rcube_charset::convert($value, $charset);
                        }
                    }
                    unset($card[$key][$i]['charset']);
                }
            }
        }

        return $card;
    }

    /**
     * Extends fieldmap definition
     *
     * @param array $map Field mapping definition
     */
    public function extend_fieldmap($map)
    {
        if (is_array($map)) {
            self::$fieldmap = array_merge($map, self::$fieldmap);
        }
    }

    /**
     * Factory method to import a vcard file
     *
     * @param string $data vCard file content
     *
     * @return rcube_vcard[] List of rcube_vcard objects
     */
    public static function import($data)
    {
        $out = [];

        if (($charset = self::detect_encoding($data)) && $charset != RCUBE_CHARSET) {
            $data = rcube_charset::convert($data, $charset);
            $data = preg_replace(['/^[\xFE\xFF]{2}/', '/^\xEF\xBB\xBF/', '/^\x00+/'], '', $data); // also remove BOM
            $charset = RCUBE_CHARSET;
        }

        $vcard_block    = '';
        $in_vcard_block = false;

        foreach (preg_split("/[\r\n]+/", $data) as $line) {
            if ($in_vcard_block && !empty($line)) {
                $vcard_block .= $line . "\n";
            }

            $line = trim($line);

            if (preg_match('/^END:VCARD$/i', $line)) {
                // parse vcard
                $obj = new rcube_vcard($vcard_block, $charset, false, self::$fieldmap);

                // FN and N is required by vCard format (RFC 2426)
                // on import we can be less restrictive, let's addressbook decide
                if (!empty($obj->displayname) || !empty($obj->surname) || !empty($obj->firstname) || !empty($obj->email)) {
                    $out[] = $obj;
                }

                $in_vcard_block = false;
            }
            else if (preg_match('/^BEGIN:VCARD$/i', $line)) {
                $vcard_block    = $line . "\n";
                $in_vcard_block = true;
            }
        }

        return $out;
    }

    /**
     * Normalize vcard data for better parsing
     *
     * @param string $vcard vCard block
     *
     * @return string Cleaned vcard block
     */
    public static function cleanup($vcard)
    {
        // convert Apple X-ABRELATEDNAMES into X-* fields for better compatibility
        $vcard = preg_replace_callback(
            '/item(\d+)\.(X-ABRELATEDNAMES)([^:]*?):(.*?)item\1.X-ABLabel:(?:_\$!<)?([\w() -]*)(?:>!\$_)?./s',
            ['rcube_vcard', 'x_abrelatednames_callback'],
            $vcard);

        // Cleanup
        $vcard = preg_replace(
            [
                // convert special types (like Skype) to normal type='skype' classes with this simple regex ;)
                '/item(\d+)\.(TEL|EMAIL|URL)([^:]*?):(.*?)item\1.X-ABLabel:(?:_\$!<)?([\w() -]*)(?:>!\$_)?./si',
                '/^item\d*\.X-AB.*$/mi',  // remove cruft like item1.X-AB*
                '/^item\d*\./mi',         // remove item1.ADR instead of ADR
                '/\n+/',                  // remove empty lines
                '/^(N:[^;\r\n]*)$/m',     // if N doesn't have any semicolons, add some
            ],
            [
                '\2;type=\5\3:\4',
                '',
                '',
                "\n",
                '\1;;;;',
            ],
            $vcard
        );

        // convert X-WAB-GENDER to X-GENDER
        if (preg_match('/X-WAB-GENDER:(\d)/', $vcard, $matches)) {
            $value = $matches[1] == '2' ? 'male' : 'female';
            $vcard = preg_replace('/X-WAB-GENDER:\d/', 'X-GENDER:' . $value, $vcard);
        }

        return $vcard;
    }

    /**
     * Apple X-ABRELATEDNAMES converter callback
     *
     * @param array $matches Matching entries
     *
     * @return string Replacement string
     */
    private static function x_abrelatednames_callback($matches)
    {
        return 'X-' . strtoupper($matches[5]) . $matches[3] . ':'. $matches[4];
    }

    /**
     * RFC2425 folding callback
     *
     * @param array $matches Matching entries
     *
     * @return string Replacement string
     */
    private static function rfc2425_fold_callback($matches)
    {
        // chunk_split string and avoid lines breaking multibyte characters
        $c = 71;
        $out = substr($matches[1], 0, $c);

        for ($n = $c; $c < strlen($matches[1]); $c++) {
            // break if length > 75 or multibyte character starts after position 71
            if ($n > 75 || ($n > 71 && ord($matches[1][$c]) >> 6 == 3)) {
                $out .= "\r\n ";
                $n = 0;
            }

            $out .= $matches[1][$c];
            $n++;
        }

        return $out;
    }

    /**
     * Apply RFC2425 folding to a vCard content
     *
     * @param string $val vCard content
     *
     * @return string Folded vCard string
     */
    public static function rfc2425_fold($val)
    {
        return preg_replace_callback('/([^\n]{72,})/', ['rcube_vcard', 'rfc2425_fold_callback'], $val);
    }

    /**
     * Decodes a vcard block (vcard 3.0 format, unfolded) into an array structure
     *
     * @param string $vcard vCard block to parse
     *
     * @return array Raw data structure
     */
    private static function vcard_decode($vcard)
    {
        // Perform RFC2425 line unfolding and split lines
        $vcard  = preg_replace(["/\r/", "/\n\s+/"], '', $vcard);
        $lines  = explode("\n", $vcard);
        $result = [];

        for ($i=0; $i < count($lines); $i++) {
            if (!($pos = strpos($lines[$i], ':'))) {
                continue;
            }

            $prefix = substr($lines[$i], 0, $pos);
            $data   = substr($lines[$i], $pos+1);

            if (preg_match('/^(BEGIN|END)$/i', $prefix)) {
                continue;
            }

            // convert 2.1-style "EMAIL;internet;home:" to 3.0-style "EMAIL;TYPE=internet;TYPE=home:"
            if (
                !empty($result['VERSION'])
                && $result['VERSION'][0] == "2.1"
                && preg_match('/^([^;]+);([^:]+)/', $prefix, $regs2)
                && !preg_match('/^TYPE=/i', $regs2[2])
            ) {
                $prefix = $regs2[1];
                foreach (explode(';', $regs2[2]) as $prop) {
                    $prefix .= ';' . (strpos($prop, '=') ? $prop : 'TYPE='.$prop);
                }
            }

            if (preg_match_all('/([^\\;]+);?/', $prefix, $regs2)) {
                $entry = [];
                $field = strtoupper($regs2[1][0]);
                $enc   = null;

                foreach ($regs2[1] as $attrid => $attr) {
                    $attr = preg_replace('/[\s\t\n\r\0\x0B]/', '', $attr);

                    if ((@list($key, $value) = explode('=', $attr)) && $value) {
                        if ($key == 'ENCODING') {
                            $value = strtoupper($value);
                            // add next line(s) to value string if QP line end detected
                            if ($value == 'QUOTED-PRINTABLE') {
                                while (preg_match('/=$/', $lines[$i])) {
                                    $data .= "\n" . $lines[++$i];
                                }
                            }
                            $enc = $value == 'BASE64' ? 'B' : $value;
                        }
                        else {
                            $lc_key = strtolower($key);
                            $value  = (array) self::vcard_unquote($value, ',');

                            if (array_key_exists($lc_key, $entry)) {
                                $entry[$lc_key] = array_merge((array) $entry[$lc_key], $value);
                            }
                            else {
                                $entry[$lc_key] = $value;
                            }
                        }
                    }
                    else if ($attrid > 0) {
                        $entry[strtolower($key)] = true;  // true means attr without =value
                    }
                }

                // decode value
                if ($enc || !empty($entry['base64'])) {
                    // save encoding type (#1488432)
                    if ($enc == 'B') {
                        $entry['encoding'] = 'B';
                        // should we use vCard 3.0 instead?
                        // $entry['base64'] = true;
                    }

                    $data = self::decode_value($data, $enc ?: 'base64');
                }
                else if ($field == 'PHOTO') {
                    // vCard 4.0 data URI, "PHOTO:data:image/jpeg;base64,..."
                    if (preg_match('/^data:[a-z\/_-]+;base64,/i', $data, $m)) {
                        $entry['encoding'] = $enc = 'B';
                        $data = substr($data, strlen($m[0]));
                        $data = self::decode_value($data, 'base64');
                    }
                }

                if ($enc != 'B' && empty($entry['base64'])) {
                    $data = self::vcard_unquote($data);
                }

                if (is_array($data) || (is_string($data) && strlen($data))) {
                    $entry = array_merge($entry, (array) $data);
                    $result[$field][] = $entry;
                }
            }
        }

        unset($result['VERSION']);

        return $result;
    }

    /**
     * Decode a given string with the encoding rule from ENCODING attributes
     *
     * @param string $value    String to decode
     * @param string $encoding Encoding type (quoted-printable and base64 supported)
     *
     * @return string Decoded 8bit value
     */
    private static function decode_value($value, $encoding)
    {
        switch (strtolower($encoding)) {
        case 'quoted-printable':
            self::$values_decoded = true;
            return quoted_printable_decode($value);

        case 'base64':
        case 'b':
            self::$values_decoded = true;
            return base64_decode($value);

        default:
            return $value;
        }
    }

    /**
     * Encodes an entry for storage in our database (vcard 3.0 format, unfolded)
     *
     * @param array $data Raw data structure to encode
     *
     * @return string vCard encoded string
     */
    static function vcard_encode($data)
    {
        $vcard = '';

        foreach ((array)$data as $type => $entries) {
            // valid N has 5 properties
            while ($type == "N" && is_array($entries[0]) && count($entries[0]) < 5) {
                $entries[0][] = "";
            }

            // make sure FN is not empty (required by RFC2426)
            if ($type == "FN" && empty($entries) && !empty($data['EMAIL'][0][0])) {
                $entries[0] = $data['EMAIL'][0][0];
            }

            foreach ((array)$entries as $entry) {
                $attr = '';
                if (is_array($entry)) {
                    $value = [];
                    foreach ($entry as $attrname => $attrvalues) {
                        if (is_int($attrname)) {
                            if (!empty($entry['base64']) || (!empty($entry['encoding']) && $entry['encoding'] == 'B')) {
                                $attrvalues = base64_encode($attrvalues);
                            }
                            $value[] = $attrvalues;
                        }
                        else if (is_bool($attrvalues)) {
                            // true means just a tag, not tag=value, as in PHOTO;BASE64:...
                            if ($attrvalues) {
                                // vCard v3 uses ENCODING=b (#1489183)
                                if ($attrname == 'base64') {
                                    $attr .= ";ENCODING=b";
                                }
                                else {
                                    $attr .= strtoupper(";$attrname");
                                }
                            }
                        }
                        else {
                            foreach ((array)$attrvalues as $attrvalue) {
                                $attr .= strtoupper(";$attrname=") . self::vcard_quote($attrvalue, ',');
                            }
                        }
                    }
                }
                else {
                    $value = $entry;
                }

                // skip empty entries
                if (self::is_empty($value)) {
                    continue;
                }

                $vcard .= self::vcard_quote($type) . $attr . ':' . self::vcard_quote($value) . self::$eol;
            }
        }

        return 'BEGIN:VCARD' . self::$eol . 'VERSION:3.0' . self::$eol . $vcard . 'END:VCARD';
    }

    /**
     * Join indexed data array to a vcard quoted string
     *
     * @param array  $str Field data
     * @param string $sep Separator
     *
     * @return string Joined and quoted string
     */
    public static function vcard_quote($str, $sep = ';')
    {
        if (is_array($str)) {
            $r = [];

            foreach ($str as $part) {
                $r[] = self::vcard_quote($part, $sep);
            }

            return(implode($sep, $r));
        }

        return strtr($str, ["\\" => "\\\\", "\r" => '', "\n" => '\n', $sep => "\\$sep"]);
    }

    /**
     * Split quoted string
     *
     * @param string $str vCard string to split
     * @param string $sep Separator char/string
     *
     * @return string|array Unquoted string or a list of strings if $sep was found
     */
    private static function vcard_unquote($str, $sep = ';')
    {
        // break string into parts separated by $sep
        if (!empty($sep)) {
            // Handle properly backslash escaping (#1488896)
            $rep1 = ["\\\\" => "\010", "\\$sep" => "\007"];
            $rep2 = ["\007" => "\\$sep", "\010" => "\\\\"];

            if (count($parts = explode($sep, strtr($str, $rep1))) > 1) {
                $result = [];
                foreach ($parts as $s) {
                    $result[] = self::vcard_unquote(strtr($s, $rep2));
                }

                return $result;
            }

            $str = trim(strtr($str, $rep2));
        }

        // some implementations (GMail) use non-standard backslash before colon (#1489085)
        // we will handle properly any backslashed character - removing dummy backslashes
        // return strtr($str, ["\r" => '', '\\\\' => '\\', '\n' => "\n", '\N' => "\n", '\,' => ',', '\;' => ';']);

        $str = str_replace("\r", '', $str);
        $pos = 0;

        while (($pos = strpos($str, "\\", $pos)) !== false) {
            $next = substr($str, $pos + 1, 1);
            if ($next == 'n' || $next == 'N') {
                $str = substr_replace($str, "\n", $pos, 2);
            }
            else {
                $str = substr_replace($str, '', $pos, 1);
            }

            $pos += 1;
        }

        return $str;
    }

    /**
     * Check if vCard entry is empty: empty string or an array with
     * all entries empty.
     *
     * @param string|array $value Attribute value
     *
     * @return bool True if the value is empty, False otherwise
     */
    private static function is_empty($value)
    {
        foreach ((array) $value as $v) {
            if (@strval($v) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns character set of a vCard input
     *
     * @param string $string Input string to test
     *
     * @return string Detected encoding
     */
    private static function detect_encoding($string)
    {
        // Extract the plain text from the vCard, so the detection is more accurate
        // This will for example exclude photos

        // Perform RFC2425 line unfolding and split lines
        $string = preg_replace(["/\r/", "/\n\s+/"], '', $string);
        $lines  = explode("\n", $string);
        $string = '';

        for ($i = 0, $len = count($lines); $i < $len; $i++) {
            if (!($pos = strpos($lines[$i], ':'))) {
                continue;
            }

            $prefix = substr($lines[$i], 0, $pos);

            // We remove \0 as so it works with UTF-16/UTF-32 encodings
            $prefix = str_replace("\0", '', $prefix);

            // Take only properties that are known to contain human-readable text
            if (!preg_match('/^(item\d+\.)?(N|FN|ORG|ADR|NOTE|TITLE|CATEGORIES)(;|$)/', $prefix)) {
                continue;
            }

            $data = substr($lines[$i], $pos + 1);

            if (preg_match('/;CHARSET=([a-z0-9-]+)/i', $prefix, $matches)) {
                // We assume there's only one charset in the input
                return $matches[1];
            }

            $matches = null;
            $enc = null;

            if (stripos($prefix, 'base64') || preg_match('/ENCODING=(QUOTED-PRINTABLE|B|BASE64)/i', $prefix, $matches)) {
                $enc = $matches ? strtoupper($matches[1]) : 'BASE64';
                // add next line(s) to value string if QP line end detected
                if ($enc == 'QUOTED-PRINTABLE') {
                    while (preg_match('/=$/', $lines[$i])) {
                        $data .= "\n" . $lines[++$i];
                    }
                }

                $data = self::decode_value($data, $enc);
            }

            if (!$enc || $enc == 'QUOTED-PRINTABLE') {
                $data = self::vcard_unquote($data);
            }

            if (is_array($data)) {
                $data = implode(' ', array_filter($data));
            }

            $string .= $data . ' ';

            // 100 KB should be enough for charset check
            if (strlen($string) > 100 * 1024) {
                break;
            }
        }

        return rcube_charset::check($string) ?: RCUBE_CHARSET;
    }
}
