<?php
global $title, $back;
$title = "Sequence Library";
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
?>



<?php
function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
    }


if(isset($_POST["submit"]) &&  $_POST["submit"] == "Upload" ) {
   if(count($_FILES['upload']['name']) > 0){
       for($i=0; $i<count($_FILES['upload']['name']); $i++) {   //Loop through each file
           $result = "Success";
           $tmpFilePath = $_FILES['upload']['tmp_name'][$i];    //Get the temp file path
           if($tmpFilePath != ""){                              //Make sure we have a filepath
               $shortname = $_FILES['upload']['name'][$i];      //save the filename
               $filePath = "/var/www/html/sequences/" . $shortname;
               if( file_exists( $filePath ) ) {
                   $result = "error: Duplicate file name";
                   $files[] = "File: <b><i>" . $shortname . "</i></b>,  Result: " . $result;
                   continue;
                   }
               if(move_uploaded_file($tmpFilePath, $filePath)) {
                   chmod( $filePath, 0777 );
                   }
               else $result = "Internal error: not able to move file";
               }
           else $result = "Internal error: no temp file path";
           $files[] = "File: <b><i>" . $shortname . "</i></b>,  Result: " . $result;
           }
       }
   }

if(isset($_POST["submit"]) &&  $_POST["submit"] == "Delete" ) {
   foreach ( $_POST["files"] as $file ) {
        $filePath = "/var/www/html/sequences/" . $file;
        if( !file_exists( $filePath ) ) {
            $result = "error: File no longer exists";
            $files[] = "File: <b><i>" . $file . "</i></b>,  Result: " . $result;
            continue;
            }
       if( unlink( $filePath ) ) {
           $result = "Deleted";
           $files[] = "File: <b><i>" . $file . "</i></b>,  Result: " . $result;
           continue;
           } 
       else {
           $result = "Error, file not deleted";
           $files[] = "File: <b><i>" . $file . "</i></b>,  Result: " . $result;
           continue;
           }
       }
   }
?>

<p>Use this web page to upload into or delete from the Centipede 416 sequence library.  Each uploaded file must be 20 megabytes or less and
must be a .txt type file. You can select up to 20 files at a time for uploading, as long as their combined size is 32 megabytes or less. When deleting
files, there are no limitations as to the number you can delete at a time.  

<p><b>Notes:</b>
<ul>
<li>You cannot Upload and Delete at the same time.
<li>You can also use FileZilla or another FTP program to upload files, without these limitations, the directory to target is '/var/www/html/sequences'.
<li>Do not use all the remaining storage space, if you need more, consider using a larger SD card.
</ul>

<?php
  $space = number_format( disk_free_space("/var/www/html/sequences") / 1048576.0 );
  echo "<p>Remaining storage space is currently: $space megabytes";


   if(isset($files)){
       echo "<hr><h1>Results</h1>";          //show success message
       echo "<ul>";
       foreach($files as $key => $file){
           echo "<li>$file</li>";
           }
       echo "</ul>";
       }
?>

<hr><h1>Upload Sequence Files</h1>
<p><form action="" method="post" enctype="multipart/form-data">
    Select file(s) to upload:
    <input id="upload" type="file" name="upload[]" multiple="multiple" />
    <input type="submit" value="Upload" name="submit">
</form>


<hr><h1>Delete Sequence Files</h1>
<p><form action="" method="post" enctype="multipart/form-data">
    Select file(s) to delete:
    <div>

<?php
   $dlist = scandir( "/var/www/html/sequences" );
   foreach( $dlist as $key => $name ) {
       if( in_array( $name, array(".", ".." ) ) ) continue;
       $ext = get_file_extension( $name );        // get the extension, IE  .mp3, etc
       if( $ext != 'txt' )  continue;
       $name = str_replace( "'", "\'", $name );   // escape single quotes
       echo "<input type='checkbox' name='files[]' value='$name' />$name<br />";
       }
?>
    </div>
    <input type="submit" value="Delete" name="submit">
</form>

<?php
include '/var/www/html/includes/footer.inc';
?>
