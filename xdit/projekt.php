<?PHP
  session_start();
  ini_set('display_errors', '1');
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

</head>
<body>
<?PHP


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

if ($sesOK == 0){
  
  ?>
  <div class="container">
    <br><br>
    <a href="../index.php">Acces Denied: Please log in!</a><br>
    <a href="http://www.lanthan.eu/de/impressum.html">Impressum: Lanthan GmbH &amp; Co. KG &copy; 2018</a><br>
    
  </div>
  <?php
}

// ------------------------------------------------------------------------------------------
// HTTP-GET PARAMETER 
// ------------------------------------------------------------------------------------------

$tjob = "NONE";  
$taction = "NONE";
$tuserid =  intval(htmlspecialchars($_REQUEST['USERID']));   // projekt id
$tpuser = htmlspecialchars($_REQUEST['PUSER']);

if (isset($_GET["JOB"])){
  $tjob =htmlspecialchars($_REQUEST['JOB']);
}
if (isset($_GET["ACTION"])){
  $taction =htmlspecialchars($_REQUEST['ACTION']);
}

echo "tID " .$tuserid ."tPU " .$tpuser ." / JOB " .$tjob ." / ACTION " .$taction ."<br>\n\r";
echo "OK " .$sesOK ." / A " .$sesAdmin ." / SUA " .$sesSuperadmin ." / VA " .$sesViewall." / SID " .$sesUserid;


// ------------------------------------------------------------------------------------------
//
// ------------------------------------------------------------------------------------------
//  Session bereits gestartet / Seiten Menü
// ------------------------------------------------------------------------------------------  
if ($sesOK == 1){

  ?>
  <nav class="navbar navbar-inverse">
    <div class="container">
      <div class="navbar-header">
        <img src="../image/lanthan_logo_150x130.jpg" with="100" height="50">
      </div>
      <ul class="nav navbar-nav">
        <li class="active"><a href="../index.php">Home</a></li>
        <li class="active"><a href="../help.html">Help</a></li>
        <li><a href="#">Map</a></li>
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
                <li><a href="../xdit/projekt.php?JOB=EDIT">Edit Project</a></li>
                <li><a href="../xdit/projekt.php?JOB=FIND">Find New Project</a></li>                
                <li><a href="./XYA/ADM450.php?server=localhost&username=lanthan-01&db=lanthan_test&pass=7VrBi-.GXTZCF" target="_blank">Adminer</a></li>
              </ul>
            </li>           
            <?php
          }        
        ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="./user.php?JOB=EDIT&USERID=<?php echo $sesUserid;?>"><span class="glyphicon glyphicon-user"></span> <?php echo $sesUser;?></a></li>
        <li><a href="../index.php?LOGOUT=1"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
      </ul>
    </div>
  </nav>
  <?php
}
  

// Abschnitte 
// LIST / LISTF --------------------------------- OK
// GRAPHDEL / GRAPHADD -------------------------- OK
// EDIT -> EDITSAVE  & GRAPHDEL/GRAPHADD -------- GRAPH OK  EDIT Fehlt
// NEW -> NEWSAVE
// EDITSAFE offen 
// NEWSAFE offen
  
  
  
