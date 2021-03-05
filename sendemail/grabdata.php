<?php
require_once('db.php');


echo "    ==>>Devices With New Firmware<==    "."<br>"."<br>";
$sql = 'SELECT  bfccio.imei , bfccio.ver , bfccio.pos, bfccio.time, bfccio.text
FROM bfccio WHERE bfccio.ver = "f190328h03" AND ((bfccio.text = "CCIOReset" OR bfccio.text = "Event") AND bfccio.pos NOT LIKE "0.5")
GROUP BY imei ORDER BY bfccio.time DESC';
$result = $mysqli->query($sql);
if ($result->num_rows > 0) {
// output data of each row
while ($row = $result->fetch_assoc()) {
        
        $imei = $row['imei'];
        $ver = $row['ver'];
        $datee = $row['pos'];
        $ti = $row['time'];
        echo "<br>   imei: ". $row["imei"]. " ,  new firmware : ". $row["ver"] . ' ,  on '  . date('j.n.Y H:i', $ti) ."<br>";
        // echo "<br> imei: ". $row["imei"]. " , new firmware: ". $row["ver"] . '  at '. substr($datee, 0,23) ."<br>";
        // echo "<br> imei: ". $row["imei"]. " , new firmware: ". $row["ver"] . '  at '. substr($datee, 0,23) ."<br>";

}
}else {
echo "1"; 

}

// // echo "<br>"."<br>";
// // echo "Devices With Old Firmware "."<br>"."<br>";

// // $sql2 = 'SELECT bfccio.imei , bfccio.ver , bfccio.pos, bfccio.time, bfccio.text
// // FROM bfccio WHERE bfccio.ver != "f190328h03" AND (bfccio.text = "CCIOReset" OR bfccio.text = "Event")
// // GROUP BY imei HAVING bfccio.ver != "f190328h03";';

// $program =  'SELECT bfccio.imei, bfccio.ver, bfccio.ID, bfccio.time FROM bfccio ORDER BY ID GROUP BY imei  ';
// // WHERE bfccio.ver != 'f190328h03' GROUP BY ID 
// $resulte = $mysqli->query($program);
// if ($resulte->num_rows > 0) {
// // output data of each row
// while ($rowt= $resulte->fetch_assoc()) {
//             $imeii = $rowt['imei'];
//             $veri = $row['ver'];
//             $tiim = $rowt['time'];

//         echo "<br> imei: ". $rowt['imei']. " ,  old firmware : ". $rowt["ver"] . '  at '  . date('j.n.Y', $tiim) ."<br>";
//         // echo "<br> imei: ". $row["imei"]. " - Name: ". $row["ver"] . '  at '. substr($datee, 0,23). ' timemee' . date('j.n.Y', $ti) ."<br>";

// }
// }else {
// echo "1"; 

// }
// var_dump($resulte);