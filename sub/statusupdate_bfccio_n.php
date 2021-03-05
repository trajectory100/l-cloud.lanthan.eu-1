<?PHP
//make the execution time to be limitless 
//  ini_set('max_execution_time', 0);

//  include("../sub/header.php"); 
//  include("../sub/base.php"); 

$db_server = 'rdbms.strato.de';
$db_benutzer = 'U969575';
$db_passwort = 'MySqL89dB3';
$db_name = 'DB969575';
$cr = '\r\n';

$outdetail = TRUE;
if (htmlspecialchars($_REQUEST['detail']) == '0')
{
  $outdetail = false;
} 
else {
  echo "<html xmlns=\"c\"><head>\n\r";
  echo "<style> p.x {font-family: consola, monospace;} </style>\n\r";
  echo "</head><body><p class=\"x\"><table> \n\r";  
  echo "<tr><td>PUSER</td><td>TW</td><td>x</td><td>A TO E</td><td>Error</td><td>TO</td></tr>\n\r";
 }

$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if($mysqli->connect_error)
{
	echo "Fehler bei der Verbindung: " .mysqli_connect_error();
	exit();
}

// Zeitmessung
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// -------------------------------------------------------------------------------------------------
// alle aktiven Projekte Bearbeiten 
$alert = 0;
$laufstart = microtime_float(); 
$projekte = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1' AND DB = 'bfccio' ");
while($zeile = $projekte->fetch_array())
{
  $pid = $zeile['ID'];
	$puser = $zeile['PUSER'];
	$db = $zeile['DB'];
	$aktiv = $zeile['AKTIV'];
  $laufstep = microtime_float(); 
   
  // Digitale Parameter
  for ($n = 1; $n <= 16; $n++)
  {
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

    $frage = "SELECT test.* , Obstacle.Obstacles FROM test LEFT JOIN Obstacle ON (Obstacle.PUSER = test.imei) WHERE imei = '$puser' ORDER BY time DESC LIMIT 1;";  

    $projektdata = $mysqli->query($frage);
    $zeile2 = $projektdata->fetch_array();
    if ($zeile2 != NULL)
    {
      $datazeit = $zeile2['time'];
      $datauser = $zeile2['imei'];
      $Ax = $zeile2['Ax'];
      $obsnum=$zeile2['Obstacles'];

      $deltaT = (int)((time() - $datazeit) / 60);
      $bftimeout = ($deltaT > (int)($interval * 1));
      

      if ($aktiv == '1')
      {
        
        // 16 Digitale Kanäle
        if (strlen($zeile2['Dx']) > 15)        
        {
         
          for ($n = 1; $n <= 16; $n++)  
          { 
            $bfD[$n] = (int)substr($zeile2['Dx'],($n-1),1);
            $bferrD[$n] = 0;
            if ((int)$DxEN[$n] === 1 && (int)$DxALERT[$n] === 1)
            {
                $bferrD[$n] = (int)$bfD[$n] === (int)$DxALVAL[$n];
            }
          }
        }

         // ---------AI error detection-----------------------------------
        //  AI9 and AI10  error checking 
        list($A,$B,$C,$D,$E,$F,$G,$H,$I,$J,$K,$L) =  explode(";",$Ax);
        $A9error= 0;
        // if ( (int)$I<18  ||  (int)$I>26 ){
        if ( floatval($I) < 23.5  ||  (int)$I>26 ){
            $A9error= 1;
            $AIkey1= '9';
            $error_on = "AI ";
            $error_channel=$AIkey1;
        }
        
        $max_AI10error =  0.15;
        $min_AI10error =  0.05;
        $A10error= 0;
       if ( $DX1[0] == 1 & ( $J<$min_AI10error ||  $J>$max_AI10error)){
        //    echo $puser. ' : '.$DX1[0].' :  '.$J.   '<br>';
           $A10error= 1;
           $AIkey2= '10';
           $error_on = "AI ";
           $error_channel=  $AIkey2;
           echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Nigth ,AI10,'.$J. ',0.05;AI10:0.15'.'<br>';
        //    echo ' imei,'. $puser. 'DI1,'.$DX1[0].',Day,AI10,'.$J. 'should be between 0.05 < AI10 < 0.15'.   '<br>';
         }elseif ($DX1[0] == 0 ){ 
        $max_AI10error = ($obsnum* 0.05+0.1)*1.25;
        $min_AI10error = ($obsnum* 0.05+0.1)*0.75;
          if  ($J<$min_AI10error ||  $J>$max_AI10error){
            $A10error= 1;
            $AIkey2= '10';
            $error_on = "AI ";
            $error_channel=  $AIkey2;
            echo ' imei,'. $puser.','. $obsnum.',DI1,'.$DX1[0].',Day,AI10,'.$J. ','.$min_AI10error.';AI10:'. $max_AI10error.'<br>';
          }
        }

       
        
        // Zusammenfassung der Fehler
        $bferr = in_array(1, $bferrD);// $bferrD[1] || $bferrD[2] || $bferrD[3] || $bferrD[4] || $bferrD[5] || $bferrD[6] || $bferrD[7] || $bferrD[8];
        $bfDerr = intval($bferrD[1]) .intval($bferrD[2]) .intval($bferrD[3]) .intval($bferrD[4]);
        $bfDerr = $bfDerr  .intval($bferrD[5]) .intval($bferrD[6]) .intval($bferrD[7]) .intval($bferrD[8]);
        $bfDerr = $bfDerr  .intval($bferrD[9]) .intval($bferrD[10]) .intval($bferrD[11]) .intval($bferrD[12]);
        $bfDerr = $bfDerr  .intval($bferrD[13]) .intval($bferrD[14]) .intval($bferrD[15]). intval($bferrD[16]);
        $bfA1err = "00000000"  . $A9error . $A10error."00";
        $bfA2err = "000000000000"; 
        $bfAnalogerr =($A9error   ||    $A10error) ? 1: 0;
        $AI_DI_error =  ($bfAnalogerr ||    $bferr) ? 1: 0 ;
        
      } else
      {// not aktive
        $AI_DI_error = 0;
        $bfDerr = "0000000000000000";
        $bfA1err = "0000000000000000";
        $bfA2err = "0000000000000000";
        $bftimeout = 0;
        $datazeit = 0;
        $deltaT = (int)((time() - $datazeit) / 60); 
      }
    } else //not null
    {
      $AI_DI_error = 0;
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
    // echo "<td>  " .$TWad .$TWnr .":" .$TWst .$TWnoe ."</td>";
    echo "<td>  " .intval($aktiv) .intval($bftimeout) .intval($AI_DI_error) ."</td>";  
    echo "<td> D:" .$bfDerr;  
    echo " A1:" .$bfA1err;  
    echo " A2:" .$bfA2err ."</td>";  
    echo "<td> T:" .(int)$deltaT ."</td></tr>\n\r";   
    
  }

  
  
    
  
  // Abspeichern
//   $insert = "INSERT INTO bfprojektstatus2_new (ID, PUSER, ERROR, TIMEOUT, LASTDATA, AKTIV, DERR, A1ERR, A2ERR, TIME) VALUES";
//   $insert = $insert ."(\"" .$pid ."\", \"" .$puser ."\", \"" .intval($AI_DI_error) ."\", \"" .intval($bftimeout) ."\", \"" .$datazeit ."\", \"" .$aktiv ."\", ";
//   $insert = $insert ."\"" .$bferr ."\", \"" .$bfA1err ."\", \"" .$bfA2err ."\", \"" .time() ."\")";
//   $insert = $insert ."ON DUPLICATE KEY UPDATE  ERROR=\"" .intval($AI_DI_error)  ."\", TIMEOUT=\"" .intval($bftimeout) ."\", LASTDATA=\"" .$datazeit ."\", AKTIV=\"" .$aktiv ."\", ";
//   $insert = $insert ."DERR=\"" .$bfDerr  ."\", A1ERR=\"" .$bfA1err ."\", A2ERR=\"" .$bfA2err ."\", TIME=\"" .time() ."\"";



  //echo $insert ."<br>";
  $ergebnis = $mysqli->query($insert);
  if ($mysqli->errno > 0) {
    echo 'SQL Error ('.$mysqli->errno.'):'.$mysqli->error;
  }
  if ($outdetail == TRUE){   
      //echo ""; 
  }
  
  //  $projektdata->close();
}
    // $projekte->close();

    // $mysqli->close();

echo "</table></p>OK<br>";

?>