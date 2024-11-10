<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_browser class
 */
class BrowserTest extends TestCase
{
    /**
     * @dataProvider provide_browser_cases
     */
    #[DataProvider('provide_browser_cases')]
    public function test_browser($useragent, $opera, $chrome, $ie, $edge, $safari, $mz)
    {
        $object = $this->getBrowser($useragent);

        $this->assertSame($opera, $object->opera, 'Check for Opera failed');
        $this->assertSame($chrome, $object->chrome, 'Check for Chrome failed');
        $this->assertSame($ie, $object->ie, 'Check for IE failed');
        $this->assertSame($edge, $object->edge, 'Check for Edge failed');
        $this->assertSame($safari, $object->safari, 'Check for Safari failed');
        $this->assertSame($mz, $object->mz, 'Check for MZ failed');
    }

    /**
     * @dataProvider provide_os_cases
     */
    #[DataProvider('provide_os_cases')]
    public function test_os($useragent, $windows, $linux, $unix, $mac)
    {
        $object = $this->getBrowser($useragent);

        $this->assertSame($windows, $object->win, 'Check Result of Windows');
        $this->assertSame($linux, $object->linux, 'Check Result of Linux');
        $this->assertSame($mac, $object->mac, 'Check Result of Mac');
        $this->assertSame($unix, $object->unix, 'Check Result of Unix');
    }

    /**
     * @dataProvider provide_version_cases
     */
    #[DataProvider('provide_version_cases')]
    public function test_version($useragent, $version)
    {
        $object = $this->getBrowser($useragent);
        $this->assertSame($version, $object->ver);
    }

    public static function provide_version_cases(): iterable
    {
        return static::extractDataSet(['version']);
    }

    public static function provide_browser_cases(): iterable
    {
        return static::extractDataSet(['isOpera', 'isChrome', 'isIE', 'isEdge', 'isSafari', 'isMZ']);
    }

    public static function useragents()
    {
        return [
            'WIN: Mozilla Firefox ' => [
                'useragent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.1) Gecko/20060111 Firefox/1.5.0.1',
                'version' => 1.8,
                'isWin' => true,
                'isLinux' => false,
                'isMac' => false,
                'isUnix' => false,
                'isOpera' => false,
                'isChrome' => false,
                'isIE' => false,
                'isEdge' => false,
                'isSafari' => false,
                'isMZ' => true,
            ],

            'LINUX: Bon Echo ' => [
                'useragent' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20070222 BonEcho/2.0.0.1',
                'version' => 1.8,
                'isWin' => false,
                'isLinux' => true,
                'isMac' => false,
                'isUnix' => false,
                'isOpera' => false,
                'isChrome' => false,
                'isIE' => false,
                'isEdge' => false,
                'isSafari' => false,
                'isMZ' => true,
            ],

            'Chrome Mac' => [
                'useragent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.461.0 Safari/534.3',
                'version' => 6.0,
                'isWin' => false,
                'isLinux' => false,
                'isMac' => true,
                'isUnix' => false,
                'isOpera' => false,
                'isChrome' => true,
                'isIE' => false,
                'isEdge' => false,
                'isSafari' => false,
                'isMZ' => false,
            ],

            'IE 11' => [
                'useragent' => 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; .NET4.0E; .NET4.0C; rv:11.0) like Gecko',
                'version' => 11.0,
                'isWin' => true,
                'isLinux' => false,
                'isMac' => false,
                'isUnix' => false,
                'isOpera' => false,
                'isChrome' => false,
                'isIE' => true,
                'isEdge' => false,
                'isSafari' => false,
                'isMZ' => false,
            ],

            'Opera 15' => [
                'useragent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.29 Safari/537.36 OPR/15.0.1147.24',
                'version' => 15.0,
                'isWin' => true,
                'isLinux' => false,
                'isMac' => false,
                'isUnix' => false,
                'isOpera' => true,
                'isChrome' => false,
                'isIE' => false,
                'isEdge' => false,
                'isSafari' => false,
                'isMZ' => false,
            ],

            'Edge 14' => [
                'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14931',
                'version' => 14.14931,
                'isWin' => true,
                'isLinux' => false,
                'isMac' => false,
                'isUnix' => false,
                'isOpera' => false,
                'isChrome' => false,
                'isIE' => false,
                'isEdge' => true,
                'isSafari' => false,
                'isMZ' => false,
            ],
        ];
    }

    public static function provide_os_cases(): iterable
    {
        return static::extractDataSet(['isWin', 'isLinux', 'isUnix', 'isMac']);
    }

    protected static function extractDataSet($keys)
    {
        $keys = array_merge(['useragent'], $keys);

        $browser = static::useragents();

        $extracted = [];

        foreach ($browser as $label => $data) {
            foreach ($keys as $key) {
                $extracted[$data['useragent']][] = $data[$key];
            }
        }

        return $extracted;
    }

    /**
     * @param string $useragent
     *
     * @return \rcube_browser
     */
    private function getBrowser($useragent)
    {
        $_SERVER['HTTP_USER_AGENT'] = $useragent;

        $object = new \rcube_browser();

        return $object;
    }
}
