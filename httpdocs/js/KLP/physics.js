var RandomRulesetJSON = '';

function KLPNumberParam(name, fallback) {
  var value = Number(GetStrParam(name, String(fallback)));
  return Number.isFinite(value) ? value : fallback;
}

var KLPPhysics = {
  dt: KLPNumberParam('dt', 1.00),
  initialMaxSpeed: Math.min(
    Math.abs(KLPNumberParam('speed', 0.10)),
    0.099999
  ),
  momentumGraphOffsetX: 800,
  momentumGraphOffsetY: 1200
};

function InitRules() {
  if(typeof(CalcProgram)==='undefined' || !CalcProgram) return false;
  gl.useProgram(CalcProgram);
  gl.uniform1f(CalcProgram.location.u_dt, KLPPhysics.dt);
  RandomRulesetJSON = JSON.stringify(KLPPhysics);
  return true;
}
