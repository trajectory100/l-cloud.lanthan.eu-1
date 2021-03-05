<?php

$db_server = 'rdbms.strato.de';
$db_benutzer = 'U969575';
$db_passwort = 'MySqL89dB3';
$db_name = 'DB969575';
$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if ($mysqli->connect_error) {
    echo "Fehler bei der Verbindung: " .mysqli_connect_error();
    exit();
}
