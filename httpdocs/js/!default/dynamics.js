var CalcFragmentShaderSource = `
precision mediump float;
precision highp int;

uniform highp usampler3D u_fieldtexture;  // Field texture
uniform highp usampler3D u_prevtexture;  // Previous Field texture
uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture

in vec2 v_texcoord;  // the texCoords passed in from the vertex shader

out uvec4 glFragColor[`+FD+`];

ivec3 tex3coord;
ivec3 fieldSize;

` + fs_ModuloTorus + `

` + fs_GetCell() + `

` + (TT>2 ? fs_GetCell('GetPrevCell', 'u_prevtexture') : ``) + `

` + fs_GetTexel2D + `

void main() {
  fieldSize = textureSize(u_fieldtexture, 0);
  
  uvec4 color;
  
  for(int layer=0; layer<`+FD+`; layer++) {
    
    // getting cell's neighborhood
    
    tex3coord = ivec3(v_texcoord, layer);
    
    uvec4 cells[`+RC+`];
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
    
    uvec4 self = cells[0];  // previous self cell state
    
    ` + fs_PackAliveness('color.a') + `
    
    ` + fs_Prepare2Return('color') + `
  }
}
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);