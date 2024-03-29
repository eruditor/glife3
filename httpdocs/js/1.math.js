// COMMON MATH ////////////////////////////////////////////////////////////////

function intval(x) { if(!x) return 0;  return parseInt(x, 10); }
function floor(x) { return Math.floor(x); }
function round(x) { return Math.round(x); }
function abs(x)   { return Math.abs(x); }
function fract(x) { return x - Math.floor(x); }
function sgn(x) { return x > 0 ? 1 : (x < 0 ? -1 : 0); }
function round100(x, d=100) { return Math.round(x/d)*d; }
function sqr(x) { return x*x; }

// ARRAYS ////////////////////////////////////////////////////////////////

function arsort_keys(obj) {
  return Object.keys(obj).sort(function(a,b){ if(a<b) return -1;  if(a>b) return  1;  return 0; });
}

function array_flip(trans) {
  var key, ret = {};
  for(key in trans) {
    if(trans.hasOwnProperty(key)) ret[trans[key]] = key;
  }
  return ret;
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
function rndF(a, b) { return Math.floor(Frand32()*(b-a)) + a; }  // random for Field generation
function rndR(a, b) { return Math.floor(Rrand32()*(b-a)) + a; }  // random for Rules generation
function rndJ(a, b) { return Math.floor(Math.random()*(b-a)) + a; }  // just a usual random
function ReInitSeeds(refield=false) {
  if(!refield) Rseed = int10(URL.get('rseed')) || Rrand32() * 4294967296;  Rrand32(Rseed);  document.getElementById('rseedinp').value = Rseed;
               Fseed = int10(URL.get('fseed')) || Frand32() * 4294967296;  Frand32(Fseed);  document.getElementById('fseedinp').value = Fseed;
  console.log('Rseed,Fseed=', Rseed, Fseed);
}

function rnd_split(n, sum, rndfunc=rndR) {  // n random numbers with fixed sum
  var walls = [];
  for(var i=0; i<n-1; i++) {
    walls[i] = rndfunc(0, sum+1);  // 0..sum
  }
  walls.sort(function(a,b){return a-b;});
  
  var ret=[];
  for(var i=0; i<n; i++) {
         if(i==0)   ret[i] = walls[i];
    else if(i==n-1) ret[i] = sum - walls[i-1];
    else            ret[i] = walls[i] - walls[i-1];
  }
  return ret;
}

// MD5 ////////////////////////////////////////////////////////////////

var MD5 = function(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}

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

//  ////////////////////////////////////////////////////////////////