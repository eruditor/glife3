statLegend =
    "<span style='background:rgb(240,240,240)'>&nbsp;</span> total mass &nbsp; "
  + "<span style='background:rgb(250,80,80)  '>&nbsp;</span> Px + " + KLPPhysics.momentumGraphOffsetX + " &nbsp; "
  + "<span style='background:rgb(80,250,80)'  >&nbsp;</span> Py + " + KLPPhysics.momentumGraphOffsetY + " &nbsp; "
  + "<span style='background:rgb(80,120,250)' >&nbsp;</span> total energy &nbsp; "
  + "<span style='background:rgb(250,220,60)' >&nbsp;</span> dense cells (> "
  + KLPPhysics.particleStatThreshold + " of layer density)";

var klpMass = 0, klpPx = 0, klpPy = 0, klpEnergy = 0, klpParticles = 0;

function StatBeforeCells() {
  klpMass = 0;  klpPx = 0;  klpPy = 0;  klpEnergy = 0;  klpParticles = 0;
}

function StatAccumulateCell(x, y, z) {
  var c0 = GetCell(x, y, z);
  klpMass += c0.r;
  klpPx += c0.g;
  klpPy += c0.b;
  klpEnergy += c0.a;
  if(c0.r>0) {
    klpEnergy += 0.5*(c0.g*c0.g+c0.b*c0.b)/c0.r;
  }
  if(c0.r > KLPPhysics.particleStatThreshold*KLPPhysics.densities[z]) klpParticles ++;
  if(c0.r > KLPPhysics.densityFloor) rec[S1].livecells[z] ++;
}

function StatAfterCells() {
  graphnums[graphstep][0][0] = klpMass;
  graphnums[graphstep][0][1] = klpPx;
  graphnums[graphstep][0][2] = klpPy;
  graphnums[graphstep][0][3] = klpEnergy;
  graphnums[graphstep][0][4] = klpParticles;
}

function StatGraphLayers() { return 1; }
function StatGraphSeries() { return 5; }

function StatPrepareGraphValue(z, v, val) {
  if(v==1) val += KLPPhysics.momentumGraphOffsetX;
  if(v==2) val += KLPPhysics.momentumGraphOffsetY;
  return val<0 ? -val : val;
}

function StatGraphColor(z, v) {
  if(v==4) return {r:250,g:220,b:60};
  return Color4Cell(z, v);
}

function StatSkipCommon() { return true; }
