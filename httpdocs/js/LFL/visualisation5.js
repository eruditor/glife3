var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    vec4 ret = cell;  ret.a = 1.;
    
    /*
    ret.r = ret.r<0. ? -2.*ret.r : 0.;
    ret.g = ret.g<0. ? -2.*ret.g : 0.;
    ret.b = ret.b<0. ? -2.*ret.b : 0.;
    */
    
    return ret;
  }
`;
