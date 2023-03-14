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
  }
  else if(cfg.debug==2) {
    SetCell(1, 1, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(5, 3, 0,   (1 << 3), 0, 0, (1 << 1) + 1);
    SetCell(3, 5, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    
    SetCell(3, 6, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(7, 8, 0,   (1 << 3), 0, 0, (1 << 1) + 1);
    SetCell(5,10, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    
    SetCell( 8, 2, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell( 9, 2, 0,   (1 << 2), 0, 0, (1 << 1) + 1);
    SetCell(10, 4, 0,   (1 << 4), 0, 0, (1 << 1) + 1);
    SetCell( 9, 4, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    
    SetCell( 9, 6, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(10, 6, 0,   (1 << 2), 0, 0, (1 << 1) + 1);
    SetCell(11, 8, 0,   (1 << 4), 0, 0, (1 << 1) + 1);
    SetCell(10, 8, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    
    SetCell(12, 1, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(14, 3, 0,   (1 << 0), 0, 0, (1 << 1) + 1);
    
    SetCell(14, 5, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(15, 7, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    
    SetCell(18, 5, 0,   (1 << 7), 0, 0, (1 << 1) + 1);
    SetCell(19, 5, 0,   (1 << 7), 0, 0, (1 << 1) + 1);
    SetCell(17, 4, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(18, 4, 0,   (1 << 2), 0, 0, (1 << 1) + 1);
  }
  else if(cfg.debug==3) {
    SetCell(2, 1, 0,   (1 << 0), 0, 0, (1 << 1) + 1);
    SetCell(3, 6, 0,   (1 << 1), 0, 0, (1 << 1) + 1);
    SetCell(4, 6, 0,   (1 << 2), 0, 0, (1 << 1) + 1);
    SetCell(5, 6, 0,   (1 << 3), 0, 0, (1 << 1) + 1);
    SetCell(6, 6, 0,   (1 << 4), 0, 0, (1 << 1) + 1);
    SetCell(7, 6, 0,   (1 << 5), 0, 0, (1 << 1) + 1);
    SetCell(8, 6, 0,   (1 << 6), 0, 0, (1 << 1) + 1);
    SetCell(8, 2, 0,   (1 << 7), 0, 0, (1 << 1) + 1);
  }
  else if(cfg.shape==1) {
    for(var z=0; z<FD; z++) {
      for(var x=0; x<FW; x++) {
        for(var y=0; y<FH; y++) {
          var density = 1;
          if(rndF(0,Lstep)<density) {
            var speed = rndF(0, 7);
            SetCell(x, y, z,   (1 << speed), 0, 0, (1 << 1) + 1);
          }
        }
      }
    }
    
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
            if(1) {  // wind to the right
              if(speed==2 || speed==3 || speed==4) speed = rndF(0, 7);
              if(speed==3) speed = rndF(0, 7);
            }
            SetCell(x, y, z,   (1 << speed), 0, 0, (1 << 1) + 1);
          }
        }
      }
    }
    
    z = 0;
    for(var x=FW-100; x<FW-20; x++) {
      for(var y=round(FH/2-40); y<round(FH/2+0); y++) {
        SetCell(x, y-FH/4, z,   (1 << 7), 0, 0, (1 << 1) + 1);
        SetCell(x, y-FH/4+80, z,   (1 << 7), 0, 0, (1 << 1) + 1);
      }
    }
    
    y = 0;
    for(var x=0; x<FW; x++) {
      SetCell(x, y, z,   (1 << 7), 0, 0, (1 << 1) + 1);
    }
  }
  else {
    // global filler
    var x, y, z=0;
    for(x=0; x<FW; x++) {
      for(y=0; y<FH; y++) {
        if(rndF(0,1000)<50) {
          var speed = rndF(0, 7);
          SetCell(x, y, z,   (1 << speed), 0, 0, (1 << 1) + 1);
        }
      }
    }
    // round
    var speedbits = new Array(7);
    for(x=0; x<FW; x++) {
      for(y=0; y<FH; y++) {
        var dx = (x-FW/2)+FW/5;
        var dy = (y-FH/2);
        if(abs(dx)>FW/4 || abs(dy)>FH/4) continue;
        var density = 1000 - 1000*abs(dx)/FW - 1000*abs(dy)/FH;
        var d = rndF(1,1000);
        if(d<density) {
          var nn = density / d;  if(nn>6) nn = 6;
          speedbits.fill(0);
          for(var sp=0; sp<=nn; sp++) {
            var speed = rndF(0, 7);
            if(1) {  // wind to the right
              if(speed==2 || speed==3 || speed==4) speed = rndF(0, 7);
              if(speed==3) speed = rndF(0, 7);
            }
            speedbits[speed] = 1;
          }
          var speedbyte = 0;
          for(var k=0; k<=6; k++) {
            if(speedbits[k]>0) speedbyte += (1 << k);
          }
          SetCell(x, y, z,   speedbyte, 0, 0, (1 << 1) + 1);
        }
      }
    }
    // obstacles
    for(x=FW/2+FW/8; x<FW/2+FW/8+FW/8; x++) {
      for(y=FH/2-FH/30; y<FH/2+FH/30; y++) {
        SetCell(x, y     , z,   (1 << 7), 0, 0, (1 << 1) + 1);
        SetCell(x, y-4*FH/30, z,   (1 << 7), 0, 0, (1 << 1) + 1);
        if(x>FW/2+FW/8+FW/16) SetCell(x, y-2*FH/30, z,   (1 << 7), 0, 0, (1 << 1) + 1);
      }
    }
    // floor
    for(var x=0; x<FW; x++) {
      SetCell(x, 0, z,   (1 << 7), 0, 0, (1 << 1) + 1);
    }
  }
}
