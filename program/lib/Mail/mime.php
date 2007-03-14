<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002-2003  Richard Heyes                                |
// | Copyright (c) 2003-2005  The PHP Group                                |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.|
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Tomas V.V.Cox <cox@idecnet.com> (port to PEAR)                |
// +-----------------------------------------------------------------------+
//
// $Id$

require_once('PEAR.php');
require_once('Mail/mimePart.php');

/**
 * Mime mail composer class. Can handle: text and html bodies, embedded html
 * images and attachments.
 * Documentation and examples of this class are avaible here:
 * http://pear.php.net/manual/
 *
 * @notes This class is based on HTML Mime Mail class from
 *   Richard Heyes <richard@phpguru.org> which was based also
 *   in the mime_mail.class by Tobias Ratschiller <tobias@dnet.it> and
 *   Sascha Schumann <sascha@schumann.cx>
 *
 * @author   Richard Heyes <richard.heyes@heyes-computing.net>
 * @author   Tomas V.V.Cox <cox@idecnet.com>
 * @package  Mail
 * @access   public
 */
class Mail_mime
{
    /**
     * Contains the plain text part of the email
     * @var string
     */
    var $_txtbody;
    /**
     * Contains the html part of the email
     * @var string
     */
    var $_htmlbody;
    /**
     * contains the mime encoded text
     * @var string
     */
    var $_mime;
    /**
     * contains the multipart content
     * @var string
     */
    var $_multipart;
    /**
     * list of the attached images
     * @var array
     */
    var $_html_images = array();
    /**
     * list of the attachements
     * @var array
     */
    var $_parts = array();
    /**
     * Build parameters
     * @var array
     */
    var $_build_params = array();
    /**
     * Headers for the mail
     * @var array
     */
    var $_headers = array();
    /**
     * End Of Line sequence (for serialize)
     * @var string
     */
    var $_eol;


    /**
     * Constructor function
     *
     * @access public
     */
    function Mail_mime($crlf = "\r\n")
    {
        $this->_setEOL($crlf);
        $this->_build_params = array(
                                     'head_encoding' => 'quoted-printable',
                                     'text_encoding' => '7bit',
                                     'html_encoding' => 'quoted-printable',
                                     '7bit_wrap'     => 998,
                                     'html_charset'  => 'ISO-8859-1',
                                     'text_charset'  => 'ISO-8859-1',
                                     'head_charset'  => 'ISO-8859-1'
                                    );
    }

    /**
     * Wakeup (unserialize) - re-sets EOL constant
     *
     * @access private
     */
    function __wakeup()
    {
        $this->_setEOL($this->_eol);
    }

    /**
     * Accessor function to set the body text. Body text is used if
     * it's not an html mail being sent or else is used to fill the
     * text/plain part that emails clients who don't support
     * html should show.
     *
     * @param  string  $data   Either a string or
     *                         the file name with the contents
     * @param  bool    $isfile If true the first param should be treated
     *                         as a file name, else as a string (default)
     * @param  bool    $append If true the text or file is appended to
     *                         the existing body, else the old body is
     *                         overwritten
     * @return mixed   true on success or PEAR_Error object
     * @access public
     */
    function setTXTBody($data, $isfile = false, $append = false)
    {
        if (!$isfile) {
            if (!$append) {
                $this->_txtbody = $data;
            } else {
                $this->_txtbody .= $data;
            }
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            if (!$append) {
                $this->_txtbody = $cont;
            } else {
                $this->_txtbody .= $cont;
            }
        }
        return true;
    }

    /**
     * Adds a html part to the mail
     *
     * @param  string  $data   Either a string or the file name with the
     *                         contents
     * @param  bool    $isfile If true the first param should be treated
     *                         as a file name, else as a string (default)
     * @return mixed   true on success or PEAR_Error object
     * @access public
     */
    function setHTMLBody($data, $isfile = false)
    {
        if (!$isfile) {
            $this->_htmlbody = $data;
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            $this->_htmlbody = $cont;
        }

        return true;
    }

    /**
    * returns the HTML body portion of the message
    * @return string HTML body of the message
    * @access public
    */
    function getHTMLBody()
    {
       return $this->_htmlbody;
    }
    
