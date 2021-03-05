<?PHP
  session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">
  <title>Lanthan Cloud Monitoring</title>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <!--<META HTTP-EQUIV=Refresh CONTENT="300">-->
  <link href="../css/content.css" rel="stylesheet" type="text/css" />
  <!-- slide toggle -->
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function(){
      $("dt").click(function(){ // trigger 
        $(this).next("dd").slideToggle("fast"); // blendet beim Klick auf "dt" die nächste "dd" ein. 
        $(this).children("a").toggleClass("closed open"); // wechselt beim Klick auf "dt" die Klasse des enthaltenen a-Tags von "closed" zu "open". 
      });
    });
</script>
<!-- datepicker -->
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
<script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
<script>
  $(function() {
      $( "#datepicker" ).datepicker({
          showOn: "both",
          buttonImage: "../image/calendar.gif",
          buttonImageOnly: true,
          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          showWeek: true,
        //   dateFormat: "yy-mm-dd"
      });
  });
</script>

</head>
<body>

<?PHP
require_once "base.php";


// -------------------------------------------------------
// Deklarations / Eingabeabfrage
// ------------------------------------------------------- 
$slogin = 0;
$sadmin = 0;
$suser = 0;     
$tpid =  intval(htmlspecialchars($_REQUEST['PID']));   // projet id
$tstamp =  intval(htmlspecialchars($_REQUEST['TIME']));  //  mitgegebener timestamp
$ww =  htmlspecialchars($_REQUEST['WW']);     // ww-WholeWeek: 0-tag, 1-woche, 2-2wochen, 3-monat

if (isset($_GET["LW"])){
  $lw =htmlspecialchars($_REQUEST['LW']);
}
 
if (isset($_GET["dpdate"])){
  $dpdate = htmlspecialchars($_REQUEST['dpdate']);     // datum vom Datepicker
  $tstamp = strtotime($dpdate);
  //echo $dpdate ."  " .$tstamp ."  " .date('d.m.Y',$tstamp);
}

if ($tstamp < 946684800) {// <1.1.2000 oder kein timestamp
  $tstamp = time();
}

//$kw = date('W',$tstamp);    // kalenderwoche von tstamp
//$yow = date('Y');           // aktuelles jahr
//$week = $yow ."-W" .$kw;    // $week = '2012-W28' -> KW28/2012
//$fdow = strtotime($week);   // timestamp erster tag der woche von tstamp (feste kalenderwoche)
//$ldow = $fdow + 604799;     // timestamp letzter tag der mitgegebenen woche

$m = date('m',$tstamp);
$d = date('d',$tstamp);
$y = date('Y',$tstamp);
$tstamp2 = mktime(23,59,59,$m,$d,$y);   // tstamp 23:59:59h des tages von tstamp
$tstamp1 = mktime(0,0,0,$m,$d,$y);    // tstamp 0:00:00h des tages von tstamp
if ($ww == '1'){
  $tstamp1 = mktime(0,0,0,$m,$d,$y) - 518400; // tstamp 0:00:00h 6 tage vor tstamp
}
$fdom = mktime(0,0,0,$m,1,$y);
$dim = date('t',$tstamp);
$ldom = mktime(23,59,59,$m,$dim,$y);
//echo $fdom ." / " .date('d.m.Y H:i:s',$fdom) ."<br>";
//echo $ldom ." / " .date('d.m.Y H:i:s',$ldom) ."<br>";

$fdow = $tstamp2 - 604799;  // 6*24h*60min*59s vorher
$tgestern = $tstamp - 86400;          // tstamp gestern
$tmorgen = $tstamp + 86400;           // tstamp morgen

$letztewoche = $tstamp - 604800;      // tstamp vor 7 tagen
$naechstewoche = $tstamp + 604800;    //
$letztermonat = $fdom - 604800;      // tstamp vor 7 tagen
$naechstermonat = $ldom + 604800;    //

