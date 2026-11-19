function StatAccumulateCell(x, y, z) {
  var fulal = 0.4;  // considered filly alive
  var c0 = GetCell(x, y, 0);
  var c1 = GetCell(x, y, 1);
  var c2 = GetCell(x, y, 2);
  
  if(Number.isNaN(c1.b) || Number.isNaN(c1.a)) {
    if(nErr<10) console.log(nErr, 'NaN:', nturn, x, y, ':', c1);
    nErr ++;
    return;
  }
  
  graphnums[graphstep][0][0] += c0.a>=fulal ? 1 : 0;
  graphnums[graphstep][0][1] += c0.r>=fulal ? 1 : 0;
  graphnums[graphstep][0][2] += c0.g>=fulal ? 1 : 0;
  graphnums[graphstep][0][3] += c0.b>=fulal ? 1 : 0;
  if(c0.r>=fulal || c0.g>=fulal || c0.b>=fulal) rec[S1].livecells[0] ++;
  
  graphnums[graphstep][1][0] += c0.a / fulal;
  graphnums[graphstep][1][1] += c0.r / fulal;
  graphnums[graphstep][1][2] += c0.g / fulal;
  graphnums[graphstep][1][3] += c0.b / fulal;
  
  var mass = c0.r*MS[0] + c0.g*MS[1] + c0.b*MS[2] + c0.a*MS[3];  // total mass
  graphnums[graphstep][2][0] += 50 * mass * c1.b;  // 1 + momentum.x
  graphnums[graphstep][2][1] += 50 * mass * c1.a;  // 1 + momentum.y
  graphnums[graphstep][2][2] += mass * (sqr(c1.b) + sqr(c1.a));  // kinetic energy
  graphnums[graphstep][2][3] += mass * (sqr(c1.b) + sqr(c1.a)) + c2.a;  // total energy
  
  if(mass * c1.a < -0.7) {
    if(nErr<10) console.log(nErr, 'vcy<<0:', nturn, x, y, ':', mass * c1.a, mass, c1.b, c1.a);
    nErr ++;
  }
}

function StatAfterGraph() { StatAutoreByAmount(1); }
function StatSkipCommon() { return true; }
