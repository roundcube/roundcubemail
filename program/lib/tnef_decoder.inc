<?php
/*
 * tnef_decoder.php
 *  Graham Norbury <gnorbury@bondcar.com>
 *  (c) 2002 (GNU GPL - see ../../COPYING)
 *
 *  Functions for decoding TNEF attachments in native PHP
 *
 *  Adapted from original designs by:
 *    Thomas Boll <tb@boll.ch>             [tnef.c]
 *    Mark Simpson <damned@world.std.com>  [tnef-1.1.1]
 *
 */

define("TNEF_SIGNATURE",      0x223e9f78);
define("TNEF_LVL_MESSAGE",    0x01);
define("TNEF_LVL_ATTACHMENT", 0x02);

define("TNEF_STRING", 0x00010000);
define("TNEF_TEXT",   0x00020000);
define("TNEF_BYTE",   0x00060000);
define("TNEF_WORD",   0x00070000);
define("TNEF_DWORD",  0x00080000);

define("TNEF_ASUBJECT",   TNEF_DWORD  | 0x8004);
define("TNEF_AMCLASS",    TNEF_WORD   | 0x8008);
define("TNEF_BODYTEXT",   TNEF_TEXT   | 0x800c);
define("TNEF_ATTACHDATA", TNEF_BYTE   | 0x800f);
define("TNEF_AFILENAME",  TNEF_STRING | 0x8010);
define("TNEF_ARENDDATA",  TNEF_BYTE   | 0x9002);
define("TNEF_AMAPIATTRS", TNEF_BYTE   | 0x9005);
define("TNEF_AVERSION",   TNEF_DWORD  | 0x9006);

define("TNEF_MAPI_NULL",           0x0001);
define("TNEF_MAPI_SHORT",          0x0002);
define("TNEF_MAPI_INT",            0x0003);
define("TNEF_MAPI_FLOAT",          0x0004);
define("TNEF_MAPI_DOUBLE",         0x0005);
define("TNEF_MAPI_CURRENCY",       0x0006);
define("TNEF_MAPI_APPTIME",        0x0007);
define("TNEF_MAPI_ERROR",          0x000a);
define("TNEF_MAPI_BOOLEAN",        0x000b);
define("TNEF_MAPI_OBJECT",         0x000d);
define("TNEF_MAPI_INT8BYTE",       0x0014);
define("TNEF_MAPI_STRING",         0x001e);
define("TNEF_MAPI_UNICODE_STRING", 0x001f);
define("TNEF_MAPI_SYSTIME",        0x0040);
define("TNEF_MAPI_CLSID",          0x0048);
define("TNEF_MAPI_BINARY",         0x0102);

define("TNEF_MAPI_ATTACH_MIME_TAG",      0x370E);
define("TNEF_MAPI_ATTACH_LONG_FILENAME", 0x3707);
define("TNEF_MAPI_ATTACH_DATA",          0x3701);

function tnef_getx($size, &$buf)
{
   $value = null;
   if (strlen($buf) >= $size)
   {
      $value = substr($buf, 0, $size);
      $buf = substr_replace($buf, '', 0, $size);
   }
   return $value;
}

function tnef_geti8(&$buf)
{
   $value = null;
   if (strlen($buf) >= 1)
   {
      $value = ord($buf{0});
      $buf = substr_replace($buf, '', 0, 1);
   }
   return $value;
}

function tnef_geti16(&$buf)
{
   $value = null;
   if (strlen($buf) >= 2)
   {
      $value = ord($buf{0}) +
               (ord($buf{1}) << 8);
      $buf = substr_replace($buf, '', 0, 2);
   }
   return $value;
}

function tnef_geti32(&$buf)
{
   $value = null;
   if (strlen($buf) >= 4)
   {
      $value = ord($buf{0}) +
               (ord($buf{1}) << 8) +
               (ord($buf{2}) << 16) +
               (ord($buf{3}) << 24);
      $buf = substr_replace($buf, '', 0, 4);
   }
   return $value;
}

function tnef_decode_attribute($attribute, &$buf)
{
   global $debug;

   $length = tnef_geti32($buf);
   $value = tnef_getx($length, $buf); //data
   tnef_geti16($buf); //checksum

   if ($debug)
   {
      printf("ATTRIBUTE[%08x] %d bytes\n", $attribute, $length);
   }

   switch($attribute)
   {
      case TNEF_BODYTEXT:
         if ($debug)
         {
            printf("<b>Embedded message:</b><pre>%s</pre>", $value);
         }
         break;

      default:
   }
}

