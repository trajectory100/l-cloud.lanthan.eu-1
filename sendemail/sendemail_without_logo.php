<?PHP
  // // --------- Connect  to the server -----------------------------------
 require_once('db.php');
 // // ------------------------------------------------------------------------------------------
 // //                 Sending email to detect the error and timeout 
 // // ------------------------------------------------------------------------------------------
//  $mysqli->query("SET SQL_BIG_SELECTS=1"); for big tabels
function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'].'/';
    return $protocol.$domainName;
}

define( 'SITE_URL', siteURL() );
// $puser= '357796103360717';
$puser= '357796103360261';


 $currentTime = time();
 $timeToSubtract = (2*60*60);
 $limittime = $currentTime - $timeToSubtract;
 // --------- Get customer information from bfprojektini-----------------------------------
//  $get_active = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'");
 $get_active = $mysqli->query("SELECT * FROM bfprojektini WHERE PUSER = '$puser'");

 while ($zeile = $get_active->fetch_array()) {
    //  $puser = $zeile['PUSER'];
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
         $posjan =$row1['pos'];
         $string= substr($posjan, 24, -21);

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
                 

            //// --------- Use bfccio again to get the 2 previous data to compare -----------------------------------
             // $latest =  $mysqli->query("SELECT * FROM bfccio  WHERE imei= '$puser'  ORDER BY time DESC LIMIT 2 ;");
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
             echo 'imei1 = '. $imei1.'   dx1= ' . $DX1 .  '   error= '.      $err1.    '   imei2 = '. $imei2.'   dx2= ' . $DX2 . '   error= '.      $err2. '  time = '. $dattime1.'<br>';

             echo "here1";


             if (($imei1 == $imei2) &&  ($err1 !==  $err2) && ($DX1cut !== $DX2cut) &&  ($limittime < $dattime1)) {
                 $count = 1;
                 $in = 0;     
                         echo "here2";
                         echo 'imei1 = '. $imei1.'   dx1= ' . $DX1 .  '   error= '.      $err1.    '   imei2 = '. $imei2.'   dx2= ' . $DX2 . '   error= '.      $err2. '  time = '. $dattime1.'<br>';


                         if ($err1==1 && $err2==0) {
                            $in = 1;
                            $topic = "Fehler Hindernisbefeuerung";
                            $text = "<tr><td  colspan=\"2\" >". "Das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt.". "</td></tr>". "<tr><td  colspan=\"2\">"."Art der Störung: ". implode(',', $err_keys_w1). "</td></tr>";
                            //  $text1= 'Art der Störung: '. implode(',', $err_keys_w1

                        //    $html_table  = "<style> div.c {line-height: 1em;  text-align: left;} </style>";
                        //    $html_table .= "<div class=\"c\">";
                           $html_table = "<table style=\"width:100%\">";
                        //    $html_table .= "<tr><th></th><th></th></tr>";
                           $html_table .= "<tr><td><u>Wenn Sie eine NOTAM-Meldung absenden wollen:</u></td><td><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></td></tr>";
                           $html_table .= "<tr><td>NOTAM Office</td><td>Lanthan GmbH & Co. KG </td></tr>";
                           $html_table .= "<tr><td>Telefon: 06103/707-5555</td><td>Jakobistraße 25A, 28195 Bremen</td></tr> ";
                           $html_table .= "<tr><td>Fax: 06103/707-5556</td><td>Telefon +49 / (0)421 / 696 465-14</td></tr> ";
                           $html_table .= "<tr><td>E-Mail: <a href=\"mailto:notam.office@dfs.de\">notam.office@dfs.de</a></td><td><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></td></tr>";
                           $html_table .= "</table>";

                           $open_page = "Oder <a href=\"https://secais.dfs.de/pilotservice/user/login/login_edit.jsp\"target=\"_blank\">https://secais.dfs.de/pilotservice/user/login/login_edit.js</a> (Registrierung erforderlich)";
                           
                           echo 'imei1 = '. $imei1.'imei2 = '. $imei2. 'time = '. $dattime1.'<br>';

                        } elseif ($err1==0 && $err2==1) {                            
                            $in = 1;
                            $topic = "Fehler Hindernisbefeuerung behoben";
                            $text = "Das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an.";
                           
                            $html_table  = "<table style=\"width:100%\">";
                            // $html_table .= "<tr><th></th></tr>";
                            $html_table .= "<tr><td><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></td></tr>";
                            $html_table .= "<tr><td>Lanthan GmbH & Co. KG </td></tr>";
                            $html_table .= "<tr><td>Jakobistraße 25A, 28195 Bremen</td></tr>";
                            $html_table .= "<tr><td>Telefon +49 / (0)421 / 696 465-14</td></tr> ";
                            $html_table .= "<tr><td><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></td></tr>";
                            $html_table .= "</table>";

                            echo 'imei1 = '. $imei1.'  * imei2 = '. $imei2. ' * time = '. $dattime1.'<br>';

                        } elseif (($err1==0 && $err2==1) || ($err1==1 && $err2==0)) {
                            $in = 0;
                        }

                        if ($in === 1) {
                            $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                            bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                            LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  '. "$puser" .';');
                                while ($rowr = $result->fetch_array()) {
                                if (!empty($rowr['EMAIL'])) {
                                   //  $email= $rowr['EMAIL'];
                                    $userID = (int)$rowr['userId'];
                                    $deviceId = $rowr['device'];

                                    if (empty(stristr($ort2, "Janneby"))){
                                       $subject = $topic;
                                       }else { 
                                            $subject =  "Janneby ".$topic;
                                       }

                                       $headers .= "MIME-Version: 1.0" . "\r\n";
                                       $headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";

                                       $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
                                       $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
                                      
/////////////////////////////////////background with shape but shows in html and not in the outlook email
                                    //    $message .= "<body><div style=\"background-color:#7bceeb;\">
                                    //                 <table height=\"100%\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                    //                 <tr>
                                    //                  <td valign=\"top\" align=\"left\" background=\"https://i.imgur.com/YJOX1PC.png\">";
//////////////////////////////////////

                                        $message .= "<body><div style=\"background-color:#7bceeb;\">
                                                     <table height=\"100%\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                     <tr>
                                                     <td valign=\"top\" align=\"left\"  style= \"background-image: linear-gradient( #000000 0%, #04619f 74%);\">";


                                        $message .= "<style> div.a {line-height: 80%;  text-align: left; }  p {line-height: 80%;} 
                                        div.center {border: 2px solid  #ffff0f; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; } table{   border: 1px solid black;} th,td{ border: 1px solid black;  padding: 5px;   border-collapse: collapse;
                                        } box{}  </style>";

                                      $message .= "<div class=\"center\">";
                                      $message .= "<img src=\" ../image/lanthan_status_600x150.jpg\" style=\" width: 400px; height: 100px;\";>";
                                    //   $message .= "<img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAADSCAMAAABThmYtAAAAXVB\" alt=\"img\" />";
                                   
                                    $message .= "<img src=\"http://test.lanthan.eu/image/lanthan_logo_150x130.jpg\" alt=\"img\;>";

                                       $message .= "<table>";
                                       $message .= "<tr><td colspan=\"2\" ><b><u>" . $topic. "</u></b></td></tr>";
                                       $message .= "<tr><td colspan=\"2\" ><p>Guten Tag, </p></td></tr>";
                                       $message .=  $text ;
                                       $message .= "<tr><td style=\"height:5px;\">" . "Zeitpunkt der Störung: "  . date("j.n.Y", $dattime1) . " , " . date("H:i:s", $dattime1) . "</td></tr>";
                                       $message .= "<tr rowspan=\"2\"><td style=\"width: 15%;\" > Adresse der Station: </td><td>". $ort . "</td></tr>";
                                       $message .= "<tr><td style=\"width: 15%;\" ></td><td>". $ort2 . "</td></tr>";                                    
                                        $message .= "<tr><td colspan=\"2\"><p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</td></tr>";
                                        $message .= "<tr><td colspan=\"2\"><p>". $open_page ."</p></td></tr>";
                                        $message .= "</table><hr style=\"height:2px;border-width:0;color:gray;background-color:gray\">";

                                        $message .= "<br>". $html_table."";
                                        $message .= "</div> "; 
                                        
                                        $message .=  "</td></tr> </table></div>";
                                        $message .= "</body></html>";

                                           echo $message;
                                            //  $mail= mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);
                                            //    if ($mail){
                                            //         echo 'Email has sent successfully.';
                                            //     } else {
                                            //         echo 'Email sending failed.';
                                            //     }
                               }
                           }
                        }                  
                    }
                    if (($imei1 == $imei2) && ($err1 !==  $err2) && ($DX1cut == $DX2cut) && ($limittime < $dattime1)) {
                       $count = 0;
                    }
        }
    }
}
//  echo 'ok'; 


