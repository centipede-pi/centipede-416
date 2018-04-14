<?php
include '../includes/login.inc';

header('Cache-Control: no-cache, must-revalidate');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
if( isset($_GET['display']) )  $display = 0 + $_GET['display'];       // .... .... .... ...1   display one output per row  (single column)
else                           $display = 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
<style type='text/css'>
iframe { width: 100%; height: 300px; border: none; }
td     { width: 50% }
h      { font-size: 45px; }
button   { font-size: 25px; }
select   { font-size: 25px; }

.apply:enabled  {  background-color: #FFFF00; }    /* yellow */
.apply:disabled {  background-color: #fcfcfc; color:black }    /* gray */

</style>

<?php
include '../includes/range_webkit.css';
?>

<title>Outputs</title>
</head>

<?php
$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");
   $ip = $_SERVER['SERVER_ADDR'];

   $rs = mysql_query("select ivalue FROM setup where type='vol_mixer'", $li);
   $vol_mixer = mysql_fetch_row( $rs )[0];
//   echo "vol_mixer = $vol_mixer<br>";
   if( $vol_mixer == 0 ) $vol_link = 1;
   else {
       $rs = mysql_query("select ivalue FROM setup where type='vol_link'", $li);
       $vol_link = mysql_fetch_row( $rs )[0];
       }
//   echo "vol_link = $vol_link<br>";
   $rs = mysql_query("select ivalue FROM setup where type='vol_mute'", $li);
   $vol_mute = mysql_fetch_row( $rs )[0];
//   echo "vol_mute = $vol_mute<br>";
   $rs = mysql_query("select ivalue FROM setup where type='vol_left'", $li);
   $vol_left = mysql_fetch_row( $rs )[0];
//   echo "vol_left = $vol_left<br>";
   if( $vol_link == 1 )  $vol_right = $vol_left;
   else {
       $rs = mysql_query("select ivalue FROM setup where type='vol_right'", $li);
       $vol_right = mysql_fetch_row( $rs )[0];
       }
//   echo "vol_right = $vol_right<br>";


echo "<body onload='GetMeters(\"$ip\"); Sequencer();'>";
?>


<script type="text/javascript">
var conn;
var BClist = [];      /* javascript array of iFrame IDs */
var PostMessages = [];
var battery_status = 0;
var battery_voltage = 0.0;
var battery_amps = 0.0;
var battery_status_txt = ["Startup", "Trickle Charging", "Charging", "Idle/cool down", "Discharging", "Battery MIssing", "n/a", "n/a", "n/a", "Power Down" ];
var grayTimer=0;

<?PHP
echo "var vol_mute = $vol_mute;";
echo "var vol_link = $vol_link;";
echo "var vol_mixer = $vol_mixer;";
include '../includes/index.js';
?>

function GetMeters(ip) {  // retrieve messages from server
   var meter = document.getElementById("meter");
   var url = "ws://" + ip + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
<?php
       $msg = "METER:";     /* build a message to send to the webSocket server:  METER:0,1,2,3,4,5  */
       $rs = mysql_query("select * FROM vals order by board, channel", $li);
       while( $row = mysql_fetch_object( $rs ) ) {
           $O = 2*$row->board + ($row->channel-1);
           $msg = $msg . "$O,";
           $l = 'B' .  $row->board . 'C' . $row->channel;
           echo(   "BClist.push('$l');\n");
           }
       $msg = substr( $msg, 0, -1 );      // trim the trailing comma
       echo(      "MeterMessage = '$msg';\n");
       echo(      "conn.send('$msg');\n");       // Send the list of board/channels we want to hear about
?>
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       conn.close();
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('color: ' + document.getElementById('CENT').style.color);
       if( document.getElementById('CENT').style.color == "gray" )   {
            document.getElementById('CENT').style.color = "blue";
            grayTimer = setTimeout(function() { document.getElementById('CENT').style.color = "gray"; }, 5000);
            }
       else {
            if( grayTimer != 0 ) clearTimeout( grayTimer );
            grayTimer = setTimeout(function() { document.getElementById('CENT').style.color = "gray"; }, 5000);
            }
       list = e.data.split(",");
       if( list[0].substr(0,3) == 'MTR' ) {       // MTR = without battery values
//           console.log( list )
           seq_switches = list[1];
           if( seq_switches & 2 )  s1 = "red";     else   s1 = "green";
           if( seq_switches & 1 )  s2 = "red";     else   s2 = "green";
           len = list.length;
           start = 2;

           if( list[0] == 'MTR2' ) {       // MTR2 = with battery values
               start = 6;
               battery_status = list[2];
               battery_voltage = list[3];
               battery_amps = list[4];
               battery_temp = list[5];
               ChoseImage( battery_status, battery_voltage);
               }
           for( i=start; i<len; i++) {
               window.frames[ BClist[i-start] ].set_meter( list[i] );
               window.frames[ BClist[i-start] ].document.getElementById('S1').style.backgroundColor = s1;
               window.frames[ BClist[i-start] ].document.getElementById('S2').style.backgroundColor = s2;
               }
           }
       if( list[0] == 'SET' ) {
           window.frames[ list[1] ].set( list[2], list[3], list[4], list[5], list[6] );
           }

       if( list[0] == 'ALL_STOP' ) {
           AllStop( 0 );
           }

       if( list[0] == 'SEQ' ) {
           orig_line_number += 1;
           if( list[1][0] > '#' )  {                   // discard comment lines
               var s = list[1];
               s = s.replace(/\s*=\s*/g, '=');         // remove spaces around equal signs
               s = s.replace(/\s+/g, ' ');             // remove extra spaces
               s = s.replace(/#.*/, ' ');              // remove trailing comment
               s = s.trim();                           // remove leading/trailing white space
               seq_index += 1;
               seq_table[seq_index] = s;
               seq_numb[seq_index] = orig_line_number;
               }
           }

       if( list[0] == 'SEQEND' ) {
           var n =  seq_index + 1;
           seq_index = 0;
           Display_seq();
           window.alert("The sequence file is loaded, " +  n + " lines.");
           }

       if( PostMessages.length > 0 ) {
           msg = PostMessages.shift();
           conn.send(msg);
           }
       else
           conn.send(MeterMessage);
       }
   }

function post(msg) {
   PostMessages.push(msg);
   }

function AllStop(local) {
   Run( 1 );                     // stop the sequencer, if running
   if(local==1) post("ALL_STOP");             // the Socket Server takes care of updating the database and sending out messages to the boards
   len = BClist.length;
   for( i=0; i<len; i++ ) {
       window.frames[ BClist[i] ].Stop();
       }
   reveal(1);                   // close sequencer window
   }

function BatteryDetails() {
    window.alert("Battery and Charger Status\n\n   Status=" + battery_status_txt[battery_status]
        + "\n   Volts=" + battery_voltage  + "\n   Amps=" + battery_amps + "\n   Temp=" + battery_temp);
   }

function setVolume() {
   msg = "VOLUME:SET:" + vol_mixer + ":" + vol_mute + ":" + document.getElementById("left").value + ":" + document.getElementById("right").value + ":" + vol_link;
   console.log( msg );
   post( msg );
   }


window.onbeforeunload=function() {
  conn.close();
  }

function Mute( ) {
    vol_mute ^= 1;
    if( vol_mute == 0 )  {
        document.getElementById('MUTE').style.backgroundColor = "#7CfC00";
        }
    else {
        document.getElementById('MUTE').style.backgroundColor = "#c0c0c0";
        }
    setVolume();
    }
function Link( ) {
    if ( vol_mixer == 0 ) return;
    vol_link ^= 1;
    if( vol_link == 1 )  {
        document.getElementById('LINK').style.backgroundColor = "#7CfC00";
        }
    else {
        document.getElementById('LINK').style.backgroundColor = "#c0c0c0";
        }
    setVolume();
    }


</script>

<?php
echo "<a href='/index.php'><img src='/images/back.jpg' alt='back' style='float:left'></a>\n";
if( $_SESSION['role'] == 'watch' ) echo "<h ID='CENT' style='color: gray'>Centipede 416</h>\n";
else                               echo "<h ID='CENT' style='color: gray' onclick='reveal(0)'>Centipede 416</h>\n";
echo "<a href='index.php?display=$display'><img src='/images/refresh.jpg' alt='refresh' style='float:right'></a>\n";
if( $_SESSION['role'] == 'watch' ) echo "<img src='/images/stop_dsbl.jpg' alt='stop' style='float:right'>\n";
else                               echo "<img src='/images/stop.jpg' alt='stop' style='float:right' onclick='AllStop(1)'>\n";
echo "<img id='BATTERY_IMG'  onclick='BatteryDetails()' style='float:right'>\n";

echo "<div id='seq' style='display: none; margin: 14px;' >";
  echo "<table style='padding: 4px; border: 4px solid black; width: 100%; height: 100%; font-size: 25px'>\n";
  echo "<tr><th colspan='2' style='width: 100%;color: white; background-color: MediumBlue;'>Sequencer Control</th></tr>\n";
  echo "<tr><td>";
     echo "<button type='button' class='apply' id='load' onclick='Load()'>LOAD</button>\n";
     echo "<select id='SEQFILE'>\n";
     $dlist = scandir( "../sequences" );
     foreach( $dlist as $key => $name ) {
         if( in_array( $name, array(".", ".." ) ) ) continue;
         echo "<option value='$name'>$name</option>\n";
         }
     echo "</select>\n";

     echo "<button type='button' id='TEST' onclick='Test()' style='background-color: #FF0000'>TEST</button>\n";
     echo "<button type='button' id='RUN' onclick='Run(0)' style='background-color: #FF0000'>OFF</button>\n";
     echo "</td><td>";
     echo "<table style='width: 100%'>";

     if( $vol_link == 1 )  $c = '#7CfC00';     else  $c = '#c0c0c0';
     echo "<tr><td style='width: 10%'>Left:</td>
<td><input type='range' id='left' oninput='if( vol_link ) document.getElementById(\"right\").value = this.value' onChange='setVolume()' min='0' max='100' value='$vol_left' style='width: 90%;'></td>
<td style='width: 10%'><button type='button' id='LINK' onclick='Link( )' style='background-color: $c'>LINK</button></td></tr>\n";

     if( $vol_mute == 0 )  $c = '#7CfC00';     else  $c = '#c0c0c0';
     echo "<tr><td style='width: 10%'>Right:</td>
<td><input type='range' id='right' oninput='if( vol_link ) document.getElementById(\"left\").value = this.value' onChange='setVolume()' min='0' max='100' value='$vol_right' style='width: 90%;'></td>
<td style='width: 10%'><button type='button' id='MUTE' onclick='Mute( )' style='background-color: $c'>MUTE</button></td></tr>\n";

     echo "</table>";


  echo "</td></tr>\n";

  $even = 1;
  $rs = mysql_query("select * FROM vals order by board, channel", $li);
  while( $row = mysql_fetch_object( $rs ) ) {
      if(( $display & 1 ) || ( $even == 1 ) ) echo "<tr>";
      echo "<td>";
          echo "<table style='height: 70px; width:100%; border: 1px solid black; '><tr>\n";
          echo "<td style='width: 20%;'>B$row->board,C$row->channel</td>\n";
          $v = $row->P3;
          if( $row->wave == 'Sweep' ) $v = $row->P4;
          if( $row->wave == 'Ramp' ) $v = $row->P4;
          if( $row->wave == 'Steps' ) $v = $row->P4;
          if( $row->wave == 'Random' ) $v = $row->P4;
          if( $row->wave == 'Annoy' ) $v = $row->P4;
          if( $row->wave == 'Steps-1' ) $v = $row->P4;
          if( $row->wave == 'Steps-2' ) $v = $row->P4;
          if( $row->wave == 'Steps-12' ) $v = $row->P4;
          if( $row->wave == 'Cycles-1' ) $v = $row->P5;
          if( $row->wave == 'Cycles-2' ) $v = $row->P5;
          if( $row->wave == 'Cycles-12' ) $v = $row->P5;
          if( $row->wave == 'Right' ) $v = $row->P4;
          if( $row->wave == 'Left' ) $v = $row->P4;
          if( $row->wave == 'Mono' ) $v = $row->P4;
          $id1 = 'V' .  "$row->board" . 'C' . "$row->channel";
          echo "<td style='width: 10%; border: 1px solid black; padding:4px; font-weight: bold'><span id='$id1'>$v</span></td>\n";
          $id2 = 'R' .  "$row->board" . 'C' . "$row->channel";
          echo "<td valign='center' style='width: 50%; padding: 4px;'><input type='range' id='$id2' oninput='showValue( \"$id1\", this.value)' min='0' max='301' value='$v' style='width: 100%;'></td>\n";
          $id3 = "$row->board" . 'C' . "$row->channel";
          echo "<td style='text-align: center'><button onclick='grab(\"$id3\");' style='background-color: #7CfC00' >GRAB</button></td>\n";
          echo "</tr></table>";
      echo "</td>";
      if(( $display & 1 ) || ( $even == 0 )) echo "</tr>";
      $even ^= 1;
      }
  if(( ~$display & 1 ) && ( $even == 1 ) ) echo "</tr>";
  echo "<tr><td colspan=2><table>";
      echo "<tr><td id='seq1'>&nbsp</td></tr>";
      echo "<tr><td id='seq2' style='background-color: #7CfC00'>No File Has been loaded</td></tr>";
      echo "<tr><td id='seq3'>&nbsp</td></tr>";
      echo "<tr><td id='seq4'>&nbsp</td></tr>";
      echo "<tr><td id='seq5'>&nbsp</td></tr>";
  echo "</td></tr></table>";
  echo "</table>";
echo "</div>\n";


echo "<script type='text/javascript'>\n";
echo "var iframe_contentDocument = '.document';\n";
echo "var iframe_contentWindow = '';\n";
echo "</script>\n";

include '../includes/sequencer.js';

echo ("<table class='outer' width='100%' cellpadding='5'>\n");

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

$rs = mysql_query("select * FROM vals order by board, channel", $li);
$even = 1;
while( $row = mysql_fetch_object( $rs ) ) {
   if(( $display & 1 ) || ( $even == 1 ) ) echo "<tr>";
   echo "<td>";
   $id = 'B' .  "$row->board" . 'C' . "$row->channel";
   $type = strtolower($row->type);
   echo ("<iframe id='$id' src='iframe.php?board=$row->board&channel=$row->channel&type=$type'></iframe>\n");
   echo("</td>");
   if(( $display & 1 ) || ( $even == 0 )) echo "</tr>";
   $even ^= 1;
   }
if(( ~$display & 1 ) && ( $even == 1 ) ) echo "</tr>";
echo("</table>");

?>
</body>
</html>