function extract_mapi_attrs($buf, &$attachment_data)
{
   global $debug;

   tnef_geti32($buf); // number of attributes
   while(strlen($buf) > 0)
   {
      $value = null;
      $length = 0;
      $attr_type = tnef_geti16($buf);
      $attr_name = tnef_geti16($buf);
      if ($debug)
      {
         printf("mapi attribute: %04x:%04x\n", $attr_type, $attr_name);
      }
      switch($attr_type)
      {
         case TNEF_MAPI_SHORT:
            $value = tnef_geti16($buf);
            break;

         case TNEF_MAPI_INT:
         case TNEF_MAPI_BOOLEAN:
            $value = tnef_geti32($buf);
            break;

         case TNEF_MAPI_FLOAT:
            $value = tnef_getx(4, $buf);
            break;

         case TNEF_MAPI_DOUBLE:
         case TNEF_MAPI_SYSTIME:
            $value = tnef_getx(8, $buf);
            break;

         case TNEF_MAPI_STRING:
         case TNEF_MAPI_UNICODE_STRING:
         case TNEF_MAPI_BINARY:
         case TNEF_MAPI_OBJECT:
            $num_vals = tnef_geti32($buf);
            for ($i = 0; $i < $num_vals; $i++) // usually just 1
            {
               $length = tnef_geti32($buf);
               $buflen = $length + ((4 - ($length % 4)) % 4); // pad to next 4 byte boundary
               $value = substr(tnef_getx($buflen, $buf), 0, $length); // read and truncate to length
            }
            break;

         default:
            if ($debug)
            {
               echo("Unknown mapi attribute!\n");
            }
      }

      // store any interesting attributes
      switch($attr_name)
      {
         case TNEF_MAPI_ATTACH_LONG_FILENAME: // used in preference to AFILENAME value
            $attachment_data[0]['name'] = preg_replace('/.*[\/](.*)$/', '\1', $value); // strip path
            break;

         case TNEF_MAPI_ATTACH_MIME_TAG: // Is this ever set, and what is format?
            $attachment_data[0]['type0'] = preg_replace('/^(.*)\/.*/', '\1', $value);
            $attachment_data[0]['type1'] = preg_replace('/.*\/(.*)$/', '\1', $value);
            break;

         case TNEF_MAPI_ATTACH_DATA:
            tnef_getx(16, $value); // skip the next 16 bytes (unknown data)
	    array_shift($attachment_data); // eliminate the current (bogus) attachment
            do_tnef_decode($value, $attachment_data); // recursively process the attached message
            break;

         default:
      }
   }
}

function tnef_decode_message(&$buf)
{
   global $debug;

   if ($debug)
   {
      echo("MESSAGE ");
   }

   $attribute = tnef_geti32($buf);
   tnef_decode_attribute($attribute, $buf);
}

function tnef_decode_attachment(&$buf, &$attachment_data)
{
   global $debug;

   if ($debug)
   {
      echo("ATTACHMENT ");
   }

   $attribute = tnef_geti32($buf);
   switch($attribute)
   {    
      case TNEF_ARENDDATA: // marks start of new attachment
         $length = tnef_geti32($buf);
         tnef_getx($length, $buf);
         tnef_geti16($buf); //checksum
         if ($debug)
         {
            printf("ARENDDATA[%08x]: %d bytes\n", $attribute, $length);
         }
         // add a new default data block to hold details of this attachment
         // reverse order is easier to handle later!
         array_unshift($attachment_data, array('type0'  => 'application',
                                               'type1'  => 'octet-stream',
                                               'name'   => 'unknown',
                                               'stream' => ''));
         break;

      case TNEF_AFILENAME: // filename
         $length = tnef_geti32($buf);
         $attachment_data[0]['name'] = preg_replace('/.*[\/](.*)$/',
                                                    '\1',
                                                    tnef_getx($length, $buf)); // strip path
         tnef_geti16($buf); //checksum
         if ($debug)
         {
            printf("AFILENAME[%08x]: %s\n", $attribute, $attachment_data[0]['name']);
         }
         break;

      case TNEF_ATTACHDATA: // the attachment itself
         $length = tnef_geti32($buf);
         $attachment_data[0]['size'] = $length;
         $attachment_data[0]['stream'] = tnef_getx($length, $buf);
         tnef_geti16($buf); //checksum
         if ($debug)
         {
            printf("ATTACHDATA[%08x]: %d bytes\n", $attribute, $length);
         }
         break;

      case TNEF_AMAPIATTRS:
         $length = tnef_geti32($buf);
         $value = tnef_getx($length, $buf);
         tnef_geti16($buf); //checksum
         if ($debug)
         {
            printf("AMAPIATTRS[%08x]: %d bytes\n", $attribute, $length);
         }
         extract_mapi_attrs($value, $attachment_data);
         break;

      default:
         tnef_decode_attribute($attribute, $buf);
   }
}

function do_tnef_decode(&$buf, &$attachment_data)
{
   global $debug;

   $tnef_signature = tnef_geti32($buf);
   if ($tnef_signature == TNEF_SIGNATURE)
   {
      $tnef_key = tnef_geti16($buf);
      if ($debug)
      {
         printf("Signature: 0x%08x\nKey: 0x%04x\n", $tnef_signature, $tnef_key);
      }

      while (strlen($buf) > 0)
      {
         $lvl_type = tnef_geti8($buf);
         switch($lvl_type)
         {
            case TNEF_LVL_MESSAGE:
               tnef_decode_message($buf);
               break;

            case TNEF_LVL_ATTACHMENT:
               tnef_decode_attachment($buf, $attachment_data);
               break;

            default:
               if ($debug)
               {
                  echo("Invalid file format!");
               }
               break 2;
         }
      }
   }
   else
   {
      if ($debug)
      {
         echo("Invalid file format!");
      }
   }
}

function tnef_decode($buf)
{
   global $debug;

   $attachment_data = array();

   if ($debug)
   {
      echo("<pre>");
   }

   do_tnef_decode($buf, $attachment_data);

   if ($debug)
   {
      echo("</pre>");
   }
   return array_reverse($attachment_data);

}

?>
