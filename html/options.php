<?php
global $title, $back;
$title = "Options";
$back = "/index.php";

include '/var/www/html/includes/login.inc';
include '/var/www/html/includes/header.inc';

if( $_SESSION['role'] != 'admin') {
    echo "<!doctype html><html lang='en'><head>";
    echo "<body>Only an administrator can access these functions</body></html>";
    exit();
    }

function SelectOptions( $v, $ar ) {
   foreach( $ar as $o ) {
       echo "<option value='$o'";
       if($v == $o)  echo " selected";
       echo ">$o\n";
       }
   echo "</select>\n";
   }

?>

<?php
$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");

   $host = $_SERVER['HTTP_HOST'];
echo "<body onload='VERSIONS(\"$host\")'>\n";

?>


<script type="text/javascript">
var conn;
var currTheme = "<?php echo $theme ?>";
var currThemeName;

<?php
  $rs = mysql_query("select * FROM vals order by board, channel", $li);
   echo "var types = [];\n";
   echo "types[0] = 'Power';\n";
   while( $row = mysql_fetch_object( $rs ) ) {
       echo "types[$row->board] = '$row->type';\n";
       }
?>

if( !String.prototype.includes) {
   String.prototype.includes = function(search) {
      'use strict';
      return this.indexOf(search) !== -1;
   };
}


function MAIN(ip) {  // retrieve messages from server
   var log = document.getElementById("LOG");
   var url = "ws://" + ip + ":8000";
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
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + '<hr>' + e.data + '<br>';
       if( e.data == '<b>Firmware Upgrade is complete</b>' )  VERSIONS(ip);
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
//       console.log('Server: |' + e.data + '|');
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + e.data + '<br>';
       if( e.data == '<b>Boot Upgrade is complete</b>' )  VERSIONS(host);
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


function VERSIONS(host) {  // retrieve messages from server
//   console.log('VERSIONS() called');
   var log = document.getElementById("LOG");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       var index = document.getElementById('POD').selectedIndex;
       var pod = document.getElementById('POD').options[index].value;
       var index = document.getElementById('HEXFILE').selectedIndex;
       var file = document.getElementById('HEXFILE').options[index].value;
       conn.send('VERSIONS:' + pod );   // Send
       }
   conn.onerror = function (error) {      // Log errors
//       console.log('WebSocket Error ' + error);
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('Server: ' + e.data);
       document.getElementById('VERSION').innerHTML =   e.data;

       var index = document.getElementById('POD').selectedIndex;
       var pod = document.getElementById('POD').options[index].value;    //  'board: n'
       var brd = parseInt(  pod.replace(/^Board:\s/, '')  );             // n  board number as an int
       var type = types[ brd ];                                          // get board type based on the database built table

       var opts = document.getElementById('HEXFILE').options.length;    // select a file to use when flashing
//       console.log('number of files is: ' + opts);                      // number of options in the list
       var patt = new RegExp("M\\d\\d\\d\\s");
       for( i=0; i<opts; i++ ) {
           var nam = document.getElementById('HEXFILE').options[i].text;
           document.getElementById('HEXFILE').options[i].disabled=true;   // assume disabled
           if( patt.test( nam ) == false ) continue;                      // disable older files with no version number
//           console.log( nam + ' returned true');                      // number of options in the list
           if( brd == 0 ) {                     // if board=0  only Power is enabled, Power is selected
               if(  nam.includes( 'Power' )) {
                   document.getElementById('HEXFILE').options[i].disabled=false;
                   document.getElementById('HEXFILE').selectedIndex = i;
                   }
               continue;
               }    // brd == 0
           if(( brd == 1 ) || ( brd == 2 )) {                     // if board=1=2  only Estim is enabled, Estim is selected
               if(  nam.includes( 'Estim' )) {
                   document.getElementById('HEXFILE').options[i].disabled=false;
                   document.getElementById('HEXFILE').selectedIndex = i;
                   }
               continue;
               }
           if(  nam.includes( type )) {      // for pods, enable & select based on database settings
               document.getElementById('HEXFILE').options[i].disabled=false;
               document.getElementById('HEXFILE').selectedIndex = i;
               }
           continue;
           }    // for( i == 0

//                                             chose which buttons to enable
       if( e.data == 'NC' ) {                           // not connected boards
           document.getElementById('VERSION').innerHTML =  "Not Connected";
           document.getElementById('MAIN').disabled = true;
           document.getElementById('BOOT').disabled = true;
           document.getElementById('VERIFY').disabled = true;
           }
       else if( e.data == 'OLD' ) {                           // not connected boards
           document.getElementById('VERSION').innerHTML =  "Needs Updating: MAIN then BOOT";
           document.getElementById('MAIN').disabled = false;
           document.getElementById('BOOT').disabled = false;
           document.getElementById('VERIFY').disabled = false;
           }
       else {
           r = e.data.split(',');
           for( i=0; i<r.length; i++)   r[i] = r[i].trim();
           main_now = "M" + r[5] + r[6];
           boot_now = "B" + r[7] + r[8];
           type_now = ['None', 'Power', 'Estim', 'Relay', 'Viby', 'Regulator'][ parseInt( r[2] ) ];
//           console.log( 'type_now=' + type_now );
           if( type != type_now ) alert('The board you are FLASHING appears to be a "' + type_now + '" type,  not a "' + type +'" type.\nIf you continue, the board may no longer work.');
           document.getElementById('VERSION').innerHTML =  "Now:  " + main_now + " " + boot_now;
           var index = document.getElementById('HEXFILE').selectedIndex;
           var file = document.getElementById('HEXFILE').options[index].value;
           if(( file.includes( main_now ))  && ( file.includes( boot_now ) ) ) {
               document.getElementById('MAIN').disabled = false;  // true;
               document.getElementById('BOOT').disabled = false;  // true;
               document.getElementById('VERIFY').disabled = false;
               document.getElementById('VERSION').innerHTML =  "Up To Date";
               }
           else if( file.includes( main_now ) ) {
               document.getElementById('MAIN').disabled = true;
               document.getElementById('BOOT').disabled = false;
               document.getElementById('VERIFY').disabled = false;
               document.getElementById('VERSION').innerHTML =  "BOOT needs update";
               }
           else  {
               document.getElementById('MAIN').disabled = false;
               document.getElementById('BOOT').disabled = true;
               document.getElementById('VERIFY').disabled = false;
               document.getElementById('VERSION').innerHTML =  "MAIN needs update";
               }
           }   // else
       }  // onmessage()
    } // VERSONS()

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

