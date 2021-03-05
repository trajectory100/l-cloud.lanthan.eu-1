<?php

$db_server = 'localhost';
$db_benutzer = 'lanthan-01';
$db_passwort = '7VrBi-.GXTZCF';
$db_name = 'lanthan_test';

$cr = "\n\r";

$outdetail = true;
if (htmlspecialchars($_REQUEST['detail']) == '0') {
    $outdetail = false;
} else {
    echo "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>\n\r";
    echo "<style> p.x {font-family: consola, monospace;} </style>\n\r";
    echo "</head><body><p class=\"x\"><table> \n\r";
    echo "<tr><td>PUSER</td><td>TW</td><td>A TO E</td><td>Error</td><td>TO</td></tr>\n\r";
}

$mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
if ($mysqli->connect_error) {
    echo "Fehler bei der Verbindung: " .mysqli_connect_error();
    exit();
}


// Zeitmessung
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// -------------------------------------------------------------------------------------------------
// alle aktiven Projekte Bearbeiten
// $puser='T00110A';
$alert = 0;
$laufstart = microtime_float();
$projekte = $mysqli->query("SELECT * FROM bfprojektini WHERE AKTIV = '1'   AND DB = 'bftopass'  ;");
// $projekte = $mysqli->query("SELECT * FROM bfprojekt WHERE AKTIV = '1'  AND DB = 'bftopass'   ;");

