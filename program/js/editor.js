/*
 +-----------------------------------------------------------------------+
 | RoundCube editor js library                                           |
 |                                                                       |
 | This file is part of the RoundCube web development suite              |
 | Copyright (C) 2006, RoundCube Dev, - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Eric Stadtherr <estadtherr@gmail.com>                         |
 +-----------------------------------------------------------------------+

 $Id: editor.js 000 2006-05-18 19:12:28Z roundcube $
*/

// Initialize the message editor

function rcmail_editor_init(skin_path, editor_lang)
  {
  tinyMCE.init({ mode : "textareas",
                 editor_selector : "mce_editor",
                 accessibility_focus : false,
                 apply_source_formatting : true,
                 theme : "advanced",
                 language : editor_lang,
                 plugins : "emotions,media,nonbreaking,table,searchreplace,visualchars,directionality",
                 theme_advanced_buttons1 : "bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,separator,link,unlink,emotions,charmap,code,forecolor,backcolor,fontselect,fontsizeselect, separator,undo,redo,image,media,ltr,rtl",
                 theme_advanced_buttons2 : "",
                 theme_advanced_buttons3 : "",
                 theme_advanced_toolbar_location : "top",
                 theme_advanced_toolbar_align : "left",
                 extended_valid_elements : "font[face|size|color|style],span[id|class|align|style]",
                 content_css : skin_path + "/editor_content.css",
                 external_image_list_url : "editor_images.js"
               });
  }

// Toggle between the HTML and Plain Text editors

function rcmail_toggle_editor(toggler)
  {
  var selectedEditor = toggler.value;

  // determine the currently displayed editor
  var htmlFlag = document.getElementsByName('_is_html')[0];
  var isHtml = htmlFlag.value;

  if (((selectedEditor == 'plain') && (isHtml == "0")) ||
      ((selectedEditor == 'html') && (isHtml == "1")))
    {
    return;
    }

  // do the appropriate conversion

  var composeElement = document.getElementById('compose-body');

  if (selectedEditor == 'html')
    {
    var existingPlainText = composeElement.value;
    var htmlText = "<pre>" + existingPlainText + "</pre>";
    composeElement.value = htmlText;
    tinyMCE.execCommand('mceAddControl', true, 'compose-body');
    htmlFlag.value = "1";
    }
  else
    {
    rcmail.set_busy(true, 'converting');
    var thisMCE = tinyMCE.get('compose-body');
    var existingHtml = thisMCE.getContent();
    rcmail_html2plain(existingHtml);
    tinyMCE.execCommand('mceRemoveControl', true, 'compose-body');
    htmlFlag.value = "0";
    }
  }

function rcmail_html2plain(htmlText)
  {
  var http_request = new rcube_http_request();

  http_request.onerror = function(o) { rcmail_handle_toggle_error(o); };
  http_request.oncomplete = function(o) { rcmail_set_text_value(o); };
  var url = rcmail.env.bin_path+'html2text.php';
  console.log('HTTP request: ' + url);
  http_request.POST(url, htmlText, 'application/octet-stream');
  }

function rcmail_set_text_value(httpRequest)
  {
  rcmail.set_busy(false);
  var composeElement = document.getElementById('compose-body');
  composeElement.value = httpRequest.get_text();
  }

function rcmail_handle_toggle_error(httpRequest)
  {
  alert('html2text request returned with error ' + httpRequest.xmlhttp.status);
  }
