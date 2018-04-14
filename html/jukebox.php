<?php

global $title, $back;
$title = "Jukebox";
$back = "index.php";

include '/var/www/html/includes/login.inc';
include '/var/www/html/includes/header.inc';

if( $_SESSION['role'] == 'watch') {
    echo "<!doctype html><html lang='en'><head>";
    echo "<body>Only an authorized person can access these functions</body></html>";
    exit();
    }

include 'includes/range_moz.css';

$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");
   $host = $_SERVER['HTTP_HOST'];
echo "<body onload='GetJukeMeters(\"$host\"); '>";

function post($str) {
  if( !empty( $_POST[$str] )) return $_POST[$str];
  return "";
  }

function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
    }
$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");
   $host = $_SERVER['HTTP_HOST'];

   $rs = mysql_query("select ivalue FROM setup where type='vol_mixer'", $li);
   $vol_mixer = mysql_fetch_row( $rs )[0];
//   echo "vol_mixer = $vol_mixer<br>";
   if( $vol_mixer == 0 ) $vol_link = 1;
   else {
       $rs = mysql_query("select ivalue FROM setup where type='vol_link'", $li);
       $vol_link = mysql_fetch_row( $rs )[0];
       }
//   echo "vol_link = $vol_link<br>";
   $rs = mysql_query("select ivalue FROM setup where type='vol_mute'", $li);
   $vol_mute = mysql_fetch_row( $rs )[0];
//   echo "vol_mute = $vol_mute<br>";
   $rs = mysql_query("select ivalue FROM setup where type='vol_left'", $li);
   $vol_left = mysql_fetch_row( $rs )[0];
//   echo "vol_left = $vol_left<br>";
   if( $vol_link == 1 )  $vol_right = $vol_left;
   else {
       $rs = mysql_query("select ivalue FROM setup where type='vol_right'", $li);
       $vol_right = mysql_fetch_row( $rs )[0];
       }
//   echo "vol_right = $vol_right<br>";

?>



<script type="text/javascript">
var conn;
var songs = [];      /* javascript array of files in /var/music  */
var playlists = [];  /* javascript array of playlists in /var/music;  the '.pl' has been removed */
var next = 0;
var starting = 0;     /* a command has been sent to start playing a song, while (seq_switches & 4) was off  */
var PostMessages = [];
var battery_status = 0;
var battery_voltage = 0.0;
var battery_amps = 0.0;
var battery_status_txt = ["Startup", "Trickle Charging", "Charging", "Idle/cool down", "Discharging", "Battery Missing", "n/a", "n/a", "n/a", "Power Down" ];
var grayTimer=0;

<?PHP
echo "var vol_mute = $vol_mute;";
echo "var vol_link = $vol_link;";
echo "var vol_mixer = $vol_mixer;";
?>

