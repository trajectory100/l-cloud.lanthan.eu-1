<?PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  session_start();
  header ("Content-type: image/png");

$slogin = 0;
$sadmin = 0;
$suser = 0;     
$tpid =  intval(htmlspecialchars($_REQUEST['PID'])); 
$graph =  htmlspecialchars($_REQUEST['GRAPH']); 
$tstamp =  intval(htmlspecialchars($_REQUEST['TIME'])); 
$kw =  htmlspecialchars($_REQUEST['KW']);
$err = htmlspecialchars($_REQUEST['ERR']);  /// error
// Aufrufschema:
// KW: 1= Wochenansicht (7Tage) 0= tagesansicht
// ERR: 1= Fehlerstatus rot anzeigen
// TSTAMP: UnixTimstamp Letzter ta
// PID: ProjectID (wird in Pnummer aufgel�st
// GRAPH: A1-9 und D1-16

if ($tstamp < 946684800) {//1.1.2000
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

// $db_server = 'rdbms.strato.de';
// $db_benutzer = 'U969575';
// $db_passwort = 'MySqL89dB3';
// $db_name = 'DB969575';
// $db_table = 'bftopass';
// $mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
// if($mysqli->connect_error){
//   echo "Fehler bei der Verbindung: " .mysqli_connect_error();
//   exit();
// }
require_once "base.php";

// -------------------------------------------------------
// Aufrufrechte / Login kl�ren
// -------------------------------------------------------       
if (isset($_SESSION['user']))
{
  $quser = $mysqli->real_escape_string($_SESSION['user']);
//  echo "tpid: " . $tpid ."<br>";
//  echo "quser: " . $quser ."<br>";  
  $frage = "SELECT * FROM bfuser_has_bfprojekt WHERE bfuser_ID = '$quser' AND bfprojekt_ID = '$tpid';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array())
  {
//  	$spuser = $zeile['bfuser_ID'];
    $spid = $zeile['bfprojekt_ID'];
//  	echo "user: ".$spuser ."  " .$spid ."<br>";
  }
  if ($tpid == $spid)
  {
    $_SESSION['pid'] = $tpid;
    $slogin = 1;
    // echo "login ok";
  }
  if (isset($_SESSION['admin']))
  {
    if ($_SESSION['admin'] == 1)
    {
      $slogin = 1;
      $sadmin = 1;  
    }
  }
  if (isset($_SESSION['username']))
  {
    $suser = $_SESSION['username'];
  }
  $ergebnis->close();    
}

// -------------------------------------------------------
// Headmenue
// -------------------------------------------------------
// $frage = "SELECT * FROM bfprojekt WHERE ID = '$tpid';"; //before and I changed that 9/5/2020

$frage = "SELECT * FROM bfprojektini WHERE ID = '$tpid';";
$ergebnis = $mysqli->query($frage);
while($zeile = $ergebnis->fetch_array())
{
	$spuser = $zeile['PUSER'];
  $sort = $zeile['ORT'];
}
$ergebnis->close();
 
// -------------------------------------------------------
// Dx Status und Name
// -------------------------------------------------------

// ----- Projekteinstellungen NEU
$frage = "SELECT * FROM bftopassini WHERE PUSER = '$spuser';";      
$ergebnis = $mysqli->query($frage);
while($zeile = $ergebnis->fetch_array())
{
  for ($n = 1; $n < 17; $n++){
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
    $DxVAR[$n] = "D" .$n;
  }
  for ($n = 1; $n < 10; $n++){
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
    $nvar = "AN" .$n ."MIN";
    $ANxMIN[$n] = floatval($zeile[$nvar]);
    $nvar = "AN" .$n ."MAX";
    $ANxMAX[$n] = floatval($zeile[$nvar]);
    $nvar = "AN" .$n ."OKTXT";  
    $ANxOKTXT[$n] = $zeile[$nvar];        
    $nvar = "AN" .$n ."AL1TXT";
    $ANxAL1TXT[$n] = $zeile[$nvar];
    $nvar = "AN" .$n ."AL2TXT";
    $ANxAL2TXT[$n] = $zeile[$nvar];
  }
  $TIMEOUT = intval($zeile['INTERVAL']); 
  $TWSWAL = $zeile['TWSWAL']; 
}
$ergebnis->close();


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
// $text = "DEBUG: NR" .$graphNR ." isD" .$ANxNAME[9] .$AN9NAME;
// imagestring($bild, 2, 400, 0, $text, $sw);

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
$frage = "SELECT * FROM $db_table WHERE ((ZEIT >$tstamp1) && (ZEIT<$tstamp2) && (PUSER = '$spuser')) ORDER BY ZEIT;";
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
  // timeout
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


// -------------------------------------------------------
// ENDE
// -------------------------------------------------------
$mysqli->close();

header("Content-type: image/png");
imagepng($bild);
imagedestroy($bild);
?>
