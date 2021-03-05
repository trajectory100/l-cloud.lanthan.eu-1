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
//$puser = "357796103360352";

// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE PUSER ='$puser'");
while ($zeile = $projini->fetch_array()) {
    // $puser = $zeile['PUSER'];
    $ort = $zeile['ORT'];
    $ort2 = $zeile['ORT2'];


// --------- Get customer information the number of the obstacle lights -----------------------------------
    // $number_obstacle=$mysqli -> query("SELECT * FROM Obstacle WHERE PUSER ='$puser'");
    // while ($numb=$number_obstacle-> fetch_array()) {
    //   $obsnum=$numb['Obstacles'];
    //   echo $puser. ' : '.$obsnum. '<br>';

    // --------- Use bfccio to for the last record-----------------------------------
    //$all =  $mysqli->query("SELECT * FROM test WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    $all =  $mysqli->query("SELECT test.* , Obstacle.Obstacles FROM test LEFT JOIN Obstacle ON (Obstacle.PUSER = test.imei) WHERE imei = '$puser'  ORDER BY test.ID DESC LIMIT 1;");
    while ($row1 = $all->fetch_array()) {
        $count = 0;
        $imei1 = $row1['imei'];
        $dattime1 = $row1['time'];
        $ID = $row1['ID'];
        $DX1 = $row1['Dx'];
        $Ax1 = $row1['Ax'];
        $obsnum=$row1['Obstacles'];

        // ---------AI error detection-----------------------------------
        //  AI9 and AI10  error checking 
        list($A,$B,$C,$D,$E,$F,$G,$H,$I1,$J1,$K,$L) =  explode(";",$Ax1);
        $A9error1= 0;
        // if ( (int)$I<18  ||  (int)$I>26 ){
     if ( floatval($I1) < 23.5  ||  floatval($I1) > 29 ){
                $A9error1= 1;
            $AIkey1= '9';
            $error_on = "AI ";
            $error_channel=$AIkey1;
        }
        
        // $max_AI10error =  0.15;
        // $min_AI10error =  0.05;

        // $A10error= 0;
        // if ( $DX1[0] == 1 & (  floatval($J) < $min_AI10error ||   floatval($J) > $max_AI10error)){
        //     //    echo $puser. ' : '.$DX1[0].' :  '.$J.   '<br>';
        //    $A10error= 1;
        //    $AIkey2= '10';
        //    $error_on = "AI ";
        //    $error_channel=  $AIkey2;

        //    echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Nigth ,AI10,'.$J. ',0.05;AI10:0.15'.'<br>';
        // //    echo ' imei,'. $puser. 'DI1,'.$DX1[0].',Day,AI10,'.$J. 'should be between 0.05 < AI10 < 0.15'.   '<br>';

        //  }elseif ($DX1[0] == 0 ){ 
        // $max_AI10error = ($obsnum* 0.05+0.1)*1.25;
        // $min_AI10error = ($obsnum* 0.05+0.1)*0.75;
        // if  ( floatval($J) <$min_AI10error ||   floatval($J) > $max_AI10error){
        //     $A10error= 1;
        //     $AIkey2= '10';
        //     $error_on = "AI ";
        //     $error_channel=  $AIkey2;

        //     echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Day,AI10,'.$J. ','.$min_AI10error.';AI10:'. $max_AI10error.'<br>';
        //   }
        // }

        $all2 =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1;");
        while ($row2 = $all2->fetch_array()) {
            $dattime2 = $row2['time'];
            $Ax2 = $row2['Ax'];
    
            // ---------AI error detection-----------------------------------
            //  AI9 and AI10  error checking 
            list($A,$B,$C,$D,$E,$F,$G,$H,$I2,$J2,$K,$L) =  explode(";",$Ax2);
            $A9error2= 0;
         if ( floatval($I2) < 23.5  ||  floatval($I2) > 29 ){
                    $A9error2= 1;
            }

                  if( ($limittime < $dattime1) && ( $A9error1 !=  $A9error2) ) {                  
                    $count = 1;
                    $in = 0;
                    if (($A9error1 == 1) && ($A9error2 == 0)){
                        $in = 1;
                        $topic = "Fehler Hindernisbefeuerung";
                        $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung : AI 9". "</p>".  "<p>"."Die Batteriespannung ist niedriger als sie sein sollte – es besteht Gefahr, dass das System bald ausfällt!". "</p>";
                        $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                        echo 'imei1 = ' . $imei1 . 'imei2 = ' . $imei2 . 'time = ' . $dattime1 . '<br>';

                    // }
                    //  elseif (  ($A9error == 1) || ($A10error == 1)) {
                    //         $in = 1;
                    //         $topic = "Fehler Hindernisbefeuerung";
                    //         $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung : " . $error_on. $error_channel . "</p>".  "<p>"."Die Batteriespannung ist niedriger als sie sein sollte – es besteht Gefahr, dass das System bald ausfällt!". "</p>";
                    //         $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                    //         echo 'imei1 = ' . $imei1 . 'imei2 = ' . $imei2 . 'time = ' . $dattime1 . '<br>';

                    } elseif (($A9error1 == 0) || ($A9error2 == 1)){
                        $in = 1;
                        $topic = "Fehler Hindernisbefeuerung behoben";
                        $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>". "<p>". "Batteriespannung ist wieder im Soll-Bereich"."</p>";
                        $time = "Zeitpunkt der Behebung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
    
                        echo 'imei1 = ' . $imei1 . '  * imei2 = ' . $imei2 . ' * time = ' . $dattime1 . '<br>';
                    } elseif  (($A9error1 == 0) && ($A9error2 == 0) || ($A9error1 == 1) && ($A9error2 == 1) ) {
                        $in = 0;
                    }
                   
                            if ($in == 1) {
                                $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  ' . "$puser" . ' ;');
                                    while ($rowr = $result->fetch_array()) {
                                    if (!empty($rowr['EMAIL'])) {
                                       $email = (int)$rowr['EMAIL'];
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
                                        $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                                        $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                                        $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";
                
                
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
                                            // echo $string;
                                            // $height= Höhe*0,3048;
                                            $message .= "<p>Koordinaten der Station im WGS84-Format: " . "WSG 84 Standort 54°38'28.6\"N 9°18'38.9\"E" . "</p>";
                                            $message .= "<p>Mast/ Bauwerkshöhe: Höhe ü. Grund  111 m</p>";
                                            $message .=  "<p>Standort-Nr. der DFS : <b>z.B. SH-448</b></p>";
                                            $message .=  "<p>NOTAM-Meldeplichtiger: Lanthan</p>";
                                            $html_table2 = "<div class='formbox' style='width: 30%;border: 2px solid  transparent ; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
                                            $html_table2  .= "<p><u>Wenn Sie eine NOTAM-Meldung absenden wollen:</u></p>";
                                            $html_table2 .= "<p>NOTAM Office</p>";
                                            $html_table2 .= "<p>Telefon: 06103/707-5555</p>";
                                            $html_table2 .= "<p>Fax: 06103/707-5556</p>";
                                            $html_table2 .= "<p>E-Mail: <a href=\"mailto:notam.office@dfs.de\">notam.office@dfs.de</a></p></div>";
                                       
                                        $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p>";
                                        $message .= "<p>" . $open_page . "</p>";
                                        $message .= "<br><br><hr style=\"height:2px;border-width:0;color:gray;background-color: black ;width: 60%;float:left;\"><br>";
                                        $message .= "</div>" . "<div class=\"footer_text \">";
                                        $message .= $html_table2;
                                        $message .= "<div class='formbox' style='width: 42%;border: 2px solid  transparent; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
                                        $message .= "" . $html_table . "</div>";
                                        $message .= "</div>" . "</div>";
                                         
                                        if (mail($email, $subject, $message, $headers)) {
                                              echo 'Email has sent successfully.';
                                       } else {
                                              echo 'Email sending failed.';
                                     }
                                    }
                                }

                        } 
                    }elseif (($limittime > $dattime1) && ( $A9error1 =  $A9error2)) {   
                            $count = 0;
                    }
                       
                        
        
         }
    }
}
echo "ok";