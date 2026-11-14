const KLP_POINT_DENSITY = GetIntParam('n',128)/(FW*FH);

function InitialFiller() {
  var mass = KLPPhysics.pointMass;
  var localizationEnergy = 0.25*KLPPhysics.focus*KLPPhysics.focus
    /(KLPPhysics.dt*KLPPhysics.dt);
  for(var x=0; x<FW; x++) {
    for(var y=0; y<FH; y++) {
      if(Frand32()>=KLP_POINT_DENSITY) continue;

      var angle = 2*Math.PI*Frand32();
      var speed = KLPPhysics.initialSpeed*(0.35+0.65*Frand32());
      var vx = speed*Math.cos(angle);
      var vy = speed*Math.sin(angle);
      var energy = mass*(
        KLPPhysics.thermalEnergy
        + localizationEnergy
        + 0.5*(vx*vx+vy*vy)
      );
      SetCell(x,y,0,mass,mass*vx,mass*vy,energy);
    }
  }
}
