// GET PARAMS ////////////////////////////////////////////////////////////////

if(!glFamily) throw new Error('Family info required!');

// get GET params
const URL = new URLSearchParams(window.location.search);
// get script self address
var scripts = document.getElementsByTagName('script');
var myScript = scripts[scripts.length - 1];
const SLF = new URLSearchParams(myScript.src);

function int10(x) { if(!x) return 0;  return parseInt(x, 10); }

function GetIntParam(nm, def=0)  { return int10(SLF.get(nm)   || URL.get(nm) || def);    }
function GetStrParam(nm, def='') { return       SLF.get(nm)   || URL.get(nm) || def;    }
function GetBoolParam(nm)        { return      (SLF.get(nm)>0 || URL.get(nm)>0) ? 1 : 0; }

class Cfg {
  constructor() {
    this.debug     = GetIntParam('debug');
    this.autore    = GetBoolParam('autore');
    this.rerun     = GetBoolParam('rerun');
    this.anyrand   = GetBoolParam('anyrand');
    this.repair    = GetBoolParam('repair');
    
    this.paused    = GetBoolParam('paused');
    this.pausestat = GetBoolParam('pausestat');
    this.pauseat   = GetIntParam('pauseat');
    
    this.maxfps    = GetIntParam('maxfps', 60);  // Calc framerate limit
    this.showiter  = GetIntParam('showiter');  if(this.showiter<2) this.showiter = 0;  // Show once per showiter Calcs
    
    this.drawrules = GetBoolParam('drawrules');
    this.calcorga  = GetBoolParam('calcorga');
    
    this.randrules = GetBoolParam('randrules');
    this.nmuta     = GetIntParam('nmuta');  // number of random mutations in rules
    
    this.turn4stats = 100;  // not recommended to change
  }
}
var cfg = new Cfg();

// global vars for constant things, good for shorter names in formulas

const Family = GetStrParam('family', glFamily.name);   // name of rule's family
const FM     = GetIntParam('fm',     glFamily.id);
const Mode   = GetStrParam('mode',   glFamily.mode);   // CA modes: ''=classic, PRT=partitioning, MVM=movement, BND=bonds
const TT     = GetIntParam('TT',     glFamily.TT);     // number of moments of time involved in dynamics (2 or 3)
const RB     = GetIntParam('RB',     glFamily.RB);     // number of states for cell
const Rgeom  = GetIntParam('Rgeom',  glFamily.Rgeom);  // neighborhood geometry (see RG)
const Rsymm  = GetIntParam('Rsymm',  glFamily.Rsymm);  // symmetry of rules (rotational, parity, etc)
const FD     = GetIntParam('FD', int10(glFamily.FD) || 3);  // field depth (number of layers)
const FW     = GetIntParam('FW', 600);  // field width
const FH     = GetIntParam('FH', 350);  // field height
const LF     = GetIntParam('LF',  90) / 100;  // initially filled piece size
const LD     = GetIntParam('LD', 100) / 100;  // initially filled piece density
const Lstep  = 10;  // setting to 100 will break previously saved fseeds

var Notaset = GetStrParam('notaset');  // encoded or named rules
if(Mutaset===undefined) var Mutaset = GetStrParam('mutaset');  // encoded mutation (thinner tuning of rules)
var Named = GetStrParam('named');
var Rseed = GetIntParam('rseed');  // seed for PRNG (Rules)
var Fseed = GetIntParam('fseed');  // seed for PRNG (Field)

// MVM PARAMS ////////////////////////////////////////////////////////////////

const mL = 10000,  mL2 = 2 * mL,  mL3 = 3 * mL,  fmL = mL + `.`;  // mL = half-radius of the cell
const mR = 3 * mL - 1;  // maximum range atom's coords can be from the center of it's cell
const mV = mL / 2;  // maximum atom's speed (per coord)

// BND PARAMS ////////////////////////////////////////////////////////////////

const Tmprtr = GetIntParam('tmprtr', 30);  // temperature

// RERUN MODE ////////////////////////////////////////////////////////////////

rerun_n = 0;  rerun_gr_id = 0;
if(cfg.rerun) {
  function GetRerun() {
    if(!gl_reruns[rerun_n]) return false;
    [rerun_gr_id, Notaset, Mutaset, Fseed] = gl_reruns[rerun_n];
    if(!rerun_gr_id) return false;
    console.log('rerun:', rerun_n, rerun_gr_id, Notaset, Mutaset, Fseed);
    rerun_n ++;
    return true;
  }
  var rerun_go = GetRerun();
  if(!rerun_go) alert('Rerun finished');
}

// ANYRAND MODE ////////////////////////////////////////////////////////////////

anyrand_n = 0; anyrand_named = '';
if(cfg.anyrand) {
  function GetAnyrand() {
    if(!gl_cleannamed[anyrand_n]) return false;
    [anyrand_named, Notaset] = gl_cleannamed[anyrand_n];
    if(!Notaset) return false;
    console.log('anyrand:', anyrand_n, anyrand_named, Notaset);
    anyrand_n ++;
    return true;
  }
  var anyrand_go = GetAnyrand();
  if(!anyrand_go) alert('Anyrand error!');
}

// REPAIR MODE ////////////////////////////////////////////////////////////////

repair_n = 0; repair_id = '';
if(cfg.repair) {
  function GetRepair() {
    if(!gl_repair[repair_n]) return false;
    [repair_id, Rseed, cfg.nmuta] = gl_repair[repair_n];
    if(!Rseed) return false;
    //console.log('repair:', repair_n, repair_id, Rseed);
    repair_n ++;
    return true;
  }
  var repair_go = GetRepair();
  if(!repair_go) alert('Repair finished!');
}

// DEBUG MODE ////////////////////////////////////////////////////////////////

if(cfg.debug) {
  cfg.paused = 1;
  //cfg.maxfps = 1;
  cfg.turn4stats = 1;
}

if(RB<=3) cfg.drawrules = 1;

// RELOAD PAGE ////////////////////////////////////////////////////////////////

reloading = false;
function ReloadPage() {
  reloading = true;
  setTimeout(function() { window.location.reload(); }, 100);
}

// DEBUGGER ////////////////////////////////////////////////////////////////

function print_r(arr, level=0) {
  var print = '';
  var level_padding = '';
  for(var j=0; j<=level; j++) level_padding += "    ";
  if(typeof(arr)=='object') {
    for(var key in arr) {
      var value = arr[key];
      if(typeof(value) == 'object') {
        print += level_padding + key + ":\n";
        print += print_r(value, level+1);
      } 
      else {
        print += level_padding + key + " => '" + value + "'\n";
      }
    }
  }
  else {
    print = "(" + typeof(arr) + "): " + "'" + arr + "'";
  }
  
  if(level==0) {
    var printcont = document.createElement('div');
    printcont.style.cssText = '';
    printcont.innerHTML = '<pre>' + print + '</pre>';
    glcont.append(printcont);
  }
  
  return print;
}

function sleep(milliseconds) {
  const date = Date.now();
  let currentDate = null;
  do {
    currentDate = Date.now();
  } while (currentDate - date < milliseconds);
}

//  ////////////////////////////////////////////////////////////////