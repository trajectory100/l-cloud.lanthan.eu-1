<?PHP
// // --------- Connect  to the server -----------------------------------
require_once('db.php');
mysqli_set_charset($mysqli, "utf8mb4");//as I could not change the language inside the mysql

// // ------------------------------------------------------------------------------------------
// //                 Sending email to detect the error and timeout 
// // ------------------------------------------------------------------------------------------
// // --------- Running time to limit the emails -----------------------------------
$currentTime = time();
$timeToSubtract = (2*60);
$limittime = $currentTime - $timeToSubtract;
// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'");
while ($zeile = $projini->fetch_array()) {
        $puser = $zeile['PUSER'];
        $ort = $zeile['ORT'];
        $ort2 = $zeile['ORT2'];
        $interval = intval($zeile['MSGINTERVAL']);

        // --------- Use bfccio to get the last record-----------------------------------
    $all =  $mysqli->query("SELECT * FROM test WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    while ($row1 = $all->fetch_array()) {
        $count= 0;
        $time1 = 0 ;
        $dattime1 = $row1['time'];
        $deltaT1 = (int)(($currentTime - $dattime1) / 60); 
        $time1 = (($deltaT1 > (int)($interval * 1))? 1 : 0);
        $ID = $row1['ID'];


        //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
        $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1 ;");
        $yes = 0;
        while ($row2 =  $latest ->fetch_array()) {
            $time2 = 0 ;
            $datazeit = $row2['time'];
            $deltaT2 = (int)(($currentTime  - $datazeit) / 60);
            $time2 = (($deltaT2 > (int)($interval * 2))? 1 : 0);
            $sendtimeout =   $dattime1 -  $datazeit;
        //    echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'. $timeToSubtract .'<br>';


        if (!empty(stristr($ort, "Janneby"))) {
            $subject =  "Statusmeldung: " . "Janneby " . $topic;
            $hourago6 = $currentTime - (6*60*60);
            $hourago6min3 = $currentTime - (21420); //(6*60*60)-3*60
            $time_to_sendemail=  (($hourago6min3> $dattime1 &&  $dattime1> $hourago6) ? 1 : 0 );

        } else {
            $hourago6 = $currentTime - 172800;// (48*60*60)
            $hourago6min3 = $currentTime - 172620;// (48*60*60)-3*60
            $time_to_sendemail=  (($hourago6min3> $dattime1 &&  $dattime1> $hourago6) ? 1 : 0 );
            $subject = "Statusmeldung: " . $topic;
        }  

        // if ($puser== '359315076932796' ) {
        //     $datazeit = $currentTime - (172802) ;
        //     $hourago61 = $currentTime - (172620); //(6*60*60)+3*60
        //     $time2 = 1;
        //     $time1 =  0;
        //     $dattime1  = $currentTime - (1*60) ;
        //     $hourago6 = $currentTime - 172800;//(6*60*60)
        //     $time_to_sendemail=  (( $hourago61> $dattime1 &&  $dattime1> $hourago6) ? 1 : 0 );
        //     echo  ' ,'.  $time_to_sendemail.  ' ,';

        //     echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'.   $time_to_sendemail. ' ,' .date('j.n.Y.', $dattime1). " um " .date('H:i:s', $dattime1).'<br>';

        // }


                if (($imei1 == $imei2)) {//&& ($dattime1 < $limittime)
                  /// echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'. $timeToSubtract .'<br>';
              
                    $count=1;
                    $iden = 0;
                    if (((int)$time1 == 1 && (int)$time2 == 0)){
                        $iden = 0;

                            // if (!empty(stristr($ort, "Janneby"))) {
                            //     $iden = 1;
                            //     $subject =  "Statusmeldung: " . "Janneby " . $topic;
                            // } else {
                            //     $iden = 0;
                            //     $subject = "Statusmeldung: " . $topic;
                            // } 
                           echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'. $timeToSubtract .'<br>';
                         }elseif (($time1 == 1 && $time2 == 1) && ( $time_to_sendemail== 1) ){
                            $iden = 1;
                            $topic = 'Timout Hindernisbefeuerung';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:" . "</p>" ;
                            $time = "Die letzten Daten wurden am " . date('j.n.Y', $dattime1) . " um " . date('H:i:s', $dattime1). " empfangen.</p>";
                             echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'. $timeToSubtract .'<br>';
                       
                        } elseif (($time1 == 0 && $time2 == 1) && ($dattime1 > $limittime)  &&   ($hourago6 > $datazeit) ){
                            $iden = 1;
                            $topic= 'Timout Hindernisbefeuerung behoben';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem überträgt seit" . date('j.n.Y.', $dattime1). " um " .date('H:i:s', $dattime1)." wieder erfolgreich Daten."."</p>";
                            echo 'imei1 = '. $imei1.'  * imei2 = '. $imei2. ' * time = '. $dattime1.'  the interval= '. $sendtimeout.'<br>';

                    }else{
                        $iden = 0;
                    }
                if ($iden === 1) {
                $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                            bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                            LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  '."$puser".' ;');
                while ($rowr = $result->fetch_array()) {
                    if (!empty($rowr['EMAIL'])) {
                        $email = (int)$rowr['EMAIL'];
                        $userID = (int)$rowr['userId'];
                        $deviceId = $rowr['device'];
                                                        
                        /////////////>>>>>>>
                      
                        
                        // $subject = "Lanthan Alram Email";
                        $html_table  = "<p><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></p>";
                        $html_table .= "<p>Lanthan GmbH & Co. KG </p>";
                        $html_table .= "<p>Jakobistraße 25A, 28195 Bremen</p> ";
                        $html_table .= "<p>Telefon +49 / (0)421 / 696 465-14</p> ";
                        $html_table .= "<p><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";

                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";
                         $headers .= "From: l-cloud@lanthan.eu" . "\r\n" ;
                       $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                       $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                       $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";


                        $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
                        $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>';
                        $message .= "<style> div.a {line-height: 80%;  text-align: left; }  div.main_text { line-height: 1.3;} div.footer_text { line-height: 1;}
                         div.center {border: 2px solid  #ffffff; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; width: 50%;} </style>";

                        $message .= "</div>" . "<di class=\"main_text\">";
                        $message .= "<h2><b><u>" . $topic . "</u></b></h2>";
                        $message .= "<p>Guten Tag, </p>";
                        $message .= "<p>" . $text . "</p>";
                        $message .= "<p>" . $time . "</p>";
                        $message .= "<p> Adresse der Station: " . $ort . "</p>";
                        $message .= "<p  style= \"margin-left: 128px;\">" . $ort2 . "</p>";
                        if (!empty(stristr($ort, "Janneby"))) {
                            // echo $string;
                            // $height= Höhe*0,3048;
                            $message .= "<p>Koordinaten der Station im WGS84-Format: " . "WSG 84 Standort 54°38'28.6\"N 9°18'38.9\"E". "</p>";
                            $message .= "<p>Mast/ Bauwerkshöhe: Höhe ü. Grund  111 m</p>";
                            $message .=  "<p>Standort-Nr. der DFS : <b>z.B. SH-448</b></p>";
                            $message .=  "<p>NOTAM-Meldeplichtiger: Lanthan</p>";
                            $html_table2 = "<div class='formbox' style='width: 30%;border: 2px solid  transparent ; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
                            $html_table2  .= "<p><u>Wenn Sie eine NOTAM-Meldung absenden wollen:</u></p>";
                            $html_table2 .= "<p>NOTAM Office</p>";
                            $html_table2 .= "<p>Telefon: 06103/707-5555</p>";
                            $html_table2 .= "<p>Fax: 06103/707-5556</p>";
                            $html_table2 .= "<p>E-Mail: <a href=\"mailto:notam.office@dfs.de\">notam.office@dfs.de</a></p></div>";
                        } else {
                        }

                        $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p>";
                        $message .= "<p>" . $open_page . "</p>";
                        $message .= "<br><br><hr style=\"height:2px;border-width:0;color:gray;background-color: black ;width: 60%;float:left;\"><br>";
                        $message .= "</div>" . "<div class=\"footer_text \">";
                        $message .= $html_table2;
                        $message .= "<div class='formbox' style='width: 42%;border: 2px solid  transparent; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
                        $message .= "" . $html_table . "</div>";
                        $message .= "</div>" . "</div>";
                        // echo $message;
                        //mail('farzaneh.shams@lanthan.eu', $subject, $message, $headers);
                         if (mail($email, $subject, $message, $headers)) {
        // //                     //      if (mail($email, $subject, $message, $headers)) {
                                  echo 'Email has sent successfully.';
                                 } else {
                                     echo 'Email sending failed.';
                                 } 
                            } 
            
                        }
                      }
                    
            }           
            if (($imei1 !== $imei2)){
            $count=0;
            }
        }
    }
}

echo "ok";