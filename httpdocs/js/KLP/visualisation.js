var fs_Color4Cell = `
  vec4 Color4Cell(vec4 cell, int layer) {
    if(layer!=0) return vec4(0.,0.,0.,1.);
    float matter = max(cell.r,0.);
    float cyan = min(matter/1.7,1.);
    float white = clamp((matter-1.7)/1.3,0.,1.);
    return vec4(white,cyan,cyan,1.);
  }
`;
