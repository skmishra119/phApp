<?php 

require("email_functions.php"); 

global $timeout, $error, $buffer; 

// Date in the past 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 

ob_start(); 

//----------------------------------------------------------------------------- 
//                 Declarations 
//----------------------------------------------------------------------------- 

$error            = "";                                                        //    Error string. 

$timeout          = 90;                                                  //    Default timeout before giving up on a network operation. 

$Count            = -1;                                                        //    Mailbox msg count 

$buffer           = 512;                          //    Socket buffer for socket fgets() calls, max per RFC 1939 the returned line a POP3 
                                                                            //    server can send is 512 bytes. 

$server               = "mail.ashirbad.in";           //    Set this to hard code the server name 

$RFC1939          = true;                                                    //   Set by noop(). See rfc1939.txt 

$msg_list_array = array();                                                  //    List of messages from server 

$login                 = 'accounts@ashirbad.in'; 
$pass                  = 'finance'; 
$KeyUserID            = 'me';                                                      //        for the db record to id the program as the user 

//----------------------------------------------------------------------------- 
//                 Control Code 
//----------------------------------------------------------------------------- 
   //echo "<h2>Starting Program</h2><br>"; 
        set_time_limit($timeout); 

        $fp = connect ($server, $port = 110); 

    $Count = login($login,$pass, $fp); 

    if( (!$Count) or ($Count == -1) ) 
    { 
        do_log("TIRS", "Check for new messages",'', "No Messages"); 
        exit; 
    }// end if 

    // ONLY USE THIS IF YOUR PHP VERSION SUPPORTS IT! 
    // register_shutdown_function(quit()); 

    if ($Count < 1) 
    { 
        die(); 
    } else { 
        //echo "Login OK: Inbox contains [$Count] messages<BR>\n"; 
        do_log("TIRS", "Check for new messages",'', "$Count Messages"); 
        $msg_list_array = uidl("", $fp); 
        set_time_limit($timeout); 
    }// end if 

    // loop thru the array to get each message 
    for ($i=1; $i <= $Count; $i++){ 
            set_time_limit($timeout); 
            $MsgOne = get($i, $fp); 

            if( (!$MsgOne) or (gettype($MsgOne) != "array") ) 
            { 
                //echo "oops, Message not returned by the server.<BR>\n"; 
                exit; 
            }// end if 
            /* 
                call the function to read the message 
                returns true if access, breakdown and insertion 
                in to db are completed sucessfully 
           */ 
              message_details($MsgOne, $i, $fp); 

      }// end for loop 

      //close the email box and delete all messages marked for deletion 
      quit($fp); 

    //close the application 
    //echo "<br>Finished</b>"; 
    exit; 

//----------------------------------------------------------------------------- 
//                                    Function Listing 
//----------------------------------------------------------------------------- 

//----------------------------------------------------------------------------- 
//                                    Get the Message Details 
//----------------------------------------------------------------------------- 
function     message_details($MsgOne, $msgNo, $fp) 
        { 
        /* 
        Function to read the message and extract : 
            a. subject 
            b. date 
            c. split the body line by line 
   */ 
            $body                     = '';                    // get the body of the message into 1 variable 
            $subjects             = '';         // get the subject of the email 
            $dates                     = '';                    // get the date of the email 
           $body_start_key = false;          // body starts at blank line, blank line is separator for from headers to body 
           $TIRSFlag             = false;      // flag for seeing if the email is really to be processed by the app 
           $base64Flag     = false;      // flag to handle base 64 encoding by email systems. 



            foreach ($MsgOne as $key => $value) 
        { 
        if (trim($value) == "Content-Transfer-Encoding: base64"){ 
          $base64Flag = true; 
        }//end if 

        //get the subject line of the email 
        if (strlen(stristr($value, "Subject"))>1){ 
          $subjects = trim(stristr($value, " ")); 

          //look for IncidentNo in the subject to see if we need to attempt to process the email 
          if (strlen(stristr($subjects, "IncidentNo:"))>1){ 
              $TIRSFlag = true; 
            }// end if 
        }// end if 

        //get the date of the email 
        if (strlen(stristr($value, "Date"))>1){ 
          $dates = trim(stristr($value, " ")); 
            $date_key = $key; 
        }// end if 


        //the body 
        if (strlen(trim($value))==0){ 
          if ($body_start_key == false){ $body_start_key = true; } //set the start key for the body 
        }//end if 

        if ($body_start_key == true){ 
          $body .= trim($value); 
          if ($base64Flag == false){ $body .="<br />"; } 
        }// end if 

      }// end foreach 

      // only create incident if the subject line contains the word incident 
      if ($TIRSFlag == false){ 

          //delete the message 
          delete($msgNo, $fp); 

      }else{ 

          //decode the message if its base64 encoded 
          if ($base64Flag == true) { 
            $body = base64_decode($body); 
          }//end if 

          // call the function that does the sql inserts 
          create_incident($subjects, $body, $msgNo, $fp); 

      }// end if 
        }// end function 

