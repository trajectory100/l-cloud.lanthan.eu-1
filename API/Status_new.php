<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

</head>



<?PHP
require_once('db.php');
// how to run the code as an example  " http://test.lanthan.eu/API/Status.php?ID=359315076932408  "

// \header('Access-Control-Allow-Origin: *');
// \header('Content-Type: application/json; charset=utf-8');
$pUser = $_GET['ID'] ?? null;

// if (\is_null($pUser) || $pUser === '') {
//     header('HTTP/1.0 403 Forbidden');
//     echo \json_encode(['e' => 'invalid ID given']);
// }

$query = 'SELECT bfprojektstatus2.`PUSER` as userID, bfprojektstatus2.*, bfprojektstatus2.`AKTIV` as Aktiv, bfprojektini.ORT2
    FROM bfprojektstatus2 
    LEFT JOIN bfprojektini ON (bfprojektstatus2.`ID` = bfprojektini.`ID`)
    WHERE bfprojektstatus2.`PUSER` = "' . mysqli_real_escape_string($mysqli, $pUser).'" OR bfprojektini.ORT2 LIKE "' . mysqli_real_escape_string($mysqli, $pUser).' %"';

    //  bfprojektstatus2.`PUSER` as userID, bfprojektstatus2.*, bfprojektstatus2.`AKTIV` as Aktiv
    // FROM bfprojektstatus2
    // WHERE (`ERROR` = 1 OR `TIMEOUT` = 1) AND PUSER = "' . mysqli_real_escape_string($mysqli, $pUser) . '"';

$result = $mysqli->query($query);

$data = [];
while ($row = $result->fetch_array()) {
    $data[] = [
        'userId' => $row['userID'],
        'deviceId' => (int)$row['ID'],
        'active' => !!$row['Aktiv'],
        'error' => !!$row['ERROR'],
        'timeout' => !!$row['TIMEOUT'],
        'User Number' => (int)$row['ORT2'],
    ];
}
print_r($data);
// echo \json_encode($data);
?>


<body style="background-color:#7bceeb;">
<!-- set the grading colorfor the background -->
<table height="100%" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
 <td  valign="top" align="center"  style= "background-image: linear-gradient( #000000 0%, #04619f 74%);"> 

<!-- style the text box in the middle  -->
 <style> 
      div.center {border: 2px solid  #ffff0f; background: white; margin: 20% 20%; padding:2% 2% ;text-align: justify;} 
      p {line-height: 80%;} 
      table {border: 1px solid black;} 
      th,td{ border: 1px solid black; padding: 5px;   border-collapse: collapse; line-height: 100%; box-sizing: border-box;} 
      tr#ROW1  {background-color:#000080; color:white;}
</style>


<!-- create the text box in the middle  -->
<div class="center">
<table style= "width=:'50%'; padding-left='8%';">
<tr  id="ROW1"><td colspan="2" >
<img src="http://test.lanthan.eu/image/lanthan_status_600x150.jpg" style=" width: 300px; height: 75px;padding: 8px; background-clip: content-box; box-shadow: inset 0 0 0 15px white;" alt="img"></td></tr>

<tr><td> User ID</td><td><? echo  $data[0]['userId'];?></td></tr>
<tr><td> User Number</td><td><? echo  $data[0]['ORT2'];?></td></tr>
<tr><td> Device ID</td><td><? echo  $data[0]['deviceId'];?></td></tr>
<tr><td> Status of the Device</td><td><? if( is_Null($data[0]['active']== 1)){echo "This device is active." ;}else{ echo "This device is inactive.";}  ?></td></tr>
<tr><td> Error</td><td><? if( is_Null($data[0]['error'])){echo "There is no error" ;}else{ echo "System has error";}  ?></td></tr>
<tr><td> Timeout</td><td><? if( is_Null($data[0]['TIMEOUT'])){echo "There is no timeout" ;}else{ echo "System has timeout";}   ?></td></tr>

</table>
</div>




<!-- //end part for the background with image                                         -->
</td></tr> 
</table> 





</body>
</html>
