<?php

/**
 * MarkAsJunk JS events example
 * This is an example of how to interact with the markasjunk JS event
 * markasjunk-update to change the spam/ham options shown for specific
 * folders
 *
 * @version 0.1
 * @author Philip Weir
 *
 * Copyright (C) 2016 Philip Weir
 *
 * This driver is part of the MarkASJunk plugin for Roundcube.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Roundcube. If not, see http://www.gnu.org/licenses/.
 */

class markasjunk_jsevent
{
    private $addition_spam_folders = array('spam2', 'spam3');
    private $suspicious_folders    = array('unknown1', 'unknown2');

    public function init()
    {
        $js_addition_spam_folders = json_encode($this->addition_spam_folders);
        $js_suspicious_folders    = json_encode($this->suspicious_folders);

        $script = <<<EOL
rcmail.addEventListener('markasjunk-update', function(props) {
    var addition_spam_folders = {$js_addition_spam_folders};
    var suspicious_folders = {$js_suspicious_folders};

    // ignore this special code when in a multifolder listing
    if (rcmail.is_multifolder_listing())
            return;

    if ($.inArray(rcmail.env.mailbox, addition_spam_folders) > -1) {
        props.disp.spam = false;
        props.disp.ham = true;
    }
    else if ($.inArray(rcmail.env.mailbox, suspicious_folders) > -1) {
        props.disp.spam = true;
        props.disp.ham = true;

        // from here it is also possible to alter the buttons themselves...
        props.objs.spamobj.find('a > span').text('As possibly spam');
    }
    else {
            props.objs.spamobj.find('a > span').text(rcmail.get_label('markasjunk.markasjunk'));
    }

    return props;
});
EOL;

        $rcmail = rcmail::get_instance();
        $rcmail->output->add_script($script, 'docready');
    }

    public function spam(&$uids, $mbox)
    {
        // Treat message as spam...
    }

    public function ham(&$uids, $mbox)
    {
        // Treat message as ham...
    }
}
