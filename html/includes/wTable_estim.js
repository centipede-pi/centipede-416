<script type="text/javascript">

var wTable = {
   Basic:   {  group: 'Wave', display: 'Basic',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:300},   },
               adjust: { p3: {scale:0, default:30 }, },
       },
   Sweep:   {  group: 'Wave', display: 'Sweep',
               labels: { p3: "Min:", p4: "Max:",   p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301},  p5: {min:1, max:10},   },
               adjust: { p3: {scale:0, default:1, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:5} },
       },
   Stroke:   { group: 'Wave', display: 'Stroke',
               labels: { p3: "Level:", p4: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:1, max:10},  },
               adjust: { p3: {scale:0, default:30}, p4: {scale:0, default:5}, },
       },
   Ramp:     { group: 'Wave', display: 'Ramp',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:50},  },
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
   StepsC1:  {  group: 'Contacts', display: 'Endure-1',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:600},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:30 }, },
       },
   StepsC2:  {  group: 'Contacts', display: 'Endure-2',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:600},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:30 }, },
       },
   StepsC12: {  group: 'Contacts', display: 'Endure-12',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:600},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:30 }, },
       },

   CycleC1: {  group: 'Contacts', display: 'Cycles-1',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:300},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
       },
   CycleC2: {  group: 'Contacts', display: 'Cycles-2',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:300},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
       },
   CycleC12: {  group: 'Contacts', display: 'Cycles-12',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:300},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
       },

   Left:    {  group: 'Music', display: 'Left',
               labels: { p3: "Min:",  p4: "Max:", p5: "Gain:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:30},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:16 }, },
       },
   Right:   {  group: 'Music', display: 'Right',
               labels: { p3: "Min:",  p4: "Max:", p5: "Gain:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:30},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:16 }, },
       },
   Mono:    {  group: 'Music', display: 'Mono',
               labels: { p3: "Min:",  p4: "Max:", p5: "Gain:" },
               ranges: { p3: {min:1, max:300}, p4: {min:2, max:301}, p5: {min:1, max:30},  },
               adjust: { p3: {scale:0, default:1, LT:'p4' }, p4: {scale:0, default:30, GT:'p3' }, p5: {scale:0, default:16 }, },
       },

   }


function rGroup() {                 // called to rotate the groups
   wave = document.getElementById( "WAVE" ).innerHTML;
   if( wave == 'Wave:' )  {
       SetSelect('Contacts');
       document.getElementById( "WAVE" ).innerHTML = 'Contacts:';
       }
   else if( wave == 'Contacts:' )  {
       SetSelect('Music');
       document.getElementById( "WAVE" ).innerHTML = 'Music:';
       }
   else {
      SetSelect('Wave');
      document.getElementById( "WAVE" ).innerHTML = 'Wave:';
      }
   document.getElementById( "seled" ).selectedIndex = 0;
   sel(document.getElementById( "seled" ).value);
   }

</script>
