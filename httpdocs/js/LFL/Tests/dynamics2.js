var fs_ExtractRGBA = ``;

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
    if(s<=0.) return 0.;
    float v = (x-m)/s;
    return exp(-v*v/2.);
  }
  
  float Km = 0.50;  // shift
  float Ks = 0.15;  // width
  
  //mat2 Km2 = mat2(0.20, 0.50, 0.50, 0.50);
  //mat2 Ks2 = mat2(0.30, 0.33, 0.44, 0.15);
  //mat2 Gh2 = mat2(1.00, 0.00, 0.50, 1.00);
  
  mat2 Km2 = mat2(0.50, 0.50, 0.50, 0.20);
  mat2 Ks2 = mat2(0.15, 0.44, 0.33, 0.30);
  mat2 Gh2 = mat2(1.00, 0.50, 0.70, 1.00);
  //                    r affects g
  
  #define eps 0.000001
  const mat2 m2eps = mat2(eps, eps, eps, eps);
  
  float K(float r) {  // Kernel
    return bell(r/R, Km, Ks);
  }
  
  float K0(float r, float m, float s) {
    return bell(r/R, m, s);
  }
  
  mat2 K2(float r) {
    return mat2(
      K0(r, Km2[0].r, Ks2[0].r),
      K0(r, Km2[0].g, Ks2[0].g),
      K0(r, Km2[1].r, Ks2[1].r),
      K0(r, Km2[1].g, Ks2[1].g)
    );
  }
  
  float G(float u) {  // Growth
    return bell(u, 0.15, 0.015) * 2. - 1.;
  }
  
  vec2 G2(mat2 u) {
    return vec2(
      Gh2[0].r*G(u[0].r) + Gh2[1].r*G(u[1].r),
      Gh2[0].g*G(u[0].g) + Gh2[1].g*G(u[1].g)
    );
  }
  
  //vec4 G(vec4 u) { return vec4(G(u.r), G(u.g), G(u.b), G(u.a)); }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    
    tex3coord = ivec3(v_texcoord, 0);
    
    vec4 color;
    
    //Ks = 0.01 + 0.50 * (v_texcoord.x / `+FW+`.);
    //Km = 0.01 + 1.50 * (v_texcoord.y / `+FH+`.);
    //Ks = 0.30;
    //Km = 0.20;
    
    vec2 self;
    mat2 sumC = mat2(0);
    mat2 sumK = mat2(0);
    for(int dx=-iR; dx<=iR; dx++) {
      for(int dy=-iR; dy<=iR; dy++) {
        vec4 cell = GetCell(dx, dy, 0);
        if(dx==0 && dy==0) self = cell.rg;
        
        mat2 k = K2( length(vec2(dx,dy)) );
        sumC += mat2(k[0].r*cell.r, k[0].g*cell.g, k[1].r*cell.r, k[1].g*cell.g);
        sumK += k;
      }
    }
    
    mat2 sum = sumC / (sumK + m2eps);
    vec2 delta = dT * G2(sum);
    
    color.rg = clamp(self + delta, 0., 1.);
    color.a = 1.;
    
    glFragColor[0] = color;
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_prevtexture", "u_rulestexture"]);
//console.log(CalcFragmentShaderSource);