<?php
header('Cache-Control: no-cache, must-revalidate');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
$VERSION = "2.00";

function SelectOptions( $v, $ar ) {
   foreach( $ar as $o ) {
       echo "<option value='$o'";
       if($v == $o)  echo " selected";
       echo ">$o\n";
       }
   echo "</select>\n";
   }

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
<style type='text/css'>
h      { font-size: 50px; }
th     { font-size: 25px; }
td     { font-size: 25px; }
td.ctr { font-size: 25px; text-align: center}
#ibox  { font-size: 25px; height:25px}
td.red { font-size: 25px; text-align: center; color:black; background-color: red}
td.add { font-size: 25px; text-align: center; color:white; background-color: blue}
td.blue { cursor: pointer; cursor:hand; font-size: 25px; text-align: left; color:blue; width:100%; text-decoration: underline; }
</style>
<title>Centipede</title>
</head>

<?php
$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");

   $host = $_SERVER['HTTP_HOST'];
echo "<body>\n";
?>


<script type="text/javascript">
var conn;

function MAIN(host) {  // retrieve messages from server
   var log = document.getElementById("LOG");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       var index = document.getElementById('POD').selectedIndex;
       var pod = document.getElementById('POD').options[index].value;
       var index = document.getElementById('HEXFILE').selectedIndex;
       var file = document.getElementById('HEXFILE').options[index].value;
       document.getElementById('LOG').innerHTML =  "";
       conn.send('MAIN:' + pod + ',' + file);   // Send
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('Server: ' + e.data);
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + e.data + '<br>';
       }
   }

function BOOT(host) {  // retrieve messages from server
   var log = document.getElementById("LOG");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       var index = document.getElementById('POD').selectedIndex;
       var pod = document.getElementById('POD').options[index].value;
       var index = document.getElementById('HEXFILE').selectedIndex;
       var file = document.getElementById('HEXFILE').options[index].value;
       document.getElementById('LOG').innerHTML =  "";
       conn.send('BOOT:' + pod + ',' + file);   // Send
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('Server: ' + e.data);
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + e.data + '<br>';
       }
   }

function VERIFY(host) {  // retrieve messages from server
   var log = document.getElementById("LOG");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       var index = document.getElementById('POD').selectedIndex;
       var pod = document.getElementById('POD').options[index].value;
       var index = document.getElementById('HEXFILE').selectedIndex;
       var file = document.getElementById('HEXFILE').options[index].value;
       document.getElementById('LOG').innerHTML =  "";
       conn.send('VERIFY:' + pod + ',' + file);   // Send
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('Server: ' + e.data);
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + e.data + '<br>';
       }
   }

function Delete( board, channel) {
   r = window.confirm("Are you sure you want to delete board " + board + ", channel " + channel + "?");
   if( r == true ) {
       document.getElementById( 'DEL_BRD' ).value = board;
       document.getElementById( 'DEL_CHN' ).value = channel;
       document.getElementById( 'DEL' ).submit();
       }
   }

function Title( board, channel, title) {
   r = window.prompt("If you wish to change the title, type the new value below..", title );
   if( r != null ) {
       document.getElementById( 'TITLE_BRD' ).value = board;
       document.getElementById( 'TITLE_CHN' ).value = channel;
       document.getElementById( 'TITLE_VAL' ).value = r;
       document.getElementById( 'TITLE' ).submit();
       }
   }

function PASSWD( pass ) {
   r = window.prompt("If you wish to change the passphrase, type the new value below..", pass );
   if( r != null ) {
       document.getElementById( 'PASS_VAL' ).value = r;
       document.getElementById( 'PASS' ).submit();
       }
   }

function SSID( ssid ) {
   r = window.prompt("If you wish to change the SSID, type the new value below..", ssid );
   if( r != null ) {
       document.getElementById( 'SSID_VAL' ).value = r;
       document.getElementById( 'SSID' ).submit();
       }
   }

function TIP( v ) {
   document.getElementById( 'TIP_VAL' ).value = v;
   document.getElementById( 'TIP' ).submit();
   }

