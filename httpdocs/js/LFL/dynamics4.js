var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  ////////////////////////////////////////////////////////////////
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define iR1 `+(RD+1)+`
  #define dT 0.2
  
  #define EPSILON 0.000001
  #define mult matrixCompMult
  
  ////////////////////////////////////////////////////////////////
  
  #define species7
  
  #ifdef species7
  // species: GDNQYX Tessellatium (stable)
  //                         0       1       2       3       4       5       6       7       8       9       10      11      12      13      14      15
  const mat4 betaLen = mat4( 1.,     1.,     2.,     2.,     1.,     2.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     1.,     0. );  // kernel ring number
  const mat4   beta0 = mat4( 1.,     1.,     1.,     0.,     1.,     5./6.,  1.,     1.,     1.,     11./12.,3./4.,  1.,     1.,     1./6.,  1.,     0. );  // kernel ring heights
  const mat4   beta1 = mat4( 0.,     0.,     1./4.,  1.,     0.,     1.,     0.,     0.,     0.,     1.,     1.,     11./12.,0.,     1.,     0.,     0. );
  const mat4   beta2 = mat4( 0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0.,     0. );
  const mat4      mu = mat4( 0.242,  0.375,  0.194,  0.122,  0.413,  0.221,  0.192,  0.492,  0.426,  0.361,  0.464,  0.361,  0.235,  0.381,  0.216,  0. );  // growth center
  const mat4   sigma = mat4( 0.061,  0.1553, 0.0361, 0.0531, 0.0774, 0.0365, 0.0649, 0.1219, 0.1759, 0.1381, 0.1044, 0.0686, 0.0924, 0.1118, 0.0748, 1. );  // growth width
  const mat4     eta = mat4( 0.144,  0.506,  0.332,  0.3,    0.502,  0.58,   0.344,  0.268,  0.582,  0.326,  0.418,  0.642,  0.39,   0.378,  0.294,  0. );  // growth strength
  const mat4    relR = mat4( 0.98,   0.59,   0.5,    0.93,   0.73,   0.88,   0.93,   0.61,   0.84,   0.7,    0.57,   0.73,   0.74,   0.87,   0.72,   1. );  // relative kernel radius
  const mat4     src = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     0.,     0.,     1.,     1.,     2.,     2.,     0. );  // source channels
  const mat4     dst = mat4( 0.,     0.,     0.,     1.,     1.,     1.,     2.,     2.,     2.,     1.,     2.,     0.,     2.,     0.,     1.,     0. );  // destination channels
  #endif
  
  ////////////////////////////////////////////////////////////////
  
  const ivec4 iv0 = ivec4(0);
  const ivec4 iv1 = ivec4(1);
  const ivec4 iv2 = ivec4(2);
  const ivec4 iv3 = ivec4(3);
  const vec4 v0 = vec4(0.);
  const vec4 v1 = vec4(1.);
  const mat4 m0 = mat4(v0, v0, v0, v0);
  const mat4 m1 = mat4(v1, v1, v1, v1);
  
  const vec4 kmv = vec4(0.5);    // kernel ring center
  const mat4 kmu = mat4(kmv, kmv, kmv, kmv);
  const vec4 ksv = vec4(0.15);    // kernel ring width
  const mat4 ksigma = mat4(ksv, ksv, ksv, ksv);
  
  const ivec4 src0 = ivec4(src[0]), src1 = ivec4(src[1]), src2 = ivec4(src[2]), src3 = ivec4(src[3]);
  const ivec4 dst0 = ivec4(dst[0]), dst1 = ivec4(dst[1]), dst2 = ivec4(dst[2]), dst3 = ivec4(dst[3]);  
  
  ////////////////////////////////////////////////////////////////
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + fs_GetTexel2D + `
  
  ////////////////////////////////////////////////////////////////
  
  mat4 bell(in mat4 x, in mat4 m, in mat4 s) {
    mat4 v = -mult(x-m, x-m) / s / s / 2.;
    return mat4( exp(v[0]), exp(v[1]), exp(v[2]), exp(v[3]) );
  }
  
  mat4 getWeight(in float r) {
    if(r > 1.) return m0;
    mat4 Br = betaLen / relR * r;  // scale radius by number of rings and relative radius
    ivec4 Br0 = ivec4(Br[0]), Br1 = ivec4(Br[1]), Br2 = ivec4(Br[2]), Br3 = ivec4(Br[3]);

    // (Br==0 ? beta0 : 0) + (Br==1 ? beta1 : 0) + (Br==2 ? beta2 : 0)
    mat4 height = mat4(
      beta0[0] * vec4(equal(Br0, iv0)) + beta1[0] * vec4(equal(Br0, iv1)) + beta2[0] * vec4(equal(Br0, iv2)),
      beta0[1] * vec4(equal(Br1, iv0)) + beta1[1] * vec4(equal(Br1, iv1)) + beta2[1] * vec4(equal(Br1, iv2)),
      beta0[2] * vec4(equal(Br2, iv0)) + beta1[2] * vec4(equal(Br2, iv1)) + beta2[2] * vec4(equal(Br2, iv2)),
      beta0[3] * vec4(equal(Br3, iv0)) + beta1[3] * vec4(equal(Br3, iv1)) + beta2[3] * vec4(equal(Br3, iv2))
    );
    mat4 mod1 = mat4( fract(Br[0]), fract(Br[1]), fract(Br[2]), fract(Br[3]) );
    return mult(height, bell(mod1, kmu, ksigma));
  }
  
  vec4 getSrc(in vec3 v, in ivec4 srcv) {  // get colors (vectorized) from source channels
    return
      v.r * vec4(equal(srcv, iv0)) + 
      v.g * vec4(equal(srcv, iv1)) +
      v.b * vec4(equal(srcv, iv2));
  }
  
  float getDst(in mat4 m, in ivec4 ch) {  // get color for destination channel
    return 
      dot(m[0], vec4(equal(dst0, ch))) + 
      dot(m[1], vec4(equal(dst1, ch))) + 
      dot(m[2], vec4(equal(dst2, ch))) + 
      dot(m[3], vec4(equal(dst3, ch)));
  }
  
  mat4 getVal(vec4 cell) {  // get values at given position
    vec3 val = cell.rgb;
    return mat4(
      getSrc(val, src0),
      getSrc(val, src1),
      getSrc(val, src2),
      getSrc(val, src3)
    );
  }
  
  ////////////////////////////////////////////////////////////////
  
  float len(int dx, int dy) {
    return length(vec2(dx,dy));  // sqrt(float(dx*dx+dy*dy))
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec4 self, cell;
  mat4 sum = mat4(0), total = mat4(0), weight;
  
  int IncSum(int dx, int dy) {
    cell = GetCell(dx, dy, 0);
    if(dx==0 && dy==0) self = cell;
    
    mat4 valSrc = getVal(cell);
    
    sum += mult(valSrc, weight);
    total += weight;
    
    return 0;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);
    
    float r;
    int x, y;
    
    /*
    for(x=-iR; x<=iR; x++) {
      for(y=-iR; y<=iR; y++) {
        r = len(x, y) / R;
        if(r>1.) continue;
        weight = getWeight(r);
        IncSum(x, y);
      }
    }
    */
    
    // central cell (self)
    x = 0;  y = 0;  r = 0.;
    weight = getWeight(r);
    IncSum(x, y);
    self = cell;
    
    // axes
    for(x=1; x<=iR; x++) {
      r = float(x) / R;
      weight = getWeight(r);
      IncSum( x,  0);
      IncSum(-x,  0);
      IncSum( 0,  x);
      IncSum( 0, -x);
    }
    
    // diagonals
    int diagR = int(floor( R / sqrt(2.) ));  // floor, not ceil or round!
    for(x=1; x<=diagR; x++) {
      r = sqrt(2.) * float(x) / R;
      if(r>1.) continue;
      weight = getWeight(r);
      IncSum( x,  x);
      IncSum( x, -x);
      IncSum(-x,  x);
      IncSum(-x, -x);
    }
    
    for(x=1; x<iR; x++) {
      for(y=x+1; y<=iR; y++) {
        r = len(x, y) / R;
        if(r>1.) continue;
        weight = getWeight(r);
        IncSum( x,  y);
        IncSum( x, -y);
        IncSum(-x,  y);
        IncSum(-x, -y);
        IncSum( y,  x);
        IncSum( y, -x);
        IncSum(-y,  x);
        IncSum(-y, -x);
      }
    }
    
    mat4 avg = sum / (total + EPSILON);
    mat4 growth = mult(eta, bell(avg, mu, sigma) * 2. - 1.);
    vec3 growthDst = vec3( getDst(growth, iv0), getDst(growth, iv1), getDst(growth, iv2) );
    vec3 rgb = clamp(self.rgb + dT * growthDst, 0., 1.);
    
    glFragColor[0] = vec4(rgb, 1.);
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture"]);
//console.log(CalcFragmentShaderSource);