$topic = "Fehler Hindernisbefeuerung behoben";
$text = "<p>" . "Das Monitoring für die Überwachung von Flughindernisfeuer zeigt eine einwandfreie Funktion an." . "</p>" . "<p>" . "Art der Störung: " . implode(',', $err_keys_w1) . "</p>";
if (!empty(stristr($ort, "Janneby"))){
    $subject =  "Statusmeldung: " . "Janneby " . $topic;
} else {
    $subject = "Statusmeldung: " . $topic;

}

$html_table  = "<p><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></p>";
$html_table .= "<p>Lanthan GmbH & Co. KG </p>";
$html_table .= "<p>Jakobistraße 25A, 28195 Bremen</p> ";
$html_table .= "<p>Telefon +49 / (0)421 / 696 465-14</p> ";
$html_table .= "<p><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";

$open_page = "Oder <a href=\"https://secais.dfs.de/pilotservice/user/login/login_edit.jsp\"target=\"_blank\">https://secais.dfs.de/pilotservice/user/login/login_edit.js</a> (Registrierung erforderlich)";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";
$headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
// $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
// $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";


$message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
$message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';

//  $message .= "<body><div style=\"background-color:#7bceeb;\">
//               <table height=\"100%\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
//               <tr>
//               <td valign=\"top\" align=\"left\"  style= \"background-image: linear-gradient( #000000 0%, #04619f 74%);\">";//table{ border: 1px solid black;} table th,td{ border: 1px solid black;


 $message .= "<style> div.a {line-height: 80%;  text-align: left; }  div.main_text { line-height: 1.3;font-size: 10px;} div.footer_text { line-height: 1;   font-size: 11px; }
 div.center {border: 2px solid  #ffffff; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; width: 50%;} </style>";

 $message .= "<div class='formbox' style='border: 2px solid  black;background-color: black; padding: 10px ; margin: 2px 2px; text-align: left;' ;>";

// $message .= "<div class=\"center\">";
$message .= "<img src=\"http://test.lanthan.eu/image/lanthan_status_600x150.jpg\" style=\" width: 350px; height: 80px;
padding: 3px; background-clip: content-box; box-shadow: inset 0 0 0 15px white;\" alt=\"img\";>";

$message .= "</div>"."<di class=\"main_text\">"; 
$message .= "<p><b><u>" . $topic. "</u></b></p>";
$message .= "<p>Guten Tag,</p>";
$message .=  "<p>".$text ."</p>";
$message .= "<p>" . "Zeitpunkt der Störung: "  . date("j.n.Y", $dattime1) . " , " . date("H:i:s", $dattime1) . "</p>";

$message .= "<p> Adresse der Station: ". $ort . "</p>";
$message .= "<p  style= \"text-indent: 129px;\">". $ort2 . "</p>";

if (!empty(stristr($ort, "Janneby"))){
    echo $string;
    // $height= Höhe*0,3048;
    $message .= "<p>Koordinaten der Station im WGS84-Format: ". $string."</p>";
    $message .= "<p>Mast/ Bauwerkshöhe: ?(m); ?(ft)</p>";
    $message .=  "<p>Standort-Nr. der DFS : <b>z.B. SH-448</b></p>";
    $message .=  "<p>NOTAM-Meldeplichtiger: Lanthan</p>";

    $html_table2 = "<div class='formbox' style='width: 30%;border: 2px solid  transparent ; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
    $html_table2  .= "<p><u>Wenn Sie eine NOTAM-Meldung absenden wollen:</u></p>";
    $html_table2 .= "<p>NOTAM Office</p>";
    $html_table2 .= "<p>Telefon: 06103/707-5555</p>";
    $html_table2 .= "<p>Fax: 06103/707-5556</p>";
    $html_table2 .= "<p>E-Mail: <a href=\"mailto:notam.office@dfs.de\">notam.office@dfs.de</a></p></div>";

         }else { 

    }

$message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p>";
$message .= "<p>". $open_page ."</p>";
$message .= "<br><br><hr style=\"height:2px;border-width:0;color:gray;background-color: black ;width: 60%;float:left;\"><br>";
$message .= "</div>". "<div class=\"footer_text \">"; 
$message .= $html_table2;
$message .= "<div class='formbox' style='width: 36%;border: 2px solid  transparent; padding: 2px ; margin: 2px 0px; text-align: left;float:left;'>";
$message .= "". $html_table."</div>";
$message .= "</div>"."</div>";
// $message .= "</div> "; 

//end part for the background with image  transparent                                      
// $message .=  "</td></tr> </table></div>";
$message .= "</body></html>";
$mail= mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);

   echo $message;
















