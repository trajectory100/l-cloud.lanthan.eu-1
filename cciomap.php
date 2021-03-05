<?PHP
  session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <title>gmaps.js &mdash; the easiest way to use Google Maps</title>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCOqe1VKk8To_77ZkRtu4wcUY1Su21Qu-4"  ></script>
  <script type="text/javascript" src="css/gmaps.js"></script>
  <link href='//fonts.googleapis.com/css?family=Convergence|Bitter|Droid+Sans|Ubuntu+Mono' rel='stylesheet' type='text/css' />

  <link rel="stylesheet" type="text/css" href="css/map.css" />

  
<?PHP
$db_server = 'rdbms.strato.de';
$db_benutzer = 'U969575';
$db_passwort = 'MySqL89dB3';
$db_name = 'DB969575';
$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if($mysqli->connect_error){
  echo "Fehler bei der Verbindung: " .mysqli_connect_error();
  exit();
}

?>  
  <script type="text/javascript">
    var map;
    $(document).ready(function(){
      map = new GMaps({
        div: '#map',
        lat: 53.043333,
        lng: 8.9,
        zoom: 4
      });
   
 <?PHP   
  
  $frageimei = "SELECT DISTINCT imei FROM bfccio ORDER BY imei DESC;"; 
  $ergebnisimei = $mysqli->query($frageimei);
  while($zeile = $ergebnisimei->fetch_array())
  {
    $merker = htmlspecialchars($zeile['imei']);   
    $frage = "SELECT * FROM bfccio WHERE imei = '$merker' ORDER BY time DESC LIMIT 1" ;
     $ergebnis = $mysqli->query($frage);
 
    while($row = $ergebnis->fetch_array())
    {
      $sort =  htmlspecialchars($row['imei']).";   ";
      $teile = explode(",", htmlspecialchars($row['pos']));
      $wgs84b = $teile[2];
      $wgs84l = $teile[3];
      
      //$serrX = floatval($serr2) + floatval($serr1)*2;
      $serrX = 3;
      $pcount = $pcount + 1;
      
      echo "map.addMarker({";
      echo "lat: " .floatval($wgs84b) .",";
      echo "lng: " .floatval($wgs84l) .",";
      echo "title: '" .$sort ."',";
      echo "icon: '../image/x" .$serrX .".png',";
      
      echo "infoWindow: {";
      echo "content: '<p>" .$sort  .$serrX ."</p>'";
      echo "}";  
      echo "});";       
      
    }

    $ergebnis->close();
  }
  $ergebnisimei->close();
  $mysqli->close();   


?>

    });
  </script>

  
</head>
<body>
  <div id="map"></div>
</body>
</html>
