<?php

/**
 * Test class to test rcube_browser class
 *
 * @package Tests
 */
class Framework_Browser extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider browsers
     */
    function test_browser($useragent, $opera, $chrome, $ie, $edge, $safari, $mz)
    {
        $object = $this->getBrowser($useragent);

        $this->assertEquals($opera, $object->opera, 'Check for Opera failed');
        $this->assertEquals($chrome, $object->chrome, 'Check for Chrome failed');
        $this->assertEquals($ie, $object->ie, 'Check for IE failed');
        $this->assertEquals($edge, $object->edge, 'Check for Edge failed');
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

    function versions()
    {
        return $this->extractDataSet(['version']);
    }

    function browsers()
    {
        return $this->extractDataSet(['isOpera', 'isChrome', 'isIE', 'isEdge', 'isSafari', 'isMZ']);
    }

    function useragents()
    {
        return [
            'WIN: Mozilla Firefox ' => [
                'useragent'    => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.1) Gecko/20060111 Firefox/1.5.0.1',
                'version'      => '1.8',
                'isWin'        => true,
                'isLinux'      => false,
                'isMac'        => false,
                'isUnix'       => false,
                'isOpera'      => false,
                'isChrome'     => false,
                'isIE'         => false,
                'isEdge'         => false,
                'isSafari'     => false,
                'isMZ'         => true,
            ],

            'LINUX: Bon Echo ' => [
                'useragent'    => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20070222 BonEcho/2.0.0.1',
                'version'      => '1.8',
                'isWin'        => false,
                'isLinux'      => true,
                'isMac'        => false,
                'isUnix'       => false,
                'isOpera'      => false,
                'isChrome'     => false,
                'isIE'         => false,
                'isEdge'       => false,
                'isSafari'     => false,
                'isMZ'         => true,
            ],

            'Chrome Mac' => [
                'useragent'    => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.461.0 Safari/534.3',
                'version'      => '6',
                'isWin'        => false,
                'isLinux'      => false,
                'isMac'        => true,
                'isUnix'       => false,
                'isOpera'      => false,
                'isChrome'     => true,
                'isIE'         => false,
                'isEdge'       => false,
                'isSafari'     => false,
                'isMZ'         => false,
            ],

            'IE 11' => [
                'useragent'    => 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0) like Gecko',
                'version'      => '11.0',
                'isWin'        => true,
                'isLinux'      => false,
                'isMac'        => false,
                'isUnix'       => false,
                'isOpera'      => false,
                'isChrome'     => false,
                'isIE'         => true,
                'isEdge'       => false,
                'isSafari'     => false,
                'isMZ'         => false,
            ],

            'Opera 15' => [
                'useragent'    => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.29 Safari/537.36 OPR/15.0.1147.24',
                'version'      => '15.0',
                'isWin'        => true,
                'isLinux'      => false,
                'isMac'        => false,
                'isUnix'       => false,
                'isOpera'      => true,
                'isChrome'     => false,
                'isIE'         => false,
                'isEdge'       => false,
                'isSafari'     => false,
                'isMZ'         => false,
            ],

            'Edge 14' => [
                'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14931',
                'version'      => '14.14931',
                'isWin'        => true,
                'isLinux'      => false,
                'isMac'        => false,
                'isUnix'       => false,
                'isOpera'      => false,
                'isChrome'     => false,
                'isIE'         => false,
                'isEdge'       => true,
                'isSafari'     => false,
                'isMZ'         => false,
            ],
        ];
    }

    function os()
    {
        return $this->extractDataSet(['isWin', 'isLinux', 'isUnix', 'isMac']);
    }

    private function extractDataSet($keys)
    {
        $keys = array_merge(['useragent'], $keys);

        $browser = $this->useragents();

        $extracted = [];

        foreach ($browser as $label => $data) {
            foreach($keys as $key) {
                $extracted[$data['useragent']][] = $data[$key];
            }
        }

        return $extracted;
    }

    /**
     * @param string $useragent
     * @return rcube_browser
     */
    private function getBrowser($useragent)
    {
        $_SERVER['HTTP_USER_AGENT'] = $useragent;

        $object = new rcube_browser();

        return $object;
    }
}
