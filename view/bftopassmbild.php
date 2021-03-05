<?PHP
  session_start();
  header ("Content-type: image/png");

$slogin = 0;
$sadmin = 0;
$suser = 0;     
$tpid =  intval(htmlspecialchars($_REQUEST['PID'])); 
$graph =  htmlspecialchars($_REQUEST['GRAPH']); 
$tstamp =  intval(htmlspecialchars($_REQUEST['TIME'])); 
$kw =  htmlspecialchars($_REQUEST['KW']); 
if ($tstamp < 946684800) {//1.1.2000
  $tstamp = time();
}
$m = date('m',$tstamp);
$d = date('d',$tstamp);
$y = date('Y',$tstamp);
//$tstamp2 = mktime(23,59,59,$m,$d,$y);   // tstamp 23:59:59h des tages von tstamp
//$tstamp1 = mktime(0,0,0,$m,$d,$y);    // tstamp 0:00:00h des tages von tstamp
//if ($kw == '1'){
//  $tstamp1 = mktime(0,0,0,$m,$d,$y) - 518400; // tstamp 0:00:00h 6 tage vor tstamp
//} 
$tstamp1 = mktime(0,0,0,$m,1,$y);
$dim = date('t',$tstamp);
$tstamp2 = mktime(23,59,59,$m,$dim,$y);


require_once "base.php";


// -------------------------------------------------------
// Aufrufrechte / Login kl�ren
// -------------------------------------------------------       
if (isset($_SESSION['user'])){
  $quser = $mysqli->real_escape_string($_SESSION['user']);
//  echo "tpid: " . $tpid ."<br>";
//  echo "quser: " . $quser ."<br>";  
  $frage = "SELECT * FROM bfuser_has_bfprojekt WHERE bfuser_ID = '$quser' AND bfprojekt_ID = '$tpid';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
//  	$spuser = $zeile['bfuser_ID'];
    $spid = $zeile['bfprojekt_ID'];
//  	echo "user: ".$spuser ."  " .$spid ."<br>";
  }
  if ($tpid == $spid){
    $_SESSION['pid'] = $tpid;
    $slogin = 1;
//    echo "login ok";
  }
  if (isset($_SESSION['admin'])){
    if ($_SESSION['admin'] == 1){
      $slogin = 1;
      $sadmin = 1;  
    }
  }
  if (isset($_SESSION['username'])){
    $suser = $_SESSION['username'];
  }
  $ergebnis->close();    
}

// -------------------------------------------------------
// Headmenue
// -------------------------------------------------------
// $frage = "SELECT * FROM bfprojekt WHERE ID = '$tpid';"; // i change that as below in 9/5/2020

$frage = "SELECT * FROM bfprojektini WHERE ID = '$tpid';";
$ergebnis = $mysqli->query($frage);
while($zeile = $ergebnis->fetch_array()){
	$spuser = $zeile['PUSER'];
  $sort = $zeile['ORT'];
}
$ergebnis->close();
 
// -------------------------------------------------------
// Dx Status und Name
// -------------------------------------------------------
$frage = "SELECT * FROM bftopassini WHERE PUSER = '$spuser';";      
$ergebnis = $mysqli->query($frage);
while($zeile = $ergebnis->fetch_array()){
  $D1Alert = $zeile['DI1ALERT'];
  $D2Alert = $zeile['DI2ALERT'];
  $D3Alert = $zeile['DI3ALERT'];
  $D4Alert = $zeile['DI4ALERT'];
  $D1L = $zeile['DI1L'];
  $D2L = $zeile['DI2L'];
  $D3L = $zeile['DI3L'];
  $D4L = $zeile['DI4L'];
  $D1H = $zeile['DI1H'];
  $D2H = $zeile['DI2H'];
  $D3H = $zeile['DI3H'];
  $D4H = $zeile['DI4H'];
  $D1NAME = $zeile['DI1NAME'];
  $D2NAME = $zeile['DI2NAME'];
  $D3NAME = $zeile['DI3NAME'];
  $D4NAME = $zeile['DI4NAME'];
  $AN1MIN = $zeile['AN1MIN']; 
  $AN2MIN = $zeile['AN2MIN']; 
  $AN1MAX = $zeile['AN1MAX']; 
  $AN2MAX = $zeile['AN2MAX']; 
  $AN1NAME = $zeile['AN1NAME']; 
  $AN2NAME = $zeile['AN2NAME'];
  $AN1SCALE = $zeile['AN1SCALE']; 
  $AN2SCALE = $zeile['AN2SCALE']; 
  $AN1EN = $zeile['AN1EN'];
  $AN2EN = $zeile['AN2EN'];

  $AN1AL1EN = $zeile['AN1AL1EN'];      
  $AN1AL2EN = $zeile['AN1AL2EN']; 
  $AN1AL1TXT = $zeile['AN1AL1TXT']; 
  $AN1AL2TXT = $zeile['AN1AL2TXT']; 
  $AN1OKTXT = $zeile['AN1OKTXT']; 
  $AN1AL1LVL = floatval($zeile['AN1AL1LVL']);
  $AN1AL2LVL = floatval($zeile['AN1AL2LVL']);

  $TIMEOUT = intval($zeile['INTERVAL']); 
}
$ergebnis->close();

