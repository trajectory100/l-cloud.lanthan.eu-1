<?PHP
  //header ("Content-type: image/png");
  //ini_set('display_errors', '1');
 

 
// ------------------------------------------------------------------------------------------
// Datenbankverbindung
// ------------------------------------------------------------------------------------------

//require_once "../sub/base.php";

$db_server = 'rdbms.strato.de';
$db_benutzer = 'U969575';
$db_passwort = 'MySqL89dB3';
$db_name = 'DB969575';
$db_table = 'bftopass';
$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if($mysqli->connect_error){
  echo "Fehler bei der Verbindung: " .mysqli_connect_error();
  exit();
}
 

// ------------------------------------------------------------------------------------------
// Datenübergabe
// ------------------------------------------------------------------------------------------
$tjob=  intval(htmlspecialchars($_REQUEST['JOB'])); 
$tpuser =  htmlspecialchars($_REQUEST['PUSER']); 

if (isset($_GET["DATE"])){
  $dpdate = htmlspecialchars($_REQUEST['DATE']);     // datum vom Datepicker
  $tstamp1 = strtotime($dpdate);
  //echo $dpdate ."  " .$tstamp ."  " .date('d.m.Y',$tstamp);
}

// ------------------------------------------------------------------------------------------
// Zeitraum Anfang und Ende
// ------------------------------------------------------------------------------------------
if ($tstamp1 < 946684800) {// vor dem 1.1.2000
  $tstamp1 = time();
}
$tstamp2 = $tstamp1 + 604799;


// -------------------------------------------------------
// Dx Status und Name
// -------------------------------------------------------

// -------------------------------------------------------
// Projektdaten / Einstellungen Laden

// -------------------------------------------------------

