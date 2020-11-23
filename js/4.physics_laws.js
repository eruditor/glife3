// GEOMS ////////////////////////////////////////////////////////////////
// geom = geometry of neighborhood

// neighborhood geometry coords (dx,dy,dz)
if(Rgeom==18) {  // Moore2D
  var RG = [
    [ 0,  0,  0],  //  0 = self
    [-1, -1,  0],  //  1
    [ 0, -1,  0],  //  2 = up
    [ 1, -1,  0],  //  3
    [ 1,  0,  0],  //  4 = right
    [ 1,  1,  0],  //  5
    [ 0,  1,  0],  //  6 = down
    [-1,  1,  0],  //  7
    [-1,  0,  0],  //  8 = left
  ];
}
else if(Rgeom==182) {  // Moore2D + vonNeumann1D
  var RG = [
    [ 0,  0,  0],  //  0 = self
    [-1, -1,  0],  //  1
    [ 0, -1,  0],  //  2 = up
    [ 1, -1,  0],  //  3
    [ 1,  0,  0],  //  4 = right
    [ 1,  1,  0],  //  5
    [ 0,  1,  0],  //  6 = down
    [-1,  1,  0],  //  7
    [-1,  0,  0],  //  8 = left
    [ 0,  0,  1],  //  9 = upper
    [ 0,  0, -1],  // 10 = lower
  ];
}
else if(Rgeom==142) {  // vonNeumann2D + vonNeumann1D
  var RG = [
    [ 0,  0,  0],  //  0 = self
    [ 0, -1,  0],  //  1 = up
    [ 1,  0,  0],  //  2 = right
    [ 0,  1,  0],  //  3 = down
    [-1,  0,  0],  //  4 = left
    [ 0,  0,  1],  //  5 = upper
    [ 0,  0, -1],  //  6 = lower
  ];
}

var RC = RG.length;  // number of cells in neighborhood that affects current cell's state (length of neib encoding)

// NEIBS ////////////////////////////////////////////////////////////////
// neib = configuration of cell states in neighborhood geometry

// each cell in neighborhood geometry can take RB values
var RL = Math.pow(RB, RC);  // total number of all possible neibs (length of physics rule encoding)

function NeibArr4Int(b) {
  var ret = new Array(RC);
  for(var i=RC-1; i>=0; i--) {
    var d = b % RB;
    b -= d;
    b /= RB;
    ret[i] = d;
  }
  return ret;
}

function NeibStr4Int(b) {
  var ret = '';
  for(var i=RC-1; i>=0; i--) {
    var d = b % RB;
    b -= d;
    b /= RB;
    ret = ''+d+ret;
  }
  return ret;
  //return b.toString(RB).padStart(RC, "0");  // it's slower!
}

function NeibInt4Str(str) {
  return parseInt(str, RB);
}

// NEIB SYMMETRIES ////////////////////////////////////////////////////////////////
// we mostly consider isotropic (rotation-invariant) rules
// conway-notation symmetry (which are more than isotropic) are hard-coded in SetConwayRules

function ArrRotate(r, l) { var ret = [];  for(var i=0; i<l; i++) ret[i] = r[(i+1)%l];  return ret; }
function ArrMirror(r, l) { var ret = [];  for(var i=0; i<l; i++) ret[i] = r[l-i-1];    return ret; }

function ValueVectorRotate(v, l) { return v>0 ? (1 + (v-1 + l-1) % l) : 0; }
function ValueVectorMirror(v, l) { return v>0 ? (1 + ((l-1) - (v-1))) : 0; }

function ArrVectorRotate(r, l) {
  var ret = [];
  for(var i=0; i<l; i++) {
    var v = r[(i+1)%l];  // space rotation
    v = ValueVectorRotate(v, l);
    ret[i] = v;
  }
  return ret;
}

function AllScalarRotations(rr, l) {
  var ret = {};
  ret[rr.join('')] = 1;
  if(Rsymm==85) {
    for(var i=1; i<l; i++) {
      rr = ArrRotate(rr, l);
      ret[rr.join('')] = 1;
    }
    
    rr = ArrMirror(rr, l);
    ret[rr.join('')] = 1;
    
    for(var i=1; i<l; i++) {
      rr = ArrRotate(rr, l);
      ret[rr.join('')] = 1;
    }
  }
  return ret;
}

function SliceNeib(rule) {
  var r0 = rule[0];
  var pfx = ''+rule[0];
  if(Rgeom>100) {  // 3D case
    var rr = rule.slice(1, -2);
    var l = rr.length;
    var sfx = ''+rule[l+1]+rule[l+2];
  }
  else {  // 2D case
    var rr = rule.slice(1);
    var l = rr.length;
    var sfx = '';
  }
  return {'r0':r0, 'pfx':pfx, 'rr':rr, 'l':l, 'sfx':sfx};
}

