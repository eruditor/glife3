var fs_ExtractRGBA = `
  
`;

var CalcFragmentShaderSource = `
  precision mediump float;
  precision highp int;
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  uniform `+field_Sampler+` u_prevtexture;  // Previous Field texture
  uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + (TT>2 ? fs_GetCell('GetPrevCell', 'u_prevtexture') : ``) + `
  
  ` + fs_GetTexel2D + `
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    `+field_Vec4P+` color;
    
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      // getting cell's neighborhood
      
      ` + fs_GetNeibs + `
      
      // finding rule for this neighborhood
      
      `+field_Vec4P+` sum4 = `+field_Vec4+`(0);
      for(int n=0; n<`+RC+`; n++) {
        sum4 += cells[n];
      }
      uint a = 4u, b = 1u;
      color = (a * self + b * (sum4 / `+RC+`u)) / (a + b);
      
      ` + fs_PackAliveness('color.a') + `
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);