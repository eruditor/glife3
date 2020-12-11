// COMMON MATH ////////////////////////////////////////////////////////////////

function intval(x) { if(!x) return 0;  return parseInt(x, 10); }
function floor(x) { return Math.floor(x); }
function round(x) { return Math.round(x); }
function sgn(x) { return x > 0 ? 1 : (x < 0 ? -1 : 0); }
function round100(x, d=100) { return Math.round(x/d)*d; }
function sqr(x) { return x*x; }

// ARRAYS ////////////////////////////////////////////////////////////////

function arsort_keys(obj) {
  return Object.keys(obj).sort(function(a,b){ if(a<b) return -1;  if(a>b) return  1;  return 0; });
}

// PRNG ////////////////////////////////////////////////////////////////

function mulberry32(a) {
  return function(seed=0) {
    if(seed) { a = seed;  return a; }
    var t = a += 0x6D2B79F5;
    t = Math.imul(t ^ t >>> 15, t | 1);
    t ^= t + Math.imul(t ^ t >>> 7, t | 61);
    return ((t ^ t >>> 14) >>> 0) / 4294967296;
  }
}
var Frand32 = mulberry32(Fseed);
var Rrand32 = mulberry32(Rseed);
function rndF(a, b) { return Math.floor(Frand32()*(b-a)) + a; }
function rndR(a, b) { return Math.floor(Rrand32()*(b-a)) + a; }
function rndJ(a, b) { return Math.floor(Math.random()*(b-a)) + a; }
function ReInitSeeds(refield=false) {
  if(!refield) Rseed = int10(URL.get('rseed')) || Rrand32() * 4294967296;  Rrand32(Rseed);  document.getElementById('rseedinp').value = Rseed;
               Fseed = int10(URL.get('fseed')) || Frand32() * 4294967296;  Frand32(Fseed);  document.getElementById('fseedinp').value = Fseed;
  console.log(Rseed, Fseed);
}

// HSL2RGB ////////////////////////////////////////////////////////////////

function HSL2RGB(h, s, l) {
  var r, g, b;
  h /= 360;
  if(s==0) {
    r = g = b = l;
  }
  else {
    var hue2rgb = function hue2rgb(p, q, t){
      if(t < 0) t += 1;
      if(t > 1) t -= 1;
      if(t < 1/6) return p + (q - p) * 6 * t;
      if(t < 1/2) return q;
      if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
      return p;
    }
    
    var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    var p = 2 * l - q;
    r = hue2rgb(p, q, h + 1/3);
    g = hue2rgb(p, q, h);
    b = hue2rgb(p, q, h - 1/3);
  }
  return {'r': Math.round(r * 255), 'g': Math.round(g * 255), 'b': Math.round(b * 255)};
}

// CUSTOM BASE64 ////////////////////////////////////////////////////////////////

var base64enc = [
  '0','1','2','3','4','5','6','7','8','9',
  'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
  'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
  '$','@',
];

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

//  ////////////////////////////////////////////////////////////////