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
$debug = intval(htmlspecialchars($_REQUEST['DEBUG'])); 

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

//echo "<font face = \"courier\"><br>";

// -------------------------------------------------------
// Daten 
// -------------------------------------------------------
if ($tjob == '1')
{
  $sr = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, $swgs84b, $swgs84l, 96, 0);
  $ss = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $swgs84b, $swgs84l, 96, 0);
 
  if(debug=="1"){echo "wgsl/b: " .$swgs84l ." / " .$swgs84b ."<br>\n\r";}
  if(debug=="1"){echo "Sunrise: " .$sr  ."; " .date('d.m H:i:s',($sr)) ."<br>\n\r";}
  if(debug=="1"){echo "Sunset: " . $ss ."; " .date('d.m H:i:s',($ss)) ."<br>\n\r";}
  
  $frage = "SELECT * FROM bftopass WHERE (ZEIT < '$tstamp1') AND PUSER = '$tpuser' ORDER BY ZEIT DESC LIMIT 1;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $dt[0] = intval($zeile['ZEIT']);
    $dd[0] = substr($zeile['D1'],0,1);
  }
  $ergebnis->close();

  //echo date('d.m H:i:s',$dt1) ."    " .$dd1 ."<br>\n\r";
  //echo "-------------------------------------------------------------------<br>\n\r";

  // next
  // $frage = "SELECT * FROM bftopass WHERE (ZEIT > '$tstamp2') AND PUSER = '$tpuser' ORDER BY ZEIT ASC LIMIT 1;";

  $n = 1;
  $frage = "SELECT * FROM bftopass WHERE (ZEIT > '$tstamp1') && (ZEIT < '$tstamp2') AND PUSER = '$tpuser' ORDER BY ZEIT;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $dt[$n] = intval($zeile['ZEIT']);
    $dd[$n] = substr($zeile['D1'],0,1);
    $n++;
  }
  $ergebnis->close();
  
  $lastn = $n;
  $tdelta = 600;  // 10min
  $tday = 86400;  // sekunden pro tag
  $tcnt = 1008; // 7 x 24 x 6
 
//--------------------------------------------------------------------------------------------- 
// auswertung 
//--------------------------------------------------------------------------------------------- 
  $block = 0;
  
  for ($n = 0; $n < $tcnt; $n++)
  {
    $tview1 = $tstamp1 + ($n * $tdelta);     
    $tview2 = $tstamp1 + ($n * $tdelta) + $tdelta - 1; 
    if(debug=="1"){echo "<br>-->" .date('d.m H:i:s',($tview1)) .";  " .date('d.m H:i:s',($tview2)) ."<br>\n\r  ";}
    
    //finde ersten wert des zeitraums
    $m = 0;
    $nview1 = 0;
    for ($m = 0; $m <= $lastn; $m++)
    {
      if ($dt[$m] >= $tview1)
      {
        $nview1 = $m-1;
        break;
      }
    }
    for ($m = 0; $m <= $lastn; $m++)
    {
      if ($dt[$m] >= $tview2)
      {
        break;
      }
      $nview2 = $m;
    }
    
    $tonsum = 0;
    $tonstart = 0;
    $tonend = 0;
    for  ($m = $nview1; $m <= $nview2; $m++)
    {
      
      if (($dd[$m]) == '1')
      {
        if ($tonstart == 0)
        {
          $tonstart = $dt[$m];
          if ($tonstart < $tview1)
          {
            $tonstart = $tview1;
          }
        }
      }
    
      if (($tonstart > 0) & ($dd[$m] == '0'))
      {
        $tonsum = $tonsum + ($dt[$m] - $tonstart); 
        $tonstart = 0;
      }

      if(debug=="1"){echo $m .": " .date('d.m H:i:s',($dt[$m])) .";  " .$dd[$m] ."; ". $tonsum ."<br>\n\r  ";}
    }
    if ($tonstart > 0)
    {
      $tonsum = $tonsum + ($tview2 - $tonstart); 
      $tonstart = 0;
    }
    
    $merker[$block] = $tonsum;
    $block++;

    if(debug=="1"){echo "summe: " .$tonsum ."<br>\n\r";}
  }
  
  $block = 0;
  $search = ".";
  $replace = ",";
  $csvdata="";

  for ($tag = 0; $tag < 7; $tag++)
  {
    //echo date('d.m',($tstamp1 + ($tag * 86400))) ."; ";
    echo date('d.m',($tstamp1 + ($tag * 86400))) ."; ";
    
    for ($stunde = 0; $stunde < 144; $stunde++)
    {
      $change =  $merker[$block]; //number_format(($tlow / 600),2) 
      //$change =  number_format(($merker[$block] / 600),2) 
      str_replace ($search, $replace, $change);
      echo $change ."; ";
      $block++;
    }
    echo "<br>\n\r";
  }

  
}


//--------------------------------------------------------------------------------------------- 
// listenausgabe
//--------------------------------------------------------------------------------------------- 
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
