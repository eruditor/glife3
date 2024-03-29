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
      
      uint rulecoord = 0u;
      for(int n=0; n<`+RC+`; n++) {
        rulecoord *= `+RB+`u;
        rulecoord += cells[n].a;  // @ 8bit-packing only!
      }
      ivec2 t = ivec2(rulecoord, 0);
      if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
      
      uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
      
      // rule.a is the new color (value) of the cell
      color.a = rule.a;
      
      ` + (TT>2 ? `
      uvec4 prev = GetPrevCell(0, 0, 0);
      // color.a - prev.a
      if(prev.a>0u) {
        int inewv = int(color.a) - int(prev.a);
        if(inewv<0) inewv += `+RB+`;
        color.a = uint(inewv);
      }
      ` : ``) + `
      
      ` + fs_PackAliveness('color.a') + `
      
      ` + fs_Prepare2Return('color') + `
    }
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);