<script type="text/javascript">
var seq_table = [];   // the script once loaded
var seq_nums = [];    // original line numbers
var seq_index = -1;
var seq_run = 0;     // 0=stopped   1=running   2=testing
var seq_wait = 0;
var seq_labels = {};
seq_stack = [];        // call / loop stack
var seq_switches;     // value of the extern contacts   .... .MTR   1=in active state
                      //    M=music   T=tip       R=ring
var auxWindow;
var seq_variables = {};   // array of user defined variables
var until_time = [-1,0];       // [0]=hours  (-1 means not yet computed)   [1]=minutes


function Run( force_off ) {                  // called when ON/OFF button is pressed
   b = document.getElementById( "RUN" );
   if(( b.innerHTML == 'ON' ) || ( force_off == 1 )) {
       b.style.backgroundColor = "#FF0000";
       b.innerHTML = 'OFF';
       seq_run = 0;
       if( seq_switches & 4 )  post( "MUSIC:STOP");
       }
   else {
      if( seq_table.length <  1 ) { window.alert('No file has been loaded' );  return; }
       b.style.backgroundColor = "#33FF00";
       b.innerHTML = 'ON';
       seq_index = -1;
       seq_wait = 0;
       seq_labels = {};       // build a dictionary of the labels, start with an empty one
       seq_stack = [];        // call / loop stack, start with it empty
       seq_variables = {};    // array of user defined variables
       until_time = [-1,0];
       for( i=0; i<seq_table.length; i++)  {
           cmds = seq_table[ i ].split(' ');
           if( cmds[0] == 'LABEL' )  {
               if( cmds[1] in seq_labels )  { window.alert(cmds[1] + ' has already been used');  continue; }
               seq_labels[ cmds[1]  ] = i;
               }
           }
       seq_run = 1;
       }
   }

function Test() {
   b = document.getElementById( "TEST" );
   if( seq_run == 2 ) {
       seq_run = 0;     // 0=stopped   1=running   2=testing
       b.style.backgroundColor = "#FF0000";
       return;
       }
   if( seq_table.length <  1 ) { window.alert('No file has been loaded' );  return; }
   seq_index = -1;
   seq_wait = 0;
   seq_labels = {};       // build a dictionary of the labels, start with an empty one
   seq_stack = [];        // call / loop stack, start with it empty
   seq_variables = {};    // array of user defined variables
   until_time = [-1,0]; 
   for( i=0; i<seq_table.length; i++)  {
       cmds = seq_table[ i ].split(' ');
       if( cmds[0] == 'LABEL' )  {
           if( cmds[1] in seq_labels )  { window.alert(cmds[1] + ' has already been used');  continue; }
           seq_labels[ cmds[1]  ] = i;
           }
       }
   seq_run = 2;
   b.style.backgroundColor = "#33FF00";
   }

function Display_seq() {
   if( seq_table.length <  1 ){
       document.getElementById('seq1').innerHTML = '&nbsp';
       document.getElementById('seq2').innerHTML = 'No File Has been loaded';
       document.getElementById('seq3').innerHTML = '&nbsp';
       return;
       }
   if( seq_index > 0 )
       document.getElementById('seq1').innerHTML = seq_numb[seq_index-1] + ') ' + seq_table[seq_index-1];
   else
       document.getElementById('seq1').innerHTML = '&nbsp';

   document.getElementById('seq2').innerHTML = seq_numb[seq_index] + ') ' + seq_table[seq_index];

   for( n=1; n<4; n++ ) {
     if( (seq_index+n) <  seq_table.length )
       document.getElementById('seq' + (n+2) ).innerHTML = seq_numb[seq_index+n] + ') ' + seq_table[seq_index+n];
     else
       document.getElementById('seq' + (n+2) ).innerHTML = '&nbsp';
     }
   }

function Load() {
  seq_table = [];
  seq_numb = [];
  seq_variables = {};   // array of user defined variables
  until_time = [-1,0]; 
  seq_index = -1;
  orig_line_number = 0;
  var index = document.getElementById('SEQFILE').selectedIndex;
  var file = document.getElementById('SEQFILE').options[index].value;
  PostMessages.push('LOAD:' + file);
  }

