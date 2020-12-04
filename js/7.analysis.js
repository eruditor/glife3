// LOAD SUB-SCRIPTS ////////////////////////////////////////////////////////////////

//DynamicScriptLoad('js/7a.fourier.js');
DynamicScriptLoad('js/7b.orga.js');

// PARAMS ////////////////////////////////////////////////////////////////

var StatGraphFuncName = 'log';

var drawRzoom = 1;

// CONTAINERS ////////////////////////////////////////////////////////////////
// naming is messy, who cares

var statcont = document.createElement('div');
statcont.style.cssText = 'font:normal 11px/11px Lucida Console, Monaco, Monospace; white-space:nowrap;';
glcont.append(statcont);

var stxtfps = document.createElement('div');
stxtfps.style.marginTop = '5px';
statcont.append(stxtfps);

var stxtstat = document.createElement('div');
stxtstat.style.marginTop = '10px';
statcont.append(stxtstat);

var statcanvas = document.createElement('div');
statcanvas.style.whiteSpace = 'normal';
statcont.append(statcanvas);

var statcanvasttl = document.createElement('div');
statcanvasttl.textContent = "species population (" + StatGraphFuncName + " scale):";
statcanvasttl.style.margin = '10px 0 0 0';
statcanvas.append(statcanvasttl);

var scnv_height = 300;
var scnvs = document.createElement('canvas');
statcanvas.append(scnvs);
scnvs.width  = IW;           scnvs.style.width  = scnvs.width  + 'px';
scnvs.height = scnv_height;  scnvs.style.height = scnvs.height + 'px';
var sctx = scnvs.getContext('2d');

if(cfg.drawrules) {
  var sdivttl0 = document.createElement('div');
  sdivttl0.textContent = "physics (rules):";
  sdivttl0.style.margin = '10px 0 0 0';
  statcont.append(sdivttl0);
  
  var rcnv = document.createElement('canvas');
  rcnv.width  = drawRzoom * 5 * (2 * 4 * 9 + 1) * FD;  rcnv.style.width  = rcnv.width  + 'px';
  rcnv.height = drawRzoom * 300 * RB;                  rcnv.style.height = rcnv.height + 'px';
  statcont.append(rcnv);
  rctx = rcnv.getContext('2d');
}

var sdivttl1 = document.createElement('div');
sdivttl1.style.margin = '10px 0 0 0';
statcont.append(sdivttl1);

var divrules = document.createElement('div');
divrules.style.margin = '10px 0 0 0';
statcont.append(divrules);

// STAT GRAPH ////////////////////////////////////////////////////////////////

     if(StatGraphFuncName=='log')   function StatGraphFunc(x) { return Math.log2(1+x); }
else if(StatGraphFuncName=='cbrt')  function StatGraphFunc(x) { return Math.cbrt(x); }
else if(StatGraphFuncName=='root4') function StatGraphFunc(x) { return Math.pow(x, 0.25); }

// RECORD ////////////////////////////////////////////////////////////////

class Record {
  constructor() {
    this.ttl = 0;  // total number of living cells
    
    this.livecells = [];  // total number of living cells in this z-plane
    this.icehsh = [];  // quasi-sum of living cell coordinates, to detect frozen states
    this.frozentime = [];  // for how many turns this z-plane is frozen

    this.nqfilld = [];  // total number of non-empty squares
    this.nqchngd = [];  // number of squared that changed their emptiness
    
    this.fillin = [];
    this.spread = [];
    this.variat = [];
    
    this.orga_num = [];
    this.orga_sum = [];
    
    this.zero();
  }

  zero() {
    for(let key in this) {
      var val = key=='orga_num' || key=='orga_sum' ? -1 : 0;
      if(Array.isArray(this[key])) {
        for(let z=0; z<FD; z++) this[key][z] = val;
      }
      else if(Number.isInteger(this[key])) {
        this[key] = val;
      }
    }
  }
}

class Records {  // tracking and writing to DB population characteristics
  constructor() {
    this[0] = new Record();  // storing it for SO (previous)
    this[1] = new Record();  // and S1 (current) turns
  }
  
  delta(field, z) {
    return 2 * (this[S1][field][z] - this[S0][field][z]) / (this[S1][field][z] + this[S0][field][z]);
  }
  
  absdelta(field, z) {
    return Math.abs(this.delta(field, z));
  }
  
  Bgc(k, v) {
    var bgc = '';
    for(let x in gl_bgc4records[k]) {
      bgc = gl_bgc4records[k][x];
      if(v<x) break;
    }
    return bgc;
  }
  
