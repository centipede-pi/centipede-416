
var  charger_mode = -99;
var  image_index = 0;

function ChoseImage( mode, volts ) {


   var i = 0;
   var inx =     [ 9, -1, -1, 9, 9, 9, 9, 9, 9, 9];
   var img =     [ "/images/battery_10.png", "/images/battery_12.png", "/images/battery_25.png", "/images/battery_37.png", "/images/battery_50.png",
          "/images/battery_62.png", "/images/battery_75.png", "/images/battery_87.png", "/images/battery_100.png" ];
   var img_chg =     [ "/images/battery_10_chg.png", "/images/battery_12_chg.png", "/images/battery_25_chg.png", "/images/battery_37_chg.png", "/images/battery_50_chg.png",
          "/images/battery_62_chg.png", "/images/battery_75_chg.png", "/images/battery_87_chg.png", "/images/battery_100_chg.png" ];

   if( mode != charger_mode )  {      // detect when the charger mode changes
       charger_mode = mode;
       image_index = inx[ mode ];     // starting index when mode changes
       }

   if( mode == 0 ) {          // startup
       i = 4;
       if( i = image_index )  {
           image_index  =  i;
           document.getElementById('BATTERY_IMG').src=img[ image_index ];
           }
       return;
       }

   if(( mode == 1 ) || (mode == 2)) {        //  Trickle || changing
       if( volts > 13.94 )      i = 8;         //  images/battery_100_chg.png
       else if( volts > 13.90 ) i = 7;         //  images/battery_87_chg.png
       else if( volts > 13.86 ) i = 6;         //  images/battery_75_chg.png
       else if( volts > 13.80 ) i = 5;         //  images/battery_62_chg.png
       else if( volts > 13.74 ) i = 4;         //  images/battery_50_chg.png
       else if( volts > 13.60 ) i = 3;         //  images/battery_37_chg.png
       else if( volts > 13.39 ) i = 2;         //  images/battery_25_chg.png
       else if( volts > 13.15 ) i = 1;         //  images/battery_12_chg.png
       else i = 0;
       if( i > image_index )  {
           image_index  =  i;
           document.getElementById('BATTERY_IMG').src=img_chg[ image_index ];
           }
       return;
       }

   if(( mode == 3 )  || (mode == 4)) {          //  Discharging
       if( volts > 13.08 )       i = 8;         //  images/battery_100.png
       else if( volts > 12.88 )  i = 7;         //  images/battery_87.png
       else if( volts > 12.76 )  i = 6;         //  images/battery_75.png
       else if( volts > 12.69 )  i = 5;         //  images/battery_62.png
       else if( volts > 12.57 )  i = 4;         //  images/battery_50.png
       else if( volts > 12.35 )  i = 3;         //  images/battery_37.png
       else if( volts > 12.08 )  i = 2;         //  images/battery_25.png
       else if( volts > 11.75 )  i = 1;         //  images/battery_12.png
       else i = 0;
       if( i < image_index )  {
           image_index  =  i;
           document.getElementById('BATTERY_IMG').src=img[ image_index ];
           }
       return;
       }
   }