function GetJukeMeters(host) {  // retrieve messages from server
   var meter = document.getElementById("meter");
   var url = "ws://" + host + ":8000";
   conn = new WebSocket(url);

//-----------------------------------------------------------------
// using the directory, create a PHP array containing all songs
// and a javascript array with all playlists
//-----------------------------------------------------------------
<?php
   $dlist = scandir( "/var/music" );
   foreach( $dlist as $key => $name ) {
       if( in_array( $name, array(".", ".." ) ) ) continue;
       $ext = get_file_extension( $name );           // get the extension, IE  .mp3, etc
       $name = str_replace( "'", "&#39;", $name );   // escape single quotes
       $name = rtrim($name);                         // remove end-of-line chars
       if( $ext == 'pl' )  {
           $fn =  substr( $name, 0, -3 );          // remove '.pl'
           echo(   "playlists.push('$fn');\n");    // load up the javascript variables
           }
       else               {
           $all_songs[] = $name;       // php array, contains all songs in the directory
           }
       }

//-----------------------------------------------------------------
// if a playlist is given, split the songs into two arrarys
// one with songs in playlist, another with everything else
//-----------------------------------------------------------------
   $other_songs = [];              // start with an empty array in case there is no valid playlist
   $playlist_songs = $all_songs;   // if not, then all songs are part of the playlist
   if( !empty( $_GET["playlist"] )) {
       $fname =  $_GET["playlist"] . '.pl';     // get the filename of the playlist
       if( file_exists("/var/music/"  . $fname )  )  {
           $playlist_songs = file(  "/var/music/"  . $fname );
           foreach ($playlist_songs as $k => $v) {
               $playlist_songs[$k] = rtrim($v);         // remove end-of-line chars
               $playlist_songs[$k] = str_replace( "'", "&#39;", $playlist_songs[$k] );   // escape single quotes
               }
           $others =  array_diff( $all_songs, $playlist_songs );   // remove songs in the playlist
           foreach( $others as $key => $name ) {    // re-key the array
               $other_songs[] = $name;     // note:  $other has the original keys from $all_songs, not 0,1,2,3,4,etc
               }
           }
       }

//-----------------------------------------------------------------
// build the javascript array of all songs, playlist first
//-----------------------------------------------------------------
   echo "// playlist songs\n";
   foreach( $playlist_songs as $key => $name ) {
       echo(   "songs.push('$name');\n");        // build javascript array from php array
       }
   echo "// other songs\n";
   foreach( $other_songs as $key => $name ) {
       echo(   "songs.push('$name');\n");        // build javascript array from php array
       }
include 'includes/index.js';
?>

   conn.onopen = function () {       // When the connection is open, send some data to the server
      conn.send('METER:0');       // Send the list of board/channels we want to hear about
       }
   conn.onerror = function (error) {      // Log errors
       console.log('WebSocket Error ' + error);
       conn.close();
       }
   conn.onmessage = function (e) {       // Log messages from the server
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
           seq_switches = list[1];

           if( list[0] == 'MTR2' ) {       // MTR2 = with battery values
               start = 6;
               battery_status = list[2];
               battery_voltage = list[3];
               battery_amps = list[4];
               battery_temp = list[5];
               ChoseImage( battery_status, battery_voltage);
               }


           if( !( seq_switches & 4 ) & (document.getElementById( "RUN" ).innerHTML == 'ON' )  & (starting == 0) )  {
               // no song is playing AND  the 'run' button is active  AND a song is starting up
               id = "S" + next;        // id of the next song to play (unless it is marked to be skipped)
               if( document.getElementById( id ).className == 'songskip' ) {
                   post( "MUSIC:STOP" );
                   }
               else {
                   starting = 1;
                   post( "MUSIC:" + songs[ next ] );
                   for( i=0; i < songs.length; i++ ) {
                       id = "S" + i;
                       if( i == next ) document.getElementById( id ).className = 'songplay';
                       else if( document.getElementById( id ).className == 'songplay' )
                           document.getElementById( id ).className = 'song';
                       }
                   }
               next = next + 1;
               if( next >= songs.length ) next = 0;
               }
           if( seq_switches & 4 ) starting = 0;    /* if busy comes back on, then the song has started already */
           }

       if( PostMessages.length > 0 ) {
           msg = PostMessages.shift();
           conn.send(msg);
           }
       else
           conn.send('METER:0');
       }
   }

function post(msg) {
//   console.log( msg );
   PostMessages.push(msg);
   }


window.onbeforeunload=function() {
  conn.close();
  }

function reveal() {
  if( document.getElementById('seq').style.display == 'block' )
      document.getElementById('seq').style.display = 'none';
  else
      document.getElementById('seq').style.display = 'block';
  }

function Run() {                  // called when ON/OFF button is pressed
   b = document.getElementById( "RUN" );
   if( b.innerHTML == 'ON' ) {
       b.style.backgroundColor = "#FF0000";
       b.innerHTML = 'OFF';
       if( seq_switches & 4 )  post( "MUSIC:STOP");
       }
   else {
       b.style.backgroundColor = "#33FF00";
       b.innerHTML = 'ON';
       }
   }

