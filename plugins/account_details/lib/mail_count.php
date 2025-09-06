<?php
 function CountUnreadMails($host, $login, $passwd) {
      $mbox = imap_open("{{$host}/pop3:110}", $login, $passwd);
      $count = 0;
      if (!$mbox) {
           echo "Error";
      } else {
           $headers = imap_headers($mbox);
           foreach ($headers as $mail) {
                $flags = substr($mail, 0, 4);
                $isunr = (strpos($flags, "U") !== false);
                if ($isunr)
                $count++;
           }
      }

 imap_close($mbox);
 return $count;
 }
 ?>