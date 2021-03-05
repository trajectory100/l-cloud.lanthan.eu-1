<?PHP

ini_set('max_execution_time', 0);
require_once('db.php');
// // --------- Connect  to the server -----------------------------------

// // ------------------------------------------------------------------------------------------
// //                 Sending email to detect the error and timeout 
// // ------------------------------------------------------------------------------------------
// // --------- Running time to limit the emails -----------------------------------
$currentTime = time();
$time = (6*60*60);
$timeToSubtract = (24*60* 60);
$limittime = $currentTime - $timeToSubtract;
// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'");
while ($zeile = $projini->fetch_array()) {
        $puser = $zeile['PUSER'];
        $ort = $zeile['ORT'];
        $ort2 = $zeile['ORT2'];
        $interval = intval($zeile['MSGINTERVAL']);

        // --------- Use bfccio to for the last record-----------------------------------
    // $all =  $mysqli->query("SELECT * FROM bfccio WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    $all =  $mysqli->query("SELECT * FROM test WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    while ($row1 = $all->fetch_array()) {
        $count= 0;
        $time1 = 0 ;
        $dattime1 = $row1['time'];
        $deltaT1 = (int)(($currentTime - $dattime1) / 60);
        $time1 = ($deltaT1 > (int)($interval * 1));
        $ID = $row1['ID'];

     
        //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
        // $latest =  $mysqli->query("SELECT * FROM bfccio  WHERE imei= '$puser'  ORDER BY time DESC LIMIT 2 ;");
        $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1 ;");
        $yes = 0;
        while ($row2 =  $latest ->fetch_array()) {
            $time2 = 0 ;
            $datazeit = $row2['time'];
            $deltaT2 = (int)(($currentTime  - $datazeit) / 60);
            $time2 = ($deltaT2 > (int)($interval * 2));
            $sendtimeout =   $dattime1 -  $datazeit;
            

             //   if (($imei1 == $imei2) &&  ($time1 !== $time2) && ($dattime1 < $limittime)) {
                    if (($imei1 == $imei2) &&  ($time1 !== $time2) && ($dattime1 < $limittime)) {

                    $count=1;
                    $iden = 0;
                 //echo  $puser.':'. (int)$time1. ':'.(int)$time2. ':'.(int)$sendtimeout.':'.(int)$timeToSubtract.'<br>';
                    if (((int)$time1 == 1 && (int)$time2 == 0)){
                            $iden = 1;
                            //$type = 'Timeout';
                            $topic = 'Fehler Hindernisbefeuerung';
                            //$text = 'Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:';
                            // $type_note='Die letzten Daten wurden am '. date('j.n.Y.', $dattime1).' um '.date('H:i:s', $dattime1).' empfangen.';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:" . "</p>" ;
                            $time = "Die letzten Daten wurden am " . date('j.n.Y', $dattime1) . " um " . date('H:i:s', $dattime1). " empfangen.</p>";
                            echo 'imei1 = '. $imei1.'imei2 = '. $imei2. 'time = '. $dattime1.'  the interval= '. $sendtimeout. '<br>';
                       
                    // } elseif (((int)$time1 == 0 && (int)$time2 == 1)&& ((int)$sendtimeout > (int)$timeToSubtract))  {
                    } elseif (((int)$time1 == 0 && (int)$time2 == 1)){
                    // } elseif (($time1 == 0 && $time2 == 1) && ((int)$sendtimeout > (int)$timeToSubtract)) {
                    // } elseif ($time1 === 0 && $time2 === 1) {
                            $iden = 1;
                            //$type = 'Timeout gone';
                            $topic= 'Timout Hindernisbefeuerung behoben';
                           // $text ='Folgendes Hindernisbefeuerungssystem überträgt seit '. date('j.n.Y.', $dattime1).' um '.date('H:i:s', $dattime1).' wieder erfolgreich Daten:';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem überträgt seit" . date('j.n.Y.', $dattime1). " um " .date('H:i:s', $dattime1)." wieder erfolgreich Daten."."</p>";
                            echo 'imei1 = '. $imei1.'  * imei2 = '. $imei2. ' * time = '. $dattime1.'  the interval= '. $sendtimeout.'<br>';

                    }else{
                        $iden = 0;
                    }
            //    echo  $puser. '  '. (int)$time1. '  '. (int)$time2 . '   '. $type. '<br>';
                if ($iden === 1) {
                $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                            bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                            LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  '."$puser".' ;');
                while ($rowr = $result->fetch_array()) {
                    if (!empty($rowr['EMAIL'])) {
                        // $email = (int)$rowr['EMAIL'];
                        $userID = (int)$rowr['userId'];
                        $deviceId = $rowr['device'];
                                                        
                        /////////////>>>>>>>
                                
                        
                        // $subject = "Lanthan Alram Email";
                        $html_table  = "<p><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></p>";
                        $html_table .= "<p>Lanthan GmbH & Co. KG </p>";
                        $html_table .= "<p>Jakobistraße 25A, 28195 Bremen</p> ";
                        $html_table .= "<p>Telefon +49 / (0)421 / 696 465-14</p> ";
                        $html_table .= "<p><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";

                        // $headers = "MIME-Version: 1.0" . "\r\n";
                        // $headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";
                        // $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                        // $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                        // $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";

                        $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
                        $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
                        $message .= "<style> div.a {line-height: 80%;  text-align: left; }  div.main_text { line-height: 1.3;} div.footer_text { line-height: 1;}
                         div.center {border: 2px solid  #ffffff; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; width: 50%;} </style>";
                         $message .= "<div class='formbox' style='border: 1px solid  black;background-color: black; padding: 5px ; margin: 2px 2px; text-align: left;' ;>";
                         // $message .= "<div class='formbox' style='border: 1px solid  black;background-color: black; padding: 10px ; margin: 2px 2px; text-align: left;' ;>";

                         $message .= "<img src=\"http://test.lanthan.eu/image/lanthan_status_600x150.jpg\" style=\" width: 300px; height: 75px;
                         padding: 3px; background-clip: content-box; box-shadow: inset 0 0 0 15px white;\" alt=\"img\";>";

                         
                        $message .= "</div>" . "<di class=\"main_text\">";
                        $message .= "<h2><b><u>" . $topic . "</u></b></h2>";
                        $message .= "<p>Guten Tag, </p>";
                        $message .= "<p>" . $text . "</p>";
                        $message .= "<p>" . $time . "</p>";
                        $message .= "<p> Adresse der Station: " . $ort . "</p>";
                        $message .= "<p  style= \"margin-left: 128px;\">" . $ort2 . "</p>";
                        $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p>";
                            $message .= "<p>" . $open_page . "</p>";
                            $message .= "<br><br><hr style=\"height:2px;border-width:0;color:gray;background-color: black ;width: 60%;float:left;\"><br>";
                            $message .= "</div>" . "<div class=\"footer_text \">";
                            $message .= $html_table2;
                            $message .= "<div class='formbox' style='width: 42%;border: 2px solid  transparent; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
                            $message .= "" . $html_table . "</div>";
                            $message .= "</div>" . "</div>";


                                //  $subject = "Statusmeldung";
                                //  $message = "<html><head>"; 
                                //  $headers = "MIME-Version: 1.0" . "\r\n";
                                //  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                //  $headers .= "<meta http-equiv=\"content-language\" content=\"de\">". "\r\n";
                                //  $message = "</head><body>"; 
                                //  $message.= "<style> div.a {line-height: 10px;} p {text-indent: 50px;} </style>";
                                //  // $message.= "<style> div.a {line-height: 10px;} p {text-indent: 50px;  text-justify:auto;} </style>";
                                //  $message .= "<div class=\"a\">";
                                //  $message .= "<h2><u>" . $type . "</u></h2>";
                                //  $message .= "<h4>Topic:</h4>";
                                //  $message .= "<p>" . $topic . "</p>";
                                //  $message .= "<h4>Text:</h4>";
                                //  $message .= "<p>Guten Tag, </p>";
                                //  $message .= "<p>". $text ."</p>";
                                //  $message .= "<p>". $ort ."</p>";
                                //  $message .= "<p>". $ort2 ."</p>";
                                //  $message .= "<p>". $type_note  ."</p>";
                                //  $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p></br>";
                                //  $message .= "<p>Bei Fragen wenden Sie sich bitte an</p>";
                                //  $message .= "<p>Wilfried Richter</p>";
                                //  $message .= "<p>Fon: +49 / (0)421 / 696 465-14 </p>";
                                //  $message .= "<p>Email: <a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";
                                //  $message .= "<p><br><br></p>";
                                //  $message .= "</div>";
                                //  $message .= "</body></html>";                            
                                //  $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                                 $headers .= "Bcc: farzaneh.shams@lanthan.eu". "\r\n";
                                echo   $message;
                                 mail('farzaneh.shams@lanthan.eu', $subject, $message, $headers);
                                // if (mail($email, $subject, $message, $headers)) {
        // //                     //      if (mail($email, $subject, $message, $headers)) {
                                  echo 'Email has sent successfully.';
                                 } else {
                                     echo 'Email sending failed.';
                                 } 
        // //                      } 
            
                        }
                      }
                    
            }           
            // if (($imei1 == $imei2) &&  ($time1 == $time2)  && ($dattime1 > $limittime)){

            if (($imei1 == $imei2) &&  ($time1 == $time2)){
            $count=0;
            }
        }
    }
}

// echo "ok";