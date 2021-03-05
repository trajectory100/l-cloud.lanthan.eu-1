<?PHP
  session_start();
  header ("Content-type: image/png");
  
$tpid =  intval(htmlspecialchars($_REQUEST['PID'])); 
// -------------------------------------------------------
// statusabfrage
// -------------------------------------------------------
require_once "base.php";

$cr = '\r\n';

$outdetail = (htmlspecialchars($_REQUEST['DETAIL']) == '1');
$tpid =  intval(htmlspecialchars($_REQUEST['PID']));

// $mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
// if($mysqli->connect_error){
// 	echo "Fehler bei der Verbindung: " .mysqli_connect_error();
// 	exit();
// }

$alert = 0;
$bferror = 0;
$bftimeout = 0;


//$projekte = $mysqli->query("SELECT * FROM bfprojekt WHERE AKTIV = 1 AND ID = '$tpid';");
$projekte = $mysqli->query("SELECT * FROM bfprojektini WHERE ID = '$tpid';");
while($zeile = $projekte->fetch_array()){
	$puser = $zeile['PUSER'];
	$db = $zeile['DB'];
	$paktiv = $zeile['AKTIV'];
}
$projekte->close();


if ($paktiv == "1"){
	//timeoutzeit holen
	$frage = "SELECT * FROM bftopassini WHERE PUSER = '$puser';";  
	$projektini = $mysqli->query($frage);
	while($zeile2 = $projektini->fetch_array()){
		$interval = $zeile2['INTERVAL'];
		$D1Alert = $zeile2['DI1ALERT'];
    $D2Alert = $zeile2['DI2ALERT'];
    $D3Alert = $zeile2['DI3ALERT'];
    $D4Alert = $zeile2['DI4ALERT'];
    $D1Alval = $zeile2['DI1ALVAL'];
    $D2Alval = $zeile2['DI2ALVAL'];
    $D3Alval = $zeile2['DI3ALVAL'];
    $D4Alval = $zeile2['DI4ALVAL'];	
	$D11Alval = $zeile2['DI11ALVAL'];
    $D12Alval = $zeile2['DI12ALVAL'];	
    $AN1EN = $zeile2['AN1EN'];
    $AN2EN = $zeile2['AN2EN'];	    
    $AN1AL1EN = $zeile2['AN1AL1EN'];      
    $AN1AL2EN = $zeile2['AN1AL2EN'];      
    $AN1AL1LVL = floatval($zeile2['AN1AL1LVL']);
    $AN1AL2LVL = floatval($zeile2['AN1AL2LVL']);
    $AN1SCALE = floatval($zeile2['AN1SCALE']);
	}
	$projektini->close();

	// timeout bestimmen
	$bftimeout = 0;
	$bferror = 0;
	$frage = "SELECT * FROM $db WHERE PUSER = '$puser' ORDER BY ZEIT DESC LIMIT 1;";  
	$projektdata = $mysqli->query($frage);
	while($zeile2 = $projektdata->fetch_array()){
		$datazeit = $zeile2['ZEIT'];
		$datauser = $zeile2['PUSER'];
		$deltaT = (int)((time() - $datazeit) / 60);
		if ($deltaT > (int)($interval * 1.1)){
			$bftimeout = 1;
		}
		if ($D1Alert == '1'){
			if ($D1Alval == (int)$zeile2['D1']){
				$bferror = 1;
			}
		}		
		if ($D2Alert == '1'){
			if ($D2Alval == (int)$zeile2['D2']){
				$bferror = 1;
			}
		}		
		if ($D3Alert == '1'){
			if ($D3Alval == (int)$zeile2['D3']){
				$bferror = 1;
			}
		}		
		if ($D4Alert == '1'){
			if ($D4Alval == (int)$zeile2['D4']){
				$bferror = 1;
			}
		}
		//added
		if ($D5Alert == '1'){
			if ($D5Alval == (int)$zeile2['D5']){
				$bferror = 1;
			}
		}
		if ($D11Alert == '1'){
			if ($D11Alval == (int)$zeile2['D11']){
				$bferror = 1;
			}
		}
		if ($D12Alert == '1'){
			if ($D12Alval == (int)$zeile2['D12']){
				$bferror = 1;
			}
		}
		if ($D13Alert == '1'){
			if ($D13Alval == (int)$zeile2['D13']){
				$bferror = 1;
			}
		}
		if ($D14Alert == '1'){
			if ($D14Alval == (int)$zeile2['D14']){
				$bferror = 1;
			}
		}
		if ($D15Alert == '1'){
			if ($D15Alval == (int)$zeile2['D15']){
				$bferror = 1;
			}
		}
		if ($D16Alert == '1'){
			if ($D16Alval == (int)$zeile2['D16']){
				$bferror = 1;
			}
		}
		
		
		if ($AN1EN == '1'){
			//echo $AN1EN ." " .$AN1SCALE ." " .$AN1AL1LVL;
			if($AN1AL1EN =='1'){
				if ((floatval($zeile2['A1']) * $AN1SCALE) < $AN1AL1LVL){
					$bferror = 1;
				}
			}			
		}
    
    if ($AN2EN == '1'){
			//echo $AN2EN ." " .$AN2SCALE ." " .$AN2AL1LVL;
			if($AN2AL1EN =='1'){
				if ((floatval($zeile2['A2']) * $AN2SCALE) < $AN2AL1LVL){
					$bferror = 1;
				}
			}			
		}
    
    if ($AN9EN == '1'){
			//echo $AN9EN ." " .$AN9SCALE ." " .$AN9AL1LVL;
			if($AN9AL1EN =='1'){
				if ((floatval($zeile2['A9']) * $AN9SCALE) < $AN9AL1LVL){
					$bferror = 1;
				}
			}			
		}

	}
	$projektdata->close();
}

