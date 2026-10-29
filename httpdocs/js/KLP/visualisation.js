var fs_Color4Cell = `
  vec4 Color4Cell(vec4 cell, int layer) {
    float rho1 = dot(cell.rg, cell.rg);
    float rho2 = dot(cell.ba, cell.ba);
    return vec4(
      1. - exp(-2.*rho1),
      1. - exp(-0.5*(rho1+rho2)),
      1. - exp(-2.*rho2),
      1.
    );
  }
`;
