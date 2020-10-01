// GEOMS ////////////////////////////////////////////////////////////////
// geom = geometry of neighborhood

if(Rgeom=='1+8+2') {  // Moore2D + vonNeumann1D
  var RG = [  // neighborhood geometry coords (dx,dy,dz)
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
else if(Rgeom=='1+4+2') {  // vonNeumann2D + vonNeumann1D
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

function NeibInt4Str(rule) {
  return parseInt(rule, RB);
}

function NeibRotate8(r) {
  var ret = [];
  for(var i=0; i<8; i++) {
    ret[i] = r[(i+1)%8];
  }
  return ret;
}

function NeibMirror8(r) {
  return [r[7], r[6], r[5], r[4], r[3], r[2], r[1], r[0]];
}

function NeibRotate4v(r) {  // incorrect !!!
  var ret = [];
  for(var i=0; i<8; i++) {
    var v = r[(i+2)%8];  // space rotation by pi/4
    if(v>0) v = v % 4 + 1;  // vector value rotation (only for non-zero values)
    ret[i] = v;
  }
  return ret;
}

function EquivNeibs(b) {
  var ret = {};
  
  var rule = NeibArr4Int(b);
  var rr = rule.slice(1, -2);
  var l = rr.length;  // must be 8
  
  var pfx = ''+rule[0];
  var sfx = ''+rule[9]+rule[10];
  
  if(Rsymm=='8p') {  // rotating by pi/4 (8 cells) plus parity
    for(var i=0; i<l; i++) {
      rr = NeibRotate8(rr);
      ret[pfx+rr.join('')+sfx] = 1;
    }
    
    rr = NeibMirror8(rr);
    ret[pfx+rr.join('')+sfx] = 2;
    
    for(var i=0; i<l; i++) {
      rr = NeibRotate8(rr);
      ret[pfx+rr.join('')+sfx] = 3;
    }
  }
  else if(Rsymm=='4v') {  // rotating by pi/2 with vector (rotating with space rotation) values
    // not working !!!
    for(var i=0; i<l; i++) {
      rr = NeibRotate4v(rr);
      ret[pfx+rr.join('')+sfx] = 1;
    }
  }
  
  var ordered = {};
  Object.keys(ret).sort().forEach(function(key) { ordered[key] = ret[key]; });
  
  return ordered;
}
//if(Rsymm=='8p') console.log(EquivNeibs(NeibInt4Str('00001011100')));
//if(Rsymm=='4v') console.log(EquivNeibs(NeibInt4Str('00100000000')));

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
  return Object.keys(uniqrules);
}
//if(Rsymm=='8p') console.log(RL, UniqNeibs());


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
for(var z=0; z<FD; z++) RulesTexture[z] = CreateTexture2D(Rtx, Rty);

function SetRulesTexture() {
  for(var z=0; z<FD; z++) SetTexture2D(2 + z, RulesTexture[z], Rtx, Rty, R[z]);
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
  if(notaset.indexOf('[')>-1) {
    var planes = notaset.replace('],]', '').split('],[');
    for(var z in planes) {
      ret[z] = planes[z].replace(/[\[\]]/g, '');
    }
  }
  else if(notaset=='random') {  // generating random rules (FD must be set)
    ret = ConwayRandomRules();
  }
  else {  // taking rules from GET as a single named rule
    ret = ConwayNotaset(NamedRules[notaset]) || [];
  }
  return ret;
}

function ConwayRandomRules() {
  var rules = [];
  
  function ConwayRandomStrand(min=0) {
    var ret = '';
    var l = rnd(1,9);
    var r = [];
    for(var j=0; j<l; j++) {
      var d = rnd(min, 9);
      r[d] = 1;
    }
    for(var k in r) {
      if(r[k]) ret += k.toString();
    }
    return ret;
  }
  
  for(var z=0; z<FD; z++) {
    rules[z] = ConwayRandomStrand(1) + ':' + ConwayRandomStrand(1);
  }
  
  var s = '';  for(z in rules) s += '  [' + rules[z] + '],\n';
  document.getElementById('stxtlog').innerHTML += '<pre>[\n' + s + '],</pre>';
  
  return rules;
}

function SetConwayRules(notaset) {
  var notas = ConwayNotaset(notaset);
  
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
         if(v) {}  // if already set - don't change
    else if(r[5]!=0) v = 0;  // no upper for top
    else if(r[6]>=2) v = 0;  // only 0 and 1 at ground layer
    else if(r[1]==1) v = r[6]==0 ? 2 : 4;
    else if(r[2]==2) v = r[6]==0 ? 3 : 1;
    else if(r[3]==3) v = r[6]==0 ? 4 : 2;
    else if(r[4]==4) v = r[6]==0 ? 1 : 3;
    else              v = 0;
    if(v) SetRule(z, b, v);
  }
  console.timeEnd('SetLangtonRules');
}

// RANDOM MUTATIONS ////////////////////////////////////////////////////////////////

function RandomRmutations(n=0) {
  console.log(n + ' random rule mutations');
  for(var i=0; i<n; i++) {
    var z = rnd(0, FD);
    var b = rnd(0, RL);
    var v0 = GetRule(z, b);
    var v = (v0 + 1) % RB;
    SetRule(z, b, v);
    if(i<100) console.log(z+':'+NeibStr4Int(b)+':'+v0+'->'+v);
  }
  /*
  for(var i=0; i<-3; i++) {
    var z = rnd(0, FD);
    var b0 = rnd(0, RL);
    for(var rule in EquivNeibs(b0)) {
      var b = NeibInt4Str(rule);
      SetRule(z, b, GetRule(z, b) > 0 ? 0 : 1);
    }
    console.log(z+':'+NeibStr4Int(b0));
  }
  */
}

// INIT RULES ////////////////////////////////////////////////////////////////

function InitRules() {
  for(var z=0; z<FD; z++) R[z].fill(0);
  
  if(Family=='langton') {
    SetLangtonRules();
  }
  else if(Family=='conway') {
    SetConwayRules(Ruleset);
  }
  
  //RandomRmutations(floor(RL/10));
  
  SetRulesTexture();
}
