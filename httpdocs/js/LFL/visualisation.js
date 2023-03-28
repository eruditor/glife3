var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    vec4 ret = cell;  //vec4(0., 0., 0., 1.);
    
    ret.a = 1.;
    
    return ret;
  }
`;