// -------------------------------------------------------
// Bildaufbau
// -------------------------------------------------------
if (substr($graph, 0,1) == "D"){
  $digianalog = "D";
  $bildh = 50; // bild h�he
} 
else {
  $digianalog = "A";
  $bildh = 225; // bild h�he
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

// Rahmen um Graph
imagefilledrectangle($bild, 0, 0, $bildw, $bildh, $we);
imageline($bild, $bildbl,$bildbo,$bildw-$bildbr,$bildbo, $ra);
imageline($bild, $bildbl,$bildh-$bildbu,$bildw-$bildbr,$bildh-$bildbu, $ra);
imageline($bild, $bildbl,$bildbo,$bildbl,$bildh-$bildbu, $ra);
imageline($bild, $bildw-$bildbr,$bildbo,$bildw-$bildbr,$bildh-$bildbu, $ra);

// Text �ber graph
//$text =  date('d.m.Y H:i:s',$tstamp1) ." - "  .date('H:i:s',$tstamp2) ." "  .$graph;
if ($kw == "1"){
  $text =  date('d.m',$tstamp1) ."-" .date('d.m.Y  ',$tstamp2) .$graph;
} 
else {
  $text =  date('d.m.Y  ',$tstamp1) .$graph;
}
if ($graph == "A1")
  $text = $text ."   " .$AN1NAME ."  MIN: " .$AN1MIN ."  MAX: " .$AN1MAX;
if ($graph == "A2")
  $text = $text ."   " .$AN2NAME ."  MIN: " .$AN2MIN ."  MAX: " .$AN2MAX;   
if ($graph == "D1")
  $text = $text ."   " .$D1NAME ."  L=" .$D1L ."  H=" .$D1H;     
if ($graph == "D2")
  $text = $text ."   " .$D2NAME ."  L=" .$D2L ."  H=" .$D2H;  
if ($graph == "D3")
  $text = $text ."   " .$D3NAME ."  L=" .$D3L ."  H=" .$D3H;  
if ($graph == "D4")
  $text = $text ."   " .$D4NAME ."  L=" .$D4L ."  H=" .$D4H;
imagestring($bild, 2, 5, 0, $text, $sw);

// X Achse Alarmlevel 
if (($AN1AL1EN == "1") && ($digianalog == "A")){
  $dAn = $AN1MAX - $AN1MIN;
  $faktor = $bilddh / $dAn;
  //$yb = (floatval($zeile['A1']) * $AN1SCALE - $AN1MIN) * $faktor ;
  $yb = ($AN1AL1LVL - $AN1MIN) * $faktor ;
  $y = $bildbo + ($bilddh - (int)$yb);
  imageline($bild, $bildbl ,$y, $bildw-$bildbr, $y, $rgbal);  
  $yb = ($AN1AL2LVL - $AN1MIN) * $faktor ;
  $y = $bildbo + ($bilddh - (int)$yb);
  imageline($bild, $bildbl ,$y, $bildw-$bildbr, $y, $rgbal);
} 

// X-Achse Beschriftung & Skala
$tfaktor = $xfaktormonth;
for ($i = 0; $i < $dim; $i++){
  $x = $bildbl + $i * ($bilddw / 31);
  if ($i > 0){ 
    imageline($bild, $x, $bildbo+1, $x, $bildh-$bildbu-1, $rb);
  }
  $datestamp = $tstamp1 + $i * 86400;
  $text =  date('d',$datestamp);
  imagestring($bild, 1, $x, $bildh-$bildbu+1, $text, $ra);
}




// Y-Achse Beschriftung & Skala
if ($digianalog == "D"){
  $yoff = 3;
  $ymult = 15;
  imagestring($bild, 1, $bildbl - 5, $bildh - ($bildbu + 9 + $ymult), "H", $ra);
  imagestring($bild, 1, $bildbl - 5, $bildh - ($bildbu + 9), "L", $ra);
} 
else {
  $yoff = 10;
  for ($i = 0; $i < 10; $i++){
    $y = $bildbo + $i * ($bildh - $bildbo - $bildbu) / 10;
    imageline($bild, $bildbl, $y, $bildw - $bildbr, $y, $rb);
    if ($graph == "A1"){
      $text = (10 - $i) * ($AN1MAX - $AN1MIN) / 10 + $AN1MIN;
    }
    if ($graph == "A2"){
      $text = (10 - $i) * ($AN2MAX - $AN2MIN) / 10 + $AN2MIN;
    }
    imagestring($bild, 1, $bildbl - 10, $y, $text, $ra);
  }
}
$xoff = $bildbl;


$n = 0;
$alttime = 0;
$frage = "SELECT * FROM $db_table WHERE ((ZEIT >$tstamp1) && (ZEIT<$tstamp2) && (PUSER = '$spuser')) ORDER BY ZEIT;";
$ergebnis = $mysqli->query($frage);
while($zeile = $ergebnis->fetch_array()){
  $x = $xoff + (intval($zeile['ZEIT']) - $tstamp1) / ($tfaktor*1);

  if ($graph == "A1"){
    $dAn = $AN1MAX - $AN1MIN;
    $faktor = $bilddh / $dAn;
    $yb = (floatval($zeile['A1']) * $AN1SCALE - $AN1MIN) * $faktor ;
    $y = $bildbo + ($bilddh - (int)$yb);
    imageline($bild, $x, $bildh-$bildbu+1, $x, $bildh-$bildbu-1, $blau);
    $type = "a";
  }
  if ($graph == "A2"){
    $dAn = $AN2MAX - $AN2MIN;
    $faktor = $bilddh / $dAn;
    $yb = (floatval($zeile['A2']) * $AN2SCALE - $AN2MIN) * $faktor ;
    $y = $bildbo + ($bilddh - (int)$yb);
    imageline($bild, $x, $bildh-$bildbu+1, $x, $bildh-$bildbu-1, $blau);
    $type = "a";
  }   
  if ($graph == "D1"){
    $y = $bildh - ($bildbu + $yoff + (int)(intval($zeile['D1']) * $ymult));
    $type = "d";
  }      
  if ($graph == "D2"){
    $y = $bildh - ($bildbu + $yoff + (int)(intval($zeile['D2']) * $ymult));
    $type = "d";
  }  
  if ($graph == "D3"){
    $y = $bildh - ($bildbu + $yoff + (int)(intval($zeile['D3']) * $ymult));
    $type = "d";
  }  
  if ($graph == "D4"){
    $type = "d";
    $y = $bildh - ($bildbu + $yoff + (int)(intval($zeile['D4']) * $ymult));
  }
  $xtime = intval($zeile['ZEIT']);
  
  if (($xtime - $xalttime) < ($TIMEOUT*60)){
    if ($n > 0){
      //$text = $ya;
      //imagestring($bild, 1, 300, 10*$n, $text, $ra);
      //$text = ($xtime - $xalttime);
      //imagestring($bild, 10, 10, 10*$n, $text, $rot);
      if ($type == "a"){
        imageline($bild, $xalt ,$yalt, $x, $y, $rot);
        //imageline($bild, $x ,$y-2, $x, $y+2, $blau);
      }
      else {
        imageline($bild, $xalt ,$yalt, $x, $yalt, $rot);
        imageline($bild, $x ,$yalt, $x, $y, $rot);
        //imageline($bild, $x ,$y-2, $x, $y+2, $blau);
      }
    }   
  }  
  $xalt = $x;
  $yalt = $y;
  $xalttime = $xtime;
  $n++;
}
$ergebnis->close();


$mysqli->close();

header("Content-type: image/png");
imagepng($bild);
imagedestroy($bild);
?>
