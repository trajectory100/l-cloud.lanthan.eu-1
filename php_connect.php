<?PHP
error_reporting(E_ALL);


// print_r(PDO::getAvailableDrivers());
//Array ( [0] => mysql [1] => odbc [2] => pgsql [3] => sqlite )

// $servername = "localhost";
// $username = "username";
// $password = "password";
// $dbname = "db00101046";

////first chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "'h2905532.stratoserver.net";
// $username = "db00101046";
// $password = "test123456";
////result   
//Connection failed: SQLSTATE[HY000] [2005] Unknown MySQL server host ''h2905532.stratoserver.net' (0)

////second chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "h2905532.stratoserver.net";
// $username = "u2905532";
// $password = "X33z99L@y123";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'u2905532'@'h2905532.stratoserver.net' (using password: YES)

////third chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "rdbms.strato.de";
// $username = "U969575";
// $password = "MySqL89dB3";
// $dbname = "DB969575";
////result   
//Connection failed: SQLSTATE[HY000] [2002] Can't connect to local MySQL server through socket '/tmp/mysql.sock' (2)

////forth chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "h2905532.stratoserver.net";
// $username = "";
// $password = "X33z99L@y123";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'u2905532'@'h2905532.stratoserver.net' (using password: YES)

////fifth chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "h2905532.stratoserver.net";
// $username = "";
// $password = "";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'u2905532'@'h2905532.stratoserver.net' (using password: NO)

////sixth chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "localhost";
// $username = "U969575";
// $password = "X33z99L@y123";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'u2905532'@'h2905532.stratoserver.net' (using password: YES)

////seventh chance //////////////////////////////////////////////////////////////////////////////////
$servername = "localhost";
$username = "U969575";
$password = "MySqL89dB3";
$dbname = "DB969575";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'root'@'localhost' (using password: YES)

////fifth chance //////////////////////////////////////////////////////////////////////////////////
// $servername = "h2905532.stratoserver.net";
// $username = "root";
// $password = "X33z99L@y123";
////result   
//Connection failed: SQLSTATE[28000] [1045] Access denied for user 'root'@'localhost' (using password: YES)

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}


