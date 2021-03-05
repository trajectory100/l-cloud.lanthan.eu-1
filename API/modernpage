<?php
require_once('db.php');
// how to run the code as an example  " http://test.lanthan.eu/API/Status.php?ID=359315076932408  "


\header('Access-Control-Allow-Origin: *');
\header('Content-Type: application/json; charset=utf-8');
$pUser = $_GET['ID'] ?? null;

if (\is_null($pUser) || $pUser === '') {
    header('HTTP/1.0 403 Forbidden');
    echo \json_encode(['e' => 'invalid ID given']);
}

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

echo \json_encode($data);


