var RandomRulesetJSON = '';

function KLPNumberParam(name, fallback) {
  var value = Number(GetStrParam(name, String(fallback)));
  return Number.isFinite(value) ? value : fallback;
}

var KLPPhysics = {
  dt: KLPNumberParam('dt', 0.20),
  focus: KLPNumberParam('focus', 1.00),
  maxMove: KLPNumberParam('maxmove', 2.00),
  densities: [1.00, 1.40, 0.70, 1.00],
  repulsions: [5.00, 8.00, 3.00, 5.00],
  densityFloor: 0.0001,
  initialSpeed: KLPNumberParam('speed', 0.35),
  thermalEnergy: KLPNumberParam('thermal', 0.05),
  particleStatThreshold: KLPNumberParam('particleat', 0.90),
  momentumGraphOffsetX: 800,
  momentumGraphOffsetY: 1200
};

function InitRules() {
  if(typeof(CalcProgram)==='undefined' || !CalcProgram) return false;
  gl.useProgram(CalcProgram);
  gl.uniform1f(CalcProgram.location.u_dt, KLPPhysics.dt);
  gl.uniform1f(CalcProgram.location.u_focus, KLPPhysics.focus);
  gl.uniform1f(CalcProgram.location.u_maxMove, KLPPhysics.maxMove);
  gl.uniform1fv(
    CalcProgram.location.u_density,
    KLPPhysics.densities.slice(0,FD)
  );
  gl.uniform1fv(
    CalcProgram.location.u_repulsion,
    KLPPhysics.repulsions.slice(0,FD)
  );
  RandomRulesetJSON = JSON.stringify(KLPPhysics);
  return true;
}
