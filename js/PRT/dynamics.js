var CalcFragmentShaderSource = `
precision mediump float;
precision highp int;

uniform highp usampler3D u_fieldtexture;  // Field texture
uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture

uniform int u_ps[`+ND+`];  // global shift of 3*3 squares

in vec2 v_texcoord;  // the texCoords passed in from the vertex shader

out uvec4 glFragColor[`+FD+`];

ivec3 tex3coord;
ivec3 fieldSize;

` + fs_ModuloTorus + `

` + fs_GetCell() + `

` + fs_GetTexel2D + `

void main() {
  fieldSize = textureSize(u_fieldtexture, 0);
  
  uvec4 color;
  
  for(int layer=0; layer<`+FD+`; layer++) {
    ivec3 cur3coord = ivec3(v_texcoord, layer);
    int dx3 = (3 + cur3coord.x + u_ps[0]) % 3;
    int dy3 = (3 + cur3coord.y + u_ps[1]) % 3;
    ivec3 shift2grid = ivec3(1 - dx3, 1 - dy3, 0);
    
    tex3coord = ivec3(v_texcoord, layer) + shift2grid;
    
    // getting cell's neighborhood
    uvec4 cells[`+RC+`];
    ` + fs_GetNeibs + `
    
    // finding rule for this neighborhood
    
    uint rulecoord = 0u;
    for(int n=0; n<`+RC+`; n++) {
      rulecoord *= `+RB+`u;
      rulecoord += cells[n].a;  // 8bit-packing only!
    }
    ivec2 t = ivec2(rulecoord, 0);
    if(t.x>=`+RX+`) { t.y = t.x / `+RX+`;  t.x = t.x % `+RX+`; }
    
    uvec4 rule = GetTexel2D(u_rulestexture, layer, t);
    int new_square = 256 * 256 * int(rule.g) + 256 * int(rule.b) + int(rule.a);
    uint u_new_square = 256u * rule.b + rule.a;
    
    int dxdy10 = 10 * dx3 + dy3;
    int pwr = 0;  // coordinate of needed cell in partition's square
         if(dxdy10== 0) pwr = 7;
    else if(dxdy10== 1) pwr = 0;
    else if(dxdy10== 2) pwr = 1;
    else if(dxdy10==10) pwr = 6;
    else if(dxdy10==11) pwr = 8;
    else if(dxdy10==12) pwr = 2;
    else if(dxdy10==20) pwr = 5;
    else if(dxdy10==21) pwr = 4;
    else if(dxdy10==22) pwr = 3;
    
    uint nth_bit = 0u;  // float pow() doesn't work here due to rounding precision!
    for(int n=0; n<=pwr; n++) {
      nth_bit = u_new_square % `+RB+`u;
      u_new_square = (u_new_square - nth_bit) / `+RB+`u;
    }
    
    color.a = nth_bit;
    
    uvec4 self = GetCell(dx3 - 1, dy3 - 1, 0);  // previous self cell state
    
    ` + fs_PackAliveness('color.a') + `
    
    ` + fs_Prepare2Return('color') + `
  }
}
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_ps"]);
