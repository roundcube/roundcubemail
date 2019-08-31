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
 |   Class representing the client browser's properties                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Provide details about the client's browser based on the User-Agent header
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_browser
{
    /** @var float $ver Browser version */
    public $ver = 0;

    /** @var bool $win Browser OS is Windows */
    public $win = false;

    /** @var bool $mac Browser OS is Mac */
    public $mac = false;

    /** @var bool $linux Browser OS is Linux */
    public $linux = false;

    /** @var bool $unix Browser OS is Unix */
    public $unix = false;

    /** @var bool $webkit Browser uses WebKit engine */
    public $webkit = false;

    /** @var bool $opera Browser is Opera */
    public $opera = false;

    /** @var bool $chrome Browser is Chrome */
    public $chrome = false;

    /** @var bool $ie Browser is Internet Explorer */
    public $ie = false;

    /** @var bool $safari Browser is Safari */
    public $safari = false;

    /** @var bool $mz Browser is Mozilla Firefox */
    public $mz = false;

    /** @var string $lang Language code */
    public $lang = 'en';


    /**
     * Object construstor
     */
    public function __construct()
    {
        $HTTP_USER_AGENT = strtolower($_SERVER['HTTP_USER_AGENT']);

        // Operating system detection
        $this->win   = strpos($HTTP_USER_AGENT, 'win') != false;
        $this->mac   = strpos($HTTP_USER_AGENT, 'mac') != false;
        $this->linux = strpos($HTTP_USER_AGENT, 'linux') != false;
        $this->unix  = strpos($HTTP_USER_AGENT, 'unix') != false;

        // Engine detection
        $this->webkit = strpos($HTTP_USER_AGENT, 'applewebkit') !== false;
        $this->opera  = strpos($HTTP_USER_AGENT, 'opera') !== false || ($this->webkit && strpos($HTTP_USER_AGENT, 'opr/') !== false);
        $this->chrome = !$this->opera && strpos($HTTP_USER_AGENT, 'chrome') !== false;
        $this->ie     = !$this->opera && (strpos($HTTP_USER_AGENT, 'compatible; msie') !== false || strpos($HTTP_USER_AGENT, 'trident/') !== false);
        $this->safari = !$this->opera && !$this->chrome && ($this->webkit || strpos($HTTP_USER_AGENT, 'safari') !== false);
        $this->mz     = !$this->ie && !$this->safari && !$this->chrome && !$this->opera && strpos($HTTP_USER_AGENT, 'mozilla') !== false;

        // Version detection
        if ($this->opera) {
            if (preg_match('/(opera|opr)\/([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
                $this->ver = (float) $regs[2];
            }
        }
        else if (preg_match('/(chrome|msie|version|khtml)(\s*|\/)([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
            $this->ver = (float) $regs[3];
        }
        else if (preg_match('/rv:([0-9.]+)/', $HTTP_USER_AGENT, $regs)) {
            $this->ver = (float) $regs[1];
        }

        // Language code
        if (preg_match('/ ([a-z]{2})-([a-z]{2})/', $HTTP_USER_AGENT, $regs)) {
            $this->lang =  $regs[1];
        }
    }
}
