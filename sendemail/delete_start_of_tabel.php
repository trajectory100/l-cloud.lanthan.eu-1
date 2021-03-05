
<?php
 //// --------- delete from test tabel to make it containing less data  -----------------------------------
require_once('db.php');

$times= strtotime('last week Monday');
$delete_from_test_tabel= $mysqli->query("DELETE FROM  topass_new WHERE  topass_new.ZEIT <  $times ");
$delete_from_test_tabel= $mysqli->query("DELETE FROM  test WHERE  test.time <  $times ");





