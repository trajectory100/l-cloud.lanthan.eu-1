<?PHP
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon">
    <title>Lanthan Cloud Monitoring</title>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <link href="../css/status.css" rel="stylesheet" type="text/css" />
    <link href="../css/status2.css" rel="stylesheet" type="text/css" />
    <!-- slide toggle -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("dt").click(function() { // trigger 
                $(this).next("dd").slideToggle("fast"); // blendet beim Klick auf "dt" die n�chste "dd" ein. 
                $(this).children("a").toggleClass("closed open"); // wechselt beim Klick auf "dt" die Klasse des enthaltenen a-Tags von "closed" zu "open". 
            });
        });
    </script>
    <!-- datepicker -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker({
      showOn: "both",
      buttonImage: "/image/calendar.gif",
      buttonImageOnly: true,
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      showWeek: true,
      buttonText: "Select date"
    });
  } );
  </script>

</head>

<body>

    <?PHP
    $db_server = 'localhost';
    $db_benutzer = 'lanthan-01';
    $db_passwort = '7VrBi-.GXTZCF';
    $db_name = 'lanthan_test';
    $mysqli = new mysqli($db_server, $db_benutzer, $db_passwort, $db_name);
    if ($mysqli->connect_error) {
        echo "Fehler bei der Verbindung: " . mysqli_connect_error();
        exit();
    }


    // ------------------------------------------------------------------------------------------------
    // Deklarations / Eingabeabfrage
    // ------------------------------------------------------------------------------------------------ 
    $slogin = 0;
    $sadmin = 0;
    $suser = 0;
    $job = "NONE";
    $action = "NONE";
    $tpid =  intval(htmlspecialchars($_REQUEST['PID']));   // projekt id

    if (isset($_GET["JOB"])) {
        $job = htmlspecialchars($_REQUEST['JOB']);
    }
    if (isset($_GET["ACTION"])) {
        $action = htmlspecialchars($_REQUEST['ACTION']);
    }
    echo "PID " . $tpid . " / JOB " . $job . " / ACTION " . $action;

    // ------------------------------------------------------------------------------------------------
    // Aufrufrechte / Login kl�ren
    // ------------------------------------------------------------------------------------------------       
    if (isset($_SESSION['user'])) {
        $quser = $mysqli->real_escape_string($_SESSION['user']);
        $frage = "SELECT * FROM bfuser_has_bfprojekt WHERE bfuser_ID = '$quser' AND bfprojekt_ID = '$tpid';";
        $ergebnis = $mysqli->query($frage);
        while ($zeile = $ergebnis->fetch_array()) {
            $spid = $zeile['bfprojekt_ID'];
        }
        if ($tpid == $spid) {
            $_SESSION['pid'] = $tpid;
            $slogin = 1;
        }
        if (isset($_SESSION['admin'])) {
            if ($_SESSION['admin'] == 1) {
                $slogin = 1;
                $sadmin = 1;
            }
        }
        if (isset($_SESSION['username'])) {
            $suser = $_SESSION['username'];
        }
        $ergebnis->close();
    }


    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // Webseitenausgabe
    // ------------------------------------------------------------------------------------------------ 
    echo "<div class=\"container\">";

    // ----- Logo �berschrift
    echo "<div class=\"contlogo\">";
    echo "<div class=\"contlogohead\"><a href=\"../index.php\"><img src=\"../image/lanthan_110x60.jpg\" width=\"110\" height=\"60\" /></a></div>";
    echo "<div class=\"contlogotext\"> Lanthan Cloud Monitoring"  . " - " . date('d.m.Y') . "</div>";
    echo "</div>";

    // ----- Navigation
    echo "<div class=\"contnav\">";
    echo "<table bgcolor=\"#DDDDDD\" width=\740\">";
    // ----- Home
    echo "<td width=\"100\"><a href=\"../index.php?\"> Home</a></td>";
    echo "<td width=\"100\"><a href=\"settings.php?JOB=USER\">User</a></td>";
    echo "<td width=\"100\"><a href=\"settings.php?JOB=PROJEKT\">Project</a></td>";
    echo "<td width=\"150\0\"><a href=\"settings.php?JOB=USERHASP\">UserProject</a></td>";
    echo "<td width=\"100\"><a href=\"settings.php?JOB=BFINI\"> ProjectINI</a></td>";
    echo "<td width=\"75\"><a href=\"settings.php?JOB=MAIL\">Mail</a></td>";
    echo "<td width=\"200\">User: " . $suser;
    if ($sadmin == 1) {
        echo ", admin";
    }
    echo "<br><a href=\"../index.php?LOGOUT=1\">abmelden</a></td></tr>";
    echo "</table></div>";


    // ------------------------------------------------------------------------------------------------
    // Hauptauswertung
    // ------------------------------------------------------------------------------------------------
    if (($slogin == 1) && ($sadmin == 1)) {

        if ($job == "NONE") {
            $job = "PROJEKT";
        }

        // -------------------------------------------------------------------------------------------------
        // -------------------------------------------------------------------------------------------------
        // -------------------------------------------------------------------------------------------------
        // -------------------------------------------------------------------------------------------------
        // $TODO: USER
        // -------------------------------------------------------------------------------------------------
        if ($job == "USER") {

            // NEW------------------------------------------------------------------------------------------
            if ($action == "NEW") {
                if (isset($_GET["NEWUSER"])) {
                    $newuser = htmlspecialchars($_REQUEST['NEWUSER']);
                    if (isset($_GET["NEWPASS"])) {
                        $newpass = htmlspecialchars($_REQUEST['NEWPASS']);
                        $test = 0;
                        if ((strlen($newpass) < 5) || (strlen($newpass) > 20)) {
                            $test = 1;
                            echo "Passwort <5 oder >20 Zeichen <br>";
                        }
                        if ((strlen($newuser) < 5) || (strlen($newuser) > 20)) {
                            $test = 1;
                            echo "Username <5 oder >20 Zeichen <br>";
                        }

                        $frage =  "SELECT * FROM bfuser;";
                        $ergebnis = $mysqli->query($frage);
                        while ($zeile = $ergebnis->fetch_array()) {
                            if ($newuser == $zeile['USER']) {
                                $test = 1;
                                echo "Username existiert bereits <br>";
                            }
                        }
                        $ergebnis->close();
                        echo "New User Entry: <br>User: " . $newuser . " / Pass: " . $newpass . "<br>";

                        if ($test == 0) {
                            $snp = $mysqli->real_escape_string($newpass);
                            $snu = $mysqli->real_escape_string($newuser);

                            $insert = "INSERT INTO bfuser (USER, PASS) VALUES('$snu','$snp');";
                            $ergebnis = $mysqli->query($insert);
                            echo "Values stored:";
                            echo 'SQL Error (' . $mysqli->errno . ') ' . $mysqli->error . '<br>';
                        }
                    }
                }
            }
            // DELETE-------------------------------------------------------------------------------
            if ($action == "DEL") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bfuser;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $insert = "DELETE FROM bfuser WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($insert);
                        echo "Values stored:";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                        //$ergebnis->close();
                        echo "DELETE: " . $insert . "<br>";
                    }
                }
            }

            // �bersicht-----------------------------------------------------------------
            echo "<br><h4>User List</h4>";
            echo "<table>";
            $frage =  "SELECT * FROM bfuser;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                echo "<tr><td>" . $zeile['ID'] . "&nbsp;&nbsp;&nbsp;</td><td>" . $zeile['USER'] . "&nbsp;&nbsp;&nbsp;</td><td>  PASS: *******</td></tr>";
            }
            echo "</table>";
            $ergebnis->close();

            // l�sch liste
            echo "<br><h4> Delete User </h4>";
            echo "<form action=\"settings.php\">";
            echo "<select name=\"UID\">";
            echo "<option value=\"0\">Choose</option>";
            $frage =  "SELECT * FROM bfuser;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                echo "<option value=\"" . $zeile['ID'] . "\">" . $zeile['USER'] . "</option>";
            }
            echo "</select></p>";
            $ergebnis->close();
            echo "<input type=\"hidden\" name=\"JOB\" value=\"USER\"/>";
            echo "<input type=\"hidden\" name=\"ACTION\" value=\"DEL\"/>";
            echo "<input type=\"submit\" value=\"Delete User\"></form><br><br>";

            // Add User
            echo "<h4> Add User </h4>";
            echo "<form action=\"settings.php\"><table>";
            echo "<tr><td><input name=\"NEWUSER\" maxlength=\"20\"/></td>";
            echo "<td><input type=\"password\" name=\"NEWPASS\" maxlength=\"20\"/></td></tr></table>";
            echo "<input type=\"hidden\" name=\"JOB\" value=\"USER\"/>";
            echo "<input type=\"hidden\" name=\"ACTION\" value=\"NEW\"/>";
            echo "<input type=\"submit\" value=\"New User\"></form><br><br>";
        }


        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // $TODO: PROJEKT
        // ----------------------------------------------------------------------------------------------------------------------------------------
        if ($job == "PROJEKT") {

            // NEW EMPTY----------------------------------------------------------------------------------------------------------
            if ($action == "NEW0") {
                $frage = "INSERT INTO bfprojekt (USER, PUSER, SN, ORT, LAND, WGS84B, WGS84L, DB, AKTIV, TEXT, MTEXT) VALUES ('', 'NEW', '', '', '', '', '', '', '', '', '');";
                $ergebnis = $mysqli->query($frage);
                echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                //$ergebnis->close();
                echo "Created Empty Project: " . $frage . "<br>";
            }

            // NEW COPY----------------------------------------------------------------------------------------------------------
            if ($action == "NEW1") {
                echo "NEW1";
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $suid = $mysqli->real_escape_string($uid);
                    $frage =  "SELECT * FROM bfprojekt WHERE ID = " . $suid . ";";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        $insert = "INSERT INTO bfprojekt (USER, PUSER, SN, ORT, LAND, WGS84B, WGS84L, DB, AKTIV, TEXT, MTEXT) VALUES ('"
                            . $zeile['USER'] . "', '"
                            . $zeile['PUSER'] . "', '"
                            . $zeile['SN'] . "', '"
                            . $zeile['ORT'] . "', '"
                            . $zeile['LAND'] . "', '"
                            . $zeile['WGS84B'] . "', '"
                            . $zeile['WGS84L'] . "', '"
                            . $zeile['DB'] . "', '"
                            . $zeile['AKTIV'] . "', '"
                            . $zeile['TEXT'] . "', '"
                            . $zeile['MTEXT'] . "');";
                    }
                    $ergebnis->close();
                    $ergebnis = $mysqli->query($insert);
                    echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                    //$ergebnis->close();
                    echo "Created  Project: from " . $insert . "<br>";
                }
            }

            // DELETE----------------------------------------------------------------------------------------------------------
            if ($action == "DEL") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bfprojekt;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $insert = "DELETE FROM bfprojekt WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($insert);
                        echo "Values stored:";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                        //$ergebnis->close();
                        echo "DELETE: " . $insert . "<br>";
                    }
                }
            }

            // EDIT----------------------------------------------------------------------------------------------------------
            if ($action == "EDIT") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bfprojekt;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $frage =  "SELECT * FROM bfprojekt WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($frage);
                        $erg_n1 = $ergebnis->fetch_fields();
                        $zeile = $ergebnis->fetch_array();
                        echo "<br><h4>Edit Project</h4>";
                        echo "<table>";
                        echo "<form action=\"settings.php\">";
                        foreach ($erg_n1 as $fields) {
                            $erg_n2[] = $fields->name;
                            echo "<tr><td>" . $fields->name . ": </td>";
                            echo "<td><input name=\"" . $fields->name . "\" value=\"" . $zeile[$fields->name] . "\"></td></tr>";
                        }
                        echo "</table>";
                        echo "<input type=\"hidden\" name=\"JOB\" value=\"PROJEKT\"/>";
                        echo "<input type=\"hidden\" name=\"ACTION\" value=\"EDITSAVE\"/>";
                        echo "<input type=\"submit\" value=\"Submit\"></form>";
                    }
                }
            }

            if ($action == "EDITSAVE") {
                if (isset($_GET["ID"])) {
                    $sid = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['ID']));
                    $sus = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['USER']));
                    $spu = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['PUSER']));
                    $ssn = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['SN']));
                    $sor = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['ORT']));
                    $sla = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['LAND']));
                    $swb = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['WGS84B']));
                    $swl = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['WGS84L']));
                    $sdb = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['DB']));
                    $sak = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['AKTIV']));
                    $ste = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['TEXT']));
                    $smt = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['MTEXT']));
                    $insert = "UPDATE bfprojekt SET "
                        . "USER=\"" . $sus . "\", "
                        . "PUSER=\"" . $spu . "\", "
                        . "SN=\"" . $ssn . "\", "
                        . "ORT=\"" . $sor . "\", "
                        . "LAND=\"" . $sla . "\", "
                        . "WGS84B=\"" . $swb . "\", "
                        . "WGS84L=\"" . $swl . "\", "
                        . "DB=\"" . $sdb . "\", "
                        . "AKTIV=\"" . $sak . "\", "
                        . "MTEXT=\"" . $smt . "\", "
                        . "TEXT=\"" . $ste . "\" WHERE ID=" . $sid . ";";
                    $ergebnis = $mysqli->query($insert);
                    echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                    //$ergebnis->close();
                    echo "Updated  Project: from " . $insert . "<br>";
                }
            }

            // $TODO: PROJEKT �bersicht----------------------------------------------------------------------------------------------------------
            echo "<br><h4>Project List</h4>";
            echo "<table><tr>";
            echo "<tr><td><b> ID </b></td>";
            echo "<td><b> USER </b></td>";
            echo "<td><b> PUSER </b></td>";
            echo "<td><b> SN </b></td>";
            echo "<td><b> ORT </b></td>";
            echo "<td><b> LAND </b></td>";
            echo "<td><b> WGS84B </b></td>";
            echo "<td><b> WGS84L </b></td>";
            echo "<td><b> DB </b></td>";
            echo "<td><b> AKTIV </b></td>";
            echo "<td><b> TEXT </b></td>";
            echo "<td><b> MTEXT </b></td></tr>";
            echo "<tr height=\"1\"><td>&nbsp;</td></tr>";

            $frage =  "SELECT * FROM bfprojekt;";
            $ergebnis = $mysqli->query($frage);
            $cnt = 0;
            while ($zeile = $ergebnis->fetch_array()) {
                echo "<tr>";
                //settings.php?ACTION=EDIT&UID=xx&JOB=PROJEKT
                echo "<td>";
                echo "<a href=\"settings.php?ACTION=EDIT&UID=" . $zeile['ID'] . "&JOB=PROJEKT\">";
                echo $zeile['ID'] . "</a></td>";
                echo "<td>" . $zeile['USER'] . "</td>";
                echo "<td>" . $zeile['PUSER'] . "</td>";
                echo "<td>" . $zeile['SN'] . "</td>";
                echo "<td>" . $zeile['ORT'] . "</td>";
                echo "<td>" . $zeile['LAND'] . "</td>";
                echo "<td>" . $zeile['WGS84B'] . "</td>";
                echo "<td>" . $zeile['WGS84L'] . "</td>";
                echo "<td>" . $zeile['DB'] . "</td>";
                echo "<td>" . $zeile['AKTIV'] . "</td>";
                echo "<td>" . $zeile['TEXT'] . "</td>";
                echo "<td>" . $zeile['MTEXT'] . "</td>";
                echo "</tr>";
                $cnt = $cnt + 1;
                if ($cnt > 5) {
                    echo "<tr height=\"1\"><td>&nbsp;</td></tr>";
                    $cnt = 0;
                }
            }
            echo "</table>";
            $ergebnis->close();


            // aktion----------------------------------------------------------------------------------------------------------
            echo "<br><h4> Aktion </h4>";
            echo "<form action=\"settings.php\">";
            echo "<select name=\"ACTION\">";
            echo "<option value=\"0\">Choose Action</option>";
            echo "<option value=\"DEL\">Delete Project</option>";
            echo "<option value=\"NEW0\">New Empty Project</option>";
            echo "<option value=\"NEW1\">New Copy From</option>";
            echo "<option value=\"EDIT\">Edit Project</option>";
            echo "</select>";

            echo "<select name=\"UID\">";
            echo "<option value=\"0\">Choose</option>";
            $frage =  "SELECT * FROM bfprojekt;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                echo "<option value=\"" . $zeile['ID'] . "\">" . $zeile['PUSER'] . "</option>";
            }
            echo "</select></p>";

            $ergebnis->close();
            echo "<input type=\"hidden\" name=\"JOB\" value=\"PROJEKT\"/>";
            echo "<input type=\"submit\" value=\"Submit\"></form><br><br>";
        }


        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // $TODO: BFINI
        // ----------------------------------------------------------------------------------------------------------------------------------------
        if ($job == "BFINI") {

            if ($action == "NONE") {
                $action = "LIST";
            }

            // $TODO: BFINI: NEW EMPTY----------------------------------------------------------------------------------------------------------
            if ($action == "NEW0") {
                $frage = "INSERT INTO bftopassini (PUSER) VALUES ('NEW');";
                $ergebnis = $mysqli->query($frage);
                echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                //$ergebnis->close();
                echo "Created Empty Project: " . $frage . "<br>";
            }

            // $TODO: BFINI: NEW COPY----------------------------------------------------------------------------------------------------------
            if ($action == "NEW1") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bftopassini;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $sqlfrage1 = "CREATE TEMPORARY TABLE tmp SELECT * FROM bftopassini WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($sqlfrage1);
                        echo "Temporary Table Created: ";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';

                        $sqlfrage2 = "UPDATE tmp SET ID = NULL;";
                        $ergebnis = $mysqli->query($sqlfrage2);
                        echo "Temporary Table ID = NULL: ";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';

                        $sqlfrage3 = "INSERT INTO bftopassini SELECT * from tmp;";
                        $ergebnis = $mysqli->query($sqlfrage3);
                        echo "Temporary Table copied: ";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';

                        echo "New Copy from completed: <br>" . $sqlfrage1 . "<br>" . $sqlfrage2 . "<br>" . $sqlfrage3 . "<br>";
                    }
                }
            }


            // $TODO: BFINI: DELETE----------------------------------------------------------------------------------------------------------
            if ($action == "DEL") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bftopassini;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $insert = "DELETE FROM bftopassini WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($insert);
                        echo "Values stored:";
                        echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                        //$ergebnis->close();
                        echo "DELETE: " . $insert . "<br>";
                    }
                }
            }

            // $TODO: BFINI: EDIT----------------------------------------------------------------------------------------------------------
            if ($action == "EDIT") {
                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $test = 0;
                    $frage =  "SELECT * FROM bftopassini;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        if ($uid == $zeile['ID']) {
                            $test = 1;
                        }
                    }
                    $ergebnis->close();
                    if ($test == 1) {
                        $suid = $mysqli->real_escape_string($uid);
                        $frage =  "SELECT * FROM bftopassini WHERE ID = " . $suid . ";";
                        $ergebnis = $mysqli->query($frage);
                        $erg_n1 = $ergebnis->fetch_fields();
                        $zeile = $ergebnis->fetch_array();
                        echo "<br><h4>Edit Project ini</h4>";
                        echo "<table>";
                        echo "<form action=\"settings.php\">";
                        foreach ($erg_n1 as $fields) {
                            $erg_n2[] = $fields->name;
                            echo "<tr><td>" . $fields->name . ": </td>";
                            echo "<td><input name=\"" . $fields->name . "\" value=\"" . $zeile[$fields->name] . "\"></td></tr>";
                        }
                        echo "</table>";
                        echo "<input type=\"hidden\" name=\"JOB\" value=\"BFINI\"/>";
                        echo "<input type=\"hidden\" name=\"ACTION\" value=\"EDITSAVE\"/>";
                        echo "<input type=\"submit\" value=\"Submit\"></form>";
                    }
                }
            }

            // $TODO: BFINI: EDITSAVE----------------------------------------------------------------------------------------------------------
            if ($action == "EDITSAVE") {
                $action = "LIST";
                if (isset($_GET["ID"])) {
                    $sid = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['ID']));
                    $spu = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['PUSER']));
                    $sin = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['INTERVAL']));
                    $stw = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['TWSWAL']));

                    $na = array(
                        1 => "NAME", 2 => "EN", 3 => "ISDIGITAL", 4 => "MIN", 5 => "MAX", 6 => "SCALE", 7 => "AL1LVL", 8 => "AL2LVL", 9 => "AL1TXT",
                        10 => "AL2TXT", 11 => "OKTXT", 12 => "AL1EN", 13 => "AL2EN", 14 => "DALVAL"
                    );
                    $nd = array(1 => "NAME", 2 => "L", 3 => "H", 4 => "ALERT", 5 => "ALVAL", 6 => "EN");

                    $insert = "UPDATE bftopassini SET ";

                    for ($m = 1; $m < 15; $m++) {
                        for ($n = 1; $n < 10; $n++) {
                            $nmvar = "AN" . $n . $na[$m];
                            $sa[$n][$m] = $mysqli->real_escape_string(htmlspecialchars($_REQUEST[$nmvar]));
                            //echo $nmvar ." = " .$sa[$n][$m] ."<br>";     
                            $insert = $insert . $nmvar . "=\"" . $sa[$n][$m] . "\", ";
                        }
                    }

                    for ($m = 1; $m < 7; $m++) {
                        for ($n = 1; $n < 9; $n++) {
                            $nmvar = "DI" . $n . $nd[$m];
                            $sd[$n][$m] = $mysqli->real_escape_string(htmlspecialchars($_REQUEST[$nmvar]));
                            //echo $nmvar ." = " .$sd[$n][$m] ."<br>"; 
                            $insert = $insert . $nmvar . "=\"" . $sd[$n][$m] . "\", ";
                        }
                    }
                    $insert = $insert . "PUSER=\"" . $spu . "\", `INTERVAL`=\"" . $sin . "\", TWSWAL=\"" . $stw . "\"";
                    $insert = $insert . " WHERE ID=" . $sid . ";";
                    //echo $insert;

                    $ergebnis = $mysqli->query($insert);
                    echo 'SQL Error (' . $mysqli->errno . '):' . $mysqli->error . '<br>';
                    //$ergebnis->close();
                    echo "Updated  Project INI: from " . $insert . "<br>";
                }
            }

            // $TODO: BFINI: EDIT2----------------------------------------------------------------------------------------------------------
            if ($action == "EDIT2") {
                $action = "LIST";
                if (isset($_GET["UID"])) {
                    if (isset($_GET["CH"])) {
                        $uid = htmlspecialchars($_REQUEST['UID']);
                        $ch = htmlspecialchars($_REQUEST['CH']);
                        $chad = substr($ch, 0, 1);
                        $chnr = substr($ch, 1, strlen($ch) - 1);
                        //echo $ch ."/" .$chnr ."/" .$chad .",   ";

                        $frage =  "SELECT * FROM bftopassini WHERE ID = " . $uid . ";";
                        //echo $frage;
                        $ergebnis = $mysqli->query($frage);
                        while ($zeile = $ergebnis->fetch_array()) {
                            $npuser = $zeile["PUSER"];

                            if ($chad == "X") {
                                $ntwswal = $zeile["TWSWAL"];
                                $ntwswch = $zeile["TWSWCH"];
                                $ntwswnight = $zeile["TWSWNIGHT"];
                                $nint = $zeile["INTERVAL"];
                            }

                            if ($chad == "A") {
                                $nvar[0] = "AN" . $chnr . "NAME";
                                $nval[0] = $zeile[$nvar[0]];
                                $nvar[1] = "AN" . $chnr . "EN";
                                $nval[1] = $zeile[$nvar[1]];
                                $nvar[2] = "AN" . $chnr . "MIN";
                                $nval[2] = $zeile[$nvar[2]];
                                $nvar[3] = "AN" . $chnr . "MAX";
                                $nval[3] = $zeile[$nvar[3]];
                                $nvar[4] = "AN" . $chnr . "SCALE";
                                $nval[4] = $zeile[$nvar[4]];
                                $nvar[5] = "AN" . $chnr . "AL1LVL";
                                $nval[5] = $zeile[$nvar[5]];
                                $nvar[6] = "AN" . $chnr . "AL2LVL";
                                $nval[6] = $zeile[$nvar[6]];
                                $nvar[7] = "AN" . $chnr . "AL1TXT";
                                $nval[7] = $zeile[$nvar[7]];
                                $nvar[8] = "AN" . $chnr . "AL2TXT";
                                $nval[8] = $zeile[$nvar[8]];
                                $nvar[9] = "AN" . $chnr . "OKTXT";
                                $nval[9] = $zeile[$nvar[9]];
                                $nvar[10] = "AN" . $chnr . "AL1EN";
                                $nval[10] = $zeile[$nvar[10]];
                                $nvar[11] = "AN" . $chnr . "AL2EN";
                                $nval[11] = $zeile[$nvar[11]];
                                $nvar[12] = "AN" . $chnr . "ISDIGITAL";
                                $nval[12] = $zeile[$nvar[12]];
                                $nvar[13] = "AN" . $chnr . "DALVAL";
                                $nval[13] = $zeile[$nvar[13]];
                            }

                            if ($chad == "D") {
                                $nvar[0] = "DI" . $chnr . "NAME";
                                $nval[0] = $zeile[$nvar[0]];
                                $nvar[1] = "DI" . $chnr . "EN";
                                $nval[1] = $zeile[$nvar[1]];
                                $nvar[2] = "DI" . $chnr . "L";
                                $nval[2] = $zeile[$nvar[2]];
                                $nvar[3] = "DI" . $chnr . "H";
                                $nval[3] = $zeile[$nvar[3]];
                                $nvar[4] = "DI" . $chnr . "ALERT";
                                $nval[4] = $zeile[$nvar[4]];
                                $nvar[5] = "DI" . $chnr . "ALVAL";
                                $nval[5] = $zeile[$nvar[5]];
                                $nvar[6] = "DI" . $chnr . "NOE";
                                $nval[6] = $zeile[$nvar[6]];
                                echo "x";
                                echo $nvar[0];
                                echo $nval[0];
                            }

                            echo "<br><h4>Edit Projekt-ini " . $npuser . "</h4>";
                            echo "<table>";
                            echo "<form action=\"settings.php\">";
                            echo "<input type=\"hidden\" name=\"JOB\" value=\"BFINI\"/>";
                            echo "<input type=\"hidden\" name=\"ACTION\" value=\"EDITSAVE2\"/>";
                            echo "<input type=\"hidden\" name=\"ID\" value=\"" . $uid  . "\"/>";
                            echo "<input type=\"hidden\" name=\"CH\" value=\"" . $ch  . "\"/>";
                            if ($chad == "A") {
                                for ($m = 0; $m < 14; $m++) {
                                    echo "<tr><td>" . $nvar[$m] . ": </td>";
                                    echo "<td><input name=\"" . $nvar[$m] . "\" value=\"" . $nval[$m] . "\"></td></tr>";
                                }
                            }
                            if ($chad == "D") {
                                for ($m = 0; $m < 7; $m++) {
                                    echo "<tr><td>" . $nvar[$m] . ": </td>";
                                    echo "<td><input name=\"" . $nvar[$m] . "\" value=\"" . $nval[$m] . "\"></td></tr>";
                                }
                            }
                            if ($chad == "X") {
                                echo "<tr><td> PUSER : </td>";
                                echo "<td><input name=\"PUSER\" value=\"" . $npuser . "\"></td></tr>";
                                echo "<tr><td> TWSWAL : </td>";
                                echo "<td><input name=\"TWSWAL\" value=\"" . $ntwswal . "\"></td></tr>";
                                echo "<tr><td> TWSWCH : </td>";
                                echo "<td><input name=\"TWSWCH\" value=\"" . $ntwswch . "\"></td></tr>";
                                echo "<tr><td> TWSWNIGHT : </td>";
                                echo "<td><input name=\"TWSWNIGHT\" value=\"" . $ntwswnight . "\"></td></tr>";
                                echo "<tr><td> INTERVAL : </td>";
                                echo "<td><input name=\"INTERVAL\" value=\"" . $nint . "\"></td></tr>";
                            }
                            echo "</table>";
                            echo "<input type=\"submit\" value=\"Submit\"></form>";
                        }
                    }
                }
            }

            // $TODO: BFINI: EDITSAVE2----------------------------------------------------------------------------------------------------------
            if ($action == "EDITSAVE2") {
                $action = "LIST";
                if (isset($_GET["ID"])) {
                    $sid = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['ID']));
                    if (isset($_GET["CH"])) {
                        $uid = htmlspecialchars($_REQUEST['UID']);
                        $ch = htmlspecialchars($_REQUEST['CH']);
                        $chad = substr($ch, 0, 1);
                        $chnr = substr($ch, 1, strlen($ch) - 1);

                        if ($chad == "A") {
                            $na = array(
                                1 => "NAME", 2 => "EN", 3 => "ISDIGITAL", 4 => "MIN", 5 => "MAX",
                                6 => "SCALE", 7 => "AL1LVL", 8 => "AL2LVL", 9 => "AL1TXT", 10 => "AL2TXT",
                                11 => "OKTXT", 12 => "AL1EN", 13 => "AL2EN", 14 => "DALVAL"
                            );
                            for ($m = 1; $m < 15; $m++) {
                                $nmvar = "AN" . $chnr . $na[$m];
                                if (isset($_GET["ID"])) {
                                    $sa = $mysqli->real_escape_string(htmlspecialchars($_REQUEST[$nmvar]));
                                    $insert = $insert . $nmvar . "=\"" . $sa . "\", ";
                                }
                                echo $nmvar . " = " . $sa . "<br>";
                            }
                            $insert = substr($insert, 0, strlen($insert) - 2);
                        }

                        if ($chad == "D") {
                            $nd = array(1 => "NAME", 2 => "L", 3 => "H", 4 => "ALERT", 5 => "ALVAL", 6 => "EN", 7 => "NOE");
                            for ($m = 1; $m < 7; $m++) {
                                $nmvar = "DI" . $chnr . $nd[$m];
                                if (isset($_GET["ID"])) {
                                    $sd = $mysqli->real_escape_string(htmlspecialchars($_REQUEST[$nmvar]));
                                    $insert = $insert . $nmvar . "=\"" . $sd . "\", ";
                                }
                                echo $nmvar . " = " . $sd . "<br>";
                            }
                            $insert = substr($insert, 0, strlen($insert) - 2);
                        }

                        if ($chad == "X") {
                            $spu = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['PUSER']));
                            $sin = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['INTERVAL']));
                            $stwal = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['TWSWAL']));
                            $stwch = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['TWSWCH']));
                            $stwnight = $mysqli->real_escape_string(htmlspecialchars($_REQUEST['TWSWNIGHT']));
                            $insert = $insert . "PUSER=\"" . $spu . "\", `INTERVAL`=\"" . $sin . "\", TWSWCH=\"" . $stwch . "\", TWSWAL=\"" . $stwal . "\", TWSWNIGHT=\"" . $stwnight . "\"";
                        }

                        $insert = "UPDATE bftopassini SET " . $insert;

                        $insert = $insert . " WHERE ID=" . $sid . ";";
                        //echo $insert;

                        $ergebnis = $mysqli->query($insert);
                        $ergerrno = $mysqli->errno;
                        $ergerror = $mysqli->error;

                        if ($ergerrno == 0) {
                            echo "Updated  Project INI. <br>";
                        } else {
                            echo "MySQL Error: " . $ergerrno . ": " . ergerror . "<br>";
                            echo $insert . "<br>";
                        }
                    }
                }
            }

            // $TODO: BFINI: SHOW----------------------------------------------------------------------------------------------------------
            if ($action == "SHOW") {
                $na = array(
                    1 => "NAME", 2 => "EN", 3 => "ISDIGITAL", 4 => "MIN", 5 => "MAX", 6 => "SCALE", 7 => "AL1LVL", 8 => "AL2LVL", 9 => "AL1TXT",
                    10 => "AL2TXT", 11 => "OKTXT", 12 => "AL1EN", 13 => "AL2EN", 14 => "DALVAL"
                );
                $nd = array(1 => "NAME", 2 => "L", 3 => "H", 4 => "ALERT", 5 => "ALVAL", 6 => "EN", 7 => "NOE");

                if (isset($_GET["UID"])) {
                    $uid = htmlspecialchars($_REQUEST['UID']);
                    $frage =  "SELECT * FROM bftopassini WHERE ID=" . $uid . " ;";
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {

                        echo "<br><h4>Project " . $zeile['PUSER'] . " INI List </h4>";
                        echo "<table>";
                        echo "<tr><td>&nbsp;</td></tr><tr><td><h4>Analog 1-4</td><td>1</td><td>2</td><td>3</td><td>4</h4></td></tr>";

                        for ($m = 1; $m < 15; $m++) {
                            echo "<tr><td>ANx" . $na[$m] . "</td>";
                            for ($n = 1; $n < 5; $n++) {
                                $nmvar = "AN" . $n . $na[$m];
                                echo "<td>" . $zeile[$nmvar] . "</td>";
                            }
                        }

                        echo "<tr><td>&nbsp;</td></tr><tr></tr><td></td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td></tr>";
                        for ($m = 1; $m < 15; $m++) {
                            echo "<tr><td>ANx" . $na[$m] . "</td>";
                            for ($n = 5; $n < 10; $n++) {
                                $nmvar = "AN" . $n . $na[$m];
                                echo "<td>" . $zeile[$nmvar] . "</td>";
                            }
                        }

                        echo "<tr><td>&nbsp;</td></tr><tr></tr><tr><td></td><td>1</td><td>2</td><td>3</td><td>4</td></tr>";
                        for ($m = 1; $m < 7; $m++) {
                            echo "<tr><td>DIx" . $nd[$m] . "</td>";
                            for ($n = 1; $n < 5; $n++) {
                                $nmvar = "DI" . $n . $nd[$m];
                                echo "<td>" . $zeile[$nmvar] . "</td>";
                            }
                        }
                        echo "<tr><td>&nbsp;</td></tr><tr></tr><tr><td></td><td>5</td><td>6</td><td>7</td><td>8</td></tr>";
                        for ($m = 1; $m < 7; $m++) {
                            echo "<tr><td>DIx" . $nd[$m] . "</td>";
                            for ($n = 5; $n < 9; $n++) {
                                $nmvar = "DI" . $n . $nd[$m];
                                echo "<td>" . $zeile[$nmvar] . "</td>";
                            }
                        }
                    }
                    $ergebnis->close();
                    echo "</table>";
                }
            }

            // $TODO: BFINI: LIST----------------------------------------------------------------------------------------------------------
            if ($action == "LIST") {
                echo "<br><h4>Project Ini List";
                echo "<table width=\"700\"><tr>";
                echo "<td width=\"20\"> ID </td>";
                echo "<td width=\"60\"> PUSER </td>";
                echo "<td> A1 </td>";
                echo "<td> A2 </td>";
                echo "<td> A3 </td>";
                echo "<td width=\"20\"> A4 </td>";
                echo "<td> A5 </td>";
                echo "<td> A6 </td>";
                echo "<td> A7 </td>";
                echo "<td> A8 </td>";
                echo "<td width=\"20\"> A9 </td>";
                echo "<td> D1 </td>";
                echo "<td> D2 </td>";
                echo "<td> D3 </td>";
                echo "<td width=\"20\"> D4 </td>";
                echo "<td> D5 </td>";
                echo "<td> D6 </td>";
                echo "<td> D7 </td>";
                echo "<td width=\"20\"> D8 </td>";
                echo "<td> TW </td>";
                echo "<td> INT </td>";
                echo "<td></td></tr></h4>";

                $frage =  "SELECT * FROM bftopassini;";
                $ergebnis = $mysqli->query($frage);
                $cnt = 0;
                while ($zeile = $ergebnis->fetch_array()) {
                    $tstr = "<td><a href=\"settings.php?ACTION=EDIT&UID=" . $zeile['ID'] . "&JOB=BFINI\">";
                    echo "<tr>";
                    echo "<td>" . $zeile['ID'] . "</td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['PUSER'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A1&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN1EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A2&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN2EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A3&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN3EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A4&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN4EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A5&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN5EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A6&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN6EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A7&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN7EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A8&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN8EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=A9&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['AN9EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D1&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI1EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D2&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI2EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D3&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI3EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D4&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI4EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D5&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI5EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D6&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI6EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D7&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI7EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=D8&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['DI8EN'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=X1&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['TWSWAL'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=EDIT2&CH=X1&UID=" . $zeile['ID'] . "&JOB=BFINI\">" . $zeile['INTERVAL'] . "</a></td>";
                    echo "<td><a href=\"settings.php?ACTION=SHOW&UID=" . $zeile['ID'] . "&JOB=BFINI\">   show</a></td>";
                    echo "</tr>";
                    $cnt = $cnt + 1;
                    if ($cnt > 4) {
                        $cnt = 0;
                        echo  "<tr><td>&nbsp;</td></tr>";
                    }
                }
                echo "</table>";
                $ergebnis->close();
            }

            // AKTION----------------------------------------------------------------------------------------------------------
            echo "<br><h4> Aktion </h4>";
            echo "<form action=\"settings.php\">";
            echo "<select name=\"ACTION\">";
            echo "<option value=\"0\">Choose Action</option>";
            echo "<option value=\"SHOW\">Show Project Ini</option>";
            echo "<option value=\"EDIT\">Edit Project Ini</option>";
            echo "<option value=\"NEW0\">New Empty Project Ini</option>";
            echo "<option value=\"NEW1\">New Copy From</option>";
            echo "<option value=\"DEL\">Delete Project Ini</option>";
            echo "</select>";

            echo "<select name=\"UID\">";
            echo "<option value=\"0\">Choose</option>";
            $frage =  "SELECT * FROM bftopassini;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                echo "<option value=\"" . $zeile['ID'] . "\">" . $zeile['PUSER'] . "</option>";
            }
            echo "</select></p>";

            $ergebnis->close();
            echo "<input type=\"hidden\" name=\"JOB\" value=\"BFINI\"/>";
            echo "<input type=\"submit\" value=\"Submit\"></form><br><br>";
        }

        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // ----------------------------------------------------------------------------------------------------------------------------------------
        // $TODO: BFUSER HAS PROJEKT
        // ----------------------------------------------------------------------------------------------------------------------------------------

        if ($job == "USERHASP") {

            // NEW EMPTY----------------------------------------------------------------------------------------------------------
            if ($action == "NEW0") {
            }

            // �bersicht----------------------------------------------------------------------------------------------------------

            $frage =  "SELECT * FROM bfuser;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                $bfuser[$zeile['ID']] = $zeile['USER'];
            }
            $ergebnis->close();

            $frage =  "SELECT * FROM bfprojekt;";
            $ergebnis = $mysqli->query($frage);
            while ($zeile = $ergebnis->fetch_array()) {
                $puser[$zeile['ID']] = $zeile['PUSER'];
            }
            $ergebnis->close();


            $frage =  "SELECT * FROM bfuser_has_bfprojekt ORDER BY bfuser_ID;";
            $ergebnis = $mysqli->query($frage);
            $merk = "";
            $cnt = 0;
            while ($zeile = $ergebnis->fetch_array()) {
                if ($merk != $zeile['bfuser_ID']) {
                    if (strlen($bfuser[$zeile['bfuser_ID']]) > 5) {  // user 
                        echo "<p><b>" . $bfuser[$zeile['bfuser_ID']] . "&nbsp;&nbsp; <br></b>";
                    } else {
                        echo  "<p><b>" . $bfuser[$zeile['bfuser_ID']] . "nop (" . $zeile['bfuser_ID'] . ")<br></b>";
                    }
                    $cnt = 0;
                }
                if ($cnt >= 5) {
                    $cnt = 0;
                    echo "<br>";
                }
                $cnt = $cnt + 1;

                // project
                if (strlen($puser[$zeile['bfprojekt_ID']]) > 2) {
                    echo "&nbsp;" . $puser[$zeile['bfprojekt_ID']];
                } else {
                    $puser[$zeile['bfprojekt_ID']] = $puser[$zeile['bfprojekt_ID']]  . "nop" . $zeile['bfprojekt_ID'];
                    echo "&nbsp;" . $puser[$zeile['bfprojekt_ID']] . "(" . $zeile['bfprojekt_ID'] . ") ";
                }

                $merk = $zeile['bfuser_ID'];
            }


            $ergebnis->close();

            // AKTION----------------------------------------------------------------------------------------------------------
            echo "<br><h4> Aktion </h4>";
            echo "<form action=\"settings.php\">";
            echo "<select name=\"ACTION\">";
            echo "<option value=\"0\">Choose Action</option>";
            echo "<option value=\"DEL1\">Delete User</option>";
            echo "<option value=\"DEL2\">Delete Project</option>";
            echo "<option value=\"DEL3\">Delete Pair</option>";
            echo "<option value=\"NEW\">New</option>";
            echo "</select>";

            echo "<select name=\"UID\">";
            echo "<option value=\"0\">Choose</option>";
            foreach ($bfuser as $key => $wert) {
                //echo "<br>key: " .$key ." / wert: " .$wert;
                echo "<option value=\"" . $key . "\">" . $wert . "</option>";
            }
            echo "</select>";

            echo "<select name=\"PID\">";
            echo "<option value=\"0\">Choose</option>";
            foreach ($puser as $key => $wert) {
                //echo "<br>key: " .$key ." / wert: " .$wert;
                echo "<option value=\"" . $key . "\">" . $wert . "</option>";
            }
            echo "</select></p>";

            echo "<input type=\"hidden\" name=\"JOB\" value=\"USERHASP\"/>";
            echo "<input type=\"submit\" value=\"Submit\"></form><br><br>";
        }

        // ------------------------------------------------------------------------------------------------
        // Haupauswertung
        // ------------------------------------------------------------------------------------------------ 

        // wochenlinks
        // echo "<div class=\"containerdates\">";

        //  echo "</div>"; 


        // ------------------------------------------------------------------------------------------------
        // Tabelle
        // ------------------------------------------------------------------------------------------------ 

        // $frage = "SELECT * FROM bftopass ORDER BY ZEIT DESC LIMIT 50;";
        // $ergebnis = $mysqli->query($frage);
        // echo "<div class=\"containertable\"><dl>";
        // echo "<dt><a href=\"#\">Aktuelle Daten</a></dt>";
        // echo "<dd><table id=\"datatable\">";
        // echo "<tr><td>ID<TD>USER<TD>Server Zeit<TD>Remote TS<TD>D1<TD>D2<TD>D3<TD>D4<TD>A1<TD>A2<TD>dt[min]" ;
        // $xold = 0;
        // while($zeile = $ergebnis->fetch_array()){
        // if ($xold != 0){
        // $x = ((int)(intval($zeile['ZEIT']))-$xold)/60;   
        // echo "<td>".round($x,1) ."</td>";
        // }   
        // $xold = (int)(intval($zeile['ZEIT']));
        // echo "</tr>";
        // echo "<tr><td>".htmlspecialchars($zeile['ID'])."</td>";
        // echo "<td>".htmlspecialchars($zeile['PUSER'])."</td>";
        // echo "<td>".htmlspecialchars(date('d.m.Y H:i:s',$zeile['ZEIT']))."</td>";
        // echo "<td>".htmlspecialchars($zeile['RTS'])."</td>";
        // echo "<td>".intval($zeile['D1'])."</td>";
        // echo "<td>".intval($zeile['D2'])."</td>";
        // echo "<td>".intval($zeile['D3'])."</td>";
        // echo "<td>".intval($zeile['D4'])."</td>";   
        // echo "<td>".intval($zeile['D5'])."</td>";
        // echo "<td>".intval($zeile['D6'])."</td>";
        // echo "<td>".intval($zeile['D7'])."</td>";
        // echo "<td>".intval($zeile['D8'])."</td>";
        // echo "<td>".floatval($zeile['A1'])."</td>";
        // echo "<td>".floatval($zeile['A2'])."</td>";   
        // echo "<td>".floatval($zeile['A3'])."</td>";
        // echo "<td>".floatval($zeile['A4'])."</td>";   
        // echo "<td>".floatval($zeile['A5'])."</td>";
        // echo "<td>".floatval($zeile['A6'])."</td>";   
        // echo "<td>".floatval($zeile['A7'])."</td>";
        // echo "<td>".floatval($zeile['A8'])."</td>";
        // }
        // echo "<td></tr></table></dd></div>";
        // $ergebnis->close();


    } else {  // kein admin / kein login
        echo "<br><a href=\"../index.php\">Bitte Anmelden</a><br>";
    }

    // ------------------------------------------------------------------------------------------------
    // Ende
    // ------------------------------------------------------------------------------------------------ 

    $mysqli->close();

    echo "<br>";
    echo "<table border='0' cellpadding='3' cellspacing='0' bgcolor='#DDDDDD' width='740'><tr><td>";
    echo "status generated on: " . date('y/m/d,H:i:s') . "<br>";
    echo "remote ip: " . $tip . "<br>";
    echo "(c) 2016 <a href=\"http://www.lanthan.eu\">Lanthan GmbH & Co. KG </a>";
    echo "</td></tr></table>";
    ?>
</body>

</html>