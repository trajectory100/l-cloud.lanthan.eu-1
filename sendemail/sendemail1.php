<?PHP
error_reporting(E_ALL);
require_once('db.php');
// // --------- Connect  to the server -----------------------------------
// // ------------------------------------------------------------------------------------------
// //                 Sending email to detect the error and timeout 
// // ------------------------------------------------------------------------------------------
// // --------- Running time to limit the emails.. -----------------------------------
//  $mysqli->query("SET SQL_BIG_SELECTS=1");
mysqli_set_charset($mysqli, "utf8mb4");//as I could not change the language inside the mysql

$currentTime = time();
$timeToSubtract = (1 * 60);
$limittime = $currentTime - $timeToSubtract;
// $puser = "359315076932978";
// --------- Get customer information from bfprojektini-----------------------------------
 $projini = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'");
while ($zeile = $projini->fetch_array()) {
   // $puser = $zeile['PUSER'];
    $ort = $zeile['ORT'];
    $ort2 = $zeile['ORT2'];
    for ($n = 1; $n <= 16; $n++) {
        $DxVAR[$n] = "D" . $n;
        $nvar = "DI" . $n . "EN";
        $DxEN[$n] = $zeile[$nvar];
        $nvar = "DI" . $n . "ALERT";
        $DxALERT[$n] = $zeile[$nvar];
        $nvar = "DI" . $n . "ALVAL";
        $DxALVAL[$n] = $zeile[$nvar];
    }
    // --------- Use bfccio to for the last record-----------------------------------
    // $all =  $mysqli->query("SELECT * FROM bfccio WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    $all =  $mysqli->query("SELECT * FROM test WHERE imei= '$puser' ORDER BY time DESC LIMIT 1 ;");
    while ($row1 = $all->fetch_array()) {
        $count = 0;
        $imei1 = $row1['imei'];
        $dattime1 = $row1['time'];
        $ID = $row1['ID'];
        $DX1 = $row1['Dx'];
        $posjan = $row1['pos'];
        $string = substr($posjan, 24, -21);

        $DX1cut = substr($DX1, 1, 15);
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

        //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
        // $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND ID < $ID ORDER BY ID DESC LIMIT 1 ;");
        $latest =  $mysqli->query("SELECT test.* , Obstacle.Obstacles FROM test LEFT JOIN Obstacle ON (Obstacle.PUSER = test.imei) WHERE imei = '$puser' AND test.ID < $ID ORDER BY test.ID DESC LIMIT 1;");

        while ($row2 =  $latest->fetch_array()) {
            $datazeit = $row2['time'];
            $imei2 = $row2['imei'];
            $ID2 = $row2['ID'];
            $DX2 = $row2['Dx'];
            $Ax = $row2['Ax'];
            $obsnum=$row2['Obstacles'];
            $DX2cut = substr($DX2, 1, 15);

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

            $err2 = 0;
            if (in_array(1, $bferrD2)) {
                $err2 = 1;
            }

        // ---------AI error detection-----------------------------------
        //  AI9 and AI10  error checking 
        list($A,$B,$C,$D,$E,$F,$G,$H,$I,$J,$K,$L) =  explode(";",$Ax);
        $A9error= 0;
        if ( floatval($I) < 23.5  ||  floatval($I) > 26 ){
            $A9error= 1;
            $AIkey1= '9';
            $error_on = "AI ";
            $error_channel=$AIkey1;
           $what_to_do = "Die Batteriespannung ist niedriger als sie sein sollte – es besteht Gefahr, dass das System bald ausfällt!";
        }
        
        $max_AI10error =  0.15;
        $min_AI10error =  0.05;
        $A10error= 0;
       if ( $DX1[0] == 1 & (  floatval($J) < $min_AI10error ||   floatval($J) > $max_AI10error)){
           $A10error= 1;
           $AIkey2= '10';
           $error_on = "AI ";
           $error_channel=  $AIkey2;
         }elseif ($DX1[0] == 0 ){ 
        $max_AI10error = ($obsnum* 0.05+0.1)*1.25;
        $min_AI10error = ($obsnum* 0.05+0.1)*0.75;
          if  ( floatval($J) <$min_AI10error ||   floatval($J) > $max_AI10error){
            $A10error= 1;
            $AIkey2= '10';
            $error_on = "AI ";
            $error_channel=  $AIkey2;
          }
        }
        $bfAnalogerr =($A9error   ||    $A10error) ? 1: 0;


//  echo $count . ' = ' .  $puser .' = ' .$err1. ' = ' .$err2 . ' = '. $A9error . ' = ' . $A10error . '<br>'; &&  ($limittime < $dattime1)


            if (($imei1 == $imei2) && (($err1 !==  $err2) ||  $bfAnalogerr == 1 ) &&  ($limittime < $dattime1)) {
                $count = 1;
                $in = 0;
                //   echo $count . ' = ' .  $puser .' = ' .$ID. ' = ' .$imei1 . ' = '. $DX1 . ' = ' .  ' = ' . '   : '.  date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) .'   ***  ' .$ID2. ' = '. $imei2  . ' = ' . $DX2 .' = '  . '<br>';

                if ($err1 == 1 && $err2 == 0) {
                    $in = 1;
                    $topic = "Fehler Hindernisbefeuerung";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung: " . implode(',', $err_keys_w1) . "</p>";
                    $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                    echo 'imei1 = ' . $imei1 . 'imei2 = ' . $imei2 . 'time = ' . $dattime1 . '<br>';
                } elseif ($err1 == 0 && $err2 == 1) {
                    $in = 1;
                    $topic = "Fehler Hindernisbefeuerung behoben";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>";
                    $time = "Zeitpunkt der Behebung: " . date('j.n.Y', $datazeit) . "," . date('H:i:s', $datazeit);

                    echo 'imei1 = ' . $imei1 . '  * imei2 = ' . $imei2 . ' * time = ' . $dattime1 . '<br>';
                } elseif (($err1 == 0 && $err2 == 0) || ($err1 == 1 && $err2 == 1)) {
                    $in = 0;
                }

                if (($A9error == 1) && ($A10error == 1)){
                    $in = 1;
                    $topic = "Fehler Hindernisbefeuerung";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung : " . $error_on. " ".$AIkey1. ", ".$AIkey2 . "</p>"."<p>". $what_to_do. "</p>" ;
                    $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                    echo 'imei1 = ' . $imei1 . 'A9error = ' . $A9error . 'A10error = ' . $A10error . '<br>';

                } elseif (($A9error == 1) || ($A10error == 1)) {
                        $in = 1;
                        $topic = "Fehler Hindernisbefeuerung";
                        $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung : " . $error_on. $error_channel . "</p>";
                        $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                        echo 'imei1 = ' . $imei1 . 'A9error = ' . $A9error . 'A10error = ' . $A10error . '<br>';

                } elseif (($A9error == 0) || ($A10error == 0)){
                    $in = 1;
                    if ($A9error == 0){ $what_to_do = "Batteriespannung ist wieder im Soll-Bereich"; }
                    $topic = "Fehler Hindernisbefeuerung behoben";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>"."<p>". $what_to_do. "</p>";
                    $time = "Zeitpunkt der Behebung: " . date('j.n.Y', $dattime1) . "," . date('H:i:s', $dattime1);
                    echo 'imei1 = ' . $imei1 . 'A9error = ' . $A9error . 'A10error = ' . $A10error . '<br>';
                } else {
                    $in = 0;
                }



                if ($in === 1) {
                    $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                 bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                 LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  ' . "$puser" . ' ;');
                    while ($rowr = $result->fetch_array()) {
                        if (!empty($rowr['EMAIL'])) {
                           //// $email = $rowr['EMAIL'];
                            $userID = (int)$rowr['userId'];
                            $deviceId = $rowr['device'];

                            if (!empty(stristr($ort, "Janneby"))) {
                                $subject =  "Statusmeldung: " . "Janneby " . $topic;
                            } else {
                                $subject = "Statusmeldung: " . $topic;
                            }


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
                            $not_sendemail = array("357796103360071", "357796103360634", "359315076932911", "359315076849305", "359315076932945", "359315076995918", "359315076927846", "359315076932788", "359315076930998" , "357796103360675");
                            if (in_array($imei1, $not_sendemail) == 1) {
                                echo $imei1;
                            } else {
                                $mail = mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);
                                // $mail = mail($email, $subject, $message, $headers);
                                if ($mail) {
                                    echo 'Email has sent successfully.';
                                } else {
                                    echo 'Email sending failed.';
                                }
                                // }else {}
                            }
                        }
                    }
                }
            }
            // if (($imei1 == $imei2) && ((!empty($errors_w1) && !empty($errors_w2)) || (empty($errors_w1) && empty($errors_w2))) && ($DX1cut == $DX2cut)) { && ($limittime < $dattime1)
            if (($imei1 == $imei2) && ( ($err1 ==  $err2) ||  $bfAnalogerr == 1 ) && ($limittime < $dattime1)) {
                $count = 0;
            }
        }
    }
}
echo 'ok';
