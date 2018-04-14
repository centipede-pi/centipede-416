<?php
include '/var/www/html/includes/login.inc';
global $title, $back, $show_stop;
$title = "Controller";
$back = "index.php";
$show_stop = true;
include '/var/www/html/includes/header.inc';

if( isset($_GET['display']) ) {
  $display = 0 + $_GET['display'];       // .... .... .... ...1   display one output per row  (single column)
} else {
  $display = 0;                          // .... .... .... ..1.   use web kit to rotate meter element in iframe, and meter width = 45px
}

$display = 22;

#include '/var/www/html/includes/range_moz.css';
?>
</head>

<?php
  $rs = mysql_query("select ivalue FROM setup where type='vol_mixer'", $li);
  $vol_mixer = mysql_fetch_row( $rs )[0];
  if( $vol_mixer == 0 ) {
    $vol_link = 1;
  } else {
    $rs = mysql_query("select ivalue FROM setup where type='vol_link'", $li);
    $vol_link = mysql_fetch_row( $rs )[0];
  }
  $rs = mysql_query("select ivalue FROM setup where type='vol_mute'", $li);
  $vol_mute = mysql_fetch_row( $rs )[0];
  $rs = mysql_query("select ivalue FROM setup where type='vol_left'", $li);
  $vol_left = mysql_fetch_row( $rs )[0];
  if( $vol_link == 1 ) {
    $vol_right = $vol_left;
  } else {
    $rs = mysql_query("select ivalue FROM setup where type='vol_right'", $li);
    $vol_right = mysql_fetch_row( $rs )[0];
  }

echo "<body onload='GetMeters(\"$host\"); Sequencer();'>";
?>


<script type="text/javascript">
var conn;
var BClist = [];      /* javascript array of iFrame IDs */
var PostMessages = [];
var battery_status = 0;
var battery_voltage = 0.0;
var battery_amps = 0.0;
var battery_status_txt = ["Startup", "Trickle Charging", "Charging", "Idle/cool down", "Discharging", "Battery Missing", "n/a", "n/a", "n/a", "Power Down" ];
var grayTimer=0;

<?PHP
echo "var vol_mute = $vol_mute;";
echo "var vol_link = $vol_link;";
echo "var vol_mixer = $vol_mixer;";
include '/var/www/html/includes/index.js';
?>

function post(msg) {
   PostMessages.push(msg);
   }

function AllStop(local) {
   Run( 1 );                     // stop the sequencer, if running
   if(local==1) post("ALL_STOP");      // the Socket Server takes care of updating the database and sending out messages to the boards
   len = BClist.length;
   for( i=0; i<len; i++ ) {
       window.frames[ BClist[i] ].contentWindow.Stop();       // this is just to change the OFF/ON button
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
echo "<div id='seq' style='display: none; margin: 14px;' >";

  echo "<table style='padding: 4px; border: 4px solid black; width: 100%; height: 100%; font-size: 25px'>\n";
  echo "<tr><th colspan='2' style='width: 100%;color: white; background-color: MediumBlue;'>Sequencer Control</th></tr>\n";
  echo "<tr><td>";
     echo "<button type='button' class='apply' id='load' onclick='Load()'>LOAD</button>\n";
     echo "<select id='SEQFILE'>\n";
     $dlist = scandir( "/var/www/html/sequences" );
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
   echo "var iframe_contentDocument = '.contentDocument';\n";
   echo "var iframe_contentWindow = '.contentWindow';\n";
echo "</script>\n";

include '/var/www/html/includes/sequencer.js';

#echo ("<table class='outer' width='100%' cellpadding='5'>\n");
echo "<div class='control_wrapper'>\n";

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

$rs = mysql_query("select * FROM vals order by board, channel", $li);
$even = 1;
while( $row = mysql_fetch_object( $rs ) ) {
   if(( $display & 1 ) || ( $even == 1 ) ) echo "  <div class='control_pair'>";
#   echo "<td>";
   echo "    <div class='control_div'>\n";
   $id = 'B' .  "$row->board" . 'C' . "$row->channel";
   $type = strtolower($row->type);
   echo ("<iframe id='$id' class='control_iframe' src='controller-iframe.php?board=$row->board&channel=$row->channel&type=$type&display=$display'></iframe>\n");
#   echo("</td>");
   echo "    </div>\n";
   if(( $display & 1 ) || ( $even == 0 )) echo "  </div>";
   $even ^= 1;
   }
if(( ~$display & 1 ) && ( $even == 1 ) ) echo "  </div>";
#echo("</table>");
echo "</div>\n";
include '/var/www/html/includes/footer.inc';
?>
</body>
</html>