function PI_PASSWD( pass ) {
   r = window.prompt("If you wish to change the password, type the new value below..", pass );
   if( r != null ) {
       document.getElementById( 'PI_PASS_VAL' ).value = r;
       document.getElementById( 'PI_PASS' ).submit();
       }
   }

function WIFI_PASSWD( pass ) {
   r = window.prompt("If you wish to change the passphrase, type the new value below..", pass );
   if( r != null ) {
       document.getElementById( 'WIFI_PASS_VAL' ).value = r;
       document.getElementById( 'WIFI_PASS' ).submit();
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

function LOGON( v ) {
   document.getElementById( 'LOGON_VAL' ).value = v;
   document.getElementById( 'LOGON' ).submit();
   }

function SSH( v ) {
   document.getElementById( 'SSH_VAL' ).value = v;
   document.getElementById( 'SSH' ).submit();
   }

function SOUND( v ) {
   document.getElementById( 'SOUND_VAL' ).value = v;
   document.getElementById( 'SOUND' ).submit();
   }

function Add() {
   document.getElementById( 'ADD' ).submit();
   }

function eth0() {
   window.open("http:/includes/eth0.php", "eth0Window", "width=600,height=375,left=100,top=100");
   }

function wlan0() {
   window.open("http:/includes/wlan0.php", "wlan0Window", "width=600,height=375,left=100,top=100");
   }


function ssh() {
   window.open("http:/includes/ssh.php", "sshWindow", "width=600,height=375,left=100,top=100");
   }

function CHANGE_THEME( theme ) {
  document.getElementById( 'CHANGE_THEME_VAL' ).value = theme.value;
  document.getElementById( 'THEME_FORM' ).submit();
}

function getTheme(theme) {
  theme.value = currTheme;
  currThemeName = theme.options[theme.selectedIndex].innerHTML;
  document.getElementById("theme_name").innerHTML = "Using " + currThemeName + " theme.";
}

</script>

<?php

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
       if( $type == "Estim" )  $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=30, P4=0, P5=0, run=0";
       if( $type == "Relay" )  $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=1, P4=0, P5=0, run=0";
       if( $type == "Viby" )   $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=30, P4=0, P5=0, run=0";
       if( $type == "Regulator" )   $sql =  "insert into vals set type='$type', board=$board, channel=$channel, title='$title', wave='Basic', P3=50, P4=0, P5=0, run=0";
//       echo "<p>$sql";
       mysql_query( $sql, $li);
       }  while(false);

   if( $action == 'PI_PASS' ) {
       $pwd = post('PHRASE');
       echo "new password is:  |$pwd|<br>";
       exec( "echo 'pi:$pwd' | sudo chpasswd", $rst );
       }

   if( $action == 'WIFI_PASS' ) {
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

   if( $action == 'LOGON' ) {
       $val = 0 + post('VALUE');
       mysql_query("update setup set ivalue = $val where type='login'", $li);
       }

   if( $action == 'SSH' ) {
       $val = 0 + post('VALUE');
       if( $val == 1 )   exec( "sudo /usr/sbin/update-rc.d ssh defaults", $rst );
       else              exec( "sudo /usr/sbin/update-rc.d -f ssh remove", $rst );
       }

   $SSH = file_exists( "/etc/rc3.d/S02ssh" );

   if( $action == 'SOUND' ) {
       $val = 0 + post('VALUE');
       mysql_query("update setup set ivalue = $val where type='vol_mixer'", $li);
       exec( "sudo /bin/cp /root/asoundrc_$val /root/.asoundrc", $rst );
       }
   $rs = mysql_query("select * FROM setup where type='vol_mixer'", $li);
   $row = mysql_fetch_object( $rs );
   $SOUND = $row->ivalue;

   if( $action == 'CHANGE_THEME' ) {
     $val = theme2id(post('CHANGE_THEME_VAL'));
     if ( $theme_id == "" ) {
       mysql_query("INSERT INTO setup SET ivalue = $val, type='theme'", $li);
     } else {
       mysql_query("UPDATE setup SET ivalue = $val WHERE type='theme'", $li);
     }
     echo "Changing Theme to $val<br>.";
     echo "<script type='text/javascript'>window.location.replace('".$_SERVER['REQUEST_URI']."');</script>";
   }


   $rs = mysql_query("select * FROM vals order by board, channel", $li);
   echo "<p><table border='2' style='width:100%'>\n";
   echo "<tr><th>BRD</th><th>CHN</th><th>TYPE</th><th style='width: 60%' colspan='2'>TITLE</th></tr>\n";
       echo "<tr><td class='ctr'>0</td><td class='ctr'>n/a</td><td class='ctr'>Power</td>";
       echo "<td class='blue'>Power Management Processor</td>";
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
   SelectOptions( "none", array('Estim', 'Relay', 'Viby', 'Regulator' ) );
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

   echo "<form id='PI_PASS' method='post'>";
   echo "<input type='hidden' name='ACTION' value='PI_PASS'>";
   echo "<input id='PI_PASS_VAL' type='hidden' name='PHRASE' value='new'>";
   echo "</form>";

   echo "<form id='WIFI_PASS' method='post'>";
   echo "<input type='hidden' name='ACTION' value='WIFI_PASS'>";
   echo "<input id='WIFI_PASS_VAL' type='hidden' name='PHRASE' value='new'>";
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

   echo "<form id='LOGON' method='post'>";
   echo "<input type='hidden' name='ACTION' value='LOGON'>";
   echo "<input id='LOGON_VAL' type='hidden' name='VALUE' value='new'>";
   echo "</form>";

   echo "<form id='SSH' method='post'>";
   echo "<input type='hidden' name='ACTION' value='SSH'>";
   echo "<input id='SSH_VAL' type='hidden' name='VALUE' value='new'>";
   echo "</form>";

   echo "<form id='SOUND' method='post'>";
   echo "<input type='hidden' name='ACTION' value='SOUND'>";
   echo "<input id='SOUND_VAL' type='hidden' name='VALUE' value='new'>";
   echo "</form>";


   echo "<br><br><table border='2' style='width:100%' >";
   echo "<tr><th style='width: 70%; text-align: left;'>PI Password:</th>";
   echo "<td  colspan=2><button type='button' value='CHANGE' onclick='PI_PASSWD()' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></td></tr>\n";

   $str = file_get_contents( '/boot/config.txt' );
   if ( strpos( $str, 'dtoverlay=pi3-disable-wifi' ) > 0 ) {
     $pass = 'WiFi disabled';
     $ssid = 'WiFi disabled';
     $wifi = false;
   } else {
     $wifi = true;
     $str = file_get_contents( '/etc/hostapd/hostapd.conf' );
//     $str2 = str_replace( "\n", '<br>', $str );
//     echo  "<p>$str2" ;
     $pass_start = 15 + strpos( $str, "wpa_passphrase=" );
     $pass_end = strpos( $str, "\n",  $pass_start );
     $pass = substr( $str, $pass_start, $pass_end - $pass_start );

     $ssid_start = 5 + strpos( $str, "ssid=" );
     $ssid_end = strpos( $str, "\n",  $ssid_start );
     $ssid = substr( $str, $ssid_start, $ssid_end - $ssid_start );
   }

   if ( $wifi ) {
     $pass_param = "class='blue' onclick='WIFI_PASSWD(\"$pass\")'";
     $ssid_param = "class='blue' onclick='SSID(\"$ssid\")'";
   } else {
     $pass_param = '';
     $ssid_param = '';
   }

   echo "<tr><th style='width: 70%; text-align: left;'>WIFI passphrase:</th>";
   echo "<td $pass_param colspan=2>$pass<span style='color:red; float:right'>***</span></td></tr>\n";

   echo "<tr><th style='width: 70%; text-align: left;'>WIFI SSID:</th>";
   echo "<td $ssid_param colspan=2>$ssid<span style='color:red; float:right'>***</span></td></tr>\n";

   echo "<tr><td style='width: 70%; text-align: left;'><b>Wireless IP Addresses:</b>&nbsp;<button onclick='wlan0()'>Advanced Settings &nbsp <span style='color:red; float:right'> ***</span></button></th><td>\n";
   $lines = array();
   $has_ip = 0;
   exec( "/sbin/ifconfig wlan0", $lines );
   foreach ($lines as $line ) {
     $line = trim($line);
     if( substr($line, 0, 10) != "inet addr:" ) continue;
     $line = substr( $line, 10, strpos( $line, 'B' ) - 10);
     if( substr( $line, 0, 3 ) == '127' ) continue;
     echo "$line<br>";
     $has_ip = 1;
     }
   if( $has_ip == 0 ) 
     echo "No Wireless IP";

   echo "<tr><td style='width: 70%; text-align: left;'><b>Hardwired IP Addesses:</b></th>
   <button onclick='eth0()'>Advanced Settings &nbsp <span style='color:red; float:right'> ***</span></button><td>\n";
   $lines = array();
   $has_ip = 0;
   exec( "/sbin/ifconfig eth0", $lines );
   foreach ($lines as $line ) {
     $line = trim($line);
     if( substr($line, 0, 10) == "inet addr:" ) {
         $line = trim(substr( $line, 10, strpos( $line, 'B' ) - 10));
         if( substr( $line, 0, 3 ) != '127' ) 
           echo "$line<br>";
           $has_ip = 1;
       }
     if(( substr($line, 0, 11) == "inet6 addr:" ) && ( strpos($line, 'Global') )) {
         $line = trim(substr( $line, 11, strpos( $line, '/' ) - 11 ));
         if( substr( $line, 0, 3 ) != '::1' )
           echo "$line<br>";
           $has_ip = 1;
       }
     }
   if( $has_ip == 0 )
     echo "No Hardwired IP";

   echo "<tr><th style='width: 20%; text-align: left;'>Upgrade Boards:\n";
   echo "<select id=POD  onchange='VERSIONS(\"$host\")'>\n";
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
   echo "<span id='VERSION' style='font-size: 15px;'> </SPAN>\n";

   echo "<select id='HEXFILE'>\n";
   $dlist = scandir( "firmware" );
   sort( $dlist );                 // the file with the larger version number will be later and therefore selected
   foreach( $dlist as $key => $name ) {
     if( in_array( $name, array(".", ".." ) ) ) continue;
     echo "<option value='$name'>File: $name</option>\n";
     }
   echo "</select>\n";

//   echo "<button onclick='VERSIONS(\"$host\")'>Get Version</button>\n";

   echo "<td><button type='button' id='MAIN' value='MAIN' onclick='MAIN(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>MAIN</button>\n";
   echo "<button type='button' id='BOOT' value='BOOT' onclick='BOOT(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>BOOT</button>\n";
   echo "<button type='button' id='VERIFY' value='VERIFY' onclick='VERIFY(\"$host\")' style='color: blue; cursor: pointer; cursor: hand;'>VERIFY</button></td></tr>\n";

   $rs = mysql_query("select ivalue FROM setup where type='contacts'", $li);
   $row = mysql_fetch_object( $rs );
   if( $row->ivalue & 2 ) echo "<tr><th style='text-align: left;'>Contact 1 is active open</td><td><label class='switch'><input type='checkbox' value='CHANGE' onclick='TIP(2)'><span class='slider round'></span></label></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Contact 1 is active shorted</td><td><label class='switch'><input type='checkbox' checked value='CHANGE' onclick='TIP(2)'><span class='slider round'></span></label></th></tr>\n";
   if( $row->ivalue & 1 ) echo "<tr><th style='text-align: left;'>Contact 2 is active open</td><td><label class='switch'><input type='checkbox' value='CHANGE' onclick='TIP(1)'><span class='slider round'></span></label></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Contact 2 is active shorted</td><td><label class='switch'><input type='checkbox' checked value='CHANGE' onclick='TIP(1)'><span class='slider round'></span></label></th></tr>\n";

   $rs = mysql_query("select ivalue FROM setup where type='speech'", $li);
   $row = mysql_fetch_object( $rs );
   if( $row->ivalue & 1 ) echo "<tr><th style='text-align: left;'>Speech is delivered by a female voice</td><td><label class='switch'><input type='checkbox' checked value='CHANGE' onclick='SPEECH(1)'><span class='slider round'></span></label></th></tr>\n";
   else                   echo "<tr><th style='text-align: left;'>Speech is delivered by a male voice</td><td><label class='switch'><input type='checkbox' value='CHANGE' onclick='SPEECH(1)'><span class='slider round'></span></label></th></tr>\n";

   $rs = mysql_query("select ivalue FROM setup where type='login'", $li);
   $row = mysql_fetch_object( $rs );
   if( $row->ivalue == 0) echo "<tr><th style='text-align: left;'>Sign in functionality is not enabled</td><td><button type='button' value='CHANGE' onclick='LOGON(1)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   if( $row->ivalue == 1) echo "<tr><th style='text-align: left;'>Persons connecting via Ethernet/Internet must sign in</td><td><button type='button' value='CHANGE' onclick='LOGON(2)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";
   if( $row->ivalue == 2) echo "<tr><th style='text-align: left;'>Everyone must sign in (have at least one admin user)</td><td><button type='button' value='CHANGE' onclick='LOGON(0)' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button></th></tr>\n";

   if( $SSH ) echo "<tr><th style='text-align: left;'>SSH/SFTP servers are enabled <button onclick='ssh()'>Advanced Settings &nbsp <span style='color:red; float:right'> ***</span></button></td><td><label class='switch'><input type='checkbox' checked value='CHANGE' onclick='SSH(0)'><span class='slider round'></span></label></th></tr>\n";
   else       echo "<tr><th style='text-align: left;'>SSH/SFTP servers are disabled</td><td><label class='switch'><input type='checkbox' value='CHANGE' onclick='SSH(1)'><span class='slider round'></span></label><span style='color:red; float:right'>***</span></th></tr>\n";

   if( $SOUND ) echo "<tr><th style='text-align: left;'>Music is played using a USB adapter (Speech Unavailable)</td><td><label class='switch'><input type='checkbox' value='CHANGE' onclick='SOUND(0)'><span class='slider round'></span></label><span style='color:red; float:right'>***</span></th></tr>\n";
   else         echo "<tr><th style='text-align: left;'>Music is played through the Audio Jack</td><td><label class='switch'><input type='checkbox' checked value='CHANGE' onclick='SOUND(1)'><span class='slider round'></span></label><span style='color:red; float:right'>***</span></th></tr>\n";

   echo "<tr><th style='text-align: left;' id='theme_name'></th><td>
<form id='THEME_FORM' method='post'>
  <input type='hidden' name='ACTION' value='CHANGE_THEME'>
  <input id='CHANGE_THEME_VAL' type='hidden' name='CHANGE_THEME_VAL' value='new'>
</form>
<select id='theme'>
  <option value='blue-green'>Blue Green</option>
  <option value='white'>White</option>
  <option value='dark'>Dark</option>
  <option value='leather'>Leather</option>
</select>
<button type='button' value='CHANGE_THEME' onclick='CHANGE_THEME(document.getElementById(\"theme\"))' style='color: blue; cursor: pointer; cursor: hand;'>CHANGE</button>
<script type='text/javascript'>
  getTheme(document.getElementById('theme'));
</script>
</td></tr>\n";

   echo "</table>\n";
   echo "<span style='color:red; font-size: 20px; float:right'>*** Reboot required</span><br><br>";
   echo "<div id='LOG'>\n";
   echo "</div>\n";

include '/var/www/html/includes/footer.inc';
exit();
?>
