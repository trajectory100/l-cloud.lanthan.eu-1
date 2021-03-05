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
// $timeToSubtract = (23*60* 60);
$timeToSubtract = (1*60* 60);
$puser = "359315076932515";
$limittime = $currentTime - $timeToSubtract;
// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE PUSER = '$puser'");
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
        $time1 = 0;
        $imei1 = $row1['imei'];
        $dattime1 = $row1['time'];
        $deltaT1 = (int)(($currentTime - $dattime1) / 60);
        $time1 = ($deltaT1 > (int)($interval * 1));
        $ID = $row1['ID'];

     
        //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
        // $latest =  $mysqli->query("SELECT * FROM bfccio  WHERE imei= '$puser'  ORDER BY time DESC LIMIT 2 ;");
        $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1 ;");
        $yes = 0;
        while ($row2 =  $latest ->fetch_array()) {
            $imei2 = $row2['imei'];
            $time2 = 0;
            $datazeit = $row2['time'];
            $deltaT2 = (int)(($currentTime  - $datazeit) / 60);
            $time2 = ($deltaT2 > (int)($interval * 2));
            $sendtimeout =   $dattime1 -  $datazeit;
            $time2 == 1;
            $time1 == 0;
            echo  $ti .'   hear    '.$imei1. "   :   ".  date('j.n.Y. H:i:s', $dattime1). "  :  ".$time1 ."  :  ". $imei2. "   :   ".  date('j.n.Y. H:i:s', $datazeit). "  :  ".(($timo == $time2) ? 0  : 1). "  :  ". date('j.n.Y. H:i:s', $sendtimeout) . ' > '. date('H', $limittime).' :  '.  $check.' <br>';
                    $count=1;
                    $iden = 0;
                    echo  $puser.':'. (int)$time1. ':'.(int)$time2. ':'.(int)$sendtimeout.':'.(int)$timeToSubtract.'<br>';
                    if (((int)$time1 == 1 && (int)$time2 == 0)){
                            $iden = 1;
                            $type = 'Timeout';
                            $topic = 'Fehler Hindernisbefeuerung';
                            $text = 'Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:';
                            $type_note='Die letzten Daten wurden am '. date('j.n.Y.', $dattime1).' um '.date('H:i:s', $dattime1).' empfangen.';
                    // } elseif (((int)$time1 == 0 && (int)$time2 == 1)&& ((int)$sendtimeout > (int)$timeToSubtract))  {
                    } elseif (((int)$time1 == 0 && (int)$time2 == 1)){
                    // } elseif (($time1 == 0 && $time2 == 1) && ((int)$sendtimeout > (int)$timeToSubtract)) {
                    // } elseif ($time1 === 0 && $time2 === 1) {
                            $iden = 1;
                            $type = 'Timeout gone';
                            $topic= 'Timout Hindernisbefeuerung behoben';
                            $text ='Folgendes Hindernisbefeuerungssystem überträgt seit '. date('j.n.Y.', $dattime1).' um '.date('H:i:s', $dattime1).' wieder erfolgreich Daten:';
                
                    }
            //    echo  $puser. '  '. (int)$time1. '  '. (int)$time2 . '   '. $type. '<br>';
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
                                 $subject = "Statusmeldungfrom new server";
                                 $message = "<html><head>"; 
                                 $headers = "MIME-Version: 1.0" . "\r\n";
                                 $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                 $headers .= "<meta http-equiv=\"content-language\" content=\"de\">". "\r\n";
                                 $message = "</head><body>"; 
                                 $message.= "<style> div.a {line-height: 10px;} p {text-indent: 50px;} </style>";
                                 // $message.= "<style> div.a {line-height: 10px;} p {text-indent: 50px;  text-justify:auto;} </style>";
                                 $message .= "<div class=\"a\">";
                                 $message .= "<h2><u>" . $type . "</u></h2>";
                                 $message .= "<h4>Topic:</h4>";
                                 $message .= "<p>" . $topic . "</p>";
                                 $message .= "<h4>Text:</h4>";
                                 $message .= "<p>Guten Tag, </p>";
                                 $message .= "<p>". $text ."</p>";
                                 $message .= "<p>". $ort ."</p>";
                                 $message .= "<p>". $ort2 ."</p>";
                                 $message .= "<p>". $type_note  ."</p>";
                                 $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p></br>";
                                 $message .= "<p>Bei Fragen wenden Sie sich bitte an</p>";
                                 $message .= "<p>Wilfried Richter</p>";
                                 $message .= "<p>Fon: +49 / (0)421 / 696 465-14 </p>";
                                 $message .= "<p>Email: <a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";
                                 $message .= "<p><br><br></p>";
                                 $message .= "</div>";
                                 $message .= "</body></html>";                            
                                //  $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                                 $headers .= "Bcc: farzaneh.shams@lanthan.eu". "\r\n";
                                //   mail('farzaneh.shams@lanthan.eu', $subject, $message, $headers);
        //                         // if (mail($email, $subject, $message, $headers)) {
             if (mail('farzaneh.shams@lanthan.eu', $subject, $message, $headers)) {
                                  echo 'Email has sent successfully.';
                                 } else {
                                     echo 'Email sending failed.';
                              } 
        // //                      } 
            
                        }
                      }
                    
            
        
        }
    }
}