<?php
include '../includes/login.inc';

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>

<html>
<head>
<meta charset='UTF-8'>

<style type='text/css'>
html { overflow: hidden }
body { overflow: hidden }
table { border: 4px solid black; width: 100%; height: 100%; font-size: 20px}
th { width: 100%;color: white; background-color: MediumBlue;   }
td.left  { width: 20%; }
td.mid   { width: 10%; border: 1px solid black; }
td.rt    { width: 60%; }
td.meter { width: 8%;  }
td.blue { cursor: pointer; cursor:hand; font-size: 20px; text-align: left; color:blue; text-decoration: underline; }
button   { font-size: 16px; }
select   { font-size: 16px; }

<?PHP
if( $_SESSION['role'] == 'watch' ) {
    echo ".apply:enabled  {  background-color: #fcfcfc; color:black }\n";   /* gray */
    echo ".apply:disabled {  background-color: #fcfcfc; color:black }\n";   /* gray */
    echo ".b_on {  background-color: #fcfcfc; color:black }\n";   /* gray */
    echo ".b_off { background-color: #fcfcfc; color:black }\n";   /* gray */
    }
else {
    echo ".apply:enabled  {  background-color: #FFFF00; }\n";               /* yellow */
    echo ".apply:disabled {  background-color: #fcfcfc; color:black }\n";   /* gray */
    echo ".b_on {  background-color: #33FF00; color:black }\n";   /* green */
    echo ".b_off { background-color: #FF0000; color:black }\n";   /* gray */
    }
?>

a:link    { color: #FFFFFF; }  /* unvisited */
a:visited { color: #FFFFFF; }  /* visited   */
a:hover   { color: #FFFFFF; }  /* mouse over */
a:active  { color: #FFFFFF; }  /* selected  */

#myProgress {
  position: relative;
  width: 120px;
  height: 30px;
  background-color: #ccc;
}

#myBar {
  position: absolute;
  width: 1%;
  height: 100%;
  background-color: #4CAF50;
}

#myProgress {
    transform:rotate(-90deg);
    -ms-transform:rotate(-90deg); /* IE 9 */
    -moz-transform:rotate(-90deg); /* Firefox */
    -webkit-transform:rotate(-90deg); /* Safari and Chrome */
    -o-transform:rotate(-90deg); /* Opera */
}

</style>

<?php
$type = $_GET['type'];
include '../includes/range_webkit.css';
include "../includes/wTable_$type.js";
include '../includes/iframe.js';
?>

</head>
<body onload="init()">

<table padding=4>

<?php
echo ("<tr><th colspan='4'>BRD:$board, CHN:$channel, Type:$row->type, $row->title</th></tr>\n");

  if( $_SESSION['role'] == 'watch' )  echo "<tr><td ID='WAVE' class='blue' >Wave:</td>\n";
  else                                echo "<tr><td ID='WAVE' class='blue' onclick='rGroup()'>Wave:</td>\n";
?>
  <td colspan=3 align='left'>

<span Id="seldiv">
<?php
// this filled in by javascript with a <select>  structure
?>
</span>

<?php
  echo ("<input type='hidden' name='board' value='$board'>\n");
  echo ("<input type='hidden' name='channel' value='$channel'>\n");

  if( $_SESSION['role'] == 'watch' )  echo ("<button type='button' class='apply' id='apply' disabled>APPLY</button>");
  else                                echo ("<button type='button' class='apply' id='apply' onclick='Apply()' disabled>APPLY</button>");

  if( $_SESSION['role'] == 'watch' ) {
      if( $row->run < 1 )
          echo ("<button type='button' id='RUN' class='b_off' >OFF</button>");
      else
          echo ("<button type='button' id='RUN' class='b_on' >ON</button>");
      }
  else {
      if( $row->run < 1 )
          echo ("<button type='button' id='RUN' onclick='Run()' class='b_off'>OFF</button>");
      else
          echo ("<button type='button' id='RUN' onclick='Run()' class='b_on'>ON</button>");
      }
?>
    <span style='float:right; width: 5px;'>&nbsp </span>
    <button id="S2" style='float:right; height: 35px; width: 35px; color: #FFF; background-color: #000; border-radius: 50px;
        border: none; vertical-align: middle; '>2</button>
    <span style='float:right; width: 5px;'>&nbsp </span>
    <button id="S1" style='float:right; height: 35px; width: 35px; color: #FFF; background-color: #000; border-radius: 50px;
        border: none; vertical-align: middle; '>1</button>
</td></tr>
<tr>
  <td class="left"><span id="p3label">&nbsp</span></td>
  <td class="mid"><span id="p3val">&nbsp</span></td>
<?php
  if( $_SESSION['role'] == 'watch' )
      echo "<td class='rt' align='right'><input id='p3slide' name='p3slide' type='range' style = 'width: 90%' oninput=\"setValue('p3', this.value)\" disabled /></td>\n";
  else
      echo "<td class='rt' align='right'><input id='p3slide' name='p3slide' type='range' style = 'width: 90%' oninput=\"setValue('p3', this.value)\" /></td>\n";
?>
  <td class='meter' rowspan='3'><div id="myProgress"><div id="myBar"></div></div></td>
</tr>
<tr>
  <td class="left"><span id="p4label">&nbsp</span></td>
  <td class="mid"><span id="p4val">&nbsp</span></td>
<?php
  if( $_SESSION['role'] == 'watch' )
      echo "<td class='rt' align='right'><input id='p4slide' name='p4slide' type='range' style = 'width: 90%' oninput=\"setValue('p4', this.value)\" disabled/></td>\n";
  else
      echo "<td class='rt' align='right'><input id='p4slide' name='p4slide' type='range' style = 'width: 90%' oninput=\"setValue('p4', this.value)\" /></td>\n";
?>
</tr>
<tr>
  <td class="left"><span id="p5label">&nbsp</span></td>
  <td class="mid"><span id="p5val">&nbsp</span></td>
<?php
  if( $_SESSION['role'] == 'watch' )
      echo "<td class='rt' align='right'><input id='p5slide' name='p5slide' type='range' style = 'width: 90%' oninput=\"setValue('p5', this.value)\" disabled /></td>";
  else
      echo "<td class='rt' align='right'><input id='p5slide' name='p5slide' type='range' style = 'width: 90%' oninput=\"setValue('p5', this.value)\" /></td>";
?>
</tr>
</table>
</body>
</html>