// -------------------------------------------------------
// Aufrufrechte / Login klären
// -------------------------------------------------------       
if (isset($_SESSION['user'])){
  $quser = $mysqli->real_escape_string($_SESSION['user']); 
  $frage = "SELECT * FROM bfuser_has_bfprojekt WHERE bfuser_ID = '$quser' AND bfprojekt_ID = '$tpid';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
    $spid = $zeile['bfprojekt_ID'];
  }
  if ($tpid == $spid){
    $_SESSION['pid'] = $tpid;
    $slogin = 1;
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
// Projektdaten / einstellungen Laden
// -------------------------------------------------------
if ($slogin == 1){
  // ----- Prokektdaten
  // $frage = "SELECT * FROM bfprojekt WHERE ID = '$tpid';"; changed this line 9/5/2020

  $frage = "SELECT * FROM bfprojektini WHERE ID = '$tpid';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
    $spuser = $zeile['PUSER'];
    $sort = $zeile['ORT'];
    $sland = $zeile['LAND'];
    $swgs84b = floatval($zeile['WGS84B']);
    $swgs84l = floatval($zeile['WGS84L']);
  }
  $ergebnis->close();

  // ----- Projekteinstellungen 
  $frage = "SELECT * FROM bftopassini WHERE PUSER = '$spuser';";      
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){

    for ($n = 1; $n < 9; $n++){
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

    $AN1EN = $zeile['AN1EN'];
    $AN2EN = $zeile['AN2EN'];
    $AN1NAME = $zeile['AN1NAME'];
    $AN1AL1EN = $zeile['AN1AL1EN'];      
    $AN1AL2EN = $zeile['AN1AL2EN']; 
    $AN1AL1TXT = $zeile['AN1AL1TXT']; 
    $AN1AL2TXT = $zeile['AN1AL2TXT']; 
    $AN1OKTXT = $zeile['AN1OKTXT']; 
    $AN1AL1LVL = floatval($zeile['AN1AL1LVL']);
    $AN1AL2LVL = floatval($zeile['AN1AL2LVL']);
    $AN1SCALE = floatval($zeile['AN1SCALE']);

    $TIMEOUT = intval($zeile['INTERVAL']); 

  }
  $ergebnis->close();
}
 

// -------------------------------------------------------
// Webseitenausgabe
// -------------------------------------------------------
echo "<div class=\"container\">";

// ----- Logo Überschrift
echo "<div class=\"contlogo\">";
echo "<div class=\"contlogohead\"><a href=\"../index.php\"><img src=\"../image/lanthan_110x60.jpg\" width=\"110\" height=\"60\" /></a></div>";
echo "<div class=\"contlogotext\"> Lanthan Cloud Monitoring"  ." - " .date('d.m.Y') ."</div>";
echo "</div>";

// ----- Navigation
echo "<div class=\"contnav\">";
echo "<table bgcolor=\"#DDDDDD\" width=\740\">";
//------- Home 
echo "<td width=\"100\"><a href=\"../index.php\">Home</a></td>";
// ----- Projekt
echo "<td width=\"200\" align=\"center\">".$spuser .", " .$sort .", " .$sland ."</br>";
echo "<a href=\"https://maps.google.com/maps?q=" .$swgs84b ."," .$swgs84l ."\" target=\"_new\">";
echo "WGS84: " .$swgs84b .", " .$swgs84l;
echo "</a></td>";
// ----- Date
echo "<td width=\"150\">Data: ".date('d.m.Y',$tstamp1)." to: " .date('d.m.Y',$tstamp2)."</br></td>";
// ----- Links
echo "<td width=\"150\"><a href=\"" .$db_table ."settings.php?PID=" .$tpid ."\">Settings</a></br>";
echo "<a href=\"../edit/comment.php?PID=" .$tpid ."\">Comments</a></td>";
// ----- User
echo "<td width=\"100\">User: " .$suser ;
if ($sadmin == 1){
  echo ", admin";
} 
echo "<br><a href=\"../index.php?LOGOUT=1\">abmelden</a></td></tr>";
echo "</table></div>";