// ------------------------------------------------------------------------------------------
// NEW PROJECT
// ------------------------------------------------------------------------------------------
if (($sesOK == 1) && ($sesSuperadmin == 1)){
?>
  <div class="container">
    <div class="row">
      <div class="col">
        
<?php

  // VALIDADE AND SAVE NEW PROJEKT FORM 
  // ------------------------------------------------------------------------------------------
  
  if (($tjob == "SAVE") && ($tpuser <> "")){
    $ausgabe ="";
    $newid = intval(htmlspecialchars($_REQUEST['ID'])); 
    $newuser = htmlspecialchars($_REQUEST['USER']);      
    $newort = htmlspecialchars($_REQUEST['ORT']);
    $newland = htmlspecialchars($_REQUEST['LAND']);
    $newwgs84b = htmlspecialchars($_REQUEST['WGS84B']);
    $newwgs84l = htmlspecialchars($_REQUEST['WGS84L']);    
    $newdb = htmlspecialchars($_REQUEST['DB']);
    $newaktiv = 0;
    if (htmlspecialchars($_REQUEST['AKTIV']) =="on"){
      $newaktiv = 1;
    }    
    
    $test= 0;
    if ((strlen($tpuser) < 5) || (strlen($tpuser) > 20)){
      $test = 1;
      $ausgabe = "Passwort <5 oder >20 Zeichen <br>\n\r";
    }
    
    $frage =  "SELECT * FROM bfprojektini WHERE PUSER=\"" .$tpuser ."\";";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array()){
      if ($tpuser == $zeile['PUSER']){
        if ($newid != $zeile['ID']){
          $test = 1;
          $ausgabe = $ausgabe ."Username existiert bereits <br>\n\r";
        }
      }
    }
    $ergebnis->close();

    if ($test == 1){
      echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
      $tjob = "NEW";
    }
    
    if ($test == 0){
      echo "        Puser: " .$tpuser ."<br>\n\r";
      echo "        User: " .$newuser ."<br>\n\r";
      echo "        Ort: " .$newort ."<br>\n\r";
      echo "        Land: " .$newland ."<br>\n\r";
      echo "        WGS84B: " .$newwgs84b ."<br>\n\r";
      echo "        WGS84L: " .$newwgs84l ."<br>\n\r";
      echo "        DB: " .$newdb ."<br>\n\r";
      echo "        Aktiv: " .$newaktiv ."<br>\n\r";
      
      $spu = $mysqli->real_escape_string($tpuser);
      $snu = $mysqli->real_escape_string($newuser);
      $sno = $mysqli->real_escape_string($newort);
      $snl = $mysqli->real_escape_string($newland);
      $swb = $mysqli->real_escape_string($newwgs84b);
      $swl = $mysqli->real_escape_string($newwgs84l);
      $sdb = $mysqli->real_escape_string($newdb);
      $sak = $mysqli->real_escape_string($newaktiv);
      $sid = $mysqli->real_escape_string($newid);      
      
      if ($taction =="NEWSAVE") {
        $insert = "INSERT INTO bfprojektini (PUSER, USER, ORT, LAND, WGS84B, WGS84L, DB, AKTIV) VALUES('$spu','$snu', '$sno', '$snl', '$swb', '$swl', '$sdb', '$sak');";
        $ergebnis = $mysqli->query($insert);
        echo $insert ."<br><b>\n\r";
        echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
        if ($mysqli->errno > 0){
          echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>\n\r";
          echo $insert ."<br>\n\r";
        } else {
          echo "Values stored<br></b>\n\r"; 
        }
        //$ergebnis->close();
        $tjob = "EDIT";
      }
      
      if ($taction =="EDITSAVE") {
        $insert = "UPDATE bfprojektini SET "
        ."PUSER=\"" .$spu ."\", "
        ."USER=\"" .$snu ."\", "
        ."ORT=\"" .$sno ."\", "
        ."LAND=\"" .$snl ."\", "
        ."WGS84B=\"" .$swb ."\", "
        ."WGS84L=\"" .$swl ."\", "
        ."DB=\"" .$sdb ."\", "
        ."AKTIV=\"" .$sak ."\" ";
        $insert = $insert ."WHERE ID=\"" .$sid ."\";";
        $ergebnis = $mysqli->query($insert);
        echo $insert ."<br><b>\n\r";
        echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
        if ($mysqli->errno > 0){
          echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>\n\r";
          echo $insert ."<br>\n\r";
        } else {
          echo "Values stored<br></b>\n\r"; 
        }
        //$ergebnis->close();
        $tjob = "EDIT";
      }
   
    }
  }


  // NEW PROJEKT FORM
  // ------------------------------------------------------------------------------------------
  if ($tjob == "NEW"){
  ?>
        <h3 class="text-center">New Project</h3>
        <form action="./projekt.php?JOB=SAVE">
        
          <div class="form-group">
            <label for="user">Project:</label>
            <input type="text" class="form-control" name="PUSER" id="text">
          </div>
          
          <div class="form-group">
            <label for="user">Company:</label>
            <input type="text" class="form-control" name="USER" id="pwd">
          </div>  
          
          <div class="form-group">
            <label for="ort">Location:</label>
            <input type="text" class="form-control" name="ORT" id="pwd">
          </div>
                    
          <div class="form-group">
            <label for="land">Country:</label>
            <input type="pwd" class="form-control" name="LAND" id="pwd">
          </div>
                    
          <div class="form-group">
            <label for="wgs84b">WGS84B:</label>
            <input type="text" class="form-control" name="WGS84B" id="text">
          </div>   
          
          <div class="form-group">
            <label for="wgs84L">WGS84L:</label>
            <input type="text" class="form-control" name="WGS84L" id="text">
          </div> 
          
          <div class="form-group">
            <label for="Text">Text:</label>
            <input type="text" class="form-control" name="TEXT" id="text">
          </div>
          
          <div class="form-group">
            <label for="database">System:</label>
            <select class="form-control" name="DB">
              <option>bftopass</option>
              <option selected>bfccio</option>
          </div> 
          
          <div class="checkbox">
            <label><input type="checkbox" name="AKTIV"> Aktiv</label>
          </div>
   
          <input type="hidden" name="JOB" value="SAVE">
          <input type="hidden" name="ACTION" value="NEWSAVE">
          
          <button type="submit" class="btn btn-default">Create Project</button>
        </form>
  <?php
  }
  
  
} //end if(($sesOK == 1) && ($sesSuperadmin == 1))

