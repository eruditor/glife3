// HTML TOP FORM ////////////////////////////////////////////////////////////////

function Pause(set=0) {
  var btn = document.getElementById('pausebtn');
  if(set==-1 || cfg.paused && set!=1) { cfg.paused = 0;  btn.value = `\u23F8\uFE0F`;  Start(); }
  else                                { cfg.paused = 1;  btn.value = `\u25B6\uFE0F`; }
}

function PauseStat() {
  var btn = document.getElementById('pausestatbtn');
  if(cfg.pausestat) { cfg.pausestat = 0;  btn.value = '\u23F9\uFE0F'; }
  else              { cfg.pausestat = 1;  btn.value = '\u23FA\uFE0F'; }
}

function Speed(val=1) {
  var speeds = [1, 10, 60, 300, 1001];
  
  var prevfps = cfg.maxfps || 60;
  
  var prevgear = 0;
  for(var k in speeds) {
    if(prevfps<=speeds[k]) { prevgear = intval(k);  break; }
  }
  
  var newgear = 0;
  if(val>0) newgear = prevgear + 1;
  else      newgear = prevgear - 1;
  
  if(speeds[newgear]>0) {
    cfg.maxfps = speeds[newgear];
  }
  
  if(prevfps<=60 && cfg.maxfps>60) Show();  // run Show's own cycle
}

// TOPBAR ////////////////////////////////////////////////////////////////

var topbar = document.createElement('div');
topbar.style.width = IW + 'px';
topbar.innerHTML += CreateNavButtons();
glcont.prepend(topbar);

function CreateNavButtons() {
  var ret = '';
  
  ret += `
  <style>
    .navTB TD {padding:0px 5px 5px 0px; text-align:center; vertical-align:top;}
    .mediabtn {background:none!important; border:none; padding:1px 0 0 1px!important; margin:0; width:auto; text-align:left; font-size:27px; line-height:29px;}
  </style>
  
  <table cellspacing=0 class='navTB'><tr>
  <td><input type=button class='mediabtn' id='pausebtn'     value='` + (cfg.paused    ? `\u25B6\uFE0F` : `\u23F8\uFE0F`) + `' onclick='Pause();' autofocus title='Start/Stop Calculation'>
  <td><input type=button class='mediabtn' value='\u23ED\uFE0F' onclick='Calc(1); Show(1); Stats(1);' title='One step'>
  <td><input type=button class='mediabtn' id='pausestatbtn' value='` + (cfg.pausestat ? `\u23FA\uFE0F` : `\u23F9\uFE0F`) + `' onclick='PauseStat();' title='Start/Stop Analysis'>
  <td><input type=button class='mediabtn' value='\u23EA\uFE0F' onclick='Speed(-1);' title='Speed Down'>
  <td><input type=button class='mediabtn' value='\u23E9\uFE0F' onclick='Speed( 1);' title='Speed Up'>
  <td><input type=button class='mediabtn' value='&#x1F500;'    onclick='if(confirm("ReField?")) { ReInitSeeds(true); Init(); Pause(-1); }' title='ReField'>
  <td><input type=button class='mediabtn' value='&#x1F504;'    onclick='if(confirm("Restart?")) { ReInitSeeds();     Init(); Pause(-1); }' title='ReStart'>
  <td><input type=button class='mediabtn' value='\u2139\uFE0F' onclick='alert("Enable WebGL2 in your browser settings (usually it is in Experimental Features).\\nTo speed up GPU calculations google for --disable-frame-rate-limit (Chrome setting).");' title='Info'>
  <td><input type=button class='mediabtn' value='\u2623\uFE0F' onclick='Pause(1); console.log(StatORGA(1));' title='Show ORGA calculus'>
  </tr></table>
  
  <table cellspacing=0 class='navTB'><tr>
  <td><input type=button class='mediabtn' value='\u2934\uFE0F' onclick="surface.left+=0.1*(FW/surface.zoom/2); surface.top +=0.1*(FH/surface.zoom/2); surface.zoom/=1.1; Show(1);" title='Zoom in'>
  <td><input type=button class='mediabtn' value='\u2935\uFE0F' onclick="surface.zoom*=1.1; surface.left-=0.1*(FW/surface.zoom/2); surface.top -=0.1*(FH/surface.zoom/2); Show(1);" title='Zoom out'>
  <td><input type=button class='mediabtn' value='\u2B05\uFE0F' onclick="surface.left+=10; Show(1);" title='Move left'>
  <td><input type=button class='mediabtn' value='\u27A1\uFE0F' onclick="surface.left-=10; Show(1);" title='Move right'>
  <td><input type=button class='mediabtn' value='\u2B06\uFE0F' onclick="surface.top -=10; Show(1);" title='Move up'>
  <td><input type=button class='mediabtn' value='\u2B07\uFE0F' onclick="surface.top +=10; Show(1);" title='Move down'>
  <td><input type=button class='mediabtn' value='&#x1F191'     onclick="surface.left=0; surface.top=0; surface.zoom=1; Show(1);" title='Reset view'>
  </tr></table>
  
  <table cellspacing=0 class='navTB'><tr>
  <td><input type=button value='Calc()' onclick="Calc(1);"></td>
  <td><input type=button value='Show(t=0)' onclick="Show(1,0);"></td>
  <td><input type=button value='Show(t=1)' onclick="Show(1,1);"></td>
  <td><input type=button value='Show(t=2)' onclick="Show(1,2);"></td>
  <td><input type=button value='log(Tn)' onclick="console.log(T0,T1,T2);"></td>
  <td><input type=button value='DT&rarr;-DT' onclick="DT=-DT; console.log(DT);"></td>
  </tr></table>
  `;
  
  return ret;
}

// TOPFORM ////////////////////////////////////////////////////////////////

var topform = document.createElement('form');
topform.method = 'GET';
topform.action = '?';
topform.style.float = 'right';
topform.style.font = 'normal 14px/17px Arial';
topform.innerHTML += CreateTopForm();
topbar.prepend(topform);

function CreateTopForm() {
  var sp = ' &nbsp; ';
  
  var inps = '';
  for(var p of URL.entries()) {
    if(p[0]=='rseed' || p[0]=='fseed' || p[0]=='FW' || p[0]=='FH' || p[0]=='LF' || p[0]=='LD') continue;
    inps += `` + p[0] + `<input type=text name='` + p[0] + `' value='` + p[1] + `' size=` + (p[1].length+1) + `>` + sp;
  }
  
  return `
    <table cellspacing=0 cellpadding=0><tr>
    <td>
      ` + inps + `<br>
      <span title="Field Width ">FW</span><input type=text name="FW" value="` + FW + `" size=4>` + sp + `
      <span title="Field Height">FH</span><input type=text name="FH" value="` + FH + `" size=4>` + sp + `
      <span title="% of initially filled area">LF</span><input type=text name="LF" value="` + round(LF*100) + `" size=3>` + sp + `
      <span title="% of initially filled density">LD</span><input type=text name="LD" value="` + round(LD*100) + `" size=3><br>
      <span title="PRNG seed for Rules">Rseed</span><input type=text id="rseedinp" name="rseed" value="` + Rseed + `" size=10>` + sp + `
      <span title="PRNG seed for Field">Fseed</span><input type=text id="fseedinp" name="fseed" value="` + Fseed + `" size=10>` + sp + `
    </td>
    <td><input type=submit value=" OK " style="float:right; height:63px;"></td>
    </tr></table>
  `;
}

//  ////////////////////////////////////////////////////////////////