  SpanBgc(k, v) {
    v = round(v);
    return `<span style='background:#` + this.Bgc(k, v) + `'>` + v + `</span>%`;
  }
}

// SAVE RULES ////////////////////////////////////////////////////////////////

function SaveGlifetri(prms={}) {
  saved = true;  // in the beginning to prevent multi-saves if case of lags
  
  StatORGA();  // calc orga stats before saving
  
  if(Family=='Conway') {
    prms['family_id'] = 1;
    var notaset = '';
    var notas = ConwayNotaset(Notaset);
    for(z in notas) notaset += (notaset?',':'') + notas[z];
    prms['notaset'] = notaset;
  }
  else if(Family=='Conway3D') {
    prms['family_id'] = 3;
    var notaset = '';
    var notas = ConwayNotaset(Notaset);
    for(z in notas) notaset += (notaset?',':'') + notas[z];
    prms['notaset'] = notaset;
  }
  else if(Family=='Langton') {
    prms['family_id'] = 10;
    prms['notaset'] = '';
  }
  
  prms['mutaset'] = EncodeMutaStr(Mutas);
  prms['rseed'] = Rseed;
  prms['fseed'] = Fseed;
  prms['stopped_nturn'] = nturn;
  prms['records'] = JSON.stringify(rec[S1]);
  prms['context'] = window.location.search;
  if(cfg.rerun) prms['rerun'] = rerun_gr_id;
  
  // put maximum among layers>0 to glifetrirun.orgasum
  // exclude z=0, because it represents "ground" non-living layer and is often too much structured or oscillating, making max(orgasum) undeservingly too large
  prms['orgasum'] = -1;
  for(z in rec[S1].orga_sum) if(z>0 && prms['orgasum']<rec[S1].orga_sum[z]) prms['orgasum'] = rec[S1].orga_sum[z];
  
  var q = '';
  for(var k in prms) {
    q += (q?'&':'') + k + '=' + encodeURIComponent(prms[k]);
  }
  
  XHRsave3(q);
}

// DRAW RULES ////////////////////////////////////////////////////////////////

function PutRpixel(x, y, z, v, r) {
  var clr, cl;
  if(v==0) {
    clr = 'rgb(187,187,187)';
  }
  else {
    cl = r==0 ? Color4Cell(z, v, 1, 0.8) : Color4Cell(z, v);
    clr = 'rgb('+cl.r+','+cl.g+','+cl.b+')';
  }
  rctx.strokeStyle = clr;
  rctx.fillStyle = clr;
  rctx.fillRect(drawRzoom * x, drawRzoom * y, drawRzoom, drawRzoom);
}

function DrawRules(full=false) {
  var sum = [];
  for(var z=0; z<FD; z++) {
    sum[z] = [];
    for(var b=0; b<RL; b++) {
      var r = GetRule(z, b);
      if(!sum[z][r]) sum[z][r] = 0;
      sum[z][r] ++;
    }
  }
  var s = '';
  for(var z=0; z<FD; z++) {
    s += 'z='+z+': ';
    for(var r in sum[z]) {
      s += '&Sigma;'+r+'='+sum[z][r]+' ';
    }
    s += '<br>';
  }
  sdivttl1.innerHTML = s;
  
  /*
  var ii = 0;
  for(var i=0; i<1000; i++) {
    var z = rndJ(0, FD);
    var b = rndJ(0, RL);
    var v = GetRule(z, b);
    if(v>0) { console.log(z+':'+NeibStr4Int(b)+'->'+v);  ii++; }
    if(ii>100) break;
  }
  */
  
  if(cfg.drawrules) {
    rctx.clearRect(0, 0, rcnv.width, rcnv.height);
    var sy = [];
    for(var z=0; z<FD; z++) {
      for(var b=0; b<RL; b++) {
        var r = GetRule(z, b);
        if(!r && !full) continue;
        var rule = NeibArr4Int(b);
        var sx = 0;
        var sum = 0;
        if(rule[ 0]!=0) sum += 4 * 9;
        if(rule[ 9]!=0) sum += 1;
        if(rule[10]!=0) sum += 2;
        for(var i=1; i<9; i++) {
          if(rule[i]!=0) sum += 4;
        }
        sx = 2 + 5 * (sum + (2 * 4 * 9 + 1) * z);
        sy[sx] = sy[sx] ? sy[sx] + 7 : 3;
        if(sy[sx] * drawRzoom <= rcnv.height) {
          for(var k in RG) {
            var dx = RG[k][0];
            var dy = RG[k][1] - 2*RG[k][2];
            PutRpixel(sx + dx, sy[sx] + dy, z, rule[k], r);
          }
        }
      }
    }
  }
}

