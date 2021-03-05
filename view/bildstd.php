<?PHP
  session_start();
  
  header ("Content-type: image/png");
  //ini_set('display_errors', '1');
 

 
// ------------------------------------------------------------------------------------------
// Datenbankverbindung
// ------------------------------------------------------------------------------------------

//require_once "../sub/base.php";

require_once "base.php";


// ------------------------------------------------------------------------------------------
// Daten�bergabe
// ------------------------------------------------------------------------------------------
$tpid =  intval(htmlspecialchars($_REQUEST['PID'])); 
$tpuser =  htmlspecialchars($_REQUEST['PUSER']); 
$graph =  htmlspecialchars($_REQUEST['GRAPH']); 
$tstamp =  intval(htmlspecialchars($_REQUEST['TIME'])); 
$kw =  htmlspecialchars($_REQUEST['KW']);
$err = htmlspecialchars($_REQUEST['ERR']);  /// error
// Aufrufschema:
// KW: 1= Wochenansicht (7Tage) 0=tagesansicht 2=monat
// ERR: 1= Fehlerstatus rot anzeigen
// TSTAMP: UnixTimstamp Letzter ta
// PID: ProjectID (wird in Pnummer aufgel�st
// GRAPH: A1-9 und D1-16

 
// ------------------------------------------------------------------------------------------
// SESSION User und Rechte 
// ------------------------------------------------------------------------------------------
$sesOK = 0;
$sesUser = "";
$sesUserid = 0;
$sesAdmin = 0;
$sesViewall = 0;
$sesSuperadmin = 0;

if (isset($_SESSION['userid'])){
  $sesUserid = htmlentities($_SESSION['userid']);
  $sesOK = 1;
  if (isset($_SESSION['user'])){
    $sesUser = htmlentities($_SESSION['user']);
  }  
  if (isset($_SESSION['admin'])){
    $sesAdmin = htmlentities($_SESSION['admin']);
  }
  if (isset($_SESSION['viewall'])){
    $sesViewall = htmlentities($_SESSION['viewall']);
  }
  if (isset($_SESSION['superadmin'])){
    $sesSuperadmin = htmlentities($_SESSION['superadmin']);
  }
}


// ------------------------------------------------------------------------------------------
// Zeitraum Anfang und Ende
// ------------------------------------------------------------------------------------------
if ($tstamp < 946684800) {// vor dem 1.1.2000
  $tstamp = time();
}
$m = date('m',$tstamp);
$d = date('d',$tstamp);
$y = date('Y',$tstamp);
$tstamp2 = mktime(23,59,59,$m,$d,$y);   // tstamp 23:59:59h des tages von tstamp
$tstamp1 = mktime(0,0,0,$m,$d,$y);    // tstamp 0:00:00h des tages von tstamp
if ($kw == '1'){
  $tstamp1 = mktime(0,0,0,$m,$d,$y) - 518400; // tstamp 0:00:00h 6 tage vor tstamp
} 
if ($kw == '2'){
  $tstamp1 = mktime(0,0,0,$m,1,$y);
  $tstamp2 = mktime(0,0,0,$m + 1,1,$y) - 1;
} 


// -------------------------------------------------------
// Dx Status und Name
// -------------------------------------------------------