if (1){
  $frage = "SELECT * FROM bfprojektini WHERE PUSER = '$tpuser';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
    $spuser = $zeile['PUSER'];
    $sort = $zeile['ORT'];
    $sland = $zeile['LAND'];
    $db_table = $zeile['DB'];
    $swgs84b = floatval($zeile['WGS84B']);
    $swgs84l = floatval($zeile['WGS84L']);

    for ($n = 1; $n < 21; $n++){
      $DxVAR[$n] = "D" .$n;
      $nvar = "DI" .$n ."NAME";
      $DxNAME[$n] = $zeile[$nvar];     
      $nvar = "DI" .$n ."EN";
      $DxEN[$n] = $zeile[$nvar];     
      $nvar = "DI" .$n ."L";
      $DxL[$n] = $zeile[$nvar];
      $nvar = "DI" .$n ."H";
      $DxH[$n] = $zeile[$nvar];
      $nvar = "DI" .$n ."ALERT";
      $DxALERT[$n] = $zeile[$nvar];      
      $nvar = "DI" .$n ."ALVAL";
      $DxALVAL[$n] = $zeile[$nvar];      
      $nvar = "DI" .$n ."NOE";
      $DxNOE[$n] = $zeile[$nvar];     
    }
    
    for ($n = 1; $n < 21; $n++){
      $ANxVAR[$n] = "A" .$n;
      $nvar = "AN" .$n ."NAME";
      $ANxNAME[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."EN";
      $ANxEN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."ISDIGITAL";
      $ANxISDIGITAL[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."MIN";
      $ANxMIN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."SCALE";
      $ANxSCALE[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."AL1LVL";
      $ANxAL1LVL[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."AL2LVL";
      $ANxAL2LVL[$n] = floatval($zeile[$nvar]);      
      $nvar = "AN" .$n ."AL1TXT";
      $ANxAL1TXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."AL2TXT";
      $ANxAL2TXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."OKTXT";
      $ANxOKTXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."AL1EN";
      $ANxAL1EN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."AL2EN";
      $ANxAL2EN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."DALVAL";
      $ANxDALVAL[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."NOE";
      $ANxNOE[$n] = $zeile[$nvar];
      
    }
    $TIMEOUT = intval($zeile['MSGINTERVAL']); 
    $TWSWAL = $zeile['TWSWAL']; 

  }
  $ergebnis->close();
}

echo "<font face = \"courier\"><br>";

// -------------------------------------------------------
// Daten 
// -------------------------------------------------------
if ($tjob == '1')
  
  {
  $sr = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $swgs84b, $swgs84l, 96, 0);
  $ss = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $swgs84b, $swgs84l, 96, 0);
 
  echo "wgsl/b: " .$swgs84l ." / " .$swgs84b ."<br>\n\r";
  echo "Sunrise: " .$sr  ."; " .date('d.m H:i:s',($sr)) ."<br>\n\r";
  echo "Sunset: " . $ss ."; " .date('d.m H:i:s',($ss)) ."<br>\n\r";
  
  $frage = "SELECT * FROM bftopass WHERE (ZEIT < '$tstamp1') AND PUSER = '$tpuser' ORDER BY ZEIT DESC LIMIT 1;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $dt1 = intval($zeile['ZEIT']);
    $dd1 = substr($zeile['D1'],0,1);
  }
  $ergebnis->close();

  //echo date('d.m H:i:s',$dt1) ."    " .$dd1 ."<br>\n\r";
  //echo "-------------------------------------------------------------------<br>\n\r";

  // next
  // $frage = "SELECT * FROM bftopass WHERE (ZEIT > '$tstamp2') AND PUSER = '$tpuser' ORDER BY ZEIT ASC LIMIT 1;";

  $n = 0;
  $frage = "SELECT * FROM bftopass WHERE (ZEIT > '$tstamp1') && (ZEIT < '$tstamp2') AND PUSER = '$tpuser' ORDER BY ZEIT;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $dt[$n] = intval($zeile['ZEIT']);
    $dd[$n] = substr($zeile['D1'],0,1);
    $n++;
  }
  $ergebnis->close();

  $trun = $tstamp1;
  //$tdelta = 3600;
  $tdelta = 600;
  $tday = 86400;
  $tx = 24;

  $lastn = $n;
  $dlast = $dd1;
  $tlast = $trun;
  $tlow = 0;
  $thigh = 0;

  //echo "trun: " .$trun .", dt: " .$dt[$n] .", n: " .$n ."<br>\n\r";

  for ($n = 0; $n <= $lastn; $n++)
  {
    //echo "trun: " .$trun ." TN: " .$dt[$n] ." ";
    //echo "DN: " .$dd[$n] ." DL: " .$dlast ."<br>\n\r";
    //echo date('d.m H:i:s',($trun)) ."<br>\n\r";
    
    if($dt[$n] > ($trun + $tdelta))
    {
      if ($dlast == "1")
      {
        $thigh = $thigh + (($trun + $tdelta) - $tlast);
      } else 
      {
        $tlow = $tlow + (($trun + $tdelta) - $tlast);
      }    
      echo date('d.m H:i:s',($trun));
      echo "; " .$tlow ."; " .number_format(($tlow / 600),2) ."<br>\n\r";
      
      $trun = $trun + $tdelta;
      $tlow = 0;
      $thigh = 0;
      $tlast = $trun;   
      
    } else 
    {
      if ($dd[$n] != $dlast)
      {
        
        //echo "N: " .$n .", DN: " .$dd[$n] .", Dlast: "  .$dlast .", dt: " .$dt[$n] .", trun: " .$trun ." dt-trun: " .$dt[$n]-$trun;
        //echo " L: " .$tlow ."; H: " .$thigh .", S: " .($tlow+$thigh); // ."<br>\n\r";
        //echo "<br>\n\r";  
        
        if($dd[$n] == "1")
        {
          $tlow = $tlow + ($dt[$n] - $tlast);
          $dlast = "1";
          $tlast = $dt[$n];
        } else 
        {
          $thigh = $thigh + ($dt[$n] - $tlast);
          $dlast = "0";
          $tlast = $dt[$n];
        }
      }
    }
  }
}

if ($tjob == '2')
  
  {
    

  $n = 0;
  $frage = "SELECT * FROM bftopass WHERE (ZEIT > '$tstamp1') && (ZEIT < '$tstamp2') AND PUSER = '$tpuser' ORDER BY ZEIT;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    echo date('d.m H:i:s',(intval($zeile['ZEIT']))) ."; ";
    echo substr($zeile['D1'],0,1) ."<br>\n\r";

  }
  $ergebnis->close();
 
}

// -------------------------------------------------------
// ENDE
// -------------------------------------------------------
$mysqli->close();

//header("Content-type: image/png");
//imagepng($bild);
//imagedestroy($bild);
?>
