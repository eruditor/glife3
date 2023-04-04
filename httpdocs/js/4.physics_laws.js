// GEOMS ////////////////////////////////////////////////////////////////
// geom = geometry of neighborhood

// neighborhood geometry coords (dx,dy,dz)
const RG = 
  /*
   1    7 6 5      5 4
   0    8 0 4    6 0 3
  -1    1 2 3    1 2  
  */
  Rgeom==18  // Moore2D 3*3 spiral
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1
      [ 0, -1,  0],  //  2 = down
      [ 1, -1,  0],  //  3
      [ 1,  0,  0],  //  4 = right
      [ 1,  1,  0],  //  5
      [ 0,  1,  0],  //  6 = up
      [-1,  1,  0],  //  7
      [-1,  0,  0],  //  8 = left
    ]
  :
  Rgeom==182  // Moore2D spiral + vonNeumann1D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1
      [ 0, -1,  0],  //  2 = down
      [ 1, -1,  0],  //  3
      [ 1,  0,  0],  //  4 = right
      [ 1,  1,  0],  //  5
      [ 0,  1,  0],  //  6 = up
      [-1,  1,  0],  //  7
      [-1,  0,  0],  //  8 = left
      [ 0,  0,  1],  //  9 = upper
      [ 0,  0, -1],  // 10 = lower
    ]
  :
  Rgeom==14  // vonNeumann2D spiral
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [ 0, -1,  0],  //  1 = down
      [ 1,  0,  0],  //  2 = right
      [ 0,  1,  0],  //  3 = up
      [-1,  0,  0],  //  4 = left
    ]
  :
  Rgeom==142  // vonNeumann2D spiral + vonNeumann1D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [ 0, -1,  0],  //  1 = down
      [ 1,  0,  0],  //  2 = right
      [ 0,  1,  0],  //  3 = up
      [-1,  0,  0],  //  4 = left
      [ 0,  0,  1],  //  5 = upper
      [ 0,  0, -1],  //  6 = lower
    ]
  :
  Rgeom==16  // Hex2D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1 = down-left
      [ 0, -1,  0],  //  2 = down-right
      [ 1,  0,  0],  //  3 = right
      [ 1,  1,  0],  //  4 = up-right
      [ 0,  1,  0],  //  5 = up-left
      [-1,  0,  0],  //  6 = left
    ]
  :
  Rgeom==162  // Hex2D + vonNeumann1D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1 = down-left
      [ 0, -1,  0],  //  2 = down-right
      [ 1,  0,  0],  //  3 = right
      [ 1,  1,  0],  //  4 = up-right
      [ 0,  1,  0],  //  5 = up-left
      [-1,  0,  0],  //  6 = left
      [ 0,  0,  1],  //  7 = upper
      [ 0,  0, -1],  //  8 = lower
    ]
  :
  Rgeom==1816  // Moore2D 5*5 spiral
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1
      [ 0, -1,  0],  //  2 = down
      [ 1, -1,  0],  //  3
      [ 1,  0,  0],  //  4 = right
      [ 1,  1,  0],  //  5
      [ 0,  1,  0],  //  6 = up
      [-1,  1,  0],  //  7
      [-1,  0,  0],  //  8 = left
      [-2, -2,  0],  //  9
      [-1, -2,  0],  //  10
      [ 0, -2,  0],  //  11 = down2
      [ 1, -2,  0],  //  12
      [ 2, -2,  0],  //  13
      [ 2, -1,  0],  //  14
      [ 2,  0,  0],  //  15 = right2
      [ 2,  1,  0],  //  16
      [ 2,  2,  0],  //  17
      [ 1,  2,  0],  //  18
      [ 0,  2,  0],  //  19 = up2
      [-1,  2,  0],  //  20
      [-2,  2,  0],  //  21
      [-2,  1,  0],  //  22
      [-2,  0,  0],  //  23 = left2
      [-2, -1,  0],  //  24
    ]
  :
  Rgeom==25  // Moore2D 5*5 cartesian
  ?
    [
      [-2, -2,  0],  //  0
      [-1, -2,  0],  //  1
      [ 0, -2,  0],  //  2
      [ 1, -2,  0],  //  3
      [ 2, -2,  0],  //  4
      [-2, -1,  0],  //  5
      [-1, -1,  0],  //  6
      [ 0, -1,  0],  //  7
      [ 1, -1,  0],  //  8
      [ 2, -1,  0],  //  9
      [-2,  0,  0],  //  10
      [-1,  0,  0],  //  11
      [ 0,  0,  0],  //  12
      [ 1,  0,  0],  //  13
      [ 2,  0,  0],  //  14
      [-2,  1,  0],  //  15
      [-1,  1,  0],  //  16
      [ 0,  1,  0],  //  17
      [ 1,  1,  0],  //  18
      [ 2,  1,  0],  //  19
      [-2,  2,  0],  //  20
      [-1,  2,  0],  //  21
      [ 0,  2,  0],  //  22
      [ 1,  2,  0],  //  23
      [ 2,  2,  0],  //  24
    ]
  :
  Rgeom==9  // Moore2D 3*3 cartesian
  ?
    [
      [-1, -1,  0],  //  0
      [ 0, -1,  0],  //  1
      [ 1, -1,  0],  //  2
      [-1,  0,  0],  //  3
      [ 0,  0,  0],  //  4
      [ 1,  0,  0],  //  5
      [-1,  1,  0],  //  6
      [ 0,  1,  0],  //  7
      [ 1,  1,  0],  //  8
    ]
  :
  []