// ------------------------------------------------------------------------------------------
// EDIT PROJEKT
// ------------------------------------------------------------------------------------------
if (($sesOK == 1) && ($sesSuperadmin == 1)){
?>
  <div class="container">
    <div class="row">
      <div class="col">
        
<?php

  
  // VALIDADE AND REMOVE GRAPHS
  // ------------------------------------------------------------------------------------------
  if ((($tjob == "GRAPHDEL") || ($tjob == "GRAPHADD")) && ($tpuser <> "")){
  //  if (($tjob == "GRAPHDEL") || ($tjob == "GRAPHADD")) {  
    $ausgabe ="";
    $fehler = 0;
    if (isset($_GET["AD"])){
      if(isset($_GET["NR"])){
        $newad = htmlspecialchars($_REQUEST['AD']);      
        $newnr = intval(htmlspecialchars($_REQUEST['NR'])); 
        
        // PUSER vorhanden?
        $n = 0;
        $frage =  "SELECT * FROM bfprojektini WHERE PUSER = \"" .$tpuser ."\";";
        $ergebnis = $mysqli->query($frage);
        while($zeile = $ergebnis->fetch_array()){
          $n++;
        }
        if ($n == 0){
          $ausgabe = "PUSER nicht vorhanden <br>\n\r";
          $fehler = 1;
        }
        $ergebnis->close();
        
        if (($newnr < 1) && ($newnr > 20)){
          $ausgabe = "ADNR zu groß <br>\n\r";
          $fehler = 1;
        } else {
          if ($newad == "A") {
            $newvar = "AN" .$newnr ."EN";
          } elseif ($newad == "D"){
             $newvar = "DI" .$newnr ."EN";
          } else {
            $ausgabe = "Parameter AD ungültig <br>\n\r";
            $fehler = 1;
          }
        }
        
        if ($tjob == "GRAPHADD"){
          $insert =  "UPDATE bfprojektini SET " .$newvar ." = '1' WHERE PUSER = \"" .$tpuser ."\";"; 
        } else {
          $insert =  "UPDATE bfprojektini SET " .$newvar ." = '0' WHERE PUSER = \"" .$tpuser ."\";"; 
        }        
        
        if ($fehler == 1){
          echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
          $tjob = "EDIT";
        }
 
        if ($fehler == 0){
          $ergebnis = $mysqli->query($insert);
          echo $insert ."<br><b>\n\r";
          echo "Values removed:";
          echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
        }
      }
    } 
    $tjob = "EDIT";    
  }  
 
  
  // EDIT PROJEKT
  // ------------------------------------------------------------------------------------------
  if (($tjob == "EDIT") && ($tpuser <> "")){
      
    $frage =  "SELECT * FROM bfprojektini WHERE PUSER = \"" .$tpuser ."\" ;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array()){
      $sid = $zeile['ID'];
      $snu = $zeile['USER'];
      $sno = $zeile['ORT'];
      $snl = $zeile['LAND'];
      $snwb = $zeile['WGS84B'];
      $snwl = $zeile['WGS84L'];
      $sndb = $zeile['DB'];
      $sntx = $zeile['TEXT'];
      $sakt = (1 == $zeile['AKTIV'])? "checked": "";
    }
    // Formularausgabe
  ?>
        <h3 class="text-center">Edit Project <?php echo $tpuser ." (ID " .$sid .")" ?></h3>
        <form action="./projekt.php?JOB=SAVE">
        
          <div class="form-group">
            <label for="user">Project:</label>
            <input type="text" class="form-control" name="PUSER" id="text" value="<?php echo $tpuser ?>">
          </div>
                              
          <div class="form-group">
            <label for="email">User:</label>
            <input type="text" class="form-control" name="USER" id="text" value="<?php echo $snu ?>">
          </div>
                    
          <div class="form-group">
            <label for="phone">Location:</label>
            <input type="text" class="form-control" name="ORT" id="text" value="<?php echo $sno ?>">
          </div>   
          
          <div class="form-group">
            <label for="mobile">Country:</label>
            <input type="text" class="form-control" name="LAND" id="text" value="<?php echo $snl ?>">
          </div> 
          
          <div class="form-group">
            <label for="Company">WGS84B:</label>
            <input type="text" class="form-control" name="WGS84B" id="text" value="<?php echo $snwb ?>">
          </div>            
          
          <div class="form-group">
            <label for="Company">WGS84L:</label>
            <input type="text" class="form-control" name="WGS84L" id="text" value="<?php echo $snwl ?>">
          </div>          
           
          <div class="form-group">
            <label for="database">System:</label>
            <select class="form-control" name="DB">
            <?php
            $txt = "<option>bftopass</option>\n\r<option selected>bfccio</option>\n\r";
            if ($sndb == "bftopass")
            {
              $txt = "<option selected>bftopass</option>\n\r<option>bfccio</option>\n\r";
            }
            if ($sndb == "bfccio")
            {
              $txt = "<option>bftopass</option>\n\r<option selected>bfccio</option>\n\r";
            }
            echo $txt;
            ?>            
          </div>
          
          <div class="checkbox">
            <label><input type="checkbox" name="AKTIV" <?php echo $sakt ?>> Aktiv </label>
          </div>
          
          <input type="hidden" name="ID" value="<?php echo $sid ?>">
          <input type="hidden" name="JOB" value="SAVE">
          <input type="hidden" name="ACTION" value="EDITSAVE">
          
          <button type="submit" class="btn btn-default">Save Project</button>
        </form>
 
  <?php
    // Projektzuweisung zugewiesene Projekte
    if ($sesSuperadmin == 1){

      $frage = "SELECT * FROM bfprojektini WHERE PUSER = \"" .$tpuser ."\";";
      $ergebnis = $mysqli->query($frage);
      $n = 0;
      $anen = array();
      $anname = array();
      $dien = array();      
      $diname = array();
      while($zeile = $ergebnis->fetch_array()){
        for ($n = 1; $n < 21; $n++){
          $avaren = "AN" .$n ."EN";
          $avarname = "AN" .$n ."NAME";
          $anen[$n] = $zeile[$avaren];
          $anname[$n] = $zeile[$avarname];
          $dvaren = "DI" .$n ."EN";        
          $dvarname = "DI" .$n ."NAME";
          $dien[$n] = $zeile[$dvaren];
          $diname[$n] = $zeile[$dvarname];
        }
      }
      $ergebnis->close(); 
  ?>
  
  <br><br>
  <div class="panel panel-default">
    <div class="panel-heading">
     Graphs assigned to Project (Click to change activate state)
    </div>
    <div class="panel-body">
  <?php
      for ($n = 1; $n < 21; $n++){
        if (intval($dien[$n]) == 1){
          echo "      <a href=\"./projekt.php?JOB=GRAPHDEL&PUSER=" .$tpuser ."&AD=D&NR=" .$n ."\" class=\"btn btn-diactive\" role=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$diname[$n] ."\"> DI" .$n ."</a> \n\r";
        }        
        if (intval($dien[$n]) == 0){
          echo "      <a href=\"./projekt.php?JOB=GRAPHADD&PUSER=" .$tpuser ."&AD=D&NR=" .$n ."\" class=\"btn btn-diinactive\" role=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$diname[$n] ."\"> DI" .$n ."</a> \n\r";
        }
      } 
      echo "<br>\n\r";    
      for ($n = 1; $n < 21; $n++){
        if (intval($anen[$n]) == 1){
          echo "      <a href=\"./projekt.php?JOB=GRAPHDEL&PUSER=" .$tpuser ."&AD=A&NR=" .$n ."\" class=\"btn btn-anactive\" role=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$anname[$n] ."\"> AN" .$n ."</a> \n\r";
        }
        if (intval($anen[$n]) <> 1){
          echo "      <a href=\"./projekt.php?JOB=GRAPHADD&PUSER=" .$tpuser ."&AD=A&NR=" .$n ."\" class=\"btn btn-aninactive\" role=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$anname[$n] ."\"> AN" .$n ."</a> \n\r";
        }        
      }
      echo "    </div>\n\r";
      echo "  </div>\n\r";  
    
  ?>
  <br>

  <script>
    $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip();   
    });
  </script>
  
  <?php
      $tjob = "LISTF";
    }
  } //END EDIT PROJECT
 
  // LIST
  // ------------------------------------------------------------------------------------------
  if (($tjob=="LIST") || ($tjob=="LISTF") || (($tjob=="EDIT") && ($tpuser == ""))){
    
    if ($tjob =="LISTF"){  
    ?>
  <div class="panel-group">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" href="#collapse1">Edit Another Project</a>
        </h4>
      </div>
      <div id="collapse1" class="panel-collapse collapse">
    <?php
    }
    ?>
        <div class="panel-body">
          <input class="form-control" id="FilterPLInput" type="text" placeholder="Filter..">
 
        <!--Tabellenkopf und Filter -->
    <script>
      $(document).ready(function(){
        $("#FilterPLInput").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#FilterPLTable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
        });
      });
    </script>
   
    <table class="table sortable table-responsive table-condensed table-hover">
      <thead>
        <tr>
          <th>PUSER</th>
          <th>USER</th>
          <th>Ort</th>
          <th>Land</th>         
          <th>Aktiv</th>
        </tr>
      </thead>  
      <tbody id="FilterPLTable">
    <?php
         
    $frage =  "SELECT * FROM bfprojektini;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array()){
      echo "      <tr>\n\r";
      echo "        <td><a href=\"projekt.php?JOB=EDIT&PUSER=" .$zeile['PUSER'] ."\">".$zeile['PUSER'] ."</a></td>\n\r";
      echo "        <td>" .$zeile['USER'] ."</td>\n\r";
      echo "        <td>" .$zeile['ORT'] ."</td>\n\r";
      echo "        <td>" .$zeile['LAND'] ."</td>\n\r";
      echo "        <td>" .$zeile['AKTIV'] ."</td>\n\r";
      echo "      </tr>\n\r";
    }
    echo "      </table>";    
    echo "      </div>";    
    echo "      </div>";    
    
    $ergebnis->close();
  } // END LIST
  
  
    
  // FIND NEW PROJECT
  // ------------------------------------------------------------------------------------------
  if ($tjob=="FIND"){
    
    ?>
        <div class="panel-body">
          <input class="form-control" id="FilterPLInput" type="text" placeholder="Filter..">
 
        <!--Tabellenkopf und Filter -->
    <script>
      $(document).ready(function(){
        $("#FilterPLInput").on("keyup", function() {
          var value = $(this).val().toLowerCase();
          $("#FilterPLTable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
          });
        });
      });
    </script>
   
    <table class="table sortable table-responsive table-condensed table-hover">
      <thead>
        <tr>
          <th>User</th>
          <th>Data</th>
          <th>Time</th>
          <th>Action</th>
        </tr>
      </thead>  
      <tbody id="FilterPLTable">
    <?php
    
    $frage =  "SELECT * FROM bfprojektini;";
    $ergebnis = $mysqli->query($frage);
    $n = 0;
    while($zeile = $ergebnis->fetch_array())
    {
      $pmerker[$n] = $zeile['PUSER'];
      $n++;
    }
    $ergebnis->close(); 
    
    $frage =  "SELECT DISTINCT imei FROM bfccio ORDER BY imei DESC;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array())
    {
      $test = 0;
      $dbimei = $zeile['imei'];
      for ($m = 0; $m < $n; $m++)
      {
        if ($dbimei == $pmerker[$m])
        {
          $test++;
        }
      }
      if ($test == 0)
      {
        $frage =  "SELECT COUNT(*) AS anzahl FROM bfccio WHERE IMEI = '$dbimei';";
        $ergebnis2 = $mysqli->query($frage);
        while($zeile2 = $ergebnis2->fetch_array())
        {
          $dbanz = $zeile2['anzahl'];
        }
        $ergebnis2->close();
        
        $frage =  "SELECT * FROM bfccio WHERE IMEI = '$dbimei' ORDER BY time DESC LIMIT 1;";
        $ergebnis2 = $mysqli->query($frage);
        while($zeile2 = $ergebnis2->fetch_array())
        {
          $dbtime = $zeile2['time'];
        }
        $ergebnis2->close();        
  
        echo "      <tr>\n\r";
        echo "        <td>" .$dbimei ."</td>\n\r";
        echo "        <td>" .$dbanz ."</td>\n\r";
        echo "        <td>" .date('d.m.Y H:i:s', $dbtime) ."</td>\n\r";
        echo "        <td><a href=\"projekt.php?JOB=SAVE&ACTION=NEWSAVE&DB=bfccio&PUSER=" .$dbimei ."\">ADD SYSTEM</a></td>\n\r";        
        echo "      </tr>\n\r";        
      }
    }
    $ergebnis->close();
    
    $frage =  "SELECT DISTINCT PUSER FROM bftopass ORDER BY PUSER DESC;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array())
    {
      $test = 0;
      $dbpuser = $zeile['PUSER'];
      for ($m = 0; $m < $n; $m++)
      {
        if ($dbpuser == $pmerker[$m])
        {
          $test++;
        }
      }
      if ($test == 0)
      {      
        $frage =  "SELECT COUNT(*) AS anzahl FROM bftopass WHERE puser = '$dbpuser';";
        $ergebnis2 = $mysqli->query($frage);
        while($zeile2 = $ergebnis2->fetch_array())
        {
          $dbanz = $zeile2['anzahl'];
        }
        $ergebnis2->close();
        
        $frage =  "SELECT * FROM bftopass WHERE puser = '$dbpuser' ORDER BY ZEIT DESC LIMIT 1;";
        $ergebnis2 = $mysqli->query($frage);
        while($zeile2 = $ergebnis2->fetch_array())
        {
          $dbtime = $zeile2['ZEIT'];
        }
        $ergebnis2->close();         
        
        echo "      <tr>\n\r";
        echo "        <td>" .$dbpuser ."</td>\n\r";
        echo "        <td>" .$dbanz ."</td>\n\r";
        echo "        <td>" .date('d.m.Y H:i:s', $dbtime) ."</td>\n\r";
        echo "        <td><a href=\"projekt.php?JOB=SAVE&ACTION=NEWSAVE&DB=bftopass&PUSER=" .$dbpuser ."\">ADD SYSTEM</a></td>\n\r";
        echo "      </tr>\n\r";
      }
    }
    $ergebnis->close();
    
    echo "      </table>";    
    echo "      </div>";    
    echo "      </div>";    

  } // END LIST
  
} //END (($sesOK == 1) && ($sesSuperadmin == 1)){
?>
      </div> 
    </div>
<?php

// ------------------------------------------------------footer
$mysqli->close();  

if ($sesOK == 1)
{
  echo "<br>\n\r";
  echo "status generated on: " .date('y/m/d,H:i:s') ."<br>\n\r";
  echo "&copy; 2018 <a href=\"http:\\\\www.lanthan.eu\">Lanthan GmbH & Co. KG </a>\n\r";
  echo "Remote Adress:: " .$tip ."<br>\n\r";      
  echo "</div>\n\r";
}

include "../sub/footer.php"; 
?>
