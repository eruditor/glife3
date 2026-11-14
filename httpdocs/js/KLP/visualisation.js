var fs_Color4Cell = `
  vec4 Color4Cell(vec4 cell, int layer) {
    float rho = max(cell.r, 0.);
    float brightness = 1. - exp(-2.5*rho);
    return vec4(0., brightness, brightness, 1.);
  }
`;
