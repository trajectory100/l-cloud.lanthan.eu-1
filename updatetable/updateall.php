<?PHP
// // ------------------------------------------------------------------------------------------
// //                 Update the table  
// // ------------------------------------------------------------------------------------------
// // --------- Connect  to the server -----------------------------------
require_once('db.php');

// --------- Get customer information from Obstacle/number of obstacle ligths-----------------------------------
$projini = $mysqli->query("SELECT * FROM Obstacle");
while ($zeile = $projini->fetch_array()) {
    $puser = $zeile['PUSER'];
    $obstnum = (int)$zeile['Obstacles'];

    // --------- update process-----------------------------------
    $pr = $mysqli->query("SELECT * FROM bfprojektini WHERE PUSER= '$puser' ");
    while ($row = $pr->fetch_array()) {
        $pus = $row['PUSER'];

        $sql = $mysqli->query("UPDATE bfprojektini SET AN9NAME = '24V Supply', AN9EN = '1', AN9ISDIGITAL = '0' , AN9MIN =  '20' , AN9MAX = '30', AN10NAME= 'System Current' ,AN10EN = '1', AN10MAX= '0.5', AN10SCALE= '1', AN12NAME= 'Temperature' , AN12EN='1', AN12MIN= '-20', AN12MAX= '40' WHERE PUSER =". $puser);
        $sql4 = $mysqli->query("UPDATE bfprojektini SET DI11NAME = 'Twilight Switch Error', DI11EN = '1', DI11L = 'Fail' , DI11H =  'OK' , DI11ALERT = '1' , DI11ALVAL= '0',DI11NOE='0' , DI12NAME = 'UPS Replace Battery', DI12EN = '1', DI12L = 'OK' , DI12H =  'Battery Fail' , DI12ALERT = '1' , DI12ALVAL= '1',DI12NOE='0' WHERE PUSER =" .  $puser);
        $sql5 = $mysqli->query("UPDATE bfprojektini SET DI13NAME = 'UPS Buffering', DI13EN = '1', DI13L = 'OK' , DI13H =  'Buffering' , DI13ALERT = '1' , DI13ALVAL= '1',DI13NOE='0' , DI14NAME = 'UPS Ready', DI14EN = '1', DI14L = 'Charge / Fail' , DI14H =  'Ready' , DI14ALERT = '1' , DI14ALVAL= '0',DI14NOE='0' WHERE PUSER =" .  $puser);
        $sql6 = $mysqli->query("UPDATE bfprojektini SET DI15NAME = 'Surge Protection', DI15EN = '1', DI15L = 'Fail' , DI15H =  'OK' , DI15ALERT = '1' , DI15ALVAL= '0', DI15NOE='0' , DI16NAME = 'Door', DI16EN = '1', DI16L = 'Open' , DI16H =  'Closed' , DI16ALERT = '1' , DI16ALVAL= '0',DI16NOE='0' WHERE PUSER =" .  $puser);
        $sql7 = $mysqli->query("UPDATE bfprojektini SET  DI17EN = '0', DI17ALERT = '0'  WHERE PUSER =" .  $puser);

        switch ($obstnum) {
            case 1:
                $sql1 = $mysqli->query("UPDATE bfprojektini SET DI1NAME = 'Twilight switch', DI1EN = '1', DI1L = 'Day' , DI1H =  'Night' , DI1ALERT = '0' , DI1ALVAL= '1',DI1NOE='0' , DI2NAME = 'Obstacle Light 1', DI2EN = '1', DI2L = 'Fail' , DI2H =  'OK' , DI2ALERT = '1' , DI2ALVAL= '0',DI2NOE='0' WHERE PUSER =" .  $puser);
                $sql2 = $mysqli->query("UPDATE bfprojektini SET DI3NAME = 'Obstacle Light 2', DI3EN = '0', DI3L = 'Fail' , DI3H =  'OK' , DI3ALERT = '0' , DI3ALVAL= '0',DI3NOE='0' , DI4NAME = 'Obstacle Light 3', DI4EN = '0', DI4L = 'Fail' , DI4H =  'OK' , DI4ALERT = '0' , DI4ALVAL= '0',DI4NOE='0' WHERE PUSER =" .  $puser);
                $sql3 = $mysqli->query("UPDATE bfprojektini SET DI5NAME = 'Obstacle Light 4', DI5EN = '0', DI5L = 'Fail' , DI5H =  'OK' , DI5ALERT = '0' , DI5ALVAL= '0',DI5NOE='0' , DI6NAME = 'Obstacle Light 5', DI6EN = '0', DI6L = 'Fail' , DI6H =  'OK' , DI6ALERT = '0' , DI6ALVAL= '0',DI6NOE='0' WHERE PUSER =" .  $puser);
               
            break;
            case 2:
                $sql1 = $mysqli->query("UPDATE bfprojektini SET DI1NAME = 'Twilight switch', DI1EN = '1', DI1L = 'Day' , DI1H =  'Night' , DI1ALERT = '0' , DI1ALVAL= '1',DI1NOE='0' , DI2NAME = 'Obstacle Light 1', DI2EN = '1', DI2L = 'Fail' , DI2H =  'OK' , DI2ALERT = '1' , DI2ALVAL= '0',DI2NOE='0' WHERE PUSER =" .  $puser);
                $sql2 = $mysqli->query("UPDATE bfprojektini SET DI3NAME = 'Obstacle Light 2', DI3EN = '1', DI3L = 'Fail' , DI3H =  'OK' , DI3ALERT = '1' , DI3ALVAL= '0',DI3NOE='0' , DI4NAME = 'Obstacle Light 3', DI4EN = '0', DI4L = 'Fail' , DI4H =  'OK' , DI4ALERT = '0' , DI4ALVAL= '0',DI4NOE='0' WHERE PUSER =" .  $puser);
                $sql3 = $mysqli->query("UPDATE bfprojektini SET DI5NAME = 'Obstacle Light 4', DI5EN = '0', DI5L = 'Fail' , DI5H =  'OK' , DI5ALERT = '0' , DI5ALVAL= '0',DI5NOE='0' , DI6NAME = 'Obstacle Light 5', DI6EN = '0', DI6L = 'Fail' , DI6H =  'OK' , DI6ALERT = '0' , DI6ALVAL= '0',DI6NOE='0' WHERE PUSER =" .  $puser);
               
              
                break;
            case 3:
                $sql1 = $mysqli->query("UPDATE bfprojektini SET DI1NAME = 'Twilight switch', DI1EN = '1', DI1L = 'Day' , DI1H =  'Night' , DI1ALERT = '0' , DI1ALVAL= '1',DI1NOE='0' , DI2NAME = 'Obstacle Light 1', DI2EN = '1', DI2L = 'Fail' , DI2H =  'OK' , DI2ALERT = '1' , DI2ALVAL= '0',DI2NOE='0' WHERE PUSER =" .  $puser);
                $sql2 = $mysqli->query("UPDATE bfprojektini SET DI3NAME = 'Obstacle Light 2', DI3EN = '1', DI3L = 'Fail' , DI3H =  'OK' , DI3ALERT = '1' , DI3ALVAL= '0',DI3NOE='0' , DI4NAME = 'Obstacle Light 3', DI4EN = '1', DI4L = 'Fail' , DI4H =  'OK' , DI4ALERT = '1' , DI4ALVAL= '0',DI4NOE='0' WHERE PUSER =" .  $puser);
                $sql3 = $mysqli->query("UPDATE bfprojektini SET DI5NAME = 'Obstacle Light 4', DI5EN = '0', DI5L = 'Fail' , DI5H =  'OK' , DI5ALERT = '0' , DI5ALVAL= '0',DI5NOE='0' , DI6NAME = 'Obstacle Light 5', DI6EN = '0', DI6L = 'Fail' , DI6H =  'OK' , DI6ALERT = '0' , DI6ALVAL= '0',DI6NOE='0' WHERE PUSER =" .  $puser);
               
                break;
            case 4:
                $sql1 = $mysqli->query("UPDATE bfprojektini SET DI1NAME = 'Twilight switch', DI1EN = '1', DI1L = 'Day' , DI1H =  'Night' , DI1ALERT = '0' , DI1ALVAL= '1',DI1NOE='0' , DI2NAME = 'Obstacle Light 1', DI2EN = '1', DI2L = 'Fail' , DI2H =  'OK' , DI2ALERT = '1' , DI2ALVAL= '0',DI2NOE='0' WHERE PUSER =" .  $puser);
                $sql2 = $mysqli->query("UPDATE bfprojektini SET DI3NAME = 'Obstacle Light 2', DI3EN = '1', DI3L = 'Fail' , DI3H =  'OK' , DI3ALERT = '1' , DI3ALVAL= '0',DI3NOE='0' , DI4NAME = 'Obstacle Light 3', DI4EN = '1', DI4L = 'Fail' , DI4H =  'OK' , DI4ALERT = '1' , DI4ALVAL= '0',DI4NOE='0' WHERE PUSER =" .  $puser);
                $sql3 = $mysqli->query("UPDATE bfprojektini SET DI5NAME = 'Obstacle Light 4', DI5EN = '1', DI5L = 'Fail' , DI5H =  'OK' , DI5ALERT = '1' , DI5ALVAL= '0',DI5NOE='0' , DI6NAME = 'Obstacle Light 5', DI6EN = '0', DI6L = 'Fail' , DI6H =  'OK' , DI6ALERT = '0' , DI6ALVAL= '0',DI6NOE='0' WHERE PUSER =" .  $puser);
               
                break;
            case 5:
                $sql1 = $mysqli->query("UPDATE bfprojektini SET DI1NAME = 'Twilight switch', DI1EN = '1', DI1L = 'Day' , DI1H =  'Night' , DI1ALERT = '0' , DI1ALVAL= '1',DI1NOE='0' , DI2NAME = 'Obstacle Light 1', DI2EN = '1', DI2L = 'Fail' , DI2H =  'OK' , DI2ALERT = '1' , DI2ALVAL= '0',DI2NOE='0' WHERE PUSER =" .  $puser);
                $sql2 = $mysqli->query("UPDATE bfprojektini SET DI3NAME = 'Obstacle Light 2', DI3EN = '1', DI3L = 'Fail' , DI3H =  'OK' , DI3ALERT = '1' , DI3ALVAL= '0',DI3NOE='0' , DI4NAME = 'Obstacle Light 3', DI4EN = '1', DI4L = 'Fail' , DI4H =  'OK' , DI4ALERT = '1' , DI4ALVAL= '0',DI4NOE='0' WHERE PUSER =" .  $puser);
                $sql3 = $mysqli->query("UPDATE bfprojektini SET DI5NAME = 'Obstacle Light 4', DI5EN = '1', DI5L = 'Fail' , DI5H =  'OK' , DI5ALERT = '1' , DI5ALVAL= '0',DI5NOE='0' , DI6NAME = 'Obstacle Light 5', DI6EN = '1', DI6L = 'Fail' , DI6H =  'OK' , DI6ALERT = '1' , DI6ALVAL= '0',DI6NOE='0' WHERE PUSER =" .  $puser);
               
                break;
            default:
            break;
        }
    }
}
