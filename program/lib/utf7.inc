<?php
//
//
//      utf7.inc - Routines to encode bytes to UTF7 and decode UTF7 strings
//
//      Copyright (C)  1999, 2002  Ziberex and Torben Rybner
//
//
//      Version 1.01    2002-06-08      19:00
//
//      - Adapted for use in IlohaMail (modified UTF-7 decoding)
//      - Converted from C to PHP4
//
//
//      Version 1.00    1999-09-03      19:00
//
//      - Encodes bytes to UTF7 strings
//          *OutString = '\0';
//          StartBase64Encode();
//          for (CP = InString;  *CP;  CP++)
//            strcat(OutString, Base64Encode(*CP));
//          strcat(OutString, StopBase64Encode());
//      - Decodes Base64 strings to bytes
//          StartBase64Decode();
//          for (CP1 = InString, CP2 = OutString;  *CP1 && (*CP1 != '=');  CP1++)
//            CP2 += Base64Decode(*CP1, CP2);
//          StopBase64Decode();
//

$BASE64LENGTH              =  60;

$BASE64DECODE_NO_DATA      = -1;
$BASE64DECODE_EMPTY_DATA   = -2;
$BASE64DECODE_INVALID_DATA = -3;


//
//
//      Used for conversion to UTF7
//
$_ToUTF7 = array
(
 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f',
 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', ','
);

//
//
//      Used for conversion from UTF7
//      (0x80 => Illegal, 0x40 => CR/LF)
//
$_FromUTF7 = array
(
  0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80,       // 00 - 07 - Ctrl -
  0x80, 0x80, 0x40, 0x80, 0x80, 0x40, 0x80, 0x80,       // 08 - 0F - Ctrl -
  0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80,       // 10 - 17 - Ctrl -
  0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80,       // 18 - 1F - Ctrl -
  0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80, 0x80,       // 20 - 27  !"#$%&'
  0x80, 0x80, 0x80, 0x3E, 0x3F, 0x80, 0x80, 0x3F,       // 28 - 2F ()*+,-./
  0x34, 0x35, 0x36, 0x37, 0x38, 0x39, 0x3A, 0x3B,       // 30 - 37 01234567
  0x3C, 0x3D, 0x80, 0x80, 0x80, 0x00, 0x80, 0x80,       // 38 - 3F 89:;<=>?
  0x80, 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06,       // 40 - 47 @ABCDEFG
  0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E,       // 48 - 4F HIJKLMNO
  0x0F, 0x10, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16,       // 50 - 57 PQRSTUVW
  0x17, 0x18, 0x19, 0x80, 0x80, 0x80, 0x80, 0x80,       // 58 - 5F XYZ[\]^_
  0x80, 0x1A, 0x1B, 0x1C, 0x1D, 0x1E, 0x1F, 0x20,       // 60 - 67 `abcdefg
  0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28,       // 68 - 6F hijklmno
  0x29, 0x2A, 0x2B, 0x2C, 0x2D, 0x2E, 0x2F, 0x30,       // 70 - 77 pqrstuvw
  0x31, 0x32, 0x33, 0x80, 0x80, 0x80, 0x80, 0x80,       // 78 - 7F xyz{|}~
);


//
//
//      UTF7EncodeInit:
//
//      Start the encoding of bytes
//
function UTF7EncodeInit(&$Context)
{
  $Context[ "Data" ]  = "";
  $Context[ "Count" ] = 0;
  $Context[ "Pos" ]   = 0;
  $Context[ "State" ] = 0;
} // UTF7EncodeInit


//
//
//      UTF7EncodeByte:
//
//      Encodes one byte to UTF7
//
function UTF7EncodeByte(&$Context, $Byte)
{
  global $_ToUTF7;

  $Byte = ord($Byte);
  switch ($Context[ "State" ])
  {
    case 0:
      // Convert into a byte
      $Context[ "Data" ] = $_ToUTF7[ $Byte >> 2 ];
      $Context[ "Pos" ]++;
      // Save residue for next converted byte
      $Context[ "Residue" ] = ($Byte & 0x03) << 4;
      // This is the first byte in this line
      $Context[ "Count" ] = 1;
      // Next state is 1
      $Context[ "State" ] = 1;
      break;
 
    case 1:
      // Convert into a byte
      $Context[ "Data" ] .= $_ToUTF7[ $Context[ "Residue" ] | ($Byte >> 4) ];
      $Context[ "Pos" ]++;
      // Save residue for next converted byte
      $Context[ "Residue" ] = ($Byte & 0x0F) << 2;
      // Bumb byte counter
      $Context[ "Count" ]++;
      // Next state is 2
      $Context[ "State" ] = 2;
      break;
 
    case 2:
      // Convert into a byte
      $Context[ "Data" ] .= $_ToUTF7[ $Context[ "Residue" ] | ($Byte >> 6) ];
      $Context[ "Pos" ]++;
      // Residue fits precisely into the next byte
      $Context[ "Data" ] .= $_ToUTF7[ $Byte & 0x3F ];
      $Context[ "Pos" ]++;
      // Bumb byte counter
      $Context[ "Count" ]++;
      // Next state is 3
      $Context[ "State" ] = 3;
      break;

    case 3:
      // Convert into a byte
      $Context[ "Data" ] .= $_ToUTF7[ $Byte >> 2 ];
      $Context[ "Pos" ]++;
      // Save residue for next converted byte
      $Context[ "Residue" ] = ($Byte & 0x03) << 4;
      // Bumb byte counter
      $Context[ "Count" ]++;
      // Next state is 1
      $Context[ "State" ] = 1;
      break;
 
    default:
      // printf("Internal error in UTF7Encode: State is %d\n", $Context[ "State" ]);
      // exit(1);
      break;
  }
} // UTF7EncodeByte


