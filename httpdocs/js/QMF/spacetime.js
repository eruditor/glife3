function InitialFiller() {
  for(var x=0; x<FW; x++) {
    for(var y=0; y<FH; y++) {
      if(rndF(0,1000)>1) continue;

      SetCell(
        x, y, 0,
        rndF(0,1000) / 1000,
        rndF(0,1000) / 1000,
        rndF(0,1000) / 1000,
        0
      );
      SetCell(
        x, y, 1,
        0, 0,
        (rndF(0,201)-100) / 1000,
        (rndF(0,201)-100) / 1000
      );
    }
  }
}
