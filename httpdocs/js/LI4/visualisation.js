var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    vec4 ret = vec4(0., 0., 0., 1.);
    
    ret.r = float(cell.r) / 255.;
    ret.g = float(cell.g) / 255.;
    ret.b = float(cell.b) / 255.;
    
    return ret;
  }
`;
