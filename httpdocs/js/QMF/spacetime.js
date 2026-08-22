function InitialFiller() {
  for(var z=0; z<FD; z++) {
    for(var x=0; x<FW; x++) {
      for(var y=0; y<FH; y++) {
        SetCell(
          x, y, z,
          rndF(0, 1000) / 1000,
          rndF(0, 1000) / 1000,
          rndF(0, 1000) / 1000,
          1
        );
      }
    }
  }
}
