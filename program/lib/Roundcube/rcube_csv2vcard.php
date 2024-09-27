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
 |   CSV to vCard data conversion                                        |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * CSV to vCard data converter
 */
class rcube_csv2vcard
{
    /**
     * CSV label to text mapping for English read from localization
     *
     * @var array
     */
    protected $label_map = [];

    /**
     * Special fields map for GMail format
     *
     * @var array
     */
    protected $gmail_label_map = [
        'E-mail' => [
            'Value' => [
                'home' => 'email:home',
                'work' => 'email:work',
                'other' => 'email:other',
                '' => 'email:other',
            ],
        ],
        'Phone' => [
            'Value' => [
                'home' => 'phone:home',
                'homefax' => 'phone:homefax',
                'main' => 'phone:main',
                'pager' => 'phone:pager',
                'mobile' => 'phone:mobile',
                'work' => 'phone:work',
                'workfax' => 'phone:workfax',
            ],
        ],
        'Relation' => [
            'Value' => [
                'spouse' => 'spouse',
            ],
        ],
        'Website' => [
            'Value' => [
                'profile' => 'website:profile',
                'blog' => 'website:blog',
                'homepage' => 'website:homepage',
                'work' => 'website:work',
            ],
        ],
        'Address' => [
            'Street' => [
                'home' => 'street:home',
                'work' => 'street:work',
            ],
            'City' => [
                'home' => 'locality:home',
                'work' => 'locality:work',
            ],
            'Region' => [
                'home' => 'region:home',
                'work' => 'region:work',
            ],
            'Postal Code' => [
                'home' => 'zipcode:home',
                'work' => 'zipcode:work',
            ],
            'Country' => [
                'home' => 'country:home',
                'work' => 'country:work',
            ],
        ],
        'Organization' => [
            'Name' => [
                '' => 'organization',
            ],
            'Title' => [
                '' => 'jobtitle',
            ],
            'Department' => [
                '' => 'department',
            ],
        ],
    ];

    /** @var rcube_vcard[] List of contacts as vCards */
    protected $vcards = [];

    /** @var array Field mapping */
    protected $map = [];

    /**
     * Class constructor
     *
     * @param string $lang File language
     */
    public function __construct($lang = 'en_US')
    {
        $this->label_map = self::read_localization_file(RCUBE_LOCALIZATION_DIR . 'en_US/csv2vcard.inc');

        // Localize fields map
        if ($lang != 'en_US' && is_dir(RCUBE_LOCALIZATION_DIR . $lang)) {
            $map = self::read_localization_file(RCUBE_LOCALIZATION_DIR . $lang . '/csv2vcard.inc');

            if (!empty($map)) {
                $this->label_map = array_merge_recursive($this->label_map, $map);
            }
        }
    }

    /**
     * Import contacts from CSV file
     *
     * @param string $csv       Content of the CSV file
     * @param bool   $dry_run   Generate automatic field mapping
     * @param bool   $skip_head Skip header line
     *
     * @return array|null Field mapping info (dry run only)
     */
    public function import($csv, $dry_run = false, $skip_head = true)
    {
        // convert to UTF-8 (supports default_charset and RCUBE_CHARSET as input)
        // TODO: If the input charset is invalid we should probably just abort here
        if ($charset = rcube_charset::check($csv)) {
            $csv = rcube_charset::convert($csv, $charset);
        }

        $csv = preg_replace(['/^[\xFE\xFF]{2}/', '/^\xEF\xBB\xBF/', '/^\x00+/'], '', $csv); // also remove BOM

        // Split CSV file into lines
        $lines = rcube_utils::explode_quoted_string('[\r\n]+', $csv);

        // Parse first 2 lines of file to identify fields
        // 2 lines because for gmail CSV we need to get the value from the "Type" fields to identify which is which
        if (empty($this->map)) {
            $this->parse_header(array_slice($lines, 0, 2));
        }

        // Parse the fields
        foreach ($lines as $n => $line) {
            $elements = $this->parse_line($line);

            if ($dry_run) {
                return ['source' => $elements, 'destination' => $this->map];
            }

            if (empty($elements)) {
                continue;
            }

            // first line is the headers so do not import unless explicitly set
            if (!$skip_head || $n > 0) {
                $this->csv_to_vcard($elements);
            }
        }

        return null;
    }

    /**
     * Set field mapping info
     *
     * @param array $elements Field mapping
     */
    public function set_map($elements, $available)
    {
        // sanitize input
        $elements = array_filter($elements, static function ($val) use ($available) {
            return in_array($val, $available);
        });

        $this->map = $elements;
    }

    /**
     * Export vCards
     *
     * @return array rcube_vcard List of vcards
     */
    public function export()
    {
        return $this->vcards;
    }

    /**
     * Parse CSV file line
     *
     * @param string $line Line of text from CSV file
     *
     * @return array CSV data extracted from the line
     */
    protected function parse_line($line)
    {
        $line = trim($line);
        if (empty($line)) {
            return [];
        }

        $fields = str_getcsv($line);

        return $fields;
    }