function EquivNeibs(b) {
  var ret = {};
  
  var rule = NeibArr4Int(b);
  let {pfx, rr, l, sfx} = SliceNeib(rule);
  
  if(Rsymm==85) {  // rotating by pi/4 (8 cells) plus parity
    var asr = AllScalarRotations(rr, l);
    for(var k in asr) ret[pfx+k+sfx] = asr[k];
  }
  else {
    ret[pfx+rr.join('')+sfx] = 1;
  }
  
  var ordered = {};
  Object.keys(ret).sort().forEach(function(key) { ordered[key] = ret[key]; });
  
  return ordered;
}

function EquivRules(b, v) {
  var ret = {};
  
  var rule = NeibArr4Int(b);
  let {r0, rr, l, sfx} = SliceNeib(rule);
  var vv = v;
  var kk = '';
  
  if(Rsymm==47) {  // rotating by pi/2 with vector (rotating with space rotation) values
    for(var i=0; i<l; i++) {
      r0 = ValueVectorRotate(r0, l);
      rr = ArrVectorRotate(rr, l);
      vv = ValueVectorRotate(vv, l);
      kk = '' + r0 + rr.join('') + sfx;
      if(ret[kk]!==undefined && ret[kk]!=vv) return {};  // forbidden (symmetry violating) rule
      ret[kk] = vv;
    }
  }
  
  var ordered = {};
  Object.keys(ret).sort().forEach(function(key) { ordered[key] = ret[key]; });
  
  return ordered;
}

function UniqNeibs() {
  console.time('UniqNeibs');
  var uniqrules = {}, allrules = {};
  for(var b=0; b<RL; b++) {
    var rule0 = NeibStr4Int(b);
    if(allrules[rule0]) continue;
    uniqrules[rule0] = 1;
    for(var rule in EquivNeibs(b)) {
      allrules[rule] = 1;
    }
  }
  console.timeEnd('UniqNeibs');
  return arsort_keys(uniqrules);  // Object.keys(uniqrules);
}
//if(Rsymm==85) console.log(RL, UniqNeibs());
//if(Rsymm==47) console.log(RL, UniqNeibs());

// RANDOM MUTATIONS ////////////////////////////////////////////////////////////////
// mutation = rule (b,v0) of some neib (b) has it's value (v) changed

var Mutas = [];

function GenMutas(n=0) {
  if(Rseed==1936799038) return DecodeMutaStr(`0f0L0X1o1r2r2V2@3X4I4L4@5p6Xbpbvb@d@wrwJx9yryvyEyXzvzXALAZBKB@CvHtLX\n0V0Z1d1q1X2u2G2X2@6t7WaVaZbtdVdWfZwVw@yry@zVAHBoBqBvBGCoCqHpHV\n080q0G0@1d1p4I4Z5p5t5V5X5Z6tbWwHwLx9xVx@yqyvyZzZBXCXCZJ@LW`);
  
  var mutas = [];
  for(var z=0; z<FD; z++) mutas[z] = {};
  
  for(var i=0; i<n; i++) {
    var z  = rndR(0, FD);  if(Family=='Langton') z = 1;  if(Family=='Conway') z = 0;
    var b  = rndR(0, RL);
    
    if(typeof mutas[z][b] !== 'undefined') continue;  // no strict duplicates
    
    var minb = 0;  // minimal b among equiv rules
    var eqr = Family=='Langton' ? EquivRules(b, 1) : EquivNeibs(b);
    for(var rule in eqr) {
      minb = NeibInt4Str(rule);
      break;
    }
    if(typeof mutas[z][minb] !== 'undefined') continue;  // no equiv duplicates
    
    var v  = rndR(0, RB);
    var v0 = GetRule(z, minb);
    if(v==v0) continue;  // same value
    
    mutas[z][minb] = v;
  }
  
  /*
  var log = '';
  for(var z in mutas) {
    for(var b in mutas[z]) {
      var v = mutas[z][b];
      var m = b * RB + v;
      log += '\n'+z+':'+b+','+m+'('+myBase64encode(m)+')'+NeibStr4Int(b)+'->'+v;
    }
  }
  console.log(log);
  */
  
  return mutas;
}

