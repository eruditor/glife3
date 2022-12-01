function InitialFiller() {
  if(cfg.debug==1) {
    SetCell(15, 5, 0, 0, 0, 0, (1 << 8) + (2 << 1) + 1);
    SetCell( 7, 2, 0, 0, 0, 0, (0 << 8) + (2 << 1) + 1);
    SetCell( 2, 6, 0, 0, 0, 0, (1 << 8) + (1 << 1) + 1);
    SetCell( 6, 5, 0, 0, 0, 0, (0 << 8) + (3 << 1) + 1);
  }
  else if(cfg.debug==2) {
    SetCell( 2, 6, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell( 6, 5, 0, 0, 0, 0, (3 << 1) + 1);
    SetCell(14, 5, 0, 0, 0, 0, (2 << 1) + 1);
  }
  else {
    var lstep = 1000;
    for(var z=0; z<FD; z++) {
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          if(rndF(0,lstep)<100*LD) {
            var rv = rndF(0, 1000);
            var v = (rv>=1000/2 ? 1 : (rv>=1000/4 ? 2 : (rv>=1000/8 ? 3 : 0)));
            if(v>=RB) v = RB - 1;
            if(v==0) continue;
            var spin = rndF(0, 2);
            var dir = rndF(0,100)<Tmprtr ? rndF(1, 5) : 0;
            var k = dir>0 ? rndF(10, 800) : 0;
            SetCell(x, y, z, 0, (k << 16) + (dir << 0), 0, (spin << 8) + (v << 1) + 1);
          }
        }
      }
    }
  }
}