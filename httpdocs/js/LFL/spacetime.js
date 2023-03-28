function InitialFiller() {
  if(cfg.debug==1) {
    SetCell(0, 0, 0,   1, 1, 1, 1);
    SetCell(1, 0, 0,   0.5, 0.5, 0.5, 1);
    SetCell(0, 1, 0,   0.5, 0, 0, 1);
  }
  else {
    for(var z=0; z<FD; z++) {
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          if(z>=1 && x>FW/2) continue;
          if(z>=2 && y<FH/2) continue;
          var density = round((1 - Math.abs(2*y/FH-1)/LF) * Lstep * LD);
          if(rndF(0,Lstep)<density) {
            SetCell(x, y, z,  rndF(1,100)/100, rndF(1,100)/100, rndF(1,100)/100, 1);
          }
        }
      }
    }
  }
}
