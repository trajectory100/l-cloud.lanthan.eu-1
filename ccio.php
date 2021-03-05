<?PHP
  session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">
  <title>Lanthan  Cloud Monitoring</title>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <META HTTP-EQUIV=Refresh CONTENT="10"/>
  <link href="css/status.css" rel="stylesheet" type="text/css" />
  <link href="css/status2.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
  <script type="text/javascript">

</script> 
</head>
<body>

<?PHP
require_once('db.php');

//echo time();
$zeit = time() - 1000;

if (isset($_GET["JOB"])){
  $job =htmlspecialchars($_REQUEST['JOB']);
}
if (isset($_GET["IMEI"])){
  $job =htmlspecialchars($_REQUEST['JOB']);
}

echo "<h3> LANTHAN CCIO Database </h3>";
echo "<a href=\"http:\\\\status.lanthan.eu/ccio.php?JOB=LAST\">Last Data </a> / " ;
echo "<a href=\"http:\\\\status.lanthan.eu/ccio.php?JOB=IMEI\"> Imei sort</a>";
echo "<a a target=\"_blank\" rel=\"noopener noreferrer\" href=\"http:\\\\status.lanthan.eu/cciomap.php\"> Map</a><br>";



if ($job == "IMEI")
{
  // $frageimei = "SELECT DISTINCT imei FROM bfccio ORDER BY imei DESC;"; 
    $frageimei = "SELECT DISTINCT imei FROM test ORDER BY imei DESC;"; 

  $ergebnisimei = $mysqli->query($frageimei);
  while($zeile = $ergebnisimei->fetch_array())
  {
    $merker = htmlspecialchars($zeile['imei']);   
        $frage = "SELECT * FROM test WHERE imei = '$merker' ORDER BY time DESC LIMIT 1" ;

    // $frage = "SELECT * FROM bfccio WHERE imei = '$merker' ORDER BY time DESC LIMIT 1" ;
    //echo $frage;
    $ergebnis = $mysqli->query($frage);
    //echo "<br>\n\r";

    while($row = $ergebnis->fetch_array())
    {
          $puser= $row['imei']; // added
          $DX = $row['DX'];
      if ($row['time'] != "")
      {
        echo "<br>\n\r";
      }
      if (($row['time']) > (time() - 600))
      {
        echo "<font color=\"red\">";
      }
      //echo htmlspecialchars($row['ID'])."; ";
      echo htmlspecialchars(date('d.m.Y H:i:s',$row['time'])).";  ";
      echo htmlspecialchars($row['imei']).";   ";
      echo htmlspecialchars($row['iccid']).";   ";
      //echo htmlspecialchars($row['csq']).";   ";
      $teile = explode(",", htmlspecialchars($row['pos']));
      echo $teile[2] .";   " .$teile[3] .";   ";
      echo htmlspecialchars($row['ver']).";   ";
      echo htmlspecialchars($row['Dx']) .";   ";
      //echo htmlspecialchars($row['Ax']).";   ";
      $teile = explode(";", htmlspecialchars($row['Ax']));
      echo $teile[8] .";   " .$teile[9] .";   ";
      echo $teile[11] .";   ";
      echo htmlspecialchars($row['text']).";   <br>\n\r";
      echo "<font color=\"black\">";
    }
    $ergebnis->close();
    
  }
  $ergebnisimei->close();
  $mysqli->close();
  echo "<br><a href=\"http:\\\\status.lanthan.eu/ccio.php?JOB=A\">Reload</a><br>";
}

if ($job == "LAST")
{
  //echo "LAST";
  // $frage = "SELECT * FROM bfccio ORDER BY time DESC LIMIT 200;" ;
    $frage = "SELECT * FROM test ORDER BY time DESC LIMIT 200;" ;

  //echo $frage;
  $ergebnis = $mysqli->query($frage);
  //echo "<br>\n\r";

  while($row = $ergebnis->fetch_array())
  {
    if ($row['time'] != "")
    {
      echo "<br>\n\r";
    }
    if (($row['time']) > (time() - 600))
    {
      echo "<font color=\"red\">";
    }
    //echo htmlspecialchars($row['ID'])."; ";
    echo htmlspecialchars(date('d.m.Y H:i:s',$row['time'])).";  ";
    echo htmlspecialchars($row['imei']).";   ";
    echo htmlspecialchars($row['iccid']).";   ";
    //echo htmlspecialchars($row['csq']).";   ";
    $teile = explode(",", htmlspecialchars($row['pos']));
    echo $teile[2] .";   " .$teile[3] .";   ";
    echo htmlspecialchars($row['ver']).";   ";
    echo htmlspecialchars($row['Dx']) .";   ";
    //echo htmlspecialchars($row['Ax']).";   ";
    $teile = explode(";", htmlspecialchars($row['Ax']));
    echo $teile[8] .";   " .$teile[9] .";   ";
    echo $teile[11] .";   ";
    echo htmlspecialchars($row['text']).";   <br>\n\r";
    echo "<font color=\"black\">";
  }

  $ergebnis->close();

  $mysqli->close();
  echo "<br><a href=\"http:\\\\status.lanthan.eu/ccio.php?JOB=LAST\">Reload</a><br>";
}

?>

</body>
</html>
