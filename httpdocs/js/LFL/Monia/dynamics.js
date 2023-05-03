var fs_ExtractRGBA = ``;

function CalcFragmentShaderSource(Uniforms4Ruleset) {
  return `
  precision highp float;
  precision highp int;
  
  ////////////////////////////////////////////////////////////////
  
  #define R `+RD+`.
  #define iR `+RD+`
  #define iR1 `+(RD+1)+`
  #define dT `+(1/DT)+`
  
  #define eps 0.000001
  
  ////////////////////////////////////////////////////////////////
  
  ` + Uniforms4Ruleset + `
  
  ////////////////////////////////////////////////////////////////
  
  uniform `+field_Sampler+` u_fieldtexture;  // Field texture
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
  
  float get1Weight(float r, int l) {
    float Br = betaLen[l] / relR[l] * r;
    int iBr = int(Br);
    float mod1 = fract(Br);
    float height = iBr==0 ? beta0[l] : (iBr==1 ? beta1[l] : beta2[l]);
    return height * bell1(mod1, 0.5, 0.15);
  }
  
  float[`+LL+`] getWeight(in float r) {
    float[`+LL+`] ret;
    `+IterateGLSLarray('ret[l] = get1Weight(r, lll);')+`
    return ret;
  }
  
  ////////////////////////////////////////////////////////////////
  
  vec3 drawKernel() {
    vec2 uv = 3. * (v_texcoord.xy / float(fieldSize.y));
    ivec2 ij = ivec2(uv);
    
    int l = 3 * ij.x + (2 - ij.y);
    if(l>=`+LL+`) return vec3(0);
    
    vec2 xy = 2. * fract(uv) - 1.;
    vec2 ab = abs(xy);
    float r = length(xy);
    
    vec3 rgb;
    if(ab.x>=0.98 || ab.y>=0.98) { return vec3(0); }
    if(ab.x>=0.94 || ab.y>=0.94) { rgb[dst[l]] = 1.;  return rgb; }
    if(xy.y<-0.84) {
      float t = (xy.x/0.94 + 1.) / 2.;
      float gr = eta[l] * ( bell1(t, mu[l], sigma[l]) * 2. - 1. );
      rgb = gr>0. ? vec3(gr, gr, 0) : vec3(0, -gr, -gr);
      return rgb;
    }
    
    if(r>1.) return vec3(0);
    rgb[src[l]] = get1Weight(r, l);
    
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
  
  vec3 CalcGrown() {
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
    
    return rgb;
  }
  
  void main() {
    fieldSize = textureSize(u_fieldtexture, 0);
    tex3coord = ivec3(v_texcoord, 0);
    
    const vec3 masses = vec3(1, 1, 1);  // masses of r, g, b substances
    const float d = 1.;  // size of cell's side
    const float l = 1.;  // size of transferred area (normally l=d; while l>d means temperature-like spraying)
    const float d2 = d / 2.;
    const float l2 = l / 2.;
    const float dd = d * d;
    const float ll = l * l;
    
    vec4 new_am = vec4(0);  // r, g, b, a=total_mass
    vec4 new_cv = vec4(0);
    for(int dx=-1; dx<=1; dx++) {
      for(int dy=-1; dy<=1; dy++) {
        vec4 cv = GetCell(dx, dy, 1);  // cx, cy, vx, vy
        vec2 qm = vec2(dx, dy) * d + cv.xy + cv.zw;  // transferring square center of mass
        vec4 Q = vec4(qm - l2, qm + l2);  // transferring square borders
        vec4 B = clamp(Q, -d2, d2);  // transferring square borders clipped to the cell
        float S = (B.z - B.x) * (B.w - B.y);  // transferred area
        if(S==0.) continue;
        vec2 Cm = (B.xy + B.zw) / 2.;  // transferred area center of mass
        float Sd = S / dd;  // percent of transferred area
        
        vec4 am = GetCell(dx, dy, 0);  // r, g, b - masses of substances
        float mass0 = dot(masses, am.rgb);
        new_am += vec4(am.rgb, mass0) * Sd;
        new_cv += vec4(Cm, cv.zw) * mass0 * Sd;
      }
    }
    if(new_am.a>0.) new_cv = new_cv / new_am.a;
    
    glFragColor[0] = new_am;
    glFragColor[1] = new_cv;
    
    //glFragColor[0] = vec4(CalcGrown(), 1.);
    
    `+(cfg.debug==-1?`glFragColor[0] = vec4(drawKernel(), 1.);`:``)+`
  }
  `;
}
var CalcProgram = createProgram4Frag(gl, CalcFragmentShaderSource(GetUniforms4Ruleset()), ["a_position", "u_fieldtexture", "u_nturn"]);
//console.log(CalcFragmentShaderSource);

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