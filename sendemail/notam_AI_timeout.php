<?PHP
// // ------------------------------------------------------------------------------------------
// //                 Sending email to detect the error and timeout 
// // ------------------------------------------------------------------------------------------
// // --------- Connect  to the server -----------------------------------
require_once('db.php');
// // --------- Running time to limit the emails.. -----------------------------------
$currentTime = time();
$timeToSubtract = (2*60);
$limittime = $currentTime - $timeToSubtract;
$puser = "357796103360717";

// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE PUSER ='$puser'");
while ($zeile = $projini->fetch_array()) {
    // $puser = $zeile['PUSER'];
    $ort = $zeile['ORT'];
    $ort2 = $zeile['ORT2'];
    $interval = intval($zeile['MSGINTERVAL']);

    for ($n = 1; $n <= 16; $n++) {
        $DxVAR[$n] = "D" . $n;
        $nvar = "DI" . $n . "EN";
        $DxEN[$n] = $zeile[$nvar];
        $nvar = "DI" . $n . "ALERT";
        $DxALERT[$n] = $zeile[$nvar];
        $nvar = "DI" . $n . "ALVAL";
        $DxALVAL[$n] = $zeile[$nvar];

    }

// --------- Get customer information the number of the obstacle lights -----------------------------------
    $number_obstacle=$mysqli -> query("SELECT * FROM Obstacle WHERE PUSER ='$puser'");
    while ($numb=$number_obstacle-> fetch_array()) {
      $obsnum=$numb['Obstacles'];
    //   echo $puser. ' : '.$obsnum. '<br>';

    // --------- Use bfccio to for the last record-----------------------------------
    $all =  $mysqli->query("SELECT * FROM test WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    while ($row1 = $all->fetch_array()) {
        $count = 0;
        $imei1 = $row1['imei'];
        $dattime1 = $row1['time'];
        $ID = $row1['ID'];
        $DX1 = $row1['Dx'];
        $Ax = $row1['Ax'];

        // --------- DX error detection-----------------------------------
        $DX1cut= substr($DX1,1, 15);
         if (strlen($DX1) > 15) {
            for ($n = 1; $n <= 16; $n++) {
                    $bfD1[$n] = (int)substr($DX1, ($n - 1), 1);
                    $bferrD1[$n] = 0;
                    if ((int)$DxEN[$n] === 1 && (int)$DxALERT[$n] === 1) {
                        $bferrD1[$n] = (int)$bfD1[$n] === (int)$DxALVAL[$n] ? 1 : 0;
                    }
            }
         } 
        $errors_w1 = array_filter($bferrD1, function ($v, $k) {
            return $v == 1;
        }, ARRAY_FILTER_USE_BOTH);
        $err_keys_w1 = array_keys($errors_w1);   
        $err1 = 0;
        if (in_array(1, $bferrD1)) {
            $err1 = 1;
        }

        // ---------AI error detection-----------------------------------
        //  AI9 and AI10  error checking 
        list($A,$B,$C,$D,$E,$F,$G,$H,$I,$J,$K,$L) =  explode(";",$Ax);
        $A9error= 0;
        // if ( (int)$I<18  ||  (int)$I>26 ){
        if ( (int)$I<23  ||  (int)$I>26 ){
            $A9error= 1;
            $AIkey1= '9';
        }

        $max_AI10error =  0.15;
        $min_AI10error =  0.05;
        $A10error= 0;
       if ( $DX1[0] == 1 & ( $J<$min_AI10error ||  $J>$max_AI10error)){
        //    echo $puser. ' : '.$DX1[0].' :  '.$J.   '<br>';
           $A10error= 1;
           $AIkey2= '10';

           echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Nigth ,AI10,'.$J. ',0.05;AI10:0.15'.'<br>';
        //    echo ' imei,'. $puser. 'DI1,'.$DX1[0].',Day,AI10,'.$J. 'should be between 0.05 < AI10 < 0.15'.   '<br>';

         }elseif ($DX1[0] == 0 ){ 
        $max_AI10error = ($obsnum* 0.05+0.1)*1.25;
        $min_AI10error = ($obsnum* 0.05+0.1)*0.75;
          if  ($J<$min_AI10error ||  $J>$max_AI10error){
            $A10error= 1;
            $AIkey2= '10';

            echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Day,AI10,'.$J. ','.$min_AI10error.';AI10:'. $max_AI10error.'<br>';
          }
        }
        // ---------timeout detection-----------------------------------
        $time1 = 0 ;
        $dattime1 = $row1['time'];
        $deltaT1 = (int)(($currentTime - $dattime1) / 60); 
        $time1 = (($deltaT1 > (int)($interval * 1))? 1 : 0);
        $ID = $row1['ID'];

        //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
            $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1 ;");
            while ($row2 =  $latest->fetch_array()) {
                $datazeit = $row2['time'];
                $imei2 = $row2['imei'];
                $ID2 = $row2['ID'];
                $DX2 = $row2['Dx'];
                $DX2cut= substr($DX2,1, 15);
    
            if (strlen($DX2) > 15) {
                  for ($n = 1; $n <= 16; $n++) {
                      $bfD2[$n] = (int)substr($DX2, ($n - 1), 1);
                      $bferrD2[$n] = 0;
                      if ((int)$DxEN[$n] === 1 && (int)$DxALERT[$n] === 1) {
                          $bferrD2[$n] = (int)$bfD2[$n] === (int)$DxALVAL[$n] ? 1 : 0;
                      }
                  }
              }
              $errors_w2 = array_filter($bferrD2, function ($v, $k1) {
                  return $v == 1;
              }, ARRAY_FILTER_USE_BOTH);
              $err_keys_w2 = array_keys($errors_w2);
              
            $err2=0;
            if (in_array(1, $bferrD2)) {
                $err2 = 1;
            }

             // ---------timeout detection-----------------------------------
            $time2 = 0 ;
            $datazeit = $row2['time'];
            $deltaT2 = (int)(($currentTime  - $datazeit) / 60);
            $time2 = (($deltaT2 > (int)($interval * 2))? 1 : 0);
            $sendtimeout =   $dattime1 -  $datazeit;


            if ($err1 == 1 || $err2  == 1){
            $error_on = "";
            $error_channel= implode(',', $err_keys_w1);
            }elseif ( $A9error == 1  ){
            $error_on = "AI ";
            $error_channel=$AIkey1;
            }elseif ($A10error == 1){
            $error_on = "AI ";
            $error_channel=  $AIkey21;
            }


                    // if (($imei1 == $imei2) &&  (!empty($errors_w1) || !empty($errors_w2)) && ($DX1cut !== $DX2cut) &&  ($limittime < $dattime1)) {
                    if (($imei1 == $imei2) &&  (($err1 !==  $err2) && ($DX1cut !== $DX2cut) &&  ($limittime < $dattime1)  ||   $A9error == 1 ||  $A10error == 1  )) {

                    $count = 1;
                    $in = 0;
                    if (($err1 == 1 && $err2 == 0) ||  ($A9error == 1) || ($A10error == 1)) {
                        $in = 1;
                        $topic = "Fehler Hindernisbefeuerung";
                        $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung : " . $error_on. $error_channel . "</p>";
                        $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                        echo 'imei1 = ' . $imei1 . 'imei2 = ' . $imei2 . 'time = ' . $dattime1 . '<br>';
                    } elseif (($err1 == 0 && $err2 == 1) ||  ($A9error == 0) || ($A10error == 0)){
                        $in = 1;
                        $topic = "Fehler Hindernisbefeuerung behoben";
                        $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>";
                        $time = "Zeitpunkt der Behebung: " . date('j.n.Y', $datazeit) . "," . date('H:i:s', $datazeit);
    
                        echo 'imei1 = ' . $imei1 . '  * imei2 = ' . $imei2 . ' * time = ' . $dattime1 . '<br>';
                    } elseif (($err1 == 0 && $err2 == 0) || ($err1 == 1 && $err2 == 1)) {
                        $in = 0;
                    }
                    if (((int)$time1 == 1 && (int)$time2 == 0)){
                        
                            $iden = 1;
                            $subject =  "Statusmeldung: " . "Janneby " . $topic;
                      
                        echo $puser. ' ,'. $deltaT1.  ' ,'. $deltaT2.  ' ,'. $time1.  ' ,'. $time2 . ' ,'.$interval. ' ,'. $limittime. ' ,'.  $sendtimeout. ' ,'. $timeToSubtract .'<br>';

                        }elseif (((int)$time1 == 1 && (int)$time2 == 1)  && ($dattime1 > $limittime) ){
                            $iden = 1;
                            $topic = 'Timout Hindernisbefeuerung';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem weist einen Fehler auf." . "</p>" ;
                            $time = "<p>"."Die letzten Daten wurden am " . date('j.n.Y', $dattime1) . " um " . date('H:i:s', $dattime1). " empfangen.</p>";
                            echo 'imei1 = '. $imei1.'imei2 = '. $imei2. 'time = '. $dattime1.'  the interval= '. $sendtimeout. '<br>';
                       
                    // } elseif (((int)$time1 == 0 && (int)$time2 == 1)&& ((int)$sendtimeout > (int)$timeToSubtract))  {
                    } elseif (($time1 == 0 && $time2 == 1)){
                    // } elseif (($time1 == 0 && $time2 == 1) && ((int)$sendtimeout > (int)$timeToSubtract)) {
                            $iden = 1;
                            $topic= 'Timout Hindernisbefeuerung behoben';
                            $text = "<p>" . "Folgendes Hindernisbefeuerungssystem überträgt seit" . date('j.n.Y.', $dattime1). " um " .date('H:i:s', $dattime1)." wieder erfolgreich Daten."."</p>";
                            echo 'imei1 = '. $imei1.'  * imei2 = '. $imei2. ' * time = '. $dattime1.'  the interval= '. $sendtimeout.'<br>';

                    }else{
                        $iden = 0;
                    }

                            // if  ($A9error == 1){
                            //     $in = 1;
                            //     $type = 'Error';
                            //     $topic = 'Fehler Hindernisbefeuerung';
                            //     $text = 'Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:';
                            //     $type_note = 'Der Fehler liegt an AI ' . $AIkey1 . ' an und ist am ' . date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) . ' aufgetreten.';
                            //     echo 'imei1 = '. $imei1.'imei2 = '. $imei2. 'time = '. $dattime1.'<br>';
                            // } elseif ($A10error == 1){
                            //     $in = 1;
                            //     $type = 'Error';
                            //     $topic = 'Fehler Hindernisbefeuerung';
                            //     $text = 'Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:';
                            //     $type_note = 'Der Fehler liegt an AI ' . $AIkey2 . ' an und ist am ' . date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) . ' aufgetreten.';
                            // }

                            if ($in === 1) {
                                $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  ' . "$puser" . ' ;');
                                    while ($rowr = $result->fetch_array()) {
                                    if (!empty($rowr['EMAIL'])) {
                                        $userID = (int)$rowr['userId'];
                                        $deviceId = $rowr['device'];
                                        $mail= 0;
                                         $subject =  "Statusmeldung: " . "Janneby " . $topic;
                                        // $subject = "Lanthan Alram Email";
                                        $html_table  = "<p><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></p>";
                                        $html_table .= "<p>Lanthan GmbH & Co. KG </p>";
                                        $html_table .= "<p>Jakobistraße 25A, 28195 Bremen</p> ";
                                        $html_table .= "<p>Telefon +49 / (0)421 / 696 465-14</p> ";
                                        $html_table .= "<p><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";
                
                                        $headers = "MIME-Version: 1.0" . "\r\n";
                                        $headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";
                                        // $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                                        // $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                                        // $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";
                
                
                                        $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
                                        $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
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
                                            $message .= "<p>Koordinaten der Station im WGS84-Format: " . $string . "</p>";
                                            $message .= "<p>Mast/ Bauwerkshöhe: ?(m); ?(ft)</p>";
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
                                        echo  $message;
                                        // $headers .= "<meta http-equiv=\"content-language\" content=\"de\">". "\r\n";
                                        // $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                                        // if ($imei1 == $imei2){
                                     
                                        // echo $message;
                                        // }else {}
                                    }
                                }

                        }
                    }
                        // if (($imei1 == $imei2) && ((!empty($errors_w1) && !empty($errors_w2)) || (empty($errors_w1) && empty($errors_w2))) && ($DX1cut == $DX2cut)) {
                        if (($imei1 == $imei2) && ($err1 !==  $err2) && ($DX1cut == $DX2cut) && ($limittime < $dattime1)) {
                            $count = 0;
                        }
                         
            } 
          }
        }
}
// echo "ok";