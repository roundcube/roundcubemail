<?php

/**
 * BIMI
 *
 * Plugin to display Display Brand Indicators for Message Identification (BIMI) icons
 * for contacts/addresses that do not have a photo image.
 *
 * @license GNU GPLv3+
 * @author Craig Andrews <candrews@integralblue.com>
 * @website http://roundcube.net
 */
class bimi extends rcube_plugin
{
    public $task = 'addressbook';


    /**
     * Plugin initialization.
     */
    function init()
    {
        $this->add_hook('contact_photo', [$this, 'contact_photo']);
    }

    /**
     * 'contact_photo' hook handler to inject a bimi image
     */
    function contact_photo($args)
    {
        // pre-conditions, exit if photo already exists or invalid input
        if (!empty($args['url']) || !empty($args['data'])
            || (empty($args['record']) && empty($args['email']))
        ) {
            return $args;
        }

        $rcmail = rcmail::get_instance();

        // supporting edit/add action may be tricky, let's not do this
        if ($rcmail->action == 'show' || $rcmail->action == 'photo') {
            $email = !empty($args['email']) ? $args['email'] : null;

            if (!$email && $args['record']) {
                $addresses = rcube_addressbook::get_col_values('email', $args['record'], true);
                if (!empty($addresses)) {
                    $email = $addresses[0];
                }
            }

            if ($email) {
                require_once __DIR__ . '/bimi_engine.php';
                $bimi_image = new bimi_engine($email);

                if ($rcmail->action == 'show') {
                    // set photo URL
                    if (($icon = $bimi_image->getBinary()) && ($icon = base64_encode($icon))) {
                        $mimetype = $bimi_image->getMimetype();
                        $args['url'] = sprintf('data:%s;base64,%s', $mimetype, $icon);
                    }
                }
                else {
                    // send the icon to the browser
                    if ($bimi_image->sendOutput()) {
                        exit;
                    }
                }
            }
        }

        return $args;
    }
}
