// STAT GRAPH ////////////////////////////////////////////////////////////////

var StatGraphFuncName = 'log';
     if(StatGraphFuncName=='log')   function StatGraphFunc(x) { return Math.log2(1+x); }
else if(StatGraphFuncName=='cbrt')  function StatGraphFunc(x) { return Math.cbrt(x); }
else if(StatGraphFuncName=='root4') function StatGraphFunc(x) { return Math.pow(x, 0.25); }

var sdiv = document.getElementById('statcanvas');

var sdivttl = document.createElement('div');
sdivttl.textContent = "species population (" + StatGraphFuncName + " scale):";
sdivttl.style.margin = '10px 0 0 0';
sdiv.appendChild(sdivttl);

var scnv_height = 300;
var scnvs = document.createElement('canvas');
sdiv.appendChild(scnvs);
scnvs.width  = IW;           scnvs.style.width  = scnvs.width  + 'px';
scnvs.height = scnv_height;  scnvs.style.height = scnvs.height + 'px';
var sctx = scnvs.getContext('2d');

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
    
    this.zero();
  }

  zero() {
    for(let key in this) {
      if(Array.isArray(this[key])) {
        for(let z=0; z<FD; z++) this[key][z] = 0;
      }
      else if(Number.isInteger(this[key])) {
        this[key] = 0;
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

function XHRsave3(q) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', '/alife/glife3/gl_save.php');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    if(xhr.status==200) { if(xhr.responseText) alert(xhr.responseText); }
  };
  xhr.send(q);
}

function SaveGlifetri(prms={}) {
  if(Family=='conway') {
    prms['family_id'] = 1;
    var notaset = '';
    var notas = ConwayNotaset(Ruleset);
    for(z in notas) notaset += (notaset?',':'') + notas[z];
    prms['notaset'] = notaset;
  }
  else if(Family=='langton') {
    prms['family_id'] = 2;
    prms['notaset'] = '';
  }
  
  prms['mutaset'] = EncodeMutaStr(Mutas);
  prms['rseed'] = Rseed;
  prms['fseed'] = Fseed;
  prms['stopped_nturn'] = nturn;
  prms['records'] = JSON.stringify(rec[S1]);
  prms['context'] = window.location.search;
  
  var q = '';
  for(var k in prms) {
    q += (q?'&':'') + k + '=' + encodeURIComponent(prms[k]);
  }
    
  XHRsave3(q);
  
  saved = true;
}

// DRAW RULES ////////////////////////////////////////////////////////////////

var drawRzoom = 1;

if(cfg.drawrules) {
  var sdivttl0 = document.createElement('div');
  sdivttl0.textContent = "physics (rules):";
  sdivttl0.style.margin = '10px 0 0 0';
  sdiv.appendChild(sdivttl0);

  var rcnv = document.createElement('canvas');
  sdiv.appendChild(rcnv);
  rcnv.width  = drawRzoom * 5 * (2 * 4 * 9 + 1) * FD;  rcnv.style.width  = rcnv.width  + 'px';
  rcnv.height = drawRzoom * 300 * RB;                  rcnv.style.height = rcnv.height + 'px';
  rcnv.style.backgroundColor = '#ccc';
  rctx = rcnv.getContext('2d');
}

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
    s += '<br>z='+z+':<br>';
    for(var r in sum[z]) {
      s += 'r'+r+'='+sum[z][r]+', ';
    }
  }
  var sdivttl1 = document.createElement('div');
  sdivttl1.innerHTML = s;
  sdivttl1.style.margin = '10px 0 0 0';
  sdiv.appendChild(sdivttl1);
  
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

// CALC ORGA ////////////////////////////////////////////////////////////////
// orga = organized structures, potential ogranisms

var Orga = [];
var NeibCache = {};

function OrgaGetNeib(x, y, z) {
  var bxy = 0;
  for(var k in RG) {
    if(RG[k][2]!=0) continue;  // considering only same-layer neibs (last 2 digits skipped)
    var cell = GetCell(x+RG[k][0], y+RG[k][1], z+RG[k][2]); // we inaccurately disregard torus-shape here
    var v = cell ? cell.a : 0;
    bxy = bxy * RB + v;
  }
  
  var b = bxy * RB * RB;  // adding 2 more digits to fit Moore's standard neibs
  
  var minb = 0;  // gonna look for minimum among equiv neibs
  if(NeibCache[b]) {
    minb = NeibCache[b];
  }
  else {
    for(var rule in EquivNeibs(b)) {
      var bb = NeibInt4Str(rule);
      if(!minb) minb = bb;
      NeibCache[bb] = minb;
    }
  }
  
  var minbxy = floor(minb / RB / RB);  // cutting last 2 digits again;
  
  return minbxy;
}

