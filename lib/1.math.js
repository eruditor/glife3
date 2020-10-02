// COMMON MATH ////////////////////////////////////////////////////////////////

function intval(x) { if(!x) return 0;  return parseInt(x, 10); }
function floor(x) { return Math.floor(x); }
function round(x) { return Math.round(x); }
function sgn(x) { return x > 0 ? 1 : (x < 0 ? -1 : 0); }

function arsort_keys(obj) {
  var keys = Object.keys(obj);
  return keys.sort(function(a,b){return obj[b]-obj[a]});
}

// seedable PRNG to replace Math.random()
function mulberry32(a) {
  return function() {
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

// HSL2RGB
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