// -------------------------------------------------------
// Haupauswertung
// ------------------------------------------------------- 
if ($slogin == 1){
  // wochenlinks
  echo "<div class=\"containerdates\">";
  echo "<table width=\"740\"><tr>";
  //echo "<td width=\"110\"><a href=\"" .$db_table ."view.php?WW=1&TIME=" .$letztewoche ."&PID=" .$tpid ."\"> " .date('d.m.Y',$letztewoche) ."</a></td>";
  //echo "<td width=\"500\" align=\"center\"><a href=\"" .$db_table ."view.php?WW=1&TIME=" .time() ."&PID=" .$tpid ."\">" .date('d.m.Y',time()) ." Heute</a></td>";
  //echo "<td width=\"110\"><a href=\"" .$db_table ."view.php?WW=1&TIME=" .$naechstewoche ."&PID=" .$tpid  ."\"> " .date('d.m.Y',$naechstewoche) ."</a></td>";
  echo "<td width=\"100\"><a href=\"" .$db_table ."month.php?TIME=" .$letztermonat ."&PID=" .$tpid ."\">" .date('m.Y',$letztermonat) ."</a></td>";
  
  echo "<td width=\"200\" align=\"center\">"; 
  echo "<form name=\"Testform\" action=\"bftopassmonth.php\" method=\"get\">";
  echo "<input type=\"hidden\" name=\"WW\"value=\"1\">";
  echo "<input type=\"hidden\" name=\"PID\"value=\"" .$tpid  ."\">";
  echo "<input type=\"text\" name=\"dpdate\" value =\"".date('Y-m-d',$tstamp) ."\"size=\"11\" id=\"datepicker\" onChange=\"this.form.submit()\"/>&nbsp;";
  //echo "<input type=\"submit\" value=\"xxx\">";
  echo "</form></td>";
  
  if ($ww==1){
    $xx = $tstamp2;
  } else 
  {
    $xx = $lw;
  }
  echo "<td width=\"200\" align=\"center\"><a href=\"" .$db_table ."view.php?WW=1&TIME=" .$xx ."&PID=" .$tpid ."\">" .date('d.m.Y',$xx-604799) ." - <br>".date('d.m.Y',$tstamp2)."</a></td>";
  echo "<td width=\"200\" align=\"center\"><a href=\"" .$db_table ."view.php?WW=1&TIME=" .time() ."&PID=" .$tpid ."\">" .date('d.m.Y',time()) ."<br>Today</a></td>";
  //echo "<td width=\"200\" align=\"center\"><a href=\"" .$db_table ."month.php?TIME=" .time() ."&PID=" .$tpid ."\">" .date('m.Y',time()) ."<br>Month</a></td>";
  echo "<td width=\"100\"><a href=\"" .$db_table ."month.php?WW=1&TIME=" .$naechstermonat ."&PID=" .$tpid  ."\">" .date('m.Y',$naechstermonat)  ."</a></td>";
  echo "</tr></table><br>";

  // tageslinks
  /*---------------------------------------------------------------------
  $eintagvor = "href=\"" .$db_table ."view.php?WW=1&TIME=" .($tstamp - 86400) ."&PID=" .$tpid ."\"";
  $eintagweiter = "href=\"" .$db_table ."view.php?WW=1&TIME=" .($tstamp + 86400) ."&PID=" .$tpid ."\"";

  echo "<table width=\"780\"><tr>";
  echo "<td width=\"20\"><a ".$eintagvor ."> <</a><br></td>";
  for ($i = 0; $i < 7; $i++){
    $tmpstamp = $fdow + ($i * 86400);
    echo "<td width=\"100\"><a href=\"" .$db_table ."view.php?WW=0&TIME=" .$tmpstamp ."&LW=" .$tstamp2 ."&PID=" .$tpid ."\">" .date('d.m.',$tmpstamp) ."</a><br>";
    $sun_info = date_sun_info($tmpstamp, $swgs84b, $swgs84l);
    echo "&uarr; ".date("H:i",$sun_info[sunrise])."</br>";
    echo "&darr; ".date("H:i",$sun_info[sunset])."</br>";
    echo "</td>";
  }
  echo "<td width=\"20\"><a ".$eintagweiter ."> <</a><br></td>";
  echo "</tr></table></div>"; 
--------------------------------------------------------------*/

  echo "<div class=\"containertable\">";
  if ($DxEN[1] == "1"){
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=D1\" usemap=\"#bildan\">";
    echo "<br><br>";   
  }
  if ($DxEN[2] == "1"){
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=D2\" usemap=\"#bildan\">";
    echo "<br><br>";   
  }
  if ($DxEN[3] == "1"){
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=D3\" usemap=\"#bildan\">";
    echo "<br><br>";   
  }
  if ($DxEN[4] == "1"){
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=D4\" usemap=\"#bildan\">";
    echo "<br><br>";   
  }

  if ($AN1EN == "1"){
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=A1\" usemap=\"#bildan\">";
  }
  if ($AN2EN == "1"){
    echo "<br><br>";    
    echo "<img src=\"" .$db_table ."mbild.php?KW=" .$ww ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=A2\" usemap=\"#bildan\">";
  }

  echo "<map name=\"bildan\">";
  echo "<area shape=\"rect\" coords=\"0,0,100,225\"" .$eintagvor ." alt=\"vor\"> ";
  echo "<area shape=\"rect\" coords=\"640,0,740,225\"" .$eintagweiter ." alt=\"weiter\"> ";
  echo "</map>";
  echo "</div>";



// -------------------------------------------------------
// Comment 
// -------------------------------------------------------  

  echo "<div class=\"containerstatus\">";
  echo "<table class=\"chtab\" width=\"740\">";
  echo "<tr><td width=\"200\">Comments</td><td width=\"200\">&nbsp;</td><td width=\"200\">&nbsp;</td></tr>";
  $merk = $tstamp1;
  $m = 0;
  $frage = "SELECT * FROM bfcomment WHERE ((ZEIT >$tstamp1) && (ZEIT<$tstamp2) && (PUSER = '$spuser')) ORDER BY ZEIT ASC;";
  //$ergebnis = $mysqli->query($frage);

  if(!$ergebnis = $mysqli->query($frage)){
  echo 'Fehler('.$mysqli->errno.'):'.$mysqli->error;
  } 
  else {
    while($zeile = $ergebnis->fetch_array()){
      echo "<tr>";
      echo "<td>" .date('d.m.Y H:i:s',$zeile['ZEIT']) ."</td>";
      echo "<td>" .$zeile['COMMENT'] ."</td>";
      echo "<tr>";
    }
  }
  echo "</table></div>";  
  $ergebnis->close();



// -------------------------------------------------------
// Datentabelle
// ------------------------------------------------------- 
  //$frage = "SELECT * FROM bftopass WHERE PUSER = '$spuser' ORDER BY ZEIT DESC LIMIT 50;";
  $frage = "SELECT * FROM bftopass WHERE ((ZEIT >$tstamp1) && (ZEIT<$tstamp2) && (PUSER = '$spuser')) ORDER BY ZEIT ASC;";
  $ergebnis = $mysqli->query($frage);
  echo "<div class=\"containertable\"><dl>";
  echo "<dt><a href=\"#\">Aktuelle Daten</a></dt>";
  echo "<dd><table class=\"dtab\">";
	echo "<tr><td>ID<TD>USER<TD>Server Zeit<TD>Remote TS<TD>D1<TD>D2<TD>D3<TD>D4<TD>A1<TD>A2<TD>dt[min]" ;
	$xold = 0;
	while($zeile = $ergebnis->fetch_array()){
	  if ($xold != 0){
  		$x = ((int)(intval($zeile['ZEIT']))-$xold)/60;   
  		echo "<td>".round($x,1) ."</td>";
    }   
  	$xold = (int)(intval($zeile['ZEIT']));
		echo "</tr>";
		echo "<tr><td>".htmlspecialchars($zeile['ID'])."</td>";
		echo "<td>".htmlspecialchars($zeile['PUSER'])."</td>";
		echo "<td>".htmlspecialchars(date('d.m.Y H:i:s',$zeile['ZEIT']))."</td>";
		//echo "<td>".htmlspecialchars($zeile['ZEIT'])."</td>";
		echo "<td>".htmlspecialchars($zeile['RTS'])."</td>";
		echo "<td>".intval($zeile['D1'])."</td>";
		echo "<td>".intval($zeile['D2'])."</td>";
		echo "<td>".intval($zeile['D3'])."</td>";
		echo "<td>".intval($zeile['D4'])."</td>";
		echo "<td>".floatval($zeile['A1'])."</td>";
		echo "<td>".floatval($zeile['A2'])."</td>";
    if ($sadmin == 1){
      echo "<td><a href=\"../edit/comment.php?ADD=1&TIME=" .$zeile['ZEIT'] ."&PID=" .$tpid ."\">+</a><br></td>";
    }

	}
	echo "<td></tr></table></dd></div>";
  $ergebnis->close();
}
else {
   echo "<br><a href=\"../index.php\">Bitte Anmelden</a><br>";
}
$mysqli->close();

echo "<br>";
echo "<table border='0' cellpadding='3' cellspacing='0' bgcolor='#DDDDDD' width='740'><tr><td>";
echo "status generated on: " .date('y/m/d,H:i:s') ."<br>";
echo "remote ip: " .$tip ."<br>";
echo "(c) 2012 <a href=\"http://www.lanthan.eu\">Lanthan GmbH & Co. KG </a>";
echo "</td></tr></table>";
?>
</body></html>