function showValue(id, newValue) {        // called to update the text box
   document.getElementById( id ).innerHTML=newValue;
//   document.getElementById( 'apply' ).disabled = false;
   }

function getWave( iframe) {
   eval( "wave = window.frames[ '"  + iframe + "' ]" + iframe_contentDocument + ".getElementById( 'seled' ).value" );
   return wave;
   }


function Sequencer() {
   if( ( seq_run == 0 ) || ( seq_wait > Date.now() ) ) {  setTimeout( Sequencer, 100 ); return; }
   seq_index++;
   if( seq_index < seq_table.length )   { Display_seq(); Process( seq_table[ seq_index] );  }
   else  {
       b = document.getElementById( "RUN" );
       b.style.backgroundColor = "#FF0000";
       b.innerHTML = 'OFF';
       if( seq_switches & 4 )  post( "MUSIC:STOP");
       if( seq_run == 2 ) {
           b = document.getElementById( "TEST" );
           b.style.backgroundColor = "#FF0000";
           window.alert('The TEST has completed' );
           }
       else  window.alert('The SEQUENCE has completed' );
       seq_run = 0;
       }
   setTimeout( Sequencer, 100 );
   }


function Process(line) {
   var brd = 0;
   var chn = 0;
   var wave = 0;
   var iframe = 0;
   var table = 0;
   cmds = line.split(' ');


  if( out = /^IF\sOUTPUT\((\d+)\.(\d)\)=(\w+)\s(.+$)/.exec( line ) )  {       // testing an OUTPUT for active
//        console.log( out[0] + ": " + out[1]  + ", " + out[2] + ", " + out[3] + ", " + out[4] );
        iframe = 'B' + out[1] + 'C' + out[2];
        if( window.frames[ iframe ] == undefined )  return;      // if not defined, treat as false, not active and not inactive
        eval("PV = window.frames[ iframe ]" + iframe_contentDocument + ".getElementById('RUN').innerHTML");    // returns "ON" or "OFF"
        if( out[3] == PV )     Process( out[4] );
        return;
        }

//            IF  VAR    X     GT     Y     cmd
//                       1      2     3       4
  if( out = /^IF\sVAR\s(\w+)\s(\w+)\s(\w+)\s(.+$)/.exec( line ) )  {       // testing an OUTPUT for active
       console.log( out[0] + ": " + out[1]  + ", " + out[2] + ", " + out[3] + ", " + out[4] );
       x1 = ComputeV( out[1] );
       x2 = ComputeV( out[3] );
       if(( out[2] == 'EQ' ) && (x1 == x2 )) Process( out[4] );
       else if(( out[2] == 'NE' ) && (x1 != x2 )) Process( out[4] );
       else if(( out[2] == 'GT' ) && (x1 >  x2 )) Process( out[4] );
       else if(( out[2] == 'GE' ) && (x1 >= x2 )) Process( out[4] );
       else if(( out[2] == 'LT' ) && (x1 <  x2 )) Process( out[4] );
       else if(( out[2] == 'LE' ) && (x1 <= x2 )) Process( out[4] );
       return;
        }

   if( cmds[0] == 'IF' ) {
       var re = /IF\s(\w+=[\w:\*]+)\s(.+$)/;
       var m = re.exec( line );
       if( !m ) {window.alert('Invalid IF statement, ignored' );  return; }

       [c,v] = m[1].split('=');

       if( c == 'MUSIC' ) {
           if(( v == 'ACTIVE' ) && ( seq_switches & 4 ))  Process( m[2] );
           if(( v == 'INACTIVE' ) && ( !(seq_switches & 4) ))  Process( m[2] );
           return;
           }
       if( c == 'CONTACT1' ) {
           if(( v == 'ACTIVE' ) && ( seq_switches & 2 ))  Process( m[2] );
           if(( v == 'INACTIVE' ) && ( !(seq_switches & 2) ))  Process( m[2] );
           return;
           }
       if( c == 'CONTACT2' ) {
           if(( v == 'ACTIVE' ) && ( seq_switches & 1 ))  Process( m[2] );
           if(( v == 'INACTIVE' ) && ( !(seq_switches & 1) ))  Process( m[2] );
           return;
           }


       if( c == 'BEFORE' ) {
           [hr,mn,se] = v.split(':');
//           console.log('BEFORE split  hr=' + hr + ',  mn=' + mn + ',  se=' + se);
           var d = new Date();
           if( se == '**' )  se = d.getSeconds();
           if( mn == '**' )  mn = d.getMinutes();
           if( hr == '**' )  hr = d.getHours();
           until = Number(se) + 60*Number(mn) + 3600*Number(hr);
           now = d.getSeconds() + 60*d.getMinutes() + 3600*d.getHours();
//           console.log('Until=' + until + ',  now=' + now);
           var dif = until - now;
           if( dif > 12*3600 )   return;                  // AFTER
           else if( dif > 0  )   Process( m[2] );         // BEFORE
           else if( dif > -12*3600 )  return;             // AFTER
           else Process( m[2] );                          // BEFORE
           return;
           }
       if( c == 'AFTER' ) {
           [hr,mn,se] = v.split(':');
//           console.log('BEFORE split  hr=' + hr + ',  mn=' + mn + ',  se=' + se);
           var d = new Date();
           if( se == '**' )  se = d.getSeconds();
           if( mn == '**' )  mn = d.getMinutes();
           if( hr == '**' )  hr = d.getHours();
           until = Number(se) + 60*Number(mn) + 3600*Number(hr);
           now = d.getSeconds() + 60*d.getMinutes() + 3600*d.getHours();
//           console.log('Until=' + until + ',  now=' + now);
//           console.log('Line=' + line + ',  Until=' + until+ ',  now=' + now);
           var dif = until - now;
           if( dif > 12*3600 )   Process( m[2] );         // AFTER
           else if( dif > 0  )   return;                  // BEFORE
           else if( dif > -12*3600 )  Process( m[2] );    // AFTER
           return;                                        // BEFORE
           }
       window.alert('Invalid IF verb, ' + m[1] + ', line ignored' );
       return;
       } 

   if( cmds[0] == 'PROMPT' ) {
       msg = line.substr( 7 );
       while( fnd = /.*(&\w+)/.exec( msg ) )  {
//           console.log( 'PROMPT' + ' "' + fnd[1] + '"' );
           x = fnd[1].substr(1);
           v = ComputeV( x );
           msg = msg.replace( fnd[1], v );
           }       
       window.alert( msg );
       return;
       }

   if( cmds[0] == 'OPEN' ) {
       wh = "300";
       ht = "200";
       lt = "0";
       tp = "0";
       url = cmds[1];
//       console.log('OPEN url=|' + url + '|');
       for( i=2; i<cmds.length; i++ ) {
           [c,v] = cmds[i].split('=');
           if( c == 'WIDTH' ) wh = v;
           if( c == 'HEIGHT' ) ht = v;
           if( c == 'LEFT' ) lt = v;
           if( c == 'TOP' ) tp = v;
//           console.log('OPEN c=|' + c + '|,  v=|' + v + '|');
           }
       auxWindow = window.open( url, "", "width=" + wh + ", height=" + ht + ", left=" + lt + ", top=" + tp );
       return;
       }

   if( cmds[0] == 'POST' ) {
       wh = "300";
       ht = "200";
       lt = "0";
       tp = "0";
       msg = "http:/includes/PostHelper.htm?url=" + cmds[1];
       for( i=2; i<cmds.length; i++ ) {
           [c,v] = cmds[i].split('=');
           if( c == 'WIDTH' ) wh = v;
           else if( c == 'HEIGHT' ) ht = v;
           else if( c == 'LEFT' ) lt = v;
           else if( c == 'TOP' ) tp = v;
           else msg += "&" + cmds[i];
           }
       auxWindow = window.open(  msg, "", "width=" + wh + ", height=" + ht + ", left=" + lt + ", top=" + tp );
//       console.log( msg );
       return;
       }

   if( cmds[0] == 'CLOSE' ) {
       auxWindow.close();
       return;
       }


   if( cmds[0] == 'WAIT' ) {
       when = 0;
       for( i=1; i<cmds.length; i++ ) {
           [c,v] = cmds[i].split('=');
           if( c == 'MUSIC' ) { 
               if( !(seq_switches & 4) ) return;   // not active
               seq_wait = 1000 + Date.now();       // else wait one second
               seq_index -= 1;                     // then try this statement again
               return;
               }
           if( c == 'UNTIL' ) {
               if( seq_run == 2 ) {        // if in the TEST mode
                   seq_wait = 1000 + Date.now();
                   return;
                   }
               var d = new Date();
               if( until_time[0] < 0 )  {          // first time on this statement
                   [hr,mn] = v.split(':');
//                   console.log('after split  h=' + hr + ',  m=' + mn );
                   if( mn.charAt(0) == '+' )  mn = d.getMinutes() + Number(mn.substring( 1 ));
                   if( hr.charAt(0) == '+' )  hr = d.getHours()   + Number(hr.substring( 1 ));
//                   console.log('after compute h=' + hr + ',  m=' + mn );
                   if( mn > 59 ) { mn = mn - 60;  hr = hr + 1; }
                   if( hr > 23 ) { hr = hr - 24; }
                   until_time[0] = hr;
                   until_time[1] = mn;
                   }
               if(( until_time[1] != d.getMinutes() )  || ( until_time[0] != d.getHours() ))  {
                   seq_wait = 1000 + Date.now();       // wait one second
                   seq_index -= 1;     // then try this statement again
                   return;
                   }
               until_time = [-1,0];       // time reached, clear the saved values
               return;
               }
           v = ComputeV(v);
           if( c == 'SECONDS' ) { when += v; continue; }
           else if( c == 'MINUTES' ) { when += 60*v; continue; }
           else if( c == 'HOURS' ) { when += 3600*v; continue; }
           else if( c == 'RANDOM' ) { when += ( Math.random() * v ) + ( v / 2); }   //  0.5V - 1.5V in seconds
           else {window.alert('Invalid keyword ' + c + ' ignored' );  return; }
           }
       if( seq_run == 2 ) seq_wait = 1000 + Date.now();     // one second wait if in test mode
       else               seq_wait = Date.now() + (1000 * when);   // else computed wait time
//       console.log("When = " + when );
       return;
       }

   if( cmds[0] == 'STOP' ) {
       if( seq_run == 1 ) {
           b = document.getElementById( "RUN" );
           b.style.backgroundColor = "#FF0000";
           b.innerHTML = 'OFF';
           window.alert('The SEQUENCE has completed' );
           seq_run = 0;
           return;
           }
       seq_wait = 1000 + Date.now();
       return;
       }

   if( cmds[0] == 'MUSIC' ) {     // supports: <title>  QUIT  STOP  PAUSE  UNPAUSE
       title = line.substr( 6 );
       if( seq_run == 2 ) window.alert('MP3 File: |' + title + '| to be played' );
       else  {
           post( "MUSIC:" + title );
           seq_wait = Date.now() + 2000;  // 2 seconds for music to start
           }
       return;
       }

   if( cmds[0] == 'SPEECH' ) {
       text = line.substr( 7 );
       if( text == 'SAY_TIME' )  {
           var d = new Date();
           var h = d.getHours();
           var m = d.getMinutes();
           if( h == 1 ) h = h + " Hour ";
           else         h = h + " Hours ";
           if( m == 1 ) m = m + " Minute";
           else         m = m + " Minutes";
//           text = 'The Time is ' + h + 'and ' + m;
           text = h + 'and ' + m;
           }
       if( seq_run == 2 ) window.alert('SPEECH: ' + text );
       else {
         while( fnd = /.*(&\w+)/.exec( text ) )  {
//             console.log( 'SPEECH-fnd[1]=' + ' |' + fnd[1] + '|' );
             x = fnd[1].substr(1);
//             console.log( 'SPEECH-x=' + ' |' + x + '|' );
             v = ComputeV( x );
//             console.log( 'SPEECH-v=' + ' |' + v + '|' );
             text = text.replace( fnd[1], v );
             }
//         console.log( 'post( "SPEECH:" ' + text );
         post( "SPEECH:" + text );
         seq_wait = Date.now() + 2000;  // 2 seconds for text to be read
         }
       return;
       }


   if( cmds[0] == 'DO' ) {
       times = 0;
       when = 0;
       for( i=1; i<cmds.length; i++ ) {
           [c,v] = cmds[i].split('=');
           v = ComputeV(v);
           if( c == 'TIMES' ) { times = v; continue; }
           else if( c == 'SECONDS' ) { when += v; continue; }
           else if( c == 'MINUTES' ) { when += 60*v; continue; }
           else if( c == 'HOURS' ) { when += 3600*v; continue; }
           else  { window.alert( c + ' is not a valid key to use for DO');  return; }
           }
       if(( times != 0 ) && ( when != 0 )) { window.alert('You cannot specify both HOURS/MINUTES/SECONDS Values and TIMES');  return; }
       if(( times == 0 ) && ( when == 0 )) { window.alert('You must specify either HOURS/MINUTES/SECONDS Values or TIMES');  return; }
       if( times > 0 )    seq_stack.push( {seq: seq_index, type: 'DO', times: times} );
       else               seq_stack.push( {seq: seq_index, type: 'DO', when: Date.now() + (1000 * when) } );
       if( seq_run == 2 ) { seq_wait = 1000 + Date.now(); }
       return;
       }

   if( cmds[0] == 'DONE' ) {
       if( seq_stack.length < 1 )  { window.alert('There is no corresponding DO in the stack');  return; }
       obj =  seq_stack[ seq_stack.length - 1 ];     // get last entry
       if( obj['type'] != 'DO' ) { window.alert('The top of the stack is type ' + obj['type'] + ', not a DO');  return; }
       if( seq_run != 1 ) { seq_stack.pop();   seq_wait = 1000 + Date.now(); return; }

       if( obj.hasOwnProperty( 'times' ) ) {
          reps =  obj[ 'times' ] - 1;
          if( reps > 0 ) {    // count based
              obj[ 'times' ] = reps;
              seq_stack[ seq_stack.length - 1 ] = obj;
              seq_index =  obj[ 'seq' ];
              return;
              } 
          else {
              seq_stack.pop();
              return;
              }
          }
      when =  obj[ 'when' ];
      if( Date.now() >= when ) { seq_stack.pop(); return; }
      seq_index =  obj[ 'seq' ];
      return;
      }

   if( cmds[0] == 'GOTO' ) {
       var mylen = cmds.length - 1;           // compute the number of targets, reduce for 'GOTO' verb     
       if( mylen > 1 ) {                      // if more than one target
           var i = 1 + Math.floor(Math.random() * mylen);    // generate an index randomly:  1-mylen
           var s = cmds[ i ];                 // use the index to select one of the targets
           }
       else {
          var s = cmds[1];                    // if only one target, select it
           }
       if(!( s in seq_labels ))  { window.alert('No such label: ' + s ); return; }
       if( seq_run == 1 ) {
          seq_index =  seq_labels[ s ];       // if in RUN mode, jump to the target line
          Display_seq();
          return;
          }
       seq_wait = 1000 + Date.now();          // if in TEST mode, continue with the next line after 1 second delay
       return;
       }

   if( cmds[0] == 'CALL' ) {
       if(!( cmds[1] in seq_labels ))  { window.alert('No such label' ); return; }
       seq_stack.push( {seq: seq_index, type: 'CALL'} );   // save return 'address'
       seq_index =  seq_labels[ cmds[1]  ];
       if( seq_run == 1 )  return;
       seq_wait = 1000 + Date.now();
       return;
       }

   if( cmds[0] == 'RETURN' ) {
       if( seq_stack.length < 1 )  { window.alert('There is no corresponding CALL in the stack');  return; }
       obj =  seq_stack[ seq_stack.length - 1 ];     // get last entry
       if( obj['type'] != 'CALL' ) { window.alert('The top of the stack is type ' + obj['type'] + ', not a CALL');  return; }
       seq_stack.pop();
       seq_index =  obj[ 'seq' ];
       if( seq_run == 1 )  return;
       seq_wait = 1000 + Date.now();
       return;
       }


   if( cmds[0] == 'LABEL' ) {
       if( seq_run == 1 ) {
          return;
          }
       seq_wait = 1000 + Date.now();
       return;
       }

   if( cmds[0] == 'VAR' ) {
       if( cmds.length != 3 ) { window.alert( '"' + line + '" is not a valid sequencer command' );  return; }
       if( /^[^a-z]/i.test(cmds[1]) )  { window.alert( '"' + cmds[1] + '" is not valid variable name' );  return; }
       if( !(cmds[1] in seq_variables ))  {
//           window.alert( '"' + cmds[1] + '" new variable found');
           seq_variables[ cmds[1]  ] = 0;
           }
       [c,v] = cmds[2].split('=');
       v = ComputeV( v );
//       v = parseInt( v );
       if( c == 'VALUE' )            seq_variables[ cmds[1]  ] = v;
       else if( c == 'INCREASEBY' )  seq_variables[ cmds[1]  ] += v;
       else if( c == 'DECREASEBY' )  seq_variables[ cmds[1]  ] -= v;
       else if( c == 'DIVIDEBY' )    seq_variables[ cmds[1]  ] = seq_variables[ cmds[1]  ] / v;
       else if( c == 'MULTIPLYBY' )  seq_variables[ cmds[1]  ] *= v;
       else if( c == 'RANDOM' )      seq_variables[ cmds[1]  ] = (Math.random() * v ) + ( v / 2);    //  0.5v - 1.5v
       else if( c == 'ROUND' )       seq_variables[ cmds[1]  ] = Math.round( v);
       else if( c == 'MODULUS' )     seq_variables[ cmds[1]  ] %= v;
       else { window.alert( '"' + c + '" is not valid variable action' );  return; }
//       window.alert( '"' + cmds[1] + '" set to: ' + seq_variables[ cmds[1] ].toString()  );
       return;
       }

   if( cmds[0] == 'SET' ) {
       [brd, chn] = cmds[1].split(".")
       iframe = 'B' + brd + 'C' + chn;
       if( window.frames[ iframe ] == undefined )  {
           if( seq_run == 1 )  return;        // ignore error if in RUN mode
           window.alert('Board.Channel ' + cmd[1] + ' is undefined' );  
           return; 
           }
       eval("table = window.frames[ iframe ]" + iframe_contentWindow + ".wTable");     // get reference to the wTable for this output type
//       console.log( "wTable:" + table[ "Invert" ].group );
       eval("wave = window.frames[ '"  + iframe + "' ]" + iframe_contentDocument + ".getElementById( 'seled' ).value" );

       for( i=2; i<cmds.length; i++ ) {
           [c,v] = cmds[i].split('=');
//           v = v.toUpperCase();        // make everything case insensitive
//           c = c.toUpperCase();
           if( c == 'WAVE' ) {                  // convert from the WAVE name displayed to the Internal name
               w2 = 0;
               for( w in table ) {
                   dspl = table[w].display.toUpperCase();
                   if( v == dspl ) { w2 = w; break; }
                   }
//               console.log( "For " + v + ", wave selected:" + w2);
               eval("r = window.frames[ iframe ]" + iframe_contentWindow + ".SetWave( w2 )");       // try setting it
               if( r < 0 )  { window.alert('Wave: "' + v + '" is not valid for this output' );  return; }
               wave = w2;
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".set_ranges( wave )");
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".set_labels( wave )");
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".Apply()");
               continue;
               }

           if( c == 'ON' ) {
               v = ComputeV( v );
               if( v > 0 ) v = 1;   if( v < 0 ) v=0;
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".SetRun(v)");
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".Apply()");
               continue;
               }

           if( ['LEVEL', 'MIN', 'MAX'].indexOf( c ) > -1 ) {       // these are entered as a percentage of the Sequencer slider values
               dest = Get_dest( table, wave, c );                  // this allows the script to be adjusted for individual tolerances without rewriting
               if( dest == 0 )  { window.alert( c + ' is not valid with wave ' + wave );  return; }



               var incr = /([+-]?)(\w+)%/.exec( v );
               if( incr == null )  { window.alert('Illegal value given:' + v  );  return; }
               v = ComputeV( incr[2] );

               $id2 = 'R' +  brd + 'C' + chn;
               PVs = +document.getElementById( $id2 ).value;    // present value of sequencer's slider

               if( incr[1] == '+' )  {
                   eval("PVo = +window.frames[ iframe ]" + iframe_contentDocument + ".getElementById('" + dest + "slide').value");   // present value output
                   sval = PVo + Math.round( v * PVs / 100);
                   }
               else if( incr[1] == '-' ) {
                   eval("PVo = +window.frames[ iframe ]" + iframe_contentDocument + ".getElementById('" + dest + "slide').value");   // present value output
                   sval = PVo - Math.round( v * PVs / 100);
                   }
               else  {
                   sval = Math.round( v  * PVs / 100);
                   }
               if( sval < 1 )  sval = 1;
               if( sval > PVs )  sval = PVs;
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".setValue( dest, sval )");
               eval("window.frames[ iframe ]" + iframe_contentWindow + ".Apply()");
               continue;
               }

           dest = Get_dest( table, wave, c );               //     default processing for all other parameters
           if( dest == 0 )  { window.alert( c + ' is not valid with this wave type' );  return; }
           v = ComputeV( v );
           if( ( table[ wave ].adjust != undefined )
               && ( table[ wave ].adjust[dest] != undefined )
               && ( table[ wave ].adjust[dest].scale != undefined )
               && ( table[ wave ].adjust[dest].scale == 1 ) )
               v = v * 10;
           mi = table[wave].ranges[dest].min;
           ma = table[wave].ranges[dest].max;
           if(v < mi ) v=mi;   if(v > ma) v=ma;
           v = Math.round(v); 
           eval("window.frames[ iframe ]" + iframe_contentWindow + ".setValue( dest, v)");
           eval("window.frames[ iframe ]" + iframe_contentWindow + ".Apply()");
           }
       if( seq_run == 2 ) seq_wait = 1000 + Date.now();
       return;
       }
   window.alert( cmds[0] + ' is not valid command' );
   }



