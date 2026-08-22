var fs_ExtractRGBA = ``;

function CalcFragmentShaderSource() {
  var outputs = '';
  for(var z=0; z<FD; z++) {
    outputs += `    glFragColor[`+z+`] = AverageNeighbors(`+z+`);\n`;
  }

  return `
  precision highp float;
  precision highp int;

  uniform `+field_Sampler+` u_fieldtexture;

  in vec2 v_texcoord;

  out `+field_Vec4P+` glFragColor[`+FD+`];

  ivec3 tex3coord;
  ivec3 fieldSize;

  `+fs_ModuloTorus+`

  `+fs_GetCell()+`

  vec4 AverageNeighbors(int layer) {
    tex3coord.z = layer;

    return (
      GetCell(-1, -1, 0) +
      GetCell( 0, -1, 0) +
      GetCell( 1, -1, 0) +
      GetCell(-1,  0, 0) +
      GetCell( 1,  0, 0) +
      GetCell(-1,  1, 0) +
      GetCell( 0,  1, 0) +
      GetCell( 1,  1, 0)
    ) / 8.0;
  }

  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);

`+outputs+`
  }
  `;
}

var CalcProgram = createProgram4Frag(
  gl,
  CalcFragmentShaderSource(),
  ["a_position", "u_fieldtexture"]
);
