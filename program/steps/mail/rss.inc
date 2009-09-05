<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/rss.inc                                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send mailboxcontents as RSS feed                                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Sjon Hortensius <sjon@hortensius.net>                         |
 +-----------------------------------------------------------------------+

 $Id$

*/


function rss_encode($string){
	$string = rep_specialchars_output($string, 'xml');
	return $string;
}


$REMOTE_REQUEST = TRUE;
$OUTPUT_TYPE = 'rss';

$webmail_url = 'http';
if (strstr('HTTPS', $_SERVER['SERVER_PROTOCOL'] )!== FALSE || $RCMAIL->config->get('use_https'))
  $webmail_url .= 's';
$webmail_url .= '://'.$_SERVER['SERVER_NAME'];
if ($_SERVER['SERVER_PORT'] != '80')
	$webmail_url .= ':'.$_SERVER['SERVER_PORT'];
$webmail_url .= '/';
if (dirname($_SERVER['SCRIPT_NAME']) != '/')
	$webmail_url .= dirname($_SERVER['SCRIPT_NAME']).'/';

$webmail_url .= '?_task=mail';

$messagecount_unread = $IMAP->messagecount('INBOX', 'UNSEEN', TRUE);
$messagecount = $IMAP->messagecount();

$sort_col = 'date';
$sort_order = 'DESC';

// Send global XML output
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="'.RCMAIL_CHARSET.'"?>
	<rss version="2.0"
	 xmlns:dc="http://purl.org/dc/elements/1.1/"
	 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	 xmlns:admin="http://webns.net/mvcb/"
	 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	 xmlns:content="http://purl.org/rss/1.0/modules/content/">';

// Send channel-specific output
echo '
	<channel>
		<pubDate>'.date('r').'</pubDate>
		<lastBuildDate>'.date('r').'</lastBuildDate>
		<ttl>5</ttl>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<description>INBOX contains '.$messagecount.' messages, of which '.$messagecount_unread.' unread</description>
		<link>'.rss_encode($webmail_url, 'xml') .'</link>
		<title>webmail for '.rss_encode($_SESSION['username'].' @ '.$_SESSION['imap_host']).'</title>
		<generator>'.rss_encode($CONFIG['useragent'], 'xml').' (RSS extension by Sjon Hortensius)</generator>
		<image>
			<link>http://www.roundcube.net/</link>
			<title>'.rss_encode($CONFIG['product_name']).' logo</title>
			<url>'.rss_encode($webmail_url.'skins/default/images/roundcube_logo.png').'</url>
		</image>';

// Check if the user wants to override the default sortingmethode
if (isset($_GET['_sort']))
  list($sort_col, $sort_order) = explode('_', get_input_value('_sort', RCUBE_INPUT_GET));

// Add message to output
if ($messagecount > 0)
  {
  $items = $IMAP->list_headers('INBOX', null, $sort_col, $sort_order);
  foreach ($items as $item)
    {

    // Convert '"name" <email>' to 'email (name)'
    if (strstr($item->from, '<'))
      $item->from = preg_replace('~"?([^"]*)"? <([^>]*)>~', '\2 (\1)', $item->from);

    $item->link = $webmail_url.'&_task=mail&_action=show&_uid='.$item->uid.'&_mbox=INBOX';

    $item->body = $IMAP->get_body($item->uid);

    // Print the actual messages
    echo '
			<item>
				<title>'.rss_encode($item->subject).'</title>
				<link>'.rss_encode($item->link).'</link>
				<description><![CDATA['."\n".nl2br(rss_encode($item->body))."\n".']]></description>
				<author>'.rss_encode($item->from).'</author>
				<category></category>
				<guid>'.rss_encode($item->link).'</guid>
				<pubDate>'.date('r', $item->timestamp).'</pubDate>
			</item>';
    }
  }

echo '</channel>
</rss>';

exit;
?>
