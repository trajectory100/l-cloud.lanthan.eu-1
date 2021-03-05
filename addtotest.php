<?PHP
require_once('db.php');
// $fe= date( strtotime(date('Y-m')." -6 month"));
$currentTime = time();
$timeToSubtract = (2* 60);
$limittime = $currentTime - $timeToSubtract;
// $projini = $mysqli->query("CREATE TABLE test LIKE  ;");
$pr = $mysqli->query("INSERT INTO test SELECT * FROM bfccio where bfccio.time > $limittime ;");

$pr2 = $mysqli->query("INSERT INTO topass_new SELECT * FROM bftopass where bftopass.ZEIT > $limittime ;");

// $prm = $mysqli->query("INSERT INTO Archive_bfccio_all SELECT * FROM bfccio where bfccio.time > $limittime ;");