// $topic = " Statusmeldung: Fehler Hindernisbefeuerung";
// $text = "<tr><td  colspan=\"2\" ><p>". "Das Monitoring für die Überwachung von Flughindernisfeuer hat eine Störung angezeigt.". "</p></td></tr>". "<tr><td  colspan=\"2\">"."Art der Störung: ". implode(',', $err_keys_w1). "</td></tr>";

// $html_table .= "<table max-width=\"100%;\">";
// $html_table .= "<tr><td style=\"width: 45%;\" ><u>Wenn Sie Fragen haben wenden Sie sich bitte an den Hersteller:</u></td></tr>";
// $html_table .= "<tr><td>Lanthan GmbH & Co. KG </td></tr>";
// $html_table .= "<tr><td>Jakobistraße 25A, 28195 Bremen</td></tr> ";
// $html_table .= "<tr><td>Telefon +49 / (0)421 / 696 465-14</td></tr> ";
// $html_table .= "<tr><td><a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></td></tr>";
// $html_table .= "</table>";

// $open_page = "Oder <a href=\"https://secais.dfs.de/pilotservice/user/login/login_edit.jsp\"target=\"_blank\">https://secais.dfs.de/pilotservice/user/login/login_edit.js</a> (Registrierung erforderlich)";

// $headers .= "MIME-Version: 1.0" . "\r\n";
// $headers .= "Content-Type: text/html; charset=utf-8"  . "\r\n";

