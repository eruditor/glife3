// GEOMS ////////////////////////////////////////////////////////////////
// geom = geometry of neighborhood

// neighborhood geometry coords (dx,dy,dz)
const RG = 
  Rgeom==18  // Moore2D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [-1, -1,  0],  //  1
      [ 0, -1,  0],  //  2 = up
      [ 1, -1,  0],  //  3
      [ 1,  0,  0],  //  4 = right
      [ 1,  1,  0],  //  5
      [ 0,  1,  0],  //  6 = down
      [-1,  1,  0],  //  7
      [-1,  0,  0],  //  8 = left
    ]
  : (
  Rgeom==182  // Moore2D + vonNeumann1D
  ?
    [
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
    ]
  : (
  Rgeom==142  // vonNeumann2D + vonNeumann1D
  ?
    [
      [ 0,  0,  0],  //  0 = self
      [ 0, -1,  0],  //  1 = up
      [ 1,  0,  0],  //  2 = right
      [ 0,  1,  0],  //  3 = down
      [-1,  0,  0],  //  4 = left
      [ 0,  0,  1],  //  5 = upper
      [ 0,  0, -1],  //  6 = lower
    ]
  :
  []
  ))
;

const RC = RG.length;  // number of cells in neighborhood that affects current cell's state (length of neib encoding)

const SNN = intval((''+Rgeom)[1]);  // number of cells in same-layer neighborhood (8 for Moore, 4 for vonNeumann)

// NEIB VARS ////////////////////////////////////////////////////////////////
// neib = configuration of cell states in neighborhood geometry

// each cell in neighborhood geometry can take one of RB values
const RL = Math.pow(RB, RC);  // total number of all possible neibs (length of physics rule space)
const RLv = RL * RB;  // length of rule encoding (rule space -> value)
const RLv64 = Math.ceil(Math.log(RLv) / Math.log(64));  // number of base64-digits needed to encode rule

// RULES ARRAY ////////////////////////////////////////////////////////////////
// rule = correspondence: neib -> cell_state (RB^RL)

const RX = gl.getParameter(gl.MAX_TEXTURE_SIZE);  // max texture length (MAX_3D_TEXTURE_SIZE for 3D)
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
  for(var z=0; z<FD; z++) SetTexture(TT + z, RulesTexture[z], R[z], Rtx, Rty);
}

// NEIB ENCODINGS ////////////////////////////////////////////////////////////////

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
               ? "<a href='?notaset=" + MD5(Notaset) + "&mutamd5=" + MD5(mutastr) + "'>" + mutastr + "</a>"
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
    var layers = nota.split( nota.indexOf('\n')>-1 ? '\n' : ',' );  if(layers.length!=fd) alert('splitted length ('+layers.length+') is not fd='+fd);
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
       if(z==FD-1 && r[ 9]==1) return 0;  // no uppers for top-layer
  else if(z==0    && r[10]==1) return 0;  // no lowers for bottom-layer
  return false;
}
  
function CheckPlaneInteraction(r, plane_interaction_z) {
  if(!plane_interaction_z) return false;
  var ucl = '';
       if(Rgeom==182) ucl = ''+r[9]+r[0]+r[10];
  else if(Rgeom==142) ucl = ''+r[5]+r[0]+r[6];
  else return false;
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

function SetRules(notaset) {
  if(Family=='Langton') SetLangtonRules();
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
        
        var l = rndR(1, gene_count);  // 1..gene_count-1: no zero length and no all-genes
        var r = {};
        for(var j=0; j<l; j++) {
          var dd = rnd_split(rb, SNN, rndR);
          var gene = '';
          for(var i=1; i<rb; i++) {
            gene += dd[i];  // use alphabet here
          }
          if(genes[gene]) continue;  // same neib (gene) and same center-cell (from) can not lead to different values (to); farther to's are slightly shorter
          if(gene==zero_gene) continue;  // nothing appeares from nowhere and everything dies in vacuum
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
  divrules.innerHTML += "<pre><a href='?notaset=" + MD5(notaset) + "'>" + notaset + "</a></pre>";
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