;

const RC = RG.length;  // number of cells in neighborhood that affects current cell's state (length of neib encoding)

var snn = 0, rgr = 0, r00 = 0, r_up = false, r_lo = false;
for(var i=0; i<RC; i++) {
  if(rgr<RG[i][0]) rgr = RG[i][0];
  if(RG[i][2]==0) {
    if(RG[i][0]==0 && RG[i][1]==0) {
      r00 = i;
    }
    else {
      snn ++;
    }
  }
  if(RG[i][0]==0 && RG[i][1]==0 && RG[i][2]== 1) r_up = i;
  if(RG[i][0]==0 && RG[i][1]==0 && RG[i][2]==-1) r_lo = i;
}

const SNN = intval(snn);  // number of cells in same-layer neighborhood (8 for Moore, 4 for vonNeumann, 24 for Moore5x5)
const RGR = rgr;  // radius of neighborhood (1 for 3*3, 2 for 5*5)
const RGD = RGR * 2 + 1;  // diameter of neighborhood (3, 5)
const RG0 = (RGD * RGD - 1) / 2;  // index of central cartesian cell = shift for RG cartesian indexing
const R00 = r00;  // index of self-cell (0,0,0)
const Rupper = r_up;
const Rlower = r_lo;

// NEIB VARS ////////////////////////////////////////////////////////////////
// neib = configuration of cell states in neighborhood geometry

// each cell in neighborhood geometry can take one of RB values
const RL =
  Mode=='MVM' || Mode=='BND' || Mode=='FLD' || Mode=='XCH' || Mode=='LFL' ? 1 : (
  Mode=='FHP' ? Math.pow(2, 9) :
  Math.pow(RB, RC)
  );  // total number of all possible neibs (length of physics rule space)
const RLv = RL * RB;  // length of rule encoding (rule space -> value)
const RLv64 = Math.ceil(Math.log(RLv) / Math.log(64));  // number of base64-digits needed to encode rule

// RULES ARRAY ////////////////////////////////////////////////////////////////
// rule = correspondence: neib -> cell_state (RB^RL)

const RX = gl.getParameter(gl.MAX_TEXTURE_SIZE);  // max texture length (MAX_3D_TEXTURE_SIZE for 3D)
var Rtx = RL, Rty = 1;
if(RL>RX) { Rtx = RX;  Rty = Math.ceil(RL/RX); }

var R = [];
if(Rty>RX) alert('max_texture_size is not sufficient to store rules ('+Rty+'>'+RX+')');
else for(var z=0; z<FD; z++) R[z] = new jsdata_Array(4 * Rtx * Rty);

function SetRule(z, b, v) {
  if(v<=255) {  // @ it was "<" (dont know why)
    R[z][4*b+3] = v;  // alpha-channel for texture value  // @ can pack it 4 times more efficiently
  }
  else {  // for partitioning CA
    for(var i=3; i>=0; i--) {
      var byte = v % 256;
      v = (v - byte) / 256;
      R[z][4*b+i] = byte;
    }
  }
}

function GetRule(z, b) {  // @todo: for partitioning
  return R[z][4*b+3];
}

// RULES TEXTURE ////////////////////////////////////////////////////////////////

var RulesTexture = [];
for(var z=0; z<FD; z++) RulesTexture[z] = CreateTexture(Rtx, Rty);

