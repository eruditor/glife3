const KLP_POINT_DENSITY = GetIntParam('n',128)/(FW*FH);

function InitialFiller() {
  for(var z=0; z<1; z++) {
    var mass = KLPPhysics.densities[z];
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        if(Frand32()>=KLP_POINT_DENSITY) continue;

        var angle = 2*Math.PI*Frand32();
        var speed = KLPPhysics.initialSpeed*(0.35+0.65*Frand32());
        var vx = speed*Math.cos(angle);
        var vy = speed*Math.sin(angle);
        SetCell(x,y,z,mass,mass*vx,mass*vy,0);
      }
    }
  }
}
