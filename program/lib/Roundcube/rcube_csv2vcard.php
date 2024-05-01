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
     * CSV to vCard fields mapping
     *
     * @var array
     */
    protected $csv2vcard_map = [
        // MS Outlook 2010
        'anniversary' => 'anniversary',
        'assistants_name' => 'assistant',
        'assistants_phone' => 'phone:assistant',
        'birthday' => 'birthday',
        'business_city' => 'locality:work',
        'business_countryregion' => 'country:work',
        'business_fax' => 'phone:workfax',
        'business_phone' => 'phone:work',
        'business_phone_2' => 'phone:work2',
        'business_postal_code' => 'zipcode:work',
        'business_state' => 'region:work',
        'business_street' => 'street:work',
        // 'business_street_2'     => '',
        // 'business_street_3'     => '',
        'car_phone' => 'phone:car',
        'categories' => 'groups',
        // 'children'              => '',
        'company' => 'organization',
        // 'company_main_phone'    => '',
        'department' => 'department',
        'email_2_address' => 'email:other',
        // 'email_2_type'          => '',
        'email_3_address' => 'email:other',
        // 'email_3_type'          => '',
        'email_address' => 'email:other',
        // 'email_type'            => '',
        'first_name' => 'firstname',
        'gender' => 'gender',
        'home_city' => 'locality:home',
        'home_countryregion' => 'country:home',
        'home_fax' => 'phone:homefax',
        'home_phone' => 'phone:home',
        'home_phone_2' => 'phone:home2',
        'home_postal_code' => 'zipcode:home',
        'home_state' => 'region:home',
        'home_street' => 'street:home',
        // 'home_street_2'         => '',
        // 'home_street_3'         => '',
        // 'initials'              => '',
        // 'isdn'                  => '',
        'job_title' => 'jobtitle',
        // 'keywords'              => '',
        // 'language'              => '',
        'last_name' => 'surname',
        // 'location'              => '',
        'managers_name' => 'manager',
        'middle_name' => 'middlename',
        // 'mileage'               => '',
        'mobile_phone' => 'phone:mobile',
        'notes' => 'notes',
        // 'office_location'       => '',
        'other_city' => 'locality:other',
        'other_countryregion' => 'country:other',
        'other_fax' => 'phone:other',
        'other_phone' => 'phone:other',
        'other_postal_code' => 'zipcode:other',
        'other_state' => 'region:other',
        'other_street' => 'street:other',
        // 'other_street_2'        => '',
        // 'other_street_3'        => '',
        'pager' => 'phone:pager',
        'primary_phone' => 'phone:main',
        // 'profession'            => '',
        // 'radio_phone'           => '',
        'spouse' => 'spouse',
        'suffix' => 'suffix',
        'title' => 'prefix',
        'web_page' => 'website:homepage',

        // Thunderbird
        'birth_day' => 'birthday-d',
        'birth_month' => 'birthday-m',
        'birth_year' => 'birthday-y',
        'display_name' => 'name',
        'fax_number' => 'phone:homefax',
        'home_address' => 'street:home',
        // 'home_address_2'        => '',
        'home_country' => 'country:home',
        'home_zipcode' => 'zipcode:home',
        'mobile_number' => 'phone:mobile',
        'nickname' => 'nickname',
        'organization' => 'organization',
        'pager_number' => 'phone:pager',
        'primary_email' => 'email:home',
        'secondary_email' => 'email:other',
        'web_page_1' => 'website:homepage',
        'web_page_2' => 'website:other',
        'work_phone' => 'phone:work',
        'work_address' => 'street:work',
        // 'work_address_2'        => '',
        'work_country' => 'country:work',
        'work_zipcode' => 'zipcode:work',
        'last' => 'surname',
        'first' => 'firstname',
        'work_city' => 'locality:work',
        'work_state' => 'region:work',
        'home_city_short' => 'locality:home',
        'home_state_short' => 'region:home',

        // Atmail
        'date_of_birth' => 'birthday',
        // 'email'                 => 'email:pref',
        'home_mobile' => 'phone:mobile',
        'home_zip' => 'zipcode:home',
        'info' => 'notes',
        'user_photo' => 'photo',
        'url' => 'website:homepage',
        'work_company' => 'organization',
        'work_dept' => 'department',
        'work_fax' => 'phone:workfax',
        'work_mobile' => 'phone:other',
        'work_title' => 'jobtitle',
        'work_zip' => 'zipcode:work',
        'group' => 'groups',

        // GMail
        'groups' => 'groups',
        'group_membership' => 'groups',
        'given_name' => 'firstname',
        'additional_name' => 'middlename',
        'family_name' => 'surname',
        'name' => 'name',
        'name_prefix' => 'prefix',
        'name_suffix' => 'suffix',

        // Format of Letter Hub test files from
        // https://letterhub.com/sample-csv-file-with-contacts/
        'company_name' => 'organization',
        'address' => 'street:home',
        'city' => 'locality:home',
        // 'county'                => '',
        'state' => 'region:home',
        'zip' => 'zipcode:home',
        'phone1' => 'phone:home',
        'phone' => 'phone:work',
        'email' => 'email:home',

        // roundcube fields
        'email_home' => 'email:home',
        'email_work' => 'email:work',
        'email_other' => 'email:other',
        'phone_video' => 'phone:video',
        'maidenname' => 'maidenname',
        'im_aim' => 'im:aim',
        'im_icq' => 'im:icq',
        'im_jabber' => 'im:jabber',
        'im_msn' => 'im:msn',
        'im_other' => 'im:other',
        'im_skype' => 'im:skype',
        'im_yahoo' => 'im:yahoo',
        'web_blog' => 'website:blog',
        'web_home' => 'website:homepage',
        'web_other' => 'website:other',
        'web_profile' => 'website:profile',
        'web_work' => 'website:work',
    ];

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

    /** @var array Localized labels map */
    protected $local_label_map = [];

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
                $this->local_label_map = array_merge($this->label_map, $map);
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
    public function set_map($elements)
    {
        // sanitize input
        $elements = array_filter($elements, function ($val) {
            return in_array($val, $this->csv2vcard_map);
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

        $fields = str_getcsv($line, ',', '"', '\\');

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

        $label_map = array_flip($this->label_map);
        $local_label_map = array_flip($this->local_label_map);

        if (count($lines) == 2) {
            // first line of contents needed to properly identify fields in gmail CSV
            $contents = $this->parse_line($lines[1]);
        }

        $map1 = [];
        $map2 = [];
        $size = count($elements);

        // check English labels
        for ($i = 0; $i < $size; $i++) {
            if (!empty($label_map[$elements[$i]])) {
                $label = $label_map[$elements[$i]];
                if (!empty($this->csv2vcard_map[$label])) {
                    $map1[$i] = $this->csv2vcard_map[$label];
                }
            }
        }

        // check localized labels
        if (!empty($local_label_map)) {
            for ($i = 0; $i < $size; $i++) {
                $label = $local_label_map[$elements[$i]];

                // special localization label
                if ($label && $label[0] == '_') {
                    $label = substr($label, 1);
                }

                if ($label && !empty($this->csv2vcard_map[$label])) {
                    $map2[$i] = $this->csv2vcard_map[$label];
                }
            }
        }

        // If nothing recognized fallback to simple non-localized labels
        if (empty($map1) && empty($map2)) {
            for ($i = 0; $i < $size; $i++) {
                $label = str_replace(' ', '_', strtolower($elements[$i]));
                if (!empty($this->csv2vcard_map[$label])) {
                    $map1[$i] = $this->csv2vcard_map[$label];
                }
            }
        }

        $this->map = count($map1) >= count($map2) ? $map1 : $map2;

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

        if (!empty($contact['gender'])) {
            $gender = strtolower($contact['gender']);
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
                $texts = array_merge($texts, $map);
            }
        }

        return $texts;
    }
}
