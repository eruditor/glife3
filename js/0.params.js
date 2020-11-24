// GET PARAMS ////////////////////////////////////////////////////////////////

// get GET params
const URL = new URLSearchParams(window.location.search);
// get script self address
var scripts = document.getElementsByTagName('script');
var myScript = scripts[scripts.length - 1];
const SLF = new URLSearchParams(myScript.src);

function int10(x) { if(!x) return 0;  return parseInt(x, 10); }

function GetIntParam(nm, def=0)  { return int10(URL.get(nm) || SLF.get(nm)) || def;  }
function GetStrParam(nm, def='') { return       URL.get(nm) || SLF.get(nm)  || def;  }
function GetBoolParam(nm)        { return      (URL.get(nm)>0 || SLF.get(nm)>0) ? 1 : 0; }

class Cfg {
  constructor() {
    this.debug = GetBoolParam('debug');
    this.paused = GetBoolParam('paused');
    this.pausestat = GetBoolParam('pausestat');
    this.maxfps = GetIntParam('maxfps', 60);  // Calc framerate limit
    this.showiter = GetIntParam('showiter');  if(this.showiter<2) this.showiter = 0;  // Show once per showiter Calcs
    this.rerun = GetBoolParam('rerun');
    this.drawrules = GetBoolParam('drawrules');
    this.autore = GetBoolParam('autore');
    this.pauseat = GetIntParam('pauseat');
    this.orga = GetBoolParam('orga');
    this.turn4stats = 100;
  }
}
var cfg = new Cfg();

// global vars for constant things, good for shorter names in formulas

var Family  = GetStrParam('family', 'Conway3D');  // name of rule's family
var Notaset = GetStrParam('notaset');  // encoded or named rules
var Mutaset = GetStrParam('mutaset');  // encoded mutation (thinner tuning of rules)

var RB    = GetIntParam('RB',    2);  // number of states for cell
var Rgeom = GetIntParam('Rgeom', 182);  // neighborhood geometry (see RG)
var Rsymm = GetIntParam('Rsymm', 85);  // symmetry of rules (rotational, parity, etc)

var FW = GetIntParam('FW', 600);  // field width
var FH = GetIntParam('FH', 350);  // field height
var FD = GetIntParam('FD',   3);  // field depth (number of layers)

var LF = GetIntParam('LF', 90) / 100;  // initially filled piece (percent)

var Rseed = GetIntParam('rseed');  // seed for PRNG (Rules)
var Fseed = GetIntParam('fseed');  // seed for PRNG (Field)

var NM = GetIntParam('nmuta');  // number of random mutations in rules

// FAMILY-SPECIFIC ////////////////////////////////////////////////////////////////

if(Family=='Conway') {
  RB = 2;
  Rgeom = 18;
  Rsymm = 85;
}
else if(Family=='Conway3D') {
  RB = 2;
  Rgeom = 182;
  Rsymm = 85;
}
else if(Family=='Langton') {
  FD = 2;
  RB = 5;
  Rgeom = 142;
  Rsymm = 47;
}

if(cfg.debug) {
  cfg.paused = 1;
  cfg.maxfps = 1;
  cfg.turn4stats = 1;
  FW = 10;  FH = 5;
  //Notaset = 'Debug';
}

if(RB<=3) cfg.drawrules = 1;

//  ////////////////////////////////////////////////////////////////