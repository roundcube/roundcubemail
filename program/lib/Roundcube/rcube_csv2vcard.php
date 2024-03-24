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
 |   CSV to vCard data conversion                                        |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * CSV to vCard data converter
 *
 * @package    Framework
 * @subpackage Addressbook
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
        'anniversary'           => 'anniversary',
        'assistants_name'       => 'assistant',
        'assistants_phone'      => 'phone:assistant',
        'birthday'              => 'birthday',
        'business_city'         => 'locality:work',
        'business_countryregion' => 'country:work',
        'business_fax'          => 'phone:work,fax',
        'business_phone'        => 'phone:work',
        'business_phone_2'      => 'phone:work2',
        'business_postal_code'  => 'zipcode:work',
        'business_state'        => 'region:work',
        'business_street'       => 'street:work',
        //'business_street_2'     => '',
        //'business_street_3'     => '',
        'car_phone'             => 'phone:car',
        'categories'            => 'groups',
        //'children'              => '',
        'company'               => 'organization',
        //'company_main_phone'    => '',
        'department'            => 'department',
        'email_2_address'       => 'email:other',
        //'email_2_type'          => '',
        'email_3_address'       => 'email:other',
        //'email_3_type'          => '',
        'email_address'         => 'email:pref',
        //'email_type'            => '',
        'first_name'            => 'firstname',
        'gender'                => 'gender',
        'home_city'             => 'locality:home',
        'home_countryregion'    => 'country:home',
        'home_fax'              => 'phone:home,fax',
        'home_phone'            => 'phone:home',
        'home_phone_2'          => 'phone:home2',
        'home_postal_code'      => 'zipcode:home',
        'home_state'            => 'region:home',
        'home_street'           => 'street:home',
        //'home_street_2'         => '',
        //'home_street_3'         => '',
        //'initials'              => '',
        //'isdn'                  => '',
        'job_title'             => 'jobtitle',
        //'keywords'              => '',
        //'language'              => '',
        'last_name'             => 'surname',
        //'location'              => '',
        'managers_name'         => 'manager',
        'middle_name'           => 'middlename',
        //'mileage'               => '',
        'mobile_phone'          => 'phone:cell',
        'notes'                 => 'notes',
        //'office_location'       => '',
        'other_city'            => 'locality:other',
        'other_countryregion'   => 'country:other',
        'other_fax'             => 'phone:other,fax',
        'other_phone'           => 'phone:other',
        'other_postal_code'     => 'zipcode:other',
        'other_state'           => 'region:other',
        'other_street'          => 'street:other',
        //'other_street_2'        => '',
        //'other_street_3'        => '',
        'pager'                 => 'phone:pager',
        'primary_phone'         => 'phone:pref',
        //'profession'            => '',
        //'radio_phone'           => '',
        'spouse'                => 'spouse',
        'suffix'                => 'suffix',
        'title'                 => 'title',
        'web_page'              => 'website:homepage',

        // Thunderbird
        'birth_day'             => 'birthday-d',
        'birth_month'           => 'birthday-m',
        'birth_year'            => 'birthday-y',
        'display_name'          => 'displayname',
        'fax_number'            => 'phone:fax',
        'home_address'          => 'street:home',
        //'home_address_2'        => '',
        'home_country'          => 'country:home',
        'home_zipcode'          => 'zipcode:home',
        'mobile_number'         => 'phone:cell',
        'nickname'              => 'nickname',
        'organization'          => 'organization',
        'pager_number'          => 'phone:pager',
        'primary_email'         => 'email:pref',
        'secondary_email'       => 'email:other',
        'web_page_1'            => 'website:homepage',
        'web_page_2'            => 'website:other',
        'work_phone'            => 'phone:work',
        'work_address'          => 'street:work',
        //'work_address_2'        => '',
        'work_country'          => 'country:work',
        'work_zipcode'          => 'zipcode:work',
        'last'                  => 'surname',
        'first'                 => 'firstname',
        'work_city'             => 'locality:work',
        'work_state'            => 'region:work',
        'home_city_short'       => 'locality:home',
        'home_state_short'      => 'region:home',

        // Atmail
        'date_of_birth'         => 'birthday',
        // 'email'                 => 'email:pref',
        'home_mobile'           => 'phone:cell',
        'home_zip'              => 'zipcode:home',
        'info'                  => 'notes',
        'user_photo'            => 'photo',
        'url'                   => 'website:homepage',
        'work_company'          => 'organization',
        'work_dept'             => 'department',
        'work_fax'              => 'phone:work,fax',
        'work_mobile'           => 'phone:work,cell',
        'work_title'            => 'jobtitle',
        'work_zip'              => 'zipcode:work',
        'group'                 => 'groups',

        // GMail
        'groups'                => 'groups',
        'group_membership'      => 'groups',
        'given_name'            => 'firstname',
        'additional_name'       => 'middlename',
        'family_name'           => 'surname',
        'name'                  => 'displayname',
        'name_prefix'           => 'prefix',
        'name_suffix'           => 'suffix',

        // Format of Letter Hub test files from
        // https://letterhub.com/sample-csv-file-with-contacts/
        'company_name'          => 'organization',
        'address'               => 'street:home',
        'city'                  => 'locality:home',
        //'county'                => '',
        'state'                 => 'region:home',
        'zip'                   => 'zipcode:home',
        'phone1'                => 'phone:home',
        'phone'                 => 'phone:work',
        'email'                 => 'email:home',
    ];

    /**
     * CSV label to text mapping for English
     *
     * @var array
     */
    protected $label_map = [
        // MS Outlook 2010
        'anniversary'       => "Anniversary",
        'assistants_name'   => "Assistant's Name",
        'assistants_phone'  => "Assistant's Phone",
        'birthday'          => "Birthday",
        'business_city'     => "Business City",
        'business_countryregion' => "Business Country/Region",
        'business_fax'      => "Business Fax",
        'business_phone'    => "Business Phone",
        'business_phone_2'  => "Business Phone 2",
        'business_postal_code' => "Business Postal Code",
        'business_state'    => "Business State",
        'business_street'   => "Business Street",
        //'business_street_2' => "Business Street 2",
        //'business_street_3' => "Business Street 3",
        'car_phone'         => "Car Phone",
        'categories'        => "Categories",
        //'children'          => "Children",
        'company'           => "Company",
        //'company_main_phone' => "Company Main Phone",
        'department'        => "Department",
        //'directory_server'  => "Directory Server",
        'email_2_address'   => "E-mail 2 Address",
        //'email_2_type'      => "E-mail 2 Type",
        'email_3_address'   => "E-mail 3 Address",
        //'email_3_type'      => "E-mail 3 Type",
        'email_address'     => "E-mail Address",
        //'email_type'        => "E-mail Type",
        'first_name'        => "First Name",
        'gender'            => "Gender",
        'home_city'         => "Home City",
        'home_countryregion' => "Home Country/Region",
        'home_fax'          => "Home Fax",
        'home_phone'        => "Home Phone",
        'home_phone_2'      => "Home Phone 2",
        'home_postal_code'  => "Home Postal Code",
        'home_state'        => "Home State",
        'home_street'       => "Home Street",
        //'home_street_2'     => "Home Street 2",
        //'home_street_3'     => "Home Street 3",
        //'initials'          => "Initials",
        //'isdn'              => "ISDN",
        'job_title'         => "Job Title",
        //'keywords'          => "Keywords",
        //'language'          => "Language",
        'last_name'         => "Last Name",
        //'location'          => "Location",
        'managers_name'     => "Manager's Name",
        'middle_name'       => "Middle Name",
        //'mileage'           => "Mileage",
        'mobile_phone'      => "Mobile Phone",
        'notes'             => "Notes",
        //'office_location'   => "Office Location",
        'other_city'        => "Other City",
        'other_countryregion' => "Other Country/Region",
        'other_fax'         => "Other Fax",
        'other_phone'       => "Other Phone",
        'other_postal_code' => "Other Postal Code",
        'other_state'       => "Other State",
        'other_street'      => "Other Street",
        //'other_street_2'    => "Other Street 2",
        //'other_street_3'    => "Other Street 3",
        'pager'             => "Pager",
        'primary_phone'     => "Primary Phone",
        //'profession'        => "Profession",
        //'radio_phone'       => "Radio Phone",
        'spouse'            => "Spouse",
        'suffix'            => "Suffix",
        'title'             => "Title",
        'web_page'          => "Web Page",

        // Thunderbird
        'birth_day'         => "Birth Day",
        'birth_month'       => "Birth Month",
        'birth_year'        => "Birth Year",
        'display_name'      => "Display Name",
        'fax_number'        => "Fax Number",
        'home_address'      => "Home Address",
        //'home_address_2'    => "Home Address 2",
        'home_country'      => "Home Country",
        'home_zipcode'      => "Home ZipCode",
        'mobile_number'     => "Mobile Number",
        'nickname'          => "Nickname",
        'organization'      => "Organization",
        'pager_number'      => "Pager Number",
        'primary_email'     => "Primary Email",
        'secondary_email'   => "Secondary Email",
        'web_page_1'        => "Web Page 1",
        'web_page_2'        => "Web Page 2",
        'work_phone'        => "Work Phone",
        'work_address'      => "Work Address",
        //'work_address_2'    => "Work Address 2",
        'work_city'         => "Work City",
        'work_country'      => "Work Country",
        'work_state'        => "Work State",
        'work_zipcode'      => "Work ZipCode",

        // Atmail
        'date_of_birth'     => "Date of Birth",
        'email'             => "Email",
        //'email_2'         => "Email2",
        //'email_3'         => "Email3",
        //'email_4'         => "Email4",
        //'email_5'         => "Email5",
        'home_mobile'       => "Home Mobile",
        'home_zip'          => "Home Zip",
        'info'              => "Info",
        'user_photo'        => "User Photo",
        'url'               => "URL",
        'work_company'      => "Work Company",
        'work_dept'         => "Work Dept",
        'work_fax'          => "Work Fax",
        'work_mobile'       => "Work Mobile",
        'work_title'        => "Work Title",
        'work_zip'          => "Work Zip",
        'group'             => "Group",

        // GMail
        'groups'            => "Groups",
        'group_membership'  => "Group Membership",
        'given_name'        => "Given Name",
        'additional_name'   => "Additional Name",
        'family_name'       => "Family Name",
        'name'              => "Name",
        'name_prefix'       => "Name Prefix",
        'name_suffix'       => "Name Suffix",
    ];

    /**
     * Special fields map for GMail format
     *
     * @var array
     */
    protected $gmail_label_map = [
        'E-mail' => [
            'Value' => [
                'home'  => 'email:home',
                'work'  => 'email:work',
                'other' => 'email:other',
                ''      => 'email:other',
            ],
        ],
        'Phone' => [
            'Value' => [
                'home'    => 'phone:home',
                'homefax' => 'phone:homefax',
                'main'    => 'phone:pref',
                'pager'   => 'phone:pager',
                'mobile'  => 'phone:cell',
                'work'    => 'phone:work',
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
                'profile'  => 'website:profile',
                'blog'     => 'website:blog',
                'homepage' => 'website:homepage',
                'work'     => 'website:work',
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
        // Localize fields map
        if ($lang && $lang != 'en_US') {
            if (file_exists(RCUBE_LOCALIZATION_DIR . "$lang/csv2vcard.inc")) {
                include RCUBE_LOCALIZATION_DIR . "$lang/csv2vcard.inc";
            }

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
     * @return array Field mapping info (dry run only)
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
    }

    /**
     * Set field mapping info
     *
     * @param array $elements Field mapping
     */
    public function set_map($elements)
    {
        // sanitize input
        $elements = array_filter($elements, function($val) {
                return in_array($val, $this->csv2vcard_map);
            });

        $this->map = $elements;
    }

    /**
     * Set field mapping info
     *
     * @return array Array of vcard fields and localized names
     */
    public function get_fields()
    {
        // get all vcard fields
        $fields            = array_unique($this->csv2vcard_map);
        $local_field_names = $this->local_label_map ?: $this->label_map;

        // translate with the local map
        $map = [];
        foreach ($fields as $csv => $vcard) {
            if ($vcard == '_auto_') {
                continue;
            }

            $map[$vcard] = $local_field_names[$csv];
        }

        // small fix to prevent "Groups" displaying as "Categories"
        $map['groups'] = $local_field_names['groups'];

        asort($map);

        return $map;
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
                if ($label && !empty($this->csv2vcard_map[$label])) {
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
                while (($_key = "$key $num - Type") && ($found = array_search($_key, $elements)) !== false) {
                    $type = $contents[$found];
                    $type = preg_replace('/[^a-z]/', '', strtolower($type));

                    foreach ($items as $item_key => $vcard_fields) {
                        $_key = "$key $num - $item_key";
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
                    $contact[$name]   = (array) $contact[$name];
                    $contact[$name][] = $value;
                }
                else {
                   $contact[$name] = $value;
                }
            }
        }

        if (empty($contact)) {
            return;
        }

        // Handle special values
        if (!empty($contact['birthday-d']) && !empty($contact['birthday-m']) && !empty($contact['birthday-y'])) {
            $contact['birthday'] = $contact['birthday-y'] .'-' .$contact['birthday-m'] . '-' . $contact['birthday-d'];
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
                $contact['address:'.$name[1]][$name[0]] = $value;
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
            }
            else {
                $vcard->set($name[0], $value, $name[1] ?? null);
            }
        }

        // add to the list
        $this->vcards[] = $vcard;
    }
}