while ($zeile = $projekte->fetch_array()) {
    $pid = $zeile['ID'];
    $puser = $zeile['PUSER'];
    $db = $zeile['DB'];
    $aktiv = $zeile['AKTIV'];
    $laufstep = microtime_float();

    // Projektparameter laden: aktuell D1 bis D8 und A1 bis A9
    $frage = "SELECT * FROM bftopassini WHERE PUSER = '$puser';";
    $projektini = $mysqli->query($frage);
    while ($zeile2 = $projektini->fetch_array()) {// Digitale Parameter
        for ($n = 1; $n < 9; $n++) {
            $DxVAR[$n] = "D" . $n;
            $nvar = "DI" . $n . "EN";
            $DxEN[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "NAME";
            $DxNAME[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "ALERT";
            $DxALERT[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "ALVAL";
            $DxALVAL[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "L";
            $DxL[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "H";
            $DxH[$n] = $zeile2[$nvar];
            $nvar = "DI" . $n . "NOE";
            $DxNOE[$n] = $zeile2[$nvar];
        }

        // Analoge Parameter
        for ($n = 1; $n < 10; $n++) {
            $ANxVAR[$n] = "A" . $n;
            $nvar = "AN" . $n . "EN";
            $ANxEN[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "NAME";
            $ANxNAME[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "ISDIGITAL";
            $ANxISDIGITAL[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "DALVAL";
            $ANxDALVAL[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "NOE";
            $ANxNOE[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "AL1EN";
            $ANxAL1EN[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "AL2EN";
            $ANxAL2EN[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "AL1LVL";
            $ANxAL1LVL[$n] = floatval($zeile2[$nvar]);
            $nvar = "AN" . $n . "AL2LVL";
            $ANxAL2LVL[$n] = floatval($zeile2[$nvar]);
            $nvar = "AN" . $n . "SCALE";
            $ANxSCALE[$n] = floatval($zeile2[$nvar]);
            $nvar = "AN" . $n . "OKTXT";
            $ANxOKTXT[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "AL1TXT";
            $ANxAL1TXT[$n] = $zeile2[$nvar];
            $nvar = "AN" . $n . "AL2TXT";
            $ANxAL2TXT[$n] = $zeile2[$nvar];
        }

        // timeout & TWSW
        $interval = intval($zeile2['INTERVAL']);
        $TWswal = intval($zeile2['TWSWAL']);
        $TWswch = $zeile2['TWSWCH'];
        $TWswnight = intval($zeile2['TWSWNIGHT']); // Sollwert für Nacht 0 = Nacht oder 1 = Nacht
    }
    $projektini->close();

    // Projektweise Fehleranalyse
    $db = 'bftopass';
    $zeile2 = null;
    $datazeit = 0;
    // $frage = "SELECT * FROM $db WHERE PUSER = '$puser' ORDER BY ZEIT DESC LIMIT 1;";
    $frage = "SELECT * FROM bftopass WHERE PUSER = '$puser' ORDER BY ZEIT DESC LIMIT 1;";

    $projektdata = $mysqli->query($frage);
    $zeile2 = $projektdata->fetch_array();
    if ($zeile2 != null) {
        $datazeit = $zeile2['ZEIT'];
        $datauser = $zeile2['PUSER'];

        //timeout
        $deltaT = (int)((time() - $datazeit) / 60);
        $bftimeout = ($deltaT > (int)($interval * 1));

        // Tag nacht abklärung
        // dodo STDN = D N -

        // Auswertung
        if ($aktiv == '1') {
            // TWSW Status erkennen

            $TWad = ''; // Twilight Switch Channel Analog/Digital
            $TWnr = ''; // Twilight Switch Channel Number
            $TWst = ''; // Twilight Status Nacht = 0
            $TWnoe = ''; // Twilightswitch Störunterdrückungsstatus
            if (strlen($TWswch) > 1) {
                $TWad = substr($TWswch, 0, 1);
                //$TWnr = (int)(substr($twswch,1,strlen($twswch)));
                $TWnr = (int)(substr($TWswch, 1));
                if (($TWnr > 0) and ($TWnr < 10)) {
                    if ($TWad == 'A') {
                        $TWst = (int)$zeile2[$ANxVAR[$TWnr]] > $ANxAL1LVL[$TWnr];
                    }
                    if ($TWad == 'D') {
                        $TWst = (int)$zeile2[$DxVAR[$TWnr]];
                    }
                    if ($TWswnight == '1') {// Normierung auf Tag = 1 Night = 0
                        $TWst = 1 - intval($TWst);
                    }
                }
            }


            // 8 Digitale Kanäle
            for ($n = 1; $n < 9; $n++) {
                $bferrD[$n] = 0;
                $TWnoe = '-' . intval($TWst) . $DxNOE[$n];
                if ((int)$DxEN[$n] === 1 && (int)$DxALERT[$n] === 1) {
                    $bferrD[$n] =  (int)$zeile2[$DxVAR[$n]] === (int)$DxALVAL[$n];
                    echo  $bferrD[$n] . ',' .  (int)$zeile2[$DxVAR[$n]] . ',' . $DxALVAL[$n] . '<br>';
                    if (($DxNOE[$n] == 'D') and ($TWst == '1')) {
                        $bferrD[$n] = 0;
                        $TWnoe = 'D' . intval($TWst) . $DxNOE[$n];
                    }
                    if (($DxNOE[$n] == 'N') and ($TWst == '0')) {
                        $bferrD[$n] = 0;
                        $TWnoe = 'D' . intval($TWst) . $DxNOE[$n];
                    }
                }
            }

            // 9 Analoge Kanäle, analog oder digital gebraucht
            for ($n = 1; $n < 10; $n++) {
                $bferrA1[$n] = 0;
                $bferrA2[$n] = 0;
                $TWnoe = '-' . intval($TWst) . $ANxNOE[$n];
                if ($ANxEN[$n] == "1") {
                    // Digital
                    if ($ANxISDIGITAL[$n] == "1") {
                        if ($ANxAL1EN[$n] == "1") {
                            $bferrA1[$n] = ($ANxDALVAL[$n] == (int)$zeile2[$ANxVAR[$n]]);
                        }
                    } else {
                        if ($ANxAL1EN[$n] == "1") {
                            $bferrA1[$n] = ((floatval($zeile2[$ANxVAR[$n]]) * $ANxSCALE[$n]) < $ANxAL1LVL[$n]);
                        }
                        if ($ANxAL2EN[$n] == "1") {
                            $bferrA2[$n] = ((floatval($zeile2[$ANxVAR[$n]]) * $ANxSCALE[$n]) < $ANxAL2LVL[$n]);
                        }
                    }
                    if (($ANxNOE[$n] == 'D') and ($TWst == '1')) {
                        $bferrA1[$n] = 0;
                        $bferrA2[$n] = 0;
                        $TWnoe = 'A' . intval($TWst) . $ANxNOE[$n];
                    }
                    if (($ANxNOE[$n] == 'N') and ($TWst == '0')) {
                        $bferrA1[$n] = 0;
                        $bferrA2[$n] = 0;
                        $TWnoe = 'A' . intval($TWst) . $ANxNOE[$n];
                    }
                }
            }

            // Zusammenfassung der Fehler
            // Zusammenfassung der Fehler
            // $bferr = in_array(1, $bferrD);// $bferrD[1] || $bferrD[2] || $bferrD[3] || $bferrD[4] || $bferrD[5] || $bferrD[6] || $bferrD[7] || $bferrD[8];
            $bferr =  in_array(1, $bferrD)|| $bferr || $bferrA1[1] || $bferrA1[2] || $bferrA1[3] || $bferrA1[4] || $bferrA1[5] || $bferrA1[6] || $bferrA1[7] || $bferrA1[8] || $bferrA1[9];
            $bferr = $bferr || $bferrA2[1] || $bferrA2[2] || $bferrA2[3] || $bferrA2[4] || $bferrA2[5] || $bferrA2[6] || $bferrA2[7] || $bferrA2[8] || $bferrA2[9];
            //echo   $puser.$bferr. ',' ;
            $bfDerr = intval($bferrD[1]) . intval($bferrD[2]) . intval($bferrD[3]) . intval($bferrD[4]);
            $bfDerr = $bfDerr  . intval($bferrD[5]) . intval($bferrD[6]) . intval($bferrD[7]) . intval($bferrD[8]);
            $bfDerr = $bfDerr  . intval($bferrD[9]) . intval($bferrD[10]) . intval($bferrD[11]) . intval($bferrD[12]);
            $bfDerr = $bfDerr  . intval($bferrD[13]) . intval($bferrD[14]) . intval($bferrD[15]) . intval($bferrD[16]);


            // $bfDerr = "00000000" .intval($bferrD[8]) .intval($bferrD[7]) .intval($bferrD[6]) .intval($bferrD[5]);
            // $bfDerr = $bfDerr  .intval($bferrD[4]) .intval($bferrD[3]) .intval($bferrD[2]) .intval($bferrD[1]);
            $bfA1err = "0000000"  . intval($bferrA1[9]) . intval($bferrA1[8]) . intval($bferrA1[7]) . intval($bferrA1[6]) . intval($bferrA1[5]);
            $bfA1err = $bfA1err . intval($bferrA1[4]) . intval($bferrA1[3]) . intval($bferrA1[2]) . intval($bferrA1[1]);
            $bfA2err = "0000000"  . intval($bferrA2[9]) . intval($bferrA2[8]) . intval($bferrA2[7]) . intval($bferrA2[6]).intval($bferrA2[5]);
            $bfA2err = $bfA2err . intval($bferrA2[4]) . intval($bferrA2[3]) . intval($bferrA2[2]) . intval($bferrA2[1]);
        } else { // not aktive
            $bferr = 0;
            $bfDerr = "0000000000000000";
            $bfA1err = "0000000000000000";
            $bfA2err = "0000000000000000";
            $bftimeout = 0;
            $datazeit = 0;
            $deltaT = (int)((time() - $datazeit) / 60);
        }
    } else //not null
    {
        $bferr = 0;
        $bfDerr = "0000000000000000";
        $bfA1err = "0000000000000000";
        $bfA2err = "0000000000000000";
        $bftimeout = 0;
        $datazeit = 0;
        $deltaT = (int)((time() - $datazeit) / 60);
    }

    if ($outdetail == true) {
        echo "<tr>";
        $laufend = microtime_float();
        //echo " " .round($laufend - $laufstart, 3) ."; ";//echo " " .round($laufend - $laufstep, 3) ."; ";
        echo "<td>" . $puser . "</td>";
        echo "<td>  " . $TWad . $TWnr . ":" . $TWst . $TWnoe . "</td>";
        echo "<td>  " . intval($aktiv) . intval($bftimeout) . intval($bferr) . "</td>";
        echo "<td> D:" . $bfDerr;
        echo " A1:" . $bfA1err;
        echo " A2:" . $bfA2err . "</td>";
        echo "<td> T:" . (int)$deltaT . "</td></tr>\n\r";
    }

    // Abspeichern
    $insertm = "INSERT INTO bfprojektstatus2_new (ID, PUSER, ERROR, TIMEOUT, LASTDATA, AKTIV, DERR, A1ERR, A2ERR, TIME) VALUES";
    $insertm = $insertm . "(\"" . $pid . "\", \"" . $puser . "\", \"" . intval($bferr) . "\", \"" . intval($bftimeout) . "\", \"" . $datazeit . "\", \"" . $aktiv . "\", ";
    $insertm = $insertm . "\"" . $bDferr . "\", \"" . $bfA1err . "\", \"" . $bfA2err . "\", \"" . time() . "\")";
    $insertm = $insertm . "ON DUPLICATE KEY UPDATE  ERROR=\"" . intval($bferr)  . "\", TIMEOUT=\"" . intval($bftimeout) . "\", LASTDATA=\"" . $datazeit . "\", AKTIV=\"" . $aktiv . "\", ";
    $insertm = $insertm . "DERR=\"" . $bfDerr  . "\", A1ERR=\"" . $bfA1err . "\", A2ERR=\"" . $bfA2err . "\", TIME=\"" . time() ."\"";

    //echo $insert ."<br>";
    $ergebnis = $mysqli->query($insertm);
    if ($mysqli->errno > 0) {
        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error;
    }
    if ($outdetail == true) {
        //echo "";
    }

    //27/08/2020  I commented these lines 1,2,3 to help the server to run faster
    // \1$projektdata->close();
}
// \2$projekte->close();

// \3$mysqli->close();
echo "</table></p>OK<br>";