// $message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">';
// $message .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';

//  $message .= "<body><div style=\"background-color:#7bceeb;\">
//               <table height=\"100%\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
//               <tr>
//               <td valign=\"top\" align=\"left\"  style= \"background-image: linear-gradient( #000000 0%, #04619f 74%);\">";//table{ border: 1px solid black;} table th,td{ border: 1px solid black;


//  $message .= "<style> div.a {line-height: 80%;  text-align: left; }  p {line-height:  1em;} 
//  div.center {border: 2px solid  #ffff0f; background: white; margin: 5% 20%; padding:5% 2%;text-align: justify; width: 50%;}  table {border: 1px solid black;} th,td{ border: 1px solid black; padding: 5px;   border-collapse: collapse; line-height: 80%; box-sizing: border-box;} 
// tr#ROW1  {background-color:#000080; color:white;}</style>";

// $message .= "<div class=\"center\">";
// $message .= "<table max-width max-width=\"100%;\">";
// $message .= "<tr  id=\"ROW1\"><td colspan=\"2\" ><img src=\"http://test.lanthan.eu/image/lanthan_status_600x150.jpg\" style=\" width: 400px; height: 100px;
// padding: 3px; background-clip: content-box; box-shadow: inset 0 0 0 15px white;\" alt=\"img\";></td></tr>";

// $message .= "<tr><td colspan=\"2\" ><b><u>" . $topic. "</u></b></td></tr>";
// $message .= "<tr><td colspan=\"2\" >Guten Tag,</td></tr>";
// $message .=  "<p>".$text ."</p>";
// $message .= "<tr><td colspan=\"2\">" . "Zeitpunkt der Störung: "  . date("j.n.Y", $dattime1) . " , " . date("H:i:s", $dattime1) . "</td></tr>";

// $message .= "<tr rowspan=\"2\"><td style=\"width: 25%;\" > Adresse der Station: </td><td style=\"width: 75%;\">". $ort . "</td></tr>";
// $message .= "<tr><td style=\"width: 25%;\" ></td><td style=\"width: 75%;\">". $ort2 . "</td></tr>";

// $message .= "<tr><td colspan=\"2\"><p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</td></tr>";
// $message .= "<tr><td colspan=\"2\"><p>". $open_page ."</p></td></tr>";
// $message .= "</table><hr style=\"height:2px;border-width:0;color:gray;background-color:gray\">";


// $message .= "<br>". $html_table."";
// $message .= "</div> "; 

// //end part for the background with image                                        
// $message .=  "</td></tr> </table></div>";
// $message .= "</body></html>";
// //$mail= mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);

//    echo $message;