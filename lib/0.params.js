// GET PARAMS ////////////////////////////////////////////////////////////////

// get GET params
const urlParams = new URLSearchParams(window.location.search);
// get script self address
var scripts = document.getElementsByTagName('script');
var myScript = scripts[scripts.length - 1];
const selfParams = new URLSearchParams(myScript.src);

function int10(x) { if(!x) return 0;  return parseInt(x, 10); }

class Cfg {
  constructor(url, slf) {
    this.debug = url.get('debug') ? 1 : 0;
    this.paused = url.get('paused') ? 1 : 0;
    this.pausestat = url.get('pausestat')>0 ? 1 : 0;
    this.maxfps = int10(url.get('maxfps'));  // Calc framerate limit
    this.showiter = int10(url.get('showiter'));  if(this.showiter<2) this.showiter = 0;  // Show once per showiter Calcs
    this.rerun = url.get('rerun') || slf.get('rerun');
    this.drawrules = url.get('drawrules') ? 1 : 0;
    this.autore = url.get('autore') ? 1 : 0;
    this.orga = url.get('orga') ? 1 : 0;
    this.pauseat = int10(url.get('pauseat'));
    this.turn4stats = 100;
  }
}
var cfg = new Cfg(urlParams, selfParams);

// global vars for constant things, good for shorter names in formulas

var FW = 600;  if(urlParams.get('FW')>0) FW = int10(urlParams.get('FW'));  // field width
var FH = 350;  if(urlParams.get('FH')>0) FH = int10(urlParams.get('FH'));  // field height
var FD =   3;  if(urlParams.get('FD')>0) FD = int10(urlParams.get('FD'));  // field depth
var LF = 0.9;  if(urlParams.get('LF')>0) LF = parseFloat(urlParams.get('LF'));  // initially filled piece
var Rseed = int10(urlParams.get('rseed') || selfParams.get('rseed'));  // seed for PRNG (Rules)
var Fseed = int10(urlParams.get('fseed') || selfParams.get('fseed'));  // seed for PRNG (Field)
var NM = int10(urlParams.get('muta')) || 0;  // number of random mutations in rules

var Family = urlParams.get('family') || 'conway';
var Ruleset = urlParams.get('ruleset') || selfParams.get('ruleset');  // encoded or named rules (physics)

var RB = 2;  // number of states for cell
var Rgeom = '1+8+2';  // neighborhood geometry (see RG)
var Rsymm = '8p';  // symmetry of rules (rotational, parity, etc)

// FAMILY-SPECIFIC ////////////////////////////////////////////////////////////////

if(Family=='conway') {
  RB = 2;
  Rgeom = '1+8+2';
  Rsymm = '8p';
  if(!Ruleset) Ruleset = 'Aphrodite';
}
else if(Family=='langton') {
  FD = 2;
  RB = 5;
  Rgeom = '1+4+2';
  Rsymm = '4v';
  if(!Ruleset) Ruleset = 'Langton';
}

if(cfg.debug) {
  cfg.paused = 1;
  cfg.maxfps = 1;
  cfg.turn4stats = 1;
  FW = 10;  FH = 5;
  Ruleset = 'Debug';
}

if(RB<=3) cfg.drawrules = 1;

//  ////////////////////////////////////////////////////////////////