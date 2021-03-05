<?PHP


$db_server = 'localhost';
$db_benutzer = 'lanthan-01';
$db_passwort = '7VrBi-.GXTZCF';
$db_name = 'lanthan_test';
$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if ($mysqli->connect_error) {
    echo "Fehler bei der Verbindung: " .mysqli_connect_error();
    exit();
}

//https://cron-job.org
//olaf.schultz@lanthan.eu
//cjXobject<?PHP

//https://cron-job.org
//olaf.schultz@lanthan.eu
//cjXobject

$outdetail = true;
  if (htmlspecialchars($_REQUEST['detail']) == '0'){
    $outdetail = false;
  }else {
    echo "<html xmlns=\"c\"><head>\n\r";
    echo "<style> p.x {font-family: consola, monospace;} </style>\n\r";
    echo "</head><body><p class=\"x\"><table> \n\r";  
    echo "<tr><td>PUSER</td><td>TW</td><td>x</td><td>A TO E</td><td>Error</td><td>TO</td></tr>\n\r";
  }


// Zeitmessung
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// $idOfInterest = '359315076932481';

// -------------------------------------------------------------------------------------------------
// alle aktiven Projekte Bearbeiten 
$alert = 0;
$laufstart = microtime_float(); 
// $projekte = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1' AND PUSER = '".$idOfInterest."'");
$projekte = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1' AND DB = 'bfccio' ");
while($zeile = $projekte->fetch_array())
{
    $pid = $zeile['ID'];
    $puser = $zeile['PUSER'];
    $db = $zeile['DB'];
    $aktiv = $zeile['AKTIV'];
    $laufstep = microtime_float();
   
  // Digitale Parameter
  for ($n = 1; $n <= 16; $n++){
        $DxVAR[$n] = "D" .$n;
        $nvar = "DI" .$n ."EN";
        $DxEN[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."NAME";
        $DxNAME[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."ALERT";
        $DxALERT[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."ALVAL";
        $DxALVAL[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."L";
        $DxL[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."H";
        $DxH[$n] = $zeile[$nvar];
        $nvar = "DI" .$n ."NOE";
        $DxNOE[$n] = $zeile[$nvar];
  }
  
  // Analoge Parameter
  for ($n = 1; $n <= 16; $n++)
  {
        $ANxVAR[$n] = "A" .$n;
        $nvar = "AN" .$n ."EN";
        $ANxEN[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."NAME";
        $ANxNAME[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."ISDIGITAL";
        $ANxISDIGITAL[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."DALVAL";
        $ANxDALVAL[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."NOE";
        $ANxNOE[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."AL1EN";
        $ANxAL1EN[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."AL2EN";
        $ANxAL2EN[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."AL1LVL";
        $ANxAL1LVL[$n] = floatval($zeile[$nvar]);
        $nvar = "AN" .$n ."AL2LVL";
        $ANxAL2LVL[$n] = floatval($zeile[$nvar]);
        $nvar = "AN" .$n ."SCALE";
        $ANxSCALE[$n] = floatval($zeile[$nvar]);
        $nvar = "AN" .$n ."OKTXT";
        $ANxOKTXT[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."AL1TXT";
        $ANxAL1TXT[$n] = $zeile[$nvar];
        $nvar = "AN" .$n ."AL2TXT";
        $ANxAL2TXT[$n] = $zeile[$nvar];
  }
  
  // timeout & TWSW
  $interval = intval($zeile['MSGINTERVAL']);
  $TWswal = intval($zeile['TWSWAL']);
  $TWswch = $zeile['TWSWCH'];
  $TWswnight = intval($zeile['TWSWNIGHT']); // Sollwert für Nacht 0 = Nacht oder 1 = Nacht 
  

  // Projektweise Fehleranalyse für bfccio
  // ******************************************************
  // CCIO has 16 DI and 12 AI
  if ($db== 'bfccio')
  {
    $zeile2 = NULL;
    $datazeit = 0;
    // due to time out error it is changed to test tabel  instead of bfccio
        // $frage = "SELECT * FROM $db WHERE imei = '$puser' ORDER BY time DESC LIMIT 1;";  

    $frage = "SELECT * FROM bfccio WHERE imei = '$puser' ORDER BY time DESC LIMIT 1;";

    $projektdata = $mysqli->query($frage);
    $zeile2 = $projektdata->fetch_array();
    if ($zeile2 != null){
      $datazeit = $zeile2['time'];
      $datauser = $zeile2['imei'];
      // $bftimeout = 0;
      // $bferr = 0;
      //timeout
      $deltaT = (int)((time() - $datazeit) / 60);
      $bftimeout = ($deltaT > (int)($interval * 1));
      
      // Tag nacht abklärung
      // dodo STDN = D N -
        
      // Auswertung
      if ($aktiv == '1')
      {
        // TWSW Status erkennen
        $TWad = ''; // Twilight Switch Channel Analog/Digital
        $TWnr = ''; // Twilight Switch Channel Number
        $TWst = ''; // Twilight Status Nacht = 0
        $TWnoe = ''; // Twilightswitch Störunterdrückungsstatus
        if (strlen($TWswch) > 1)
        {
          $TWad = substr($TWswch,0,1);
          //$TWnr = (int)(substr($twswch,1,strlen($twswch)));
          $TWnr = (int)(substr($TWswch,1));
          if (($TWnr > 0 ) AND ($TWnr < 10))
          {
            if ($TWad == 'A')
            {
              $TWst = (int)$zeile2[$ANxVAR[$TWnr]] > $ANxAL1LVL[$TWnr];
            }        
            if ($TWad == 'D')
            {
              $TWst = (int)$zeile2[$DxVAR[$TWnr]];
            }
            if ($TWswnight == '1') // Normierung auf Tag = 1 Night = 0
            {
              $TWst = 1 - intval($TWst);
            }
          }
        }
      
        // 16 Digitale Kanäle
        if (strlen($zeile2['Dx']) > 15)        
        {
         
          for ($n = 1; $n <= 16; $n++)  
          { 
            $bfD[$n] = (int)substr($zeile2['Dx'],($n-1),1);
            //echo $bfD[$n];
            $bferrD[$n] = 0;
            $TWnoe = '-' .intval($TWst) .$DxNOE[$n];

            if ((int)$DxEN[$n] === 1 && (int)$DxALERT[$n] === 1)
            {
                $bferrD[$n] = (int)$bfD[$n] === (int)$DxALVAL[$n];

              // $bferrD[$n] = ((int)$DxALVAL[$n] == (int)$zeile2[$DxVAR[$n]]);
              // - DxNOE doesn't contain any D or N. Maybe wrong array used here?
              // if (($DxNOE[$n] == 'D') AND ($TWst == '1')); 
              // {
              //    $bferrD[$n] = 0; ////23/5/2020
              //   $TWnoe = 'D' .intval($TWst) .$DxNOE[$n];
              // }
              // if (($DxNOE[$n] == 'N') AND ($TWst == '0'))
              // {
              //   $bferrD[$n] = 0; ////23/5/2020
              //   $TWnoe = 'D' .intval($TWst) .$DxNOE[$n];
              // }
            }
          }
        }
        // ======>>>>>>>>>>>>>>>>>>>>>>>>>>>>       deactivated on 28/8/2020 not useful           <<<<<<<<<<<<+++++++
        // 12 Analoge Kanäle, analog oder digital gebraucht
        if (substr_count($zeile2['Ax'],';') == 11)
        {
         $bfA = explode(";", $zeile2['Ax']);   
          for ($n = 1; $n <= 12; $n++)
          {
            //echo $bfA[$n-1] ." / ";
            $bferrA1[$n] = 0;
            $bferrA2[$n] = 0;
            $TWnoe = '-' .intval($TWst) .$ANxNOE[$n];
            if ($ANxEN[$n] == "1")
            {
              // Digital 
              if ($ANxISDIGITAL[$n] =="1")
              {
                if ($ANxAL1EN[$n] == "1")
                {
                  $bferrA1[$n] = ($ANxDALVAL[$n] == (int)$bfA[$n-1]);
                }
              } else 
              {
                if ($ANxAL1EN[$n] == "1")
                {
                  $bferrA1[$n] = ((floatval($bfA[$n-1]) * $ANxSCALE[$n] ) < $ANxAL1LVL[$n]);
                }
                if ($ANxAL2EN[$n] == "1")
                {
                  $bferrA2[$n] = ((floatval($bfA[$n-1]) * $ANxSCALE[$n] ) < $ANxAL2LVL[$n]);
                }
              } 
              if (($ANxNOE[$n] == 'D') AND ($TWst == '1')) 
              {
                $bferrA1[$n] = 0;              
                $bferrA2[$n] = 0;
                $TWnoe = 'A' .intval($TWst) .$ANxNOE[$n];
              }
              if (($ANxNOE[$n] == 'N') AND ($TWst == '0'))
              {
                $bferrA1[$n] = 0;              
                $bferrA2[$n] = 0;
                $TWnoe = 'A' .intval($TWst) .$ANxNOE[$n];
              }
            }
          }
        }
        
        // Zusammenfassung der Fehler
        $bferr = in_array(1, $bferrD);// $bferrD[1] || $bferrD[2] || $bferrD[3] || $bferrD[4] || $bferrD[5] || $bferrD[6] || $bferrD[7] || $bferrD[8];
        $bferr = $bferr || $bferrA1[1] || $bferrA1[2] || $bferrA1[3] || $bferrA1[4] || $bferrA1[5] || $bferrA1[6] || $bferrA1[7] || $bferrA1[8] || $bferrA1[9];
        $bferr = $bferr || $bferrA2[1] || $bferrA2[2] || $bferrA2[3] || $bferrA2[4] || $bferrA2[5] || $bferrA2[6] || $bferrA2[7] || $bferrA2[8] || $bferrA2[9];
        
        $bfDerr = intval($bferrD[1]) .intval($bferrD[2]) .intval($bferrD[3]) .intval($bferrD[4]);
        $bfDerr = $bfDerr  .intval($bferrD[5]) .intval($bferrD[6]) .intval($bferrD[7]) .intval($bferrD[8]);
        $bfDerr = $bfDerr  .intval($bferrD[9]) .intval($bferrD[10]) .intval($bferrD[11]) .intval($bferrD[12]);
        $bfDerr = $bfDerr  .intval($bferrD[13]) .intval($bferrD[14]) .intval($bferrD[15]). intval($bferrD[16]);


        // $bfDerr = "00000000" .intval($bferrD[8]) .intval($bferrD[7]) .intval($bferrD[6]) .intval($bferrD[5]);
        // $bfDerr = $bfDerr  .intval($bferrD[4]) .intval($bferrD[3]) .intval($bferrD[2]) .intval($bferrD[1]);
        $bfA1err = "0000000"  .intval($bferrA1[9]) .intval($bferrA1[8]) .intval($bferrA1[7]) .intval($bferrA1[6]) .intval($bferrA1[5]);
        $bfA1err = $bfA1err .intval($bferrA1[4]) .intval($bferrA1[3]) .intval($bferrA1[2]) .intval($bferrA1[1]);
        $bfA2err = "0000000"  .intval($bferrA2[9]) .intval($bferrA2[8]) .intval($bferrA2[7]) .intval($bferrA2[6]) .intval($bferrA2[5]); 
        $bfA2err = $bfA2err .intval($bferrA2[4]) .intval($bferrA2[3]) .intval($bferrA2[2]) .intval($bferrA2[1]);
      
        
      } else
      {// not aktive
        $bferr = 0;
        $bfDerr = "0000000000000000";
        $bfA1err = "0000000000000000";
        $bfA2err = "0000000000000000";
        $bftimeout = 0;
        $datazeit = 0;
        $deltaT = (int)((time() - $datazeit) / 60); 
      }
    } else //not null
    {
      $bferr = 0;
      $bfDerr = "0000000000000000";
      $bfA1err = "0000000000000000";
      $bfA2err = "0000000000000000";
      $bftimeout = 0;
      $datazeit = 0;
      $deltaT = (int)((time() - $datazeit) / 60);     
    }    
  }

  // bftopass und ccio
  
  if ($outdetail == TRUE)
  {   
    echo "<tr>";
    $laufend = microtime_float();
    //echo " " .round($laufend - $laufstart, 3) ."; ";
    //echo " " .round($laufend - $laufstep, 3) ."; ";      
    echo "<td>" .$puser ."</td>";
    echo "<td>  " .$TWad .$TWnr .":" .$TWst .$TWnoe ."</td>";
    echo "<td>  " .intval($aktiv) .intval($bftimeout) .intval($bferr) ."</td>";
    echo "<td> D:" .$bferr;
    echo " A2:" .$bfA2err ."</td>";
    echo "<td> T:" .(int)$deltaT ."</td></tr>\n\r";
    
  }

  
  
    
  
  // Abspeichern
      $insert = "INSERT INTO bfprojektstatus2_new (ID, PUSER, ERROR, TIMEOUT, LASTDATA, AKTIV, DERR, A1ERR, A2ERR, TIME) VALUES";
      $insert = $insert ."(\"" .$pid ."\", \"" .$puser ."\", \"" .intval($bferr) ."\", \"" .intval($bftimeout) ."\", \"" .$datazeit ."\", \"" .$aktiv ."\", ";
      $insert = $insert ."\"" .$bferr ."\", \"" .$bfA1err ."\", \"" .$bfA2err ."\", \"" .time() ."\")";
      $insert = $insert ."ON DUPLICATE KEY UPDATE  ERROR=\"" .intval($bferr)  ."\", TIMEOUT=\"" .intval($bftimeout) ."\", LASTDATA=\"" .$datazeit ."\", AKTIV=\"" .$aktiv ."\", ";
      $insert = $insert ."DERR=\"" .$bfDerr  ."\", A1ERR=\"" .$bfA1err ."\", A2ERR=\"" .$bfA2err ."\", TIME=\"" .time() ."\"";



  //echo $insert ."<br>";
  $ergebnis = $mysqli->query($insert);
    if ($mysqli->errno > 0) {
       echo 'SQL Error ('.$mysqli->errno.'):'.$mysqli->error;
    }
    if ($outdetail == true) { //echo ""; 
    }
    
 
  
  //  $projektdata->close();
}
    // $projekte->close();

    // $mysqli->close();

echo "</table></p>OK<br>";
