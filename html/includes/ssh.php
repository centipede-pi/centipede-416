<?php
global $title, $show_header;
$title = "SSH Settings";
$show_header = false;
include 'login.inc';
include 'header.inc';
?>

<script type="text/javascript">
function loadFileAsText() {
    var fileToLoad = document.getElementById("fileToLoad").files[0];
 
    var fileReader = new FileReader();
    fileReader.onload = function(fileLoadedEvent) 
    {
        var textFromFileLoaded = fileLoadedEvent.target.result;
        document.getElementById("keys").value = textFromFileLoaded;
    };
    fileReader.readAsText(fileToLoad, "UTF-8");
}
 </script>

</head>



<?php

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

$action = post('submit');
if( $action == 'SUBMIT' ) {
    $port = post('port');
    $allow_pwd = post('allow_pwd');
    $keys = post('keys');
    if ( $allow_pwd == 'on' ) {
      $allow_pwd = 'yes';
    } else {
      $allow_pwd = 'no';
    }
    
    if ( $port == "" ) {
      $port = 22;
    }
    $verboten_ports = array(53, 80, 8000);
    if ( ! is_numeric($port) ) {
      echo "$port is not an integer.<br>";
    } elseif (( $port < 1 ) || ( $port > 65535 )) {
      echo "$port is not between 1 and 65535.<br>";
    } elseif ( in_array($port, $verboten_ports, false) ) {
      echo "$port is already in use.<br>";
    } else {
      # Every submit changes the sshd_config file, this may not be needed.
      $port_cmd = "'s/^Port\ .*/Port\ $port/'";
      $pwd_cmd = "'s/^#\?PasswordAuthentication\ .*/PasswordAuthentication\ $allow_pwd/'";
      $cmd = "sed -e $port_cmd -e $pwd_cmd /etc/ssh/sshd_config > /tmp/sshd_config && sudo cp /tmp/sshd_config /etc/ssh/sshd_config && rm /tmp/sshd_config";
      exec( $cmd, $rst );
      file_put_contents('/tmp/authorized_keys.tmp', $keys);
      exec('sudo cp /tmp/authorized_keys.tmp /home/pi/.ssh/authorized_keys');
      unlink('/tmp/authorized_keys.tmp');
      
#      echo "<script type='text/javascript'>\n";
#      echo "window.close();\n";
#      echo "</script>\n";
    }
   }

if( $action == 'CLOSE' ) {
   echo "<script type='text/javascript'>\n";
   echo "window.close();\n";
   echo "</script>\n";
   }

$port = "";
$str = file_get_contents( '/etc/ssh/sshd_config' );
$port_start = strpos ( $str, "\nPort " );
$port_end = strpos ( $str, "\n", $port_start + 1 );
$port = trim ( substr ( $str, $port_start + 6, $port_end - $port_start - 6 ) );

$keys = "12345";

exec('sudo cp /home/pi/.ssh/authorized_keys /tmp/');
$keys = file_get_contents('/tmp/authorized_keys');
#exec('touch /tmp/empty_file && sudo cp /tmp/empty_file /tmp/authorized_keys');

$pwd = 'yes';
$pwd_start = strpos ( $str, "\nPasswordAuthentication " );
# The default config uses a comment so there may be no setting.
if ( $pwd_start != '' ) { 
  $pwd_start = strpos ( $str, " ", $pwd_start + 1 );
  $pwd_end = strpos ( $str, "\n", $pwd_start + 1 );
  $pwd = trim ( substr ( $str, $pwd_start, $pwd_end - $pwd_start ) );
}

echo "<body>";
echo ("<center><h style='color: blue'>SSH Settings</h></center>");
echo "<p>You must reboot for the changes to take effect and
be reflected on the options page. See the Users Manual for more details.";

   echo "<form method='post'>";
   echo "<p><center><table border='2' >\n";
   echo "<tr><td class='ctr'>Port:</td><td class='ctr'><input type='number' name='port' min='1' max='65535' size=5 value='$port'</td></tr>\n";
   echo "<tr><td class='ctr'>Authorized Key(s):</td><td class='ctr'><textarea name='keys' id='keys'>$keys</textarea><br><input type='file' onchange='loadFileAsText()' id='fileToLoad'></td></tr>\n";

   if ( $pwd == 'yes' ) {
     $checked = 'checked';
   } else {
     $checked = '';
   }
   echo "<tr><td class='ctr'>Allow Passwords:</td><td class='ctr'><label class='switch'><input type='checkbox' $checked name='allow_pwd'><span class='slider round'></span></label></td></tr>\n";
   echo "</table></center>";

   echo "<p><center><input type='submit' name='submit' value='SUBMIT'>";
   echo "<span style='padding: 20px'> </span><input type='submit' name='submit' value='CLOSE'>";

echo "</body>";
echo "</html>";
exit();
?>
