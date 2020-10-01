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

topbar.style.width = canvas.width + 'px';

function CreateTopForm() {
  var ret = '', sp = ' &nbsp; ';
  
  ret += '<input type=submit value=" OK " style="float:right; height:42px;">';
  
  var s = '';  for(var t=1; t<=4; t++) s += '<option value="' + t + '" ' + (t==FD?'selected':'') + '>' + t;
  ret += '<span title="Field Depth">FD=</span><select name="FD" id="FDsel">' + s + '</select>' + sp;
  
  ret += '<span title="Field Width">FW=</span><input type=text name="FW" value="' + FW + '" size=4>' + sp;
  
  ret += '<span title="Field Height">FH=</span><input type=text name="FH" value="' + FH + '" size=4>' + sp;
  
  var rule0 = [];  if(Ruleset.indexOf(',')>-1) rule0[Ruleset] = Ruleset;  else rule0[''] = '';
  var s = '';  for(var t in {...rule0, 'random':'', ...NamedRules}) s += '<option value="' + t + '" ' + (t==Ruleset?'selected':'') + '>' + t;
  var onchange = `if(this.value!='random') document.getElementById('FDsel').value = ConwayNotaset(NamedRules[this.value]).length`;
  ret += '<span title="NamedRules">Genom=</span><select name="Ruleset" onchange="' + onchange + '">' + s + '</select>' + sp;
  
  ret += '<span title="PRNG seed">seed=</span><input type=text name="seed" value="' + Seed + '" size=10>' + sp;
  
  ret += '<br>';
  
  ret += '<span title="Width of initially filled area">LW=</span><input type=text name="LW" value="' + LW + '" size=3>' + sp;
  
  ret += '<span title="Height of initially filled area">LH=</span><input type=text name="LH" value="' + LH + '" size=3>' + sp;
  
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
    <input type=button id='pausebtn' value='` + (cfg.paused ? `unpause` : `pause`) + `' onclick='Pause();' autofocus>
    <input type=button id='pausestatbtn' value='` + (cfg.pausestat ? `unpause stats` : `pause stats`) + `' onclick='PauseStat();'>
  `;
  ret += '<br>';
  ret += `
    <input type=button value="&minus;" onclick="surface.left+=0.1*(FW/surface.zoom/2); surface.top +=0.1*(FH/surface.zoom/2); surface.zoom/=1.1; Show(1);">
    <input type=button value="+"       onclick="surface.zoom*=1.1; surface.left-=0.1*(FW/surface.zoom/2); surface.top -=0.1*(FH/surface.zoom/2); Show(1);">
    
    <input type=button value="&larr;" onclick="surface.left+=10; Show(1);">
    <input type=button value="&rarr;" onclick="surface.left-=10; Show(1);">
    <input type=button value="&uarr;" onclick="surface.top -=10; Show(1);">
    <input type=button value="&darr;" onclick="surface.top +=10; Show(1);">
    
    <input type=button value="&empty;" onclick="surface.left=0; surface.top=0; surface.zoom=1; Show(1);">
  `;
  
  return ret;
}
topbar.innerHTML += CreateNavButtons();
