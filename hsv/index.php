<?PHP

$rest = (1526141700 - 3600) - time();
if ($rest < 0)
{
  $rest = 0;
}

$d = floor($rest / 86400);
$rest = $rest - ($d * 86400);
$h = floor($rest / 3600);
$rest = $rest - ($h * 3600);
$m = floor($rest / 60);
$s = $rest - (60 * $m);

echo "[rev]r1/2018\n\r";
echo "[hsv]" .$d  .":" .$h .":" .$m .":" .$s ."\n\r";
echo "[motto]Zweite Liga!\n\r";
?>
