// HTML TOP FORM ////////////////////////////////////////////////////////////////

function Pause(set=0) {
  var btn = document.getElementById('pausebtn');
  if(set==-1 || cfg.paused && set!=1) { cfg.paused = 0;  btn.value = 'pause';  Start(); }
  else                                { cfg.paused = 1;  btn.value = 'unpause'; }
}

function PauseStat() {
  var btn = document.getElementById('pausestatbtn');
  if(cfg.pausestat) { cfg.pausestat = 0;  btn.value = 'pause stats';   }
  else              { cfg.pausestat = 1;  btn.value = 'unpause stats'; }
}

var topbar  = document.getElementById('topbar');
var topform = document.getElementById('topform');

topbar.style.width = IW + 'px';

function CreateTopForm() {
  var ret = '', sp = ' &nbsp; ';
  
  ret += '<input type=submit value=" OK " style="float:right; height:63px;">';
  
  var s = '';  for(var t in {'conway':1,'langton':1}) s += '<option value="' + t + '" ' + (t==Family?'selected':'') + '>' + t;
  ret += '<span title="Rules Family">Family=</span><select name="family">' + s + '</select>' + sp;
  
  var rule0 = [];  if(Ruleset.indexOf(',')>-1) rule0[Ruleset] = Ruleset;  else rule0[''] = '';
  var s = '';  for(var t in {...rule0, 'random':'', ...NamedRules}) s += '<option value="' + t + '" ' + (t==Ruleset?'selected':'') + '>' + t;
  var onchange = `if(this.value!='random') document.getElementById('FDsel').value = ConwayNotaset(NamedRules[this.value]).length`;
  ret += '<span title="NamedRules">Rules=</span><select name="ruleset" onchange="' + onchange + '" style="width:160px;">' + s + '</select>' + sp;
  
  ret += '<span title="PRNG seed for Rules">Rseed=</span><input type=text id="rseedinp" name="Rseed" value="' + Rseed + '" size=10>' + sp;
  
  ret += '<br>';
  
  ret += '<span title="Field Depth ">FD=</span><input type=text name="FD" value="' + FD + '" size=4 id="FDsel">' + sp;
  ret += '<span title="Field Width ">FW=</span><input type=text name="FW" value="' + FW + '" size=4>' + sp;
  ret += '<span title="Field Height">FH=</span><input type=text name="FH" value="' + FH + '" size=4>' + sp;
  
  ret += '<span title="% of initially filled area">LF=</span><input type=text name="LF" value="' + LF + '" size=3>' + sp;
  
  ret += '<span title="PRNG seed for Field">Fseed=</span><input type=text id="fseedinp" name="Fseed" value="' + Fseed + '" size=10>' + sp;
  
  ret += '<br>';
  
  ret += '<span title="0 = requestAnimationFrame, 1000 = no setTimeout">maxfps=</span>';
  ret += '<input type=number name="maxfps" value="' + cfg.maxfps + '" min=0 max=1001 style="width:4em;" onchange="var runshow=0; if(cfg.maxfps<=60 && this.value>60) runshow=1; cfg.maxfps=this.value; if(runshow) Show();">' + sp;
  
  ret += '<span title="Show once per showiter Calcs">showiter=</span><input type=text name="showiter" value="' + cfg.showiter + '" size=3>' + sp;
  
  ret += '<span title="paused at start">paused=</span><input type=checkbox name="paused" ' + (cfg.paused ? 'checked' : '') + '>' + sp;
  
  return ret;
}
topform.innerHTML += CreateTopForm();

function CreateNavButtons() {
  var ret = '';
  
  ret += `
  <table cellspacing=0 cellpadding=5><tr>
  <td>
    <input type=button id='pausebtn' value='` + (cfg.paused ? `unpause` : `pause`) + `' onclick='Pause();' autofocus>
    <input type=button id='pausestatbtn' value='` + (cfg.pausestat ? `unpause stats` : `pause stats`) + `' onclick='PauseStat();'>
    <br>
    <input type=button value="&minus;" onclick="surface.left+=0.1*(FW/surface.zoom/2); surface.top +=0.1*(FH/surface.zoom/2); surface.zoom/=1.1; Show(1);">
    <input type=button value="+"       onclick="surface.zoom*=1.1; surface.left-=0.1*(FW/surface.zoom/2); surface.top -=0.1*(FH/surface.zoom/2); Show(1);">
    <input type=button value="&larr;" onclick="surface.left+=10; Show(1);">
    <input type=button value="&rarr;" onclick="surface.left-=10; Show(1);">
    <input type=button value="&uarr;" onclick="surface.top -=10; Show(1);">
    <input type=button value="&darr;" onclick="surface.top +=10; Show(1);">
    <input type=button value="&empty;" onclick="surface.left=0; surface.top=0; surface.zoom=1; Show(1);">
  </td><td>
    <input type=button value='ReField' style='width:70px;' onclick='if(confirm("ReField?")) { ReInitSeeds(true); Init(); }'>
    <br>
    <input type=button value='Restart' style='width:70px;' onclick='if(confirm("Restart?")) { ReInitSeeds(); Init(); }'>
  </td><td>
    <input type=button value='FillFT' onclick='FillFT();' title='Fill DFT buffer with initial data'>
    <input type=button value='DFT' onclick='StatDFT();' title='Make Discrete Fourier Transform'>
    <input type=button value='IDFT' onclick='StatDFT(1);' title='Inverse Discrete Fourier Transform'>
    <input type=button value='FilterFT' onclick='FilterFT();' title='Cut off central areas'>
    <br>
    <input type=button value='ShowFT0' onclick='ShowFT(0);' title='Show DFT buffer#0'>
    <input type=button value='ShowFT1' onclick='ShowFT(1);' title='Show DFT buffer#1'>
  </td>
  </table>
  `;
  
  return ret;
}
topbar.innerHTML += CreateNavButtons();

//  ////////////////////////////////////////////////////////////////