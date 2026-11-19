var fs_Color4Cell = `
  vec4 Color4Cell(vec4 cell, int layer) {
    if(layer!=0) return vec4(0.,0.,0.,1.);
    float brightness = 1. - exp(-2.5*max(cell.r,0.));
    return vec4(brightness,brightness,brightness,1.);
  }
`;
