var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    vec4 ret;
    
    const float k = 0.9;
    
    ret.r = k * cell.r + 1. - k;
    ret.g = k * cell.g + 1. - k;
    ret.b = k * cell.b + 1. - k;
    ret.a = 1.;
    
    return ret;
  }
`;