function SetMutaRules(mutas) {
  var n = 0;
  for(var z in mutas) {
    for(var b in mutas[z]) {
      var v = mutas[z][b];
      
      if(Family=='Langton') {
        var eqr = EquivRules(b, v);
        for(var rule in eqr) {
          var bb = NeibInt4Str(rule);
          var vv = eqr[rule];
          SetRule(z, bb, vv);
        }
      }
      else if(Family=='Conway3D') {
        for(var rule in EquivNeibs(b)) {
          var bb = NeibInt4Str(rule);
          SetRule(z, bb, v);
        }
      }
      else if(Family=='Conway') {
        for(var rule in EquivNeibs(b)) {
          var bb = NeibInt4Str(rule);
          SetRule(z, bb, v);
        }
      }
      else {
        console.log('No mutation formula for this Family');
      }
      
      n ++;
    }
  }
  console.log(n + ' random rule mutations:\n' + EncodeMutaStr(mutas));
  return mutas;
}

function EncodeMutaStr(mutas) {  // myBase64-encoded Mutas
  var ret = '';
  for(var z in mutas) {
    if(ret) ret += '\n';
    for(var b in mutas[z]) {
      var v = mutas[z][b];
      var m = b * RB + v;
      ret += myBase64encode(m);
    }
  }
  return ret;
}

function DecodeMutaStr(mutastr) {
  var ret = [];
  var ts = mutastr.split('\n');
  for(var z in ts) {
    ret[z] = {};
    var ms = myBase64decode(ts[z]);
    for(var i in ms) {
      var m = ms[i];
      var v = m % RB;
      var b = (m - v) / RB;
      ret[z][b] = v;
    }
  }
  return ret;
}

// RANDOM NOTASET ////////////////////////////////////////////////////////////////

function ConwayRandomRules() {
  function ConwayRandomStrand(min=0) {
    var ret = '';
    var l = rndR(1,9);
    var r = [];
    for(var j=0; j<l; j++) {
      var d = rndR(min, 9);
      r[d] = 1;
    }
    for(var k in r) {
      if(r[k]) ret += k.toString();
    }
    return ret;
  }
  
  var rules = [];
  for(var z=0; z<FD; z++) {
    rules[z] = ConwayRandomStrand(1) + ':' + ConwayRandomStrand(1);
  }
  
  var notaset = '';  for(z in rules) notaset += (notaset?',':'') + rules[z];
  
  divrules.innerHTML += '<pre>' + notaset + '</pre>';
  
  return notaset;
}

function RandomNotaset() {
  var notaset = '';
  if(Family=='Langton') {
    // todo
  }
  else if(Family=='Conway' || Family=='Conway3D') {
    notaset = ConwayRandomRules();
  }
  return notaset;
}

// RULES ARRAY ////////////////////////////////////////////////////////////////
// rule = correspondence: neib -> cell_state (RB^RL)

var RX = gl.getParameter(gl.MAX_TEXTURE_SIZE);  // max texture length (MAX_3D_TEXTURE_SIZE for 3D)
var Rtx = RL, Rty = 1;
if(RL>RX) { Rtx = RX;  Rty = Math.ceil(RL/RX); }
if(Rty>RX) alert('max_texture_size is not sufficient to store rules ('+Rty+'>'+RX+')');

var R = [];
for(var z=0; z<FD; z++) R[z] = new jsdata_Array(4 * Rtx * Rty);

function SetRule(z, b, v) {
  R[z][4*b + 3] = v;  // alpha-channel for texture value
}

function GetRule(z, b) {
  return R[z][4*b + 3];
}

// RULES TEXTURE ////////////////////////////////////////////////////////////////

var RulesTexture = [];
for(var z=0; z<FD; z++) RulesTexture[z] = CreateTexture(Rtx, Rty);

function SetRulesTexture() {
  for(var z=0; z<FD; z++) SetTexture(2 + z, RulesTexture[z], R[z], Rtx, Rty);
}

// RULES NOTATION ////////////////////////////////////////////////////////////////
// nota = rules encoded in short symbolic notation (neib-setting specific)
// notaset = set of notas for all FD layers

// CONWAY RULES ////////////////////////////////////////////////////////////////
// each Rule ('3:23') encodes two DNA Strands of Born and Surv rules
// each Strand consists of 9 Genes
// each Gene is a bit (0/1)

function ConwayNotaset(notaset) {
  var ret = [];
  if(notaset.indexOf(':')>-1) {
    ret = notaset.split(',');
  }
  return ret;
}

function ConwayStrand(r) {
  var strand = [0, 0, 0, 0, 0, 0, 0, 0, 0];
  var ar = r.split('');
  if(ar) for(k in ar) strand[ar[k]] = 1;
  return strand;
}

function ConwayList4Nota(rule) {
  var b, s;
  [b, s] = rule.split(':');
  return [ConwayStrand(b), ConwayStrand(s)];
}

