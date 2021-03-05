<?PHP
$db_server = 'rdbms.strato.de';
$db_benutzer = 'U969575';
$db_passwort = 'MySqL89dB3';
$db_name = 'DB969575';
$cr = '\r\n';

$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if($mysqli->connect_error){
	echo "Fehler bei der Verbindung: " .mysqli_connect_error();
	exit();
}

//---------------------------------------------------------------------------
// Klärung der Funktion
//---------------------------------------------------------------------------
$job = "NONE";  
if (isset($_GET["PUSER"])){
   $jobuser =  htmlspecialchars($_REQUEST['PUSER']);   // projekt id
}
if (isset($_GET["JOB"])){
  $job =htmlspecialchars($_REQUEST['JOB']);
}


//---------------------------------------------------------------------------
// JOB = NONE
// Ausgabe aller Projekte order by ID ASC
//---------------------------------------------------------------------------
if ($job == 'NONE'){
  $alert = 0;
  $projekte = $mysqli->query("SELECT * FROM bfprojektstatus2 WHERE AKTIV = 1 ORDER BY ID ASC");
  while($zeile = $projekte->fetch_array()){
    $pid = $zeile['ID'];
    $puser = $zeile['PUSER'];
    $e1 = $zeile['ERROR'];
    $e2 = $zeile['TIMEOUT'];
    $e3 = $zeile['AKTIV'];
    
    if ($e1=='1') {
      if ($e2=='1'){
        $x=3;
      }
       else {
         $x=2;
       }
    } else {
       if ($e2=='1'){
        $x=1;
      }
       else {
         $x=0;
       }   
    }
    if ($e3=='0'){
      $x='-';
    }
    echo $x;
  }
  $projekte->close();
}

//---------------------------------------------------------------------------
// JOB = ST1
// Ausgabe aller Projekte order by ID ASC
//---------------------------------------------------------------------------
if ($job == 'ST1'){
  $alert = 0;
  $projekte = $mysqli->query("SELECT * FROM bfprojektstatus2 WHERE PUSER =  '$jobuser' ");
  while($zeile = $projekte->fetch_array()){
    $pid = $zeile['ID'];
    $puser = $zeile['PUSER'];
    $e1 = $zeile['ERROR'];
    $e2 = $zeile['TIMEOUT'];
    $e3 = $zeile['AKTIV'];
    
    if ($e1=='1') {
      if ($e2=='1'){
        $x=3;
      }
       else {
         $x=2;
       }
    } else {
       if ($e2=='1'){
        $x=1;
      }
       else {
         $x=0;
       }   
    }
    if ($e3=='0'){
      $x='-';
    }
    echo $x;
  }
  $projekte->close();
}


$mysqli->close();
?>
