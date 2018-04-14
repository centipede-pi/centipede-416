<script type="text/javascript">
<?php

// functions common to all the Centipede 416 iFrames


$li = mysql_connect("localhost", "www-data", "estim")
      or die("Could not connect to SQL server: localhost");
   mysql_select_db("centipede", $li)
      or die("Could not select database: centipede");

   $board = $_GET['board'];
   $channel = $_GET['channel'];
   $type = $_GET['type'];

   $rs = mysql_query("select * FROM vals where board=$board and channel=$channel", $li);
   $n = mysql_num_rows($rs);
   if( $n != 1  ) die("Database error, number or records = $n, board=$board, channel=$channel");
   $row = mysql_fetch_object( $rs );
   echo ("   var CHANNEL = $channel;\n");      // 1 or 2, presearved until page reload
   echo ("   var TYPE = '$type';\n");            // estim, viby, relay
   echo ("   var WAVE = '$row->wave';\n");
   echo ("   var P3 = '$row->P3';\n");
   echo ("   var P4 = '$row->P4';\n");
   echo ("   var P5 = '$row->P5';\n");
?>


function getType() {
   return TYPE;
   }

// build a <select> structure with <option> tags for each wTable row in the group
function SetSelect(grp) {
<?php
   if( $_SESSION['role'] == 'watch' )  echo "msg = '<Select Id=\"seled\" disabled >'\n";
   else                                echo "msg = '<Select Id=\"seled\" onchange=\"sel(this.value)\" >'\n";
?>
   for( w in wTable ) {
       if( wTable[w].group == grp )  msg +=  '<option value="' + w + '">' + wTable[w].display + '</option>';
       }
   msg += '</select>';
   document.getElementById( "seldiv" ).innerHTML = msg;
   return;
   }

 // helper function for init(), and set() build the <select> and activate the current selection
function SetWave( w ) {
   if( !(w in wTable) )  return -1;      // test to see if this wave is supported by this kind of output
   if( wTable[ w ].channel !== undefined ) {          // see if wave is limited by channel
       ch = wTable[ w ].channel;
       if( ch != CHANNEL ) return -1;
       }
   grp = wTable[ w ].group;
   SetSelect( grp );
   document.getElementById( "WAVE" ).innerHTML =  grp + ':';
   document.getElementById( "seled" ).value = w;
   return 0;
   }

function range( ctrl, min, max ) {      // helper function for set_ranges()
   slide = ctrl + "slide";
   val   = ctrl + "val";
   document.getElementById( slide ).max = max;
   if( min == -1 ) {        // -i indicates this slider is not used
       document.getElementById( slide ).min = 0;
       document.getElementById( val ).style = "visibility: hidden";
       }
   else {
       document.getElementById( slide ).min = min;
       document.getElementById( val ).style = "visibility: visible; font-weight: bold";
       }
   }

function set_ranges( w ) {
   if( wTable[ w ].ranges == undefined ) {          // ranges missing for all sliders
       range( "p3", -1, 0 );
       range( "p4", -1, 0 );
       range( "p5", -1, 0 );
       }
   else {
       if( wTable[ w ].ranges.p3 !== undefined )   range( "p3", wTable[ w ].ranges.p3.min, wTable[ w ].ranges.p3.max);
       else                                        range( "p3", -1, 0 );
       if( wTable[ w ].ranges.p4 !== undefined )   range( "p4", wTable[ w ].ranges.p4.min, wTable[ w ].ranges.p4.max);
       else                                        range( "p4", -1, 0 );
       if( wTable[ w ].ranges.p5 !== undefined )   range( "p5", wTable[ w ].ranges.p5.min, wTable[ w ].ranges.p5.max);
       else                                        range( "p5", -1, 0 );
       }
   }

function set_labels( wave ) {       // give the selected wave, set the labels for the sliders
   if( wTable[ wave ].labels == undefined ) {          // lables missing for all sliders
       document.getElementById( "p3label" ).innerHTML = " ";
       document.getElementById( "p4label" ).innerHTML = " ";
       document.getElementById( "p5label" ).innerHTML = " ";
       }
   else {
       if( wTable[ wave ].labels.p3 !== undefined )   document.getElementById( "p3label" ).innerHTML = wTable[ wave ].labels.p3;
       else                                           document.getElementById( "p3label" ).innerHTML = " ";
       if( wTable[ wave ].labels.p4 !== undefined )   document.getElementById( "p4label" ).innerHTML = wTable[ wave ].labels.p4;
       else                                           document.getElementById( "p4label" ).innerHTML = " ";
       if( wTable[ wave ].labels.p5 !== undefined )   document.getElementById( "p5label" ).innerHTML = wTable[ wave ].labels.p5;
       else                                           document.getElementById( "p5label" ).innerHTML = " ";
       }
   }

function init() {       // called onload to initualize the screen to current mySql values
  SetWave( WAVE );
  set_labels( WAVE );
  set_ranges( WAVE );
  setValue( 'p3', P3 );
  setValue( 'p4', P4 );
  setValue( 'p5', P5 );
  document.getElementById( 'apply' ).disabled = true;
  }