function ComputeV( v ) {          // return a number or a variable's current value
   if( /^[^a-z]/i.test( v ) )  {       // a number is given
       return parseFloat( v );
       }
   if( v == 'TIME' )  { var d = new Date();   var n = d.getTime();  return  Math.round(n/1000); }
   else if( v == 'HOUR' )    { var d = new Date();   return d.getHours();  }
   else if( v == 'MINUTE' )  { var d = new Date();   return d.getMinutes();  }
   else if( v == 'SECOND' )  { var d = new Date();   return d.getSeconds();  }

   if( !( v in seq_variables ))  {         // undefined variable
       window.alert( '"' + v + '" not defined');
       return 0;
       }
//   console.log('looking up the value of "' + v + '"' + ",  returning " + seq_variables[ v ].toString() )
   return seq_variables[ v ];       // return variable's value
   }


function Get_dest( table, wave, c ) {
   for( Pn in table[wave].labels )  {
//       console.log("Get_dest() checking:" + Pn )
       lab =  table[wave].labels[Pn];
       lab = lab.replace( ":", "" );
       lab = lab.replace( " ", "_" );
       lab = lab.toUpperCase();
//       console.log( "Get_dest() comparing:" + c + "  to:" + lab );
       if( c == lab )  { 
//         console.log( "returning:" + Pn );
           return Pn; 
           }
//     console.log( Pn + ": " +  lab );
       }
    return 0;
    }

