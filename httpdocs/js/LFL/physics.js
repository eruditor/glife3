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

// RULES -> UNIFORMS ////////////////////////////////////////////////////////////////

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
    ret += (ret?'\n':'') + `const `+typ+` `+key+`[`+len+`] = `+typ+`[`+len+`]( `+t+` );`;
  }
  LL = len;
  return ret;
}

function GetUniforms4Ruleset() {
  var s4uniforms = Mat4uniforms(ParseUniformsJSON(Ruleset));
  //console.log(s4uniforms);
  return s4uniforms;
}

// INIT RULES ////////////////////////////////////////////////////////////////

function InitRules() {
}

// --- ////////////////////////////////////////////////////////////////