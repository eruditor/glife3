var fs_ExtractRGBA = `
  
`;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define dT 0.1
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  uniform `+field_Sampler+` u_prevtexture;  // Previous Field texture
  uniform highp usampler2D u_rulestexture[`+FD+`];  // Rules texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + fs_GetTexel2D + `
  
  float bell(float x, float m, float s) {
    float v = (x-m)/s;
    return exp(-v*v/2.);
  }
  
  float Km = 0.50;
  float Ks = 0.15;
  
  float K(float r) {  // Kernel
    return bell(r/R, Km, Ks);
  }
  
  float G(float r) {  // Growth
    return bell(r, 0.15, 0.015) * 2. - 1.;
  }
  
  vec4 G(vec4 v) {
    return vec4(G(v.r), G(v.g), G(v.b), G(v.a));
  }
  
  float len(int dx, int dy) {
    return sqrt(float(dx*dx+dy*dy));
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    tex3coord = ivec3(v_texcoord, 0);
    
    vec4 color;
    
    //Ks = 0.01 + 0.50 * (v_texcoord.x / `+FW+`.);
    //Km = 0.01 + 1.50 * (v_texcoord.y / `+FH+`.);
    //Ks = 0.30;
    //Km = 0.20;
    
    vec4 self = vec4(0);
    vec4 cell;
    vec4 sumC = vec4(0);
    float sumK = 0.;
    for(int dx=-iR; dx<=iR; dx++) {
      for(int dy=-iR; dy<=iR; dy++) {
        cell = GetCell(dx, dy, 0);
        if(dx==0 && dy==0) self = cell;
        float r = len(dx, dy);
        float k = K(r);
        sumC += k * cell;
        sumK += k;
      }
    }
    
    vec4 sum = sumC / sumK;
    vec4 delta = dT * G(sum);
    
    color = clamp(self + delta, 0., 1.);
    
    //color = vec4(0., K(len(tex3coord.x - fieldSize.x/2, tex3coord.y - fieldSize.y/2)), 0., 1.);  // draw Kernel
    
    glFragColor[0] = color;
  }
`;
/*
    for(int layer=0; layer<`+FD+`; layer++) {
      tex3coord = ivec3(v_texcoord, layer);
      
      // getting cell's neighborhood
      
      ` + fs_GetNeibs + `
      
      // finding rule for this neighborhood
      
      `+field_Vec4P+` sum4 = `+field_Vec4+`(0);
      for(int n=0; n<`+RC+`; n++) {
        sum4 += cells[n];
      }
      float a = 4., b = 1.;
      color = (a * self + b * (sum4 / `+RC+`.)) / (a + b);
      
      ` + fs_PackAliveness('color.a') + `
      
      ` + fs_Prepare2Return('color') + `
    }
*/
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);
//console.log(CalcFragmentShaderSource);