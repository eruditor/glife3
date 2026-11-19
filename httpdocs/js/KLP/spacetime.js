function InitialFiller() {
  var cellCount = FW*FH;
  var particleCount = Math.min(
    Math.max(GetIntParam('n',128),0),
    cellCount
  );
  var cells = new Array(cellCount);
  for(var i=0; i<cellCount; i++) cells[i] = i;

  for(var i=0; i<particleCount; i++) {
    var j = i+floor(Frand32()*(cellCount-i));
    var index = cells[j];
    cells[j] = cells[i];

    var x = index%FW;
    var y = floor(index/FW);
    var angle = 2*Math.PI*Frand32();
    var speed = KLPPhysics.initialMaxSpeed*Math.sqrt(Frand32());
    var vx = speed*Math.cos(angle);
    var vy = speed*Math.sin(angle);

    SetCell(x,y,0,1,0,0,0);
    SetCell(x,y,1,0.5,0.5,vx,vy);
  }
}