//
//
//      UTF7EncodeFinal:
//
//      Terminates the encoding of bytes
//
function UTF7EncodeFinal(&$Context)
{
  if ($Context[ "State" ] == 0)
    return "";
  if ($Context[ "State" ] != 3)
    UTF7EncodeByte($Context, "\0");
  return $Context[ "Data" ];
} // UTF7EncodeFinal


//
//
//      UTF7EncodeString
//
//      Encodes a string to modified UTF-7 format
//
function UTF7EncodeString($String)
{
  // Not during encoding, yet
  $Encoding = false;
  // Go through the string
  for ($I = 0;  $I < strlen($String);  $I++)
  {
    $Ch = substr($String, $I, 1);
    if (ord($Ch) > 0x7F)
    {
      if (! $Encoding)
      {
        $RetVal .= "&";
        $Encoding = true;
        // Initialise UTF7 context
        UTF7EncodeInit($Context);
      }
      UTF7EncodeByte($Context, "\0");
      UTF7EncodeByte($Context, $Ch);
    }
    elseif ($Ch == "&")
    {
      if (! $Encoding)
      {
        $RetVal .= "&";
        $Encoding = true;
        // Initialise UTF7 context
        UTF7EncodeInit($Context);
      }
      else
      {
        UTF7EncodeByte($Context, "\0");
        UTF7EncodeByte($Context, $Ch);
      }
    }
    else
    {
      if ($Encoding)
      {
        $RetVal .= UTF7EncodeFinal($Context) . "-$Ch";
        $Encoding = false;
      }
      else
        $RetVal .= $Ch;
    }
  }
  if ($Encoding)
    $RetVal .= UTF7EncodeFinal($Context) . "-";
  return $RetVal;
} // UTF7EncodeString


//
//
//      UTF7DecodeInit:
//
//      Start the decoding of bytes
//
function UTF7DecodeInit(&$Context)
{
  $Context[ "Data" ]  = "";
  $Context[ "State" ] = 0;
  $Context[ "Pos" ]   = 0;
} // UTF7DecodeInit


//
//
//      UTF7DecodeByte:
//
//      Decodes one character from UTF7
//
function UTF7DecodeByte(&$Context, $Byte)
{
  global $BASE64DECODE_INVALID_DATA;
  global $_FromUTF7;

  // Restore bits
  $Byte = $_FromUTF7[ ord($Byte) ];
  // Ignore carriage returns and linefeeds
  if ($Byte == 0x40)
    return "";
  // Invalid byte - Tell caller!
  if ($Byte == 0x80)
    $Context[ "Count" ] = $BASE64DECODE_INVALID_DATA;
  switch ($Context[ "State" ])
  {
    case 0:
      // Save residue
      $Context[ "Residue" ] = $Byte;
      // Initialise count
      $Context[ "Count" ] = 0;
      // Next state
      $Context[ "State" ] = 1;
      break;

    case 1:
      // Store byte
      $Context[ "Data" ] .= chr(($Context[ "Residue" ] << 2) | ($Byte >> 4));
      $Context[ "Pos" ]++;
      // Update count
      $Context[ "Count" ]++;
      // Save residue
      $Context[ "Residue" ] = $Byte;
      // Next state
      $Context[ "State" ] = 2;
      break;

    case 2:
      // Store byte
      $Context[ "Data" ] .= chr(($Context[ "Residue" ] << 4) | ($Byte >> 2));
      $Context[ "Pos" ]++;
      // Update count
      $Context[ "Count" ]++;
      // Save residue
      $Context[ "Residue" ] = $Byte;
      // Next state
      $Context[ "State" ] = 3;
      break;

    case 3:
      // Store byte
      $Context[ "Data" ] .= chr(($Context[ "Residue" ] << 6) | $Byte);
      $Context[ "Pos" ]++;
      // Update count
      $Context[ "Count" ]++;
      // Next state
      $Context[ "State" ] = 4;
      break;

    case 4:
      // Save residue
      $Context[ "Residue" ] = $Byte;
      // Next state
      $Context[ "State" ] = 1;
      break;
  }
} // UTF7DecodeByte


//
//
//      UTF7DecodeFinal:
//
//      Decodes one character from UTF7
//
function UTF7DecodeFinal(&$Context)
{
  // Buffer not empty - Return remainder!
  if ($Context[ "Count" ])
  {
    $Context[ "Pos" ] = 0;
    $Context[ "State" ] = 0;
    return $Context[ "Data" ];
  }
  return "";
} // UTF7DecodeFinal


//
//
//      UTF7DecodeString
//
//      Converts a string encoded in modified UTF-7 encoding
//      to ISO 8859-1.
//      OBS: Works only for valid ISO 8859-1 characters in the
//      encoded data
//
function UTF7DecodeString($String)
{
  $Decoding = false;
  for ($I = 0;  $I < strlen($String);  $I++)
  {
    $Ch = substr($String, $I, 1);
    if ($Decoding)
    {
      if ($Ch == "-")
      {
        $RetVal .= UTF7DecodeFinal($Context);
        $Decoding = false;
      }
      else
        UTF7DecodeByte($Context, $Ch);
    }
    elseif ($Ch == "&")
    {
      if (($I < strlen($String) - 1) && (substr($String, $I + 1, 1) == "-"))
      {
        $RetVal .= $Ch;
        $I++;
      }
      else
      {
        UTF7DecodeInit($Context);
        $Decoding = true;
      }
    }
    else
      $RetVal .= $Ch;
  }
  return str_replace("\0", "", $RetVal);
} // UTF7DecodeString
?>
