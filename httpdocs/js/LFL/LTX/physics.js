// RULES FROM JSON ////////////////////////////////////////////////////////////////

function ParseAllUnterminatedFractions(s) {
  return s.replaceAll(
    /(\d+)\.[^\d]/g,
    (t) => t.replace('.', '')
  );
}

function ParseTerminateFraction(n) {
  var s = n.toString();
  var d = s.indexOf('.');
  if(d===-1) s += '.';
  else {
    s = s.substring(0,d+8);
  }
  return s;
}

function ParseSimpleFraction(s) {
  var ret = s;
  if(typeof(s)=='string' && s.indexOf('/')!==-1) {
    var t = /(\d+)\.?\/(\d+)\.?/g.exec(s);
    if(t[1] && t[2]) ret = t[1] / t[2];
  }
  return ret;
}

function ParseAllSimpleFractions(s) {
  return s.replaceAll(
    /(\d+)\.?\/(\d+)\.?/g,
    (t) => ParseSimpleFraction(t)
  );
}

function ParseAllFractions(s) {
  return ParseAllSimpleFractions(ParseAllUnterminatedFractions(s));
}

function ParseUniformsJSON(json) {
  return JSON.parse(ParseAllFractions(json));
}

// RULES -> TEXTURE ////////////////////////////////////////////////////////////////

var LL = 0;
function Mat4uniforms(v) {
  var ret = '', len = 0;
  for(var key in v) {
    if(!len) len = v[key].length;
    if(v[key].length!=len) alert('Mat4uniforms: length mismatch: '+v[key].length+'<>'+len);
    
    var t = '';
    for(var i in v[key]) {
      var val = v[key][i];
      if(key!='src' && key!='dst') val = ParseTerminateFraction(val);
      t += (t?', ':'') + val;
    }
    var typ = (key!='src' && key!='dst' ? 'float' : 'int')
    ret += `  const `+typ+` `+key+`[`+len+`] = `+typ+`[`+len+`]( `+t+` );\n`;
  }
  LL = len;
  return ret.trim();
}

function GetUniforms4Ruleset() {
  var uniforms = ParseUniformsJSON(Ruleset);
  var s4uniforms = Mat4uniforms(uniforms);
  console.log(uniforms);
  return s4uniforms;
}

// INIT RULES ////////////////////////////////////////////////////////////////

function bell1(x, m, s) {
  if(s<=0.) return 0.;
  var v = (x-m)/s;
  return Math.exp(-v*v/2.);
}

function get1Weight(r, betaLen, relR, beta0, beta1, beta2) {
  var Br = betaLen / relR * r;
  var iBr = floor(Br);
  var mod1 = fract(Br);
  var height = iBr==0 ? beta0 : (iBr==1 ? beta1 : beta2);
  return height * bell1(mod1, 0.5, 0.15);
}

var R;
const TXL = 1024;  // space granularity: 256 points for [0,1] interval
const TXL1 = TXL-1;
var K4;  // = (number of kernels / 4)  // 4 numbers stored in 1 texture cell

var kernelist = ParseUniformsJSON(Ruleset);
var kernels = [];
for(var key in kernelist) {
  for(var i in kernelist[key]) {
    if(!kernels[i]) kernels[i] = [];
    kernels[i][key] = kernelist[key][i];
  }
}

LL = kernels.length;

K4 = Math.ceil( LL / 4 );

R = new jsdata_Array(4 * TXL * K4);
R.fill(0);

function InitRules() {
  for(var kb=0; kb<K4; kb++) {
    for(var xb=0; xb<4; xb++) {
      var l = 4 * kb + xb;
      var K = kernels[l];
      if(!K) break;
      
      var t ='';
      for(var ix=0; ix<=TXL1; ix++) {
        var r = ix / TXL1;
        var weight = get1Weight(r, K['betaLen'], K['relR'], K['beta0'], K['beta1'], K['beta2']);
        
        R[4*(kb*TXL+ix)+xb] = weight;
        
        t += ' ' + weight;
      }
      //console.log(l + ': ' + t);
    }
  }
  
  var RulesTexture = [];
  RulesTexture[0] = CreateTexture(TXL, K4);

  SetTexture(TT + 0, RulesTexture[0], R, TXL, K4);
  
}

// --- ////////////////////////////////////////////////////////////////