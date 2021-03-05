<?php
require_once('db.php');


$rows = 0;
if (($handle = \fopen('data.csv', 'r')) !== false) {
    while (($data = \fgetcsv($handle, 1000, ',')) !== false) {
        $rows++;
        if ($rows === 1) {
            continue;
        }

        $imei = $data[16];
        //$result = $mysqli->query('SELECT ini.* FROM bfccio AS bfc LEFT JOIN bfprojektini AS ini ON (bfc.ID = ini.ID) WHERE bfc.imei = "' . mysqli_real_escape_string($mysqli, $imei) . '"');
        $result = $mysqli->query('SELECT ID FROM bfccio WHERE imei = "' . mysqli_real_escape_string($mysqli, $imei) . '" LIMIT 1');

        while ($row = $result->fetch_array()) {
            $iniResult = $mysqli->query('SELECT * FROM bfprojektini WHERE ID = "' . mysqli_real_escape_string($mysqli, $row['ID']) . '" LIMIT 1');
            var_dump('Trying to get bfccio ');
            while ($row2 = $iniResult->fetch_array()) {
                var_dump($row2);
            }
            // - now we're goingt to get related data by deviceId
            //var_dump('Loading ' . $imei . ' ' . $row['ID']);
        }


        //var_dump($imei);
    }
}