function SetRulesTexture() {
  for(var z=0; z<FD; z++) SetTexture(TT + z, RulesTexture[z], R[z], Rtx, Rty);
}

// NEIB ENCODINGS ////////////////////////////////////////////////////////////////

function NeibArr4Int(b, rc=RC) {
  var ret = new Array(rc);
  for(var i=rc-1; i>=0; i--) {
    var d = b % RB;
    b -= d;
    b /= RB;
    ret[i] = d;
  }
  return ret;
}

function NeibStr4Int(b, rc=RC) {
  var ret = '';
  for(var i=rc-1; i>=0; i--) {
    var d = b % RB;
    b -= d;
    b /= RB;
    ret = ''+d+ret;
  }
  return ret;
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

function SliceNeib(rule) {  // slices neib to: r0 = cell itself, rr = rotatable neighborhood, sfx = fixed top and bottom neighbors
  var r0 = rule[0];
  var pfx = ''+rule[0];
  if(Rgeom>100) {  // 3D case
    var rr = rule.slice(1, -2);
    var l = rr.length;
    var sfx = ''+rule[l+1]+rule[l+2];
  }
  else if(Mode=='FHP') {
    var rr = rule.slice(1, -1);
    var l = rr.length;
    var sfx = ''+rule[l+1];
  }
  else {  // 2D case
    var rr = rule.slice(1);
    var l = rr.length;
    var sfx = '';
  }
  return {'r0':r0, 'pfx':pfx, 'rr':rr, 'l':l, 'sfx':sfx};
}

function GlueNeib(r0, rr, sfx) {
  return '' + r0 + rr.join('') + sfx;
}

var equiv_cache = new Array(RL);
function EquivRules(b, v=1) {
  var bv = b * RB + v;
  
  if(equiv_cache[bv]!==undefined) return equiv_cache[bv];
  
  var ret = {};
  
  var rule = NeibArr4Int(b);
  let {r0, rr, l, sfx} = SliceNeib(rule);
  var vv = v;
  var kk = '';
  
  if(Rsymm==85) {  // rotating by pi/4 (8 cells) plus parity
    ret[GlueNeib(r0, rr, sfx)] = vv;
    
    for(var i=1; i<l; i++) {
      rr = ArrRotate(rr, l);
      ret[GlueNeib(r0, rr, sfx)] = vv;
    }
    
    rr = ArrMirror(rr, l);
    ret[GlueNeib(r0, rr, sfx)] = vv;
    
    for(var i=1; i<l; i++) {
      rr = ArrRotate(rr, l);
      ret[GlueNeib(r0, rr, sfx)] = vv;
    }
  }
  else if(Rsymm==80) {  // rotating by pi/4 (8 cells)
    ret[GlueNeib(r0, rr, sfx)] = vv;
    
    for(var i=1; i<l; i++) {
      rr = ArrRotate(rr, l);
      ret[GlueNeib(r0, rr, sfx)] = vv;
    }
  }
  else if(Rsymm==47) {  // rotating by pi/2 with vector (rotating with space rotation) values
    for(var i=0; i<l; i++) {
      r0 = ValueVectorRotate(r0, l);
      rr = ArrVectorRotate(rr, l);
      vv = ValueVectorRotate(vv, l);
      kk = GlueNeib(r0, rr, sfx);
      if(ret[kk]!==undefined && ret[kk]!=vv) return {};  // forbidden (symmetry violating) rule
      ret[kk] = vv;
    }
  }
  else {  // no symmetry = just the rule itself
    ret[GlueNeib(r0, rr, sfx)] = vv;
  }
  
  var ordered = {};
  Object.keys(ret).sort().forEach(function(key) { ordered[key] = ret[key]; });
  
  equiv_cache[bv] = {...ordered};
  return ordered;
}

function MinimalOfEquivRules(b) {
  var minb = 0;
  for(var rule in EquivRules(b)) {  // EquivRules are already sorted - take the first one
    minb = NeibInt4Str(rule);
    break;
  }
  return minb;
}

// RANDOM MUTATIONS ////////////////////////////////////////////////////////////////
// mutation = rule (b,v0) of some neib (b) has it's value (v) changed

var Mutas = [];

function GenMutas(n=0) {
  var mutas = [];
  for(var z=0; z<FD; z++) mutas[z] = {};
  
  for(var i=0; i<n; i++) {
    var z  = rndR(0, FD);  if(Family=='Langton') z = 1;  if(Family=='Conway') z = 0;
    var b  = rndR(0, RL);
    
    if(mutas[z][b]!==undefined) continue;  // no strict duplicates
    
    var minb = MinimalOfEquivRules(b);
    if(mutas[z][minb]!==undefined) continue;  // no equiv duplicates
    
    var v  = rndR(0, RB);
    var v0 = GetRule(z, minb);
    if(v==v0) continue;  // same value
    
    mutas[z][minb] = v;
  }
  
  return mutas;
}

function SetMutaRules(mutas) {
  var n = 0;
  for(var z in mutas) {
    for(var b in mutas[z]) {
      var v = mutas[z][b];
      
      var eqr = EquivRules(b, v);
      for(var rule in eqr) {
        var bb = NeibInt4Str(rule);
        var vv = eqr[rule];
        SetRule(z, bb, vv);
      }
      
      n ++;
    }
  }
  
  var mutastr = EncodeMutaStr(mutas);
  var divstr = mutastr.length < 1000
               ? "<a href='?fm=" + FM + "&notaset=" + (Notaset) + "&mutamd5=" + MD5(mutastr) + "'>" + mutastr + "</a>"
               : mutastr.substring(0,8) + "&hellip;(" + mutastr.length + ")&hellip;" + mutastr.substring(mutastr.length-8);
  divrules.innerHTML += "<pre class='nrrw'>" + divstr + "</pre>";
  console.log(n + ' random rule mutations:\n' + mutastr);
  
  return mutas;
}

// BASE64 ENCODING ////////////////////////////////////////////////////////////////

var base64enc = [
  '0','1','2','3','4','5','6','7','8','9',
  'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
  'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
  '$','@',
];
// js.encodeURIComponent leaves -_.!~*'() 
// php.urlencode leaves -_.

function myBase64encode(m, nn) {
  var ret = '';
  for(var i=0; i<nn; i++) {
    var d = m % 64;
    m -= d;
    m /= 64;
    ret = ''+base64enc[d]+ret;
  }
  if(m>0) alert('base64 encoding error: not enough digits!');
  return ret;
}

function myBase64decode(s, nn) {
  var ret = [];
  var m = 0;
  var tz = s.split('');
  for(var i in tz) {
    var d = base64enc.indexOf(tz[i]);  if(d<0) continue;
    m = m * 64 + d;
    if(i % nn == nn-1) {
      ret.push(m);
      m = 0;
    }
  }
  return ret;
}

function EncodeMutaStr(mutas) {  // myBase64-encoded Mutas
  var ret = '';
  for(var z in mutas) {
    if(ret) ret += '\n';
    for(var b in mutas[z]) {
      var v = mutas[z][b];
      var m = b * RB + v;
      ret += myBase64encode(m, RLv64);
    }
  }
  return ret;
}

function DecodeMutaStr(mutastr) {
  var ret = [];
  var ts = mutastr.split('\n');
  for(var z in ts) {
    ret[z] = {};
    var ms = myBase64decode(ts[z], RLv64);
    for(var i in ms) {
      var m = ms[i];
      var v = m % RB;
      var b = (m - v) / RB;
      ret[z][b] = v;
    }
  }
  return ret;
}

// RULES NOTATION ////////////////////////////////////////////////////////////////
// nota = rules encoded in short symbolic notation (neib-setting specific)
// notaset = set of notas for all FD layers

// UNI-CONWAY RULES ////////////////////////////////////////////////////////////////
// 0->         : 1->         : 2->
// 0->1 . 0->2 : 1->1 . 1->2 : 2->1 . 2->2

// digits = number of living cells in same-layer neib: 25 means that there are 2 cells of value=1 and 5 cells of value=2 around current cell
// "20.14:.35:" means:
// - cell with value=0 transforms to value=1 if there are 2 cells with value=1 and 0 cells with value=2 around
// - cell with value=0 transforms to value=2 if there are 1 cells with value=1 and 4 cells with value=2 around
// - cell with value=1 transforms to value=2 if there are 3 cells with value=1 and 5 cells with value=2 around
// - in all other cases cell dies (value->0)

// inter-plane (different layers) interactions are listed after "!" sign:
// "!21*-0" means: if current cell has value=1 and the cell above (z+1) has value=2 and no matter (*) which cell is below - current cell dies ->0
// wildcards: * = any value, ? = any > 0

// alphabet is to be used for large neighborhoods (having >9 of cells): amount of alive neibs must be always denoted by single char
var sum_alphabet = [0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h'];  // not implemented yet

function Nota_Decode(notaset, fd=FD, rb=RB) {
  function Split_Layers(nota) {
    var layers = nota.split( nota.indexOf('\n')>-1 ? '\n' : ',' );
    if(layers.length!=fd) {
      if(notaset) alert('splitted length ('+layers.length+') is not fd='+fd+' (notaset='+notaset+')');
      for(var z=0; z<fd; z++) layers[z] = '';
    }
    return layers;
  }
  
  var layers = Split_Layers(notaset);
  
  if(glFamily.plane_inter && notaset.indexOf('!')<0) {  // Family default plane interaction
    var plane_inters = Split_Layers(glFamily.plane_inter);
    for(var z=0; z<fd; z++) {
      layers[z] += '!' + plane_inters[z];
    }
  }
  
  var sigma_conditions = [], plane_interactions = [];
  for(var z=0; z<fd; z++) {
    var layer = layers[z];
    
    var affecting_layers = layer.split('!');
    
    sigma_conditions[z] = [];
    var affecting_self_layer = affecting_layers[0];  // first part of !-string describes interaction in self-plane
    var state0s = affecting_self_layer.split(':');
    for(var state0=0; state0<state0s.length; state0++) {
      sigma_conditions[z][state0] = [];
      var state1s = state0s[state0].split('.');
      for(var state1=0; state1<state1s.length; state1++) {
        var sumstr = state1s[state1];
        var ncond = sumstr.length / (rb-1);
        for(var i=0; i<ncond; i++) {
          var chars = sumstr.substring(i*(rb-1), (i+1)*(rb-1));  // not checking if values in sumstr are correct; their sum must be <=8 for 3*3 Moore2D
          sigma_conditions[z][state0][chars] = state1 + 1;  // state1=0 is default, so we store only states>0 in our encoding
        }
      }
    }
    
    plane_interactions[z] = [];
    for(var i=1; i<affecting_layers.length; i++) {  // other parts of !-string describe interactions with other layers (they override self-plane rules)
      var upperlower_restriction = affecting_layers[i];
      if(!upperlower_restriction) continue;
      
      // 0 = layer itself, 1 = upper layer (z+1), 2 = lower layer (z-1); can add more if needed
      if(upperlower_restriction.length!=5) alert('error in nota_decode: upperlower_restriction.length<>5');
      var [upper, ccell, lower, dash, val] = upperlower_restriction.split('');
      if(dash!='-') alert('error in nota_decode: missing dash');
      
      // 11*-0,11*-0!*00-0,*00-0
      // ??*-0,??*-0!*00-0,*00-0
      
      var check_ucl = function(char, n) {
        return (char==n || char=='*' || char=='?' && n>0);
      };
      
      for(var u=0; u<rb; u++) {
        if(!check_ucl(upper, u)) continue;
        for(var c=0; c<rb; c++) {
          if(!check_ucl(ccell, c)) continue;
          for(var l=0; l<rb; l++) {
            if(!check_ucl(lower, l)) continue;
            plane_interactions[z][''+u+c+l] = val;
          }
        }
      }
    }
  }
  
  return {'sigma_conditions':sigma_conditions, 'plane_interactions':plane_interactions};
}
//print_r(Nota_Decode(`1234.77:3456.:.7809!*00-0!11*-0\n55.:.1032:03.!*0?-1\n43.:.:.`, 3, 3));

function CheckLegacyBorder(r, z) {  // this is not necessary, but keeps less significant (nonzero) values in rules table
  if(Rupper===false || Rlower===false) return false;
       if(z==FD-1 && r[Rupper]==1) return 0;  // no uppers for top-layer
  else if(z==0    && r[Rlower]==1) return 0;  // no lowers for bottom-layer
  return false;
}
  
function CheckPlaneInteraction(r, plane_interaction_z) {
  if(!plane_interaction_z) return false;
  if(Rupper===false || Rlower===false) return false;
  var ucl = ''+r[Rupper]+r[0]+r[Rlower];
  if(plane_interaction_z[ucl]===undefined) return false;
  return plane_interaction_z[ucl];
}
  
function CheckSigmaCondition(r, sigma_condition_z) {
  if(sigma_condition_z[r[0]]===undefined) return 0;
  var sums = Array(RB).fill(0);
  for(var i=1; i<=SNN; i++) {
    sums[r[i]] ++;
  }
  var sigma = '';
  for(var i=1; i<RB; i++) {
    sigma += sums[i] || '0';
  }
  if(sigma_condition_z[r[0]][sigma]===undefined) return 0;
  return sigma_condition_z[r[0]][sigma];
}

function SetConwayRules(notaset) {
  var {sigma_conditions, plane_interactions} = Nota_Decode(notaset);
  
  for(var z=0; z<FD; z++) {
    for(var b=0; b<RL; b++) {
      var r = NeibArr4Int(b);
      
      var v = 0;
      
      var legbor = CheckLegacyBorder(r, z);
      if(legbor!==false) {
        v = legbor;
      }
      else {
        var plin = CheckPlaneInteraction(r, plane_interactions[z]);
        if(plin!==false) {  // plane interactions override self-layer rules
          v = plin;
        }
        else {
          v = CheckSigmaCondition(r, sigma_conditions[z]);
        }
      }
      
      SetRule(z, b, v);
    }
  }
}

// LANGTON RULES ////////////////////////////////////////////////////////////////

function SetLangtonRules() {
  console.time('SetLangtonRules');
  // direction of Ant's movement is encoded in cell's values: 1=down, 2=left, 3=up, 4=right
  // these values are "vector", they rotate with space rotation
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
      var eqr = EquivRules(b, v);
      for(var rule in eqr) {
        var bb = NeibInt4Str(rule);
        var vv = eqr[rule];
        SetRule(z, bb, vv);
      }
    }
  }
  console.timeEnd('SetLangtonRules');
}

