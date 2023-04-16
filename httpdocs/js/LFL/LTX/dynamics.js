var fs_ExtractRGBA = ``;

var CalcFragmentShaderSource = `
  precision highp float;
  precision highp int;
  
  ////////////////////////////////////////////////////////////////
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define iR1 `+(RD+1)+`
  #define dT `+(1/DT)+`
  
  #define eps 0.000001
  
  ////////////////////////////////////////////////////////////////
  
  ` + GetUniforms4Ruleset() + `
  
  ////////////////////////////////////////////////////////////////
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
  uniform highp sampler2D u_rulestexture;  // Rules texture
  uniform highp uint u_nturn;
  
  in vec2 v_texcoord;  // the texCoords passed in from the vertex shader
  
  out `+field_Vec4P+` glFragColor[`+FD+`];
  
  ivec3 tex3coord;
  ivec3 fieldSize;
  
  ` + fs_ModuloTorus + `
  
  ` + fs_GetCell() + `
  
  ` + fs_GetTexel2D + `
  
  ////////////////////////////////////////////////////////////////
  
  float bell1(float x, float m, float s) {
    if(s<=0.) return 0.;
    float v = (x-m)/s;
    return exp(-v*v/2.);
    //return exp( -(x-m)*(x-m) / s / s / 2. );  // order of operands in this multiplication affects the result!!!
  }
  
  vec4 texels[`+K4+`];
  
  void getTexels(float r) {
    int ix = int(round(r*`+TXL1+`.));
    ` + (() => {
      var ret = '';
      for(var kb=0; kb<K4; kb++) {
        ret += `    texels[`+kb+`] = texelFetch(u_rulestexture, ivec2(ix, `+kb+`), 0);\n`;
      }
      return ret.trim();
    })() + `
  }
  
  float[`+LL+`] getWeight(float r) {
    float[`+LL+`] ret;
    getTexels(r);
    ` + (() => {
      var ret = '';
      for(var l=0; l<LL; l++) {
        var ll = (LL>=10 && l<10 ? ' ' : '') + l;
        ret += '    ret['+ll+'] = texels['+floor(l/4)+']['+(l%4)+'];\n';
      }
      return ret.trim();
    })() + `
    return ret;
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec3 drawKernel(vec2 uv) {
    ivec2 ij = ivec2(uv / 0.25);  // 0..3
    if(ij.x>3) return vec3(0);
    
    int l = (3-ij.y)*4 + ij.x;  // [3-ij.y][ij.x]
    if(l>=`+LL+`) return vec3(0);
    
    vec2 xy = mod(uv, 0.25) * 8. - 1.;  // -1..1
    float r = length(xy);
    
    vec3 rgb;
    
    vec2 a = abs(xy);
    if((a.x>0.94 || a.y>0.94) && (a.x<0.98 && a.y<0.98)) { rgb[dst[l]] = 1.;  return rgb; }
    
    if(r>1.) return vec3(0);
    
    getTexels(r);
    rgb[src[l]] = texels[l/4][l%4];
    
    return rgb;
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec4 self, cell;
  float[`+LL+`] sum, total, weight;
  
  int IncSum(int dx, int dy) {
    cell = GetCell(dx, dy, 0);
    `+IterateGLSLarray('sum[l] += cell[src[l]] * weight[l];  total[l] += weight[l];')+`
    return 0;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);
    
    float r;
    int x, y;
    
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
      weight = getWeight(r);
      IncSum( x,  x);
      IncSum( x, -x);
      IncSum(-x,  x);
      IncSum(-x, -x);
    }
    
    // semiquadrants
    for(x=1; x<iR; x++) {
      for(y=x+1; y<=iR; y++) {
        r = length(vec2(x,y)) / R;
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
    
    vec3 growths = vec3(0);
    float avg;
    `+IterateGLSLarray('avg = sum[l] / total[l];  growths[dst[l]] += eta[l] * ( bell1(avg, mu[l], sigma[l]) * 2. - 1. );')+`
    vec3 rgb = clamp(self.rgb + dT * growths, 0., 1.);
    
    //rgb = drawKernel(v_texcoord.xy / float(fieldSize.y));
    
    glFragColor[0] = vec4(rgb, 1.);
  }
`;
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource, ["a_position", "u_fieldtexture", "u_rulestexture", "u_nturn"]);
console.log(CalcFragmentShaderSource);

// LEGACY ////////////////////////////////////////////////////////////////

/*
// first 2 frames are awfully slow on MacBook: https://stackoverflow.com/questions/28005206/gldrawarrays-first-few-calls-very-slow-using-my-shader-and-then-very-fast
    ` + (() => {
      var ret = '';
      for(var x=1; x<RD; x++) {
        for(var y=x+1; y<RD; y++) {
          var r = Math.sqrt(x*x+y*y) / RD;
          if(r>1) continue;
          ret += 'weight = getWeight('+ParseTerminateFraction(r)+');';
          ret += 'IncSum('+x+','+y+');IncSum('+x+',-'+y+');IncSum(-'+x+','+y+');IncSum(-'+x+',-'+y+');';
          ret += 'IncSum('+y+','+x+');IncSum('+y+',-'+x+');IncSum(-'+y+','+x+');IncSum(-'+y+',-'+x+');\n';
        }
      }
      return ret;
    })() + `
*/