// -------------------------------------------------------
// Projektdaten / Einstellungen Laden
// -------------------------------------------------------
if ($sesOK == 1){
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


// -------------------------------------------------------
// Bildaufbau
// -------------------------------------------------------
$graphAD = substr($graph, 0,1);
$graphNR = intval(substr($graph, -1*(strlen($graph)-1)));

// -------------------------------------------------------
//  Bildh�he und Unterscheidung Analog / Digital
if ($graphAD == "D")
{
  $digianalog = "D";
  $bildh = 50; // bild h�he
} 
else 
{
  $digianalog = "A";
  $bildh = 225; // bild h�he
  
  if ($ANxISDIGITAL[$graphNR] == "1")
  {
    $digianalog = "B";
    $bildh = 50; //bild h�he
  }
}

$bildw = 740; // bild breite
$bildbl = 10; // bild abstand links
$bildbr = 10; // bild abstand rechts
$bildbo = 15; // bild abstand oben
$bildbu = 10; // bild abstand unten
$bilddw = $bildw - $bildbl - $bildbr;
$bilddh = $bildh - $bildbo - $bildbu;
$xfaktorday = 86400 * 1 / $bilddw;
$xfaktorweek = 86400 * 7 / $bilddw;
$xfaktormonth = 86400 * 31 / $bilddw;

// -------------------------------------------------------
// Farbdefinition
$bild = imagecreatetruecolor($bildw, $bildh);
$grau = imagecolorallocate($bild, 200, 200, 200);
$rgbal = imagecolorallocate($bild, 255 , 175, 175);
$we = imagecolorallocate($bild, 255, 255, 255);
$rot = imagecolorallocate($bild, 255, 0, 0);
$gruen = imagecolorallocate($bild, 0, 255, 0);
$blau = imagecolorallocate($bild, 0, 0, 255);
$sw = imagecolorallocate($bild, 0, 0, 0);
$ra = $sw;
$rb = $grau;


// -------------------------------------------------------
// Rahmen um Graph
imagefilledrectangle($bild, 0, 0, $bildw, $bildh, $we);
imageline($bild, $bildbl,$bildbo,$bildw-$bildbr,$bildbo, $ra);
imageline($bild, $bildbl,$bildh-$bildbu,$bildw-$bildbr,$bildh-$bildbu, $ra);
imageline($bild, $bildbl,$bildbo,$bildbl,$bildh-$bildbu, $ra);
imageline($bild, $bildw-$bildbr,$bildbo,$bildw-$bildbr,$bildh-$bildbu, $ra);

// debug1
 //$text = "DEBUG: " .$tpuser ."NR:" .$graphNR ." isD" .$ANxNAME[9] .$AN9NAME;
 //imagestring($bild, 2, 400, 0, $text, $sw);

// -------------------------------------------------------
// Fehlerstatus
if ($err == "1"){
  imageline($bild, $bildw-2,0,$bildw-2,$bildh, $rot);
  imageline($bild, $bildw-1,0,$bildw-1,$bildh, $rot);
}

// -------------------------------------------------------
// Text �ber graph
if ($kw == "1"){
  $text =  date('d.m',$tstamp1) ."-" .date('d.m.Y  ',$tstamp2) .$graph;
} 
else {
  $text =  date('d.m.Y  ',$tstamp1) .$graph;
}
if ($graphAD == "A")
{
  $text = $text ."   " .$ANxNAME[$graphNR] ."  MIN: " .$ANxMIN[$graphNR] ."  MAX: " .$ANxMAX[$graphNR];
  if ($ANxAL1EN[$graphNR] == "1")
  {
    $text2 = "1";
  }  
  if ($ANxAL2EN[$graphNR] == "1")
  {
    $text2 = $text2 ."2";
  }
}
if ($graphAD == "D")
{
  $text = $text ."   " .$DxNAME[$graphNR] ."  L=" .$DxL[$graphNR] ."  H=" .$DxH[$graphNR];  
  if ($DxALERT[$graphNR] == "1")
  {
    $text2 = "A" .$DxALVAL[$graphNR];
  }
}
imagestring($bild, 2, 5, 0, $text, $sw);
imagestring($bild, 2, 720, 0, $text2, $rot);


// -------------------------------------------------------
// X Achse Alarmlevel 
if (($ANxAL1EN[$graphNR] == "1") && ($digianalog == "A"))
{
  $dAn = $ANxMAX[$graphNR] - $ANxMIN[$graphNR];
  $faktor = $bilddh / $dAn;
  
  $yb = ($ANxAL1LVL[$graphNR] - $ANxMIN[$graphNR]) * $faktor ;
  $y = $bildbo + ($bilddh - (int)$yb);
  imageline($bild, $bildbl ,$y, $bildw-$bildbr, $y, $rgbal); 
  
  $yb = ($ANxAL2LVL[$graphNR] - $ANxMIN[$graphNR]) * $faktor ;
  $y = $bildbo + ($bilddh - (int)$yb);
  imageline($bild, $bildbl ,$y, $bildw-$bildbr, $y, $rgbal);
} 

// -------------------------------------------------------
// X-Achse Beschriftung & Skala
if ($kw == "1")
{
  $tfaktor = $xfaktorweek;
  for ($i = 0; $i < 7; $i++)
  {
    $x = $bildbl + $i * ($bilddw / 7);
    if ($i > 0)
    { 
      imageline($bild, $x, $bildbo+1, $x, $bildh-$bildbu-1, $rb);
    }
    $datestamp = $tstamp1 + $i * 86400;
    $text =  date('d.m',$datestamp);
    imagestring($bild, 1, $x, $bildh-$bildbu+1, $text, $ra);
  }
}
else 
{
  $tfaktor = $xfaktorday;
  for ($i = 0; $i < 24; $i++)
  {
    $x = $bildbl + $i * ($bilddw / 24);
    if ($i > 0)
    {
      imageline($bild, $x, $bildbo+1, $x, $bildh-$bildbu-1, $rb);
    }
    $text = $i ."h";
    imagestring($bild, 1, $x, $bildh-$bildbu+1, $text, $ra);
  }
}

// -------------------------------------------------------
// Y-Achse Beschriftung & Skala
if (($digianalog == "D") || ($digianalog == "B"))
{
  $yoff = 3;
  $ymult = 15;
  imagestring($bild, 1, $bildbl - 5, $bildh - ($bildbu + 9 + $ymult), "H", $ra);
  imagestring($bild, 1, $bildbl - 5, $bildh - ($bildbu + 9), "L", $ra);
} 
else 
{
  $yoff = 10;
  for ($i = 0; $i < 10; $i++)
  {
    $y = $bildbo + $i * ($bildh - $bildbo - $bildbu) / 10;
    imageline($bild, $bildbl, $y, $bildw - $bildbr, $y, $rb);
    $text = (10 - $i) * ($ANxMAX[$graphNR] - $ANxMIN[$graphNR]) / 10 + $ANxMIN[$graphNR];
    imagestring($bild, 1, $bildbl - 10, $y, $text, $ra);
  }
}
$xoff = $bildbl;

// -------------------------------------------------------
// Daten 
// -------------------------------------------------------
$n = 0;
$alttime = 0;
//------------------------------------------------
// topass
if ($db_table == 'bftopass')
{
  $frage = "SELECT * FROM bftopass WHERE (ZEIT >$tstamp1) && (ZEIT<$tstamp2) AND PUSER = '$tpuser' ORDER BY ZEIT;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $x = $xoff + (intval($zeile['ZEIT']) - $tstamp1) / $tfaktor;
    
    // -------------------------------------------------------
    // Analogwerte
    if ($digianalog == "A")
    {
      $wert = substr($zeile[$ANxVAR[$graphNR]],0,5);
      if ($wert <> "")
      {
        $dAn = $ANxMAX[$graphNR] - $ANxMIN[$graphNR];
        $faktor = $bilddh / $dAn;
        $yb = (floatval($wert) * $ANxSCALE[$graphNR] - $ANxMIN[$graphNR]) * $faktor ;
        $y = $bildbo + ($bilddh - (int)$yb);
        imageline($bild, $x, $bildh-$bildbu+1, $x, $bildh-$bildbu-1, $blau);     
      }
      else 
      {
        imageline($bild, $x, $bildh-$bildbu+2, $x, $bildh-$bildbu-3, $rot);  
      } 
    }
    
    // -------------------------------------------------------
    // Digitale Analogwerte
    if ($digianalog == "B")
    {
      $wert = substr($zeile[$ANxVAR[$graphNR]],0,5);
      if ($wert <> "")
      {
        $abit = floatval($wert) > $ANxAL1LVL[$graphNR];
        $y = $bildh - ($bildbu + $yoff + ((int)($abit) * $ymult));
      }
    }
    
    // -------------------------------------------------------
    // Digitalwerte
    if ($digianalog == "D")
    {
      $wert = substr($zeile[$DxVAR[$graphNR]],0,1);
      if ($wert <> "")
      $y = $bildh - ($bildbu + $yoff + (int)(intval($wert) * $ymult));    
     }
    
    // -------------------------------------------------------
    // timeout und zeichnen
    $xtime = intval($zeile['ZEIT']);
    if (($xtime - $xalttime) < ($TIMEOUT*60))
    {
      if ($n > 0)
      {
        if ($digianalog == "A")  // analogwerte direkt verbinden
        {
          imageline($bild, $xalt ,$yalt, $x, $y, $rot);
        }
        else //digitalwerte flanke zeichnen
        {
          imageline($bild, $xalt ,$yalt, $x, $yalt, $rot);
          imageline($bild, $x ,$yalt, $x, $y, $rot);
        }
      }   
    }  
    $xalt = $x;
    $yalt = $y;
    $xalttime = $xtime;
    $n++;
  }
  $ergebnis->close();
}
//----------------------
// ccio
if ($db_table == 'bfccio')
{
  $frage = "SELECT * FROM bfccio WHERE (time >$tstamp1) && (time<$tstamp2) AND imei = '$tpuser' ORDER BY time;";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
    $x = $xoff + (intval($zeile['time']) - $tstamp1) / $tfaktor;
    
    // -------------------------------------------------------
    // Analogwerte
    if ($digianalog == "A")
    {
      $rohwert = explode(";", $zeile['Ax']);
      $wert = $rohwert[$graphNR-1];
      //$wert = substr($zeile[$ANxVAR[$graphNR]],0,5);
      if ($wert <> "")
      {
        $dAn = $ANxMAX[$graphNR] - $ANxMIN[$graphNR];
        $faktor = $bilddh / $dAn;
        $yb = (floatval($wert) * $ANxSCALE[$graphNR] - $ANxMIN[$graphNR]) * $faktor ;
        $y = $bildbo + ($bilddh - (int)$yb);
        imageline($bild, $x, $bildh-$bildbu+1, $x, $bildh-$bildbu-1, $blau);     
      }
      else 
      {
        imageline($bild, $x, $bildh-$bildbu+2, $x, $bildh-$bildbu-3, $rot);  
      } 
    }
    
    // -------------------------------------------------------
    // Digitale Analogwerte
    if ($digianalog == "B")
    {
      $rohwert = explode(";", $zeile['Ax']);
      $wert = $rohwert[$graphNR-1];
      //$wert = substr($zeile[$ANxVAR[$graphNR]],0,5);
      if ($wert <> "")
      {
        $abit = floatval($wert) > $ANxAL1LVL[$graphNR];
        $y = $bildh - ($bildbu + $yoff + ((int)($abit) * $ymult));
      }
    }
    
    // -------------------------------------------------------
    // Digitalwerte
    if ($digianalog == "D")
    {
      $wert = substr($zeile['Dx'],$graphNR-1,1);
      //$wert = substr($zeile[$DxVAR[$graphNR]],0,1);
      if ($wert <> "")
      $y = $bildh - ($bildbu + $yoff + (int)(intval($wert) * $ymult));    
     }
    
    // -------------------------------------------------------
    // timeout und zeichnen
    $xtime = intval($zeile['time']);
    if (($xtime - $xalttime) < ($TIMEOUT*60))
    {
      if ($n > 0)
      {
        if ($digianalog == "A")  // analogwerte direkt verbinden
        {
          imageline($bild, $xalt ,$yalt, $x, $y, $rot);
        }
        else //digitalwerte flanke zeichnen
        {
          imageline($bild, $xalt ,$yalt, $x, $yalt, $rot);
          imageline($bild, $x ,$yalt, $x, $y, $rot);
        }
      }   
    }  
    $xalt = $x;
    $yalt = $y;
    $xalttime = $xtime;
    $n++;
  }
  $ergebnis->close();
}

// -------------------------------------------------------
// ENDE
// -------------------------------------------------------
$mysqli->close();

header("Content-type: image/png");
imagepng($bild);
imagedestroy($bild);
?>
