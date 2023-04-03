var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    //vec4 ret = cell;
    
    vec4 ret = vec4(cell.g/4., sqrt(cell.r*cell.r + cell.g*cell.g)/8., cell.r/1., 1);
    
    return ret;
  }
`;
