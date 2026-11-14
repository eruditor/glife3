const KLP_POINT_COUNT = GetIntParam('n', 64);
var KLPPoints = [];

function KLPRandom(a, b) {
  return a + (b-a)*Frand32();
}

function KLPCreatePoints() {
  var points = [];
  var attempts = 0;
  while(points.length<KLP_POINT_COUNT && attempts<100*KLP_POINT_COUNT) {
    attempts ++;
    var x = rndF(0,FW), y = rndF(0,FH), free = true;
    for(var point of points) {
      var dx = Math.abs(x-point[0]);
      var dy = Math.abs(y-point[1]);
      dx = Math.min(dx,FW-dx);
      dy = Math.min(dy,FH-dy);
      if(dx*dx+dy*dy<=25) {
        free = false;
        break;
      }
    }
    if(!free) continue;

    var angle = KLPRandom(0,2*Math.PI);
    var speed = KLPPhysics.initialSpeed*KLPRandom(0.35,1.00);
    points.push([
      x, y,
      speed*Math.cos(angle),
      speed*Math.sin(angle)
    ]);
  }
  return points;
}

if(FD!=1 || RB<4 || KLP_POINT_COUNT<1) {
  throw new Error('KLP requires FD=1, RB>=4 and n>=1');
}

function InitialFiller() {
  var mass = KLPPhysics.pointMass;
  var localizationEnergy = 0.25*KLPPhysics.focus*KLPPhysics.focus
    /(KLPPhysics.dt*KLPPhysics.dt);
  for(var point of KLPPoints) {
    var vx = point[2], vy = point[3];
    var energy = mass*(
      KLPPhysics.thermalEnergy
      + localizationEnergy
      + 0.5*(vx*vx+vy*vy)
    );
    SetCell(
      point[0],point[1],0,
      mass,mass*vx,mass*vy,energy
    );
  }
}

var KLPInitSpacetime = window.InitSpacetime;
window.InitSpacetime = function() {
  if(
    !Number.isFinite(KLPPhysics.pointMass)
    || KLPPhysics.pointMass<=0
    || !Number.isFinite(KLPPhysics.initialSpeed)
    || KLPPhysics.initialSpeed<0
    || !Number.isFinite(KLPPhysics.dt)
    || KLPPhysics.dt<=0
    || !Number.isFinite(KLPPhysics.focus)
    || KLPPhysics.focus<0
    || KLPPhysics.focus>1
    || !Number.isFinite(KLPPhysics.maxMove)
    || KLPPhysics.maxMove<=0
    || KLPPhysics.maxMove>2
    || !Number.isFinite(KLPPhysics.thermalEnergy)
    || KLPPhysics.thermalEnergy<0
  ) {
    throw new Error(
      'KLP requires mass>0, speed>=0, dt>0, 0<=focus<=1, '
      + '0<maxmove<=2 and thermal>=0'
    );
  }
  KLPPoints = KLPCreatePoints();
  KLPInitSpacetime();
};
