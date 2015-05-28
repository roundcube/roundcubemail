<?php

class Emoticons_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once __DIR__ . '/../emoticons.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new emoticons($rcube->api);

        $this->assertInstanceOf('emoticons', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * replace() method tests
     */
    function test_replace()
    {
        $rcube  = rcube::get_instance();
        $plugin = new emoticons($rcube->api);

        $map = array(
            ':D'  => array('smiley-laughing.gif',    ':D'    ),
            ':-D' => array('smiley-laughing.gif',    ':-D'   ),
            ':('  => array('smiley-frown.gif',       ':('    ),
            ':-(' => array('smiley-frown.gif',       ':-('   ),
            '8)'  => array('smiley-cool.gif',        '8)'    ),
            '8-)' => array('smiley-cool.gif',        '8-)'   ),
            ':O'  => array('smiley-surprised.gif',   ':O'    ),
            ':-O' => array('smiley-surprised.gif',   ':-O'   ),
            ':P'  => array('smiley-tongue-out.gif',  ':P'    ),
            ':-P' => array('smiley-tongue-out.gif',  ':-P'   ),
            ':@'  => array('smiley-yell.gif',        ':@'    ),
            ':-@' => array('smiley-yell.gif',        ':-@'   ),
            'O:)' => array('smiley-innocent.gif',    'O:)'   ),
            'O:-)' => array('smiley-innocent.gif',    'O:-)' ),
            ':)'  => array('smiley-smile.gif',       ':)'    ),
            ':-)' => array('smiley-smile.gif',       ':-)'   ),
            ':$'  => array('smiley-embarassed.gif',  ':$'    ),
            ':-$' => array('smiley-embarassed.gif',  ':-$'   ),
            ':*'  => array('smiley-kiss.gif',       ':*'     ),
            ':-*' => array('smiley-kiss.gif',       ':-*'    ),
            ':S'  => array('smiley-undecided.gif',   ':S'    ),
            ':-S' => array('smiley-undecided.gif',   ':-S'   ),
        );

        foreach ($map as $body => $expected) {
            $args = array('type' => 'plain', 'body' => $body);
            $args = $plugin->replace($args);
            $this->assertRegExp('/' . preg_quote($expected[0], '/') . '/', $args['body']);
            $this->assertRegExp('/title="' . preg_quote($expected[1], '/') . '"/', $args['body']);
        }
    }
}