function CountOrgas(z) {
  Orga[z] = {};
  for(var x=0; x<FW; x++) {
    for(var y=0; y<FH; y++) {
      var b = OrgaGetNeib(x, y, z);
      if(!Orga[z][b]) Orga[z][b] = 0;
      Orga[z][b] ++;
    }
  }
}

// FOURIER TRANSFORM ////////////////////////////////////////////////////////////////

var DFT = {};
DFT.NW = floor(Math.log2(FW));  DFT.W = Math.pow(2, DFT.NW);
DFT.NH = floor(Math.log2(FH));  DFT.H = Math.pow(2, DFT.NH);

DFT.TX = new Array(2);
DFT.TX[0] = CreateTexture(FW, FH, FD, 1);
DFT.TX[1] = CreateTexture(FW, FH, FD, 1);

DFT.FB = new Array(2);
DFT.FB[0] = CreateFramebuffer(DFT.TX[0], FD);
DFT.FB[1] = CreateFramebuffer(DFT.TX[1], FD);

DFT.FillFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp usampler3D u_fieldtexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  void main() {
    vec4 colors[`+FD+`];
    for(int layer=0; layer<`+FD+`; layer++) {
      vec4 color = vec4(0);
      uvec4 pixel =  texelFetch(u_fieldtexture, ivec3(v_texcoord, layer), 0);
      color.r = pixel.a>200u ? 1. : float(pixel.a) / 255.;
      colors[layer] = color;
    }
    glFragColor[0] = colors[0];
    ` + (FD>1 ? `glFragColor[1] = colors[1];` : ``) + `
    ` + (FD>2 ? `glFragColor[2] = colors[2];` : ``) + `
    ` + (FD>3 ? `glFragColor[3] = colors[3];` : ``) + `
  }
`;
DFT.FillProgram = createProgram4Frag(gl, DFT.FillFragmentShaderSource, ["a_position", "u_fieldtexture"]);

DFT.FilterFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp sampler3D u_ffttexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  void main() {
    vec4 colors[`+FD+`];
    for(int layer=0; layer<`+FD+`; layer++) {
      vec4 f =  texelFetch(u_ffttexture, ivec3(v_texcoord, layer), 0);
      float x = (v_texcoord.x - `+FW+`./2.) / `+FW+`.;
      float y = (v_texcoord.y - `+FH+`./2.) / `+FH+`.;
      vec4 color = f;
      float r2 = f.x*f.x+f.y*f.y;
      if(r2<0.07 || abs(x*y)<0.001 || (x*x+y*y)<0.03) color = vec4(0);
      else color *= 1.7;
      colors[layer] = color;
    }
    glFragColor[0] = colors[0];
    ` + (FD>1 ? `glFragColor[1] = colors[1];` : ``) + `
    ` + (FD>2 ? `glFragColor[2] = colors[2];` : ``) + `
    ` + (FD>3 ? `glFragColor[3] = colors[3];` : ``) + `
  }
`;
DFT.FilterProgram = createProgram4Frag(gl, DFT.FilterFragmentShaderSource, ["a_position", "u_ffttexture"]);

DFT.CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  uniform highp sampler3D u_ffttexture;
  uniform int u_layer;
  uniform int u_invrs;
  uniform int u_horiz;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  out vec4 glFragColor[`+FD+`];
  
  #define PI 3.14159265359
  #define FW `+FW+`
  #define fW `+FW+`.
  #define FH `+FH+`
  #define fH `+FH+`.
  #define fW2 `+round(FW/2)+`.
  #define fH2 `+round(FH/2)+`.
  
  vec2 DFT1(vec2 xy, int layer) {
    float dvapi = 2. * PI;
    if(u_invrs>0) dvapi = -dvapi;
    vec2 sum = vec2(0, 0);
    if(u_horiz>0) {
      for(int i=0; i<FW; i++) {
        vec4 f = texelFetch(u_ffttexture, ivec3(i, xy.y, layer), 0);
        float ii = float(i) + 0.5;
        float a = dvapi * (xy.x-fW2) * (ii-fW2) / fW;
        float c = cos(a);
        float s = sin(a);
        sum.x += f.x * c + f.y * s;
        sum.y += f.y * c - f.x * s;
      }
      sum /= sqrt(fW);
    }
    else {
      for(int j=0; j<FH; j++) {
        vec4 f = texelFetch(u_ffttexture, ivec3(xy.x, j, layer), 0);
        float jj = float(j) + 0.5;
        float a = dvapi * (xy.y-fH2) * (jj-fH2) / fH;
        float c = cos(a);
        float s = sin(a);
        sum.x += f.x * c + f.y * s;
        sum.y += f.y * c - f.x * s;
      }
      sum /= sqrt(fH);
    }
    return sum;
  }
  
  void main() {
    vec2 dft = DFT1(v_texcoord, u_layer);
    vec4 color = vec4(dft, 0, 1);
    `+(function() {
      var ret = '';
      for(var z=0; z<FD; z++) ret += 'glFragColor['+z+'] = u_layer=='+z+' ? color : vec4(0);\n    ';
      return ret;
    })()+`
  }
