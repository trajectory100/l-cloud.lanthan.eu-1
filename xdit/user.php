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

if (isset($_GET["JOB"])){
  $tjob =htmlspecialchars($_REQUEST['JOB']);
}
if (isset($_GET["ACTION"])){
  $taction =htmlspecialchars($_REQUEST['ACTION']);
}

echo "tID " .$tuserid ." / JOB " .$tjob ." / ACTION " .$taction ."<br>\n\r";
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
                <li><a href="../XYA/ADM450.php?server=rdbms.strato.de&username=U969575&db=DB969575" target="_blank">Adminer</a></li>
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
  

// ------------------------------------------------------------------------------------------
// NEW USER
// ------------------------------------------------------------------------------------------
if (($sesOK == 1) && ($sesSuperadmin == 1)){
?>
  <div class="container">
    <div class="row">
      <div class="col">
        
<?php

  // VALIDADE AND SAVE NEW USER FORM
  // ------------------------------------------------------------------------------------------
  if ($tjob == "NEWSAVE"){
    $ausgabe ="";
    if (isset($_GET["USER"])){
      $newuser = htmlspecialchars($_REQUEST['USER']);      
      if (isset($_GET["PASS1"])){
        $newpass1 = htmlspecialchars($_REQUEST['PASS1']);
        if (isset($_GET["PASS2"])){
          $newpass2 = htmlspecialchars($_REQUEST['PASS2']);
          $test= 0;
          if ((strlen($newpass1) < 5) || (strlen($newpass1) > 20)){
            $test = 1;
            $ausgabe = "Passwort <5 oder >20 Zeichen <br>\n\r";
          }
          if ((strlen($newuser) < 5) || (strlen($newuser) > 20)){
            $test = 1;
            $ausgabe = $ausgabe ."Username <5 oder >20 Zeichen <br>\n\r";
          }
          if ($newpass1 != $newpass2){
            $test = 1;
            $ausgabe = $ausgabe ."Passwort ungleich <br>\n\r";
          }
          $frage =  "SELECT * FROM bfuser;";
          $ergebnis = $mysqli->query($frage);
          while($zeile = $ergebnis->fetch_array()){
            if($newuser==$zeile['USER']){
              $test = 1;
              $ausgabe = $ausgabe ."Username existiert bereits <br>\n\r";
            }
          }
          $ergebnis->close();
          
          $newemail = htmlspecialchars($_REQUEST['EMAIL']);
          $newfon = htmlspecialchars($_REQUEST['FON']);
          $newmobile = htmlspecialchars($_REQUEST['MOB']);
          $newcompany = htmlspecialchars($_REQUEST['CO']);
          
          $newadmin = 0;
          $newsua = 0;
          $newviewall = 0;
          if (htmlspecialchars($_REQUEST['ADM']) =="on"){
            $newadmin = 1;
          }
          if (htmlspecialchars($_REQUEST['SUA']) =="on"){
            $newsua = 1;
          }          
          if (htmlspecialchars($_REQUEST['VA']) =="on"){
            $newviewall = 1;
          }         
           
          echo "        <h3 class=\"text-center\">Saved User</h3>\n\r";
          echo "        User: " .$newuser ."<br>\n\r";
          echo "        Pass: " .$newpass1 ."<br>\n\r";
          echo "        Email: " .$newemail ."<br>\n\r";
          echo "        Fon: " .$newfon ."<br>\n\r";
          echo "        Mobile: " .$newmobile ."<br>\n\r";
          echo "        Company: " .$newcompany ."<br>\n\r";
          echo "        Admin: " .$newadmin ."<br>\n\r";
          echo "        SUA: " .$newsua ."<br>\n\r";
          echo "        Viewall: " .$newviewall ."<br>\n\r";

          if ($test == 1){
            echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
            $tjob = "NEW";
          }
          
          
          if ($test == 0){
            $snp = $mysqli->real_escape_string($newpass1);
            $snw = password_hash($newpass1, PASSWORD_DEFAULT);
            $snu = $mysqli->real_escape_string($newuser);
            $sne = $mysqli->real_escape_string($newemail);
            $snf = $mysqli->real_escape_string($newfon);
            $snm = $mysqli->real_escape_string($newmobile);
            $snc = $mysqli->real_escape_string($newcompany);
            $sad = $mysqli->real_escape_string($newadmin);
            $sua = $mysqli->real_escape_string($newsua);
            $sva = $mysqli->real_escape_string($newviewall);

            $insert = "INSERT INTO bfuser (USER, PASS, PASSW, EMAIL, COMPANY, FON, MOBILE, ADMIN, SUPERADMIN, VIEWALL) VALUES('$snu','$snp', '$snw', '$sne', '$snc', '$snf', '$snm', '$sad', '$sua', '$sva');";
            $ergebnis = $mysqli->query($insert);
            echo $insert ."<br><b>\n\r";
            echo "Values stored:";
            echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
          }


        }
      }
    }
  }
  

  // NEW USER FORM
  // ------------------------------------------------------------------------------------------
  if ($tjob == "NEW"){
  ?>
        <h3 class="text-center">New User</h3>
        <form action="./user.php?JOB=NEWSAVE">
        
          <div class="form-group">
            <label for="user">User:</label>
            <input type="text" class="form-control" name="USER" id="text">
          </div>
          
          <div class="form-group">
            <label for="pass1">Password 1:</label>
            <input type="pwd" class="form-control" name="PASS1" id="pwd">
          </div>  
          
          <div class="form-group">
            <label for="pass2">Password again:</label>
            <input type="pwd" class="form-control" name="PASS2" id="pwd">
          </div>
                    
          <div class="form-group">
            <label for="email">email:</label>
            <input type="pwd" class="form-control" name="EMAIL" id="pwd">
          </div>
                    
          <div class="form-group">
            <label for="phone">FON:</label>
            <input type="text" class="form-control" name="FON" id="text">
          </div>   
          
          <div class="form-group">
            <label for="mobile">Mobile:</label>
            <input type="text" class="form-control" name="MOB" id="text">
          </div> 
          
          <div class="form-group">
            <label for="Company">Company:</label>
            <input type="text" class="form-control" name="CO" id="text">
          </div>          
          
          <div class="checkbox">
            <label><input type="checkbox" name="ADM"> Admin</label>
          </div>
          
          <div class="checkbox">
            <label><input type="checkbox" name="SUA"> Superadmin</label>
          </div>
          
          <div class="checkbox">
            <label><input type="checkbox" name="VA"> Viewall</label>
          </div>
          <input type="hidden" name="JOB" value="NEWSAVE">
          
          <button type="submit" class="btn btn-default">Save User</button>
        </form>
  <?php
  }
}