function SPEECH( v ) {
   document.getElementById( 'SPEECH_VAL' ).value = v;
   document.getElementById( 'SPEECH' ).submit();
   }

function Add() {
   document.getElementById( 'ADD' ).submit();
   }

function staticIP() {
   window.open("http:/includes/staticip.php", "staticipWindow", "width=600,height=375,left=100,top=100");
   }
</script>

<?php
echo "<a href='index.php'><img src='/images/back.png' alt='back' style='float:left'></a>\n";
echo ("<center><h style='color: blue'>Centipede Options</h></center>");

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }


   $action = post('ACTION');
   if( $action == 'DELETE' ) {
       $board = 0 + post('BOARD');
       $channel = 0 + post('CHANNEL');
       mysql_query("delete FROM vals where board=$board and channel=$channel", $li);
       }
   if( $action == 'TITLE' ) {
       $board = 0 + post('BOARD');
       $channel = 0 + post('CHANNEL');
       $title = post('TITLE');
       mysql_query("update vals set title = '$title' where board=$board and channel=$channel", $li);
       }

   do if( $action == 'ADD' ) {
       $board = 0 + post('BOARD');
       if(( $board > 15 ) || ($board < 0 ) ) {
           echo "<p>Add failed, invalid board number<br>";
           break;
           }
       $channel = 0 + post('CHANNEL');
       if(( $channel > 2 ) || ( $channel < 1 )) {
           echo "<p>Add failed, invalid channel number<br>";
           break;
           }
       $title = post('TITLE');
       $sql =  "select COUNT(*) as c from vals where board=$board and channel=$channel";
       $rs = mysql_query( $sql, $li);
       $row = mysql_fetch_object( $rs );
       if( $row->c > 0 ) {
           echo "<p>Add failed, duplicate record<br>";
           break;
           }
       $type = post('TYPE');
       if( $type == "Estim" )  $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=0, P4=0, P5=0, run=0";
       if( $type == "Viby" )   $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=0, P4=0, P5=0, run=0";
       if( $type == "Relay" )  $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=1, P4=0, P5=0, run=0";
//       echo "<p>$sql";
       mysql_query( $sql, $li);
       }  while(false);

   if( $action == 'PASS' ) {
       $phrase =  post('PHRASE');
       echo "new phrase is:  |$phrase| power cycle the machine to put this into effect<br>";
       $str = file_get_contents( '/etc/hostapd/hostapd.conf' );
       $pass_start = strpos( $str, "wpa_passphrase=" );
       $pass_end = strpos( $str, "\n",  $pass_start );
       $pass = substr( $str, $pass_start, $pass_end - $pass_start );
//       echo  "<p>|$pass|" ;

       $str2 = str_replace( $pass, "wpa_passphrase=" . $phrase, $str );
//       $str3 = str_replace( "\n", '<br>', $str2 );
//       echo  "<p>$str3" ;
       $str = file_put_contents( '/etc/hostapd/hostapd.conf', $str2 );
       }

   if( $action == 'SSID' ) {
       $nid =  post('SSID');
       echo "new SSID is:  |$nid| power cycle the machine to put this into effect<br>";
       $str = file_get_contents( '/etc/hostapd/hostapd.conf' );
       $ssid_start = strpos( $str, "ssid=" );
       $ssid_end = strpos( $str, "\n",  $ssid_start );
       $ssid = substr( $str, $ssid_start, $ssid_end - $ssid_start );
//       echo  "<p>|$ssid|" ;

       $str2 = str_replace( $ssid, "ssid=" . $nid, $str );
//       $str3 = str_replace( "\n", '<br>', $str2 );
//       echo  "<p>$str3" ;
       $str = file_put_contents( '/etc/hostapd/hostapd.conf', $str2 );
       }

   if( $action == 'TIP' ) {
       $val = 0 + post('VALUE');
       mysql_query("update setup set ivalue = ivalue ^ $val where type='contacts'", $li);
       }

   if( $action == 'SPEECH' ) {
       $val = 0 + post('VALUE');
       mysql_query("update setup set ivalue = ivalue ^ $val where type='speech'", $li);
       }


   $rs = mysql_query("select * FROM vals order by board, channel", $li);
   echo "<p><table border='2' style='width:100%'>\n";
   echo "<tr><th>BRD</th><th>CHN</th><th>TYPE</th><th style='width: 60%' colspan='2'>TITLE</th></tr>\n";
       echo "<tr><td class='ctr'>0</td><td class='ctr'>n/a</td><td class='ctr'>Charger</td>";
       echo "<td class='blue'>Internal Battery Charger</td>";
       echo "<td width='10%' class='red'> </td></tr>\n";
   while( $row = mysql_fetch_object( $rs ) ) {
       echo "<tr><td class='ctr'>$row->board</td><td class='ctr'>$row->channel</td><td class='ctr'>$row->type</td>";
       echo "<td class='blue' onclick='Title($row->board,$row->channel,\"$row->title\")'>$row->title</td>";
       echo "<td width='10%' class='red' onclick='Delete($row->board,$row->channel)'>DEL</td></tr>\n";
       }
   echo "<form id='ADD' method='post'>\n";
   echo "<input type='hidden' name='ACTION' value='ADD'>\n";
   echo "<tr><td class='ctr'><input type='number' name='BOARD' max=15 min=0 required='required'></td>\n";
   echo "<td class='ctr'><input type='number' name='CHANNEL' max=2 min=1></td>\n";

   echo "<td class='ctr'><select name='TYPE' size='1'>";
   SelectOptions( "none", array('Estim', 'Viby', 'Relay' ) );
   echo "</td>\n";

   echo "<td class='blue'><input type='text' name='TITLE' maxlength=32 size=32></td>\n";
   echo "<td width='10%' class='add' onclick='Add()'>ADD</td></tr>\n";
   echo "</form>\n";

   echo "</table>";

   echo "<form id='DEL' method='post'>";
   echo "<input type='hidden' name='ACTION' value='DELETE'>";
   echo "<input id='DEL_BRD' type='hidden' name='BOARD' value='-1'>";
   echo "<input id='DEL_CHN' type='hidden' name='CHANNEL' value='-1'>";
   echo "</form>";

   echo "<form id='TITLE' method='post'>";
   echo "<input type='hidden' name='ACTION' value='TITLE'>";
   echo "<input id='TITLE_BRD' type='hidden' name='BOARD' value='-1'>";
   echo "<input id='TITLE_CHN' type='hidden' name='CHANNEL' value='-1'>";
   echo "<input id='TITLE_VAL' type='hidden' name='TITLE' value='new'>";
   echo "</form>";

   echo "<form id='PASS' method='post'>";
   echo "<input type='hidden' name='ACTION' value='PASS'>";
   echo "<input id='PASS_VAL' type='hidden' name='PHRASE' value='new'>";
   echo "</form>";

   echo "<form id='SSID' method='post'>";
   echo "<input type='hidden' name='ACTION' value='SSID'>";
   echo "<input id='SSID_VAL' type='hidden' name='SSID' value='new'>";
   echo "</form>";

   echo "<form id='TIP' method='post'>";
   echo "<input type='hidden' name='ACTION' value='TIP'>";
   echo "<input id='TIP_VAL' type='hidden' name='VALUE' value='new'>";
   echo "</form>";

   echo "<form id='SPEECH' method='post'>";
   echo "<input type='hidden' name='ACTION' value='SPEECH'>";
   echo "<input id='SPEECH_VAL' type='hidden' name='VALUE' value='new'>";
   echo "</form>";


   echo "<br><br><table border='2' style='width:100%' >";
   $str = file_get_contents( '/etc/hostapd/hostapd.conf' );
