var fs_Color4Cell = `
  vec4 Color4Cell(vec4 cell, int layer) {
    float rho = max(cell.r, 0.);
    float brightness = 1. - exp(-2.5*rho);
    if(layer==0) return vec4(0.,brightness,brightness,1.);
    if(layer==1) return vec4(brightness,0.,brightness,1.);
    if(layer==2) return vec4(brightness,brightness,0.,1.);
    return vec4(brightness);
  }
`;
