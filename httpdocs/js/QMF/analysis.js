function StatAccumulateCell(x, y, z) {
  if(z!=0) return;
  var c0 = GetCell(x, y, 0);
  graphnums[graphstep][0][1] += c0.r;
  graphnums[graphstep][0][2] += c0.g;
  graphnums[graphstep][0][3] += c0.b;

  if(c0.r+c0.g+c0.b>0.5) {
    var c1 = GetCell(x, y, 1);
    var mass = c0.r*QMFPhysics.masses[0]
             + c0.g*QMFPhysics.masses[1]
             + c0.b*QMFPhysics.masses[2];
    var graphScale = 1000;
    graphnums[graphstep][1][1] += graphScale * mass * c1.b;  // momentum.x
    graphnums[graphstep][1][2] += graphScale * mass * c1.a;  // momentum.y
    graphnums[graphstep][1][3] += graphScale * 0.5 * mass * (sqr(c1.b) + sqr(c1.a));

    var mask = Math.floor(c0.a + 0.5) % 1024;
    for(var d of [[1,0,5], [-1,1,6], [0,1,7], [1,1,8]]) {
      var nx = (x + d[0] + FW) % FW;
      var ny = (y + d[1] + FH) % FH;
      var other = GetCell(nx, ny, 0);
      if(other.r+other.g+other.b<=0.5) continue;

      var otherMotion = GetCell(nx, ny, 1);
      var distance = Math.hypot(
        d[0] + otherMotion.r - c1.r,
        d[1] + otherMotion.g - c1.g
      );
      if(mask & (1 << d[2])) {
        var stretch = distance - QMFPhysics.bondLength;
        var bondWidth = QMFPhysics.bondRange - QMFPhysics.bondLength;
        graphnums[graphstep][1][3] += graphScale * QMFPhysics.spring * (
          0.5*sqr(stretch) - stretch*stretch*stretch/(3*bondWidth)
          - sqr(bondWidth)/6
        );
      }
      else if(distance<QMFPhysics.repulsionRange) {
        graphnums[graphstep][1][3] += graphScale * 0.5 * QMFPhysics.repulsion
          * sqr(QMFPhysics.repulsionRange - distance);
      }
    }
  }
}

function StatAfterGraph() { StatAutoreByAmount(0); }
function StatSkipCommon() { return true; }
