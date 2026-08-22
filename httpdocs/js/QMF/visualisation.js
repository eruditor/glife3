var fs_Color4Cell = `
  vec4 Color4Cell(`+field_Vec4P+` cell, int layer) {
    return vec4(cell.rgb, 1.);
  }
`;