// FHP RULES ////////////////////////////////////////////////////////////////

function SetFHPRules() {
  var z = 0;
  
  // default movement = no interaction
  for(var b=0; b<RL; b++) {
    var v = b;
    SetRule(z, b + (0 << 8), v);  // rnd=0
    SetRule(z, b + (1 << 8), v);  // rnd=1
  }
  
  // FHP-I: 2-particle head-on collisions (p=0.5)
  var r = 1 << 8;
  SetRule(z,   0b00010010, 0b00100100);
  SetRule(z,   0b00100100, 0b01001000);
  SetRule(z,   0b01001000, 0b00010010);
  SetRule(z, r+0b00010010, 0b01001000);
  SetRule(z, r+0b00100100, 0b00010010);
  SetRule(z, r+0b01001000, 0b00100100);
  //             76543210    76543210
  
  // FHP-I: symmetric 3-particle collisions
  for(var rnd=0; rnd<=1; rnd++) {
    var r = rnd << 8;
    SetRule(z, r+0b00101010, 0b01010100);
    SetRule(z, r+0b01010100, 0b00101010);
    //             76543210    76543210
  }
  
  // FHP-II: 4-particle head-on collisions (p=0.5)
  var r = 1 << 8;
  SetRule(z,   0b01101100, 0b01011010);
  SetRule(z,   0b01011010, 0b00110110);
  SetRule(z,   0b00110110, 0b01101100);
  SetRule(z, r+0b01101100, 0b00110110);
  SetRule(z, r+0b01011010, 0b01101100);
  SetRule(z, r+0b00110110, 0b01011010);
  //             76543210    76543210
  
  // FHP-II: 2+1 head-on collisions with spectator
  for(var rnd=0; rnd<=1; rnd++) {
    var r = rnd << 8;
    SetRule(z, r+0b01010010, 0b01100100);
    SetRule(z, r+0b00011010, 0b00101100);
    SetRule(z, r+0b00010110, 0b01001100);
    SetRule(z, r+0b00110010, 0b01101000);
    //             76543210    76543210
    SetRule(z, r+0b00100110, 0b01001010);
    SetRule(z, r+0b00110100, 0b01011000);
    SetRule(z, r+0b00101100, 0b00011010);
    SetRule(z, r+0b01100100, 0b01010010);
    //             76543210    76543210
    SetRule(z, r+0b01001100, 0b00010110);
    SetRule(z, r+0b01101000, 0b00110010);
    SetRule(z, r+0b01001010, 0b00100110);
    SetRule(z, r+0b01011000, 0b00110100);
    //             76543210    76543210
  }
  
  // FHP-II: rest particle collisions
  for(var rnd=0; rnd<=1; rnd++) {
    var r = rnd << 8;
    //             76543210    76543210
    SetRule(z, r+0b00000011, 0b01000100);
    SetRule(z, r+0b00000101, 0b00001010);
    SetRule(z, r+0b00001001, 0b00010100);
    SetRule(z, r+0b00010001, 0b00101000);
    SetRule(z, r+0b00100001, 0b01010000);
    SetRule(z, r+0b01000001, 0b00100010);
    //             76543210    76543210
    SetRule(z, r+0b01000100, 0b00000011);
    SetRule(z, r+0b00001010, 0b00000101);
    SetRule(z, r+0b00010100, 0b00001001);
    SetRule(z, r+0b00101000, 0b00010001);
    SetRule(z, r+0b01010000, 0b00100001);
    SetRule(z, r+0b00100010, 0b01000001);
    //             76543210    76543210
  }
  
  // obstacle collisions
  for(var b=0; b<(1 << 7); b++) {
    var bb = b + (1 << 7);
    
    var rule = NeibArr4Int(bb, 8);
    let {r0, rr, l, sfx} = SliceNeib(rule);
    
    rr = ArrRotate(rr, l);
    rr = ArrRotate(rr, l);
    rr = ArrRotate(rr, l);
    
    var v = NeibInt4Str(GlueNeib(r0, rr, sfx));
    
    SetRule(z, bb + (0 << 8), v);  // rnd=0
    SetRule(z, bb + (1 << 8), v);  // rnd=1
  }
  
  /*
  b = 0b10001101;
  var rule = NeibArr4Int(b, 8);
  let {r0, rr, l, sfx} = SliceNeib(rule);
  
  console.log(SliceNeib(rule));
  console.log(GlueNeib(r0, rr, sfx));
  
  
  console.log(GlueNeib(r0, rr, sfx));
  */
  
  //SetRule(z,   0b10000000, 0b10000000);
  //             76543210    76543210
  
  //SetRule(z,   0b00000000, 0b00000000);
}

