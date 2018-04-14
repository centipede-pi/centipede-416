<?php
global $title, $back;
$title = "Centipede Users";
$back = "index.php";

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
   $rs = mysql_query("show tables like 'users'", $li);
echo "<body>\n";
   if( mysql_num_rows($rs) == 0 ) {
       mysql_query("create table users ( name char(32), password char (32), role ENUM('watch', 'control', 'admin' ))");
       echo "creating user table<br>";
       }
//   else
//       echo "user table already exists<br>";
?>


<script type="text/javascript">


function Delete( user ) {
   r = window.confirm("Are you sure you want to delete user " + user + "?");
   if( r == true ) {
       document.getElementById( 'DEL_USER' ).value = user;
       document.getElementById( 'DEL' ).submit();
       }
   }



function Add() {
   document.getElementById( 'ADD' ).submit();
   }

</script>

<?php

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }


   $action = post('ACTION');
   if( $action == 'DELETE' ) {
       $user = post('USER');
       mysql_query("delete FROM users where name='$user'", $li);
       }

   if( $action == 'ADD' ) {
       $user = post('USER');
       $password = post('PASSWORD');
       $sql =  "select COUNT(*) as c from users where name='$user'";
       $rs = mysql_query( $sql, $li);
       $row = mysql_fetch_object( $rs );
       if( $row->c > 0 ) {
           echo "<p>Add failed, duplicate record<br>";
           }
       else {
           $role = post('ROLE');
           $sql =  "insert into users set name='$user', password='$password', role='$role'";
//       echo "<p>$sql";
           mysql_query( $sql, $li);
           }
       }



   $rs = mysql_query("select * FROM users order by name", $li);
   echo "<p><table border='2' style='width:100%'>\n";
   echo "<tr><th align='left'>User Name</th><th align='left'>Password</th><th colspan='2' align='left'>Role</th></tr>\n";
   while( $row = mysql_fetch_object( $rs ) ) {
       echo "<tr><td  align='left'>$row->name</td><td  align='left'>$row->password</td>";
       echo "<td align='left'>$row->role</td>";
       echo "<td width='10%' class='red' onclick='Delete(\"$row->name\")'>DEL</td></tr>\n";
       }
   echo "<form id='ADD' method='post'>\n";
   echo "<input type='hidden' name='ACTION' value='ADD'>\n";
   echo "<tr><td   align='left'><input name='USER' required='required'></td>\n";
   echo "<td   align='left'><input name='PASSWORD' requiured='required'></td>\n";

   echo "<td   align='left'><select name='ROLE' size='1'>";
   SelectOptions( "none", array('watch', 'control', 'admin' ) );
   echo "</td>\n";
   echo "<td width='10%' class='add' onclick='Add()'>ADD</td></tr>\n";
   echo "</form>\n";

   echo "</table>";

   echo "<form id='DEL' method='post'>";
   echo "<input type='hidden' name='ACTION' value='DELETE'>";
   echo "<input id='DEL_USER' type='hidden' name='USER' value='-1'>";
   echo "</form>";


include '/var/www/html/includes/footer.inc';
exit();
?>
