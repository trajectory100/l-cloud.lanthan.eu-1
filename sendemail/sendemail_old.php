<!-- <?PHP
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);
// ini_set('max_execution_time', 0);

 require_once('db.php');
 // // --------- Connect  to the server -----------------------------------
 // // ------------------------------------------------------------------------------------------
 // //                 Sending email to detect the error and timeout 
 // // ------------------------------------------------------------------------------------------
 // // --------- Running time to limit the emails.. -----------------------------------
//  $mysqli->query("SET SQL_BIG_SELECTS=1");

 $currentTime = time();
 $timeToSubtract = (2*60);
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
                      //// --------- delete from test tabel to make it containing less data  -----------------------------------

                     //  $date_delete = new DateTime("-4 months");
            //  $today_date = $date_delete->modify("-" . ($date_delete->format('j')-1) . " days");
            //  // $ti= $date->format('j F Y');
            //  $ti= $date_delete->format('Y-m-d h:i:s');
            //  // echo $ti .' af ';             
            //  $int_delete_date= strtotime($ti);
            //  $delete_from_test_tabel= $mysqli->query("DELETE FROM test WHERE  test.time < $int_delete_date ");


            
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


             if (($imei1 == $imei2) &&  ($err1 !==  $err2) && ($DX1cut !== $DX2cut) &&  ($limittime < $dattime1)) {
                 $count = 1;
                 $in = 0;
                //   echo $count . ' = ' .  $puser .' = ' .$ID. ' = ' .$imei1 . ' = '. $DX1 . ' = ' .  ' = ' . '   : '.  date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) .'   ***  ' .$ID2. ' = '. $imei2  . ' = ' . $DX2 .' = '  . '<br>';

                              if ($err1==1 && $err2==0) {
                                 $in = 1;
                                 // $type = 'Error   '.    $DX1cut . '    '. $DX2cut.' >  '. $errors_w . '    '. $errors_w2. '<   '. print_r($err_keys_w1). '   ' .print_r($err_keys_w2).'<br>';
                                 $topic = 'Fehler Hindernisbefeuerung';
                                 $text = 'Folgendes Hindernisbefeuerungssystem weist einen Fehler auf:';
                                 $type_note = 'Der Fehler liegt an DI ' . implode(',', $err_keys_w1) . ' an und ist am ' . date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) . ' aufgetreten.';
                                 echo 'imei1 = '. $imei1.'imei2 = '. $imei2. 'time = '. $dattime1.'<br>';
 
                             } elseif ($err1==0 && $err2==1) {                            
                                 $in = 1;
                                 $topic = 'Fehler Hindernisbefeuerung behoben';
                                 $text = 'Folgendes Hindernisbefeuerungssystem, funktioniert seit ' . date('j.n.Y.', $dattime1) . ' um ' . date('H:i:s', $dattime1) . ' wieder einwandfrei:';
                                 echo 'imei1 = '. $imei1.'  * imei2 = '. $imei2. ' * time = '. $dattime1.'<br>';
 
                             } elseif (($err1==0 && $err2==1) || ($err1==1 && $err2==0)) {
                                 $in = 0;
                                 echo $imei1. ",". $in;
                                 // - no error?
                                 // echo "No error?";
                                 // echo "<pre>";
                                 // print_r([
                                 //     'e1' => $errors_w1,
                                 //     'd1' => $bferrD1,
                                 //     'e2' => $errors_w2,
                                 //     'd2' => $bferrD2,
                                 //     'imei' => [
                                 //         $row1['imei'],
                                 //         $row2['imei'],
                                 //     ],
                                 // ]);
 
                                 // var_dump($bferrD1);
                             }

 
                             if ($in === 1) {
                                 $result = $mysqli->query('SELECT bfuser.ID as userId, bfuser.EMAIL, bfprojektini.ID as device, bfprojektini.PUSER FROM
                                 bfprojektini LEFT JOIN bfuserhasprojekt ON (bfprojektini.ID = bfuserhasprojekt.projektiniID)
                                 LEFT JOIN bfuser ON (bfuser.ID = bfuserhasprojekt.userID) WHERE  bfprojektini.PUSER =  ' . "$puser" . ' ;');
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

                                         $message = "<html><body>";
                                         $message .= "<style> div.a {line-height: 10px;  text-align: justify;} p {text-indent: 50px;} </style>";
                                         // $message.= "<style> div.a {line-height: 10px;} p {text-indent: 50px;  text-justify:auto;} </style>";
                                         $message .= "<div class=\"a\">";
                                         $message .= "<h2><u>" . $topic. "</u></h2>";
                                        //  $message .= "<h4>Topic:</h4>";
                                        //  $message .= "<p>" . $topic . "</p>";
                                        //  $message .= "<h4>Text:</h4>";
                                         $message .= "<p>Guten Tag, </p>";
                                         $message .= "<p>" . $text . "</p>";
                                         $message .= "<p>" . $ort . "</p>";
                                         $message .= "<p>" . $ort2 . "</p>";
                                         $message .= "<p>" . $type_note  . "</p>";
                                         $message .= "<p> Das System ist unter <a href=\"http://test.lanthan.eu/view/viewstd.php?WW=1&PUSER=$puser&PID=$deviceId&TIME=$dattime1\"target=\"_blank\"><em><b>Link</b></em></a> einsehbar.</p></br>";
                                         $message .= "<p>Bei Fragen wenden Sie sich bitte an</p>";
                                         $message .= "<p>Wilfried Richter</p>";
                                         $message .= "<p>Fon: +49 / (0)421 / 696 465-14 </p>";
                                         $message .= "<p>Email: <a href=\"mailto:vertrieb@lanthan.eu\">vertrieb@lanthan.eu</a></p>";
                                         $message .= "<p><br><br></p>";
                                         $message .= "</div>";
                                         $message .= "</body></html>";
                                         $headers = "MIME-Version: 1.0" . "\r\n";
                                         // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                         $headers .= "Content-Type: text/html; charset=ISO-8859-1" . "\r\n";
                                         // $headers .= "<meta http-equiv=\"content-language\" content=\"de\">". "\r\n";
                                         $headers .= "Bcc: farzaneh.shams@lanthan.eu" . "\r\n";
                                        //  $headers .= "Bcc: wilfried.richter@lanthan.eu" . "\r\n";
                                        //  $headers .= "Bcc: jonny.hildebrand@lanthan.eu" . "\r\n";

                                         $not_sendemail= array("357796103360071", "357796103360634", "359315076932911", "359315076849305", "359315076932945", "359315076995918");

                                         if  (in_array($imei1,$not_sendemail) == 1){

                                         }else {
                                        //     $mail=(object) mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);
                                        //  }
                                        $mail= mail("farzaneh.shams@lanthan.eu", $subject, $message, $headers);

                                        //  $mail= mail($email, $subject, $message, $headers);
                                        //  // if (mail('farzaneh.shams@lanthan.eu', $subject, $message, $headers)) {
                                            if ($mail){
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
                         // if (($imei1 == $imei2) && ((!empty($errors_w1) && !empty($errors_w2)) || (empty($errors_w1) && empty($errors_w2))) && ($DX1cut == $DX2cut)) {
                         if (($imei1 == $imei2) && ($err1 !==  $err2) && ($DX1cut == $DX2cut) && ($limittime < $dattime1)) {
                             $count = 0;
                         }
             }



         }
 }
 echo 'ok'; 