// PARTITIONING RULES ////////////////////////////////////////////////////////////////
// classic rules are: neib->value, but partitioning rules are: neib->neib

function SetPartitRules() {
  var neibs_by_class = [];
  var class4neib = [];
  
  for(var b=0; b<RL; b++) {
    var eqr = EquivRules(b);
    var neq = Object.keys(eqr).length;
    var r = NeibArr4Int(b);
    
    var max_ned_per_cell = 99;
    var ned = 0;  // for use in class to conserve some value of living cells
    var conserving_value = 0;
    for(var i=0; i<RC; i++) {
      conserving_value = r[i]>0 ? 1 : 0;  // conserve number of living cells
      //conserving_value = r[i];  // conserve total value of cells
      //conserving_value = 
      ned += conserving_value;
    }
    
    var bclass = (max_ned_per_cell + 1) * RC * neq + ned;  // classifying neibs by number of it's equiv-neibs
    
    class4neib[b] = bclass;
    
    if(neibs_by_class[bclass]===undefined) neibs_by_class[bclass] = [];
    neibs_by_class[bclass].push(b);
  }
  //var strs = [];  for(var bclass in neibs_by_class) { strs[bclass] = [];  for(var k in neibs_by_class[bclass]) strs[bclass][k] = NeibStr4Int(neibs_by_class[bclass][k]); }  console.log(strs);
  
  var rules = [], invrules = [];
  for(var b=0; b<RL; b++) {
    if(rules[b]) continue;
    
    var bclass = class4neib[b];
    
    var free_dests = [];
    for(var i=0; i<neibs_by_class[bclass].length; i++) {
      var dest = neibs_by_class[bclass][i];
      if(!invrules[dest]) free_dests.push(dest);
    }
    if(!free_dests.length) console.log("ERROR: no free dests for b="+b);
    
    var rnd = rndR(0, free_dests.length);
    var to = free_dests[rnd];  // rule is mapping: b->to
    if(invrules[to]) console.log('IMPERR: not found <to> for b='+b);
    
    var arr1 = NeibArr4Int(b);   var slice1 = SliceNeib(arr1);  var r01=slice1.r0, rr1=slice1.rr, l1=slice1.l, sfx1 = slice1.sfx;
    var arr2 = NeibArr4Int(to);  var slice2 = SliceNeib(arr2);  var r02=slice2.r0, rr2=slice2.rr, l2=slice2.l, sfx2 = slice2.sfx;
    
    if(Rsymm==80) {  // rotating by pi/4 (8 cells)
      rules[NeibInt4Str(GlueNeib(r01, rr1, sfx1))] = NeibInt4Str(GlueNeib(r02, rr2, sfx2));
      
      for(var i=1; i<l1; i++) {
        rr1 = ArrRotate(rr1, l1);
        rr2 = ArrRotate(rr2, l2);
        rules[NeibInt4Str(GlueNeib(r01, rr1, sfx1))] = NeibInt4Str(GlueNeib(r02, rr2, sfx2));
      }
    }
    
    invrules = array_flip(rules);
  }
  
  for(var b=0; b<RL; b++) SetRule(0, b, rules[b]);
  
  //for(var b=0; b<RL; b++) console.log(b, NeibStr4Int(b), NeibStr4Int(rules[b]), rules[b]);
}

