<?PHP
session_start();
?>

<?PHP

include "sub/header.php";
require_once "db.php";

$passed = 0;
// aufrufende website IP-Adresse
$tip =  $_SERVER['REMOTE_ADDR'];

if (isset($_SESSION['cnt'])) {
    $_SESSION['cnt']++;
}

?>
<!-- Custom styles for this template -->
<link href="css/loginform.css" rel="stylesheet">

</head>

<body>
    <?PHP

    // -----------------------------------------------------------------------------------------
    // LOGOUT 
    // ------------------------------------------------------------------------------------------
    if (htmlspecialchars($_REQUEST['LOGOUT']) != '') {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
        }
        session_destroy();
    }


    // ------------------------------------------------------------------------------------------
    // LOGIN Checken
    // ------------------------------------------------------------------------------------------
    if (isset($_POST["USER"]) && isset($_POST["PASS"])) {
        $tuser = $_POST["USER"];
        $tuser = htmlentities($tuser);
        $tpass = $_POST["PASS"];
        $tpass = htmlentities($tpass);
        $tmerk = $_POST["HIRN"];
        $tmerk = htmlentities($tmerk);


        $ergebnis = $mysqli->query("SELECT * FROM bfuser ORDER BY USER;");
        while ($zeile = $ergebnis->fetch_array()) {
            $szeit = time();
            if ($tuser == $zeile['USER']) {
                if (password_verify($tpass, $zeile['PASSW'])) {
                    if (!isset($_SESSION['userid'])) {
                        $_SESSION['userid'] = $zeile['ID'];
                        $_SESSION['admin'] = $zeile['ADMIN'];
                        $_SESSION['viewall'] = $zeile['VIEWALL'];
                        $_SESSION['superadmin'] = $zeile['SUPERADMIN'];
                        $_SESSION['user'] = $zeile['USER'];
                        $_SESSION['cnt'] = 0;
                    }
                }
            }
        }
        $ergebnis->close();
    }



    // ------------------------------------------------------------------------------------------
    // SESSION User und Rechte 
    // ------------------------------------------------------------------------------------------
    $sesOK = 0;
    $sesUser = "";
    $sesUserid = 0;
    $sesAdmin = 0;
    $sesViewall = 0;
    $sesSuperadmin = 0;

    if (isset($_SESSION['userid'])) {
        $sesUserid = htmlentities($_SESSION['userid']);
        $sesOK = 1;
        if (isset($_SESSION['user'])) {
            $sesUser = htmlentities($_SESSION['user']);
        }
        if (isset($_SESSION['admin'])) {
            $sesAdmin = htmlentities($_SESSION['admin']);
        }
        if (isset($_SESSION['viewall'])) {
            $sesViewall = htmlentities($_SESSION['viewall']);
        }
        if (isset($_SESSION['superadmin'])) {
            $sesSuperadmin = htmlentities($_SESSION['superadmin']);
        }
    }


    // ------------------------------------------------------------------------------------------
    // LOGIN
    // ------------------------------------------------------------------------------------------
    if ($sesOK == 0) {

    ?>
        <div class="container">
            <br><br>
            <form method="post" action="index.php">
                <div class="form-signin">
                    <img class="img-responsive" src="./image/lanthan_status_600x150.jpg" width="600" height="150">
                    <br><br>
                    <h2 class="text-center">Cloud Monitoring</h2>
                    Please sign in
                    <label for="inputuser" class="sr-only">Name:</label>
                    <input type="text" name="USER" class="form-control" id="inputuser" placeholder="user" required autofocus>
                    <label for="inputpass" class="sr-only">Password:</label>
                    <input type="password" name="PASS" class="form-control" id="inputpass" placeholder="***">
                    <!--
        <div class="checkbox">
          <label>
          <input type="checkbox" name="HIRN" value="MERK"> Remember me
          </label>
        </div>
        -->
                    <button class="btn btn-lg btn-basic btn-block" type="submit" value="login">Submit</button>
            </form>
            <a href="http://www.lanthan.eu/de/impressum.html">Impressum: Lanthan GmbH &amp; Co. KG &copy; 2018</a><br>
            Due to security reasons all IP-adresses will be loged
        </div>
    <?php
    }


    // ------------------------------------------------------------------------------------------
    //
    // ------------------------------------------------------------------------------------------
    //  Session bereits gestartet / Seiten Menü
    // ------------------------------------------------------------------------------------------  
    if ($sesOK == 1) {

    ?>

        <!--Menue -->
        <nav class="navbar navbar-inverse">
            <div class="container">
                <div class="navbar-header">
                    <img src="./image/lanthan_logo_150x130.jpg" with="100" height="50">
                </div>
                <ul class="nav navbar-nav">
                    <li class="active"><a href="https://l-cloud.lanthan.eu/index.php">Home</a></li>
                    <li class="active"><a href="help.html">Help</a></li>
                    <li><a href="http://status.lanthan.eu/cciomap.php">Map</a></li> <!-- instead of # I added a link to map -->
                    <?php
                    if ($sesAdmin == 1) { ?>
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="./xdit/edit.php">Edit
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="./xdit/user.php?JOB=NEW">New User</a></li>
                                <li><a href="./xdit/user.php?JOB=EDIT">Edit User</a></li>
                                <li><a href="./xdit/projekt.php?JOB=NEW">New Project</a></li>
                                <li><a href="./xdit/projekt.php?JOB=EDIT">Edit Project</a></li>
                                <li><a href="./xdit/projekt.php?JOB=FIND">Find New Project</a></li>
                                <li><a href="./XYA/ADM450.php?server=localhost&username=lanthan-01&db=lanthan_test&pass=7VrBi-.GXTZCF" target="_blank">Adminer</a></li>
                            </ul>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="xdit/user.php?JOB=EDIT&USERID=<?php echo $sesUserid; ?>"><span class="glyphicon glyphicon-user"></span> <?php echo $sesUser; ?></a></li>
                    <li><a href="index.php?LOGOUT=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
                </ul>
            </div>
        </nav>



        <div class="container">
            <!--seitencontainer -->



            <?php
            //-----------------------------------------------------------------------------------  
            // aktive Projekte
            ?>
            <!--Tabellenkopf und Filter -->
            <script>
                $(document).ready(function() {
                    $("#Filter1Input").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $("#Filter1Table tr").filter(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                        });
                    });
                });
            </script>

            <div class="row">
                <div class="col">
                    <h3 class="text-center">Active System Summary <?php echo date('d.m.Y'); ?> </h3>
                    <input class="form-control" id="Filter1Input" type="text" placeholder="Filter..">
                </div>
            </div>

            <table class="table sortable table-responsive table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Nr</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>P-Nr</th>
                        <th>Project</th>
                        <th>Location-Nr / Street </th>
                        <th>Last Data</th>
                    </tr>
                </thead>
                <tbody id="Filter1Table">
                    <?php
                    $date =   "2020-01-01 00:00";;
                    $this_year =  strtotime($date);

                    $pcount = 0;
                    if (($sesSuperadmin == 1) || ($sesViewall == 1)) {
                        //$frage = "SELECT * FROM bfprojektini WHERE AKTIV = '1';";   
                        $frage = "SELECT * FROM bfprojektini AS bpi JOIN bfprojektstatus2_new as bps WHERE bpi.AKTIV = '1' AND bps.LASTDATA > $this_year AND bpi.PUSER = bps.PUSER"; //alt listet nur Projekte die auch vom Status erfasst wurden 

                    } else {
                        ////  changed 11/5
                        //  $frage = "SELECT * FROM bfuserhasprojekt AS bhp JOIN bfprojektini AS bpi JOIN bfprojektstatus as bps WHERE bhp.userID = '$sesUserid' AND bhp.projektiniID = bpi.ID AND bpi.AKTIV = '1' AND bpi.PUSER = bps.PUSER;";

                        $frage = "SELECT * FROM bfuserhasprojekt AS bhp JOIN bfprojektini AS bpi JOIN bfprojektstatus2_new as bps WHERE bhp.userID = '$sesUserid' AND bhp.projektiniID = bpi.ID AND bpi.AKTIV = '1' AND bps.LASTDATA > $this_year AND bpi.PUSER = bps.PUSER;";
                        //$frage = "SELECT * FROM bfuser_has_bfprojekt AS bt JOIN bfprojekt AS bp JOIN bfprojektstatus as bps WHERE bt.bfuser_ID = '$quser' AND bt.bfprojekt_ID = bp.ID AND bp.AKTIV = '1' AND bp.PUSER = bps.PUSER;";
                    }
                    $ergebnis = $mysqli->query($frage);
                    while ($zeile = $ergebnis->fetch_array()) {
                        $suser = $zeile['USER'];
                        $spuser = $zeile['PUSER'];
                        $sort = $zeile['ORT'];
                        $sort2 = $zeile['ORT2'];
                        $sdb = $zeile['DB'];
                        $sid = $zeile['ID'];
                        $serr1 = $zeile['ERROR'];
                        $serr2 = $zeile['TIMEOUT'];
                        $datazeit = $zeile['LASTDATA'];
                        $serrX = floatval($serr2) + floatval($serr1) * 2;
                        $pcount = $pcount + 1;
                        if ($datazeit == 0) {
                            $datatext = "--.--.---- --:--:--";
                        } else {
                            $datatext = date('d.m.Y H:i:s', $datazeit);
                        }

                        echo "      <tr>\n\r";
                        echo "          <td>" . $pcount  . "</td>\n\r";
                        echo "          <td sorttable_customkey=\"" . $serrX . "\"><img src=\"image/ampel_" . $serrX . ".png\"> </td>\n\r";
                        echo "          <td>" . $suser  . "</td>";
                        echo "          <td>" . "<a href=\"view/viewstd.php?WW=1&PUSER=" . $spuser . "&PID=" . $sid . "&TIME=" . $datazeit . "\">" . $spuser  . "</a></td>\n\r";
                        echo "          <td>" . $sort  . "</td>\n\r";
                        echo "          <td>" . $sort2  . "</td>\n\r";
                        echo "          <td sorttable_customkey=\"" . $datazeit . "\"><a href=\"view/" . $sdb . "view.php?WW=1&PUSER=" . $spuser . "&PID=" . $sid . "&TIME=" . $datazeit . "\">" . $datatext  . "</td>\n\r";
                        echo "        </tr>\n\r";
                    }
                    echo "      </tbody>\n\r";
                    echo "    </table>\n\r";
                    echo "\n\r";
                    $ergebnis->close();


                    //-----------------------------------------------------------------------------------
                    // inaktive Projekte

                    ?>
                    <!--Tabellenkopf und Filter -->
                    <script>
                        $(document).ready(function() {
                            $("#Filter2Input").on("keyup", function() {
                                var value = $(this).val().toLowerCase();
                                $("#Filter2Table tr").filter(function() {
                                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                                });
                            });
                        });
                    </script>

                    <div class="row">
                        <div class="col">
                            <h3 class="text-center">Inanctive Systems <?php echo date('d.m.Y'); ?> </h3>
                            <!-- <h3 class="text-center">Recent Inanctive Systems <?php echo date('d.m.Y'); ?> </h3> -->
                            <input class="form-control" id="Filter2Input" type="text" placeholder="Filter..">
                        </div>
                    </div>

                    <table class="table sortable table-responsive table-condensed table-hover">
                        <thead>
                            <tr>
                                <th>Nr</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>P-Nr</th>
                                <th>Project</th>
                                <th>Location-Nr / Street </th>
                                <th>Last Data</th>
                            </tr>
                        </thead>
                        <tbody id="Filter2Table">
                        <?php

                        if (($sesSuperadmin == 1) || ($sesViewall == 1)) {
                            $frage = "SELECT * FROM bfprojektini AS bpi JOIN bfprojektstatus2_new as bps WHERE (bpi.AKTIV = '0' OR bps.LASTDATA < $this_year) AND bpi.PUSER = bps.PUSER  ORDER BY LASTDATA";
                            //$frage = "SELECT * FROM bfprojekt AS bp JOIN bfprojektstatus as bps WHERE bp.AKTIV = '0' AND bp.PUSER = bps.PUSER";
                        } else {
                            $frage = "SELECT * FROM bfuserhasprojekt AS bhp INNER JOIN bfprojektini AS bpi WHERE bhp.userID = '$sesUserid' AND bhp.projektiniID = bpi.ID AND (bpi.AKTIV = '0' OR bps.LASTDATA < $this_year) ORDER BY LASTDATA;";
                            //$frage = "SELECT * FROM bfuser_has_bfprojekt AS bt INNER JOIN bfprojekt AS bp WHERE bt.bfuser_ID = '$quser' AND bt.bfprojekt_ID = bp.ID AND bp.AKTIV = '0';";
                        }
                        $ergebnis = $mysqli->query($frage);
                        while (!empty($zeile = $ergebnis->fetch_array())) {
                            $suser = $zeile['USER'];
                            $spuser = $zeile['PUSER'];
                            $sort = $zeile['ORT'];
                            $sort2 = $zeile['ORT2'];
                            $sdb = $zeile['DB'];
                            $sid = $zeile['ID'];
                            $serr1 = $zeile['ERROR'];
                            $serr2 = $zeile['TIMEOUT'];
                            $datazeit = $zeile['LASTDATA'];
                            $serrX = floatval($serr2) + floatval($serr1) * 2;
                            $pcount = $pcount + 1;
                            if ($datazeit == 0) {
                                $datatext = "--";
                            } else {
                                $datatext = date('d.m.Y H:i:s', $datazeit);
                            }
                            // $serrX = -1;

                            echo "        <tr>\n\r";
                            echo "          <td>" . $pcount  . "</td>\n\r";
                            echo "          <td sorttable_customkey=\"" . $serrX . "\"><img src=\"image/ampel_hellgrau.png\"> </td>\n\r";
                            echo "          <td>" . $suser  . "</td>\n\r";
                            echo "          <td>" . "<a href=\"view/viewstd.php?WW=1&PUSER=" . $spuser . "&PID=" . $sid . "&TIME=" . $datazeit . "\">" . $spuser  . "</a></td>\n\r";
                            echo "          <td>" . $sort  . "</td>\n\r";
                            echo "          <td>" . $sort2  . "</td>\n\r";
                            echo "          <td sorttable_customkey=\"" . $datazeit . "\"><a href=\"view/" . $sdb . "view.php?WW=1&PUSER=" . $spuser . "&PID=" . $sid . "&TIME=" . $datazeit . "\">" . $datatext  . "</td>\n\r";
                            echo "        </tr>\n\r";
                        }
                        echo "      </tbody>\n\r";
                        echo "    </table>\n\r";
                        echo "\n\r";
                        $ergebnis->close();
                    }

                    //-----------------------------------------------------------------------------------
                    //LasData
                    if ($sesSuperadmin == 1) {

                        $frage = "SELECT * FROM bftopass ORDER BY ZEIT DESC LIMIT 40;";

                        //$frage = "SELECT * FROM bftopass ORDER BY ZEIT DESC LIMIT 40;";
                        $ergebnis = $mysqli->query($frage);

                        //echo "  <div class=\"container\">\n\r";
                        echo "    <table class=\"table table-responsive table-condensed table-hover\">\n\r";
                        echo "      <tr>\n\n";
                        echo "        <td>Zeit</td>\n\r";
                        echo "        <td>PUser</td>\n\r";
                        echo "        <td>D1-8</td>\n\r";
                        echo "        <td>A1-4</td>\n\r";
                        echo "        <td>A9</td>\n\r";
                        echo "        <td>TXT</td>\n\r";
                        echo "      </tr>\n\r";

                        while ($row = $ergebnis->fetch_array()) {
                            echo "      <tr>\n\r";
                            echo "        <td>" . htmlspecialchars(date('H:i:s', $row['ZEIT'])) . "</td>\n\r";
                            echo "        <td>" . htmlspecialchars($row['PUSER']) . "</td>";
                            echo "        <td>" . intval($row['D1']) . intval($row['D2']) . intval($row['D3']) . intval($row['D4']) . " ";
                            echo intval($row['D5']) . intval($row['D6']) . intval($row['D7']) . intval($row['D8']) . "</td>\n\r";
                            echo "        <td>" . round(floatval($row['A1']), 1) . "-" . round(floatval($row['A2']), 1) . "-" . round(floatval($row['A3']), 1) . "-" . round(floatval($row['A4']), 1) . "</td>\n\r";
                            echo "        <td>" . round(floatval($row['A9']), 1) . "</td>\n\r";
                            echo "        <td>| " . htmlspecialchars($row['TEXT']) . "</td>";
                            echo "      </tr>\n\r";
                        }
                        echo "      </table>\n\r";
                        //echo "  </div>\n\r";
                        $ergebnis->close();
                        $mysqli->close();
                    }


                    //-----------------------------------------------------------------------------------
                    // FOOTER.
                    if ($sesOK == 1) {
                        echo "<br>\n\r";
                        echo "status generated on: " . date('y/m/d,H:i:s') . "<br>\n\r";
                        echo "&copy; 2018 <a href=\"http:\\\\www.lanthan.eu\">Lanthan GmbH & Co. KG </a>\n\r";
                        echo "Remote Adress:: " . $tip . "<br>\n\r";
                        echo "</div>\n\r";
                    }

                    include "./sub/footer.php";
                        ?>