    /**
     * Adds an image to the list of embedded images.
     *
     * @param  string  $file       The image file name OR image data itself
     * @param  string  $c_type     The content type
     * @param  string  $name       The filename of the image.
     *                             Only use if $file is the image data
     * @param  bool    $isfilename Whether $file is a filename or not
     *                             Defaults to true
     * @param  string  $contentid  Desired Content-ID of MIME part
     *                             Defaults to generated unique ID
     * @return mixed   true on success or PEAR_Error object
     * @access public
     */
    function addHTMLImage($file, $c_type='application/octet-stream',
                          $name = '', $isfilename = true, $contentid = '')
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfilename === true) {
            $filename = ($name == '' ? $file : $name);
        } else {
            $filename = $name;
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        if ($contentid == '') {
           $contentid = md5(uniqid(time()));
        }
        $this->_html_images[] = array(
                                      'body'   => $filedata,
                                      'name'   => $filename,
                                      'c_type' => $c_type,
                                      'cid'    => $contentid
                                     );
        return true;
    }

    /**
     * Adds a file to the list of attachments.
     *
     * @param  string  $file        The file name of the file to attach
     *                              OR the file contents itself
     * @param  string  $c_type      The content type
     * @param  string  $name        The filename of the attachment
     *                              Only use if $file is the contents
     * @param  bool    $isFilename  Whether $file is a filename or not
     *                              Defaults to true
     * @param  string  $encoding    The type of encoding to use.
     *                              Defaults to base64.
     *                              Possible values: 7bit, 8bit, base64, 
     *                              or quoted-printable.
     * @param  string  $disposition The content-disposition of this file
     *                              Defaults to attachment.
     *                              Possible values: attachment, inline.
     * @param  string  $charset     The character set used in the filename
     *                              of this attachment.
     * @return mixed true on success or PEAR_Error object
     * @access public
     */
    function addAttachment($file, $c_type = 'application/octet-stream',
                           $name = '', $isfilename = true,
                           $encoding = 'base64',
                           $disposition = 'attachment', $charset = '')
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : $file;
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            $err = PEAR::raiseError(
              "The supplied filename for the attachment can't be empty"
            );
	    return $err;
        }
        $filename = basename($filename);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'        => $filedata,
                                'name'        => $filename,
                                'c_type'      => $c_type,
                                'encoding'    => $encoding,
                                'charset'     => $charset,
                                'disposition' => $disposition
                               );
        return true;
    }

    /**
     * Get the contents of the given file name as string
     *
     * @param  string  $file_name  path of file to process
     * @return string  contents of $file_name
     * @access private
     */
    function &_file2str($file_name)
    {
        if (!is_readable($file_name)) {
            $err = PEAR::raiseError('File is not readable ' . $file_name);
            return $err;
        }
        if (!$fd = fopen($file_name, 'rb')) {
            $err = PEAR::raiseError('Could not open ' . $file_name);
            return $err;
        }
        $filesize = filesize($file_name);
        if ($filesize == 0){
            $cont =  "";
        }else{
            if ($magic_quote_setting = get_magic_quotes_runtime()){
                set_magic_quotes_runtime(0);
            }
            $cont = fread($fd, $filesize);
            if ($magic_quote_setting){
                set_magic_quotes_runtime($magic_quote_setting);
            }
        }
        fclose($fd);
        return $cont;
    }

    /**
     * Adds a text subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param mixed    The object to add the part to, or
     *                 null if a new object is to be created.
     * @param string   The text to add.
     * @return object  The text mimePart object
     * @access private
     */
    function &_addTextPart(&$obj, $text)
    {
        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->_build_params['text_encoding'];
        $params['charset']      = $this->_build_params['text_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($text, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($text, $params);
            return $ret;
        }
    }

    /**
     * Adds a html subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param  mixed   The object to add the part to, or
     *                 null if a new object is to be created.
     * @return object  The html mimePart object
     * @access private
     */
    function &_addHtmlPart(&$obj)
    {
        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->_build_params['html_encoding'];
        $params['charset']      = $this->_build_params['html_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($this->_htmlbody, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($this->_htmlbody, $params);
            return $ret;
        }
    }

    /**
     * Creates a new mimePart object, using multipart/mixed as
     * the initial content-type and returns it during the
     * build process.
     *
     * @return object  The multipart/mixed mimePart object
     * @access private
     */
    function &_addMixedPart()
    {
        $params['content_type'] = 'multipart/mixed';
        $ret = new Mail_mimePart('', $params);
        return $ret;
    }

    /**
     * Adds a multipart/alternative part to a mimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param  mixed   The object to add the part to, or
     *                 null if a new object is to be created.
     * @return object  The multipart/mixed mimePart object
     * @access private
     */
    function &_addAlternativePart(&$obj)
    {
        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            $ret = new Mail_mimePart('', $params);
            return $ret;
        }
    }

    /**
     * Adds a multipart/related part to a mimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param mixed    The object to add the part to, or
     *                 null if a new object is to be created
     * @return object  The multipart/mixed mimePart object
     * @access private
     */
    function &_addRelatedPart(&$obj)
    {
        $params['content_type'] = 'multipart/related';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            $ret = new Mail_mimePart('', $params);
            return $ret;
        }
    }

    /**
     * Adds an html image subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param  object  The mimePart to add the image to
     * @param  array   The image information
     * @return object  The image mimePart object
     * @access private
     */
    function &_addHtmlImagePart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'] . '; ' .
                                  'name="' . $value['name'] . '"';
        $params['encoding']     = 'base64';
        $params['disposition']  = 'inline';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        $ret = $obj->addSubpart($value['body'], $params);
        return $ret;
	
    }

    /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param  object  The mimePart to add the image to
     * @param  array   The attachment information
     * @return object  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['dfilename']    = $value['name'];
        $params['encoding']     = $value['encoding'];
        if ($value['disposition'] != "inline") {
            $fname = array("fname" => $value['name']);
            $fname_enc = $this->_encodeHeaders($fname);
            $params['dfilename'] = $fname_enc['fname'];
        }
        if ($value['charset']) {
            $params['charset'] = $value['charset'];
        }
        $params['content_type'] = $value['c_type'] . '; ' .
                                  'name="' . $params['dfilename'] . '"';
        $params['disposition']  = isset($value['disposition']) ? 
                                  $value['disposition'] : 'attachment';
        $ret = $obj->addSubpart($value['body'], $params);
        return $ret;
    }

    /**
     * Returns the complete e-mail, ready to send using an alternative
     * mail delivery method. Note that only the mailpart that is made
     * with Mail_Mime is created. This means that,
     * YOU WILL HAVE NO TO: HEADERS UNLESS YOU SET IT YOURSELF 
     * using the $xtra_headers parameter!
     * 
     * @param  string $separation   The separation etween these two parts.
     * @param  array  $build_params The Build parameters passed to the
     *                              &get() function. See &get for more info.
     * @param  array  $xtra_headers The extra headers that should be passed
     *                              to the &headers() function.
     *                              See that function for more info.
     * @param  bool   $overwrite    Overwrite the existing headers with new.
     * @return string The complete e-mail.
     * @access public
     */
    function getMessage($separation = null, $build_params = null, $xtra_headers = null, $overwrite = false)
    {
        if ($separation === null)
        {
            $separation = MAIL_MIME_CRLF;
        }
        $body = $this->get($build_params);
        $head = $this->txtHeaders($xtra_headers, $overwrite);
        $mail = $head . $separation . $body;
        return $mail;
    }


    /**
     * Builds the multipart message from the list ($this->_parts) and
     * returns the mime content.
     *
     * @param  array  Build parameters that change the way the email
     *                is built. Should be associative. Can contain:
     *                head_encoding  -  What encoding to use for the headers. 
     *                                  Options: quoted-printable or base64
     *                                  Default is quoted-printable
     *                text_encoding  -  What encoding to use for plain text
     *                                  Options: 7bit, 8bit, base64, or quoted-printable
     *                                  Default is 7bit
     *                html_encoding  -  What encoding to use for html
     *                                  Options: 7bit, 8bit, base64, or quoted-printable
     *                                  Default is quoted-printable
     *                7bit_wrap      -  Number of characters before text is
     *                                  wrapped in 7bit encoding
     *                                  Default is 998
     *                html_charset   -  The character set to use for html.
     *                                  Default is iso-8859-1
     *                text_charset   -  The character set to use for text.
     *                                  Default is iso-8859-1
     *                head_charset   -  The character set to use for headers.
     *                                  Default is iso-8859-1
     * @return string The mime content
     * @access public
     */
    function &get($build_params = null)
    {
        if (isset($build_params)) {
            while (list($key, $value) = each($build_params)) {
                $this->_build_params[$key] = $value;
            }
        }

        if (!empty($this->_html_images) AND isset($this->_htmlbody)) {
            foreach ($this->_html_images as $key => $value) {
                $regex = array();
                $regex[] = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\3#';
                $regex[] = '#(?i)url(?-i)\(\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\1\s*\)#';
                $rep = array();
                $rep[] = '\1\2=\3cid:' . $value['cid'] .'\3';
                $rep[] = 'url(\1cid:' . $value['cid'] . '\2)';
                $this->_htmlbody = preg_replace($regex, $rep,
                                       $this->_htmlbody
                                   );
                $this->_html_images[$key]['name'] = basename($this->_html_images[$key]['name']);
            }
        }

        $null        = null;
        $attachments = !empty($this->_parts)                ? true : false;
        $html_images = !empty($this->_html_images)          ? true : false;
        $html        = !empty($this->_htmlbody)             ? true : false;
        $text        = (!$html AND !empty($this->_txtbody)) ? true : false;

        switch (true) {
        case $text AND !$attachments:
            $message =& $this->_addTextPart($null, $this->_txtbody);
            break;

        case !$text AND !$html AND $attachments:
            $message =& $this->_addMixedPart();
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $text AND $attachments:
            $message =& $this->_addMixedPart();
            $this->_addTextPart($message, $this->_txtbody);
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND !$attachments AND !$html_images:
            if (isset($this->_txtbody)) {
                $message =& $this->_addAlternativePart($null);
                $this->_addTextPart($message, $this->_txtbody);
                $this->_addHtmlPart($message);
            } else {
                $message =& $this->_addHtmlPart($null);
            }
            break;

        case $html AND !$attachments AND $html_images:
            if (isset($this->_txtbody)) {
                $message =& $this->_addAlternativePart($null);
                $this->_addTextPart($message, $this->_txtbody);
                $related =& $this->_addRelatedPart($message);
            } else {
                $message =& $this->_addRelatedPart($null);
                $related =& $message;
            }
            $this->_addHtmlPart($related);
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($related, $this->_html_images[$i]);
            }
            break;

        case $html AND $attachments AND !$html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $this->_addHtmlPart($alt);
            } else {
                $this->_addHtmlPart($message);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND $attachments AND $html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $rel =& $this->_addRelatedPart($alt);
            } else {
                $rel =& $this->_addRelatedPart($message);
            }
            $this->_addHtmlPart($rel);
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        }

        if (isset($message)) {
            $output = $message->encode();
            $this->_headers = array_merge($this->_headers,
                                          $output['headers']);
            $body = $output['body'];
            return $body;

        } else {
            $ret = false;
            return $ret;
        }
    }

    /**
     * Returns an array with the headers needed to prepend to the email
     * (MIME-Version and Content-Type). Format of argument is:
     * $array['header-name'] = 'header-value';
     *
     * @param  array $xtra_headers Assoc array with any extra headers.
     *                             Optional.
     * @param  bool  $overwrite    Overwrite already existing headers.
     * @return array Assoc array with the mime headers
     * @access public
     */
    function &headers($xtra_headers = null, $overwrite = false)
    {
        // Content-Type header should already be present,
        // So just add mime version header
        $headers['MIME-Version'] = '1.0';
        if (isset($xtra_headers)) {
            $headers = array_merge($headers, $xtra_headers);
        }
        if ($overwrite){
            $this->_headers = array_merge($this->_headers, $headers);
        }else{
            $this->_headers = array_merge($headers, $this->_headers);
        }

        $encodedHeaders = $this->_encodeHeaders($this->_headers);
        return $encodedHeaders;
    }

    /**
     * Get the text version of the headers
     * (usefull if you want to use the PHP mail() function)
     *
     * @param  array   $xtra_headers Assoc array with any extra headers.
     *                               Optional.
     * @param  bool    $overwrite    Overwrite the existing heaers with new.
     * @return string  Plain text headers
     * @access public
     */
    function txtHeaders($xtra_headers = null, $overwrite = false)
    {
        $headers = $this->headers($xtra_headers, $overwrite);
        $ret = '';
        foreach ($headers as $key => $val) {
            $ret .= "$key: $val" . MAIL_MIME_CRLF;
        }
        return $ret;
    }

    /**
     * Sets the Subject header
     *
     * @param  string $subject String to set the subject to
     * access  public
     */
    function setSubject($subject)
    {
        $this->_headers['Subject'] = $subject;
    }

    /**
     * Set an email to the From (the sender) header
     *
     * @param  string $email The email direction to add
     * @access public
     */
    function setFrom($email)
    {
        $this->_headers['From'] = $email;
    }

    /**
     * Add an email to the Cc (carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param  string $email The email direction to add
     * @access public
     */
    function addCc($email)
    {
        if (isset($this->_headers['Cc'])) {
            $this->_headers['Cc'] .= ", $email";
        } else {
            $this->_headers['Cc'] = $email;
        }
    }

    /**
     * Add an email to the Bcc (blank carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param  string $email The email direction to add
     * @access public
     */
    function addBcc($email)
    {
        if (isset($this->_headers['Bcc'])) {
            $this->_headers['Bcc'] .= ", $email";
        } else {
            $this->_headers['Bcc'] = $email;
        }
    }

    /**
     * Since the PHP send function requires you to specifiy 
     * recipients (To: header) separately from the other
     * headers, the To: header is not properly encoded.
     * To fix this, you can use this public method to 
     * encode your recipients before sending to the send
     * function
     *
     * @param  string $recipients A comma-delimited list of recipients
     * @return string Encoded data
     * @access public
     */
    function encodeRecipients($recipients)
    {
        $input = array("To" => $recipients);
        $retval = $this->_encodeHeaders($input);
        return $retval["To"] ;
    }

    /**
     * Encodes a header as per RFC2047
     *
     * @param  array $input The header data to encode
     * @return array Encoded data
     * @access private
     */
    function _encodeHeaders($input)
    {
        $maxlen = 73;
        foreach ($input as $hdr_name => $hdr_value) {
            // if header contains e-mail addresses
            if (preg_match('/\s<.+@[a-z0-9\-\.]+\.[a-z]+>/U', $hdr_value))
                $chunks = $this->_explode_quoted_string(',', $hdr_value);
            else
               $chunks = array($hdr_value);

            $hdr_value = '';
            $line_len = 0;

            foreach ($chunks as $i => $value) {
                $value = trim($value);

                //This header contains non ASCII chars and should be encoded.
                if (preg_match('#[\x80-\xFF]{1}#', $value)) {
                    $suffix = '';
                    // Don't encode e-mail address
                    if (preg_match('/(.+)\s(<.+@[a-z0-9\-\.]+>)$/Ui', $value, $matches)) {
                        $value = $matches[1];
                        $suffix = ' '.$matches[2];
                    }

                    switch ($this->_build_params['head_encoding']) {
                    case 'base64':
                        // Base64 encoding has been selected.
                        $mode = 'B';
                        $encoded = base64_encode($value);
                        break;

                    case 'quoted-printable':
                    default:
                        // quoted-printable encoding has been selected
                        $mode = 'Q';
                        $encoded = preg_replace('/([\x2C\x3F\x80-\xFF])/e', "'='.sprintf('%02X', ord('\\1'))", $value);
                        // replace spaces with _
                        $encoded = str_replace(' ', '_', $encoded);
                    }

                $value = '=?' . $this->_build_params['head_charset'] . '?' . $mode . '?' . $encoded . '?=' . $suffix;
                }

                // add chunk to output string by regarding the header maxlen
                $len = strlen($value);
                if ($line_len + $len < $maxlen) {
                    $hdr_value .= ($i>0?', ':'') . $value;
                    $line_len += $len + ($i>0?2:0);
                }
                else {
                    $hdr_value .= ($i>0?', ':'') . "\n " . $value;
                    $line_len = $len;
                }
            }

            $input[$hdr_name] = $hdr_value;
        }

        return $input;
    }


  function _explode_quoted_string($delimiter, $string)
    {
    $quotes = explode("\"", $string);
    foreach ($quotes as $key => $val)
      if (($key % 2) == 1)
        $quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);

    $string = implode("\"", $quotes);

    $result = explode($delimiter, $string);
    foreach ($result as $key => $val) 
      $result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);

    return $result;
    }


    /**
     * Set the object's end-of-line and define the constant if applicable
     *
     * @param string $eol End Of Line sequence
     * @access private
     */
    function _setEOL($eol)
    {
        $this->_eol = $eol;
        if (!defined('MAIL_MIME_CRLF')) {
            define('MAIL_MIME_CRLF', $this->_eol, true);
        }
    }

    

} // End of class
?>

