<?php

/**
 * Test class to test rcmail_output_html class
 *
 * @package Tests
 */
class Rcmail_OutputHtml extends PHPUnit_Framework_TestCase
{
    /**
     * Test get_template_logo()
     */
    function test_logo()
    {
        $rcmail            = rcube::get_instance();
        $output            = new rcmail_output_html();
        $reflection        = new ReflectionClass('rcmail_output_html');
        $set_skin          = $reflection->getProperty('skin_name');
        $set_template      = $reflection->getProperty('template_name');
        $get_template_logo = $reflection->getMethod('get_template_logo');

        $set_skin->setAccessible(true);
        $set_template->setAccessible(true);
        $get_template_logo->setAccessible(true);

        $set_skin->setValue($output, 'elastic');

        $rcmail->config->set('skin_logo', 'img00');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img00', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img00', $result);

        $rcmail->config->set('skin_logo', array(
             "elastic:login[small]" => "img01",
             "elastic:login"        => "img02",
             "elastic:*[small]"     => "img03",
             "larry:*"              => "img04",
             "*:login[small]"       => "img05",
             "*:login"              => "img06",
             "*[print]"             => "img07",
             "*"                    => "img08",
           ));

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img01', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img02', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img03', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img08', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img03', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img08', $result);

        $set_skin->setValue($output, 'larry');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img05', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img04', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img04', $result);

        $set_skin->setValue($output, '_test_');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img05', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img06', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array('print'));
        $this->assertSame('img07', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img08', $result);

        $rcmail->config->set('skin_logo', array(
             "elastic:login[small]" => "img09",
             "elastic:login"        => "img10",
             "larry:*"              => "img11",
             "elastic[small]"       => "img12",
             "login[small]"         => "img13",
             "login"                => "img14",
             "[print]"              => "img15",
             "*"                    => "img16",
           ));

        $set_skin->setValue($output, 'elastic');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img09', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame(null, $result);

        $set_skin->setValue($output, '_test_');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array('small'));
        $this->assertSame('img13', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img14', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array('print'));
        $this->assertSame('img15', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array('_test_'));
        $this->assertSame(null, $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, array());
        $this->assertSame('img16', $result);
    }
}