    /**
     * Parse CSV header line, detect fields mapping
     *
     * @param array $lines One or two header lines in CSV file
     */
    protected function parse_header($lines)
    {
        $elements = $this->parse_line($lines[0]);

        if (count($lines) == 2) {
            // first line of contents needed to properly identify fields in gmail CSV
            $contents = $this->parse_line($lines[1]);
        }

        $size = count($elements);

        // check labels
        for ($i = 0; $i < $size; $i++) {
            if ($field = self::search_map($elements[$i], $this->label_map)) {
                $this->map[$i] = $field;
            }
        }

        if (!empty($contents)) {
            foreach ($this->gmail_label_map as $key => $items) {
                $num = 1;
                // @phpstan-ignore-next-line
                while (($_key = "{$key} {$num} - Type") && ($found = array_search($_key, $elements)) !== false) {
                    $type = $contents[$found];
                    $type = preg_replace('/[^a-z]/', '', strtolower($type));

                    foreach ($items as $item_key => $vcard_fields) {
                        $_key = "{$key} {$num} - {$item_key}";
                        if (($found = array_search($_key, $elements)) !== false) {
                            $this->map[$found] = $vcard_fields[$type];
                        }
                    }

                    $num++;
                }
            }
        }
    }

    /**
     * Convert CSV data row to vCard
     *
     * @param array $data CSV data array
     */
    protected function csv_to_vcard($data)
    {
        $contact = [];

        foreach ($this->map as $idx => $name) {
            if ($name == '_auto_') {
                continue;
            }

            $value = $data[$idx];
            if ($value !== null && $value !== '') {
                if (!empty($contact[$name])) {
                    $contact[$name] = (array) $contact[$name];
                    $contact[$name][] = $value;
                } else {
                    $contact[$name] = $value;
                }
            }
        }

        if (empty($contact)) {
            return;
        }

        // Handle special values
        if (!empty($contact['birthday-d']) && !empty($contact['birthday-m']) && !empty($contact['birthday-y'])) {
            $contact['birthday'] = $contact['birthday-y'] . '-' . $contact['birthday-m'] . '-' . $contact['birthday-d'];
        }

        if (!empty($contact['groups'])) {
            // categories/groups separator in vCard is ',' not ';'
            $contact['groups'] = str_replace(';', ',', $contact['groups']);

            // remove "* " added by GMail
            $contact['groups'] = str_replace('* ', '', $contact['groups']);
            // replace strange delimiter added by GMail
            $contact['groups'] = str_replace(' ::: ', ',', $contact['groups']);
        }

        // Empty dates, e.g. "0/0/00", "0000-00-00 00:00:00"
        foreach (['birthday', 'anniversary'] as $key) {
            if (!empty($contact[$key])) {
                $date = preg_replace('/[0[:^word:]]/', '', $contact[$key]);
                if (empty($date)) {
                    unset($contact[$key]);
                }
            }
        }

        if (!empty($contact['gender']) && ($gender = strtolower($contact['gender']))) {
            if (!in_array($gender, ['male', 'female'])) {
                unset($contact['gender']);
            }
        }

        // Convert address(es) to rcube_vcard data
        foreach ($contact as $idx => $value) {
            $name = explode(':', $idx);
            if (in_array($name[0], ['street', 'locality', 'region', 'zipcode', 'country'])) {
                $contact['address:' . $name[1]][$name[0]] = $value;
                unset($contact[$idx]);
            }
        }

        // Create vcard object
        $vcard = new rcube_vcard();
        foreach ($contact as $name => $value) {
            $name = explode(':', $name);
            if (is_array($value) && $name[0] != 'address') {
                foreach ((array) $value as $val) {
                    $vcard->set($name[0], $val, $name[1] ?? null);
                }
            } else {
                $vcard->set($name[0], $value, $name[1] ?? null);
            }
        }

        // add to the list
        $this->vcards[] = $vcard;
    }

    /**
     * Load localization file
     *
     * @param string $file  File location
     * @param array  $texts Additional texts to merge with
     *
     * @return array Localization csv2vcard map
     */
    protected static function read_localization_file($file, $texts = [])
    {
        if (is_file($file) && is_readable($file)) {
            $map = [];

            // use buffering to handle empty lines/spaces after closing PHP tag
            ob_start();
            require $file;
            ob_end_clean();

            // @phpstan-ignore-next-line
            if (!empty($map)) {
                $texts = array_merge_recursive($texts, $map);
            }
        }

        return $texts;
    }

    /**
     * Search csv2vcard mapping array
     *
     * @param string $needle Field name to search for
     * @param array  $map    Field map to be searched
     *
     * @return string|null vcard field id
     */
    protected static function search_map($needle, $map)
    {
        $result = null;
        foreach ($map as $key => $headings) {
            if (array_search($needle, $headings) !== false) {
                $result = $key;
                break;
            }
        }

        return $result;
    }
}
