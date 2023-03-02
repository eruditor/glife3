function InitialFiller() {
  if(cfg.debug==1) {
    var x = 5;
    var y = floor(FH / 2);
    
    for(var i in RG) {
      var rg = RG[i];
      SetCell(x+rg[0], y+rg[1], 0,   (1 << i), 0, 0, (1 << 1) + 1);
    }
    
    for(var i=0; i<=30; i++) {
      SetCell(12 + 2 * floor(i / FH),  i % FH, 0,   i, 0, 0, (1 << 1) + 1);
    }
    
    SetCell(7,  3, 0,   255, 0, 0, (1 << 1) + 1);
    
    /*
    SetCell( 1, 1, 0, 0, 0, 0, 1);
    SetCell( 2, 2, 0, 0, 0, 0, 2);
    SetCell( 3, 3, 0, 0, 0, 0, 3);
    SetCell( 4, 4, 0, 0, 0, 0, 4);
    SetCell( 5, 5, 0, 0, 0, 0, 5);
    SetCell( 6, 6, 0, 0, 0, 0, 6);
    SetCell( 7, 7, 0, 0, 0, 0, 7);
    
    SetCell( 1, 2, 0, 0, 0, 0, 2);
    SetCell( 1, 3, 0, 0, 0, 0, 3);
    SetCell( 1, 4, 0, 0, 0, 0, 4);
    SetCell( 1, 5, 0, 0, 0, 0, 5);
    SetCell( 1, 6, 0, 0, 0, 0, 6);
    SetCell( 1, 7, 0, 0, 0, 0, 7);
    */
    console.log(RG);
  }
  else if(cfg.debug==2) {
    SetCell( 2, 6, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell( 6, 5, 0, 0, 0, 0, (3 << 1) + 1);
    SetCell(14, 5, 0, 0, 0, 0, (2 << 1) + 1);
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
            var v = RB>2 ? rndF(1, RB) : 1;
            var speed = rndF(0, 7);
            SetCell(x, y, z,   (1 << speed), 0, 0, (1 << 1) + 1);
          }
        }
      }
    }
  }
}
