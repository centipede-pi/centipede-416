<script type="text/javascript">

var wTable = {
   Basic:   {  group: 'Wave', display: 'Basic',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:100},   },
               adjust: { p3: {scale:0, default:30 }, },
       },
   Sweep:   {  group: 'Wave', display: 'Sweep',
               labels: { p3: "Min:", p4: "Max:",   p5: "Speed:" },
               ranges: { p3: {min:1, max:99}, p4: {min:2, max:100},  p5: {min:1, max:50},   },
               adjust: { p3: {scale:0, default:15, LT:'p4'}, p4: {scale:0, default:40, GT:'p3'}, p5: {scale:0, default:10} },
       },
   Stroke:   { group: 'Wave', display: 'Stroke',
               labels: { p3: "Level:", p4: "Speed:" },
               ranges: { p3: {min:1, max:100}, p4: {min:1, max:10},  },
               adjust: { p3: {scale:0, default:30}, p4: {scale:0, default:2}, },
       },
   Ramp:     { group: 'Wave', display: 'Ramp',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:99}, p4: {min:2, max:100}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:15, LT:'p4'}, p4: {scale:0, default:40, GT:'p3'}, p5: {scale:0, default:25} },
       },
   Steps:     { group: 'Wave', display: 'Steps',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:99}, p4: {min:2, max:100}, p5: {min:1, max:1000},  },
               adjust: { p3: {scale:0, default:15, LT:'p4'}, p4: {scale:0, default:40, GT:'p3'}, p5: {scale:0, default:200}},
       },
   Random:     { group: 'Wave', display: 'Random',
               labels: { p3: "Min:",  p4: "Max:", p5: "Speed:" },
               ranges: { p3: {min:1, max:99}, p4: {min:2, max:100}, p5: {min:1, max:1000},  },
               adjust: { p3: {scale:0, default:15, LT:'p4'}, p4: {scale:0, default:40, GT:'p3'}, p5: {scale:0, default:200}},
       },
   Annoy:      { group: 'Wave', display: 'Annoy',
               labels: { p3: "MinSec:",  p4: "MaxSec:", p5: "Level:" },
               ranges: { p3: {min:1, max:500}, p4: {min:2, max:501}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:10, LT:'p4'}, p4: {scale:0, default:30, GT:'p3'}, p5: {scale:0, default:40}},
       },
   BasicC1:  {  group: 'Contacts', display: 'Basic-1',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:100},   },
               adjust: { p3: {scale:0, default:20 }, },
       },
   BasicC2:  {  group: 'Contacts', display: 'Basic-2',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:100},   },
               adjust: { p3: {scale:0, default:20 }, },
       },
   BasicC12: {  group: 'Contacts', display: 'Basic-12',
               labels: { p3: "Level:",  },
               ranges: { p3: {min:1, max:100},   },
               adjust: { p3: {scale:0, default:20 }, },
       },
   StepsC1:  {  group: 'Contacts', display: 'Reward-1',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:100}, p4: {min:2, max:101}, p5: {min:1, max:60},  },
               adjust: { p3: {scale:0, default:20, LT:'p4' }, p4: {scale:0, default:60, GT:'p3' }, p5: {scale:0, default:10 }, },
       },
   StepsC2:  {  group: 'Contacts', display: 'Reward-2',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:100}, p4: {min:2, max:101}, p5: {min:1, max:60},  },
               adjust: { p3: {scale:0, default:20, LT:'p4' }, p4: {scale:0, default:60, GT:'p3' }, p5: {scale:0, default:10 }, },
       },
   StepsC12: {  group: 'Contacts', display: 'Reward-12',
               labels: { p3: "Min:",  p4: "Max:", p5: "Seconds:" },
               ranges: { p3: {min:1, max:100}, p4: {min:2, max:101}, p5: {min:1, max:60},  },
               adjust: { p3: {scale:0, default:20, LT:'p4' }, p4: {scale:0, default:60, GT:'p3' }, p5: {scale:0, default:10 }, },
       },

   CycleC1: {  group: 'Contacts', display: 'Cycles-1',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
       },
   CycleC2: {  group: 'Contacts', display: 'Cycles-2',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
       },
   CycleC12: {  group: 'Contacts', display: 'Cycles-12',
               labels: { p3: "Min/rpm:",  p5: "Level:" },
               ranges: { p3: {min:1, max:200}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:0, default:120}, p5: {scale:0, default:30 }, },
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
