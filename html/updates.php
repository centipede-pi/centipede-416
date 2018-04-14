<?php
global $title, $back;
$title = "Software Updates";
$back = "index.php";

include '/var/www/html/includes/login.inc';
include '/var/www/html/includes/header.inc';

if( $_SESSION['role'] != 'admin') {
    echo "<!doctype html><html lang='en'><head>";
    echo "<body>Only an administrator can access these functions</body></html>";
    exit();
    }

header('Cache-Control: no-cache, must-revalidate');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
$rcode = 0;       // true if the upload succeeded
?>

<script type="text/javascript">
var conn;

function FeedBack( nextline, Button ) {
   if(( Button == "CONTINUE" ) || ( Button == "IGNORE MISMATCH" )) {
       conn.send('RESUME:' + shortname + ':' + nextline );   // Send message to continue running the script
       }
   document.getElementById( 'A' + nextline ).style.display="none";
   if( document.getElementById( 'B' + nextline ) ) 
       document.getElementById( 'B' + nextline ).style.display="none";
   }


function MAIN(host, file) {  // retrieve messages from server
   var log = document.getElementById("LOG");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);     // the web socket is used to receive updates to iFrame's meter element values

   conn.onopen = function () {       // When the connection is open, send some data to the server
       conn.send('UPGRADE:' + file);   // Send
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       }
   conn.onmessage = function (e) {       // Log messages from the server
//       console.log('Server: |' + e.data + '|');
       if(e.data.substr(0,8) == '$$$PAUSE') {              //      0       1        2       3
           parms = e.data.split(":")                       //  $$$PAUSE:nextline:CONTINUE:CANCEL
           btn = "<button";
           func =  " onClick=\"FeedBack( "   + parms[1] +  ", '" + parms[2] + "' )\" ";
           id = " ID='A" +  parms[1] + "'";
           val = ">" + parms[2] + "</button>";
           msg =  btn + id + func + val;
//           console.log( msg );
           if( parms[3] ) {
               btn = " <button";
               func =  " onClick=\"FeedBack( "   + parms[1] +  ", '" + parms[3] + "' )\" ";
               id = " ID='B" +  parms[1] + "'";
               val = ">" + parms[3] + "</button>";
               msg2 =  btn + id + func + val;
//               console.log( msg2 );
               msg = msg + msg2;
               }
           document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + msg + '<br>';
           return;
           }
       document.getElementById('LOG').innerHTML =  document.getElementById('LOG').innerHTML + e.data + '<br>';
       }
   }
</script>



<?php
function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
    }

function rmdir_contents($dir) {          // ensure $dir ends with a slash
    $files = glob( $dir . '*', GLOB_MARK );
    foreach( $files as $file ){
        if( substr( $file, -1 ) == '/' ) {
//            echo "rmdir_contents( $file )<br>";
            rmdir_contents( $file );
            }
        else  {
//            echo "unlink( $file )<br>";
            unlink( $file );
            }
        }
    if( $dir != "/var/upgrades/")  {
//       echo "rmdir( $dir )<br>";
       rmdir( $dir );
       }
}

if(isset($_POST["submit"]) &&  $_POST["submit"] == "Upload" ) {
   rmdir_contents("/var/upgrades/");        // remove existing files recursively
//   exit();

   $rcode = 2;           // assume success
   $result = "Was successfully transfered to the Centipede 416";
   $tmpFilePath = $_FILES['upload']['tmp_name'];    //Get the temp file path
   if($tmpFilePath != ""){                          //Make sure we have a filepath
       $shortname = $_FILES['upload']['name'];      //save the filename
echo "<script type='text/javascript'>\n";
echo "var shortname = '$shortname';\n";
echo "</script>\n";
       $filePath = "/var/upgrades/" . $shortname;
       if( file_exists( $filePath ) ) {
           $result = "error: Duplicate file name";
           $rcode = 1;
           }
       elseif(move_uploaded_file($tmpFilePath, $filePath)) {
           }
       else {
           $result = "Internal error: not able to move file";
           $rcode = 1;
           }
       }
   else {
       $result = "Internal error: no temp file path";
       $rcode = 1;
       }
   $files[] = "<b><i>" . $shortname . "</i></b>,  " . $result;
   }
?>

<p>Use this web page to upload and then install software updates.  Software update files are created from time to time to add features 
or fix problems that are discovered after your Centipede was shipped.  To install an update, first you must download the update
file to your computer and then use this web page to install it inside the Centipede. Be sure to follow the instructions below, if any.

<p>Depending on the size of the update, uploading can take a few minutes, and installing can take even longer, please be patient.


<?php


$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");

   $rs = mysql_query("select ivalue FROM setup where type='version'", $li);
   $row = mysql_fetch_object( $rs );
   $version = number_format( $row->ivalue / 100, 2);
   echo "<p>Your current software version is <b>$version</b>\n";
   $space = number_format( disk_free_space("/var/upgrades") / 1048576.0 );
   echo "<p>Remaining storage space is currently: $space megabytes";

if( $rcode == 0 ) {  ?>
<hr><h1>Upload the software update file</h1>
<p><form action="" method="post" enctype="multipart/form-data">
    Select file(s) to upload:
    <input id="upload" type="file" name="upload" />
    <input type="submit" value="Upload" name="submit">
</form>
<?php }; ?>


<div id='LOG'>
<?php
if(isset($files)){
   foreach($files as $key => $file){
      echo "<span id='FILENAME'>$file<br>\n";
      }
   }
if( $rcode == 1 )
   echo "<hr>update terminated<br>\n";
echo "</div>\n";

$host = $_SERVER['HTTP_HOST'];
//echo "MAIN2( '$host', '$shortname' );\n";

if( $rcode == 2 ) {
   echo "<hr><script type='text/javascript'>\n";
   echo "MAIN( '$host', '$shortname' );\n";
   echo "</script>\n";
   }

include '/var/www/html/includes/footer.inc';
?>
