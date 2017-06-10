<?php
 
 $mbox = imap_open("{mail.ashirbad.in}INBOX", "accounts@ashirbad.in", "finance") or die("can't connect: " . imap_last_error());

$check = imap_mailboxmsginfo($mbox);

print_r($check);
/*   echo "Date: "    . $check->Date    . "<br />\n" ;
   echo "Driver: "  . $check->Driver  . "<br />\n" ;
   echo "Mailbox: "  . $check->Mailbox . "<br />\n" ;
   echo "Messages: " . $check->Nmsgs  . "<br />\n" ;
   echo "Recent: "  . $check->Recent  . "<br />\n" ;
   echo "Unread: "  . $check->Unread  . "<br />\n" ;
   echo "Deleted: "  . $check->Deleted . "<br />\n" ;
   echo "Size: "    . $check->Size    . "<br />\n" ;

imap_close($mbox);
*/


?>

