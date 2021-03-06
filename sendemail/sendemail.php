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
$timeToSubtract = (1* 60);
$limittime = $currentTime - $timeToSubtract;
// --------- Get customer information from bfprojektini-----------------------------------
$projini = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'");
while ($zeile = $projini->fetch_array()) {
    $puser = $zeile['PUSER'];
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
        // $latest =  $mysqli->query("SELECT * FROM bfccio  WHERE imei= '$puser'  ORDER BY time DESC LIMIT 2 ;");
        $latest =  $mysqli->query("SELECT * FROM test  WHERE imei = '$puser' AND test.time < $dattime1  ORDER BY time DESC LIMIT 1 ;");
        while ($row2 =  $latest->fetch_array()) {
            $datazeit = $row2['time'];
            $imei2 = $row2['imei'];
            $ID2 = $row2['ID'];
            $DX2 = $row2['Dx'];
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


            if (($imei1 == $imei2) &&  ($err1 !==  $err2)  &&  ($limittime < $dattime1)) {
                $count = 1;
                $in = 0;
                //   echo $count . ' = ' .  $puser .' = ' .$ID. ' = ' .$imei1 . ' = '. $DX1 . ' = ' .  ' = ' . '   : '.  date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) .'   ***  ' .$ID2. ' = '. $imei2  . ' = ' . $DX2 .' = '  . '<br>';

                if ($err1 == 1 && $err2 == 0) {
                    $in = 1;
                    $topic = "Fehler Hindernisbefeuerung";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt." . "</p>" . "<p>" . "Art der Störung: " . implode(',', $err_keys_w1) . "</p>";
                    $time = "Zeitpunkt der Störung: " . date('j.n.Y', $dattime1) . ", " . date('H:i:s', $dattime1);
                    echo 'imei1 = ' . $imei1 . 'imei2 = ' . $imei2 . 'time = ' . $dattime1 . '<br>';
                } elseif ($err1 == 0 && $err2 == 1) {
                    $in = 1;
                    $topic = "Fehler Hindernisbefeuerung behoben";
                    $text = "<p>" . "das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>";
                    $time = "Zeitpunkt der Behebung: " . date('j.n.Y', $datazeit) . ", " . date('H:i:s', $datazeit);

                    echo 'imei1 = ' . $imei1 . '  * imei2 = ' . $imei2 . ' * time = ' . $dattime1 . '<br>';
                } elseif (($err1 == 0 && $err2 == 0) || ($err1 == 1 && $err2 == 1)) {
                    $in = 0;
                }


                if ($in === 1) {
                    $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                 bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                 LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  ' . "$puser" . ' ;');
                    while ($rowr = $result->fetch_array()) {
                        if (!empty($rowr['EMAIL'])) {
                            $email = $rowr['EMAIL'];
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
                            $headers .= "From: l-cloud@lanthan.eu" . "\r\n" ;
                            $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                            $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                            $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";


                            $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
                            $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
                            $message .= "<style> div.a {line-height: 80%;  text-align: left; }  div.main_text { line-height: 1.3;} div.footer_text { line-height: 1;}
                             div.center {border: 2px solid  #ffffff; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; width: 50%;} </style>";
                            // $message .= "<div class='formbox' style='border: 1px solid  black;background-color: black; padding: 5px ; margin: 2px 2px; text-align: left;' ;>";
                            // // $message .= "<div class='formbox' style='border: 1px solid  black;background-color: black; padding: 10px ; margin: 2px 2px; text-align: left;' ;>";

                            // $message .= "<img src=\"http://test.lanthan.eu/image/lanthan_status_600x150.jpg\" style=\" width: 300px; height: 75px;
                            // padding: 3px; background-clip: content-box; box-shadow: inset 0 0 0 15px white;\" alt=\"img\";>";

                            $message .= "</div>" . "<di class=\"main_text\">";
                            //$message .="<p>Bitte beachten Sie, dass unsere Überwachungs-E-Mail in geändert wurde l-cloud@lanthan.eu</p>";
                            $message .= "<h2><b><u>" . $topic . "</u></b></h2>";
                            $message .= "<p>Guten Tag, </p>";
                            $message .= "<p>" . $text . "</p>";
                            $message .= "<p>" . $time . "</p>";
                            $message .= "<p> Adresse der Station: " . $ort . "</p>";
                            $message .= "<p  style= \"margin-left: 128px;\">" . $ort2 . "</p>";
                            if (!empty(stristr($ort, "Janneby"))) {
                                // echo $string;
                                // $height= Höhe*0,3048;
                                $message .= "<p>Koordinaten der Station im WGS84-Format: " . "WSG 84 Standort 54°38'28.6\"N 9°18'38.9\"E" . "</p>";
                                $message .= "<p>Mast/ Bauwerkshöhe: Höhe ü. Grund  111 m </p>";
                                $message .=  "<p>Standort-Nr. der DFS : <b>SH-448</b></p>";
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
                            $not_sendemail = array("357796103360071", "359315076932911", "359315076849305", "359315076932945", "359315076995918",  "359315076930998" , "357796103360675");
                            if (in_array($imei1, $not_sendemail) == 1) {
                                echo $imei1;
                            } else {
                                // $mail = mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);
                                $mail = mail($email, $subject, $message, $headers);
                                if ($mail) {
                                    echo 'Email has sent successfully.';
                                } else {
                                    echo 'Email sending failed.';
                                }
                            }
                        }
                    }
                }
            }
            // if (($imei1 == $imei2) && ((!empty($errors_w1) && !empty($errors_w2)) || (empty($errors_w1) && empty($errors_w2))) && ($DX1cut == $DX2cut)) {
            if (($imei1 == $imei2) && ($err1 ==  $err2)  && ($limittime < $dattime1)) {
                $count = 0;
            }
        }
    }
}
echo 'ok';