function grab(id) {
   iframe = 'B' + id;
   if( eval("window.frames[ iframe ]" + iframe_contentDocument + ".getElementById( 'apply' ).disabled == false" ) ) {
      window.alert("You must APPLY changes before you can GRAB them");
      return;
      }
   eval("table = window.frames[ iframe ]" + iframe_contentWindow + ".wTable");     // get reference to the wTable for this output type
   eval("wave = window.frames[ '"  + iframe + "' ]" + iframe_contentDocument + ".getElementById( 'seled' ).value" );

   Pn = Get_dest(  table, wave, "LEVEL" );
   if( Pn == 0 )  Pn = Get_dest(  table, wave, "MAX" );
   if( Pn == 0 ) {
       window.alert("No LEVEL or MAX to Grab");
       return;
       }
   eval("v = window.frames[ iframe ]"   + iframe_contentDocument + ".getElementById( Pn + 'slide').value");
   document.getElementById( 'V' + id ).innerHTML = v;
   document.getElementById( 'R' + id ).value = v;
   }

function reveal( force_close ) {
  if(( document.getElementById('seq').style.display == 'block' ) || ( force_close == 1 ) )
      document.getElementById('seq').style.display = 'none';
  else
      document.getElementById('seq').style.display = 'block';
  }


</script>
