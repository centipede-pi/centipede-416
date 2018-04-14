<?php
global $title, $show_header;
$title = "Wired Ethernet Settings";
$show_header = false;
include 'login.inc';
include 'header.inc';

function set_tor_vars() {
  $tor_installed = file_exists('/usr/bin/tor');
  $GLOBALS['tor_installed'] = $tor_installed;
  if ( $tor_installed ) {
    $cmd = "pgrep tor";
    $GLOBALS['tor_pid'] = shell_exec( $cmd );
    $tor_hs_dir = '/etc/tor/hidden_service/';
    $GLOBALS['tor_hs_dir'] = $tor_hs_dir;
    $GLOBALS['tor_hostname'] = trim(file_get_contents( $tor_hs_dir.'/hostname' ));
  } 
  
}

set_tor_vars();

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

$action = post('submit');
if( $action == 'SUBMIT' ) {
   $str = file_get_contents( '/etc/dhcpcd.conf' );
   $eth0 =  strpos( $str, "\ninterface eth0\n" );
   if( $eth0 ) {
       $str = substr( $str, 0, $eth0 );
       }
   $gw = post('gateway');
   $ip = post('address');
   if( ! $gw || ! $ip )  $str2 = $str;
   else  $str2 = $str . "\ninterface eth0\nstatic routers=$gw\nstatic domain_name_servers=8.8.8.8\nstatic domain_search=4.4.4.4\nstatic ip_address=$ip\n";
   file_put_contents( '/etc/dhcpcd.conf', $str2 );

   $tor_new = post('tor_new');
   if ( $tor_new == 'on' ) {
     if ( $tor_pid != '' ) {
       echo "Stopping Tor.";
       $cmd = 'kill '.$tor_pid;
       exec($cmd, $rst);
       set_tor_vars();
       $tor_pid = '';
     }
     echo "..Deleting old hostname...";
     unlink('/etc/tor/hidden_service/hostname');
     unlink('/etc/tor/hidden_service/private_key');
   }
   $tor_enable = post('tor_enable');
   $tor_cmd = 'tor -DataDirectory '.$tor_hs_dir.'working -RunAsDaemon 1';
   $cron_data = '@reboot sleep 10; '.$tor_cmd;
   if ( $tor_enable == 'on' ) {
     if ( $tor_pid == '' ) {
       echo "Starting Tor";
       if ( ! file_exists($tor_hs_dir.'/working') ) {
         mkdir($tor_hs_dir.'/working');
       }
       exec($tor_cmd, $rst);
       $cmd = "crontab -l 2>/dev/null | grep -v '$cron_data'; echo '$cron_data' | crontab -";
       exec($cmd, $rst);
       sleep(5);
     }
   } else {
     if ( $tor_pid != '' ) {
       echo "Stopping Tor.";
       $cmd = 'kill '.$tor_pid;
       exec($cmd, $rst);
       $tor_pid = '';
       $cmd = "crontab -l 2>/dev/null | grep -v '$cron_data' | crontab -";
       exec($cmd, $rst);
     }
   }
   set_tor_vars();

#   echo "<script type='text/javascript'>\n";
#   echo "window.close();\n";
#   echo "</script>\n";
   }

if( $action == 'CLOSE' ) {
   echo "<script type='text/javascript'>\n";
   echo "window.close();\n";
   echo "</script>\n";
   }


$gw = "";
$ip = "";
$str = file_get_contents( '/etc/dhcpcd.conf' );
$eth0 =  strpos( $str, "\ninterface eth0\n" );
if( $eth0 ) {
   $start = 16 + strpos( $str, "\nstatic routers=", $eth0 );
   if( $start ) {
       $end = strpos( $str, "\n",  $start + 1 );
       $gw = substr( $str, $start, $end - $start );
       }
   $start = 19 + strpos( $str, "\nstatic ip_address=", $eth0 );
   if( $start ) {
       $end = strpos( $str, "\n",  $start + 1 );
       $ip = substr( $str, $start, $end - $start );
       }
   }

echo "<body>";
if ($tor_installed) {
  echo "
<script type='text/javascript'>
function toggleTorHost() {
  var host_td = document.getElementById('tor_host');
  var hostname = '$tor_hostname';
  var hideText = 'Double click to show';
  var showText = '<a target=\"_blank\" href=\"http://'+hostname+'\">'+hostname+'</a>';
  if ( host_td.innerText == hideText ) {
    host_td.innerHTML = showText;
  } else {
    host_td.innerHTML = hideText;
  }
}
</script>
";
}

echo ("<center><h style='color: blue'>Advanced LAN Settings</h></center>");
echo "<p>For static addressing, fill in the gateway address, for example:  <b>10.0.0.1</b> and the IP Address, for example:  <b>10.0.0.20/24</b> ";
echo "<p>For dynamic addressing, the fields should be empty. You must reboot for the changes to take effect and
be reflected on the options page. See the Users Manual for more details.";

   echo "<form method='post'>";
   echo "<p><center><table border='2' >\n";
   echo "<tr><td class='ctr'>Gateway:</td><td class='ctr'><input type='text' name='gateway' size=40 value='$gw'</td></tr>\n";
   echo "<tr><td class='ctr'>IP Address:</td><td class='ctr'><input type='text' name='address' size=40 value='$ip'</td></tr>\n";

   if ( $tor_installed ) {
     echo "<tr><td colspan='2'><hr></td></tr>";
     if ( $tor_pid == '' ) {
       $tor_text = 'Tor is not running';
       $checked = '';
     } else {
       $tor_text = 'Tor is running';
       $checked = 'checked';
     }
     echo "<tr><td class='ctr'>$tor_text:</td><td class='ctr'><label class='switch'><input type='checkbox' $checked name='tor_enable'><span class='slider round'></span></label></td></tr>\n";
     echo "<tr onDblClick='toggleTorHost()'><td class='ctr'>Tor Hostname (keep secret):</td><td id='tor_host' class='ctr'>Double click to show</td></tr>\n";
     echo "<tr><td class='ctr'>Get new Tor Hostname:</td><td class='ctr'><label class='switch'><input type='checkbox' name='tor_new'><span class='slider round'></span></label></td></tr>\n";
   }

   echo "</table></center>";

   echo "<p><center><input type='submit' name='submit' value='SUBMIT'>";
   echo "<span style='padding: 20px'> </span><input type='submit' name='submit' value='CLOSE'>";





echo "</body>";
echo "</html>";
exit();
?>
