<?php
if( isset($_GET['OFF'] ) ) {
    session_start();
    $_SESSION = array();
    session_destroy();
    }

global $title, $back;
$title = "Home Menu";
$back = "none";

include '/var/www/html/includes/login.inc';
include '/var/www/html/includes/header.inc';
?>

<script type="text/javascript">
var conn;

function PowerDown(host) {  // retrieve messages from server
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       conn.send('PwrOff');   // Send
       }
   }

function updateLegacyURL(select) {
  document.getElementById('select_dest').value = select.value;
}

function goLegacy(button) {
  window.location = button.value;
}
</script>

<?php
echo "<table>\n";
echo "<tr><td align='left' valign='top' ><ul><b>Output Controls</b>";
echo "<li><a href='controller.php'>Universal Output Controller</a></ul></td>\n";
echo "<td align='left' valign='top' ><ul><b>Legacy Controller</b>";
echo "<li><select id='legacy_select' onChange='updateLegacyURL(this);'>
  <option value='/combined/index.php?display=0'>Firefox (Desktop)</option>
  <option value='/combined/index.php?display=2'>Chrome (Desktop)</option>
  <option value='/combined/index.php?display=3'>Chrome (Phone/Tablet)</option>
  <option value='/apple/index.php?display=0'>iPad</option>
  <option value='/apple/index.php?display=1'>iPhone</option>
</select>
<button onClick='goLegacy(this)' id='select_dest' value='/combined/index.php?display=0'>Go</button></li>";
echo "</ul></td></tr></table>\n";
echo "<hr>";
echo "<h2>Setup and Additional features</h2>\n";
echo "<ul>";
if( $_SESSION['role'] == 'control') {
    echo "<li><a href='jukebox.php'>Jukebox: music player</a>\n";
    }
if( $_SESSION['role'] == 'admin') {
    echo "<li><a href='jukebox.php'>Jukebox: music player</a>\n";
    echo "<li><a href='options.php'>Set options and parameters</a>\n";
    echo "<li><a href='music_library.php'>Music Library: Upload or Delete files</a>\n";
    echo "<li><a href='sequence_library.php'>Sequence Library: Upload or Delete files</a>\n";
    echo "<li><a href='updates.php'>Install Software Updates</a>\n";
    echo "<li><a href='users.php'>Usernames and Passwords</a>\n";
    echo "<li><a href='javascript:PowerDown(\"$host\");'>Power Off</a>\n";
    }
echo "<li><a href='index.php?OFF'>Log Off</a>\n";
echo "</ul>";
include '/var/www/html/includes/footer.inc';
?>