// INIT STATS ////////////////////////////////////////////////////////////////

var nGen = 1;  // number of generations produced in random-search

function InitStats() {
  nturn = 0;  nturn0 = 0;
  nshow = 0;  nshow0 = 0;
  date0 = new Date;
  saved = false;
  qq0 = [];
  
  rec = new Records();  
  
  prevpoints = [], infostep = -1;
  
  S0 = 0;  S1 = 1;  // time moments for Stats
  
  sctx.clearRect(0, 0, scnvs.width, scnvs.height);
  
  DrawRules();
}

// STATS MAIN ////////////////////////////////////////////////////////////////

function Stats(force=false) {
  // calc fps
  var sfps = '';
  
  sfps += `turn = ` + nturn + ' | ';
  sfps += `shown  = ` + nshow + ' | ';
  
  var date1 = new Date;
  var msCalc = (date1 - date0) / (nturn - nturn0);
  var msShow = (date1 - date0) / (nshow - nshow0);
  date0 = date1;  nturn0 = nturn;  nshow0 = nshow;
  sfps += 'fps = ' + round(1000 / msCalc) + ' <span class=gr>(' + round(1000 / msShow) + ')</span><br>';
  
  if(nGen>1) sfps = 'nGen = ' + nGen + ' | ' + sfps;
  
  if(sfps) stxtfps.innerHTML = sfps;
  
  if(!force && (cfg.paused || cfg.pausestat)) return;
  
  var sstat = '';
  
  var x, y, z;
  
  var specstat = [];  // stat for graph
  
  var qd = 10, qx, qy, qq = []; // grid of squares qd*qd with coordinates (qx,qy) is to measure how species spread
  var qw = floor(FW / qd), qh = floor(FH / qd);  // assuming FW%qd==0 && FH%qd==0
  var nqtotal = qw * qh;  // total number of squares
  
  rec[S1].zero();
  
  if(cfg.calcorga && !force) {
    StatORGA();
  }
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, Framebuffers[T0]);  // preparing to fetch Field data from GPU
  for(var z=0; z<FD; z++) {
    gl.readBuffer(gl.COLOR_ATTACHMENT0 + z);  // z-plane of the Field is a buffer attached to a texture point
    gl.readPixels(0, 0, FW, FH, gldata_Format, gldata_Type, F, 4 * FW * FH * z);  // reading pixels of layer=z to F[z]
  }
  
  for(var z=0; z<FD; z++) {
    specstat[z] = 0;  qq[z] = [];
    for(var x=0; x<FW; x++) {
      qx = floor(x / qd);
      if(!qq[z][qx]) qq[z][qx] = [];
      for(var y=0; y<FH; y++) {
        var cell = GetCell(x, y, z);
        if(!cell.a) continue;  // dead cell
        
        specstat[z] ++;
        rec[S1].ttl ++;

        rec[S1].livecells[z] ++;
        
        qy = floor(y / qd);
        if(!qq[z][qx][qy]) qq[z][qx][qy] = 0;
        qq[z][qx][qy] ++;  // each element of qq stores number of living cells in this square
        
        rec[S1].icehsh[z] += (x + y);  // hash-like sum of living cells
      }
    }
  }
  
  // plotting graphs
  infostep ++;
  if(infostep<zoom*FW) {
    for(var z=0; z<FD; z++) {
      var clr = Color4Cell(z);
      var xx = infostep;
      var yy = specstat[z] ? scnv_height - round(StatGraphFunc(specstat[z]) / StatGraphFunc(FW*FH) * scnv_height) : scnv_height;
      var style = 'rgba('+clr.r+','+clr.g+','+clr.b+',0.9)';
      if(prevpoints[z] && xx>0) {
        sctx.beginPath();
        sctx.strokeStyle = style;
        sctx.moveTo(xx-1, prevpoints[z]);
        sctx.lineTo(xx, yy);
        sctx.stroke();
      }
      else {
        sctx.fillStyle = style;
        sctx.fillRect(xx, yy, 1, 1);
      }
      prevpoints[z] = yy;
    }
  }
  
  // counting squares
  for(z=0; z<FD; z++) {
    for(qx=0; qx<qw; qx++) {
      for(qy=0; qy<qh; qy++) {
        if(qq[z][qx][qy]) rec[S1].nqfilld[z] ++;
        if(qq0.length && sgn(qq[z][qx][qy]) != sgn(qq0[z][qx][qy])) rec[S1].nqchngd[z] ++;
      }
    }
  }
  qq0 = [...qq];
  
  // for how long planes are frozen
  for(z=0; z<FD; z++) {
    if(!rec[S1].ttl || !rec[S1].icehsh[z]) rec[S1].frozentime[z] = rec[S0].frozentime[z] + 10;
    else if(rec.absdelta('icehsh', z) < 0.01 && rec.absdelta('livecells', z) < 0.01) rec[S1].frozentime[z] = rec[S0].frozentime[z] + 1;
    else rec[S1].frozentime[z] = 0;
  }
  
  // empty or full or frozen planes
  var interesting_z = 0;  // number of planes that are not full and not dead and not frozen
  var failed_at = '';  // reason why failed
  var stb = '';  // table contents to output
  for(z=0; z<FD; z++) {
    var fillin = 100 * (rec[S1].livecells[z] / (FW*FH));  // percent of all cells alive
    var spread = 100 * (rec[S1].nqfilld[z] / nqtotal);  // percent of filled (non-empty) squares
    var variat = 100 * (rec[S1].nqchngd[z] / rec[S1].nqfilld[z]);  // percent of non-empty squares that changed their emptiness in the current turn
    
    var flags = '';
    
    var clr1 = '';
         if(spread< 5) clr1 = 'd00';
    else if(spread<10) clr1 = 'ff0';
    else if(spread>99) clr1 = '777';
    else if(spread>95) clr1 = 'ccc';
    if(clr1) flags = '<span style="background:#' + clr1 + ';">' + round(spread) + '%</span> ';
    
    var clr2 = '';
         if(rec[S1].frozentime[z]>2) clr2 = 'D00';
    else if(rec[S1].frozentime[z]>0) clr2 = 'FF0';
    if(clr2) flags += '<span style="background:#' + clr2 + ';">frozen=' + rec[S1].frozentime[z] + '</span> ';
    
    if(spread<5 || spread>99 || rec[S1].frozentime[z]>2) { failed_at += clr1 + clr2 + ';'; }
    else interesting_z ++;
    
    rec[S1].fillin[z] = fillin;
    rec[S1].spread[z] = spread;
    rec[S1].variat[z] = variat;
    
    stb += `
      <tr>
        <td>` + z + `</td>
        <td>` + rec[S1].livecells[z] + `</td>
        <td>` + rec[S1].nqfilld[z] + `</td>
        <td>` + rec[S1].nqchngd[z] + `</td>
        <td>` + rec.SpanBgc('fillin', fillin) + `</td>
        <td>` + rec.SpanBgc('spread', spread) + `</td>
        <td>` + rec.SpanBgc('variat', variat) + `</td>
        <td>` + rec[S1].icehsh[z] + `</td>
        <td>` + flags + `</td>
        <td>` + rec[S1].orga_num[z] + `</td>
        <td>` + rec[S1].orga_sum[z] + `</td>
      </tr>
    `;
  }
  sstat += `
    <table cellspacing=0 id='glifeStatTB'>
      <tr>
        <th>z</th><th>livecells</th><th>nqfilld</th><th>nqchngd</th>
        <th>fillin</th><th>spread</th><th>variat</th>
        <th>&nbsp;&nbsp;icehsh</th><th>flags</th>
        <th>orgaN</th><th>orga&Sigma;</th>
      </tr>
      ` + stb + `
    </table>
  `;
  
  if(cfg.autore || cfg.rerun) {
    if((!interesting_z && nturn>500) || (nturn>=5000)) {  // if no interesting planes left - restart
      Pause(1);
      SaveGlifetri({'stopped_at':failed_at});
      nGen ++;
      if(nGen>300) {
        window.location.reload();  // reloading page sometimes to refresh seeds and avoid potential locks
      }
      else if(cfg.rerun) {
        rerun_continue = GetRerun();
        if(!rerun_continue) window.location.reload();
        Frand32(Fseed);  document.getElementById('fseedinp').value = Fseed;
        Init();
      }
      else {
        ReInitSeeds();
        Init();
      }
      Pause(-1);
    }
  }
  else if(nturn>=10000 && !saved) {  // saving all long-runned cases
    SaveGlifetri({'stopped_at':'x'});
  }
  
  if(sstat) stxtstat.innerHTML = sstat;
  
  // flipping time for Stats
  if(S1==1) { S1 = 0;  S0 = 1; } else { S1 = 1;  S0 = 0; }
  
}

//  ////////////////////////////////////////////////////////////////