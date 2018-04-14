<script type="text/javascript">

var wTable = {
   Manual:   {  group: 'Wave', display: 'Manual',
       },
   Basic:   {  group: 'Wave', display: 'Basic',
               labels: { p3: "Off Secs:", p4: "On Secs:",   p5: " " },
               ranges: { p3: {min:1, max:100},   p4: {min:1, max:100},  },
               adjust: { p3: {scale:1, default:10 }, p4: {scale:1, default:10 } },
       },
   Sweep:   {  group: 'Wave', display: 'Sweep',
               labels: { p3: "Min Sec:", p4: "Max Sec:",   p5: "Cycles:" },
               ranges: { p3: {min:1, max:100},   p4: {min:1, max:100}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:1, default:10, LT:'p4' }, p4: {scale:1, default:20, GT:'p3' }, p5: {scale:0, default:10 } },
       },
   Random:   {  group: 'Wave', display: 'Random',
               labels: { p3: "Min Sec:", p4: "Max Sec:",   p5: "Cycles:" },
               ranges: { p3: {min:1, max:100},   p4: {min:1, max:100}, p5: {min:1, max:100},  },
               adjust: { p3: {scale:1, default:10, LT:'p4' }, p4: {scale:1, default:20, GT:'p3' }, p5: {scale:0, default:10 } },
       },
   BasicC1:  { group: 'Contacts', display: 'Basic-1',
       },
   BasicC2:  { group: 'Contacts', display: 'Basic-2',
       },
   BasicC12: { group: 'Contacts', display: 'Basic-12',
       },
   CycleC1:  { group: 'Contacts', display: 'Cycle-1',
               labels: { p3: "Min/rpm:",  },
               ranges: { p3: {min:1, max:200},    },
               adjust: { p3: {scale:0, default:60 }, },
       },
   CycleC2:  { group: 'Contacts', display: 'Cycle-2',
               labels: { p3: "Min/rpm:",  },
               ranges: { p3: {min:1, max:200},    },
               adjust: { p3: {scale:0, default:60 }, },
       },
   CycleC12: { group: 'Contacts', display: 'Cycle-12',
               labels: { p3: "Min/rpm:",  },
               ranges: { p3: {min:1, max:200},    },
               adjust: { p3: {scale:0, default:60 }, },
       },
   Invert:   { group: 'Link', display: 'Invert', channel: 2,
       },
   Follow:   { group: 'Link', display: 'Follow', channel: 2,
       },
   }

function rGroup() {                 // called to rotate the groups
   wave = document.getElementById( "WAVE" ).innerHTML;
   if( wave == 'Wave:' && CHANNEL == 2 )  {        // only channel 2 can use Link options
       SetSelect('Link');
       document.getElementById( "WAVE" ).innerHTML = 'Link:';
       }
   else if( wave == 'Link:' || wave == 'Wave:' )  {
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