// SET RULES ////////////////////////////////////////////////////////////////

function SetRules(notaset) {
  if(Family=='Langton') SetLangtonRules();
  else if(Mode=='PRT')  SetPartitRules();
  else if(Mode=='FHP')  SetFHPRules();
  else                  SetConwayRules(notaset);
}

// RANDOM NOTASET ////////////////////////////////////////////////////////////////

function RandomConwayRules(fd=FD, rb=RB) {
  var zero_gene = '';  for(var i=1; i<rb; i++) zero_gene += '0';
  
  var gene_count = 0, countar = {};
  function CountSigmaGenes(n, max, base) {
    for(var d=0; d<=max; d++) {  // each digit in gene is 0..max
      if(n<rb-2) {
        CountSigmaGenes(n + 1, max - d, base + d);
      }
      else {
        gene_count ++;
        countar[base + d] = 1;
      }
    }
  }
  CountSigmaGenes(0, SNN, '');
  gene_count -= 1;  // substracting 1 zero_gene
  
  var nota = '';
  for(var z=0; z<fd; z++) {
    if(z>0) nota += ',';
    var layer = '';
    for(var from=0; from<rb; from++) {
      if(from>0) layer += ':';
      var s = '', genes = {};
      for(var to=1; to<rb; to++) {
        if(to>1) s += '.';
        
        var l = TT==2
          ? rndR(1, gene_count)  // 1..gene_count-1: no zero length and no all-genes
          : (
            RB==2
            ? round(Math.sqrt(rndR(0, sqr(gene_count+1))))  // higher probability for many-digit genes for TT=3/RB=2
            : sqr(rndR(0, round(Math.sqrt(gene_count+1))))
          )
        ;
        var r = {};
        for(var j=0; j<l; j++) {
          var dd = rnd_split(rb, SNN, rndR);
          var gene = '';
          for(var i=1; i<rb; i++) {
            gene += dd[i];  // use alphabet here
          }
          if(genes[gene]) continue;  // same neib (gene) and same center-cell (from) can not lead to different values (to); farther to's are slightly shorter
          if(TT==2 && gene==zero_gene) continue;  // nothing appeares from nowhere and everything dies in vacuum
          genes[gene] = 1;
          r[gene] = 1;
        }
        
        var strand = '';
        for(var k in r) {
          if(r[k]) strand += k;
        }
        
        s += strand;
      }
      layer += s;
    }
    nota += layer;
  }
  return nota;
}
//for(var i=0; i<100; i++) print_r(RandomConwayRules());
//print_r(RandomConwayRules(3, 3).length);

/*
function ConwayRandomRules() {
  function ConwayRandomStrand(min=0) {
    var ret = '';
    var l = rndR(1, 9);
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
  
  return notaset;
}
*/

function RandomNotaset() {
  var notaset = '';
  if(Family=='Langton') {
    // todo
  }
  else {
    notaset = RandomConwayRules();
  }
  divrules.innerHTML += "<pre><a href='?fm=" + FM + "&notaset=" + (notaset) + "'>" + notaset + "</a></pre>";
  return notaset;
}

// INIT RULES ////////////////////////////////////////////////////////////////

function InitRules() {
  for(var z=0; z<FD; z++) R[z].fill(0);
  
  if(cfg.randrules) {
    Notaset = RandomNotaset();
  }
  
  SetRules(Notaset);
  
  console.log('Notaset:', Notaset);
  
  if(Mutaset)          Mutas = DecodeMutaStr(Mutaset);
  else if(cfg.nmuta>0) Mutas = GenMutas(cfg.nmuta);
  
  if(Mutas) SetMutaRules(Mutas);
  
  console.log('Mutas:', Mutas);
  
  SetRulesTexture();
}

//  ////////////////////////////////////////////////////////////////