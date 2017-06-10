<? 
//<!-- Version=2.30 Date=11/08/04 Name=logging.php --> 
/* 

----------------------------------------------------------------------------- 

    goes to db  
    if db not available, goes to text file 
*/ 

function do_log($client, $activity, $sql='', $success) 
        { 
        //logs the client, activity, sql and success or failure of the action 
        // 
        /* 
        $host = "192.168.100.10"; 
        $user = "root"; 
        $pass = "3JN4xM9Jvgj2VwHe"; 
          
        */ 
        $host  = '192.168.160.31';    
        $user  = 'rume'; 
        $pass  = 'RumE2#$w'; 
        $db     = "loggingdb"; 
          
        $err = 0; 
            //echo "commnecing connection to local db<br>"; 
              
            if (!($conn=mysql_connect($host, $user, $pass)))  { 
                $err = mysql_errno(); 
            } 
            if (!$db3=mysql_select_db($db, $conn)){ //or die("Unable to connect to local database"); 
                $err = mysql_errno(); 
            } 
              
            if ($err != 0){ 
              
              //database is not available or can't be connected to at the moment 
              write_log($client, $activity, $sql, $success); 
              
            }else{ 
               $time = time(); 
              //enter the data into the log table    
              $sql = "insert into logg (client, sql, activity, success, unix_timestamp) values ('$client','$sql','$activity','$success',$time)";  
               //echo $sql; 
              $result = mysql_query($sql) or die ("Can't connect because ". mysql_error()."<br>$sql"); 
              $err = mysql_errno(); 
        //if ($err != 0){ 
            write_log($client, $activity, $sql, $success);      
        //}// end if 
      
      }// end if 
              
        }//end function      
          
          
function write_log($client, $activity, $sql, $success) 
        { 
            //write the text log 
            $time = time(); 
            $filename = "C:\\apisonline.com\\html\\scripts\\logs\\test.log"; 
              
            $somecontent = "Client\t$client.\nActivity\t$activity\nSQL\t:$sql\nSuccess:\t$success($time)\n\n"; 
                  
                // Let's make sure the file exists and is writable first. 
                if (is_writable($filename)) { 
                  
                   if (!$handle = fopen($filename, 'a')) { 
                         //echo "Cannot open file ($filename)"; 
                         //exit; 
                   } 
                  
                   // Write $somecontent to our opened file. 
                   if (fwrite($handle, $somecontent) === FALSE) { 
                       //echo "Cannot write to file ($filename)"; 
                       //exit; 
                   } 
                    
                   //echo "Success, wrote ($somecontent) to file ($filename)"; 
                    
                    fclose($handle); 
      
            }// end if          
          
        }// end function 

?>