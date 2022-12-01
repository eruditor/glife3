function InitialFiller() {
  if(cfg.debug==1) {
    SetCell(2, 2, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(7, 2, 0, 4, 0, 0, (2 << 1) + 1);
    SetCell(2, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(6, 4, 0, 0, 0, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==2) {
    SetCell(4, 4, 0, 1, 0, 0, (1 << 1) + 1);
    SetCell(2, 3, 0, 2, 0, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==3) {
    SetCell(3, 4, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 3, 0, 0, 0, 0, (2 << 1) + 1);
    SetCell(3, 2, 0, 0, 0, 0, (2 << 1) + 1);
    SetCell(5, 2, 0, 4, 0, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==4) {
    SetCell(3, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(4, 3, 0, 2, 0, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==5) {
    SetCell(3, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 4, 0, 2, 0, 0, (2 << 1) + 1);
  }
  else if(cfg.debug==6) {
    SetCell(3, 2, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(2, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 4, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 3, 0, 2, 0, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==7) {
    SetCell(3, 2, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(2, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(3, 3, 0, 0, 0, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==8) {
    SetCell(3, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(3, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 2, 0, 0, 0, 0, (3 << 1) + 1);
  }
  else if(cfg.debug==9) {
    SetCell(2, 2, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(2, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 2, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(3, 3, 0, 0, 0, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==10) {
    SetCell(4, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(4, 3, 0, 0, 0, 0, (2 << 1) + 1);
    SetCell(4, 2, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(6, 3, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(6, 2, 0, 0, 0, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==11) {
    SetCell(2, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(4, 3, 0, 0, 4 << 3, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==12) {
    SetCell(2, 4, 0, 2, 0, 0, (1 << 1) + 1);
    SetCell(3, 4, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(6, 3, 0, 0, 0, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==13) {
    SetCell(2, 2, 0, 0, 0, 0, (2 << 1) + 1);
    SetCell(3, 2, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(1, 2, 0, 2, 0, 0, (1 << 1) + 1);
    
    SetCell(5, 6, 0, 1, 0, 0, (2 << 1) + 1);
    SetCell(6, 6, 0, 0, 0, 0, (1 << 1) + 1);
    SetCell(4, 6, 0, 0, 0, 0, (1 << 1) + 1);
  }
  else if(Fseed<10) {
    var ii = FW * FH * LD / 100;
    for(var i=1; i<ii; i++) {
      var x = rndF(5, FW-5);
      var y = rndF(5, FH-5);
      var speed = rndF(0,100)<Tmprtr ? rndF(1, 5) : 0;
      
      var busy = false;
      for(var dx=-2; dx<=2; dx++) {
        for(var dy=-2; dy<=2; dy++) {
          if(GetCell(x+dx, y+dy, 0).a!=0) { busy = true;  break; }
        }
      }
      if(busy) continue;
      
      if(Fseed==1) {
        SetCell(x  , y  , 0, speed, 0, 0, (3 << 1) + 1);
        SetCell(x+1, y  , 0,     0, 0, 0, (1 << 1) + 1);
        SetCell(x-1, y  , 0,     0, 0, 0, (1 << 1) + 1);
        SetCell(x  , y+1, 0,     0, 0, 0, (1 << 1) + 1);
        SetCell(x  , y-1, 0,     0, 0, 0, (1 << 1) + 1);
      }
      else if(Fseed==2) {
        SetCell(x  , y  , 0, speed, 0, 0, (2 << 1) + 1);
        SetCell(x+1, y  , 0,     0, 0, 0, (1 << 1) + 1);
        SetCell(x-1, y  , 0,     0, 0, 0, (1 << 1) + 1);
      }
    }
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
            var speed = rndF(0,100)<Tmprtr ? rndF(1, 5) : 0;
            SetCell(x, y, z, speed, 0, 0, (v << 1) + 1);
          }
        }
      }
    }
  }
}