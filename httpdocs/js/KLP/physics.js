var RandomRulesetJSON = '';

function KLPNumberParam(name, fallback) {
  var value = Number(GetStrParam(name, String(fallback)));
  return Number.isFinite(value) ? value : fallback;
}

var KLPPhysics = {
  dt: KLPNumberParam('dt', 0.20),
  focus: KLPNumberParam('focus', 1.00),
  maxMove: KLPNumberParam('maxmove', 2.00),
  densityFloor: 0.0001,
  pointMass: KLPNumberParam('mass', 1.00),
  initialSpeed: KLPNumberParam('speed', 0.35),
  thermalEnergy: KLPNumberParam('thermal', 0.05),
  momentumGraphOffset: 1000
};

function InitRules() {
  if(typeof(CalcProgram)==='undefined' || !CalcProgram) return false;
  gl.useProgram(CalcProgram);
  gl.uniform1f(CalcProgram.location.u_dt, KLPPhysics.dt);
  gl.uniform1f(CalcProgram.location.u_focus, KLPPhysics.focus);
  gl.uniform1f(CalcProgram.location.u_maxMove, KLPPhysics.maxMove);
  RandomRulesetJSON = JSON.stringify(KLPPhysics);
  return true;
}
