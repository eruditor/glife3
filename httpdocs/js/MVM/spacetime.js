function InitialFiller() {
  if(cfg.debug==1) {
    var vv = 215;
    SetCell( 9, 4, 0, (2000 + 32768) + (vv + 32768) * 65536, (2000 + 32768) + (-vv + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell(10, 5, 0, (   0 + 32768) + ( 0 + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==2) {
    var vv = 215;
    SetCell( 8, 3, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + ( vv + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 8, 5, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
    SetCell( 8, 7, 0, (   0 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (-vv + 32768) * 65536, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==3) {
    var vv = 0;
    SetCell( 9, 4, 0, (-4000 + 32768) + (vv + 32768) * 65536, (   0 + 32768) + (-vv + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell(10, 4, 0, (    0 + 32768) + ( 0 + 32768) * 65536, (   0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==4) {
    var vv = 0;
    SetCell( 9, 4, 0, (-4000 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + ( 50 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell(10, 4, 0, (    0 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + (  0 + 32768) * 65536, 0, (3 << 1) + 1);
    //SetCell(11, 4, 0, ( 4000 + 32768) + ( 0 + 32768) * 65536, (    0 + 32768) + (  0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell(10, 3, 0, (    0 + 32768) + (80 + 32768) * 65536, (-3000 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
    //SetCell(10, 5, 0, (    0 + 32768) + ( 0 + 32768) * 65536, ( 3500 + 32768) + (  0 + 32768) * 65536, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==5) {
    SetCell( 3, 3, 0, (0 + 32768) + (210 + 32768) * 65536, (0 + 32768) + ( 120 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 3, 5, 0, (0 + 32768) + (200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (2 << 1) + 1);
    SetCell( 3, 7, 0, (0 + 32768) + (220 + 32768) * 65536, (0 + 32768) + (-105 + 32768) * 65536, 0, (1 << 1) + 1);
    //SetCell(12, 5, 0, (0 + 32768) + (  0 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==6) {
    SetCell( 4, 5, 0, (0 + 32768) + (220 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 5, 3, 0, (0 + 32768) + (  0 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==7) {
    SetCell( 3, 5, 0, (0 + 32768) + (220 + 32768) * 65536, (-3000 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 5, 5, 0, (0 + 32768) + (  0 + 32768) * 65536, ( 3000 + 32768) + (   0 + 32768) * 65536, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==8) {
    SetCell( 2, 2, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 2, 4, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 2, 6, 0, (0 + 32768) + (-200 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 4, 2, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 4, 4, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 4, 6, 0, (0 + 32768) + (   0 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 6, 2, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + (-200 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 6, 4, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + (   0 + 32768) * 65536, 0, (1 << 1) + 1);
    SetCell( 6, 6, 0, (0 + 32768) + ( 200 + 32768) * 65536, (0 + 32768) + ( 200 + 32768) * 65536, 0, (1 << 1) + 1);
  }
  else {
    var lstep = 1000;
    var speedlimit = round(mL/100);
    for(var z=0; z<FD; z++) {
      for(var x=round(FW/2-FW*LF/2); x<round(FW/2+FW*LF/2); x++) {
        for(var y=round(FH/2-FH*LF/2); y<round(FH/2+FH*LF/2); y++) {
          if(y<0 || y>=FH) continue;
          if(rndF(0,lstep)<100*LD) {
            var rv = rndF(0, 1000);
            var v = (rv>=1000/2 ? 1 : (rv>=1000/4 ? 2 : (rv>=1000/8 ? 3 : 0)));
            if(v>=RB) v = RB - 1;
            if(v==0) continue;
            var xx = rndF(-mL+1, mL-1) + 32768;
            var yy = rndF(-mL+1, mL-1) + 32768;
            var vx = rndF(-speedlimit, speedlimit) + 32768;
            var vy = rndF(-speedlimit, speedlimit) + 32768;
            SetCell(x, y, z, xx + vx * 65536, yy + vy * 65536, 0, (v << 1) + 1);
          }
        }
      }
    }
  }
}