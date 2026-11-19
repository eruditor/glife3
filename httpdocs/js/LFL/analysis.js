function StatAccumulateCell(x, y, z) {
  var fulal = 0.7;  // considered filly alive
  var c0 = GetCell(x, y, 0);
  if(z==0) {
    if(c0.r>=0.99 && c0.g>=0.99 && c0.b>=0.99) return;  // ignoring artificial static white borders
    graphnums[graphstep][z][0] += c0.r>=fulal || c0.g>=fulal || c0.b>=fulal ? 1 : 0;
    graphnums[graphstep][z][1] += c0.r>=fulal ? 1 : 0;
    graphnums[graphstep][z][2] += c0.g>=fulal ? 1 : 0;
    graphnums[graphstep][z][3] += c0.b>=fulal ? 1 : 0;
    if(c0.r>=fulal || c0.g>=fulal || c0.b>=fulal) rec[S1].livecells[z] ++;
  }
  else if(z==1) {
    if(1) {  // total amount
      graphnums[graphstep][z][1] += c0.r / fulal;
      graphnums[graphstep][z][2] += c0.g / fulal;
      graphnums[graphstep][z][3] += c0.b / fulal;
    }
    else {  // amount of negative values
      graphnums[graphstep][z][1] += c0.r<0 ? -c0.r : 0;
      graphnums[graphstep][z][2] += c0.g<0 ? -c0.g : 0;
      graphnums[graphstep][z][3] += c0.b<0 ? -c0.b : 0;
    }
  }
}

function StatAfterGraph() { StatAutoreByAmount(1); }
function StatSkipCommon() { return true; }