`;
DFT.CalcProgram = createProgram4Frag(gl, DFT.CalcFragmentShaderSource, ["a_position", "u_ffttexture", "u_layer", "u_invrs", "u_horiz"]);

DFT.ShowFragmentShaderSource = `
  precision mediump float;
  
  uniform highp sampler3D u_ffttexture;
  
  in vec2 v_texcoord;  // [0..FW, 0..FH]
  
  out vec4 color;
  
  #define PI 3.14159265359
  
  void main() {
    ivec2 xy = ivec2(gl_FragCoord.xy);  // canvas coords
    ivec3 texcoord = ivec3(xy, 0);
    ` + (FD>1 ? `
      if(xy.x>=`+FW+`) { texcoord.z += 1;  texcoord.x = xy.x % `+FW+`; }
      if(xy.y>=`+FH+`) { texcoord.z += 2;  texcoord.y = xy.y % `+FH+`; }
    ` : ``) + `
    
    vec4 texel = texelFetch(u_ffttexture, texcoord, 0);
    vec2 dft = texel.rg;
    color.r = 0.;
    color.g = sqrt(dft.x*dft.x + dft.y*dft.y);  if(color.g<0.2) color.g = 0.;  else color.g = log2(1. + color.g);
    color.b = dft.x!=0. ? (0.5 + atan(dft.y, dft.x)/(2.*PI)) : (dft.y>0. ? 0.75 : 0.25);  color.b /= 4.;
    color.a = 1.;
  }
