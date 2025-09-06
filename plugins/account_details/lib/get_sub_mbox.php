<?php
// open a connection
$imapsub = imap_open("{localhost}mbox","%u","%p");

// list all mailboxes
$boxes = imap_listmailbox($imap, "{localhost}", "*");
for ($i=0; $i<count($boxes); $i++) {
			$table->add('title', '&nbsp;&#9679;&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('drafts') . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('folder') . ':')));
			$table->add('value', $boxes[$i] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('unread') . '&nbsp;-&nbsp;' . $boxes[$i] . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('total') . '&nbsp;-&nbsp;' . round($imap->folder_size('INBOX.Drafts')/ 1024),2) . '&nbsp;' . rcube_utils::rep_specialchars_output($this->gettext('KB'))));
			}
}
?>