// ------------------------------------------------------------------------------------------
// EDIT USER
// ------------------------------------------------------------------------------------------
if (($sesOK == 1) && (($sesSuperadmin == 1) || ($sesUserid == $tuserid))){
?>
  <div class="container">
    <div class="row">
      <div class="col">
        
<?php



  // VALIDADE AND SAVE EDIT USER
  // ------------------------------------------------------------------------------------------
  if (($tjob == "EDITSAVE") && ($tuserid > 0)){
    
    $ausgabe ="";
    
    if (isset($_GET["USER"])){
      $newuser = htmlspecialchars($_REQUEST['USER']);

      if ((strlen($newuser) < 5) || (strlen($newuser) > 20)){
        $test = 1;
        $ausgabe = $ausgabe ."Username <5 oder >20 Zeichen <br>\n\r";
      }

      $frage =  "SELECT * FROM bfuser;";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        if(($newuser == $zeile['USER']) && ($tuserid != $zeile['ID'])){
          $test = 1;
          $ausgabe = $ausgabe ."Username existiert bereits <br>\n\r";
        }
      }
      $ergebnis->close();
    
      $newemail = htmlspecialchars($_REQUEST['EMAIL']);
      $newfon = htmlspecialchars($_REQUEST['FON']);
      $newmobile = htmlspecialchars($_REQUEST['MOB']);
      $newcompany = htmlspecialchars($_REQUEST['CO']);
      
      //echo "        <h3 class=\"text-center\">Saved User</h3>\n\r";
      //echo "        ID: " .$tuserid ."<br>\n\r";
      //echo "        User: " .$newuser ."<br>\n\r";
      //echo "        Email: " .$newemail ."<br>\n\r";
      //echo "        Fon: " .$newfon ."<br>\n\r";
      //echo "        Mobile: " .$newmobile ."<br>\n\r";
      //echo "        Company: " .$newcompany ."<br>\n\r";
          
      if ($sesSuperadmin == 1){
       
        $newadmin = 0;
        $newsua = 0;
        $newviewall = 0;
        if (htmlspecialchars($_REQUEST['ADM']) =="on"){
          $newadmin = 1;
        }
        if (htmlspecialchars($_REQUEST['SUA']) =="on"){
          $newsua = 1;
        }          
        if (htmlspecialchars($_REQUEST['VA']) =="on"){
          $newviewall = 1;
        }         
         
        //echo "        Admin: " .$newadmin ."<br>\n\r";
        //echo "        SUA: " .$newsua ."<br>\n\r";
        //echo "        Viewall: " .$newviewall ."<br>\n\r";
        
      }

      if ($test == 1){
        echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
        $tjob = "EDIT";
      }
      
      if ($test == 0){
        $snu = $mysqli->real_escape_string($newuser);
        $sne = $mysqli->real_escape_string($newemail);
        $snf = $mysqli->real_escape_string($newfon);
        $snm = $mysqli->real_escape_string($newmobile);
        $snc = $mysqli->real_escape_string($newcompany);
        $sad = $mysqli->real_escape_string($newadmin);
        $sua = $mysqli->real_escape_string($newsua);
        $sva = $mysqli->real_escape_string($newviewall);
        
        $insert = "UPDATE bfuser SET "
          ."USER=\"" .$snu ."\", "
          ."EMAIL=\"" .$sne ."\", "
          ."COMPANY=\"" .$snc ."\", "
          ."FON=\"" .$snf ."\", "
          ."MOBILE=\"" .$snm ."\" ";
        
        if ($sesSuperadmin == 1){
          $insert = $insert .", ADMIN=\"" .$sad ."\", "."SUPERADMIN=\"" .$sua ."\", " ."VIEWALL=\"" .$sva ."\"";
        }
        
        $insert = $insert ."WHERE ID=" .$tuserid .";";
        
        $ergebnis = $mysqli->query($insert);
        //echo $insert ."<br><b>\n\r";
        if ($mysqli->errno > 0){
          echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>\n\r";
          echo $insert ."<br>\n\r";
        } else {
          echo "Values stored<br></b>\n\r"; 
        }
        $tjob = "EDIT";
      }
    }   
  }
  
  // VALIDATE AND ADD PROJECTS
  // ------------------------------------------------------------------------------------------
  if (($tjob == "PIDADD") && ($tuserid > 0)){
    $ausgabe ="";
    $test = 0;
    if (isset($_GET["PID"])){
      $newpid = htmlspecialchars($_REQUEST['PID']);      
      $n = 0;
      $frage =  "SELECT * FROM bfuserhasprojekt WHERE userID = " .$tuserid ." AND projektiniID = " .$newpid .";";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        $n++;
      }
      echo "test: " .$n ."\n\r";
      if ($n != 0){
        $ausgabe = "Eintrag vorhanden";
        $test = 1;
      }
      $ergebnis->close();
      
      if ($test == 1){
        echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
        $tjob = "EDIT";
      }

      if ($test == 0){
        $snp = $mysqli->real_escape_string($newpid);
        $snu = $mysqli->real_escape_string($tuserid);

        $insert = "INSERT INTO bfuserhasprojekt (USERID, PROJEKTINIID) VALUES('$snu','$snp');";
        $ergebnis = $mysqli->query($insert);
        echo $insert ."<br><b>\n\r";
        echo "Values stored:";
        echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
      }
    } 
    $tjob = "EDIT";    
  }  
  
  // VALIDADE AND REMOVE PROJECTS
  // ------------------------------------------------------------------------------------------
  if (($tjob == "PIDDEL") && ($tuserid > 0)){
    $ausgabe ="";
    $test = 0;
    if (isset($_GET["PID"])){
      $newpid = htmlspecialchars($_REQUEST['PID']);      
      $n = 0;
      $frage =  "SELECT * FROM bfuserhasprojekt WHERE userID = " .$tuserid ." AND projektiniID = " .$newpid .";";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        $n++;
      }
      echo "test: " .$n ."\n\r";
      if ($n == 0){
        $ausgabe = "Eintrag nicht vorhanden";
        $test = 1;
      }
      $ergebnis->close();
      
      if ($test == 1){
        echo "<br><b>FEHLER: " .$ausgabe ."</b><br>\n\r";
        $tjob = "EDIT";
      }

      if ($test == 0){
        $snp = $mysqli->real_escape_string($newpid);
        $snu = $mysqli->real_escape_string($tuserid);

        $insert = "DELETE FROM bfuserhasprojekt WHERE userID = ".$snu ." AND projektiniID = " .$snp .";";
        $ergebnis = $mysqli->query($insert);
        echo $insert ."<br><b>\n\r";
        echo "Values removed:";
        echo 'SQL Error ('.$mysqli->errno.') '.$mysqli->error ."<br></b>";
      }
    } 
    $tjob = "EDIT";    
  }  
 
  
  // EDIT USER
  // ------------------------------------------------------------------------------------------
  if (($tjob == "EDIT") && ($tuserid > 0)){
      
    $frage =  "SELECT * FROM bfuser WHERE ID = " .$tuserid ." ;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array()){
      $sid = $zeile['ID'];
      $snu = $zeile['USER'];
      $sne = $zeile['EMAIL'];
      $snf = $zeile['FON'];
      $snm = $zeile['MOBILE'];
      $snc = $zeile['COMPANY'];
      $sad = (1 == $zeile['ADMIN'])? "checked": "";
      $sua = (1 == $zeile['SUPERADMIN'])? "checked": "";
      $sva = (1 == $zeile['VIEWALL'])? "checked": "";
    }
    // Formularausgabe
  ?>
        <h3 class="text-center">Edit User</h3>
        <form action="./user.php?JOB=NEWSAVE">
        
          <div class="form-group">
            <label for="user">User:</label>
            <input type="text" class="form-control" name="USER" id="text" value="<?php echo $snu ?>">
          </div>
                              
          <div class="form-group">
            <label for="email">email:</label>
            <input type="pwd" class="form-control" name="EMAIL" id="pwd" value="<?php echo $sne ?>">
          </div>
                    
          <div class="form-group">
            <label for="phone">FON:</label>
            <input type="text" class="form-control" name="FON" id="text" value="<?php echo $snf ?>">
          </div>   
          
          <div class="form-group">
            <label for="mobile">Mobile:</label>
            <input type="text" class="form-control" name="MOB" id="text" value="<?php echo $snm ?>">
          </div> 
          
          <div class="form-group">
            <label for="Company">Company:</label>
            <input type="text" class="form-control" name="CO" id="text" value="<?php echo $snc ?>">
          </div>          
    
  <?php
    if ($sesSuperadmin == 1){
  ?>    
          <div class="checkbox">
            <label><input type="checkbox" name="ADM" <?php echo $sad ?>> Admin </label>
          </div>
          
          <div class="checkbox">
            <label><input type="checkbox" name="SUA" <?php echo $sua ?>> Superadmin</label>
          </div>
          
          <div class="checkbox">
            <label><input type="checkbox" name="VA"  <?php echo $sva ?>> Viewall</label>
          </div>
  <?php
    }
  ?>
          <input type="hidden" name="USERID" value="<?php echo $sid ?>">
          <input type="hidden" name="JOB" value="EDITSAVE">
          
          <button type="submit" class="btn btn-default">Save User</button>
        </form>
 
  <?php
    // Projektzuweisung zugewiesene Projekte
    if ($sesSuperadmin == 1){
  ?>
  <br><br>
  <div class="panel panel-default">
    <div class="panel-heading">
     Projects assigned to user
    </div>
    <div class="panel-body">  

  <?php
      $frage = "SELECT * FROM bfuserhasprojekt as bhp JOIN bfprojektini AS bpi WHERE bhp.projektiniID = bpi.ID AND bhp.userID = " .$tuserid .";";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        $spuser = $zeile['PUSER'];
        $sid = $zeile['ID'];
        $suser = $zeile['USER'];
        $sort = $zeile['ORT'];        
        $saktiv = $zeile['AKTIV']; 
        $sbutton = "btn btn-default btn-sm";
        if ($saktiv == '1') {
          $sbutton = "btn btn-info btn-sm";
        }
        echo "      <a href=\"./user.php?JOB=PIDDEL&USERID=" .$tuserid ."&PID=" .$sid ."\" class=\"" .$sbutton ."\" role=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$sort ." / " .$suser ."\">" .$spuser ."</a>\n\r";
      } 
      $ergebnis->close(); 
      echo "    </div>\n\r";
      echo "  </div>\n\r";      
  ?>
  
  <script>
  $(document).ready(function(){
    $("#myInput").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#myDIV *").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });
  });
  </script>  
  
  <script>
    $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip();   
    });
  </script>
  
  <br>
  <div class="panel panel-default">
    <div class="panel-heading">
      Projecs to assign 
      <input class="form-control form-inline" id="myInput" type="text" placeholder="Search..">
    </div>
    <div class="panel-body">  
      <div id="myDIV">

  <?php
      $PIDarr = array();
      $frage = "SELECT projektiniID FROM bfuserhasprojekt WHERE userID = " .$tuserid .";";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        $PIDarr[] = $zeile['projektiniID'];
       }
      $ergebnis->close(); 

      $frage = "SELECT ID, PUSER, USER, ORT, AKTIV FROM bfprojektini;";
      $ergebnis = $mysqli->query($frage);
      while($zeile = $ergebnis->fetch_array()){
        $n = 0;
        $nid = $zeile['ID'];
        $npuser = $zeile['PUSER'];
        $suser = $zeile['USER'];
        $sort = $zeile['ORT']; 
        $saktiv = $zeile['AKTIV']; 

        
        foreach($PIDarr as $data){
          if ($nid == $data) {
            $n = 1;
          }
        }

        $sbutton = "btn btn-default btn-sm";
        if ($saktiv == '1') {
          $sbutton = "btn btn-info btn-sm";
        }
        
        if ($n != 1){
          echo "      <a href=\"./user.php?JOB=PIDADD&USERID=" .$tuserid ."&PID=" .$nid ."\" class=\"" .$sbutton ."\" role=\"button\"data-toggle=\"tooltip\" data-placement=\"top\" title=\"" .$sort ." / " .$suser ."\">" .$npuser ."</a> \n\r";
        } 
     }
      $ergebnis->close(); 
    
      echo "    </div>\n\r";
      echo "  </div>\n\r";    

  ?>  

  <?php
    }
  }
 
  // LIST
  // ------------------------------------------------------------------------------------------
  if (($tjob=="LIST") || (($tjob=="EDIT") && ($tuserid == ""))){
    
    ?>
      <h3 class="text-center">Userlist</h3>
      </div>
    </div>  
   
    <table class="table sortable table-responsive table-condensed table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Company</th>
          <th>E-Mail</th>         
          <th>ASV</th>
        </tr>
      </thead>  
      <tbody>
    <?php
         
    $frage =  "SELECT * FROM bfuser;";
    $ergebnis = $mysqli->query($frage);
    while($zeile = $ergebnis->fetch_array()){
      echo "      <tr>\n\r";
      echo "        <td><a href=\"user.php?JOB=EDIT&USERID=" .$zeile['ID'] ."\">".$zeile['ID'] ."</a></td>\n\r";
      echo "        <td>" .$zeile['USER'] ."</td>\n\r";
      echo "        <td>" .$zeile['COMPANY'] ."</td>\n\r";
      echo "        <td>" .$zeile['EMAIL'] ."</td>\n\r";
      echo "        <td>" .$zeile['ADMIN'].$zeile['SUPERADMIN'].$zeile['VIEWALL'] ."</td>\n\r";
      echo "      </tr>\n\r";
    }
    echo "      </table>";     
    $ergebnis->close();
    
  }
}   
?>
      </div> 
    </div>
<?php



// ------------------------------------------------------footer
$mysqli->close();  
if ($sesOK == 1){
  echo "<br>\n\r";
  echo "status generated on: " .date('y/m/d,H:i:s') ."<br>\n\r";
  echo "&copy; 2018 <a href=\"http:\\\\www.lanthan.eu\">Lanthan GmbH & Co. KG </a>\n\r";
  echo "Remote Adress:: " .$tip ."<br>\n\r";      
  echo "</div>\n\r";
}

include "./sub/footer.php"; 
?>