//   $str2 = str_replace( "\n", '<br>', $str );
//   echo  "<p>$str2" ;
   $pass_start = 15 + strpos( $str, "wpa_passphrase=" );
   $pass_end = strpos( $str, "\n",  $pass_start );
   $pass = substr( $str, $pass_start, $pass_end - $pass_start );

   $ssid_start = 5 + strpos( $str, "ssid=" );
   $ssid_end = strpos( $str, "\n",  $ssid_start );
   $ssid = substr( $str, $ssid_start, $ssid_end - $ssid_start );
   



   echo "<tr><th style='width: 70%; text-align: left;'>WIFI passphrase:</th>";
   echo "<td class='blue' onclick='PASSWD(\"$pass\")' colspan=2>$pass</td></tr>\n";

   echo "<tr><th style='width: 70%; text-align: left;'>WIFI SSID:</th>";
   echo "<td class='blue' onclick='SSID(\"$ssid\")' colspan=2>$ssid</td></tr>\n";

   echo "<tr><td style='width: 70%; text-align: left;'><b>Current IP Addesses:</b>  <small><i>( 192.168.42.1 is the address to use for a WIFI connection.
   If a second address is shown, it is the address in use for a wired connection.)</i></small></th>
   <button onclick='staticIP()'>Static IP</button><td>\n";
   $lines = array();
   exec( "/sbin/ifconfig", $lines );
   foreach ($lines as $line ) {
     $line = trim($line);
     if( substr($line, 0, 10) != "inet addr:" ) continue;
     $line = substr( $line, 10, strpos( $line, 'B' ) - 10);
     if( substr( $line, 0, 3 ) == '127' ) continue;
     echo "$line<br>";
     } 
   echo "</td></tr>\n";

   echo "<tr><th style='width: 20%; text-align: left;'>Upgrade Boards:\n";
   echo "<select id=POD>\n";
   $rs = mysql_query("select * FROM vals order by board, channel", $li);
   $last = -1;
   echo "<option value='0'>Board: 0</option>\n";
   while( $row = mysql_fetch_object( $rs ) ) {
       if( $last !=  $row->board ) {
           echo "<option value='$row->board'>Board: $row->board</option>\n";
           $last =  $row->board;
           }
       }

   echo "</select>\n";

   echo "<select id='HEXFILE'>\n";
   $dlist = scandir( "firmware" );
   foreach( $dlist as $key => $name ) {
     if( in_array( $name, array(".", ".." ) ) ) continue;
     echo "<option value='$name'>File: $name</option>\n";
     }
   echo "</select></th>\n";
   echo "<td><button type='button' value='MAIN' onclick='MAIN(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>MAIN</button>\n";
   echo "<button type='button' value='BOOT' onclick='BOOT(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>BOOT</button>\n";
   echo "<button type='button' value='VERIFY' onclick='VERIFY(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>VERIFY</button></td></tr>\n";

   $rs = mysql_query("select ivalue FROM setup where type='contacts'", $li);
   $row = mysql_fetch_object( $rs );
   if( $row->ivalue & 2 ) echo "<tr><th style='text-align: left;'>Contact 1 is active open</td><td><button type='button' value='CHANGE' onclick='TIP(2)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Contact 1 is active shorted</td><td><button type='button' value='CHANGE' onclick='TIP(2)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   if( $row->ivalue & 1 ) echo "<tr><th style='text-align: left;'>Contact 2 is active open</td><td><button type='button' value='CHANGE' onclick='TIP(1)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Contact 2 is active shorted</td><td><button type='button' value='CHANGE' onclick='TIP(1)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";

   $rs = mysql_query("select ivalue FROM setup where type='speech'", $li);
   $row = mysql_fetch_object( $rs );
   if( $row->ivalue & 1 ) echo "<tr><th style='text-align: left;'>Speech is delivered by a female voice</td><td><button type='button' value='CHANGE' onclick='SPEECH(1)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Speech is delivered by a male voice</td><td><button type='button' value='CHANGE' onclick='SPEECH(1)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";


   echo "</table>\n";
   
   echo "<div id='LOG'>\n";
   echo "<hr>";
   echo "</div>\n";

echo "</body>";
echo "</html>";
exit();
?>