$mysqli->close();


// -------------------------------------------------------
// Bildaufbau
// -------------------------------
$bildh = 16;
//$bildw = 3*$bildh+10; // bild breite
$bildw = 3*$bildh+10; // bild breite

$bild = imagecreatetruecolor($bildw, $bildh);
$gelb = imagecolorallocate($bild, 255, 235, 0);
$we = imagecolorallocate($bild, 255, 255, 255); 
$gr1 = imagecolorallocate($bild, 200, 200, 200);
$gr2 = imagecolorallocate($bild, 100, 100, 100);
$rot = imagecolorallocate($bild, 255, 0, 0);
$gruen = imagecolorallocate($bild, 0, 255, 0);
$blau = imagecolorallocate($bild, 0, 0, 255);
$sw = imagecolorallocate($bild, 0, 0, 0);
$noakt = imagecolorallocate($bild, 222, 222, 222);

imagefilledrectangle($bild, 0, 0, $bildw, $bildh, $we);

if ($paktiv == "1"){
	if ($bftimeout == 1){
		imagefilledellipse($bild, $bildw/2, $bildh/2, $bildh*0.9, $bildh*0.9 , $gelb);
	} 
	else {
		imagefilledellipse($bild, $bildw/2, $bildh/2, $bildh*0.9, $bildh*0.9 , $gr1);
	}

	if ($bferror == 1){
		imagefilledellipse($bild, $bildw/2-($bildh)-5, $bildh/2, $bildh*0.9, $bildh*0.9 , $rot); 
		imagefilledellipse($bild, $bildw/2+($bildh)+5, $bildh/2, $bildh*0.9, $bildh*0.9 , $gr1);
	} 
	else {
		imagefilledellipse($bild, $bildw/2+($bildh)+5, $bildh/2, $bildh*0.9, $bildh*0.9 , $gruen);
		imagefilledellipse($bild, $bildw/2-($bildh)-5, $bildh/2, $bildh*0.9, $bildh*0.9 , $gr1); 
	}	
} 
else {
	imagefilledellipse($bild, $bildw/2-($bildh)-5, $bildh/2, $bildh*0.9, $bildh*0.9 , $noakt); 
	imagefilledellipse($bild, $bildw/2, $bildh/2, $bildh*0.9, $bildh*0.9 , $noakt);
	imagefilledellipse($bild, $bildw/2+($bildh)+5, $bildh/2, $bildh*0.9, $bildh*0.9 , $noakt);	
}


$text = $tpid ." " .$puser ." " .$paktiv ." " .$bferror ." " .$bftimeout;
//imagestring($bild, 25, 3, 3, $text, $gr2);

header("Content-type: image/png");
imagepng($bild);
imagedestroy($bild);
?>
