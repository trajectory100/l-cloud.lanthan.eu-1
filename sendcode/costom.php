<?php
require_once('db.php');

// $handle = fopen("f190328h03.hex", "rb");
// var_dump($handle);
///https://blog-en.openalfa.com/how-to-work-with-binary-data-in-php
//read the hex file 

$fp = fopen("f190328h03.hex","rb");
while (!feof($fp)) {
    // Read the file, in chunks of 16 byte
    $data = fread($fp,16);
    $arr = unpack("C*",$data);
    foreach ($arr as $key => $value) {
        echo " " . $value;
    }
    echo "\n";
}
 
//////////////$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
//write the hex file 
 
// $fp = fopen("f190328h03.hex", "r");
// for ($i = 0; $i < 256; $i++) {
//     // Assign a binary byte to a variable
//     $data = pack("C*",$i);
 
//     // Write the byte to the file
//     fwrite($fp, $data);
// }
// fclose($fp);



////$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$


//Extracting a substring from a binary string.


// $fp = fopen("f190328h03.hex","rb");
// while (!feof($fp)) {
//     $data = fread($fp,100);  // read up to 100 bytes
//     $data[4] = chr(88);      // Modify the value of the 5th byte
//     $nbytes = strlen($data); // get the number of bytes actuall read
//     echo "nbytes: " . $nbytes . "\n";
//     for ($i = 0; $i < $nbytes; $i++) {
//         $subdata = substr($data,$i,1);      // extract a single byte
//         $arr = unpack("C*",$subdata);       // convert to decimal
//         foreach ($arr as $key => $value) {
//             echo " " . $value;
//         }
//         if ($i % 16 == 15) {
//             echo "\n";
//         }
//     }
//     echo "\n\n";
// }
// fclose($fp);
 