function Skip() {
   b = document.getElementById( "RUN" );
   if( b.innerHTML == 'OFF' ) {
       b.style.backgroundColor = "#33FF00";
       b.innerHTML = 'ON';
       }
   if( seq_switches & 4 )  post( "MUSIC:STOP");
   }

function play( num ) {
   b = document.getElementById( "RUN" );
   if( b.innerHTML == 'OFF' ) {
       b.style.backgroundColor = "#33FF00";
       b.innerHTML = 'ON';
       }
   document.getElementById( "S" + num ).className = "song";   // incase it was set to skip
   next = num;
   if( seq_switches & 4 )  post( "MUSIC:STOP");
   }

function toggle_skip( num ) {
   b = document.getElementById( "S" + num );
   if( b.className == "songskip" )  b.className = "song";
   else if( b.className == "songplay" )  {
       b.className = "songskip";
       if( seq_switches & 4 )  post( "MUSIC:STOP");
       }
   else {
       b.className = "songskip";
       }
   }

function move_up( num ) {
   if( num < 1 ) return;
   above = num - 1;

   a = document.getElementById( "S" + above );
   atext = a.innerHTML;
   aclass = a.className;

   b = document.getElementById( "S" + num );
   btext = b.innerHTML;
   bclass = b.className;

/* swap the text on the screen  */
   a.innerHTML = btext;
   b.innerHTML = atext;
/* swap classNames */
   a.className = bclass;
   b.className = aclass;

/* swap the array values */
   songs[ above ] = btext;
   songs[ num ] = atext;

/* adjust next if needed */
   if( num == next ) {    /* the 'above' song is playing */
       next = next + 1;
       if( next >= songs.length ) next = 0;
       }
   else {
       if( (num + 1) == next ) {    /* the selected song is playing   */
           next = next - 1;
           }
       }
   }

function WritePlayList() {
   name = window.prompt("Enter a new or existing Playlist file name");
   if( name == null ) return;
   if( songs.length == 0 ) return;
   cmd = "PL_WRITE1:";
   for( i=0; i<songs.length; i++) {
       b = document.getElementById( "S" + i );
       if( b.className == "songskip" )  continue;      // omit skipped songs from playlist
       post( cmd + name + ":" + songs[i] + "\n" );
       cmd = "PL_WRITEn:";
       }
   }

function LoadPlayList() {
   if( playlists.length == 0 ) {
       window.alert("There are no Playlists stored");
       window.location = "jukebox.php";
       return;
       }
   p = "Enter the name of an existing Playlist:\nALL\n";
   for( i=0; i<playlists.length; i++)
       p = p.concat( playlists[i], "\n");
   name = window.prompt( p );
   if( name == null ) return;
   if( name == 'ALL' ) { window.location = "jukebox.php";  return;  }
   window.location = "jukebox.php?playlist=" + name;
   }


/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function myFunction(id) {
    document.getElementById("myDropdown" + id).classList.toggle("show");
}

// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
  if( event.target.matches('.song'))     return;
  if( event.target.matches('.songskip')) return;
  if( event.target.matches('.songplay')) return;

  var dropdowns = document.getElementsByClassName("dropdown-content");
  var i;
  for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }

function BatteryDetails() {
    window.alert("Battery and Charger Status\n\n   Status=" + battery_status_txt[battery_status]
        + "\n   Volts=" + battery_voltage  + "\n   Amps=" + battery_amps + "\n   Temp=" + battery_temp);
   }

function setVolume() {
   msg = "VOLUME:SET:" + vol_mixer + ":" + vol_mute + ":" + document.getElementById("left").value + ":" + document.getElementById("right").value + ":" + vol_link;
//   console.log( msg );
   post( msg );
   }

function Mute( ) {
    vol_mute ^= 1;
    if( vol_mute == 0 )  {
        document.getElementById('MUTE').style.backgroundColor = "#7CfC00";
        }
    else {
        document.getElementById('MUTE').style.backgroundColor = "#c0c0c0";
        }
    setVolume();
    }