function SetConway2DRules(notaset) {
  var notas = ConwayNotaset(notaset);  console.log(notas);
  
  for(var z=0; z<FD; z++) {
    var conwayar = ConwayList4Nota(notas[z]);
    for(var b=0; b<RL; b++) {
      var v = 0, nope = false;
      var r = NeibArr4Int(b);
      if(true) {
        var sum = 0;
        for(var i=1; i<=8; i++) {
               if(r[i]==1) { sum ++; }
          else if(r[i]!=0) { nope = true;  break; }
        }
        var vv = conwayar[r[0]];
        v = vv ? vv[sum] : 0;
      }
      if(nope) v = 0;
      
      SetRule(z, b, v);
    }
  }
}

function SetConway3DRules(notaset) {
  var notas = ConwayNotaset(notaset);  console.log(notas);
  
  for(var z=0; z<FD; z++) {
    var conwayar = ConwayList4Nota(notas[z]);
    for(var b=0; b<RL; b++) {
      var v = 0, nope = false;
      var r = NeibArr4Int(b);
      if(r[ 9]!=0 && r[ 9]!=1) nope = true;
      if(r[10]!=0 && r[10]!=1) nope = true;
           if(FD>1 && z==FD-1 && r[ 9]==1) v = 0;  // no uppers for top-layer
      else if(FD>1 && z==0    && r[10]==1) v = 0;  // no lowers for bottom-layer
      else if(FD>1 && z!=FD-1 && r[ 9]==1 && r[0]==1) v = 0;  // die, eaten by carnivore
      else if(FD>1 && z!=0    && r[10]==0 && r[0]==0) v = 0;  // can't be born without food below
      else if(FD==1 && (r[9]!=0 || r[10]!=0)) v = 0;
      else {
        var sum = 0;
        for(var i=1; i<=8; i++) {
               if(r[i]==1) { sum ++; }
          else if(r[i]!=0) { nope = true;  break; }
        }
        var vv = conwayar[r[0]];
        v = vv ? vv[sum] : 0;
      }
      if(nope) v = 0;
      
      SetRule(z, b, v);
    }
  }
}

// LANGTON RULES ////////////////////////////////////////////////////////////////
// direction of Ant's movement is encoded in cell's values: 1=down, 2=left, 3=up, 4=right
// these values are "vector", they rotate with space rotation

function SetLangtonRules() {
  console.time('SetLangtonRules');
  var v, z;
  for(var b=0; b<RL; b++) {
    var r = NeibArr4Int(b);
    
    z = 0;  // ground level
    v = GetRule(z, b);
         if(v) {}  // if already set - don't change
    else if(r[6]!=0) v = 0;  // no lower for bottom
    else if(r[5]!=0) v = r[0]==0 ? 1 : 0;  // if upper alive = switch ground
    else             v = r[0]==0 ? 0 : 1;  // if no upper = stay same
    if(v) SetRule(z, b, v);
    
    z = 1;  // ant level
    v = GetRule(z, b);
         if(v==99) {}  // if already set - don't change
    else if(r[5]!=0) v = 0;  // no upper for top
    else if(r[6]>=2) v = 0;  // only 0 and 1 at ground layer
    else if(r[1]==1) v = r[6]==0 ? 2 : 4;
    else if(r[2]==2) v = r[6]==0 ? 3 : 1;
    else if(r[3]==3) v = r[6]==0 ? 4 : 2;
    else if(r[4]==4) v = r[6]==0 ? 1 : 3;
    else             v = 0;
    if(v) {
      if(0) {
        SetRule(z, b, v);
      }
      else {
        var eqr = EquivRules(b, v);
        for(var rule in eqr) {
          var bb = NeibInt4Str(rule);
          var vv = eqr[rule];
          SetRule(z, bb, vv);
        }
      }
    }
  }
  console.timeEnd('SetLangtonRules');
}

// INIT RULES ////////////////////////////////////////////////////////////////

function InitRules() {
  for(var z=0; z<FD; z++) R[z].fill(0);
  
  if(cfg.autore || !Notaset || Notaset=='random') {
    Notaset = RandomNotaset();
  }
  
  if(Family=='Langton') {
    SetLangtonRules();
  }
  else if(Family=='Conway') {
    SetConway2DRules(Notaset);
  }
  else if(Family=='Conway3D') {
    SetConway3DRules(Notaset);
  }
  else {
    alert('Unsupported Family');
  }
  
  if(NM>0) Mutas = SetMutaRules(GenMutas(NM));
  //console.log(Mutas);
  
  SetRulesTexture();
}

//  ////////////////////////////////////////////////////////////////