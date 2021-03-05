<?PHP
  session_start();
?>

<?PHP

include "../sub/header.php"; 
require_once "../sub/base.php";

$passed = 0;
// aufrufende website IP-Adresse
$tip =  $_SERVER['REMOTE_ADDR'];

if (isset($_SESSION['cnt'])) {
  $_SESSION['cnt']++;
}

?>
<!-- START custom script section -->

  <!-- datepicker -->
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
  <script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
  <script>
    $(function() {
        $( "#datepicker" ).datepicker({
            showOn: "both",
            buttonImage: "../image/calendar.gif",
            buttonImageOnly: true,
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            showWeek: true,
            dateFormat: "yy-mm-dd"
        });
    });
  </script>

<!-- END custom script section -->

</head>
<body>

<?PHP

// ------------------------------------------------------------------------------------------
// LOGOUT 
// ------------------------------------------------------------------------------------------
if (htmlspecialchars($_REQUEST['LOGOUT'])!=''){
  $_SESSION = array();
  if (isset($_COOKIE[session_name()])) {
  }
  session_destroy();
}
  

// ------------------------------------------------------------------------------------------
// LOGIN Checken
// ------------------------------------------------------------------------------------------
if (isset($_POST["USER"]) && isset($_POST["PASS"])){
  $tuser = $_POST["USER"];
  $tuser = htmlentities($tuser);
  $tpass = $_POST["PASS"];
  $tpass = htmlentities($tpass);
  $tmerk = $_POST["HIRN"];
  $tmerk = htmlentities($tmerk);
    
  
  $ergebnis = $mysqli->query("SELECT * FROM bfuser ORDER BY USER;");
  while($zeile = $ergebnis->fetch_array()){
    $szeit = time();
  	if ($tuser == $zeile['USER']){
    	if (password_verify($tpass, $zeile['PASSW'])){
        if (!isset($_SESSION['userid'])){
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

if (isset($_SESSION['userid'])){
  $sesUserid = htmlentities($_SESSION['userid']);
  $sesOK = 1;
  if (isset($_SESSION['user'])){
    $sesUser = htmlentities($_SESSION['user']);
  }  
  if (isset($_SESSION['admin'])){
    $sesAdmin = htmlentities($_SESSION['admin']);
  }
  if (isset($_SESSION['viewall'])){
    $sesViewall = htmlentities($_SESSION['viewall']);
  }  
  if (isset($_SESSION['superadmin'])){
    $sesSuperadmin = htmlentities($_SESSION['superadmin']);
  }
}

// ------------------------------------------------------------------------------------------
// Not Logged in
// ------------------------------------------------------------------------------------------
if ($sesOK == 0){
  
  ?>
  <div class="container">
    <br><br>
    <img class="img-responsive" src="../image/lanthan_status_600x150.jpg" width="600" height="150">
    <br><br>
    <h2 class="text-center">Cloud Monitoring</h2>
    <a href="../index.php">Please sign in!</a><br>
    <a href="http://www.lanthan.eu/de/impressum.html">Impressum: Lanthan GmbH &amp; Co. KG &copy; 2018</a><br>
  </div>
  <?php
}


// ------------------------------------------------------------------------------------------
// Deklarations / Eingabeabfrage
// ------------------------------------------------------------------------------------------ 
//$tpid =  intval(htmlspecialchars($_REQUEST['PID']));   // projet id
$tpuser =  htmlspecialchars($_REQUEST['PUSER']);   // projet id
$tstamp =  intval(htmlspecialchars($_REQUEST['TIME']));  //  mitgegebener timestamp
$ww =  htmlspecialchars($_REQUEST['WW']);     // ww-WholeWeek: 0-tag, 1-woche, 2-2wochen, 3-monat

echo "pid" .$tpid ." / puser " .$tpuser ." / T " .$tstamp ."\n\r";

if (isset($_GET["LW"])){
  $lw =htmlspecialchars($_REQUEST['LW']);
}
 
if (isset($_GET["dpdate"])){
  $dpdate = htmlspecialchars($_REQUEST['dpdate']);     // datum vom Datepicker
  $tstamp = strtotime($dpdate);
  //echo $dpdate ."  " .$tstamp ."  " .date('d.m.Y',$tstamp);
}

if ($tstamp < 946684800) {// <1.1.2000 oder kein timestamp
  $tstamp = time();
}

$m = date('m',$tstamp);
$d = date('d',$tstamp);
$y = date('Y',$tstamp);
$tstamp2 = mktime(23,59,59,$m,$d,$y);   // tstamp 23:59:59h des tages von tstamp
$tstamp1 = mktime(0,0,0,$m,$d,$y);    // tstamp 0:00:00h des tages von tstamp
if ($ww == '1'){
  $tstamp1 = mktime(0,0,0,$m,$d,$y) - 518400; // tstamp 0:00:00h 6 tage vor tstamp
}

$fdom = mktime(0,0,0,$m,1,$y);
$dim = date('t',$tstamp);
$ldom = mktime(23,59,59,$m,$dim,$y);
$fdow = $tstamp2 - 604799;  // 6*24h*60min*59s vorher
$tgestern = $tstamp - 86400;          // tstamp gestern
$tmorgen = $tstamp + 86400;           // tstamp morgen

$letztewoche = $tstamp - 604800;      // tstamp vor 7 tagen
$naechstewoche = $tstamp + 604800;    //
$letztermonat = $fdom - 604800;      // tstamp vor 7 tagen
$naechstermonat = $ldom + 604800;    //


// -------------------------------------------------------
// Projektdaten / Einstellungen Laden
// -------------------------------------------------------
if ($sesOK == 1){


 

// $program->close();

  // ----- Prokektdaten
  $frage = "SELECT * FROM bfprojektini WHERE PUSER = '$tpuser';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
    $spuser = $zeile['PUSER'];
    $sort = $zeile['ORT'];
    $sort2 = $zeile['ORT2'];
    $sland = $zeile['LAND'];
    $db_table = $zeile['DB'];
    // $swgs84b = floatval($zeile['WGS84B']);
    // $swgs84l = floatval($zeile['WGS84L']);

    for ($n = 1; $n < 21; $n++){
      $nvar = "DI" .$n ."NAME";
      $DxNAME[$n] = $zeile[$nvar];     
      $nvar = "DI" .$n ."EN";
      $DxEN[$n] = $zeile[$nvar];     
      $nvar = "DI" .$n ."L";
      $DxL[$n] = $zeile[$nvar];
      $nvar = "DI" .$n ."H";
      $DxH[$n] = $zeile[$nvar];
      $nvar = "DI" .$n ."ALERT";
      $DxALERT[$n] = $zeile[$nvar];      
      $nvar = "DI" .$n ."ALVAL";
      $DxALVAL[$n] = $zeile[$nvar];      
      $nvar = "DI" .$n ."NOE";
      $DxNOE[$n] = $zeile[$nvar];    
    }
    
    for ($n = 1; $n < 21; $n++){
      $nvar = "AN" .$n ."NAME";
      $ANxNAME[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."EN";
      $ANxEN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."ISDIGITAL";
      $ANxISDIGITAL[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."MIN";
      $ANxMIN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."SCALE";
      $ANxSCALE[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."MAX";
      $ANxMAX[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."AL1LVL";
      $ANxAL1LVL[$n] = floatval($zeile[$nvar]);
      $nvar = "AN" .$n ."AL2LVL";
      $ANxAL2LVL[$n] = floatval($zeile[$nvar]);      
      $nvar = "AN" .$n ."AL1TXT";
      $ANxAL1TXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."AL2TXT";
      $ANxAL2TXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."OKTXT";
      $ANxOKTXT[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."AL1EN";
      $ANxAL1EN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."AL2EN";
      $ANxAL2EN[$n] = $zeile[$nvar];
      $nvar = "AN" .$n ."DALVAL";
      $ANxDALVAL[$n] = $zeile[$nvar];      
      $nvar = "AN" .$n ."NOE";
      $ANxNOE[$n] = $zeile[$nvar];
    }
    $TIMEOUT = intval($zeile['INTERVAL']); 
  }

// adding name of the program f190415h03 and lat/lon with uncertainty 
// $progra =  $mysqli->query ("SELECT imei, ver, ID,pos FROM bdccio WHERE bfccio.imei = '$tpuser' ORDER BY ID DESC LIMIT 1");
 $progra =  $mysqli->query ("SELECT imei, ver, ID,pos FROM test WHERE test.imei = '$tpuser' ORDER BY ID DESC LIMIT 1");
 while($row = $progra->fetch_array()){ 
         $prname =$row['ver'];
         $pos = $row['pos'];
         $position= substr($pos, 24, -14);
         list($pos1, $pos2, $p0, $p1) = explode(",", $position);
         $p1= sprintf("%.1f", ($p1/1000));
         $swgs84b = floatval($pos1);
         $swgs84l = floatval($pos2);
   
      }
       $progra ->close();

// ------ Fehlerstatus
  $frage = "SELECT * FROM bfprojektstatus2 WHERE PUSER = '$spuser';";
  $ergebnis = $mysqli->query($frage);
  while($zeile = $ergebnis->fetch_array()){
    $serr1 = $zeile['ERROR'];
    $serr2 = $zeile['TIMEOUT'];
    $datazeit = $zeile['LASTDATA'];
    $derr = $zeile['DERR'];
    $a1err = $zeile['A1ERR'];
    $a2err = $zeile['A2ERR'];
    $serrX = floatval($serr2) + floatval($serr1)*2;
  }
  $ergebnis->close();
  
  //fehler aufbereiten
  for ($n = 1; $n < 21; $n++)
  {
    $dxerr[$n] = substr($derr,(-1* $n),1);
    $a1xerr[$n] = substr($a1err,(-1* $n),1);
    $a2xerr[$n] = substr($a2err,(-1* $n),1);
    $axerr[$n] = $a1xerr[$n] || $a2xerr[$n];
  }

}

// ------------------------------------------------------------------------------------------
//
// ------------------------------------------------------------------------------------------
//  Session bereits gestartet / Seiten Menü
// ------------------------------------------------------------------------------------------  
if ($sesOK == 1){

  ?>
  
  <!--Menue -->  
  <nav class="navbar navbar-inverse">
    <div class="container">
      <div class="navbar-header">
        <img src="../image/lanthan_logo_150x130.jpg" with="100" height="50">
      </div>
      <ul class="nav navbar-nav">
        <li class="active"><a href="../index.php">Home</a></li>
        <li class="active"><a href="../help.html">Help</a></li>
        <li><a href="#">Map</a></li>
        <!-- <li class="active"><a href="../test/test.php">Archive</a></li> -->

        <?php 
          if ($sesAdmin == 1){ ?>
            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="../xdit/edit.php">Edit
              <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="../xdit/user.php?JOB=NEW">New User</a></li>
                <li><a href="../xdit/user.php?JOB=EDIT">Edit User</a></li>
                <li><a href="../xdit/projekt.php?JOB=NEW">New Project</a></li>
                <li><a href="../xdit/projekt.php?JOB=EDIT">Edit Project</a></li>

                <li><a href="../XYA/ADM450.php?server=rdbms.strato.de&username=U969575&db=DB969575" target="_blank">Adminer</a></li>
              </ul>
            </li>           
            <?php
          }        
        ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="../xdit/user.php?JOB=EDIT&USERID=<?php echo $sesUserid;?>"><span class="glyphicon glyphicon-user"></span> <?php echo $sesUser;?></a></li>
        <li><a href="../index.php?LOGOUT=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container"> <!--seitencontainer -->
  <?php
  
  // --------------------------------------------------------------------------------
  // Project data
  // --------------------------------------------------------------------------------
  
  echo "<div class=\"panel panel-default\">\n\r";
  echo "  <div class=\"panel-body\">\n\r";
  echo "    <div class=\"row\">\n\r";
  
  // ----- Projekt & Location  
  echo "    <div class=\"col-sm-4\">\n\r";
  echo "    <b>" .$tpuser .", " .$sort .", " .$sland ."</b></br>\n\r";
  echo "    <b>" .$sort2 ."</b></br>\n\r";
  echo "      <a href=\"https://maps.google.com/maps?q=" .$swgs84b ."," .$swgs84l ."\" target=\"_new\">\n\r";
  echo "        WGS84: " .$swgs84b .", " .$swgs84l . "\n\r";
  echo "       </a>. <strong> &#177; ". $p1 ." Km </strong> \n\r";
  echo "    <b></br>\n\r" .$prname ."</br>\n\r";
  // echo "    <b></br>\n\r" .$prname ."</b></br>\n\r";
  echo "     </div>\n\r";
  
  // ----- Date 
  echo "       <div class=\"col-sm-4\">\n\r";
  echo "         <p class=\"text-center\">\n\r";
  echo "           ".date('d.m.Y',$tstamp1)." <br> to: " .date('d.m.Y',$tstamp2)."</br>\n\r";
  echo "         </p>\n\r";
  echo "       </div>\n\r";
  
  // ----- Status
  echo "       <div class=\"col-sm-4\">\n\r";
  echo "         <p class=\"text-right\">\n\r";
  echo "           <a href=\"..\sub\status.php?JOB=ST1&PUSER=" .$spuser ."\">Status ID</a></br>\n\r";
  echo "           <img src=\"../image/ampel_" .$serrX .".png\"><br>\n\r";
  echo "           <a href=\"../edit/comment.php?PID=" .$tpid ."\">Add Comments</a>\n\r";
  echo "         </p>\n\r";
  echo "       </div>\n\r";
 
  echo "     </div>\n\r";
  echo "   </div>\n\r";
  echo " </div>\n\r";
  
  $pcount = 0;
  
  
  // --------------------------------------------------------------------------------
  // Navigation 
  // --------------------------------------------------------------------------------
  $navb = "viewstd.php?WW=1&TIME=" .($tstamp - 86400) ."&PUSER=" .$tpuser;
  $navf = "viewstd.php?WW=1&TIME=" .($tstamp + 86400) ."&PUSER=" .$tpuser;
  $navbb = "viewstd.php?WW=1&TIME=" .$letztewoche ."&PUSER=" .$tpuser;
  $navff = "viewstd.php?WW=1&TIME=" .$naechstewoche ."&PUSER=" .$tpuser;
  $navtd = "viewstd.php?WW=1&TIME=" .time() ."&PUSER=" .$tpuser;
  $navfirstdata = "viewstd.php?WW=1&TIME=" .($tstamp + 86400) ."&PUSER=" .$tpuser;  // todo
  $navlastdatad = "viewstd.php?WW=1&TIME=" .($tstamp + 86400) ."&PUSER=" .$tpuser;  // todo
  
?>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-sm-1">
          <a href=" <?php echo $navb; ?>" data-toggle="tooltip" data-placement="top" title="- 1 Day"><span class="glyphicon glyphicon-triangle-left"></span></a>
        </div> 

        <div class="col-sm-1">
          <a href="<?php echo $navbb; ?>"data-toggle="tooltip" data-placement="top" title="- 1 Week"><span class="glyphicon glyphicon-backward"></span></a>
        </div> 
        
        <div class="col-sm-1">
          <a href=" <?php echo $navfirstdata; ?>"data-toggle="tooltip" data-placement="top" title="Back to first Data"><span class="glyphicon glyphicon-fast-backward"></span></a>
        </div>
        
        <div class="col-sm-3">
  <?php 
  echo "        <form name=\"Testform\" action=\"viewstd.php\" method=\"get\">\n\r";
  echo "          <input type=\"hidden\" name=\"WW\"value=\"1\">\n\r";
  echo "          <input type=\"hidden\" name=\"PID\"value=\"" .$tpid  ."\">\n\r";
  echo "          <input type=\"hidden\" name=\"PUSER\"value=\"" .$tpuser  ."\">\n\r";
  echo "          <input type=\"text\" name=\"dpdate\" value =\"".date('Y-m-d',$tstamp) ."\"size=\"11\" id=\"datepicker\" onChange=\"this.form.submit()\"/>&nbsp;\n\r";
  echo "        </form>\n\r";      
  ?>
        </div>
        
        <div class="col-sm-3">
          <a href="#"> Day  </a>   
          <a href="#"> Month  </a>   
        </div>
        
        <div class="col-sm-1">
          <a href=" <?php echo $navlastdata; ?>"data-toggle="tooltip" data-placement="top" title="Last Data">
            <span class="glyphicon glyphicon-fast-forward"></span>
          </a>
        </div> 
        
        <div class="col-sm-1">
          <a href=" <?php echo $navff; ?>"data-toggle="tooltip" data-placement="top" title="+ 1 Week">
            <span class="glyphicon glyphicon-forward"></span>
          </a>
        </div>
        
        <div class="col-sm-1">
          <a href=" <?php echo $navf; ?>"data-toggle="tooltip" data-placement="top" title="+ 1 Day">
            <span class="glyphicon glyphicon-triangle-right"></span>
          </a>
        </div>
      </div>
    </div>
  </div>
<?php


  
  // --------------------------------------------------------------------------------
  // Day Navigation // Susdata
  // --------------------------------------------------------------------------------
  
  echo "  <div class=\"row\">\n\r";
  echo "  </div>\n\r";
  
  
  // --------------------------------------------------------------------------------
  // Graphen
  // --------------------------------------------------------------------------------
 
  echo "      <br><br>\n\r";
  for ($i = 1; $i < 21; $i++){
    if ($DxEN[$i] == "1"){
      echo "      <img src=\"bildstd.php?&KW=" .$ww ."&ERR=" .$dxerr[$i] ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=D" .$i ."&PUSER=" .$tpuser ."\" usemap=\"#bildan\" class=\".img-responsive\" width=\"100%\">\n\r";
      echo "      <br><br>\n\r";   
    }
  }

  for ($i = 1; $i < 21; $i++){
    if ($ANxEN[$i] == "1"){
      echo "      <img src=\"bildstd.php?&KW=" .$ww ."&ERR=" .$axerr[$i] ."&TIME=" .$tstamp ."&PID=" .$tpid ."&GRAPH=A" .$i ."&PUSER=" .$tpuser ."\" usemap=\"#bildan\" class=\".img-responsive\" width=\"100%\">\n\r";
      echo "      <br><br>\n\r"; 
    }
  }
  
  // Graphmap
  echo "      <map name=\"bildan\">\n\r";
  echo "        <area shape=\"rect\" coords=\"0,0,100,225\"" .$eintagvor ." alt=\"vor\"> \n\r";
  echo "        <area shape=\"rect\" coords=\"640,0,740,225\"" .$eintagweiter ." alt=\"weiter\"> \n\r";
  echo "      </map>\n\r";
  
  
  // --------------------------------------------------------------------------------
  // Werte 
  // --------------------------------------------------------------------------------
  // bftopass
  if ($db_table == 'bftopass')
  {
    ?>
    <div class="panel-group">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapse1">Aktuelle Daten</a>
          </h4>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Zeit</th>
                  <th>Puser</th>
                  <th>D1-8</th>
                  <th>A1-8</th>
                </tr>
              </thead>
              <tbody>
     <?php
    
    $frage = "SELECT * FROM  Archive_bftopass WHERE ((ZEIT >$tstamp1) && (ZEIT<$tstamp2) && (PUSER = '$spuser')) ORDER BY ZEIT DESC LIMIT 25;";  
    $ergebnis = $mysqli->query($frage);
    $xold = 0;
    while($zeile = $ergebnis->fetch_array()){
      echo "  <tr>\n\r";
      echo "    <td>" .htmlspecialchars(date('d.m H:i:s',$zeile['ZEIT']));
      echo " " .htmlspecialchars($zeile['RTS']) ."</td>\n\r";
      echo "    <td>\n\r";
      if ($xold != 0){
        $x = ((int)(intval($zeile['ZEIT']))-$xold)/60;   
        echo round($x,1);
      }
      echo "    </td>\n\r";
      $xold = (int)(intval($zeile['ZEIT']));   
      echo "    <td>" .substr($zeile['D1'],0,1);
      echo substr($zeile['D2'],0,1);
      echo substr($zeile['D3'],0,1);
      echo substr($zeile['D4'],0,1);   
      echo substr($zeile['D5'],0,1);
      echo substr($zeile['D6'],0,1);
      echo substr($zeile['D7'],0,1);
      echo substr($zeile['D8'],0,1) ."</td>\n\r";
      echo "    <td>" .floatval($zeile['A1']);
      echo "/".floatval($zeile['A2']);
      echo "/".floatval($zeile['A3']);
      echo "/".floatval($zeile['A4']);
      echo "/".floatval($zeile['A5']);
      echo "/".floatval($zeile['A6']);
      echo "/".floatval($zeile['A7']);
      echo "/".floatval($zeile['A8']);
      echo "/".floatval($zeile['A9'])."</td>";
      
      if ($sesAdmin == 1){
        echo "<td><a href=\"../edit/comment.php?ADD=1&TIME=" .$zeile['ZEIT'] ."&PID=" .$tpid ."\">+</a><br></td>";
      }

    }
    echo "<td></tr></table></dd></div>";
    $ergebnis->close();  
  } 
  
  //-----------------------------------------------------
  //bfccio
  if ($db_table == 'bfccio')
  {
    ?>
    <div class="panel-group">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapse1">Aktuelle Daten</a>
          </h4>
        </div>
        <div id="collapse1" class="panel-collapse collapse">
          <div class="panel-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Zeit</th>
                  <th>Puser</th>
                  <th>Dx</th>
                  <th>Ax</th>
                </tr>
              </thead>
              <tbody>
     <?php

    // $frage = "SELECT * FROM bfccio WHERE ((time >$tstamp1) && (time<$tstamp2) && (imei = '$spuser')) ORDER BY time DESC LIMIT 50;";  
    $frage = "SELECT * FROM Archive_bfccio WHERE ((time >$tstamp1) && (time<$tstamp2) && (imei = '$spuser')) ORDER BY time DESC LIMIT 50;";  
    $ergebnis = $mysqli->query($frage);
    $xold = 0;
    while($zeile = $ergebnis->fetch_array()){
      echo "  <tr>\n\r";
      echo "    <td>" .htmlspecialchars(date('d.m H:i:s',$zeile['time']));
      //echo " " .htmlspecialchars($zeile['RTS']) ."</td>\n\r";
      echo "    <td>\n\r";
      if ($xold != 0){
        $x = ((int)(intval($zeile['time']))-$xold)/60;   
        echo round($x,1);
      }
      echo "    </td>\n\r";
      $xold = (int)(intval($zeile['time']));   
      echo "    <td>" .$zeile['Dx'] ."</td>\n\r";
      echo "    <td>" .$zeile['Ax'] ."</td>";
      
      if ($sesAdmin == 1){
        echo "<td><a href=\"../edit/comment.php?ADD=1&TIME=" .$zeile['time'] ."&PID=" .$tpid ."\">+</a><br></td>";
      }

    }
    echo "<td></tr></table></dd></div>";
    $ergebnis->close();  
  } 
   

   ?>
        </div>
      </div>
    </div>
  </div>  

  <?php
  
  

  
  
  
}
 

  
  
//-----------------------------------------------------------------------------------
// FOOTER
if ($sesOK == 1){
  echo "<br>\n\r";
  echo "status generated on: " .date('y/m/d,H:i:s') ."<br>\n\r";
  echo "&copy; 2018 <a href=\"http:\\\\www.lanthan.eu\">Lanthan GmbH & Co. KG </a>\n\r";
  echo "Remote Adress:: " .$tip ."<br>\n\r";      
  echo "</div>\n\r";
}
echo "<td></tr></table></dd></div>";
    // $ergebnis->close();
// include "./sub/footer.php"; 

?>
    
        </div>
      </div>
    </div>
  </div>  

  