//----------------------------------------------------------------------------- 
//                                    Create the incident 
//----------------------------------------------------------------------------- 
function create_incident($subject, $message_body, $msgNo, $fp) 
        { 
             //process the body of the email and take it apart and find stuff in it...do whatever processing you need to here 
             //the code below is simply a hint and probably not relevant to your situation 
             //its strictly here as a guide as to what can be done with the body of the email 
             //my application takes the subject and body apart and creates a record in our web-based 
             //application's database (the code for this has been removed...) 


             // declarations for function 
                $inc_sec_off = ''; 
                $inc_site    = ''; 
                $inc_type    = ''; 
                $inc_issue   = ''; 
                $inc_co_id   = ''; 
                $inc_http    = ''; 
              $options     = ''; 

              // take the subject apart to get the individual elements 

           $sub_details         = stristr($subject, ":");                                      // get rest of subject line from the first ':' 
           $split_subject     = explode (" ", $sub_details);                                  // split on space 
           $incident_no            =    trim($split_subject[1]);                                      // incident number from email 
           $incident_type        = trim($split_subject[3]);                                      // incident type from email 
           $inc_date                 = trim($split_subject[5]);                                     // incident date from email 
             $inc_dates                = explode("/",$inc_date); 


             if (count($inc_dates)>1){ 
                     $inc_date            = $inc_dates[2]."-".$inc_dates[0]."-".$inc_dates[1]; 
    //proper date format (Damned Americans) 
             }//end if 

             $weekday                    = date( "l", $inc_date);                                                         // get the weekday 

             $time                         = strtotime($inc_date . " ". trim($split_subject[6])); 

             $inc_time                 = date("H:i:s", strftime($time) );                                   // incident time from email 

             //convert the body crlf to <br> tags if not done when the message was built 
             if (strlen(stristr($message_body,"<br>"))==0){ 
                     $message_body = nl2br($message_body); 
           } 

           // take the body apart to get the individual elements 
           $body                        =    explode("<br />", $message_body); 

           for ($x=0; $x<count($body); $x++){ 

                   if (strlen(stristr($body[$x], "Security Officer"))>1)       {  
$inc_sec_off            = substr(stristr($body[$x], ":"),1);   } 
                   if (strlen(stristr($body[$x], "client_number"))>1)                {  $inc_site  
            = substr(stristr($body[$x], ":"),1);   } 
                   if (strlen(stristr($body[$x], "Company ID"))>1)                      {  $inc_co_id  
            = substr(stristr($body[$x], ":"),1);   } 
                   if (strlen(stristr($body[$x], "Are"))>1)                            {  $inc_issue 
            = substr(stristr($body[$x], ":"),1);   } 
                   if (strlen(stristr($body[$x], "http"))>1)                           {  $inc_https 
            = explode("//",$body[$x]);                 } 

       }// end for loop 

       //echo $message_body; 


      //delete the email if the insertion was succesful 
          if ($result && $result1){ 
            delete ($msgNo, $fp); 
          }// end if 


        }// end function 


?>