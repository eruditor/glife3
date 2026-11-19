statLegend =
    "<span style='background:rgb(240,240,240)'>&nbsp;</span> total mass &nbsp; "
  + "<span style='background:rgb(250,80,80)  '>&nbsp;</span> Px + " + KLPPhysics.momentumGraphOffsetX + " &nbsp; "
  + "<span style='background:rgb(80,250,80)'  >&nbsp;</span> Py + " + KLPPhysics.momentumGraphOffsetY + " &nbsp; "
  + "<span style='background:rgb(80,120,250)' >&nbsp;</span> total energy &nbsp; "
  + "<span style='background:rgb(250,220,60)' >&nbsp;</span> occupied cells";

var klpMass = 0, klpPx = 0, klpPy = 0, klpEnergy = 0, klpOccupied = 0;

function StatBeforeCells() {
  klpMass = 0;  klpPx = 0;  klpPy = 0;  klpEnergy = 0;  klpOccupied = 0;
}

function StatAccumulateCell(x, y, z) {
  if(z!=0) return;
  var c0 = GetCell(x, y, 0);
  var c1 = GetCell(x, y, 1);
  klpMass += c0.r;
  klpPx += c0.r*c1.b;
  klpPy += c0.r*c1.a;
  klpEnergy += c0.g + 0.5*c0.r*(c1.b*c1.b+c1.a*c1.a);
  if(c0.r > KLPPhysics.densityFloor) {
    klpOccupied ++;
    StatMarkLive(x,y,0);
  }
}

function StatAfterCells() {
  graphnums[graphstep][0][0] = klpMass;
  graphnums[graphstep][0][1] = klpPx;
  graphnums[graphstep][0][2] = klpPy;
  graphnums[graphstep][0][3] = klpEnergy;
  graphnums[graphstep][0][4] = klpOccupied;
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
