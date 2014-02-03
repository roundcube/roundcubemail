<?php

/**
 * Test class to test rcube_browser class
 *
 * @package Tests
 */
class Framework_Browser extends PHPUnit_Framework_TestCase
{

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcube_browser();

        $this->assertInstanceOf('rcube_browser', $object, "Class constructor");
    }

    /**
     * @dataProvider browsers
     */
    function test_browser($useragent, $opera, $chrome, $ie, $ns, $safari, $mz)
    {

        $object = $this->getBrowser($useragent);

        $this->assertEquals($opera, $object->opera, 'Check for Opera failed');
        $this->assertEquals($chrome, $object->chrome, 'Check for Chrome failed');
        $this->assertEquals($ie, $object->ie, 'Check for IE failed');
        $this->assertEquals($ns, $object->ns, 'Check for NS failed');
        $this->assertEquals($safari, $object->safari, 'Check for Safari failed');
        $this->assertEquals($mz, $object->mz, 'Check for MZ failed');
    }

    /**
     * @dataProvider os
     */
    function test_os($useragent, $windows, $linux, $unix, $mac)
    {
        $object = $this->getBrowser($useragent);

        $this->assertEquals($windows, $object->win, 'Check Result of Windows');
        $this->assertEquals($linux, $object->linux, 'Check Result of Linux');
        $this->assertEquals($mac, $object->mac, 'Check Result of Mac');
        $this->assertEquals($unix, $object->unix, 'Check Result of Unix');

    }

    /**
     * @dataProvider versions
     */
    function test_version($useragent, $version)
    {
        $object = $this->getBrowser($useragent);
        $this->assertEquals($version, $object->ver);
    }

    /**
     * @dataProvider dom
     */
    function test_dom($useragent, $dom)
    {
        $object = $this->getBrowser($useragent);
        $this->assertEquals($dom, $object->dom);

    }

    /**
     * @dataProvider pngalpha
     */
    function test_pngalpha($useragent, $pngalpha)
    {
        $object = $this->getBrowser($useragent);
        $this->assertEquals($pngalpha, $object->pngalpha);
    }

    /**
     * @dataProvider imgdata
     */
    function test_imgdata($useragent, $imgdata)
    {
        $object = $this->getBrowser($useragent);
        $this->assertEquals($imgdata, $object->imgdata);
    }

    function versions()
    {
        return $this->extractDataSet(array('version'));
    }

    function pngalpha()
    {
        return $this->extractDataSet(array('canPNGALPHA'));
    }

    function imgdata()
    {
        return $this->extractDataSet(array('canIMGDATA'));
    }

    private function extractDataSet($keys)
    {
        $keys = array_merge(array('useragent'), $keys);

        $browser = $this->useragents();

        $extracted = array();

        foreach ($browser as $label => $data) {
            foreach($keys as $key) {
                $extracted[$data['useragent']][] = $data[$key];
            }

        }

        return $extracted;
    }

    function lang()
    {
        return $this->extractDataSet(array('lang'));
    }

    function dom()
    {
        return $this->extractDataSet(array('hasDOM'));
    }

    function browsers()
    {
        return $this->extractDataSet(array('isOpera','isChrome','isIE','isNS','isSafari','isMZ'));
    }

    function useragents()
    {
        return array(
             'WIN: Mozilla Firefox ' => array(
                 'useragent'    => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.1) Gecko/20060111 Firefox/1.5.0.1',
                 'version'      => '1.8',                                                                                      //Version
                 'isWin'        => true,                                                                                           //isWindows
                 'isLinux'      => false,
                 'isMac'        => false,                                                                                           //isMac
                 'isUnix'       => false,                                                                                           //isUnix
                 'isOpera'      => false,                                                                                           //isOpera
                 'isChrome'     => false,                                                                                           //isChrome
                 'isIE'         => false,                                                                                           //isIE
                 'isNS'         => false,                                                                                           //isNS
                 'isSafari'     => false,                                                                                           //isSafari
                 'isMZ'         => true,                                                                                           //isMZ
                 'lang'         => 'en-US',                                                                               //lang
                 'hasDOM'       => true,                                                                                            //hasDOM
                 'canPNGALPHA'  => true,                                                                                            //canPNGALPHA
                 'canIMGDATA'   => true,                                                                                            //canIMGDATA
             ),
            'LINUX: Bon Echo ' => array(
                 'useragent'    => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20070222 BonEcho/2.0.0.1',
                 'version'      => '1.8',                                                                                      //Version
                 'isWin'        => false,                                                                                           //isWindows
                 'isLinux'      => true,
                 'isMac'        => false,                                                                                           //isMac
                 'isUnix'       => false,                                                                                           //isUnix
                 'isOpera'      => false,                                                                                           //isOpera
                 'isChrome'     => false,                                                                                           //isChrome
                 'isIE'         => false,                                                                                           //isIE
                 'isNS'         => false,                                                                                           //isNS
                 'isSafari'     => false,                                                                                           //isSafari
                 'isMZ'         => true,                                                                                           //isMZ
                 'lang'         => 'en-US',                                                                               //lang
                 'hasDOM'       => true,                                                                                            //hasDOM
                 'canPNGALPHA'  => true,                                                                                            //canPNGALPHA
                 'canIMGDATA'   => true,                                                                                            //canIMGDATA
             ),

            'Chrome Mac' => array(
                 'useragent'    => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.461.0 Safari/534.3',
                 'version'      => '6',                                                                                      //Version
                 'isWin'        => false,                                                                                           //isWindows
                 'isLinux'      => false,
                 'isMac'        => true,                                                                                           //isMac
                 'isUnix'       => false,                                                                                           //isUnix
                 'isOpera'      => false,                                                                                           //isOpera
                 'isChrome'     => true,                                                                                           //isChrome
                 'isIE'         => false,                                                                                           //isIE
                 'isNS'         => false,                                                                                           //isNS
                 'isSafari'     => false,                                                                                           //isSafari
                 'isMZ'         => false,                                                                                           //isMZ
                 'lang'         => 'en-US',                                                                               //lang
                 'hasDOM'       => false,                                                                                            //hasDOM
                 'canPNGALPHA'  => false,                                                                                            //canPNGALPHA
                 'canIMGDATA'   => true,                                                                                            //canIMGDATA
             ),

            'IE 11' => array(
                 'useragent'    => 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0) like Gecko',
                 'version'      => '11.0',                                                                                      //Version
                 'isWin'        => true,                                                                                           //isWindows
                 'isLinux'      => false,
                 'isMac'        => false,                                                                                           //isMac
                 'isUnix'       => false,                                                                                           //isUnix
                 'isOpera'      => false,                                                                                           //isOpera
                 'isChrome'     => false,                                                                                           //isChrome
                 'isIE'         => true,                                                                                           //isIE
                 'isNS'         => false,                                                                                           //isNS
                 'isSafari'     => false,                                                                                           //isSafari
                 'isMZ'         => false,                                                                                           //isMZ
                 'lang'         => '',                                                                                         //lang
                 'hasDOM'       => true,                                                                                            //hasDOM
                 'canPNGALPHA'  => true,                                                                                            //canPNGALPHA
                 'canIMGDATA'   => false,                                                                                            //canIMGDATA
             ),

            'Opera 15' => array(
                 'useragent'    => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.29 Safari/537.36 OPR/15.0.1147.24',
                 'version'      => '15.0',                                                                                      //Version
                 'isWin'        => true,                                                                                           //isWindows
                 'isLinux'      => false,
                 'isMac'        => false,                                                                                           //isMac
                 'isUnix'       => false,                                                                                           //isUnix
                 'isOpera'      => true,                                                                                           //isOpera
                 'isChrome'     => false,                                                                                           //isChrome
                 'isIE'         => false,                                                                                           //isIE
                 'isNS'         => false,                                                                                           //isNS
                 'isSafari'     => false,                                                                                           //isSafari
                 'isMZ'         => false,                                                                                           //isMZ
                 'lang'         => '',                                                                                         //lang
                 'hasDOM'       => true,                                                                                            //hasDOM
                 'canPNGALPHA'  => true,                                                                                            //canPNGALPHA
                 'canIMGDATA'   => true,                                                                                            //canIMGDATA
             ),
        );
    }

    function os()
    {
        return $this->extractDataSet(array('isWin','isLinux','isUnix','isMac'));
    }

    /**
     * @param string $useragent
     * @return rcube_browser
     */
    private function getBrowser($useragent)
    {
        /** @var $object rcube_browser */
        $_SERVER['HTTP_USER_AGENT'] = $useragent;

        $object = new rcube_browser();

        return $object;
    }
}