function set(wave,p3,p4,p5,run) {       // called by index (the parent web page) to change a channel's values
  SetWave( wave );
  set_labels( wave );
  set_ranges( wave );
  setValue( 'p3', p3 );
  setValue( 'p4', p4 );
  setValue( 'p5', p5 );
  document.getElementById( 'apply' ).disabled = true;
  b = document.getElementById( "RUN" );
  if( run == '0' ) {
       b.className = 'b_off';
       b.innerHTML = 'OFF';
       }
   else {
       b.className = 'b_on';
       b.innerHTML = 'ON';
       }
  }

function sel(wave) {            // called when the selected wave changes
   set_labels( wave );
   set_ranges( wave );
   document.getElementById( 'apply' ).disabled = false;
   if( wTable[ wave ].adjust == undefined ) return;          // no defaults available
   var v = ['p3', 'p4', 'p5'];
   for( var i=0; i<3; i++ ) {
       var id = v[i];
       if( wTable[ wave ].adjust[ id ] == undefined ) continue;     // no default available for this one
       if( wTable[ wave ].adjust[ id ].default !== undefined ) {
           var nv = wTable[ wave ].adjust[ id ].default;
           setValue( id, nv );
           }
       }
   }

function Stop() {                 // called by parent to change running/stopped button
   b = document.getElementById( "RUN" );
   b.className = 'b_off';
   b.innerHTML = 'OFF';
   }

function Run() {                  // called when running/stopped button is pressed
   b = document.getElementById( "RUN" );
   if( b.innerHTML == 'ON' ) {
       b.className = 'b_off';
       b.innerHTML = 'OFF';
       }
   else {
       b.className = 'b_on';
       b.innerHTML = 'ON';
       }
   Apply();
   }

function SetRun(val) {                  // called by sequencer.js, VAL can be a whole number, or a floating point number
   b = document.getElementById( "RUN" );
   r = Math.random();
   if((val > 0 ) && ( val >= r )) {
       if( b.innerHTML == 'ON' ) return;
       b.className = 'b_on';
       b.innerHTML = 'ON';
       }
   else {
       if( b.innerHTML == 'OFF' ) return;
       b.className = 'b_off';
       b.innerHTML = 'OFF';
       }
   Apply();
   }

function Apply() {               // called to send the APPLY message to the socket server
   document.getElementById( 'apply' ).disabled = true;
   wave = document.getElementById( "seled" ).value
   p3 = document.getElementById( "p3slide" ).value;
   p4 = document.getElementById( "p4slide" ).value;
   p5 = document.getElementById( "p5slide" ).value;
   button = document.getElementById( "RUN" ).innerHTML;
   if( button == 'ON' ) run = "1";
   else                 run = "0";
   if(( wave != 'Stroke' ) && ( wave != 'Basic' ) && ( wave != 'BasicC1' )
      && ( wave != 'BasicC2' ) && ( wave != 'BasicC12' ) && ( wave != 'CycleC1' )
      && ( wave != 'CycleC2' ) && ( wave != 'CycleC12' ) && ( +p3 > +p4 )) {
   p4 = +p3 + 1 ;
   document.getElementById( "p4val" ).innerHTML = p4;
   document.getElementById( "p4slide" ).value = p4;
   }
<?php
    echo("    msg = 'APPLY:$board,$channel,' + wave + ',' + p3 + ',' + p4 + ',' + p5 + ',' + run;\n");   // a curious mix of PHP and JavaScript, used to create msg
?>
   parent.post( msg );
   }

function setValue(id, newValue) {        // called to update the text box and make sure max > min
   document.getElementById( id + 'slide' ).value=newValue;   // update the slide just in case called from init() or set()
   document.getElementById( 'apply' ).disabled = false;

   nv = newValue;
   wave = document.getElementById( "seled" ).value          // update the text box, with possible scaling

   if( wTable[ wave ].adjust == undefined ) return;         // do not post the value
   if( wTable[ wave ].adjust[id] == undefined ) return;     // do not post the value

   if( wTable[ wave ].adjust[ id ].scale !== undefined ) {
       if( wTable[ wave ].adjust[ id ].scale == 0 )           // value unchanged
           document.getElementById( id + 'val' ).innerHTML= nv;
       if( wTable[ wave ].adjust[ id ].scale == 1 )  {        // divide by 10
           if( newValue >= 10 ) nv = (newValue / 10).toPrecision(2);
           else                 nv = (newValue / 10).toPrecision(1);
           document.getElementById( id + 'val' ).innerHTML= nv;
           }
       }

   if( wTable[ wave ].adjust[ id ].LT !== undefined )  {
       var other_id = wTable[ wave ].adjust[ id ].LT;
       var other_val = +document.getElementById( other_id + 'slide' ).value;
       if( other_val <= newValue ) setValue( other_id, +newValue + 1 );
       }
   if( wTable[ wave ].adjust[ id ].GT !== undefined )  {
       var other_id = wTable[ wave ].adjust[ id ].GT;
       var other_val = +document.getElementById( other_id + 'slide' ).value;
       if( other_val >= newValue ) setValue( other_id, newValue - 1 );
       }
   }

function set_meter( val ) {         // used by iphone and ipad
   v = val / 3;
   document.getElementById("myBar").style.width = v.toString() + '%';
   };

</script>
