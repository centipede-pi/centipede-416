<?php
session_start();

if( !empty( $_SESSION['role'] ) )  return;          // already logged in

//---------------------- is logging in required ---------------------------
$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");

  # Get Theme
  global $theme;
  $rs = mysql_query("select ivalue FROM setup where type='theme'", $li);
  $theme = mysql_fetch_row( $rs )[0];
  if ( $theme == "" ) {
    $theme = "blue-green";
  }

   $rs = mysql_query("select * from setup where type = 'login' ");
   $row = mysql_fetch_object( $rs );
   if( empty( $row->ivalue ) ) $row->ivalue = 0;
   if( $row->ivalue == 0 )  {             // no login, so everyone is an administrator
       $_SESSION['role'] = 'admin';
       return;
       }
   $us =  mysql_query("select * from users where role = 'admin' ");
   if( mysql_num_rows( $us ) < 1 ) {
       $_SESSION['role'] = 'admin';         // prevent him from locking himself out
       return;
       }
   $adr = $_SERVER['REMOTE_ADDR'];
   $adr = substr( $adr, 0, 9 );
   if(( $row->ivalue == 1 ) && (( $adr  == '192.168.4.' ) || ( $adr == '127.0.0.1' ) || ( $adr == '::1' ))) {
       $_SESSION['role'] = 'admin';
       return;        // require login only over LAN, so WIFI folks are 'admin'
       }

//----------------------  process the submitted form ----------------------
function get_post_var($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }
 $action = get_post_var('ACTION');
 if( $action == 'LOGIN' ) {
       $user = get_post_var('USER');
       $password = get_post_var('PASSWORD');
//       echo "<p>got $user  $password<br>";
       $rs = mysql_query("select * from users where name = '$user' ");
       $row = mysql_fetch_object( $rs );
//       echo "<p>table returned:  $row->name, $row->password, $row->role<br>";
       if( $row->password == $password ) {
           $_SESSION['role'] = $row->role;      // give her the role assigned in the database
           return;
           }
       }


//------------------------ output the login form ---------------------------
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name='viewport' content='initial-scale=1.0, user-scalable=no' />
<link rel="stylesheet" type="text/css" href="/css/centipede.css">
<link rel='stylesheet' type='text/css' href='/css/centipede-<?php echo $theme; ?>.css'>;

<title>Login</title>
</head>
<body>

<?php

   echo "<center>";
   echo "<img src='/images/logo.png' width='60%'><h1>Centipede 416 Login</h1><br>";

   echo "<form method='post'>\n";
   echo "<p><table border='2'>\n";
   echo "<tr><th align='left'>User Name</th><td align='left'><input type='text' name='USER' required='required' autocorrect='off' autocapitalize='none'></td>\n";
   echo "<tr><th align='left'>Password</th><td align='left'><input type='password' name='PASSWORD' required='required'></td>\n";
   echo "</table><br>";

   echo "<input type='submit' name='ACTION' value='LOGIN'>";
   echo "</form>";

echo "</body>";
echo "</html>";
exit();
?>
