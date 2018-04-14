<script type="text/javascript">

var wTable = {
   Basic:   {  group: 'Wave', display: 'Basic',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:300},   },
               adjust: { p3: {scale:0, default:150 }, },
       },
   Stroke:   { group: 'Wave', display: 'Stroke',
               labels: { p3: "Off Secs:", p4: "On Secs:", p5: "Level:" },
               ranges: { p3: {min:1, max:60}, p4: {min:1, max:60}, p5: {min:1, max:300} },
               adjust: { p3: {scale:0, default:10}, p4: {scale:0, default:10}, p5: {scale:0, default:150} },
       },
   Ramp:     { group: 'Wave', display: 'Ramp',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:1}, p4: {scale:0, default:30}, p5: {scale:0, default:25} },
       },
   Steps:     { group: 'Wave', display: 'Steps',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:400},  },
               adjust: { p3: {scale:0, default:1, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:100}},
       },
   Random:     { group: 'Wave', display: 'Random',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:400},  },
               adjust: { p3: {scale:0, default:1, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:100}},
       },
   Sweep:   {  group: 'Wave', display: 'Sweep',
               labels: { p3: "Min:", p4: "Max:",   p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301},  p5: {min:1, max:100},   },
               adjust: { p3: {scale:0, default:1, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:25} },
       },
   Annoy:      { group: 'Wave', display: 'Annoy',
               labels: { p3: "MinSec:",  p4: "MaxSec:", p5: "Level:" },
               ranges: { p3: {min:1, max:500}, p4: {min:2, max:501}, p5: {min:1, max:300},  },
               adjust: { p3: {scale:0, default:1, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:30}},
       },

   BasicC1:  {  group: 'Contacts', display: 'Basic-1',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:300},   },
               adjust: { p3: {scale:0, default:30 }, },
       },
   BasicC2:  {  group: 'Contacts', display: 'Basic-2',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:300},   },
               adjust: { p3: {scale:0, default:30 }, },
       },
   BasicC12: {  group: 'Contacts', display: 'Basic-12',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:300},   },
               adjust: { p3: {scale:0, default:30 }, },
       },
   }


function rGroup() {                 // called to rotate the groups
   wave = document.getElementById( "WAVE" ).innerHTML;
   if( wave == 'Wave:' )  {
       SetSelect('Contacts');
       document.getElementById( "WAVE" ).innerHTML = 'Contacts:';
       }
   else {
      SetSelect('Wave');
      document.getElementById( "WAVE" ).innerHTML = 'Wave:';
      }
   document.getElementById( "seled" ).selectedIndex = 0;
   sel(document.getElementById( "seled" ).value);
   }

</script>
