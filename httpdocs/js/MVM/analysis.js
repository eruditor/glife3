var mvmTmp = [];

function StatBeforeCells() {
  mvmTmp = [];
}

function StatAccumulateCell(x, y, z) {
  var cell = GetCell(x, y, z);
  var al = DataFormat=='UI8' ? (cell.a > 0 ? 1 : 0) : (cell.a & 1);
  var v = DataFormat=='UI8' ? cell.a : (al ? cell.a << 27 >>> 28 : 0);
  if(al==0) return;
  
  var vx = ((cell.r >>> 16) - 32768) / 200;
  var vy = ((cell.g >>> 16) - 32768) / 200;
  var am = v; // @ sync with shader!
  var KE = am * (vx*vx + vy*vy) / 2;
  graphnums[graphstep][z][v] += 1000 * KE;
  if(!mvmTmp[v]) mvmTmp[v] = 0;
  mvmTmp[v] ++;
  
  StatMarkLive(x, y, z);
}

function StatAfterLayer(z) {
  graphnums[graphstep][z][0] = 0;
  for(var v=1; v<RB; v++) {
    if(mvmTmp[v]>0) graphnums[graphstep][z][v] /= mvmTmp[v];
    graphnums[graphstep][z][0] += graphnums[graphstep][z][v];
  }
}
