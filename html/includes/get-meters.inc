<?php 
include '/var/www/html/includes/sequencer.js';
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

<?php
#echo "var vol_mute = $vol_mute;";
#echo "var vol_link = $vol_link;";
#echo "var vol_mixer = $vol_mixer;";
include '/var/www/html/includes/index.js';
?>

function GetMeters(host) {  // retrieve messages from server
   var meter = document.getElementById("meter");
   var url = "ws://" + host + ":8000";
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
             if ( typeof window.frames[ BClist[i-start] ] != 'undefined' ) {
               window.frames[ BClist[i-start] ].contentDocument.getElementById('meter').value = list[i];
               window.frames[ BClist[i-start] ].contentDocument.getElementById('S1').style.backgroundColor = s1;
               window.frames[ BClist[i-start] ].contentDocument.getElementById('S2').style.backgroundColor = s2;
               }
             }
           }
       if( list[0] == 'SET' ) {
           window.frames[ list[1] ].contentWindow.set( list[2], list[3], list[4], list[5], list[6] );
           }

       if( list[0] == 'ALL_STOP' ) {
           AllStop( 0 );
           }

       if( list[0] == 'SEQ' ) {
//           console.log( list )
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

function BatteryDetails() {
    window.alert("Battery and Charger Status\n\n   Status=" + battery_status_txt[battery_status]
        + "\n   Volts=" + battery_voltage  + "\n   Amps=" + battery_amps + "\n   Temp=" + battery_temp);
   }

</script>