`;
DFT.ShowProgram = createProgram4Frag(gl, DFT.ShowFragmentShaderSource, ["a_position", "u_ffttexture"]);

DFT.filled = false;
function FillFT() {
  gl.useProgram(DFT.FillProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, DFT.FB[0]);
  var color_attachments = [];
  for(var z=0; z<FD; z++) color_attachments[z] = gl.COLOR_ATTACHMENT0 + z;
  gl.drawBuffers(color_attachments);
  
  gl.viewport(0, 0, FW, FH);
  
  gl.activeTexture(gl.TEXTURE0 + T0);
  gl.bindTexture(gl.TEXTURE_3D, Textures[T0]);
  gl.uniform1i(DFT.FillProgram.location.u_fieldtexture, T0);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  ShowFT();
  
  DFT.filled = true;
}

function FilterFT(back=0) {
  var fb, tx;
  if(!back) { fb = 1;  tx = 0; }
  else      { fb = 0;  tx = 1; }
  
  gl.useProgram(DFT.FilterProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, DFT.FB[fb]);
  var color_attachments = [];
  for(var z=0; z<FD; z++) color_attachments[z] = gl.COLOR_ATTACHMENT0 + z;
  gl.drawBuffers(color_attachments);
  
  gl.viewport(0, 0, FW, FH);
  
  gl.activeTexture(gl.TEXTURE0 + (FD+2) + tx);
  gl.bindTexture(gl.TEXTURE_3D, DFT.TX[tx]);
  gl.uniform1i(DFT.FilterProgram.location.u_ffttexture, (FD+2) + tx);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  if(!back) FilterFT(1);
  else ShowFT();
}

function CalcDFT(layer=0, invrs=0, horiz=0) {
  var fb, tx;
  if(!horiz) { fb = 1;  tx = 0; }
  else       { fb = 0;  tx = 1; }
  
  gl.useProgram(DFT.CalcProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, DFT.FB[fb]);
  var color_attachments = [];
  for(var z=0; z<FD; z++) {  // rendering only 1 layer of 3D texture per call
    color_attachments[z] = (z==layer ? gl.COLOR_ATTACHMENT0 + z : gl.NONE);
  }
  gl.drawBuffers(color_attachments);
  
  gl.viewport(0, 0, FW, FH);
  
  gl.activeTexture(gl.TEXTURE0 + (FD+2) + tx);
  gl.bindTexture(gl.TEXTURE_3D, DFT.TX[tx]);
  gl.uniform1i(DFT.CalcProgram.location.u_ffttexture, (FD+2) + tx);
  
  gl.uniform1i(DFT.CalcProgram.location.u_layer, layer);
  gl.uniform1i(DFT.CalcProgram.location.u_invrs, invrs);
  gl.uniform1i(DFT.CalcProgram.location.u_horiz, horiz);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
  
  if(!horiz) CalcDFT(layer, invrs, 1);  // run horizontal after vertical: full 2D-FT = sum of two 1D-FT (rows and columns)
}

function ShowFT(tx=0) {
  gl.useProgram(DFT.ShowProgram);
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, null);
  
  gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
  
  gl.activeTexture(gl.TEXTURE0 + (FD+2) + tx);
  gl.bindTexture(gl.TEXTURE_3D, DFT.TX[tx]);
  gl.uniform1i(DFT.ShowProgram.location.u_ffttexture, (FD+2) + tx);
  
  gl.drawArrays(gl.TRIANGLE_FAN, 0, 4);
}

function StatDFT(invrs=0) {
  Pause(1);
  
  if(!DFT.filled) FillFT();
  
  for(var z=0; z<FD; z++) CalcDFT(z, invrs);
  
  ShowFT(0);
}

// INIT STATS ////////////////////////////////////////////////////////////////

var nGen = 1;  // number of generations produced in random-search

function InitStats() {
  nturn = 0;
  nturn0 = 0;
  nshow = 0;
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
  
  sfps += 'turn = ' + nturn + ' | ';
  sfps += 'shown = ' + nshow + ' | ';
  
  var date1 = new Date;
  var ms = (date1 - date0) / (nturn - nturn0);
  date0 = date1;  nturn0 = nturn;
  sfps += 'fps = ' + round(1000 / ms) + '<br>';
  if(nGen>1) sfps = 'nGen = ' + nGen + ' | ' + sfps;
  
  if(sfps) document.getElementById('stxtfps').innerHTML = sfps;
  
  if(!force && (cfg.paused || cfg.pausestat)) return;
  
  var sstat = '';
  
  var x, y, z;
  
  var specstat = [];  // stat for graph
  
  var qd = 10, qx, qy, qq = []; // grid of squares qd*qd with coordinates (qx,qy) is to measure how species spread
  var qw = floor(FW / qd), qh = floor(FH / qd);  // assuming FW%qd==0 && FH%qd==0
  var nqtotal = qw * qh;  // total number of squares
  
  rec[S1].zero();
  
  gl.bindFramebuffer(gl.FRAMEBUFFER, Framebuffers[T0]);  // preparing to fetch Field data from GPU
  for(var z=0; z<FD; z++) {
    gl.readBuffer(gl.COLOR_ATTACHMENT0 + z);  // z-plane of the Field is a buffer attached to a texture point
    gl.readPixels(0, 0, FW, FH, gldata_Format, gldata_Type, F, 4 * FW * FH * z);  // reading pixels of layer=z to F[z]
  }
  
  for(var z=0; z<FD; z++) {
    //if(cfg.orga && nturn>0 && z==1) CountOrgas(z);
    
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
  if(Orga.length) {
    var s = '', t = '';
    for(var z in Orga) {
      t = '';
      for(var b in Orga[z]) {
        t += '<td>'+NeibStr4Int(b)+'<br>'+Orga[z][b]+'</td>';
      }
      s += '<tr><td>z='+z+'</td>'+t+'</tr>';
    }
    document.getElementById('stxtlog').innerHTML += '<table>'+s+'</table>';
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
      </tr>
    `;
  }
  sstat += `
    <table cellspacing=0 id='glifeStatTB'>
      <tr>
        <th>z</th><th>livecells</th><th>nqfilld</th><th>nqchngd</th>
        <th>fillin</th><th>spread</th><th>variat</th>
        <th>icehsh</th><th>flags</th>
      </tr>
      ` + stb + `
    </table>
  `;
  
  if(cfg.autore || cfg.rerun) {
    if((!interesting_z && nturn>500) || (nturn>=5000)) {  // if no interesting planes left - restart
      SaveGlifetri({'stopped_at':failed_at});
      nGen ++;
      if(nGen>300 || cfg.rerun) {
        window.location.reload(true);  // reloading page sometimes to refresh seed for rand32 to avoid cycles
      }
      else {
        ReInitSeeds();
        Init();
      }
    }
  }
  else if(nturn>=10000 && !saved) {  // saving all long-runned cases
    SaveGlifetri({'stopped_at':'x'});
  }
  
  if(S1==1) { S1 = 0;  S0 = 1; } else { S1 = 1;  S0 = 0; }  // flipping time for Stats
  
  if(sstat)  document.getElementById('stxtstat').innerHTML = sstat;
  
}

//  ////////////////////////////////////////////////////////////////