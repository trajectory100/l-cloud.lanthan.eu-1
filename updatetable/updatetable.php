<?php
require_once('db.php');

$rows = 0;
if (($handle = \fopen('data.csv', 'r')) !== false) {
    while (($data = \fgetcsv($handle, 1000, ',')) !== false) {
        $rows++;
        if ($rows === 1) {
            continue;
        }

        $customerId = $data[0];
        $imei = $data[16];
        $WGS84B = $data[6];
        $WGS84L = $data[8];
        $street = $data[5];
        $country = $data[10];

        $plz = $data[11];
        $city = $data[12];
        $number = $data[13];
        $state = $data[14];

        $fire = $data[4];
        $imei_neu = $data[19];
        $serialnumber = $data[18];

        $ort1 = $plz . ' ' . $city;
        $ort2 = $customerId . ' / ' . $street . ' ' . $number;

        $dl1en = 0;
        $dl2en = 0;
        $dl3en = 0;
        $dl4en = 0;
        $dl5en = 0;

        switch ($fire) {
            case 1:
                $dl1en = 1;
                break;
            case 2:
                $dl1en = 1;
                $dl2en = 1;
                break;
            case 3:
                $dl1en = 1;
                $dl2en = 1;
                $dl3en = 1;
                break;
            case 4:
                $dl1en = 1;
                $dl2en = 1;
                $dl3en = 1;
                $dl4en = 1;
                break;
            case 5:
                $dl1en = 1;
                $dl2en = 1;
                $dl3en = 1;
                $dl4en = 1;
                $dl5en = 1;
                break;
            default:
                echo "Got " . $fire . " fires from " . $customerId . "<br>";
                break;
        }
        
        $sql = $mysqli->query('UPDATE bfprojektini SET WGS84B = '. $WGS84B . ', WGS84L = ' . $WGS84L . ', ORT = "' . $ort1 . '", ORT2 = "' . $ort2 . '", LAND = "' . $country . '", DI1EN = ' . $dl1en . ', DI2EN = ' . $dl2en . ', DI3EN = ' . $dl3en . ', DI4EN = ' . $dl4en . ', DI5EN = ' . $dl5en . ' WHERE PUSER = ' .  $imei);
        $sql3 = $mysqli->query('UPDATE bfprojektini SET PUSER = '. $imei_neu . ' WHERE WGS84B = ' .  $WGS84B);
        $sql4 = $mysqli->query('UPDATE bfprojektini SET TXSN = '.  $serialnumber  . ' WHERE WGS84B = ' .  $WGS84B);
        //..........................
        // $sql = "UPDATE bfprojektini SET WGS84B='51.14' WHERE PUSER= '359315076927911'";
        // if ($mysqli->query($sql) === true) {
        //     echo "Record updated successfully";
        // } else {
        //     echo "Error updating record: " . $mysqli->error;
        // }
        // $mysqli->close();
        // die;
        //.................................
        

    }
}
