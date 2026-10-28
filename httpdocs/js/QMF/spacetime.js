function InitialFiller() {
  for(var x=0; x<FW; x++) {
    for(var y=0; y<FH; y++) {
      if(rndF(0,100)>7) continue;

      // One cell contains one atom: H, O, or C (stored in r, g, b).
      var atom = rndF(0,100);
      var h = atom<50 ? 1 : 0;
      var o = atom>=50 && atom<80 ? 1 : 0;
      var c = atom>=80 ? 1 : 0;

      SetCell(
        x, y, 0,
        h, o, c,
        0
      );
      SetCell(
        x, y, 1,
        (rndF(0,701)-350) / 1000,
        (rndF(0,701)-350) / 1000,
        (rndF(0,61)-30) / 1000,
        (rndF(0,61)-30) / 1000
      );
    }
  }
}
