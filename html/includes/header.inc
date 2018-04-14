<?php
header('Cache-Control: no-cache, must-revalidate');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

function theme2id($theme_name) {
  $id = '';
  switch ($theme_name) {
    case "blue-green":
      $id = 0;
      break;
    case "white":
      $id = 1;
      break;
    case "dark":
      $id = 2;
      break;
    case "leather":
      $id = 3;
      break;
  }
  return $id;
}

function id2theme($theme_id) {
  $theme = "";
  switch ($theme_id) {
    case 0:
      $theme = "blue-green";
      break;
    case 1:
      $theme = "white";
      break;
    case 2:
      $theme = "dark";
      break;
    case 3:
      $theme = "leather";
      break;
  }
  return $theme;
}

?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />

<?php
  echo "<title>Centipede 416 $title</title>\n";

  # Open database
  global $li;
  $li = mysql_connect("localhost", "www-data", "estim")
    or die("Could not connect to SQL server: localhost");
  mysql_select_db("centipede", $li)
    or die("Could not select database: centipede");

  # Get Version
  global $version;
  $rs = mysql_query("select ivalue FROM setup where type='version'", $li);
  $version = mysql_fetch_row( $rs )[0];

  # Get Theme
  global $theme;
  global $theme_id;
  $rs = mysql_query("select ivalue FROM setup where type='theme'", $li);
  $theme_id = mysql_fetch_row( $rs )[0];
  if ( $theme_id == "" ) {
    $theme = "blue-green";
  } else {
    $theme = id2theme($theme_id);
    $theme_db = true;
  }

  global $host;
  $host = $_SERVER['HTTP_HOST'];
  echo "<link rel='stylesheet' type='text/css' href='/css/centipede.css'>\n";
  echo "<link rel='stylesheet' type='text/css' href='/css/centipede-$theme.css'>\n";
  include '/var/www/html/includes/get-meters.inc';
  echo "<body onload='GetMeters(\"$host\"); Sequencer();'>";
?>
<?php
if ((isset($show_header)) && ($show_header == false)) {
  return;
}
?>
<table width='100%'>
  <tr>
    <th width='20%' align='left'>
<?php if ($back != 'none') {
  echo "      <a href='/index.php'><img src='/images/back.png' alt='back'></a>";
} else {
  echo "      &nbsp;";
} ?>
    </th>
    <th width='60%' align='center'>
      <h ID='CENT' style='color: gray' <?php if( $_SESSION['role'] != 'watch' ) echo " onclick='reveal(0)'"; ?>><img src='/images/logo.png' width='80%'></h><br>
      <h id='CENT'><?php echo $title; ?></h>
    </th>
    <th width='20%' align='right'>
      <a href='<?php echo $_SERVER['REQUEST_URI']; ?>'><img src='/images/refresh.png' alt='refresh' style='float:right'></a>
      <?php 
if ($show_stop == true) {
  if ($_SESSION['role'] == 'watch') {
    echo "<img src='/images/stop_dsbl.png' alt='stop' style='float:right'>\n";
  } else {
    echo "<img src='/images/stop.png' alt='stop' style='float:right' onclick='AllStop(1)'>\n"; 
  }
} ?>
      <img id='BATTERY_IMG'  onclick='BatteryDetails()' style='float:right'>
    </th>
  </tr>
</table>
<hr>
