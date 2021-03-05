<?php

$db_server = 'localhost';
$db_benutzer = 'lanthan-01';
$db_passwort = '7VrBi-.GXTZCF';
$db_name = 'lanthan_test';
$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if ($mysqli->connect_error) {
    echo "Fehler bei der Verbindung: " .mysqli_connect_error();
    exit();
}