function Link( ) {
    if ( vol_mixer == 0 ) return;
    vol_link ^= 1;
console.log(vol_link);
    if( vol_link == 1 )  {
        document.getElementById('LINK').style.backgroundColor = "#7CfC00";
        }
    else {
        document.getElementById('LINK').style.backgroundColor = "#c0c0c0";
        }
    setVolume();
    }


</script>

<?php

echo "<div id='seq' style='display: block; margin: 14px;' >";
  echo "<table style='padding: 4px; border: 4px solid black; width: 100%; height: 100%; font-size: 25px'>\n";
  echo "<tr><th colspan='2' style='width: 100%;color: white; background-color: MediumBlue;'>Music Controls</th></tr>\n";
  echo "<tr><td>";
     echo "<button type='button' id='RUN' onclick='Run()' style='background-color: #FF0000'>OFF</button>\n";
     echo "<button type='button' onclick='Skip()' style='background-color: #FF0000'>Skip</button>\n";
//     echo "<button type='button' onclick='post(\"VOLUME:UP\")' style='background-color: #00FFFF'>Louder</button>\n";
//     echo "<button type='button' onclick='post(\"VOLUME:DOWN\")' style='background-color: #00FFFF'>Softer</button>\n";
//     echo "<button type='button' onclick='post(\"VOLUME:MUTE\")' style='background-color: #00FFFF'>Mute</button>\n";
     echo "<button type='button' onclick='WritePlayList()' style='background-color: #00FFFF'>Save Playlist</button>\n";
     echo "<button type='button' onclick='LoadPlayList()' style='background-color: #00FFFF'>Load Playlist</button>\n";

  echo "</td><td>\n";
     echo "<table style='width: 100%'>";

     if( $vol_link == 1 )  $c = '#7CfC00';     else  $c = '#c0c0c0';
     echo "<tr><td style='width: 10%'>Left:</td>
<td><input type='range' id='left' oninput='if( vol_link ) document.getElementById(\"right\").value = this.value' onChange='setVolume()' min='0' max='100' value='$vol_left' style='width: 90%;'></td>
<td style='width: 10%'><button type='button' id='LINK' onclick='Link( )' style='background-color: $c'>LINK</button></td></tr>\n";

     if( $vol_mute == 0 )  $c = '#7CfC00';     else  $c = '#c0c0c0';
     echo "<tr><td style='width: 10%'>Right:</td>
<td><input type='range' id='right' oninput='if( vol_link ) document.getElementById(\"left\").value = this.value' onChange='setVolume()' min='0' max='100' value='$vol_right' style='width: 90%;'></td>
<td style='width: 10%'><button type='button' id='MUTE' onclick='Mute( )' style='background-color: $c'>MUTE</button></td></tr>\n";

     echo "</table>";


  echo "</td></tr>\n";


  echo "</table>";
echo "</div>\n";

   foreach( $playlist_songs as $key => $name ) {       // playlist songs, set as 'non-skip'
      echo "<div class='dropdown'>\n";
        echo "<button id='S$key' onclick='myFunction($key)' class='song'>$name</button>\n";
        echo "<div id='myDropdown$key' class='dropdown-content'>\n";
          echo "<a href='javascript:play( $key )'>Play</a>\n";
          echo "<a href='javascript:toggle_skip( $key )'>Toggle Skip</a>\n";
          echo "<a href='javascript:move_up( $key )'>Move Up</a>\n";
        echo "</div>\n";
      echo "</div><br>\n";
      }

   foreach( $other_songs as $key => $name ) {       // non-playlist songs, set as 'skip'
      $key += count( $playlist_songs );
      echo "<div class='dropdown'>\n";
        echo "<button id='S$key' onclick='myFunction($key)' class='songskip'>$name</button>\n";
        echo "<div id='myDropdown$key' class='dropdown-content'>\n";
          echo "<a href='javascript:play( $key )'>Play</a>\n";
          echo "<a href='javascript:toggle_skip( $key )'>Toggle Skip</a>\n";
          echo "<a href='javascript:move_up( $key )'>Move Up</a>\n";
        echo "</div>\n";
      echo "</div><br>\n";
      }

include '/var/www/html/includes/footer.php';
?>
