function InitialFiller() {
  if(cfg.debug==1) {
    SetCell( 5, 2, 0,   0, 0.9,    0,    1);
    SetCell( 5, 2, 1,   0,   0,  0.1, 0.05);
    SetCell(11, 2, 0,   0.8, 0,    0,    1);
    SetCell(11, 2, 1,   0,   0, -0.1, 0.05);
  }
  else {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        var rr = Math.sqrt( (sqr(x/FW-0.5) + sqr(y/FH-0.5)) * 4) / LF;
        var density = round( (1 - rr) * 10000 * LD );
        if(rndF(0,10000)<density) {
          SetCell(x, y, 0,  rndF(1,100)/100, rndF(1,100)/100, rndF(1,100)/100, 1);
          SetCell(x, y, 1,  0, 0, (rndF(1,100)-rndF(1,100))/10000, (rndF(1,100)-rndF(1,100))/10000);
        }
      }
    }
  }
}
