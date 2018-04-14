<?php
global $title, $show_header;
$title = "Wireless Settings";
$show_header = false;
include 'login.inc';
include 'header.inc';

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

$action = post('submit');
if( $action == 'SUBMIT' ) {
    $enable_wifi = post('enable_wifi');

    if ( $enable_wifi == 'on' ) {
      $enable = 'yes';
      $enable_cmd = 'echo -ne ""';
    } else {
      $enable = 'no';
      $enable_cmd = 'echo -ne "\ndtoverlay=pi3-disable-wifi\n"';
    }

    # Every submit changes the /boot/config.txt file, this may not be needed.
    $str = file_get_contents( '/boot/config.txt' );
    $write_file = false;
    if (strpos ( $str, "\ndtoverlay=pi3-disable-wifi" ) ) {
      if ( $enable == "yes" ) {
        $str = str_replace("\ndtoverlay=pi3-disable-wifi", "", $str);
        $wifi_blacklist = "";
        echo "Enabling WiFi";
        $write_file = true;
      }
    } else {
      if ( $enable == "no" ) {
        $str = $str . "dtoverlay=pi3-disable-wifi\n";
        $wifi_blacklist = "blacklist brcmfmac\nblacklist brcmutil\n";
        echo "Disabling WiFi";
        $write_file = true;
      }
    }
    if ( $write_file ) {
      file_put_contents('/tmp/config.txt', $str);
      $cmd = 'sudo cp /tmp/config.txt /boot/config.txt';
      exec( $cmd, $rst );
      unlink('/tmp/config.txt');
      file_put_contents('/tmp/wifi-blacklist.conf', $wifi_blacklist);
      $cmd = 'sudo cp /tmp/wifi-blacklist.conf /etc/modprobe.d/wifi-blacklist.conf';
      exec( $cmd, $rst );
      unlink('/tmp/wifi-blacklist.conf');
    }
  }

if( $action == 'CLOSE' ) {
   echo "<script type='text/javascript'>\n";
   echo "window.close();\n";
   echo "</script>\n";
   }

$str = file_get_contents( '/boot/config.txt' );

$enabled = 'yes';
$enabled_loc = strpos ( $str, "\ndtoverlay=pi3-disable-wifi" );
if ( $enabled_loc != '' ) {
 $enabled = 'no';
}

echo "<body>";
echo ("<center><h style='color: blue'>WiFi Settings</h></center>");
echo "<p>You must reboot for the changes to take effect and be reflected on the options page. See the Users Manual for more details.";

   echo "<form method='post'>";
   echo "<p><center><table border='2' >\n";
   if ( $enabled == 'yes' ) {
     $checked = 'checked';
   } else {
     $checked = '';
   }
   echo "<tr><td class='ctr'>Enable WiFi:</td><td class='ctr'><label class='switch'><input type='checkbox' $checked name='enable_wifi'><span class='slider round'></span></label></td></tr>\n";
   echo "</table></center>";

   echo "<p><center><input type='submit' name='submit' value='SUBMIT'>";
   echo "<span style='padding: 20px'> </span><input type='submit' name='submit' value='CLOSE'>";

echo "</body>";
echo "</html>";
exit();
?>
